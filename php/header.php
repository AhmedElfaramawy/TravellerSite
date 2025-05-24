<?php
/**
 * Shared header for all pages
 */
?>
<!-- header section starts -->
<header class="header">
    <div id="menu-btn" class="fas fa-bars"></div>
    <a data-aos="zoom-in-left" data-aos-delay="150" href="/Traveller-Site-main/index.html" class="logo"><i class="fas fa-paper-plane"></i>Book airline tickets \ B32</a>
    <nav class="navbar">
        <a data-aos="zoom-in-left" data-aos-delay="300" href="/Traveller-Site-main/index.html#home">Home</a>
        <a data-aos="zoom-in-left" data-aos-delay="450" href="/Traveller-Site-main/index.html#about">About</a>
        <a data-aos="zoom-in-left" data-aos-delay="600" href="/Traveller-Site-main/index.html#destination">Destination</a>
        <a data-aos="zoom-in-left" data-aos-delay="750" href="/Traveller-Site-main/index.html#services">Services</a>
        <a data-aos="zoom-in-left" data-aos-delay="900" href="/Traveller-Site-main/index.html#gallery">Gallery</a>
        <a data-aos="zoom-in-left" data-aos-delay="1050" href="/Traveller-Site-main/index.html#blogs">Blogs</a>
        <a data-aos="zoom-in-left" data-aos-delay="1200" href="/Traveller-Site-main/ContactUs.html">Contact Us</a>
        <a data-aos="zoom-in-left" data-aos-delay="1350" href="/Traveller-Site-main/profile.html" id="profile-link" style="display: none;">Profile</a>
        <a data-aos="zoom-in-left" data-aos-delay="1350" href="/Traveller-Site-main/admin/dashboard.php" id="admin-dashboard-link" style="display: none; color: #2ecc71;"><i class="fas fa-cogs"></i> Admin</a>
        <a data-aos="zoom-in-left" data-aos-delay="1350" href="/Traveller-Site-main/login.html" id="join-us-btn">Join Us</a>
    </nav>
    <a data-aos="zoom-in-left" data-aos-delay="1500" href="/Traveller-Site-main/php/direct_flights.php" class="btn">Book Now</a>
</header>
<!-- header section ends -->

<script>
// Check if user is logged in
document.addEventListener('DOMContentLoaded', function() {
    const userId = sessionStorage.getItem('userId');
    const userRole = sessionStorage.getItem('userRole');
    
    if (userId) {
        document.getElementById('profile-link').style.display = 'inline-block';
        document.getElementById('join-us-btn').style.display = 'none';
        
        // إظهار زر لوحة تحكم المسؤول إذا كان المستخدم مسؤولاً
        if (userRole === 'admin') {
            document.getElementById('admin-dashboard-link').style.display = 'inline-block';
        }
    }
});
</script>
