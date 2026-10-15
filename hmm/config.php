<?php
session_start();

$host = '127.0.0.1';
$db   = 'pesez_farm_db';
$user = 'root'; // Change according to your DB setup
$pass = '';     // Change according to your DB setup
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log this error instead of throwing to frontend
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

/**
 * Checks if the user is logged in. If not, redirects to index.php.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Checks if the logged in user has a specific role.
 */
function requireRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        die("Unauthorized access.");
    }
}
?>
