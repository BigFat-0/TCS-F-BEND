<?php
// v1/admin_panel.php (Dashboard)
require_once 'db_connect.php';
require_once 'admin_header.php';

// Today's Bookings
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT b.*, u.email FROM bookings b JOIN users u ON b.user_id = u.id WHERE DATE(scheduled_date) = ? ORDER BY scheduled_date ASC");
$stmt->execute([$today]);
$todays_bookings = $stmt->fetchAll();

// Recent Requests (Awaiting Quote)
$stmt = $pdo->query("SELECT b.*, u.email FROM bookings b JOIN users u ON b.user_id = u.id WHERE status = 'awaiting_quote' ORDER BY created_at DESC LIMIT 5");
$recent_requests = $stmt->fetchAll();
?>

<div class="admin-container">
    <div class="page-header">
        <h1>Dashboard Overview</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card" style="grid-column: span 2;">
            <h3>Today's Schedule (<?php echo date('M d'); ?>)</h3>
            <?php if (count($todays_bookings) > 0): ?>
                <ul>
                    <?php foreach ($todays_bookings as $b): ?>
                        <li style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                            <strong><?php echo date('H:i', strtotime($b['scheduled_date'])); ?></strong> - 
                            <?php echo htmlspecialchars($b['email']); ?>
                            <span class="badge badge-<?php echo $b['status']; ?>" style="float:right;"><?php echo $b['status']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #7f8c8d;">No bookings scheduled for today.</p>
            <?php endif; ?>
            <a href="admin_calendar.php" class="btn btn-sm btn-primary" style="margin-top: 10px;">View Calendar</a>
        </div>

        <div class="stat-card">
            <h3>Recent Requests</h3>
            <?php if (count($recent_requests) > 0): ?>
                <ul>
                    <?php foreach ($recent_requests as $r): ?>
                        <li style="margin-bottom: 10px;">
                            #<?php echo $r['id']; ?> - <?php echo htmlspecialchars($r['email']); ?>
                            <br>
                            <a href="admin_booking_edit.php?id=<?php echo $r['id']; ?>" style="font-size: 0.85em;">Review</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #7f8c8d;">No pending requests.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div style="display: flex; gap: 10px;">
        <a href="admin_bookings.php?action=create" class="btn btn-primary">Create Booking</a>
        <a href="admin_users.php" class="btn btn-primary">Manage Users</a>
    </div>

</div>
</body>
</html>
