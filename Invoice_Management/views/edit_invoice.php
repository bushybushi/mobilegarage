<?php
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Include the InvoiceManagement class
require_once '../models/invoice_model.php';

// Start the session early
session_start();

// Get the invoice ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Debug: Log invoice ID
error_log("Edit invoice page loading for ID: " . $id);

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

// If no invoice data, redirect to main page
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
$addressSql = 'select Address from Addresses where CustomerID = ?';
$addressStmt = $pdo->prepare($addressSql);
$addressStmt->execute([$id]);

// Store all addresses in an array
$old_address = $addressStmt->fetchAll();

// Query to fetch all phone numbers associated with the customer
$phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
$phoneStmt = $pdo->prepare($phoneSql);
$phoneStmt->execute([$id]);

// Store all phone numbers in an array
$old_phone = $phoneStmt->fetchAll();

// Query to fetch all email addresses associated with the customer
$emailSql = 'SELECT Emails from Emails where CustomerID = ?';
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
        <a href="javascript:void(0);" onclick="window.location.href='invoice_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <!-- Title Display -->
        <div class="flex-grow-1 text-center">
            <h2 class="mb-0">Edit Invoice</h2>
        </div>
        <!-- Empty div for spacing -->
        <div style="width: 30px;"></div>
    </div>

    <!-- Add this right after the form opening tag -->
    <div id="validationErrorContainer" class="alert alert-danger" style="display: none;">
        <strong>Please fill out all required fields:</strong>
        <ul id="validationErrorList"></ul>
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

        <div class="supplier-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplier">Supplier Name</label>
                        <input type="text" id="supplier" name="supplier" class="form-control" 
                            value="<?php echo htmlspecialchars($invoice['SupplierName']); ?>">
                        <input type="hidden" id="supplierID" name="supplierID" value="<?php echo htmlspecialchars($invoice['SupplierID']); ?>">
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

        <div class="parts-section">
            <div class="parts-list mb-4">
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
        </div>

        <div class="form-actions text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-5">Save</button>
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
                        <label for="priceBulk" class="form-label">Bulk Price</label>
                        <input type="number" class="form-control" id="priceBulk" min="0" step="0.01">
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

<style>
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

.supplier-option, .part-option {
    padding: 8px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.supplier-option:hover, .part-option:hover {
    background-color: #f8f9fa;
}

.error, .no-results {
    padding: 8px;
    color: #666;
    font-style: italic;
}

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

.edit-part-btn, .delete-part-btn {
    padding: 6px 15px;
    border-radius: 4px;
    font-size: 0.9em;
    margin-left: 5px;
}

.part-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}

#addPartBtn {
    padding: 8px 20px;
    font-size: 1em;
    border-radius: 4px;
    margin-top: 10px;
}

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

<div id="messagePopup" class="popup"></div>

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

