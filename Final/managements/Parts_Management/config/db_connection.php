<?php
// Database configuration settings
$host = 'localhost'; 
$dbname = 'webvaria_MobileGarageLarnaca';
$username = 'webvaria_MobileGarageLarnaca'; 
$password = 'vn{2i1;BA}@s';


try {
    // Set up database connection options to prevent common issues
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Make PDO throw exceptions on error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Return results as associative arrays
        PDO::ATTR_EMULATE_PREPARES => false,                   // Use real prepared statements
        PDO::ATTR_PERSISTENT => false,                         // Don't use persistent connections
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,           // Buffer query results
        PDO::ATTR_TIMEOUT => 600,                             // Set 10-minute timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, 
                                         wait_timeout=1200, 
                                         interactive_timeout=1200,
                                         net_read_timeout=120,
                                         net_write_timeout=120"  // Set MySQL connection parameters
    ];

    // Create a new database connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Make sure PDO throws exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Log successful connection for debugging
    error_log("Database connection established successfully");
    
    return $pdo;
    
} catch (PDOException $e) {
    // Log any connection errors and re-throw the exception
    error_log("Database connection failed: " . $e->getMessage());
    throw $e;
}
?>