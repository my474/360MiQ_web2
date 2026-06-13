/* ================================================
   360MiQ.com — Highcharts Theme
   Light / Dark theme objects + helpers
   ================================================ */

/* ---- shared export menu style helper ---- */

function exportMenuStyle(dark) {
  return {
    buttons: {
      contextButton: {
        menuStyle: {
          background: dark ? '#2a2a3e' : '#ffffff',
          border: dark ? '1px solid #3a3a4e' : '1px solid #ccc',
          color: dark ? '#cccccc' : '#333333'
        },
        menuItemStyle: {
          background: 'transparent',
          color: dark ? '#cccccc' : '#333333'
        },
        menuItemHoverStyle: {
          background: dark ? '#3a3a4e' : '#e6e6e6',
          color: dark ? '#ffffff' : '#333333'
        },
        symbolFill: dark ? '#cccccc' : '#303030',
        theme: {
          fill: dark ? '#2a2a3e' : '#ffffff',
          stroke: dark ? '#3a3a4e' : '#ccc',
          r: 0,
          states: {
            hover: {
              fill: dark ? '#3a3a4e' : '#e6e6e6',
              stroke: dark ? '#555' : '#999'
            },
            select: {
              fill: dark ? '#3a3a4e' : '#e6e6e6',
              stroke: dark ? '#555' : '#999'
            }
          }
        }
      }
    }
  };
}

/* ---- LIGHT THEME (exact audit values) ---- */

var highchartsLightTheme = {
  colors: ['#7cb5ec', '#434348', '#ec73ff', '#95a5a6', '#90ed7d',
           '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f',
           '#ff9b9b', '#91e8e1'],
  chart: {
    backgroundColor: '#FFFFFF',
    borderColor: '#777',
    style: { fontFamily: 'inherit' }
  },
  title:  { style: { color: '#333333' } },
  subtitle: { style: { color: '#555555' } },
  xAxis: {
    labels:  { style: { color: '#555555' } },
    title:   { style: { color: '#333333' } },
    lineColor: '#ccc',
    tickColor: '#ccc',
    gridLineColor: '#e6e6e6'
  },
  yAxis: {
    labels:  { style: { color: '#555555' } },
    title:   { style: { color: '#333333' } },
    lineColor: '#ccc',
    tickColor: '#ccc',
    gridLineColor: '#e6e6e6'
  },
  legend: {
    itemStyle:          { color: '#333333' },
    itemHoverStyle:     { color: '#000000' },
    itemHiddenStyle:    { color: '#ccc' }
  },
  tooltip: {
    backgroundColor: '#FFFFFF',
    style:           { color: '#333333' },
    borderColor:     '#ccc'
  },
  credits: {
    style: { color: '#999', cursor: 'pointer' }
  },
  navigator: {
    maskFill: 'rgba(0, 0, 0, 0.15)',
    handles: {
      backgroundColor: '#eee',
      borderColor: '#777'
    },
    series: {
      lineColor: 'rgba(0, 128, 255, 0.5)',
      fillOpacity: 0.05
    },
    outlineColor: '#ccc',
    xAxis: {
      gridLineColor: '#e6e6e6',
      labels: { style: { color: '#555' } }
    }
  },
  scrollbar: {
    barBackgroundColor:  '#f0f0f0',
    barBorderColor:      '#ccc',
    buttonBackgroundColor: '#f0f0f0',
    buttonArrowColor:    '#333',
    rifleColor:          '#333',
    trackBackgroundColor: '#e6e6e6',
    trackBorderColor:    '#ccc'
  },
  rangeSelector: {
    buttonTheme: {
      fill: '#f0f0f0',
      stroke: '#ccc',
      style: { color: '#333' }
    },
    inputStyle: {
      color: '#333',
      backgroundColor: '#f0f0f0'
    },
    labelStyle: {
      color: '#555'
    }
  },
  exporting: exportMenuStyle(false)
};

/* ---- DARK THEME (brighter text) ---- */

