<?php
require_once '../config/db_connection.php';

// Get page number from request
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$invoicesPerPage = 10;

// Get total number of invoices
$totalInvoices = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$totalPages = ceil($totalInvoices / $invoicesPerPage);
$offset = ($page - 1) * $invoicesPerPage;

// SQL query to fetch invoices with pagination
$sql = "SELECT DISTINCT i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Total, i.Vat,
        s.Name as SupplierName
        FROM invoices i
        LEFT JOIN PartsSupply ps ON i.InvoiceID = ps.InvoiceID
        LEFT JOIN Parts p ON ps.PartID = p.PartID
        LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
        ORDER BY i.DateCreated DESC
        LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $invoicesPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the table rows
foreach ($result as $row): ?>
    <tr data-invoice-id="<?php echo htmlspecialchars($row['InvoiceID']); ?>">
        <td><input type="checkbox" class="print-invoice-select"></td>
        <td><?php echo htmlspecialchars($row['InvoiceNr']); ?></td>
        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['DateCreated']))); ?></td>
        <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
        <td>€<?php echo htmlspecialchars(number_format($row['Total'], 2)); ?></td>
        <td><?php echo htmlspecialchars($row['Vat']); ?>%</td>
    </tr>
<?php endforeach; ?> 