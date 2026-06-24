(function (window, document) {
    'use strict';

    if (!window.PriceDisplayPolicy || PriceDisplayPolicy.showStockIndexPrices())
        return;

    function percentFromText(text) {
        return PriceDisplayPolicy.extractLastPercent(text);
    }

    function applyPeerRows(root) {
        if (!root)
            return;

        root.querySelectorAll('td').forEach(function (cell) {
            if (cell.dataset.pricePolicyApplied === '1')
                return;

            var html = cell.innerHTML;
            var updated = html.replace(
                /\s-?\d+(?:,\d{3})*(?:\.\d+)?\s+(<span[^>]*>[+\-\u00B1Â]*\d+(?:\.\d+)?%<\/span>)/,
                ' $1'
            );
            if (updated !== html) {
                cell.innerHTML = updated;
                cell.dataset.pricePolicyApplied = '1';
            }
        });
    }

    function applyScreenerRows(root) {
        if (!root)
            return;

        root.querySelectorAll('tr').forEach(function (row) {
            var cell = row.children[2];
            if (!cell || cell.dataset.pricePolicyApplied === '1')
                return;

            var percent = percentFromText(cell.textContent);
            if (!percent)
                return;

            var wrapper = cell.firstElementChild;
            var value = wrapper && wrapper.children[0];
            if (!value)
                return;

            value.textContent = percent;
            value.style.color = percent.charAt(0) === '-' ? 'red' : (percent.charAt(0) === '+' ? 'green' : 'grey');
            cell.dataset.pricePolicyApplied = '1';
        });
    }

    function applyScreenerHeaders() {
        document.querySelectorAll('#screener_grid thead th:nth-child(3)').forEach(function (header) {
            var label = header.querySelector('div > div:first-child');
            if (label && label.textContent.trim() !== 'Change')
                label.innerHTML = 'Change&nbsp;';
        });
    }

    function configureScreenerExports() {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!node || node.nodeName !== 'SCRIPT' || !/buttons\.html5\.min\.js/.test(node.src || ''))
                        return;

                    node.addEventListener('load', function () {
                        if (!window.jQuery || !jQuery.fn.dataTable || !jQuery.fn.dataTable.ext.buttons)
                            return;

                        ['copyHtml5', 'csvHtml5', 'excelHtml5'].forEach(function (name) {
                            var definition = jQuery.fn.dataTable.ext.buttons[name];
                            if (!definition)
                                return;

                            definition.exportOptions = definition.exportOptions || {};
                            definition.exportOptions.format = definition.exportOptions.format || {};
                            var originalBody = definition.exportOptions.format.body;
                            definition.exportOptions.format.body = function (data, row, column, node) {
                                if (column === 2)
                                    return percentFromText(node && node.textContent) || '';
                                return originalBody ? originalBody(data, row, column, node) : data;
                            };
                        });
                    }, { once: true });
                });
            });
        });
        observer.observe(document.head, { childList: true });
    }

    function configureChartComposer() {
        if (typeof window.baseChartConfig === 'undefined')
            return;

        function isStockOrIndex(series) {
            var code = series && series.options && series.options.custom ? series.options.custom.code || '' : '';
            return code.indexOf('stock:') === 0 || code.indexOf('idx:') === 0;
        }

        baseChartConfig.legend.labelFormatter = function () {
            if (!isStockOrIndex(this)) {
                var current = this.yData[this.yData.length - 1];
                return '<span style="color:' + this.color + '">' + this.name + ': </span><b>' + current + '</b>';
            }

            var values = this.processedYData || this.yData || [];
            return '<span style="color:' + this.color + '">' + this.name + ': ' +
                PriceDisplayPolicy.percentChange(values[values.length - 1], values[values.length - 2], 2) + '</span>';
        };

        baseChartConfig.tooltip.formatter = function () {
            var html = '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %B %d, %Y', this.x) + '</span>';
            this.points.forEach(function (point) {
                var value = isStockOrIndex(point.series) ?
                    PriceDisplayPolicy.pointPercentChange(point, 2) :
                    point.y;
                html += '<br/><span style="color:' + point.series.color + '">●</span> ' +
                    point.series.name + ': <b>' + value + '</b>';
            });
            return html;
        };
    }

    function sanitizeAnyChart(root) {
        if (!root)
            return;

        root.querySelectorAll('.anychart-tooltip, .anychart-legend-item, [class*="anychart-legend-item"]').forEach(function (element) {
            var html = element.innerHTML;
            var percent = percentFromText(element.textContent);
            var updated;
            if (/\bPrice\s*:|\bClose\s*:|\bOpen\s*:|\bHigh\s*:|\bLow\s*:/.test(element.textContent)) {
                if (percent)
                    updated = 'Change: <b>' + percent + '</b>';
                else
                    updated = element.textContent.split(/\s+(?:O|Open|Close)\s*:/)[0].trim();
            } else {
                updated = html.replace(
                    /\s-?\d+(?:,\d{3})*(?:\.\d+)?\s*\(\s*([+\-\u00B1]?\d+(?:\.\d+)?%)\s*\)/g,
                    ' $1'
                );
            }
            if (updated !== html)
                element.innerHTML = updated;
        });
    }

    function applyAll() {
        applyPeerRows(document.getElementById('peersContent'));
        applyScreenerHeaders();
        applyScreenerRows(document.querySelector('#screener_grid tbody'));
        sanitizeAnyChart(document);
    }

    configureScreenerExports();
    configureChartComposer();

    var pageObserver = new MutationObserver(function () {
        applyAll();
    });

    function start() {
        applyAll();
        pageObserver.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', start, { once: true });
    else
        start();

    document.documentElement.addEventListener('themechange', function () {
        window.setTimeout(applyAll, 0);
    });
})(window, document);
