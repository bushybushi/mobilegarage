// Function to open the Add Car modal and clear previous values
function openAddCarModal() {
    // Check if the modal exists
    if ($("#addCarModal").length) {
        // Clear previous values
        $("#addCarModal input").val('');
        $("#addCarModal textarea").val('');
        
        // Show the modal
        $("#addCarModal").modal('show');
    } else {
        console.warn("Add Car Modal not found. This function should only be called from pages that contain the modal.");
    }
}

// Function to handle car row clicks in the customer view
// This function has been moved to utils.js to prevent conflicts

// Function to edit a car
function editCar(licenseNr) {
    // Check if we're in the add customer form by looking for the form ID
    const isAddCustomerForm = document.getElementById('customerForm') && !document.querySelector('input[name="id"]');
    
    if (isAddCustomerForm) {
        // Show a message that the customer needs to be saved first
        const message = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Please save the customer first before editing car details.
            </div>
        `;
        
        // Create or update the message container
        let messageContainer = document.getElementById('messageContainer');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'messageContainer';
            messageContainer.style.width = '100%';
            messageContainer.style.marginTop = '10px';
            
            // Insert the message container after the top-container
            const topContainer = document.querySelector('.top-container');
            topContainer.parentNode.insertBefore(messageContainer, topContainer.nextSibling);
        }
        
        // Clear any existing messages
        messageContainer.innerHTML = '';
        
        // Add the new message
        messageContainer.innerHTML = message;
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-hide the message after 3 seconds
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 3000);
        
        return;
    }
    
    // Get the customer ID from the URL
    const customerId = new URLSearchParams(window.location.search).get('id');
    
    // Fetch car details
    fetch('../controllers/get_car_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `licenseNr=${licenseNr}&customerId=${customerId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                throw new Error('Invalid JSON response');
            }
        });
    })
    .then(data => {
        if (data && data.car) {
            const car = data.car;
            // Store the old license number
            document.getElementById('editOldLicenseNr').value = car.LicenseNr;
            // Populate the modal with car details
            document.getElementById('editLicenseNr').value = car.LicenseNr;
            document.getElementById('editBrand').value = car.Brand;
            document.getElementById('editModel').value = car.Model;
            document.getElementById('editVIN').value = car.VIN;
            document.getElementById('editFuel').value = car.Fuel;
            document.getElementById('editEngine').value = car.Engine;
            document.getElementById('editManuDate').value = car.ManuDate;
            document.getElementById('editColor').value = car.Color;
            document.getElementById('editKwHorse').value = car.KWHorse || '';
            document.getElementById('editKmMiles').value = car.KMMiles || '';
            document.getElementById('editComments').value = car.Comments || '';
            
            // Show the modal
            $('#carEditModal').modal('show');
        } else {
            console.error('Invalid data format:', data);
            alert('Error: Invalid car data received');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading car details: ' + error.message);
    });
}

