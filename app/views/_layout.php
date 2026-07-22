<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gloop</title>
    <link rel="stylesheet" href="/public/assets/style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo"><a href="/">Gloop</a></div>

        <nav>
            <a class="<?= $page === 'home' ? 'active' : '' ?>" href="/">Home</a>
            <a class="<?= $page === 'browse' ? 'active' : '' ?>" href="/browse">Browse</a>
        </nav>

        <?php if (is_logged()): ?>
            <a href="/movie/search" class="new-entry-nav-btn">+ New entry</a>
        <?php endif; ?>

        <div class="search-bar">
            <span class="search-icon">⌕</span>
            <input
                type="text"
                placeholder="Search movies..."
                id="search-input"
                onkeydown="if(event.key==='Enter' && this.value.trim()!=='') window.location='/search?q='+encodeURIComponent(this.value.trim())"
            />
        </div>

        <div class="auth-buttons">
            <?php if (is_logged()): ?>
                <?php if (is_admin()): ?>
                    <a href="/admin" class="login-btn">Admin Dashboard</a>
                <?php endif; ?>
                <span class="logged-in-as">Logged in as <strong><?= $_SESSION['user_username'] ?></strong></span>
                <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark mode">L</button>
                <a href="/logout" class="login-btn">Log Out</a>
            <?php else: ?>
                <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark mode">L</button>
                <a href="/login" class="login-btn">Log In</a>
                <a href="/signup" class="signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>

        <button class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <div class="mobile-menu" id="mobile-menu">
        <nav class="mobile-nav">
            <a class="<?= $page === 'home' ? 'active' : '' ?>" href="/">Home</a>
            <a class="<?= $page === 'browse' ? 'active' : '' ?>" href="/browse">Browse</a>
            <?php if (is_logged()): ?>
                <a href="/movie/search">New entry</a>
            <?php endif; ?>
        </nav>
        <div class="mobile-auth">
            <?php if (is_logged()): ?>
                <?php if (is_admin()): ?>
                    <a href="/admin" class="mobile-menu-link">Admin Dashboard</a>
                <?php endif; ?>
                <span class="mobile-logged-in">Logged in as <strong><?= $_SESSION['user_username'] ?></strong></span>
                <a href="/logout" class="mobile-menu-link">Log Out</a>
            <?php else: ?>
                <a href="/login" class="mobile-menu-link">Log In</a>
                <a href="/signup" class="mobile-menu-link">Sign Up</a>
            <?php endif; ?>
            <button class="theme-toggle mobile-theme-toggle" onclick="toggleTheme()">L</button>
        </div>
    </div>

    <?= $page_content ?>

    <div class="footer">
        <p>© 2026 Gloop</p>
    </div>

    <script>
        // Apply saved theme on load
        const saved = localStorage.getItem('theme');
        if (saved) {
            document.documentElement.setAttribute('data-theme', saved);
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) toggleBtn.textContent = saved === 'light' ? 'D' : 'L';
            const mobileToggle = document.querySelector('.mobile-theme-toggle');
            if (mobileToggle) mobileToggle.textContent = saved === 'light' ? 'D' : 'L';
        }

        function toggleTheme() {
            document.documentElement.classList.add('no-transition');
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', next === 'dark' ? '' : next);
            localStorage.setItem('theme', next === 'dark' ? '' : next);
            const label = next === 'light' ? 'D' : 'L';
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) toggleBtn.textContent = label;
            const mobileToggle = document.querySelector('.mobile-theme-toggle');
            if (mobileToggle) mobileToggle.textContent = label;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    document.documentElement.classList.remove('no-transition');
                });
            });
        }

        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            const burger = document.getElementById('hamburger');
            menu.classList.toggle('open');
            burger.classList.toggle('open');
        }
    </script>
</body>
</html>