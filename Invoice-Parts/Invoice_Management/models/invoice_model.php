<?php
/**
 * Invoice Management System
 * CODE CREATED BY JORGOS XIDIAS AND TEAM
 * AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*
 * 
 * This file contains the core classes for managing invoices, suppliers, and parts
 * in the invoice management system. It handles database operations, data validation,
 * and provides methods for CRUD operations on invoices.
 */

require_once '../includes/sanitize_inputs.php';
require_once '../config/db_connection.php';

/**
 * Invoice Class
 * 
 * Represents an invoice in the system with all its properties and methods.
 * This class handles the basic invoice data structure and provides getters/setters.
 */
class Invoice {
    // Invoice properties
    public $id;                  // Unique identifier for the invoice
    public $invoiceNr;           // Invoice number (displayed as '-' if empty or 0)
    public $dateCreated;         // Date when the invoice was created
    public $supplierID;          // ID of the supplier who issued the invoice
    public $supplierName;        // Name of the supplier
    public $supplierPhone;       // Phone number of the supplier
    public $supplierEmail;       // Email address of the supplier
    public $vat;                 // Value Added Tax amount
    public $total;               // Total amount of the invoice
    public array $parts;         // Array of parts included in the invoice
    public $pdf;                 // PDF file associated with the invoice

    /**
     * Constructor for the Invoice class
     * 
     * Initializes a new invoice with the provided data.
     * If invoice number is empty, '0', or just whitespace, it's set to '-'.
     * 
     * @param int|null $id           Invoice ID
     * @param string|null $invoiceNr Invoice number
     * @param string|null $dateCreated Date created
     * @param int|null $supplierID   Supplier ID
     * @param string|null $supplierName Supplier name
     * @param string|null $supplierPhone Supplier phone
     * @param string|null $supplierEmail Supplier email
     * @param float|null $vat        VAT amount
     * @param float|null $total      Total amount
     * @param array $parts           Array of parts
     * @param string|null $pdf       PDF file path
     */
    function __construct($id = null, $invoiceNr = null, $dateCreated = null, $supplierID = null, 
                        $supplierName = null, $supplierPhone = null, $supplierEmail = null,
                        $vat = null, $total = null, array $parts = [], $pdf = null) {
        $this->id = $id;
        $this->invoiceNr = (!$invoiceNr || trim($invoiceNr) === '' || $invoiceNr === '0') ? '-' : $invoiceNr;
        $this->dateCreated = $dateCreated;
        $this->supplierID = $supplierID;
        $this->supplierName = $supplierName;
        $this->supplierPhone = $supplierPhone;
        $this->supplierEmail = $supplierEmail;
        $this->vat = $vat;
        $this->total = $total;
        $this->parts = $parts;
        $this->pdf = $pdf;
    }

    // Getters - Methods to retrieve invoice properties
    function getID() { return $this->id; }
    function getInvoiceNr() { return $this->invoiceNr; }
    function getDateCreated() { return $this->dateCreated; }
    function getSupplierID() { return $this->supplierID; }
    function getSupplierName() { return $this->supplierName; }
    function getSupplierPhone() { return $this->supplierPhone; }
    function getSupplierEmail() { return $this->supplierEmail; }
    function getVat() { return $this->vat; }
    function getTotal() { return $this->total; }
    function getParts() { return $this->parts; }
    function getPDF() { return $this->pdf; }

    // Setters - Methods to update invoice properties
    function setID($id) { $this->id = $id; }
    function setInvoiceNr($invoiceNr) { $this->invoiceNr = $invoiceNr; }
    function setDateCreated($dateCreated) { $this->dateCreated = $dateCreated; }
    function setSupplierID($supplierID) { $this->supplierID = $supplierID; }
    function setSupplierName($supplierName) { $this->supplierName = $supplierName; }
    function setSupplierPhone($supplierPhone) { $this->supplierPhone = $supplierPhone; }
    function setSupplierEmail($supplierEmail) { $this->supplierEmail = $supplierEmail; }
    function setVat($vat) { $this->vat = $vat; }
    function setTotal($total) { $this->total = $total; }
    function setParts($parts) { $this->parts = $parts; }
    function setPDF($pdf) { $this->pdf = $pdf; }

