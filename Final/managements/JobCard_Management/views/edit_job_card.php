<?php
require_once '../../UserAccess/protect.php';
require_once '../config/db_connection.php';

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get job card details
$sql = "SELECT j.*, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, c.CustomerID,
        car.LicenseNr, car.Brand, car.Model, pn.Nr as PhoneNumber
        FROM jobcards j 
        LEFT JOIN jobcar jc ON j.JobID = jc.JobID
        LEFT JOIN cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN carassoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN phonenumbers pn ON c.CustomerID = pn.CustomerID
        WHERE j.JobID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$jobId]);
$jobCard = $stmt->fetch();

// Get parts used in this job
$partsSql = "SELECT p.PartID, p.PartDesc, p.SellPrice, jp.PiecesSold, jp.PricePerPiece
             FROM jobcardparts jp
             JOIN parts p ON jp.PartID = p.PartID
             WHERE jp.JobID = ?
             ORDER BY jp.PiecesSold DESC";

$partsStmt = $pdo->prepare($partsSql);
$partsStmt->execute([$jobId]);
$parts = $partsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Card</title>
    
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
            top: 50px; /* Set a fixed top position to appear below the Job Card header */
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

<script>
        $(document).ready(function() {
            
            $('#editJobCardForm').on('submit', function(e) {
                e.preventDefault();
                
                // Create FormData object
                var formData = new FormData(this);
                
                // Add removed photos to form data
                if (window.removedPhotos.length > 0) {
                    formData.append('removed_photos', JSON.stringify(window.removedPhotos));
                }
                
                // Submit form with AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        try {
                            response = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (response.status === 'success') {
                                // Show success message
                                const successAlert = document.createElement('div');
                                successAlert.className = 'alert alert-success alert-dismissible fade show';
                                successAlert.innerHTML = `
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span>${response.message}</span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                `;
                                document.querySelector('.showmessage').insertBefore(successAlert, document.querySelector('.showmessage').firstChild);
                                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                
                                // Redirect after showing the message
                                setTimeout(() => {
                                    openForm('<?php echo $jobId; ?>');
                                }, 2000);
                            } else {
                                // Show error message
                                const errorAlert = document.createElement('div');
                                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                                errorAlert.innerHTML = `
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span>${response.message || 'Error updating job card'}</span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                `;
                                document.querySelector('.showmessage').insertBefore(errorAlert, document.querySelector('.showmessage').firstChild);
                                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            // Show error message
                            const errorAlert = document.createElement('div');
                            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                            errorAlert.innerHTML = `
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span>Error updating job card: Invalid response from server</span>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            `;
                            document.querySelector('.showmessage').insertBefore(errorAlert, document.querySelector('.showmessage').firstChild);
                            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>Error updating job card: ${error}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        `;
                        document.querySelector('.showmessage').insertBefore(errorAlert, document.querySelector('.showmessage').firstChild);
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            });
            
            // Handle photo preview - only set up once
            $('.photo-input').off('change').on('change', function() {
                var input = this;
                var container = $('#photoPreviewContainer');
                
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = $('<div class="col-md-3 mb-3 photo-preview-container">' +
                            '<div class="position-relative">' +
                            '<img src="' + e.target.result + '" class="img-fluid rounded photo-preview" alt="Preview">' +
                            '<button type="button" class="btn btn-danger btn-sm position-absolute remove-photo" style="top: 5px; right: 5px;">' +
                            '<i class="fas fa-trash"></i></button>' +
                            '</div></div>');
                        container.append(preview);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

            // Handle photo removal - only set up once
            $('.remove-photo').off('click').on('click', function() {
                var photoName = $(this).data('photo');
                if (photoName) {
                    window.removedPhotos.push(photoName);
                }
                $(this).closest('.photo-preview-container').remove();
            });
        });

        // Function to add new photo field
        function addPhotoField() {
            const container = document.getElementById('photosContainer');
            if (!container) return;
            
            const newField = document.createElement('div');
            newField.className = 'input-group mt-2';
            newField.innerHTML = `
                <div class="position-relative" style="flex: 1;">
                    <input type="file" name="photos[]" class="form-control photo-input" accept="image/*">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link text-danger" onclick="removeNewPhoto(this)" style="padding: 0;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newField);
            
            // Setup preview for the new field
            $(newField.querySelector('.photo-input')).off('change').on('change', function() {
                var input = this;
                var container = $('#photoPreviewContainer');
                
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = $('<div class="col-md-3 mb-3 photo-preview-container">' +
                            '<div class="position-relative">' +
                            '<img src="' + e.target.result + '" class="img-fluid rounded photo-preview" alt="Preview">' +
                            '<button type="button" class="btn btn-danger btn-sm position-absolute remove-photo" style="top: 5px; right: 5px;">' +
                            '<i class="fas fa-trash"></i></button>' +
                            '</div></div>');
                        container.append(preview);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
        }

        // Function to remove new photo field
        function removeNewPhoto(button) {
            const inputGroup = button.closest('.input-group');
            const previewId = inputGroup.querySelector('.photo-input')?.dataset.previewId;
            const previewElement = previewId ? document.getElementById(previewId) : null;
            
            if (previewElement) {
                // If there's a preview, show confirmation dialog
                if (confirm('Are you sure you want to delete this photo?')) {
                    // Remove both the input field and the preview
                    inputGroup.remove();
                    previewElement.remove();
                }
            } else {
                // If there's no preview, just remove the input field
                inputGroup.remove();
            }
        }

        // Cancel button functionality
        function openForm(jobId) {
            $.get('job_card_view.php', { id: jobId }, function(response) {
                $('#dynamicContent').html(response);
            });
        }

        function updatePartPrice(select) {
            const selectedOption = select.options[select.selectedIndex];
            const priceInput = select.closest('.input-group').querySelector('input[name="partPrices[]"]');
            const defaultPrice = selectedOption.getAttribute('data-price');
            
            // Only update the price if it hasn't been manually changed
            if (priceInput.value === '' || priceInput.value === '0.00') {
                priceInput.value = defaultPrice;
            }
            
            // Update the hidden part ID input
            const partIdInput = select.closest('.input-group').querySelector('input[name="parts[]"]');
            partIdInput.value = select.value;
            
            // Update the part description input
            const partDescInput = select.closest('.input-group').querySelector('.part-search');
            partDescInput.value = selectedOption.text;
            
            calculateTotal();
        }
    </script>
</head>

<body>
    <div class="pc-container3">
        <!-- Sticky header for customer name -->
        <div id="sticky-customer-header" class="sticky-top shadow-sm d-none">
            <div class="d-flex align-items-center">
                <span class="font-weight-bold mr-2"><?php echo htmlspecialchars($jobCard['CustomerName'] ?? ''); ?></span>
                <span class="mr-2">|</span>
                <span class="mr-2"><?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model']); ?></span>
            </div>
        </div>
        
        <div class="form-container">
           
            <div class="top-container d-flex justify-content-between align-items-center">
                <a href="javascript:void(0);" onclick="window.location.href='<?php echo $_GET['previous_link']; ?>'" class="back-arrow">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Edit Job Card</h5>
                </div>
                <div class="d-flex justify-content-end">
                </div>
            </div>
         
            <div class="form-content">
                <form id="editJobCardForm" class="showmessage ajax-form" action="../controllers/update_job_card_controller.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $jobId; ?>">
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Customer Selection -->
                            <div class="form-group">
                                <label for="customer">Customer <span class="text-danger">*</span></label>
                                <input type="text" id="customerSearch" class="form-control" placeholder="Search customer..." value="<?php echo htmlspecialchars($jobCard['CustomerName']); ?>">
                                <div id="customerSearchResults" class="list-group mt-1"></div>
                                <select name="customer" id="customer" class="form-control mt-2" required style="display: none;">
                                    <?php
                                    $customerSql = "SELECT DISTINCT c.CustomerID, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName 
                                                   FROM customers c 
                                                   LEFT JOIN phonenumbers pn ON c.CustomerID = pn.CustomerID 
                                                   ORDER BY c.FirstName ASC, c.LastName ASC";
                                    $customerStmt = $pdo->prepare($customerSql);
                                    $customerStmt->execute();
                                    while ($row = $customerStmt->fetch()) {
                                        $selected = ($row['CustomerID'] == $jobCard['CustomerID']) ? 'selected' : '';
                                        echo "<option value='" . $row['CustomerID'] . "' " . $selected . ">" . 
                                             htmlspecialchars($row['CustomerName']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Car Brand and Model -->
                            <div class="form-group">
                                <label for="carBrandModel">Car Brand and Model <span class="text-danger">*</span></label>
                                <select name="carBrandModel" id="carBrandModel" class="form-control" onchange="updateRegistrationPlate(this)" required>
                                    <option value="">Select Car Brand and Model</option>
                                    <?php
                                    $carSql = "SELECT c.* 
                                              FROM cars c
                                              JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr
                                              WHERE ca.CustomerID = ?";
                                    $carStmt = $pdo->prepare($carSql);
                                    $carStmt->execute([$jobCard['CustomerID']]);
                                    
                                    while ($row = $carStmt->fetch()) {
                                        $value = $row['Brand'] . ' ' . $row['Model'];
                                        $selected = ($row['Brand'] == $jobCard['Brand'] && $row['Model'] == $jobCard['Model']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($value) . "' data-license='" . htmlspecialchars($row['LicenseNr']) . "' " . $selected . ">" . 
                                             htmlspecialchars($value) . " (" . htmlspecialchars($row['LicenseNr']) . ")</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Registration Plate -->
                            <div class="form-group">
                                <label for="registration">Registration Plate</label>
                                <input type="text" name="registration" id="registration" class="form-control" 
                                       value="<?php echo htmlspecialchars($jobCard['LicenseNr']); ?>" required readonly style="background-color: #e9ecef;">
                            </div>

                            <!-- Dates Row -->
                            <div class="row">
                                <!-- Date of Call -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dateCall">Date of Call <span class="text-danger">*</span></label>
                                        <input type="date" name="dateCall" id="dateCall" class="form-control" 
                                               value="<?php echo htmlspecialchars($jobCard['DateCall']); ?>" required>
                                    </div>
                                </div>

                                <!-- Job Start Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="jobStartDate">Job Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="jobStartDate" id="jobStartDate" class="form-control" 
                                               value="<?php echo htmlspecialchars($jobCard['DateStart']); ?>" required>
                                    </div>
                                </div>

                                <!-- Job End Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="jobEndDate">Job End Date</label>
                                        <input type="date" name="jobEndDate" id="jobEndDate" class="form-control" 
                                               value="<?php echo htmlspecialchars($jobCard['DateFinish']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Job Report -->
                            <div class="form-group">
                                <label for="jobReport">Job Report</label>
                                <textarea name="jobReport" id="jobReport" class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobReport']); ?></textarea>
                            </div>

                            <!-- Parts Used/Replaced -->
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="partsUsed" class="mb-0">Parts Used/Replaced</label>
                                    <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem!important; font-size: 0.875rem!important;" onclick="addPartField()">Add Part</button>
                                </div>
                                <div id="partsContainer">
                                    <?php foreach ($parts as $part): ?>
                                    <div class="input-group mt-2">
                                        <div class="position-relative" style="flex: 1;">
                                            <input type="text" class="form-control part-search" placeholder="Search part..." value="<?php echo htmlspecialchars($part['PartDesc']); ?>">
                                            <div class="list-group mt-1 part-search-results-container"></div>
                                            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                                <button type="button" class="btn btn-link text-danger" onclick="removePart(this)" style="padding: 0;">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <input type="number" name="partQuantities[]" class="form-control ml-2" min="1" value="<?php echo htmlspecialchars($part['PiecesSold']); ?>" style="max-width: 80px;" placeholder="Qty">
                                        <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>" style="max-width: 100px;" placeholder="Price">
                                        <input type="hidden" name="parts[]" value="<?php echo htmlspecialchars($part['PartID']); ?>">
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
                                                $selected = ($row['PartID'] == $part['PartID']) ? 'selected' : '';
                                                echo "<option value='" . $row['PartID'] . "' 
                                                        data-stock='" . $row['Stock'] . "' 
                                                        data-price='" . $row['SellPrice'] . "' 
                                                        data-date-created='" . $row['DateCreated'] . "' 
                                                        data-supplier='" . htmlspecialchars($row['SupplierName']) . "' " . 
                                                        $disabled . " " . $selected . ">" . 
                                                        htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . 
                                                        ($row['Stock'] <= 0 ? ' - Out of Stock' : '') . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted mt-2" id="partsTotal"></small>
                            </div>

                            <!-- Costs Row -->
                            <div class="row mt-3">
                                <!-- Total Costs -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="totalCosts">Total Costs </label>
                                        <input type="number" name="totalCosts" id="totalCosts" class="form-control" step="0.01" min="0" 
                                               value="<?php 
                                                    // Calculate total from parts and drive costs
                                                    $totalPartsCost = 0;
                                                    foreach ($parts as $part) {
                                                        $totalPartsCost += $part['SellPrice'] * $part['PiecesSold'];
                                                    }
                                                    $totalCost = $totalPartsCost + $jobCard['DriveCosts'];
                                                    echo htmlspecialchars($totalCost);
                                               ?>" readonly style="background-color: #e9ecef;">
            <small class="form-text text-muted">(excl. VAT)</small>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($jobCard['PhoneNumber']); ?>" readonly style="background-color: #e9ecef;">
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label for="location">Location of Visit <span class="text-danger">*</span></label>
                                <input type="text" name="location" id="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($jobCard['Location']); ?>" required>
                            </div>

                            <!-- Job Description -->
                            <div class="form-group">
                                <label for="jobDescription">Job Description by Customer <span class="text-danger">*</span></label>
                                <textarea name="jobDescription" id="jobDescription" class="form-control" rows="3" required><?php echo htmlspecialchars($jobCard['JobDesc']); ?></textarea>
                            </div>

                            <!-- Rides -->
                            <div class="form-group">
                                <label for="rides">Rides</label>
                                <input type="number" name="rides" id="rides" class="form-control" min="0" 
                                       value="<?php echo htmlspecialchars($jobCard['Rides']); ?>">
                            </div>

                            <!-- Drive Costs -->
                            <div class="form-group">
                                <label for="driveCosts">Drive Costs</label>
                                <input type="number" name="driveCosts" id="driveCosts" class="form-control" step="0.01" min="0" 
                                       value="<?php echo htmlspecialchars($jobCard['DriveCosts']); ?>">
                            </div>

                            <!-- Photos -->
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
                                    <?php 
                                    if (!empty($jobCard['Photo'])) {
                                        $photos = json_decode($jobCard['Photo'], true);
                                        if (is_array($photos)) {
                                            foreach ($photos as $photo): ?>
                                                <div class="col-md-3 mb-3">
                                                    <div class="position-relative">
                                                        <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                                             class="img-fluid rounded" alt="Job photo"
                                                             style="max-height: 150px; cursor: pointer;">
                                                        <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                                                style="top: 5px!important; right: 5px!important;padding: 0.25rem 0.5rem!important;" 
                                                                onclick="deletePhoto(this, '<?php echo htmlspecialchars($photo); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <input type="hidden" name="existing_photos[]" value="<?php echo htmlspecialchars($photo); ?>">
                                                    </div>
                                                </div>
                                            <?php endforeach;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="btngroup text-center mt-4">
                        <button type="submit" class="btn btn-primary" title="Save Changes">Save <i class="fas fa-save"></i></button>
                        <button type="button" class="btn btn-secondary" onclick="openForm('<?php echo $jobId; ?>')" title="Cancel">Cancel</button>
                    </div>
                </form>
            </div>
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

    <!-- Delete Photo Confirmation Modal -->
    <div class="modal fade" id="deletePhotoModal" tabindex="-1" role="dialog" aria-labelledby="deletePhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePhotoModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="deletePhotoModalMessage">Are you sure you want to delete this photo?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeletePhotoBtn" onclick="confirmDeletePhoto()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize removedPhotos array
        window.removedPhotos = [];
        
        // Initialize photo preview functionality
        $(document).ready(function() {
            // Handle form submission
            $('#editJobCardForm').on('submit', function(e) {
                e.preventDefault();
                
                // Create FormData object
                var formData = new FormData(this);
                
                // Add removed photos to form data
                if (window.removedPhotos.length > 0) {
                    formData.append('removed_photos', JSON.stringify(window.removedPhotos));
                }
            });

        });

         

    </script>
</body>
</html>