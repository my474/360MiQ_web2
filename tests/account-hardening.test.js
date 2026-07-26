'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const api = read('account_api.php');
const auth = read('account/auth.php');
const bootstrap = read('account/bootstrap.php');
const schema = read('account/schema.sql');
const migrations = fs.readdirSync(path.join(root, 'account/migrations'))
    .filter((name) => name.endsWith('.sql'))
    .map((name) => read(path.join('account/migrations', name)))
    .join('\n');

assert.match(api, /This action requires POST\./);
assert.match(api, /miq_api_require_post_csrf\(\$body\)/);
assert.match(api, /max_api_request_bytes/);
assert.match(api, /'moderate_reply'/);
assert.match(api, /'pending'.*UTC_TIMESTAMP\(\), UTC_TIMESTAMP\(\)/s);
assert.match(auth, /preg_match\('\/\[\\x00-\\x1F\\x7F\\\\\\\\\]\//);
assert.match(auth, /length > 1024/);
assert.doesNotMatch(bootstrap, /miq_account_bootstrap\(\);/);
assert.doesNotMatch(schema, /^\s*CONSTRAINT\s+/mi);
assert.doesNotMatch(migrations, /^\s*CONSTRAINT\s+/mi);
assert.match(read('account/check_price_alerts.php'), /code > \?/);
assert.match(read('account/cleanup_rate_limits.php'), /password_reset_tokens/);
assert.match(read('account/lifecycle.php'), /miq_account_delete_user_data/);
assert.doesNotMatch(api, /â€œ|â€|Ã¢|Â·/);

console.log('Account hardening regression checks passed.');
