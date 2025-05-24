<?php
session_start();
session_unset();
session_destroy();
header("Location: ../login.html"); // إعادة التوجيه لصفحة تسجيل الدخول
exit();
?>
