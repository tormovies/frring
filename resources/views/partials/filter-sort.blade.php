{{--
  Блок ссылок «Новинки / Популярные / Лучшие» — одинаковый вид везде.
  На главной и на первой странице категории/тега (Новинки) — не показываем ссылку «Новинки».
  На страницах Популярные — ссылки: Новинки, Лучшие.
  На страницах Лучшие — ссылки: Новинки, Популярные.

  @param string $context 'main' | 'category' | 'tag'
  @param string $currentSort 'new' | 'plays' | 'rating'
  @param string|null $slug slug категории или тега (для category/tag)
--}}
<div class="col-12 filter">
    <span>Смотрите также</span>
    @if($currentSort !== 'new')
        <a href="{{ sort_filter_url($context, 'new', $slug) }}" class="{{ $currentSort === 'new' ? 'selected' : '' }}">Новинки</a>
    @endif
    @if($currentSort !== 'plays')
        <a href="{{ sort_filter_url($context, 'plays', $slug) }}" class="{{ $currentSort === 'plays' ? 'selected' : '' }}">Популярные</a>
    @endif
    @if($currentSort !== 'rating')
        <a href="{{ sort_filter_url($context, 'rating', $slug) }}" class="{{ $currentSort === 'rating' ? 'selected' : '' }}">Лучшие</a>
    @endif
</div>
