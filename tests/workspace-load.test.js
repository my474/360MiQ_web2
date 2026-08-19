'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const workspacePage = read('workspace.php');
const workspaceScript = read('assets/js/workspace.js');
const accountApi = read('account_api.php');
const header = read('header.php');

const jqueryIndex = workspacePage.indexOf('jquery.min.js');
const utilsIndex = workspacePage.indexOf('assets/js/Utils.js');
const jqueryUiIndex = workspacePage.indexOf('assets/js/jquery-ui.min.js');
const headerIndex = workspacePage.indexOf("include __DIR__ . '/header.php'");

assert.ok(jqueryIndex >= 0 && jqueryIndex < utilsIndex, 'workspace must load jQuery before Utils.js');
assert.ok(utilsIndex < jqueryUiIndex && jqueryUiIndex < headerIndex, 'workspace dependencies must load before header.php');
assert.match(header, /assets\/js\/account\.js\?v=20260819\.3/);
assert.match(workspacePage, /assets\/js\/workspace\.js\?v=20260819\.2/);
assert.match(workspaceScript, /request\('workspace', \{ defer_quotes: '1' \}, 'GET'\)/);
assert.match(workspaceScript, /request\('workspace_quotes', \{\}, 'GET'\)/);
assert.match(accountApi, /'workspace_quotes'/);
assert.match(accountApi, /session_write_close\(\)/);
assert.match(accountApi, /miq_api_workspace\(\$user, !\$defer_quotes\)/);
assert.match(accountApi, /miq_api_workspace_quote_payload\(\$user_id, \$lists, \$alerts\)/);

console.log('Workspace load and session-lock regression checks passed.');
