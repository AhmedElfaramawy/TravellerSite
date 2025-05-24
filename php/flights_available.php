<?php
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'db.php';

// Check if database connection is successful
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

try {
    // Check if the flights table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'flights'");
    if ($checkTable->num_rows == 0) {
        // Create flights table with sample data if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS flights (
            id INT AUTO_INCREMENT PRIMARY KEY,
            flight_number VARCHAR(20) NOT NULL,
            departure VARCHAR(100) NOT NULL,
            destination VARCHAR(100) NOT NULL,
            departure_time DATETIME NOT NULL,
            arrival_time DATETIME NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            seats_available INT NOT NULL
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception("Error creating flights table: " . $conn->error);
        }
        
        // Insert sample data
        $insertSample = "INSERT INTO flights (flight_number, departure, destination, departure_time, arrival_time, price, seats_available) VALUES 
            ('FL001', 'Cairo', 'Dubai', '2025-05-15 08:00:00', '2025-05-15 12:00:00', 350.00, 120),
            ('FL002', 'Cairo', 'London', '2025-05-16 10:30:00', '2025-05-16 14:30:00', 450.00, 100),
            ('FL003', 'Cairo', 'Paris', '2025-05-17 09:15:00', '2025-05-17 13:45:00', 400.00, 80),
            ('FL004', 'Cairo', 'New York', '2025-05-18 23:00:00', '2025-05-19 06:30:00', 700.00, 150)";
        
        if (!$conn->query($insertSample)) {
            throw new Exception("Error inserting sample data: " . $conn->error);
        }
    }
    
    // Query flights
    $sql = "SELECT * FROM flights ORDER BY departure_time ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error querying flights: " . $conn->error);
    }
    
    $flights = [];
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
    
    if (count($flights) > 0) {
        echo json_encode(["status" => "success", "flights" => $flights]);
    } else {
        echo json_encode(["status" => "success", "flights" => [], "message" => "No flights found"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>