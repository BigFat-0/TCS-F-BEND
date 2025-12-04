<?php require 'auth_session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hair Cutting Hub</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="" id="theme-css">
    <script>
        const selectedTheme = localStorage.getItem('selectedTheme');
        if (selectedTheme && selectedTheme !== 'default') {
            document.getElementById('theme-css').href = `themes/${selectedTheme}-theme.css`;
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard" id="admin-dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-cut"></i>
                <h3>Hair Cutting Hub</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#bookings" class="nav-item" data-section="bookings">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <a href="#services" class="nav-item" data-section="services">
                    <i class="fas fa-cut"></i>
                    <span>Manage Services</span>
                </a>
                <a href="#staff" class="nav-item" data-section="staff">
                    <i class="fas fa-users"></i>
                    <span>Manage Staff</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1 id="page-title">Dashboard</h1>
                <div class="header-actions">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span>Admin</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Section -->
            <div class="content-section active" id="dashboard-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-info">
                            <h3 id="total-bookings">0</h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-info">
                            <h3 id="total-revenue">$0</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
                <!-- Recent Bookings can be added here -->
            </div>

            <!-- Bookings Section -->
            <div class="content-section" id="bookings-section">
                <div class="section-header">
                    <h3>All Bookings</h3>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bookings-table"></tbody>
                    </table>
                </div>
            </div>

            <!-- Manage Services Section -->
            <div class="content-section" id="services-section">
                <div class="section-header">
                    <h3>Manage Services</h3>
                    <button class="btn-primary" id="add-service-btn"><i class="fas fa-plus"></i> Add Service</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="services-table"></tbody>
                    </table>
                </div>
            </div>

            <!-- Manage Staff Section -->
            <div class="content-section" id="staff-section">
                <div class="section-header">
                    <h3>Manage Staff</h3>
                    <button class="btn-primary" id="add-staff-btn"><i class="fas fa-plus"></i> Add Staff</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staff-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals will be defined in a separate file or at the end of the body -->
    <!-- Modals -->
    <div id="service-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Service</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form id="service-form">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration" required>
                </div>
                <button type="submit" class="btn-primary">Save Service</button>
            </form>
        </div>
    </div>

    <div id="staff-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Staff</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form id="staff-form">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Save Staff</button>
            </form>
        </div>
    </div>

    <script src="admin-script.js"></script>
</body>
</html>