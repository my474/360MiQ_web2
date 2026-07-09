/*
 * 360MiQ Stock Chart Engine
 * A dependency-light, canvas based chart runtime with pane-aware indicators,
 * drawings, theme switching, and layout persistence.
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.StockChartEngine = factory();
  }
}(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var VERSION = '0.1.0';
  var SCHEMA_VERSION = 1;
  var DEFAULT_STORE_PREFIX = '360miq-stock-chart';

  var DEFAULT_LIGHT_THEME = {
    name: 'light',
    background: '#ffffff',
    paneBackground: '#ffffff',
    panelBackground: '#f8fafc',
    grid: '#e5e7eb',
    axis: '#6b7280',
    text: '#111827',
    mutedText: '#6b7280',
    crosshair: '#64748b',
    border: '#d1d5db',
    up: '#16a34a',
    down: '#dc2626',
    volumeUp: 'rgba(22, 163, 74, 0.35)',
    volumeDown: 'rgba(220, 38, 38, 0.35)',
    drawing: '#2563eb',
    indicatorPalette: ['#2563eb', '#d97706', '#7c3aed', '#0891b2', '#db2777', '#059669'],
    tooltipBackground: 'rgba(255, 255, 255, 0.94)',
    tooltipBorder: '#d1d5db'
  };

  var DEFAULT_DARK_THEME = {
    name: 'dark',
    background: '#111827',
    paneBackground: '#111827',
    panelBackground: '#172033',
    grid: '#293244',
    axis: '#9ca3af',
    text: '#f9fafb',
    mutedText: '#cbd5e1',
    crosshair: '#94a3b8',
    border: '#374151',
    up: '#22c55e',
    down: '#f87171',
    volumeUp: 'rgba(34, 197, 94, 0.32)',
    volumeDown: 'rgba(248, 113, 113, 0.32)',
    drawing: '#60a5fa',
    indicatorPalette: ['#60a5fa', '#fbbf24', '#a78bfa', '#22d3ee', '#f472b6', '#34d399'],
    tooltipBackground: 'rgba(17, 24, 39, 0.94)',
    tooltipBorder: '#475569'
  };

  function isObject(value) {
    return value && typeof value === 'object' && !Array.isArray(value);
  }

  function clone(value) {
    return value == null ? value : JSON.parse(JSON.stringify(value));
  }

  function merge(target) {
    var output = target || {};
    for (var i = 1; i < arguments.length; i += 1) {
      var source = arguments[i];
      if (!source) continue;
      Object.keys(source).forEach(function (key) {
        if (isObject(source[key])) {
          output[key] = merge(isObject(output[key]) ? output[key] : {}, source[key]);
        } else {
          output[key] = source[key];
        }
      });
    }
    return output;
  }

  function uid(prefix) {
    return (prefix || 'id') + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
  }

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function toNumber(value, fallback) {
    var parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function normalizeTime(value) {
    if (value instanceof Date) return Math.floor(value.getTime() / 1000);
    if (typeof value === 'string') {
      var parsed = Date.parse(value);
      return Number.isFinite(parsed) ? Math.floor(parsed / 1000) : 0;
    }
    return toNumber(value, 0);
  }

  function normalizeBar(bar) {
    if (Array.isArray(bar)) {
      return {
        time: normalizeTime(bar[0]),
        open: toNumber(bar[1], 0),
        high: toNumber(bar[2], 0),
        low: toNumber(bar[3], 0),
        close: toNumber(bar[4], 0),
        volume: toNumber(bar[5], 0)
      };
    }

    return {
      time: normalizeTime(bar.time || bar.t || bar.date),
      open: toNumber(bar.open || bar.o, 0),
      high: toNumber(bar.high || bar.h, 0),
      low: toNumber(bar.low || bar.l, 0),
      close: toNumber(bar.close || bar.c, 0),
      volume: toNumber(bar.volume || bar.v, 0)
    };
  }

  function normalizeBars(bars) {
    return (bars || []).map(normalizeBar).filter(function (bar) {
      return bar.time && Number.isFinite(bar.close);
    }).sort(function (a, b) {
      return a.time - b.time;
    });
  }

  function seriesValueAt(data, time) {
    for (var i = 0; i < data.length; i += 1) {
      if (data[i].time === time) return data[i].value;
    }
    return null;
  }

  function compactSeries(data) {
    return data.filter(function (point) {
      return point && point.value != null && Number.isFinite(point.value);
    });
  }

  function sourceSeries(context, source) {
    source = source || { kind: 'price', field: 'close' };
    if (source.kind === 'indicator') {
      var indicatorResult = context.indicatorResults[source.indicatorId];
      if (!indicatorResult) return [];
      return clone(indicatorResult.outputs[source.output || 'value'] || []);
    }

    var field = source.field || 'close';
    return context.bars.map(function (bar) {
      return { time: bar.time, value: toNumber(bar[field], null) };
    }).filter(function (point) {
      return point.value != null;
    });
  }

  function rollingSma(data, length) {
    var out = [];
    var sum = 0;
    for (var i = 0; i < data.length; i += 1) {
      sum += data[i].value;
      if (i >= length) sum -= data[i - length].value;
      if (i >= length - 1) out.push({ time: data[i].time, value: sum / length });
    }
    return out;
  }

  function rollingEma(data, length) {
    if (!data.length || data.length < length) return [];
    var out = [];
    var sum = 0;
    for (var i = 0; i < length; i += 1) sum += data[i].value;
    var previous = sum / length;
    var k = 2 / (length + 1);
    out.push({ time: data[length - 1].time, value: previous });
    for (var j = length; j < data.length; j += 1) {
      previous = data[j].value * k + previous * (1 - k);
      out.push({ time: data[j].time, value: previous });
    }
    return out;
  }

  function rollingStd(data, smaData, length) {
    var out = [];
    for (var i = length - 1; i < data.length; i += 1) {
      var mean = seriesValueAt(smaData, data[i].time);
      if (mean == null) continue;
      var sum = 0;
      for (var j = i - length + 1; j <= i; j += 1) {
        sum += Math.pow(data[j].value - mean, 2);
      }
      out.push({ time: data[i].time, value: Math.sqrt(sum / length) });
    }
    return out;
  }

  function computeRsi(data, length) {
    if (data.length <= length) return [];
    var gains = 0;
    var losses = 0;
    var out = [];
    for (var i = 1; i <= length; i += 1) {
      var diff = data[i].value - data[i - 1].value;
      if (diff >= 0) gains += diff;
      else losses -= diff;
    }
    var avgGain = gains / length;
    var avgLoss = losses / length;
    out.push({ time: data[length].time, value: avgLoss === 0 ? 100 : 100 - (100 / (1 + avgGain / avgLoss)) });
    for (var j = length + 1; j < data.length; j += 1) {
      var change = data[j].value - data[j - 1].value;
      avgGain = ((avgGain * (length - 1)) + Math.max(change, 0)) / length;
      avgLoss = ((avgLoss * (length - 1)) + Math.max(-change, 0)) / length;
      out.push({ time: data[j].time, value: avgLoss === 0 ? 100 : 100 - (100 / (1 + avgGain / avgLoss)) });
    }
    return out;
  }

  function computeAtr(bars, length) {
    if (bars.length <= length) return [];
    var trueRanges = [];
    for (var i = 0; i < bars.length; i += 1) {
      var previousClose = i > 0 ? bars[i - 1].close : bars[i].close;
      trueRanges.push({
        time: bars[i].time,
        value: Math.max(
          bars[i].high - bars[i].low,
          Math.abs(bars[i].high - previousClose),
          Math.abs(bars[i].low - previousClose)
        )
      });
    }
    return rollingEma(trueRanges, length);
  }

  function computeStochastic(bars, length, smoothK, smoothD) {
    var kRaw = [];
    for (var i = length - 1; i < bars.length; i += 1) {
      var high = -Infinity;
      var low = Infinity;
      for (var j = i - length + 1; j <= i; j += 1) {
        high = Math.max(high, bars[j].high);
        low = Math.min(low, bars[j].low);
      }
      var value = high === low ? 50 : ((bars[i].close - low) / (high - low)) * 100;
      kRaw.push({ time: bars[i].time, value: value });
    }
    var k = rollingSma(kRaw, smoothK);
    return { k: k, d: rollingSma(k, smoothD) };
  }

  function createIndicatorResult(indicator, outputs, render) {
    return {
      id: indicator.id,
      type: indicator.type,
      paneId: indicator.paneId,
      outputs: outputs,
      render: render || []
    };
  }

  var Indicators = {
    SMA: {
      id: 'SMA',
      name: 'Simple Moving Average',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var value = rollingSma(data, Math.max(1, input.length));
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    EMA: {
      id: 'EMA',
      name: 'Exponential Moving Average',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var value = rollingEma(data, Math.max(1, input.length));
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    RSI: {
      id: 'RSI',
      name: 'Relative Strength Index',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var value = computeRsi(data, Math.max(1, input.length));
        return createIndicatorResult(indicator, { value: value }, [
          { output: 'value', type: 'line' },
          { output: 'level70', type: 'level', value: 70 },
          { output: 'level30', type: 'level', value: 30 }
        ]);
      }
    },
    MACD: {
      id: 'MACD',
      name: 'MACD',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { fast: 12, slow: 26, signal: 9 },
      defaultStyles: {
        macd: { color: null, lineWidth: 2 },
        signal: { color: null, lineWidth: 2 },
        histogram: { color: null }
      },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var fast = rollingEma(data, input.fast);
        var slow = rollingEma(data, input.slow);
        var byTime = {};
        fast.forEach(function (point) { byTime[point.time] = { fast: point.value }; });
        slow.forEach(function (point) {
          if (!byTime[point.time]) byTime[point.time] = {};
          byTime[point.time].slow = point.value;
        });
        var macd = Object.keys(byTime).map(function (time) {
          var pair = byTime[time];
          return pair.fast != null && pair.slow != null ? { time: Number(time), value: pair.fast - pair.slow } : null;
        }).filter(Boolean).sort(function (a, b) { return a.time - b.time; });
        var signal = rollingEma(macd, input.signal);
        var histogram = macd.map(function (point) {
          var signalValue = seriesValueAt(signal, point.time);
          return signalValue == null ? null : { time: point.time, value: point.value - signalValue };
        }).filter(Boolean);
        return createIndicatorResult(indicator, { macd: macd, signal: signal, histogram: histogram }, [
          { output: 'histogram', type: 'histogram' },
          { output: 'macd', type: 'line' },
          { output: 'signal', type: 'line' }
        ]);
      }
    },
    BBANDS: {
      id: 'BBANDS',
      name: 'Bollinger Bands',
      category: 'Volatility',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20, multiplier: 2 },
      defaultStyles: {
        upper: { color: null, lineWidth: 1 },
        basis: { color: null, lineWidth: 1 },
        lower: { color: null, lineWidth: 1 }
      },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var basis = rollingSma(data, input.length);
        var deviation = rollingStd(data, basis, input.length);
        var upper = [];
        var lower = [];
        deviation.forEach(function (point) {
          var mean = seriesValueAt(basis, point.time);
          if (mean == null) return;
          upper.push({ time: point.time, value: mean + point.value * input.multiplier });
          lower.push({ time: point.time, value: mean - point.value * input.multiplier });
        });
        return createIndicatorResult(indicator, { upper: upper, basis: basis, lower: lower }, [
          { output: 'upper', type: 'line' },
          { output: 'basis', type: 'line' },
          { output: 'lower', type: 'line' }
        ]);
      }
    },
    VOLUME: {
      id: 'VOLUME',
      name: 'Volume',
      category: 'Volume',
      defaultPanePolicy: 'new',
      defaultInputs: {},
      defaultStyles: { value: { color: null } },
      compute: function (context, indicator) {
        var value = context.bars.map(function (bar) {
          return { time: bar.time, value: bar.volume, open: bar.open, close: bar.close };
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'volume' }]);
      }
    },
    ATR: {
      id: 'ATR',
      name: 'Average True Range',
      category: 'Volatility',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = computeAtr(context.bars, Math.max(1, input.length));
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    STOCH: {
      id: 'STOCH',
      name: 'Stochastic',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14, smoothK: 3, smoothD: 3 },
      defaultStyles: {
        k: { color: null, lineWidth: 2 },
        d: { color: null, lineWidth: 2 }
      },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = computeStochastic(context.bars, input.length, input.smoothK, input.smoothD);
        return createIndicatorResult(indicator, { k: value.k, d: value.d }, [
          { output: 'k', type: 'line' },
          { output: 'd', type: 'line' },
          { output: 'level80', type: 'level', value: 80 },
          { output: 'level20', type: 'level', value: 20 }
        ]);
      }
    },
    VWAP: {
      id: 'VWAP',
      name: 'VWAP',
      category: 'Volume',
      defaultPanePolicy: 'source',
      defaultInputs: {},
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var cumulativePriceVolume = 0;
        var cumulativeVolume = 0;
        var value = context.bars.map(function (bar) {
          var typicalPrice = (bar.high + bar.low + bar.close) / 3;
          cumulativePriceVolume += typicalPrice * bar.volume;
          cumulativeVolume += bar.volume;
          return { time: bar.time, value: cumulativeVolume ? cumulativePriceVolume / cumulativeVolume : typicalPrice };
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    }
  };

  function EventBus() {
    this.listeners = {};
  }

  EventBus.prototype.on = function (eventName, handler) {
    if (!this.listeners[eventName]) this.listeners[eventName] = [];
    this.listeners[eventName].push(handler);
    return this.off.bind(this, eventName, handler);
  };

  EventBus.prototype.off = function (eventName, handler) {
    var list = this.listeners[eventName];
    if (!list) return;
    this.listeners[eventName] = list.filter(function (item) { return item !== handler; });
  };

  EventBus.prototype.emit = function (eventName, payload) {
    (this.listeners[eventName] || []).slice().forEach(function (handler) {
      handler(payload);
    });
  };

  function createDefaultDocument(options) {
    options = options || {};
    return {
      schemaVersion: SCHEMA_VERSION,
      symbol: options.symbol || 'DEMO',
      interval: options.interval || '1D',
      theme: options.theme || null,
      visibleRange: null,
      settings: {
        chartType: options.chartType || 'candles',
        priceField: 'close',
        autosave: options.autosave !== false
      },
      panes: [
        { id: 'price', title: 'Price', height: 420, seriesIds: ['main'], scaleIds: ['right'], type: 'price' }
      ],
      indicators: [],
      drawings: [],
      updatedAt: new Date().toISOString()
    };
  }

  function migrateDocument(doc) {
    var migrated = merge(createDefaultDocument(), doc || {});
    migrated.schemaVersion = SCHEMA_VERSION;
    migrated.panes = migrated.panes && migrated.panes.length ? migrated.panes : createDefaultDocument().panes;
    migrated.indicators = migrated.indicators || [];
    migrated.drawings = migrated.drawings || [];
    migrated.settings = merge(createDefaultDocument().settings, migrated.settings || {});
    return migrated;
  }

  function LocalStorageAdapter(prefix) {
    this.prefix = prefix || DEFAULT_STORE_PREFIX;
  }

  LocalStorageAdapter.prototype.key = function (layoutId) {
    return this.prefix + ':' + (layoutId || 'default');
  };

  LocalStorageAdapter.prototype.load = function (layoutId) {
    if (typeof localStorage === 'undefined') return null;
    var raw = localStorage.getItem(this.key(layoutId));
    if (!raw) return null;
    try {
      return migrateDocument(JSON.parse(raw));
    } catch (error) {
      return null;
    }
  };

  LocalStorageAdapter.prototype.save = function (layoutId, doc) {
    if (typeof localStorage === 'undefined') return false;
    localStorage.setItem(this.key(layoutId), JSON.stringify(doc));
    return true;
  };

  LocalStorageAdapter.prototype.remove = function (layoutId) {
    if (typeof localStorage === 'undefined') return false;
    localStorage.removeItem(this.key(layoutId));
    return true;
  };

  function topoSortIndicators(indicators) {
    var byId = {};
    indicators.forEach(function (indicator) { byId[indicator.id] = indicator; });
    var sorted = [];
    var visiting = {};
    var visited = {};

    function visit(indicator) {
      if (!indicator || visited[indicator.id]) return;
      if (visiting[indicator.id]) throw new Error('Circular indicator dependency: ' + indicator.id);
      visiting[indicator.id] = true;
      if (indicator.source && indicator.source.kind === 'indicator') {
        visit(byId[indicator.source.indicatorId]);
      }
      visiting[indicator.id] = false;
      visited[indicator.id] = true;
      sorted.push(indicator);
    }

    indicators.forEach(visit);
    return sorted;
  }

  function computeIndicatorGraph(bars, indicators) {
    var context = { bars: bars, indicatorResults: {} };
    topoSortIndicators(indicators).forEach(function (indicator) {
      var definition = Indicators[indicator.type];
      if (!definition) return;
      context.indicatorResults[indicator.id] = definition.compute(context, indicator);
    });
    return context.indicatorResults;
  }

  function getThemeName(explicitTheme) {
    if (explicitTheme) return explicitTheme;
    if (typeof document !== 'undefined') {
      var attr = document.documentElement.getAttribute('data-theme');
      if (attr === 'dark') return 'dark';
    }
    if (typeof window !== 'undefined' && window.ThemeController && window.ThemeController.isDark()) return 'dark';
    return 'light';
  }

  function defaultStyleForIndicator(theme, index) {
    return {
      color: theme.indicatorPalette[index % theme.indicatorPalette.length],
      lineWidth: 2
    };
  }

  function Chart(container, options) {
    if (typeof document === 'undefined') {
      throw new Error('StockChartEngine.Chart requires a browser document.');
    }
    options = options || {};
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    if (!this.container) throw new Error('Chart container was not found.');

    this.options = options;
    this.layoutId = options.layoutId || 'default';
    this.storage = options.storage || new LocalStorageAdapter(options.storagePrefix);
    this.events = new EventBus();
    this.bars = normalizeBars(options.data || []);
    this.document = migrateDocument(options.document || (options.load !== false ? this.storage.load(this.layoutId) : null) || createDefaultDocument(options));
    this.document.theme = this.document.theme || getThemeName(options.theme);
    this.indicatorResults = {};
    this.paneRects = [];
    this.pointer = null;
    this.autosaveTimer = null;
    this.destroyed = false;
    this.themeListener = this.handleThemeChange.bind(this);
    this.resizeListener = this.resize.bind(this);
    this.initDom();
    this.bindDom();
    this.compute();
    this.resize();
    this.scheduleAutosave();
  }

  Chart.prototype.initDom = function () {
    this.root = document.createElement('div');
    this.root.className = 'sce-root';
    this.toolbar = document.createElement('div');
    this.toolbar.className = 'sce-toolbar';
    this.toolbar.innerHTML = [
      '<span class="sce-title"></span>',
      '<button type="button" data-sce-action="sma">SMA</button>',
      '<button type="button" data-sce-action="rsi">RSI</button>',
      '<button type="button" data-sce-action="macd">MACD</button>',
      '<button type="button" data-sce-action="line">Line</button>',
      '<button type="button" data-sce-action="save">Save</button>'
    ].join('');
    this.canvasWrap = document.createElement('div');
    this.canvasWrap.className = 'sce-canvas-wrap';
    this.canvas = document.createElement('canvas');
    this.canvas.className = 'sce-canvas';
    this.canvasWrap.appendChild(this.canvas);
    this.root.appendChild(this.toolbar);
    this.root.appendChild(this.canvasWrap);
    this.container.innerHTML = '';
    this.container.appendChild(this.root);
    this.ctx = this.canvas.getContext('2d');
    this.updateToolbar();
  };

  Chart.prototype.bindDom = function () {
    var self = this;
    this.toolbar.addEventListener('click', function (event) {
      var action = event.target && event.target.getAttribute('data-sce-action');
      if (!action) return;
      if (action === 'sma') self.addIndicator('SMA', { placement: 'source', inputs: { length: 20 } });
      if (action === 'rsi') self.addIndicator('RSI', { placement: 'new' });
      if (action === 'macd') self.addIndicator('MACD', { placement: 'new' });
      if (action === 'line') self.startDrawing('trendline');
      if (action === 'save') self.save();
    });

    this.canvas.addEventListener('mousemove', function (event) {
      self.pointer = self.pointerFromEvent(event);
      self.draw();
    });
    this.canvas.addEventListener('mouseleave', function () {
      self.pointer = null;
      self.draw();
    });
    this.canvas.addEventListener('click', function (event) {
      self.handleCanvasClick(event);
    });
    window.addEventListener('resize', this.resizeListener);
    document.documentElement.addEventListener('themechange', this.themeListener);
  };

  Chart.prototype.destroy = function () {
    this.destroyed = true;
    window.removeEventListener('resize', this.resizeListener);
    document.documentElement.removeEventListener('themechange', this.themeListener);
    clearTimeout(this.autosaveTimer);
    this.container.innerHTML = '';
  };

  Chart.prototype.on = function (eventName, handler) {
    return this.events.on(eventName, handler);
  };

  Chart.prototype.emitChange = function (type, payload) {
    this.document.updatedAt = new Date().toISOString();
    this.events.emit('change', { type: type, payload: payload, document: this.serialize() });
    this.scheduleAutosave();
  };

  Chart.prototype.serialize = function () {
    return clone(this.document);
  };

  Chart.prototype.restore = function (doc) {
    this.document = migrateDocument(doc);
    this.compute();
    this.resize();
    this.emitChange('restore', {});
  };

  Chart.prototype.save = function () {
    var saved = this.storage.save(this.layoutId, this.serialize());
    this.events.emit('save', { saved: saved, document: this.serialize() });
    return saved;
  };

  Chart.prototype.load = function (layoutId) {
    var doc = this.storage.load(layoutId || this.layoutId);
    if (!doc) return false;
    this.layoutId = layoutId || this.layoutId;
    this.restore(doc);
    return true;
  };

  Chart.prototype.scheduleAutosave = function () {
    var self = this;
    if (!this.document.settings.autosave) return;
    clearTimeout(this.autosaveTimer);
    this.autosaveTimer = setTimeout(function () {
      if (!self.destroyed) self.save();
    }, this.options.autosaveDelay || 1200);
  };

  Chart.prototype.setData = function (bars) {
    this.bars = normalizeBars(bars);
    this.compute();
    this.fitContent();
    this.emitChange('data', { barCount: this.bars.length });
  };

  Chart.prototype.updateBar = function (bar) {
    var normalized = normalizeBar(bar);
    if (!normalized.time) return;
    var last = this.bars[this.bars.length - 1];
    if (last && last.time === normalized.time) this.bars[this.bars.length - 1] = normalized;
    else this.bars.push(normalized);
    this.bars.sort(function (a, b) { return a.time - b.time; });
    this.compute();
    this.draw();
    this.emitChange('bar', normalized);
  };

  Chart.prototype.addPane = function (pane) {
    var created = merge({
      id: uid('pane'),
      title: pane && pane.title ? pane.title : 'Indicator',
      height: pane && pane.height ? pane.height : 180,
      seriesIds: [],
      scaleIds: ['right'],
      type: 'indicator'
    }, pane || {});
    this.document.panes.push(created);
    this.resize();
    this.emitChange('pane:add', created);
    return created.id;
  };

  Chart.prototype.removePane = function (paneId) {
    if (paneId === 'price') return false;
    this.document.indicators = this.document.indicators.filter(function (indicator) {
      return indicator.paneId !== paneId;
    });
    this.document.drawings = this.document.drawings.filter(function (drawing) {
      return drawing.paneId !== paneId;
    });
    this.document.panes = this.document.panes.filter(function (pane) {
      return pane.id !== paneId;
    });
    this.compute();
    this.resize();
    this.emitChange('pane:remove', { paneId: paneId });
    return true;
  };

  Chart.prototype.resolvePaneForIndicator = function (type, options) {
    options = options || {};
    if (options.paneId) return options.paneId;
    var definition = Indicators[type];
    var placement = options.placement || (definition && definition.defaultPanePolicy) || 'source';
    if (placement === 'new') {
      return this.addPane({ title: definition ? definition.name : type });
    }
    if (options.source && options.source.kind === 'indicator') {
      var sourceIndicator = this.document.indicators.filter(function (indicator) {
        return indicator.id === options.source.indicatorId;
      })[0];
      if (sourceIndicator) return sourceIndicator.paneId;
    }
    return 'price';
  };

  Chart.prototype.addIndicator = function (type, options) {
    options = options || {};
    if (!Indicators[type]) throw new Error('Unknown indicator: ' + type);
    var id = options.id || uid(type.toLowerCase());
    var paneId = this.resolvePaneForIndicator(type, options);
    var indicator = {
      id: id,
      type: type,
      paneId: paneId,
      source: options.source || { kind: 'price', field: options.field || 'close' },
      inputs: merge({}, Indicators[type].defaultInputs || {}, options.inputs || {}),
      styles: merge({}, Indicators[type].defaultStyles || {}, options.styles || {}),
      visible: options.visible !== false,
      zIndex: options.zIndex || this.document.indicators.length + 1
    };
    this.document.indicators.push(indicator);
    this.compute();
    this.draw();
    this.emitChange('indicator:add', indicator);
    return id;
  };

  Chart.prototype.removeIndicator = function (indicatorId) {
    var before = this.document.indicators.length;
    this.document.indicators = this.document.indicators.filter(function (indicator) {
      return indicator.id !== indicatorId && !(indicator.source && indicator.source.kind === 'indicator' && indicator.source.indicatorId === indicatorId);
    });
    this.compute();
    this.draw();
    this.emitChange('indicator:remove', { indicatorId: indicatorId, removed: before - this.document.indicators.length });
  };

  Chart.prototype.updateIndicator = function (indicatorId, patch) {
    var indicator = this.document.indicators.filter(function (item) { return item.id === indicatorId; })[0];
    if (!indicator) return false;
    merge(indicator, patch || {});
    this.compute();
    this.draw();
    this.emitChange('indicator:update', indicator);
    return true;
  };

  Chart.prototype.moveIndicatorToPane = function (indicatorId, paneId) {
    return this.updateIndicator(indicatorId, { paneId: paneId });
  };

  Chart.prototype.addDrawing = function (type, points, options) {
    options = options || {};
    var drawing = {
      id: options.id || uid('drawing'),
      type: type,
      paneId: options.paneId || 'price',
      points: clone(points || []),
      text: options.text || '',
      style: merge({ color: null, width: 2, fill: 'rgba(37, 99, 235, 0.12)', font: '12px sans-serif' }, options.style || {}),
      locked: !!options.locked,
      visible: options.visible !== false,
      zIndex: options.zIndex || this.document.drawings.length + 1
    };
    this.document.drawings.push(drawing);
    this.draw();
    this.emitChange('drawing:add', drawing);
    return drawing.id;
  };

  Chart.prototype.removeDrawing = function (drawingId) {
    this.document.drawings = this.document.drawings.filter(function (drawing) { return drawing.id !== drawingId; });
    this.draw();
    this.emitChange('drawing:remove', { drawingId: drawingId });
  };

  Chart.prototype.startDrawing = function (type) {
    this.pendingDrawing = { type: type, points: [] };
    this.canvas.classList.add('sce-crosshair-drawing');
  };

  Chart.prototype.handleCanvasClick = function (event) {
    if (!this.pendingDrawing) return;
    var point = this.valueFromEvent(event);
    if (!point) return;
    this.pendingDrawing.points.push({ time: point.time, value: point.value });
    if (this.pendingDrawing.points.length >= 2 || this.pendingDrawing.type === 'hline' || this.pendingDrawing.type === 'text') {
      this.addDrawing(this.pendingDrawing.type, this.pendingDrawing.points, { paneId: point.paneId });
      this.pendingDrawing = null;
      this.canvas.classList.remove('sce-crosshair-drawing');
    }
  };

  Chart.prototype.setTheme = function (themeName) {
    this.document.theme = themeName === 'dark' ? 'dark' : 'light';
    this.root.setAttribute('data-sce-theme', this.document.theme);
    this.draw();
    this.emitChange('theme', { theme: this.document.theme });
  };

  Chart.prototype.handleThemeChange = function (event) {
    var themeName = event && event.detail && event.detail.theme ? event.detail.theme : getThemeName();
    this.setTheme(themeName);
  };

  Chart.prototype.theme = function () {
    var base = this.document.theme === 'dark' ? DEFAULT_DARK_THEME : DEFAULT_LIGHT_THEME;
    return merge({}, base, this.options.themeTokens || {});
  };

  Chart.prototype.compute = function () {
    this.indicatorResults = computeIndicatorGraph(this.bars, this.document.indicators);
  };

  Chart.prototype.fitContent = function () {
    if (!this.bars.length) return;
    var count = Math.min(this.bars.length, this.options.initialBarCount || 140);
    this.document.visibleRange = {
      from: this.bars[this.bars.length - count].time,
      to: this.bars[this.bars.length - 1].time
    };
  };

  Chart.prototype.updateToolbar = function () {
    var title = this.toolbar.querySelector('.sce-title');
    if (title) title.textContent = this.document.symbol + ' ' + this.document.interval;
  };

  Chart.prototype.resize = function () {
    var rect = this.canvasWrap.getBoundingClientRect();
    var dpr = window.devicePixelRatio || 1;
    var width = Math.max(320, Math.floor(rect.width || this.options.width || 900));
    var height = Math.max(280, Math.floor(rect.height || this.options.height || 560));
    this.canvas.width = Math.floor(width * dpr);
    this.canvas.height = Math.floor(height * dpr);
    this.canvas.style.width = width + 'px';
    this.canvas.style.height = height + 'px';
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    if (!this.document.visibleRange) this.fitContent();
    this.draw();
  };

  Chart.prototype.visibleBars = function () {
    if (!this.bars.length) return [];
    var range = this.document.visibleRange;
    if (!range) return this.bars;
    return this.bars.filter(function (bar) {
      return bar.time >= range.from && bar.time <= range.to;
    });
  };

  Chart.prototype.layoutPanes = function () {
    var width = this.canvas.clientWidth;
    var height = this.canvas.clientHeight;
    var top = 0;
    var scaleWidth = 68;
    var timeAxisHeight = 28;
    var totalRequested = this.document.panes.reduce(function (sum, pane) { return sum + (pane.height || 180); }, 0);
    var available = Math.max(100, height - timeAxisHeight);
    var self = this;
    this.paneRects = this.document.panes.map(function (pane, index) {
      var paneHeight = Math.max(86, Math.round(available * ((pane.height || 180) / totalRequested)));
      if (index === self.document.panes.length - 1) paneHeight = available - top;
      var rect = {
        paneId: pane.id,
        title: pane.title,
        x: 0,
        y: top,
        width: width - scaleWidth,
        height: paneHeight,
        scaleX: width - scaleWidth,
        scaleWidth: scaleWidth
      };
      top += paneHeight;
      return rect;
    });
    this.timeAxisRect = { x: 0, y: available, width: width - scaleWidth, height: timeAxisHeight };
    return this.paneRects;
  };

  Chart.prototype.paneRange = function (paneId) {
    var values = [];
    var visible = this.visibleBars();
    if (paneId === 'price') {
      visible.forEach(function (bar) {
        values.push(bar.high, bar.low);
      });
    }
    var self = this;
    this.document.indicators.forEach(function (indicator) {
      if (indicator.paneId !== paneId || indicator.visible === false) return;
      var result = self.indicatorResults[indicator.id];
      if (!result) return;
      Object.keys(result.outputs).forEach(function (key) {
        result.outputs[key].forEach(function (point) {
          if (visible.length && point.time >= visible[0].time && point.time <= visible[visible.length - 1].time && point.value != null) {
            values.push(point.value);
          }
        });
      });
    });
    this.document.drawings.forEach(function (drawing) {
      if (drawing.paneId !== paneId) return;
      drawing.points.forEach(function (point) {
        if (point.value != null) values.push(point.value);
      });
    });
    if (!values.length) return { min: 0, max: 1 };
    var min = Math.min.apply(null, values);
    var max = Math.max.apply(null, values);
    if (min === max) {
      min -= Math.abs(min || 1) * 0.05;
      max += Math.abs(max || 1) * 0.05;
    }
    var pad = (max - min) * 0.08;
    return { min: min - pad, max: max + pad };
  };

  Chart.prototype.xForTime = function (time, rect) {
    var visible = this.visibleBars();
    if (!visible.length) return rect.x;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    if (first === last) return rect.x + rect.width / 2;
    return rect.x + ((time - first) / (last - first)) * rect.width;
  };

  Chart.prototype.yForValue = function (value, rect, range) {
    return rect.y + ((range.max - value) / (range.max - range.min)) * rect.height;
  };

  Chart.prototype.timeForX = function (x, rect) {
    var visible = this.visibleBars();
    if (!visible.length) return null;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    return first + ((x - rect.x) / rect.width) * (last - first);
  };

  Chart.prototype.valueForY = function (y, rect, range) {
    return range.max - ((y - rect.y) / rect.height) * (range.max - range.min);
  };

  Chart.prototype.pointerFromEvent = function (event) {
    var bounds = this.canvas.getBoundingClientRect();
    return { x: event.clientX - bounds.left, y: event.clientY - bounds.top };
  };

  Chart.prototype.valueFromEvent = function (event) {
    var point = this.pointerFromEvent(event);
    for (var i = 0; i < this.paneRects.length; i += 1) {
      var rect = this.paneRects[i];
      if (point.x >= rect.x && point.x <= rect.x + rect.width && point.y >= rect.y && point.y <= rect.y + rect.height) {
        var range = this.paneRange(rect.paneId);
        return {
          paneId: rect.paneId,
          time: Math.round(this.timeForX(point.x, rect)),
          value: this.valueForY(point.y, rect, range)
        };
      }
    }
    return null;
  };

  Chart.prototype.clear = function (theme) {
    var ctx = this.ctx;
    ctx.save();
    ctx.fillStyle = theme.background;
    ctx.fillRect(0, 0, this.canvas.clientWidth, this.canvas.clientHeight);
    ctx.restore();
  };

  Chart.prototype.draw = function () {
    if (!this.ctx) return;
    var theme = this.theme();
    this.root.setAttribute('data-sce-theme', theme.name);
    this.updateToolbar();
    this.clear(theme);
    this.layoutPanes();
    for (var i = 0; i < this.paneRects.length; i += 1) {
      this.drawPane(this.paneRects[i], theme, i);
    }
    this.drawTimeAxis(theme);
    this.drawCrosshair(theme);
  };

  Chart.prototype.drawPane = function (rect, theme, paneIndex) {
    var ctx = this.ctx;
    var range = this.paneRange(rect.paneId);
    ctx.save();
    ctx.fillStyle = theme.paneBackground;
    ctx.fillRect(rect.x, rect.y, rect.width + rect.scaleWidth, rect.height);
    this.drawGrid(rect, range, theme);
    if (rect.paneId === 'price') this.drawCandles(rect, range, theme);
    this.drawIndicators(rect, range, theme);
    this.drawDrawings(rect, range, theme);
    this.drawScale(rect, range, theme);
    ctx.fillStyle = theme.mutedText;
    ctx.font = '12px sans-serif';
    ctx.fillText(rect.title || (paneIndex === 0 ? 'Price' : 'Indicator'), rect.x + 10, rect.y + 18);
    ctx.strokeStyle = theme.border;
    ctx.beginPath();
    ctx.moveTo(rect.x, rect.y + rect.height - 0.5);
    ctx.lineTo(rect.x + rect.width + rect.scaleWidth, rect.y + rect.height - 0.5);
    ctx.stroke();
    ctx.restore();
  };

  Chart.prototype.drawGrid = function (rect, range, theme) {
    var ctx = this.ctx;
    ctx.save();
    ctx.strokeStyle = theme.grid;
    ctx.lineWidth = 1;
    for (var i = 1; i < 5; i += 1) {
      var y = rect.y + (rect.height / 5) * i;
      ctx.beginPath();
      ctx.moveTo(rect.x, Math.round(y) + 0.5);
      ctx.lineTo(rect.x + rect.width, Math.round(y) + 0.5);
      ctx.stroke();
    }
    var visible = this.visibleBars();
    var step = Math.max(1, Math.floor(visible.length / 8));
    for (var j = 0; j < visible.length; j += step) {
      var x = this.xForTime(visible[j].time, rect);
      ctx.beginPath();
      ctx.moveTo(Math.round(x) + 0.5, rect.y);
      ctx.lineTo(Math.round(x) + 0.5, rect.y + rect.height);
      ctx.stroke();
    }
    ctx.restore();
  };

  Chart.prototype.drawScale = function (rect, range, theme) {
    var ctx = this.ctx;
    ctx.save();
    ctx.fillStyle = theme.panelBackground;
    ctx.fillRect(rect.scaleX, rect.y, rect.scaleWidth, rect.height);
    ctx.strokeStyle = theme.border;
    ctx.beginPath();
    ctx.moveTo(rect.scaleX + 0.5, rect.y);
    ctx.lineTo(rect.scaleX + 0.5, rect.y + rect.height);
    ctx.stroke();
    ctx.fillStyle = theme.axis;
    ctx.font = '11px sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    for (var i = 0; i <= 4; i += 1) {
      var value = range.max - ((range.max - range.min) / 4) * i;
      var y = rect.y + (rect.height / 4) * i;
      ctx.fillText(formatNumber(value), rect.scaleX + 6, y);
    }
    ctx.restore();
  };

  Chart.prototype.drawCandles = function (rect, range, theme) {
    var ctx = this.ctx;
    var bars = this.visibleBars();
    if (!bars.length) return;
    var spacing = rect.width / Math.max(1, bars.length);
    var candleWidth = clamp(spacing * 0.62, 2, 18);
    ctx.save();
    bars.forEach(function (bar) {
      var x = this.xForTime(bar.time, rect);
      var open = this.yForValue(bar.open, rect, range);
      var close = this.yForValue(bar.close, rect, range);
      var high = this.yForValue(bar.high, rect, range);
      var low = this.yForValue(bar.low, rect, range);
      var up = bar.close >= bar.open;
      ctx.strokeStyle = up ? theme.up : theme.down;
      ctx.fillStyle = up ? theme.up : theme.down;
      ctx.beginPath();
      ctx.moveTo(x, high);
      ctx.lineTo(x, low);
      ctx.stroke();
      var bodyTop = Math.min(open, close);
      var bodyHeight = Math.max(1, Math.abs(open - close));
      ctx.fillRect(x - candleWidth / 2, bodyTop, candleWidth, bodyHeight);
    }, this);
    ctx.restore();
  };

  Chart.prototype.drawIndicators = function (rect, range, theme) {
    var self = this;
    var paletteIndex = 0;
    this.document.indicators.forEach(function (indicator) {
      if (indicator.paneId !== rect.paneId || indicator.visible === false) return;
      var result = self.indicatorResults[indicator.id];
      if (!result) return;
      result.render.forEach(function (renderItem) {
        if (renderItem.type === 'level') {
          self.drawLevel(rect, range, theme, renderItem.value);
          return;
        }
        var data = result.outputs[renderItem.output] || [];
        var style = merge(defaultStyleForIndicator(theme, paletteIndex), indicator.styles && indicator.styles[renderItem.output] || {});
        if (!style.color) style.color = theme.indicatorPalette[paletteIndex % theme.indicatorPalette.length];
        if (renderItem.type === 'histogram') self.drawHistogram(rect, range, theme, data, style, false);
        else if (renderItem.type === 'volume') self.drawHistogram(rect, range, theme, data, style, true);
        else self.drawLineSeries(rect, range, theme, data, style);
        paletteIndex += 1;
      });
    });
  };

  Chart.prototype.drawLineSeries = function (rect, range, theme, data, style) {
    var ctx = this.ctx;
    var visible = this.visibleBars();
    if (!visible.length || !data.length) return;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    ctx.save();
    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.lineWidth || 2;
    ctx.beginPath();
    var started = false;
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var y = this.yForValue(point.value, rect, range);
      if (!started) {
        ctx.moveTo(x, y);
        started = true;
      } else {
        ctx.lineTo(x, y);
      }
    }, this);
    if (started) ctx.stroke();
    ctx.restore();
  };

  Chart.prototype.drawHistogram = function (rect, range, theme, data, style, useVolumeColor) {
    var ctx = this.ctx;
    var visible = this.visibleBars();
    if (!visible.length || !data.length) return;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    var spacing = rect.width / Math.max(1, visible.length);
    var barWidth = clamp(spacing * 0.7, 1, 14);
    var zeroY = this.yForValue(Math.max(0, range.min), rect, range);
    ctx.save();
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var y = this.yForValue(point.value, rect, range);
      if (useVolumeColor) ctx.fillStyle = point.close >= point.open ? theme.volumeUp : theme.volumeDown;
      else ctx.fillStyle = point.value >= 0 ? theme.volumeUp : theme.volumeDown;
      if (style.color) ctx.fillStyle = style.color;
      ctx.fillRect(x - barWidth / 2, Math.min(y, zeroY), barWidth, Math.max(1, Math.abs(zeroY - y)));
    }, this);
    ctx.restore();
  };

  Chart.prototype.drawLevel = function (rect, range, theme, value) {
    var ctx = this.ctx;
    var y = this.yForValue(value, rect, range);
    if (y < rect.y || y > rect.y + rect.height) return;
    ctx.save();
    ctx.strokeStyle = theme.border;
    ctx.setLineDash([4, 4]);
    ctx.beginPath();
    ctx.moveTo(rect.x, y);
    ctx.lineTo(rect.x + rect.width, y);
    ctx.stroke();
    ctx.restore();
  };

  Chart.prototype.drawDrawings = function (rect, range, theme) {
    var self = this;
    this.document.drawings.filter(function (drawing) {
      return drawing.paneId === rect.paneId && drawing.visible !== false;
    }).sort(function (a, b) {
      return (a.zIndex || 0) - (b.zIndex || 0);
    }).forEach(function (drawing) {
      self.drawDrawing(rect, range, theme, drawing);
    });
  };

  Chart.prototype.drawDrawing = function (rect, range, theme, drawing) {
    var ctx = this.ctx;
    var points = drawing.points || [];
    var style = merge({ color: theme.drawing, width: 2, fill: 'rgba(37, 99, 235, 0.12)', font: '12px sans-serif' }, drawing.style || {});
    if (!style.color) style.color = theme.drawing;
    ctx.save();
    ctx.strokeStyle = style.color;
    ctx.fillStyle = style.fill;
    ctx.lineWidth = style.width || 2;
    ctx.font = style.font || '12px sans-serif';
    if (drawing.type === 'hline' && points[0]) {
      var y = this.yForValue(points[0].value, rect, range);
      ctx.beginPath();
      ctx.moveTo(rect.x, y);
      ctx.lineTo(rect.x + rect.width, y);
      ctx.stroke();
    } else if (drawing.type === 'vline' && points[0]) {
      var x = this.xForTime(points[0].time, rect);
      ctx.beginPath();
      ctx.moveTo(x, rect.y);
      ctx.lineTo(x, rect.y + rect.height);
      ctx.stroke();
    } else if (drawing.type === 'rectangle' && points[0] && points[1]) {
      var x1 = this.xForTime(points[0].time, rect);
      var y1 = this.yForValue(points[0].value, rect, range);
      var x2 = this.xForTime(points[1].time, rect);
      var y2 = this.yForValue(points[1].value, rect, range);
      ctx.fillRect(Math.min(x1, x2), Math.min(y1, y2), Math.abs(x2 - x1), Math.abs(y2 - y1));
      ctx.strokeRect(Math.min(x1, x2), Math.min(y1, y2), Math.abs(x2 - x1), Math.abs(y2 - y1));
    } else if (drawing.type === 'text' && points[0]) {
      ctx.fillStyle = style.color;
      ctx.fillText(drawing.text || 'Note', this.xForTime(points[0].time, rect), this.yForValue(points[0].value, rect, range));
    } else if ((drawing.type === 'trendline' || drawing.type === 'arrow') && points[0] && points[1]) {
      var ax = this.xForTime(points[0].time, rect);
      var ay = this.yForValue(points[0].value, rect, range);
      var bx = this.xForTime(points[1].time, rect);
      var by = this.yForValue(points[1].value, rect, range);
      ctx.beginPath();
      ctx.moveTo(ax, ay);
      ctx.lineTo(bx, by);
      ctx.stroke();
      if (drawing.type === 'arrow') drawArrowHead(ctx, ax, ay, bx, by, style.color);
    }
    ctx.restore();
  };

  Chart.prototype.drawTimeAxis = function (theme) {
    var ctx = this.ctx;
    var rect = this.timeAxisRect;
    var visible = this.visibleBars();
    if (!rect || !visible.length) return;
    ctx.save();
    ctx.fillStyle = theme.panelBackground;
    ctx.fillRect(rect.x, rect.y, rect.width + 68, rect.height);
    ctx.strokeStyle = theme.border;
    ctx.beginPath();
    ctx.moveTo(rect.x, rect.y + 0.5);
    ctx.lineTo(rect.x + rect.width + 68, rect.y + 0.5);
    ctx.stroke();
    ctx.fillStyle = theme.axis;
    ctx.font = '11px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    var step = Math.max(1, Math.floor(visible.length / 7));
    for (var i = 0; i < visible.length; i += step) {
      var x = this.xForTime(visible[i].time, { x: rect.x, width: rect.width });
      ctx.fillText(formatDate(visible[i].time), x, rect.y + rect.height / 2);
    }
    ctx.restore();
  };

  Chart.prototype.drawCrosshair = function (theme) {
    if (!this.pointer) return;
    var ctx = this.ctx;
    var rect = null;
    for (var i = 0; i < this.paneRects.length; i += 1) {
      var candidate = this.paneRects[i];
      if (this.pointer.y >= candidate.y && this.pointer.y <= candidate.y + candidate.height) {
        rect = candidate;
        break;
      }
    }
    if (!rect) return;
    ctx.save();
    ctx.strokeStyle = theme.crosshair;
    ctx.setLineDash([3, 3]);
    ctx.beginPath();
    ctx.moveTo(this.pointer.x, rect.y);
    ctx.lineTo(this.pointer.x, rect.y + rect.height);
    ctx.moveTo(rect.x, this.pointer.y);
    ctx.lineTo(rect.x + rect.width, this.pointer.y);
    ctx.stroke();
    ctx.restore();
  };

  function drawArrowHead(ctx, ax, ay, bx, by, color) {
    var angle = Math.atan2(by - ay, bx - ax);
    var size = 9;
    ctx.save();
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.moveTo(bx, by);
    ctx.lineTo(bx - size * Math.cos(angle - Math.PI / 6), by - size * Math.sin(angle - Math.PI / 6));
    ctx.lineTo(bx - size * Math.cos(angle + Math.PI / 6), by - size * Math.sin(angle + Math.PI / 6));
    ctx.closePath();
    ctx.fill();
    ctx.restore();
  }

  function formatNumber(value) {
    var abs = Math.abs(value);
    if (abs >= 1000000000) return (value / 1000000000).toFixed(2) + 'B';
    if (abs >= 1000000) return (value / 1000000).toFixed(2) + 'M';
    if (abs >= 1000) return (value / 1000).toFixed(2) + 'K';
    if (abs < 1 && abs > 0) return value.toFixed(4);
    return value.toFixed(2);
  }

  function formatDate(time) {
    var date = new Date(time * 1000);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
  }

  function createDemoData(count) {
    var data = [];
    var time = Math.floor(Date.now() / 1000) - count * 86400;
    var close = 100;
    for (var i = 0; i < count; i += 1) {
      var open = close;
      var change = (Math.sin(i / 8) + Math.random() - 0.48) * 1.8;
      close = Math.max(5, close + change);
      var high = Math.max(open, close) + Math.random() * 2.4;
      var low = Math.min(open, close) - Math.random() * 2.4;
      data.push({ time: time + i * 86400, open: open, high: high, low: low, close: close, volume: 800000 + Math.random() * 1500000 });
    }
    return data;
  }

  return {
    VERSION: VERSION,
    SCHEMA_VERSION: SCHEMA_VERSION,
    Chart: Chart,
    EventBus: EventBus,
    LocalStorageAdapter: LocalStorageAdapter,
    Indicators: Indicators,
    createDefaultDocument: createDefaultDocument,
    migrateDocument: migrateDocument,
    computeIndicatorGraph: computeIndicatorGraph,
    normalizeBars: normalizeBars,
    createDemoData: createDemoData,
    themes: {
      light: DEFAULT_LIGHT_THEME,
      dark: DEFAULT_DARK_THEME
    }
  };
}));
