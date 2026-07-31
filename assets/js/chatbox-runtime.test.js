'use strict';

var assert = require('assert');
var fs = require('fs');
var path = require('path');
var runtime = require('./chatbox-runtime');

var resolverCases = [
  ['is mmm pe too high?', 'mmm'],
  ["What is AAPL's EPS?", 'AAPL'],
  ['how is brk.b doing?', 'brk.b'],
  ['$TSLA valuation', 'TSLA'],
  ['ticker PE outlook', 'PE'],
  ['How is NVIDIA doing?', 'NVIDIA'],
  ['Is Tesla overvalued?', 'Tesla'],
  ['show Apple earnings', 'Apple'],
  ['tell me about meta', 'meta'],
  ['7203 TYO', '7203'],
  ['is the pe too high?', ''],
  ['what is the p/e ratio?', ''],
  ['WHAT IS THE P/E RATIO?', ''],
  ['what about it?', '']
];

resolverCases.forEach(function (testCase) {
  assert.strictEqual(
    runtime.findLikelyStockCandidate(testCase[0]),
    testCase[1],
    testCase[0]
  );
});

assert.strictEqual(runtime.isIgnoredStockTerm('PE'), true);
assert.strictEqual(runtime.isIgnoredStockTerm('EPS'), true);
assert.strictEqual(runtime.isIgnoredStockTerm('MMM'), false);

var scroller = { scrollHeight: 700, clientHeight: 200, scrollTop: 120 };
var savedPosition = runtime.captureScrollState(scroller);
scroller.scrollHeight = 900;
runtime.restoreScrollState(scroller, savedPosition);
assert.strictEqual(scroller.scrollTop, 120, 'restores a reader\'s exact position');

scroller = { scrollHeight: 700, clientHeight: 200, scrollTop: 500 };
savedPosition = runtime.captureScrollState(scroller);
scroller.scrollHeight = 900;
runtime.restoreScrollState(scroller, savedPosition);
assert.strictEqual(scroller.scrollTop, 700, 'keeps a bottom-anchored conversation at the bottom');

var footer = fs.readFileSync(path.join(__dirname, '..', '..', 'footer.php'), 'utf8');
var inlineScript = footer.match(/<script id="rendered-js" >([\s\S]*?)<\/script>/);
assert.ok(inlineScript, 'chatbox inline script is present');
assert.ok(
  footer.indexOf('<script src="/assets/js/chatbox-runtime.js?v=20260731-2"></script>') < footer.indexOf('<script id="rendered-js" >'),
  'chatbox runtime loads before the inline integration'
);
assert.ok(
  inlineScript[1].indexOf("if (localStockCandidate != '')") < inlineScript[1].indexOf('const protectedText'),
  'a strong local stock candidate bypasses the noun-based NLP fallback'
);
assert.ok(
  inlineScript[1].includes('setTimeout(() => {\n    focusChatbotInput();\n}, 100);'),
  'delayed input focus uses the scroll-safe helper'
);
assert.doesNotThrow(function () {
  new Function(inlineScript[1]);
}, 'chatbox inline integration has valid JavaScript syntax');

console.log('chatbox-runtime tests passed');
