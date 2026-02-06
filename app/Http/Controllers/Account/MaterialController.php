<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Material;
use App\Models\Tag;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use getID3;

class MaterialController extends Controller
{

    /**
     * Список материалов пользователя
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $materials = $user->materials()
            ->with(['type', 'authors'])
            ->latest()
            ->paginate(20);

        return view('account.materials.index', compact('materials'));
    }

    /**
     * Показать форму создания материала
     */
    public function create()
    {
        $user = Auth::user();

        // Проверяем статус пользователя
        if (!$user->isActive()) {
            return redirect()->route('dashboard')
                ->with('error', 'У вас нет прав на создание материалов. Ожидайте активации администратором.');
        }

        // Проверяем, есть ли у пользователя активные авторы
        $activeAuthors = $user->authors()->get();
        if ($activeAuthors->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Нет активных авторов. Запросите доступ к автору перед созданием материала.');
        }

        $types = Type::where('status', true)->get();
        $categories = Category::where('status', true)->get();
        $tags = Tag::where('status', true)->get();

        return view('account.materials.create', compact('types', 'categories', 'tags', 'activeAuthors'));
    }

    /**
     * Сохранить новый материал
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Проверяем статус пользователя
        if (!$user->canCreateMaterials()) {
            return redirect()->route('dashboard')
                ->with('error', 'У вас нет прав на создание материалов.');
        }

        // Проверяем, есть ли у пользователя активные авторы
        $activeAuthors = $user->authors()->get();
        if ($activeAuthors->isEmpty()) {
            return back()->withErrors(['authors' => 'Нет активных авторов. Запросите доступ к автору.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:materials'],
            'type_id' => ['required', 'exists:types,id'],
            'content' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['exists:authors,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'mp4' => ['required', 'file', 'mimes:mp3,mp4,m4a', 'max:10240'], // 10MB max
            // SEO поля (опциональные, скрыты под спойлером)
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_h1' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string', 'max:5000'],
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'type_id.required' => 'Поле "Тип" обязательно для заполнения.',
            'authors.required' => 'Необходимо выбрать хотя бы одного автора.',
            'mp4.required' => 'MP4 файл обязателен для загрузки.',
        ]);

        // Проверяем, что выбранные авторы принадлежат пользователю
        $userAuthorIds = $activeAuthors->pluck('id')->toArray();
        $selectedAuthorIds = $validated['authors'];
        if (count(array_diff($selectedAuthorIds, $userAuthorIds)) > 0) {
            return back()->withErrors(['authors' => 'Вы можете выбрать только авторов, к которым у вас есть доступ.'])->withInput();
        }

        // Очищаем и обрабатываем content
        $content = '';
        if (!empty($validated['content'])) {
            $content = wrap_lyrics_content($validated['content']);
        }

        // Определяем SEO поля - если не заполнены, используем название
        $title = !empty($validated['seo_title']) ? $validated['seo_title'] : $validated['name'];
        $description = !empty($validated['seo_description']) ? $validated['seo_description'] : $validated['name'];
        $h1 = !empty($validated['seo_h1']) ? $validated['seo_h1'] : $validated['name'];
        
        // Обрабатываем long_description - очищаем от HTML тегов (кроме <br>) и конвертируем переносы строк в <br>
        $longDescription = $validated['long_description'] ?? '';
        if (!empty($longDescription)) {
            // Сначала преобразуем все <br> и <br/> в \n для нормализации
            $longDescription = preg_replace('/<br\s*\/?>/i', "\n", $longDescription);
            // Декодируем HTML entities
            $longDescription = html_entity_decode($longDescription, ENT_QUOTES, 'UTF-8');
            // Удаляем все HTML теги (теперь их не должно быть, но на всякий случай)
            $longDescription = strip_tags($longDescription);
            // Преобразуем переносы строк \n в <br> теги (один раз)
            $longDescription = str_replace("\n", "<br>", $longDescription);
        }

        try {
            // Обрабатываем MP4 файл
            $mp4File = $request->file('mp4');
            $filename = Str::uuid() . '.' . strtolower($mp4File->getClientOriginalExtension());
            
            // Сохраняем файл
            $mp4File->storeAs('', $filename, 'mp4');
            
            // Анализируем аудио файл после сохранения
            $analyzer = new getID3();
            $filePath = Storage::disk('mp4')->path($filename);
            $audioInfo = $analyzer->analyze($filePath);
            
            $bitrate = isset($audioInfo['bitrate']) ? (int)round($audioInfo['bitrate'] / 1000) : null;
            $duration = isset($audioInfo['playtime_seconds']) ? (int)round($audioInfo['playtime_seconds']) : null;

            // Создаем материал
            $material = Material::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'type_id' => $validated['type_id'],
                'title' => $title,
                'description' => $description,
                'h1' => $h1,
                'long_description' => $longDescription,
                'content' => $content,
                'mp4' => $filename,
                'mp4_bitrate' => $bitrate,
                'mp4_duration' => $duration,
                'moderation_status' => 'pending',
                'status' => null, // При создании статус null
                'views' => 0,
                'likes' => 0,
                'downloads' => 0,
            ]);

            // Привязываем авторов
            $material->authors()->sync($validated['authors']);

            // Привязываем категории, если указаны
            if (!empty($validated['categories'])) {
                $material->categories()->sync($validated['categories']);
            }

            // Привязываем теги, если указаны
            if (!empty($validated['tags'])) {
                $material->tags()->sync($validated['tags']);
            }

            return redirect()->route('account.materials.index')
                ->with('success', 'Материал создан и отправлен на модерацию.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ошибки валидации уже обрабатываются автоматически
            throw $e;
        } catch (\Exception $e) {
            // Логируем ошибку с деталями
            Log::error('Ошибка при создании материала', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['mp4', 'password']), // Не логируем файлы и пароли
            ]);
            
            // Возвращаем пользователю понятное сообщение об ошибке
            return back()
                ->withInput($request->except('mp4'))
                ->withErrors(['error' => 'Произошла ошибка при создании материала. Пожалуйста, попробуйте еще раз или обратитесь к администратору.']);
        }
    }

    /**
     * Показать форму редактирования материала
     */
    public function edit(Material $material)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if ($material->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этому материалу.');
        }

