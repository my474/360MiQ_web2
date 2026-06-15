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
    gridLineWidth: 0,
    minorGridLineWidth: 0,
    gridLineColor: '#e6e6e6',
    minorGridLineColor: '#e6e6e6'
  },
  yAxis: {
    labels:  { style: { color: '#555555' } },
    title:   { style: { color: '#333333' } },
    lineColor: '#ccc',
    tickColor: '#ccc',
    gridLineColor: '#e6e6e6',
    minorGridLineColor: '#e6e6e6'
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
      gridLineWidth: 1,
      gridLineColor: '#e6e6e6',
      labels: { style: { color: '#555' } }
    },
    yAxis: {
      gridLineWidth: 0,
      minorGridLineWidth: 0
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
    gridLineWidth: 0,
    minorGridLineWidth: 0,
    gridLineColor: '#45475f',
    minorGridLineColor: '#45475f'
  },
  yAxis: {
    labels:  { style: { color: '#cccccc' } },
    title:   { style: { color: '#e8e8e8' } },
    lineColor: '#444',
    tickColor: '#444',
    gridLineColor: '#45475f',
    minorGridLineColor: '#45475f'
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
      gridLineWidth: 1,
      gridLineColor: '#45475f',
      labels: { style: { color: '#888' } }
    },
    yAxis: {
      gridLineWidth: 0,
      minorGridLineWidth: 0
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
        if (shouldSkipHighchartsThemeUpdate(ch)) {
          continue;
        }
        themeHighchartsAxisAccents(ch);
        try { ch.update(opts, true, false); } catch(e) {}
        themeNeutralSeriesColors(ch, isDark);
        themeHighchartsAxisAccents(ch);
        themeHighchartsGridLines(ch, isDark);
        themeHighchartsChartText(ch, isDark);
        themeHighchartsLegendText(ch, isDark);
		themeHighchartsPlotLines(ch, isDark);
      }
    }
    /* Patch navigator and scrollbar in-place */
    for (var i = 0; i < Highcharts.charts.length; i++) {
      themeNavScrollbar(Highcharts.charts[i], isDark);
    }
  }
}

