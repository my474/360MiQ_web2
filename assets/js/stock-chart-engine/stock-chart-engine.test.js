const assert = require('assert');
const StockChartEngine = require('./stock-chart-engine.js');
const PineScriptRuntime = require('./pine-script-runtime.js');

class FakeClassList {
  constructor(element) {
    this.element = element;
  }

  add(name) {
    const parts = this.element.className ? this.element.className.split(/\s+/) : [];
    if (!parts.includes(name)) parts.push(name);
    this.element.className = parts.join(' ');
  }

  remove(name) {
    this.element.className = (this.element.className || '').split(/\s+/).filter((part) => part && part !== name).join(' ');
  }

  toggle(name, force) {
    const parts = this.element.className ? this.element.className.split(/\s+/).filter(Boolean) : [];
    const hasClass = parts.includes(name);
    const shouldAdd = force === undefined ? !hasClass : Boolean(force);
    if (shouldAdd && !hasClass) parts.push(name);
    if (!shouldAdd && hasClass) {
      const index = parts.indexOf(name);
      parts.splice(index, 1);
    }
    this.element.className = parts.join(' ');
    return shouldAdd;
  }
}

class FakeStyle {
  setProperty(name, value) {
    this[name] = value;
  }
}

class FakeElement {
  constructor(tagName) {
    this.tagName = tagName;
    this.children = [];
    this.attributes = {};
    this.style = new FakeStyle();
    this.className = '';
    this.classList = new FakeClassList(this);
    this.listeners = {};
    this.parentNode = null;
    this.textContent = '';
    this.dataset = {};
  }

  set innerHTML(value) {
    this._innerHTML = value;
    this.children = [];
    if (value && value.indexOf('data-sce-stock-info-link') !== -1) {
      const stockInfoLink = new FakeElement('a');
      stockInfoLink.className = 'sce-title sce-stock-info-link';
      stockInfoLink.setAttribute('data-sce-stock-info-link', '');
      stockInfoLink.setAttribute('href', '#');
      stockInfoLink.setAttribute('hidden', 'hidden');
      this.appendChild(stockInfoLink);
    } else if (value && value.indexOf('sce-title') !== -1) {
      const title = new FakeElement('span');
      title.className = 'sce-title';
      this.appendChild(title);
    }
  }

  get innerHTML() {
    return this._innerHTML || '';
  }

  get clientWidth() {
    return parseInt(this.style.width, 10) || 900;
  }

  get clientHeight() {
    return parseInt(this.style.height, 10) || 560;
  }

  appendChild(child) {
    child.parentNode = this;
    this.children.push(child);
    return child;
  }

  setAttribute(name, value) {
    this.attributes[name] = String(value);
  }

  removeAttribute(name) {
    delete this.attributes[name];
  }

  hasAttribute(name) {
    return Object.prototype.hasOwnProperty.call(this.attributes, name);
  }

  getAttribute(name) {
    return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null;
  }

  querySelector(selector) {
    if (selector.charAt(0) === '.') {
      const className = selector.slice(1);
      return findElement(this, (element) => (element.className || '').split(/\s+/).includes(className));
    }
    const attrMatch = selector.match(/^\[([^=\]]+)(?:="([^"]*)")?\]$/);
    if (attrMatch) {
      return findElement(this, (element) => {
        const value = element.getAttribute(attrMatch[1]);
        return attrMatch[2] == null ? value != null : value === attrMatch[2];
      });
    }
    return null;
  }

  contains(target) {
    while (target) {
      if (target === this) return true;
      target = target.parentNode;
    }
    return false;
  }

  addEventListener(type, handler) {
    if (!this.listeners[type]) this.listeners[type] = [];
    this.listeners[type].push(handler);
  }

  removeEventListener(type, handler) {
    this.listeners[type] = (this.listeners[type] || []).filter((item) => item !== handler);
  }

  dispatchEvent(event) {
    (this.listeners[event.type] || []).forEach((handler) => handler(event));
  }

  focus() {}

  getBoundingClientRect() {
    return { left: 0, top: 0, width: 900, height: 560 };
  }
}

class FakeCanvas extends FakeElement {
  constructor() {
    super('canvas');
    this.commands = [];
    this.exportCalls = [];
  }

  getContext() {
    const canvas = this;
    const ctx = {
      _alphaStack: [],
      globalAlpha: 1,
      fillStyle: '#000000',
      strokeStyle: '#000000',
      beginPath() {},
      clearRect() {},
      closePath() {},
      fill() {
        canvas.commands.push({ type: 'fill', alpha: this.globalAlpha });
      },
      fillRect(x, y, width, height) {
        canvas.commands.push({ type: 'fillRect', x, y, width, height, alpha: this.globalAlpha, fillStyle: this.fillStyle });
      },
      fillText(text, x, y) {
        canvas.commands.push({ type: 'fillText', text: String(text), x, y, alpha: this.globalAlpha, fillStyle: this.fillStyle, font: this.font });
      },
      lineTo(x, y) {
        canvas.commands.push({ type: 'lineTo', x, y, alpha: this.globalAlpha });
      },
      measureText(text) {
        return { width: String(text || '').length * 6 };
      },
      moveTo(x, y) {
        canvas.commands.push({ type: 'moveTo', x, y, alpha: this.globalAlpha });
      },
      rect(x, y, width, height) {
        canvas.commands.push({ type: 'rect', x, y, width, height, alpha: this.globalAlpha });
      },
      quadraticCurveTo(cpx, cpy, x, y) {
        canvas.commands.push({ type: 'quadraticCurveTo', cpx, cpy, x, y, alpha: this.globalAlpha });
      },
      restore() {
        this.globalAlpha = this._alphaStack.length ? this._alphaStack.pop() : 1;
      },
      save() {
        this._alphaStack.push(this.globalAlpha);
      },
      clip() {
        canvas.commands.push({ type: 'clip', alpha: this.globalAlpha });
      },
      setLineDash() {},
      setTransform() {},
      stroke() {
        canvas.commands.push({ type: 'stroke', alpha: this.globalAlpha, strokeStyle: this.strokeStyle, lineWidth: this.lineWidth });
      },
      strokeRect(x, y, width, height) {
        canvas.commands.push({ type: 'strokeRect', x, y, width, height, alpha: this.globalAlpha, strokeStyle: this.strokeStyle });
      }
    };
    return ctx;
  }

  toDataURL(type, quality) {
    this.exportCalls.push({ type, quality });
    return `data:${type || 'image/png'};base64,ZmFrZS1jaGFydA==`;
  }
}

class FakePineWorker {
  constructor(url) {
    this.url = url;
    this.messages = [];
    this.terminated = false;
    this.onmessage = null;
    this.onerror = null;
  }

  postMessage(message) {
    this.messages.push(message);
    const response = PineScriptRuntime.runInWorker(message);
    response.requestId = message.requestId;
    if (this.onmessage) this.onmessage({ data: response });
  }

  terminate() {
    this.terminated = true;
  }
}

function findElement(root, predicate) {
  if (predicate(root)) return root;
  for (const child of root.children || []) {
    const found = findElement(child, predicate);
    if (found) return found;
  }
  return null;
}

function createFakeDom() {
  const container = new FakeElement('div');
  const documentElement = new FakeElement('html');
  global.document = {
    documentElement,
    createElement(tagName) {
      return tagName === 'canvas' ? new FakeCanvas() : new FakeElement(tagName);
    },
    querySelector(selector) {
      return selector === '#chart' ? container : null;
    }
  };
  global.window = {
    devicePixelRatio: 1,
    PineScriptRuntime,
    addEventListener() {},
    removeEventListener() {}
  };
  global.CustomEvent = function CustomEvent(type, options) {
    return { type, detail: options && options.detail };
  };
  const store = {};
  global.localStorage = {
    getItem(key) {
      return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
    },
    setItem(key, value) {
      store[key] = String(value);
    },
    removeItem(key) {
      delete store[key];
    }
  };
  return { container, documentElement };
}

function formatTestNumber(value) {
  const abs = Math.abs(value);
  if (abs >= 1000000000) return `${(value / 1000000000).toFixed(2)}B`;
  if (abs >= 1000000) return `${(value / 1000000).toFixed(2)}M`;
  if (abs >= 1000) return `${(value / 1000).toFixed(2)}K`;
  if (abs < 1 && abs > 0) return value.toFixed(4);
  return value.toFixed(2);
}

function trimTestFixed(value, decimals) {
  const text = Number(value).toFixed(decimals).replace(/\.?0+$/, '');
  return text === '-0' ? '0' : text;
}

function formatTestChangeNumber(value) {
  const abs = Math.abs(value);
  if (abs >= 1000000000) return `${trimTestFixed(value / 1000000000, 2)}B`;
  if (abs >= 1000000) return `${trimTestFixed(value / 1000000, 2)}M`;
  if (abs >= 1000) return `${trimTestFixed(value / 1000, 2)}K`;
  if (abs < 1 && abs > 0) return trimTestFixed(value, 4);
  return trimTestFixed(value, 2);
}

