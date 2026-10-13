<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();

$userId = currentUserId();
$errors = [];

$stmt = $conn->prepare("SELECT name, email, bio, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $bio = clean($_POST['bio']);

    if ($name === '') {
        $errors[] = "Name cannot be empty.";
    }

    $profilePic = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../uploads/' . $newName;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $profilePic = $newName;
            } else {
                $errors[] = "Could not upload the image. Please try again.";
            }
        } else {
            $errors[] = "Profile picture must be a JPG, PNG or GIF file.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, bio = ?, profile_pic = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $name, $bio, $profilePic, $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['name'] = $name;
        redirectWithMessage('auth/profile.php', 'Profile updated successfully.');
    }
}

$pageTitle = "My Profile";
include __DIR__ . '/../includes/header.php';
?>

<div class="basic-form" style="max-width:480px; margin:0 auto;">
    <h1>My Profile</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($user['profile_pic'])): ?>
        <img src="uploads/<?php echo clean($user['profile_pic']); ?>" alt="Profile picture" style="width:90px; height:90px; object-fit:cover; border-radius:50%; margin-bottom:16px; border:1px solid var(--border);">
    <?php endif; ?>

    <form method="POST" action="auth/profile.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo clean($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo clean($user['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" placeholder="A short line about yourself..."><?php echo clean($user['bio']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="profile_pic">Profile Picture</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
        </div>
        <button type="submit" class="btn">Save Changes</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
