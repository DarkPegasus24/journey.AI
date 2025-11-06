<?php

declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}
// update_transaction.php

include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$description = $data['description'];
$amount = (float)$data['amount'];
$type = $data['type'];
$category = $data['category'];
$date = $data['date'];

// We add "user_id = ?" to the WHERE clause to make sure a user
// can't accidentally update someone else's transaction.
$stmt = $conn->prepare("UPDATE transactions SET description = ?, amount = ?, type = ?, category = ?, date = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sdsssii", $description, $amount, $type, $category, $date, $id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating transaction.']);
}

$stmt->close();
$conn->close();
?>