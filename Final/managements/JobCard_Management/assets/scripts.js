// Initialize form functionality when the page loads
$(document).ready(function() {
    // Initialize part search for all existing part fields
    const partSearchInputs = document.querySelectorAll('.part-search');
    partSearchInputs.forEach(input => {
        const selectElement = input.closest('.input-group').querySelector('.part-select');
        setupPartSearch(input, selectElement);
    });

    // Initialize customer search functionality
    initializeCustomerSearch();
    
    // Initialize photo preview functionality
    setupPhotoPreview();
});


// Function to setup part search functionality
function setupPartSearch(searchInput, selectElement) {
    const searchResultsDiv = searchInput.closest('.position-relative').querySelector('.list-group');
    const partField = searchInput.closest('.input-group');
    const priceInput = partField.querySelector('input[name="partPrices[]"]');
    const quantityInput = partField.querySelector('input[name="partQuantities[]"]');
    const hiddenInput = partField.querySelector('input[name="parts[]"]');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Get all options from the select
        const options = Array.from(selectElement.options).slice(1); // Skip the first "Select Part" option
        
        // Create search results
        searchResultsDiv.innerHTML = '';
        
        if (query.length > 0) {
            // Filter options based on partial matches
            const filteredOptions = options.filter(option => {
                const partDesc = option.text.split(' (Stock:')[0].toLowerCase();
                const searchTerms = query.toLowerCase().split(' ');
                
                // Check if any search term matches any part of the description
                return searchTerms.some(term => {
                    return partDesc.includes(term);
                });
            });
            
            if (filteredOptions.length > 0) {
                // Sort filtered options by description
                filteredOptions.sort((a, b) => {
                    const descA = a.text.split(' (Stock:')[0].toLowerCase();
                    const descB = b.text.split(' (Stock:')[0].toLowerCase();
                    return descA.localeCompare(descB);
                });

                // Group parts by description to find duplicates
                const partsByDesc = {};
                filteredOptions.forEach(option => {
                    const desc = option.text.split(' (Stock:')[0]; // Get description without stock info
                    if (!partsByDesc[desc]) {
                        partsByDesc[desc] = [];
                    }
                    partsByDesc[desc].push(option);
                });

                // Create result items
                filteredOptions.forEach(option => {
                    const resultItem = document.createElement('a');
                    resultItem.href = '#';
                    resultItem.className = 'list-group-item list-group-item-action';
                    
                    // Get description without stock info
                    const desc = option.text.split(' (Stock:')[0];
                    const stock = option.text.match(/Stock: (\d+)/)[1];
                    const dateCreated = option.dataset.dateCreated || 'N/A';
                    const supplier = option.dataset.supplier || 'N/A';
                    
                    // Check if this is a duplicate
                    const isDuplicate = partsByDesc[desc].length > 1;
                    
                    // Create display text with date created and supplier if duplicate
                    const displayText = isDuplicate ? 
                        `${desc} (Stock: ${stock}) (Created: ${dateCreated}) (Supplier: ${supplier})` : 
                        `${desc} (Stock: ${stock})`;
                    
                    resultItem.textContent = displayText;
                    resultItem.dataset.id = option.value;
                    resultItem.dataset.stock = option.dataset.stock;
                    resultItem.dataset.price = option.dataset.price;
                    resultItem.dataset.text = displayText;
                    
                    // Add visual indicator for duplicates
                    if (isDuplicate) {
                        resultItem.style.borderLeft = '4px solid #007bff';
                        resultItem.title = 'This part has duplicates. Check the creation date and supplier to distinguish between them.';
                    }
                    
                    // Check if this part is already added with stock of 1
                    const stockNum = parseInt(option.dataset.stock);
                    const partId = parseInt(option.value);
                    const currentPartId = hiddenInput ? parseInt(hiddenInput.value) : null;
                    
                    // If stock is 1 and it's already added, disable the option
                    if (stockNum === 1 && partId !== currentPartId) {
                        const existingPartInputs = document.querySelectorAll('input[name="parts[]"]');
                        let isAlreadyAdded = false;
                        existingPartInputs.forEach(input => {
                            if (parseInt(input.value) === partId) {
                                isAlreadyAdded = true;
                            }
                        });
                        
                        if (isAlreadyAdded) {
                            resultItem.className += ' disabled text-muted';
                            resultItem.style.pointerEvents = 'none';
                            resultItem.title = 'This part is already added and has only 1 in stock';
                        }
                    }
                    // If stock is 0 or less and it's not a currently selected part, disable it
                    else if (stockNum <= 0 && partId !== currentPartId) {
                        resultItem.className += ' disabled text-muted';
                        resultItem.style.pointerEvents = 'none';
                        resultItem.title = 'Out of stock';
                    }
                    
                    resultItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Check if part is already added with stock of 1
                        const stock = parseInt(this.dataset.stock);
                        const partId = parseInt(this.dataset.id);
                        
                        if (stock === 1) {
                            const existingPartInputs = document.querySelectorAll('input[name="parts[]"]');
                            let isAlreadyAdded = false;
                            existingPartInputs.forEach(input => {
                                if (parseInt(input.value) === partId) {
                                    isAlreadyAdded = true;
                                }
                            });
                            
                            if (isAlreadyAdded) {
                                showValidationMessage('This part is already added and has only 1 in stock');
                                return;
                            }
                        }
                        
                        selectElement.value = this.dataset.id;
                        searchInput.value = this.dataset.text;
                        searchResultsDiv.innerHTML = '';
                        
                        // Update hidden input with selected part ID
                        if (hiddenInput) {
                            hiddenInput.value = this.dataset.id;
                        }
                        
                        // Set the price input value
                        if (priceInput && this.dataset.price) {
                            priceInput.value = this.dataset.price;
                        }

                        // Set max quantity based on stock
                        if (quantityInput && this.dataset.stock) {
                            const stock = parseInt(this.dataset.stock);
                            quantityInput.max = stock;
                            quantityInput.title = `Maximum available: ${stock}`;
                            
                            // If current quantity is more than stock, adjust it
                            if (parseInt(quantityInput.value) > stock) {
                                quantityInput.value = stock;
                            }
                        }
                        
                        // Trigger the change event to update price and stock
                        const changeEvent = new Event('change');
                        selectElement.dispatchEvent(changeEvent);
                        
                        // Trigger total calculation
                        calculateTotal();
                    });
                    
                    searchResultsDiv.appendChild(resultItem);
                });
            } else {
                const noResults = document.createElement('div');
                noResults.className = 'list-group-item text-muted';
                noResults.textContent = 'No parts found';
                searchResultsDiv.appendChild(noResults);
            }
        }
    });
    
    // Add event listener to validate quantity against stock
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const max = parseInt(this.max) || 0;
            const value = parseInt(this.value) || 0;
            
            if (value > max) {
                showValidationMessage(`Cannot exceed available stock of ${max} units`);
                this.value = max;
                calculateTotal();
            }
        });
    }
    
    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
            searchResultsDiv.innerHTML = '';
        }
    });
}

