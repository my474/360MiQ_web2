-- Add account activity tracking and administrator suspension controls.
-- Apply once to the aamiqcom_accounts database after taking a backup.
-- Replace the miq_ prefix if MIQ_ACCOUNT_TABLE_PREFIX is customized.

ALTER TABLE miq_users
    ADD COLUMN last_login_at DATETIME NULL AFTER session_version,
    ADD COLUMN last_seen_at DATETIME NULL AFTER last_login_at,
    ADD COLUMN login_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER last_seen_at,
    ADD COLUMN suspended_at DATETIME NULL AFTER login_count,
    ADD COLUMN suspended_until DATETIME NULL AFTER suspended_at,
    ADD COLUMN suspension_reason VARCHAR(500) NULL AFTER suspended_until,
    ADD COLUMN suspended_by_user_id BIGINT UNSIGNED NULL AFTER suspension_reason,
    ADD KEY ix_miq_users_last_seen (last_seen_at),
    ADD KEY ix_miq_users_suspension (status, suspended_until),
    ADD CONSTRAINT fk_miq_users_suspender
        FOREIGN KEY (suspended_by_user_id) REFERENCES miq_users (id) ON DELETE SET NULL;

ALTER TABLE miq_sessions
    ADD KEY ix_miq_session_last_seen (last_seen_at),
    ADD KEY ix_miq_session_expiry (expires_at);

CREATE TABLE IF NOT EXISTS miq_user_activity_daily (
    user_id BIGINT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    first_seen_at DATETIME NOT NULL,
    last_seen_at DATETIME NOT NULL,
    request_count INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, activity_date),
    KEY ix_miq_activity_date (activity_date),
    CONSTRAINT fk_miq_activity_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_user_admin_actions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    target_user_id BIGINT UNSIGNED NULL,
    admin_user_id BIGINT UNSIGNED NULL,
    target_email VARCHAR(254) NOT NULL,
    target_display_name VARCHAR(80) NOT NULL,
    action VARCHAR(32) NOT NULL,
    reason VARCHAR(500) NULL,
    suspended_until DATETIME NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_user_admin_target (target_user_id, created_at),
    KEY ix_miq_user_admin_actor (admin_user_id, created_at),
    KEY ix_miq_user_admin_created (created_at),
    CONSTRAINT fk_miq_user_admin_target FOREIGN KEY (target_user_id) REFERENCES miq_users (id) ON DELETE SET NULL,
    CONSTRAINT fk_miq_user_admin_actor FOREIGN KEY (admin_user_id) REFERENCES miq_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
