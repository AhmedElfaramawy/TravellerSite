<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "❌ معرف المستخدم غير موجود"]);
    exit();
}

$user_id = intval($data['user_id']);

$sql = "SELECT id, name, email, phone_number, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Return all user data including role
    $response = [
        "status" => "success",
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "phone_number" => $user['phone_number'] ?? '',
        "role" => $user['role'] ?? 'passenger'
    ];
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "❌ User data not found"]);
}

$stmt->close();
$conn->close();
?>