{{-- Общий список рингтонов в стиле старого сайта (главная, категория, поиск, популярные, лучшие) --}}
<div id="player">
    <audio src="" preload="none"></audio>
</div>

@foreach($materials as $material)
    @php
        $durationStr = $material->mp4_duration ? gmdate('i:s', $material->mp4_duration) : '0:00';
        $audioUrl = $material->hasFile() ? $material->fileUrl() : '';
    @endphp
    <div class="col-xl-6">
        <div id="song_{{ $material->id }}" audio_url="{{ $audioUrl }}" class="aduio_player" data-material-id="{{ $material->id }}">
            <div class="play_l_btn">
                @if($material->hasFile())
                    <button type="button" class="play_audio" aria-label="Play/Pause"><i class="far fa-play-circle"></i><i class="far fa-pause-circle"></i></button>
                @else
                    <button type="button" class="play_audio disabled" disabled aria-label="Нет файла"><i class="far fa-play-circle"></i><i class="far fa-pause-circle"></i></button>
                @endif
            </div>
            <div class="info_to_range">
                <a href="{{ route('materials.show', $material->slug) }}" class="name">{{ $material->name }}</a>
                <span class="time"><i class="far fa-clock"></i> <time>{{ $durationStr }}</time></span>
                <span class="dwnld"><i class="fas fa-download"></i> {{ number_format($material->downloads ?? 0) }}</span>
            </div>
            <div class="like-container">
                <button type="button" class="like-btn {{ session()->has('liked_'.$material->id) ? 'liked' : '' }}"
                   data-like-url="{{ route('materials.like', $material->slug) }}"
                   data-dislike-url="{{ route('materials.dislike', $material->slug) }}"
                   title="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Нравится' }}"
                   aria-label="{{ session()->has('liked_'.$material->id) ? 'Убрать лайк' : 'Нравится' }}">{{ session()->has('liked_'.$material->id) ? '♥' : '♡' }}</button>
                <span class="like-count" id="vcount_{{ $material->id }}">{{ $material->likes ?? 0 }}</span>
            </div>
        </div>
    </div>
@endforeach
