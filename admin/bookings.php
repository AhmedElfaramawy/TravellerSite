<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// حذف حجز
if (isset($_GET['delete_booking']) && !empty($_GET['delete_booking'])) {
    $booking_id = $_GET['delete_booking'];
    
    // حذف الحجز
    $delete_sql = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $delete_success = "تم حذف الحجز بنجاح.";
    } else {
        $delete_error = "لا يمكن حذف الحجز. قد يكون غير موجود.";
    }
}

// البحث عن الحجوزات
$search = "";
$filter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $filter = $_GET['filter'];
}

$sql = "SELECT b.*, u.name as user_name, f.flight_number, f.departure, f.destination, f.departure_time 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR f.flight_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($filter)) {
    if ($filter == 'today') {
        $sql .= " AND DATE(b.booking_date) = CURDATE()";
    } elseif ($filter == 'week') {
        $sql .= " AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter == 'month') {
        $sql .= " AND b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }
}

$sql .= " ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحجوزات - لوحة تحكم المسؤول</title>
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

        .search-filter {
            display: flex;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .search-form {
            flex: 1;
            display: flex;
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

        .filter-form {
            display: flex;
            align-items: center;
        }

        .filter-form select {
            padding: 1rem;
            font-size: 1.6rem;
            border: 0.1rem solid var(--light-black);
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .bookings-container {
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

            .search-filter {
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
            <h2>إدارة الحجوزات</h2>
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

        <!-- Search and Filter -->
        <div class="search-filter">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="البحث عن حجز باسم المستخدم أو رقم الرحلة" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <form action="" method="GET" class="filter-form">
                <select name="filter" onchange="this.form.submit()">
                    <option value="">جميع الحجوزات</option>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>اليوم</option>
                    <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>آخر أسبوع</option>
                    <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>>آخر شهر</option>
                </select>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-container">
            <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المستخدم</th>
                        <th>رقم الرحلة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>تاريخ المغادرة</th>
                        <th>عدد المقاعد</th>
                        <th>تاريخ الحجز</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['flight_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['departure']); ?></td>
                        <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($booking['departure_time'])); ?></td>
                        <td><?php echo $booking['seats'] ?? ''; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></td>
                        <td>
                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn"><i class="fas fa-eye"></i> عرض</a>
                            <a href="bookings.php?delete_booking=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الحجز؟')"><i class="fas fa-trash"></i> حذف</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-message">لا يوجد حجوزات للعرض.</div>
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
