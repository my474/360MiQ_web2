/* ---- PolarChart colour helper ---- */
function polarTheme() {
  var dark = document.documentElement.getAttribute('data-theme') === 'dark';
  return {
    dark: dark,
    gridLineColor: dark ? '#3a3a5e' : '#ccccff',
    bandEven:      dark ? '#2a2a3e' : '#ffffff',
    bandOdd:       dark ? '#1e1e32' : '#f5f5ff'
  };
}

function polarComment(polarArray)
{
/*    var comment = "", strongcomment = "", goodcomment = "", mediocrecomment = "", weakcomment = "", NAcomment = "";
    var typeArray = ["technical", "valuation", "fundamental"];

    for(var i = 0; i < polarArray.length; i++)
    {
        if (polarArray[i] >=4)
        {
            if (strongcomment === "")
                strongcomment = "Strong " + typeArray[i];
            else
                strongcomment += ", " + typeArray[i];
        }
    }
    if (strongcomment !== "")
        strongcomment += ". ";
    if ((strongcomment.match(/,/g) || []).length > 0)
        strongcomment = strongcomment.substring(0,strongcomment.lastIndexOf(','))+' and'+strongcomment.substring(strongcomment.lastIndexOf(',')+1);
    for(i = 0; i < polarArray.length; i++)
    {
        if (polarArray[i] >= 3 && polarArray[i] < 4)
        {
            if (goodcomment === "")
                goodcomment = "Good " + typeArray[i];
            else
                goodcomment += ", " + typeArray[i];
        }
    }
    if (goodcomment !== "")
        goodcomment += ". ";
    if ((goodcomment.match(/,/g) || []).length > 0)
        goodcomment = goodcomment.substring(0,goodcomment.lastIndexOf(','))+' and'+goodcomment.substring(goodcomment.lastIndexOf(',')+1);
    for(i = 0; i < polarArray.length; i++)
    {
        if (polarArray[i] >= 2 && polarArray[i] <= 3)
        {
            if (mediocrecomment === "")
                mediocrecomment = "Mediocre " + typeArray[i];
            else
                mediocrecomment += ", " + typeArray[i];
        }
    }
    if (mediocrecomment !== "")
        mediocrecomment += ". ";
    if ((mediocrecomment.match(/,/g) || []).length > 0)
        mediocrecomment = mediocrecomment.substring(0,mediocrecomment.lastIndexOf(','))+' and'+mediocrecomment.substring(mediocrecomment.lastIndexOf(',')+1);
    for(i = 0; i < polarArray.length; i++)
    {
        if (polarArray[i] >= 0 && polarArray[i] < 2)
        {
            if (weakcomment === "")
                weakcomment = "Weak " + typeArray[i];
            else
                weakcomment += ", " + typeArray[i];
        }
    }
    if (weakcomment !== "")
        weakcomment += ". ";
    if ((weakcomment.match(/,/g) || []).length > 0)
        weakcomment = weakcomment.substring(0,weakcomment.lastIndexOf(','))+' and'+weakcomment.substring(weakcomment.lastIndexOf(',')+1);

    for(i = 0; i < polarArray.length; i++)
    {
        if (polarArray[i] < 0)
        {
            if (NAcomment === "")
                NAcomment = typeArray[i].charAt(0).toUpperCase() + typeArray[i].slice(1) + " not avaliable";
            else
                NAcomment = typeArray[i].charAt(0).toUpperCase() + typeArray[i].slice(1) + ", " + NAcomment.charAt(0).toLowerCase() + NAcomment.slice(1);
        }
    }
    if (NAcomment !== "")
        NAcomment += ". ";
    if ((NAcomment.match(/,/g) || []).length > 0)
        NAcomment = NAcomment.substring(0,NAcomment.lastIndexOf(','))+' and'+NAcomment.substring(NAcomment.lastIndexOf(',')+1);
*/
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var conclusion = "<br><span style=\"color:" + (isDark ? '#999999' : '#666666') + "; font-weight:600\"><u>Comment</u>:</span> ";
    //comment = strongcomment + goodcomment + mediocrecomment + weakcomment + NAcomment;
    var comment = '';
    if (polarArray[0] >= 4)
    {
        if (polarArray[1] <= 1 && polarArray[1] >= 0)
        {
            if (polarArray[2] <= 1 && polarArray[2] >= 0)
                comment += conclusion + "Trending well, but fundamental is not up to it and also expensive.";
            else if (polarArray[2] >= 2 && polarArray[2] <= 3)
                comment += conclusion + "Trending well along with average fundamental, but expensive.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Trending well along with good fundamental, but expensive.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Trending well, but expensive.";
        }
        else if (polarArray[1] >= 2 && polarArray[1] <= 3)
        {
            if (polarArray[2] <= 1 && polarArray[2] >= 0)
                comment += conclusion + "Trending well along with fair value, but fundamental is not up to it.";
            else if (polarArray[2] >= 2 && polarArray[2] <= 3)
                comment += conclusion + "Trending well along with fair value and average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Trending well along with good fundamental, but just fair value.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Trending well along with fair value.";
        }
        else if (polarArray[1] >= 4)
        {
            if (polarArray[2] < 2  && polarArray[2] >= 0)
                comment += conclusion + "Trending well along with good value, but fundamental is not up to it.";
            else if (polarArray[2] >= 2  && polarArray[2] <= 3)
                comment += conclusion + "Trending well along with good value, but just average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Trending well along with both good fundamental and value.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Trending well along with good value.";
        }
        else if (polarArray[1] < 0)
        {
            if (polarArray[2] < 2  && polarArray[2] >= 0)
                comment += conclusion + "Trending well, but fundamental is not up to it.";
            else if (polarArray[2] >= 2  && polarArray[2] <= 3)
                comment += conclusion + "Trending well along with average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Trending well along with good fundamental.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Trending well.";
        }
    }
    else if (polarArray[0] >= 2 && polarArray[0] <= 3)
    {
        if (polarArray[1] <= 1 && polarArray[1] >= 0)
        {
            if (polarArray[2] <= 1 && polarArray[2] >= 0)
                comment += conclusion + "Mediocre trend, but fundamental is not up to it and also expensive.";
            else if (polarArray[2] >= 2 && polarArray[2] <= 3)
                comment += conclusion + "Mediocre trend along with average fundamental, but expensive.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Mediocre trend along with good fundamental, but expensive.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Mediocre trend, but expensive.";
        }
        else if (polarArray[1] >= 2 && polarArray[1] <= 3)
        {
            if (polarArray[2] <= 1 && polarArray[2] >= 0)
                comment += conclusion + "Mediocre trend along with fair value, but fundamental is not up to it.";
            else if (polarArray[2] >= 2 && polarArray[2] <= 3)
                comment += conclusion + "Mediocre trend along with fair value and average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Mediocre trend along with good fundamental, but just fair value.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Mediocre trend along with fair value.";
        }
        else if (polarArray[1] >= 4)
        {
            if (polarArray[2] < 2  && polarArray[2] >= 0)
                comment += conclusion + "Mediocre trend along with good value, but fundamental is not up to it.";
            else if (polarArray[2] >= 2  && polarArray[2] <= 3)
                comment += conclusion + "Mediocre trend along with good value, but just average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Mediocre trend along with both good fundamental and value.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Mediocre trend along with good value.";
        }
        else if (polarArray[1] < 0)
        {
            if (polarArray[2] < 2  && polarArray[2] >= 0)
                comment += conclusion + "Mediocre trend, but fundamental is not up to it.";
            else if (polarArray[2] >= 2  && polarArray[2] <= 3)
                comment += conclusion + "Mediocre trend along with average fundamental.";
            else if (polarArray[2] >= 4)
                comment += conclusion + "Mediocre trend along with good fundamental.";
            else if (polarArray[2] < 0)
                comment += conclusion + "Mediocre trend.";
        }
    }
    else if (polarArray[0] <= 1 && polarArray[0] >= 0)
    {
        if (polarArray[2] >= 4)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Strong fundamental, but not in a good trend and expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Strong fundamental along with fair value, but not in a good trend.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Strong fundamental and value, but not in a good trend.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Strong fundamental, but not in a good trend.";
        }
        else if (polarArray[2] >= 2 && polarArray[2] <= 3)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Average fundamental, but not in a good trend and expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Average fundamental along with fair value, but not in a good trend.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Average fundamental along with good value, but not in a good trend.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Average fundamental, but not in a good trend.";
        }
        else if (polarArray[2] >= 0 && polarArray[2] <= 2)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Weak fundamental, not in a good trend and expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Weak fundamental along with fair value, and not in a good trend.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Weak fundamental along with good value, and not in a good trend.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Weak fundamental and not in a good trend.";
        }
        else if (polarArray[2] < 0)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Not in a good trend and expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Not in a good trend, but along with fair value.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Not in a good trend, but along with good value.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Not in a good trend.";
        }
    }
    else if (polarArray[0] < 0)
    {
        if (polarArray[2] >= 4)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Strong fundamental, but expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Strong fundamental along with fair value.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Strong fundamental and value.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Strong fundamental.";
        }
        else if (polarArray[2] >= 2 && polarArray[2] <= 3)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Average fundamental, but expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Average fundamental along with fair value.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Average fundamental along with good value.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Average fundamental.";
        }
        else if (polarArray[2] >= 0 && polarArray[2] <= 2)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Weak fundamental and expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Weak fundamental along with fair value.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Weak fundamental along with good value.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Weak fundamental.";
        }
        else if (polarArray[2] < 0)
        {
            if (polarArray[1] < 2 && polarArray[1] >= 0)
                comment += conclusion + "Expensive.";
            else if (polarArray[1] >= 2 && polarArray[1] <= 3)
                comment += conclusion + "Fair value.";
            else if (polarArray[1] >= 4)
                comment += conclusion + "Good value.";
            else if (polarArray[1] < 0)
                comment += conclusion + "Not available.";
        }
    }
    return comment;
}

