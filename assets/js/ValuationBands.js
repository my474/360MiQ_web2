function valuationBands(stock, valTable, pricedata, type, container, patternup, patterndown, subtitle)
{
    var rows = pricedata.split("\n");
    
    k = 0;
    var close = [];
    var eps = [];
    var epsEstimate = [];
    var total = 0, sum = 0;
    var newest_epsdate = -1;
    var newest_epsindex = -1;
    for (j = valTable.length - 1; j >=0 ; j--)
    {
        if (newest_epsdate == -1) // find the newest eps date
        {
            var tmpfield = valTable[j].split(";");
            if ((tmpfield.length == 3 && tmpfield[2] !== "") || // PE
                tmpfield.length == 2) // PB
            {
                newest_epsdate = str2date(valTable[j].substring(0, valTable[j].indexOf(";")));
                newest_epsindex = j;
                break;
            }
        }
    }
    
    for (j = 1; j < valTable.length; j++)
    {
        if (valTable[j] === "" || valTable[j-1] === "")
            continue;
            
        epsdate = str2date(valTable[j].substring(0, valTable[j].indexOf(";")));
        lastepsdate = str2date(valTable[j-1].substring(0, valTable[j-1].indexOf(";")));

        var tradedateLatest;
        for (i = k; i < rows.length; i++)
        {
            var fields = rows[i].split(",");
            var fieldslast = i<rows.length-1 && i > 0 ? rows[i-1].split(",") : "";
            if (fields.length != 6)
                continue;
                
            var tradedate = str2date(fields[0]);
            tradedateLatest = tradedate;
            if (close === undefined || close.length === 0 || (close.length > 0 && close[close.length - 1][0] < utc(str2date(formatDate(tradedate)))))
                close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[4])]);

            if (tradedate == lastepsdate ||
                (tradedate >= lastepsdate && tradedate <= epsdate) ||
                (fieldslast !== "" && tradedate > lastepsdate && str2date(fieldslast[0]) < lastepsdate))
            {
                var currentEPS = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
                var lastEPS = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

                if ((isNaN(currentEPS) || isNaN(lastEPS)) && tradedate <= newest_epsdate)
                    eps.push([str2date(formatDate(tradedate)), null]);
                else
                {
                    var epsdatediff = (epsdate - lastepsdate);
                    var tradedateepsdatediff = (tradedate - lastepsdate);
                    var ratio = tradedateepsdatediff / epsdatediff;
                    if (tradedate <= newest_epsdate)
                        eps.push([str2date(formatDate(tradedate)), (ratio * (currentEPS - lastEPS) + lastEPS)]);
                    else
                        epsEstimate.push([str2date(formatDate(tradedate)), (ratio * (currentEPS - lastEPS) + lastEPS)]);
                        
                    if (close.length > i)
                    {
                        sum += close[i][1] / (ratio * (currentEPS - lastEPS) + lastEPS);
                        total++;
                    }
                }
            }
            else if (tradedate < lastepsdate && tradedate <= newest_epsdate)
                eps.push([str2date(formatDate(tradedate)), null]);
            else if (tradedate > epsdate)
                break;
        }
        
        if (j >= newest_epsindex && tradedateLatest < epsdate)
        {
            var epsdatediffEst = (epsdate - lastepsdate);
            var currentEPSEst = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
            var lastEPSEst = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

            for (m = 1; tradedateLatest.setDate(tradedateLatest.getDate() + m) <= epsdate;)
            {
                if(tradedateLatest.getDay() != 6 && tradedateLatest.getDay() !== 0)
                {
                    var tradedateepsdatediffEst = (tradedateLatest - lastepsdate);
                    var ratioEst = tradedateepsdatediffEst / epsdatediffEst;
                    epsEstimate.push([str2date(formatDate(tradedateLatest)), (ratioEst * (currentEPSEst - lastEPSEst) + lastEPSEst)]);
                }
            }
        }        
        /*if (j >= newest_epsindex && newest_epsdate < epsdate)
        {
            var tmp_newest_epsdate = newest_epsdate;
            var epsdatediffEst = (epsdate - lastepsdate);
            var currentEPSEst = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
            var lastEPSEst = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

            for (m = 1; tmp_newest_epsdate.setDate(tmp_newest_epsdate.getDate() + m) <= epsdate;)
            {
                if(tmp_newest_epsdate.getDay() != 6 && tmp_newest_epsdate.getDay() !== 0)
                {
                    var tradedateepsdatediffEst = (tmp_newest_epsdate - lastepsdate);
                    var ratioEst = tradedateepsdatediffEst / epsdatediffEst;
                    epsEstimate.push([str2date(formatDate(tmp_newest_epsdate)), (ratioEst * (currentEPSEst - lastEPSEst) + lastEPSEst)]);
                }
            }
        }*/

        k = i;
    }
    
    
    var avg = sum/total;
    var stdsum = 0.0;
    var eps_both = eps.concat(epsEstimate);
    //var CurrentEPS = eps_both.length > close.length ? eps_both[close.length - 1][1] : 1/0; // nan
    var CurrentEPS = 1/0;
    if (eps_both.length > 0 && close.length > 0 && utc(eps_both[eps_both.length - 1][0]) >= close[close.length - 1][0] && eps.length > 0)
    {
        for(i = eps_both.length - 1; i > 0; i-- )
        {
            if (utc(eps_both[i][0]) == close[close.length - 1][0])
            {
                CurrentEPS =  eps_both[i][1];
                break;
            }
            else if (utc(eps_both[i][0]) > close[close.length - 1][0] && utc(eps_both[i - 1][0]) < close[close.length - 1][0])
            {
                CurrentEPS =  eps_both[i][1];
                break;
            }
            else if (utc(eps_both[i - 1][0]) == close[close.length - 1][0])
            {
                CurrentEPS =  eps_both[i - 1][1];
                break;
            }
        }
    }
    for(i = 0; i < eps_both.length && i < close.length; i++)
    {
        if (eps_both[i][1] !== null)
            stdsum += (close[i][1]/eps_both[i][1] - avg)*(close[i][1]/eps_both[i][1] - avg);
    }
    
    var stdev = Math.sqrt(stdsum / total);
    
    var rangesA = [];
    var rangesB = [];
    var rangesC = [];
    var rangesD = [];
    var rangesE = [];
    var rangesF = [];
    var rangesA1 = [];
    var rangesB1 = [];
    var rangesC1 = [];
    var rangesD1 = [];
    var rangesE1 = [];
    var rangesF1 = [];
    if (!isNaN(avg) && isFinite(avg) && !isNaN(stdev) && isFinite(stdev))
    {
        for (i = 0; i < eps.length; i++)
        {
            if (eps[i][1] !== null && eps[i][1] > 0)
            {
                rangesA.push([utc(eps[i][0]), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2))]);
                rangesB.push([utc(eps[i][0]), parseFloat(((avg) * eps[i][1]).toFixed(2)), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2))]);
                rangesC.push([utc(eps[i][0]), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg) * eps[i][1]).toFixed(2))]);
                rangesD.push([utc(eps[i][0]), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2))]);
                rangesE.push([utc(eps[i][0]), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 3 * stdev) * eps[i][1]).toFixed(2))]);
                rangesF.push([utc(eps[i][0]), parseFloat(((avg - 3 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2))]);
            }
        }
        if(rangesA.length > 0)
        {
            for (i = 0; i < epsEstimate.length; i++)
            {
                if (epsEstimate[i][1] !== null && epsEstimate[i][1] > 0)
                {
                    rangesA1.push([utc(epsEstimate[i][0]), parseFloat(((avg + stdev) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg + 2 * stdev) * epsEstimate[i][1]).toFixed(2))]);
                    rangesB1.push([utc(epsEstimate[i][0]), parseFloat(((avg) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg + stdev) * epsEstimate[i][1]).toFixed(2))]);
                    rangesC1.push([utc(epsEstimate[i][0]), parseFloat(((avg - stdev) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg) * epsEstimate[i][1]).toFixed(2))]);
                    rangesD1.push([utc(epsEstimate[i][0]), parseFloat(((avg - 2 * stdev) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg - stdev) * epsEstimate[i][1]).toFixed(2))]);
                    rangesE1.push([utc(epsEstimate[i][0]), parseFloat(((avg + 2 * stdev) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg + 3 * stdev) * epsEstimate[i][1]).toFixed(2))]);
                    rangesF1.push([utc(epsEstimate[i][0]), parseFloat(((avg - 3 * stdev) * epsEstimate[i][1]).toFixed(2)), parseFloat(((avg - 2 * stdev) * epsEstimate[i][1]).toFixed(2))]);
                }
            }
        }
    }
    
    var rangesA2 = [rangesA[rangesA.length - 1], rangesA1[0]];
    var rangesB2 = [rangesB[rangesB.length - 1], rangesB1[0]];
    var rangesC2 = [rangesC[rangesC.length - 1], rangesC1[0]];
    var rangesD2 = [rangesD[rangesD.length - 1], rangesD1[0]];
    var rangesE2 = [rangesE[rangesE.length - 1], rangesE1[0]];
    var rangesF2 = [rangesF[rangesF.length - 1], rangesF1[0]];
    
    var length = close.length;
    for (i = 0; i < length - 1 && rangesA.length > 0; i++)
    {
        if (close[0][0] < rangesA[0][0])
            close.splice(0, 1);
        else
            break;
    }

    var allRangesE = rangesE.concat(rangesE1);
    var allRangesF = rangesF.concat(rangesF1);
    var showE = false;
    var showF = false;
    for (var i = close.length - 1; i >= close.length - 60 && close.length >= 60; i--)
    {
        if (close.length > 0 && allRangesE.length >= close.length && close[i][1] > (allRangesE[i][2] + allRangesE[i][1]) / 2)
        {
            showE = true;
            break;
        }
        else if (close.length > 0 && allRangesF.length >= close.length && close[i][1] < (allRangesF[i][2] + allRangesF[i][1]) / 2)
        {
            showF = true;
            break;
        }
    }
    
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

    var chart = Highcharts.chart(container, {
        chart: {
            style: {
                fontFamily: 'Montserrat'
            },
            events: {
                load: function () {
                    /*this.series[0].points[this.series[0].points.length - 1].update({
                        dataLabels: {
                            enabled: true,
                            align: 'left',
                            x: -5,
                            color: 'darkslategrey',
                        }
                    });*/
                    var series = this.series[0],
                    yData = series.yData;
                    
                    if (yData.length > 0)
                    {
                        //min = Math.min(...yData),
                        //max = Math.max(...yData),
                        var min = Math.min.apply(null, yData),
                        max = Math.max.apply(null, yData),
                        x1 = series.points[yData.indexOf(min)].x,
                        x2 = series.points[yData.indexOf(max)].x,
                        lastx = series.points[series.points.length - 1].x,
                        lasty = series.points[series.points.length - 1].y;
                        
                        if (lastx == x1 || lastx == x2)
                        {
                    	    lastx = null;
                        }
                        
                        if (x1 == x2)
                        {
                    	    x2 = null;
                        }
                        
                        this.addSeries({
                            type: 'flags',
                            name: 'Price Tag',
                            zIndex: 4,
                            color: 'orange',
                            //showInLegend: false,
                            data: [{
                                x: x1,
                                y: min,
                                title: min,
                            }, {
                                x: x2,
                                y: max,
                                title: max,
                            }, {
                                x: lastx,
                                y: lasty,
                                title: lasty,
                            }]
                        });
                    }
                    

                    // dim/highlight series with linked series together on mouseover/mouseout on legend item                    
                    var chart = this;
                    // Add event:
                    chart.series.forEach(function(series) {
                        if (series.legendGroup) {
                            Highcharts.addEvent(series.legendGroup.element, 'mouseover', function() {
                                // Internal method:
                                chart.pointer.applyInactiveState([series.points[0]]);
                            });
                            Highcharts.addEvent(series.legendGroup.element, 'mouseout', function() {
                                chart.series.forEach(function(s) {
                                    s.setState();
                                });
                            });
                        }
                    });
                }
            }
        },
        title: {
            text: stock + ' ' + type
        },
        subtitle : {
            text: subtitle
        },
        xAxis: {
            plotLines: [{
              color: 'lightgrey',
              dashStyle: 'ShortDash',
              width: 3,
              value: newest_epsdate,//Date.UTC(2019, 8, 27),
              zIndex: 3,
              label: {
                text: 'Estimate',
                x: 10,
                y: -5,
                verticalAlign: 'bottom',
                rotation: 0,
                style: {
                  color: 'darkgrey',
                  fontWeight: 'bold',
                  fontSize: '13px',
                }
              }
            },
            {
              color: 'lightgrey',
              dashStyle: 'ShortDash',
              width: 3,
              value: newest_epsdate, //Date.UTC(2019, 8, 27),
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
            selected: 6,
            buttons: [{
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
                count: 4,
                text: '4y'
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
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + changeStr + '</b>';
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
              backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#2a2a3e':'#eee';})(),
              borderColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#555':'#777';})(),
              symbols: [
                'customcircle',
                'customcircle'
              ]
            },
            series: {
                type: 'areaspline',
                lineColor: 'rgba(255, 255, 0, 1)',
                lineWidth: 2,
                shadow: false,
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
            zIndex: 4,
            color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
            lineWidth: 4,
            marker: {
                enabled: false,
                fillColor: 'white',
                lineWidth: 2,
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})()
            },
            navigatorOptions: { // Specific values that affect the series in the navigator only
                type: 'line',
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
                lineWidth: 1,
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
            }
        }, /*{
            // this is for show in navigator, as showing last price label disable price series in PB Bands navigator (stock PE Bands is ok), highcharts' bug???
            name: 'Hidden by default',
            data: close,
            showInNavigator: true,
            showInLegend: false,
            visible: false,
            navigatorOptions: {
                visible: true,
                type: 'line',
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
                zIndex: 4,
                lineWidth: 1,
            },
            dataLabels: {
                formatter: function() {
                    if(this.series.xAxis.userOptions.id == "navigator-x-axis") {
                         return false;   
                    }
                    return this.y;    
                }
            }
        },*/ {
            visible: showE,
            name: '2 to 3 STDEV',
            id: 'rangesE',
            data: rangesE,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(17, 129, 200, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '2 to 3 STDEV',
            data: rangesE1,
            type: 'arearange',
            lineWidth: 0,
            color: 'rgba(17, 129, 200, 0.3)',
            linkedTo: ':previous',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#1181C8'
              }
            },
            states: { hover: {enabled: false }},
          }, {
            data: rangesE2,
            enableMouseTracking: false,

            name: '2 to 3 STDEV',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesE', 
            color: 'rgba(17, 129, 200, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#1181C8'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }, {
            name: '1 to 2 STDEV',
            id: 'rangesA',
            data: rangesA,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(10, 182, 243, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '1 to 2 STDEV',
            data: rangesA1,
            type: 'arearange',
            lineWidth: 0,
            linkedTo: ':previous',
            color: 'rgba(10, 182, 243, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#0ab6f3'
              }
            },
            states: { hover: {enabled: false }},
          }, {
            data: rangesA2,
            enableMouseTracking: false,

            name: '1 to 2 STDEV',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesA', 
            color: 'rgba(10, 182, 243, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#0ab6f3'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }, {
            name: 'Median to 1 STDEV',
            id: 'rangesB',
            data: rangesB,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(83, 247, 255, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: 'Median to 1 STDEV',
            data: rangesB1,
            type: 'arearange',
            lineWidth: 0,
            color: 'rgba(83, 247, 255, 0.3)',
            linkedTo: ':previous',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#53f7ff'
              }
            },
            states: { hover: {enabled: false }},
          }, {
            data: rangesB2,
            enableMouseTracking: false,

            name: 'Median to 1 STDEV',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesB',
            color: 'rgba(83, 247, 255, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patternup,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#53f7ff'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }, {
            name: 'Median',
            id: 'median',
            data: rangesB,
            lineWidth: 4,
            //linkedTo: ':previous',
            color: '#ffff00',
            shadow: true,
            zIndex: 3,
            marker: {
                enabled: false,
                symbol: 'square',
                fillColor: 'white',
                lineWidth: 2,
                lineColor: '#ffff00'
            },
            //states: { hover: {enabled: false }},
            showInNavigator: true,
        }, {
            name: 'Median',
            data: rangesB1,
            //dashStyle: 'dash',
            lineWidth: 4,
            linkedTo: ':previous',
            color: '#ffff00',
            shadow: true,
            zIndex: 2,
            marker: {
                enabled: false,
                symbol: 'square',
                fillColor: 'white',
                lineWidth: 2,
                lineColor: '#ffff00'
            },
            showInNavigator: true,
          }, {
            data: rangesB2,
            enableMouseTracking: false,

            name: 'Median',
            //dashStyle: 'dash',
            lineWidth: 4,
            linkedTo: 'median', 
            color: '#ffff00',
            shadow: true,
            zIndex: 1,
            marker: {
                enabled: false,
            },
            states: {
                inactive: {
                    opacity: 1
                },
                //hover: {enabled: false },
            },
            showInNavigator: true,
        }, {
            name: '-1 STDEV to Median',
            id: 'rangesC',
            data: rangesC,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(239, 80, 207, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-1 STDEV to Median',
            data: rangesC1,
            type: 'arearange',
            lineWidth: 0,
            color: 'rgba(239, 80, 207, 0.5)',
            linkedTo: ':previous',
            //color: 'red',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#ef50cf'
              }
            },
            states: { hover: {enabled: false }},
        }, {
            data: rangesC2,
            enableMouseTracking: false,

            name: '-1 STDEV to Median',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesC', 
            color: 'rgba(239, 80, 207, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#ef50cf'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }, {
            name: '-2 to -1 STDEV',
            id: 'rangesD',
            data: rangesD,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(142, 76, 233, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-2 to -1 STDEV',
            data: rangesD1,
            type: 'arearange',
            lineWidth: 0,
            color: 'rgba(142, 76, 233, 0.3)',
            linkedTo: ':previous',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#8e4ce9'
              }
            },
            states: { hover: {enabled: false }},
          }, {
            data: rangesD2,
            enableMouseTracking: false,

            name: '-2 to -1 STDEV',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesD', 
            color: 'rgba(142, 76, 233, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#8e4ce9'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }, {
            visible: showF,
            name: '-3 to -2 STDEV',
            id: 'rangesF',
            data: rangesF,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(91, 28, 179, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-3 to -2 STDEV',
            data: rangesF1,
            type: 'arearange',
            lineWidth: 0,
            color: 'rgba(91, 28, 179, 0.3)',
            linkedTo: ':previous',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#5B1CB3'
              }
            },
            states: { hover: {enabled: false }},
          }, {
            data: rangesF2,
            enableMouseTracking: false,

            name: '-3 to -2 STDEV',
            type: 'arearange',
            lineWidth: 0,
            linkedTo: 'rangesF', 
            color: '#rgba(91, 28, 179, 0.3)',
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            },
            fillColor: {
              pattern: {
                path: {
                  d: patterndown,
                  strokeWidth: 5,
                },
                width: 10,
                height: 10,
                opacity: 0.5,
                color: '#5B1CB3'
              }
            },
            states: {
                inactive: {
                    opacity: 1
                },
                hover: {enabled: false }
            }
        }
        ]
    });
    
    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    return valuationComment(stock, type, avg, stdev, close, CurrentEPS, rangesB.concat(rangesB1), epsEstimate.length > 0);
}

