<?php
// Include the database connection
include "../db_connection.php";

$customers = []; // Prevents undefined variable error

try {
    // Query to fetch customer data
    $query = "SELECT CustomerID, FirstName, LastName, Emails, Nr, Address
                FROM Customers
                NATURAL JOIN Addresses
                NATURAL JOIN Emails
                NATURAL JOIN PhoneNumbers;";

    // Fetch data from the database
    $stmt = $pdo->query($query);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error fetching customers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: auto;
        }

         /* Search Bar Styling */
         .search-container {
            display: block;
            justify-content: center;
            margin-bottom: 15px;
            top: 20px;
            width: 100%;
            background: #fff;
            padding: 10px 0;
            z-index: 10;
        }

        #search-input {
            width: 50%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }

        .filter-buttons button {
            padding: 8px 15px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .filter-buttons button:hover {
            background-color: #0056b3;
        }

        #customer-list-form {
            max-width: 100%;
        }

        .table-container {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            padding: 15px;
        }

        .table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
        }

        tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 10px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .select-all-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .print-btn {
            display: block;
            width: 25%;
            max-width: 150px;
            max-height: 50px;
            margin: 15px auto;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
        }

        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Search Bar -->
    <div class="search-container">
        <input type="text" id="search-input" placeholder="Search by Name, Email, Phone, or Address...">
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <button id="filter-name">Name</button>
        <button id="filter-email">Email</button>
        <button id="filter-phone">Phone</button>
        <button id="filter-address">Address</button>
    </div>

        <div class="container">
        <h1 style="text-align: center;">Select Customers to Print</h1>
        

        <div class="table-container">
            <!-- Select All Checkbox -->
            <div class="select-all-container">
                <input type="checkbox" id="select-all">
                <label for="select-all">Select All</label>
            </div>

            <form id="customer-list-form">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">Select</th>
                            <th style="width: 25%;">Name</th>
                            <th style="width: 25%;">Email</th>
                            <th style="width: 20%;">Phone</th>
                            <th style="width: 25%;">Address</th>
                        </tr>
                    </thead>
                </table>
                <div class="table-wrapper">
                    <table>
                        <tbody id="customer-table-body">
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td style="width: 5%;"><input type="checkbox" class="customer-checkbox" value="<?php echo htmlspecialchars($customer['CustomerID']); ?>"></td>
                                        <td style="width: 25%;"><?php echo htmlspecialchars($customer['FirstName']) . " " . htmlspecialchars($customer['LastName']); ?></td>
                                        <td style="width: 25%;"><?php echo htmlspecialchars($customer['Emails']); ?></td>
                                        <td style="width: 20%;"><?php echo htmlspecialchars($customer['Nr']); ?></td>
                                        <td style="width: 25%;"><?php echo htmlspecialchars($customer['Address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center;">No customers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        
    </div>

    <!-- Button to trigger print -->
    <button class="print-btn" onclick="openPrintPopup(customersData)">Print Selected Customers</button>


    <script>
        // Pass PHP customer data to JavaScript
        const customersData = <?php echo json_encode($customers); ?>;

        // Create a set to store selected customer IDs
        let selectedCustomers = new Set();
        let filteredCustomers = customersData;

        // Select All Checkbox Functionality
        document.getElementById("select-all").addEventListener("change", function() {
            const checkboxes = document.querySelectorAll(".customer-checkbox");
            const visibleCheckboxes = [...checkboxes].filter(checkbox => checkbox.closest("tr").style.display !== "none");
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedCustomers.add(checkbox.value);  // Add to selected customers set
                } else {
                    selectedCustomers.delete(checkbox.value);  // Remove from selected customers set
                }
            });
        });

        // Search Functionality
        document.getElementById("search-input").addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                const searchText = this.value.toLowerCase();
                filteredCustomers = customersData.filter(customer => 
                    customer.FirstName.toLowerCase().includes(searchText) || 
                    customer.LastName.toLowerCase().includes(searchText) || 
                    customer.Emails.toLowerCase().includes(searchText) || 
                    customer.Nr.toLowerCase().includes(searchText) || 
                    customer.Address.toLowerCase().includes(searchText)
                );
                renderTable(filteredCustomers);
            }
        });

        // Filter functionality for specific attributes
        document.getElementById("filter-name").addEventListener("click", function() {
            const searchText = document.getElementById("search-input").value.toLowerCase();
            filteredCustomers = customersData.filter(customer => 
                (customer.FirstName.toLowerCase().includes(searchText) || 
                customer.LastName.toLowerCase().includes(searchText))
            );
            renderTable(filteredCustomers);
        });

        document.getElementById("filter-email").addEventListener("click", function() {
            const searchText = document.getElementById("search-input").value.toLowerCase();
            filteredCustomers = customersData.filter(customer => 
                customer.Emails.toLowerCase().includes(searchText)
            );
            renderTable(filteredCustomers);
        });

        document.getElementById("filter-phone").addEventListener("click", function() {
            const searchText = document.getElementById("search-input").value.toLowerCase();
            filteredCustomers = customersData.filter(customer => 
                customer.Nr.toLowerCase().includes(searchText)
            );
            renderTable(filteredCustomers);
        });

        document.getElementById("filter-address").addEventListener("click", function() {
            const searchText = document.getElementById("search-input").value.toLowerCase();
            filteredCustomers = customersData.filter(customer => 
                customer.Address.toLowerCase().includes(searchText)
            );
            renderTable(filteredCustomers);
        });

        // Function to render the table based on filtered customers
        function renderTable(customers) {
            const tableBody = document.getElementById("customer-table-body");
            tableBody.innerHTML = '';
            customers.forEach(customer => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td style="width: 5%;"><input type="checkbox" class="customer-checkbox" value="${customer.CustomerID}"></td>
                    <td style="width: 25%;">${customer.FirstName} ${customer.LastName}</td>
                    <td style="width: 25%;">${customer.Emails}</td>
                    <td style="width: 20%;">${customer.Nr}</td>
                    <td style="width: 25%;">${customer.Address}</td>
                `;
                tableBody.appendChild(row);
            });

            // Reapply selected checkboxes after filtering
            document.querySelectorAll(".customer-checkbox").forEach(checkbox => {
                if (selectedCustomers.has(checkbox.value)) {
                    checkbox.checked = true;
                }
            });
        }

        // Initialize the table
        renderTable(filteredCustomers);

    </script>

    <script src="print_customer_list.js"></script>
</body>
</html>
