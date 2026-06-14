function adpie(container, datestr, advValue, title, fullname, link){   

    Highcharts.chart(container, {
    
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false,
            style: {
                fontFamily: 'Montserrat'
            }
        },
        title: {
            //useHTML: true,
            text: '<span style="font-size:8px">' + datestr.substring(5) + '</span><br>' + title,// + (title == 'SH'? '<span style="font-weight:600;font-size:7px;vertical-align: 10px;color:#ab47bc;" data-toggle="tooltip" title="End of Day">E</span>' : ''),
            align: 'center',
            verticalAlign: 'middle',
            y: 27,
            style: {
              fontSize: '15px' 
           }
        },
    
    	exporting: { enabled: false },
    
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: false,
                    distance: -10,
                    style: {
                        color: '#000000',
                        fontSize: '10px',
                        textOutline: '2px #ffffff'
                    }
                },
                cursor: 'pointer',
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '180%'],
                size: '420%',
                events: {
                    click: function (event) {
                        //alert(
                        //    this.name + ' clicked\n' +
                        //    'Alt: ' + event.altKey + '\n' +
                        //    'Control: ' + event.ctrlKey + '\n' +
                        //    'Meta: ' + event.metaKey + '\n' +
                        //    'Shift: ' + event.shiftKey
                        //);
                        location.href = link;
                    }
                }            
            }
        },
        
        tooltip: {
            formatter: function() {
                // If the point is going to have a tooltip
                if(!this.point.noTooltip) {
                    // Mimic default tooltip contents
                    //return '<br/><span style="color:' + this.color + '">\u25CF</span> ' + this.point.name+ '<br/>' + this.series.name + ': ' + '<b>' + this.y + '</b>' + '<br/>' + '<span style="font-size:10px;">' + datestr + '</span>';
                    return '<span style="font-size:10px;">' + datestr + '</span><br><span style="color:' + this.color + '">\u25CF </span><span style="font-size:10px;">' + this.point.name+ '</span><br><span style="font-size:10px;">' + this.series.name + ': ' + '<b>' + this.y + '</b></span>';
                }
    
                // If tooltip is disabled
                return false;
            }
        },
    
        credits: {
            enabled: false
        },
        
        series: [{
            type: 'pie',
            name: fullname,
            innerSize: '50%',
    
            data: [
            {
                dataLabels: {
                    enabled: true,
                    style: {
                        color: '#000000',
                        textOutline: '2px #ffffff'
                    }
    			},
                name: advValue + '%',
                y: 0,
                noTooltip: true
    		},            
    		{
                name: 'Advance %',
                y: advValue,
                color: {
                  linearGradient: {
                    x1: 1,
                    x2: 0,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, '#3f0'],
                    [0.5, '#0e0'],
                    [1, '#0c0']
                  ]
                }
            },
            {
                name: 'Decline %',
                y: 100 - advValue,
                color: {
                  linearGradient: {
                    x1: 0,
                    x2: 1,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, '#F30'],
                    [0.5, '#e00'],
                    [1, '#c00']
                  ]
                }
    		},
            {
                dataLabels: {
                    enabled: true,
                    style: {
                        color: '#000000',
                        textOutline: '2px #ffffff'
                    }
    			},
                name: 100 - advValue + '%',
                y: 0,
                noTooltip: true
    		},
            ]
        }]
    });
}
/*function adpie(container, advValue, title, fullname, link){   

    Highcharts.chart(container, {
    
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false,
            style: {
                fontFamily: 'Montserrat'
            }
        },
        title: {
            text: title,
            align: 'center',
            verticalAlign: 'middle',
            y: 36
        },
    
    	exporting: { enabled: false },
    
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: false,
                    distance: -3,
                    style: {
                        fontSize: '10px'
                    }
                },
                cursor: 'pointer',
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '180%'],
                size: '520%',
                events: {
                    click: function (event) {
                        //alert(
                        //    this.name + ' clicked\n' +
                        //    'Alt: ' + event.altKey + '\n' +
                        //    'Control: ' + event.ctrlKey + '\n' +
                        //    'Meta: ' + event.metaKey + '\n' +
                        //    'Shift: ' + event.shiftKey
                        //);
                        location.href = link;
                    }
                }            
            }
        },
        
        tooltip: {
            formatter: function() {
                // If the point is going to have a tooltip
                if(!this.point.noTooltip) {
                    // Mimic default tooltip contents
                    return '<br/><span style="color:' + this.color + '">\u25CF</span> ' + this.point.name+'<br/>' + this.series.name + ': ' + '<b>' + this.y + '</b>';
                }
    
                // If tooltip is disabled
                return false;
            }
        },
    
        credits: {
            enabled: false
        },
        
        series: [{
            type: 'pie',
            name: fullname,
            innerSize: '50%',
    
            data: [
            {
                dataLabels: {
                    enabled: true,
    			},
                name: advValue + '%',
                y: 0,
                noTooltip: true
    		},            
    		{
                name: 'Advance %',
                y: advValue,
                color: {
                  linearGradient: {
                    x1: 1,
                    x2: 0,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, '#3f0'],
                    [0.5, '#0e0'],
                    [1, '#0c0']
                  ]
                }
            },
            {
                name: 'Decline %',
                y: 100 - advValue,
                color: {
                  linearGradient: {
                    x1: 0,
                    x2: 1,
                    y1: 0,
                    y2: 1
                  },
                  stops: [
                    [0, '#F30'],
                    [0.5, '#e00'],
                    [1, '#c00']
                  ]
                }
    		},
            {
                dataLabels: {
                    enabled: true,
    			},
                name: 100 - advValue + '%',
                y: 0,
                noTooltip: true
    		},
            ]
        }]
    });
}*/
