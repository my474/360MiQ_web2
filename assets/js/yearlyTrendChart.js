function yearlyTrendChart(id, dictYearlyTrend, seriesname, stockcode, browserwidth, isTooltip, yAxis_name, stockname_en_tc, isPercentMode = false, alwaysHideValueModeValues = false) {
    var hideStockPriceValues = !isPercentMode && (
        alwaysHideValueModeValues ||
        (window.PriceDisplayPolicy && !PriceDisplayPolicy.showStockIndexPrices())
    );
    var isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
    var axisLabelColor = isDarkTheme ? '#cccccc' : '#555555';
    var axisTitleColor = isDarkTheme ? '#cccccc' : '#333333';
    var axisLineColor = isDarkTheme ? '#666666' : '#cccccc';
    var chartContainer = typeof id === 'string' ? document.getElementById(id) : id;
    if (chartContainer && chartContainer.classList)
        chartContainer.classList.add('yearly-trend-uniform-axes');
    var indexNameMap = {
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
    var displayIndexName = function (text) {
        if (typeof text !== 'string' || text.indexOf('S&P 500 ETF') !== -1)
            return text;

        Object.keys(indexNameMap).forEach(function (oldName) {
            text = text.replace(oldName, indexNameMap[oldName]);
        });
        return text;
    };
    stockcode = displayIndexName(stockcode);
    yAxis_name = displayIndexName(yAxis_name);

    // Prepare data: Convert to percentages if isPercentMode is true
    let processedData = {};
    if (isPercentMode) {
        // Calculate percentages relative to the first year's values (or another baseline)
        for (let year of seriesname) {
            processedData[year] = [];
            let baseline = dictYearlyTrend[year][0][1] || 1; // Use first series as baseline. Avoid division by zero
            for (let i = 0; i < dictYearlyTrend[year].length; i++) {
                let value = dictYearlyTrend[year][i];
                processedData[year].push([value[0], rounding(((value[1] / baseline - 1) * 100), 10)]); // Convert to percentage
            }
        }
    } else {
        processedData = dictYearlyTrend; // Use original data
    }

    var chart = Highcharts.chart(id, {
        title: {
            text: stockcode + (isPercentMode ? ' Yearly Trend (Performance %)' : ' Yearly Trend'),
            style: {
                fontSize: '16px',
            },
        },
        subtitle: {
            text: stockname_en_tc.replace(/\./g, ' ● ').substring(0, 45) // Fixed 'dot' to regex
        },
        chart: {
            events: {
                render: function() {
                    // Update credits position: plotTop + offset
                    this.credits.attr({
                        y: this.plotTop + 42 // 42px below plot area top
                    });
                }
            },
            style: {
                fontFamily: 'Montserrat'
            },
        },
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    align: 'right',
                    x: 4,
                    formatter: function () {
                        if (this.point.index == this.series.points.length - 1) {
                            var fontsize = '';
                            if (this.series.index == 5) fontsize = ';font-size:14';
                            return (
                                '<span style="color:' +
                                this.point.color +
                                fontsize +
                                '">' +
                                this.series.name +
                                '</span> '
                            );
                        } else {
                            return null;
                        }
                    },
                    crop: false,
                    overflow: false
                }
            }
        },
        xAxis: {
            crosshair: {
                color: '#9d81ba88'
            },
            lineColor: axisLineColor,
            tickColor: axisLineColor,
            title: {
                text: 'Day of Year',
                style: { color: axisTitleColor }
            },
            tickInterval: 30,
            tickAmount: 12,
            ceiling: 366,
            visible: true,
            dateTimeLabelFormats: { month: '%b' }, // Fixed typo 'dateTimeLabelFormaty'
            min: 1,
            max: 366,
            minPadding: 0.0,
            maxPadding: 0.0,
            labels: {
                rotation: browserwidth <= 576 ? -45 : 0,
                style: { color: axisLabelColor }
            },
            plotLines: [{
                color: '#c9c9c9', // Color value
                value: 1, // Value of where the line will appear
                width: 0,
                dashStyle: 'Dash',
                label: {
                    text: 'Q1', // Label for the next quarter
                    rotation: 0,
                    style: { color: '#c9c9c9', fontSize: '14px' },
                    x: 1, // Adjust horizontal position
                    y: 12    // Position above the line
                }
            }, {
                color: '#c9c9c9', // Color value
                value: 91, // Value of where the line will appear
                dashStyle: 'Dash',
                label: {
                    text: 'Q2', // Label for the next quarter
                    rotation: 0,
                    style: { color: '#c9c9c9', fontSize: '14px' },
                    x: 1, // Adjust horizontal position
                    y: 12    // Position above the line
                }
            }, {
                color: '#c9c9c9', // Color value
                value: 182, // Value of where the line will appear
                dashStyle: 'Dash',
                label: {
                    text: 'Q3', // Label for the next quarter
                    rotation: 0,
                    style: { color: '#c9c9c9', fontSize: '14px' },
                    x: 1, // Adjust horizontal position
                    y: 12    // Position above the line
                }
            }, {
                color: '#c9c9c9', // Color value
                value: 274, // Value of where the line will appear
                dashStyle: 'Dash',
                label: {
                    text: 'Q4', // Label for the next quarter
                    rotation: 0,
                    style: { color: '#c9c9c9', fontSize: '14px' },
                    x: 1, // Adjust horizontal position
                    y: 12    // Position above the line
                }
            }]
        },
        yAxis: [
            {
                crosshair: {
                    color: '#9d81ba88'
                },
                lineColor: axisLineColor,
                tickColor: axisLineColor,
                title: {
                    text: isPercentMode ? 'Performance %' : yAxis_name,
                    style: { color: axisTitleColor }
                },
                lineWidth: 1,
                tickAmount: 8,
                opposite: true,
                labels: {
                    format: isPercentMode ? '{value}%' : '{value}', // Add % symbol in percent mode
                    style: { color: axisLabelColor }
                },
                endOnTick: false,
                startOnTick: false,
                plotLines: [{
                    color: '#c9c9c9', // Color value
                    //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                    value: 0, // Value of where the line will appear
                    width: isPercentMode? 4 : 0, // Width of the line    
                    zIndex: 2
                }]
            },
            {
                crosshair: {
                    color: '#9d81ba88'
                },
                lineColor: axisLineColor,
                tickColor: axisLineColor,
                title: {
                    text: isPercentMode ? 'Performance %' : yAxis_name,
                    style: { color: axisTitleColor }
                },
                lineWidth: 1,
                tickAmount: 8,
                opposite: false,
                labels: {
                    format: isPercentMode ? '{value}%' : '{value}', // Add % symbol in percent mode
                    style: { color: axisLabelColor }
                },
                endOnTick: false,
                startOnTick: false,
                linkedTo: 0 // Link to the first yAxis (index 0)
            }
        ],
        credits: {
            enabled: true,
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '11px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                x: 85,
                y: -400
            }
        },
        tooltip: {
            shared: true,
            split: false,
            enabled: true,
            formatter: function () {
                let s = 'Day Of Year: <b>' + this.x + '</b>';
                if (isTooltip)
                {
                    for (let point of this.points) {
                        s +=
                            '<br><span style="color:' +
                            point.series.color +
                            '">●</span> ' +
                            point.series.name +
                            (hideStockPriceValues ? '' : ': ' +
                                                        (isPercentMode && point.y < 0 ? '<span style="color:red;font-weight:bold">' : (isPercentMode && point.y === 0 ? '<span style="color:grey;font-weight:bold">\u00B1' : '<span style="color:' + (document.documentElement.getAttribute('data-theme') === 'dark' ? '#cccccc' : 'black') + ';font-weight:bold">' + (isPercentMode ? '+' : ''))) +
                            point.y +
                            (isPercentMode ? '%' : '') +
                            '</span>');
                    }
                }

                return s;
            }
        },
        legend: {
            enabled: true,
            borderWidth: 0,
            layout: 'horizontal',
            labelFormatter: function () {
                if (hideStockPriceValues)
                    return '<span style="color:' + this.color + '">' + this.name + '</span>';
                
                var lastValue = this.yData[this.yData.length - 1];
                var color = 'black';
                var sign = "";
                var percentSign = '';
                
                if (isPercentMode)
                {
                    percentSign = '%';
                    
                    if(lastValue > 0)
                    {
                      	sign = "+";
                    }
                  	else if (lastValue === 0)
                  	{
                  	    sign = "\u00B1";
                  	    color = 'grey';
                  	}
                  	else
                  	    color = 'red';
                }

                var values = '</span>';
                if (isTooltip)
                    values = ': </span><span style="color:' + color + '">' + sign + lastValue + percentSign + '</span>';

                return '<span style="color:' + this.color + '">' + this.name + values;
            }
        }
    });

    var colorSeries = ['#00BAFF', '#F88888', '#03ACA8', '#F9C82A', '#79d424', '#F3088D'];
    for (var i = 0; i < seriesname.length; i++) {
        chart.addSeries(
            {
                name: seriesname[i],
                color: colorSeries[i + (colorSeries.length - seriesname.length)],
                lineWidth: i == seriesname.length - 1 ? 3 : 2,
                data: processedData[seriesname[i]],
                marker: {
                    enabled: false
                },
                yAxis: 0
            },
            true
        );
    }
    
    // prevent small part of the end of series appear and disappear after 1st drawn such as stockcode: XXXX
    setTimeout(function() {
        if (chart.series.length > 0)
        {
            // Hide the first series
            chart.series[0].setVisible(false);
            
            // Show the first series again
            chart.series[0].setVisible(true);
        }
    }, 1000);
}
/*function yearlyTrendChart(dictYearlyTrend, seriesname, stockcode, browserwidth, isTooltip, yAxis_name, stockname_en_tc)
{
    var chart = Highcharts.chart('chartcontainerYearlyTrend',{
    	title: { 
	        text: stockcode + ' Yearly Trend',
        	style: {
                fontSize: '16px',
            },
    	},
    	subtitle: {
    	    text: stockname_en_tc.replace(dot, " ● ").substring(0, 45)
    	},
    	chart: {
            style: {
                fontFamily: 'Montserrat'
            },
        },
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    align: 'right',
                    x: 4,
                    formatter: function() {
                        if (this.point.index == this.series.points.length - 1) {
                            var fontsize = '';
                            if (this.series.index == 5)
                                fontsize = ';font-size:14';

                            return '<span style="color:' + this.point.color + fontsize + '">'+this.series.name+'</span> ';
                        } else {
                            return null;
                        }
                    },
                    crop: false,
                    overflow: false
                }
            }
        },
    	xAxis: {
    	    crosshair: {
                color: '#9d81ba88'
            },
    		title: { text: 'Day of Year' },
    		tickInterval: 30,
    		tickAmount: 12,
    		ceiling: 366,
			visible: true,
			dateTimeLabelFormaty: {month: '%b'},
    		min: 1,
    		max: 366,
    		minPadding: 0.0,
    		maxPadding: 0.0,
    		labels: {
                rotation: browserwidth <= 576 ? -45 : 0
            }
		},
		yAxis: [{
          	crosshair: {
                color: '#9d81ba88'
            },
            title: { text: yAxis_name },
    		lineWidth: 1,
    		tickAmount : 8,
    		opposite: true,
        }, {
          	crosshair: {
                color: '#9d81ba88'
            },
            title: { text: yAxis_name },
    		lineWidth: 1,
    		tickAmount : 8,
    		opposite: false,
    		linkedTo: 0 // Link to the first yAxis (index 0)
        }],

    	credits: {
            enabled: true,
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '11px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                x: 85,
                y: -400
            }
        },
    	labels: {
			align: 'right',
        		step: 30,
			x: 0,
			y: 0
		},
    	tooltip: {
			shared: true,
			split: false,
			enabled: isTooltip,
			formatter: function (tooltip) {
				return this.points.reduce(function (s, point) {
                    return s + '<br><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                }, 'Day Of Year: <b>' + this.x + '<b>');
			},
		},
		legend: {
            enabled: true,
            borderWidth: 0,
            layout: 'horizontal',
            labelFormatter: function() {
                return '<span style="color:' + this.color + '">' + this.name + '</span>';
            }
        },
	});
	
	var colorSeries = ['#00BAFF', '#F88888', '#03ACA8', '#F9C82A', '#79d424', '#F3088D'];
    for(var i = 0; i < seriesname.length; i++)
    {
        chart.addSeries({
            name: seriesname[i],
    		color: colorSeries[i + (colorSeries.length - seriesname.length)],
    		lineWidth: i == seriesname.length - 1 ? 3 : 2,
    		data: dictYearlyTrend[seriesname[i]],
    		marker: {
                enabled: false,
            },
            yAxis: 0,
    	}, true);
    }
}*/
