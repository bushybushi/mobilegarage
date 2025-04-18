// Common functions for User Management

// Form navigation functions
function openForm(username) {
    $.get('user_view.php', { id: username }, function(response) {
        $('#dynamicContent').html(response);
    });
}

function loadAddForm() {
    $.get('add_user_form.php', function(response) {
        $('#dynamicContent').html(response);
    });
}

function loadEditForm(username) {
    $.get('edit_user.php', { id: username }, function(response) {
        $('#dynamicContent').html(response);
    });
}

// Form submission handling
$(document).ready(function() {
    // Handle form submissions
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    if (response.redirect) {
                        setTimeout(function() {
                            if (response.redirect.includes('user_view.php')) {
                                const username = response.redirect.split('=')[1];
                                openForm(username);
                            } else {
                                window.location.href = response.redirect;
                            }
                        }, 1500);
                    }
                } else {
                    showMessage(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error: ' + error, 'error');
            }
        });
    });
});

// Delete user functionality
function confirmDelete(username) {
    $('#deleteModalMessage').text('Are you sure you want to delete user: ' + username + '?');
    $('#confirmDeleteBtn').data('username', username);
    $('#deleteModal').modal('show');
}

function enablePasswordReset() {
    const passwordInput = document.getElementById('passwrd');
    passwordInput.disabled = false;
    passwordInput.placeholder = "Enter new password";
}

function deleteUser(username) {
    $.ajax({
        url: '../controllers/delete_user_controller.php',
        method: 'POST',
        data: {
            username: username,
            action: 'delete'
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                $('#deleteModal').modal('hide');
                showMessage(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showMessage(data.message || 'Error deleting user', 'error');
            }
        },
        error: function(xhr, status, error) {
            showMessage('Error deleting user: ' + error, 'error');
        }
    });
}

// Message display functions
function showMessage(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas ${icon} mr-2"></i>
        <span>${message}</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    document.querySelector('.showmessage').insertBefore(alert, document.querySelector('.showmessage').firstChild);
    alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    setTimeout(() => {
        $(alert).fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

// Sort functionality
function updateSort(sortBy) {
    $('#selectedSort').text(sortBy);
    // Add your sorting logic here
}

// Document ready handlers
$(document).ready(function() {
    // Initialize delete modal
    if ($('#deleteModal').length) {
        $('#deleteModal').modal({
            show: false,
            backdrop: 'static',
            keyboard: false
        });
    }

    // Set up delete confirmation button
    $('#confirmDeleteBtn').on('click', function() {
        const username = $(this).data('username');
        deleteUser(username);
    });

    // Add user button handler
    $('#addnewuser-link').on('click', function() {
        loadAddForm();
    });

    // Check for auto-open add form
    if (sessionStorage.getItem('openAddUserForm') === 'true') {
        sessionStorage.removeItem('openAddUserForm');
        setTimeout(loadAddForm, 100);
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-toggle="popover"]').popover();
});

// Backup and restore functionality
function initializeBackupRestore() {
    $('#backupForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button');
        const $result = $('#backupResult');
        
        $btn.prop('disabled', true).text('Backing up...');
        
        $.ajax({
            url: '/MGAdmin2025/managements/includes/backup.php',
            type: 'POST',
            success: function(response) {
                $result.text(response).css('color', '#90ee90');
            },
            error: function(xhr, status, error) {
                $result.text("Backup failed: " + error).css('color', '#ffcccb');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Backup');
            }
        });
    });

    $('#restoreForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $btn = $(this).find('button');
        const $result = $('#backupResult');
        
        $btn.prop('disabled', true).text('Restoring...');
        
        $.ajax({
            url: '/MGAdmin2025/managements/includes/restore.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $result.text(response).css('color', '#90ee90');
            },
            error: function(xhr, status, error) {
                $result.text("Restore failed: " + error).css('color', '#ffcccb');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Restore');
            }
        });
    });
}

// Dynamic greeting
function updateGreeting() {
    const greetingElement = document.getElementById('greeting-text');
    if (!greetingElement) return;

    const hour = new Date().getHours();
    let greeting = '';

    if (hour < 12) {
        greeting = 'Good Morning';
    } else if (hour < 17) {
        greeting = 'Good Afternoon';
    } else {
        greeting = 'Good Evening';
    }

    greetingElement.textContent = greeting;
}

// Initialize all common functionality
function initializeCommon() {
    updateGreeting();
    initializeBackupRestore();
    
    // Add active class to current sidebar item
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.pc-navbar .pc-link');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace('../', ''))) {
            link.classList.add('active');
            link.closest('.pc-item')?.classList.add('active');
        }
    });
}

// Call initialization when document is ready
$(document).ready(initializeCommon);