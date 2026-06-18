function treemap(data_orig, container, toShowPrice, d1_d5_d20, sectorDict, isMobile, highlight = "")
{
    var highlightstock = highlight.split(',');
    
    const data = JSON.parse(JSON.stringify(data_orig));
    //var maxdate = "1 day gain: ";
    var isIEX = false;
    var total_cap = 0;
    var drilled_down = false;
    
    if (typeof data !== 'undefined' && data.length > 0)
    {
        if (data[0].isIEX == 1)
            isIEX = true;
        //maxdate = data[0].tradedate + ": ";
        
        var period_idx = 0;
        if (d1_d5_d20 == 5)
            period_idx = 1;
        else if (d1_d5_d20 == 20)
            period_idx = 2;
            
        var items = Object.keys(sectorDict).map(function(key) {
            return [key, sectorDict[key][period_idx]];
        });
        
        // Sort the array based on the second element
        items.sort(function(first, second) {
            return second[1] - first[1];
        });
        
        var worstsectorcolor = {"fontColor": "#E97BA8", "fontWeight": "bold"};
        var bestsectorcolor = {"fontColor": "#D4AF37", "fontWeight": "bold"};

        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var headerFontColor = isDark ? '#e0e0e0' : '#000000';

        for (var i = 0; i < data.length; i++) {
            if (data[i].parent > 0)
            {
                total_cap += parseFloat(data[i].size);
                
                var change_pct_tmp = 0;
                var rounding_decimal = 10;
                if (data[i]['change_pct'] === undefined)
                {
                    if (d1_d5_d20 == 5)
                    {
                        change_pct_tmp = data[i].day_change_5d / (data[i].close - data[i].day_change_5d) * 100;
                        if (Math.abs(change_pct_tmp) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].day_change_5d === null ? null : rounding(change_pct_tmp, rounding_decimal);
                    }
                    else if (d1_d5_d20 == 20)
                    {
                        change_pct_tmp = data[i].day_change_20d / (data[i].close - data[i].day_change_20d) * 100;
                        if (Math.abs(change_pct_tmp) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].day_change_20d === null ? null : rounding(change_pct_tmp, rounding_decimal);
                    }
                    else
                    {
                        change_pct_tmp = data[i].day_change / (data[i].close - data[i].day_change) * 100;
                        if (Math.abs(change_pct_tmp) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].day_change === null ? null : rounding(change_pct_tmp, rounding_decimal);
                    }
                }
                else
                {
                    if (d1_d5_d20 == 5)
                    {
                        if (Math.abs(data[i].change_pct_5d) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].change_pct_5d === null ? null : rounding(data[i].change_pct_5d, rounding_decimal);
                    }
                    else if (d1_d5_d20 == 20)
                    {
                        if (Math.abs(data[i].change_pct_20d) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].change_pct_20d === null ? null : rounding(data[i].change_pct_20d, rounding_decimal);
                    }
                    else
                    {
                        if (Math.abs(data[i].change_pct) < 0.1)
                            rounding_decimal = 100;
                        data[i]['value'] = data[i].change_pct === null ? null : rounding(data[i].change_pct, rounding_decimal);
                    }
                }
            }
            else if (data[i].parent === 0)
            {
                if (sectorDict[data[i].product] && data[0].product.endsWith(sectorDict[data[i].product][3]))
                {
                    if (sectorDict[data[i].product][period_idx] == items[0][1])
                        data[i]["header"] = bestsectorcolor;
                    else if (sectorDict[data[i].product][period_idx] == items[items.length - 1][1])
                        data[i]["header"] = worstsectorcolor;

                    data[i].product += ' ' + (sectorDict[data[i].product][period_idx] > 0 ? '+' : (sectorDict[data[i].product][period_idx] === 0 ? '\u00B1' : '')) + sectorDict[data[i].product][period_idx] + '%';
                }
            }

            if (data[i].parent === 0 || data[i].size === undefined)
            {
                data[i]["hoverHeader"] = Object.assign({}, data[i]["header"] || {}, {
                    "fontColor": headerFontColor
                });
            }
        }

    }
    else
        return;
        
    anychart.licenseKey("360miq-3e54a810-e0920e24");
    // makes tree from the data for the sample
    var dataTree = anychart.data.tree(data, 'as-table');
    var chart = anychart.treeMap(dataTree);
    chart.credits().enabled(false);
    chart.headers().fontColor(headerFontColor).background().fill(isDark ? "#2a2a3e" : "#F7F7F7").stroke(isDark ? "#3a3a4e" : "#e0e0e0");
    if (isDark)
        chart.hovered().headers().background().fill("#3a3a50").stroke("#55556a");
    
    if (isMobile)
        chart.contextMenu(false);
    else
    {
    	chart.contextMenu().itemsFormatter(function(items){
            // Disable save as image option
            delete items["select-marquee-start"];
            delete items["select-marquee-separator"];
            delete items["save-data-as"];
           
            // delete about and separator 
            delete items["full-screen-separator"];
            delete items["about"];
           
            // return modified array
            return items;
        });
    }
    

    // sets title for chart and customizes it
    /*chart.title()
            .enabled(false)
            .useHtml(true)
            .padding([0, 0, 20, 0])
            .text(
                    'Top ACME Products by Revenue<br/>' +
                    '<span style="color:#212121; font-size: 13px;">(average sales during the year, in $)</span>'
            );*/

    //var credits = chart.credits();
    // put your license key here and uncomment the next line
    // anychart.licenseKey("COPY-PASTE-YOUR-LICENSE-KEY");
    // credits are enabled by default. The only exception is our  playground site. You can remove next line in production.
    //credits.enabled(isIEX);
    //credits.text("IEX Cloud");
    //credits.url("//iexcloud.io");

    // sets scale
    var scale = anychart.scales.ordinalColor([
        {less: -4},
        {from: -4, to: -2},
        {from: -2, to: -0.5},
        {from: -0.5, to: 0.5},
        {from: 0.5, to: 2},
        {from: 2, to: 4},
        {greater: 4}
    ]);

    // sets colors for scale
    scale.colors([['#ff0000', '#ef0000'], ['#bf0000', '#af0000'], ['#7f0000', '#6f0000'], ['#7e7e7e', '#6e6e6e'], ['#008800', '#007800'], ['#00bf00', '#00af00'], ['#00ff00', '#00ef00']]);

    // sets chart settings
    chart.padding([0, 0, 0, 0])
            // setting the number of levels shown
            .maxDepth(2)
            .selectionMode('none')
            .colorScale(scale);

    // sets padding for legend
    chart.legend()
            .enabled(false)
            .padding([0, 0, 0, 20])
            .position('right')
            .align('top')
            .itemsLayout('vertical');

    // sets settings for labels
    chart.labels()
            .useHtml(true)
            .fontColor(isDark ? '#cccccc' : '#ffffff')
            //.fontSize(12)
            .format(function () {
                var fontsize_code = 12;
                if (!drilled_down && total_cap > 0 && this.size / total_cap < 0.005 && this.getData('product').length > 4)
                    fontsize_code = 10;

                var color_code = "";
                var color_percent = "";
                // yellow label for highlighted stocks
                for (var i = 0; i < highlightstock.length; i++)
                {
                    if (this.getData('product') == highlightstock[i])
                    {
                        color_percent = ";color:yellow";
                        color_code = color_percent + ";font-weight:bold";
                    }
                }

                return '<span style="font-size:' + fontsize_code + 'px' + color_code + '">' + this.getData('product') + '</span><br><span style="font-size:10px' + color_percent + '">' + (this.value > 0? '+': '') +
                        (isNaN(this.value) ? '- ' : anychart.format.number(this.value, {groupsSeparator: ' '})) + '%</span>';
            });

    chart.listen("drillchange", function(e){
        drilled_down = !drilled_down; // So that no label size shrink on drill down
    });
    
    // sets settings for headers
    chart.headers().format(function () {
        var a = this.getData('product');
        if (this.getData('size') === undefined)
        {
            var date = str2date(a.substring(a.indexOf(",") + 2)).toLocaleString("default", { weekday: "short" });
            if (d1_d5_d20 > 1)            
                return a.replace(",", ", " + d1_d5_d20 + " Days Performance,") + ' ' + date;
            else
                return a.replace(",", ", 1 Day Performance,") + ' ' + date;
        }
        return a;
    });

    // sets settings for tooltip
    chart.tooltip()
            .useHtml(true)
            .titleFormat(function () {
                if (this.getData('id') !== undefined) // market or sector
                    return this.getData('product');
                    
                var tc = this.getData('tc');
                var en = this.getData('en');
                if ((tc === undefined || tc === null) && (en === undefined || en === null))
                    return this.getData('product');
                else if (tc === undefined || tc === null)
                    return this.getData('product') + ' \u25CF ' + en;
                else if (en === undefined || en === null)
                    return this.getData('product') + ' \u25CF ' + tc;
                else
                    return this.getData('product') + ' \u25CF ' + tc + ' \u25CF ' + en;
            })
            .format(function () {
                if (this.getData('id') === 0) // market
                    return '';
                else if (this.getData('id') !== undefined) // sector
                    return 'Click to zoom';

                if (this.getData('series') !== undefined)
                {
                    var cs = this.getData('series');
                    if (this.getData('close') !== undefined)
                    {
                        var c = this.getData('close');
                        cs = (cs.endsWith(',' + c) ? cs : cs + ',' + c); // if cs == c also makes cs + ',' + c. If not, no sparkline is drawn as only 1 point
                    }
                    var closeSeries = JSON.parse("[" + cs + "]");

                    if (closeSeries.length > 1 &&
                        (closeSeries[closeSeries.length - 1] / closeSeries[closeSeries.length - 2] > 90 || closeSeries[closeSeries.length - 2] / closeSeries[closeSeries.length - 1] > 90))
                    {
                        closeSeries.pop(); // remove last item
                    }
                        
                    setTimeout(function() {
                        var tooltip_div = "hc-tooltip";
                        if($('#' + tooltip_div).length)
                        {
                            Highcharts.chart(tooltip_div, {
                                chart: {
                                    type: 'areaspline',
                                    //width: 60,
                                    height: '70px', // if using % such as 40%, height will vary based on width which in turn based on the stockname length. It keeps aspect ratio, but may be too large sometimes
                                    //plotAreaWidth: 60,
                                    //plotAreaHeight: 20
                                    backgroundColor: 'rgba(0,0,0,0)',
                                    spacingBottom: 0,
                                    spacingTop: 3,
                                    style: {
                                        fontFamily: 'Montserrat'
                                    }
                                },
                                title: {
                                    text: null
                                },
                              	xAxis: {
                              	    margin: 0,
                              	    lineWidth: 0,
                              	    endOnTick: false,
                                    startOnTick: false,
                                    tickPositions: [],
                                    padding: 0,
                                    title: {
                                        //align: 'high',
                                        offset: 0,
                                        text: '6 months',
                                        //rotation: 0,
                                        //y: 0,
                                        style: { "color": (document.documentElement.getAttribute('data-theme')==='dark'?'#aaa':'#ccc'), "fontSize": "12px" },
                                    }
                                },
                                yAxis: {
                                    gridLineColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#45475f':'#444'),
                                    visible: true,
                                    min: Math.min.apply(this, closeSeries) * 0.98, // : Math.min(...data) * 0.98,
                                    max: Math.max.apply(this, closeSeries) * 1.02, // : Math.max(...data) * 1.02
                                    opposite: true,
                                    offset: 0,
                                    endOnTick: false,
                                    startOnTick: false,
                                    labels: {
                                        enabled: true,
                                        x: 1, 
                                        style: {
                                            color: (document.documentElement.getAttribute('data-theme')==='dark'?'#aaa':'#ccc'),
                                            opacity: 1,
                                            fontSize: 12,
                                        },
                                        formatter: function() {
                                            if ( this.value >= 10000 )
                                                return Highcharts.numberFormat( this.value/1000, 0) + "K";  //  only switch if >= 10000
                                            else if ( this.value >= 1000000 )
                                                return Highcharts.numberFormat( this.value/1000000, 2) + "M";  //  only switch if >= 1000000
                                            else if (this.value < 0.01)
                                                return Highcharts.numberFormat(this.value,4);
                                            else if (this.value < 0.1)
                                                return Highcharts.numberFormat(this.value,3);
                                            else if (this.value < 1)
                                                return Highcharts.numberFormat(this.value,2);
                                            else if (this.value < 10 || (this.axis.max - this.axis.min) < 5)
                                                return Highcharts.numberFormat(this.value,1);
                                            else
                                                return Highcharts.numberFormat(this.value,0);
                                        }
                                    },
                                    title: {
                                        text: null
                                    },
                                    tickPositioner: function () {
                                        var positions = [],
                                            tick = Math.floor(this.dataMin),
                                            increment = (this.dataMax - this.dataMin) / 2;
                            
                                        if (this.dataMax !== null && this.dataMin !== null) {
                                            for (tick; tick - increment <= this.dataMax; tick += increment) {
                                                positions.push(tick);
                                            }
                                        }
                                        return positions;
                                    }
                                },
                                legend: { enabled: false },
                                
                            	exporting: { enabled: false },
                            
                                credits: {
                                    enabled: false
                                },
                                series: [{
                                    data: closeSeries,
                                    marker: {
                                        enabled: false
                                    }
                                }]
                            });
                        }
                    }, 100);
                }
                
                var dayStr = "1 Day ";
                var dayStrShort = "1d: ";
                if (d1_d5_d20 == 5)
                {
                    dayStr = "5 Days ";
                    dayStrShort = "5d: ";
                }
                else if (d1_d5_d20 == 20)
                {
                    dayStr = "20 Days ";
                    dayStrShort = "20d: ";
                }

                var industry = this.getData('industry');
                if (industry === null || industry == '')
                    industry = '-';
                    
                if (this.getData('close') === undefined && this.getData('day_change') === undefined)
                {
                    return '<span style="color: #bfbfbf">' + dayStr + 'Change: ' + '</span>' + (this.value > 0? '+': '') +
                        (isNaN(this.value) ? '- ' : anychart.format.number(this.value, {groupsSeparator: ' '})) + '%' + '<br><span style="color: #bfbfbf">' + 'Industry: ' + '</span>' + industry + '<div id="hc-tooltip" style="height:70px;margin-top:3px"></div>';
                }
                else
                {
                    var change = parseFloat(this.getData('day_change'));
                    if (d1_d5_d20 == 5)
                        change = parseFloat(this.getData('day_change_5d'));
                    else if (d1_d5_d20 == 20)
                        change = parseFloat(this.getData('day_change_20d'));
                        
                    if (toShowPrice == 1 || toShowPrice == 3)
                    {
                        return '<span style="color: #bfbfbf">' + 'Price: ' + '</span>' + this.getData('close') + ' (' + dayStrShort + (change > 0? '+': '') + (isNaN(change) ? '-' : change) + ', ' + (this.value > 0? '+': '') +
                            (isNaN(this.value) ? '- ' : anychart.format.number(this.value, {groupsSeparator: ' '})) + '%)' + '<br><span style="color: #bfbfbf">' + 'Industry: ' + '</span>' + industry + '<div id="hc-tooltip" style="height:70px;margin-top:3px"></div>';
                    }
                    else
                    {
                        return '<span style="color: #bfbfbf">' + dayStr + 'Change: ' + '</span>' + (this.value > 0? '+': '') +
                            (isNaN(this.value) ? '- ' : anychart.format.number(this.value, {groupsSeparator: ' '})) + '%' + '<br><span style="color: #bfbfbf">' + 'Industry: ' + '</span>' + industry + '<div id="hc-tooltip" style="height:70px;margin-top:3px"></div>';
                    }
                }
            });

    chart.hovered().stroke("3 yellow");
    chart.normal().stroke(isDark ? "#555" : "black");

    // set container id for the chart
    chart.container(container);
    // initiate chart drawing
    chart.draw();

    // store render params so treemap can be redrawn on theme change
    var el = document.getElementById(container);
    if (el) {
        el._treemapArgs = [data_orig, container, toShowPrice, d1_d5_d20, sectorDict, isMobile, highlight];
    }

    $('#' + container).on('mouseleave', function(){
        for (var i = Highcharts.charts.length - 1; i >= 0; i--) {
            var chart = Highcharts.charts[i];
            if (!chart || chart === undefined || chart.renderTo === null)
                Highcharts.charts.splice(i);
            else if (chart.renderTo.id == "hc-tooltip")
            {
                chart.destroy();
                Highcharts.charts.splice(i);
            }
        }
    });
    
    if (toShowPrice == 1)
    {
        // create an event listener for the mouseOver event
        chart.listen("mouseOver", function(e){
            // change the cursor style
            document.getElementById(container).style.cursor = "pointer";
        });
        
        // create an event listener for the mouseOut event
        chart.listen("mouseOut", function(e){
            // set the default cursor style
            document.getElementById(container).style.cursor = "auto";
        });

        chart.listen("pointClick", function(e) {
            var point = e.point;
            var index = point.getIndex();
            
            var previous;
            var count = 0;
            for (var i = 0; i < data.length; i++)
            {
                if (data[i].parent === 0)
                {
                    previous = data[i].parent;
                    count--;
                    continue;
                }
                else if (data[i].parent != previous)
                {
                    previous = data[i].parent;
                    count++;
                }
    
                if (i + count == index)
                {
                    if (data[i].parent !== null)
                        //location.href = "stockinfo?code=" + data[i].product;
                        window.open("stockinfo?code=" + encodeURIComponent(data[i].product), '_blank');
                    break;
                }
            }

        });
    }
}

/* ---- redraw all treemaps on theme change without page refresh ---- */
document.documentElement.addEventListener('themechange', function() {
    var containers = document.querySelectorAll('[id^="chartcontainer"], [id^="treecontainer"]');
    for (var i = 0; i < containers.length; i++) {
        var el = containers[i];
        if (el._treemapArgs) {
            el.innerHTML = '';
            (function(args) {
                setTimeout(function() {
                    treemap.apply(null, args);
                }, 50);
            })(el._treemapArgs);
        }
    }
});
