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
  var DEFAULT_SERIES_COLOR_ORDER = [
    '#2563eb', '#d97706', '#7c3aed', '#0891b2', '#db2777', '#059669',
    '#dc2626', '#4f46e5', '#65a30d', '#ea580c', '#0f766e', '#9333ea',
    '#be123c', '#0369a1', '#ca8a04', '#16a34a', '#c2410c', '#7f1d1d'
  ];
  var CHART_PERIODS = {
    daily: { id: 'daily', interval: '1D', label: 'Daily' },
    weekly: { id: 'weekly', interval: '1W', label: 'Weekly' },
    monthly: { id: 'monthly', interval: '1M', label: 'Monthly' },
    quarterly: { id: 'quarterly', interval: '3M', label: 'Quarterly' },
    yearly: { id: 'yearly', interval: '1Y', label: 'Yearly' }
  };
  var DATE_RANGE_PRESETS = [
    { id: '1m', label: '1M', months: 1 },
    { id: '3m', label: '3M', months: 3 },
    { id: '6m', label: '6M', months: 6 },
    { id: 'ytd', label: 'YTD', ytd: true },
    { id: '1y', label: '1Y', years: 1 },
    { id: '2y', label: '2Y', years: 2 },
    { id: '3y', label: '3Y', years: 3 },
    { id: '5y', label: '5Y', years: 5 },
    { id: '8y', label: '8Y', years: 8 },
    { id: '10y', label: '10Y', years: 10 },
    { id: 'all', label: 'All', all: true }
  ];

  var DRAWING_TOOLS = createDrawingTools();

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

  function fallbackCopyText(text, unsupportedMessage) {
    if (typeof Promise === 'undefined') {
      throw new Error(unsupportedMessage);
    }
    if (typeof document === 'undefined' || !document.createElement || !document.body) {
      return Promise.reject(new Error(unsupportedMessage));
    }
    return new Promise(function (resolve, reject) {
      var textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', 'readonly');
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      textarea.style.top = '0';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        if (document.execCommand && document.execCommand('copy')) {
          resolve(text);
        } else {
          reject(new Error(unsupportedMessage));
        }
      } catch (error) {
        reject(error);
      } finally {
        if (textarea.parentNode) textarea.parentNode.removeChild(textarea);
      }
    });
  }

  function createDrawingTools() {
    var specs = [
      ['trendline', 'Trendline', 'trend', 'line', 2, ['trend_line']],
      ['arrow', 'Arrow', 'trend', 'arrow', 2],
      ['ray', 'Ray', 'trend', 'ray', 2],
      ['info_line', 'Info line', 'trend', 'measurement', 2],
      ['extended_line', 'Extended line', 'trend', 'extendedLine', 2],
      ['trend_angle', 'Trend angle', 'trend', 'angle', 2],
      ['hline', 'Horizontal line', 'trend', 'hline', 1, ['horizontal_line']],
      ['horizontal_ray', 'Horizontal ray', 'trend', 'horizontalRay', 1],
      ['vline', 'Vertical line', 'trend', 'vline', 1, ['vertical_line']],
      ['crossline', 'Crossline', 'trend', 'crossline', 1],
      ['parallel_channel', 'Parallel channel', 'trend', 'channel', 3],
      ['regression_trend', 'Regression trend', 'trend', 'channel', 2],
      ['flat_top_bottom', 'Flat top/bottom', 'trend', 'channel', 3],
      ['disjoint_channel', 'Disjoint channel', 'trend', 'channel', 4],
      ['anchored_vwap', 'Anchored VWAP', 'trend', 'vwapAnchor', 1],
      ['fib_retracement', 'Fib retracement', 'fibonacci', 'fibRetracement', 2],
      ['trend_based_fib_extension', 'Trend-based fib extension', 'fibonacci', 'fibExtension', 3],
      ['pitchfork', 'Pitchfork', 'fibonacci', 'pitchfork', 3],
      ['schiff_pitchfork', 'Schiff pitchfork', 'fibonacci', 'pitchfork', 3],
      ['modified_schiff_pitchfork', 'Modified Schiff pitchfork', 'fibonacci', 'pitchfork', 3],
      ['inside_pitchfork', 'Inside pitchfork', 'fibonacci', 'pitchfork', 3],
      ['fib_channel', 'Fib channel', 'fibonacci', 'fibChannel', 3],
      ['fib_time_zone', 'Fib time zone', 'fibonacci', 'timeZones', 2],
      ['gann_box', 'Gann box', 'fibonacci', 'gridBox', 2],
      ['gann_square_fixed', 'Gann square fixed', 'fibonacci', 'gridBox', 2],
      ['gann_square', 'Gann square', 'fibonacci', 'gridBox', 2],
      ['gann_fan', 'Gann fan', 'fibonacci', 'fan', 2],
      ['fib_speed_resistance_fan', 'Fib speed resistance fan', 'fibonacci', 'fan', 2],
      ['trend_based_fib_time', 'Trend-based fib time', 'fibonacci', 'timeZones', 3],
      ['fib_circles', 'Fib circles', 'fibonacci', 'circles', 2],
      ['pitchfan', 'Pitchfan', 'fibonacci', 'fan', 3],
      ['fib_spiral', 'Fib spiral', 'fibonacci', 'spiral', 2],
      ['fib_speed_resistance_arcs', 'Fib speed resistance arcs', 'fibonacci', 'arcs', 2],
      ['fib_wedge', 'Fib wedge', 'fibonacci', 'wedge', 3],
      ['brush', 'Brush', 'geometric', 'path', 2],
      ['highlighter', 'Highlighter', 'geometric', 'path', 2],
      ['rectangle', 'Rectangle', 'geometric', 'rectangle', 2],
      ['circle', 'Circle', 'geometric', 'ellipse', 2],
      ['ellipse', 'Ellipse', 'geometric', 'ellipse', 2],
      ['path', 'Path', 'geometric', 'path', 2],
      ['curve', 'Curve', 'geometric', 'curve', 3],
      ['polyline', 'Polyline', 'geometric', 'polyline', 3],
      ['triangle', 'Triangle', 'geometric', 'polygon', 3],
      ['rotated_rectangle', 'Rotated rectangle', 'geometric', 'rotatedRectangle', 3],
      ['arc', 'Arc', 'geometric', 'arc', 3],
      ['double_curve', 'Double curve', 'geometric', 'doubleCurve', 4],
      ['text', 'Text', 'annotation', 'text', 1],
      ['note', 'Note', 'annotation', 'text', 1],
      ['anchored_note', 'Anchored note', 'annotation', 'text', 1, ['anchored_text']],
      ['signpost', 'Signpost', 'annotation', 'callout', 1],
      ['callout', 'Callout', 'annotation', 'callout', 2],
      ['comment', 'Comment', 'annotation', 'callout', 1],
      ['price_label', 'Price label', 'annotation', 'priceLabel', 1],
      ['price_note', 'Price note', 'annotation', 'priceLabel', 1],
      ['arrow_marker', 'Arrow marker', 'annotation', 'marker', 1],
      ['arrow_mark_left', 'Arrow mark left', 'annotation', 'markerLeft', 1],
      ['arrow_mark_right', 'Arrow mark right', 'annotation', 'markerRight', 1],
      ['arrow_mark_up', 'Arrow mark up', 'annotation', 'markerUp', 1],
      ['arrow_mark_down', 'Arrow mark down', 'annotation', 'markerDown', 1],
      ['flag_mark', 'Flag mark', 'annotation', 'flag', 1],
      ['xabcd_pattern', 'XABCD pattern', 'pattern', 'pattern', 5],
      ['cypher_pattern', 'Cypher pattern', 'pattern', 'pattern', 5],
      ['abcd_pattern', 'ABCD pattern', 'pattern', 'pattern', 4],
      ['triangle_pattern', 'Triangle pattern', 'pattern', 'polygon', 3],
      ['three_drives_pattern', 'Three drives pattern', 'pattern', 'pattern', 6],
      ['head_and_shoulders', 'Head and shoulders', 'pattern', 'pattern', 5],
      ['elliott_impulse_wave', 'Elliott impulse wave (12345)', 'pattern', 'numberedWave', 5, ['elliot_impulse_wave', 'elliott_impulse_wave_12345', 'elliot_impulse_wave_12345']],
      ['elliott_triangle_wave', 'Elliott triangle wave (ABCDE)', 'pattern', 'letteredWave', 5, ['elliot_triangle_wave', 'elliott_triangle_wave_abcde', 'elliot_triangle_wave_abcde']],
      ['elliott_triple_combo_wave', 'Elliott triple combo wave (WXYXZ)', 'pattern', 'letteredWave', 5, ['elliot_triple_combo_wave']],
      ['elliott_correction_wave', 'Elliott correction wave (ABC)', 'pattern', 'letteredWave', 3, ['elliot_correction_wave', 'elliott_correction_wave_abc', 'elliot_correction_wave_abc']],
      ['elliott_double_combo_wave', 'Elliott double combo wave (WXY)', 'pattern', 'letteredWave', 3, ['elliot_double_combo_wave']],
      ['cyclic_lines', 'Cyclic lines', 'pattern', 'cyclicLines', 2],
      ['time_cycles', 'Time cycles', 'pattern', 'cyclicLines', 2],
      ['sine_line', 'Sine line', 'pattern', 'sine', 2],
      ['long_position', 'Long position', 'measurement', 'positionLong', 2],
      ['short_position', 'Short position', 'measurement', 'positionShort', 2],
      ['position_forecast', 'Position forecast', 'measurement', 'positionForecast', 2],
      ['date_range', 'Date range', 'measurement', 'dateRange', 2],
      ['price_range', 'Price range', 'measurement', 'priceRange', 2],
      ['date_and_price_range', 'Date and price range', 'measurement', 'datePriceRange', 2],
      ['bars_pattern', 'Bars pattern', 'measurement', 'barsPattern', 2],
      ['ghost_feed', 'Ghost feed', 'measurement', 'ghostFeed', 2],
      ['sector', 'Sector', 'measurement', 'sector', 3],
      ['fixed_range_volume_profile', 'Fixed range volume profile', 'measurement', 'volumeProfile', 2],
      ['icon', 'Icon', 'icon', 'marker', 1],
      ['sticker', 'Sticker', 'sticker', 'marker', 1],
      ['emoji', 'Emoji', 'emoji', 'text', 1]
    ];
    var tools = {};
    specs.forEach(function (spec) {
      tools[spec[0]] = {
        id: spec[0],
        name: spec[1],
        category: spec[2],
        renderKind: spec[3],
        points: spec[4],
        aliases: spec[5] || [],
        icon: drawingToolIconSvg(spec[0], spec[3])
      };
    });
    return tools;
  }

  function drawingToolIconSvg(type, renderKind) {
    var tool = DRAWING_TOOLS && DRAWING_TOOLS[normalizeDrawingType(type)];
    var kind = renderKind || (tool ? tool.renderKind : normalizeDrawingKey(type));
    var id = tool ? tool.id : normalizeDrawingKey(type);
    var common = ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

    function svg(body) {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"' + common + '>' + body + '</svg>';
    }

    if (id === 'arrow_mark_left') return svg('<path d="M6 12h12"/><path d="M10 8l-4 4 4 4"/>');
    if (id === 'arrow_mark_right') return svg('<path d="M6 12h12"/><path d="M14 8l4 4-4 4"/>');
    if (id === 'arrow_mark_up') return svg('<path d="M12 18V6"/><path d="M8 10l4-4 4 4"/>');
    if (id === 'arrow_mark_down') return svg('<path d="M12 6v12"/><path d="M8 14l4 4 4-4"/>');
    if (id.indexOf('elliott') === 0) return svg('<path d="M3 16l4-8 4 6 4-7 6 9"/><path d="M5 19h14"/>');
    if (id.indexOf('xabcd') === 0 || id.indexOf('cypher') === 0 || id.indexOf('abcd') === 0) return svg('<path d="M3 16l5-9 5 8 4-6 4 7"/><path d="M3 16h18"/>');
    if (id === 'head_and_shoulders') return svg('<path d="M3 17c3-7 4-7 6 0 2-12 4-12 6 0 2-7 3-7 6 0"/>');
    if (id === 'three_drives_pattern') return svg('<path d="M3 18l3-7 3 5 3-8 3 6 3-9 3 13"/>');

    var icons = {
      line: '<path d="M4 18L20 6"/><circle cx="4" cy="18" r="1.5"/><circle cx="20" cy="6" r="1.5"/>',
      arrow: '<path d="M4 18L20 6"/><path d="M14 6h6v6"/>',
      ray: '<path d="M4 18L18 8"/><path d="M15 7l4 1-1 4"/><circle cx="4" cy="18" r="1.5"/>',
      measurement: '<path d="M4 17L20 7"/><path d="M5 13l4 4"/><path d="M15 7l4 4"/>',
      extendedLine: '<path d="M2 20L22 4"/><path d="M5 18h4"/><path d="M15 6h4"/>',
      angle: '<path d="M5 18h14"/><path d="M5 18L17 7"/><path d="M9 18a4 4 0 0 1 2-3"/>',
      hline: '<path d="M3 12h18"/><path d="M5 8v8"/><path d="M19 8v8"/>',
      horizontalRay: '<path d="M5 12h14"/><path d="M15 8l4 4-4 4"/><circle cx="5" cy="12" r="1.5"/>',
      vline: '<path d="M12 3v18"/><path d="M8 5h8"/><path d="M8 19h8"/>',
      crossline: '<path d="M12 3v18"/><path d="M3 12h18"/>',
      channel: '<path d="M4 17L18 7"/><path d="M6 21L20 11"/>',
      vwapAnchor: '<path d="M12 4v16"/><path d="M6 10c4-4 8 8 12 0"/><circle cx="12" cy="4" r="2"/>',
      fibRetracement: '<path d="M5 5h14"/><path d="M5 9h14"/><path d="M5 12h14"/><path d="M5 15h14"/><path d="M5 19h14"/>',
      fibExtension: '<path d="M4 17l5-10 5 6"/><path d="M13 8h8"/><path d="M13 12h8"/><path d="M13 16h8"/>',
      pitchfork: '<path d="M5 19L19 5"/><path d="M9 19l10-10"/><path d="M3 15l10-10"/>',
      fibChannel: '<path d="M4 18L19 8"/><path d="M6 21L21 11"/><path d="M5 20L20 10"/><path d="M5 17L20 7"/>',
      timeZones: '<path d="M5 4v16"/><path d="M9 4v16"/><path d="M14 4v16"/><path d="M21 4v16"/>',
      gridBox: '<rect x="4" y="5" width="16" height="14"/><path d="M9 5v14"/><path d="M15 5v14"/><path d="M4 10h16"/><path d="M4 15h16"/>',
      fan: '<path d="M4 19L20 5"/><path d="M4 19l16-1"/><path d="M4 19L20 12"/><path d="M4 19l7-15"/>',
      circles: '<circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="8"/>',
      spiral: '<path d="M12 12c3-1 3 3 0 4-5 1-8-4-5-8 4-6 14-3 14 5"/>',
      arcs: '<path d="M5 18a7 7 0 0 1 14 0"/><path d="M8 18a4 4 0 0 1 8 0"/>',
      wedge: '<path d="M4 18L20 6"/><path d="M4 18l16-1"/><path d="M8 16h10"/>',
      path: '<path d="M4 16c4-8 7 5 11-3 2-4 4-3 5-1"/>',
      rectangle: '<rect x="5" y="6" width="14" height="12"/>',
      ellipse: '<ellipse cx="12" cy="12" rx="8" ry="5"/>',
      curve: '<path d="M4 17c5-12 10 12 16-5"/>',
      polyline: '<path d="M4 17l5-8 5 5 6-8"/>',
      polygon: '<path d="M5 18l7-12 7 12z"/>',
      rotatedRectangle: '<path d="M8 4l12 6-4 10-12-6z"/>',
      arc: '<path d="M5 17a8 8 0 0 1 14 0"/><path d="M5 17h3"/><path d="M16 17h3"/>',
      doubleCurve: '<path d="M4 9c5-6 11 6 16 0"/><path d="M4 16c5-6 11 6 16 0"/>',
      text: '<path d="M5 6h14"/><path d="M12 6v12"/><path d="M8 18h8"/>',
      callout: '<path d="M5 6h14v9H9l-4 4z"/>',
      priceLabel: '<path d="M4 8h11l5 4-5 4H4z"/><path d="M8 12h5"/>',
      marker: '<path d="M12 4l7 16H5z"/>',
      markerLeft: '<path d="M5 12l10-7v14z"/><path d="M15 12h5"/>',
      markerRight: '<path d="M19 12L9 5v14z"/><path d="M4 12h5"/>',
      markerUp: '<path d="M12 5l7 10H5z"/><path d="M12 15v4"/>',
      markerDown: '<path d="M12 19L5 9h14z"/><path d="M12 5v4"/>',
      flag: '<path d="M6 20V5"/><path d="M6 5h12l-2 4 2 4H6"/>',
      pattern: '<path d="M3 17l5-8 4 5 4-8 5 11"/><circle cx="8" cy="9" r="1"/><circle cx="16" cy="6" r="1"/>',
      numberedWave: '<path d="M3 17l5-8 4 5 4-8 5 11"/><path d="M6 20h2"/><path d="M12 20h2"/><path d="M18 20h2"/>',
      letteredWave: '<path d="M3 17l5-8 4 5 4-8 5 11"/><path d="M5 21l1-3 1 3"/><path d="M13 18v3"/><path d="M12 18h3"/>',
      cyclicLines: '<path d="M5 4v16"/><path d="M12 4v16"/><path d="M19 4v16"/><path d="M5 18c3-4 4-4 7 0s4 4 7 0"/>',
      sine: '<path d="M3 12c3-8 6 8 9 0s6-8 9 0"/>',
      positionLong: '<rect x="5" y="5" width="14" height="14"/><path d="M5 12h14"/><path d="M12 16V8"/><path d="M8 12l4-4 4 4"/>',
      positionShort: '<rect x="5" y="5" width="14" height="14"/><path d="M5 12h14"/><path d="M12 8v8"/><path d="M8 12l4 4 4-4"/>',
      positionForecast: '<rect x="5" y="6" width="14" height="12"/><path d="M7 15l4-4 3 2 3-5"/>',
      dateRange: '<path d="M5 7v10"/><path d="M19 7v10"/><path d="M5 12h14"/><path d="M8 9l-3 3 3 3"/><path d="M16 9l3 3-3 3"/>',
      priceRange: '<path d="M8 5h8"/><path d="M8 19h8"/><path d="M12 5v14"/><path d="M9 8l3-3 3 3"/><path d="M9 16l3 3 3-3"/>',
      datePriceRange: '<rect x="5" y="6" width="14" height="12"/><path d="M5 12h14"/><path d="M12 6v12"/>',
      barsPattern: '<path d="M5 17v-6"/><path d="M9 17V7"/><path d="M13 17v-8"/><path d="M17 17V5"/>',
      ghostFeed: '<path d="M5 17v-6" opacity=".45"/><path d="M9 17V7" opacity=".45"/><path d="M13 17v-8" opacity=".45"/><path d="M17 17V5" opacity=".45"/><path d="M4 20h16"/>',
      sector: '<path d="M7 18L17 6"/><path d="M7 18h12"/><path d="M7 18a12 12 0 0 1 10-12"/>',
      volumeProfile: '<path d="M5 6h11"/><path d="M5 10h14"/><path d="M5 14h9"/><path d="M5 18h13"/>'
    };

    return svg(icons[kind] || icons.marker);
  }

  function drawingToolStripHtml() {
    var categoryOrder = [
      ['trend', 'Trend Lines', 'trendline'],
      ['fibonacci', 'Fibonacci & Gann', 'fib_retracement'],
      ['geometric', 'Geometric Shapes', 'rectangle'],
      ['annotation', 'Text & Notes', 'text'],
      ['pattern', 'Patterns', 'xabcd_pattern'],
      ['measurement', 'Measure & Forecast', 'date_range'],
      ['icon', 'Icons', 'icon'],
      ['sticker', 'Stickers', 'sticker'],
      ['emoji', 'Emoji', 'emoji']
    ];
    var byCategory = {};
    Object.keys(DRAWING_TOOLS).forEach(function (key) {
      var tool = DRAWING_TOOLS[key];
      if (!byCategory[tool.category]) byCategory[tool.category] = [];
      byCategory[tool.category].push(tool);
    });
    var html = ['<div class="sce-drawing-tools-scroll">'];
    categoryOrder.forEach(function (category) {
      var tools = byCategory[category[0]];
      if (!tools || !tools.length) return;
      var representative = DRAWING_TOOLS[category[2]] || tools[0];
      html.push(
        '<details class="sce-drawing-tool-group">',
        '<summary title="', escapeHtml(category[1]), '" aria-label="', escapeHtml(category[1]), '">',
        representative.icon,
        '<span class="sce-drawing-tool-caret">&rsaquo;</span>',
        '</summary>',
        '<div class="sce-drawing-tool-menu" role="menu" aria-label="', escapeHtml(category[1]), '">'
      );
      tools.forEach(function (tool) {
        html.push(
          '<button type="button" data-sce-drawing-tool="', escapeHtml(tool.id), '" title="', escapeHtml(tool.name), '" role="menuitem">',
          tool.icon,
          '<span>', escapeHtml(tool.name), '</span>',
          '</button>'
        );
      });
      html.push('</div></details>');
    });
    html.push('</div><div class="sce-drawing-tools-bottom">');
    html.push('<button type="button" data-sce-action="toggle-magnet" title="Magnet mode" aria-label="Magnet mode" aria-pressed="false">', paneControlIconSvg('magnet'), '</button>');
    html.push('<button type="button" data-sce-action="toggle-stay-drawing" title="Stay in drawing mode" aria-label="Stay in drawing mode" aria-pressed="false">', paneControlIconSvg('repeat'), '</button>');
    html.push('<button type="button" data-sce-action="toggle-lock-drawings" title="Lock drawings" aria-label="Lock drawings" aria-pressed="false">', paneControlIconSvg('lock'), '</button>');
    html.push('<button type="button" data-sce-action="toggle-drawings-visible" title="Hide drawings" aria-label="Hide drawings" aria-pressed="false">', paneControlIconSvg('eye-off'), '</button>');
    html.push('<button type="button" data-sce-action="export-image" title="Export image" aria-label="Export image">', paneControlIconSvg('download'), '</button>');
    html.push('<button type="button" data-sce-action="copy-image" title="Copy image" aria-label="Copy image">', paneControlIconSvg('copy'), '</button>');
    html.push('<button type="button" data-sce-action="clear-drawings" title="Clear drawings" aria-label="Clear drawings">', paneControlIconSvg('trash'), '</button>');
    html.push('</div>');
    return html.join('');
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

  function normalizePeriod(period) {
    var normalized = String(period || '').toLowerCase();
    var map = {
      d: 'daily',
      day: 'daily',
      daily: 'daily',
      '1d': 'daily',
      w: 'weekly',
      week: 'weekly',
      weekly: 'weekly',
      '1w': 'weekly',
      m: 'monthly',
      month: 'monthly',
      monthly: 'monthly',
      '1m': 'monthly',
      q: 'quarterly',
      quarter: 'quarterly',
      quarterly: 'quarterly',
      '3m': 'quarterly',
      y: 'yearly',
      year: 'yearly',
      yearly: 'yearly',
      '1y': 'yearly'
    };
    return map[normalized] || 'daily';
  }

  function periodInterval(period) {
    var normalized = normalizePeriod(period);
    return CHART_PERIODS[normalized].interval;
  }

  function normalizeDateRangePreset(presetId) {
    var normalized = String(presetId || '').toLowerCase();
    for (var i = 0; i < DATE_RANGE_PRESETS.length; i += 1) {
      if (DATE_RANGE_PRESETS[i].id === normalized) return normalized;
    }
    return null;
  }

  function dateRangePresetById(presetId) {
    var normalized = normalizeDateRangePreset(presetId);
    for (var i = 0; i < DATE_RANGE_PRESETS.length; i += 1) {
      if (DATE_RANGE_PRESETS[i].id === normalized) return DATE_RANGE_PRESETS[i];
    }
    return null;
  }

  function shiftUtcMonths(time, amount) {
    var date = new Date(time * 1000);
    date.setUTCMonth(date.getUTCMonth() + amount);
    return Math.floor(date.getTime() / 1000);
  }

  function shiftUtcYears(time, amount) {
    var date = new Date(time * 1000);
    date.setUTCFullYear(date.getUTCFullYear() + amount);
    return Math.floor(date.getTime() / 1000);
  }

  function startOfUtcYear(time) {
    var date = new Date(time * 1000);
    return Math.floor(Date.UTC(date.getUTCFullYear(), 0, 1) / 1000);
  }

  function periodBucketStart(time, period) {
    var date = new Date(time * 1000);
    var year = date.getUTCFullYear();
    var month = date.getUTCMonth();
    var day = date.getUTCDate();
    if (period === 'weekly') {
      var dayOfWeek = date.getUTCDay() || 7;
      var start = Date.UTC(year, month, day);
      return Math.floor((start - (dayOfWeek - 1) * 86400000) / 1000);
    }
    if (period === 'monthly') return Math.floor(Date.UTC(year, month, 1) / 1000);
    if (period === 'quarterly') return Math.floor(Date.UTC(year, Math.floor(month / 3) * 3, 1) / 1000);
    if (period === 'yearly') return Math.floor(Date.UTC(year, 0, 1) / 1000);
    return Math.floor(Date.UTC(year, month, day) / 1000);
  }

  function aggregateBars(bars, period) {
    var normalizedPeriod = normalizePeriod(period);
    var normalizedBars = normalizeBars(bars);
    if (normalizedPeriod === 'daily') return normalizedBars;
    var buckets = [];
    var current = null;
    normalizedBars.forEach(function (bar) {
      var bucketTime = periodBucketStart(bar.time, normalizedPeriod);
      if (!current || current.time !== bucketTime) {
        current = {
          time: bucketTime,
          open: bar.open,
          high: bar.high,
          low: bar.low,
          close: bar.close,
          volume: bar.volume
        };
        buckets.push(current);
        return;
      }
      current.high = Math.max(current.high, bar.high);
      current.low = Math.min(current.low, bar.low);
      current.close = bar.close;
      current.volume += bar.volume;
    });
    return buckets;
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

  function rollingWma(data, length) {
    var out = [];
    var denominator = length * (length + 1) / 2;
    for (var i = length - 1; i < data.length; i += 1) {
      var sum = 0;
      for (var j = 0; j < length; j += 1) {
        sum += data[i - j].value * (length - j);
      }
      out.push({ time: data[i].time, value: sum / denominator });
    }
    return out;
  }

  function rollingVwma(bars, length) {
    var out = [];
    for (var i = length - 1; i < bars.length; i += 1) {
      var priceVolume = 0;
      var volume = 0;
      for (var j = i - length + 1; j <= i; j += 1) {
        priceVolume += bars[j].close * bars[j].volume;
        volume += bars[j].volume;
      }
      out.push({ time: bars[i].time, value: volume ? priceVolume / volume : bars[i].close });
    }
    return out;
  }

  function rollingLinReg(data, length) {
    var out = [];
    var xMean = (length - 1) / 2;
    var denominator = 0;
    for (var x = 0; x < length; x += 1) denominator += Math.pow(x - xMean, 2);
    for (var i = length - 1; i < data.length; i += 1) {
      var yMean = 0;
      for (var j = 0; j < length; j += 1) yMean += data[i - length + 1 + j].value;
      yMean /= length;
      var numerator = 0;
      for (var k = 0; k < length; k += 1) numerator += (k - xMean) * (data[i - length + 1 + k].value - yMean);
      var slope = denominator ? numerator / denominator : 0;
      out.push({ time: data[i].time, value: yMean + slope * xMean });
    }
    return out;
  }

  function combineSeries(left, right, combiner) {
    var byTime = {};
    left.forEach(function (point) {
      byTime[point.time] = { left: point.value };
    });
    right.forEach(function (point) {
      if (!byTime[point.time]) byTime[point.time] = {};
      byTime[point.time].right = point.value;
    });
    return Object.keys(byTime).map(function (time) {
      var pair = byTime[time];
      return pair.left != null && pair.right != null ? { time: Number(time), value: combiner(pair.left, pair.right) } : null;
    }).filter(Boolean).sort(function (a, b) { return a.time - b.time; });
  }

  function typicalPriceSeries(bars) {
    return bars.map(function (bar) {
      return { time: bar.time, value: (bar.high + bar.low + bar.close) / 3 };
    });
  }

  function highestLowest(bars, index, length) {
    var high = -Infinity;
    var low = Infinity;
    for (var i = index - length + 1; i <= index; i += 1) {
      high = Math.max(high, bars[i].high);
      low = Math.min(low, bars[i].low);
    }
    return { high: high, low: low };
  }

  function rollingSum(data, length) {
    var out = [];
    var sum = 0;
    for (var i = 0; i < data.length; i += 1) {
      sum += data[i].value;
      if (i >= length) sum -= data[i - length].value;
      if (i >= length - 1) out.push({ time: data[i].time, value: sum });
    }
    return out;
  }

  function sumWindow(data, endIndex, length) {
    var sum = 0;
    var start = Math.max(0, endIndex - length + 1);
    for (var i = start; i <= endIndex; i += 1) sum += data[i].value || 0;
    return sum;
  }

  function computeAroon(bars, length) {
    var up = [];
    var down = [];
    for (var i = length - 1; i < bars.length; i += 1) {
      var highIndex = i - length + 1;
      var lowIndex = highIndex;
      for (var j = i - length + 1; j <= i; j += 1) {
        if (bars[j].high >= bars[highIndex].high) highIndex = j;
        if (bars[j].low <= bars[lowIndex].low) lowIndex = j;
      }
      up.push({ time: bars[i].time, value: ((length - (i - highIndex)) / length) * 100 });
      down.push({ time: bars[i].time, value: ((length - (i - lowIndex)) / length) * 100 });
    }
    return { up: up, down: down };
  }

  function computeUltimateOscillator(bars, fast, middle, slow) {
    var bp = [];
    var tr = [];
    for (var i = 0; i < bars.length; i += 1) {
      var previousClose = i ? bars[i - 1].close : bars[i].close;
      bp.push({ time: bars[i].time, value: bars[i].close - Math.min(bars[i].low, previousClose) });
      tr.push({ time: bars[i].time, value: Math.max(bars[i].high, previousClose) - Math.min(bars[i].low, previousClose) });
    }
    var out = [];
    for (var j = slow - 1; j < bars.length; j += 1) {
      var fastAvg = sumWindow(tr, j, fast) ? sumWindow(bp, j, fast) / sumWindow(tr, j, fast) : 0;
      var midAvg = sumWindow(tr, j, middle) ? sumWindow(bp, j, middle) / sumWindow(tr, j, middle) : 0;
      var slowAvg = sumWindow(tr, j, slow) ? sumWindow(bp, j, slow) / sumWindow(tr, j, slow) : 0;
      out.push({ time: bars[j].time, value: 100 * ((4 * fastAvg + 2 * midAvg + slowAvg) / 7) });
    }
    return out;
  }

  function computeVortex(bars, length) {
    var plus = [];
    var minus = [];
    var tr = [];
    for (var i = 1; i < bars.length; i += 1) {
      plus.push({ time: bars[i].time, value: Math.abs(bars[i].high - bars[i - 1].low) });
      minus.push({ time: bars[i].time, value: Math.abs(bars[i].low - bars[i - 1].high) });
      tr.push({ time: bars[i].time, value: Math.max(bars[i].high - bars[i].low, Math.abs(bars[i].high - bars[i - 1].close), Math.abs(bars[i].low - bars[i - 1].close)) });
    }
    return {
      plus: combineSeries(rollingSum(plus, length), rollingSum(tr, length), function (a, b) { return b ? a / b : 0; }),
      minus: combineSeries(rollingSum(minus, length), rollingSum(tr, length), function (a, b) { return b ? a / b : 0; })
    };
  }

  function computeFisher(bars, length) {
    var out = [];
    var previousValue = 0;
    var previousFish = 0;
    for (var i = length - 1; i < bars.length; i += 1) {
      var high = -Infinity;
      var low = Infinity;
      for (var j = i - length + 1; j <= i; j += 1) {
        high = Math.max(high, bars[j].high);
        low = Math.min(low, bars[j].low);
      }
      var mid = (bars[i].high + bars[i].low) / 2;
      var value = high === low ? 0 : 0.33 * (2 * ((mid - low) / (high - low) - 0.5)) + 0.67 * previousValue;
      value = clamp(value, -0.999, 0.999);
      var fish = 0.5 * Math.log((1 + value) / (1 - value)) + 0.5 * previousFish;
      out.push({ time: bars[i].time, value: fish });
      previousValue = value;
      previousFish = fish;
    }
    return out;
  }

  function cumulativeLine(bars, mapper) {
    var total = 0;
    return bars.map(function (bar, index) {
      total += mapper(bar, index, bars);
      return { time: bar.time, value: total };
    });
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
      defaultPanePolicy: 'source',
      defaultInputs: {},
      defaultStyles: { value: { color: null, opacity: 0.55 } },
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

  merge(Indicators, {
    WMA: {
      id: 'WMA',
      name: 'Weighted Moving Average',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = rollingWma(compactSeries(sourceSeries(context, indicator.source)), Math.max(1, input.length));
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    HMA: {
      id: 'HMA',
      name: 'Hull Moving Average',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var length = Math.max(2, input.length);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var half = rollingWma(data, Math.max(1, Math.round(length / 2)));
        var full = rollingWma(data, length);
        var diff = combineSeries(half, full, function (a, b) { return 2 * a - b; });
        var value = rollingWma(diff, Math.max(1, Math.round(Math.sqrt(length))));
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    DEMA: {
      id: 'DEMA',
      name: 'Double EMA',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var ema1 = rollingEma(compactSeries(sourceSeries(context, indicator.source)), input.length);
        var ema2 = rollingEma(ema1, input.length);
        return createIndicatorResult(indicator, { value: combineSeries(ema1, ema2, function (a, b) { return 2 * a - b; }) }, [{ output: 'value', type: 'line' }]);
      }
    },
    TEMA: {
      id: 'TEMA',
      name: 'Triple EMA',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var ema1 = rollingEma(compactSeries(sourceSeries(context, indicator.source)), input.length);
        var ema2 = rollingEma(ema1, input.length);
        var ema3 = rollingEma(ema2, input.length);
        var value = [];
        ema1.forEach(function (point) {
          var two = seriesValueAt(ema2, point.time);
          var three = seriesValueAt(ema3, point.time);
          if (two != null && three != null) value.push({ time: point.time, value: 3 * point.value - 3 * two + three });
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    ROC: {
      id: 'ROC',
      name: 'Rate of Change',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 12 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var value = [];
        for (var i = input.length; i < data.length; i += 1) {
          value.push({ time: data[i].time, value: data[i - input.length].value ? ((data[i].value - data[i - input.length].value) / data[i - input.length].value) * 100 : 0 });
        }
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    MOM: {
      id: 'MOM',
      name: 'Momentum',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 10 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var value = [];
        for (var i = input.length; i < data.length; i += 1) value.push({ time: data[i].time, value: data[i].value - data[i - input.length].value });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    CCI: {
      id: 'CCI',
      name: 'Commodity Channel Index',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var typical = typicalPriceSeries(context.bars);
        var basis = rollingSma(typical, input.length);
        var value = [];
        for (var i = input.length - 1; i < typical.length; i += 1) {
          var mean = seriesValueAt(basis, typical[i].time);
          if (mean == null) continue;
          var deviation = 0;
          for (var j = i - input.length + 1; j <= i; j += 1) deviation += Math.abs(typical[j].value - mean);
          deviation /= input.length;
          value.push({ time: typical[i].time, value: deviation ? (typical[i].value - mean) / (0.015 * deviation) : 0 });
        }
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'level100', type: 'level', value: 100 }, { output: 'level-100', type: 'level', value: -100 }]);
      }
    },
    MFI: {
      id: 'MFI',
      name: 'Money Flow Index',
      category: 'Volume',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var pos = [], neg = [];
        for (var i = 0; i < context.bars.length; i += 1) {
          var typical = (context.bars[i].high + context.bars[i].low + context.bars[i].close) / 3;
          var prevTypical = i ? (context.bars[i - 1].high + context.bars[i - 1].low + context.bars[i - 1].close) / 3 : typical;
          var flow = typical * context.bars[i].volume;
          pos.push({ time: context.bars[i].time, value: typical > prevTypical ? flow : 0 });
          neg.push({ time: context.bars[i].time, value: typical < prevTypical ? flow : 0 });
        }
        var posSum = rollingSum(pos, input.length);
        var negSum = rollingSum(neg, input.length);
        var value = combineSeries(posSum, negSum, function (a, b) { return b === 0 ? 100 : 100 - (100 / (1 + a / b)); });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'level80', type: 'level', value: 80 }, { output: 'level20', type: 'level', value: 20 }]);
      }
    },
    ADX: {
      id: 'ADX',
      name: 'Average Directional Index',
      category: 'Trend',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { adx: { color: null, lineWidth: 2 }, plusDI: { color: null, lineWidth: 1 }, minusDI: { color: null, lineWidth: 1 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var plusDM = [], minusDM = [], tr = [];
        for (var i = 1; i < context.bars.length; i += 1) {
          var upMove = context.bars[i].high - context.bars[i - 1].high;
          var downMove = context.bars[i - 1].low - context.bars[i].low;
          var previousClose = context.bars[i - 1].close;
          plusDM.push({ time: context.bars[i].time, value: upMove > downMove && upMove > 0 ? upMove : 0 });
          minusDM.push({ time: context.bars[i].time, value: downMove > upMove && downMove > 0 ? downMove : 0 });
          tr.push({ time: context.bars[i].time, value: Math.max(context.bars[i].high - context.bars[i].low, Math.abs(context.bars[i].high - previousClose), Math.abs(context.bars[i].low - previousClose)) });
        }
        var trEma = rollingEma(tr, input.length);
        var plusDI = combineSeries(rollingEma(plusDM, input.length), trEma, function (a, b) { return b ? 100 * a / b : 0; });
        var minusDI = combineSeries(rollingEma(minusDM, input.length), trEma, function (a, b) { return b ? 100 * a / b : 0; });
        var dx = combineSeries(plusDI, minusDI, function (a, b) { return (a + b) ? 100 * Math.abs(a - b) / (a + b) : 0; });
        var adx = rollingEma(dx, input.length);
        return createIndicatorResult(indicator, { adx: adx, plusDI: plusDI, minusDI: minusDI }, [{ output: 'adx', type: 'line' }, { output: 'plusDI', type: 'line' }, { output: 'minusDI', type: 'line' }]);
      }
    },
    OBV: {
      id: 'OBV',
      name: 'On Balance Volume',
      category: 'Volume',
      defaultPanePolicy: 'new',
      defaultInputs: {},
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var value = cumulativeLine(context.bars, function (bar, index, bars) {
          if (!index) return 0;
          return bar.close > bars[index - 1].close ? bar.volume : (bar.close < bars[index - 1].close ? -bar.volume : 0);
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    ADL: {
      id: 'ADL',
      name: 'Accumulation/Distribution Line',
      category: 'Volume',
      defaultPanePolicy: 'new',
      defaultInputs: {},
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var value = cumulativeLine(context.bars, function (bar) {
          var range = bar.high - bar.low;
          return range ? (((bar.close - bar.low) - (bar.high - bar.close)) / range) * bar.volume : 0;
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    CMF: {
      id: 'CMF',
      name: 'Chaikin Money Flow',
      category: 'Volume',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var mfv = context.bars.map(function (bar) {
          var range = bar.high - bar.low;
          var multiplier = range ? (((bar.close - bar.low) - (bar.high - bar.close)) / range) : 0;
          return { time: bar.time, value: multiplier * bar.volume };
        });
        var vol = context.bars.map(function (bar) { return { time: bar.time, value: bar.volume }; });
        var value = combineSeries(rollingSum(mfv, input.length), rollingSum(vol, input.length), function (a, b) { return b ? a / b : 0; });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    WILLIAMS: {
      id: 'WILLIAMS',
      name: 'Williams %R',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = [];
        for (var i = input.length - 1; i < context.bars.length; i += 1) {
          var range = highestLowest(context.bars, i, input.length);
          value.push({ time: context.bars[i].time, value: range.high === range.low ? -50 : ((range.high - context.bars[i].close) / (range.high - range.low)) * -100 });
        }
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'level-20', type: 'level', value: -20 }, { output: 'level-80', type: 'level', value: -80 }]);
      }
    },
    DONCHIAN: {
      id: 'DONCHIAN',
      name: 'Donchian Channels',
      category: 'Volatility',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { upper: { color: null, lineWidth: 1 }, middle: { color: null, lineWidth: 1 }, lower: { color: null, lineWidth: 1 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var upper = [], middle = [], lower = [];
        for (var i = input.length - 1; i < context.bars.length; i += 1) {
          var range = highestLowest(context.bars, i, input.length);
          upper.push({ time: context.bars[i].time, value: range.high });
          lower.push({ time: context.bars[i].time, value: range.low });
          middle.push({ time: context.bars[i].time, value: (range.high + range.low) / 2 });
        }
        return createIndicatorResult(indicator, { upper: upper, middle: middle, lower: lower }, [{ output: 'upper', type: 'line' }, { output: 'middle', type: 'line' }, { output: 'lower', type: 'line' }]);
      }
    },
    KELTNER: {
      id: 'KELTNER',
      name: 'Keltner Channels',
      category: 'Volatility',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20, multiplier: 2 },
      defaultStyles: { upper: { color: null, lineWidth: 1 }, basis: { color: null, lineWidth: 1 }, lower: { color: null, lineWidth: 1 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var basis = rollingEma(typicalPriceSeries(context.bars), input.length);
        var atr = computeAtr(context.bars, input.length);
        var upper = combineSeries(basis, atr, function (a, b) { return a + input.multiplier * b; });
        var lower = combineSeries(basis, atr, function (a, b) { return a - input.multiplier * b; });
        return createIndicatorResult(indicator, { upper: upper, basis: basis, lower: lower }, [{ output: 'upper', type: 'line' }, { output: 'basis', type: 'line' }, { output: 'lower', type: 'line' }]);
      }
    },
    ICHIMOKU: {
      id: 'ICHIMOKU',
      name: 'Ichimoku Cloud',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { conversion: 9, base: 26, spanB: 52 },
      defaultStyles: { conversion: { color: null, lineWidth: 1 }, base: { color: null, lineWidth: 1 }, spanA: { color: null, lineWidth: 1 }, spanB: { color: null, lineWidth: 1 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var conversion = [], base = [], spanA = [], spanB = [];
        for (var i = 0; i < context.bars.length; i += 1) {
          if (i >= input.conversion - 1) {
            var c = highestLowest(context.bars, i, input.conversion);
            conversion.push({ time: context.bars[i].time, value: (c.high + c.low) / 2 });
          }
          if (i >= input.base - 1) {
            var b = highestLowest(context.bars, i, input.base);
            base.push({ time: context.bars[i].time, value: (b.high + b.low) / 2 });
          }
          var conv = seriesValueAt(conversion, context.bars[i].time);
          var bas = seriesValueAt(base, context.bars[i].time);
          if (conv != null && bas != null) spanA.push({ time: context.bars[i].time, value: (conv + bas) / 2 });
          if (i >= input.spanB - 1) {
            var s = highestLowest(context.bars, i, input.spanB);
            spanB.push({ time: context.bars[i].time, value: (s.high + s.low) / 2 });
          }
        }
        return createIndicatorResult(indicator, { conversion: conversion, base: base, spanA: spanA, spanB: spanB }, [{ output: 'conversion', type: 'line' }, { output: 'base', type: 'line' }, { output: 'spanA', type: 'line' }, { output: 'spanB', type: 'line' }]);
      }
    },
    SUPERTREND: {
      id: 'SUPERTREND',
      name: 'SuperTrend',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 10, multiplier: 3 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var atr = computeAtr(context.bars, input.length);
        var value = [];
        var trend = 1;
        var previous = null;
        context.bars.forEach(function (bar) {
          var atrValue = seriesValueAt(atr, bar.time);
          if (atrValue == null) return;
          var hl2 = (bar.high + bar.low) / 2;
          var upper = hl2 + input.multiplier * atrValue;
          var lower = hl2 - input.multiplier * atrValue;
          if (previous != null) {
            if (bar.close > previous) trend = 1;
            if (bar.close < previous) trend = -1;
          }
          previous = trend === 1 ? lower : upper;
          value.push({ time: bar.time, value: previous });
        });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    PIVOTS: {
      id: 'PIVOTS',
      name: 'Pivot Points',
      category: 'Support/Resistance',
      defaultPanePolicy: 'source',
      defaultInputs: {},
      defaultStyles: { pivot: { color: null, lineWidth: 1 }, r1: { color: null, lineWidth: 1 }, s1: { color: null, lineWidth: 1 } },
      compute: function (context, indicator) {
        var pivot = [], r1 = [], s1 = [];
        for (var i = 1; i < context.bars.length; i += 1) {
          var previous = context.bars[i - 1];
          var p = (previous.high + previous.low + previous.close) / 3;
          pivot.push({ time: context.bars[i].time, value: p });
          r1.push({ time: context.bars[i].time, value: 2 * p - previous.low });
          s1.push({ time: context.bars[i].time, value: 2 * p - previous.high });
        }
        return createIndicatorResult(indicator, { pivot: pivot, r1: r1, s1: s1 }, [{ output: 'pivot', type: 'line' }, { output: 'r1', type: 'line' }, { output: 's1', type: 'line' }]);
      }
    },
    PSAR: {
      id: 'PSAR',
      name: 'Parabolic SAR',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { step: 0.02, max: 0.2 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        if (context.bars.length < 2) return createIndicatorResult(indicator, { value: [] }, [{ output: 'value', type: 'line' }]);
        var value = [];
        var rising = context.bars[1].close >= context.bars[0].close;
        var sar = rising ? context.bars[0].low : context.bars[0].high;
        var ep = rising ? context.bars[0].high : context.bars[0].low;
        var af = input.step;
        for (var i = 1; i < context.bars.length; i += 1) {
          sar += af * (ep - sar);
          if (rising) {
            if (context.bars[i].low < sar) {
              rising = false;
              sar = ep;
              ep = context.bars[i].low;
              af = input.step;
            } else if (context.bars[i].high > ep) {
              ep = context.bars[i].high;
              af = Math.min(input.max, af + input.step);
            }
          } else if (context.bars[i].high > sar) {
            rising = true;
            sar = ep;
            ep = context.bars[i].high;
            af = input.step;
          } else if (context.bars[i].low < ep) {
            ep = context.bars[i].low;
            af = Math.min(input.max, af + input.step);
          }
          value.push({ time: context.bars[i].time, value: sar });
        }
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }]);
      }
    },
    TRIX: {
      id: 'TRIX',
      name: 'TRIX',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 15 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var ema1 = rollingEma(compactSeries(sourceSeries(context, indicator.source)), input.length);
        var ema2 = rollingEma(ema1, input.length);
        var ema3 = rollingEma(ema2, input.length);
        var value = [];
        for (var i = 1; i < ema3.length; i += 1) {
          value.push({ time: ema3[i].time, value: ema3[i - 1].value ? ((ema3[i].value - ema3[i - 1].value) / ema3[i - 1].value) * 100 : 0 });
        }
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    AROON: {
      id: 'AROON',
      name: 'Aroon',
      category: 'Trend',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 25 },
      defaultStyles: { up: { color: null, lineWidth: 2 }, down: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = computeAroon(context.bars, Math.max(1, input.length));
        return createIndicatorResult(indicator, value, [{ output: 'up', type: 'line' }, { output: 'down', type: 'line' }, { output: 'level50', type: 'level', value: 50 }]);
      }
    },
    AO: {
      id: 'AO',
      name: 'Awesome Oscillator',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { fast: 5, slow: 34 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var median = context.bars.map(function (bar) { return { time: bar.time, value: (bar.high + bar.low) / 2 }; });
        var value = combineSeries(rollingSma(median, input.fast), rollingSma(median, input.slow), function (a, b) { return a - b; });
        return createIndicatorResult(indicator, { value: value }, [{ output: 'value', type: 'histogram' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    STOCHRSI: {
      id: 'STOCHRSI',
      name: 'Stochastic RSI',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14, smoothK: 3, smoothD: 3 },
      defaultStyles: { k: { color: null, lineWidth: 2 }, d: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var rsi = computeRsi(compactSeries(sourceSeries(context, indicator.source)), input.length);
        var raw = [];
        for (var i = input.length - 1; i < rsi.length; i += 1) {
          var high = -Infinity;
          var low = Infinity;
          for (var j = i - input.length + 1; j <= i; j += 1) {
            high = Math.max(high, rsi[j].value);
            low = Math.min(low, rsi[j].value);
          }
          raw.push({ time: rsi[i].time, value: high === low ? 50 : ((rsi[i].value - low) / (high - low)) * 100 });
        }
        var k = rollingSma(raw, input.smoothK);
        return createIndicatorResult(indicator, { k: k, d: rollingSma(k, input.smoothD) }, [{ output: 'k', type: 'line' }, { output: 'd', type: 'line' }, { output: 'level80', type: 'level', value: 80 }, { output: 'level20', type: 'level', value: 20 }]);
      }
    },
    UO: {
      id: 'UO',
      name: 'Ultimate Oscillator',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { fast: 7, middle: 14, slow: 28 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        return createIndicatorResult(indicator, { value: computeUltimateOscillator(context.bars, input.fast, input.middle, input.slow) }, [{ output: 'value', type: 'line' }, { output: 'level70', type: 'level', value: 70 }, { output: 'level30', type: 'level', value: 30 }]);
      }
    },
    VORTEX: {
      id: 'VORTEX',
      name: 'Vortex Indicator',
      category: 'Trend',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 14 },
      defaultStyles: { plus: { color: null, lineWidth: 2 }, minus: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var value = computeVortex(context.bars, Math.max(1, input.length));
        return createIndicatorResult(indicator, value, [{ output: 'plus', type: 'line' }, { output: 'minus', type: 'line' }]);
      }
    },
    VWMA: {
      id: 'VWMA',
      name: 'Volume Weighted Moving Average',
      category: 'Volume',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 20 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        return createIndicatorResult(indicator, { value: rollingVwma(context.bars, Math.max(1, input.length)) }, [{ output: 'value', type: 'line' }]);
      }
    },
    LSMA: {
      id: 'LSMA',
      name: 'Least Squares Moving Average',
      category: 'Trend',
      defaultPanePolicy: 'source',
      defaultInputs: { length: 25 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        return createIndicatorResult(indicator, { value: rollingLinReg(compactSeries(sourceSeries(context, indicator.source)), Math.max(2, input.length)) }, [{ output: 'value', type: 'line' }]);
      }
    },
    FISHER: {
      id: 'FISHER',
      name: 'Fisher Transform',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { length: 10 },
      defaultStyles: { value: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        return createIndicatorResult(indicator, { value: computeFisher(context.bars, Math.max(2, input.length)) }, [{ output: 'value', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    PPO: {
      id: 'PPO',
      name: 'Percentage Price Oscillator',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { fast: 12, slow: 26, signal: 9 },
      defaultStyles: { ppo: { color: null, lineWidth: 2 }, signal: { color: null, lineWidth: 2 }, histogram: { color: null } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var ppo = combineSeries(rollingEma(data, input.fast), rollingEma(data, input.slow), function (a, b) { return b ? ((a - b) / b) * 100 : 0; });
        var signal = rollingEma(ppo, input.signal);
        var histogram = combineSeries(ppo, signal, function (a, b) { return a - b; });
        return createIndicatorResult(indicator, { ppo: ppo, signal: signal, histogram: histogram }, [{ output: 'histogram', type: 'histogram' }, { output: 'ppo', type: 'line' }, { output: 'signal', type: 'line' }]);
      }
    },
    KST: {
      id: 'KST',
      name: 'Know Sure Thing',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { signal: 9 },
      defaultStyles: { value: { color: null, lineWidth: 2 }, signal: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var data = compactSeries(sourceSeries(context, indicator.source));
        function roc(length) {
          var out = [];
          for (var i = length; i < data.length; i += 1) out.push({ time: data[i].time, value: data[i - length].value ? ((data[i].value - data[i - length].value) / data[i - length].value) * 100 : 0 });
          return out;
        }
        var kst = combineSeries(combineSeries(rollingSma(roc(10), 10), rollingSma(roc(15), 10), function (a, b) { return a + 2 * b; }), combineSeries(rollingSma(roc(20), 10), rollingSma(roc(30), 15), function (a, b) { return 3 * a + 4 * b; }), function (a, b) { return a + b; });
        var signal = rollingSma(kst, merge({}, this.defaultInputs, indicator.inputs).signal);
        return createIndicatorResult(indicator, { value: kst, signal: signal }, [{ output: 'value', type: 'line' }, { output: 'signal', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    },
    TSI: {
      id: 'TSI',
      name: 'True Strength Index',
      category: 'Momentum',
      defaultPanePolicy: 'new',
      defaultInputs: { long: 25, short: 13, signal: 7 },
      defaultStyles: { value: { color: null, lineWidth: 2 }, signal: { color: null, lineWidth: 2 } },
      compute: function (context, indicator) {
        var input = merge({}, this.defaultInputs, indicator.inputs);
        var data = compactSeries(sourceSeries(context, indicator.source));
        var momentum = [];
        var absMomentum = [];
        for (var i = 1; i < data.length; i += 1) {
          var diff = data[i].value - data[i - 1].value;
          momentum.push({ time: data[i].time, value: diff });
          absMomentum.push({ time: data[i].time, value: Math.abs(diff) });
        }
        var value = combineSeries(rollingEma(rollingEma(momentum, input.long), input.short), rollingEma(rollingEma(absMomentum, input.long), input.short), function (a, b) { return b ? 100 * a / b : 0; });
        return createIndicatorResult(indicator, { value: value, signal: rollingEma(value, input.signal) }, [{ output: 'value', type: 'line' }, { output: 'signal', type: 'line' }, { output: 'zero', type: 'level', value: 0 }]);
      }
    }
  });

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
      interval: periodInterval(options.period || options.interval || 'daily'),
      theme: options.theme || null,
      visibleRange: null,
      settings: {
        chartType: normalizeChartType(options.chartType || 'candlestick'),
        period: normalizePeriod(options.period || options.interval || 'daily'),
        dateRangePreset: null,
        priceField: 'close',
        maximizedPaneId: null,
        magnetMode: false,
        stayInDrawingMode: false,
        seriesColorIndex: 0,
        seriesColorOrder: DEFAULT_SERIES_COLOR_ORDER.slice(),
        autosave: options.autosave !== false
      },
      panes: [
        { id: 'price', title: 'Price', height: 420, seriesIds: ['main'], scaleIds: ['right'], scaleMode: 'linear', type: 'price' }
      ],
      indicators: [],
      drawings: [],
      updatedAt: new Date().toISOString()
    };
  }

  function migrateDocument(doc) {
    var savedPeriod = doc && doc.settings && doc.settings.period || doc && doc.interval;
    var migrated = merge(createDefaultDocument(), doc || {});
    migrated.schemaVersion = SCHEMA_VERSION;
    migrated.panes = migrated.panes && migrated.panes.length ? migrated.panes : createDefaultDocument().panes;
    migrated.panes.forEach(function (pane) {
      pane.scaleMode = normalizeScaleMode(pane.scaleMode);
      pane.height = Math.max(80, toNumber(pane.height, pane.type === 'price' ? 420 : 180));
    });
    migrated.indicators = migrated.indicators || [];
    migrated.drawings = migrated.drawings || [];
    migrated.settings = merge(createDefaultDocument().settings, migrated.settings || {});
    migrated.settings.chartType = normalizeChartType(migrated.settings.chartType);
    migrated.settings.period = normalizePeriod(savedPeriod || migrated.settings.period || migrated.interval);
    migrated.settings.dateRangePreset = normalizeDateRangePreset(migrated.settings.dateRangePreset);
    migrated.settings.magnetMode = !!migrated.settings.magnetMode;
    migrated.settings.stayInDrawingMode = !!migrated.settings.stayInDrawingMode;
    migrated.interval = periodInterval(migrated.settings.period);
    migrated.settings.seriesColorOrder = Array.isArray(migrated.settings.seriesColorOrder) && migrated.settings.seriesColorOrder.length ? migrated.settings.seriesColorOrder : DEFAULT_SERIES_COLOR_ORDER.slice();
    migrated.settings.seriesColorIndex = Math.max(0, toNumber(migrated.settings.seriesColorIndex, 0));
    if (migrated.settings.maximizedPaneId && !migrated.panes.some(function (pane) { return pane.id === migrated.settings.maximizedPaneId; })) {
      migrated.settings.maximizedPaneId = null;
    }
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
      lineWidth: 2,
      lineStyle: 'solid'
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
    this.document = migrateDocument(options.document || (options.load !== false ? this.storage.load(this.layoutId) : null) || createDefaultDocument(options));
    this.sourceBars = normalizeBars(options.data || []);
    this.bars = aggregateBars(this.sourceBars, this.document.settings.period);
    this.document.theme = this.document.theme || getThemeName(options.theme);
    this.indicatorResults = {};
    this.paneRects = [];
    this.legendHitZones = [];
    this.scaleHitZones = [];
    this.paneControlHitZones = [];
    this.paneResizeHitZones = [];
    this.pointer = null;
    this.hoverDrawingId = null;
    this.selectedDrawingId = null;
    this.pendingDrawing = null;
    this.dragState = null;
    this.tapState = null;
    this.paneResizeState = null;
    this.panState = null;
    this.autosaveTimer = null;
    this.destroyed = false;
    this.themeListener = this.handleThemeChange.bind(this);
    this.resizeListener = this.resize.bind(this);
    this.keydownListener = this.handleDocumentKeyDown.bind(this);
    this.drawingMenuPositionListener = this.positionDrawingToolMenus.bind(this);
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
      '<details class="sce-chart-type-picker" data-sce-chart-type-picker>',
      '<summary data-sce-chart-type-button aria-label="Chart type"></summary>',
      '<div class="sce-chart-type-menu">',
      '<button type="button" data-sce-chart-type-option="candlestick">', chartTypeIconSvg('candlestick'), '<span>Candlestick</span></button>',
      '<button type="button" data-sce-chart-type-option="heikin_ashi">', chartTypeIconSvg('heikin_ashi'), '<span>Heikin Ashi</span></button>',
      '<button type="button" data-sce-chart-type-option="bar">', chartTypeIconSvg('bar'), '<span>Bar</span></button>',
      '<button type="button" data-sce-chart-type-option="line">', chartTypeIconSvg('line'), '<span>Line</span></button>',
      '<button type="button" data-sce-chart-type-option="area">', chartTypeIconSvg('area'), '<span>Area</span></button>',
      '<button type="button" data-sce-chart-type-option="baseline">', chartTypeIconSvg('baseline'), '<span>Baseline</span></button>',
      '</div>',
      '</details>',
      '<select class="sce-chart-type" data-sce-chart-type aria-label="Chart type" hidden>',
      '<option value="candlestick">Candlestick</option>',
      '<option value="heikin_ashi">Heikin Ashi</option>',
      '<option value="bar">Bar</option>',
      '<option value="line">Line</option>',
      '<option value="area">Area</option>',
      '<option value="baseline">Baseline</option>',
      '</select>',
      '<select class="sce-chart-period" data-sce-chart-period aria-label="Chart period">',
      '<option value="daily">Daily</option>',
      '<option value="weekly">Weekly</option>',
      '<option value="monthly">Monthly</option>',
      '<option value="quarterly">Quarterly</option>',
      '<option value="yearly">Yearly</option>',
      '</select>',
      '<details class="sce-date-range-picker" data-sce-date-range-picker>',
      '<summary data-sce-date-range-button aria-label="Date range"></summary>',
      '<div class="sce-date-range-menu" data-sce-date-ranges role="menu">',
      dateRangeButtonsHtml('data-sce-date-range'),
      '</div>',
      '</details>',
      '<button type="button" class="sce-toolbar-icon-button" data-sce-action="zoom-in" title="Zoom in" aria-label="Zoom in">', paneControlIconSvg('zoom-in'), '</button>',
      '<button type="button" class="sce-toolbar-icon-button" data-sce-action="zoom-out" title="Zoom out" aria-label="Zoom out">', paneControlIconSvg('zoom-out'), '</button>',
      '<button type="button" data-sce-action="fit">Fit</button>',
      '<button type="button" data-sce-action="export-image">Export PNG</button>',
      '<button type="button" data-sce-action="save">Save</button>'
    ].join('');
    this.canvasWrap = document.createElement('div');
    this.canvasWrap.className = 'sce-canvas-wrap';
    this.canvas = document.createElement('canvas');
    this.canvas.className = 'sce-canvas';
    this.canvas.setAttribute('tabindex', '0');
    this.settingsPopup = document.createElement('div');
    this.settingsPopup.className = 'sce-settings-popup';
    this.settingsPopup.setAttribute('hidden', 'hidden');
    this.paneControlsLayer = document.createElement('div');
    this.paneControlsLayer.className = 'sce-pane-controls-layer';
    this.paneControlsLayer.setAttribute('aria-hidden', 'false');
    this.drawingToolsLayer = document.createElement('div');
    this.drawingToolsLayer.className = 'sce-drawing-tools-layer';
    this.drawingToolsLayer.innerHTML = drawingToolStripHtml();
    this.canvasWrap.appendChild(this.canvas);
    this.canvasWrap.appendChild(this.settingsPopup);
    this.canvasWrap.appendChild(this.paneControlsLayer);
    this.canvasWrap.appendChild(this.drawingToolsLayer);
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
      var chartTypeOption = closestAttribute(event.target, 'data-sce-chart-type-option');
      if (chartTypeOption) {
        if (event.preventDefault) event.preventDefault();
        self.setChartType(chartTypeOption.getAttribute('data-sce-chart-type-option'));
        var picker = closestAttribute(chartTypeOption, 'data-sce-chart-type-picker');
        if (picker) picker.removeAttribute('open');
        return;
      }
      var dateRangeButton = closestAttribute(event.target, 'data-sce-date-range');
      if (dateRangeButton) {
        if (event.preventDefault) event.preventDefault();
        self.setDateRangePreset(dateRangeButton.getAttribute('data-sce-date-range'));
        var rangePicker = closestAttribute(dateRangeButton, 'data-sce-date-range-picker');
        if (rangePicker) rangePicker.removeAttribute('open');
        return;
      }
      var action = event.target && event.target.getAttribute('data-sce-action');
      if (!action) return;
      if (action === 'sma') self.addIndicator('SMA', { placement: 'source', inputs: { length: 20 } });
      if (action === 'rsi') self.addIndicator('RSI', { placement: 'new' });
      if (action === 'macd') self.addIndicator('MACD', { placement: 'new' });
      if (action === 'line') self.startDrawing('trendline');
      if (action === 'clear-drawings') self.removeAllShapes();
      if (action === 'zoom-in') self.zoomIn(0.5);
      if (action === 'zoom-out') self.zoomOut(0.5);
      if (action === 'fit') {
        self.fitContent();
        self.draw();
        self.emitChange('range:fit', { visibleRange: clone(self.document.visibleRange) });
      }
      if (action === 'log') self.togglePaneScaleMode(self.activePaneId() || 'price');
      if (action === 'theme') self.toggleTheme();
      if (action === 'export-image') self.downloadImage();
      if (action === 'save') self.save();
    });
    this.drawingToolsLayer.addEventListener('click', function (event) {
      var toolButton = closestAttribute(event.target, 'data-sce-drawing-tool');
      if (toolButton) {
        if (event.preventDefault) event.preventDefault();
        self.startDrawing(toolButton.getAttribute('data-sce-drawing-tool'));
        var details = toolButton;
        while (details && details !== self.drawingToolsLayer && details.tagName !== 'DETAILS') details = details.parentNode;
        if (details && details.tagName === 'DETAILS') details.removeAttribute('open');
        return;
      }
      var actionButton = closestAttribute(event.target, 'data-sce-action');
      if (!actionButton) return;
      if (event.preventDefault) event.preventDefault();
      var drawingAction = actionButton.getAttribute('data-sce-action');
      if (drawingAction === 'toggle-magnet') self.toggleDrawingMagnetMode();
      if (drawingAction === 'toggle-stay-drawing') self.toggleStayInDrawingMode();
      if (drawingAction === 'toggle-lock-drawings') self.setAllDrawingsLocked(!self.areAllDrawingsLocked());
      if (drawingAction === 'toggle-drawings-visible') self.setAllDrawingsVisible(!self.areAnyDrawingsVisible());
      if (drawingAction === 'clear-drawings') self.removeAllShapes();
      if (drawingAction === 'export-image') self.downloadImage();
      if (drawingAction === 'copy-image') {
        self.copyImage().catch(function (error) {
          console.error(error);
        });
      }
      self.updateDrawingUtilityButtons();
    });
    this.drawingToolsLayer.addEventListener('toggle', function (event) {
      if (!event.target || event.target.tagName !== 'DETAILS') return;
      setTimeout(function () {
        self.positionDrawingToolMenus();
      }, 0);
    }, true);
    this.drawingToolsLayer.addEventListener('scroll', function () {
      self.positionDrawingToolMenus();
    }, true);
    this.toolbar.addEventListener('change', function (event) {
      if (event.target && event.target.getAttribute('data-sce-chart-type') != null) {
        self.setChartType(event.target.value);
      }
      if (event.target && event.target.getAttribute('data-sce-chart-period') != null) {
        self.setPeriod(event.target.value);
      }
    });
    this.paneControlsLayer.addEventListener('click', function (event) {
      var button = closestAttribute(event.target, 'data-sce-pane-action');
      if (!button) return;
      if (event.preventDefault) event.preventDefault();
      if (event.stopPropagation) event.stopPropagation();
      self.handlePaneControl({
        paneId: button.getAttribute('data-sce-pane-id'),
        action: button.getAttribute('data-sce-pane-action'),
        disabled: button.getAttribute('aria-disabled') === 'true'
      });
    });

    this.canvas.addEventListener('mousedown', function (event) {
      self.handlePointerDown(event);
    });
    this.canvas.addEventListener('mousemove', function (event) {
      self.handlePointerMove(event);
    });
    this.canvas.addEventListener('mouseup', function (event) {
      self.handlePointerUp(event);
    });
    this.canvas.addEventListener('touchstart', function (event) {
      self.handleTouchStart(event);
    }, { passive: false });
    this.canvas.addEventListener('touchmove', function (event) {
      self.handleTouchMove(event);
    }, { passive: false });
    this.canvas.addEventListener('touchend', function (event) {
      self.handleTouchEnd(event);
    }, { passive: false });
    this.canvas.addEventListener('touchcancel', function (event) {
      self.handleTouchCancel(event);
    }, { passive: false });
    this.canvas.addEventListener('wheel', function (event) {
      self.handleWheel(event);
    }, { passive: false });
    this.canvas.addEventListener('mouseleave', function () {
      self.handlePointerUp();
      self.pointer = null;
      self.hoverDrawingId = null;
      self.draw();
    });
    this.canvas.addEventListener('click', function (event) {
      self.handleCanvasClick(event);
    });
    this.canvas.addEventListener('dblclick', function (event) {
      self.handleCanvasDoubleClick(event);
    });
    this.canvas.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && self.pendingDrawing) {
        event.preventDefault();
        self.cancelDrawing();
        return;
      }
      if ((event.key === 'Delete' || event.key === 'Backspace') && self.selectedDrawingId) {
        event.preventDefault();
        self.removeDrawing(self.selectedDrawingId);
      }
    });
    window.addEventListener('resize', this.resizeListener);
    window.addEventListener('scroll', this.drawingMenuPositionListener, true);
    document.documentElement.addEventListener('themechange', this.themeListener);
    if (document.addEventListener) document.addEventListener('keydown', this.keydownListener);
  };

  Chart.prototype.destroy = function () {
    this.destroyed = true;
    window.removeEventListener('resize', this.resizeListener);
    window.removeEventListener('scroll', this.drawingMenuPositionListener, true);
    document.documentElement.removeEventListener('themechange', this.themeListener);
    if (document.removeEventListener) document.removeEventListener('keydown', this.keydownListener);
    clearTimeout(this.autosaveTimer);
    this.container.innerHTML = '';
  };

  Chart.prototype.handleDocumentKeyDown = function (event) {
    if (event.key !== 'Escape') return;
    if (this.settingsPopup && !this.settingsPopup.hasAttribute('hidden')) {
      event.preventDefault();
      this.closeIndicatorSettingsPopup();
      return;
    }
    if (this.pendingDrawing) {
      event.preventDefault();
      this.cancelDrawing();
    }
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
    this.bars = aggregateBars(this.sourceBars, this.document.settings.period);
    this.compute();
    this.resize();
    this.emitChange('restore', {});
  };

  Chart.prototype.save = function () {
    var saved = this.storage.save(this.layoutId, this.serialize());
    this.events.emit('save', { saved: saved, document: this.serialize() });
    return saved;
  };

  Chart.prototype.exportImage = function (options) {
    options = options || {};
    var type = options.type || 'image/png';
    var quality = options.quality;
    var includeInteraction = options.includeInteraction === true;
    if (!this.canvas || !this.canvas.toDataURL) {
      throw new Error('Chart image export requires a canvas with toDataURL support.');
    }

    var previousPointer = this.pointer;
    var previousHoverDrawingId = this.hoverDrawingId;
    var previousSelectedDrawingId = this.selectedDrawingId;
    var previousPendingDrawing = this.pendingDrawing;
    try {
      if (!includeInteraction) {
        this.pointer = null;
        this.hoverDrawingId = null;
        this.selectedDrawingId = null;
        this.pendingDrawing = null;
        this.canvas.classList.remove('sce-crosshair-drawing');
        this.draw();
      }
      return quality == null ? this.canvas.toDataURL(type) : this.canvas.toDataURL(type, quality);
    } finally {
      if (!includeInteraction) {
        this.pointer = previousPointer;
        this.hoverDrawingId = previousHoverDrawingId;
        this.selectedDrawingId = previousSelectedDrawingId;
        this.pendingDrawing = previousPendingDrawing;
        if (this.pendingDrawing) this.canvas.classList.add('sce-crosshair-drawing');
        this.draw();
      }
    }
  };

  Chart.prototype.downloadImage = function (filename, options) {
    var dataUrl = this.exportImage(options);
    var name = filename || (this.document.symbol || 'chart') + '-' + (this.document.interval || 'image') + '.png';
    if (typeof document !== 'undefined' && document.createElement) {
      var link = document.createElement('a');
      link.href = dataUrl;
      link.download = name;
      if (document.body && document.body.appendChild) document.body.appendChild(link);
      if (link.click) link.click();
      if (link.parentNode && link.parentNode.removeChild) link.parentNode.removeChild(link);
    }
    return dataUrl;
  };

  Chart.prototype.exportLayout = function (options) {
    options = options || {};
    var layout = {
      type: 'stock-chart-engine-layout',
      version: VERSION,
      schemaVersion: SCHEMA_VERSION,
      exportedAt: new Date().toISOString(),
      document: this.serialize()
    };
    if (options.includeData !== false) layout.data = clone(this.sourceBars);
    return layout;
  };

  Chart.prototype.exportLayoutJson = function (options) {
    options = options || {};
    return JSON.stringify(this.exportLayout(options), null, options.pretty === false ? 0 : 2);
  };

  Chart.prototype.importLayout = function (layout) {
    var payload = typeof layout === 'string' ? JSON.parse(layout) : clone(layout);
    if (!payload || typeof payload !== 'object') throw new Error('Chart layout import requires a layout object or JSON string.');
    var doc = payload.document || payload.chart || payload;
    if (payload.data) this.sourceBars = normalizeBars(payload.data);
    this.restore(doc);
    return this.serialize();
  };

  Chart.prototype.downloadLayout = function (filename, options) {
    options = options || {};
    var json = this.exportLayoutJson(options);
    var name = filename || (this.document.symbol || 'chart') + '-layout.json';
    if (typeof document !== 'undefined' && document.createElement) {
      var link = document.createElement('a');
      var objectUrl = null;
      if (typeof Blob !== 'undefined') {
        var urlApi = typeof URL !== 'undefined' ? URL : (typeof window !== 'undefined' ? window.URL : null);
        if (urlApi && urlApi.createObjectURL) {
          objectUrl = urlApi.createObjectURL(new Blob([json], { type: 'application/json' }));
          link.href = objectUrl;
        }
      }
      if (!link.href) link.href = 'data:application/json;charset=utf-8,' + encodeURIComponent(json);
      link.download = name;
      if (document.body && document.body.appendChild) document.body.appendChild(link);
      if (link.click) link.click();
      if (link.parentNode && link.parentNode.removeChild) link.parentNode.removeChild(link);
      if (objectUrl) {
        setTimeout(function () {
          var revokeApi = typeof URL !== 'undefined' ? URL : (typeof window !== 'undefined' ? window.URL : null);
          if (revokeApi && revokeApi.revokeObjectURL) revokeApi.revokeObjectURL(objectUrl);
        }, 0);
      }
    }
    return json;
  };

  Chart.prototype.copyLayout = function (options) {
    var text = this.exportLayoutJson(options);
    var clipboard = typeof navigator !== 'undefined' ? navigator.clipboard : null;
    if (clipboard && clipboard.writeText) {
      return clipboard.writeText(text).then(function () {
        return text;
      });
    }
    return fallbackCopyText(text, 'Copying chart layouts requires browser clipboard text support.');
  };

  Chart.prototype.createShareUrl = function (options) {
    options = options || {};
    var baseUrl = options.baseUrl;
    if (!baseUrl && typeof window !== 'undefined' && window.location) {
      baseUrl = window.location.href.split('#')[0];
    }
    if (!baseUrl) throw new Error('A base URL is required to create a chart share URL.');
    var json = this.exportLayoutJson(merge({}, options, { pretty: false }));
    var url = baseUrl + '#sce-layout=' + encodeURIComponent(json);
    var maxLength = options.maxLength || 16000;
    if (maxLength && url.length > maxLength) {
      throw new Error('This chart is too large for a share URL. Use Export Chart JSON or Copy Chart Layout instead.');
    }
    return url;
  };

  Chart.prototype.copyShareUrl = function (options) {
    var url;
    try {
      url = this.createShareUrl(options);
    } catch (error) {
      return Promise.reject(error);
    }
    var clipboard = typeof navigator !== 'undefined' ? navigator.clipboard : null;
    if (clipboard && clipboard.writeText) {
      return clipboard.writeText(url).then(function () {
        return url;
      });
    }
    return fallbackCopyText(url, 'Copying chart share URLs requires browser clipboard text support.');
  };

  Chart.prototype.exportImageBlob = function (options) {
    options = options || {};
    var self = this;
    var type = options.type || 'image/png';
    var quality = options.quality;
    var includeInteraction = options.includeInteraction === true;
    if (!this.canvas || !this.canvas.toBlob) {
      return Promise.reject(new Error('Chart image blob export requires a canvas with toBlob support.'));
    }

    return new Promise(function (resolve, reject) {
      var previousPointer = self.pointer;
      var previousHoverDrawingId = self.hoverDrawingId;
      var previousSelectedDrawingId = self.selectedDrawingId;
      var previousPendingDrawing = self.pendingDrawing;

      function restoreInteractionState() {
        if (includeInteraction) return;
        self.pointer = previousPointer;
        self.hoverDrawingId = previousHoverDrawingId;
        self.selectedDrawingId = previousSelectedDrawingId;
        self.pendingDrawing = previousPendingDrawing;
        if (self.pendingDrawing) self.canvas.classList.add('sce-crosshair-drawing');
        self.draw();
      }

      try {
        if (!includeInteraction) {
          self.pointer = null;
          self.hoverDrawingId = null;
          self.selectedDrawingId = null;
          self.pendingDrawing = null;
          self.canvas.classList.remove('sce-crosshair-drawing');
          self.draw();
        }
        self.canvas.toBlob(function (blob) {
          restoreInteractionState();
          if (blob) resolve(blob);
          else reject(new Error('Chart image export returned an empty image blob.'));
        }, type, quality);
      } catch (error) {
        restoreInteractionState();
        reject(error);
      }
    });
  };

  Chart.prototype.copyImage = function (options) {
    options = options || {};
    var clipboard = typeof navigator !== 'undefined' ? navigator.clipboard : null;
    var ClipboardItemCtor = typeof ClipboardItem !== 'undefined' ? ClipboardItem : null;
    if (!ClipboardItemCtor && typeof window !== 'undefined') ClipboardItemCtor = window.ClipboardItem;
    if (!clipboard || !clipboard.write || !ClipboardItemCtor) {
      return Promise.reject(new Error('Copying chart images requires browser clipboard image support.'));
    }
    return this.exportImageBlob(merge({ type: 'image/png' }, options)).then(function (blob) {
      var item = {};
      item[blob.type || 'image/png'] = blob;
      return clipboard.write([new ClipboardItemCtor(item)]).then(function () {
        return blob;
      });
    });
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
    this.sourceBars = normalizeBars(bars);
    this.bars = aggregateBars(this.sourceBars, this.document.settings.period);
    this.compute();
    this.fitContent();
    this.emitChange('data', { barCount: this.bars.length, sourceBarCount: this.sourceBars.length });
  };

  Chart.prototype.updateBar = function (bar) {
    var normalized = normalizeBar(bar);
    if (!normalized.time) return;
    var last = this.sourceBars[this.sourceBars.length - 1];
    if (last && last.time === normalized.time) this.sourceBars[this.sourceBars.length - 1] = normalized;
    else this.sourceBars.push(normalized);
    this.sourceBars.sort(function (a, b) { return a.time - b.time; });
    this.bars = aggregateBars(this.sourceBars, this.document.settings.period);
    this.compute();
    this.draw();
    this.emitChange('bar', { bar: normalized, period: this.document.settings.period, barCount: this.bars.length });
  };

  Chart.prototype.setChartType = function (chartType) {
    this.document.settings.chartType = normalizeChartType(chartType);
    this.draw();
    this.emitChange('chart:type', { chartType: this.document.settings.chartType });
    return this.document.settings.chartType;
  };

  Chart.prototype.setPeriod = function (period) {
    var nextPeriod = normalizePeriod(period);
    this.document.settings.period = nextPeriod;
    this.document.interval = periodInterval(nextPeriod);
    this.bars = aggregateBars(this.sourceBars, nextPeriod);
    this.compute();
    this.fitContent();
    this.draw();
    this.emitChange('chart:period', {
      period: nextPeriod,
      interval: this.document.interval,
      barCount: this.bars.length,
      sourceBarCount: this.sourceBars.length
    });
    return nextPeriod;
  };

  Chart.prototype.setDateRangePreset = function (presetId) {
    var preset = dateRangePresetById(presetId);
    if (!preset || !this.bars.length) return null;
    var lastIndex = this.bars.length - 1;
    var firstIndex = 0;
    if (!preset.all) {
      var endTime = this.bars[lastIndex].time;
      var startTime = preset.ytd ? startOfUtcYear(endTime) : preset.months ? shiftUtcMonths(endTime, -preset.months) : shiftUtcYears(endTime, -preset.years);
      for (var i = 0; i < this.bars.length; i += 1) {
        if (this.bars[i].time >= startTime) {
          firstIndex = i;
          break;
        }
      }
    }
    this.document.settings.dateRangePreset = preset.id;
    this.setVisibleIndexRange(firstIndex, lastIndex);
    this.draw();
    this.emitChange('range:preset', { preset: preset.id, visibleRange: clone(this.document.visibleRange) });
    return this.document.visibleRange;
  };

  Chart.prototype.zoom = function (factor, anchorRatio) {
    if (!this.bars.length) return false;
    this.document.settings.dateRangePreset = null;
    factor = Math.max(0.1, toNumber(factor, 1));
    anchorRatio = clamp(toNumber(anchorRatio, 0.5), 0, 1);
    var range = this.visibleIndexRange();
    var currentCount = range.to - range.from + 1;
    var minVisible = Math.min(this.bars.length, Math.max(2, this.options.minVisibleBars || 8));
    var nextCount = clamp(Math.round(currentCount * factor), minVisible, this.bars.length);
    if (nextCount === currentCount) nextCount = factor < 1 ? Math.max(minVisible, currentCount - 1) : Math.min(this.bars.length, currentCount + 1);
    var anchorIndex = range.from + anchorRatio * Math.max(1, currentCount - 1);
    var nextFrom = Math.round(anchorIndex - anchorRatio * Math.max(1, nextCount - 1));
    this.setVisibleIndexRange(nextFrom, nextFrom + nextCount - 1);
    this.draw();
    this.emitChange('range:zoom', { factor: factor, anchorRatio: anchorRatio, visibleRange: clone(this.document.visibleRange) });
    return true;
  };

  Chart.prototype.zoomIn = function (anchorRatio) {
    return this.zoom(0.78, anchorRatio);
  };

  Chart.prototype.zoomOut = function (anchorRatio) {
    return this.zoom(1.28, anchorRatio);
  };

  Chart.prototype.scroll = function (barDelta) {
    if (!this.bars.length) return false;
    barDelta = Math.round(toNumber(barDelta, 0));
    if (!barDelta) return false;
    this.document.settings.dateRangePreset = null;
    var range = this.visibleIndexRange();
    this.setVisibleIndexRange(range.from + barDelta, range.to + barDelta);
    this.draw();
    this.emitChange('range:scroll', { barDelta: barDelta, visibleRange: clone(this.document.visibleRange) });
    return true;
  };

  Chart.prototype.setPaneScaleMode = function (paneId, scaleMode) {
    var pane = this.document.panes.filter(function (item) { return item.id === paneId; })[0];
    if (!pane) return null;
    pane.scaleMode = normalizeScaleMode(scaleMode);
    this.draw();
    this.emitChange('pane:scale', { paneId: paneId, scaleMode: pane.scaleMode });
    return pane.scaleMode;
  };

  Chart.prototype.togglePaneScaleMode = function (paneId) {
    var pane = this.document.panes.filter(function (item) { return item.id === paneId; })[0];
    if (!pane) return null;
    return this.setPaneScaleMode(paneId, pane.scaleMode === 'log' ? 'linear' : 'log');
  };

  Chart.prototype.paneScaleMode = function (paneId) {
    var pane = this.document.panes.filter(function (item) { return item.id === paneId; })[0];
    return pane ? normalizeScaleMode(pane.scaleMode) : 'linear';
  };

  Chart.prototype.activePaneId = function () {
    if (this.pointer) {
      for (var i = 0; i < this.paneRects.length; i += 1) {
        var rect = this.paneRects[i];
        if (this.pointer.y >= rect.y && this.pointer.y <= rect.y + rect.height) return rect.paneId;
      }
    }
    return this.document.panes[0] ? this.document.panes[0].id : 'price';
  };

  Chart.prototype.toggleTheme = function () {
    var next = this.document.theme === 'dark' ? 'light' : 'dark';
    if (typeof document !== 'undefined') {
      document.documentElement.setAttribute('data-theme', next);
      document.documentElement.dispatchEvent(new CustomEvent('themechange', {
        detail: { theme: next, isDark: next === 'dark' }
      }));
    } else {
      this.setTheme(next);
    }
    return next;
  };

  Chart.prototype.addPane = function (pane) {
    var created = merge({
      id: uid('pane'),
      title: pane && pane.title ? pane.title : 'Indicator',
      height: pane && pane.height ? pane.height : 180,
      seriesIds: [],
      scaleIds: ['right'],
      scaleMode: 'linear',
      type: 'indicator'
    }, pane || {});
    created.scaleMode = normalizeScaleMode(created.scaleMode);
    this.document.panes.push(created);
    this.resize();
    this.emitChange('pane:add', created);
    return created.id;
  };

  Chart.prototype.movePane = function (paneId, direction) {
    var panes = this.document.panes;
    var index = panes.findIndex(function (pane) { return pane.id === paneId; });
    if (index < 0) return false;
    var delta = direction === 'down' ? 1 : -1;
    var nextIndex = index + delta;
    if (nextIndex < 0 || nextIndex >= panes.length) return false;
    var pane = panes.splice(index, 1)[0];
    panes.splice(nextIndex, 0, pane);
    this.resize();
    this.emitChange('pane:move', { paneId: paneId, from: index, to: nextIndex });
    return true;
  };

  Chart.prototype.movePaneUp = function (paneId) {
    return this.movePane(paneId, 'up');
  };

  Chart.prototype.movePaneDown = function (paneId) {
    return this.movePane(paneId, 'down');
  };

  Chart.prototype.setPaneHeight = function (paneId, height) {
    var pane = this.document.panes.filter(function (item) { return item.id === paneId; })[0];
    if (!pane) return false;
    pane.height = Math.max(80, toNumber(height, pane.height || 180));
    this.resize();
    this.emitChange('pane:height', { paneId: paneId, height: pane.height });
    return pane.height;
  };

  Chart.prototype.maximizePane = function (paneId) {
    if (!this.document.panes.some(function (pane) { return pane.id === paneId; })) return false;
    this.document.settings.maximizedPaneId = paneId;
    this.resize();
    this.emitChange('pane:maximize', { paneId: paneId });
    return true;
  };

  Chart.prototype.restorePanes = function () {
    if (!this.document.settings.maximizedPaneId) return false;
    var paneId = this.document.settings.maximizedPaneId;
    this.document.settings.maximizedPaneId = null;
    this.resize();
    this.emitChange('pane:restore', { paneId: paneId });
    return true;
  };

  Chart.prototype.togglePaneMaximize = function (paneId) {
    if (this.document.settings.maximizedPaneId === paneId) return this.restorePanes();
    return this.maximizePane(paneId);
  };

  Chart.prototype.removePane = function (paneId) {
    if (paneId === 'price') return false;
    if (this.document.settings.maximizedPaneId === paneId) this.document.settings.maximizedPaneId = null;
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
    var styles = merge({}, Indicators[type].defaultStyles || {}, options.styles || {});
    this.assignIndicatorSeriesStyles(styles);
    var indicator = {
      id: id,
      type: type,
      paneId: paneId,
      source: options.source || { kind: 'price', field: options.field || 'close' },
      inputs: merge({}, Indicators[type].defaultInputs || {}, options.inputs || {}),
      styles: styles,
      visible: options.visible !== false,
      zIndex: options.zIndex || this.document.indicators.length + 1
    };
    this.document.indicators.push(indicator);
    this.compute();
    this.draw();
    this.emitChange('indicator:add', indicator);
    return id;
  };

  Chart.prototype.nextSeriesColor = function () {
    var order = this.document.settings.seriesColorOrder || DEFAULT_SERIES_COLOR_ORDER;
    var index = this.document.settings.seriesColorIndex || 0;
    var color = order[index % order.length];
    this.document.settings.seriesColorIndex = index + 1;
    return color;
  };

  Chart.prototype.assignIndicatorSeriesStyles = function (styles) {
    var self = this;
    Object.keys(styles || {}).forEach(function (outputName) {
      var style = styles[outputName] || {};
      if (!style.color) style.color = self.nextSeriesColor();
      if (!style.lineWidth) style.lineWidth = 2;
      if (!style.lineStyle) style.lineStyle = 'solid';
      styles[outputName] = style;
    });
    return styles;
  };

  Chart.prototype.setSeriesColorOrder = function (colors) {
    if (!Array.isArray(colors) || !colors.length) return this.document.settings.seriesColorOrder;
    this.document.settings.seriesColorOrder = colors.slice();
    this.document.settings.seriesColorIndex = 0;
    this.emitChange('settings:seriesColors', { seriesColorOrder: this.document.settings.seriesColorOrder });
    return this.document.settings.seriesColorOrder;
  };

  Chart.prototype.removeIndicator = function (indicatorId) {
    var removedIds = {};
    var affectedPaneIds = {};
    var changed = true;
    while (changed) {
      changed = false;
      this.document.indicators.forEach(function (indicator) {
        var source = indicator.source || {};
        var shouldRemove = indicator.id === indicatorId || (source.kind === 'indicator' && removedIds[source.indicatorId]);
        if (shouldRemove && !removedIds[indicator.id]) {
          removedIds[indicator.id] = true;
          affectedPaneIds[indicator.paneId] = true;
          changed = true;
        }
      });
    }
    var removedCount = Object.keys(removedIds).length;
    if (!removedCount) return false;
    this.document.indicators = this.document.indicators.filter(function (indicator) {
      return !removedIds[indicator.id];
    });
    var removedPaneIds = Object.keys(affectedPaneIds).filter(function (paneId) {
      if (paneId === 'price') return false;
      return !this.document.indicators.some(function (indicator) {
        return indicator.paneId === paneId;
      });
    }, this);
    var removedPaneMap = {};
    removedPaneIds.forEach(function (paneId) {
      removedPaneMap[paneId] = true;
    });
    this.document.drawings = this.document.drawings.filter(function (drawing) {
      return !removedIds[drawing.ownerStudyId] && !removedPaneMap[drawing.paneId];
    });
    if (removedPaneIds.length) {
      this.document.panes = this.document.panes.filter(function (pane) {
        return !removedPaneMap[pane.id];
      });
      if (removedPaneMap[this.document.settings.maximizedPaneId]) {
        this.document.settings.maximizedPaneId = null;
      }
      this.paneResizeState = null;
      this.panState = null;
    }
    this.compute();
    this.resize();
    this.emitChange('indicator:remove', {
      indicatorId: indicatorId,
      removed: removedCount,
      removedIndicatorIds: Object.keys(removedIds),
      removedPaneIds: removedPaneIds
    });
    return true;
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

  Chart.prototype.updateIndicatorSettings = function (indicatorId, settings) {
    var indicator = this.document.indicators.filter(function (item) { return item.id === indicatorId; })[0];
    if (!indicator) return false;
    settings = settings || {};
    if (settings.inputs) {
      indicator.inputs = merge({}, indicator.inputs || {}, sanitizeIndicatorInputs(settings.inputs));
    }
    if (settings.styles) {
      indicator.styles = merge({}, indicator.styles || {}, sanitizeIndicatorStyles(settings.styles));
    }
    this.assignIndicatorSeriesStyles(indicator.styles);
    this.compute();
    this.draw();
    this.emitChange('indicator:settings', indicator);
    return true;
  };

  Chart.prototype.setIndicatorInput = function (indicatorId, inputName, value) {
    var inputs = {};
    inputs[inputName] = value;
    return this.updateIndicatorSettings(indicatorId, { inputs: inputs });
  };

  Chart.prototype.setIndicatorStyle = function (indicatorId, outputName, style) {
    var styles = {};
    styles[outputName || 'value'] = style || {};
    return this.updateIndicatorSettings(indicatorId, { styles: styles });
  };

  Chart.prototype.hitTestLegend = function (pointer) {
    for (var i = this.legendHitZones.length - 1; i >= 0; i -= 1) {
      var zone = this.legendHitZones[i];
      if (pointer.x >= zone.x && pointer.x <= zone.x + zone.width && pointer.y >= zone.y && pointer.y <= zone.y + zone.height) {
        return zone;
      }
    }
    return null;
  };

  Chart.prototype.hitTestScaleMode = function (pointer) {
    for (var i = this.scaleHitZones.length - 1; i >= 0; i -= 1) {
      var zone = this.scaleHitZones[i];
      if (pointer.x >= zone.x && pointer.x <= zone.x + zone.width && pointer.y >= zone.y && pointer.y <= zone.y + zone.height) {
        return zone;
      }
    }
    return null;
  };

  Chart.prototype.openIndicatorSettingsPopup = function (legendHit, pointer) {
    var indicator = this.document.indicators.filter(function (item) {
      return item.id === legendHit.indicatorId;
    })[0];
    if (!indicator) return;
    var definition = Indicators[indicator.type] || {};
    var output = legendHit.output || Object.keys(indicator.styles || { value: {} })[0];
    var style = indicator.styles && indicator.styles[output] || {};
    var length = indicator.inputs && indicator.inputs.length != null ? indicator.inputs.length : '';
    var opacity = style.opacity == null ? 1 : style.opacity;
    this.settingsPopup.innerHTML = [
      '<div class="sce-settings-title">',
      '<strong>', escapeHtml(definition.name || indicator.type), '</strong>',
      '<button type="button" data-sce-popup-action="close" aria-label="Close">x</button>',
      '</div>',
      '<label>Period<input type="number" min="1" max="1000" data-sce-popup-field="length" value="', escapeHtml(length), '"></label>',
      '<label>Output<input type="text" readonly data-sce-popup-field="output" value="', escapeHtml(output), '"></label>',
      '<label>Color<div class="sce-color-control">',
      '<button type="button" class="sce-color-swatch" data-sce-popup-action="toggle-color" style="background:', escapeHtml(style.color || '#2563eb'), '" aria-label="Choose color"></button>',
      '<input type="text" data-sce-popup-field="color" value="', escapeHtml(style.color || '#2563eb'), '">',
      '<div class="sce-color-palette" hidden>',
      colorPaletteHtml(style.color || '#2563eb'),
      '</div>',
      '</div></label>',
      '<label>Thickness<input type="number" min="1" max="8" data-sce-popup-field="lineWidth" value="', escapeHtml(style.lineWidth || 2), '"></label>',
      '<label>Opacity<input type="number" min="0.05" max="1" step="0.05" data-sce-popup-field="opacity" value="', escapeHtml(opacity), '"></label>',
      '<label>Style<select data-sce-popup-field="lineStyle">',
      '<option value="solid"', normalizeLineStyle(style.lineStyle) === 'solid' ? ' selected' : '', '>Solid</option>',
      '<option value="dash"', normalizeLineStyle(style.lineStyle) === 'dash' ? ' selected' : '', '>Dash</option>',
      '<option value="dot"', normalizeLineStyle(style.lineStyle) === 'dot' ? ' selected' : '', '>Dot</option>',
      '</select></label>',
      '<div class="sce-settings-actions">',
      '<button type="button" data-sce-popup-action="remove">Remove</button>',
      '<button type="button" data-sce-popup-action="apply">Apply</button>',
      '</div>'
    ].join('');
    this.settingsPopup.removeAttribute('hidden');
    this.settingsPopup.dataset.mode = 'indicator';
    this.settingsPopup.dataset.indicatorId = indicator.id;
    this.settingsPopup.dataset.output = output;
    delete this.settingsPopup.dataset.drawingId;
    this.bindSettingsPopup();
    this.positionSettingsPopup(pointer, 286, 324);
  };

  Chart.prototype.bindSettingsPopup = function () {
    var self = this;
    this.settingsPopup.onclick = function (event) {
      var action = event.target && event.target.getAttribute('data-sce-popup-action');
      if (!action) return;
      if (action === 'toggle-color') self.togglePopupColorPalette();
      if (action === 'pick-color') self.pickPopupColor(event.target.getAttribute('data-sce-color'));
      if (action === 'close') self.closeIndicatorSettingsPopup();
      if (action === 'remove') {
        self.removeIndicator(self.settingsPopup.dataset.indicatorId);
        self.closeIndicatorSettingsPopup();
      }
      if (action === 'apply') self.applyIndicatorSettingsPopup();
    };
    this.settingsPopup.onfocusin = function (event) {
      if (!closest(event.target, 'sce-color-control')) self.closePopupColorPalette();
    };
    this.settingsPopup.onkeydown = function (event) {
      if (event.key === 'Escape') {
        event.preventDefault();
        self.closeIndicatorSettingsPopup();
      }
    };
  };

  Chart.prototype.closeIndicatorSettingsPopup = function () {
    this.settingsPopup.setAttribute('hidden', 'hidden');
  };

  Chart.prototype.applyIndicatorSettingsPopup = function () {
    var indicatorId = this.settingsPopup.dataset.indicatorId;
    var output = this.settingsPopup.dataset.output || 'value';
    var length = this.settingsPopup.querySelector('[data-sce-popup-field="length"]');
    var color = this.settingsPopup.querySelector('[data-sce-popup-field="color"]');
    var lineWidth = this.settingsPopup.querySelector('[data-sce-popup-field="lineWidth"]');
    var lineStyle = this.settingsPopup.querySelector('[data-sce-popup-field="lineStyle"]');
    var opacity = this.settingsPopup.querySelector('[data-sce-popup-field="opacity"]');
    var style = {};
    style[output] = {
      color: color ? color.value : '#2563eb',
      lineWidth: lineWidth ? Number(lineWidth.value) : 2,
      lineStyle: lineStyle ? lineStyle.value : 'solid',
      opacity: opacity ? Number(opacity.value) : 1
    };
    this.updateIndicatorSettings(indicatorId, {
      inputs: length && length.value ? { length: Number(length.value) } : {},
      styles: style
    });
    this.closeIndicatorSettingsPopup();
  };

  Chart.prototype.togglePopupColorPalette = function () {
    var palette = this.settingsPopup.querySelector('.sce-color-palette');
    if (!palette) return;
    if (palette.hasAttribute('hidden')) palette.removeAttribute('hidden');
    else palette.setAttribute('hidden', 'hidden');
  };

  Chart.prototype.closePopupColorPalette = function () {
    var palette = this.settingsPopup.querySelector('.sce-color-palette');
    if (palette) palette.setAttribute('hidden', 'hidden');
  };

  Chart.prototype.pickPopupColor = function (color) {
    if (!color) return;
    var field = this.settingsPopup.querySelector('[data-sce-popup-field="color"]') ||
      this.settingsPopup.querySelector('[data-sce-popup-field="drawingColor"]');
    var swatch = this.settingsPopup.querySelector('.sce-color-swatch');
    if (field) field.value = color;
    if (swatch) swatch.style.background = color;
    this.closePopupColorPalette();
  };

  Chart.prototype.openDrawingSettingsPopup = function (drawing, pointer) {
    if (!drawing) return false;
    var tool = drawingToolDefinition(drawing.type);
    var canEditText = isEditableTextDrawing(drawing);
    var style = sanitizeDrawingStyle(drawing.style || {});
    var color = style.color || this.theme().drawing;
    var width = style.width || 2;
    var lineStyle = normalizeLineStyle(style.lineStyle);
    var opacity = style.opacity == null ? 1 : style.opacity;
    pointer = pointer || this.drawingTextPopupPoint(drawing);
    var textControls = canEditText
      ? ['<label>Text<textarea rows="4" data-sce-popup-field="drawingText">', escapeHtml(drawing.text || ''), '</textarea></label>'].join('')
      : '';
    this.settingsPopup.innerHTML = [
      '<div class="sce-settings-title">',
      '<strong>', escapeHtml(tool.name || 'Drawing'), '</strong>',
      '<button type="button" data-sce-popup-action="close" aria-label="Close">x</button>',
      '</div>',
      textControls,
      '<label>Color<div class="sce-color-control">',
      '<button type="button" class="sce-color-swatch" data-sce-popup-action="toggle-color" style="background:', escapeHtml(color), '" aria-label="Choose color"></button>',
      '<input type="text" data-sce-popup-field="drawingColor" value="', escapeHtml(color), '">',
      '<div class="sce-color-palette" hidden>',
      colorPaletteHtml(color),
      '</div>',
      '</div></label>',
      '<label>Thickness<input type="number" min="1" max="16" data-sce-popup-field="drawingWidth" value="', escapeHtml(width), '"></label>',
      '<label>Opacity<input type="number" min="0.05" max="1" step="0.05" data-sce-popup-field="drawingOpacity" value="', escapeHtml(opacity), '"></label>',
      '<label>Style<select data-sce-popup-field="drawingLineStyle">',
      '<option value="solid"', lineStyle === 'solid' ? ' selected' : '', '>Solid</option>',
      '<option value="dash"', lineStyle === 'dash' ? ' selected' : '', '>Dash</option>',
      '<option value="dot"', lineStyle === 'dot' ? ' selected' : '', '>Dot</option>',
      '</select></label>',
      '<div class="sce-settings-actions">',
      '<button type="button" data-sce-popup-action="send-back">Send back</button>',
      '<button type="button" data-sce-popup-action="bring-front">Bring front</button>',
      '</div>',
      '<div class="sce-settings-actions">',
      '<button type="button" data-sce-popup-action="remove">Remove</button>',
      '<button type="button" data-sce-popup-action="apply">Apply</button>',
      '</div>'
    ].join('');
    this.settingsPopup.removeAttribute('hidden');
    this.settingsPopup.dataset.mode = canEditText ? 'drawing-text' : 'drawing-style';
    this.settingsPopup.dataset.drawingId = drawing.id;
    delete this.settingsPopup.dataset.indicatorId;
    delete this.settingsPopup.dataset.output;
    this.bindDrawingSettingsPopup();
    this.positionSettingsPopup(pointer, 306, canEditText ? 376 : 304);
    var field = this.settingsPopup.querySelector('[data-sce-popup-field="drawingText"]');
    if (field && field.focus) {
      field.focus();
      if (field.select) field.select();
    }
    return true;
  };

  Chart.prototype.positionSettingsPopup = function (pointer, fallbackWidth, fallbackHeight) {
    pointer = pointer || { x: 20, y: 20 };
    var margin = 8;
    var gap = 12;
    var width = this.settingsPopup.offsetWidth || fallbackWidth || 286;
    var height = this.settingsPopup.offsetHeight || fallbackHeight || 260;
    var canvasWidth = this.canvas.clientWidth || width + margin * 2;
    var canvasHeight = this.canvas.clientHeight || height + margin * 2;
    var left = pointer.x + gap;
    if (left + width > canvasWidth - margin) left = pointer.x - width - gap;
    left = clamp(left, margin, Math.max(margin, canvasWidth - width - margin));
    var top = pointer.y + gap;
    if (top + height > canvasHeight - margin && pointer.y - height - gap >= margin) {
      top = pointer.y - height - gap;
    }
    top = clamp(top, margin, Math.max(margin, canvasHeight - height - margin));
    this.settingsPopup.style.left = left + 'px';
    this.settingsPopup.style.top = top + 'px';
  };

  Chart.prototype.openDrawingTextPopup = function (drawing, pointer) {
    if (!drawing || !isEditableTextDrawing(drawing)) return false;
    return this.openDrawingSettingsPopup(drawing, pointer);
  };

  Chart.prototype.bindDrawingSettingsPopup = function () {
    var self = this;
    this.settingsPopup.onclick = function (event) {
      var action = event.target && event.target.getAttribute('data-sce-popup-action');
      if (!action) return;
      if (action === 'toggle-color') self.togglePopupColorPalette();
      if (action === 'pick-color') self.pickPopupColor(event.target.getAttribute('data-sce-color'));
      if (action === 'close') self.closeIndicatorSettingsPopup();
      if (action === 'remove') {
        self.removeDrawing(self.settingsPopup.dataset.drawingId);
        self.closeIndicatorSettingsPopup();
      }
      if (action === 'bring-front') self.moveDrawingZOrder(self.settingsPopup.dataset.drawingId, 'front');
      if (action === 'send-back') self.moveDrawingZOrder(self.settingsPopup.dataset.drawingId, 'back');
      if (action === 'apply') self.applyDrawingSettingsPopup();
    };
    this.settingsPopup.onkeydown = function (event) {
      if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault();
        self.applyDrawingSettingsPopup();
      }
      if (event.key === 'Escape') {
        event.preventDefault();
        self.closeIndicatorSettingsPopup();
      }
    };
    this.settingsPopup.onfocusin = function (event) {
      if (!closest(event.target, 'sce-color-control')) self.closePopupColorPalette();
    };
  };

  Chart.prototype.applyDrawingSettingsPopup = function () {
    var drawingId = this.settingsPopup.dataset.drawingId;
    var textField = this.settingsPopup.querySelector('[data-sce-popup-field="drawingText"]');
    var color = this.settingsPopup.querySelector('[data-sce-popup-field="drawingColor"]');
    var width = this.settingsPopup.querySelector('[data-sce-popup-field="drawingWidth"]');
    var lineStyle = this.settingsPopup.querySelector('[data-sce-popup-field="drawingLineStyle"]');
    var opacity = this.settingsPopup.querySelector('[data-sce-popup-field="drawingOpacity"]');
    if (textField) this.updateDrawingText(drawingId, textField.value);
    this.updateDrawingStyle(drawingId, {
      color: color ? color.value : null,
      width: width ? Number(width.value) : 2,
      lineStyle: lineStyle ? lineStyle.value : 'solid',
      opacity: opacity ? Number(opacity.value) : 1
    });
    this.closeIndicatorSettingsPopup();
  };

  Chart.prototype.drawingTextPopupPoint = function (drawing) {
    var points = this.drawingScreenPoints(drawing).filter(function (point) { return point.y != null; });
    return points[0] || { x: 20, y: 20 };
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
    var baseStyle = merge({ color: null, width: 2, fill: 'rgba(37, 99, 235, 0.12)', font: '12px sans-serif' }, options.style || {});
    if (baseStyle.color && !(options.style && options.style.fill)) {
      baseStyle.fill = colorWithAlpha(baseStyle.color, 0.14) || baseStyle.fill;
    }
    var drawing = {
      id: options.id || uid('drawing'),
      type: type,
      paneId: options.paneId || (ownerStudy ? ownerStudy.paneId : 'price'),
      ownerStudyId: options.ownerStudyId || null,
      points: clone(points || []),
      text: options.text || '',
      style: sanitizeDrawingStyle(baseStyle),
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

  Chart.prototype.updateDrawingText = function (drawingId, text) {
    var drawing = this.getDrawingById(drawingId);
    if (!drawing || !isEditableTextDrawing(drawing)) return false;
    drawing.text = String(text == null ? '' : text);
    this.draw();
    this.emitChange('drawing:text', drawing);
    return true;
  };

  Chart.prototype.updateDrawingStyle = function (drawingId, patch) {
    var drawing = this.getDrawingById(drawingId);
    if (!drawing) return false;
    var stylePatch = merge({}, patch || {});
    if (stylePatch.color && stylePatch.fill == null) {
      stylePatch.fill = colorWithAlpha(stylePatch.color, 0.14) || (drawing.style && drawing.style.fill);
    }
    drawing.style = sanitizeDrawingStyle(merge({}, drawing.style || {}, stylePatch));
    this.draw();
    this.emitChange('drawing:style', drawing);
    return true;
  };

  Chart.prototype.setDrawingMagnetMode = function (enabled) {
    this.document.settings.magnetMode = !!enabled;
    this.emitChange('drawing:magnet', { enabled: this.document.settings.magnetMode });
    return this.document.settings.magnetMode;
  };

  Chart.prototype.toggleDrawingMagnetMode = function () {
    return this.setDrawingMagnetMode(!this.document.settings.magnetMode);
  };

  Chart.prototype.setStayInDrawingMode = function (enabled) {
    this.document.settings.stayInDrawingMode = !!enabled;
    this.emitChange('drawing:stayMode', { enabled: this.document.settings.stayInDrawingMode });
    return this.document.settings.stayInDrawingMode;
  };

  Chart.prototype.toggleStayInDrawingMode = function () {
    return this.setStayInDrawingMode(!this.document.settings.stayInDrawingMode);
  };

  Chart.prototype.setAllDrawingsLocked = function (locked) {
    this.document.drawings.forEach(function (drawing) {
      drawing.locked = !!locked;
    });
    this.draw();
    this.emitChange('drawing:lockAll', { locked: !!locked });
    return !!locked;
  };

  Chart.prototype.areAllDrawingsLocked = function () {
    return !!this.document.drawings.length && this.document.drawings.every(function (drawing) {
      return !!drawing.locked;
    });
  };

  Chart.prototype.setAllDrawingsVisible = function (visible) {
    this.document.drawings.forEach(function (drawing) {
      drawing.visible = !!visible;
    });
    if (!visible) {
      this.selectedDrawingId = null;
      this.hoverDrawingId = null;
    }
    this.draw();
    this.emitChange('drawing:visibilityAll', { visible: !!visible });
    return !!visible;
  };

  Chart.prototype.areAnyDrawingsVisible = function () {
    return this.document.drawings.some(function (drawing) {
      return drawing.visible !== false;
    });
  };

  Chart.prototype.moveDrawingZOrder = function (drawingId, direction) {
    var drawing = this.getDrawingById(drawingId);
    if (!drawing) return false;
    var maxZ = this.document.drawings.reduce(function (max, item) {
      return Math.max(max, item.zIndex || 0);
    }, 0);
    if (direction === 'front' || direction === 'up') drawing.zIndex = maxZ + 1;
    else if (direction === 'back' || direction === 'down') drawing.zIndex = 0;
    else return false;
    this.normalizeDrawingZOrder();
    this.draw();
    this.emitChange('drawing:zorder', { drawingId: drawingId, direction: direction });
    return true;
  };

  Chart.prototype.normalizeDrawingZOrder = function () {
    this.document.drawings.slice().sort(function (a, b) {
      return (a.zIndex || 0) - (b.zIndex || 0);
    }).forEach(function (drawing, index) {
      drawing.zIndex = index + 1;
    });
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
        if (patch && patch.style) drawing.style = sanitizeDrawingStyle(drawing.style || {});
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
      },
      setText: function (text) {
        return self.updateDrawingText(drawing.id, text);
      },
      setStyle: function (style) {
        return self.updateDrawingStyle(drawing.id, style);
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

  Chart.prototype.startDrawing = function (type, options) {
    options = options || {};
    type = normalizeDrawingType(type);
    var tool = drawingToolDefinition(type);
    var pendingStyle = merge({ color: null, width: 2, fill: 'rgba(37, 99, 235, 0.12)' }, options.style || {});
    if (pendingStyle.color && !(options.style && options.style.fill)) {
      pendingStyle.fill = colorWithAlpha(pendingStyle.color, 0.14) || pendingStyle.fill;
    }
    this.pendingDrawing = {
      type: type,
      points: [],
      paneId: options.paneId || null,
      requiredPoints: drawingRequiredPointCount(type),
      text: options.text || tool.name,
      style: sanitizeDrawingStyle(pendingStyle)
    };
    this.selectedDrawingId = null;
    this.canvas.classList.add('sce-crosshair-drawing');
    this.canvas.style.cursor = 'crosshair';
    this.draw();
    this.emitChange('drawing:start', { type: type, requiredPoints: this.pendingDrawing.requiredPoints });
    return this.pendingDrawing;
  };

  Chart.prototype.cancelDrawing = function () {
    if (!this.pendingDrawing) return false;
    this.pendingDrawing = null;
    this.canvas.classList.remove('sce-crosshair-drawing');
    this.draw();
    this.emitChange('drawing:cancel', {});
    return true;
  };

  Chart.prototype.handleCanvasClick = function (event) {
    var pointer = this.pointerFromEvent(event);
    if (!this.pendingDrawing) {
      var paneControlHit = this.hitTestPaneControl(pointer);
      if (paneControlHit) {
        this.handlePaneControl(paneControlHit);
        return;
      }
      var scaleHit = this.hitTestScaleMode(pointer);
      if (scaleHit) {
        this.togglePaneScaleMode(scaleHit.paneId);
        return;
      }
      var legendHit = this.hitTestLegend(pointer);
      if (legendHit) {
        this.openIndicatorSettingsPopup(legendHit, pointer);
        return;
      }
      return;
    }
    var point = this.valueFromEvent(event);
    if (!point) return;
    if (this.pendingDrawing.paneId && point.paneId !== this.pendingDrawing.paneId) return;
    if (!this.pendingDrawing.paneId) this.pendingDrawing.paneId = point.paneId;
    this.pendingDrawing.points.push({ time: point.time, value: point.value });
    if (this.pendingDrawing.points.length >= this.pendingDrawing.requiredPoints) {
      var pending = this.pendingDrawing;
      var drawingId = this.addDrawing(pending.type, pending.points, {
        paneId: pending.paneId,
        text: pending.text,
        style: pending.style
      });
      this.selectedDrawingId = drawingId;
      this.pendingDrawing = null;
      this.canvas.classList.remove('sce-crosshair-drawing');
      this.draw();
      var createdDrawing = this.getDrawingById(drawingId);
      if (this.document.settings.stayInDrawingMode) {
        this.startDrawing(pending.type, {
          paneId: pending.paneId,
          text: pending.text,
          style: pending.style
        });
      } else if (createdDrawing) {
        this.openDrawingSettingsPopup(createdDrawing, pointer);
      }
    } else {
      this.draw();
    }
  };

  Chart.prototype.handleCanvasDoubleClick = function (event) {
    if (this.pendingDrawing) return;
    var pointer = this.pointerFromEvent(event);
    var hit = this.hitTestDrawing(pointer);
    if (!hit) return;
    if (event.preventDefault) event.preventDefault();
    this.selectedDrawingId = hit.drawing.id;
    this.hoverDrawingId = hit.drawing.id;
    this.openDrawingSettingsPopup(hit.drawing, pointer);
    this.draw();
  };

  Chart.prototype.handlePointerDown = function (event) {
    this.canvas.focus();
    if (this.pendingDrawing) return;
    this.pointer = this.pointerFromEvent(event);
    this.tapState = {
      startPointer: this.pointer,
      pointer: this.pointer,
      moved: false,
      openSettingsOnTap: false
    };
    if (this.hitTestPaneControl(this.pointer)) return;
    var paneResizeHit = this.hitTestPaneResize(this.pointer);
    if (paneResizeHit) {
      this.tapState = null;
      this.beginPaneResize(paneResizeHit, this.pointer);
      return;
    }
    var deleteHit = this.hitTestDrawingDelete(this.pointer);
    if (deleteHit) {
      this.tapState = null;
      this.removeDrawing(deleteHit.id);
      return;
    }

    var hit = this.hitTestDrawing(this.pointer);
    if (!hit) {
      this.tapState = null;
      this.selectedDrawingId = null;
      this.hoverDrawingId = null;
      var paneHit = this.valueFromPoint(this.pointer);
      if (paneHit) {
        this.panState = {
          startPointer: this.pointer,
          startRange: this.visibleIndexRange(),
          startIndex: this.barIndexAtPoint(this.pointer),
          moved: false
        };
        this.canvas.style.cursor = 'grabbing';
      }
      this.draw();
      return;
    }

    var wasSelected = this.selectedDrawingId === hit.drawing.id;
    this.selectedDrawingId = hit.drawing.id;
    this.hoverDrawingId = hit.drawing.id;
    this.tapState.openSettingsOnTap = wasSelected && hit.pointIndex == null;
    if (!hit.drawing.locked) {
      this.dragState = {
        drawingId: hit.drawing.id,
        pointIndex: hit.pointIndex,
        paneId: hit.drawing.paneId,
        startPointer: this.pointer,
        startValue: this.valueFromPoint(this.pointer, hit.drawing.paneId),
        originalPoints: clone(hit.drawing.points || []),
        moved: false,
        openSettingsOnTap: this.tapState.openSettingsOnTap
      };
    }
    this.draw();
  };

  Chart.prototype.handlePointerMove = function (event) {
    this.pointer = this.pointerFromEvent(event);
    if (this.tapState && distance(this.pointer, this.tapState.startPointer) > 5) this.tapState.moved = true;
    if (this.pendingDrawing) {
      this.hoverDrawingId = null;
      this.canvas.style.cursor = 'crosshair';
      this.draw();
      return;
    }
    if (this.paneResizeState) {
      this.resizePaneBoundary(this.pointer);
      this.draw();
      return;
    }
    if (this.panState) {
      this.panVisibleRange(this.pointer);
      this.draw();
      return;
    }
    if (this.dragState) {
      if (distance(this.pointer, this.dragState.startPointer) > 3) this.dragState.moved = true;
      this.moveSelectedDrawing(this.pointer);
      this.draw();
      return;
    }
    var hit = this.hitTestDrawing(this.pointer);
    this.hoverDrawingId = hit ? hit.drawing.id : null;
    if (this.hitTestPaneResize(this.pointer)) this.canvas.style.cursor = 'ns-resize';
    else if (this.hitTestPaneControl(this.pointer)) this.canvas.style.cursor = 'pointer';
    else if (this.hitTestLegend(this.pointer)) this.canvas.style.cursor = 'pointer';
    else this.canvas.style.cursor = hit ? 'move' : 'crosshair';
    this.draw();
  };

  Chart.prototype.handlePointerUp = function (event) {
    var pointer = event ? this.pointerFromEvent(event) : this.pointer;
    if (this.paneResizeState) {
      var resized = {
        upperPaneId: this.paneResizeState.upperPaneId,
        lowerPaneId: this.paneResizeState.lowerPaneId
      };
      this.paneResizeState = null;
      this.tapState = null;
      this.canvas.style.cursor = 'crosshair';
      this.emitChange('pane:resize', resized);
      return;
    }
    if (this.panState) {
      var panned = this.panState.moved;
      this.panState = null;
      this.tapState = null;
      this.canvas.style.cursor = 'crosshair';
      if (panned) this.emitChange('range:pan', { visibleRange: clone(this.document.visibleRange) });
      return;
    }
    if (!this.dragState) {
      this.tapState = null;
      return;
    }
    var state = this.dragState;
    var drawingId = state.drawingId;
    var shouldOpenSettings = state.openSettingsOnTap && !state.moved && !(this.tapState && this.tapState.moved);
    this.dragState = null;
    this.tapState = null;
    this.canvas.style.cursor = 'crosshair';
    if (state.moved) this.emitChange('drawing:move', { drawingId: drawingId });
    if (shouldOpenSettings) {
      var drawing = this.getDrawingById(drawingId);
      if (drawing) this.openDrawingSettingsPopup(drawing, pointer || state.startPointer);
    }
  };

  Chart.prototype.handleTouchStart = function (event) {
    if (event.preventDefault) event.preventDefault();
    this.handlePointerDown(event);
  };

  Chart.prototype.handleTouchMove = function (event) {
    if (event.preventDefault) event.preventDefault();
    this.handlePointerMove(event);
  };

  Chart.prototype.handleTouchEnd = function (event) {
    if (event.preventDefault) event.preventDefault();
    var wasPending = !!this.pendingDrawing;
    var moved = this.tapState && this.tapState.moved;
    if (wasPending && !moved) this.handleCanvasClick(event);
    this.handlePointerUp(event);
  };

  Chart.prototype.handleTouchCancel = function (event) {
    if (event && event.preventDefault) event.preventDefault();
    this.tapState = null;
    this.dragState = null;
    this.paneResizeState = null;
    this.panState = null;
    this.canvas.style.cursor = 'crosshair';
    this.draw();
  };

  Chart.prototype.handleWheel = function (event) {
    if (!this.bars.length || this.pendingDrawing) return;
    if (event.preventDefault) event.preventDefault();
    this.pointer = this.pointerFromEvent(event);
    var rect = this.getPaneRect(this.activePaneId()) || this.paneRects[0];
    var anchorRatio = rect ? clamp((this.pointer.x - rect.x) / Math.max(1, rect.width), 0, 1) : 0.5;
    var deltaX = toNumber(event.deltaX, 0);
    var deltaY = toNumber(event.deltaY, 0);
    var scrollIntent = event.shiftKey || Math.abs(deltaX) > Math.abs(deltaY);
    if (scrollIntent) {
      var range = this.visibleIndexRange();
      var count = Math.max(1, range.to - range.from + 1);
      var bars = deltaX || deltaY;
      var barDelta = Math.round((bars / 100) * Math.max(1, count / 8));
      if (!barDelta && bars) barDelta = bars > 0 ? 1 : -1;
      this.scroll(barDelta);
      return;
    }
    if (deltaY < 0) this.zoomIn(anchorRatio);
    else if (deltaY > 0) this.zoomOut(anchorRatio);
  };

  Chart.prototype.panVisibleRange = function (pointer) {
    var state = this.panState;
    if (!state || !this.bars.length) return;
    var rect = this.getPaneRect(this.activePaneId()) || this.paneRects[0] || { width: this.canvas.clientWidth || 1 };
    var rangeCount = Math.max(1, state.startRange.to - state.startRange.from + 1);
    var pixelDelta = pointer.x - state.startPointer.x;
    var barDelta = Math.round(-(pixelDelta / Math.max(1, rect.width)) * rangeCount);
    if (Math.abs(pixelDelta) > 2) state.moved = true;
    if (state.moved) this.document.settings.dateRangePreset = null;
    this.setVisibleIndexRange(state.startRange.from + barDelta, state.startRange.to + barDelta);
  };

  Chart.prototype.hitTestPaneControl = function (pointer) {
    for (var i = 0; i < this.paneControlHitZones.length; i += 1) {
      var zone = this.paneControlHitZones[i];
      if (zone.disabled) continue;
      if (pointer.x >= zone.x && pointer.x <= zone.x + zone.width && pointer.y >= zone.y && pointer.y <= zone.y + zone.height) {
        return zone;
      }
    }
    return null;
  };

  Chart.prototype.handlePaneControl = function (zone) {
    if (!zone || zone.disabled) return false;
    if (zone.action === 'move-up') return this.movePaneUp(zone.paneId);
    if (zone.action === 'move-down') return this.movePaneDown(zone.paneId);
    if (zone.action === 'maximize') return this.togglePaneMaximize(zone.paneId);
    if (zone.action === 'close') return this.removePane(zone.paneId);
    return false;
  };

  Chart.prototype.hitTestPaneResize = function (pointer) {
    if (this.document.settings.maximizedPaneId) return null;
    for (var i = 0; i < this.paneResizeHitZones.length; i += 1) {
      var zone = this.paneResizeHitZones[i];
      if (pointer.x >= zone.x && pointer.x <= zone.x + zone.width && pointer.y >= zone.y && pointer.y <= zone.y + zone.height) {
        return zone;
      }
    }
    return null;
  };

  Chart.prototype.beginPaneResize = function (zone, pointer) {
    var upperPane = this.document.panes.filter(function (pane) { return pane.id === zone.upperPaneId; })[0];
    var lowerPane = this.document.panes.filter(function (pane) { return pane.id === zone.lowerPaneId; })[0];
    if (!upperPane || !lowerPane) return false;
    var totalDocHeight = (upperPane.height || 180) + (lowerPane.height || 180);
    var totalScreenHeight = Math.max(1, zone.upperHeight + zone.lowerHeight);
    this.paneResizeState = {
      upperPaneId: upperPane.id,
      lowerPaneId: lowerPane.id,
      startY: pointer.y,
      upperHeight: upperPane.height || 180,
      lowerHeight: lowerPane.height || 180,
      docPerPixel: totalDocHeight / totalScreenHeight
    };
    this.canvas.style.cursor = 'ns-resize';
    return true;
  };

  Chart.prototype.resizePaneBoundary = function (pointer) {
    var state = this.paneResizeState;
    if (!state) return;
    var upperPane = this.document.panes.filter(function (pane) { return pane.id === state.upperPaneId; })[0];
    var lowerPane = this.document.panes.filter(function (pane) { return pane.id === state.lowerPaneId; })[0];
    if (!upperPane || !lowerPane) return;
    var total = state.upperHeight + state.lowerHeight;
    var minHeight = Math.min(80, total / 2);
    var delta = (pointer.y - state.startY) * state.docPerPixel;
    var nextUpper = clamp(state.upperHeight + delta, minHeight, total - minHeight);
    upperPane.height = Math.round(nextUpper);
    lowerPane.height = Math.round(total - nextUpper);
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
    this.document.settings.dateRangePreset = null;
    var count = Math.min(this.bars.length, this.options.initialBarCount || 140);
    this.setVisibleIndexRange(this.bars.length - count, this.bars.length - 1);
  };

  Chart.prototype.visibleIndexRange = function () {
    if (!this.bars.length) return { from: 0, to: -1 };
    var range = this.document.visibleRange;
    if (!range) return { from: 0, to: this.bars.length - 1 };
    var from = 0;
    var to = this.bars.length - 1;
    for (var i = 0; i < this.bars.length; i += 1) {
      if (this.bars[i].time >= range.from) {
        from = i;
        break;
      }
    }
    for (var j = this.bars.length - 1; j >= 0; j -= 1) {
      if (this.bars[j].time <= range.to) {
        to = j;
        break;
      }
    }
    if (to < from) to = from;
    return { from: from, to: to };
  };

  Chart.prototype.setVisibleIndexRange = function (fromIndex, toIndex) {
    if (!this.bars.length) {
      this.document.visibleRange = null;
      return null;
    }
    fromIndex = Math.round(toNumber(fromIndex, 0));
    toIndex = Math.round(toNumber(toIndex, this.bars.length - 1));
    var count = Math.max(1, toIndex - fromIndex + 1);
    count = Math.min(count, this.bars.length);
    fromIndex = clamp(fromIndex, 0, this.bars.length - count);
    toIndex = fromIndex + count - 1;
    this.document.visibleRange = {
      from: this.bars[fromIndex].time,
      to: this.bars[toIndex].time
    };
    return this.document.visibleRange;
  };

  Chart.prototype.barIndexAtPoint = function (point) {
    if (!this.bars.length || !point) return 0;
    var rect = null;
    for (var i = 0; i < this.paneRects.length; i += 1) {
      if (point.y >= this.paneRects[i].y && point.y <= this.paneRects[i].y + this.paneRects[i].height) {
        rect = this.paneRects[i];
        break;
      }
    }
    rect = rect || this.paneRects[0] || { x: 0, width: this.canvas.clientWidth || 1 };
    var range = this.visibleIndexRange();
    var ratio = clamp((point.x - rect.x) / Math.max(1, rect.width), 0, 1);
    return Math.min(range.to, range.from + ratio * this.visibleIndexSpan(range));
  };

  Chart.prototype.rightLeadBars = function () {
    return Math.max(0, toNumber(this.options.rightLeadBars, 3));
  };

  Chart.prototype.visibleIndexSpan = function (range) {
    range = range || this.visibleIndexRange();
    return Math.max(1, range.to - range.from + this.rightLeadBars());
  };

  Chart.prototype.updateToolbar = function () {
    var title = this.toolbar.querySelector('.sce-title');
    if (title) title.textContent = this.document.symbol + ' ' + this.document.interval;
    var chartType = this.toolbar.querySelector('[data-sce-chart-type]');
    if (chartType) chartType.value = this.document.settings.chartType;
    var chartTypeButton = this.toolbar.querySelector('[data-sce-chart-type-button]');
    if (chartTypeButton) {
      chartTypeButton.innerHTML = chartTypeIconSvg(this.document.settings.chartType) + '<span>' + chartTypeLabel(this.document.settings.chartType) + '</span>' + chartTypeChevronSvg();
    }
    if (this.toolbar.querySelectorAll) {
      Array.prototype.forEach.call(this.toolbar.querySelectorAll('[data-sce-chart-type-option]'), function (button) {
        var selected = button.getAttribute('data-sce-chart-type-option') === this.document.settings.chartType;
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-selected', selected ? 'true' : 'false');
      }, this);
    }
    var chartPeriod = this.toolbar.querySelector('[data-sce-chart-period]');
    if (chartPeriod) chartPeriod.value = this.document.settings.period;
    var dateRangeButton = this.toolbar.querySelector('[data-sce-date-range-button]');
    if (dateRangeButton) {
      dateRangeButton.innerHTML = '<span>Range</span><strong>' + dateRangeLabel(this.document.settings.dateRangePreset) + '</strong>' + chartTypeChevronSvg();
    }
    if (this.toolbar.querySelectorAll) {
      Array.prototype.forEach.call(this.toolbar.querySelectorAll('[data-sce-date-range]'), function (button) {
        var selected = button.getAttribute('data-sce-date-range') === this.document.settings.dateRangePreset;
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
      }, this);
    }
    var themeButton = this.toolbar.querySelector('[data-sce-action="theme"]');
    if (themeButton) themeButton.textContent = this.document.theme === 'dark' ? 'Light' : 'Dark';
    var logButton = this.toolbar.querySelector('[data-sce-action="log"]');
    if (logButton) logButton.textContent = 'Log: ' + this.paneScaleMode(this.activePaneId()).toUpperCase();
    this.updateDrawingUtilityButtons();
  };

  Chart.prototype.updateDrawingUtilityButtons = function () {
    if (!this.drawingToolsLayer || !this.drawingToolsLayer.querySelectorAll) return;
    var self = this;
    function update(action, active, title, icon) {
      var button = self.drawingToolsLayer.querySelector('[data-sce-action="' + action + '"]');
      if (!button) return;
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
      button.classList.toggle('is-active', !!active);
      if (title) {
        button.setAttribute('title', title);
        button.setAttribute('aria-label', title);
      }
      if (icon) button.innerHTML = paneControlIconSvg(icon);
    }
    update('toggle-magnet', this.document.settings.magnetMode, 'Magnet mode', 'magnet');
    update('toggle-stay-drawing', this.document.settings.stayInDrawingMode, 'Stay in drawing mode', 'repeat');
    var allLocked = this.areAllDrawingsLocked();
    var anyVisible = this.areAnyDrawingsVisible();
    var hasDrawings = this.document.drawings.length > 0;
    update('toggle-lock-drawings', allLocked, allLocked ? 'Unlock drawings' : 'Lock drawings', allLocked ? 'unlock' : 'lock');
    update('toggle-drawings-visible', hasDrawings && !anyVisible, hasDrawings && !anyVisible ? 'Show drawings' : 'Hide drawings', hasDrawings && !anyVisible ? 'eye' : 'eye-off');
  };

  Chart.prototype.resize = function () {
    var rect = this.canvasWrap.getBoundingClientRect();
    var dpr = window.devicePixelRatio || 1;
    var measuredWidth = rect.width || this.options.width || 900;
    var width = Math.max(1, Math.floor(measuredWidth));
    var height = Math.max(280, Math.floor(rect.height || this.options.height || 560));
    this.canvas.width = Math.floor(width * dpr);
    this.canvas.height = Math.floor(height * dpr);
    this.canvas.style.width = width + 'px';
    this.canvas.style.height = height + 'px';
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    if (!this.document.visibleRange) this.fitContent();
    this.draw();
    this.positionDrawingToolMenus();
  };

  Chart.prototype.positionDrawingToolMenus = function () {
    if (!this.drawingToolsLayer || !this.drawingToolsLayer.querySelectorAll) return;
    var groups = Array.prototype.slice.call(this.drawingToolsLayer.querySelectorAll('.sce-drawing-tool-group[open]'));
    if (!groups.length) return;
    var viewportWidth = window.innerWidth || document.documentElement.clientWidth || this.canvas.clientWidth || 900;
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight || this.canvas.clientHeight || 560;
    groups.forEach(function (group) {
      var summary = group.querySelector && group.querySelector('summary');
      var menu = group.querySelector && group.querySelector('.sce-drawing-tool-menu');
      if (!summary || !menu || !summary.getBoundingClientRect) return;
      var rect = summary.getBoundingClientRect();
      var menuRect = menu.getBoundingClientRect ? menu.getBoundingClientRect() : { width: 0 };
      var fallbackWidth = viewportWidth < 880 ? Math.min(280, Math.max(220, viewportWidth - 58)) : 360;
      var menuWidth = Math.min(menuRect.width || fallbackWidth, Math.max(220, viewportWidth - 16));
      var left = rect.right + 8;
      if (left + menuWidth > viewportWidth - 8) left = Math.max(8, viewportWidth - menuWidth - 8);
      var top = Math.max(8, Math.min(rect.top, viewportHeight - 180));
      var maxHeight = Math.max(160, viewportHeight - top - 12);
      menu.style.setProperty('--sce-drawing-menu-left', Math.round(left) + 'px');
      menu.style.setProperty('--sce-drawing-menu-top', Math.round(top) + 'px');
      menu.style.setProperty('--sce-drawing-menu-max-height', Math.round(maxHeight) + 'px');
    });
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
    var drawingRailWidth = this.drawingRailWidth();
    var plotWidth = Math.max(120, width - scaleWidth - drawingRailWidth);
    var timeAxisHeight = 28;
    var layoutPanes = this.document.panes;
    var maximizedPaneId = this.document.settings.maximizedPaneId;
    if (maximizedPaneId) {
      layoutPanes = this.document.panes.filter(function (pane) { return pane.id === maximizedPaneId; });
      if (!layoutPanes.length) {
        this.document.settings.maximizedPaneId = null;
        layoutPanes = this.document.panes;
      }
    }
    var totalRequested = layoutPanes.reduce(function (sum, pane) { return sum + (pane.height || 180); }, 0);
    var available = Math.max(100, height - timeAxisHeight);
    var minPaneHeight = layoutPanes.length > 1 ? Math.max(42, Math.min(86, Math.floor(available / layoutPanes.length))) : available;
    var self = this;
    this.paneRects = layoutPanes.map(function (pane, index) {
      var paneHeight = Math.max(minPaneHeight, Math.round(available * ((pane.height || 180) / totalRequested)));
      if (index === layoutPanes.length - 1) paneHeight = available - top;
      var rect = {
        paneId: pane.id,
        title: pane.title,
        paneIndex: self.document.panes.indexOf(pane),
        x: drawingRailWidth,
        y: top,
        width: plotWidth,
        height: paneHeight,
        scaleX: drawingRailWidth + plotWidth,
        scaleWidth: scaleWidth
      };
      top += paneHeight;
      return rect;
    });
    this.timeAxisRect = { x: drawingRailWidth, y: available, width: plotWidth, height: timeAxisHeight };
    return this.paneRects;
  };

  Chart.prototype.drawingRailWidth = function () {
    if (this.options.drawingTools === false) return 0;
    var width = this.canvas && this.canvas.clientWidth || 0;
    return width && width < 520 ? 46 : 56;
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
      if (paneId === 'price' && indicator.type === 'VOLUME') return;
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
    var paddedMin = min - pad;
    var paddedMax = max + pad;
    if (this.paneScaleMode(paneId) === 'log' && min > 0 && paddedMin <= 0) {
      paddedMin = Math.max(min * 0.92, 0.0000001);
    }
    return { min: paddedMin, max: paddedMax };
  };

  Chart.prototype.xForTime = function (time, rect) {
    if (!this.bars.length) return rect.x;
    var range = this.visibleIndexRange();
    var from = range.from;
    var to = range.to;
    if (to < from) return rect.x;
    if (from === to) return rect.x + rect.width / 2;
    var index = this.indexForTime(time);
    return rect.x + ((index - from) / this.visibleIndexSpan(range)) * rect.width;
  };

  Chart.prototype.yForValue = function (value, rect, range) {
    if (this.paneScaleMode(rect.paneId) === 'log') {
      var logTransform = logScaleTransform(range);
      var logMin = logTransform.value(range.min);
      var logMax = logTransform.value(range.max);
      var logValue = logTransform.value(value);
      if (logMin == null || logMax == null || logValue == null) return null;
      return rect.y + ((logMax - logValue) / (logMax - logMin || 1)) * rect.height;
    }
    return rect.y + ((range.max - value) / (range.max - range.min)) * rect.height;
  };

  Chart.prototype.timeForX = function (x, rect) {
    if (!this.bars.length) return null;
    var range = this.visibleIndexRange();
    if (range.to < range.from) return null;
    if (range.from === range.to) return this.bars[range.from].time;
    var ratio = clamp((x - rect.x) / Math.max(1, rect.width), 0, 1);
    return this.timeForIndex(Math.min(range.to, range.from + ratio * this.visibleIndexSpan(range)));
  };

  Chart.prototype.indexForTime = function (time) {
    if (!this.bars.length) return 0;
    time = toNumber(time, this.bars[0].time);
    if (time <= this.bars[0].time) return 0;
    var lastIndex = this.bars.length - 1;
    if (time >= this.bars[lastIndex].time) return lastIndex;
    for (var i = 1; i < this.bars.length; i += 1) {
      var left = this.bars[i - 1];
      var right = this.bars[i];
      if (time === right.time) return i;
      if (time < right.time) {
        var span = Math.max(1, right.time - left.time);
        return (i - 1) + ((time - left.time) / span);
      }
    }
    return lastIndex;
  };

  Chart.prototype.timeForIndex = function (index) {
    if (!this.bars.length) return null;
    index = clamp(toNumber(index, 0), 0, this.bars.length - 1);
    var leftIndex = Math.floor(index);
    var rightIndex = Math.ceil(index);
    if (leftIndex === rightIndex) return this.bars[leftIndex].time;
    var left = this.bars[leftIndex];
    var right = this.bars[rightIndex];
    return left.time + (index - leftIndex) * (right.time - left.time);
  };

  Chart.prototype.valueForY = function (y, rect, range) {
    if (this.paneScaleMode(rect.paneId) === 'log') {
      var logTransform = logScaleTransform(range);
      var logMin = logTransform.value(range.min);
      var logMax = logTransform.value(range.max);
      if (logMin == null || logMax == null) return range.min;
      var logValue = logMax - ((y - rect.y) / rect.height) * (logMax - logMin);
      return logTransform.inverse(logValue);
    }
    return range.max - ((y - rect.y) / rect.height) * (range.max - range.min);
  };

  Chart.prototype.pointerFromEvent = function (event) {
    var bounds = this.canvas.getBoundingClientRect();
    var source = event;
    if (event && event.touches && event.touches.length) source = event.touches[0];
    else if (event && event.changedTouches && event.changedTouches.length) source = event.changedTouches[0];
    source = source || { clientX: 0, clientY: 0 };
    return {
      x: source.clientX - bounds.left,
      y: source.clientY - bounds.top,
      pointerType: event && (event.pointerType || (event.touches || event.changedTouches ? 'touch' : 'mouse'))
    };
  };

  Chart.prototype.valueFromEvent = function (event) {
    var point = this.pointerFromEvent(event);
    return this.valueFromPoint(point);
  };

  Chart.prototype.valueFromPoint = function (point, forcedPaneId) {
    for (var i = 0; i < this.paneRects.length; i += 1) {
      var rect = this.paneRects[i];
      var inPane = point.x >= rect.x && point.x <= rect.x + rect.width && point.y >= rect.y && point.y <= rect.y + rect.height;
      if (forcedPaneId ? rect.paneId === forcedPaneId : inPane) {
        var range = this.paneRange(rect.paneId);
        var valuePoint = {
          paneId: rect.paneId,
          time: Math.round(this.timeForX(point.x, rect)),
          value: this.valueForY(point.y, rect, range)
        };
        return this.snapValuePoint(valuePoint);
      }
    }
    return null;
  };

  Chart.prototype.snapValuePoint = function (point) {
    if (!point || !this.document.settings.magnetMode || point.paneId !== 'price' || !this.bars.length) return point;
    var bar = this.barNearTime(point.time);
    if (!bar) return point;
    var values = [bar.open, bar.high, bar.low, bar.close];
    var nearest = values[0];
    var nearestDistance = Math.abs(nearest - point.value);
    for (var i = 1; i < values.length; i += 1) {
      var currentDistance = Math.abs(values[i] - point.value);
      if (currentDistance < nearestDistance) {
        nearest = values[i];
        nearestDistance = currentDistance;
      }
    }
    return { paneId: point.paneId, time: bar.time, value: nearest };
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
    var tolerance = pointer && pointer.pointerType === 'touch' ? 18 : 8;
    for (var i = points.length - 1; i >= 0; i -= 1) {
      if (points[i].y == null) continue;
      if (distance(pointer, points[i]) <= tolerance) return i;
    }
    return null;
  };

  Chart.prototype.isPointOnDrawing = function (pointer, drawing) {
    var points = this.drawingScreenPoints(drawing);
    var tolerance = pointer && pointer.pointerType === 'touch' ? 18 : 8;
    if (!points.length) return false;
    points = points.filter(function (point) { return point.y != null; });
    if (!points.length) return false;
    var tool = drawingToolDefinition(drawing.type);
    var kind = tool.renderKind;
    var rect = this.getPaneRect(drawing.paneId);
    var bounds = pointBounds(points);

    if ((kind === 'hline' || kind === 'horizontalRay') && points[0]) {
      if (kind === 'horizontalRay' && pointer.x < points[0].x - tolerance) return false;
      return Math.abs(pointer.y - points[0].y) <= tolerance;
    }
    if (kind === 'vline' && points[0]) {
      return Math.abs(pointer.x - points[0].x) <= tolerance;
    }
    if (kind === 'crossline' && points[0]) {
      return Math.abs(pointer.x - points[0].x) <= tolerance || Math.abs(pointer.y - points[0].y) <= tolerance;
    }
    if (kind === 'text' || kind === 'priceLabel' || kind === 'callout' || kind === 'vwapAnchor' || kind.indexOf('marker') === 0 || kind === 'flag') {
      return pointer.x >= points[0].x - tolerance && pointer.x <= points[0].x + 130 && pointer.y >= points[0].y - 26 && pointer.y <= points[0].y + 14;
    }

    if (points.length > 1) {
      for (var i = 0; i < points.length - 1; i += 1) {
        if (distanceToSegment(pointer, points[i], points[i + 1]) <= tolerance) return true;
      }
    }

    if (kind === 'rectangle' || kind === 'gridBox' || kind === 'rotatedRectangle' || kind === 'ellipse' ||
      kind === 'polygon' || kind === 'channel' || kind === 'fibChannel' || kind === 'wedge' ||
      kind.indexOf('position') === 0 || kind === 'dateRange' || kind === 'priceRange' || kind === 'datePriceRange' ||
      kind === 'barsPattern' || kind === 'ghostFeed' || kind === 'volumeProfile') {
      return pointerInBounds(pointer, bounds, tolerance);
    }

    if (kind === 'ray' || kind === 'extendedLine') {
      if (!rect || !points[1]) return false;
      var leftX = kind === 'ray' ? points[0].x : rect.x;
      var rightX = rect.x + rect.width;
      return distanceToSegment(pointer, { x: leftX, y: lineYAtX(points[0], points[1], leftX) }, { x: rightX, y: lineYAtX(points[0], points[1], rightX) }) <= tolerance;
    }

    if (kind === 'fibRetracement' || kind === 'fibExtension' || kind === 'timeZones' || kind === 'cyclicLines' || kind === 'fan') {
      return pointerInBounds(pointer, bounds, Math.max(tolerance, 18));
    }

    return false;
  };

  Chart.prototype.drawingDeleteZone = function (drawing) {
    var points = this.drawingScreenPoints(drawing);
    points = points.filter(function (point) { return point.y != null; });
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
    this.legendHitZones = [];
    this.scaleHitZones = [];
    this.paneControlHitZones = [];
    this.paneResizeHitZones = [];
    for (var i = 0; i < this.paneRects.length; i += 1) {
      this.drawPane(this.paneRects[i], theme, i);
    }
    this.drawPaneResizeHandles(theme);
    this.renderPaneControlOverlays();
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
    if (rect.paneId === 'price') this.drawPriceVolumeOverlays(rect, theme);
    if (rect.paneId === 'price') this.drawPriceSeries(rect, range, theme);
    this.drawIndicators(rect, range, theme);
    this.drawDrawings(rect, range, theme);
    this.drawPendingDrawing(rect, range, theme);
    this.drawScale(rect, range, theme);
    this.drawPaneLegend(rect, range, theme);
    this.drawPaneControls(rect, theme);
    ctx.strokeStyle = theme.border;
    ctx.beginPath();
    ctx.moveTo(rect.x, rect.y + rect.height - 0.5);
    ctx.lineTo(rect.x + rect.width + rect.scaleWidth, rect.y + rect.height - 0.5);
    ctx.stroke();
    ctx.restore();
  };

  Chart.prototype.drawPaneLegend = function (rect, range, theme) {
    var ctx = this.ctx;
    var x = rect.x + 10;
    var y = rect.y + 18;
    var legendTime = this.legendTimeForPane(rect);
    ctx.save();
    ctx.font = '12px sans-serif';
    ctx.textBaseline = 'middle';

    if (rect.paneId === 'price') {
      var bar = this.barNearTime(legendTime);
      if (bar) {
        var priceLabel = formatDate(bar.time) + '  O ' + formatNumber(bar.open) + ' H ' + formatNumber(bar.high) + ' L ' + formatNumber(bar.low) + ' C ' + formatNumber(bar.close) + this.pricePercentChangeLabel(bar);
        ctx.fillStyle = bar.close >= bar.open ? theme.up : theme.down;
        ctx.fillText(priceLabel, x, y);
        x += approximateTextWidth(priceLabel) + 14;
      }
    }

    this.indicatorLegendItems(rect.paneId, legendTime, theme).forEach(function (item) {
      if (x > rect.x + rect.width - 80) return;
      var itemWidth = approximateTextWidth(item.label) + 24;
      this.legendHitZones.push({
        x: x - 3,
        y: y - 11,
        width: itemWidth,
        height: 22,
        indicatorId: item.indicatorId,
        output: item.output
      });
      ctx.fillStyle = item.color;
      ctx.fillRect(x, y - 4, 8, 8);
      ctx.fillText(item.label, x + 12, y);
      x += itemWidth + 4;
    }, this);
    ctx.restore();
  };

  Chart.prototype.drawPaneControls = function (rect, theme) {
    var panes = this.document.panes;
    var index = rect.paneIndex;
    var maximized = this.document.settings.maximizedPaneId === rect.paneId;
    var size = 24;
    var gap = 4;
    var y = rect.y + 4;
    var actions = [
      { action: 'move-up', icon: 'chevron-up', label: 'Move pane up', disabled: index <= 0 || !!this.document.settings.maximizedPaneId },
      { action: 'move-down', icon: 'chevron-down', label: 'Move pane down', disabled: index >= panes.length - 1 || !!this.document.settings.maximizedPaneId },
      { action: 'maximize', icon: maximized ? 'minimize' : 'maximize', label: maximized ? 'Restore pane' : 'Maximize pane', disabled: false },
      { action: 'close', icon: 'close', label: 'Close pane', disabled: rect.paneId === 'price' }
    ];
    var totalWidth = actions.length * size + (actions.length - 1) * gap;
    var x = Math.max(rect.x + 120, rect.x + rect.width - totalWidth - 10);

    actions.forEach(function (item) {
      var zone = {
        x: x,
        y: y,
        width: size,
        height: size,
        paneId: rect.paneId,
        action: item.action,
        icon: item.icon,
        label: item.label,
        disabled: item.disabled
      };
      this.paneControlHitZones.push(zone);
      x += size + gap;
    }, this);
  };

  Chart.prototype.renderPaneControlOverlays = function () {
    if (!this.paneControlsLayer) return;
    this.paneControlsLayer.innerHTML = '';
    this.paneControlHitZones.forEach(function (zone) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'sce-pane-control-button';
      button.setAttribute('data-sce-pane-id', zone.paneId);
      button.setAttribute('data-sce-pane-action', zone.action);
      button.setAttribute('aria-label', zone.label);
      button.setAttribute('title', zone.label);
      button.setAttribute('aria-disabled', zone.disabled ? 'true' : 'false');
      if (zone.disabled) button.setAttribute('disabled', 'disabled');
      button.style.left = zone.x + 'px';
      button.style.top = zone.y + 'px';
      button.style.width = zone.width + 'px';
      button.style.height = zone.height + 'px';
      button.innerHTML = paneControlIconSvg(zone.icon);
      this.paneControlsLayer.appendChild(button);
    }, this);
  };

  Chart.prototype.drawPaneResizeHandles = function (theme) {
    if (this.document.settings.maximizedPaneId || this.paneRects.length < 2) return;
    var ctx = this.ctx;
    ctx.save();
    for (var i = 0; i < this.paneRects.length - 1; i += 1) {
      var upper = this.paneRects[i];
      var lower = this.paneRects[i + 1];
      var y = upper.y + upper.height;
      this.paneResizeHitZones.push({
        x: upper.x,
        y: y - 5,
        width: upper.width + upper.scaleWidth,
        height: 10,
        upperPaneId: upper.paneId,
        lowerPaneId: lower.paneId,
        upperHeight: upper.height,
        lowerHeight: lower.height
      });
      ctx.strokeStyle = theme.border;
      ctx.beginPath();
      ctx.moveTo(upper.x, Math.round(y) + 0.5);
      ctx.lineTo(upper.x + upper.width + upper.scaleWidth, Math.round(y) + 0.5);
      ctx.stroke();
      ctx.fillStyle = theme.mutedText;
      ctx.globalAlpha = 0.55;
      ctx.fillRect(upper.x + upper.width / 2 - 14, y - 1, 28, 2);
    }
    ctx.restore();
  };

  Chart.prototype.legendTimeForPane = function (rect) {
    if (this.pointer && this.pointerPaneRect() && this.pointer.x >= rect.x && this.pointer.x <= rect.x + rect.width) {
      return Math.round(this.timeForX(this.pointer.x, rect));
    }
    var visible = this.visibleBars();
    return visible.length ? visible[visible.length - 1].time : null;
  };

  Chart.prototype.scaleMarkerTimeForPane = function () {
    var visible = this.visibleBars();
    return visible.length ? visible[visible.length - 1].time : null;
  };

  Chart.prototype.pointerPaneRect = function () {
    if (!this.pointer) return null;
    for (var i = 0; i < this.paneRects.length; i += 1) {
      var rect = this.paneRects[i];
      if (this.pointer.x >= rect.x && this.pointer.x <= rect.x + rect.width && this.pointer.y >= rect.y && this.pointer.y <= rect.y + rect.height) {
        return rect;
      }
    }
    return null;
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

  Chart.prototype.previousBarForTime = function (time) {
    if (time == null || !this.bars.length) return null;
    for (var i = 0; i < this.bars.length; i += 1) {
      if (this.bars[i].time === time) return i > 0 ? this.bars[i - 1] : null;
      if (this.bars[i].time > time) return i > 1 ? this.bars[i - 2] : null;
    }
    return this.bars.length > 1 ? this.bars[this.bars.length - 2] : null;
  };

  Chart.prototype.pricePercentChangeLabel = function (bar) {
    var previous = bar ? this.previousBarForTime(bar.time) : null;
    if (!previous || !previous.close) return '';
    var percent = ((bar.close - previous.close) / previous.close) * 100;
    if (!Number.isFinite(percent)) return '';
    return ' (' + (percent >= 0 ? '+' : '') + percent.toFixed(1) + '%)';
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
          indicatorId: indicator.id,
          output: renderItem.output,
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
    var scaleModeLabel = this.paneScaleMode(rect.paneId).toUpperCase();
    this.scaleHitZones.push({
      x: rect.scaleX + 4,
      y: rect.y + 2,
      width: Math.max(42, approximateTextWidth(scaleModeLabel) + 8),
      height: 20,
      paneId: rect.paneId
    });
    ctx.fillText(scaleModeLabel, rect.scaleX + 6, rect.y + 12);
    for (var i = 0; i <= 4; i += 1) {
      var value = range.max - ((range.max - range.min) / 4) * i;
      if (this.paneScaleMode(rect.paneId) === 'log') {
        var logTransform = logScaleTransform(range);
        var logMin = logTransform.value(range.min);
        var logMax = logTransform.value(range.max);
        if (logMin != null && logMax != null) {
          value = logTransform.inverse(logMax - ((logMax - logMin) / 4) * i);
        }
      }
      var y = rect.y + (rect.height / 4) * i;
      ctx.fillText(formatNumber(value), rect.scaleX + 6, y);
    }
    this.drawScaleMarkers(rect, range, theme);
    ctx.restore();
  };

  Chart.prototype.scaleMarkerItems = function (rect, time, theme) {
    var self = this;
    var items = [];
    var paletteIndex = 0;
    var visible = this.visibleBars();
    var visibleStart = visible.length ? visible[0].time : null;
    if (rect.paneId === 'price') {
      var bar = this.barNearTime(time);
      if (bar && bar.close != null) {
        items.push({
          kind: 'price-close',
          value: bar.close,
          color: bar.close >= bar.open ? theme.up : theme.down,
          label: formatNumber(bar.close)
        });
      }
    }
    this.document.indicators.forEach(function (indicator) {
      if (indicator.paneId !== rect.paneId || indicator.visible === false) return;
      if (rect.paneId === 'price' && indicator.type === 'VOLUME') return;
      var result = self.indicatorResults[indicator.id];
      if (!result) return;
      result.render.forEach(function (renderItem) {
        if (renderItem.type === 'level') return;
        var data = result.outputs[renderItem.output] || [];
        var valuePoint = lastVisibleSeriesPoint(data, visibleStart, time);
        if (!valuePoint || valuePoint.value == null) return;
        var style = merge(defaultStyleForIndicator(theme, paletteIndex), indicator.styles && indicator.styles[renderItem.output] || {});
        var color = style.color || theme.indicatorPalette[paletteIndex % theme.indicatorPalette.length];
        items.push({
          kind: 'indicator',
          indicatorId: indicator.id,
          output: renderItem.output,
          value: valuePoint.value,
          color: color,
          label: formatNumber(valuePoint.value)
        });
        paletteIndex += 1;
      });
    });
    return items;
  };

  Chart.prototype.drawScaleMarkers = function (rect, range, theme) {
    var ctx = this.ctx;
    var time = this.scaleMarkerTimeForPane(rect);
    var items = this.scaleMarkerItems(rect, time, theme);
    if (!items.length) return;
    ctx.save();
    ctx.font = '11px sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    var height = 18;
    var minTop = Math.min(rect.y + 24, rect.y + rect.height - height - 2);
    var maxTop = rect.y + rect.height - height - 2;
    var positioned = [];
    items.forEach(function (item) {
      var y = this.yForValue(item.value, rect, range);
      if (y == null || y < rect.y || y > rect.y + rect.height) return;
      positioned.push({
        item: item,
        y: y,
        top: clamp(y - height / 2, minTop, maxTop)
      });
    }, this);
    positioned.sort(function (a, b) {
      return a.top - b.top;
    });
    positioned.forEach(function (entry, index) {
      if (index > 0) entry.top = Math.max(entry.top, positioned[index - 1].top + height + 2);
      entry.top = clamp(entry.top, minTop, maxTop);
    });
    positioned.forEach(function (entry) {
      var item = entry.item;
      var label = item.label;
      var width = Math.min(rect.scaleWidth - 8, Math.max(34, approximateTextWidth(label) + 12));
      ctx.fillStyle = item.color || theme.crosshair;
      ctx.fillRect(rect.scaleX + 4, entry.top, width, height);
      ctx.fillStyle = contrastTextColor(item.color || theme.crosshair);
      ctx.fillText(label, rect.scaleX + 9, entry.top + height / 2);
    });
    ctx.restore();
  };

  Chart.prototype.drawPriceSeries = function (rect, range, theme) {
    var chartType = normalizeChartType(this.document.settings.chartType);
    if (chartType === 'heikin_ashi') {
      this.drawCandles(rect, range, theme, this.heikinAshiBars());
      return;
    }
    if (chartType === 'bar') {
      this.drawBars(rect, range, theme);
      return;
    }
    if (chartType === 'line') {
      this.drawPriceLine(rect, range, theme);
      return;
    }
    if (chartType === 'area') {
      this.drawPriceArea(rect, range, theme);
      return;
    }
    if (chartType === 'baseline') {
      this.drawPriceBaseline(rect, range, theme);
      return;
    }
    this.drawCandles(rect, range, theme);
  };

  Chart.prototype.drawCandles = function (rect, range, theme, sourceBars) {
    var ctx = this.ctx;
    var source = sourceBars || this.bars;
    var visible = this.visibleBars();
    var visibleStart = visible.length ? visible[0].time : null;
    var visibleEnd = visible.length ? visible[visible.length - 1].time : null;
    var bars = source.filter(function (bar) {
      return visibleStart == null || (bar.time >= visibleStart && bar.time <= visibleEnd);
    });
    if (!bars.length) return;
    var spacing = rect.width / Math.max(1, bars.length);
    var candleWidth = clamp(spacing * 0.62, 2, 18);
    var allStartIndex = source.findIndex(function (candidate) {
      return candidate.time === bars[0].time;
    });
    ctx.save();
    bars.forEach(function (bar, index) {
      var x = this.xForTime(bar.time, rect);
      var open = this.yForValue(bar.open, rect, range);
      var close = this.yForValue(bar.close, rect, range);
      var high = this.yForValue(bar.high, rect, range);
      var low = this.yForValue(bar.low, rect, range);
      if (open == null || close == null || high == null || low == null) return;
      var previousBar = allStartIndex > 0 ? source[allStartIndex + index - 1] : bars[index - 1];
      var previousClose = previousBar ? previousBar.close : bar.open;
      var green = bar.close >= previousClose;
      var hollow = bar.close >= bar.open;
      ctx.strokeStyle = green ? theme.up : theme.down;
      ctx.fillStyle = green ? theme.up : theme.down;
      var bodyTop = Math.min(open, close);
      var bodyBottom = Math.max(open, close);
      var bodyHeight = Math.max(1, bodyBottom - bodyTop);
      ctx.beginPath();
      if (hollow) {
        ctx.moveTo(x, high);
        ctx.lineTo(x, bodyTop);
        ctx.moveTo(x, bodyBottom);
        ctx.lineTo(x, low);
      } else {
        ctx.moveTo(x, high);
        ctx.lineTo(x, low);
      }
      ctx.stroke();
      if (hollow) {
        ctx.strokeRect(x - candleWidth / 2, bodyTop, candleWidth, bodyHeight);
      } else {
        ctx.fillRect(x - candleWidth / 2, bodyTop, candleWidth, bodyHeight);
      }
    }, this);
    ctx.restore();
  };

  Chart.prototype.heikinAshiBars = function () {
    var output = [];
    this.bars.forEach(function (bar, index) {
      var close = (bar.open + bar.high + bar.low + bar.close) / 4;
      var open = index && output[index - 1] ? (output[index - 1].open + output[index - 1].close) / 2 : (bar.open + bar.close) / 2;
      output.push({
        time: bar.time,
        open: open,
        high: Math.max(bar.high, open, close),
        low: Math.min(bar.low, open, close),
        close: close,
        volume: bar.volume
      });
    });
    return output;
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
      if (open == null || close == null || high == null || low == null) return;
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

  Chart.prototype.drawPriceArea = function (rect, range, theme) {
    var data = this.visibleBars().map(function (bar) {
      return { time: bar.time, value: bar.close };
    });
    this.drawAreaSeries(rect, range, theme, data, {
      color: theme.indicatorPalette[0],
      fill: colorWithAlpha(theme.indicatorPalette[0], 0.18),
      lineWidth: 2
    });
  };

  Chart.prototype.drawPriceBaseline = function (rect, range, theme) {
    var bars = this.visibleBars();
    if (!bars.length) return;
    var baseline = bars[0].close;
    var upData = bars.map(function (bar) { return { time: bar.time, value: Math.max(bar.close, baseline) }; });
    var downData = bars.map(function (bar) { return { time: bar.time, value: Math.min(bar.close, baseline) }; });
    this.drawAreaSeries(rect, range, theme, upData, {
      color: theme.up,
      fill: colorWithAlpha(theme.up, 0.16),
      baseline: baseline,
      lineWidth: 2
    });
    this.drawAreaSeries(rect, range, theme, downData, {
      color: theme.down,
      fill: colorWithAlpha(theme.down, 0.14),
      baseline: baseline,
      lineWidth: 2
    });
  };

  Chart.prototype.drawIndicators = function (rect, range, theme) {
    var self = this;
    var paletteIndex = 0;
    this.document.indicators.forEach(function (indicator) {
      if (indicator.paneId !== rect.paneId || indicator.visible === false) return;
      if (rect.paneId === 'price' && indicator.type === 'VOLUME') return;
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

  Chart.prototype.drawPriceVolumeOverlays = function (rect, theme) {
    var self = this;
    this.document.indicators.forEach(function (indicator) {
      if (indicator.paneId !== 'price' || indicator.type !== 'VOLUME' || indicator.visible === false) return;
      var result = self.indicatorResults[indicator.id];
      if (!result) return;
      result.render.forEach(function (renderItem) {
        if (renderItem.type !== 'volume') return;
        var data = result.outputs[renderItem.output] || [];
        var style = merge({ opacity: 0.55 }, indicator.styles && indicator.styles[renderItem.output] || {});
        self.drawVolumeOverlay(rect, theme, data, style);
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
    ctx.globalAlpha = normalizeOpacity(style.opacity, 1);
    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.lineWidth || 2;
    ctx.setLineDash(lineDashForStyle(style.lineStyle));
    ctx.beginPath();
    var started = false;
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var y = this.yForValue(point.value, rect, range);
      if (y == null) {
        started = false;
        return;
      }
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

  Chart.prototype.drawAreaSeries = function (rect, range, theme, data, style) {
    var ctx = this.ctx;
    var visible = this.visibleBars();
    if (!visible.length || !data.length) return;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    var baselineValue = style.baseline != null ? style.baseline : Math.max(range.min, Math.min(range.max, visible[0].close));
    var baselineY = this.yForValue(baselineValue, rect, range);
    if (baselineY == null) baselineY = rect.y + rect.height;
    var points = [];
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var y = this.yForValue(point.value, rect, range);
      if (y != null) points.push({ x: x, y: y });
    }, this);
    if (!points.length) return;
    ctx.save();
    ctx.globalAlpha = normalizeOpacity(style.opacity, 1);
    ctx.fillStyle = style.fill || colorWithAlpha(style.color || theme.indicatorPalette[0], 0.16);
    ctx.beginPath();
    ctx.moveTo(points[0].x, baselineY);
    points.forEach(function (point) {
      ctx.lineTo(point.x, point.y);
    });
    ctx.lineTo(points[points.length - 1].x, baselineY);
    ctx.closePath();
    ctx.fill();
    ctx.strokeStyle = style.color || theme.indicatorPalette[0];
    ctx.lineWidth = style.lineWidth || 2;
    ctx.setLineDash(lineDashForStyle(style.lineStyle));
    ctx.beginPath();
    points.forEach(function (point, index) {
      if (!index) ctx.moveTo(point.x, point.y);
      else ctx.lineTo(point.x, point.y);
    });
    ctx.stroke();
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
    ctx.globalAlpha = normalizeOpacity(style.opacity, 1);
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var y = this.yForValue(point.value, rect, range);
      if (y == null) return;
      if (useVolumeColor) ctx.fillStyle = point.close >= point.open ? theme.volumeUp : theme.volumeDown;
      else ctx.fillStyle = point.value >= 0 ? theme.volumeUp : theme.volumeDown;
      if (style.color) ctx.fillStyle = style.color;
      ctx.fillRect(x - barWidth / 2, Math.min(y, zeroY), barWidth, Math.max(1, Math.abs(zeroY - y)));
    }, this);
    ctx.restore();
  };

  Chart.prototype.drawVolumeOverlay = function (rect, theme, data, style) {
    var ctx = this.ctx;
    var visible = this.visibleBars();
    if (!visible.length || !data.length) return;
    var first = visible[0].time;
    var last = visible[visible.length - 1].time;
    var maxVolume = 0;
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      maxVolume = Math.max(maxVolume, point.value);
    });
    if (!maxVolume) return;
    var spacing = rect.width / Math.max(1, visible.length);
    var barWidth = clamp(spacing * 0.7, 1, 14);
    var overlayHeight = Math.max(36, Math.min(rect.height * 0.28, 130));
    var baseline = rect.y + rect.height - 2;
    var opacity = normalizeOpacity(style.opacity, 0.55);
    ctx.save();
    ctx.globalAlpha = opacity;
    data.forEach(function (point) {
      if (point.time < first || point.time > last || point.value == null) return;
      var x = this.xForTime(point.time, rect);
      var height = Math.max(1, (point.value / maxVolume) * overlayHeight);
      ctx.fillStyle = style.color || (point.close >= point.open ? theme.volumeUp : theme.volumeDown);
      ctx.fillRect(x - barWidth / 2, baseline - height, barWidth, height);
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

  Chart.prototype.drawPendingDrawing = function (rect, range, theme) {
    if (!this.pendingDrawing) return;
    if (this.pendingDrawing.paneId && this.pendingDrawing.paneId !== rect.paneId) return;
    var points = clone(this.pendingDrawing.points || []);
    if (this.pointer && points.length && points.length < this.pendingDrawing.requiredPoints) {
      var preview = this.valueFromPoint(this.pointer, rect.paneId);
      if (preview && (!this.pendingDrawing.paneId || preview.paneId === this.pendingDrawing.paneId)) {
        points.push({ time: preview.time, value: preview.value });
      }
    }
    if (!points.length) return;
    this.drawDrawing(rect, range, theme, {
      id: 'pending-drawing',
      type: this.pendingDrawing.type,
      paneId: rect.paneId,
      points: points,
      text: this.pendingDrawing.text,
      style: merge({ color: theme.drawing, width: 2, fill: 'rgba(37, 99, 235, 0.08)', lineStyle: 'dash' }, this.pendingDrawing.style || {}),
      visible: true
    });
  };

  Chart.prototype.drawDrawing = function (rect, range, theme, drawing) {
    var ctx = this.ctx;
    var rawPoints = drawing.points || [];
    var points = this.drawingScreenPoints(drawing).filter(function (point) { return point.y != null; });
    var tool = drawingToolDefinition(drawing.type);
    var kind = tool.renderKind;
    var style = merge({ color: theme.drawing, width: 2, fill: 'rgba(37, 99, 235, 0.12)', font: '12px sans-serif' }, drawing.style || {});
    if (!style.color) style.color = theme.drawing;
    if (!points.length) return;

    function point(index) {
      return points[Math.min(index, points.length - 1)];
    }

    function drawValueLabel(text, anchor) {
      ctx.fillStyle = style.color;
      ctx.fillText(text, anchor.x + 6, anchor.y - 6);
      ctx.fillStyle = style.fill;
    }

    ctx.save();
    ctx.globalAlpha = normalizeOpacity(style.opacity, 1);
    ctx.strokeStyle = style.color;
    ctx.fillStyle = style.fill;
    ctx.lineWidth = style.width || 2;
    ctx.font = style.font || '12px sans-serif';
    ctx.setLineDash(lineDashForStyle(style.lineStyle));

    if (kind === 'hline' && point(0)) {
      var y = point(0).y;
      ctx.beginPath();
      ctx.moveTo(rect.x, y);
      ctx.lineTo(rect.x + rect.width, y);
      ctx.stroke();
    } else if (kind === 'horizontalRay' && point(0)) {
      ctx.beginPath();
      ctx.moveTo(point(0).x, point(0).y);
      ctx.lineTo(rect.x + rect.width, point(0).y);
      ctx.stroke();
    } else if (kind === 'vline' && point(0)) {
      var x = point(0).x;
      ctx.beginPath();
      ctx.moveTo(x, rect.y);
      ctx.lineTo(x, rect.y + rect.height);
      ctx.stroke();
    } else if (kind === 'crossline' && point(0)) {
      ctx.beginPath();
      ctx.moveTo(rect.x, point(0).y);
      ctx.lineTo(rect.x + rect.width, point(0).y);
      ctx.moveTo(point(0).x, rect.y);
      ctx.lineTo(point(0).x, rect.y + rect.height);
      ctx.stroke();
    } else if ((kind === 'rectangle' || kind === 'gridBox') && point(1)) {
      drawScreenRectangle(ctx, point(0), point(1), true);
      if (kind === 'gridBox') drawGridBox(ctx, point(0), point(1), 3, 3);
    } else if (kind === 'rotatedRectangle' && point(2)) {
      drawPolyline(ctx, rotatedRectanglePoints(point(0), point(1), point(2)), true);
      ctx.fill();
      ctx.stroke();
    } else if (kind === 'ellipse' && point(1)) {
      drawEllipseApprox(ctx, point(0), point(1));
      ctx.fill();
      ctx.stroke();
    } else if (kind === 'text' && point(0)) {
      ctx.fillStyle = style.color;
      ctx.fillText(drawing.text || tool.name, point(0).x, point(0).y);
    } else if (kind === 'priceLabel' && point(0)) {
      var label = drawing.text || formatNumber(rawPoints[0] && rawPoints[0].value != null ? rawPoints[0].value : point(0).value);
      drawTag(ctx, label, point(0), style.color, theme.background);
    } else if (kind === 'callout' && point(0)) {
      var target = point(1) || { x: point(0).x + 56, y: point(0).y - 34 };
      drawLine(ctx, point(0), target);
      drawTag(ctx, drawing.text || tool.name, target, style.color, theme.background);
    } else if (kind.indexOf('marker') === 0 || kind === 'flag') {
      drawMarker(ctx, point(0), kind, style.color);
      if (drawing.text) drawValueLabel(drawing.text, point(0));
    } else if (kind === 'line' || kind === 'arrow' || kind === 'measurement' || kind === 'angle') {
      if (point(1)) {
        drawLine(ctx, point(0), point(1));
        if (kind === 'arrow') drawArrowHead(ctx, point(0).x, point(0).y, point(1).x, point(1).y, style.color);
        if (kind === 'measurement') drawValueLabel(measurementLabel(rawPoints[0], rawPoints[1]), midpoint(point(0), point(1)));
        if (kind === 'angle') drawValueLabel(angleLabel(point(0), point(1)), midpoint(point(0), point(1)));
      }
    } else if ((kind === 'ray' || kind === 'extendedLine') && point(1)) {
      drawProjectedLine(ctx, point(0), point(1), rect, kind === 'ray');
    } else if (kind === 'channel' || kind === 'fibChannel' || kind === 'wedge') {
      drawChannel(ctx, points, kind === 'fibChannel');
    } else if (kind === 'fibRetracement' && point(1)) {
      drawFibLevels(ctx, rect, point(0), point(1), style.color, false);
    } else if (kind === 'fibExtension' && point(2)) {
      drawPolyline(ctx, [point(0), point(1), point(2)], false);
      ctx.stroke();
      drawFibLevels(ctx, rect, point(1), point(2), style.color, true);
    } else if (kind === 'timeZones' && point(1)) {
      drawTimeZones(ctx, rect, point(0), point(1), style.color);
    } else if (kind === 'fan' && point(1)) {
      drawFan(ctx, rect, point(0), point(1), style.color);
    } else if (kind === 'pitchfork' && point(2)) {
      drawPitchfork(ctx, rect, point(0), point(1), point(2));
    } else if (kind === 'circles' && point(1)) {
      drawFibCircles(ctx, point(0), point(1));
    } else if (kind === 'spiral' && point(1)) {
      drawSpiral(ctx, point(0), point(1));
    } else if (kind === 'arcs' && point(1)) {
      drawFibArcs(ctx, point(0), point(1));
    } else if (kind === 'arc' && point(2)) {
      drawArcThroughPoints(ctx, point(0), point(1), point(2));
    } else if (kind === 'curve' || kind === 'doubleCurve') {
      drawSmoothPolyline(ctx, points);
      ctx.stroke();
    } else if (kind === 'polygon') {
      drawPolyline(ctx, points, true);
      ctx.fill();
      ctx.stroke();
    } else if (kind === 'polyline' || kind === 'path') {
      if (drawing.type === 'highlighter') {
        ctx.lineWidth = Math.max(8, (style.width || 2) * 4);
      }
      drawPolyline(ctx, points, false);
      ctx.stroke();
    } else if (kind === 'pattern' || kind === 'numberedWave' || kind === 'letteredWave') {
      drawPolyline(ctx, points, false);
      ctx.stroke();
      drawPatternLabels(ctx, points, kind, style.color);
    } else if (kind === 'cyclicLines' && point(1)) {
      drawCyclicLines(ctx, rect, point(0), point(1), style.color);
    } else if (kind === 'sine' && point(1)) {
      drawSineLine(ctx, point(0), point(1));
      ctx.stroke();
    } else if (kind.indexOf('position') === 0 || kind === 'dateRange' || kind === 'priceRange' || kind === 'datePriceRange') {
      if (point(1)) {
        drawScreenRectangle(ctx, point(0), point(1), true);
        drawValueLabel(rangeLabel(kind, rawPoints[0], rawPoints[1]), midpoint(point(0), point(1)));
      }
    } else if (kind === 'barsPattern' || kind === 'ghostFeed') {
      drawGhostBars(ctx, point(0), point(1) || { x: point(0).x + 80, y: point(0).y - 30 }, kind === 'ghostFeed');
    } else if (kind === 'sector' && point(2)) {
      drawSector(ctx, point(0), point(1), point(2));
      ctx.stroke();
    } else if (kind === 'volumeProfile' && point(1)) {
      drawVolumeProfile(ctx, point(0), point(1), style.color);
    } else if (kind === 'vwapAnchor' && point(0)) {
      drawMarker(ctx, point(0), 'markerUp', style.color);
      drawValueLabel('Anchored VWAP', point(0));
    } else if (point(1)) {
      drawPolyline(ctx, points, false);
      ctx.stroke();
    } else {
      drawMarker(ctx, point(0), 'marker', style.color);
    }

    if (drawing.id === this.selectedDrawingId || drawing.id === this.hoverDrawingId) {
      ctx.globalAlpha = 1;
      this.drawDrawingSelection(drawing, theme);
    }
    ctx.restore();
  };

  Chart.prototype.drawDrawingSelection = function (drawing, theme) {
    var ctx = this.ctx;
    var points = this.drawingScreenPoints(drawing);
    points = points.filter(function (point) { return point.y != null; });
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
    var activeRect = this.pointerPaneRect();
    if (!activeRect) return;
    ctx.save();
    ctx.strokeStyle = theme.crosshair;
    ctx.setLineDash([3, 3]);
    ctx.beginPath();
    this.paneRects.forEach(function (rect) {
      ctx.moveTo(this.pointer.x, rect.y);
      ctx.lineTo(this.pointer.x, rect.y + rect.height);
    }, this);
    ctx.moveTo(activeRect.x, this.pointer.y);
    ctx.lineTo(activeRect.x + activeRect.width, this.pointer.y);
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

  function drawLine(ctx, a, b) {
    ctx.beginPath();
    ctx.moveTo(a.x, a.y);
    ctx.lineTo(b.x, b.y);
    ctx.stroke();
  }

  function drawPolyline(ctx, points, closed) {
    if (!points.length) return;
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (var i = 1; i < points.length; i += 1) {
      ctx.lineTo(points[i].x, points[i].y);
    }
    if (closed && points.length > 2) ctx.closePath();
  }

  function drawSmoothPolyline(ctx, points) {
    if (points.length < 3 || !ctx.quadraticCurveTo) {
      drawPolyline(ctx, points, false);
      return;
    }
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (var i = 1; i < points.length - 1; i += 1) {
      var mid = midpoint(points[i], points[i + 1]);
      ctx.quadraticCurveTo(points[i].x, points[i].y, mid.x, mid.y);
    }
    ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
  }

  function drawScreenRectangle(ctx, a, b, fill) {
    var x = Math.min(a.x, b.x);
    var y = Math.min(a.y, b.y);
    var width = Math.abs(b.x - a.x);
    var height = Math.abs(b.y - a.y);
    if (fill) ctx.fillRect(x, y, width, height);
    ctx.strokeRect(x, y, width, height);
  }

  function drawGridBox(ctx, a, b, columns, rows) {
    var minX = Math.min(a.x, b.x);
    var maxX = Math.max(a.x, b.x);
    var minY = Math.min(a.y, b.y);
    var maxY = Math.max(a.y, b.y);
    ctx.beginPath();
    for (var c = 1; c < columns; c += 1) {
      var x = minX + (maxX - minX) * c / columns;
      ctx.moveTo(x, minY);
      ctx.lineTo(x, maxY);
    }
    for (var r = 1; r < rows; r += 1) {
      var y = minY + (maxY - minY) * r / rows;
      ctx.moveTo(minX, y);
      ctx.lineTo(maxX, y);
    }
    ctx.stroke();
  }

  function drawEllipseApprox(ctx, a, b) {
    var cx = (a.x + b.x) / 2;
    var cy = (a.y + b.y) / 2;
    var rx = Math.max(2, Math.abs(b.x - a.x) / 2);
    var ry = Math.max(2, Math.abs(b.y - a.y) / 2);
    ctx.beginPath();
    for (var i = 0; i <= 40; i += 1) {
      var angle = Math.PI * 2 * i / 40;
      var x = cx + Math.cos(angle) * rx;
      var y = cy + Math.sin(angle) * ry;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.closePath();
  }

  function drawArcApprox(ctx, center, radius, startAngle, endAngle, steps) {
    ctx.beginPath();
    for (var i = 0; i <= steps; i += 1) {
      var angle = startAngle + (endAngle - startAngle) * i / steps;
      var x = center.x + Math.cos(angle) * radius;
      var y = center.y + Math.sin(angle) * radius;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
  }

  function drawFibCircles(ctx, a, b) {
    var base = Math.max(4, distance(a, b));
    [0.382, 0.5, 0.618, 1].forEach(function (ratio) {
      drawArcApprox(ctx, a, base * ratio, 0, Math.PI * 2, 48);
      ctx.stroke();
    });
  }

  function drawFibArcs(ctx, a, b) {
    var base = Math.max(4, distance(a, b));
    [0.382, 0.5, 0.618, 1].forEach(function (ratio) {
      drawArcApprox(ctx, a, base * ratio, 0, Math.PI, 32);
      ctx.stroke();
    });
  }

  function drawSpiral(ctx, a, b) {
    var base = Math.max(6, distance(a, b) / 8);
    ctx.beginPath();
    for (var i = 0; i <= 96; i += 1) {
      var angle = i / 8;
      var radius = base * Math.pow(1.045, i);
      var x = a.x + Math.cos(angle) * radius;
      var y = a.y + Math.sin(angle) * radius;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();
  }

  function drawArcThroughPoints(ctx, a, b, c) {
    var center = b;
    var radius = Math.max(4, distance(a, center));
    var start = Math.atan2(a.y - center.y, a.x - center.x);
    var end = Math.atan2(c.y - center.y, c.x - center.x);
    drawArcApprox(ctx, center, radius, start, end, 32);
    ctx.stroke();
  }

  function rotatedRectanglePoints(a, b, c) {
    return [a, b, c, { x: a.x + c.x - b.x, y: a.y + c.y - b.y }];
  }

  function lineYAtX(a, b, x) {
    if (b.x === a.x) return a.y;
    return a.y + (b.y - a.y) * ((x - a.x) / (b.x - a.x));
  }

  function drawProjectedLine(ctx, a, b, rect, rayOnly) {
    if (a.x === b.x) {
      ctx.beginPath();
      ctx.moveTo(a.x, rayOnly ? a.y : rect.y);
      ctx.lineTo(a.x, rect.y + rect.height);
      ctx.stroke();
      return;
    }
    var startX = rayOnly ? a.x : rect.x;
    var endX = rect.x + rect.width;
    drawLine(ctx, { x: startX, y: lineYAtX(a, b, startX) }, { x: endX, y: lineYAtX(a, b, endX) });
  }

  function drawChannel(ctx, points, fib) {
    if (points.length < 2) return;
    drawLine(ctx, points[0], points[1]);
    var offset = points[2] ? { x: points[2].x - points[0].x, y: points[2].y - points[0].y } : { x: 0, y: -40 };
    var p2 = { x: points[0].x + offset.x, y: points[0].y + offset.y };
    var p3 = { x: points[1].x + offset.x, y: points[1].y + offset.y };
    drawLine(ctx, p2, p3);
    drawPolyline(ctx, [points[0], points[1], p3, p2], true);
    ctx.fill();
    if (fib) {
      [0.236, 0.382, 0.5, 0.618, 0.786].forEach(function (ratio) {
        drawLine(ctx,
          { x: points[0].x + offset.x * ratio, y: points[0].y + offset.y * ratio },
          { x: points[1].x + offset.x * ratio, y: points[1].y + offset.y * ratio }
        );
      });
    }
  }

  function drawFibLevels(ctx, rect, a, b, color, extension) {
    var levels = extension ? [0, 0.618, 1, 1.272, 1.618, 2.618] : [0, 0.236, 0.382, 0.5, 0.618, 0.786, 1];
    var topX = Math.min(a.x, b.x);
    var bottomX = Math.max(a.x, b.x);
    ctx.fillStyle = color;
    levels.forEach(function (level) {
      var y = a.y + (b.y - a.y) * level;
      ctx.beginPath();
      ctx.moveTo(topX, y);
      ctx.lineTo(Math.max(bottomX, rect.x + rect.width), y);
      ctx.stroke();
      ctx.fillText(String(level), topX + 4, y - 4);
    });
  }

  function drawTimeZones(ctx, rect, a, b, color) {
    var step = Math.max(8, Math.abs(b.x - a.x));
    var fib = [0, 1, 2, 3, 5, 8, 13, 21, 34];
    ctx.fillStyle = color;
    fib.forEach(function (value) {
      var x = a.x + step * value;
      if (x > rect.x + rect.width) return;
      ctx.beginPath();
      ctx.moveTo(x, rect.y);
      ctx.lineTo(x, rect.y + rect.height);
      ctx.stroke();
      ctx.fillText(String(value), x + 3, rect.y + 12);
    });
  }

  function drawCyclicLines(ctx, rect, a, b, color) {
    var step = Math.max(8, Math.abs(b.x - a.x));
    ctx.fillStyle = color;
    for (var x = a.x; x <= rect.x + rect.width; x += step) {
      ctx.beginPath();
      ctx.moveTo(x, rect.y);
      ctx.lineTo(x, rect.y + rect.height);
      ctx.stroke();
    }
  }

  function drawFan(ctx, rect, a, b, color) {
    var ratios = [0.25, 0.333, 0.5, 0.667, 1, 1.5, 2, 3];
    ctx.fillStyle = color;
    ratios.forEach(function (ratio) {
      var target = { x: b.x, y: a.y + (b.y - a.y) * ratio };
      drawProjectedLine(ctx, a, target, rect, true);
    });
  }

  function drawPitchfork(ctx, rect, a, b, c) {
    var forkMid = midpoint(b, c);
    drawProjectedLine(ctx, a, forkMid, rect, true);
    var offsetB = { x: b.x - forkMid.x, y: b.y - forkMid.y };
    var offsetC = { x: c.x - forkMid.x, y: c.y - forkMid.y };
    drawProjectedLine(ctx, { x: a.x + offsetB.x, y: a.y + offsetB.y }, b, rect, true);
    drawProjectedLine(ctx, { x: a.x + offsetC.x, y: a.y + offsetC.y }, c, rect, true);
  }

  function drawMarker(ctx, p, kind, color) {
    var size = 9;
    ctx.fillStyle = color;
    ctx.beginPath();
    if (kind === 'markerLeft') {
      ctx.moveTo(p.x - size, p.y);
      ctx.lineTo(p.x + size, p.y - size);
      ctx.lineTo(p.x + size, p.y + size);
    } else if (kind === 'markerRight') {
      ctx.moveTo(p.x + size, p.y);
      ctx.lineTo(p.x - size, p.y - size);
      ctx.lineTo(p.x - size, p.y + size);
    } else if (kind === 'markerDown') {
      ctx.moveTo(p.x, p.y + size);
      ctx.lineTo(p.x - size, p.y - size);
      ctx.lineTo(p.x + size, p.y - size);
    } else {
      ctx.moveTo(p.x, p.y - size);
      ctx.lineTo(p.x - size, p.y + size);
      ctx.lineTo(p.x + size, p.y + size);
    }
    ctx.closePath();
    ctx.fill();
    if (kind === 'flag') {
      ctx.fillRect(p.x, p.y - 22, 22, 13);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y - 22);
      ctx.lineTo(p.x, p.y + 12);
      ctx.stroke();
    }
  }

  function drawTag(ctx, label, p, color, background) {
    var width = Math.max(52, approximateTextWidth(label) + 14);
    var height = 22;
    ctx.fillStyle = color;
    ctx.fillRect(p.x, p.y - height, width, height);
    ctx.fillStyle = background || '#fff';
    ctx.fillText(label, p.x + 7, p.y - 7);
  }

  function drawPatternLabels(ctx, points, kind, color) {
    var letters = ['A', 'B', 'C', 'D', 'E', 'W', 'X', 'Y', 'X', 'Z'];
    ctx.fillStyle = color;
    points.forEach(function (point, index) {
      var label = kind === 'numberedWave' ? String(index + 1) : letters[index] || String(index + 1);
      ctx.fillText(label, point.x + 5, point.y - 5);
    });
  }

  function drawSineLine(ctx, a, b) {
    var amplitude = Math.max(8, Math.abs(b.y - a.y) / 2);
    var centerY = (a.y + b.y) / 2;
    ctx.beginPath();
    for (var i = 0; i <= 80; i += 1) {
      var t = i / 80;
      var x = a.x + (b.x - a.x) * t;
      var y = centerY + Math.sin(t * Math.PI * 4) * amplitude;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
  }

  function drawGhostBars(ctx, a, b, ghost) {
    var minX = Math.min(a.x, b.x);
    var maxX = Math.max(a.x, b.x);
    var width = Math.max(6, (maxX - minX) / 8);
    for (var i = 0; i < 8; i += 1) {
      var x = minX + i * width;
      var high = Math.min(a.y, b.y) + (i % 3) * 8;
      var low = Math.max(a.y, b.y) - (i % 2) * 8;
      ctx.strokeRect(x, high, Math.max(3, width * 0.55), Math.max(8, low - high));
    }
    if (ghost) drawScreenRectangle(ctx, a, b, false);
  }

  function drawSector(ctx, center, a, b) {
    drawLine(ctx, center, a);
    drawLine(ctx, center, b);
    drawArcThroughPoints(ctx, a, center, b);
  }

  function drawVolumeProfile(ctx, a, b, color) {
    var minX = Math.min(a.x, b.x);
    var minY = Math.min(a.y, b.y);
    var maxY = Math.max(a.y, b.y);
    var height = (maxY - minY) / 8;
    ctx.fillStyle = color;
    for (var i = 0; i < 8; i += 1) {
      var width = Math.abs(b.x - a.x) * (0.35 + (Math.sin(i) + 1) * 0.3);
      ctx.fillRect(minX, minY + i * height, width, Math.max(2, height - 2));
    }
  }

  function midpoint(a, b) {
    return { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
  }

  function measurementLabel(a, b) {
    if (!a || !b) return '';
    return formatNumber((b.value || 0) - (a.value || 0));
  }

  function rangeLabel(kind, a, b) {
    if (!a || !b) return kind;
    if (kind === 'dateRange') return Math.abs(Math.round((b.time - a.time) / 86400)) + ' bars';
    if (kind === 'priceRange') return formatNumber((b.value || 0) - (a.value || 0));
    return Math.abs(Math.round((b.time - a.time) / 86400)) + ' bars / ' + formatNumber((b.value || 0) - (a.value || 0));
  }

  function angleLabel(a, b) {
    var angle = Math.atan2(b.y - a.y, b.x - a.x) * 180 / Math.PI;
    return Math.abs(angle).toFixed(1) + ' deg';
  }

  function pointBounds(points) {
    var bounds = { minX: points[0].x, maxX: points[0].x, minY: points[0].y, maxY: points[0].y };
    points.forEach(function (point) {
      bounds.minX = Math.min(bounds.minX, point.x);
      bounds.maxX = Math.max(bounds.maxX, point.x);
      bounds.minY = Math.min(bounds.minY, point.y);
      bounds.maxY = Math.max(bounds.maxY, point.y);
    });
    return bounds;
  }

  function pointerInBounds(pointer, bounds, tolerance) {
    return pointer.x >= bounds.minX - tolerance && pointer.x <= bounds.maxX + tolerance && pointer.y >= bounds.minY - tolerance && pointer.y <= bounds.maxY + tolerance;
  }

  function drawingToolDefinition(type) {
    return DRAWING_TOOLS[normalizeDrawingType(type)] || DRAWING_TOOLS.text;
  }

  function drawingRequiredPointCount(type) {
    var tool = drawingToolDefinition(type);
    return Math.max(1, tool.points || 1);
  }

  function isEditableTextDrawing(drawing) {
    if (!drawing) return false;
    var kind = drawingToolDefinition(drawing.type).renderKind;
    return kind === 'text' || kind === 'callout' || kind === 'priceLabel';
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

  function lastVisibleSeriesPoint(data, startTime, endTime) {
    if (!data || !data.length) return null;
    if (endTime == null) return data[data.length - 1] || null;
    for (var i = data.length - 1; i >= 0; i -= 1) {
      var point = data[i];
      if (point.value == null) continue;
      if (point.time <= endTime && (startTime == null || point.time >= startTime)) return point;
    }
    return null;
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
    var key = normalizeDrawingKey(type || 'text');
    if (DRAWING_TOOLS[key]) return key;
    var legacyMap = {
      horizontal_line: 'hline',
      vertical_line: 'vline',
      trend_line: 'trendline',
      anchored_text: 'anchored_note',
      arrowmark_left: 'arrow_mark_left',
      arrowmark_right: 'arrow_mark_right',
      arrowmark_up: 'arrow_mark_up',
      arrowmark_down: 'arrow_mark_down'
    };
    if (legacyMap[key]) return legacyMap[key];
    var ids = Object.keys(DRAWING_TOOLS);
    for (var i = 0; i < ids.length; i += 1) {
      if (DRAWING_TOOLS[ids[i]].aliases.indexOf(key) !== -1) return ids[i];
    }
    return key || 'text';
  }

  function normalizeDrawingKey(value) {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replace(/&/g, 'and')
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '')
      .replace(/_+/g, '_');
  }

  function normalizeChartType(type) {
    var normalized = String(type || 'candlestick').toLowerCase();
    var map = {
      candle: 'candlestick',
      candles: 'candlestick',
      candlestick: 'candlestick',
      candlesticks: 'candlestick',
      hollow: 'candlestick',
      hollow_candle: 'candlestick',
      hollow_candles: 'candlestick',
      hollow_candlestick: 'candlestick',
      hollow_candlesticks: 'candlestick',
      'hollow-candle': 'candlestick',
      'hollow-candles': 'candlestick',
      'hollow-candlestick': 'candlestick',
      'hollow-candlesticks': 'candlestick',
      heikin: 'heikin_ashi',
      heikinashi: 'heikin_ashi',
      heikin_ashi: 'heikin_ashi',
      'heikin-ashi': 'heikin_ashi',
      ha: 'heikin_ashi',
      bar: 'bar',
      bars: 'bar',
      ohlc: 'bar',
      line: 'line',
      area: 'area',
      mountain: 'area',
      baseline: 'baseline',
      base_line: 'baseline',
      'base-line': 'baseline'
    };
    return map[normalized] || 'candlestick';
  }

  function normalizeScaleMode(mode) {
    return String(mode || 'linear').toLowerCase() === 'log' ? 'log' : 'linear';
  }

  function normalizeLineStyle(style) {
    var normalized = String(style || 'solid').toLowerCase();
    if (normalized === 'dash' || normalized === 'dashed') return 'dash';
    if (normalized === 'dot' || normalized === 'dotted') return 'dot';
    return 'solid';
  }

  function lineDashForStyle(style) {
    var normalized = normalizeLineStyle(style);
    if (normalized === 'dash') return [8, 5];
    if (normalized === 'dot') return [2, 5];
    return [];
  }

  function sanitizeIndicatorInputs(inputs) {
    var output = {};
    Object.keys(inputs || {}).forEach(function (key) {
      var value = inputs[key];
      var numeric = Number(value);
      output[key] = Number.isFinite(numeric) ? numeric : value;
    });
    return output;
  }

  function sanitizeIndicatorStyles(styles) {
    var output = {};
    Object.keys(styles || {}).forEach(function (outputName) {
      var style = merge({}, styles[outputName] || {});
      if (style.lineWidth != null) style.lineWidth = clamp(Number(style.lineWidth) || 1, 1, 8);
      if (style.lineStyle != null) style.lineStyle = normalizeLineStyle(style.lineStyle);
      if (style.opacity != null) style.opacity = normalizeOpacity(style.opacity, 1);
      output[outputName] = style;
    });
    return output;
  }

  function sanitizeDrawingStyle(style) {
    var output = merge({}, style || {});
    if (output.lineWidth != null) output.width = output.lineWidth;
    if (output.width != null) output.width = clamp(Number(output.width) || 1, 1, 16);
    if (output.lineStyle != null) output.lineStyle = normalizeLineStyle(output.lineStyle);
    if (output.opacity != null) output.opacity = normalizeOpacity(output.opacity, 1);
    delete output.lineWidth;
    return output;
  }

  function normalizeOpacity(value, fallback) {
    var opacity = Number(value);
    if (!Number.isFinite(opacity)) opacity = fallback == null ? 1 : Number(fallback);
    if (!Number.isFinite(opacity)) opacity = 1;
    return clamp(opacity, 0.05, 1);
  }

  function colorWithAlpha(color, alpha) {
    var hex = String(color || '').trim();
    if (/^#[0-9a-f]{3}$/i.test(hex)) {
      hex = '#' + hex.charAt(1) + hex.charAt(1) + hex.charAt(2) + hex.charAt(2) + hex.charAt(3) + hex.charAt(3);
    }
    if (/^#[0-9a-f]{6}$/i.test(hex)) {
      var r = parseInt(hex.slice(1, 3), 16);
      var g = parseInt(hex.slice(3, 5), 16);
      var b = parseInt(hex.slice(5, 7), 16);
      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }
    return null;
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

  function signedLogValue(value) {
    if (!Number.isFinite(value)) return null;
    if (value === 0) return 0;
    return (value < 0 ? -1 : 1) * Math.log10(1 + Math.abs(value));
  }

  function signedLogInverse(value) {
    if (!Number.isFinite(value)) return 0;
    if (value === 0) return 0;
    return (value < 0 ? -1 : 1) * (Math.pow(10, Math.abs(value)) - 1);
  }

  function logScaleTransform(range) {
    var useSignedLog = !range || range.min <= 0;
    return {
      value: function (value) {
        value = Number(value);
        if (!Number.isFinite(value)) return null;
        if (useSignedLog) return signedLogValue(value);
        return value > 0 ? Math.log10(value) : null;
      },
      inverse: function (value) {
        return useSignedLog ? signedLogInverse(value) : Math.pow(10, value);
      }
    };
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
    });
  }

  function colorPaletteHtml(selectedColor) {
    var colors = DEFAULT_SERIES_COLOR_ORDER.concat(['#111827', '#f9fafb', '#facc15', '#22c55e', '#ef4444', '#38bdf8']);
    return colors.map(function (color) {
      return '<button type="button" data-sce-popup-action="pick-color" data-sce-color="' + escapeHtml(color) + '" style="background:' + escapeHtml(color) + '"' + (color.toLowerCase() === String(selectedColor || '').toLowerCase() ? ' class="is-selected"' : '') + ' aria-label="' + escapeHtml(color) + '"></button>';
    }).join('');
  }

  function closest(element, className) {
    while (element) {
      if (element.className && String(element.className).split(/\s+/).indexOf(className) !== -1) return element;
      element = element.parentNode;
    }
    return null;
  }

  function closestAttribute(element, attributeName) {
    while (element) {
      if (element.getAttribute && element.getAttribute(attributeName) != null) return element;
      element = element.parentNode;
    }
    return null;
  }

  function paneControlIconSvg(icon) {
    var common = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">';
    var paths = {
      'chevron-up': '<path d="m7 14 5-5 5 5"/>',
      'chevron-down': '<path d="m7 10 5 5 5-5"/>',
      maximize: '<path d="M8 4H4v4"/><path d="M16 4h4v4"/><path d="M20 16v4h-4"/><path d="M8 20H4v-4"/><path d="M4 4l6 6"/><path d="m20 4-6 6"/><path d="m20 20-6-6"/><path d="m4 20 6-6"/>',
      minimize: '<path d="M10 4v6H4"/><path d="M14 4v6h6"/><path d="M14 20v-6h6"/><path d="M10 20v-6H4"/>',
      close: '<path d="M6 6l12 12"/><path d="M18 6 6 18"/>',
      trash: '<path d="M4 7h16"/><path d="M9 7V5h6v2"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v5"/><path d="M14 11v5"/>',
      download: '<path d="M12 4v10"/><path d="m8 10 4 4 4-4"/><path d="M5 20h14"/>',
      copy: '<rect x="8" y="8" width="11" height="13" rx="1.5"/><path d="M5 16H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v1"/>',
      'zoom-in': '<circle cx="10.5" cy="10.5" r="5.5"/><path d="m15 15 5 5"/><path d="M10.5 8v5"/><path d="M8 10.5h5"/>',
      'zoom-out': '<circle cx="10.5" cy="10.5" r="5.5"/><path d="m15 15 5 5"/><path d="M8 10.5h5"/>',
      magnet: '<path d="M7 5v7a5 5 0 0 0 10 0V5"/><path d="M7 5h4"/><path d="M13 5h4"/><path d="M7 9h4"/><path d="M13 9h4"/>',
      repeat: '<path d="M17 2l4 4-4 4"/><path d="M3 11V9a3 3 0 0 1 3-3h15"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a3 3 0 0 1-3 3H3"/>',
      lock: '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
      unlock: '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M16 10V7a4 4 0 0 0-7.7-1.5"/>',
      eye: '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
      'eye-off': '<path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 13.4 13.4"/><path d="M9.9 4.3A10.4 10.4 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-3.2 4.1"/><path d="M6.6 6.6C3.8 8.5 2 12 2 12s4 8 10 8a10 10 0 0 0 4.2-.9"/>'
    };
    return common + (paths[icon] || paths.maximize) + '</svg>';
  }

  function chartTypeIconSvg(type) {
    var normalized = normalizeChartType(type);
    var paths = {
      candlestick: '<path d="M7 4v4"/><rect x="5" y="8" width="4" height="7"/><path d="M7 15v5"/><path d="M17 4v3"/><rect x="15" y="7" width="4" height="9"/><path d="M17 16v4"/>',
      heikin_ashi: '<path d="M7 4v4"/><rect x="5" y="8" width="4" height="8"/><path d="M7 16v4"/><path d="M17 4v5"/><rect x="15" y="9" width="4" height="6"/><path d="M17 15v5"/><path d="M4 20h16"/>',
      bar: '<path d="M7 4v16"/><path d="M4 8h3"/><path d="M7 15h4"/><path d="M17 4v16"/><path d="M14 11h3"/><path d="M17 17h4"/>',
      line: '<path d="M4 17l5-6 4 3 7-8"/><circle cx="4" cy="17" r="1.2"/><circle cx="9" cy="11" r="1.2"/><circle cx="13" cy="14" r="1.2"/><circle cx="20" cy="6" r="1.2"/>',
      area: '<path d="M4 17l5-6 4 3 7-8"/><path d="M4 20V17l5-6 4 3 7-8v14z" fill="currentColor" opacity=".16" stroke="none"/>',
      baseline: '<path d="M4 12h16"/><path d="M4 17l5-5 4 2 7-8"/><path d="M4 7l5 5 4-2 7 8"/>'
    };
    return '<svg class="sce-chart-type-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' + (paths[normalized] || paths.candlestick) + '</svg>';
  }

  function chartTypeLabel(type) {
    var normalized = normalizeChartType(type);
    if (normalized === 'heikin_ashi') return 'Heikin Ashi';
    if (normalized === 'bar') return 'Bar';
    if (normalized === 'line') return 'Line';
    if (normalized === 'area') return 'Area';
    if (normalized === 'baseline') return 'Baseline';
    return 'Candlestick';
  }

  function chartTypeChevronSvg() {
    return '<svg class="sce-chart-type-chevron" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
  }

  function dateRangeButtonsHtml(attributeName) {
    return DATE_RANGE_PRESETS.map(function (preset) {
      return '<button type="button" ' + attributeName + '="' + escapeHtml(preset.id) + '" aria-pressed="false">' + escapeHtml(preset.label) + '</button>';
    }).join('');
  }

  function dateRangeLabel(presetId) {
    var preset = dateRangePresetById(presetId);
    return preset ? preset.label : 'Auto';
  }

  function formatNumber(value) {
    var abs = Math.abs(value);
    if (abs >= 1000000000) return (value / 1000000000).toFixed(2) + 'B';
    if (abs >= 1000000) return (value / 1000000).toFixed(2) + 'M';
    if (abs >= 1000) return (value / 1000).toFixed(2) + 'K';
    if (abs < 1 && abs > 0) return value.toFixed(4);
    return value.toFixed(2);
  }

  function contrastTextColor(color) {
    if (!color || typeof color !== 'string') return '#ffffff';
    var hex = color.trim();
    if (hex.charAt(0) === '#') {
      if (hex.length === 4) {
        hex = '#' + hex.charAt(1) + hex.charAt(1) + hex.charAt(2) + hex.charAt(2) + hex.charAt(3) + hex.charAt(3);
      }
      if (hex.length === 7) {
        var r = parseInt(hex.slice(1, 3), 16);
        var g = parseInt(hex.slice(3, 5), 16);
        var b = parseInt(hex.slice(5, 7), 16);
        var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.62 ? '#111827' : '#ffffff';
      }
    }
    return '#ffffff';
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
      var previousClose = close;
      var wave = Math.sin(i / 9) * 0.28;
      var pattern = i % 4;
      var openGap = pattern === 0 ? -0.45 : pattern === 1 ? 1.55 : pattern === 2 ? -1.55 : 0.45;
      var bodyMove = pattern === 0 ? 1.25 : pattern === 1 ? -0.65 : pattern === 2 ? 0.65 : -1.25;
      var open = Math.max(5, previousClose + openGap + wave);
      close = Math.max(5, open + bodyMove);
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
    drawingTools: DRAWING_TOOLS,
    drawingToolIconSvg: drawingToolIconSvg,
    chartTypeIconSvg: chartTypeIconSvg,
    chartTypeChevronSvg: chartTypeChevronSvg,
    chartTypeLabel: chartTypeLabel,
    chartPeriods: CHART_PERIODS,
    dateRangePresets: DATE_RANGE_PRESETS,
    dateRangeButtonsHtml: dateRangeButtonsHtml,
    dateRangeLabel: dateRangeLabel,
    createDefaultDocument: createDefaultDocument,
    migrateDocument: migrateDocument,
    computeIndicatorGraph: computeIndicatorGraph,
    aggregateBars: aggregateBars,
    normalizeBars: normalizeBars,
    createDemoData: createDemoData,
    themes: {
      light: DEFAULT_LIGHT_THEME,
      dark: DEFAULT_DARK_THEME
    }
  };
}));
