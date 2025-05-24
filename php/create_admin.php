<?php
// ملف لإنشاء حساب مسؤول جديد
include 'db.php'; // الاتصال بقاعدة البيانات

// بيانات المسؤول
$name = "Admin User";
$email = "admin@example.com";
$password = "admin123"; // كلمة المرور بشكل نصي
$role = "admin";

// تشفير كلمة المرور باستخدام password_hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// حذف المستخدم إذا كان موجودًا بالفعل
$sql_delete = "DELETE FROM users WHERE email = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("s", $email);
$stmt_delete->execute();
$stmt_delete->close();

// إضافة المستخدم الجديد
$sql_insert = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt_insert->execute()) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>تم إنشاء حساب المسؤول بنجاح!</h3>";
    echo "<p><strong>البريد الإلكتروني:</strong> $email</p>";
    echo "<p><strong>كلمة المرور:</strong> $password</p>";
    echo "<p><strong>كلمة المرور المشفرة:</strong> $hashed_password</p>";
    echo "<p>يمكنك الآن استخدام هذه البيانات لتسجيل الدخول.</p>";
    echo "<p><a href='../login.html' style='color: #155724; text-decoration: underline;'>العودة إلى صفحة تسجيل الدخول</a></p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>حدث خطأ أثناء إنشاء حساب المسؤول!</h3>";
    echo "<p>الخطأ: " . $stmt_insert->error . "</p>";
    echo "</div>";
}

$stmt_insert->close();
$conn->close();
?>
