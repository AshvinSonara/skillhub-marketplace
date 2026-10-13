<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('client');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$clientId = currentUserId();

$stmt = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE order_id = ? AND client_id = ? AND status = 'Delivered'");
$stmt->bind_param("ii", $orderId, $clientId);
$stmt->execute();
$updated = $stmt->affected_rows > 0;
$stmt->close();

redirectWithMessage('client/my_orders.php', $updated ? 'Order marked as completed. You can now leave a review!' : 'Could not update this order.');
