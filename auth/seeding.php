<?php
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/seed_config.php';
$p2pEnabled = !empty($config['enabled']);

// Fetch movies with seeding info
$stmt = $pdo->query("SELECT id, title, video, magnet_link, created_at FROM movies ORDER BY created_at DESC");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$uploadDir = realpath(__DIR__ . '/../uploads/videos');
$taskDir = realpath(__DIR__ . '/../uploads/to_seed');
if ($taskDir === false) $taskDir = __DIR__ . '/../uploads/to_seed';

function queued_for_seed($id) {
    $f = __DIR__ . '/../uploads/to_seed/' . $id . '.json';
    return file_exists($f);
}

function video_exists($video) {
    $p = __DIR__ . '/../uploads/videos/' . ($video ?: '');
    return $video && file_exists($p);
}

// optional messages
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Seeding Status | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container{max-width:1100px;margin:40px auto;padding:0 20px;color:#fff}
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.04);text-align:left}
        th{color:#bbb;font-size:0.9rem}
        td small{color:#bdbdbd}
        .btn{display:inline-block;padding:8px 12px;background:#222;border:1px solid #333;border-radius:8px;color:#fff;text-decoration:none}
        .btn.warn{background:#b03}
        .status{padding:6px 8px;border-radius:8px;font-weight:600}
        .ok{background:rgba(40,167,69,0.12);color:#2ecc71;border:1px solid rgba(40,167,69,0.18)}
        .no{background:rgba(220,53,69,0.06);color:#ff7675;border:1px solid rgba(220,53,69,0.12)}
        .muted{color:#aaa}
        .copy-btn{cursor:pointer;background:#111;border:1px solid #333;padding:6px 8px;border-radius:6px;color:#fff}
        .top-row{display:flex;gap:12px;align-items:center;justify-content:space-between}
    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="top-row">
        <div>
            <h1>Seeding Status</h1>
            <p class="muted">See magnet links, queued tasks, and requeue/clear actions.</p>
            <?php if (!$p2pEnabled): ?>
                <div style="margin-top:12px;padding:12px;background:rgba(220,53,69,0.06);border:1px solid rgba(220,53,69,0.12);color:#ff7675;border-radius:8px">Seeding is disabled in configuration.</div>
            <?php endif; ?>
        </div>
        <div>
            <a href="admin_add_movie.php" class="btn"><i class="fa fa-plus"></i> Add Movie</a>
            <a href="admin_dashboard.php" class="btn"><i class="fa fa-arrow-left"></i> Back</a>
            <?php if ($p2pEnabled): ?>
                <a href="queue_missing.php" class="btn" style="margin-left:8px;"><i class="fa fa-link"></i> Queue Missing Magnets</a>
            <?php else: ?>
                <span class="btn" style="margin-left:8px;opacity:0.6;cursor:default">Seeding Disabled</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="margin-top:12px;padding:10px;background:rgba(40,167,69,0.08);border:1px solid rgba(40,167,69,0.12);color:#2ecc71;border-radius:8px"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="margin-top:12px;padding:10px;background:rgba(220,53,69,0.06);border:1px solid rgba(220,53,69,0.12);color:#ff7675;border-radius:8px"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Video</th>
                <th>Magnet Link</th>
                <th>Seeded?</th>
                <th>Queued</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($movies as $m): ?>
                <?php $id = (int)$m['id']; $hasMagnet = !empty($m['magnet_link']); $videoOk = video_exists($m['video']); $queued = queued_for_seed($id); ?>
                <tr>
                    <td><?= $id ?></td>
                    <td><?= htmlspecialchars($m['title']) ?><br><small><?= htmlspecialchars($m['created_at']) ?></small></td>
                    <td>
                        <?php if ($videoOk): ?>
                            <small><?= htmlspecialchars($m['video']) ?></small>
                        <?php else: ?>
                            <small class="muted">missing</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasMagnet): ?>
                            <code id="magnet-<?= $id ?>" style="display:block;max-width:360px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($m['magnet_link']) ?></code>
                            <button class="copy-btn" data-target="#magnet-<?= $id ?>">Copy</button>
                        <?php else: ?>
                            <small class="muted">no magnet</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasMagnet): ?>
                            <span class="status ok">Seeded</span>
                        <?php else: ?>
                            <span class="status no">Not seeded</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($queued): ?>
                            <small class="muted">Queued</small>
                        <?php else: ?>
                            <small class="muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p2pEnabled): ?>
                            <?php if (!$queued): ?>
                                <form method="POST" action="requeue_seed.php" style="display:inline">
                                    <input type="hidden" name="movie_id" value="<?= $id ?>">
                                    <button class="btn" type="submit">Queue Seed</button>
                                </form>
                            <?php else: ?>
                                <small class="muted">queued</small>
                            <?php endif; ?>

                            <?php if ($hasMagnet): ?>
                                <form method="POST" action="clear_magnet.php" style="display:inline;margin-left:8px;">
                                    <input type="hidden" name="movie_id" value="<?= $id ?>">
                                    <button class="btn warn" type="submit">Clear Magnet</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <small class="muted">Seeding disabled</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-target');
        const el = document.querySelector(target);
        if (!el) return;
        const text = el.textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'Copied';
            setTimeout(() => btn.textContent = 'Copy', 1200);
        }).catch(() => alert('Copy failed'));
    });
});
</script>

</body>
</html>