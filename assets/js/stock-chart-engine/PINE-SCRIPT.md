# Pine-Compatible Scripts

The Advanced Chart includes a restricted, browser-safe Pine-compatible runtime. It is designed for custom indicators and does not execute arbitrary JavaScript.

## Supported in the client runtime

- `//@version` directives
- `indicator()` and `study()` declarations
- `open`, `high`, `low`, `close`, `volume`, `bar_index`, `time`, `hl2`, `hlc3`, and `ohlc4`
- assignments, `var` declarations, tuples, arithmetic, comparisons, boolean operators, ternaries, and user functions (`name(args) => expression` or an indented multi-statement body)
- `if`/`else`, bounded `for ... to ...` loops, `return`, and `break`
- `plot()`, `hline()`, `plotshape()`, `plotchar()`, `bgcolor()`, `fill()`, and `alertcondition()`
- safe declarative `label.new()`, `line.new()`, and `box.new()` outputs for chart annotations
- `request.security(symbol, timeframe, expression)` with timestamp alignment across missing or non-trading dates. The chart requests missing symbols through its configured comparison-data loader.
- `input.int()`, `input.float()`, `input.bool()`, `input.string()`, `input.source()`, and `input.color()`
- `ta.sma()`, `ta.ema()`, `ta.rma()`, `ta.wma()`, `ta.rsi()`, `ta.macd()`, `ta.highest()`, `ta.lowest()`, `ta.sum()`, `ta.stdev()`, `ta.change()`, `ta.roc()`, `ta.crossover()`, and `ta.crossunder()`
- `math.abs()`, `math.max()`, `math.min()`, `math.pow()`, `math.sqrt()`, `math.log()`, `math.log10()`, `math.round()`, `math.floor()`, and `math.ceil()`
- common colors and plot styles
- `color.new()`, `color.rgb()`, common `shape.*`, `location.*`, and `size.*` values

## Example

```pine
//@version=5
indicator("RSI Average", overlay=false)
length = input.int(14, "RSI Length", min=1, max=200)
rsiValue = ta.rsi(close, length)
plot(rsiValue, title="RSI", color=color.orange)
plot(ta.sma(rsiValue, 5), title="RSI SMA", color=color.blue)
hline(70, title="Overbought")
hline(30, title="Oversold")
```

Open the `Pine Script` button in the chart toolbar to create or edit a script. Existing Pine indicators can also be edited by clicking their legend. `Ctrl/Cmd+Enter` runs the editor and `Escape` closes it.

## Deliberate limitations

Strategies, orders, broker integration, mutable drawing handles, and unrestricted drawing APIs are not enabled. The supported label/line/box calls are rendered as isolated script outputs and do not mutate the user drawing store. `alertcondition()` is exposed as chart metadata; it does not send notifications by itself. `request.security()` uses data supplied by the embedding chart and does not fetch network data from inside a script. Unsupported syntax returns a line and column diagnostic instead of being silently interpreted. User-function recursion is capped at 32 calls, loops at 1,000 iterations, plots/security requests are bounded, and evaluation is capped by an operation budget.

The runtime caches a small number of compiled scripts and never evaluates user text as JavaScript. Missing security symbols are reported in the result as `securityRequests`, allowing the chart host to load them and recompute the script.

The runtime has a dedicated Web Worker entry point at `pine-script-worker.js`.

## Execution model

When the browser supports Web Workers, Advanced Chart now evaluates each Pine Script indicator in `pine-script-worker.js` after the synchronous preview is rendered. The synchronous result keeps the chart responsive while the worker starts, and the worker result replaces it when it returns. A request revision and script fingerprint prevent results from an older symbol, timeframe, or edit from being applied to the current chart. Browsers that cannot create a worker, pages opened from an unsupported origin, and automated test environments continue to use the synchronous runtime automatically.

The worker URL can be overridden by passing `pineWorkerUrl` to the chart constructor. Set `pineWorker: false` only for an embedding that deliberately requires synchronous evaluation.
