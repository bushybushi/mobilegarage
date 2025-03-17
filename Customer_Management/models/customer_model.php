<?php
require_once '../includes/sanitize_inputs.php';
require_once '../includes/flatten.php';

$pdo = require '../config/db_connection.php';
/**
 * Customer class to represent a customer entity
 */
class customer {
    // Customer properties
    public $id;
    public $fName;
    public $lName;
    public $company;
    public array $address;
    public array $phone;
    public array $email;

    /**
     * Constructor to initialize customer properties
     * @param int|null $id Customer ID
     * @param string|null $fName First Name
     * @param string|null $lName Last Name
     * @param string|null $company Company Name
     * @param array $address Array of addresses
     * @param array $phone Array of phone numbers
     * @param array $email Array of email addresses
     */
    function __construct($id = null, $fName = null, $lName = null, $company = null, array $address = [], array $phone = [], array $email = []) {
        $this->editID($id);
        $this->editFName($fName);
        $this->editLName($lName);
        $this->editCompany($company);
        $this->editAddress($address);
        $this->editPhone($phone);
        $this->editEmail($email);
    }

    // Getter methods
    function getID() { return $this->id; }
    function getFName() { return $this->fName; }
    function getLName() { return $this->lName; }
    function getCompany() { return $this->company; }
    function getAddress(): array { return $this->address; }
    function getPhone(): array { return $this->phone; }
    function getEmail(): array { return $this->email; }

    // Setter methods
    function editID($id) { $this->id = $id; }
    function editFName($fName) { $this->fName = $fName; }
    function editLName($lName) { $this->lName = $lName; }
    function editCompany($company) { $this->company = $company; }
    function editAddress(array $address) { $this->address = $address; }
    function editPhone(array $phone) { $this->phone = $phone; }
    function editEmail(array $email) { $this->email = $email; }
}

/**
 * CustomerManagement class to handle customer-related operations
 */
class customerManagement {
    public $sInput;
    public $customer;

    /**
     * Constructor to initialize customerManagement and handle form submission
     */
    function __construct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $this->sInput = sanitizeInputs($_POST);


            // Validate required fields
            if (empty($this->sInput['firstName'])) {
                die("Error: First name is required. Please provide a valid first name.");
            }

