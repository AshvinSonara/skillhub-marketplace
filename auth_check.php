<?php
require_once __DIR__ . '/functions.php';

// Call this at the top of any page that requires login.
// Pass a role ('client' | 'freelancer' | 'admin') to also restrict by role.
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        redirectWithMessage('auth/login.php', 'Please log in to continue.', 'error');
    }
    if ($role !== null && currentRole() !== $role) {
        redirectWithMessage('index.php', 'You do not have access to that page.', 'error');
    }
}
