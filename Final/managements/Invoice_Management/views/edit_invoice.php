<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

/**
 * Invoice Edit Page
 * 
 * This file provides a user interface for editing existing invoices in the system.
 * It allows users to modify invoice details, supplier information, and manage parts
 * associated with the invoice. The page includes form validation and real-time
 * feedback to ensure data integrity.
 */

// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Include the InvoiceManagement class
require_once '../models/invoice_model.php';

// Include the user access protection file
require_once '../../UserAccess/protect.php';

// Check if a session is already active before starting a new one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the invoice ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Debug: Log invoice ID
error_log("Edit invoice page loading for ID: " . $id);

// Redirect if no invoice ID is provided
if (!$id) {
    error_log("No invoice ID provided");
    $_SESSION['message'] = "Error: No invoice ID provided.";
    $_SESSION['alert_type'] = "danger";
    header("Location: invoice_main.php");
    exit;
}

// Create instance of InvoiceManagement
$invoiceMang = new InvoiceManagement();

// Get invoice data directly from the model
$invoice = $invoiceMang->ViewSingle($id);

// Debug what we got from the database
error_log("Invoice data from database: " . json_encode($invoice));

// Redirect if no invoice data is found
if (!$invoice || empty($invoice)) {
    error_log("No invoice data found for ID: " . $id);
    $_SESSION['message'] = "Error: Invoice not found or data is incomplete.";
    $_SESSION['alert_type'] = "danger";
    header("Location: invoice_main.php");
    exit;
}

// Get parts for this invoice
$invoice['parts'] = $invoiceMang->getPartsByInvoiceId($id);

// Clean up any old form data
if (isset($_SESSION['invoice_form_data'])) {
    unset($_SESSION['invoice_form_data']);
}

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set view_only flag to bypass validation in constructor
$_POST['view_only'] = true;

// Remove unused database queries
$customerSql = 'SELECT * from customers where CustomerID = ?';
$customerStmt = $pdo->prepare($customerSql);
$customerStmt->execute([$id]);

// Store the customer data in a variable
$old_customer = $customerStmt->fetch();

// Query to fetch all addresses associated with the customer
$addressSql = 'select Address from addresses where CustomerID = ?';
$addressStmt = $pdo->prepare($addressSql);
$addressStmt->execute([$id]);

// Store all addresses in an array
$old_address = $addressStmt->fetchAll();

// Query to fetch all phone numbers associated with the customer
$phoneSql = 'SELECT Nr from phonenumbers where CustomerID = ?';
$phoneStmt = $pdo->prepare($phoneSql);
$phoneStmt->execute([$id]);

// Store all phone numbers in an array
$old_phone = $phoneStmt->fetchAll();

// Query to fetch all email addresses associated with the customer
$emailSql = 'SELECT Emails from emails where CustomerID = ?';
$emailStmt = $pdo->prepare($emailSql);
$emailStmt->execute([$id]);

