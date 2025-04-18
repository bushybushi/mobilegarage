<?php
require_once '../../UserAccess/protect.php';
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
   AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

/**
 * Invoice Creation Form
 * 
 * This file contains the HTML form and JavaScript functionality for creating new invoices.
 * It allows users to enter invoice details, supplier information, and add multiple parts to the invoice.
 * The form includes validation to ensure all required fields are filled correctly.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Invoice</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Override default browser styling for invalid inputs */
        input:invalid {
            border-color: #ced4da !important;  /* Same as Bootstrap's default border color */
            box-shadow: none !important;
        }

        /* Remove red asterisk color */
        .text-info {
            color: #333 !important;
            font-weight: bold;
        }

        .form-control:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
        }
        
        label small {
            display: block;
            margin-top: -5px;
            font-style: italic;
        }

        @media (max-width: 768px) {
  .top-container h2 {
      font-size: 1.2rem;
      margin: 0;
  }
}
    </style>
</head>
<body>
<?php
// Display error message if it exists in the session
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">';
    echo $_SESSION['error_message'];
    echo '</div>';
    
    // Clear the error message after displaying it
    unset($_SESSION['error_message']);
}
?>
<div class="form-container">
    <!-- Header with back button and title -->
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='invoice_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
            <h2 class="mb-0">Add Invoice</h2>
        </div>
        <div style="width: 30px;"></div>
    </div>

    <!-- Success message alert (hidden by default) -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Main invoice form -->
    <form action="../controllers/add_invoice_controller.php" method="POST" id="invoiceForm">
        <!-- Two-column row for Invoice Number and Date -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="invoiceNr">Invoice Number *</label>
                    <input type="text" id="invoiceNr" name="invoiceNr" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="dateCreated">Date Created *</label>
                    <input type="date" id="dateCreated" name="dateCreated" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- Supplier Section with autocomplete functionality -->
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

        <!-- Two-column row for VAT and Total Price -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="vat">VAT (%) *</label>
                    <input type="number" id="vat" name="vat" class="form-control" step="0.01" required min="0" max="100">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="total">Invoice Total Price *</label>
                    <input type="number" id="total" name="total" class="form-control" step="0.01" required min="0">
                </div>
            </div>
        </div>

        <!-- Parts Section - Parts will be added here dynamically -->
        <h3>Parts *</h3>
        <div id="parts-section">
            <!-- Parts will be displayed here -->
        </div>

        <!-- Button to open the Add Part modal -->
        <button type="button" class="btn btn-success mt-2" onclick="openAddPartModal()">
            Add Part <i class="fas fa-plus"></i>
        </button>

        <!-- Form submission button -->
        <div class="btngroup">
            <button type="submit" class="btn btn-primary mt-3">Save <i class="fas fa-save"></i></button>
        </div>
    </form>
</div>

<!-- Modal for adding or editing parts -->
<div class="modal fade" id="addPartModal" tabindex="-1" role="dialog" aria-labelledby="addPartModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPartModalLabel">Add New Part</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPartForm">
                    <div class="form-group">
                        <label for="modalPartDesc">Part/Description *</label>
                        <input type="text" id="modalPartDesc" class="form-control part-desc" required autocomplete="off">
                        <div class="part-suggestions"></div>
                    </div>
                    <div class="form-group">
                        <label for="modalPiecesPurch">Pieces Purchased *</label>
                        <input type="number" id="modalPiecesPurch" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="modalPricePerPiece">Price Per Piece *</label>
                        <input type="number" id="modalPricePerPiece" class="form-control" step="0.01" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="modalSellingPrice">Selling Price *</label>
                        <input type="number" id="modalSellingPrice" class="form-control" step="0.01" required min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePart()">Save Part</button>
            </div>
        </div>
    </div>
</div>

