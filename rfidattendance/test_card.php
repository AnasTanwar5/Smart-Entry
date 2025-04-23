<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - New request received\n", FILE_APPEND);
file_put_contents('debug_log.txt', "GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents('debug_log.txt', "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Test database connection
require 'connectDB.php';
if (!$conn) {
    file_put_contents('debug_log.txt', "Database connection failed: " . mysqli_connect_error() . "\n", FILE_APPEND);
    die("Database connection failed!");
}

// Get card UID and device token
$card_uid = isset($_GET['card_uid']) ? $_GET['card_uid'] : (isset($_POST['card_uid']) ? $_POST['card_uid'] : '');
$device_token = isset($_GET['device_token']) ? $_GET['device_token'] : (isset($_POST['device_token']) ? $_POST['device_token'] : '');

file_put_contents('debug_log.txt', "Card UID: $card_uid\n", FILE_APPEND);
file_put_contents('debug_log.txt', "Device Token: $device_token\n", FILE_APPEND);

if (empty($card_uid) || empty($device_token)) {
    file_put_contents('debug_log.txt', "Missing card_uid or device_token\n", FILE_APPEND);
    die("Missing card_uid or device_token");
}

// Check if device exists
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    file_put_contents('debug_log.txt', "SQL Error: Cannot prepare device query\n", FILE_APPEND);
    die("SQL Error");
}

mysqli_stmt_bind_param($stmt, "s", $device_token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    file_put_contents('debug_log.txt', "Device not found: $device_token\n", FILE_APPEND);
    die("Device not found!");
}

// Check if card exists
$sql = "SELECT * FROM users WHERE card_uid=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    file_put_contents('debug_log.txt', "SQL Error: Cannot prepare card query\n", FILE_APPEND);
    die("SQL Error");
}

mysqli_stmt_bind_param($stmt, "s", $card_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    file_put_contents('debug_log.txt', "Card not found: $card_uid\n", FILE_APPEND);
    die("Card not found!");
}

// Check if card is allowed on this device
if ($row['device_uid'] != $device_token) {
    file_put_contents('debug_log.txt', "Card not allowed on this device. Card device: " . $row['device_uid'] . ", Current device: $device_token\n", FILE_APPEND);
    die("Not Allowed!");
}

file_put_contents('debug_log.txt', "Card scan successful!\n", FILE_APPEND);
echo "OK";
?> 