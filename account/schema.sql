-- 360MiQ main-site account/workspace schema.
-- Replace `miq_` with MIQ_ACCOUNT_TABLE_PREFIX when deploying.

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
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_users_email (email),
    UNIQUE KEY uq_miq_users_display_name (display_name),
    KEY ix_miq_users_status (status)
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
    KEY ix_miq_identity_user (user_id),
    CONSTRAINT fk_miq_identity_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_email_token_expiry (expires_at),
    CONSTRAINT fk_miq_email_token_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_reset_token_expiry (expires_at),
    CONSTRAINT fk_miq_reset_token_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    CONSTRAINT fk_miq_session_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_recent_user_time (user_id, searched_at),
    CONSTRAINT fk_miq_recent_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_saved_charts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(40) NOT NULL,
    layout_json MEDIUMTEXT NOT NULL,
    visibility ENUM('private', 'unlisted', 'public') NOT NULL DEFAULT 'private',
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    last_client_updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_chart_user_time (user_id, updated_at),
    KEY ix_miq_chart_public (visibility, updated_at),
    CONSTRAINT fk_miq_chart_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_chart_versions_user (user_id, created_at),
    CONSTRAINT fk_miq_chart_version_chart FOREIGN KEY (chart_id) REFERENCES miq_saved_charts (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_chart_version_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_pine_scripts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(40) NULL,
    source_code MEDIUMTEXT NOT NULL,
    visibility ENUM('private', 'unlisted', 'public') NOT NULL DEFAULT 'private',
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_script_user_time (user_id, updated_at),
    KEY ix_miq_script_public (visibility, status, updated_at),
    CONSTRAINT fk_miq_script_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_script_versions_user (user_id, created_at),
    CONSTRAINT fk_miq_script_version_script FOREIGN KEY (script_id) REFERENCES miq_pine_scripts (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_script_version_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_watchlists (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_watchlist_user (user_id, updated_at),
    CONSTRAINT fk_miq_watchlist_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_watchlist_item_user (user_id),
    CONSTRAINT fk_miq_watchlist_item_list FOREIGN KEY (watchlist_id) REFERENCES miq_watchlists (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_watchlist_item_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_idea_user_time (user_id, updated_at),
    CONSTRAINT fk_miq_idea_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_idea_revisions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    idea_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    payload_json MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_idea_revision (idea_id, created_at),
    CONSTRAINT fk_miq_idea_revision_idea FOREIGN KEY (idea_id) REFERENCES miq_community_ideas (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_idea_revision_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_community_votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    context_type VARCHAR(32) NOT NULL,
    context_key VARCHAR(80) NOT NULL,
    direction ENUM('bullish', 'bearish', 'neutral') NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miq_vote (user_id, context_type, context_key),
    KEY ix_miq_vote_context (context_type, context_key, direction),
    CONSTRAINT fk_miq_vote_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
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
    KEY ix_miq_report_status (status, created_at),
    CONSTRAINT fk_miq_report_idea FOREIGN KEY (idea_id) REFERENCES miq_community_ideas (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_report_user FOREIGN KEY (reporter_user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS miq_moderation_actions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    moderator_user_id BIGINT UNSIGNED NOT NULL,
    idea_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(32) NOT NULL,
    note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_miq_moderation_idea (idea_id, created_at),
    CONSTRAINT fk_miq_moderation_user FOREIGN KEY (moderator_user_id) REFERENCES miq_users (id) ON DELETE CASCADE,
    CONSTRAINT fk_miq_moderation_idea FOREIGN KEY (idea_id) REFERENCES miq_community_ideas (id) ON DELETE CASCADE
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
    KEY ix_miq_sso_expiry (expires_at),
    CONSTRAINT fk_miq_sso_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
