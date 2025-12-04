<?php
header('Content-Type: text/plain');

echo "--- Database Connection Diagnostic ---

";

// --- 1. Environment Variable Check ---
echo "--- Checking for Environment Variables ---
";
$db_host_env = getenv('DB_HOST');
$is_env = !empty($db_host_env);

if ($is_env) {
    echo "✅ ENV DETECTED: DB_HOST is set to '{$db_host_env}'
";
} else {
    echo "❌ ENV MISSING - Using Fallback. The getenv('DB_HOST') call returned an empty value.
";
}

// --- 2. Resolve Credentials (same logic as db_connect.php) ---
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

// Fallback values
$fallback_host = '72.61.207.32';
if (empty($db_host)) $db_host = $fallback_host;
if (empty($db_port)) $db_port = '9000';
if (empty($db_name)) $db_name = 'default';
if (empty($db_user)) $db_user = 'user1';
if (empty($db_pass)) $db_pass = '---'; // Not showing the real password

echo "
--- Resolving Connection Details ---
";
echo "Host:         {$db_host}
";
echo "Port:         {$db_port}
";
echo "Database:     {$db_name}
";
echo "User:         {$db_user}
";

// --- 3. Connection Test ---
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
$masked_dsn = preg_replace('/password=([^;]+)/', 'password=*****', $dsn);

echo "
--- Attempting Connection ---
";
echo "Final Connection String (DSN): {$dsn}
";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Use the real password for the connection attempt
    $real_db_pass = getenv('DB_PASS');
    if (empty($real_db_pass)) {
        $real_db_pass = 'KIS710SnmXeRMVqv6zhJO4gzkvUUG1qLyO1n8Rn0HkNFAMAnf3OoqOcWGjdfdVvQ';
    }

    $pdo = new PDO($dsn, $db_user, $real_db_pass, $options);
    echo "
✅ Database connection successful!
";

} catch (\PDOException $e) {
    echo "
--- Connection Failed ---
";
    echo "❌ Raw PDOException Message: " . $e->getMessage() . "
";

    // --- 4. Detailed Error Reporting & Specific Check ---
    $is_connection_refused = str_contains($e->getMessage(), 'Connection refused');
    $used_fallback_host = ($db_host === $fallback_host);

    if (!$is_env && $used_fallback_host && $is_connection_refused) {
        echo "
💡 Suggestion: The connection was refused while using the hardcoded external IP ('{$fallback_host}').
This commonly happens in a containerized environment like Coolify when the application cannot find the database using its public IP.
Please ensure you have set the correct Internal Environment Variables for your database service in your Coolify project settings. The DB_HOST should be the name of your database service (e.g., 'db').
";
    }
}

echo "
--- End of Diagnostic ---
";
?>