// Function to format price with 2 decimal places
function formatPrice(input) {
    const value = parseFloat(input.value) || 0;
    input.value = value.toFixed(2);
}

// Function to remove part field
function removePart(button) {
    const partField = button.closest('.input-group');
    if (partField) {
        partField.remove();
        calculateTotal();
    }
}

// Function to handle part removal confirmation
function confirmRemovePart(partId, quantity, returnToStock) {
    const modal = document.getElementById('returnToStockModal');
    const partFieldId = modal.dataset.partFieldId;
    const partField = document.getElementById(partFieldId);

    if (returnToStock) {
        // Send request to update stock
        fetch('../controllers/update_part_stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `partId=${partId}&quantity=${quantity}&action=return`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the part field
                if (partField) {
                    partField.remove();
                    calculateTotal();
                }
                // Update the stock in all part selects
                updatePartStock(partId, data.newStock);
            } else {
                showValidationMessage('Error returning part to stock: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showValidationMessage('Error returning part to stock');
        });
    } else {
        // Just remove the part field without returning to stock
        if (partField) {
            partField.remove();
            calculateTotal();
        }
    }

    // Hide and remove the modal
    $('#returnToStockModal').modal('hide');
    setTimeout(() => {
        modal.remove();
    }, 500);
}

// Function to update stock in all part selects
function updatePartStock(partId, newStock) {
    // Update stock in template and all existing selects
    const selects = document.querySelectorAll('#partSelectTemplate, .part-select');
    selects.forEach(select => {
        const option = select.querySelector(`option[value="${partId}"]`);
        if (option) {
            // Update the stock in the option text
            const partDesc = option.text.split(' (Stock:')[0];
            option.text = `${partDesc} (Stock: ${newStock})`;
            
            // Update data-stock attribute
            option.dataset.stock = newStock;
            
            // Enable/disable based on new stock
            if (newStock <= 0) {
                option.disabled = true;
                option.text += ' - Out of Stock';
            } else {
                option.disabled = false;
            }
        }
    });
}

