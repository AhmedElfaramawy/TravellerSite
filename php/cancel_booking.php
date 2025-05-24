<?php
header("Content-Type: application/json");
include 'db.php'; // الاتصال بقاعدة البيانات

// قراءة بيانات الحجز من الطلب
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من وجود معرف الحجز
if (empty($data['booking_id'])) {
    echo json_encode(["status" => "error", "message" => "معرف الحجز مطلوب"]);
    exit();
}

// تأكد من أن معرف الحجز هو رقم صحيح
$booking_id = intval($data['booking_id']);

// سجل معلومات التصحيح
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Received booking_id: " . $booking_id . "\n", FILE_APPEND);

// تحقق من وجود الحجز أولاً
$check_sql = "SELECT * FROM bookings WHERE id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

// سجل معلومات التصحيح
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Checking booking: " . $booking_id . ", Found rows: " . $result->num_rows . "\n", FILE_APPEND);

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "الحجز غير موجود", "booking_id" => $booking_id]);
    $stmt->close();
    $conn->close();
    exit();
}

// حذف الحجز
$delete_sql = "DELETE FROM bookings WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $booking_id);

// سجل معلومات التصحيح
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Attempting to delete booking: " . $booking_id . "\n", FILE_APPEND);

if ($stmt->execute()) {
    // سجل معلومات التصحيح
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Successfully deleted booking: " . $booking_id . ", Affected rows: " . $stmt->affected_rows . "\n", FILE_APPEND);
    
    echo json_encode(["status" => "success", "message" => "تم إلغاء الحجز بنجاح", "booking_id" => $booking_id, "affected_rows" => $stmt->affected_rows]);
} else {
    // سجل معلومات التصحيح
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Error deleting booking: " . $booking_id . ", Error: " . $conn->error . "\n", FILE_APPEND);
    
    echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء إلغاء الحجز", "error" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
