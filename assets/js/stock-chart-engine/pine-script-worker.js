/* Web Worker entry point for the Pine-compatible runtime. */
importScripts('pine-backtest-engine.js?v=20260718.3');
importScripts('pine-script-runtime.js?v=20260718.7');
self.PineScriptRuntime.setBacktestEngine(self.PineBacktestEngine);

self.onmessage = function (event) {
  var message = event && event.data || {};
  try {
    var result = self.PineScriptRuntime.runInWorker(message);
    result.requestId = message.requestId || null;
    self.postMessage(result);
  } catch (error) {
    self.postMessage({
      ok: false,
      requestId: message.requestId || null,
      error: { message: error && error.message ? error.message : String(error) }
    });
  }
};
