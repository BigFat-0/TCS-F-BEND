<?php
header('Content-Type: application/json');

require 'db_connect.php';

$json_str = file_get_contents('php://input');
$data = json_decode($json_str, true);

if ($data === null || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$id = $data['id'];

try {
    $sql = "UPDATE bookings SET 
                customer_name = :customer_name, 
                customer_phone = :customer_phone, 
                service = :service, 
                barber = :barber, 
                booking_date = :booking_date, 
                booking_time = :booking_time, 
                status = :status 
            WHERE id = :id";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':id' => $id,
        ':customer_name' => $data['customer'],
        ':customer_phone' => $data['phone'],
        ':service' => $data['service'],
        ':barber' => $data['barber'],
        ':booking_date' => $data['date'],
        ':booking_time' => $data['time'],
        ':status' => $data['status']
    ]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Booking updated.']);
    } else {
        // This can happen if the data was the same and no rows were affected
        echo json_encode(['success' => true, 'message' => 'Booking data was unchanged.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.', 'error' => $e->getMessage()]);
}
?>
