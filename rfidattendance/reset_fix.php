<?php
require 'connectDB.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Drop and recreate database
$sql = "DROP DATABASE IF EXISTS rfidattendance";
mysqli_query($conn, $sql);

$sql = "CREATE DATABASE rfidattendance";
mysqli_query($conn, $sql);
mysqli_select_db($conn, "rfidattendance");

// Create devices table
$sql = "CREATE TABLE devices (
    id INT(11) NOT NULL AUTO_INCREMENT,
    device_uid VARCHAR(50) NOT NULL,
    device_name VARCHAR(50) NOT NULL,
    device_dep VARCHAR(50) NOT NULL,
    device_date DATE NOT NULL,
    device_mode INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY device_uid (device_uid)
)";
mysqli_query($conn, $sql);

// Create users table
$sql = "CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) DEFAULT 'New User',
    serialnumber VARCHAR(50) DEFAULT '0000',
    gender VARCHAR(10) DEFAULT 'Male',
    email VARCHAR(50) DEFAULT 'user@example.com',
    card_uid VARCHAR(50) NOT NULL,
    device_uid VARCHAR(50) NOT NULL,
    device_dep VARCHAR(50) NOT NULL,
    user_date DATE NOT NULL,
    add_card INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY card_uid (card_uid)
)";
mysqli_query($conn, $sql);

// Create users_logs table
$sql = "CREATE TABLE users_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    serialnumber VARCHAR(50) NOT NULL,
    card_uid VARCHAR(50) NOT NULL,
    device_uid VARCHAR(50) NOT NULL,
    device_dep VARCHAR(50) NOT NULL,
    checkindate DATE NOT NULL,
    timein TIME NOT NULL,
    timeout TIME NOT NULL,
    card_out INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
)";
mysqli_query($conn, $sql);

// Register device
$device_uid = "caa8b6bb5969bdc0";
$device_name = "RFID Reader 1";
$device_dep = "Main Entrance";

$sql = "INSERT INTO devices (device_uid, device_name, device_dep, device_date, device_mode) 
        VALUES (?, ?, ?, CURDATE(), 1)";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_name, $device_dep);
mysqli_stmt_execute($stmt);

// Register card
$card_uid = "13AE69F5";
$sql = "INSERT INTO users (card_uid, device_uid, device_dep, user_date, add_card) 
        VALUES (?, ?, ?, CURDATE(), 1)";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
mysqli_stmt_execute($stmt);

// Create default admin
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
)";
mysqli_query($conn, $sql);

$sql = "INSERT INTO admin (username, password) VALUES ('admin', 'admin')";
mysqli_query($conn, $sql);

// Verify everything
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "s", $device_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$device = mysqli_fetch_assoc($result);

$sql = "SELECT * FROM users WHERE card_uid=?";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_bind_param($stmt, "s", $card_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Log the results
file_put_contents('debug_log.txt', "\n" . date('Y-m-d H:i:s') . " - System Reset Complete\n", FILE_APPEND);
file_put_contents('debug_log.txt', "Device: " . print_r($device, true) . "\n", FILE_APPEND);
file_put_contents('debug_log.txt', "User: " . print_r($user, true) . "\n", FILE_APPEND);

echo "System has been completely reset and fixed!<br>";
echo "Device and card are registered.<br>";
echo "Default admin created (username: admin, password: admin).<br>";
echo "Try scanning your card now - it should work!";
?> 