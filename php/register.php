<?php
// استدعاء ملف الاتصال بقاعدة البيانات
include 'db.php';

// التحقق من إرسال البيانات عبر POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // تشفير كلمة المرور قبل تخزينها
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // التحقق مما إذا كان البريد الإلكتروني مسجل مسبقًا
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($check_email);

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "البريد الإلكتروني مستخدم مسبقًا."]);
    } else {
        // إدخال المستخدم الجديد إلى قاعدة البيانات
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "تم التسجيل بنجاح!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "خطأ أثناء التسجيل: " . $conn->error]);
        }
    }
}
?>
