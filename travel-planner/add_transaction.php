<?php

declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}
// add_transaction.php

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);

$description = $data['description'];
$amount = (float)$data['amount'];
$type = $data['type'];
$category = $data['category'];
$date = $data['date'];

$stmt = $conn->prepare("INSERT INTO transactions (user_id, description, amount, type, category, date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdsss", $user_id, $description, $amount, $type, $category, $date);

if ($stmt->execute()) {
    // Get the ID of the new transaction we just inserted
    $new_id = $stmt->insert_id;
    // Send back the full transaction object, just as fetch_transactions.php would
    echo json_encode([
        'success' => true,
        'transaction' => [
            'id' => $new_id,
            'description' => $description,
            'amount' => $amount,
            'type' => $type,
            'category' => $category,
            'date' => $date
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding transaction.']);
}

$stmt->close();
$conn->close();
?>