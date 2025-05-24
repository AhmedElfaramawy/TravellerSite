<?php
header("Content-Type: application/json");
include 'db.php'; // الاتصال بقاعدة البيانات

// قراءة بيانات الحجز من الطلب
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من نوع الطلب (إنشاء حجز جديد أو استرجاع الحجوزات)
if (isset($data['action']) && $data['action'] === 'get_bookings') {
    // استرجاع الحجوزات الخاصة بالمستخدم
    if (empty($data['user_id'])) {
        echo json_encode(["status" => "error", "message" => "معرف المستخدم مطلوب"]);
        exit();
    }
    
    $user_id = $data['user_id'];
    
    // استعلام لجلب الحجوزات مع تفاصيل الرحلات
    $sql = "SELECT b.*, f.flight_number, f.departure, f.destination, f.departure_time, f.arrival_time, f.price 
            FROM bookings b 
            JOIN flights f ON b.flight_id = f.id 
            WHERE b.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo json_encode(["status" => "success", "bookings" => $bookings]);
    $stmt->close();
    $conn->close();
    exit();
}

// إذا لم يكن الطلب لاسترجاع الحجوزات، فهو لإنشاء حجز جديد
// التحقق من أن جميع الحقول المطلوبة موجودة
if (
    empty($data['user_id']) || empty($data['flight_id']) || empty($data['first_name']) ||
    empty($data['last_name']) || empty($data['email']) || empty($data['phone_number']) ||
    empty($data['birth_date']) || empty($data['gender']) || empty($data['passport_number']) ||
    empty($data['nationality']) || empty($data['passport_expiration'])
) {
    echo json_encode(["status" => "error", "message" => "يرجى ملء جميع الحقول"]);
    exit();
}

// تحضير البيانات للحفظ
$user_id = $data['user_id'];
$flight_id = $data['flight_id'];
$first_name = mysqli_real_escape_string($conn, $data['first_name']);
$last_name = mysqli_real_escape_string($conn, $data['last_name']);
$email = mysqli_real_escape_string($conn, $data['email']);
$phone_number = mysqli_real_escape_string($conn, $data['phone_number']);
$birth_date = $data['birth_date'];
$gender = $data['gender'];
$passport_number = mysqli_real_escape_string($conn, $data['passport_number']);
$nationality = mysqli_real_escape_string($conn, $data['nationality']);
$passport_expiration = $data['passport_expiration'];

// ✅ التحقق مما إذا كان المستخدم قد حجز رحلة بالفعل
$check_sql = "SELECT * FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "⚠️ لا يمكنك حجز أكثر من رحلة واحدة في نفس الوقت!"]);
    exit();
}

$stmt->close();

// تنفيذ عملية الحجز وإدخال البيانات في قاعدة البيانات
$sql = "INSERT INTO bookings (user_id, flight_id, first_name, last_name, email, phone_number, birth_date, gender, passport_number, nationality, passport_expiration)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssssssss", $user_id, $flight_id, $first_name, $last_name, $email, $phone_number, $birth_date, $gender, $passport_number, $nationality, $passport_expiration);

// ✅ تنفيذ الاستعلام مرة واحدة فقط والحصول على رقم الحجز
if ($stmt->execute()) {
    $booking_id = $stmt->insert_id; // الحصول على رقم الحجز الجديد
    
    // ✅ طباعة البيانات للتصحيح
    error_log("Booking successful. Booking ID: " . $booking_id);
    error_log("User ID: " . $user_id . ", Flight ID: " . $flight_id);
    
    // إرجاع الاستجابة مع رقم الحجز
    echo json_encode([
        "status" => "success", 
        "message" => "تم تأكيد الحجز بنجاح", 
        "booking_id" => $booking_id
    ]);
} else {
    // ✅ سجل الخطأ للتصحيح
    error_log("Booking error: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء الحجز: " . $conn->error]);
}

// ✅ إغلاق الاتصال بعد الانتهاء من جميع العمليات
$stmt->close();
$conn->close();

?>