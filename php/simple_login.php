<?php
session_start(); // بدء الجلسة
include 'php/db.php'; // الاتصال بقاعدة البيانات

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error_message = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";
    } else {
        // استخدام prepared statements لحماية قاعدة البيانات
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // عرض كلمة المرور المخزنة للتشخيص
            $stored_password = $user['password'];
            
            // التحقق من كلمة المرور
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'passenger';
                
                $success_message = "تم تسجيل الدخول بنجاح! مرحبًا " . $user['name'];
                
                // إعادة التوجيه بعد تسجيل الدخول الناجح
                header("refresh:2;url=index.html");
            } else {
                $error_message = "كلمة المرور غير صحيحة.";
            }
        } else {
            $error_message = "البريد الإلكتروني غير مسجل.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول البسيط</title>
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="login-form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h3>تسجيل الدخول البسيط</h3>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <span>البريد الإلكتروني</span>
            <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" class="box" required>
            
            <span>كلمة المرور</span>
            <input type="password" name="password" placeholder="أدخل كلمة المرور" class="box" required>
            
            <div class="checkbox">
                <input type="checkbox" name="remember" id="remember-me">
                <label for="remember-me">تذكرني</label>
            </div>
            
            <input type="submit" value="تسجيل الدخول" class="btn">
            
            <p>نسيت كلمة المرور؟ <a href="#">انقر هنا</a></p>
            <p>ليس لديك حساب؟ <a href="register.html">إنشاء حساب</a></p>
            
            <div class="debug-info">
                <h4>معلومات تشخيصية:</h4>
                <p>بيانات تسجيل الدخول الصحيحة:</p>
                <ul>
                    <li>البريد الإلكتروني: admin@example.com</li>
                    <li>كلمة المرور: admin123</li>
                </ul>
                <p>روابط مفيدة:</p>
                <ul>
                    <li><a href="php/check_users.php">التحقق من المستخدمين</a></li>
                    <li><a href="php/create_admin.php">إنشاء حساب مسؤول جديد</a></li>
                    <li><a href="login.html">صفحة تسجيل الدخول الأصلية</a></li>
                </ul>
            </div>
        </form>
    </div>
</body>
</html>
