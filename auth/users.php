<?php
session_start();
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';

/* ================= ADMIN PROTECTION ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SEARCH ================= */
$search = $_GET['search'] ?? '';

$sql = "SELECT id, username, email, role 
        FROM users 
        WHERE username LIKE :search OR email LIKE :search
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':search' => "%$search%"
]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.container { padding: 20px; color:#fff; }
h2 { margin-bottom:15px; }
.search-box { margin-bottom:15px; }

table {
    width:100%;
    border-collapse: collapse;
    background:#111;
}

th, td {
    padding:10px;
    border-bottom:1px solid #333;
    text-align:left;
}

th {
    background:#222;
}

.btn {
    padding:6px 10px;
    border-radius:6px;
    font-size:0.8rem;
    border:none;
    cursor:pointer;
}

.btn-role { background:#ffc107; }
.btn-delete { background:#dc3545; color:#fff; }
.btn-role:hover, .btn-delete:hover { opacity:0.85; }

.badge {
    padding:4px 8px;
    border-radius:10px;
    font-size:0.75rem;
}

.badge-admin { background:#28a745; }
.badge-user { background:#6c757d; }
</style>

<?php admin_nav(); ?>

<div class="container">
    <h2>👥 Users Management</h2>

    <!-- SEARCH -->
    <form class="search-box" method="get">
        <input type="text" name="search" placeholder="Search username or email"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn">Search</button>
    </form>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge badge-admin">Admin</span>
                        <?php else: ?>
                            <span class="badge badge-user">User</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- CHANGE ROLE -->
                        <form method="post" action="user_role.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button class="btn btn-role">
                                <?= $u['role'] === 'admin' ? 'Make User' : 'Make Admin' ?>
                            </button>
                        </form>

                        <!-- DELETE USER -->
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <form method="post" action="user_delete.php" style="display:inline;"
                                  onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button class="btn btn-delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
