<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../models/invoice_model.php";
require_once '../../UserAccess/protect.php';

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get invoice ID from URL
$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$invoiceId) {
    $_SESSION['message'] = "No invoice ID provided.";
    $_SESSION['message_type'] = "error";
    header("Location: invoice_main.php");
    exit;
}

// Create instance of InvoiceManagement
$invoiceMang = new InvoiceManagement();

// Get invoice details using ViewSingle
$invoice = $invoiceMang->ViewSingle($invoiceId);

if (!$invoice) {
    $_SESSION['message'] = "Invoice not found.";
    $_SESSION['message_type'] = "error";
    header("Location: invoice_main.php");
    exit;
}

// Log the invoice data for debugging
error_log("Invoice data in view: " . json_encode($invoice));

// Get parts for this invoice
$parts = $invoiceMang->getPartsByInvoiceId($invoiceId);
$invoice['parts'] = $parts;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/print.css">
    <style>
        @media print {
            body { 
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: none;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
                margin: 0 !important;
                padding: 20px 30px !important;
            }
            .header {
                position: relative !important;
                width: 100% !important;
                margin-bottom: 20px !important;
                padding-bottom: 15px !important;
                border-bottom: 1px solid #000 !important;
            }
            .header-info {
                text-align: left !important;
                padding-right: 220px !important;
            }
            .header-info p {
                font-size: 11pt !important;
                margin: 5px 0 !important;
                color: #333 !important;
            }
            .logo {
                position: absolute !important;
                top: 0 !important;
                right: 0 !important;
                width: 200px !important;
                height: auto !important;
            }
            .form-container {
                padding: 0 !important;
                margin: 0 !important;
            }
            .section-header {
                background: none !important;
                padding: 0 0 5px 0 !important;
                margin: 20px 0 15px 0 !important;
                border: none !important;
                border-bottom: 1px solid #000 !important;
                border-radius: 0 !important;
            }
            .section-header i {
                display: none !important;
            }
            .section-header span {
                font-size: 14pt !important;
                font-weight: bold !important;
                color: #000 !important;
            }
            .form-group {
                margin: 8px 0 !important;
                display: grid !important;
                grid-template-columns: 180px 1fr !important;
                align-items: center !important;
            }
            
            label {
                font-weight: 600 !important;
                color: #333 !important;
                font-size: 11pt !important;
            }
            .parts-list {
                margin-top: 10px !important;
                box-shadow: none !important;
            }
            .part-item {
                padding: 12px 0 !important;
                margin-bottom: 12px !important;
                border: none !important;
                border-bottom: 1px solid #ddd !important;
            }
            .part-header {
                margin-bottom: 8px !important;
            }
            .part-desc {
                font-weight: bold !important;
                font-size: 12pt !important;
            }
            .part-pieces {
                background: none !important;
                padding: 0 !important;
                font-size: 11pt !important;
            }
            .part-pricing {
                display: grid !important;
                grid-template-columns: repeat(3, auto) !important;
                gap: 20px !important;
                font-size: 11pt !important;
            }
            .price-item {
                color: #333 !important;
            }
            .price-item i {
                display: none !important;
            }
            .supplier-section, .parts-section {
                margin-top: 0 !important;
            }
            .top-container {
                display: none !important;
            }
        }

        .print-only {
            display: none;
        }
    </style>
</head>
<body>

