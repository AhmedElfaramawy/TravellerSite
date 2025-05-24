<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// التحقق من وجود معرف الحجز
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = $_GET['id'];

// الحصول على تفاصيل الحجز
$sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone_number as user_phone, 
        f.flight_number, f.departure, f.destination, f.departure_time, f.arrival_time, f.price 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: bookings.php");
    exit();
}

$booking = $result->fetch_assoc();

// تحديث حالة الحجز
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $booking_id);
    $update_stmt->execute();
    
    if ($update_stmt->affected_rows > 0) {
        $update_success = "تم تحديث حالة الحجز بنجاح.";
        // تحديث بيانات الحجز بعد التعديل
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
    } else {
        $update_error = "حدث خطأ أثناء تحديث حالة الحجز.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الحجز - لوحة تحكم المسؤول</title>
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

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        .detail-card h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        .detail-item {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .detail-item .label {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--light-black);
            width: 40%;
        }

        .detail-item .value {
            font-size: 1.6rem;
            color: var(--black);
            width: 60%;
        }

        .status-form {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .status-form h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 1.6rem;
            color: var(--black);
            margin-bottom: 0.5rem;
        }

        .form-group select {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            border: 0.1rem solid var(--light-black);
            border-radius: 0.5rem;
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

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .price-calculation {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .price-calculation h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.6rem;
        }

        .price-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 0.1rem solid var(--light-bg);
            font-size: 1.8rem;
            font-weight: 600;
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

            .booking-details {
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
            <h2>تفاصيل الحجز #<?php echo $booking['id']; ?></h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($update_success)): ?>
        <div class="alert alert-success">
            <?php echo $update_success; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($update_error)): ?>
        <div class="alert alert-danger">
            <?php echo $update_error; ?>
        </div>
        <?php endif; ?>

        <!-- Status Update Form -->
        <form action="" method="POST" class="status-form">
            <h3>تحديث حالة الحجز</h3>
            <div class="form-group">
                <label for="status">الحالة:</label>
                <select name="status" id="status">
                    <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>مؤكد</option>
                    <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                    <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn">تحديث الحالة</button>
        </form>

        <!-- Booking Details -->
        <div class="booking-details">
            <!-- User Information -->
            <div class="detail-card">
                <h3>معلومات المستخدم</h3>
                <div class="detail-item">
                    <div class="label">الاسم:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">البريد الإلكتروني:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">رقم الهاتف:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['user_phone'] ?? 'غير متوفر'); ?></div>
                </div>
                <div class="action-buttons">
                    <a href="users.php?search=<?php echo urlencode($booking['user_email']); ?>" class="btn"><i class="fas fa-user"></i> عرض المستخدم</a>
                </div>
            </div>

            <!-- Flight Information -->
            <div class="detail-card">
                <h3>معلومات الرحلة</h3>
                <div class="detail-item">
                    <div class="label">رقم الرحلة:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['flight_number']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">من:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['departure']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">إلى:</div>
                    <div class="value"><?php echo htmlspecialchars($booking['destination']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">تاريخ المغادرة:</div>
                    <div class="value"><?php echo date('Y-m-d H:i', strtotime($booking['departure_time'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">تاريخ الوصول:</div>
                    <div class="value"><?php echo date('Y-m-d H:i', strtotime($booking['arrival_time'])); ?></div>
                </div>
                <div class="action-buttons">
                    <a href="flights.php?search=<?php echo urlencode($booking['flight_number']); ?>" class="btn"><i class="fas fa-plane"></i> عرض الرحلة</a>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <!-- Booking Information -->
            <div class="detail-card">
                <h3>معلومات الحجز</h3>
                <div class="detail-item">
                    <div class="label">رقم الحجز:</div>
                    <div class="value">#<?php echo $booking['id']; ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">تاريخ الحجز:</div>
                    <div class="value"><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">عدد المقاعد:</div>
                    <div class="value"><?php echo $booking['seats'] ?? ''; ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">الحالة:</div>
                    <div class="value">
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
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="bookings.php" class="btn"><i class="fas fa-arrow-left"></i> العودة إلى الحجوزات</a>
                    <a href="bookings.php?delete_booking=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الحجز؟')"><i class="fas fa-trash"></i> حذف الحجز</a>
                </div>
            </div>

            <!-- Price Calculation -->
            <div class="price-calculation">
                <h3>تفاصيل السعر</h3>
                <div class="price-item">
                    <span>سعر التذكرة:</span>
                    <span><?php echo number_format($booking['price'], 2); ?> ريال</span>
                </div>
                <div class="price-item">
                    <span>عدد المقاعد:</span>
                    <span><?php echo $booking['seats'] ?? ''; ?></span>
                </div>
                <?php if (isset($booking['tax']) && $booking['tax'] > 0): ?>
                <div class="price-item">
                    <span>الضريبة:</span>
                    <span><?php echo number_format($booking['tax'], 2); ?> ريال</span>
                </div>
                <?php endif; ?>
                <div class="price-total">
                    <span>الإجمالي:</span>
                    <span><?php echo number_format($booking['price'] * ($booking['seats'] ?? 0), 2); ?> ريال</span>
                </div>
            </div>
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
