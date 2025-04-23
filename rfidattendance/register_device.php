<?php
require 'connectDB.php';

$device_uid = "caa8b6bb5969bdc0"; // Your device UID
$device_name = "RFID Reader 1";
$device_dep = "Main Entrance";

// Check if device exists
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    die("SQL Error: Cannot prepare device query!");
}

mysqli_stmt_bind_param($stmt, "s", $device_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    // Device not found, register it
    $sql = "INSERT INTO devices (device_uid, device_name, device_dep, device_date, device_mode) 
            VALUES (?, ?, ?, CURDATE(), 1)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("SQL Error: Cannot prepare device insert!");
    }

    mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_name, $device_dep);
    mysqli_stmt_execute($stmt);
    
    echo "Device registered successfully!";
} else {
    echo "Device already registered!";
}

// Check if card exists
$card_uid = "13AE69F5"; // New card UID
$sql = "SELECT * FROM users WHERE card_uid=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    die("SQL Error: Cannot prepare card query!");
}

mysqli_stmt_bind_param($stmt, "s", $card_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    // Card not found, register it
    $sql = "INSERT INTO users (card_uid, device_uid, device_dep, user_date, add_card) 
            VALUES (?, ?, ?, CURDATE(), 1)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("SQL Error: Cannot prepare card insert!");
    }

    mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
    mysqli_stmt_execute($stmt);
    
    echo "<br>Card registered successfully!";
} else {
    echo "<br>Card already registered!";
    // Update card's device
    $sql = "UPDATE users SET device_uid=?, device_dep=? WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("SQL Error: Cannot prepare card update!");
    }

    mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_dep, $card_uid);
    mysqli_stmt_execute($stmt);
    
    echo "<br>Card's device updated!";
}
?> 