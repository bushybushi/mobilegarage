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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['CustomerName']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Vehicle</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Brand'] . ' ' . $jobCard['Model'] . ' (' . $jobCard['LicenseNr'] . ')'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Date of Call</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['DateCall']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Job Report</label>
                            <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobReport']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Location']); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['PhoneNumber']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Job Description</label>
                            <textarea class="form-control" rows="3"><?php echo htmlspecialchars($jobCard['JobDesc']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateStart']) ? htmlspecialchars($jobCard['DateStart']) : 'Not started'; ?>">
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="text" class="form-control" value="<?php echo !empty($jobCard['DateFinish']) ? htmlspecialchars($jobCard['DateFinish']) : 'In progress'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Rides</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['Rides']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Drive Costs</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($jobCard['DriveCosts']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Parts Used</label>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Part Description</th>
                                <th>Quantity</th>
                                <th>Price Per Piece</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalPartsCost = 0;
                            foreach ($parts as $part): 
                                $partTotal = $part['PricePerPiece'] * $part['PiecesSold'];
                                $totalPartsCost += $partTotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($part['PartDesc']); ?></td>
                                    <td><?php echo htmlspecialchars($part['PiecesSold']); ?></td>
                                    <td>€<?php echo htmlspecialchars(number_format($part['PricePerPiece'], 2)); ?></td>
                                    <td>€<?php echo htmlspecialchars(number_format($partTotal, 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total Parts Cost:</strong></td>
                                <td>€<?php echo htmlspecialchars(number_format($totalPartsCost, 2)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Drive Costs:</strong></td>
                                <td>€<?php echo htmlspecialchars(number_format($jobCard['DriveCosts'], 2)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total Cost:</strong></td>
                                <td>€<?php echo htmlspecialchars(number_format($totalPartsCost + $jobCard['DriveCosts'], 2)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($jobCard['Photo'])): ?>
                <div class="form-group">
                    <label>Photos</label>
                    <div class="row">
                        <?php 
                        $photos = json_decode($jobCard['Photo'], true);
                        foreach ($photos as $photo): ?>
                            <div class="col-md-3 mb-3">
                                <img src="../uploads/job_photos/<?php echo htmlspecialchars($photo); ?>" class="img-fluid rounded" alt="Damage photo">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
</body>
</html> 