// Handle Add New Customer button click
$(document).ready(function() {
    $('#addnewcustomer-link').on('click', function(e) {
        e.preventDefault();
        $.get('add_customer_form.php', function(response) {
            document.body.innerHTML = response;
            
            // Re-attach the add/remove field functions
            window.addAddressField = function() {
                const container = document.getElementById('addresses');
                const newField = document.createElement('div');
                newField.className = 'form-group';
                newField.innerHTML = `
                    <label for="address[]">Address</label>
                    <div class="input-group">
                        <input type="text" name="address[]" class="form-control" style="padding-right: 80px;">
                        <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                            <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newField);
            };

            window.addPhoneNumberField = function() {
                const container = document.getElementById('phoneNumbers');
                const newField = document.createElement('div');
                newField.className = 'form-group';
                newField.innerHTML = `
                    <label for="phoneNumber[]">Phone Number</label>
                    <div class="input-group">
                        <input type="tel" name="phoneNumber[]" class="form-control" required style="padding-right: 80px;">
                        <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                            <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newField);
            };

            window.addEmailAddressField = function() {
                const container = document.getElementById('emailAddresses');
                const newField = document.createElement('div');
                newField.className = 'form-group';
                newField.innerHTML = `
                    <label for="emailAddress[]">Email Address</label>
                    <div class="input-group">
                        <input type="email" name="emailAddress[]" class="form-control" style="padding-right: 80px;">
                        <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                            <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newField);
            };

            window.removeField = function(button) {
                button.closest('.form-group').remove();
            };
        });
    });
});

// Auto-hide popup after 3 seconds
setTimeout(function() {
    let popup = document.getElementById("customPopup");
    if (popup) {
        popup.style.animation = "fadeOut 0.5s ease-in-out";
        setTimeout(() => popup.remove(), 500);
    }
}, 3000);

// Global variable to store selected customer IDs
let selectedCustomerIds = new Set();

// Function to update selection count
function updateSelectionCount() {
    const selectedCount = selectedCustomerIds.size;
    $('#selectionCount').text(selectedCount + ' customer(s) selected');
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

    // Initialize selection count
    updateSelectionCount();

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

// Function to load print modal page
function loadPrintModalPage(page) {
    $.ajax({
        url: 'print/get_print_customers.php',
        method: 'GET',
        data: { page: page },
        success: function(response) {
            $('#printCustomersTable').html(response);
            
            // Restore selections after loading new page
            $('.print-customer-select').each(function() {
                const customerId = $(this).closest('tr').data('customer-id');
                $(this).prop('checked', selectedCustomerIds.has(customerId));
            });
            
            // Update select all checkbox state
            const totalCheckboxes = $('.print-customer-select').length;
            const checkedCheckboxes = $('.print-customer-select:checked').length;
            $('#printSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            
            // Update pagination active state (only for modal pagination)
            $('.modal-pagination .page-item').removeClass('active');
            $(`.modal-pagination .page-item:nth-child(${page})`).addClass('active');

            // Update selection count
            updateSelectionCount();

            // Add empty rows if needed
            const visibleRows = $('#printCustomersTable tr:not(.empty-row)').length;
            const emptyRows = 10 - visibleRows;
            if (emptyRows > 0) {
                for (let i = 0; i < emptyRows; i++) {
                    $('#printCustomersTable').append(`
                        <tr class="empty-row">
                            <td><input type="checkbox" class="print-customer-select" disabled></td>
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
            alert('Error loading customers. Please try again.');
        }
    });
}

function printAllCustomers() {
    const iframe = document.getElementById('printFrame');
    iframe.src = 'print/PrintCustomerList.php';
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
            case 'Name':
                aValue = a.cells[1].textContent.trim().toLowerCase();
                bValue = b.cells[1].textContent.trim().toLowerCase();
                break;
            case 'Email':
                aValue = a.cells[2].textContent.trim().toLowerCase();
                bValue = b.cells[2].textContent.trim().toLowerCase();
                break;
            case 'Phone':
                aValue = a.cells[3].textContent.trim().toLowerCase();
                bValue = b.cells[3].textContent.trim().toLowerCase();
                break;
            case 'Address':
                aValue = a.cells[4].textContent.trim().toLowerCase();
                bValue = b.cells[4].textContent.trim().toLowerCase();
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

// Initialize sort on page load
$(document).ready(function() {
    $('#selectedSort').text('Name');
    updateSort('Name');
});

// Open customer view form
function openForm(username) {
    $.get('customer_view.php', { id: username }, function(response) {
        document.body.innerHTML = response;
    });
}