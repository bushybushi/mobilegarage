<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
require_once '../config/db_connection.php';
require_once '../models/invoice_model.php';

try {
    // Get page number from request, default to 1
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;

    // Get total count of invoices
    $count_sql = "SELECT COUNT(DISTINCT i.InvoiceID) as total FROM invoices i";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute();
    $total_invoices = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_invoices / $items_per_page);

    // Get invoices for current page
    $sql = "SELECT 
                i.InvoiceID,
                i.InvoiceNr,
                i.DateCreated,
                i.Vat,
                i.Total,
                s.Name as SupplierName,
                s.PhoneNr as SupplierPhone,
                s.Email as SupplierEmail
            FROM invoices i
            LEFT JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
            LEFT JOIN parts p ON ps.PartID = p.PartID
            LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
            GROUP BY i.InvoiceID
            ORDER BY i.DateCreated DESC
            LIMIT :limit OFFSET :offset";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output pagination info as a data attribute
    echo '<div id="paginationInfo" 
             data-total-pages="' . $total_pages . '" 
             data-current-page="' . $page . '" 
             data-total-items="' . $total_invoices . '"
             style="display: none;">
          </div>';

    // Output the table rows
    foreach ($invoices as $invoice): ?>
        <tr>
            <td>
                <input type="checkbox" class="print-invoice-select" 
                       onchange="toggleInvoiceSelection(<?php echo $invoice['InvoiceID']; ?>, this)">
            </td>
            <td><?php echo htmlspecialchars($invoice['InvoiceNr'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($invoice['DateCreated']); ?></td>
            <td><?php echo htmlspecialchars($invoice['SupplierName'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($invoice['SupplierPhone'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($invoice['SupplierEmail'] ?? 'N/A'); ?></td>
            <td>€<?php echo htmlspecialchars($invoice['Total']); ?></td>
            <td><?php echo htmlspecialchars($invoice['Vat']); ?>%</td>
        </tr>
    <?php endforeach;
} catch (PDOException $e) {
    error_log("Error fetching invoices for print: " . $e->getMessage());
    echo "<tr><td colspan='8' class='text-center'>Error loading invoices</td></tr>";
}
?> 