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
assert.strictEqual(normalized.messages[0].html.includes('5'), true, 'chat history keeps the newest messages');
assert.strictEqual(typeof normalized.messages[0].id, 'string', 'legacy messages receive stable IDs');
assert.strictEqual(typeof normalized.messages[0].createdAt, 'number', 'legacy messages receive UTC timestamps');
assert.strictEqual(normalized.messages[0].role, 'user', 'message roles are inferred from legacy HTML');
assert.ok(normalized.messages[0].html.includes('data-chat-created-at'), 'stored HTML carries timestamp metadata for the blog runtime');
var migratedLegacyPlan = sync.dateDividerPlan(normalized.messages, Date.now());
assert.strictEqual(migratedLegacyPlan.length, 1, 'an actual pre-timestamp history migration renders as one estimated group');
assert.strictEqual(migratedLegacyPlan[0].label, 'Earlier', 'an actual pre-timestamp history migration never claims Today or Yesterday');
var normalizedAgain = sync.normalizeState(normalized);
assert.strictEqual(normalizedAgain.messages[0].id, normalized.messages[0].id, 'message IDs survive repeated normalization');
assert.strictEqual(normalizedAgain.messages[0].createdAt, normalized.messages[0].createdAt, 'message timestamps survive repeated normalization');
var metadataOnlyHtml = sync.normalizeState({ messages: [normalized.messages[0].html] });
assert.strictEqual(metadataOnlyHtml.messages[0].id, normalized.messages[0].id, 'blog HTML metadata restores the same message ID');
assert.strictEqual(metadataOnlyHtml.messages[0].createdAt, normalized.messages[0].createdAt, 'blog HTML metadata restores the same UTC timestamp');
assert.strictEqual(sync.normalizeState({ stockchatDict: { AAPL_12: {} } }).count, 13, 'chat history infers the next stock result id');

var utcTimestamp = Date.UTC(2026, 7, 9, 12, 34, 0);
var timestamped = sync.normalizeState({
  savedAt: utcTimestamp + 1000,
  messages: [{
    id: 'chat-test-message',
    role: 'assistant',
    html: '<li class="is-ai"><div class="chatbot__message">Answer</div></li>',
    createdAt: utcTimestamp
  }]
});
assert.strictEqual(timestamped.messages[0].createdAt, utcTimestamp, 'explicit UTC timestamps are preserved');
assert.strictEqual(timestamped.messages[0].id, 'chat-test-message', 'explicit message IDs are preserved');
assert.strictEqual(sync.utcIso(utcTimestamp), '2026-08-09T12:34:00.000Z', 'UTC timestamps serialize as ISO Z time');
var compactTimestamp = sync.formatLocalTimestamp(utcTimestamp, 'en-US', 'UTC');
assert.ok(/12:34/.test(compactTimestamp), 'UTC timestamps format as compact local times');
assert.ok(!compactTimestamp.includes('2026'), 'individual message timestamps do not repeat the date');