function formatTestDate(time) {
  const date = new Date(time * 1000);
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function formatTestPriceLegend(chart, bar) {
  const previous = chart.previousBarForTime(bar.time);
  const change = previous && previous.close ? bar.close - previous.close : null;
  const percent = previous && previous.close ? (change / previous.close) * 100 : null;
  const suffix = change == null || !Number.isFinite(change) || !Number.isFinite(percent) ? '' : ` ${change >= 0 ? '+' : ''}${formatTestChangeNumber(change)} (${percent >= 0 ? '+' : ''}${percent.toFixed(1)}%)`;
  return `${formatTestDate(bar.time)}  O ${formatTestNumber(bar.open)} H ${formatTestNumber(bar.high)} L ${formatTestNumber(bar.low)} C ${formatTestNumber(bar.close)}${suffix}`;
}

const { documentElement } = createFakeDom();
documentElement.setAttribute('data-theme', 'light');

const data = StockChartEngine.createDemoData(90);
const recentStockSelections = [];
const demoCandleStyles = data.slice(1).reduce((counts, bar, index) => {
  const previousClose = data[index].close;
  if (bar.close >= bar.open && bar.close >= previousClose) counts.greenHollow += 1;
  if (bar.close < bar.open && bar.close >= previousClose) counts.greenFilled += 1;
  if (bar.close >= bar.open && bar.close < previousClose) counts.redHollow += 1;
  if (bar.close < bar.open && bar.close < previousClose) counts.redFilled += 1;
  return counts;
}, { greenHollow: 0, greenFilled: 0, redHollow: 0, redFilled: 0 });
assert.ok(demoCandleStyles.greenHollow > 0);
assert.ok(demoCandleStyles.greenFilled > 0);
assert.ok(demoCandleStyles.redHollow > 0);
assert.ok(demoCandleStyles.redFilled > 0);
const chart = new StockChartEngine.Chart('#chart', {
  data,
  symbol: 'TEST',
  interval: '1D',
  load: false,
  autosave: false,
  recentStocks: [{ code: 'SPY', name_en: 'SPDR S&P 500 ETF Trust' }],
  onRecentStockSelect(stock) {
    recentStockSelections.push(stock);
  }
});
assert.strictEqual(chart.autosaveTimer, null);
const pineSource = `//@version=5
indicator("RSI Average", overlay=false)
r = ta.rsi(close, 14)
plot(r, title="RSI", color=color.orange)
plot(ta.sma(r, 5), title="RSI SMA")
hline(50, title="Mid")`;
const pinePreview = PineScriptRuntime.run(pineSource, data);
assert.strictEqual(pinePreview.metadata.title, 'RSI Average');
assert.strictEqual(pinePreview.plots.length, 2);
assert.ok(pinePreview.outputs.plot1.length > 0);
assert.ok(pinePreview.render.some((item) => item.type === 'level' && item.value === 50));
const inputPreview = PineScriptRuntime.run(`indicator("Input test")
length = input.int(10, "Length", min=2, max=50)
plot(ta.sma(close, length))`, data, { inputs: { length: 5 } });
assert.strictEqual(inputPreview.inputs[0].id, 'length');
assert.strictEqual(inputPreview.inputs[0].value, 5);
const sourcePreview = PineScriptRuntime.run(`indicator("Source test")
source = input.source(close, "Source")
plot(ta.sma(source, 3))`, data);
assert.strictEqual(sourcePreview.inputs[0].type, 'source');
assert.ok(sourcePreview.outputs.plot1.length > 0);
const functionPreview = PineScriptRuntime.run(`indicator("Function test")
smooth(source, length) => ta.sma(source, length)
double(value) => value * 2
plot(double(smooth(close, 3)), title="Double SMA")`, data);
assert.strictEqual(functionPreview.metadata.title, 'Function test');
assert.strictEqual(functionPreview.plots[0].title, 'Double SMA');
assert.ok(functionPreview.outputs.plot1.length > 0);
const indexedFunctionPreview = PineScriptRuntime.run(`indicator("Indexed function")
previous(value) => value[1]
plot(previous(close))`, data);
assert.strictEqual(indexedFunctionPreview.outputs.plot1.length, data.length - 1);
const securityBars = data.filter((bar, index) => index % 3 === 0).map((bar) => ({
  time: bar.time,
  open: bar.open + 100,
  high: bar.high + 100,
  low: bar.low + 100,
  close: bar.close + 100,
  volume: bar.volume
}));
const securityPreview = PineScriptRuntime.run(`indicator("Security test")
benchmark = request.security("SPY", "D", ta.sma(close, 2))
plot(benchmark)`, data, { security: { SPY: securityBars } });
assert.ok(securityPreview.outputs.plot1.length > 0);
assert.strictEqual(securityPreview.securityRequests.length, 0);
const weeklySecurityPreview = PineScriptRuntime.run(`indicator("Weekly security")
plot(request.security("SPY", "W", close))`, data, { security: { SPY: data } });
assert.ok(weeklySecurityPreview.outputs.plot1.length > 0);
const missingSecurityPreview = PineScriptRuntime.run(`indicator("Missing security")
plot(request.security("QQQ", "W", close))`, data);
assert.deepStrictEqual(missingSecurityPreview.securityRequests, [{ symbol: 'QQQ', timeframe: 'W' }]);
const advancedOutputPreview = PineScriptRuntime.run(`indicator("Advanced outputs")
upper = plot(close, title="Upper")
lower = plot(ta.sma(close, 3), title="Lower")
fill(upper, lower, color=color.new(color.blue, 80))
plotshape(close > open, style=shape.triangleup, location=location.belowbar, color=color.green, title="Up")
plotchar(close < open, char="x", location=location.abovebar, color=color.red, title="Down")
bgcolor(close > open ? color.new(color.green, 90) : na)
alertcondition(close > open, title="Up bar", message="Up bar")`, data);
assert.ok(advancedOutputPreview.render.some((item) => item.type === 'fill'));
assert.ok(advancedOutputPreview.render.some((item) => item.type === 'shape'));
assert.ok(advancedOutputPreview.render.some((item) => item.type === 'char'));
assert.ok(advancedOutputPreview.render.some((item) => item.type === 'background'));
assert.strictEqual(advancedOutputPreview.alertConditions[0].title, 'Up bar');
const drawingOutputPreview = PineScriptRuntime.run(`indicator("Drawing outputs")
label.new(bar_index, high, "Latest", style=label.style_label_down)
line.new(bar_index - 3, low, bar_index, high, color=color.red, width=2, style=line.style_dashed)
box.new(bar_index - 5, high, bar_index, low, bgcolor=color.new(color.blue, 90))`, data);
assert.strictEqual(drawingOutputPreview.drawings.length, 3);
assert.deepStrictEqual(drawingOutputPreview.drawings.map((drawing) => drawing.type), ['label', 'line', 'box']);
const workerSecurityPreview = PineScriptRuntime.runInWorker({
  source: `indicator("Worker security")
plot(request.security("SPY", "W", close))`,
  bars: data,
  options: { security: { SPY: data } }
});
assert.strictEqual(workerSecurityPreview.ok, true);
assert.ok(workerSecurityPreview.result.outputs.plot1.length > 0);
assert.ok(PineScriptRuntime.limits.maxOperations >= 100000);
assert.ok(PineScriptRuntime.limits.compileCacheSize >= 16);
const blockFunctionPreview = PineScriptRuntime.run(`indicator("Block function")
signal(source) =>
    doubled = source * 2
    if doubled > 200
        return doubled
    doubled
plot(signal(close), title="Block signal")`, data);
assert.strictEqual(blockFunctionPreview.plots[0].title, 'Block signal');
assert.ok(blockFunctionPreview.outputs.plot1.length > 0);
const loopFunctionPreview = PineScriptRuntime.run(`indicator("Loop function")
total(source) =>
    result = 0
    for offset = 0 to 2
        result := result + source[offset]
    result
plot(total(close), title="Three bar sum")`, data);
assert.strictEqual(loopFunctionPreview.plots[0].title, 'Three bar sum');
assert.ok(loopFunctionPreview.outputs.plot1.length > 0);
assert.throws(() => PineScriptRuntime.run(`indicator("Recursive function")
loop(value) => loop(value)
plot(loop(close))`, data), /Function call depth exceeded/);
const pineIndicatorId = chart.addIndicator('PINE_SCRIPT', {
  placement: 'new',
  inputs: { code: pineSource, title: 'RSI Average' }
});
assert.ok(chart.indicatorResults[pineIndicatorId]);
assert.strictEqual(chart.indicatorResults[pineIndicatorId].error, null);
assert.ok(chart.indicatorResults[pineIndicatorId].outputs.plot1.length > 0);
chart.openPineScriptPopup();
assert.ok(chart.settingsPopup.className.split(/\s+/).includes('sce-pine-window'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-remove'));
const pineRemoveButton = new FakeElement('button');
pineRemoveButton.setAttribute('data-sce-popup-action', 'remove');
pineRemoveButton.setAttribute('data-sce-pine-remove', '');
pineRemoveButton.hidden = true;
chart.settingsPopup.appendChild(pineRemoveButton);
chart.setPineRemoveButtonVisible(false);
assert.strictEqual(pineRemoveButton.hidden, true);
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="docs"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-docs'));
const pineDocsPanel = new FakeElement('section');
pineDocsPanel.setAttribute('data-sce-pine-docs', '');
pineDocsPanel.hidden = true;
const pineDocsSearch = new FakeElement('input');
pineDocsSearch.setAttribute('data-sce-pine-doc-search', '');
const pineDocsFilter = new FakeElement('select');
pineDocsFilter.setAttribute('data-sce-pine-doc-filter', '');
pineDocsFilter.value = 'all';
const pineDocsList = new FakeElement('div');
pineDocsList.setAttribute('data-sce-pine-doc-list', '');
const pineDocsDetail = new FakeElement('article');
pineDocsDetail.setAttribute('data-sce-pine-doc-detail', '');
chart.settingsPopup.appendChild(pineDocsPanel);
chart.settingsPopup.appendChild(pineDocsSearch);
chart.settingsPopup.appendChild(pineDocsFilter);
chart.settingsPopup.appendChild(pineDocsList);
chart.settingsPopup.appendChild(pineDocsDetail);
assert.strictEqual(chart.openPineDocumentation(), true);
assert.strictEqual(pineDocsPanel.hidden, false);
assert.ok(pineDocsList.innerHTML.includes('Functions'));
pineDocsSearch.value = 'moving average';
chart.renderPineDocumentation(pineDocsSearch.value, pineDocsFilter.value);
assert.ok(pineDocsList.innerHTML.includes('ta.sma'));
pineDocsFilter.value = 'keyword';
chart.renderPineDocumentation('', pineDocsFilter.value);
assert.ok(pineDocsList.innerHTML.includes('and / or / not'));
chart.closePineDocumentation();
assert.strictEqual(pineDocsPanel.hidden, true);
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-window-action="minimize"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-resize'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-splitter'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-help-action="minimize"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-help-action="maximize"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-help-action="restore"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-highlight'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-signature-help'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-status aria-live="polite" hidden'));
assert.strictEqual(chart.settingsPopup.innerHTML.includes('Supports indicator(), plot(), hline()'), false);
assert.ok(chart.settingsPopup.innerHTML.includes('sce-pine-token-function'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="undo"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="redo"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-findbar'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-command-palette'));
assert.strictEqual(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="command-palette"'), false);
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="font-increase"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="font-decrease"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="font-reset"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-editor-action="recent"'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-pine-recent-menu'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-popup-action="load-pine"'));
assert.ok(chart.settingsPopup.innerHTML.includes('Load Pine Script from your device (Ctrl/Cmd+O)'));
assert.ok(chart.settingsPopup.innerHTML.includes('data-sce-popup-action="save-pine"'));
assert.ok(chart.settingsPopup.innerHTML.includes('accept=".pine,.txt,text/plain"'));
const recentPineCode = 'indicator("Recent")\nplot(close)';
chart.rememberRecentPineScript('Recent test', recentPineCode);
const recentPineScripts = chart.getRecentPineScripts();
assert.strictEqual(recentPineScripts[0].title, 'Recent test');
assert.strictEqual(recentPineScripts[0].code, recentPineCode);
const pineSignatureField = new FakeElement('textarea');
pineSignatureField.setAttribute('data-sce-pine-field', 'code');
pineSignatureField.value = 'plot(ta.sma(close, 20), ';
pineSignatureField.selectionStart = pineSignatureField.value.length;
const pineSignatureHelp = new FakeElement('div');
pineSignatureHelp.setAttribute('data-sce-pine-signature-help', '');
chart.settingsPopup.appendChild(pineSignatureField);
chart.settingsPopup.appendChild(pineSignatureHelp);
chart.showPineSignatureHelp();
assert.strictEqual(pineSignatureHelp.hidden, false);
assert.ok(pineSignatureHelp.innerHTML.includes('color'));
assert.ok(pineSignatureHelp.innerHTML.includes('series'));
const pineFileInput = new FakeElement('input');
pineFileInput.setAttribute('data-sce-pine-file-input', '');
pineFileInput.click = function () { pineFileInput.wasClicked = true; };
chart.settingsPopup.appendChild(pineFileInput);
const openPineShortcutEvent = {
  key: 'o',
  ctrlKey: true,
  preventDefault() { this.defaultPrevented = true; },
  stopPropagation() { this.propagationStopped = true; }
};
chart.handlePineEditorKeydown(openPineShortcutEvent);
assert.strictEqual(openPineShortcutEvent.defaultPrevented, true);
assert.strictEqual(openPineShortcutEvent.propagationStopped, true);
assert.strictEqual(pineFileInput.wasClicked, true);
pineSignatureField.value = 'indicator("Run test")\nplot(close)';
const originalAddIndicator = chart.addIndicator;
const originalUpdateIndicatorSettings = chart.updateIndicatorSettings;
let pineAddCount = 0;
let pineUpdateCount = 0;
chart.addIndicator = function () {
  pineAddCount += 1;
  return 'pine-run-test';
};
chart.updateIndicatorSettings = function (indicatorId) {
  pineUpdateCount += 1;
  assert.strictEqual(indicatorId, 'pine-run-test');
};
chart.applyPineScriptPopup();
assert.strictEqual(pineAddCount, 1);
assert.strictEqual(pineUpdateCount, 0);
assert.strictEqual(chart.settingsPopup.dataset.indicatorId, 'pine-run-test');
assert.strictEqual(pineRemoveButton.hidden, false);
chart.applyPineScriptPopup();
assert.strictEqual(pineAddCount, 1);
assert.strictEqual(pineUpdateCount, 1);
assert.ok(chart.pineRunGlow);
assert.deepStrictEqual(chart.pineRunGlow.paneIds, ['price']);
chart.canvas.commands = [];
chart.triggerPineRunGlow(['price', 'pane-rsi', 'price']);
assert.deepStrictEqual(chart.pineRunGlow.paneIds, ['price', 'pane-rsi']);
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect' && command.strokeStyle === chart.theme().drawing));
chart.canvas.commands = [];
chart.setTheme('dark');
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect' && command.strokeStyle === chart.theme().drawing));
chart.canvas.commands = [];
chart.setTheme('light');
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect' && command.strokeStyle === chart.theme().drawing));
chart.pineRunGlow.startedAt = Date.now() - chart.pineRunGlow.duration;
chart.draw();
assert.strictEqual(chart.pineRunGlow, null);
chart.addIndicator = originalAddIndicator;
chart.updateIndicatorSettings = originalUpdateIndicatorSettings;
const existingPineIndicator = chart.document.indicators.filter((indicator) => indicator.id === pineIndicatorId)[0];
const existingPinePaneId = existingPineIndicator.paneId;
assert.notStrictEqual(existingPinePaneId, 'price');
chart.settingsPopup.dataset.indicatorId = pineIndicatorId;
pineSignatureField.value = 'indicator("Run overlay", overlay=true)\nplot(ta.sma(close, 200))';
chart.applyPineScriptPopup();
assert.strictEqual(existingPineIndicator.paneId, 'price');
assert.strictEqual(chart.document.panes.some((pane) => pane.id === existingPinePaneId), false);
pineSignatureField.value = 'indicator("Run pane", overlay=false)\nplot(ta.sma(close, 200))';
chart.applyPineScriptPopup();
assert.notStrictEqual(existingPineIndicator.paneId, 'price');
assert.ok(chart.document.panes.some((pane) => pane.id === existingPineIndicator.paneId));
chart.openPineScriptPopup(pineIndicatorId);
const actualPineRemoveButton = new FakeElement('button');
actualPineRemoveButton.setAttribute('data-sce-popup-action', 'remove');
actualPineRemoveButton.setAttribute('data-sce-pine-remove', '');
chart.settingsPopup.appendChild(actualPineRemoveButton);
chart.settingsPopup.onclick({ target: actualPineRemoveButton, preventDefault() {} });
assert.strictEqual(chart.document.indicators.some((indicator) => indicator.id === pineIndicatorId), false);
assert.strictEqual(actualPineRemoveButton.hidden, true);
assert.strictEqual(chart.settingsPopup.hasAttribute('hidden'), false);
assert.strictEqual(chart.pineScriptFileName('My RSI Script'), 'My-RSI-Script.pine');
assert.strictEqual(chart.pineScriptFileName('already.pine'), 'already.pine');
assert.ok(parseInt(chart.settingsPopup.style.left, 10) >= chart.drawingRailWidth());
chart.setPineWindowAction('maximize');
assert.ok(chart.settingsPopup.className.split(/\s+/).includes('is-maximized'));
chart.setPineWindowAction('restore');
assert.ok(!chart.settingsPopup.className.split(/\s+/).includes('is-maximized'));
const pinePaneSplitBeforeHelpAction = chart.pineWindowState.paneSplit;
chart.setPineHelpPaneAction('maximize');
assert.strictEqual(chart.pineWindowState.helpMaximized, true);
chart.setPineHelpPaneAction('restore');
assert.strictEqual(chart.pineWindowState.helpMaximized, false);
assert.strictEqual(chart.pineWindowState.paneSplit, pinePaneSplitBeforeHelpAction);
chart.setPineHelpPaneAction('minimize');
assert.strictEqual(chart.pineWindowState.helpMinimized, true);
chart.setPineHelpPaneAction('minimize');
assert.strictEqual(chart.pineWindowState.helpMinimized, false);
chart.pineWindowState.height = 300;
chart.positionPineScriptWindow();
const pineDragHandle = new FakeElement('div');
pineDragHandle.setAttribute('data-sce-pine-drag-handle', '');
chart.settingsPopup.appendChild(pineDragHandle);
const pineLeftBeforeDrag = chart.pineWindowState.left;
chart.settingsPopup.onpointerdown({ target: pineDragHandle, clientX: 100, clientY: 100, pointerId: 7, preventDefault() {} });
chart.handlePineWindowPointerMove({ clientX: 130, clientY: 120, pointerId: 7, preventDefault() {} });
assert.strictEqual(chart.pineWindowState.left, pineLeftBeforeDrag + 30);
assert.strictEqual(chart.pineWindowState.top, 28);
chart.handlePineWindowPointerUp({ pointerId: 7 });
const pineResizeHandle = new FakeElement('div');
pineResizeHandle.setAttribute('data-sce-pine-resize', '');
chart.settingsPopup.appendChild(pineResizeHandle);
const pineWidthBeforeResize = chart.pineWindowState.width;
chart.settingsPopup.onpointerdown({ target: pineResizeHandle, clientX: 200, clientY: 200, pointerId: 8, preventDefault() {} });
chart.handlePineWindowPointerMove({ clientX: 240, clientY: 240, pointerId: 8, preventDefault() {} });
assert.strictEqual(chart.pineWindowState.width, pineWidthBeforeResize + 40);
assert.strictEqual(chart.pineWindowState.height, 340);
chart.handlePineWindowPointerUp({ pointerId: 8 });
const pinePaneSplitter = new FakeElement('div');
pinePaneSplitter.setAttribute('data-sce-pine-splitter', '');
chart.settingsPopup.appendChild(pinePaneSplitter);
chart.pineWindowState.helpMinimized = true;
chart.pineWindowState.helpMaximized = false;
chart.pineWindowState.helpRestore = { paneSplit: 0.52 };
chart.settingsPopup.onpointerdown({ target: pinePaneSplitter, clientX: 200, clientY: 300, pointerId: 9, preventDefault() {} });
assert.strictEqual(chart.pineWindowInteraction.type, 'pane-split');
assert.strictEqual(chart.pineWindowState.helpMinimized, false);
chart.handlePineWindowPointerMove({ clientX: 200, clientY: 330, pointerId: 9, preventDefault() {} });
assert.ok(chart.pineWindowState.paneSplit > 0.2);
chart.handlePineWindowPointerUp({ pointerId: 9 });
chart.pineWindowState.left = 46;
chart.pineWindowState.top = 32;
chart.pineWindowState.width = 640;
chart.pineWindowState.height = 420;
chart.pineWindowState.paneSplit = 0.44;
chart.pineWindowState.helpMinimized = false;
chart.pineWindowState.helpMaximized = true;
chart.pineWindowState.helpRestore = { paneSplit: 0.56 };
chart.pineWindowState.minimized = true;
chart.pineWindowState.maximized = false;
chart.savePineWindowSettings();
chart.pineWindowState.left = null;
chart.pineWindowState.top = null;
chart.pineWindowState.width = 480;
chart.pineWindowState.height = 580;
chart.pineWindowState.minimized = false;
chart.loadPineWindowSettings();
assert.strictEqual(chart.pineWindowState.left, 46);
assert.strictEqual(chart.pineWindowState.top, 32);
assert.strictEqual(chart.pineWindowState.width, 640);
assert.strictEqual(chart.pineWindowState.height, 420);
assert.strictEqual(chart.pineWindowState.paneSplit, 0.44);
assert.strictEqual(chart.pineWindowState.helpMaximized, true);
assert.deepStrictEqual(chart.pineWindowState.helpRestore, { paneSplit: 0.56 });
assert.strictEqual(chart.pineWindowState.minimized, true);
chart.closeIndicatorSettingsPopup();
chart.emitChange('autosave-disabled-check', {});
assert.strictEqual(chart.autosaveTimer, null);
const toolbarOpenDetail = new FakeElement('details');
toolbarOpenDetail.setAttribute('open', 'open');
chart.toolbar.querySelectorAll = function queryOpenToolbarMenus(selector) {
  return selector === 'details[open]' ? [toolbarOpenDetail] : [];
};
const drawingOpenDetail = new FakeElement('details');
drawingOpenDetail.setAttribute('open', 'open');
chart.drawingToolsLayer.querySelectorAll = function queryOpenDrawingMenus(selector) {
  return selector === 'details[open]' ? [drawingOpenDetail] : [];
};
chart.handleDocumentPointerDown({ target: new FakeElement('button') });
assert.strictEqual(toolbarOpenDetail.hasAttribute('open'), false);
assert.strictEqual(drawingOpenDetail.hasAttribute('open'), false);
chart.settingsPopup.removeAttribute('hidden');
const settingsInnerButton = new FakeElement('button');
chart.settingsPopup.appendChild(settingsInnerButton);
chart.handleDocumentPointerDown({ target: settingsInnerButton });
assert.strictEqual(chart.settingsPopup.hasAttribute('hidden'), false);
chart.handleDocumentPointerDown({ target: new FakeElement('button') });
assert.strictEqual(chart.settingsPopup.hasAttribute('hidden'), true);
const openedToolbarDetail = new FakeElement('details');
const siblingToolbarDetail = new FakeElement('details');
openedToolbarDetail.setAttribute('open', 'open');
siblingToolbarDetail.setAttribute('open', 'open');
chart.toolbar.querySelectorAll = function querySiblingToolbarMenus(selector) {
  return selector === 'details[open]' ? [openedToolbarDetail, siblingToolbarDetail] : [];
};
chart.closeSiblingDetailsMenus(chart.toolbar, openedToolbarDetail);
assert.strictEqual(openedToolbarDetail.hasAttribute('open'), true);
assert.strictEqual(siblingToolbarDetail.hasAttribute('open'), false);
const chartTypeDetailForSelectFocus = new FakeElement('details');
chartTypeDetailForSelectFocus.setAttribute('open', 'open');
const chartTypeSummaryForSelectFocus = new FakeElement('summary');
chartTypeDetailForSelectFocus.appendChild(chartTypeSummaryForSelectFocus);
chart.toolbar.appendChild(chartTypeDetailForSelectFocus);
chart.toolbar.querySelectorAll = function queryToolbarMenusForSelectFocus(selector) {
  return selector === 'details[open]' ? [chartTypeDetailForSelectFocus] : [];
};
chart.closeToolbarMenusForTarget(chartTypeSummaryForSelectFocus);
assert.strictEqual(chartTypeDetailForSelectFocus.hasAttribute('open'), true);
const periodSelectForFocus = new FakeElement('select');
chart.toolbar.appendChild(periodSelectForFocus);
chart.closeToolbarMenusForTarget(periodSelectForFocus);
assert.strictEqual(chartTypeDetailForSelectFocus.hasAttribute('open'), false);
const historyPaneCount = chart.document.panes.length;
const historyIndicatorId = chart.addIndicator('RSI', { placement: 'new' });
assert.ok(chart.document.indicators.some((indicator) => indicator.id === historyIndicatorId));
assert.strictEqual(chart.document.panes.length, historyPaneCount + 1);
assert.strictEqual(chart.undo(), true);
assert.strictEqual(chart.document.indicators.some((indicator) => indicator.id === historyIndicatorId), false);
assert.strictEqual(chart.document.panes.length, historyPaneCount);
assert.strictEqual(chart.redo(), true);
assert.ok(chart.document.indicators.some((indicator) => indicator.id === historyIndicatorId));
assert.strictEqual(chart.undo(), true);
const keyboardDrawingId = chart.addDrawing('trendline', [
  { time: chart.bars[0].time, value: chart.bars[0].close },
  { time: chart.bars[1].time, value: chart.bars[1].close }
]);
assert.ok(chart.getDrawingById(keyboardDrawingId));
const undoEvent = {
  key: 'z',
  ctrlKey: true,
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.handleDocumentKeyDown(undoEvent);
assert.strictEqual(undoEvent.defaultPrevented, true);
assert.strictEqual(chart.getDrawingById(keyboardDrawingId), null);
const redoEvent = {
  key: 'z',
  metaKey: true,
  shiftKey: true,
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.handleDocumentKeyDown(redoEvent);
assert.strictEqual(redoEvent.defaultPrevented, true);
assert.ok(chart.getDrawingById(keyboardDrawingId));
assert.strictEqual(chart.undo(), true);
const ctrlYRedoEvent = {
  key: 'y',
  ctrlKey: true,
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.handleDocumentKeyDown(ctrlYRedoEvent);
assert.strictEqual(ctrlYRedoEvent.defaultPrevented, true);
assert.ok(chart.getDrawingById(keyboardDrawingId));
assert.strictEqual(chart.undo(), true);
const drawingMenuForPosition = new FakeElement('div');
drawingMenuForPosition.getBoundingClientRect = function getMenuRect() {
  return { width: 360, height: 240 };
};
const drawingSummaryForPosition = new FakeElement('summary');
drawingSummaryForPosition.getBoundingClientRect = function getSummaryRect() {
  return { left: 12, right: 54, top: 120, bottom: 152, width: 42, height: 32 };
};
chart.drawingToolsLayer.querySelectorAll = function queryOpenDrawingGroups(selector) {
  if (selector !== '.sce-drawing-tool-group[open]') return [];
  return [{
    querySelector(query) {
      if (query === 'summary') return drawingSummaryForPosition;
      if (query === '.sce-drawing-tool-menu') return drawingMenuForPosition;
      return null;
    }
  }];
};
global.window.innerWidth = 500;
global.window.innerHeight = 420;
chart.positionDrawingToolMenus();
assert.strictEqual(drawingMenuForPosition.style['--sce-drawing-menu-left'], '62px');
assert.strictEqual(drawingMenuForPosition.style['--sce-drawing-menu-top'], '120px');
assert.strictEqual(drawingMenuForPosition.style['--sce-drawing-menu-max-height'], '288px');
const priceRectForLeadSpace = chart.getPaneRect('price');
const latestXWithLeadSpace = chart.xForTime(chart.visibleBars()[chart.visibleBars().length - 1].time, priceRectForLeadSpace);
assert.ok(priceRectForLeadSpace.x + priceRectForLeadSpace.width - latestXWithLeadSpace > 10);
assert.strictEqual(chart.timeForX(priceRectForLeadSpace.x + priceRectForLeadSpace.width, priceRectForLeadSpace), chart.visibleBars()[chart.visibleBars().length - 1].time);
assert.strictEqual(Math.round(chart.barIndexAtPoint({
  x: priceRectForLeadSpace.x + priceRectForLeadSpace.width,
  y: priceRectForLeadSpace.y + priceRectForLeadSpace.height / 2
})), chart.visibleIndexRange().to);

assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'light');
assert.strictEqual(chart.document.settings.chartType, 'candlestick');
assert.strictEqual(chart.document.settings.autosave, true);
assert.ok(chart.drawingToolsLayer.innerHTML.indexOf('data-sce-action="toggle-magnet"') !== -1);
assert.ok(chart.drawingToolsLayer.innerHTML.indexOf('data-sce-action="toggle-stay-drawing"') !== -1);
assert.ok(chart.drawingToolsLayer.innerHTML.indexOf('data-sce-action="toggle-lock-drawings"') !== -1);
assert.ok(chart.drawingToolsLayer.innerHTML.indexOf('data-sce-action="toggle-drawings-visible"') !== -1);
assert.strictEqual(chart.drawingToolsLayer.innerHTML.indexOf('aria-label="Icons"'), -1);
assert.strictEqual(chart.drawingToolsLayer.innerHTML.indexOf('aria-label="Stickers"'), -1);
assert.strictEqual(chart.drawingToolsLayer.innerHTML.indexOf('aria-label="Emoji"'), -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-action="zoom-in"') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('aria-label="Zoom in"><svg') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-action="zoom-out"') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('aria-label="Zoom out"><svg') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-period-picker') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-period-option="weekly"') !== -1);
const periodOptionForTest = new FakeElement('button');
periodOptionForTest.setAttribute('data-sce-period-option', 'weekly');
chart.toolbar.appendChild(periodOptionForTest);
chart.toolbar.dispatchEvent({
  type: 'click',
  target: periodOptionForTest,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.strictEqual(chart.document.settings.period, 'weekly');
chart.setPeriod('daily');
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-recent-stocks-picker') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-recent-stock="SPY"') !== -1);
const indicatorSearchForFocusTest = new FakeElement('input');
indicatorSearchForFocusTest.setAttribute('data-sce-indicator-search', '');
let indicatorSearchFocusCount = 0;
indicatorSearchForFocusTest.focus = () => { indicatorSearchFocusCount += 1; };
chart.toolbar.appendChild(indicatorSearchForFocusTest);
chart.focusIndicatorMenuSearch();
assert.strictEqual(indicatorSearchFocusCount, 1);
let projectSheetOptions = null;
global.window.openProjectBottomSheet = (options) => {
  projectSheetOptions = options;
  return true;
};
global.window.setTimeout = (callback) => callback();
assert.strictEqual(chart.openRecentStocksInProjectSheet(), true);
assert.strictEqual(projectSheetOptions.title, 'Recent stocks');
assert.strictEqual(projectSheetOptions.items[0].code, 'SPY');
projectSheetOptions.onSelect({ code: 'SPY' });
assert.strictEqual(recentStockSelections.length, 1);
assert.strictEqual(chart.openToolbarPickerInProjectSheet('chart-type'), true);
assert.strictEqual(projectSheetOptions.title, 'Chart type');
assert.strictEqual(projectSheetOptions.items[0].label, 'Candlestick');
projectSheetOptions.onSelect({ value: 'line' });
assert.strictEqual(chart.document.settings.chartType, 'line');
assert.strictEqual(chart.openToolbarPickerInProjectSheet('period'), true);
assert.strictEqual(projectSheetOptions.title, 'Timeframe');
projectSheetOptions.onSelect({ value: 'weekly' });
assert.strictEqual(chart.document.settings.period, 'weekly');
assert.strictEqual(chart.openToolbarPickerInProjectSheet('date-range'), true);
assert.strictEqual(projectSheetOptions.title, 'Date range');
assert.strictEqual(projectSheetOptions.items.some((item) => item.value === '1y'), true);
assert.strictEqual(chart.openToolbarPickerInProjectSheet('indicators'), true);
assert.strictEqual(projectSheetOptions.title, 'Indicators');
assert.strictEqual(projectSheetOptions.searchable, true);
assert.strictEqual(projectSheetOptions.items.some((item) => item.value === 'RSI'), true);
assert.strictEqual(chart.openToolbarPickerInProjectSheet('share'), true);
assert.strictEqual(projectSheetOptions.title, 'Share chart');
assert.strictEqual(projectSheetOptions.items[1].value, 'copy-share-url');
chart.setChartType('candlestick');
chart.setPeriod('daily');
const recentStockButtonForTest = new FakeElement('button');
recentStockButtonForTest.setAttribute('data-sce-recent-stock', 'SPY');
chart.toolbar.appendChild(recentStockButtonForTest);
chart.toolbar.dispatchEvent({
  type: 'click',
  target: recentStockButtonForTest,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.strictEqual(recentStockSelections.length, 2);
assert.strictEqual(recentStockSelections[0].code, 'SPY');
chart.setRecentStocks([{ code: 'QQQ', name_en: 'Invesco QQQ Trust' }]);
assert.ok(chart.options.recentStocks.some((stock) => stock.code === 'QQQ'));
const zoomButtonForNestedClick = new FakeElement('button');
zoomButtonForNestedClick.setAttribute('data-sce-action', 'zoom-in');
const zoomSvgForNestedClick = new FakeElement('svg');
zoomButtonForNestedClick.appendChild(zoomSvgForNestedClick);
chart.toolbar.appendChild(zoomButtonForNestedClick);
chart.fitContent();
const zoomBeforeNestedClick = chart.visibleIndexRange();
chart.toolbar.dispatchEvent({
  type: 'click',
  target: zoomSvgForNestedClick,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
const zoomAfterNestedClick = chart.visibleIndexRange();
assert.ok(zoomAfterNestedClick.to - zoomAfterNestedClick.from < zoomBeforeNestedClick.to - zoomBeforeNestedClick.from);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-action="full-browser"') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-action="fullscreen"') !== -1);
const fullBrowserButtonForTest = new FakeElement('button');
fullBrowserButtonForTest.setAttribute('data-sce-action', 'full-browser');
const fullBrowserSvgForTest = new FakeElement('svg');
fullBrowserButtonForTest.appendChild(fullBrowserSvgForTest);
chart.toolbar.appendChild(fullBrowserButtonForTest);
chart.toolbar.dispatchEvent({
  type: 'click',
  target: fullBrowserSvgForTest,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.strictEqual(chart.fullBrowserMode, true);
assert.ok((chart.root.className || '').split(/\s+/).includes('sce-root-full-browser'));
assert.strictEqual(fullBrowserButtonForTest.getAttribute('aria-pressed'), 'true');
const fullBrowserEscape = {
  key: 'Escape',
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.handleDocumentKeyDown(fullBrowserEscape);
assert.strictEqual(fullBrowserEscape.defaultPrevented, true);
assert.strictEqual(chart.fullBrowserMode, false);
assert.strictEqual((chart.root.className || '').split(/\s+/).includes('sce-root-full-browser'), false);
chart.root.requestFullscreen = function requestFullscreenForTest() {
  global.document.fullscreenElement = chart.root;
};
global.document.exitFullscreen = function exitFullscreenForTest() {
  global.document.fullscreenElement = null;
};
chart.toggleFullscreenMode();
assert.strictEqual(chart.isFullscreenActive(), true);
chart.toggleFullscreenMode();
assert.strictEqual(chart.isFullscreenActive(), false);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-stock-info-link') !== -1);
assert.strictEqual(chart.stockInfoHref(), 'stockinfo?code=TEST');
const stockInfoLink = chart.toolbar.querySelector('[data-sce-stock-info-link]');
assert.strictEqual(stockInfoLink.href, 'stockinfo?code=TEST');
assert.strictEqual(stockInfoLink.hasAttribute('hidden'), false);
assert.strictEqual(stockInfoLink.textContent, 'TEST');
assert.strictEqual(chart.toolbar.querySelector('[data-sce-interval-label]'), null);
assert.strictEqual(chart.toolbar.innerHTML.indexOf('>Stock Info<'), -1);
chart.setSymbolInfo({
  code: 'TEST',
  name_en: 'Example Holdings',
  name_tc: '\u6e2c\u8a66\u516c\u53f8'
});
assert.strictEqual(chart.document.symbolInfo.name_en, 'Example Holdings');
assert.strictEqual(chart.document.symbolInfo.name_tc, '\u6e2c\u8a66\u516c\u53f8');
chart.canvas.commands = [];
chart.draw();
let watermarkText = chart.canvas.commands.filter((command) => command.type === 'fillText').map((command) => command.text);
assert.ok(watermarkText.includes('TEST 1D'));
assert.ok(watermarkText.includes('Example Holdings'));
assert.ok(watermarkText.includes('\u6e2c\u8a66\u516c\u53f8'));
const lightWatermarkCommand = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === 'TEST 1D');
assert.ok(lightWatermarkCommand.alpha > 0 && lightWatermarkCommand.alpha < 0.1);
chart.canvas.commands = [];
chart.setTheme('dark');
watermarkText = chart.canvas.commands.filter((command) => command.type === 'fillText').map((command) => command.text);
assert.ok(watermarkText.includes('Example Holdings'));
const darkWatermarkCommand = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === 'TEST 1D');
assert.ok(darkWatermarkCommand.alpha > lightWatermarkCommand.alpha);
chart.canvas.commands = [];
chart.setTheme('light');
watermarkText = chart.canvas.commands.filter((command) => command.type === 'fillText').map((command) => command.text);
assert.ok(watermarkText.includes('\u6e2c\u8a66\u516c\u53f8'));
assert.strictEqual(chart.document.theme, 'light');
chart.canvas.commands = [];
chart.setPeriod('quarterly');
watermarkText = chart.canvas.commands.filter((command) => command.type === 'fillText').map((command) => command.text);
assert.ok(watermarkText.includes('TEST 1Q'));
chart.setPeriod('daily');
const originalSourceBarsForStockInfo = chart.sourceBars;
chart.sourceBars = [];
assert.strictEqual(chart.stockInfoHref(), 'stockinfo?code=TEST');
chart.sourceBars = originalSourceBarsForStockInfo;
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-indicator-picker') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-indicator-search') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-indicator-option="RSI"') !== -1);
const menuSmaId = chart.addIndicatorFromMenu('SMA');
assert.ok(chart.document.indicators.some((indicator) => indicator.id === menuSmaId && indicator.type === 'SMA'));
assert.strictEqual(chart.addIndicatorFromMenu('NOPE'), null);
chart.removeIndicator(menuSmaId);
const sourceRsiId = chart.addIndicator('RSI', { placement: 'new' });
const sourceRsi = chart.document.indicators.find((indicator) => indicator.id === sourceRsiId);
const sourceSmaOnRsiId = chart.addIndicator('SMA', {
  source: { kind: 'indicator', indicatorId: sourceRsiId, output: 'value' },
  inputs: { length: 9 }
});
const sourceSmaOnRsi = chart.document.indicators.find((indicator) => indicator.id === sourceSmaOnRsiId);
assert.strictEqual(sourceSmaOnRsi.paneId, sourceRsi.paneId);
assert.strictEqual(sourceSmaOnRsi.source.indicatorId, sourceRsiId);
assert.ok((chart.indicatorResults[sourceSmaOnRsiId].outputs.value || []).every((point) => point.value >= 0 && point.value <= 100));
const detachedSmaId = chart.addIndicator('SMA', { inputs: { length: 5 } });
chart.updateIndicatorSettings(detachedSmaId, {
  source: { kind: 'indicator', indicatorId: sourceRsiId, output: 'value' }
});
const detachedSma = chart.document.indicators.find((indicator) => indicator.id === detachedSmaId);
assert.strictEqual(detachedSma.paneId, sourceRsi.paneId);
assert.strictEqual(detachedSma.source.indicatorId, sourceRsiId);
const sourceOptionKeys = chart.indicatorSourceOptions(sourceSmaOnRsiId).map((option) => option.key);
assert.ok(sourceOptionKeys.includes('price:close'));
assert.ok(sourceOptionKeys.includes(`indicator:${sourceRsiId}:value`));
assert.strictEqual(sourceOptionKeys.some((key) => key.indexOf(`indicator:${sourceSmaOnRsiId}:`) === 0), false);
chart.removeIndicator(detachedSmaId);
chart.removeIndicator(sourceSmaOnRsiId);
chart.removeIndicator(sourceRsiId);
const savedToolbarQuerySelectorAll = chart.toolbar.querySelectorAll;
const savedToolbarQuerySelector = chart.toolbar.querySelector;
const menuSmaButton = new FakeElement('button');
const menuRsiButton = new FakeElement('button');
const menuEmptyMessage = new FakeElement('div');
menuSmaButton.setAttribute('data-sce-indicator-search', 'sma simple moving average trend');
menuRsiButton.setAttribute('data-sce-indicator-search', 'rsi relative strength index momentum');
chart.toolbar.querySelectorAll = function queryIndicatorMenuButtons(selector) {
  return selector === '[data-sce-indicator-option]' ? [menuSmaButton, menuRsiButton] : [];
};
chart.toolbar.querySelector = function queryIndicatorMenuEmpty(selector) {
  return selector === '[data-sce-indicator-empty]' ? menuEmptyMessage : null;
};
chart.filterIndicatorMenu('strength');
assert.strictEqual(menuSmaButton.hidden, true);
assert.strictEqual(menuRsiButton.hidden, false);
assert.strictEqual(menuEmptyMessage.hidden, true);
chart.filterIndicatorMenu('zzzz');
assert.strictEqual(menuSmaButton.hidden, true);
assert.strictEqual(menuRsiButton.hidden, true);
assert.strictEqual(menuEmptyMessage.hidden, false);
chart.toolbar.querySelectorAll = savedToolbarQuerySelectorAll;
chart.toolbar.querySelector = savedToolbarQuerySelector;
assert.strictEqual(chart.toolbar.innerHTML.indexOf('data-sce-action="save"'), -1);
assert.strictEqual(chart.toolbar.innerHTML.indexOf('data-sce-action="export-image"'), -1);
assert.ok(chart.drawingToolsLayer.innerHTML.indexOf('data-sce-action="export-image"') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-share-action="share-chart"') !== -1);
assert.ok(chart.toolbar.innerHTML.indexOf('data-sce-share-action="copy-share-url"') !== -1);
assert.strictEqual(chart.toolbar.innerHTML.indexOf('data-sce-share-action="copy-layout"'), -1);
assert.strictEqual(chart.toolbar.innerHTML.indexOf('data-sce-share-action="download-layout"'), -1);
assert.strictEqual(chart.setPaneScaleMode('price', 'log'), 'log');
assert.strictEqual(chart.paneScaleMode('price'), 'log');
assert.strictEqual(chart.yForValue(0, chart.getPaneRect('price'), chart.paneRange('price')), null);
assert.strictEqual(chart.yForValue(-1, chart.getPaneRect('price'), chart.paneRange('price')), null);
assert.ok(chart.scaleHitZones.some((zone) => zone.paneId === 'price'));
assert.strictEqual(chart.togglePaneScaleMode('price'), 'linear');
assert.strictEqual(chart.paneScaleMode('price'), 'linear');
chart.draw();
let scaleModeText = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === 'LINEAR');
assert.ok(scaleModeText);
assert.strictEqual(scaleModeText.font, '11px sans-serif');
const scaleModeZone = chart.scaleHitZones.find((zone) => zone.paneId === 'price');
chart.pointer = { x: scaleModeZone.x + 2, y: scaleModeZone.y + 2, pointerType: 'mouse' };
chart.canvas.commands = [];
chart.draw();
scaleModeText = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === 'LINEAR');
assert.ok(scaleModeText);
assert.strictEqual(scaleModeText.font, '700 11px sans-serif');
chart.pointer = null;
const yScaleDragRect = chart.getPaneRect('price');
const yScaleStartRange = chart.paneRange('price');
const yScaleStartSpan = yScaleStartRange.max - yScaleStartRange.min;
chart.handlePointerDown({
  clientX: yScaleDragRect.scaleX + 12,
  clientY: yScaleDragRect.y + yScaleDragRect.height / 2
});
chart.handlePointerMove({
  clientX: yScaleDragRect.scaleX + 12,
  clientY: yScaleDragRect.y + yScaleDragRect.height / 2 + 72
});
const yScaleZoomedOutRange = chart.paneRange('price');
assert.ok(yScaleZoomedOutRange.max - yScaleZoomedOutRange.min > yScaleStartSpan);
chart.handlePointerUp();
assert.ok(chart.paneManualRange('price'));
chart.handlePointerDown({
  clientX: yScaleDragRect.scaleX + 12,
  clientY: yScaleDragRect.y + yScaleDragRect.height / 2
});
chart.handlePointerMove({
  clientX: yScaleDragRect.scaleX + 12,
  clientY: yScaleDragRect.y + yScaleDragRect.height / 2 - 72
});
const yScaleZoomedInRange = chart.paneRange('price');
assert.ok(yScaleZoomedInRange.max - yScaleZoomedInRange.min < yScaleZoomedOutRange.max - yScaleZoomedOutRange.min);
chart.handlePointerUp();
assert.ok(chart.paneManualRange('price'));
chart.handleCanvasDoubleClick({
  clientX: yScaleDragRect.scaleX + 12,
  clientY: yScaleDragRect.y + yScaleDragRect.height / 2,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.strictEqual(chart.paneManualRange('price'), null);
chart.setPaneManualRange('price', yScaleZoomedInRange);
chart.fitContent();
assert.strictEqual(chart.paneManualRange('price'), null);

const macdLogId = chart.addIndicator('MACD', { placement: 'new' });
const macdLogIndicator = chart.document.indicators.find((indicator) => indicator.id === macdLogId);
chart.setPaneScaleMode(macdLogIndicator.paneId, 'log');
const macdRect = chart.getPaneRect(macdLogIndicator.paneId);
const signedLogRange = { min: -5, max: 5 };
const negativeLogY = chart.yForValue(-1, macdRect, signedLogRange);
const zeroLogY = chart.yForValue(0, macdRect, signedLogRange);
const positiveLogY = chart.yForValue(1, macdRect, signedLogRange);
assert.ok(Number.isFinite(negativeLogY));
assert.ok(Number.isFinite(zeroLogY));
assert.ok(Number.isFinite(positiveLogY));
assert.ok(Math.abs(chart.valueForY(zeroLogY, macdRect, signedLogRange)) < 0.0000001);
chart.draw();

assert.strictEqual(chart.setChartType('bar'), 'bar');
assert.strictEqual(chart.document.settings.chartType, 'bar');
assert.strictEqual(chart.setChartType('line'), 'line');
assert.strictEqual(chart.document.settings.chartType, 'line');
assert.strictEqual(chart.setChartType('heikin-ashi'), 'heikin_ashi');
assert.strictEqual(chart.document.settings.chartType, 'heikin_ashi');
assert.strictEqual(chart.setChartType('area'), 'area');
assert.strictEqual(chart.document.settings.chartType, 'area');
chart.canvas.commands = [];
chart.drawPriceArea({ x: 0, y: 0, width: 100, height: 100, scaleWidth: 68, paneId: 'price' }, { min: 70, max: 140 }, chart.theme());
assert.ok(chart.canvas.commands.some((command) => command.type === 'fill'), 'area chart should fill under the price line');
assert.strictEqual(chart.setChartType('baseline'), 'baseline');
assert.strictEqual(chart.document.settings.chartType, 'baseline');
assert.strictEqual(chart.setChartType('hollow-candles'), 'candlestick');
assert.strictEqual(chart.document.settings.chartType, 'candlestick');
assert.ok(chart.candleBodyWidth(0.5) < 1);
assert.ok(chart.ohlcTickWidth(0.5) < 0.5);
assert.ok(chart.priceStrokeWidth(0.5) < 1);
assert.ok(chart.candleBodyWidth(12) > chart.candleBodyWidth(0.5));
const originalBarsForCandlestickRules = chart.bars;
const originalVisibleBarsForCandlestickRules = chart.visibleBars.bind(chart);
chart.bars = [
  { time: 1000, open: 101, high: 108, low: 99, close: 105, volume: 1000 },
  { time: 2000, open: 108, high: 110, low: 104, close: 106, volume: 1100 },
  { time: 3000, open: 102, high: 105, low: 100, close: 104, volume: 1200 },
  { time: 4000, open: 103, high: 104, low: 98, close: 99, volume: 1300 }
];
chart.visibleBars = function visibleBarsForCandlestickRulesTest() {
  return chart.bars;
};
chart.canvas.commands = [];
chart.drawCandles({ x: 0, y: 0, width: 100, height: 100, scaleWidth: 68 }, { min: 90, max: 120 }, chart.theme());
const candleTheme = chart.theme();
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect' && command.strokeStyle === candleTheme.up), 'green hollow candle should stroke the body');
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillRect' && command.fillStyle === candleTheme.up), 'green filled candle should fill the body');
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect' && command.strokeStyle === candleTheme.down), 'red hollow candle should stroke the body');
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillRect' && command.fillStyle === candleTheme.down), 'red filled candle should fill the body');
const firstHollowWickCommands = chart.canvas.commands.slice(0, 5);
assert.deepStrictEqual(firstHollowWickCommands.map((command) => command.type), ['moveTo', 'lineTo', 'moveTo', 'lineTo', 'stroke']);
assert.ok(firstHollowWickCommands[1].y < firstHollowWickCommands[2].y, 'hollow candle wick should skip the body interior');
const candleRuleVolumeData = chart.bars.map((bar) => ({
  time: bar.time,
  value: bar.volume,
  open: bar.open,
  close: bar.close
}));
chart.canvas.commands = [];
chart.drawVolumeOverlay({ x: 0, y: 0, width: 100, height: 100, scaleWidth: 68, paneId: 'price' }, candleTheme, candleRuleVolumeData, { color: '#2563eb', opacity: 1 });
const overlayVolumeStyles = chart.canvas.commands.filter((command) => command.type === 'fillRect').map((command) => command.fillStyle);
assert.strictEqual(overlayVolumeStyles[1], candleTheme.volumeUp, 'green filled candle volume should use up color');
assert.strictEqual(overlayVolumeStyles[2], candleTheme.volumeDown, 'red hollow candle volume should use down color');
chart.canvas.commands = [];
chart.drawHistogram({ x: 0, y: 0, width: 100, height: 100, scaleWidth: 68, paneId: 'volume' }, { min: 0, max: 1400 }, candleTheme, candleRuleVolumeData, { color: '#2563eb', opacity: 1 }, true);
const paneVolumeStyles = chart.canvas.commands.filter((command) => command.type === 'fillRect').map((command) => command.fillStyle);
assert.strictEqual(paneVolumeStyles[1], candleTheme.volumeUp, 'pane volume should match green filled candle color');
assert.strictEqual(paneVolumeStyles[2], candleTheme.volumeDown, 'pane volume should match red hollow candle color');
chart.bars = originalBarsForCandlestickRules;
chart.visibleBars = originalVisibleBarsForCandlestickRules;
assert.strictEqual(chart.setChartType('candles'), 'candlestick');
assert.strictEqual(chart.document.settings.chartType, 'candlestick');
assert.strictEqual(chart.heikinAshiBars().length, chart.bars.length);

assert.strictEqual(chart.document.settings.period, 'daily');
assert.strictEqual(chart.document.interval, '1D');
assert.strictEqual(chart.sourceBars.length, data.length);
assert.strictEqual(chart.bars.length, data.length);
assert.strictEqual(chart.setPeriod('weekly'), 'weekly');
assert.strictEqual(chart.document.interval, '1W');
assert.ok(chart.bars.length < chart.sourceBars.length);
const weeklyCount = chart.bars.length;
assert.strictEqual(chart.setPeriod('monthly'), 'monthly');
assert.strictEqual(chart.document.interval, '1M');
assert.ok(chart.bars.length < weeklyCount);
assert.strictEqual(chart.setPeriod('quarterly'), 'quarterly');
assert.strictEqual(chart.document.interval, '3M');
assert.ok(chart.bars.length > 0);
assert.strictEqual(chart.setPeriod('yearly'), 'yearly');
assert.strictEqual(chart.document.interval, '1Y');
assert.ok(chart.bars.length > 0);
assert.strictEqual(chart.setPeriod('daily'), 'daily');
assert.strictEqual(chart.bars.length, chart.sourceBars.length);

const longRangeData = StockChartEngine.createDemoData(4200);
chart.setData(longRangeData);
const oneYearRange = chart.setDateRangePreset('1y');
assert.ok(oneYearRange);
assert.strictEqual(chart.document.settings.dateRangePreset, '1y');
const oneYearBars = chart.visibleBars();
assert.ok(oneYearBars.length >= 350 && oneYearBars.length <= 370);
assert.strictEqual(oneYearBars[oneYearBars.length - 1].time, chart.bars[chart.bars.length - 1].time);
chart.setDateRangePreset('all');
assert.strictEqual(chart.document.settings.dateRangePreset, 'all');
assert.deepStrictEqual(chart.visibleIndexRange(), { from: 0, to: chart.bars.length - 1 });
chart.zoomIn(0.5);
assert.strictEqual(chart.document.settings.dateRangePreset, null);
chart.setData(data);

const initialVisibleRange = chart.visibleIndexRange();
const initialVisibleCount = initialVisibleRange.to - initialVisibleRange.from + 1;
assert.ok(initialVisibleCount > 20);
assert.strictEqual(chart.zoomIn(0.5), true);
const zoomedVisibleRange = chart.visibleIndexRange();
const zoomedVisibleCount = zoomedVisibleRange.to - zoomedVisibleRange.from + 1;
assert.ok(zoomedVisibleCount < initialVisibleCount);
assert.strictEqual(chart.zoomOut(0.5), true);
assert.ok(chart.visibleIndexRange().to - chart.visibleIndexRange().from + 1 > zoomedVisibleCount);
chart.zoom(0.5, 0.5);
const scrollStart = chart.visibleIndexRange();
assert.strictEqual(chart.scroll(3), true);
assert.strictEqual(chart.visibleIndexRange().from, scrollStart.from + 3);
chart.handleWheel({
  clientX: 450,
  clientY: 180,
  deltaY: -120,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.ok(chart.visibleIndexRange().to - chart.visibleIndexRange().from + 1 < zoomedVisibleCount);
const wheelScrollStart = chart.visibleIndexRange();
chart.handleWheel({
  clientX: 450,
  clientY: 180,
  deltaX: 120,
  deltaY: 0,
  preventDefault() {
    this.defaultPrevented = true;
  }
});
assert.ok(chart.visibleIndexRange().from >= wheelScrollStart.from);
const panStart = chart.visibleIndexRange();
const priceRectForPan = chart.getPaneRect('price');
chart.handlePointerDown({ clientX: priceRectForPan.x + priceRectForPan.width / 2, clientY: priceRectForPan.y + priceRectForPan.height / 2 });
chart.handlePointerMove({ clientX: priceRectForPan.x + priceRectForPan.width / 2 - 80, clientY: priceRectForPan.y + priceRectForPan.height / 2 });
chart.handlePointerUp();
assert.ok(chart.visibleIndexRange().from >= panStart.from);
assert.strictEqual(chart.goToEarliest(), true);
const earliestKeyboardRange = chart.visibleIndexRange();
const pageDownEvent = {
  type: 'keydown',
  key: 'PageDown',
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.canvas.dispatchEvent(pageDownEvent);
assert.strictEqual(pageDownEvent.defaultPrevented, true);
assert.ok(chart.visibleIndexRange().from > earliestKeyboardRange.from);
const pageUpEvent = {
  type: 'keydown',
  key: 'PageUp',
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.canvas.dispatchEvent(pageUpEvent);
assert.strictEqual(pageUpEvent.defaultPrevented, true);
assert.ok(chart.visibleIndexRange().from < earliestKeyboardRange.from + chart.bars.length);
const endEvent = {
  type: 'keydown',
  key: 'End',
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.canvas.dispatchEvent(endEvent);
assert.strictEqual(endEvent.defaultPrevented, true);
assert.strictEqual(chart.visibleIndexRange().to, chart.bars.length - 1);
const homeEvent = {
  type: 'keydown',
  key: 'Home',
  preventDefault() {
    this.defaultPrevented = true;
  }
};
chart.canvas.dispatchEvent(homeEvent);
assert.strictEqual(homeEvent.defaultPrevented, true);
assert.strictEqual(chart.visibleIndexRange().from, 0);
chart.fitContent();
chart.draw();
assert.strictEqual(chart.latestMarker.hidden, true);
chart.setVisibleIndexRange(0, chart.bars.length - 3);
chart.draw();
assert.strictEqual(chart.latestMarker.hidden, false);
assert.ok(chart.latestMarker.className.indexOf('sce-latest-marker-pulse') !== -1);
assert.strictEqual(chart.latestMarker.getAttribute('aria-label'), 'Go to latest bar');
assert.strictEqual(chart.goToLatest(), true);
assert.strictEqual(chart.visibleIndexRange().to, chart.bars.length - 1);
assert.strictEqual(chart.latestMarker.hidden, true);
assert.ok(chart.latestMarker.className.indexOf('sce-latest-marker-pulse') === -1);

const originalDrawingsForViewportRange = chart.document.drawings;
chart.document.drawings = [];
chart.setVisibleIndexRange(30, 50);
const priceRangeWithoutViewportDrawings = chart.paneRange('price');
chart.document.drawings = [{
  id: 'offscreen-drawing-range-test',
  type: 'trendline',
  paneId: 'price',
  visible: true,
  points: [
    { time: chart.bars[2].time, value: 1000000 },
    { time: chart.bars[4].time, value: 1000000 }
  ]
}];
assert.deepStrictEqual(chart.paneRange('price'), priceRangeWithoutViewportDrawings);
chart.document.drawings = [{
  id: 'hidden-drawing-range-test',
  type: 'trendline',
  paneId: 'price',
  visible: false,
  points: [
    { time: chart.bars[40].time, value: 1000000 },
    { time: chart.bars[41].time, value: 1000000 }
  ]
}];
assert.deepStrictEqual(chart.paneRange('price'), priceRangeWithoutViewportDrawings);
chart.document.drawings = [{
  id: 'crossing-drawing-range-test',
  type: 'trendline',
  paneId: 'price',
  visible: true,
  points: [
    { time: chart.bars[20].time, value: 1000000 },
    { time: chart.bars[60].time, value: 1000000 }
  ]
}];
assert.ok(chart.paneRange('price').max > priceRangeWithoutViewportDrawings.max * 10);
chart.document.drawings = originalDrawingsForViewportRange;
chart.fitContent();

const panesBeforeVolumeOverlay = chart.document.panes.length;
const priceRangeBeforeVolumeOverlay = chart.paneRange('price');
const volumeOverlayId = chart.addIndicator('VOLUME');
const volumeOverlay = chart.document.indicators.find((indicator) => indicator.id === volumeOverlayId);
assert.strictEqual(volumeOverlay.paneId, 'price');
assert.strictEqual(chart.document.panes.length, panesBeforeVolumeOverlay);
const priceRangeAfterVolumeOverlay = chart.paneRange('price');
assert.strictEqual(priceRangeAfterVolumeOverlay.max, priceRangeBeforeVolumeOverlay.max);
assert.strictEqual(priceRangeAfterVolumeOverlay.min, priceRangeBeforeVolumeOverlay.min);
chart.removeIndicator(volumeOverlayId);

chart.setSeriesColorOrder(['#111111', '#222222', '#333333']);
const ma20Id = chart.addIndicator('SMA', { placement: 'source', inputs: { length: 20 } });
const ma200Id = chart.addIndicator('SMA', { placement: 'source', inputs: { length: 200 } });
assert.strictEqual(chart.document.indicators.find((indicator) => indicator.id === ma20Id).styles.value.color, '#111111');
assert.strictEqual(chart.document.indicators.find((indicator) => indicator.id === ma200Id).styles.value.color, '#222222');
assert.notStrictEqual(
  chart.document.indicators.find((indicator) => indicator.id === ma20Id).styles.value.color,
  chart.document.indicators.find((indicator) => indicator.id === ma200Id).styles.value.color
);
assert.strictEqual(chart.updateIndicatorSettings(ma20Id, {
  inputs: { length: 50 },
  styles: { value: { color: '#abcdef', lineWidth: 4, lineStyle: 'dash', opacity: 0.35 } }
}), true);
const updatedMa = chart.document.indicators.find((indicator) => indicator.id === ma20Id);
assert.strictEqual(updatedMa.inputs.length, 50);
assert.strictEqual(updatedMa.styles.value.color, '#abcdef');
assert.strictEqual(updatedMa.styles.value.lineWidth, 4);
assert.strictEqual(updatedMa.styles.value.lineStyle, 'dash');
assert.strictEqual(updatedMa.styles.value.opacity, 0.35);
chart.canvas.commands = [];
chart.draw();
assert.ok(chart.canvas.commands.some((command) => command.type === 'stroke' && command.alpha === 0.35));

['AROON', 'AO', 'STOCHRSI', 'UO', 'VORTEX', 'VWMA', 'LSMA', 'FISHER', 'PPO', 'KST', 'TSI'].forEach((type) => {
  const id = chart.addIndicator(type, { placement: StockChartEngine.Indicators[type].defaultPanePolicy === 'source' ? 'source' : 'new' });
  assert.ok(chart.indicatorResults[id], `${type} should compute a result`);
  assert.ok(Object.keys(chart.indicatorResults[id].outputs).length > 0, `${type} should expose outputs`);
});

chart.setTheme('dark');
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'dark');

documentElement.dispatchEvent({ type: 'themechange', detail: { theme: 'light', isDark: false } });
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'light');
chart.toggleTheme();
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'dark');
chart.toggleTheme();
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'light');

const additionalIndicators = [
  'WMA', 'HMA', 'DEMA', 'TEMA', 'ROC', 'MOM', 'CCI', 'MFI', 'ADX', 'OBV',
  'ADL', 'CMF', 'WILLIAMS', 'DONCHIAN', 'KELTNER', 'ICHIMOKU', 'SUPERTREND',
  'PIVOTS', 'PSAR', 'TRIX'
];
additionalIndicators.forEach((type) => {
  const id = `test-${type}`;
  const result = StockChartEngine.computeIndicatorGraph(data, [{
    id,
    type,
    paneId: 'price',
    source: { kind: 'price', field: 'close' },
    inputs: {},
    styles: {},
    visible: true
  }])[id];
  assert.ok(result, `${type} should compute`);
  assert.ok(Object.keys(result.outputs).length > 0, `${type} should expose outputs`);
});

const relativeStrengthPrimaryBars = [
  { time: 100, open: 10, high: 10, low: 10, close: 10, volume: 1 },
  { time: 200, open: 11, high: 11, low: 11, close: 11, volume: 1 },
  { time: 300, open: 12, high: 12, low: 12, close: 12, volume: 1 },
  { time: 400, open: 13, high: 13, low: 13, close: 13, volume: 1 }
];
const relativeStrengthBenchmarkBars = [
  { time: 100, open: 2, high: 2, low: 2, close: 2, volume: 1 },
  { time: 300, open: 3, high: 3, low: 3, close: 3, volume: 1 },
  { time: 400, open: 0, high: 0, low: 0, close: 0, volume: 1 }
];
const relativeStrengthResult = StockChartEngine.computeIndicatorGraph(relativeStrengthPrimaryBars, [{
  id: 'test-relative-strength',
  type: 'RELATIVE_STRENGTH',
  paneId: 'relative-strength',
  inputs: { benchmark: 'spy', mode: 'rebased' },
  styles: {},
  visible: true
}], { SPY: relativeStrengthBenchmarkBars })['test-relative-strength'];
assert.ok(StockChartEngine.Indicators.RELATIVE_STRENGTH);
assert.strictEqual(StockChartEngine.Indicators.RELATIVE_STRENGTH.menuCode, 'RS');
assert.deepStrictEqual(relativeStrengthResult.outputs.value, [
  { time: 100, value: 100 },
  { time: 300, value: 80 }
]);

const rsiId = chart.addIndicator('RSI', { placement: 'new' });
chart.draw();
assert.ok(chart.legendHitZones.some((zone) => zone.indicatorId === rsiId));
const rsiLegendZone = chart.legendHitZones.find((zone) => zone.indicatorId === rsiId);
chart.handlePointerMove({ clientX: rsiLegendZone.x + 4, clientY: rsiLegendZone.y + 4 });
assert.strictEqual(chart.canvas.style.cursor, 'pointer');
const priceRectForLegendCursor = chart.getPaneRect('price');
chart.handlePointerMove({ clientX: priceRectForLegendCursor.x + priceRectForLegendCursor.width / 2, clientY: priceRectForLegendCursor.y + priceRectForLegendCursor.height / 2 });
assert.strictEqual(chart.canvas.style.cursor, 'crosshair');
const priceMarkerTime = chart.scaleMarkerTimeForPane(priceRectForLegendCursor);
assert.strictEqual(priceMarkerTime, chart.visibleBars()[chart.visibleBars().length - 1].time);
assert.notStrictEqual(priceMarkerTime, chart.legendTimeForPane(priceRectForLegendCursor));
const priceMarkerBar = chart.barNearTime(priceMarkerTime);
const priceScaleMarkers = chart.scaleMarkerItems(priceRectForLegendCursor, priceMarkerTime, chart.theme());
const closeScaleMarkers = priceScaleMarkers.filter((item) => item.kind === 'price-close');
assert.strictEqual(closeScaleMarkers.length, 1);
assert.strictEqual(closeScaleMarkers[0].value, priceMarkerBar.close);
assert.strictEqual(priceScaleMarkers.filter((item) => item.kind === 'price-open' || item.kind === 'price-high' || item.kind === 'price-low').length, 0);
const rsiPaneForMarker = chart.getPaneRect(chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId);
const rsiScaleMarkers = chart.scaleMarkerItems(rsiPaneForMarker, priceMarkerTime, chart.theme());
assert.ok(rsiScaleMarkers.some((item) => item.kind === 'indicator' && item.indicatorId === rsiId && item.output === 'value'));
const rsiRange = chart.paneRange(rsiPaneForMarker.paneId);
assert.ok(rsiRange.min > 0);
assert.ok(rsiRange.max < 100);
assert.ok(rsiRange.min <= 30);
assert.ok(rsiRange.max >= 70);
assert.deepStrictEqual(chart.scaleTicks(rsiPaneForMarker, rsiRange, 5).map((tick) => tick.value), [70, 50, 30]);
chart.canvas.commands = [];
chart.drawScale(priceRectForLegendCursor, chart.paneRange('price'), chart.theme());
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillText' && command.text === formatTestNumber(priceMarkerBar.close)));
const yAxisTickRect = chart.getPaneRect('price');
const yAxisTickRange = chart.paneRange('price');
const yAxisTicks = chart.scaleTicks(yAxisTickRect, yAxisTickRange, 5);
chart.canvas.commands = [];
chart.drawGrid(yAxisTickRect, yAxisTickRange, chart.theme());
const horizontalGridY = chart.canvas.commands
  .filter((command) => command.type === 'lineTo' && command.x === yAxisTickRect.x + yAxisTickRect.width)
  .map((command) => command.y);
assert.deepStrictEqual(horizontalGridY, yAxisTicks
  .filter((tick) => tick.y > yAxisTickRect.y + 1 && tick.y < yAxisTickRect.y + yAxisTickRect.height - 1)
  .map((tick) => Math.round(tick.y) + 0.5));
chart.canvas.commands = [];
chart.drawScale(yAxisTickRect, yAxisTickRange, chart.theme());
const yAxisLabelY = chart.canvas.commands
  .filter((command) => command.type === 'fillText' && command.x === yAxisTickRect.scaleX + 6 && Number.isFinite(parseFloat(command.text)))
  .map((command) => command.y);
assert.deepStrictEqual(yAxisLabelY, yAxisTicks.map((tick) => tick.y));
assert.ok(yAxisTicks.every((tick) => Math.abs(tick.value) >= 100 || Number.isInteger(tick.value)));
chart.setPaneScaleMode('price', 'log');
assert.deepStrictEqual(
  chart.scaleTicks(yAxisTickRect, { min: 1, max: 1000 }, 5).map((tick) => tick.value),
  [1000, 100, 10, 1]
);
assert.deepStrictEqual(
  chart.scaleTicks(yAxisTickRect, { min: 420, max: 760 }, 5).map((tick) => tick.value),
  [700, 600, 500]
);
assert.deepStrictEqual(
  chart.scaleTicks(yAxisTickRect, { min: -100, max: 100 }, 5).map((tick) => tick.value),
  [100, 10, 1, 0, -1, -10, -100]
);
chart.setPaneScaleMode('price', 'linear');
chart.openIndicatorSettingsPopup({ indicatorId: rsiId, output: 'value' }, { x: 180, y: chart.canvas.clientHeight - 4 });
assert.ok(parseFloat(chart.settingsPopup.style.top) <= chart.canvas.clientHeight - 324 - 8);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="opacity"') !== -1);
const volumePaneIndicatorId = chart.addIndicator('VOLUME', { placement: 'new' });
chart.updateIndicatorSettings(volumePaneIndicatorId, {
  inputs: { length: 20 },
  styles: { value: { color: '#2563eb', opacity: 0.55 } }
});
chart.canvas.commands = [];
chart.draw();
const volumePaneIndicator = chart.document.indicators.find((indicator) => indicator.id === volumePaneIndicatorId);
const volumeLegendPoint = chart.indicatorResults[volumePaneIndicatorId].outputs.value[chart.indicatorResults[volumePaneIndicatorId].outputs.value.length - 1];
const volumeLegendItem = chart.indicatorLegendItems(volumePaneIndicator.paneId, volumeLegendPoint.time, chart.theme()).find((item) => item.indicatorId === volumePaneIndicatorId);
assert.strictEqual(volumeLegendItem.color, chart.volumeColorForPoint(volumeLegendPoint, chart.theme()));
assert.notStrictEqual(volumeLegendItem.color, '#2563eb');
assert.strictEqual(volumeLegendItem.label.indexOf('VOLUME 20 '), -1);
assert.strictEqual(volumeLegendItem.label.indexOf('VOLUME (20) '), -1);
assert.ok(volumeLegendItem.label.indexOf('VOLUME ') === 0);
chart.openIndicatorSettingsPopup({ indicatorId: volumePaneIndicatorId, output: 'value' }, { x: 180, y: 160 });
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="opacity"') !== -1);
assert.strictEqual(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="length"'), -1);
assert.strictEqual(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="output"'), -1);
assert.strictEqual(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="color"'), -1);
assert.strictEqual(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="lineWidth"'), -1);
assert.strictEqual(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="lineStyle"'), -1);
const paneLegendTexts = chart.canvas.commands
  .filter((command) => command.type === 'fillText')
  .map((command) => command.text);
assert.ok(paneLegendTexts.some((text) => /^\d{4}-\d{2}-\d{2}  O .* C .*$/.test(text)));
assert.ok(paneLegendTexts.some((text) => /^ [+-][0-9.]+(?:K|M|B)? \([+-]\d+\.\d%\)$/.test(text)));
assert.ok(paneLegendTexts.some((text) => text.indexOf('RSI (14) ') === 0));
assert.ok(paneLegendTexts.some((text) => text.indexOf('VOLUME ') === 0));
assert.ok(!paneLegendTexts.includes('Price'));
assert.ok(!paneLegendTexts.includes('Volume'));
assert.ok(!paneLegendTexts.includes('Relative Strength Index'));
const originalIndicatorLegendItemsForLayout = chart.indicatorLegendItems;
const originalLegendTimeForLayout = chart.legendTimeForPane;
const originalBarNearTimeForLayout = chart.barNearTime;
chart.indicatorLegendItems = function testIndicatorLegendItems() {
  return [{ indicatorId: 'layout-volume', output: 'value', color: '#2563eb', label: 'VOLUME 1.00M' }];
};
chart.legendTimeForPane = function testLegendTimeForPane() {
  return data[10].time;
};
chart.barNearTime = function testBarNearTime() {
  return data[10];
};
chart.canvas.commands = [];
chart.drawPaneLegend({ paneId: 'price', x: 0, y: 0, width: 500, height: 120 }, null, chart.theme());
const priceLegendCommand = chart.canvas.commands.find((command) => command.type === 'fillText' && /^\d{4}-\d{2}-\d{2}  O /.test(command.text));
const priceVolumeLegendCommand = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === 'VOLUME 1.00M');
assert.ok(priceLegendCommand);
assert.ok(priceVolumeLegendCommand);
assert.strictEqual(priceVolumeLegendCommand.x, priceLegendCommand.x + 12);
assert.ok(priceVolumeLegendCommand.y > priceLegendCommand.y);
chart.indicatorLegendItems = originalIndicatorLegendItemsForLayout;
chart.legendTimeForPane = originalLegendTimeForLayout;
chart.barNearTime = originalBarNearTimeForLayout;
const rsiPaneRectForCrosshair = chart.getPaneRect(chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId);
const crosshairPaneRects = chart.paneRects.slice();
const crosshairX = crosshairPaneRects[0].x + Math.round(crosshairPaneRects[0].width / 3);
chart.pointer = { x: crosshairX, y: rsiPaneRectForCrosshair.y + Math.round(rsiPaneRectForCrosshair.height / 2), pointerType: 'mouse' };
const crosshairLegendTime = chart.legendTimeForPane(crosshairPaneRects[0]);
const expectedCrosshairBar = chart.barNearTime(crosshairLegendTime);
const expectedCrosshairPriceLabel = formatTestPriceLegend(chart, expectedCrosshairBar);
assert.ok(/ C [-0-9.]+ [+-][0-9.]+(?:K|M|B)? \([+-][0-9.]+%\)$/.test(expectedCrosshairPriceLabel));
const latestVisibleBar = chart.visibleBars()[chart.visibleBars().length - 1];
const latestPriceLabel = formatTestPriceLegend(chart, latestVisibleBar);
assert.notStrictEqual(expectedCrosshairPriceLabel, latestPriceLabel);
crosshairPaneRects.forEach((rect) => {
  assert.strictEqual(chart.legendTimeForPane(rect), crosshairLegendTime);
});
const expectedRsiLegendLabel = chart.indicatorLegendItems(rsiPaneRectForCrosshair.paneId, crosshairLegendTime, chart.theme())[0].label;
chart.canvas.commands = [];
chart.draw();
const crosshairLegendTexts = chart.canvas.commands.filter((command) => command.type === 'fillText').map((command) => command.text);
const crosshairBaseLabel = expectedCrosshairPriceLabel.replace(/ [+-][0-9.]+(?:K|M|B)? \([+-][0-9.]+%\)$/, '');
const crosshairChangeLabel = expectedCrosshairPriceLabel.slice(crosshairBaseLabel.length);
assert.ok(crosshairLegendTexts.includes(crosshairBaseLabel));
assert.ok(crosshairLegendTexts.includes(crosshairChangeLabel));
assert.ok(!crosshairLegendTexts.includes(latestPriceLabel));
assert.ok(crosshairLegendTexts.includes(expectedRsiLegendLabel));
chart.canvas.commands = [];
chart.drawCrosshair(chart.theme());
const verticalCrosshairSegments = chart.canvas.commands.filter((command) => command.type === 'lineTo' && command.x === crosshairX);
assert.strictEqual(verticalCrosshairSegments.length, crosshairPaneRects.length);
crosshairPaneRects.forEach((rect) => {
  assert.ok(verticalCrosshairSegments.some((command) => command.y === rect.y + rect.height));
});
assert.strictEqual(chart.canvas.commands.filter((command) => command.type === 'lineTo' && command.y === chart.pointer.y && command.x !== crosshairX).length, 1);
chart.pointer = null;
const originalBarsForPriceLegend = chart.bars;
const originalVisibleRangeForPriceLegend = chart.document.visibleRange;
chart.bars = [
  { time: 1000, open: 100, high: 101, low: 99, close: 100.69, volume: 1000 },
  { time: 2000, open: 100.1, high: 101, low: 99, close: 100.3, volume: 1000 }
];
chart.document.visibleRange = { from: 1000, to: 2000 };
chart.canvas.commands = [];
chart.drawPaneLegend({ paneId: 'price', x: 0, y: 0, width: 500, height: 120 }, null, chart.theme());
const negativeBaseText = chart.canvas.commands.find((command) => command.type === 'fillText' && /^\d{4}-\d{2}-\d{2}  O .* C 100\.30$/.test(command.text));
const negativeChangeText = chart.canvas.commands.find((command) => command.type === 'fillText' && command.text === ' -0.39 (-0.4%)');
assert.ok(negativeBaseText);
assert.ok(negativeChangeText);
assert.strictEqual(negativeChangeText.x, negativeBaseText.x + negativeBaseText.text.length * 6);
assert.strictEqual(chart.pricePercentChangeLabel(chart.bars[1]), ' -0.39 (-0.4%)');
assert.strictEqual(chart.priceLegendColor(chart.bars[1], chart.theme()), chart.theme().down);
assert.strictEqual(negativeChangeText.fillStyle, chart.theme().down);
chart.bars = originalBarsForPriceLegend;
chart.document.visibleRange = originalVisibleRangeForPriceLegend;
const volumePaneId = chart.document.indicators.find((indicator) => indicator.id === volumePaneIndicatorId).paneId;
assert.notStrictEqual(volumePaneId, 'price');
assert.strictEqual(chart.removeIndicator(volumePaneIndicatorId), true);
assert.ok(!chart.document.panes.some((pane) => pane.id === volumePaneId));
const rsiPaneId = chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId;
assert.ok(chart.paneControlHitZones.some((zone) => zone.paneId === rsiPaneId && zone.action === 'maximize'));
assert.ok(chart.paneControlsLayer.children.some((group) => group.className === 'sce-pane-control-group' && group.children.some((button) => button.innerHTML.indexOf('<svg') === 0)));
const rsiPaneOriginalIndex = chart.document.panes.findIndex((pane) => pane.id === rsiPaneId);
assert.ok(chart.movePaneUp(rsiPaneId));
assert.strictEqual(chart.document.panes.findIndex((pane) => pane.id === rsiPaneId), rsiPaneOriginalIndex - 1);
assert.ok(chart.movePaneDown(rsiPaneId));
assert.strictEqual(chart.document.panes.findIndex((pane) => pane.id === rsiPaneId), rsiPaneOriginalIndex);

chart.draw();
assert.ok(chart.canvas.commands.filter((command) => command.type === 'clip').length >= chart.paneRects.length);
const resizeZone = chart.paneResizeHitZones.find((zone) => zone.upperPaneId === 'price' || zone.lowerPaneId === 'price');
assert.ok(resizeZone);
chart.handlePointerMove({ clientX: resizeZone.x + 10, clientY: resizeZone.y + 5 });
assert.strictEqual(chart.canvas.style.cursor, 'ns-resize');
assert.deepStrictEqual(chart.hoverPaneResize, {
  upperPaneId: resizeZone.upperPaneId,
  lowerPaneId: resizeZone.lowerPaneId
});
const upperPaneBeforeResize = chart.document.panes.find((pane) => pane.id === resizeZone.upperPaneId).height;
chart.handlePointerDown({ clientX: resizeZone.x + 10, clientY: resizeZone.y + 5 });
chart.handlePointerMove({ clientX: resizeZone.x + 10, clientY: resizeZone.y + 45 });
chart.handlePointerUp();
assert.notStrictEqual(chart.document.panes.find((pane) => pane.id === resizeZone.upperPaneId).height, upperPaneBeforeResize);

chart.draw();
const maximizeZone = chart.paneControlHitZones.find((zone) => zone.paneId === rsiPaneId && zone.action === 'maximize');
chart.handleCanvasClick({ clientX: maximizeZone.x + 2, clientY: maximizeZone.y + 2 });
assert.strictEqual(chart.document.settings.maximizedPaneId, rsiPaneId);
assert.strictEqual(chart.paneRects.length, 1);
assert.strictEqual(chart.paneRects[0].paneId, rsiPaneId);
const restoreZone = chart.paneControlHitZones.find((zone) => zone.paneId === rsiPaneId && zone.action === 'maximize');
chart.handleCanvasClick({ clientX: restoreZone.x + 2, clientY: restoreZone.y + 2 });
assert.strictEqual(chart.document.settings.maximizedPaneId, null);
assert.ok(chart.paneRects.length > 1);

const temporaryPaneId = chart.addPane({ title: 'Temporary pane' });
chart.draw();
const temporaryPaneControls = chart.paneControlsLayer.children.find((group) => group.getAttribute('data-sce-pane-control-group') === temporaryPaneId);
const closeButton = temporaryPaneControls && temporaryPaneControls.children.find((button) => button.getAttribute('data-sce-pane-action') === 'close');
assert.ok(closeButton);
assert.ok(closeButton.innerHTML.indexOf('<svg') === 0);
chart.paneControlsLayer.dispatchEvent({ type: 'click', target: closeButton });
assert.ok(!chart.document.panes.some((pane) => pane.id === temporaryPaneId));

const disposableRsiId = chart.addIndicator('RSI', { placement: 'new' });
const disposableSmaId = chart.addIndicator('SMA', {
  source: { kind: 'indicator', indicatorId: disposableRsiId, output: 'value' },
  inputs: { length: 5 }
});
const disposablePaneId = chart.document.indicators.find((indicator) => indicator.id === disposableRsiId).paneId;
assert.strictEqual(chart.document.indicators.find((indicator) => indicator.id === disposableSmaId).paneId, disposablePaneId);
assert.strictEqual(chart.removeIndicator(disposableRsiId), true);
assert.ok(!chart.document.indicators.some((indicator) => indicator.id === disposableRsiId));
assert.ok(!chart.document.indicators.some((indicator) => indicator.id === disposableSmaId));
assert.ok(!chart.document.panes.some((pane) => pane.id === disposablePaneId));

const smaOnRsiId = chart.addIndicator('SMA', {
  source: { kind: 'indicator', indicatorId: rsiId, output: 'value' },
  inputs: { length: 9 }
});
assert.ok(chart.indicatorResults[rsiId].outputs.value.length > 0);
assert.ok(chart.indicatorResults[smaOnRsiId].outputs.value.length > 0);
assert.strictEqual(
  chart.document.indicators.find((indicator) => indicator.id === smaOnRsiId).paneId,
  chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId
);

const last = data[data.length - 1];
const drawingId = chart.addDrawing('trendline', [
  { time: data[data.length - 15].time, value: data[data.length - 15].close },
  { time: last.time, value: last.close }
], { paneId: 'price' });
assert.ok(chart.serialize().drawings.find((drawing) => drawing.id === drawingId));
const beforeMoveValue = chart.getDrawingById(drawingId).points[0].value;
const secondPointBeforeHandleMove = chart.getDrawingById(drawingId).points[1].value;
const screenPoint = chart.drawingScreenPoints(chart.getDrawingById(drawingId))[0];
chart.handlePointerDown({ clientX: screenPoint.x, clientY: screenPoint.y });
chart.handlePointerMove({ clientX: screenPoint.x + 16, clientY: screenPoint.y + 12 });
chart.handlePointerUp();
assert.notStrictEqual(chart.getDrawingById(drawingId).points[0].value, beforeMoveValue);
assert.strictEqual(chart.getDrawingById(drawingId).points[1].value, secondPointBeforeHandleMove);

const bodyDragPointsBefore = chart.getDrawingById(drawingId).points.map((point) => point.value);
const bodyScreenPoints = chart.drawingScreenPoints(chart.getDrawingById(drawingId));
const bodyMidpoint = {
  x: (bodyScreenPoints[0].x + bodyScreenPoints[1].x) / 2,
  y: (bodyScreenPoints[0].y + bodyScreenPoints[1].y) / 2
};
chart.handlePointerDown({ clientX: bodyMidpoint.x, clientY: bodyMidpoint.y });
chart.handlePointerMove({ clientX: bodyMidpoint.x + 10, clientY: bodyMidpoint.y + 10 });
chart.handlePointerUp();
assert.notDeepStrictEqual(chart.getDrawingById(drawingId).points.map((point) => point.value), bodyDragPointsBefore);
assert.ok(chart.indicatorLegendItems(chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId, last.time, chart.theme()).length > 0);

let drawingStylePopupOpened = false;
const currentBodyScreenPoints = chart.drawingScreenPoints(chart.getDrawingById(drawingId));
const currentBodyMidpoint = {
  x: (currentBodyScreenPoints[0].x + currentBodyScreenPoints[1].x) / 2,
  y: (currentBodyScreenPoints[0].y + currentBodyScreenPoints[1].y) / 2
};
chart.handleCanvasDoubleClick({
  clientX: currentBodyMidpoint.x,
  clientY: currentBodyMidpoint.y,
  preventDefault() {
    drawingStylePopupOpened = true;
  }
});
assert.strictEqual(drawingStylePopupOpened, true);
assert.strictEqual(chart.settingsPopup.getAttribute('hidden'), null);
assert.strictEqual(chart.settingsPopup.dataset.mode, 'drawing-style');
assert.strictEqual(chart.settingsPopup.dataset.drawingId, drawingId);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="drawingColor"') !== -1);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="drawingWidth"') !== -1);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="drawingOpacity"') !== -1);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="drawingLineStyle"') !== -1);
assert.strictEqual(chart.updateDrawingStyle(drawingId, { color: '#ef4444', lineWidth: 5, lineStyle: 'dotted', opacity: 0.4 }), true);
assert.strictEqual(chart.getDrawingById(drawingId).style.color, '#ef4444');
assert.strictEqual(chart.getDrawingById(drawingId).style.width, 5);
assert.strictEqual(chart.getDrawingById(drawingId).style.lineStyle, 'dot');
assert.strictEqual(chart.getDrawingById(drawingId).style.opacity, 0.4);
assert.strictEqual(chart.getDrawingById(drawingId).style.fill, 'rgba(239, 68, 68, 0.14)');
chart.canvas.commands = [];
chart.draw();
assert.ok(chart.canvas.commands.some((command) => command.type === 'stroke' && command.alpha === 0.4));
assert.strictEqual(chart.getShapeById(drawingId).setStyle({ color: '#22c55e', width: 3, lineStyle: 'dash', opacity: 0.6 }), true);
assert.strictEqual(chart.getDrawingById(drawingId).style.color, '#22c55e');
assert.strictEqual(chart.getDrawingById(drawingId).style.width, 3);
assert.strictEqual(chart.getDrawingById(drawingId).style.lineStyle, 'dash');
assert.strictEqual(chart.getDrawingById(drawingId).style.opacity, 0.6);

const textDrawingId = chart.addDrawing('text', [
  { time: data[data.length - 10].time, value: data[data.length - 10].close }
], { paneId: 'price', text: 'Original text' });
assert.strictEqual(chart.updateDrawingText(textDrawingId, 'Edited text'), true);
assert.strictEqual(chart.getDrawingById(textDrawingId).text, 'Edited text');
assert.strictEqual(chart.getShapeById(textDrawingId).setText('Shape handle text'), true);
assert.strictEqual(chart.getDrawingById(textDrawingId).text, 'Shape handle text');
const textPoint = chart.drawingScreenPoints(chart.getDrawingById(textDrawingId))[0];
chart.handleCanvasDoubleClick({ clientX: textPoint.x, clientY: textPoint.y, preventDefault() {} });
assert.strictEqual(chart.settingsPopup.getAttribute('hidden'), null);
assert.strictEqual(chart.settingsPopup.dataset.mode, 'drawing-text');
assert.strictEqual(chart.settingsPopup.dataset.drawingId, textDrawingId);
assert.strictEqual(chart.hitTestDrawing({ x: textPoint.x + 82, y: textPoint.y - 8 }).drawing.id, textDrawingId);

const noteHitId = chart.addDrawing('note', [
  { time: data[data.length - 34].time, value: data[data.length - 34].close }
], { paneId: 'price', text: 'Selectable note body' });
const noteHitPoint = chart.drawingScreenPoints(chart.getDrawingById(noteHitId))[0];
assert.strictEqual(chart.hitTestDrawing({ x: noteHitPoint.x + 88, y: noteHitPoint.y - 14 }).drawing.id, noteHitId);

const textHitRect = chart.getPaneRect('price');
const textHitRange = chart.paneRange('price');
const stableTextHitValue = textHitRange.min + (textHitRange.max - textHitRange.min) * 0.35;
const anchoredNoteHitId = chart.addDrawing('anchored_note', [
  { time: data[data.length - 30].time, value: stableTextHitValue }
], { paneId: 'price', text: 'Selectable anchored note' });
const anchoredNoteHitPoint = chart.drawingScreenPoints(chart.getDrawingById(anchoredNoteHitId))[0];
assert.strictEqual(chart.hitTestDrawing({ x: anchoredNoteHitPoint.x + 142, y: anchoredNoteHitPoint.y - 56 }).drawing.id, anchoredNoteHitId);

assert.strictEqual(StockChartEngine.drawingTools.comment, undefined);
assert.strictEqual(StockChartEngine.drawingTools.callout.points, 2);
const commentCalloutId = chart.addDrawing('callout', [
  { time: data[data.length - 25].time, value: data[data.length - 25].close },
  { time: data[data.length - 23].time, value: data[data.length - 23].close - 3 }
], { paneId: 'price', text: 'Callout' });
const commentCalloutDrawing = chart.getDrawingById(commentCalloutId);
const commentCalloutPoints = chart.drawingScreenPoints(commentCalloutDrawing);
const originalDrawingsForCommentCalloutHit = chart.document.drawings;
chart.document.drawings = [commentCalloutDrawing];
assert.strictEqual(chart.hitTestDrawing(commentCalloutPoints[1]).pointIndex, 1);
assert.strictEqual(chart.hitTestDrawing({
  x: commentCalloutPoints[1].x,
  y: (commentCalloutPoints[0].y + commentCalloutPoints[1].y) / 2
}).drawing.id, commentCalloutId);
chart.document.drawings = originalDrawingsForCommentCalloutHit;
chart.dragState = {
  drawingId: commentCalloutId,
  pointIndex: 1,
  paneId: 'price',
  startPointer: commentCalloutPoints[1],
  startValue: chart.valueFromPoint(commentCalloutPoints[1], 'price'),
  originalPoints: commentCalloutDrawing.points.map((point) => Object.assign({}, point)),
  moved: false
};
const movedCommentTail = {
  x: chart.xForTime(data[data.length - 22].time, textHitRect),
  y: chart.yForValue(data[data.length - 22].close - 4, textHitRect, textHitRange)
};
chart.moveSelectedDrawing(movedCommentTail);
assert.strictEqual(commentCalloutDrawing.points[0].time, chart.dragState.originalPoints[0].time);
assert.notStrictEqual(commentCalloutDrawing.points[1].time, chart.dragState.originalPoints[1].time);
chart.dragState = null;

chart.startDrawing('note');
const noteRect = chart.getPaneRect('price');
const noteRange = chart.paneRange('price');
chart.handleCanvasClick({
  clientX: chart.xForTime(data[data.length - 12].time, noteRect),
  clientY: chart.yForValue(data[data.length - 12].close, noteRect, noteRange)
});
assert.strictEqual(chart.settingsPopup.dataset.mode, 'drawing-text');
assert.ok(chart.getDrawingById(chart.selectedDrawingId).type === 'note');

const interactiveCountBefore = chart.getAllShapes().length;
chart.startDrawing('triangle');
const interactiveRect = chart.getPaneRect('price');
const interactiveRange = chart.paneRange('price');
[
  { time: data[data.length - 40].time, value: data[data.length - 40].close },
  { time: data[data.length - 34].time, value: data[data.length - 34].close + 4 },
  { time: data[data.length - 28].time, value: data[data.length - 28].close }
].forEach((point, index) => {
  chart.handleCanvasClick({
    clientX: chart.xForTime(point.time, interactiveRect),
    clientY: chart.yForValue(point.value, interactiveRect, interactiveRange)
  });
  if (index < 2) assert.strictEqual(chart.getAllShapes().length, interactiveCountBefore);
});
assert.strictEqual(chart.getAllShapes().length, interactiveCountBefore + 1);
assert.strictEqual(chart.getDrawingById(chart.selectedDrawingId).type, 'triangle');

chart.startDrawing('arrow_mark_down');
assert.strictEqual(chart.pendingDrawing.text, '');
chart.cancelDrawing();
const arrowMarkId = chart.addDrawing('arrow_mark_down', [
  { time: data[data.length - 18].time, value: data[data.length - 18].close }
], { paneId: 'price', text: 'Arrow mark down' });
chart.canvas.commands = [];
chart.drawDrawing(chart.getPaneRect('price'), chart.paneRange('price'), chart.theme(), chart.getDrawingById(arrowMarkId));
assert.ok(!chart.canvas.commands.some((command) => command.type === 'fillText' && command.text === 'Arrow mark down'));
const flagMarkId = chart.addDrawing('flag_mark', [
  { time: data[data.length - 17].time, value: data[data.length - 17].close }
], { paneId: 'price', text: 'Flag mark' });
chart.canvas.commands = [];
chart.drawDrawing(chart.getPaneRect('price'), chart.paneRange('price'), chart.theme(), chart.getDrawingById(flagMarkId));
assert.ok(!chart.canvas.commands.some((command) => command.type === 'fillText' && command.text === 'Flag mark'));

const shapeId = chart.createMultipointShape([
  { time: data[data.length - 20].time, price: data[data.length - 20].close },
  { time: last.time, price: last.close }
], { shape: 'trendline' });
assert.ok(chart.getAllShapes().find((drawing) => drawing.id === shapeId));
chart.getShapeById(shapeId).setPoints([
  { time: data[data.length - 18].time, value: data[data.length - 18].close },
  { time: last.time, value: last.close + 2 }
]);
assert.strictEqual(chart.getShapeById(shapeId).getProperties().points.length, 2);
chart.removeEntity(shapeId);
assert.ok(!chart.getAllShapes().find((drawing) => drawing.id === shapeId));

assert.ok(Object.keys(StockChartEngine.drawingTools).length >= 84);
Object.keys(StockChartEngine.drawingTools).forEach((toolId) => {
  assert.ok(StockChartEngine.drawingTools[toolId].icon.indexOf('<svg') === 0, `${toolId} should expose an icon`);
});
assert.ok(StockChartEngine.drawingToolIconSvg('trendline').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('candlestick').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('heikin_ashi').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('bar').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('line').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('area').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeIconSvg('baseline').indexOf('<svg') === 0);
assert.ok(StockChartEngine.chartTypeChevronSvg().indexOf('sce-chart-type-chevron') !== -1);
assert.strictEqual(StockChartEngine.chartTypeLabel('bar'), 'Bar');
assert.strictEqual(StockChartEngine.chartTypeLabel('heikin_ashi'), 'Heikin Ashi');
assert.ok(Object.keys(StockChartEngine.Indicators).length >= 40);
const registryShapeId = chart.createMultipointShape([
  { time: data[data.length - 22].time, price: data[data.length - 22].close },
  { time: data[data.length - 8].time, price: data[data.length - 8].close }
], { shape: 'trend_line' });
assert.strictEqual(chart.getDrawingById(registryShapeId).type, 'trendline');
assert.strictEqual(chart.setDrawingMagnetMode(true), true);
const priceRectForSnap = chart.getPaneRect('price');
const snapBar = chart.bars[chart.bars.length - 5];
const snapPoint = chart.valueFromPoint({
  x: chart.xForTime(snapBar.time + 500, priceRectForSnap),
  y: chart.yForValue(snapBar.close + 0.01, priceRectForSnap, chart.paneRange('price'))
});
assert.strictEqual(snapPoint.time, snapBar.time);
assert.strictEqual(snapPoint.value, snapBar.close);
assert.strictEqual(chart.toggleDrawingMagnetMode(), false);
chart.startDrawing('signpost');
chart.handleCanvasClick({
  clientX: chart.xForTime(snapBar.time + 500, priceRectForSnap),
  clientY: chart.yForValue(snapBar.high + 0.01, priceRectForSnap, chart.paneRange('price'))
});
const snappedSignpost = chart.getDrawingById(chart.selectedDrawingId);
assert.strictEqual(snappedSignpost.type, 'signpost');
assert.strictEqual(snappedSignpost.points[0].time, snapBar.time);
assert.ok(Math.abs(snappedSignpost.points[0].value - (snapBar.high + 0.01)) < 0.0000001);
assert.strictEqual(snappedSignpost.points[0].anchorValue, snapBar.high);
const snappedSignpostPoint = chart.drawingScreenPoints(snappedSignpost)[0];
chart.dragState = {
  drawingId: snappedSignpost.id,
  pointIndex: null,
  paneId: snappedSignpost.paneId,
  startPointer: snappedSignpostPoint,
  startValue: chart.valueFromPoint(snappedSignpostPoint, snappedSignpost.paneId),
  originalPoints: snappedSignpost.points.map((point) => Object.assign({}, point)),
  moved: false
};
chart.moveSelectedDrawing(snappedSignpostPoint);
assert.strictEqual(snappedSignpost.points[0].anchorValue, snapBar.high);
const movedSignpostBar = chart.bars[chart.bars.length - 8];
const movedSignpostPointer = {
  x: chart.xForTime(movedSignpostBar.time + 400, priceRectForSnap),
  y: chart.yForValue(movedSignpostBar.low - 0.02, priceRectForSnap, chart.paneRange('price'))
};
chart.moveSelectedDrawing(movedSignpostPointer);
assert.strictEqual(snappedSignpost.points[0].time, movedSignpostBar.time);
assert.strictEqual(snappedSignpost.points[0].anchorValue, movedSignpostBar.low);
chart.dragState = null;
chart.selectedDrawingId = registryShapeId;
assert.strictEqual(chart.moveDrawingZOrder(registryShapeId, 'front'), true);
assert.strictEqual(chart.setAllDrawingsLocked(true), true);
assert.strictEqual(chart.getAllShapes().every((drawing) => drawing.locked), true);
assert.strictEqual(chart.setAllDrawingsLocked(false), false);
assert.strictEqual(chart.setAllDrawingsVisible(false), false);
assert.strictEqual(chart.getAllShapes().every((drawing) => drawing.visible === false), true);
assert.strictEqual(chart.setAllDrawingsVisible(true), true);
assert.strictEqual(chart.setStayInDrawingMode(true), true);
assert.strictEqual(chart.toggleStayInDrawingMode(), false);
chart.removeEntity(registryShapeId);
const measurementRenderRect = chart.getPaneRect('price');
const measurementRenderRange = chart.paneRange('price');
const measurementRenderPoints = [
  { time: data[data.length - 28].time, value: data[data.length - 28].close },
  { time: data[data.length - 16].time, value: data[data.length - 16].close + 4 },
  { time: data[data.length - 8].time, value: data[data.length - 8].close - 2 }
];
const measurementRenderFourPoints = measurementRenderPoints.concat([
  { time: data[data.length - 4].time, value: data[data.length - 4].close + 1.5 }
]);
const measurementRenderSixPoints = measurementRenderFourPoints.concat([
  { time: data[data.length - 3].time, value: data[data.length - 3].close - 1 },
  { time: data[data.length - 2].time, value: data[data.length - 2].close + 2 }
]);
function renderMeasurementTool(type, points) {
  chart.canvas.commands = [];
  chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
    id: `render-${type}`,
    type,
    paneId: 'price',
    points: points || measurementRenderPoints.slice(0, StockChartEngine.drawingTools[type].points),
    style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
  });
  return chart.canvas.commands;
}
function renderSignature(type, points) {
  return renderMeasurementTool(type, points).filter((command) => [
    'fillRect',
    'fillText',
    'lineTo',
    'moveTo',
    'stroke',
    'strokeRect'
  ].includes(command.type)).map((command) => {
    const output = { type: command.type };
    ['text', 'x', 'y', 'width', 'height', 'lineWidth'].forEach((key) => {
      if (command[key] != null) output[key] = typeof command[key] === 'number' ? Math.round(command[key]) : command[key];
    });
    return output;
  });
}
function assertUniqueRenderSignatures(toolIds, points) {
  const signatures = toolIds.map((toolId) => JSON.stringify(renderSignature(toolId, points)));
  assert.strictEqual(new Set(signatures).size, signatures.length, `${toolIds.join(', ')} should not render identically`);
}
assert.ok(renderMeasurementTool('long_position').some((command) => command.type === 'fillText' && command.text.indexOf('Long ') === 0));
assert.ok(renderMeasurementTool('short_position').some((command) => command.type === 'fillText' && command.text.indexOf('Short ') === 0));
assert.ok(renderMeasurementTool('position_forecast').some((command) => command.type === 'fillText' && command.text.indexOf('Forecast ') === 0));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-long-position',
  type: 'date_and_price_range',
  paneId: 'price',
  text: 'Long position',
  points: measurementRenderPoints.slice(0, 2),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillText' && command.text.indexOf('Long ') === 0));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-short-position',
  type: 'date_and_price_range',
  paneId: 'price',
  text: 'Short position',
  points: measurementRenderPoints.slice(0, 2),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillText' && command.text.indexOf('Short ') === 0));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-position-forecast',
  type: 'date_and_price_range',
  paneId: 'price',
  text: 'Position forecast',
  points: measurementRenderPoints.slice(0, 2),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillText' && command.text.indexOf('Forecast ') === 0));
assert.ok(renderMeasurementTool('date_range').some((command) => command.type === 'fillText' && /\d+d$/.test(command.text)));
assert.ok(renderMeasurementTool('price_range').some((command) => command.type === 'fillText' && command.text.indexOf('%') !== -1));
assert.ok(renderMeasurementTool('date_and_price_range').some((command) => command.type === 'fillText' && command.text.indexOf(' / ') !== -1));
assert.ok(renderMeasurementTool('bars_pattern').filter((command) => command.type === 'strokeRect').length >= 8);
assert.ok(renderMeasurementTool('ghost_feed').filter((command) => command.type === 'strokeRect').length >= 9);
assert.ok(renderMeasurementTool('sector', measurementRenderPoints).some((command) => command.type === 'stroke'));
assert.ok(renderMeasurementTool('fixed_range_volume_profile').filter((command) => command.type === 'fillRect').length >= 8);
const gannBoxCommands = renderMeasurementTool('gann_box', measurementRenderPoints.slice(0, 2));
const gannSquareCommands = renderMeasurementTool('gann_square', measurementRenderPoints.slice(0, 2));
const gannSquareFixedCommands = renderMeasurementTool('gann_square_fixed', measurementRenderPoints.slice(0, 2));
const gannBoxRect = gannBoxCommands.find((command) => command.type === 'strokeRect');
const gannSquareRect = gannSquareCommands.find((command) => command.type === 'strokeRect');
const gannSquareFixedRect = gannSquareFixedCommands.find((command) => command.type === 'strokeRect');
assert.ok(gannBoxRect);
assert.ok(gannSquareRect);
assert.ok(gannSquareFixedRect);
assert.notStrictEqual(Math.round(gannBoxRect.width), Math.round(gannBoxRect.height));
assert.strictEqual(Math.round(gannSquareRect.width), Math.round(gannSquareRect.height));
assert.strictEqual(Math.round(gannSquareFixedRect.width), Math.round(gannSquareFixedRect.height));
assert.notStrictEqual(Math.round(gannSquareRect.width), Math.round(gannSquareFixedRect.width));
assert.ok(gannSquareFixedCommands.filter((command) => command.type === 'lineTo').length > gannSquareCommands.filter((command) => command.type === 'lineTo').length);
const fibWedgeCommands = renderMeasurementTool('fib_wedge', measurementRenderPoints.slice(0, 3));
assert.ok(fibWedgeCommands.filter((command) => command.type === 'quadraticCurveTo').length >= 12);
assert.ok(fibWedgeCommands.filter((command) => command.type === 'fill').length >= 6);
assert.deepStrictEqual(fibWedgeCommands.filter((command) => command.type === 'fillText').map((command) => command.text), ['0.236', '0.382', '0.5', '0.618', '0.786', '1']);
assertUniqueRenderSignatures(['parallel_channel', 'regression_trend', 'flat_top_bottom', 'disjoint_channel'], measurementRenderFourPoints);
assertUniqueRenderSignatures(['pitchfork', 'schiff_pitchfork', 'modified_schiff_pitchfork', 'inside_pitchfork'], measurementRenderPoints);
function commandIncludesPoint(commands, type, point) {
  return commands.some((command) => command.type === type && Math.abs(command.x - point.x) < 0.01 && Math.abs(command.y - point.y) < 0.01);
}
const pitchforkScreenPoints = chart.drawingScreenPoints({
  type: 'pitchfork',
  paneId: 'price',
  points: measurementRenderPoints
});
const pitchforkA = pitchforkScreenPoints[0];
const pitchforkB = pitchforkScreenPoints[1];
const pitchforkC = pitchforkScreenPoints[2];
const standardPitchforkCommands = renderMeasurementTool('pitchfork');
assert.ok(standardPitchforkCommands.some((command) => command.type === 'fill'));
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkA));
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkB));
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkC));
const pitchforkChannelMidpoint = { x: (pitchforkB.x + pitchforkC.x) / 2, y: (pitchforkB.y + pitchforkC.y) / 2 };
const pitchforkInnerUpper = { x: pitchforkChannelMidpoint.x + (pitchforkB.x - pitchforkChannelMidpoint.x) * 0.5, y: pitchforkChannelMidpoint.y + (pitchforkB.y - pitchforkChannelMidpoint.y) * 0.5 };
const pitchforkInnerLower = { x: pitchforkChannelMidpoint.x + (pitchforkC.x - pitchforkChannelMidpoint.x) * 0.5, y: pitchforkChannelMidpoint.y + (pitchforkC.y - pitchforkChannelMidpoint.y) * 0.5 };
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkChannelMidpoint));
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkInnerUpper));
assert.ok(commandIncludesPoint(standardPitchforkCommands, 'moveTo', pitchforkInnerLower));
assert.ok(standardPitchforkCommands.filter((command) => command.type === 'fill').length >= 2);
const schiffHandleStart = { x: pitchforkA.x, y: (pitchforkA.y + pitchforkB.y) / 2 };
const schiffPitchforkCommands = renderMeasurementTool('schiff_pitchfork');
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'moveTo', schiffHandleStart));
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'lineTo', pitchforkB));
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'lineTo', pitchforkC));
const schiffChannelMidpoint = { x: (pitchforkB.x + pitchforkC.x) / 2, y: (pitchforkB.y + pitchforkC.y) / 2 };
const schiffInnerUpper = { x: schiffChannelMidpoint.x + (pitchforkB.x - schiffChannelMidpoint.x) * 0.5, y: schiffChannelMidpoint.y + (pitchforkB.y - schiffChannelMidpoint.y) * 0.5 };
const schiffInnerLower = { x: schiffChannelMidpoint.x + (pitchforkC.x - schiffChannelMidpoint.x) * 0.5, y: schiffChannelMidpoint.y + (pitchforkC.y - schiffChannelMidpoint.y) * 0.5 };
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'moveTo', schiffChannelMidpoint));
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'moveTo', schiffInnerUpper));
assert.ok(commandIncludesPoint(schiffPitchforkCommands, 'moveTo', schiffInnerLower));
assert.ok(schiffPitchforkCommands.filter((command) => command.type === 'fill').length >= 2);
const modifiedSchiffHandleStart = { x: (pitchforkA.x + pitchforkB.x) / 2, y: (pitchforkA.y + pitchforkB.y) / 2 };
const modifiedSchiffPitchforkCommands = renderMeasurementTool('modified_schiff_pitchfork');
assert.ok(commandIncludesPoint(modifiedSchiffPitchforkCommands, 'moveTo', modifiedSchiffHandleStart));
assert.ok(commandIncludesPoint(modifiedSchiffPitchforkCommands, 'moveTo', pitchforkChannelMidpoint));
assert.ok(commandIncludesPoint(modifiedSchiffPitchforkCommands, 'moveTo', pitchforkInnerUpper));
assert.ok(commandIncludesPoint(modifiedSchiffPitchforkCommands, 'moveTo', pitchforkInnerLower));
assert.ok(modifiedSchiffPitchforkCommands.filter((command) => command.type === 'fill').length >= 2);
const insidePitchforkCommands = renderMeasurementTool('inside_pitchfork');
const insideHandleMidpoint = { x: (pitchforkA.x + pitchforkB.x) / 2, y: (pitchforkA.y + pitchforkB.y) / 2 };
const insideChannelMidpoint = { x: (pitchforkB.x + pitchforkC.x) / 2, y: (pitchforkB.y + pitchforkC.y) / 2 };
const insideChannelMiddleEnd = {
  x: measurementRenderRect.x + measurementRenderRect.width,
  y: insideChannelMidpoint.y + (pitchforkC.y - insideHandleMidpoint.y) * ((measurementRenderRect.x + measurementRenderRect.width - insideChannelMidpoint.x) / (pitchforkC.x - insideHandleMidpoint.x))
};
const insideInnerUpper = { x: insideChannelMidpoint.x + (pitchforkB.x - insideChannelMidpoint.x) * 0.5, y: insideChannelMidpoint.y + (pitchforkB.y - insideChannelMidpoint.y) * 0.5 };
const insideInnerLower = { x: insideChannelMidpoint.x + (pitchforkC.x - insideChannelMidpoint.x) * 0.5, y: insideChannelMidpoint.y + (pitchforkC.y - insideChannelMidpoint.y) * 0.5 };
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', pitchforkA));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', pitchforkB));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', pitchforkC));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', insideHandleMidpoint));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'lineTo', pitchforkC));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', insideChannelMidpoint));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'lineTo', insideChannelMiddleEnd));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', insideInnerUpper));
assert.ok(commandIncludesPoint(insidePitchforkCommands, 'moveTo', insideInnerLower));
assert.ok(insidePitchforkCommands.filter((command) => command.type === 'fill').length >= 2);
assert.notStrictEqual(StockChartEngine.drawingTools.pitchfork.icon, StockChartEngine.drawingTools.schiff_pitchfork.icon);
assert.ok(StockChartEngine.drawingTools.pitchfork.icon.indexOf('M6 5 22 1') !== -1);
assert.ok(StockChartEngine.drawingTools.pitchfork.icon.indexOf('M3 13 22 8.25') !== -1);
assert.ok(StockChartEngine.drawingTools.pitchfork.icon.indexOf('M10 18 22 15') !== -1);
assert.ok(StockChartEngine.drawingTools.pitchfork.icon.indexOf('M6 5 10 18') !== -1);
assert.ok(StockChartEngine.drawingTools.schiff_pitchfork.icon.indexOf('stroke-dasharray') === -1);
assert.notStrictEqual(StockChartEngine.drawingTools.schiff_pitchfork.icon, StockChartEngine.drawingTools.modified_schiff_pitchfork.icon);
assert.ok(StockChartEngine.drawingTools.modified_schiff_pitchfork.icon.indexOf('M3 21 6.5 13') !== -1);
assert.notStrictEqual(StockChartEngine.drawingTools.modified_schiff_pitchfork.icon, StockChartEngine.drawingTools.inside_pitchfork.icon);
assert.strictEqual((StockChartEngine.drawingTools.pitchfan.icon.match(/<path/g) || []).length, 6);
assert.ok(StockChartEngine.drawingTools.pitchfan.icon.indexOf('M3 19 22 2') !== -1);
assert.ok(StockChartEngine.drawingTools.pitchfan.icon.indexOf('M11 12 14 17') !== -1);
assert.ok(StockChartEngine.drawingTools.gann_box.icon.indexOf('width="16" height="12"') !== -1);
assert.ok(StockChartEngine.drawingTools.gann_square.icon.indexOf('width="16" height="16"') !== -1);
assert.ok(StockChartEngine.drawingTools.gann_square_fixed.icon.indexOf('x="7" y="7" width="10" height="10"') !== -1);
assert.notStrictEqual(StockChartEngine.drawingTools.gann_box.icon, StockChartEngine.drawingTools.gann_square.icon);
assert.notStrictEqual(StockChartEngine.drawingTools.gann_square.icon, StockChartEngine.drawingTools.gann_square_fixed.icon);
assert.strictEqual((StockChartEngine.drawingTools.fib_wedge.icon.match(/<path/g) || []).length, 6);
assert.ok(StockChartEngine.drawingTools.fib_wedge.icon.indexOf('Q') !== -1);
assertUniqueRenderSignatures(['gann_fan', 'fib_speed_resistance_fan', 'pitchfan'], measurementRenderPoints);
assertUniqueRenderSignatures(['fib_time_zone', 'trend_based_fib_time', 'cyclic_lines', 'time_cycles'], measurementRenderPoints);
assertUniqueRenderSignatures(['circle', 'ellipse'], measurementRenderPoints.slice(0, 2));
assertUniqueRenderSignatures(['text', 'note', 'anchored_note', 'signpost', 'callout', 'price_label', 'price_note'], measurementRenderPoints);
const noteCommands = renderMeasurementTool('note', measurementRenderPoints);
assert.ok(noteCommands.some((command) => command.type === 'strokeRect'));
const anchoredNoteCommands = renderMeasurementTool('anchored_note', measurementRenderPoints);
assert.ok(anchoredNoteCommands.some((command) => command.type === 'strokeRect'));
assert.ok(anchoredNoteCommands.some((command) => command.type === 'lineTo'));
const signpostCommands = renderMeasurementTool('signpost', measurementRenderPoints);
const signpostAnchorPoint = chart.drawingScreenPoints({
  type: 'signpost',
  paneId: 'price',
  points: measurementRenderPoints.slice(0, 1)
})[0];
assert.ok(signpostCommands.some((command) => command.type === 'lineTo' && Math.round(command.x) === Math.round(signpostAnchorPoint.x) && command.y < signpostAnchorPoint.y));
assert.ok(signpostCommands.some((command) => command.type === 'quadraticCurveTo'));
assert.ok(signpostCommands.some((command) => command.type === 'fillText' && command.text === 'Signpost'));
const selectableSignpost = {
  id: 'selectable-signpost',
  type: 'signpost',
  paneId: 'price',
  text: 'Signpost',
  points: [{
    time: measurementRenderPoints[0].time,
    value: measurementRenderPoints[0].value,
    anchorValue: measurementRenderPoints[0].value + 3
  }],
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
};
const originalDrawingsForSignpostHit = chart.document.drawings;
chart.document.drawings = [selectableSignpost];
const selectableSignpostPoint = chart.drawingScreenPoints(selectableSignpost)[0];
assert.strictEqual(chart.hitTestDrawing({ x: selectableSignpostPoint.x, y: selectableSignpostPoint.y }).drawing, selectableSignpost);
const selectableSignpostRect = chart.getPaneRect('price');
const selectableSignpostRange = chart.paneRange('price');
const selectableSignpostAnchorY = chart.yForValue(selectableSignpost.points[0].anchorValue, selectableSignpostRect, selectableSignpostRange);
const selectableSignpostBubbleTop = selectableSignpostPoint.y - 15;
assert.strictEqual(chart.hitTestDrawing({
  x: selectableSignpostPoint.x,
  y: (selectableSignpostAnchorY + selectableSignpostBubbleTop) / 2
}).drawing, selectableSignpost);
chart.document.drawings = originalDrawingsForSignpostHit;
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-note-shape',
  type: 'text',
  paneId: 'price',
  text: 'Note',
  points: measurementRenderPoints.slice(0, 1),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect'));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-anchored-note-shape',
  type: 'text',
  paneId: 'price',
  text: 'Anchored note',
  points: measurementRenderPoints.slice(0, 1),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'strokeRect'));
