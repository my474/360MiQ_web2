# Pine Strategy Backtesting Plan

## Objective

Add a browser-only Pine strategy backtesting system to the Advanced Chart that is compatible with TradingView-style strategy semantics. The system should execute supported Pine strategies against the loaded OHLCV data, simulate orders through a deterministic broker emulator, expose strategy state back to Pine, and present a useful Strategy Tester without requiring a backend.

The target is **TradingView-compatible behavior for the supported subset**, not a claim of byte-for-byte parity with TradingView's proprietary implementation. Every unsupported behavior must be explicit in the UI and documentation.

## What TradingView Does

TradingView strategies are Pine programs that simulate historical and realtime trades. They do not place real broker orders by themselves. Orders are sent to a broker emulator, which applies the strategy's execution settings, determines fills, updates the position and account, and makes the resulting state available to later calculations.

The relevant TradingView concepts are:

- `strategy()` declares a strategy and its properties, such as initial capital, order sizing, pyramiding, commission, slippage, margin, and recalculation behavior.
- Strategy code creates order intents with functions such as `strategy.entry()`, `strategy.order()`, `strategy.exit()`, `strategy.close()`, and cancellation functions.
- The broker emulator fills market, limit, stop, and stop-limit orders according to historical OHLC data, gaps, order timing, and optional lower-timeframe information.
- The execution model determines whether code runs once per bar, on every realtime tick, after order fills, or with orders processed on the bar close.
- Strategy state includes equity, position size, average entry price, open profit, closed trades, open trades, and trade statistics.
- Strategy Tester exposes an overview, performance summary, list of trades, and properties. The results include profit, drawdown, trade count, win/loss statistics, commissions, and related metrics.
- Non-standard chart types and careless lookahead or repainting can make backtests unrealistic, so the engine must surface warnings and preserve the distinction between chart display data and executable standard OHLC data.

Primary references:

