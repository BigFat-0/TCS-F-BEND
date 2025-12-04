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
    $sql = "DELETE FROM bookings WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Booking deleted.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking not found.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.', 'error' => $e->getMessage()]);
}
?>
