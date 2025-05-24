<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../php/simple_login.php");
    exit();
}

// تحديد نوع التقرير المطلوب
$report_type = isset($_GET['type']) ? $_GET['type'] : 'bookings';
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// تحديد الفترة الزمنية
$date_condition = "";
$period_title = "";

switch ($period) {
    case 'today':
        $date_condition = "DATE(created_at) = CURDATE()";
        $period_title = "اليوم";
        break;
    case 'week':
        $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $period_title = "آخر أسبوع";
        break;
    case 'month':
        $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $period_title = "آخر شهر";
        break;
    case 'year':
        $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
        $period_title = "آخر سنة";
        break;
    default:
        $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $period_title = "آخر شهر";
}

// استعلامات التقارير
$report_data = [];
$chart_labels = [];
$chart_data = [];
$chart_title = "";

// التحقق من وجود الجداول المطلوبة
$check_bookings = "SHOW TABLES LIKE 'bookings'";
$check_users = "SHOW TABLES LIKE 'users'";
$check_flights = "SHOW TABLES LIKE 'flights'";
$check_payments = "SHOW TABLES LIKE 'payments'";

$bookings_exists = $conn->query($check_bookings)->num_rows > 0;
$users_exists = $conn->query($check_users)->num_rows > 0;
$flights_exists = $conn->query($check_flights)->num_rows > 0;
$payments_exists = $conn->query($check_payments)->num_rows > 0;

// تقرير الحجوزات
if ($report_type == 'bookings' && $bookings_exists) {
    $chart_title = "تقرير الحجوزات - " . $period_title;
    
    // استعلام للحصول على عدد الحجوزات حسب اليوم
    $sql = "SELECT DATE(booking_date) as date, COUNT(*) as count 
            FROM bookings 
            WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL ";
    
    switch ($period) {
        case 'today':
            $sql .= "1 DAY";
            break;
        case 'week':
            $sql .= "7 DAY";
            break;
        case 'month':
            $sql .= "30 DAY";
            break;
        case 'year':
            $sql .= "365 DAY";
            break;
        default:
            $sql .= "30 DAY";
    }
    
    $sql .= ") GROUP BY DATE(booking_date) ORDER BY date";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chart_labels[] = date('Y-m-d', strtotime($row['date']));
            $chart_data[] = $row['count'];
            $report_data[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'count' => $row['count']
            ];
        }
    }
}

// تقرير المستخدمين
elseif ($report_type == 'users' && $users_exists) {
    $chart_title = "تقرير المستخدمين الجدد - " . $period_title;
    
    // استعلام للحصول على عدد المستخدمين الجدد حسب اليوم
    $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ";
    
    switch ($period) {
        case 'today':
            $sql .= "1 DAY";
            break;
        case 'week':
            $sql .= "7 DAY";
            break;
        case 'month':
            $sql .= "30 DAY";
            break;
        case 'year':
            $sql .= "365 DAY";
            break;
        default:
            $sql .= "30 DAY";
    }
    
    $sql .= ") GROUP BY DATE(created_at) ORDER BY date";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chart_labels[] = date('Y-m-d', strtotime($row['date']));
            $chart_data[] = $row['count'];
            $report_data[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'count' => $row['count']
            ];
        }
    }
}

// تقرير الإيرادات
elseif ($report_type == 'revenue' && $payments_exists) {
    $chart_title = "تقرير الإيرادات - " . $period_title;
    
    // استعلام للحصول على الإيرادات حسب اليوم
    $sql = "SELECT DATE(payment_date) as date, SUM(amount) as total 
            FROM payments 
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL ";
    
    switch ($period) {
        case 'today':
            $sql .= "1 DAY";
            break;
        case 'week':
            $sql .= "7 DAY";
            break;
        case 'month':
            $sql .= "30 DAY";
            break;
        case 'year':
            $sql .= "365 DAY";
            break;
        default:
            $sql .= "30 DAY";
    }
    
    $sql .= ") GROUP BY DATE(payment_date) ORDER BY date";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chart_labels[] = date('Y-m-d', strtotime($row['date']));
            $chart_data[] = $row['total'];
            $report_data[] = [
                'date' => date('Y-m-d', strtotime($row['date'])),
                'total' => $row['total']
            ];
        }
    }
}

