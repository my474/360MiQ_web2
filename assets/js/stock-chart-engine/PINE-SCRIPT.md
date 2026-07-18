# Pine-Compatible Scripts

Advanced Chart includes a restricted, browser-safe Pine-compatible runtime and a searchable Pine v6 reference catalog. It supports custom indicators and a deterministic browser-only strategy backtest; it does not execute arbitrary JavaScript or place real broker orders.

## Backtesting status

The current release implements the trustworthy historical subset in phases: declaration-driven properties, deterministic order/account ledgers, next-open and process-on-close market timing, limit/stop/stop-limit fills, reversals, pyramiding, long-only/short-only filters, exits with profit/loss and trailing offsets, OCA cancellation, sizing, commission, slippage, margin checks, date/warm-up filters, strategy state series, trade/equity metrics, diagnostics, worker execution, and Strategy Tester results. The chart displays simulated executions, pending prices, and position-average guides separately from user drawings.

The result is versioned with the broker engine and keeps the full trade ledger in memory for the current chart. The script and backtest properties are part of the saved/shareable chart layout; OHLCV is still loaded from the host endpoint rather than copied into the layout URL.

## Coverage in the editor

The editor catalog mirrors the executable runtime for the supported API surface and also lists a small set of language constructs that need a larger parser or platform integration:

- Functions: declarations, plots, alerts, inputs, technical analysis, math, strings, requests, arrays, matrices, maps, colors, time, tickers, strategies, lines, labels, boxes, polylines, tables, and line fills. Entries tagged `Reference` are visible for guidance but are not claimed as executable.
- Built-ins: OHLCV and derived price series, time and bar indexes, symbol information, timeframe information, bar states, strategy values, and named colors.
- Namespaces: `ta`, `math`, `input`, `request`, `color`, `str`, `array`, `matrix`, `map`, `strategy`, `syminfo`, `barstate`, `timeframe`, `chart`, `line`, `linefill`, `label`, `box`, `polyline`, `table`, `ticker`, `runtime`, `log`, `display`, `format`, `font`, `location`, `size`, `shape`, `text`, `xloc`, `yloc`, `alert`, `session`, and `scale`.
- Language syntax: version directives, comments, indentation, assignments, tuple declarations, history references, type qualifiers, user-defined types, enums, methods, conditionals, loops, `switch`, `for ... in`, and user-defined functions.

Search the `Pine Script reference` panel with a function name, parameter, namespace, keyword, or description. Select a category to browse a manageable list instead of loading every topic at once. Click a function or exact reserved-word name in the detail header to insert it at the editor cursor. The capability registry distinguishes executable entries from `Reference` entries; it is intentionally conservative for external data requests, currency conversion, and object APIs that do not yet render with TradingView-equivalent behavior.

## Supported in the client runtime

- `//@version` directives, `indicator()`, `study()`, `strategy()`, and `library()` declarations.
- `open`, `high`, `low`, `close`, `volume`, `hl2`, `hlc3`, `ohlc4`, `bar_index`, `time`, and related chart series.
- Assignments, `var` declarations, tuples, arithmetic, comparisons, boolean operators, ternaries, and user functions (`name(args) => expression` or an indented multi-statement body).
- `if`/`else`, bounded `for ... to ...` and `for ... in ...` loops, `switch`, typed declarations, `return`, and `break`.
- `plot()`, `plotarrow()`, `plotcandle()`, `plotbar()`, `barcolor()`, `hline()`, `plotshape()`, `plotchar()`, `bgcolor()`, `fill()`, `alert()`, and `alertcondition()`.
- `label`, `line`, and `box` objects, including common getters, setters, copies, and deletes. `linefill`, `polyline`, and `table` names remain in the reference catalog until their chart rendering is complete.
- `request.security()` and `request.security_lower_tf()` with timestamp alignment. Other request APIs return configured external data when supplied, otherwise `na` without crashing the chart.
- `input.int()`, `input.float()`, `input.bool()`, `input.string()`, `input.source()`, `input.color()`, `input.time()`, `input.symbol()`, `input.session()`, `input.enum()`, and `input.text_area()`.
- The rolling `ta.*` family in the reference catalog, including moving averages, oscillators, volatility, pivots, channels, correlations, percentiles, and tuple-returning functions such as `ta.bb()`, `ta.dmi()`, `ta.aroon()`, and `ta.supertrend()`.
- The cataloged `math.*`, `str.*`, `array.*`, `matrix.*`, `map.*`, `color.*`, `ticker.*`, time, symbol, timeframe, barstate, strategy, and logging helpers.
- The complete documented strategy order and trade API for the supported historical emulator: `strategy.entry()`, `strategy.order()`, `strategy.exit()`, `strategy.close()`, `strategy.close_all()`, `strategy.cancel()`, `strategy.cancel_all()`, all six `strategy.risk.*()` rules, `strategy.convert_to_account()`, `strategy.convert_to_symbol()`, `strategy.default_entry_qty()`, and the `strategy.opentrades.*()` and `strategy.closedtrades.*()` accessors.
- Strategy performance variables include equity, net/gross/open profit and percentages, drawdown/run-up, position and margin state, trade counts, average trade statistics, maximum contracts held, account currency, and `strategy.opentrades.capital_held`. The Strategy Tester exposes fills, pending orders, trade comments, commissions, equity, costs, drawdown, monthly/yearly returns, risk halts, diagnostics, and editable properties. `strategy()` declaration properties are used by default; editing a Strategy Tester property intentionally overrides that declaration for the saved chart layout.

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

