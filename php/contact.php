<?php
include "config.php"; // الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    // التحقق من صحة البيانات
    if (empty($name) || empty($email) || empty($message)) {
        die("❌ جميع الحقول مطلوبة.");
    }

    // إدخال البيانات إلى قاعدة البيانات
    $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        echo "✅ تم إرسال رسالتك بنجاح!";
        header("Location: ../ContactUs.html?success=1"); // إعادة التوجيه مع رسالة نجاح
        exit();
    } else {
        echo "❌ حدث خطأ أثناء إرسال رسالتك.";
    }

    $stmt->close();
}

$conn->close();
?>