-- 360MiQ main-site account/workspace schema.
-- Replace `miq_` with MIQ_ACCOUNT_TABLE_PREFIX when deploying.
-- Foreign keys are intentionally omitted from this portable baseline.
-- Databases whose account user has REFERENCES may apply the optional
-- migrations/20260726_add_foreign_keys.sql after every other migration.
-- Ordered lifecycle cleanup remains in account/lifecycle.php as defense in depth.

CREATE TABLE IF NOT EXISTS miq_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NULL,
    display_name VARCHAR(80) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    role ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    session_version INT UNSIGNED NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    last_seen_at DATETIME NULL,
    login_count INT UNSIGNED NOT NULL DEFAULT 0,
    suspended_at DATETIME NULL,
    suspended_until DATETIME NULL,
    suspension_reason VARCHAR(500) NULL,
    suspended_by_user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_users_email (email),
    UNIQUE KEY uq_miq_users_display_name (display_name),
    KEY ix_miq_users_status (status),
    KEY ix_miq_users_last_seen (last_seen_at),
    KEY ix_miq_users_suspension (status, suspended_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_identities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(32) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    provider_email VARCHAR(254) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_identity (provider, provider_user_id),
    KEY ix_miq_identity_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_email_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_email_token (token_hash),
    KEY ix_miq_email_token_user (user_id),
    KEY ix_miq_email_token_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_password_reset_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_reset_token (token_hash),
    KEY ix_miq_reset_token_user (user_id),
    KEY ix_miq_reset_token_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_rate_limits (
    scope VARCHAR(64) NOT NULL,
    key_hash CHAR(64) NOT NULL,
    window_started_at DATETIME NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_attempt_at DATETIME NOT NULL,
    PRIMARY KEY (scope, key_hash),
    KEY ix_miq_rate_last (last_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    session_hash CHAR(64) NOT NULL,
    user_agent VARCHAR(500) NULL,
    ip_hash CHAR(64) NULL,
    last_seen_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_session_hash (session_hash),
    KEY ix_miq_session_user (user_id),
    KEY ix_miq_session_last_seen (last_seen_at),
    KEY ix_miq_session_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_user_activity_daily (
    user_id BIGINT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    first_seen_at DATETIME NOT NULL,
    last_seen_at DATETIME NOT NULL,
    request_count INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, activity_date),
    KEY ix_miq_activity_date (activity_date)
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
    KEY ix_miq_user_admin_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_recent_searches (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    exchange VARCHAR(32) NULL,
    display_name VARCHAR(160) NULL,
    searched_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_recent_search (user_id, code),
    KEY ix_miq_recent_user_time (user_id, searched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    KEY ix_miq_screener_preset_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_saved_charts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    asset_key CHAR(36) NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(40) NOT NULL,
    kind ENUM('workspace', 'named') NOT NULL DEFAULT 'named',
    layout_json MEDIUMTEXT NOT NULL,
    visibility ENUM('private', 'unlisted', 'public') NOT NULL DEFAULT 'private',
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    last_client_updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_chart_asset (user_id, asset_key),
    KEY ix_miq_chart_user_time (user_id, updated_at),
    KEY ix_miq_chart_workspace (user_id, kind, code),
    KEY ix_miq_chart_public (visibility, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_chart_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    chart_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    revision INT UNSIGNED NOT NULL,
    layout_json MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_chart_revision (chart_id, revision),
    KEY ix_miq_chart_versions_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_pine_scripts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    asset_key CHAR(36) NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(40) NULL,
    source_code MEDIUMTEXT NOT NULL,
    visibility ENUM('private', 'unlisted', 'public') NOT NULL DEFAULT 'private',
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    last_client_updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_script_asset (user_id, asset_key),
    KEY ix_miq_script_user_time (user_id, updated_at),
    KEY ix_miq_script_public (visibility, status, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_pine_script_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    script_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    revision INT UNSIGNED NOT NULL,
    source_code MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_script_revision (script_id, revision),
    KEY ix_miq_script_versions_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_watchlists (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_watchlist_user (user_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_watchlist_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    watchlist_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_watchlist_item (watchlist_id, code),
    KEY ix_miq_watchlist_item_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS miq_chat_histories (
    user_id BIGINT UNSIGNED NOT NULL,
    history_json MEDIUMTEXT NOT NULL,
    history_bytes INT UNSIGNED NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id),
    KEY ix_miq_chat_history_updated (updated_at)
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

CREATE TABLE IF NOT EXISTS miq_community_ideas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(40) NULL,
    title VARCHAR(160) NOT NULL,
    direction ENUM('bullish', 'bearish', 'neutral') NOT NULL,
    timeframe VARCHAR(40) NULL,
    thesis TEXT NOT NULL,
    catalyst TEXT NULL,
    risk TEXT NULL,
    disclosure VARCHAR(500) NULL,
    status ENUM('draft', 'pending', 'published', 'rejected', 'hidden', 'archived') NOT NULL DEFAULT 'draft',
    visibility ENUM('private', 'public') NOT NULL DEFAULT 'private',
    slug VARCHAR(180) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    published_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_idea_slug (slug),
    KEY ix_miq_idea_context (code, status, published_at),
    KEY ix_miq_idea_user_time (user_id, updated_at)
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

CREATE TABLE IF NOT EXISTS miq_community_idea_revisions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    idea_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    payload_json MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_idea_revision (idea_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    context_type VARCHAR(32) NOT NULL,
    context_key VARCHAR(80) NOT NULL,
    direction ENUM('bullish', 'bearish', 'neutral') NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_vote (user_id, context_type, context_key),
    KEY ix_miq_vote_context (context_type, context_key, direction),
    KEY ix_miq_vote_active (context_type, context_key, expires_at, direction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_vote_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    context_type VARCHAR(32) NOT NULL,
    context_key VARCHAR(80) NOT NULL,
    direction ENUM('bullish', 'bearish', 'neutral') NOT NULL,
    previous_direction ENUM('bullish', 'bearish', 'neutral') NULL,
    timeframe VARCHAR(10) NOT NULL DEFAULT '30d',
    period_end DATE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_vote_event_context (context_type, context_key, created_at),
    KEY ix_miq_vote_event_user (user_id, context_type, context_key, created_at),
    KEY ix_miq_vote_event_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_sentiment_daily (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    snapshot_date DATE NOT NULL,
    context_type VARCHAR(32) NOT NULL,
    context_key VARCHAR(80) NOT NULL,
    timeframe VARCHAR(10) NOT NULL DEFAULT '30d',
    bullish_count INT UNSIGNED NOT NULL DEFAULT 0,
    neutral_count INT UNSIGNED NOT NULL DEFAULT 0,
    bearish_count INT UNSIGNED NOT NULL DEFAULT 0,
    total_count INT UNSIGNED NOT NULL DEFAULT 0,
    sentiment_score DECIMAL(6,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_sentiment_daily (snapshot_date, context_type, context_key, timeframe),
    KEY ix_miq_sentiment_context (context_type, context_key, snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_reports (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    idea_id BIGINT UNSIGNED NOT NULL,
    reporter_user_id BIGINT UNSIGNED NOT NULL,
    reason VARCHAR(80) NOT NULL,
    details VARCHAR(500) NULL,
    status ENUM('open', 'reviewed', 'dismissed') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_report_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_moderation_actions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    moderator_user_id BIGINT UNSIGNED NOT NULL,
    idea_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(32) NOT NULL,
    note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_moderation_idea (idea_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_sso_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_sso_token (token_hash),
    KEY ix_miq_sso_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
