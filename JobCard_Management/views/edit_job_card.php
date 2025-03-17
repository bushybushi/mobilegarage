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
</head>

<body>
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

        <form action="../controllers/update_job_card_controller.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($jobId); ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <!-- Customer Selection -->
                    <div class="form-group">
                        <label for="customer">Customer</label>
                        <select name="customer" id="customer" class="form-control" required>
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
                        <textarea name="jobReport" id="jobReport" class="form-control" rows="3"><?php 
                            echo htmlspecialchars($jobCard['JobReport']); 
                        ?></textarea>
                    </div>

                    <!-- Job End Date -->
                    <div class="form-group">
                        <label for="jobEndDate">Job End Date</label>
                        <input type="date" name="jobEndDate" id="jobEndDate" class="form-control" 
                               value="<?php echo !empty($jobCard['DateFinish']) ? htmlspecialchars($jobCard['DateFinish']) : ''; ?>">
                    </div>

                    <!-- Car Brand and Model -->
                    <div class="form-group">
                        <label for="carBrandModel">Car Brand and Model</label>
                        <select name="carBrandModel" id="carBrandModel" class="form-control">
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
                                echo "<option value='" . htmlspecialchars($value) . "' " . $selected . ">" . 
                                     htmlspecialchars($value) . " (" . htmlspecialchars($row['LicenseNr']) . ")</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

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
                        <textarea name="jobDescription" id="jobDescription" class="form-control" rows="3" required><?php 
                            echo htmlspecialchars($jobCard['JobDesc']); 
                        ?></textarea>
                    </div>

                    <!-- Job Start Date -->
                    <div class="form-group">
                        <label for="jobStartDate">Job Start Date</label>
                        <input type="date" name="jobStartDate" id="jobStartDate" class="form-control" 
                               value="<?php echo !empty($jobCard['DateStart']) ? htmlspecialchars($jobCard['DateStart']) : ''; ?>">
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
                    
                    <!-- Total Costs -->
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
                               ?>">
                    </div>
                </div>
            </div>

            <!-- Parts Used -->
            <div class="form-group">
                <label>Parts Used</label>
                <div id="partsContainer">
                    <?php foreach ($parts as $part): ?>
                    <div class="input-group mb-2">
                        <select name="parts[]" class="form-control part-select" onchange="updatePartPrice(this)">
                            <option value="">Select Part</option>
                            <?php
                            $partsSql = "SELECT PartID, PartDesc, SellPrice FROM Parts ORDER BY SellPrice ASC";
                            $partsStmt = $pdo->prepare($partsSql);
                            $partsStmt->execute();
                            while ($row = $partsStmt->fetch()) {
                                $selected = ($row['PartID'] == $part['PartID']) ? 'selected' : '';
                                echo "<option value='" . $row['PartID'] . "' " . $selected . ">" . 
                                     htmlspecialchars($row['PartDesc']) . " (" . number_format($row['SellPrice'], 2) . " €)</option>";
                            }
                            ?>
                        </select>
                        <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>" placeholder="Price">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger" onclick="this.closest('.input-group').remove(); calculateTotal();">-</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <button type="button" class="btn btn-primary" onclick="addPartField()">Add Part</button>
                </div>
            </div>

            <!-- Photos -->
            <div class="form-group">
                <label for="photos">Photos of damage</label>
                <?php if (!empty($jobCard['Photo'])): ?>
                <div class="row mb-3">
                    <?php 
                    $photos = json_decode($jobCard['Photo'], true);
                    foreach ($photos as $photo): ?>
                        <div class="col-md-3 mb-3">
                            <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                 class="img-fluid rounded" alt="Damage photo">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <input type="file" name="photos[]" id="photos" class="form-control-file" multiple accept="image/*">
            </div>

            <div class="btngroup">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <script>
        function addPartField() {
            const container = document.getElementById('partsContainer');
            const newField = document.createElement('div');
            newField.className = 'input-group mb-2';
            newField.innerHTML = `
                <select name="parts[]" class="form-control part-select" onchange="updatePartPrice(this)">
                    <option value="">Select Part</option>
                    <?php
                    $partsStmt = $pdo->prepare("SELECT PartID, PartDesc, SellPrice FROM Parts ORDER BY SellPrice ASC");
                    $partsStmt->execute();
                    while ($row = $partsStmt->fetch()) {
                        echo "<option value='" . $row['PartID'] . "'>" . 
                             htmlspecialchars($row['PartDesc']) . " (" . number_format($row['SellPrice'], 2) . " €)</option>";
                    }
                    ?>
                </select>
                <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" placeholder="Price">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="this.closest('.input-group').remove(); calculateTotal();">-</button>
                </div>
            `;
            container.insertBefore(newField, container.lastElementChild);
        }

        // Function to update part price when a part is selected
        function updatePartPrice(selectElement) {
            const partId = selectElement.value;
            if (!partId) return;
            
            // Find the corresponding price input
            const priceInput = selectElement.nextElementSibling;
            
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

        // Add event listener to existing part selects
        document.addEventListener('DOMContentLoaded', function() {
            const partSelects = document.querySelectorAll('.part-select');
            partSelects.forEach(select => {
                select.addEventListener('change', function() {
                    updatePartPrice(this);
                });
            });
        });

        // Calculate total costs
        function calculateTotal() {
            const driveCosts = parseFloat(document.getElementById('driveCosts').value) || 0;
            let partPricesTotal = 0;
            
            // Get all part price inputs
            const partPrices = document.querySelectorAll('input[name="partPrices[]"]');
            partPrices.forEach(function(input) {
                partPricesTotal += parseFloat(input.value) || 0;
            });
            
            const total = driveCosts + partPricesTotal;
            
            // Only update the calculated total display
            const totalCostsField = document.getElementById('totalCosts');
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
        document.getElementById('partsContainer').addEventListener('input', calculateTotal);
        
        // Calculate total on page load
        document.addEventListener('DOMContentLoaded', calculateTotal);

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
                                carSelect.appendChild(option);
                            });
                        }
                    });
            }
        });
    </script>
</body>
</html> 