// Function to show validation message modal
function showValidationMessage(message) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('validationModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'validationModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Validation Message</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="validationMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Set the message and show the modal
    document.getElementById('validationMessage').textContent = message;
    $(modal).modal('show');
}

// Function to validate quantity
function validateQuantity(input) {
    const max = parseInt(input.max) || 0;
    const value = parseInt(input.value) || 0;
    
    if (value <= 0) {
        showValidationMessage('Quantity must be at least 1');
        input.value = 1;
        return false;
    } else if (value > max) {
        showValidationMessage(`Cannot exceed available stock of ${max} units`);
        input.value = max;
        return false;
    }
    calculateTotal();
    return true;
}

// Function to update part price and stock when a part is selected
function updatePartPrice(selectElement) {
    const partId = selectElement.value;
    if (!partId) return;
    
    // Find the corresponding part field container
    const partField = selectElement.closest('.input-group');
    const priceInput = partField.querySelector('input[name="partPrices[]"]');
    const quantityInput = partField.querySelector('input[name="partQuantities[]"]');
    const hiddenInput = partField.querySelector('input[name="parts[]"]');
    
    // Get stock and price from selected option
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const stock = parseInt(selectedOption.dataset.stock);
    const price = parseFloat(selectedOption.dataset.price);
    
    // Update quantity input max attribute and title
    if (quantityInput) {
        quantityInput.max = stock;
        quantityInput.title = `Maximum available: ${stock}`;
        
        // If current quantity is more than stock, adjust it
        const currentQty = parseInt(quantityInput.value) || 0;
        if (currentQty > stock) {
            showValidationMessage(`Cannot exceed available stock of ${stock} units`);
            quantityInput.value = stock;
        }
    }
    
    // Update price input with 2 decimal places
    if (priceInput && price) {
        priceInput.value = price.toFixed(2);
    }
    
    // Update hidden input
    if (hiddenInput) {
        hiddenInput.value = partId;
    }
    
    calculateTotal();
}

// Function to calculate total costs
function calculateTotal() {
    const driveCosts = parseFloat(document.getElementById('driveCosts')?.value) || 0;
    let partPricesTotal = 0;
    
    // Get all part prices and quantities
    const partPrices = document.getElementsByName('partPrices[]');
    const partQuantities = document.getElementsByName('partQuantities[]');
    
    for (let i = 0; i < partPrices.length; i++) {
        const price = parseFloat(partPrices[i].value) || 0;
        const quantity = parseInt(partQuantities[i]?.value) || 1;
        partPricesTotal += price * quantity;
    }
    
    // Update parts total display
    const partsTotalElement = document.getElementById('partsTotal');
    if (partsTotalElement) {
        partsTotalElement.textContent = `Total parts: ${partPricesTotal.toFixed(2)} €`;
    }
    
    const total = driveCosts + partPricesTotal;
    
    // Update both the total costs field and the calculated total display
    const totalCostsField = document.getElementById('totalCosts');
    if (totalCostsField) {
        totalCostsField.value = total.toFixed(2);
        
        const totalCostsGroup = totalCostsField.closest('.form-group');
        let calculatedTotalElement = totalCostsGroup.querySelector('.calculated-total');
        
        if (!calculatedTotalElement) {
            calculatedTotalElement = document.createElement('small');
            calculatedTotalElement.className = 'form-text text-muted calculated-total';
            totalCostsGroup.appendChild(calculatedTotalElement);
        }
        
        calculatedTotalElement.textContent = `Calculated total: ${total.toFixed(2)} €`;
    }
}

