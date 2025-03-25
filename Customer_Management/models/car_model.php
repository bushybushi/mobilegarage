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
            die("Error: Car object is not properly initialized.");
        }

        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Insert car information
            $carSql = "INSERT INTO Cars (LicenseNr, Brand, Model, VIN, ManuDate, Fuel, KWHorse, Engine, KMMiles, Color, Comments) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $carStmt = $pdo->prepare($carSql);
            $carStmt->execute([
                $this->car->getLicenseNr(),
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
            $_SESSION['message'] = "New Car Added Successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ../views/customer_main.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Function to update existing car information
     */
    function Update() {
        global $pdo;
        try {
            // Create car object for old data
            $old_car = new car($this->car->getLicenseNr());

            // Fetch old car data
            $carSql = 'SELECT Brand, Model, VIN, ManuDate, Fuel, KWHorse, Engine, KMMiles, Color, Comments 
                       FROM Cars WHERE LicenseNr = ?';
            $carStmt = $pdo->prepare($carSql);
            $carStmt->execute([$old_car->getLicenseNr()]);
            $carData = $carStmt->fetch(PDO::FETCH_ASSOC);

            if ($carData) {
                $old_car->editBrand($carData['Brand']);
                $old_car->editModel($carData['Model']);
                $old_car->editVIN($carData['VIN']);
                $old_car->editManuDate($carData['ManuDate']);
                $old_car->editFuel($carData['Fuel']);
                $old_car->editKWHorse($carData['KWHorse']);
                $old_car->editEngine($carData['Engine']);
                $old_car->editKMMiles($carData['KMMiles']);
                $old_car->editColor($carData['Color']);
                $old_car->editComments($carData['Comments']);
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Update car information
                $carSql = "UPDATE Cars 
                          SET Brand = ?, Model = ?, VIN = ?, ManuDate = ?, Fuel = ?, 
                              KWHorse = ?, Engine = ?, KMMiles = ?, Color = ?, Comments = ?
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
                    $this->car->getLicenseNr()
                ]);

                // Commit transaction
                $pdo->commit();
                $_SESSION['message'] = "Car Updated Successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ../views/customer_main.php");
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
                $stmt = $pdo->prepare("DELETE FROM JobCar WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                $stmt = $pdo->prepare("DELETE FROM CarAssoc WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                // Finally, delete the car record
                $stmt = $pdo->prepare("DELETE FROM Cars WHERE LicenseNr = ?");
                $stmt->execute([$this->car->getLicenseNr()]);

                // Commit transaction
                $pdo->commit();

                // Set success message in session
                $_SESSION['message'] = "Car deleted successfully.";
                $_SESSION['message_type'] = "success";
                
                // Redirect to car_main.php
                header("Location: ../views/customer_main.php");
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
