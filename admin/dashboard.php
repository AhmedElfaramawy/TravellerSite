<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// إنشاء دالة الترجمة البسيطة
if (!function_exists('__')) {
    function __($key) {
        $translations = [
            'dashboard' => 'لوحة التحكم',
            'welcome_user' => 'مرحباً',
            'logout' => 'تسجيل الخروج',
            'total_flights' => 'إجمالي الرحلات',
            'total_bookings' => 'إجمالي الحجوزات',
            'total_users' => 'إجمالي المستخدمين',
            'total_revenue' => 'إجمالي الإيرادات',
            'recent_flights' => 'أحدث الرحلات',
            'flight_number' => 'رقم الرحلة',
            'departure' => 'المغادرة',
            'destination' => 'الوجهة',
            'departure_time' => 'وقت المغادرة',
            'price' => 'السعر',
            'seats_available' => 'المقاعد المتاحة',
            'actions' => 'الإجراءات',
            'confirm_delete' => 'هل أنت متأكد من حذف هذا العنصر؟',
            'no_records' => 'لا توجد سجلات',
            'view_all' => 'عرض الكل',
            'recent_bookings' => 'أحدث الحجوزات',
            'booking_id' => 'رقم الحجز',
            'user_name' => 'اسم المستخدم',
            'admin_panel' => 'لوحة التحكم'
        ];
        
        return isset($translations[$key]) ? $translations[$key] : $key;
    }
}

// الحصول على إحصائيات
$stats = [
    'flights' => 0,
    'bookings' => 0,
    'users' => 0,
    'revenue' => 0
];

// عدد الرحلات
$sql = "SELECT COUNT(*) as count FROM flights";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['flights'] = $result->fetch_assoc()['count'];
}

// عدد الحجوزات
$sql = "SELECT COUNT(*) as count FROM bookings";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['bookings'] = $result->fetch_assoc()['count'];
}

// عدد المستخدمين
$sql = "SELECT COUNT(*) as count FROM users WHERE role = 'passenger'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['users'] = $result->fetch_assoc()['count'];
}

// إجمالي الإيرادات
$sql = "SELECT SUM(amount) as total FROM payments";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['revenue'] = $row['total'] ? $row['total'] : 0;
}

// الحصول على أحدث الرحلات
$recent_flights = [];
$sql = "SELECT * FROM flights ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_flights[] = $row;
    }
}

