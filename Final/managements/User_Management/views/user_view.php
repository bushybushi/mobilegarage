<?php
// Include admin protection and database connection
require_once '../../UserAccess/protect.php';
protectAdminPage(); // Ensure only admins can access this page
require_once '../config/db_connection.php';

// Get username from URL parameter
$username = $_GET['id'];

// SQL query to get user details including security question
$sql = "SELECT username, email, admin, (SELECT question FROM security_questions s where s.id = u.security_question_id) as security_question, 
        security_answer FROM users u WHERE username = :username";

// Prepare and execute query with parameter binding for security
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();

// Get user data
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and title -->
    <meta charset="UTF-8">
    <title>User Details</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/scripts.js"></script>
</head>
<body>
<!-- Main container -->

    <div class="form-container">
        <!-- Top navigation bar -->
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='user_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                User Management
            </div>
            <div style="width: 30px;"></div>
        </div>

        <?php if ($userData): ?>
            <!-- User details form (disabled for viewing only) -->
            <form class="showmessage">
                <fieldset disabled>
                    <!-- Username and Password fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" id="usernameField" value="<?php echo htmlspecialchars($userData['username']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwrd">Password</label>
                                <input type="password" id="passwrd" name="passwrd" class="form-control" value="********" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Security Question and Answer fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="security_question">Security Question</label>
                                <input type="text" name="security_question" class="form-control" value="<?php echo htmlspecialchars($userData['security_question'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="security_answer">Security Answer</label>
                                <input type="text" name="security_answer" class="form-control" value="<?php echo htmlspecialchars($userData['security_answer'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Email and Admin Status fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="admin">Admin</label>
                                <input type="text" class="form-control" value="<?php echo $userData['admin'] == 1 ? 'Yes' : 'No'; ?>">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>

            <!-- Action buttons -->
            <div class="btngroup text-center mt-4">
                <button class="btn btn-primary" onclick="loadEditForm('<?php echo $username; ?>')" title="Edit User">Edit <i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()" title="Delete User">Delete <i class="fas fa-trash"></i></button>
            </div>
        <?php else: ?>
            <!-- Show message if user not found -->
            <p>User not found.</p>
        <?php endif; ?>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteModalMessage">Are you sure you want to delete this user?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to show delete confirmation modal
    function confirmDelete() {
        $('#deleteModal').modal('show');
    }

    // Handle delete confirmation
    $(document).ready(function() {
        $('#confirmDeleteBtn').click(function() {
            const username = $('#usernameField').val();
            
            $.ajax({
                url: '../controllers/delete_user_controller.php',
                type: 'POST',
                data: {
                    username: username
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    } else {
                        showMessage(response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('Error deleting user: ' + error, 'error');
                }
            });
            
            $('#deleteModal').modal('hide');
        });
    });
</script>

</body>
</html>