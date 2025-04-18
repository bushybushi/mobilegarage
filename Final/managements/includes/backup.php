<?php
include 'db_connection.php';

// Full path to mysqldump (check this on your system with `which mysqldump`)
$mysqldump_path = '/bin/mysqldump'; // Adjust if needed

$backup_file = "../backup/db_backup_" . date("Y-m-d_H-i-s") . ".sql";
$log_file = "../backup/debug_log.txt";

// Construct command
$command = "$mysqldump_path -u$username --password='$password' $dbname > $backup_file";

// Log the command being run
file_put_contents($log_file, "COMMAND:\n$command\n\n", FILE_APPEND);

// Run command and capture output
$output = null;
$return_var = null;
exec($command . " 2>&1", $output, $return_var); // include stderr

// Log the output and return code
file_put_contents($log_file, "OUTPUT:\n" . implode("\n", $output) . "\n\n", FILE_APPEND);
file_put_contents($log_file, "RETURN CODE:\n$return_var\n\n", FILE_APPEND);

// Respond
if ($return_var === 0) {
    file_put_contents("../backup/last_backup.txt", $backup_file);
    echo "Backup successful.";
} else {
    echo "Backup failed. Check debug_log.txt for details.";
}
?>