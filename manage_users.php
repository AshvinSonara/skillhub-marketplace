<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('admin');

$users = $conn->query("SELECT user_id, name, email, role, status, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");

$pageTitle = "Manage Users";
include __DIR__ . '/../includes/header.php';
?>

<h1>Manage Users</h1>

<div class="table-wrap">
<table class="basic-table">
    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    <?php if ($users->num_rows > 0): ?>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($u['name']); ?></td>
                <td><?php echo clean($u['email']); ?></td>
                <td><?php echo ucfirst($u['role']); ?></td>
                <td>
                    <span class="status-badge <?php echo $u['status'] === 'active' ? 'status-completed' : 'status-cancelled'; ?>">
                        <?php echo ucfirst($u['status']); ?>
                    </span>
                </td>
                <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if ($u['status'] === 'active'): ?>
                        <a href="admin/toggle_user.php?id=<?php echo $u['user_id']; ?>&action=block" class="btn btn-small btn-danger confirm-action" data-confirm="Block this user?">Block</a>
                    <?php else: ?>
                        <a href="admin/toggle_user.php?id=<?php echo $u['user_id']; ?>&action=unblock" class="btn btn-small">Unblock</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No users found.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
