<?php

declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}
// fetch_transactions.php
// This file gets all transactions FOR THE LOGGED-IN USER from the database.

include 'db_connect.php'; // Connects to DB and starts session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all transactions for this user
$stmt = $conn->prepare("SELECT id, description, amount, type, category, date FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    // We convert amount to a float because it comes from DB as a string
    $row['amount'] = (float)$row['amount']; 
    $transactions[] = $row;
}

echo json_encode(['success' => true, 'transactions' => $transactions]);

$stmt->close();
$conn->close();
?>