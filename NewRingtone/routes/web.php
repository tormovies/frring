<?php

use App\Http\Controllers\Account\AuthorController as AccountAuthorController;
use App\Http\Controllers\Account\MaterialController as AccountMaterialController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TypeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

// Sitemap и robots.txt для поисковиков
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $sitemapUrl = url('/sitemap.xml');
    return response("User-agent: *\nDisallow:\n\nSitemap: {$sitemapUrl}\n", 200, [
        'Content-Type' => 'text/plain',
        'Charset' => 'UTF-8',
    ]);
})->name('robots');

// Главная (без пагинации) и новинки по offset: /index-24-date.html, /index-48-date.html
Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('/index-0-date.html', fn () => redirect()->route('home', [], 301));
Route::get('/index-{offset}-date.html', [MainController::class, 'indexWithOffset'])->name('home.offset')->where('offset', '[0-9]+');

// URL как на старом сайте (SEO): /play/{slug}.html, /category/{slug}.html, /page/{slug}.html
// Рингтоны (материалы) — старый формат /play/alias.html
Route::get('/play/{slug}.html', [MaterialController::class, 'show'])->name('materials.show')->where('slug', '[^/]+');
Route::get('/play/{slug}/download/{format}', [MaterialController::class, 'download'])->name('materials.download')->where('slug', '[^/]+');
Route::post('/play/{slug}/like', [MaterialController::class, 'like'])->name('materials.like')->where('slug', '[^/]+');
Route::post('/play/{slug}/dislike', [MaterialController::class, 'dislike'])->name('materials.dislike')->where('slug', '[^/]+');

// Категории — URL с offset в пути: index-0-plays.html, index-24-plays.html, na-vraga-48-plays.html (24 на страницу)
Route::get('/category/index-{offset}-plays.html', [MaterialController::class, 'popular'])->name('materials.popular')->where('offset', '[0-9]+');
Route::get('/category/index-{offset}-rating.html', [MaterialController::class, 'best'])->name('materials.best')->where('offset', '[0-9]+');
Route::get('/category/{path}.html', [CategoryController::class, 'show'])->name('categories.show')->where('path', '[^/]+');

// Теги — URL с offset: tag/slug.html, tag/slug-24-plays.html и т.д.
Route::get('/tag/{path}.html', [TagController::class, 'show'])->name('tags.show')->where('path', '[^/]+');

// Типы, авторы — с .html
Route::get('/type/{slug}.html', [TypeController::class, 'show'])->name('types.show')->where('slug', '[^/]+');
Route::get('/author/{slug}.html', [AuthorController::class, 'show'])->name('authors.show')->where('slug', '[^/]+');

// Статьи (если были на старом — подстроить при необходимости)
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/article/{slug}.html', [ArticleController::class, 'show'])->name('articles.show')->where('slug', '[^/]+');
Route::post('/article/{slug}/like', [ArticleController::class, 'like'])->name('articles.like')->where('slug', '[^/]+');
Route::post('/article/{slug}/dislike', [ArticleController::class, 'dislike'])->name('articles.dislike')->where('slug', '[^/]+');

// Страницы — старый формат /page/alias.html
Route::get('/page/{slug}.html', [PageController::class, 'show'])->name('pages.show')->where('slug', '[^/]+');

// Поиск — как на старом: search.php?query=...
Route::get('/search.php', [SearchController::class, 'index'])->name('search');
Route::match(['get', 'post'], '/search', [SearchController::class, 'index']);

// Редиректы со старых URL нового сайта на формат как на старом (301 для SEO)
Route::get('/materials/popular', fn () => redirect()->route('materials.popular', [], 301));
Route::get('/materials/best', fn () => redirect()->route('materials.best', [], 301));
Route::get('/materials/{slug}', fn (string $slug) => redirect()->route('materials.show', $slug, 301))->where('slug', '[^/]+');
Route::get('/category/{path}', fn (string $path) => redirect()->route('categories.show', ['path' => $path], 301))->where('path', '[^/]+');
Route::get('/page/{slug}', fn (string $slug) => redirect()->route('pages.show', $slug, 301))->where('slug', '[^/]+');
Route::get('/tag/{path}', fn (string $path) => redirect()->route('tags.show', ['path' => $path], 301))->where('path', '[^/]+');
Route::get('/type/{slug}', fn (string $slug) => redirect()->route('types.show', $slug, 301))->where('slug', '[^/]+');
Route::get('/author/{slug}', fn (string $slug) => redirect()->route('authors.show', $slug, 301))->where('slug', '[^/]+');
Route::get('/article/{slug}', fn (string $slug) => redirect()->route('articles.show', $slug, 301))->where('slug', '[^/]+');

// Аутентификация
Route::get('/register/{secretKey}', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register/{secretKey}', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Подтверждение email
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    
    // После подтверждения email создаем запрос на автора, если он был создан при регистрации
    $user = $request->user();
    if (isset($user->status) && $user->status === 'not_verified') {
        $user->update(['status' => 'email_verified']);
        
        // Если есть запрос на автора при регистрации, он уже был создан
        // Теперь админ видит его в админке
    }
    
    return redirect()->route('dashboard')->with('success', 'Email подтвержден!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Ссылка для подтверждения отправлена!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Восстановление пароля
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => \Illuminate\Support\Facades\Hash::make($password)
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('success', 'Пароль успешно изменен. Вы можете войти в систему.')
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

// Личный кабинет (требуется авторизация и подтверждение email)
// Админы перенаправляются на панель админки
Route::middleware(['auth', 'verified', 'redirect.admin'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware(['auth', 'verified', 'redirect.admin'])->prefix('account')->name('account.')->group(function () {
    // Материалы
    Route::get('/materials', [AccountMaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [AccountMaterialController::class, 'create'])->name('materials.create');
    Route::post('/materials', [AccountMaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/edit', [AccountMaterialController::class, 'edit'])->name('materials.edit');
    Route::post('/materials/{material}', [AccountMaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [AccountMaterialController::class, 'destroy'])->name('materials.destroy');
    
    // Авторы
    Route::get('/authors', [AccountAuthorController::class, 'index'])->name('authors.index');
    Route::post('/authors/request', [AccountAuthorController::class, 'requestAuthor'])->name('authors.request');
    Route::get('/authors/{author}/edit', [AccountAuthorController::class, 'edit'])->name('authors.edit');
    Route::post('/authors/{author}', [AccountAuthorController::class, 'update'])->name('authors.update');
    Route::post('/authors/{author}/detach', [AccountAuthorController::class, 'detach'])->name('authors.detach');
});
