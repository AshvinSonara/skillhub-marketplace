<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANT: This must match your project's folder name inside htdocs.
// Every internal link in this project is written relative to this root
// (e.g. href="auth/login.php"), and the <base> tag in header.php uses
// this constant to make those links work correctly from any page.
define('BASE_URL', '/');

require_once __DIR__ . '/../config/db_connect.php';

// Escape output safely
function clean($str) {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentRole() {
    return $_SESSION['role'] ?? null;
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Redirect to a page relative to project root, e.g. redirectTo('client/dashboard.php')
function redirectTo($path) {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

// Redirect with a flash message shown on the next page
function redirectWithMessage($path, $message, $type = 'success') {
    $_SESSION['flash'] = $message;
    $_SESSION['flash_type'] = $type;
    redirectTo($path);
}

// Fetch and clear the flash message, if any
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $data = [
            'message' => $_SESSION['flash'],
            'type' => $_SESSION['flash_type'] ?? 'success',
        ];
        unset($_SESSION['flash'], $_SESSION['flash_type']);
        return $data;
    }
    return null;
}

// Simple star rating as text (e.g. for use inside <span class="stars">)
function starRating($rating) {
    $rating = round((float)$rating);
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '&#9733;' : '&#9734;'; // filled / empty star
    }
    return $out;
}

function formatMoney($amount) {
    return '&#8377;' . number_format((float)$amount, 2);
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

// Returns a background colour (hex, no #) for a category placeholder thumbnail,
// so gigs without an uploaded image still look reasonably designed.
function categoryColor($categoryName) {
    $palette = ['2C7A7B', '2B6CB0', 'B7791F', '6B46C1', 'C05621', '2F855A'];
    $index = crc32($categoryName ?? '') % count($palette);
    return $palette[abs($index)];
}