// الحصول على أحدث الحجوزات
$recent_bookings = [];
$sql = "SELECT b.id, b.flight_id, b.user_id, b.booking_date, 
               f.flight_number, f.price, u.name as user_name 
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        JOIN users u ON b.user_id = u.id
        ORDER BY b.id DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin_panel'); ?></title>
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
            margin-left: 1rem;
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
        }

        .header h2 {
            font-size: 2.5rem;
            color: var(--black);
        }

        .user-info {
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
            background: var(--main-color);
            color: var(--white);
            font-size: 1.7rem;
            padding: 1rem 3rem;
            cursor: pointer;
            border-radius: .5rem;
        }

        .btn i {
            margin-left: 0.5rem;
        }

        .btn:hover {
            background: var(--black);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--box-shadow);
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

        .stat-card .count {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--black);
        }

        .table-container {
            background-color: var(--white);
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 3rem;
            overflow-x: auto;
        }

        .table-container h2 {
            font-size: 2.2rem;
            color: var(--black);
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .table-container h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 5rem;
            height: 3px;
            background-color: var(--main-color);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        table th, table td {
            padding: 1.5rem 2rem;
            text-align: left;
            font-size: 1.5rem;
            vertical-align: middle;
        }
        
        table th {
            background-color: var(--light-bg);
            color: var(--black);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        table th:first-child {
            border-top-left-radius: 1rem;
            border-left: 1px solid #eee;
        }
        
        table th:last-child {
            border-top-right-radius: 1rem;
            border-right: 1px solid #eee;
        }
        
        table td {
            border-bottom: 1px solid #eee;
            border-left: 1px solid #eee;
            border-right: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        table tr:hover td {
            background-color: rgba(142, 68, 173, 0.05);
        }
        
        table tr:last-child td:first-child {
            border-bottom-left-radius: 1rem;
        }
        
        table tr:last-child td:last-child {
            border-bottom-right-radius: 1rem;
        }

        table tr:last-child td {
            border-bottom: 1px solid #eee;
        }
        
        /* تنسيق خلية الإجراءات */
        table td:last-child {
            text-align: center;
            min-width: 12rem;
        }

        .action-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            font-size: 1.4rem;
            border-radius: 0.3rem;
            margin-right: 0.5rem;
            cursor: pointer;
            color: var(--white);
            text-decoration: none;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .view-btn {
            background-color: #2ecc71;
        }
        
        .action-btn i {
            font-size: 1.6rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            opacity: 0.9;
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }

            .sidebar {
                width: 20rem;
            }

            .main-content {
                padding-right: 20rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 7rem;
                padding: 1.5rem 1rem;
            }

            .sidebar .logo {
                font-size: 0;
                justify-content: center;
            }

            .sidebar .logo i {
                margin-left: 0;
                font-size: 2.5rem;
            }

            .sidebar .menu h3 {
                display: none;
            }

            .sidebar .menu a {
                justify-content: center;
                padding: 1rem;
            }

            .sidebar .menu a i {
                margin-left: 0;
                font-size: 2rem;
            }

            .sidebar .menu a span {
                display: none;
            }

            .main-content {
                padding-right: 7rem;
            }

            .header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>لوحة التحكم</h1>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-plane"></i>
                <h4><?php echo __('total_flights'); ?></h4>
                <div class="count"><?php echo $stats['flights'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-ticket-alt"></i>
                <h4><?php echo __('total_bookings'); ?></h4>
                <div class="count"><?php echo $stats['bookings'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h4><?php echo __('total_users'); ?></h4>
                <div class="count"><?php echo $stats['users'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <h4><?php echo __('total_revenue'); ?></h4>
                <div class="count"><?php echo number_format($stats['revenue'] ?? 0, 2); ?> ريال</div>
            </div>
        </div>

        <!-- Recent Flights -->
        <div class="table-container">
            <h2><?php echo __('recent_flights'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('flight_number'); ?></th>
                        <th><?php echo __('departure'); ?></th>
                        <th><?php echo __('destination'); ?></th>
                        <th><?php echo __('departure_time'); ?></th>
                        <th><?php echo __('price'); ?></th>
                        <th><?php echo __('seats_available'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_flights) > 0): ?>
                        <?php foreach ($recent_flights as $flight): ?>
                            <tr>
                                <td><?php echo $flight['flight_number']; ?></td>
                                <td><?php echo $flight['departure']; ?></td>
                                <td><?php echo $flight['destination']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($flight['departure_time'])); ?></td>
                                <td><?php echo number_format($flight['price'], 2); ?> ريال</td>
                                <td><?php echo $flight['seats_available']; ?></td>
                                <td>
                                    <a href="edit_flight.php?id=<?php echo $flight['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                    <a href="delete_flight.php?id=<?php echo $flight['id']; ?>" class="action-btn delete-btn" onclick="return confirm('<?php echo __('confirm_delete'); ?>')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;"><?php echo __('no_records'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (count($recent_flights) > 0): ?>
                <a href="flights.php" class="btn" style="margin-top: 1.5rem;"><?php echo __('view_all'); ?></a>
            <?php endif; ?>
        </div>

        <!-- Recent Bookings -->
        <div class="table-container">
            <h2><?php echo __('recent_bookings'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('booking_id'); ?></th>
                        <th><?php echo __('user_name'); ?></th>
                        <th><?php echo __('flight_number'); ?></th>
                        <th>السعر الإجمالي</th>
                        <th>تاريخ الحجز</th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_bookings) > 0): ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo $booking['user_name']; ?></td>
                                <td><?php echo $booking['flight_number']; ?></td>
                                <td><?php echo number_format($booking['price'], 2); ?> ريال</td>
                                <td><?php echo date('Y-m-d', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;"><?php echo __('no_records'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (count($recent_bookings) > 0): ?>
                <a href="bookings.php" class="btn" style="margin-top: 1.5rem;"><?php echo __('view_all'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
