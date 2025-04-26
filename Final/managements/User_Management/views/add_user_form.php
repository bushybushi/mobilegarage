<?php
require_once '../../UserAccess/protect.php';
protectAdminPage(); // This ensures only admins can access this section
// AddNewUserForm.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/scripts.js"></script>
</head>
<body>
<div class="form-container">
    <div class="top-container d-flex justify-content-between align-items-center">
        <a href="javascript:void(0);" onclick="window.location.href='user_main.php'" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
            User Management
        </div>
        <div style="width: 30px;"></div>
    </div>
    <form id="addUserForm" class="showmessage">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="passwrd">Password</label>
                    <input type="text" id="passwrd" name="passwrd" class="form-control" required>
                    <a href="javascript:void(0);" onclick="generateRandomPassword()">Generate Random Password</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="security_question_id">Security Question</label>
                    <select class="form-control" id="security_question_id" name="security_question_id" required>
                        <option value="">Select a security question</option>
                        <option value="1">What was your first pet's name?</option>
                        <option value="2">What is your mother's maiden name?</option>
                        <option value="3">What was the name of your first school?</option>
                        <option value="4">What city were you born in?</option>
                        <option value="5">What is your favorite book?</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="security_answer">Security Answer</label>
                    <input type="text" class="form-control" id="security_answer" name="security_answer" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="admin">Admin</label>
                    <select id="admin" name="admin" class="form-control" required>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="btngroup">
            <button type="button" class="btn btn-primary" onclick="saveUser()">Save <i class="fas fa-save"></i></button>
        </div>
    </form>
</div>

<script>
function generateRandomPassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    document.getElementById('passwrd').value = password;
}

function saveUser() {
    console.log('Save user function called');
    
    // Get form data
    const formData = {
        username: $('#username').val(),
        passwrd: $('#passwrd').val(),
        security_question_id: $('#security_question_id').val(),
        security_answer: $('#security_answer').val(),
        email: $('#email').val(),
        admin: $('#admin').val()
    };
    
    console.log('Form data:', formData);
    
    $.ajax({
        url: '../controllers/add_user_controller.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(data) {
            console.log('Add user response:', data);
            if (data.success) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>${data.message}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                `;
                document.querySelector('.showmessage').insertBefore(successAlert, document.querySelector('.showmessage').firstChild);
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Redirect after showing the message
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showErrorMessage(data.message || 'Error adding user');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', {xhr, status, error});
            showErrorMessage('Error adding user: ' + error);
        }
    });
}

function showErrorMessage(message) {
    console.log('Showing error message:', message);
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
    errorAlert.innerHTML = `
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span>${message}</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    document.querySelector('.showmessage').insertBefore(errorAlert, document.querySelector('.showmessage').firstChild);
    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Document ready function
$(document).ready(function() {
    console.log('Document ready');
});
</script>

</body>
</html>
