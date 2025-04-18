<?php
/**
 * This file generates an HTML table of parts for the print modal.
 * It fetches all parts from the database with their supplier information
 * and creates a table with checkboxes for selecting parts to print.
 */
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

require_once __DIR__ . '/../config/db_connection.php';

try {
    // SQL query to get all parts with their supplier information
    $sql = "SELECT p.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail
        FROM parts p
        LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
        ORDER BY p.DateCreated DESC";
            
    // Execute the query and fetch all results
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display a message if no parts are found
    if (empty($parts)) {
        echo '<tr><td colspan="8" class="text-center">No parts found</td></tr>';
    } else {
        // Loop through each part and create a table row
        foreach ($parts as $part) {
            echo '<tr data-part-id="' . htmlspecialchars($part['PartID']) . '">';
            echo '<td><input type="checkbox" class="print-part-select"></td>';
            echo '<td>' . htmlspecialchars($part['PartID']) . '</td>';
            echo '<td>' . htmlspecialchars($part['PartDesc']) . '</td>';
            echo '<td>' . htmlspecialchars($part['DateCreated']) . '</td>';
            echo '<td>' . htmlspecialchars($part['SupplierName'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($part['SupplierPhone'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($part['SupplierEmail'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($part['Vat'] ?? '0') . '%</td>';
            echo '</tr>';
        }
    }
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error in get_print_parts.php: " . $e->getMessage());
    // Return a user-friendly error message
    echo '<tr><td colspan="8" class="text-center text-danger">Error loading parts. Please try again.</td></tr>';
} catch (Exception $e) {
    // Log any other errors
    error_log("Error in get_print_parts.php: " . $e->getMessage());
    // Return a user-friendly error message
    echo '<tr><td colspan="8" class="text-center text-danger">Error loading parts. Please try again.</td></tr>';
}
?> 