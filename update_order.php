<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (int)($_POST['order_id'] ?? 0);
$userId = currentUserId();

$stmt = $conn->prepare("
    SELECT o.order_id, o.status, g.title, cl.name AS client_name
    FROM orders o JOIN gigs g ON o.gig_id = g.gig_id JOIN users cl ON o.client_id = cl.user_id
    WHERE o.order_id = ? AND o.freelancer_id = ?
");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirectWithMessage('freelancer/manage_orders.php', 'Order not found.', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'];
    $allowedStatuses = ['In Progress', 'Delivered', 'Cancelled'];

    if (!in_array($newStatus, $allowedStatuses)) {
        $errors[] = "Invalid status selected.";
    }

    // if marking as Delivered, a delivery file is required
    if ($newStatus === 'Delivered' && empty($_FILES['delivery_file']['name'])) {
        $errors[] = "Please attach a delivery file when marking the order as Delivered.";
    }

    if (empty($errors)) {
        if (!empty($_FILES['delivery_file']['name'])) {
            $newName = 'delivery_' . $orderId . '_' . time() . '_' . basename($_FILES['delivery_file']['name']);
            $target = __DIR__ . '/../uploads/' . $newName;
            if (move_uploaded_file($_FILES['delivery_file']['tmp_name'], $target)) {
                $ins = $conn->prepare("INSERT INTO deliveries (order_id, file_path) VALUES (?, ?)");
                $ins->bind_param("is", $orderId, $newName);
                $ins->execute();
                $ins->close();
            } else {
                $errors[] = "Could not upload the delivery file. Please try again.";
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $newStatus, $orderId);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('freelancer/manage_orders.php', 'Order status updated to ' . $newStatus . '.');
        }
    }
}

$pageTitle = "Update Order";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:460px; margin:0 auto;">
    <h1>Update Order</h1>
    <p class="small-text"><?php echo clean($order['title']); ?> &mdash; Client: <?php echo clean($order['client_name']); ?></p>
    <p>Current Status: <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo clean($order['status']); ?></span></p>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="freelancer/update_order.php?order_id=<?php echo $orderId; ?>" enctype="multipart/form-data">
        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
        <div class="form-group">
            <label for="status">New Status</label>
            <select id="status" name="status" required>
                <option value="In Progress" <?php echo $order['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div class="form-group">
            <label for="delivery_file">Delivery File <span class="small-text">(required when marking Delivered)</span></label>
            <input type="file" id="delivery_file" name="delivery_file">
        </div>
        <button type="submit" class="btn">Update Order</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
