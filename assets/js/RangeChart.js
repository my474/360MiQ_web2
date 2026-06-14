function rangeChart(rangecontainer, backgroundColor, low250, high250, low50, high50, lastprice, lastyearendprice, ma50, ma250, lastpricetxt, lastyearendpricetxt, ma50txt, ma250txt, range50txt, range250txt, color50a, color50b, color250a, color250b, ma50color, ma250color) {
    var rangeChartArgs = Array.prototype.slice.call(arguments);
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    if (dark && (backgroundColor === '#f9f9f9' || backgroundColor === '#ffffff')) {
      backgroundColor = '#2a2a3e';
    }
    (function (H) {
/*        if (low250 >= 10)
            low250 = Math.floor(low250 * 10) / 10;
        else
            low250 = Math.floor(low250 * 100) / 100;
            
        if (high250 >= 10)
            high250 = Math.ceil(high250 * 10) / 10;
        else
            high250 = Math.ceil(high250 * 100) / 100;
            
        if (low50 >= 10)    
            low50 = Math.floor(low50 * 10) / 10;
        else
            low50 = Math.floor(low50 * 100) / 100;
            
        if (high50 >= 10)
            high50 = Math.ceil(high50 * 10) / 10;
        else
            high50 = Math.ceil(high50 * 100) / 100;
*/            
//        lastprice = rounding(lastprice, 100);
//        if (lastyearendprice != '-')
//            lastyearendprice = rounding(lastyearendprice, 100);
        if (ma50 != '-')
        {
            if (ma50 >= 1)
                ma50 = rounding(ma50, 100);
            else
                ma50 = rounding(ma50, 10000);
        }
        if (ma250 != '-')
        {
            if (ma250 >= 1)
                ma250 = rounding(ma250, 100);
            else
                ma250 = rounding(ma250, 10000);
        }
        
        var defaultPlotOptions = H.getOptions().plotOptions,
            columnType = H.seriesTypes.column,
            wrap = H.wrap,
            each = H.each;

        defaultPlotOptions.lineargauge = H.merge(defaultPlotOptions.column, {});
        H.seriesTypes.lineargauge = H.extendClass(columnType, {
            type: 'lineargauge',
            //inverted: true,
            setVisible: function () {
                columnType.prototype.setVisible.apply(this, arguments);
                if (this.markLine) {
                    this.markLine[this.visible ? 'show' : 'hide']();
                }
            },
            drawPoints: function () {
                // Draw the Column like always
                columnType.prototype.drawPoints.apply(this, arguments);

                // Add a Marker
                var series = this,
                    chart = this.chart,
                    inverted = chart.inverted,
                    xAxis = this.xAxis,
                    yAxis = this.yAxis,
                    point = this.points[0], // we know there is only 1 point
                    markLine = this.markLine,
                    ani = markLine ? 'animate' : 'attr';

                // Hide column
                if (point.graphic !== undefined)
                    point.graphic.hide();

                if (!markLine) {
                    var path = inverted ? ['M', 0, 0, 'L', -5, -5, 'L', 5, -5, 'L', 0, 0, 'L', 0, 0 + xAxis.len] : ['M', 0, 0, 'L', -5, -5, 'L', -5, 5,'L', 0, 0, 'L', xAxis.len, 0];                
                    markLine = this.markLine = chart.renderer.path(path)
                        .attr({
                        fill: series.color,
                        stroke: series.color,
                            'stroke-width': 1
                    }).add();
                }
                markLine[ani]({
                    translateX: inverted ? xAxis.left + yAxis.translate(point.y) : xAxis.left,
                    translateY: inverted ? xAxis.top : yAxis.top + yAxis.len -  yAxis.translate(point.y)
                });
            }
        });
    })(Highcharts);
    
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });
    
    var chart = Highcharts.chart(rangecontainer, {
        chart: {
            backgroundColor: backgroundColor,
            type: 'lineargauge',
            inverted: true,
          	height: 75,
            events: {
                render: function() {
                    hideRangeChartLegendSymbols(this);
                }
            }
        },
        title: {
            text: null
        },
        xAxis: {
            lineColor: color250a,
            labels: {
                enabled: false
            },
            tickLength: 0,
            
        },
        yAxis: {
            //height: 25,
            min: low250,
            max: high250,
            //tickPositions: [low250, low50, high50, high250],
            tickPositioner: function () {
                var positions = [];
                positions.push(low250);
                
                var range = high250 - low250;
                
                if ((low50 - low250)/range > 0.14 && (low50 - low250)/range < 0.86)
                    positions.push(low50);
                
                if ((high50 - low250)/range > 0.14 && (high50 - low250)/range < 0.86)
                    positions.push(high50);

                positions.push(high250);
                return positions;
            },            
            tickLength: 0,
            tickWidth: 0,
            tickColor: '#C0C0C0',
            gridLineColor: '#C0C0C0',
            gridLineWidth: 0,
            minorTickInterval: 0,
            minorTickWidth: 0,
            minorTickLength: 0,
            minorGridLineWidth: 0,
            startOnTick: true,
            endOnTick: true,
            labels: {
                y: 10,
                style: {
                    color: 'grey',
                    fontSize:'10px'
                },
                formatter: function() {
                    return numberformat(this.value);
                }
            },

            title: {
                text: null
            },

            plotBands: [{
                from: low250,
                to: low50,
                //color: 'rgba(229, 27, 27, 1)'
                //color: '#F9E79F'
                color: {
                  linearGradient: {
                    x1: 0,
                    x2: 0,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, color250a],
                    [1, color250b]
                  ]
                }                
            }, {
                from: low50,
                to: high50,
                color: {
                  linearGradient: {
                    x1: 0,
                    x2: 0,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, color50a],
                    [1, color50b]
                  ]
                }                
            }, 
            {
                from: high50,
                to: high250,
                color: {
                  linearGradient: {
                    x1: 0,
                    x2: 0,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, color250a],
                    [1, color250b]
                  ]
                }
                //color: 'rgba(61, 197, 155, 1)'
            }]
        },
        
        legend: {
            y: 0,
            itemMarginTop: 0,
            padding: 0,
            margin: 0,
            enabled: true,
            itemStyle: {
              //font: '7pt Trebuchet MS, Verdana, sans-serif',
              font: '7.5pt Montserrat',
              color: dark ? '#cccccc' : '#606060',
              cursor: 'default'
            },
            symbolPadding: 0,
            symbolWidth: 0,
            symbolHeight: 0,
            symbolRadius: 0,
            useHTML: true,
            labelFormatter: function() {
                if(this.name==lastpricetxt){
                    var space = '2em';
                    if (lastpricetxt == "Price")
                    {
                        space = '6.2em';
                        var mute = dark ? '#cccccc' : '#606060';
                        return '<span style="font-weight:500;color:' + this.color + ';">' + '▼ </span><span style="font-weight:500;color:white;background:' + this.color + ';">' + this.name +': ' + this.yData[0] + '</span><span style="font-weight:500;"><font color="'+ma250color+'" style="padding-left:'+space+'">▼ ' + ma250txt + ': '+ ma250+ '</font><font color="'+color250a+'" style="padding-left:2em">█</font> <font color="'+mute+'">'+range250txt + '</span>';
                    }
                    else {
                        var mute = dark ? '#cccccc' : '#606060';
                        return '<span style="font-weight:500;color:' + this.color + ';">' + '▼ </span><span style="font-weight:500;color:white;background:' + this.color + ';">' + this.name +': ' + numberformat(this.yData[0]) + '</span><span style="font-weight:500;"><font color="'+ma250color+'" style="padding-left:'+space+'">▼ ' + ma250txt + ': '+numberformat(ma250)+ '</font><font color="'+color250a+'" style="padding-left:2em">█</font> <font color="'+mute+'">'+range250txt + '</span>';
                    }
                }
                if(this.name==lastyearendpricetxt){
                    var txt = ';">' + '▼ ' + this.name +': ' + lastyearendprice;
                    var space = '2.52em';
                    var color = this.color;
                    if (lastyearendpricetxt == "DON'T DISPLAY")
                    {
                        txt = ';">' + '▼ ' + lastpricetxt +': ' + numberformat(lastprice);
                        color = backgroundColor;
                        space = '2em';
                        return '<span style="font-weight:500;color:' + color + txt + '<font color="'+ma50color+'" style="padding-left:2em">▼ ' + ma50txt + ': '+numberformat(ma50)+ '</font><font color="'+color50a+'" style="padding-left:'+space+'">█</font> <font color="'+(dark?'#cccccc':'#606060')+'">'+range50txt+'</font></span>';
                    }
                    else
                        return '<span style="font-weight:500;color:' + color + txt + '<font color="'+ma50color+'" style="padding-left:2em">▼ ' + ma50txt + ': '+ ma50 + '</font><font color="'+color50a+'" style="padding-left:'+space+'">█</font> <font color="'+(dark?'#cccccc':'#606060')+'">'+range50txt+'</font></span>';
                }

            }   
        },

        tooltip: {
            positioner: function () {
                return { y: 0 };
            },
            useHTML: true,
            style: {
                fontSize: '10px',
            },
            padding: 3,
            backgroundColor: (dark?'rgba(42,42,62,0.93)':'rgba(255,255,255,.93)'),
            formatter: function () {
                return '<font color="'+color50a+'">█ </font>'+range50txt+': ' + numberformat(low50) + ' - ' + numberformat(high50) + '<br><font color="'+color250a+'">█ </font>'+range250txt+': ' + numberformat(low250) + ' - ' + numberformat(high250);
            },
        },
        
        credits: {
          enabled: false
        },

        exporting: { enabled: false },
        series: [{
    		name: ma50txt,
            data: [ma50 == '-'? -9999999999 : ma50],
            color: ma50color,
            dataLabels: {
                enabled: false,
                align: 'center',
                format: '{point.y}',
                y: -20,
            }
        },
        {
    		name: ma250txt,
            data: [ma250 == '-'? -9999999999 : ma250],
            color: ma250color,
            dataLabels: {
                enabled: false,
                align: 'center',
                format: '{point.y}',
                y: -20,
            }
        },
        {
    		name: lastyearendpricetxt,
            data: [lastyearendprice == '-'? -9999999999 : lastyearendprice],
            color: 'grey',
            dataLabels: {
                enabled: false,
                align: 'center',
                format: '{point.y}',
                y: 0,
            },
            states: {
              inactive: {
                opacity: 1
              }
            },
            events: {
                legendItemClick: function (e) {
                    e.preventDefault();
                }
            }
        },                 
        {
            name: lastpricetxt,
            data: [lastprice],
            color: 'GoldenRod',
            dataLabels: {
                enabled: true,
                align: 'center',
                //format: '{point.y}',
                formatter: function() {
                    if (this.series.name != "Price")
                        return numberformat(this.point.y);
                    else
                        return this.point.y;
                },
                y: 0,
                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
            },
            states: {
              inactive: {
                opacity: 1
              }
            },
            events: {
                legendItemClick: function (e) {
                    e.preventDefault();
                }
            }            
        }                 
    ]});

    registerRangeChartThemeRefresh(rangecontainer, rangeChartArgs);
    hideRangeChartLegendSymbols(chart);
    return chart;
}

