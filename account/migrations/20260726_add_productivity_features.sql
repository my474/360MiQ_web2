-- Account productivity, research, alert, and community follow-up features.
-- This migration intentionally omits FOREIGN KEY clauses so it can be
-- imported by restricted Bluehost database users without REFERENCES grants.

CREATE TABLE IF NOT EXISTS miq_user_preferences (
    user_id BIGINT UNSIGNED NOT NULL,
    default_market VARCHAR(32) NOT NULL DEFAULT 'NYSE',
    preferred_timeframe VARCHAR(16) NOT NULL DEFAULT '6m',
    theme_mode ENUM('system', 'light', 'dark') NOT NULL DEFAULT 'system',
    chart_type VARCHAR(24) NOT NULL DEFAULT 'candlestick',
    chart_period VARCHAR(24) NOT NULL DEFAULT 'daily',
    auto_save_charts TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_research_notes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    stock_code VARCHAR(40) NULL,
    chart_id BIGINT UNSIGNED NULL,
    script_id BIGINT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_note_user_time (user_id, updated_at),
    KEY ix_miq_note_stock (user_id, stock_code, updated_at),
    KEY ix_miq_note_chart (user_id, chart_id),
    KEY ix_miq_note_script (user_id, script_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_price_alerts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    condition_type ENUM('above', 'below') NOT NULL,
    target_price DECIMAL(20,6) NOT NULL,
    status ENUM('active', 'triggered', 'disabled') NOT NULL DEFAULT 'active',
    last_price DECIMAL(20,6) NULL,
    triggered_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_alert_user_status (user_id, status, updated_at),
    KEY ix_miq_alert_code_status (code, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(40) NOT NULL,
    title VARCHAR(160) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link_url VARCHAR(500) NULL,
    dedupe_key VARCHAR(190) NULL,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_notification_dedupe (user_id, dedupe_key),
    KEY ix_miq_notification_user_read (user_id, read_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_bookmarks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    idea_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_community_bookmark (user_id, idea_id),
    KEY ix_miq_bookmark_user_time (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_replies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    idea_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    parent_reply_id BIGINT UNSIGNED NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'published', 'rejected', 'hidden', 'deleted') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_reply_idea_time (idea_id, status, created_at),
    KEY ix_miq_reply_user_time (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
