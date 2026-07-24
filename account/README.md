# 360MiQ account feature

The main PHP site now has a first-party account/workspace layer. It is intentionally separate from the WordPress editorial application under `/blog`.

## What is included

- Email/password sign-up with verification and password reset.
- Google Identity Services sign-in hook.
- Secure PHP session cookie and CSRF token helpers.
- Header account dropdown and private workspace page.
- Recent-search syncing.
- Advanced-chart layout autosave for logged-in users.
- Pine script autosave through the existing chart layout engine.
- Watchlist storage and workspace display.
- Community Pulse voting on every page through the shared footer.
- Moderated community idea drafts and public idea feed.
- Moderator queue with publish/reject/hide actions.
- Account settings, data export, Google linking, and account deletion.
- Optional main-site-to-WordPress contributor SSO handoff.

Public browsing does not require an account. Saving, following, voting, submitting, and publishing require an account. Community content is never published directly to the homepage.

## Deployment setup

1. Apply `schema.sql` to the account database. The default table prefix is `miq_`; set `MIQ_ACCOUNT_TABLE_PREFIX` if a different prefix is required.
   Existing installations must resolve any duplicate display names before adding `uq_miq_users_display_name` to the existing users table. Check them with `SELECT LOWER(display_name) AS normalized_name, COUNT(*) AS total FROM miq_users GROUP BY LOWER(display_name) HAVING COUNT(*) > 1;`, then run `ALTER TABLE miq_users ADD UNIQUE KEY uq_miq_users_display_name (display_name);` using the configured table prefix.
2. Configure a dedicated account database include with `ACCOUNT_DB_INCLUDE`, or provide `ACCOUNT_DB_HOST`, `ACCOUNT_DB_NAME`, `ACCOUNT_DB_USER`, `ACCOUNT_DB_PASSWORD`, and optional `ACCOUNT_DB_PORT`.
3. Set `MIQ_SITE_URL=https://360miq.com` and `ACCOUNT_EMAIL_FROM` to a sender that the host can deliver.
4. Configure email delivery. The current implementation uses PHP `mail()`; production should route that function through the host's authenticated SMTP configuration.
5. For Google login, create a production Web OAuth client in Google Cloud, configure the exact 360MiQ origin, set `GOOGLE_CLIENT_ID`, and enable the Google Identity Services client library. The backend verifies the returned ID token through Google's tokeninfo endpoint. A mature Google API client can replace that verification implementation if desired.
6. Set `MIQ_ACCOUNT_DEBUG=false` in production.

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
- Review the financial-content disclaimer, privacy notice, user-content terms, and moderation policy before publishing community content.

## API surface

The browser-facing endpoint is `account_api.php`. It supports workspace reads, search/chart/script/idea saves, community pulse counts and votes, reports, and moderator actions. All write actions require the session CSRF token.

The API is deliberately small so the existing PHP pages can adopt account-backed persistence incrementally without moving market-data endpoints into the account service.
