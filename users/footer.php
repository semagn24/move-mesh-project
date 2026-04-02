<?php // Simple reusable footer fragment ?>
<footer class="site-footer" role="contentinfo">
    <div class="footer-inner">
        <div class="footer-left">
            <a href="/movie_stream/index.php">Home</a> ·
            <a href="/movie_stream/movies/catalog.php">Catalog</a> ·
            <a href="/movie_stream/about.php">About</a>
        </div>
        <div class="footer-right">
            <small>© <?= date('Y') ?> MovieStream. All rights reserved.</small>
        </div>
    </div>
</footer>

<style>
.site-footer{background:#070707;color:#bdbdbd;padding:20px 0;border-top:1px solid rgba(255,255,255,0.03);position:relative}
.footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:0 18px;font-size:0.9rem}
.footer-inner a{color:#bdbdbd;text-decoration:none;margin:0 6px}
.footer-inner a:hover{color:#fff}
@media (max-width:720px){.footer-inner{flex-direction:column;gap:8px;text-align:center}}
</style>