var localToday = new Date(2026, 7, 10, 12, 0, 0).getTime();
var localYesterdayMorning = new Date(2026, 7, 9, 9, 0, 0).getTime();
var localYesterdayEvening = new Date(2026, 7, 9, 20, 0, 0).getTime();
var localOlderDay = new Date(2026, 7, 7, 12, 0, 0).getTime();
assert.strictEqual(sync.formatLocalDateLabel(localToday, localToday), 'Today', 'current local date uses a Today divider');
assert.strictEqual(sync.formatLocalDateLabel(localYesterdayMorning, localToday), 'Yesterday', 'previous local date uses a Yesterday divider');
assert.strictEqual(sync.localDateKey(localToday), '2026-08-10', 'local date grouping uses the viewing device calendar');
for (var weekdayDate = 2; weekdayDate <= 8; weekdayDate += 1) {
  var weekdayTimestamp = new Date(2026, 7, weekdayDate, 12, 0, 0).getTime();
  var twoDaysLater = new Date(2026, 7, weekdayDate + 2, 12, 0, 0).getTime();
  var expectedWeekday = new Intl.DateTimeFormat(undefined, { weekday: 'long' }).format(new Date(weekdayTimestamp));
  assert.strictEqual(sync.formatLocalDateLabel(weekdayTimestamp, twoDaysLater), expectedWeekday, expectedWeekday + ' displays for an exact recent timestamp');
}
var localOlderThanWeek = new Date(2026, 7, 2, 12, 0, 0).getTime();
assert.ok(sync.formatLocalDateLabel(localOlderThanWeek, localToday).includes('2026'), 'exact timestamps older than seven days display the local date');
var dividerPlan = sync.dateDividerPlan([
  { id: 'chat-older', createdAt: localOlderDay },
  { id: 'chat-yesterday-1', createdAt: localYesterdayMorning },
  { id: 'chat-yesterday-2', createdAt: localYesterdayEvening },
  { id: 'chat-today', createdAt: localToday }
], localToday);
assert.deepStrictEqual(dividerPlan.map(function (divider) { return divider.beforeIndex; }), [0, 1, 3], 'one render-only divider is inserted at each local day boundary');
assert.strictEqual(dividerPlan[1].label, 'Yesterday', 'same-day messages share one Yesterday divider');
assert.strictEqual(dividerPlan[2].label, 'Today', 'newest day is labeled Today');
var legacyDividerPlan = sync.dateDividerPlan([
  { id: 'legacy-july-message-1', createdAt: localYesterdayMorning },
  { id: 'legacy-july-message-2', createdAt: localToday }
], localToday);
assert.strictEqual(sync.isEstimatedTimestampMessage('legacy-july-message-1'), true, 'legacy IDs identify messages whose original timestamp was unavailable');
assert.strictEqual(sync.isEstimatedTimestampMessage('chat-current-message'), false, 'new messages retain exact timestamps');
assert.strictEqual(legacyDividerPlan.length, 1, 'synthetic legacy dates are not split into misleading calendar groups');
assert.strictEqual(legacyDividerPlan[0].label, 'Earlier', 'legacy history uses an honest non-date label');
assert.strictEqual(legacyDividerPlan[0].estimated, true, 'legacy divider records its estimated timestamp status');
var scrollPillDividers = [
  { top: 80, label: 'Friday' },
  { top: 220, label: 'Today' }
];
assert.strictEqual(sync.scrollDateLabel(scrollPillDividers, 60), 'Friday', 'the floating pill starts with the first stamped date');
assert.strictEqual(sync.scrollDateLabel(scrollPillDividers, 100), 'Friday', 'the floating pill keeps the stamped date currently at the top');
assert.strictEqual(sync.scrollDateLabel(scrollPillDividers, 230), 'Today', 'the floating pill changes when the next stamped date reaches the top');
var openingScroller = { scrollHeight: 900, scrollTop: 0, style: {} };
assert.strictEqual(sync.scrollChatToBottom(openingScroller), 900, 'opening a chat snaps directly to its newest content');
assert.strictEqual(openingScroller.scrollTop, openingScroller.scrollHeight, 'the open position is the absolute bottom');
assert.strictEqual(openingScroller.style.scrollBehavior, 'auto', 'the bottom snap never animates through the history');

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
assert.ok(mainFooter.includes('src="assets/js/chatbox-sync.js?v=20260810-5"'), 'main footer uses document-relative chat sync for root and subfolder deployments');
assert.ok(mainFooter.includes('src="assets/js/chatbox-runtime.js?v=20260731-2"'), 'main footer uses a document-relative supporting runtime');
assert.ok(!mainFooter.includes('src="/assets/js/chatbox-sync.js'), 'main footer does not escape a subfolder deployment');
assert.ok(blogFooter.includes('src="/assets/js/chatbox-sync.js?v=20260810-5"'), 'production blog loads chat sync from the main-site root');
assert.ok(alternateBlogFooter.includes('src="/assets/js/chatbox-sync.js?v=20260810-5"'), 'alternate production blog footer loads chat sync from the main-site root');
assert.ok(blogFooter.includes("'apiUrl' => '/account_api.php'"), 'production blog syncs through the main-site account API');
assert.ok(alternateBlogFooter.includes("'apiUrl' => '/account_api.php'"), 'alternate production blog footer syncs through the main-site account API');
assert.ok(mainFooter.includes('window.MiqChatboxSync.captureMessages()'), 'main footer captures structured timestamped messages');
assert.ok(mainFooter.includes('window.MiqChatboxSync.renderMessages(messages, messagesEl)'), 'main footer renders timestamps in local device time');
assert.ok(mainFooter.includes('window.MiqChatboxSync.scrollOpenChatToBottom($chatbotMessageWindow)'), 'main chat openings explicitly snap to the latest message');
assert.ok(!mainFooter.includes('restoreChatboxScroll(chatScrollState)'), 'main chat openings no longer restore an older reading position');

