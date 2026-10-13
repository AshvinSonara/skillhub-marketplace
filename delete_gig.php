<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$gigId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = currentUserId();

$stmt = $conn->prepare("DELETE FROM gigs WHERE gig_id = ? AND freelancer_id = ?");
$stmt->bind_param("ii", $gigId, $userId);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

redirectWithMessage('freelancer/my_gigs.php', $deleted ? 'Gig deleted.' : 'Could not delete that gig.');
