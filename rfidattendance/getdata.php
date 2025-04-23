<?php  
// Connect to database
require 'connectDB.php';
date_default_timezone_set('Asia/Kolkata');

$d = date("Y-m-d");
$t = date("H:i:sa");

// Enable debugging log
$log_file = "debug_log.txt";
file_put_contents($log_file, "===== New Request at $d $t =====\n", FILE_APPEND);

// Check if card_uid and device_token are received (support both GET and POST)
$card_uid = isset($_POST['card_uid']) ? $_POST['card_uid'] : (isset($_GET['card_uid']) ? $_GET['card_uid'] : null);
$device_uid = isset($_POST['device_token']) ? $_POST['device_token'] : (isset($_GET['device_token']) ? $_GET['device_token'] : null);

if (!$card_uid || !$device_uid) {
    $error_msg = "Missing parameters! POST data: " . print_r($_POST, true) . ", GET data: " . print_r($_GET, true);
    file_put_contents($log_file, "❌ Error: " . $error_msg . "\n", FILE_APPEND);
    echo $error_msg;
    exit();
}

// Convert hex card_uid to decimal for database storage
if (preg_match('/^[0-9A-Fa-f]+$/', $card_uid)) {
    $card_uid = hexdec($card_uid);
} else {
    // If it's already decimal, just ensure it's a number
    $card_uid = intval($card_uid);
}

file_put_contents($log_file, "Received Data: card_uid=$card_uid, device_token=$device_uid\n", FILE_APPEND);

// Check if the device exists
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    file_put_contents($log_file, "❌ SQL Error: Cannot prepare device query!\n", FILE_APPEND);
    echo "SQL_Error_Select_device";
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $device_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    file_put_contents($log_file, "❌ Error: Device not registered!\n", FILE_APPEND);
    echo "Error: Device not registered!";
    exit();
}

$device_mode = $row['device_mode'];
$device_dep = $row['device_dep'];

// ** Device Mode 1: Login/Logout **
if ($device_mode == 1) {
    $sql = "SELECT * FROM users WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        file_put_contents($log_file, "❌ SQL Error: Cannot prepare user query!\n", FILE_APPEND);
        echo "SQL_Error_Select_card";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$user = mysqli_fetch_assoc($result)) {
        file_put_contents($log_file, "❌ Error: No user found for card UID: $card_uid\n", FILE_APPEND);
        echo "Error: No user found!";
        exit();
    }

    if ($user['add_card'] == 0) {
        file_put_contents($log_file, "⚠️ Card detected but not registered!\n", FILE_APPEND);
        echo "Not registered!";
        exit();
    }

    if ($user['device_uid'] != $device_uid && $user['device_uid'] != 0) {
        file_put_contents($log_file, "❌ Error: Card not allowed on this device!\n", FILE_APPEND);
        echo "Not Allowed!";
        exit();
    }

    $Uname = $user['username'];
    $Number = $user['serialnumber'];

    // Check if user is already logged in
    $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND card_out=0";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        file_put_contents($log_file, "❌ SQL Error: Cannot prepare login check query!\n", FILE_APPEND);
        echo "SQL_Error_Select_logs";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $card_uid, $d);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$row = mysqli_fetch_assoc($result)) {
        // **Login**
        $sql = "INSERT INTO users_logs (username, serialnumber, card_uid, device_uid, device_dep, checkindate, timein, timeout) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            file_put_contents($log_file, "❌ SQL Error: Cannot insert login log!\n", FILE_APPEND);
            echo "SQL_Error_Insert_Login";
            exit();
        }

        $timeout = "00:00:00";
        mysqli_stmt_bind_param($stmt, "sdssssss", $Uname, $Number, $card_uid, $device_uid, $device_dep, $d, $t, $timeout);
        mysqli_stmt_execute($stmt);

        file_put_contents($log_file, "✅ User $Uname logged in!\n", FILE_APPEND);
        echo "login " . $Uname;
        exit();
    } else {
        // **Logout**
        $sql = "UPDATE users_logs SET timeout=?, card_out=1 WHERE card_uid=? AND checkindate=? AND card_out=0";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            file_put_contents($log_file, "❌ SQL Error: Cannot insert logout log!\n", FILE_APPEND);
            echo "SQL_Error_Insert_Logout";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "sss", $t, $card_uid, $d);
        mysqli_stmt_execute($stmt);

        file_put_contents($log_file, "✅ User $Uname logged out!\n", FILE_APPEND);
        echo "logout " . $Uname;
        exit();
    }
}

// ** Device Mode 0: Register New Card **
if ($device_mode == 0) {
    $sql = "SELECT * FROM users WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        file_put_contents($log_file, "❌ SQL Error: Cannot prepare new card query!\n", FILE_APPEND);
        echo "SQL_Error_Select_card";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Card already exists - Update selection
        $sql = "UPDATE users SET card_select=0 WHERE card_select=1"; // Clear other selections
        mysqli_query($conn, $sql);

        $sql = "UPDATE users SET card_select=1, device_uid=?, device_dep=? WHERE card_uid=?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            file_put_contents($log_file, "❌ SQL Error: Cannot select available card!\n", FILE_APPEND);
            echo "SQL_Error_insert_An_available_card";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "sss", $device_uid, $device_dep, $card_uid);
        mysqli_stmt_execute($stmt);

        file_put_contents($log_file, "✅ Card selected and updated successfully!\n", FILE_APPEND);
        echo "available";
        exit();
    } else {
        // Register new card
        $sql = "UPDATE users SET card_select=0 WHERE card_select=1"; // Clear other selections
        mysqli_query($conn, $sql);

        $sql = "INSERT INTO users (card_uid, card_select, device_uid, device_dep, user_date) 
                VALUES (?, 1, ?, ?, CURDATE())";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            file_put_contents($log_file, "❌ SQL Error: Cannot insert new card!\n", FILE_APPEND);
            echo "SQL_Error_Select_add";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
        mysqli_stmt_execute($stmt);

        file_put_contents($log_file, "✅ New card registered and selected successfully!\n", FILE_APPEND);
        echo "successful";
        exit();
    }
}

file_put_contents($log_file, "❌ Error: Invalid device mode!\n", FILE_APPEND);
echo "Invalid Device Mode!";
exit();
?>
