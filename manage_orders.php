<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$userId = currentUserId();

$stmt = $conn->prepare("
    SELECT o.order_id, o.status, o.order_date, o.deadline, g.title,
           cl.name AS client_name, p.package_type, p.price, pay.payment_status
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    JOIN users cl ON o.client_id = cl.user_id
    JOIN gig_packages p ON o.package_id = p.package_id
    JOIN payments pay ON pay.order_id = o.order_id
    WHERE o.freelancer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result();

$pageTitle = "Manage Orders";
include __DIR__ . '/../includes/header.php';

function statusClass($status) {
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>

<h1>Manage Orders</h1>

<div class="table-wrap">
<table class="basic-table">
    <tr><th>Gig</th><th>Client</th><th>Package</th><th>Payment</th><th>Status</th><th>Deadline</th><th>Actions</th></tr>
    <?php if ($orders->num_rows > 0): ?>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($o['title']); ?></td>
                <td><?php echo clean($o['client_name']); ?></td>
                <td><?php echo clean(ucfirst($o['package_type'])); ?></td>
                <td><?php echo clean($o['payment_status']); ?></td>
                <td><span class="status-badge <?php echo statusClass($o['status']); ?>"><?php echo clean($o['status']); ?></span></td>
                <td><?php echo $o['deadline'] ? date('d M Y', strtotime($o['deadline'])) : '-'; ?></td>
                <td>
                    <a href="freelancer/chat.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small btn-secondary">Chat</a>
                    <?php if ($o['payment_status'] === 'Paid' && $o['status'] !== 'Completed' && $o['status'] !== 'Cancelled'): ?>
                        <a href="freelancer/update_order.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-small">Update</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No orders yet.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
