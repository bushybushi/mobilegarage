<?php
require_once '../../UserAccess/protect.php';
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Include the customer model
require_once '../models/customer_model.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Get the customer ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Create customer object and get all related data
$customer = new customer($id);
$old_customer = $customer->getBasicInfo();
$old_address = $customer->getAddresses();
$old_phone = $customer->getPhoneNumbers();
$old_email = $customer->getEmailAddresses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    

    <!-- JavaScript dependencies - Load in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/form-functions.js"></script>
    <script src="../assets/js/car-functions.js"></script>
    <script src="../assets/js/customer-functions.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <!-- Add a hidden input field for the customer ID -->
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" id="customerId" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Script to handle form submission -->
    <script>
        $(document).ready(function() {
            // Initialize car row click handler
            handleCarRowClick();
            
            $('#editCustomerForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
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
                            document.querySelector('.form-container').insertBefore(successAlert, document.querySelector('.form-container').firstChild);
                            successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            // Redirect after showing the message
                            setTimeout(() => {
                                openForm('<?php echo $id; ?>');
                            }, 2000);
                        } else {
                            // Show error message
                            const errorAlert = document.createElement('div');
                            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                            errorAlert.innerHTML = `
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span>${response.message || 'Error updating customer'}</span>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            `;
                            document.querySelector('.form-container').insertBefore(errorAlert, document.querySelector('.form-container').firstChild);
                            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>Error updating customer: ${error}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        `;
                        document.querySelector('.form-container').insertBefore(errorAlert, document.querySelector('.form-container').firstChild);
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            });
        });
    </script>
</head>
<body>

<!-- Main Content Container -->
<div class="form-container">
    <!-- Top Navigation Bar with Customer Name and Action Buttons -->
    <div class="top-container d-flex justify-content-between align-items-center">
        <!-- Back Arrow Button -->
        <a href="javascript:void(0);" onclick="openForm('<?php echo $id; ?>')" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <!-- Customer Name Display -->
        <div class="flex-grow-1 text-center">
            <h5 class="mb-0"><?php echo htmlspecialchars($old_customer['FirstName']) . ' ' . htmlspecialchars($old_customer['LastName']); ?></h5>
        </div>
    </div>

    <div class="section-header">
        <i class="fas fa-user"></i>
        <span>Customer Information</span>
    </div>

    <!-- Customer Edit Form -->
    <form id="editCustomerForm" action="../controllers/update_customer_controller.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
        
        <!-- Name Fields -->
        <div class="form-group">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="firstName">First Name</label>
                    <input type="text" name="firstName" class="form-control" value="<?php echo htmlspecialchars($old_customer['FirstName'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="surname">Surname</label>
                    <input type="text" name="surname" class="form-control" value="<?php echo htmlspecialchars($old_customer['LastName'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
            </div>
        </div>

        <!-- Company and Address Fields -->
        <div class="form-group">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="companyName">Company Name</label>
                    <input type="text" name="companyName" class="form-control" value="<?php echo htmlspecialchars($old_customer['Company'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <?php 
                    // Address Fields Section
                    echo '<div id="addresses">';
                    if (!empty($old_address)) {
                        // Display existing addresses with plus/minus buttons
                        foreach ($old_address as $row)
                        echo '<div class="form-group">
                            <label for="address">Address</label>
                            <div class="input-group">
                                <input type="text" name="address[]" class="form-control" value="' . htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8') . '" style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';
                    } else {
                        // Display empty address field if no addresses exist
                        echo '<div class="form-group">
                            <label for="address">Address</label>
                            <div class="input-group">
                                <input type="text" name="address[]" class="form-control" style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';    
                    }
                    echo '</div>';
                    ?>
                </div>
            </div>
        </div>

        <!-- Phone and Email Fields -->
        <div class="form-group">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <?php 
                    echo '<div id="phoneNumbers">';
                    if (!empty($old_phone)) {
                        // Display existing phone numbers with plus/minus buttons
                        foreach ($old_phone as $row)
                        echo '<div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <div class="input-group">
                                <input type="tel" name="phoneNumber[]" class="form-control" value="' . htmlspecialchars($row['Nr'], ENT_QUOTES, 'UTF-8') . '" required style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';
                    } else {
                        // Display empty phone field if no phone numbers exist
                        echo '<div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <div class="input-group">
                                <input type="tel" name="phoneNumber[]" class="form-control" required style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';    
                    }
                    echo '</div>';
                    ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <?php 
                    echo '<div id="emailAddresses">';
                    if (!empty($old_email)) {
                        // Display existing email addresses with plus/minus buttons
                        foreach ($old_email as $row)
                        echo '<div class="form-group">
                            <label for="emailAddress">Email Address</label>
                            <div class="input-group">
                                <input type="email" name="emailAddress[]" class="form-control" value="' . htmlspecialchars($row['Emails'], ENT_QUOTES, 'UTF-8') . '" style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';
                    } else {
                        // Display empty email field if no email addresses exist
                        echo '<div class="form-group">
                            <label for="emailAddress">Email Address</label>
                            <div class="input-group">
                                <input type="email" name="emailAddress[]" class="form-control" style="padding-right: 80px;">
                                <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';    
                    }
                    echo '</div>';
                    ?>
                </div>
            </div>
        </div>

        <!-- Cars Section -->
        <div class="cars-section">
            <div class="section-header">
                <i class="fas fa-car"></i>
                <span>Associated Cars</span>
            </div>
            <div class="cars-container">
                <?php
                $cars_query = "SELECT c.* FROM cars c 
                               INNER JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
                               WHERE ca.CustomerID = :id";
                $stmt = $pdo->prepare($cars_query);
                $stmt->execute(['id' => $id]);
                
                if ($stmt->rowCount() > 0) {
                    while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="car-row" id="car-' . htmlspecialchars($car['LicenseNr']) . '" data-license="' . htmlspecialchars($car['LicenseNr']) . '">';
                        echo '<div class="car-info">';
                        echo '<div class="car-desc">' . htmlspecialchars($car['Brand']) . ' ' . htmlspecialchars($car['Model']) . ' (' . htmlspecialchars($car['LicenseNr']) . ')</div>';
                        echo '<div class="car-details">';
                        echo '<span>VIN: ' . htmlspecialchars($car['VIN']) . '</span>';
                        echo '<span>Fuel: ' . htmlspecialchars($car['Fuel']) . '</span>';
                        echo '<span>Engine: ' . htmlspecialchars($car['Engine']) . '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="car-actions">';
                        echo '<button type="button" onclick="editCar(\'' . htmlspecialchars($car['LicenseNr']) . '\')" class="btn btn-sm btn-primary edit-car"><i class="fas fa-edit"></i> Edit</button>';
                        echo '<button type="button" onclick="deleteCar(\'' . htmlspecialchars($car['LicenseNr']) . '\')" class="btn btn-sm btn-danger remove-car"><i class="fas fa-trash"></i> Delete</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-info">No cars associated with this customer.</div>';
                }
                ?>
            </div>
        </div>
        <button id="addCarBtn" type="button" class="btn btn-success ml-auto" onclick="openAddCarModal()">
                <i class="fas fa-plus"></i> Add Car
            </button>
        <!-- Submit Button -->
        <div class="btngroup text-center mt-4">
            <button type="submit" class="btn btn-primary">Save <i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-secondary" onclick="openForm('<?php echo $id; ?>')">Cancel</button>
        </div>
    </form>
</div>

<!-- Delete Car Confirmation Modal -->
<div class="modal fade" id="deleteCarModal" tabindex="-1" role="dialog" aria-labelledby="deleteCarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCarModalLabel">Delete Car</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteCarModalMessage">Are you sure you want to delete this car?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" id="noDeleteCarBtn" style="display: none;">No</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCarBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Car Edit Modal -->
<div class="modal fade" id="carEditModal" tabindex="-1" role="dialog" aria-labelledby="carEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow rounded">
            <div class="modal-header rounded-top">
                <h5 class="modal-title" id="carEditModalLabel">
                    <i class="fas fa-car-side mr-2"></i>Edit Car Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form id="editCarForm" method="POST" action="../controllers/update_car_controller.php">
                    <input type="hidden" id="editOldLicenseNr" name="oldLicenseNr">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editBrand">Brand *</label>
                                <input type="text" id="editBrand" class="form-control" name="brand" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editModel">Model *</label>
                                <input type="text" id="editModel" class="form-control" name="model" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editLicenseNr">License Plate *</label>
                                <input type="text" id="editLicenseNr" class="form-control" name="licenseNr" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editVIN">Vehicle Identification Number (VIN) *</label>
                                <input type="text" id="editVIN" class="form-control" name="vin" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editManuDate">Manufacture Date *</label>
                                <input type="date" id="editManuDate" class="form-control" name="manuDate" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editFuel">Fuel Type *</label>
                                <input type="text" id="editFuel" class="form-control" name="fuel" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editKwHorse">Kw/Horsepower</label>
                                <input type="number" step="0.1" id="editKwHorse" class="form-control" name="kwHorse">
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editEngine">Engine Type *</label>
                                <input type="text" id="editEngine" class="form-control" name="engine" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editKmMiles">Km/Miles *</label>
                                <input type="number" step="0.1" id="editKmMiles" class="form-control" name="kmMiles" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="editColor">Color *</label>
                                <input type="text" id="editColor" class="form-control" name="color" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="editComments">Comments</label>
                                <textarea id="editComments" class="form-control" name="comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCarEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Car Modal -->
<div class="modal fade" id="addCarModal" tabindex="-1" role="dialog" aria-labelledby="addCarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow rounded">
            <div class="modal-header rounded-top">
                <h5 class="modal-title" id="addCarModalLabel">
                    <i class="fas fa-car mr-2"></i>Add New Car
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form id="addCarForm" method="POST" action="../controllers/add_car_controller.php">
                    <input type="hidden" name="customerId" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="isTemporary" value="true">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="brand">Brand *</label>
                                <input type="text" id="brand" class="form-control" name="brand" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="model">Model *</label>
                                <input type="text" id="model" class="form-control" name="model" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="licenseNr">License Plate *</label>
                                <input type="text" id="licenseNr" class="form-control" name="licenseNr" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="vin">Vehicle Identification Number (VIN) *</label>
                                <input type="text" id="vin" class="form-control" name="vin" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="manuDate">Manufacture Date *</label>
                                <input type="date" id="manuDate" class="form-control" name="manuDate" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="fuel">Fuel Type *</label>
                                <input type="text" id="fuel" class="form-control" name="fuel" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="kwHorse">Kw/Horsepower</label>
                                <input type="number" step="0.1" id="kwHorse" class="form-control" name="kwHorse">
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="engine">Engine Type *</label>
                                <input type="text" id="engine" class="form-control" name="engine" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="kmMiles">Km/Miles *</label>
                                <input type="number" step="0.1" id="kmMiles" class="form-control" name="kmMiles" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label for="color">Color *</label>
                                <input type="text" id="color" class="form-control" name="color" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="comments">Comments</label>
                                <textarea id="comments" class="form-control" name="comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCar()">Add Car</button>
            </div>
        </div>
    </div>
</div>


<style>
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
}

.print-btn i {
    font-size: 20px;
}

button:focus {
    outline: none;
    outline: none;
}
.btn:focus {
    outline: none;
    box-shadow: none;
}

.section-header {
    background: #f8f9fa;
    padding: 12px 20px;
    margin: 25px 0 20px 0;
    color: #495057;
    border-radius: 6px;
    display: flex;
    align-items: center;
    border: 1px solid #dee2e6;
}

.section-header i {
    margin-right: 12px;
    font-size: 1.1rem;
    color: #6c757d;
}

.section-header span {
    font-size: 1.1rem;
    font-weight: 500;
    letter-spacing: 0.3px;
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

.car-actions {
    display: flex;
    gap: 10px;
}

.car-actions button {
    padding: 5px;
}

.car-actions button:hover {
    transform: scale(1.1);
}

.address-group, .phone-group, .email-group {
    margin-bottom: 1rem;
}

.address-group:last-child, .phone-group:last-child, .email-group:last-child {
    margin-bottom: 0;
}

.rounded {
    border-radius: 1rem !important;
}

#addCarBtn {
    padding: 8px 20px;
    font-size: 1em;
    border-radius: 4px;
    margin-top: 10px;
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