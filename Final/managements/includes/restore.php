<?php
include 'db_connection.php';

// Full path to mysql binary (check with `which mysql`)
$mysql_path = '/usr/bin/mysql'; // Adjust if needed
$log_file = "../backup/debug_log.txt";

// Path to the last backup file
$last_backup_file_path = "../backup/last_backup.txt";

if (file_exists($last_backup_file_path)) {
    $backup_file = trim(file_get_contents($last_backup_file_path));

    if (file_exists($backup_file)) {
        // Escape path and credentials
        $escaped_file = escapeshellarg($backup_file);
        $escaped_password = escapeshellarg($password);

        // Construct restore command
        $command = "$mysql_path -u$username --password=$escaped_password $dbname < $escaped_file";

        // Log the command
        file_put_contents($log_file, "RESTORE COMMAND:\n$command\n\n", FILE_APPEND);

        // Execute restore command
        $output = null;
        $retval = null;
        exec($command . " 2>&1", $output, $retval);

        // Log output
        file_put_contents($log_file, "RESTORE OUTPUT:\n" . implode("\n", $output) . "\n\n", FILE_APPEND);
        file_put_contents($log_file, "RESTORE RETURN CODE:\n$retval\n\n", FILE_APPEND);

        // Report
        if ($retval === 0) {
            echo "Restore successful.";
        } else {
            echo "Restore failed. Check debug_log.txt for details.";
        }
    } else {
        echo "Backup file not found: $backup_file";
    }
} else {
    echo "No backup record found in last_backup.txt.";
}
?>