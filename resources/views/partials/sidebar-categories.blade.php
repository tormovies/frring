{{--
  Правый блок категорий — как на старом сайте (aside_right, leftcats.tpl).
  Обновить: php artisan sidebar:sync-from-old
--}}
<div class="aside_right popup" id="category">
    <nav class="nav_aside">
        @include('partials.category-links')
    </nav>
</div>