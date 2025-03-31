<?php
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

class JobCard {
    private $jobId;
    private $customerId;
    private $location;
    private $dateCall;
    private $jobDesc;
    private $jobReport;
    private $dateStart;
    private $dateFinish;
    private $rides;
    private $driveCosts;
    private $additionalCost;
    private $photo;
    private $licensePlate;
    private $parts;
    private $partPrices;
    private $partQuantities;
    private $totalCosts;

    public function __construct($data = []) {
        $this->jobId = $data['jobId'] ?? null;
        $this->customerId = $data['customer'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->dateCall = $data['dateCall'] ?? null;
        $this->jobDesc = $data['jobDescription'] ?? null;
        $this->jobReport = $data['jobReport'] ?? null;
        $this->dateStart = $data['jobStartDate'] ?? null;
        $this->dateFinish = $data['jobEndDate'] ?? null;
        $this->rides = $data['rides'] ?? null;
        $this->driveCosts = $data['driveCosts'] ?? null;
        $this->additionalCost = $data['additionalCost'] ?? 0;
        $this->photo = $data['photos'] ?? null;
        $this->licensePlate = $data['registration'] ?? null;
        
        // Handle parts data - ensure it's always an array
        if (isset($data['parts'])) {
            if (is_string($data['parts'])) {
                // If it's a JSON string, decode it
                $decodedParts = json_decode($data['parts'], true);
                if (is_array($decodedParts)) {
                    // Extract part IDs if it's in the format [{id: ..., name: ..., price: ...}]
                    if (!empty($decodedParts) && isset($decodedParts[0]['id'])) {
                        $this->parts = array_column($decodedParts, 'id');
                        $this->partPrices = array_column($decodedParts, 'price');
                        $this->partQuantities = array_column($decodedParts, 'quantity', null) ?: array_fill(0, count($this->parts), 1);
                    } else {
                        $this->parts = $decodedParts;
                    }
                } else {
                    $this->parts = [];
                }
            } else {
                // If it's already an array, use it directly
                $this->parts = $data['parts'];
            }
        } else {
            $this->parts = [];
        }
        
        // Handle part prices
        $this->partPrices = $data['partPrices'] ?? [];
        
        // Handle part quantities
        $this->partQuantities = $data['partQuantities'] ?? array_fill(0, count($this->parts), 1);
        
        $this->totalCosts = $data['totalCosts'] ?? 0;
    }

    public function save() {
        global $pdo;
        try {
            $pdo->beginTransaction();

            // Process dates - convert empty strings to NULL
            $dateStart = empty($this->dateStart) ? null : $this->dateStart;
            $dateFinish = empty($this->dateFinish) ? null : $this->dateFinish;

            // Insert into JobCards table
            $sql = "INSERT INTO JobCards (Location, DateCall, JobDesc, JobReport, DateStart, DateFinish, 
                    Rides, DriveCosts, AdditionalCost, Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $this->location,
                $this->dateCall,
                $this->jobDesc,
                $this->jobReport,
                $dateStart,
                $dateFinish,
                $this->rides,
                $this->driveCosts,
                $this->additionalCost,
                $this->photo
            ]);

            $this->jobId = $pdo->lastInsertId();

            // Insert into JobCar table
            $sql = "INSERT INTO JobCar (JobID, LicenseNr) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->jobId, $this->licensePlate]);

            // Insert parts and their prices
            if (!empty($this->parts)) {
                $sql = "INSERT INTO JobCardParts (JobID, PartID, PiecesSold, PricePerPiece) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                // Prepare SQL for updating parts inventory
                $updatePartSql = "UPDATE Parts SET Sold = Sold + ?, Stock = Stock - ? WHERE PartID = ?";
                $updatePartStmt = $pdo->prepare($updatePartSql);
                
                foreach ($this->parts as $index => $partId) {
                    if (!empty($partId)) {
                        $price = $this->partPrices[$index] ?? 0;
                        $quantity = $this->partQuantities[$index] ?? 1;
                        
                        $stmt->execute([$this->jobId, $partId, $quantity, $price]);
                        
                        // Update the parts inventory (increment Sold, decrement Stock)
                        $updatePartStmt->execute([$quantity, $quantity, $partId]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getById($jobId) {
        global $pdo;
        
        $sql = "SELECT j.*, c.LicenseNr 
                FROM JobCards j 
                LEFT JOIN JobCar c ON j.JobID = c.JobID 
                WHERE j.JobID = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jobId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($jobId, $data) {
        global $pdo;
        try {
            $pdo->beginTransaction();

            // Process dates - convert empty strings to NULL
            $dateStart = empty($data['jobStartDate']) ? null : $data['jobStartDate'];
            $dateFinish = empty($data['jobEndDate']) ? null : $data['jobEndDate'];

            // Update JobCards table
            $sql = "UPDATE JobCards SET 
                    Location = ?, DateCall = ?, JobDesc = ?, JobReport = ?, 
                    DateStart = ?, DateFinish = ?, Rides = ?, DriveCosts = ?, 
                    AdditionalCost = ?, Photo = ? WHERE JobID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['location'],
                $data['dateCall'],
                $data['jobDescription'],
                $data['jobReport'],
                $dateStart,
                $dateFinish,
                $data['rides'],
                $data['driveCosts'],
                $data['additionalCost'] ?? 0,
                $data['photos'],
                $jobId
            ]);

            // Update JobCar table
            $sql = "UPDATE JobCar SET LicenseNr = ? WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['registration'], $jobId]);

            // Process parts data if it's a JSON string
            $parts = $data['parts'] ?? [];
            $partPrices = $data['partPrices'] ?? [];
            $partQuantities = $data['partQuantities'] ?? [];
            
            if (is_string($parts)) {
                $decodedParts = json_decode($parts, true);
                if (is_array($decodedParts) && !empty($decodedParts) && isset($decodedParts[0]['id'])) {
                    // Extract part IDs, prices and quantities if it's in the format [{id: ..., name: ..., price: ...}]
                    $parts = array_column($decodedParts, 'id');
                    $partPrices = array_column($decodedParts, 'price');
                    $partQuantities = array_column($decodedParts, 'quantity', null) ?: array_fill(0, count($parts), 1);
                } else if (is_array($decodedParts)) {
                    $parts = $decodedParts;
                } else {
                    $parts = [];
                }
            }

            // If quantities are not provided, default to 1 for each part
            if (empty($partQuantities) && !empty($parts)) {
                $partQuantities = array_fill(0, count($parts), 1);
            }

            // Update parts
            if (!empty($parts)) {
                // First, get existing parts
                $existingPartsSql = "SELECT PartID, PiecesSold FROM JobCardParts WHERE JobID = ?";
                $existingPartsStmt = $pdo->prepare($existingPartsSql);
                $existingPartsStmt->execute([$jobId]);
                $existingParts = $existingPartsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Create associative array of existing parts for easy lookup
                $existingPartsMap = [];
                foreach ($existingParts as $part) {
                    $existingPartsMap[$part['PartID']] = $part['PiecesSold'];
                }

                // Prepare statements for stock updates
                $updatePartSql = "UPDATE Parts SET Sold = Sold + ?, Stock = Stock - ? WHERE PartID = ?";
                $updatePartStmt = $pdo->prepare($updatePartSql);

                // Delete existing JobCardParts entries
                $deletePartsSql = "DELETE FROM JobCardParts WHERE JobID = ?";
                $deletePartsStmt = $pdo->prepare($deletePartsSql);
                $deletePartsStmt->execute([$jobId]);

                // Insert new parts and update stock only for changes
                $insertPartSql = "INSERT INTO JobCardParts (JobID, PartID, PiecesSold, PricePerPiece) VALUES (?, ?, ?, ?)";
                $insertPartStmt = $pdo->prepare($insertPartSql);

                foreach ($parts as $index => $partId) {
                    if (!empty($partId)) {
                        $newQuantity = $partQuantities[$index] ?? 1;
                        $price = $partPrices[$index] ?? 0;

                        // Insert the new part record
                        $insertPartStmt->execute([$jobId, $partId, $newQuantity, $price]);

                        // Update stock only if quantity has changed
                        $oldQuantity = $existingPartsMap[$partId] ?? 0;
                        $quantityDiff = $newQuantity - $oldQuantity;
                        
                        if ($quantityDiff != 0) {
                            // Update the parts inventory only for the difference
                            $updatePartStmt->execute([$quantityDiff, $quantityDiff, $partId]);
                        }
                    }
                }

                // Handle removed parts - restore their stock
                foreach ($existingPartsMap as $partId => $oldQuantity) {
                    if (!in_array($partId, $parts)) {
                        // Part was removed, restore its stock
                        $updatePartStmt->execute([-$oldQuantity, -$oldQuantity, $partId]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete($jobId) {
        global $pdo;
        try {
            $pdo->beginTransaction();

            // Get parts used in this job card to restore stock
            $partsSql = "SELECT PartID, PiecesSold FROM JobCardParts WHERE JobID = ? ORDER BY PiecesSold DESC";
            $partsStmt = $pdo->prepare($partsSql);
            $partsStmt->execute([$jobId]);
            $parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Restore stock for each part
            $restoreStockSql = "UPDATE Parts SET Sold = Sold - ?, Stock = Stock + ? WHERE PartID = ?";
            $restoreStockStmt = $pdo->prepare($restoreStockSql);
            
            foreach ($parts as $part) {
                $restoreStockStmt->execute([$part['PiecesSold'], $part['PiecesSold'], $part['PartID']]);
            }

            // Get invoice IDs associated with this job
            $invoiceSql = "SELECT InvoiceID FROM InvoiceJob WHERE JobID = ?";
            $invoiceStmt = $pdo->prepare($invoiceSql);
            $invoiceStmt->execute([$jobId]);
            $invoiceIds = $invoiceStmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete from InvoiceJob first
            $sql = "DELETE FROM InvoiceJob WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete invoices that were linked to this job
            if (!empty($invoiceIds)) {
                $invoicePlaceholders = implode(',', array_fill(0, count($invoiceIds), '?'));
                
                // Delete from PartsSupply first if it exists
                $sql = "DELETE FROM PartsSupply WHERE InvoiceID IN ($invoicePlaceholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($invoiceIds);
                
                // Delete the invoices
                $sql = "DELETE FROM Invoices WHERE InvoiceID IN ($invoicePlaceholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($invoiceIds);
            }

            // Delete from JobCardParts
            $sql = "DELETE FROM JobCardParts WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete from JobCar
            $sql = "DELETE FROM JobCar WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Finally delete the job card
            $sql = "DELETE FROM JobCards WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
?> 
