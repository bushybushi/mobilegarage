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

        #search-input {
            width: 50%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
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

        // If any individual checkbox is unchecked, uncheck the "Select All" checkbox
        document.querySelectorAll(".customer-checkbox").forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                if (!this.checked) {
                    selectedCustomers.delete(this.value);  // Remove from selected customers set
                    document.getElementById("select-all").checked = false;
                } else {
                    selectedCustomers.add(this.value);  // Add to selected customers set
                    const allChecked = [...document.querySelectorAll(".customer-checkbox")]
                        .filter(checkbox => checkbox.closest("tr").style.display !== "none") // Only consider visible checkboxes
                        .every(checkbox => selectedCustomers.has(checkbox.value));
                    document.getElementById("select-all").checked = allChecked;
                }
            });
        });

        // Search Functionality
        document.getElementById("search-input").addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                const searchText = this.value.toLowerCase();
                const rows = document.querySelectorAll("#customer-table-body tr");

                rows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    const isVisible = rowText.includes(searchText);
                    row.style.display = isVisible ? "table-row" : "none";

                    const checkbox = row.querySelector(".customer-checkbox");
                    if (!isVisible) {
                        checkbox.checked = false;  // Uncheck hidden rows when search filters them out
                    } else {
                        // Reapply selection for visible rows based on the selectedCustomers set
                        checkbox.checked = selectedCustomers.has(checkbox.value);
                    }
                });

                // Update "Select All" checkbox state based on visible checkboxes
                const visibleCheckboxes = [...document.querySelectorAll(".customer-checkbox")]
                    .filter(checkbox => checkbox.closest("tr").style.display !== "none");
                const allVisibleChecked = visibleCheckboxes.every(checkbox => selectedCustomers.has(checkbox.value));
                document.getElementById("select-all").checked = allVisibleChecked;
            }
        });

    </script>

    <script src="print_customer_list.js"></script>
</body>
</html>
