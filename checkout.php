<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('client');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$clientId = currentUserId();

$stmt = $conn->prepare("
    SELECT o.order_id, o.status, g.title, p.package_type, p.price, pay.payment_status, pay.payment_id
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    JOIN gig_packages p ON o.package_id = p.package_id
    JOIN payments pay ON pay.order_id = o.order_id
    WHERE o.order_id = ? AND o.client_id = ?
");
$stmt->bind_param("ii", $orderId, $clientId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirectWithMessage('client/my_orders.php', 'Order not found.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $stmt = $conn->prepare("UPDATE payments SET payment_status = 'Paid', payment_date = NOW() WHERE payment_id = ?");
    $stmt->bind_param("i", $order['payment_id']);
    $stmt->execute();
    $stmt->close();
    redirectWithMessage('client/my_orders.php', 'Payment successful! Your order has been placed.');
}

$pageTitle = "Checkout";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:480px; margin:0 auto;">
    <h1>Checkout</h1>

    <div class="note" style="background:#fdf3d9; border:1px solid #e0c164; padding:12px 16px; border-radius:6px; margin-bottom:18px; font-size:13.5px;">
        This is a <strong>simulated payment</strong> for demonstration purposes only — no real transaction, card details, or KYC verification is processed.
    </div>

    <table class="basic-table" style="margin-bottom:20px;">
        <tr><td>Gig</td><td><?php echo clean($order['title']); ?></td></tr>
        <tr><td>Package</td><td><?php echo clean(ucfirst($order['package_type'])); ?></td></tr>
        <tr><td>Amount</td><td><strong><?php echo formatMoney($order['price']); ?></strong></td></tr>
        <tr><td>Payment Status</td><td><?php echo clean($order['payment_status']); ?></td></tr>
    </table>

    <?php if ($order['payment_status'] === 'Paid'): ?>
        <p class="small-text">This order has already been paid.</p>
        <a href="client/my_orders.php" class="btn btn-block">Go to My Orders</a>
    <?php else: ?>
        <form method="POST" action="client/checkout.php?order_id=<?php echo $orderId; ?>">
            <button type="submit" name="pay_now" value="1" class="btn btn-block">Pay Now (Dummy)</button>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
