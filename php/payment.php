<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include 'db.php'; // الاتصال بقاعدة البيانات

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['booking_id']) || empty($data['amount'])) {
    echo json_encode(["status" => "error", "message" => "❌ بيانات الدفع غير مكتملة"]);
    exit();
}

$booking_id = $data['booking_id'];
$amount = $data['amount'];

// تسجيل الدفع في قاعدة البيانات بدون تنفيذ معاملة حقيقية
$sql = "INSERT INTO payments (booking_id, amount, payment_status) VALUES (?, ?, 'paid')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $booking_id, $amount);

if ($stmt->execute()) {
    // تحديث حالة الحجز إلى "confirmed"
    $update_sql = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $booking_id);
    $update_stmt->execute();

    echo json_encode(["status" => "success", "message" => "✅ تم حفظ بيانات الدفع بنجاح"]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ حدث خطأ أثناء معالجة الدفع"]);
}

$stmt->close();
$conn->close();
?>