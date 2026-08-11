'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const auth = read('account/auth.php');
const config = read('account/config.php');
const accountReadme = read('account/README.md');

assert.match(config, /MIQ_ACCOUNT_SESSION_SAVE_PATH/);
assert.match(auth, /function miq_account_prepare_private_session_path\(\$config\)/);
assert.match(auth, /refused to store account sessions inside the public document root/);
assert.match(auth, /@mkdir\(\$candidate, 0700\)/);
assert.match(auth, /private account session directory must use owner-only permissions/);

assert.match(auth, /function miq_account_migrate_session_file\(/);
assert.match(auth, /\^\[a-zA-Z0-9,-\]\{16,256\}\$\/D/);
assert.match(auth, /flock\(\$input, LOCK_SH\)/);
assert.match(auth, /\.migrate\.' \. bin2hex\(random_bytes\(8\)\)/);
assert.match(auth, /@fopen\(\$temporary, 'x\+b'\)/);
assert.match(auth, /@link\(\$temporary, \$destination\)/);

assert.match(auth, /ini_set\('session\.save_path', \$private_path\)/);
assert.match(auth, /ini_set\('session\.gc_probability', '1'\)/);
assert.match(auth, /ini_set\('session\.gc_divisor', '100'\)/);
assert.ok(
    auth.indexOf('miq_account_use_private_session_storage($config);') < auth.indexOf('session_start();'),
    'private session storage must be active before PHP opens the session'
);
assert.match(accountReadme, /MIQ_ACCOUNT_SESSION_SAVE_PATH/);
assert.match(accountReadme, /preventing cPanel's shared-session cleanup from logging out idle users/);

console.log('Account private-session storage regression checks passed.');
