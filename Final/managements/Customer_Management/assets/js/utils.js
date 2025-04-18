function loadJobCard(jobId) {
    // Close the car details modal first
    $('#carDetailsModal').modal('hide');
    
    // Wait for modal to finish hiding before redirecting
    $('#carDetailsModal').on('hidden.bs.modal', function () {
        // Store the job ID in session storage
        sessionStorage.setItem('selectedJobId', jobId);
        
        // Update the URL to reflect navigation to job card management
        window.history.pushState({}, '', '../../JobCard_Management/views/job_cards_main.php');
        
        // Redirect to jobs page
        $.get('../../JobCard_Management/views/job_cards_main.php', function(response) {
            $('#dynamicContent').html(response);
            
            // After jobs page loads, trigger the job card view
           
                $.get('../../JobCard_Management/views/job_card_view.php', { id: jobId }, function(response) {
                    $('#dynamicContent').html(response);
                });
             // Small delay to ensure jobs page is loaded
        });
    });
}

// Global variable to store current sort order
let currentSortOrder = 'Name';

// Function to initialize table sorting
function initializeTableSorting() {
    // Set initial sort text
    $('#selectedSort').text(currentSortOrder);
    
    // Handle sort dropdown clicks - only target sort dropdown items
    $('.sort-dropdown-menu .dropdown-item').click(function(e) {
        e.preventDefault();
        currentSortOrder = $(this).text();
        updateMainTableSort(currentSortOrder);
        if ($('#printModal').is(':visible')) {
            loadPrintModalPage(1);
        }
    });
}

// Function to update main table sorting
function updateMainTableSort(sortBy) {
    // Update the sort text in the dropdown
    $('#selectedSort').text(sortBy);
    
    // Get the main table body
    const mainTableBody = document.querySelector('.table-responsive .table tbody');
    if (mainTableBody) {
        const rows = Array.from(mainTableBody.querySelectorAll('tr:not(.empty-row)'));
        
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
        
        // Clear and re-append sorted rows
        const emptyRows = Array.from(mainTableBody.querySelectorAll('tr.empty-row'));
        mainTableBody.innerHTML = '';
        rows.forEach(row => mainTableBody.appendChild(row));
        emptyRows.forEach(row => mainTableBody.appendChild(row));
    }

    // Handle main table pagination active state
    const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
    $('.main-pagination .page-item').removeClass('active');
    $(`.main-pagination .page-item:nth-child(${parseInt(currentPage) + 1})`).addClass('active');
}

// Function to update print modal table sorting
function updatePrintModalSort(sortBy) {
    // Get the print modal table body
    const printTableBody = document.querySelector('#printModal .table tbody');
    if (printTableBody) {
        const rows = Array.from(printTableBody.querySelectorAll('tr:not(.empty-row)'));
        
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
        
        // Clear and re-append sorted rows
        const emptyRows = Array.from(printTableBody.querySelectorAll('tr.empty-row'));
        printTableBody.innerHTML = '';
        rows.forEach(row => printTableBody.appendChild(row));
        emptyRows.forEach(row => printTableBody.appendChild(row));
    }
}

// Function to update sort when dropdown item is clicked
function updateSort(sortBy) {
    // Update the sort text in the dropdown
    $('#selectedSort').text(sortBy);
    
    // Call the existing updateMainTableSort function
    updateMainTableSort(sortBy);
}

