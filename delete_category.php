<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// only delete if no gigs are using this category
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM gigs WHERE category_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

if ($count == 0) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    redirectWithMessage('admin/manage_categories.php', 'Category deleted.');
} else {
    redirectWithMessage('admin/manage_categories.php', 'Cannot delete a category that is in use.', 'error');
}
