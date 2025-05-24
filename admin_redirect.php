<?php
// ملف توجيه للوحة تحكم المسؤول
session_start();

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: simple_login.php");
    exit();
}

// توجيه إلى لوحة تحكم المسؤول
header("Location: admin/dashboard.php");
exit();
?>