// Add event listeners for cost calculation
document.addEventListener('DOMContentLoaded', function() {
    const driveCostsInput = document.getElementById('driveCosts');
    if (driveCostsInput) {
        driveCostsInput.addEventListener('input', calculateTotal);
    }

    // Function to validate quantity input
    function validateQuantityInput(input) {
        const max = parseInt(input.max) || 0;
        const value = parseInt(input.value) || 0;
        
        if (value <= 0) {
            showValidationMessage('Quantity must be at least 1');
            input.value = 1;
        } else if (value > max) {
            showValidationMessage(`Cannot exceed available stock of ${max} units`);
            input.value = max;
        }
        calculateTotal();
    }

    // Function to setup quantity validation for an input
    function setupQuantityValidation(input) {
        input.addEventListener('input', function() {
            validateQuantityInput(this);
        });
        
        // Also validate on blur
        input.addEventListener('blur', function() {
            validateQuantityInput(this);
        });
    }

    // Setup validation for all existing quantity inputs
    document.querySelectorAll('input[name="partQuantities[]"]').forEach(setupQuantityValidation);

    const partsContainer = document.getElementById('partsContainer');
    if (partsContainer) {
        // Listen for changes in quantities and prices in the parts container
        partsContainer.addEventListener('input', function(e) {
            if (e.target.matches('input[name="partQuantities[]"]')) {
                validateQuantityInput(e.target);
            } else if (e.target.matches('input[name="partPrices[]"]')) {
                calculateTotal();
            }
        });

        // Also listen for new parts being added
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.querySelectorAll) {
                            node.querySelectorAll('input[name="partQuantities[]"]').forEach(setupQuantityValidation);
                        }
                    });
                }
            });
        });

        observer.observe(partsContainer, { childList: true, subtree: true });
        
        // Initial calculation
        calculateTotal();
    }
});

// Function to initialize customer search
function initializeCustomerSearch() {
    const customerSearchInput = document.getElementById('customerSearch');
    const customerSearchResults = document.getElementById('customerSearchResults');
    const customerSelect = document.getElementById('customer');

    if (!customerSearchInput || !customerSearchResults || !customerSelect) {
        console.error('Customer search elements not found');
        return;
    }

    // Store the initial valid value
    let lastValidValue = customerSearchInput.value;

    if (customerSelect) {
        // Auto-populate phone and cars when customer is selected
        customerSelect.addEventListener('change', function() {
            const customerId = this.value;
            if (customerId) {
                // Get customer phone
                fetch(`../controllers/get_customer_phone.php?id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.phone) {
                            document.getElementById('phone').value = data.phone;
                        }
                    })
                    .catch(error => console.error('Error fetching phone:', error));
                
                // Get customer cars
                fetch(`../controllers/get_customer_cars.php?id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Try both possible select elements
                        let carSelect = document.getElementById('carBrandModel') || document.getElementById('carDetails');
                        if (carSelect) {
                            // Clear previous options
                            carSelect.innerHTML = '<option value="">Select Car Brand and Model</option>';
                            
                            if (data.success && data.cars && data.cars.length > 0) {
                                data.cars.forEach(car => {
                                    const option = document.createElement('option');
                                    option.value = car.Brand + ' ' + car.Model;
                                    option.textContent = car.Brand + ' ' + car.Model + ' (' + car.LicenseNr + ')';
                                    option.dataset.license = car.LicenseNr;
                                    carSelect.appendChild(option);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cars:', error);
                    });
            } else {
                // Clear car select if no customer is selected
                let carSelect = document.getElementById('carBrandModel') || document.getElementById('carDetails');
                if (carSelect) {
                    carSelect.innerHTML = '<option value="">Select Car Brand and Model</option>';
                }
                // Clear phone
                document.getElementById('phone').value = '';
            }
        });
    }

    if (customerSearchInput && customerSearchResults && customerSelect) {
        customerSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Get all options from the select
            const options = Array.from(customerSelect.options).slice(1); // Skip the first "Select Customer" option
            
            // Create search results
            customerSearchResults.innerHTML = '';
            
            if (query.length > 0) {
                // Filter options based on the first letter of each word in the customer name
                const filteredOptions = options.filter(option => {
                    const customerName = option.text.toLowerCase();
                    const searchTerms = query.toLowerCase().split(' ');
                    
                    // Check if all search terms match the start of any word in the customer name
                    return searchTerms.every(term => {
                        const words = customerName.split(' ');
                        return words.some(word => word.startsWith(term));
                    });
                });
                
                if (filteredOptions.length > 0) {
                    filteredOptions.forEach(option => {
                        const resultItem = document.createElement('a');
                        resultItem.href = '#';
                        resultItem.className = 'list-group-item list-group-item-action';
                        resultItem.textContent = option.text;
                        resultItem.dataset.id = option.value;
                        
                        resultItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            customerSelect.value = this.dataset.id;
                            customerSearchInput.value = option.text;
                            lastValidValue = option.text; // Update last valid value
                            customerSearchResults.innerHTML = '';
                            
                            // Trigger the change event on the select
                            const changeEvent = new Event('change');
                            customerSelect.dispatchEvent(changeEvent);
                        });
                        
                        customerSearchResults.appendChild(resultItem);
                    });
                } else {
                    const noResults = document.createElement('div');
                    noResults.className = 'list-group-item text-muted';
                    noResults.textContent = 'No customers found';
                    customerSearchResults.appendChild(noResults);
                }
            }
        });

        // Prevent manual text input - restore last valid value if no match found
        customerSearchInput.addEventListener('blur', function() {
            setTimeout(() => {
                const selectedOption = Array.from(customerSelect.options).find(option => 
                    option.text === this.value
                );
                
                if (!selectedOption) {
                    this.value = lastValidValue;
                }
            }, 200);
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!customerSearchInput.contains(e.target) && !customerSearchResults.contains(e.target)) {
                customerSearchResults.innerHTML = '';
            }
        });

        // Prevent form submission if no valid customer is selected
        const form = customerSearchInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const selectedOption = Array.from(customerSelect.options).find(option => 
                    option.text === customerSearchInput.value
                );
                
                if (!selectedOption) {
                    e.preventDefault();
                    showValidationMessage('Please select a valid customer from the list');
                    customerSearchInput.focus();
                }
            });
        }
    }
}