function earningSurpriseChart(stockcode, valuationdata, pricedata, type, chartcontainer, subtitle)
{
    var dates = [];
    var surprise = [];
    var close = [];
    for (i = 0; i < valuationdata.length; i++)
    {
        var fields = valuationdata[i].split(";");
        if (fields.length == 3 && fields[2] !== "")
        {
            dates.push(fields[0].substr(0,4) + "-" + fields[0].substr(4,2) + "-" + fields[0].substr(6,2));
            surprise.push(parseFloat(fields[2]));
        }
    }
    
    var rows = pricedata.split("\n");
    for (j = 0; j < dates.length; j++)
    {
        for (i = rows.length - 1; i >= 0; i--)
        {
            var fields = rows[i].split(",");
            if (fields.length != 6)
                continue;
                
            var tradedate = str2date(fields[0]);
            
            if (tradedate <= str2date(dates[j]))
            {
                close.push(parseFloat(fields[4]));
                break;
            }
            else if (i == 0) // has surprise date but no price on that date, eg. newly IPO 9999.HK
                close.push(null);
        }
    }
    //close.reverse();
    var ymin = Math.min.apply(null, close);// * 0.9,
    var ymax = Math.max.apply(null, close);// * 1.1;
    
    var ymin0 = Math.min.apply(null, surprise);// * 0.9,
    var ymax0 = Math.max.apply(null, surprise);// * 1.1;
    
    var chart = Highcharts.chart(chartcontainer, {
        chart: {
            type: 'column',
            style: {
                fontFamily: 'Montserrat'
            }
        },
        plotOptions: {
            column: {
                borderRadius: 3
            }
        },        
        title: {
            text: stockcode + " " + type
        },
        subtitle : {
            text: subtitle
        },
        xAxis: {
            categories: dates,
            labels: {
                rotation: 300,
                align: 'right',
                y: 15
            },
        },
        yAxis: [{
			min: ymin0,
		    max: ymax0,
            title: {
                text: 'Surprise %',
                margin: 3
            },
            //tickInterval: 1,
            //gridLineDashStyle: 'dot',
		    //endOnTick: false,
            //maxPadding: 0,
        }, { // Secondary yAxis
			min: ymin,
		    max: ymax,
			title: {
				text: 'Stock Price',
				style: {
					color: '#FFb000',
				},
                //margin: 0
			},
			gridLineWidth: 0,
			labels: {
				format: '{value}',
				style: {
					color: '#FFb000'
				},
                x: 5,
			},
			opposite: true
		}],
        legend: {
            enabled: false
        },    
		tooltip: {
			split: false,
			shared: true,
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
                x: 63,
                y: -410
            }
        },
        series: [ {
            name: 'Surprise %',
            data: surprise,
            negativeColor: {
              linearGradient: {
                x1: 0,
                x2: 0,
                y1: 1,
                y2: 0
              },
              stops: [
                [0, '#ff9600'],
                [1, '#FF6900']
              ]
            },
            color: {
              linearGradient: {
                x1: 0,
                x2: 0,
                y1: 0,
                y2: 1
              },
              stops: [
                [0, '#00eeFF'],
                [1, '#00bbff']
              ]
            }
        },{
            type: 'spline',
            name: 'Stock Price',
            color: 'gold',
            lineWidth: 4,
            shadow: true, 
            yAxis: 1,
            data: close
        }]
    });
    
    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    return earningSurpriseComment(dates, surprise);
}
function earningSurpriseComment(dates, surprise)
{
    var comment = "";
    var tmp = 0;
    if (dates.length > 0 && surprise.length > 0)
    {
        var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
        var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLup1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLdown1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLsideway1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';        
        
        if (surprise[surprise.length - 1] > 0)
        {
            tmp++;
            comment = "Earnings beat estimates by <b>"+surprise[surprise.length - 1]+"%</b>";
        }
        else if (surprise[surprise.length - 1] < 0)
        {
            tmp += 6;
            comment = "Earnings missed estimates by <b>"+surprise[surprise.length - 1]+"%</b>";
        }
        else
        {
            comment = "Earnings met estimates";
        }
        
        if (dates.length > 1 && surprise.length > 1)
        {
            var diff = Math.abs((str2date(dates[dates.length - 1]) - str2date(dates[dates.length - 2]))/1000/86400);
            var report = "interim report";
            if (diff > 75 && diff < 105)
                report = "quarterly report";
            else if (diff > 340 && diff < 390)
                report = "annual report";

            if (surprise[surprise.length - 1] > surprise[surprise.length - 2])
            {
                comment += ", which is better than the previous " + report;
            }
            else if (surprise[surprise.length - 1] < surprise[surprise.length - 2])
            {
                comment += ", which is worse than the previous " + report;
            }
            else
            {
                comment += ", which is the same as the previous " + report;
            }
        }
        
        if (comment != "")
        {
            if (tmp == 1)
                TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
            else if (tmp == 6)
                TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
            else
                TAcommentTXT.push(TAcommentHTMLsideway1st.format(comment)); 
        }  
        
        
        return TAcommentTXT.join("") + "</table>";
    }
    else
        return "";
}

