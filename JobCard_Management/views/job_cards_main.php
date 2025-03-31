<?php
require_once '../config/db_connection.php';
require_once '../includes/sanitize_inputs.php';

// SQL query to fetch all job cards with related information
$sql = "SELECT j.JobID, j.Location, j.DateCall, j.JobDesc, j.DateStart, j.DateFinish, 
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, 
        car.LicenseNr, car.Brand, car.Model, 
        pn.Nr as PhoneNumber,
        a.Address
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN CarAssoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN PhoneNumbers pn ON c.CustomerID = pn.CustomerID
        LEFT JOIN Addresses a ON c.CustomerID = a.CustomerID
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

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
    
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        text-align: left;
        background-color: #fff;
    }

    .pc-container3 {
        padding: 20px;
        background-color: #fff;
    }

    .form-container {
        background-color: transparent;
        padding: 20px;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
    }

    .table thead th {
        background-color: transparent;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
        font-weight: 600;
        color: #495057;
    }

    .table tbody tr {
        border-bottom: 1px solid #dee2e6;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: rgb(201, 214, 223);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 5px;
    }

    /* Status styles */
    .status-open {
        color: #28a745;
        font-weight: bold;
    }
    
    .status-closed {
        color: rgb(255, 0, 0);
        font-weight: bold;
    }

    .title-container {
        margin-bottom: 1.5rem;
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

                    <button href="#" type="button" class="btn btn-success mr-3" data-toggle="modal" data-target="#printModal">Print 
                        <span>
                            <i class="fas fa-print"></i>
                        </span>
                    </button>
                    <button href="#" id="addnewjobcard-link" type="button" class="btn btn-primary">Add New Job Card 
                        <span>
                            <i class="fas fa-plus"></i>
                        </span>
                    </button>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Car Info</th>
                        <th>Phone</th>
                        <th>Job Start/End date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr onclick="openForm('<?php echo $row['JobID']; ?>')">
                            <td><i class="fas fa-file-alt"></i></td>
                            <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                            <td>
                                <?php 
                                $carInfo = '';
                                if (!empty($row['Brand']) || !empty($row['Model'])) {
                                    $carInfo = htmlspecialchars(trim($row['Brand'] . ' ' . $row['Model']));
                                }
                                if (!empty($row['LicenseNr'])) {
                                    $carInfo .= (!empty($carInfo) ? ', ' : '') . htmlspecialchars($row['LicenseNr']);
                                }
                                echo !empty($carInfo) ? $carInfo : 'N/A';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['PhoneNumber'] ?: 'N/A'); ?></td>
                            <td>
                                <?php 
                                $startDate = !empty($row['DateStart']) ? date('d/m/Y', strtotime($row['DateStart'])) : 'N/A';
                                $endDate = !empty($row['DateFinish']) ? date('d/m/Y', strtotime($row['DateFinish'])) : 'N/A';
                                echo $startDate . ' - ' . $endDate;
                                ?>
                            </td>
                            <td><?php 
                                if (!empty($row['DateFinish'])) {
                                    echo '<span class="status-closed">CLOSED</span>';
                                } else {
                                    echo '<span class="status-open">OPEN</span>';
                                }
                            ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Print Job Cards</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="printSearch" class="form-control" placeholder="Search job cards...">
                        </div>
                        <div class="col-md-6">
                            <select id="printFilter" class="form-control">
                                <option value="all">All Job Cards</option>
                                <option value="name">Customer Name</option>
                                <option value="car">Car Info</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <span id="selectionCount">0 job(s) selected</span>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" onclick="printAllJobs()">Print All</button>
                            <button type="button" class="btn btn-success ml-2" onclick="printSelectedJobs()">Print Selected</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="printSelectAll"></th>
                                    <th>Name</th>
                                    <th>Car Info</th>
                                    <th>Phone</th>
                                    <th>Job Start/End date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="printJobsTable">
                                <!-- Jobs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <nav aria-label="Print modal pagination" class="modal-pagination">
                            <ul class="pagination mb-0">
                                <?php
                                for ($i = 1; $i <= ceil(count($result) / 10); $i++) {
                                    echo "<li class='page-item'><a class='page-link' href='#' onclick='loadPrintModalPage($i)'>$i</a></li>";
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="modal-footer" style="display: none;">
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden iframe for printing -->
    <iframe id="printFrame" style="display: none;"></iframe>

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
                        // Extract dates from the Job Start/End date column
                        const aDateText = a.cells[4].textContent.trim();
                        const bDateText = b.cells[4].textContent.trim();
                        
                        // Try to get start date first, if not available use end date
                        const aStartDate = aDateText.split(' - ')[0];
                        const bStartDate = bDateText.split(' - ')[0];
                        
                        if (aStartDate === 'N/A' && bStartDate === 'N/A') {
                            return 0;
                        } else if (aStartDate === 'N/A') {
                            return 1; // b comes first
                        } else if (bStartDate === 'N/A') {
                            return -1; // a comes first
                        }
                        
                        // Convert DD/MM/YYYY to Date objects
                        const aParts = aStartDate.split('/');
                        const bParts = bStartDate.split('/');
                        
                        if (aParts.length === 3 && bParts.length === 3) {
                            aValue = new Date(aParts[2], aParts[1] - 1, aParts[0]);
                            bValue = new Date(bParts[2], bParts[1] - 1, bParts[0]);
                            return bValue - aValue; // Most recent first
                        }
                        
                        return 0;
                    case 'Status':
                        aValue = a.cells[5].textContent.trim();
                        bValue = b.cells[5].textContent.trim();
                        
                        // Custom order: OPEN, CLOSED
                        const statusOrder = {
                            'OPEN': 0,
                            'CLOSED': 1
                        };
                        
                        return statusOrder[aValue] - statusOrder[bValue];
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

        // Global variable to store selected job IDs
        let selectedJobIds = new Set();

        // Function to update selection count
        function updateSelectionCount() {
            const selectedCount = selectedJobIds.size;
            $('#selectionCount').text(selectedCount + ' job(s) selected');
        }

        // Function to load print modal page
        function loadPrintModalPage(page) {
            $.ajax({
                url: 'print/get_print_jobs.php',
                method: 'GET',
                data: { page: page },
                success: function(response) {
                    $('#printJobsTable').html(response);
                    
                    // Restore selections after loading new page
                    $('.print-job-select').each(function() {
                        const jobId = $(this).closest('tr').data('job-id');
                        $(this).prop('checked', selectedJobIds.has(jobId));
                    });
                    
                    // Update select all checkbox state
                    const totalCheckboxes = $('.print-job-select').length;
                    const checkedCheckboxes = $('.print-job-select:checked').length;
                    $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
                    
                    // Update pagination active state
                    $('.modal-pagination .page-item').removeClass('active');
                    $(`.modal-pagination .page-item:nth-child(${page})`).addClass('active');

                    // Update selection count
                    updateSelectionCount();
                },
                error: function() {
                    alert('Error loading jobs. Please try again.');
                }
            });
        }

        // Print functions
        function printAllJobs() {
            const iframe = document.getElementById('printFrame');
            iframe.src = 'print/PrintJobCardList.php';
            iframe.onload = function() {
                iframe.contentWindow.print();
            };
            $('#printModal').modal('hide');
        }

        function printSelectedJobs() {
            if (selectedJobIds.size === 0) {
                alert('Please select at least one job to print');
                return;
            }
            
            const iframe = document.getElementById('printFrame');
            iframe.src = 'print/PrintSelectedJobs.php?ids=' + Array.from(selectedJobIds).join(',');
            iframe.onload = function() {
                iframe.contentWindow.print();
            };
            $('#printModal').modal('hide');
        }

        // Initialize print modal functionality
        $(document).ready(function() {
            // Load first page when modal opens
            $('#printModal').on('show.bs.modal', function() {
                loadPrintModalPage(1);
            });

            // Handle print select all checkbox
            $('#printSelectAll').change(function() {
                const isChecked = $(this).prop('checked');
                $('.print-job-select').prop('checked', isChecked);
                
                // Update selectedJobIds set
                if (isChecked) {
                    $('.print-job-select').each(function() {
                        selectedJobIds.add($(this).closest('tr').data('job-id'));
                    });
                } else {
                    selectedJobIds.clear();
                }
                
                updateSelectionCount();
            });

            // Handle individual checkbox changes
            $(document).on('change', '.print-job-select', function() {
                const jobId = $(this).closest('tr').data('job-id');
                if ($(this).prop('checked')) {
                    selectedJobIds.add(jobId);
                } else {
                    selectedJobIds.delete(jobId);
                }
                
                // Update select all checkbox state
                const totalCheckboxes = $('.print-job-select').length;
                const checkedCheckboxes = $('.print-job-select:checked').length;
                $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
                
                updateSelectionCount();
            });

            // Handle search functionality
            $('#printSearch').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                var filterType = $('#printFilter').val();
                
                $('#printJobsTable tr').each(function() {
                    var row = $(this);
                    var show = false;
                    
                    if (searchText === '') {
                        show = true;
                    } else {
                        switch(filterType) {
                            case 'name':
                                show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                                break;
                            case 'car':
                                show = row.find('td:eq(2)').text().toLowerCase().includes(searchText);
                                break;
                            case 'status':
                                show = row.find('td:eq(5)').text().toLowerCase().includes(searchText);
                                break;
                            default:
                                show = row.text().toLowerCase().includes(searchText);
                        }
                    }
                    
                    row.toggle(show);
                });
            });

            // Handle filter change
            $('#printFilter').change(function() {
                $('#printSearch').trigger('keyup');
            });
        });
    </script>
</body>
</html> 
