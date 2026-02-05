/* Old-style player: one global audio, play/pause per row. No jQuery .on() (SES-safe). */
var current_song = 0;
var range_manipulations = false;
var last_song = 0;
var mouse_down = false;

function convert_to_timecode(secs) {
    if (isNaN(secs) || secs < 0) return '0:00';
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
    if (seconds < 10) seconds = "0" + seconds;
    return minutes + ":" + seconds;
}

function handlePlayLBtnClick(e) {
    e.preventDefault();
    var main = this.closest && this.closest('.aduio_player');
    if (!main) return;
    var audioUrl = main.getAttribute('audio_url');
    var playBtn = main.querySelector('.play_l_btn button');
    if (!audioUrl || !playBtn || playBtn.disabled || playBtn.classList.contains('disabled')) return;

    var audioEl = document.querySelector('#player audio');
    if (!audioEl) return;

    var range = main.querySelector('.slider-range-max');
    var timecode = main.querySelector('time');
    var mainId = main.getAttribute('id') || '';

    current_song = mainId;

    if (last_song !== mainId) {
        var prevMain = last_song ? document.getElementById(last_song) : null;
        if (prevMain) {
            var prevTime = prevMain.querySelector('time');
            var saved = prevMain.getAttribute('data-initial-time');
            if (prevTime && saved !== null) prevTime.textContent = saved;
        }
        var allPause = document.querySelectorAll('.aduio_player .play_l_btn button.pause_audio');
        for (var p = 0; p < allPause.length; p++) allPause[p].classList.remove('pause_audio');
        if (timecode && !main.getAttribute('data-initial-time')) main.setAttribute('data-initial-time', timecode.textContent || '0:00');
        if (range) {
            var rangeFill = range.querySelector('.ui-slider-range');
            var rangeHandle = range.querySelector('.ui-slider-handle');
            if (rangeFill) rangeFill.removeAttribute('style');
            if (rangeHandle) rangeHandle.removeAttribute('style');
        }
        audioEl.removeEventListener('timeupdate', audioEl._timeupdateHandler);
        audioEl.removeEventListener('ended', audioEl._endedHandler);
        audioEl.src = audioUrl;
        audioEl.load();
        playBtn.classList.add('pause_audio');
        try { audioEl.play(); } catch (err) { console.warn('play', err); }
    } else {
        if (audioEl.paused) {
            playBtn.classList.add('pause_audio');
            try { audioEl.play(); } catch (err) { console.warn('play', err); }
        } else {
            playBtn.classList.remove('pause_audio');
            audioEl.pause();
        }
    }
    last_song = mainId;

    function onEnded() {
        audioEl.currentTime = 0;
        if (range) {
            var rFill = range.querySelector('.ui-slider-range');
            var rHandle = range.querySelector('.ui-slider-handle');
            if (rFill) rFill.style.width = '0%';
            if (rHandle) rHandle.style.left = '0%';
        }
        playBtn.classList.remove('pause_audio');
        var curr = document.getElementById(current_song);
        if (curr) {
            var t = curr.querySelector('time');
            var init = curr.getAttribute('data-initial-time');
            if (t && init !== null) t.textContent = init;
        }
    }
    function onTimeupdate() {
        var dur = audioEl.duration;
        var cur = audioEl.currentTime;
        if (dur && !isNaN(dur)) {
            if (timecode) timecode.textContent = '-' + convert_to_timecode(dur - cur);
            if (range && !range_manipulations) {
                var i = Math.floor((100 / dur) * cur);
                var rFill = range.querySelector('.ui-slider-range');
                var rHandle = range.querySelector('.ui-slider-handle');
                if (rFill) rFill.style.width = i + '%';
                if (rHandle) rHandle.style.left = i + '%';
            }
        }
    }

    audioEl._endedHandler = onEnded;
    audioEl._timeupdateHandler = onTimeupdate;
    audioEl.removeEventListener('ended', onEnded);
    audioEl.removeEventListener('timeupdate', onTimeupdate);
    audioEl.addEventListener('ended', onEnded);
    audioEl.addEventListener('timeupdate', onTimeupdate);
}

function onDocMouseUp() {
    if (range_manipulations && current_song) {
        var main = document.getElementById(current_song);
        var audioEl = document.querySelector('#player audio');
        if (main && audioEl) {
            var range = main.querySelector('.slider-range-max');
            var handle = range && range.querySelector('.ui-slider-handle');
            if (handle && audioEl.duration && !isNaN(audioEl.duration)) {
                var leftPx = handle.style.left || '0';
                var left = parseInt(leftPx, 10) || 0;
                audioEl.currentTime = (audioEl.duration / 100) * left;
            }
        }
        range_manipulations = false;
    }
    mouse_down = false;
}

function onBodyMouseOver(e) {
    var el = e.target && e.target.closest && e.target.closest('.slider-range-max');
    if (el && el !== e.relatedTarget && !el.contains(e.relatedTarget)) {
        var player = el.closest('.aduio_player');
        if (player && player.getAttribute('id') === current_song) range_manipulations = true;
    }
}

function onBodyMouseDown(e) {
    if (e.target && e.target.closest && e.target.closest('.slider-range-max')) mouse_down = true;
}

function onBodyMouseOut(e) {
    var el = e.target && e.target.closest && e.target.closest('.slider-range-max');
    if (el && el !== e.relatedTarget && !el.contains(e.relatedTarget) && !mouse_down) range_manipulations = false;
}

function initPlayerOld() {
    var audioEl = document.querySelector('#player audio');
    if (!audioEl) return;

    document.addEventListener('click', function(e) {
        var btn = e.target && e.target.closest && e.target.closest('.play_l_btn');
        if (!btn) return;
        e.preventDefault();
        handlePlayLBtnClick.call(btn, e);
    }, false);

    document.addEventListener('mouseup', onDocMouseUp, false);
    document.body.addEventListener('mouseover', onBodyMouseOver, false);
    document.body.addEventListener('mousedown', onBodyMouseDown, false);
    document.body.addEventListener('mouseout', onBodyMouseOut, false);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlayerOld);
} else {
    initPlayerOld();
}
