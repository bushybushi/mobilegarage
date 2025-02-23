<?php
// Include the database connection
include "../db_connection.php";

// Query to fetch customer data
$query = "SELECT CustomerID, FirstName, LastName, Emails, Nr, Address
            FROM Customers
                    NATURAL JOIN Addresses
                    NATURAL JOIN Emails
                    NATURAL JOIN PhoneNumbers;"; // Adjust to your database table and fields

// Fetch data from the database
$stmt = $pdo->query($query);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="content">
        <h1>Select Customers to Print</h1>
        <p>Select customers from the list and click the button below to print.</p>
        
        <!-- Customer List with checkboxes -->
        <form id="customer-list-form">
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><input type="checkbox" class="customer-checkbox" value="<?php echo htmlspecialchars($customer['CustomerID']); ?>"></td>
                            <td><?php echo htmlspecialchars($customer['FirstName']) . " " . htmlspecialchars($customer['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Emails']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Nr']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        
        <!-- Button to trigger print -->
        <button onclick="openPrintPopup(customersData)">Print Selected Customers</button>
    </div>

    <script>
        // Pass PHP customer data to JavaScript
        const customersData = <?php echo json_encode($customers); ?>;
    </script>

    <script src="print_customer_list.js"></script>
</body>
</html>