function pchart(stockcode, stockArray, industryArray, isIE)
{
    var t = polarTheme();
    var polarchart = Highcharts.chart('polarcontainer', {

        chart: {
            polar: true,
            type: 'areaspline',
            backgroundColor: t.dark ? '#1e1e32' : '#ffffff',
            style: {
                fontFamily: 'Montserrat'
            },
            events: {
                resize: function (event) {
                    setLabelEvent(isIE); // for F12 key or very small screen
                },
            }
        },

        title: {
            text: '',
            x: -80
        },

        lang: {
            noData: ""
        },

        pane: {
            size: '83%',
            center: ['50%', '55%'],
        },

        xAxis: {
            categories: ['Technical', 'Valuation', 'Fundamental'/*, 'Profitability', 'Health'*/],
            tickmarkPlacement: 'on',
            lineWidth: 0,
            gridLineColor: t.gridLineColor,
            labels: {
                style: {
                    fontSize:'12px',
                    color: t.dark ? '#cccccc' : '#333333'
                }
            }
        },

    	exporting: { enabled: false },

      	credits: {
            enabled: false
        },

        yAxis: {
            gridLineInterpolation: 'circle',
            lineWidth: 0,
            min: 0,
            max: 5,
            tickInterval: 1,
            gridLineColor: t.dark ? '#2a2a3e' : '#f0f0ff',
            labels:
            {
            	enabled: false
            },

            plotBands: [{
              from: 0,
              to: 1,
              color: t.bandOdd
            }, {
              from: 1,
              to: 2,
              color: t.bandEven
            }, {
              from: 2,
              to: 3,
              color: t.bandOdd
            }, {
              from: 3,
              to: 4,
              color: t.bandEven
            }, {
              from: 4,
              to: 5,
              color: t.bandOdd
            }],
        },

        tooltip: {
            shared: true,
            formatter: function() {
                var s = '<span style="font-size: 10px;">' + this.x + ' Polar</span>';

                $.each(this.points, function(i, point) {
                    s += '<br/><span style="font-weight: bold; color:' + point.series.color + '">' + point.series.name + '</span><span style="font-weight: bold; color:' + (t.dark ? '#999999' : 'darkgrey') + '">:</span><b> '+ point.y+ '</b>';
                });
                return s;
            }
        },

        legend: {
            enabled: true,
            align: 'center',
            verticalAlign: 'bottom',
    	    labelFormatter: function() {
                return '<span style="color:' + this.color + '">' + this.name + '</span>';
            }
        },

        plotOptions: {
            series: {
                events: {
                    legendItemClick: function () {
                        setLabelEvent(isIE)
                        return true;
                    }
                }
            }
        },

        series: [{
            cursor: 'pointer',
            name: stockcode,
            data: stockArray,
            pointPlacement: 'on',
            lineWidth: 0,
          	color: {
              radialGradient: {
                cx: 0.5,
                cy: 0.5,
                r: 1
              },
              stops: [
                [0, 'rgba(255, 255, 147, 0.5)'],
                [1, 'rgba(165, 75, 0, 0.75)']
              ]
            },
            point: {
                events: {
                    click: function() {
                        $([document.documentElement, document.body]).animate({
                        scrollTop: $("#"+this.category).offset().top - 85
                        }, 1000);
                    }
                }
            },
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        align: 'center',
                        verticalAlign: 'bottom'
                    },
                    pane: {
                        size: '83%',
                        center: ['50%', '55%'],
                    }
                }
            }]
        }

    });

    if (industryArray !== null && industryArray[0] != -1 && industryArray[1] != -1 && industryArray[2] != -1)
    {
        polarchart.options.legend.enabled = true;
       	polarchart.addSeries({
            cursor: 'pointer',
            name: 'Industry Median',
            data: industryArray,
            pointPlacement: 'on',
            lineWidth: 0,
            color: {
              radialGradient: {
                cx: 0.5,
                cy: 0.5,
                r: 1
              },
              stops: [
                  [0, 'rgba(147, 255, 228, 0.35)'],
                  [1, 'rgba(0, 105, 165, 0.6)']
              ]
            },
            zIndex: -1,
            point: {
                events: {
                    click: function() {
                        $([document.documentElement, document.body]).animate({
                        scrollTop: $("#"+this.category).offset().top - 85
                        }, 1000);
                    }
                }
            },
        }, true);
    }

    /* ---- live theme toggle: destroy & recreate ---- */
    if (!window._polarBound) {
      window._polarBound = true;
      document.addEventListener('themechange', function() {
        var args = window._polarArgs;
        if (args) {
          var old = window._polarChart;
          if (old && old.destroy) { try { old.destroy(); } catch(e) {} }
          var container = document.getElementById('polarcontainer');
          if (container) { container.innerHTML = ''; }
          setTimeout(function() {
            window._polarChart = pchart.apply(null, args);
          }, 30);
        }
      });
    }
    window._polarChart = polarchart;
    window._polarArgs = [stockcode, stockArray, industryArray, isIE];

    return polarchart;
}

