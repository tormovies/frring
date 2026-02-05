<nav class="account-nav" style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
    <div class="container">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" 
                   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   style="padding: 0.5rem 1rem; text-decoration: none; color: var(--text-primary); border-radius: 6px; {{ request()->routeIs('dashboard') ? 'background: var(--bg-tertiary);' : '' }}">
                    Кабинет
                </a>
                <a href="{{ route('account.materials.index') }}" 
                   class="{{ request()->routeIs('account.materials.*') ? 'active' : '' }}"
                   style="padding: 0.5rem 1rem; text-decoration: none; color: var(--text-primary); border-radius: 6px; {{ request()->routeIs('account.materials.*') ? 'background: var(--bg-tertiary);' : '' }}">
                    Материалы
                </a>
                <a href="{{ route('account.authors.index') }}" 
                   class="{{ request()->routeIs('account.authors.*') ? 'active' : '' }}"
                   style="padding: 0.5rem 1rem; text-decoration: none; color: var(--text-primary); border-radius: 6px; {{ request()->routeIs('account.authors.*') ? 'background: var(--bg-tertiary);' : '' }}">
                    Авторы
                </a>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="padding: 0.5rem 1rem; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                    Выйти
                </button>
            </form>
        </div>
    </div>
</nav>
