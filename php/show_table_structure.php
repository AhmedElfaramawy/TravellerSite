<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Database connection
include 'db.php';

// Get the structure of the bookings table
$query = "DESCRIBE bookings";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
    exit();
}

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
}

// Get a sample booking record
$query = "SELECT * FROM bookings LIMIT 1";
$result = $conn->query($query);

$sample = null;
if ($result && $result->num_rows > 0) {
    $sample = $result->fetch_assoc();
}

// Get the CREATE TABLE statement
$query = "SHOW CREATE TABLE bookings";
$result = $conn->query($query);

$createTable = null;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $createTable = $row['Create Table'];
}

// Return all data as JSON
echo json_encode([
    'table_structure' => $columns,
    'sample_record' => $sample,
    'create_table' => $createTable
], JSON_PRETTY_PRINT);

// Close the database connection
$conn->close();
?>