// Function to update registration plate when car is selected
function updateRegistrationPlate(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption && selectedOption.dataset.license) {
        document.getElementById('registration').value = selectedOption.dataset.license;
    }
}

// Photo preview functionality
document.addEventListener('DOMContentLoaded', function() {
    setupPhotoPreview();
});

// Function to setup photo preview
function setupPhotoPreview(element) {
    const inputs = element ? [element] : document.querySelectorAll('.photo-input');
    
    inputs.forEach(input => {
        if (input.hasPhotoListener) return; // Prevent duplicate listeners
        
        input.hasPhotoListener = true;
        input.addEventListener('change', function(event) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            if (!previewContainer) return;
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (!file.type.match('image.*')) {
                    return;
                }
                
                // Create a unique ID for this preview
                const previewId = 'preview-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                this.dataset.previewId = previewId;
                
                // Remove old preview if exists
                if (this.dataset.oldPreviewId) {
                    const oldPreview = document.getElementById(this.dataset.oldPreviewId);
                    if (oldPreview) oldPreview.remove();
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.id = previewId;
                    
                    const div = document.createElement('div');
                    div.className = 'position-relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-fluid rounded photo-preview';
                    img.style.cursor = 'pointer';
                    
                    // Add click event to open modal
                    img.addEventListener('click', function() {
                        document.getElementById('modalImage').src = this.src;
                        $('#photoModal').modal('show');
                    });
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-danger btn-sm position-absolute';
                    deleteBtn.style.cssText = 'top: 5px; right: 5px;';
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                    deleteBtn.onclick = function() {
                        if (confirm('Are you sure you want to delete this photo?')) {
                            // Clear the file input
                            input.value = '';
                            // Remove the preview
                            col.remove();
                        }
                    };
                    
                    div.appendChild(img);
                    div.appendChild(deleteBtn);
                    col.appendChild(div);
                    previewContainer.appendChild(col);
                };
                
                reader.readAsDataURL(file);
                this.dataset.oldPreviewId = previewId;
            }
        });
    });
}

// Function to add new part field
function addPartField() {
    const container = document.getElementById('partsContainer');
    if (!container) return;
    
    // Get the template select element with all the options
    let templateSelect = container.querySelector('.part-select');
    
    // If no template select exists in the container, look for a hidden template
    if (!templateSelect) {
        templateSelect = document.getElementById('partSelectTemplate');
    }
    
    if (!templateSelect) return;
    
    const newField = document.createElement('div');
    newField.className = 'input-group mt-2 d-flex flex-column flex-sm-row';
    newField.innerHTML = `
        <div class="position-relative w-100 mb-2 mb-sm-0" style="flex: 1;">
            <input type="text" class="form-control part-search" placeholder="Search part...">
            <div class="list-group mt-1 position-absolute" style="width: 100%; top: 38px; z-index: 1000;"></div>
            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <button type="button" class="btn btn-link text-danger" onclick="removePart(this)" style="padding: 0;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="d-flex d-sm-inline-flex">
            <input type="number" name="partQuantities[]" class="form-control ml-sm-2" min="1" value="1" style="max-width: 80px;" placeholder="Qty" onchange="validateQuantity(this)">
            <input type="number" name="partPrices[]" class="form-control ml-2" step="0.01" min="0" value="0.00" style="max-width: 100px;" placeholder="Price">
        </div>
        <input type="hidden" name="parts[]" value="">
        <select name="parts_select[]" class="form-control part-select" style="display: none;" onchange="updatePartPrice(this)">
            ${templateSelect.innerHTML}
        </select>
    `;
    container.appendChild(newField);

    // Setup part search for the new field
    setupPartSearch(newField.querySelector('.part-search'), newField.querySelector('.part-select'));
}

