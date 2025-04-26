<?php
require_once '../../UserAccess/protect.php';

/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../models/parts_model.php";

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get parts ID from URL
$partsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$partsId) {
    $_SESSION['message'] = "No part ID provided.";
    $_SESSION['message_type'] = "error";
    header("Location: parts_main.php");
    exit;
}

// Create instance of PartsManagement
$partsMang = new PartsManagement();

// Get part details using ViewSingle
$part = $partsMang->ViewSingle($partsId);

// Get invoice information if part is associated with an invoice
try {
    // Get the existing database connection from PartsManagement
    $pdo = $partsMang->getConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT ps.InvoiceID, i.InvoiceNr, i.DateCreated
        FROM partssupply ps
        JOIN invoices i ON ps.InvoiceID = i.InvoiceID
        WHERE ps.PartID = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    $stmt->execute([$partsId]);
    $invoiceInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($invoiceInfo) {
        error_log("Found invoice information for part: " . json_encode($invoiceInfo));
    } else {
        error_log("No invoice information found for part ID: " . $partsId);
    }

} catch (Exception $e) {
    error_log("Error fetching invoice information: " . $e->getMessage());
    $invoiceInfo = null;
}

if (!$part) {
    $_SESSION['message'] = "Part not found.";
    $_SESSION['message_type'] = "error";
    header("Location: parts_main.php");
    exit;
}

