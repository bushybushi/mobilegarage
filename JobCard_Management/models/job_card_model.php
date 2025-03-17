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
    private $photo;
    private $licensePlate;
    private $parts;
    private $partPrices;
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
        $this->photo = $data['photos'] ?? null;
        $this->licensePlate = $data['registration'] ?? null;
        $this->parts = $data['parts'] ?? [];
        $this->partPrices = $data['partPrices'] ?? [];
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
                    Rides, DriveCosts, Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                $updatePartSql = "UPDATE Parts SET Sold = Sold + 1, Stock = Stock - 1 WHERE PartID = ?";
                $updatePartStmt = $pdo->prepare($updatePartSql);
                
                foreach ($this->parts as $index => $partId) {
                    if (!empty($partId)) {
                        $price = $this->partPrices[$index] ?? 0;
                        $stmt->execute([$this->jobId, $partId, 1, $price]);
                        
                        // Update the parts inventory (increment Sold, decrement Stock)
                        $updatePartStmt->execute([$partId]);
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
                    Photo = ? WHERE JobID = ?";
            
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
                $data['photos'],
                $jobId
            ]);

            // Update JobCar table
            $sql = "UPDATE JobCar SET LicenseNr = ? WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['registration'], $jobId]);

            // Update parts
            if (!empty($data['parts'])) {
                // First, get existing parts to restore stock counts
                $existingPartsSql = "SELECT PartID, PiecesSold FROM JobCardParts WHERE JobID = ? ORDER BY PiecesSold DESC";
                $existingPartsStmt = $pdo->prepare($existingPartsSql);
                $existingPartsStmt->execute([$jobId]);
                $existingParts = $existingPartsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Restore stock for parts that will be removed or changed
                $restoreStockSql = "UPDATE Parts SET Sold = Sold - ?, Stock = Stock + ? WHERE PartID = ?";
                $restoreStockStmt = $pdo->prepare($restoreStockSql);
                
                foreach ($existingParts as $part) {
                    $restoreStockStmt->execute([$part['PiecesSold'], $part['PiecesSold'], $part['PartID']]);
                }
                
                // Delete existing parts
                $sql = "DELETE FROM JobCardParts WHERE JobID = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$jobId]);

                // Insert new parts
                $sql = "INSERT INTO JobCardParts (JobID, PartID, PiecesSold, PricePerPiece) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                // Prepare SQL for updating parts inventory
                $updatePartSql = "UPDATE Parts SET Sold = Sold + 1, Stock = Stock - 1 WHERE PartID = ?";
                $updatePartStmt = $pdo->prepare($updatePartSql);
                
                foreach ($data['parts'] as $index => $partId) {
                    if (!empty($partId)) {
                        $price = $data['partPrices'][$index] ?? 0;
                        $stmt->execute([$jobId, $partId, 1, $price]);
                        
                        // Update the parts inventory (increment Sold, decrement Stock)
                        $updatePartStmt->execute([$partId]);
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

            // Delete from JobCardParts
            $sql = "DELETE FROM JobCardParts WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete from JobCar
            $sql = "DELETE FROM JobCar WHERE JobID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jobId]);

            // Delete from InvoiceJob
            $sql = "DELETE FROM InvoiceJob WHERE JobID = ?";
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