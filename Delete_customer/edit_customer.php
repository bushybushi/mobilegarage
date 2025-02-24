<?php
// edit_customer.php

// Include the database connection file
$pdo = require 'db_connection.php';

// Check for the customerID in the URL
if (!isset($_GET['customerID'])) {
    die("No customer specified.");
}

$customerID = intval($_GET['customerID']);

// Retrieve customer details
$sql = "SELECT c.CustomerID, c.FirstName, c.LastName, c.Company,
               a.Address, p.Nr AS phone, e.Emails AS email
        FROM customers c
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID
        LEFT JOIN phonenumbers p ON c.CustomerID = p.CustomerID
        LEFT JOIN emails e ON c.CustomerID = e.CustomerID
        WHERE c.CustomerID = :customerID";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="edit_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        function deleteCustomer() {
            let customerID = document.getElementById('customerID').value;
            
            if (!confirm("Are you sure you want to delete this customer?")) return;

            fetch("delete_customer.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "customerID=" + customerID
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("form-container").style.display = "none";
                    document.getElementById("success-message").style.display = "block";
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</head>
<body>

<div class="container">
    <!-- Customer Header -->
    <div class="customer-header">
        <h2>Customer</h2>
    </div>

    <!-- Customer Info Form -->
    <div id="form-container">
        <form onsubmit="event.preventDefault(); deleteCustomer();">
            <input type="hidden" id="customerID" name="customerID" value="<?php echo $customer['CustomerID']; ?>">

            <div class="form-group">
                <label>First Name</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['FirstName']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Surname</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['LastName']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['Company']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['Address']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="text" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
            </div>
   <!-- Delete Button/Important -->
            <button type="submit" class="delete-button">
                Delete <i class="fas fa-trash-alt"></i>
            </button>
        </form>
    </div>

    <!-- Success Message -->
    <div id="success-message" class="success-message">
        <p>Customer Deleted Successfully!</p>
        <button onclick="window.location.href='index.php'">Return to Dashboard</button>
    </div>
</div>

</body>
</html>