            // Initialize customer object with sanitized inputs
            $this->customer = new customer(
                $this->sInput['id'] ?? null,
                $this->sInput['firstName'],
                $this->sInput['surname'],
                $this->sInput['companyName'] ?? null,
                $this->sInput['address'] ?? [],
                $this->sInput['phoneNumber'] ?? [],
                $this->sInput['emailAddress'] ?? []
            );
        } else {
            die("Error: Invalid request method.");
        }
    }

    /**
     * Function to add a new customer to the database
     */
    function Add() {
        global $pdo;

        // Validate customer object
        if (!isset($this->customer)) {
            die("Error: customer object is not properly initialized.");
        }

        if (!method_exists($this->customer, 'getPhone')) {
            die("Error: Method getPhone() does not exist in customer class.");
        }

        try {
            // Validate required phone number
            if (empty($this->customer->getPhone())) {
                die("Error: At least one phone number is required.");
            }

            // Start database transaction
            $pdo->beginTransaction();

            // Insert customer basic information
            $customerSql = "INSERT INTO customers (FirstName, LastName, Company) VALUES (?, ?, ?)";
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->execute([
                $this->customer->getFName(),
                $this->customer->getLName(),
                $this->customer->getCompany()
            ]);

            // Get the new customer ID
            $this->customer->editID($pdo->lastInsertId());

            if (!$this->customer->getID()) {
                throw new Exception("Error: Failed to retrieve CustomerID after insertion.");
            }

            // Insert addresses
            foreach ($this->customer->getAddress() as $address) {
                if (!empty($address)) {
                    $addressSql = "INSERT INTO addresses (CustomerID, Address) VALUES (?, ?)";
                    $addressStmt = $pdo->prepare($addressSql);
                    $addressStmt->execute([$this->customer->getID(), $address]);
                }
            }

            // Insert phone numbers
            foreach ($this->customer->getPhone() as $phoneNumber) {
                if (!empty($phoneNumber)) {
                    $phoneSql = "INSERT INTO phonenumbers (CustomerID, Nr) VALUES (?, ?)";
                    $phoneStmt = $pdo->prepare($phoneSql);
                    $phoneStmt->execute([$this->customer->getID(), $phoneNumber]);
                }
            }

            // Insert email addresses
            foreach ($this->customer->getEmail() as $emailAddress) {
                if (!empty($emailAddress)) {
                    $emailSql = "INSERT INTO emails (CustomerID, Emails) VALUES (?, ?)";
                    $emailStmt = $pdo->prepare($emailSql);
                    $emailStmt->execute([$this->customer->getID(), $emailAddress]);
                }
            }

            // Handle car associations
            if (isset($this->sInput['car'])) {
                foreach ($this->sInput['car'] as $index => $carData) {
                    // Skip empty car entries
                    if (empty($carData['brand']) || empty($carData['model']) || empty($carData['licenseNr'])) {
                        continue;
                    }

                    // Insert car information
                    $carSql = "INSERT INTO Cars (LicenseNr, Brand, Model, VIN, ManuDate, Fuel, KWHorse, Engine, KMMiles, Color, Comments) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $carStmt = $pdo->prepare($carSql);
                    $carStmt->execute([
                        $carData['licenseNr'][$index],
                        $carData['brand'][$index],
                        $carData['model'][$index],
                        $carData['vin'][$index],
                        $carData['manuDate'][$index],
                        $carData['fuel'][$index],
                        $carData['kwHorse'][$index] ?? null,
                        $carData['engine'][$index],
                        $carData['kmMiles'][$index],
                        $carData['color'][$index],
                        $carData['comments'][$index] ?? null
                    ]);

                    // Create car-customer association
                    $assocSql = "INSERT INTO CarAssoc (CustomerID, LicenseNr) VALUES (?, ?)";
                    $assocStmt = $pdo->prepare($assocSql);
                    $assocStmt->execute([$this->customer->getID(), $carData['licenseNr'][$index]]);
                }
            }

            // Commit transaction
            $pdo->commit();
            $_SESSION['message'] = "New Customer Added Successfully!";
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
     * Function to update existing customer information
     */
    function Update() {
        global $pdo;
        try {
            // Create customer object for old data
            $old_customer = new customer($this->customer->getID());

            // Fetch old customer data
            $customerSql = 'SELECT firstName, lastName, Company from customers where CustomerID = ?';
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->execute([$old_customer->getID()]);

            $old_customer->editFName($customerStmt->fetchColumn());
            $old_customer->editLName($customerStmt->fetchColumn());
            $old_customer->editCompany($customerStmt->fetchColumn());

            // Fetch old addresses
            $addressSql = 'select Address from Addresses where CustomerID = ?';
            $addressStmt = $pdo->prepare($addressSql);
            $addressStmt->execute([$old_customer->getID()]);
            $old_customer->editAddress($addressStmt->fetchAll());

            // Fetch old phone numbers
            $phoneSql = 'SELECT Nr from PhoneNumbers where CustomerID = ?';
            $phoneStmt = $pdo->prepare($phoneSql);
            $phoneStmt->execute([$old_customer->getID()]);
            $old_customer->editPhone($phoneStmt->fetchAll());

            // Fetch old email addresses
            $emailSql = 'SELECT Emails from Emails where CustomerID = ?';
            $emailStmt = $pdo->prepare($emailSql);
            $emailStmt->execute([$old_customer->getID()]);
            $old_customer->editEmail($emailStmt->fetchAll());

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Update customer basic information if changed
                if ($old_customer->getFName() != $this->customer->getFName() ||
                    $old_customer->getLName() != $this->customer->getLName() ||
                    $old_customer->getCompany() != $this->customer->getCompany()) {

                    $customerSql = "UPDATE customers 
                                    SET firstName = ?, LastName = ?, Company = ?
                                    where CustomerID = ?";
                    $customerStmt = $pdo->prepare($customerSql);
                    $customerStmt->execute([
                        $this->customer->getFName(), 
                        $this->customer->getLName(), 
                        $this->customer->getCompany(), 
                        $this->customer->getID()
                    ]);
                }

                // Flatten arrays for comparison
                $this->customer->editAddress(flattenArray($this->customer->getAddress()));
                $this->customer->editPhone(flattenArray($this->customer->getPhone()));
                $this->customer->editEmail(flattenArray($this->customer->getEmail()));

                $old_customer->editAddress(flattenArray($old_customer->getAddress()));
                $old_customer->editPhone(flattenArray($old_customer->getPhone()));
                $old_customer->editEmail(flattenArray($old_customer->getEmail()));

                // Handle address updates
                $addressToAdd = array_diff($this->customer->getAddress(), $old_customer->getAddress());
                $addressToDelete = array_diff($old_customer->getAddress(), $this->customer->getAddress());

                // Insert new addresses
                if (!empty($addressToAdd)) {
                    $insertStmt = $pdo->prepare("INSERT INTO Addresses (customerID, Address) VALUES (?, ?)");
                    foreach ($addressToAdd as $row) {
                        $insertStmt->execute([$old_customer->getID(), $row]);
                    }
                }

                // Delete removed addresses
                if (!empty($addressToDelete)) {
                    $deleteStmt = $pdo->prepare("DELETE FROM Addresses WHERE customerID = ? AND Address IN (" . str_repeat('?,', count($addressToDelete) - 1) . '?)');
                    $deleteStmt->execute(array_merge([$old_customer->getID()], $addressToDelete));
                }

                // Handle phone number updates
                $phoneToAdd = array_diff($this->customer->getPhone(), $old_customer->getPhone());
                $phoneToDelete = array_diff($old_customer->getPhone(), $this->customer->getPhone());

                // Insert new phone numbers
                if (!empty($phoneToAdd)) {
                    $insertStmt = $pdo->prepare("INSERT INTO phoneNumbers (customerID, Nr) VALUES (?, ?)");
                    foreach ($phoneToAdd as $row) {
                        $insertStmt->execute([$old_customer->getID(), $row]);
                    }
                }

                // Delete removed phone numbers
                if (!empty($phoneToDelete)) {
                    $deleteStmt = $pdo->prepare("DELETE FROM phoneNumbers WHERE customerID = ? AND Nr IN (" . str_repeat('?,', count($phoneToDelete) - 1) . '?)');
                    $deleteStmt->execute(array_merge([$old_customer->getID()], $phoneToDelete));
                }

                // Handle email updates
                $emailToAdd = array_diff($this->customer->getEmail(), $old_customer->getEmail());
                $emailToDelete = array_diff($old_customer->getEmail(), $this->customer->getEmail());

                // Insert new email addresses
                if (!empty($emailToAdd)) {
                    $insertStmt = $pdo->prepare("INSERT INTO Emails (customerID, Emails) VALUES (?, ?)");
                    foreach ($emailToAdd as $row) {
                        $insertStmt->execute([$old_customer->getID(), $row]);
                    }
                }

                // Delete removed email addresses
                if (!empty($emailToDelete)) {
                    $deleteStmt = $pdo->prepare("DELETE FROM Emails WHERE customerID = ? AND Emails IN (" . str_repeat('?,', count($emailToDelete) - 1) . '?)');
                    $deleteStmt->execute(array_merge([$old_customer->getID()], $emailToDelete));
                }

                // Clean up empty entries
                $deleteEmptyStmt = $pdo->prepare("DELETE FROM Addresses WHERE Address IS NULL OR TRIM(Address) = ''");
                $deleteEmptyStmt->execute();
                $deleteEmptyStmt = $pdo->prepare("DELETE FROM phoneNumbers WHERE Nr IS NULL OR TRIM(Nr) = ''");
                $deleteEmptyStmt->execute();
                $deleteEmptyStmt = $pdo->prepare("DELETE FROM Emails WHERE Emails IS NULL OR TRIM(Emails) = ''");
                $deleteEmptyStmt->execute();

                // Commit transaction
                $pdo->commit();
                $_SESSION['message'] = "Customer Updated Successfully!";
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
            echo "<h1>Error: Unable to Update Customer</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }

    /**
     * Function to delete a customer and all related information
     */
    function Delete() {
        global $pdo;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Start transaction
                $pdo->beginTransaction();

                // Delete related records in reverse order of dependencies
                $stmt = $pdo->prepare("DELETE FROM emails WHERE CustomerID = ?");
                $stmt->execute([$this->customer->getID()]);

                $stmt = $pdo->prepare("DELETE FROM phonenumbers WHERE CustomerID = ?");
                $stmt->execute([$this->customer->getID()]);

                $stmt = $pdo->prepare("DELETE FROM addresses WHERE CustomerID = ?");
                $stmt->execute([$this->customer->getID()]);

                // Finally, delete the customer record
                $stmt = $pdo->prepare("DELETE FROM customers WHERE CustomerID = ?");
                $stmt->execute([$this->customer->getID()]);

                // Commit transaction
                $pdo->commit();

                // Set success message in session
                $_SESSION['message'] = "Customer deleted successfully.";
                $_SESSION['message_type'] = "success";
                
                // Redirect to customer_main.php
                header("Location: ../views/customer_main.php");
                exit;
            } catch (PDOException $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                echo json_encode(["success" => false, "message" => "Error deleting customer: " . $e->getMessage()]);
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
