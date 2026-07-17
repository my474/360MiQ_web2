# Pine-Compatible Scripts

The Advanced Chart includes a restricted, browser-safe Pine-compatible runtime. It is designed for custom indicators and does not execute arbitrary JavaScript.

## Supported in the first release

- `//@version` directives
- `indicator()` and `study()` declarations
- `open`, `high`, `low`, `close`, `volume`, `hl2`, `hlc3`, and `ohlc4`
- assignments, `var` declarations, tuples, arithmetic, comparisons, boolean operators, and ternaries
- `plot()` and `hline()`
- `input.int()`, `input.float()`, `input.bool()`, `input.string()`, `input.source()`, and `input.color()`
- `ta.sma()`, `ta.ema()`, `ta.rma()`, `ta.wma()`, `ta.rsi()`, `ta.macd()`, `ta.highest()`, `ta.lowest()`, `ta.sum()`, `ta.stdev()`, `ta.change()`, `ta.roc()`, `ta.crossover()`, and `ta.crossunder()`
- `math.abs()`, `math.max()`, `math.min()`, `math.pow()`, `math.sqrt()`, `math.log()`, `math.log10()`, `math.round()`, `math.floor()`, and `math.ceil()`
- common colors and plot styles

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

Strategies, orders, alerts, `request.security()`, user-defined functions, loops, labels, lines, boxes, and unrestricted drawing APIs are not enabled in this release. Unsupported syntax returns a line and column diagnostic instead of being silently interpreted.

The runtime has a dedicated Web Worker entry point at `pine-script-worker.js`. The current chart renderer remains synchronous so it can preserve the existing indicator graph contract; worker-backed chart computation will be enabled when the renderer gains an asynchronous compute boundary.