function hideRangeChartLegendSymbols(chart) {
    try {
        if (!chart || !chart.series) return;
        for (var i = 0; i < chart.series.length; i++) {
            var series = chart.series[i];
            if (series.legendSymbol && series.legendSymbol.hide) series.legendSymbol.hide();
            if (series.legendLine && series.legendLine.hide) series.legendLine.hide();
            if (series.legendGroup && series.legendGroup.element && series.legendGroup.element.querySelectorAll) {
                var symbols = series.legendGroup.element.querySelectorAll('path, rect, circle');
                for (var j = 0; j < symbols.length; j++) {
                    symbols[j].setAttribute('opacity', '0');
                    symbols[j].setAttribute('fill-opacity', '0');
                    symbols[j].setAttribute('stroke-opacity', '0');
                }
            }
        }
    } catch(e) {}
}

function registerRangeChartThemeRefresh(rangecontainer, args) {
    window._rangeChartArgsByContainer = window._rangeChartArgsByContainer || {};
    window._rangeChartArgsByContainer[rangecontainer] = args;

    if (window._rangeChartThemeBound) return;
    window._rangeChartThemeBound = true;

    document.documentElement.addEventListener('themechange', function() {
        if (!window._rangeChartArgsByContainer) return;
        var containers = Object.keys(window._rangeChartArgsByContainer);

        for (var i = 0; i < containers.length; i++) {
            var id = containers[i];
            var savedArgs = window._rangeChartArgsByContainer[id];
            destroyRangeChartByContainer(id);
            rangeChart.apply(null, savedArgs);
        }
    });
}

function destroyRangeChartByContainer(containerId) {
    if (typeof Highcharts === 'undefined' || !Highcharts.charts) return;
    for (var i = Highcharts.charts.length - 1; i >= 0; i--) {
        var chart = Highcharts.charts[i];
        if (chart && chart.renderTo && chart.renderTo.id === containerId) {
            try { chart.destroy(); } catch(e) {}
        }
    }
}
