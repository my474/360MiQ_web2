function priceDeviation(chartcontainer, data, stockcode, MA_amount, High_amount, title, subtitle, yaxis0, yaxis1, unitType, rangeSelected, creditY, startx = null, endx = null)
{
    var visibleDict = {};
    var MA = {};
    var High = {};
    var seriesType = 'line';
    var seriesOpacity = 1;
    var maxdate = null;
    var sum = 0;
    var close = {};
    var dateclose = {};
    var MA_deviation = {};
    var drawdown = {};
    for (var j = 0; j < data.length; j++)
    {
        if (data[j] && data[j].length > 0)
        {
            var tmp = data[j][data[j].length - 1][0];
    		if (maxdate < tmp)
    		    maxdate = tmp;
    		    
		    if (j == MA_amount - 1)
		    {
                MA = [[data[j][0], (sum + data[j][4]) / MA_amount]];
                MA_deviation = [[data[j][0], (data[j][4]/MA[MA.length - 1][1] - 1) * 100]];
		    }
		    else if (j > MA_amount - 1)
		    {
                MA.push([data[j][0], (MA[MA.length - 1][1] * MA_amount - data[j - MA_amount][4] + data[j][4])/ MA_amount]);
                MA_deviation.push([data[j][0], (data[j][4]/MA[MA.length - 1][1] - 1) * 100]);
		    }
		    else
		    {
		        sum += data[j][4];
		    }
		    
		    if (close.length > 0)
		        close.push(data[j][4]);
	        else
	            close = [data[j][4]];
	            
            if (dateclose.length > 0)
		        dateclose.push([data[j][0], data[j][4]]);
	        else
	            dateclose = [[data[j][0], data[j][4]]];
        }
    }
    
    [High, drawdown] = findMovingMax(close, dateclose, High_amount);
    
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
                    /*if (visibleDict !== undefined)
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
                    }*/
                    this.legend.render();
                  },
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
                cursor: 'auto',
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

                var current = this.processedYData[this.processedYData.length - 1];
                var previous = this.processedYData[this.processedYData.length - 2];

                var changeStr = '';
                if (this.index === 0) {
                    var change = ((current / previous) - 1) * 100;
                    var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                    change = rounding( change, rounding_decimal );
                    if (change > 0)
                        changeStr = ' (+' + change + '%)';
                    else if (change === 0)
                        changeStr = ' (\u00B1' + change + '%)';
                    else
                        changeStr = ' (' + change + '%)';
                    
                    if (!PriceDisplayPolicy.showStockIndexPrices())
                        return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:' + this.color + '">' + PriceDisplayPolicy.formatPercent(change) + '</span>';
                    return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:' + this.color + '">' + current + changeStr + '</span>';
                }
                else
                {
                    var y = current;
                    if(this.index != 2)
                        y = rounding( current, Math.abs(current) < 0.01 ? 10000 : (Math.abs(current) < 0.5 ? 1000 : 100 ));
                        
                    return '<span style="color:' + this.color + '">' + this.name + ': <span style="color:' + this.color + '">' + y + '</span>';
                }

            }
        },

        rangeSelector: {
            inputEnabled: true,
            selected: rangeSelected,
            buttons: [{
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
            }, {
                type: 'all',
                text: 'All'
            }]
        },

        title: {
            text: title,
            //margin: 38
        },
        subtitle: {
            text: subtitle,
            /*floating: true,
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
                if (unitType == "day")
                {
                    var s = '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %B %d, %Y', this.x) + '</span>';
                                
                    $.each(this.points , function(i, point) {
                        
                        if(point.series.index === 0)
                        {
                            var changeStr = '',
                                index = point.point.index;
                            if (index > 0) {
                                var change = ((point.series.yData[index] / point.series.yData[index - 1]) - 1) * 100;
                                var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                                change = rounding( change, rounding_decimal );
                                if (change > 0)
                                    changeStr = ' (+' + change + '%)';
                                else if (change === 0)
                                    changeStr = ' (\u00B1' + change + '%)';
                                else
                                    changeStr = ' (' + change + '%)';
                            }
                            s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + (PriceDisplayPolicy.showStockIndexPrices() ? point.y + changeStr : PriceDisplayPolicy.formatPercent(change)) + '</b>';
                        }
                        else
                        {
                            var y = point.y;
                            if(point.series.index != 2)
                                y = rounding( point.y, Math.abs(point.y) < 0.01 ? 10000 : (Math.abs(point.y) < 0.5 ? 1000 : 100 ));
                                
                            s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + y + '</b>';
                        }
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
            href: '',
            style: {
                fontSize: '12px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                x: 25,
                y: creditY
            }
        },

      	xAxis: {
            crosshair: {
                color: '#9d81ba88'
            },
            //max: maxdate, // set future date in case of 1st series is short than the rest
            min: startx,
	    	max: endx,
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
            top: '25%',
            height: '50%',
        }, {
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: '% from MA' + MA_amount
            },
            opposite: true,
            lineWidth: 1,
            //max : ymax,
            //min: ymin,
            showLastLabel: true,
            //tickAmount : 5,
            labels: {
               // y: 5,
            },
            plotLines: [{
                color: 'pink', // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: 0, // Value of where the line will appear
                width: 3, // Width of the line    
                zIndex: 3,
            }],
            top: '75%',
            height: '25%',
            offset: 0,
        }, {
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: '% from ' + High_amount + '-day High'
            },
            opposite: true,
            lineWidth: 1,
            //max : ymax,
            //min: ymin,
            showLastLabel: true,
            //tickAmount : 5,
            labels: {
                //y: 5,
            },
            plotLines: [{
                color: 'pink', // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: 0, // Value of where the line will appear
                width: 3, // Width of the line    
                zIndex: 3,
            }],
            top: '0%',
            height: '25%',
            offset: 0,
        }
        ],
        
        navigator: {
          yAxis :{
              type: 'linear',
            },
          outlineWidth: 1,
          maskFill: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'rgba(0, 0, 0, 0.35)':'rgba(0, 0, 0, 0.15)';})(),
          handles: {
            backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#3f415c':'#ffffff';})(),
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
            //lineWidth:3,

            type: 'line',
            name: stockcode,
            data: dateclose,
            yAxis: 0,

            zIndex: 5,
            color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
            lineWidth: 3,
            shadow: {
                color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#6f8aff':'MidnightBlue';})(),
                width: 5,
                opacity: 0.5,
            },
            marker: {
                enabled: false,
                fillColor: 'white',
                lineWidth: 2,
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})()
            },
            states: {
                inactive: {
                    opacity: 1
                },
                //hover: {enabled: false },
            },
            dataLabels: {
                formatter: function() {
                    if(this.series.xAxis.userOptions.id == "navigator-x-axis") {
                         return false;   
                    }
                    return this.y;    
                }
            },
            showInNavigator: true,
        }]
    });
    
    colorSeries = "#7CB5EC";
    
    chart.addSeries({
    		    type: 'line',
    			name: 'MA' + MA_amount,
    			data: MA,
    			lineWidth: 3,
    			color: colorSeries,
    			negativeColor: colorSeries,
    			yAxis: 0,
    			allowOverlapX: true,
    			//zIndex: types[i].endsWith('_stacked') || types[i].endsWith('_column')? -1 : 1,
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
        	
        
    colorSeries = "#F7A35C";
    chart.addSeries({
		    type: 'line',
			name: High_amount + '-day High',
			data: High,
			lineWidth: 3,
			color: colorSeries,
			negativeColor: colorSeries,
			yAxis: 0,
			allowOverlapX: true,
			//zIndex: types[i].endsWith('_stacked') || types[i].endsWith('_column')? -1 : 1,
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
    	
	colorSeries = "#7CB5EC";
    chart.addSeries({
    		    type: 'area',
    			name: '% from MA' + MA_amount,
    			data: MA_deviation,
    			lineWidth: 3,
    			color: colorSeries,
    			negativeColor: colorSeries,
    			yAxis: 1,
    			allowOverlapX: true,
    			//zIndex: types[i].endsWith('_stacked') || types[i].endsWith('_column')? -1 : 1,
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
        	
        
    colorSeries = "#F7A35C";
    chart.addSeries({
		    type: 'area',
			name: '% from ' + High_amount + '-day High',
			data: drawdown,
			lineWidth: 3,
			color: colorSeries,
			negativeColor: colorSeries,
			yAxis: 2,
			allowOverlapX: true,
			//zIndex: types[i].endsWith('_stacked') || types[i].endsWith('_column')? -1 : 1,
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
    	
    /*for (var i = 1; i < types.length; i++)
    {
        if (data[types[i]] && data[types[i]].length > 0)
        {
            var colorSeries = new Highcharts.Color(Highcharts.getOptions().colors[i]).setOpacity(seriesOpacity).get();
            
    		chart.addSeries({
    		    type: seriesType,
    			name: types[i],
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
    chart.legend.render();*/
    
    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    return chart;
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

function findMovingMax(close, dateclose, windowSize) {
  // Create an empty array to store the moving maximums.
  var movingMaxs = {}
  var drawdown = {}

  // Iterate over the array, starting at the window size.
  for (let i = windowSize; i <= dateclose.length; i++) {
    // Get the current window of elements.
    const window = close.slice(i - windowSize, i);
    const window1 = dateclose.slice(i - windowSize, i);

    // Find the maximum value in the window.
    const max = Math.max(...window);

    // Add the maximum value to the moving maximums array.
    if (movingMaxs.length > 0)
    {
        movingMaxs.push([window1[window1.length - 1][0], max]);
        drawdown.push([window1[window1.length - 1][0], (window1[window1.length - 1][1]/max - 1) * 100]);
        
    }
    else
    {
        movingMaxs = [[window1[window1.length - 1][0], max]];
        drawdown = [[window1[window1.length - 1][0], (window1[window1.length - 1][1]/max - 1) * 100]];
    }
    
  }

  // Return the moving maximums array.
  return [movingMaxs, drawdown];
}
