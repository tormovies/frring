<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\AuthorRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegistrationForm($secretKey)
    {
        // Проверяем секретный ключ
        if ($secretKey !== config('app.registration_secret_key')) {
            abort(404);
        }

        return view('auth.register', ['secretKey' => $secretKey]);
    }

    /**
     * Обработать регистрацию
     */
    public function register(Request $request, $secretKey)
    {
        // Проверяем секретный ключ
        if ($secretKey !== config('app.registration_secret_key')) {
            abort(404);
        }

        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'author_name' => ['required', 'string', 'max:255'],
            'author_card_url' => ['required', 'url', 'max:500'],
        ], [
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'name.string' => 'Поле "Имя" должно быть строкой.',
            'name.max' => 'Поле "Имя" не может содержать более 255 символов.',
            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Поле "Email" должно быть валидным email адресом.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'email.max' => 'Поле "Email" не может содержать более 255 символов.',
            'password.required' => 'Поле "Пароль" обязательно для заполнения.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
            'author_name.required' => 'Поле "Имя Автора" обязательно для заполнения.',
            'author_name.string' => 'Поле "Имя Автора" должно быть строкой.',
            'author_name.max' => 'Поле "Имя Автора" не может содержать более 255 символов.',
            'author_card_url.required' => 'Поле "Ссылка на карточку артиста" обязательно для заполнения.',
            'author_card_url.url' => 'Поле "Ссылка на карточку артиста" должно быть валидным URL.',
            'author_card_url.max' => 'Поле "Ссылка на карточку артиста" не может содержать более 500 символов.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Проверяем, существует ли автор и привязан ли он к другому пользователю
        $existingAuthor = Author::where('name', $request->author_name)->first();
        if ($existingAuthor) {
            // Проверяем, привязан ли автор к пользователю
            if ($existingAuthor->users()->exists()) {
                return back()->withErrors([
                    'author_name' => 'Автор "' . $request->author_name . '" уже привязан к другому пользователю. Пожалуйста, обратитесь к администратору: ' . route('authors.show', ['slug' => 'neurozvuk']),
                ])->withInput();
            }

            // Проверяем, есть ли уже запрос на этого автора
            $pendingRequest = AuthorRequest::where('author_id', $existingAuthor->id)
                ->where('status', 'pending')
                ->exists();

            if ($pendingRequest) {
                return back()->withErrors([
                    'author_name' => 'На этого автора уже подана заявка, которая находится на модерации. Пожалуйста, подождите или обратитесь к администратору.',
                ])->withInput();
            }
        }

        // Создаем пользователя
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'not_verified',
        ]);

        // Создаем запрос на автора
        $authorRequest = AuthorRequest::create([
            'user_id' => $user->id,
            'author_name' => $request->author_name,
            'author_card_url' => $request->author_card_url,
            'author_id' => $existingAuthor?->id,
            'status' => 'pending',
        ]);

        // Отправляем email для подтверждения
        event(new Registered($user));

        return redirect()->route('verification.notice')
            ->with('status', 'Регистрация успешна! Пожалуйста, проверьте вашу почту для подтверждения email.');
    }

    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        // Если пользователь уже авторизован, редиректим его
        if (Auth::check()) {
            $user = Auth::user();
            
            // Проверяем, является ли пользователь админом
            try {
                $roles = $user->getRoleNames();
                $hasAdminRole = false;
                
                if (!$roles->isEmpty()) {
                    foreach ($roles as $role) {
                        $roleName = trim((string)$role);
                        if ($roleName === 'Admin' || $roleName === 'admin' || $roleName === 'super_admin') {
                            $hasAdminRole = true;
                            break;
                        }
                    }
                }
                
                // Если админ - на админку, иначе на dashboard
                if ($hasAdminRole) {
                    return redirect('/admin');
                }
            } catch (\Exception $e) {
                // В случае ошибки - на dashboard
            }
            
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Обработать вход
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        // Проверяем статус пользователя (если колонка существует)
        if (isset($user->status) && $user->status === 'blocked') {
            return back()->withErrors([
                'email' => 'Ваш аккаунт заблокирован. Пожалуйста, обратитесь к администратору.',
            ]);
        }

        // Проверяем подтверждение email
        if (!$user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Пожалуйста, подтвердите ваш email перед входом.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Проверяем, является ли пользователь админом
        // Если админ - редиректим на админку, иначе на dashboard
        try {
            $roles = $user->getRoleNames();
            $hasAdminRole = false;
            
            if (!$roles->isEmpty()) {
                foreach ($roles as $role) {
                    $roleName = trim((string)$role);
                    if ($roleName === 'Admin' || $roleName === 'admin' || $roleName === 'super_admin') {
                        $hasAdminRole = true;
                        break;
                    }
                }
            }
            
            // Если админ - на админку, иначе на dashboard
            if ($hasAdminRole) {
                return redirect('/admin');
            }
        } catch (\Exception $e) {
            // В случае ошибки - на dashboard
        }
        
        // Обычный пользователь - на dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Выход
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
