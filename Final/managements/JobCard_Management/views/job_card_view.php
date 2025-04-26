<?php
require_once '../../UserAccess/protect.php';
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

$jobId = $_GET['id'];

$sql = "SELECT j.*, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        car.LicenseNr, car.Brand, car.Model, pn.Nr as PhoneNumber
        FROM jobcards j 
        LEFT JOIN jobcar jc ON j.JobID = jc.JobID
        LEFT JOIN cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN carassoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN phonenumbers pn ON c.CustomerID = pn.CustomerID
        WHERE j.JobID = :jobId";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
$stmt->execute();

$jobCard = $stmt->fetch(PDO::FETCH_ASSOC);

// Get parts used in this job
$partsSql = "SELECT p.PartID, p.PartDesc, jp.PricePerPiece as price, jp.PiecesSold
             FROM jobcardparts jp
             JOIN parts p ON jp.PartID = p.PartID
             WHERE jp.JobID = :jobId
             ORDER BY jp.PiecesSold DESC";

$partsStmt = $pdo->prepare($partsSql);
$partsStmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
$partsStmt->execute();
$parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);

// Debug output to check parts data
error_log("Parts data for job ID " . $jobId . ": " . print_r($parts, true));

// Calculate totalPartsCost
$totalPartsCost = 0;
foreach ($parts as $part) {
    // Debug each part's data
    error_log("Part Details - ID: " . $part['PartID'] . 
              ", Description: " . $part['PartDesc'] . 
              ", Price: " . $part['price'] . 
              ", PiecesSold: " . $part['PiecesSold']);
    
    $price = $part['price'];
    $quantity = $part['PiecesSold'];
    $totalPartsCost += $price * $quantity;
}

// Debug the total calculation
error_log("Total parts cost calculation: " . $totalPartsCost);

