// Global variable to store selected customer IDs
let selectedCustomerIds = new Set();

// Function to update selection count
function updateSelectionCount() {
    const selectedCount = selectedCustomerIds.size;
    $('#selectionCount').text(selectedCount + ' customer(s) selected');
    
    // Show/hide Clear button based on selections
    if (selectedCount > 0) {
        $('#clearSelectionsBtn').css('display', 'inline-block');
    } else {
        $('#clearSelectionsBtn').css('display', 'none');
    }
}

// Function to clear all selections
function clearPrintSelections() {
    selectedCustomerIds.clear();
    $('.print-customer-select').prop('checked', false);
    $('#printSelectAll').prop('checked', false);
    updateSelectionCount();
}

// Print Modal Functions
$(document).ready(function() {
    // Handle print select all checkbox
    $('#printSelectAll').change(function() {
        const isChecked = $(this).prop('checked');
        $('.print-customer-select').prop('checked', isChecked);
        
        // Update selectedCustomerIds set
        if (isChecked) {
            $('.print-customer-select').each(function() {
                selectedCustomerIds.add($(this).closest('tr').data('customer-id'));
            });
        } else {
            $('.print-customer-select').each(function() {
                selectedCustomerIds.delete($(this).closest('tr').data('customer-id'));
            });
        }
        
        updateSelectionCount();
    });

    // Handle individual checkbox changes
    $(document).on('change', '.print-customer-select', function() {
        const customerId = $(this).closest('tr').data('customer-id');
        if ($(this).prop('checked')) {
            selectedCustomerIds.add(customerId);
        } else {
            selectedCustomerIds.delete(customerId);
        }
        
        // Update select all checkbox state
        const totalCheckboxes = $('.print-customer-select').length;
        const checkedCheckboxes = $('.print-customer-select:checked').length;
        $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        
        updateSelectionCount();
    });

    // Initialize selection count and Clear button visibility
    updateSelectionCount();

    // Handle modal show event to ensure correct sorting
    $('#printModal').on('show.bs.modal', function() {
        // Use the global sort order from utils.js
        loadPrintModalPage(1);
    });

    // Clear selections when modal is hidden
    $('#printModal').on('hidden.bs.modal', function() {
        selectedCustomerIds.clear();
        $('.print-customer-select').prop('checked', false);
        $('#printSelectAll').prop('checked', false);
        updateSelectionCount();
    });

    // Handle search functionality
    $('#printSearch').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        var filterType = $('#printFilter').val();
        
        $('#printCustomersTable tr').each(function() {
            var row = $(this);
            var show = false;
            
            if (searchText === '') {
                show = true;
            } else {
                switch(filterType) {
                    case 'name':
                        show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                        break;
                    case 'email':
                        show = row.find('td:eq(2)').text().toLowerCase().includes(searchText);
                        break;
                    case 'phone':
                        show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                        break;
                    default:
                        show = row.text().toLowerCase().includes(searchText);
                }
            }
            
            row.toggle(show);
        });
    });

    // Handle filter change
    $('#printFilter').change(function() {
        $('#printSearch').trigger('keyup');
    });
});

function printAllCustomers() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '../views/print/PrintCustomerList.php';
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

function printSelectedCustomers() {
    if (selectedCustomerIds.size === 0) {
        alert('Please select at least one customer to print');
        return;
    }
    
    const iframe = document.getElementById('printFrame');
    iframe.src = 'print/PrintSelectedCustomers.php?ids=' + Array.from(selectedCustomerIds).join(',');
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

// Function to clear selections (only used by Clear button)
function clearPrintSelections() {
    selectedCustomerIds.clear();
    $('.print-customer-select').prop('checked', false);
    $('#printSelectAll').prop('checked', false);
    updateSelectionCount();
}

// Function to update sort text and handle sorting
function updateSort(sortBy) {
    currentSortOrder = sortBy;
    document.getElementById('selectedSort').textContent = sortBy;
    loadPrintModalPage(1); // Reset to first page when sorting changes
}

// Function to load print modal page
function loadPrintModalPage(page) {
    // Use the global sort order from utils.js
    $.ajax({
        url: 'print/get_print_customers.php',
        method: 'GET',
        data: { 
            page: page,
            sort: currentSortOrder
        },
        success: function(response) {
            $('#printCustomersTable').html(response);
            
            // Restore selections after loading new page
            $('.print-customer-select:not([disabled])').each(function() {
                const customerId = $(this).closest('tr').data('customer-id');
                if (selectedCustomerIds.has(customerId)) {
                    $(this).prop('checked', true);
                }
            });
            
            // Update select all checkbox state (only for non-empty rows)
            const totalCheckboxes = $('.print-customer-select:not([disabled])').length;
            const checkedCheckboxes = $('.print-customer-select:checked:not([disabled])').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
            
            // Update pagination active state (only for modal pagination)
            $('.modal-pagination .page-item').removeClass('active');
            $(`.modal-pagination .page-item:nth-child(${page})`).addClass('active');

            // Update selection count
            updateSelectionCount();
        },
        error: function() {
            alert('Error loading customers. Please try again.');
        }
    });
}





