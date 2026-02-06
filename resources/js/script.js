/**
 * AudioCloud v2 — компактный JS-плеер
 * Работает для кнопок:
 * .play-cloud, .card-play-featured-cloud, .play-btn-list, .play-btn-grid,
 * .similar-play-sidebar, .play-pause-btn
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemes();
    initMobileMenu();
    UI.init();
    Player.init();
    console.log('AudioPlayer ready');
});

// === UI ===
const UI = {
    init() {
        this.initViewToggle();
        this.initSort();
        this.initLikes();
    },

    initViewToggle() {
        const buttons = document.querySelectorAll('.view-btn');
        const list = document.getElementById('list-view');
        const grid = document.getElementById('grid-view');
        if (!buttons.length) return;

        const params = new URLSearchParams(location.search);
        let view = params.get('view') || localStorage.getItem('ac-view') || 'list';
        apply(view);

        buttons.forEach(btn => btn.addEventListener('click', () => {
            view = btn.dataset.view;
            params.set('view', view);
            localStorage.setItem('ac-view', view);
            history.replaceState({}, '', `${location.pathname}?${params}`);
            apply(view);
        }));

        function apply(v) {
            buttons.forEach(b => b.classList.toggle('active', b.dataset.view === v));
            list && (list.style.display = v === 'list' ? 'flex' : 'none');
            grid && (grid.style.display = v === 'grid' ? 'grid' : 'none');
        }
    },

    initSort() {
        const select = document.getElementById('sort-select');
        if (!select) return;
        select.addEventListener('change', () => {
            const params = new URLSearchParams(location.search);
            params.set('sort', select.value);
            params.set('view', localStorage.getItem('ac-view') || 'list');
            location.search = params.toString();
        });
    },

    initLikes() {
        document.body.addEventListener('click', e => {
            const btn = e.target.closest('.like-btn');
            if (!btn) return;

            // полностью блокируем переход по ссылке
            e.preventDefault();
            e.stopPropagation();

            const liked = btn.classList.toggle('liked');
            const counter = btn.nextElementSibling;
            const url = liked
                ? btn.dataset.likeUrl
                : btn.dataset.dislikeUrl;

            // обновляем вид сразу
            btn.textContent = liked ? '♥' : '♡';
            if (counter) {
                let n = +counter.textContent + (liked ? 1 : -1);
                counter.textContent = Math.max(0, n);
                counter.animate([{ transform: 'scale(1.2)' }, { transform: 'scale(1)' }], { duration: 200 });
            }

            // отправляем безопасный POST-запрос
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).catch(err => console.warn('like error', err));
        });
    }
};

// === PLAYER ===
const Player = {
    audios: {},
    active: null,
    volume: 1,

    init() {
        ['global', 'local', 'similar'].forEach(t => this.audios[t] = this.createAudio(t));
        this.createFloatingUI();
        this.bindPlayButtons();
        this.loop();
    },

    createAudio(type) {
        const a = document.createElement('audio');
        a.id = `${type}-audio`;
        a.hidden = true;
        document.body.appendChild(a);
        a.volume = this.volume;
        return a;
    },

    bindPlayButtons() {
        document.body.addEventListener('click', e => {
            const btn = e.target.closest(
                '.play-cloud, .card-play-featured-cloud, .play-btn-list, .play-btn-grid, .similar-play-sidebar, .play-pause-btn'
            );
            if (!btn) return;
            e.preventDefault();

            const type = btn.classList.contains('similar-play-sidebar')
                ? 'similar'
                : btn.classList.contains('play-pause-btn')
                    ? 'local'
                    : 'global';

            const data = {
                src: btn.dataset.audioUrl,
                title: btn.dataset.title || 'Без названия',
                author: btn.dataset.author || 'AI SoundLab',
                kind: btn.dataset.type || 'Аудио'
            };
            if (!data.src) return;

            const a = this.audios[type];
            if (this.active === type && a.src === data.src && !a.paused) {
                return this.pause(type);
            }

            this.play(type, data, btn);
        });
    },

    play(type, { src, title, author, kind }, btn) {
        this.stopAllExcept(type);
        const a = this.audios[type];
        a.src = src;
        a.load();
        a.play().catch(err => console.warn('play error', err));
        this.active = type;
        this.lastButton = btn;
        this.updateUI(title, `${author} • ${kind}`);
        this.toggleFloating(true);
        this.updateButtons(btn, true);
        if (type === 'local') this.toggleWave(true);
    },

    pause(type) {
        const a = this.audios[type];
        a.pause();
        this.updateButtons(null, false);
        if (type === 'local') this.toggleWave(false);
    },

    stopAllExcept(type) {
        for (const [t, a] of Object.entries(this.audios)) {
            if (t !== type) {
                a.pause(); a.currentTime = 0;
            }
        }
        this.updateButtons(null, false);
        this.toggleWave(false);
    },

    updateButtons(activeBtn, playing) {
        document.querySelectorAll(
            '.play-cloud, .card-play-featured-cloud, .play-btn-list, .play-btn-grid, .similar-play-sidebar, .play-pause-btn'
        ).forEach(b => {
            b.textContent = (b === activeBtn && playing) ? '❚❚' : '▶';
            b.style.background = (b === activeBtn && playing) ? 'var(--accent-primary)' : '';
        });
    },

    toggleWave(active) {
        document.querySelectorAll('.wave-bar').forEach(b =>
            b.classList.toggle('active', active)
        );
    },

    createFloatingUI() {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="floating-player" class="floating-player" style="display:none;">
                <div class="player-progress-bar"><div id="player-progress" class="progress-fill"></div></div>
                <div class="player-content">
                    <button id="player-play" class="player-play-pause">▶</button>
                    <div class="player-info"><div id="p-title">Выберите трек</div><div id="p-meta">AudioCloud</div></div>
                    <div id="p-time" class="time-display">0:00 / 0:00</div>
                    <div class="volume-control"><button id="p-vol">🔊</button><div class="volume-slider"><div id="p-volbar"></div></div></div>
                </div>
            </div>`);

        this.ui = {
            box: document.getElementById('floating-player'),
            play: document.getElementById('player-play'),
            prog: document.getElementById('player-progress'),
            time: document.getElementById('p-time'),
            vol: document.getElementById('p-vol'),
            volbar: document.getElementById('p-volbar'),
            title: document.getElementById('p-title'),
            meta: document.getElementById('p-meta')
        };

        this.ui.play.onclick = () => this.toggle();
        this.ui.vol.onclick = () => this.toggleMute();
        this.ui.box.querySelector('.player-progress-bar')
            .addEventListener('click', e => this.seek(e));
        this.ui.box.querySelector('.volume-slider')
            .addEventListener('click', e => this.setVolume(e));
    },

    updateUI(title, meta) {
        this.ui.title.textContent = title;
        this.ui.meta.textContent = meta;
        this.ui.play.textContent = '❚❚';
    },

    updateProgress() {
        const a = this.active ? this.audios[this.active] : null;
        if (!a || !a.duration) return;
        const pct = (a.currentTime / a.duration) * 100;
        this.ui.prog.style.width = `${pct || 0}%`;
        this.ui.time.textContent = `${this.fmt(a.currentTime)} / ${this.fmt(a.duration)}`;
    },

    fmt(s) {
        if (!s) return '0:00';
        const m = Math.floor(s / 60), sec = Math.floor(s % 60);
        return `${m}:${sec.toString().padStart(2, '0')}`;
    },

    toggleFloating(show) {
        this.ui.box.style.display = show ? 'flex' : 'none';
    },

    toggle() {
        const a = this.active && this.audios[this.active];
        if (!a) return;

        if (a.paused) {
            a.play();
            this.ui.play.textContent = '❚❚';
            this.updateButtons(this.lastButton, true);
        } else {
            a.pause();
            this.ui.play.textContent = '▶';
            this.updateButtons(this.lastButton, false);
            this.toggleWave(false);
        }
    },

    toggleMute() {
        this.volume = this.volume > 0 ? 0 : 1;
        Object.values(this.audios).forEach(a => a.volume = this.volume);
        this.ui.vol.textContent = this.volume === 0 ? '🔇' : (this.volume < 0.5 ? '🔈' : '🔊');
        this.ui.volbar.style.width = (this.volume * 100) + '%';
    },

    seek(e) {
        const a = this.active && this.audios[this.active];
        if (!a || !a.duration) return;
        const r = e.currentTarget.getBoundingClientRect();
        a.currentTime = ((e.clientX - r.left) / r.width) * a.duration;
        this.updateProgress();
    },

    setVolume(e) {
        const r = e.currentTarget.getBoundingClientRect();
        this.volume = Math.max(0, Math.min(1, (e.clientX - r.left) / r.width));
        Object.values(this.audios).forEach(a => a.volume = this.volume);
        this.ui.volbar.style.width = (this.volume * 100) + '%';
    },

    loop() {
        this.updateProgress();
        requestAnimationFrame(() => this.loop());
    }
};

// === THEMES & MENU ===
function initThemes() {
    const btns = document.querySelectorAll('.theme-btn');
    const body = document.body;
    const saved = localStorage.getItem('ac-theme') || 'light';
    body.dataset.theme = saved;
    btns.forEach(b => b.classList.toggle('active', b.dataset.theme === saved));
    btns.forEach(b => b.addEventListener('click', () => {
        body.dataset.theme = b.dataset.theme;
        localStorage.setItem('ac-theme', b.dataset.theme);
        btns.forEach(x => x.classList.remove('active'));
        b.classList.add('active');
    }));
}

function initMobileMenu() {
    const open = document.querySelector('.hamburger-btn');
    const close = document.querySelector('.close-menu');
    const menu = document.querySelector('.mobile-menu');
    if (!open || !menu) return;
    open.onclick = () => { menu.classList.add('active'); document.body.style.overflow = 'hidden'; };
    [close, menu].forEach(el => el && el.addEventListener('click', e => {
        if (e.target === el || e.target === close) {
            menu.classList.remove('active'); document.body.style.overflow = '';
        }
    }));
}
