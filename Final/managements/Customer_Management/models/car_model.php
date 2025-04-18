<?php
require_once '../includes/sanitize_inputs.php';
require_once '../includes/flatten.php';

$pdo = require '../config/db_connection.php';
/**
 * Car class to represent a car entity
 */
class car {
    // Car properties
    public $licenseNr;
    public $brand;
    public $model;
    public $vin;
    public $manuDate;
    public $fuel;
    public $kwHorse;
    public $engine;
    public $kmMiles;
    public $color;
    public $comments;

    /**
     * Constructor to initialize car properties
     * @param string|null $licenseNr License Number
     * @param string|null $brand Car Brand
     * @param string|null $model Car Model
     * @param string|null $vin Vehicle Identification Number
     * @param string|null $manuDate Manufacturing Date
     * @param string|null $fuel Fuel Type
     * @param float|null $kwHorse Power in KW/Horse
     * @param string|null $engine Engine Details
     * @param float|null $kmMiles Kilometers/Miles
     * @param string|null $color Car Color
     * @param string|null $comments Additional Comments
     */
    function __construct($licenseNr = null, $brand = null, $model = null, $vin = null, $manuDate = null, 
                        $fuel = null, $kwHorse = null, $engine = null, $kmMiles = null, $color = null, $comments = null) {
        $this->editLicenseNr($licenseNr);
        $this->editBrand($brand);
        $this->editModel($model);
        $this->editVIN($vin);
        $this->editManuDate($manuDate);
        $this->editFuel($fuel);
        $this->editKWHorse($kwHorse);
        $this->editEngine($engine);
        $this->editKMMiles($kmMiles);
        $this->editColor($color);
        $this->editComments($comments);
    }

    // Getter methods
    function getLicenseNr() { return $this->licenseNr; }
    function getBrand() { return $this->brand; }
    function getModel() { return $this->model; }
    function getVIN() { return $this->vin; }
    function getManuDate() { return $this->manuDate; }
    function getFuel() { return $this->fuel; }
    function getKWHorse() { return $this->kwHorse; }
    function getEngine() { return $this->engine; }
    function getKMMiles() { return $this->kmMiles; }
    function getColor() { return $this->color; }
    function getComments() { return $this->comments; }

    // Setter methods
    function editLicenseNr($licenseNr) { $this->licenseNr = $licenseNr; }
    function editBrand($brand) { $this->brand = $brand; }
    function editModel($model) { $this->model = $model; }
    function editVIN($vin) { $this->vin = $vin; }
    function editManuDate($manuDate) { $this->manuDate = $manuDate; }
    function editFuel($fuel) { $this->fuel = $fuel; }
    function editKWHorse($kwHorse) { $this->kwHorse = $kwHorse; }
    function editEngine($engine) { $this->engine = $engine; }
    function editKMMiles($kmMiles) { $this->kmMiles = $kmMiles; }
    function editColor($color) { $this->color = $color; }
    function editComments($comments) { $this->comments = $comments; }

    /**
     * Function to delete a car from the database
     * @param bool $deleteJobCards Whether to delete associated job cards
     * @return bool True if deletion was successful, false otherwise
     */
    function Delete($deleteJobCards = false) {
        global $pdo;
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Check if car has job cards
            $checkJobCardsSql = "SELECT COUNT(*) as count FROM jobcar WHERE LicenseNr = ?";
            $checkJobCardsStmt = $pdo->prepare($checkJobCardsSql);
            $checkJobCardsStmt->execute([$this->licenseNr]);
            $jobCardsCount = $checkJobCardsStmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($jobCardsCount > 0 && !$deleteJobCards) {
                throw new Exception("Cannot delete car because it has associated job cards. Please delete the job cards first or set deleteJobCards to true.");
            }

            // Delete from JobCar table if requested
            if ($deleteJobCards) {
                $jobCarSql = "DELETE FROM jobcar WHERE LicenseNr = ?";
                $jobCarStmt = $pdo->prepare($jobCarSql);
                $jobCarStmt->execute([$this->licenseNr]);
            }

            // Delete from CarAssoc table
            $carAssocSql = "DELETE FROM carassoc WHERE LicenseNr = ?";
            $carAssocStmt = $pdo->prepare($carAssocSql);
            $carAssocStmt->execute([$this->licenseNr]);

            // Delete from Cars table
            $carSql = "DELETE FROM cars WHERE LicenseNr = ?";
            $carStmt = $pdo->prepare($carSql);
            $carStmt->execute([$this->licenseNr]);

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }
    }
}

