<?php
require 'connectDB.php';
date_default_timezone_set('Asia/Kolkata');

// Handle card selection
if (isset($_POST['select_card'])) {
    $card_uid = $_POST['card_uid'];
    
    // First, clear all selections
    $sql = "UPDATE users SET card_select=0";
    mysqli_query($conn, $sql);
    
    // Then select the chosen card
    $sql = "UPDATE users SET card_select=1 WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
}

// Handle user update
if (isset($_POST['update_user'])) {
    $card_uid = $_POST['card_uid'];
    $username = $_POST['username'];
    $serialnumber = $_POST['serialnumber'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    
    $sql = "UPDATE users SET username=?, serialnumber=?, gender=?, email=? WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $username, $serialnumber, $gender, $email, $card_uid);
    mysqli_stmt_execute($stmt);
}

// Handle user removal
if (isset($_POST['remove_user'])) {
    $card_uid = $_POST['card_uid'];
    
    $sql = "DELETE FROM users WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
}
?>
<div class="table-responsive-sm" style="max-height: 870px;"> 
    <table class="table">
        <thead class="table-primary">
            <tr>
                <th>Card UID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>S.No</th>
                <th>Date</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody class="table-secondary">
        <?php
            $sql = "SELECT * FROM users ORDER BY id DESC";
            $result = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($result, $sql)) {
                echo '<p class="error">SQL Error</p>';
            }
            else{
                mysqli_stmt_execute($result);
                $resultl = mysqli_stmt_get_result($result);
                if (mysqli_num_rows($resultl) > 0){
                    while ($row = mysqli_fetch_assoc($resultl)){
                        $card_uid = $row['card_uid'];
        ?>
                        <tr>
                            <td>
                                <button type="button" class="select_btn" onclick="selectCard('<?php echo $card_uid; ?>')" title="select this UID">
                                    <?php if ($row['card_select'] == 1): ?>
                                        <i class='glyphicon glyphicon-ok'></i>
                                    <?php endif; ?>
                                    <?php echo $card_uid; ?>
                                </button>
                            </td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['gender']; ?></td>
                            <td><?php echo $row['serialnumber']; ?></td>
                            <td><?php echo $row['user_date']; ?></td>
                            <td><?php echo ($row['device_dep'] == "0") ? "All" : $row['device_dep']; ?></td>
                        </tr>
        <?php
                    }   
                }
            }
        ?>
        </tbody>
    </table>
</div>

<script>
function selectCard(card_uid) {
    // First clear all checkmarks
    $('.select_btn i').remove();
    
    // Add checkmark to selected card
    $('.select_btn').filter(function() {
        return $(this).text().trim() === card_uid;
    }).prepend('<i class="glyphicon glyphicon-ok"></i>');
    
    // Send selection to server
    $.ajax({
        type: 'POST',
        url: 'manage_users_conf.php',
        data: {
            select_card: 1,
            card_uid: card_uid
        },
        success: function(response) {
            if (response != 1) {
                // If server update failed, remove the checkmark
                $('.select_btn i').remove();
            }
        }
    });
}
</script>