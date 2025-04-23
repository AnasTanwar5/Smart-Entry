<?php
require 'connectDB.php';

// Test database connection
echo "Testing database connection...<br>";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connection successful!<br><br>";

// Check users table structure
echo "Checking users table structure...<br>";
$sql = "DESCRIBE users";
$result = $conn->query($sql);
if ($result) {
    echo "Users table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error describing table: " . $conn->error . "<br>";
}

// Test card selection
echo "<br>Testing card selection...<br>";
$test_card = "TEST123";
$sql = "INSERT INTO users (card_uid, card_select) VALUES (?, 0) ON DUPLICATE KEY UPDATE card_select=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_card);
$stmt->execute();

// Select the card
$sql = "UPDATE users SET card_select=1 WHERE card_uid=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_card);
if ($stmt->execute()) {
    echo "Card selection successful!<br>";
} else {
    echo "Card selection failed: " . $stmt->error . "<br>";
}

// Check if card is selected
$sql = "SELECT card_select FROM users WHERE card_uid=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_card);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo "Card selection status: " . $row['card_select'] . "<br>";
} else {
    echo "Could not verify card selection<br>";
}

$conn->close();
?> 