// Function to save car edit
function saveCarEdit() {
    const form = document.getElementById('editCarForm');
    const formData = new FormData(form);
    
    // Log the form data for debugging
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Store the old values for comparison
    const oldLicenseNr = formData.get('oldLicenseNr');
    const oldBrand = formData.get('oldBrand');
    const oldModel = formData.get('oldModel');
    const oldVIN = formData.get('oldVIN');
    const oldFuel = formData.get('oldFuel');
    const oldEngine = formData.get('oldEngine');
    
    fetch('../controllers/update_car_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text().then(text => {
            // If the response is empty or not JSON, consider it a success
            if (!text) {
                return { success: true };
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                console.log('Non-JSON response received:', text);
                // If we get here, the update likely succeeded but didn't return JSON
                return { success: true };
            }
        });
    })
    .then(data => {
        if (data.success) {
            // Close the modal
            $('#carEditModal').modal('hide');
            
            // Show success message using Bootstrap alert
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                <span>Car details updated successfully!</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            // Create a message container if it doesn't exist
            let messageContainer = document.getElementById('messageContainer');
            if (!messageContainer) {
                messageContainer = document.createElement('div');
                messageContainer.id = 'messageContainer';
                messageContainer.style.width = '100%';
                messageContainer.style.marginTop = '10px';
                
                // Insert the message container after the top-container
                const topContainer = document.querySelector('.top-container');
                topContainer.parentNode.insertBefore(messageContainer, topContainer.nextSibling);
            }
            
            // Clear any existing messages
            messageContainer.innerHTML = '';
            
            // Add the new message
            messageContainer.appendChild(successAlert);
            successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto-hide the success message after 3 seconds
            setTimeout(() => {
                $(successAlert).fadeOut(500, function() {
                    $(this).remove();
                });
            }, 2000);
            
            // Update the car row in the DOM instead of refreshing the page
            const licenseNr = formData.get('licenseNr');
            const brand = formData.get('brand');
            const model = formData.get('model');
            const vin = formData.get('vin');
            const fuel = formData.get('fuel');
            const engine = formData.get('engine');
            
            // If the license number changed, we need to update the ID of the car row
            if (oldLicenseNr !== licenseNr) {
                const oldCarRow = document.getElementById(`car-${oldLicenseNr}`);
                if (oldCarRow) {
                    oldCarRow.id = `car-${licenseNr}`;
                    
                    // Update the onclick attributes for the edit and delete buttons
                    const editBtn = oldCarRow.querySelector('button[onclick^="editCar"]');
                    if (editBtn) {
                        editBtn.setAttribute('onclick', `editCar('${licenseNr}')`);
                    }
                    
                    const deleteBtn = oldCarRow.querySelector('button[onclick^="deleteCar"]');
                    if (deleteBtn) {
                        deleteBtn.setAttribute('onclick', `deleteCar('${licenseNr}')`);
                    }
                }
            }
            
            // Update the car details in the DOM
            const carRow = document.getElementById(`car-${licenseNr}`);
            if (carRow) {
                const descElement = carRow.querySelector('.info-view-desc');
                if (descElement) {
                    descElement.textContent = `${brand} ${model}`;
                }
                
                const detailsElement = carRow.querySelector('.info-view-details');
                if (detailsElement) {
                    const spans = detailsElement.querySelectorAll('span');
                    if (spans.length >= 4) {
                        spans[0].textContent = `License: ${licenseNr}`;
                        spans[1].textContent = `VIN: ${vin || 'N/A'}`;
                        spans[2].textContent = `Fuel: ${fuel || 'N/A'}`;
                        spans[3].textContent = `Engine: ${engine || 'N/A'}`;
                    }
                }
            }
            
            // Update the hidden inputs in the main form
            const mainForm = document.getElementById('customerForm');
            if (mainForm) {
                // Find all hidden inputs for this car
                const inputs = mainForm.querySelectorAll(`input[name^="car["]`);
                
                // Update the values
                inputs.forEach(input => {
                    const fieldName = input.name.match(/car\[(.*?)\]/)[1];
                    if (fieldName === 'licenseNr' && input.value === oldLicenseNr) {
                        input.value = licenseNr;
                    } else if (fieldName === 'brand' && input.value === oldBrand) {
                        input.value = brand;
                    } else if (fieldName === 'model' && input.value === oldModel) {
                        input.value = model;
                    } else if (fieldName === 'vin' && input.value === oldVIN) {
                        input.value = vin;
                    } else if (fieldName === 'fuel' && input.value === oldFuel) {
                        input.value = fuel;
                    } else if (fieldName === 'engine' && input.value === oldEngine) {
                        input.value = engine;
                    }
                });
            }
        } else {
            console.error('Server error:', data);
            alert('Error updating car: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating car: ' + error.message);
    });
}

// Function to delete a car
function deleteCar(licenseNr) {
    console.log('deleteCar function called with licenseNr:', licenseNr);
    
    // Get the customer ID from the hidden input field
    const customerId = document.querySelector('input[name="id"]')?.value;
    
    console.log('Customer ID:', customerId);
    
    if (!customerId) {
        alert('Error: Customer ID not found');
        return;
    }
    
    // Store the license number and customer ID for later use
    $('#deleteCarModal').data('licenseNr', licenseNr);
    $('#deleteCarModal').data('customerId', customerId);
    $('#deleteCarModal').data('deleteStep', 1);
    
    // Show the initial confirmation modal
    $('#deleteCarModalMessage').text('Are you sure you want to delete this car?');
    $('#noDeleteCarBtn').hide();
    $('#confirmDeleteCarBtn').text('Delete');
    $('#deleteCarModal').modal('show');
}