assert.ok(chart.canvas.commands.some((command) => command.type === 'lineTo'));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-comment-shape',
  type: 'comment',
  paneId: 'price',
  text: 'Comment',
  points: measurementRenderPoints.slice(0, 2),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillRect'));
assert.ok(chart.canvas.commands.some((command) => command.type === 'lineTo'));
chart.canvas.commands = [];
chart.drawDrawing(measurementRenderRect, measurementRenderRange, chart.theme(), {
  id: 'legacy-signpost-shape',
  type: 'callout',
  paneId: 'price',
  text: 'Signpost',
  points: measurementRenderPoints.slice(0, 1),
  style: { color: '#123456', width: 2, fill: 'rgba(18, 52, 86, 0.1)' }
});
assert.ok(chart.canvas.commands.some((command) => command.type === 'lineTo'));
assert.ok(chart.canvas.commands.some((command) => command.type === 'quadraticCurveTo'));
assertUniqueRenderSignatures(['brush', 'highlighter', 'path'], measurementRenderPoints);
assertUniqueRenderSignatures(['triangle', 'triangle_pattern'], measurementRenderPoints);
assertUniqueRenderSignatures(['arrow_marker', 'icon', 'sticker', 'emoji'], measurementRenderPoints);
assertUniqueRenderSignatures(['xabcd_pattern', 'cypher_pattern', 'abcd_pattern', 'three_drives_pattern', 'head_and_shoulders'], measurementRenderSixPoints);
assertUniqueRenderSignatures(['elliott_triangle_wave', 'elliott_triple_combo_wave', 'elliott_correction_wave', 'elliott_double_combo_wave'], measurementRenderSixPoints);
const correctionLabels = renderMeasurementTool('elliott_correction_wave', measurementRenderPoints)
  .filter((command) => command.type === 'fillText')
  .map((command) => command.text);