// Function to handle car row clicks in the customer view
function handleCarRowClick() {
    // Remove any existing click handlers to prevent duplicates
    $(document).off('click', '.car-row');
    
    // Add click event handler to car rows
    $(document).on('click', '.car-row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const licenseNr = $(this).data('license');
        const customerId = $('#customerId').val();
        
        if (!licenseNr) {
            console.error('License number not found');
            return;
        }
        
        // Show loading indicator in the modal
        $('#carDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading car details...</p></div>');
        $('#carJobCards').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading job cards...</p></div>');
        
        // Show the modal
        $('#carDetailsModal').modal('show');
        
        // Fetch car details
        $.ajax({
            url: '../controllers/get_car_details.php',
            method: 'POST',
            data: {
                licenseNr: licenseNr,
                customerId: customerId
            },
            dataType: 'json',
            success: function(data) {
                if (data && data.car) {
                    const car = data.car;
                    
                    // Populate car details in the modal using the same design as utils.js
                    let carDetailsHtml = `
                        <div class="card bg shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item px-0">
                                                <strong>Brand:</strong> ${car.Brand}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Model:</strong> ${car.Model}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>License Number:</strong> ${car.LicenseNr}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>VIN:</strong> ${car.VIN}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>KWHorse:</strong> ${car.KWHorse || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item px-0">
                                                <strong>Fuel Type:</strong> ${car.Fuel || 'N/A'}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Engine:</strong> ${car.Engine || 'N/A'}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Manufacture Date:</strong> ${car.ManuDate || 'N/A'}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Color:</strong> ${car.Color || 'N/A'}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>KM/Miles:</strong> ${car.KMMiles || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#carDetailsContent').html(carDetailsHtml);
                    
                    // Fetch job cards for this car
                    $.ajax({
                        url: '../controllers/get_car_job_cards.php',
                        method: 'POST',
                        data: {
                            licenseNr: licenseNr
                        },
                        dataType: 'json',
                        success: function(jobCardsData) {
                            if (jobCardsData && jobCardsData.jobCards && jobCardsData.jobCards.length > 0) {
                                let jobCardsHtml = '';
                                
                                jobCardsData.jobCards.forEach(function(jobCard) {
                                    jobCardsHtml += `
                                        <div class="card mb-3 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-folder mr-2 text-primary"></i>
                                                        <h6 class="mb-0">Job Card for <strong>${car.LicenseNr}</strong></h6>
                                                    </div>
                                                    <button class="btn btn-primary btn-sm rounded-pill" onclick="loadJobCard('${jobCard.JobID}')">
                                                        <i class="fas fa-eye mr-1"></i>View Details
                                                    </button>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="list-group-item px-0 border-0">
                                                            <strong>Call Date:</strong><br>
                                                            ${jobCard.JobDate || 'N/A'}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="list-group-item px-0 border-0">
                                                            <strong>Description:</strong><br>
                                                            ${jobCard.Description || 'N/A'}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="list-group-item px-0 border-0">
                                                            <strong>Status:</strong><br>
                                                            ${jobCard.Status || 'N/A'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                                
                                $('#carJobCards').html(jobCardsHtml);
                            } else {
                                $('#carJobCards').html(`
                                    <div class="text-center py-5">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No job cards associated with this car.</p>
                                    </div>
                                `);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching job cards:', error);
                            $('#carJobCards').html('<p class="text-center text-danger">Error loading job cards.</p>');
                        }
                    });
                } else {
                    $('#carDetailsContent').html('<p class="text-center text-danger">Error loading car details.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching car details:', error);
                $('#carDetailsContent').html('<p class="text-center text-danger">Error loading car details.</p>');
            }
        });
    });
}

// Document ready function to initialize event handlers and sort functionality
$(document).ready(function() {
    // Initialize Bootstrap modals
    if ($('#carDetailsModal').length) {
        $('#carDetailsModal').modal({
            show: false,
            backdrop: 'static',
            keyboard: false
        });
    }
    
    // Add click handler for car rows
    $(document).on('click', '.car-row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const licenseNr = $(this).data('license');
        const customerId = $('#customerId').val() || sessionStorage.getItem('customerId');
        
        if (!customerId) {
            console.error('Customer ID not found');
            return;
        }
        
        $.ajax({
            url: '../controllers/get_car_details.php',
            method: 'POST',
            data: {
                licenseNr: licenseNr,
                customerId: customerId
            },
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    
                    // Display car details
                    let carDetailsHtml = `
                        <div class="card bg shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item px-0">
                                                <strong>Brand:</strong> ${data.car.Brand}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Model:</strong> ${data.car.Model}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>License Number:</strong> ${data.car.LicenseNr}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>VIN:</strong> ${data.car.VIN}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>KWHorse:</strong> ${data.car.KWHorse}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item px-0">
                                                <strong>Fuel Type:</strong> ${data.car.Fuel}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Engine:</strong> ${data.car.Engine}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Manufacture Date:</strong> ${data.car.ManuDate}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>Color:</strong> ${data.car.Color}
                                            </div>
                                            <div class="list-group-item px-0">
                                                <strong>KM/Miles:</strong> ${data.car.KMMiles}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Display job cards
                    let jobCardsHtml = '';
                    if (data.jobCards && data.jobCards.length > 0) {
                        data.jobCards.forEach(jobCard => {
                            jobCardsHtml += `
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-folder mr-2 text-primary"></i>
                                                <h6 class="mb-0">Job Card for <strong>${data.car.LicenseNr}</strong></h6>
                                            </div>
                                            <button class="btn btn-primary btn-sm rounded-pill" onclick="loadJobCard('${jobCard.JobID}')">
                                                <i class="fas fa-eye mr-1"></i>View Details
                                            </button>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="list-group-item px-0 border-0">
                                                    <strong>Call Date:</strong><br>
                                                    ${jobCard.DateCall}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="list-group-item px-0 border-0">
                                                    <strong>Location:</strong><br>
                                                    ${jobCard.Location}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="list-group-item px-0 border-0">
                                                    <strong>Job Report:</strong><br>
                                                    ${jobCard.JobReport}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        jobCardsHtml = `
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No job cards associated with this car.</p>
                            </div>
                        `;
                    }
                    
                    $('#carDetailsContent').html(carDetailsHtml);
                    $('#carJobCards').html(jobCardsHtml);
                    
                    $('#carDetailsModal').modal('show');
                } catch (parseError) {
                    console.error('Error parsing JSON:', parseError);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', {xhr, status, error});
                alert('Error loading car details. Please try again.');
            }
        });
    });

   
}); 