

<?php
/**
 * Edit Parts Form
 * 
 * This file provides a form for editing existing parts in the system. It includes:
 * - Supplier details section with name, phone, and email fields
 * - Part details section with description, quantity, pricing, and VAT
 * - Client-side validation for required fields
 * - AJAX functionality for supplier suggestions
 * - Dynamic calculation of bulk price including VAT
 */

/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
  
// Include the input sanitization file for secure data handling
require_once '../includes/sanitize_inputs.php';

// Get the PDO database connection instance
$pdo = require '../config/db_connection.php';

// Include the PartsManagement class
require_once '../models/parts_model.php';

// Start the session early
session_start();

// Get the part ID from URL parameter and sanitize it
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Debug: Log part ID
error_log("Edit part page loading for ID: " . $id);

if (!$id) {
    error_log("No part ID provided");
    $_SESSION['error_message'] = "Error: No part ID provided.";
    header("Location: parts_main.php");
    exit;
}

// Create instance of PartsManagement
$partsMang = new PartsManagement();

// Get part data directly from the model
$part = $partsMang->ViewSingle($id);

// Debug what we got from the database
error_log("Part data from database: " . json_encode($part));

// If no part data, redirect to main page
if (!$part || empty($part)) {
    error_log("No part data found for ID: " . $id);
    $_SESSION['error_message'] = "Error: Part not found or data is incomplete.";
    header("Location: parts_main.php");
    exit;
}

