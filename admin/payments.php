<?php
session_start();
include '../php/db.php';

// إنشاء دالة الترجمة البسيطة
if (!function_exists('__')) {
    function __($key) {
        $translations = [
            'dashboard' => 'لوحة التحكم',
            'flights' => 'الرحلات',
            'bookings' => 'الحجوزات',
            'users' => 'المستخدمين',
            'payments' => 'المدفوعات',
            'settings' => 'الإعدادات',
            'logout' => 'تسجيل الخروج',
            'welcome_user' => 'مرحباً',
            'admin_panel' => 'لوحة التحكم',
            'payment_management' => 'إدارة المدفوعات',
            'search' => 'بحث',
            'filter' => 'تصفية',
            'all' => 'الكل',
            'today' => 'اليوم',
            'week' => 'هذا الأسبوع',
            'month' => 'هذا الشهر',
            'payment_id' => 'رقم المدفوعة',
            'user_name' => 'اسم المستخدم',
            'booking_id' => 'رقم الحجز',
            'amount' => 'المبلغ',
            'payment_method' => 'طريقة الدفع',
            'transaction_id' => 'رقم العملية',
            'status' => 'الحالة',
            'payment_date' => 'تاريخ الدفع',
            'actions' => 'الإجراءات',
            'view_details' => 'عرض التفاصيل',
            'no_payments' => 'لا توجد مدفوعات',
            'completed' => 'مكتملة',
            'pending' => 'قيد الانتظار',
            'failed' => 'فاشلة',
            'refunded' => 'مستردة',
            'total_revenue' => 'إجمالي الإيرادات'
        ];
        return isset($translations[$key]) ? $translations[$key] : $key;
    }
}

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// البحث عن المدفوعات
$search = "";
$filter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $filter = $_GET['filter'];
}

// التحقق من وجود جدول المدفوعات
$check_table = "SHOW TABLES LIKE 'payments'";
$table_exists = $conn->query($check_table)->num_rows > 0;

$payments = [];