    /**
     * Get supplier suggestions based on search term
     * 
     * Searches for suppliers whose names match the provided search term.
     * Used for autocomplete functionality in the UI.
     * 
     * @param string $searchTerm The term to search for in supplier names
     * @return array|false Array of matching suppliers or false on error
     */
    static function getSupplierSuggestions($searchTerm) {
        global $pdo;
        try {
            $sql = "SELECT SupplierID, Name, PhoneNr as Phone, Email 
                    FROM Suppliers 
                    WHERE Name LIKE :search 
                    ORDER BY Name ASC 
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => '%' . $searchTerm . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getSupplierSuggestions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update supplier information
     * 
     * Updates the name, phone, and email of an existing supplier.
     * 
     * @param int $supplierID ID of the supplier to update
     * @param string $name New name for the supplier
     * @param string $phone New phone number for the supplier
     * @param string $email New email for the supplier
     * @return bool True if successful, false otherwise
     */
    static function updateSupplier($supplierID, $name, $phone, $email) {
        global $pdo;
        try {
            $sql = "UPDATE Suppliers 
                    SET Name = ?, PhoneNr = ?, Email = ?
                    WHERE SupplierID = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$name, $phone, $email, $supplierID]);
        } catch (PDOException $e) {
            error_log("Error in updateSupplier: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all invoices with basic information
     * 
     * Retrieves a list of all invoices with their supplier information and parts.
     * Results are ordered by date created (newest first).
     * 
     * @return array|false Array of invoices or false on error
     */
    static function getAllInvoices() {
        try {
            // Get a fresh connection
            $pdo = require '../config/db_connection.php';
            
            // Get all invoices with their supplier information
            $sql = "
                SELECT DISTINCT
                    i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Vat, i.Total,
                    s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail,
                    GROUP_CONCAT(DISTINCT p.PartDesc SEPARATOR ', ') as Parts
                FROM Invoices i
                LEFT JOIN partssupply ps ON i.InvoiceID = ps.InvoiceID
                LEFT JOIN Parts p ON ps.PartID = p.PartID
                LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
                GROUP BY i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Vat, i.Total,
                        s.Name, s.PhoneNr, s.Email
                ORDER BY i.DateCreated DESC
            ";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getAllInvoices: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a single invoice by ID with all its details
     * 
     * Retrieves a complete invoice record including all parts and supplier information.
     * 
     * @param int $id The ID of the invoice to retrieve
     * @return array|null The invoice data or null if not found
     */
    static function getInvoiceById($id) {
        try {
            $pdo = require '../config/db_connection.php';
            
            // First, get the invoice details
            $sql = "SELECT i.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail 
                    FROM Invoices i 
                    LEFT JOIN Suppliers s ON i.SupplierID = s.SupplierID 
                    WHERE i.InvoiceID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                error_log("No invoice found with ID: " . $id);
                return null;
            }
            
            // Get the parts for this invoice
            $partsSql = "SELECT p.*, ps.PiecesPurch, ps.PricePerPiece, ps.PriceBulk, ps.SellPrice 
                        FROM Parts p 
                        JOIN PartsSupply ps ON p.PartID = ps.PartID 
                        WHERE ps.InvoiceID = ?";
            
            $partsStmt = $pdo->prepare($partsSql);
            $partsStmt->execute([$id]);
            $parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add parts to the invoice data
            $invoice['parts'] = $parts;
            
            // Ensure all required fields are present
            $requiredFields = [
                'InvoiceID' => $invoice['InvoiceID'],
                'InvoiceNr' => $invoice['InvoiceNr'],
                'DateCreated' => $invoice['DateCreated'],
                'SupplierID' => $invoice['SupplierID'],
                'SupplierName' => $invoice['SupplierName'],
                'Vat' => $invoice['Vat'],
                'Total' => $invoice['Total']
            ];
            
            // Log the data for debugging
            error_log("Invoice data fetched: " . json_encode($invoice));
            
            // Return the complete invoice data
            return array_merge($invoice, $requiredFields);
            
        } catch (PDOException $e) {
            error_log("Error fetching invoice: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get part suggestions based on search term
     * 
     * Searches for parts whose descriptions match the provided search term.
     * Used for autocomplete functionality in the UI.
     * 
     * @param string $searchTerm The term to search for in part descriptions
     * @return array|false Array of matching parts or false on error
     */
    static function getPartSuggestions($searchTerm) {
        global $pdo;
        try {
            $sql = "SELECT DISTINCT PartDesc 
                    FROM Parts 
                    WHERE PartDesc LIKE :search 
                    ORDER BY PartDesc ASC 
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => '%' . $searchTerm . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}

/**
 * InvoiceManagement Class
 * 
 * Handles all database operations related to invoices, including creating,
 * reading, updating, and deleting invoices and their associated parts.
 */
class InvoiceManagement {
    private $conn;      // Database connection
    private $view_only; // Flag to indicate if the user has view-only permissions

    /**
     * Constructor for InvoiceManagement
     * 
     * Initializes the database connection and sets the view_only flag
     * based on the POST data.
     */
    public function __construct() {
        $this->conn = require '../config/db_connection.php';
        $this->view_only = isset($_POST['view_only']) ? true : false;
    }

    /**
     * Validate email address format
     * 
     * Checks if the provided email address has a valid format.
     * Email is considered optional (empty is valid).
     * 
     * @param string $email The email address to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateEmail($email) {
        if (empty($email)) {
            return true; // Email is optional
        }

        // Check for @ symbol
        if (!strpos($email, '@')) {
            return false;
        }

        // Split into local and domain parts
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];
        
        // Check domain format
        if (!strpos($domain, '.') || 
            strpos($domain, '.') === 0 || 
            strpos($domain, '.') === strlen($domain) - 1) {
            return false;
        }

        return true;
    }

    /**
     * Add a new invoice (alias for Create)
     * 
     * @param array $data Invoice data
     * @return int|false Invoice ID if successful, false otherwise
     */
    public function Add($data) {
        return $this->Create($data);
    }

    /**
     * Create a new invoice
     * 
     * Creates a new invoice record and associated parts in the database.
     * Uses a transaction to ensure data integrity.
     * 
     * @param array $data Invoice data including parts
     * @return int|false Invoice ID if successful, false otherwise
     * @throws PDOException if database error occurs
     */
    public function Create($data) {
        try {
            error_log("Starting invoice creation with data: " . json_encode($data));
            
            if (!$this->conn) {
                throw new PDOException("Database connection is not available");
            }
            
            $this->conn->beginTransaction();

            // Insert into Invoices table
            $sql = "INSERT INTO Invoices (InvoiceNr, DateCreated, Vat, Total, PDF) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['invoiceNr'],
                $data['dateCreated'],
                $data['vat'],
                $data['total'],
                $data['pdf'] ?? null
            ]);
            
            $invoiceId = $this->conn->lastInsertId();
            
            // Handle parts
            if (isset($data['parts']) && is_array($data['parts'])) {
                foreach ($data['parts'] as $part) {
                    // Insert new part
                    $insertSql = "INSERT INTO Parts (PartDesc, SupplierID, PiecesPurch, PricePerPiece, PriceBulk, SellPrice, DateCreated, Stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $insertPart = $this->conn->prepare($insertSql);
                    $insertPart->execute([
                        $part['partDesc'],
                        $data['supplierID'],
                        $part['piecesPurch'],
                        $part['pricePerPiece'],
                        $part['priceBulk'] ?? null,
                        $part['sellingPrice'],
                        $data['dateCreated'],
                        $part['piecesPurch']
                    ]);
                    $partId = $this->conn->lastInsertId();
                    
                    // Link part to invoice
                    $supplySql = "INSERT INTO PartsSupply (InvoiceID, PartID) VALUES (?, ?)";
                    $insertPartsSupply = $this->conn->prepare($supplySql);
                    $insertPartsSupply->execute([$invoiceId, $partId]);
                }
            }
            
            $this->conn->commit();
            return $invoiceId;
            
        } catch (PDOException $e) {
            if ($this->conn && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error creating invoice: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * View all invoices with optional sorting
     * 
     * Retrieves a list of all invoices with basic information.
     * Results can be sorted by invoice number, date, or supplier name.
     * 
     * @param string $sortBy Sort criteria (invoice_number, date_asc, date_desc, supplier)
     * @return array|false Array of invoices or false on error
     */
    public function View($sortBy = 'date_desc') {
        try {
            $sql = "SELECT i.InvoiceID, i.InvoiceNr, i.DateCreated, i.Total, i.Vat,
                           s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail
                    FROM Invoices i
                    LEFT JOIN PartsSupply ps ON i.InvoiceID = ps.InvoiceID
                    LEFT JOIN Parts p ON ps.PartID = p.PartID
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
                    GROUP BY i.InvoiceID";

            switch ($sortBy) {
                case 'invoice_number':
                    $sql .= " ORDER BY CAST(NULLIF(i.InvoiceNr, '') AS UNSIGNED)";
                    break;
                case 'date_asc':
                    $sql .= " ORDER BY i.DateCreated ASC";
                    break;
                case 'date_desc':
                    $sql .= " ORDER BY i.DateCreated DESC";
                    break;
                case 'supplier':
                    $sql .= " ORDER BY s.Name ASC";
                    break;
                default:
                    $sql .= " ORDER BY i.DateCreated DESC";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error in View method: " . $e->getMessage());
            return false;
        }
    }

    /**
     * View a single invoice by ID
     * 
     * Retrieves a complete invoice record including all parts and supplier information.
     * 
     * @param int $id The ID of the invoice to retrieve
     * @return array|false The invoice data or false if not found
     */
    public function ViewSingle($id) {
        try {
            // First get basic invoice information
            $sql = "SELECT 
                        i.InvoiceID,
                        i.InvoiceNr,
                        i.DateCreated,
                        i.Vat,
                        i.Total,
                        i.PDF
                    FROM Invoices i
                    WHERE i.InvoiceID = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                return false;
            }
            
            // Get parts for this invoice
            $invoice['parts'] = $this->getPartsByInvoiceId($id);
            
            // If there are any parts, get the supplier info from the first part
            // This ensures we always show the current supplier
            if (!empty($invoice['parts'])) {
                $firstPartId = $invoice['parts'][0]['PartID'];
                
                $supplierSql = "SELECT 
                                s.SupplierID,
                                s.Name as SupplierName,
                                s.PhoneNr as SupplierPhone,
                                s.Email as SupplierEmail
                            FROM Parts p
                            JOIN Suppliers s ON p.SupplierID = s.SupplierID
                            WHERE p.PartID = ?";
                
                $supplierStmt = $this->conn->prepare($supplierSql);
                $supplierStmt->execute([$firstPartId]);
                $supplierData = $supplierStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($supplierData) {
                    $invoice['SupplierID'] = $supplierData['SupplierID'];
                    $invoice['SupplierName'] = $supplierData['SupplierName'];
                    $invoice['SupplierPhone'] = $supplierData['SupplierPhone'];
                    $invoice['SupplierEmail'] = $supplierData['SupplierEmail'];
                }
            }
            
            return $invoice;
            
        } catch (PDOException $e) {
            error_log("Error fetching invoice: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing invoice
     * 
     * Updates the information of an existing invoice in the database.
     * 
     * @param int $id The ID of the invoice to update
     * @param array $data The new invoice data
     * @return bool True if successful, false otherwise
     */
    public function Update($id, $data) {
        try {
            $stmt = $this->conn->prepare("UPDATE invoices SET invoice_number = ?, date_created = ?, supplier = ?, phone = ?, email = ?, total = ?, vat = ? WHERE id = ?");
            $stmt->execute([
                $data['invoice_number'],
                $data['date_created'],
                $data['supplier'],
                $data['phone'],
                $data['email'],
                $data['total'],
                $data['vat'],
                $id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error updating invoice: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an invoice
     * 
     * Deletes an invoice and optionally its associated parts from the database.
     * Uses a transaction to ensure data integrity.
     * 
     * @param int $id The ID of the invoice to delete
     * @param bool $deleteParts Whether to also delete associated parts
     * @return bool True if successful, false otherwise
     */
    public function Delete($id, $deleteParts = false) {
        try {
            $this->conn->beginTransaction();
            
            if ($deleteParts) {
                $stmt = $this->conn->prepare("
                    SELECT DISTINCT p.PartID 
                    FROM Parts p 
                    JOIN PartsSupply ps ON p.PartID = ps.PartID 
                    WHERE ps.InvoiceID = ?
                ");
                $stmt->execute([$id]);
                $parts = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Delete from PartsSupply
            $stmt1 = $this->conn->prepare("DELETE FROM PartsSupply WHERE InvoiceID = ?");
            $stmt1->execute([$id]);
            
            if ($deleteParts && !empty($parts)) {
                foreach ($parts as $partId) {
                    $stmt = $this->conn->prepare("DELETE FROM JobCardParts WHERE PartID = ?");
                    $stmt->execute([$partId]);
                    
                    $stmt = $this->conn->prepare("DELETE FROM PartSupplier WHERE PartID = ?");
                    $stmt->execute([$partId]);
                    
                    $stmt = $this->conn->prepare("DELETE FROM Parts WHERE PartID = ?");
                    $stmt->execute([$partId]);
                }
            }
            
            // Delete invoice
            $stmt2 = $this->conn->prepare("DELETE FROM Invoices WHERE InvoiceID = ?");
            $stmt2->execute([$id]);
            
            $this->conn->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error deleting invoice: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get parts associated with an invoice
     * 
     * Retrieves all parts linked to a specific invoice.
     * 
     * @param int $invoiceId The ID of the invoice
     * @return array Array of parts or empty array on error
     */
    public function getPartsByInvoiceId($invoiceId) {
        try {
            $sql = "SELECT 
                        p.PartID,
                        p.PartDesc,
                        p.PiecesPurch,
                        p.PricePerPiece,
                        p.PriceBulk,
                        p.SellPrice,
                        p.Stock,
                        s.SupplierID,
                        s.Name as SupplierName,
                        s.PhoneNr as SupplierPhone,
                        s.Email as SupplierEmail
                    FROM Parts p
                    JOIN PartsSupply ps ON p.PartID = ps.PartID
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
                    WHERE ps.InvoiceID = ?
                    ORDER BY p.PartDesc";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$invoiceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting parts for invoice: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of invoices
     * 
     * Counts the total number of invoices in the database.
     * 
     * @return int Total number of invoices or 0 on error
     */
    public function getTotalInvoices() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM Invoices");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total invoices: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Destructor
     * 
     * Cleans up resources when the object is destroyed.
     */
    public function __destruct() {
        $this->conn = null;
    }
}

// ===== AJAX Request Handlers =====
// These sections handle AJAX requests from the frontend

// Handle supplier search suggestions
if (isset($_POST['query']) && !isset($_POST['dateCreated'])) {
    $searchTerm = $_POST['query'];
    $suppliers = Invoice::getSupplierSuggestions($searchTerm);
    
    if ($suppliers === false) {
        echo '<div class="error">Error fetching suppliers</div>';
    } else if (empty($suppliers)) {
        echo '';
    } else {
        foreach ($suppliers as $supplier) {
            echo '<div class="supplier-option" 
                       data-id="' . htmlspecialchars($supplier['SupplierID']) . '"
                       data-phone="' . htmlspecialchars($supplier['Phone'] ?? '') . '"
                       data-email="' . htmlspecialchars($supplier['Email'] ?? '') . '">' 
                       . htmlspecialchars($supplier['Name']) . 
                  '</div>';
        }
    }
    exit;
}

// Handle supplier creation
if (isset($_POST['action']) && $_POST['action'] === 'create_supplier') {
    try {
        $pdo = require '../config/db_connection.php';
        
        if (empty($_POST['name'])) {
            throw new Exception("Supplier name is required");
        }
        
        if (empty($_POST['phone']) && empty($_POST['email'])) {
            throw new Exception("Either phone or email is required for new suppliers");
        }
        
        $stmt = $pdo->prepare("INSERT INTO Suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['phone'] ?? null,
            $_POST['email'] ?? null
        ]);
        
        $supplierID = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'supplierID' => $supplierID,
            'message' => 'Supplier created successfully'
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle part search suggestions
if (isset($_POST['part_query'])) {
    $searchTerm = $_POST['part_query'];
    $parts = Invoice::getPartSuggestions($searchTerm);
    
    if ($parts === false) {
        echo '<div class="error">Error fetching parts</div>';
    } else if (empty($parts)) {
        echo '';
    } else {
        foreach ($parts as $part) {
            echo '<div class="part-option">' . htmlspecialchars($part['PartDesc']) . '</div>';
        }
    }
    exit;
}

// Handle supplier update
if (isset($_POST['supplierID']) && isset($_POST['name']) && (isset($_POST['phone']) || isset($_POST['email']))) {
    $result = Invoice::updateSupplier(
        $_POST['supplierID'],
        $_POST['name'],
        $_POST['phone'] ?? '',
        $_POST['email'] ?? ''
    );
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Supplier updated successfully' : 'Failed to update supplier'
    ]);
    exit;
}

// Check for duplicate invoice number
if (isset($_POST['check_invoice']) && isset($_POST['invoice_nr'])) {
    $invoice_nr = trim($_POST['invoice_nr']);
    
    $check_sql = "SELECT COUNT(*) as count FROM Invoices WHERE InvoiceNr = :invoice_nr";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['invoice_nr' => $invoice_nr]);
    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['exists' => $result['count'] > 0]);
    exit;
}
?>
