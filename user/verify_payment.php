<?php
require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/payment_config.php';

$tx_ref = $_GET['tx_ref'] ?? $_GET['reference'] ?? '';
if (!$tx_ref) {
    echo "Missing tx_ref";
    exit;
}

// Chapa verify endpoint - try to verify transaction
$ch = curl_init('https://api.chapa.co/v1/transaction/verify/' . urlencode($tx_ref));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . ($config['chapa_secret'] ?? ''),
]);
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
file_put_contents($logDir . '/chapa_debug.log', date('c') . " VERIFY HTTP $http_code: $res\n", FILE_APPEND);
if ($res === false) {
    echo 'Verification error: ' . curl_error($ch);
    exit;
}
$json = json_decode($res, true);

$success = false;
if (isset($json['data']) && is_array($json['data'])) {
    $status = strtolower($json['data']['status'] ?? '');
    if ($status === 'success' || $status === 'successful') $success = true;
}

// Determine user id: prefer logged in user; otherwise parse tx_ref that we generated on init (sub_<time>_<user_id>)
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    if (preg_match('/_([0-9]+)$/', $tx_ref, $m)) {
        $user_id = (int)$m[1];
    }
}

if ($success && $user_id) {
    $days = (int)($config['plan_days'] ?? 30);
    $new_expiry = date('Y-m-d H:i:s', strtotime("+{$days} days"));
    $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'premium', subscription_expiry = ? WHERE id = ?");
    $stmt->execute([$new_expiry, $user_id]);

    // Redirect to profile with success
    header('Location: ' . ($config['return_url'] ?? '../users/profile.php') . '?msg=' . urlencode('Subscription activated')); exit;
}

// Failure
header('Location: ' . ($config['return_url'] ?? '../users/profile.php') . '?error=' . urlencode('Payment verification failed')); exit;
?>