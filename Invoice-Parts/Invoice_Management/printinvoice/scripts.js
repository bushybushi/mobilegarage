/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/


// Set to store selected invoice IDs
let selectedInvoiceIds = new Set();
let currentPage = 1;

// Function to update selection count
function updateSelectionCount() {
    const count = selectedInvoiceIds.size;
    const element = document.getElementById('selectionCount');
    if (element) {
        element.textContent = `${count} invoice(s) selected`;
    }
}

// Function to handle invoice selection
function toggleInvoiceSelection(invoiceId, checkbox) {
    if (checkbox.checked) {
        selectedInvoiceIds.add(invoiceId);
    } else {
        selectedInvoiceIds.delete(invoiceId);
    }
    updateSelectionCount();
}

// Function to print all invoices
function printAllInvoices() {
    fetch('../printinvoice/PrintInvoicesList.php')
        .then(response => response.text())
        .then(html => {
            // Create an iframe
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            // Write content to iframe
            iframe.contentWindow.document.write(html);
            iframe.contentWindow.document.close();
            
            // Wait for content to load then print
            iframe.onload = function() {
                iframe.contentWindow.print();
                // Remove the iframe after printing
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 500);
            };
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading print preview');
        });
}

// Function to print selected invoices
function printSelectedInvoices() {
    if (selectedInvoiceIds.size === 0) {
        alert('Please select at least one invoice to print');
        return;
    }

    const ids = Array.from(selectedInvoiceIds).join(',');
    fetch(`../printinvoice/PrintSelectedInvoices.php?ids=${ids}`)
        .then(response => response.text())
        .then(html => {
            // Create an iframe
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            // Write content to iframe
            iframe.contentWindow.document.write(html);
            iframe.contentWindow.document.close();
            
            // Wait for content to load then print
            iframe.onload = function() {
                iframe.contentWindow.print();
                // Remove the iframe after printing
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 500);
            };
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading print preview');
        });
}

// Function to update pagination controls
function updatePagination(totalPages, currentPage) {
    const paginationContainer = document.getElementById('printModalPagination');
    if (!paginationContainer) return;

    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }

    // Next button
    html += `
        <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    `;

    paginationContainer.innerHTML = html;
}

// Function to change page
function changePage(page) {
    currentPage = page;
    loadPrintInvoices();
}

// Function to load invoices in the print modal
function loadPrintInvoices() {
    const tableBody = document.querySelector('#invoicesTable');
    if (!tableBody) return;

    // Clear existing content
    tableBody.innerHTML = '';

    // Add loading indicator
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Loading invoices...</td></tr>';

    // Fetch invoices data
    fetch(`../printinvoice/get_print_invoices.php?page=${currentPage}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            tableBody.innerHTML = html;
            
            // Extract pagination info
            const paginationInfo = document.getElementById('paginationInfo');
            if (paginationInfo) {
                const totalPages = parseInt(paginationInfo.dataset.totalPages);
                const currentPage = parseInt(paginationInfo.dataset.currentPage);
                updatePagination(totalPages, currentPage);
                paginationInfo.remove(); // Remove the info div as it's no longer needed
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Error loading invoices</td></tr>';
        });
}

// Function to select/deselect all visible invoices
function toggleAllInvoices(checkbox) {
    // Only select checkboxes from visible rows
    const checkboxes = document.querySelectorAll('#invoicesTable tr:not([style*="display: none"]) .print-invoice-select');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        const invoiceId = parseInt(cb.getAttribute('onchange').match(/\d+/)[0]);
        if (checkbox.checked) {
            selectedInvoiceIds.add(invoiceId);
        } else {
            selectedInvoiceIds.delete(invoiceId);
        }
    });
    updateSelectionCount();
}

// Function to handle search and filter
function handleSearchAndFilter() {
    const searchText = document.getElementById('printSearch').value.toLowerCase();
    const filterType = document.getElementById('printFilter').value;
    const rows = document.querySelectorAll('#invoicesTable tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return; // Skip header row

        let show = false;
        if (searchText === '') {
            show = true;
        } else {
            switch(filterType) {
                case 'invoice_number':
                    show = cells[1].textContent.toLowerCase().includes(searchText);
                    break;
                case 'supplier':
                    show = cells[3].textContent.toLowerCase().includes(searchText);
                    break;
                case 'total':
                    show = cells[6].textContent.toLowerCase().includes(searchText);
                    break;
                case 'vat':
                    show = cells[7].textContent.toLowerCase().includes(searchText);
                    break;
                default:
                    show = Array.from(cells).some(cell => 
                        cell.textContent.toLowerCase().includes(searchText)
                    );
            }
        }

        row.style.display = show ? '' : 'none';
    });

    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('select-all-visible');
    if (selectAllCheckbox) {
        const visibleCheckboxes = document.querySelectorAll('.print-invoice-select:not([style*="display: none"])');
        const checkedVisibleCheckboxes = document.querySelectorAll('.print-invoice-select:checked:not([style*="display: none"])');
        selectAllCheckbox.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisibleCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedVisibleCheckboxes.length > 0 && checkedVisibleCheckboxes.length < visibleCheckboxes.length;
    }
}

// Initialize when the print modal is shown
document.addEventListener('DOMContentLoaded', function() {
    const printModal = document.getElementById('printModal');
    if (printModal) {
        // Use jQuery to handle the modal show event since we're using Bootstrap's modal
        $('#printModal').on('show.bs.modal', function() {
            // Reset to first page
            currentPage = 1;
            
            // Clear previous selections
            selectedInvoiceIds.clear();
            updateSelectionCount();
            
            // Reset search and filter
            document.getElementById('printSearch').value = '';
            document.getElementById('printFilter').value = 'all';
            
            // Load invoices
            loadPrintInvoices();
        });

        // Add event listeners for search and filter
        document.getElementById('printSearch').addEventListener('keyup', handleSearchAndFilter);
        document.getElementById('printFilter').addEventListener('change', handleSearchAndFilter);
    }
}); 