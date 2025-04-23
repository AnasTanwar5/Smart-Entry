-- Add indexes to improve query performance
ALTER TABLE users ADD INDEX idx_card_uid (card_uid);
ALTER TABLE users ADD INDEX idx_serialnumber (serialnumber);
ALTER TABLE users_logs ADD INDEX idx_card_uid (card_uid);
ALTER TABLE users_logs ADD INDEX idx_checkindate (checkindate);
ALTER TABLE devices ADD INDEX idx_device_uid (device_uid);

-- Optimize tables
OPTIMIZE TABLE users;
OPTIMIZE TABLE users_logs;
OPTIMIZE TABLE devices;

-- Set MySQL performance variables
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL innodb_log_file_size = 64M;
SET GLOBAL innodb_flush_log_at_trx_commit = 2;
SET GLOBAL innodb_flush_method = O_DIRECT; 