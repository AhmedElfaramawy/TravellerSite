<?php
// Direct flights display page
include 'db.php';

// Create flights table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(20) NOT NULL,
    departure VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    seats_available INT NOT NULL
)";

if ($conn->query($createTable) === FALSE) {
    echo "Error creating table: " . $conn->error;
}

// البيانات التجريبية الآن موجودة في ملف flightbooking.sql
// لذلك لا نحتاج إلى إدخالها هنا مرة أخرى
$checkData = "SELECT COUNT(*) as count FROM flights";
$result = $conn->query($checkData);
$row = $result->fetch_assoc();

// فقط للتنبيه إذا كان الجدول فارغًا
if ($row['count'] == 0) {
    echo "<div style='background-color: #ffdddd; padding: 10px; margin: 10px; border-radius: 5px;'>
        <strong>تنبيه:</strong> جدول الرحلات فارغ. يرجى استيراد ملف flightbooking.sql إلى قاعدة البيانات.
    </div>";
}

// Get flights
$sql = "SELECT * FROM flights ORDER BY departure_time ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Flights</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/direct_flights.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="flights-section">
        <div class="heading" data-aos="fade-up" data-aos-delay="150">
            <span>discover</span>
            <h1>Available Flights</h1>
        </div>
        
        <div class="container">
            <a href="../index.html" class="btn" data-aos="fade-up" data-aos-delay="300"><i class="fas fa-home"></i> Back to Home</a>
        
            <div class="flight-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $delay = 300; $count = 0; while($flight = $result->fetch_assoc()): $count++; $delay += 150; ?>
                        <div class="flight-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="flight-header">
                                <div class="flight-number">
                                    <i class="fas fa-plane"></i>
                                    <h3><?php echo $flight['flight_number']; ?></h3>
                                </div>
                                <div class="flight-price">
                                    <span>$<?php echo $flight['price']; ?></span>
                                </div>
                            </div>
                            
                            <div class="flight-route">
                                <div class="departure">
                                    <i class="fas fa-plane-departure"></i>
                                    <div>
                                        <h4><?php echo $flight['departure']; ?></h4>
                                        <p><?php echo date('d M Y, H:i', strtotime($flight['departure_time'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="route-line">
                                    <div class="line"></div>
                                    <div class="duration">
                                        <?php 
                                            $departure = new DateTime($flight['departure_time']);
                                            $arrival = new DateTime($flight['arrival_time']);
                                            $interval = $departure->diff($arrival);
                                            echo $interval->format('%h h %i m');
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="arrival">
                                    <i class="fas fa-plane-arrival"></i>
                                    <div>
                                        <h4><?php echo $flight['destination']; ?></h4>
                                        <p><?php echo date('d M Y, H:i', strtotime($flight['arrival_time'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flight-details">
                                <div class="seats">
                                    <i class="fas fa-chair"></i>
                                    <span><?php echo $flight['seats_available']; ?> seats available</span>
                                </div>
                                
                                <a href="../booking.html" class="btn" onclick="saveFlightData(<?php echo htmlspecialchars(json_encode($flight)); ?>)">
                                    <i class="fas fa-ticket-alt"></i> Book Now
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-flights" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>No flights available at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <div class="debug-info" data-aos="fade-up" data-aos-delay="300">
        <h3>Database Connection Info:</h3>
        <p>Host: <?php echo $host; ?></p>
        <p>Database: <?php echo $database; ?></p>
        <p>Connection Status: <?php echo ($conn->connect_error) ? 'Failed: ' . $conn->connect_error : 'Connected'; ?></p>
        <?php if ($result): ?>
            <p>Query Result: <?php echo $result->num_rows; ?> flights found</p>
        <?php else: ?>
            <p>Query Error: <?php echo $conn->error; ?></p>
        <?php endif; ?>
    </div>

    <script>
        function saveFlightData(flight) {
            // Make sure the flight object has the correct property names
            // that match what booking.js and continue.js expect
            console.log('Saving flight data:', flight);
            sessionStorage.setItem('selectedFlight', JSON.stringify(flight));
            
            // Also store the flight ID separately to ensure it's accessible
            sessionStorage.setItem('selectedFlightId', flight.id);
        }
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
