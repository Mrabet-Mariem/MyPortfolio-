<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Sanitize & Validate ──────────────────────────────────────
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

$errors = [];

if (empty($name) || mb_strlen($name) < 2) {
    $errors[] = 'Invalid name.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}

if (empty($subject)) {
    $errors[] = 'Subject is required.';
}

if (empty($message) || mb_strlen($message) < 10) {
    $errors[] = 'Message too short.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// ── Rate Limiting (simple IP-based) ─────────────────────────
session_start();
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';
$nowTime = time();
$window  = 300; // 5 minutes
$limit   = 3;

if (!isset($_SESSION['contact_attempts'])) {
    $_SESSION['contact_attempts'] = [];
}

// Clean old entries
$_SESSION['contact_attempts'] = array_filter(
    $_SESSION['contact_attempts'],
    fn($ts) => $nowTime - $ts < $window
);

if (count($_SESSION['contact_attempts']) >= $limit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many requests. Please wait a few minutes.'
    ]);
    exit;
}

// ── Insert to DB ─────────────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$stmt->bind_param('ssss', $name, $email, $subject, $message);

if ($stmt->execute()) {
    $_SESSION['contact_attempts'][] = $nowTime;
    echo json_encode([
        'success' => true,
        'message' => 'Message saved successfully.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save message: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