<script>
// Main JavaScript functionality for the invoice form
$(document).ready(function() {
    // Variables for supplier autocomplete functionality
    let supplierTimeout;
    const supplierInput = $("#supplier");
    const supplierSuggestions = $("#supplierSuggestions");
    const supplierIDInput = $("#supplierID");
    const supplierPhoneInput = $("#supplierPhone");
    const supplierEmailInput = $("#supplierEmail");

    // Real-time validation for invoice number to check for duplicates
    let invoiceCheckTimeout;
    $("#invoiceNr").on('input', function() {
        clearTimeout(invoiceCheckTimeout);
        const invoiceNr = $(this).val().trim();
        
        if (invoiceNr.length === 0) {
            $(this).removeClass('is-invalid is-valid');
            return;
        }

        // Check if invoice number already exists in the database
        invoiceCheckTimeout = setTimeout(function() {
            $.ajax({
                url: '../models/invoice_model.php',
                method: 'POST',
                data: { 
                    check_invoice: true,
                    invoice_nr: invoiceNr 
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        const input = $("#invoiceNr");
                        
                        if (result.exists) {
                            input.removeClass('is-valid').addClass('is-invalid');
                            let feedback = input.next('.invalid-feedback');
                            if (!feedback.length) {
                                feedback = $('<div class="invalid-feedback"></div>').insertAfter(input);
                            }
                            feedback.text('This invoice number already exists').show();
                        } else {
                            input.removeClass('is-invalid').addClass('is-valid');
                            input.next('.invalid-feedback').hide();
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                },
                error: function() {
                    console.error('Error checking invoice number');
                }
            });
        }, 500);
    });

    // Supplier name autocomplete functionality
    supplierInput.on('input', function() {
        clearTimeout(supplierTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            supplierSuggestions.empty().hide();
            return;
        }

        // Fetch matching suppliers from the database
        supplierTimeout = setTimeout(function() {
            $.ajax({
                url: '../models/invoice_model.php',
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

    // Handle supplier selection from autocomplete dropdown
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

    // Close supplier suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.supplier-section').length) {
            supplierSuggestions.empty().hide();
        }
    });

    // Form submission handling with validation
    $("#invoiceForm").on('submit', function(e) {
        e.preventDefault();

        // Check if invoice number is duplicate
        if ($("#invoiceNr").hasClass('is-invalid')) {
            showErrorMessage('Please fix the duplicate invoice number before submitting.');
            $("#invoiceNr").focus();
            return false;
        }

        // Clear previous error messages
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

        // Required fields validation
        const requiredFields = {
            'invoiceNr': 'Invoice number',
            'dateCreated': 'Date',
            'supplier': 'Supplier name',
            'vat': 'VAT',
            'total': 'Total'
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

        // Validate numeric fields
        const vat = parseFloat(document.getElementById('vat').value);
        const total = parseFloat(document.getElementById('total').value);

        if (isNaN(vat) || vat < 0 || vat > 100) {
            hasErrors = true;
            const vatField = document.getElementById('vat');
            vatField.classList.add('is-invalid');
            let feedback = vatField.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                vatField.parentNode.insertBefore(feedback, vatField.nextSibling);
            }
            feedback.textContent = "VAT must be a number between 0 and 100";
            feedback.style.display = 'block';
            
            if (!document.querySelector('.is-invalid:focus')) {
                vatField.focus();
                vatField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        if (isNaN(total) || total <= 0) {
            hasErrors = true;
            const totalField = document.getElementById('total');
            totalField.classList.add('is-invalid');
            let feedback = totalField.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                totalField.parentNode.insertBefore(feedback, totalField.nextSibling);
            }
            feedback.textContent = "Total must be a positive number";
            feedback.style.display = 'block';
            
            if (!document.querySelector('.is-invalid:focus')) {
                totalField.focus();
                totalField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Validate supplier contact info - either phone or email is required
        const supplierPhone = document.getElementById('supplierPhone').value.trim();
        const supplierEmail = document.getElementById('supplierEmail').value.trim();
        
        if (!supplierPhone && !supplierEmail) {
            hasErrors = true;
            document.getElementById('supplierPhone').classList.add('is-invalid');
            document.getElementById('supplierEmail').classList.add('is-invalid');
            let feedback = document.getElementById('supplierPhone').nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                document.getElementById('supplierPhone').parentNode.insertBefore(feedback, document.getElementById('supplierPhone').nextSibling);
            }
            feedback.textContent = "Either phone or email is required";
            feedback.style.display = 'block';
            
            if (!document.querySelector('.is-invalid:focus')) {
                document.getElementById('supplierPhone').focus();
                document.getElementById('supplierPhone').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Check if there are any parts added to the invoice
        if (document.querySelectorAll('.part-row').length === 0) {
            hasErrors = true;
            const partsSection = document.getElementById('parts-section');
            let feedback = partsSection.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                partsSection.appendChild(feedback);
            }
            feedback.textContent = "At least one part is required";
            feedback.style.display = 'block';
            
            if (!document.querySelector('.is-invalid:focus')) {
                document.getElementById('addPartBtn').focus();
                document.getElementById('addPartBtn').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        if (hasErrors) {
            return false;
        }

        // If this is a new supplier (no supplierID), create it first
        if (!$("#supplierID").val()) {
            $.ajax({
                url: "../models/invoice_model.php",
                method: "POST",
                data: {
                    action: "create_supplier",
                    name: $("#supplier").val(),
                    phone: $("#supplierPhone").val(),
                    email: $("#supplierEmail").val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $("#supplierID").val(response.supplierID);
                        submitFormData();
                    } else {
                        showErrorMessage('Failed to create supplier: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    showErrorMessage('Failed to create supplier. Please try again.');
                }
            });
        } else {
            submitFormData();
        }
    });

    // Function to submit the form data via AJAX
    function submitFormData() {
        // Create a FormData object
        const formData = new FormData(document.getElementById('invoiceForm'));

        // Get all parts data
        const parts = [];
        $('.part-row').each(function() {
            const partId = $(this).attr('id');
            const hiddenInputs = $(`#hidden-${partId}`);
            parts.push({
                partDesc: hiddenInputs.find('input[name="partDesc[]"]').val(),
                piecesPurch: hiddenInputs.find('input[name="piecesPurch[]"]').val(),
                pricePerPiece: hiddenInputs.find('input[name="pricePerPiece[]"]').val(),
                sellingPrice: hiddenInputs.find('input[name="sellingPrice[]"]').val()
            });
        });

        // Add parts data to FormData
        formData.append('parts', JSON.stringify(parts));
        formData.append('supplierID', $('#supplierID').val());

        // Submit form data via AJAX
        $.ajax({
            url: '../controllers/add_invoice_controller.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    // First try to parse the response if it's a string
                    let result = response;
                    if (typeof response === 'string') {
                        result = JSON.parse(response);
                    }

                    // Check if we have a success status
                    if (result.status === 'success') {
                        showSuccessMessage('Invoice added successfully');
                        // Wait for the success message to be visible before redirecting
                        setTimeout(() => {
                            window.location.href = 'invoice_main.php';
                        }, 1000);
                    } else {
                        // If we have an error message, display it
                        showErrorMessage(result.message || 'Failed to add invoice');
                    }
                } catch (e) {
                    console.error('Response parsing error:', e);
                    console.error('Raw response:', response);
                    // If the response is HTML (likely a PHP error), show a generic message
                    if (typeof response === 'string' && response.includes('<!DOCTYPE html>')) {
                        showErrorMessage('Server error occurred. Please try again.');
                    } else {
                        // Show the raw response if it's not HTML
                        showErrorMessage('Error processing response: ' + response);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                let errorMessage = 'Error adding invoice';
                
                // Try to get more specific error information
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.message) {
                            errorMessage += ': ' + result.message;
                        }
                    } catch (e) {
                        errorMessage += ': ' + error;
                    }
                } else {
                    errorMessage += ': ' + error;
                }
                
                showErrorMessage(errorMessage);
            }
        });
    }

    // Function to display error messages
    function showErrorMessage(message) {
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
        // Add the new error message
        document.querySelector('.form-container').insertBefore(popup, document.querySelector('form'));
        
        // Scroll to error message
        popup.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $(popup).alert('close');
        }, 5000);
    }

    // Function to display success messages
    function showSuccessMessage(message) {
        const successAlert = document.getElementById('successAlert');
        document.getElementById('successMessage').textContent = message;
        successAlert.classList.add('show');
        successAlert.style.display = 'block';
        
        // Scroll to success message
        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            successAlert.classList.remove('show');
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 150);
        }, 3000);
    }
});

// Function to validate email format using regex
function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(String(email).toLowerCase());
}

// Function to open the Add Part modal
function openAddPartModal() {
    // Reset the form
    document.getElementById('addPartForm').reset();
    
    // Explicitly clear all input values
    const inputs = document.getElementById('addPartForm').getElementsByTagName('input');
    for(let input of inputs) {
        input.value = '';
    }
    
    // Clear validation states
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
    
    // Clear suggestions
    $(".part-suggestions").empty().hide();
    
    // Reset editing state and title
    $("#addPartModal").removeData('editing-part-id');
    $("#addPartModalLabel").text("Add New Part");
    
    // Show the modal
    $("#addPartModal").modal('show');
}

// Event handler for when the modal is hidden
$('#addPartModal').on('hidden.bs.modal', function () {
    document.getElementById('addPartForm').reset();
    const inputs = document.getElementById('addPartForm').getElementsByTagName('input');
    for(let input of inputs) {
        input.value = '';
    }
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
    $(".part-suggestions").empty().hide();
    
    // Reset editing state and title
    $(this).removeData('editing-part-id');
    $("#addPartModalLabel").text("Add New Part");
});

// Function to save a part (either new or edited)
function savePart() {
    // Get values from modal
    const partDesc = $("#modalPartDesc").val();
    const pieces = $("#modalPiecesPurch").val();
    const pricePerPiece = $("#modalPricePerPiece").val();
    const sellingPrice = $("#modalSellingPrice").val();

    // Validate inputs
    if (!partDesc || !pieces || !pricePerPiece || !sellingPrice) {
        alert("All required fields must be filled!");
        return;
    }

    // Get the part ID if we're editing
    const editingPartId = $("#addPartModal").data('editing-part-id');

    // Create a unique ID for this part entry if it's new
    const partId = editingPartId || 'part-' + Date.now();

    // Create new part row with the unique ID
    const partRow = `
        <div class="part-row" id="${partId}">
            <div class="part-info">
                <div class="part-desc">${partDesc}</div>
                <div class="part-details">
                    <span>Pieces: ${pieces}</span>
                    <span>Price: €${pricePerPiece}</span>
                    <span>Sell: €${sellingPrice}</span>
                </div>
            </div>
            <div class="part-actions">
                <button type="button" class="btn btn-primary btn-sm edit-part" onclick="editPart('${partId}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm remove-part" onclick="removePart('${partId}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Add hidden inputs for form submission with the same unique ID
    const hiddenInputs = `
        <div id="hidden-${partId}">
            <input type="hidden" name="partDesc[]" value="${partDesc}">
            <input type="hidden" name="piecesPurch[]" value="${pieces}">
            <input type="hidden" name="pricePerPiece[]" value="${pricePerPiece}">
            <input type="hidden" name="sellingPrice[]" value="${sellingPrice}">
        </div>
    `;

    if (editingPartId) {
        // If editing, replace existing elements
        $(`#${partId}`).replaceWith(partRow);
        $(`#hidden-${partId}`).replaceWith(hiddenInputs);
    } else {
        // If new, append to parts section
        $("#parts-section").append(partRow);
        $("#parts-section").append(hiddenInputs);
    }

    // Reset editing state
    $("#addPartModal").removeData('editing-part-id');

    // Close modal
    $("#addPartModal").modal('hide');
}