assert.deepStrictEqual(correctionLabels.slice(-3), ['A', 'B', 'C']);
const doubleComboLabels = renderMeasurementTool('elliott_double_combo_wave', measurementRenderPoints)
  .filter((command) => command.type === 'fillText')
  .map((command) => command.text);
assert.deepStrictEqual(doubleComboLabels.slice(-3), ['W', 'X', 'Y']);
const cypherLabels = renderMeasurementTool('cypher_pattern', measurementRenderFourPoints.concat([
  { time: data[data.length - 2].time, value: data[data.length - 2].close - 1 }
]))
  .filter((command) => command.type === 'fillText')
  .map((command) => command.text);
assert.ok(cypherLabels.includes('Cypher'));
assert.deepStrictEqual(cypherLabels.filter((label) => ['X', 'A', 'B', 'C', 'D'].includes(label)).slice(0, 5), ['X', 'A', 'B', 'C', 'D']);

Object.keys(StockChartEngine.drawingTools).forEach((toolId, toolIndex) => {
  const tool = StockChartEngine.drawingTools[toolId];
  const pointCount = Math.max(1, tool.points || 1);
  const points = Array.from({ length: pointCount }, (_, pointIndex) => {
    const index = Math.max(0, data.length - 80 + toolIndex % 12 + pointIndex * 4);
    return { time: data[index].time, value: data[index].close + pointIndex * 0.3 };
  });
  const id = chart.addDrawing(toolId, points, {
    paneId: 'price',
    text: tool.name,
    style: { color: '#123456', width: 2 }
  });
  assert.strictEqual(chart.getDrawingById(id).type, toolId);
});

