<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// حذف مستخدم
if (isset($_GET['delete_user']) && !empty($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    
    // التحقق من وجود حجوزات للمستخدم
    $check_bookings = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($check_bookings);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $delete_error = "لا يمكن حذف المستخدم لأن لديه حجوزات مرتبطة. يجب حذف الحجوزات أولاً.";
    } else {
        // حذف المستخدم
        $delete_sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $delete_success = "تم حذف المستخدم بنجاح.";
        } else {
            $delete_error = "لا يمكن حذف المستخدم. قد يكون مسؤولاً أو غير موجود.";
        }
    }
}

// البحث عن المستخدمين
$search = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM users WHERE (name LIKE ? OR email LIKE ?) AND role != 'admin' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("ss", $search_param, $search_param);
} else {
    $sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - لوحة تحكم المسؤول</title>
    <link rel="stylesheet" href="../css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --main-color: #8e44ad;
            --black: #222;
            --white: #fff;
            --light-black: #777;
            --light-white: #fff9;
            --dark-bg: rgba(0, 0, 0, .7);
            --light-bg: #eee;
            --border: .1rem solid var(--black);
            --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
            --text-shadow: 0 1.5rem 3rem rgba(0, 0, 0, .3);
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
        }

        html {
            font-size: 62.5%;
            overflow-x: hidden;
        }

        body {
            background-color: var(--light-bg);
            padding-left: 30rem;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 30rem;
            background-color: var(--black);
            padding: 2rem;
            z-index: 1000;
        }

        .sidebar .logo {
            font-size: 2.5rem;
            color: var(--white);
            margin-bottom: 2rem;
            display: block;
            text-align: center;
        }

        .sidebar .logo i {
            color: var(--main-color);
            margin-right: 1rem;
        }

        .sidebar .menu {
            margin-top: 3rem;
        }

        .sidebar .menu h3 {
            color: var(--white);
            font-size: 1.7rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .sidebar .menu a {
            display: block;
            padding: 1rem;
            margin: 0.5rem 0;
            font-size: 1.6rem;
            color: var(--light-white);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .sidebar .menu a:hover,
        .sidebar .menu a.active {
            background-color: var(--main-color);
            color: var(--white);
        }

        .sidebar .menu a i {
            margin-right: 1rem;
        }

        .main-content {
            padding: 2rem;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            background-color: var(--white);
            box-shadow: var(--box-shadow);
            position: relative;
            z-index: 100;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }

        .header h2 {
            font-size: 2.5rem;
            color: var(--black);
        }

        .header .user-info {
            display: flex;
            align-items: center;
        }

        .header .user-info h3 {
            font-size: 1.8rem;
            color: var(--black);
            margin-right: 1rem;
        }

        .header .user-info .btn {
            margin-left: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: var(--main-color);
            color: var(--white);
            font-size: 1.6rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #7d32a8;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-danger {
            background-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .search-form {
            display: flex;
            margin-bottom: 2rem;
        }

        .search-form input {
            flex: 1;
            padding: 1rem;
            font-size: 1.6rem;
            border: 0.1rem solid var(--light-black);
            border-radius: 0.5rem 0 0 0.5rem;
        }

        .search-form button {
            padding: 1rem 2rem;
            background-color: var(--main-color);
            color: var(--white);
            font-size: 1.6rem;
            border-radius: 0 0.5rem 0.5rem 0;
            cursor: pointer;
        }

        .users-container {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            overflow-x: auto;
        }

        .alert {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            font-size: 1.6rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background-color: var(--light-bg);
            padding: 1.5rem;
            text-align: right;
            font-size: 1.6rem;
            font-weight: 600;
        }

        table td {
            padding: 1.2rem;
            text-align: right;
            font-size: 1.6rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        table tr:last-child td {
            border-bottom: none;
        }

        .empty-message {
            text-align: center;
            font-size: 1.8rem;
            color: var(--light-black);
            padding: 2rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .pagination a {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 0 0.5rem;
            background-color: var(--white);
            color: var(--black);
            font-size: 1.6rem;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
        }

        .pagination a.active {
            background-color: var(--main-color);
            color: var(--white);
        }

        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }

            .sidebar {
                left: -30rem;
                transition: 0.3s linear;
            }

            .sidebar.active {
                left: 0;
            }

            .toggle-sidebar {
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>إدارة المستخدمين</h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($delete_success)): ?>
        <div class="alert alert-success">
            <?php echo $delete_success; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($delete_error)): ?>
        <div class="alert alert-danger">
            <?php echo $delete_error; ?>
        </div>
        <?php endif; ?>

        <!-- Search Form -->
        <form action="" method="GET" class="search-form">
            <input type="text" name="search" placeholder="البحث عن مستخدم بالاسم أو البريد الإلكتروني" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- Users Table -->
        <div class="users-container">
            <?php if (count($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>رقم الهاتف</th>
                        <th>الدور</th>
                        <th>عدد الحجوزات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number'] ?? 'غير متوفر'); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php
                            // الحصول على عدد حجوزات المستخدم
                            $booking_sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
                            $stmt = $conn->prepare($booking_sql);
                            $stmt->bind_param("i", $user['id']);
                            $stmt->execute();
                            $booking_result = $stmt->get_result();
                            $booking_row = $booking_result->fetch_assoc();
                            echo $booking_row['count'];
                            ?>
                        </td>
                        <td>
                            <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn"><i class="fas fa-eye"></i> عرض</a>
                            <a href="users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')"><i class="fas fa-trash"></i> حذف</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-message">لا يوجد مستخدمين للعرض.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // للتعامل مع القائمة الجانبية في الشاشات الصغيرة
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
