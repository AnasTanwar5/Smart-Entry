<?php
require 'connectDB.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check if we have a backup
$backup_file = 'database_backup.sql';
if (file_exists($backup_file)) {
    echo "Found backup file! Attempting to restore...<br>";
    
    // Read the backup file
    $sql = file_get_contents($backup_file);
    
    // Execute the backup SQL
    if (mysqli_multi_query($conn, $sql)) {
        echo "Backup restored successfully!<br>";
    } else {
        echo "Error restoring backup: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "No backup file found. Let's try to recover from the current database...<br>";
}

// Check if we have any data in the current database
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "Found tables in database. Checking for data...<br>";
    
    // Check users table
    $sql = "SELECT * FROM users";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        echo "Found user data:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "Card UID: " . $row['card_uid'] . "<br>";
            echo "Username: " . $row['username'] . "<br>";
            echo "Serial Number: " . $row['serialnumber'] . "<br>";
            echo "-------------------<br>";
        }
    } else {
        echo "No user data found.<br>";
    }
    
    // Check devices table
    $sql = "SELECT * FROM devices";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        echo "<br>Found device data:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "Device UID: " . $row['device_uid'] . "<br>";
            echo "Device Name: " . $row['device_name'] . "<br>";
            echo "Device Department: " . $row['device_dep'] . "<br>";
            echo "-------------------<br>";
        }
    } else {
        echo "No device data found.<br>";
    }
    
    // Check users_logs table
    $sql = "SELECT * FROM users_logs ORDER BY checkindate DESC, timein DESC LIMIT 10";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        echo "<br>Recent attendance logs:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "Username: " . $row['username'] . "<br>";
            echo "Date: " . $row['checkindate'] . "<br>";
            echo "Time In: " . $row['timein'] . "<br>";
            echo "Time Out: " . $row['timeout'] . "<br>";
            echo "-------------------<br>";
        }
    } else {
        echo "No attendance logs found.<br>";
    }
} else {
    echo "No tables found in database.<br>";
}

// Create a backup of current data
$backup_file = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
$tables = array('users', 'devices', 'users_logs', 'admin');
$backup = '';

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW CREATE TABLE $table");
    $row = mysqli_fetch_row($result);
    $backup .= "\n\n" . $row[1] . ";\n\n";
    
    $result = mysqli_query($conn, "SELECT * FROM $table");
    while ($row = mysqli_fetch_row($result)) {
        $backup .= "INSERT INTO $table VALUES(";
        for ($i = 0; $i < count($row); $i++) {
            $row[$i] = addslashes($row[$i]);
            $row[$i] = str_replace("\n", "\\n", $row[$i]);
            if (isset($row[$i])) {
                $backup .= '"' . $row[$i] . '"';
            } else {
                $backup .= '""';
            }
            if ($i < (count($row) - 1)) {
                $backup .= ',';
            }
        }
        $backup .= ");\n";
    }
}

file_put_contents($backup_file, $backup);
echo "<br>Created backup file: $backup_file<br>";

// Restore your device and card
$device_uid = "caa8b6bb5969bdc0";
$device_name = "RFID Reader 1";
$device_dep = "Main Entrance";

$sql = "INSERT INTO devices (device_uid, device_name, device_dep, device_date, device_mode) 
        VALUES (?, ?, ?, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE 
        device_name=VALUES(device_name),
        device_dep=VALUES(device_dep),
        device_date=CURDATE()";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_name, $device_dep);
mysqli_stmt_execute($stmt);

$card_uid = "13AE69F5";
$sql = "INSERT INTO users (card_uid, device_uid, device_dep, user_date, add_card) 
        VALUES (?, ?, ?, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE 
        device_uid=VALUES(device_uid),
        device_dep=VALUES(device_dep),
        user_date=CURDATE()";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
mysqli_stmt_execute($stmt);

echo "<br>Device and card registration restored!<br>";
echo "Try scanning your card now.";
?> 