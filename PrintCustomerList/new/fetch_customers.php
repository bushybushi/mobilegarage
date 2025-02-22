<?php
require_once 'db_connection.php';

$sql = "SELECT FirstName, LastName, Emails, Nr, Address
        FROM Customers
            NATURAL JOIN Emails
            NATURAL JOIN Addresses
            NATURAL JOIN PhoneNumbers;";

try {
    $stmt = $pdo->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($customers) {
        foreach ($customers as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["FirstName"]) . " " . htmlspecialchars($row["LastName"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Emails"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Nr"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Address"]) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No customers found</td></tr>";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
}
?>
