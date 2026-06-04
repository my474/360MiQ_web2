<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/annotations.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<figure class="highcharts-figure">
  <div id="parent-container">
    <div id="play-controls">
      <button id="play-pause-button" class="fa fa-play" title="play"></button>
      <input id="play-range" type="range" value="1973" min="1973" max="2022" />
    </div>
    <div id="container"></div>
  </div>
</figure>
<pre id="csv" style="display:none">
# Data from RIAA
year	LpEp	Vinyl	Cassette	CD	Downloads	Streams
1973	1.2	0.19	0.076
1974	1.4	0.194	0.087
1975	1.5	0.212	0.099
1976	1.7	0.245	0.146
1977	2.2	0.245	0.25
1978	2.5	0.26	0.45
1979	2.1	0.354	0.581
1980	2.2	0.25	0.705
1981	2.3	0.256	1.1
1982	1.9	0.283	1.4	0
1983	1.7	0.269	1.8	0.017
1984	1.5	0.299	2.4	0.103
1985	1.3	0.281	2.4	0.39
1986	0.983	0.228	2.5	0.93
1987	0.793	0.203	3	1.6
1988	0.532	0.18	3.4	2.1
1989	0.22	0.116	3.3	2.6
1990	0.086	0.094	3.5	3.5
1991	0.029	0.064	3	4.3
1992	0.014	0.066	3.1	5.3
1993	0.011	0.051	2.9	6.5
1994	0.018	0.047	3	8.5
1995	0.025	0.047	2.3	9.4
1996	0.036	0.048	1.9	9.9
1997	0.033	0	1.5	9.9
1998	0.034		1.4	11.4
1999	0.032		1.1	12.8
2000	0.028		0.626	13.2
2001	0.027		0.363	12.9
2002	0.021		0.21	12
2003	0.022		0.108	11.2	0
2004	0.019		0.023	11.4	0.138	0
2005	0.014			10.5	0.921	0.149
2006	0.016			9.4	1.631	0.206
2007	0.023			7.5	2.408	0.234
2008	0.057			5.5	2.612	0.321
2009	0.064			4.3	2.647	0.362
2010	0.089			3.4	2.62	0.461
2011	0.119			3.1	2.876	0.654
2012	0.161			2.5	2.946	1.033
2013	0.211			2.1	2.898	1.454
2014	0.244			1.8	2.5	1.827
2015	0.333			1.4	2.3	2.375
2016	0.355			1.1	1.769	3.818
2017	0.389			1	1.318	5.459
2018	0.419			0.696	0.945	7.255
2019	0.48			0.931	0.775	8.853
2020	0.644			0.483	0.622	10.078
2021	1			0.584	0.538	12.509
</pre>

<style>
@import url("https://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css");

figure {
    margin: 0;
}

#play-controls {
    max-width: 600px;
    margin: 1em auto;
}

#container {
    height: 800px;
    max-width: 1000px;
    margin: 0 auto;
}

#csv {
    display: none;
}

#play-pause-button {
    margin-left: 10px;
    width: 50px;
    height: 50px;
    cursor: pointer;
    border: 1px solid rgba(2, 117, 255, 1);
    border-radius: 25px;
    color: white;
    background-color: rgba(2, 117, 255, 1);
    transition: background-color 250ms;
}

#play-pause-button:hover {
    background-color: rgba(2, 117, 255, 0.5);
}

#play-range {
    transform: translateY(2.5px);
    width: calc(100% - 90px);
    background: #f8f8f8;
}
</style>

<script>
const btn = document.getElementById('play-pause-button'),
    input = document.getElementById('play-range'),
    startYear = 1973,
    endYear = 2021;

// General helper functions
const arrToAssociative = arr => {
    const tmp = {};
    arr.forEach(item => {
        tmp[item[0]] = item[1];
    });

    return tmp;
};

function getSubtitle() {
    return `<span style='font-size: 36px;font-weight:bold;color:grey'>${input.value}</span>`;
}

const formatRevenue = [];

