<?php
// Database connection settings - change these if your XAMPP setup is different
$db_host = 'sql310.infinityfree.com';
$db_user = 'if0_42461471';
$db_pass = 'Ashvin8872';
$db_name = 'if0_42461471_skillhub';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error . '. Make sure MySQL is running in XAMPP and the "skillhub" database has been imported.');
}

$conn->set_charset('utf8mb4');
