'use strict';

var assert = require('assert');
var fs = require('fs');
var path = require('path');
var sync = require('./chatbox-sync');

var state = {
  messages: Array.from({ length: 45 }, function (_, index) {
    return '<li class="is-user"><p>' + index + '</p></li>';
  }),
  stockchatDict: { old: { value: 'old' }, recent: { value: 'recent' } },
  checkboxStates: { ytd_old: true, ytd_recent: false }
};

var normalized = sync.normalizeState(state);
assert.strictEqual(normalized.messages.length, 40, 'chat history keeps at most 40 messages');
assert.strictEqual(normalized.messages[0].includes('5'), true, 'chat history keeps the newest messages');
assert.strictEqual(sync.normalizeState({ stockchatDict: { AAPL_12: {} } }).count, 13, 'chat history infers the next stock result id');

var oversized = sync.fitState({
  messages: Array.from({ length: 40 }, function () { return '<li>' + 'x'.repeat(20000) + '</li>'; }),
  stockchatDict: {},
  checkboxStates: {}
});
assert.ok(sync.serializedByteLength(oversized) <= sync.maxBytes, 'chat history is bounded by the byte cap');
assert.ok(oversized.messages.length < 40, 'old messages are removed when the byte cap is reached');
assert.strictEqual(sync.storageKey, 'chatbotState');

var mainFooter = fs.readFileSync(path.join(__dirname, '..', '..', 'footer.php'), 'utf8');
var blogFooter = fs.readFileSync(path.join(__dirname, '..', '..', 'blog', 'wp-content', 'themes', 'startup-blog', 'footer.php'), 'utf8');
var alternateBlogFooter = fs.readFileSync(path.join(__dirname, '..', '..', 'blog', 'wp-content', 'themes', 'startup-blog', 'footer_blog.php'), 'utf8');
assert.ok(mainFooter.includes('.slice(-40)'), 'main footer keeps the newest 40 messages');
assert.ok(blogFooter.includes("slice'](-0x28)"), 'blog footer keeps the newest 40 messages');
assert.ok(alternateBlogFooter.includes("slice'](-0x28)"), 'alternate blog footer keeps the newest 40 messages');
assert.ok(mainFooter.includes('/assets/js/chatbox-sync.js?v=20260807-1'), 'main footer loads account chat sync');
assert.ok(blogFooter.includes('/assets/js/chatbox-sync.js?v=20260807-1'), 'blog footer loads account chat sync');

console.log('chatbox-sync tests passed');
