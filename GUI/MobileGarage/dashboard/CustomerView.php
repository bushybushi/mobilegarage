<?php
require_once 'db_connection.php';
$pdo = require 'db_connection.php';

// Fetch data from the database
$customerId = $_GET['id']; // Assuming you pass the customer ID as a query parameter
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, addresses.Address, phonenumbers.nr AS PhoneNumber, emails.Emails AS EmailAddress 
    FROM customers 
    JOIN addresses ON customers.CustomerID = addresses.CustomerID 
    JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
    JOIN emails ON customers.CustomerID = emails.CustomerID
    WHERE customers.CustomerID = :customerId";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <div class="top-container d-flex justify-content-between align-items-center">
               
                    <strong><?php echo ($customer['FirstName']) . ' ' . ($customer['LastName']); ?></strong>  <!-- Display the customer's full name on top -->
          
            <div class="d-flex gap-2">
                <div id="btngroup1">
                    <button href="#" type="button" id="topbtn" class="btn btn-success mb-2 mr-2">Print 
                        <span>
                            <i class="ti ti-printer"></i>
                        </span>
                    </button>
                    <button href="#" type="button" id="topbtn" class="btn btn-primary mb-2">Job Cards 
                        <span>
                            <i class="ti ti-folder"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <form action="Customer.php" method="POST">
            <fieldset disabled>
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="disabledInput" name="firstName" class="form-control" value="<?php echo ($customer['FirstName']); ?>">
                </div>
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname" class="form-control" value="<?php echo ($customer['LastName']); ?>">
                </div>
                <div class="form-group">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo ($customer['Company']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo ($customer['Address']); ?>">
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control" value="<?php echo ($customer['PhoneNumber']); ?>">
                </div>
                <div class="form-group">
                    <label for="emailAddress">Email Address</label>
                    <input type="email" id="emailAddress" name="emailAddress" class="form-control" value="<?php echo ($customer['EmailAddress']); ?>">
                </div>
            </fieldset>
            <div id="btngroup2">
                <button href="#" type="button" id="bottombtn" class="btn btn-primary">Edit
                    <span>
                        <i class="ti ti-plus"></i>
                    </span>
                </button>
                <button href="#" type="button" id="bottombtn" class="btn btn-danger">Delete
                    <span>
                        <i class="ti ti-trash"></i>
                    </span>
                </button>
            </div>
        </form>
    </div>
</body>
</html>