<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: application/json');

require __DIR__ . '/db_connect.php';
session_start();

// Read POST or JSON body
$body = $_POST;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($body)) {
  $raw = file_get_contents('php://input');
  $json = json_decode($raw, true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
    $body = $json;
  }
}

$email = trim($body['email'] ?? '');
$password = (string)($body['password'] ?? '');

if ($email === '' || $password === '') {
  echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
  exit;
}

// Detect actual columns in users table (name/username + password column)
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM users");
while ($row = $res->fetch_assoc()) { $cols[strtolower($row['Field'])] = true; }
$res->close();

$userCol = isset($cols['name']) ? 'name' : (isset($cols['username']) ? 'username' : null);
$pwdCol  = null;
foreach (['password','pass','pwd','user_password','password_hash'] as $c) {
  if (isset($cols[$c])) { $pwdCol = $c; break; }
}
if (!$pwdCol) {
  echo json_encode(['success' => false, 'message' => 'Password column not found in users table.']);
  exit;
}

$selectCols = $userCol ? "id, $userCol, $pwdCol" : "id, $pwdCol";
$stmt = $conn->prepare("SELECT $selectCols FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
  echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
  exit;
}

$hash = $user[$pwdCol];
if (!password_verify($password, $hash)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
  exit;
}

// Success → set session
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $userCol ? (string)$user[$userCol] : '';
$_SESSION['email'] = $email;

echo json_encode(['success' => true, 'message' => 'Login successful!', 'username' => $_SESSION['username']]);