// Store all email addresses in an array
$old_email = $emailStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice</title>
    
    <!-- CSS and JavaScript dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Styles for popup messages -->
    <style>
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<!-- Main Content Container -->
<div class="form-container">
    <!-- Top Navigation Bar with Title -->
    <div class="top-container d-flex justify-content-between align-items-center">
        <!-- Back Arrow Button -->
        <a href="javascript:void(0);" onclick="openForm('<?php echo $id; ?>')" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <!-- Title Display -->
        <div class="flex-grow-1 text-center">
            <h2 class="mb-0">Edit Invoice</h2>
        </div>
        <!-- Empty div for spacing -->
        <div style="width: 30px;"></div>
    </div>

    <!-- Validation Error Container (hidden by default) -->
    <div id="validationErrorContainer" class="alert alert-danger" style="display: none;">
        <strong>Please fill out all required fields:</strong>
        <ul id="validationErrorList"></ul>
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Invoice Edit Form -->
    <form id="invoiceForm" action="../controllers/update_invoice_controller.php?id=<?php echo htmlspecialchars($id); ?>" method="POST" enctype="multipart/form-data">
        <!-- Hidden input for invoice ID -->
        <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="InvoiceID" value="<?php echo htmlspecialchars($id); ?>">
        
        <!-- Invoice Information Section -->
        <div class="section-header">
            <i class="fas fa-file-invoice"></i>
            <span>Invoice Information</span>
        </div>

        <!-- Invoice Number and Date Fields -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="invoiceNr">Invoice Number</label>
                    <input type="text" id="invoiceNr" name="invoiceNr" class="form-control" 
                        value="<?php echo htmlspecialchars($invoice['InvoiceNr']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="dateCreated">Date Created</label>
                    <input type="date" id="dateCreated" name="dateCreated" class="form-control" 
                        value="<?php echo htmlspecialchars($invoice['DateCreated']); ?>">
                </div>
            </div>
        </div>

        <!-- Supplier Section -->
        <div class="section-header">
            <i class="fas fa-building"></i>
            <span>Supplier Information</span>
        </div>

        <!-- Supplier Details with Autocomplete -->
        <div class="supplier-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplier">Supplier Name</label>
                        <input type="text" id="supplier" name="supplier" class="form-control" 
                            value="<?php echo htmlspecialchars($invoice['SupplierName'] ?? ''); ?>">
                        <input type="hidden" id="supplierID" name="supplierID" value="<?php echo htmlspecialchars($invoice['SupplierID'] ?? null); ?>">
                        <input type="hidden" name="original_supplier_phone" value="<?php echo htmlspecialchars($invoice['SupplierPhone'] ?? ''); ?>">
                        <input type="hidden" name="original_supplier_email" value="<?php echo htmlspecialchars($invoice['SupplierEmail'] ?? ''); ?>">
                        <div id="supplierSuggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierPhone">Supplier Phone <span class="text-info">*</span></label>
                        <input type="tel" id="supplierPhone" name="supplierPhone" class="form-control" 
                            value="<?php echo htmlspecialchars($invoice['SupplierPhone'] ?? ''); ?>">
                        <small class="form-text text-muted">Either phone or email is required</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierEmail">Supplier Email <span class="text-info">*</span></label>
                        <input type="email" id="supplierEmail" name="supplierEmail" class="form-control"
                            value="<?php echo htmlspecialchars($invoice['SupplierEmail'] ?? ''); ?>"
                            oninput="validateEmailFormat(this)"
                            oninvalid="validateEmailFormat(this)">
                        <div class="invalid-feedback" style="display: none;"></div>
                        <small class="form-text text-muted">Either phone or email is required</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Section -->
        <div class="section-header">
            <i class="fas fa-calculator"></i>
            <span>Financial Information</span>
        </div>

        <!-- VAT and Total Price Fields -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="vat">VAT (%)</label>
                    <input type="number" id="vat" name="vat" class="form-control" step="0.01" min="0" max="100" 
                        value="<?php echo htmlspecialchars($invoice['Vat']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="total">Invoice Total Price</label>
                    <input type="number" id="total" name="total" class="form-control" step="0.01" min="0" 
                        value="<?php echo htmlspecialchars($invoice['Total']); ?>">
                </div>
            </div>
        </div>

        <!-- Parts Section -->
        <div class="section-header">
            <i class="fas fa-tools"></i>
            <span>Parts Information</span>
        </div>

        <!-- Parts List and Add Part Button -->
        <div class="parts-section" >
            <div class="parts-list mb-4" style="max-height: 300px; overflow-y: auto;">
            <?php foreach ($invoice['parts'] as $part): ?>
                    <div class="part-item">
                        <div class="part-header">
                            <span class="part-desc"><?php echo htmlspecialchars($part['PartDesc']); ?></span>
                            <span class="pieces-badge"><?php echo $part['PiecesPurch']; ?> pieces</span>
                </div>
                        <div class="part-pricing">
                            <div class="price-item">
                                <i class="fas fa-tag"></i>
                                <span>€<?php echo number_format($part['PricePerPiece'], 2); ?> per piece</span>
                            </div>
                            <?php if ($part['PriceBulk']): ?>
                            <div class="price-item">
                                <i class="fas fa-boxes"></i>
                                <span>€<?php echo number_format($part['PriceBulk'], 2); ?> bulk</span>
            </div>
            <?php endif; ?>
                            <div class="price-item selling-price">
                                <i class="fas fa-shopping-cart"></i>
                                <span>€<?php echo number_format($part['SellPrice'], 2); ?> selling price</span>
            </div>
            </div>
            <div class="part-actions">
                            <button type="button" class="btn btn-primary edit-part-btn" 
                                    data-part-id="<?php echo $part['PartID']; ?>"
                                    data-part-desc="<?php echo htmlspecialchars($part['PartDesc']); ?>"
                                    data-pieces="<?php echo $part['PiecesPurch']; ?>"
                                    data-price="<?php echo $part['PricePerPiece']; ?>"
                                    data-bulk="<?php echo $part['PriceBulk']; ?>"
                                    data-selling="<?php echo $part['SellPrice']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" class="btn btn-danger delete-part-btn" data-part-id="<?php echo $part['PartID']; ?>">
                                <i class="fas fa-trash"></i> Delete
                </button>
            </div> 
             </div>
            <?php endforeach; ?>
        </div>
        
       

