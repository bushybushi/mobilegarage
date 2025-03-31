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
                <h5>Job Card</h5>
            </div>
            <div style="width: 30px;"></div>
        </div>
            <form action="../controllers/add_job_card_controller.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Customer Selection -->
                        <div class="form-group">
                            <label for="customer">Customer <span class="text-danger">*</span></label>
                            <input type="text" id="customerSearch" class="form-control" placeholder="Search customer...">
                            <div id="customerSearchResults" class="list-group mt-1"></div>
                            <select name="customer" id="customer" class="form-control mt-2" required style="display: none;">
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

                        <!-- Car Details -->
                        <div class="form-group">
                            <label for="carDetails">Car Brand and Model <span class="text-danger">*</span></label>
                            <select name="carDetails" id="carDetails" class="form-control" onchange="updateRegistrationPlate(this)" required>
                                <option value="">Select Car</option>
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
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="addPartField()">Add Part</button>
                            </div>
                            <div id="partsContainer">
                                <div class="input-group">
                                    <div class="position-relative" style="flex: 1;">
                                        <input type="text" class="form-control part-search" placeholder="Search part...">
                                        <div class="list-group mt-1 position-absolute" style="width: 100%; top: 38px; z-index: 1000;"></div>
                                    </div>
                                    <input type="number" name="partQuantities[]" class="form-control ml-2" min="1" value="1" style="max-width: 80px;" placeholder="Qty">
                                    <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" style="max-width: 100px;" placeholder="Price">
                                    <input type="hidden" name="parts[]" value="">
                                    <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                                        <option value="">Select Part</option>
                                        <?php
                                        $sql = "SELECT PartID, PartDesc, SellPrice, Stock FROM Parts ORDER BY SellPrice ASC";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        while ($row = $stmt->fetch()) {
                                            $disabled = ($row['Stock'] <= 0) ? 'disabled' : '';
                                            echo "<option value='" . $row['PartID'] . "' data-stock='" . $row['Stock'] . "' data-price='" . $row['SellPrice'] . "' " . $disabled . ">" . 
                                                 htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . ($row['Stock'] <= 0 ? ' - Out of Stock' : '') . "</option>";
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
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="addPhotoField()">Add Photos</button>
                            </div>
                            <div id="photosContainer">
                                <div class="input-group">
                                    <div class="position-relative" style="flex: 1;">
                                        <input type="file" name="photos[]" class="form-control photo-input" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div id="photoPreviewContainer" class="mt-2 row"></div>
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
                <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" style="max-width: 100px;" placeholder="Price">
                <input type="hidden" name="parts[]" value="">
                <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
                    <option value="">Select Part</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT PartID, PartDesc, SellPrice, Stock FROM Parts ORDER BY SellPrice ASC");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                        $disabled = ($row['Stock'] <= 0) ? 'disabled' : '';
                        echo "<option value='" . $row['PartID'] . "' data-stock='" . $row['Stock'] . "' data-price='" . $row['SellPrice'] . "' " . $disabled . ">" . 
                             htmlspecialchars($row['PartDesc']) . " (Stock: " . $row['Stock'] . ")" . ($row['Stock'] <= 0 ? ' - Out of Stock' : '') . "</option>";
                    }
                    ?>
                </select>
            `;
            container.appendChild(newField);

            // Setup part search for the new field
            setupPartSearch(newField.querySelector('.part-search'), newField.querySelector('.part-select'));
        }
        
        // Function to remove part and its corresponding price field
        function removePart(button) {
            const partField = button.closest('.input-group');
            if (partField) {
                partField.remove();
                // Update total after removing the part
                calculateTotal();
            }
        }

        // Function to update part price and stock limit when a part is selected
        function updatePartPrice(selectElement) {
            const partId = selectElement.value;
            if (!partId) return;
            
            // Find the corresponding part field container
            const partField = selectElement.closest('.input-group');
            
            // Find the price and quantity inputs directly within the current part field
            const priceInput = partField.querySelector('input[name="partPrices[]"]');
            const quantityInput = partField.querySelector('input[name="partQuantities[]"]');
            
            // Get stock from selected option
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const stock = selectedOption.dataset.stock;
            
            // Update quantity input max attribute
            if (quantityInput) {
                quantityInput.max = stock;
            }
            
            // Fetch part information including price
            fetch(`../controllers/get_part_info.php?id=${partId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.part && priceInput) {
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

        // When customer is selected, populate their cars
        document.getElementById('customer').addEventListener('change', function() {
            const customerId = this.value;
            if (!customerId) return;
            
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
                    const carSelect = document.getElementById('carDetails');
                    carSelect.innerHTML = '<option value="">Select Car</option>';
                        
                        if (data.cars && data.cars.length > 0) {
                            data.cars.forEach(car => {
                                const option = document.createElement('option');
                            option.value = car.LicenseNr;
                            option.textContent = `${car.Brand} ${car.Model} (${car.LicenseNr})`;
                            option.dataset.license = car.LicenseNr;
                                carSelect.appendChild(option);
                            });
                    }
                });
        });

        // Function to update registration plate when car is selected
        function updateRegistrationPlate(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption && selectedOption.dataset.license) {
                document.getElementById('registration').value = selectedOption.dataset.license;
            }
        }
    </script>
    
    <script>
        // Photo preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            setupPhotoPreview();
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
                            img.className = 'img-fluid rounded';
                            img.style.maxHeight = '150px';
                            img.style.cursor = 'pointer'; // Add pointer cursor
                            
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
    </script>

    <script>
        // Customer search functionality
        const customerSearchInput = document.getElementById('customerSearch');
        const customerSelect = document.getElementById('customer');
        const customerSearchResults = document.getElementById('customerSearchResults');
        
        customerSearchInput.addEventListener('keyup', function() {
            const query = this.value.trim();
            if (query.length > 0) {
                // Get all options from the select
                const options = Array.from(customerSelect.options).slice(1); // Skip the first "Select Customer" option
                
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
    </script>

    <script>
        // Initialize form functionality when the page loads
        $(document).ready(function() {
            // Initialize part search for the first part field
            const initialPartSearchInput = document.querySelector('.part-search');
            const initialPartSelect = document.querySelector('.part-select');
            
            if (initialPartSearchInput && initialPartSelect) {
                setupPartSearch(initialPartSearchInput, initialPartSelect);
            }
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
                        filteredOptions.forEach(option => {
                            const resultItem = document.createElement('a');
                            resultItem.href = '#';
                            resultItem.className = 'list-group-item list-group-item-action';
                            resultItem.textContent = option.text;
                            resultItem.dataset.id = option.value;
                            resultItem.dataset.stock = option.dataset.stock;
                            resultItem.dataset.price = option.dataset.price;
                            
                            // Check if this part is already added with stock of 1
                            const stock = parseInt(option.dataset.stock);
                            const partId = parseInt(option.value);
                            const currentPartId = hiddenInput ? parseInt(hiddenInput.value) : null;
                            
                            // If stock is 1 and it's already added, disable the option
                            if (stock === 1 && partId !== currentPartId) {
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
                            else if (stock <= 0 && partId !== currentPartId) {
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
    </script>

    <script>
        // Set modal image source when a photo is clicked
        document.addEventListener('click', function(e) {
            if (e.target && e.target.matches('img[src^="../uploads/job_photos/"]')) {
                e.preventDefault();
                document.getElementById('modalImage').src = e.target.src;
                $('#photoModal').modal('show');
            }
        });

        // Toggle maximize/minimize
        let isFullscreen = false;
        document.getElementById('toggleSize').addEventListener('click', function() {
            const modal = document.getElementById('photoModal');
            const icon = document.getElementById('sizeIcon');
            
            if (isFullscreen) {
                modal.classList.remove('modal-fullscreen');
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            } else {
                modal.classList.add('modal-fullscreen');
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
            }
            
            isFullscreen = !isFullscreen;
        });

        // Reset modal size when closing
        $('#photoModal').on('hidden.bs.modal', function () {
            const modal = document.getElementById('photoModal');
            const icon = document.getElementById('sizeIcon');
            modal.classList.remove('modal-fullscreen');
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
            isFullscreen = false;
        });
    </script>
</body>
</html> 
