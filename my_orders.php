<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('client');

$clientId = currentUserId();

$stmt = $conn->prepare("
    SELECT o.order_id, o.status, o.order_date, g.gig_id, g.title, f.name AS freelancer_name,
           p.package_type, p.price, pay.payment_status,
           (SELECT COUNT(*) FROM reviews WHERE order_id = o.order_id) AS has_review
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    JOIN users f ON o.freelancer_id = f.user_id
    JOIN gig_packages p ON o.package_id = p.package_id
    JOIN payments pay ON pay.order_id = o.order_id
    WHERE o.client_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$orders = $stmt->get_result();

$pageTitle = "My Orders";
include __DIR__ . '/../includes/header.php';

function statusClass($status) {
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>

<h1>My Orders</h1>

<div class="table-wrap">
<table class="basic-table">
    <tr>
        <th>Gig</th><th>Freelancer</th><th>Package</th><th>Price</th>
        <th>Payment</th><th>Status</th><th>Actions</th>
    </tr>
    <?php if ($orders->num_rows > 0): ?>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($o['title']); ?></td>
                <td><?php echo clean($o['freelancer_name']); ?></td>
                <td><?php echo clean(ucfirst($o['package_type'])); ?></td>
                <td><?php echo formatMoney($o['price']); ?></td>
                <td><?php echo clean($o['payment_status']); ?></td>
                <td><span class="status-badge <?php echo statusClass($o['status']); ?>"><?php echo clean($o['status']); ?></span></td>
                <td>
                    <?php if ($o['payment_status'] !== 'Paid'): ?>
                        <a href="client/checkout.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small">Pay Now</a>
                    <?php endif; ?>
                    <a href="client/chat.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small btn-secondary">Chat</a>
                    <?php if ($o['status'] === 'Delivered'): ?>
                        <a href="client/mark_completed.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small confirm-action" data-confirm="Mark this order as completed?">Mark Completed</a>
                    <?php endif; ?>
                    <?php if ($o['status'] === 'Completed' && $o['has_review'] == 0): ?>
                        <a href="client/submit_review.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small btn-secondary">Leave Review</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No orders yet. <a href="client/browse_gigs.php">Browse gigs</a> to get started.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