- [TradingView: Strategies](https://www.tradingview.com/pine-script-docs/concepts/strategies/)
- [TradingView: Execution model](https://www.tradingview.com/pine-script-docs/language/execution-model/)
- [TradingView: Declaration statements](https://www.tradingview.com/pine-script-docs/language/declaration-statements/)
- [TradingView: Strategies FAQ](https://www.tradingview.com/pine-script-docs/faq/strategies/)
- [TradingView: Broker emulator](https://www.tradingview.com/support/solutions/43000786181-broker-emulator/)

## Current State in This Project

The implementation has now started replacing the original intent-only strategy placeholder with a deterministic browser broker emulator:

- `assets/js/stock-chart-engine/pine-backtest-engine.js` contains the normalized settings contract, historical broker emulator, account ledger, pending orders, fills, trades, costs, drawdown, diagnostics, and metrics.
- `assets/js/stock-chart-engine/pine-script-runtime.js` executes strategy scripts bar by bar, exposes live `strategy.*` state, and returns versioned backtest results.
- `stock-chart-engine.js` integrates the worker/runtime result with a desktop/mobile Strategy Tester, editable properties, trade navigation, theme switching, and simulated chart overlays.
- `PINE-SCRIPT.md` documents the supported subset and explicitly calls out unsupported tick-level and lower-timeframe behavior.
- The existing worker architecture is retained so strategy runs do not block chart interaction, and request revisions prevent stale results from overwriting a newer run.

The remaining phases are deliberate fidelity work: fill-triggered/tick re-execution, lower-timeframe Bar Magnifier data loading, richer order tooltips and optional equity panes, broader capability metadata, and additional compatibility fixtures.

The first implementation must replace intent recording with a deterministic event pipeline while keeping the current indicator runtime working unchanged.

## Design Principles

1. **Deterministic results:** the same data, script, settings, and engine version produce the same result.
2. **No lookahead:** a strategy cannot use data that was unavailable at the simulated execution time.
3. **Explicit fill assumptions:** ambiguous intrabar order sequences must follow a documented policy and be visible in Properties or diagnostics.
4. **Separate concerns:** Pine execution, order simulation, accounting, metrics, and UI result rendering use separate modules and contracts.
5. **Worker first:** strategy execution belongs in the Pine worker; the main thread receives progress, diagnostics, and immutable results.
6. **No backend requirement:** OHLCV and optional lower-timeframe data come from existing endpoints; layout/share data stores the script and settings, not the full market history.
7. **Safe subset:** no dynamic JavaScript evaluation, network access from user scripts, unrestricted DOM access, or arbitrary code execution.
8. **Versioned results:** strategy settings, engine version, and data assumptions are included in serialized results for reproducibility.

## Target Architecture

```text
Chart data + strategy source + backtest settings
                    |
                    v
            Pine worker execution
                    |
        series values + order intents
                    |
                    v
             Broker emulator
                    |
          fills + account ledger
                    |
                    v
           Strategy state snapshot
                    |
          +---------+---------+
          v                   v
   Pine next-bar state     Strategy Tester UI
```

The worker should process one run as a cancellable job. A run receives a normalized data series and produces a versioned `BacktestResult` containing configuration, diagnostics, executions, trades, equity curve, drawdown curve, and metrics.

## Phased Implementation Plan

### Phase 1: Strategy configuration contract

Define a normalized `BacktestSettings` object and connect it to `strategy()` declaration defaults and the Strategy Tester Properties panel.

Required settings:

- Initial capital and account currency.
- Default order size: contracts, cash value, or percent of equity.
- Pyramiding limit.
- Commission type and value: percent, cash per order, or cash per contract.
- Slippage in ticks.
- Margin long and margin short.
- Limit-order verification threshold.
- Process orders on close.
- Recalculate on every tick and after order fills.
- Date range filter and warm-up period.
- Standard OHLC execution mode.
- Optional Bar Magnifier/lower-timeframe mode.
- Whether trades are long-only, short-only, or both where supported.

The settings must be serializable with the chart layout and share URL, while OHLCV remains endpoint-loaded.

### Phase 2: Backtest domain model and ledger

Create dedicated runtime modules rather than expanding the current placeholder object in one function.

Core models:

- `StrategyConfig`: normalized strategy declaration and user overrides.
- `BarContext`: current bar, historical index, timestamp, available OHLCV, and calculation phase.
- `PendingOrder`: id, type, direction, requested quantity, limit/stop prices, parent exit id, OCA group, creation bar, and status.
- `Execution`: order id, timestamp, bar index, fill price, quantity, commission, slippage, and reason.
- `Position`: signed size, average price, realized P&L, unrealized P&L, and entry references.
- `OpenTrade`: entry order, entry fill, quantity, direction, and accumulated costs.
- `ClosedTrade`: entry/exit references, size, prices, gross/net profit, commission, bars held, and exit reason.
- `AccountLedger`: cash, equity, balance, margin used, free margin, peak equity, drawdown, and cumulative costs.
- `StrategyState`: values exposed through `strategy.*` during the current calculation.
- `BacktestResult`: immutable final result plus diagnostics and serialized metadata.

Use integer bar indices internally and preserve timestamps separately. This avoids errors when data has missing dates, irregular sessions, or duplicate display labels.

### Phase 3: Historical execution loop

Implement the minimum trustworthy execution model first:

1. Normalize standard OHLCV bars in chronological order.
2. Apply the date range and warm-up filter.
3. Initialize the account and strategy state.
4. Calculate the strategy on each eligible bar.
5. Collect order intents without filling them during the same calculation unless the configured model permits it.
6. Fill eligible market orders at the next available execution point, normally the next bar open.
7. Update position, ledger, closed trades, and strategy built-ins.
8. Continue until the last bar, then calculate final mark-to-market values.

Add the following timing rules as separate tested behaviors:

- Default historical calculation once per bar with market orders filled on the next bar.
- `process_orders_on_close` allows eligible market orders to fill on the current bar close.
- `calc_on_order_fills` schedules a recalculation after a fill.
- `calc_on_every_tick` is meaningful for realtime data and lower-timeframe simulation; historical behavior must be clearly documented.
- Orders created after the final executable bar remain unfilled and are reported as pending or expired according to the chosen policy.

### Phase 4: Broker emulator and order types

Implement order lifecycle handling with explicit state transitions:

- Market orders.
- Limit orders.
- Stop orders.
- Stop-limit orders.
- Entry orders with reversal behavior.
- Exit orders with stop, limit, profit, loss, and trailing parameters.
- Close and close-all orders.
- Order and all-order cancellation.
- Reduce-only exit behavior.
- Pyramiding rules.
- One-cancels-all groups where the Pine API exposes them.

For every order, track `created -> pending -> filled/partially-filled/cancelled/expired/rejected`. Reject invalid quantities, prices, margin requirements, and impossible combinations with a diagnostic instead of silently changing user intent.

Fill rules must handle:

- Gaps between bars.
- Whether a limit or stop touched the bar range.
- Price improvement or worse-than-requested fills caused by gaps.
- Tick-size rounding.
- Slippage applied in the correct direction.
- Commission charged at execution.
- Reversal and position-size calculations.
- Same-bar stop/target ambiguity.

### Phase 5: Intrabar simulation and Bar Magnifier

Historical OHLC alone does not reveal the exact order of high and low within a bar. Implement two documented modes:

1. **OHLC path mode:** use a deterministic path heuristic based on the bar's open and proximity to high/low, matching TradingView's documented broker-emulator assumption as closely as practical.
2. **Lower-timeframe mode:** request lower-timeframe OHLC data for each chart bar and simulate fills in that sequence. Use the current endpoint/data-loading pattern, cache requests, and gracefully fall back when lower-timeframe data is unavailable.

Expose a warning when a result contains ambiguous same-bar fills. Include the selected mode, lower timeframe, and fallback count in the result metadata.

### Phase 6: Pine strategy built-ins

Replace placeholder values with live series backed by the ledger:

- `strategy.initial_capital`.
- `strategy.equity`.
- `strategy.netprofit`.
- `strategy.openprofit`.
- `strategy.position_size`.
- `strategy.position_avg_price`.
- `strategy.grossprofit` and `strategy.grossloss`.
- `strategy.wintrades` and `strategy.losstrades`.
- `strategy.closedtrades` and `strategy.opentrades` counts.
- `strategy.max_drawdown`, `strategy.max_runup`, and related values where supported.
- `strategy.opentrades.*` accessors.
- `strategy.closedtrades.*` accessors.

Values must be available at the correct calculation phase. For example, a fill-triggered recalculation must see the updated position, while a pre-fill calculation must not.

Add a capability registry so unsupported built-ins are labeled accurately in the Pine documentation and diagnostics.

### Phase 7: Metrics and result validation

Compute metrics from executions and closed trades, not from chart pixels or approximate series values.

Minimum metrics:

- Net profit and net return.
- Gross profit and gross loss.
- Profit factor.
- Total trades, winning trades, losing trades, win rate.
- Average trade, average winning trade, average losing trade.
- Largest winning and losing trade.
- Commission and slippage totals.
- Buy-and-hold return for comparison.
- Maximum drawdown and maximum drawdown percent.
- Maximum run-up.
- Long and short trade breakdown.
- Average bars in trade.
- Ending equity and peak equity.

Second-stage metrics:

- Sharpe ratio.
- Sortino ratio.
- Recovery factor.
- Calmar-style return/drawdown measures.
- Monthly and yearly return tables.

Define numerical tolerances and invariants, including:

- Sum of closed trade P&L equals realized net profit after costs.
- Equity equals cash plus marked position value where applicable.
- Every filled entry has a corresponding close or remains in open trades.
- Cancelled orders never generate executions.
- Commission and slippage are applied exactly once.

### Phase 8: Strategy Tester interface

Add a Strategy Tester panel associated with the active Pine strategy. It should be a desktop resizable panel and a mobile bottom sheet using the chart's existing responsive dialog conventions.

Tabs/views:

- **Overview:** net result, return, drawdown, trade count, win rate, and equity curve.
- **Performance Summary:** complete metric table with long/short breakdown and costs.
- **List of Trades:** sortable trade rows with entry/exit time, direction, size, prices, P&L, commission, and exit reason.
- **Properties:** capital, sizing, costs, margin, execution mode, date range, data source, and engine version.
- **Diagnostics:** rejected orders, ambiguous fills, unavailable lower-timeframe data, lookahead warnings, and unsupported calls.

Clicking a trade should move the chart crosshair to the entry or exit bar. The panel must not cover essential chart controls and must support dark mode, light mode, keyboard escape, and outside-click close where appropriate.

### Phase 9: Strategy settings and run workflow

Add a clear workflow around Pine Script:

1. Open Pine Script editor.
2. Parse and validate the declaration.
3. Run the script.
4. If it is an indicator, render plots as today.
5. If it is a strategy, show strategy status and open Strategy Tester results.
6. Allow Properties changes and rerun without losing the source.
7. Show the last run time, data range, settings hash, and result status.

Use a Run ID so a slow previous run cannot overwrite a newer run. Disable stale result updates when the stock, timeframe, settings, or source changes.

### Phase 10: Chart visualization

Add strategy-specific layers that remain separate from user drawings:

- Entry and exit markers.
- Long/short direction styling.
- Pending order markers and optional order price lines.
- Position average-price line.
- Stop-loss and take-profit lines.
- Fill tooltips with order id, quantity, price, costs, and reason.
- Optional equity and drawdown panes.
- Visual distinction between simulated fills and manually drawn annotations.

All layers must follow the existing chart viewport, zoom, scroll, pane movement, theme switching, export, share, and mobile behavior. They must be included in layout/share state as strategy visualization settings, while raw OHLCV remains endpoint-loaded.

### Phase 11: Safety, diagnostics, and performance

Protect the browser and make limitations understandable:

- Keep Pine execution in the worker.
- Enforce maximum bars, orders, trades, lower-timeframe requests, and execution time.
- Support cancellation and progress updates.
- Reject network, DOM, storage, and JavaScript escape attempts from scripts.
- Detect invalid or missing OHLCV fields.
- Detect duplicate timestamps and non-monotonic data.
- Warn on non-standard chart types and use standard OHLC for execution unless explicitly supported.
- Warn about lookahead and future-data access.
- Report when a request cannot align dates or when lower-timeframe data is incomplete.
- Use compact typed arrays for large equity and series outputs where practical.
- Avoid rendering every execution marker when the viewport cannot display them; retain full data for the trade list.

### Phase 12: Documentation and capability catalog

Update `PINE-SCRIPT.md`, the function catalog, runtime capability checks, and editor documentation together. Use distinct states:

- Supported and executable.
- Parsed but not executable.
- Reference-only.
- Unsupported.

Document each supported strategy function with its execution timing, fill behavior, supported arguments, and known differences from TradingView. Do not label a strategy API as supported merely because the parser accepts its name.

Add a Backtesting section covering:

- Supported strategy declarations.
- Order types and fill model.
- Costs and sizing.
- Intrabar assumptions.
- Date ranges and warm-up.
- Repainting/lookahead limitations.
- Standard versus non-standard chart data.
- Result reproducibility and engine versioning.

## Recommended Delivery Order

### Milestone 1: Trustworthy basic backtest

Deliver a simple `strategy()` with market `strategy.entry()` and `strategy.close()` support, next-bar fills, percent/cash/contract sizing, commission, equity curve, closed trade list, net profit, drawdown, and Strategy Tester Properties.

Example acceptance script:

```pine
//@version=5
strategy("SMA crossover", initial_capital=100000, commission_type=strategy.commission.percent, commission_value=0.1)
fast = ta.sma(close, 20)
slow = ta.sma(close, 50)
if ta.crossover(fast, slow)
    strategy.entry("Long", strategy.long)
if ta.crossunder(fast, slow)
    strategy.close("Long")
plot(fast)
plot(slow)
```

### Milestone 2: Practical order simulation

Add limit, stop, exit, cancellation, reversal, pyramiding, slippage, tick rounding, OCA behavior, and detailed diagnostics.

### Milestone 3: TradingView-style execution controls

Add process-on-close, recalculation modes, historical intrabar path, lower-timeframe Bar Magnifier, and strategy built-ins backed by the ledger.

### Milestone 4: Complete Strategy Tester experience

Add all result views, charts, trade-to-chart navigation, export/share of source and settings, mobile sheets, and performance safeguards.

### Milestone 5: Fidelity and breadth

Expand strategy namespaces and accessors, add advanced statistics, improve compatibility fixtures, and document any remaining behavioral differences.

## Testing Strategy

Build deterministic fixtures before expanding the API:

- Market order fills next bar open.
- Market order with process-on-close.
- Reversal and pyramiding limits.
- Long and short positions.
- Limit touch, limit gap, stop touch, and stop gap.
- Stop-limit activation and fill.
- Same-bar stop/target ambiguity.
- Commission percent, cash per order, and cash per contract.
- Slippage and tick-size rounding.
- Percent-of-equity sizing and insufficient margin.
- Exit quantity reduction and partial closes.
- Cancellation before fill.
- Date range and warm-up handling.
- Missing dates and unequal lower-timeframe coverage.
- `calc_on_order_fills` and recalculation ordering.
- Equity, trade, drawdown, and cost accounting invariants.
- Script cancellation and stale-run protection.
- Light/dark mode, responsive Strategy Tester, mobile sheet, export, and share behavior.

Run the existing stock-chart-engine tests after each runtime change and add a dedicated backtest test suite for ledger and broker-emulator behavior. Include snapshot fixtures for serialized results so future changes to execution semantics are deliberate.

## Acceptance Criteria

The first complete release is ready when:

- A supported strategy can produce real fills, positions, equity, and closed trades in the browser.
- Results are deterministic and explain their execution assumptions.
- Strategy Tester metrics reconcile with the trade ledger.
- Clicking a trade navigates the chart to the correct bar.
- Costs, sizing, and timing settings visibly affect results.
- Unsupported Pine behavior is rejected or labeled instead of silently producing misleading output.
- A shared chart preserves the strategy source, settings, and tester configuration while reloading OHLCV from the endpoint.
- The feature works in light mode, dark mode, desktop, and mobile without blocking chart controls.

## Important Scope Boundary

Exact TradingView parity should be approached incrementally. The most important trust boundary is correct order timing, fill assumptions, costs, and accounting. It is better to support a smaller strategy subset with transparent diagnostics than to expose every `strategy.*` name while returning placeholder values.
