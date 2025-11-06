<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (!empty($_SESSION['user_id'])) {
  echo json_encode([
    'loggedIn' => true,
    'userId' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? '',
    'email' => $_SESSION['email'] ?? ''
  ]);
} else {
  echo json_encode(['loggedIn' => false]);
}