assert.ok(chart.getAllShapes().length > 0);
chart.toolbar.dispatchEvent({
  type: 'click',
  target: {
    getAttribute(name) {
      return name === 'data-sce-action' ? 'clear-drawings' : null;
    }
  }
});
assert.strictEqual(chart.getAllShapes().length, 0);
chart.addDrawing('text', [{ time: last.time, value: last.close }], { paneId: 'price', text: 'Saved annotation' });

assert.strictEqual(chart.save(), true);
const savedLayout = chart.storage.load('default');
assert.ok(savedLayout.drawings.length > 0);
assert.deepStrictEqual(savedLayout.panes.map((pane) => pane.id), chart.document.panes.map((pane) => pane.id));
assert.deepStrictEqual(savedLayout.panes.map((pane) => pane.height), chart.document.panes.map((pane) => pane.height));
const exportedPng = chart.exportImage();
assert.ok(exportedPng.indexOf('data:image/png;base64,') === 0);
const exportedJpeg = chart.exportImage({ type: 'image/jpeg', quality: 0.8, includeInteraction: true });
assert.ok(exportedJpeg.indexOf('data:image/jpeg;base64,') === 0);
assert.strictEqual(chart.canvas.exportCalls[chart.canvas.exportCalls.length - 1].quality, 0.8);
const exportedLayout = chart.exportLayout({ includeData: true });
assert.strictEqual(exportedLayout.type, 'stock-chart-engine-layout');
assert.ok(exportedLayout.document.drawings.length > 0);
assert.strictEqual(exportedLayout.data.length, data.length);
const exportedLayoutJson = chart.exportLayoutJson({ includeData: true, pretty: false });
chart.removeAllShapes();
assert.strictEqual(chart.getAllShapes().length, 0);
chart.importLayout(exportedLayoutJson);
assert.ok(chart.getAllShapes().length > 0);
assert.strictEqual(chart.sourceBars.length, data.length);
const shareUrl = chart.createShareUrl({ baseUrl: 'https://example.test/chart.html', includeData: true, maxLength: 100000 });
assert.ok(shareUrl.indexOf('https://example.test/chart.html#sce-layout=') === 0);
const sharePayload = JSON.parse(decodeURIComponent(shareUrl.split('#sce-layout=')[1]));
assert.strictEqual(Object.prototype.hasOwnProperty.call(sharePayload, 'data'), false);
const bestShareUrl = chart.createBestShareUrl({ baseUrl: 'https://example.test/chart.html', maxLength: 100000 });
assert.ok(bestShareUrl.url.indexOf('https://example.test/chart.html#sce-layout=') === 0);
assert.strictEqual(bestShareUrl.includeData, false);
const layoutOnlyShareUrl = chart.createShareUrl({ baseUrl: 'https://example.test/chart.html', includeData: false, maxLength: 16000 });
assert.ok(layoutOnlyShareUrl.length < 16000);
const layoutOnlyPayload = JSON.parse(decodeURIComponent(layoutOnlyShareUrl.split('#sce-layout=')[1]));
assert.strictEqual(layoutOnlyPayload.type, 'stock-chart-engine-layout');
assert.ok(layoutOnlyPayload.document.drawings.length > 0);
assert.strictEqual(Object.prototype.hasOwnProperty.call(layoutOnlyPayload, 'data'), false);

