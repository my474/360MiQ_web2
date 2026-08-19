'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const accountScript = read('assets/js/account.js');
const accountApi = read('account_api.php');

assert.match(accountScript, /function saveSearch\(code, metadata, options\)/);
assert.match(accountScript, /options\.preserveTimestamp && metadata\.searched_at/);
assert.match(accountScript, /Object\.assign\(\{\}, item, \{ preserve_searched_at: true \}\)/);
assert.match(accountScript, /if \(!item \|\| !hasSearchTimestamp\(item\.searched_at\)\) return;/);
assert.match(accountScript, /saveSearch\(item\.code, item, \{ preserveTimestamp: true \}\)/);
assert.match(accountApi, /preserve_searched_at/);
assert.match(accountApi, /ON DUPLICATE KEY UPDATE exchange = VALUES\(exchange\), display_name = VALUES\(display_name\)/);

console.log('Recent-search timestamp synchronization regression checks passed.');
