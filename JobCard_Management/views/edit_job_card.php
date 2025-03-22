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
    </style>
</head>

<body>
    <div class="pc-container3">
        <div class="form-container">
            <div class="top-container d-flex justify-content-between align-items-center">
                <a href="javascript:void(0);" onclick="window.location.href='job_card_view.php?id=<?php echo $jobId; ?>'" class="back-arrow">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Edit Job Card #<?php echo htmlspecialchars($jobId); ?></h5>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="btngroup">
                        <button href="#" type="button" class="btn btn-success mr-2">Print</button>
                        <button href="#" type="button" class="btn btn-primary">Create Invoice</button>
                    </div>
                </div>
            </div>

            <form action="../controllers/update_job_card_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $jobId; ?>">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Customer Selection -->
                        <div class="form-group">
                            <label for="customer">Customer</label>
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
                            <label for="carBrandModel">Car Brand and Model</label>
                            <select name="carBrandModel" id="carBrandModel" class="form-control" onchange="updateRegistrationPlate(this)">
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
                                   value="<?php echo htmlspecialchars($jobCard['LicenseNr']); ?>" required>
                        </div>

                        <!-- Date of Call -->
                        <div class="form-group">
                            <label for="dateCall">Date of Call</label>
                            <input type="date" name="dateCall" id="dateCall" class="form-control" 
                                   value="<?php echo htmlspecialchars($jobCard['DateCall']); ?>" required>
                        </div>

                        <!-- Job Report -->
                        <div class="form-group">
                            <label for="jobReport">Job Report</label>
                            <textarea name="jobReport" id="jobReport" class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobReport']); ?></textarea>
                        </div>

                        <!-- Job End Date -->
                        <div class="form-group">
                            <label for="jobEndDate">Job End Date</label>
                            <input type="date" name="jobEndDate" id="jobEndDate" class="form-control" 
                                   value="<?php echo htmlspecialchars($jobCard['DateFinish']); ?>">
                        </div>

                        <!-- Parts Used -->
                        <div class="form-group">
                            <label>Parts Used/Replaced</label>
                            <div id="partsContainer">
                                <?php foreach ($parts as $part): ?>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control part-search" placeholder="Search part..." value="<?php echo htmlspecialchars($part['PartDesc']); ?>">
                                    <input type="number" name="partQuantities[]" class="form-control" min="1" max="<?php 
                                        // Get current stock for this part
                                        $stockSql = "SELECT Stock FROM Parts WHERE PartID = ?";
                                        $stockStmt = $pdo->prepare($stockSql);
                                        $stockStmt->execute([$part['PartID']]);
                                        $stock = $stockStmt->fetchColumn();
                                        echo $stock;
                                    ?>" value="<?php echo htmlspecialchars($part['PiecesSold']); ?>" style="max-width: 80px;" placeholder="Qty">
                                    <div class="input-group-append">
                                        <?php if ($part === reset($parts)): ?>
                                        <button type="button" class="btn btn-primary" onclick="addPartField()">+</button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-danger" onclick="removePart(this)">-</button>
                                        <?php endif; ?>
                                    </div>
                                    <select name="parts[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                                        <option value="">Select Part</option>
                                        <?php
                                        $partsSql = "SELECT PartID, PartDesc, SellPrice, Stock FROM Parts ORDER BY SellPrice ASC";
                                        $partsStmt = $pdo->prepare($partsSql);
                                        $partsStmt->execute();
                                        while ($row = $partsStmt->fetch()) {
                                            $selected = ($row['PartID'] == $part['PartID']) ? 'selected' : '';
                                            echo "<option value='" . $row['PartID'] . "' data-stock='" . $row['Stock'] . "' data-price='" . $row['SellPrice'] . "' " . $selected . ">" . 
                                                 htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($parts)): ?>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control part-search" placeholder="Search part...">
                                    <input type="number" name="partQuantities[]" class="form-control" min="1" value="1" style="max-width: 80px;" placeholder="Qty">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="addPartField()">+</button>
                                    </div>
                                    <select name="parts[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                                        <option value="">Select Part</option>
                                        <?php
                                        $partsSql = "SELECT PartID, PartDesc, SellPrice, Stock FROM Parts ORDER BY SellPrice ASC";
                                        $partsStmt = $pdo->prepare($partsSql);
                                        $partsStmt->execute();
                                        while ($row = $partsStmt->fetch()) {
                                            echo "<option value='" . $row['PartID'] . "' data-stock='" . $row['Stock'] . "' data-price='" . $row['SellPrice'] . "'>" . 
                                                 htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Costs Row -->
                        <div class="row mt-3">
                            <!-- Additional Costs -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="additionalCosts">Additional Costs</label>
                                    <input type="number" name="additionalCosts" id="additionalCosts" class="form-control" step="0.01" min="0" 
                                           value="<?php echo htmlspecialchars($jobCard['AdditionalCosts'] ?? 0); ?>">
                                </div>
                            </div>
                            <!-- Total Costs -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="totalCosts">Total Costs</label>
                                    <input type="number" name="totalCosts" id="totalCosts" class="form-control" step="0.01" min="0" 
                                           value="<?php 
                                                // Calculate total from parts and drive costs
                                                $totalPartsCost = 0;
                                                foreach ($parts as $part) {
                                                    $totalPartsCost += $part['PricePerPiece'] * $part['PiecesSold'];
                                                }
                                                $additionalCosts = $jobCard['AdditionalCosts'] ?? 0;
                                                $totalCost = $totalPartsCost + $jobCard['DriveCosts'] + $additionalCosts;
                                                echo htmlspecialchars($totalCost);
                                           ?>">
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
                                   value="<?php echo htmlspecialchars($jobCard['PhoneNumber']); ?>">
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label for="location">Location of Visit</label>
                            <input type="text" name="location" id="location" class="form-control" 
                                   value="<?php echo htmlspecialchars($jobCard['Location']); ?>" required>
                        </div>

                        <!-- Job Description -->
                        <div class="form-group">
                            <label for="jobDescription">Job Description by Customer</label>
                            <textarea name="jobDescription" id="jobDescription" class="form-control" rows="3" required><?php echo htmlspecialchars($jobCard['JobDesc']); ?></textarea>
                        </div>

                        <!-- Job Start Date -->
                        <div class="form-group">
                            <label for="jobStartDate">Job Start Date</label>
                            <input type="date" name="jobStartDate" id="jobStartDate" class="form-control" 
                                   value="<?php echo htmlspecialchars($jobCard['DateStart']); ?>">
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

                        <!-- Price for Each Part -->
                        <div class="form-group">
                            <label for="partPrices">Price for Each Part</label>
                            <div id="partPricesContainer">
                                <?php foreach ($parts as $part): ?>
                                <div class="input-group mb-2">
                                    <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" 
                                           value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>" placeholder="Price">
                                    <?php if ($part !== reset($parts)): ?>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger" onclick="removePart(this)">-</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($parts)): ?>
                                <div class="input-group mb-2">
                                    <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" placeholder="Price">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Photos -->
                        <div class="form-group">
                            <label>Photos</label>
                            <div id="photosContainer">
                                <div class="input-group mb-2">
                                    <input type="file" name="photos[]" class="form-control-file photo-input" accept="image/*">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="addPhotoField()">+</button>
                                    </div>
                                </div>
                            </div>
                            <div id="photoPreviewContainer" class="mt-2 row">
                                <?php 
                                if (!empty($jobCard['Photo'])) {
                                    $photos = json_decode($jobCard['Photo'], true);
                                    foreach ($photos as $photo): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="position-relative">
                                                <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                                     class="img-fluid rounded" alt="Job photo">
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

    <script>
        // Function to add new part field
        function addPartField() {
            const container = document.getElementById('partsContainer');
            const partFields = container.getElementsByClassName('input-group').length;
            
            const newField = document.createElement('div');
            newField.className = 'input-group mb-2';
            newField.innerHTML = `
                <select name="parts[]" class="form-control part-select" onchange="updatePartPrice(this)">
                    <option value="">Select Part</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT PartID, PartDesc, SellPrice, Stock FROM Parts ORDER BY SellPrice ASC");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                        echo "<option value='" . $row['PartID'] . "' data-stock='" . $row['Stock'] . "'>" . 
                             htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")</option>";
                    }
                    ?>
                </select>
                <input type="number" name="partQuantities[]" class="form-control" min="1" value="1" style="max-width: 80px;" placeholder="Qty" onchange="calculateTotal()">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removePart(this)">-</button>
                </div>
            `;
            container.appendChild(newField);

            // Add corresponding price field
            const priceContainer = document.getElementById('partPricesContainer');
            const newPriceField = document.createElement('div');
            newPriceField.className = 'input-group mb-2';
            newPriceField.innerHTML = `
                <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" placeholder="Price">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removePart(this)">-</button>
                </div>
            `;
            priceContainer.appendChild(newPriceField);
            
            // Store references to link the fields
            newField.dataset.priceField = priceContainer.children.length - 1;
            newPriceField.dataset.partField = container.children.length - 1;
        }
        
        // Function to remove part and its corresponding price field
        function removePart(button) {
            const partField = button.closest('.input-group');
            const priceFields = document.getElementById('partPricesContainer').children;
            const partFields = document.getElementById('partsContainer').children;
            
            // Find index of the current field
            let index = Array.from(partFields).indexOf(partField);
            if (index === -1) {
                // If not found in part fields, check price fields
                index = Array.from(priceFields).indexOf(partField);
                if (index !== -1) {
                    // Remove corresponding part field
                    partFields[index].remove();
                }
            } else {
                // Remove corresponding price field
                priceFields[index].remove();
            }
            
            // Remove this field
            partField.remove();
            
            // Update total
            calculateTotal();
        }

        // Function to update part price and stock limit when a part is selected
        function updatePartPrice(selectElement) {
            const partId = selectElement.value;
            if (!partId) return;
            
            // Find the corresponding price input and quantity input
            const quantityInput = selectElement.nextElementSibling;
            const priceInput = document.getElementsByName('partPrices[]')[Array.from(document.getElementsByName('parts[]')).indexOf(selectElement)];
            
            // Get stock from selected option
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const stock = selectedOption.dataset.stock;
            
            // Update quantity input max attribute
            quantityInput.max = stock;
            
            // Fetch part information including price
            fetch(`../controllers/get_part_info.php?id=${partId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.part) {
                        priceInput.value = data.part.SellPrice || 0;
                        calculateTotal();
                    }
                })
                .catch(error => console.error('Error fetching part info:', error));
        }

        // Add event listener to validate quantity against stock
        document.addEventListener('DOMContentLoaded', function() {
            const partsContainer = document.getElementById('partsContainer');
            
            partsContainer.addEventListener('input', function(e) {
                if (e.target.matches('input[name="partQuantities[]"]')) {
                    const input = e.target;
                    const max = parseInt(input.max);
                    const value = parseInt(input.value);
                    
                    if (value > max) {
                        alert('Cannot exceed available stock of ' + max + ' units');
                        input.value = max;
                    }
                    calculateTotal();
                }
            });
        });

        // Function to calculate total costs
        function calculateTotal() {
            const driveCosts = parseFloat(document.getElementById('driveCosts').value) || 0;
            const additionalCosts = parseFloat(document.getElementById('additionalCosts').value) || 0;
            let partPricesTotal = 0;
            
            // Get all part prices and quantities
            const partPrices = document.getElementsByName('partPrices[]');
            const partQuantities = document.getElementsByName('partQuantities[]');
            
            for (let i = 0; i < partPrices.length; i++) {
                const price = parseFloat(partPrices[i].value) || 0;
                const quantity = parseInt(partQuantities[i]?.value) || 1;
                partPricesTotal += price * quantity;
            }
            
            const total = driveCosts + partPricesTotal + additionalCosts;
            
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
        document.getElementById('additionalCosts').addEventListener('input', calculateTotal);
        
        // Add event listener for part quantities and prices
        document.addEventListener('DOMContentLoaded', function() {
            const partsContainer = document.getElementById('partsContainer');
            const partPricesContainer = document.getElementById('partPricesContainer');
            
            // Listen for changes in quantities and prices
            partsContainer.addEventListener('input', function(e) {
                if (e.target.matches('input[name="partQuantities[]"]')) {
                    calculateTotal();
                }
            });
            
            partPricesContainer.addEventListener('input', function(e) {
                if (e.target.matches('input[name="partPrices[]"]')) {
                    calculateTotal();
                }
            });
            
            // Initial calculation
            calculateTotal();
        });

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

        // Function to add new photo field
        function addPhotoField() {
            const container = document.getElementById('photosContainer');
            
            const newField = document.createElement('div');
            newField.className = 'input-group mb-2';
            newField.innerHTML = `
                <input type="file" name="photos[]" class="form-control-file photo-input" accept="image/*">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="this.closest('.input-group').remove()">-</button>
                </div>
            `;
            container.appendChild(newField);
            
            // Setup preview for the new field
            setupPhotoPreview(newField.querySelector('.photo-input'));
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
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-fluid rounded';
                            img.style.maxHeight = '150px';
                            
                            col.appendChild(img);
                            previewContainer.appendChild(col);
                        };
                        
                        reader.readAsDataURL(file);
                        this.dataset.oldPreviewId = previewId;
                    }
                });
            });
        }

        // Customer search functionality
        const customerSearchInput = document.getElementById('customerSearch');
        const customerSelect = document.getElementById('customer');
        const customerSearchResults = document.getElementById('customerSearchResults');
        
        customerSearchInput.addEventListener('keyup', function() {
            const query = this.value.trim();
            if (query.length > 0) {
                // Get all options from the select
                const options = Array.from(customerSelect.options);
                
                // Filter options based on search query
                const filteredOptions = options.filter(option => {
                    const names = option.text.toLowerCase().split(' ');
                    return names.some(name => name.startsWith(query.toLowerCase()));
                });
                
                // Create search results
                customerSearchResults.innerHTML = '';
                if (filteredOptions.length > 0) {
                    filteredOptions.forEach(option => {
                        const resultItem = document.createElement('a');
                        resultItem.href = '#';
                        resultItem.className = 'list-group-item list-group-item-action';
                        resultItem.textContent = option.text;
                        resultItem.dataset.id = option.value;
                        
                        resultItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            customerSelect.value = this.dataset.id;
                            customerSearchInput.value = this.textContent;
                            customerSearchResults.innerHTML = '';
                            
                            // Trigger the change event to load customer data
                            const changeEvent = new Event('change');
                            customerSelect.dispatchEvent(changeEvent);
                        });
                        
                        customerSearchResults.appendChild(resultItem);
                    });
                } else {
                    const noResults = document.createElement('div');
                    noResults.className = 'list-group-item text-muted';
                    noResults.textContent = 'No customers found';
                    customerSearchResults.appendChild(noResults);
                }
            } else {
                customerSearchResults.innerHTML = '';
            }
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!customerSearchInput.contains(e.target) && !customerSearchResults.contains(e.target)) {
                customerSearchResults.innerHTML = '';
            }
        });

        // Part search functionality
        function setupPartSearch(partSearchInput, partSelect) {
            const searchResultsId = 'partSearchResults-' + Math.random().toString(36).substr(2, 9);
            
            const searchResultsDiv = document.createElement('div');
            searchResultsDiv.id = searchResultsId;
            searchResultsDiv.className = 'list-group mt-1 position-absolute';
            searchResultsDiv.style.width = 'calc(100% - 130px)';
            searchResultsDiv.style.top = '38px';
            searchResultsDiv.style.zIndex = '1000';
            
            // Insert the search results div after the search input
            partSearchInput.parentNode.insertBefore(searchResultsDiv, partSearchInput.nextSibling);
            
            partSearchInput.addEventListener('keyup', function() {
                const query = this.value.trim();
                if (query.length > 0) {
                    // Get all options from the select
                    const options = Array.from(partSelect.options).slice(1); // Skip the first "Select Part" option
                    
                    // Filter options based on search query
                    const filteredOptions = options.filter(option => {
                        return option.text.toLowerCase().startsWith(query.toLowerCase());
                    });
                    
                    // Create search results
                    searchResultsDiv.innerHTML = '';
                    if (filteredOptions.length > 0) {
                        filteredOptions.forEach(option => {
                            const resultItem = document.createElement('a');
                            resultItem.href = '#';
                            resultItem.className = 'list-group-item list-group-item-action';
                            resultItem.textContent = option.text;
                            resultItem.dataset.id = option.value;
                            resultItem.dataset.stock = option.dataset.stock;
                            resultItem.dataset.price = option.dataset.price;
                            
                            resultItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                partSelect.value = this.dataset.id;
                                partSearchInput.value = this.textContent;
                                searchResultsDiv.innerHTML = '';
                                
                                // Update corresponding price field and max quantity
                                const quantityInput = partSearchInput.nextElementSibling;
                                quantityInput.max = this.dataset.stock;
                                
                                // Find the index of this part field
                                const partFields = document.querySelectorAll('#partsContainer .input-group');
                                const index = Array.from(partFields).indexOf(partSearchInput.closest('.input-group'));
                                
                                // Update price
                                const priceInputs = document.getElementsByName('partPrices[]');
                                if (priceInputs[index]) {
                                    priceInputs[index].value = this.dataset.price;
                                }
                                
                                // Update total
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
            
            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!partSearchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
                    searchResultsDiv.innerHTML = '';
                }
            });
            
            return searchResultsDiv;
        }
        
        // Setup part search for all part search fields
        document.addEventListener('DOMContentLoaded', function() {
            const partSearchInputs = document.querySelectorAll('.part-search');
            
            partSearchInputs.forEach(function(partSearchInput) {
                const partSelect = partSearchInput.parentNode.querySelector('.part-select');
                if (partSelect) {
                    setupPartSearch(partSearchInput, partSelect);
                }
            });
        });
        
        // Update the addPartField function to include part search
        const originalAddPartField = addPartField;
        addPartField = function() {
            originalAddPartField();
            
            // Get the new part field
            const container = document.getElementById('partsContainer');
            const newPartField = container.lastElementChild;
            
            // Replace the select with search input if not already there
            const oldSelect = newPartField.querySelector('select');
            if (oldSelect && !newPartField.querySelector('.part-search')) {
                const selectName = oldSelect.name;
                const selectId = 'parts-' + Math.random().toString(36).substr(2, 9);
                const selectClasses = oldSelect.className;
                const selectOnChange = oldSelect.getAttribute('onchange');
                const selectOptions = oldSelect.innerHTML;
                
                // Create new search input
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'form-control part-search';
                searchInput.placeholder = 'Search part...';
                
                // Create new hidden select
                const newSelect = document.createElement('select');
                newSelect.name = selectName;
                newSelect.id = selectId;
                newSelect.className = selectClasses;
                newSelect.setAttribute('onchange', selectOnChange);
                newSelect.style.display = 'none';
                newSelect.innerHTML = selectOptions;
                
                // Replace the old select with search input and hidden select
                oldSelect.parentNode.insertBefore(searchInput, oldSelect);
                oldSelect.parentNode.insertBefore(newSelect, oldSelect);
                oldSelect.remove();
                
                // Setup search functionality
                setupPartSearch(searchInput, newSelect);
            }
        };

        // Function to delete a photo
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
    </script>
</body>
</html> 
