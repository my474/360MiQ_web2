-- Upgrade existing notification tables to immutable device bindings and a
-- lease-based retry queue. Safe to rerun on MySQL/MariaDB installations.

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND COLUMN_NAME = 'installation_hash'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD COLUMN `installation_hash` CHAR(64) NULL AFTER `token_hash`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND COLUMN_NAME = 'session_hash'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD COLUMN `session_hash` CHAR(64) NULL AFTER `installation_hash`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND COLUMN_NAME = 'session_version'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD COLUMN `session_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `session_hash`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND INDEX_NAME = 'uq_miq_notification_device_installation'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD UNIQUE KEY `uq_miq_notification_device_installation` (`channel`, `installation_hash`)'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_devices' AND INDEX_NAME = 'ix_miq_notification_device_session'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_devices` ADD KEY `ix_miq_notification_device_session` (`user_id`, `session_hash`, `enabled`)'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'user_id'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'attempt_count'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `attempt_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `status`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'next_attempt_at'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `next_attempt_at` DATETIME NULL AFTER `attempt_count`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'lease_token'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `lease_token` CHAR(64) NULL AFTER `next_attempt_at`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'lease_expires_at'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `lease_expires_at` DATETIME NULL AFTER `lease_token`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'requeue_requested'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `requeue_requested` TINYINT(1) NOT NULL DEFAULT 0 AFTER `lease_expires_at`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'error_code'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `error_code` VARCHAR(80) NULL AFTER `error_message`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'http_status'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `http_status` SMALLINT UNSIGNED NULL AFTER `error_code`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND COLUMN_NAME = 'updated_at'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;

ALTER TABLE miq_notification_deliveries
    MODIFY COLUMN status ENUM('pending', 'processing', 'retry', 'sent', 'failed', 'skipped') NOT NULL DEFAULT 'pending';

DELETE delivery
FROM miq_notification_deliveries delivery
LEFT JOIN miq_notifications notification ON notification.id = delivery.notification_id
WHERE notification.id IS NULL;

UPDATE miq_notification_deliveries delivery
INNER JOIN miq_notifications notification ON notification.id = delivery.notification_id
SET delivery.user_id = notification.user_id,
    delivery.next_attempt_at = COALESCE(delivery.next_attempt_at, delivery.created_at),
    delivery.updated_at = COALESCE(delivery.updated_at, delivery.created_at)
WHERE delivery.user_id IS NULL OR delivery.next_attempt_at IS NULL OR delivery.updated_at IS NULL;

ALTER TABLE miq_notification_deliveries
    MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL,
    MODIFY COLUMN updated_at DATETIME NOT NULL;

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND INDEX_NAME = 'ix_miq_notification_delivery_status'),
    'ALTER TABLE `miq_notification_deliveries` DROP INDEX `ix_miq_notification_delivery_status`',
    'SELECT 1'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;
ALTER TABLE miq_notification_deliveries
    ADD KEY ix_miq_notification_delivery_status (status, next_attempt_at, lease_expires_at, id);

SET @miq_notification_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notification_deliveries' AND INDEX_NAME = 'ix_miq_notification_delivery_user'),
    'SELECT 1',
    'ALTER TABLE `miq_notification_deliveries` ADD KEY `ix_miq_notification_delivery_user` (`user_id`, `created_at`)'
);
PREPARE miq_notification_stmt FROM @miq_notification_sql; EXECUTE miq_notification_stmt; DEALLOCATE PREPARE miq_notification_stmt;
