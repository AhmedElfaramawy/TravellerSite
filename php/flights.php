<?php
header("Content-Type: application/json");
include 'db.php'; // الاتصال بقاعدة البيانات

// قراءة بيانات البحث من `POST`
$data = json_decode(file_get_contents("php://input"), true);

$destination = isset($data['destination']) ? mysqli_real_escape_string($conn, $data['destination']) : "";
$date = isset($data['date']) ? mysqli_real_escape_string($conn, $data['date']) : "";
$travelers = isset($data['travelers']) ? (int) $data['travelers'] : 1;

$sql = "SELECT * FROM flights WHERE 1=1";

if (!empty($destination)) {
    $sql .= " AND destination LIKE '%$destination%'";
}

if (!empty($date)) {
    $sql .= " AND DATE(departure_time) = '$date'";
}

if ($travelers > 0) {
    $sql .= " AND seats_available >= $travelers";
}

$sql .= " ORDER BY departure_time ASC";

$result = $conn->query($sql);
$flights = [];

while ($row = $result->fetch_assoc()) {
    $flights[] = $row;
}

// إرجاع البيانات بصيغة JSON
if (empty($flights)) {
    echo json_encode(["status" => "no_results", "message" => "لا توجد رحلات متاحة تطابق البحث"]);
} else {
    echo json_encode(["status" => "success", "flights" => $flights]);
}
?>