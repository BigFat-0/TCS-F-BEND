<?php
header('Content-Type: text/html');

try {
    // Step 0: Include the database connection
    require 'db_connect.php';

    echo "Connecting to the database...
";

    // Step 1: Create Tables (using IF NOT EXISTS to be safe)
    $sqlCreateTables = "
    CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        duration_minutes INT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_details TEXT NOT NULL,
        service_id INT,
        staff_id INT,
        booking_date DATE,
        booking_time TIME,
        price_charged DECIMAL(10, 2),
        status VARCHAR(50) DEFAULT 'pending'
    );
    ";
    $pdo->exec($sqlCreateTables);
    echo "✅ Tables created (if they didn't exist).
";

    // Step 2: Seed Staff (using INSERT IGNORE to prevent errors on re-run)
    $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password123

    $sqlSeedStaff = "
    INSERT IGNORE INTO staff (name, email, password_hash, role) VALUES
    ('Boss Man', 'owner@salon.com', '{$password_hash}', 'owner'),
    ('Sweeney Todd', 'barber@salon.com', '{$password_hash}', 'barber'),
    ('Jessica Cutter', 'jess@salon.com', '{$password_hash}', 'barber');
    ";
    $pdo->exec($sqlSeedStaff);
    echo "✅ Staff seeded.
";

    // Step 3: Seed Services (using INSERT IGNORE)
    $sqlSeedServices = "
    INSERT IGNORE INTO services (name, description, price, duration_minutes) VALUES
    ('Gentleman''s Haircut', 'Classic haircut and style.', 25.00, 30),
    ('Beard Trim', 'Shape and trim your beard to perfection.', 15.00, 15),
    ('Hot Towel Shave', 'A luxurious shave experience.', 30.00, 45);
    ";
    $pdo->exec($sqlSeedServices);
    echo "✅ Services seeded.
";

    // Final Success Message
    echo "<h1>✅ Database Installed & Seeded!</h1> <p>You can now log in as <b>owner@salon.com</b> / <b>password123</b></p>";

} catch (PDOException $e) {
    // Error Message
    echo "<h1>❌ An Error Occurred</h1>";
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}
?>