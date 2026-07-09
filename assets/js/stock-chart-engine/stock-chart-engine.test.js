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
}

class FakeElement {
  constructor(tagName) {
    this.tagName = tagName;
    this.children = [];
    this.attributes = {};
    this.style = {};
    this.className = '';
    this.classList = new FakeClassList(this);
    this.listeners = {};
    this.parentNode = null;
    this.textContent = '';
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
  }

  getContext() {
    const noop = () => {};
    return {
      beginPath: noop,
      clearRect: noop,
      closePath: noop,
      fill: noop,
      fillRect: noop,
      fillText: noop,
      lineTo: noop,
      moveTo: noop,
      restore: noop,
      save: noop,
      setLineDash: noop,
      setTransform: noop,
      stroke: noop,
      strokeRect: noop
    };
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

const { documentElement } = createFakeDom();
documentElement.setAttribute('data-theme', 'light');

const data = StockChartEngine.createDemoData(90);
const chart = new StockChartEngine.Chart('#chart', {
  data,
  symbol: 'TEST',
  interval: '1D',
  load: false,
  autosave: false
});

assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'light');

chart.setTheme('dark');
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'dark');

documentElement.dispatchEvent({ type: 'themechange', detail: { theme: 'light', isDark: false } });
assert.strictEqual(chart.root.getAttribute('data-sce-theme'), 'light');

const rsiId = chart.addIndicator('RSI', { placement: 'new' });
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
const screenPoint = chart.drawingScreenPoints(chart.getDrawingById(drawingId))[0];
chart.handlePointerDown({ clientX: screenPoint.x, clientY: screenPoint.y });
chart.handlePointerMove({ clientX: screenPoint.x + 16, clientY: screenPoint.y + 12 });
chart.handlePointerUp();
assert.notStrictEqual(chart.getDrawingById(drawingId).points[0].value, beforeMoveValue);
assert.ok(chart.indicatorLegendItems(chart.document.indicators.find((indicator) => indicator.id === rsiId).paneId, last.time, chart.theme()).length > 0);

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

assert.strictEqual(chart.save(), true);
assert.ok(chart.storage.load('default').drawings.length > 0);

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
