<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Здесь задаются title, description и H1 для общих разделов сайта (главная, поиск, популярные, лучшие, статьи).
        Подстановки: <code>%year%</code>, <code>%page%</code> (страница пагинации), для поиска — <code>%query%</code>.
    </p>
    <form wire:submit="save">
        <div class="mb-6">{{ $this->form }}</div>

        <div class="flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700" style="margin-top: 2rem; padding-top: 2rem;">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
