# 360MiQ account feature

The main PHP site now has a first-party account/workspace layer. It is intentionally separate from the WordPress editorial application under `/blog`.

## What is included

- Email/password sign-up with verification and password reset.
- Google Identity Services sign-in hook.
- Secure PHP session cookie and CSRF token helpers.
- Header account dropdown and private workspace page.
- Recent-search syncing.
- Advanced-chart local autosave for everyone, plus conflict-safe account sync for logged-in users.
- Named chart layouts with Save As, rename, duplicate, delete, explicit versions, and restore.
- Pine scripts stored as first-class account assets with stable IDs, rename, duplicate, archive, delete, version history, restore, and source download.
- Existing browser-local chart and Pine storage formats remain supported.
- Watchlist storage and workspace display.
- Community Pulse voting on the homepage and stock and market pages.
- Moderated community idea drafts, public idea feed, and logged-in user reporting.
- Moderator dashboard with pending and reported-content queues, publish/reject/hide decisions, required adverse-action notes, and audit history.
- Administrator dashboard with user search, 24-hour/7-day/30-day activity, saved-work counts, timed suspension, permanent blocking, session revocation, restoration, and an access-action audit trail.
- Account settings, data export, Google linking, and account deletion.
- Optional main-site-to-WordPress contributor SSO handoff.

Public browsing does not require an account. Saving, following, voting, submitting, and publishing require an account. Community content is never published directly to the homepage.

## Deployment setup

1. For a new account database, apply `schema.sql`. For an existing account database, apply all migrations in this order:
   - `migrations/20260725_add_rate_limits.sql`
   - `migrations/20260725_upgrade_chart_script_assets.sql`
   - `migrations/20260726_add_community_sentiment_history.sql`
   - `migrations/20260726_add_user_activity_admin.sql`

   Apply the chart/script asset migration before deploying the matching PHP and JavaScript changes. It preserves existing chart layouts and Pine scripts while assigning stable asset keys and identifying the existing `Auto:*` chart records as per-symbol workspaces. The default table prefix is `miq_`; set `MIQ_ACCOUNT_TABLE_PREFIX` if a different prefix is required.

   Existing installations must resolve any duplicate display names before adding `uq_miq_users_display_name` to the existing users table. Check them with `SELECT LOWER(display_name) AS normalized_name, COUNT(*) AS total FROM miq_users GROUP BY LOWER(display_name) HAVING COUNT(*) > 1;`, then run `ALTER TABLE miq_users ADD UNIQUE KEY uq_miq_users_display_name (display_name);` using the configured table prefix.
2. Configure the dedicated account database include. Production defaults to `/home2/aamiqcom/php_script/mysql_vars_account.php`; `ACCOUNT_DB_INCLUDE` can override it. The repository template is `mysql_vars_account.php`; deploy it outside the web root, or provide `ACCOUNT_DB_HOST`, `ACCOUNT_DB_NAME`, `ACCOUNT_DB_USER`, `ACCOUNT_DB_PASSWORD`, and optional `ACCOUNT_DB_PORT`. Account code never falls back to the stock database.
3. Set `MIQ_SITE_URL=https://360miq.com` and `ACCOUNT_EMAIL_FROM` to a sender that the host can deliver.
4. Configure email delivery. Production defaults to `/home2/aamiqcom/cronjobs/email.php` through `ACCOUNT_MAILER_INCLUDE`; that file must define `email($subject, $body, $toEmail, $toName)` and keep the PHPMailer SMTP credentials outside this repository. If the configured helper is missing or fails, verification/reset delivery fails safely and is logged.
5. For Google login, create a production Web OAuth client in Google Cloud, configure the exact 360MiQ origin, set `GOOGLE_CLIENT_ID`, and enable the Google Identity Services client library. The backend verifies the returned ID token through Google's tokeninfo endpoint. A mature Google API client can replace that verification implementation if desired.
6. Set `MIQ_ACCOUNT_DEBUG=false` in production.
7. Set `MIQ_COMMUNITY_ENABLED=true` to expose Community Pulse and Community Ideas. Change it to `false` to remove community cards, links, workspace controls, and pages and to reject community-only API actions. The root `.htaccess` contains this switch.
8. Grant your own account administrator access with `UPDATE miq_users SET role = 'admin' WHERE email = 'your-email@example.com';`. Alternatively, set `MIQ_ADMIN_EMAILS` to a comma-separated administrator allowlist. Only administrators can open `account_user_admin`.

Activity writes are throttled to once per signed-in session every 15 minutes. Adjust that interval with `MIQ_ACTIVITY_WRITE_INTERVAL`; the minimum is 60 seconds. The chart and script quotas can be adjusted with `MIQ_MAX_CHART_COUNT`, `MIQ_MAX_NAMED_CHART_COUNT`, `MIQ_MAX_SCRIPT_COUNT`, and `MIQ_MAX_ASSET_VERSIONS`.

## WordPress SSO

The optional SSO bridge is disabled until the same high-entropy secret is configured in both applications.

In the main-site environment:

```text
MIQ_SSO_SHARED_SECRET=<long-random-secret>
```

In `blog/wp-config.php`:

```php
define('MIQ_SSO_SHARED_SECRET', '<the-same-long-random-secret>');
define('MIQ_MAIN_SITE_URL', 'https://360miq.com');
```

The mu-plugin `blog/wp-content/mu-plugins/miq-main-site-sso.php` maps a main-site account to a WordPress Contributor account. It does not copy or synchronize passwords. Existing administrators are never downgraded.

## Important production checks

- Use HTTPS everywhere.
- Keep database credentials and Google secrets outside the repository.
- Keep charts, scripts, and drafts private by default.
- Do not store raw market history in saved layouts; the chart engine stores configuration only.
- Keep Pine execution in the existing browser-safe restricted runtime.
- Add SMTP delivery monitoring before relying on verification and reset email.
- Add a scheduled cleanup for expired email, reset, and SSO tokens.
- Run the included cleanup script daily from cron: `php /path/to/account/cleanup_rate_limits.php`. It removes stale rate-limit rows, expired session records, and activity records older than 400 days, and releases expired timed suspensions.
- Run `php /path/to/account/snapshot_community_sentiment.php` daily after midnight UTC. It persists the current rolling 30-day bullish/neutral/bearish counts for every active stock, market, and global context. The same job bounds storage by retaining 211 days of vote events and 400 days of daily snapshots.
- Review the financial-content disclaimer, privacy notice, user-content terms, and moderation policy before publishing community content.

## Default abuse limits

The account layer stores only a SHA-256 hash of each IP/email key. Defaults are configurable through environment variables:

- Login: 20 attempts per IP and 8 per email per 15 minutes.
- Registration: 5 attempts per IP and 3 per email per hour.
- Password reset: 10 requests per IP and 3 per email per hour.
- Verification/reset email delivery: 12 per IP per hour, 3 per recipient per hour, and one message per recipient per 60 seconds.
- Community pulse: 30 vote submissions or changes per account per hour.
- Community reports: 10 report submissions per account per hour, with duplicate open reports blocked.

Rate-limit failures fail closed and are logged without recording raw IP addresses or email addresses.

## API surface

The browser-facing endpoint is `account_api.php`. It supports workspace reads, search/chart/script/idea saves, community pulse counts and votes, reports, and moderator actions. Chart and script assets support paginated listing, exact loading, optimistic-revision conflict detection, rename, duplicate, delete/archive, version listing, and version restore. All write actions require the session CSRF token.

The API is deliberately small so the existing PHP pages can adopt account-backed persistence incrementally without moving market-data endpoints into the account service.
