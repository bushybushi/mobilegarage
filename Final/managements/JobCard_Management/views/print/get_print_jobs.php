<?php
require_once '../../config/db_connection.php';

// Get page number from request
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$jobsPerPage = 10;

// Get total number of job cards
$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobcards")->fetchColumn();
$totalPages = ceil($totalJobs / $jobsPerPage);
$offset = ($page - 1) * $jobsPerPage;

// SQL query to fetch job cards with pagination
$sql = "SELECT j.JobID, j.Location, j.DateCall, j.JobDesc, j.DateStart, j.DateFinish,
        CONCAT(c.FirstName, ' ', c.LastName) as CustomerName, 
        car.LicenseNr, car.Brand, car.Model, 
        pn.Nr as PhoneNumber,
        a.Address
        FROM jobcards j 
        LEFT JOIN jobcar jc ON j.JobID = jc.JobID
        LEFT JOIN cars car ON jc.LicenseNr = car.LicenseNr
        LEFT JOIN carassoc ca ON car.LicenseNr = ca.LicenseNr
        LEFT JOIN customers c ON ca.CustomerID = c.CustomerID
        LEFT JOIN phonenumbers pn ON c.CustomerID = pn.CustomerID
        LEFT JOIN addresses a ON c.CustomerID = a.CustomerID
        ORDER BY j.DateCall DESC
        LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $jobsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the table rows
foreach ($result as $row): ?>
    <tr data-job-id="<?php echo htmlspecialchars($row['JobID']); ?>">
        <td><input type="checkbox" class="print-job-select"></td>
        <td><?php echo htmlspecialchars($row['CustomerName'] ?: 'N/A'); ?></td>
        <td>
            <?php 
            $carInfo = '';
            if (!empty($row['Brand']) || !empty($row['Model'])) {
                $carInfo = htmlspecialchars(trim($row['Brand'] . ' ' . $row['Model']));
            }
            if (!empty($row['LicenseNr'])) {
                $carInfo .= (!empty($carInfo) ? ', ' : '') . htmlspecialchars($row['LicenseNr']);
            }
            echo !empty($carInfo) ? $carInfo : 'N/A';
            ?>
        </td>
        <td><?php echo htmlspecialchars($row['PhoneNumber'] ?: 'N/A'); ?></td>
        <td>
            <?php 
            $startDate = !empty($row['DateStart']) ? date('d/m/Y', strtotime($row['DateStart'])) : 'N/A';
            $endDate = !empty($row['DateFinish']) ? date('d/m/Y', strtotime($row['DateFinish'])) : 'N/A';
            echo $startDate . ' - ' . $endDate;
            ?>
        </td>
        <td>
            <?php 
            if (!empty($row['DateFinish'])) {
                echo '<span class="status-closed">CLOSED</span>';
            } else {
                echo '<span class="status-open">OPEN</span>';
            }
            ?>
        </td>
    </tr>
<?php endforeach; ?> 