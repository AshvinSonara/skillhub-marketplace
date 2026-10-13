<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('admin');

$gigs = $conn->query("
    SELECT g.gig_id, g.title, g.status, u.name AS freelancer_name, c.category_name,
           (SELECT MIN(price) FROM gig_packages WHERE gig_id = g.gig_id) AS starting_price
    FROM gigs g
    JOIN users u ON g.freelancer_id = u.user_id
    JOIN categories c ON g.category_id = c.category_id
    ORDER BY g.created_at DESC
");

$pageTitle = "Manage Gigs";
include __DIR__ . '/../includes/header.php';
?>

<h1>Manage Gigs</h1>

<div class="table-wrap">
<table class="basic-table">
    <tr><th>Title</th><th>Freelancer</th><th>Category</th><th>Starting Price</th><th>Status</th><th>Actions</th></tr>
    <?php if ($gigs->num_rows > 0): ?>
        <?php while ($g = $gigs->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($g['title']); ?></td>
                <td><?php echo clean($g['freelancer_name']); ?></td>
                <td><?php echo clean($g['category_name']); ?></td>
                <td><?php echo formatMoney($g['starting_price']); ?></td>
                <td><?php echo ucfirst($g['status']); ?></td>
                <td>
                    <a href="client/gig_detail.php?id=<?php echo $g['gig_id']; ?>" class="btn btn-small btn-secondary">View</a>
                    <a href="admin/delete_gig.php?id=<?php echo $g['gig_id']; ?>" class="btn btn-small btn-danger confirm-action" data-confirm="Delete this gig? This cannot be undone.">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No gigs found.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
