<?php
// Test database connection
require 'connectDB.php';
echo "Database connection: " . ($conn ? "OK" : "Failed") . "<br>";

// Test if Apache is running
echo "Apache status: " . (function_exists('apache_get_version') ? "Running" : "Not Running") . "<br>";

// Test if MySQL is running
$mysql_test = @mysqli_connect("localhost", "root", "");
echo "MySQL status: " . ($mysql_test ? "Running" : "Not Running") . "<br>";

// Test if the directory is writable
$test_file = "test_write.txt";
if (file_put_contents($test_file, "test")) {
    echo "Directory is writable<br>";
    unlink($test_file);
} else {
    echo "Directory is not writable<br>";
}

// Test if the device is registered
$device_uid = "caa8b6bb5969bdc0";
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $device_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    echo "Device registration: " . (mysqli_fetch_assoc($result) ? "Registered" : "Not Registered") . "<br>";
}

// Test if the card is registered
$card_uid = "13AE69F5";
$sql = "SELECT * FROM users WHERE card_uid=?";
$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    echo "Card registration: " . (mysqli_fetch_assoc($result) ? "Registered" : "Not Registered") . "<br>";
}

// Test network connectivity
$host = "192.168.27.114";
$port = 80;
$timeout = 5;
$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
echo "Server connectivity: " . ($fp ? "OK" : "Failed - $errstr ($errno)") . "<br>";
if ($fp) {
    fclose($fp);
}
?> 