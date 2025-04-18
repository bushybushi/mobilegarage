<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/
/**
 * Parts Management System
 * 
 * This file contains the core classes for managing parts in the system.
 * It handles database operations, data validation, and provides methods
 * for creating, reading, updating, and deleting parts and their relationships
 * with suppliers.
 */

require_once '../includes/sanitize_inputs.php';
require_once '../config/db_connection.php';

/**
 * Parts Class
 * 
 * This class represents a parts record in the system. It stores all the
 * information about a parts entry including supplier details, financial
 * information, and associated parts. The class provides methods to access
 * and modify this data.
 */
class Parts {
    // Properties to store parts information
    public $id;                  // Unique identifier for the parts record
    public $partsNr;             // Parts number (displayed as '-' if empty or 0)
    public $dateCreated;         // Date when the parts record was created
    public $supplierID;          // ID of the supplier who provided the parts
    public $supplierName;        // Name of the supplier
    public $supplierPhone;       // Phone number of the supplier
    public $supplierEmail;       // Email address of the supplier
    public $vat;                 // Value Added Tax amount
    public $total;               // Total amount of the parts
    public array $parts;         // Array of parts included in this record
    public $pdf;                 // PDF file associated with the parts record

    /**
     * Constructor for the Parts class
     * 
     * Creates a new Parts object with the provided data.
     * If parts number is empty, '0', or just whitespace, it's set to '-'.
     */
    function __construct($id = null, $partsNr = null, $dateCreated = null, $supplierID = null, 
                        $supplierName = null, $supplierPhone = null, $supplierEmail = null,
                        $vat = null, $total = null, array $parts = [], $pdf = null) {
        $this->id = $id;
        $this->partsNr = (!$partsNr || trim($partsNr) === '' || $partsNr === '0') ? '-' : $partsNr;
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

    /**
     * Getter methods
     * 
     * These methods allow access to the private properties of the Parts class.
     * They provide a clean interface for retrieving parts data.
     */
    // Getters
    function getID() { return $this->id; }
    function getPartsNr() { return $this->partsNr; }
    function getDateCreated() { return $this->dateCreated; }
    function getSupplierID() { return $this->supplierID; }
    function getSupplierName() { return $this->supplierName; }
    function getSupplierPhone() { return $this->supplierPhone; }
    function getSupplierEmail() { return $this->supplierEmail; }
    function getVat() { return $this->vat; }
    function getTotal() { return $this->total; }
    function getParts() { return $this->parts; }
    function getPDF() { return $this->pdf; }

    /**
     * Setter methods
     * 
     * These methods allow modification of the private properties of the Parts class.
     * They provide a controlled way to update parts data.
     */
    // Setters
    function setID($id) { $this->id = $id; }
    function setPartsNr($partsNr) { $this->partsNr = $partsNr; }
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
     * This method searches for suppliers whose names match the provided search term.
     * It's used for autocomplete functionality in the user interface.
     * 
     * @param string $searchTerm The term to search for in supplier names
     * @return array|false Array of matching suppliers or false on error
     */
    static function getSupplierSuggestions($searchTerm) {
        global $pdo;
        try {
            $sql = "SELECT SupplierID, Name, PhoneNr as Phone, Email 
                    FROM suppliers 
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
     * This method updates the name, phone, and email of an existing supplier.
     * It's used when editing supplier details from the parts interface.
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
            $sql = "UPDATE suppliers 
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
     * Get all parts with basic information
     * 
     * This method retrieves a list of all parts records with their supplier information.
     * It joins multiple tables to get complete data and orders results by date created (newest first).
     * 
     * @return array|false Array of parts records or false on error
     */
    static function getAllParts() {
        try {
            // Get a fresh connection
            $pdo = require '../config/db_connection.php';
            
            // Get all parts with their supplier information
            $sql = "
                SELECT DISTINCT
                    i.partsid, i.partsnr, i.datecreated, i.vat, i.total,
                    s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail,
                    GROUP_CONCAT(DISTINCT p.PartDesc SEPARATOR ', ') as Parts
                FROM parts i
                LEFT JOIN partssupply ps ON i.partsid = ps.partsid
                LEFT JOIN parts p ON ps.PartID = p.PartID
                LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
                GROUP BY i.partsid, i.partsnr, i.datecreated, i.vat, i.total,
                        s.Name, s.PhoneNr, s.Email
                ORDER BY i.datecreated DESC
            ";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getAllParts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a single parts record with all its details
     * 
     * This method retrieves a complete parts record including all associated parts and supplier information.
     * It first gets the basic parts data, then fetches all related parts and combines them.
     * 
     * @param int $id The ID of the parts record to retrieve
     * @return array|null The parts data or null if not found
     */
    static function getPartsById($id) {
        try {
            $pdo = require '../config/db_connection.php';
            
            // First, get the parts details
            $sql = "SELECT i.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail 
                    FROM parts i 
                    LEFT JOIN suppliers s ON i.SupplierID = s.SupplierID 
                    WHERE i.partsid = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $parts = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parts) {
                error_log("No parts found with ID: " . $id);
                return null;
            }
            
            // Get the parts for this parts
            $partsSql = "SELECT p.*, ps.PiecesPurch, ps.PricePerPiece, ps.PriceBulk, ps.SellPrice 
                        FROM parts p 
                        JOIN partssupply ps ON p.PartID = ps.PartID 
                        WHERE ps.partsid = ?";
            
            $partsStmt = $pdo->prepare($partsSql);
            $partsStmt->execute([$id]);
            $partsList = $partsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add parts to the parts data
            $parts['parts'] = $partsList;
            
            // Ensure all required fields are present
            $requiredFields = [
                'partsid' => $parts['partsid'],
                'partsnr' => $parts['partsnr'],
                'datecreated' => $parts['datecreated'],
                'SupplierID' => $parts['SupplierID'],
                'SupplierName' => $parts['SupplierName'],
                'Vat' => $parts['Vat'],
                'Total' => $parts['Total']
            ];
            
            // Log the data for debugging
            error_log("Parts data fetched: " . json_encode($parts));
            
            // Return the complete parts data
            return array_merge($parts, $requiredFields);
            
        } catch (PDOException $e) {
            error_log("Error fetching parts: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get part suggestions based on search term
     * 
     * This method searches for parts whose descriptions match the provided search term.
     * It's used for autocomplete functionality in the user interface.
     * 
     * @param string $searchTerm The term to search for in part descriptions
     * @return array|false Array of matching parts or false on error
     */
    static function getPartSuggestions($searchTerm) {
        global $pdo;
        try {
            $sql = "SELECT DISTINCT PartDesc 
                    FROM parts 
                    WHERE PartDesc LIKE :search 
                    ORDER BY PartDesc ASC 
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => '%' . $searchTerm . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPartSuggestions: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * PartsManagement Class
 * 
 * This class handles all database operations related to parts, including creating,
 * reading, updating, and deleting parts records and their associated data.
 * It provides a higher-level interface for parts management than the Parts class.
 */
class PartsManagement {
    private $conn;      // Database connection
    private $view_only; // Flag to indicate if the user has view-only permissions

    /**
     * Constructor for PartsManagement
     * 
     * Initializes the database connection and sets the view_only flag
     * based on the POST data.
     */
    public function __construct() {
        try {
            $this->conn = require '../config/db_connection.php';
            if (!$this->conn) {
                error_log("Failed to establish database connection in PartsManagement constructor");
                throw new Exception("Database connection failed");
            }
            $this->view_only = isset($_POST['view_only']) ? true : false;
        } catch (Exception $e) {
            error_log("Error in PartsManagement constructor: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get database connection
     * 
     * This method ensures a valid database connection is available.
     * If the connection is lost, it attempts to establish a new one.
     * 
     * @return PDO|null The database connection or null if connection fails
     */
    public function getConnection() {
        if (!$this->conn) {
            try {
                $this->conn = require '../config/db_connection.php';
            } catch (Exception $e) {
                error_log("Failed to get database connection: " . $e->getMessage());
                return null;
            }
        }
        return $this->conn;
    }

    /**
     * Validate email address format
     * 
     * This method checks if the provided email address has a valid format.
     * It performs basic validation including checking for @ symbol and domain format.
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
     * Add a new parts record (alias for Create)
     * 
     * This is a convenience method that calls the Create method.
     * It provides an alternative name for the same functionality.
     * 
     * @param array $data Parts data
     * @return int|false Parts ID if successful, false otherwise
     */
    public function Add($data) {
        return $this->Create($data);
    }

    /**
     * Create a new parts record
     * 
     * This method creates a new parts record and associated parts in the database.
     * It uses a transaction to ensure data integrity - if any part of the process fails,
     * all changes are rolled back.
     * 
     * @param array $data Parts data including associated parts
     * @return int|false Parts ID if successful, false otherwise
     * @throws PDOException if database error occurs
     */
    public function Create($data) {
        try {
            error_log("Starting parts creation with data: " . json_encode($data));
            
            if (!$this->conn) {
                throw new PDOException("Database connection is not available");
            }
            
            $this->conn->beginTransaction();

            // Insert into parts table
            $sql = "INSERT INTO parts (partsnr, datecreated, vat, total, pdf) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['partsNr'],
                $data['dateCreated'],
                $data['vat'],
                $data['total'],
                $data['pdf'] ?? null
            ]);
            
            $partsId = $this->conn->lastInsertId();
            
            // Handle parts
            if (isset($data['parts']) && is_array($data['parts'])) {
                foreach ($data['parts'] as $part) {
                    // Insert new part
                    $insertSql = "INSERT INTO parts (PartDesc, SupplierID, PiecesPurch, PricePerPiece, PriceBulk, SellPrice, datecreated, Stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
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
                    
                    // Link part to parts
                    $supplySql = "INSERT INTO partssupply (partsid, PartID) VALUES (?, ?)";
                    $insertPartsSupply = $this->conn->prepare($supplySql);
                    $insertPartsSupply->execute([$partsId, $partId]);
                }
            }
            
            $this->conn->commit();
            return $partsId;
            
        } catch (PDOException $e) {
            if ($this->conn && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error creating parts: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * View parts records with optional sorting and pagination
     * 
     * This method retrieves a list of parts records with basic information.
     * Results can be sorted by parts number, date, or supplier name.
     * Pagination is supported to limit the number of results returned.
     * 
     * @param string $sortBy Sort criteria (parts_number, date_asc, date_desc, supplier)
     * @param int $page Page number for pagination
     * @param int $perPage Number of records per page
     * @return array|false Array of parts records with pagination info or false on error
     */
    public function View($sortBy = 'date_desc', $page = 1, $filter = '', $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $filter = trim($filter);
        
        // Build filter clauses
        $filterClauses = [];
        $filterParams = [];
        if ($filter !== '') {
            $filter = '%' . strtolower($filter) . '%';
            $cols = [
                'p.PartDesc','p.DateCreated','p.Vat',
                's.Name','s.PhoneNr','s.Email'
            ];
            foreach ($cols as $_) {
                $filterClauses[] = "LOWER($_) LIKE ?";
                $filterParams[] = $filter;
            }
        }
        
        // COUNT query
        $countSql = "SELECT COUNT(DISTINCT p.PartID) AS total
                     FROM parts p
                     LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID";
        if (count($filterClauses)) {
            $countSql .= " WHERE (" . implode(" OR ", $filterClauses) . ")";
        }
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($filterParams);
        $totalCount = $countStmt->fetchColumn();
        
        // Main query
        $sql = "SELECT p.PartID,p.PartDesc,p.DateCreated,p.Vat,
                       s.Name AS SupplierName,s.PhoneNr AS SupplierPhone,s.Email AS SupplierEmail
                FROM parts p
                LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID";
        if (count($filterClauses)) {
            $sql .= " WHERE (" . implode(" OR ", $filterClauses) . ")";
        }
        // sorting
        switch ($sortBy) {
            case 'parts_number': $sql .= " ORDER BY p.PartID"; break;
            case 'date_asc':     $sql .= " ORDER BY p.DateCreated ASC"; break;
            case 'date_desc':    $sql .= " ORDER BY p.DateCreated DESC"; break;
            case 'supplier':     $sql .= " ORDER BY s.Name ASC"; break;
            default:             $sql .= " ORDER BY p.DateCreated DESC";
        }
        // pagination
        $sql .= " LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        // merge filter params + perPage + offset
        $execParams = array_merge($filterParams, [ (int)$perPage, (int)$offset ]);
        $stmt->execute($execParams);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
          'data'        => $results,
          'total_count' => $totalCount,
          'total_pages' => $perPage ? ceil($totalCount/$perPage) : 1,
          'current_page'=> $page,
          'per_page'    => $perPage,
        ];
    }

    /**
     * View a single parts record by ID
     * 
     * This method retrieves a complete parts record including supplier information.
     * It's used when viewing the details of a specific parts record.
     * 
     * @param int $id The ID of the parts record to retrieve
     * @return array|null The parts data or null if not found
     */
    public function ViewSingle($id) {
        try {
            if (!$this->conn) {
                throw new PDOException("Database connection is not available");
            }

            $sql = "SELECT p.*, s.Name as SupplierName, s.PhoneNr as SupplierPhone, s.Email as SupplierEmail
                    FROM parts p
                    LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
                    WHERE p.PartID = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $part = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$part) {
                error_log("No part found with ID: " . $id);
                return null;
            }

            // Log the data for debugging
            error_log("Part data fetched: " . json_encode($part));

            return $part;

        } catch (PDOException $e) {
            error_log("Error fetching part: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing parts record
     * 
     * This method updates the information of an existing parts record in the database.
     * It can also update the associated supplier information if provided.
     * Uses a transaction to ensure data integrity.
     * 
     * @param int $id The ID of the parts record to update
     * @param array $data The new parts data
     * @return bool True if successful, false otherwise
     */
    public function Update($id, $data) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Update supplier information if provided
            if (isset($data['SupplierID']) && isset($data['SupplierName'])) {
                $supplierStmt = $this->conn->prepare("UPDATE suppliers SET 
                    Name = ?, 
                    PhoneNr = ?, 
                    Email = ?
                    WHERE SupplierID = ?");
                
                $supplierStmt->execute([
                    $data['SupplierName'],
                    $data['SupplierPhone'] ?? null,
                    $data['SupplierEmail'] ?? null,
                    $data['SupplierID']
                ]);
            }

            // Update the part details
            $stmt = $this->conn->prepare("UPDATE parts SET 
                PartDesc = ?, 
                PiecesPurch = ?, 
                PricePerPiece = ?, 
                PriceBulk = ?, 
                SellPrice = ?, 
                Stock = ?,
                DateCreated = ?,
                Vat = ?,
                SupplierID = ?
                WHERE PartID = ?");
            
            $stmt->execute([
                $data['partDesc'],
                $data['piecesPurch'],
                $data['pricePerPiece'],
                $data['priceBulk'],
                $data['sellingPrice'],
                $data['stock'],
                $data['dateCreated'],
                $data['vat'],
                $data['supplierID'],
                $id
            ]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error updating part: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a parts record
     * 
     * This method deletes a parts record and its associated data from the database.
     * It handles foreign key constraints by deleting related records first.
     * Uses a transaction to ensure data integrity.
     * 
     * @param int $id The ID of the parts record to delete
     * @return array Result with success status and message
     */
    public function Delete($id) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection is not available");
            }

            $this->conn->beginTransaction();
            
            // First delete from partssupply to handle foreign key constraints
            $stmt = $this->conn->prepare("DELETE FROM partssupply WHERE PartID = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Failed to delete from partssupply");
            }
            
            // Then delete from partsupplier to handle foreign key constraints
            $stmt = $this->conn->prepare("DELETE FROM partsupplier WHERE PartID = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Failed to delete from partsupplier");
            }
            
            // Finally delete the part itself
            $stmt = $this->conn->prepare("DELETE FROM parts WHERE PartID = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Failed to delete part");
            }
            
            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Part deleted successfully'
            ];
            
        } catch (Exception $e) {
            if ($this->conn && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error deleting part: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get parts associated with a parts record
     * 
     * This method retrieves all parts linked to a specific parts record.
     * It joins multiple tables to get complete part information including supplier details.
     * 
     * @param int $partsId The ID of the parts record
     * @return array Array of parts or empty array on error
     */
    public function getPartsByPartsId($partsId) {
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
                    FROM parts p
                    JOIN partssupply ps ON p.PartID = ps.PartID
                    LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID
                    WHERE ps.partsid = ?
                    ORDER BY p.PartDesc";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$partsId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting parts for parts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of parts records
     * 
     * This method counts the total number of parts records in the database.
     * It's used for pagination and reporting.
     * 
     * @return int Total number of parts records or 0 on error
     */
    public function getTotalParts() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM parts");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total parts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Destructor
     * 
     * Cleans up resources when the object is destroyed.
     * Closes the database connection.
     */
    public function __destruct() {
        $this->conn = null;
    }
}

/**
 * AJAX Request Handlers
 * 
 * The following code handles AJAX requests from the frontend.
 * These sections process form submissions and return appropriate responses.
 */

// Handle supplier search suggestions
if (isset($_POST['query']) && !isset($_POST['dateCreated'])) {
    $searchTerm = $_POST['query'];
    $suppliers = Parts::getSupplierSuggestions($searchTerm);
    
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
        
        $stmt = $pdo->prepare("INSERT INTO suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)");
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
    $parts = Parts::getPartSuggestions($searchTerm);
    
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
if (isset($_POST['SupplierID']) && isset($_POST['Name']) && (isset($_POST['PhoneNr']) || isset($_POST['Email']))) {
    $result = Parts::updateSupplier(
        $_POST['SupplierID'],
        $_POST['Name'],
        $_POST['PhoneNr'] ?? '',
        $_POST['Email'] ?? ''
    );
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Supplier updated successfully' : 'Failed to update supplier'
    ]);
    exit;
}

// Handle AJAX part search requests
if (isset($_POST['action']) && $_POST['action'] === 'search_parts' && isset($_POST['part_query'])) {
    try {
        $searchTerm = $_POST['part_query'];
        $sql = "SELECT p.PartID, p.PartDesc, p.SellPrice, p.Stock, s.Name as SupplierName 
                FROM parts p 
                LEFT JOIN suppliers s ON p.SupplierID = s.SupplierID 
                WHERE p.PartDesc LIKE :search 
                ORDER BY p.PartDesc ASC 
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    } catch (PDOException $e) {
        error_log("Error in part search: " . $e->getMessage());
        echo json_encode([]);
        exit;
    }
}
?>