// Function to edit an existing part
function editPart(partId) {
    // Get values from hidden inputs
    const hiddenInputs = $(`#hidden-${partId}`);
    
    // Set values in modal
    $("#modalPartDesc").val(hiddenInputs.find('input[name="partDesc[]"]').val());
    $("#modalPiecesPurch").val(hiddenInputs.find('input[name="piecesPurch[]"]').val());
    $("#modalPricePerPiece").val(hiddenInputs.find('input[name="pricePerPiece[]"]').val());
    $("#modalSellingPrice").val(hiddenInputs.find('input[name="sellingPrice[]"]').val());
    
    // Set editing state
    $("#addPartModal").data('editing-part-id', partId);
    
    // Update modal title
    $("#addPartModalLabel").text("Edit Part");
    
    // Show modal
    $("#addPartModal").modal('show');
}

// Function to remove a part
function removePart(partId) {
    // Show confirmation dialog
    if (confirm('Are you sure you want to delete this part?')) {
        // Remove both the visible part row and its associated hidden inputs
        $(`#${partId}`).remove();
        $(`#hidden-${partId}`).remove();
        
        // Show success message
        const successAlert = document.getElementById('successAlert');
        document.getElementById('successMessage').textContent = 'Part deleted successfully';
        successAlert.classList.add('show');
        successAlert.style.display = 'block';
        
        // Auto-dismiss after 2 seconds
        setTimeout(() => {
            successAlert.classList.remove('show');
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 150);
        }, 2000);
    }
}

