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
  var DEFAULT_MAX_BYTES = 262144;
  var MESSAGE_ID_ATTRIBUTE = 'data-chat-message-id';
  var MESSAGE_TIME_ATTRIBUTE = 'data-chat-created-at';
  var MESSAGE_TIME_CLASS = 'chatbot__message-time';
  var MESSAGE_DATE_DIVIDER_CLASS = 'chatbot__date-divider';
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
    var source = value.slice(-MAX_MESSAGES);
    var baseTimestamp = normalizeTimestamp(savedAt) || nowMilliseconds();
    var usedIds = {};

    return source.reduce(function (messages, rawMessage, index) {
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

  function fitState(value) {
    var state = normalizeState(value);
    if (!state) return null;

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
      '.chatbot__message-time{display:block;float:right;clear:both;margin:5px 0 -1px 10px;font-size:10px;line-height:1;font-weight:400;font-variant-numeric:tabular-nums;opacity:.58;white-space:nowrap;text-align:right;color:inherit}' +
      '.chatbot__date-divider{align-self:center!important;display:block!important;width:auto!important;max-width:85%;margin:4px auto 14px!important;padding:5px 9px!important;border-radius:8px;background:rgba(95,99,104,.12);color:#5f6368;font-size:11px;line-height:1.2;font-weight:600;letter-spacing:0;white-space:nowrap;text-align:center;pointer-events:none;user-select:none;transition:color .2s ease,background-color .2s ease}' +
      '[data-theme="light"] .chatbot__date-divider{background:rgba(95,99,104,.12);color:#5f6368}' +
      '[data-theme="dark"] .chatbot__date-divider{background:rgba(232,232,232,.12);color:#d0d0dc}';
    root.document.head.appendChild(style);
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
      .slice(-MAX_MESSAGES);
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
  }

  function installMessageObserver() {
    if (!root || !root.document) return;
    var start = function () {
      decorateOpenChat();
      var list = root.document.querySelector('.chatbot__messages');
      if (!list || typeof root.MutationObserver !== 'function') return;
      var observer = new root.MutationObserver(function (mutations) {
        var refreshDateDividers = false;
        mutations.forEach(function (mutation) {
          Array.prototype.slice.call(mutation.addedNodes || []).forEach(function (node) {
            if (!node || node.nodeType !== 1) return;
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
  installThemeRefresh();
  scheduleLegacyBindings();

  return {
    captureMessages: captureMessages,
    clear: clear,
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
    serializedByteLength: serializedByteLength,
    storageKey: STORAGE_KEY,
    localDateKey: localDateKey,
    utcIso: utcIso,
    utf8ByteLength: utf8ByteLength
  };
}));
