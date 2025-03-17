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
</head>
<body>
    <div class="pc-container3">
        <div class="form-container">
            <h2 class="mb-4">Job Card</h2>
            <form action="../controllers/add_job_card_controller.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Customer Selection -->
                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <select name="customer" id="customer" class="form-control" required>
                                <option value="">Select Customer</option>
                                <?php
                                require_once '../config/db_connection.php';
                                $sql = "SELECT CustomerID, CONCAT(FirstName, ' ', LastName) as CustomerName FROM Customers";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                while ($row = $stmt->fetch()) {
                                    echo "<option value='" . $row['CustomerID'] . "'>" . htmlspecialchars($row['CustomerName']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Registration Plate -->
                        <div class="form-group">
                            <label for="registration">Registration Plate</label>
                            <input type="text" name="registration" id="registration" class="form-control" required>
                        </div>

                        <!-- Date of Call -->
                        <div class="form-group">
                            <label for="dateCall">Date of Call</label>
                            <input type="date" name="dateCall" id="dateCall" class="form-control" required>
                        </div>

                        <!-- Job Report -->
                        <div class="form-group">
                            <label for="jobReport">Job Report</label>
                            <textarea name="jobReport" id="jobReport" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Job End Date -->
                        <div class="form-group">
                            <label for="jobEndDate">Job End Date</label>
                            <input type="date" name="jobEndDate" id="jobEndDate" class="form-control">
                        </div>

                        <!-- Car Brand and Model -->
                        <div class="form-group">
                            <label for="carBrandModel">Car Brand and Model</label>
                            <select name="carBrandModel" id="carBrandModel" class="form-control">
                                <option value="">Select Car Brand and Model</option>
                            </select>
                        </div>

                        <!-- Parts Used/Replaced -->
                        <div class="form-group">
                            <label for="partsUsed">Parts Used/Replaced</label>
                            <div id="partsContainer">
                                <div class="input-group mb-2">
                                    <select name="parts[]" class="form-control part-select">
                                        <option value="">Select Part</option>
                                        <?php
                                        $sql = "SELECT PartID, PartDesc, SellPrice FROM Parts ORDER BY SellPrice ASC";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        while ($row = $stmt->fetch()) {
                                            echo "<option value='" . $row['PartID'] . "'>" . htmlspecialchars($row['PartDesc']) . " (" . number_format($row['SellPrice'], 2) . " €)</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="addPartField()">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" id="phone" class="form-control">
                        </div>

                        <!-- Location of Visit -->
                        <div class="form-group">
                            <label for="location">Location of Visit</label>
                            <input type="text" name="location" id="location" class="form-control" required>
                        </div>

                        <!-- Job Description -->
                        <div class="form-group">
                            <label for="jobDescription">Job Description by Customer</label>
                            <textarea name="jobDescription" id="jobDescription" class="form-control" rows="3" required></textarea>
                        </div>

                        <!-- Job Start Date -->
                        <div class="form-group">
                            <label for="jobStartDate">Job Start Date</label>
                            <input type="date" name="jobStartDate" id="jobStartDate" class="form-control">
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

                        <!-- Price for Each Part -->
                        <div class="form-group">
                            <label for="partPrices">Price for Each Part</label>
                            <div id="partPricesContainer">
                                <div class="input-group mb-2">
                                    <input type="number" name="partPrices[]" class="form-control" step="0.01" min="0" placeholder="Price">
                                </div>
                            </div>
                        </div>

                        <!-- Total Costs -->
                        <div class="form-group">
                            <label for="totalCosts">Total Costs</label>
                            <input type="number" name="totalCosts" id="totalCosts" class="form-control" step="0.01" min="0">
                        </div>

                        <!-- Photos of damage -->
                        <div class="form-group">
                            <label for="photos">Photos of damage</label>
                            <input type="file" name="photos[]" id="photos" class="form-control-file" multiple accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
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
                    $stmt = $pdo->prepare("SELECT PartID, PartDesc, SellPrice FROM Parts ORDER BY SellPrice ASC");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                        echo "<option value='" . $row['PartID'] . "'>" . htmlspecialchars($row['PartDesc']) . " (" . number_format($row['SellPrice'], 2) . " €)</option>";
                    }
                    ?>
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">-</button>
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
                    <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">-</button>
                </div>
            `;
            priceContainer.appendChild(newPriceField);
        }

        // Function to update part price when a part is selected
        function updatePartPrice(selectElement) {
            const partId = selectElement.value;
            if (!partId) return;
            
            // Find the corresponding price input
            const partIndex = Array.from(document.querySelectorAll('.part-select')).indexOf(selectElement);
            const priceInputs = document.getElementsByName('partPrices[]');
            const priceInput = priceInputs[partIndex];
            
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

        // Function to calculate total costs
        function calculateTotal() {
            const driveCosts = parseFloat(document.getElementById('driveCosts').value) || 0;
            const partPrices = Array.from(document.getElementsByName('partPrices[]'))
                .map(input => parseFloat(input.value) || 0)
                .reduce((sum, price) => sum + price, 0);
            
            const total = driveCosts + partPrices;
            
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
        document.getElementById('partPricesContainer').addEventListener('input', calculateTotal);

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