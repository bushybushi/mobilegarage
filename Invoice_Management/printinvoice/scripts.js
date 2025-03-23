// Global variable to store selected invoice IDs
let selectedInvoiceIds = new Set();

// Function to update selection count
function updateSelectionCount() {
    const selectedCount = selectedInvoiceIds.size;
    $('#selectionCount').text(selectedCount + ' invoice(s) selected');
}

// Print Modal Functions
$(document).ready(function() {
    // Handle main table pagination active state
    const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
    $('.main-pagination .page-item').removeClass('active');
    $(`.main-pagination .page-item:nth-child(${parseInt(currentPage) + 1})`).addClass('active');

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

    // Initialize selection count
    updateSelectionCount();

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
                    case 'customer':
                        show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                        break;
                    case 'status':
                        show = row.find('td:eq(5)').text().toLowerCase().includes(searchText);
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

// Function to load print modal page
function loadPrintModalPage(page) {
    $.ajax({
        url: '../printinvoice/get_print_invoices.php',
        method: 'GET',
        data: { page: page },
        success: function(response) {
            $('#printInvoicesTable').html(response);
            
            // Restore selections after loading new page
            $('.print-invoice-select').each(function() {
                const invoiceId = $(this).closest('tr').data('invoice-id');
                $(this).prop('checked', selectedInvoiceIds.has(invoiceId));
            });
            
            // Update select all checkbox state
            const totalCheckboxes = $('.print-invoice-select').length;
            const checkedCheckboxes = $('.print-invoice-select:checked').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            
            // Update pagination active state (only for modal pagination)
            $('.modal-pagination .page-item').removeClass('active');
            $(`.modal-pagination .page-item:nth-child(${page})`).addClass('active');

            // Update selection count
            updateSelectionCount();

            // Add empty rows if needed
            const visibleRows = $('#printInvoicesTable tr:not(.empty-row)').length;
            const emptyRows = 10 - visibleRows;
            if (emptyRows > 0) {
                for (let i = 0; i < emptyRows; i++) {
                    $('#printInvoicesTable').append(`
                        <tr class="empty-row">
                            <td><input type="checkbox" class="print-invoice-select" disabled></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    `);
                }
            }
        },
        error: function() {
            alert('Error loading invoices. Please try again.');
        }
    });
}

function printAllInvoices() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '../printinvoice/PrintInvoiceList.php';
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
    iframe.src = '../printinvoice/PrintSelectedInvoices.php?ids=' + Array.from(selectedInvoiceIds).join(',');
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

// Update sort text and handle sorting
function updateSort(sortBy) {
    // Update the dropdown text
    document.getElementById('selectedSort').textContent = sortBy;
    
    // Get the table body and rows (excluding empty rows)
    const tbody = document.querySelector('table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));
    
    // Sort the rows based on the selected criteria
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortBy) {
            case 'Number':
                aValue = a.cells[1].textContent.trim().toLowerCase();
                bValue = b.cells[1].textContent.trim().toLowerCase();
                break;
            case 'Date':
                aValue = new Date(a.cells[2].textContent.trim());
                bValue = new Date(b.cells[2].textContent.trim());
                return bValue - aValue; // Sort by date descending
            case 'Customer':
                aValue = a.cells[3].textContent.trim().toLowerCase();
                bValue = b.cells[3].textContent.trim().toLowerCase();
                break;
            case 'Total':
                aValue = parseFloat(a.cells[4].textContent.trim().replace(/[^0-9.-]+/g, ''));
                bValue = parseFloat(b.cells[4].textContent.trim().replace(/[^0-9.-]+/g, ''));
                return bValue - aValue; // Sort by amount descending
            case 'Status':
                aValue = a.cells[5].textContent.trim().toLowerCase();
                bValue = b.cells[5].textContent.trim().toLowerCase();
                break;
            default:
                aValue = a.cells[1].textContent.trim().toLowerCase();
                bValue = b.cells[1].textContent.trim().toLowerCase();
        }
        
        return aValue.localeCompare(bValue);
    });
    
    // Get empty rows before clearing tbody
    const emptyRows = Array.from(tbody.querySelectorAll('tr.empty-row'));
    
    // Clear tbody and append sorted rows followed by empty rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    emptyRows.forEach(row => tbody.appendChild(row));
} 