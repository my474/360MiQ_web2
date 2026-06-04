// DO NOT DELETE
/*function gaugevalue(midbreadth, longbreadth, midAvg, midSTDEV, longAvg, longSTDEV)
{
    var trendgaugevalue = -1;
    
    if (midbreadth === null || midbreadth === undefined || longbreadth === null || longbreadth === undefined)
        return trendgaugevalue;
        
    if (longSTDEV < 25)
    {
        longSTDEV *= 1.2;
        if (longSTDEV > 25)
            longSTDEV = 25;
    }
    var middlelimitLong = longAvg;
    var upperlimitLong = longAvg + longSTDEV;
    var lowerlimitLong = longAvg - longSTDEV;
    if (middlelimitLong > 50 && longSTDEV < 25) // no need to do (middlelimitLong < 50, upperlimitLong) as fear is more than greed
    {
        lowerlimitLong -= (middlelimitLong - 50) * 2;
    }
    
    if (midSTDEV < 25)
    {
        midSTDEV *= 1.2;
        if (midSTDEV > 25)
            midSTDEV = 25;
    }
    var middlelimit = midAvg;
    var upperlimit = midAvg + midSTDEV;
    var lowerlimit = midAvg - midSTDEV;
    if (middlelimit > 50 && midSTDEV < 25) // no need to do (middlelimit < 50, upperlimit) as fear is more than greed
    {
        lowerlimit -= (middlelimit - 50) * 2;
    }
    
    var midbreadthMA = ema0(midbreadth, 10);
    var midbreadthMA2 = ema0(midbreadth, 5);

    var longbreadthMA = ema0(longbreadth, 5);        
    // trendguage
    if (midbreadth !== null && longbreadth !== null && midbreadthMA !== null && longbreadthMA !== null &&
        midbreadth.length > 0 && longbreadth.length > 0 && midbreadthMA.length > 0 && longbreadthMA.length > 0)
    {
        var midbreadthvalue = midbreadth[midbreadth.length - 1];
        var longbreadthvalue = longbreadth[longbreadth.length - 1];
        midbreadthvalue = midbreadthvalue > upperlimit? upperlimit : midbreadthvalue < lowerlimit? lowerlimit : midbreadthvalue;
        longbreadthvalue = longbreadthvalue > upperlimit? upperlimit : longbreadthvalue < lowerlimit? lowerlimit : longbreadthvalue;
        trendgaugevalue = rangeconverter(midbreadthvalue, lowerlimit, upperlimit, 0, 90);
        if (longbreadthvalue > middlelimitLong || longbreadthvalue > 50)
        {
            percentage = rangeconverter(longbreadthvalue, middlelimitLong, upperlimitLong, 50, 100); // longbreadth  weighted 0.5 only
            trendgaugevalue *= percentage / 100;
            trendgaugevalue = midbreadthMA2[midbreadthMA2.length - 1] < midbreadthMA[midbreadthMA.length - 1] && longbreadthvalue < longbreadthMA[longbreadthMA.length - 1] && midbreadthvalue < midbreadthMA[midbreadthMA.length - 1]? 180 - trendgaugevalue : trendgaugevalue;
        }
        else
        {
            percentage = rangeconverter(longbreadthvalue, lowerlimitLong, middlelimitLong, 50, 100);
            trendgaugevalue *= percentage / 100;                    
            trendgaugevalue = midbreadthMA2[midbreadthMA2.length - 1] < midbreadthMA[midbreadthMA.length - 1] && longbreadthvalue < longbreadthMA[longbreadthMA.length - 1] && midbreadthvalue < midbreadthMA[midbreadthMA.length - 1]? 270 - trendgaugevalue : 270 + trendgaugevalue;
        }
    }
    
    return trendgaugevalue;
}*/