function Pxband(stock, valTable, pricedata, type, container, patternup, patterndown, title, subtitle)
{
    var rows = pricedata.split("\n");
    
    k = 0;
    var close = [];
    var eps = [];
    var epsEstimate = [];
    var total = 0, sum = 0;
    for (j = 1; j < valTable.length; j++)
    {
        if (valTable[j] === "" || valTable[j-1] === "")
            continue;
            
        epsdate = str2date(valTable[j].substring(0, valTable[j].indexOf(";")));
        lastepsdate = str2date(valTable[j-1].substring(0, valTable[j-1].indexOf(";")));
        
        if (j == valTable.length - 1)
        {
            var lastrow;
            if (rows.length > 0)
                lastrow = rows[rows.length - 1];
            if (lastrow === "" && rows.length > 1)
                lastrow = rows[rows.length - 2];
            var fieldslatest = lastrow.split(",");
            if (fieldslatest.length == 6 || fieldslatest.length == 2)
            {
                if (epsdate > str2date(fieldslatest[0]))
                    epsdate = str2date(fieldslatest[0]);
            }
        }
        
        var tradedateLatest;
        for (i = k; i < rows.length; i++)
        {
            var fields = rows[i].split(",");
            var fieldslast = i<rows.length-1 && i > 0 ? rows[i-1].split(",") : "";
            if (fields.length != 6 && fields.length != 2)
                continue;
                
            var tradedate = str2date(fields[0]);
            tradedateLatest = tradedate;
            if (close === undefined || close.length === 0 || (close.length > 0 && close[close.length - 1][0] < utc(str2date(formatDate(tradedate)))))
            {
                if (fields.length == 6)
                    close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[4])]);
                else if (fields.length == 2)
                    close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[1])]);
            }
            
            if (tradedate == lastepsdate ||
                (tradedate >= lastepsdate && tradedate <= epsdate) ||
                (fieldslast !== "" && tradedate > lastepsdate && str2date(fieldslast[0]) < lastepsdate))
            {
                var currentEPS = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
                var lastEPS = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

                if (isNaN(currentEPS) || isNaN(lastEPS))
                    eps.push([str2date(formatDate(tradedate)), null]);
                else
                {
                    var epsdatediff = (epsdate - lastepsdate);
                    var tradedateepsdatediff = (tradedate - lastepsdate);
                    var ratio = tradedateepsdatediff / epsdatediff;
                    eps.push([str2date(formatDate(tradedate)), (ratio * (currentEPS - lastEPS) + lastEPS)]);
                    sum += close[i][1] / (ratio * (currentEPS - lastEPS) + lastEPS);
                    total++;
                }
            }
            else if (tradedate < lastepsdate)
                eps.push([str2date(formatDate(tradedate)), null]);
            else if (tradedate > epsdate)
                break;
        }
        
        if (j == valTable.length - 1 && tradedateLatest < epsdate)
        {
            var epsdatediffEst = (epsdate - lastepsdate);
            var currentEPSEst = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
            var lastEPSEst = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

            for (m = 1; tradedateLatest.setDate(tradedateLatest.getDate() + m) <= epsdate;)
            {
                if(tradedateLatest.getDay() != 6 && tradedateLatest.getDay() !== 0)
                {
                    var tradedateepsdatediffEst = (tradedateLatest - lastepsdate);
                    var ratioEst = tradedateepsdatediffEst / epsdatediffEst;
                    epsEstimate.push([str2date(formatDate(tradedateLatest)), (ratioEst * (currentEPSEst - lastEPSEst) + lastEPSEst)]);
                }
            }
        }

        k = i;
    }
    
    var avg = sum/total;
    var stdsum = 0.0;
    for(i = 0; i < eps.length; i++)
    {
        if (eps[i][1] !== null)
            stdsum += (close[i][1]/eps[i][1] - avg)*(close[i][1]/eps[i][1] - avg);
    }
    
    var stdev = Math.sqrt(stdsum / total);
    
    var rangesA = [];
    var rangesB = [];
    var rangesC = [];
    var rangesD = [];
    var rangesE = [];
    var rangesF = [];
    if (!isNaN(avg) && isFinite(avg) && !isNaN(stdev) && isFinite(stdev))
    {
        for (i = 0; i < eps.length; i++)
        {
            if (eps[i][1] !== null && eps[i][1] > 0)
            {
                rangesA.push([utc(eps[i][0]), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2))]);
                rangesB.push([utc(eps[i][0]), parseFloat(((avg) * eps[i][1]).toFixed(2)), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2))]);
                rangesC.push([utc(eps[i][0]), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg) * eps[i][1]).toFixed(2))]);
                rangesD.push([utc(eps[i][0]), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2))]);
                rangesE.push([utc(eps[i][0]), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 3 * stdev) * eps[i][1]).toFixed(2))]);
                rangesF.push([utc(eps[i][0]), parseFloat(((avg - 3 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2))]);
            }
        }
    }

    var length = close.length;
    for (i = 0; i < length - 1 && rangesA.length > 0; i++)
    {
        if (close[0][0] < rangesA[0][0])
            close.splice(0, 1);
        else
            break;
    }

    var showE = false;
    var showF = false;
    var rangeIdx = 0;
    for (var i = close.length - 1; i >= close.length - 60 && close.length >= 60; i--)
    {
        if (rangesE.length - 1 - rangeIdx > 0 && close.length > 0 && rangesE.length > 0 && close[i][1] > (rangesE[rangesE.length - 1 - rangeIdx][2] + rangesE[rangesE.length - 1 - rangeIdx][1]) / 2)
        {
            showE = true;
            break;
        }
        else if (rangesF.length - 1 - rangeIdx > 0 && close.length > 0 && rangesF.length > 0 && close[i][1] < (rangesF[rangesF.length - 1 - rangeIdx][2] + rangesF[rangesF.length - 1 - rangeIdx][1]) / 2)
        {
            showF = true;
            break;
        }
        rangeIdx++;
    }
    
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

    var chart = Highcharts.chart(container, {
        chart: {
            style: {
                fontFamily: 'Montserrat'
            },
            events: {
                load: function () {
                    /*this.series[0].points[this.series[0].points.length - 1].update({
                        dataLabels: {
                            enabled: true,
                            color: 'darkslategrey',
                        }
                    });*/
                    var series = this.series[0],
                    yData = series.yData;
                    
                    if (yData.length > 0)
                    {
                        //min = Math.min(...yData),
                        //max = Math.max(...yData),
                        var min = Math.min.apply(null, yData),
                        max = Math.max.apply(null, yData),
                        x1 = series.points[yData.indexOf(min)].x,
                        x2 = series.points[yData.indexOf(max)].x,
                        lastx = series.points[series.points.length - 1].x,
                        lasty = series.points[series.points.length - 1].y;
                        
                        if (lastx == x1 || lastx == x2)
                        {
                    	    lastx = null;
                        }
                        
                        if (x1 == x2)
                        {
                    	    x2 = null;
                        }
                        
                        this.addSeries({
                            type: 'flags',
                            name: 'Price Tag',
                            zIndex: 4,
                            color: 'orange',
                            //showInLegend: false,
                            data: [{
                                x: x1,
                                y: min,
                                title: min,
                            }, {
                                x: x2,
                                y: max,
                                title: max,
                            }, {
                                x: lastx,
                                y: lasty,
                                title: lasty,
                            }]
                        });
                    }
                }
            }
        },
        title: {
            text: (subtitle == "" ? stock : subtitle) + ' ' + type
        },
        //subtitle: {
        //    text: subtitle,
        //},
        xAxis: {
            type: 'datetime',
            crosshair: {
                color: '#9d81ba88'
            },
        },

        yAxis: {
            endOnTick: false,
            startOnTick: false,
            title: {
                text: title
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
            selected: 6,
            buttons: [{
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
                count: 4,
                text: '4y'
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
                        
                    return'<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span><br>' + txt + ': <b>' + y + '</b>';
                }
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
                            return s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + changeStr + '</b>';
                        }
                        else if(Array.isArray(point.series.yData[0]))
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
              backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#2a2a3e':'#eee';})(),
              borderColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#555':'#777';})(),
              symbols: [
                'customcircle',
                'customcircle'
              ]
            },
            series: {
                type: 'areaspline',
                lineColor: 'rgba(255, 255, 0, 1)',
                lineWidth: 2,
                shadow: false,
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
                    align: 'center',
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
            zIndex: 4,
            color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
            lineWidth: 4,
            marker: {
                enabled: false,
                fillColor: 'white',
                lineWidth: 2,
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})()
            },
            navigatorOptions: { // Specific values that affect the series in the navigator only
                type: 'line',
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
                lineWidth: 1,
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
            }
        }, /*{
            // this is for show in navigator, as showing last price label disable price series in navigator, highcharts' bug???
            name: 'Hidden by default',
            data: close,
            showInNavigator: true,
            showInLegend: false,
            visible: false,
            navigatorOptions: {
                visible: true,
                type: 'line',
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
                zIndex: 4,
                lineWidth: 1,
            },
            dataLabels: {
                formatter: function() {
                    if(this.series.xAxis.userOptions.id == "navigator-x-axis") {
                         return false;   
                    }
                    return this.y;    
                }
            }
        },*/ {
            visible: showE,
            name: '2 to 3 STDEV',
            id: 'rangesE',
            data: rangesE,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(17, 129, 200, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '1 to 2 STDEV',
            id: 'rangesA',
            data: rangesA,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(10, 182, 243, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: 'Median to 1 STDEV',
            id: 'rangesB',
            data: rangesB,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(83, 247, 255, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: 'Median',
            id: 'median',
            data: rangesB,
            lineWidth: 4,
            //linkedTo: ':previous',
            color: '#ffff00',
            shadow: true,
            zIndex: 3,
            marker: {
                enabled: false,
                symbol: 'square',
                fillColor: 'white',
                lineWidth: 2,
                lineColor: '#ffff00'
            },
            //states: { hover: {enabled: false }},
            showInNavigator: true,
        }, {
            name: '-1 STDEV to Median',
            id: 'rangesC',
            data: rangesC,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(239, 80, 207, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-2 to -1 STDEV',
            id: 'rangesD',
            data: rangesD,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(142, 76, 233, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            visible: showF,
            name: '-3 to -2 STDEV',
            id: 'rangesF',
            data: rangesF,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: 'rgba(91, 28, 179, 0.5)',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }
        ]
    });
    
    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    return valuationComment(stock, type, avg, stdev, close, eps[eps.length - 1][1], rangesB, false);
}
function valuationComment(stock, type, avg, stdev, close, currentEPS, rangesB, EstOrActual)
{
    var comment = "";
    if (close.length > 0)
    {
        var score = NaN;
        var currentClose = close[close.length - 1][1];
        //var currentEPS = eps[eps.length - 1][1];
        var currentValue = Math.round((currentClose / currentEPS - avg) / stdev * 10) / 10;
        var isEPSup = "";
        var isBandup = 0;
        
        //var stdevTXT = '&nbsp;<span class="help-tip mytooltip" data-tooltip="For normal distribution, a 68% chance that the stock price would not go above 1 STDEV or below -1 STDEV; a 95% chance would not go above 2 STDEV or below -2 STDEV; a 99.7% chance would not go above 3 STDEV or below -3 STDEV."></span>';
        var stdevTXT = '&nbsp;<div class="help-tip2"><p>For normal distribution, stock price has<br>a 68% chance that would <u>not</u> go above <span style="background:rgba(83, 247, 255, 0.5);border-radius:3px">&nbsp;1&nbsp;STDEV&nbsp;</span> or below <span style="background:rgba(239, 80, 207, 0.5);border-radius:3px">&nbsp;-1&nbsp;STDEV&nbsp;</span>;<br>a 95% chance that would <u>not</u> go above <span style="background:rgba(10, 182, 243, 0.5);border-radius:3px">&nbsp;2&nbsp;STDEV&nbsp;</span> or below <span style="background:rgba(142, 76, 233, 0.5);border-radius:3px">&nbsp;-2&nbsp;STDEV&nbsp;</span>;<br>a 99.7% chance that would <u>not</u> go above <span style="background:rgba(17, 129, 200, 0.5);border-radius:3px">&nbsp;3&nbsp;STDEV&nbsp;</span> or below <span style="background:rgba(91, 28, 179, 0.5);border-radius:3px">&nbsp;-3&nbsp;STDEV&nbsp;</span>.</p></div>';
        var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
        var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLup1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLdown1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLsideway1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var tmp = 0;
        var median = '<span style="text-decoration: underline;text-decoration-color: yellow;text-decoration-thickness: 3px;">Median</span>';
        
        var epstxt = "Earnings";
        var ratiotxt = "PE Ratio";
        if (type == "PB Band")
        {
            epstxt = "Book Value";
            ratiotxt = "PB Ratio";
        }
        
        var period = 125;
        if (!isFinite(currentEPS) || isNaN(currentEPS) || rangesB.length < period || rangesB[rangesB.length - 1][0] < close[close.length - 1][0] - 3456000000) // 3456000000 = 40 days
        {
            score = NaN;
            tmp += 6;
            if (EstOrActual)
                comment = stock + " has no or not enough data of "+epstxt+" Estimate";
            else
                comment = stock + " has no or not enough data of "+epstxt;
        }
        else if (currentEPS == 0)
        {
            score = 0;
            tmp += 6;
            if (EstOrActual)
                comment = stock + " has a zero "+epstxt+" Estimate";
            else
                comment = stock + " has a zero "+epstxt;
        }
        else if (currentEPS < 0)
        {
            score = 0;
            tmp += 6;
            if (EstOrActual)
                comment = stock + " has a negative "+epstxt+" Estimate";
            else
                comment = stock + " has a negative "+epstxt;
        }
        else if (currentValue < 0.5 && currentValue > -0.5)
        {
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is "+ (currentValue === 0 ? "at " : "near ") + "fair value (" + median + ")";
            score = 4;
        }
        else if (currentValue <= -0.5 && currentValue > -1.2)
        {
            //tmp++;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is mildly below fair value (" + median + ")";
            score = 5;
        }
        else if (currentValue <= -1.2 && currentValue > -1.8)
        {
            tmp++;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is significantly below fair value (" + median + "); hence cheap in terms of " + ratiotxt;
            score = 6;
        }
        else if (currentValue <= -1.8 && currentValue > -2.8)
        {
            tmp++;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is severely below fair value (" + median + "); hence very cheap in terms of " + ratiotxt;
            score = 7;
        }
        else if (currentValue <= -2.8)
        {
            tmp++;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is extremely below fair value (" + median + "); hence extremely cheap in terms of " + ratiotxt;
            score = 8;
        }
        else if (currentValue >= 0.5 && currentValue < 1.2)
        {
            //tmp += 6;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is mildly above fair value (" + median + ")";
            score = 3;
        }
        else if (currentValue >= 1.2 && currentValue < 1.8)
        {
            tmp += 6;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is significantly above fair value (" + median + "); hence expensive in terms of " + ratiotxt;
            score = 2;
        }
        else if (currentValue >= 1.8 && currentValue < 2.8)
        {
            tmp += 6;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is severely above fair value (" + median + "); hence very expensive in terms of " + ratiotxt;
            score = 1;
        }
        else if (currentValue >= 2.8)
        {
            tmp += 6;
            comment = stock + " is at <b>" + currentValue + "</b> Standard Deviation (STDEV)" + stdevTXT + ", which is extremely above fair value (" + median + "); hence extremely expensive in terms of " + ratiotxt;
            score = 0;
        }
        
        if (comment != "")
        {
            if (tmp == 1)
                TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
            else if (tmp == 6)
                TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
            else
                TAcommentTXT.push(TAcommentHTMLsideway1st.format(comment)); 
        }    
        
        
        
        tmp = 0;
        if (!isNaN(score) && rangesB.length >= period && rangesB[rangesB.length - 1][0] >= close[close.length - 1][0] - 3456000000) // Median // 3456000000 = 40 days
        {
            if (rangesB[rangesB.length - 1][1] / rangesB[rangesB.length - period][1] - 1 > 0.1)
            {
                tmp++;
                isEPSup = type + " is strongly trending up";
                isBandup = 1;
                score += 2;
            }
            else if (rangesB[rangesB.length - 1][1] / rangesB[rangesB.length - period][1] - 1 > 0.02)
            {
                tmp++;
                isEPSup = type + " is mildly trending up";
                isBandup = 1;
                score++;
            }
            else if (rangesB[rangesB.length - 1][1] / rangesB[rangesB.length - period][1] - 1 < -0.1)
            {
                tmp += 6;
                isEPSup = type + " is strongly trending down";
                isBandup = -1;
                score -= 2;
            }
            else if (rangesB[rangesB.length - 1][1] / rangesB[rangesB.length - period][1] - 1 < -0.02)
            {
                tmp += 6;
                isEPSup = type + " is mildly trending down";
                isBandup = -1;
                score--;
            }
            else
            {
                isEPSup = type + " is moving sideways";
                isBandup = 0;
            }
        }
        
        /*if (eps.length >= period)
        {
            if (currentEPS / eps[eps.length - period][1] - 1 > 0.02)
            {
                if (isBandup == 1)
                {
                    tmp++;
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " & " + type + " are both trending up.";
                }
                else if (isBandup == -1)
                {
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is increasing, but " + type + " is trending down, means price is increasing faster than "+(type == "PE Band"? "EPS" : "Book Value")+".";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is increasing, but " + type + " is trending down.";
                }
                else
                {
                    tmp++;
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is increasing, but " + type + " is moving siderways, means price is increasing at the same rate as "+(type == "PE Band"? "EPS" : "Book Value")+".";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is increasing, but " + type + " is moving siderways.";
                }
            }
            else if (currentEPS / eps[eps.length - period][1] - 1 < -0.02)
            {
                if (isBandup == 1)
                {
                    //tmp += 6;
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is decreasing, but " + type + " is trending up, means price is dropping faster than "+(type == "PE Band"? "EPS" : "Book Value")+".";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is decreasing, but " + type + " is trending up.";
                }
                else if (isBandup == -1)
                {
                    tmp += 6;
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " & " + type + " are both trending down.";
                }
                else
                {
                    tmp += 6;
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is decreasing, but " + type + " is moving sideways, means price is dropping at the same rate as "+(type == "PE Band"? "EPS" : "Book Value")+".";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is decreasing, but " + type + " is moving sideways.";
                }
            }
            else
            {
                if (isBandup == 1)
                {
                    tmp++;
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is moving sideways, but " + type + " is trending up, means price is dropping.";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is moving sideways, but " + type + " is trending up.";
                }
                else if (isBandup == -1)
                {
                    tmp += 6;
                    //isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is moving sideways, but " + type + " is trending down, means price is increasing.";
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " is moving sideways, but " + type + " is trending down.";
                }
                else
                {
                    isEPSup = (type == "PE Band"? "EPS" : "Book Value") + " & " + type + " are both moving sideways.";
                }
            }
        }*/
        
        if (isEPSup != "")
        {
            if (tmp == 1)
                TAcommentTXT.push(TAcommentHTMLup.format(isEPSup));
            else if (tmp == 6)
                TAcommentTXT.push(TAcommentHTMLdown.format(isEPSup));
            else
                TAcommentTXT.push(TAcommentHTMLsideway.format(isEPSup)); 
        }    

        return [TAcommentTXT.join("") + "</table>", isNaN(score) ? -1 : Math.round((score < 0 ? 0 : score > 10 ? 10 : score)/2)];
    }
    else
        return ["", -1];
}

/*
function Pxband_index(stock, valTable, pricedata, type, container, patternup, patterndown, title)
{
    // valTable as PE, instead of EPS in Pxband, but the band difference is minimal
    var rows = pricedata.split("\n");
    
    k = 0;
    for (j = 0; j < valTable.length; j++)
    {
        if (valTable[j] === "")
            continue;
            
        epsdate = valTable[j].substring(0, valTable[j].indexOf(";"));
        value = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
        
        for (i = k; i < rows.length; i++)
        {
            var fields = rows[i].split(",");
            if (fields.length != 6 && fields.length != 2)
                continue;
                
            var tradedate = fields[0];
            if (epsdate == tradedate)
            {
                valTable[j] = epsdate + ";" + parseFloat(fields[1])/value;
                break;
            }
            else 
            {
                if (rows[rows.length - 1] != "")
                {
                    if (i ==  rows.length - 1 && j == valTable.length - 1)
                        valTable[j] = epsdate + ";" + parseFloat(fields[1])/value;
                }
                else
                {
                    if (i ==  rows.length - 2 && j == valTable.length - 1)
                        valTable[j] = epsdate + ";" + parseFloat(fields[1])/value;
                }
            }
        }
        k = i;
    }
    
    k = 0;
    var close = [];
    var eps = [];
    var epsEstimate = [];
    var total = 0, sum = 0;
    for (j = 1; j < valTable.length; j++)
    {
        if (valTable[j] === "" || valTable[j-1] === "")
            continue;
            
        epsdate = str2date(valTable[j].substring(0, valTable[j].indexOf(";")));
        lastepsdate = str2date(valTable[j-1].substring(0, valTable[j-1].indexOf(";")));
        
        if (j == valTable.length - 1)
        {
            var lastrow;
            if (rows.length > 0)
                lastrow = rows[rows.length - 1];
            if (lastrow === "" && rows.length > 1)
                lastrow = rows[rows.length - 2];
            var fieldslatest = lastrow.split(",");
            if (fieldslatest.length == 6 || fieldslatest.length == 2)
            {
                if (epsdate > str2date(fieldslatest[0]))
                    epsdate = str2date(fieldslatest[0]);
            }
        }
        
        var tradedateLatest;
        for (i = k; i < rows.length; i++)
        {
            var fields = rows[i].split(",");
            var fieldslast = i<rows.length-1 && i > 0 ? rows[i-1].split(",") : "";
            if (fields.length != 6 && fields.length != 2)
                continue;
                
            var tradedate = str2date(fields[0]);
            tradedateLatest = tradedate;
            if (close === undefined || close.length === 0 || (close.length > 0 && close[close.length - 1][0] < utc(str2date(formatDate(tradedate)))))
            {
                if (fields.length == 6)
                    close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[4])]);
                else if (fields.length == 2)
                    close.push([utc(str2date(formatDate(tradedate))), parseFloat(fields[1])]);
            }
            
            if (tradedate == lastepsdate ||
                (tradedate >= lastepsdate && tradedate <= epsdate) ||
                (fieldslast !== "" && tradedate > lastepsdate && str2date(fieldslast[0]) < lastepsdate))
            {
                var currentEPS = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
                var lastEPS = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

                if (isNaN(currentEPS) || isNaN(lastEPS))
                    eps.push([str2date(formatDate(tradedate)), null]);
                else
                {
                    var epsdatediff = (epsdate - lastepsdate);
                    var tradedateepsdatediff = (tradedate - lastepsdate);
                    var ratio = tradedateepsdatediff / epsdatediff;
                    eps.push([str2date(formatDate(tradedate)), (ratio * (currentEPS - lastEPS) + lastEPS)]);
                    sum += close[i][1] / (ratio * (currentEPS - lastEPS) + lastEPS);
                    total++;
                }
            }
            else if (tradedate < lastepsdate)
                eps.push([str2date(formatDate(tradedate)), null]);
            else if (tradedate > epsdate)
                break;
        }
        
        if (j == valTable.length - 1 && tradedateLatest < epsdate)
        {
            var epsdatediffEst = (epsdate - lastepsdate);
            var currentEPSEst = parseFloat(valTable[j].substring(valTable[j].indexOf(";") + 1));
            var lastEPSEst = parseFloat(valTable[j-1].substring(valTable[j-1].indexOf(";") + 1));

            for (m = 1; tradedateLatest.setDate(tradedateLatest.getDate() + m) <= epsdate;)
            {
                if(tradedateLatest.getDay() != 6 && tradedateLatest.getDay() !== 0)
                {
                    var tradedateepsdatediffEst = (tradedateLatest - lastepsdate);
                    var ratioEst = tradedateepsdatediffEst / epsdatediffEst;
                    epsEstimate.push([str2date(formatDate(tradedateLatest)), (ratioEst * (currentEPSEst - lastEPSEst) + lastEPSEst)]);
                }
            }
        }

        k = i;
    }
    
    var avg = sum/total;
    var stdsum = 0.0;
    for(i = 0; i < eps.length; i++)
    {
        if (eps[i][1] !== null)
            stdsum += (close[i][1]/eps[i][1] - avg)*(close[i][1]/eps[i][1] - avg);
    }
    
    var stdev = Math.sqrt(stdsum / total);
    
    var rangesA = [];
    var rangesB = [];
    var rangesC = [];
    var rangesD = [];
    var rangesE = [];
    var rangesF = [];
    if (!isNaN(avg) && isFinite(avg) && !isNaN(stdev) && isFinite(stdev))
    {
        for (i = 0; i < eps.length; i++)
        {
            if (eps[i][1] !== null && eps[i][1] > 0)
            {
                rangesA.push([utc(eps[i][0]), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2))]);
                rangesB.push([utc(eps[i][0]), parseFloat(((avg) * eps[i][1]).toFixed(2)), parseFloat(((avg + stdev) * eps[i][1]).toFixed(2))]);
                rangesC.push([utc(eps[i][0]), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg) * eps[i][1]).toFixed(2))]);
                rangesD.push([utc(eps[i][0]), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - stdev) * eps[i][1]).toFixed(2))]);
                rangesE.push([utc(eps[i][0]), parseFloat(((avg + 2 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg + 3 * stdev) * eps[i][1]).toFixed(2))]);
                rangesF.push([utc(eps[i][0]), parseFloat(((avg - 3 * stdev) * eps[i][1]).toFixed(2)), parseFloat(((avg - 2 * stdev) * eps[i][1]).toFixed(2))]);
            }
        }
    }

    var length = close.length;
    for (i = 0; i < length - 1 && rangesA.length > 0; i++)
    {
        if (close[0][0] < rangesA[0][0])
            close.splice(0, 1);
        else
            break;
    }

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

    var chart = Highcharts.chart(container, {
        chart: {
            style: {
                fontFamily: 'Montserrat'
            },
        },

        title: {
            text: (title == "" ? stock : title) + ' ' + type
        },

        xAxis: {
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
            selected: 6,
            buttons: [{
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
                count: 4,
                text: '4y'
            }, {
                type: 'all',
                text: 'All'
            }]
        },
        tooltip: {
            crosshairs: true,
            shared: true,
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
              backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#2a2a3e':'#eee';})(),
              borderColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#555':'#777';})(),
              symbols: [
                'customcircle',
                'customcircle'
              ]
            },
            series: {
                type: 'areaspline',
                lineColor: 'rgba(0, 128, 255, 0.5)',
                shadow: false,
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

        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    align: 'center',
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
        },
    
        series: [{
            name: stock,
            data: close,
            zIndex: 4,
            color: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})(),
            lineWidth: 4,
            marker: {
                enabled: false,
                fillColor: 'white',
                lineWidth: 2,
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})()
            },
            navigatorOptions: { // Specific values that affect the series in the navigator only
                type: 'line',
                lineColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#e0e0e0':'black';})()
            },
            states: {
                inactive: {
                    opacity: 1
                },
                //hover: {enabled: false },
            },
        }, {
            name: 'Median',
            id: 'median',
            data: rangesB,
            lineWidth: 4,
            //linkedTo: ':previous',
            color: '#ffff00',
            shadow: true,
            zIndex: 3,
            marker: {
                enabled: false,
                symbol: 'square',
                fillColor: 'white',
                lineWidth: 2,
                lineColor: '#ffff00'
            },
            //states: { hover: {enabled: false }},
            showInNavigator: true,
        }, {
            visible: false,
            name: '2 to 3 STDEV',
            id: 'rangesE',
            data: rangesE,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#1181C8',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '1 to 2 STDEV',
            id: 'rangesA',
            data: rangesA,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#0ab6f3',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: 'Median to 1 STDEV',
            id: 'rangesB',
            data: rangesB,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#53f7ff',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-1 STDEV to Median',
            id: 'rangesC',
            data: rangesC,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#ef50cf',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            name: '-2 to -1 STDEV',
            id: 'rangesD',
            data: rangesD,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#8e4ce9',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }, {
            visible: false,
            name: '-3 to -2 STDEV',
            id: 'rangesF',
            data: rangesF,
            type: 'arearange',
            lineWidth: 0,
            //linkedTo: ':previous',
            color: '#5B1CB3',
            fillOpacity: 0.5,
            zIndex: 0,
            marker: {
                enabled: false
            },
            states: { hover: {enabled: false }},
        }
        ]
    });
    
    return valuationComment(stock, type, avg, stdev, close, eps[eps.length - 1][1], rangesB);
}
*/
