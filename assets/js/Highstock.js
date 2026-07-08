function highstock(chartcontainer, data, types, title, subtitle, yaxis0, yaxis1, ymin, ymax, unitType, rangeSelected, seriesType, seriesOpacity, creditY, plotlevel, YaxisType = 'linear', oneYaxis = false, updowncolor = false, dataGrouping_approximation = 'close', monthlyFund = Infinity, showPriceTooltipLegend = true, plotMajorEvents = false, rangeSelector = [{type: 'month', count: 6, text: '6m'}, {type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:2, text:'2y'}, {type: 'year', count: 3, text: '3y'}, {type: 'year', count: 8, text: '8y'}, {type: 'all', text: 'All'}])
{
    var isNyseValuationChart = chartcontainer == 'chartcontainerD2' || chartcontainer == 'chartcontainerD3';
    var showPolicyPriceValues = !window.PriceDisplayPolicy || PriceDisplayPolicy.showStockIndexPrices();
    var showPriceValues = showPolicyPriceValues || plotMajorEvents === 'GOLD';
    var marketIndexNameMap = {
        'S&P 500': 'SPX',
        'Nasdaq Composite': 'Nas Composite',
        'Dow Industrial': 'DJI',
        'Dow Transportation': 'DJT',
        'FTSE 100': 'UK 100',
        'TSX Composite': 'Toronto Composite',
        'ASX 200': 'Australia 200',
        'Nifty 50': 'India 50',
        'Nikkei 225': 'Japan 225',
        'Hang Seng': 'HSI'
    };
    var marketIndexDisplayName = function (text) {
        if (isNyseValuationChart || typeof text !== 'string' || text.indexOf('S&P 500 ETF') !== -1)
            return text;

        Object.keys(marketIndexNameMap).forEach(function (oldName) {
            text = text.replace(oldName, marketIndexNameMap[oldName]);
        });
        return text;
    };
    title = marketIndexDisplayName(title);
    yaxis0 = marketIndexDisplayName(yaxis0);
    yaxis1 = marketIndexDisplayName(yaxis1);

    if (plotlevel !== null)
        plotlevel = plotlevel.toString();

	var plotlevel_color = (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#45475F':'#eaeaea';})();
    var leftYAxisThemeColor = document.documentElement.getAttribute('data-theme') === 'dark' ? '#ffffff' : '#000000';

    var majorEvents = typeof getHighstockMajorEvents === 'function' ? getHighstockMajorEvents(plotMajorEvents) : [];
    var tooltipDiv = null;

    function styleMajorEventsTooltip()
    {
        var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        tooltipDiv.style.background = isDarkMode ? 'rgba(24, 26, 38, 0.96)' : 'rgba(255, 255, 255, 0.8)';
        tooltipDiv.style.color = isDarkMode ? '#f2f4ff' : 'black';
        tooltipDiv.style.boxShadow = isDarkMode ? '0 8px 24px rgba(0, 0, 0, 0.55)' : 'none';
        tooltipDiv.style.borderColor = isDarkMode ? '#64748b' : 'rgba(0, 0, 0, 0.5)';
    }

    function getMajorEventOpacity(color)
    {
        var rgbaMatch = color && color.match(/rgba\(\d+,\s*\d+,\s*\d+,\s*([\d.]+)\)/);
        return rgbaMatch ? parseFloat(rgbaMatch[1]) : 0.1;
    }

    function applyMajorEventsTheme(chart)
    {
        if (!chart || majorEvents.length === 0 || typeof applyHighstockMajorEventTheme !== 'function')
            return;

        var eventsById = {};
        majorEvents.forEach(function(event) {
            applyHighstockMajorEventTheme(event);
            eventsById[event.id] = event;
        });

        chart.xAxis[0].plotLinesAndBands.forEach(function(plotband) {
            var event = eventsById[plotband.options.id];
            if (!event || !plotband.svgElem)
                return;

            var isHidden = plotband.svgElem.attr('fill') === 'transparent';
            plotband.options.color = event.color;
            plotband.options.zIndex = event.zIndex;
            plotband.options.label.style = event.label.style;
            plotband.originalColor = event.color;
            plotband.svgElem.originalColor = event.color;
            plotband.svgElem.originalOpacity = getMajorEventOpacity(event.color);
            plotband.svgElem.attr({
                fill: isHidden ? 'transparent' : event.color,
                zIndex: event.zIndex
            });

            if (plotband.label)
            {
                plotband.label.css(event.label.style);
                plotband.label.attr({
                    fill: event.label.style.color,
                    zIndex: event.zIndex + 1
                });
            }
        });

        styleMajorEventsTooltip();
    }

    if (majorEvents.length > 0)
    {
        tooltipDiv = document.createElement('div');
        tooltipDiv.id = 'custom-tooltip';
        tooltipDiv.style.cssText = 'position: absolute; padding: 8px; border-radius: 4px; pointer-events: none; display: none; font-size: 12px; z-index: 1031;';
        styleMajorEventsTooltip();
        document.body.appendChild(tooltipDiv);
    }


    var pb = [];
    var bandID = 'bands';
    var isStacked_Column = false;
    var isColumn = false;
    var isNewYaxis = false;
    var NewYaxisText = '';
    var hindenburgColor = 'pink';
    var plotbandsColor = 'rgba(128, 128, 128, 0.15)';
    var maxdate = null;
    for (var j = 0; j < types.length; j++)
    {
        if (data[types[j]] && data[types[j]].length > 0)
        {
            var tmp = data[types[j]][data[types[j]].length - 1][0];
    		if (maxdate < tmp)
    		    maxdate = tmp;
        }

        if (types[j].includes('_stacked'))
        {
            isStacked_Column = true;
        }
        else if (types[j].includes('_column'))
        {
            isStacked_Column = true;
            isColumn = true;
        }
        else if (types[j].includes('_plotBands'))
        {
            pb = getPlotBandPeriods(data[types[j]], plotbandsColor, bandID); // assume only 1 plotbands data
        }

        if (types[j].includes('_newY'))
        {
            isNewYaxis = true;
            NewYaxisText = types[j].replace('_newY', '').replace('_stacked', '').replace('_column', '').replace('_plotBands', '');
        }
    }
    var now = new Date();
    var utc_timestamp = Date.UTC(now.getFullYear(),now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds(), now.getMilliseconds());

    maxdate = maxdate === null ? utc_timestamp : maxdate;

    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

    // Define a custom symbol path
    Highcharts.SVGRenderer.prototype.symbols.customcircle =
        function(x, y, w, h) {
          var r = 8;

          return [
            //
            'M', 0, 9,
            'm', -r, 0,
            'a', r, r, 0, 1, 0, 2 * r, 0,
            'a', r, r, 0, 1, 0, -2 * r, 0
          ];
        };

    if (Highcharts.VMLRenderer) {
        Highcharts.VMLRenderer.prototype.symbols.customcircle =
          Highcharts.SVGRenderer.prototype.symbols.customcircle;
    }

/*    (function (H) {
        if (!isNaN(plotlevel))
        {
        	H.wrap(H.Tick.prototype, 'render', function (p, index, old, opacity) {
              	p.call(this, index, old, opacity);
              	var gridLine = this.gridLine;

                if (!this.axis.horiz && this.pos == plotlevel && gridLine) {
                	gridLine.attr({
                    	"stroke-width": 3,
                  	    stroke: '#e5f1f6'
                    });
                }
            });
        }
    })(Highcharts);
*/
    // create the chart
    chart = new Highcharts.StockChart({
        chart: {
            renderTo: chartcontainer,
            alignTicks: true,
            events: {
                load: function (event) {
                    this.yAxis[0].update({
                        lineColor: this.series[0].color
                    });
                    this.yAxis[1].update({
                        lineColor: leftYAxisThemeColor,
                        tickColor: leftYAxisThemeColor,
                        title: {
                            style: {
                                color: leftYAxisThemeColor
                            }
                        },
                        labels: {
                            style: {
                                color: leftYAxisThemeColor
                            }
                        }
                    });
                    applyMajorEventsTheme(this);
                },

                render: function() {
                    if (majorEvents.length > 0)
                    {
                        var chart = this;
                        var bw = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var fontSize = bw < 576 ? '6px' : bw < 768 ? '7px' : bw < 1024 ? '8px' : '10px';
                        // Loop through all plotbands on the xAxis
                        chart.xAxis[0].plotLinesAndBands.forEach(function(item) {
                            // Check if the item is a plotband (has 'from' and 'to')
                            if (item.options.from !== undefined && item.options.to !== undefined) {
                                var plotband = item;
                                var svgElem = plotband.svgElem;

                                // Skip if svgElem isn’t available
                                if (!svgElem) return;

                                // Store original properties on first render
                                if (!plotband.originalColor) {
                                    plotband.originalColor = plotband.options.color || 'rgba(0, 0, 0, 0.1)';
                                }
                                if (plotband.label && !plotband.originalLabelText) {
                                    plotband.originalLabelText = plotband.label.textStr || '';
                                }

                                // Define the toggle function
                                var toggleVisibility = function() {
                                    var currentFill = svgElem.attr('fill');
                                    var isHidden = currentFill === 'transparent';
                                    if (isHidden) {
                                        // Show the plotband and label
                                        svgElem.attr({ fill: plotband.originalColor });
                                        if (plotband.label) {
                                            plotband.label.attr({ opacity: 1 });
                                            plotband.label.attr({ text: plotband.originalLabelText });
                                        }
                                    } else {
                                        // Hide the plotband and label
                                        svgElem.attr({ fill: 'transparent' });
                                        if (plotband.label) {
                                            plotband.label.attr({ opacity: 0 });
                                        }
                                    }
                                };

                                // Attach the toggle function to the plotband's SVG element
                                svgElem.element.onclick = null;
                                svgElem.element.onclick = toggleVisibility;

                                // Set cursor to pointer for plotband
                                svgElem.attr({ cursor: 'pointer' });

                                // Store original color if not already stored
                                if (!svgElem.originalColor) {
                                    svgElem.originalColor = plotband.options.color || 'rgba(0, 0, 0, 0.1)';
                                }
                                // Store original opacity if not already stored
                                if (!svgElem.originalOpacity) {
                                    // Extract original alpha value
                                    var originalColor = svgElem.originalColor;
                                    var rgbaMatch = originalColor.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/);
                                    svgElem.originalOpacity = rgbaMatch ? parseFloat(rgbaMatch[4]) : 0.1; // Default to 0.1 if no match
                                }

                                // Clear existing plotband events
                                svgElem.element.onmouseover = null;
                                svgElem.element.onmouseout = null;

                                // Add mouseover/mouseout for plotband opacity
                                svgElem.element.onmouseover = function() {
                                    if (svgElem.attr('fill') !== 'transparent') {
                                        var bandColor = plotband.options.color || 'coral'; // Default if undefined
                                        var rgbaMatch = bandColor.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/);
                                        if (rgbaMatch) {
                                            var r = rgbaMatch[1];
                                            var g = rgbaMatch[2];
                                            var b = rgbaMatch[3];
                                            var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
                                            var a = Math.min(1, svgElem.originalOpacity + (isDarkMode ? 0.1 : 0.15));
                                            bandColor = `rgba(${r}, ${g}, ${b}, ${a})`;
                                            svgElem.attr({ fill: bandColor });
                                        }
                                    }
                                };
                                svgElem.element.onmouseout = function() {
                                    if (svgElem.attr('fill') !== 'transparent') {
                                        svgElem.attr({ fill: svgElem.originalColor });
                                    }
                                };

                                // Set cursor to pointer for label and attach click handler
                                if (plotband.label && plotband.label.element) {
                                    // Dynamically adjust label font size based on browser width
                                    plotband.label.element.style.fontSize = fontSize;

                                    // Clear existing events to avoid duplicates
                                    plotband.label.element.onclick = null;
                                    plotband.label.element.onmouseover = null;
                                    plotband.label.element.onmouseout = null;
                                    plotband.label.element.ontouchstart = null;
                                    plotband.label.element.ontouchend = null;

                                    plotband.label.attr({ cursor: 'pointer' });
                                    plotband.label.element.onclick = toggleVisibility;

                                    // Add mouseover event to show tooltip
                                    plotband.label.element.onmouseover = function(event) {
                                        var currentFill = svgElem.attr('fill');
                                        var isHidden = currentFill === 'transparent';
                                        if (isHidden)
                                            return;

                                        var tooltipText = plotband.options.tooltip || 'No tooltip available';
                                        tooltipDiv.innerHTML = tooltipText;
                                        styleMajorEventsTooltip();

                                        // Dynamically set width based on browser size
                                        var browserWidth = window.innerWidth;
                                        tooltipDiv.style.width = (browserWidth < 800 ? browserWidth - 37 : 763) + 'px';

                                        // Set border color based on plotband color
                                        var borderColor = plotband.options.color || 'coral'; // Default if undefined
                                        // Extract RGB values and set opacity to 0.5
                                        var rgbaMatch = borderColor.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/);
                                        if (rgbaMatch) {
                                            var r = rgbaMatch[1];
                                            var g = rgbaMatch[2];
                                            var b = rgbaMatch[3];
                                            var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
                                            borderColor = `rgba(${r}, ${g}, ${b}, ${isDarkMode ? 0.9 : 0.5})`;
                                        } else {
                                            borderColor = 'rgba(0, 0, 0, 0.5)'; // Fallback if parsing fails
                                        }
                                        tooltipDiv.style.border = '2px solid ' + borderColor;

                                        // Show tooltip temporarily to measure its size
                                        tooltipDiv.style.display = 'block';
                                        var tooltipWidth = tooltipDiv.offsetWidth;
                                        var tooltipHeight = tooltipDiv.offsetHeight;

                                        // Calculate position to fit within screen
                                        var x = event.pageX + 10; // Default to right of cursor
                                        var y = event.pageY - 10; // Default above cursor

                                        // Adjust x if tooltip would overflow right edge
                                        if (x + tooltipWidth > window.innerWidth) {
                                            x = event.pageX - tooltipWidth - 10; // Move to left of cursor
                                        }
                                        // Ensure x doesn’t go negative (left edge)
                                        if (x < 0) x = 10;

                                        // Adjust y if tooltip would overflow bottom edge
                                        if (y + tooltipHeight > window.innerHeight) {
                                            y = event.pageY - tooltipHeight - 10; // Move above cursor
                                        }
                                        // Ensure y doesn’t go negative (top edge)
                                        if (y < 0) y = 10;

                                        // Apply calculated position
                                        tooltipDiv.style.left = x + 'px';
                                        tooltipDiv.style.top = y + 'px';

                                        if (svgElem.attr('fill') !== 'transparent') {
                                            var bandColor = plotband.options.color || 'coral'; // Default if undefined
                                            var rgbaMatch = bandColor.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/);
                                            if (rgbaMatch) {
                                                var r = rgbaMatch[1];
                                                var g = rgbaMatch[2];
                                                var b = rgbaMatch[3];
                                                var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
                                                var a = Math.min(1, svgElem.originalOpacity + (isDarkMode ? 0.1 : 0.15));
                                                bandColor = `rgba(${r}, ${g}, ${b}, ${a})`;
                                                svgElem.attr({ fill: bandColor });
                                            }
                                        }
                                    };

                                    // Add mouseout event to hide tooltip
                                    plotband.label.element.onmouseout = function() {
                                        tooltipDiv.style.display = 'none';
                                        if (svgElem.attr('fill') !== 'transparent') {
                                            svgElem.attr({ fill: svgElem.originalColor });
                                        }
                                    };

                                    plotband.label.element.ontouchstart = plotband.label.element.onmouseover;
                                    plotband.label.element.ontouchend = plotband.label.element.onmouseout;
                                }
                            }
                        });
                    }

                    // Update credits position: plotTop + offset
                    this.credits.attr({
                        y: this.plotTop + 20 // 20px below plot area top
                    });

                },

                destroy: function() {
                    if (this.majorEventsThemeHandler)
                        document.documentElement.removeEventListener('themechange', this.majorEventsThemeHandler);
                    if (tooltipDiv && tooltipDiv.parentNode)
                        tooltipDiv.parentNode.removeChild(tooltipDiv);
                }
            },
            style: {
                fontFamily: 'Montserrat'
            }
        },
        plotOptions: {
            series: {
                dataGrouping: {
                    enabled: true,
                    forced: true,
                    approximation: 'close',
                    units: [
                        [unitType, [1]]
                    ]
                },
                states: {
                    inactive: {
                        opacity: 1
                    }
                },
            },
            flags: {
                title: '',
                style:{
                    color: 'rgba(0,0,0,0)',
                }
            },
            column: {
                stacking: isColumn ? undefined : 'normal',
                dataLabels: {
                    enabled: false
                },
                borderRadius: 1.5
            }

        },
        legend: {
            enabled: true,
            //verticalAlign: 'top',
            borderWidth: 0,
            //y: -35,
            layout: 'horizontal',
            labelFormatter: function() {
                var current = this.yData[this.yData.length - 1];
                var previous = this.yData[this.yData.length - 2];
                if (unitType == "week" || unitType == "month")
                {
                    /*var i = 2;
                    while (this.xData.length - i >= 0 && new Date(this.xData[this.xData.length - i]).getWeek() == new Date(this.xData[this.xData.length - 1]).getWeek())
                        i++;
                    if (this.yData.length - i - 1 >= 0)
                        previous = this.yData[this.yData.length - i - 1];*/

                    if (this.processedYData !== undefined && this.processedYData.length > 1)
                        previous = this.processedYData[this.processedYData.length - 2];
                }
                var chg_percent2 = (current / Math.abs(previous) - 1) * 100;
                var	chg_percent = current - previous;
                if(this.index !== 0){
                    chg_percent = rounding( chg_percent, Math.abs(chg_percent) < 0.1 ? 100 : 10 );
                }
                else
                {
                    chg_percent = rounding( chg_percent, 1000 );
                    var rounding_decimal = Math.abs(chg_percent2) < 0.1 ? 100 : 10;
                    chg_percent2 = rounding( chg_percent2, rounding_decimal );
                }
                var sign = "";
                if(chg_percent > 0)
                  	sign = "+";
              	else if (chg_percent === 0)
              	    sign = "\u00B1";

          	    if (unitType == 'month' && dataGrouping_approximation == 'sum' && this.index !== 0 && monthlyFund !== Infinity)
  	                current = monthlyFund;

          	    if (this.name == 'Hindenburg Omen')
          	        return '<span style="color:' + this.color + '">' + this.name + '</span>';
      	        else if (this.name == 'Recession')
          	        return '<span style="color:grey">' + this.name + '</span>';
                else if (!showPriceValues && this.index === 0 && showPriceTooltipLegend)
                    return '<span style="color:' + this.color + '">' + this.name + ': ' + PriceDisplayPolicy.formatPercent(chg_percent2) + '</span>';
          	    else if (current < 0)
          	    {
          	        if(this.index !== 0)
          	        {
          	            if (!showPriceTooltipLegend || dataGrouping_approximation == 'sum')
          	                return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:red">' + current + '</span></span>';
          	            else
                            return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:red">' + current + '</span> (' + sign + chg_percent + ")" + '</span>';
          	        }
                    else
                    {
                        if (showPriceTooltipLegend)
                            return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:red">' + current + '</span> (' + sign + chg_percent + (isFinite(chg_percent2) ? (", " + sign + chg_percent2 + "%") : "") + ")" + '</span>';
                        else
                            return '<span style="color:' + this.color + '">' + this.name + '</span>';
                    }
          	    }
          	    else
          	    {
          	        if (isNaN(current))
          	        {
          	            if (this.data.length === 0) // 1700 Events
          	                return '<span style="color:' + this.color + '">' + this.name + '</span>';
      	                else
          	                return '<span style="color:' + this.color + '">' + this.name + ': -</span>';
          	        }

                    if(this.index !== 0)
                    {
                        if (!showPriceTooltipLegend || dataGrouping_approximation == 'sum') // return % charts, southbound charts
                        {
                            if (current > 0)
                                return '<span style="color:' + this.color + '">' + this.name + ": +" + current + '</span>';
                            else
                                return '<span style="color:' + this.color + '">' + this.name + ": \u00B1" + current + '</span>';
                        }
                        //else if (dataGrouping_approximation == 'sum')
                        //    return '<span style="color:' + this.color + '">' + this.name + ": " + current + '</span>';
                        else
                            return '<span style="color:' + this.color + '">' + this.name + ": " + current + " (" + sign + chg_percent + ")" + '</span>';
                    }
                    else
                    {
                        if (showPriceTooltipLegend)
                            return '<span style="color:' + this.color + '">' + this.name + ": " + current + " (" + sign + chg_percent + (isFinite(chg_percent2) ? (", " + sign + chg_percent2 + "%") : "") + ")" + '</span>';
                        else
                            return '<span style="color:' + this.color + '">' + this.name + '</span>';
                    }
          	    }
            }
        },

        rangeSelector: {
            selected: rangeSelected,
            buttons: rangeSelector
        },

        title: {
            text: title,
            //margin: 38
        },
        subtitle: {
            text: subtitle,
        /*    floating: true,
            y: 128,
            style: {
                fontSize: '32px',
                fontWeight: 600,
                color : 'rgba(0, 0, 0, 0.05)',
            }*/
        },

        tooltip: {
            shared: true,
            split: false,
            useHTML: true,
            xDateFormat: '%A, %b %e, %Y',
            //valueDecimals: 4,
            formatter: function (tooltip) {
                if (unitType == "week")
                    return this.points.reduce(function (s, point) {
                        var point_series_color = point.series.color;
                        if (!showPriceValues && point.series.index === 0 && showPriceTooltipLegend)
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': <b>' + PriceDisplayPolicy.pointPercentChange(point, 2) + '</b>';
                        if (point.y < 0)
                        {
                            if (updowncolor)
                                point_series_color = '#F30';

                            if (point.series.name.indexOf('%') > -1)
                                return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + Math.round(point.y*10)/10 + '</span>';
                            else
                                return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + (point.y <= -20 ? Math.round(point.y*100)/100 : Math.round(point.y*1000)/1000) + '</span>';
                        }
                        else
                        {
                            if (updowncolor)
                                point_series_color = '#BDE000';

                            if (point.series.name.indexOf('%') > -1)
                                return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>' + Math.round(point.y*10)/10 + '</b>';
                            else
                            {
                                if(point.series.index === 0)
                                {
                                    var changeStr = '';
                                    var x = point.x;
                                    for(i = point.series.groupedData.length - 1; i >= 0; i--)
                                        if(point.series.groupedData[i].x == x)
                                        {
                                            if (i > 0)
                                            {
                                                var change = ((point.series.groupedData[i].y / Math.abs(point.series.groupedData[i - 1].y)) - 1) * 100;
                                                var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                                                change = rounding( change, rounding_decimal );
                                                if (isFinite(change))
                                                {
                                                    if (change > 0)
                                                        changeStr = ' (+' + change + '%)';
                                                    else if (change === 0)
                                                        changeStr = ' (\u00B1' + change + '%)';
                                                    else
                                                        changeStr = ' (' + change + '%)';
                                                }
                                            }
                                            break;
                                        }
                                    return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + (point.y >= 20 ? Math.round(point.y*100)/100 : Math.round(point.y*1000)/1000) + changeStr + '</b>';
                                }
                                else
                                    return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>' + (point.y >= 20 ? Math.round(point.y*100)/100 : Math.round(point.y*1000)/1000) + '</b>';
                            }
                        }
                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('Week from %A, %b %e, %Y', this.x) + '</span>');
                else if (unitType == "month")
                    return this.points.reduce(function (s, point) {
                        var point_series_color = point.series.color;
                        if (!showPriceValues && point.series.index === 0 && showPriceTooltipLegend)
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': <b>' + PriceDisplayPolicy.pointPercentChange(point, 2) + '</b>';
                        var yvalue = point.y;
                        if (dataGrouping_approximation == 'sum')
                            yvalue = rounding(point.y, 2);

                        if (point.y < 0)
                        {
                            if (updowncolor)
                                point_series_color = '#F30';

                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + yvalue + '</span>';
                        }
                        else
                        {
                            if (updowncolor)
                                point_series_color = '#BDE000';

                            if(point.series.index === 0)
                            {
                                var changeStr = '';
                                var x = point.x;
                                for(i = point.series.groupedData.length - 1; i >= 0; i--)
                                    if(point.series.groupedData[i].x == x)
                                    {
                                        if (i > 0)
                                        {
                                            var change = ((point.series.groupedData[i].y / Math.abs(point.series.groupedData[i - 1].y)) - 1) * 100;
                                            var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                                            change = rounding( change, rounding_decimal );
                                            if (isFinite(change))
                                            {
                                                if (change > 0)
                                                    changeStr = ' (+' + change + '%)';
                                                else if (change === 0)
                                                    changeStr = ' (\u00B1' + change + '%)';
                                                else
                                                    changeStr = ' (' + change + '%)';
                                            }
                                        }
                                        break;
                                    }
                                return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + changeStr + '</b>';
                            }
                            else
                            {
                                if (dataGrouping_approximation == 'sum') // southbound monthly chart
                                {
                                    if (point.y > 0)
                                        return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>+' + yvalue + '</b>';
                                    else
                                        return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>\u00B1' + yvalue + '</b>';
                                }
                                else
                                    return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>' + yvalue + '</b>';
                            }
                        }
                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%B %Y', this.x) + '</span>');
                else if (unitType == "day" && this.color != hindenburgColor) // not Hindenburg
                    return this.points.reduce(function (s, point) {
                        var point_series_color = point.series.color;
                        if (!showPriceValues && point.series.index === 0 && showPriceTooltipLegend)
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': <b>' + PriceDisplayPolicy.pointPercentChange(point, 2) + '</b>';
                        if (point.y < 0)
                        {
                            if (updowncolor)
                                point_series_color = '#F30';

                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + point.y + '</span>';
                        }
                        else
                        {
                            if (updowncolor)
                                point_series_color = '#BDE000';

                            if(point.series.index === 0)
                            {
                                if (showPriceTooltipLegend)
                                {
                                    var changeStr = '',
                                        index = point.point.index;
                                    if (index > 0) {
                                        var change = ((point.series.yData[index] / Math.abs(point.series.yData[index - 1])) - 1) * 100;
                                        var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                                        change = rounding( change, rounding_decimal );
                                        if (isFinite(change))
                                        {
                                            if (change > 0)
                                                changeStr = ' (+' + change + '%)';
                                            else if (change === 0)
                                                changeStr = ' (\u00B1' + change + '%)';
                                            else
                                                changeStr = ' (' + change + '%)';
                                        }
                                    }
                                    return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + changeStr + '</b>';
                                }
                                else
                                    return s;
                            }
                            else
                            {
                                if (!showPriceTooltipLegend || dataGrouping_approximation == 'sum') // return % charts, southbound daily chart
                                {
                                    if (point.y > 0)
                                        return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>+' + point.y + '</b>';
                                    else
                                        return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>\u00B1' + point.y + '</b>';
                                }
                                else
                                    return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                            }
                        }
                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>');
                else if (unitType == "second")
                    return this.points.reduce(function (s, point) {
                        return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y %H:%M:%S', this.x) + '</span>');
                else if (this.color == hindenburgColor)
                    return '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span><br>Hindenburg Omen';
                else
                    return tooltip.defaultFormatter.call(this, tooltip);
            },

        },

        credits: {
            enabled: true,
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '12px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                x: isNewYaxis ? 135 : 85,
                y: creditY
            }
        },

      	xAxis: {
            crosshair: {
                color: '#9d81ba88'
            },
            max: maxdate, // set future date in case of 1st series is short than the rest
            plotBands: majorEvents.length > 0 ? majorEvents : pb,
            tickPositioner: function () {
                const positions = []
                const axis = this
                const series = axis.chart.series[0]
                const data = series?.processedXData || []

                if (data.length < 2) return undefined

                const expectedDiff = 365 * 24 * 3600 * 1000
                const tolerance = 15 * 24 * 3600 * 1000
                let isYearly = true

                for (let i = 1; i < data.length - 1; i++) {
                    const diff = data[i] - data[i - 1]
                    if (Math.abs(diff - expectedDiff) > tolerance) {
                        isYearly = false
                        break
                    }
                }

                if (!isYearly && unitType != 'month') return undefined // let Highstock handle everything

                // ✅ Assign formatter directly to axis.labels
                axis.options.labels.format = '{value:%Y}' // Highcharts will use this format string

                const visibleMin = axis.min
                const visibleMax = axis.max
                const minYear = new Date(visibleMin).getUTCFullYear()
                const maxYear = new Date(visibleMax).getUTCFullYear()

                const dataMin = axis.dataMin
                const dataMax = axis.dataMax
                const startYear = new Date(dataMin).getUTCFullYear()
                const endYear = new Date(dataMax).getUTCFullYear()
                const endMonth = new Date(dataMax).getMonth()
                //const step = Math.ceil((endYear - startYear) / 10) || 1 // ~10 ticks max

                // Decide interval between ticks depending on visible range
                const visibleRange = axis.max - axis.min
                let stepYears = 1
                let step = 1

                if (visibleRange > 200 * expectedDiff) {
                    stepYears = 30
                    step = 10
                } else if (visibleRange > 100 * expectedDiff) {
                    stepYears = 20
                    step = 10
                } else if (visibleRange > 50 * expectedDiff) {
                    stepYears = 10
                    step = 5
                } else if (visibleRange > 20 * expectedDiff) {
                    stepYears = 4
                    step = 1
                } else if (visibleRange > 10 * expectedDiff) {
                    stepYears = 2
                    step = 1
                }

                // Align starting point to the nearest multiple of `step`
                const alignedEnd = Math.floor(endYear / step) * step

                for (let year = alignedEnd; year >= startYear; year -= stepYears) {
                    var ts = Date.UTC(year, 11, 1)
                    if (year == endYear)
                        ts = Date.UTC(year, endMonth, 1)
                    if (ts >= visibleMin && ts <= visibleMax) {
                        positions.push(ts)
                    }
                }

                return positions
            }
        },
        yAxis: [{
            type: YaxisType,
            crosshair: oneYaxis,
            title: {
                text: yaxis0,
                style: {
                  color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                }
            },
            opposite: true,
            lineWidth: 1,
            endOnTick: !oneYaxis || isStacked_Column,
            startOnTick: !(!oneYaxis || isStacked_Column),
            showLastLabel: true,
            tickAmount : YaxisType == 'logarithmic' ? undefined : 5,
            gridLineDashStyle: YaxisType == 'logarithmic' ? 'LongDashDotDot' : 'Solid',
            labels: {
                y: 5,
                align: 'right',
                reserveSpace: true,
                style: {
                  color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                }
            },
            plotLines: [{
                color: plotlevel_color, // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: plotlevel !== null && plotlevel.endsWith('_opposite') ? parseFloat(plotlevel.replace('_opposite', '')) : null, // Value of where the line will appear
                width: 4 // Width of the line
            }]
        }, {
            className: 'theme-left-y-axis-monochrome',
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: yaxis1,
                style: {
                    color: leftYAxisThemeColor
                }
            },
            opposite: false,
            lineColor: leftYAxisThemeColor,
            tickColor: leftYAxisThemeColor,
            lineWidth: 1,
            max: ymax,
            min: ymin,
            showLastLabel: true,
            tickAmount : 5,
            labels: {
                y: 5,
                style: {
                    color: leftYAxisThemeColor
                }
            },
            plotLines: [{
                color: plotlevel_color, // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: plotlevel !== null && plotlevel.endsWith('_opposite') ? null : parseFloat(plotlevel), // Value of where the line will appear
                width: 4 // Width of the line
            }]
        }],

        navigator: {
          yAxis :{
              type: YaxisType,
            },
          outlineWidth: 1,
          maskFill: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'rgba(0, 0, 0, 0.35)':'rgba(0, 0, 0, 0.15)';})(),
          handles: {
            backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#6ea8ff':'#eaf3ff';})(),
            borderColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#dbeafe':'#1f6feb';})(),
            symbols: [
              'customcircle',
              'customcircle'
            ]
          },
          series: {
            shadow: false,
            type: 'areaspline',
            lineColor: 'rgba(0, 128, 255, 0.5)',
            fillColor : {
                linearGradient : {
                    x1 : 0,
                    y1 : 0,
                    x2 : 0,
                    y2 : 1
                },
                stops : [[0, 'rgba(0, 128, 255, 0.5)'], [1, 'rgba(0, 255, 255, 0.1)']]
            },
          }
        },

        series: [{
            lineWidth:3,
            shadow: true,
            type: 'line',
            name: marketIndexDisplayName(name(types[0])),
            data: data[types[0]],
            yAxis: 0,
            //dataGrouping: {
            //    units: groupingUnits
            //}
        }]
    });

    if (majorEvents.length > 0)
    {
        var majorEventsChart = chart;
        majorEventsChart.majorEventsThemeHandler = function() {
            applyMajorEventsTheme(majorEventsChart);
        };
        document.documentElement.addEventListener('themechange', majorEventsChart.majorEventsThemeHandler);
    }

    if (isNewYaxis)
    {
        chart.addAxis({
            id: NewYaxisText,
            title: {
                text: NewYaxisText
            },
            opposite: false,
        });
    }

    for (var i = 1; i < types.length; i++)
    {
        if (types[i].indexOf('MA') !== -1)
        {
            seriesType = "line";
            seriesOpacity = 1;
        }
        else if (types[i].indexOf('Hindenburg') !== -1)
        {
            seriesType = "flags";
            seriesOpacity = 1;
        }
        else if (types[i].includes('_stacked') || types[i].includes('_column') || types[i].includes('_plotBands'))
        {
            seriesType = "column";
            if (types[i].endsWith('_plotBands'))
                seriesOpacity = 1;
            else
                seriesOpacity = 0.9;
        }

        if (data[types[i]] && data[types[i]].length > 0)
        {
            var colorSeries = new Highcharts.Color(Highcharts.getOptions().colors[i]).setOpacity(seriesOpacity).get();

    		var s = chart.addSeries({
    		    type: seriesType,
                name: marketIndexDisplayName(name(types[i].replace('_stacked', '').replace('_column', '').replace('_plotBands', '').replace('_newY', ''))),
    			data: types[i].includes('_plotBands') ? null : data[types[i]],
    			color: types[i].includes('_plotBands') ? plotbandsColor : types[i] == 'Hindenburg' ? hindenburgColor : updowncolor? {  linearGradient: { x1: 0,  x2: 0,  y1: 0,  y2: 1 },  stops: [  [0, '#BDE000'],	[0.7, '#BDD000'],	[1, '#BDC400']  ] } : colorSeries,
    			negativeColor: types[i] == 'Hindenburg' ? hindenburgColor : updowncolor? {  linearGradient: {  x1: 0,  x2: 0,  y1: 1,  y2: 0   },   stops: [ [0, '#F30'],	[0.7, '#e00'],	[1, '#d40']  ] } : colorSeries,
    			yAxis: oneYaxis && !types[i].includes('_stacked') && !types[i].includes('_column') && !types[i].includes('_plotBands') ? 0 : (types[i].includes('_newY') ? NewYaxisText : 1),
    			allowOverlapX: true,
    			zIndex: types[i].includes('_stacked') || types[i].includes('_column') || types[i].includes('_plotBands')? -1 : 1,
    			title: '',
    			fillColor: hindenburgColor,
    			//visible: !types[i].endsWith('_plotBands'),
    			shape: 'circlepin',
    			width: 5,
                height: 5,
                dataGrouping: {
                    enabled: true,
                    forced: true,
                    approximation: dataGrouping_approximation,
                    units: [
                        [unitType, [1]]
                    ]
                },
                plotbandId: types[i].endsWith('_plotBands') ? bandID : '',
                events: {
                    legendItemClick: function(e) {
                        //var ids = this.userOptions['plotbandId'];

                        if (this.userOptions['plotbandId'] === bandID) {

                            var hides = this.xAxis.plotLinesAndBands.filter(id => id.id == bandID);

                            for(var i = 0; i < hides.length; i++)
                            {
                                hides[i].svgElem.attr({
                                    opacity: hides[i].svgElem.opacity ? 0 : 1
                                })
                            }

                            for(var i = 0; i < hides.length; i++)
                            {
                                this.setVisible(hides[i].svgElem.opacity)
                            }
                            this.visible = false;
                        }

                        return true;
                    }
                }
        	}, true);

        	if (types[i].includes('_newY'))
        	{
            	var axis = s.yAxis;
                axis.update({
                    title: {
                        style: {
                            color: s.color
                        }
                    },
                    labels: {
                        style: {
                            color: s.color
                        }
                    }
                });
        	}
        }
    }

    if (majorEvents.length > 0)
    {
    	chart.addSeries({
		    id: 'plotbands-toggle',
            name: 'Events', // Legend label
            data: [], // No data to plot
            showInLegend: true,
            type: 'column',
            color: 'rgba(128, 128, 128, 0.7)',
            events: {
                hide: function() {
                    var chart = this.chart;
                    chart.xAxis[0].plotLinesAndBands.forEach(function(item) {
                        // Check if it's a plot band
                        if (item.options.from !== undefined && item.options.to !== undefined) {
                            item.svgElem.attr({ fill: 'transparent' }); // Hide the band
                            if (item.label) {
                                item.label.attr({ text: '' }); // Hide the label
                            }
                        }
                    });
                },
                show: function() {
                    var chart = this.chart;
                    chart.xAxis[0].plotLinesAndBands.forEach(function(item) {
                        // Check if it's a plot band
                        if (item.options.from !== undefined && item.options.to !== undefined) {
                            item.svgElem.attr({ fill: item.options.color }); // Restore color
                            if (item.label && item.options.label) {
                                item.label.attr({ opacity: 1 }); // Restore label
                                item.label.attr({ text: item.options.label.text }); // Restore label
                            }
                        }
                    });
                }
            }
    	}, true);
    }

	// remove Highcharts chart series data to prevent web scraping. if the charts act weird, remove this to restore to normal
    // this is also in market.php
    /*try
    {
        Highcharts.charts.forEach((element, index, array) => {
            if (array[index].title.textStr != "Advance Decline % (Daily)")  // don't empty AD Chart data as it can't scroll
                array[index].series.forEach((element, index, array) => {
                    array[index].options.data = [];
                });
        });
    }
    catch {}*/
}

