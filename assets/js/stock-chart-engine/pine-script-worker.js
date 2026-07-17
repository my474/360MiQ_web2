/* Web Worker entry point for the Pine-compatible runtime. */
importScripts('pine-script-runtime.js');

self.onmessage = function (event) {
  var message = event && event.data || {};
  try {
    var result = self.PineScriptRuntime.runInWorker(message);
    self.postMessage(result);
  } catch (error) {
    self.postMessage({
      ok: false,
      error: { message: error && error.message ? error.message : String(error) }
    });
  }
};
