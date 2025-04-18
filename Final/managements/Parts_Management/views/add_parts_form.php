<?php
require_once '../../UserAccess/protect.php';
/**
 * Add Parts Form
 * 
 * This file provides a form for adding new parts to the system. It includes:
 * - Supplier details section with name, phone, and email fields
 * - Part details section with description, quantity, pricing, and VAT
 * - Client-side validation for required fields
 * - AJAX functionality for supplier and part suggestions
 * - Dynamic calculation of bulk price including VAT
 */
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Parts</title>
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

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .top-container h2 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
<?php
// Display error message if it exists
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">';
    echo $_SESSION['error_message'];
    echo '</div>';
    
    // Clear the error message after displaying it
    unset($_SESSION['error_message']);
}
?>
<div class="form-container">
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='parts_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
            <h2 class="mb-0">Add Parts</h2>
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

    <form action="../controllers/add_parts_controller.php" method="POST" id="partsForm">
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
                        <input type="text" id="supplier" name="supplier" class="form-control" required autocomplete="off">
                        <input type="hidden" id="supplierID" name="supplierID" required>
                        <div id="supplierSuggestions" class="suggestions-container"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierPhone">Supplier Phone <span class="text-info">*</span></label>
                        <input type="tel" id="supplierPhone" name="supplierPhone" class="form-control">
                        <small class="form-text text-muted">Either phone or email is required</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="supplierEmail">Supplier Email <span class="text-info">*</span></label>
                        <input type="email" 
                               id="supplierEmail" 
                               name="supplierEmail" 
                               class="form-control"
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
                    <input type="text" id="partDesc" name="partDesc[]" class="form-control" required autocomplete="off">
                    <div class="part-suggestions"></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="piecesPurch">Pieces Purchased *</label>
                    <input type="number" id="piecesPurch" name="piecesPurch[]" class="form-control" required min="1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="dateCreated">Date Created *</label>
                    <input type="date" id="dateCreated" name="dateCreated" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="pricePerPiece">Price Per Piece *</label>
                    <input type="number" id="pricePerPiece" name="pricePerPiece[]" class="form-control" step="0.01" required min="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="sellingPrice">Selling Price *</label>
                    <input type="number" id="sellingPrice" name="sellingPrice[]" class="form-control" step="0.01" required min="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="vat">VAT (%) *</label>
                    <input type="number" id="vat" name="vat[]" class="form-control" step="0.01" required min="0" max="100" value="19">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="priceBulk">Price Bulk (total)</label>
                    <input type="number" id="priceBulk" name="priceBulk[]" class="form-control" step="0.01" readonly>
                </div>
            </div>
        </div>

        <div class="btngroup text-center mt-4">
                    <button type="submit" class="btn btn-primary">Save <i class="fas fa-save"></i></button>
                </div>
    </form>
</div>