var highchartsDarkTheme = {
  colors: ['#4dc9ff', '#7b7be8', '#33ff99', '#ff8c5a', '#8fafd4',
           '#e87fff', '#50f5e0', '#ff6b61', '#ffd08a', '#b8f5ee',
           '#ff9b9b', '#91e8e1'],
  chart: {
    backgroundColor: '#2a2a3e',
    borderColor: '#555',
    style: { fontFamily: 'inherit' }
  },
  title:  { style: { color: '#e8e8e8' } },
  subtitle: { style: { color: '#b0b0b0' } },
  xAxis: {
    labels:  { style: { color: '#cccccc' } },
    title:   { style: { color: '#e8e8e8' } },
    lineColor: '#444',
    tickColor: '#444',
    gridLineColor: '#2e2e2e'
  },
  yAxis: {
    labels:  { style: { color: '#cccccc' } },
    title:   { style: { color: '#e8e8e8' } },
    lineColor: '#444',
    tickColor: '#444',
    gridLineColor: '#2e2e2e'
  },
  legend: {
    itemStyle:          { color: '#cccccc' },
    itemHoverStyle:     { color: '#ffffff' },
    itemHiddenStyle:    { color: '#555' }
  },
  tooltip: {
    backgroundColor: '#2a2a2a',
    style:           { color: '#e8e8e8' },
    borderColor:     '#444'
  },
  credits: {
    style: { color: '#666', cursor: 'pointer' }
  },
  navigator: {
    maskFill: 'rgba(0, 0, 0, 0.35)',
    handles: {
      backgroundColor: '#2a2a3e',
      borderColor: '#555'
    },
    series: {
      lineColor: 'rgba(0, 128, 255, 0.5)',
      fillOpacity: 0.05
    },
    outlineColor: '#555',
    xAxis: {
      gridLineColor: '#2e2e2e',
      labels: { style: { color: '#888' } }
    }
  },
  scrollbar: {
    barBackgroundColor:  '#2a2a3e',
    barBorderColor:      '#444',
    buttonBackgroundColor: '#2a2a3e',
    buttonArrowColor:    '#aaa',
    rifleColor:          '#aaa',
    trackBackgroundColor: '#1a1a2e',
    trackBorderColor:    '#444'
  },
  rangeSelector: {
    buttonTheme: {
      fill: '#2a2a3e',
      stroke: '#444',
      style: { color: '#cccccc' }
    },
    inputStyle: {
      color: '#cccccc',
      backgroundColor: '#2a2a3e'
    },
    labelStyle: {
      color: '#cccccc'
    }
  },
  exporting: exportMenuStyle(true)
};

/* ---- apply global baseline ---- */

function applyHighchartsTheme(isDark) {
  if (typeof Highcharts === 'undefined') return;
  Highcharts.setOptions(isDark ? highchartsDarkTheme : highchartsLightTheme);
  if (Highcharts.charts && Highcharts.charts.length) {
    var opts = getHighchartsThemeOptions();
    /* Update chart-level options only (no navigator — it would destroy
       navigator+scrollbar on StockChart sub-charts) */
    for (var i = 0; i < Highcharts.charts.length; i++) {
      var ch = Highcharts.charts[i];
      if (ch && ch.update) {
        try { ch.update(opts, true, false); } catch(e) {}
        themeNeutralSeriesColors(ch, isDark);
      }
    }
    /* Patch navigator and scrollbar in-place */
    for (var i = 0; i < Highcharts.charts.length; i++) {
      themeNavScrollbar(Highcharts.charts[i], isDark);
    }
  }
}

/* Explicit black/dark-grey series colors are readable in light mode but can
   disappear on dark backgrounds. Do the inverse when returning to light mode. */
function themeNeutralSeriesColors(ch, isDark) {
  if (!ch || !ch.series || !ch.series.length) return;
  var needsRedraw = false;

  for (var i = 0; i < ch.series.length; i++) {
    var series = ch.series[i];
    if (!series || !series.update) continue;

    var update = {};
    var changed = false;
    var color = themeNeutralColorValue(series.color || (series.options && series.options.color), isDark);

    if (color !== null) {
      update.color = color;
      changed = true;
    }

    var markerOptions = series.options && series.options.marker;
    if (markerOptions) {
      var marker = {};
      var markerChanged = false;
      var markerFill = themeNeutralColorValue(markerOptions.fillColor, isDark);
      var markerLine = themeNeutralColorValue(markerOptions.lineColor, isDark);

      if (markerFill !== null) {
        marker.fillColor = markerFill;
        markerChanged = true;
      }
      if (markerLine !== null) {
        marker.lineColor = markerLine;
        markerChanged = true;
      }
      if (markerChanged) {
        update.marker = marker;
        changed = true;
      }
    }

    if (changed) {
      try {
        series.update(update, false);
        needsRedraw = true;
      } catch(e) {}
    }
  }

  if (needsRedraw && ch.redraw) {
    try { ch.redraw(false); } catch(e) {}
  }
}

function themeNeutralColorValue(value, isDark) {
  if (typeof value === 'string') {
    return themeNeutralColorString(value, isDark);
  }

  if (value && typeof value === 'object' && value.stops && value.stops.length) {
    var copy = {};
    var changed = false;

    for (var k in value) {
      if (Object.prototype.hasOwnProperty.call(value, k)) {
        copy[k] = k === 'stops' ? [] : value[k];
      }
    }

    for (var i = 0; i < value.stops.length; i++) {
      var stop = value.stops[i];
      var nextColor = themeNeutralColorValue(stop[1], isDark);
      copy.stops[i] = [stop[0], nextColor === null ? stop[1] : nextColor];
      changed = changed || nextColor !== null;
    }

    return changed ? copy : null;
  }

  return null;
}

