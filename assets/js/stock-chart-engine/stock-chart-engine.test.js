const assert = require('assert');
const StockChartEngine = require('./stock-chart-engine.js');

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
    if (value && value.indexOf('sce-title') !== -1) {
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
    return this.attributes[name] || null;
  }

  querySelector(selector) {
    if (selector.charAt(0) === '.') {
      const className = selector.slice(1);
      return findElement(this, (element) => (element.className || '').split(/\s+/).includes(className));
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
        canvas.commands.push({ type: 'fillText', text: String(text), x, y, alpha: this.globalAlpha });
      },
      lineTo(x, y) {
        canvas.commands.push({ type: 'lineTo', x, y, alpha: this.globalAlpha });
      },
      moveTo(x, y) {
        canvas.commands.push({ type: 'moveTo', x, y, alpha: this.globalAlpha });
      },
      quadraticCurveTo() {},
      restore() {
        this.globalAlpha = this._alphaStack.length ? this._alphaStack.pop() : 1;
      },
      save() {
        this._alphaStack.push(this.globalAlpha);
      },
      setLineDash() {},
      setTransform() {},
      stroke() {
        canvas.commands.push({ type: 'stroke', alpha: this.globalAlpha, strokeStyle: this.strokeStyle });
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

function formatTestDate(time) {
  const date = new Date(time * 1000);
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function formatTestPriceLegend(chart, bar) {
  const previous = chart.previousBarForTime(bar.time);
  const percent = previous && previous.close ? ((bar.close - previous.close) / previous.close) * 100 : null;
  const suffix = percent == null || !Number.isFinite(percent) ? '' : ` (${percent >= 0 ? '+' : ''}${percent.toFixed(1)}%)`;
  return `${formatTestDate(bar.time)}  O ${formatTestNumber(bar.open)} H ${formatTestNumber(bar.high)} L ${formatTestNumber(bar.low)} C ${formatTestNumber(bar.close)}${suffix}`;
}

const { documentElement } = createFakeDom();
documentElement.setAttribute('data-theme', 'light');

const data = StockChartEngine.createDemoData(90);
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
  autosave: false
});
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
assert.strictEqual(chart.toolbar.innerHTML.indexOf('data-sce-action="save"'), -1);
assert.strictEqual(chart.setPaneScaleMode('price', 'log'), 'log');
assert.strictEqual(chart.paneScaleMode('price'), 'log');
assert.strictEqual(chart.yForValue(0, chart.getPaneRect('price'), chart.paneRange('price')), null);
assert.strictEqual(chart.yForValue(-1, chart.getPaneRect('price'), chart.paneRange('price')), null);
assert.ok(chart.scaleHitZones.some((zone) => zone.paneId === 'price'));
assert.strictEqual(chart.togglePaneScaleMode('price'), 'linear');
assert.strictEqual(chart.paneScaleMode('price'), 'linear');

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
chart.fitContent();
chart.draw();

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

const rsiId = chart.addIndicator('RSI', { placement: 'new' });
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
chart.canvas.commands = [];
chart.drawScale(priceRectForLegendCursor, chart.paneRange('price'), chart.theme());
assert.ok(chart.canvas.commands.some((command) => command.type === 'fillText' && command.text === formatTestNumber(priceMarkerBar.close)));
chart.openIndicatorSettingsPopup({ indicatorId: rsiId, output: 'value' }, { x: 180, y: chart.canvas.clientHeight - 4 });
assert.ok(parseFloat(chart.settingsPopup.style.top) <= chart.canvas.clientHeight - 324 - 8);
assert.ok(chart.settingsPopup.innerHTML.indexOf('data-sce-popup-field="opacity"') !== -1);
const volumePaneIndicatorId = chart.addIndicator('VOLUME', { placement: 'new' });
chart.updateIndicatorSettings(volumePaneIndicatorId, {
  styles: { value: { color: '#2563eb', opacity: 0.55 } }
});
chart.canvas.commands = [];
chart.draw();
const volumePaneIndicator = chart.document.indicators.find((indicator) => indicator.id === volumePaneIndicatorId);
const volumeLegendPoint = chart.indicatorResults[volumePaneIndicatorId].outputs.value[chart.indicatorResults[volumePaneIndicatorId].outputs.value.length - 1];
const volumeLegendItem = chart.indicatorLegendItems(volumePaneIndicator.paneId, volumeLegendPoint.time, chart.theme()).find((item) => item.indicatorId === volumePaneIndicatorId);
assert.strictEqual(volumeLegendItem.color, chart.volumeColorForPoint(volumeLegendPoint, chart.theme()));
assert.notStrictEqual(volumeLegendItem.color, '#2563eb');
const paneLegendTexts = chart.canvas.commands
  .filter((command) => command.type === 'fillText')
  .map((command) => command.text);
assert.ok(paneLegendTexts.some((text) => /^\d{4}-\d{2}-\d{2}  O .* C .* \([+-]\d+\.\d%\)$/.test(text)));
assert.ok(paneLegendTexts.some((text) => text.indexOf('RSI ') === 0));
assert.ok(paneLegendTexts.some((text) => text.indexOf('VOLUME ') === 0));
assert.ok(!paneLegendTexts.includes('Price'));
assert.ok(!paneLegendTexts.includes('Volume'));
assert.ok(!paneLegendTexts.includes('Relative Strength Index'));
const rsiPaneRectForCrosshair = chart.getPaneRect(chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId);
const crosshairPaneRects = chart.paneRects.slice();
const crosshairX = crosshairPaneRects[0].x + Math.round(crosshairPaneRects[0].width / 3);
chart.pointer = { x: crosshairX, y: rsiPaneRectForCrosshair.y + Math.round(rsiPaneRectForCrosshair.height / 2), pointerType: 'mouse' };
const crosshairLegendTime = chart.legendTimeForPane(crosshairPaneRects[0]);
const expectedCrosshairBar = chart.barNearTime(crosshairLegendTime);
const expectedCrosshairPriceLabel = formatTestPriceLegend(chart, expectedCrosshairBar);
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
assert.ok(crosshairLegendTexts.includes(expectedCrosshairPriceLabel));
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
const volumePaneId = chart.document.indicators.find((indicator) => indicator.id === volumePaneIndicatorId).paneId;
assert.notStrictEqual(volumePaneId, 'price');
assert.strictEqual(chart.removeIndicator(volumePaneIndicatorId), true);
assert.ok(!chart.document.panes.some((pane) => pane.id === volumePaneId));
const rsiPaneId = chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId;
assert.ok(chart.paneControlHitZones.some((zone) => zone.paneId === rsiPaneId && zone.action === 'maximize'));
assert.ok(chart.paneControlsLayer.children.some((button) => button.innerHTML.indexOf('<svg') === 0));
const rsiPaneOriginalIndex = chart.document.panes.findIndex((pane) => pane.id === rsiPaneId);
assert.ok(chart.movePaneUp(rsiPaneId));
assert.strictEqual(chart.document.panes.findIndex((pane) => pane.id === rsiPaneId), rsiPaneOriginalIndex - 1);
assert.ok(chart.movePaneDown(rsiPaneId));
assert.strictEqual(chart.document.panes.findIndex((pane) => pane.id === rsiPaneId), rsiPaneOriginalIndex);

chart.draw();
const resizeZone = chart.paneResizeHitZones.find((zone) => zone.upperPaneId === 'price' || zone.lowerPaneId === 'price');
assert.ok(resizeZone);
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
const closeButton = chart.paneControlsLayer.children.find((button) => {
  return button.getAttribute('data-sce-pane-id') === temporaryPaneId && button.getAttribute('data-sce-pane-action') === 'close';
});
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
const layoutOnlyShareUrl = chart.createShareUrl({ baseUrl: 'https://example.test/chart.html', includeData: false, maxLength: 16000 });
assert.ok(layoutOnlyShareUrl.length < 16000);
const layoutOnlyPayload = JSON.parse(decodeURIComponent(layoutOnlyShareUrl.split('#sce-layout=')[1]));
assert.strictEqual(layoutOnlyPayload.type, 'stock-chart-engine-layout');
assert.ok(layoutOnlyPayload.document.drawings.length > 0);
assert.strictEqual(Object.prototype.hasOwnProperty.call(layoutOnlyPayload, 'data'), false);

chart.destroy();

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
darkChart.destroy();
