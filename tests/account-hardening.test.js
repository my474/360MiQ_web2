'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const api = read('account_api.php');
const auth = read('account/auth.php');
const bootstrap = read('account/bootstrap.php');
const config = read('account/config.php');
const schema = read('account/schema.sql');
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
