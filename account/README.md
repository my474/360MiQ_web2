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
- In-app notifications with shared unread badges plus optional FCM delivery to browser and Android devices.
- Optional main-site-to-WordPress contributor SSO handoff.

Public browsing does not require an account. Saving, following, voting, submitting, and publishing require an account. Community content is never published directly to the homepage.

## Deployment setup

1. For a new account database, apply `schema.sql`. For an existing account database, apply all migrations in this order:
   - `migrations/20260725_add_rate_limits.sql`
   - `migrations/20260725_upgrade_chart_script_assets.sql`
   - `migrations/20260726_add_community_sentiment_history.sql`
   - `migrations/20260726_add_user_activity_admin.sql`
   - `migrations/20260726_add_screener_presets.sql`
   - `migrations/20260726_add_productivity_features.sql`
   - `migrations/20260726_harden_account_features.sql`
   - `migrations/20260807_add_chat_history.sql`
   - `migrations/20260811_add_notifications.sql`
   - `migrations/20260812_harden_notification_delivery.sql`
   - `migrations/20260813_add_notification_target_type.sql`

   Apply the chart/script asset migration before deploying the matching PHP and JavaScript changes. It preserves existing chart layouts and Pine scripts while assigning stable asset keys and identifying the existing `Auto:*` chart records as per-symbol workspaces. The baseline schema and required migrations omit foreign keys so they remain portable to restricted hosting accounts. Account deletion performs explicit ordered cleanup in application code. The default table prefix is `miq_`; set `MIQ_ACCOUNT_TABLE_PREFIX` if a different prefix is required.

   If the account database user has the `REFERENCES` privilege, apply `migrations/20260726_add_foreign_keys.sql` last. This optional migration adds all 40 account relationships and is safe to rerun because it skips each named foreign key that already exists. Existing orphan rows must be corrected before a related constraint can be installed.

   Verify the optional migration with `SELECT COUNT(*) AS foreign_key_count FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND LEFT(TABLE_NAME, 4) = 'miq_';`. A complete default-prefix installation returns `40`.

   Existing installations must resolve any duplicate display names before adding `uq_miq_users_display_name` to the existing users table. Check them with `SELECT LOWER(display_name) AS normalized_name, COUNT(*) AS total FROM miq_users GROUP BY LOWER(display_name) HAVING COUNT(*) > 1;`, then run `ALTER TABLE miq_users ADD UNIQUE KEY uq_miq_users_display_name (display_name);` using the configured table prefix.
2. Configure the dedicated account database include. Production defaults to `/home2/aamiqcom/php_script/mysql_vars_account.php`; `ACCOUNT_DB_INCLUDE` can override it. The repository template is `mysql_vars_account.php`; deploy it outside the web root, or provide `ACCOUNT_DB_HOST`, `ACCOUNT_DB_NAME`, `ACCOUNT_DB_USER`, `ACCOUNT_DB_PASSWORD`, and optional `ACCOUNT_DB_PORT`. Account code never falls back to the stock database. Main-site chat assets and account API URLs are document-relative, so the same files work at production `/` and a staging prefix such as `/full`. The production WordPress blog under `/blog` uses explicit main-site-root URLs for those shared endpoints.
3. Keep PHP account sessions in a private directory outside the document root. When the production `ACCOUNT_DB_INCLUDE` lives in its default private `php_script` directory, the application automatically creates and uses `/home2/aamiqcom/php_script/account_sessions` with owner-only permissions. Other deployments must set `MIQ_ACCOUNT_SESSION_SAVE_PATH` to an absolute path whose parent already exists outside the public web root. The application migrates the current session file on first use and enables PHP garbage collection for the configured 14-day lifetime, preventing cPanel's shared-session cleanup from logging out idle users.
4. Set `MIQ_SITE_URL=https://360miq.com` and `ACCOUNT_EMAIL_FROM` to a sender that the host can deliver.
5. Configure email delivery. Production defaults to `/home2/aamiqcom/cronjobs/email.php` through `ACCOUNT_MAILER_INCLUDE`; that file must define `email($subject, $body, $toEmail, $toName)`, return the boolean result of `$mail->send()`, and keep PHPMailer SMTP credentials outside this repository. If the configured helper is missing, returns anything other than `true`, or fails, verification/reset delivery fails safely and is logged. Rotate any SMTP password that has ever been pasted into a chat or source file.
6. For Google login, use the production Web OAuth client ID `735181786268-s7n2c9fdg268labp2estg8au267c3m0r.apps.googleusercontent.com`. It is the checked-in public default; `GOOGLE_CLIENT_ID` can override it for staging or rotation. Keep `https://360miq.com` as an exact authorized JavaScript origin. The backend verifies the returned ID token through Google's tokeninfo endpoint. A mature Google API client can replace that verification implementation if desired.
7. Set `MIQ_ACCOUNT_DEBUG=false` in production.
8. Set `MIQ_COMMUNITY_ENABLED=true` to expose Community Pulse and Community Ideas. Change it to `false` to remove community cards, links, workspace controls, and pages and to reject community-only API actions. The root `.htaccess` contains this switch.
9. Grant your own account administrator access with `UPDATE miq_users SET role = 'admin' WHERE email = 'your-email@example.com';`. Alternatively, set `MIQ_ADMIN_EMAILS` to a comma-separated administrator allowlist. Only administrators can open `account_user_admin`.