<?php
// Display success/error message if it exists
if (isset($_SESSION['message'])) {
    $messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
    $alertClass = $messageType === 'success' ? 'alert-success' : 'alert-danger';
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo '<i class="fas fa-' . ($messageType === 'success' ? 'check-circle' : 'exclamation-circle') . ' mr-2"></i>';
    echo $_SESSION['message'];
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    echo '<span aria-hidden="true">&times;</span>';
    echo '</button>';
    echo '</div>';
    
    // Clear the message after displaying it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!-- Print Header -->
<!-- 
    This section contains the header information that appears when printing the invoice:
    - Company logo
    - Generation date and time
    - Professional layout for printed documents
-->
<div class="print-only">
    <div class="header">
        <div class="header-info">
            <p id="generatedDateTime"></p>
        </div>
        <img src="../assets/logo.png" alt="Logo" class="logo">
    </div>
</div>

<!-- Main Content -->
<!-- 
    The main content area is divided into several sections:
    1. Invoice Information - Basic invoice details
    2. Supplier Information - Contact details of the supplier
    3. Financial Information - VAT and total price
    4. Parts Information - List of parts with their details
-->
<div class="form-container">
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='invoice_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0">Invoice Details</h2>
        <button id="printButton" class="print-btn" onclick="printInvoice()" title="Print Invoice">
            <i class="fas fa-print"></i>
        </button>
    </div>

    <form id="invoiceForm">
        <!-- Hidden input for invoice ID -->
        <input type="hidden" name="InvoiceID" value="<?php echo htmlspecialchars($invoiceId); ?>">
        
        <!-- Invoice Information Section -->
        <div class="section-header">
            <i class="fas fa-file-invoice"></i>
            <span>Invoice Information</span>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="invoiceNr">Invoice Number</label>
                    <div class="form-control"><?php echo htmlspecialchars($invoice['InvoiceNr']); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="dateCreated">Date Created</label>
                    <div class="form-control"><?php echo htmlspecialchars($invoice['DateCreated']); ?></div>
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
                        <div class="form-control"><?php echo htmlspecialchars($invoice['SupplierName'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierPhone">Supplier Phone</label>
                        <div class="form-control"><?php echo htmlspecialchars($invoice['SupplierPhone'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierEmail">Supplier Email</label>
                        <div class="form-control"><?php echo htmlspecialchars($invoice['SupplierEmail'] ?? 'N/A'); ?></div>
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
                    <div class="form-control"><?php echo htmlspecialchars($invoice['Vat']); ?>%</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="total">Invoice Total Price</label>
                    <div class="form-control">€<?php echo number_format($invoice['Total'], 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Parts Section -->
        <div class="section-header">
            <i class="fas fa-tools"></i>
            <span>Parts Information</span>
        </div>

        <div class="parts-section" style="max-height: 300px; overflow-y: auto;">
            <div class="parts-list">
                <?php foreach ($invoice['parts'] as $part): ?>
                    <div class="part-item">
                        <div class="part-info">
                            <div class="part-header">
                                <span class="part-desc"><?php echo htmlspecialchars($part['PartDesc']); ?></span>
                                <span class="part-pieces"><?php echo $part['PiecesPurch']; ?> pieces</span>
                            </div>
                            <div class="part-pricing">
                                <span class="price-item">
                                    <i class="fas fa-tag"></i>
                                    €<?php echo number_format($part['PricePerPiece'], 2); ?> per piece
                                </span>
                                <span class="price-item selling-price">
                                    <i class="fas fa-shopping-cart"></i>
                                    €<?php echo number_format($part['SellPrice'], 2); ?> selling price
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="btngroup text-center mt-4">
            <button type="button" class="btn btn-primary " onclick="loadEditForm(<?php echo $invoiceId; ?>)">Edit <i class="fas fa-edit"></i></button>
            <button type="button" class="btn btn-danger " onclick="confirmDelete(<?php echo $invoiceId; ?>)">Delete <i class="fas fa-trash"></i></button>
        </div>


    </form>
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
                <div id="partsDeletionOption" class="mt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="deletePartsCheckbox">
                        <label class="custom-control-label" for="deletePartsCheckbox">Also delete associated parts from the database</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
/* 
    Print-specific styles that ensure the invoice looks professional when printed:
    - Clean layout without unnecessary UI elements
    - Proper spacing and margins
    - Consistent font sizes and styles
    - Hidden elements that shouldn't appear in print
*/
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

.part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
    border-radius: 4px;
}

.part-info {
    flex: 1;
}

.part-desc {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.part-details {
    display: block;
    color: #666;
    font-size: 0.9em;
}

.edit-part-btn, .delete-part-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.edit-part-btn i, .delete-part-btn i {
    font-size: 0.875rem;
}

.invoice-actions {
    margin-top: 2rem;
}

.invoice-actions .btn {
    margin: 0 5px;
    min-width: 100px;
}

.parts-section {
    margin-top: 1.5rem;
}

.parts-list {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.part-item {
    padding: 0.9rem;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s ease;
}

.part-item:last-child {
    border-bottom: none;
}

.part-item:hover {
    background-color: #f8f9fa;
}

.part-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.part-desc {
    font-size: 1.1rem;
    font-weight: 500;
    color: #212529;
}

.part-pieces {
    background: #e9ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #495057;
}

.part-pricing {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.price-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.95rem;
}

.price-item i {
    color: #adb5bd;
    font-size: 0.9rem;
}

.selling-price {
    color: #28a745;
    font-weight: 500;
}

.selling-price i {
    color: #28a745;
}

@media (max-width: 768px) {
    .part-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .part-pricing {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

.print-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

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

.print-btn {
    color: #6c757d;
    background: none;
    border: none;
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

.part-row:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
  .top-container h2 {
      font-size: 1.2rem;
      margin: 0;
  }
}

/* 
    Responsive styles for the parts list:
    - Adjusts layout for smaller screens
    - Maintains readability on mobile devices
    - Ensures proper spacing and alignment
*/

</style>

<script>
    // Function to load the edit form
function loadEditForm(invoiceId) {
        $.get('edit_invoice.php', { id: invoiceId }, function(response) {
            $('#dynamicContent').html(response);
        });
    }

/**
 * Handles the deletion of an invoice with a two-step confirmation process:
 * 1. First confirms if the user wants to delete the invoice
 * 2. Then asks if associated parts should also be deleted
 * Uses AJAX to communicate with the server and handles the response appropriately
 */
function deleteInvoice(invoiceId) {
    // First confirmation for invoice deletion
    if (!confirm('Are you sure you want to delete this invoice?')) {
        return;
    }

    // Second confirmation for parts deletion
    const deleteParts = confirm('Do you want to delete the associated parts from the database as well?');

    // Send AJAX request
    fetch('../controllers/delete_invoice_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            invoiceId: invoiceId,
            deleteParts: deleteParts
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'invoice_main.php';
        } else {
            alert(data.message || 'Error deleting invoice');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting invoice');
    });
}

/**
 * Handles the printing functionality:
 * - Sets the current date and time in the header
 * - Formats the date/time in a user-friendly way
 * - Triggers the browser's print dialog
 */
function printInvoice() {
    // Set current date and time
    const now = new Date();
    const formattedDateTime = now.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    document.getElementById('generatedDateTime').textContent = 'Generated on: ' + formattedDateTime;
    
    // Print the document
    window.print();
}

// Function to show the delete confirmation modal
function confirmDelete(invoiceId) {
    $('#deleteModalMessage').text('Are you sure you want to delete this invoice?');
    $('#confirmDeleteBtn').text('Delete');
    $('#deleteModal').modal('show');
    
    // Store the invoice ID for use when the confirm button is clicked
    $('#confirmDeleteBtn').data('invoiceId', invoiceId);
}

// Function to handle the actual deletion process
function deleteInvoice(invoiceId) {
    // Show loading state on the confirm button in the modal
    const confirmBtn = $('#confirmDeleteBtn');
    const originalText = confirmBtn.html();
    confirmBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
    confirmBtn.prop('disabled', true);

    // Get the checkbox value for deleting parts
    const deleteParts = $('#deletePartsCheckbox').is(':checked');

    // Send AJAX request
    fetch('../controllers/delete_invoice_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            invoiceId: invoiceId,
            deleteParts: deleteParts
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success alert before redirecting
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                <span>Invoice deleted successfully!</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            // Insert the alert after the top-container
            const topContainer = document.querySelector('.top-container');
            topContainer.parentNode.insertBefore(successAlert, topContainer.nextSibling);
            successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Redirect after showing the message
            setTimeout(() => {
                window.location.href = 'invoice_main.php';
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to delete invoice');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error deleting invoice. Please try again.');
    })
    .finally(() => {
        // Restore button state
        confirmBtn.html(originalText);
        confirmBtn.prop('disabled', false);
    });
}

// Set up the delete confirmation button
$(document).ready(function() {
    $('#confirmDeleteBtn').on('click', function() {
        const invoiceId = $(this).data('invoiceId');
        $('#deleteModal').modal('hide');
        deleteInvoice(invoiceId);
    });
});
</script>
</body>
</html>