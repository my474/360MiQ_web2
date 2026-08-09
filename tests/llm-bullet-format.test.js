'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(path.resolve(__dirname, '..', 'LLM_request.php'), 'utf8');

assert.match(source, /Prefix every bullet with the Unicode bullet character • followed by one space/);
assert.match(source, /if \(\$isSearch === ''\) \{\s*\$text = normalizeBulletMarkers\(\$text\);\s*\}/s);
assert.ok(source.includes(String.raw`return preg_replace('/^([ \t]*)[*+-][ \t]+/mu', '$1• ', (string)$text);`));

const normalizeBulletMarkers = (text) => text.replace(/^([ \t]*)[*+-][ \t]+/gmu, '$1• ');

assert.strictEqual(
    normalizeBulletMarkers('* First\n- Second\n  + Third'),
    '• First\n• Second\n  • Third'
);
assert.strictEqual(
    normalizeBulletMarkers('EPS grew * faster.\n**Bold text**\nPrice is $10.'),
    'EPS grew * faster.\n**Bold text**\nPrice is $10.'
);

console.log('LLM bullet-format tests passed');