FCM push delivery is optional and is enabled only when the server credentials and public web configuration are present. Keep the service-account JSON or private key outside the repository and configure these environment variables on the PHP host:

```text
FCM_PROJECT_ID=<firebase-project-id>
FCM_CLIENT_EMAIL=<service-account-client-email>
FCM_PRIVATE_KEY=<service-account-private-key-with-escaped-newlines>
FCM_SERVICE_ACCOUNT_FILE=<absolute-private-path-to-service-account-json>
FCM_WEB_API_KEY=<firebase-web-api-key>
FCM_WEB_AUTH_DOMAIN=<firebase-auth-domain>
FCM_WEB_STORAGE_BUCKET=<firebase-storage-bucket>
FCM_WEB_MESSAGING_SENDER_ID=<firebase-messaging-sender-id>
FCM_WEB_APP_ID=<firebase-web-app-id>
FCM_WEB_VAPID_KEY=<firebase-cloud-messaging-web-push-certificate-key-pair-public-key>
```

The preferred shared-hosting setup is to upload the Firebase service-account JSON outside the public web root with owner-only permissions and point `FCM_SERVICE_ACCOUNT_FILE` to it. Production defaults to `/home2/aamiqcom/php_script/firebase-service-account.json`, allowing web PHP and the CLI worker to share the credential without exposing its contents in `.htaccess` or a cron command. `FCM_SERVICE_ACCOUNT_JSON` may instead contain the complete JSON, or the three split server credential variables may be used when the host provides a secure environment store. `FCM_WEB_SDK_VERSION` defaults to `12.16.0`; `FCM_MAX_DEVICES_PER_NOTIFICATION` defaults to 10 and `FCM_MAX_DEVICES_PER_USER` defaults to 20 active devices. Device upserts are additionally limited to 300 per account per hour by `MIQ_RATE_NOTIFICATION_DEVICE_LIMIT`/`MIQ_RATE_NOTIFICATION_DEVICE_WINDOW`. The account settings page never asks for browser permission until the user clicks Enable browser notifications. Current browser and Android clients register a Firebase Installation ID (FID) with `target_type=fid`; legacy clients may continue sending only `token` and are stored as `target_type=token`. During rollout, new clients also include the FID in the legacy `token` field so an older server can accept it. The Android wrapper must preserve the shared main-site session cookie or complete the normal account sign-in first.

Push delivery is written to a durable database queue and sent outside the web request; notification calls made inside an existing application transaction enqueue within that transaction. Run this worker once per minute (the optional numeric argument overrides the batch size):

```text
* * * * * php /path/to/account/process_notification_queue.php 50
```

The worker uses short leases, retries transient FCM and network failures with bounded exponential backoff, honors `Retry-After`, refreshes an expired OAuth token once, and retires a device only for FCM's structured `UNREGISTERED` token error. Tune it with `FCM_WORKER_BATCH_SIZE`, `FCM_DELIVERY_MAX_ATTEMPTS`, `FCM_RETRY_BASE_SECONDS`, `FCM_RETRY_MAX_SECONDS`, and `FCM_DELIVERY_LEASE_SECONDS` when needed.

