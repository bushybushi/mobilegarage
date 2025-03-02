<?php

require_once '../db_connection.php';
$pdo = require '../db_connection.php';

// Fetch data from the database
$sql = "SELECT users.username, users.email, users.admin
    FROM users";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<body>

<div class="pc-container3">
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
        

<!-- Display the table with data from database -->

<table class="table table-hover">
  <thead>
    <tr>
      <th>Role</th>
      <th>Username</th>
      <th>Email Address</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if (count($result) > 0) {
      // Output data of each row
      foreach($result as $row) {
        $role = $row['admin'] == 1 ? 'Admin' : 'User';
        echo "<tr onclick=\"openForm('{$row['username']}')\">
          <td>{$role}</td>
          <td>{$row['username']}</td>
          <td>{$row['email']}</td>
          </tr>";
      }
    } else {
      echo "<tr><td colspan='3'>No records found</td></tr>";
    }
    ?>
  </tbody>
</table>

<script>
function openForm(username) {
  $.get('UserManagementView.php', { id: username }, function(response) {
    $('.pc-container3').html(response);
  });
}
</script> <!-- This script will send a GET request to CustomerView.php with the customer ID as a parameter when a row in the table is clicked. 
                The response from the server will replace the content of the .pc-container2 div with the CustomerView.php -->


                <div id="btngroup2">
                <button href="#" id="addnewuser-link" type="button" class="bottombtn btn btn-primary">Add New User 
            <span>
                   <i class="ti ti-check"></i>
         </span>
            </button>
               </div>
    </div>
</div>


    <script>
      $(document).ready(function() {
        $('#addnewuser-link').on('click', function(e) {
          e.preventDefault();
          $.get('AddNewUserForm.php', function(response) {
            $('.pc-container3').html(response);
          });
        });
      });
    </script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


</body>
</html>
