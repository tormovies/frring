<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Здесь задаются title, description и H1 для общих разделов сайта (главная, поиск, популярные, лучшие, статьи).
        Подстановки: <code>%year%</code>, <code>%page%</code> (страница пагинации), для поиска — <code>%query%</code>.
    </p>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end mt-6">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
