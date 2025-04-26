<?php
require_once '../../UserAccess/protect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/scripts.js"></script>
    <style>
        /* Remove spinner buttons from number inputs */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Style for the parts search dropdown */
        .part-search-results-container {
            position: absolute;
            width: 100%;
            top: 38px;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border-top: none;
        }
        .modal-fullscreen {
            padding: 0 !important;
        }
        .modal-fullscreen .modal-dialog {
            width: 100% !important;
            max-width: none;
            height: 100%;
            margin: 0;
        }
        .modal-fullscreen .modal-content {
            height: 100%;
            border: 0;
            border-radius: 0;
        }
        .modal-fullscreen .modal-body {
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.9);
        }
        .modal-fullscreen #modalImage {
            max-height: 95vh;
            object-fit: contain;
        }
        .text-danger {
            color: #dc3545;
        }
        .photo-preview {
            max-height: 150px;
            object-fit: contain;
        }
        .error {
            color: #dc3545;
        }
        #sticky-customer-header {
            position: fixed;
            top: 50px;
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
    </style>
</head>
<body>
    <div class="pc-container3">
        <div class="form-container">
       
        
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='job_cards_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                <h5 class="mb-0">Job Card</h5>
            </div>
            <div style="width: 30px;"></div>
        </div>

         <!-- Alert container -->
         <div id="alert-container" style="margin-bottom: 1rem;"></div>
         
            <form action="../controllers/add_job_card_controller.php" method="POST" enctype="multipart/form-data" id="addJobCardForm">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Customer Selection -->
                        <div class="form-group">
                            <label for="customer">Customer <span class="text-danger">*</span></label>
                            <input type="text" id="customerSearch" class="form-control" placeholder="Search customer...">
                            <div id="customerSearchResults" class="list-group mt-1" style="max-height: 150px; overflow-y: auto;"></div>
                            <select name="customer" id="customer" class="form-control mt-2" required style="display: none;">
                                <option value="">Select Customer</option>
                                <?php
                                require_once '../config/db_connection.php';
                                $sql = "SELECT CustomerID, CONCAT(FirstName, ' ', LastName) as CustomerName FROM customers";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                while ($row = $stmt->fetch()) {
                                    echo "<option value='" . $row['CustomerID'] . "'>" . htmlspecialchars($row['CustomerName']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Car Details -->
                        <div class="form-group">
                            <label for="carBrandModel">Car Brand and Model <span class="text-danger">*</span></label>
                            <select name="carBrandModel" id="carBrandModel" class="form-control" onchange="updateRegistrationPlate(this)" required>
                                <option value="">Select Car Brand and Model</option>
                            </select>
                        </div>

                        <!-- Registration Plate -->
                        <div class="form-group">
                            <label for="registration">Registration Plate</label>
                            <input type="text" name="registration" id="registration" class="form-control" required readonly style="background-color: #e9ecef;">
                        </div>

                        <!-- Dates Row -->
                        <div class="row">
                        <!-- Date of Call -->
                            <div class="col-md-4">
                        <div class="form-group">
                            <label for="dateCall">Date of Call <span class="text-danger">*</span></label>
                            <input type="date" name="dateCall" id="dateCall" class="form-control" required>
                                </div>
                            </div>

                            <!-- Job Start Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jobStartDate">Job Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="jobStartDate" id="jobStartDate" class="form-control" required>
                                </div>
                            </div>

                            <!-- Job End Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jobEndDate">Job End Date</label>
                                    <input type="date" name="jobEndDate" id="jobEndDate" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Job Report -->
                        <div class="form-group">
                            <label for="jobReport">Job Report</label>
                            <textarea name="jobReport" id="jobReport" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Parts Used/Replaced -->
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="partsUsed" class="mb-0">Parts Used/Replaced</label>
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem!important; font-size: 0.875rem!important;" onclick="addPartField()">Add Part</button>
                            </div>
                            <div id="partsContainer">
                                 <div class="input-group mt-2 d-flex flex-column flex-sm-row">
                                    <div class="position-relative w-100 mb-2 mb-sm-0" style="flex: 1;">
                                        <input type="text" class="form-control part-search" placeholder="Search part...">
                                        <div class="list-group mt-1"></div>
                                        <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                            <button type="button" class="btn btn-link text-danger" onclick="removePart(this)" style="padding: 0;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex d-sm-inline-flex">
                                        <input type="number" name="partQuantities[]" class="form-control ml-sm-2" min="1" value="1" style="max-width: 80px;" placeholder="Qty">
                                        <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" value="0.00" style="max-width: 100px;" placeholder="Price">
                                    </div>
                                    <input type="hidden" name="parts[]" value="">
                                    <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                                        <option value="">Select Part</option>
                                        <?php
                                        $sql = "SELECT p.PartID, p.PartDesc, p.SellPrice, p.Stock, p.DateCreated, s.Name as SupplierName 
                                                FROM parts p 
                                                LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID 
                                                ORDER BY p.PartDesc ASC";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        while ($row = $stmt->fetch()) {
                                            $disabled = ($row['Stock'] <= 0) ? 'disabled' : '';
                                            echo "<option value='" . $row['PartID'] . "' 
                                                    data-stock='" . $row['Stock'] . "' 
                                                    data-price='" . $row['SellPrice'] . "' 
                                                    data-date-created='" . $row['DateCreated'] . "' 
                                                    data-supplier='" . htmlspecialchars($row['SupplierName']) . "' " . 
                                                    $disabled . ">" . 
                                                    htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . 
                                                    ($row['Stock'] <= 0 ? ' - Out of Stock' : '') . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-2" id="partsTotal"></small>
                        </div>

                        <!-- Costs Row -->
                        <div class="row mt-3">
                            <!-- Total Costs -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="totalCosts">Total Costs</label>
                                    <input type="number" name="totalCosts" id="totalCosts" class="form-control" step="0.01" min="0" value="0.00" readonly style="background-color: #e9ecef;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" id="phone" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>

                        <!-- Location of Visit -->
                        <div class="form-group">
                            <label for="location">Location of Visit <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="location" class="form-control" required>
                        </div>

                        <!-- Job Description -->
                        <div class="form-group">
                            <label for="jobDescription">Job Description by Customer <span class="text-danger">*</span></label>
                            <textarea name="jobDescription" id="jobDescription" class="form-control" rows="3" required></textarea>
                        </div>

                        <!-- Rides -->
                        <div class="form-group">
                            <label for="rides">Rides</label>
                            <input type="number" name="rides" id="rides" class="form-control" min="0">
                        </div>

                        <!-- Drive Costs -->
                        <div class="form-group">
                            <label for="driveCosts">Drive Costs</label>
                            <input type="number" name="driveCosts" id="driveCosts" class="form-control" step="0.01" min="0">
                        </div>

                        <!-- Photos of damage -->
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="photos" class="mb-0">Photos of damage</label>
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem!important; font-size: 0.875rem!important;" onclick="addPhotoField()">Add Photos</button>
                            </div>
                            <div id="photosContainer">
                                <div class="input-group">
                                    <div class="position-relative" style="flex: 1;">
                                        <input type="file" name="photos[]" class="form-control photo-input" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div id="photoPreviewContainer" class="mt-2 row">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="btngroup text-center mt-4">
                    <button type="submit" class="btn btn-primary">Save <i class="fas fa-save"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="photoModalDialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Photo View</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="toggleSize">
                            <i class="fas fa-expand" id="sizeIcon"></i>
                        </button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Enlarged photo">
                </div>
            </div>
        </div>
    </div>


    <style>
        .modal-fullscreen {
            padding: 0 !important;
        }
        .modal-fullscreen .modal-dialog {
            width: 100% !important;
            max-width: none;
            height: 100%;
            margin: 0;
        }
        .modal-fullscreen .modal-content {
            height: 100%;
            border: 0;
            border-radius: 0;
        }
        .modal-fullscreen .modal-body {
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.9);
        }
        .modal-fullscreen #modalImage {
            max-height: 95vh;
            object-fit: contain;
        }
        .text-danger {
            color: #dc3545;
        }
    </style>
    
