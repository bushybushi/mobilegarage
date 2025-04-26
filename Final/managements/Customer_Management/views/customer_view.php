<?php
require_once '../../UserAccess/protect.php';

// Include database connection file and customer model
require_once '../config/db_connection.php';
require_once '../models/customer_model.php';

// Get customer ID from URL parameter
$customerId = $_GET['id'];

// Create customer object and get details
$customer = new customer($customerId);
$customerData = $customer->getCustomerDetails();


// SQL query to fetch customer details with related information
$sql = "SELECT c.CustomerID, c.FirstName, c.LastName, c.Company,
        GROUP_CONCAT(DISTINCT a.Address SEPARATOR '||') AS Addresses,
        GROUP_CONCAT(DISTINCT p.nr SEPARATOR ',') AS PhoneNumbers,
        GROUP_CONCAT(DISTINCT e.Emails SEPARATOR ',') AS EmailAddresses
        FROM customers c
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID
        LEFT JOIN phonenumbers p ON c.CustomerID = p.CustomerID
        LEFT JOIN emails e ON c.CustomerID = e.CustomerID
        WHERE c.CustomerID = :customerId
        GROUP BY c.CustomerID, c.FirstName, c.LastName, c.Company";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the customer data
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle NULL values for concatenated fields
if ($customer) {
    $customer['Addresses'] = $customer['Addresses'] ?? '';
    $customer['PhoneNumbers'] = $customer['PhoneNumbers'] ?? '';
    $customer['EmailAddresses'] = $customer['EmailAddresses'] ?? '';
    
    // Split the concatenated strings into arrays
    $addresses = !empty($customer['Addresses']) ? explode('||', $customer['Addresses']) : [];
    $phoneNumbers = !empty($customer['PhoneNumbers']) ? explode(',', $customer['PhoneNumbers']) : [];
    $emailAddresses = !empty($customer['EmailAddresses']) ? explode(',', $customer['EmailAddresses']) : [];
} else {
    // If no customer found, redirect to main page
    header("Location: customer_main.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer View</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- JavaScript dependencies - Load in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/customer-functions.js"></script>
    <script src="../assets/js/car-functions.js"></script>
    <script>
        // Initialize car row click handler when document is ready
        $(document).ready(function() {
            handleCarRowClick();
        });
    </script>
</head>

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
        color: #6c757d;
    }

    .print-btn i {
        font-size: 20px;
    }
    button:focus {
    outline: none;
    outline: none;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

    .car-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .car-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .badge-custom {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .rounded {
    border-radius: 1rem !important;
}
.rounded-top {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}
.card {
    position: relative;
    display: -ms-flexbox;
    display: flex
;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, .125);
    border-radius: 1rem;
}

.form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}

.form-control[readonly]:not(:last-child) {
    margin-bottom: 0.5rem;
}

    .alert {
        margin: 20px 0;
        border-radius: 8px;
        padding: 15px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .top-container h5 {
            font-size: 1.2rem;
        }
    }
</style>

<body>
    <!-- Main Content Container -->
    <div class="form-container">
        
        <!-- Hidden input for customer ID -->
        <input type="hidden" id="customerId" value="<?php echo htmlspecialchars($customerId); ?>">
        
        <!-- Top Navigation Bar with Customer Name and Action Buttons -->
        <div class="top-container d-flex justify-content-between align-items-center">
            <!-- Back Arrow Button -->
            <a href="javascript:void(0);" onclick="window.location.href='customer_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Customer Name Display -->
            <div class="flex-grow-1 text-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($customer['FirstName']) . ' ' . htmlspecialchars($customer['LastName']); ?></h5>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end">
                <div class="btngroup">
                    <button class="print-btn" onclick="printCustomerView(<?php echo $customerId; ?>)" title="Print Customer">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <i class="fas fa-user"></i>
            <span>Customer Information</span>
        </div>

        <!-- Customer View Form -->
        <div class="form-content">
            <!-- Disable form fields for view-only mode -->
            <fieldset disabled>
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="firstName">First Name</label>
                            <input type="text" id="disabledInput" name="firstName" class="form-control" value="<?php echo ($customer['FirstName']); ?>" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="surname">Surname</label>
                            <input type="text" id="surname" name="surname" class="form-control" value="<?php echo ($customer['LastName']); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="companyName">Company Name</label>
                            <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo ($customer['Company']); ?>" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="address">Addresses</label>
                            <?php
                            if (!empty($addresses)) {
                                foreach ($addresses as $address) {
                                    if (!empty($address)) {
                                        echo '<input type="text" id="address" name="address" class="form-control mb-2" value="' . htmlspecialchars($address) . '" readonly>';
                                    }
                                }
                            } else {
                                echo '<input type="text" id="address" name="address" class="form-control mb-2" value="" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="phoneNumber">Phone Numbers</label>
                            <?php
                            if (!empty($phoneNumbers)) {
                                foreach ($phoneNumbers as $phone) {
                                    if (!empty($phone)) {
                                        echo '<input type="tel" id="phoneNumber" name="phoneNumber" class="form-control mb-2" value="' . htmlspecialchars($phone) . '" readonly>';
                                    }
                                }
                            } else {
                                echo '<input type="tel" id="phoneNumber" name="phoneNumber" class="form-control mb-2" value="" readonly>';
                            }
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="emailAddress">Email Addresses</label>
                            <?php
                            if (!empty($emailAddresses)) {
                                foreach ($emailAddresses as $email) {
                                    if (!empty($email)) {
                                        echo '<input type="email" id="emailAddress" name="emailAddress" class="form-control mb-2" value="' . htmlspecialchars($email) . '" readonly>';
                                    }
                                }
                            } else {
                                echo '<input type="email" id="emailAddress" name="emailAddress" class="form-control mb-2" value="" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </fieldset>

           
        </div>
        <div class="form-group">
        <!-- Cars Section -->
        <div class="section-header">
            <i class="fas fa-car"></i>
            <span>Cars Information</span>
        </div>
        
            <div id="carsContainer" style="max-height: 300px; overflow-y: auto;">
                <?php
                // Fetch cars for this customer using carassoc table
                $carSql = "SELECT c.*, ca.CustomerID 
                           FROM cars c 
                           JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
                           WHERE ca.CustomerID = :customerId";
                $carStmt = $pdo->prepare($carSql);
                $carStmt->execute(['customerId' => $customerId]);
                $cars = $carStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($cars as $car) {
                    echo '<div class="info-view-row car-row" id="car-' . htmlspecialchars($car['LicenseNr']) . '" data-license="' . htmlspecialchars($car['LicenseNr']) . '">';
                    echo '<div class="info-view-info">';
                    echo '<div class="info-view-desc">' . htmlspecialchars($car['Brand']) . ' ' . htmlspecialchars($car['Model']) .' (' . htmlspecialchars($car['LicenseNr']) . ')</div>';
                    echo '<div class="info-view-details d-flex flex-column flex-md-row gap-2">';
                    echo '<span>VIN: ' . htmlspecialchars($car['VIN']) . '</span>';
                    echo '<span>Fuel: ' . htmlspecialchars($car['Fuel']) . '</span>';
                    echo '<span>Engine: ' . htmlspecialchars($car['Engine']) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>

            </div>

            <div class="btngroup text-center mt-4">
            <button class="btn btn-primary" onclick="loadEditForm('<?php echo $customerId; ?>')" title="Edit Customer"> Edit
                        <i class="fas fa-edit"></i>
                    </button>
            <button class="btn btn-danger" onclick="confirmDelete()" title="Delete Customer"> Delete
                        <i class="fas fa-trash"></i>
                    </button>
</div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="deleteModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-secondary" id="noDeleteBtn" style="display: none;">No</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Car Details Modal -->
    <div class="modal fade" id="carDetailsModal" tabindex="-1" role="dialog" aria-labelledby="carDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow rounded">
                <div class="modal-header rounded-top">
                    <h5 class="modal-title" id="carDetailsModalLabel">
                        <i class="fas fa-car mr-2"></i>Car Information
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-car mr-2 text-primary"></i>Car Details
                    </h6>
                    <div id="carDetailsContent" class="mb-4">
                        <!-- Car details will be loaded here -->
                    </div>
                    <div class="job-cards-section">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-folder mr-2 text-primary"></i>Associated Job Cards
                        </h6>
                        <div id="carJobCards" style="max-height: 300px; overflow-y: auto;">
                            <!-- Job cards will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <iframe id="printFrame" style="display:none;"></iframe>
    <script>
    function printCustomerView(customerId) {
        var iframe = document.getElementById('printFrame');
        iframe.src = 'print/PrintCustomerView.php?id=' + customerId + '&print=1';
        iframe.onload = function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        };
    }
    </script>

</body>
</html>