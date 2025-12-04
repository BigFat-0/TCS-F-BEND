<?php
header('Content-Type: application/json');
require 'db_connect.php';

try {
    $sql = "
        SELECT 
            b.id, 
            b.customer_name, 
            b.customer_phone, 
            b.customer_email, 
            s.name as service_name, 
            st.name as staff_name, 
            b.booking_date, 
            b.booking_time, 
            b.notes, 
            b.price_charged,
            b.status
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN staff st ON b.staff_id = st.id
        ORDER BY b.booking_date DESC, b.booking_time DESC
    ";
    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll();
    echo json_encode($bookings);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>