// Display error message if it exists
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">';
    echo $_SESSION['error_message'];
    echo '</div>';
    
    // Clear the error message after displaying it
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Part</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
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

        .suggestions-container {
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

        .supplier-option {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .supplier-option:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="form-container">
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='parts_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
                <h2 class="mb-0">Edit Part</h2>
        </div>
        <div style="width: 30px;"></div>
    </div>

        <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="editPartForm">
            <!-- Hidden input for part ID -->
            <input type="hidden" name="partId" value="<?php echo htmlspecialchars($id); ?>">

        <!-- Supplier Section -->
        <!-- 
        This section captures supplier information required for the part:
        - Supplier name with autocomplete suggestions
        - Phone and email fields with validation
        - Either phone or email is required
        -->
            <h3>Supplier Details *</h3>
        <div class="supplier-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                            <label for="supplier">Supplier Name *</label>
                            <input type="text" id="supplier" name="supplier" class="form-control" required 
                                value="<?php echo htmlspecialchars($part['SupplierName']); ?>">
                            <input type="hidden" id="supplierID" name="supplierID" 
                                value="<?php echo htmlspecialchars($part['SupplierID']); ?>">
                        <div id="supplierSuggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierPhone">Supplier Phone <span class="text-info">*</span></label>
                        <input type="tel" id="supplierPhone" name="supplierPhone" class="form-control" 
                                value="<?php echo htmlspecialchars($part['SupplierPhone']); ?>">
                        <small class="form-text text-muted">Either phone or email is required</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierEmail">Supplier Email <span class="text-info">*</span></label>
                        <input type="email" id="supplierEmail" name="supplierEmail" class="form-control"
                                value="<?php echo htmlspecialchars($part['SupplierEmail']); ?>"
                            oninput="validateEmailFormat(this)"
                            oninvalid="validateEmailFormat(this)">
                        <div class="invalid-feedback" style="display: none;"></div>
                        <small class="form-text text-muted">Either phone or email is required</small>
                    </div>
                </div>
            </div>
        </div>

            <!-- Part Details Section -->
            <!-- 
            This section captures detailed information about the part:
            - Description, quantity, and pricing fields
            - Automatic calculation of bulk price including VAT
            - Validation to ensure all required fields are filled
            -->
            <h3>Part Details *</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                        <label for="partDesc">Part/Description *</label>
                        <input type="text" id="partDesc" name="partDesc" class="form-control" required 
                            value="<?php echo htmlspecialchars($part['PartDesc']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="piecesPurch">Pieces Purchased *</label>
                        <input type="number" id="piecesPurch" name="piecesPurch" class="form-control" required 
                            min="1" value="<?php echo htmlspecialchars($part['PiecesPurch']); ?>">
            </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="dateCreated">Date Created *</label>
                        <input type="date" id="dateCreated" name="dateCreated" class="form-control" required
                            value="<?php echo htmlspecialchars($part['DateCreated']); ?>">
        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="pricePerPiece">Price Per Piece *</label>
                        <input type="number" id="pricePerPiece" name="pricePerPiece" class="form-control" 
                            step="0.01" required min="0" value="<?php echo htmlspecialchars($part['PricePerPiece']); ?>">
            </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sellingPrice">Selling Price *</label>
                        <input type="number" id="sellingPrice" name="sellingPrice" class="form-control" 
                            step="0.01" required min="0" value="<?php echo htmlspecialchars($part['SellPrice']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="vat">VAT (%) *</label>
                        <input type="number" id="vat" name="vat" class="form-control" step="0.01" 
                            required min="0" max="100" value="<?php echo htmlspecialchars($part['Vat']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="priceBulk">Price Bulk (total)</label>
                        <input type="number" id="priceBulk" name="priceBulk" class="form-control" 
                            step="0.01" readonly value="<?php echo htmlspecialchars($part['PriceBulk'] ?? ''); ?>">
                </div>
                </div>
            </div>

            <div class="btngroup">
                <button type="submit" class="btn btn-primary mt-3">Save</button>
            </div>
        </form>
    </div>

<script>
    $(document).ready(function() {
        // Add the calculation function
        function calculatePriceBulk() {
            const pricePerPiece = parseFloat($('#pricePerPiece').val()) || 0;
            const piecesPurch = parseInt($('#piecesPurch').val()) || 0;
            const vat = parseFloat($('#vat').val()) || 0;

            const subtotal = pricePerPiece * piecesPurch;
            const vatAmount = subtotal * (vat / 100);
            const total = subtotal + vatAmount;

            // Set the calculated value to price bulk field
            $('#priceBulk').val(total.toFixed(2));
        }

        // Add event listeners for the input fields
        $('#pricePerPiece, #piecesPurch, #vat').on('input', calculatePriceBulk);

        // Calculate initial value if fields are pre-filled
        calculatePriceBulk();

        // Supplier suggestions
        let supplierTimeout;
        const supplierInput = $("#supplier");
        const supplierSuggestions = $("#supplierSuggestions");
        const supplierIDInput = $("#supplierID");
        const supplierPhoneInput = $("#supplierPhone");
        const supplierEmailInput = $("#supplierEmail");

        supplierInput.on('input', function() {
            clearTimeout(supplierTimeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                supplierSuggestions.empty().hide();
                return;
            }

            supplierTimeout = setTimeout(function() {
                $.ajax({
                    url: '../models/parts_model.php',
                    method: 'POST',
                    data: { query: query },
                    success: function(response) {
                        if (response.trim() === '') {
                            supplierSuggestions.empty().hide();
                        } else {
                            supplierSuggestions.html(response).show();
                        }
                    },
                    error: function() {
                        supplierSuggestions.html('<div class="error">Error fetching suppliers</div>').show();
                    }
                });
            }, 300);
        });

        // Handle supplier selection
        $(document).on('click', '.supplier-option', function() {
            const id = $(this).data('id');
            const name = $(this).text();
            const phone = $(this).data('phone');
            const email = $(this).data('email');

            supplierInput.val(name);
            supplierIDInput.val(id);
            supplierPhoneInput.val(phone);
            supplierEmailInput.val(email);
            supplierSuggestions.empty().hide();
        });

        // Close suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.supplier-section').length) {
                supplierSuggestions.empty().hide();
            }
        });

        // Handle form submission
        $("#editPartForm").on('submit', function(e) {
            e.preventDefault();
            
            // Clear previous error messages
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

            // Required fields validation
            const requiredFields = {
                'dateCreated': 'Date',
                'supplier': 'Supplier name',
                'partDesc': 'Part description',
                'piecesPurch': 'Pieces purchased',
                'pricePerPiece': 'Price per piece',
                'sellingPrice': 'Selling price',
                'vat': 'VAT'
            };

            let hasErrors = false;

            // Validate required fields
            Object.entries(requiredFields).forEach(([field, label]) => {
                const input = $(`#${field}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    hasErrors = true;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            // Validate that either phone or email is provided
            if (!$('#supplierPhone').val() && !$('#supplierEmail').val()) {
                $('#supplierPhone').addClass('is-invalid');
                $('#supplierEmail').addClass('is-invalid');
                hasErrors = true;
            } else {
                $('#supplierPhone').removeClass('is-invalid');
                $('#supplierEmail').removeClass('is-invalid');
            }

            if (hasErrors) {
                return false;
            }

            // Create form data
            const formData = new FormData(this);
            formData.append('partId', document.querySelector('input[name="partId"]').value);
            formData.append('partDesc', document.getElementById('partDesc').value);
            formData.append('piecesPurch', document.getElementById('piecesPurch').value);
            formData.append('pricePerPiece', document.getElementById('pricePerPiece').value);
            formData.append('priceBulk', document.getElementById('priceBulk').value);
            formData.append('sellingPrice', document.getElementById('sellingPrice').value);
            formData.append('dateCreated', document.getElementById('dateCreated').value);
            formData.append('supplierID', document.getElementById('supplierID').value);
            formData.append('supplierName', document.getElementById('supplier').value);
            formData.append('supplierPhone', document.getElementById('supplierPhone').value);
            formData.append('supplierEmail', document.getElementById('supplierEmail').value);

            // Submit the form
            $.ajax({
                url: '../controllers/update_part_controller.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text(response.message);
                        $('#successAlert').addClass('show').show();
                        setTimeout(function() {
                            window.location.href = 'parts_main.php';
                        }, 2000);
                    } else {
                        alert(response.message || 'An error occurred while updating the part.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error});
                    alert('An error occurred while updating the part. Please try again.');
                }
            });
        });
    });
</script>
</body>
</html>