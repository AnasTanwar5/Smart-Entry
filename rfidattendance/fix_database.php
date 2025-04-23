<?php
require 'connectDB.php';

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS rfidattendance";
if (mysqli_query($conn, $sql)) {
    echo "Database checked/created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "rfidattendance");

// Create devices table if not exists
$sql = "CREATE TABLE IF NOT EXISTS devices (
    id INT(11) NOT NULL AUTO_INCREMENT,
    device_uid VARCHAR(50) NOT NULL,
    device_name VARCHAR(50) NOT NULL,
    device_dep VARCHAR(50) NOT NULL,
    device_date DATE NOT NULL,
    device_mode INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY device_uid (device_uid)
)";
if (mysqli_query($conn, $sql)) {
    echo "Devices table checked/created successfully<br>";
} else {
    echo "Error creating devices table: " . mysqli_error($conn) . "<br>";
}

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    serialnumber VARCHAR(50) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    email VARCHAR(50) NOT NULL,
    card_uid VARCHAR(50) NOT NULL,
    device_uid VARCHAR(50) NOT NULL,
    device_dep VARCHAR(50) NOT NULL,
    user_date DATE NOT NULL,
    add_card INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY card_uid (card_uid)
)";
if (mysqli_query($conn, $sql)) {
    echo "Users table checked/created successfully<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Register device if not exists
$device_uid = "caa8b6bb5969bdc0";
$device_name = "RFID Reader 1";
$device_dep = "Main Entrance";

$sql = "INSERT IGNORE INTO devices (device_uid, device_name, device_dep, device_date, device_mode) 
        VALUES (?, ?, ?, CURDATE(), 1)";
$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_name, $device_dep);
    mysqli_stmt_execute($stmt);
    echo "Device registration checked<br>";
}

// Register card if not exists
$card_uid = "13AE69F5";
$sql = "INSERT IGNORE INTO users (card_uid, device_uid, device_dep, user_date, add_card) 
        VALUES (?, ?, ?, CURDATE(), 1)";
$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
    mysqli_stmt_execute($stmt);
    echo "Card registration checked<br>";
}

// Update card's device if exists
$sql = "UPDATE users SET device_uid=?, device_dep=? WHERE card_uid=?";
$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_dep, $card_uid);
    mysqli_stmt_execute($stmt);
    echo "Card device update checked<br>";
}

echo "Database setup complete!<br>";
?> 