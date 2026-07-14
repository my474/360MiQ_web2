// The data used in this sample can be obtained from the CDN
// https://cdn.anychart.com/csv-data/aapl-daily.csv
var chart;
function anystockTheme() {
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        dark: dark,
        background: dark ? '#1e1e32' : '#ffffff',
        grid: dark ? '#3a3a4e' : '#cccccc'
    };
}

function applyAnystockTheme(stockChart, plots) {
    if (!stockChart) return;

    var t = anystockTheme();

    try {
        stockChart.background().enabled(true);
        stockChart.background().fill(t.background);
        stockChart.background().stroke(t.background);
    } catch(e) {}

    try {
        stockChart.label(1).fontColor(t.dark ? '#cccccc' : '#455a64').enabled(true);
        stockChart.label(2).fontColor(t.dark ? '#cccccc' : '#455a64').enabled(true);
    } catch(e) {}

    if (!plots) return;
    for (var i = 0; i < plots.length; i++) {
        var plot = plots[i];
        if (!plot) continue;

        try {
            plot.background().enabled(true);
            plot.background().fill(t.background);
            plot.background().stroke(t.background);
        } catch(e) {}

        try { plot.yGrid().stroke({dash: "1 5", color: t.grid}); } catch(e) {}
        try { plot.xGrid().stroke(t.grid); } catch(e) {}
        try { plot.xMinorGrid().stroke({dash: "1 3", color: t.grid}); } catch(e) {}
    }

    patchAnystockSvgBackground(t);
    patchAnystockRangeSelector(t);
}

function patchAnystockSvgBackground(theme) {
    var containerId = window._anystockContainerId || 'stockcontainer';
    var container = document.getElementById(containerId);
    if (!container) return;

    var bg = theme.background;
    var rects = container.querySelectorAll('svg rect');
    for (var i = 0; i < rects.length; i++) {
        var rect = rects[i];
        var fill = (rect.getAttribute('fill') || '').toLowerCase();
        var styleFill = (rect.style && rect.style.fill ? rect.style.fill : '').toLowerCase();
        var isRootBackground = rect.parentNode && rect.parentNode.tagName && rect.parentNode.tagName.toLowerCase() === 'svg';
        var isWhite = fill === '#fff' || fill === '#ffffff' || fill === 'white' || styleFill === 'rgb(255, 255, 255)' || styleFill === '#fff' || styleFill === '#ffffff' || styleFill === 'white';

        if (isRootBackground || isWhite) {
            rect.setAttribute('fill', bg);
            if (rect.style) rect.style.fill = bg;
        }
    }
}

function patchAnystockRangeSelector(theme) {
    var containerId = window._anystockRangeContainerId || 'stockrangecontainer';
    var container = document.getElementById(containerId);
    if (!container) return;

    container.style.background = 'transparent';
    container.style.color = theme.dark ? '#cccccc' : '#333333';

    var buttons = container.querySelectorAll('.anychart-button, .anychart-button-outer-box, .anychart-button-inner-box, .anychart-button-caption');
    for (var i = 0; i < buttons.length; i++) {
        var button = buttons[i];
        button.style.background = theme.dark ? '#2a2a3e' : '#f0f0f0';
        button.style.backgroundImage = 'none';
        button.style.borderColor = theme.dark ? '#444444' : '#cccccc';
        button.style.color = theme.dark ? '#cccccc' : '#333333';
    }
}

