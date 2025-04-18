// Function to open customer view form
function openForm(username) {
    $.get('customer_view.php', { id: username }, function(response) {
        $('#dynamicContent').html(response);
    });
}

// Function to print customer details
function PrintCustomer() {
    const customerId = $('#customerId').val() || sessionStorage.getItem('customerId');
    
    if (!customerId) {
        console.error('Customer ID not found');
        return;
    }
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    
    iframe.src = 'print/PrintCustomerView.php?id=' + customerId;
    
    iframe.onload = function() {
        try {
            iframe.contentWindow.print();
        } catch (e) {
            console.error('Print error:', e);
        }
        
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    };
}

// Function to handle customer deletion steps
let deleteStep = 1;
let deleteCars = false;
let deleteJobCards = false;

function confirmDelete() {
    deleteStep = 1;
    deleteCars = false;
    deleteJobCards = false;
    $('#deleteModalMessage').text('Are you sure you want to delete this customer?');
    $('#confirmDeleteBtn').text('Delete');
    $('#noDeleteBtn').hide();
    $('#deleteModal').modal('show');
}

// Function to delete a customer
function deleteCustomer() {
    const customerId = $('#customerId').val() || sessionStorage.getItem('customerId');
    
    if (!customerId) {
        console.error('Customer ID not found');
        return;
    }
    
    $.ajax({
        url: '../controllers/delete_customer_controller.php',
        method: 'POST',
        data: {
            id: customerId,
            deleteCars: deleteCars,
            deleteJobCards: deleteJobCards
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Close the delete modal
                $('#deleteModal').modal('hide');
                
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>${data.message}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                `;
                document.querySelector('.form-container').insertBefore(successAlert, document.querySelector('.form-container').firstChild);
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Redirect after showing the message
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showErrorMessage(data.message || 'Error deleting customer');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', {xhr, status, error});
            showErrorMessage('Error deleting customer: ' + error);
        }
    });
}

function loadEditForm(customerId) {
    $.get('edit_customer.php', { id: customerId }, function(response) {
        $('#dynamicContent').html(response);
    });
}


// Document ready function to initialize event handlers
$(document).ready(function() {
    // Check if we should open the add customer form
    if (sessionStorage.getItem('openAddCustomerForm') === 'true') {
        // Clear the flag immediately
        sessionStorage.removeItem('openAddCustomerForm');
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function() {
            $.get('add_customer_form.php', function(response) {
                $('#dynamicContent').html(response);
            });
        }, 100);
    }

    // Initialize Bootstrap modals
    if ($('#carDetailsModal').length) {
        $('#carDetailsModal').modal({
            show: false,
            backdrop: 'static',
            keyboard: false
        });
    }
    
    if ($('#deleteModal').length) {
        $('#deleteModal').modal({
            show: false,
            backdrop: 'static',
            keyboard: false
        });
    }

    $('#addnewcustomer-link').on('click', function(e) {
        e.preventDefault();
        $.get('add_customer_form.php', function(response) {
            $('#dynamicContent').html(response);
        });
    });

    // Add click handler for confirm delete button
    $(document).on('click', '#confirmDeleteBtn', function() {
        const customerId = $('#customerId').val();
        
        if (deleteStep === 1) {
            // First check if customer has cars with job cards
            $.ajax({
                url: '../controllers/check_customer_cars_job_cards.php',
                type: 'POST',
                data: { customerId: customerId },
                dataType: 'json',
                success: function(response) {
                    if (response.hasJobCards) {
                        $('#deleteModalMessage').text('This customer has cars with associated job cards. Do you want to delete the cars and their job cards as well?');
                        $('#noDeleteBtn').show();
                        $('#confirmDeleteBtn').text('Yes, Delete All');
                        deleteStep = 2;
                    } else {
                        // If no job cards, ask about deleting cars
                        $('#deleteModalMessage').text('Do you want to delete the customer\'s cars as well?');
                        $('#noDeleteBtn').show();
                        $('#confirmDeleteBtn').text('Yes, Delete Cars');
                        deleteStep = 2;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking job cards:', error);
                    alert('Error checking job cards: ' + error);
                }
            });
        } else if (deleteStep === 2) {
            deleteCars = true;
            deleteJobCards = true;
            deleteCustomer();
        }
    });

    // Add click handler for No button
    $(document).on('click', '#noDeleteBtn', function() {
        deleteCars = false;
        deleteJobCards = false;
        deleteCustomer();
    });
}); 