<?php
// Database configuration
$host = 'localhost'; 
$dbname = 'mobilegarage';
$username = 'root'; 
$password = '';

try {
    // Set options to prevent "MySQL server has gone away" errors
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false, // Don't use persistent connections to avoid issues
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 600, // 10 minutes timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, 
                                         wait_timeout=1200, 
                                         interactive_timeout=1200,
                                         net_read_timeout=120,
                                         net_write_timeout=120"
    ];

    // Create a new PDO instance with the options
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Log successful connection
    error_log("Database connection established successfully");
    
    return $pdo;
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    throw $e;
}
?>