@php
    $uniqueId = $uniqueId ?? 'quill-' . uniqid();
    $contentHtml = $content ?? '';
@endphp

<div class="quill-content-preview-{{ $uniqueId }}" data-content="{{ htmlspecialchars($contentHtml, ENT_QUOTES, 'UTF-8') }}" data-initialized="false" style="position: relative;">
    <div class="quill-editor-{{ $uniqueId }}" style="min-height: 200px; border: 1px solid #e5e7eb; border-radius: 6px; background: #ffffff;"></div>
</div>

<link href="/js/quill/quill.snow.css" rel="stylesheet">
<script>
(function() {
    var uniqueId = '{{ $uniqueId }}';
    var wrapperSelector = '.quill-content-preview-{{ $uniqueId }}';
    var editorSelector = '.quill-editor-{{ $uniqueId }}';
    
    function initQuillPreview() {
        if (typeof Quill === 'undefined') {
            setTimeout(initQuillPreview, 100);
            return;
        }
        
        var wrapper = document.querySelector(wrapperSelector);
        if (!wrapper || wrapper.dataset.initialized === 'true') return;
        wrapper.dataset.initialized = 'true';
        
        var editorDiv = wrapper.querySelector(editorSelector);
        if (!editorDiv) return;
        
        var contentHtml = wrapper.getAttribute('data-content') || '';
        
        // Инициализируем Quill в режиме только для чтения
        var quill = new Quill(editorDiv, {
            theme: 'snow',
            modules: {
                toolbar: false
            },
            readOnly: true
        });
        
        // Скрываем toolbar если появится
        setTimeout(function() {
            var toolbar = wrapper.querySelector('.ql-toolbar');
            if (toolbar) {
                toolbar.style.display = 'none';
            }
        }, 100);
        
        // Декодируем HTML entities
        if (contentHtml) {
            var decodedContent = contentHtml
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&quot;/g, '"')
                .replace(/&#039;/g, "'")
                .replace(/&amp;/g, '&');
            quill.root.innerHTML = decodedContent;
        }
    }
    
    // Ждем загрузки Quill.js
    if (typeof Quill !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQuillPreview);
        } else {
            setTimeout(initQuillPreview, 100);
        }
    } else {
        // Если Quill еще не загружен, ждем его загрузки
        var script = document.createElement('script');
        script.src = '/js/quill/quill.js';
        script.onload = function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initQuillPreview);
            } else {
                setTimeout(initQuillPreview, 100);
            }
        };
        document.head.appendChild(script);
    }
})();
</script>
<style>
.quill-content-preview-{{ $uniqueId }} .ql-toolbar {
    display: none !important;
}
.quill-content-preview-{{ $uniqueId }} .ql-container {
    font-family: inherit;
    font-size: inherit;
    border: none;
}
.quill-content-preview-{{ $uniqueId }} .ql-editor {
    padding: 12px 15px;
    min-height: 150px;
}
.quill-content-preview-{{ $uniqueId }} .ql-editor.ql-disabled {
    color: inherit;
}
</style>
