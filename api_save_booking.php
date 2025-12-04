<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

if (empty($data['customer_name']) || empty($data['service_id']) || empty($data['staff_id']) || empty($data['booking_date']) || empty($data['booking_time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

try {
    // Get service price from services table
    $stmt = $pdo->prepare("SELECT price FROM services WHERE id = :service_id");
    $stmt->execute([':service_id' => $data['service_id']]);
    $service = $stmt->fetch();

    if (!$service) {
        http_response_code(404);
        echo json_encode(['error' => 'Service not found.']);
        exit;
    }

    $sql = "INSERT INTO bookings (customer_name, customer_phone, customer_email, service_id, staff_id, booking_date, booking_time, notes, price_charged) VALUES (:customer_name, :customer_phone, :customer_email, :service_id, :staff_id, :booking_date, :booking_time, :notes, :price_charged)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':customer_name' => $data['customer_name'],
        ':customer_phone' => $data['customer_phone'] ?? null,
        ':customer_email' => $data['customer_email'] ?? null,
        ':service_id' => $data['service_id'],
        ':staff_id' => $data['staff_id'],
        ':booking_date' => $data['booking_date'],
        ':booking_time' => $data['booking_time'],
        ':notes' => $data['notes'] ?? null,
        ':price_charged' => $service['price'],
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>