const chart = Highcharts.chart('container', {
    chart: {
        events: {
            // Some annotation labels need to be rotated to make room
            load: function () {
                const labels = this.annotations[0].labels;
                labels
                    .find(a => a.options.id === 'vinyl-label')
                    .graphic.attr({
                        rotation: -20
                    });
                labels
                    .find(a => a.options.id === 'cassettes-label')
                    .graphic.attr({
                        rotation: 20
                    });
            },
            render: function() {
                this.legend.render();
            },
        },
        type: 'area',
        marginTop: 46,
        animation: {
            duration: 200,
            easing: t => t
        },
        style: {
            fontFamily: 'Montserrat'
        }
    },
    
    credits: {
        enabled: true,
        text: '360MiQ.com',
        href: '',
        style: {
            fontSize: '20px',
            fontWeight: 'bold',
            cursor: 'arrow',
            color: 'lightgrey'
        },
        position: {
            align: 'center',
            //x: 100,
            y: -400
        }
    },
        
    title: {
        text: 'Performance Race Chart'
    },
    subtitle: {
        text: getSubtitle(),
        floating: true,
        align: 'left',
        verticalAlign: 'middle',
        x: 80,//-100,
        y: -293
    },
    data: {
        csv: document.getElementById('csv').innerHTML,
        itemDelimiter: '\t',
        complete: function (options) {
            for (let i = 0; i < options.series.length; i++) {
                formatRevenue[i] = arrToAssociative(options.series[i].data);
                options.series[i].data = null;
            }
        }
    },
    xAxis: {
        allowDecimals: false,
        min: startYear,
    },
    yAxis: [{
        reversedStacks: false,
        title: {
            text: 'Performance %'
        },
        labels: {
            format: '{text}'
        },
        plotLines: [{
            color: '#c9c9c9', // Color value
            //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
            value: 0, // Value of where the line will appear
            width: 4, // Width of the line    
            zIndex: 3
        }]
    },{
    		linkedTo: 0,
    		opposite: true,
        reversedStacks: false,
        title: {
            text: 'Performance %'
        },
        labels: {
            format: '{text}'
        }
    }],
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
                var performance = this.processedYData[this.processedYData.length - 1];
                var sign = '';

                if(performance > 0)
                      sign = "+";
                    else if (performance === 0)
                        sign = "\u00B1";

                    if (performance < 0)
                    {
                        return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:red">' + performance + '%</span>';
                    }
                    else if (performance === null || isNaN(performance))
                    {
                        return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">-</span>';
                    }
                    else
                    {
                        return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">' + sign + performance + '%</span>';
                    }
            }
        },
        
    tooltip: {
        shared: true,
        split: false,
        useHTML: true,
        //valueDecimals: 4,
        formatter: function (tooltip) {
                var s = '<span style="font-size:10px">' + this.x + '</span>';
                            
                /*this.points.forEach(function(point) {
                    var y = point.y;
                    y = Math.round( point.y * 100) / 100;
                        
                    s = s + '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + ': ' + '<b>' + y + '</b>';
                });
    
                return s;*/
                                        
                var sortedPoints = this.points.sort(function(a, b){
                    return (a.y < b.y ? -1 : (a.y > b.y ? 1 : 0));
                });
                
                sortedPoints.reverse();
                
                sortedPoints.forEach(function(point) {
                    var performance = point.y;
                    if (performance < 0)
                        s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + ': <span style="color:red;font-weight:bold">'+ performance + '%</span>';
                    else if (performance > 0)
                        s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + ': <b>+'+ performance + '%</b>';
                    else
                        s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + ': <b>\u00B1'+ performance + '%</b>';
                });
                
                return s;
                
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

        },

    },
    plotOptions: {
        area: {
            stacking: null,
            pointStart: startYear,
            marker: {
                enabled: false
            },
            fillColor: '#00000000'
        },
        series: {
            lineWidth: 3,
            dataLabels: {
                enabled: true,
                align: 'right',
                x: 24,
                formatter: function() {
                  if (this.point.index == this.series.points.length - 1) {
                    var fontsize = '';
                    //if (this.series.index == 5)
                      fontsize = ';font-size:14';
                
                    return '<span style="color:' + this.point.color + fontsize + '">'+this.series.name+'</span> ';
                  } else {
                    return null;
                  }
                },
                crop: false,
                overflow: false,
                allowOverlap: true
            },
        }
    },
    annotations: [{
        labelOptions: {
            borderWidth: 0,
            backgroundColor: undefined,
            verticalAlign: 'middle',
            allowOverlap: true,
            style: {
                pointerEvents: 'none',
                opacity: 0,
                transition: 'opacity 500ms'
            }
        },
        labels: [{
            text: 'Vinyl',
            verticalAlign: 'top',
            point: {
                x: 1975,
                xAxis: 0,
                y: 1.45,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#000'
            },
            id: 'vinyl-label'
        },
        {
            text: 'LP-EP',
            point: {
                x: 1980,
                xAxis: 0,
                y: 0.2,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#ffffff'
            },
            id: 'lpep-label'
        },
        {
            text: 'Cass',
            point: {
                x: 1987,
                xAxis: 0,
                y: 2.6,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#ffffff'
            },
            id: 'cassettes-label'
        },
        {
            text: 'CD',
            point: {
                x: 1999,
                xAxis: 0,
                y: 6,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#ffffff'
            },
            id: 'cd-label'
        },
        {
            text: 'DL',
            point: {
                x: 2011,
                xAxis: 0,
                y: 4,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#ffffff'
            },
            id: 'dl-label'
        },
        {
            text: 'Strm',
            point: {
                x: 2018,
                xAxis: 0,
                y: 5,
                yAxis: 0
            },
            style: {
                fontSize: '0em',
                color: '#ffffff'
            },
            id: 'streams-label'
        }]
    }],

    responsive: {
        rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                title: {
                    align: 'left'
                },
                subtitle: {
                    y: -150,
                    x: -20
                },
                yAxis: {
                    labels: {
                        align: 'left',
                        x: 0,
                        y: -3
                    },
                    tickLength: 0,
                    title: {
                        align: 'high',
                        reserveSpace: false,
                        rotation: 0,
                        textAlign: 'left',
                        y: -20
                    }
                }
            }
        }]
    }
});

