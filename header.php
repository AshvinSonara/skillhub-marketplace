<?php
// Expects functions.php to already be included (session started, BASE_URL defined)
$role = currentRole();

// Build the base href dynamically from the current host, so this also
// works if the site is opened from another device on the same network
// (e.g. a phone), not just from "localhost".
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$dynamicBase = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<base href="<?php echo $dynamicBase; ?>">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? clean($pageTitle) . ' - SkillHub' : 'SkillHub Marketplace'; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">SkillHub<span>Marketplace</span></a>
        <button type="button" class="nav-toggle">Menu &#9776;</button>
        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="client/browse_gigs.php">Browse Gigs</a>

            <?php if (!isLoggedIn()): ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">Register</a>
            <?php else: ?>
                <?php if ($role === 'client'): ?>
                    <a href="client/dashboard.php">Dashboard</a>
                    <a href="client/my_orders.php">My Orders</a>
                <?php elseif ($role === 'freelancer'): ?>
                    <a href="freelancer/dashboard.php">Dashboard</a>
                    <a href="freelancer/my_gigs.php">My Gigs</a>
                    <a href="freelancer/manage_orders.php">Orders</a>
                <?php elseif ($role === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a href="auth/profile.php">Profile</a>
                <span class="nav-user">Hi, <?php echo clean($_SESSION['name']); ?></span>
                <a href="auth/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container page-content">
<?php
$flash = getFlash();
if ($flash) {
    $cls = $flash['type'] === 'error' ? 'flash-message error' : 'flash-message';
    echo '<div class="' . $cls . '">' . clean($flash['message']) . '</div>';
}
?>
