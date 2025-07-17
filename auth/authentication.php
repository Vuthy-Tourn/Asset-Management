<?php
require_once __DIR__ . '/../components/config/db.php';
require_once __DIR__ . '/../components/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php?error=empty");
    exit();
}


$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: login.php?error=empty");
    exit();
}

$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
if (!$stmt) {
    header("Location: login.php?error=db");
    exit();
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    header("Location: login.php?error=db");
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Delay to prevent timing attacks
    usleep(random_int(100000, 500000));
    header("Location: login.php?error=auth");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    header("Location: login.php?error=auth");
    exit();
}

// Regenerate session ID
session_regenerate_id(true);

// Set session variables
$_SESSION = [
    'user_id' => $user['id'],
    'email' => $user['email'],
    'logged_in' => true,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'last_activity' => time()
];

// Redirect to dashboard
flash('success', 'Logged in successfully!');
header("Location: ../index.php?success=login");
exit();
