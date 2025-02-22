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
    <script>
        // Collect selected customer IDs
        function getSelectedCustomers() {
            const selectedCustomers = [];
            const checkboxes = document.querySelectorAll('.customer-checkbox:checked');
            checkboxes.forEach((checkbox) => {
                selectedCustomers.push(checkbox.value);
            });
            return selectedCustomers;
        }

        // Function to generate the customer table for printing
        function generatePrintTable(customers) {
            let tableHTML = `<table id="print-customer-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                    </tr>
                                </thead>
                                <tbody>`;
            
            customers.forEach((customer) => {
                tableHTML += `<tr>
                                <td>${customer.firstName} ${customer.lastName}</td>
                                <td>${customer.email}</td>
                                <td>${customer.phone}</td>
                                <td>${customer.address}</td>
                              </tr>`;
            });
            
            tableHTML += `</tbody></table>`;
            
            return tableHTML;
        }

        // Show the pop-up for printing based on the selected customers
        function openPrintPopup() {
            const selectedCustomers = getSelectedCustomers();
            
            if (selectedCustomers.length === 0) {
                alert("No customers selected.");
                return;
            }

            const userChoice = confirm("Do you want to print the information of selected customers?");
            
            if (userChoice) {
                // Create a list of selected customer data to pass to the print function
                const selectedCustomerDetails = selectedCustomers.map((id) => {
                    const customer = <?php echo json_encode($customers); ?>.find(cust => cust.CustomerID == id);
                    return {
                        firstName: customer.FirstName,
                        lastName: customer.LastName,
                        email: customer.Emails,
                        phone: customer.Nr,
                        address: customer.Address
                    };
                });

                // Generate the HTML table for the selected customers
                const printTableHTML = generatePrintTable(selectedCustomerDetails);
                
                // Create a hidden iframe and add the table to it
                const iframe = document.createElement('iframe');
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                document.body.appendChild(iframe);
                
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write('<html><head><style>table,th,td{border-collapse:collapse;border:1px solid;width:100%;text-align:left;padding:5px;}</style>');
                iframeDoc.write('<title>Print Customer Information</title></head><body>');
                iframeDoc.write(printTableHTML);
                iframeDoc.write('</body></html>');
                iframeDoc.close();
                
                // Trigger the print dialog for the iframe content
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                
                // Remove the iframe after printing
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 1000);  // Delay to allow print dialog to show before removing iframe
            } else {
                alert("Printing is canceled.");
            }
        }
    </script>
</head>
<body>
    <div class="content">

        <!-- Replace when view Customers is done -->
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
        <button onclick="openPrintPopup()">Print Selected Customers</button>
    </div>
</body>
</html>
