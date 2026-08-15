(function() {
  'use strict';

  var STORE_KEY = '360miq-dark-mode';
  var htmlEl = document.documentElement;
  var accountState = null;
  var userChangedTheme = false;

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

  function saveTheme(dark) {
    try {
      localStorage.setItem(STORE_KEY, dark ? 'true' : 'false');
    } catch (e) {}
  }

  function accountStateFrom(eventOrState) {
    return eventOrState && eventOrState.detail
      ? eventOrState.detail
      : (eventOrState || window.__MIQ_ACCOUNT__ || {});
  }

  function persistThemePreference(dark) {
    var state = accountState || window.__MIQ_ACCOUNT__;
    if (!state || !state.loggedIn || !state.apiUrl || !state.csrfToken || typeof window.fetch !== 'function') return;

    var preferences = {};
    if (state.preferences && typeof state.preferences === 'object') {
      Object.keys(state.preferences).forEach(function(key) {
        preferences[key] = state.preferences[key];
      });
    }
    preferences.action = 'save_preferences';
    preferences.csrf_token = state.csrfToken;
    preferences.theme_mode = dark ? 'dark' : 'light';

    window.fetch(state.apiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(preferences)
    }).then(function(response) {
      if (!response.ok) throw new Error('Theme preference sync failed.');
      return response.json();
    }).then(function(payload) {
      if (payload && payload.preferences) state.preferences = payload.preferences;
    }).catch(function() {});
  }

  function isDark() {
    return htmlEl.getAttribute('data-theme') === 'dark';
  }

  function getToggleButton() {
    return document.getElementById('theme-toggle') || document.getElementById('miq360-blog-theme-toggle');
  }

  function updateToggleIcon(dark) {
    var btn = getToggleButton();
    if (!btn) return;

    var label = dark ? 'Switch to light mode' : 'Switch to dark mode';

    btn.textContent = '';
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
    userChangedTheme = true;
    saveTheme(dark);
    applyTheme(dark);
    persistThemePreference(dark);
  }

  function toggleTheme() {
    setTheme(!isDark());
  }

  function initToggle() {
    var btn = getToggleButton();
    if (!btn) return;

    btn.addEventListener('click', function(e) {
      e.preventDefault();
      toggleTheme();
    });

    updateToggleIcon(isDark());
  }

  function directChild(element, tagName, className) {
    if (!element || !element.children) return null;

    for (var i = 0; i < element.children.length; i += 1) {
      var child = element.children[i];
      var matchesTag = !tagName || child.tagName.toLowerCase() === tagName.toLowerCase();
      var matchesClass = !className || child.classList.contains(className);

      if (matchesTag && matchesClass) return child;
    }

    return null;
  }

  function applyAccountState(eventOrState) {
    var state = eventOrState && eventOrState.detail ? eventOrState.detail : (eventOrState || window.__MIQ_ACCOUNT__ || {});
    var item = document.querySelector('[data-miq-blog-account-shell]');
    var trigger = document.getElementById('miq360-blog-account-toggle');
    var menu = document.getElementById('miq360-blog-account-menu');
    if (!item || !trigger || !menu) return;

    var loggedIn = !!state.loggedIn;
    var count = Math.max(0, parseInt(state.unreadNotifications, 10) || 0);
    var label = count > 0 ? 'Account menu, ' + count + ' unread notifications' : 'Account menu';
    var loginUrl = item.getAttribute('data-login-url') || trigger.href;
    item.classList.toggle('is-authenticated', loggedIn);
    item.classList.toggle('is-guest', !loggedIn);
    trigger.classList.toggle('is-authenticated', loggedIn);
    trigger.classList.toggle('is-guest', !loggedIn);
    trigger.href = loggedIn ? '#' : loginUrl;
    trigger.title = loggedIn ? 'Account' : 'Sign in';
    trigger.setAttribute('aria-label', loggedIn ? label : 'Sign in');
    trigger.setAttribute('aria-haspopup', loggedIn ? 'true' : 'false');
    trigger.setAttribute('aria-expanded', 'false');
    item.classList.remove('is-open');
    var mobileAccount = window.matchMedia
      ? window.matchMedia('(max-width: 49.99em)').matches
      : window.innerWidth < 800;
    menu.hidden = !loggedIn || mobileAccount;

    var chevron = item.querySelector('[data-miq-account-chevron]');
    if (chevron) chevron.hidden = !loggedIn;
    var displayName = item.querySelector('[data-miq-account-display-name]');
    if (displayName) displayName.textContent = state.displayName || 'Account';

    Array.prototype.forEach.call(item.querySelectorAll('[data-miq-account-unread-badge]'), function(badge) {
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.hidden = !loggedIn || count < 1;
    });
  }

  function initNavigationMenus() {
    var mobileQuery = window.matchMedia
      ? window.matchMedia('(max-width: 49.99em)')
      : { matches: window.innerWidth < 800 };
    var menuRoot = document.getElementById('menu-primary-items') || document.querySelector('.menu-unset > ul');
    var menuContainer = document.getElementById('menu-primary-container');
    var navigationToggle = document.getElementById('toggle-navigation');
    var accountItem = document.querySelector('.miq360-account-item');
    var accountTrigger = document.getElementById('miq360-blog-account-toggle');
    var accountMenu = document.getElementById('miq360-blog-account-menu');
    var controls = [];

    function normalizedText(element) {
      var value = element && element.textContent ? element.textContent : 'Menu';
      return value.replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '') || 'Menu';
    }

    function addControl(item, trigger, menu, type, label) {
      controls.push({
        item: item,
        trigger: trigger,
        menu: menu,
        type: type,
        label: label || 'Menu'
      });
    }

    if (menuRoot) {
      var submenuParents = menuRoot.querySelectorAll('li.menu-item-has-children');

      for (var i = 0; i < submenuParents.length; i += 1) {
        var parent = submenuParents[i];
        var submenu = directChild(parent, 'ul');
        var parentLink = directChild(parent, 'a');
        var submenuToggle = directChild(parent, 'button', 'miq360-submenu-toggle');

        if (!submenu || !parentLink) continue;

        if (!submenu.id) submenu.id = 'miq360-blog-submenu-' + (i + 1);

        if (!submenuToggle) {
          submenuToggle = document.createElement('button');
          submenuToggle.type = 'button';
          submenuToggle.className = 'miq360-submenu-toggle';
          parent.insertBefore(submenuToggle, submenu);
        }

        submenuToggle.setAttribute('aria-controls', submenu.id);
        submenuToggle.setAttribute('aria-expanded', 'false');
        submenuToggle.setAttribute('aria-haspopup', 'true');
        addControl(parent, submenuToggle, submenu, 'submenu', normalizedText(parentLink));
      }
    }

    if (accountItem && accountTrigger && accountMenu) {
      addControl(accountItem, accountTrigger, accountMenu, 'account', 'Account');
    }

    if (!controls.length) return;

    htmlEl.classList.add('miq360-blog-menu-ready');

    function openClass(control) {
      return control.type === 'account' ? 'is-open' : 'miq360-mobile-submenu-open';
    }

    function isOpen(control) {
      return control.item.classList.contains(openClass(control));
    }

    function setOpen(control, open) {
      if (control.type === 'account' && !control.item.classList.contains('is-authenticated')) {
        open = false;
      }
      control.item.classList.toggle(openClass(control), open);
      control.trigger.setAttribute('aria-expanded', open ? 'true' : 'false');

      if (control.type === 'submenu') {
        control.trigger.setAttribute(
          'aria-label',
          (open ? 'Close ' : 'Open ') + control.label + ' submenu'
        );
      }

      var guestAccount = control.type === 'account' && !control.item.classList.contains('is-authenticated');
      control.menu.hidden = guestAccount || (mobileQuery.matches ? !open : false);
    }

    function closeControl(control) {
      for (var i = controls.length - 1; i >= 0; i -= 1) {
        if (controls[i] === control || control.item.contains(controls[i].item)) {
          setOpen(controls[i], false);
        }
      }
    }

    function closeOthers(current) {
      for (var i = 0; i < controls.length; i += 1) {
        var candidate = controls[i];
        var keepsAncestorOpen = candidate.item.contains(current.item);

        if (candidate !== current && !keepsAncestorOpen) closeControl(candidate);
      }
    }

    function closeAll() {
      for (var i = 0; i < controls.length; i += 1) closeControl(controls[i]);
    }

    function toggleControl(control) {
      var willOpen = !isOpen(control);

      if (willOpen) closeOthers(control);
      if (willOpen) {
        setOpen(control, true);
      } else {
        closeControl(control);
      }
    }

    for (var i = 0; i < controls.length; i += 1) {
      (function(control) {
        control.trigger.addEventListener('click', function(e) {
          if (control.type === 'submenu' && !mobileQuery.matches) return;
          if (control.type === 'account' && !control.item.classList.contains('is-authenticated')) return;

          e.preventDefault();
          e.stopPropagation();
          toggleControl(control);
        });
      })(controls[i]);
    }

    function applyViewport() {
      closeAll();
    }

    document.addEventListener('click', function(e) {
      if (mobileQuery.matches && menuContainer && menuContainer.contains(e.target)) return;

      for (var i = 0; i < controls.length; i += 1) {
        if (controls[i].item.contains(e.target)) return;
      }

      closeAll();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key !== 'Escape') return;

      var focusTarget = null;

      for (var i = 0; i < controls.length; i += 1) {
        if (isOpen(controls[i])) focusTarget = controls[i].trigger;
      }

      if (!focusTarget) return;

      closeAll();
      focusTarget.focus();
    });

    if (navigationToggle && menuContainer) {
      navigationToggle.addEventListener('click', function() {
        window.setTimeout(function() {
          if (!menuContainer.classList.contains('open')) closeAll();
        }, 0);
      });
    }

    if (mobileQuery.addEventListener) {
      mobileQuery.addEventListener('change', applyViewport);
    } else if (mobileQuery.addListener) {
      mobileQuery.addListener(applyViewport);
    }

    applyViewport();
  }

  function handleAccountState(eventOrState) {
    var state = accountStateFrom(eventOrState);
    accountState = state;
    applyAccountState(state);

    if (!state.loggedIn) return;

    var preference = state.preferences && state.preferences.theme_mode;
    if (!userChangedTheme && (preference === 'dark' || preference === 'light')) {
      var dark = preference === 'dark';
      saveTheme(dark);
      applyTheme(dark);
    } else if (!userChangedTheme && preference === 'system') {
      applyTheme(prefersDark());
    } else if (userChangedTheme) {
      persistThemePreference(isDark());
    }
  }

  function boot() {
    applyTheme(resolveDark());
    handleAccountState(window.__MIQ_ACCOUNT__ || {});
    initToggle();
    initNavigationMenus();
  }

  window.ThemeController = {
    isDark: isDark,
    toggle: toggleTheme,
    enable: function() { setTheme(true); },
    disable: function() { setTheme(false); }
  };
  window.MiqBlogAccountShell = { applyState: handleAccountState };

  window.addEventListener('miq:account-state', handleAccountState);

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
