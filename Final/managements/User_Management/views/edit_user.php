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

// Get all security questions
$sql = "SELECT id, question FROM security_questions";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$security_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and title -->
    <meta charset="UTF-8">
    <title>Edit User</title>
    
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
            <a href="javascript:void(0);" onclick="openForm('<?php echo $username; ?>')" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                Edit User
            </div>
            <div style="width: 30px;"></div>
        </div>

        <?php if ($userData): ?>
            <!-- User edit form -->
            <form class="showmessage ajax-form" action="../controllers/update_user_controller.php" method="POST">
                <!-- Username and Password fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="passwrd">Password</label>
                            <input type="password" id="passwrd" name="passwrd" class="form-control" value="********" disabled>
                            <a href="javascript:void(0);" onclick="enablePasswordReset()">Reset Password</a>
                        </div>
                    </div>
                </div>

                <!-- Security Question and Answer fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="security_question">Security Question</label>
                            <select class="form-control" name="security_question" disabled>
                                <?php foreach ($security_questions as $question): ?>
                                    <option value="<?php echo $question['id']; ?>" <?php echo $question['question'] === $userData['security_question'] ? 'selected' : ''; ?> >
                                        <?php echo htmlspecialchars($question['question']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="security_answer">Security Answer</label>
                            <input type="text" class="form-control" name="security_answer" value="<?php echo htmlspecialchars($userData['security_answer']); ?>" disabled>
                        </div>
                    </div>
                </div>

                <!-- Email and Admin Status fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="admin">Admin Status</label>
                            <select class="form-control" name="admin" required>
                                <option value="0" <?php echo $userData['admin'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $userData['admin'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="btngroup text-center mt-4">
                    <button type="submit" class="btn btn-primary" title="Save Changes">Save <i class="fas fa-save"></i></button>
                    <button type="button" class="btn btn-secondary" onclick="openForm('<?php echo $username; ?>')" title="Cancel">Cancel</button>
                </div>
            </form>
        <?php else: ?>
            <!-- Show message if user not found -->
            <p>User not found.</p>
        <?php endif; ?>
    </div>

</body>
</html>