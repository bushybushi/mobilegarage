<?php
// Include database connection file
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

// Get customer ID from URL parameter
$customerId = $_GET['id'];

// SQL query to fetch customer details with related information
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
        addresses.Address, phonenumbers.nr AS PhoneNumber, emails.Emails AS EmailAddress 
        FROM customers 
        JOIN addresses ON customers.CustomerID = addresses.CustomerID 
        JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
        JOIN emails ON customers.CustomerID = emails.CustomerID
        WHERE customers.CustomerID = :customerId";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the customer data
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer View</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <!-- Main Content Container -->
    <div class="form-container">
        <!-- Top Navigation Bar with Customer Name and Action Buttons -->
        <div class="top-container d-flex justify-content-between align-items-center">
            <!-- Back Arrow Button -->
            <a href="javascript:void(0);" onclick="window.location.href='customer_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Customer Name Display -->
            <div class="flex-grow-1 text-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($customer['FirstName']) . ' ' . htmlspecialchars($customer['LastName']); ?></h5>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end">
                <div class="btngroup">
                    <!-- Print Button -->
                    <button href="#" type="button" class="btn btn-success mr-2">Print </button>
                    <!-- Job Cards Button -->
                    <button href="#" type="button" class="btn btn-primary">Job Cards </button>
                </div>
            </div>
        </div>

        <!-- Customer View Form -->
        <div class="form-content">
            <!-- Disable form fields for view-only mode -->
            <fieldset disabled>
                <!-- First Name Field -->
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="disabledInput" name="firstName" class="form-control" value="<?php echo ($customer['FirstName']); ?>">
                </div>

                <!-- Last Name Field -->
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname" class="form-control" value="<?php echo ($customer['LastName']); ?>">
                </div>

                <!-- Company Name Field -->
                <div class="form-group">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo ($customer['Company']); ?>">
                </div>

                <!-- Address Field -->
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo ($customer['Address']); ?>">
                </div>

                <!-- Phone Number Field -->
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control" value="<?php echo ($customer['PhoneNumber']); ?>">
                </div>

                <!-- Email Address Field -->
                <div class="form-group">
                    <label for="emailAddress">Email Address</label>
                    <input type="email" id="emailAddress" name="emailAddress" class="form-control" value="<?php echo ($customer['EmailAddress']); ?>">
                </div>
            </fieldset>

            <!-- Action Buttons -->
            <div class="btngroup">
                <!-- Edit Button - Links to edit_customer.php -->
                <a href="edit_customer.php?id=<?php echo $customerId; ?>" class="btn btn-primary">Edit</a>
                <!-- Delete Button - Form with POST method -->
                <form action="../controllers/delete_customer_controller.php" method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $customerId; ?>">
                    <input type="hidden" name="firstName" value="<?php echo htmlspecialchars($customer['FirstName']); ?>">
                    <input type="hidden" name="surname" value="<?php echo htmlspecialchars($customer['LastName']); ?>">
                    <input type="hidden" name="companyName" value="<?php echo htmlspecialchars($customer['Company']); ?>">
                    <input type="hidden" name="address[]" value="<?php echo htmlspecialchars($customer['Address']); ?>">
                    <input type="hidden" name="phoneNumber[]" value="<?php echo htmlspecialchars($customer['PhoneNumber']); ?>">
                    <input type="hidden" name="emailAddress[]" value="<?php echo htmlspecialchars($customer['EmailAddress']); ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>