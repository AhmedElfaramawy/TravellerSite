<?php
// ملف مبسط لاسترجاع رقم الهاتف بشكل مباشر
header('Content-Type: application/json');
include 'db.php';

// سجل بداية التنفيذ
error_log("DIRECT_PHONE.PHP START: " . date('Y-m-d H:i:s'));

// قراءة بيانات الطلب
$data = json_decode(file_get_contents("php://input"), true);
error_log("Request data: " . print_r($data, true));

if (empty($data['user_id'])) {
    echo json_encode([
        "status" => "error", 
        "message" => "معرف المستخدم مطلوب"
    ]);
    error_log("Error: User ID missing");
    exit();
}

$user_id = intval($data['user_id']);

// استعلام بسيط للحصول على رقم الهاتف فقط
$sql = "SELECT phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // تنسيق رقم الهاتف
    $phone_number = isset($user['phone_number']) && !empty($user['phone_number']) 
        ? $user['phone_number'] 
        : "";
    
    error_log("Phone number found: " . $phone_number);
    
    echo json_encode([
        "status" => "success",
        "phone_number" => $phone_number
    ]);
} else {
    error_log("User not found with ID: " . $user_id);
    echo json_encode([
        "status" => "error",
        "message" => "لم يتم العثور على المستخدم"
    ]);
}

$stmt->close();
$conn->close();
error_log("DIRECT_PHONE.PHP END: " . date('Y-m-d H:i:s'));
?>
