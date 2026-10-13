<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('client');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (int)($_POST['order_id'] ?? 0);
$clientId = currentUserId();

$stmt = $conn->prepare("
    SELECT o.order_id, o.status, g.title,
           (SELECT COUNT(*) FROM reviews WHERE order_id = o.order_id) AS has_review
    FROM orders o JOIN gigs g ON o.gig_id = g.gig_id
    WHERE o.order_id = ? AND o.client_id = ?
");
$stmt->bind_param("ii", $orderId, $clientId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order || $order['status'] !== 'Completed' || $order['has_review'] > 0) {
    redirectWithMessage('client/my_orders.php', 'This order is not available for review.', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = clean($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a rating between 1 and 5.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO reviews (order_id, client_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $orderId, $clientId, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        redirectWithMessage('client/my_orders.php', 'Thank you for your review!');
    }
}

$pageTitle = "Leave a Review";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:460px; margin:0 auto;">
    <h1>Leave a Review</h1>
    <p class="small-text">For: <?php echo clean($order['title']); ?></p>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="client/submit_review.php?order_id=<?php echo $orderId; ?>">
        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
        <div class="form-group">
            <label for="rating">Rating</label>
            <select id="rating" name="rating" required>
                <option value="">Select rating</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Below Average</option>
                <option value="1">1 - Poor</option>
            </select>
        </div>
        <div class="form-group">
            <label for="comment">Comment</label>
            <textarea id="comment" name="comment" placeholder="Share your experience..."></textarea>
        </div>
        <button type="submit" class="btn btn-block">Submit Review</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
