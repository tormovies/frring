@if($imagePath)
    @php
        $imageUrl = \Illuminate\Support\Facades\Storage::disk('authors')->url($imagePath);
    @endphp
    <div style="text-align: center; padding: 1rem; background: var(--bg-primary, #f5f5f5); border-radius: 8px; border: 1px solid var(--border-color, #ddd);">
        <img src="{{ $imageUrl }}" alt="{{ $label }}" 
             style="max-width: 100%; max-height: 400px; border-radius: 8px; margin-bottom: 0.5rem;">
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-tertiary, #666); word-break: break-all;">
            {{ $imagePath }}
        </p>
    </div>
@else
    <div style="text-align: center; padding: 2rem; color: var(--text-tertiary, #666);">
        Изображение отсутствует
    </div>
@endif
