<?php
// v1/admin_revenue.php
require_once 'db_connect.php';
require_once 'admin_header.php';

// KPI: Total Revenue
$stmt = $pdo->query("SELECT SUM(actual_bill) FROM bookings WHERE status = 'completed'");
$total_revenue = $stmt->fetchColumn() ?: 0;

// KPI: Pending Revenue (Quoted but not completed)
$stmt = $pdo->query("SELECT SUM(quoted_price) FROM bookings WHERE status IN ('quoted', 'confirmed')");
$pending_revenue = $stmt->fetchColumn() ?: 0;

// KPI: Total Jobs Completed
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'");
$total_completed = $stmt->fetchColumn() ?: 0;

$year = date('Y');

// Dataset 1: Actual Revenue
$sql = "SELECT MONTH(scheduled_date) as m, SUM(actual_bill) as total 
        FROM bookings 
        WHERE status = 'completed' AND YEAR(scheduled_date) = ? 
        GROUP BY MONTH(scheduled_date) 
        ORDER BY m";
$stmt = $pdo->prepare($sql);
$stmt->execute([$year]);
$actual_results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Dataset 2: Pending Revenue
$sql = "SELECT MONTH(scheduled_date) as m, SUM(quoted_price) as total 
        FROM bookings 
        WHERE status = 'confirmed' AND YEAR(scheduled_date) = ? 
        GROUP BY MONTH(scheduled_date) 
        ORDER BY m";
$stmt = $pdo->prepare($sql);
$stmt->execute([$year]);
$pending_results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fill missing months
$chart_data_actual = [];
$chart_data_pending = [];
for ($i=1; $i<=12; $i++) {
    $chart_data_actual[] = $actual_results[$i] ?? 0;
    $chart_data_pending[] = $pending_results[$i] ?? 0;
}

?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Revenue Analytics</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="value">$<?php echo number_format($total_revenue, 2); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: var(--warning-color);">
            <h3>Pending Revenue</h3>
            <div class="value">$<?php echo number_format($pending_revenue, 2); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <h3>Jobs Completed</h3>
            <div class="value"><?php echo $total_completed; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <h3>Revenue vs Projected (<?php echo $year; ?>)</h3>
        <div style="height: 400px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: 'Actual Revenue ($)',
                data: <?php echo json_encode($chart_data_actual); ?>,
                backgroundColor: '#3498db',
                borderRadius: 4
            },
            {
                label: 'Pending/Confirmed ($)',
                data: <?php echo json_encode($chart_data_pending); ?>,
                backgroundColor: '#f1c40f',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
</body>
</html>