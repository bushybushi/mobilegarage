
<?php
// Include user access protection
require_once '../../UserAccess/protect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- JavaScript dependencies - Load in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom utility scripts -->
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/form-functions.js"></script>
    <script src="../assets/js/car-functions.js"></script>
    <script src="../assets/js/customer-functions.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/print.js"></script>
    
    <!-- Form submission and validation script -->
    <script>
        $(document).ready(function() {
            // Handle form submission via AJAX
            $('#customerForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showSuccessMessage(response.message || 'Customer added successfully!');
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            showErrorMessage(response.message || 'An error occurred');
                        }
                    },
                    error: function(xhr, status, error) {
                        showErrorMessage('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : error));
                    }
                });
            });
        });

        // Function to display success messages
        function showSuccessMessage(message) {
            // Get the success alert element and update its content
            const successAlert = document.getElementById('successAlert');
            document.getElementById('successMessage').textContent = message;
            
            // Show the alert with animation
            successAlert.classList.add('show');
            successAlert.style.display = 'block';
            successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Hide the alert after 3 seconds
            setTimeout(() => {
                successAlert.classList.remove('show');
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 150);
            }, 3000);
        }

        // Function to display error messages
        function showErrorMessage(message) {
            // Create error popup element
            const popup = document.createElement('div');
            popup.className = 'alert alert-danger alert-dismissible fade show';
            popup.innerHTML = `
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>${message}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            // Remove any existing error messages
            document.querySelectorAll('.alert-danger').forEach(el => el.remove());
            
            // Insert the new error message before the form
            document.querySelector('.form-container').insertBefore(popup, document.querySelector('form'));
            popup.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto-dismiss the error message after 5 seconds
            setTimeout(() => {
                $(popup).alert('close');
            }, 5000);
        }
    </script>
</head>
<body>
<?php
// Display error message if it exists in session
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">';
    echo $_SESSION['error_message'];
    echo '</div>';
    
    // Clear the error message after displaying it
    unset($_SESSION['error_message']);
}
?>

<!-- Main Form Container -->
<div class="form-container">
    <!-- Top Navigation Bar -->
    <div class="top-container d-flex justify-content-between align-items-center">
        <!-- Back Button -->
        <a href="javascript:void(0);" onclick="window.location.href='customer_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <!-- Page Title -->
        <div class="flex-grow-1 text-center">
            <h2 class="mb-0">Add Customer</h2>
        </div>
        <!-- Spacer for alignment -->
        <div style="width: 30px;"></div>
    </div>

    <!-- Success Message Alert (Hidden by default) -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Customer Information Form -->
    <form id="customerForm" action="../controllers/add_customer_controller.php" method="POST">
        <!-- Personal Information Section -->
        <div class="form-group">
            <div class="row">
                <!-- First Name Input -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['firstName']) ? $sanitizedInputs['firstName'] : ''); ?>" required>
                    <?php if (isset($errors['firstName'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['firstName']); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Surname Input -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="surname">Surname *</label>
                    <input type="text" id="surname" name="surname" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['surname']) ? $sanitizedInputs['surname'] : ''); ?>" required>
                    <?php if (isset($errors['surname'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['surname']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Company and Address Section -->
        <div class="form-group">
            <div class="row">
                <!-- Company Name Input -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['companyName']) ? $sanitizedInputs['companyName'] : ''); ?>">
                    <?php if (isset($errors['companyName'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['companyName']); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Address Input with Add Button -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="address[]">Address *</label>
                    <div id="addresses">
                        <div class="input-group">
                            <input type="text" id="address[]" name="address[]" value="" class="form-control" required style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="form-group">
            <div class="row">
                <!-- Phone Numbers Input -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="phoneNumber[]">Phone Number *</label>
                    <div id="phoneNumbers">
                        <div class="input-group">
                            <input type="tel" id="phoneNumber[]" name="phoneNumber[]" value="" class="form-control" required style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Email Address Input with Add Button -->
                <div class="col-12 col-md-6 mb-3">
                    <label for="emailAddress[]">Email Address</label>
                    <div id="emailAddresses">
                        <div class="input-group">
                            <input type="email" id="emailAddress[]" name="emailAddress[]" value="" class="form-control" style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Information Section -->
        <div id="carsContainer">
            <h3>Cars*</h3>
            <!-- Dynamic car forms container -->
            <button type="button" class="btn btn-success mt-2" id="addCarBtn" onclick="openAddCarModal()">Add Car
            <i class="fa fa-plus"></i></button>
        </div>

        <!-- Form Submission Section -->
        <div class="btngroup text-center mt-4">
            <button type="submit" class="btn btn-primary">Save <i class="fas fa-save"></i></button>
        </div>
     </div>
    </form>
</div>

<!-- Add Car Modal - Modal dialog for adding new vehicles to customer profile -->
<div class="modal fade" id="addCarModal" tabindex="-1" role="dialog" aria-labelledby="addCarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow rounded">
            <!-- Modal Header with Title and Close Button -->
            <div class="modal-header rounded-top">
                <h5 class="modal-title" id="addCarModalLabel">
                    <i class="fas fa-car mr-2"></i>Add New Car
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body with Car Information Form -->
            <div class="modal-body p-4">
                <form id="addCarForm" method="POST">
                    <input type="hidden" name="isTemporary" value="true">
                    <!-- Basic Vehicle Information Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- Vehicle Brand Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="brand">Brand *</label>
                                <input type="text" id="brand" class="form-control" name="brand" required>
                            </div>
                            <!-- Vehicle Model Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="model">Model *</label>
                                <input type="text" id="model" class="form-control" name="model" required>
                            </div>
                        </div>
                    </div>
                    <!-- Vehicle Identification Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- License Plate Number Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="licenseNr">License Plate *</label>
                                <input type="text" id="licenseNr" class="form-control" name="licenseNr" required>
                            </div>
                            <!-- Vehicle Identification Number (VIN) Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="vin">Vehicle Identification Number (VIN) *</label>
                                <input type="text" id="vin" class="form-control" name="vin" required>
                            </div>
                        </div>
                    </div>
                    <!-- Vehicle Manufacturing Details Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- Manufacturing Date Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="manuDate">Manufacture Date *</label>
                                <input type="date" id="manuDate" class="form-control" name="manuDate" required>
                            </div>
                            <!-- Fuel Type Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="fuel">Fuel Type *</label>
                                <input type="text" id="fuel" class="form-control" name="fuel" required>
                            </div>
                        </div>
                    </div>
                    <!-- Vehicle Performance Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- Power Output Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="kwHorse">Kw/Horsepower</label>
                                <input type="number" step="0.1" id="kwHorse" class="form-control" name="kwHorse">
                            </div>
                            <!-- Engine Type Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="engine">Engine Type *</label>
                                <input type="text" id="engine" class="form-control" name="engine" required>
                            </div>
                        </div>
                    </div>
                    <!-- Vehicle Usage and Appearance Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- Mileage Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="kmMiles">Km/Miles *</label>
                                <input type="number" step="0.1" id="kmMiles" class="form-control" name="kmMiles" required>
                            </div>
                            <!-- Vehicle Color Input -->
                            <div class="col-12 col-md-6 mb-3">
                                <label for="color">Color *</label>
                                <input type="text" id="color" class="form-control" name="color" required>
                            </div>
                        </div>
                    </div>
                    <!-- Additional Information Section -->
                    <div class="form-group">
                        <div class="row">
                            <!-- Comments/Notes Input -->
                            <div class="col-12 mb-3">
                                <label for="comments">Comments</label>
                                <textarea id="comments" class="form-control" name="comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Modal Footer with Action Buttons -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCar()">Add Car</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Car Confirmation Modal - Modal dialog for confirming vehicle deletion -->
<div class="modal fade" id="deleteCarModal" tabindex="-1" role="dialog" aria-labelledby="deleteCarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Modal Header with Title and Close Button -->
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCarModalLabel">Delete Car</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body with Confirmation Message -->
            <div class="modal-body">
                <p id="deleteCarModalMessage">Are you sure you want to delete this car?</p>
            </div>
            <!-- Modal Footer with Action Buttons -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" id="noDeleteCarBtn" style="display: none;">No</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCarBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
.error, .no-results {
    padding: 8px;
    color: #666;
    font-style: italic;
}

.part-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.part-info {
    flex-grow: 1;
}

.part-desc {
    font-weight: 500;
    margin-bottom: 5px;
}

.part-details {
    display: flex;
    gap: 20px;
    color: #666;
    font-size: 0.9rem;
}

.remove-part {
    margin-left: 10px;
}

.modal-content {
    border-radius: 10px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 10px 10px;
}

#successAlert {
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
    background-color: #d4edda;
    color: #155724;
    padding: 15px 20px;
}

#successAlert .close {
    color: #155724;
    opacity: 0.5;
    text-shadow: none;
}

#successAlert .close:hover {
    opacity: 1;
}

#successAlert i {
    font-size: 1.2rem;
}

#successAlert.show {
    display: block !important;
}

.car-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.car-info {
    flex-grow: 1;
}

.car-desc {
    font-weight: 500;
    margin-bottom: 5px;
}

.car-details {
    display: flex;
    gap: 20px;
    color: #666;
    font-size: 0.9rem;
}

.remove-car {
    margin-left: 10px;
}

.modal-lg {
    max-width: 800px;
}

.modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.info-view-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-view-row:hover {
    background-color: #f8f9fa;
}

.info-view-info {
    flex-grow: 1;
}

.info-view-desc {
    font-weight: 500;
    margin-bottom: 5px;
}

.info-view-details {
    display: flex;
    gap: 20px;
    color: #666;
    font-size: 0.9rem;
}

/* Mobile-specific styles */
@media (max-width: 768px) {
    .top-container h2 {
        font-size: 1.2rem;
    }
}
</style>
</body>
</html>
