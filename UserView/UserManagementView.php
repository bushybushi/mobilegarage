<?php
require_once '../db_connection.php';
$pdo = require '../db_connection.php';

// Fetch data from the database
$username = isset($_GET['id']) ? $_GET['id'] : null;
$sql = "SELECT users.username, users.email, users.passwrd, users.admin
    FROM users
    WHERE users.username = :username";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$users = $stmt->fetch(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <!-- [Tabler Icons] https://tablericons.com -->
     <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
<div class="pc-container4">
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
        <form action="../User.php" method="POST">
            <fieldset disabled>
            <?php if ($users): ?>
            <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($users['username']); ?>">
               </div>
            <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="********">
            </div>
            <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($users['email']); ?>">
            </div>
            <div class="form-group">
                    <label for="admin">Admin</label>
                    <input type="text" id="admin" name="admin" class="form-control" value="<?php echo $users['admin'] == 1 ? 'Yes' : 'No'; ?>">
            </div>
            <?php else: ?>
                <p>User not found.</p>
            <?php endif; ?>
            </fieldset>
            <div id="btngroup2">
                <button href="#" type="button" id="bottombtn" class="btn btn-primary" onclick="openForm('<?php echo htmlspecialchars($users['username']); ?>')">Edit
                    <span>
                        <i class="ti ti-plus"></i>
                    </span>
                </button>
                <button href="#" type="button" id="bottombtn" class="btn btn-danger">Delete
                    <span>
                        <i class="ti ti-trash"></i>
                    </span>
                </button>
            </div>
        </form>
    </div>
    </div>

    <script>
function openForm(username) {
  $.get('EditUser.php', { username: username }, function(response) {
    $('.pc-container4').html(response);
  });
}
</script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
