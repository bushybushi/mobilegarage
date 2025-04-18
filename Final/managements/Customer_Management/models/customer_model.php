<?php
require_once dirname(__DIR__) . '/includes/sanitize_inputs.php';
require_once dirname(__DIR__) . '/includes/flatten.php';

$pdo = require dirname(__DIR__) . '/config/db_connection.php';
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

    /**
     * Get customer details with related information
     * @return array|null Customer data with related information
     */
    function getCustomerDetails() {
        global $pdo;
        $sql = "SELECT c.*, 
                (SELECT Address FROM addresses WHERE CustomerID = c.CustomerID LIMIT 1) as Address,
                (SELECT nr FROM phonenumbers WHERE CustomerID = c.CustomerID LIMIT 1) as Phone,
                (SELECT Emails FROM emails WHERE CustomerID = c.CustomerID LIMIT 1) as Email
                FROM customers c
                WHERE c.CustomerID = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's basic information
     * @return array|null Customer basic information
     */
    function getBasicInfo() {
        global $pdo;
        $sql = 'SELECT * from customers where CustomerID = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's addresses
     * @return array Array of addresses
     */
    function getAddresses() {
        global $pdo;
        $sql = 'select Address from addresses where CustomerID = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's phone numbers
     * @return array Array of phone numbers
     */
    function getPhoneNumbers() {
        global $pdo;
        $sql = 'SELECT Nr from phonenumbers where CustomerID = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's email addresses
     * @return array Array of email addresses
     */
    function getEmailAddresses() {
        global $pdo;
        $sql = 'SELECT Emails from emails where CustomerID = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's cars
     * @return array Array of cars associated with the customer
     */
    function getCars() {
        global $pdo;
        $sql = "SELECT c.*, 
                (SELECT Address FROM addresses WHERE CustomerID = c.CustomerID LIMIT 1) as Address,
                (SELECT nr FROM phonenumbers WHERE CustomerID = c.CustomerID LIMIT 1) as Phone,
                (SELECT Emails FROM emails WHERE CustomerID = c.CustomerID LIMIT 1) as Email
                FROM cars c
                JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr
                WHERE ca.CustomerID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['customerId' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * CustomerManagement class to handle customer-related operations
 */
class customerManagement {
    public $sInput;
    public $customer;
    private $pdo;
    private $requirePost;
    private $deleteJobCards;
    private $customerId;

    /**
     * Constructor to initialize customerManagement and handle form submission
     * @param bool $requirePost Whether to require POST method (default: false)
     */
    function __construct($requirePost = false) {
        $this->pdo = require dirname(__DIR__) . '/config/db_connection.php';
        $this->requirePost = $requirePost;
        $this->deleteJobCards = false;
        $this->customerId = null;

        if ($requirePost && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $this->sInput = sanitizeInputs($_POST);

            // Check if this is a delete operation
            if (isset($this->sInput['id']) && isset($this->sInput['deleteCars'])) {
                // For delete operation, we only need the ID
                $this->customer = new customer($this->sInput['id']);
                return;
            }

            // For update/add operations, validate required fields
            if (empty($this->sInput['firstName'])) {
                throw new Exception("First name is required. Please provide a valid first name.");
            }

            // Handle arrays properly
            $addresses = [];
            $phones = [];
            $emails = [];

            // Handle addresses
            if (isset($this->sInput['address'])) {
                if (is_array($this->sInput['address'])) {
                    foreach ($this->sInput['address'] as $addr) {
                        if (!empty(trim($addr))) {
                            $addresses[] = $addr;
                        }
                    }
                } else if (!empty(trim($this->sInput['address']))) {
                    $addresses[] = $this->sInput['address'];
                }
            }

            // Handle phone numbers
            if (isset($this->sInput['phoneNumber'])) {
                if (is_array($this->sInput['phoneNumber'])) {
                    foreach ($this->sInput['phoneNumber'] as $phone) {
                        if (!empty(trim($phone))) {
                            $phones[] = $phone;
                        }
                    }
                } else if (!empty(trim($this->sInput['phoneNumber']))) {
                    $phones[] = $this->sInput['phoneNumber'];
                }
            }

            // Handle email addresses
            if (isset($this->sInput['emailAddress'])) {
                if (is_array($this->sInput['emailAddress'])) {
                    foreach ($this->sInput['emailAddress'] as $email) {
                        if (!empty(trim($email))) {
                            $emails[] = $email;
                        }
                    }
                } else if (!empty(trim($this->sInput['emailAddress']))) {
                    $emails[] = $this->sInput['emailAddress'];
                }
            }

            // Initialize customer object with sanitized inputs
            $this->customer = new customer(
                $this->sInput['id'] ?? null,
                $this->sInput['firstName'],
                $this->sInput['surname'],
                $this->sInput['companyName'] ?? null,
                $addresses,
                $phones,
                $emails
            );
        }
    }

    public function setDeleteJobCards($value) {
        $this->deleteJobCards = $value;
    }

    /**
     * Function to add a new customer to the database
     */
    function Add() {
        global $pdo;
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Insert customer information
            $customerSql = "INSERT INTO customers (FirstName, LastName, Company) VALUES (?, ?, ?)";
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->execute([
                $this->customer->getFName(),
                $this->customer->getLName(),
                $this->customer->getCompany()
            ]);

            // Get the new customer ID
            $customerId = $pdo->lastInsertId();

            // Insert addresses
            if (!empty($this->sInput['address'])) {
                foreach ($this->sInput['address'] as $address) {
                    if (!empty($address)) {
                        $addressSql = "INSERT INTO addresses (CustomerID, Address) VALUES (?, ?)";
                        $addressStmt = $pdo->prepare($addressSql);
                        $addressStmt->execute([$customerId, $address]);
                    }
                }
            }

            // Insert phone numbers
            if (!empty($this->sInput['phoneNumber'])) {
                foreach ($this->sInput['phoneNumber'] as $phoneNumber) {
                    if (!empty($phoneNumber)) {
                        $phoneSql = "INSERT INTO phonenumbers (CustomerID, Nr) VALUES (?, ?)";
                        $phoneStmt = $pdo->prepare($phoneSql);
                        $phoneStmt->execute([$customerId, $phoneNumber]);
                    }
                }
            }

            // Insert email addresses
            if (!empty($this->sInput['emailAddress'])) {
                foreach ($this->sInput['emailAddress'] as $emailAddress) {
                    if (!empty($emailAddress)) {
                        $emailSql = "INSERT INTO emails (CustomerID, Emails) VALUES (?, ?)";
                        $emailStmt = $pdo->prepare($emailSql);
                        $emailStmt->execute([$customerId, $emailAddress]);
                    }
                }
            }

            // Handle car data if present
            if (!empty($this->sInput['car'])) {
                foreach ($this->sInput['car']['licenseNr'] as $index => $licenseNr) {
                    if (!empty($licenseNr)) {
                        // Check if license plate already exists
                        $checkSql = "SELECT COUNT(*) FROM cars WHERE LicenseNr = ?";
                        $checkStmt = $pdo->prepare($checkSql);
                        $checkStmt->execute([$licenseNr]);
                        
                        if ($checkStmt->fetchColumn() > 0) {
                            throw new Exception("A car with license plate " . $licenseNr . " already exists.");
                        }

                        // Insert car information
                        $carSql = "INSERT INTO cars (LicenseNr, Brand, Model, VIN, ManuDate, Fuel, KWHorse, Engine, KMMiles, Color, Comments) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $carStmt = $pdo->prepare($carSql);
                        $carStmt->execute([
                            $licenseNr,
                            $this->sInput['car']['brand'][$index],
                            $this->sInput['car']['model'][$index],
                            $this->sInput['car']['vin'][$index],
                            $this->sInput['car']['manuDate'][$index],
                            $this->sInput['car']['fuel'][$index],
                            !empty($this->sInput['car']['kwHorse'][$index]) ? $this->sInput['car']['kwHorse'][$index] : null,
                            $this->sInput['car']['engine'][$index],
                            $this->sInput['car']['kmMiles'][$index],
                            $this->sInput['car']['color'][$index],
                            !empty($this->sInput['car']['comments'][$index]) ? $this->sInput['car']['comments'][$index] : null
                        ]);

                        // Create car-customer association
                        $assocSql = "INSERT INTO carassoc (CustomerID, LicenseNr) VALUES (?, ?)";
                        $assocStmt = $pdo->prepare($assocSql);
                        $assocStmt->execute([$customerId, $licenseNr]);
                    }
                }
            }

            // Commit transaction
            $pdo->commit();
            
            // Set success message in session for the redirect
            $_SESSION['message'] = "New Customer Added Successfully!";
            $_SESSION['message_type'] = "success";
            
            // Return the new customer ID
            return $customerId;
        } catch (PDOException $e) {
            // Rollback transaction on database error
            $pdo->rollBack();
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            // Rollback transaction on any other error
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Function to update existing customer information
     */
    function Update() {
        global $pdo;
        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Update customer basic information
            $customerSql = "UPDATE customers SET FirstName = ?, LastName = ?, Company = ? WHERE CustomerID = ?";
            $customerStmt = $pdo->prepare($customerSql);
            $customerStmt->execute([
                $this->customer->getFName(),
                $this->customer->getLName(),
                $this->customer->getCompany(),
                $this->customer->getID()
            ]);

            // Get new values from form
            $newAddresses = $this->customer->getAddress();
            $newPhones = $this->customer->getPhone();
            $newEmails = $this->customer->getEmail();

            // Delete existing contact information
            $deleteAddressSql = "DELETE FROM addresses WHERE CustomerID = ?";
            $deletePhoneSql = "DELETE FROM phonenumbers WHERE CustomerID = ?";
            $deleteEmailSql = "DELETE FROM emails WHERE CustomerID = ?";

            $pdo->prepare($deleteAddressSql)->execute([$this->customer->getID()]);
            $pdo->prepare($deletePhoneSql)->execute([$this->customer->getID()]);
            $pdo->prepare($deleteEmailSql)->execute([$this->customer->getID()]);

            // Insert new addresses
            foreach ($newAddresses as $address) {
                if (!empty(trim($address))) {
                    $addressSql = "INSERT INTO addresses (CustomerID, Address) VALUES (?, ?)";
                    $addressStmt = $pdo->prepare($addressSql);
                    $addressStmt->execute([$this->customer->getID(), $address]);
                }
            }

            // Insert new phone numbers
            foreach ($newPhones as $phoneNumber) {
                if (!empty(trim($phoneNumber))) {
                    $phoneSql = "INSERT INTO phonenumbers (CustomerID, Nr) VALUES (?, ?)";
                    $phoneStmt = $pdo->prepare($phoneSql);
                    $phoneStmt->execute([$this->customer->getID(), $phoneNumber]);
                }
            }

            // Insert new email addresses
            foreach ($newEmails as $emailAddress) {
                if (!empty(trim($emailAddress))) {
                    $emailSql = "INSERT INTO emails (CustomerID, Emails) VALUES (?, ?)";
                    $emailStmt = $pdo->prepare($emailSql);
                    $emailStmt->execute([$this->customer->getID(), $emailAddress]);
                }
            }

            // Commit transaction
            $pdo->commit();
            
            // Set success message in session for the redirect
            $_SESSION['message'] = "Customer Updated Successfully!";
            $_SESSION['message_type'] = "success";
            
            // Return success status
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Function to delete a customer and all related information
     */
    function Delete() {
        if ($this->requirePost && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        $this->customerId = $_POST['id'] ?? null;
        $deleteCars = isset($_POST['deleteCars']) ? $_POST['deleteCars'] === 'true' : false;

        if (!$this->customerId) {
            throw new Exception('Customer ID is required');
        }

        try {
            $this->pdo->beginTransaction();

            if ($deleteCars) {
                // Get all cars associated with this customer
                $carSql = "SELECT c.LicenseNr 
                          FROM cars c 
                          JOIN carassoc ca ON c.LicenseNr = ca.LicenseNr 
                          WHERE ca.CustomerID = :customerId";
                $carStmt = $this->pdo->prepare($carSql);
                $carStmt->execute(['customerId' => $this->customerId]);
                $cars = $carStmt->fetchAll(PDO::FETCH_COLUMN);

                if ($this->deleteJobCards) {
                    // Delete job cards associated with these cars
                    foreach ($cars as $licenseNr) {
                        // Get job IDs for this car
                        $jobSql = "SELECT JobID FROM jobcar WHERE LicenseNr = :licenseNr";
                        $jobStmt = $this->pdo->prepare($jobSql);
                        $jobStmt->execute(['licenseNr' => $licenseNr]);
                        $jobIds = $jobStmt->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($jobIds)) {
                            // Delete job card parts
                            $placeholders = str_repeat('?,', count($jobIds) - 1) . '?';
                            $partsSql = "DELETE FROM jobcardparts WHERE JobID IN ($placeholders)";
                            $partsStmt = $this->pdo->prepare($partsSql);
                            $partsStmt->execute($jobIds);

                            // Delete job car associations
                            $jobCarSql = "DELETE FROM jobcar WHERE JobID IN ($placeholders)";
                            $jobCarStmt = $this->pdo->prepare($jobCarSql);
                            $jobCarStmt->execute($jobIds);

                            // Delete job cards
                            $jobSql = "DELETE FROM jobcards WHERE JobID IN ($placeholders)";
                            $jobStmt = $this->pdo->prepare($jobSql);
                            $jobStmt->execute($jobIds);
                        }
                    }
                }

                // Delete car associations for this customer
                $assocSql = "DELETE FROM carassoc WHERE CustomerID = :customerId";
                $assocStmt = $this->pdo->prepare($assocSql);
                $assocStmt->execute(['customerId' => $this->customerId]);

                // Delete only the cars that were associated with this customer
                if (!empty($cars)) {
                    $placeholders = str_repeat('?,', count($cars) - 1) . '?';
                    $carSql = "DELETE FROM cars WHERE LicenseNr IN ($placeholders)";
                    $carStmt = $this->pdo->prepare($carSql);
                    $carStmt->execute($cars);
                }
            }

            // Delete customer's addresses
            $addressSql = "DELETE FROM addresses WHERE CustomerID = :customerId";
            $addressStmt = $this->pdo->prepare($addressSql);
            $addressStmt->execute(['customerId' => $this->customerId]);

            // Delete customer's phone numbers
            $phoneSql = "DELETE FROM phonenumbers WHERE CustomerID = :customerId";
            $phoneStmt = $this->pdo->prepare($phoneSql);
            $phoneStmt->execute(['customerId' => $this->customerId]);

            // Delete customer's email addresses
            $emailSql = "DELETE FROM emails WHERE CustomerID = :customerId";
            $emailStmt = $this->pdo->prepare($emailSql);
            $emailStmt->execute(['customerId' => $this->customerId]);

            // Delete the customer
            $customerSql = "DELETE FROM customers WHERE CustomerID = :customerId";
            $customerStmt = $this->pdo->prepare($customerSql);
            $customerStmt->execute(['customerId' => $this->customerId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get paginated list of customers with their first contact information
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @return array Array of customers with their contact information
     */
    function getPaginatedCustomers($limit, $offset, $filter) {
        global $pdo;
    
        $sql = "SELECT 
                    c.CustomerID, 
                    c.FirstName, 
                    c.LastName, 
                    c.Company,
                    (SELECT Address FROM addresses WHERE CustomerID = c.CustomerID LIMIT 1) as Address,
                    (SELECT nr FROM phonenumbers WHERE CustomerID = c.CustomerID LIMIT 1) as Phone,
                    (SELECT Emails FROM emails WHERE CustomerID = c.CustomerID LIMIT 1) as Email
                FROM customers c ";
    
        // Add WHERE clause if filtering
        if (!empty($filter)) {
            $sql .= "WHERE 
                    LOWER(c.FirstName) LIKE :filter OR
                    LOWER(c.LastName) LIKE :filter OR
                    LOWER(c.Company) LIKE :filter OR
                    LOWER((SELECT Address FROM addresses WHERE CustomerID = c.CustomerID LIMIT 1)) LIKE :filter OR
                    LOWER((SELECT nr FROM phonenumbers WHERE CustomerID = c.CustomerID LIMIT 1)) LIKE :filter OR
                    LOWER((SELECT Emails FROM emails WHERE CustomerID = c.CustomerID LIMIT 1)) LIKE :filter ";
        }
    
        $sql .= "ORDER BY c.FirstName ASC 
                 LIMIT :limit OFFSET :offset";
    
        $stmt = $pdo->prepare($sql);
    
        // Bind parameters
        if (!empty($filter)) {
            $filter = '%' . strtolower($filter) . '%';
            $stmt->bindValue(':filter', $filter, PDO::PARAM_STR);
        }
    
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total number of customers
     * @return int Total number of customers
     */
    function getTotalCustomers($filter) {
        global $pdo;
    
        $sql = "SELECT COUNT(DISTINCT customers.CustomerID) 
                FROM customers 
                LEFT JOIN addresses ON customers.CustomerID = addresses.CustomerID 
                LEFT JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
                LEFT JOIN emails ON customers.CustomerID = emails.CustomerID";
    
        if (!empty($filter)) {
            $sql .= " WHERE 
                LOWER(customers.FirstName) LIKE :filter OR
                LOWER(customers.LastName) LIKE :filter OR
                LOWER(customers.Company) LIKE :filter OR
                LOWER(addresses.Address) LIKE :filter OR
                LOWER(phonenumbers.nr) LIKE :filter OR
                LOWER(emails.Emails) LIKE :filter";
        }
    
        $stmt = $pdo->prepare($sql);
    
        if (!empty($filter)) {
            $filter = '%' . strtolower($filter) . '%';
            $stmt->bindValue(':filter', $filter, PDO::PARAM_STR);
        }
    
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Get selected customers for printing
     * @param array $selectedIds Array of customer IDs
     * @return array Array of selected customers with their information
     */
    function getSelectedCustomers($selectedIds) {
        global $pdo;
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        
        $sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
                addresses.Address, phonenumbers.nr, emails.Emails 
                FROM customers 
                LEFT JOIN addresses ON customers.CustomerID = addresses.CustomerID 
                LEFT JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
                LEFT JOIN emails ON customers.CustomerID = emails.CustomerID
                WHERE customers.CustomerID IN ($placeholders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($selectedIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated and sorted customers for printing
     * @param int $page Current page number
     * @param int $customersPerPage Number of customers per page
     * @param string $sort Sort order
     * @return array Array of customers with their information
     */
    function getPrintCustomers($page, $customersPerPage, $sort) {
        global $pdo;
        $offset = ($page - 1) * $customersPerPage;

        // Determine sort order
        $orderBy = '';
        switch($sort) {
            case 'Name':
                $orderBy = 'CONCAT(customers.FirstName, " ", customers.LastName) ASC';
                break;
            case 'Email':
                $orderBy = 'COALESCE(emails.Emails, "") ASC';
                break;
            case 'Phone':
                $orderBy = 'COALESCE(phonenumbers.nr, "") ASC';
                break;
            case 'Address':
                $orderBy = 'COALESCE(addresses.Address, "") ASC';
                break;
            default:
                $orderBy = 'CONCAT(customers.FirstName, " ", customers.LastName) ASC';
        }

        $sql = "SELECT customers.CustomerID, customers.FirstName, customers.LastName, customers.Company, 
                COALESCE(addresses.Address, '') as Address, 
                COALESCE(phonenumbers.nr, '') as Phone, 
                COALESCE(emails.Emails, '') as Email
                FROM customers 
                LEFT JOIN addresses ON customers.CustomerID = addresses.CustomerID 
                LEFT JOIN phonenumbers ON customers.CustomerID = phonenumbers.CustomerID 
                LEFT JOIN emails ON customers.CustomerID = emails.CustomerID
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $customersPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
