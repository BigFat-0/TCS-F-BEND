<?php
// v1/dashboard.php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];
$message = '';

// 1. Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone_number']);
    $billing = trim($_POST['billing_address']);

    if ($first_name && $last_name && $phone && $billing) {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, billing_address = ? WHERE id = ?");
        if ($stmt->execute([$first_name, $last_name, $phone, $billing, $user_id])) {
            $message = "Profile updated successfully.";
        } else {
            $message = "Error updating profile.";
        }
    } else {
        $message = "All profile fields are required.";
    }
}

// 2. Handle Booking Actions (Accept, Reject, Cancel, Undo, Resubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    $new_status = '';
    $required_current_status = [];

    if ($action === 'accept') {
        $new_status = 'confirmed';
        $required_current_status = ['quoted'];
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
        $required_current_status = ['quoted'];
    } elseif ($action === 'cancel') {
        $new_status = 'cancelled';
        $required_current_status = ['awaiting_quote', 'confirmed'];
    } elseif ($action === 'undo_reject') {
        $new_status = 'quoted'; // Revert to quoted so they can accept
        $required_current_status = ['rejected'];
    } elseif ($action === 'resubmit') {
        $new_status = 'awaiting_quote'; // Resubmit for quote
        $required_current_status = ['cancelled'];
    }

    if ($new_status) {
        // Build placeholder string
        $placeholders = implode(',', array_fill(0, count($required_current_status), '?'));
        
        $sql = "UPDATE bookings SET status = ? WHERE id = ? AND user_id = ? AND status IN ($placeholders)";
        $params = array_merge([$new_status, $booking_id, $user_id], $required_current_status);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Refresh to show changes
        header("Location: dashboard.php"); 
        exit();
    }
}

// Fetch User Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <style>
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.5); 
            justify-content: center; 
            align-items: center;
        }
        .modal-content {
            background-color: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            width: 90%; 
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .action-btn-link {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            text-decoration: underline;
            margin-right: 10px;
            font-size: 0.9em;
        }
        .text-warning { color: #f39c12; }
        .text-success { color: #27ae60; }
        .text-danger { color: #c0392b; }
        
        #calendar {
            max-width: 100%;
            margin: 20px 0;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .nav-tabs button {
            margin-right: 10px;
            padding: 10px 20px;
            cursor: pointer;
            background: #eee;
            border: none;
            border-radius: 4px;
        }
        .nav-tabs button.active {
            background: #3498db;
            color: white;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="nav">
            <a href="request_quote.php" class="btn btn-success">Request New Quote</a>
            <div style="float:right;">
                <button onclick="openProfileModal()" class="btn btn-secondary">Update Details</button>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info" style="margin-top: 15px;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h2>

        <!-- Tabs -->
        <div class="nav-tabs">
            <button class="active" onclick="switchTab('list')">My Bookings</button>
            <button onclick="switchTab('calendar')">Calendar</button>
        </div>

        <!-- List View -->
        <div id="tab-list" class="tab-content active">
            <?php if (count($bookings) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($b['scheduled_date'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($b['job_description'], 0, 30)) . (strlen($b['job_description'])>30 ? '...' : ''); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $b['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $b['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        if ($b['status'] == 'quoted' || $b['status'] == 'confirmed') echo '$'.$b['quoted_price'];
                                        elseif ($b['status'] == 'completed') echo '<strong>$'.$b['actual_bill'].'</strong>';
                                        else echo '-';
                                    ?>
                                </td>
                                <td>
                                    <!-- Dynamic Action Buttons -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        
                                        <?php if ($b['status'] == 'quoted'): ?>
                                            <button type="submit" name="action" value="accept" class="btn btn-sm btn-success">Accept</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                        
                                        <?php elseif ($b['status'] == 'awaiting_quote' || $b['status'] == 'confirmed'): ?>
                                            <button type="submit" name="action" value="cancel" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                        
                                        <?php elseif ($b['status'] == 'rejected'): ?>
                                            <button type="submit" name="action" value="undo_reject" class="action-btn-link text-warning">Undo Rejection</button>
                                        
                                        <?php elseif ($b['status'] == 'cancelled'): ?>
                                            <button type="submit" name="action" value="resubmit" class="action-btn-link text-success">Resubmit</button>
                                        
                                        <?php elseif ($b['status'] == 'completed'): ?>
                                            <span class="text-success"><i class="fas fa-check"></i> Done</span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bookings found.</p>
            <?php endif; ?>
        </div>

        <!-- Calendar View -->
        <div id="tab-calendar" class="tab-content">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <h3>Update Profile</h3>
            <form method="post">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Billing Address</label>
                    <textarea name="billing_address" required><?php echo htmlspecialchars($user['billing_address']); ?></textarea>
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeProfileModal()" class="btn btn-danger">Cancel</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Logic
        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
        }
        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        // Tab Logic
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tabs button').forEach(el => el.classList.remove('active'));
            
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Highlight button (simple find based on text for this demo, or add IDs)
            if(tabName === 'list') document.querySelector('.nav-tabs button:nth-child(1)').classList.add('active');
            if(tabName === 'calendar') {
                document.querySelector('.nav-tabs button:nth-child(2)').classList.add('active');
                setTimeout(() => { calendar.render(); }, 100); // Re-render when visible
            }
        }

        // Calendar Logic
        var calendar;
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: 'api_customer_calendar.php',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                height: 500
            });
            calendar.render();
        });
    </script>
</body>
</html>