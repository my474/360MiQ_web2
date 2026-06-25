function seasonalityChart(stockcode, pricedata, firstDailyClose, chartcontainer, subtitle)
{
    var dates = [];
    var gains = []
    var gainsYearly = []
    var closeYearly = []
    var close = [];
    var monthlygain = {};
    var monthlyavg = [];
    var winningPct = [];
    var monthlytabledata = {};
    var Months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    if (pricedata.length < 1)
        return null;
    
    var k = 4;
    if (pricedata[0].length == 2)
        k = 1;
        
    if (firstDailyClose !== null) // 1st monthly close / 1st daily close
        gains.push([pricedata[0][0], (pricedata[0][k]/firstDailyClose - 1) * 100]); // close / open
        
    for (var i = 1; i < pricedata.length; i++)
    {
        gains.push([pricedata[i][0], (pricedata[i][k]/pricedata[i - 1][k] - 1) * 100]);
        
        var date_current = new Date(pricedata[i][0]);
        var date_previous = new Date(pricedata[i - 1][0]);
        if (date_current.getYear() > date_previous.getYear())
        {
            if (closeYearly.length === 0)
            {
                if (i > 0 && firstDailyClose !== null)
                    gainsYearly.push([pricedata[i - 1][0], (pricedata[i - 1][k]/firstDailyClose - 1) * 100]);
            }
            else
            {
                gainsYearly.push([pricedata[i - 1][0], (pricedata[i - 1][k]/closeYearly[closeYearly.length - 1][1] - 1) * 100]);
            }
            
            closeYearly.push([pricedata[i - 1][0], pricedata[i - 1][k]]);
        }
    }
    
    if (closeYearly.length === 0)
    {
        if (firstDailyClose !== null)
            gainsYearly.push([pricedata[pricedata.length - 1][0], (pricedata[pricedata.length - 1][k]/firstDailyClose - 1) * 100]);
    }
    else
        gainsYearly.push([pricedata[pricedata.length - 1][0], (pricedata[pricedata.length - 1][k]/closeYearly[closeYearly.length - 1][1] - 1) * 100]);

    var startdate = new Date(gains[0][0]);
    var enddate = new Date(gains[gains.length - 1][0]);
    var start = Months[startdate.getMonth()] + ", " + (startdate.getYear() + 1900);
    var end = Months[enddate.getMonth()] + ", " + (enddate.getYear() + 1900);
    
    for (i = 0; i < gains.length; i++)
    {
        var m = Months[new Date(gains[i][0]).getMonth()];
        if (monthlygain[m])
            monthlygain[m].push(gains[i][1]);
        else
            monthlygain[m] = [gains[i][1]];
            
        var y = new Date(gains[i][0]).getYear() + 1900;
        if (monthlytabledata[y])
            monthlytabledata[y].push({[`${m}`]:rounding(gains[i][1], 10)});
        else
            monthlytabledata[y] = [{[`${m}`]:rounding(gains[i][1], 10)}];
    }
    
    for (i = 0; i < gainsYearly.length; i++)
    {
        var y = new Date(gainsYearly[i][0]).getYear() + 1900;
        if (monthlytabledata[y])
            monthlytabledata[y].push({['YTD']:rounding(gainsYearly[i][1], 10)});
        else
            monthlytabledata[y] = [{['YTD']:rounding(gainsYearly[i][1], 10)}];
    }
    
    for (i = 0; i < Months.length; i++)
    {
        var sum = 0;
        var winning = 0;
        for (j = 0; monthlygain[Months[i]] && j < monthlygain[Months[i]].length; j++)
        {
            var mg = monthlygain[Months[i]][j];
            sum += mg;
            
            if (mg > 0)
                winning++;
        }
        
        if (monthlygain[Months[i]])
        {
            monthlyavg.push([Months[i], rounding(sum / monthlygain[Months[i]].length, 10)]);
            winningPct.push([Months[i], rounding(winning / monthlygain[Months[i]].length * 100, 10)]);
        }
    }
    
    for (i = 0; i < monthlyavg.length; i++)
    {
        if (monthlytabledata['Avg'])
            monthlytabledata['Avg'].push({[monthlyavg[i][0]]:monthlyavg[i][1]});
        else
            monthlytabledata['Avg'] = [{[monthlyavg[i][0]]:monthlyavg[i][1]}];
    }
    var sumYearly = 0;
    for (i = 0; i < gainsYearly.length; i++)
        sumYearly += gainsYearly[i][1];
    monthlytabledata['Avg'].push({['YTD']:rounding(sumYearly/gainsYearly.length, 10)});
    
    //close.reverse();
    //var ymin = null;//Math.min.apply(null, close);// * 0.9,
    //var ymax = null;//Math.max.apply(null, close);// * 1.1;
    
    var ymin0 = null;//Math.min.apply(null, surprise);// * 0.9,
    var ymax0 = null;//Math.max.apply(null, surprise);// * 1.1;
    
    // legend wrapper
    (function (HC) {    // this will override any column/bar chart legend, not just Seasonality Chart
        HC.wrap(Highcharts.seriesTypes.column.prototype, 'drawLegendSymbol', function (fn, legend, item) {
        	var dx = -5;
        	var r = 9;
        	// commented this out on 2025-03-25 as item.options.outlineSeries doesn't seem to be existed according to google and grok
            /*if (item.options.outlineSeries)
            {
                item.legendSymbol = this.chart.renderer.rect(
                dx,
                legend.baseline - r,
                r * 2,
                r).attr({
                    zIndex: 3,
                    stroke: item.options.borderColor,
                        'stroke-width': 2
                }).add(item.legendGroup);
            }
            else*/ if (item.options.name == "Average Monthly Return %")
            {
                item.legendSymbol = this.chart.renderer.rect(
                    dx,
                    legend.baseline - r,
                    r * 2,
                    r)
                .attr({
                        zIndex: 3
                })
                .add(item.legendGroup);
                
                //red triangle in legend symbol
                item.legendSymbol2 = this.chart.renderer.path([
                    'M',
                    dx + r * 2 + 0.5,
                    legend.baseline - r,
                    'L',
                    dx + r * 2 + 0.5,
                    legend.baseline + 0.5,
                    
                    dx - 0.5,
                    legend.baseline + 0.5,
                    'Z'])
                .attr({
                        zIndex: 3,
                        fill: {
        	              linearGradient: {
        	                x1: 0,
        	                x2: 0,
        	                y1: 1,
        	                y2: 0
        	              },
        	              stops: [
        	              	[0, '#FF6900'],
        	                [1, '#ff9600'],
        	              ]
        	            }
                })
                .add(item.legendGroup);
            }
            else if (legend.chart.title.textStr == "Piotroski F‑Score")   // this will override F-Score chart on StockInfo.php
            {
                item.legendSymbol = this.chart.renderer.rect(
                    0,
                    legend.baseline - 10,
                    7,
                    legend.baseline - 4)
                .attr({
                    zIndex: 3,
                })
                .add(item.legendGroup);
            }
            else
            {
                // Create circle symbol for default same as original for any column chart on the same page as this Seasonality Chart
                item.legendSymbol = this.chart.renderer.circle(
                    9,              // x position (center)
                    11,                  // y position (centered vertically)
                    6              // radius
                )
                .attr({
                    zIndex: 3,
                    fill: item.color || this.color || '#666666',  // Use series color
                    stroke: window.tv ? window.tv('black', '#555') : 'black',     // Optional outline
                    'stroke-width': 0    // Set to 0 if no outline wanted
                })
                .add(item.legendGroup);
            }
        });
    })(Highcharts);
        
    (function (H) {
    	H.wrap(H.Tick.prototype, 'render', function (p, index, old, opacity) {
          	p.call(this, index, old, opacity);
          	var gridLine = this.gridLine;
          	
            if (!this.axis.horiz && this.pos === 0 && gridLine)
            {
            	gridLine.attr({
                  	stroke: window.tv ? window.tv('white', '#555') : 'white',
                });
            }
        });
    })(Highcharts);
    
    var isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
    var axisLabelColor = isDarkTheme ? '#cccccc' : '#555555';
    var axisTitleColor = axisLabelColor;
    var axisLineColor = isDarkTheme ? '#666666' : '#cccccc';
    var chartContainerElement = typeof chartcontainer === 'string' ? document.getElementById(chartcontainer) : chartcontainer;
    if (chartContainerElement && chartContainerElement.classList)
        chartContainerElement.classList.add('average-monthly-return-axes');

    Highcharts.chart(chartcontainer, {
        chart: {
            type: 'column',
            style: {
                fontFamily: 'Montserrat'
            }
        },
        plotOptions: {
            column: {
                borderRadius: 3,
                dataLabels: {
                    enabled: true,
                    crop: false,
                    overflow: 'none',
                    formatter: function () {
                        if (this.y > 0) {
                            return '<span style="fill:dimgrey;font-weight:bold;font-size:13px">+' + this.y + '%</span>';
                        }
                        else if (this.y === 0) {
                            return '<span style="fill:dimgrey;font-weight:bold;font-size:13px">\u00B1' + this.y + '%</span>';
                        } else {
                            return '<span style="fill:dimgrey;font-weight:bold;font-size:13px">' + this.y + '%</span>';
                        }
                    }
                }
            }
        },        
        title: {
            text: stockcode + " Average Monthly Return %"
        },
        subtitle : {
            text: subtitle + " ● " + start + " to " + end
        },
        xAxis: {
            categories: Months,
            type: 'category',
            lineColor: axisLineColor,
            tickColor: axisLineColor,
            labels: {
                rotation: 300,
                align: 'right',
                y: 15,
                style: {
                    color: axisLabelColor,
                    fontSize:'14px'
                }
            },
        },
        yAxis: [{
			min: ymin0,
		    max: ymax0,
            lineWidth: 1,
            lineColor: axisLineColor,
            tickColor: axisLineColor,
            title: {
                text: 'Average Monthly Return %',
                margin: 3,
                style: {
					color: axisTitleColor,
					fontWeight: 'bold'
				},
            },
            labels: {
				style: {
					color: axisLabelColor,
				},
				formatter: function () {
                    if (this.value < 0) {
                        return '<span style="fill:#ff9600;font-weight:bold;">' + this.value + '</span>';
                    }
                    else if (this.value === 0) {
                        return '<span style="fill:lightgrey;font-weight:bold;">' + this.value + '</span>';
                    } else {
                        return '<span style="fill:#00bbff;font-weight:bold;">' + this.value + '</span>';
                    }
                }
			},
			plotLines: [{
                color: window.tv ? window.tv('lightgrey', '#555') : 'lightgrey',
                dashStyle: 'dash',
                width: 2,
                value: 0,
                zIndex: 3
            }],
            //tickInterval: 1,
            //gridLineDashStyle: 'dot',
		    endOnTick: false,
            //maxPadding: 0,
        }, { // Secondary yAxis
			min: 0,
		    max: 100,
            lineWidth: 1,
            lineColor: 'gold',
            tickColor: 'gold',
			title: {
				text: 'Winning %',
				style: {
					color: 'gold',
					fontWeight: 'bold'
				},
                //margin: 0
			},
			gridLineWidth: 0,
			labels: {
				format: '{value}',
				style: {
					color: 'gold',
					fontWeight: 'bold'
				},
                x: 5,
			},
			plotLines: [{
                color: 'gold',
                dashStyle: 'dash',
                width: 2,
                value: 50,
                zIndex: 3
            }],
			endOnTick: false,
			tickInterval: 25,
			opposite: true
		}],
        legend: {
            enabled: true
        },    
		tooltip: {
			split: false,
			shared: true,
			useHTML: true,
            //valueDecimals: 4,
            formatter: function (tooltip) {
                var s = '<span style="font-size:10px">' + this.x + '</span>';
                            
                $.each(this.points , function(i, point) {
                    
                    var change = point.y;
                    if(point.series.index === 0)
                    {
                        if (change > 0)
                        {
                            s = s + '<br/><span style="color:#00bbff">●</span> ' + point.series.name + ': ' + '<b>+' + point.y + '</b>';
                        }
                        else if (change < 0)
                        {
                            s = s + '<br/><span style="color:#FF6900">●</span> ' + point.series.name + ': ' + '<span style="color:red"><b>' + point.y + '</b></span>';
                        }
                        else
                        {
                            s = s + '<br/><span style="color:#FF6900">●</span> ' + point.series.name + ': ' + '<span style="color:grey"><b>\u00B1' + point.y + '</b></span>';
                        }
                    }
                    else
                    {
                        if (change >= 50)
                        {
                            s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                        }
                        else
                        {
                            s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red"><b>' + point.y + '</b></span>';
                        }
                    }
                    
                });
    
                return s;
            },
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
                y: -500
            }
        },
        series: [ {
            name: 'Average Monthly Return %',
            data: monthlyavg,
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
            name: 'Winning %',
            color: 'gold',
            lineWidth: 0,
            shadow: false, 
            yAxis: 1,
            data: winningPct,
            marker: {
                radius: 7
            },
        }]
    });
    
    return monthlytabledata;
}
