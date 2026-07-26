-- Security/lifecycle follow-up. Safe to run after the other 20260726 migrations.
-- Existing published replies remain published; new replies enter moderation.
ALTER TABLE miq_community_replies
    MODIFY COLUMN status ENUM('pending', 'published', 'rejected', 'hidden', 'deleted')
        NOT NULL DEFAULT 'pending';
