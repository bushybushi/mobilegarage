// Global variables
let selectedInvoiceIds = new Set();
let currentSortOrder = 'Date'; // Default sort order

// Function to update selection count
function updateSelectionCount() {
    const selectedCount = selectedInvoiceIds.size;
    $('#selectionCount').text(selectedCount + ' invoice(s) selected');
    
    // Show/hide Clear button based on selections
    if (selectedCount > 0) {
        $('#clearSelectionsBtn').css('display', 'inline-block');
    } else {
        $('#clearSelectionsBtn').css('display', 'none');
    }
}

// Print Modal Functions
$(document).ready(function() {
    // Handle print select all checkbox
    $('#printSelectAll').change(function() {
        const isChecked = $(this).prop('checked');
        $('.print-invoice-select').prop('checked', isChecked);
        
        // Update selectedInvoiceIds set
        if (isChecked) {
            $('.print-invoice-select').each(function() {
                selectedInvoiceIds.add($(this).closest('tr').data('invoice-id'));
            });
        } else {
            $('.print-invoice-select').each(function() {
                selectedInvoiceIds.delete($(this).closest('tr').data('invoice-id'));
            });
        }
        
        updateSelectionCount();
    });

    // Handle individual checkbox changes
    $(document).on('change', '.print-invoice-select', function() {
        const invoiceId = $(this).closest('tr').data('invoice-id');
        if ($(this).prop('checked')) {
            selectedInvoiceIds.add(invoiceId);
        } else {
            selectedInvoiceIds.delete(invoiceId);
        }
        
        // Update select all checkbox state
        const totalCheckboxes = $('.print-invoice-select').length;
        const checkedCheckboxes = $('.print-invoice-select:checked').length;
        $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        
        updateSelectionCount();
    });

    // Initialize selection count and Clear button visibility
    updateSelectionCount();

    // Handle modal show event to ensure correct sorting
    $('#printModal').on('show.bs.modal', function() {
        loadPrintModalPage(1);
    });

    // Handle search functionality
    $('#printSearch').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        var filterType = $('#printFilter').val();
        
        $('#printInvoicesTable tr').each(function() {
            var row = $(this);
            var show = false;
            
            if (searchText === '') {
                show = true;
            } else {
                switch(filterType) {
                    case 'number':
                        show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                        break;
                    case 'supplier':
                        show = row.find('td:eq(2)').text().toLowerCase().includes(searchText);
                        break;
                    case 'date':
                        show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                        break;
                    case 'amount':
                        show = row.find('td:eq(4)').text().toLowerCase().includes(searchText);
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

function printAllInvoices() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '../views/print/PrintInvoiceList.php';
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

function printSelectedInvoices() {
    if (selectedInvoiceIds.size === 0) {
        alert('Please select at least one invoice to print');
        return;
    }
    
    const iframe = document.getElementById('printFrame');
    iframe.style.display = 'block';
    iframe.src = '../views/print/PrintSelectedInvoices.php?ids=' + Array.from(selectedInvoiceIds).join(',');
    
    iframe.onload = function() {
        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                console.error('Print error:', e);
                alert('Error printing. Please try again.');
            }
            
            setTimeout(function() {
                iframe.style.display = 'none';
            }, 1000);
        }, 1000);
    };
    
    $('#printModal').modal('hide');
}

// Function to clear selections
function clearPrintSelections() {
    selectedInvoiceIds.clear();
    $('.print-invoice-select').prop('checked', false);
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
    $.ajax({
        url: '../views/print/get_print_invoice.php',
        method: 'GET',
        data: { 
            page: page,
            sort: currentSortOrder
        },
        success: function(response) {
            $('#printInvoicesTable').html(response);
            
            // Restore selections after loading new page
            $('.print-invoice-select:not([disabled])').each(function() {
                const invoiceId = $(this).closest('tr').data('invoice-id');
                if (selectedInvoiceIds.has(invoiceId)) {
                    $(this).prop('checked', true);
                }
            });
            
            // Update select all checkbox state
            const totalCheckboxes = $('.print-invoice-select:not([disabled])').length;
            const checkedCheckboxes = $('.print-invoice-select:checked:not([disabled])').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
            
            // Update pagination active state
            $('.modal-pagination .page-item').removeClass('active');
            $(`.modal-pagination .page-item:nth-child(${page})`).addClass('active');

            // Update selection count
            updateSelectionCount();
        },
        error: function() {
            alert('Error loading invoices. Please try again.');
        }
    });
} 