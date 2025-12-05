<?php
// v1/request_quote.php
require('auth_session.php');
require('db_connect.php');

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_description = $_POST['job_description'];
    $service_address = $_POST['service_address'];
    $scheduled_date = $_POST['scheduled_date'];

    if (empty($job_description) || empty($service_address) || empty($scheduled_date)) {
        $message = "All fields are required.";
    } else {
        $user_id = $_SESSION['user_id'];
        // Default status is 'awaiting_quote' per schema default
        $sql = "INSERT INTO bookings (user_id, job_description, service_address, scheduled_date, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$user_id, $job_description, $service_address, $scheduled_date])) {
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Failed to submit request.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Quote - Cleaning Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
        <h2>Request a Quote</h2>
        <?php if ($message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Job Description</label>
                <textarea name="job_description" rows="4" placeholder="Describe your needs..." required></textarea>
            </div>
            <div class="form-group">
                <label>Service Address</label>
                <input type="text" name="service_address" required>
            </div>
            <div class="form-group">
                <label>Date/Time</label>
                <input type="datetime-local" name="scheduled_date" required>
            </div>
            <button type="submit">Submit Request</button>
        </form>
    </div>
</body>
</html>
