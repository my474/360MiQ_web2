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
        chartType: normalizeChartType(options.chartType || 'candlestick'),
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
    migrated.settings.chartType = normalizeChartType(migrated.settings.chartType);
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
    this.hoverDrawingId = null;
    this.selectedDrawingId = null;
    this.dragState = null;
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
      '<select class="sce-chart-type" data-sce-chart-type aria-label="Chart type">',
      '<option value="candlestick">Candlestick</option>',
      '<option value="bar">Bar</option>',
      '<option value="line">Line</option>',
      '</select>',
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
    this.canvas.setAttribute('tabindex', '0');
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
    this.toolbar.addEventListener('change', function (event) {
      if (event.target && event.target.getAttribute('data-sce-chart-type') != null) {
        self.setChartType(event.target.value);
      }
    });

    this.canvas.addEventListener('mousedown', function (event) {
      self.handlePointerDown(event);
    });
    this.canvas.addEventListener('mousemove', function (event) {
      self.handlePointerMove(event);
    });
    this.canvas.addEventListener('mouseup', function () {
      self.handlePointerUp();
    });
    this.canvas.addEventListener('mouseleave', function () {
      self.handlePointerUp();
      self.pointer = null;
      self.hoverDrawingId = null;
      self.draw();
    });
    this.canvas.addEventListener('click', function (event) {
      self.handleCanvasClick(event);
    });
    this.canvas.addEventListener('keydown', function (event) {
      if ((event.key === 'Delete' || event.key === 'Backspace') && self.selectedDrawingId) {
        event.preventDefault();
        self.removeDrawing(self.selectedDrawingId);
      }
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

  Chart.prototype.setChartType = function (chartType) {
    this.document.settings.chartType = normalizeChartType(chartType);
    this.draw();
    this.emitChange('chart:type', { chartType: this.document.settings.chartType });
    return this.document.settings.chartType;
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
    this.document.drawings = this.document.drawings.filter(function (drawing) {
      return drawing.ownerStudyId !== indicatorId;
    });
    this.compute();
    this.draw();
    this.emitChange('indicator:remove', { indicatorId: indicatorId, removed: before - this.document.indicators.length });
  };

  Chart.prototype.updateIndicator = function (indicatorId, patch) {
    var indicator = this.document.indicators.filter(function (item) { return item.id === indicatorId; })[0];
    if (!indicator) return false;
    merge(indicator, patch || {});
    if (patch && patch.paneId) {
      this.document.drawings.forEach(function (drawing) {
        if (drawing.ownerStudyId === indicatorId) drawing.paneId = patch.paneId;
      });
    }
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
    type = normalizeDrawingType(type);
    var ownerStudy = options.ownerStudyId ? this.document.indicators.filter(function (indicator) {
      return indicator.id === options.ownerStudyId;
    })[0] : null;
    var drawing = {
      id: options.id || uid('drawing'),
      type: type,
      paneId: options.paneId || (ownerStudy ? ownerStudy.paneId : 'price'),
      ownerStudyId: options.ownerStudyId || null,
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
    if (this.selectedDrawingId === drawingId) this.selectedDrawingId = null;
    if (this.hoverDrawingId === drawingId) this.hoverDrawingId = null;
    this.draw();
    this.emitChange('drawing:remove', { drawingId: drawingId });
  };

  Chart.prototype.getAllShapes = function () {
    return clone(this.document.drawings);
  };

  Chart.prototype.getShapeById = function (drawingId) {
    var self = this;
    var drawing = this.getDrawingById(drawingId);
    if (!drawing) return null;
    return {
      id: drawing.id,
      getProperties: function () {
        return clone(drawing);
      },
      setProperties: function (patch) {
        merge(drawing, patch || {});
        self.draw();
        self.emitChange('drawing:update', drawing);
      },
      setPoints: function (points) {
        drawing.points = clone(points || []);
        self.draw();
        self.emitChange('drawing:move', drawing);
      },
      remove: function () {
        self.removeDrawing(drawing.id);
      }
    };
  };

  Chart.prototype.removeEntity = function (id) {
    this.removeDrawing(id);
  };

  Chart.prototype.removeAllShapes = function () {
    this.document.drawings = [];
    this.selectedDrawingId = null;
    this.hoverDrawingId = null;
    this.draw();
    this.emitChange('drawing:removeAll', {});
  };

  Chart.prototype.createShape = function (point, options) {
    options = options || {};
    var shape = options.shape || 'text';
    var drawingOptions = merge({}, options, { text: options.text || '', style: options.overrides || options.style || {} });
    return this.addDrawing(shape, [{ time: point.time, value: point.price != null ? point.price : point.value }], drawingOptions);
  };

  Chart.prototype.createMultipointShape = function (points, options) {
    options = options || {};
    var shape = options.shape || 'trendline';
    var drawingOptions = merge({}, options, { text: options.text || '', style: options.overrides || options.style || {} });
    return this.addDrawing(shape, (points || []).map(function (point) {
      return { time: point.time, value: point.price != null ? point.price : point.value };
    }), drawingOptions);
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

  Chart.prototype.handlePointerDown = function (event) {
    this.canvas.focus();
    if (this.pendingDrawing) return;
    this.pointer = this.pointerFromEvent(event);
    var deleteHit = this.hitTestDrawingDelete(this.pointer);
    if (deleteHit) {
      this.removeDrawing(deleteHit.id);
      return;
    }

    var hit = this.hitTestDrawing(this.pointer);
    if (!hit) {
      this.selectedDrawingId = null;
      this.hoverDrawingId = null;
      this.draw();
      return;
    }

    this.selectedDrawingId = hit.drawing.id;
    this.hoverDrawingId = hit.drawing.id;
    if (!hit.drawing.locked) {
      this.dragState = {
        drawingId: hit.drawing.id,
        pointIndex: hit.pointIndex,
        paneId: hit.drawing.paneId,
        startPointer: this.pointer,
        startValue: this.valueFromPoint(this.pointer, hit.drawing.paneId),
        originalPoints: clone(hit.drawing.points || [])
      };
    }
    this.draw();
  };

  Chart.prototype.handlePointerMove = function (event) {
    this.pointer = this.pointerFromEvent(event);
    if (this.dragState) {
      this.moveSelectedDrawing(this.pointer);
      this.draw();
      return;
    }
    var hit = this.hitTestDrawing(this.pointer);
    this.hoverDrawingId = hit ? hit.drawing.id : null;
    this.canvas.style.cursor = hit ? 'move' : 'crosshair';
    this.draw();
  };

  Chart.prototype.handlePointerUp = function () {
    if (!this.dragState) return;
    var drawingId = this.dragState.drawingId;
    this.dragState = null;
    this.canvas.style.cursor = 'crosshair';
    this.emitChange('drawing:move', { drawingId: drawingId });
  };

  Chart.prototype.moveSelectedDrawing = function (pointer) {
    var state = this.dragState;
    if (!state) return;
    var current = this.valueFromPoint(pointer, state.paneId);
    if (!current || !state.startValue) return;
    var deltaTime = current.time - state.startValue.time;
    var deltaValue = current.value - state.startValue.value;
    var drawing = this.getDrawingById(state.drawingId);
    if (!drawing || drawing.locked) return;
    if (state.pointIndex != null && state.originalPoints[state.pointIndex]) {
      drawing.points = state.originalPoints.map(function (point, index) {
        if (index !== state.pointIndex) return point;
        return {
          time: Math.round(current.time),
          value: current.value
        };
      });
      return;
    }
    drawing.points = state.originalPoints.map(function (point) {
      return {
        time: Math.round(point.time + deltaTime),
        value: point.value + deltaValue
      };
    });
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
    var chartType = this.toolbar.querySelector('[data-sce-chart-type]');
    if (chartType) chartType.value = this.document.settings.chartType;
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
    return this.valueFromPoint(point);
  };

  Chart.prototype.valueFromPoint = function (point, forcedPaneId) {
    for (var i = 0; i < this.paneRects.length; i += 1) {
      var rect = this.paneRects[i];
      var inPane = point.x >= rect.x && point.x <= rect.x + rect.width && point.y >= rect.y && point.y <= rect.y + rect.height;
      if ((forcedPaneId && rect.paneId === forcedPaneId) || inPane) {
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

  Chart.prototype.getDrawingById = function (drawingId) {
    return this.document.drawings.filter(function (drawing) {
      return drawing.id === drawingId;
    })[0] || null;
  };

  Chart.prototype.getPaneRect = function (paneId) {
    return this.paneRects.filter(function (rect) {
      return rect.paneId === paneId;
    })[0] || null;
  };

  Chart.prototype.drawingScreenPoints = function (drawing) {
    var rect = this.getPaneRect(drawing.paneId);
    if (!rect) return [];
    var range = this.paneRange(drawing.paneId);
    return (drawing.points || []).map(function (point) {
      return {
        x: this.xForTime(point.time, rect),
        y: this.yForValue(point.value, rect, range),
        time: point.time,
        value: point.value
      };
    }, this);
  };

  Chart.prototype.hitTestDrawingDelete = function (pointer) {
    if (!this.selectedDrawingId) return null;
    var drawing = this.getDrawingById(this.selectedDrawingId);
    var zone = drawing ? this.drawingDeleteZone(drawing) : null;
    if (!zone) return null;
    if (pointer.x >= zone.x && pointer.x <= zone.x + zone.size && pointer.y >= zone.y && pointer.y <= zone.y + zone.size) {
      return drawing;
    }
    return null;
  };

  Chart.prototype.hitTestDrawing = function (pointer) {
    var sorted = this.document.drawings.slice().sort(function (a, b) {
      return (b.zIndex || 0) - (a.zIndex || 0);
    });
    for (var i = 0; i < sorted.length; i += 1) {
      var drawing = sorted[i];
      if (drawing.visible === false) continue;
      var rect = this.getPaneRect(drawing.paneId);
      if (!rect || pointer.x < rect.x || pointer.x > rect.x + rect.width || pointer.y < rect.y || pointer.y > rect.y + rect.height) continue;
      var pointIndex = this.hitTestDrawingPoint(pointer, drawing);
      if (pointIndex != null) return { drawing: drawing, pointIndex: pointIndex };
      if (this.isPointOnDrawing(pointer, drawing)) return { drawing: drawing, pointIndex: null };
    }
    return null;
  };

  Chart.prototype.hitTestDrawingPoint = function (pointer, drawing) {
    var points = this.drawingScreenPoints(drawing);
    var tolerance = 8;
    for (var i = points.length - 1; i >= 0; i -= 1) {
      if (distance(pointer, points[i]) <= tolerance) return i;
    }
    return null;
  };

  Chart.prototype.isPointOnDrawing = function (pointer, drawing) {
    var points = this.drawingScreenPoints(drawing);
    var tolerance = 8;
    if (!points.length) return false;

    if ((drawing.type === 'trendline' || drawing.type === 'arrow') && points[0] && points[1]) {
      return distanceToSegment(pointer, points[0], points[1]) <= tolerance;
    }
    if (drawing.type === 'rectangle' && points[0] && points[1]) {
      var minX = Math.min(points[0].x, points[1].x);
      var maxX = Math.max(points[0].x, points[1].x);
      var minY = Math.min(points[0].y, points[1].y);
      var maxY = Math.max(points[0].y, points[1].y);
      var nearEdge = Math.abs(pointer.x - minX) <= tolerance || Math.abs(pointer.x - maxX) <= tolerance || Math.abs(pointer.y - minY) <= tolerance || Math.abs(pointer.y - maxY) <= tolerance;
      return pointer.x >= minX - tolerance && pointer.x <= maxX + tolerance && pointer.y >= minY - tolerance && pointer.y <= maxY + tolerance && nearEdge;
    }
    if (drawing.type === 'hline' && points[0]) {
      return Math.abs(pointer.y - points[0].y) <= tolerance;
    }
    if (drawing.type === 'vline' && points[0]) {
      return Math.abs(pointer.x - points[0].x) <= tolerance;
    }
    if (drawing.type === 'text' && points[0]) {
      return pointer.x >= points[0].x - tolerance && pointer.x <= points[0].x + 110 && pointer.y >= points[0].y - 20 && pointer.y <= points[0].y + 8;
    }
    return false;
  };

  Chart.prototype.drawingDeleteZone = function (drawing) {
    var points = this.drawingScreenPoints(drawing);
    if (!points.length) return null;
    var minX = points[0].x;
    var minY = points[0].y;
    points.forEach(function (point) {
      minX = Math.min(minX, point.x);
      minY = Math.min(minY, point.y);
    });
    return { x: minX - 9, y: minY - 23, size: 16 };
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
    if (rect.paneId === 'price') this.drawPriceSeries(rect, range, theme);
    this.drawIndicators(rect, range, theme);
    this.drawDrawings(rect, range, theme);
    this.drawScale(rect, range, theme);
    this.drawPaneLegend(rect, range, theme, paneIndex);
    ctx.strokeStyle = theme.border;
    ctx.beginPath();
    ctx.moveTo(rect.x, rect.y + rect.height - 0.5);
    ctx.lineTo(rect.x + rect.width + rect.scaleWidth, rect.y + rect.height - 0.5);
    ctx.stroke();
    ctx.restore();
  };

  Chart.prototype.drawPaneLegend = function (rect, range, theme, paneIndex) {
    var ctx = this.ctx;
    var x = rect.x + 10;
    var y = rect.y + 18;
    var legendTime = this.legendTimeForPane(rect);
    ctx.save();
    ctx.font = '12px sans-serif';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = theme.mutedText;
    var paneTitle = rect.title || (paneIndex === 0 ? 'Price' : 'Indicator');
    ctx.fillText(paneTitle, x, y);
    x += approximateTextWidth(paneTitle) + 14;

    if (rect.paneId === 'price') {
      var bar = this.barNearTime(legendTime);
      if (bar) {
        var priceLabel = 'O ' + formatNumber(bar.open) + ' H ' + formatNumber(bar.high) + ' L ' + formatNumber(bar.low) + ' C ' + formatNumber(bar.close);
        ctx.fillStyle = bar.close >= bar.open ? theme.up : theme.down;
        ctx.fillText(priceLabel, x, y);
        x += approximateTextWidth(priceLabel) + 14;
      }
    }

    this.indicatorLegendItems(rect.paneId, legendTime, theme).forEach(function (item) {
      if (x > rect.x + rect.width - 80) return;
      ctx.fillStyle = item.color;
      ctx.fillRect(x, y - 4, 8, 8);
      ctx.fillText(item.label, x + 12, y);
      x += approximateTextWidth(item.label) + 28;
    });
    ctx.restore();
  };

  Chart.prototype.legendTimeForPane = function (rect) {
    if (this.pointer && this.pointer.x >= rect.x && this.pointer.x <= rect.x + rect.width && this.pointer.y >= rect.y && this.pointer.y <= rect.y + rect.height) {
      return Math.round(this.timeForX(this.pointer.x, rect));
    }
    var visible = this.visibleBars();
    return visible.length ? visible[visible.length - 1].time : null;
  };

  Chart.prototype.barNearTime = function (time) {
    if (time == null || !this.bars.length) return null;
    var nearest = this.bars[0];
    var nearestDistance = Math.abs(nearest.time - time);
    this.bars.forEach(function (bar) {
      var currentDistance = Math.abs(bar.time - time);
      if (currentDistance < nearestDistance) {
        nearest = bar;
        nearestDistance = currentDistance;
      }
    });
    return nearest;
  };

  Chart.prototype.indicatorLegendItems = function (paneId, time, theme) {
    var self = this;
    var paletteIndex = 0;
    var items = [];
    this.document.indicators.forEach(function (indicator) {
      var result = self.indicatorResults[indicator.id];
      if (!result) return;
      result.render.forEach(function (renderItem) {
        if (indicator.paneId !== paneId || indicator.visible === false || renderItem.type === 'level') return;
        var data = result.outputs[renderItem.output] || [];
        var valuePoint = nearestSeriesPoint(data, time);
        if (!valuePoint) return;
        var style = merge(defaultStyleForIndicator(theme, paletteIndex), indicator.styles && indicator.styles[renderItem.output] || {});
        if (!style.color) style.color = theme.indicatorPalette[paletteIndex % theme.indicatorPalette.length];
        items.push({
          color: style.color,
          label: indicatorLegendName(indicator, renderItem.output) + ' ' + formatNumber(valuePoint.value)
        });
        paletteIndex += 1;
      });
    });
    return items;
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

  Chart.prototype.drawPriceSeries = function (rect, range, theme) {
    var chartType = normalizeChartType(this.document.settings.chartType);
    if (chartType === 'bar') {
      this.drawBars(rect, range, theme);
      return;
    }
    if (chartType === 'line') {
      this.drawPriceLine(rect, range, theme);
      return;
    }
    this.drawCandles(rect, range, theme);
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

  Chart.prototype.drawBars = function (rect, range, theme) {
    var ctx = this.ctx;
    var bars = this.visibleBars();
    if (!bars.length) return;
    var spacing = rect.width / Math.max(1, bars.length);
    var tickWidth = clamp(spacing * 0.34, 3, 10);
    ctx.save();
    ctx.lineWidth = 1.25;
    bars.forEach(function (bar) {
      var x = this.xForTime(bar.time, rect);
      var open = this.yForValue(bar.open, rect, range);
      var close = this.yForValue(bar.close, rect, range);
      var high = this.yForValue(bar.high, rect, range);
      var low = this.yForValue(bar.low, rect, range);
      ctx.strokeStyle = bar.close >= bar.open ? theme.up : theme.down;
      ctx.beginPath();
      ctx.moveTo(x, high);
      ctx.lineTo(x, low);
      ctx.moveTo(x - tickWidth, open);
      ctx.lineTo(x, open);
      ctx.moveTo(x, close);
      ctx.lineTo(x + tickWidth, close);
      ctx.stroke();
    }, this);
    ctx.restore();
  };

  Chart.prototype.drawPriceLine = function (rect, range, theme) {
    var data = this.visibleBars().map(function (bar) {
      return { time: bar.time, value: bar.close };
    });
    this.drawLineSeries(rect, range, theme, data, {
      color: theme.indicatorPalette[0],
      lineWidth: 2
    });
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
    if (drawing.id === this.selectedDrawingId || drawing.id === this.hoverDrawingId) {
      this.drawDrawingSelection(drawing, theme);
    }
    ctx.restore();
  };

  Chart.prototype.drawDrawingSelection = function (drawing, theme) {
    var ctx = this.ctx;
    var points = this.drawingScreenPoints(drawing);
    if (!points.length) return;
    ctx.save();
    ctx.fillStyle = theme.background;
    ctx.strokeStyle = drawing.id === this.selectedDrawingId ? theme.crosshair : theme.mutedText;
    ctx.lineWidth = 1;
    points.forEach(function (point) {
      ctx.fillRect(point.x - 4, point.y - 4, 8, 8);
      ctx.strokeRect(point.x - 4, point.y - 4, 8, 8);
    });

    if (drawing.id === this.selectedDrawingId && !drawing.locked) {
      var zone = this.drawingDeleteZone(drawing);
      if (zone) {
        ctx.fillStyle = theme.panelBackground;
        ctx.strokeStyle = theme.down;
        ctx.fillRect(zone.x, zone.y, zone.size, zone.size);
        ctx.strokeRect(zone.x, zone.y, zone.size, zone.size);
        ctx.beginPath();
        ctx.moveTo(zone.x + 4, zone.y + 4);
        ctx.lineTo(zone.x + zone.size - 4, zone.y + zone.size - 4);
        ctx.moveTo(zone.x + zone.size - 4, zone.y + 4);
        ctx.lineTo(zone.x + 4, zone.y + zone.size - 4);
        ctx.stroke();
      }
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

  function nearestSeriesPoint(data, time) {
    if (!data || !data.length) return null;
    if (time == null) return data[data.length - 1];
    var nearest = null;
    var nearestDistance = Infinity;
    data.forEach(function (point) {
      if (point.value == null) return;
      var currentDistance = Math.abs(point.time - time);
      if (currentDistance < nearestDistance) {
        nearest = point;
        nearestDistance = currentDistance;
      }
    });
    return nearest;
  }

  function indicatorLegendName(indicator, output) {
    var definition = Indicators[indicator.type];
    var inputs = indicator.inputs || {};
    var name = definition ? definition.id : indicator.type;
    if (inputs.length) name += ' ' + inputs.length;
    if (indicator.type === 'MACD') {
      if (output === 'signal') return 'MACD Signal';
      if (output === 'histogram') return 'MACD Hist';
      return 'MACD';
    }
    if (indicator.type === 'BBANDS') {
      if (output === 'basis') return 'BB Basis';
      return output === 'upper' ? 'BB Upper' : 'BB Lower';
    }
    if (indicator.type === 'STOCH') return output === 'k' ? 'Stoch K' : 'Stoch D';
    return name;
  }

  function normalizeDrawingType(type) {
    var map = {
      arrow: 'arrow',
      anchored_text: 'text',
      horizontal_line: 'hline',
      hline: 'hline',
      icon: 'text',
      note: 'text',
      rectangle: 'rectangle',
      text: 'text',
      trend_line: 'trendline',
      trendline: 'trendline',
      vertical_line: 'vline',
      vline: 'vline'
    };
    return map[type] || type || 'text';
  }

  function normalizeChartType(type) {
    var normalized = String(type || 'candlestick').toLowerCase();
    var map = {
      candle: 'candlestick',
      candles: 'candlestick',
      candlestick: 'candlestick',
      candlesticks: 'candlestick',
      bar: 'bar',
      bars: 'bar',
      ohlc: 'bar',
      line: 'line'
    };
    return map[normalized] || 'candlestick';
  }

  function approximateTextWidth(text) {
    return String(text || '').length * 7;
  }

  function distance(a, b) {
    return Math.sqrt(Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2));
  }

  function distanceToSegment(point, a, b) {
    var dx = b.x - a.x;
    var dy = b.y - a.y;
    if (dx === 0 && dy === 0) return distance(point, a);
    var t = ((point.x - a.x) * dx + (point.y - a.y) * dy) / (dx * dx + dy * dy);
    t = clamp(t, 0, 1);
    return distance(point, { x: a.x + t * dx, y: a.y + t * dy });
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
