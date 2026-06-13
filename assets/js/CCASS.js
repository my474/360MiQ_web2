function CCASS(chartcontainer, data, stockcode, CCASSshare, CCASSpercent, title, subtitle, yaxis0, yaxis1, unitType, rangeSelected, creditY, startx = null, endx = null)
{
    var visibleDict = {};
    var seriesType = 'line';
    var seriesOpacity = 1;
    var close = {};
    var dateclose = {};

    for (var j = 0; j < data.length; j++)
    {
        if (data[j] && data[j].length > 0 && data[j][0] >= CCASSshare[0][0])
        {
            if (dateclose.length > 0)
		        dateclose.push([data[j][0], data[j][4]]);
	        else
	            dateclose = [[data[j][0], data[j][4]]];
        }
    }
    
    var now = new Date();
    var utc_timestamp = Date.UTC(now.getFullYear(),now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds(), now.getMilliseconds());
    
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

                    for (var i = 0; i < this.series.length; i++)
                    {
                        //if (visibleDict[this.series[i].name]) {
                            //visibleDict[this.series[i].name].push([this.series[i].processedYData[0], this.series[i].processedYData[this.series[i].processedYData.length - 1]]);
                        //} else {
                            visibleDict[this.series[i].name] = [{ x: this.series[i].processedXData[0], y: this.series[i].processedYData[0] }, { x: this.series[i].processedXData[this.series[i].processedXData.length - 1], y: this.series[i].processedYData[this.series[i].processedYData.length - 1] }];
                        //}
                    }

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
                            s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + changeStr + '</b>';
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
                x: 75,
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
            top: '33%',
            height: '67%',
        }, {
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: 'Share in CCASS',
                style: {
                  color: "#62b8d7"
                }
            },
            opposite: true,
            lineWidth: 1,
            //max : ymax,
            //min: ymin,
            showLastLabel: true,
            //tickAmount : 5,
            labels: {
                x: -1,
                y: 9,
            },
            top: '0%',
            height: '33%',
            offset: 0,
        }, {
          	crosshair: {
                color: '#9d81ba88'
            },
            title: {
                //useHTML: yaxis1.indexOf('font') !== -1 ? true : false,
                text: '% of All Issued Shares',
                style: {
                  color: "#e696d6"
                }
            },
            opposite: false,
            lineWidth: 1,
            //max : ymax,
            //min: ymin,
            showLastLabel: true,
            //tickAmount : 5,
            labels: {
                x: 1,
                y: 9,
                align: 'left',
            },
            //top: '75%',
            //height: '25%',
            top: '0%',
            height: '33%',
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
            backgroundColor: (function(){var d=document.documentElement.getAttribute('data-theme');return d==='dark'?'#2a2a3e':'#eee';})(),
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
    	
	colorSeries = "#84DAF9";
    chart.addSeries({
    		    type: 'area',
    			name: 'Southbound Share Holding in CCASS',
    			data: CCASSshare,
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
        	
    colorSeries = "#F7A7E7";
    chart.addSeries({
		    type: 'line',
			name: '% of All Issued Shares',
			data: CCASSpercent,
			lineWidth: 4,
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

    document.documentElement.addEventListener('themechange', function() {
      if (chart && chart.update) {
        chart.update(getHighchartsThemeOptions(), true, false);
      }
    });

    return chart;
}