function themeNeutralColorString(value, isDark) {
  var parsed = parseThemeColor(value);
  if (!parsed || parsed.a <= 0.2 || !isNeutralThemeColor(parsed)) return null;

  var luminance = themeColorLuminance(parsed);
  if (isDark && luminance <= 170) {
    return luminance <= 30 ? '#e8e8e8' : '#d0d0d0';
  }
  if (!isDark && luminance >= 170) {
    return luminance >= 245 ? '#333333' : '#555555';
  }

  return null;
}

function parseThemeColor(value) {
  if (typeof value !== 'string') return null;
  var color = value.trim().toLowerCase();
  var named = {
    black: '#000000',
    white: '#ffffff',
    grey: '#808080',
    gray: '#808080',
    dimgrey: '#696969',
    dimgray: '#696969',
    darkgrey: '#a9a9a9',
    darkgray: '#a9a9a9',
    lightgrey: '#d3d3d3',
    lightgray: '#d3d3d3',
    darkslategrey: '#2f4f4f',
    darkslategray: '#2f4f4f'
  };

  if (named[color]) color = named[color];

  var hex = color.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
  if (hex) {
    var raw = hex[1];
    if (raw.length === 3) {
      raw = raw.charAt(0) + raw.charAt(0) + raw.charAt(1) + raw.charAt(1) + raw.charAt(2) + raw.charAt(2);
    }
    return {
      r: parseInt(raw.substr(0, 2), 16),
      g: parseInt(raw.substr(2, 2), 16),
      b: parseInt(raw.substr(4, 2), 16),
      a: 1
    };
  }

  var rgb = color.match(/^rgba?\(\s*([0-9.]+)\s*,\s*([0-9.]+)\s*,\s*([0-9.]+)(?:\s*,\s*([0-9.]+))?\s*\)$/);
  if (rgb) {
    return {
      r: Math.max(0, Math.min(255, parseFloat(rgb[1]))),
      g: Math.max(0, Math.min(255, parseFloat(rgb[2]))),
      b: Math.max(0, Math.min(255, parseFloat(rgb[3]))),
      a: rgb[4] === undefined ? 1 : Math.max(0, Math.min(1, parseFloat(rgb[4])))
    };
  }

  return null;
}

function isNeutralThemeColor(color) {
  return Math.max(color.r, color.g, color.b) - Math.min(color.r, color.g, color.b) <= 48;
}

function themeColorLuminance(color) {
  return (0.2126 * color.r) + (0.7152 * color.g) + (0.0722 * color.b);
}

/* Patch navigator & scrollbar SVG in-place. chart.update() with navigator
   options destroys the navigator (and its scrollbar) on StockChart sub-charts. */
