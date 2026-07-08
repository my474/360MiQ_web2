function stockCompare(chartcontainer, data, types, title, subtitle, yaxis0, yaxis1, unitType, rangeSelected, creditY, from_Home_Market_StockInfo, clickable, isInputEnabled = false, datafull = null, creditURL = false)
{
    var highchartsSeriesNameMap = {
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
    var getHighchartsSeriesName = function (name) {
        return highchartsSeriesNameMap[name] || name;
    };

    var colors = [
        '#7cb5ec',
        '#434348',
        '#ec73ff',
        '#27F584',
        '#90ed7d',
        '#f7a35c',
        '#8085e9',
        '#f15c80',
        '#e4d354',
        '#2b908f',
        '#ff9b9b',
        '#91e8e1'
    ];
    
    var visibleDict = {};
    var codeNameDict = {};
    var seriesType = 'line';
    var seriesOpacity = 1;
    var plotlevel = 0;
    var maxdate = null;
    for (var j = 0; j < types.length; j++)
    {
        if (data[types[j]] && data[types[j]].length > 0)
        {
            var tmp = data[types[j]][data[types[j]].length - 1][0];
    		if (maxdate < tmp)
    		    maxdate = tmp;
        }
    }
    
    if (datafull !== null)
    {
        for (j = 0; j < datafull.length; j++)
        {
            if (datafull[j])
            {
                if (datafull[j].name.toUpperCase().endsWith(" CO. LTD."))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 9);
                if (datafull[j].name.toUpperCase().endsWith(" CORP."))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 6);
                if (datafull[j].name.toUpperCase().endsWith(" PLC"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 4);
                if (datafull[j].name.toUpperCase().endsWith(" LTD"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 4);
                if  (datafull[j].name.toUpperCase().endsWith(" LIMITED"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 8);
                if  (datafull[j].name.toUpperCase().endsWith(" HOLDINGS"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 9);
                if  (datafull[j].name.toUpperCase().endsWith(" INC"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 4);
                if  (datafull[j].name.toUpperCase().endsWith(" INC."))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 5);
                if  (datafull[j].name.toUpperCase().endsWith(" INCORPORATED"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 13);
                if  (datafull[j].name.toUpperCase().endsWith(" CORPORATION"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 12);
                if  (datafull[j].name.toUpperCase().endsWith(" CORP"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 5);
                if  (datafull[j].name.toUpperCase().endsWith(" (THE)"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 6);
                if  (datafull[j].name.toUpperCase().endsWith(" GROUP"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 6);
                if  (datafull[j].name.toUpperCase().endsWith(" AND COMPANY"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 12);
                if  (datafull[j].name.toUpperCase().endsWith(" & CO"))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 5);
                if  (datafull[j].name.toUpperCase().endsWith(" & CO."))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 6);
                if  (datafull[j].name.toUpperCase().endsWith(","))
                    datafull[j].name = datafull[j].name.substring(0, datafull[j].name.length - 1);
                    
                codeNameDict[datafull[j].code] = datafull[j].name;
            }
        }
    }
    else
    {
        for (j = 0; j < types.length - 1; j++)
        {
            codeNameDict[types[j]] = types[j];
            codeNameDict[getHighchartsSeriesName(types[j])] = getHighchartsSeriesName(types[j]);
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
            /*events: {
                load: function (event) {
                    this.yAxis[0].update({
                        lineColor: this.series[0].color
                    });
                    this.yAxis[1].update({
                        lineColor: this.series[1].color
                    });
                }
            },*/
            
            events: {
                render: function() {
                    //var firstValue = this.series[0].processedYData[0];

                    //var endValue = this.series[0].processedYData[this.series[0].processedYData.length - 1];
                    for (var i = 0; i < this.series.length; i++)
                    {
                        //if (visibleDict[this.series[i].name]) {
                            //visibleDict[this.series[i].name].push([this.series[i].processedYData[0], this.series[i].processedYData[this.series[i].processedYData.length - 1]]);
                        //} else {
                            visibleDict[this.series[i].name] = [{ x: this.series[i].processedXData[0], y: this.series[i].processedYData[0] }, { x: this.series[i].processedXData[this.series[i].processedXData.length - 1], y: this.series[i].processedYData[this.series[i].processedYData.length - 1] }];
                        //}
                    }
                    if (visibleDict !== undefined)
                    {
                        var minkey = Object.keys(visibleDict)[0];
                        var minValueX = visibleDict[minkey][0].x;
                        var maxkey = Object.keys(visibleDict)[1];
                        var maxValueX = visibleDict[minkey][1].x;
                        for (var key in visibleDict) {
                            if (visibleDict.hasOwnProperty(key) && key != "tmp" && key != "Navigator 1") {
                                if(visibleDict[key][1].x > maxValueX || (maxValueX === undefined && visibleDict[key][1].x !== undefined)){
                                   maxValueX = visibleDict[key][1].x;
                                   maxKey = key;
                                }
                                
                                if(visibleDict[key][0].x < minValueX || (minValueX === undefined && visibleDict[key][0].x !== undefined)){
                                   minValueX = visibleDict[key][0].x;
                                   minKey = key;
                                }
                            }
                        }
                        
                        this.setTitle(null, { text: Highcharts.dateFormat('%Y-%m-%d %a', minValueX)  + ' to ' + Highcharts.dateFormat('%Y-%m-%d %a', maxValueX) });
                    }
                    this.legend.render();
                },
                  
                load: function() {
                    this.credits.element.onclick = function() {
                        if (creditURL)
                        {
                            window.open('https://360miq.com', '_blank');
                        }
                    }
                    this.credits.element.onmouseover = function() {
                        if (creditURL)
                        {
                            this.style.fill = '#5CBCEE';
                        }
                    }
                    this.credits.element.onmouseout = function() {
                        if (creditURL)
                        {
                            this.style.fill = '#999999';
                        }
                    }
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
                compare: 'percent',
                compareStart: false,
                //cursor: clickable ? 'pointer' : 'auto', // indexes or top10 stocks
                point: {
                    events: {
                        mouseOver: function() {
                            if (clickable)
                            {
                                if (from_Home_Market_StockInfo == 2)
                                {
                                    if(this.series.name != types[0])
                                        if (this.series.halo)
                                        {
                                            this.series.halo.css({
                                                cursor: 'pointer'
                                            });
                                        }
                                }
                                else if (this.series.halo)
                                {
                                    this.series.halo.css({
                                        cursor: 'pointer'
                                    });
                                }
                            }
                        },
                        mouseOut: function() {
                            if (this.series.halo)
                            {
                                this.series.halo.css({
                                    cursor: 'auto'
                                });
                            }
                        }
                    }
                },
                events: {
                    click: function (event) {
                        if (clickable)
                        {
                            if (from_Home_Market_StockInfo == 2)
                            {
                                if(this.name != types[0])
                                    window.open("stockinfo?code=" + encodeURIComponent(this.name), '_blank');
                            }
                            else if (from_Home_Market_StockInfo == 1)
                                window.open("stockinfo?code=" + encodeURIComponent(this.name), '_blank');
                            else if (from_Home_Market_StockInfo === 0)
                            {
                                var exchange = 'NYSE';
                                if (this.name == 'Nas Composite')
                                    exchange = 'NASDAQ';
                                else if (this.name == 'UK 100')
                                    exchange = 'LSE';
                                else if (this.name == 'Toronto Composite')
                                    exchange = 'TSX';
                                else if (this.name == 'Australia 200')
                                    exchange = 'ASX';
                                else if (this.name == 'India 50')
                                    exchange = 'NSE';
                                else if (this.name == 'Japan 225')
                                    exchange = 'TYO';
                                else if (this.name == 'HSI')
                                    exchange = 'HKEX';
                                else if (this.name == 'Shanghai Composite')
                                    exchange = 'SHSE';
    
                                window.open("market?data=" + exchange + '#tab-5', '_blank');
                            }
                        }
                    },
                    mouseOver: function() {
                        if (clickable)
                        {
                            if (from_Home_Market_StockInfo == 2)
                            {
                                if(this.name != types[0])
                                    if (this.halo)
                                    {
                                        this.halo.css({
                                            cursor: 'pointer'
                                        });
                                    }
                            }
                            else if (this.halo)
                            {
                                this.halo.css({
                                    cursor: 'pointer'
                                });
                            }
                        }
                    },
                    mouseOut: function() {
                        if (this.halo)
                        {
                            this.halo.css({
                                cursor: 'auto'
                            });
                        }
                    }
                }
            },
            flags: {
                title: '',
                style:{
                    color: 'rgba(0,0,0,0)',
                }
            },
        },
        legend: {
            enabled: true,
            //verticalAlign: 'top',
            borderWidth: 0,
            //y: -35,
            layout: 'horizontal',
            labelFormatter: function() {
                if (this.processedYData === undefined)
                    return;

                var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                var performance = rounding((this.processedYData[this.processedYData.length - 1] / this.processedYData[0] - 1) * 100, 10);
                var sign = '';

                if(performance > 0)
                      sign = "+";
                    else if (performance === 0)
                        sign = "\u00B1";

                    if (performance < 0)
                    {
                        return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:red">' + performance + '%</span>';
                    }
                    else if (isNaN(performance))
                    {
                        return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">-</span>';
                    }
                    else
                    {
                        return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">' + sign + performance + '%</span>';
                    }
            }
        },

        rangeSelector: {
            inputEnabled: isInputEnabled,
            selected: rangeSelected,
            buttons: [{
                type: 'day',
                count: 1,
                text: '1d'
            },{
                type: 'week',
                count: 1,
                text: '1w'
            },{
                type: 'month',
                count: 1,
                text: '1m'
            },{
                type: 'month',
                count: 3,
                text: '3m'
            },{
                type: 'month',
                count: 6,
                text: '6m'
            }, {
                type: 'ytd',
                text: 'YTD'
            }, {
                type: 'year',
                count: 1,
                text: '1y'
            }, {
                type: 'year',
                count: 2,
                text: '2y'
            }, {
                type: 'year',
                count: 3,
                text: '3y'
            }, {
                type: 'year',
                count: 5,
                text: '5y'
            }, {
                type: 'year',
                count: 8,
                text: '8y'
            }]
        },

        title: {
            text: title,
            //margin: 38
        },
        /*subtitle: {
            text: subtitle,
            floating: true,
            y: 128,
            style: {
                fontSize: '32px',
                fontWeight: 600,
                color : 'rgba(0, 0, 0, 0.05)',
            }
        },*/
        
        tooltip: {
            shared: true,
            split: false,
            useHTML: true,
            xDateFormat: '%A, %b %e, %Y',
            //valueDecimals: 4,
            formatter: function (tooltip) {
                if (unitType == "day")
                {
                    var minkey = Object.keys(visibleDict)[0];
                    var minValueX = visibleDict[minkey][0].x;
                    for (var key in visibleDict) {
                        if (visibleDict.hasOwnProperty(key) && key != "tmp" && key != "Navigator 1") {
                            if(visibleDict[key][0].x < minValueX || (minValueX === undefined && visibleDict[key][0].x !== undefined)){
                               minValueX = visibleDict[key][0].x;
                               minKey = key;
                            }
                        }
                    }
                    var s = '<span style="font-size:10px">To: ' + Highcharts.dateFormat('%a, %B %d, %Y', this.x) + '<br>From: ' + Highcharts.dateFormat('%a, %B %d, %Y', minValueX) + '</span>';
                                
                    var sortedPoints = this.points.sort(function(a, b){
                        return ((a.y/visibleDict[a.series.name][0].y < b.y/visibleDict[b.series.name][0].y) ? -1 : ((a.y/visibleDict[a.series.name][0].y > b.y/visibleDict[b.series.name][0].y) ? 1 : 0));
                    });
                    
                    sortedPoints.reverse();
                    
                    $.each(sortedPoints , function(i, point) {
                        var performance = rounding((point.y/visibleDict[point.series.name][0].y - 1) * 100, 10);
                        if (performance < 0)
                            s += '<br/><span style="color:' + point.series.color + '">●</span> ' + codeNameDict[point.series.name] +': <span style="color:red;font-weight:bold">'+ performance + '%</span>';
                        else if (performance > 0)
                            s += '<br/><span style="color:' + point.series.color + '">●</span> ' + codeNameDict[point.series.name] +': <b>+'+ performance + '%</b>';
                        else
                            s += '<br/><span style="color:' + point.series.color + '">●</span> ' + codeNameDict[point.series.name] +': <b>\u00B1'+ performance + '%</b>';
                    });
        
                    return s;
                                            
                }                        
                                            
                 /*   return this.points.reduce(function (s, point) {
                        var point_series_color = point.series.color;
                        var performance = rounding((point.y/visibleDict[point.series.name][0][0] - 1) * 100, 10);
                        
                        if (performance < 0)
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + performance + '%</span>';
                        else if (performance > 0)
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>+' + performance + '%</b>';
                        else
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>\u00B1' + performance + '%</b>';

                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>');*/
                else
                    return tooltip.defaultFormatter.call(this, tooltip);
            },

        },
 
        credits: {
            enabled: true,
            text: '360MiQ.com',
            href: 'https://360miq.com',
            style: {
                fontSize: creditURL? '14px' : '12px',
                fontWeight: creditURL? '550' : 'normal',
                cursor: creditURL? 'pointer' : 'arrow'
            },
            position: {
                align: 'left',
                x: creditURL? 10 : 25,
                y: creditY
            }
        },

      	xAxis: {
            crosshair: {
                color: '#9d81ba88'
            },
            max: maxdate, // set future date in case of 1st series is short than the rest
        },
        yAxis: [{
            type: 'linear',
            crosshair: true,
            title: {
                text: yaxis0,
                //style: {
                  //color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                //}
            },
            opposite: true,
            endOnTick: false,
            startOnTick: false,
            lineWidth: 1,
            showLastLabel: true,
            tickAmount : 9,
            labels: {
                y: 5,
                align: 'right',
                reserveSpace: true,
                //style: {
                  //color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                //}
            },
            plotLines: [{
                color: '#c9c9c9', // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: plotlevel, // Value of where the line will appear
                width: 4, // Width of the line    
                zIndex: 3
            }]
        }, /*{
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: yaxis1
            },
            opposite: false,
            lineWidth: 1,
            //max : ymax,
            //min: ymin,
            showLastLabel: true,
            tickAmount : 5,
            labels: {
                y: 5,
            },
            plotLines: [{
                color: '#eaeaea', // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: plotlevel, // Value of where the line will appear
                width: 4 // Width of the line    
            }]
        }*/
        ],
        
        navigator: {
          yAxis :{
              type: 'linear',
            },
          outlineWidth: 1,
          maskFill: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'rgba(0, 0, 0, 0.35)':'rgba(0, 0, 0, 0.15)';})(),
          handles: {
            backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#4b5673':'#d9e6f2';})(),
            borderColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#555':'#777';})(),
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
            lineWidth: from_Home_Market_StockInfo == 2 ? 3 : 2,
            color: colors[0],
            shadow: from_Home_Market_StockInfo == 2,
            type: 'line',
            name: getHighchartsSeriesName(types[0]),
            data: data[types[0]],
            yAxis: 0,
            //dataGrouping: {
            //    units: groupingUnits
            //}
        }]
    });
    
    for (var i = 1; i < types.length; i++)
    {
        if (data[types[i]] && data[types[i]].length > 0)
        {
            var colorSeries = colors[i]; //new Highcharts.Color(Highcharts.getOptions().colors[i]).setOpacity(seriesOpacity).get();
            
    		chart.addSeries({
    		    type: seriesType,
                name: getHighchartsSeriesName(types[i]),
    			data: data[types[i]],
    			color: colorSeries,
    			negativeColor: colorSeries,
    			yAxis: 0,
    			allowOverlapX: true,
    			zIndex: types[i].endsWith('_stacked') || types[i].endsWith('_column')? -1 : 1,
    			title: '',
    			width: 5,
                height: 5,
                dataGrouping: {
                    enabled: true,
                    forced: true,
                    approximation: 'close',
                    units: [
                        [unitType, [1]]
                    ]
                }
        	}, true);
        }
    }
    
    chart.series[chart.series.length-2].remove(false); // Remove 2nd last series. It is a tmp series to avoid error. The last series is navigator series.
    chart.legend.render();

    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });
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
