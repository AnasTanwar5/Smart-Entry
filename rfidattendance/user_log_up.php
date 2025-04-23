//user log 

<?php  
session_start();
require 'connectDB.php'; // Ensure this file exists and has a valid DB connection
date_default_timezone_set('Asia/Kolkata');  // Set timezone to Indian Standard Time

?>
<div class="table-responsive" style="max-height: 500px;"> 
  <table class="table">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Serial Number</th>
        <th>Card UID</th>
        <th>Device Dep</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
      </tr>
    </thead>
    <tbody class="table-secondary">
      <?php

        // Initialize search query
        $_SESSION['searchQuery'] = "1=1";  // Default condition (always true)

        if (isset($_POST['log_date'])) {
            $conditions = []; // Array to store conditions

            // Date Filters
            if (!empty($_POST['date_sel_start'])) {
                $Start_date = $_POST['date_sel_start'];
                $conditions[] = "checkindate >= '".$Start_date."'";
            }
            if (!empty($_POST['date_sel_end'])) {
                $End_date = $_POST['date_sel_end'];
                $conditions[] = "checkindate <= '".$End_date."'";
            }

            // Time Filters
            if ($_POST['time_sel'] == "Time_in") {
                if (!empty($_POST['time_sel_start'])) {
                    $Start_time = $_POST['time_sel_start'];
                    $conditions[] = "timein >= '".$Start_time."'";
                }
                if (!empty($_POST['time_sel_end'])) {
                    $End_time = $_POST['time_sel_end'];
                    $conditions[] = "timein <= '".$End_time."'";
                }
            }
            if ($_POST['time_sel'] == "Time_out") {
                if (!empty($_POST['time_sel_start'])) {
                    $Start_time = $_POST['time_sel_start'];
                    $conditions[] = "timeout >= '".$Start_time."'";
                }
                if (!empty($_POST['time_sel_end'])) {
                    $End_time = $_POST['time_sel_end'];
                    $conditions[] = "timeout <= '".$End_time."'";
                }
            }

            // Card UID Filter
            if (!empty($_POST['card_sel'])) {
                $Card_sel = $_POST['card_sel'];
                $conditions[] = "card_uid = '".$Card_sel."'";
            }

            // Device UID Filter
            if (!empty($_POST['dev_uid'])) {
                $dev_uid = $_POST['dev_uid'];
                $conditions[] = "device_uid = '".$dev_uid."'";
            }

            // Build final search query
            if (!empty($conditions)) {
                $_SESSION['searchQuery'] = implode(" AND ", $conditions);
            }
        }

        // Default query for today's records if no filters are applied
        if ($_POST['select_date'] == 1) {
            $Start_date = date("Y-m-d");
            $_SESSION['searchQuery'] = "checkindate = '".$Start_date."'";
        }

        // Execute the SQL query
        $sql = "SELECT * FROM users_logs ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            echo '<p class="error">SQL Error: ' . mysqli_error($conn) . '</p>';
        } else {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
      ?>
                  <tr>
                  <td><?php echo htmlspecialchars($row['id']); ?></td>
                  <td><?php echo htmlspecialchars($row['username']); ?></td>
                  <td><?php echo htmlspecialchars($row['serialnumber']); ?></td>
                  <td><?php echo htmlspecialchars($row['card_uid']); ?></td>
                  <td><?php echo htmlspecialchars($row['device_dep']); ?></td>
                  <td><?php echo htmlspecialchars($row['checkindate']); ?></td>
                  <td><?php echo htmlspecialchars($row['timein']); ?></td>
                  <td><?php echo htmlspecialchars($row['timeout']); ?></td>
                  </tr>
      <?php
                }
            } else {
                echo '<tr><td colspan="8">No records found</td></tr>';
            }
        }
      ?>
    </tbody>
  </table>
</div>