<button type="button" class="btn btn-success" id="addPartBtn">
                <i class="fas fa-plus"></i> Add Part
                                        </button>
        <!-- Form Submit Button -->
        <div class="btngroup text-center mt-4">
          <button type="submit" class="btn btn-primary">Save <i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-secondary" onclick="openForm('<?php echo $id; ?>')">Cancel</button>
        </div>


    </form>
</div>

<!-- Part Edit Modal -->
<div class="modal fade" id="partModal" tabindex="-1" aria-labelledby="partModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partModalLabel">Edit Part</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="partForm">
                    <input type="hidden" id="partId">
                    <div class="mb-3">
                        <label for="partDesc" class="form-label">Part Description</label>
                        <input type="text" class="form-control" id="partDesc" required>
                </div>
                    <div class="mb-3">
                        <label for="piecesPurch" class="form-label">Pieces Purchased</label>
                        <input type="number" class="form-control" id="piecesPurch" min="1" required>
                </div>
                    <div class="mb-3">
                        <label for="pricePerPiece" class="form-label">Price Per Piece</label>
                        <input type="number" class="form-control" id="pricePerPiece" min="0" step="0.01" required>
                </div>
                    <div class="mb-3">
                        <label for="sellingPrice" class="form-label">Selling Price</label>
                        <input type="number" class="form-control" id="sellingPrice" min="0" step="0.01" required>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePartBtn">Save Part</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Part Confirmation Modal -->
<div class="modal fade" id="deletePartModal" tabindex="-1" role="dialog" aria-labelledby="deletePartModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePartModalLabel">Delete Part</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deletePartModalMessage">Are you sure you want to delete this part?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePartBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styles for the Page -->
<style>
/* Styles for autocomplete dropdowns */
.suggestions-container, .part-suggestions {
    position: absolute;
    width: 100%;
    max-height: 150px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    z-index: 1000;
    display: none;
}

