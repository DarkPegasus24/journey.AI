<?php
header('Content-Type: application/json'); // Tell the browser we're sending JSON
include 'db_connect.php';

$hotels = [];
$sql = "SELECT * FROM hotels";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $hotels[] = $row; // Add each hotel to an array
    }
}

echo json_encode($hotels); // Send the array of hotels as JSON

$conn->close();
?>