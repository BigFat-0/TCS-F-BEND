<?php
// v1/admin_panel.php
require('auth_session.php');
require('db_connect.php');

// Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: dashboard.php"); // Redirect non-staff to customer dashboard
    exit();
}

$message = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['quote_booking_id'])) {
        // Send Quote
        $booking_id = $_POST['quote_booking_id'];
        $quoted_price = $_POST['quoted_price'];

        if (is_numeric($quoted_price)) {
            $stmt = $pdo->prepare("UPDATE bookings SET quoted_price = ?, status = 'quoted' WHERE id = ?");
            if ($stmt->execute([$quoted_price, $booking_id])) {
                $message = "Quote sent successfully.";
            }
        }
    } elseif (isset($_POST['complete_booking_id'])) {
        // Complete Job
        $booking_id = $_POST['complete_booking_id'];
        $actual_bill = $_POST['actual_bill'];

        if (is_numeric($actual_bill)) {
            $stmt = $pdo->prepare("UPDATE bookings SET actual_bill = ?, status = 'completed' WHERE id = ?");
            if ($stmt->execute([$actual_bill, $booking_id])) {
                 $message = "Job marked as completed.";
            }
        }
    }
}

// Fetch New Requests (awaiting_quote)
// Joining with users to get phone number
$sql_new = "SELECT b.*, u.phone_number, u.email FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.status = 'awaiting_quote' ORDER BY b.created_at ASC";
$stmt_new = $pdo->query($sql_new);
$new_requests = $stmt_new->fetchAll();

// Fetch Active Jobs (confirmed)
$sql_active = "SELECT b.*, u.phone_number, u.email FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.status = 'confirmed' ORDER BY b.scheduled_date ASC";
$stmt_active = $pdo->query($sql_active);
$active_jobs = $stmt_active->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Cleaning Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="nav">
            <span>Welcome, Staff</span>
            <a href="logout.php" style="float:right;">Logout</a>
        </div>
        <h2>Admin Panel</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h3>Section 1: New Requests (Awaiting Quote)</h3>
        <?php if (count($new_requests) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Description</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($new_requests as $req): ?>
                        <tr>
                            <td>#<?php echo $req['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($req['email']); ?><br>
                                Phone: <?php echo htmlspecialchars($req['phone_number']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($req['job_description']); ?></td>
                            <td><?php echo htmlspecialchars($req['service_address']); ?></td>
                            <td>
                                <form method="post" style="display:flex; gap:5px;">
                                    <input type="hidden" name="quote_booking_id" value="<?php echo $req['id']; ?>">
                                    <input type="number" name="quoted_price" step="0.01" placeholder="Price" required style="width: 100px; padding: 5px;">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px;">Send Quote</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No new requests.</p>
        <?php endif; ?>

        <h3 style="margin-top: 40px;">Section 2: Active Jobs (Confirmed)</h3>
        <?php if (count($active_jobs) > 0): ?>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Quoted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_jobs as $job): ?>
                        <tr>
                            <td>#<?php echo $job['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($job['email']); ?><br>
                                Phone: <?php echo htmlspecialchars($job['phone_number']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($job['job_description']); ?></td>
                            <td><?php echo htmlspecialchars($job['scheduled_date']); ?></td>
                            <td>$<?php echo htmlspecialchars($job['quoted_price']); ?></td>
                            <td>
                                <form method="post" style="display:flex; gap:5px;">
                                    <input type="hidden" name="complete_booking_id" value="<?php echo $job['id']; ?>">
                                    <input type="number" name="actual_bill" step="0.01" placeholder="Actual Bill" required style="width: 100px; padding: 5px;">
                                    <button type="submit" class="btn btn-success" style="padding: 5px 10px;">Complete Job</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No active jobs.</p>
        <?php endif; ?>
    </div>
</body>
</html>
