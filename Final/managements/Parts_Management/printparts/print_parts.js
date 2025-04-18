// Global variable to store selected part IDs
let selectedPartsIds = new Set();

// Function to update selection count
function updateSelectionCount() {
    const selectedCount = selectedPartsIds.size;
    $('#selectionCount').text(selectedCount + ' part(s) selected');
    $('#clearSelectionsBtn').toggle(selectedCount > 0);
}

// Function to clear all selections
function clearPrintSelections() {
    selectedPartsIds.clear();
    $('.print-part-select').prop('checked', false);
    $('#select-all-visible').prop('checked', false);
    updateSelectionCount();
}

// Function to toggle all parts
function toggleAllParts(checkbox) {
    const isChecked = $(checkbox).prop('checked');
    $('#printPartsTable tbody tr:visible').each(function() {
        const partId = $(this).data('part-id');
        const partCheckbox = $(this).find('.print-part-select');
        partCheckbox.prop('checked', isChecked);
        if (isChecked) {
            selectedPartsIds.add(partId);
        } else {
            selectedPartsIds.delete(partId);
        }
    });
    updateSelectionCount();
}

// Function to load parts into print modal
function loadPrintModalPage() {
    $.ajax({
        url: '../printparts/get_print_parts.php',
        method: 'GET',
        success: function(response) {
            $('#partsTable').html(response);
            updateSelectionCount();
            
            // Restore checkbox states
            $('#partsTable tr').each(function() {
                const partId = $(this).data('part-id');
                if (selectedPartsIds.has(partId)) {
                    $(this).find('.print-part-select').prop('checked', true);
                }
            });
        },
        error: function() {
            alert('Error loading parts. Please try again.');
        }
    });
}

// Function to print all parts
function printAllParts() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '../printparts/PrintPartsList.php';
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
    $('#printModal').modal('hide');
}

// Function to print selected parts
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

// Initialize print modal
$(document).ready(function() {
    // Load parts when modal opens
    $('#printModal').on('show.bs.modal', function() {
        selectedPartsIds.clear();
        updateSelectionCount();
        loadPrintModalPage();
    });

    // Handle search functionality
    $('#printSearch').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        var filterType = $('#printFilter').val();
        
        $('#printPartsTable tbody tr').each(function() {
            var row = $(this);
            var show = false;
            
            if (searchText === '') {
                show = true;
            } else {
                switch(filterType) {
                    case 'part_number':
                        show = row.find('td:eq(1)').text().toLowerCase().includes(searchText);
                        break;
                    case 'description':
                        show = row.find('td:eq(2)').text().toLowerCase().includes(searchText);
                        break;
                    case 'supplier':
                        show = row.find('td:eq(4)').text().toLowerCase().includes(searchText);
                        break;
                    case 'price':
                        show = row.find('td:eq(5)').text().replace('€', '').trim().toLowerCase().includes(searchText);
                        break;
                    case 'stock':
                        show = row.find('td:eq(7)').text().toLowerCase().includes(searchText);
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

    // Handle individual checkbox changes
    $(document).on('change', '.print-part-select', function() {
        const partId = $(this).closest('tr').data('part-id');
        if ($(this).prop('checked')) {
            selectedPartsIds.add(partId);
        } else {
            selectedPartsIds.delete(partId);
        }
        updateSelectionCount();
    });
}); 