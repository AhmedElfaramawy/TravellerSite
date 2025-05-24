<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'php/db.php';

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111111;
            color: white;
            margin: 0;
            padding: 0;
        }
        
        .bookings-container {
            width: 90%;
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 20px;
        }
        
        h1, h2 {
            color: #29D9D5;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        
        .booking-card {
            background: #222;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            text-align: left;
            border-left: 4px solid #29D9D5;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(41, 217, 213, 0.2);
        }
        
        .booking-card h3 {
            color: #29D9D5;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .booking-card h3 i {
            margin-right: 10px;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .booking-details p {
            margin: 8px 0;
            font-size: 1rem;
        }
        
        .booking-details strong {
            color: #29D9D5;
        }
        
        .booking-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-view {
            background-color: #29D9D5;
            color: #111;
        }
        
        .btn-view:hover {
            background-color: #1ba8a5;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background-color: #d9534f;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #c9302c;
            transform: translateY(-2px);
        }
        
        .no-bookings {
            text-align: center;
            padding: 40px 20px;
            background: #222;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .no-bookings i {
            font-size: 3rem;
            color: #29D9D5;
            margin-bottom: 20px;
            display: block;
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #333;
            color: #29D9D5;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 1px solid #29D9D5;
        }
        
        .back-btn:hover {
            background-color: rgba(41, 217, 213, 0.1);
            transform: translateY(-2px);
        }
        
        .user-select {
            margin: 30px 0;
            padding: 20px;
            text-align: center;
            background: #222;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .user-select h3 {
            color: #29D9D5;
            margin-bottom: 15px;
        }
        
        select {
            padding: 12px 15px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #444;
            background-color: #333;
            color: white;
            font-size: 16px;
            min-width: 250px;
        }
        
        select:focus {
            border-color: #29D9D5;
            outline: none;
        }
        
        button.view-btn {
            padding: 12px 25px;
            margin: 5px;
            border-radius: 5px;
            background: #29D9D5;
            color: #111;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        button.view-btn:hover {
            background-color: #1ba8a5;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        
        .success {
            background: rgba(46, 125, 50, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .error {
            background: rgba(198, 40, 40, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }
        
        .navigation {
            margin-top: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .navigation a {
            padding: 10px 20px;
            color: #29D9D5;
            text-decoration: none;
            background: #333;
            border-radius: 5px;
            transition: all 0.3s ease;
            border: 1px solid #29D9D5;
        }
        
        .navigation a:hover {
            background-color: rgba(41, 217, 213, 0.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'php/header.php'; ?>
    
    <div class="bookings-container">
        <h1>View Bookings</h1>
        
        <div class="user-select">
            <h3><i class="fas fa-user"></i> Select User</h3>
            <form method="get">
                <select name="user_id">
                    <?php
                    // Get all users
                    $users = $conn->query("SELECT id, name, email FROM users");
                    if ($users && $users->num_rows > 0) {
                        while ($user = $users->fetch_assoc()) {
                            $selected = (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : '';
                            echo "<option value='" . $user['id'] . "' $selected>" . $user['name'] . " (" . $user['email'] . ")</option>";
                        }
                    } else {
                        echo "<option value=''>No users found</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="view-btn"><i class="fas fa-search"></i> View Bookings</button>
            </form>
        </div>
        
        <?php
        // Check if user ID is provided
        if (isset($_GET['user_id'])) {
            $userId = $_GET['user_id'];
            
            // Get user info
            $userQuery = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
            $userQuery->bind_param("i", $userId);
            $userQuery->execute();
            $userResult = $userQuery->get_result();
            $user = $userResult->fetch_assoc();
            
            if ($user) {
                echo "<h2>Bookings for " . $user['name'] . "</h2>";
                
                // Get bookings for this user
                $sql = "SELECT b.*, f.flight_number, f.departure, f.destination, f.departure_time, f.arrival_time, f.price 
                        FROM bookings b 
                        JOIN flights f ON b.flight_id = f.id 
                        WHERE b.user_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    while ($booking = $result->fetch_assoc()) {
                        ?>
                        <div class="booking-card">
                            <h3><i class="fas fa-plane"></i> Flight #<?php echo $booking['flight_number']; ?></h3>
                            
                            <div class="booking-details">
                                <div class="flight-details">
                                    <p><strong>From:</strong> <?php echo $booking['departure']; ?></p>
                                    <p><strong>To:</strong> <?php echo $booking['destination']; ?></p>
                                    <p><strong>Departure:</strong> <?php echo $booking['departure_time']; ?></p>
                                    <p><strong>Arrival:</strong> <?php echo $booking['arrival_time']; ?></p>
                                    <p><strong>Price:</strong> $<?php echo $booking['price']; ?></p>
                                </div>
                                
                                <div class="traveler-details">
                                    <p><strong>Passenger:</strong> <?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $booking['phone_number']; ?></p>
                                    <p><strong>Passport #:</strong> <?php echo $booking['passport_number']; ?></p>
                                    <p><strong>Nationality:</strong> <?php echo $booking['nationality']; ?></p>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="#" class="btn btn-view"><i class="fas fa-ticket-alt"></i> View Ticket</a>
                                <a href="#" class="btn btn-cancel"><i class="fas fa-times-circle"></i> Cancel Booking</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='no-bookings'>
                            <i class='fas fa-calendar-times'></i>
                            <h3>No Bookings Found</h3>
                            <p>This user doesn't have any bookings yet.</p>
                          </div>";
                    
                    // Check if bookings table exists
                    $tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
                    if ($tableCheck->num_rows == 0) {
                        echo "<div class='message error'><i class='fas fa-exclamation-triangle'></i> The bookings table does not exist in the database.</div>";
                    } else {
                        // Check if there are any bookings at all
                        $bookingsCheck = $conn->query("SELECT COUNT(*) as count FROM bookings");
                        $bookingsCount = $bookingsCheck->fetch_assoc()['count'];
                        
                        if ($bookingsCount == 0) {
                            echo "<div class='message info'><i class='fas fa-info-circle'></i> There are no bookings in the database yet.</div>";
                        }
                    }
                }
                
                $stmt->close();
            } else {
                echo "<div class='message error'><i class='fas fa-exclamation-triangle'></i> User not found.</div>";
            }
            $userQuery->close();
        }
        
        // Add a button to add test booking for development purposes
        echo "<div class='navigation-buttons'>";
        echo "<a href='index.html' class='back-btn'><i class='fas fa-home'></i> Back to Home</a>";
        echo "<a href='php/add_test_booking.php' class='back-btn'><i class='fas fa-plus-circle'></i> Add Test Booking</a>";
        echo "</div>";
    ?>
    </div>
    
    <script>
    // Add this script to store the selected user ID in session storage
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const userId = document.querySelector('select[name="user_id"]').value;
            sessionStorage.setItem('userId', userId);
        });
    });
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 150,
        });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>
