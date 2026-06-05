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
    Highcharts.charts.forEach(function(ch) {
      if (ch && ch.update) {
        try { ch.update(opts, true, false); } catch(e) {}
        /* Scrollbar: chart.update() doesn't re-render already-created scrollbar
           SVG elements on StockChart sub-charts (only the first chart works).
           Directly update each chart's scrollbar colors via its SVG group. */
        themeScrollbar(ch, isDark);
      }
    });
  }
}

/* Directly update scrollbar SVG element colors — chart.update() doesn't
   propagate scrollbar options to already-rendered scrollbars on StockChart
   sub-charts. We patch SVG fill/stroke in-place so event handlers survive. */
function themeScrollbar(ch, isDark) {
  var sb = ch.scrollbar;
  if (!sb || !sb.group) return;

  /* Update options so the internal state is correct */
  sb.update({
    barBackgroundColor:   isDark ? '#2a2a3e' : '#f0f0f0',
    barBorderColor:       isDark ? '#444' : '#ccc',
    buttonArrowColor:     isDark ? '#aaa' : '#333',
    rifleColor:           isDark ? '#aaa' : '#333',
    trackBackgroundColor: isDark ? '#1a1a2e' : '#e6e6e6',
    trackBorderColor:     isDark ? '#444' : '#ccc'
  });

  /* Patch in-place — don't recreate elements, that kills interactivity.
     Highcharts Scrollbar SVG structure (children of sb.group):
       index 0: rifle path (the ||| thumb grip)
       index 1: track group (contains track rect)
       index 2: bar rect
       index 3: button-left group (arrow path inside)
       index 4: button-right group (arrow path inside) */
  var g = sb.group.element;
  if (!g) return;
  var c = g.childNodes || g.children;
  if (!c) return;

  var set = function(el, attr, val) {
    try { if (el && el.setAttribute) el.setAttribute(attr, val); } catch(e) {}
  };

  for (var i = 0; i < c.length; i++) {
    var node = c[i];
    var tag = node.tagName ? node.tagName.toLowerCase() : '';

    if (tag === 'path') {
      /* Rifle grip (|||) — update stroke, keep fill unchanged */
      set(node, 'stroke', isDark ? '#aaa' : '#333');
    }
    else if (tag === 'rect') {
      /* Could be bar or track rect — check position in group.
         The bar rect is usually the top-level rect, track is nested. */
      set(node, 'fill',   isDark ? '#2a2a3e' : '#f0f0f0');
      set(node, 'stroke', isDark ? '#444' : '#ccc');
    }
    else if (tag === 'g') {
      /* Track group or button group — find rects and paths inside */
      var kids = node.childNodes || node.children;
      if (kids) {
        for (var k = 0; k < kids.length; k++) {
          var kid = kids[k];
          var ktag = kid.tagName ? kid.tagName.toLowerCase() : '';
          if (ktag === 'rect') {
            set(kid, 'fill',   isDark ? '#1a1a2e' : '#e6e6e6');
            set(kid, 'stroke', isDark ? '#444' : '#ccc');
          } else if (ktag === 'path') {
            set(kid, 'fill',   isDark ? '#aaa' : '#333');
            set(kid, 'stroke', isDark ? '#aaa' : '#333');
          }
        }
      }
    }
  }
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
    navigator: {
      maskFill: dark ? 'rgba(0, 0, 0, 0.35)' : 'rgba(0, 0, 0, 0.15)',
      handles: {
        backgroundColor: dark ? '#2a2a3e' : '#eee',
        borderColor: dark ? '#555' : '#777'
      },
      outlineColor: dark ? '#555' : '#ccc',
      xAxis: {
        gridLineColor: dark ? '#2e2e2e' : '#e6e6e6',
        labels: { style: { color: dark ? '#888' : '#555' } }
      }
    },
    /* scrollbar NOT included — chart.update() may destroy it on StockChart
       sub-charts. It is patched in-place by themeScrollbar() instead. */
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
