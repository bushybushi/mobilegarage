<?php
// Include admin protection and database connection
require_once '../../UserAccess/protect.php';
protectAdminPage(); // Ensure only admins can access this page
require_once '../config/db_connection.php';

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
    <title>Add User</title>
    
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
<div class="pc-container4">
    <div class="form-container">
        <!-- Top navigation bar -->
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='user_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                Add New User
            </div>
            <div style="width: 30px;"></div>
        </div>

        <!-- Add user form -->
        <form class="showmessage ajax-form" action="../controllers/add_user_controller.php" method="POST">
            <!-- Username and Password fields -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="password" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPassword()">
                                    <i class="fas fa-random"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email and Admin Status fields -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Admin Status</label>
                        <select class="form-control" name="admin" required>
                            <option value="0">User</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Question and Answer fields -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Security Question</label>
                        <select class="form-control" name="security_question" required>
                            <?php foreach ($security_questions as $question): ?>
                                <option value="<?php echo $question['id']; ?>">
                                    <?php echo htmlspecialchars($question['question']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Security Answer</label>
                        <input type="text" class="form-control" name="security_answer" required>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="btngroup text-center mt-4">
                <button type="submit" class="btn btn-primary" title="Add User">Add <i class="fas fa-user-plus"></i></button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='user_main.php'" title="Cancel">Cancel <i class="fas fa-times"></i></button>
            </div>
        </form>
    </div>
</div>

</body>
</html> 