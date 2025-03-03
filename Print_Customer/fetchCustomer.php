<?php
// Database Connection Configuration
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP password 
$dbname = "mobilegarage"; // database name

try {
    // Create a new PDO database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error mode
} catch (PDOException $e) {
    die(json_encode(["error" => "Database connection failed: " . $e->getMessage()]));
}

$customer_id = 1; // Get customer ID (default to 1 for testing)

// Fetch Customer Basic Info
$sql = "SELECT CustomerID AS id, FirstName AS first_name, LastName AS last_name, Company AS company_name 
        FROM customers WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$customer_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer_data) { // Check if customer exists
    echo json_encode(["error" => "No customer found"]);
    exit();
}

$customer_data["addresses"] = [];
$customer_data["phones"] = []; // Initialize empty arrays to prevent errors
$customer_data["emails"] = [];
$customer_data["cars"] = [];

// Get Addresses
fetchData($conn, "SELECT Address FROM addresses WHERE CustomerID = ?", $customer_id, $customer_data["addresses"], "Address");

// Get Phone Numbers
fetchData($conn, "SELECT Nr FROM phonenumbers WHERE CustomerID = ?", $customer_id, $customer_data["phones"], "Nr");

// Get Emails
fetchData($conn, "SELECT Emails FROM emails WHERE CustomerID = ?", $customer_id, $customer_data["emails"], "Emails");

// Get Cars 
$sql_cars = "SELECT c.LicenseNr, c.Brand, c.Model, c.VIN, c.ManuDate, c.Fuel, c.KWHorse, c.Engine, c.KMMiles, c.Color, c.Comments 
             FROM cars c
             INNER JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr
             WHERE ca.CustomerID = ?";
$stmt_cars = $conn->prepare($sql_cars);
$stmt_cars->execute([$customer_id]);
$customer_data["cars"] = $stmt_cars->fetchAll(PDO::FETCH_ASSOC);

if (!isset($customer_data["cars"])) { // Ensure cars array exists even if empty
    $customer_data["cars"] = [];
}

header('Content-Type: application/json');
echo json_encode($customer_data, JSON_PRETTY_PRINT);

// Close database connection (PDO automatically closes when script ends)

function fetchData($conn, $query, $customer_id, &$targetArray, $columnName) {
    $stmt = $conn->prepare($query);
    $stmt->execute([$customer_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $targetArray[] = $row[$columnName];
    }
}