/**
 * CarManagement class to handle car-related operations
 */
class carManagement {
    public $sInput;
    public $car;

    /**
     * Constructor to initialize carManagement and handle form submission
     */
    function __construct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $this->sInput = sanitizeInputs($_POST);

            // Validate required fields
            if (empty($this->sInput['licenseNr'])) {
                die("Error: License number is required. Please provide a valid license number.");
            }
            if (empty($this->sInput['brand'])) {
                die("Error: Brand is required. Please provide a valid brand.");
            }
            if (empty($this->sInput['model'])) {
                die("Error: Model is required. Please provide a valid model.");
            }
            if (empty($this->sInput['vin'])) {
                die("Error: VIN is required. Please provide a valid VIN.");
            }

            // Initialize car object with sanitized inputs
            $this->car = new car(
                $this->sInput['licenseNr'],
                $this->sInput['brand'],
                $this->sInput['model'],
                $this->sInput['vin'],
                $this->sInput['manuDate'] ?? null,
                $this->sInput['fuel'] ?? null,
                $this->sInput['kwHorse'] ?? null,
                $this->sInput['engine'] ?? null,
                $this->sInput['kmMiles'] ?? null,
                $this->sInput['color'] ?? null,
                $this->sInput['comments'] ?? null
            );
        } else {
            die("Error: Invalid request method.");
        }
    }

    /**
     * Function to add a new car to the database
     */
    function Add() {
        global $pdo;

        // Validate car object
        if (!isset($this->car)) {
            throw new Exception("Car object is not properly initialized.");
        }

        try {
            // Start transaction
            $pdo->beginTransaction();

            // Get and clean license number
            $licenseNr = trim($this->car->getLicenseNr());
            // Remove any non-alphanumeric characters except spaces
            $licenseNr = preg_replace('/[^A-Za-z0-9\s]/', '', $licenseNr);
            
            // Debug: Log the license number
            error_log("Attempting to add car with license plate: " . $licenseNr);

            // First check if license number exists in Cars table
            $checkCarSql = "SELECT COUNT(*) FROM cars WHERE LOWER(TRIM(LicenseNr)) = LOWER(?)";
            $checkCarStmt = $pdo->prepare($checkCarSql);
            $checkCarStmt->execute([$licenseNr]);
            $carCount = $checkCarStmt->fetchColumn();
            
            // Debug: Log the count
            error_log("Found " . $carCount . " existing cars with license plate: " . $licenseNr);

            // Then check if it exists in CarAssoc table
            $checkAssocSql = "SELECT COUNT(*) FROM carassoc WHERE LOWER(TRIM(LicenseNr)) = LOWER(?)";
            $checkAssocStmt = $pdo->prepare($checkAssocSql);
            $checkAssocStmt->execute([$licenseNr]);
            $assocCount = $checkAssocStmt->fetchColumn();
            
            // Debug: Log the association count
            error_log("Found " . $assocCount . " existing associations with license plate: " . $licenseNr);

            if ($carCount > 0 || $assocCount > 0) {
                // Get the existing license plate for debugging
                $existingSql = "SELECT LicenseNr FROM cars WHERE LOWER(TRIM(LicenseNr)) = LOWER(?)";
                $existingStmt = $pdo->prepare($existingSql);
                $existingStmt->execute([$licenseNr]);
                $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Existing license plate in database: " . ($existing ? $existing['LicenseNr'] : 'none'));
                throw new Exception("A car with license number '" . $licenseNr . "' already exists in the system.");
            }

            // Insert car information
            $carSql = "INSERT INTO cars (LicenseNr, Brand, Model, VIN, ManuDate, Fuel, KWHorse, Engine, KMMiles, Color, Comments) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $carStmt = $pdo->prepare($carSql);
            $carStmt->execute([
                $licenseNr,
                $this->car->getBrand(),
                $this->car->getModel(),
                $this->car->getVIN(),
                $this->car->getManuDate(),
                $this->car->getFuel(),
                $this->car->getKWHorse(),
                $this->car->getEngine(),
                $this->car->getKMMiles(),
                $this->car->getColor(),
                $this->car->getComments()
            ]);

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Function to update existing car information
     */
    function Update() {
        global $pdo;
        try {
            // Get the old license number from the form data
            $oldLicenseNr = isset($this->sInput['oldLicenseNr']) ? $this->sInput['oldLicenseNr'] : $this->car->getLicenseNr();
            $newLicenseNr = $this->car->getLicenseNr();

            // Start transaction
            $pdo->beginTransaction();

            try {
                // If license number is being changed
                if ($oldLicenseNr !== $newLicenseNr) {
                    // Update CarAssoc table first
                    $carAssocSql = "UPDATE carassoc SET LicenseNr = ? WHERE LicenseNr = ?";
                    $carAssocStmt = $pdo->prepare($carAssocSql);
                    $carAssocStmt->execute([$newLicenseNr, $oldLicenseNr]);

                    // Update Cars table with new license number
                    $carSql = "UPDATE cars 
                              SET LicenseNr = ?, Brand = ?, Model = ?, VIN = ?, ManuDate = ?, 
                                  Fuel = ?, KWHorse = ?, Engine = ?, KMMiles = ?, Color = ?, Comments = ?
                              WHERE LicenseNr = ?";
                    $carStmt = $pdo->prepare($carSql);
                    $carStmt->execute([
                        $newLicenseNr,
                        $this->car->getBrand(),
                        $this->car->getModel(),
                        $this->car->getVIN(),
                        $this->car->getManuDate(),
                        $this->car->getFuel(),
                        $this->car->getKWHorse(),
                        $this->car->getEngine(),
                        $this->car->getKMMiles(),
                        $this->car->getColor(),
                        $this->car->getComments(),
                        $oldLicenseNr
                    ]);
                } else {
                    // If license number is not changing, just update other fields
                    $carSql = "UPDATE cars 
                              SET Brand = ?, Model = ?, VIN = ?, ManuDate = ?, 
                                  Fuel = ?, KWHorse = ?, Engine = ?, KMMiles = ?, 
                                  Color = ?, Comments = ?
                              WHERE LicenseNr = ?";
                    $carStmt = $pdo->prepare($carSql);
                    $carStmt->execute([
                        $this->car->getBrand(),
                        $this->car->getModel(),
                        $this->car->getVIN(),
                        $this->car->getManuDate(),
                        $this->car->getFuel(),
                        $this->car->getKWHorse(),
                        $this->car->getEngine(),
                        $this->car->getKMMiles(),
                        $this->car->getColor(),
                        $this->car->getComments(),
                        $oldLicenseNr
                    ]);
                }

                // Commit transaction
                $pdo->commit();
                $_SESSION['message'] = "Car Updated Successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ../views/customer_view.php");
                exit;
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                throw $e;
            }
        } catch (PDOException $e) {
            // Handle database errors
            echo "<h1>Error: Unable to Update Car</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }

    /**
     * Function to delete a car and all related information
     */
    function Delete() {
        global $pdo;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Start transaction
                $pdo->beginTransaction();

                // Delete related records in reverse order of dependencies
                $stmt = $pdo->prepare("DELETE FROM jobcar WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                $stmt = $pdo->prepare("DELETE FROM carassoc WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                // Finally, delete the car record
                $stmt = $pdo->prepare("DELETE FROM cars WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                // Commit transaction
                $pdo->commit();

                // Set success message in session
                $_SESSION['message'] = "Car deleted successfully.";
                $_SESSION['message_type'] = "success";
                
                // Redirect to car_main.php
                header("Location: ../views/customer_view.php");
                exit;
            } catch (PDOException $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                echo json_encode(["success" => false, "message" => "Error deleting car: " . $e->getMessage()]);
                exit;
            }
        } else {
            // Handle invalid request method
            echo json_encode(["success" => false, "message" => "Invalid request."]);
            exit;
        }
    }
}
?>
