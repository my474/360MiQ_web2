(function (root, factory) {
  var api = factory();

  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }

  if (root) {
    root.ChatboxRuntime = api;
  }
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  var financialTerms = new Set([
    'dps', 'ebit', 'ebitda', 'ema', 'eps', 'ev', 'fcf', 'fpe', 'macd', 'ma',
    'mcap', 'nav', 'pb', 'pe', 'peg', 'ps', 'qoq', 'roa', 'roe', 'roi', 'rsi',
    'sma', 'ttm', 'yoy'
  ]);

  var promptTerms = new Set([
    'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and',
    'annual', 'any', 'are', 'as', 'asx', 'at', 'be', 'because', 'been', 'before', 'below',
    'bad', 'between', 'book', 'both', 'but', 'buy', 'by', 'can', 'cap', 'cash', 'cheap',
    'check', 'code', 'compare', 'could', 'currently', 'debt', 'did', 'dividend', 'do', 'does', 'doing',
    'down', 'during', 'each', 'earnings', 'expensive', 'fair', 'few', 'for',
    'forecast', 'forward', 'from', 'further', 'going', 'good', 'growth', 'had', 'has', 'have', 'having', 'he', 'hkex',
    'her', 'here', 'hers', 'herself', 'high', 'him', 'himself', 'his', 'hold',
    'how', 'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself', 'just', 'low', 'lse',
    'latest', 'look', 'margin', 'market', 'me', 'more', 'most', 'my', 'myself', 'name', 'news', 'no', 'nor',
    'nasdaq', 'not', 'now', 'nse', 'nyse', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours',
    'okay', 'out', 'outlook', 'over', 'overpriced', 'overvalued', 'own', 'perform', 'performance', 'price', 'profit', 'quarter',
    'ratio', 'recent', 'revenue', 'same', 'sales', 'sell', 'she', 'should', 'show', 'shse', 'so', 'some',
    'stock', 'such', 'symbol', 'szse', 'than', 'that', 'the', 'their', 'theirs', 'them',
    'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through',
    'target', 'tell', 'think', 'ticker', 'to', 'today', 'too', 'trailing', 'trend', 'tsx', 'tyo', 'under', 'undervalued', 'until',
    'up', 'valuation', 'value', 'very', 'was', 'we', 'were', 'what', 'when',
    'where', 'which', 'while', 'who', 'why', 'will', 'with', 'worth', 'would',
    'yield', 'you', 'your', 'yours'
  ]);

  var explicitContextTerms = new Set(['code', 'stock', 'symbol', 'ticker']);

  function normalizeTerm(term) {
    return String(term || '')
      .replace(/^[\s$]+|[\s?!,;:]+$/g, '')
      .replace(/[\u2019']s$/i, '')
      .replace(/^[.-]+|[.-]+$/g, '');
  }

  function isIgnoredStockTerm(term) {
    var normalized = normalizeTerm(term).toLowerCase();
    return normalized === '' || financialTerms.has(normalized) || promptTerms.has(normalized);
  }

  function findLikelyStockCandidate(input) {
    var text = String(input || '').replace(/\bP\s*\/\s*[EB]\b/gi, ' ');
    var tokenPattern = /\$?[A-Za-z0-9][A-Za-z0-9.-]{0,9}(?:[\u2019']s)?/g;
    var best = null;
    var match;

    while ((match = tokenPattern.exec(text)) !== null) {
      var raw = match[0];
      var candidate = normalizeTerm(raw);
      if (!candidate) continue;

      var lower = candidate.toLowerCase();
      var before = text.slice(0, match.index).match(/([A-Za-z]+)\s*$/);
      var hasExplicitContext = !!(before && explicitContextTerms.has(before[1].toLowerCase()));
      var hasDollarPrefix = raw.charAt(0) === '$';
      var isFinancialTerm = financialTerms.has(lower);

      if (isIgnoredStockTerm(candidate) && !hasDollarPrefix && !hasExplicitContext) continue;

      var hasMarketPunctuation = /[0-9.]/.test(candidate);
      var isUppercase = /[A-Z]/.test(candidate) && candidate === candidate.toUpperCase();
      var isTitleCase = /^[A-Z][a-z]{1,4}$/.test(candidate);
      var isShortWord = /^[A-Za-z]{2,5}$/.test(candidate);
      var isUppercaseCode = /^[A-Z]{1,6}$/.test(candidate);

      if (!hasDollarPrefix && !hasExplicitContext && !hasMarketPunctuation && !isShortWord && !isUppercaseCode) continue;

      var score = 0;
      if (hasDollarPrefix) score += 120;
      if (hasExplicitContext) score += 110;
      if (hasMarketPunctuation) score += 90;
      if (isUppercase) score += 70;
      if (isTitleCase) score += 25;
      if (isShortWord) score += 45;
      if (isFinancialTerm) score -= 40;
      score -= match.index / Math.max(text.length, 1);

      if (!best || score > best.score) {
        best = { candidate: candidate, score: score };
      }
    }

    return best ? best.candidate : '';
  }

  function captureScrollState(element) {
    if (!element) return null;

    var maximum = Math.max(0, element.scrollHeight - element.clientHeight);
    var top = Math.max(0, element.scrollTop || 0);

    return {
      top: top,
      wasAtBottom: maximum - top <= 4
    };
  }

  function restoreScrollState(element, state) {
    if (!element || !state) return;

    var maximum = Math.max(0, element.scrollHeight - element.clientHeight);
    element.scrollTop = state.wasAtBottom ? maximum : Math.min(Math.max(0, state.top), maximum);
  }

  return {
    captureScrollState: captureScrollState,
    findLikelyStockCandidate: findLikelyStockCandidate,
    isIgnoredStockTerm: isIgnoredStockTerm,
    restoreScrollState: restoreScrollState
  };
}));
