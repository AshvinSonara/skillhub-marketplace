<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$userId = currentUserId();

$gigCount = $conn->prepare("SELECT COUNT(*) AS c FROM gigs WHERE freelancer_id = ?");
$gigCount->bind_param("i", $userId);
$gigCount->execute();
$gigCount = $gigCount->get_result()->fetch_assoc()['c'];

$activeOrders = $conn->prepare("SELECT COUNT(*) AS c FROM orders WHERE freelancer_id = ? AND status IN ('Pending','In Progress')");
$activeOrders->bind_param("i", $userId);
$activeOrders->execute();
$activeOrders = $activeOrders->get_result()->fetch_assoc()['c'];

$earnings = $conn->prepare("
    SELECT COALESCE(SUM(pay.amount),0) AS total
    FROM payments pay JOIN orders o ON pay.order_id = o.order_id
    WHERE o.freelancer_id = ? AND pay.payment_status = 'Paid'
");
$earnings->bind_param("i", $userId);
$earnings->execute();
$earnings = $earnings->get_result()->fetch_assoc()['total'];

$avgRating = $conn->prepare("
    SELECT ROUND(AVG(r.rating),1) AS avg_r, COUNT(*) AS cnt
    FROM reviews r JOIN orders o ON r.order_id = o.order_id
    WHERE o.freelancer_id = ?
");
$avgRating->bind_param("i", $userId);
$avgRating->execute();
$avgRating = $avgRating->get_result()->fetch_assoc();

$recentOrders = $conn->prepare("
    SELECT o.order_id, o.status, o.order_date, g.title, cl.name AS client_name
    FROM orders o JOIN gigs g ON o.gig_id = g.gig_id JOIN users cl ON o.client_id = cl.user_id
    WHERE o.freelancer_id = ? ORDER BY o.order_date DESC LIMIT 5
");
$recentOrders->bind_param("i", $userId);
$recentOrders->execute();
$recentOrders = $recentOrders->get_result();

$pageTitle = "Freelancer Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<h1>Welcome, <?php echo clean($_SESSION['name']); ?></h1>

<div class="stats-row">
    <div class="stat-box"><div class="stat-num"><?php echo $gigCount; ?></div><div>My Gigs</div></div>
    <div class="stat-box"><div class="stat-num"><?php echo $activeOrders; ?></div><div>Active Orders</div></div>
    <div class="stat-box"><div class="stat-num"><?php echo formatMoney($earnings); ?></div><div>Earnings</div></div>
    <div class="stat-box"><div class="stat-num"><?php echo $avgRating['avg_r'] ?? '—'; ?></div><div>Avg Rating (<?php echo $avgRating['cnt'] ?? 0; ?>)</div></div>
</div>

<p><a href="freelancer/add_gig.php" class="btn">+ Add New Gig</a> <a href="freelancer/my_gigs.php" class="btn btn-secondary">My Gigs</a> <a href="freelancer/manage_orders.php" class="btn btn-secondary">Manage Orders</a></p>

<h3 class="mt-25">Recent Orders</h3>
<div class="table-wrap">
<table class="basic-table">
    <tr><th>Gig</th><th>Client</th><th>Status</th><th>Date</th></tr>
    <?php if ($recentOrders->num_rows > 0): ?>
        <?php while ($o = $recentOrders->fetch_assoc()): ?>
            <tr>
                <td><?php echo clean($o['title']); ?></td>
                <td><?php echo clean($o['client_name']); ?></td>
                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $o['status'])); ?>"><?php echo clean($o['status']); ?></span></td>
                <td><?php echo date('d M Y', strtotime($o['order_date'])); ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">No orders yet.</td></tr>
    <?php endif; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