function trendgauge(container, value, valueIndustry, title, link, fontsize, pointer, benchmark){
        var radius = '80%';
        var radiusIndustry = '60%';
        var isGreyBG = false;
        var IndustryHasValue = true;
        var pivotradius = 4;
        if (value < 0)
        {
            radius = '0%'; // no needle
            value = undefined;
            isGreyBG = true;
        }
        if (valueIndustry < 0)
        {
            IndustryHasValue = false;
            radiusIndustry = '0%'; // no needle
            valueIndustry = undefined;
        }
        if (value === undefined && valueIndustry === undefined)
            pivotradius = 0;

        /* ---- detect dark mode for background ---- */
        var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        var gaugeBgColor = isDarkMode ? '#2a2a3e' : '#ffffff';

        var options = { chart: {
            style:{
              cursor: pointer,
              fontFamily: 'Montserrat'
            },
            type: 'gauge',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false,
            /*events: {
                click: function (event) {
                    alert(
                        this.name + ' clicked\n' +
                        'Alt: ' + event.altKey + '\n' +
                        'Control: ' + event.ctrlKey + '\n' +
                        'Meta: ' + event.metaKey + '\n' +
                        'Shift: ' + event.shiftKey
                    );
                    if (link != "#")
                        location.href = link;
                }
            }*/        
        },
                       
        plotOptions: {
            gauge: {
                dataLabels: {
                    borderColor: 'transparent'
                },
                pivot: {
                    backgroundColor: '#777777',
                    radius: pivotradius
                }
            }
        },
                       
        exporting: { 
            enabled: benchmark === '' ? false : true,
            buttons: {
                contextButton: {
                    menuItems: [
                        'printChart',
                        'separator',
                        'downloadPNG',
                        'downloadJPEG',
                        'downloadPDF',
                        'downloadSVG'
                    ]
                }
            }
        },

        tooltip: {
                enabled: false
        },
        
        legend: {
    	    enabled: link == '#'? true : false,
    	    labelFormatter: function() {
                return '<span style="color:' + this.color + '">' + this.name + '</span>';
            }
        },
    
        title: {
            text: benchmark === '' ? (link == '#' ? null : title) : 'Market Trend Gauge',
            style: {
                whiteSpace: 'nowrap',
            },            
        },

        subtitle: {
            text: benchmark === '' ? null : title,
            style: {
                whiteSpace: 'nowrap',
            },            
        },
        
        pane: {
            size: '103%',
            center: ['50%', link == '#'? '50%' : '45%'],
            startAngle: -90,
            endAngle: 270,
        },

        credits: {
            enabled: false
        },

        // the value axis
        yAxis: {
            lineWidth: 0,
            min: 0,
            max: 360,
            minorTicks: false,
            tickPositions: [0, 45, 90, 135, 180, 225, 270, 315],
            tickPixelInterval: 45,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#7f7f7f',
            labels: {
                style: {
                    'font-size': fontsize //'9.2px'
                },
                formatter: function() {
                                if(this.value === 0) return 'Up';
                                if(this.value == 45) return 'Strong Up';
                                if(this.value == 90) return 'Overbought';
                                if(this.value == 135) return 'Correction';
                                if(this.value == 180) return 'Down';
                                if(this.value == 225) return 'Strong Down';
                                if(this.value == 270) return 'Oversold';
                                if(this.value == 315) return 'Rebound';
                                return this.value;
                            },
            },
            /*title: {
                text: 'km/h'
            },*/
            plotBands: [{
                from: 0,
                to: 90,
                //color: '#55BF3B' // green
                  color: {
                    linearGradient: {x1: 0, x2: 1, y1: 0, y2: 0},
                    stops: [
                        [0, '#DDDF0D'], //yellow
                        [1, '#55BF3B'] //green
                    ]
                }
            }, {
                from: 90,
                to: 180,
               // color: '#008F00' // yellow
                 color: {
                linearGradient: {x1: 0, x2: 1, y1: 0, y2: 0},
                stops: [
                    [0, '#55BF3B'], //green
                    [1, '#DDDF0D'] //yellow
                ]
            }
            }, {
                from: 180,
                to: 270,
                //color: '#8F0000' // yellow
                color: {
                linearGradient: {x1: 0, x2: 0, y1: 0, y2: 1},
                stops: [
                    [0, '#DDDF0D'], //yellow
                    [1, '#DF5353']
                ]
            }
            }, {
                from: 270,
                to: 360,
                //color: '#8f0000' // yellow
                 color: {
                linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0},
                stops: [
                    [0, '#DF5353'],
                    [1, '#DDDF0D'] //yellow

                ]
            }
            }]
        },

        series: [{
            name: title,
            data: [value],
            dataLabels: {
                enabled: false
            },        
            tooltip: {
                valueSuffix: ''
            },
            color: '#777777',
            dial: {
                radius: radius,
				backgroundColor: '#777777',
				borderWidth: 0,
				baseWidth: 4,
				topWidth: 1,
				baseLength: '25%', // of radius
				rearLength: '-10%',
            },
            showInLegend: true,
            marker: {
                enabled: false
            }
        }]
};
    	
    var r, g, b, percent;
    if (value >= 0 && value < 90)
    {
        percent = value / 90;
        r = 221 - (221 - 85) * percent;
        g = 223 - (223 - 191) * percent;
        b = 13 + (59 - 13) * percent;
    }    
    else if (value >= 90 && value < 180)
    {
        percent = (value - 90) / 90;
        r = 85 + (221 - 85) * percent;
        g = 191 + (223 - 191) * percent;
        b = 59 - (59 - 13) * percent;
    }
    else if (value >= 180 && value < 270)
    {
        percent = (value - 180) / 90;
        r = 221 - (221 - 223) * percent;
        g = 223 - (223 - 83) * percent;
        b = 13 + (83 - 13) * percent;
    }    
    else if (value >= 270 && value <= 360)
    {
        percent = (value - 270) / 90;
        r = 223 + (221 - 223) * percent;
        g = 83 + (223 - 83) * percent;
        b = 83 - (83 - 13) * percent;
    }

    var background;
    if (!isGreyBG)
    {
        if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)){
            var r_str = Math.round(r).toString(16);
            var g_str = Math.round(g).toString(16);
            var b_str = Math.round(b).toString(16);
            if (Math.round(r) <= 15)
                r_str = "0" + Math.round(r).toString(16);
            if (Math.round(g) <= 15)
                g_str = "0" + Math.round(g).toString(16);
            if (Math.round(b) <= 15)
                b_str = "0" + Math.round(b).toString(16);    
            
            var firefoxcolor = "#" + r_str + g_str + b_str + "30";
            
            background = [{
                        borderWidth: 0,
        	            backgroundColor: {
                        radialGradient: { cx: 0.5,
                        cy: 0.5,
                        r: 0.43},
                        stops: [
                            [0, gaugeBgColor],
                            [0.1, gaugeBgColor],
                            //[0.99, "rgba("+Math.round(r)+","+Math.round(g)+","+Math.round(b)+", 0)"],
                            [0.99, firefoxcolor],
                            [1, gaugeBgColor],
                        ]
                        }
        	        }];
        }
        else
        {
            background = [{
                borderWidth: 0,
	            backgroundColor: {
                radialGradient: { cx: 0.5,
                cy: 0.5,
                r: 0.43},
                stops: [
                    [0, gaugeBgColor],
                    [0.1, gaugeBgColor],
                    [0.99, "rgba("+Math.round(r)+","+Math.round(g)+","+Math.round(b)+", 0)"],
                    [1, gaugeBgColor],
                ]
                }
	        }];
        }
    }
    else
    {
        if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)){
            background = [{
                        borderWidth: 0,
        	            backgroundColor: {
                        radialGradient: { cx: 0.5,
                        cy: 0.5,
                        r: 0.43},
                        stops: [
                            [0, gaugeBgColor],
                            [0.1, gaugeBgColor],
                            [0.99, "#aaaaaa30"],
                            [1, gaugeBgColor],
                        ]
                        }
        	        }];  
        }
        else
        {
            background = [{
                borderWidth: 0,
	            backgroundColor: {
                radialGradient: { cx: 0.5,
                cy: 0.5,
                r: 0.43},
                stops: [
                    [0, gaugeBgColor],
                    [0.1, gaugeBgColor],
                    [0.99, "rgba(153, 153, 153, 0)"],
                    [1, gaugeBgColor],
                ]
                }
	        }];
        }
    }
    options.pane.background = background;
    
    var chart = Highcharts.chart(container, options);
    /* store original pane background so theme toggle can preserve the inner gradient color */
    chart._gaugeBgStores = JSON.parse(JSON.stringify(options.pane.background));
    _gaugeCharts.push(chart);
    
    if (IndustryHasValue)
    {
        var color = '#79ABE0';
		chart.addSeries({
			name: benchmark === '' ? 'Industry Median' : benchmark,
			data: [valueIndustry],
			dataLabels: {
                enabled: false
            },        
            tooltip: {
                valueSuffix: ''
            },
            color: color,
            dial: {
                radius: radiusIndustry,
				backgroundColor: color,
				borderWidth: 0,
				baseWidth: 5,
				topWidth: 1,
				baseLength: '25%', // of radius
				rearLength: '-13%',
            },
            showInLegend: true,
            marker: {
                enabled: false
            }
    	}, true);
    }

    var animate = true;
    chart.container.onmouseover = function() {
        if (!chart.renderer.forExport && animate) {
            var i0cnt = 0, i1cnt = 0, i2cnt = 0;
            var i0 = setInterval(function () {
                var point = chart.series[0].points[0];
                if (point !== undefined)
                {
                    if (i0cnt > 2)
                        clearInterval(i0);
                    else
                        point.update(point.y + 5);
                    i0cnt++;
                }
            }, 200);

            var i1 = setInterval(function () {
                var point = chart.series[0].points[0];
                if (point !== undefined)
                {
                    if (i1cnt > 2)
                        clearInterval(i1);
                    else                
                        point.update(point.y - 10);
                    i1cnt++;
                }
            }, 300);   

            var i2 = setInterval(function () {
                var point = chart.series[0].points[0];
                if (point !== undefined)
                {
                    if (i2cnt > 2)
                        clearInterval(i2);
                    else
                        point.update(point.y + 5);
                    i2cnt++;
                }
            }, 400);
            animate = false;
        }        
        if (pointer == 'pointer') // don't bold in Stock Info
            chart.update({
                title: {
                    style: {
                        fontWeight: 'bold'
                    }
                }
            });
    }
    chart.container.onmouseleave = function() {
        animate = true;
        chart.update({
            title: {
                style: {
                    fontWeight: 'normal'
                }
            }
        })
    }    
    chart.container.onclick = function() {
        if (link != "#")
            location.href = link;
    }

    return chart;
}

/* ---- collect gauge charts for live theme update ---- */
window._gaugeCharts = [];

/* ---- Update all gauge chart backgrounds on theme toggle ---- */
document.documentElement.addEventListener('themechange', function() {
  var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  var bgColor = isDark ? '#2a2a3e' : '#ffffff';
  _gaugeCharts.forEach(function(ch) {
    if (!ch || !ch._gaugeBgStores) return;
    /* deep-clone the stored background and replace outer stops */
    var bg = JSON.parse(JSON.stringify(ch._gaugeBgStores));
    bg.forEach(function(layer) {
      if (layer && layer.backgroundColor && layer.backgroundColor.stops) {
        layer.backgroundColor.stops.forEach(function(stop, i) {
          if (i !== 2) stop[1] = bgColor; // keep index 2 (the inner colored/grey stop)
        });
      }
    });
    ch.update({ pane: { background: bg } }, true, true);
  });
});