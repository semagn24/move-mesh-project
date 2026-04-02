<?php
session_start();
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';

/* ================= ADMIN PROTECTION ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SEARCH LOGIC ================= */
$search = $_GET['search'] ?? '';

$sql = "SELECT id, username, email, role 
        FROM users 
        WHERE username LIKE :search OR email LIKE :search
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':search' => "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | MovieBox</title>
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #141414;
            --border: #333;
            --text-dim: #999;
            --success: #2ecc71;
            --warning: #ffc107;
        }

        body { background: var(--bg); color: #fff; font-family: 'Poppins', sans-serif; margin: 0; }
        .container { width: 95%; max-width: 1200px; margin: auto; padding: 20px 0; }

        /* Header & Search Bar */
        .header-flex { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; padding: 0 10px; }
        .search-form { display: flex; gap: 5px; background: #1a1a1a; padding: 5px; border-radius: 8px; border: 1px solid var(--border); flex-grow: 1; max-width: 600px; margin: 0 10px 20px 10px; }
        .search-form input { background: transparent; border: none; color: white; padding: 10px; width: 100%; outline: none; }
        .btn-search { background: var(--primary); color: #fff; border-radius: 6px; padding: 0 20px; font-weight: 500; border: none; cursor: pointer; }

        /* Desktop Table Styling */
        .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .user-table th { text-align: left; padding: 15px; color: var(--text-dim); font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        .user-table td { padding: 15px; border-bottom: 1px solid #222; vertical-align: middle; }

        /* Badges & General Buttons */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
        .badge-admin { background: rgba(46, 204, 113, 0.2); color: var(--success); border: 1px solid var(--success); }
        .badge-user { background: rgba(153, 153, 153, 0.2); color: var(--text-dim); border: 1px solid var(--text-dim); }
        
        .btn { padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; transition: 0.3s; white-space: nowrap; overflow: visible; }
        .btn-role { background: #222; color: var(--warning); border: 1px solid var(--warning); }
        .btn-role:hover { background: var(--warning); color: #000; }
        .btn-delete { background: transparent; color: #ff4d4d; border: 1px solid #ff4d4d; }
        .btn-delete:hover { background: #ff4d4d; color: #fff; }

        /* MOBILE CARD VIEW (Fixed Labels & Spacing) */
        @media (max-width: 768px) {
            .user-table thead { display: none; }
            .user-table, .user-table tbody, .user-table tr, .user-table td { display: block; width: 100%; box-sizing: border-box; }

            .user-table tr { 
                background: var(--card); 
                margin: 0 auto 20px auto; 
                border: 1px solid var(--border); 
                border-radius: 12px;
                overflow: hidden;
                width: calc(100% - 20px);
            }

            .user-table td { 
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 12px 15px;
                border-bottom: 1px solid #222;
                min-height: 45px;
            }

            /* Bottom Action row styling */
            .user-table td:last-child { border-bottom: none; background: rgba(255,255,255,0.02); padding: 15px; }

            .user-table td::before {
                content: attr(data-label);
                text-align: left;
                font-weight: 700;
                color: var(--text-dim);
                font-size: 0.75rem;
                text-transform: uppercase;
                flex: 1;
            }

            .user-table td > * { flex: 1; }
            .header-flex { flex-direction: column; text-align: center; }
            .search-form { width: 92%; margin: 0 auto 20px auto; }
        }
    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="header-flex">
        <h2><i class="fa fa-users" style="color:var(--primary)"></i> User Management</h2>
        <span style="color:var(--text-dim); font-size:0.9rem;">Total Users: <?= count($users) ?></span>
    </div>

    <form class="search-form" method="get">
        <input type="text" name="search" placeholder="Search by username or email..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-search">Search</button>
    </form>

    <?php if (empty($users)): ?>
        <div style="text-align:center; padding: 50px; color:var(--text-dim);">
            <i class="fa fa-user-slash" style="font-size: 3rem; margin-bottom:10px;"></i>
            <p>No matching users found.</p>
        </div>
    <?php else: ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td data-label="ID"><span>#<?= $u['id'] ?></span></td>
                        <td data-label="Username"><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td data-label="Email"><span><?= htmlspecialchars($u['email']) ?></span></td>
                        <td data-label="Role">
                            <div>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge badge-admin">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-user">User</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td data-label="Actions">
                            <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center; width: 100%; flex-wrap: wrap;">
                                <form method="post" action="user_role.php" style="margin:0; flex: 1; min-width: 120px; max-width: 150px;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-role" style="width: 100%; justify-content: center;">
                                        <i class="fa fa-sync-alt"></i> 
                                        <span><?= $u['role'] === 'admin' ? 'Demote' : 'Promote' ?></span>
                                    </button>
                                </form>

                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="post" action="user_delete.php" onsubmit="return confirm('Delete user permanently?');" style="margin:0; flex: 1; min-width: 120px; max-width: 150px;">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-delete" style="width: 100%; justify-content: center;">
                                            <i class="fa fa-trash-alt"></i> 
                                            <span style="display: inline-block;">Delete</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div style="flex: 1; min-width: 120px; max-width: 150px;"></div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>