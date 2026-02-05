<script>
// Custom scripts loaded

// Глобальная функция для добавления пункта в меню "Вставить"
window.addLyricsMenuItem = function(editorInstance) {
    // Защита от множественных вызовов
    if (window._lyricsMenuItemAdding) {
        return false;
    }
    
    const menu = document.querySelector('.tox-menu.tox-collection');
    const alternativeMenu = document.querySelector('.tox-pop.tox-pop--bottom.tox-pop--right'); // Альтернативный селектор для меню
    
    let activeMenu = null;
    if (menu && menu.offsetParent !== null) { // Check if menu is visible
        activeMenu = menu;
    } else if (alternativeMenu && alternativeMenu.offsetParent !== null) { // Check if alternative menu is visible
        activeMenu = alternativeMenu;
    } else {
        window._lyricsMenuItemAdding = false;
        return false;
    }
    
    if (activeMenu.querySelector('[data-lyrics-menu-item]')) {
        window._lyricsMenuItemAdding = false;
        return true; // Уже добавлен
    }
    
    // Устанавливаем флаг, чтобы предотвратить параллельные вызовы
    window._lyricsMenuItemAdding = true;
    
    const menuText = activeMenu.textContent || '';
    
    // Проверяем, это меню "Вставить" - ищем характерные пункты
    const hasInsertItems = menuText.includes('Изображение') ||
                           menuText.includes('Ссылка') ||
                           menuText.includes('Image') ||
                           menuText.includes('Link') ||
                           menuText.includes('изображен') ||
                           menuText.includes('ссылка') ||
                           menuText.includes('таблиц') ||
                           menuText.includes('эмодзи') ||
                           menuText.includes('emoticon') ||
                           menuText.includes('Insert'); // Добавил "Insert" для более точного определения
    
    if (!hasInsertItems) {
        window._lyricsMenuItemAdding = false;
        return false;
    }
    
    // Ищем место для вставки - после "inserttable" или перед разделителем
    let targetElement = null;
    const allItems = Array.from(activeMenu.querySelectorAll('.tox-collection__item'));
    
    // Ищем пункт "Дата/время"
    let dateTimeItem = null;
    for (let i = 0; i < allItems.length; i++) {
        const item = allItems[i];
        const label = item.querySelector('.tox-collection__item-label');
        if (label) {
            const text = label.textContent.trim();
            if (text.includes('дата') || text.includes('время') || text.includes('date') || text.includes('time') || text.includes('Date') || text.includes('Time')) {
                dateTimeItem = item;
                break;
            }
        }
    }
    
    if (dateTimeItem) {
        targetElement = dateTimeItem;
    } else {
        // Если "Дата/время" не найдено, ищем "Таблица"
        let tableItem = null;
        for (let i = 0; i < allItems.length; i++) {
            const item = allItems[i];
            const label = item.querySelector('.tox-collection__item-label');
            if (label) {
                const text = label.textContent.trim();
                if (text.includes('Таблица') || text.includes('Table')) {
                    tableItem = item;
                    break;
                }
            }
        }
        if (tableItem) {
            targetElement = tableItem.nextElementSibling; // Вставляем после таблицы
        } else {
            // Если ничего не найдено, вставляем в конец
            targetElement = null;
        }
    }
    
    // Создаем новый пункт меню
    const newItem = document.createElement('div');
    newItem.className = 'tox-collection__item tox-collection__item--enabled';
    newItem.setAttribute('role', 'menuitem');
    newItem.setAttribute('tabindex', '-1');
    newItem.setAttribute('data-lyrics-menu-item', 'true'); // Для отслеживания
    
    newItem.innerHTML = `
        <div class="tox-collection__item-icon"></div>
        <div class="tox-collection__item-label">Текст песни</div>
        <div class="tox-collection__item-accessory"></div>
        <div class="tox-collection__item-checkmark"></div>
    `;
    newItem.style.cssText = 'cursor: pointer;'; // Делаем курсор активным
    newItem.addEventListener('mouseenter', function() {
        this.classList.add('tox-collection__item--active');
    });
    newItem.addEventListener('mouseleave', function() {
        this.classList.remove('tox-collection__item--active');
    });
    
    // Обработчик клика
    newItem.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (editorInstance) {
            editorInstance.execCommand('insertLyrics');
            // Закрываем меню
            const menuParent = newItem.closest('.tox-menu');
            if (menuParent) {
                const editorContainer = editorInstance.getContainer();
                const menuButton = editorContainer.querySelector('.tox-mbtn[aria-label="Insert"]');
                if (menuButton) {
                    menuButton.focus(); // Фокусируем кнопку меню, чтобы закрыть его
                    menuButton.click(); // Имитируем клик для закрытия
                } else {
                    // Если кнопка не найдена, пробуем закрыть через ESC
                    editorInstance.execCommand('mceCancel');
                }
            }
        }
    }, true); // Используем capture phase
    
    if (targetElement && targetElement.parentNode === activeMenu) {
        activeMenu.insertBefore(newItem, targetElement);
    } else if (targetElement && targetElement.parentNode && targetElement.parentNode.parentNode === activeMenu) {
        // Если targetElement - это элемент внутри группы, вставляем перед группой
        activeMenu.insertBefore(newItem, targetElement.parentNode);
    }
    else {
        activeMenu.appendChild(newItem);
    }
    
    // Сбрасываем флаг после успешного добавления
    window._lyricsMenuItemAdding = false;
    return true;
};

