<?php
// Include database connection file
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

// Get car License Number from URL parameter
$licenseNr = $_GET['id'];

// SQL query to fetch car details
$sql = "SELECT * FROM Cars WHERE LicenseNr = :licenseNr";

// Prepare and execute the query with parameter binding
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':licenseNr', $licenseNr, PDO::PARAM_STR);
$stmt->execute();

// Fetch the car data
$car = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for proper character encoding and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car View</title>
    
    <!-- CSS dependencies -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <!-- Main Content Container -->
    <div class="form-container">
        <!-- Top Navigation Bar with Car Info and Action Buttons -->
        <div class="top-container d-flex justify-content-between align-items-center">
            <!-- Back Arrow Button -->
            <a href="javascript:void(0);" onclick="window.location.href='car_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <!-- Car Info Display -->
            <div class="flex-grow-1 text-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($car['Brand']) . ' ' . htmlspecialchars($car['Model']); ?></h5>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end">
                <div class="btngroup">
                    <!-- Print Button -->
                    <button href="#" type="button" class="btn btn-success mr-2">Print</button>
                    <!-- Job Cards Button -->
                    <button href="#" type="button" class="btn btn-primary">Job Cards</button>
                </div>
            </div>
        </div>

        <!-- Car View Form -->
        <div class="form-content">
            <!-- Disable form fields for view-only mode -->
            <fieldset disabled>
                <!-- Brand Field -->
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand" class="form-control" value="<?php echo htmlspecialchars($car['Brand']); ?>">
                </div>

                <!-- Model Field -->
                <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" class="form-control" value="<?php echo htmlspecialchars($car['Model']); ?>">
                </div>

                <!-- License Number Field -->
                <div class="form-group">
                    <label for="licenseNr">License Plate</label>
                    <input type="text" id="licenseNr" name="licenseNr" class="form-control" value="<?php echo htmlspecialchars($car['LicenseNr']); ?>">
                </div>

                <!-- VIN Field -->
                <div class="form-group">
                    <label for="vin">Vehicle Identification Number (VIN)</label>
                    <input type="text" id="vin" name="vin" class="form-control" value="<?php echo htmlspecialchars($car['VIN']); ?>">
                </div>

                <!-- Manufacturing Date Field -->
                <div class="form-group">
                    <label for="manuDate">Manufacturing Date</label>
                    <input type="date" id="manuDate" name="manuDate" class="form-control" value="<?php echo htmlspecialchars($car['ManuDate']); ?>">
                </div>

                <!-- Fuel Type Field -->
                <div class="form-group">
                    <label for="fuel">Fuel Type</label>
                    <input type="text" id="fuel" name="fuel" class="form-control" value="<?php echo htmlspecialchars($car['Fuel']); ?>">
                </div>

                <!-- KW/Horse Power Field -->
                <div class="form-group">
                    <label for="kwHorse">KW/Horse Power</label>
                    <input type="number" step="0.1" id="kwHorse" name="kwHorse" class="form-control" value="<?php echo htmlspecialchars($car['KWHorse']); ?>">
                </div>

                <!-- Engine Field -->
                <div class="form-group">
                    <label for="engine">Engine</label>
                    <input type="text" id="engine" name="engine" class="form-control" value="<?php echo htmlspecialchars($car['Engine']); ?>">
                </div>

                <!-- KM/Miles Field -->
                <div class="form-group">
                    <label for="kmMiles">KM/Miles</label>
                    <input type="number" step="0.1" id="kmMiles" name="kmMiles" class="form-control" value="<?php echo htmlspecialchars($car['KMMiles']); ?>">
                </div>

                <!-- Color Field -->
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" class="form-control" value="<?php echo htmlspecialchars($car['Color']); ?>">
                </div>

                <!-- Comments Field -->
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea id="comments" name="comments" class="form-control" rows="3"><?php echo htmlspecialchars($car['Comments']); ?></textarea>
                </div>
            </fieldset>

            <!-- Action Buttons -->
            <div class="btngroup">
                <!-- Edit Button - Links to edit_car.php -->
                <a href="edit_car.php?id=<?php echo urlencode($licenseNr); ?>" class="btn btn-primary">Edit</a>
                <!-- Delete Button - Form with POST method -->
                <form action="../controllers/delete_car_controller.php" method="POST" style="display: inline;">
                    <input type="hidden" name="licenseNr" value="<?php echo htmlspecialchars($car['LicenseNr']); ?>">
                    <input type="hidden" name="brand" value="<?php echo htmlspecialchars($car['Brand']); ?>">
                    <input type="hidden" name="model" value="<?php echo htmlspecialchars($car['Model']); ?>">
                    <input type="hidden" name="vin" value="<?php echo htmlspecialchars($car['VIN']); ?>">
                    <input type="hidden" name="manuDate" value="<?php echo htmlspecialchars($car['ManuDate']); ?>">
                    <input type="hidden" name="fuel" value="<?php echo htmlspecialchars($car['Fuel']); ?>">
                    <input type="hidden" name="kwHorse" value="<?php echo htmlspecialchars($car['KWHorse']); ?>">
                    <input type="hidden" name="engine" value="<?php echo htmlspecialchars($car['Engine']); ?>">
                    <input type="hidden" name="kmMiles" value="<?php echo htmlspecialchars($car['KMMiles']); ?>">
                    <input type="hidden" name="color" value="<?php echo htmlspecialchars($car['Color']); ?>">
                    <input type="hidden" name="comments" value="<?php echo htmlspecialchars($car['Comments']); ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this car?');">Delete</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>