// Calculate total cost including drive costs
$totalCost = $totalPartsCost + ($jobCard['DriveCosts'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card View</title>
    
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        #sticky-customer-header {
            position: fixed;
            top: 80px; /* Set a fixed top position to appear below the Job Card header */
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            max-width: 80%;
            z-index: 1000;
            transition: all 0.3s ease;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background-color: #ffffff;
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        .print-btn {
  background: none;
  border: none;
  color: #6c757d;
  padding: 8px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.print-btn:hover {
  transform: scale(1.1);
  color: #6c757d;
}

.print-btn i {
  font-size: 20px;
}
button:focus {
outline: none;
outline: none;
}
        
        /* Ensure there's padding-top to prevent content jump when sticky header appears */
        body {
            padding-top: 0;
        }
    </style>
</head>

<body>
    <div class="pc-container3">
        <!-- Sticky header for customer name -->
        <div id="sticky-customer-header" class="sticky-top shadow-sm d-none">
            <div class="d-flex align-items-center">
                <span class="font-weight-bold mr-2"><?php echo htmlspecialchars($jobCard['CustomerName']); ?></span>
                <span class="mr-2">|</span>
                <span class="mr-2"><?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model']); ?></span>
            </div>
        </div>
        
        <div class="form-container">
            <div class="top-container d-flex justify-content-between align-items-center">
                <a href="javascript:void(0);" onclick="window.location.href='job_cards_main.php'" class="back-arrow">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Job Card</h5>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="btngroup">
                        <button type="button" class="print-btn" onclick="printJobInvoice(<?php echo $jobId; ?>)">
                        <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-content">
                <fieldset disabled>
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Customer -->
                            <div class="form-group">
                                <label>Customer</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['CustomerName']); ?>">
                            </div>

                            <!-- Car Brand and Model -->
                            <div class="form-group">
                                <label>Car Brand and Model</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model']); ?>">
                            </div>

                            <!-- Registration Plate -->
                            <div class="form-group">
                                <label>Registration Plate</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['LicenseNr']); ?>">
                            </div>

                            <!-- Dates Row -->
                            <div class="row">
                                <!-- Date of Call -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Date of Call</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['DateCall']); ?>">
                                    </div>
                                </div>

                                <!-- Job Start Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Job Start Date</label>
                                        <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateStart']) ? htmlspecialchars($jobCard['DateStart']) : 'Not started'; ?>">
                                    </div>
                                </div>

                                <!-- Job End Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Job End Date</label>
                                        <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateFinish']) ? htmlspecialchars($jobCard['DateFinish']) : 'Not finished'; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Job Report -->
                            <div class="form-group">
                                <label>Job Report</label>
                                <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobReport']); ?></textarea>
                            </div>

                            <!-- Parts Used -->
                            <div class="form-group">
                                <label>Parts Used/Replaced</label>
                                <div id="partsContainer">
                                    <?php foreach ($parts as $part): ?>
                                    <div class="input-group">
                                        <div class="position-relative" style="flex: 1;">
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($part['PartDesc']); ?>" readonly>
                                        </div>
                                        <input type="text" class="form-control ml-2" style="max-width: 80px;" value="<?php echo htmlspecialchars($part['PiecesSold']); ?>" readonly>
                                        <input type="text" class="form-control ml-2" style="max-width: 100px;" value="€<?php echo number_format($part['price'], 2); ?>" readonly>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($parts)): ?>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="No parts used" readonly>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted mt-2">Total parts: €<?php echo number_format($totalPartsCost, 2); ?></small>
                            </div>

                            <!-- Costs Row -->
                            <div class="row mt-3">
                                <!-- Total Costs -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total Costs</label>
                                        <input type="text" class="form-control" value="€<?php echo number_format($totalCost, 2); ?>">
                                        <small class="form-text text-muted">(excl. VAT)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['PhoneNumber']); ?>">
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label>Location of Visit</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Location']); ?>">
                            </div>

                            <!-- Job Description -->
                            <div class="form-group">
                                <label>Job Description by Customer</label>
                                <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobDesc']); ?></textarea>
                            </div>

                            <!-- Rides -->
                            <div class="form-group">
                                <label>Rides</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Rides']); ?>">
                            </div>

                            <!-- Drive Costs -->
                            <div class="form-group">
                                <label>Drive Costs</label>
                                <input type="text" class="form-control" value="€<?php echo number_format($jobCard['DriveCosts'], 2); ?>">
                            </div>

                            <!-- Photos -->
                            <div class="form-group">
                                <label>Photos of damage</label>
                                <div class="row">
                                    <?php 
                                    if (!empty($jobCard['Photo'])) {
                                        $photos = json_decode($jobCard['Photo'], true);
                                        if (is_array($photos)) {
                                            foreach ($photos as $photo): ?>
                                                <div class="col-md-3 mb-3">
                                                    <div class="position-relative">
                                                        <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                                             class="img-fluid rounded" alt="Job photo"
                                                             style="max-height: 150px; cursor: pointer;"
                                                             onclick="showPhotoModal(this.src)">
                                                    </div>
                                                </div>
                                            <?php endforeach;
                                        }
                                    } else {
                                        echo '<div class="col-12"><p class="text-muted">No photos available</p></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

               

                <div class="btngroup text-center mt-4">
            <button type="button" class="btn btn-primary " onclick="loadEditForm(<?php echo $jobId; ?>)">Edit <i class="fas fa-edit"></i></button>
            <button type="button" class="btn btn-danger " onclick="showDeleteModal()">Delete <i class="fas fa-trash"></i></button>
        </div>
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Photo View</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Enlarged photo">
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Job Card</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>What would you like to do with the parts used in this job card?</p>
                    
                    <div class="parts-list mb-3">
                        <h6>Parts Used:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Part</th>
                                        <th>Quantity</th>
                                        <th>Return to Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parts as $part): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($part['PartDesc']); ?></td>
                                        <td><?php echo htmlspecialchars($part['PiecesSold']); ?></td>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input return-part-checkbox" 
                                                       id="return_<?php echo $part['PartID']; ?>" 
                                                       data-part-id="<?php echo $part['PartID']; ?>"
                                                       data-quantity="<?php echo $part['PiecesSold']; ?>">
                                                <label class="custom-control-label" for="return_<?php echo $part['PartID']; ?>"></label>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteJobCard()">Delete Job Card</button>
                </div>
            </div>
        </div>
    </div>

    <iframe id="printFrame" style="display:none;"></iframe>

    <script>

           // Function to load the edit form