/* Styles for autocomplete options */
.supplier-option, .part-option {
    padding: 8px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.supplier-option:hover, .part-option:hover {
    background-color: #f8f9fa;
}

/* Styles for error and no results messages */
.error, .no-results {
    padding: 8px;
    color: #666;
    font-style: italic;
}

/* Styles for part items */
.part-item {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.part-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.part-desc {
    font-size: 1.1em;
    font-weight: 500;
    color: #333;
}

.pieces-badge {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.9em;
}

/* Styles for part pricing information */
.part-pricing {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 10px;
}

.price-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.price-item i {
    color: #888;
}

.selling-price {
    color: #2e7d32;
    font-weight: 500;
}

.part-actions {
    display: flex
;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}


/* Styles for add part button */
#addPartBtn {
    padding: 8px 20px;
    font-size: 1em;
    border-radius: 4px;
    margin-top: 10px;
}

/* Styles for form actions */
.form-actions {
    margin-top: 30px;
    padding: 20px 0;
    text-align: center;
}

.form-actions .btn {
    min-width: 150px;
    font-size: 1.1em;
    padding: 10px 30px;
}

/* Styles for modal */
.modal-header {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
}

.modal-header .btn-close {
    display: none;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
}

.modal-footer .btn {
    padding: 8px 20px;
    min-width: 100px;
}

/* Styles for section headers */
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

/* Styles for form controls */
.form-control {
    min-height: calc(1.5em + .75rem + 2px);
    padding: .375rem .75rem;
    line-height: 1.5;
    display: block;
    width: 100%;
    height: calc(1.5em + .75rem + 2px);
}

/* Required field indicators */
label {
    position: relative;
}

/* Add asterisk to required fields */
label[for="invoiceNr"]:after,
label[for="dateCreated"]:after,
label[for="supplier"]:after,
label[for="vat"]:after,
label[for="total"]:after,
.modal-body label[for="partDesc"]:after,
.modal-body label[for="piecesPurch"]:after,
.modal-body label[for="pricePerPiece"]:after,
.modal-body label[for="sellingPrice"]:after {
    content: "*";
    color: #333;
    margin-left: 3px;
}

/* Required field indicator styling */
.text-info {
    color: #17a2b8 !important;
    font-weight: bold;
}

label small {
    display: block;
    margin-top: -5px;
    font-style: italic;
}

/* Remove asterisk from optional fields */
label[for="priceBulk"]:after,
label[for="supplierPhone"]:after,
label[for="supplierEmail"]:after {
    content: "" !important;
}
</style>

<!-- Message Popup for Notifications -->
<div id="messagePopup" class="popup"></div>

<!-- Script to Display Session Messages -->
<script>
    $(document).ready(function() {
        // Show popup message if exists
        <?php if (isset($_SESSION['message'])): ?>
            $('#messagePopup')
                .addClass('<?php echo $_SESSION['message_type']; ?>')
                .text('<?php echo $_SESSION['message']; ?>')
                .fadeIn()
                .delay(3000)
                .fadeOut();
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    });
</script>

<!-- Main JavaScript Functionality -->
<script>
    // Function to open the invoice main page
function openForm(invoiceId) {
    $.get('invoice_view.php', { id: invoiceId }, function(response) {
        $('#dynamicContent').html(response);
    });
}

        // Store original supplier data for comparison
        let originalSupplierData = {
            id: $('#supplierID').val(),
            name: $('#supplier').val(),
            phone: $('#supplierPhone').val(),
            email: $('#supplierEmail').val()
        };
        let isNewSupplier = false;

    $(document).ready(function() {
        // Supplier autocomplete functionality
        $("#supplier").keyup(function(){
            let query = $(this).val();
            if(query !== ""){
                $.ajax({
                    url: "../models/invoice_model.php",
                    method: "POST",
                    data: {query: query},
                    success: function(data){
                        $("#supplierSuggestions").html(data).fadeIn();
                        if(data.includes("No matching suppliers found")) {
                            $("#supplierSuggestions").empty().fadeOut();
                            // Clear supplier ID and contact info for new supplier
                                $("#supplierID").val('');
                            $("#supplierPhone").val('').attr('placeholder', 'Required for new supplier');
                            $("#supplierEmail").val('').attr('placeholder', 'Required for new supplier');
                            isNewSupplier = true;
                        }
                    }
                });
            } else {
                $("#supplierSuggestions").empty().fadeOut();
            }
        });

        // Handle supplier selection from autocomplete dropdown
        $(document).on("click", ".supplier-option", function(){
            let supplierName = $(this).text().trim();
            let supplierID = $(this).data('id');
            let supplierPhone = $(this).data('phone');
            let supplierEmail = $(this).data('email');
            
            $("#supplier").val(supplierName);
            $("#supplierID").val(supplierID);
            $("#supplierPhone").val(supplierPhone || '');
            $("#supplierEmail").val(supplierEmail || '');
            $("#supplierSuggestions").fadeOut();

            // Update original supplier data to prevent reset
            originalSupplierData = {
                id: supplierID,
                name: supplierName,
                phone: supplierPhone || '',
                email: supplierEmail || ''
            };

            // Reset placeholders
            $("#supplierPhone").attr('placeholder', '');
            $("#supplierEmail").attr('placeholder', '');
            isNewSupplier = false;
        });

        // Handle manual supplier name changes
        $("#supplier").on('input', function() {
            // Only clear supplier ID if not one of the suggestions was selected
            // This allows manual typing and editing of supplier names
            if ($(this).val() !== originalSupplierData.name && $("#supplierSuggestions").is(":hidden")) {
                $("#supplierID").val('');
                isNewSupplier = true;
                
                // Only clear contact info if completely different supplier
                if ($(this).val().trim() === '' || !$(this).val().includes(originalSupplierData.name)) {
                    $("#supplierPhone").val('').attr('placeholder', 'Required for new supplier');
                    $("#supplierEmail").val('').attr('placeholder', 'Required for new supplier');
                }
            }
        });

        // Form submission handling with validation
        $("#invoiceForm").submit(function(e) {
            e.preventDefault();
            
            // Clear previous error messages
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
            $('#validationErrorContainer').hide();
            $('#successAlert').hide();

            // Required fields validation
            const requiredFields = {
                'invoiceNr': 'Invoice number',
                'dateCreated': 'Date created',
                'supplier': 'Supplier name',
                'vat': 'VAT',
                'total': 'Total price'
            };

            let hasErrors = false;
            let errorMessages = [];

            // Validate required fields
            Object.entries(requiredFields).forEach(([field, label]) => {
                const input = $(`#${field}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    errorMessages.push(`${label} is required`);
                    hasErrors = true;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            // Validate that either phone or email is provided
            if (!$('#supplierPhone').val() && !$('#supplierEmail').val()) {
                $('#supplierPhone').addClass('is-invalid');
                $('#supplierEmail').addClass('is-invalid');
                errorMessages.push('Either supplier phone or email is required');
                hasErrors = true;
                    } else {
                $('#supplierPhone').removeClass('is-invalid');
                $('#supplierEmail').removeClass('is-invalid');
            }

            // Validate numeric fields
            const numericFields = {
                'vat': 'VAT',
                'total': 'Total price'
            };

            Object.entries(numericFields).forEach(([field, label]) => {
                const input = $(`#${field}`);
                const value = input.val().trim();
                if (value && (isNaN(value) || parseFloat(value) < 0)) {
                    input.addClass('is-invalid');
                    errorMessages.push(`${label} must be a valid number`);
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                // Show error messages in the validation container
                const errorList = $('#validationErrorList');
                errorList.empty();
                errorMessages.forEach(msg => {
                    errorList.append(`<li>${msg}</li>`);
                });
                $('#validationErrorContainer').show();
                return false;
            }

            // Create form data
            const formData = new FormData(this);
            formData.append('invoice_id', document.querySelector('input[name="invoice_id"]').value);
            formData.append('InvoiceID', document.querySelector('input[name="InvoiceID"]').value);

            // Collect parts data
            const parts = [];
            $('.part-item').each(function() {
                const partId = $(this).find('.edit-part-btn').data('part-id');
                const partDesc = $(this).find('.part-desc').text().trim();
                const piecesPurch = $(this).find('.pieces-badge').text().replace(' pieces', '').trim();
                const pricePerPiece = $(this).find('.price-item:first-child span').text().replace('€', '').replace(' per piece', '').trim();
                const priceBulk = $(this).find('.price-item:nth-child(2) span').text().replace('€', '').replace(' bulk', '').trim();
                const sellingPrice = $(this).find('.selling-price span').text().replace('€', '').replace(' selling price', '').trim();
                
                parts.push({
                    partId: partId,
                    partDesc: partDesc,
                    piecesPurch: piecesPurch,
                    pricePerPiece: pricePerPiece,
                    priceBulk: priceBulk || null,
                    sellingPrice: sellingPrice
                });
            });

            // Add parts data to form data
            formData.append('parts', JSON.stringify(parts));

            // Disable submit button and show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            // Submit the form
            $.ajax({
                url: '../controllers/update_invoice_controller.php?id=<?php echo htmlspecialchars($id); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        // Parse response if it's a string
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                    if (result.success) {
                            $('#successMessage').text(result.message || 'Invoice updated successfully!');
                            $('#successAlert').addClass('show').show();
                            setTimeout(function() {
                                openForm('<?php echo $id; ?>');
                            }, 2000);
                    } else {
                            // Show error message
                            const errorMsg = result.message || 'An error occurred while updating the invoice.';
                            $('#validationErrorList').html(`<li>${errorMsg}</li>`);
                            $('#validationErrorContainer').show();
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        $('#validationErrorList').html('<li>Error processing server response</li>');
                        $('#validationErrorContainer').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error});
                    let errorMsg = 'An error occurred while updating the invoice.';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    $('#validationErrorList').html(`<li>${errorMsg}</li>`);
                    $('#validationErrorContainer').show();
                },
                complete: function() {
                    // Restore submit button state
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
    });

    // Function to validate and save a part
    function validateAndSavePart() {
        const partId = $('#partId').val().trim();
        const partDesc = $('#partDesc').val().trim();
        const piecesPurch = $('#piecesPurch').val().trim();
        const pricePerPiece = $('#pricePerPiece').val().trim();
        const sellingPrice = $('#sellingPrice').val().trim();

        // Validate required fields
        if (!partDesc || !piecesPurch || !pricePerPiece || !sellingPrice) {
            alert('Please fill in all required fields');
            return;
        }

        // Validate numeric fields
        if (isNaN(piecesPurch) || piecesPurch <= 0 || 
            isNaN(pricePerPiece) || pricePerPiece < 0 || 
            isNaN(sellingPrice) || sellingPrice < 0) {
            alert('Please enter valid numbers');
            return;
        }

        // Create part HTML for display
        const partHtml = `
                <div class="part-item">
                    <div class="part-header">
                    <span class="part-desc">${escapeHtml(partDesc)}</span>
                        <span class="pieces-badge">${piecesPurch} pieces</span>
                    </div>
                    <div class="part-pricing">
                        <div class="price-item">
                            <i class="fas fa-tag"></i>
                            <span>€${parseFloat(pricePerPiece).toFixed(2)} per piece</span>
                        </div>
                        <div class="price-item selling-price">
                            <i class="fas fa-shopping-cart"></i>
                            <span>€${parseFloat(sellingPrice).toFixed(2)} selling price</span>
                        </div>
                    </div>
                    <div class="part-actions">
                    <button type="button" class="btn btn-primary edit-part-btn" 
                            data-part-id="${partId}"
                            data-part-desc="${escapeHtml(partDesc)}"
                            data-pieces="${piecesPurch}"
                            data-price="${pricePerPiece}"
                            data-selling="${sellingPrice}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    <button type="button" class="btn btn-danger delete-part-btn" data-part-id="${partId}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;

        // If we have a partId, we're editing an existing part
        if (partId) {
            // Find the existing part by its ID and replace it
            const existingPart = $(`.part-item .edit-part-btn[data-part-id="${partId}"]`).closest('.part-item');
            if (existingPart.length) {
                existingPart.replaceWith(partHtml);
            }
        } else {
            // For new parts, append to the list
            $('.parts-list').append(partHtml);
        }

        // Close modal and reset form
        $('#partModal').modal('hide');
        $('#partForm')[0].reset();
        formChanged = true;
    }

    // Initialize delete part handler
    function initializeDeletePartHandler() {
        let currentPartToDelete = null;
        let currentPartItem = null;

        // Show delete confirmation modal
        $(document).off('click', '.delete-part-btn').on('click', '.delete-part-btn', function(e) {
            e.preventDefault();
            
            // Check if this is the last part
            if ($('.part-item').length <= 1) {
                alert('Cannot delete the last part. Each invoice must have at least one part.');
                return;
            }

            const $button = $(this);
            currentPartToDelete = $button.data('part-id');
            currentPartItem = $button.closest('.part-item');
            const partDesc = currentPartItem.find('.part-desc').text();

            // Update modal message
            $('#deletePartModalMessage').text(`Are you sure you want to delete "${partDesc}"?`);
            
            // Show the modal
            $('#deletePartModal').modal('show');
        });

        // Handle delete confirmation
        $('#confirmDeletePartBtn').off('click').on('click', function() {
            if (!currentPartToDelete || !currentPartItem) return;

            // Check if this is a newly added part (has temp_ prefix)
            if (currentPartToDelete.startsWith('temp_')) {
                // For newly added parts, just remove from DOM
                currentPartItem.remove();
                formChanged = true;
                $('#messagePopup')
                    .removeClass('error')
                    .addClass('success')
                    .text('Part removed successfully')
                    .fadeIn()
                    .delay(3000)
                    .fadeOut();
            } else {
                // For existing parts, make AJAX call to delete from database
        $.ajax({
                    url: '../controllers/delete_part_controller.php',
            method: 'POST',
                    data: { partId: currentPartToDelete },
                    dataType: 'json',
                    success: function(response) {
                        try {
                            // Parse response if it's a string
                    const result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.success) {
                                currentPartItem.remove();
                                formChanged = true;
                                $('#messagePopup')
                                    .removeClass('error')
                                    .addClass('success')
                                    .text('Part deleted successfully')
                                    .fadeIn()
                                    .delay(3000)
                                    .fadeOut();
                    } else {
                                $('#messagePopup')
                                    .removeClass('success')
                                    .addClass('error')
                                    .text(result.message || 'Failed to delete part')
                                    .fadeIn()
                                    .delay(3000)
                                    .fadeOut();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                            $('#messagePopup')
                                .removeClass('success')
                                .addClass('error')
                                .text('Error processing server response')
                                .fadeIn()
                                .delay(3000)
                                .fadeOut();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete request failed:', error);
                        $('#messagePopup')
                            .removeClass('success')
                            .addClass('error')
                            .text('Error deleting part: ' + error)
                            .fadeIn()
                            .delay(3000)
                            .fadeOut();
                    }
                });
            }

            // Hide the modal
            $('#deletePartModal').modal('hide');
            
            // Clear the current part references
            currentPartToDelete = null;
            currentPartItem = null;
        });
    }

    // Call the initialization function when the document is ready
    $(document).ready(function() {
        initializeDeletePartHandler();
    });

    // Function to open the part modal
    function openPartModal(isNew = false) {
        if (isNew) {
            // Clear form for new part
            $('#partId').val('');
            $('#partDesc').val('');
            $('#piecesPurch').val('');
            $('#pricePerPiece').val('');
            $('#sellingPrice').val('');
            $('#partModalLabel').text('Add New Part');
        }
        $('#partModal').modal('show');
    }

    // Function to escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize part handlers when document is ready
    $(document).ready(function() {
        // Initialize delete part handler
        initializeDeletePartHandler();

        // Add part button handler
        $('#addPartBtn').on('click', function() {
            openPartModal(true);
        });

        // Save part button handler
        $('#savePartBtn').on('click', function() {
            validateAndSavePart();
        });

        // Edit part button handler
        $(document).on('click', '.edit-part-btn', function() {
            const btn = $(this);
            $('#partId').val(btn.data('part-id'));
            $('#partDesc').val(btn.data('part-desc'));
            $('#piecesPurch').val(btn.data('pieces'));
            $('#pricePerPiece').val(btn.data('price'));
            $('#sellingPrice').val(btn.data('selling'));
            $('#partModalLabel').text('Edit Part');
        $('#partModal').modal('show');
        });

        // Track form changes
        let formChanged = false;
        let originalFormData = $('#invoiceForm').serialize();

        // Track form changes
        $('#invoiceForm :input').on('change input', function() {
            formChanged = true;
        });

        // Track part changes using MutationObserver
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    formChanged = true;
                }
            });
        });

        observer.observe(document.querySelector('.parts-list'), {
            childList: true,
            subtree: true
        });

        // Handle back button click
        $('.back-arrow').on('click', function(e) {
            e.preventDefault();
            handleReturn();
        });

        // Add keydown event listener for Enter key
        $(document).on('keydown', function(e) {
            if (e.key === 'Enter' && !$(e.target).is('textarea')) {
                e.preventDefault();
                // If we're in the form, trigger submit
                if ($(e.target).closest('#invoiceForm').length) {
                    $('#invoiceForm').submit();
                }
            }
        });
    });

    // Function to handle returning to the main page
    function handleReturn() {
        // Check if there are no parts
        if ($('.part-item').length === 0) {
            alert('You must add at least one part before leaving this page.');
            return;
        }

        // Check for unsaved changes, but only if we're not submitting the form
        if (formChanged && !document.activeElement.matches('[type="submit"]')) {
            const confirmLeave = confirm('Changes you made will not be saved.');
            if (!confirmLeave) {
                return;
            }
        }

        // Remove the beforeunload event handler before redirecting
        window.removeEventListener('beforeunload', beforeUnloadHandler);
        
        // If all checks pass, redirect to main page
        window.location.href = 'invoice_main.php';
    }

    // Define the beforeunload handler as a named function
    function beforeUnloadHandler(e) {
        if (formChanged && !document.activeElement.matches('[type="submit"]')) {
            e.preventDefault();
            e.returnValue = 'Changes you made will not be saved.';
        }
    }

    // Add the beforeunload event listener
    window.addEventListener('beforeunload', beforeUnloadHandler);
});
</script>

<!-- Error Container for Form Validation -->
<div id="errorContainer" class="mb-3"></div>

<!-- Additional Script for Delete Part Functionality -->
<script>
// Wait for document to be ready
$(document).ready(function() {
    // Remove any existing click handlers from delete buttons
    $('.delete-part-btn').off('click');
    
    // Add new click handler
    $('.delete-part-btn').on('click', function() {
        const button = $(this);
        const partId = button.data('part-id');
        const partItem = button.closest('.part-item');
        const partDesc = partItem.find('.part-desc').text().trim();
        
        // Check if this is the last part
        if ($('.part-item').length <= 1) {
            alert('Cannot delete the last part. Each invoice must have at least one part.');
            return;
        }
        
        // Show confirmation modal instead of using browser confirm
        $('#deletePartModalMessage').text('Are you sure you want to delete "' + partDesc + '"?');
        $('#confirmDeletePartBtn').data('partId', partId);
        $('#confirmDeletePartBtn').data('partItem', partItem);
        $('#deletePartModal').modal('show');
    });
    
    // Handle confirm delete button click
    $('#confirmDeletePartBtn').on('click', function() {
        const partId = $(this).data('partId');
        const partItem = $(this).data('partItem');
        
        // Show loading state
        const confirmBtn = $(this);
        const originalText = confirmBtn.html();
        confirmBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
        confirmBtn.prop('disabled', true);
        
        // Send delete request
        $.ajax({
            url: '../controllers/delete_part_controller.php',
            type: 'POST',
            data: {
                partId: partId
            },
            success: function(response) {
                if (response.success) {
                    // Remove the part from the DOM
                    partItem.remove();
                    
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
                    successAlert.innerHTML = `
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Part deleted successfully!</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    `;
                    // Insert the alert after the top-container
                    const topContainer = document.querySelector('.top-container');
                    topContainer.parentNode.insertBefore(successAlert, topContainer.nextSibling);
                    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Auto-hide the success message after 3 seconds
                    setTimeout(() => {
                        $(successAlert).fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 2000);
                    
                    // Hide the modal
                    $('#deletePartModal').modal('hide');
                } else {
                    // Show error message
                    alert('Error: ' + (response.message || 'Failed to delete part'));
                }
            },
            error: function() {
                alert('Error: Failed to delete part');
            },
            complete: function() {
                // Restore button state
                confirmBtn.html(originalText);
                confirmBtn.prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>