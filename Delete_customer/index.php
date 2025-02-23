<?php
// list_customers.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Query to fetch customer details (assuming one email, phone, and address per customer)
$sql = "SELECT c.CustomerID, c.FirstName, c.LastName, e.Emails AS email, p.Nr AS phone, a.Address
        FROM customers c
        LEFT JOIN emails e ON c.CustomerID = e.CustomerID
        LEFT JOIN phonenumbers p ON c.CustomerID = p.CustomerID
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer List</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <h1 class="title">Customer List</h1>
    <?php if(count($customers) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $customer): ?>
                    <tr>
                        <td>
                            <a href="edit_customer.php?customerID=<?php echo $customer['CustomerID']; ?>">
                                <?php echo htmlspecialchars($customer['FirstName'] . " " . $customer['LastName']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['Address']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-customers">No customers found.</p>
    <?php endif; ?>
</body>
</html>
