function scoreGaugeZoneLabelStyle(fontSize) {
    return {
        color: '#ffffff',
        fill: '#ffffff',
        fontWeight: 'bold',
        textOutline: '1px #000000',
        textShadow: '0px 1px 1px black',
        fontSize: fontSize
    };
}

function forceScoreGaugeZoneLabels(chart) {
    if (!chart || !chart.container || !chart.container.querySelectorAll) {
        return;
    }

    var labels = chart.container.querySelectorAll(
        '.highcharts-pie-series.highcharts-data-labels text, ' +
        '.highcharts-pie-series.highcharts-data-labels tspan, ' +
        '.highcharts-series-0.highcharts-data-labels text, ' +
        '.highcharts-series-0.highcharts-data-labels tspan'
    );

    for (var i = 0; i < labels.length; i++) {
        var label = labels[i];
        if (label.removeAttribute) {
            label.removeAttribute('data-theme-label-patched');
        }
        if (label.classList && label.classList.contains('highcharts-text-outline')) {
            label.setAttribute('fill', '#000000');
            label.setAttribute('stroke', '#000000');
            continue;
        }

        label.setAttribute('fill', '#ffffff');
        label.style.color = '#ffffff';
        label.style.fill = '#ffffff';
        label.style.stroke = '#000000';
        label.style.strokeWidth = '2px';
        label.style.strokeLinejoin = 'round';
        label.style.paintOrder = 'stroke';
    }
}

function mscore(stockcode, value, industryValue, mrq) {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var radius = '100%';
    if (value === null)
        radius = '0%';
        
    Highcharts.setOptions({
     //colors: ['#ED561B', '#DDDF00', '#50B432']
     colors: ['#50B432', '#ED561B']
    });
    Highcharts.setOptions({
        colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {
                    cx: 0.5,
                    cy: 0.5,
                    r: 0.4
                },
                stops: [
                    [0, Highcharts.color(color).brighten(-0.3).get('rgb')], // darken
                    [0.2, Highcharts.color(color).brighten(-0.15).get('rgb')], // darken
                    [1, color]
                ]
            };
        })
    });    
    
    var border = -2.22;
    var min = -5;
    var max = 0.56;
    if (value < min)
    {
        //min = rounding(value * 1.25, 100);
        min = value;
        max = border * 2 - min;
    }
    else if (value > max)
    {
        //max = rounding(value * 1.25, 100);
        max = value;
        min = border * 2 - max;
        
    }
        
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: 'mscorecontainer',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false,
            style: {
                fontFamily: 'Montserrat'
            },
            events: {
                render: function () {
                    forceScoreGaugeZoneLabels(this);
                }
            }
        },
        title: {
            text: 'Beneish M\u2011Score',
            align: 'center',
            verticalAlign: 'top',
            y: 10
        },
        subtitle: {
            useHTML: true,
            style: {
                fontSize: '12px',
                color: value === null ? (isDark ? '#999999' : '#666666') : (isDark ? '#ffffff' : '#333333'),
                fontWeight: value === null ? 'bold' : 'normal'
            },
            text: value === null ? 'No data to display' : mrq,
            align: 'center',
            verticalAlign: 'bottom',
            y: value === null ? -64 : 5
        },
        lang: {
            noData: ""
        },
        exporting: { enabled: false },
        credits: {
                enabled: false
        },
        tooltip: {
        	enabled: false,
        },
        pane: {
            center: ['50%', '64.5%'],
            size: '66%',
            startAngle: -90,
            endAngle: 90,
            background: {
            	borderWidth: 0,
                backgroundColor: 'none',
                innerRadius: '50%',
                outerRadius: '100%',
                shape: 'arc'
            }
        },
		yAxis: [{
            lineWidth: 0,
	        min: min,
	        max: max,
            gridLineWidth: 0,
            minorGridLineWidth: 0,
            minorTickInterval: null,
            minorTickLength: 0,
            tickLength: 0,
            tickWidth: 0,
            tickPositions: [border],
            labels: {
                step: 1,
                distance: 48,
                rotation: 'auto',
                style: {
                  color: isDark ? '#e8e8e8' : '#333333',
                  fontSize: '12px'
                }
            },
            title: {
                text: '', //'<div class="gaugeFooter">46% Rate</div>',
                useHTML: true,
                y: 80
            },
            /*plotBands: [{
            	from: 0,
            	to: 46,
            	color: 'pink',
            	innerRadius: '90%',
            	outerRadius: '0%'
            },{
            	from: 46,
            	to: 100,
            	color: 'tan',
            	innerRadius: '90%',
            	outerRadius: '0%'
            }],*/
            pane: 0,
	        
	    }],

        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: true,
                    distance: -50,
                    x: 0,
                    y: -40,
                    style: scoreGaugeZoneLabelStyle('9px'),
                },
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '69%'],
                size: '130%',
            },
            gauge: {
	    		dataLabels: {
	    			enabled: value === null? false : true
	    		},
	    		pivot: {
                    backgroundColor: '#777777',
                    radius: value === null? 0 : 4
                }
	    	}
        },
     
        series: [{
            type: 'pie',
            name: '',
            innerSize: '50%',
            data: [
                ['Non Manipulator',   -2.22 - min],
                ['Manipulator', max + 2.22],
            ],
            states: {
                hover: { enabled: false },
                inactive: {
                    opacity: 1
                }
            },
        },{
            type: 'gauge',
            data: [value],
            dial: {
                radius: radius,
				backgroundColor: '#777777',
				color: '#777777',
				borderWidth: 1,
				borderColor: 'white',
				baseWidth: 5,
				topWidth: 1,
				baseLength: '25%', // of radius
				rearLength: '-12%',
            },
            states: { 
                inactive: {
                    opacity: 1
                }
            },
        }],
    });
    forceScoreGaugeZoneLabels(chart);
    
    var animate = true;
    chart.container.onmouseover = function() {
        if (!chart.renderer.forExport && animate) {
            var i0cnt = 0, i1cnt = 0, i2cnt = 0;
            var i0 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i0cnt > 2)
                    clearInterval(i0);
                else
                    point.update(rounding(point.y + .53, 100));
                i0cnt++;
            }, 200);

            var i1 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i1cnt > 2)
                    clearInterval(i1);
                else                
                    point.update(rounding(point.y - 1.06, 100));
                i1cnt++;
            }, 300);   

            var i2 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i2cnt > 2)
                    clearInterval(i2);
                else
                    point.update(rounding(point.y + .53, 100));
                i2cnt++;
            }, 400);
            animate = false;
        }        
    }
    chart.container.onmouseleave = function() {
        animate = true;
    }     
}

