<?php
// Try first with the custom port 3307
$host = "127.0.0.1:3307";
$user = "root"; // Default username
$password = ""; // Leave empty if no password is set
$database = "flight_booking"; // Make sure this matches your imported database name

// إنشاء الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $password, $database);

// التحقق من نجاح الاتصال
if ($conn->connect_error) {
    // If connection fails, try with the default port 3306
    $host = "127.0.0.1:3306";
    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
}

// ضبط الترميز إلى utf8 لضمان دعم اللغة العربية
$conn->set_charset("utf8");
?>