function themeHighchartsPlotLines(chart, isDark) {
    // 1. Fetch the targeted grid line color from your active theme config
    var activeTheme = isDark ? highchartsDarkTheme : highchartsLightTheme;
    var targetColor = activeTheme.yAxis && activeTheme.yAxis.gridLineColor 
                      ? activeTheme.yAxis.gridLineColor 
                      : '#45475f'; // Safe fallback color

    // 2. Loop through every Y-axis to handle multi-axis Stock charts
    if (chart.yAxis && chart.yAxis.length) {
        for (var j = 0; j < chart.yAxis.length; j++) {
            var yAxis = chart.yAxis[j];
            
            // Check if this axis contains any user-defined plotlines
            if (yAxis.options && yAxis.options.plotLines && yAxis.options.plotLines.length) {
                
                // Map over current options to inject the updated color attribute
                var updatedPlotLines = yAxis.options.plotLines.map(function(line) {
                    line.color = targetColor;
                    return line;
                });

                // Update the axis and trigger a redraw
                yAxis.update({
                    plotLines: updatedPlotLines
                }, false); // Set to false to batch redraws efficiently
            }
        }
        // Force a singular final redraw for performance optimizations
        chart.redraw();
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

function themeHighchartsLegendText(ch, isDark) {
  if (!ch || !ch.container) return;
  var fallback = isDark ? '#e8e8e8' : '#333333';

  try {
    var legendGroup = ch.legend && ch.legend.group && ch.legend.group.element;
    if (legendGroup) {
      patchLegendElementTree(legendGroup, fallback, isDark);
    }

    var container = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (container && container.querySelectorAll) {
      var htmlLegendText = container.querySelectorAll('.highcharts-legend span, .highcharts-legend div');
      for (var i = 0; i < htmlLegendText.length; i++) {
        patchLegendHTMLElement(htmlLegendText[i], isDark);
      }
    }
  } catch(e) {}
}

function themeHighchartsChartText(ch, isDark) {
  if (!ch || !ch.container) return;
  var fallback = isDark ? '#e8e8e8' : '#333333';

  try {
    var container = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (!container || !container.querySelectorAll) return;

    var selectors = [
      '.highcharts-title',
      '.highcharts-subtitle',
      '.highcharts-axis-title',
      '.highcharts-axis-labels text',
      '.highcharts-axis-labels tspan',
      '.highcharts-no-data text',
      '.highcharts-no-data tspan',
      '.highcharts-credits',
      '.highcharts-range-label text',
      '.highcharts-range-label tspan'
    ];

    var labels = container.querySelectorAll(selectors.join(','));
    for (var i = 0; i < labels.length; i++) {
      patchChartTextElement(labels[i], fallback, isDark);
    }

    var htmlLabels = container.querySelectorAll('.highcharts-label span, .highcharts-title span, .highcharts-subtitle span');
    for (var j = 0; j < htmlLabels.length; j++) {
      patchChartHTMLElement(htmlLabels[j], fallback, isDark);
    }
  } catch(e) {}
}

function patchChartTextElement(el, fallback, isDark) {
  if (!el || !el.setAttribute) return;
  if (el.closest && el.closest('.advdec-percent-label')) return;

  var cls = el.getAttribute('class') || '';
  if (cls.indexOf('highcharts-text-outline') !== -1) return;

  var raw = el.getAttribute('fill') || (el.style && (el.style.fill || el.style.color)) || '';
  var wasPatched = el.getAttribute('data-theme-label-patched') === '1';
  var next = null;

  if (isDark) {
    next = raw ? themeNeutralColorValue(raw, true) : fallback;
  } else if (wasPatched) {
    next = raw ? themeNeutralColorValue(raw, false) : fallback;
  }

  if (next !== null) {
    el.setAttribute('fill', next);
    el.setAttribute('data-theme-label-patched', '1');
    if (el.style) {
      el.style.fill = next;
      el.style.color = next;
    }
  }
}

function patchChartHTMLElement(el, fallback, isDark) {
  if (!el || !el.style) return;
  var raw = el.style.color || el.style.fill || '';
  var wasPatched = el.getAttribute && el.getAttribute('data-theme-label-patched') === '1';
  var next = null;

  if (isDark) {
    next = raw ? themeNeutralColorValue(raw, true) : fallback;
  } else if (wasPatched) {
    next = raw ? themeNeutralColorValue(raw, false) : fallback;
  }

  if (next !== null) {
    if (el.setAttribute) {
      el.setAttribute('data-theme-label-patched', '1');
    }
    el.style.color = next;
    el.style.fill = next;
  }
}

function patchLegendElementTree(el, fallback, isDark) {
  if (!el || !el.setAttribute) return;
  var tag = (el.tagName || '').toLowerCase();
  var cls = (el.getAttribute('class') || '');

  if ((tag === 'text' || tag === 'tspan') && cls.indexOf('highcharts-text-outline') === -1) {
    var fill = el.getAttribute('fill') || (el.style && (el.style.fill || el.style.color)) || '';
    var next = fill ? themeNeutralColorValue(fill, isDark) : (tag === 'text' ? fallback : null);
    if (next !== null) {
      el.setAttribute('fill', next);
      if (el.style) {
        el.style.fill = next;
        el.style.color = next;
      }
    }
  }

  var kids = el.childNodes || el.children;
  if (kids) {
    for (var i = 0; i < kids.length; i++) {
      patchLegendElementTree(kids[i], fallback, isDark);
    }
  }
}

function patchLegendHTMLElement(el, isDark) {
  if (!el || !el.style) return;
  var raw = el.style.color || el.style.fill || '';
  var next = raw ? themeNeutralColorValue(raw, isDark) : null;

  if (next !== null) {
    el.style.color = next;
    el.style.fill = next;
  }
}

function themeHighchartsAxisAccents(ch) {
  if (!ch || !ch.yAxis || !ch.yAxis.length) return;

  for (var i = 0; i < ch.yAxis.length; i++) {
    var axis = ch.yAxis[i];
    if (!axis) continue;

    rememberAxisAccentColors(axis);

    var titleColor = axis._themeTitleAccent360 || getSingleAxisSeriesAccent(axis);
    var labelColor = axis._themeLabelAccent360;
    var lineColor = axis._themeLineAccent360 || titleColor;

    if (titleColor) {
      patchAxisTitleColor(axis, titleColor);
    }
    if (labelColor) {
      patchAxisLabelColor(axis, labelColor);
    }
    if (lineColor) {
      patchAxisLineColor(axis, lineColor);
    }
  }
}

function rememberAxisAccentColors(axis) {
  var titleColor = getAxisOptionColor(axis, 'title');
  var labelColor = getAxisOptionColor(axis, 'labels');
  var lineColor = axis.userOptions && axis.userOptions.lineColor || axis.options && axis.options.lineColor;

  if (isThemeAccentColor(titleColor)) axis._themeTitleAccent360 = titleColor;
  if (isThemeAccentColor(labelColor)) axis._themeLabelAccent360 = labelColor;
  if (isThemeAccentColor(lineColor)) axis._themeLineAccent360 = lineColor;
}

function getAxisOptionColor(axis, key) {
  return axis &&
    ((axis.userOptions && axis.userOptions[key] && axis.userOptions[key].style && axis.userOptions[key].style.color) ||
    (axis.options && axis.options[key] && axis.options[key].style && axis.options[key].style.color) ||
    '');
}

function getSingleAxisSeriesAccent(axis) {
  if (!axis || !axis.series || !axis.series.length) return null;
  var color = null;

  for (var i = 0; i < axis.series.length; i++) {
    var series = axis.series[i];
    var seriesColor = series && (series.color || (series.options && series.options.color));
    if (!isThemeAccentColor(seriesColor)) continue;
    if (color && color.toLowerCase() !== String(seriesColor).toLowerCase()) return null;
    color = seriesColor;
  }

  return color;
}

function isThemeAccentColor(value) {
  var parsed = parseThemeColor(value);
  return !!(parsed && parsed.a > 0.2 && !isNeutralThemeColor(parsed));
}

function patchAxisTitleColor(axis, color) {
  if (!axis || !color) return;
  try {
    if (axis.axisTitle && axis.axisTitle.attr) {
      axis.axisTitle.attr({ fill: color });
    }
    var el = axis.axisTitle && axis.axisTitle.element;
    if (el) patchSvgTextColor(el, color);
    ensureAxisStyleColor(axis, 'title', color);
  } catch(e) {}
}

function patchAxisLabelColor(axis, color) {
  if (!axis || !color) return;
  try {
    var group = axis.labelGroup && axis.labelGroup.element;
    if (group && group.querySelectorAll) {
      var labels = group.querySelectorAll('text,tspan');
      for (var i = 0; i < labels.length; i++) {
        patchSvgTextColor(labels[i], color);
      }
    }
    ensureAxisStyleColor(axis, 'labels', color);
  } catch(e) {}
}

function patchAxisLineColor(axis, color) {
  if (!axis || !color) return;
  try {
    axis.options = axis.options || {};
    axis.userOptions = axis.userOptions || {};
    if (axis.axisLine && axis.axisLine.attr) {
      axis.axisLine.attr({ stroke: color });
    }
    if (axis.tickPositions && axis.ticks) {
      for (var i = 0; i < axis.tickPositions.length; i++) {
        var tick = axis.ticks[axis.tickPositions[i]];
        if (tick && tick.mark && tick.mark.attr) {
          tick.mark.attr({ stroke: color });
        }
      }
    }
    axis.options.lineColor = color;
    axis.options.tickColor = color;
    axis.userOptions.lineColor = color;
    axis.userOptions.tickColor = color;
  } catch(e) {}
}

function patchSvgTextColor(el, color) {
  if (!el || !color) return;
  if (el.setAttribute) {
    el.setAttribute('fill', color);
  }
  if (el.style) {
    el.style.fill = color;
    el.style.color = color;
  }
}

function ensureAxisStyleColor(axis, key, color) {
  axis.options = axis.options || {};
  axis.userOptions = axis.userOptions || {};
  if (!axis.options[key]) axis.options[key] = {};
  if (!axis.options[key].style) axis.options[key].style = {};
  axis.options[key].style.color = color;

  if (!axis.userOptions[key]) axis.userOptions[key] = {};
  if (!axis.userOptions[key].style) axis.userOptions[key].style = {};
  axis.userOptions[key].style.color = color;
}

function themeHighchartsGridLines(ch, isDark) {
  if (!ch || shouldSkipHighchartsGridTheme(ch)) return;
  var color = isDark ? '#45475f' : '#e6e6e6';

  patchAxisGridOptions(ch.xAxis, color, 'xAxis', ch);
  patchAxisGridOptions(ch.yAxis, color, 'yAxis', ch);

  try {
    var navChart = ch.navigator && ch.navigator.chart;
    if (navChart && navChart !== ch) {
      patchAxisGridOptions(navChart.xAxis, color, 'xAxis', ch);
      patchAxisGridOptions(navChart.yAxis, color, 'yAxis', ch);
    }
  } catch(e) {}

  patchRenderedGridLines(ch, color);
}

function shouldSkipHighchartsGridTheme(ch) {
  try {
    if (isRangeGaugeChart(ch)) return true;
    var renderTo = ch && ch.renderTo;
    if (renderTo && renderTo.id === 'fscorecontainer') return true;
    var container = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (container && container.closest && container.closest('#fscorecontainer')) return true;
    var title = ch && ch.title && ch.title.textStr;
    return title === 'Piotroski F\u2011Score';
  } catch(e) {}
  return false;
}

function patchAxisGridOptions(axes, color, axisType, ch) {
  if (!axes || !axes.length) return;
  for (var i = 0; i < axes.length; i++) {
    var axis = axes[i];
    if (!axis) continue;
    var hidden = shouldHideAxisGrid(axis, axisType, ch);
    axis.options = axis.options || {};
    axis.userOptions = axis.userOptions || {};
    axis.options.gridLineColor = color;
    axis.options.minorGridLineColor = color;
    axis.userOptions.gridLineColor = color;
    axis.userOptions.minorGridLineColor = color;
    if (hidden) {
      axis.options.gridLineWidth = 0;
      axis.options.minorGridLineWidth = 0;
      axis.userOptions.gridLineWidth = 0;
      axis.userOptions.minorGridLineWidth = 0;
    } else {
      axis.options.gridLineWidth = 1;
      axis.userOptions.gridLineWidth = 1;
    }
  }
}

function shouldSkipHighchartsThemeUpdate(ch) {
  return isRangeGaugeChart(ch);
}

function isRangeGaugeChart(ch) {
  try {
    var renderTo = ch && ch.renderTo;
    if (renderTo && (renderTo.id === 'rangecontainer' || renderTo.id === 'volrangecontainer')) return true;
    var container = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (container && container.closest && container.closest('#rangecontainer, #volrangecontainer')) return true;
  } catch(e) {}
  return false;
}

function shouldHideAxisGrid(axis, axisType, ch) {
  var isNavigator = isHighchartsNavigatorAxis(axis, axisType, ch);
  return (axisType === 'xAxis' && !isNavigator) || (axisType === 'yAxis' && isNavigator);
}

function isHighchartsNavigatorAxis(axis, axisType, ch) {
  if (!axis) return false;
  var opts = axis.options || {};
  var user = axis.userOptions || {};
  var id = String(opts.id || user.id || '').toLowerCase();
  if (id.indexOf('navigator') !== -1 || !!axis.navigatorAxis) return true;

  var nav = (ch && ch.navigator) || (axis.chart && axis.chart.navigator);
  if (!nav) return false;

  var candidates = [];
  collectAxisCandidates(candidates, nav[axisType]);
  if (nav.chart) {
    collectAxisCandidates(candidates, nav.chart[axisType]);
  }

  for (var i = 0; i < candidates.length; i++) {
    if (candidates[i] === axis) return true;
  }
  return false;
}

function collectAxisCandidates(candidates, axisOrAxes) {
  if (!axisOrAxes) return;
  if (axisOrAxes.length !== undefined && typeof axisOrAxes !== 'string') {
    for (var i = 0; i < axisOrAxes.length; i++) {
      if (axisOrAxes[i]) candidates.push(axisOrAxes[i]);
    }
  } else {
    candidates.push(axisOrAxes);
  }
}

function patchRenderedGridLines(ch, color) {
  try {
    var container = typeof ch.container === 'string' ? document.getElementById(ch.container) : ch.container;
    if (!container || !container.querySelectorAll) return;

    var lines = container.querySelectorAll('.highcharts-grid-line, .highcharts-minor-grid-line');
    for (var i = 0; i < lines.length; i++) {
      var direction = getGridLineDirection(lines[i]);
      if (!direction) continue;

      var inNavigator = isRenderedGridLineInNavigator(lines[i], ch, container);
      var shouldHide = (direction === 'vertical' && !inNavigator) || (direction === 'horizontal' && inNavigator);

      if (shouldHide) {
        hideGridLineElement(lines[i]);
      } else {
        patchGridLineElement(lines[i], color);
      }
    }
  } catch(e) {}
}

function getGridLineDirection(el) {
  try {
    if (el.getBBox) {
      var box = el.getBBox();
      if (box.height > box.width + 2) return 'vertical';
      if (box.width > box.height + 2) return 'horizontal';
    }
  } catch(e) {}

  var points = parseGridLinePathPoints(el);
  if (!points) return null;
  var dx = Math.abs(points.x2 - points.x1);
  var dy = Math.abs(points.y2 - points.y1);
  if (dy > dx + 2) return 'vertical';
  if (dx > dy + 2) return 'horizontal';
  return null;
}

function parseGridLinePathPoints(el) {
  if (!el || !el.getAttribute) return null;
  var values = (el.getAttribute('d') || '').match(/-?\d+(?:\.\d+)?/g);
  if (!values || values.length < 4) return null;
  return {
    x1: parseFloat(values[0]),
    y1: parseFloat(values[1]),
    x2: parseFloat(values[2]),
    y2: parseFloat(values[3])
  };
}

function isRenderedGridLineInNavigator(el, ch, container) {
  if (hasNavigatorAncestor(el)) return true;

  try {
    if (!el.getBoundingClientRect || !container.getBoundingClientRect) return false;
    var lineRect = el.getBoundingClientRect();
    var containerRect = container.getBoundingClientRect();
    var lineMiddleY = lineRect.top + (lineRect.height / 2) - containerRect.top;
    var plotBottom = (ch.plotTop || 0) + (ch.plotHeight || 0);
    return lineMiddleY > plotBottom + 2;
  } catch(e) {}

  return false;
}

function hasNavigatorAncestor(el) {
  var node = el;
  while (node && node.getAttribute) {
    var cls = (node.getAttribute('class') || '').toLowerCase();
    if (cls.indexOf('navigator') !== -1) return true;
    node = node.parentNode;
  }
  return false;
}

function patchGridLineElement(el, color) {
  if (!el || !el.setAttribute) return;
  el.setAttribute('stroke', color);
  el.setAttribute('stroke-opacity', '1');
  el.setAttribute('opacity', '1');
  if (el.getAttribute('stroke-width') === '0') {
    el.setAttribute('stroke-width', '1');
  }
  if (el.style) {
    el.style.stroke = color;
    el.style.strokeOpacity = '1';
    el.style.opacity = '1';
  }
}

function hideGridLineElement(el) {
  if (!el || !el.setAttribute) return;
  el.setAttribute('stroke-opacity', '0');
  el.setAttribute('opacity', '0');
  if (el.style) {
    el.style.strokeOpacity = '0';
    el.style.opacity = '0';
  }
}

function bindHighchartsLegendTheme() {
  if (typeof Highcharts === 'undefined' || !Highcharts.addEvent || !Highcharts.Chart || Highcharts._legendThemeBound360) return;
  Highcharts._legendThemeBound360 = true;
  Highcharts.addEvent(Highcharts.Chart, 'render', function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    themeHighchartsAxisAccents(this);
    themeHighchartsGridLines(this, isDark);
    themeHighchartsChartText(this, isDark);
    themeHighchartsLegendText(this, isDark);
    themeNavScrollbar(this, isDark);
  });
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
  var grid = isDark ? '#45475f' : '#e6e6e6';
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
        else if (c.indexOf('outline') !== -1) {
          s('stroke', outl);
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
      gridLineWidth: 0,
      minorGridLineWidth: 0,
      gridLineColor: dark ? '#45475f' : '#e6e6e6',
      minorGridLineColor: dark ? '#45475f' : '#e6e6e6'
    },
    yAxis: [{
      labels:  { style: { color: dark ? '#cccccc' : '#555555' } },
      title:   { style: { color: dark ? '#e8e8e8' : '#333333' } },
      lineColor: dark ? '#444' : '#ccc',
      tickColor: dark ? '#444' : '#ccc',
      gridLineColor: dark ? '#45475f' : '#e6e6e6',
      minorGridLineColor: dark ? '#45475f' : '#e6e6e6'
    }, {
      labels:  { style: { color: dark ? '#cccccc' : '#555555' } },
      title:   { style: { color: dark ? '#e8e8e8' : '#333333' } },
      lineColor: dark ? '#444' : '#ccc',
      tickColor: dark ? '#444' : '#ccc',
      gridLineColor: dark ? '#45475f' : '#e6e6e6',
      minorGridLineColor: dark ? '#45475f' : '#e6e6e6'
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
  bindHighchartsLegendTheme();
  if (document.documentElement.getAttribute('data-theme') === 'dark') {
    applyHighchartsTheme(true);
  }
})();