// تقرير الرحلات
elseif ($report_type == 'flights' && $flights_exists) {
    $chart_title = "تقرير الرحلات - " . $period_title;
    
    // استعلام للحصول على عدد الرحلات حسب المدينة
    $sql = "SELECT departure as city, COUNT(*) as count FROM flights GROUP BY departure
            UNION
            SELECT destination as city, COUNT(*) as count FROM flights GROUP BY destination
            ORDER BY count DESC LIMIT 10";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chart_labels[] = $row['city'];
            $chart_data[] = $row['count'];
            $report_data[] = [
                'city' => $row['city'],
                'count' => $row['count']
            ];
        }
    }
}

// تحويل البيانات إلى تنسيق JSON للرسوم البيانية
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);
?>

<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير - لوحة تحكم المسؤول</title>
    <link rel="stylesheet" href="../css/all.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .report-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .report-filters .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .report-filters .filter-group label {
            font-size: 1.4rem;
            color: var(--black);
            font-weight: 600;
        }

        .report-filters .filter-group select {
            padding: 0.8rem 1.5rem;
            font-size: 1.6rem;
            border-radius: 0.5rem;
            border: 0.1rem solid var(--light-black);
            background-color: var(--white);
            cursor: pointer;
        }

        .report-container {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .report-container h3 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        .chart-container {
            width: 100%;
            height: 40rem;
            margin-bottom: 2rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .data-table th {
            background-color: var(--light-bg);
            padding: 1.2rem;
            text-align: right;
            font-size: 1.6rem;
            font-weight: 600;
        }

        .data-table td {
            padding: 1.2rem;
            text-align: right;
            font-size: 1.6rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .empty-message {
            text-align: center;
            font-size: 1.8rem;
            color: var(--light-black);
            padding: 2rem;
        }

        .report-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            padding: 2rem;
            text-align: center;
        }

        .summary-card i {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .summary-card h4 {
            font-size: 1.8rem;
            color: var(--light-black);
            margin-bottom: 0.5rem;
        }

        .summary-card .number {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--black);
        }

        .export-btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: #27ae60;
            color: var(--white);
            font-size: 1.6rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .export-btn:hover {
            background-color: #219653;
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

            .report-filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body style="direction: ltr;">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>التقارير</h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="report-filters">
            <div class="filter-group">
                <label for="report-type">نوع التقرير:</label>
                <select id="report-type" onchange="changeReportType(this.value)">
                    <option value="bookings" <?php echo $report_type == 'bookings' ? 'selected' : ''; ?>>تقرير الحجوزات</option>
                    <option value="users" <?php echo $report_type == 'users' ? 'selected' : ''; ?>>تقرير المستخدمين</option>
                    <option value="revenue" <?php echo $report_type == 'revenue' ? 'selected' : ''; ?>>تقرير الإيرادات</option>
                    <option value="flights" <?php echo $report_type == 'flights' ? 'selected' : ''; ?>>تقرير الرحلات</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="report-period">الفترة الزمنية:</label>
                <select id="report-period" onchange="changeReportPeriod(this.value)">
                    <option value="today" <?php echo $period == 'today' ? 'selected' : ''; ?>>اليوم</option>
                    <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>آخر أسبوع</option>
                    <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>آخر شهر</option>
                    <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>آخر سنة</option>
                </select>
            </div>
        </div>

        <!-- Report Summary -->
        <div class="report-summary">
            <?php if ($bookings_exists): ?>
            <div class="summary-card">
                <i class="fas fa-ticket-alt"></i>
                <h4>إجمالي الحجوزات</h4>
                <div class="number">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM bookings";
                    $result = $conn->query($sql);
                    echo $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($users_exists): ?>
            <div class="summary-card">
                <i class="fas fa-users"></i>
                <h4>إجمالي المستخدمين</h4>
                <div class="number">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'passenger'";
                    $result = $conn->query($sql);
                    echo $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($flights_exists): ?>
            <div class="summary-card">
                <i class="fas fa-plane-departure"></i>
                <h4>إجمالي الرحلات</h4>
                <div class="number">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM flights";
                    $result = $conn->query($sql);
                    echo $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($payments_exists): ?>
            <div class="summary-card">
                <i class="fas fa-money-bill-wave"></i>
                <h4>إجمالي الإيرادات</h4>
                <div class="number">
                    <?php
                    $sql = "SELECT SUM(amount) as total FROM payments";
                    $result = $conn->query($sql);
                    $total = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total'] : 0;
                    echo number_format($total, 2) . ' ريال';
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Report Container -->
        <div class="report-container">
            <h3><?php echo $chart_title; ?></h3>
            
            <?php if (count($chart_labels) > 0): ?>
            <div class="chart-container">
                <canvas id="reportChart"></canvas>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <?php if ($report_type == 'bookings' || $report_type == 'users'): ?>
                        <th>التاريخ</th>
                        <th>العدد</th>
                        <?php elseif ($report_type == 'revenue'): ?>
                        <th>التاريخ</th>
                        <th>الإيرادات</th>
                        <?php elseif ($report_type == 'flights'): ?>
                        <th>المدينة</th>
                        <th>عدد الرحلات</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $data): ?>
                    <tr>
                        <?php if ($report_type == 'bookings' || $report_type == 'users'): ?>
                        <td><?php echo $data['date']; ?></td>
                        <td><?php echo $data['count']; ?></td>
                        <?php elseif ($report_type == 'revenue'): ?>
                        <td><?php echo $data['date']; ?></td>
                        <td><?php echo number_format($data['total'], 2) . ' ريال'; ?></td>
                        <?php elseif ($report_type == 'flights'): ?>
                        <td><?php echo $data['city']; ?></td>
                        <td><?php echo $data['count']; ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <a href="#" class="export-btn" onclick="exportReport()"><i class="fas fa-file-export"></i> تصدير التقرير</a>
            <?php else: ?>
            <div class="empty-message">لا توجد بيانات متاحة للعرض.</div>
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
            
            // إنشاء الرسم البياني
            createChart();
        });
        
        // تغيير نوع التقرير
        function changeReportType(type) {
            const period = document.getElementById('report-period').value;
            window.location.href = `reports.php?type=${type}&period=${period}`;
        }
        
        // تغيير الفترة الزمنية
        function changeReportPeriod(period) {
            const type = document.getElementById('report-type').value;
            window.location.href = `reports.php?type=${type}&period=${period}`;
        }
        
        // إنشاء الرسم البياني
        function createChart() {
            const ctx = document.getElementById('reportChart');
            
            if (!ctx) return;
            
            const labels = <?php echo $chart_labels_json ? $chart_labels_json : '[]'; ?>;
            const data = <?php echo $chart_data_json ? $chart_data_json : '[]'; ?>;
            const reportType = '<?php echo $report_type; ?>';
            
            let chartType = 'bar';
            let chartTitle = '<?php echo $chart_title; ?>';
            let yAxisLabel = '';
            
            switch (reportType) {
                case 'bookings':
                    chartType = 'line';
                    yAxisLabel = 'عدد الحجوزات';
                    break;
                case 'users':
                    chartType = 'line';
                    yAxisLabel = 'عدد المستخدمين الجدد';
                    break;
                case 'revenue':
                    chartType = 'line';
                    yAxisLabel = 'الإيرادات (ريال)';
                    break;
                case 'flights':
                    chartType = 'bar';
                    yAxisLabel = 'عدد الرحلات';
                    break;
            }
            
            const chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartTitle,
                        data: data,
                        backgroundColor: 'rgba(142, 68, 173, 0.2)',
                        borderColor: 'rgba(142, 68, 173, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: yAxisLabel
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: reportType === 'flights' ? 'المدينة' : 'التاريخ'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }
        
        // تصدير التقرير
        function exportReport() {
            alert('سيتم تنفيذ تصدير التقرير قريبًا.');
            // يمكن تنفيذ التصدير إلى CSV أو PDF هنا
        }
    </script>
</body>
</html>
