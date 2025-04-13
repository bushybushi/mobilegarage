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
        
        // Handle parts data
        if (isset($data['parts'])) {
            if (is_string($data['parts'])) {
                $decodedParts = json_decode($data['parts'], true);
                if (is_array($decodedParts)) {
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
                $this->parts = $data['parts'];
            }
        } else {
            $this->parts = [];
        }
        
        $this->partPrices = $data['partPrices'] ?? [];
        $this->partQuantities = $data['partQuantities'] ?? array_fill(0, count($this->parts), 1);
        $this->totalCosts = $data['totalCosts'] ?? 0;
    }

    // Getters
    public function getJobId(): mixed { return $this->jobId; }
    public function getCustomerId(): mixed { return $this->customerId; }
    public function getLocation(): mixed { return $this->location; }
    public function getDateCall(): mixed { return $this->dateCall; }
    public function getJobDesc(): mixed { return $this->jobDesc; }
    public function getJobReport(): mixed { return $this->jobReport; }
    public function getDateStart(): mixed { return $this->dateStart; }
    public function getDateFinish(): mixed { return $this->dateFinish; }
    public function getRides(): mixed { return $this->rides; }
    public function getDriveCosts(): mixed { return $this->driveCosts; }
    public function getAdditionalCost(): mixed { return $this->additionalCost; }
    public function getPhoto(): mixed { return $this->photo; }
    public function getLicensePlate(): mixed { return $this->licensePlate; }
    public function getParts(): array { return $this->parts; }
    public function getPartPrices(): array { return $this->partPrices; }
    public function getPartQuantities(): array { return $this->partQuantities; }
    public function getTotalCosts(): mixed { return $this->totalCosts; }

    // Setters
    public function setJobId($jobId): void { $this->jobId = $jobId; }
    public function setCustomerId($customerId): void { $this->customerId = $customerId; }
    public function setLocation($location): void { $this->location = $location; }
    public function setDateCall($dateCall): void { $this->dateCall = $dateCall; }
    public function setJobDesc($jobDesc): void { $this->jobDesc = $jobDesc; }
    public function setJobReport($jobReport): void { $this->jobReport = $jobReport; }
    public function setDateStart($dateStart): void { $this->dateStart = $dateStart; }
    public function setDateFinish($dateFinish): void { $this->dateFinish = $dateFinish; }
    public function setRides($rides): void { $this->rides = $rides; }
    public function setDriveCosts($driveCosts): void { $this->driveCosts = $driveCosts; }
    public function setAdditionalCost($additionalCost): void { $this->additionalCost = $additionalCost; }
    public function setPhoto($photo): void { $this->photo = $photo; }
    public function setLicensePlate($licensePlate): void { $this->licensePlate = $licensePlate; }
    public function setParts($parts): void { $this->parts = $parts; }
    public function setPartPrices($partPrices): void { $this->partPrices = $partPrices; }
    public function setPartQuantities($partQuantities): void { $this->partQuantities = $partQuantities; }
    public function setTotalCosts($totalCosts): void { $this->totalCosts = $totalCosts; }

    // Add new method to get customer suggestions
    public static function getCustomerSuggestions($searchTerm) {
        global $pdo;
        try {
            $sql = "SELECT CustomerID, FirstName, LastName, Phone, Email 
                    FROM Customers 
                    WHERE CONCAT(FirstName, ' ', LastName) LIKE :search 
                    ORDER BY FirstName ASC 
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => '%' . $searchTerm . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCustomerSuggestions: " . $e->getMessage());
            return false;
        }
    }

    // Add new method to update customer
    public static function updateCustomer($customerID, $firstName, $lastName, $phone, $email) {
        global $pdo;
        try {
            $sql = "UPDATE Customers 
                    SET FirstName = ?, LastName = ?, Phone = ?, Email = ?
                    WHERE CustomerID = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$firstName, $lastName, $phone, $email, $customerID]);
        } catch (PDOException $e) {
            error_log("Error in updateCustomer: " . $e->getMessage());
            return false;
        }
    }

    // Method to get all job cards with basic information
    public static function getAllJobCards() {
        try {
            $pdo = require '../config/db_connection.php';
            
            $sql = "
                SELECT j.*, c.FirstName, c.LastName, c.Phone, c.Email, 
                       car.LicenseNr as LicensePlate
                FROM JobCards j
                LEFT JOIN Customers c ON j.CustomerID = c.CustomerID
                LEFT JOIN JobCar car ON j.JobID = car.JobID
                ORDER BY j.DateCall DESC
            ";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getAllJobCards: " . $e->getMessage());
            return false;
        }
    }

    // Method to get a single job card with all its details
    public static function getJobCardById($id) {
        try {
            $pdo = require '../config/db_connection.php';
            
            // Get job card details
            $sql = "SELECT j.*, c.FirstName, c.LastName, c.Phone, c.Email, 
                           car.LicenseNr as LicensePlate
                    FROM JobCards j
                    LEFT JOIN Customers c ON j.CustomerID = c.CustomerID
                    LEFT JOIN JobCar car ON j.JobID = car.JobID
                    WHERE j.JobID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $jobCard = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$jobCard) {
                error_log("No job card found with ID: " . $id);
                return null;
            }
            
            // Get parts for this job card
            $partsSql = "SELECT p.*, jp.PiecesSold, jp.PricePerPiece 
                        FROM Parts p 
                        JOIN JobCardParts jp ON p.PartID = jp.PartID 
                        WHERE jp.JobID = ?";
            
            $partsStmt = $pdo->prepare($partsSql);
            $partsStmt->execute([$id]);
            $partsList = $partsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add parts to the job card data
            $jobCard['parts'] = $partsList;
            
            return $jobCard;
            
        } catch (PDOException $e) {
            error_log("Error fetching job card: " . $e->getMessage());
            return null;
        }
    }

    // Add new method to get part suggestions
    public static function getPartSuggestions($searchTerm) {
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

    public function save(): bool {
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

    public static function update($jobId, $data): bool {
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

    public static function delete($jobId): bool {
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

// Handle AJAX requests
if (isset($_POST['query']) && !isset($_POST['dateCall'])) {
    $searchTerm = $_POST['query'];
    $customers = JobCard::getCustomerSuggestions($searchTerm);
    
    if ($customers === false) {
        echo '<div class="error">Error fetching customers</div>';
    } else if (empty($customers)) {
        echo '';
    } else {
        foreach ($customers as $customer) {
            echo '<div class="customer-option" 
                       data-id="' . htmlspecialchars($customer['CustomerID']) . '"
                       data-phone="' . htmlspecialchars($customer['Phone'] ?? '') . '"
                       data-email="' . htmlspecialchars($customer['Email'] ?? '') . '">' 
                       . htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']) . 
                  '</div>';
        }
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'create_customer') {
    try {
        $pdo = require '../config/db_connection.php';
        
        if (empty($_POST['firstName']) || empty($_POST['lastName'])) {
            throw new Exception("Customer name is required");
        }
        
        if (empty($_POST['phone']) && empty($_POST['email'])) {
            throw new Exception("Either phone or email is required for new customers");
        }
        
        $stmt = $pdo->prepare("INSERT INTO Customers (FirstName, LastName, Phone, Email) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['firstName'],
            $_POST['lastName'],
            $_POST['phone'] ?? null,
            $_POST['email'] ?? null
        ]);
        
        $customerID = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'customerID' => $customerID,
            'message' => 'Customer created successfully'
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
    $parts = JobCard::getPartSuggestions($searchTerm);
    
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

if (isset($_POST['customerID']) && isset($_POST['firstName']) && isset($_POST['lastName']) && (isset($_POST['phone']) || isset($_POST['email']))) {
    $result = JobCard::updateCustomer(
        $_POST['customerID'],
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['phone'] ?? '',
        $_POST['email'] ?? ''
    );
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Customer updated successfully' : 'Failed to update customer'
    ]);
    exit;
}
?> 
