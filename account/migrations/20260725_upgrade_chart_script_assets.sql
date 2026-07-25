-- Upgrade existing chart and Pine rows to stable account assets.
-- Apply once to the aamiqcom_accounts database after taking a backup.

ALTER TABLE miq_saved_charts
    ADD COLUMN asset_key CHAR(36) NULL AFTER user_id,
    ADD COLUMN kind ENUM('workspace', 'named') NOT NULL DEFAULT 'named' AFTER code;

UPDATE miq_saved_charts
SET asset_key = LOWER(UUID())
WHERE asset_key IS NULL OR asset_key = '';

UPDATE miq_saved_charts
SET kind = CASE WHEN name LIKE 'Auto:%' THEN 'workspace' ELSE 'named' END;

ALTER TABLE miq_saved_charts
    MODIFY asset_key CHAR(36) NOT NULL,
    ADD UNIQUE KEY uq_miq_chart_asset (user_id, asset_key),
    ADD KEY ix_miq_chart_workspace (user_id, kind, code);

ALTER TABLE miq_pine_scripts
    ADD COLUMN asset_key CHAR(36) NULL AFTER user_id,
    ADD COLUMN last_client_updated_at DATETIME NULL AFTER status;

UPDATE miq_pine_scripts
SET asset_key = LOWER(UUID())
WHERE asset_key IS NULL OR asset_key = '';

ALTER TABLE miq_pine_scripts
    MODIFY asset_key CHAR(36) NOT NULL,
    ADD UNIQUE KEY uq_miq_script_asset (user_id, asset_key);
