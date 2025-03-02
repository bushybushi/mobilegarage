<?php
// Include the input sanitization file
require_once '../sanitize_inputs.php';

// Get the PDO instance from the included file
$pdo = require '../db_connection.php';

$username = isset($_GET['username']) ? $_GET['username'] : null;

$userSql = 'SELECT * FROM users WHERE username = ?';
$userStmt = $pdo->prepare($userSql);
$userStmt->execute([$username]);

$old_user = $userStmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div class="form-container">
        <div class="top-container d-flex justify-content-center align-items-center">
            <div>User Management</div>
        </div>

        <?php if ($old_user): ?>
            <form id="updateForm" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($old_user['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-group">
                <label for="passwrd">Password</label>
                    <input type="password" id="passwrd" name="passwrd" class="form-control" placeholder="********" disabled>
                  
                    <a href="javascript:void(0);" onclick="enablePasswordReset()">Reset Password</a>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($old_user['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-group">
                <label for="admin">Admin</label>
                <select name="admin" class="form-control" required>
                    <option value="1" <?php echo $old_user['admin'] == 1 ? 'selected' : ''; ?>>Yes</option>
                    <option value="0" <?php echo $old_user['admin'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div id="btngroup2">
                <button type="submit" id="bottombtn" class="btn btn-primary">Save
                    <span><i class="ti ti-check"></i></span>
                </button>

                <button type="button" id="bottombtn" class="btn btn-danger" onclick="window.location.href='UserManagementView.php?id=<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>'">Close
                    <span><i class="ti ti-x"></i></span>
                </button>
            </div>
        </form>



        <script>
            function enablePasswordReset() {
                let passField = document.getElementById('passwrd');
                passField.disabled = false;
                passField.value = '';
                passField.focus();
            }

            // Handle the form submission via AJAX
            $('#updateForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Use AJAX to submit the form
                $.ajax({
                    type: 'POST',
                    url: 'update_user.php',  // Your server-side script to handle the form data
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

        <!-- Placeholder for popup message -->
        <div id="popupMessage" style="display:none;"></div>

        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
    </div>



    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
