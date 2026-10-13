<?php
require_once __DIR__ . '/../includes/auth_check.php';

if (isLoggedIn()) {
    redirectTo('index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, name, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] === 'blocked') {
            $errors[] = "This account has been blocked. Contact the admin.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                redirectTo('admin/dashboard.php');
            } elseif ($user['role'] === 'freelancer') {
                redirectTo('freelancer/dashboard.php');
            } else {
                redirectTo('client/dashboard.php');
            }
        } else {
            $errors[] = "Incorrect email or password.";
        }
    } else {
        $errors[] = "Incorrect email or password.";
    }
    $stmt->close();
}

$pageTitle = "Login";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:420px; margin:0 auto;">
    <h1>Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="auth/login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo clean($email); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
    </form>

    <p class="mt-15 small-text">Don't have an account? <a href="auth/register.php">Register here</a></p>
    <p class="small-text">Demo admin login: admin@skillhub.com / admin123</p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
