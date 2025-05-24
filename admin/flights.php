<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../simple_login.php");
    exit();
}

// حذف رحلة
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $flight_id = $_GET['delete'];
    
    // التحقق من وجود حجوزات مرتبطة بالرحلة
    $check_bookings = "SELECT COUNT(*) as count FROM bookings WHERE flight_id = ?";
    $stmt = $conn->prepare($check_bookings);
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $delete_error = "لا يمكن حذف هذه الرحلة لأنها مرتبطة بـ " . $row['count'] . " حجز.";
    } else {
        // حذف الرحلة
        $delete_sql = "DELETE FROM flights WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $flight_id);
        
        if ($stmt->execute()) {
            $delete_success = "تم حذف الرحلة بنجاح.";
        } else {
            $delete_error = "حدث خطأ أثناء حذف الرحلة: " . $conn->error;
        }
    }
}

// البحث والتصفية
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_departure = isset($_GET['departure']) ? $_GET['departure'] : '';
$filter_destination = isset($_GET['destination']) ? $_GET['destination'] : '';

// بناء استعلام البحث
$sql = "SELECT * FROM flights WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (flight_number LIKE ? OR departure LIKE ? OR destination LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($filter_departure)) {
    $sql .= " AND departure = ?";
    $params[] = $filter_departure;
    $types .= "s";
}

if (!empty($filter_destination)) {
    $sql .= " AND destination = ?";
    $params[] = $filter_destination;
    $types .= "s";
}

$sql .= " ORDER BY departure_time ASC";

// تنفيذ الاستعلام
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$flights_result = $stmt->get_result();

// الحصول على قائمة المدن للتصفية
$cities_sql = "SELECT DISTINCT departure FROM flights UNION SELECT DISTINCT destination FROM flights ORDER BY departure";
$cities_result = $conn->query($cities_sql);
$cities = [];
if ($cities_result && $cities_result->num_rows > 0) {
    while ($row = $cities_result->fetch_assoc()) {
        $cities[] = $row['departure'];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الرحلات</title>
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

        .search-filter {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .search-filter form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-end;
        }

        .search-filter .form-group {
            flex: 1;
            min-width: 20rem;
        }

        .search-filter label {
            display: block;
            font-size: 1.6rem;
            color: var(--black);
            margin-bottom: 0.5rem;
        }

        .search-filter input,
        .search-filter select {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            border: 0.1rem solid var(--light-bg);
            border-radius: 0.5rem;
        }

        .search-filter .btn-group {
            display: flex;
            gap: 1rem;
        }

        .search-filter .btn-reset {
            background-color: var(--light-black);
        }

        .flights-table {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .flights-table h2 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            border-bottom: 0.1rem solid var(--light-bg);
            padding-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 1.2rem;
            text-align: left;
            font-size: 1.6rem;
            border-bottom: 0.1rem solid var(--light-bg);
        }

        table th {
            background-color: var(--light-bg);
            color: var(--black);
        }

        table tr:hover {
            background-color: var(--light-bg);
        }

        .action-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            font-size: 1.4rem;
            border-radius: 0.3rem;
            margin-right: 0.5rem;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #3498db;
            color: var(--white);
        }

        .delete-btn {
            background-color: #e74c3c;
            color: var(--white);
        }

        .view-btn {
            background-color: #2ecc71;
            color: var(--white);
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
            transition: all 0.3s ease;
        }

        .pagination a.active,
        .pagination a:hover {
            background-color: var(--main-color);
            color: var(--white);
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }
            body {
                padding-left: 0;
            }
            .sidebar {
                left: -30rem;
                transition: all 0.3s ease;
            }
            .sidebar.active {
                left: 0;
            }
            .toggle-btn {
                display: block;
            }
        }

        @media (max-width: 450px) {
            html {
                font-size: 50%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>إدارة الرحلات</h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

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
            <form action="" method="GET">
                <div class="form-group">
                    <label for="search">بحث:</label>
                    <input type="text" id="search" name="search" placeholder="رقم الرحلة، المدينة..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label for="departure">مدينة المغادرة:</label>
                    <select id="departure" name="departure">
                        <option value="">الكل</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city; ?>" <?php echo ($filter_departure == $city) ? 'selected' : ''; ?>>
                                <?php echo $city; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="destination">مدينة الوصول:</label>
                    <select id="destination" name="destination">
                        <option value="">الكل</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city; ?>" <?php echo ($filter_destination == $city) ? 'selected' : ''; ?>>
                                <?php echo $city; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn"><i class="fas fa-search"></i>بحث</button>
                    <a href="flights.php" class="btn btn-reset"><i class="fas fa-redo"></i>إعادة تعيين</a>
                </div>
            </form>
        </div>

        <!-- Flights Table -->
        <div class="flights-table">
            <h2>
                قائمة الرحلات
                <a href="add_flight.php" class="btn"><i class="fas fa-plus"></i>إضافة رحلة جديدة</a>
            </h2>
            <table>
                <thead>
                    <tr>
                        <th>رقم الرحلة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>تاريخ المغادرة</th>
                        <th>تاريخ الوصول</th>
                        <th>السعر</th>
                        <th>المقاعد المتاحة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($flights_result && $flights_result->num_rows > 0) {
                        while ($flight = $flights_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$flight['flight_number']}</td>";
                            echo "<td>{$flight['departure']}</td>";
                            echo "<td>{$flight['destination']}</td>";
                            echo "<td>" . date('Y-m-d H:i', strtotime($flight['departure_time'])) . "</td>";
                            echo "<td>" . date('Y-m-d H:i', strtotime($flight['arrival_time'])) . "</td>";
                            echo "<td>$" . number_format($flight['price'], 2) . "</td>";
                            echo "<td>{$flight['seats_available']}</td>";
                            echo "<td>
                                    <a href='edit_flight.php?id={$flight['id']}' class='action-btn edit-btn'><i class='fas fa-edit'></i></a>
                                    <a href='flights.php?delete={$flight['id']}' class='action-btn delete-btn' onclick='return confirm(\"هل أنت متأكد من حذف هذه الرحلة؟\")'><i class='fas fa-trash'></i></a>
                                    <a href='view_flight.php?id={$flight['id']}' class='action-btn view-btn'><i class='fas fa-eye'></i></a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center;'>لا توجد رحلات متاحة</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // للتعامل مع القائمة الجانبية في الشاشات الصغيرة
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.sidebar');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
