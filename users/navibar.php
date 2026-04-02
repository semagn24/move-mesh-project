<?php
// Responsive, attractive navbar fragment
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Improved header styles + minimal dependency on Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pQ2x1Kk+FQmI3V5pGkPl1Gz6x1x4v7Yp6hVf8zLr9VjGJqK5n/0V6Qf6xk6j2n0zQw7e1xGq5R2rBf2Y8j0w2g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    :root{
        --accent: #E50914;
        --bg: rgba(10,10,10,0.95);
        --muted: #bcbcbc;
    }
    .site-header{position:sticky;top:0;z-index:1300;background:var(--bg);backdrop-filter:blur(6px);border-bottom:1px solid rgba(255,255,255,0.03);}
    .header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:12px;padding:12px 18px;}
    .logo{color:var(--accent);font-weight:800;font-size:20px;text-decoration:none;letter-spacing:1px}

    /* Desktop nav */
    .main-nav{display:flex;gap:8px;margin-left:12px}
    .main-nav a{color:var(--muted);text-decoration:none;padding:8px 12px;border-radius:8px;font-size:14px;display:flex;gap:8px;align-items:center}
    .main-nav a:hover{background:rgba(255,255,255,0.03);color:#fff;transform:translateY(-2px)}
    .main-nav a.active{color:#fff;background:linear-gradient(90deg, rgba(229,9,20,0.12), rgba(229,9,20,0.06));border:1px solid rgba(229,9,20,0.08)}

    .search-area{margin-left:auto;display:flex;align-items:center;gap:8px}
    .search-input{background:#111;border:1px solid #222;color:#fff;padding:8px 12px;border-radius:8px;min-width:200px}
    .search-btn{background:var(--accent);color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer}

    .user-area{display:flex;align-items:center;gap:8px;margin-left:8px}
    .user-area a, .user-area button{color:var(--muted);text-decoration:none;padding:8px 10px;border-radius:8px;background:transparent;border:0;cursor:pointer}
    .user-area a:hover, .user-area button:hover{color:#fff}

    .hamburger{display:none;background:transparent;border:none;color:#fff;font-size:20px;cursor:pointer}

    /* Mobile menu - offcanvas top */
    .mobile-drawer{position:fixed;left:0;right:0;top:0;transform:translateY(-120%);transition:transform .28s ease;z-index:1400;background:linear-gradient(180deg, rgba(10,10,10,0.98), rgba(10,10,10,0.98));box-shadow:0 8px 30px rgba(0,0,0,0.6);padding:18px;border-bottom:1px solid rgba(255,255,255,0.03)}
    .mobile-drawer.open{transform:translateY(0)}
    .mobile-drawer .mobile-links{display:flex;flex-direction:column;gap:10px;margin-top:10px}
    .mobile-drawer a{color:#ddd;text-decoration:none;padding:12px;border-radius:8px;background:transparent}

    /* overlay for drawer */
    .drawer-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.45);opacity:0;pointer-events:none;transition:opacity .2s ease;z-index:1350}
    .drawer-backdrop.show{opacity:1;pointer-events:auto}

    /* responsive */
    @media (max-width:920px){
        .main-nav{display:none}
        .search-input{min-width:120px}
        .hamburger{display:inline-flex}
        .search-area{display:flex}
    }

    @media (max-width:420px){
        .search-input{display:none}
    }

    /* subtle icon styles */
    .fa-fw{width:1.25em;text-align:center}
</style>

<header class="site-header" role="banner">
    <div class="header-inner">
        <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false"><i class="fa fa-bars"></i></button>
        <a class="logo" href="/movie_stream/index.php">MOVIESTREAM</a>

        <nav class="main-nav" role="navigation" aria-label="Primary">
            <a href="/movie_stream/index.php"><i class="fa fa-home fa-fw"></i> Home</a>
            <a href="/movie_stream/movies/catalog.php?type=movie"><i class="fa fa-film fa-fw"></i> Movies</a>
            <a href="/movie_stream/movies/catalog.php?type=tv"><i class="fa fa-tv fa-fw"></i> TV Shows</a>
            <a href="/movie_stream/movies/catalog.php?sort=most_viewed"><i class="fa fa-fire fa-fw"></i> Trending</a>
            <a href="/movie_stream/about.php"><i class="fa fa-info-circle fa-fw"></i> About</a>
        </nav>

        <div class="search-area">
            <form action="/movie_stream/movies/catalog.php" method="GET" style="display:flex;gap:8px;align-items:center">
                <input class="search-input" name="q" type="search" placeholder="Search movies, actors..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" aria-label="Search">
                <button class="search-btn" type="submit" aria-label="Search"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="user-area">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/movie_stream/users/profile.php" aria-label="Profile"><i class="fa fa-user fa-fw"></i></a>
                <a href="/movie_stream/auth/logout.php" aria-label="Logout"><i class="fa fa-sign-out-alt fa-fw"></i></a>
            <?php else: ?>
                <a href="/movie_stream/auth/login.php" aria-label="Login">Login</a>
                <a href="/movie_stream/auth/register.php" aria-label="Sign up" style="background:var(--accent);color:#fff;padding:8px 10px">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
        <form action="/movie_stream/movies/catalog.php" method="GET" style="display:flex;gap:8px;align-items:center">
            <input name="q" type="search" placeholder="Search..." style="flex:1;padding:10px;border-radius:8px;border:1px solid #222;background:#0b0b0b;color:#fff">
            <button type="submit" style="background:var(--accent);border:none;color:#fff;padding:10px;border-radius:8px"><i class="fa fa-search"></i></button>
        </form>

        <div class="mobile-links">
            <a href="/movie_stream/index.php"><i class="fa fa-home fa-fw"></i> Home</a>
            <a href="/movie_stream/movies/catalog.php?type=movie"><i class="fa fa-film fa-fw"></i> Movies</a>
            <a href="/movie_stream/movies/catalog.php?type=tv"><i class="fa fa-tv fa-fw"></i> TV Shows</a>
            <a href="/movie_stream/movies/catalog.php?sort=most_viewed"><i class="fa fa-fire fa-fw"></i> Trending</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/movie_stream/users/profile.php"><i class="fa fa-user fa-fw"></i> Profile</a>
                <a href="/movie_stream/auth/logout.php"><i class="fa fa-sign-out-alt fa-fw"></i> Logout</a>
            <?php else: ?>
                <a href="/movie_stream/auth/login.php"><i class="fa fa-sign-in-alt fa-fw"></i> Login</a>
                <a href="/movie_stream/auth/register.php" style="background:var(--accent);color:#fff;padding:8px;border-radius:8px;text-align:center">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="drawer-backdrop" id="drawerBackdrop" tabindex="-1" aria-hidden="true"></div>
</header>

<script>
    (function(){
        var btn = document.getElementById('hamburger');
        var drawer = document.getElementById('mobileDrawer');
        var backdrop = document.getElementById('drawerBackdrop');
        if(!btn || !drawer || !backdrop) return;

        function openDrawer(){
            drawer.classList.add('open');
            backdrop.classList.add('show');
            drawer.setAttribute('aria-hidden','false');
            backdrop.setAttribute('aria-hidden','false');
            btn.setAttribute('aria-expanded','true');
            document.body.style.overflow = 'hidden';
        }
        function closeDrawer(){
            drawer.classList.remove('open');
            backdrop.classList.remove('show');
            drawer.setAttribute('aria-hidden','true');
            backdrop.setAttribute('aria-hidden','true');
            btn.setAttribute('aria-expanded','false');
            document.body.style.overflow = '';
        }

        btn.addEventListener('click', function(){
            if(drawer.classList.contains('open')) closeDrawer(); else openDrawer();
        });
        backdrop.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeDrawer(); });
    })();
</script>