chart.destroy();

global.window.Worker = FakePineWorker;
const workerChart = new StockChartEngine.Chart('#chart', {
  data,
  symbol: 'WORKER_TEST',
  interval: '1D',
  load: false,
  autosave: false
});
const workerIndicatorId = workerChart.addIndicator('PINE_SCRIPT', {
  placement: 'new',
  inputs: { code: pineSource, title: 'Worker RSI Average' }
});
assert.ok(workerChart.pineWorker instanceof FakePineWorker);
assert.ok(workerChart.pineWorker.url.indexOf('pine-script-worker.js') !== -1);
assert.ok(workerChart.pineWorker.messages.length > 0);
assert.strictEqual(workerChart.indicatorResults[workerIndicatorId].computeMode, 'worker');
const workerAdvancedIndicatorId = workerChart.addIndicator('PINE_SCRIPT', {
  placement: 'new',
  inputs: {
    code: `indicator("Worker drawings")
plotshape(close > open, style=shape.triangleup, location=location.belowbar, color=color.green)
label.new(bar_index, high, "Now")`,
    title: 'Worker drawings'
  }
});
assert.strictEqual(workerChart.indicatorResults[workerAdvancedIndicatorId].computeMode, 'worker');
assert.ok(workerChart.indicatorResults[workerAdvancedIndicatorId].drawings.length === 1);
const workerRequestCount = workerChart.pineWorker.messages.length;
workerChart.setData(data.slice(0, 45));
assert.ok(workerChart.pineWorker.messages.length > workerRequestCount);
assert.strictEqual(workerChart.indicatorResults[workerIndicatorId].computeMode, 'worker');
const workerInstance = workerChart.pineWorker;
workerChart.destroy();
assert.strictEqual(workerInstance.terminated, true);
delete global.window.Worker;

const darkDom = createFakeDom();
darkDom.documentElement.setAttribute('data-theme', 'dark');
const darkChart = new StockChartEngine.Chart('#chart', {
  data,
  symbol: 'TEST',
  interval: '1D',
  load: false,
  autosave: false
});
assert.strictEqual(darkChart.root.getAttribute('data-sce-theme'), 'dark');
darkDom.documentElement.dispatchEvent({ type: 'themechange', detail: { theme: 'light', isDark: false } });
assert.strictEqual(darkChart.root.getAttribute('data-sce-theme'), 'light');
darkDom.documentElement.dispatchEvent({ type: 'themechange', detail: { theme: 'dark', isDark: true } });
assert.strictEqual(darkChart.root.getAttribute('data-sce-theme'), 'dark');
const persistedLightDocument = darkChart.serialize();
persistedLightDocument.theme = 'light';
const siteThemedChart = new StockChartEngine.Chart('#chart', {
  data,
  document: persistedLightDocument,
  theme: 'dark',
  load: false,
  autosave: false
});
assert.strictEqual(siteThemedChart.document.theme, 'dark');
assert.strictEqual(siteThemedChart.root.getAttribute('data-sce-theme'), 'dark');
siteThemedChart.destroy();
darkChart.destroy();
