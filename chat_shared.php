<?php
// Included by client/chat.php and freelancer/chat.php.
// Expects $requiredRole ('client' or 'freelancer') to be set before including this file,
// and auth_check.php to already be required.

requireLogin($requiredRole);

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$userId = currentUserId();

// Make sure this user is actually part of the order (either as client or freelancer)
$stmt = $conn->prepare("
    SELECT o.order_id, o.client_id, o.freelancer_id, g.title,
           cl.name AS client_name, fr.name AS freelancer_name
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    JOIN users cl ON o.client_id = cl.user_id
    JOIN users fr ON o.freelancer_id = fr.user_id
    WHERE o.order_id = ? AND (o.client_id = ? OR o.freelancer_id = ?)
");
$stmt->bind_param("iii", $orderId, $userId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirectWithMessage($requiredRole . '/dashboard.php', 'Order not found or you do not have access to it.', 'error');
}

$otherPartyName = ($userId == $order['client_id']) ? $order['freelancer_name'] : $order['client_name'];

// Handle sending a new message
// Note: chat updates via normal page reload (not AJAX/fetch), so it works reliably
// on free hosts like InfinityFree, whose bot-protection can block fetch()/XHR requests.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim($_POST['message_text'] ?? '') !== '') {
    $text = clean($_POST['message_text']);
    $stmt = $conn->prepare("INSERT INTO messages (order_id, sender_id, message_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $orderId, $userId, $text);
    $stmt->execute();
    $stmt->close();
    redirectTo($requiredRole . '/chat.php?order_id=' . $orderId);
}

$msgStmt = $conn->prepare("SELECT sender_id, message_text, sent_at FROM messages WHERE order_id = ? ORDER BY sent_at ASC");
$msgStmt->bind_param("i", $orderId);
$msgStmt->execute();
$chatMessages = $msgStmt->get_result();
