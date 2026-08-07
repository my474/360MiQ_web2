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

  function normalizeState(value) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
      return null;
    }

    var messages = Array.isArray(value.messages)
      ? value.messages.filter(function (message) { return typeof message === 'string'; }).slice(-MAX_MESSAGES)
      : [];
    var stockchatDict = copyLastEntries(value.stockchatDict, MAX_MESSAGES);
    var state = {
      messages: messages,
      stockchatDict: stockchatDict,
      checkboxStates: copyLastEntries(value.checkboxStates, 400),
      count: Number(value.count) >= 0 ? Number(value.count) : inferNextCount(stockchatDict),
      savedAt: Number(value.savedAt) > 0 ? Number(value.savedAt) : 0
    };

    return state;
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
      }).then(parseResponse).catch(function () {
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

  function remoteTimestamp(body) {
    if (body && body.history && Number(body.history.savedAt) > 0) {
      return Number(body.history.savedAt);
    }
    if (body && body.updated_at) {
      var parsed = Date.parse(String(body.updated_at).replace(' ', 'T') + 'Z');
      if (!isNaN(parsed)) return parsed;
    }
    return 0;
  }

  function applyRemoteStateToOpenChat(state) {
    if (!root || !root.document || !state) return;
    var messages = root.document.querySelector('.chatbot__messages');
    if (!messages) return;

    var existing = messages.querySelectorAll('li.is-user, li.is-ai');
    if (existing.length > 1) return;

    messages.innerHTML = state.messages.join('');
    if (root.stockchatDict && typeof root.stockchatDict === 'object') {
      Object.keys(root.stockchatDict).forEach(function (key) { delete root.stockchatDict[key]; });
      Object.assign(root.stockchatDict, state.stockchatDict || {});
    }
    if (root.checkboxStates && typeof root.checkboxStates === 'object') {
      Object.keys(root.checkboxStates).forEach(function (key) { delete root.checkboxStates[key]; });
      Object.assign(root.checkboxStates, state.checkboxStates || {});
    }
    if (typeof state.count === 'number') root.count = state.count;
    Object.keys(state.checkboxStates || {}).forEach(function (id) {
      var checkbox = root.document.querySelector('.bulk-checkbox[id="' + id.replace(/"/g, '\\"') + '"]');
      if (checkbox) checkbox.checked = !!state.checkboxStates[id];
    });
    if (typeof root.scrollDown === 'function') root.scrollDown();
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
      var localTimestamp = localState && Number(localState.savedAt) > 0 ? Number(localState.savedAt) : 0;
      var serverTimestamp = remoteTimestamp(body);

      if (remoteState && (!localState || localTimestamp <= serverTimestamp || !serverTimestamp)) {
        cancelPendingRemoteSave();
        writeLocalState(remoteState);
        applyRemoteStateToOpenChat(remoteState);
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
          if (!bounded.savedAt) bounded.savedAt = Date.now();
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
    bounded.savedAt = Date.now();
    bounded = fitState(bounded);
    writeLocalState(bounded);
    queueRemoteSave(bounded);
    return bounded;
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

  installStorageGuard();
  readyPromise = hydrateFromAccount();

  return {
    clear: clear,
    fitState: fitState,
    getState: function () { return memoryState || readLocalState(); },
    maxBytes: maxBytes,
    maxMessages: MAX_MESSAGES,
    normalizeState: normalizeState,
    ready: function () { return readyPromise; },
    save: save,
    serializedByteLength: serializedByteLength,
    storageKey: STORAGE_KEY,
    utf8ByteLength: utf8ByteLength
  };
}));
