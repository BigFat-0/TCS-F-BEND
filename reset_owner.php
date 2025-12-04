<?php
header('Content-Type: text/html');
echo "<h1>Password Reset Utility for owner@salon.com</h1>";

try {
    // Step 1: Include the database connection
    require 'db_connect.php';
    echo "<p>✅ Database connection established.</p>";

    // Step 2: Generate a fresh hash
    $new_password = 'password123';
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    echo "<p>Generated new hash: <code>{$new_hash}</code></p>";

    // Step 3: Update the user's password hash in the database
    $target_email = 'owner@salon.com';
    $sql_update = "UPDATE staff SET password_hash = :hash WHERE email = :email";

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute([
        ':hash' => $new_hash,
        ':email' => $target_email
    ]);

    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Old hash replaced for {$target_email}.</p>";
    } else {
        echo "<p>⚠️ Notice: The UPDATE query ran, but no rows were changed. This probably means the hash was already the same, or the user '{$target_email}' does not exist.</p>";
    }


    // Step 4: Verify the change immediately
    echo "<h2>Verification Test</h2>";
    $sql_select = "SELECT password_hash FROM staff WHERE email = :email";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([':email' => $target_email]);
    $user = $stmt_select->fetch();

    if ($user && isset($user['password_hash'])) {
        $fetched_hash = $user['password_hash'];
        echo "<p>Fetched hash from DB: <code>{$fetched_hash}</code></p>";

        // Verify the plain-text password against the fetched hash
        if (password_verify($new_password, $fetched_hash)) {
            echo "<p style='color:green; font-weight:bold;'>✅ Verification Test Result: PASS</p>";
            echo '<p><a href="login.php">Go to Login</a></p>';
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ Verification Test Result: FAIL</p>";
            echo "<p>The new hash was stored, but password_verify failed. This is unexpected. Check PHP version or for potential data corruption.</p>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ Verification Test Result: FAIL</p>";
        echo "<p>Could not find user '{$target_email}' to perform verification.</p>";
    }

} catch (PDOException $e) {
    echo "<h1>❌ An Error Occurred</h1>";
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}
?>