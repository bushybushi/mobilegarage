<?php
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Get the customer ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Query to fetch customer's basic information
$customerSql = 'SELECT * from customers where CustomerID = ?';
$customerStmt = $pdo->prepare($customerSql);
$customerStmt->execute([$id]);

// Store the customer data in a variable
$old_customer = $customerStmt->fetch();

// Query to fetch all addresses associated with the customer
$addressSql = 'select Address from Addresses where CustomerID = ?';
$addressStmt = $pdo->prepare($addressSql);
$addressStmt->execute([$id]);

// Store all addresses in an array
$old_address = $addressStmt->fetchAll();

// Query to fetch all phone numbers associated with the customer
$phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
$phoneStmt = $pdo->prepare($phoneSql);
$phoneStmt->execute([$id]);

// Store all phone numbers in an array
$old_phone = $phoneStmt->fetchAll();

// Query to fetch all email addresses associated with the customer
$emailSql = 'SELECT Emails from Emails where CustomerID = ?';
$emailStmt = $pdo->prepare($emailSql);
$emailStmt->execute([$id]);

// Store all email addresses in an array
$old_email = $emailStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    
    <!-- CSS and JavaScript dependencies -->
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
            <a href="javascript:void(0);" onclick="window.location.href='customer_view.php?id=<?php echo $id; ?>'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Customer Name Display -->
            <div class="flex-grow-1 text-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($old_customer['FirstName']) . ' ' . htmlspecialchars($old_customer['LastName']); ?></h5>
                </a>
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

    <!-- Customer Edit Form -->
    <form action="../controllers/update_customer_controller.php" method="post">
        <!-- First Name Input Field -->
        <div class="form-group">
            <label for="firstName">First Name</label>
            <!-- Hidden input for customer ID -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" name="firstName" class="form-control" value="<?php echo htmlspecialchars($old_customer['FirstName'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Last Name Input Field -->
        <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" name="surname" class="form-control" value="<?php echo htmlspecialchars($old_customer['LastName'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Company Name Input Field -->
        <div class="form-group">
            <label for="companyName">Company Name</label>
            <input type="text" name="companyName" class="form-control" value="<?php echo htmlspecialchars($old_customer['Company'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <?php 
        // Address Fields Section
        echo '<div id="addresses">';
        if (!empty($old_address)) {
            // Display existing addresses with plus/minus buttons
            foreach ($old_address as $row)
            echo '<div class="form-group">
                <label for="address">Address</label>
                <div class="input-group">
                    <input type="text" name="address[]" class="form-control" value="' . htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8') . '" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';
        } else {
            // Display empty address field if no addresses exist
            echo '<div class="form-group">
                <label for="address">Address</label>
                <div class="input-group">
                    <input type="text" name="address[]" class="form-control" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';    
        }
        echo '</div>';
        
        // Phone Numbers Section
        echo '<div id="phoneNumbers">';
        if (!empty($old_phone)) {
            // Display existing phone numbers with plus/minus buttons
            foreach ($old_phone as $row)
            echo '<div class="form-group">
                <label for="phoneNumber">Phone Number</label>
                <div class="input-group">
                    <input type="tel" name="phoneNumber[]" class="form-control" value="' . htmlspecialchars($row['Nr'], ENT_QUOTES, 'UTF-8') . '" required style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';
        } else {
            // Display empty phone field if no phone numbers exist
            echo '<div class="form-group">
                <label for="phoneNumber">Phone Number</label>
                <div class="input-group">
                    <input type="tel" name="phoneNumber[]" class="form-control" required style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';    
        }
        echo '</div>';
        
        // Email Addresses Section
        echo '<div id="emailAddresses">';
        if (!empty($old_email)) {
            // Display existing email addresses with plus/minus buttons
            foreach ($old_email as $row)
            echo '<div class="form-group">
                <label for="emailAddress">Email Address</label>
                <div class="input-group">
                    <input type="email" name="emailAddress[]" class="form-control" value="' . htmlspecialchars($row['Emails'], ENT_QUOTES, 'UTF-8') . '" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';
        } else {
            // Display empty email field if no email addresses exist
            echo '<div class="form-group">
                <label for="emailAddress">Email Address</label>
                <div class="input-group">
                    <input type="email" name="emailAddress[]" class="form-control" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>';    
        }
        echo '</div>';
        ?>
        
        <!-- Submit Button -->
       <div class="btngroup">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

<!-- JavaScript Functions for Dynamic Form Fields -->
<script>
    // Function to add a new address field
    function addAddressField() {
        const container = document.getElementById('addresses');
        const newField = document.createElement('div');
        newField.className = 'form-group';
        newField.innerHTML = `
            <label for="address[]">Address</label>
            <div class="input-group">
                <input type="text" name="address[]" class="form-control" style="padding-right: 80px;">
                <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                    <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newField);
    }

    // Function to add a new phone number field
    function addPhoneNumberField() {
        const container = document.getElementById('phoneNumbers');
        const newField = document.createElement('div');
        newField.className = 'form-group';
        newField.innerHTML = `
            <label for="phoneNumber[]">Phone Number</label>
            <div class="input-group">
                <input type="tel" name="phoneNumber[]" class="form-control" required style="padding-right: 80px;">
                <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                    <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newField);
    }

    // Function to add a new email address field
    function addEmailAddressField() {
        const container = document.getElementById('emailAddresses');
        const newField = document.createElement('div');
        newField.className = 'form-group';
        newField.innerHTML = `
            <label for="emailAddress[]">Email Address</label>
            <div class="input-group">
                <input type="email" name="emailAddress[]" class="form-control" style="padding-right: 80px;">
                <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                    <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newField);
    }

    // Function to remove a field (address, phone, or email)
    function removeField(button) {
        button.closest('.form-group').remove();
    }
</script>
</body>
</html>