function zscore(stockcode, value, industryValue, mrq) {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var radius = '100%';
    if (value === null)
        radius = '0%';
        
    Highcharts.setOptions({
     //colors: ['#ED561B', '#DDDF00', '#50B432']
     colors: ['#ED561B', '#999999', '#50B432']
    });
    Highcharts.setOptions({
        colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {
                    cx: 0.5,
                    cy: 0.5,
                    r: 0.4
                },
                stops: [
                    [0, Highcharts.color(color).brighten(-0.3).get('rgb')], // darken
                    [0.2, Highcharts.color(color).brighten(-0.15).get('rgb')], // darken
                    [1, color]
                ]
            };
        })
    });    
    
    var min = 0;
    var max = 10;
    if (value < min)
        //min = rounding(value * 1.25, 100);
        min = value;
    else if (value > max)
        //max = rounding(value * 1.25, 100);
        max = value;
        
    var chart = new Highcharts.Chart({
        chart: {
            renderTo: 'zscorecontainer',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false,
            style: {
                    fontFamily: 'Montserrat'
                },
            events: {
                render: function () {
                    forceScoreGaugeZoneLabels(this);
                }
            }
        },
        title: {
            text: 'Altman Z-Score',
            align: 'center',
            verticalAlign: 'top',
            y: 10
        },
        subtitle: {
            useHTML: true,
            style: {
                fontSize: '12px',
                color: value === null ? (isDark ? '#999999' : '#666666') : (isDark ? '#ffffff' : '#333333'),
                fontWeight: value === null ? 'bold' : 'normal'
            },
            text: value === null ? 'No data to display' : mrq,
            align: 'center',
            verticalAlign: 'bottom',
            y: value === null ? -64 : 5
        },
        lang: {
            noData: ""
        },
        exporting: { enabled: false },
        credits: {
                enabled: false
        },
        tooltip: {
        	enabled: false,
        },
        pane: {
            center: ['50%', '64.5%'],
            size: '66%',
            startAngle: -90,
            endAngle: 90,
            background: {
            	borderWidth: 0,
                backgroundColor: 'none',
                innerRadius: '50%',
                outerRadius: '100%',
                shape: 'arc'
            }
        },
		yAxis: [{
            lineWidth: 0,
	        min: min,
	        max: max,
            gridLineWidth: 0,
            minorGridLineWidth: 0,
            minorTickInterval: null,
            minorTickLength: 0,
            tickLength: 0,
            tickWidth: 0,
            tickPositions: [min, 1.81, 2.99, max],
            labels: {
                step: 1,
                distance: 48,
                rotation: 'auto',
                style: {
                  color: isDark ? '#e8e8e8' : '#333333',
                  fontSize: '12px'
                }
            },
            title: {
                text: '', //'<div class="gaugeFooter">46% Rate</div>',
                useHTML: true,
                y: 80
            },
            /*plotBands: [{
            	from: 0,
            	to: 46,
            	color: 'pink',
            	innerRadius: '90%',
            	outerRadius: '0%'
            },{
            	from: 46,
            	to: 100,
            	color: 'tan',
            	innerRadius: '90%',
            	outerRadius: '0%'
            }],*/
            pane: 0,
	        
	    }],
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: true,
                    distance: -40,
                    x: -10,
                    y: -12,
                    style: scoreGaugeZoneLabelStyle('10px')
                },
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '69%'],
                size: '130%',
            },
            gauge: {
	    		dataLabels: {
	    			enabled: value === null? false : true
	    		},
	    		pivot: {
                    backgroundColor: '#777777',
                    radius: value === null? 0 : 4
                }
	    	}
        },
     
        series: [{
            type: 'pie',
            name: '',
            innerSize: '50%',
            data: [
                ['Distress',   (1.81 - min)/(max - min) * 100],
                ['Grey',       (2.99 - 1.81)/(max - min) * 100],
                ['Safe', (max - 2.99)/(max - min) * 100],
            ],
            states: {
                hover: { enabled: false },
                inactive: {
                    opacity: 1
                }
            },
        },{
            type: 'gauge',
            data: [value],
            dial: {
                radius: radius,
				backgroundColor: '#777777',
				color: '#777777',
				borderWidth: 1,
				borderColor: 'white',
				baseWidth: 5,
				topWidth: 1,
				baseLength: '25%', // of radius
				rearLength: '-12%',
            },
            states: { 
                inactive: {
                    opacity: 1
                }
            },
        }],
    });
    forceScoreGaugeZoneLabels(chart);
    
    var animate = true;
    chart.container.onmouseover = function() {
        if (!chart.renderer.forExport && animate) {
            var i0cnt = 0, i1cnt = 0, i2cnt = 0;
            var i0 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i0cnt > 2)
                    clearInterval(i0);
                else
                    point.update(rounding(point.y + .53, 100));
                i0cnt++;
            }, 200);

            var i1 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i1cnt > 2)
                    clearInterval(i1);
                else                
                    point.update(rounding(point.y - 1.06, 100));
                i1cnt++;
            }, 300);   

            var i2 = setInterval(function () {
                var point = chart.series[1].points[0];
                if (i2cnt > 2)
                    clearInterval(i2);
                else
                    point.update(rounding(point.y + .53, 100));
                i2cnt++;
            }, 400);
            animate = false;
        }        
    }
    chart.container.onmouseleave = function() {
        animate = true;
    }
}