<script>
/**
 * JavaScript Functions
 * 
 * calculatePriceBulk(): Calculates the total price including VAT
 * validateEmailFormat(input): Validates the email format and provides feedback
 * AJAX handlers for supplier and part suggestions
 * Form submission handler with validation
 */

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
    $("#partsForm").on('submit', function(e) {
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

        // Check each required field
        for (const [fieldId, fieldName] of Object.entries(requiredFields)) {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                hasErrors = true;
                field.classList.add('is-invalid');
                let feedback = field.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    field.parentNode.insertBefore(feedback, field.nextSibling);
                }
                feedback.textContent = `${fieldName} is required`;
                feedback.style.display = 'block';
                
                // Focus the first invalid field
                if (!document.querySelector('.is-invalid:focus')) {
                    field.focus();
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        if (hasErrors) {
            return;
        }

        // Structure the parts data
        const parts = [];
        const partDesc = document.getElementsByName('partDesc[]');
        const piecesPurch = document.getElementsByName('piecesPurch[]');
        const pricePerPiece = document.getElementsByName('pricePerPiece[]');
        const priceBulk = document.getElementsByName('priceBulk[]');
        const sellingPrice = document.getElementsByName('sellingPrice[]');
        const vat = document.getElementsByName('vat[]');

        for (let i = 0; i < partDesc.length; i++) {
            parts.push({
                partDesc: partDesc[i].value,
                piecesPurch: piecesPurch[i].value,
                pricePerPiece: pricePerPiece[i].value,
                priceBulk: priceBulk[i].value || null,
                sellingPrice: sellingPrice[i].value,
                vat: vat[i].value
            });
        }

        // Create FormData object
        const formData = new FormData();
        
        // Add non-array fields
        formData.append('supplier', document.getElementById('supplier').value);
        formData.append('supplierID', document.getElementById('supplierID').value);
        formData.append('supplierPhone', document.getElementById('supplierPhone').value);
        formData.append('supplierEmail', document.getElementById('supplierEmail').value);
        formData.append('dateCreated', document.getElementById('dateCreated').value);
        
        // Add parts data as JSON string
        formData.append('parts', JSON.stringify(parts));

        // Debug log
        console.log('Sending data:', {
            supplier: document.getElementById('supplier').value,
            supplierID: document.getElementById('supplierID').value,
            dateCreated: document.getElementById('dateCreated').value,
            parts: parts
        });

        // Submit the form
        $.ajax({
            url: '../controllers/add_parts_controller.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // Tell jQuery to expect JSON response
            success: function(response) {
                console.log('Response:', response);
                if (response.status === 'success') {
                    $('#successMessage').text(response.message);
                    $('#successAlert').addClass('show').show();
                    setTimeout(function() {
                        window.location.href = 'parts_main.php';
                    }, 2000);
                } else {
                    alert(response.message || 'An error occurred while saving the parts.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                console.error('Response Text:', xhr.responseText);
                let errorMessage = 'An error occurred while saving the parts.';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    if (xhr.responseText) {
                        errorMessage = xhr.responseText;
                    } else {
                        errorMessage = error;
                    }
                }
                
                alert(errorMessage);
            }
        });
    });

    function validateEmailFormat(input) {
        const invalidFeedback = input.nextElementSibling;
        
        // Reset validation state
        input.setCustomValidity('');
        invalidFeedback.style.display = 'none';
        
        if (input.value === '') {
            // Email is optional if phone is provided
            return true;
        }

        // Check for @ symbol
        if (!input.value.includes('@')) {
            input.setCustomValidity("Invalid email");
            invalidFeedback.textContent = `Please enter a part following '@'. '${input.value}' is incomplete.`;
            invalidFeedback.style.display = 'block';
            return false;
        }

        // Check for proper domain format
        const parts = input.value.split('@');
        if (parts.length === 2) {
            const domain = parts[1];
            if (!domain.includes('.')) {
                input.setCustomValidity("Invalid email");
                invalidFeedback.textContent = `Please enter a domain with '.' (e.g., domain.com). '${domain}' is incomplete.`;
                invalidFeedback.style.display = 'block';
                return false;
            }
            
            // Check if dot is at start or end of domain
            if (domain.startsWith('.') || domain.endsWith('.')) {
                input.setCustomValidity("Invalid email");
                invalidFeedback.textContent = `Domain cannot start or end with '.'. '${domain}' is invalid.`;
                invalidFeedback.style.display = 'block';
                return false;
            }
        }

        return true;
    }

    // Part suggestions functionality
    $(document).ready(function() {
        let partTimeout;
        const partInput = $("#partDesc");
        const partSuggestions = $(".part-suggestions");

        partInput.on('input', function() {
            clearTimeout(partTimeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                partSuggestions.empty().hide();
                return;
            }

            partTimeout = setTimeout(function() {
                $.ajax({
                    url: '../models/parts_model.php',
                    method: 'POST',
                    data: { part_query: query },
                    success: function(response) {
                        if (response.trim() === '') {
                            partSuggestions.empty().hide();
                        } else {
                            partSuggestions.html(response).show();
                        }
                    },
                    error: function() {
                        partSuggestions.html('<div class="error">Error fetching parts</div>').show();
                    }
                });
            }, 300);
        });

        // Handle part selection
        $(document).on('click', '.part-option', function() {
            const partDesc = $(this).text();
            partInput.val(partDesc);
            partSuggestions.empty().hide();
        });

        // Close suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.form-group').length) {
                partSuggestions.empty().hide();
            }
        });
    });
});
</script>

<style>
/**
 * CSS Styles
 * 
 * The styles section defines the appearance of the form and its components:
 * - Responsive design for different screen sizes
 * - Visual feedback for user interactions
 * - Styles for suggestions and validation feedback
 */

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

.invalid-feedback {
    color: #dc3545;
    font-size: 80%;
    margin-top: 0.25rem;
}

/* Success alert styles */
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
</style>
</body>
</html>