function name(str)
{
    if (str.indexOf(">") > 0 && str.indexOf("d") == -1 && str.indexOf("w") == -1 )
        return "MA" + [str.slice(0, str.indexOf(">")+1), "MA", str.slice(str.indexOf(">")+1)].join('') + " %";
    else if (str.indexOf(">") === 0)
        return "Close" + [str.slice(0, str.indexOf(">")+1), "MA", str.slice(str.indexOf(">")+1)].join('') + " %";
    else if (str.indexOf("14d") === 0 || str.indexOf("14w") === 0)
        return "RSI" + str + " %";
    else if (str == "250HL")
        return "250-Day High - Low %";
    else if (str == "AD")
        return "Advance - Decline %";
    else if (str == "Osc")
        return "McClellan " + str;
    else if (str == "SumIdx")
        return "McClellan Summation Index";
    else if (str == "HKexShortSelling")
        return "Short Selling %";
    else if (str == "Hindenburg")
        return "Hindenburg Omen";
    else
        return str;
}

Date.prototype.getWeek = function() {
  var date = new Date(this.getTime());
  date.setHours(0, 0, 0, 0);
  // Thursday in current week decides the year.
  date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
  // January 4 is always in week 1.
  var week1 = new Date(date.getFullYear(), 0, 4);
  // Adjust to Thursday in week 1 and count number of weeks from date to week1.
  return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000
                        - 3 + (week1.getDay() + 6) % 7) / 7);
}

function getPlotBandPeriods(data, color, bandID) {
    let plotbands = [];
    let start = null;

    for (let i = 0; i < data.length; i++) {
        let [time, value] = data[i];

        if (value === 1 && start === null) {
            start = time; // Start of recession
        }
        if (value === 0 && start !== null) {
            plotbands.push({id: bandID, from: start, to: time, color: color });
            start = null; // Reset
        }
    }

    return plotbands;
}
