<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🔗 Ссылка на регистрацию
        </x-slot>
        
        <x-slot name="description">
            Секретная ссылка для регистрации новых пользователей
        </x-slot>
        
        <div class="space-y-4">
            @php
                $secretKey = config('app.registration_secret_key', 'ksd2528');
                $registrationUrl = route('register', $secretKey);
            @endphp
            
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                            URL регистрации:
                        </label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                value="{{ $registrationUrl }}" 
                                readonly 
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-mono text-sm"
                                id="registration-url"
                            >
                            <button 
                                type="button"
                                onclick="copyToClipboard(event)"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium transition-colors"
                            >
                                📋 Копировать
                            </button>
                            <a 
                                href="{{ $registrationUrl }}" 
                                target="_blank"
                                class="px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-md text-sm font-medium transition-colors no-underline"
                            >
                                🔗 Открыть
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <strong>Секретный ключ:</strong> <code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">{{ $secretKey }}</code>
            </div>
        </div>
    </x-filament::section>
    
    <script>
        function copyToClipboard(event) {
            const input = document.getElementById('registration-url');
            input.select();
            input.setSelectionRange(0, 99999); // Для мобильных устройств
            
            try {
                navigator.clipboard.writeText(input.value).then(function() {
                    // Показываем уведомление
                    const button = event.target;
                    const originalText = button.textContent;
                    button.textContent = '✓ Скопировано!';
                    button.classList.add('bg-success-600');
                    button.classList.remove('bg-primary-600', 'hover:bg-primary-700');
                    button.classList.add('hover:bg-success-700');
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove('bg-success-600', 'hover:bg-success-700');
                        button.classList.add('bg-primary-600', 'hover:bg-primary-700');
                    }, 2000);
                }).catch(function() {
                    // Fallback для старых браузеров
                    document.execCommand('copy');
                    alert('Ссылка скопирована в буфер обмена!');
                });
            } catch (err) {
                alert('Не удалось скопировать. Пожалуйста, скопируйте вручную.');
            }
        }
    </script>
</x-filament-widgets::widget>
