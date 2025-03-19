<?php
require_once '../../config/db_connection.php';

// Get page number from request
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$customersPerPage = 10;

// Get total number of customers
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalPages = ceil($totalCustomers / $customersPerPage);
$offset = ($page - 1) * $customersPerPage;

// SQL query to fetch customers with pagination
$sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
        addresses.Address, phonenumbers.nr, emails.Emails 
        FROM customers 
        JOIN addresses ON customers.CustomerID = addresses.CustomerID 
        JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
        JOIN emails ON customers.CustomerID = emails.CustomerID
        LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $customersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the table rows
foreach ($result as $row): ?>
    <tr data-customer-id="<?php echo htmlspecialchars($row['CustomerID']); ?>">
        <td><input type="checkbox" class="print-customer-select"></td>
        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
        <td><?php echo htmlspecialchars($row['Emails']); ?></td>
        <td><?php echo htmlspecialchars($row['nr']); ?></td>
        <td><?php echo htmlspecialchars($row['Address']); ?></td>
    </tr>
<?php endforeach; ?> 