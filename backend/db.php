<?php
// Database configuration
$host = 'localhost';
$dbname = 'studyfetch';
$username = 'root';
$password = ''; // Default WampServer MySQL password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    session_start();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper function to get current user ID
function getCurrentUserId() {
    session_start();
    return $_SESSION['user_id'] ?? null;
}

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>