// Log the part data for debugging
error_log("Part data in view: " . json_encode($part));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Details</title>
    <link rel="stylesheet" href="../assets/styles.css">
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
                margin-bottom: 30px !important;
                padding-bottom: 15px !important;
                border-bottom: 2px solid #000 !important;
            }
            .header-info {
                text-align: left !important;
                padding-right: 220px !important;
            }
            .header-info p {
                font-size: 12pt !important;
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
                margin: 25px 0 15px 0 !important;
                border: none !important;
                border-bottom: 2px solid #000 !important;
                border-radius: 0 !important;
            }
            .section-header i {
                display: none !important;
            }
            .section-header span {
                font-size: 16pt !important;
                font-weight: bold !important;
                color: #000 !important;
            }
            .form-group {
                margin: 12px 0 !important;
                display: grid !important;
                grid-template-columns: 200px 1fr !important;
                align-items: center !important;
            }
            .form-control {
                border: none !important;
                padding: 0 !important;
                height: auto !important;
                min-height: auto !important;
                background: none !important;
                font-size: 12pt !important;
            }
            label {
                font-weight: 600 !important;
                color: #333 !important;
                font-size: 12pt !important;
            }
            .parts-list {
                margin-top: 15px !important;
                box-shadow: none !important;
            }
            .part-item {
                padding: 15px 0 !important;
                margin-bottom: 15px !important;
                border: none !important;
                border-bottom: 1px solid #ddd !important;
            }
            .part-header {
                margin-bottom: 10px !important;
            }
            .part-desc {
                font-weight: bold !important;
                font-size: 14pt !important;
            }
            .part-pieces {
                background: none !important;
                padding: 0 !important;
                font-size: 12pt !important;
            }
            .part-pricing {
                display: grid !important;
                grid-template-columns: repeat(3, auto) !important;
                gap: 30px !important;
                font-size: 12pt !important;
                margin-top: 10px !important;
            }
            .price-item {
                color: #333 !important;
                font-size: 12pt !important;
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
            .parts-actions {
                display: none !important;
            }
            .pieces-info {
                margin-top: 8px !important;
                font-size: 12pt !important;
            }
            .pieces-item {
                background: none !important;
                padding: 0 !important;
                border: none !important;
                font-size: 12pt !important;
            }
            .pieces-item i {
                display: none !important;
            }
            .part-reference-info {
                font-size: 11pt !important;
            }
            .part-id {
                background: none !important;
                border: none !important;
                padding: 0 !important;
                font-family: Arial, sans-serif !important;
            }
            .part-date i {
                display: none !important;
            }
            .part-pricing.mt-2 {
                margin-top: 15px !important;
                padding-top: 15px !important;
                border-top: 1px solid #ddd !important;
            }
            .part-pricing .bulk-price {
                background: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }

        .print-only {
            display: none;
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .top-container h2 {
                font-size: 1.2rem;
            }

            .parts-section {
                padding: 0.5rem;
            }

            .parts-list {
                margin: 0;
                border-radius: 0;
            }

            .part-item {
                padding: 0.75rem;
                margin-bottom: 0.5rem;
            }

            .part-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .part-main-info {
                width: 100%;
            }

            .part-reference-info {
                width: 100%;
                justify-content: space-between;
            }

            .part-pricing {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.5rem 0;
            }

            .price-item {
                width: 100%;
                justify-content: space-between;
            }

            .pieces-info {
                flex-direction: column;
                gap: 0.5rem;
            }

            .pieces-item {
                width: 100%;
                justify-content: space-between;
            }

            .part-pricing.mt-2 {
                margin-top: 0.5rem;
                padding-top: 0.5rem;
            }

            .section-header {
                padding: 0.75rem;
                margin: 1rem 0;
            }

            .section-header span {
                font-size: 1rem;
            }

            .form-group {
                margin: 0.5rem 0;
            }

            .form-control {
                font-size: 0.9rem;
            }

            label {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>


<!-- Print Header -->
<div class="print-only">
    <div class="header">
        <div class="header-info">
            <p id="generatedDateTime"></p>
        </div>
        <img src="../assets/logo.png" alt="Logo" class="logo">
    </div>
</div>

<!-- Main Content -->
<div class="form-container">
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='parts_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0">Parts Details</h2>
        <button id="printButton" class="print-btn" onclick="printParts()" title="Print Parts">
            <i class="fas fa-print"></i>
        </button>
    </div>

    <?php if ($invoiceInfo): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        This part is associated with Invoice #<?php echo htmlspecialchars($invoiceInfo['InvoiceNr']); ?>
        (created on <?php echo date('Y-m-d', strtotime($invoiceInfo['DateCreated'])); ?>)
        <a href="javascript:void(0);" onclick="viewInvoice(<?php echo $invoiceInfo['InvoiceID']; ?>)" class="alert-link">View Invoice</a>
    </div>
    <?php endif; ?>

    <form id="partsForm">
        <!-- Hidden input for parts ID -->
        <input type="hidden" name="PartsID" value="<?php echo htmlspecialchars($partsId); ?>">
        
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
                        <div class="form-control"><?php echo htmlspecialchars($part['SupplierName'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierPhone">Supplier Phone</label>
                        <div class="form-control"><?php echo htmlspecialchars($part['SupplierPhone'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierEmail">Supplier Email</label>
                        <div class="form-control"><?php echo htmlspecialchars($part['SupplierEmail'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Part Information Section -->
        <div class="section-header">
            <i class="fas fa-info-circle"></i>
            <span>Part Information</span>
        </div>

        <div class="parts-section">
            <div class="parts-list">
                <div class="part-item">
                    <div class="part-info">
                        <div class="part-header">
                            <div class="part-main-info">
                                <span class="part-desc"><?php echo htmlspecialchars($part['PartDesc']); ?></span>
                                <div class="pieces-info">
                                    <span class="pieces-item">
                                        <i class="fas fa-box"></i>
                                        Purchased: <?php echo $part['PiecesPurch']; ?>
                                    </span>
                                    <?php if ($part['Sold']): ?>
                                        <span class="pieces-item">
                                            <i class="fas fa-check"></i>
                                            Sold: <?php echo $part['Sold']; ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="pieces-item">
                                        <i class="fas fa-warehouse"></i>
                                        Stock: <?php echo $part['Stock']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="part-reference-info">
                                <span class="part-id">#<?php echo htmlspecialchars($part['PartID']); ?></span>
                                <span class="part-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('Y-m-d', strtotime($part['DateCreated'])); ?>
                                </span>
                            </div>
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
                            <?php
                            // Calculate total price including VAT
                            $subtotal = $part['PiecesPurch'] * $part['PricePerPiece'];
                            $vat = isset($part['Vat']) && $part['Vat'] !== null ? $part['Vat'] : 0;
                            $vatAmount = $subtotal * ($vat / 100);
                            $total = $subtotal + $vatAmount;
                            ?>
                            <span class="price-item bulk-price">
                                <i class="fas fa-boxes"></i>
                                €<?php echo number_format($total, 2); ?> total (incl. VAT)
                            </span>
                        </div>
                        <div class="part-pricing mt-2">
                            <span class="price-item">
                                <i class="fas fa-percent"></i>
                                VAT: <?php echo number_format($vat, 2); ?>%
                            </span>
                            <span class="price-item">
                                <i class="fas fa-calculator"></i>
                                Subtotal: €<?php echo number_format($subtotal, 2); ?>
                            </span>
                            <span class="price-item">
                                <i class="fas fa-plus"></i>
                                VAT Amount: €<?php echo number_format($vatAmount, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


                    <div class="btngroup text-center mt-4">
            <button type="button" class="btn btn-primary " onclick="loadEditForm(<?php echo $partsId; ?>)">Edit <i class="fas fa-edit"></i></button>
            <?php if ($invoiceInfo): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    This part cannot be deleted directly as it is associated with an invoice. To delete this part, please 
                    <a href="javascript:void(0);" onclick="viewInvoice(<?php echo $invoiceInfo['InvoiceID']; ?>)" class="alert-link">view the invoice</a> 
                    and delete it from there.
                </div>
            <?php else: ?>
            <button type="button" class="btn btn-danger " onclick="confirmDelete(<?php echo $partsId; ?>)">Delete <i class="fas fa-trash"></i></button>
            <?php endif; ?>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-secondary" id="noDeleteBtn" style="display: none;">No</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="invoiceContent">
                    <!-- Invoice content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="goToInvoiceBtn" class="btn btn-primary mb-2 mb-md-0">
                    <i class="fas fa-external-link-alt"></i> Go to Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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

.parts-actions {
    margin-top: 2rem;
}

.parts-actions .btn {
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
    padding: 1.25rem;
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
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.part-main-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.part-reference-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.part-id {
    font-family: monospace;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.part-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.part-date i {
    color: #adb5bd;
    font-size: 0.9rem;
}

.part-desc {
    font-size: 1.1rem;
    font-weight: 500;
    color: #212529;
    margin-bottom: 0;
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
    font-size: 0.95rem;
    padding: 0.75rem 0;
}

.part-pricing .price-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
}

.part-pricing .price-item i {
    color: #adb5bd;
    font-size: 0.9rem;
}

.part-pricing .selling-price {
    color: #28a745;
    font-weight: 500;
}

.part-pricing .selling-price i {
    color: #28a745;
}

.part-pricing.mt-2 {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
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

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-warning .fas {
    margin-right: 8px;
}

.alert-warning .alert-link {
    color: #533f03;
    font-weight: 700;
    text-decoration: underline;
}

.alert-warning .alert-link:hover {
    color: #533f03;
    text-decoration: none;
}

.part-pricing .bulk-price {
    color: #0d6efd;
    font-weight: 500;
    background-color: #f8f9fa;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.part-pricing .bulk-price i {
    color: #0d6efd;
}

.part-pricing .bulk-total {
    color: #0d6efd;
    font-weight: 600;
    font-size: 1.1rem;
    background-color: #f8f9fa;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.part-pricing .bulk-total i {
    color: #0d6efd;
}

.pieces-info {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #495057;
}

.pieces-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    border: 1px solid #e9ecef;
}

.pieces-item i {
    color: #6c757d;
    font-size: 0.9rem;
}

.part-main-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.modal-xl {
    max-width: 90%;
}

#invoiceContent {
    max-height: 70vh;
    overflow-y: auto;
}

.modal-body {
    padding: 1.5rem;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.modal {
    display: none;
}

.modal.show {
    display: block;
}
</style>

<script>

$('#goToInvoiceBtn').on('click', function(e) {
    e.preventDefault();
    const invoiceId = <?php echo $invoiceInfo ? $invoiceInfo['InvoiceID'] : 'null'; ?>;
    if (invoiceId) {
        // Close the modal and remove backdrop
        $('#invoiceModal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        // Store the invoice ID in session storage
        sessionStorage.setItem('selectedInvoiceId', invoiceId);
        
        // Update the URL to reflect navigation to invoice management
        window.history.pushState({}, '', '../../Invoice_Management/views/invoice_main.php');
        
        // Load the invoice main page first
        $.get('../../Invoice_Management/views/invoice_main.php', function(response) {
            $('#dynamicContent').html(response);
            
            // After invoice main page loads, trigger the invoice view
            $.get('../../Invoice_Management/views/invoice_view.php', { id: invoiceId }, function(response) {
                $('#dynamicContent').html(response);
            });
        });
    }
});

function loadEditForm(partsId) {
    $.get('edit_parts.php', { id: partsId }, function(response) {
        $('#dynamicContent').html(response);
    });
}

function confirmDelete() {
    $('#deleteModalMessage').text('Are you sure you want to delete this part?');
    $('#confirmDeleteBtn').text('Delete');
    $('#noDeleteBtn').hide();
    $('#deleteModal').modal('show');
}

function deleteParts(partsId) {
    // Show loading state on the confirm button in the modal
    const confirmBtn = $('#confirmDeleteBtn');
    const originalText = confirmBtn.html();
    confirmBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
    confirmBtn.prop('disabled', true);

    // Send AJAX request
    fetch('../controllers/delete_part_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'partId=' + partsId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success alert before redirecting
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
            
            // Redirect after showing the message
            setTimeout(() => {
                window.location.href = 'parts_main.php';
            }, 2000);
        } else {
            // Check if the error is about invoice association
            if (data.message.includes('Cannot delete part: It is associated with Invoice')) {
                alert(data.message);
                window.location.href = 'parts_main.php';
                return;
            }
            throw new Error(data.message || 'Failed to delete part');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error deleting part. Please try again.');
    })
    .finally(() => {
        // Restore button state
        confirmBtn.html(originalText);
        confirmBtn.prop('disabled', false);
    });
}

function printParts() {
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



       

function viewInvoice(invoiceId) {
    // Clear any previous content
    $('#invoiceContent').empty();
    
    // Show loading state
    $('#invoiceContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading invoice details...</div>');
    
    // Show the modal
    $('#invoiceModal').modal('show');
    
    // Fetch invoice details
    fetch(`../../Invoice_Management/views/invoice_view.php?id=${invoiceId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Extract the main content from the invoice view
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const mainContent = doc.querySelector('.form-container');
            
            if (!mainContent) {
                throw new Error('Could not find invoice content');
            }
            
            // Remove the top container (back button, title, print button)
            const topContainer = mainContent.querySelector('.top-container');
            if (topContainer) {
                topContainer.remove();
            }
            
            // Remove the form actions (edit/delete buttons)
            const formActions = mainContent.querySelector('.invoice-actions');
            if (formActions) {
                formActions.remove();
            }
            
            // Update the modal content
            $('#invoiceContent').html(mainContent.innerHTML);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#invoiceContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Error loading invoice details: ${error.message}
                </div>
            `);
        });
}

// Prevent modal from showing on page load
$(document).ready(function() {
    // Hide modal if it's visible
    $('#invoiceModal').modal('hide');
    
    // Clear modal content
    $('#invoiceContent').empty();
    
    // Set up the delete confirmation button
    $('#confirmDeleteBtn').on('click', function() {
        const partsId = $('input[name="PartsID"]').val();
        $('#deleteModal').modal('hide');
        deleteParts(partsId);
    });

    // Handle modal close events
    $('#invoiceModal').on('hidden.bs.modal', function () {
        $('#invoiceContent').empty();
    });

    // Handle modal close button
    $('.modal .close').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });

    // Handle modal close button in header
    $('.modal-header .close').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });

    // Handle modal close button in footer
    $('.modal-footer .btn-secondary').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });
});
</script>
</body>
</html>