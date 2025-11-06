<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/db_connect.php';

// Helper to read either form POST or JSON payload
function read_request_body(): array {
    $body = $_POST ?? [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($body)) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $body = $json;
            }
        }
    }
    return $body;
}

// GET -> show a minimal working form so we can test submissions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Signup (diag)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem;}
  form{max-width:420px;display:grid;gap:.75rem}
  input,button{padding:.6rem .75rem;font-size:1rem}
  .ok{color:green}.err{color:#b00020}
</style>
</head>
<body>
<h2>Diagnostic Signup Form</h2>
<p>Submit this simple form. If it works, your original form/JS has the issue (names, action, or JS preventing submit).</p>

<form action="/journey.AI/travel-planner/signup.php" method="post">
  <input type="text"     name="name"              placeholder="Your name" required>
  <input type="email"    name="email"             placeholder="you@example.com" required>
  <input type="password" name="password"          placeholder="Password (min 6)" minlength="6" required>
  <input type="password" name="confirm_password"  placeholder="Confirm password" minlength="6" required>
  <button type="submit">Sign up</button>
</form>

<hr>
<h3>Or test via fetch() (AJAX)</h3>
<button id="testFetch">Send JSON request</button>
<pre id="out"></pre>
<script>
document.getElementById('testFetch').onclick = async () => {
  const res = await fetch('/journey.AI/travel-planner/signup.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      name: 'Test User',
      email: 'test'+Math.floor(Math.random()*9999)+'@example.com',
      password: 'secret123',
      confirm_password: 'secret123'
    })
  });
  const text = await res.text();
  document.getElementById('out').textContent = 'Status ' + res.status + ':\n' + text;
};
</script>
</body>
</html>
<?php
    exit;
}

// POST -> process submission
$body = read_request_body();

// normalize fields
$name     = trim((string)($body['name'] ?? ''));
$email    = trim((string)($body['email'] ?? ''));
$password = (string)($body['password'] ?? '');
$confirm  = (string)($body['confirm_password'] ?? '');

// validate
$errors = [];
if ($name === '') { $errors[] = 'Name is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
if (strlen($password) < 6) { $errors[] = 'Password must be at least 6 characters.'; }
if ($password !== $confirm) { $errors[] = 'Passwords do not match.'; }

if ($errors) {
    http_response_code(422);
    echo implode("<br>", $errors);
    exit;
}

try {
    // ensure table
    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // unique email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->bind_param('s', $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        http_response_code(409);
        echo 'This email is already registered.';
        $check->close();
        exit;
    }
    $check->close();

    // insert
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $email, $hash);
    $stmt->execute();
    $stmt->close();

    // success
    echo 'Signup successful.';
    // header('Location: login.php'); exit;

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo 'Database error: ' . htmlspecialchars($e->getMessage());
}
