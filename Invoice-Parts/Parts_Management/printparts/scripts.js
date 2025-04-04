/**
 * This JavaScript file handles the functionality for the parts printing system.
 * It manages the selection of parts, search and filtering, pagination,
 * and the actual printing process for both all parts and selected parts.
 */
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

// Set to store IDs of parts selected for printing
// This Set ensures each part ID is only stored once
let selectedPartsIds = new Set();

// Updates the count of selected parts shown to the user
// This function keeps the UI in sync with the user's selections
function updateSelectionCount() {
    const selectedCount = selectedPartsIds.size;
    $('#selectionCount').text(selectedCount + ' part(s) selected');
}

// Initialize print modal functionality
// This runs when the document is fully loaded and ready
$(document).ready(function() {
    // Handle "Select All" checkbox in print modal
    // This allows users to select or deselect all visible parts at once
    $(document).on('change', '#select-all-visible', function() {
        const isChecked = $(this).prop('checked');
        const currentPageRows = $('#printPartsTable tbody tr:visible');
        
        // Only affect visible rows on current page
        currentPageRows.each(function() {
            const checkbox = $(this).find('.print-part-select');
            checkbox.prop('checked', isChecked);
            
            const partId = $(this).data('part-id');
            if (isChecked) {
                selectedPartsIds.add(partId);
            } else {
                // Only remove if it's not selected on another page
                // This prevents accidentally deselecting parts on other pages
                const isSelectedOnOtherPage = $('#printPartsTable tbody tr:not(:visible)')
                    .filter(function() {
                        return $(this).data('part-id') === partId;
                    })
                    .find('.print-part-select')
                    .prop('checked');
                
                if (!isSelectedOnOtherPage) {
                    selectedPartsIds.delete(partId);
                }
            }
        });
        
        updateSelectionCount();
    });

    // Handle individual part checkbox changes
    // This manages the selection state when a user checks or unchecks a single part
    $(document).on('change', '.print-part-select', function() {
        const isChecked = $(this).prop('checked');
        const row = $(this).closest('tr');
        const partId = row.data('part-id');
        
        if (isChecked) {
            selectedPartsIds.add(partId);
        } else {
            selectedPartsIds.delete(partId);
        }
        
        // Update header checkbox state based on current page only
        // This ensures the "Select All" checkbox reflects the state of visible checkboxes
        const currentPageRows = $('#printPartsTable tbody tr:visible');
        const currentPageCheckboxes = currentPageRows.find('.print-part-select');
        const currentPageChecked = currentPageCheckboxes.filter(':checked');
        $('#select-all-visible').prop('checked', currentPageCheckboxes.length > 0 && currentPageCheckboxes.length === currentPageChecked.length);
        
        updateSelectionCount();
    });

    // Handle search functionality in print modal
    // This allows users to filter parts by various criteria
    $('#printSearch').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        var filterType = $('#printFilter').val();
        let visibleCount = 0;
        
        // Store the header if it exists
        const header = $('#printPartsTable thead');
        
        // Check each row against the search criteria
        $('#printPartsTable tbody tr').each(function() {
            var row = $(this);
            var show = false;
            
            if (searchText === '') {
                show = true;
            } else {
                // Apply different search logic based on the selected filter type
                switch(filterType) {
                    case 'description':
                        show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                        break;
                    case 'supplier':
                        show = row.find('td:eq(3)').text().toLowerCase().includes(searchText);
                        break;
                    case 'price_piece':
                        show = row.find('td:eq(4)').text().replace('€', '').trim().toLowerCase().includes(searchText);
                        break;
                    case 'sell_price':
                        show = row.find('td:eq(5)').text().replace('€', '').trim().toLowerCase().includes(searchText);
                        break;
                    case 'stock':
                        show = row.find('td:eq(6)').text().toLowerCase().includes(searchText);
                        break;
                    default:
                        // Search across all columns if no specific filter is selected
                        show = row.find('td').map(function() {
                            return $(this).text().toLowerCase();
                        }).get().some(text => text.includes(searchText));
                }
            }
            
            // Show or hide the row based on the search result
            if (show) {
                visibleCount++;
                row.show();
            } else {
                row.hide();
            }
        });

        // Ensure the header is always visible
        if (header.length) {
            header.show();
        }

        // Update pagination based on visible items
        // This ensures the pagination reflects the current search results
        totalPrintPages = Math.ceil(visibleCount / printItemsPerPage);
        if (currentPrintPage > totalPrintPages) {
            currentPrintPage = Math.max(1, totalPrintPages);
        }
        updatePrintPagination();
        showCurrentPageItems();

        // Update select all checkbox state after search
        // This ensures the "Select All" checkbox reflects the state of visible checkboxes
        const visibleRows = $('#printPartsTable tbody tr:visible');
        const visibleCheckboxes = visibleRows.find('.print-part-select');
        const visibleChecked = visibleCheckboxes.filter(':checked');
        $('#select-all-visible').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length);
    });

    // Handle filter change
    // This triggers the search when the filter type is changed
    $('#printFilter').change(function() {
        $('#printSearch').trigger('keyup');
    });

    // Load initial data when modal opens
    // This resets the selection and loads the parts data
    $('#printModal').on('show.bs.modal', function() {
        selectedPartsIds.clear();
        updateSelectionCount();
        loadPrintModalPage();
    });
});

