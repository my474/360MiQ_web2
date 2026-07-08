function TSFchart(stock, pricedata, container, subtitle)
{
    var rows = pricedata.split("\n");
    
    var close = [];
    var newest_closedate = -1;

    var tradedateLatest;
    for (i = 0; i < rows.length; i++)
    {
        var fields = rows[i].split(",");
        var fieldslast = i<rows.length-1 && i > 0 ? rows[i-1].split(",") : "";
        if (fields.length != 6)
            continue;
            
        var tradedate = str2date(fields[0]);
        tradedateLatest = tradedate;
        if (close === undefined || close.length === 0 || (close.length > 0 && close[close.length - 1][0] < utc(str2date(formatDate(tradedate)))))
            close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[4])]);

    }
    
    if (close.length > 0)
        newest_closedate = close[close.length - 1][0];

    var forecastSteps = 10;
    var forecast = tsf(close.slice(0), forecastSteps);

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
        'a', r, r, 0, 1, 0, -2 * r, 0,
        'z'
      ];
    };

    if (Highcharts.VMLRenderer) {
      Highcharts.VMLRenderer.prototype.symbols.customcircle =
        Highcharts.SVGRenderer.prototype.symbols.customcircle;
    }

    var chart = Highcharts.chart(container, {
        chart: {
            style: {
                fontFamily: 'Montserrat'
            },
        },
        title: {
            text: stock + ' ' + forecastSteps + '-Day Trend Forecast'
        },
        subtitle : {
            text: subtitle
        },
        xAxis: {
            plotLines: [{
              color: 'lightgrey',
              dashStyle: 'ShortDash',
              width: 3,
              value: newest_closedate,//Date.UTC(2019, 8, 27),
              zIndex: 3,
              label: {
                text: 'Forecast',
                x: 10,
                y: -5,
                verticalAlign: 'bottom',
                rotation: 0,
                style: {
                  color: 'darkorange',
                  fontWeight: 'bold',
                  fontSize: '13px',
                }
              }
            },
            {
              color: 'lightgrey',
              dashStyle: 'ShortDash',
              width: 3,
              value: newest_closedate, //Date.UTC(2019, 8, 27),
              zIndex: 3,
              label: {
                text: 'Actual',
                x: -56,
                y: -5,
                align: 'left',
                verticalAlign: 'bottom',
                rotation: 0,
                style: {
                  color: 'darkgrey',
                  fontWeight: 'bold',
                  fontSize: '13px',
                }
              }
            }],
            type: 'datetime',
            crosshair: {
                color: '#9d81ba88'
            },
        },

        yAxis: {
            endOnTick: false,
            startOnTick: false,
            title: {
                text: "Stock Price"
            },
			labels: {
                x: 5,
			},            
			opposite: true,
			crosshair: {
                color: '#9d81ba88'
            },
        },
        rangeSelector: {
            enabled: true,
            selected: 0,
            buttons: [{
                type: 'month',
                count: 4,
                text: '4m'
            }, {
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
                type: 'all',
                text: 'All'
            }]
        },
        tooltip: {
            crosshairs: true,
            shared: true,
            formatter: function(tooltip) {
                if (tooltip.chart.hoverSeries.name == 'Price Tag') {
                    var x = tooltip.chart.hoverPoint.options.x;
                    var y = tooltip.chart.hoverPoint.options.y;
                    var txt = '';
                    var data = tooltip.chart.hoverSeries.data;

                    if (data[1].x === null && data[1].y == y && data[1].y == data[0].y &&
                        data[2].x === null && data[2].y == y && data[2].y == data[1].y)
                        txt += 'Min, Max & Latest';    
                    else if (data[2].x === null && data[2].y == y && data[2].y == data[1].y)
                        txt += 'Max & Latest';
                    else if (data[2].x === null && data[2].y == y && data[2].y == data[0].y)
                        txt += 'Min & Latest';
                    else if (data[1].x === null && data[1].y == y && data[1].y == data[0].y)
                        txt += 'Min & Max';
                    else if (x == data[0].x)
                        txt = 'Min';
                    else if (x == data[1].x)
                        txt = 'Max';
                    else if (x == data[2].x)
                        txt = 'Latest';

                    /*if (tooltip.chart.hoverPoint.index == 0)
                        txt = 'Min: ';
                    else if (tooltip.chart.hoverPoint.index == 1)
                        txt = 'Max: ';*/
                   // if (x == tooltip.chart.hoverPoints[])
                    return'<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span><br>' + txt + ': <b>' + y + '</b>';
                }
                /*else if (tooltip.chart.hoverSeries.name == 'Forecast') {
                    return 'Forecast';
                }*/
                else
                    return this.points.reduce(function (s, point) {
                        if (point.series.index === 0)         
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
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + (PriceDisplayPolicy.showStockIndexPrices() ? point.y + changeStr : PriceDisplayPolicy.formatPercent(change)) + '</b>';
                        }
                        else if (point.series.index == 1)         
                        {
                            if (point.point.index === 0)
                                return 'Forecast';
                            else
                            {
                                if (point.y >= 0.5)
                                    return 'Day ' + point.point.index + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + Math.round(point.y * 100) / 100 + '</b>';
                                else if (point.y < 0.5 && point.y >= 0.05)
                                    return 'Day ' + point.point.index  + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + Math.round(point.y * 1000) / 1000 + '</b>';
                                else
                                    return 'Day ' + point.point.index  + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                            }
                        }
                        else if (Array.isArray(point.series.yData[0]))
                        {
                            var index = point.point.index;
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.series.yData[index][0] + '</b> - <b>' + point.series.yData[index][1] + '</b>';
                        }
                        else
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';

                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>');
                //return tooltip.defaultFormatter.call(this, tooltip);
            }
        },

        credits: {
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '12px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                x: 20,
                y: -370
            }
        },

        scrollbar: {
            enabled: true
        },

        legend: {
          enabled: true
        },
        navigator: {
            enabled: true,
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

        /*plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    align: 'left',
                    x: -5,
                    color: 'darkslategrey',
                    formatter: function() {
                        if (this.point.index == this.series.points.length - 1 && this.series.index == 0) {
                            return this.y;
                        } else {
                            return null;
                        }
                    },
                    crop: false,
                    overflow: false
                }
            }
        },*/
    
        series: [{
            name: stock,
            data: close,
            zIndex: 5,
            color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
            lineWidth: 4,
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
        },
        {
            //enableMouseTracking: false,
            visible: true,
            name: forecast === null || forecast.length === 0 ? 'No Forecast Available' : 'Forecast',
            id: 'forecast',
            data: forecast,
            type: 'line',
            lineWidth: 6,
            shadow: {
                color: 'gold',
                width: 7,
                opacity: 0.5,
            },
            //linkedTo: ':previous',
            color: 'orange',
            zIndex: 4,
            marker: {
                enabled: false
            },
            states: { 
                hover: {enabled: true },
                inactive: {
                    opacity: 1
                },
            },
        }
        ]
    });
    
    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    //return TSFComment(stock, close, forecast);
}

