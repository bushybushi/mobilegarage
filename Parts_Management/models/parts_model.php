<?php
require_once '../includes/sanitize_inputs.php';
require_once '../includes/flatten.php';

$pdo = require '../config/db_connection.php';

/**
 * Part class to represent a part entity
 */
class part {
    // Part properties
    public $id;
    public $description;
    public $priceBulk;
    public $sellPrice;
    public $sold;
    public $stock;

    /**
     * Constructor to initialize part properties
     * @param int|null $id Part ID
     * @param string|null $description Part Description
     * @param float|null $priceBulk Bulk Price
     * @param float|null $sellPrice Selling Price
     * @param int|null $sold Number of parts sold
     * @param int|null $stock Current stock
     */
    function __construct($id = null, $description = null, $priceBulk = null, $sellPrice = null, $sold = null, $stock = null) {
        $this->editID($id);
        $this->editDescription($description);
        $this->editPriceBulk($priceBulk);
        $this->editSellPrice($sellPrice);
        $this->editSold($sold);
        $this->editStock($stock);
    }

    // Getter methods
    function getID() { return $this->id; }
    function getDescription() { return $this->description; }
    function getPriceBulk() { return $this->priceBulk; }
    function getSellPrice() { return $this->sellPrice; }
    function getSold() { return $this->sold; }
    function getStock() { return $this->stock; }

    // Setter methods
    function editID($id) { $this->id = $id; }
    function editDescription($description) { $this->description = $description; }
    function editPriceBulk($priceBulk) { $this->priceBulk = $priceBulk; }
    function editSellPrice($sellPrice) { $this->sellPrice = $sellPrice; }
    function editSold($sold) { $this->sold = $sold; }
    function editStock($stock) { $this->stock = $stock; }
}

/**
 * PartManagement class to handle part-related operations
 */
class partManagement {
    public $sInput;
    public $part;

    /**
     * Constructor to initialize partManagement and handle form submission
     */
    function __construct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $this->sInput = sanitizeInputs($_POST);

            // Check if this is a delete operation
            if (isset($this->sInput['action']) && $this->sInput['action'] === 'delete') {
                // For delete operation, only initialize the part ID
                $this->part = new part($this->sInput['id']);
                return;
            }

            // Validate required fields for add/update operations
            if (empty($this->sInput['description'])) {
                die("Error: Part description is required. Please provide a valid description.");
            }

