<?php
require "header_admin.php";   
require_once __DIR__ . "/../config/db.php";

// Fetch all payments with user information
try {
    $stmt = $pdo->query("
        SELECT p.*, u.username, u.email 
        FROM payments p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $transactions = [];
    $error = "Error fetching transactions: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #1a1a1a;
            --success: #2ecc71;
            --pending: #f1c40f;
            --failed: #e74c3c;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #0f0f0f, #000); 
            color: white; 
            margin: 0; 
            min-height: 100vh;
        }

        .container { 
            width: 95%; 
            max-width: 1200px; 
            margin: auto; 
            padding: 40px 0; 
        }

        h1 { 
            font-weight: 600; 
            letter-spacing: -1px; 
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* ===== TABLE STYLING ===== */
        .table-card {
            background: var(--card);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #333;
            box-shadow: 0 25px 60px rgba(0,0,0,.6);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #333;
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid #2a2a2a;
            font-size: 0.95rem;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* ===== STATUS BADGES ===== */
        .status {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-success { background: rgba(46, 204, 113, 0.2); color: var(--success); }
        .status-pending { background: rgba(241, 196, 15, 0.2); color: var(--pending); }
        .status-failed { background: rgba(231, 76, 60, 0.2); color: var(--failed); }

        .tx-ref {
            font-family: monospace;
            color: #aaa;
            font-size: 0.85rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #aaa;
            text-decoration: none;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .btn-back:hover { color: var(--primary); }

    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <a href="admin_dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
    
    <h1><i class="fa fa-receipt"></i> Transaction History</h1>
    <p style="color: #888; margin-bottom: 30px;">Track all subscription payments and revenue details.</p>

    <div class="table-card">
        <?php if (empty($transactions)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fa fa-folder-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
                <p>No transactions found yet.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td>
                                <div><?= date('M d, Y', strtotime($tx['created_at'])) ?></div>
                                <small style="color: #666;"><?= date('h:i A', strtotime($tx['created_at'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($tx['username']) ?></strong><br>
                                <small style="color: #888;"><?= htmlspecialchars($tx['email']) ?></small>
                            </td>
                            <td class="tx-ref"><?= htmlspecialchars($tx['tx_ref']) ?></td>
                            <td style="font-weight: 600;">
                                <?= number_format($tx['amount'], 2) ?> <?= htmlspecialchars($tx['currency']) ?>
                            </td>
                            <td>
                                <span class="status status-<?= strtolower($tx['status']) ?>">
                                    <?= $tx['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>