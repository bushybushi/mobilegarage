<?php
// Include the database connection file
require_once 'db_connection.php';

// Check if the search query is set
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prevent SQL injection by using prepared statements
    $stmt = $conn->prepare("
        SELECT c.FirstName, c.LastName, c.Company, p.nr
        FROM customers c
        LEFT JOIN phonenumbers p ON c.CustomerID = p.CustomerID
        WHERE c.FirstName LIKE ? 
        OR c.LastName LIKE ? 
        OR c.Company LIKE ? 
        OR p.nr LIKE ?
    ");

    $searchTerm = "%" . $query . "%"; // Add wildcards for partial matching
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any results are found
    if ($result->num_rows > 0) {
        // Output results
        while ($row = $result->fetch_assoc()) {
            echo "<p><strong>Name:</strong> " . $row['FirstName'] . " " . $row['LastName'] . "";
        }
    } else {
        echo "<p>No results found.</p>";
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