// Function to submit the car deletion
function submitCarDeletion(deleteJobCards) {
    const licenseNr = $('#deleteCarModal').data('licenseNr');
    const customerId = $('#deleteCarModal').data('customerId');
    
    console.log('Submitting car deletion:', { licenseNr, customerId, deleteJobCards });
    
    // Use AJAX instead of form submission
    $.ajax({
        url: '../controllers/delete_car_controller.php',
        type: 'POST',
        data: {
            licenseNr: licenseNr,
            customerId: customerId,
            deleteJobCards: deleteJobCards ? '1' : '0'
        },
        success: function(response) {
            try {
                // Parse the response if it's a string
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.success) {
                    // Close the modal first
                    $('#deleteCarModal').modal('hide');
                    
                    // Show success message using Bootstrap alert
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
                    successAlert.innerHTML = `
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>${data.message || 'Car deleted successfully!'}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    `;
                    
                    // Create a message container if it doesn't exist
                    let messageContainer = document.getElementById('messageContainer');
                    if (!messageContainer) {
                        messageContainer = document.createElement('div');
                        messageContainer.id = 'messageContainer';
                        messageContainer.style.width = '100%';
                        messageContainer.style.marginTop = '10px';
                        
                        // Insert the message container after the top-container
                        const topContainer = document.querySelector('.top-container');
                        topContainer.parentNode.insertBefore(messageContainer, topContainer.nextSibling);
                    }
                    
                    // Clear any existing messages
                    messageContainer.innerHTML = '';
                    
                    // Add the new message
                    messageContainer.appendChild(successAlert);
                    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Auto-hide the success message after 3 seconds
                    setTimeout(() => {
                        $(successAlert).fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 2000);
                    
                    // Remove the car row from the DOM
                    $(`#car-${licenseNr}`).fadeOut(500, function() {
                        $(this).remove();
                    });
                } else {
                    alert(data.message || 'Failed to delete car');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error processing server response');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            alert('Error deleting car: ' + error);
        }
    });
}

// Initialize the delete car modal event handlers
$(document).ready(function() {
    // Handle confirm delete button click
    $('#confirmDeleteCarBtn').on('click', function() {
        const deleteStep = $('#deleteCarModal').data('deleteStep');
        const isTemporary = $('#deleteCarModal').data('isTemporary');
        
        if (isTemporary) {
            // Handle temporary car removal
            removeTemporaryCarConfirmed();
            return;
        }
        
        if (deleteStep === 1) {
            // Check if the car has associated job cards
            $.ajax({
                url: '../controllers/check_car_job_cards.php',
                type: 'POST',
                data: { licenseNr: $('#deleteCarModal').data('licenseNr') },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        alert(response.error || 'Error checking job cards');
                        return;
                    }
                    
                    if (response.hasJobCards) {
                        // Show job cards warning and update buttons
                        $('#deleteCarModalMessage').text('This car has associated job cards. Do you want to delete them as well?');
                        $('#noDeleteCarBtn').show();
                        $('#confirmDeleteCarBtn').text('Yes, Delete All');
                        $('#deleteCarModal').data('deleteStep', 2);
                    } else {
                        // No job cards, proceed with deletion
                        $('#deleteCarModal').modal('hide');
                        submitCarDeletion(false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking job cards:', error);
                    alert('Error checking job cards: ' + (xhr.responseJSON?.error || error));
                }
            });
        } else if (deleteStep === 2) {
            // Delete car with job cards
            $('#deleteCarModal').modal('hide');
            submitCarDeletion(true);
        }
    });
    
    // Handle No button click
    $('#noDeleteCarBtn').on('click', function() {
        const isTemporary = $('#deleteCarModal').data('isTemporary');
        
        if (isTemporary) {
            // Just close the modal for temporary cars
            $('#deleteCarModal').modal('hide');
            return;
        }
        
        // Delete car without job cards
        $('#deleteCarModal').modal('hide');
        submitCarDeletion(false);
    });
});

// Function to print customer details
function PrintCustomer() {
    window.print();
}

// Function to show the add car modal
function showAddCarModal() {
    // Reset the form
    document.getElementById('addCarForm').reset();
    // Show the modal
    $('#addCarModal').modal('show');
}

// Function to save a new car
function saveNewCar() {
    const form = document.getElementById('addCarForm');
    const formData = new FormData(form);
    
    // Get the car details for display
    const brand = formData.get('brand');
    const model = formData.get('model');
    const licenseNr = formData.get('licenseNr');
    const vin = formData.get('vin');
    const fuel = formData.get('fuel');
    const engine = formData.get('engine');
    
    // Validate required fields
    if (!brand || !model || !licenseNr) {
        alert('Please fill in all required fields (Brand, Model, License Number)');
        return;
    }
    
    // Check if we're in the add customer form or edit customer form
    const isAddCustomerForm = document.getElementById('customerForm') && !document.querySelector('input[name="id"]');
    
    if (isAddCustomerForm) {
        // Handle add customer form case
        // Create new car row
        const carRow = document.createElement('div');
        carRow.className = 'car-row d-flex flex-column gap-2 flex-md-row justify-content-between align-items-start align-items-md-center p-3"';
        carRow.id = `car-${licenseNr}`;
        carRow.setAttribute('data-license', licenseNr);
        carRow.innerHTML = `
            <div class="car-info flex-grow-1 mb-2 mb-md-0">
                <div class="car-desc">ert t (t)</div>
                <div class="car-details d-flex flex-column flex-md-row gap-2">
                    <span>VIN: t</span>
                    <span>Fuel: t</span>
                    <span>Engine: t</span>
                </div>
            </div>
            <div class="car-actions d-flex justify-content-end align-self-end">
                <button type="button" onclick="editCar('t')" class="btn btn-sm btn-primary edit-car">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" onclick="removeTemporaryCar('t')" class="btn btn-sm btn-danger remove-car">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        `;
        
        // Add the new car row to the container
        const carsContainer = document.getElementById('carsContainer');
        if (carsContainer) {
            // Remove the "No cars" message if it exists
            const noCarsMessage = carsContainer.querySelector('.alert-info');
            if (noCarsMessage) {
                noCarsMessage.remove();
            }
            
            // Add the new car row before the "Add Car" button
            const addCarBtn = carsContainer.querySelector('#addCarBtn');
            if (addCarBtn) {
                carsContainer.insertBefore(carRow, addCarBtn);
            } else {
                carsContainer.appendChild(carRow);
            }
        } else {
            console.error('Cars container not found');
        }
        
        // Add car data to the main form as hidden fields
        const mainForm = document.getElementById('customerForm');
        if (mainForm) {
            const carData = {
                brand: brand,
                model: model,
                licenseNr: licenseNr,
                vin: vin,
                manuDate: formData.get('manuDate'),
                fuel: fuel,
                kwHorse: formData.get('kwHorse'),
                engine: engine,
                kmMiles: formData.get('kmMiles'),
                color: formData.get('color'),
                comments: formData.get('comments')
            };
            
            // Add each car field as a hidden input
            Object.entries(carData).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `car[${key}][]`;
                input.value = value || '';
                mainForm.appendChild(input);
            });
        }
    } else {
        // Handle edit customer form case - submit to server
        // Get customer ID from the URL or hidden input
        const customerId = document.querySelector('input[name="id"]')?.value || new URLSearchParams(window.location.search).get('id');
        
        if (!customerId) {
            alert('Error: Customer ID not found');
            return;
        }
        
        // Add customer ID to form data
        formData.append('customerId', customerId);
        
        fetch('../controllers/add_car_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create new car row
                const carRow = document.createElement('div');
                carRow.className = 'car-row d-flex flex-column gap-2 flex-md-row justify-content-between align-items-start align-items-md-center p-3"';
                carRow.id = `car-${licenseNr}`;
                carRow.setAttribute('data-license', licenseNr);
                carRow.innerHTML = `
                    <div class="car-info flex-grow-1 mb-2 mb-md-0">
                <div class="car-desc">ert t (t)</div>
                <div class="car-details d-flex flex-column flex-md-row gap-2">
                    <span>VIN: t</span>
                    <span>Fuel: t</span>
                    <span>Engine: t</span>
                </div>
            </div>
            <div class="car-actions d-flex justify-content-end align-self-end">
                <button type="button" onclick="editCar('t')" class="btn btn-sm btn-primary edit-car">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" onclick="removeTemporaryCar('t')" class="btn btn-sm btn-danger remove-car">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
                `;
                
                // Add the new car row to the container
                const carsContainer = document.querySelector('.cars-container');
                if (carsContainer) {
                    // Remove the "No cars" message if it exists
                    const noCarsMessage = carsContainer.querySelector('.alert-info');
                    if (noCarsMessage) {
                        noCarsMessage.remove();
                    }
                    
                    // Add the new car row
                    carsContainer.appendChild(carRow);
                } else {
                    console.error('Cars container not found');
                }
            } else {
                alert(data.message || 'Error adding car');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding car: ' + error.message);
        });
    }
    
    // Close the modal and reset the form
    $('#addCarModal').modal('hide');
    form.reset();
    
    // Show success message
    const successAlert = document.createElement('div');
    successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
    successAlert.innerHTML = `
        <i class="fas fa-check-circle mr-2"></i>
        <span>${isAddCustomerForm ? 'Car added to the form. Click Save to save all changes.' : 'Car added successfully!'}</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Create a message container if it doesn't exist
    let messageContainer = document.getElementById('messageContainer');
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.id = 'messageContainer';
        messageContainer.style.width = '100%';
        messageContainer.style.marginTop = '10px';
        
        // Insert the message container after the top-container
        const topContainer = document.querySelector('.top-container');
        topContainer.parentNode.insertBefore(messageContainer, topContainer.nextSibling);
    }
    
    // Clear any existing messages
    messageContainer.innerHTML = '';
    
    // Add the new message
    messageContainer.appendChild(successAlert);
    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Auto-hide the success message after 3 seconds
    setTimeout(() => {
        $(successAlert).fadeOut(500, function() {
            $(this).remove();
        });
    }, 3000);
}

function removeTemporaryCar(licenseNr) {
    // Store the license number for later use
    $('#deleteCarModal').data('licenseNr', licenseNr);
    $('#deleteCarModal').data('isTemporary', true);
    $('#deleteCarModal').data('deleteStep', 1);
    
    // Show the initial confirmation modal
    $('#deleteCarModalMessage').text('Are you sure you want to remove this car?');
    $('#noDeleteCarBtn').hide();
    $('#confirmDeleteCarBtn').text('Delete');
    $('#deleteCarModal').modal('show');
}

// Function to handle temporary car removal
function removeTemporaryCarConfirmed() {
    const licenseNr = $('#deleteCarModal').data('licenseNr');
    
    // Remove the car row from the DOM
    const carElement = document.getElementById(`car-${licenseNr}`);
    if (carElement) {
        carElement.remove();
    }
    
    // Remove the corresponding hidden inputs from the main form
    const mainForm = document.getElementById('customerForm');
    const inputs = mainForm.querySelectorAll(`input[name^="car["]`);
    inputs.forEach(input => {
        if (input.name.includes(`car[licenseNr][]`) && input.value === licenseNr) {
            // Find and remove all related car fields
            const index = Array.from(inputs).indexOf(input);
            const fields = ['brand', 'model', 'licenseNr', 'vin', 'manuDate', 'fuel', 'kwHorse', 'engine', 'kmMiles', 'color', 'comments'];
            fields.forEach(field => {
                const fieldInput = mainForm.querySelector(`input[name="car[${field}][]"]`);
                if (fieldInput) {
                    fieldInput.remove();
                }
            });
        }
    });
    
    // Close the modal
    $('#deleteCarModal').modal('hide');
    
    // Show success message
    const successAlert = document.getElementById('successAlert');
    document.getElementById('successMessage').textContent = 'Car removed successfully!';
    successAlert.classList.add('show');
    successAlert.style.display = 'block';
    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        successAlert.classList.remove('show');
        setTimeout(() => {
            successAlert.style.display = 'none';
        }, 150);
    }, 3000);
}