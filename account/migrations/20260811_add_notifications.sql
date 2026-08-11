-- Shared notification preferences, FCM device registrations, and delivery history.
-- The migration is intentionally idempotent for Bluehost/staging deploys.

CREATE TABLE IF NOT EXISTS miq_notification_preferences (
    user_id BIGINT UNSIGNED NOT NULL,
    price_alerts_enabled TINYINT(1) NOT NULL DEFAULT 1,
    community_replies_enabled TINYINT(1) NOT NULL DEFAULT 0,
    moderation_enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_notification_devices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    channel ENUM('web', 'android') NOT NULL,
    target_type ENUM('token', 'fid') NOT NULL DEFAULT 'token',
    device_token TEXT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    installation_hash CHAR(64) NULL,
    session_hash CHAR(64) NULL,
    session_version INT UNSIGNED NOT NULL DEFAULT 1,
    label VARCHAR(120) NULL,
    app_version VARCHAR(40) NULL,
    user_agent VARCHAR(500) NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    last_seen_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_notification_device_token (channel, token_hash),
    UNIQUE KEY uq_miq_notification_device_installation (channel, installation_hash),
    KEY ix_miq_notification_device_user (user_id, enabled, channel, updated_at),
    KEY ix_miq_notification_device_session (user_id, session_hash, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_notification_deliveries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_id BIGINT UNSIGNED NOT NULL,
    device_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'processing', 'retry', 'sent', 'failed', 'skipped') NOT NULL DEFAULT 'pending',
    attempt_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    next_attempt_at DATETIME NULL,
    lease_token CHAR(64) NULL,
    lease_expires_at DATETIME NULL,
    requeue_requested TINYINT(1) NOT NULL DEFAULT 0,
    provider_message_id VARCHAR(190) NULL,
    error_message VARCHAR(500) NULL,
    error_code VARCHAR(80) NULL,
    http_status SMALLINT UNSIGNED NULL,
    attempted_at DATETIME NULL,
    delivered_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_notification_delivery (notification_id, device_id),
    KEY ix_miq_notification_delivery_status (status, next_attempt_at, lease_expires_at, id),
    KEY ix_miq_notification_delivery_user (user_id, created_at),
    KEY ix_miq_notification_delivery_device (device_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
