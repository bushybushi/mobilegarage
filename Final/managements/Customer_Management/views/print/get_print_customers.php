<?php
require_once dirname(dirname(__DIR__)) . '/config/db_connection.php';
require_once dirname(dirname(__DIR__)) . '/models/customer_model.php';

// Get page number and sort order from request
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'Name';
$customersPerPage = 10;

// Get customers using the model
$customerMang = new customerManagement();
$result = $customerMang->getPrintCustomers($page, $customersPerPage, $sort);

// Output the table rows
foreach ($result as $row): ?>
    <tr data-customer-id="<?php echo htmlspecialchars($row['CustomerID']); ?>">
        <td><input type="checkbox" class="print-customer-select"></td>
        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
        <td><?php echo htmlspecialchars($row['Email'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($row['Phone'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($row['Address'] ?? ''); ?></td>
    </tr>
<?php endforeach; ?> 