var accountApi = fs.readFileSync(path.join(__dirname, '..', '..', 'account_api.php'), 'utf8');
assert.ok(accountApi.includes("'createdAt' => $created_at"), 'account sync preserves each message UTC timestamp');
assert.ok(accountApi.includes("'id' => $message_id"), 'account sync preserves stable message IDs');
var syncSource = fs.readFileSync(path.join(__dirname, 'chatbox-sync.js'), 'utf8');
assert.ok(syncSource.includes('color:inherit'), 'timestamp text follows light, dark, and live theme colors');
assert.ok(syncSource.includes('display:block;float:none;clear:both;width:100%'), 'single- and multi-line message times use the same bottom row');
assert.ok(syncSource.includes('SCROLL_DATE_PILL_TOP_GAP = 14'), 'the floating pill has breathing room below the chat header');
assert.ok(syncSource.includes('[data-theme="light"] .chatbot__date-divider'), 'date dividers support an initial light-mode load');
assert.ok(syncSource.includes('[data-theme="dark"] .chatbot__date-divider'), 'date dividers support an initial dark-mode load');
assert.ok(syncSource.includes("addEventListener('themechange', decorateOpenChat)"), 'date dividers refresh during live light-dark-light theme toggles');
assert.ok(syncSource.includes("label.className = MESSAGE_DATE_DIVIDER_CLASS + ' ' + SCROLL_DATE_PILL_LABEL_CLASS"), 'the scroll pill reuses the exact current date-stamp styling in every theme');
assert.ok(syncSource.includes("scroller.addEventListener('wheel', markUserScrollIntent"), 'mouse and trackpad scrolling can reveal the date pill');
assert.ok(syncSource.includes("scroller.addEventListener('touchstart', onTouchStart"), 'touch scrolling can reveal the date pill');
assert.ok(syncSource.includes("scroller.addEventListener('pointerdown', onPointerDown"), 'scrollbar dragging can reveal the date pill');
assert.ok(syncSource.includes("scroller.addEventListener('scroll', onUserScroll"), 'the date pill follows the visible stamped date while the user scrolls');
assert.ok(syncSource.includes('setTimeout(hidePill, SCROLL_DATE_PILL_HIDE_DELAY)'), 'the date pill fades after scrolling stops');
assert.ok(syncSource.includes('@media (prefers-reduced-motion:reduce)'), 'the date pill respects reduced-motion preferences');
assert.ok(syncSource.includes("observer.observe(chatbot, { attributes: true, attributeFilter: ['class'] })"), 'main and blog chat openings are detected by the shared runtime');
assert.ok(syncSource.includes('scrollChatToBottom(element)'), 'open positioning uses a direct non-animated bottom snap');
assert.ok(syncSource.includes('__miqSuppressScrollPillUntil'), 'the open snap cannot reveal the user-scroll date pill');
assert.ok(syncSource.includes('renderDateDividers(element)'), 'restored account and local histories render date dividers');
assert.ok(syncSource.includes('if (metadata.timestampEstimated)'), 'fabricated times are hidden for migrated legacy messages');
assert.ok(syncSource.includes('root.saveChatState = saveOpenChatState'), 'blog legacy persistence is upgraded by the shared runtime');

var meta = fs.readFileSync(path.join(__dirname, '..', '..', 'meta.php'), 'utf8');
assert.ok(meta.includes("'apiUrl' => 'account_api.php'"), 'main-site API URL is document-relative');
assert.ok(meta.includes("'accountUrl' => 'account.php'"), 'main-site account URL is document-relative');

console.log('chatbox-sync tests passed');
