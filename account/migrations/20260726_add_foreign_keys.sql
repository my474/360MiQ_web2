-- Optional account foreign-key migration for databases whose account user has
-- the REFERENCES privilege. Apply after every other account migration.
--
-- This migration is rerunnable: each named constraint is added only when it
-- does not already exist. Existing orphan rows or non-InnoDB tables will make
-- the relevant ALTER fail; correct those data/schema issues before retrying.
-- Replace the miq_ prefix throughout if MIQ_ACCOUNT_TABLE_PREFIX is customized.

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_users' AND CONSTRAINT_NAME = 'fk_miq_users_suspender' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_users` ADD CONSTRAINT `fk_miq_users_suspender` FOREIGN KEY (`suspended_by_user_id`) REFERENCES `miq_users` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_identities' AND CONSTRAINT_NAME = 'fk_miq_identity_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_identities` ADD CONSTRAINT `fk_miq_identity_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_email_tokens' AND CONSTRAINT_NAME = 'fk_miq_email_token_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_email_tokens` ADD CONSTRAINT `fk_miq_email_token_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_password_reset_tokens' AND CONSTRAINT_NAME = 'fk_miq_reset_token_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_password_reset_tokens` ADD CONSTRAINT `fk_miq_reset_token_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_sessions' AND CONSTRAINT_NAME = 'fk_miq_session_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_sessions` ADD CONSTRAINT `fk_miq_session_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_user_activity_daily' AND CONSTRAINT_NAME = 'fk_miq_activity_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_user_activity_daily` ADD CONSTRAINT `fk_miq_activity_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_user_admin_actions' AND CONSTRAINT_NAME = 'fk_miq_user_admin_target' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_user_admin_actions` ADD CONSTRAINT `fk_miq_user_admin_target` FOREIGN KEY (`target_user_id`) REFERENCES `miq_users` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_user_admin_actions' AND CONSTRAINT_NAME = 'fk_miq_user_admin_actor' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_user_admin_actions` ADD CONSTRAINT `fk_miq_user_admin_actor` FOREIGN KEY (`admin_user_id`) REFERENCES `miq_users` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_recent_searches' AND CONSTRAINT_NAME = 'fk_miq_recent_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_recent_searches` ADD CONSTRAINT `fk_miq_recent_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_screener_presets' AND CONSTRAINT_NAME = 'fk_miq_screener_preset_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_screener_presets` ADD CONSTRAINT `fk_miq_screener_preset_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_saved_charts' AND CONSTRAINT_NAME = 'fk_miq_chart_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_saved_charts` ADD CONSTRAINT `fk_miq_chart_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_chart_versions' AND CONSTRAINT_NAME = 'fk_miq_chart_version_chart' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_chart_versions` ADD CONSTRAINT `fk_miq_chart_version_chart` FOREIGN KEY (`chart_id`) REFERENCES `miq_saved_charts` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_chart_versions' AND CONSTRAINT_NAME = 'fk_miq_chart_version_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_chart_versions` ADD CONSTRAINT `fk_miq_chart_version_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_pine_scripts' AND CONSTRAINT_NAME = 'fk_miq_script_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_pine_scripts` ADD CONSTRAINT `fk_miq_script_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_pine_script_versions' AND CONSTRAINT_NAME = 'fk_miq_script_version_script' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_pine_script_versions` ADD CONSTRAINT `fk_miq_script_version_script` FOREIGN KEY (`script_id`) REFERENCES `miq_pine_scripts` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_pine_script_versions' AND CONSTRAINT_NAME = 'fk_miq_script_version_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_pine_script_versions` ADD CONSTRAINT `fk_miq_script_version_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_watchlists' AND CONSTRAINT_NAME = 'fk_miq_watchlist_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_watchlists` ADD CONSTRAINT `fk_miq_watchlist_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_watchlist_items' AND CONSTRAINT_NAME = 'fk_miq_watchlist_item_list' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_watchlist_items` ADD CONSTRAINT `fk_miq_watchlist_item_list` FOREIGN KEY (`watchlist_id`) REFERENCES `miq_watchlists` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_watchlist_items' AND CONSTRAINT_NAME = 'fk_miq_watchlist_item_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_watchlist_items` ADD CONSTRAINT `fk_miq_watchlist_item_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_user_preferences' AND CONSTRAINT_NAME = 'fk_miq_preference_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_user_preferences` ADD CONSTRAINT `fk_miq_preference_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_research_notes' AND CONSTRAINT_NAME = 'fk_miq_note_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_research_notes` ADD CONSTRAINT `fk_miq_note_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_research_notes' AND CONSTRAINT_NAME = 'fk_miq_note_chart' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_research_notes` ADD CONSTRAINT `fk_miq_note_chart` FOREIGN KEY (`chart_id`) REFERENCES `miq_saved_charts` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_research_notes' AND CONSTRAINT_NAME = 'fk_miq_note_script' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_research_notes` ADD CONSTRAINT `fk_miq_note_script` FOREIGN KEY (`script_id`) REFERENCES `miq_pine_scripts` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_price_alerts' AND CONSTRAINT_NAME = 'fk_miq_alert_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_price_alerts` ADD CONSTRAINT `fk_miq_alert_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_notifications' AND CONSTRAINT_NAME = 'fk_miq_notification_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_notifications` ADD CONSTRAINT `fk_miq_notification_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_ideas' AND CONSTRAINT_NAME = 'fk_miq_idea_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_ideas` ADD CONSTRAINT `fk_miq_idea_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_bookmarks' AND CONSTRAINT_NAME = 'fk_miq_bookmark_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_bookmarks` ADD CONSTRAINT `fk_miq_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_bookmarks' AND CONSTRAINT_NAME = 'fk_miq_bookmark_idea' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_bookmarks` ADD CONSTRAINT `fk_miq_bookmark_idea` FOREIGN KEY (`idea_id`) REFERENCES `miq_community_ideas` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_replies' AND CONSTRAINT_NAME = 'fk_miq_reply_idea' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_replies` ADD CONSTRAINT `fk_miq_reply_idea` FOREIGN KEY (`idea_id`) REFERENCES `miq_community_ideas` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_replies' AND CONSTRAINT_NAME = 'fk_miq_reply_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_replies` ADD CONSTRAINT `fk_miq_reply_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_replies' AND CONSTRAINT_NAME = 'fk_miq_reply_parent' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_replies` ADD CONSTRAINT `fk_miq_reply_parent` FOREIGN KEY (`parent_reply_id`) REFERENCES `miq_community_replies` (`id`) ON DELETE SET NULL'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_idea_revisions' AND CONSTRAINT_NAME = 'fk_miq_idea_revision_idea' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_idea_revisions` ADD CONSTRAINT `fk_miq_idea_revision_idea` FOREIGN KEY (`idea_id`) REFERENCES `miq_community_ideas` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_idea_revisions' AND CONSTRAINT_NAME = 'fk_miq_idea_revision_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_idea_revisions` ADD CONSTRAINT `fk_miq_idea_revision_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_votes' AND CONSTRAINT_NAME = 'fk_miq_vote_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_votes` ADD CONSTRAINT `fk_miq_vote_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_vote_events' AND CONSTRAINT_NAME = 'fk_miq_vote_event_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_vote_events` ADD CONSTRAINT `fk_miq_vote_event_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_reports' AND CONSTRAINT_NAME = 'fk_miq_report_idea' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_reports` ADD CONSTRAINT `fk_miq_report_idea` FOREIGN KEY (`idea_id`) REFERENCES `miq_community_ideas` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_community_reports' AND CONSTRAINT_NAME = 'fk_miq_report_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_community_reports` ADD CONSTRAINT `fk_miq_report_user` FOREIGN KEY (`reporter_user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_moderation_actions' AND CONSTRAINT_NAME = 'fk_miq_moderation_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_moderation_actions` ADD CONSTRAINT `fk_miq_moderation_user` FOREIGN KEY (`moderator_user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_moderation_actions' AND CONSTRAINT_NAME = 'fk_miq_moderation_idea' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_moderation_actions` ADD CONSTRAINT `fk_miq_moderation_idea` FOREIGN KEY (`idea_id`) REFERENCES `miq_community_ideas` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'miq_sso_tokens' AND CONSTRAINT_NAME = 'fk_miq_sso_user' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),
    'SELECT 1',
    'ALTER TABLE `miq_sso_tokens` ADD CONSTRAINT `fk_miq_sso_user` FOREIGN KEY (`user_id`) REFERENCES `miq_users` (`id`) ON DELETE CASCADE'
);
PREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;

SET @miq_fk_sql = NULL;
