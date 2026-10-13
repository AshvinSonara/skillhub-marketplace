<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';
$newStatus = $action === 'block' ? 'blocked' : 'active';

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role != 'admin'");
$stmt->bind_param("si", $newStatus, $id);
$stmt->execute();
$stmt->close();

redirectWithMessage('admin/manage_users.php', 'User status updated.');