// Function to validate email format with detailed feedback
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
    const partInput = $("#modalPartDesc");
    const partSuggestions = $(".part-suggestions");

    // Real-time part name autocomplete
    partInput.on('input', function() {
        clearTimeout(partTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            partSuggestions.empty().hide();
            return;
        }

        // Fetch matching parts from the database
        partTimeout = setTimeout(function() {
            $.ajax({
                url: '../models/invoice_model.php',
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

    // Handle part selection from autocomplete dropdown
    $(document).on('click', '.part-option', function() {
        const partDesc = $(this).text();
        partInput.val(partDesc);
        partSuggestions.empty().hide();
    });

    // Close part suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.modal-body').length) {
            partSuggestions.empty().hide();
        }
    });
});

// Email validation function
function validateEmailFormat(input) {
    const email = input.value;
    const feedback = input.nextElementSibling;
    
    if (!email) {
        input.setCustomValidity('');
        feedback.style.display = 'none';
        return true;
    }
    
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/;
    const isValid = emailRegex.test(email);
    
    if (!isValid) {
        input.setCustomValidity('Please enter a valid email address');
        feedback.textContent = 'Please enter a valid email address (e.g., example@domain.com)';
        feedback.style.display = 'block';
    } else {
        input.setCustomValidity('');
        feedback.style.display = 'none';
    }
    
    return isValid;
}

// Form validation on submit
$("#invoiceForm").submit(function(e) {
    const emailInput = document.getElementById('supplierEmail');
    if (!validateEmailFormat(emailInput)) {
        e.preventDefault();
        return false;
    }

    let isValid = true;
    let errorMessage = "";

    // Check if either phone or email is provided
    if(!$("#supplierPhone").val() && !$("#supplierEmail").val()) {
        isValid = false;
        errorMessage += "Either phone or email is required.\n";
    }

    // Check if at least one part exists
    if($(".part-row").length < 1) {
        isValid = false;
        errorMessage += "At least one part is required.\n";
    }

    if(!isValid) {
        e.preventDefault();
        alert(errorMessage);
        return false;
    }

    // If supplier was modified, update their information
    if($("#supplierID").val()) {
        const supplierData = {
            supplierID: $("#supplierID").val(),
            name: $("#supplier").val(),
            phone: $("#supplierPhone").val(),
            email: $("#supplierEmail").val()
        };

        $.ajax({
            url: '../models/invoice_model.php',
            method: 'POST',
            data: supplierData,
            success: function(response) {
                const result = JSON.parse(response);
                if(result.status === 'error') {
                    alert('Error updating supplier: ' + result.message);
                }
            }
        });
    }
});
</script>

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

/* Styles for part rows */
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

/* Styles for modal */
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

/* Styles for validation feedback */
.invalid-feedback {
    color: #dc3545;
    font-size: 80%;
    margin-top: 0.25rem;
}

input:invalid {
    border-color: #ced4da !important;  /* Same as Bootstrap's default border color */
    box-shadow: none !important;
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

/* Add other styles below */
</style>
</body>
</html>