function themeNavScrollbar(ch, isDark) {
  if (!ch || !ch.container) return;
  var bg   = isDark ? '#2a2a3e' : '#f0f0f0';
  var edge = isDark ? '#444' : '#ccc';
  var arr  = isDark ? '#aaa' : '#333';
  var trBg = isDark ? '#1a1a2e' : '#e6e6e6';
  var mask = isDark ? 'rgba(0,0,0,0.35)' : 'rgba(0,0,0,0.15)';
  var grid = isDark ? '#2e2e2e' : '#e6e6e6';
  var hndl = isDark ? '#555' : '#777';
  var hndlBg = isDark ? '#2a2a3e' : '#eee';
  var outl = isDark ? '#555' : '#ccc';

  try {
    var containers = [];
    var el = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (el) containers.push(el);

    /* Also walk navigator sub-chart container if it exists (StockChart) */
    try {
      var navChart = ch.navigator && ch.navigator.chart;
      if (navChart && navChart.container) {
        var navEl = typeof navChart.container === 'string'
          ? document.getElementById(navChart.container)
          : navChart.container;
        if (navEl && containers.indexOf(navEl) === -1) containers.push(navEl);
      }
    } catch(e1) {}

    for (var ci = 0; ci < containers.length; ci++) {
      patchContainer(containers[ci]);
    }

    function patchContainer(el) {
      if (!el) return;
      walk(el);
    }

    function walk(el) {
      if (!el || !el.setAttribute) return;
      var t = (el.tagName || '').toLowerCase();
      var c = (el.getAttribute('class') || '');
      var s = function(a, v) { try { el.setAttribute(a, v); } catch(e) {} };

      if (t === 'rect') {
        if (c.indexOf('outline') !== -1) {
          s('stroke', outl);
        }
        else if (c.indexOf('handle') !== -1) {
          s('fill', hndlBg); s('stroke', hndl);
        }
        else if (c.indexOf('navigator') !== -1 && c.indexOf('mask') !== -1) {
          s('fill', mask);
        }
        else if (c.indexOf('scrollbar') !== -1) {
          if (c.indexOf('track') !== -1) { s('fill', trBg); s('stroke', outl); }
          else if (c.indexOf('button') !== -1) { s('fill', bg); s('stroke', outl); }
          else { s('fill', bg); s('stroke', outl); }
        }
        else if (c.indexOf('navigator') !== -1 && c.indexOf('button') !== -1) {
          s('fill', bg); s('stroke', outl);
        }
        else if (c.indexOf('range') !== -1) {
          var parent = el.parentNode;
          if (parent) {
            var pc = (parent.getAttribute && parent.getAttribute('class')) || '';
            if (pc.indexOf('button') !== -1) { s('fill', bg); s('stroke', edge); }
          }
        }
      }
      else if (t === 'path') {
        if (c.indexOf('handle') !== -1) {
          s('fill', hndlBg); s('stroke', hndl);
        }
        else if (c.indexOf('grid') !== -1) {
          s('stroke', grid);
        }
        else if (c.indexOf('scrollbar') !== -1) {
          var d = (el.getAttribute('d') || '');
          if (d.length > 60) { s('stroke', arr); }
          else { s('fill', arr); s('stroke', arr); }
        }
      }
      else if (t === 'circle') {
        if (c.indexOf('handle') !== -1) {
          s('fill', hndlBg); s('stroke', hndl);
        }
      }
      else if (t === 'line') {
        if (c.indexOf('navigator') !== -1 && c.indexOf('outline') !== -1) {
          s('stroke', outl);
        }
      }

      var kids = el.childNodes || el.children;
      if (kids) { for (var i = 0; i < kids.length; i++) { walk(kids[i]); } }
    }
  } catch(e) {}
}

/* ---- per-instance options helper ---- */

function getHighchartsThemeOptions() {
  var dark = document.documentElement.getAttribute('data-theme') === 'dark';
  return {
    chart: {
      backgroundColor: dark ? '#2a2a3e' : '#FFFFFF',
      borderColor: dark ? '#555' : '#777'
    },
    title: {
      style: { color: dark ? '#e8e8e8' : '#333333' }
    },
    subtitle: {
      style: { color: dark ? '#b0b0b0' : '#555555' }
    },
    xAxis: {
      labels:  { style: { color: dark ? '#cccccc' : '#555555' } },
      title:   { style: { color: dark ? '#e8e8e8' : '#333333' } },
      lineColor: dark ? '#444' : '#ccc',
      tickColor: dark ? '#444' : '#ccc',
      gridLineColor: dark ? '#2e2e2e' : '#e6e6e6'
    },
    yAxis: [{
      labels:  { style: { color: dark ? '#cccccc' : '#555555' } },
      title:   { style: { color: dark ? '#e8e8e8' : '#333333' } },
      lineColor: dark ? '#444' : '#ccc',
      tickColor: dark ? '#444' : '#ccc',
      gridLineColor: dark ? '#2e2e2e' : '#e6e6e6'
    }, {
      labels:  { style: { color: dark ? '#cccccc' : '#555555' } },
      title:   { style: { color: dark ? '#e8e8e8' : '#333333' } },
      lineColor: dark ? '#444' : '#ccc',
      tickColor: dark ? '#444' : '#ccc',
      gridLineColor: dark ? '#2e2e2e' : '#e6e6e6'
    }],
    legend: {
      itemStyle:          { color: dark ? '#cccccc' : '#333333' },
      itemHoverStyle:     { color: dark ? '#ffffff' : '#000000' },
      itemHiddenStyle:    { color: dark ? '#555' : '#ccc' }
    },
    tooltip: {
      backgroundColor: dark ? '#2a2a2a' : '#FFFFFF',
      style:           { color: dark ? '#e8e8e8' : '#333333' },
      borderColor:     dark ? '#444' : '#ccc'
    },
    rangeSelector: {
      buttonTheme: {
        fill: dark ? '#2a2a3e' : '#f0f0f0',
        stroke: dark ? '#444' : '#ccc',
        style: { color: dark ? '#cccccc' : '#333' }
      },
      inputStyle: {
        color: dark ? '#cccccc' : '#333',
        backgroundColor: dark ? '#2a2a3e' : '#f0f0f0'
      },
      labelStyle: {
        color: dark ? '#cccccc' : '#555'
      }
    },
    exporting: exportMenuStyle(dark)
  };
}

/* ---- auto-apply on load if dark mode already active ---- */

(function boot() {
  if (document.documentElement.getAttribute('data-theme') === 'dark') {
    applyHighchartsTheme(true);
  }
})();
