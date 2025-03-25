<?php
// PartsManagementMain.php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';
require_once '../models/parts_model.php';

// Fetch all parts with supplier information
$sql = "SELECT p.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail,
               ps.PiecesPurch, ps.PricePerPiece
        FROM Parts p
        LEFT JOIN PartsSupply ps ON p.PartID = ps.PartID
        LEFT JOIN Suppliers s ON ps.SupplierID = s.SupplierID
        ORDER BY p.PartID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start session for handling messages
session_start();

// Display session message if exists
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

    // Clear session message after displaying
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Management</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<!-- Custom CSS for popup styling -->
<style>
    /* Popup container styling */
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

    /* Popup content styling */
    .popup-content p {
        margin: 0;
        font-weight: bold;
    }

    /* Fade in animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    /* Fade out animation */
    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }
</style>

<body>
    <!-- Main Content Container -->
    <div class="pc-container3">
        <div class="form-container">
            <!-- Title Bar with Parts Count and Action Buttons -->
            <div class="title-container d-flex justify-content-between align-items-center">
                <!-- Parts Count Display -->
                <div>
                    Total: <?php echo count($result); ?> Parts
                </div>
                <!-- Action Buttons -->
                <div class="d-flex">
                    <!-- Sort Dropdown -->
                    <div class="dropdown mr-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Sort by: <span id="selectedSort">Date Created</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Name')">Name</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateSort('Date Created')">Date Created</a></li>
                        </ul>
                    </div>

                    <!-- Print Button -->
                    <button href="#" type="button" class="btn btn-success mr-3">Print 
                        <span>
                            <i class="ti ti-printer"></i>
                        </span>
                    </button>

                    <!-- Add New Part Button -->
                    <button href="#" id="addnewpart-link" type="button" class="btn btn-primary">Add New Part 
                        <span>
                            <i class="ti ti-plus"></i>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Parts Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <!-- Table Header -->
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Supplier</th>
                            <th>Sell Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <!-- Table Body -->
                    <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr onclick="openForm('<?php echo $row['PartID']; ?>')">
                                <td><?php echo htmlspecialchars($row['PartID']); ?></td>
                                <td><?php echo htmlspecialchars($row['PartDesc']); ?></td>               
                                <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                                <td><?php echo htmlspecialchars($row['SellPrice']); ?></td>
                                <td><?php echo htmlspecialchars($row['Stock']); ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript Functions -->
    <script>
        // Update sort text and handle sorting
        function updateSort(sortBy) {
            document.getElementById('selectedSort').textContent = sortBy;
            
            // Get the table body
            const tbody = document.querySelector('table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort the rows based on the selected criteria
            rows.sort((a, b) => {
                let aValue, bValue;
                
                switch(sortBy) {
                    case 'Name':
                        aValue = a.cells[1].textContent.trim();
                        bValue = b.cells[1].textContent.trim();
                        break;
                    case 'Date Created':
                        aValue = parseInt(a.cells[0].textContent);
                        bValue = parseInt(b.cells[0].textContent);
                        break;
                }
                
                return aValue.localeCompare(bValue);
            });
            
            // Clear and re-append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        // Sort by Date Created when page loads
        $(document).ready(function() {
            updateSort('Date Created');
        });

        // Auto-hide popup after 3 seconds
        setTimeout(function() {
            let popup = document.getElementById("customPopup");
            if (popup) {
                popup.style.animation = "fadeOut 0.5s ease-in-out";
                setTimeout(() => popup.remove(), 500);
            }
        }, 3000);

        // Open part view form
        function openForm(partId) {
            $.get('part_view.php', { id: partId }, function(response) {
                document.body.innerHTML = response;
            });
        }

        // Handle Add New Part button click
        $(document).ready(function() {
            $('#addnewpart-link').on('click', function(e) {
                e.preventDefault();
                $.get('add_part_form.php', function(response) {
                    document.body.innerHTML = response;
                });
            });
        });
    </script>
</body>
</html> 