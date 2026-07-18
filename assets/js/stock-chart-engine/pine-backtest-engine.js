/*
 * Deterministic, browser-safe broker emulator for the Pine-compatible runtime.
 *
 * This deliberately keeps execution and accounting separate from Pine parsing.
 * It implements the supported backtest subset and reports every assumption in
 * the result so a chart never presents a simulated fill as a real broker fill.
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) module.exports = factory();
  else root.PineBacktestEngine = factory();
}(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var VERSION = '0.3.3';
  var INTRABAR_PATH_POLICY = 'open-nearest-extreme';
  var EOD_FILL_MODE = 'ohlc';
  var DEFAULT_MAX_BARS = 100000;
  var DEFAULT_MAX_BROKER_ORDERS = 100000;
  var DEFAULT_MAX_EXECUTIONS = 100000;
  var DEFAULT_MAX_TRADES = 50000;

  function number(value, fallback) {
    if (value == null || value === '') return fallback;
    var result = Number(value);
    return Number.isFinite(result) ? result : fallback;
  }

  function positive(value, fallback) {
    var result = number(value, fallback);
    return result > 0 ? result : fallback;
  }

  function boundedLimit(value, fallback, maximum) {
    var result = Math.floor(number(value, fallback));
    if (!Number.isFinite(result) || result < 1) return fallback;
    return Math.min(result, maximum);
  }

  function normalizeDirection(value) {
    if (value === 1 || value === '1' || String(value).toLowerCase() === 'long') return 1;
    if (value === -1 || value === '-1' || String(value).toLowerCase() === 'short') return -1;
    return 0;
  }

  function normalizeBars(bars) {
    return (Array.isArray(bars) ? bars : []).map(function (bar, index) {
      var adjustedClose = number(bar && (bar.adjustedClose != null ? bar.adjustedClose : bar.adjClose != null ? bar.adjClose : bar.adj_close), NaN);
      var splitFactor = number(bar && (bar.splitFactor != null ? bar.splitFactor : bar.split_factor != null ? bar.split_factor : bar.split), NaN);
      var dividend = number(bar && (bar.dividend != null ? bar.dividend : bar.dividends != null ? bar.dividends : bar.cashDividend), NaN);
      var priceBasis = String(bar && (bar.priceBasis || bar.price_basis || '') || '').toLowerCase();
      return {
        sourceIndex: index,
        time: number(bar && bar.time, NaN),
        open: number(bar && bar.open, NaN),
        high: number(bar && bar.high, NaN),
        low: number(bar && bar.low, NaN),
        close: number(bar && bar.close, NaN),
        volume: number(bar && bar.volume, 0),
        adjustedClose: adjustedClose,
        adjusted: bar && (bar.adjusted === true || bar.isAdjusted === true || priceBasis === 'adjusted' || Number.isFinite(adjustedClose)),
        priceBasis: priceBasis || (bar && bar.adjusted === true ? 'adjusted' : ''),
        splitFactor: splitFactor,
        dividend: dividend,
        timezone: String(bar && (bar.timezone || bar.tz || '') || '')
      };
    }).filter(function (bar) {
      return Number.isFinite(bar.time) && Number.isFinite(bar.open) && Number.isFinite(bar.high) && Number.isFinite(bar.low) && Number.isFinite(bar.close) &&
        bar.high >= Math.max(bar.open, bar.close) && bar.low <= Math.min(bar.open, bar.close);
    }).sort(function (left, right) {
      return left.time - right.time || left.sourceIndex - right.sourceIndex;
    }).map(function (bar, index) {
      bar.index = index;
      delete bar.sourceIndex;
      return bar;
    });
  }

  function utcDayKey(time) {
    var date = new Date(number(time, 0) > 100000000000 ? number(time, 0) : number(time, 0) * 1000);
    return Number.isFinite(date.getTime()) ? date.getUTCFullYear() + '-' + String(date.getUTCMonth() + 1).padStart(2, '0') + '-' + String(date.getUTCDate()).padStart(2, '0') : '';
  }

  function normalizeSettings(input) {
    input = input || {};
    var commissionType = String(input.commissionType || input.commission_type || 'percent').toLowerCase();
    if (['percent', 'cash_per_order', 'cash_per_contract'].indexOf(commissionType) === -1) commissionType = 'percent';
    var orderQtyType = String(input.defaultQtyType || input.default_qty_type || input.orderQtyType || 'fixed').toLowerCase();
    if (['fixed', 'cash', 'percent_of_equity'].indexOf(orderQtyType) === -1) orderQtyType = 'fixed';
    var directionMode = String(input.directionMode || input.direction_mode || 'both').toLowerCase();
    if (['both', 'long', 'short'].indexOf(directionMode) === -1) directionMode = 'both';
    var fillMode = String(input.fillMode || input.fill_mode || EOD_FILL_MODE).toLowerCase();
    if (fillMode !== EOD_FILL_MODE) fillMode = EOD_FILL_MODE;
    return {
      initialCapital: positive(input.initialCapital != null ? input.initialCapital : input.initial_capital, 100000),
      currency: String(input.currency || 'USD'),
      defaultQtyType: orderQtyType,
      defaultQtyValue: positive(input.defaultQtyValue != null ? input.defaultQtyValue : input.default_qty_value, 1),
      directionMode: directionMode,
      pyramiding: Math.max(0, Math.floor(number(input.pyramiding, 0))),
      commissionType: commissionType,
      commissionValue: Math.max(0, number(input.commissionValue != null ? input.commissionValue : input.commission_value, 0)),
      slippageTicks: Math.max(0, number(input.slippageTicks != null ? input.slippageTicks : input.slippage, 0)),
      tickSize: positive(input.tickSize != null ? input.tickSize : input.mintick, 0.01),
      marginLong: positive(input.marginLong != null ? input.marginLong : input.margin_long, 100),
      marginShort: positive(input.marginShort != null ? input.marginShort : input.margin_short, 100),
      conversionRate: positive(input.conversionRate != null ? input.conversionRate : input.conversion_rate, 1),
      closeEntriesRule: String(input.closeEntriesRule || input.close_entries_rule || 'FIFO').toUpperCase() === 'ANY' ? 'ANY' : 'FIFO',
      riskFreeRate: number(input.riskFreeRate != null ? input.riskFreeRate : input.risk_free_rate, 0),
      processOrdersOnClose: input.processOrdersOnClose === true || input.process_orders_on_close === true,
      calcOnOrderFills: input.calcOnOrderFills === true || input.calc_on_order_fills === true,
      calcOnEveryTick: input.calcOnEveryTick === true || input.calc_on_every_tick === true,
      fillMode: fillMode,
      limitVerificationTicks: Math.max(0, number(input.limitVerificationTicks != null ? input.limitVerificationTicks : input.backtest_fill_limits_assumption, 0)),
      dateFrom: input.dateFrom == null ? null : number(input.dateFrom, null),
      dateTo: input.dateTo == null ? null : number(input.dateTo, null),
      warmupBars: Math.max(0, Math.floor(number(input.warmupBars != null ? input.warmupBars : input.warmup_bars, 0))),
      symbol: String(input.symbol || ''),
      timeframe: String(input.timeframe || '1D'),
      timezone: String(input.timezone || 'UTC'),
      maxBars: boundedLimit(input.maxBars != null ? input.maxBars : input.max_bars, DEFAULT_MAX_BARS, DEFAULT_MAX_BARS),
      maxOrders: boundedLimit(input.maxOrders != null ? input.maxOrders : input.max_orders, DEFAULT_MAX_BROKER_ORDERS, DEFAULT_MAX_BROKER_ORDERS),
      maxExecutions: boundedLimit(input.maxExecutions != null ? input.maxExecutions : input.max_executions, DEFAULT_MAX_EXECUTIONS, DEFAULT_MAX_EXECUTIONS),
      maxTrades: boundedLimit(input.maxTrades != null ? input.maxTrades : input.max_trades, DEFAULT_MAX_TRADES, DEFAULT_MAX_TRADES),
      barMagnifier: input.barMagnifier === true || input.useBarMagnifier === true || input.use_bar_magnifier === true,
      barMagnifierMode: String(input.barMagnifierMode || input.bar_magnifier_mode || '').toLowerCase(),
      lowerTimeframe: String(input.lowerTimeframe || input.lower_timeframe || '')
    };
  }

  function commission(settings, price, quantity) {
    var value = Math.abs(number(price, 0) * number(quantity, 0));
    if (settings.commissionType === 'cash_per_order') return settings.commissionValue;
    if (settings.commissionType === 'cash_per_contract') return Math.abs(quantity) * settings.commissionValue;
    return value * settings.commissionValue / 100;
  }

  function orderSidePrice(settings, direction, price) {
    var adjustment = settings.slippageTicks * settings.tickSize;
    return number(price, NaN) + (direction >= 0 ? adjustment : -adjustment);
  }

  function orderQuantity(settings, order, price, snapshot) {
    var explicit = order.quantity != null ? number(order.quantity, NaN) : NaN;
    if (Number.isFinite(explicit) && explicit > 0) return Math.abs(explicit);
    var value = settings.defaultQtyValue;
    if (settings.defaultQtyType === 'cash') return price > 0 ? value / price : 0;
    if (settings.defaultQtyType === 'percent_of_equity') return price > 0 ? snapshot.equity * value / 100 / price : 0;
    return value;
  }

  function createSession(settingsInput, barsInput) {
    var settings = normalizeSettings(settingsInput);
    var normalizedBars = normalizeBars(barsInput);
    var bars = normalizedBars.slice(0, settings.maxBars);
    var lowerBarsInput = settingsInput && Array.isArray(settingsInput.lowerTimeframeBars) ? settingsInput.lowerTimeframeBars : [];
    var lowerBars = normalizeBars(lowerBarsInput);
    var lowerBarsByChart = bars.map(function () { return []; });
    var lowerBarsCoveredChartBars = 0;
    var barMagnifierFallbacks = [];
    var barMagnifierFallbackKeys = Object.create(null);
    var pending = [];
    var executions = [];
    var closedTrades = [];
    var openTrades = [];
    var diagnostics = [];
    var sameBarAmbiguities = [];
    var sameBarAmbiguityKeys = Object.create(null);
    var submittedOrderCount = 0;
    var limitDiagnosticKeys = Object.create(null);
    var equityCurve = [];
    var drawdownCurve = [];
    var buyAndHoldCurve = [];
    var stateCurve = [];
    var rawBars = Array.isArray(barsInput) ? barsInput : [];
    var dataQuality = {
      inputBars: rawBars.length,
      validBars: bars.length,
      sourceValidBars: normalizedBars.length,
      truncatedBars: Math.max(0, normalizedBars.length - bars.length),
      invalidBars: 0,
      invalidGeometry: 0,
      duplicateTimestamps: 0,
      outOfOrder: 0,
      largeCalendarGaps: [],
      adjustedBars: 0,
      unadjustedBars: 0,
      splitEvents: 0,
      dividendEvents: 0,
      priceBasis: 'unspecified',
      timezone: settings.timezone || 'UTC'
    };
    var previousTime = -Infinity;
    var seenTimes = Object.create(null);
    var observedTimezones = Object.create(null);
    rawBars.forEach(function (bar, index) {
      var time = number(bar && bar.time, NaN);
      var open = number(bar && bar.open, NaN);
      var high = number(bar && bar.high, NaN);
      var low = number(bar && bar.low, NaN);
      var close = number(bar && bar.close, NaN);
      var fieldsValid = Number.isFinite(time) && Number.isFinite(open) && Number.isFinite(high) && Number.isFinite(low) && Number.isFinite(close);
      var geometryValid = fieldsValid && high >= Math.max(open, close) && low <= Math.min(open, close);
      var adjustedClose = number(bar && (bar.adjustedClose != null ? bar.adjustedClose : bar.adjClose != null ? bar.adjClose : bar.adj_close), NaN);
      var adjusted = bar && (bar.adjusted === true || bar.isAdjusted === true || String(bar.priceBasis || bar.price_basis || '').toLowerCase() === 'adjusted' || Number.isFinite(adjustedClose));
      var splitFactor = number(bar && (bar.splitFactor != null ? bar.splitFactor : bar.split_factor != null ? bar.split_factor : bar.split), NaN);
      var dividend = number(bar && (bar.dividend != null ? bar.dividend : bar.dividends != null ? bar.dividends : bar.cashDividend), NaN);
      var barTimezone = String(bar && (bar.timezone || bar.tz || '') || '').trim();
      if (barTimezone) observedTimezones[barTimezone] = true;
      if (adjusted) dataQuality.adjustedBars += 1;
      else if (fieldsValid) dataQuality.unadjustedBars += 1;
      if (Number.isFinite(splitFactor) && splitFactor !== 1) dataQuality.splitEvents += 1;
      if (Number.isFinite(dividend) && dividend !== 0) dataQuality.dividendEvents += 1;
      if (!fieldsValid) {
        diagnostics.push({ type: 'data', message: 'Bar ' + index + ' was ignored because it is missing a valid OHLC field.', orderId: null, barIndex: index });
        dataQuality.invalidBars += 1;
        return;
      }
      if (!geometryValid) {
        diagnostics.push({ type: 'data-geometry', message: 'Bar ' + index + ' was ignored because high/low do not contain the open and close.', orderId: null, barIndex: index });
        dataQuality.invalidBars += 1;
        dataQuality.invalidGeometry += 1;
        return;
      }
      if (time < previousTime) {
        dataQuality.outOfOrder += 1;
        diagnostics.push({ type: 'data-order', message: 'Input bars were not chronological; the broker sorted them by timestamp.', orderId: null, barIndex: index });
      }
      if (seenTimes[String(time)]) {
        dataQuality.duplicateTimestamps += 1;
        diagnostics.push({ type: 'duplicate-time', message: 'Duplicate bar timestamp detected; source order was preserved for equal timestamps.', orderId: null, barIndex: index });
      }
      seenTimes[String(time)] = true;
      previousTime = time;
    });
    var timezoneNames = Object.keys(observedTimezones);
    if (timezoneNames.length === 1) dataQuality.timezone = timezoneNames[0];
    else if (timezoneNames.length > 1) dataQuality.timezone = 'mixed (' + timezoneNames.join(', ') + ')';
    if (dataQuality.adjustedBars && dataQuality.unadjustedBars) {
      dataQuality.priceBasis = 'mixed';
      diagnostics.push({ type: 'data-basis', message: 'The input mixes adjusted and unadjusted bars. Verify that the OHLC series uses one price basis before comparing returns.', orderId: null, barIndex: -1 });
    } else if (dataQuality.adjustedBars) dataQuality.priceBasis = 'adjusted';
    else if (dataQuality.unadjustedBars) dataQuality.priceBasis = 'unadjusted';
    for (var gapIndex = 1; gapIndex < bars.length; gapIndex += 1) {
      var gapSeconds = bars[gapIndex].time - bars[gapIndex - 1].time;
      if (!(gapSeconds > 0)) continue;
      var previousDelta = gapIndex > 1 ? bars[gapIndex - 1].time - bars[gapIndex - 2].time : gapSeconds;
      if (previousDelta > 0 && gapSeconds > previousDelta * 3) {
        dataQuality.largeCalendarGaps.push({ from: bars[gapIndex - 1].time, to: bars[gapIndex].time, seconds: gapSeconds });
      }
    }
    if (dataQuality.largeCalendarGaps.length) diagnostics.push({ type: 'data-gap', message: dataQuality.largeCalendarGaps.length + ' calendar gap(s) exceed three times the preceding bar interval; no synthetic bars were inserted.', orderId: null, barIndex: -1 });
    if (dataQuality.truncatedBars > 0) diagnostic('limit', 'The input was truncated at the maximum backtest bar limit; later bars were not evaluated.', null, -1, { limit: settings.maxBars, truncatedBars: dataQuality.truncatedBars });
    if (settings.barMagnifier && lowerBarsInput.length !== lowerBars.length) {
      diagnostics.push({ type: 'bar-magnifier-data', message: 'Some ' + (settings.barMagnifierMode === 'daily-eod' ? 'daily EOD magnifier bars' : 'lower-timeframe bars') + ' were ignored because they were missing a valid OHLC field.', orderId: null, barIndex: -1 });
    }
    if (settings.barMagnifier && lowerBars.length) {
      var chartCursor = 0;
      lowerBars.forEach(function (lowerBar) {
        while (chartCursor < bars.length - 1 && lowerBar.time >= bars[chartCursor + 1].time) chartCursor += 1;
        if (bars[chartCursor] && lowerBar.time >= bars[chartCursor].time) lowerBarsByChart[chartCursor].push(lowerBar);
      });
      lowerBarsCoveredChartBars = lowerBarsByChart.reduce(function (count, chartBars) {
        return count + (chartBars.length ? 1 : 0);
      }, 0);
    }
    var state = {
      cash: settings.initialCapital,
      realizedGross: 0,
      totalCommission: 0,
      totalSlippage: 0,
      equity: settings.initialCapital,
      peakEquity: settings.initialCapital,
      maxDrawdown: 0,
      maxRunup: 0,
      positionSize: 0,
      positionAvgPrice: NaN,
      openProfit: 0,
      currentBar: -1,
      currentPrice: NaN,
      currentTime: null,
      wins: 0,
      losses: 0,
      grossProfit: 0,
      grossLoss: 0,
      marginUsed: 0,
      freeMargin: settings.initialCapital,
      capitalHeld: 0,
      positionEntryName: '',
      maxContractsHeldAll: 0,
      maxContractsHeldLong: 0,
      maxContractsHeldShort: 0,
      evenTrades: 0
    };
    var risk = {
      allowDirection: 0,
      maxDrawdown: null,
      maxIntradayLoss: null,
      maxIntradayFilledOrders: null,
      maxPositionSize: null,
      maxConsecutiveLossDays: null,
      halted: false,
      haltReason: '',
      dayKey: '',
      dayStartEquity: settings.initialCapital,
      dayStartNetProfit: 0,
      dayFilledOrders: 0,
      consecutiveLossDays: 0
    };

    function isEligible(index) {
      var bar = visibleBar(index);
      if (!bar || index < settings.warmupBars || risk.halted) return false;
      if (settings.dateFrom != null && bar.time < settings.dateFrom) return false;
      if (settings.dateTo != null && bar.time > settings.dateTo) return false;
      return true;
    }

    function diagnostic(type, message, order, index, details) {
      var item = { type: type, message: message, orderId: order && order.id || null, barIndex: index == null ? state.currentBar : index };
      if (details) item.details = details;
      diagnostics.push(item);
    }

    function brokerLimit(kind, message, order, index) {
      if (limitDiagnosticKeys[kind]) return false;
      limitDiagnosticKeys[kind] = true;
      diagnostic('limit', message, order, index, { limit: settings['max' + kind.charAt(0).toUpperCase() + kind.slice(1)] });
      return true;
    }

    function visibleBar(index) {
      return bars[Math.max(0, Math.min(bars.length - 1, index))] || null;
    }

    function markToMarket(bar) {
      if (!bar) return;
      state.currentPrice = bar.close;
      openTrades.forEach(function (trade) {
        var closeProfit = (trade.direction > 0 ? bar.close - trade.entryPrice : trade.entryPrice - bar.close) * trade.quantity;
        var highProfit = (trade.direction > 0 ? bar.high - trade.entryPrice : trade.entryPrice - bar.low) * trade.quantity;
        var lowProfit = (trade.direction > 0 ? bar.low - trade.entryPrice : trade.entryPrice - bar.high) * trade.quantity;
        trade.currentProfit = closeProfit;
        trade.size = trade.direction * trade.quantity;
        trade.maxRunup = Math.max(number(trade.maxRunup, 0), highProfit);
        trade.maxDrawdown = Math.max(number(trade.maxDrawdown, 0), Math.max(0, -lowProfit));
        var tradeNotional = Math.abs(trade.entryPrice * trade.quantity);
        trade.currentProfitPercent = tradeNotional > 0 ? closeProfit / tradeNotional * 100 : NaN;
        trade.maxRunupPercent = tradeNotional > 0 ? trade.maxRunup / tradeNotional * 100 : NaN;
        trade.maxDrawdownPercent = tradeNotional > 0 ? trade.maxDrawdown / tradeNotional * 100 : NaN;
      });
      state.openProfit = openTrades.reduce(function (total, trade) {
        return total + (trade.direction > 0 ? (bar.close - trade.entryPrice) : (trade.entryPrice - bar.close)) * trade.quantity;
      }, 0);
      state.positionSize = openTrades.reduce(function (total, trade) { return total + trade.direction * trade.quantity; }, 0);
      var longQuantity = openTrades.reduce(function (total, trade) { return total + (trade.direction > 0 ? trade.quantity : 0); }, 0);
      var shortQuantity = openTrades.reduce(function (total, trade) { return total + (trade.direction < 0 ? trade.quantity : 0); }, 0);
      if (state.positionSize > 0 && longQuantity > 0) {
        state.positionAvgPrice = openTrades.filter(function (trade) { return trade.direction > 0; }).reduce(function (total, trade) { return total + trade.entryPrice * trade.quantity; }, 0) / longQuantity;
      } else if (state.positionSize < 0 && shortQuantity > 0) {
        state.positionAvgPrice = openTrades.filter(function (trade) { return trade.direction < 0; }).reduce(function (total, trade) { return total + trade.entryPrice * trade.quantity; }, 0) / shortQuantity;
      } else {
        state.positionAvgPrice = NaN;
      }
      state.positionEntryName = openTrades.length ? openTrades[0].entryId : '';
      state.equity = state.cash + state.positionSize * bar.close;
      state.peakEquity = Math.max(state.peakEquity, state.equity);
      state.maxRunup = Math.max(state.maxRunup, state.equity - settings.initialCapital);
      state.maxDrawdown = Math.max(state.maxDrawdown, state.peakEquity - state.equity);
      state.marginUsed = openTrades.reduce(function (total, trade) {
        var marginRate = trade.direction > 0 ? settings.marginLong : settings.marginShort;
        return total + Math.abs(bar.close * trade.quantity) * marginRate / 100;
      }, 0);
      state.freeMargin = state.equity - state.marginUsed;
      state.capitalHeld = state.marginUsed;
      state.maxContractsHeldAll = Math.max(state.maxContractsHeldAll, Math.abs(state.positionSize));
      state.maxContractsHeldLong = Math.max(state.maxContractsHeldLong, Math.max(0, state.positionSize));
      state.maxContractsHeldShort = Math.max(state.maxContractsHeldShort, Math.max(0, -state.positionSize));
    }

    function snapshot(executionBar) {
      var last = executionBar || visibleBar(state.currentBar);
      if (last) markToMarket(last);
      var net = realizedNetProfit();
      var winningTrades = closedTrades.filter(function (trade) { return trade.netProfit > 0; });
      var losingTrades = closedTrades.filter(function (trade) { return trade.netProfit < 0; });
      var averageTrade = closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / closedTrades.length : 0;
      var averageTradePercent = closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / closedTrades.length : 0;
      var averageWinningTrade = winningTrades.length ? winningTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / winningTrades.length : 0;
      var averageWinningTradePercent = winningTrades.length ? winningTrades.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / winningTrades.length : 0;
      var averageLosingTrade = losingTrades.length ? losingTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / losingTrades.length : 0;
      var averageLosingTradePercent = losingTrades.length ? losingTrades.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / losingTrades.length : 0;
      return {
        initialCapital: settings.initialCapital,
        equity: state.equity,
        netprofit: net,
        netprofit_percent: settings.initialCapital ? net / settings.initialCapital * 100 : 0,
        openprofit: state.openProfit,
        openprofit_percent: state.equity - state.openProfit !== 0 ? state.openProfit / (state.equity - state.openProfit) * 100 : 0,
        position_size: state.positionSize,
        position_avg_price: state.positionAvgPrice,
        wintrades: state.wins,
        losstrades: state.losses,
        closedtrades: closedTrades.length,
        closedtrades_first_index: closedTrades.length ? 0 : NaN,
        opentrades: openTrades.length,
        grossprofit: state.grossProfit,
        grossprofit_percent: settings.initialCapital ? state.grossProfit / settings.initialCapital * 100 : 0,
        grossloss: state.grossLoss,
        grossloss_percent: settings.initialCapital ? state.grossLoss / settings.initialCapital * 100 : 0,
        avg_trade: averageTrade,
        avg_trade_percent: averageTradePercent,
        avg_winning_trade: averageWinningTrade,
        avg_winning_trade_percent: averageWinningTradePercent,
        avg_losing_trade: averageLosingTrade,
        avg_losing_trade_percent: averageLosingTradePercent,
        max_drawdown: state.maxDrawdown,
        max_drawdown_percent: state.peakEquity ? state.maxDrawdown / state.peakEquity * 100 : 0,
        max_runup: state.maxRunup,
        max_runup_percent: settings.initialCapital ? state.maxRunup / settings.initialCapital * 100 : 0,
        commission: state.totalCommission,
        cash: state.cash,
        margin_used: state.marginUsed,
        free_margin: state.freeMargin,
        capital_held: settings.marginLong > 0 || settings.marginShort > 0 ? state.capitalHeld : NaN,
        margin_liquidation_price: marginLiquidationPrice(),
        position_entry_name: state.positionEntryName,
        max_contracts_held_all: state.maxContractsHeldAll,
        max_contracts_held_long: state.maxContractsHeldLong,
        max_contracts_held_short: state.maxContractsHeldShort,
        eventrades: state.evenTrades,
        risk_halted: risk.halted,
        risk_halt_reason: risk.haltReason
      };
    }

    function realizedNetProfit() {
      return state.realizedGross - state.totalCommission;
    }

    function prepareRiskDay(index) {
      var bar = visibleBar(index);
      var key = bar ? utcDayKey(bar.time) : '';
      if (!key || key === risk.dayKey) return;
      if (risk.dayKey) {
        var previousDayProfit = realizedNetProfit() - risk.dayStartNetProfit;
        if (previousDayProfit < 0) risk.consecutiveLossDays += 1;
        else if (previousDayProfit > 0) risk.consecutiveLossDays = 0;
      }
      risk.dayKey = key;
      risk.dayStartEquity = state.equity;
      risk.dayStartNetProfit = realizedNetProfit();
      risk.dayFilledOrders = 0;
      if (risk.maxConsecutiveLossDays && risk.consecutiveLossDays >= risk.maxConsecutiveLossDays) {
        triggerRisk('max_cons_loss_days', 'Trading halted after reaching the maximum consecutive losing days.', index);
      }
    }

    function triggerRisk(type, message, index) {
      if (risk.halted) return;
      risk.halted = true;
      risk.haltReason = type;
      pending.forEach(function (order) { order.status = 'cancelled'; });
      pending.length = 0;
      diagnostic('risk-halt', message, null, index);
      if (Math.abs(state.positionSize) > 0) {
        var bar = visibleBar(index);
        if (bar) {
          applyFill({
            id: 'risk-' + type,
            kind: 'close_all',
            type: 'close',
            direction: state.positionSize > 0 ? -1 : 1,
            quantity: Math.abs(state.positionSize),
            reduceOnly: true,
            fromEntry: '',
            reason: type,
            comment: 'Risk rule: ' + type,
            status: 'pending'
          }, bar.close, index, 'close', bar);
        }
      }
    }

    function checkRisk(index) {
      if (risk.halted) return;
      var drawdownLimit = risk.maxDrawdown;
      if (drawdownLimit && drawdownLimit.value > 0) {
        var drawdownReached = String(drawdownLimit.type).toLowerCase().indexOf('percent') >= 0
          ? state.peakEquity > 0 && state.maxDrawdown / state.peakEquity * 100 >= drawdownLimit.value
          : state.maxDrawdown >= drawdownLimit.value;
        if (drawdownReached) return triggerRisk('max_drawdown', 'Trading halted after the maximum drawdown risk limit was reached.', index);
      }
      var intradayLimit = risk.maxIntradayLoss;
      if (intradayLimit && intradayLimit.value > 0) {
        var dayLoss = Math.max(0, risk.dayStartEquity - state.equity);
        var intradayReached = String(intradayLimit.type).toLowerCase().indexOf('percent') >= 0
          ? risk.dayStartEquity > 0 && dayLoss / risk.dayStartEquity * 100 >= intradayLimit.value
          : dayLoss >= intradayLimit.value;
        if (intradayReached) return triggerRisk('max_intraday_loss', 'Trading halted after the maximum intraday loss risk limit was reached.', index);
      }
      if (risk.maxIntradayFilledOrders && risk.dayFilledOrders >= risk.maxIntradayFilledOrders) {
        triggerRisk('max_intraday_filled_orders', 'Trading halted after the maximum intraday filled-order limit was reached.', index);
      }
    }

    function configureRisk(name, value, type) {
      var numericValue = number(value, NaN);
      if (name === 'allow_entry_in') risk.allowDirection = normalizeDirection(value);
      if (name === 'max_drawdown' && Number.isFinite(numericValue)) risk.maxDrawdown = { value: Math.max(0, numericValue), type: type || 'cash' };
      if (name === 'max_intraday_loss' && Number.isFinite(numericValue)) risk.maxIntradayLoss = { value: Math.max(0, numericValue), type: type || 'cash' };
      if (name === 'max_intraday_filled_orders' && Number.isFinite(numericValue)) risk.maxIntradayFilledOrders = Math.max(0, Math.floor(numericValue));
      if (name === 'max_position_size' && Number.isFinite(numericValue)) risk.maxPositionSize = Math.max(0, numericValue);
      if (name === 'max_cons_loss_days' && Number.isFinite(numericValue)) risk.maxConsecutiveLossDays = Math.max(0, Math.floor(numericValue));
      return true;
    }

    function defaultEntryQty(direction, price) {
      var bar = visibleBar(state.currentBar);
      var currentPrice = number(price, bar ? bar.close : NaN);
      return orderQuantity(settings, { quantity: null, direction: normalizeDirection(direction) }, currentPrice, snapshot(bar));
    }

    function convertToAccount(value) {
      return number(value, NaN) * settings.conversionRate;
    }

    function convertToSymbol(value) {
      return number(value, NaN) / settings.conversionRate;
    }

    function marginLiquidationPrice() {
      var quantity = Math.abs(state.positionSize);
      if (!(quantity > 0)) return NaN;
      var rate = state.positionSize > 0 ? settings.marginLong / 100 : settings.marginShort / 100;
      if (!(rate > 0) || rate >= 1) return NaN;
      if (state.positionSize > 0) return -state.cash / (quantity * (1 - rate));
      return state.cash / (quantity * (1 + rate));
    }

    function canUseOrderPrice(order, price) {
      return Number.isFinite(price) && price > 0;
    }

    function inferredIntrabarPath(bar) {
      if (!bar) return ['open', 'close'];
      return Math.abs(bar.open - bar.high) < Math.abs(bar.open - bar.low)
        ? ['open', 'high', 'low', 'close']
        : ['open', 'low', 'high', 'close'];
    }

    function intrabarTriggerIndex(order, bar) {
      if (!order || !bar) return Number.POSITIVE_INFINITY;
      var type = String(order.type || '').toLowerCase();
      if (type === 'market' || type === 'close') return 0;
      var direction = order.direction || (state.positionSize >= 0 ? -1 : 1);
      var path = inferredIntrabarPath(bar);
      var open = bar.open;
      var limit = number(order.limit, NaN);
      var stop = number(order.stop, NaN);
      if (type === 'limit' && Number.isFinite(limit)) {
        if ((direction > 0 && open <= limit) || (direction < 0 && open >= limit)) return 0;
        var limitExtreme = direction > 0 ? 'low' : 'high';
        return path.indexOf(limitExtreme) === -1 ? Number.POSITIVE_INFINITY : path.indexOf(limitExtreme);
      }
      if (type === 'stop' && Number.isFinite(stop)) {
        if ((direction > 0 && open >= stop) || (direction < 0 && open <= stop)) return 0;
        var stopExtreme = direction > 0 ? 'high' : 'low';
        return path.indexOf(stopExtreme) === -1 ? Number.POSITIVE_INFINITY : path.indexOf(stopExtreme);
      }
      if (type === 'stop_limit' && Number.isFinite(stop) && Number.isFinite(limit)) {
        var stopExtremeName = direction > 0 ? 'high' : 'low';
        var limitExtremeName = direction > 0 ? 'low' : 'high';
        var stopIndex = ((direction > 0 && open >= stop) || (direction < 0 && open <= stop)) ? 0 : path.indexOf(stopExtremeName);
        var limitIndex = ((direction > 0 && open <= limit) || (direction < 0 && open >= limit)) ? 0 : path.indexOf(limitExtremeName);
        if (stopIndex === -1 || limitIndex === -1 || limitIndex < stopIndex) return Number.POSITIVE_INFINITY;
        return limitIndex;
      }
      return Number.POSITIVE_INFINITY;
    }

    function orderLabel(order) {
      return String(order && (order.displayId || order.id || order.type) || 'order');
    }

    function isExitPriceOrder(order) {
      return !!order && order.status === 'pending' && (order.kind === 'exit' || order.reduceOnly || order.fromEntry) && (order.type === 'limit' || order.type === 'stop');
    }

    function sameExitGroup(left, right) {
      if (left.ocaName && right.ocaName && left.ocaName === right.ocaName) return true;
      return !!(left.fromEntry && right.fromEntry && left.fromEntry === right.fromEntry && left.reduceOnly && right.reduceOnly);
    }

    function recordSameBarAmbiguities(index, bar) {
      if (!bar || !isEligible(index)) return;
      var candidates = pending.filter(function (order) {
        return isExitPriceOrder(order) && shouldFillOnBar(order, bar, 'intrabar', index) && Number.isFinite(intrabarTriggerIndex(order, bar));
      });
      for (var leftIndex = 0; leftIndex < candidates.length; leftIndex += 1) {
        for (var rightIndex = leftIndex + 1; rightIndex < candidates.length; rightIndex += 1) {
          var left = candidates[leftIndex];
          var right = candidates[rightIndex];
          if (left.type === right.type || !sameExitGroup(left, right)) continue;
          var pair = [orderLabel(left), orderLabel(right)].sort();
          var key = String(index) + '|' + pair.join('|');
          if (sameBarAmbiguityKeys[key]) continue;
          sameBarAmbiguityKeys[key] = true;
          var leftTrigger = intrabarTriggerIndex(left, bar);
          var rightTrigger = intrabarTriggerIndex(right, bar);
          var first = leftTrigger <= rightTrigger ? left : right;
          var second = first === left ? right : left;
          var path = inferredIntrabarPath(bar);
          var firstKind = first.type === 'stop' ? 'stop' : 'target';
          var secondKind = second.type === 'stop' ? 'stop' : 'target';
          var sameSegment = leftTrigger === rightTrigger;
          var message = 'Daily bar touched both ' + firstKind + ' ' + orderLabel(first) + ' and ' + secondKind + ' ' + orderLabel(second) + '; ' + firstKind + ' fills first under the ' + path.join(' -> ') + ' path' + (sameSegment ? ' (same path segment, submission order used).' : '.');
          var details = {
            path: path.slice(),
            orders: [
              { id: orderLabel(left), type: left.type, triggerIndex: leftTrigger },
              { id: orderLabel(right), type: right.type, triggerIndex: rightTrigger }
            ],
            selectedFirst: orderLabel(first)
          };
          sameBarAmbiguities.push({ barIndex: index, time: bar.time, path: path.slice(), selectedFirst: orderLabel(first), orders: details.orders });
          diagnostic('same-bar-ambiguity', message, first, index, details);
        }
      }
    }

    function fillPrice(order, bar, phase) {
      if (!bar) return NaN;
      if (order.type === 'market' || order.type === 'close') return phase === 'close' ? bar.close : bar.open;
      var direction = order.direction || (state.positionSize >= 0 ? -1 : 1);
      var limit = number(order.limit, NaN);
      var stop = number(order.stop, NaN);
      if (order.type === 'limit') {
        if (!canUseOrderPrice(order, limit)) return NaN;
        if (direction > 0 && bar.low <= limit) return bar.open <= limit ? bar.open : limit;
        if (direction < 0 && bar.high >= limit) return bar.open >= limit ? bar.open : limit;
        return NaN;
      }
      if (order.type === 'stop') {
        if (!canUseOrderPrice(order, stop)) return NaN;
        if (direction > 0 && bar.high >= stop) return bar.open >= stop ? bar.open : stop;
        if (direction < 0 && bar.low <= stop) return bar.open <= stop ? bar.open : stop;
        return NaN;
      }
      if (order.type === 'stop_limit') {
        var wasActivated = !!order.activated;
        var triggeredAtOpen = (direction > 0 && bar.open >= stop) || (direction < 0 && bar.open <= stop);
        if (!order.activated) {
          if (direction > 0 && bar.high >= stop) order.activated = true;
          if (direction < 0 && bar.low <= stop) order.activated = true;
          if (order.activated) order.activatedBarIndex = bar.index;
        }
        if (order.activated && Number.isFinite(limit) && (wasActivated || triggeredAtOpen)) {
          if (direction > 0 && bar.low <= limit) return bar.open <= limit ? bar.open : limit;
          if (direction < 0 && bar.high >= limit) return bar.open >= limit ? bar.open : limit;
        }
      }
      return NaN;
    }

    function closeTrade(trade, quantity, price, order, index, time) {
      var gross = (trade.direction > 0 ? price - trade.entryPrice : trade.entryPrice - price) * quantity;
      var exitCommission = commission(settings, price, quantity);
      var entryCommission = trade.entryCommission * (quantity / trade.quantity);
      var net = gross - entryCommission - exitCommission;
      var notional = Math.abs(trade.entryPrice * quantity);
      var closed = {
        number: closedTrades.length + 1,
        entryId: trade.entryId,
        entryComment: trade.entryComment || '',
        exitId: order.displayId || order.id || order.type,
        exitComment: order.comment || '',
        direction: trade.direction > 0 ? 'long' : 'short',
        quantity: quantity,
        size: trade.direction * quantity,
        entryBarIndex: trade.entryBarIndex,
        exitBarIndex: index,
        entryTime: trade.entryTime,
        exitTime: time,
        entryPrice: trade.entryPrice,
        exitPrice: price,
        grossProfit: gross,
        commission: entryCommission + exitCommission,
        netProfit: net,
        profitPercent: notional > 0 ? net / notional * 100 : NaN,
        maxRunup: number(trade.maxRunup, 0) * (quantity / trade.quantity),
        maxRunupPercent: notional > 0 ? number(trade.maxRunup, 0) * (quantity / trade.quantity) / notional * 100 : NaN,
        maxDrawdown: number(trade.maxDrawdown, 0) * (quantity / trade.quantity),
        maxDrawdownPercent: notional > 0 ? number(trade.maxDrawdown, 0) * (quantity / trade.quantity) / notional * 100 : NaN,
        barsHeld: Math.max(0, index - trade.entryBarIndex),
        exitReason: order.reason || order.type
      };
      closedTrades.push(closed);
      state.realizedGross += gross;
      /* Entry commission is charged when the position opens; only the exit
       * commission is new at close time. The trade record keeps both sides. */
      state.totalCommission += exitCommission;
      if (net >= 0) {
        state.wins += 1;
        if (net === 0) state.evenTrades += 1;
        state.grossProfit += Math.max(0, gross);
      } else {
        state.losses += 1;
        state.grossLoss += Math.min(0, gross);
      }
      return closed;
    }

    function openTrade(order, direction, quantity, price, index, time, entryCommission) {
      openTrades.push({
        entryId: order.displayId || order.id || order.type,
        entryComment: order.comment || '',
        direction: direction,
        quantity: quantity,
        initialQuantity: quantity,
        entryPrice: price,
        entryBarIndex: index,
        entryTime: time,
        entryCommission: entryCommission,
        currentProfit: 0,
        maxRunup: 0,
        maxDrawdown: 0
      });
    }

    function applyFill(order, rawPrice, index, phase, executionBar) {
      var bar = executionBar || visibleBar(index);
      var time = bar ? bar.time : index;
      var kind = String(order.kind || order.type || '').toLowerCase();
      var isCloseKind = kind === 'close' || kind === 'close_all' || kind === 'exit' || order.reduceOnly;
      if (executions.length >= settings.maxExecutions) {
        brokerLimit('executions', 'The run reached the maximum broker execution limit; additional fills were rejected.', order, index);
        order.status = 'rejected';
        return false;
      }
      var direction = normalizeDirection(order.direction);
      if (!direction && isCloseKind) direction = state.positionSize >= 0 ? -1 : 1;
      if (!direction) {
        diagnostic('invalid-order', 'The order has no valid direction.', order, index);
        order.status = 'rejected';
        return false;
      }
      var price = orderSidePrice(settings, direction, rawPrice);
      if (!canUseOrderPrice(order, price)) {
        diagnostic('invalid-price', 'The order could not be filled at a valid price.', order, index);
        order.status = 'rejected';
        return false;
      }
      var snapshotBefore = snapshot(bar);
      var quantity;
      if (order.quantity != null) quantity = Math.abs(number(order.quantity, 0));
      else if (kind === 'close_all') quantity = Math.abs(state.positionSize);
      else if (kind === 'close' || kind === 'exit') {
        var targetTrades = openTrades.filter(function (trade) { return trade.direction !== direction && (!order.fromEntry || trade.entryId === order.fromEntry); });
        quantity = targetTrades.reduce(function (total, trade) { return total + trade.quantity; }, 0);
      } else quantity = orderQuantity(settings, order, price, snapshotBefore);
      if (order.quantity == null && Number.isFinite(order.qtyPercent)) quantity = Math.abs(state.positionSize) * Math.max(0, Math.min(100, order.qtyPercent)) / 100;
      if (kind === 'entry' && state.positionSize && direction !== (state.positionSize > 0 ? 1 : -1)) quantity += Math.abs(state.positionSize);
      if (!(quantity > 0)) {
        if (kind === 'exit') return false;
        diagnostic('invalid-quantity', 'The order quantity must be greater than zero.', order, index);
        order.status = 'rejected';
        return false;
      }
      if (isCloseKind) quantity = Math.min(quantity, Math.abs(state.positionSize));
      if (!(quantity > 0)) {
        order.status = 'cancelled';
        return false;
      }
      if (kind === 'entry' && Number.isFinite(risk.maxPositionSize)) {
        var currentSameDirection = openTrades.reduce(function (total, trade) { return total + (trade.direction === direction ? trade.quantity : 0); }, 0);
        var requestedEntryQuantity = Math.max(0, quantity - (state.positionSize && direction !== (state.positionSize > 0 ? 1 : -1) ? Math.abs(state.positionSize) : 0));
        var allowedEntryQuantity = Math.max(0, risk.maxPositionSize - currentSameDirection);
        if (requestedEntryQuantity > allowedEntryQuantity) {
          diagnostic('risk', 'The entry quantity was reduced by strategy.risk.max_position_size.', order, index);
          quantity -= requestedEntryQuantity - allowedEntryQuantity;
        }
      }
      if (!(quantity > 0)) {
        order.status = 'cancelled';
        return false;
      }
      var margin = Math.abs(price * quantity) * (direction > 0 ? settings.marginLong : settings.marginShort) / 100;
      if (margin > snapshotBefore.equity && !isCloseKind) {
        diagnostic('margin', 'The order was rejected because required margin exceeds equity.', order, index);
        order.status = 'rejected';
        return false;
      }
      var commissionValue = commission(settings, price, quantity);
      var remaining = quantity;
      if (state.positionSize && direction !== (state.positionSize > 0 ? 1 : -1)) {
        var opposite = openTrades.filter(function (trade) { return trade.direction !== direction; });
        if (kind === 'exit' && order.fromEntry) opposite = opposite.filter(function (trade) { return trade.entryId === order.fromEntry; });
        if (kind === 'close' && order.fromEntry && settings.closeEntriesRule === 'ANY') {
          opposite.sort(function (left, right) { return (left.entryId === order.fromEntry ? 0 : 1) - (right.entryId === order.fromEntry ? 0 : 1) || left.entryBarIndex - right.entryBarIndex; });
        }
        for (var oppositeIndex = 0; oppositeIndex < opposite.length && remaining > 0; oppositeIndex += 1) {
          var trade = opposite[oppositeIndex];
          var closeQuantity = Math.min(remaining, trade.quantity);
          closeTrade(trade, closeQuantity, price, order, index, time);
          trade.quantity -= closeQuantity;
          trade.entryCommission *= trade.quantity > 0 ? (trade.quantity / (trade.quantity + closeQuantity)) : 0;
          remaining -= closeQuantity;
          if (trade.quantity <= 1e-12) openTrades.splice(openTrades.indexOf(trade), 1);
        }
      }
      if (remaining > 0 && !isCloseKind) {
        var sameDirectionCount = openTrades.filter(function (trade) { return trade.direction === direction; }).length;
        if (sameDirectionCount > 0 && sameDirectionCount >= Math.max(1, settings.pyramiding + 1) && kind === 'entry') {
          diagnostic('pyramiding', 'The order was ignored because the pyramiding limit was reached.', order, index);
          order.status = 'cancelled';
          return false;
        }
        var entryCommission = commission(settings, price, remaining);
        openTrade(order, direction, remaining, price, index, time, entryCommission);
        state.totalCommission += entryCommission;
      }
      state.cash -= direction * price * quantity + commissionValue;
      state.totalSlippage += Math.abs(price - rawPrice) * quantity;
      executions.push({
        orderId: order.displayId || order.id || order.type,
        type: order.type,
        role: isCloseKind ? 'exit' : 'entry',
        direction: direction > 0 ? 'long' : 'short',
        quantity: quantity,
        price: price,
        requestedPrice: rawPrice,
        commission: commissionValue,
        fromEntry: order.fromEntry || '',
        comment: order.comment || '',
        barIndex: index,
        time: time,
        executionBarIndex: executionBar && executionBar.index != null ? executionBar.index : null,
        executionTime: time,
        phase: phase || 'open',
        reason: order.reason || order.type
      });
      order.status = 'filled';
      order.filledQuantity = quantity;
      risk.dayFilledOrders += 1;
      if (order.ocaName) {
        pending.slice().forEach(function (sibling) {
          if (sibling === order || sibling.status !== 'pending' || sibling.ocaName !== order.ocaName) return;
          var ocaType = String(order.ocaType || 'cancel').toLowerCase();
          if (ocaType === 'cancel') {
            sibling.status = 'cancelled';
            pending.splice(pending.indexOf(sibling), 1);
          } else if (ocaType === 'reduce') {
            sibling.quantity = Math.max(0, number(sibling.quantity, 0) - number(order.filledQuantity, 0));
            if (!(sibling.quantity > 0)) {
              sibling.status = 'cancelled';
              pending.splice(pending.indexOf(sibling), 1);
            }
          }
        });
      }
      markToMarket(bar);
      return true;
    }

    function shouldFillOnBar(order, bar, phase, chartIndex) {
      if (!order || order.status !== 'pending') return false;
      if (order.createdBarIndex >= chartIndex && phase === 'open' && order.type === 'market') return false;
      if (order.createdBarIndex > chartIndex) return false;
      return true;
    }

    function fillPending(index, phase, executionBar) {
      var bar = executionBar || visibleBar(index);
      if (!bar || !isEligible(index)) return;
      if (executionBar === visibleBar(index)) recordSameBarAmbiguities(index, bar);
      pending.slice().sort(function (left, right) {
        return intrabarTriggerIndex(left, bar) - intrabarTriggerIndex(right, bar);
      }).forEach(function (order) {
        if (!shouldFillOnBar(order, bar, phase, index)) return;
        if (order.trailing && Number.isFinite(order.trailOffset)) {
          var trailingDistance = order.trailOffset * settings.tickSize;
          if (!order.trailingActive) {
            if ((order.direction < 0 && bar.high >= order.activationPrice) || (order.direction > 0 && bar.low <= order.activationPrice)) {
              order.trailingActive = true;
            } else {
              return;
            }
          }
          if (order.direction < 0) {
            var longTrail = bar.high - trailingDistance;
            order.stop = Number.isFinite(order.stop) ? Math.max(order.stop, longTrail) : longTrail;
          } else {
            var shortTrail = bar.low + trailingDistance;
            order.stop = Number.isFinite(order.stop) ? Math.min(order.stop, shortTrail) : shortTrail;
          }
        }
        var price = fillPrice(order, bar, phase);
        if (!Number.isFinite(price)) return;
        if (order.type === 'limit' && settings.limitVerificationTicks > 0 && Math.abs(price - number(order.limit, price)) < settings.limitVerificationTicks * settings.tickSize) return;
        applyFill(order, price, index, phase, bar);
        if (order.status !== 'pending') pending.splice(pending.indexOf(order), 1);
      });
    }

    function executionBarsFor(index) {
      var mainBar = visibleBar(index);
      if (!mainBar || !settings.barMagnifier) return mainBar ? [mainBar] : [];
      var intrabars = lowerBarsByChart[index] || [];
      if (intrabars.length) return intrabars;
      if (!barMagnifierFallbackKeys[index]) {
        barMagnifierFallbackKeys[index] = true;
        barMagnifierFallbacks.push(index);
        diagnostic('bar-magnifier-fallback', 'No ' + (settings.barMagnifierMode === 'daily-eod' ? 'daily EOD magnifier bars' : 'lower-timeframe bars') + ' covered chart bar ' + index + '; the chart OHLC bar was used for fills.', null, index);
      }
      return [mainBar];
    }

    function lastExecutionBar(index) {
      var executionBars = executionBarsFor(index);
      return executionBars.length ? executionBars[executionBars.length - 1] : visibleBar(index);
    }

    function makeOrder(intent, index) {
      intent = intent || {};
      var type = String(intent.type || 'order').toLowerCase();
      var order = {
        id: String(intent.id || type + '-' + index + '-' + (pending.length + executions.length + 1)),
        displayId: String(intent.displayId || intent.id || type),
        kind: type,
        type: type,
        direction: normalizeDirection(intent.direction),
        quantity: intent.quantity == null ? null : Math.abs(number(intent.quantity, 0)),
        limit: number(intent.limit, NaN),
        stop: number(intent.stop, NaN),
        reduceOnly: !!intent.reduceOnly,
        fromEntry: String(intent.fromEntry || intent.from || ''),
        createdBarIndex: index,
        status: 'pending',
        reason: intent.reason || type,
        profit: number(intent.profit, NaN),
        loss: number(intent.loss, NaN),
        qtyPercent: number(intent.qty_percent != null ? intent.qty_percent : intent.qtyPercent, NaN),
        trailPoints: number(intent.trail_points != null ? intent.trail_points : intent.trailPoints, NaN),
        trailOffset: number(intent.trail_offset != null ? intent.trail_offset : intent.trailOffset, NaN),
        trailPrice: number(intent.trail_price != null ? intent.trail_price : intent.trailPrice, NaN),
        ocaName: String(intent.oca_name != null ? intent.oca_name : intent.ocaName || ''),
        ocaType: String(intent.oca_type != null ? intent.oca_type : intent.ocaType || 'cancel'),
        comment: String(intent.comment || ''),
        alertMessage: String(intent.alert_message != null ? intent.alert_message : intent.alertMessage || ''),
        alertProfit: String(intent.alert_profit != null ? intent.alert_profit : intent.alertProfit || ''),
        alertLoss: String(intent.alert_loss != null ? intent.alert_loss : intent.alertLoss || ''),
        alertTrailing: String(intent.alert_trailing != null ? intent.alert_trailing : intent.alertTrailing || ''),
        disableAlert: !!(intent.disable_alert != null ? intent.disable_alert : intent.disableAlert),
        immediately: !!intent.immediately
      };
      if (order.quantity == null && !Number.isFinite(order.qtyPercent) && (type === 'close' || type === 'close_all' || type === 'exit')) {
        var targetDirection = state.positionSize >= 0 ? 1 : -1;
        var targetTrades = openTrades.filter(function (trade) {
          return trade.direction === targetDirection && (!order.fromEntry || trade.entryId === order.fromEntry);
        });
        var targetQuantity = targetTrades.reduce(function (total, trade) { return total + trade.quantity; }, 0);
        if (targetQuantity > 0 || type !== 'exit') order.quantity = targetQuantity;
      }
      if (type === 'close_all') {
        order.type = 'close';
        order.direction = state.positionSize >= 0 ? -1 : 1;
        order.reduceOnly = true;
      }
      if (type === 'close') {
        order.reduceOnly = true;
        order.direction = state.positionSize >= 0 ? -1 : 1;
      }
      if (type === 'exit') {
        order.reduceOnly = true;
        order.direction = state.positionSize >= 0 ? -1 : 1;
        var positionDirection = state.positionSize >= 0 ? 1 : -1;
        if (!Number.isFinite(order.limit) && Number.isFinite(order.profit) && Number.isFinite(state.positionAvgPrice)) order.limit = state.positionAvgPrice + positionDirection * order.profit * settings.tickSize;
        if (!Number.isFinite(order.stop) && Number.isFinite(order.loss) && Number.isFinite(state.positionAvgPrice)) order.stop = state.positionAvgPrice - positionDirection * order.loss * settings.tickSize;
        if (!Number.isFinite(order.stop) && Number.isFinite(order.trailOffset) && Number.isFinite(state.positionAvgPrice)) {
          order.stop = state.positionAvgPrice - positionDirection * order.trailOffset * settings.tickSize;
          order.trailing = true;
          order.trailingActive = !Number.isFinite(order.trailPoints) && !Number.isFinite(order.trailPrice);
          order.activationPrice = Number.isFinite(order.trailPrice) ? order.trailPrice : state.positionAvgPrice + positionDirection * order.trailPoints * settings.tickSize;
        }
        if (Number.isFinite(order.limit)) order.type = 'limit';
        else if (Number.isFinite(order.stop)) order.type = 'stop';
        else order.type = 'close';
        if (!order.ocaName) order.ocaName = '__exit_' + order.displayId;
        if (!intent.oca_type && !intent.ocaType) order.ocaType = 'reduce';
      }
      if (order.type === 'entry' || order.type === 'order') {
        if (Number.isFinite(order.stop) && Number.isFinite(order.limit)) order.type = 'stop_limit';
        else if (Number.isFinite(order.stop)) order.type = 'stop';
        else if (Number.isFinite(order.limit)) order.type = 'limit';
        else order.type = 'market';
      }
      return order;
    }

    function submit(intents, index) {
      var replacements = Object.create(null);
      (Array.isArray(intents) ? intents : []).forEach(function (intent) {
        var intentType = String(intent && intent.type || '').toLowerCase();
        if (!isEligible(index)) {
          diagnostic('date-range', 'The order was ignored outside the selected backtest range or warm-up period.', intent, index);
          return;
        }
        if (intent && ['entry', 'order', 'exit', 'close', 'close_all'].indexOf(intentType) !== -1) {
          if (submittedOrderCount >= settings.maxOrders) {
            brokerLimit('orders', 'The run reached the maximum broker order limit; additional orders were ignored.', intent, index);
            return;
          }
          if (['entry', 'order'].indexOf(String(intent.type || '').toLowerCase()) !== -1 &&
              closedTrades.length + openTrades.length >= settings.maxTrades &&
              (!state.positionSize || normalizeDirection(intent.direction) === (state.positionSize > 0 ? 1 : -1))) {
            brokerLimit('trades', 'The run reached the maximum trade limit; additional entries were ignored.', intent, index);
            return;
          }
          submittedOrderCount += 1;
        }
        var requestedDirection = normalizeDirection(intent && intent.direction);
        if ((intentType === 'entry' || intentType === 'order') && ((settings.directionMode === 'long' && requestedDirection < 0) || (settings.directionMode === 'short' && requestedDirection > 0))) {
          diagnostic('risk', 'The order was rejected by the selected trading direction filter.', intent, index);
          return;
        }
        if (risk.halted && intentType !== 'cancel' && intentType !== 'cancel_all') return;
        if (intentType === 'entry' && risk.allowDirection && requestedDirection && requestedDirection !== risk.allowDirection) {
          if (state.positionSize && state.positionSize * requestedDirection < 0) {
            intent = { type: 'close_all', id: String(intent.id || 'risk-close'), comment: 'strategy.risk.allow_entry_in' };
            intentType = 'close_all';
          } else {
            diagnostic('risk', 'The entry was rejected by strategy.risk.allow_entry_in.', intent, index);
            return;
          }
        }
        var type = String(intent && intent.type || '').toLowerCase();
        if (type === 'cancel_all') {
          pending.forEach(function (order) { order.status = 'cancelled'; });
          pending.length = 0;
          return;
        }
        if (type === 'cancel') {
          pending.slice().forEach(function (order) {
            if (order.id === String(intent.id || '') || order.displayId === String(intent.id || '')) { order.status = 'cancelled'; pending.splice(pending.indexOf(order), 1); }
          });
          return;
        }
        if (type === 'entry' || type === 'order' || type === 'exit') {
          var replacementId = String(intent.displayId || intent.id || '');
          if (replacementId && !replacements[replacementId]) {
            pending.slice().forEach(function (existing) {
              if (existing.displayId === replacementId || existing.id === replacementId) {
                existing.status = 'cancelled';
                pending.splice(pending.indexOf(existing), 1);
              }
            });
            replacements[replacementId] = true;
          }
        }
        var order = makeOrder(intent, index);
        pending.push(order);
        if ((settings.processOrdersOnClose || order.immediately) && (order.type === 'market' || order.type === 'close')) fillPending(index, 'close', lastExecutionBar(index));
      });
    }

    function beginBar(index) {
      state.currentBar = index;
      state.currentTime = visibleBar(index) && visibleBar(index).time;
      prepareRiskDay(index);
      var executionBars = executionBarsFor(index);
      executionBars.forEach(function (executionBar, executionIndex) {
        fillPending(index, executionIndex === 0 ? 'open' : 'intrabar', executionBar);
      });
      markToMarket(visibleBar(index));
      return snapshot();
    }

    function endBar(index) {
      state.currentBar = index;
      markToMarket(visibleBar(index));
      checkRisk(index);
      equityCurve.push({ time: visibleBar(index).time, value: state.equity, barIndex: index });
      drawdownCurve.push({ time: visibleBar(index).time, value: state.peakEquity - state.equity, barIndex: index });
      var firstClose = bars.length && Number(bars[0].close);
      buyAndHoldCurve.push({ time: visibleBar(index).time, value: Number.isFinite(firstClose) && firstClose !== 0 ? settings.initialCapital * visibleBar(index).close / firstClose : settings.initialCapital, barIndex: index });
      var current = snapshot();
      stateCurve.push({
        time: visibleBar(index).time,
        barIndex: index,
        equity: current.equity,
        netprofit: current.netprofit,
        openprofit: current.openprofit,
        position_size: current.position_size,
        position_avg_price: current.position_avg_price,
        wintrades: current.wintrades,
        losstrades: current.losstrades,
        closedtrades: current.closedtrades,
        closedtrades_first_index: current.closedtrades_first_index,
        opentrades: current.opentrades,
        max_drawdown: current.max_drawdown,
        max_runup: current.max_runup,
        margin_used: current.margin_used,
        free_margin: current.free_margin,
        capital_held: current.capital_held,
        margin_liquidation_price: current.margin_liquidation_price,
        position_entry_name: current.position_entry_name,
        max_contracts_held_all: current.max_contracts_held_all,
        max_contracts_held_long: current.max_contracts_held_long,
        max_contracts_held_short: current.max_contracts_held_short,
        eventrades: current.eventrades,
        risk_halted: current.risk_halted,
        risk_halt_reason: current.risk_halt_reason
      });
      return current;
    }

    function metrics() {
      var net = state.realizedGross - state.totalCommission;
      var grossLoss = Math.abs(state.grossLoss);
      var returns = equityCurve.filter(function (point, index) { return index === 0 || equityCurve[index - 1].value !== 0; }).map(function (point, index) {
        var previous = index ? equityCurve[index - 1].value : settings.initialCapital;
        return previous ? (point.value - previous) / previous : 0;
      });
      var mean = returns.length ? returns.reduce(function (sum, value) { return sum + value; }, 0) / returns.length : 0;
      var variance = returns.length > 1 ? returns.reduce(function (sum, value) { return sum + Math.pow(value - mean, 2); }, 0) / (returns.length - 1) : 0;
      var downsideVariance = returns.length ? returns.reduce(function (sum, value) { return sum + Math.pow(Math.min(0, value), 2); }, 0) / returns.length : 0;
      var longTrades = closedTrades.filter(function (trade) { return trade.direction === 'long'; });
      var shortTrades = closedTrades.filter(function (trade) { return trade.direction === 'short'; });
      var winning = closedTrades.filter(function (trade) { return trade.netProfit > 0; });
      var losing = closedTrades.filter(function (trade) { return trade.netProfit < 0; });
      var even = closedTrades.filter(function (trade) { return trade.netProfit === 0; });
      var average = closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / closedTrades.length : 0;
      var averagePercent = closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / closedTrades.length : 0;
      var averageWinning = winning.length ? winning.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / winning.length : 0;
      var averageWinningPercent = winning.length ? winning.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / winning.length : 0;
      var averageLosing = losing.length ? losing.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / losing.length : 0;
      var averageLosingPercent = losing.length ? losing.reduce(function (sum, trade) { return sum + number(trade.profitPercent, 0); }, 0) / losing.length : 0;
      function periodReturns(unit) {
        var groups = Object.create(null);
        equityCurve.forEach(function (point) {
          var date = new Date(point.time > 100000000000 ? point.time : point.time * 1000);
          if (!Number.isFinite(date.getTime())) return;
          var key = unit === 'year' ? String(date.getUTCFullYear()) : date.getUTCFullYear() + '-' + String(date.getUTCMonth() + 1).padStart(2, '0');
          if (!groups[key]) groups[key] = { key: key, first: point.value, last: point.value };
          groups[key].last = point.value;
        });
        return Object.keys(groups).sort().map(function (key) {
          var group = groups[key];
          var base = key === Object.keys(groups).sort()[0] ? settings.initialCapital : group.first;
          return { period: key, value: base ? (group.last - base) / base * 100 : 0 };
        });
      }
      return {
        netProfit: net,
        netReturnPercent: settings.initialCapital ? net / settings.initialCapital * 100 : 0,
        grossProfit: state.grossProfit,
        grossLoss: state.grossLoss,
        profitFactor: grossLoss ? state.grossProfit / grossLoss : state.grossProfit > 0 ? Infinity : 0,
        totalTrades: closedTrades.length,
        winningTrades: state.wins,
        losingTrades: state.losses,
        evenTrades: even.length,
        winRatePercent: closedTrades.length ? state.wins / closedTrades.length * 100 : 0,
        averageTrade: average,
        averageTradePercent: averagePercent,
        averageWinningTrade: averageWinning,
        averageWinningTradePercent: averageWinningPercent,
        averageLosingTrade: averageLosing,
        averageLosingTradePercent: averageLosingPercent,
        largestWinningTrade: closedTrades.reduce(function (max, trade) { return Math.max(max, trade.netProfit); }, 0),
        largestLosingTrade: closedTrades.reduce(function (min, trade) { return Math.min(min, trade.netProfit); }, 0),
        longTrades: longTrades.length,
        shortTrades: shortTrades.length,
        longNetProfit: longTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0),
        shortNetProfit: shortTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0),
        commission: state.totalCommission,
        slippage: state.totalSlippage,
        maxDrawdown: state.maxDrawdown,
        maxDrawdownPercent: state.peakEquity ? state.maxDrawdown / state.peakEquity * 100 : 0,
        maxRunup: state.maxRunup,
        maxRunupPercent: settings.initialCapital ? state.maxRunup / settings.initialCapital * 100 : 0,
        endingEquity: state.equity,
        openProfit: state.openProfit,
        openProfitPercent: state.equity - state.openProfit !== 0 ? state.openProfit / (state.equity - state.openProfit) * 100 : 0,
        netProfitPercent: settings.initialCapital ? net / settings.initialCapital * 100 : 0,
        grossProfitPercent: settings.initialCapital ? state.grossProfit / settings.initialCapital * 100 : 0,
        grossLossPercent: settings.initialCapital ? state.grossLoss / settings.initialCapital * 100 : 0,
        maxContractsHeldAll: state.maxContractsHeldAll,
        maxContractsHeldLong: state.maxContractsHeldLong,
        maxContractsHeldShort: state.maxContractsHeldShort,
        marginLiquidationPrice: marginLiquidationPrice(),
        averageBarsInTrade: closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + trade.barsHeld; }, 0) / closedTrades.length : 0,
        sharpe: variance > 0 ? mean / Math.sqrt(variance) * Math.sqrt(returns.length || 1) : 0,
        sortino: downsideVariance > 0 ? mean / Math.sqrt(downsideVariance) * Math.sqrt(returns.length || 1) : 0,
        recoveryFactor: state.maxDrawdown > 0 ? net / state.maxDrawdown : 0,
        calmar: state.maxDrawdown > 0 && settings.initialCapital > 0 ? (net / settings.initialCapital) / (state.maxDrawdown / settings.initialCapital) : 0,
        buyAndHoldPercent: bars.length && bars[0].close ? (bars[bars.length - 1].close / bars[0].close - 1) * 100 : 0,
        monthlyReturns: periodReturns('month'),
        yearlyReturns: periodReturns('year')
      };
    }

    function result() {
      markToMarket(visibleBar(state.currentBar));
      var tradeNet = closedTrades.reduce(function (total, trade) { return total + number(trade.netProfit, 0); }, 0);
      var closedGross = closedTrades.reduce(function (total, trade) { return total + number(trade.grossProfit, 0); }, 0);
      var closedCommission = closedTrades.reduce(function (total, trade) { return total + number(trade.commission, 0); }, 0);
      var expectedNet = closedGross - closedCommission;
      if (Math.abs(tradeNet - expectedNet) > 1e-8) {
        diagnostic('ledger-integrity', 'Closed-trade net profit does not reconcile with realized profit and costs.', null, state.currentBar);
      }
      var markedEquity = state.cash + state.positionSize * state.currentPrice;
      if (Number.isFinite(markedEquity) && Math.abs(markedEquity - state.equity) > 1e-8) {
        diagnostic('ledger-integrity', 'Equity does not reconcile with cash and the marked position value.', null, state.currentBar);
      }
      var config = {};
      Object.keys(settings).forEach(function (key) { config[key] = settings[key]; });
      config.intrabarPathPolicy = INTRABAR_PATH_POLICY;
      config.executionMode = settings.barMagnifier && lowerBars.length
        ? (settings.barMagnifierMode === 'daily-eod' ? 'daily-eod-magnifier' : 'lower-timeframe')
        : 'eod-ohlc';
      config.sameBarAmbiguityCount = sameBarAmbiguities.length;
      config.lowerBarCount = lowerBars.length;
      config.lowerTimeframeCoverage = {
        chartBars: bars.length,
        coveredChartBars: lowerBarsCoveredChartBars,
        uncoveredChartBars: Math.max(0, bars.length - lowerBarsCoveredChartBars),
        lowerBars: lowerBars.length,
        timeframe: settings.lowerTimeframe || null,
        source: settings.barMagnifierMode === 'daily-eod' ? 'host daily EOD bars' : settings.lowerTimeframe ? 'host-supplied bars' : null
      };
      config.barMagnifierFallbacks = barMagnifierFallbacks.slice();
      return {
        engineVersion: VERSION,
        config: config,
        assumptions: {
          data: 'End-of-day OHLCV bars',
          executionMode: config.executionMode,
          fillMode: EOD_FILL_MODE,
          intrabarPath: INTRABAR_PATH_POLICY,
          intrabarPathDescription: 'When no lower-timeframe bars are supplied, the emulator infers open to high to low to close when the high is closer to the open; otherwise it infers open to low to high to close.',
          sameBarStopTarget: 'If a daily candle touches both a stop and a target, the first level touched along the inferred path fills first. The run emits a same-bar-ambiguity warning, and OCA siblings are then cancelled or reduced according to their OCA rule.',
          gapFill: 'If a price order is crossed between the previous close and the current open, it fills at the current open rather than at the requested level.',
          dailyEodMagnifier: 'On weekly, monthly, quarterly, and yearly chart bars, host-loaded daily EOD bars can refine the order sequence inside each chart bar. Daily candles still use the documented OHLC path.',
          lowerTimeframeOverride: 'Host-supplied lower-timeframe bars override the inferred path for covered chart bars.'
        },
        dataQuality: dataQuality,
        executions: executions.slice(),
        trades: closedTrades.slice(),
        openTrades: openTrades.map(function (trade) {
          var copy = {};
          Object.keys(trade).forEach(function (key) { copy[key] = trade[key]; });
          return copy;
        }),
        pendingOrders: pending.slice(),
        sameBarAmbiguities: sameBarAmbiguities.slice(),
        limits: {
          maxBars: settings.maxBars,
          maxOrders: settings.maxOrders,
          maxExecutions: settings.maxExecutions,
          maxTrades: settings.maxTrades,
          submittedOrders: submittedOrderCount,
          executions: executions.length,
          closedTrades: closedTrades.length
        },
        risk: {
          halted: risk.halted,
          haltReason: risk.haltReason,
          allowDirection: risk.allowDirection,
          maxDrawdown: risk.maxDrawdown,
          maxIntradayLoss: risk.maxIntradayLoss,
          maxIntradayFilledOrders: risk.maxIntradayFilledOrders,
          maxPositionSize: risk.maxPositionSize,
          maxConsecutiveLossDays: risk.maxConsecutiveLossDays,
          consecutiveLossDays: risk.consecutiveLossDays
        },
        equityCurve: equityCurve.slice(),
        drawdownCurve: drawdownCurve.slice(),
        buyAndHoldCurve: buyAndHoldCurve.slice(),
        stateCurve: stateCurve.slice(),
        metrics: metrics(),
        diagnostics: diagnostics.slice(),
        state: snapshot()
      };
    }

    if (settings.calcOnEveryTick) diagnostic('execution-mode', 'calc_on_every_tick is accepted, but historical chart bars are still the primary calculation boundary in this browser engine.', null, -1);

    return {
      version: VERSION,
      settings: settings,
      bars: bars,
      beginBar: beginBar,
      submit: submit,
      endBar: endBar,
      snapshot: snapshot,
      configureRisk: configureRisk,
      defaultEntryQty: defaultEntryQty,
      convertToAccount: convertToAccount,
      convertToSymbol: convertToSymbol,
      result: result,
      get pendingOrders() { return pending; },
      get executions() { return executions; },
      get closedTrades() { return closedTrades; },
      get openTrades() { return openTrades; },
      get diagnostics() { return diagnostics; }
    };
  }

  return {
    VERSION: VERSION,
    normalizeSettings: normalizeSettings,
    createSession: createSession
  };
}));