function pause(button) {
    button.title = 'play';
    button.className = 'fa fa-play';
    clearTimeout(chart.sequenceTimer);
    chart.sequenceTimer = undefined;
}

function update() {
    chart.update(
        {
            subtitle: {
                text: getSubtitle()
            },
        },
        false,
        false,
        false
    );

    const series = chart.series,
        labels = chart.annotations[0].labels,
        yearIndex = input.value - startYear,
        dataLength = series[0].options.data.length;

    // If slider moved back in time
    if (yearIndex < dataLength - 1) {
        for (let i = 0; i < series.length; i++) {
            const seriesData = series[i].data.slice(0, yearIndex);
            series[i].setData(seriesData, false);
        }
    }

    // If slider moved forward in time
    if (yearIndex > dataLength - 1) {
        const remainingYears = yearIndex - dataLength;
        for (let i = 0; i < series.length; i++) {
            for (let j = input.value - remainingYears; j < input.value; j++) {
                series[i].addPoint([formatRevenue[i][j]], false);
            }
        }
    }

    // Add current year
    for (let i = 0; i < series.length; i++) {
        const newY = formatRevenue[i][input.value];
        series[i].addPoint([newY], false);
    }

    labels.forEach(label => {
        label
            .graphic
            .css({
                opacity: input.value >= label.options.point.x | 0
            });
    });

    chart.redraw();

    input.value = parseInt(input.value, 10) + 1;

    if (input.value > endYear) {
        // Auto-pause
        pause(btn);
    }
}

function play(button) {
    // Reset slider at the end
    if (input.value > endYear) {
        input.value = startYear;
    }
    button.title = 'pause';
    button.className = 'fa fa-pause';
    chart.sequenceTimer = setInterval(function () {
        update();
    }, 200);
}

btn.addEventListener('click', function () {
    if (chart.sequenceTimer) {
        pause(this);
    } else {
        play(this);
    }
});

play(btn);

// Trigger the update on the range bar click.
input.addEventListener('input', update);
</script>