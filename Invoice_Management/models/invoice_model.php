<?php
require_once '../includes/sanitize_inputs.php';
require_once '../config/db_connection.php';

class Invoice {
    public $id;
    public $invoiceNr;
    public $dateCreated;
    public $supplierID;
    public $supplierName;
    public $supplierPhone;
    public $supplierEmail;
    public $vat;
    public $total;
    public array $parts;
    public $pdf;

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

    // Getters
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

    // Setters
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

    // Add new method to get supplier suggestions
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

    // Add new method to update supplier
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

    // Method to get all invoices with basic information
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

    // Method to get a single invoice with all its details
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

    // Add new method to get part suggestions
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

class InvoiceManagement {
    private $conn;
    private $view_only;

    public function __construct() {
        $this->conn = require '../config/db_connection.php';
        $this->view_only = isset($_POST['view_only']) ? true : false;
    }

    // Add validateEmail method
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

    // Add method as an alias for Create
    public function Add($data) {
        return $this->Create($data);
    }

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

    public function __destruct() {
        $this->conn = null;
    }
}

// Handle AJAX requests
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
?>
