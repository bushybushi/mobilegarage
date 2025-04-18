<?php
// Database configuration settings
$host = 'localhost'; 
$dbname = 'webvaria_MobileGarageLarnaca';
$username = 'webvaria_MobileGarageLarnaca'; 
$password = 'vn{2i1;BA}@s';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set up custom error logging
$logFile = __DIR__ . '/../logs/db_connection.log';
if (!function_exists('writeLog')) {
    function writeLog($message) {
        global $logFile;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

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
    
    // Verify the connection and table existence
    try {
        // Log all tables in the database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        writeLog("All tables in database '$dbname': " . implode(", ", $tables));
        
        // Check for Invoices table with case-insensitive search
        $stmt = $pdo->query("SHOW TABLES LIKE '%invoices%'");
        $invoiceTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        writeLog("Tables matching 'invoices' (case-insensitive): " . implode(", ", $invoiceTables));
        
        // Check user permissions
        $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        writeLog("Current user permissions: " . implode(", ", $grants));
        
        if ($stmt->rowCount() === 0) {
            writeLog("Table 'Invoices' does not exist in database '$dbname'");
        } else {
            writeLog("Table 'Invoices' exists in database '$dbname'");
        }
    } catch (PDOException $e) {
        writeLog("Error checking table existence: " . $e->getMessage());
    }
    
    // Log successful connection for debugging
    writeLog("Database connection established successfully to database: $dbname");
    
    return $pdo;
    
} catch (PDOException $e) {
    // Log any connection errors and re-throw the exception
    writeLog("Database connection failed: " . $e->getMessage());
    writeLog("Connection details - Host: $host, Database: $dbname, User: $username");
    
    // For development environment, display detailed error
    if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
        echo "Database connection failed: " . $e->getMessage();
    } else {
        // For production, show a generic error
        echo "A database error occurred. Please try again later.";
    }
    
    // Exit to prevent further execution
    exit;
}
?>