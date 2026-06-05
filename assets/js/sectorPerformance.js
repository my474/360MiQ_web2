function sectorPerformance(barcontainer, title, href, dict_sector, toShowBreadth, sort, showbar, toShowScreener)
{
  if (dict_sector === null || typeof dict_sector === 'undefined' || dict_sector.length === 0)
    return null;
    
  var market = href.substring(href.indexOf('=') + 1);
  //var categories = ['Auto', 'Oil', 'Property', 'Bank', 'Telecom', 'Insurance', 'Coal', 'Tech', 'Construction', 'Aviation', 'Manufacture', 'Food', 'Electronics', 'Retail & Trading', 'Conglomerate', 'Hotel', 'Utilities', 'Finance', 'Healthcare', 'Industrial', 'Metal'];
  var categories = Array();
  var d1 = Array();
  var d5 = Array();
  var d20 = Array();
  var subtitle = "";
  if (dict_sector[0].length == 5)
      subtitle = dict_sector[0][4];

  for(i = 0; i < dict_sector.length; i++)
  {
      categories.push(dict_sector[i][0]);
      d1.push(dict_sector[i][1]);
      d5.push(dict_sector[i][2]);
      d20.push(dict_sector[i][3]);
  }

	(function (H) {
    	H.wrap(H.Tick.prototype, 'render', function (p, index, old, opacity) {
      	p.call(this, index, old, opacity);
      	var gridLine = this.gridLine;
      	
        if (!this.axis.horiz && this.pos === 0 && gridLine) {
        	gridLine.attr({
          	//"stroke-width": 2,
          	stroke: 'grey'
          });
        }
      });
    })(Highcharts);
    
  var show1 = true;
  var show5 = true;
  var show20 = true;
  var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  var labelColor = isDark ? '#cccccc' : '#555555';
  if (showbar !== '')
  {
    if (showbar == '1')
    {
      show5 = false;
      show20 = false;
    }
    else if (showbar == '5')
    {
      show1 = false;
      show20 = false;
    }
    else if (showbar == '20')
    {
      show1 = false;
      show5 = false;
    }
    else if (showbar == '1,5')
    {
      show20 = false;
    }
    else if (showbar == '1,20')
    {
      show5 = false;
    }
    else if (showbar == '5,20')
    {
      show1 = false;
    }
  }
  
  var chart = Highcharts.chart(barcontainer, {
  chart: {
    style: {
        fontFamily: 'Montserrat'
    },
    type: 'column',
    margin: [7, 35, 150, 55],
    events: {
        resize: function (event) {
            setLabelEvent(chart, market, toShowScreener); // for F12 key or very small screen
        },
        click: function (event) {
            //alert(
            //    this.name + ' clicked\n' +
            //    'Alt: ' + event.altKey + '\n' +
            //    'Control: ' + event.ctrlKey + '\n' +
            //    'Meta: ' + event.metaKey + '\n' +
            //    'Shift: ' + event.shiftKey
            //);
            if (toShowBreadth)
                location.href = href;
        },
        load: function() {
            var points = this.series[0].points,
                chart = this,
                newPoints = [];
    
            Highcharts.each(points, function(point, i) {
                point.update({
                    name: categories[i]
                }, false);
                newPoints.push({
                    x: point.x,
                    y: point.y,
                    name: point.name
                });
            });
          
            var points1 = this.series[1].points,
                //chart = this,
                newPoints1 = [];
    
            Highcharts.each(points1, function(point, i) {
                point.update({
                    name: categories[i]
                }, false);
                newPoints1.push({
                    x: point.x,
                    y: point.y,
                    name: point.name
                });
            });          
              
            var points2 = this.series[2].points,
                //chart = this,
                newPoints2 = [];
    
            Highcharts.each(points2, function(point, i) {
                point.update({
                    name: categories[i]
                }, false);
                newPoints2.push({
                    x: point.x,
                    y: point.y,
                    name: point.name
                });
            });            
            chart.redraw();
            
            // default sort 1 day
            if (sort == '1' || sort === '')
                sort1day(newPoints, newPoints1, newPoints2, chart);          
            else if (sort == '5')
                sort5day(newPoints, newPoints1, newPoints2, chart, title);
            else if (sort == '20')
                sort20day(newPoints, newPoints1, newPoints2, chart, title);
            else if (sort == 'AZ')
                sortAZ(newPoints, newPoints1, newPoints2, chart, title);
                
            // Sorting A - Z
            $('#sort1'+title.replace(/\s+/g, '')).on('click', function() {
              //$("button").removeClass("active");            
              //$(this).addClass('active').siblings('.active').removeClass('active');
              $(this).siblings('.active').removeClass("active");
              $('#sort1'+title.replace(/\s+/g, '')).removeClass("active");
              newPoints.sort(function(a, b) {
                if (a.name < b.name)
                  return -1;
                if (a.name > b.name)
                  return 1;
                return 0;
              });
    
              Highcharts.each(newPoints, function(el, i) {
                el.x = i;
              });
    
                var newPointsTmp = [];
                var newPointsTmp2 = [];
                for (var i = 0; i < newPoints1.length; i++)
                {
                    for (var j = 0; j < newPoints.length; j++)
                    {
                        if (newPoints1[j].name == newPoints[i].name)
                        {
                            newPointsTmp.push({
                                x: newPoints[i].x,
                                y: newPoints1[j].y,
                                name: newPoints[i].name
                            });
                        }
                        
                        if (newPoints2[j].name == newPoints[i].name)
                        {
                           newPointsTmp2.push({
                                x: newPoints[i].x,
                                y: newPoints2[j].y,
                                name: newPoints[i].name
                            });
                        }                    
                    }
                }
                chart.series[0].setData(newPoints, true, false, false);
                chart.series[1].setData(newPointsTmp, true, false, false);
                chart.series[2].setData(newPointsTmp2, true, false, false);
            });
    
            // Sorting min - max
            $('#sort2'+title.replace(/\s+/g, '')).on('click', function() {
                //$("button").removeClass("active");            
                //$(this).addClass('active').siblings('.active').removeClass('active');
                $(this).siblings('.active').removeClass("active");
                $('#sort2'+title.replace(/\s+/g, '')).removeClass("active");
                sort1day(newPoints, newPoints1, newPoints2, chart);          
            });
    
            // Sorting 5 days
            $('#sort3'+title.replace(/\s+/g, '')).on('click', function() {
              //$("button").removeClass("active");            
              //$(this).addClass('active').siblings('.active').removeClass('active');
              $(this).siblings('.active').removeClass("active");
              $('#sort3'+title.replace(/\s+/g, '')).removeClass("active");
              newPoints1.sort(function(a, b) {
                return b.y - a.y
              });
    
              Highcharts.each(newPoints1, function(el, i) {
                el.x = i;
              });
                
                var newPointsTmp = [];
                var newPointsTmp2 = [];
                for (var i = 0; i < newPoints1.length; i++)
                {
                    for (var j = 0; j < newPoints.length; j++)
                    {
                        if (newPoints[j].name == newPoints1[i].name)
                        {
                           newPointsTmp.push({
                                x: newPoints1[i].x,
                                y: newPoints[j].y,
                                name: newPoints1[i].name
                            });
                        }
                        
                        if (newPoints2[j].name == newPoints1[i].name)
                        {
                           newPointsTmp2.push({
                                x: newPoints1[i].x,
                                y: newPoints2[j].y,
                                name: newPoints1[i].name
                            });
                        }                    
                    }
                }
                chart.series[0].setData(newPointsTmp, true, false, false);
                chart.series[1].setData(newPoints1, true, false, false);
                chart.series[2].setData(newPointsTmp2, true, false, false);
            });
              
            // Sorting 20 Days
            $('#sort4'+title.replace(/\s+/g, '')).on('click', function() {
              //$("button").removeClass("active");            
              //$(this).addClass('active').siblings('.active').removeClass('active');
              $(this).siblings('.active').removeClass("active");
              $('#sort4'+title.replace(/\s+/g, '')).removeClass("active");
              newPoints2.sort(function(a, b) {
                return b.y - a.y
              });
    
              Highcharts.each(newPoints2, function(el, i) {
                el.x = i;
              });
                
                var newPointsTmp = [];
                var newPointsTmp2 = [];
                for (var i = 0; i < newPoints2.length; i++)
                {
                    for (var j = 0; j < newPoints.length; j++)
                    {
                        if (newPoints[j].name == newPoints2[i].name)
                        {
                           newPointsTmp.push({
                                x: newPoints2[i].x,
                                y: newPoints[j].y,
                                name: newPoints2[i].name
                            });
                        }   
                        
                        if (newPoints2[i].name == newPoints1[j].name)
                        {
                           newPointsTmp2.push({
                                x: newPoints2[i].x,
                                y: newPoints1[j].y,
                                name: newPoints2[i].name
                            });
                        }  
                    }
                }
                chart.series[0].setData(newPointsTmp, true, false, false);
                chart.series[1].setData(newPointsTmp2, true, false, false);            
                chart.series[2].setData(newPoints2, true, false, false);            
            });          
        }
    }, 
  },

    plotOptions: {
        column: {
            borderRadius: 2
        },
        
        series: {
            cursor: toShowScreener? 'pointer' : 'default',
            point: {
                events: {
                    click: function () {
                        //alert('Category: ' + this.name + ', value: ' + this.y);
                        if (toShowScreener)
                        {
                            var market = href.split('=')[1].toUpperCase();
                            
                            var sector = this.name;
                            if (sector == "Market")
                                //location.href = 'screener?market='+market+'&sector=All&industry=All';
                                location.href = 'screener?market='+market;
                            else
                                //location.href = 'screener?market='+market+'&sector=' + sector + '&industry=All';
                                location.href = 'screener?market='+market+'&sector=' + sector;
                        }
                    },
                    
                    mouseOver: function() {
                        var point;
                        var length = this.series.chart.xAxis[0].labelGroup.element.childNodes.length;
                        for (var i = 0; i < length; i++)
                        {
                            if (this.name == this.series.chart.xAxis[0].labelGroup.element.childNodes[i].textContent)
                            {
                                point = i;
                                break;
                            }
                        }
                        $(this.series.chart.xAxis[0].labelGroup.element.childNodes[point]).css('fill', '#00d9FF');
                        $(this.series.chart.xAxis[0].labelGroup.element.childNodes[point]).css('fontWeight', 'bold');
                    },
                    mouseOut: function() {    
                        var point;
                        var length = this.series.chart.xAxis[0].labelGroup.element.childNodes.length;
                        for (var i = 0; i < length; i++)
                        {
                            if (this.name == this.series.chart.xAxis[0].labelGroup.element.childNodes[i].textContent)
                            {
                                point = i;
                                break;
                            }
                        }
                        $(this.series.chart.xAxis[0].labelGroup.element.childNodes[point]).css('fill', labelColor);
                        $(this.series.chart.xAxis[0].labelGroup.element.childNodes[point]).css('fontWeight', 'normal');
                    },                    
                    // go to URL
                    //click: function () {
                    //    location.href = 'https://en.wikipedia.org/wiki/' +
                    //        this.options.key;
                    //}
                }
            },
            events: {
                legendItemClick: function () { // restore label event after all series are toggled invisible and back
                    setLabelEvent(chart, market, toShowScreener);
                    return true;
                }
            }
        }
    },
      
  exporting: { 
        enabled: true,
        buttons: {
            contextButton: {
                align: 'right',
                verticalAlign: 'bottom'
            }
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
            verticalAlign: 'bottom',
            x: 60,
            y: -152
        }
    },
    
  tooltip: {
    shared: true,
    split: false,
    useHTML: true,
    //valueDecimals: 4,
    formatter: function (tooltip) {
        return this.points.reduce(function (s, point) {
            if (point.y < 0)
                return s + '<br/><span style="color:' + point.color.stops[1][1] + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + rounding(point.y, 10) + '%</span>';
            else if (point.y > 0)
                return s + '<br/><span style="color:' + point.color.stops[1][1] + '">●</span> ' + point.series.name + ': ' + '<span style="color:green;font-weight:bold">+' + rounding(point.y, 10) + '%</span>';
            else
                return s + '<br/><span style="color:' + point.color.stops[1][1] + '">●</span> ' + point.series.name + ': ' + '<span style="color:grey;font-weight:bold">\u00B1' + rounding(point.y, 10) + '%</span>';
        }, '<span style="font-size:10px">' + this.points[0].key + '<br>' + Highcharts.dateFormat('%Y-%m-%d, %a', new Date(subtitle)) + '</span>');
    },
  },
  
  title: {
    useHTML: true,
    //style: {
    //    'background-color': '#ffffff66',
    //},
    text: title },

    subtitle: {
        text: subtitle + ', ' + str2date(subtitle).toLocaleString("default", { weekday: "short" }),
    },
    
  xAxis: {
    labels: {
        rotation: 300,
        align: 'right',
        y: 15
        
      //  y: 45
    },
    //categories: categories,
    type: 'category'
  },

  yAxis: [{
      allowDecimals: false,
      labels: {
        style: {
            color: 'grey'
        },
      },
      title: {
        text: '% Gain/Loss',
        margin: 3
      },
      //tickInterval: 1,
      gridLineDashStyle: 'dot'
  },{
      labels: {
        style: {
            color: 'grey'
        },
      },      
      title: {
        text: '',
        margin: 0
      },
      linkedTo:0,
      opposite:true,
      //tickInterval: 1,
      gridLineDashStyle: 'dot'
  }],

  series: [{
    visible: show1,
    name: '1 Day',
    data: d1,//[5, 3, 4, 7, 2, -3, 2, 1,3, 4, 9,8,7,2,3,4,-1,-5, 1, 2,3],
    negativeColor: {
      linearGradient: {
        x1: 0,
        x2: 0,
        y1: 1,
        y2: 0
      },
      stops: [
        [0, '#FFd900'],
        [1, '#ffa600']
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
        [0, '#FFd900'],
        [1, '#ffa600']
      ]
    }
  },
  {
    visible: show5,
    name: '5 Days',
    data: d5,//[2, -2, -3, 2, 1, 5, 6, 1, 2, -1, 3,6,9, -5, -2, -1, 2,3, 4, 3, 2],
    negativeColor: {
      linearGradient: {
        x1: 0,
        x2: 0,
        y1: 1,
        y2: 0
      },
      stops: [
        [0, '#00eeFF'],
        [1, '#00bbff']
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
  },
  {
    visible: show20,
    name: '20 Days',
    data: d20,//[3, 4, 4, -2, 5, 10, 5, -2, 3, 1, 1, 2, 2, 5, 6, 7, -2, -4, 2, 2, 1],
    negativeColor: {
      linearGradient: {
        x1: 0,
        x2: 0,
        y1: 1,
        y2: 0
      },
      stops: [
        [0, '#baff00'],
        [1, '#77dd00']
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
        [0, '#baff00'],
        [1, '#77dd00']
      ]
    }
  }] });
  
  return chart;
}

function sortAZ(newPoints, newPoints1, newPoints2, chart, title) 
{
    $('#sort1'+title.replace(/\s+/g, '')).siblings('.active').removeClass("active");
    $('#sort1'+title.replace(/\s+/g, '')).removeClass("active");
    $('#sort1'+title.replace(/\s+/g, '')).addClass("active");
    
    newPoints.sort(function(a, b) {
        if (a.name < b.name)
            return -1;
        if (a.name > b.name)
            return 1;
        return 0;
    });
    
    Highcharts.each(newPoints, function(el, i) {
        el.x = i;
    });
    
    var newPointsTmp = [];
    var newPointsTmp2 = [];
    for (var i = 0; i < newPoints1.length; i++)
    {
        for (var j = 0; j < newPoints.length; j++)
        {
            if (newPoints1[j].name == newPoints[i].name)
            {
                newPointsTmp.push({
                    x: newPoints[i].x,
                    y: newPoints1[j].y,
                    name: newPoints[i].name
                });
            }
            
            if (newPoints2[j].name == newPoints[i].name)
            {
               newPointsTmp2.push({
                    x: newPoints[i].x,
                    y: newPoints2[j].y,
                    name: newPoints[i].name
                });
            }                    
        }
    }
    chart.series[0].setData(newPoints, true, false, false);
    chart.series[1].setData(newPointsTmp, true, false, false);
    chart.series[2].setData(newPointsTmp2, true, false, false);
}

function sort1day(newPoints, newPoints1, newPoints2, chart)
{
    newPoints.sort(function(a, b) {
        return b.y - a.y
    });

    Highcharts.each(newPoints, function(el, i) {
        el.x = i;
    });

    var newPointsTmp = [];
    var newPointsTmp2 = [];
    for (var i = 0; i < newPoints1.length; i++)
    {
        for (var j = 0; j < newPoints.length; j++)
        {
            if (newPoints1[j].name == newPoints[i].name)
            {
               newPointsTmp.push({
                    x: newPoints[i].x,
                    y: newPoints1[j].y,
                    name: newPoints[i].name
                });
            }

            if (newPoints2[j].name == newPoints[i].name)
            {
               newPointsTmp2.push({
                    x: newPoints[i].x,
                    y: newPoints2[j].y,
                    name: newPoints[i].name
                });
            }                    
        }
    }
    chart.series[0].setData(newPoints, true, false, false);
    chart.series[1].setData(newPointsTmp, true, false, false);
    chart.series[2].setData(newPointsTmp2, true, false, false);
}

function sort5day(newPoints, newPoints1, newPoints2, chart, title)
{
    $('#sort3'+title.replace(/\s+/g, '')).siblings('.active').removeClass("active");
    $('#sort3'+title.replace(/\s+/g, '')).removeClass("active");
    $('#sort3'+title.replace(/\s+/g, '')).addClass("active");
      
    newPoints1.sort(function(a, b) {
        return b.y - a.y
    });

    Highcharts.each(newPoints1, function(el, i) {
        el.x = i;
    });
        
    var newPointsTmp = [];
    var newPointsTmp2 = [];
    for (var i = 0; i < newPoints1.length; i++)
    {
        for (var j = 0; j < newPoints.length; j++)
        {
            if (newPoints[j].name == newPoints1[i].name)
            {
               newPointsTmp.push({
                    x: newPoints1[i].x,
                    y: newPoints[j].y,
                    name: newPoints1[i].name
                });
            }
            
            if (newPoints2[j].name == newPoints1[i].name)
            {
               newPointsTmp2.push({
                    x: newPoints1[i].x,
                    y: newPoints2[j].y,
                    name: newPoints1[i].name
                });
            }                    
        }
    }
    chart.series[0].setData(newPointsTmp, true, false, false);
    chart.series[1].setData(newPoints1, true, false, false);
    chart.series[2].setData(newPointsTmp2, true, false, false);
}

function sort20day(newPoints, newPoints1, newPoints2, chart, title)
{
    $('#sort4'+title.replace(/\s+/g, '')).siblings('.active').removeClass("active");
    $('#sort4'+title.replace(/\s+/g, '')).removeClass("active");
    $('#sort4'+title.replace(/\s+/g, '')).addClass("active");
  
    newPoints2.sort(function(a, b) {
        return b.y - a.y
    });

    Highcharts.each(newPoints2, function(el, i) {
        el.x = i;
    });
        
    var newPointsTmp = [];
    var newPointsTmp2 = [];
    for (var i = 0; i < newPoints2.length; i++)
    {
        for (var j = 0; j < newPoints.length; j++)
        {
            if (newPoints[j].name == newPoints2[i].name)
            {
               newPointsTmp.push({
                    x: newPoints2[i].x,
                    y: newPoints[j].y,
                    name: newPoints2[i].name
                });
            }   
            
            if (newPoints2[i].name == newPoints1[j].name)
            {
               newPointsTmp2.push({
                    x: newPoints2[i].x,
                    y: newPoints1[j].y,
                    name: newPoints2[i].name
                });
            }  
        }
    }
    chart.series[0].setData(newPointsTmp, true, false, false);
    chart.series[1].setData(newPointsTmp2, true, false, false);            
    chart.series[2].setData(newPoints2, true, false, false);            
}
         
function setLabelEvent(chart, market, toShowScreener)
{
    if (chart === null || chart.xAxis === undefined)
        return;
        
    if(detectIE())
    {
        var labels = Array.prototype.slice.call(chart.xAxis[0].labelGroup.element.childNodes, 0);
        
        for (var i = 0; i < labels.length; i++)
        {
            labels[i].onclick = null;
            labels[i].onmouseover = null;
            labels[i].onmouseout = null;
            
            if (toShowScreener)
            {
                labels[i].style.cursor = "pointer";
                labels[i].onclick = function(){
                    //alert('Category: ' + this.textContent);
                    var sector = this.textContent;
                    if (sector == "Market")
                        //location.href = 'screener?market='+market+'&sector=All&industry=All';
                        location.href = 'screener?market='+market;
                    else
                        //location.href = 'screener?market='+market+'&sector=' + sector + '&industry=All';
                        location.href = 'screener?market='+market+'&sector=' + sector;
                };
            }
    
            labels[i].onmouseover = function(){
                var point;
                var length = chart.series[0].data.length;
                for (var i = 0; i < length; i++)
                {
                    if (this.textContent == chart.series[0].data[i].name)
                    {
                        point = i;
                        break;
                    }
                }
                
                for (var j = 0; j < labels.length; j++)
                {
                    labels[j].style.fontWeight='normal';
                    labels[j].style.fill= labelColor;
                }
                this.style.fontWeight='bold';
                this.style.fill= '#00d9FF';
                if (toShowScreener)
                    this.style.cursor = 'pointer';
                chart.tooltip.refresh([chart.series[0].points[point], chart.series[1].points[point], chart.series[2].points[point]]);
            };
    
            labels[i].onmouseout = function(){
                for (var j = 0; j < labels.length; j++)
                {
                    labels[j].style.fontWeight='normal';
                    labels[j].style.fill= labelColor;
                    labels[j].style.cursor = 'default';
                }
                
            };              
        }
    }
    else
    {
        chart.xAxis[0].labelGroup.element.childNodes.forEach(function(label)
        {
            label.onclick = null;
            label.onmouseover = null;
            label.onmouseout = null;
            
            if (toShowScreener)
            {
                label.style.cursor = "pointer";
                label.onclick = function(){
                    //alert('Category: ' + this.textContent);
                    var sector = this.textContent;
                    if (sector == "Market")
                        //location.href = 'screener?market='+market+'&sector=All&industry=All';
                        location.href = 'screener?market='+market;
                    else
                        //location.href = 'screener?market='+market+'&sector=' + sector + '&industry=All';
                        location.href = 'screener?market='+market+'&sector=' + sector;
                };
            }
    
            label.onmouseover = function(){
                var point;
                var length = chart.series[0].data.length;
                for (var i = 0; i < length; i++)
                {
                    if (this.textContent == chart.series[0].data[i].name)
                    {
                        point = i;
                        break;
                    }
                }
                
                label.style.fontWeight='bold';
                label.style.fill= '#00d9FF';
                if (toShowScreener)
                    label.style.cursor = 'pointer';
                chart.tooltip.refresh([chart.series[0].points[point], chart.series[1].points[point], chart.series[2].points[point]]);
            };
    
            label.onmouseout = function(){
                label.style.fontWeight='normal';
                label.style.fill= labelColor;
                label.style.cursor = 'default';
            };    
        });  
    }
}