// Отслеживаем появление редакторов и добавляем функционал
function processEditor(editor) {
    if (editor._lyricsInitialized) return;
    editor._lyricsInitialized = true;
    
    // Очищаем предыдущие интервалы, если они есть
    if (editor._lyricsMenuInterval) {
        clearInterval(editor._lyricsMenuInterval);
    }
    
    // Регистрируем команду
    editor.addCommand('insertLyrics', function() {
        // Простая вставка блока для текста песни
        editor.insertContent('<div class="lyrics-content">Здесь вставьте текст песни</div>');
    });
    
    // Отслеживаем появление меню через MutationObserver с debounce
    let debounceTimeout = null;
    const observer = new MutationObserver(function(mutations) {
        // Debounce: ждем 100мс после последнего изменения
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(function() {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.classList.contains('tox-menu') || node.classList.contains('tox-pop'))) {
                            // Меню появилось, пытаемся добавить пункт
                            window.addLyricsMenuItem(editor);
                        }
                    });
                }
            });
        }, 100);
    });
    
    // Наблюдаем за изменениями в document.body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Также вызываем периодически, чтобы поймать меню, если observer пропустит
    // Но с ограничением попыток
    let attempts = 0;
    const maxAttempts = 50; // Максимум 10 секунд (50 * 200мс)
    let intervalId = setInterval(() => {
        attempts++;
        if (window.addLyricsMenuItem(editor)) {
            clearInterval(intervalId); // Останавливаем, если успешно добавлено
            editor._lyricsMenuIntervalCleared = true;
        } else if (attempts >= maxAttempts) {
            clearInterval(intervalId); // Останавливаем после максимального количества попыток
            editor._lyricsMenuIntervalCleared = true;
        }
    }, 200); // Каждые 200 мс
    
    // Сохраняем ссылку на интервал для возможной очистки
    editor._lyricsMenuInterval = intervalId;
}

// Функция для обработки всех редакторов
function processAllEditors() {
    if (typeof tinymce === 'undefined' || !tinymce.editors) {
        return;
    }
    
    tinymce.editors.forEach(editor => {
        if (editor.initialized) {
            processEditor(editor);
        }
    });
}

// Ожидаем загрузки TinyMCE и подписываемся на события
function initializeLyricsPlugin() {
    if (typeof tinymce === 'undefined') {
        setTimeout(initializeLyricsPlugin, 100);
        return;
    }
    
    // Подписываемся на глобальное событие добавления редактора
    tinymce.on('AddEditor', function(e) {
        const editor = e.editor;
        
        // Ждём полной инициализации редактора
        editor.on('init', function() {
            processEditor(editor);
        });
    });
    
    // Также обрабатываем уже существующие редакторы
    processAllEditors();
    
    // Периодическая проверка для динамически созданных редакторов
    // Но только если еще не запущен
    if (!window._lyricsGlobalInterval) {
        window._lyricsGlobalInterval = setInterval(function() {
            // Проверяем только неинициализированные редакторы
            if (typeof tinymce !== 'undefined' && tinymce.editors) {
                tinymce.editors.forEach(editor => {
                    if (editor.initialized && !editor._lyricsInitialized) {
                        processEditor(editor);
                    }
                });
            }
        }, 2000); // Каждые 2 секунды
    }
}

// Запускаем инициализацию
initializeLyricsPlugin();

// Слушаем события Livewire для динамически создаваемых редакторов
document.addEventListener('livewire:initialized', () => {
    setTimeout(processAllEditors, 500);
    
    Livewire.hook('morph.added', ({ el }) => {
        if (el.querySelector('.tinyeditor')) {
            setTimeout(processAllEditors, 100);
        }
    });
});

document.addEventListener('livewire:navigated', function() {
    setTimeout(processAllEditors, 500);
});
</script>
