<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('client');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('client/browse_gigs.php');
}

$gigId = (int)$_POST['gig_id'];
$packageId = (int)$_POST['package_id'];
$clientId = currentUserId();

// verify gig + package are valid and get freelancer_id + price
$stmt = $conn->prepare("
    SELECT g.freelancer_id, p.price, p.delivery_days
    FROM gig_packages p JOIN gigs g ON p.gig_id = g.gig_id
    WHERE p.package_id = ? AND p.gig_id = ? AND g.status = 'active'
");
$stmt->bind_param("ii", $packageId, $gigId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    redirectWithMessage('client/browse_gigs.php', 'Invalid package selected.', 'error');
}

if ($row['freelancer_id'] == $clientId) {
    redirectWithMessage('client/browse_gigs.php', 'You cannot order your own gig.', 'error');
}

$deadline = date('Y-m-d', strtotime('+' . (int)$row['delivery_days'] . ' days'));

$stmt = $conn->prepare("INSERT INTO orders (gig_id, package_id, client_id, freelancer_id, status, deadline) VALUES (?, ?, ?, ?, 'Pending', ?)");
$stmt->bind_param("iiiis", $gigId, $packageId, $clientId, $row['freelancer_id'], $deadline);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_status) VALUES (?, ?, 'Pending')");
$stmt->bind_param("id", $orderId, $row['price']);
$stmt->execute();
$stmt->close();

redirectTo('client/checkout.php?order_id=' . $orderId);
