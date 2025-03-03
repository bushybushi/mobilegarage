<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Details</title>
    <script src="printCustomer.js"></script>
    <link rel="stylesheet" href="print.css">
    <link rel="stylesheet" href="http://localhost/Print_Customer/assets/css/print.css" media="print">
    <script defer src="http://localhost/Print_Customer/assets/js/printCustomer.js"></script>
</head>
<body>
    <div id="customerDetails">
        <h2>Customer Details</h2>
        <div class="customer-info-grid">
            <p><strong>First Name:</strong> <span id="firstName"></span></p>
            <p><strong>Surname:</strong> <span id="lastName"></span></p>
            <p><strong>Company Name:</strong> <span id="companyName"></span></p>
        </div>
        <p><strong>Address:</strong> <span id="address"></span></p>
        <p><strong>Phone Number:</strong> <span id="phone"></span></p>
        <p><strong>Email:</strong> <span id="email"></span></p>

        <h3>Brand Model Registration</h3>

        <!-- New section for displaying cars -->
        <div id="carDetails">
            <!-- Car data will be inserted here dynamically -->
        </div>
    </div>
<script src="printCustomer.js"></script>

    <button id="printButton">Print</button>
</body>
</html>
