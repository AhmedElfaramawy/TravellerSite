<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Link</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .admin-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .admin-btn:hover {
            background-color: #27ae60;
            transform: translateY(-3px);
        }
        .admin-btn i {
            margin-right: 10px;
        }
        .info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: left;
        }
        .info h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .info p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <?php include 'php/header.php'; ?>
    
    <div class="container">
        <h1>رابط لوحة تحكم المسؤول</h1>
        
        <a href="admin/dashboard.php" class="admin-btn">
            <i class="fas fa-cogs"></i> الذهاب إلى لوحة تحكم المسؤول
        </a>
        
        <div class="info">
            <h2>معلومات المستخدم:</h2>
            <p><strong>الاسم:</strong> <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'غير متوفر'; ?></p>
            <p><strong>الدور:</strong> <?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'غير متوفر'; ?></p>
            <p><strong>معرف المستخدم:</strong> <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'غير متوفر'; ?></p>
            
            <h2>معلومات الجلسة (Session):</h2>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h2>معلومات تخزين الجلسة (SessionStorage):</h2>
            <p>لا يمكن عرض محتويات SessionStorage من جانب الخادم. يرجى فتح وحدة تحكم المتصفح (F12) وكتابة الأمر التالي:</p>
            <pre>console.log(sessionStorage);</pre>
        </div>
        
        <a href="php/logout.php" class="logout-btn">تسجيل الخروج</a>
    </div>
    
    <script>
        // عرض محتويات sessionStorage في وحدة تحكم المتصفح
        console.log("محتويات sessionStorage:", {
            userId: sessionStorage.getItem('userId'),
            userRole: sessionStorage.getItem('userRole'),
            userName: sessionStorage.getItem('userName'),
            userEmail: sessionStorage.getItem('userEmail')
        });
    </script>
</body>
</html>