// Function to add new photo field
function addPhotoField() {
    const container = document.getElementById('photosContainer');
    if (!container) return;
    
    const newField = document.createElement('div');
    newField.className = 'input-group mt-2';
    newField.innerHTML = `
        <div class="position-relative" style="flex: 1;">
            <input type="file" name="photos[]" class="form-control photo-input" accept="image/*">
            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <button type="button" class="btn btn-link text-danger" onclick="removeNewPhoto(this)" style="padding: 0;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newField);
    
    // Setup preview for the new field
    setupPhotoPreview(newField.querySelector('.photo-input'));
}

// Function to remove new photo field with confirmation
function removeNewPhoto(button) {
    const inputGroup = button.closest('.input-group');
    const previewId = inputGroup.querySelector('.photo-input')?.dataset.previewId;
    const previewElement = previewId ? document.getElementById(previewId) : null;
    
    if (previewElement) {
        // If there's a preview, show confirmation dialog
        if (confirm('Are you sure you want to delete this photo?')) {
            // Remove both the input field and the preview
            inputGroup.remove();
            previewElement.remove();
        }
    } else {
        // If there's no preview, just remove the input field
        inputGroup.remove();
    }
}

// Function to delete an existing photo
function deletePhoto(button, photoName) {
    // Store the button and photo name in variables accessible to the modal
    window.currentDeleteButton = button;
    window.currentPhotoName = photoName;
    
    // Show the delete confirmation modal
    $('#deletePhotoModal').modal('show');
}

// Function to confirm photo deletion (called from the modal)
function confirmDeletePhoto() {
    const button = window.currentDeleteButton;
    const photoName = window.currentPhotoName;
    
    // Add the photo name to a hidden input for tracking deleted photos
    const deletedPhotosInput = document.querySelector('input[name="removed_photos"]') || (() => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_photos';
        document.querySelector('form').appendChild(input);
        return input;
    })();
    
    // Add to the list of removed photos
    const removedPhotos = deletedPhotosInput.value ? JSON.parse(deletedPhotosInput.value) : [];
    removedPhotos.push(photoName);
    deletedPhotosInput.value = JSON.stringify(removedPhotos);
    
    // Remove the photo container from the display
    button.closest('.col-md-3').remove();
    
    // Hide the modal
    $('#deletePhotoModal').modal('hide');
}

// Sticky header functionality
window.addEventListener('scroll', function() {
    const stickyHeader = document.getElementById('sticky-customer-header');
    if (!stickyHeader) return;

    const customerNameField = document.querySelector('.form-group');
    if (customerNameField) {
        const rect = customerNameField.getBoundingClientRect();
        const headerHeight = document.querySelector('.top-container')?.offsetHeight || 0;
        
        // Show the sticky header when the customer name field is scrolled out of view
        if (rect.bottom <= headerHeight + 10) {
            stickyHeader.classList.remove('d-none');
            
            // Adjust the sticky header width based on form container width
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                const formContainerRect = formContainer.getBoundingClientRect();
                stickyHeader.style.maxWidth = (formContainerRect.width * 0.8) + 'px';
            }
        } else {
            stickyHeader.classList.add('d-none');
        }
    }
});

// Initialize photo modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const photoModal = document.getElementById('photoModal');
    if (!photoModal) return;

    const toggleSizeBtn = document.getElementById('toggleSize');
    if (toggleSizeBtn) {
        toggleSizeBtn.addEventListener('click', function() {
            const dialog = document.getElementById('photoModalDialog');
            const icon = document.getElementById('sizeIcon');
            if (dialog.classList.contains('modal-lg')) {
                dialog.classList.remove('modal-lg');
                dialog.classList.add('modal-fullscreen');
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
            } else {
                dialog.classList.remove('modal-fullscreen');
                dialog.classList.add('modal-lg');
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            }
        });
    }
});
