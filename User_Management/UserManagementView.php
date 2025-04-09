<?php
// UserManagementView.php
require_once 'user.php';
$user = new User();
$username = isset($_GET['id']) ? $_GET['id'] : null;
$userData = $user->getUser($username);

// Get security question text based on ID
function getSecurityQuestionText($id) {
    $questions = [
        1 => "What was your first pet's name?",
        2 => "What is your mother's maiden name?",
        3 => "What was the name of your first school?",
        4 => "What city were you born in?",
        5 => "What is your favorite book?"
    ];
    return isset($questions[$id]) ? $questions[$id] : "Unknown question";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Details</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="pc-container4">
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
    <?php if ($userData): ?>
        <form>
            <fieldset disabled>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" id="usernameField" value="<?php echo htmlspecialchars($userData['username']); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Admin</label>
                    <input type="text" class="form-control" value="<?php echo $userData['admin'] == 1 ? 'Yes' : 'No'; ?>">
                </div>
                <div class="form-group">
                    <label>Security Question</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars(getSecurityQuestionText($userData['security_question_id'])); ?>">
                </div>
                <div class="form-group">
                    <label>Security Answer</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($userData['security_answer']); ?>">
                </div>
            </fieldset>
        </form>
        <div class="btngroup">
        <button class="btn btn-primary" onclick="window.location.href='EditUser.php?username=<?php echo htmlspecialchars($username); ?>'">Edit</button>
        <button class="btn btn-danger" onclick="window.location.href='delete_user.php?username=<?php echo htmlspecialchars($username); ?>'">Delete</button>
        </div>
        </div>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>
</div>

</body>
</html>