Every browser/app registration includes a stable, non-secret installation ID and is bound to both the authenticated account and its current main-site session. Explicit logout or account switching disables the previous session's devices, and the worker also rejects stale, revoked, suspended, deleted, or expired-session bindings before delivery. Browser FID refresh runs automatically only after that account previously opted in on that browser and permission is already granted; startup never opens a permission prompt. Legacy browser tokens are replaced in-place by installation ID after `onRegistered` supplies the FID. Legacy opt-in markers that predate account ownership are retired safely, so an affected user may need to click Enable once after this deployment (an already-granted browser permission is not prompted again).

For a rolling FID migration, deploy the PHP/JavaScript compatibility release first, run `migrations/20260813_add_notification_target_type.sql`, then publish the Android update. The PHP release also works before the column exists and new clients duplicate their FID into the legacy `token` request field, so cached pages and older app versions remain usable throughout the rollout. Do not bulk-label existing rows as FIDs: they deliberately start as `token` and are upgraded in place the next time each current client registers.

The native WebView implementation is in `android/notification-integration/README.md`. Include that module in the Android host app, keep its Firebase Google Services configuration, attach the origin-scoped bridge, and route launch intents as documented there.

The WordPress blog emits only a generic guest account shell so public page caches cannot store a name, CSRF token, or unread count. Account state is hydrated from the no-store main-site bootstrap endpoint after load. Purge the WordPress/Endurance/CDN page cache once when deploying this release so pages generated by an older personalized template cannot remain cached.

Activity writes are throttled to once per signed-in session every 15 minutes. Adjust that interval with `MIQ_ACTIVITY_WRITE_INTERVAL`; the minimum is 60 seconds. Chart and Pine quotas can be adjusted with `MIQ_MAX_CHART_COUNT`, `MIQ_MAX_NAMED_CHART_COUNT`, `MIQ_MAX_SCRIPT_COUNT`, `MIQ_MAX_ASSET_VERSIONS`, and `MIQ_MAX_ASSET_STORAGE_BYTES`. The combined chart/Pine current-and-version storage default is 50 MB per user. Footer chat history keeps up to 40 messages and is capped at 256 KiB of serialized UTF-8 JSON per browser and account by default; adjust the cap with `MIQ_MAX_CHAT_HISTORY_BYTES` within the built-in 32 KiB–1 MiB safety range. Messages are never partially truncated: an individually oversized message is omitted as a whole without evicting the previously stored history, while ordinary aggregate overflow removes the oldest whole messages first. Every newly stored message has a stable ID and Unix-millisecond UTC `createdAt` value. The browser displays a compact local time in each bubble and renders exact history as `Today`, `Yesterday`, a local weekday for the previous seven days, or a full local date; those dividers are not saved or synced. While the user scrolls, a matching floating date pill shows the same visible divider label and fades after scrolling stops. The first chatbox opening after each page load snaps immediately to the newest message without animation; subsequent openings restore the position captured when the chatbox closed. Chat sparkline grids, axes, and labels are normalized to the current theme on creation, restoration, and live theme changes. Messages migrated from history created before per-message timestamps are grouped under `Earlier` without a fabricated time because their original creation instants cannot be recovered.

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

The mu-plugin `blog/wp-content/mu-plugins/miq-main-site-sso.php` maps a main-site account to a WordPress user record. It does not copy or synchronize passwords.

`writeforus.php` sends users through an allowlisted `new-post` SSO target. Email registration, verification, email/password login, and Google login all preserve that target and return the user to the WordPress editor. WordPress first looks for the durable `miq_main_user_id` link and then falls back to the same verified email address, so an existing WordPress profile and its posts are reused. A conflicting link fails closed for administrator review.

New SSO-created profiles are managed by 360MiQ: their public display name and HTTPS avatar synchronize on the next Write for Us sign-in. Existing WordPress profiles keep their WordPress display name. Subscribers are promoted to Contributor; existing Contributors, Authors, Editors, Administrators, and stronger custom roles are never downgraded.

