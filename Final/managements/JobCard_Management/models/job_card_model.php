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
        $this->jobId = $data['id'] ?? null;
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
        
        // Handle part prices - check both lowercase and camelCase versions
        $this->partPrices = $data['partPrices'] ?? $data['partprices'] ?? [];
        
        // Handle part quantities
        $this->partQuantities = $data['partQuantities'] ?? $data['partquantities'] ?? array_fill(0, count($this->parts), 1);
        
        $this->totalCosts = $data['totalCosts'] ?? 0;
    }

    public function save() {
        global $pdo;
        try {
            $pdo->beginTransaction();

            // Process dates - convert empty strings to NULL
            $dateStart = empty($this->dateStart) ? null : $this->dateStart;
            $dateFinish = empty($this->dateFinish) ? null : $this->dateFinish;

            // Insert into jobcards table
            $sql = "INSERT INTO jobcards (Location, DateCall, JobDesc, JobReport, DateStart, DateFinish, 
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

            // Insert into jobcar table
            $sql = "INSERT INTO jobcar (JobID, LicenseNr) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->jobId, $this->licensePlate]);

            // Insert parts and their prices
            if (!empty($this->parts)) {
                $sql = "INSERT INTO jobcardparts (JobID, PartID, PiecesSold, PricePerPiece) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                // Prepare SQL for updating parts inventory
                $updatePartSql = "UPDATE parts SET Sold = Sold + ?, Stock = Stock - ? WHERE PartID = ?";
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
                FROM jobcards j 
                LEFT JOIN jobcar c ON j.JobID = c.JobID 
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

            // Handle photos - ensure it's a valid JSON string
            $photos = null;
            if (isset($data['photos'])) {
                if (is_string($data['photos'])) {
                    $photos = $data['photos'];
                } else if (is_array($data['photos'])) {
                    $photos = json_encode($data['photos']);
                }
            }

            // Update jobcards table
            $sql = "UPDATE jobcards SET 
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
                $photos,
                $jobId
            ]);

            // Update jobcar table
            $sql = "UPDATE jobcar SET LicenseNr = ? WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['registration'], $jobId]);

            // Process parts data
            $parts = isset($data['parts']) ? $data['parts'] : [];
            $partPrices = isset($data['partPrices']) ? $data['partPrices'] : [];
            $partQuantities = isset($data['partQuantities']) ? $data['partQuantities'] : [];

            // First, delete all existing parts for this job
            $sql = "DELETE FROM jobcardparts WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Then insert the updated parts with their new prices
            if (!empty($parts)) {
                $sql = "INSERT INTO jobcardparts (JobID, PartID, PiecesSold, PricePerPiece) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                foreach ($parts as $index => $partId) {
                    if (!empty($partId)) {
                        $price = $partPrices[$index] ?? 0;
                        $quantity = $partQuantities[$index] ?? 1;
                        
                        $stmt->execute([$jobId, $partId, $quantity, $price]);
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
            $partsSql = "SELECT PartID, PiecesSold FROM jobcardparts WHERE JobID = ? ORDER BY PiecesSold DESC";
            $partsStmt = $pdo->prepare($partsSql);
            $partsStmt->execute([$jobId]);
            $parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Restore stock for each part
            $restoreStockSql = "UPDATE parts SET Sold = Sold - ?, Stock = Stock + ? WHERE PartID = ?";
            $restoreStockStmt = $pdo->prepare($restoreStockSql);
            
            foreach ($parts as $part) {
                $restoreStockStmt->execute([$part['PiecesSold'], $part['PiecesSold'], $part['PartID']]);
            }

            // Get invoice IDs associated with this job
            $invoiceSql = "SELECT InvoiceID FROM invoicejob WHERE JobID = ?";
            $invoiceStmt = $pdo->prepare($invoiceSql);
            $invoiceStmt->execute([$jobId]);
            $invoiceIds = $invoiceStmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete from invoicejob first
            $sql = "DELETE FROM invoicejob WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete invoices that were linked to this job
            if (!empty($invoiceIds)) {
                $invoicePlaceholders = implode(',', array_fill(0, count($invoiceIds), '?'));
                
                // Delete from partssupply first if it exists
                $sql = "DELETE FROM partssupply WHERE InvoiceID IN ($invoicePlaceholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($invoiceIds);
                
                // Delete the invoices
                $sql = "DELETE FROM invoices WHERE InvoiceID IN ($invoicePlaceholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($invoiceIds);
            }

            // Delete from jobcardparts
            $sql = "DELETE FROM jobcardparts WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete from jobcar
            $sql = "DELETE FROM jobcar WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Finally delete the job card
            $sql = "DELETE FROM jobcards WHERE JobID = ?";
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