<?php
require_once '../config/db_connection.php';
$pdo = require '../config/db_connection.php';

$jobId = $_GET['id'];

$sql = "SELECT j.*, CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
        car.LicenseNr, car.Brand, car.Model, pn.Nr as PhoneNumber
        FROM JobCards j 
        LEFT JOIN JobCar jc ON j.JobID = jc.JobID
        LEFT JOIN Cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN CarAssoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN Customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN PhoneNumbers pn ON c.CustomerID = pn.CustomerID
        WHERE j.JobID = :jobId";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
$stmt->execute();

$jobCard = $stmt->fetch(PDO::FETCH_ASSOC);

// Get parts used in this job
$partsSql = "SELECT p.PartDesc, jp.PricePerPiece, jp.PiecesSold
             FROM JobCardParts jp
             JOIN Parts p ON jp.PartID = p.PartID
             WHERE jp.JobID = :jobId
             ORDER BY jp.PiecesSold DESC";

$partsStmt = $pdo->prepare($partsSql);
$partsStmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
$partsStmt->execute();
$parts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totalPartsCost
$totalPartsCost = 0;
foreach ($parts as $part) {
    $totalPartsCost += $part['PricePerPiece'] * $part['PiecesSold'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card View</title>
    
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="pc-container3">
        <div class="form-container">
            <div class="top-container d-flex justify-content-between align-items-center">
                <a href="javascript:void(0);" onclick="window.location.href='job_cards_main.php'" class="back-arrow">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Job Card #<?php echo htmlspecialchars($jobId); ?></h5>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="btngroup">
                        <button href="#" type="button" class="btn btn-success mr-2">Print</button>
                        <button href="#" type="button" class="btn btn-primary">Create Invoice</button>
                    </div>
                </div>
            </div>

            <div class="form-content">
                <fieldset disabled>
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Customer -->
                            <div class="form-group">
                                <label>Customer</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['CustomerName']); ?>">
                            </div>

                            <!-- Car Brand and Model -->
                            <div class="form-group">
                                <label>Car Brand and Model</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model']); ?>">
                            </div>

                            <!-- Registration Plate -->
                            <div class="form-group">
                                <label>Registration Plate</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['LicenseNr']); ?>">
                            </div>

                            <!-- Date of Call -->
                            <div class="form-group">
                                <label>Date of Call</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['DateCall']); ?>">
                            </div>

                            <!-- Job Report -->
                            <div class="form-group">
                                <label>Job Report</label>
                                <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobReport']); ?></textarea>
                            </div>

                            <!-- Job End Date -->
                            <div class="form-group">
                                <label>Job End Date</label>
                                <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateFinish']) ? htmlspecialchars($jobCard['DateFinish']) : 'Not finished'; ?>">
                            </div>

                            <!-- Parts Used -->
                            <div class="form-group">
                                <label>Parts Used/Replaced</label>
                                <div id="partsContainer">
                                    <?php foreach ($parts as $part): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($part['PartDesc']); ?>" readonly>
                                        <input type="text" class="form-control" style="max-width: 80px;" value="<?php echo htmlspecialchars($part['PiecesSold']); ?>" readonly>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($parts)): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" value="No parts used" readonly>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Costs Row -->
                            <div class="row mt-3">
                                <!-- Additional Costs -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Additional Costs</label>
                                        <div class="form-control">
                                            €<?php echo htmlspecialchars(number_format($jobCard['AdditionalCosts'] ?? 0, 2)); ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Total Costs -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Total Costs</label>
                                        <div class="form-control">
                                            €<?php 
                                                $additionalCosts = $jobCard['AdditionalCosts'] ?? 0;
                                                $totalCost = $totalPartsCost + $jobCard['DriveCosts'] + $additionalCosts;
                                                echo htmlspecialchars(number_format($totalCost, 2)); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['PhoneNumber']); ?>">
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label>Location of Visit</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Location']); ?>">
                            </div>

                            <!-- Job Description -->
                            <div class="form-group">
                                <label>Job Description by Customer</label>
                                <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobDesc']); ?></textarea>
                            </div>

                            <!-- Job Start Date -->
                            <div class="form-group">
                                <label>Job Start Date</label>
                                <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateStart']) ? htmlspecialchars($jobCard['DateStart']) : 'Not started'; ?>">
                            </div>

                            <!-- Rides -->
                            <div class="form-group">
                                <label>Rides</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Rides']); ?>">
                            </div>

                            <!-- Drive Costs -->
                            <div class="form-group">
                                <label>Drive Costs</label>
                                <input type="text" class="form-control" value="€<?php echo htmlspecialchars(number_format($jobCard['DriveCosts'], 2)); ?>">
                            </div>

                            <!-- Price for Each Part -->
                            <div class="form-group">
                                <label>Price for Each Part</label>
                                <div id="partPricesContainer">
                                    <?php foreach ($parts as $part): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" value="€<?php echo htmlspecialchars(number_format($part['PricePerPiece'], 2)); ?>" readonly>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($parts)): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" value="No prices" readonly>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Photos -->
                            <?php if (!empty($jobCard['Photo'])): ?>
                            <div class="form-group">
                                <label>Photos</label>
                                <div class="row">
                                    <?php 
                                    $photos = json_decode($jobCard['Photo'], true);
                                    foreach ($photos as $photo): ?>
                                        <div class="col-md-3 mb-3">
                                            <a href="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                               data-toggle="modal" data-target="#photoModal" 
                                               class="photo-link">
                                                <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" 
                                                     class="img-fluid rounded" alt="Job photo"
                                                     style="max-height: 150px;">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <div class="btngroup">
                    <a href="edit_job_card.php?id=<?php echo $jobId; ?>" class="btn btn-primary">Edit</a>
                    <form action="../controllers/delete_job_card_controller.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $jobId; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this job card?');">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Photo View</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Enlarged photo">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set modal image source when a photo is clicked
        document.querySelectorAll('.photo-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('modalImage').src = this.href;
            });
        });
    </script>
</body>
</html> 
