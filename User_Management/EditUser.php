<?php
require_once 'user.php';

$user = new User();
$username = isset($_GET['username']) ? $_GET['username'] : null;
$old_user = $user->getUser($username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>User Management</div>
    </div>

    <?php if ($old_user): ?>
        <form id="updateForm" method="POST">
            <div class="edituserform">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($old_user['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="passwrd">Password</label>
                    <input type="password" id="passwrd" name="passwrd" class="form-control" placeholder="********" disabled>
                    <a href="javascript:void(0);" onclick="enablePasswordReset()">Reset Password</a>
                </div>

                <div class="form-group">
                    <label for="admin">Admin</label>
                    <select name="admin" class="form-control" required>
                        <option value="1" <?php echo $old_user['admin'] == 1 ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo $old_user['admin'] == 0 ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>

                <div class="btngroup">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-danger" onclick="window.location.href='UserManagementView.php?id=<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>'">Close</button>
                </div>
            </div>
        </form>

        <script>
            function enablePasswordReset() {
                let passField = document.getElementById('passwrd');
                passField.disabled = false;
                passField.value = '';
                passField.focus();
            }

            $('#updateForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'update_user.php', 
                    data: $(this).serialize(),
                    success: function() {
                        window.location.href = 'UserManagementMain.php';
                    },
                    error: function() {
                        alert('An error occurred while updating the user.');
                    }
                });
            });
        </script>

    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>
</div>
</body>
</html>