// Load parts into the print modal
// This function fetches the parts data from the server and displays it in the modal
function loadPrintModalPage() {
    $.ajax({
        url: '../printparts/get_print_parts.php',
        method: 'GET',
        success: function(response) {
            $('.table-responsive').html(response);
            updateSelectionCount();
            
            // Restore checkbox states after loading
            // This ensures selections persist when navigating between pages
            $('#printPartsTable tbody tr').each(function() {
                const partId = $(this).data('part-id');
                if (selectedPartsIds.has(partId)) {
                    $(this).find('.print-part-select').prop('checked', true);
                }
            });
            
            // Update select all checkbox state
            // This ensures the "Select All" checkbox reflects the state of visible checkboxes
            const visibleRows = $('#printPartsTable tbody tr:visible');
            const visibleCheckboxes = visibleRows.find('.print-part-select');
            const visibleChecked = visibleCheckboxes.filter(':checked');
            $('#select-all-visible').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length);
        },
        error: function() {
            alert('Error loading parts. Please try again.');
        }
    });
}

// Print all parts regardless of selection
// This function opens the PrintPartsList.php in an iframe and triggers the print dialog
function printAllParts() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '../printparts/PrintPartsList.php';
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

// Print only the selected parts
// This function opens the PrintSelectedParts.php with the selected IDs in an iframe and triggers the print dialog
function printSelectedParts() {
    if (selectedPartsIds.size === 0) {
        alert('Please select at least one part to print');
        return;
    }
    
    const iframe = document.getElementById('printFrame');
    iframe.src = '../printparts/PrintSelectedParts.php?ids=' + Array.from(selectedPartsIds).join(',');
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

// Update sort text and handle table sorting
// This function sorts the table based on the selected column
function updateSort(sortBy) {
    // Update the dropdown text to show current sort method
    document.getElementById('selectedSort').textContent = sortBy;
    
    // Get table body and all non-empty rows
    const tbody = document.querySelector('table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));
    
    // Sort the rows based on the selected criteria
    rows.sort((a, b) => {
        let aValue, bValue;
        
        // Extract values to compare based on sort type
        // This handles different data types appropriately (text, dates, numbers)
        switch(sortBy) {
            case 'Description':
                aValue = a.cells[1].textContent.trim().toLowerCase();
                bValue = b.cells[1].textContent.trim().toLowerCase();
                return aValue.localeCompare(bValue);
            case 'Date':
                aValue = new Date(a.cells[2].textContent.trim());
                bValue = new Date(b.cells[2].textContent.trim());
                return bValue - aValue; // Sort by date descending
            case 'Supplier':
                aValue = a.cells[3].textContent.trim().toLowerCase();
                bValue = b.cells[3].textContent.trim().toLowerCase();
                return aValue.localeCompare(bValue);
            case 'Price':
                aValue = parseFloat(a.cells[4].textContent.replace('€', '').trim());
                bValue = parseFloat(b.cells[4].textContent.replace('€', '').trim());
                return aValue - bValue;
            case 'VAT':
                aValue = parseFloat(a.cells[5].textContent.replace('%', '').trim());
                bValue = parseFloat(b.cells[5].textContent.replace('%', '').trim());
                return aValue - bValue;
            case 'Stock':
                aValue = parseInt(a.cells[6].textContent.trim());
                bValue = parseInt(b.cells[6].textContent.trim());
                return aValue - bValue;
            default:
                return 0;
        }
    });
    
    // Save empty rows before clearing table
    // This preserves any empty rows that might be used for messaging
    const emptyRows = Array.from(tbody.querySelectorAll('tr.empty-row'));
    
    // Clear table and add sorted rows followed by empty rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    emptyRows.forEach(row => tbody.appendChild(row));
} 