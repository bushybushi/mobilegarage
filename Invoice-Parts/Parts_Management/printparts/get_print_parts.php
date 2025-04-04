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
    // Establish database connection
    $pdo = require __DIR__ . '/../config/db_connection.php';
    
    // SQL query to get all parts with their supplier information
    // This query joins the Parts and Suppliers tables to get complete part details
    $sql = "SELECT p.PartID, p.PartDesc, p.DateCreated, p.PricePerPiece, p.SellPrice, p.Stock,
                   s.Name as SupplierName
            FROM Parts p
            LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
            ORDER BY p.DateCreated DESC";
            
    // Execute the query and fetch all results
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create the HTML table structure with sticky headers for better usability
    echo '<table class="table" id="printPartsTable">';
    echo '<thead class="print-header">';
    echo '<tr>';
    // Add a checkbox column for selecting parts to print
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;"><input type="checkbox" id="select-all-visible"></th>';
    // Add columns for part details
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Description</th>';
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Date</th>';
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Supplier</th>';
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Price/Piece</th>';
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Sell Price</th>';
    echo '<th style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">Stock</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Display a message if no parts are found
    if (empty($parts)) {
        echo '<tr><td colspan="7" class="text-center">No parts found</td></tr>';
    } else {
        // Loop through each part and create a table row
        foreach ($parts as $part) {
            // Each row has a data attribute with the part ID for JavaScript interaction
            echo '<tr data-part-id="' . htmlspecialchars($part['PartID']) . '">';
            // Add a checkbox for selecting this part
            echo '<td><input type="checkbox" class="print-part-select"></td>';
            // Display part details with proper formatting and escaping for security
            echo '<td>' . htmlspecialchars($part['PartDesc']) . '</td>';
            echo '<td>' . htmlspecialchars($part['DateCreated']) . '</td>';
            echo '<td>' . htmlspecialchars($part['SupplierName'] ?? '') . '</td>';
            echo '<td>€' . htmlspecialchars($part['PricePerPiece']) . '</td>';
            echo '<td>€' . htmlspecialchars($part['SellPrice']) . '</td>';
            echo '<td>' . htmlspecialchars($part['Stock']) . '</td>';
            echo '</tr>';
        }
    }

    // Close the table structure
    echo '</tbody>';
    echo '</table>';
    
} catch (Exception $e) {
    // Log any errors that occur during execution
    error_log("Error in get_print_parts.php: " . $e->getMessage());
    // Display a user-friendly error message
    echo '<div class="alert alert-danger">Error loading parts. Please try again.</div>';
} 