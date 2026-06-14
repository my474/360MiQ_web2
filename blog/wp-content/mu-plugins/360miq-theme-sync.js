(function() {
  'use strict';

  var STORE_KEY = '360miq-dark-mode';
  var htmlEl = document.documentElement;

  function getSaved() {
    try {
      return localStorage.getItem(STORE_KEY);
    } catch (e) {
      return null;
    }
  }

  function prefersDark() {
    return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  }

  function resolveDark() {
    var saved = getSaved();
    if (saved === 'true') return true;
    if (saved === 'false') return false;
    return prefersDark();
  }

  function isDark() {
    return htmlEl.getAttribute('data-theme') === 'dark';
  }

  function updateToggleIcon(dark) {
    var btn = document.getElementById('miq360-blog-theme-toggle');
    if (!btn) return;

    var label = dark ? 'Switch to light mode' : 'Switch to dark mode';
    var icon = btn.querySelector('.miq360-theme-toggle-icon');

    if (icon) {
      icon.textContent = dark ? '\uD83C\uDF19' : '\u2600\uFE0F';
    }

    btn.title = label;
    btn.setAttribute('aria-label', label);
    btn.classList.add('is-ready');
  }

  function applyTheme(dark) {
    var theme = dark ? 'dark' : 'light';

    htmlEl.setAttribute('data-theme', theme);

    if (document.body) {
      document.body.classList.toggle('theme-dark', dark);
      document.body.classList.toggle('theme-light', !dark);
    }

    htmlEl.dispatchEvent(new CustomEvent('themechange', {
      detail: { theme: theme, isDark: dark }
    }));

    updateToggleIcon(dark);
  }

  function setTheme(dark) {
    try {
      localStorage.setItem(STORE_KEY, dark ? 'true' : 'false');
    } catch (e) {}

    applyTheme(dark);
  }

  function toggleTheme() {
    setTheme(!isDark());
  }

  function initToggle() {
    var btn = document.getElementById('miq360-blog-theme-toggle');
    if (!btn) return;

    btn.addEventListener('click', function(e) {
      e.preventDefault();
      toggleTheme();
    });

    updateToggleIcon(isDark());
  }

  function boot() {
    applyTheme(resolveDark());
    initToggle();
  }

  window.ThemeController = {
    isDark: isDark,
    toggle: toggleTheme,
    enable: function() { setTheme(true); },
    disable: function() { setTheme(false); }
  };

  window.addEventListener('storage', function(e) {
    if (e.key === STORE_KEY) {
      applyTheme(resolveDark());
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
