<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Mobile Garage</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Mobile Garage Larnaca</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li class="active"><a href="#">Customers</a></li>
            <li><a href="#">Parts</a></li>
            <li><a href="#">Jobs</a></li>
            <li><a href="#">Accounting</a></li>
            <li><a href="#">Invoice</a></li>
            <li><a href="#">Log Out</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2><?php echo date("l, d M Y") ?></h2>
            <input type="text" placeholder="Search something here">
        </div>

        <div class="content">
            <div class="top-bar">
                <h3>Total: <span id="customerCount">0</span> Customers</h3>
                <div class="actions">
                    <button class="btn">Sort by: Date Created</button>
                    <button class="btn green" onclick="showPrintPopup()">Print</button>
                    <button class="btn blue">Add New Customer</button>
                </div>
            </div>

            
            <!-- Customer Table -->
            <div id="customerTable">
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                    <?php include 'fetch_customers.php'; ?>
                </table>
            </div>

        </div>
    </div>
    <!-- Print Popup -->
    <div id="printPopup" class="popup">
        <div class="popup-content">
            <h3>Print Customers</h3>
            <p>Do you want to print the customer list?</p>
            <button onclick="printTable()">Print</button>
            <button onclick="saveTableAsPDF()">Save as PDF</button>
            <button onclick="closePrintPopup()">Cancel</button>
        </div>
    </div>

    <script>
        // Show the print popup
        function showPrintPopup() {
            document.getElementById("printPopup").style.display = "block";
        }

        // Close the popup
        function closePrintPopup() {
            document.getElementById("printPopup").style.display = "none";
        }

        // Print only the customer table
        function printTable() {
            closePrintPopup();
            var tableContent = document.getElementById("customerTable").innerHTML;
            var newWindow = window.open('', '', 'width=800, height=600');
            newWindow.document.write('<html><head><title>Print Customers</title>');
            newWindow.document.write('<link rel="stylesheet" href="styles.css">'); // Keep styling
            newWindow.document.write('</head><body>');
            newWindow.document.write('<h2>Customer List</h2>');
            newWindow.document.write('<table>' + tableContent + '</table>'); // Only the table
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        }

        // Save the table as PDF (opens print dialog with "Save as PDF" option)
        function saveTableAsPDF() {
            printTable(); // Opens the print dialog where the user can select "Save as PDF"
        }
    </script>

</body>
</html>
