function adchart(chartcontainer, data, types, title, subtitle, yaxis0, yaxis1, ymin, ymax, unitType, rangeSelected, seriesType)
{
    var hideStockPriceValues = window.PriceDisplayPolicy && !PriceDisplayPolicy.showStockIndexPrices();

    /**
     * Highcharts plugin for creating individual rounded corners.
     * 
     * Author: Torstein Honsi
     * Version: 1.0.5
     * License: MIT License
     */
    (function (factory) {
        "use strict";
    
        if (typeof module === "object" && module.exports) {
            module.exports = factory;
        } else {
            factory(Highcharts);
        }
    }(function (H) {
        var rel = H.relativeLength;
    
        H.wrap(H.seriesTypes.column.prototype, 'translate', function (proceed) {
            var options = this.options,
                topMargin = options.topMargin || 0,
                bottomMargin = options.bottomMargin || 0;
    
            proceed.call(this);
    
            H.each(this.points, function (point) {
                var shapeArgs = point.shapeArgs,
                    w = shapeArgs.width,
                    h = shapeArgs.height,
                    x = shapeArgs.x,
                    y = shapeArgs.y;
    
                // Get the radius
                var rTopLeft = rel(options.borderRadiusTopLeft || 0, w),
                    rTopRight = rel(options.borderRadiusTopRight || 0, w),
                    rBottomRight = rel(options.borderRadiusBottomRight || 0, w),
                    rBottomLeft = rel(options.borderRadiusBottomLeft || 0, w);
            
                if (rTopLeft || rTopRight || rBottomRight || rBottomLeft) {
                    var maxR = Math.min(w, h) / 2
                        
                    if (rTopLeft > maxR) {
                        rTopLeft = maxR;
                    }
    
                    if (rTopRight > maxR) {
                        rTopRight = maxR;
                    }
    
                    if (rBottomRight > maxR) {
                        rBottomRight = maxR;
                    }
    
                    if (rBottomLeft > maxR) {
                        rBottomLeft = maxR;
                    }
    
                    // Preserve the box for data labels
                    point.dlBox = point.shapeArgs;
    
                    point.shapeType = 'path';
                    point.shapeArgs = {
                        d: [
                            'M', x + rTopLeft, y + topMargin,
                            // top side
                            'L', x + w - rTopRight, y + topMargin,
                            // top right corner
                            'C', x + w - rTopRight / 2, y, x + w, y + rTopRight / 2, x + w, y + rTopRight,
                            // right side
                            'L', x + w, y + h - rBottomRight,
                            // bottom right corner
                            'C', x + w, y + h - rBottomRight / 2, x + w - rBottomRight / 2, y + h, x + w - rBottomRight, y + h + bottomMargin,
                            // bottom side
                            'L', x + rBottomLeft, y + h + bottomMargin,
                            // bottom left corner
                            'C', x + rBottomLeft / 2, y + h, x, y + h - rBottomLeft / 2, x, y + h - rBottomLeft,
                            // left side
                            'L', x, y + rTopLeft,
                            // top left corner
                            'C', x, y + rTopLeft / 2, x + rTopLeft / 2, y, x + rTopLeft, y,
                            'Z'
                        ]
                    };
                }
                    
            });
        });
    }));

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
			type: seriesType,
			style: {
                fontFamily: 'Montserrat'
            }
		},
		title: {
			text: title,
			//margin: 5
		},
        subtitle: {
            text: subtitle,
        },
		xAxis: {
            tickWidth: 0,
            minRange: 1135000000,
        	labels: {
        		align: 'center',
        	//	x: -10,
	        },
		},
		yAxis: [{
			min: ymin,
			max: ymax,
			endOnTick: false,
            maxPadding: 0,
			gridLineWidth: 0,
			title: {
                enabled: false,
				text: yaxis1
			},
			labels : {
				enabled: false,
			},
			stackLabels: {
				enabled: false,
				style: {
					fontWeight: 'bold',
					color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
				}
			}
		}, { // Secondary yAxis
			title: {
				text: yaxis0,
				style: {
					color: '#FF7b00',
				},
                //margin: 0
                
			},
			showLastLabel: true,
			labels: {
				format: '{value}',
				style: {
					color: '#FF7b00'
				},
				align: 'left',
                x: 5,
                y: 5,
			},
			opposite: true
		}],
		
        credits: {
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '12px',
                cursor: 'arrow'
            },
            position: {
                align: 'left',
                verticalAlign: 'bottom',
                x: 10,
                y: -105
            }
        },
				
		navigator: {
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
                shadow: false,
			}
		},	
				
        legend: {
            align: 'center',

            enabled: true,
            //verticalAlign: 'top',
            borderWidth: 0,
            //y: -35,
            layout: 'horizontal',
            labelFormatter: function() {
                var current = this.yData[this.yData.length - 1];
                var previous = this.yData[this.yData.length - 2];

                var chg_percent2 = (current / previous - 1) * 100;
                var	chg_percent = current - previous;
                if(this.index == 2)
                {
                    chg_percent = rounding( chg_percent, 1000 );
                    var rounding_decimal = Math.abs(chg_percent2) < 0.1 ? 100 : 10;
                    chg_percent2 = rounding( chg_percent2, rounding_decimal );
                }
                else
                {
                    chg_percent = rounding( chg_percent, 10 );
                }
                
                var sign = "";
                if(chg_percent > 0)
                  	sign = "+";
              	else if (chg_percent === 0)
              	    sign = "\u00B1";

                if(this.index == 2)  	
                    return '<span style="color:' + this.color + '">' + this.name + ": " + (hideStockPriceValues ? sign + chg_percent2 + "%" : current + " (" + sign + chg_percent + ", " + sign + chg_percent2 + "%)") + '</span>';
                else
                    return '<span style="color:' + this.color + '">' + this.name + ": " + current + '</span>';
                
            }
        },
		tooltip: {
			split: false,
			shared: true,
		/*useHTML: true,
			formatter: function () {
				return '<b>' + this.x + '</b><br/>' +
					this.series.name + ': ' + this.y + '<br/>' +
					'Total: ' + this.point.stackTotal;
			}*/
		    formatter: function() {
                var changeStr = '',
                    index = this.points.length == 3 ? this.points[2].point.index : -1;
                if (index > 0) {
                    var change = ((this.points[2].series.yData[index] / this.points[2].series.yData[index - 1]) - 1) * 100;
                    var rounding_decimal = Math.abs(change) < 0.1 ? 100 : 10;
                    change = rounding( change, rounding_decimal );
                    if (change > 0)
                        changeStr = ' (+' + change + '%)';
                    else if (change === 0)
                        changeStr = ' (\u00B1' + change + '%)';
                    else
                        changeStr = ' (' + change + '%)';
                
                    return '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span><br>' + 
                            '<span style="color:' + this.points[2].series.color + '">●</span> ' + this.points[2].series.name + ': <b>' + (hideStockPriceValues ? changeStr.replace(/[()]/g, '').trim() : this.points[2].series.yData[index] + changeStr) + '</b><br>' + 
                            '<span style="color:' + this.points[0].series.color + '">●</span> ' + this.points[0].series.name + ': <b>' + this.points[0].series.yData[index] + '</b><br>' + 
                            '<span style="color:' + this.points[1].series.color + '">●</span> ' + this.points[1].series.name + ': <b>' + this.points[1].series.yData[index] + '</b>';
                }
            }
		},
        rangeSelector: {
            selected: rangeSelected,
            buttons: [{
                type: 'month',
                count: 1,
                text: '1m'
            }, {
                type: 'month',
                count: 2,
                text: '2m'
            }, {
                type: 'month',
                count: 3,
                text: '3m'
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
                type: 'all',
                text: 'All'
            }]
        },
		plotOptions: {
			column: {
    			pointPadding: -0.1,
    			pointPlacement: 0,
    			stacking: 'normal',
    			borderWidth: 2,
    			dataLabels: {
    				enabled: true,
    				color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
    				style: {
    					textShadow: '0 0 1px black, 0 0 1px black'
    				}
    			},
    			dataGrouping: {
    				enabled: false,
    				forced: false,
    				units: [
    					[unitType, [1]]
    				]
    			}
			}
		},
		series: [{
			name: 'Decline %',
            showInNavigator: false,
			color:  {
			  linearGradient: {
				x1: 0,
				x2: 0,
				y1: 1,
				y2: 0
			  },
			  stops: [
				[0, '#F30'],
				[0.7, '#e00'],
				[1, '#c00']
			  ]
			},
			data: data[types[2]],
            borderRadiusTopLeft: '3px',
            borderRadiusTopRight: '3px',
            borderRadiusBottomLeft: '3px',
            borderRadiusBottomRight: '3px'			
		},{
			name: 'Advance %',
			showInNavigator: false,
			color: {
			  linearGradient: {
				x1: 0,
				x2: 0,
				y1: 0,
				y2: 1
			  },
			  stops: [
				[0, '#3f0'],
				[0.7, '#0e0'],
				[1, '#0c0']
			  ]
			},
			data: data[types[1]],
			borderRadiusTopLeft: '3px',
            borderRadiusTopRight: '3px',
            borderRadiusBottomLeft: '3px',
            borderRadiusBottomRight: '3px'
		},{
		showInNavigator: true,
		yAxis: 1,
		lineWidth: 4,
		color: 'gold', /*{
				  linearGradient: {
					x1: 0,
					x2: 0,
					y1: 0,
					y2: 1
				  },
				  stops: [
					[0, 'gold'],
					[0.5, 'goldenrod'],
                    [1, 'gold'],
				  ]
				},*/
		name: types[0],
		type: 'spline',
        shadow: true,            
		data: data[types[0]],
		
		pointPlacement: 0.5
		}]
	});
	
	return adComment(data, types, subtitle);
}