            // Initialize part object with sanitized inputs
            $this->part = new part(
                $this->sInput['id'] ?? null,
                $this->sInput['description'],
                $this->sInput['priceBulk'] ?? null,
                $this->sInput['sellPrice'] ?? null,
                $this->sInput['sold'] ?? 0,
                $this->sInput['stock'] ?? 0
            );
        } else {
            die("Error: Invalid request method.");
        }
    }

    /**
     * Function to add a new part to the database
     */
    function Add() {
        global $pdo;

        // Validate part object
        if (!isset($this->part)) {
            die("Error: Part object is not properly initialized.");
        }

        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Insert part information
            $partSql = "INSERT INTO Parts (PartDesc, PriceBulk, SellPrice, Sold, Stock) VALUES (?, ?, ?, ?, ?)";
            $partStmt = $pdo->prepare($partSql);
            $partStmt->execute([
                $this->part->getDescription(),
                $this->part->getPriceBulk(),
                $this->part->getSellPrice(),
                $this->part->getSold(),
                $this->part->getStock()
            ]);

            // Get the new part ID
            $this->part->editID($pdo->lastInsertId());

            if (!$this->part->getID()) {
                throw new Exception("Error: Failed to retrieve PartID after insertion.");
            }

            // Insert supplier information if provided
            if (!empty($this->sInput['supplierName'])) {
                // Insert into Suppliers table
                $supplierSql = "INSERT INTO Suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)";
                $supplierStmt = $pdo->prepare($supplierSql);
                $supplierStmt->execute([
                    $this->sInput['supplierName'],
                    $this->sInput['supplierPhone'] ?? null,
                    $this->sInput['supplierEmail'] ?? null
                ]);
                $supplierId = $pdo->lastInsertId();

                // Insert parts supply information directly linking to supplier
                $partsSupplySql = "INSERT INTO PartsSupply (SupplierID, PartID, PiecesPurch, PricePerPiece) VALUES (?, ?, ?, ?)";
                $partsSupplyStmt = $pdo->prepare($partsSupplySql);
                $partsSupplyStmt->execute([
                    $supplierId,
                    $this->part->getID(),
                    $this->sInput['piecesPurch'] ?? 0,
                    $this->sInput['pricePerPiece'] ?? 0
                ]);
            }

            // Commit transaction
            $pdo->commit();
            $_SESSION['message'] = "New Part Added Successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ../views/parts_main.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Function to update an existing part in the database
     */
    function Update() {
        global $pdo;

        // Validate part object
        if (!isset($this->part)) {
            die("Error: Part object is not properly initialized.");
        }

        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Update part information
            $partSql = "UPDATE Parts SET PartDesc = ?, PriceBulk = ?, SellPrice = ?, Sold = ?, Stock = ? WHERE PartID = ?";
            $partStmt = $pdo->prepare($partSql);
            $partStmt->execute([
                $this->part->getDescription(),
                $this->part->getPriceBulk(),
                $this->part->getSellPrice(),
                $this->part->getSold(),
                $this->part->getStock(),
                $this->part->getID()
            ]);

            // Handle supplier information if provided
            if (!empty($this->sInput['supplierName'])) {
                // Check if supplier exists
                $checkSupplierSql = "SELECT SupplierID FROM Suppliers WHERE Name = ?";
                $checkSupplierStmt = $pdo->prepare($checkSupplierSql);
                $checkSupplierStmt->execute([$this->sInput['supplierName']]);
                $supplier = $checkSupplierStmt->fetch(PDO::FETCH_ASSOC);

                if ($supplier) {
                    // Update existing supplier
                    $supplierSql = "UPDATE Suppliers SET PhoneNr = ?, Email = ? WHERE SupplierID = ?";
                    $supplierStmt = $pdo->prepare($supplierSql);
                    $supplierStmt->execute([
                        $this->sInput['supplierPhone'] ?? null,
                        $this->sInput['supplierEmail'] ?? null,
                        $supplier['SupplierID']
                    ]);
                    $supplierId = $supplier['SupplierID'];
                } else {
                    // Insert new supplier
                    $supplierSql = "INSERT INTO Suppliers (Name, PhoneNr, Email) VALUES (?, ?, ?)";
                    $supplierStmt = $pdo->prepare($supplierSql);
                    $supplierStmt->execute([
                        $this->sInput['supplierName'],
                        $this->sInput['supplierPhone'] ?? null,
                        $this->sInput['supplierEmail'] ?? null
                    ]);
                    $supplierId = $pdo->lastInsertId();
                }

                // Check if parts supply record exists
                $checkPartsSupplySql = "SELECT * FROM PartsSupply WHERE PartID = ?";
                $checkPartsSupplyStmt = $pdo->prepare($checkPartsSupplySql);
                $checkPartsSupplyStmt->execute([$this->part->getID()]);
                $partsSupply = $checkPartsSupplyStmt->fetch(PDO::FETCH_ASSOC);

                if ($partsSupply) {
                    // Update existing parts supply record
                    $partsSupplySql = "UPDATE PartsSupply SET SupplierID = ?, PiecesPurch = ?, PricePerPiece = ? WHERE PartID = ?";
                    $partsSupplyStmt = $pdo->prepare($partsSupplySql);
                    $partsSupplyStmt->execute([
                        $supplierId,
                        $this->sInput['piecesPurch'] ?? 0,
                        $this->sInput['pricePerPiece'] ?? 0,
                        $this->part->getID()
                    ]);
                } else {
                    // Insert new parts supply record
                    $partsSupplySql = "INSERT INTO PartsSupply (SupplierID, PartID, PiecesPurch, PricePerPiece) VALUES (?, ?, ?, ?)";
                    $partsSupplyStmt = $pdo->prepare($partsSupplySql);
                    $partsSupplyStmt->execute([
                        $supplierId,
                        $this->part->getID(),
                        $this->sInput['piecesPurch'] ?? 0,
                        $this->sInput['pricePerPiece'] ?? 0
                    ]);
                }
            }

            // Commit transaction
            $pdo->commit();
            $_SESSION['message'] = "Part Updated Successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ../views/parts_main.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Function to delete a part
     */
    function Delete() {
        global $pdo;
        
        // Validate part object and ID
        if (!isset($this->part) || !$this->part->getID()) {
            die("Error: Part ID is required for deletion.");
        }

        try {
            // Start transaction
            $pdo->beginTransaction();

            // Get supplier ID for this part
            $supplierSql = "SELECT SupplierID FROM PartsSupply WHERE PartID = ?";
            $supplierStmt = $pdo->prepare($supplierSql);
            $supplierStmt->execute([$this->part->getID()]);
            $supplierResult = $supplierStmt->fetch(PDO::FETCH_ASSOC);
            $supplierId = $supplierResult ? $supplierResult['SupplierID'] : null;

            // Delete related records first
            $stmt = $pdo->prepare("DELETE FROM JobCardParts WHERE PartID = ?");
            $stmt->execute([$this->part->getID()]);

            $stmt = $pdo->prepare("DELETE FROM PartsSupply WHERE PartID = ?");
            $stmt->execute([$this->part->getID()]);

            // Finally, delete the part record
            $stmt = $pdo->prepare("DELETE FROM Parts WHERE PartID = ?");
            $stmt->execute([$this->part->getID()]);

            // If we have a supplier ID, check if it's used by any other parts
            if ($supplierId) {
                $checkSupplierSql = "SELECT COUNT(*) as count FROM PartsSupply WHERE SupplierID = ?";
                $checkSupplierStmt = $pdo->prepare($checkSupplierSql);
                $checkSupplierStmt->execute([$supplierId]);
                $supplierCount = $checkSupplierStmt->fetch(PDO::FETCH_ASSOC)['count'];

                // If supplier is not used by any other parts, delete the supplier
                if ($supplierCount == 0) {
                    $stmt = $pdo->prepare("DELETE FROM Suppliers WHERE SupplierID = ?");
                    $stmt->execute([$supplierId]);
                }
            }

            // Commit transaction
            $pdo->commit();

            // Set success message in session
            $_SESSION['message'] = "Part deleted successfully.";
            $_SESSION['message_type'] = "success";
            
            // Redirect to parts_main.php
            header("Location: ../views/parts_main.php");
            exit;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            die("Error deleting part: " . $e->getMessage());
        }
    }
}
?>
