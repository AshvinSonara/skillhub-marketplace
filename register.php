<?php
require_once __DIR__ . '/../includes/auth_check.php';

if (isLoggedIn()) {
    redirectTo('index.php');
}

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'] === 'freelancer' ? 'freelancer' : 'client';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Please fill in all fields.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed, $role);
        if ($stmt->execute()) {
            $stmt->close();
            redirectWithMessage('auth/login.php', 'Registration successful. Please log in.');
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

$pageTitle = "Register";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:460px; margin:0 auto;">
    <h1>Create an Account</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="auth/register.php">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo clean($name); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo clean($email); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
        </div>
        <div class="form-group">
            <label for="role">Register as</label>
            <select id="role" name="role">
                <option value="client">Client (I want to hire)</option>
                <option value="freelancer">Freelancer (I want to sell services)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-block">Register</button>
    </form>

    <p class="mt-15 small-text">Already have an account? <a href="auth/login.php">Login here</a></p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