// theme change now handled by highcharts-theme.js applyHighchartsTheme()

function adComment(data, types, subtitle)
{
    if (data[types[0]].length > 1 && data[types[1]].length > 0 && data[types[0]][data[types[0]].length - 1][0] == data[types[1]][data[types[1]].length - 1][0])
    {
        var currentClose = data[types[0]][data[types[0]].length - 1][1];
        var previousClose = data[types[0]][data[types[0]].length - 2][1];
        var change_p = (currentClose / previousClose - 1) * 100;
        var rounding_decimal = Math.abs(change_p) < 0.1 ? 100 : 10;
        change_p = rounding( change_p, rounding_decimal );
        
        var isUp = currentClose > previousClose? 1 : (currentClose < previousClose? -1 : 0);
        var adv = data[types[1]][data[types[1]].length - 1][1];

        var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
        var TAcommentHTMLup1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var TAcommentHTMLdown1st = '<tr><td rowspan="2" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
        var tmp = 0;
        var comment = "";

        if (isUp == -1 && adv > 50)
        {
            tmp++;
            comment = types[0] + " declined " + change_p + "%, but with more than half of all " + subtitle + " stocks (" + adv + "%) that advanced.";
            
            for (var i = data[types[0]].length - 2; i > 0; i--)
            {
                var benchmark_gainX = rounding((data[types[0]][i][1] / data[types[0]][i - 1][1] - 1) * 100, 100);
                if (benchmark_gainX < 0 && data[types[1]][i][1] > 50)
                {
                    comment = "For " + (data[types[0]].length - i) + " days in a row, " + types[0] + " declined, but with more than half of all " + subtitle + " stocks that advanced.";
                }
                else
                    break;
            }
        }
        else if (isUp == 1 && adv < 50)
        {
            tmp += 6;
            comment = types[0] + " advanced +" + change_p + "%, but with more than half of all " + subtitle + " stocks (" + (100 - adv) + "%) that declined.";
            
            for (var i = data[types[0]].length - 2; i > 0; i--)
            {
                var benchmark_gainX = rounding((data[types[0]][i][1] / data[types[0]][i - 1][1] - 1) * 100, 100);
                if (benchmark_gainX > 0 && data[types[1]][i][1] < 50)
                {
                    comment = "For " + (data[types[0]].length - i) + " days in a row, " + types[0] + " advanced, but with more than half of all " + subtitle + " stocks that declined.";
                }
                else
                    break;
            }
        }


        if (adv < 33)
        {
            var sincetxt = "";
            var minSince = data[types[1]][data[types[1]].length - 1][0];
            var j = data[types[1]].length - 2;
            for (j = data[types[1]].length - 2; j >= 0 ; j--)
            {
                var advtmp = data[types[1]][j][1];
                if (adv > advtmp)
                {
                    minSince = data[types[1]][j][0];
                    break;
                }
            }

            for(var i = 24; i > 0; i--)
            {
                var TotalDays = (data[types[1]][data[types[1]].length - 1][0] - minSince) / 86400000;
                if (TotalDays >= i * 30.5)
                {
                    var approx = "";
                    var mid = (TotalDays % 30.5) / 30.5;
                    if (j > 0)
                    {
                        if (mid > .75)
                        {
                            i++;
                            approx = "nearly ";
                        }
                        else if (mid > 0.25 && mid < 0.75)
                            approx = "more than ";
                    }

                    if (i == 24)
                        sincetxt = "The " + adv + "% advance is the worst in " + approx + "2 years";
                    else if (i == 12)
                        sincetxt = "The " + adv + "% advance is the worst in " + approx + "1 year";
                    else if (i == 1)
                        sincetxt = "The " + adv + "% advance is the worst in " + approx + "1 month";
                    else
                        sincetxt = "The " + adv + "% advance is the worst in " + approx + i + " months";

                    break;
                }
            }

            if (sincetxt != "")
                tmp = 6;
                
            comment += comment == "" ? sincetxt : (" " + sincetxt);
        }
        else if (adv > 66)
        {
            var sincetxt = "";
            var maxSince = data[types[1]][data[types[1]].length - 1][0];
            var j = data[types[1]].length - 2;
            for (j = data[types[1]].length - 2; j >= 0 ; j--)
            {
                var advtmp = data[types[1]][j][1];
                if (adv < advtmp)
                {
                    maxSince = data[types[1]][j][0];
                    break;
                }
            }

            for(var i = 24; i > 0; i--)
            {
                var TotalDays = (data[types[1]][data[types[1]].length - 1][0] - maxSince) / 86400000;
                if (TotalDays >= i * 30.5)
                {
                    var approx = "";
                    var mid = (TotalDays % 30.5) / 30.5;
                    if (j > 0)
                    {
                        if (mid > .75)
                        {
                            i++;
                            approx = "nearly ";
                        }
                        else if (mid > 0.25 && mid < 0.75)
                            approx = "more than ";
                    }
                    
                    if (i == 24)
                        sincetxt = "The " + adv + "% advance is the best in " + approx + "2 years";
                    else if (i == 12)
                        sincetxt = "The " + adv + "% advance is the best in " + approx + "1 year";
                    else if (i == 1)
                        sincetxt = "The " + adv + "% advance is the best in " + approx + "1 month";
                    else
                        sincetxt = "The " + adv + "% advance is the best in " + approx + i + " months";

                    break;
                }
            }
            
            if (sincetxt != "")
                tmp = 1;
                
            comment += comment == "" ? sincetxt : (" " + sincetxt);
        }
            
            
        if (tmp == 1)
            TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
        else if (tmp == 6)
            TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
        else
            return "";

        return TAcommentTXT.join("") + "</table><br>";
    }
    else
        return "";
}
