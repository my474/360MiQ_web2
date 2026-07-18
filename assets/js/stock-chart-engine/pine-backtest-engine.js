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

  var VERSION = '0.1.0';

  function number(value, fallback) {
    var result = Number(value);
    return Number.isFinite(result) ? result : fallback;
  }

  function positive(value, fallback) {
    var result = number(value, fallback);
    return result > 0 ? result : fallback;
  }

  function normalizeDirection(value) {
    if (value === 1 || value === '1' || String(value).toLowerCase() === 'long') return 1;
    if (value === -1 || value === '-1' || String(value).toLowerCase() === 'short') return -1;
    return 0;
  }

  function normalizeBars(bars) {
    return (Array.isArray(bars) ? bars : []).map(function (bar, index) {
      return {
        sourceIndex: index,
        time: number(bar && bar.time, index),
        open: number(bar && bar.open, NaN),
        high: number(bar && bar.high, NaN),
        low: number(bar && bar.low, NaN),
        close: number(bar && bar.close, NaN),
        volume: number(bar && bar.volume, 0)
      };
    }).filter(function (bar) {
      return Number.isFinite(bar.open) && Number.isFinite(bar.high) && Number.isFinite(bar.low) && Number.isFinite(bar.close);
    }).sort(function (left, right) {
      return left.time - right.time || left.sourceIndex - right.sourceIndex;
    }).map(function (bar, index) {
      bar.index = index;
      delete bar.sourceIndex;
      return bar;
    });
  }

  function normalizeSettings(input) {
    input = input || {};
    var commissionType = String(input.commissionType || input.commission_type || 'percent').toLowerCase();
    if (['percent', 'cash_per_order', 'cash_per_contract'].indexOf(commissionType) === -1) commissionType = 'percent';
    var orderQtyType = String(input.defaultQtyType || input.default_qty_type || input.orderQtyType || 'fixed').toLowerCase();
    if (['fixed', 'cash', 'percent_of_equity'].indexOf(orderQtyType) === -1) orderQtyType = 'fixed';
    var directionMode = String(input.directionMode || input.direction_mode || 'both').toLowerCase();
    if (['both', 'long', 'short'].indexOf(directionMode) === -1) directionMode = 'both';
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
      processOrdersOnClose: input.processOrdersOnClose === true || input.process_orders_on_close === true,
      calcOnOrderFills: input.calcOnOrderFills === true || input.calc_on_order_fills === true,
      calcOnEveryTick: input.calcOnEveryTick === true || input.calc_on_every_tick === true,
      fillMode: String(input.fillMode || input.fill_mode || 'ohlc').toLowerCase(),
      limitVerificationTicks: Math.max(0, number(input.limitVerificationTicks != null ? input.limitVerificationTicks : input.backtest_fill_limits_assumption, 0)),
      dateFrom: input.dateFrom == null ? null : number(input.dateFrom, null),
      dateTo: input.dateTo == null ? null : number(input.dateTo, null),
      warmupBars: Math.max(0, Math.floor(number(input.warmupBars != null ? input.warmupBars : input.warmup_bars, 0))),
      symbol: String(input.symbol || ''),
      timeframe: String(input.timeframe || '1D')
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
    var bars = normalizeBars(barsInput);
    var pending = [];
    var executions = [];
    var closedTrades = [];
    var openTrades = [];
    var diagnostics = [];
    var equityCurve = [];
    var drawdownCurve = [];
    var stateCurve = [];
    var rawBars = Array.isArray(barsInput) ? barsInput : [];
    var previousTime = -Infinity;
    var seenTimes = Object.create(null);
    rawBars.forEach(function (bar, index) {
      var time = number(bar && bar.time, NaN);
      var valid = Number.isFinite(time) && Number.isFinite(number(bar && bar.open, NaN)) && Number.isFinite(number(bar && bar.high, NaN)) && Number.isFinite(number(bar && bar.low, NaN)) && Number.isFinite(number(bar && bar.close, NaN));
      if (!valid) {
        diagnostics.push({ type: 'data', message: 'Bar ' + index + ' was ignored because it is missing a valid OHLC field.', orderId: null, barIndex: index });
        return;
      }
      if (time < previousTime) diagnostics.push({ type: 'data-order', message: 'Input bars were not chronological; the broker sorted them by timestamp.', orderId: null, barIndex: index });
      if (seenTimes[String(time)]) diagnostics.push({ type: 'duplicate-time', message: 'Duplicate bar timestamp detected; source order was preserved for equal timestamps.', orderId: null, barIndex: index });
      seenTimes[String(time)] = true;
      previousTime = time;
    });
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
      grossLoss: 0
    };

    function isEligible(index) {
      var bar = visibleBar(index);
      if (!bar || index < settings.warmupBars) return false;
      if (settings.dateFrom != null && bar.time < settings.dateFrom) return false;
      if (settings.dateTo != null && bar.time > settings.dateTo) return false;
      return true;
    }

    function diagnostic(type, message, order, index) {
      diagnostics.push({ type: type, message: message, orderId: order && order.id || null, barIndex: index == null ? state.currentBar : index });
    }

    function visibleBar(index) {
      return bars[Math.max(0, Math.min(bars.length - 1, index))] || null;
    }

    function markToMarket(bar) {
      if (!bar) return;
      state.currentPrice = bar.close;
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
      state.equity = settings.initialCapital + state.realizedGross - state.totalCommission + state.openProfit;
      state.peakEquity = Math.max(state.peakEquity, state.equity);
      state.maxRunup = Math.max(state.maxRunup, state.equity - settings.initialCapital);
      state.maxDrawdown = Math.max(state.maxDrawdown, state.peakEquity - state.equity);
    }

    function snapshot() {
      var last = visibleBar(state.currentBar);
      if (last) markToMarket(last);
      return {
        initialCapital: settings.initialCapital,
        equity: state.equity,
        netprofit: state.realizedGross - state.totalCommission,
        openprofit: state.openProfit,
        position_size: state.positionSize,
        position_avg_price: state.positionAvgPrice,
        wintrades: state.wins,
        losstrades: state.losses,
        closedtrades: closedTrades.length,
        opentrades: openTrades.length,
        grossprofit: state.grossProfit,
        grossloss: state.grossLoss,
        max_drawdown: state.maxDrawdown,
        max_runup: state.maxRunup,
        commission: state.totalCommission,
        cash: state.cash
      };
    }

    function canUseOrderPrice(order, price) {
      return Number.isFinite(price) && price > 0;
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
        if (!order.activated) {
          if (direction > 0 && bar.high >= stop) order.activated = true;
          if (direction < 0 && bar.low <= stop) order.activated = true;
        }
        if (order.activated && Number.isFinite(limit)) {
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
      var closed = {
        number: closedTrades.length + 1,
        entryId: trade.entryId,
        exitId: order.id || order.type,
        direction: trade.direction > 0 ? 'long' : 'short',
        quantity: quantity,
        entryBarIndex: trade.entryBarIndex,
        exitBarIndex: index,
        entryTime: trade.entryTime,
        exitTime: time,
        entryPrice: trade.entryPrice,
        exitPrice: price,
        grossProfit: gross,
        commission: entryCommission + exitCommission,
        netProfit: net,
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
        state.grossProfit += Math.max(0, gross);
      } else {
        state.losses += 1;
        state.grossLoss += Math.min(0, gross);
      }
      return closed;
    }

    function openTrade(order, direction, quantity, price, index, time, entryCommission) {
      openTrades.push({
        entryId: order.id || order.type,
        direction: direction,
        quantity: quantity,
        entryPrice: price,
        entryBarIndex: index,
        entryTime: time,
        entryCommission: entryCommission
      });
    }

    function applyFill(order, rawPrice, index, phase) {
      var bar = visibleBar(index);
      var time = bar ? bar.time : index;
      var direction = normalizeDirection(order.direction);
      if (!direction && (order.type === 'close' || order.type === 'exit')) direction = state.positionSize >= 0 ? -1 : 1;
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
      var snapshotBefore = snapshot();
      var quantity = order.quantity != null ? Math.abs(number(order.quantity, 0)) : orderQuantity(settings, order, price, snapshotBefore);
      if (order.quantity == null && Number.isFinite(order.qtyPercent)) quantity = Math.abs(state.positionSize) * Math.max(0, Math.min(100, order.qtyPercent)) / 100;
      if (!(quantity > 0)) {
        diagnostic('invalid-quantity', 'The order quantity must be greater than zero.', order, index);
        order.status = 'rejected';
        return false;
      }
      if (order.reduceOnly || order.type === 'close' || order.type === 'exit') quantity = Math.min(quantity, Math.abs(state.positionSize));
      if (!(quantity > 0)) {
        order.status = 'cancelled';
        return false;
      }
      var margin = Math.abs(price * quantity) * (direction > 0 ? settings.marginLong : settings.marginShort) / 100;
      if (margin > snapshotBefore.equity && order.type !== 'close' && order.type !== 'exit') {
        diagnostic('margin', 'The order was rejected because required margin exceeds equity.', order, index);
        order.status = 'rejected';
        return false;
      }
      var commissionValue = commission(settings, price, quantity);
      var remaining = quantity;
      if (state.positionSize && direction !== (state.positionSize > 0 ? 1 : -1)) {
        var opposite = openTrades.filter(function (trade) {
          return trade.direction !== direction && (!order.fromEntry || trade.entryId === order.fromEntry);
        });
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
      if (remaining > 0 && order.type !== 'close' && order.type !== 'exit') {
        var sameDirectionCount = openTrades.filter(function (trade) { return trade.direction === direction; }).length;
        if (sameDirectionCount > 0 && sameDirectionCount >= Math.max(1, settings.pyramiding + 1) && order.type === 'entry') {
          diagnostic('pyramiding', 'The order was ignored because the pyramiding limit was reached.', order, index);
          order.status = 'cancelled';
          return false;
        }
        var entryCommission = commission(settings, price, remaining);
        openTrade(order, direction, remaining, price, index, time, entryCommission);
        state.totalCommission += entryCommission;
      }
      state.totalSlippage += Math.abs(price - rawPrice) * quantity;
      executions.push({
        orderId: order.id || order.type,
        type: order.type,
        direction: direction > 0 ? 'long' : 'short',
        quantity: quantity,
        price: price,
        requestedPrice: rawPrice,
        commission: commissionValue,
        barIndex: index,
        time: time,
        phase: phase || 'open',
        reason: order.reason || order.type
      });
      order.status = 'filled';
      if (order.ocaName) {
        pending.slice().forEach(function (sibling) {
          if (sibling !== order && sibling.status === 'pending' && sibling.ocaName === order.ocaName && String(order.ocaType || 'cancel').toLowerCase() === 'cancel') {
            sibling.status = 'cancelled';
            pending.splice(pending.indexOf(sibling), 1);
          }
        });
      }
      markToMarket(bar);
      return true;
    }

    function shouldFillOnBar(order, bar, phase) {
      if (!order || order.status !== 'pending') return false;
      if (order.createdBarIndex >= bar.index && phase === 'open' && order.type === 'market') return false;
      if (order.createdBarIndex > bar.index) return false;
      return true;
    }

    function fillPending(index, phase) {
      var bar = visibleBar(index);
      if (!bar || !isEligible(index)) return;
      pending.slice().forEach(function (order) {
        if (!shouldFillOnBar(order, bar, phase)) return;
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
        applyFill(order, price, index, phase);
        if (order.status !== 'pending') pending.splice(pending.indexOf(order), 1);
      });
    }

    function makeOrder(intent, index) {
      intent = intent || {};
      var type = String(intent.type || 'order').toLowerCase();
      var order = {
        id: String(intent.id || type + '-' + index + '-' + (pending.length + executions.length + 1)),
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
        ocaType: String(intent.oca_type != null ? intent.oca_type : intent.ocaType || 'cancel')
      };
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
      (Array.isArray(intents) ? intents : []).forEach(function (intent) {
        if (pending.length + executions.length >= 100000) {
          diagnostic('limit', 'The run reached the broker order limit; additional orders were ignored.', intent, index);
          return;
        }
        if (!isEligible(index)) {
          diagnostic('date-range', 'The order was ignored outside the selected backtest range or warm-up period.', intent, index);
          return;
        }
        var intentType = String(intent && intent.type || '').toLowerCase();
        var requestedDirection = normalizeDirection(intent && intent.direction);
        if ((intentType === 'entry' || intentType === 'order') && ((settings.directionMode === 'long' && requestedDirection < 0) || (settings.directionMode === 'short' && requestedDirection > 0))) {
          diagnostic('risk', 'The order was rejected by the selected trading direction filter.', intent, index);
          return;
        }
        var type = String(intent && intent.type || '').toLowerCase();
        if (type === 'cancel_all') {
          pending.forEach(function (order) { order.status = 'cancelled'; });
          pending.length = 0;
          return;
        }
        if (type === 'cancel') {
          pending.slice().forEach(function (order) {
            if (order.id === String(intent.id || '')) { order.status = 'cancelled'; pending.splice(pending.indexOf(order), 1); }
          });
          return;
        }
        var order = makeOrder(intent, index);
        pending.push(order);
        if (settings.processOrdersOnClose && (order.type === 'market' || order.type === 'close')) fillPending(index, 'close');
      });
    }

    function beginBar(index) {
      state.currentBar = index;
      state.currentTime = visibleBar(index) && visibleBar(index).time;
      fillPending(index, 'open');
      markToMarket(visibleBar(index));
      return snapshot();
    }

    function endBar(index) {
      state.currentBar = index;
      markToMarket(visibleBar(index));
      equityCurve.push({ time: visibleBar(index).time, value: state.equity, barIndex: index });
      drawdownCurve.push({ time: visibleBar(index).time, value: state.peakEquity - state.equity, barIndex: index });
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
        opentrades: current.opentrades,
        max_drawdown: current.max_drawdown,
        max_runup: current.max_runup
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
      var longTrades = closedTrades.filter(function (trade) { return trade.direction === 'long'; });
      var shortTrades = closedTrades.filter(function (trade) { return trade.direction === 'short'; });
      var winning = closedTrades.filter(function (trade) { return trade.netProfit > 0; });
      var losing = closedTrades.filter(function (trade) { return trade.netProfit < 0; });
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
        winRatePercent: closedTrades.length ? state.wins / closedTrades.length * 100 : 0,
        averageTrade: closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / closedTrades.length : 0,
        averageWinningTrade: winning.length ? winning.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / winning.length : 0,
        averageLosingTrade: losing.length ? losing.reduce(function (sum, trade) { return sum + trade.netProfit; }, 0) / losing.length : 0,
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
        endingEquity: state.equity,
        openProfit: state.openProfit,
        averageBarsInTrade: closedTrades.length ? closedTrades.reduce(function (sum, trade) { return sum + trade.barsHeld; }, 0) / closedTrades.length : 0,
        sharpe: variance > 0 ? mean / Math.sqrt(variance) * Math.sqrt(returns.length || 1) : 0,
        buyAndHoldPercent: bars.length && bars[0].close ? (bars[bars.length - 1].close / bars[0].close - 1) * 100 : 0,
        monthlyReturns: periodReturns('month'),
        yearlyReturns: periodReturns('year')
      };
    }

    function result() {
      markToMarket(visibleBar(state.currentBar));
      return {
        engineVersion: VERSION,
        config: settings,
        executions: executions.slice(),
        trades: closedTrades.slice(),
        openTrades: openTrades.map(function (trade) {
          var copy = {};
          Object.keys(trade).forEach(function (key) { copy[key] = trade[key]; });
          return copy;
        }),
        pendingOrders: pending.slice(),
        equityCurve: equityCurve.slice(),
        drawdownCurve: drawdownCurve.slice(),
        stateCurve: stateCurve.slice(),
        metrics: metrics(),
        diagnostics: diagnostics.slice(),
        state: snapshot()
      };
    }

    if (settings.calcOnEveryTick) diagnostic('execution-mode', 'calc_on_every_tick is accepted, but historical bars are evaluated once per bar in this browser engine.', null, -1);
    if (settings.calcOnOrderFills) diagnostic('execution-mode', 'calc_on_order_fills is accepted, but fill-triggered recalculation is not available in the current historical runtime.', null, -1);

    return {
      version: VERSION,
      settings: settings,
      bars: bars,
      beginBar: beginBar,
      submit: submit,
      endBar: endBar,
      snapshot: snapshot,
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
