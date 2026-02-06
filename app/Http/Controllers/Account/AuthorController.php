<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\AuthorModeration;
use App\Models\AuthorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AuthorController extends Controller
{

    /**
     * Список авторов пользователя
     */
    public function index()
    {
        $user = Auth::user();
        $authors = $user->authors()->get();
        $pendingRequests = $user->authorRequests()->pending()->get();

        return view('account.authors.index', compact('authors', 'pendingRequests'));
    }

    /**
     * Создать запрос на автора
     */
    public function requestAuthor(Request $request)
    {
        $user = Auth::user();

        // Проверяем, нет ли уже активного запроса
        $activeRequest = $user->authorRequests()->pending()->exists();
        if ($activeRequest) {
            return back()->withErrors(['author_name' => 'У вас уже есть активный запрос на автора. Дождитесь модерации текущего запроса.']);
        }

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_card_url' => ['required', 'url', 'max:500'],
        ], [
            'author_name.required' => 'Поле "Имя Автора" обязательно для заполнения.',
            'author_card_url.required' => 'Поле "Ссылка на карточку артиста" обязательно для заполнения.',
            'author_card_url.url' => 'Поле "Ссылка на карточку артиста" должно быть валидным URL.',
        ]);

        // Проверяем, существует ли автор
        $existingAuthor = Author::where('name', $validated['author_name'])->first();

        if ($existingAuthor) {
            // Проверяем, привязан ли автор к другому пользователю
            if ($existingAuthor->users()->exists()) {
                return back()->withErrors([
                    'author_name' => 'Автор "' . $validated['author_name'] . '" уже привязан к другому пользователю. Пожалуйста, обратитесь к администратору: ' . route('authors.show', ['slug' => 'neurozvuk']),
                ])->withInput();
            }

            // Проверяем, есть ли уже запрос на этого автора
            $pendingRequest = AuthorRequest::where('author_id', $existingAuthor->id)
                ->where('status', 'pending')
                ->exists();

            if ($pendingRequest) {
                return back()->withErrors([
                    'author_name' => 'На этого автора уже подана заявка, которая находится на модерации.',
                ])->withInput();
            }
        }

        // Создаем запрос
        AuthorRequest::create([
            'user_id' => $user->id,
            'author_name' => $validated['author_name'],
            'author_card_url' => $validated['author_card_url'],
            'author_id' => $existingAuthor?->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Запрос на автора отправлен на модерацию.');
    }

    /**
     * Показать форму редактирования автора
     */
    public function edit(Author $author)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if (!$user->authors()->where('authors.id', $author->id)->exists()) {
            abort(403, 'У вас нет доступа к этому автору.');
        }

        // Проверяем, есть ли незавершенные модерации
        $hasPendingModerations = $author->pendingModerations()->exists();

        return view('account.authors.edit', compact('author', 'hasPendingModerations'));
    }

    /**
     * Обновить автора (создает записи для модерации)
     */
    public function update(Request $request, Author $author)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if (!$user->authors()->where('authors.id', $author->id)->exists()) {
            abort(403, 'У вас нет доступа к этому автору.');
        }

        // Проверяем, нет ли незавершенных модераций
        $pendingModerations = $author->pendingModerations()->where('user_id', $user->id)->exists();
        if ($pendingModerations) {
            return back()->withErrors(['general' => 'Нельзя редактировать автора, пока есть незавершенные изменения на модерации.']);
        }

        $validated = $request->validate([
            'img' => ['nullable', 'image', 'max:2048'],
            'long_description' => ['nullable', 'string', 'max:5000'],
            'content' => ['nullable', 'string'],
            // SEO поля (опциональные, скрыты под спойлером)
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_h1' => ['nullable', 'string', 'max:255'],
        ]);

        // Определяем, какие поля изменились и создаем записи для модерации
        $fieldsToCheck = [];

        // Обрабатываем SEO поля - они переопределяют обычные поля
        $seoTitle = $validated['seo_title'] ?? null;
        $seoDescription = $validated['seo_description'] ?? null;
        $seoH1 = $validated['seo_h1'] ?? null;

        // Если SEO поля заполнены, добавляем их в проверку
        if (!empty($seoTitle)) {
            $fieldsToCheck['title'] = $seoTitle;
        }
        
        if (!empty($seoDescription)) {
            $fieldsToCheck['description'] = $seoDescription;
        }
        
        if (!empty($seoH1)) {
            $fieldsToCheck['h1'] = $seoH1;
        }
        
        // Добавляем остальные поля
        if ($request->hasFile('img')) {
            $fieldsToCheck['img'] = null; // Будет обработано ниже
        }
        
        if (isset($validated['long_description'])) {
            $fieldsToCheck['long_description'] = $validated['long_description'];
        }
        
        if (isset($validated['content'])) {
            $fieldsToCheck['content'] = $validated['content'];
        }

        $hasChanges = false;
        
        foreach ($fieldsToCheck as $field => $newValue) {
            $oldValue = $author->$field;

            // Обрабатываем загрузку изображения - сохраняем файл сразу, но применим только после одобрения
            if ($field === 'img' && $request->hasFile('img')) {
                $newValue = $this->handleImageUpload($request->file('img'));
            }

            // Нормализуем HTML для поля content перед сравнением (Quill может возвращать HTML в другом формате)
            $skipField = false;
            if ($field === 'content') {
                // Очищаем HTML от стилей и классов перед обработкой
                $newValue = $this->cleanHtmlFromStyles((string)$newValue);
                
                $oldValueNormalized = trim(preg_replace('/>\s+</', '><', (string)$oldValue));
                $newValueNormalized = trim(preg_replace('/>\s+</', '><', (string)$newValue));
                // Удаляем пустые параграфы в конце (Quill добавляет <p><br></p>)
                $oldValueNormalized = preg_replace('/<p><br><\/p>(\s*<p><br><\/p>)*$/i', '', $oldValueNormalized);
                $newValueNormalized = preg_replace('/<p><br><\/p>(\s*<p><br><\/p>)*$/i', '', $newValueNormalized);
                
                // Временное логирование для отладки
                \Log::debug('Content field comparison', [
                    'old_length' => strlen((string)$oldValue),
                    'new_length' => strlen((string)$newValue),
                    'old_normalized_length' => strlen($oldValueNormalized),
                    'new_normalized_length' => strlen($newValueNormalized),
                    'old_value_preview' => substr((string)$oldValue, 0, 150),
                    'new_value_preview' => substr((string)$newValue, 0, 150),
                    'are_equal_strict' => (string)$oldValue === (string)$newValue,
                    'are_equal_normalized' => $oldValueNormalized === $newValueNormalized,
                ]);
                
                // Используем нормализованное сравнение для content
                if ($oldValueNormalized === $newValueNormalized) {
                    $skipField = true;
                } else {
                    // Обновляем $newValue на нормализованное для сохранения
                    $newValue = $newValueNormalized;
                }
            } elseif ($field !== 'img' && (string)$oldValue === (string)$newValue) {
                // Пропускаем, если значение не изменилось (для других текстовых полей)
                $skipField = true;
            } elseif ($field === 'img' && $oldValue === $newValue) {
                // Для изображений проверяем по имени файла
                $skipField = true;
            }
            
            if ($skipField) {
                continue;
            }

            $hasChanges = true;
            
            // Создаем запись для модерации
            AuthorModeration::create([
                'author_id' => $author->id,
                'user_id' => $user->id,
                'field_name' => $field,
                'old_value' => (string)($oldValue ?? ''),
                'new_value' => (string)($newValue ?? ''),
                'status' => 'pending',
            ]);
        }
        
        if (!$hasChanges) {
            // Если были загружены файлы, но изменения не обнаружены, удаляем временные файлы
            if ($request->hasFile('img')) {
                $tempFile = $this->handleImageUpload($request->file('img'));
                if (Storage::disk('authors')->exists($tempFile)) {
                    Storage::disk('authors')->delete($tempFile);
                }
            }
            return back()->with('info', 'Изменений не обнаружено.');
        }

        return back()->with('success', 'Изменения отправлены на модерацию. Автор будет обновлен после одобрения администратором.');
    }

    /**
     * Отвязать автора
     */
    public function detach(Request $request, Author $author)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if (!$user->authors()->where('authors.id', $author->id)->exists()) {
            abort(403, 'У вас нет доступа к этому автору.');
        }

        // Подтверждение (checkbox отправляет '1' если отмечен)
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ], [
            'confirm.accepted' => 'Необходимо подтвердить отвязку автора, отметив чекбокс.',
        ]);

        // Отвязываем автора
        $user->authors()->detach($author->id);

        return redirect()->route('account.authors.index')
            ->with('success', 'Автор отвязан. Вы потеряли доступ к материалам этого автора.');
    }

    /**
     * Обработать загрузку изображения автора
     */
    private function handleImageUpload($file): string
    {
        $fileName = Str::uuid() . '.webp';
        $path = Storage::disk('authors')->path($fileName);
        
        Image::read($file->getRealPath())
            ->toWebp()
            ->save($path, 90);

        return $fileName;
    }

    /**
     * Очистить HTML от inline стилей и CSS классов
     * Удаляет все style и class атрибуты, оставляя только базовое форматирование
     */
    private function cleanHtmlFromStyles(string $html): string
    {
        if (empty(trim($html))) {
            return $html;
        }

        // Удаляем все inline стили (style="...")
        $html = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $html);
        
        // Удаляем все классы (class="...")
        $html = preg_replace('/\s*class\s*=\s*["\'][^"\']*["\']/i', '', $html);
        
        // Удаляем другие потенциально опасные атрибуты, которые могут содержать стили
        $html = preg_replace('/\s*id\s*=\s*["\'][^"\']*["\']/i', '', $html);
        
        // Разрешаем только безопасные атрибуты для ссылок и изображений
        // Для ссылок оставляем только href и target
        $html = preg_replace_callback('/<a\s+([^>]*?)>/i', function($matches) {
            $attrs = $matches[1];
            // Извлекаем только href и target
            preg_match('/href\s*=\s*["\']([^"\']*)["\']/i', $attrs, $hrefMatch);
            preg_match('/target\s*=\s*["\']([^"\']*)["\']/i', $attrs, $targetMatch);
            $result = '<a';
            if (!empty($hrefMatch[1])) {
                $result .= ' href="' . htmlspecialchars($hrefMatch[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            if (!empty($targetMatch[1])) {
                $result .= ' target="' . htmlspecialchars($targetMatch[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            $result .= '>';
            return $result;
        }, $html);
        
        // Для изображений оставляем только src и alt
        $html = preg_replace_callback('/<img\s+([^>]*?)>/i', function($matches) {
            $attrs = $matches[1];
            // Извлекаем только src и alt
            preg_match('/src\s*=\s*["\']([^"\']*)["\']/i', $attrs, $srcMatch);
            preg_match('/alt\s*=\s*["\']([^"\']*)["\']/i', $attrs, $altMatch);
            $result = '<img';
            if (!empty($srcMatch[1])) {
                $result .= ' src="' . htmlspecialchars($srcMatch[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            if (!empty($altMatch[1])) {
                $result .= ' alt="' . htmlspecialchars($altMatch[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            $result .= '>';
            return $result;
        }, $html);
        
        // Удаляем пустые атрибуты (например, <p > становится <p>)
        $html = preg_replace('/<(\w+)\s+>/i', '<$1>', $html);
        
        // Удаляем множественные пробелы между тегами
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }
}
