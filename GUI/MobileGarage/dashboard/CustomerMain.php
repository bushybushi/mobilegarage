<?php

require_once 'db_connection.php';
$pdo = require 'db_connection.php';

// Fetch data from the database
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, addresses.Address, phonenumbers.nr, emails.Emails 
    FROM customers 
    JOIN addresses ON customers.CustomerID = addresses.CustomerID 
    JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
    JOIN emails ON customers.CustomerID = emails.CustomerID";

    
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!doctype html>
<html lang="en">
  
  <!-- [Head] start -->
  <head>
    <title>Dashboard</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon.png"/>
    <link rel="stylesheet" href="styles.css">


  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->
  <body>
    

    <!-- [ Main Content ] start -->
  <div class="pc-container2">
      <div class="title-container d-flex justify-content-between align-items-center">
        <div>
          Total: Customers
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                Sort by: <span id="selectedSort">Select</span>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li><a class="dropdown-item" href="#" onclick="updateSort('Date Created')">Date Created</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateSort('Another action')">Another action</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateSort('Something else here')">Something else here</a></li>
              </ul>
            </div>

            <script>
            function updateSort(sortName) {
              document.getElementById('selectedSort').innerText = sortName;
            }
            </script> <!-- This script will update the text of the dropdown button when an item is clicked in the dropdown menu. -->

          <button href="#" type="button" class="btn btn-success">Print 
            <span>
          <i class="ti ti-printer"></i>
        </span>
      </button>
          <button href="#" id="addnewcustomer-link" type="button" class="btn btn-primary">Add New Customer 
          <span>
          <i class="ti ti-plus"></i>
        </span>
          </button>
        </div>
      </div>
    

   <!-- Display the table with data from database -->

<table class="table table-hover">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email Address</th>
      <th>Phone Number</th>
      <th>Address</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if (count($result) > 0) {
      // Output data of each row
      foreach($result as $row) {
        echo "<tr onclick=\"openForm('{$row['CustomerID']}')\">
          <td>{$row['CustomerID']}</td>
          <td>{$row['FirstName']} {$row['LastName']}</td>
          <td>{$row['Emails']}</td>
          <td>{$row['nr']}</td>
          <td>{$row['Address']}</td>
          </tr>";
      }
    } else {
      echo "<tr><td colspan='5'>No records found</td></tr>";
    }
    ?>
  </tbody>
</table>

<script>
function openForm(customerID) {
  $.get('CustomerView.php', { id: customerID }, function(response) {
    $('.pc-container2').html(response);
  });
}
</script> <!-- This script will send a GET request to CustomerView.php with the customer ID as a parameter when a row in the table is clicked. 
                The response from the server will replace the content of the .pc-container2 div with the CustomerView.php -->

<!-- End of table -->

        <!-- [ Main Content ] end -->
         </div>
    </div>
    <!-- [ Main Content ] end -->


    <script>
      $(document).ready(function() {
        $('#addnewcustomer-link').on('click', function(e) {
          e.preventDefault();
          $.get('AddNewCustomerForm.php', function(response) {
            $('.pc-container2').html(response);
          });
        });
      });
    </script>


  </body>
  <!-- [Body] end -->
</html>
