'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const api = read('account_api.php');
const accountPage = read('account.php');
const auth = read('account/auth.php');
const bootstrap = read('account/bootstrap.php');
const config = read('account/config.php');
const mainSso = read('account_sso.php');
const schema = read('account/schema.sql');
const wordpressSso = read('blog/wp-content/mu-plugins/miq-main-site-sso.php');
const writeForUs = read('writeforus.php');
const chartPage = read('assets/js/pages/tool-stock-chart.js');
const chartEngine = read('assets/js/stock-chart-engine/stock-chart-engine.js');
const pineRuntime = read('assets/js/stock-chart-engine/pine-script-runtime.js');
const workspaceScript = read('assets/js/workspace.js');
const migrationNames = fs.readdirSync(path.join(root, 'account/migrations'))
    .filter((name) => name.endsWith('.sql'));
const migrations = migrationNames
    .filter((name) => name !== '20260726_add_foreign_keys.sql')
    .map((name) => read(path.join('account/migrations', name)))
    .join('\n');
const foreignKeyMigration = read('account/migrations/20260726_add_foreign_keys.sql');

assert.match(api, /This action requires POST\./);
assert.match(api, /miq_api_require_post_csrf\(\$body\)/);
assert.match(api, /max_api_request_bytes/);
assert.match(api, /'moderate_reply'/);
assert.match(api, /'pending'.*UTC_TIMESTAMP\(\), UTC_TIMESTAMP\(\)/s);
assert.match(api, /function miq_api_workspace_asset_key/);
assert.match(api, /ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID\(id\)/);
assert.match(api, /function miq_api_enforce_asset_storage/);
assert.match(api, /miq_api_require_asset_write\(\$user_id, \$will_create_version\)/);
assert.match(api, /SET status = \?, visibility = 'private', revision = \?/);
assert.match(config, /MIQ_MAX_ASSET_STORAGE_BYTES/);
assert.match(config, /MIQ_RATE_ASSET_WRITE_LIMIT/);
assert.match(config, /MIQ_RATE_ASSET_VERSION_LIMIT/);
assert.match(config, /MIQ_RATE_SSO_USER_LIMIT/);
assert.match(workspaceScript, /archive_script', \{ id: scriptId, expected_revision:/);
assert.match(workspaceScript, /unarchive_script', \{ id: scriptId, expected_revision:/);
assert.doesNotMatch(chartPage, /\[file,\s*'\/'\s*\+\s*file\]/);
assert.doesNotMatch(chartPage, /function legacySyncPineScripts/);
assert.match(chartPage, /delete indicator\.accountScript/);
assert.match(chartPage, /function flushChartCloudSave\(targetSync\)/);
assert.match(chartEngine, /includePineSource:\s*false/);
assert.match(chartEngine, /delete indicator\.accountScript/);
assert.match(pineRuntime, /var MAX_SOURCE_LENGTH = 100000/);
assert.match(pineRuntime, /var MAX_COLLECTION_SIZE = 10000/);
assert.match(auth, /preg_match\('\/\[\\x00-\\x1F\\x7F\\\\\\\\\]\//);
assert.match(auth, /length > 1024/);
assert.doesNotMatch(bootstrap, /miq_account_bootstrap\(\);/);
assert.match(accountPage, /function miq_account_token_link\(\$kind, \$token, \$return_to = 'workspace'\)/);
assert.match(accountPage, /'&return_to=' \. rawurlencode\(\$safe_return_to\)/);
assert.match(accountPage, /miq_account_send_verification_for_user\(\$user, \$return_to\)/);
assert.match(accountPage, /Public display name/);
const accountJqueryPosition = accountPage.indexOf('ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js');
const accountHeaderPosition = accountPage.indexOf("include __DIR__ . '/header.php'");
assert(accountJqueryPosition >= 0, 'The account page must load jQuery for the shared chat widget');
assert(accountJqueryPosition < accountHeaderPosition, 'jQuery must load before the shared header and footer scripts');
assert.match(mainSso, /function miq_sso_targets\(\)/);
assert.match(mainSso, /'new-post' => '\/blog\/wp-admin\/post-new\.php'/);
assert.match(mainSso, /\$handoff = 'account_sso\.php\?target='/);
assert.doesNotMatch(mainSso, /\$handoff = 'account_sso\.php\?return_to='/);
assert.match(mainSso, /DELETE FROM \{\$tokens\} WHERE user_id = \? AND consumed_at IS NULL/);
assert.match(mainSso, /\$_SERVER\['HTTP_X_MIQ_SSO_SECRET'\].*:\s*''/);
assert.doesNotMatch(mainSso, /\$_POST\['secret'\]/);
assert.match(mainSso, /u\.status = 'active'.*FOR UPDATE/s);
assert.match(mainSso, /function miq_sso_issuer\(\)/);
assert.match(mainSso, /preg_match\('#\^\/full\(\?:\/\|\$\)#/);
assert.match(mainSso, /hash_hmac\('sha256'.*\$issuer.*\\n.*\$token/s);
assert.match(mainSso, /'issuer_sig' => miq_sso_issuer_signature\(\$issuer, \$token\)/);
assert.match(wordpressSso, /function miq_main_site_sso_linked_user/);
assert.match(wordpressSso, /miq_main_user_id/);
assert.match(wordpressSso, /already linked to another 360MiQ account/);
assert.match(wordpressSso, /miq_sso_managed_profile/);
assert.match(wordpressSso, /wp_update_user\(array\('ID' => \$user->ID, 'display_name' => \$public_name\)\)/);
assert.match(wordpressSso, /add_filter\('get_avatar_url', 'miq_main_site_sso_avatar_url'/);
assert.match(wordpressSso, /if \(!\$user->has_cap\('edit_posts'\)\)/);
assert.doesNotMatch(wordpressSso, /!\$user->has_cap\('manage_options'\).*!\$user->has_cap\('edit_others_posts'\)/s);
assert.match(wordpressSso, /in_array\(\$issuer, array\('production', 'full'\), true\)/);
assert.match(wordpressSso, /hash_equals\(\$expected, \$provided\)/);
assert.match(wordpressSso, /if \(\$issuer === 'full'\) \{\s*\$url \.= '\/full';/s);
assert.match(wordpressSso, /miq_main_site_sso_main_site_url\(\$issuer\) \. '\/account_sso\.php\?mode=consume'/);
assert.match(wordpressSso, /'redirection' => 0/);
assert.match(wordpressSso, /'sslverify' => true/);
assert.match(writeForUs, /account_sso\.php\?target=new-post/);
assert.match(writeForUs, /account_sso\.php\?target=new-post&signup=1/);
assert.match(writeForUs, /there is no separate WordPress registration or password to manage/);
assert.match(writeForUs, /Existing WordPress contributors:/);
assert.doesNotMatch(schema, /^\s*CONSTRAINT\s+/mi);
assert.doesNotMatch(migrations, /^\s*CONSTRAINT\s+/mi);
assert.strictEqual((foreignKeyMigration.match(/ADD CONSTRAINT/g) || []).length, 40);
assert.strictEqual((foreignKeyMigration.match(/CONSTRAINT_TYPE = 'FOREIGN KEY'/g) || []).length, 40);
assert.strictEqual((foreignKeyMigration.match(/^PREPARE miq_fk_stmt/gm) || []).length, 40);
assert.strictEqual((foreignKeyMigration.match(/^EXECUTE miq_fk_stmt/gm) || []).length, 0);
assert.strictEqual((foreignKeyMigration.match(/ EXECUTE miq_fk_stmt;/g) || []).length, 40);
assert.strictEqual((foreignKeyMigration.match(/DEALLOCATE PREPARE miq_fk_stmt;/g) || []).length, 40);
assert.strictEqual((foreignKeyMigration.match(
    /SET @miq_fk_sql = IF\([\s\S]*?\);\r?\nPREPARE miq_fk_stmt FROM @miq_fk_sql; EXECUTE miq_fk_stmt; DEALLOCATE PREPARE miq_fk_stmt;/g
) || []).length, 40);
assert.match(foreignKeyMigration, /fk_miq_users_suspender/);
assert.match(foreignKeyMigration, /fk_miq_sso_user/);

const schemaTables = new Map();
for (const tableMatch of schema.matchAll(/CREATE TABLE IF NOT EXISTS\s+(miq_\w+)\s*\(([\s\S]*?)\) ENGINE=/g)) {
    schemaTables.set(
        tableMatch[1],
        new Set(Array.from(tableMatch[2].matchAll(/^\s*([a-zA-Z_]\w*)\s+/gm), (columnMatch) => columnMatch[1]))
    );
}
const foreignKeyDefinitions = Array.from(foreignKeyMigration.matchAll(
    /ALTER TABLE `([^`]+)` ADD CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)/g
));
assert.strictEqual(foreignKeyDefinitions.length, 40);
for (const definition of foreignKeyDefinitions) {
    const [, childTable, constraintName, childColumn, parentTable, parentColumn] = definition;
    assert(schemaTables.has(childTable), `${constraintName} has an unknown child table`);
    assert(schemaTables.get(childTable).has(childColumn), `${constraintName} has an unknown child column`);
    assert(schemaTables.has(parentTable), `${constraintName} has an unknown parent table`);
    assert(schemaTables.get(parentTable).has(parentColumn), `${constraintName} has an unknown parent column`);
}
assert.match(read('account/check_price_alerts.php'), /code > \?/);
assert.match(read('account/cleanup_rate_limits.php'), /password_reset_tokens/);
assert.match(read('account/lifecycle.php'), /miq_account_delete_user_data/);
assert.doesNotMatch(api, /â€œ|â€|Ã¢|Â·/);

console.log('Account hardening regression checks passed.');