<script>
$(document).ready(function() {
    // Add event listeners for quantity changes
    $(document).on('input', 'input[name="partQuantities[]"]', function() {
        calculateTotal();
    });

    // Add event listeners for price changes
    $(document).on('input', 'input[name="partPrices[]"]', function() {
        calculateTotal();
    });

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
        
        // Update the total costs field
        const totalCostsField = document.getElementById('totalCosts');
        totalCostsField.value = total.toFixed(2);
    }

    // Initialize total calculation
    calculateTotal();

    // AJAX form submission
    $('#addJobCardForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default submit
        
        var formData = new FormData(this);
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalButtonText = submitButton.html();
        
        // Show loading state
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#alert-container').empty(); // Clear previous alerts
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success message
                    $('#alert-container').html('<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle mr-2"></i>' +
                        response.message +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>');
                    
                    // Redirect after a short delay (e.g., 2 seconds)
                    setTimeout(function() {
                        window.location.href = 'job_cards_main.php'; // Redirect to the main list page
                    }, 2000); 
                    
                } else {
                    // Show error message
                    $('#alert-container').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle mr-2"></i>' +
                        response.message +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>');
                    // Re-enable the button on error
                    submitButton.prop('disabled', false).html(originalButtonText);
                }
            },
            error: function(xhr, status, error) {
                // Show generic error message
                $('#alert-container').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle mr-2"></i>' +
                    'An error occurred while saving. Please try again.' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '</div>');
                // Re-enable the button on error
                submitButton.prop('disabled', false).html(originalButtonText);
            }
        });
    });
    
    // Initialize customer search, part search, etc. here if not already done in scripts.js
    // For example: 
    // initializeCustomerSearch(); 
    // initializePartSearch(); 
});
</script>

</body>
</html>