`MIQ_MAIN_SITE_URL` is always the production root. The identical `account_sso.php` copies detect whether they are running at `/account_sso.php` or `/full/account_sso.php` and send a signed `production` or `full` issuer marker to the shared WordPress installation. WordPress verifies that marker and consumes the one-time token from the matching endpoint, so no manual URL switch is needed between production and testing.

## Important production checks

- Use HTTPS everywhere.
- Keep database credentials and Google secrets outside the repository.
- Keep charts, scripts, and drafts private by default.
- Do not store raw market history in saved layouts; the chart engine stores configuration only.
- Keep Pine execution in the existing browser-safe restricted runtime.
- Add SMTP delivery monitoring before relying on verification and reset email.
- Run the included cleanup script daily from cron: `php /path/to/account/cleanup_rate_limits.php`. It removes stale rate-limit rows, expired email/reset/SSO tokens, expired session records, and activity records older than 400 days, and releases expired timed suspensions.
- Run `php /path/to/account/snapshot_community_sentiment.php` daily after midnight UTC. It persists the current rolling 30-day bullish/neutral/bearish counts for every active stock, market, and global context. The same job bounds storage by retaining 211 days of vote events and 400 days of daily snapshots.
- Review the financial-content disclaimer, privacy notice, user-content terms, and moderation policy before publishing community content.

## Default abuse limits

The account layer stores only a SHA-256 hash of each IP/email key. Defaults are configurable through environment variables:

- Login: 20 attempts per IP and 8 per email per 15 minutes.
- Registration: 5 attempts per IP and 3 per email per hour.
- Password reset: 10 requests per IP and 3 per email per hour.
- Verification/reset email delivery: 12 per IP per hour, 3 per recipient per hour, and one message per recipient per 60 seconds.
- WordPress SSO: 20 handoffs per account per hour.
- Chart/Pine writes: 600 per account per hour; explicit versions: 60 per account per hour.
- Community pulse: 30 vote submissions or changes per account per hour.
- Community reports: 10 report submissions per account per hour, with duplicate open reports blocked.
- Notification-device registration/refresh: 300 updates per account per hour, with at most 20 active devices.

Rate-limit failures fail closed and are logged without recording raw IP addresses or email addresses.

## Android WebView Google login

Google Identity Services intentionally does not render its web sign-in button inside an Android WebView. The account page therefore keeps the normal Google button in desktop and mobile browsers, but replaces it with a native handoff link only when the user agent contains the official app's versioned wrapper marker, such as `360MiQAndroid/1.05`. Generic Android WebView markers (`wv` or `Version/4.0`) are deliberately ignored so third-party embedded browsers and older app builds cannot navigate to the app-only handoff endpoint.

The Android OAuth client (`com.miq360` plus the Play App Signing SHA-1) remains configured only in Google Cloud. Do not put its client ID in the website configuration. For the explicit native button, build a `GetSignInWithGoogleOption` with the Web OAuth client ID above; this makes the resulting ID token's audience match the website's `GOOGLE_CLIENT_ID`.

The wrapper must intercept navigation to `account_android_google.php?state=...`, keep that `state` value, and launch Credential Manager. Pass the same value to `GetSignInWithGoogleOption.Builder(WEB_CLIENT_ID).setNonce(state)`. After a successful credential result, URL-encode and POST these form fields back to `https://360miq.com/account_android_google.php` using the same WebView cookie store:

```text
state=<the intercepted state>
credential=<Google ID token>
```

For example, `WebView.postUrl(...)` can submit the UTF-8 `application/x-www-form-urlencoded` bytes. The endpoint consumes the session-bound state once, checks its five-minute expiry, verifies the token issuer, Web client audience, expiry, verified email, and matching nonce, then creates the normal HttpOnly 360MiQ login session and redirects within the WebView. Never place the ID token in a URL or expose it through a JavaScript bridge. A custom URI scheme and an OAuth redirect URI are not needed for this flow.

## API surface

The browser-facing endpoint is `account_api.php`. It supports workspace reads, search/chart/script/idea saves, community pulse counts and votes, reports, and moderator actions. Chart and script assets support paginated listing, exact loading, optimistic-revision conflict detection, rename, duplicate, delete/archive, version listing, and version restore. All write actions require the session CSRF token.

The API is deliberately small so the existing PHP pages can adopt account-backed persistence incrementally without moving market-data endpoints into the account service.
