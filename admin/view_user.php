<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// التحقق من وجود معرف المستخدم
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// الحصول على معلومات المستخدم
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();

// الحصول على حجوزات المستخدم
$bookings_sql = "SELECT b.*, f.flight_number, f.departure, f.destination, f.departure_time, f.price 
                FROM bookings b 
                JOIN flights f ON b.flight_id = f.id 
                WHERE b.user_id = ? 
                ORDER BY b.booking_date DESC";
$bookings_stmt = $conn->prepare($bookings_sql);
$bookings_stmt->bind_param("i", $user_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$bookings = [];
while ($row = $bookings_result->fetch_assoc()) {
    $bookings[] = $row;
}

// حساب إجمالي الإنفاق
$total_spent = 0;
foreach ($bookings as $booking) {
    $total_spent += ($booking['price'] * $booking['seats']);
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المستخدم - لوحة تحكم المسؤول</title>
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

        .user-profile {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        .profile-card h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        .profile-item {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .profile-item .label {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--light-black);
            width: 40%;
        }

        .profile-item .value {
            font-size: 1.6rem;
            color: var(--black);
            width: 60%;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .bookings-container {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .bookings-container h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 0.1rem solid var(--light-bg);
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

        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 1.4rem;
            font-weight: 500;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            text-align: center;
        }

        .stat-card i {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .stat-card h4 {
            font-size: 1.8rem;
            color: var(--light-black);
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--black);
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

            .user-profile {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body style="direction: ltr;">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>تفاصيل المستخدم: <?php echo htmlspecialchars($user['name']); ?></h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <!-- User Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-ticket-alt"></i>
                <h4>عدد الحجوزات</h4>
                <div class="number"><?php echo count($bookings); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <h4>إجمالي الإنفاق</h4>
                <div class="number"><?php echo number_format($total_spent, 2); ?> ريال</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <h4>تاريخ التسجيل</h4>
                <div class="number"><?php echo isset($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : 'غير متوفر'; ?></div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="user-profile">
            <!-- User Information -->
            <div class="profile-card">
                <h3>معلومات المستخدم</h3>
                <div class="profile-item">
                    <div class="label">الاسم:</div>
                    <div class="value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div class="profile-item">
                    <div class="label">البريد الإلكتروني:</div>
                    <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="profile-item">
                    <div class="label">رقم الهاتف:</div>
                    <div class="value"><?php echo htmlspecialchars($user['phone_number'] ?? 'غير متوفر'); ?></div>
                </div>
                <div class="profile-item">
                    <div class="label">الدور:</div>
                    <div class="value"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
                <div class="action-buttons">
                    <a href="users.php" class="btn"><i class="fas fa-arrow-left"></i> العودة إلى المستخدمين</a>
                    <a href="users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيتم حذف جميع البيانات المرتبطة به.')"><i class="fas fa-trash"></i> حذف المستخدم</a>
                </div>
            </div>
        </div>

        <!-- User Bookings -->
        <div class="bookings-container">
            <h3>حجوزات المستخدم</h3>
            <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم الرحلة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>تاريخ المغادرة</th>
                        <th>عدد المقاعد</th>
                        <th>تاريخ الحجز</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['flight_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['departure']); ?></td>
                        <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($booking['departure_time'])); ?></td>
                        <td><?php echo $booking['seats']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></td>
                        <td>
                            <?php 
                            $status_class = '';
                            $status_text = '';
                            
                            switch($booking['status']) {
                                case 'confirmed':
                                    $status_class = 'status-confirmed';
                                    $status_text = 'مؤكد';
                                    break;
                                case 'pending':
                                    $status_class = 'status-pending';
                                    $status_text = 'قيد الانتظار';
                                    break;
                                case 'cancelled':
                                    $status_class = 'status-cancelled';
                                    $status_text = 'ملغي';
                                    break;
                                default:
                                    $status_text = $booking['status'];
                            }
                            ?>
                            <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn"><i class="fas fa-eye"></i> عرض</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-message">لا يوجد حجوزات لهذا المستخدم.</div>
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