if ($table_exists) {
    // التحقق من وجود بيانات في جدول المدفوعات
    $check_data = "SELECT COUNT(*) as count FROM payments";
    $data_exists = false;
    $result = $conn->query($check_data);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $data_exists = true;
        }
    }
    
    if ($data_exists) {
        // التحقق من وجود جداول users و bookings
        $check_users = "SHOW TABLES LIKE 'users'";
        $check_bookings = "SHOW TABLES LIKE 'bookings'";
        $users_exists = $conn->query($check_users)->num_rows > 0;
        $bookings_exists = $conn->query($check_bookings)->num_rows > 0;
        
        if ($users_exists && $bookings_exists) {
            // إذا كانت جميع الجداول موجودة، نستخدم JOIN
            $sql = "SELECT p.*, u.name as user_name, b.id as booking_id 
                    FROM payments p 
                    LEFT JOIN users u ON p.user_id = u.id 
                    LEFT JOIN bookings b ON p.booking_id = b.id 
                    WHERE 1=1";
        } else {
            // إذا لم تكن جميع الجداول موجودة، نستخدم استعلام بسيط
            $sql = "SELECT * FROM payments WHERE 1=1";
        }
        
        $params = [];
        $types = "";

        if (!empty($search)) {
            if ($users_exists && $bookings_exists) {
                $sql .= " AND (u.name LIKE ? OR p.payment_method LIKE ? OR p.transaction_id LIKE ?)";
                $search_param = "%$search%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
                $types .= "sss";
            } else {
                $sql .= " AND (payment_method LIKE ? OR transaction_id LIKE ?)";
                $search_param = "%$search%";
                $params[] = $search_param;
                $params[] = $search_param;
                $types .= "ss";
            }
        }

        if (!empty($filter)) {
            if ($users_exists && $bookings_exists) {
                if ($filter == 'today') {
                    $sql .= " AND DATE(p.payment_date) = CURDATE()";
                } elseif ($filter == 'week') {
                    $sql .= " AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                } elseif ($filter == 'month') {
                    $sql .= " AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                }
            } else {
                if ($filter == 'today') {
                    $sql .= " AND DATE(payment_date) = CURDATE()";
                } elseif ($filter == 'week') {
                    $sql .= " AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                } elseif ($filter == 'month') {
                    $sql .= " AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                }
            }
        }

        if ($users_exists && $bookings_exists) {
            $sql .= " ORDER BY p.payment_date DESC";
        } else {
            $sql .= " ORDER BY payment_date DESC";
        }

        $stmt = $conn->prepare($sql);

        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }
}

// حساب إجمالي المدفوعات
$total_revenue = 0;
foreach ($payments as $payment) {
    // التحقق من وجود عمود status قبل استخدامه
    if (isset($payment['status']) && $payment['status'] === 'completed') {
        $total_revenue += $payment['amount'];
    } else {
        // إذا لم يكن هناك عمود status، نضيف جميع المدفوعات
        $total_revenue += $payment['amount'];
    }
}

// إنشاء جدول المدفوعات إذا لم يكن موجودًا
if (!$table_exists) {
    // التحقق من وجود جداول users و bookings
    $check_users = "SHOW TABLES LIKE 'users'";
    $check_bookings = "SHOW TABLES LIKE 'bookings'";
    $users_exists = $conn->query($check_users)->num_rows > 0;
    $bookings_exists = $conn->query($check_bookings)->num_rows > 0;
    
    // إنشاء جدول المدفوعات بدون مفاتيح أجنبية لتجنب المشاكل
    $create_table = "CREATE TABLE payments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        booking_id INT(11) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100),
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table) === TRUE) {
        $table_created = true;
        
        // إضافة بعض بيانات المدفوعات التجريبية إذا كانت جداول users و bookings موجودة
        if ($users_exists && $bookings_exists) {
            // الحصول على معرف مستخدم وحجز موجودين
            $user_query = "SELECT id FROM users LIMIT 1";
            $booking_query = "SELECT id FROM bookings LIMIT 1";
            $user_result = $conn->query($user_query);
            $booking_result = $conn->query($booking_query);
            
            if ($user_result->num_rows > 0 && $booking_result->num_rows > 0) {
                $user_id = $user_result->fetch_assoc()['id'];
                $booking_id = $booking_result->fetch_assoc()['id'];
                
                // إضافة مدفوعات تجريبية
                $sample_data = [
                    ["user_id" => $user_id, "booking_id" => $booking_id, "amount" => 500.00, "payment_method" => "بطاقة ائتمان", "transaction_id" => "TXN123456", "status" => "completed"],
                    ["user_id" => $user_id, "booking_id" => $booking_id, "amount" => 750.50, "payment_method" => "PayPal", "transaction_id" => "PP789012", "status" => "completed"],
                    ["user_id" => $user_id, "booking_id" => $booking_id, "amount" => 1200.00, "payment_method" => "تحويل بنكي", "transaction_id" => "BT345678", "status" => "pending"]
                ];
                
                foreach ($sample_data as $data) {
                    $insert = "INSERT INTO payments (user_id, booking_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert);
                    $stmt->bind_param("iidsss", $data["user_id"], $data["booking_id"], $data["amount"], $data["payment_method"], $data["transaction_id"], $data["status"]);
                    $stmt->execute();
                }
            }
        }
    } else {
        $table_error = "خطأ في إنشاء جدول المدفوعات: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('payment_management'); ?></title>
    <link rel="stylesheet" href="../css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/multilingual.css">
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

        .payments-container {
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
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

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-refunded {
            background-color: #f39c12;
            color: #fff;
        }
        
        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
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
            <h1><?php echo __('payment_management'); ?></h1>
            <div class="user-info">
                <h3><?php echo __('welcome_user'); ?>, <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i><?php echo __('logout'); ?></a>
            </div>
        </div>

        <?php if (isset($table_created)): ?>
        <div class="alert alert-success">
            <?php echo __('table_created_successfully'); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($table_error)): ?>
        <div class="alert alert-danger">
            <?php echo $table_error; ?>
        </div>
        <?php endif; ?>

        <?php if (!$table_exists && !isset($table_created)): ?>
        <div class="alert alert-info">
            <?php echo __('table_does_not_exist'); ?>
        </div>
        <?php endif; ?>

        <!-- Payment Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-money-bill-wave"></i>
                <h4><?php echo __('total_revenue'); ?></h4>
                <div class="number"><?php echo number_format($total_revenue, 2); ?> SAR</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-receipt"></i>
                <h4><?php echo __('number_of_transactions'); ?></h4>
                <div class="number"><?php echo count($payments); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h4><?php echo __('completed_transactions'); ?></h4>
                <div class="number">
                    <?php 
                    $completed_count = 0;
                    foreach ($payments as $payment) {
                        if ($payment['status'] === 'completed') {
                            $completed_count++;
                        }
                    }
                    echo $completed_count;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h4><?php echo __('pending_transactions'); ?></h4>
                <div class="number">
                    <?php 
                    $pending_count = 0;
                    foreach ($payments as $payment) {
                        if ($payment['status'] === 'pending') {
                            $pending_count++;
                        }
                    }
                    echo $pending_count;
                    ?>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="<?php echo __('search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <form action="" method="GET" class="filter-form">
                <select name="filter" onchange="this.form.submit()">
                    <option value=""><?php echo __('all'); ?></option>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>><?php echo __('today'); ?></option>
                    <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>><?php echo __('last_week'); ?></option>
                    <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>><?php echo __('last_month'); ?></option>
                </select>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="payments-container">
            <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('payment_id'); ?></th>
                        <th><?php echo __('user_name'); ?></th>
                        <th><?php echo __('booking_id'); ?></th>
                        <th><?php echo __('amount'); ?></th>
                        <th><?php echo __('payment_method'); ?></th>
                        <th><?php echo __('transaction_id'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('payment_date'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['id']; ?></td>
                        <td><?php echo htmlspecialchars($payment['user_name'] ?? 'غير معروف'); ?></td>
                        <td>
                            <?php if ($payment['booking_id']): ?>
                            <a href="view_booking.php?id=<?php echo $payment['booking_id']; ?>">#<?php echo $payment['booking_id']; ?></a>
                            <?php else: ?>
                            غير مرتبط
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($payment['amount'], 2); ?> ريال</td>
                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'غير متوفر'); ?></td>
                        <td>
                            <?php 
                            $status_class = '';
                            $status_text = '';
                            
                            if (isset($payment['status'])) {
                                switch($payment['status']) {
                                    case 'completed':
                                        $status_class = 'status-completed';
                                        $status_text = __('completed');
                                        break;
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        $status_text = __('pending');
                                        break;
                                    case 'failed':
                                        $status_class = 'status-failed';
                                        $status_text = __('failed');
                                        break;
                                    case 'refunded':
                                        $status_class = 'status-refunded';
                                        $status_text = __('refunded');
                                        break;
                                    default:
                                        $status_text = $payment['status'];
                                }
                            } else {
                                // إذا لم يكن هناك عمود status، نفترض أنها مكتملة
                                $status_class = 'status-completed';
                                $status_text = __('completed');
                            }
                            ?>
                            <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                        <td>
                            <!-- تم إزالة رابط عرض التفاصيل لأن الملف غير موجود -->
                            <span class="btn disabled"><i class="fas fa-eye"></i> <?php echo __('view_details'); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-message"><?php echo __('no_payments_to_display'); ?></div>

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
