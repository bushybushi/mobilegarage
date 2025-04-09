<?php
// UserManagementMain.php
require_once 'db_connection.php';
$pdo = require 'db_connection.php';
$sql = "SELECT username, email, admin FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


session_start();
if (isset($_SESSION['message'])) {
    echo "<div id='customPopup' class='popup-container'>";
    echo "<div class='popup-content'>";
    echo "<p>" . $_SESSION['message'] . "</p>";
    echo "</div>";
    echo "</div>";

    // Remove session message after displaying
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<style>
    
    .popup-container {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #2196f3; /* Dark blue background */
      padding: 20px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
      color: white;
      font-size: 18px;
      width: 300px;
      z-index: 1000;
      animation: fadeIn 0.5s ease-in-out;
  }

  .popup-content p {
      margin: 0;
      font-weight: bold;
  }


  @keyframes fadeIn {
      from { opacity: 0; transform: translate(-50%, -55%); }
      to { opacity: 1; transform: translate(-50%, -50%); }
  }

  @keyframes fadeOut {
      from { opacity: 1; transform: translate(-50%, -50%); }
      to { opacity: 0; transform: translate(-50%, -55%); }
  }
</style>

<body>

<div class="pc-container3">
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Role</th>
                <th>Username</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr onclick="openForm('<?php echo $row['username']; ?>')">
                    <td><?php echo $row['admin'] == 1 ? 'Admin' : 'User'; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="btngroup">
    <button id="addnewuser-link" class="bottombtn btn btn-primary">Add New User</button>
    </div>
</div>




<script>
    setTimeout(function() {
        let popup = document.getElementById("customPopup");
        if (popup) {
            popup.style.animation = "fadeOut 0.5s ease-in-out";
            setTimeout(() => popup.remove(), 500);
        }
    }, 3000);
</script>
</div>

<script>
function openForm(username) {
    $.get('UserManagementView.php', { id: username }, function(response) {
        document.body.innerHTML = response;
    });
}

$(document).ready(function() {
    $('#addnewuser-link').on('click', function(e) {
        e.preventDefault();
        $.get('AddNewUserForm.php', function(response) {
            document.body.innerHTML = response;
        });
    });
});
</script>


</body>
</html>
