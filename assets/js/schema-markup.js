(function (window, document) {
    "use strict";

    var SCHEMA_CONTEXT = "https://schema.org";
    var SCRIPT_ID = "schema-markup-page";
    var config = window.__SCHEMA_MARKUP_CONFIG || {};
    var siteUrl = config.siteUrl || "https://360miq.com";
    var canonicalUrl = config.canonicalUrl || window.location.href.split("#")[0];
    var route = config.route || routeFromPath(window.location.pathname);
    var updateTimer = null;
    var updateRunning = false;
    var lastSerialized = "";

    function routeFromPath(pathname) {
        var path = String(pathname || "").replace(/^\/+|\/+$/g, "").replace(/\.php$/i, "");
        return path || "home";
    }

    function isFiniteNumber(value) {
        return typeof value === "number" && isFinite(value);
    }

    function asNumber(value) {
        if (isFiniteNumber(value)) return value;
        if (typeof value !== "string") return null;
        var normalized = value.replace(/,/g, "").replace(/[+%$]/g, "").trim();
        if (!normalized || !/^[-+]?\d*\.?\d+(?:e[-+]?\d+)?$/i.test(normalized)) return null;
        var number = Number(normalized);
        return isFiniteNumber(number) ? number : null;
    }

    function cleanText(value) {
        if (value === null || value === undefined) return "";
        return String(value).replace(/\s+/g, " ").trim();
    }

    function cleanValue(value, seen) {
        if (value === null || value === undefined) return undefined;
        if (typeof value === "number") return isFinite(value) ? value : undefined;
        if (typeof value === "string") return value.trim() === "" ? undefined : value;
        if (typeof value === "boolean") return value;
        if (typeof value !== "object") return undefined;

        seen = seen || [];
        if (seen.indexOf(value) !== -1) return undefined;
        seen.push(value);

        if (Array.isArray(value)) {
            var arrayResult = value.map(function (item) {
                return cleanValue(item, seen.slice());
            }).filter(function (item) {
                return item !== undefined;
            });
            return arrayResult.length ? arrayResult : undefined;
        }

        var objectResult = {};
        Object.keys(value).forEach(function (key) {
            var cleaned = cleanValue(value[key], seen.slice());
            if (cleaned !== undefined) objectResult[key] = cleaned;
        });
        return Object.keys(objectResult).length ? objectResult : undefined;
    }

    function safeSerialize(value) {
        return JSON.stringify(value)
            .replace(/</g, "\\u003c")
            .replace(/\u2028/g, "\\u2028")
            .replace(/\u2029/g, "\\u2029");
    }

    function absoluteUrl(value) {
        if (!value) return undefined;
        try {
            return new URL(value, siteUrl + "/").href;
        } catch (error) {
            return undefined;
        }
    }

    function stableId(value) {
        return cleanText(value).toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "")
            .substring(0, 90) || "data";
    }

    function SchemaMarkup(options) {
        if (!(this instanceof SchemaMarkup)) return SchemaMarkup.render(options);
        return SchemaMarkup.render(options);
    }

    SchemaMarkup.render = function (options) {
        try {
            options = options || {};
            var data = cleanValue(options.data);
            if (!data) return SchemaMarkup.remove(options.id);
            if (!data["@context"]) data["@context"] = SCHEMA_CONTEXT;
            if (options.type && !data["@type"]) data["@type"] = options.type;

            var serialized = safeSerialize(data);
            var id = options.id || SCRIPT_ID;
            var script = document.getElementById(id);
            if (!script) {
                script = document.createElement("script");
                script.id = id;
                script.type = "application/ld+json";
                script.setAttribute("data-schema-markup", "true");
                (document.head || document.documentElement).appendChild(script);
            }
            if (script.textContent !== serialized) script.textContent = serialized;
            return script;
        } catch (error) {
            return null;
        }
    };

    SchemaMarkup.remove = function (id) {
        try {
            var script = document.getElementById(id || SCRIPT_ID);
            if (script && script.parentNode) script.parentNode.removeChild(script);
        } catch (error) {
            return null;
        }
        return null;
    };

    function propertyValue(name, value, unit, extra) {
        var number = asNumber(value);
        var item = {
            "@type": "PropertyValue",
            "name": cleanText(name),
            "value": number !== null ? number : cleanText(value),
            "unitText": cleanText(unit)
        };
        if (extra && typeof extra === "object") {
            Object.keys(extra).forEach(function (key) {
                item[key] = extra[key];
            });
        }
        return cleanValue(item);
    }

    function pageName() {
        var heading = document.querySelector("main h1, h1");
        return cleanText(heading ? heading.textContent : document.title.replace(/\s*-\s*360MiQ.*$/i, "")) || "360MiQ";
    }

    function pageDescription(name) {
        var description = document.querySelector('meta[name="description"]');
        var text = cleanText(description && description.getAttribute("content"));
        if (text.length >= 50) return text.substring(0, 5000);
        return (name + " provides current financial market data, rankings, tables, and chart summaries on 360MiQ.").substring(0, 5000);
    }

    function pageType() {
        if (route === "screener") return "SearchResultsPage";
        if (route === "home") return "CollectionPage";
        if (route === "market" || route === "econ") return "CollectionPage";
        return "WebPage";
    }

    function nearestTitle(element) {
        var current = element;
        while (current && current !== document.body) {
            var heading = null;
            var children = current.children || [];
            for (var childIndex = 0; childIndex < children.length && !heading; childIndex++) {
                var child = children[childIndex];
                if (/^H[1-6]$/.test(child.tagName)) {
                    heading = child;
                } else if (child.classList && child.classList.contains("card-header")) {
                    heading = child.querySelector("h1, h2, h3, h4, h5, h6");
                }
            }
            if (heading && heading !== element && cleanText(heading.textContent)) return cleanText(heading.textContent);
            current = current.parentElement;
        }
        var preceding = element.previousElementSibling;
        while (preceding) {
            if (/^H[1-6]$/.test(preceding.tagName)) return cleanText(preceding.textContent);
            preceding = preceding.previousElementSibling;
        }
        return "";
    }

    function codeFromLink(element) {
        if (!element || !element.querySelector) return "";
        var link = element.querySelector('a[href*="stockinfo?code="], a[href*="/stockinfo?code="]');
        if (!link) return "";
        try {
            return cleanText(new URL(link.href, window.location.href).searchParams.get("code")).toUpperCase();
        } catch (error) {
            return "";
        }
    }

    function isLayoutTable(table) {
        if (!table || table.closest("script")) return true;
        var rows = table.rows ? table.rows.length : 0;
        if (!rows) return true;
        if (table.id === "failedtable" || table.id === "nodatatable") return true;
        var text = cleanText(table.textContent);
        return text === "" || text.length > 60000;
    }

    function tableHeaders(table) {
        var headers = Array.prototype.map.call(table.querySelectorAll("thead th"), function (cell) {
            return cleanText(cell.textContent);
        });
        if (headers.length) return headers;
        var firstRow = table.rows && table.rows[0];
        if (firstRow && firstRow.querySelectorAll("th").length) {
            return Array.prototype.map.call(firstRow.cells, function (cell) {
                return cleanText(cell.textContent);
            });
        }
        return [];
    }

    function tableRows(table, limit) {
        var headers = tableHeaders(table);
        var rows = [];
        Array.prototype.some.call(table.tBodies && table.tBodies.length ? table.tBodies[0].rows : table.rows || [], function (row) {
            if (rows.length >= limit) return true;
            var cells = Array.prototype.map.call(row.cells || [], function (cell) {
                return cleanText(cell.textContent);
            });
            if (!cells.length || cells.every(function (cell) { return !cell; })) return false;
            if (headers.length && cells.join("|") === headers.join("|")) return false;
            rows.push({ cells: cells, row: row });
            return false;
        });
        return { headers: headers, rows: rows };
    }

    function rankingMetric(title, rowData) {
        var cells = rowData.cells;
        var value = cells.length ? cells[cells.length - 1] : "";
        var unit = /%/.test(value) ? "%" : "";
        return propertyValue(title || "Value", value, unit);
    }

    function financialItem(rowData, title, position) {
        var code = codeFromLink(rowData.row);
        var name = cleanText(rowData.cells[0] || code);
        var href = code ? absoluteUrl("stockinfo?code=" + encodeURIComponent(code)) : undefined;
        var properties = [];
        var metric = rankingMetric(title, rowData);
        if (metric) properties.push(metric);
        return cleanValue({
            "@type": "ListItem",
            "position": position,
            "item": {
                "@type": "FinancialProduct",
                "@id": href ? href + "#instrument" : undefined,
                "name": name || code,
                "identifier": code || undefined,
                "url": href,
                "additionalProperty": properties
            }
        });
    }

    function rankingLists() {
        var lists = [];
        var selector = [
            '[id^="mover"] table',
            '[id^="highlow"] table',
            '[id^="MA50_Long"] table',
            '[id^="RSI"] table'
        ].join(",");
        Array.prototype.forEach.call(document.querySelectorAll(selector), function (table, index) {
            if (isLayoutTable(table)) return;
            var title = nearestTitle(table) || nearestTitle(table.parentElement) || "Market ranking";
            var parsed = tableRows(table, 100);
            var items = parsed.rows.map(function (rowData, rowIndex) {
                return financialItem(rowData, title, rowIndex + 1);
            }).filter(Boolean);
            if (!items.length) return;
            lists.push({
                "@type": "ItemList",
                "@id": canonicalUrl + "#ranking-" + stableId(title) + "-" + index,
                "name": title,
                "numberOfItems": items.length,
                "itemListOrder": "https://schema.org/ItemListOrderDescending",
                "itemListElement": items
            });
        });
        return lists;
    }

    function screenerList() {
        var table = document.getElementById("screener_grid");
        if (!table || isLayoutTable(table)) return null;
        var parsed = tableRows(table, 200);
        if (!parsed.rows.length) return null;
        var items = parsed.rows.map(function (rowData, rowIndex) {
            var code = codeFromLink(rowData.row) || cleanText(rowData.cells[0]).toUpperCase();
            var properties = [];
            rowData.cells.forEach(function (value, columnIndex) {
                var header = parsed.headers[columnIndex] || "Column " + (columnIndex + 1);
                if (columnIndex > 1 && value) properties.push(propertyValue(header, value, /%/.test(value) ? "%" : ""));
            });
            return {
                "@type": "ListItem",
                "position": rowIndex + 1,
                "item": {
                    "@type": "FinancialProduct",
                    "@id": code ? absoluteUrl("stockinfo?code=" + encodeURIComponent(code)) + "#instrument" : undefined,
                    "identifier": code || undefined,
                    "name": cleanText(rowData.cells[1] || code),
                    "url": code ? absoluteUrl("stockinfo?code=" + encodeURIComponent(code)) : undefined,
                    "additionalProperty": properties
                }
            };
        });
        var info = document.querySelector("#screener_grid_info");
        var totalMatch = cleanText(info && info.textContent).match(/of\s+([\d,]+)\s+entries/i);
        return cleanValue({
            "@type": "ItemList",
            "@id": canonicalUrl + "#screener-results",
            "name": pageName() + " results",
            "description": "The stock screener results currently shown for the selected market and technical, valuation, and fundamental filters.",
            "numberOfItems": totalMatch ? asNumber(totalMatch[1]) : items.length,
            "itemListElement": items
        });
    }

    function tableDatasets() {
        var datasets = [];
        Array.prototype.forEach.call(document.querySelectorAll("table[id]"), function (table) {
            if (isLayoutTable(table) || /^(screener_grid|failedtable|nodatatable)$/i.test(table.id)) return;
            if (table.closest('[id^="mover"], [id^="highlow"], [id^="MA50_Long"], [id^="RSI"]')) return;
            var parsed = tableRows(table, 30);
            if (!parsed.rows.length) return;
            var title = nearestTitle(table) || table.id.replace(/[-_]+/g, " ");
            var variables = parsed.headers.filter(Boolean).map(function (header) {
                return propertyValue(header, "Displayed table column");
            });
            datasets.push(cleanValue({
                "@type": "Dataset",
                "@id": canonicalUrl + "#table-" + stableId(table.id),
                "name": title,
                "description": ("Displayed table data for " + title + " on " + pageName() + ". The structured summary identifies the table columns and current displayed coverage.").substring(0, 5000),
                "creator": { "@id": siteUrl + "/#organization" },
                "publisher": { "@id": siteUrl + "/#organization" },
                "isAccessibleForFree": true,
                "variableMeasured": variables,
                "additionalProperty": propertyValue("Displayed row count", parsed.rows.length)
            }));
        });
        return datasets.filter(Boolean);
    }

    function seriesData(series) {
        if (!series) return [];
        if (Array.isArray(series.xData) && Array.isArray(series.yData)) {
            return series.xData.map(function (x, index) { return [x, series.yData[index]]; });
        }
        if (series.options && Array.isArray(series.options.data)) return series.options.data;
        return [];
    }

    function pointXY(point, index) {
        if (Array.isArray(point)) {
            if (point.length >= 2) {
                var y = point[1];
                if (Array.isArray(y)) {
                    for (var nestedIndex = y.length - 1; nestedIndex >= 0; nestedIndex--) {
                        if (asNumber(y[nestedIndex]) !== null) {
                            y = y[nestedIndex];
                            break;
                        }
                    }
                } else if (point.length > 2) {
                    for (var pointIndex = point.length - 1; pointIndex >= 1; pointIndex--) {
                        if (asNumber(point[pointIndex]) !== null) {
                            y = point[pointIndex];
                            break;
                        }
                    }
                }
                return { x: point[0], y: y };
            }
            return { x: index, y: point[0] };
        }
        if (typeof point === "number") return { x: index, y: point };
        if (point && typeof point === "object") return { x: point.x, y: point.y };
        return { x: index, y: null };
    }

    function dateString(value) {
        var number = asNumber(value);
        if (number === null || number < 100000000000) return "";
        try {
            return new Date(number).toISOString().substring(0, 10);
        } catch (error) {
            return "";
        }
    }

    function chartDatasets() {
        var datasets = [];
        var charts = window.Highcharts && Array.isArray(window.Highcharts.charts) ? window.Highcharts.charts : [];
        charts.forEach(function (chart, chartIndex) {
            try {
                if (!chart || !chart.renderTo || !document.documentElement.contains(chart.renderTo)) return;
                if (chart.renderTo.closest && chart.renderTo.closest('[data-schema-markup="true"]')) return;
                var title = cleanText(chart.title && chart.title.textStr) || nearestTitle(chart.renderTo) || chart.renderTo.id.replace(/[-_]+/g, " ");
                if (!title || /sparkline/i.test(title + " " + chart.renderTo.id)) return;
                var variables = [];
                var firstDate = "";
                var lastDate = "";
                (chart.series || []).forEach(function (series) {
                    if (!series || (series.options && series.options.isInternal) || /navigator/i.test(cleanText(series.name))) return;
                    var data = seriesData(series);
                    var validPoints = data.map(pointXY).filter(function (point) {
                        return point.y !== null && point.y !== undefined && !(typeof point.y === "number" && !isFinite(point.y));
                    });
                    if (!validPoints.length) return;
                    var first = validPoints[0];
                    var last = validPoints[validPoints.length - 1];
                    var seriesFirstDate = dateString(first.x);
                    var seriesLastDate = dateString(last.x);
                    if (seriesFirstDate && (!firstDate || seriesFirstDate < firstDate)) firstDate = seriesFirstDate;
                    if (seriesLastDate && (!lastDate || seriesLastDate > lastDate)) lastDate = seriesLastDate;
                    variables.push(propertyValue(cleanText(series.name) || "Series", last.y, cleanText(series.userOptions && series.userOptions.unit), {
                        "measurementTechnique": cleanText(series.type || series.options && series.options.type || chart.options && chart.options.chart && chart.options.chart.type)
                    }));
                });
                if (!variables.length) return;
                var subtitle = cleanText(chart.subtitle && chart.subtitle.textStr).replace(/<[^>]+>/g, "");
                var description = cleanText(title + ". " + subtitle + " This dataset summarizes the variables, date coverage, and latest values displayed by the chart.");
                datasets.push(cleanValue({
                    "@type": "Dataset",
                    "@id": canonicalUrl + "#chart-" + stableId(chart.renderTo.id || title) + "-" + chartIndex,
                    "name": title,
                    "description": description.length >= 50 ? description.substring(0, 5000) : (description + " Financial chart data on 360MiQ."),
                    "creator": { "@id": siteUrl + "/#organization" },
                    "publisher": { "@id": siteUrl + "/#organization" },
                    "isAccessibleForFree": true,
                    "temporalCoverage": firstDate && lastDate ? firstDate + "/" + lastDate : lastDate || firstDate,
                    "dateModified": lastDate,
                    "variableMeasured": variables,
                    "measurementTechnique": "Interactive JavaScript financial chart"
                }));
            } catch (error) {
                return;
            }
        });
        return datasets.filter(Boolean);
    }

    function stockHistoryDataset() {
        if (route !== "stockinfo" || !Array.isArray(window.dailydata) || !window.dailydata.length) return null;
        var rows = window.dailydata;
        var first = rows[0];
        var last = rows[rows.length - 1];
        if (!Array.isArray(last) || last.length < 5) return null;
        var firstDate = dateString(Array.isArray(first) ? first[0] : null);
        var lastDate = dateString(last[0]);
        var stockConfig = window.__STOCKINFO_PAGE_CONFIG || {};
        var code = cleanText(stockConfig.stockcode).toUpperCase();
        return cleanValue({
            "@type": "Dataset",
            "@id": canonicalUrl + "#stock-price-history",
            "name": (code || pageName()) + " historical price and volume data",
            "description": "Historical open, high, low, close, and volume data used by the interactive stock price, technical indicator, range, performance, and forecast charts on this page.",
            "creator": { "@id": siteUrl + "/#organization" },
            "publisher": { "@id": siteUrl + "/#organization" },
            "isAccessibleForFree": true,
            "temporalCoverage": firstDate && lastDate ? firstDate + "/" + lastDate : lastDate || firstDate,
            "dateModified": lastDate,
            "measurementTechnique": "Daily market price history and derived technical analysis",
            "variableMeasured": [
                propertyValue("Latest open", last[1]),
                propertyValue("Latest high", last[2]),
                propertyValue("Latest low", last[3]),
                propertyValue("Latest close", last[4]),
                propertyValue("Latest volume", last[5])
            ].filter(Boolean)
        });
    }

    function stockEntity() {
        var stockConfig = window.__STOCKINFO_PAGE_CONFIG;
        if (!stockConfig) return null;
        var fields = cleanText(stockConfig.stockinfo).split("_");
        var code = cleanText(stockConfig.stockcode || fields[13]).toUpperCase();
        var nameTc = cleanText(fields[2]);
        var nameEn = cleanText(fields[3]);
        function textById(id) {
            var element = document.getElementById(id);
            var text = cleanText(element && element.textContent);
            return text === "-" || /:\s*-$/.test(text) ? "" : text;
        }
        function labeledDomProperty(id, fallbackName, unit) {
            var text = textById(id);
            if (!text) return null;
            var separator = text.indexOf(":");
            var name = separator > 0 ? text.substring(0, separator) : fallbackName;
            var value = separator > 0 ? text.substring(separator + 1) : text;
            return propertyValue(name, value, unit || (/%/.test(value) ? "%" : ""));
        }
        var properties = [
            propertyValue("Market capitalization", fields[0], fields[11]),
            propertyValue("Exchange", fields[1]),
            propertyValue("EPS", fields[9], fields[11]),
            propertyValue("PE ratio", fields[10]),
            propertyValue("Currency", fields[11]),
            propertyValue("Trend Gauge", fields[14]),
            labeledDomProperty("cardheader-subtext", "Current quote"),
            labeledDomProperty("updatetime", "Quote update time"),
            labeledDomProperty("marketcap", "Market capitalization", textById("currency")),
            labeledDomProperty("boardlot", "Board lot"),
            labeledDomProperty("shortable", "Short selling, options, and futures eligibility"),
            labeledDomProperty("pe", "PE ratio"),
            labeledDomProperty("eps", "EPS", textById("currency")),
            labeledDomProperty("dividend", "Dividend yield", "%"),
            labeledDomProperty("cell5d", "5-day return", "%"),
            labeledDomProperty("cell20d", "20-day return", "%"),
            labeledDomProperty("cell60d", "60-day return", "%"),
            labeledDomProperty("cellytd", "Year-to-date return", "%"),
            labeledDomProperty("cell1y", "1-year return", "%"),
            labeledDomProperty("cell3y", "3-year return", "%"),
            labeledDomProperty("cell5y", "5-year return", "%"),
            labeledDomProperty("cell8y", "8-year return", "%"),
            labeledDomProperty("cellSector", "Sector"),
            labeledDomProperty("cellIndustry", "Industry"),
            labeledDomProperty("cellMarket", "Market")
        ].filter(Boolean);
        return cleanValue({
            "@type": "FinancialProduct",
            "@id": canonicalUrl + "#instrument",
            "name": nameEn || code,
            "alternateName": nameTc || undefined,
            "identifier": code,
            "url": canonicalUrl,
            "additionalProperty": properties
        });
    }

    function routeDataset(children) {
        var name = pageName();
        var spatialCoverage = "";
        var variables = [];
        if (route === "market" && window.__MARKET_PAGE_CONFIG) {
            spatialCoverage = cleanText(window.__MARKET_PAGE_CONFIG.exchangeName || window.__MARKET_PAGE_CONFIG.data);
            variables.push(propertyValue("Market", window.__MARKET_PAGE_CONFIG.data));
            variables.push(propertyValue("Benchmark", window.__MARKET_PAGE_CONFIG.benchmarkname || window.__MARKET_PAGE_CONFIG.benchmark));
        } else if (route === "econ" && window.__ECON_PAGE_CONFIG) {
            spatialCoverage = window.__ECON_PAGE_CONFIG.country === "US" ? "United States" : "Hong Kong";
            variables.push(propertyValue("Economy", spatialCoverage));
        } else if (route === "home") {
            spatialCoverage = "Global";
        }
        if (route !== "home" && route !== "market" && route !== "econ") return null;
        return cleanValue({
            "@type": "Dataset",
            "@id": canonicalUrl + "#dataset",
            "name": name + " data",
            "description": pageDescription(name),
            "url": canonicalUrl,
            "creator": { "@id": siteUrl + "/#organization" },
            "publisher": { "@id": siteUrl + "/#organization" },
            "isAccessibleForFree": true,
            "spatialCoverage": spatialCoverage,
            "keywords": route === "econ" ? "economic indicators, macroeconomics, time series" : "stock market, rankings, technical analysis, financial charts",
            "variableMeasured": variables,
            "hasPart": children.map(function (child) { return { "@id": child["@id"] }; })
        });
    }

    function toolEntity(chartChildren) {
        if (route !== "tool") return null;
        var toolConfig = window.__TOOL_PAGE_CONFIG || {};
        return cleanValue({
            "@type": "WebApplication",
            "@id": canonicalUrl + "#application",
            "name": pageName(),
            "url": canonicalUrl,
            "applicationCategory": "FinanceApplication",
            "operatingSystem": "Any",
            "isAccessibleForFree": true,
            "description": pageDescription(pageName()),
            "additionalProperty": [
                propertyValue("Performance Race symbols", toolConfig.codefromURL),
                propertyValue("Performance Race timeframe", toolConfig.timeframefromURL),
                propertyValue("Chart Composer series", toolConfig.composerCodesfromURL)
            ].filter(Boolean),
            "subjectOf": chartChildren.map(function (child) { return { "@id": child["@id"] }; })
        });
    }

    function currentGraph() {
        var rankings = rankingLists();
        var screener = route === "screener" ? screenerList() : null;
        var tables = tableDatasets();
        var charts = chartDatasets();
        var stockHistory = stockHistoryDataset();
        var dataChildren = charts.concat(tables).concat(stockHistory ? [stockHistory] : []);
        var dataset = routeDataset(dataChildren);
        var stock = route === "stockinfo" ? stockEntity() : null;
        var tool = toolEntity(charts);
        var mainEntity = [];
        if (dataset) mainEntity.push({ "@id": dataset["@id"] });
        if (stock) mainEntity.push({ "@id": stock["@id"] });
        if (tool) mainEntity.push({ "@id": tool["@id"] });
        if (screener) mainEntity.push({ "@id": screener["@id"] });
        rankings.forEach(function (ranking) { mainEntity.push({ "@id": ranking["@id"] }); });
        if (!dataset && !tool) {
            dataChildren.forEach(function (child) { mainEntity.push({ "@id": child["@id"] }); });
        }

        var page = cleanValue({
            "@type": pageType(),
            "@id": canonicalUrl + "#webpage",
            "url": canonicalUrl,
            "name": pageName(),
            "description": pageDescription(pageName()),
            "isPartOf": { "@id": siteUrl + "/#website" },
            "publisher": { "@id": siteUrl + "/#organization" },
            "mainEntity": mainEntity
        });

        return [page]
            .concat(dataset ? [dataset] : [])
            .concat(stock ? [stock] : [])
            .concat(tool ? [tool] : [])
            .concat(screener ? [screener] : [])
            .concat(rankings)
            .concat(dataChildren)
            .filter(Boolean);
    }

    function updatePageSchema() {
        if (updateRunning) return;
        updateRunning = true;
        try {
            var graph = currentGraph();
            var payload = cleanValue({ "@context": SCHEMA_CONTEXT, "@graph": graph });
            var serialized = payload ? safeSerialize(payload) : "";
            if (serialized && serialized !== lastSerialized) {
                lastSerialized = serialized;
                SchemaMarkup.render({ id: SCRIPT_ID, data: payload });
            }
        } catch (error) {
            return;
        } finally {
            updateRunning = false;
        }
    }

    function scheduleUpdate(delay) {
        window.clearTimeout(updateTimer);
        updateTimer = window.setTimeout(function () {
            if (typeof window.requestIdleCallback === "function") {
                window.requestIdleCallback(updatePageSchema, { timeout: 1000 });
            } else {
                updatePageSchema();
            }
        }, typeof delay === "number" ? delay : 250);
    }

    function mutationIsSchemaOnly(mutations) {
        return mutations.length > 0 && mutations.every(function (mutation) {
            var target = mutation.target && mutation.target.nodeType === 1 ? mutation.target : mutation.target && mutation.target.parentElement;
            return target && (target.id === SCRIPT_ID || target.closest && target.closest("#" + SCRIPT_ID));
        });
    }

    window.SchemaMarkup = SchemaMarkup;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () { scheduleUpdate(0); }, { once: true });
    } else {
        scheduleUpdate(0);
    }
    window.addEventListener("load", function () { scheduleUpdate(100); }, { once: true });
    document.addEventListener("shown.bs.tab", function () { scheduleUpdate(250); });
    document.addEventListener("change", function () { scheduleUpdate(400); });
    document.addEventListener("draw.dt", function () { scheduleUpdate(100); });
    if (window.jQuery) {
        window.jQuery(document).on("shown.bs.tab.schemaMarkup draw.dt.schemaMarkup", function () {
            scheduleUpdate(150);
        });
    }

    if (window.MutationObserver && document.documentElement) {
        var observer = new MutationObserver(function (mutations) {
            if (!mutationIsSchemaOnly(mutations)) scheduleUpdate(350);
        });
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    [1000, 3000, 7000].forEach(function (delay) {
        window.setTimeout(function () { scheduleUpdate(0); }, delay);
    });
})(window, document);
