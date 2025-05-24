<?php
session_start(); // بدء الجلسة
header("Content-Type: application/json");
include 'db.php'; // الاتصال بقاعدة البيانات

// ✅ استقبال البيانات بصيغة JSON
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data['email']) && isset($data['password'])) {
    $email = trim($data['email']);
    $password = trim($data['password']);

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "❌ يرجى إدخال البريد الإلكتروني وكلمة المرور."]);
        exit();
    }

    // ✅ استخدام `prepared statements` لحماية قاعدة البيانات من هجمات SQL Injection
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'passenger'; // ✅ افتراض "passenger" إذا لم يكن هناك `role`

            echo json_encode([
                "status" => "success",
                "message" => "✅ تم تسجيل الدخول بنجاح!",
                "user_id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "role" => $user['role']
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "❌ كلمة المرور غير صحيحة."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "❌ البريد الإلكتروني غير مسجل."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "❌ طلب غير صالح."]);
}

$conn->close();
?>