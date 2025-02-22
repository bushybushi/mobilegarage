<?php
$pdo = require 'db_connection.php';

$sql = "SELECT FirstName, LastName, Emails, Nr, Address 
        FROM Customers 
        NATURAL JOIN Addresses 
        NATURAL JOIN PhoneNumbers 
        NATURAL JOIN Emails;";

try {
    $stmt = $pdo->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($customers) > 0) {
        foreach ($customers as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["FirstName"]) . " " . htmlspecialchars($row["LastName"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Nr"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Address"]) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No customers found</td></tr>";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='4'>Error retrieving data: " . $e->getMessage() . "</td></tr>";
}
?>
