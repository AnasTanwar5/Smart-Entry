<?php
require 'connectDB.php';

// Function to optimize tables
function optimizeTables($conn) {
    $tables = ['users', 'users_logs', 'devices'];
    foreach ($tables as $table) {
        $sql = "OPTIMIZE TABLE $table";
        if ($conn->query($sql)) {
            echo "Table $table optimized successfully\n";
        } else {
            echo "Error optimizing table $table: " . $conn->error . "\n";
        }
    }
}

// Function to add indexes
function addIndexes($conn) {
    $indexes = [
        'users' => [
            'idx_card_uid' => 'card_uid',
            'idx_serialnumber' => 'serialnumber'
        ],
        'users_logs' => [
            'idx_card_uid' => 'card_uid',
            'idx_checkindate' => 'checkindate'
        ],
        'devices' => [
            'idx_device_uid' => 'device_uid'
        ]
    ];

    foreach ($indexes as $table => $columns) {
        foreach ($columns as $index => $column) {
            $sql = "SHOW INDEX FROM $table WHERE Key_name = '$index'";
            $result = $conn->query($sql);
            
            if ($result->num_rows == 0) {
                $sql = "ALTER TABLE $table ADD INDEX $index ($column)";
                if ($conn->query($sql)) {
                    echo "Index $index added to table $table\n";
                } else {
                    echo "Error adding index $index to table $table: " . $conn->error . "\n";
                }
            }
        }
    }
}

// Execute optimizations
echo "Starting database optimization...\n";
optimizeTables($conn);
addIndexes($conn);
echo "Database optimization completed!\n";
?> 