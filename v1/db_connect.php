<?php
// v1/db_connect.php

// Priority 1: Use environment variables
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_name = getenv('DB_NAME'); // Assuming DB_NAME is the env var for database name
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

// Priority 2: Fallback to hardcoded credentials
if (empty($db_host)) {
    $db_host = '72.61.207.32';
}
if (empty($db_port)) {
    $db_port = '9000';
}
if (empty($db_name)) {
    $db_name = 'default';
}
if (empty($db_user)) {
    $db_user = 'user1';
}
if (empty($db_pass)) {
    $db_pass = 'KIS710SnmXeRMVqv6zhJO4gzkvUUG1qLyO1n8Rn0HkNFAMAnf3OoqOcWGjdfdVvQ';
}

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
