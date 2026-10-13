<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$userId = currentUserId();

$stmt = $conn->prepare("
    SELECT g.gig_id, g.title, g.status, c.category_name,
           (SELECT MIN(price) FROM gig_packages WHERE gig_id = g.gig_id) AS starting_price,
           (SELECT COUNT(*) FROM orders WHERE gig_id = g.gig_id) AS order_count
    FROM gigs g JOIN categories c ON g.category_id = c.category_id
    WHERE g.freelancer_id = ?
    ORDER BY g.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$gigs = $stmt->get_result();

$pageTitle = "My Gigs";
include __DIR__ . '/../includes/header.php';
?>

<h1>My Gigs</h1>
<p><a href="freelancer/add_gig.php" class="btn">+ Add New Gig</a></p>

<div class="table-wrap">
<table class="basic-table">
    <tr><th>Title</th><th>Category</th><th>Starting Price</th><th>Orders</th><th>Status</th><th>Actions</th></tr>
    <?php if ($gigs->num_rows > 0): ?>
        <?php while ($g = $gigs->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($g['title']); ?></td>
                <td><?php echo clean($g['category_name']); ?></td>
                <td><?php echo formatMoney($g['starting_price']); ?></td>
                <td><?php echo (int)$g['order_count']; ?></td>
                <td><?php echo ucfirst($g['status']); ?></td>
                <td>
                    <a href="client/gig_detail.php?id=<?php echo $g['gig_id']; ?>" class="btn btn-small btn-secondary">View</a>
                    <a href="freelancer/add_gig.php?id=<?php echo $g['gig_id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                    <a href="freelancer/delete_gig.php?id=<?php echo $g['gig_id']; ?>" class="btn btn-small btn-danger confirm-action" data-confirm="Delete this gig? This cannot be undone.">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">You haven't posted any gigs yet. <a href="freelancer/add_gig.php">Create your first gig</a>.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
