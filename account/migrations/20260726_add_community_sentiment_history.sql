-- Adds rolling 30-day vote expiry, append-only vote events, and daily
-- sentiment snapshots. Apply once to an existing account database.

ALTER TABLE miq_community_votes
    ADD COLUMN expires_at DATETIME NULL AFTER direction;

UPDATE miq_community_votes
SET expires_at = DATE_ADD(updated_at, INTERVAL 30 DAY)
WHERE expires_at IS NULL;

ALTER TABLE miq_community_votes
    MODIFY COLUMN expires_at DATETIME NOT NULL,
    ADD KEY ix_miq_vote_active (context_type, context_key, expires_at, direction);

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
    KEY ix_miq_vote_event_created (created_at),
    CONSTRAINT fk_miq_vote_event_user FOREIGN KEY (user_id) REFERENCES miq_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO miq_community_vote_events
    (user_id, context_type, context_key, direction, previous_direction, timeframe, period_end, expires_at, created_at)
SELECT
    v.user_id,
    v.context_type,
    v.context_key,
    v.direction,
    NULL,
    '30d',
    DATE_ADD(DATE(v.updated_at), INTERVAL 30 DAY),
    v.expires_at,
    v.updated_at
FROM miq_community_votes v
WHERE NOT EXISTS (SELECT 1 FROM miq_community_vote_events LIMIT 1);

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