<script>
        let originalSupplierData = {
            id: $('#supplierID').val(),
            name: $('#supplier').val(),
            phone: $('#supplierPhone').val(),
            email: $('#supplierEmail').val()
        };
        let isNewSupplier = false;

    $(document).ready(function() {
        // Supplier suggestions
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

        // Handle supplier selection
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

        // Main form submission
        $("#invoiceForm").submit(function(e) {
            e.preventDefault();
            
            // Basic validation
            let isValid = true;
            
            // Check required fields
            const requiredFields = ['invoiceNr', 'dateCreated', 'supplier', 'vat', 'total'];
            requiredFields.forEach(field => {
                const input = $(`#${field}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                isValid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            // Check if there are any parts
            if ($('.part-item').length === 0) {
                alert('Please add at least one part');
                return false;
            }

            // Check that either phone or email is provided for any supplier (new or existing)
            if (!$('#supplierPhone').val() && !$('#supplierEmail').val()) {
                $('#supplierPhone').addClass('is-invalid');
                $('#supplierEmail').addClass('is-invalid');
                alert('Either supplier phone or email is required');
                return false;
            } else {
                $('#supplierPhone').removeClass('is-invalid');
                $('#supplierEmail').removeClass('is-invalid');
            }

            if (!isValid) {
                alert('Please fill in all required fields');
                return false;
            }

            // Collect parts data
            const parts = [];
            $('.part-item').each(function() {
                const partBtn = $(this).find('.edit-part-btn');
                parts.push({
                    partId: partBtn.data('part-id'),
                    partDesc: partBtn.data('part-desc'),
                    piecesPurch: partBtn.data('pieces'),
                    pricePerPiece: partBtn.data('price'),
                    priceBulk: partBtn.data('bulk'),
                    sellingPrice: partBtn.data('selling')
                });
            });

            // Create form data
            const formData = new FormData(this);
            formData.append('parts', JSON.stringify(parts));

            // Submit form
                $.ajax({
                url: '../controllers/update_invoice_controller.php?id=' + <?php echo htmlspecialchars($id); ?>,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                    success: function(response) {
                    console.log('Response:', response); // Debug log
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        if (response.status === 'success') {
                            // Show success message before redirect
                            $('#messagePopup')
                                .addClass('success')
                                .text('Invoice updated successfully!')
                                .fadeIn();
                            
                            // Redirect after a short delay
                            setTimeout(function() {
                                window.location.href = 'invoice_main.php';
                            }, 1500);
                        } else {
                            alert(response.message || 'Failed to update invoice');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error updating invoice. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error});
                    alert('Error updating invoice. Please try again.');
            }
        });
    });

        // Part save functionality
    function validateAndSavePart() {
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

            const partId = $('#partId').val();
            const priceBulk = $('#priceBulk').val() || '0';

            // Create part HTML
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
                        ${priceBulk > 0 ? `
                        <div class="price-item">
                            <i class="fas fa-boxes"></i>
                            <span>€${parseFloat(priceBulk).toFixed(2)} bulk</span>
                        </div>
                        ` : ''}
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
                                data-bulk="${priceBulk}"
                                data-selling="${sellingPrice}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-danger delete-part-btn" data-part-id="${partId}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;

            // Update or add part
            const existingPart = $(`.edit-part-btn[data-part-id="${partId}"]`).closest('.part-item');
            if (existingPart.length) {
                existingPart.replaceWith(partHtml);
            } else {
                $('.parts-list').append(partHtml);
            }

            // Close modal and reset form
            $('#partModal').modal('hide');
            $('#partForm')[0].reset();
        }

        // Save part button handler
        $('#savePartBtn').click(function() {
            validateAndSavePart();
        });

        // Add part button handler
        $('#addPartBtn').click(function() {
            openPartModal(true);
        });

        // Edit part button handler
        $(document).on('click', '.edit-part-btn', function() {
            const btn = $(this);
            $('#partId').val(btn.data('part-id'));
            $('#partDesc').val(btn.data('part-desc'));
            $('#piecesPurch').val(btn.data('pieces'));
            $('#pricePerPiece').val(btn.data('price'));
            $('#priceBulk').val(btn.data('bulk'));
            $('#sellingPrice').val(btn.data('selling'));
            $('#partModalLabel').text('Edit Part');
            $('#partModal').modal('show');
        });

    // Photo upload preview functionality
    document.getElementById('invoicePhoto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }

            // Check file type
            if (!file.type.match('image.*')) {
                alert('Please upload an image file');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('photoPreview').style.display = 'block';
                document.querySelector('.custom-file-label').textContent = file.name;
            }
            reader.readAsDataURL(file);
        }
    });

    let formChanged = false;
    let originalFormData;

    $(document).ready(function() {
        // Store original form data
        originalFormData = $('#invoiceForm').serialize();

        // Track form changes
        $('#invoiceForm :input').on('change input', function() {
            formChanged = true;
        });

        // Track part changes
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
        $('.back-arrow').off('click').on('click', function(e) {
            e.preventDefault();
            handleReturn();
        });
    });

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

    // Single delete handler - placed at the top level
    $(document).on('click', '.delete-part-btn', function(e) {
        e.preventDefault();
        
        // Check if this is the last part
        if ($('.part-item').length <= 1) {
            alert('Cannot delete the last part. Each invoice must have at least one part.');
            return;
        }

        const $button = $(this);
        const partId = $button.data('part-id');
        const $partItem = $button.closest('.part-item');
        const partDesc = $partItem.find('.part-desc').text();

        if (confirm(`Are you sure you want to delete "${partDesc}"?`)) {
            $.ajax({
                url: '../controllers/delete_part_controller.php',
                method: 'POST',
                data: { partId: partId },
                dataType: 'json',
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            $partItem.remove();
                            formChanged = true; // Mark form as changed
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
    });

        // Part handling functions
        function openPartModal(isNew = false) {
            if (isNew) {
                // Clear form for new part
                $('#partId').val('temp_' + Date.now());
                $('#partDesc').val('');
                $('#piecesPurch').val('');
                $('#pricePerPiece').val('');
                $('#priceBulk').val('');
                $('#sellingPrice').val('');
                $('#partModalLabel').text('Add New Part');
                
                // Reset any validation states
                $('#partForm .is-invalid').removeClass('is-invalid');
                $('#partForm .invalid-feedback').hide();
            }
            $('#partModal').modal('show');
        }

        function closePartModal() {
            $('#partModal').modal('hide');
            $('#partForm')[0].reset();
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>

<!-- Add error container at the top of the form -->
<div id="errorContainer" class="mb-3"></div>

<!-- Add this right before closing body tag -->
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
        
        // Confirm deletion
        if (confirm('Are you sure you want to delete "' + partDesc + '"?')) {
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
                        alert('Part deleted successfully');
                    } else {
                        // Show error message
                        alert('Error: ' + (response.message || 'Failed to delete part'));
                    }
                },
                error: function() {
                    alert('Error: Failed to delete part');
                }
            });
        }
    });
});
</script>
</body>
</html>