Open the `Pine Script` button in the chart toolbar to create or edit a script. Existing Pine indicators can also be edited by clicking their legend. The editor provides Pine-aware syntax highlighting, line numbers, autocomplete with `Ctrl+Space` (or `Cmd+I`), context-sensitive signature help inside calls such as `plot(`, and a `?` reference button with searchable functions, parameters, built-ins, namespaces, keywords, and syntax examples. It also provides custom undo/redo, `Ctrl/Cmd+F` find, `Ctrl/Cmd+H` find and replace, `F1` or `Ctrl/Cmd+Shift+P` commands, word wrap with `Alt/Option+Z`, indentation with `Tab`, and font-size commands. `Recent` lists the last eight scripts stored in this browser for quick loading; loading one updates the editor without running it until you press `Run`. `Ctrl/Cmd+Enter` runs the editor and `Escape` closes it.

## Deliberate limitations

The strategy engine is a historical broker emulator, not a live broker integration. It evaluates historical data once per bar and uses standard OHLC bars. A market entry/order is filled at the next eligible bar open unless `process_orders_on_close=true`; `strategy.close()` and `strategy.close_all()` can opt into the same-bar close with `immediately=true`. Limit, stop, and stop-limit orders use the bar high/low and fill at the requested level or the bar open when the bar gaps through it. `strategy.exit()` gives absolute prices precedence over relative tick distances and creates separate take-profit and stop-loss legs when both are supplied. `backtest_fill_limits_assumption` is represented by the Strategy Tester limit-verification setting. Slippage is applied in ticks using the chart's minimum tick size, commissions support percent, cash per order, and cash per contract, and margin settings can reject an order whose required margin exceeds equity. Date bounds and warm-up bars prevent order submission and pending-order fills outside the selected range.

`barMagnifier` and `lowerTimeframe` are available when the host supplies lower-timeframe bars with `chart.setBacktestLowerTimeframeBars(bars, timeframe)`. Those bars are runtime-only inputs and are never written into a saved layout or share URL; uncovered chart bars produce a fallback diagnostic and use their chart OHLC bar. `calc_on_order_fills` now schedules bounded same-bar recalculation passes after fills, including process-on-close fills; it is intentionally capped to keep a pathological script from locking the browser. `calc_on_every_tick` remains a portability setting and does not turn historical chart bars into an unrestricted tick stream. The supported label/line/box calls are rendered as isolated script outputs and do not mutate the user drawing store. `alertcondition()` is exposed as chart metadata; it does not send notifications by itself. `request.security()` uses data supplied by the embedding chart and does not fetch network data from inside a script. Unsupported syntax returns a line and column diagnostic instead of being silently interpreted. User-function recursion is capped at 32 calls, loops at 1,000 iterations, plots/security requests are bounded, and evaluation is capped by an operation budget.

The runtime caches a small number of compiled scripts and never evaluates user text as JavaScript. Missing security symbols are reported in the result as `securityRequests`, allowing the chart host to load them and recompute the script.

The runtime has a dedicated Web Worker entry point at `pine-script-worker.js`.

## Execution model

When the browser supports Web Workers, Advanced Chart evaluates each Pine Script indicator in `pine-script-worker.js` after the synchronous preview is rendered. The synchronous result keeps the chart responsive while the worker starts, and the worker result replaces it when it returns. A request revision and script fingerprint prevent results from an older symbol, timeframe, or edit from being applied to the current chart. Browsers that cannot create a worker, pages opened from an unsupported origin, and automated test environments continue to use the synchronous runtime automatically.

The worker URL can be overridden by passing `pineWorkerUrl` to the chart constructor. Set `pineWorker: false` only for an embedding that deliberately requires synchronous evaluation.

## Official reference

The catalog follows TradingView's official Pine Script v6 documentation:

- Pine Script language reference manual: https://www.tradingview.com/pine-script-reference/v6/
- Pine Script language and built-ins: https://www.tradingview.com/pine-script-docs/language/built-ins/
- Pine Script language overview: https://www.tradingview.com/pine-script-docs/language/

The official manual remains authoritative for exact overloads, type qualifiers, limits, version changes, and behavior differences from the browser runtime.
