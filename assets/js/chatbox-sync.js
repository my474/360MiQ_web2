(function (root, factory) {
  var api = factory(root);

  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }

  if (root) {
    root.MiqChatboxSync = api;
  }
}(typeof globalThis !== 'undefined' ? globalThis : this, function (root) {
  'use strict';

  var STORAGE_KEY = 'chatbotState';
  var MAX_MESSAGES = 40;
  var MAX_MESSAGE_CANDIDATES = MAX_MESSAGES + 1;
  var DEFAULT_MAX_BYTES = 262144;
  var MESSAGE_ID_ATTRIBUTE = 'data-chat-message-id';
  var MESSAGE_TIME_ATTRIBUTE = 'data-chat-created-at';
  var MESSAGE_TIME_CLASS = 'chatbot__message-time';
  var MESSAGE_DATE_DIVIDER_CLASS = 'chatbot__date-divider';
  var SCROLL_DATE_PILL_CLASS = 'chatbot__scroll-date-pill';
  var SCROLL_DATE_PILL_LABEL_CLASS = 'chatbot__scroll-date-pill__label';
  var SCROLL_DATE_PILL_VISIBLE_CLASS = 'is-visible';
  var SCROLL_DATE_PILL_TOP_GAP = 14;
  var SCROLL_DATE_PILL_HIDE_DELAY = 700;
  var SCROLL_DATE_PILL_OPEN_SUPPRESSION = 180;
  var USER_SCROLL_INTENT_WINDOW = 900;
  var USER_SCROLL_INERTIA_WINDOW = 250;
  var CHAT_CHART_SELECTOR = '[id^="chatchart"]';
  var CHAT_CHART_NODE_SELECTOR = '[id^="chatchart"],.highcharts-container,.highcharts-root';
  var CHAT_CHART_THEME_ATTRIBUTE = 'data-miq-chat-chart-theme';
  var config = root && root.__MIQ_CHATBOX_SYNC__
    ? root.__MIQ_CHATBOX_SYNC__
    : (root && root.__MIQ_ACCOUNT__ ? root.__MIQ_ACCOUNT__ : {});
  var configuredMaxBytes = Number(config.chatHistoryMaxBytes || DEFAULT_MAX_BYTES);
  var maxBytes = isFinite(configuredMaxBytes)
    ? Math.max(32768, configuredMaxBytes)
    : DEFAULT_MAX_BYTES;
  var storage = null;
  var localWriteInProgress = false;
  var memoryState = null;
  var pendingRemoteState = null;
  var remoteSaveTimer = null;
  var readyPromise;
  var fallbackIdSequence = 0;
  var chatChartThemeScheduled = false;

  function nowMilliseconds() {
    return typeof Date.now === 'function' ? Date.now() : new Date().getTime();
  }

  function normalizeTimestamp(value) {
    var timestamp = Number(value);
    return isFinite(timestamp) && timestamp > 0 ? Math.floor(timestamp) : 0;
  }

  function utcIso(value) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    try {
      return new Date(timestamp).toISOString();
    } catch (error) {
      return '';
    }
  }

  function formatLocalTimestamp(value, locale, timeZone) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    var date = new Date(timestamp);
    try {
      if (typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
        var options = {
          hour: 'numeric',
          minute: '2-digit'
        };
        if (timeZone) options.timeZone = timeZone;
        return new Intl.DateTimeFormat(locale, options).format(date);
      }
      return date.toLocaleTimeString(locale, { hour: 'numeric', minute: '2-digit' });
    } catch (error) {
      return String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
    }
  }

  function formatLocalDate(value, locale, timeZone) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    var date = new Date(timestamp);
    try {
      if (typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
        var options = {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        };
        if (timeZone) options.timeZone = timeZone;
        return new Intl.DateTimeFormat(locale, options).format(date);
      }
      return date.toLocaleDateString(locale);
    } catch (error) {
      return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    }
  }

  function formatLocalDateTime(value) {
    var localDate = formatLocalDate(value);
    var localTime = formatLocalTimestamp(value);
    return localDate && localTime ? localDate + ', ' + localTime : (localDate || localTime);
  }

  function localDateKey(value) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    var date = new Date(timestamp);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
  }

  function localDayNumber(value) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return 0;
    var date = new Date(timestamp);
    return Math.floor(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()) / 86400000);
  }

  function formatLocalWeekday(value) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    var date = new Date(timestamp);
    try {
      if (typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
        return new Intl.DateTimeFormat(undefined, { weekday: 'long' }).format(date);
      }
      return date.toLocaleDateString(undefined, { weekday: 'long' });
    } catch (error) {
      return formatLocalDate(timestamp);
    }
  }

  function formatLocalDateLabel(value, nowValue) {
    var timestamp = normalizeTimestamp(value);
    if (!timestamp) return '';
    var currentTimestamp = normalizeTimestamp(nowValue) || nowMilliseconds();
    var messageKey = localDateKey(timestamp);
    var todayKey = localDateKey(currentTimestamp);
    if (messageKey === todayKey) return 'Today';

    var currentDate = new Date(currentTimestamp);
    var yesterday = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
    yesterday.setDate(yesterday.getDate() - 1);
    if (messageKey === localDateKey(yesterday.getTime())) return 'Yesterday';
    var dayDifference = localDayNumber(currentTimestamp) - localDayNumber(timestamp);
    if (dayDifference >= 2 && dayDifference <= 7) return formatLocalWeekday(timestamp);
    return formatLocalDate(timestamp);
  }

  function dateDividerPlan(messages, nowValue) {
    if (!Array.isArray(messages)) return [];
    var previousDividerKey = '';
    return messages.reduce(function (dividers, message, index) {
      var messageObject = message && typeof message === 'object' ? message : {};
      var html = typeof message === 'string' ? message : messageObject.html;
      var id = cleanMessageId(messageObject.id) || cleanMessageId(readHtmlAttribute(html, MESSAGE_ID_ATTRIBUTE));
      var estimated = isEstimatedTimestampMessage(id);
      var createdAt = message && typeof message === 'object'
        ? normalizeTimestamp(messageObject.createdAt)
        : normalizeTimestamp(readHtmlAttribute(message, MESSAGE_TIME_ATTRIBUTE));
      var dateKey = localDateKey(createdAt);
      var dividerKey = estimated ? 'earlier' : dateKey;
      if (dividerKey && dividerKey !== previousDividerKey) {
        dividers.push({
          beforeIndex: index,
          dateKey: dividerKey,
          estimated: estimated,
          label: estimated ? 'Earlier' : formatLocalDateLabel(createdAt, nowValue)
        });
      }
      if (dividerKey) previousDividerKey = dividerKey;
      return dividers;
    }, []);
  }

  function scrollDateLabel(dividers, viewportTop) {
    if (!Array.isArray(dividers) || !dividers.length) return '';
    var threshold = Number(viewportTop);
    if (!isFinite(threshold)) threshold = 0;
    var firstLabel = '';
    var currentLabel = '';
    var currentTop = -Infinity;

    dividers.forEach(function (divider) {
      var label = divider && divider.label != null ? String(divider.label).trim() : '';
      var top = divider ? Number(divider.top) : NaN;
      if (!label || !isFinite(top)) return;
      if (!firstLabel) firstLabel = label;
      if (top <= threshold && top >= currentTop) {
        currentTop = top;
        currentLabel = label;
      }
    });

    return currentLabel || firstLabel;
  }

  function chatThemeName(value) {
    var requested = String(value || '').toLowerCase();
    if (requested === 'dark' || requested === 'light') return requested;
    if (root && root.document && root.document.documentElement) {
      var attribute = root.document.documentElement.getAttribute('data-theme');
      if (attribute === 'dark' || attribute === 'light') return attribute;
    }
    if (root && root.ThemeController && typeof root.ThemeController.isDark === 'function') {
      return root.ThemeController.isDark() ? 'dark' : 'light';
    }
    return 'light';
  }

  function chatChartPalette(themeName) {
    var name = chatThemeName(themeName);
    if (name === 'dark') {
      return {
        name: 'dark',
        label: '#b8bac8',
        grid: '#3b3d52',
        axisLine: '#5a5d73',
        background: 'transparent'
      };
    }
    return {
      name: 'light',
      label: '#7a7a7a',
      grid: '#e0e2e7',
      axisLine: '#b8bbc4',
      background: 'transparent'
    };
  }

  function chatChartThemeOptions(palette) {
    var axis = function (includeGrid) {
      var options = {
        lineColor: palette.axisLine,
        tickColor: palette.axisLine,
        labels: { style: { color: palette.label } },
        title: { style: { color: palette.label } }
      };
      if (includeGrid) options.gridLineColor = palette.grid;
      return options;
    };
    return {
      chart: { backgroundColor: palette.background },
      xAxis: axis(false),
      yAxis: axis(true)
    };
  }

  function setChatChartPaint(container, selector, attribute, color) {
    if (!container || !container.querySelectorAll) return;
    Array.prototype.slice.call(container.querySelectorAll(selector)).forEach(function (node) {
      if (node.setAttribute) node.setAttribute(attribute, color);
      if (node.style) {
        node.style[attribute] = color;
        if (attribute === 'fill') node.style.color = color;
      }
    });
  }

  function applyChatChartSvgTheme(container, palette) {
    if (!container) return;
    setChatChartPaint(container, '.highcharts-background', 'fill', palette.background);
    setChatChartPaint(container, '.highcharts-grid-line', 'stroke', palette.grid);
    setChatChartPaint(container, '.highcharts-axis-line,.highcharts-tick', 'stroke', palette.axisLine);
    setChatChartPaint(container, '.highcharts-axis-labels text,.highcharts-axis-title', 'fill', palette.label);
    if (container.setAttribute) container.setAttribute(CHAT_CHART_THEME_ATTRIBUTE, palette.name);
  }

  function applyChatChartTheme(themeName) {
    if (!root || !root.document) return 0;
    var messages = root.document.querySelector('.chatbot__messages');
    if (!messages || !messages.querySelectorAll) return 0;
    var palette = chatChartPalette(themeName);
    var containers = Array.prototype.slice.call(messages.querySelectorAll(CHAT_CHART_SELECTOR));
    var charts = root.Highcharts && Array.isArray(root.Highcharts.charts)
      ? root.Highcharts.charts
      : [];

    charts.forEach(function (chart) {
      if (!chart) return;
      var target = chart.renderTo || (chart.container ? chart.container.parentNode : null);
      if (!target || !messages.contains || !messages.contains(target)) return;
      var appliedTheme = target.getAttribute ? target.getAttribute(CHAT_CHART_THEME_ATTRIBUTE) : '';
      if (appliedTheme === palette.name) return;
      if (target.setAttribute) target.setAttribute(CHAT_CHART_THEME_ATTRIBUTE, palette.name);
      try {
        if (typeof chart.update === 'function') chart.update(chatChartThemeOptions(palette), false, false);
        if (typeof chart.redraw === 'function') chart.redraw(false);
      } catch (error) {
        // The SVG fallback below also handles restored or partially destroyed charts.
      }
    });

    containers.forEach(function (container) {
      applyChatChartSvgTheme(container, palette);
    });
    return containers.length;
  }

  function scheduleChatChartTheme() {
    if (chatChartThemeScheduled) return;
    chatChartThemeScheduled = true;
    var apply = function () {
      chatChartThemeScheduled = false;
      applyChatChartTheme();
      if (root && typeof root.requestAnimationFrame === 'function') {
        root.requestAnimationFrame(function () {
          applyChatChartTheme();
        });
      }
    };
    if (root && typeof root.requestAnimationFrame === 'function') {
      root.requestAnimationFrame(apply);
    } else {
      setTimeout(apply, 0);
    }
  }

  function utf8ByteLength(value) {
    var text = String(value || '');
    if (typeof TextEncoder !== 'undefined') {
      return new TextEncoder().encode(text).length;
    }
    try {
      return unescape(encodeURIComponent(text)).length;
    } catch (error) {
      return text.length;
    }
  }

  function cloneObject(value) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) return {};
    return value;
  }

  function copyLastEntries(value, limit) {
    var source = cloneObject(value);
    var keys = Object.keys(source).slice(-limit);
    var result = {};
    keys.forEach(function (key) {
      result[key] = source[key];
    });
    return result;
  }

  function inferNextCount(dict) {
    var nextCount = 0;
    Object.keys(dict || {}).forEach(function (key) {
      var match = String(key).match(/_(\d+)$/);
      if (match) nextCount = Math.max(nextCount, Number(match[1]) + 1);
    });
    return nextCount;
  }

  function hashString(value) {
    var text = String(value || '');
    var hash = 2166136261;
    for (var index = 0; index < text.length; index += 1) {
      hash ^= text.charCodeAt(index);
      hash = Math.imul ? Math.imul(hash, 16777619) : (hash * 16777619);
    }
    return (hash >>> 0).toString(36);
  }

  function cleanMessageId(value) {
    var id = typeof value === 'string' ? value.trim() : '';
    return /^[A-Za-z0-9._:-]{1,96}$/.test(id) ? id : '';
  }

  function isEstimatedTimestampMessage(value) {
    return cleanMessageId(value).indexOf('legacy-') === 0;
  }

  function createMessageId() {
    if (root && root.crypto && typeof root.crypto.randomUUID === 'function') {
      return 'chat-' + root.crypto.randomUUID();
    }
    fallbackIdSequence += 1;
    return 'chat-' + nowMilliseconds().toString(36) + '-' + fallbackIdSequence.toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function readHtmlAttribute(html, attribute) {
    var pattern = new RegExp('\\s' + attribute + '\\s*=\\s*(["\\\'])(.*?)\\1', 'i');
    var match = String(html || '').match(pattern);
    return match ? match[2] : '';
  }

  function removeHtmlAttribute(html, attribute) {
    var pattern = new RegExp('\\s' + attribute + '\\s*=\\s*(["\\\']).*?\\1', 'ig');
    return String(html || '').replace(pattern, '');
  }

  function stripRenderedTimestamp(html) {
    return String(html || '')
      .replace(/<time\b[^>]*class\s*=\s*(["'])[^"']*\bchatbot__message-time\b[^"']*\1[^>]*>[\s\S]*?<\/time>/gi, '')
      .replace(/\sdata-chat-local-time\s*=\s*(["']).*?\1/gi, '');
  }

  function canonicalMessageHtml(html) {
    return removeHtmlAttribute(
      removeHtmlAttribute(stripRenderedTimestamp(html), MESSAGE_ID_ATTRIBUTE),
      MESSAGE_TIME_ATTRIBUTE
    );
  }

  function roleFromHtml(html) {
    return /<li\b[^>]*class\s*=\s*(["'])[^"']*\bis-user\b/i.test(String(html || ''))
      ? 'user'
      : 'assistant';
  }

  function messageHtmlWithMetadata(html, id, createdAt) {
    var cleanHtml = stripRenderedTimestamp(html);
    var cleanId = cleanMessageId(id);
    var timestamp = normalizeTimestamp(createdAt);

    if (root && root.document && typeof root.document.createElement === 'function') {
      try {
        var template = root.document.createElement('template');
        template.innerHTML = cleanHtml.trim();
        var element = template.content && template.content.querySelector
          ? template.content.querySelector('li.is-user, li.is-ai')
          : null;
        if (element) {
          Array.prototype.slice.call(element.querySelectorAll('.' + MESSAGE_TIME_CLASS)).forEach(function (time) {
            if (time.parentNode) time.parentNode.removeChild(time);
          });
          element.setAttribute(MESSAGE_ID_ATTRIBUTE, cleanId);
          element.setAttribute(MESSAGE_TIME_ATTRIBUTE, String(timestamp));
          return element.outerHTML;
        }
      } catch (error) {
        // The string fallback below supports older browsers and test runners.
      }
    }

    cleanHtml = removeHtmlAttribute(removeHtmlAttribute(cleanHtml, MESSAGE_ID_ATTRIBUTE), MESSAGE_TIME_ATTRIBUTE);
    return cleanHtml.replace(
      /<li\b/i,
      '<li ' + MESSAGE_ID_ATTRIBUTE + '="' + cleanId + '" ' + MESSAGE_TIME_ATTRIBUTE + '="' + timestamp + '"'
    );
  }

  function findExistingMessage(html, usedIds) {
    if (!memoryState || !Array.isArray(memoryState.messages)) return null;
    var canonical = canonicalMessageHtml(html);
    for (var index = 0; index < memoryState.messages.length; index += 1) {
      var existing = memoryState.messages[index];
      if (!existing || usedIds[existing.id]) continue;
      if (canonicalMessageHtml(existing.html) === canonical) return existing;
    }
    return null;
  }

  function normalizeMessages(value, savedAt) {
    if (!Array.isArray(value)) return [];
    // Keep one overflow candidate so an oversized newest message can be
    // rejected before applying the 40-message count limit.
    var source = value.slice(-MAX_MESSAGE_CANDIDATES);
    var baseTimestamp = normalizeTimestamp(savedAt) || nowMilliseconds();
    var usedIds = {};

    var messages = source.reduce(function (messages, rawMessage, index) {
      var messageObject = rawMessage && typeof rawMessage === 'object' && !Array.isArray(rawMessage)
        ? rawMessage
        : {};
      var html = typeof rawMessage === 'string' ? rawMessage : messageObject.html;
      if (typeof html !== 'string' || html.trim() === '') return messages;

      var existing = findExistingMessage(html, usedIds);
      var id = cleanMessageId(messageObject.id)
        || cleanMessageId(readHtmlAttribute(html, MESSAGE_ID_ATTRIBUTE))
        || (existing ? existing.id : '');
      var createdAt = normalizeTimestamp(messageObject.createdAt)
        || normalizeTimestamp(readHtmlAttribute(html, MESSAGE_TIME_ATTRIBUTE))
        || (existing ? existing.createdAt : 0)
        || Math.max(1, baseTimestamp - ((source.length - index - 1) * 1000));
      var role = messageObject.role === 'user' || messageObject.role === 'assistant'
        ? messageObject.role
        : roleFromHtml(html);

      if (!id) {
        id = 'legacy-' + hashString(canonicalMessageHtml(html) + '|' + createdAt + '|' + index);
      }
      if (usedIds[id]) {
        id = id.slice(0, 82) + '-' + hashString(html + '|' + createdAt + '|' + index);
      }
      usedIds[id] = true;

      messages.push({
        id: id,
        role: role,
        html: messageHtmlWithMetadata(html, id, createdAt),
        createdAt: createdAt
      });
      return messages;
    }, []);

    return messages.filter(function (message) {
      return messageFitsByItself(message, savedAt);
    }).slice(-MAX_MESSAGES);
  }

  function normalizeState(value) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
      return null;
    }

    var savedAt = normalizeTimestamp(value.savedAt);
    var stockchatDict = copyLastEntries(value.stockchatDict, MAX_MESSAGES);
    return {
      messages: normalizeMessages(value.messages, savedAt),
      stockchatDict: stockchatDict,
      checkboxStates: copyLastEntries(value.checkboxStates, 400),
      count: Number(value.count) >= 0 ? Number(value.count) : inferNextCount(stockchatDict),
      savedAt: savedAt
    };
  }

  function serializeState(state) {
    return JSON.stringify(state);
  }

  function serializedByteLength(state) {
    return utf8ByteLength(serializeState(state));
  }

  function messageFitsByItself(message, savedAt) {
    return serializedByteLength({
      messages: [message],
      stockchatDict: {},
      checkboxStates: {},
      count: 0,
      savedAt: savedAt
    }) <= maxBytes;
  }

  function fitState(value) {
    var state = normalizeState(value);
    if (!state) return null;

    // Never let one unsaveable message evict the otherwise valid history.
    // Messages are atomic: omit an oversized one instead of slicing its HTML.
    state.messages = state.messages.filter(function (message) {
      return messageFitsByItself(message, state.savedAt);
    });

    while (serializedByteLength(state) > maxBytes) {
      if (state.messages.length > 0) {
        state.messages.shift();
        continue;
      }

      var dictKeys = Object.keys(state.stockchatDict);
      if (dictKeys.length > 0) {
        delete state.stockchatDict[dictKeys[0]];
        continue;
      }

      var checkboxKeys = Object.keys(state.checkboxStates);
      if (checkboxKeys.length > 0) {
        delete state.checkboxStates[checkboxKeys[0]];
        continue;
      }

      break;
    }

    return state;
  }

  function readLocalState() {
    if (!storage) return memoryState;

    try {
      var raw = storage.getItem(STORAGE_KEY);
      if (!raw) return null;
      var state = fitState(JSON.parse(raw));
      memoryState = state;
      if (state && serializeState(state) !== raw) writeLocalState(state);
      return state;
    } catch (error) {
      return null;
    }
  }

  function writeLocalState(state) {
    var bounded = fitState(state);
    if (!bounded) return null;

    memoryState = bounded;
    if (!storage) return bounded;

    try {
      localWriteInProgress = true;
      storage.setItem(STORAGE_KEY, serializeState(bounded));
    } catch (error) {
      // Chat history is optional; an unavailable or full localStorage must not
      // prevent the chat from working or account sync from being attempted.
    } finally {
      localWriteInProgress = false;
    }

    return bounded;
  }

  function parseResponse(response) {
    return response.text().then(function (text) {
      var body = {};
      try { body = text ? JSON.parse(text) : {}; } catch (error) { body = {}; }
      if (!response.ok || body.error) {
        var requestError = new Error(body.error || 'The chat history request failed.');
        requestError.status = response.status;
        throw requestError;
      }
      return body;
    });
  }

  function apiUrl() {
    var configured = String(config.apiUrl || '/account_api.php');
    if (!root || !root.location || typeof URL === 'undefined') return configured;
    try {
      return new URL(configured, root.location.href).toString();
    } catch (error) {
      return configured;
    }
  }

  function isLoggedIn() {
    return !!config.loggedIn && !!config.csrfToken;
  }

  function queueRemoteSave(state) {
    if (!isLoggedIn()) return;
    pendingRemoteState = fitState(state);
    if (remoteSaveTimer) return;

    remoteSaveTimer = setTimeout(function () {
      remoteSaveTimer = null;
      var nextState = pendingRemoteState;
      pendingRemoteState = null;
      if (!nextState) return;

      fetch(apiUrl(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'save_chat_history',
          csrf_token: config.csrfToken,
          history: nextState
        })
      }).then(parseResponse).then(function (body) {
        if (body.csrf_token) config.csrfToken = body.csrf_token;
      }).catch(function () {
        // Local history remains the source of truth until the next retry.
      });
    }, 500);
  }

  function cancelPendingRemoteSave() {
    pendingRemoteState = null;
    if (remoteSaveTimer) {
      clearTimeout(remoteSaveTimer);
      remoteSaveTimer = null;
    }
  }

  function meaningfulState(state) {
    return !!state && (
      (Array.isArray(state.messages) && state.messages.length > 0) ||
      Object.keys(state.stockchatDict || {}).length > 0
    );
  }

  function messagesNeedMigration(state) {
    if (!state || !Array.isArray(state.messages)) return false;
    return state.messages.some(function (message) {
      return !message || typeof message !== 'object' || Array.isArray(message) ||
        !cleanMessageId(message.id) || !normalizeTimestamp(message.createdAt) ||
        typeof message.html !== 'string' ||
        (message.role !== 'user' && message.role !== 'assistant');
    });
  }

  function remoteTimestamp(body) {
    if (body && body.history && normalizeTimestamp(body.history.savedAt)) {
      return normalizeTimestamp(body.history.savedAt);
    }
    if (body && body.updated_at) {
      var parsed = Date.parse(String(body.updated_at).replace(' ', 'T') + 'Z');
      if (!isNaN(parsed)) return parsed;
    }
    return 0;
  }

  function ensureTimestampStyle() {
    if (!root || !root.document || !root.document.head) return;
    if (root.document.getElementById('miq-chat-message-time-style')) return;
    var style = root.document.createElement('style');
    style.id = 'miq-chat-message-time-style';
    style.textContent =
      '.chatbot__message-time{display:block;float:none;clear:both;width:100%;margin:4px 0 -3px;font-size:10px;line-height:1;font-weight:400;font-variant-numeric:tabular-nums;opacity:.58;white-space:nowrap;text-align:right;color:inherit}' +
      '.chatbot__date-divider{align-self:center!important;display:block!important;width:auto!important;max-width:85%;margin:4px auto 14px!important;padding:5px 9px!important;border-radius:8px;background:rgba(95,99,104,.12);color:#5f6368;font-size:11px;line-height:1.2;font-weight:600;letter-spacing:0;white-space:nowrap;text-align:center;pointer-events:none;user-select:none;transition:color .2s ease,background-color .2s ease}' +
      '.chatbot__scroll-date-pill{position:absolute;top:68px;left:0;right:0;z-index:4;display:flex;justify-content:center;height:0;overflow:visible;opacity:0;pointer-events:none;transform:translateY(-4px);transition:opacity .18s ease,transform .18s ease;will-change:opacity,transform}' +
      '.chatbot__scroll-date-pill.is-visible{opacity:1;transform:translateY(0)}' +
      '.chatbot__scroll-date-pill .chatbot__date-divider{margin:0!important;background:rgba(58,58,76,.96);color:#e2e2ea}' +
      '.chatbot--closed .chatbot__scroll-date-pill{display:none}' +
      '[data-theme="light"] .chatbot__date-divider{background:rgba(95,99,104,.12);color:#5f6368}' +
      '[data-theme="dark"] .chatbot__date-divider{background:rgba(232,232,232,.12);color:#d0d0dc}' +
      '[data-theme="light"] .chatbot__scroll-date-pill .chatbot__date-divider{background:rgba(58,58,76,.96);color:#e2e2ea}' +
      '[data-theme="dark"] .chatbot__scroll-date-pill .chatbot__date-divider{background:rgba(244,246,248,.96);color:#5f6368}' +
      '@media (prefers-reduced-motion:reduce){.chatbot__scroll-date-pill{transition:none}}';
    root.document.head.appendChild(style);
  }

  function ensureScrollDatePill(scroller) {
    if (!scroller || !root || !root.document || typeof root.document.createElement !== 'function') return null;
    var chatbot = scroller.closest ? scroller.closest('.chatbot') : scroller.parentNode;
    if (!chatbot || !chatbot.querySelector || !chatbot.insertBefore) return null;
    var existing = chatbot.querySelector('.' + SCROLL_DATE_PILL_CLASS);
    if (existing) return existing;

    var pill = root.document.createElement('div');
    var label = root.document.createElement('span');
    pill.className = SCROLL_DATE_PILL_CLASS;
    pill.setAttribute('aria-hidden', 'true');
    label.className = MESSAGE_DATE_DIVIDER_CLASS + ' ' + SCROLL_DATE_PILL_LABEL_CLASS;
    pill.appendChild(label);
    chatbot.insertBefore(pill, scroller);
    return pill;
  }

  function positionScrollDatePill(scroller, pill) {
    if (!scroller || !pill || !pill.parentNode || !scroller.getBoundingClientRect || !pill.parentNode.getBoundingClientRect) return;
    var scrollerRect = scroller.getBoundingClientRect();
    var parentRect = pill.parentNode.getBoundingClientRect();
    if (!scrollerRect.width || !scrollerRect.height) return;
    pill.style.top = Math.round(scrollerRect.top - parentRect.top + SCROLL_DATE_PILL_TOP_GAP) + 'px';
    pill.style.left = Math.round(scrollerRect.left - parentRect.left) + 'px';
    pill.style.right = 'auto';
    pill.style.width = Math.round(scrollerRect.width) + 'px';
  }

  function updateScrollDatePill(scroller, pill) {
    if (!scroller || !pill || !scroller.querySelectorAll || !scroller.getBoundingClientRect) return '';
    var viewportTop = scroller.getBoundingClientRect().top + SCROLL_DATE_PILL_TOP_GAP + 4;
    var dividers = Array.prototype.slice.call(scroller.querySelectorAll('.chatbot__messages .' + MESSAGE_DATE_DIVIDER_CLASS));
    var label = scrollDateLabel(dividers.map(function (divider) {
      return {
        label: divider.textContent,
        top: divider.getBoundingClientRect().top
      };
    }), viewportTop);
    var labelElement = pill.querySelector('.' + SCROLL_DATE_PILL_LABEL_CLASS);
    if (labelElement && labelElement.textContent !== label) labelElement.textContent = label;
    return label;
  }

  function isScrollKey(event) {
    var key = event && event.key;
    return key === 'ArrowUp' || key === 'ArrowDown' || key === 'PageUp' ||
      key === 'PageDown' || key === 'Home' || key === 'End' || key === ' ';
  }

  function isEditableTarget(target) {
    if (!target || !target.tagName) return false;
    var tagName = String(target.tagName).toLowerCase();
    return tagName === 'input' || tagName === 'textarea' || tagName === 'select' || !!target.isContentEditable;
  }

  function chatboxForScroller(scroller) {
    if (!scroller) return null;
    return scroller.closest ? scroller.closest('.chatbot') : scroller.parentNode;
  }

  function isOpenChatScroller(scroller) {
    var chatbot = chatboxForScroller(scroller);
    return !!scroller && (!chatbot || !chatbot.classList || !chatbot.classList.contains('chatbot--closed'));
  }

  function scrollChatToBottom(scroller) {
    if (!scroller) return 0;
    if (scroller.style) scroller.style.scrollBehavior = 'auto';
    scroller.scrollTop = Math.max(0, Number(scroller.scrollHeight) || 0);
    return Number(scroller.scrollTop) || 0;
  }

  function scrollOpenChatToBottom(scroller) {
    var element = scroller || (root && root.document ? root.document.querySelector('.chatbot__message-window') : null);
    if (!element) return;
    element.__miqSuppressScrollPillUntil = nowMilliseconds() + SCROLL_DATE_PILL_OPEN_SUPPRESSION;
    var pillContainer = chatboxForScroller(element);
    var pill = pillContainer && pillContainer.querySelector
      ? pillContainer.querySelector('.' + SCROLL_DATE_PILL_CLASS)
      : null;
    if (pill && pill.classList) pill.classList.remove(SCROLL_DATE_PILL_VISIBLE_CLASS);
    scrollChatToBottom(element);
    if (root && typeof root.requestAnimationFrame === 'function') {
      root.requestAnimationFrame(function () {
        scrollChatToBottom(element);
        root.requestAnimationFrame(function () {
          scrollChatToBottom(element);
        });
      });
    }
  }

  function installChatOpenScroll() {
    if (!root || !root.document) return;
    var start = function () {
      var scroller = root.document.querySelector('.chatbot__message-window');
      var chatbot = chatboxForScroller(scroller);
      if (!scroller || !chatbot || !chatbot.classList || chatbot.__miqOpenScrollInstalled) return;
      chatbot.__miqOpenScrollInstalled = true;
      var wasClosed = chatbot.classList.contains('chatbot--closed');
      var handleState = function () {
        var isClosed = chatbot.classList.contains('chatbot--closed');
        if (wasClosed && !isClosed) scrollOpenChatToBottom(scroller);
        wasClosed = isClosed;
      };

      if (typeof root.MutationObserver === 'function') {
        var observer = new root.MutationObserver(handleState);
        observer.observe(chatbot, { attributes: true, attributeFilter: ['class'] });
      } else {
        var header = chatbot.querySelector ? chatbot.querySelector('.chatbot__header') : null;
        if (header && header.addEventListener) {
          header.addEventListener('click', function () {
            setTimeout(handleState, 0);
          });
        }
      }
      if (!wasClosed) scrollOpenChatToBottom(scroller);
    };

    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
      start();
    }
  }

  function installScrollDatePill() {
    if (!root || !root.document) return;
    var start = function () {
      var scroller = root.document.querySelector('.chatbot__message-window');
      if (!scroller || !scroller.addEventListener || scroller.__miqScrollDatePillInstalled) return;
      ensureTimestampStyle();
      var pill = ensureScrollDatePill(scroller);
      if (!pill) return;
      scroller.__miqScrollDatePillInstalled = true;

      var hideTimer = null;
      var userScrollIntentUntil = 0;
      var pointerActive = false;
      var touchActive = false;

      var markUserScrollIntent = function () {
        userScrollIntentUntil = Math.max(userScrollIntentUntil, nowMilliseconds() + USER_SCROLL_INTENT_WINDOW);
      };
      var hidePill = function () {
        pill.classList.remove(SCROLL_DATE_PILL_VISIBLE_CLASS);
      };
      var scheduleHide = function () {
        if (hideTimer) clearTimeout(hideTimer);
        hideTimer = setTimeout(hidePill, SCROLL_DATE_PILL_HIDE_DELAY);
      };
      var onUserScroll = function () {
        var now = nowMilliseconds();
        if (now <= Number(scroller.__miqSuppressScrollPillUntil || 0)) {
          hidePill();
          return;
        }
        if (!pointerActive && !touchActive && now > userScrollIntentUntil) return;
        userScrollIntentUntil = Math.max(userScrollIntentUntil, now + USER_SCROLL_INERTIA_WINDOW);
        positionScrollDatePill(scroller, pill);
        if (!updateScrollDatePill(scroller, pill)) {
          hidePill();
          return;
        }
        pill.classList.add(SCROLL_DATE_PILL_VISIBLE_CLASS);
        scheduleHide();
      };
      var onPointerDown = function () {
        pointerActive = true;
      };
      var onPointerEnd = function () {
        pointerActive = false;
      };
      var onTouchStart = function () {
        touchActive = true;
        markUserScrollIntent();
      };
      var onTouchEnd = function () {
        touchActive = false;
      };
      var onScrollKey = function (event) {
        if (isScrollKey(event) && !isEditableTarget(event.target)) markUserScrollIntent();
      };

      scroller.addEventListener('wheel', markUserScrollIntent, { passive: true });
      scroller.addEventListener('touchstart', onTouchStart, { passive: true });
      scroller.addEventListener('touchmove', markUserScrollIntent, { passive: true });
      scroller.addEventListener('touchend', onTouchEnd, { passive: true });
      scroller.addEventListener('touchcancel', onTouchEnd, { passive: true });
      scroller.addEventListener('pointerdown', onPointerDown, { passive: true });
      scroller.addEventListener('keydown', onScrollKey);
      scroller.addEventListener('scroll', onUserScroll, { passive: true });
      if (root.addEventListener) {
        root.addEventListener('pointerup', onPointerEnd, { passive: true });
        root.addEventListener('pointercancel', onPointerEnd, { passive: true });
        root.addEventListener('resize', function () {
          if (pill.classList.contains(SCROLL_DATE_PILL_VISIBLE_CLASS)) positionScrollDatePill(scroller, pill);
        });
      }
    };

    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
      start();
    }
  }

  function renderDateDividers(target, nowValue) {
    var element = target || (root && root.document ? root.document.querySelector('.chatbot__messages') : null);
    if (!element || !element.querySelectorAll) return [];

    Array.prototype.slice.call(element.querySelectorAll('.' + MESSAGE_DATE_DIVIDER_CLASS)).forEach(function (divider) {
      if (divider.parentNode) divider.parentNode.removeChild(divider);
    });

    var renderedMessages = Array.prototype.slice.call(element.querySelectorAll('li.is-user, li.is-ai'));
    var plan = dateDividerPlan(renderedMessages.map(function (message) {
      return {
        id: message.getAttribute(MESSAGE_ID_ATTRIBUTE),
        createdAt: message.getAttribute(MESSAGE_TIME_ATTRIBUTE)
      };
    }), nowValue);

    if (!root || !root.document || typeof root.document.createElement !== 'function') return plan;
    plan.forEach(function (entry) {
      var message = renderedMessages[entry.beforeIndex];
      if (!message || !message.parentNode || typeof message.parentNode.insertBefore !== 'function') return;
      var divider = root.document.createElement('li');
      divider.className = MESSAGE_DATE_DIVIDER_CLASS + (entry.estimated ? ' chatbot__date-divider--estimated' : '');
      divider.setAttribute('data-chat-date', entry.dateKey);
      divider.setAttribute('role', 'separator');
      divider.setAttribute('aria-label', 'Messages from ' + entry.label);
      divider.textContent = entry.label;
      message.parentNode.insertBefore(divider, message);
    });
    return plan;
  }

  function messageMetadataFromElement(element, fallbackTimestamp) {
    var id = cleanMessageId(element.getAttribute(MESSAGE_ID_ATTRIBUTE));
    var createdAt = normalizeTimestamp(element.getAttribute(MESSAGE_TIME_ATTRIBUTE))
      || normalizeTimestamp(fallbackTimestamp)
      || nowMilliseconds();
    if (!id) id = createMessageId();
    element.setAttribute(MESSAGE_ID_ATTRIBUTE, id);
    element.setAttribute(MESSAGE_TIME_ATTRIBUTE, String(createdAt));
    return {
      id: id,
      role: element.classList && element.classList.contains('is-user') ? 'user' : 'assistant',
      createdAt: createdAt,
      timestampEstimated: isEstimatedTimestampMessage(id)
    };
  }

  function decorateMessageElement(element, fallbackTimestamp) {
    if (!element || !element.getAttribute || !element.querySelector) return null;
    var metadata = messageMetadataFromElement(element, fallbackTimestamp);
    var bubble = element.querySelector('.chatbot__message');
    if (!bubble) return metadata;

    var timestamps = Array.prototype.slice.call(element.querySelectorAll('.' + MESSAGE_TIME_CLASS));
    if (metadata.timestampEstimated) {
      timestamps.forEach(function (timestamp) {
        if (timestamp.parentNode) timestamp.parentNode.removeChild(timestamp);
      });
      return metadata;
    }
    var time = timestamps.shift();
    timestamps.forEach(function (duplicate) {
      if (duplicate.parentNode) duplicate.parentNode.removeChild(duplicate);
    });
    if (!time) {
      time = root.document.createElement('time');
      time.className = MESSAGE_TIME_CLASS;
      bubble.appendChild(time);
    } else if (time.parentNode !== bubble) {
      bubble.appendChild(time);
    }

    time.dateTime = utcIso(metadata.createdAt);
    time.title = formatLocalDateTime(metadata.createdAt);
    time.setAttribute('aria-label', formatLocalDateTime(metadata.createdAt));
    time.textContent = formatLocalTimestamp(metadata.createdAt);
    return metadata;
  }

  function storedMessageFromElement(element, fallbackTimestamp) {
    var metadata = decorateMessageElement(element, fallbackTimestamp);
    if (!metadata) return null;
    var clone = element.cloneNode(true);
    Array.prototype.slice.call(clone.querySelectorAll('.' + MESSAGE_TIME_CLASS)).forEach(function (time) {
      if (time.parentNode) time.parentNode.removeChild(time);
    });
    return {
      id: metadata.id,
      role: metadata.role,
      html: messageHtmlWithMetadata(clone.outerHTML, metadata.id, metadata.createdAt),
      createdAt: metadata.createdAt
    };
  }

  function captureMessages() {
    if (!root || !root.document) return [];
    var elements = Array.prototype.slice.call(root.document.querySelectorAll('.chatbot__messages li'))
      .filter(function (message) {
        return message.classList.contains('is-user') || message.classList.contains('is-ai');
      })
      .slice(-MAX_MESSAGE_CANDIDATES);
    var baseTimestamp = nowMilliseconds() - Math.max(0, elements.length - 1);
    return elements.map(function (element, index) {
      return storedMessageFromElement(element, baseTimestamp + index);
    }).filter(Boolean);
  }

  function renderMessages(messages, target) {
    var element = target || (root && root.document ? root.document.querySelector('.chatbot__messages') : null);
    if (!element) return [];
    ensureTimestampStyle();
    var normalized = normalizeMessages(messages, memoryState ? memoryState.savedAt : 0);
    element.innerHTML = normalized.map(function (message) { return message.html; }).join('');
    var rendered = Array.prototype.slice.call(element.querySelectorAll('li.is-user, li.is-ai'));
    rendered.forEach(function (message, index) {
      decorateMessageElement(message, normalized[index] ? normalized[index].createdAt : 0);
    });
    renderDateDividers(element);
    return normalized;
  }

  function replaceObject(target, source) {
    if (!target || typeof target !== 'object') return;
    Object.keys(target).forEach(function (key) { delete target[key]; });
    Object.assign(target, source || {});
  }

  function applyStateToOpenChat(state, force) {
    if (!root || !root.document || !state) return;
    var messages = root.document.querySelector('.chatbot__messages');
    if (!messages) return;

    var existing = messages.querySelectorAll('li.is-user, li.is-ai');
    if (!force && existing.length > 1) return;

    renderMessages(state.messages, messages);
    replaceObject(root.stockchatDict, state.stockchatDict);
    replaceObject(root.checkboxStates, state.checkboxStates);
    if (typeof state.count === 'number') root.count = state.count;
    Object.keys(state.checkboxStates || {}).forEach(function (id) {
      var checkbox = root.document.querySelector('.bulk-checkbox[id="' + id.replace(/"/g, '\\"') + '"]');
      if (checkbox) checkbox.checked = !!state.checkboxStates[id];
    });
    if (typeof root.scrollDown === 'function') root.scrollDown();
    var scroller = messages.closest
      ? messages.closest('.chatbot__message-window')
      : root.document.querySelector('.chatbot__message-window');
    if (isOpenChatScroller(scroller)) scrollOpenChatToBottom(scroller);
  }

  function applyRemoteStateToOpenChat(state) {
    applyStateToOpenChat(state, false);
  }

  function hydrateFromAccount() {
    if (!isLoggedIn() || typeof fetch !== 'function') return Promise.resolve();

    return fetch(apiUrl() + (apiUrl().indexOf('?') === -1 ? '?' : '&') + 'action=get_chat_history', {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    }).then(parseResponse).then(function (body) {
      if (body.csrf_token) config.csrfToken = body.csrf_token;

      var localState = readLocalState();
      var remoteState = fitState(body.history);
      var localTimestamp = localState ? normalizeTimestamp(localState.savedAt) : 0;
      var serverTimestamp = remoteTimestamp(body);

      if (remoteState && (!localState || localTimestamp <= serverTimestamp || !serverTimestamp)) {
        var migrateRemoteMessages = messagesNeedMigration(body.history);
        cancelPendingRemoteSave();
        writeLocalState(remoteState);
        applyRemoteStateToOpenChat(remoteState);
        if (migrateRemoteMessages) queueRemoteSave(remoteState);
      } else if (localState && meaningfulState(localState)) {
        queueRemoteSave(localState);
      }
    }).catch(function () {
      // Account sync is best effort; local history remains available offline.
    });
  }

  function installStorageGuard() {
    if (!root || !root.localStorage || !root.Storage || !root.Storage.prototype) return;
    storage = root.localStorage;
    var prototype = root.Storage.prototype;
    if (prototype.__miqChatboxSyncInstalled) return;

    var originalSetItem = prototype.setItem;
    prototype.setItem = function (key, value) {
      if (key !== STORAGE_KEY || localWriteInProgress) {
        return originalSetItem.call(this, key, value);
      }

      try {
        var bounded = fitState(JSON.parse(String(value)));
        if (bounded) {
          if (!bounded.savedAt) bounded.savedAt = nowMilliseconds();
          bounded = fitState(bounded);
          value = serializeState(bounded);
          memoryState = bounded;
          var result = originalSetItem.call(this, key, value);
          queueRemoteSave(bounded);
          return result;
        }
      } catch (error) {
        // Preserve the existing behavior for malformed values.
      }
      try {
        return originalSetItem.call(this, key, value);
      } catch (error) {
        return undefined;
      }
    };
    try {
      Object.defineProperty(prototype, '__miqChatboxSyncInstalled', { value: true });
    } catch (error) {
      prototype.__miqChatboxSyncInstalled = true;
    }
  }

  function save(state) {
    var bounded = fitState(state);
    if (!bounded) return null;
    bounded.savedAt = nowMilliseconds();
    bounded = fitState(bounded);
    writeLocalState(bounded);
    queueRemoteSave(bounded);
    return bounded;
  }

  function saveOpenChatState() {
    if (!root || !root.document) return null;
    var checkboxStates = {};
    Array.prototype.slice.call(root.document.querySelectorAll('.bulk-checkbox[id]')).forEach(function (checkbox) {
      checkboxStates[checkbox.id] = !!checkbox.checked;
    });
    replaceObject(root.checkboxStates, checkboxStates);
    return save({
      messages: captureMessages(),
      stockchatDict: cloneObject(root.stockchatDict),
      checkboxStates: checkboxStates,
      count: Number(root.count) >= 0 ? Number(root.count) : 0
    });
  }

  function restoreOpenChatState() {
    return Promise.resolve(readyPromise).then(function () {
      var state = memoryState || readLocalState();
      if (state) applyStateToOpenChat(state, true);
      return state;
    });
  }

  function clear() {
    cancelPendingRemoteSave();
    if (storage) {
      try { storage.removeItem(STORAGE_KEY); } catch (error) { /* optional storage */ }
    }
    memoryState = null;
    if (!isLoggedIn() || typeof fetch !== 'function') return Promise.resolve();
    return fetch(apiUrl(), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'clear_chat_history',
        csrf_token: config.csrfToken
      })
    }).then(parseResponse).catch(function () { /* optional remote clear */ });
  }

  function decorateOpenChat() {
    if (!root || !root.document) return;
    ensureTimestampStyle();
    var list = root.document.querySelector('.chatbot__messages');
    if (!list) return;
    var messages = Array.prototype.slice.call(list.querySelectorAll('li.is-user, li.is-ai'));
    var baseTimestamp = nowMilliseconds() - Math.max(0, messages.length - 1);
    messages.forEach(function (message, index) {
      decorateMessageElement(message, baseTimestamp + index);
    });
    renderDateDividers(list);
    scheduleChatChartTheme();
  }

  function installMessageObserver() {
    if (!root || !root.document) return;
    var start = function () {
      decorateOpenChat();
      var list = root.document.querySelector('.chatbot__messages');
      if (!list || typeof root.MutationObserver !== 'function') return;
      var observer = new root.MutationObserver(function (mutations) {
        var refreshDateDividers = false;
        var refreshChatCharts = false;
        mutations.forEach(function (mutation) {
          Array.prototype.slice.call(mutation.addedNodes || []).forEach(function (node) {
            if (!node || node.nodeType !== 1) return;
            if ((node.matches && node.matches(CHAT_CHART_NODE_SELECTOR)) ||
                (node.querySelector && node.querySelector(CHAT_CHART_NODE_SELECTOR))) {
              refreshChatCharts = true;
            }
            if (node.matches && node.matches('li.is-user, li.is-ai')) {
              decorateMessageElement(node);
              refreshDateDividers = true;
            }
            if (node.querySelectorAll) {
              Array.prototype.slice.call(node.querySelectorAll('li.is-user, li.is-ai')).forEach(function (message) {
                decorateMessageElement(message);
                refreshDateDividers = true;
              });
            }
          });
          Array.prototype.slice.call(mutation.removedNodes || []).forEach(function (node) {
            if (!node || node.nodeType !== 1) return;
            if (node.matches && node.matches('li.is-user, li.is-ai')) refreshDateDividers = true;
            if (node.querySelector && node.querySelector('li.is-user, li.is-ai')) refreshDateDividers = true;
          });
        });
        if (refreshDateDividers) renderDateDividers(list);
        if (refreshChatCharts) scheduleChatChartTheme();
      });
      observer.observe(list, { childList: true, subtree: true });
    };

    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
      start();
    }
  }

  function installLegacyBindings() {
    if (!root || !root.document || !root.document.querySelector('.chatbot__messages')) return;
    root.saveChatState = saveOpenChatState;
    root.restoreChatState = restoreOpenChatState;
    root.clearChatState = clear;
  }

  function scheduleLegacyBindings() {
    if (!root || !root.document) return;
    setTimeout(installLegacyBindings, 0);
    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', installLegacyBindings, { once: true });
    } else {
      installLegacyBindings();
    }
  }

  function installThemeRefresh() {
    if (!root || !root.document || !root.document.documentElement || !root.document.documentElement.addEventListener) return;
    root.document.documentElement.addEventListener('themechange', decorateOpenChat);
  }

  installStorageGuard();
  readyPromise = hydrateFromAccount();
  installMessageObserver();
  installScrollDatePill();
  installChatOpenScroll();
  installThemeRefresh();
  scheduleLegacyBindings();

  return {
    captureMessages: captureMessages,
    clear: clear,
    applyChatChartTheme: applyChatChartTheme,
    chatChartPalette: chatChartPalette,
    decorateOpenChat: decorateOpenChat,
    dateDividerPlan: dateDividerPlan,
    fitState: fitState,
    formatLocalDateLabel: formatLocalDateLabel,
    formatLocalTimestamp: formatLocalTimestamp,
    getState: function () { return memoryState || readLocalState(); },
    maxBytes: maxBytes,
    maxMessages: MAX_MESSAGES,
    isEstimatedTimestampMessage: isEstimatedTimestampMessage,
    normalizeState: normalizeState,
    ready: function () { return readyPromise; },
    renderDateDividers: renderDateDividers,
    renderMessages: renderMessages,
    restoreOpenChatState: restoreOpenChatState,
    save: save,
    saveOpenChatState: saveOpenChatState,
    scrollChatToBottom: scrollChatToBottom,
    scrollDateLabel: scrollDateLabel,
    scrollOpenChatToBottom: scrollOpenChatToBottom,
    serializedByteLength: serializedByteLength,
    storageKey: STORAGE_KEY,
    localDateKey: localDateKey,
    utcIso: utcIso,
    utf8ByteLength: utf8ByteLength
  };
}));
