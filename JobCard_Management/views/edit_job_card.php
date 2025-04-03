<?php
require_once '../config/db_connection.php';

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get job card details
$sql = "SELECT j.*, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, c.CustomerID,
        car.LicenseNr, car.Brand, car.Model, pn.Nr as PhoneNumber
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN CarAssoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN PhoneNumbers pn ON c.CustomerID = pn.CustomerID
        WHERE j.JobID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$jobId]);
$jobCard = $stmt->fetch();

// Get parts used in this job
$partsSql = "SELECT jp.*, p.PartDesc
             FROM JobCardParts jp
             JOIN Parts p ON jp.PartID = p.PartID
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
                <a href="javascript:void(0);" onclick="window.location.href='job_cards_main.php'" class="back-arrow">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Edit Job Card</h5>
                </div>
                <div class="d-flex justify-content-end">
                </div>
            </div>

            <form action="../controllers/update_job_card_controller.php" method="POST" enctype="multipart/form-data">
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
                                $customerSql = "SELECT CustomerID, CONCAT(FirstName, ' ', LastName) as CustomerName FROM Customers";
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
                                // Get cars for this customer
                                $carSql = "SELECT c.* 
                                          FROM Cars c
                                          JOIN CarAssoc ca ON c.LicenseNr = ca.LicenseNr
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
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="addPartField()">Add Part</button>
                            </div>
                            <div id="partsContainer">
                                <?php foreach ($parts as $part): ?>
                                <div class="input-group mt-2">
                                    <div class="position-relative" style="flex: 1;">
                                        <input type="text" class="form-control part-search" placeholder="Search part..." value="<?php echo htmlspecialchars($part['PartDesc']); ?>">
                                        <div class="list-group mt-1 position-absolute" style="width: 100%; top: 38px; z-index: 1000;"></div>
                                        <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                            <button type="button" class="btn btn-link text-danger" onclick="removePart(this)" style="padding: 0;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="number" name="partQuantities[]" class="form-control ml-2" min="1" value="<?php echo htmlspecialchars($part['PiecesSold']); ?>" style="max-width: 80px;">
                                    <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>" style="max-width: 100px;">
                                    <input type="hidden" name="parts[]" value="<?php echo htmlspecialchars($part['PartID']); ?>">
                                    <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                                        <option value="">Select Part</option>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT p.PartID, p.PartDesc, p.SellPrice, p.Stock, p.DateCreated, s.Name as SupplierName 
                                                               FROM Parts p 
                                                               LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                                                               ORDER BY p.SellPrice ASC");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch()) {
                                            $selected = ($row['PartID'] == $part['PartID']) ? 'selected' : '';
                                            // Only disable if stock is 0 AND it's not the currently selected part
                                            $disabled = ($row['Stock'] <= 0 && $row['PartID'] != $part['PartID']) ? 'disabled' : '';
                                            echo "<option value='" . $row['PartID'] . "' 
                                                data-stock='" . $row['Stock'] . "' 
                                                data-price='" . $row['SellPrice'] . "' 
                                                data-date-created='" . $row['DateCreated'] . "' 
                                                data-supplier='" . htmlspecialchars($row['SupplierName']) . "' " . 
                                                $selected . " " . $disabled . ">" . 
                                                htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . 
                                                (($row['Stock'] <= 0 && $row['PartID'] != $part['PartID']) ? ' - Out of Stock' : '') . "</option>";
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
                                    <label for="totalCosts">Total Costs</label>
                                    <input type="number" name="totalCosts" id="totalCosts" class="form-control" step="0.01" min="0" 
                                           value="<?php 
                                                // Calculate total from parts and drive costs
                                                $totalPartsCost = 0;
                                                foreach ($parts as $part) {
                                                    $totalPartsCost += $part['PricePerPiece'] * $part['PiecesSold'];
                                                }
                                                $totalCost = $totalPartsCost + $jobCard['DriveCosts'];
                                                echo htmlspecialchars($totalCost);
                                           ?>" readonly style="background-color: #e9ecef;">
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
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="addPhotoField()">Add Photos</button>
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
                                                            style="top: 5px; right: 5px;" 
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

                <div class="btngroup">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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

    <script>
        // Initialize form functionality when the page loads
        $(document).ready(function() {
            // Initialize part search for all existing part fields
            const partSearchInputs = document.querySelectorAll('.part-search');
            partSearchInputs.forEach(input => {
                const selectElement = input.closest('.input-group').querySelector('.part-select');
                setupPartSearch(input, selectElement);
            });
        });

        // Function to setup part search functionality
        function setupPartSearch(searchInput, selectElement) {
            const searchResultsDiv = searchInput.closest('.position-relative').querySelector('.list-group');
            const partField = searchInput.closest('.input-group');
            const priceInput = partField.querySelector('input[name="partPrices[]"]');
            const quantityInput = partField.querySelector('input[name="partQuantities[]"]');
            const hiddenInput = partField.querySelector('input[name="parts[]"]');
            
            searchInput.addEventListener('keyup', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length > 0) {
                    // Get all options from the select
                    const options = Array.from(selectElement.options).slice(1); // Skip the first "Select Part" option
                    
                    // Filter options based on search query
                    const filteredOptions = options.filter(option => {
                        return option.text.toLowerCase().includes(query);
                    });
                    
                    // Create search results
                    searchResultsDiv.innerHTML = '';
                    if (filteredOptions.length > 0) {
                        // Group parts by description to find duplicates
                        const partsByDesc = {};
                        filteredOptions.forEach(option => {
                            const desc = option.text.split(' (Stock:')[0]; // Get description without stock info
                            if (!partsByDesc[desc]) {
                                partsByDesc[desc] = [];
                            }
                            partsByDesc[desc].push(option);
                        });

                        // Create result items
                        filteredOptions.forEach(option => {
                            const resultItem = document.createElement('a');
                            resultItem.href = '#';
                            resultItem.className = 'list-group-item list-group-item-action';
                            
                            // Get description without stock info
                            const desc = option.text.split(' (Stock:')[0];
                            const stock = option.text.match(/Stock: (\d+)/)[1];
                            const dateCreated = option.dataset.dateCreated || 'N/A';
                            const supplier = option.dataset.supplier || 'N/A';
                            
                            // Check if this is a duplicate part
                            const isDuplicate = partsByDesc[desc].length > 1;
                            
                            // Create the display text with date created and supplier if it's a duplicate
                            const displayText = isDuplicate ? 
                                `${desc} (Stock: ${stock}) (Created: ${dateCreated}) (Supplier: ${supplier})` : 
                                option.text;
                            
                            resultItem.textContent = displayText;
                            resultItem.dataset.id = option.value;
                            resultItem.dataset.stock = option.dataset.stock;
                            resultItem.dataset.price = option.dataset.price;
                            
                            // Add visual indicator for duplicates
                            if (isDuplicate) {
                                resultItem.style.borderLeft = '4px solid #007bff';
                                resultItem.title = 'This part has duplicates. Date created and supplier are shown to distinguish between them.';
                            }
                            
                            // Check if this part is already added with stock of 1
                            const stockNum = parseInt(option.dataset.stock);
                            const partId = parseInt(option.value);
                            const currentPartId = hiddenInput ? parseInt(hiddenInput.value) : null;
                            
                            // If stock is 1 and it's already added, disable the option
                            if (stockNum === 1 && partId !== currentPartId) {
                                const existingPartInputs = document.querySelectorAll('input[name="parts[]"]');
                                let isAlreadyAdded = false;
                                existingPartInputs.forEach(input => {
                                    if (parseInt(input.value) === partId) {
                                        isAlreadyAdded = true;
                                    }
                                });
                                
                                if (isAlreadyAdded) {
                                    resultItem.className += ' disabled text-muted';
                                    resultItem.style.pointerEvents = 'none';
                                    resultItem.title = 'This part is already added and has only 1 in stock';
                                }
                            }
                            // If stock is 0 or less and it's not a currently selected part, disable it
                            else if (stockNum <= 0 && partId !== currentPartId) {
                                resultItem.className += ' disabled text-muted';
                                resultItem.style.pointerEvents = 'none';
                                resultItem.title = 'Out of stock';
                            }
                            
                            resultItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                
                                // Check if part is already added with stock of 1
                                const stock = parseInt(this.dataset.stock);
                                const partId = parseInt(this.dataset.id);
                                
                                if (stock === 1) {
                                    const existingPartInputs = document.querySelectorAll('input[name="parts[]"]');
                                    let isAlreadyAdded = false;
                                    existingPartInputs.forEach(input => {
                                        if (parseInt(input.value) === partId) {
                                            isAlreadyAdded = true;
                                        }
                                    });
                                    
                                    if (isAlreadyAdded) {
                                        alert('This part is already added and has only 1 in stock');
                                        return;
                                    }
                                }
                                
                                selectElement.value = this.dataset.id;
                                searchInput.value = this.textContent;
                                searchResultsDiv.innerHTML = '';
                                
                                // Update hidden input with selected part ID
                                if (hiddenInput) {
                                    hiddenInput.value = this.dataset.id;
                                }
                                
                                // Set the price input value
                                if (priceInput && this.dataset.price) {
                                    priceInput.value = this.dataset.price;
                                }

                                // Set max quantity based on stock
                                if (quantityInput && this.dataset.stock) {
                                    const stock = parseInt(this.dataset.stock);
                                    quantityInput.max = stock;
                                    quantityInput.title = `Maximum available: ${stock}`;
                                    
                                    // If current quantity is more than stock, adjust it
                                    if (parseInt(quantityInput.value) > stock) {
                                        quantityInput.value = stock;
                                    }
                                }
                                
                                // Trigger the change event to update price and stock
                                const changeEvent = new Event('change');
                                selectElement.dispatchEvent(changeEvent);
                                
                                // Trigger total calculation
                                calculateTotal();
                            });
                            
                            searchResultsDiv.appendChild(resultItem);
                        });
                    } else {
                        const noResults = document.createElement('div');
                        noResults.className = 'list-group-item text-muted';
                        noResults.textContent = 'No parts found';
                        searchResultsDiv.appendChild(noResults);
                    }
                } else {
                    searchResultsDiv.innerHTML = '';
                }
            });
            
            // Add event listener to validate quantity against stock
            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    const max = parseInt(this.max) || 0;
                    const value = parseInt(this.value) || 0;
                    
                    if (value > max) {
                        alert(`Cannot exceed available stock of ${max} units`);
                        this.value = max;
                        calculateTotal();
                    }
                });
            }
            
            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
                    searchResultsDiv.innerHTML = '';
                }
            });
        }

        // Function to add new part field
        function addPartField() {
            const container = document.getElementById('partsContainer');
            
            const newField = document.createElement('div');
            newField.className = 'input-group mt-2';
            newField.innerHTML = `
                <div class="position-relative" style="flex: 1;">
                    <input type="text" class="form-control part-search" placeholder="Search part...">
                    <div class="list-group mt-1 position-absolute" style="width: 100%; top: 38px; z-index: 1000;"></div>
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link text-danger" onclick="removePart(this)" style="padding: 0;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <input type="number" name="partQuantities[]" class="form-control ml-2" min="1" value="1" style="max-width: 80px;" placeholder="Qty">
                <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" value="0.00" style="max-width: 100px;" placeholder="Price" onchange="formatPrice(this)">
                <input type="hidden" name="parts[]" value="">
                <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                    <option value="">Select Part</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT p.PartID, p.PartDesc, p.SellPrice, p.Stock, p.DateCreated, s.Name as SupplierName 
                                           FROM Parts p 
                                           LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                                           ORDER BY p.SellPrice ASC");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                        $disabled = ($row['Stock'] <= 0) ? 'disabled' : '';
                        echo "<option value='" . $row['PartID'] . "' 
                            data-stock='" . $row['Stock'] . "' 
                            data-price='" . $row['SellPrice'] . "' 
                            data-date-created='" . $row['DateCreated'] . "' 
                            data-supplier='" . htmlspecialchars($row['SupplierName']) . "' " . 
                            $disabled . ">" . 
                            htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . ($row['Stock'] <= 0 ? ' - Out of Stock' : '') . "</option>";
                    }
                    ?>
                </select>
            `;
            container.appendChild(newField);

            // Setup part search for the new field
            setupPartSearch(newField.querySelector('.part-search'), newField.querySelector('.part-select'));
        }

        // Function to format price with 2 decimal places
        function formatPrice(input) {
            const value = parseFloat(input.value) || 0;
            input.value = value.toFixed(2);
        }

        // Function to remove part field
        function removePart(button) {
            const partField = button.closest('.input-group');
            if (partField) {
                if (confirm('Are you sure you want to remove this part?')) {
                    partField.remove();
                    calculateTotal();
                }
            }
        }

        // Function to update part price and stock when a part is selected
        function updatePartPrice(selectElement) {
            const partId = selectElement.value;
            if (!partId) return;
            
            // Find the corresponding part field container
            const partField = selectElement.closest('.input-group');
            const priceInput = partField.querySelector('input[name="partPrices[]"]');
            const quantityInput = partField.querySelector('input[name="partQuantities[]"]');
            const hiddenInput = partField.querySelector('input[name="parts[]"]');
            
            // Get stock and price from selected option
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const stock = parseInt(selectedOption.dataset.stock);
            const price = parseFloat(selectedOption.dataset.price);
            
            // Update quantity input max attribute and title
            if (quantityInput) {
                quantityInput.max = stock;
                quantityInput.title = `Maximum available: ${stock}`;
                
                // If current quantity is more than stock, adjust it
                const currentQty = parseInt(quantityInput.value) || 0;
                if (currentQty > stock) {
                    alert(`Cannot exceed available stock of ${stock} units`);
                    quantityInput.value = stock;
                }
            }
            
            // Update price input with 2 decimal places
            if (priceInput && price) {
                priceInput.value = price.toFixed(2);
            }
            
            // Update hidden input
            if (hiddenInput) {
                hiddenInput.value = partId;
            }
            
            calculateTotal();
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

        // Auto-populate phone when customer is selected
        document.getElementById('customer').addEventListener('change', function() {
            const customerId = this.value;
            if (customerId) {
                // Get customer phone
                fetch(`../controllers/get_customer_phone.php?id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.phone) {
                            document.getElementById('phone').value = data.phone;
                        }
                    });
                
                // Get customer cars
                fetch(`../controllers/get_customer_cars.php?id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        const carSelect = document.getElementById('carBrandModel');
                        // Clear previous options
                        carSelect.innerHTML = '<option value="">Select Car Brand and Model</option>';
                        
                        if (data.cars && data.cars.length > 0) {
                            data.cars.forEach(car => {
                                const option = document.createElement('option');
                                option.value = car.Brand + ' ' + car.Model;
                                option.textContent = car.Brand + ' ' + car.Model + ' (' + car.LicenseNr + ')';
                                option.dataset.license = car.LicenseNr;
                                carSelect.appendChild(option);
                            });
                        }
                    });
            }
        });

        // Function to update registration plate when car is selected
        function updateRegistrationPlate(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption && selectedOption.dataset.license) {
                document.getElementById('registration').value = selectedOption.dataset.license;
            }
        }

        // Photo preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            setupPhotoPreview();
        });

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

        // Function to add new photo field
        function addPhotoField() {
            const container = document.getElementById('photosContainer');
            
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
            setupPhotoPreview(newField.querySelector('.photo-input'));
        }

        // Function to remove new photo field with confirmation
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
                // If there's no preview (just the input field), remove it directly
                inputGroup.remove();
            }
        }

        // Function to delete an existing photo
        function deletePhoto(button, photoName) {
            if (confirm('Are you sure you want to delete this photo?')) {
                // Add the photo name to a hidden input for tracking deleted photos
                const deletedPhotosInput = document.querySelector('input[name="removed_photos"]') || (() => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'removed_photos';
                    document.querySelector('form').appendChild(input);
                    return input;
                })();
                
                // Add to the list of removed photos
                const removedPhotos = deletedPhotosInput.value ? JSON.parse(deletedPhotosInput.value) : [];
                removedPhotos.push(photoName);
                deletedPhotosInput.value = JSON.stringify(removedPhotos);
                
                // Remove the photo container from the display
                button.closest('.col-md-3').remove();
            }
        }

        // Setup photo preview functionality
        function setupPhotoPreview(element) {
            const inputs = element ? [element] : document.querySelectorAll('.photo-input');
            
            inputs.forEach(input => {
                if (input.hasPhotoListener) return; // Prevent duplicate listeners
                
                input.hasPhotoListener = true;
                input.addEventListener('change', function(event) {
                    const previewContainer = document.getElementById('photoPreviewContainer');
                    
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        if (!file.type.match('image.*')) {
                            return;
                        }
                        
                        // Create a unique ID for this preview
                        const previewId = 'preview-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                        this.dataset.previewId = previewId;
                        
                        // Remove old preview if exists
                        if (this.dataset.oldPreviewId) {
                            const oldPreview = document.getElementById(this.dataset.oldPreviewId);
                            if (oldPreview) oldPreview.remove();
                        }
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 mb-3';
                            col.id = previewId;
                            
                            const div = document.createElement('div');
                            div.className = 'position-relative';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-fluid rounded photo-preview';
                            img.style.cursor = 'pointer';
                            
                            // Add click event to open modal
                            img.addEventListener('click', function() {
                                document.getElementById('modalImage').src = this.src;
                                $('#photoModal').modal('show');
                            });
                            
                            const deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'btn btn-danger btn-sm position-absolute';
                            deleteBtn.style.cssText = 'top: 5px; right: 5px;';
                            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                            deleteBtn.onclick = function() {
                                if (confirm('Are you sure you want to delete this photo?')) {
                                    // Clear the file input
                                    input.value = '';
                                    // Remove the preview
                                    col.remove();
                                }
                            };
                            
                            div.appendChild(img);
                            div.appendChild(deleteBtn);
                            col.appendChild(div);
                            previewContainer.appendChild(col);
                        };
                        
                        reader.readAsDataURL(file);
                        this.dataset.oldPreviewId = previewId;
                    }
                });
            });
        }

        // Add event listener for part quantities and prices
        document.addEventListener('DOMContentLoaded', function() {
            const partsContainer = document.getElementById('partsContainer');
            
            // Listen for changes in quantities and prices in the parts container
            partsContainer.addEventListener('input', function(e) {
                if (e.target.matches('input[name="partQuantities[]"]') || e.target.matches('input[name="partPrices[]"]')) {
                    calculateTotal();
                }
            });
            
            // Initial calculation
            calculateTotal();
        });
    </script>
</body>
</html>
