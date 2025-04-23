//manage user

<?php  
//Connect to database
require'connectDB.php';

//Get user data
if (isset($_POST['get_user'])) {
    $card_uid = $_POST['card_uid'];
    
    // Convert hex card_uid to decimal for database query if it's in hex format
    if (preg_match('/^[0-9A-Fa-f]+$/', $card_uid)) {
        $card_uid = hexdec($card_uid);
    }
    
    $sql = "SELECT * FROM users WHERE card_uid=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error";
        exit();
    }
    else {
        mysqli_stmt_bind_param($result, "s", $card_uid);
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)) {
            echo json_encode($row);
        } else {
            echo "User not found";
        }
        exit();
    }
}

//Select card
if (isset($_POST['select_card']) && isset($_POST['card_uid'])) {
    $card_uid = $_POST['card_uid'];
    
    // First clear all card selections
    $sql = "UPDATE users SET card_select=0";
    mysqli_query($conn, $sql);
    
    // Then select the new card
    $sql = "UPDATE users SET card_select=1 WHERE card_uid=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error";
        exit();
    }
    else {
        mysqli_stmt_bind_param($result, "s", $card_uid);
        mysqli_stmt_execute($result);
        echo 1;
        exit();
    }
}

//Add user
if (isset($_POST['Add'])) {
    $user_id = $_POST['user_id'];
    $Uname = $_POST['name'];
    $Number = $_POST['number'];
    $Email = $_POST['email'];
    $dev_uid = $_POST['dev_uid'];
    $Gender = $_POST['gender'];
    
    // Check if there is any selected card
    $sql = "SELECT * FROM users WHERE card_select=1";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error";
        exit();
    }
    else {
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)) {
            $selected_card_uid = $row['card_uid'];

            if (!empty($Uname) && !empty($Number) && !empty($Email)) {
                // Check if the serial number already exists for another user
                $sql = "SELECT serialnumber FROM users WHERE serialnumber=? AND card_uid != ?";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error";
                    exit();
                }
                else {
                    mysqli_stmt_bind_param($result, "ds", $Number, $selected_card_uid);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    
                    if (!mysqli_fetch_assoc($resultl)) {
                        // Update user information
                        $sql = "UPDATE users SET username=?, serialnumber=?, email=?, gender=?, device_uid=?, add_card=1 WHERE card_uid=?";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error";
                            exit();
                        }
                        else {
                            mysqli_stmt_bind_param($result, "sdssss", $Uname, $Number, $Email, $Gender, $dev_uid, $selected_card_uid);
                            mysqli_stmt_execute($result);
                            
                            // Clear the card selection after successful enrollment
                            $sql = "UPDATE users SET card_select=0 WHERE card_uid=?";
                            $clear_result = mysqli_stmt_init($conn);
                            if (mysqli_stmt_prepare($clear_result, $sql)) {
                                mysqli_stmt_bind_param($clear_result, "s", $selected_card_uid);
                                mysqli_stmt_execute($clear_result);
                            }
                            
                            echo 1;
                        }
                    }
                    else {
                        echo "This Serial Number already exists!";
                    }
                }
            }
            else {
                echo "Please fill all the fields!";
            }
        }
        else {
            echo "There's no selected Card!";
        }
    }
}
?>