function fscoreTheme() {
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        dark: dark,
        chartBg: dark ? '#1e1e32' : '#ffffff',
        plotBg: dark ? '#2a2a3e' : '#EbF5Fb',
        plotBorder: dark ? '#3a3a5e' : '#EbF5Fb',
        axisLine: dark ? '#3a3a5e' : '#EfF9Ff',
        axisLabel: dark ? '#cccccc' : 'grey',
        title: dark ? '#e8e8e8' : '#333333',
        subtitle: dark ? '#b0b0b0' : '#555555',
        tooltipBg: dark ? 'rgba(42,42,62,1)' : 'rgba(255,255,255,1)',
        tooltipText: dark ? '#e8e8e8' : '#333333',
        tick: dark ? '#666699' : '#9f9f9f',
        legend: dark ? '#cccccc' : '#606060'
    };
}

function fscore(stockcode, value, industryValue, mrq) {
    //var value = valuearray.reduce(function(valuearray, b) { return valuearray + b; }, 0);
    //var oldvalue = oldvaluearray.reduce(function(oldvaluearray, b) { return oldvaluearray + b; }, 0);
    var t = fscoreTheme();
    var scoreLabel = {
        0: '0 worst',
        9: 'best 9'
    };
    
    //$(document).ready(function() {
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'fscorecontainer',
                defaultSeriesType: 'bar',
                plotBorderWidth: 0,
                backgroundColor: t.chartBg,
                plotBackgroundColor: t.plotBg,
                plotBorderColor: t.plotBorder,
                plotShadow: false,
                //spacingBottom: 43,
                height: 162,
                style: {
                    fontFamily: 'Montserrat'
                }

            },
            credits: {
                enabled: false
            },
            xAxis: {
                lineColor: t.axisLine,
                labels: {
                    enabled: false
                },
                tickLength: 0
            },
            title: {
                text: 'Piotroski F\u2011Score',
                style: {
                    color: t.title
                }
            },
            subtitle: {
                style: {
                    fontSize: '12px',
                    color: t.subtitle
                },
                text: mrq,
                verticalAlign: 'bottom',
                y: 25
            },
            yAxis: {
                title: {
                    text: null
                },
                gridLineWidth: 0,
                labels: {
                    y: 14,
                  	style: {
                        color: t.axisLabel,
                        fontSize:'12px'
                    },
                    formatter: function() {
                        var value = scoreLabel[this.value];
                        return value !== 'undefined' ? value : this.value;
                    }
                },
                min: 0,
                max: 9,
                tickInterval: 9,
                tickWidth: 1,
                tickLength: 4,
                minorTickInterval: 1,
                minorTickLength: 4,
                minorTickWidth: 1,
                minorGridLineWidth: 0,
                tickColor: t.tick,
                minorTickColor: t.tick,
            },
            
            tooltip: {
                enabled: false,
                useHTML: true,
                style: {
                    fontSize: '8px',
                    color: t.tooltipText
                },
                padding: 3,
                backgroundColor: t.tooltipBg,
                formatter: function () {
                    var array;
                    if (this.series.name == stockcode)
                        array = valuearray;
                    else
                        array = oldvaluearray;
                    
                    var a = array[0] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var b = array[1] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var c = array[2] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var d = array[3] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var e = array[4] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var f = array[5] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var g = array[6] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var h = array[7] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    var i = array[8] == 1? '<font color="green"><b>✓</b></font>' : '<font color="red"><b>✗</b></font>';
                    
                    return '<font color="'+ this.series.color.stops[0][1] + '"><b>' + this.series.name + ' components</b></font><br>'+ a +' +ve net income, ' + b + ' +ve return on assets, ' + c + ' +ve operating cash flow,<br>' + d + ' Operating cash flow > net income, ' + e + ' Lower long term debt ratio,<br>' + f + ' Higher current ratio, ' + g + ' No new shares issued,<br>' + h + ' Higher gross margin, ' + i + ' Higher asset turnover ratio';
                },
            },
            
            legend: {
                y: 0,
                itemMarginTop: 0,
                padding: 0,
                margin: 0,
                enabled: true,
                itemStyle: {
                  font: 'Montserrat',
                  fontWeight: 600,
                  color: t.legend,
                },
                // symbol items below will be overridden by seasonality.js
                symbolWidth: 7,
                symbolHeight: 11,
                symbolRadius: 0,
                squareSymbol: false,
        	    labelFormatter: function() {
                    return '<span style="color:' + this.color + '">' + this.name + '</span>';
                }
             },
        
            plotOptions: {
                series: {
                    pointPadding: 0,
                    groupPadding: 0,
                    borderWidth: 0,
                    shadow: false
                }
            },
            
            exporting: { enabled: false },
            series: [{
                name: stockcode,
                borderColor: '#7070B8',
                borderRadius: 0,
                borderWidth: 0,
              	dataLabels: {
                    enabled: true,
                    align: 'center',
                    format: '{point.y}',
                    y: 0,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                },
                color: {
                    linearGradient: {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 0
                    },
                    stops: [[0, '#F1AED6'],
                            [1, '#e19Ec6']]
                },
                //pointWidth: 20,
                data: [value],
                states: {
                    inactive: {
                        opacity: 1
                    }
                },
              	/*events: {
                    legendItemClick: function (e) {
                        e.preventDefault();
                    }
                }*/
            },
            {
                name: 'Industry Median',
                borderColor: '#7070B8',
                borderRadius: 0,
                borderWidth: 0,
              	dataLabels: {
                    enabled: true,
                    align: 'center',
                    format: '{point.y}',
                    y: 0,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                },
                color: {
                    linearGradient: {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 0
                    },
                    stops: [[0, '#AED6F1'],
                            [1, '#9Ec6e1']]
                },
                //pointWidth: 6,
                data: [industryValue],
                states: {
                    inactive: {
                        opacity: 1
                    }
                },
              	/*events: {
                    legendItemClick: function (e) {
                        e.preventDefault();
                    }
                }*/
            }]
        });

        if (!window._fscoreThemeBound) {
            window._fscoreThemeBound = true;
            document.documentElement.addEventListener('themechange', function() {
                var args = window._fscoreArgs;
                if (args) {
                    var old = window._fscoreChart;
                    if (old && old.destroy) {
                        try { old.destroy(); } catch(e) {}
                    }
                    var container = document.getElementById('fscorecontainer');
                    if (container) {
                        container.innerHTML = '';
                    }
                    setTimeout(function() {
                        window._fscoreChart = fscore.apply(null, args);
                    }, 30);
                }
            });
        }
        window._fscoreChart = chart;
        window._fscoreArgs = [stockcode, value, industryValue, mrq];
    
        return chart;
    //});
}
