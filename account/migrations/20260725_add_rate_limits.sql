-- Apply this migration to an existing account database.
-- Replace the miq_ prefix if MIQ_ACCOUNT_TABLE_PREFIX is customized.

CREATE TABLE IF NOT EXISTS miq_rate_limits (
    scope VARCHAR(64) NOT NULL,
    key_hash CHAR(64) NOT NULL,
    window_started_at DATETIME NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_attempt_at DATETIME NOT NULL,
    PRIMARY KEY (scope, key_hash),
    KEY ix_miq_rate_last (last_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
