<?php
header('Content-Type: application/json');

require 'db_connect.php';

$json_str = file_get_contents('php://input');
$data = json_decode($json_str, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Basic validation
if (empty($data['customer']) || empty($data['service']) || empty($data['date']) || empty($data['time'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

try {
    $sql = "INSERT INTO bookings (customer_name, customer_phone, service, barber, booking_date, booking_time, status) VALUES (:customer_name, :customer_phone, :service, :barber, :booking_date, :booking_time, :status)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':customer_name' => $data['customer'] ?? null,
        ':customer_phone' => $data['phone'] ?? null,
        ':service' => $data['service'] ?? null,
        ':barber' => $data['barber'] ?? null,
        ':booking_date' => $data['date'] ?? null,
        ':booking_time' => $data['time'] ?? null,
        ':status' => $data['status'] ?? 'pending'
    ]);

    $lastInsertId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Booking saved successfully.', 'id' => $lastInsertId]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save booking.', 'error' => $e->getMessage()]);
}

?>