function anystock(data, stockcode, stockname, longMAperiod, stockcontainer, stockrangecontainer, showRangeSelector) {
    var hideStockPriceValues = window.PriceDisplayPolicy && !PriceDisplayPolicy.showStockIndexPrices();

    function hiddenSeriesText(seriesName, changeText) {
        if (seriesName == stockcode)
            return changeText === "" ? seriesName : seriesName + ": " + changeText.replace(/[()]/g, "");
        return seriesName;
    }
	// create data table on loaded data
	var dataTable = anychart.data.table();
	dataTable.addData(data);


	var mapping = dataTable.mapAs();
	mapping.addField('open', 1, 'first');
	mapping.addField('high', 2, 'max');
	mapping.addField('low', 3, 'min');
	mapping.addField('close', 4, 'last');
	mapping.addField('value', 4, 'last');
	
	var MappingX = dataTable.mapAs({'value': 0});
	//var MappingY = dataTable.mapAs({'value': 4});
  
    anychart.licenseKey("360miq-3e54a810-e0920e24");
	// create stock chart
	chart = anychart.stock();
	chart.credits().enabled(false);


    var label = chart.label();
    label.padding(5, 5, 0, 5);
    label.position("left-bottom");
    label.anchor("left-bottom");
    label.offsetY(0);
    label.offsetX(0);
    label.width(170);
    label.hAlign("left");
    label.fontFamily("Montserrat");
    label.fontSize(10);
    label.text("");

    var labelstockcode = chart.label(1);
    //labelstockcode.padding(5, 5, 5, 5);
    labelstockcode.position("center-top");
    labelstockcode.anchor("center-top");
    labelstockcode.offsetY(12);
    //labelstockcode.offsetX(100);
    //labelstockcode.width(170);
    labelstockcode.hAlign("center");
    labelstockcode.fontFamily("Montserrat");
    labelstockcode.fontSize(22);
    labelstockcode.text(stockcode);
    labelstockcode.fontOpacity(0.2);
    labelstockcode.fontWeight(600);
    labelstockcode.zIndex(1000); // Keep the watermark above plot backgrounds.
    
    var labelstockname = chart.label(2);
    //labelstockname.padding(5, 5, 5, 5);
    labelstockname.position("center-top");
    labelstockname.anchor("center-top");
    labelstockname.offsetY(38);
    //labelstockname.offsetX(100);
    //labelstockname.width(170);
    labelstockname.hAlign("center");
    labelstockname.fontFamily("Montserrat");
    labelstockname.fontSize(12);
    labelstockname.text(stockname);
    labelstockname.fontOpacity(0.3);
    labelstockname.fontWeight(400);
    labelstockname.zIndex(1000); // Keep the watermark above plot backgrounds.
    
	chart.contextMenu().itemsFormatter(function(items){
        // Disable save as image option
        delete items["save-data-as"];
       
        // delete about and separator 
        delete items["full-screen-separator"];
        delete items["about"];
       
        // return modified array
        return items;
    });

	//chart.tooltip(false);
	chart.tooltip().fontSize(10);
    chart.crosshair().xLabel().fontSize(8);
    chart.crosshair().yLabel().fontSize(8);            
	var background = chart.tooltip().background();
	background.fill(document.documentElement.getAttribute('data-theme')==='dark' ? '#2a2a3e 0.9' : '#888 0.8');
	background.stroke(document.documentElement.getAttribute('data-theme')==='dark' ? '#2a2a3e 0.9' : '#888 0.8');
	background.cornerType("round");
	background.corners(5);
	chart.tooltip().positionMode("chart");
	chart.tooltip().anchor("left-bottom");
	chart.tooltip().position("left-bottom");
    chart.tooltip().titleFormat(function () {
        return anychart.format.dateTime(this.x, "dd MMM yyyy") + ", " + getDayOfWeek(this.x);
    });
    //chart.tooltip().format("{%seriesName}: {%value}{decimalsCount:4}");
    function tooltipFormat(closeDaily, closeWeekly, closeMonthly) {
        chart.tooltip().format(function() {
            var list_close_tooltip = [];
            if (this.dataIntervalUnit == 'day')
                list_close_tooltip = closeDaily;
            else if (this.dataIntervalUnit == 'week')
                list_close_tooltip = closeWeekly;
            else if (this.dataIntervalUnit == 'month')
                list_close_tooltip = closeMonthly;
                
            var series = this.series;
            var value = Math.abs(parseFloat(this.value));
            var decimals = value >= 1 ? 100 : 10000;
            if (series.name() == stockcode)
            {
                var changePstr = "";
                if (this.index > 0 && this.index < list_close_tooltip.length)
                {
                    var changeP = rounding((this.getData("close")/list_close_tooltip[this.index - 1][1] - 1) * 100, 100);
                    var sign = "";
                    if (changeP > 0)
                        sign = "+";
                    else if (changeP === 0)
                        sign = "\u00B1";
                        
                    changePstr = " ("+ sign + changeP +"%)";
                }
                
                if (hideStockPriceValues)
                    return hiddenSeriesText(series.name(), changePstr);

                return "\nClose: " + this.close + changePstr + "\nOpen: " + this.getData("open") + "\nHigh: " + this.getData("high") + "\nLow: " + this.getData("low");
            }
            else if (hideStockPriceValues && series.name() == "Vol")
                return "";
            else
                return series.name().replace(/^SMA\(/,'MA(') + ": " + Math.round(this.value*decimals)/decimals;
        });
    }

    var interactivity = chart.interactivity();
    // Enable use mouse wheel for zooming.
    interactivity.zoomOnMouseWheel(true);

  	chart.scroller().enabled(false);
	// set chart padding
	chart.padding().right(32);
  	chart.padding().left(20);
  	chart.padding().top(0);
  	chart.padding().bottom(18);

	// create plot on the chart
	var plot = chart.plot(0);
  
	var indicatorPlot = chart.plot(1);

    plot.legend().fontSize(7);
    plot.yAxis().drawLastLabel(false);
    plot.yAxis(1).drawLastLabel(false);
    plot.yScale().alignMaximum(false);
    plot.legend().padding().left(0);
    plot.legend().padding().top(2);
    plot.legend().padding().bottom(1);
    plot.legend().padding().right(0);
    plot.legend().maxHeight("18%");
    plot.legend().title().useHtml(true);
    // configure the format of the legend title
    plot.legend().titleFormat(
      "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-size:8'>" +
      "{%value}{dateTimeFormat: yyyy-MM-dd} {%dataIntervalUnit}</span>"
    );
	plot.legend().useHtml(true);
    plot.legend().itemsFormat(function() {
      var series = this.series;
	  var type = series.getType();
      if (type == "candlestick") {
        if (hideStockPriceValues)
          return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" + series.name() + "</span>";

        return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" +
               series.name() + " O:</span> " +
               Math.round(parseFloat(this.open)*10000)/10000 + " <span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>H:</span> " + Math.round(parseFloat(this.high)*10000)/10000 + " <span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>L:</span> " +
               Math.round(parseFloat(this.low)*10000)/10000 + " <span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>C:</span> " + Math.round(parseFloat(this.close)*10000)/10000;
      }
	  else //if (type == 'column') 
      {
        if (hideStockPriceValues && series.name() == "Vol")
          return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" + series.name() + "</span>";

        var value = parseFloat(this.value);
        var decimals = value >= 1 ? 100 : 10000;
        return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" +
               series.name().replace(/^SMA\(/,'MA(') + ":</span> " + Math.round(this.value*decimals)/decimals;
      }
    });
  
    var paginator = plot.legend().paginator();
    //paginator.layout("vertical");
    paginator.orientation("right");
    //paginator.padding(-10);
    paginator.fontSize(8);
    paginator.fontWeight(600);

  	plot.xAxis().height(0);
  	indicatorPlot.xAxis().height(0);
    indicatorPlot.yAxis().drawLastLabel(false);
    indicatorPlot.yAxis().drawFirstLabel(false);
    indicatorPlot.yScale().alignMaximum(false);
    indicatorPlot.yScale().alignMinimum(false);
    indicatorPlot.legend().padding().left(0);
    indicatorPlot.legend().padding().top(3);
    indicatorPlot.legend().padding().bottom(2);
    indicatorPlot.legend().padding().right(0);
	indicatorPlot.legend().fontSize(7);
    indicatorPlot.legend().maxHeight("25%");
    indicatorPlot.legend().title().useHtml(true);
    // configure the format of the legend title
    indicatorPlot.legend().titleFormat("");
	indicatorPlot.legend().useHtml(true);
    indicatorPlot.legend().itemsFormat(function() {
      var series = this.series;
      var value = Math.abs(parseFloat(this.value));
      var decimals = value >= 1 ? 100 : 10000;
      return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" +
			series.name() + ":</span> " + Math.round(this.value*decimals)/decimals;
    });
	
	var macdIndicator = indicatorPlot.macd(mapping);
	// et series type for histogram series.
	macdIndicator.histogramSeries("column");
	macdIndicator.histogramSeries().normal().fill('green .1').stroke('green .2');
	macdIndicator.histogramSeries().normal().negativeFill('red .1').negativeStroke('red .2');
	macdIndicator.macdSeries().stroke('#64b5f6');
	macdIndicator.signalSeries().stroke('#ef8f00');
	macdIndicator.histogramSeries().legendItem(false);
	macdIndicator.histogramSeries().tooltip().enabled(false);

  	var secondPlot = chart.plot(2);
	yScale = secondPlot.yScale();
	yScale.minimum(5);
	yScale.maximum(95);   
    secondPlot.height('30%');
    secondPlot.xAxis().labels().fontSize(9);
    secondPlot.xAxis().minorLabels().fontSize(9);
    secondPlot.yAxis().orientation('right');
    secondPlot.legend().padding().left(0);
    secondPlot.legend().padding().top(3);
    secondPlot.legend().padding().bottom(2);
    secondPlot.legend().padding().right(0);
    secondPlot.legend().fontSize(7);
    secondPlot.legend().maxHeight("25%");
    secondPlot.legend().title().useHtml(true);
    // configure the format of the legend title
    secondPlot.legend().titleFormat("");
	secondPlot.legend().useHtml(true);
    secondPlot.legend().itemsFormat(function() {
      var series = this.series;
	  var type = series.getType();
      return "<span style='color:" + (document.documentElement.getAttribute('data-theme')==='dark' ? '#cccccc' : '#455a64') + ";font-weight:600'>" +
			series.name() + ":</span> " + Math.round(parseFloat(this.value)*100)/100;
    });
  	secondPlot.yGrid().enabled(true);
    secondPlot.yGrid().stroke({dash: "1 5", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
  	secondPlot.xMinorGrid().enabled(true);
  	secondPlot.xMinorGrid().stroke({dash: "1 3", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
    secondPlot.xGrid().stroke((document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc'));
    
  	plot.yAxis().labels().fontSize(8);
  	plot.yAxis(1).labels().fontSize(7);
  	indicatorPlot.yAxis().labels().fontSize(8);
  	secondPlot.yAxis().labels().fontSize(8);
  	
    chart.listen('chartDraw', function () {
        // go to through all labels
        for (var i = 0; i < plot.yAxis().labels().getLabelsCount(); i++) {
            value = plot.yAxis().scale().ticks().get()[i];
            if (value < 0.01 && Math.round(value * 1000) / 1000 != value)
            {
                var label = plot.yAxis().labels().getLabel(i);
                if (label !== null)
                {
                    label.fontSize(6.5);
                    //label.background().enabled(true).stroke("black");
                    label.draw();
                }
            }
        }
        
        for (var i = 0; i < indicatorPlot.yAxis().labels().getLabelsCount(); i++) {
            value = indicatorPlot.yAxis().scale().ticks().get()[i];
            if (value < 0.01 && Math.round(value * 1000) / 1000 != value)
            {
                var label = indicatorPlot.yAxis().labels().getLabel(i);
                if (label !== null)
                {
                    label.fontSize(6.5);
                    //label.background().enabled(true).stroke("black");
                    label.draw();
                }
            }
        }
    });
    
    var MACDpaginator = indicatorPlot.legend().paginator();
    //paginator.layout("vertical");
    MACDpaginator.orientation("right");
    //MACDpaginator.padding(-10);
    MACDpaginator.fontSize(8);
    MACDpaginator.fontWeight(600);
    
    var RSIpaginator = secondPlot.legend().paginator();
    //paginator.layout("vertical");
    RSIpaginator.orientation("right");
    RSIpaginator.padding(-10);
    //RSIpaginator.fontSize(8);
    RSIpaginator.fontWeight(600);

  	var upperMarker = createMarkers(secondPlot, 70, '#f00', '');
 	var lowerMarker = createMarkers(secondPlot, 30, '#5faf5f', '');
  	var midMarker = createMarkers(secondPlot, 50, '#999', '');            
    // create RSI indicator with period 14
    var rsi = secondPlot.rsi(mapping, 14).series();
    rsi.stroke('#64b5f6');
    
    plot.yAxis().ticks().enabled(false);
    plot.yAxis(1).ticks().enabled(false);
    indicatorPlot.yAxis().ticks().enabled(false);
    secondPlot.yAxis().ticks().enabled(false);
  
	// enabled x-grid/y-grid
	plot
		.xGrid(true)
		.yGrid(true);

	// set orientation y-axis to the right side
	plot.yAxis().orientation('right');
	plot.yAxis(1).orientation('left');
  	plot.yGrid().enabled(true);
    plot.yGrid().stroke({dash: "1 5", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
  	plot.xMinorGrid().enabled(true);
  	plot.xMinorGrid().stroke({dash: "1 5", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
  	plot.height('40%');
  
  	// enabled x-grid/y-grid
	indicatorPlot
		.xGrid(true)
		.yGrid(true);
	indicatorPlot.height('22%');
	// set orientation y-axis to the right side
	indicatorPlot.yAxis().orientation('right');
    indicatorPlot.yGrid().enabled(true);
    indicatorPlot.yGrid().stroke({dash: "1 5", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
  	indicatorPlot.xMinorGrid().enabled(true);
  	indicatorPlot.xMinorGrid().stroke({dash: "1 3", color: (document.documentElement.getAttribute('data-theme')==='dark' ? '#3a3a4e' : '#cccccc')});
  
	// create candlestick series on the plot
	var aaplSeries = plot.candlestick(mapping);
	// set series settings
	aaplSeries.name(stockcode)
		.zIndex(50);
	aaplSeries.risingFill('green', 0.5)
		.fallingFill('red', 0.5)
		.risingStroke('green', 0.5)
		.fallingStroke('red', 0.5);

    aaplSeries.legendItem().iconType('rising-falling');
    if (hideStockPriceValues)
        aaplSeries.legendItem().enabled(false);
    
    // map loaded data
	var mapping1 = dataTable.mapAs({'value': 4});

    // create SMA indicators with period 20
    var sma20 = plot.sma(mapping1, 20).series();
    sma20.stroke('#ee5fb1');

    // create SMA indicators with period 50
    var sma50 = plot.sma(mapping1, 50).series();
    sma50.stroke('#5FB1EE');  
    var sma50data = sma50.data();
    var selectable = sma50data.createSelectable();
    selectable.selectAll();

    // Get iterator.
    var iterator = selectable.getIterator();
    var list_sma50 = [];
    while (iterator.advance()) {
        list_sma50.push([iterator.getKey(), iterator.get('value')]);
    }
    
    selectable = mapping1.createSelectable().selectAll();
    iterator = selectable.getIterator();
    var list_close = [];
    while (iterator.advance()) {
        list_close.push([iterator.getKey(), parseFloat(iterator.get('value'))]);
    }
    
    selectable = mapping.createSelectable();
    selectable.select(0, selectable.length - 1, 'week', 1);
    iterator = selectable.getIterator();
    var list_close_week = [];
    while (iterator.advance()) {
        list_close_week.push([iterator.getKey(), parseFloat(iterator.get('value'))]);
    }
    
    selectable = mapping.createSelectable();
    selectable.select(0, selectable.length - 1, 'month', 1);
    iterator = selectable.getIterator();
    var list_close_month = [];
    while (iterator.advance()) {
        list_close_month.push([iterator.getKey(), parseFloat(iterator.get('value'))]);
    }
    
    tooltipFormat(list_close, list_close_week, list_close_month);
                
    var above_below = -1;
    var idx;
    var lookback = 10; // consecutive lookback close > ma50 or close < ma50
    for (idx = list_close.length - 1; idx >= lookback - 1; idx--)
    {
        /*if (list_close[idx][1] >= list_sma50[idx][1] &&
            list_close[idx - 1][1] >= list_sma50[idx - 1][1] &&
            list_close[idx - 2][1] >= list_sma50[idx - 2][1] &&
            list_close[idx - 3][1] >= list_sma50[idx - 3][1] &&
            list_close[idx - 4][1] >= list_sma50[idx - 4][1] &&
            list_close[idx - 5][1] >= list_sma50[idx - 5][1] &&
            list_close[idx - 6][1] >= list_sma50[idx - 6][1] &&
            list_close[idx - 7][1] >= list_sma50[idx - 7][1] &&
            list_close[idx - 8][1] >= list_sma50[idx - 8][1] &&
            list_close[idx - 9][1] >= list_sma50[idx - 9][1])
        {
            above_below = 1;
            break;
        }
        else if (list_close[idx][1] < list_sma50[idx][1] &&
            list_close[idx - 1][1] < list_sma50[idx - 1][1] &&
            list_close[idx - 2][1] < list_sma50[idx - 2][1] &&
            list_close[idx - 3][1] < list_sma50[idx - 3][1] &&
            list_close[idx - 4][1] < list_sma50[idx - 4][1] &&
            list_close[idx - 5][1] < list_sma50[idx - 5][1] &&
            list_close[idx - 6][1] < list_sma50[idx - 6][1] &&
            list_close[idx - 7][1] < list_sma50[idx - 7][1] &&
            list_close[idx - 8][1] < list_sma50[idx - 8][1] &&
            list_close[idx - 9][1] < list_sma50[idx - 9][1])
        {
            above_below = 0;
            break;
        }*/
        var gt = 0, lt = 0;
        for (var i = 0; i < lookback; i++)
        {
            if (list_close[idx - i][1] >= list_sma50[idx - i][1])
                gt++;
            else
                lt++;
        }
        
        if (gt == lookback)
        {
            above_below = 1;
            break;
        }
        else if (lt == lookback)
        {
            above_below = 0;
            break;
        }
    }
    
    var crossoveridx;
    for (var i = idx; i >= 1; i--)
    {
        if (above_below == 1)
        {
            if (list_close[i-1][1] <= list_sma50[i-1][1])
            {
                crossoveridx = i;
                break;
            }
        }
        else if (above_below == 0)
        {
            if (list_close[i-1][1] >= list_sma50[i-1][1])
            {
                crossoveridx = i;
                break;
            }
        }
        else
            break;
    }            

    var sd = stdev(list_close, list_close.length > 100 ? list_close.length - 100 : 0);
    sd += 6;
    var percentmove = sd > 15 ? 15 : (sd < 8 ? 8 : sd);
    var barresult = findPeaksAndTroughsBarFromEnd(list_close, percentmove);
    
    var startindex = -1;
    var minbars = 15;
    if ((above_below == 1 && barresult.troughs.length > 0) || (above_below == 0 && barresult.peaks.length > 1 && barresult.peaks[0] <= minbars && list_close[list_close.length - 1 - barresult.peaks[0]][1] > list_close[list_close.length - 1 - barresult.peaks[1]][1])) // above 50ma or last peak > 2nd last peak
    {
        for (var i = 0; i < barresult.troughs.length; i++)
        {
            startindex = barresult.troughs[i];
            if (startindex > list_close.length - crossoveridx && startindex > minbars) // if less than 15 days, use the next trough
                break;
        }
    }
    else if ((above_below == 0 && barresult.peaks.length > 0) || (above_below == 1 && barresult.troughs.length > 1 && barresult.troughs[0] <= minbars && list_close[list_close.length - 1 - barresult.troughs[0]][1] < list_close[list_close.length - 1 - barresult.troughs[1]][1])) // below 50ma or last trough < 2nd last trough
    {
        for (var i = 0; i < barresult.peaks.length; i++)
        {
            startindex = barresult.peaks[i];
            if (startindex > list_close.length - crossoveridx && startindex > minbars)
                break;
        }
    }
    
    var r2 = findLineByLeastSquaresAndSE(list_close, list_close.length - startindex - 1);
    
    if (r2 !== null && r2[0].length > 0 && !isNaN(r2[1]))
    {
        var annotation = plot.annotations();
        var channel = annotation.trendChannel({
            // X - part of the first anchor
            xAnchor: r2[0][0][0],
            // Y - part of the first anchor
            valueAnchor: r2[0][0][1] + r2[1],
            // X - part of the second anchor
            secondXAnchor: r2[0][r2[0].length - 1][0],
            // Y - part of the second anchor
            secondValueAnchor: r2[0][r2[0].length - 1][1] + r2[1],
            // X - part of the third anchor
            thirdXAnchor: r2[0][0][0],
            // Y - part of the third anchor
            thirdValueAnchor: r2[0][0][1] - r2[1],
            // set stroke settings
            stroke: '1 purple',
            // set fill settings
            fill: 'orange 0.05',
            // set hover fill/stroke settings
            hovered: {
                fill: 'orange 0.05',
                stroke: '1 purple'
            }

        });
        channel.allowEdit(false);
        
        // create annotation line
        var line = annotation.line({
            // X - part of the first anchor
            xAnchor: r2[0][0][0],
            // Y - part of the first anchor
            valueAnchor: r2[0][0][1],
            // X - part of the second anchor
            secondXAnchor: r2[0][r2[0].length - 1][0],
            // Y - part of the second anchor
            secondValueAnchor: r2[0][r2[0].length - 1][1],
            stroke: {
                thickness: 1,
                color :'purple',
                dash: '3 3'
            },
            // set hover stroke settings
            hovered: {
                thickness: 1,
                color :'purple',
                dash: '3 3'
            }                
        });
        line.allowEdit(false);
    }
    
  	// create SMA indicators with period 250
    var sma250 = plot.sma(mapping1, longMAperiod).series();
    sma250.stroke('#bfFF21');  
  
    var volumeMapping = dataTable.mapAs({'volume': 5})
	// create volume series on the plot

    selectable = volumeMapping.createSelectable().selectAll();
    iterator = selectable.getIterator();
    var list_volume = [];
    while (iterator.advance()) {
        list_volume.push([iterator.getKey(), parseFloat(iterator.get('volume'))]);
    }
    
  	var volumeMaIndicator = plot.volumeMa(volumeMapping, 20, 'sma', 'column', 'splineArea');
    var maSeries = volumeMaIndicator.maSeries();
    maSeries.stroke('black');
    maSeries.fill('grey .1');
    volumeMaIndicator.volumeSeries('column');

	// set series settings
	volumeMaIndicator.volumeSeries().name('Vol')
		.zIndex(100)
		.maxHeight('33%')
		.bottom(0);
	volumeMaIndicator.volumeSeries().legendItem({
		enabled: !hideStockPriceValues,
		iconEnabled: !hideStockPriceValues,
		textOverflow: ''
	});
    if (hideStockPriceValues)
        volumeMaIndicator.volumeSeries().tooltip().enabled(false);
  
  	// set series settings
	maSeries.name('Vol MA(20)')
		.zIndex(100)
		.maxHeight('33%')
		.bottom(0);
	maSeries.legendItem({
		iconEnabled: true,
		textOverflow: ''
	});

	// create a logarithmic scale
	var customScale = anychart.scales.linear();
	// sets y-scale
	volumeMaIndicator.volumeSeries().yScale(customScale);
  	maSeries.yScale(customScale);
	// set volume rising and falling stroke settings
	volumeMaIndicator.volumeSeries().risingStroke('grey .3');
	volumeMaIndicator.volumeSeries().fallingStroke('grey .3');

	// set volume rising and falling fill settings
	volumeMaIndicator.volumeSeries().risingFill('grey .2');
	volumeMaIndicator.volumeSeries().fallingFill('grey .2');            

    volumeMaIndicator.volumeSeries().stroke('grey .3');
    volumeMaIndicator.volumeSeries().fill('grey .2');
    
	// set chart selected date/time range
	//chart.selectRange('2016-07-01', '2016-12-30');

	// set container id for the chart
	chart.container(stockcontainer);
    window._anystockContainerId = stockcontainer;
    window._anystockRangeContainerId = stockrangecontainer;
    applyAnystockTheme(chart, [plot, indicatorPlot, secondPlot]);

    window._anystockChart = chart;
    window._anystockPlots = [plot, indicatorPlot, secondPlot];

    if (!window._anystockThemeBound) {
        window._anystockThemeBound = true;
        document.documentElement.addEventListener('themechange', function() {
            applyAnystockTheme(window._anystockChart, window._anystockPlots);
        });
    }

	// initiate chart drawing
	chart.draw();
    setTimeout(function() {
        applyAnystockTheme(chart, [plot, indicatorPlot, secondPlot]);
    }, 0);



	//var yScale = chart.plot(0).yScale();
    var xScale = chart.xScale();
    
    //var firstVisibleValue = null;
    //var lastVisibleValue = null;

    var firstVisibleTick = null;
    var lastVisibleTick = null;

    //after every scroll change recalculate reference points
    //and reformat tooltip and legend
    chart.listen('selectedrangechange', function() {
        getVisibleValues();

        var barcount = 0;
        var minVisibleClose = 100000000000;
        var maxVisibleClose = -100000000000;
        var firstVisibleClose = -100000000000;
        var isFound_close = false;
        var isFound_volume = false;
        for (var i = list_close.length - 1; i >= 0; i--)
        {
            if (firstVisibleTick <= list_close[i][0] && lastVisibleTick >= list_close[i][0])
            {
                barcount++;
                isFound_close = true;
                if (list_close[i][1] > maxVisibleClose)
                    maxVisibleClose = list_close[i][1];
                    
                if (list_close[i][1] < minVisibleClose)
                    minVisibleClose = list_close[i][1];
                    
                firstVisibleClose = list_close[i][1];
            }
            else if (isFound_close)
                break;
        }
        
        var unit = this.grouping().getCurrentDataInterval().unit;
        if (unit == 'week')
        {
            var isFound_weeklyclose = false;
            for (var i = list_close_week.length - 1; i >= 0; i--)
            {
                if (firstVisibleTick <= list_close_week[i][0] && lastVisibleTick >= list_close_week[i][0])
                {
                    isFound_weeklyclose = true;
                    firstVisibleClose = list_close_week[i][1];
                }
                else if (isFound_weeklyclose)
                    break;
            }
        }
        else if (unit == 'month')
        {
            var isFound_monthlyclose = false;
            for (var i = list_close_month.length - 1; i >= 0; i--)
            {
                if (firstVisibleTick <= list_close_month[i][0] && lastVisibleTick >= list_close_month[i][0])
                {
                    isFound_monthlyclose = true;
                    firstVisibleClose = list_close_month[i][1];
                }
                else if (isFound_monthlyclose)
                    break;
            }
        }
        plot.yAxis(1).labels().format(function() {
            var value = this.value;
            if (firstVisibleClose > -100000000000)
            {
                value = rounding((value / firstVisibleClose - 1) * 100, 1);
                if (value == -100)
                    plot.yAxis(1).labels().fontSize(6.5);
                else if (value >= 20000)
                    plot.yAxis(1).labels().fontSize(4.5);
                else if (value >= 10000)
                    plot.yAxis(1).labels().fontSize(4.9);
                else if (value >= 2000)
                    plot.yAxis(1).labels().fontSize(5.5);
                else if (value >= 1000)
                    plot.yAxis(1).labels().fontSize(6);
                else if (plot.yAxis(1).labels().fontSize() != 6.5)
                    plot.yAxis(1).labels().fontSize(7);
                
                if (this.value == plot.yScale().minimum())
                    return value + "\n%";
                else
                    return value;
            }
            else
                return '';
        });
        
        var bin_count = barcount == 0 ? 5 : (barcount <= 5 ? barcount : 5 * Math.ceil(Math.log10(barcount)));
        var binHeight = (maxVisibleClose - minVisibleClose) / bin_count;
        var bin = Array(bin_count);
        
        if (isFound_close)
        {
            for (var j = 0; j < bin_count; j++)
            {
                bin[j] = 0;
            }
    
            var maxbin = 0;
            for (i = list_volume.length - 1; i >= 0; i--)
            {
                if (firstVisibleTick <= list_close[i][0] && lastVisibleTick >= list_close[i][0])
                {
                    isFound_volume = true;
                    
                    var index = Math.floor((list_close[i][1] - minVisibleClose) / binHeight);
                    {
                        if (index == bin_count)
                            index--;
                            
                        bin[index] += list_volume[i][1];
                            
                        if (bin[index] > maxbin)
                            maxbin = bin[index];
                    }
                }
                else if (isFound_volume)
                    break;
            }

            if (isFound_volume)
            {
                // access the annotations() object of the plot to work with annotations
                var controller = plot.annotations();
        
                var annotationsCount = controller.getAnnotationsCount();
                // remove the last annotation
                for (i = annotationsCount - 1; i > 1; i--)
                    controller.removeAnnotationAt(i);
        
                var tickRange = (lastVisibleTick - firstVisibleTick) * 0.45;
        
                for (j = 0; j < bin_count; j++)
                {
                    var x2Tick = bin[j]/maxbin * tickRange + firstVisibleTick;
                    drawRectangle(controller, new Date(firstVisibleTick).toString(), new Date(x2Tick).toString(), minVisibleClose + j * binHeight, minVisibleClose + (j + 1) * binHeight);
                }
            }
        }
    });

    function drawRectangle(controller, x1, x2, y1, y2) {
        // create a Rectangle annotation
        controller.rectangle({
            xAnchor: x1,
            valueAnchor: y1,
            secondXAnchor: x2,
            secondValueAnchor: y2,
            normal: {
                fill: "#0000ff 0.1",
                stroke: "0 #ff0000"
            },
            allowEdit: false
        });
    }
    
    function getVisibleValues() {
        // Gets scale minimum.
        var minimum = xScale.getMinimum();
        var maximum = xScale.getMaximum();
    
        //select data from mappings
        var selectableX = MappingX.createSelectable();
        // Sets value for search.
        var selectX0 = selectableX.search(minimum, "nearest");
        var selectX1 = selectableX.search(maximum, "nearest");
        // get values in first visible points
        var firstVisibleValue = selectX0.get('value');
        var lastVisibleValue = selectX1.get('value');
        firstVisibleTick = new Date(firstVisibleValue).getTime() - 43200000;
        lastVisibleTick = new Date(lastVisibleValue).getTime();
        //lastVisibleValue= new Date(((lastVisibleTick - firstVisibleTick) * 0.4) + (firstVisibleTick-0)).toString();
    }
    
    
    chart.selectRange("month", 6, "lastDate", true);
	// create range picker
	//var rangePicker = anychart.ui.rangePicker();
	// init range picker
	//rangePicker.render(chart);			
    
    // create range selector
	var rangeSelector = anychart.ui.rangeSelector();
	rangeSelector.target(chart);

    // Set ranges for range selector.
    rangeSelector.ranges([{      
        'type': 'unit',
        'unit': 'month',
        'count': 1,
        'anchor': 'last-date',
        'text': '1m'
    }, {      
        'type': 'unit',
        'unit': 'month',
        'count': 3,
        'anchor': 'last-date',
        'text': '3m'
    }, {      
        'type': 'unit',
        'unit': 'month',
        'count': 6,
        'anchor': 'last-date',
        'text': '6m'
    }, {
        'type': 'ytd',
        'text': 'YTD'
    }, {      
        'type': 'unit',
        'unit': 'year',
        'count': 1,
        'anchor': 'last-date',
        'text': '1y'
    }, {      
        'type': 'unit',
        'unit': 'year',
        'count': 2,
        'anchor': 'last-date',
        'text': '2y'
    }, {
        'type': 'unit',
        'unit': 'year',
        'count': 8,
        'anchor': 'last-date',
        'text': '8y'
    }, {
        'type': 'max',
        'text': '10y'
    }
    ]);            

	// init range selector
    if (showRangeSelector) {
	    rangeSelector.render(document.getElementById(stockrangecontainer));
        patchAnystockRangeSelector(anystockTheme());
    }
}

var markersCount = 0;
function createMarkers(plot, value, color, text) {
  // create and setup line marker
  var marker = plot.lineMarker(markersCount);
  marker
    .value(value)
    .stroke(color, 1);

  // create and setup text marker
  plot.textMarker(markersCount).text(text)
    .value(value)
    .align('left')
    .anchor('left-bottom')
    .padding(5)
    .fontColor(color);

  markersCount++;
  return marker;
}

function shareWithFacebook(url, stockcode, name) {

    // Share chart with Facebook.
    chart.shareWithFacebook('360MiQ.com', url, stockcode, name + ' detail info');
}    

function getDayOfWeek(date) {
  var dayOfWeek = new Date(date).getDay();    
  return isNaN(dayOfWeek) ? null : ['Sun', 'Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat'][dayOfWeek];
}

function stdev(list_close, index)
{
    var sum_x = 0;
    var sum_y = 0;
    var sum_xy = 0;
    var sum_xx = 0;
    var count = 0;

    /*
     * We'll use those variables for faster read/write access.
     */
    var x = 0;
    var y = 0;
    var values_length = list_close.length;

    /*
     * Nothing to do.
     */
    if (values_length === 0) {
        return null;
    }

    /*
     * Calculate the sum for each of the parts necessary.
     */
    for (var v = index; v < values_length; v++) {
        x = v - index + 1;
        y = parseFloat(list_close[v][1]);
        sum_x += x;
        sum_y += y;
        sum_xx += x*x;
        sum_xy += x*y;
        count++;
    }

    /*
     * Calculate m and b for the formular:
     * y = x * m + b
     */
    var avg = sum_y/count;
    var m = (count*sum_xy - sum_x*sum_y) / (count*sum_xx - sum_x*sum_x);
    var b = avg - (m*sum_x)/count;
    

    /*
     * We will make the x and y result line now
     */
    //var result_values = [];
    var sumdiff = 0;
    for (var v = index; v < values_length; v++) {
        //x = list_close[v][0];
        y = (v-index+1) * m + b;
        //result_values.push([x, y]);
        sumdiff += (list_close[v][1] - avg) * (list_close[v][1] - avg);
    }
    
    return Math.sqrt(sumdiff/avg/count);// /Math.sqrt(count);
}

function findLineByLeastSquaresAndSE(list_close, index) {
    var sum_x = 0;
    var sum_y = 0;
    var sum_xy = 0;
    var sum_xx = 0;
    var count = 0;

    /*
     * We'll use those variables for faster read/write access.
     */
    var x = 0;
    var y = 0;
    var values_length = list_close.length;

    /*
     * Nothing to do.
     */
    if (values_length === 0) {
        return null;
    }

    /*
     * Calculate the sum for each of the parts necessary.
     */
    for (var v = index; v < values_length; v++) {
        x = v - index + 1;
        y = parseFloat(list_close[v][1]);
        sum_x += x;
        sum_y += y;
        sum_xx += x*x;
        sum_xy += x*y;
        count++;
    }

    /*
     * Calculate m and b for the formular:
     * y = x * m + b
     */
    var avg = sum_y/count;
    var m = (count*sum_xy - sum_x*sum_y) / (count*sum_xx - sum_x*sum_x);
    var b = avg - (m*sum_x)/count;
    

    /*
     * We will make the x and y result line now
     */
    var result_values = [];
    var sumdiff = 0;
    for (var v = index; v < values_length; v++) {
        x = list_close[v][0];
        y = (v-index+1) * m + b;
        result_values.push([x, y]);
        sumdiff += (list_close[v][1] - avg) * (list_close[v][1] - avg);
    }
    var times = Math.pow(1/Math.abs(m), 0.12) * (7 + (values_length - index)/90);
    var se = times * Math.sqrt(sumdiff/count)/Math.sqrt(count);
    
    var inliner1 = 0, inliner2 = 0, inliner3 = 0, inliner4 = 0, inliner5 = 0, inliner6 = 0, inliner7 = 0, inliner8 = 0, inliner9 = 0, inliner10 = 0,
        inliner11 = 0, inliner12 = 0, inliner13 = 0, inliner14 = 0, inliner15 = 0, inliner16 = 0;
    for (var v = 0; v < result_values.length - 3; v++) // don't count latest 3 bars, let it breakout the channel if it does
    {
        var x = list_close[list_close.length - result_values.length + v][1];
        var mid = result_values[v][1];
        var c = x - mid;
        /*if ((c > 0 && c < se*.2) || (c < 0 && c > - se*.2))
        {
            inliner1++; inliner2++; inliner3++; inliner4++; inliner5++; inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.25) || (c < 0 && c > -se*.25))
        {
            inliner2++; inliner3++; inliner4++; inliner5++; inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.3) || (c < 0 && c > -se*.3))
        {
            inliner3++; inliner4++; inliner5++; inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.35) || (c < 0 && c > -se*.35))
        {
            inliner4++; inliner5++; inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }        
        else if ((c > 0 && c < se*.4) || (c < 0 && c > -se*.4))
        {
            inliner5++; inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.45) || (c < 0 && c > -se*.45))
        {
            inliner6++; inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }        
        else*/ if ((c > 0 && c < se*.5) || (c < 0 && c > -se*.5))
        {
            inliner7++; inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.55) || (c < 0 && c > -se*.55))
        {
            inliner8++; inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.6) || (c < 0 && c > -se*.6))
        {
            inliner9++; inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.65) || (c < 0 && c > -se*.65))
        {
            inliner10++; inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.7) || (c < 0 && c > -se*.7))
        {
            inliner11++; inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.75) || (c < 0 && c > -se*.75))
        {
            inliner12++; inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.8) || (c < 0 && c > -se*.8))
        {
            inliner13++; inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.85) || (c < 0 && c > -se*.85))
        {
            inliner14++; inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.9) || (c < 0 && c > -se*.9))
        {
            inliner15++; inliner16++;
        }
        else if ((c > 0 && c < se*.95) || (c < 0 && c > -se*.95))
        {
            inliner16++;
        }
    }
    /*if (inliner1 == count)
        se *= 0.2;
    else if (inliner2 == count)
        se *= 0.25;
    else if (inliner3 == count)
        se *= 0.3;
    else if (inliner4 == count)
        se *= 0.35;
    else if (inliner5 == count)
        se *= 0.4;
    else if (inliner6 == count)
        se *= 0.45;
    else*/ 
    count-=3; // don't count latest 3 bars, let it breakout the channel if it does
    if (inliner7 == count)
        se *= 0.5;
    else if (inliner8 == count)
        se *= 0.55;
    else if (inliner9 == count)
        se *= 0.6;
    else if (inliner10 == count)
        se *= 0.65;
    else if (inliner11 == count)
        se *= 0.7;
    else if (inliner12 == count)
        se *= 0.75;
    else if (inliner13 == count)
        se *= 0.8;
    else if (inliner14 == count)
        se *= 0.85;
    else if (inliner15 == count)
        se *= 0.9;
    else if (inliner16 == count)
        se *= 0.95;
        
    return [result_values, se];
}
