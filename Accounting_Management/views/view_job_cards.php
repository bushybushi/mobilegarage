<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

$startDate = $_GET['startDate'] ?? NULL;
$endDate = $_GET['endDate'] ?? NULL;

// SQL query to fetch all job cards with related information
$sql = "SELECT DISTINCT j.JobID as JobID, j.DateStart as DateStart, j.DateFinish as DateFinish, 
            CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, i.Total as Income,
            (SELECT SUM((jp.PiecesSold * p.PricePerPiece))
             FROM Jobcardparts jp
             LEFT JOIN Parts p ON jp.PartID = p.PartID
             WHERE j.JobID = jp.JobID) as Expenses
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Carassoc ca ON jc.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN Invoicejob ij ON j.JobID = ij.JobID
        LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID";

if ($startDate != NULL && $endDate != NULL) {
    // Transform dates into real dates
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    $sql .= ' WHERE j.DateFinish BETWEEN :startDate AND :endDate';
}

$sql .= " ORDER BY j.DateFinish DESC";

$stmt = $pdo->prepare($sql);
if ($startDate != NULL && $endDate != NULL) {
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
}
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

session_start();

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Calculate total profit
$totalProfit = 0;
foreach ($result as $row) {
    $income = $row['Income'] ?: 0;
    $expenses = $row['Expenses'] ?: 0;
    $totalProfit += ($income - $expenses);
}
?>

<style>
    .popup-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #2196f3;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        color: white;
        font-size: 18px;
        width: 300px;
        z-index: 1000;
        animation: fadeIn 0.5s ease-in-out;
    }

    .popup-content p {
        margin: 0;
        font-weight: bold;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }
    
    /* Table styles */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
        font-weight: 600;
        color: #495057;
    }
    
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: #f1f8ff;
    }
    
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    
    .table td:first-child {
        width: 40px;
        text-align: center;
    }
    
    .table td:first-child i {
        color: #6c757d;
        font-size: 1.2rem;
    }
    
    .badge {
        padding: 6px 10px;
        font-weight: 500;
        border-radius: 4px;
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }

    /* Custom button styles */
    #filterButton {
        background-color: #007bff; /* Bootstrap primary blue */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    #filterButton:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    #printButton {
        background-color: #28a745; /* Bootstrap success green */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    #printButton:hover {
        background-color: #218838; /* Darker green on hover */
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
        }
        .no-print {
            display: none;
        }
    }

    .print-section {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100vh;
        background: white;
        z-index: 9999;
        padding: 20px;
        overflow: auto;
        pointer-events: none;
    }

    @media screen {
        .print-section {
            pointer-events: none;
        }
        .print-section * {
            pointer-events: none;
        }
    }

    #printFrame {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 0;
        height: 0;
        border: none;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Cards Management</title>
    
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/scripts.js" defer></script>
</head>

<body>
    <!-- Add iframe for printing -->
    <iframe id="printFrame"></iframe>

    <div class="pc-container3">
        <div class="form-container">
            <div class="title-container d-flex justify-content-between align-items-center">
                <div>
                    Total: <?php echo count($result); ?> Job Cards
                </div>
                <div class="d-flex">
                    <div class="col-md-4">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo $startDate; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo $endDate; ?>" required>
                    </div>
                    <button type="button" id="filterButton" class="btn">Filter</button>
                    <button type="button" id="printButton" class="btn ml-2">Print</button> <!-- Print button -->
                </div>
            </div>

            <table class="table table-striped" id="jobCardsTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Job Start/End Date</th>
                        <th>Expenses</th>
                        <th>Income</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr onclick="openForm(<?php echo $row['JobID']; ?>)">
                            <td><i class="fas fa-file-alt"></i></td>
                            <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                            <td>
                                <?php 
                                    $rowStartDate = !empty($row['DateStart']) ? date('d/m/Y', strtotime($row['DateStart'])) : 'N/A';
                                    $rowEndDate = !empty($row['DateFinish']) ? date('d/m/Y', strtotime($row['DateFinish'])) : 'N/A';
                                    echo $rowStartDate . ' - ' . $rowEndDate;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['Expenses'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['Income'] ?: 'N/A'); ?></td>
                            <td class="profit" data-profit="<?php echo ($row['Income'] ?: 0) - ($row['Expenses'] ?: 0); ?>">
                                <?php echo number_format(($row['Income'] ?: 0) - ($row['Expenses'] ?: 0), 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-profit">
                Total Profit: <?php echo number_format($totalProfit, 2); ?>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('filterButton').addEventListener('click', function() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;

        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert('Start Date cannot be greater than End Date.');
            return;
        }

        var url = new URL(window.location.href);
        var params = new URLSearchParams(url.search);
        if (startDate) params.set('startDate', startDate);
        if (endDate) params.set('endDate', endDate);

        url.search = params.toString();
        window.location.href = url.toString();
    });

    // Print functionality
    document.getElementById('printButton').addEventListener('click', function() {
        const tableRows = document.querySelectorAll('#jobCardsTable tbody tr');
        let totalProfit = 0;
        
        // Create the print content
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                    }
                    .header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 30px;
                    }
                    .logo {
                        max-height: 80px;
                    }
                    .title {
                        text-align: right;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f8f9fa;
                    }
                    .total-profit {
                        text-align: right;
                        font-weight: bold;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <img src="../assets/logo.png" alt="Logo" style="max-height: 80px;">
                    </div>
                    <div class="title">
                        <h2>Job Cards</h2>
                        <p>Total Job Cards: ${tableRows.length}</p>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Job Start/End Date</th>
                            <th>Expenses</th>
                            <th>Income</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>`;

        // Add table rows
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            printContent += '<tr>';
            // Skip the first cell (icon) and add the rest
            for (let i = 1; i < cells.length; i++) {
                printContent += `<td>${cells[i].textContent}</td>`;
            }
            printContent += '</tr>';
            
            // Add to total profit
            const profit = parseFloat(cells[5].getAttribute('data-profit')) || 0;
            totalProfit += profit;
        });

        // Complete the HTML content
        printContent += `
                    </tbody>
                </table>
                <div class="total-profit">
                    Total Profit: ${totalProfit.toFixed(2)}
                </div>
            </body>
            </html>`;

        // Get the iframe
        const frame = document.getElementById('printFrame');
        
        // Write the content to the iframe
        frame.contentWindow.document.open();
        frame.contentWindow.document.write(printContent);
        frame.contentWindow.document.close();
        
        // Wait for images to load then print
        frame.contentWindow.onload = function() {
            frame.contentWindow.print();
        };
    });

    setTimeout(function() {
        let popup = document.getElementById("customPopup");
        if (popup) {
            popup.style.animation = "fadeOut 0.5s ease-in-out";
            setTimeout(() => popup.remove(), 500);
        }
    }, 3000);

    function openForm(jobId) {
        window.location.href = '../../JobCard_Management/views/job_card_view.php?id=' + jobId;
    }
    </script>
</body>
</html>