function loadEditForm(jobId) {
        $.get('edit_job_card.php', { id: jobId }, function(response) {
            $('#dynamicContent').html(response);
        });
    }

        // Set modal image source when a photo is clicked
        document.querySelectorAll('.photo-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('modalImage').src = this.href;
            });
        });

        // Function to show delete confirmation modal
        function showDeleteModal() {
            $('#deleteModal').modal('show');
        }

        // Sticky header logic
        window.addEventListener('scroll', function() {
            const customerNameField = document.querySelector('.form-group:first-child');
            const stickyHeader = document.getElementById('sticky-customer-header');
            const headerHeight = document.querySelector('.top-container').offsetHeight;
            
            if (customerNameField) {
                const rect = customerNameField.getBoundingClientRect();
                // Show the sticky header when the customer name field is scrolled out of view
                if (rect.bottom <= headerHeight + 10) {
                    stickyHeader.classList.remove('d-none');
                    
                    // Adjust the sticky header position to be within the form container width
                    const formContainer = document.querySelector('.form-container');
                    if (formContainer) {
                        const formContainerRect = formContainer.getBoundingClientRect();
                        stickyHeader.style.maxWidth = (formContainerRect.width * 0.8) + 'px';
                    }
                } else {
                    stickyHeader.classList.add('d-none');
                }
            }
        });

        // Function to delete job card and handle parts
        function deleteJobCard() {
            // Get all checked parts
            const checkedParts = [];
            document.querySelectorAll('.return-part-checkbox:checked').forEach(checkbox => {
                checkedParts.push({
                    partId: checkbox.dataset.partId,
                    quantity: parseInt(checkbox.dataset.quantity)
                });
            });

            // Send delete request with parts information
            fetch('../controllers/delete_job_card.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    jobId: <?php echo $jobId; ?>,
                    partsToReturn: checkedParts
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to job cards list
                    window.location.href = 'job_cards_main.php';
                } else {
                    alert('Error deleting job card: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the job card');
            });
        }

        // Function to calculate total costs
        function calculateTotal() {
            const driveCosts = parseFloat(document.getElementById('driveCosts').value) || 0;
            let partPricesTotal = 0;
            
            // Get all part prices and quantities
            const partPrices = document.getElementsByName('partPrices[]');
            const partQuantities = document.getElementsByName('partQuantities[]');
            
            for (let i = 0; i < partPrices.length; i++) {
                const price = parseFloat(partPrices[i].value) || 0;
                const quantity = parseInt(partQuantities[i]?.value) || 1;
                partPricesTotal += price * quantity;
            }
            
            // Update parts total display
            const partsTotalElement = document.getElementById('partsTotal');
            if (partsTotalElement) {
                partsTotalElement.textContent = `Total parts: ${partPricesTotal.toFixed(2)} €`;
            }
            
            const total = driveCosts + partPricesTotal;
            
            // Update both the total costs field and the calculated total display
            const totalCostsField = document.getElementById('totalCosts');
            totalCostsField.value = total.toFixed(2);
            
            const totalCostsGroup = totalCostsField.closest('.form-group');
            let calculatedTotalElement = totalCostsGroup.querySelector('.calculated-total');
            
            if (!calculatedTotalElement) {
                calculatedTotalElement = document.createElement('small');
                calculatedTotalElement.className = 'form-text text-muted calculated-total';
                totalCostsGroup.appendChild(calculatedTotalElement);
            }
            
            calculatedTotalElement.textContent = `Calculated total: ${total.toFixed(2)} €`;
        }

        // Add event listeners for cost calculation
        document.getElementById('driveCosts').addEventListener('input', calculateTotal);

        function printJobInvoice(jobId) {
            var iframe = document.getElementById('printFrame');
            iframe.src = 'print/PrintInvoice.php?id=' + jobId + '&print=1';
            iframe.onload = function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
        }

        function showPhotoModal(src) {
            $('#modalImage').attr('src', src);
            $('#photoModal').modal('show');
        }
    </script>
</body>
</html> 