<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

// SQL query to fetch all job cards with related information
$sql = "SELECT j.JobID, j.Location, j.DateCall, j.JobDesc, j.DateStart, j.DateFinish, 
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, car.LicenseNr
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN CarAssoc ca ON jc.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        ORDER BY j.DateCall DESC";

$stmt = $pdo->prepare($sql);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Cards Management</title>
    
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
</style>

<body>
    <div class="pc-container3">
        <div class="form-container">
            <div class="title-container d-flex justify-content-between align-items-center">
                <div>
                    Total: <?php echo count($result); ?> Job Cards
                </div>
                <div class="d-flex">
                    <div class="dropdown mr-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">Date</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Customer')">Customer</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Date')">Date</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Status')">Status</a></li>
                        </ul>
                    </div>

                    <button href="#" type="button" class="btn btn-success mr-3">Print 
                        <span>
                            <i class="ti ti-printer"></i>
                        </span>
                    </button>
                    <button href="#" id="addnewjobcard-link" type="button" class="btn btn-primary">Add New Job Card 
                        <span>
                            <i class="ti ti-plus"></i>
                        </span>
                    </button>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Date Called</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr onclick="openForm('<?php echo $row['JobID']; ?>')">
                            <td><?php echo htmlspecialchars($row['JobID']); ?></td>
                            <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                            <td><?php echo htmlspecialchars($row['LicenseNr']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCall']); ?></td>
                            <td><?php echo htmlspecialchars($row['Location']); ?></td>
                            <td><?php 
                                if (!empty($row['DateFinish'])) {
                                    echo '<span class="badge badge-success">Completed</span>';
                                } elseif (!empty($row['DateStart'])) {
                                    echo '<span class="badge badge-warning">In Progress</span>';
                                } else {
                                    echo '<span class="badge badge-secondary">Pending</span>';
                                }
                            ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function updateSort(sortBy) {
            document.getElementById('selectedSort').textContent = sortBy;
            
            const tbody = document.querySelector('table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                let aValue, bValue;
                
                switch(sortBy) {
                    case 'Customer':
                        aValue = a.cells[1].textContent.trim();
                        bValue = b.cells[1].textContent.trim();
                        break;
                    case 'Date':
                        aValue = new Date(a.cells[3].textContent);
                        bValue = new Date(b.cells[3].textContent);
                        return bValue - aValue; // Most recent first
                    case 'Status':
                        aValue = a.cells[5].textContent.trim();
                        bValue = b.cells[5].textContent.trim();
                        break;
                }
                
                return aValue.localeCompare(bValue);
            });
            
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        $(document).ready(function() {
            updateSort('Date');
        });

        setTimeout(function() {
            let popup = document.getElementById("customPopup");
            if (popup) {
                popup.style.animation = "fadeOut 0.5s ease-in-out";
                setTimeout(() => popup.remove(), 500);
            }
        }, 3000);

        function openForm(jobId) {
            window.location.href = 'job_card_view.php?id=' + jobId;
        }

        $(document).ready(function() {
            $('#addnewjobcard-link').on('click', function(e) {
                e.preventDefault();
                window.location.href = 'job_cards.php';
            });
        });
    </script>
</body>
</html> 