        // Проверяем, может ли пользователь редактировать материалы
        if (!$user->canCreateMaterials()) {
            return redirect()->route('account.materials.index')
                ->with('error', 'У вас нет прав на редактирование материалов.');
        }

        // Пользователь может редактировать материалы любого статуса модерации
        // После редактирования материал снова отправляется на модерацию

        $types = Type::where('status', true)->get();
        $categories = Category::where('status', true)->get();
        $tags = Tag::where('status', true)->get();
        $activeAuthors = $user->authors()->get();

        // Разворачиваем content для редактирования
        $material->content = unwrap_lyrics_content($material->content ?? '');
        
        // Преобразуем <br> в long_description обратно в \n для textarea
        if ($material->long_description) {
            $material->long_description = preg_replace('/<br\s*\/?>/i', "\n", $material->long_description);
            $material->long_description = html_entity_decode($material->long_description, ENT_QUOTES, 'UTF-8');
        }

        return view('account.materials.edit', compact('material', 'types', 'categories', 'tags', 'activeAuthors'));
    }

    /**
     * Обновить материал
     */
    public function update(Request $request, Material $material)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if ($material->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этому материалу.');
        }

        // Проверяем, может ли пользователь редактировать материалы
        if (!$user->canCreateMaterials()) {
            return redirect()->route('account.materials.index')
                ->with('error', 'У вас нет прав на редактирование материалов.');
        }

        $activeAuthors = $user->authors()->get();
        if ($activeAuthors->isEmpty()) {
            return back()->withErrors(['authors' => 'Нет активных авторов.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('materials', 'name')->ignore($material->id, 'id')],
            'type_id' => ['required', 'exists:types,id'],
            'content' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['exists:authors,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'mp4' => ['nullable', 'file', 'mimes:mp3,mp4,m4a', 'max:10240'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_h1' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string', 'max:5000'],
        ]);

        // Проверяем авторов
        $userAuthorIds = $activeAuthors->pluck('id')->toArray();
        $selectedAuthorIds = $validated['authors'];
        if (count(array_diff($selectedAuthorIds, $userAuthorIds)) > 0) {
            return back()->withErrors(['authors' => 'Вы можете выбрать только авторов, к которым у вас есть доступ.'])->withInput();
        }

        // Обрабатываем content - очищаем и оборачиваем
        $content = '';
        if (!empty($validated['content'])) {
            $content = wrap_lyrics_content($validated['content']);
        }

        // SEO поля - если не заполнены, используем название
        $title = !empty($validated['seo_title']) ? $validated['seo_title'] : $material->name;
        $description = !empty($validated['seo_description']) ? $validated['seo_description'] : $material->name;
        $h1 = !empty($validated['seo_h1']) ? $validated['seo_h1'] : $material->name;

        // Обрабатываем long_description - очищаем от HTML тегов (кроме <br>) и конвертируем переносы строк в <br>
        $longDescription = $validated['long_description'] ?? '';
        if (!empty($longDescription)) {
            // Сначала преобразуем все <br> и <br/> в \n для нормализации
            $longDescription = preg_replace('/<br\s*\/?>/i', "\n", $longDescription);
            // Декодируем HTML entities
            $longDescription = html_entity_decode($longDescription, ENT_QUOTES, 'UTF-8');
            // Удаляем все HTML теги (теперь их не должно быть, но на всякий случай)
            $longDescription = strip_tags($longDescription);
            // Преобразуем переносы строк \n в <br> теги (один раз)
            $longDescription = str_replace("\n", "<br>", $longDescription);
        }

        // Обновляем материал (name не обновляем, оно readonly)
        $material->update([
            'type_id' => $validated['type_id'],
            'title' => $title,
            'description' => $description,
            'h1' => $h1,
            'long_description' => $longDescription,
            'content' => $content,
            'moderation_status' => 'pending', // При редактировании снова отправляем на модерацию
        ]);

        // Обрабатываем MP4 файл, если загружен новый
        if ($request->hasFile('mp4')) {
            // Удаляем старый файл
            if ($material->mp4 && Storage::disk('mp4')->exists($material->mp4)) {
                Storage::disk('mp4')->delete($material->mp4);
            }

            $mp4File = $request->file('mp4');
            $filename = Str::uuid() . '.' . strtolower($mp4File->getClientOriginalExtension());
            $mp4File->storeAs('', $filename, 'mp4');

            // Анализируем аудио после сохранения
            $analyzer = new getID3();
            $filePath = Storage::disk('mp4')->path($filename);
            $audioInfo = $analyzer->analyze($filePath);
            
            $bitrate = isset($audioInfo['bitrate']) ? (int)round($audioInfo['bitrate'] / 1000) : null;
            $duration = isset($audioInfo['playtime_seconds']) ? (int)round($audioInfo['playtime_seconds']) : null;

            $material->update([
                'mp4' => $filename,
                'mp4_bitrate' => $bitrate,
                'mp4_duration' => $duration,
            ]);
        }

        // Обновляем связи
        $material->authors()->sync($validated['authors']);
        if (!empty($validated['categories'])) {
            $material->categories()->sync($validated['categories']);
        } else {
            $material->categories()->detach();
        }
        if (!empty($validated['tags'])) {
            $material->tags()->sync($validated['tags']);
        } else {
            $material->tags()->detach();
        }

        return redirect()->route('account.materials.index')
            ->with('success', 'Материал обновлен и отправлен на модерацию.');
    }

    /**
     * Удалить материал
     */
    public function destroy(Material $material)
    {
        $user = Auth::user();

        // Проверяем права доступа
        if ($material->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этому материалу.');
        }

        // Пользователь может удалять только свои материалы (любого статуса)

        // Удаляем MP4 файл
        if ($material->mp4 && Storage::disk('mp4')->exists($material->mp4)) {
            Storage::disk('mp4')->delete($material->mp4);
        }

        $material->delete();

        return redirect()->route('account.materials.index')
            ->with('success', 'Материал удален.');
    }
}
