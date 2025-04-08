<?php
require_once '../includes/sanitize_inputs.php';

$pdo = require '../config/db_connection.php';

/**
 * ExtraExpense class to represent an extra expense entity
 */
class extraExpense {
    // Extra expense properties
    public $id;
    public $description;
    public $dateCreated;
    public $expense;

    /**
     * Constructor to initialize extra expense properties
     * @param int|null $id Expense ID
     * @param string|null $description Description of the expense
     * @param string|null $dateCreated Date the expense was created
     * @param float|null $expense Amount of the expense
     */
    function __construct($id = null, $description = null, $dateCreated = null, $expense = null) {
        $this->editID($id);
        $this->editDescription($description);
        $this->editDateCreated($dateCreated);
        $this->editExpense($expense);
    }

    // Getter methods
    function getID() { return $this->id; }
    function getDescription() { return $this->description; }
    function getDateCreated() { return $this->dateCreated; }
    function getExpense() { return $this->expense; }

    // Setter methods
    function editID($id) { $this->id = $id; }
    function editDescription($description) { $this->description = $description; }
    function editDateCreated($dateCreated) { $this->dateCreated = $dateCreated; }
    function editExpense($expense) { $this->expense = $expense; }
}

/**
 * ExtraExpenseManagement class to handle extra expense-related operations
 */
class extraExpenseManagement {
    public $sInput;
    public $extraExpense;

    /**
     * Constructor to initialize extraExpenseManagement and handle form submission
     */
    function __construct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $this->sInput = sanitizeInputs($_POST);

            // Validate required fields
            if (empty($this->sInput['description'])) {
                die("Error: Description is required. Please provide a valid description.");
            }

            if (empty($this->sInput['expense'])) {
                die("Error: Expense amount is required. Please provide a valid amount.");
            }

            // Initialize extra expense object with sanitized inputs
            $this->extraExpense = new extraExpense(
                $this->sInput['id'] ?? null,
                $this->sInput['description'],
                $this->sInput['dateCreated'] ?? date('Y-m-d'),
                $this->sInput['expense']
            );
        } else {
            die("Error: Invalid request method.");
        }
    }

    /**
     * Function to add a new extra expense to the database
     * @return bool True if successful, false otherwise
     */
    function Add() {
        global $pdo;

        // Validate extra expense object
        if (!isset($this->extraExpense)) {
            die("Error: Extra expense object is not properly initialized.");
        }

        try {
            // Start database transaction
            $pdo->beginTransaction();

            // Insert extra expense information
            $expenseSql = "INSERT INTO extraexpenses (Description, DateCreated, Expense) VALUES (?, ?, ?)";
            $expenseStmt = $pdo->prepare($expenseSql);
            $expenseStmt->execute([
                $this->extraExpense->getDescription(),
                $this->extraExpense->getDateCreated(),
                $this->extraExpense->getExpense()
            ]);

            // Get the new expense ID
            $this->extraExpense->editID($pdo->lastInsertId());

            if (!$this->extraExpense->getID()) {
                throw new Exception("Error: Failed to retrieve ExpenseID after insertion.");
            }

            // Commit transaction
            $pdo->commit();
            
            // Set success message in session
            $_SESSION['message'] = "Extra expense added successfully.";
            $_SESSION['message_type'] = "success";
            
            // Return true to indicate success
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
            return false;
        }
    }
}
?> 