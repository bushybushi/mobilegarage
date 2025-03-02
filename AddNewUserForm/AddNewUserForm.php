<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


    <script>
        function generateRandomPassword() {
            var length = 12;
            var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            var password = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                password += charset.charAt(Math.floor(Math.random() * n));
            }
            document.getElementById("passwrd").value = password;
        }
    </script>
</head>


<body>
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
    <form id="updateForm" method="POST">
            <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['username']) ? $sanitizedInputs['username'] : ''); ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['username']); ?></div>
                    <?php endif; ?>
               </div>
            <div class="form-group">
                    <label for="passwrd">Password</label>
                    <input type="text" id="passwrd" name="passwrd" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['passwrd']) ? $sanitizedInputs['passwrd'] : ''); ?>" required>
                    <a href="javascript:void(0);" onclick="generateRandomPassword()">Generate Random Password</a>
                    <?php if (isset($errors['passwrd'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['passwrd']); ?></div>
                    <?php endif; ?> 
            </div>
            <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['email']) ? $sanitizedInputs['email'] : ''); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                    </div>
            <div class="form-group">
                    <label for="admin">Admin</label>
                    <select id="admin" name="admin" class="form-control" required>
                        <option value="no" <?php echo (isset($sanitizedInputs['admin']) && $sanitizedInputs['admin'] == 'no') ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo (isset($sanitizedInputs['admin']) && $sanitizedInputs['admin'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                    </select>
                    <?php if (isset($errors['admin'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['admin']); ?></div>
                    <?php endif; ?>
            </div>
            <div id="btngroup2">
            <button type="submit" id="bottombtn" class="btn btn-primary">Save
            <span>
                   <i class="ti ti-check"></i>
         </span>
            </button>
            </div>
             
        </form>
        <!-- Placeholder for popup message -->
        <div id="popupMessage" style="display:none;"></div>
    </div>


<script>
    // Handle the form submission via AJAX
            $('#updateForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Use AJAX to submit the form
                $.ajax({
                    type: 'POST',
                    url: 'User.php',  // Your server-side script to handle the form data
                    data: $(this).serialize(),  // Serialize the form data
                    dataType: 'json',  // Expect a JSON response
                    success: function(response) {
                        // Check the response status and show a message
                        if (response.status === 'success') {
                            showPopupMessage('success', response.message);
                        } else {
                            showPopupMessage('error', response.message);
                        }
                    },
                    error: function() {
                        // If there's an error with the AJAX request
                        showPopupMessage('error', 'An error occurred while updating the user.');
                    }
                });
            });

            // Show popup message
            function showPopupMessage(type, message) {
                var popupClass = type === 'success' ? 'alert-success' : 'alert-danger';
                var popupMessage = '<div class="alert ' + popupClass + ' mt-4">' + message + '</div>';
                $('#popupMessage').html(popupMessage).fadeIn();
            }
        </script>

</body>
</html>