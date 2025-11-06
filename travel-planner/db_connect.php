<?php
// db_connect.php
declare(strict_types=1);

// Show useful DB errors during development (you can disable later)
error_reporting(E_ALL);
ini_set('display_errors', '1');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ---- EDIT THESE IF NEEDED ----
$DB_HOST = '127.0.0.1';   // use 127.0.0.1 on Windows
$DB_USER = 'root';
$DB_PASS = '12345';            // put your real password if you set one in XAMPP
$DB_NAME = 'travel_db';
$DB_PORT = 3307;          // <- set to 3306 if that’s the single port you kept
// --------------------------------

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
$conn->set_charset('utf8mb4');

// Optional alias if some old code used $mysqli
$mysqli = $conn;
