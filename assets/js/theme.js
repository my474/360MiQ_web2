/* ================================================
   360MiQ.com Theme Controller
   ================================================ */

(function() {
  'use strict';

  var STORE_KEY = '360miq-dark-mode';
  var htmlEl = document.documentElement;

  /* ---- helpers ---- */

  function isDark() {
    return htmlEl.getAttribute('data-theme') === 'dark';
  }

  function getSaved() {
    return localStorage.getItem(STORE_KEY);
  }

  /* ---- apply / toggle ---- */

  function applyTheme(dark) {
    var theme = dark ? 'dark' : 'light';

    htmlEl.setAttribute('data-theme', theme);

    /* swap Bootstrap navbar classes */
    var nav = document.querySelector('.navbar');
    if (nav) {
      if (dark) {
        nav.classList.remove('bg-white', 'navbar-light');
        nav.classList.add('bg-dark', 'navbar-dark');
      } else {
        nav.classList.remove('bg-dark', 'navbar-dark');
        nav.classList.add('bg-white', 'navbar-light');
      }
    }

    /* update Highcharts global baseline */
    if (typeof Highcharts !== 'undefined' && typeof window.applyHighchartsTheme === 'function') {
      window.applyHighchartsTheme(dark);
    }

    /* fire custom event — chart files listen for this */
    htmlEl.dispatchEvent(new CustomEvent('themechange', {
      detail: { theme: theme, isDark: dark }
    }));

    updateToggleIcon(dark);
  }

  function setTheme(dark) {
    localStorage.setItem(STORE_KEY, dark ? 'true' : 'false');
    applyTheme(dark);
  }

  function toggleTheme() {
    setTheme(!isDark());
  }

  /* ---- toggle icon ---- */

  function updateToggleIcon(dark) {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.textContent = dark ? '☂' : '☀';  /* ☂ crescent moon  /  ☀ sun */
    btn.title = dark ? 'Switch to light mode' : 'Switch to dark mode';
  }

  /* ---- init toggle button ---- */

  function initToggle() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      toggleTheme();
    });
    updateToggleIcon(isDark());
  }

  /* ---- expose public API ---- */

  window.ThemeController = {
    isDark: isDark,
    toggle: toggleTheme,
    enable: function() { setTheme(true); },
    disable: function() { setTheme(false); }
  };

  /* ---- boot ---- */

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initToggle);
  } else {
    initToggle();
  }

})();
