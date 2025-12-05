<?php
// v1/dashboard.php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];

// Handle Customer Actions (Accept/Reject Quote)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    $new_status = '';
    if ($action === 'accept') {
        $new_status = 'confirmed';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    }

    if ($new_status) {
        // Verify ownership and current status before updating
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND user_id = ? AND status = 'quoted'");
        $stmt->execute([$new_status, $booking_id, $user_id]);
        header("Location: dashboard.php"); 
        exit();
    }
}

// Fetch Bookings
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cleaning Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="nav">
            <a href="request_quote.php" class="btn btn-success">Request New Quote</a>
            <a href="logout.php" style="float:right;">Logout</a>
        </div>
        <h2>My Bookings</h2>
        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date Scheduled</th>
                        <th>Description</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Quote / Bill</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['scheduled_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['job_description']); ?></td>
                            <td><?php echo htmlspecialchars($booking['service_address']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    if ($booking['status'] == 'quoted' || $booking['status'] == 'confirmed') {
                                        echo '$' . htmlspecialchars($booking['quoted_price']);
                                    } elseif ($booking['status'] == 'completed') {
                                        echo 'Final Bill: $' . htmlspecialchars($booking['actual_bill']);
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($booking['status'] == 'quoted'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-success">Accept</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                                    </form>
                                <?php elseif ($booking['status'] == 'confirmed'): ?>
                                    <span>Job Confirmed</span>
                                <?php elseif ($booking['status'] == 'completed'): ?>
                                    <span>Completed</span>
                                <?php elseif ($booking['status'] == 'rejected'): ?>
                                    <span>Rejected</span>
                                <?php else: ?>
                                    <span>Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found. <a href="request_quote.php">Request one now.</a></p>
        <?php endif; ?>
    </div>
</body>
</html>