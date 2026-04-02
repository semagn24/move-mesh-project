<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/payment_config.php';

// Require logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { exit('User not found'); }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = (float) ($config['plan_price'] ?? 150);
    $tx_ref = 'sub_' . time() . '_' . $user_id;

    $payload = [
        'amount' => $price,
        'currency' => 'ETB',
        'email' => $user['email'],
        'tx_ref' => $tx_ref,
        'callback_url' => $config['callback_url'],
        'return_url' => $config['return_url'],
        'first_name' => $user['username'],
    ];

    // Basic safety: ensure secret is set
    if (empty($config['chapa_secret']) || $config['chapa_secret'] === 'CHANGE_ME') {
        $message = 'Payment not configured. Please set CHAPA_SECRET in your environment.';
    } else {
        $ch = curl_init('https://api.chapa.co/v1/transaction/initialize');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['chapa_secret']
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        file_put_contents($logDir . '/chapa_debug.log', date('c') . " INIT HTTP $http_code: $res\n", FILE_APPEND);

        if ($res === false) {
            $message = 'Payment initialization failed: ' . curl_error($ch);
        } else {
            $json = json_decode($res, true);
            if (isset($json['data']['checkout_url'])) {
                header('Location: ' . $json['data']['checkout_url']);
                exit;
            } else {
                $message = 'Payment provider error (HTTP ' . $http_code . '): ' . ($json['message'] ?? json_encode($json));
            }
        }
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Subscribe | <?= htmlspecialchars($user['username']) ?></title>
    <style>body{font-family:Arial,Helvetica,sans-serif;background:#0b0b0b;color:#fff;padding:40px} .card{max-width:600px;margin:auto;background:#111;padding:30px;border-radius:12px;border:1px solid #222}</style>
</head>
<body>

<div class="card">
    <h2>Get Premium Access 💎</h2>
    <p>Price: <strong><?= htmlspecialchars($config['plan_price']) ?> ETB</strong> for <?= htmlspecialchars($config['plan_days']) ?> days.</p>

    <?php if ($message): ?>
        <div style="background:#2b2b2b;padding:10px;border-radius:8px;margin-bottom:12px;color:#f66"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <button type="submit" style="background:#e50914;color:#fff;padding:12px 20px;border-radius:8px;border:none;font-weight:600">Subscribe now</button>
    </form>

    <p style="margin-top:14px;color:#aaa">You will be redirected to the Chapa checkout. After successful payment you will be returned to your profile.</p>
</div>

</body>
</html>