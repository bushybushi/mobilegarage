<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log the current directory
error_log("Current directory: " . __DIR__);

// Get the correct path to the root directory
$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
error_log("Root path: " . $rootPath);

// Require files with absolute paths
require_once $rootPath . '/managements/Invoice_Management/config/db_connection.php';
require_once $rootPath . '/managements/Invoice_Management/models/invoice_model.php';

// Debug: Check if files were included
error_log("DB connection file exists: " . (file_exists($rootPath . '/managements/Invoice_Management/config/db_connection.php') ? 'Yes' : 'No'));
error_log("Invoice model file exists: " . (file_exists($rootPath . '/managements/Invoice_Management/models/invoice_model.php') ? 'Yes' : 'No'));

// Get page number and sort order from request
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'Name';
$invoicesPerPage = 10;

try {
    // Debug: Log the parameters
    error_log("Page: " . $page . ", Sort: " . $sort . ", Invoices per page: " . $invoicesPerPage);

    // Get invoices using the model
    $invoiceMang = new InvoiceManagement();
    $result = $invoiceMang->getPrintInvoices($page, $invoicesPerPage, $sort);

    if ($result === false) {
        throw new Exception("Failed to get invoices");
    }

    // Debug: Log the result count
    error_log("Number of invoices retrieved: " . count($result));

    // Output the table rows
    foreach ($result as $row): ?>
        <tr data-invoice-id="<?php echo htmlspecialchars($row['InvoiceID']); ?>">
            <td><input type="checkbox" class="print-invoice-select"></td>
            <td><?php echo htmlspecialchars($row['InvoiceNr']); ?></td>
            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
            <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
            <td><?php echo htmlspecialchars($row['SupplierPhone']); ?></td>
            <td><?php echo htmlspecialchars($row['SupplierEmail']); ?></td>
            <td><?php echo htmlspecialchars($row['Total']); ?></td>
            <td><?php echo htmlspecialchars($row['Vat']); ?></td>
        </tr>
    <?php endforeach;
} catch (Exception $e) {
    error_log("Error in get_print_invoice.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo '<tr><td colspan="8" class="text-center">Error loading invoices. Please try again.</td></tr>';
}
?> 