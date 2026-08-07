-- Account-synced footer chat history. The application enforces the configured
-- serialized UTF-8 byte limit (256 KiB by default) before writing this row.
CREATE TABLE IF NOT EXISTS miq_chat_histories (
    user_id BIGINT UNSIGNED NOT NULL,
    history_json MEDIUMTEXT NOT NULL,
    history_bytes INT UNSIGNED NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id),
    KEY ix_miq_chat_history_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