function ema0(mArray, mRange) {
    if (mArray === null || mArray.length < mRange)
        return null;

    var k = 2/(mRange + 1);
    // first item is just the same as the first item in the input

    var emaArray = [];
    var sum = 0;
    for (var j = 0; j < mRange; j++) {
        sum += mArray[j][1];
    }
    emaArray.push(sum / mRange);
    // for the rest of the items, they are computed with the previous one
    for (var i = mRange; i < mArray.length; i++) {
        emaArray.push(mArray[i][1] * k + emaArray[emaArray.length - 1] * (1 - k));
    }
    if (emaArray.length == 0)
        return null;
    else
        return emaArray[emaArray.length - 1];
}

function tsfLookback(optInTimePeriod)
{
    if ((optInTimePeriod < 10) || (optInTimePeriod > 100000))
        return -1;
	else if (optInTimePeriod > 20)
		return 19;
		
    return optInTimePeriod - 1;
}
function tsf(inReal, aheadPeriod)
{
    var optInTimePeriod = Math.floor(inReal.length/2);
    var startIdx = 0;
    var endIdx = inReal.length - 1;
    var outReal = [];
    if (startIdx < 0)
        return null;
    if ((endIdx < 0) || (endIdx < startIdx))
        return null;
    if ((optInTimePeriod < 10) || (optInTimePeriod > 100000))
        return null;
    else if (optInTimePeriod > 20)
		optInTimePeriod = 19;
    lookbackTotal = tsfLookback(optInTimePeriod);
    if (startIdx < lookbackTotal)
        startIdx = lookbackTotal;
    if (startIdx > endIdx)
        return null;
    var today = startIdx;
    var SumX = optInTimePeriod * (optInTimePeriod - 1) * 0.5;
    var SumXSqr = optInTimePeriod * (optInTimePeriod - 1) * (2 * optInTimePeriod - 1) / 6;
    var Divisor = SumX * SumX - optInTimePeriod * SumXSqr;
    
    if (inReal.length > 0)
    {
        var currentActual = inReal[inReal.length - 1][1];
        outReal.push([inReal[inReal.length - 1][0] + 1000, inReal[inReal.length - 1][1]]);
        while (today <= endIdx + aheadPeriod)
        {
            var SumXY = 0;
            var SumY = 0;
            for (var i = optInTimePeriod; i-- != 0;)
            {
    			var tempValue1;
                if (today - i >= inReal.length)
    			{
    				tempValue1 = ema0(inReal, optInTimePeriod); // or use last value in inReal
                    SumY += tempValue1;
    			}
                else
    			{
    				tempValue1 = inReal[today - i][1];
                    SumY += tempValue1;
    			}
                SumXY += i * tempValue1;
            }
            var m = (optInTimePeriod * SumXY - SumX * SumY) / Divisor;
            var b = (SumY - m * SumX) / optInTimePeriod;
            
            if (today > endIdx)
    		{
                var ttt = (new Date(inReal[inReal.length - 1][0] + 86400000));
                ttt.setHours(ttt.getHours() + 12); // otherwise, 1 day behind in US
                var dayofweek = ttt.getDay();
                
                var ms = 86400000;
                if (dayofweek == 6)
                    ms = 3 * 86400000;
                else if (dayofweek == 0)
                    ms = 2 * 86400000;
                
                var tttt = (new Date(inReal[inReal.length - 1][0] + ms));
                tttt.setHours(ttt.getHours() + 12); // otherwise, 1 day behind in US    
                if (tttt.getMonth() == 0 && tttt.getDate() == 1) // Jan 1
                    ms += 86400000;
                    
                var estimate = b + m * optInTimePeriod;
    			outReal.push([inReal[inReal.length - 1][0] + ms, Math.round(estimate*10000)/10000]);
                inReal.push([inReal[inReal.length - 1][0] + ms, estimate]); // push new estimate back to inarray
    		}
            today++;
        }
        
        if (outReal.length > 1)
        {
            var diff = (currentActual - outReal[1][1]) / 2;
            for (var i = 1; i < outReal.length; i++)
            {
                outReal[i][1] = outReal[i][1] + diff;
            }
        }
    }
    
    return outReal;
}
