-- Expand notification targets from legacy registration tokens to an explicit
-- token/FID union. Existing rows intentionally remain tokens: FCM accepts FIDs
-- in the legacy token field during migration, while guessing a row is an FID
-- could break a genuine legacy Android registration token.

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND COLUMN_NAME = 'target_type'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD COLUMN `target_type` ENUM(''token'', ''fid'') NOT NULL DEFAULT ''token'' AFTER `channel`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

UPDATE miq_notification_devices
SET target_type = 'token'
WHERE target_type IS NULL OR target_type NOT IN ('token', 'fid');
