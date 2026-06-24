(function (window) {
    'use strict';

    var config = window.__SITE_DISPLAY_POLICY || {};
    var showStockIndexPrices = config.showStockIndexPrices !== false;

    function numberOrNull(value) {
        if (value === null || value === undefined || value === '')
            return null;

        var parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function formatPercent(value, decimals) {
        var parsed = numberOrNull(value);
        if (parsed === null)
            return '-';

        var precision = Number.isInteger(decimals) ? decimals : null;
        var formatted = precision === null ? String(parsed) : parsed.toFixed(precision);
        if (precision !== null)
            formatted = formatted.replace(/\.?0+$/, '');

        if (parsed > 0)
            return '+' + formatted + '%';
        if (parsed === 0)
            return '\u00B1' + formatted + '%';
        return formatted + '%';
    }

    function percentChange(current, previous, decimals) {
        var currentNumber = numberOrNull(current);
        var previousNumber = numberOrNull(previous);
        if (currentNumber === null || previousNumber === null || previousNumber === 0)
            return '-';

        return formatPercent((currentNumber / Math.abs(previousNumber) - 1) * 100, decimals);
    }

    function pointPercentChange(point, decimals) {
        if (!point || !point.series)
            return '-';

        var series = point.series;
        var values = series.processedYData || series.yData || [];
        var index = typeof point.index === 'number' ? point.index : -1;

        if (series.groupedData && series.groupedData.length) {
            for (var i = 0; i < series.groupedData.length; i++) {
                if (series.groupedData[i].x === point.x) {
                    index = i;
                    values = series.groupedData.map(function (item) { return item.y; });
                    break;
                }
            }
        }

        if (index <= 0 || index >= values.length)
            return '-';

        return percentChange(values[index], values[index - 1], decimals);
    }

    function extractLastPercent(text) {
        var matches = String(text || '').match(/[+\-\u00B1]?\d+(?:\.\d+)?%/g);
        if (!matches || !matches.length)
            return null;

        return matches[matches.length - 1].replace(/^\+?0(?:\.0+)?%$/, '\u00B10%');
    }

    window.PriceDisplayPolicy = Object.freeze({
        showStockIndexPrices: function () {
            return showStockIndexPrices;
        },
        formatPercent: formatPercent,
        percentChange: percentChange,
        pointPercentChange: pointPercentChange,
        extractLastPercent: extractLastPercent
    });
})(window);
