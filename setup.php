<?php
// Run this file ONCE in your browser right after importing database.sql.
// It safely hashes the demo account passwords using PHP's own password_hash()
// function on your machine (this is more reliable than shipping pre-hashed
// passwords inside the .sql file, since hash formats can vary by system).
//
// URL: http://localhost/skillhub-marketplace/setup.php
//
// After you see "Setup complete", you can delete this file - it is not
// needed for the app to run.

require_once __DIR__ . '/config/db_connect.php';

$demoAccounts = [
    'admin@skillhub.com' => 'admin123',
    'rahul@skillhub.com' => 'password123',
    'priya@skillhub.com' => 'password123',
    'aman@skillhub.com'  => 'password123',
];

$results = [];
foreach ($demoAccounts as $email => $plainPassword) {
    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed, $email);
    $stmt->execute();
    $results[] = [$email, $plainPassword, $stmt->affected_rows > 0 ? 'Updated' : 'No matching row found'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SkillHub Setup</title>
<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 60px auto; color: #222; }
table { border-collapse: collapse; width: 100%; margin-top: 16px; }
th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; font-size: 14px; }
th { background: #eef3f3; }
h1 { color: #2c7a7b; }
.note { background: #fdf3d9; border: 1px solid #e0c164; padding: 12px 16px; border-radius: 6px; margin-top: 20px; font-size: 14px; }
</style>
</head>
<body>
<h1>Setup Complete</h1>
<p>Demo account passwords have been securely set:</p>
<table>
<tr><th>Email</th><th>Password</th><th>Status</th></tr>
<?php foreach ($results as $r): ?>
<tr><td><?php echo htmlspecialchars($r[0]); ?></td><td><?php echo htmlspecialchars($r[1]); ?></td><td><?php echo htmlspecialchars($r[2]); ?></td></tr>
<?php endforeach; ?>
</table>
<div class="note">For safety, please delete <strong>setup.php</strong> from your project folder now that setup is done.</div>
<p><a href="index.php">Go to the homepage &rarr;</a></p>
</body>
</html>
