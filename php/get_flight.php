<?php
header("Content-Type: application/json");
include 'db.php'; // الاتصال بقاعدة البيانات

// قراءة بيانات `POST`
$data = json_decode(file_get_contents("php://input"), true);

$flight_id = isset($data['flight_id']) ? (int)$data['flight_id'] : 0;

if ($flight_id === 0) {
    echo json_encode(["status" => "error", "message" => "لم يتم تحديد الرحلة."]);
    exit();
}

// جلب بيانات الرحلة من قاعدة البيانات
$sql = "SELECT * FROM flights WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $flight = $result->fetch_assoc();
    echo json_encode(["status" => "success", "flight" => $flight]);
} else {
    echo json_encode(["status" => "error", "message" => "الرحلة غير موجودة."]);
}

$stmt->close();
$conn->close();
?>
