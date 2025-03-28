<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

try {
    // SQL query to fetch all job cards with related information
    $sql = "SELECT j.JobID as JobID, j.DateStart as DateStart, j.DateFinish as DateFinish, 
                CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, 
                i.Total as Income,
                (SELECT SUM(jp.PiecesSold * p.PricePerPiece) 
                 FROM JobCardParts jp 
                 LEFT JOIN Parts p ON jp.PartID = p.PartID 
                 WHERE j.JobID = jp.JobID) as Expenses
            FROM JobCards j 
                LEFT JOIN JobCar jc ON j.JobID = jc.JobID
                LEFT JOIN CarAssoc ca ON jc.LicenseNr = ca.LicenseNr
                LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
                LEFT JOIN InvoiceJob ij ON j.JobID = ij.JobID
                LEFT JOIN Invoices i ON ij.InvoiceID = i.InvoiceID
            ORDER BY j.DateCall DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching job cards: " . $e->getMessage());
}

session_start();

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'><p>" . $_SESSION['message'] . "</p></div>";
    echo "</div>";

    unset($_SESSION['message']);
}

// Calculate total profit
$totalProfit = 0;
foreach ($result as $row) {
    $income = $row['Income'] ?: 0;
    $expenses = $row['Expenses'] ?: 0;
    $totalProfit += ($income - $expenses);
}
?>

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
    <script>
        function openForm(jobId) {
            window.location.href = '../../JobCard_Management/views/job_card_view.php?id=' + jobId;
        }
    </script>
</head>

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

    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }

    .table tbody tr:hover {
        background-color: #f1f8ff;
    }

    .total-profit {
        font-weight: bold;
        font-size: 18px;
        margin-top: 20px;
    }
</style>

<body>
    <div class="pc-container3">
        <div class="form-container">
            <div class="title-container d-flex justify-content-between align-items-center">
                <div>Total: <?php echo count($result); ?> Job Cards</div>
                <div class="d-flex">
                    <!-- Sort By Dropdown -->
                    <div class="dropdown mr-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortByDropdown" data-toggle="dropdown">
                            Sort By: <span id="selectedSort">Date</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="sortTable('date')">Date</a></li>
                            <li><a class="dropdown-item" href="#" onclick="sortTable('year')">Year</a></li>
                            <li><a class="dropdown-item" href="#" onclick="sortTable('profit')">Profit</a></li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-success mr-3">Print 
                        <span><i class="ti ti-printer"></i></span>
                    </button>
                </div>
            </div>

            <table class="table table-striped" id="jobCardsTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Job Start/End date</th>
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
                            <td class="job-date" data-date="<?php echo strtotime($row['DateStart']); ?>">
                                <?php 
                                    $startDate = !empty($row['DateStart']) ? date('d/m/Y', strtotime($row['DateStart'])) : 'N/A';
                                    $endDate = !empty($row['DateFinish']) ? date('d/m/Y', strtotime($row['DateFinish'])) : 'N/A';
                                    echo $startDate . ' - ' . $endDate;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['Expenses'] ?: '0.00'); ?></td>
                            <td><?php echo htmlspecialchars($row['Income'] ?: '0.00'); ?></td>
                            <td class="profit" data-profit="<?php echo ($row['Income'] ?: 0) - ($row['Expenses'] ?: 0); ?>">
                                <?php echo number_format(($row['Income'] ?: 0) - ($row['Expenses'] ?: 0), 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total Profit -->
            <div class="total-profit">
                Total Profit: <?php echo number_format($totalProfit, 2); ?>
            </div>
        </div>
    </div>

    <script>
        function sortTable(criteria) {
            let table = document.getElementById("jobCardsTable").getElementsByTagName("tbody")[0];
            let rows = Array.from(table.rows);
            
            if (criteria === "profit") {
                rows.sort((a, b) => {
                    let profitA = parseFloat(a.querySelector(".profit").dataset.profit);
                    let profitB = parseFloat(b.querySelector(".profit").dataset.profit);
                    return profitB - profitA;
                });
                document.getElementById("selectedSort").textContent = "Profit";
            } 
            else if (criteria === "year") {
                rows.sort((a, b) => {
                    let dateA = new Date(a.querySelector(".job-date").dataset.date * 1000).getFullYear();
                    let dateB = new Date(b.querySelector(".job-date").dataset.date * 1000).getFullYear();
                    return dateB - dateA;
                });
                document.getElementById("selectedSort").textContent = "Year";
            } 
            else {
                rows.sort((a, b) => a.querySelector(".job-date").dataset.date - b.querySelector(".job-date").dataset.date);
                document.getElementById("selectedSort").textContent = "Date";
            }

            table.innerHTML = "";
            rows.forEach(row => table.appendChild(row));
        }
    </script>
</body>
</html>
