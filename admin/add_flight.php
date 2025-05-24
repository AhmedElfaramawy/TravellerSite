<?php
session_start();
include '../php/db.php';

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../simple_login.php");
    exit();
}

$errors = [];
$success = false;

// معالجة النموذج عند الإرسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام البيانات من النموذج
    $flight_number = trim($_POST['flight_number']);
    $departure = trim($_POST['departure']);
    $destination = trim($_POST['destination']);
    $departure_time = trim($_POST['departure_date'] . ' ' . $_POST['departure_time']);
    $arrival_time = trim($_POST['arrival_date'] . ' ' . $_POST['arrival_time']);
    $price = floatval($_POST['price']);
    $seats_available = intval($_POST['seats_available']);
    
    // التحقق من البيانات
    if (empty($flight_number)) {
        $errors[] = "رقم الرحلة مطلوب";
    }
    
    if (empty($departure)) {
        $errors[] = "مدينة المغادرة مطلوبة";
    }
    
    if (empty($destination)) {
        $errors[] = "مدينة الوصول مطلوبة";
    }
    
    if ($departure === $destination) {
        $errors[] = "مدينة المغادرة ومدينة الوصول يجب أن تكونا مختلفتين";
    }
    
    if (empty($departure_time)) {
        $errors[] = "تاريخ ووقت المغادرة مطلوبان";
    }
    
    if (empty($arrival_time)) {
        $errors[] = "تاريخ ووقت الوصول مطلوبان";
    }
    
    if (strtotime($departure_time) >= strtotime($arrival_time)) {
        $errors[] = "تاريخ ووقت الوصول يجب أن يكونا بعد تاريخ ووقت المغادرة";
    }
    
    if ($price <= 0) {
        $errors[] = "السعر يجب أن يكون أكبر من صفر";
    }
    
    if ($seats_available <= 0) {
        $errors[] = "عدد المقاعد المتاحة يجب أن يكون أكبر من صفر";
    }
    
    // التحقق من عدم وجود رقم رحلة مكرر
    $check_sql = "SELECT id FROM flights WHERE flight_number = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $flight_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "رقم الرحلة موجود بالفعل، يرجى اختيار رقم آخر";
    }
    
    // إذا لم تكن هناك أخطاء، أضف الرحلة
    if (empty($errors)) {
        $sql = "INSERT INTO flights (flight_number, departure, destination, departure_time, arrival_time, price, seats_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdi", $flight_number, $departure, $destination, $departure_time, $arrival_time, $price, $seats_available);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "حدث خطأ أثناء إضافة الرحلة: " . $conn->error;
        }
    }
}

// الحصول على قائمة المدن للاختيار
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
    <title>إضافة رحلة جديدة</title>
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

        .form-container {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-container h2 {
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            border-bottom: 0.1rem solid var(--light-bg);
            padding-bottom: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
            gap: 1.5rem;
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            border: 0.1rem solid var(--light-bg);
            border-radius: 0.5rem;
        }

        .form-group textarea {
            height: 15rem;
            resize: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-secondary {
            background-color: var(--light-black);
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
            <h2>إضافة رحلة جديدة</h2>
            <div class="user-info">
                <h3>مرحباً، <?php echo $_SESSION['user_name']; ?></h3>
                <a href="../php/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i>تسجيل الخروج</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                تمت إضافة الرحلة بنجاح! <a href="flights.php">العودة إلى قائمة الرحلات</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>يرجى تصحيح الأخطاء التالية:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2>معلومات الرحلة</h2>
            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="flight_number">رقم الرحلة *</label>
                        <input type="text" id="flight_number" name="flight_number" value="<?php echo isset($_POST['flight_number']) ? htmlspecialchars($_POST['flight_number']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="departure">مدينة المغادرة *</label>
                        <input type="text" id="departure" name="departure" list="cities-list" value="<?php echo isset($_POST['departure']) ? htmlspecialchars($_POST['departure']) : ''; ?>" required>
                        <datalist id="cities-list">
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="destination">مدينة الوصول *</label>
                        <input type="text" id="destination" name="destination" list="cities-list" value="<?php echo isset($_POST['destination']) ? htmlspecialchars($_POST['destination']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="departure_date">تاريخ المغادرة *</label>
                        <input type="date" id="departure_date" name="departure_date" value="<?php echo isset($_POST['departure_date']) ? htmlspecialchars($_POST['departure_date']) : date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="departure_time">وقت المغادرة *</label>
                        <input type="time" id="departure_time" name="departure_time" value="<?php echo isset($_POST['departure_time']) ? htmlspecialchars($_POST['departure_time']) : '08:00'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="arrival_date">تاريخ الوصول *</label>
                        <input type="date" id="arrival_date" name="arrival_date" value="<?php echo isset($_POST['arrival_date']) ? htmlspecialchars($_POST['arrival_date']) : date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="arrival_time">وقت الوصول *</label>
                        <input type="time" id="arrival_time" name="arrival_time" value="<?php echo isset($_POST['arrival_time']) ? htmlspecialchars($_POST['arrival_time']) : '10:00'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">السعر ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '100.00'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="seats_available">المقاعد المتاحة *</label>
                        <input type="number" id="seats_available" name="seats_available" min="1" value="<?php echo isset($_POST['seats_available']) ? htmlspecialchars($_POST['seats_available']) : '100'; ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="flights.php" class="btn btn-secondary"><i class="fas fa-times"></i>إلغاء</a>
                    <button type="submit" class="btn"><i class="fas fa-plus"></i>إضافة الرحلة</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // للتحقق من أن تاريخ ووقت الوصول بعد تاريخ ووقت المغادرة
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const departureDate = document.getElementById('departure_date').value;
                const departureTime = document.getElementById('departure_time').value;
                const arrivalDate = document.getElementById('arrival_date').value;
                const arrivalTime = document.getElementById('arrival_time').value;
                
                const departureDateTime = new Date(departureDate + 'T' + departureTime);
                const arrivalDateTime = new Date(arrivalDate + 'T' + arrivalTime);
                
                if (departureDateTime >= arrivalDateTime) {
                    alert('تاريخ ووقت الوصول يجب أن يكونا بعد تاريخ ووقت المغادرة');
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
