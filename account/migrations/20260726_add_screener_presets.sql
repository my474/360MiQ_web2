-- Named screener presets for signed-in account synchronization.
CREATE TABLE IF NOT EXISTS miq_screener_presets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    client_key CHAR(36) NOT NULL,
    name VARCHAR(120) NOT NULL,
    config_json TEXT NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    client_updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_screener_preset_key (user_id, client_key),
    UNIQUE KEY uq_miq_screener_preset_name (user_id, name),
    KEY ix_miq_screener_preset_time (user_id, updated_at),
    KEY ix_miq_screener_preset_default (user_id, is_default),
    CONSTRAINT fk_miq_screener_preset_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