function setLabelEvent(isIE)
{
    if(isIE)
    {
        var labels = Array.prototype.slice.call(polarchart.xAxis[0].labelGroup.element.childNodes, 0);

        for (var i = 0; i < labels.length; i++)
        {
            labels[i].onclick = null;
            labels[i].onmouseover = null;
            labels[i].onmouseout = null;

            labels[i].style.cursor = "pointer";
            labels[i].onclick = function(){
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#"+this.textContent).offset().top - 85
                    }, 1000);
            };

            labels[i].onmouseover = function(){
                for (var i = 0; i < labels.length; i++)
                {
                    labels[i].style.fontWeight='normal';
                }
                this.style.fontWeight='bold';
                this.style.cursor = 'pointer';
            };

            labels[i].onmouseout = function(){
                for (var j = 0; j < labels.length; j++)
                {
                    labels[j].style.fontWeight='normal';
                    labels[j].style.cursor = 'default';
                }
            };
        }
    }
    else
    {
        polarchart.xAxis[0].labelGroup.element.childNodes.forEach(function(label)
        {
            label.style.cursor = "pointer";
            label.onclick = function(){
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#"+this.textContent).offset().top - 85
                    }, 1000);
            };

            label.onmouseover = function(){
                label.style.fontWeight='bold';
            };

            label.onmouseout = function(){
                label.style.fontWeight='normal';
            };
        });
    }
}