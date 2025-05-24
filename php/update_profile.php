<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "❌ User ID not found"]);
    exit();
}

$user_id = intval($data['user_id']);
$phone_number = $data['phone_number'] ?? null;

$sql = "UPDATE users SET phone_number = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $phone_number, $user_id);

if ($stmt->execute()) {
    // Fetch the updated user data
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo json_encode([
        "status" => "success", 
        "message" => "✅ Phone number updated successfully",
        "user" => $user
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ An error occurred while updating phone number"]);
}

$stmt->close();
$conn->close();
?>