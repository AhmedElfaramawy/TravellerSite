<?php
// التحقق من وجود الجلسة
if (!isset($_SESSION)) {
    session_start();
}

// الحصول على اسم الصفحة الحالية
$current_page = basename($_SERVER['PHP_SELF']);

// الحصول على إحصائيات سريعة
$stats = [];

// عدد الرحلات
$flights_count = 0;
$sql = "SELECT COUNT(*) as count FROM flights";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $flights_count = $row['count'];
}

// عدد الحجوزات
$bookings_count = 0;
$sql = "SELECT COUNT(*) as count FROM bookings";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $bookings_count = $row['count'];
}

// عدد المستخدمين
$users_count = 0;
$sql = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $users_count = $row['count'];
}
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-plane"></i>
            <span>نظام حجز الرحلات</span>
        </a>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <h4><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'المدير'; ?></h4>
                <span>مدير النظام</span>
            </div>
        </div>
    </div>
    
    <div class="menu">
        <div class="menu-section">
            <h3>القائمة الرئيسية</h3>
            <a href="dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>لوحة التحكم</span>
                <span class="badge">الرئيسية</span>
            </a>
        </div>
        
        <div class="menu-section">
            <h3>إدارة الرحلات</h3>
            <a href="flights.php" class="menu-item <?php echo ($current_page == 'flights.php') ? 'active' : ''; ?>">
                <i class="fas fa-plane-departure"></i>
                <span>الرحلات</span>
                <span class="badge"><?php echo $flights_count; ?></span>
            </a>
            <a href="add_flight.php" class="menu-item <?php echo ($current_page == 'add_flight.php') ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>إضافة رحلة</span>
            </a>
        </div>
        
        <div class="menu-section">
            <h3>إدارة الحجوزات</h3>
            <a href="bookings.php" class="menu-item <?php echo ($current_page == 'bookings.php' || $current_page == 'view_booking.php') ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>الحجوزات</span>
                <span class="badge"><?php echo $bookings_count; ?></span>
            </a>
        </div>
        
        <div class="menu-section">
            <h3>إدارة المستخدمين</h3>
            <a href="users.php" class="menu-item <?php echo ($current_page == 'users.php' || $current_page == 'view_user.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>المستخدمين</span>
                <span class="badge"><?php echo $users_count; ?></span>
            </a>
        </div>
        
        <div class="menu-section">
            <h3>الإدارة المالية</h3>
            <a href="payments.php" class="menu-item <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>المدفوعات</span>
            </a>
            <a href="reports.php" class="menu-item <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>التقارير</span>
            </a>
        </div>
        
        <div class="menu-section">
            <h3>إعدادات النظام</h3>
            <a href="../index.html" class="menu-item" target="_blank">
                <i class="fas fa-home"></i>
                <span>الموقع الرئيسي</span>
            </a>
            <a href="../php/logout.php" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>تسجيل الخروج</span>
            </a>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <p>© <?php echo date('Y'); ?> نظام حجز الرحلات</p>
        <p>جميع الحقوق محفوظة</p>
    </div>
</div>

<div class="mobile-menu-toggle">
    <i class="fas fa-bars"></i>
</div>

<style>
/* تنسيق القائمة الجانبية */
.sidebar {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--main-color) var(--black);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: var(--black);
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: var(--main-color);
    border-radius: 10px;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar .logo {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    font-size: 2rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 1.5rem;
    text-decoration: none;
}

.sidebar .logo i {
    color: var(--main-color);
    font-size: 2.5rem;
    margin-right: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    padding: 1rem 0;
}

.user-avatar {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background-color: var(--main-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.user-avatar i {
    font-size: 2.5rem;
    color: var(--white);
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-details h4 {
    color: var(--white);
    font-size: 1.6rem;
    margin: 0;
}

.user-details span {
    color: var(--light-white);
    font-size: 1.2rem;
}

.menu {
    flex: 1;
    padding: 1.5rem;
}

.menu-section {
    margin-bottom: 2.5rem;
}

.menu-section h3 {
    color: var(--light-white);
    font-size: 1.4rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 1.2rem 1.5rem;
    color: var(--light-white);
    text-decoration: none;
    border-radius: 0.8rem;
    margin-bottom: 0.8rem;
    transition: all 0.3s ease;
    position: relative;
}

.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--white);
}

.menu-item.active {
    background-color: var(--main-color);
    color: var(--white);
}

.menu-item i {
    font-size: 1.8rem;
    min-width: 3rem;
}

.menu-item span {
    font-size: 1.5rem;
}

.badge {
    position: absolute;
    right: 1.5rem;
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--white);
    font-size: 1.2rem;
    padding: 0.3rem 0.8rem;
    border-radius: 2rem;
}

.menu-item.active .badge {
    background-color: var(--white);
    color: var(--main-color);
}

.menu-item.logout {
    margin-top: 1rem;
    color: #ff6b6b;
}

.menu-item.logout:hover {
    background-color: rgba(255, 99, 99, 0.1);
}

.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-footer p {
    color: var(--light-white);
    font-size: 1.2rem;
    margin: 0.5rem 0;
}

/* زر القائمة للشاشات الصغيرة */
.mobile-menu-toggle {
    position: fixed;
    top: 2rem;
    left: 2rem;
    background-color: var(--main-color);
    color: var(--white);
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 999;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    display: none;
}

.mobile-menu-toggle i {
    font-size: 2rem;
}

/* تجاوب مع الشاشات الصغيرة */
@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    body {
        padding-left: 0 !important;
    }
}
</style>

<script>
// التعامل مع القائمة في الشاشات الصغيرة
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
});
</script>
