<?php
    $map = isset($_GET["map"]) ? $_GET["map"] : '';
    $longMA = 200;
    $data = strtoupper(isset($_GET["data"]) ? $_GET["data"] : 'NYSE');
    $sort = isset($_GET["sort"]) ? $_GET["sort"] : '';
    $showbar = isset($_GET["showbar"]) ? $_GET["showbar"] : '';
    $highlight = isset($_GET["highlight"]) ? $_GET["highlight"] : '';
    $short_Margin = "";
    $country = "";
    $indexname = "";
    
    if ($data == "HKEX")
    {
        $exchangeName = "Hong Kong";
        $benchmark = "2800.HK";
        $benchmarkname = "Tracker Fund (2800.HK)";     
        $longMA = 250;
        $valuationIdx = "HSI";
        $ad = "P";
        $short_Margin = "HKEXShortSelling";
        $indexname = "HSI";
    }
    else if ($data == "SZSE")
    {
        $exchangeName = "Shenzhen";
        $benchmark = "159903.SZ";
        $benchmarkname = "SZSE Component Index ETF (159903.SZ)";
        $longMA = 250;
        $valuationIdx = "SZSE";
        $ad = "S";
        $short_Margin = "SZSEShortMargin";
        $indexname = "Shenzhen Composite";
    }
    else if ($data == "SHSE")
    {
        $exchangeName = "Shanghai";
        $benchmark = "510500.SH";
        $benchmarkname = "CSI 500 Index ETF (510500.SH)";      
        $longMA = 250;
        $valuationIdx = "SSEC";
        $ad = "T";
        $short_Margin = "SHSEShortMargin";
        $country = "China";
        $indexname = "Shanghai Composite";
    }
    else if ($data == "NYSE" || $data == "")
    {
        $data = "NYSE";
        $exchangeName = "NYSE";
        $benchmark = "SPY";
        $benchmarkname = "S&P 500 ETF (SPY)";      
        $valuationIdx = "SP500";
        $ad = "Q";
        $short_Margin = "NYSEMarginDebt";
        $country = "US";
        $indexname = "SPX";
    }
    else if ($data == "NASDAQ")
    {
        $exchangeName = "Nasdaq";
        $benchmark = "QQQ";
        $benchmarkname = "Invesco QQQ Trust (QQQ)";     
        $valuationIdx = "NDX";
        $ad = "R";
        $short_Margin = "NASDAQMarginDebt";
        $indexname = "Nas Composite";
    }
    else if ($data == "LSE")
    {
        $exchangeName = "London";
        $benchmark = "ISF.L";
        $benchmarkname = "UK 100 ETF (ISF.L)";
        $valuationIdx = "FTSE100";
        $ad = "U";
        $indexname = "UK 100";
    }
    else if ($data == "ASX")
    {
        $exchangeName = "Australia";
        $benchmark = "STW.AX";
        $benchmarkname = "Australia 200 Fund (STW.AX)";
        $valuationIdx = "AS";
        $ad = "V";
        $indexname = "Australia 200";
    }
    else if ($data == "TSX")
    {
        $exchangeName = "Toronto TSX";
        $benchmark = "XIC.TO";
        $benchmarkname = "Toronto Composite Index ETF (XIC.TO)";
        $valuationIdx = "SPTSX";
        $ad = "W";
        $indexname = "Toronto Composite";
    }
    else if ($data == "NSE")
    {
        $exchangeName = "India NSE";
        $benchmark = "NIFTYIETF.N";
        $benchmarkname = "ICICI Prudential Nifty ETF (NIFTYIETF.N)";  
        $valuationIdx = "NSE";
        $ad = "Y";
        $indexname = "BSE Sensex";
    }
    else if ($data == "TYO")
    {
        $exchangeName = "Tokyo";
        $benchmark = "1306.T";
        $benchmarkname = "Nomura Topix Listed (1306.T)";
        $valuationIdx = "TPX";
        $ad = "Z";
        $indexname = "Japan 225";
    }
    else
    {
        header("Location: /error");
        exit();
    }

    function scatterChartNoteMarkup($type)
    {
        $descriptions = [
            "performance1" => "The 1-day Stock Performance vs Relative Volume scatter plot compares each stock's 1-day return with its 1-day trading volume relative to its 5-day average volume—roughly one trading day compared with one week. Each bubble represents a stock, and larger bubbles represent companies with a larger market capitalization. Stocks farther to the right are trading at higher relative volume, while their vertical position shows the size and direction of the 1-day return. Use the chart to find unusually active daily gainers, laggards and market clusters.",
            "performance5" => "The 5-day Stock Performance vs Relative Volume scatter plot compares each stock's 5-day return with its 5-day average trading volume relative to its 20-day average volume—roughly one trading week compared with one month. Each bubble represents a stock, and larger bubbles represent companies with a larger market capitalization. Stocks farther to the right have higher recent relative volume, while their vertical position shows the size and direction of the 5-day return. Use the chart to find weekly price moves supported by unusually strong or weak trading activity.",
            "performance20" => "The 20-day Stock Performance vs Relative Volume scatter plot compares each stock's 20-day return with its 20-day average trading volume relative to its 60-day average volume—roughly one trading month compared with three months. Each bubble represents a stock, and larger bubbles represent companies with a larger market capitalization. Stocks farther to the right have higher medium-term relative volume, while their vertical position shows the size and direction of the 20-day return. Use the chart to identify sustained monthly gainers, laggards and shifts in trading interest.",
            "highlow" => "This scatter plot shows where each stock sits within its 250-day trading range. The horizontal axis measures how far the price is above its 250-day low, while the vertical axis measures how far it is below its 250-day high. Each bubble represents a stock, with larger bubbles indicating larger market capitalization. Comparing both axes helps distinguish stocks trading near their highs, near their lows or between the two extremes.",
            "ma" => "This scatter plot compares each stock's percentage deviation from its 50-day moving average with its deviation from the longer-term 200-day or 250-day moving average used for that market. Positive values mean the price is above an average and negative values mean it is below. Each bubble represents a stock, with size reflecting market capitalization. The quadrants make it easier to spot stocks with aligned short- and long-term momentum or mixed trends.",
            "rsi" => "This scatter plot compares daily RSI on the horizontal axis with weekly RSI on the vertical axis. Each bubble represents a stock, and larger bubbles indicate larger market capitalization. Readings above 50 suggest stronger momentum for the relevant timeframe, while readings below 50 suggest weaker momentum. The upper-right and lower-left areas highlight stocks whose daily and weekly momentum are moving in the same direction."
        ];

        if (!isset($descriptions[$type])) {
            return "";
        }

        return '<div class="chartNote scatterChartNote"><div class="showNote">Show more</div><div class="noteWrapper collapsed">'
            . $descriptions[$type]
            . '</div></div>';
    }

    ob_start(function ($html) {
        return preg_replace_callback(
            '/(<div id="(bubblecontainer|tabubblecontainer)([A-C])[1-4]" style="width:auto;height:700px;"><\/div>)/',
            function ($matches) {
                $type = ["A" => "performance1", "B" => "performance5", "C" => "performance20"][$matches[3]];
                if ($matches[2] === "tabubblecontainer") {
                    $type = ["A" => "highlow", "B" => "ma", "C" => "rsi"][$matches[3]];
                }

                return $matches[1] . scatterChartNoteMarkup($type);
            },
            $html
        );
    });
?>
<!DOCTYPE html>
<html style="opacity: 1;">

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="<?php echo $exchangeName . " Market Indicators - 360MiQ.com"; ?>" />
    <meta name="description" content="<?php echo $exchangeName; ?> market indicators including breadth, sector performance, heatmaps, valuation bands and advance-decline data." />
    <meta property="og:description" content="<?php echo $exchangeName; ?> market indicators including breadth, sector performance, heatmaps, valuation bands and advance-decline data." />

    <title><?php echo $exchangeName . " Market Indicators - 360MiQ.com"; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="16x15" href="assets/img/360Logo_16.png">
    <link rel="icon" type="image/png" sizes="32x31" href="assets/img/360Logo_32.png">
    <link rel="icon" type="image/png" sizes="179x169" href="assets/img/360Logo_180.png">
    <link rel="icon" type="image/png" sizes="192x181" href="assets/img/360Logo_192.png">
    <link rel="icon" type="image/png" sizes="512x482" href="assets/img/360Logo_512.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://code.highcharts.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i,600,600i&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/simple-line-icons.min.css">
    <link rel="stylesheet" href="assets/css/card.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <link rel="stylesheet" href="assets/css/inlinehelp.css">
    <link rel="stylesheet" href="assets/css/help-tip2.css">
    <link rel="stylesheet" href="assets/css/toggleSwitch.css">
    <style>
        .masonry-grid {
            column-count: 5;
            column-gap: 1rem;
        }
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 1rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* Smooth transition */
			transform-style: preserve-3d; /* Enable 3D transforms */
        }
        .masonry-item:hover {
            box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.2); /* Shadow increase on hover */
        }
        .masonry-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        .masonry-item .content {
            padding: 15px;
        }
        .masonry-item a:hover {
            text-decoration: none; /* Ensure no underline on hover */
        }
        [data-theme="dark"] .masonry-item {
            background: var(--bg-card);
        }
        [data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label text,
        [data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label text tspan {
            fill: #fff !important;
            color: #fff !important;
            font-weight: 700 !important;
        }
        [data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label .highcharts-text-outline {
            fill: #2a2a3e !important;
            stroke: #2a2a3e !important;
        }
        #chartcontainerH1 .highcharts-yaxis .highcharts-axis-line,
        #chartcontainerH1 .highcharts-yaxis .highcharts-tick,
        #chartcontainerH1 .highcharts-plot-line,
        #chartcontainerH2 .highcharts-yaxis .highcharts-axis-line,
        #chartcontainerH2 .highcharts-yaxis .highcharts-tick,
        #chartcontainerH2 .highcharts-plot-line {
            stroke: #e6e6e6 !important;
        }
        [data-theme="dark"] #chartcontainerH1 .highcharts-yaxis .highcharts-axis-line,
        [data-theme="dark"] #chartcontainerH1 .highcharts-yaxis .highcharts-tick,
        [data-theme="dark"] #chartcontainerH1 .highcharts-plot-line,
        [data-theme="dark"] #chartcontainerH2 .highcharts-yaxis .highcharts-axis-line,
        [data-theme="dark"] #chartcontainerH2 .highcharts-yaxis .highcharts-tick,
        [data-theme="dark"] #chartcontainerH2 .highcharts-plot-line {
            stroke: #45475f !important;
        }
        [data-theme="dark"] #chartcontainerH2 .highcharts-series-1 .highcharts-graph,
        [data-theme="dark"] #chartcontainerH2 [stroke="green"] {
            stroke: #33ff99 !important;
        }
        [data-theme="dark"] #chartcontainerH2 .highcharts-yaxis-labels-1 text,
        [data-theme="dark"] #chartcontainerH2 .highcharts-yaxis-labels-1 tspan,
        [data-theme="dark"] #chartcontainerH2 .highcharts-legend-item:nth-child(2) text,
        [data-theme="dark"] #chartcontainerH2 .highcharts-legend-item:nth-child(2) tspan,
        [data-theme="dark"] #chartcontainerH2 [fill="green"] {
            color: #33ff99 !important;
            fill: #33ff99 !important;
        }
        @media (max-width: 768px) {
            .masonry-grid {
                column-count: 1;
            }
        }
        
        .pb-5, .py-5 {
            padding-bottom: 0rem !important;
        }
        .pt-5, .py-5 {
            padding-top: 2rem !important;
        }
    </style>
    <style>
        .masonry-item-mb {
          break-inside: avoid;
          margin-bottom: 1rem;
          background: #fff;
          border-radius: 10px;
          box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
          overflow: hidden;
          transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* Smooth transition for both */
          transform-style: preserve-3d; /* Enable 3D transforms */
        }
        .masonry-item-mb:hover {
          transform: scale(1.05); /* Original zoom effect */
          box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.2); /* Shadow increase on hover */
        }
        .masonry-item-mb img {
          width: 100%;
          height: auto;
          display: block;
        }
        .masonry-item-mb .content {
          padding: 15px;
        }
        .masonry-item-mb a:hover {
          text-decoration: none; /* Ensure no underline on hover */
        }
        [data-theme="dark"] .masonry-item-mb {
          background: var(--bg-card);
        }
    </style>
</head>

<body class="match-left-y-axis-to-labels">
<style>
.not-selectable {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
body.match-left-y-axis-to-labels .theme-left-y-axis-title,
body.match-left-y-axis-to-labels .theme-left-y-axis-title text,
body.match-left-y-axis-to-labels .theme-left-y-axis-title tspan,
body.match-left-y-axis-to-labels .theme-left-y-axis-labels text,
body.match-left-y-axis-to-labels .theme-left-y-axis-labels tspan,
body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-title,
body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-labels text,
body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-labels tspan {
  color: #000000 !important;
  fill: #000000 !important;
}
body.match-left-y-axis-to-labels .theme-left-y-axis-line,
body.match-left-y-axis-to-labels .theme-left-y-axis-tick,
body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome .highcharts-axis-line,
body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome .highcharts-tick {
  stroke: #000000 !important;
}
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-title,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-title text,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-title tspan,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-labels text,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-labels tspan,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-title,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-labels text,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome.highcharts-axis-labels tspan {
  color: #ffffff !important;
  fill: #ffffff !important;
}
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-line,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-tick,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome .highcharts-axis-line,
html[data-theme="dark"] body.match-left-y-axis-to-labels .theme-left-y-axis-monochrome .highcharts-tick {
  stroke: #ffffff !important;
}
.selectable {
    /* override parent */
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    user-select: text !important;
    /* keep cursor familiar */
    cursor: text;
}
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="assets/js/Utils.js"></script>
<!--script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/v8/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet">
<link href="https://cdn.anychart.com/releases/v8/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet"-->
<!--script src="https://code.jquery.com/jquery-3.5.1.js"></script //if dataTable weird, may need this js--> 
<script>
var marketCriticalRequests = {
    marketData: $.ajax({
        url: "/db_market_get.php?data=<?php echo rawurlencode($data); ?>",
        dataType: "text"
    }),
    featuredPosts: $.ajax({
        url: "db_featuredblogpost_get.php",
        dataType: "text"
    })
};
</script>
<style>
.irs {
	font-family: Montserrat
}
.irs--round .irs-from, .irs--round .irs-to, .irs--round .irs-single {
    background-color: #FF4B4B!important;
}
.irs--round .irs-from:before, .irs--round .irs-to:before, .irs--round .irs-single:before {
    border-top-color: #FF4B4B!important;
}

.irs--round .irs-bar {
	top: 35px;
	height: 6px;
	background-color: #FF4B4B
}
.irs--round .irs-handle {
	top: 26px;
	width: 24px;
	height: 24px;
	border: 4px solid #FF4B4B;
	background-color: white;
	border-radius: 24px;
	box-shadow: 0 1px 3px rgba(0, 0, 255, 0.3)
}
.irs--round .irs-single {
	font-size: 14px;
	line-height: 1;
	text-shadow: none;
	padding: 3px 5px;
	background-color: #FF4B4B;
	color: white;
	border-radius: 4px
}
.irs--round .irs-single:before {
	position: absolute;
	display: block;
	content: "";
	bottom: -6px;
	left: 50%;
	width: 0;
	height: 0;
	margin-left: -3px;
	overflow: hidden;
	border: 3px solid transparent;
	border-top-color: #FF4B4B
}
.irs--round .irs-min,
.irs--round .irs-max {
	font-size: 16px;
}

.irs--round .irs-from,
.irs--round .irs-to,
.irs--round .irs-single {
	font-size: 16px;
	font-weight: bold;
}
.irs--round .irs-grid-text {
	font-size: 14px;
	color: grey;
}
</style>
<style>
.dt-body-center{
    text-align: center;
}
</style>
<style>
.btn-group-xs > .btn, .btn-xs {
  padding: .25rem .4rem;
  font-size: .875rem;
  line-height: .5;
  border-radius: .2rem;
}

.nav-pills > li > a.active{
  background-color:#46b3e6 !important;
  color:white !important;
}

.nav-pills > li.active > a:hover {
  background-color:#46b3e6 !important;
  color:white !important;
}

.pill-link {
  color: #46b3e6;
}

.chartNote {
    text-align:left;
    padding-top: 8px;
    padding-right: 15px;
    padding-left: 15px;
    font-size: 15px;
    color: #333;
}

.noteWrapper {
  overflow: hidden;
  position: relative;
  max-height: 43px; /* collapsed height for 1.5 lines */
  transition: max-height 0.5s ease;
  font-size: 15px;
  line-height: 1.5;
  margin-bottom: 26px;
}

.noteWrapper.collapsed::after {
  content: "";
  pointer-events: none;
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 18px;
  background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
}

.noteWrapper.expanded {
  max-height: 500px; /* large enough to show full text */
}

.noteWrapper.expanded::after {
  display: none;
}

.showNote {
  font-size: 14px;
  font-weight: 550;
  cursor: pointer;
  margin: 6px 0 12px 0;
  user-select: none;
  color: #007bff;
}

</style>

<?php $page = 'market'; include "./header.php" ?>

<!--<div style="cursor: default;">
<div id="adblockerMSG" class="container">
  Our website is made possible by displaying online advertisements to our visitors.<br>
  Please consider supporting us by disabling your ad blocker.
</div>
<style>
#adblockerMSG {
display: none;
margin-top: 90px;

padding: 20px 10px;
background: #D30000;
text-align: center;
font-weight: bold;
color: #fff;
border-radius: 5px;
}
</style>

<script type="text/javascript">
var isIE = detectIE();

document.addEventListener('DOMContentLoaded', init, false);

function init(){
    if (!isIE)
    {
      adsBlocked(function(blocked){
        if(blocked){
          document.getElementById('adblockerMSG').style.display='block';
        } else {
          document.getElementById('adblockerMSG').innerHTML = 'ads are not blocked';
        }
      });
    }
    else
        document.getElementById('adblockerMSG').innerHTML = 'ads are not blocked';
}

function adsBlocked(callback){
  var testURL = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'

  var myInit = {
    method: 'HEAD',
    mode: 'no-cors'
  };

  var myRequest = new Request(testURL, myInit);

  fetch(myRequest).then(function(response) {
    return response;
  }).then(function(response) {
    console.log(response);
    callback(false)
  }).catch(function(e){
    console.log(e)
    callback(true)
  });
}
</script>
</div>-->
    <main class="page" style="cursor: default;">
        <section class="clean-block clean-form" style="padding: 0px 0px 30px;">
            <h1 class="sr-only"><?php echo $exchangeName." "; ?>Market Indicators - Market Breadth, Short Selling Ratio, Sector Performance, Market Heatmap, PE Band, Advance Decline Ratio</h1>
            <div id="featuredpost" class="container" style="padding: 0px 0px 0px;min-height:274px">
                <div class=" text-center container" style="margin: 70px 0 0;padding-right:0;padding-left:0">
                    <div class="container py-5" style="padding: 0px 0px 0px">
                        <div id="blogGrid" class="masonry-grid">
                            <div id="blogGridItem1" class="masonry-item" style="display:none">
                                <div id="featuredpostcontainer1"></div>
                                <div class="content" id="featuredposttitle1"></div>
                            </div>
                            <div id="blogGridItem2" class="masonry-item" style="display:none">
                                <div id="featuredpostcontainer2"></div>
                                <div class="content" id="featuredposttitle2"></div>
                            </div>
                            <div id="blogGridItem3" class="masonry-item" style="display:none">
                                <div id="featuredpostcontainer3"></div>
                                <div class="content" id="featuredposttitle3"></div>
                            </div>
                            <div id="blogGridItem4" class="masonry-item" style="display:none">
                                <div id="featuredpostcontainer4"></div>
                                <div class="content" id="featuredposttitle4"></div>
                            </div>
                            <div id="blogGridItem5" class="masonry-item" style="display:none">
                                <div id="featuredpostcontainer5"></div>
                                <div class="content" id="featuredposttitle5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding: 30px 0px 0px;">
                <div id="indicatorCard" style="margin: 70px 0 0;text-align:center;font-size:1.7rem;font-weight:500"><?php echo $exchangeName." "; ?>Market Indicators</div>
                <div class="card clean-card text-center container" style="padding-right:0;padding-left:0">
                    <div class="panel panel-default" style="margin: 5px 0 0;">
                        <ul class="nav nav-tabs panel-heading not-selectable">
                            <li class="nav-item"><a class="nav-link active mynav-link" role="tab" data-toggle="tab" href="#tab-1">Market Breadth&nbsp;<span class="help-tip mytooltip" data-tooltip="Market breadth refers to how many stocks fulfill a given condition (eg. Close > MA50). A benchmark index may fulfill a condition while less than half the stocks do. This would be a warning sign that the market is not as strong."></span></a></li>
                            <li class="nav-item" <?php if ($data != "HKEX" && $data != "SHSE" && $data != "SZSE" && $data != "NYSE" && $data != "NASDAQ") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-2"><?php if ($short_Margin == "SZSEShortMargin" || $short_Margin == "SHSEShortMargin") echo "Short Selling & Margin Debt"; else if ($short_Margin == "HKEXShortSelling") echo "Short Selling Ratio"; else echo "Margin Debt"; ?></a></li>
                            <li class="nav-item"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-3">Sector</a></li>
                            <li class="nav-item"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-4">Valuation&nbsp;<span class="help-tip mytooltip" data-tooltip="PE Band is computed using historical PE Ratio and price, so that it has the benefit of both fundamental and technical factors. The higher the price relative to the band the more expensive it is in terms of PE Ratio, and vice versa."></span></a></li>
                            <li class="nav-item"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-5">Adv Dec</a></li>
                            <li class="nav-item" <?php if ($data != "HKEX") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-6">Stock Connect Southbound</a></li>
                            <li class="nav-item" <?php if ($data != "HKEX") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-7">Housing</a></li>
                            <li class="nav-item" <?php if ($data != "NYSE" && $data != "SHSE") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-8">Sentiment</a></li>
                            <li class="nav-item" <?php if ($data != "NYSE") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-9">Since 1700</a></li>
                            <li class="nav-item" <?php if ($data != "HKEX") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-10">Since 1841</a></li>
                            <li class="nav-item" <?php if ($data != "NYSE") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-11">Gold</a></li>
                            <li class="nav-item" <?php if ($data != "HKEX") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-12">HSI + HSCEI + HSTECH Constituents</a></li>
                            <li class="nav-item" <?php if ($data != "NYSE" && $data != "NASDAQ") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-13">S&P 100 Constituents</a></li>
                            <li class="nav-item" <?php if ($data != "NYSE" && $data != "NASDAQ") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-14">Nasdaq 100 Constituents</a></li>
                        </ul>
                        <div class="tab-content panel-body">
                            <div class="tab-pane active" role="tabpanel" id="tab-1">
                                
                                            
                                <div id="pinnedpost" class="card clean-card text-center container" style="padding: 0;margin-bottom:16px">
                                  <div class=" text-center container" style="margin: 0 0 -4px 0;padding:8px 26px 0px 26px;">
                                    <div id="pinnedGridItem1">
                                      <div class="row masonry-item-mb" style="padding: 10px;">
                                        <div class="col-md-2 px-0">
                                          <div id="pinnedpostcontainer1">
                                              <a href="/blog/market-breadth-can-make-you-a-better-trader-proven-by-backtesting/"><img src="https://360miq.com/blog/wp-content/uploads/2021/01/MB.jpg" width="800" height="599" fetchpriority="high" decoding="async" style="width: 100%;height: auto"></a>
                                          </div>
                                        </div>
                                        <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
                                          <div class="content" id="pinnedposttitle1" style="margin-left: 20px;text-align:left;box-shadow:0 0">
                                              <a href="/blog/market-breadth-can-make-you-a-better-trader-proven-by-backtesting/">Market Breadth Can Make You a Better Trader (Proven by Backtesting)</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
            
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Market Breadth</h2>
                                <div>
                                    <!--<div id="loader-icon" style="padding:100px"><img src="assets/img/LoaderIcon.gif" /></div>-->
                                    <div id="loader-icon" style="margin:150px 150px 0px;" class="spinner-border text-warning" role="status">
                                      <span class="sr-only">Loading...</span>
                                    </div>
                                    <div id="loader-tip" style="margin:30px 0px 0px;color:#888">
                                      <b>Tip:</b>&nbsp;Click the chart legend to show/hide the data series.
                                    </div>
                                    <table id="failedtable" style="width:100%;">
                                        <tr>
                                            <td id="failedcell" class="just-to-show" style="text-align:center; valign:middle; width:100%; height:380px;"></td>
                                        </tr>
                                    </table>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Short MA > Long MA Market Breadth</h3>
                                    <div id="chartcontainerA1" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A1Note">
                                      <span>These market breadth indicators show the percentage of stocks whose shorter-term moving average is above a longer-term one — a sign of trend strength.</span>
                                      <ul style="padding-left: 20px; margin-top: 6px; margin-bottom: 6px;">
                                        <li><strong>MA3 &gt; MA18</strong>: short-term momentum (3-day vs. 18-day average)</li>
                                        <li><strong>MA10 &gt; MA50</strong>: medium-term trend strength (10-day vs. 50-day average)</li>
                                        <li><strong>MA50 &gt; MA<span class="longperiod">200</span></strong>: long-term trend confirmation (50-day vs. <span class="longperiod">200</span>-day average)</li>
                                      </ul>
                                      <span style="margin-top: 6px;">Higher values suggest broader participation in uptrends. Used together, they give a layered view of market breadth across timeframes.</span>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Close > MA Market Breadth</h3>
                                    <div id="chartcontainerA2" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A2Note">
                                      <span>
                                        These indicators show the percentage of stocks whose <strong>closing price</strong> is above its moving average — an indication of direct trend alignment.
                                      </span>
                                      <ul style="padding-left:20px; margin:6px 0;">
                                        <li><strong>Close &gt; MA20</strong>: Signals short-term strength — price is above the 20‑day average.</li>
                                        <li><strong>Close &gt; MA50</strong>: Reflects medium-term health, showing price is holding above a 2‑month average.</li>
                                        <li><strong>Close &gt; MA<span class="longperiod">200</span></strong>: Indicates long-term trend stability — price is above the key <span class="longperiod">200</span>‑day average.</li>
                                      </ul>
                                      <span style="margin-top:6px;">
                                        Unlike <em>MA<sup>short</sup> > MA<sup>long</sup></em> (which compares two averages), these measures look at direct price vs. trend alignment. They tend to be faster signals and can confirm momentum even before averages cross, but they are also more prone to noise and false signals.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Weekly RSI Market Breadth</h3>
                                    <div id="chartcontainerA3" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A3Note">
                                      <span>
                                        These weekly indicators show the percentage of stocks whose 14-week RSI exceeds or falls below key thresholds, providing a breadth measure of momentum and extreme across the market.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>RSI 14-week &gt; 50</strong>: Percentage of stocks with longer-term bullish momentum, with RSI above the neutral 50 level.</li>
                                        <li><strong>RSI 14-week &gt; 70</strong>: Percentage of stocks in sustained overbought territory, possibly signaling a maturing trend or medium-term consolidation risk.</li>
                                        <li><strong>RSI 14-week &lt; 30</strong>: Percentage of stocks in prolonged oversold territory, suggesting broad weakness or a potential trend reversal setup.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Tracking these longer-term breadth measures help assess how broadly momentum is distributed across the market. High readings above 50 suggest strong, widespread momentum, while extremes near 70 or 30 highlight potential market turning points.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Daily RSI Market Breadth</h3>
                                    <div id="chartcontainerA4" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A4Note">
                                      <span>
                                        These daily breadth indicators show the percentage of stocks whose 14-day RSI is above or below key thresholds, offering insight into short-term momentum and extreme across the market.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>RSI 14-day &gt; 50</strong>: Percentage of stocks with short-term bullish momentum, above the neutral 50 level.</li>
                                        <li><strong>RSI 14-day &gt; 70</strong>: Percentage of stocks in short-term overbought territory, possibly indicating near-term pullback or consolidation.</li>
                                        <li><strong>RSI 14-day &lt; 30</strong>: Percentage of stocks in short-term oversold territory, which may signal a short-term rebound opportunity.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Tracking these short-term breadth measures helps evaluate how broadly momentum is spread across stocks daily. High percentages above 50 suggest widespread strength, while extremes near 70 or 30 highlight potential reversal or exhaustion points.
                                      </span>
                                      <p></p>
                                      <span style="margin-top: 6px;">
                                        Using both weekly and daily RSI breadth measures provides a fuller picture of market momentum. The weekly data captures longer-term trend participation and helps assess whether strength is sustainable. The daily data reflects short-term shifts and is useful for spotting immediate overbought or oversold conditions. Together, they help confirm broader trend direction while identifying tactical entry and exit points.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>250-Day High minus Low Market Breadth</h3>
                                    <div id="chartcontainerA5" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A5Note">
                                      <span>
                                        The 250-day high–low breadth measures the percentage of stocks making new 52-week highs minus those making new 52-week lows. It captures the overall strength or weakness of market participation over a full trading year.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Positive readings</strong> indicate more stocks are hitting new highs than new lows, often confirming strong, broad-based uptrends.</li>
                                        <li><strong>Negative readings</strong> show more stocks are making new lows, signaling deterioration in market health or internal weakness.</li>
                                        <li><strong>Extreme values</strong> can highlight overbought or oversold breadth conditions that may precede reversals or regime shifts.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        This indicator is particularly useful for confirming the sustainability of long-term trends. Rising indices with weak or negative high–low breadth may suggest narrow leadership and vulnerability to correction. Consistent positive breadth, on the other hand, supports confidence in the broader market’s strength.
                                      </span>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>McClellan Oscillator</h3>
                                    <div id="chartcontainerA6" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A6Note">
                                      <span>
                                        The McClellan Oscillator is a market breadth indicator based on the difference between two exponential moving averages (typically 19- and 39-day) of daily advancing minus declining stocks. It reflects short- to medium-term momentum in market participation.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Positive values</strong> indicate that more stocks are advancing than declining, suggesting broad-based market strength.</li>
                                        <li><strong>Negative values</strong> show more decliners than advancers, signaling underlying weakness or selling pressure.</li>
                                        <li><strong>Extremes</strong> (very high or very low readings) may signal overbought or oversold conditions and potential reversals.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Traders use the McClellan Oscillator to confirm the strength behind market moves. Sustained positive readings support bullish momentum, while prolonged negative values can precede deeper corrections. It's most useful when combined with price action and other breadth indicators to gauge internal market confirmation.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>McClellan Summation Index</h3>
                                    <div id="chartcontainerA7" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A7Note">
                                      <span>
                                        The McClellan Summation Index is a cumulative version of the McClellan Oscillator, designed to track the long-term trend of market breadth. It adds each day's oscillator value to the previous total, building a running measure of whether advancing or declining stocks are dominating over time.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Rising values</strong> indicate sustained positive breadth, where more stocks are advancing than declining, confirming long-term market strength.</li>
                                        <li><strong>Falling values</strong> show persistent negative breadth, suggesting underlying weakness even if index prices are stable or rising.</li>
                                        <li><strong>Sharp reversals</strong> or breaks of key levels may signal major turning points in overall market trend.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        The Summation Index is best used to validate the health of a long-term uptrend or identify when broad participation is fading. It can provide early warnings of market tops or bottoms, especially when diverging from price indexes. Sustained deterioration in this index, even as prices rise, often points to narrow leadership and increased risk.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Market Trend Gauge</h3>
                                    <div id="chartcontainerA8" class="not-selectable" style="height: 311px;"></div>
                                    <div style="width: 100%; font-size:11px; padding:0px 0px 5px 10px">The&nbsp;<span style="color:#777777;font-weight:600;font-style:italic">Dark grey needle</span>&nbsp;is the whole market trend based on market breadth. The&nbsp;<span style="color:#79ABE0;font-weight:600;font-style:italic">Blue needle</span>&nbsp;is the major stock index trend.</div><div style="width: 100%; font-size:11px; padding:0px 0px 5px 10px">If the smaller angle of the two needles is greater than 90&#176;, that indicates both trends are not well aligned.</div>
                                </div>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-2">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "HKEX") echo "Hong Kong Short Selling Ratio"; else if ($data == "NYSE" || $data == "NASDAQ") echo "FINRA Margin Debt"; else if ($data == "SHSE" || $data == "SZSE") echo $exchangeName." Short Selling & Margin Debt"; ?></h2>
                                <div>
                                    <div id="chartcontainerB1" class="not-selectable market-left-axis-title-theme" style="height: 525px;"></div>
                                </div>
                                <div id="marginComment" style="text-align:left;padding-right: 15px"></div>
                                
                                <?php 
                                    if ($data == "NYSE" || $data == "NASDAQ")
                                    {  
                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="B1Note">';
                                      
                                        echo "<span>";
                                        echo "FINRA's Margin Debt tracks the total amount of money investors borrow to buy securities on margin through brokerage accounts. It reflects the use of leverage in the stock market and is often viewed as a sentiment and risk appetite gauge.";
                                        echo "</span>";
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo "<li><strong>Rising margin debt</strong> can signal growing investor confidence and risk-taking, often accompanying bull markets.</li>";
                                        echo "<li><strong>Record highs</strong> in margin debt, especially when diverging from price trends, may indicate excessive speculation or leverage buildup.</li>";
                                        echo "<li><strong>Sharp declines</strong> in margin debt can occur during corrections or forced deleveraging, often accelerating downside moves.</li>";
                                        echo "</ul>";
                                        echo '<span style="margin-top: 6px;">';
                                        echo "While not a timing tool by itself, margin debt trends help contextualize the market’s underlying risk environment. Analysts often watch for rapid increases followed by downturns as a warning of potential market tops. Comparing margin debt levels to overall market cap or price indexes can also highlight periods of speculative excess.";
                                        echo "</span>";
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    else if ($data == "HKEX")
                                    {
                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="B1Note">';
                                      
                                        echo "<span>";
                                        echo "The HKEX Short Selling Ratio represents the proportion of daily short-sell turnover to total market turnover on the Hong Kong Stock Exchange. It reflects the level of bearish positioning or hedging activity in the market.";
                                        echo "</span>";
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo "<li><strong>Higher ratios</strong> suggest increasing short interest or caution among institutional traders, which may indicate bearish sentiment or downside hedging.</li>";
                                        echo "<li><strong>Lower ratios</strong> imply reduced short activity, possibly reflecting bullish conviction or reduced demand for protection.</li>";
                                        echo "<li><strong>Sharp spikes</strong> can precede market reversals, as excessive shorting may lead to short-covering rallies if sentiment shifts.</li>";
                                        echo "</ul>";
                                        echo '<span style="margin-top: 6px;">';
                                        echo "Traders use the short selling ratio as a contrarian indicator or to gauge market sentiment pressure. Sustained high levels may signal underlying weakness, while sudden drops after elevated readings could suggest short squeeze potential. It's especially useful when viewed alongside price action, volume, and broader market conditions.";
                                        echo "</span>";
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    else if ($data == "SZSE" || $data == "SHSE")
                                    {
                                        echo '<h3 class="sr-only">'.$exchangeName.' Margin Debt Amount</h3>';
                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="B1Note">';
                                      
                                        echo '<span>';
                                        echo 'Margin Debt Amount refers to the total amount of borrowed funds investors use to purchase stocks on margin. It reflects the level of leverage and speculative appetite in China’s equity market, and is closely watched as a sentiment and risk indicator.';
                                        echo '</span>';
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo '<li><strong>Rising margin debt</strong> signals increasing investor confidence and risk-taking, often accompanying bull markets.</li>';
                                        echo '<li><strong>Sharp spikes</strong> may indicate excessive speculation, especially when not supported by fundamentals.</li>';
                                        echo '<li><strong>Rapid declines</strong> in margin debt often coincide with market corrections or forced liquidations, amplifying downside moves.</li>';
                                        echo '</ul>';
                                        echo '<span style="margin-top: 6px;">';
                                        echo 'Investors use margin debt trends as a proxy for leverage buildup and crowd behavior. Persistent surges can suggest overheating, while sudden contractions may indicate stress or risk aversion. It’s particularly useful in China, where retail participation is high and leverage has historically played a large role in market swings.';
                                        echo "</span>";
                                        echo '</div>';
                                        echo '</div>';
                                        
                                        echo "<hr>";
                                        echo "<div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>";
                                        echo "<div>";
                                        echo "<div id='chartcontainerB2' class='not-selectable market-left-axis-title-theme' style='height: 525px;'></div>";
                                        echo "</div>";
                                        
                                        
                                        echo '<h3 class="sr-only">'.$exchangeName.' Short Selling Amount</h3>';
                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="B2Note">';
                                        echo '<span>';
                                        echo 'Short Selling Amount measures the total value of stocks sold short on the exchange. It reflects the level of bearish positioning or hedging activity by traders who expect price declines or are protecting long positions.';
                                        echo '</span>';
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo '<li><strong>Rising short selling</strong> suggests growing negative sentiment or hedging demand, especially during volatile or uncertain periods.</li>';
                                        echo '<li><strong>Low short interest</strong> may indicate complacency or a lack of downside conviction.</li>';
                                        echo '<li><strong>Sharp reversals</strong> after elevated short selling can lead to short squeezes, especially if sentiment quickly turns or if key technical levels are broken.</li>';
                                        echo '</ul>';
                                        echo '<span style="margin-top: 6px;">';
                                        echo "Traders watch short selling trends to assess market pressure from the bearish side. While the short selling mechanism is more restricted in China than in Western markets, significant changes in short activity can still reveal shifts in sentiment or institutional positioning. It's most useful when analyzed alongside margin debt and price action.";
                                        echo '</span>';
                                        echo '</div>';
                                        echo '</div>';

                                    }
                                ?>
                                
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-3">
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div id="chartcontainerC1" class="not-selectable">
                                    <div id="minimal-tabs" class = "card clean-card text-center container" style="padding:0">
                                        <div class="card-header">
                                            <h2>Sector Performance</h2>
                                        </div>
                                        <div class="not-selectable">
                                            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <button id="sort1<?php echo str_replace(' ', '', $exchangeName); ?>" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
                                            <button id="sort2<?php echo str_replace(' ', '', $exchangeName); ?>" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
                                            <button id="sort3<?php echo str_replace(' ', '', $exchangeName); ?>" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
                                            <button id="sort4<?php echo str_replace(' ', '', $exchangeName); ?>" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
                                            </div>
                                            <div id="barcontainer1" style="height: 500px; min-width: 100%; margin: 0 auto"></div>
                                        </div>
                                    </div>
                                                
                                    <hr>
                            
                                    <div id="Heatmap" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Market Heatmap & Scatter</h2>
                                        </div>
                                        <div id="bodyContent">
                                            <div id="maptab" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptab-1">1 Day</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-2">5 Days</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-3">20 Days</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="maptab-1">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverA1"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (1-Day)</h3>
                                                            <div id="treecontainerA1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (1-Day)</h3>
                                                            <div id="bubblecontainerA1" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="maptab-2">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverB1"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (5-Day)</h3>
                                                            <div id="treecontainerB1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (5-Day)</h3>
                                                            <div id="bubblecontainerB1" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" role="tabpanel" id="maptab-3">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverC1"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (20-Day)</h3>
                                                            <div id="treecontainerC1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (20-Day)</h3>
                                                            <div id="bubblecontainerC1" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>    

                                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">

                                            <div id="tatab" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatab-1">250-Day High vs Low</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-2">MA<?php 
                                                        if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX")
                                                            echo "250";
                                                        else
                                                            echo "200";?>&nbsp;vs MA50
                                                    </a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-3">RSI Weekly vs RSI Daily</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="tatab-1">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From 250-Day High vs 250-Day Low</h3>
                                                            <div id="tabubblecontainerA1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="highlowA1"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-2">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From MA<?php if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX") echo "250"; else echo "200"; ?> vs MA50</h3>
                                                            <div id="tabubblecontainerB1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="MA50_LongB1"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-3">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Weekly RSI vs Daily RSI</h3>
                                                            <div id="tabubblecontainerC1" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="RSIC1"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div id="Top10" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Top 10 Market Cap Stocks<button id="peersBtn" class="btn btn-warning btn-sm" onclick="openTOOL()" style="margin:-2px 5px 0px 0px;float:right;font-weight:bold;color:#383838;height:29px">RACE</button></h2>
                                        </div>
                                        <div>
                                            <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                            <div>
                                                <div id="top10container" style="width:auto;height:700px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-4">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "NYSE") echo $exchangeName." Price-to-Earnings Band, Shiller PE, S&P 500 Earnings & Dividend"; else echo $exchangeName." Price-to-Earnings Band"; ?></h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Price-to-Earnings Band</h3>
                                    <div id="chartcontainerD1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="valuationComment" style="text-align:left;padding-right: 15px"></div>
                                
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D1Note">
                                      <span>
                                        PE Band is a set of valuation ranges derived from historical Price-to-Earnings (P/E) ratios, typically showing upper and lower boundaries around a median or average P/E level. They provide a visual framework for assessing whether the current market valuation is relatively high, low, or within normal limits.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Upper band</strong> often represent overvalued territory where the market may be priced for high future growth, increasing the risk of correction.</li>
                                        <li><strong>Lower band</strong> suggest undervaluation or bargain levels, potentially offering attractive entry points for long-term investors.</li>
                                        <li><strong>Middle band</strong> reflect normal or fair-value ranges based on historical data.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Investors use PE Band to contextualize current valuations relative to history, helping to set expectations for returns and risk. Staying near the upper band might warrant caution or defensive positioning, while valuations near the lower band can indicate buying opportunities.
                                      </span>
                                    </div>
                                </div>

                                <?php 
                                    if ($data == "NYSE")
                                    {
                                        echo "<hr>";
                                        echo "<div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>";
                                        echo "<div>";
                                        echo '<h3 class="sr-only">S&P 500 Shiller PE - Cyclically Adjusted PE (CAPE)</h3>';
                                        echo "<div id='chartcontainerD2' class='not-selectable' style='height: 525px;'></div>";
                                        echo "</div>";

                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="D2Note">';
                                        echo '<span>';
                                        echo 'The Shiller PE Ratio, also known as the CAPE (Cyclically Adjusted Price-to-Earnings) ratio, measures stock market valuation by dividing the current price of the S&amp;P 500 by the average inflation-adjusted earnings over the past 10 years. Developed by economist Robert Shiller, it smooths out earnings volatility to better reflect long-term valuation trends.';
                                        echo '</span>';
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo '<li><strong>High CAPE values</strong> suggest the market may be overvalued relative to historical earnings, potentially signaling lower future returns.</li>';
                                        echo '<li><strong>Low CAPE values</strong> indicate more attractive long-term valuations, often occurring after major market corrections.</li>';
                                        echo '<li><strong>Best used</strong> as a long-term risk/return gauge, not for short-term timing, as valuations can remain stretched for years.</li>';
                                        echo '</ul>';
                                        echo '<span style="margin-top: 6px;">';
                                        echo 'Investors and analysts use the Shiller PE to assess whether the broad equity market is priced above or below its historical norms. While not predictive of exact tops or bottoms, extreme readings often correspond to secular bull or bear market turning points. It is especially helpful for setting expectations about long-term returns and portfolio risk management.';
                                        echo '</span>';
                                        echo '</div>';
                                        echo '</div>';
                                        
                                        
                                        echo "<hr>";
                                        echo "<div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>";
                                        echo "<div>";
                                        echo '<h3 class="sr-only">S&P 500 Earnings Yield & Dividend Yield</h3>';
                                        echo "<div id='chartcontainerD3' class='not-selectable' style='height: 525px;'></div>";
                                        echo "</div>";
                                        
                                        echo '<div class="chartNote">';
                                        echo '<div class="showNote">Show more</div>';
                                        echo '<div class="noteWrapper collapsed" id="D3Note">';
                                        echo '<span>';
                                        echo 'The S&amp;P 500 Earnings Yield and Dividend Yield are valuation and income metrics used to assess the return potential of U.S. large-cap equities relative to other asset classes.';
                                        echo '</span>';
                                        echo '<p></p>';
                                        echo '<h4>Earnings Yield:</h4>';
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo '<li>Calculated as the inverse of the Price-to-Earnings (P/E) ratio: <strong>Earnings Yield = Earnings ÷ Price</strong>.</li>';
                                        echo '<li><strong>Higher yields</strong> suggest better value and potentially stronger forward returns, especially compared to bond yields.</li>';
                                        echo '<li>Often used in asset allocation decisions to compare equity returns vs. fixed income (e.g., 10-year Treasury yield).</li>';
                                        echo '</ul>';
                                        
                                        echo '<h4>Dividend Yield:</h4>';
                                        echo '<ul style="padding-left: 20px; margin: 6px 0;">';
                                        echo '<li>Formula: <strong>Dividend Yield = Annual Dividends per Share ÷ Price per Share</strong></li>';
                                        echo '<li>Represents the annual cash dividends paid by S&amp;P 500 companies as a percentage of the index price.</li>';
                                        echo '<li><strong>Higher yields</strong> may offer income support during market volatility and appeal to income-focused investors.</li>';
                                        echo '<li><strong>Low yields</strong> often reflect growth-focused markets or elevated valuations where dividends are less emphasized.</li>';
                                        echo '</ul>';
                                        
                                        echo '<span style="margin-top: 6px;">';
                                        echo 'Together, these yields provide insight into market valuation and income attractiveness. Comparing earnings yield to interest rates helps evaluate relative value, while dividend yield offers perspective on the market’s income-generating potential. Both are useful for setting return expectations and assessing equity risk premiums over time.';
                                        echo '</span>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                ?>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-5">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." Advance-Decline & Performance"; ?></h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." "; ?>Advance Decline %</h3>
                                    <div id="chartcontainerE1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="adComment" style="text-align:left;padding-right: 15px"></div>
                                <p><span style="color:#0e0;font-weight:550">Advance %</span> = number of Advance / (number of Advance + number of Decline) * 100%</p><p><span style="color:#e00;font-weight:550">Decline %</span> = number of Decline / (number of Advance + number of Decline) * 100%</p>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E1Note">
                                      <span>
                                        Advance % vs. Decline % measures the percentage of stocks that closed higher or lower on a given day relative to the total number of traded stocks. It is a simple yet powerful way to gauge daily market breadth and sentiment.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Balanced values</strong> near 50% suggest mixed sentiment, while <strong>extremes</strong> (e.g. 80%+ advancing or declining) can indicate broad-based buying or selling pressure.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        This indicator helps assess the strength behind market moves. For example, a market index rising on only 40% advancing stocks may reflect weak participation. In contrast, rallies with 70–80% advancers are typically seen as healthier and more sustainable. Monitoring advance & decline percentages over time can help identify trend shifts and capitulation points.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Daily Return %</h3>
                                    <div id="chartcontainerE2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="dailyReturnComment" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Weekly Return %</h3>
                                    <div id="chartcontainerE3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="weeklyReturnComment" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Monthly Return %</h3>
                                    <div id="chartcontainerE4" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Yearly Return %</h3>
                                    <div id="chartcontainerE5" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="yearlyReturnComment" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <div class="toggle-container">
                                        <label for="toggle" class="label-on">Value</label>
                                        <input type="checkbox" id="toggle" class="toggle-checkbox" checked onChange="YearlyTrendToggle(this.checked, 'chartcontainerYearlyTrend')"/>
                                        <label for="toggle" class="toggle-switch"></label>
                                        <label for="toggle" class="label-off">Percent</label>
                                    </div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Yearly Trend</h3>
                                    <div id="chartcontainerYearlyTrend" class="not-selectable" style="height: 525px;"></div>
                                    <div class="chartNote">
                                        <div class="showNote">Show more</div>
                                        <div class="noteWrapper collapsed">
                                            <span>
                                                The Yearly Trend chart aligns recent calendar years on the same January-to-December timeline, making it easier to compare how the index moved during each year. In Performance % mode, every yearly line begins near 0%, so differences in index level do not obscure the shape and strength of each year's performance.
                                            </span>
                                            <ul style="padding-left: 20px; margin: 6px 0;">
                                                <li><strong>A rising line</strong> shows that the index has gained from the beginning of that year, while a falling line shows a loss.</li>
                                                <li><strong>Similar paths across several years</strong> may reveal recurring periods of strength, weakness or consolidation.</li>
                                                <li><strong>Large gaps between yearly lines</strong> show that performance differed substantially during the same part of the calendar year.</li>
                                            </ul>
                                            <span style="margin-top: 6px;">
                                                Use the Value/Percent switch above the chart to alternate between actual historical index levels and percentage performance from the start of each displayed year.
                                            </span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div style="margin: 30px;">
                                        <div>
                                            <input type="text" class="js-range-slider" id="slider" name="seasonalityRangeSelector" value=""
                                                data-type="double"
                                                data-grid="true"
                                            />
                                        </div>
                                    </div>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Average Monthly Return %</h3>
                                    <div id="chartcontainerSeasonality" class="not-selectable" style="height: 600px;"></div>
                                    <div class="chartNote">
                                        <div class="showNote">Show more</div>
                                        <div class="noteWrapper collapsed">
                                            <span>
                                                The Average Monthly Return % chart summarizes the index's historical return for each calendar month. Each monthly average is calculated from the returns recorded for that month during the selected years, helping show when the index has tended to be stronger or weaker.
                                            </span>
                                            <ul style="padding-left: 20px; margin: 6px 0;">
                                                <li><strong>Positive bars</strong> indicate months with a positive average historical return; negative bars indicate months with a negative average.</li>
                                                <li><strong>Winning %</strong> shows how often the month produced a positive return within the selected period.</li>
                                                <li><strong>Compare return and winning rate</strong> to distinguish frequently positive months from months whose average was driven by a smaller number of unusually large moves.</li>
                                            </ul>
                                            <span style="margin-top: 6px;">
                                                To examine a different period, drag the left and right handles of the year-range slider above the chart. The left handle selects the starting year and the right handle selects the ending year. As the range changes, the chart recalculates the average monthly returns and winning percentages using only the years inside that range. A wider range gives a longer historical view, while a narrower range can highlight more recent seasonal behavior.
                                            </span>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Monthly Return % Table</h3>
                                    <h6 class="card-title" id="monthlytabletitle">Monthly Return %</h6>
                                    <table id="monthlytable" class="display" width="99%" style="margin:0 auto;padding: 0px 15px 15px 15px"></table>
                                    <div class="chartNote">
                                        <div class="showNote">Show more</div>
                                        <div class="noteWrapper collapsed">
                                            <span>
                                                The Monthly Return % table provides the year-by-year detail behind the seasonality analysis. Each row represents a calendar year, while the monthly columns show the percentage change for January through December.
                                            </span>
                                            <ul style="padding-left: 20px; margin: 6px 0;">
                                                <li><strong>Green cells</strong> show positive monthly returns, red cells show negative returns, and stronger shades represent larger moves.</li>
                                                <li><strong>The YTD column</strong> shows the return for the full year, or the available year-to-date period when the year is not yet complete.</li>
                                                <li><strong>The Avg row</strong> summarizes the average return for each month across the available history.</li>
                                            </ul>
                                            <span style="margin-top: 6px;">
                                                Read down a month to compare how it performed across different years, or read across a row to follow the sequence of gains and losses within one year.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-6">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." Stock Connect Southbound Fund Flow"; ?></h2>
                                <div>
                                    <h3 class="sr-only">Hong Kong Stock Connect Southbound Daily Fund Flow</h3>
                                    <div id="chartcontainerF1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="dailyCCASSComment" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="F1Note">
                                      <span>
                                        Hong Kong Daily Stock Connect Southbound Fund Flow tracks the net buying or selling activity by mainland Chinese investors in the Hong Kong stock market via the Stock Connect programs (mainly through the Shenzhen–Hong Kong and Shanghai–Hong Kong Connect channels). It represents cross-border capital flow from China into Hong Kong-listed equities.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Net inflows</strong> indicate buying interest from mainland investors, often seen as supportive for Hong Kong market sentiment and liquidity.</li>
                                        <li><strong>Net outflows</strong> suggest capital retreat or cautious positioning, which can pressure local stock performance.</li>
                                        <li><strong>Sudden shifts</strong> in flow can reflect changing macro views, regulatory expectations, or currency outlooks (especially RMB vs. HKD).</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Southbound fund flow is closely watched as a real-time signal of mainland investor appetite for Hong Kong equities. Persistent inflows can reflect bullish conviction or policy support, while sustained outflows may indicate risk-off sentiment or tightening liquidity in China. It is particularly useful when viewed alongside market breadth and foreign institutional flows.
                                      </span>
                                    </div>
                                </div>
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Stock Connect Southbound Monthly Fund Flow</h3>
                                    <div id='chartcontainerF2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div id="monthlyCCASSComment" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="F2Note">
                                      <span>
                                        Hong Kong Monthly Stock Connect Southbound Fund Flow aggregates the net buying or selling activity of mainland Chinese investors in the Hong Kong stock market over a monthly period, via the Stock Connect programs. It provides a broader view of capital allocation trends and investor sentiment beyond short-term trading noise.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Monthly data</strong> smooths out daily volatility and reveals sustained directional trends in mainland investor participation.</li>
                                        <li><strong>Persistent net inflows</strong> over several months can signal structural confidence or policy-driven allocation into Hong Kong-listed assets.</li>
                                        <li><strong>Reversals</strong> in monthly flows often align with shifts in macro conditions, liquidity, or geopolitical sentiment.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Compared to daily flows, which are more reactive to news and sentiment swings, monthly Southbound Fund Flow offers a clearer picture of long-term positioning by mainland investors. Analysts and institutional investors use this data to assess the underlying demand for Hong Kong equities, sector rotation trends, and capital commitment levels over time. It is often used in conjunction with index performance and macro policy cues.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Stock Connect Southbound Cumulative Annual Fund Flow</h3>
                                    <div id='chartcontainerF3' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div id="yearlyCCASSComment" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="F3Note">
                                      <span>
                                        The Hong Kong Stock Connect Southbound Cumulative Annual Fund Flow tracks the total net capital inflow or outflow from mainland Chinese investors into the Hong Kong stock market over the course of each calendar year, via the Stock Connect programs. This long-term measure helps assess structural investment trends and shifting cross-border sentiment.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>High cumulative inflows</strong> in a given year suggest sustained confidence and strategic capital allocation into Hong Kong equities.</li>
                                        <li><strong>Declining or negative annual flows</strong> can signal reduced risk appetite, tighter liquidity in mainland China, or shifting investment focus.</li>
                                        <li><strong>Comparing across years</strong> helps reveal major policy changes, such as post-pandemic recovery, monetary easing, or tightening cycles.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Investors and strategists use cumulative annual fund flow to evaluate the long-term commitment of mainland capital to the Hong Kong market. Unlike daily or monthly data, which may fluctuate due to short-term sentiment or policy events, the annual trend highlights broader shifts in portfolio strategy and market confidence. It is especially useful when aligned with macroeconomic cycles, policy changes, and structural reforms.
                                      </span>
                                    </div>
                                </div>

                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-7">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." Housing Market"; ?></h2>
                                <div>
                                    <h3 class="sr-only">Hong Kong Residential Price Indices by Class</h3>
                                    <div id="chartcontainerG1" class="not-selectable" style="height: 525px;"></div>
                                    <table style="margin: 0 auto;">
                                    <tr style="background:var(--bg-card,#fff)"><td><span style="color:black;font-weight:bold">A</span> - saleable area less than 40 m<sup>2</sup></td><td><span style="color:#8AEC75;font-weight:bold">B</span> - saleable area of 40 m<sup>2</sup> to 69.9 m<sup>2</sup></td><td><span style="color:#F7A35C;font-weight:bold">C</span> - saleable area of 70 m<sup>2</sup> to 99.9 m<sup>2</sup></td></tr>
                                    <tr style="background:var(--bg-card,#fff)"><td><span style="color:#767BE7;font-weight:bold">D</span> - saleable area of 100 m<sup>2</sup> to 159.9 m<sup>2</sup></td><td><span style="color:#F04F76;font-weight:bold">E</span> - saleable area of 160 m<sup>2</sup> or above</td><td><span style="color:#7AB4EC;font-weight:bold">Residential</span> - A & B & C & D & E</td></tr>
                                    </table>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="G1Note">
                                      <span>
                                        The Hong Kong Rating and Valuation Department (RVD) publishes official residential property price indices and the number of sale agreements, providing a benchmark for local housing market trends. The price indices track the average valuation of private residential units across different size classes, offering a standardized view of market direction over time. The number of sales captures transactional volume, which reflects market participation, sentiment and policy shift such as Special Stamp Duty (SSD), Buyer's Stamp Duty (BSD) and New Residential Stamp Duty (NRSD).
                                      </span>
                                    </div>
                                </div>
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Property Price Indices by Type</h3>
                                    <div id='chartcontainerG2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="G2Note">
                                      <span>
                                        The Hong Kong Rating and Valuation Department (RVD) tracks property price indices and number of sale agreements across four key sectors: residential, office, retail, and flatted factory. These indices reflect average values within each segment and are updated regularly to monitor price trends and sector-specific cycles.
                                      </span>
                                      <p></p>
                                      <span>
                                        Comparing price movements across these sectors can reveal where capital is rotating — for example, strength in factory or office prices during an industrial rebound, or softness in retail prices amid consumer weakness. The number of sale agreements complements this by indicating liquidity and investor confidence. Monitoring both price and transaction volume helps assess underlying market health, sector resilience, and turning points in the broader property cycle.
                                      </span>
                                    </div>
                                </div>
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Property Rental Indices by Type</h3>
                                    <div id='chartcontainerG3' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="G3Note">
                                      <span>
                                        The Hong Kong Rating and Valuation Department (RVD) publishes rental indices for residential, office, retail, and flatted factory properties. These indices track the movement of average rental levels over time, reflecting demand strength, lease renewal trends, and sector-specific occupancy dynamics.
                                      </span>
                                      <p></p>
                                      <span>
                                        Rental indices are especially useful for gauging real-time economic activity. Rising residential rents may indicate population pressure or supply constraints. Office and retail rental trends help reveal corporate sentiment, hiring appetite, and consumer foot traffic. Factory rent movements can reflect industrial usage, zoning change or logistics demand. Monitoring rental trends across these segments supports a better understanding of yield potential, sector rotation, and the broader health of Hong Kong’s property market.
                                      </span>
                                    </div>
                                </div>

                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Property Rent / Price Ratio by Type</h3>
                                    <div id='chartcontainerG4' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="G4Note">
                                      <span>
                                        The ratio of Hong Kong property rental indices to price indices — calculated separately for residential, office, retail, and flatted factory sectors — provides insight into potential rental yield trends and valuation levels. It effectively approximates the gross rental return, which is the annualized rent relative to property value.
                                      </span>
                                      <p></p>
                                      <span>
                                        When this ratio rises, it signals that rental income is improving relative to property prices, often seen during market corrections or when demand for usage outpaces speculation. A declining ratio suggests prices are rising faster than rents, which may imply overvaluation, lower future yield, or strong capital appreciation expectations. Tracking this ratio across property types helps assess income-generating potential versus speculative risk, aiding investors, landlords, and policy analysts in gauging property market sustainability.
                                      </span>
                                    </div>
                                </div>
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hong Kong Residential Price, Completion, Takeup, Vacancy</h3>
                                    <div id='chartcontainerG5' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div style = "text-align:left;margin:20px 24px">
                                    <p><span style="font-weight:550">Completion</span> refers to the number of new residential units completed in a given year.</p>
                                    <p><span style="font-weight:550">Takeup</span> is calculated by adding the completions in that year to the vacancy figures at the beginning of the year, then subtracting the year's demolition and the year-end vacancy figures.</p>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="G5Note">
                                      <span>
                                        Comparing Hong Kong's residential property price index with metrics such as the number of new unit completions, vacancy rates, and unit take-up levels offers insight into supply-demand balance and market pressure. When completions rise but take-up remains low and vacancy increases, this may point to oversupply risks, potentially putting downward pressure on prices.
                                      </span>
                                      <p></p>
                                      <span>
                                        Conversely, consistently high take-up and low vacancy, even amid strong completions, indicate resilient end-user or investment demand. When price trends diverge from physical supply indicators — for example, prices rising despite higher vacancies — it may reflect speculative buying, limited alternative investments, or external capital inflows. Monitoring these dynamics helps gauge whether price movements are fundamentally supported or vulnerable to correction.
                                      </span>
                                    </div>
                                </div>

                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-8">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "NYSE") echo "US Economic Surprise Index, Fear & Greed Index"; else if ($data == "SHSE") echo "China Economic Surprise Index"; ?></h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $country." "; ?>Economic Surprise Index</h3>
                                    <div id="chartcontainerH1" class="not-selectable sentiment-chart" style="height: 825px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H1Note">
                                      <span>
                                        The Economic Surprise Index tracks how recent economic data releases compare to market expectations. It rises when data consistently beats forecasts and falls when results come in weaker than expected..
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Positive readings</strong> indicate economic data is outperforming expectations, often supporting risk assets and strengthening currency outlooks.</li>
                                        <li><strong>Negative readings</strong> reflect disappointment in economic data, which can weigh on equities and signal cautious sentiment.</li>
                                        <li><strong>Turning points</strong> in the index may lead or coincide with shifts in monetary policy expectations and investor positioning.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Investors use the Economic Surprise Index to assess macro momentum and sentiment. It’s particularly helpful in understanding whether strong or weak data is already priced in. For example, markets may react negatively to “good” data if expectations were even higher.
                                      </span>
                                    </div>
                                </div>

                                <div id="fear_greed" style="display:none">
                                    <hr>
                                    <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only"><?php echo $country." "; ?>Stock Fear and Greed Index</h3>
                                        <div id="chartcontainerH2" class="not-selectable sentiment-chart" style="height: 725px;"></div>
                                    </div>
                                    <div class="chartNote">
                                        <div class="showNote">Show more</div>
                                        <div class="noteWrapper collapsed" id="H2Note">
                                          <span>
                                            The Fear &amp; Greed Index is a composite sentiment indicator ranging from 0 (extreme fear) to 10 (extreme greed), designed to gauge whether investors are too fearful or too greedy in the market.
                                          </span>
                                          <ul style="padding-left: 20px; margin: 6px 0;">
                                            <li><strong>Extreme fear</strong> (scores below 2.5) often occurs near market lows, when sentiment is overly pessimistic.</li>
                                            <li><strong>Extreme greed</strong> (scores above 7.5) may signal overbought conditions and complacency, raising the risk of a pullback.</li>
                                            <li><strong>Neutral levels</strong> (4.5–5.5) suggest balanced sentiment, with no strong bias in positioning or risk appetite.</li>
                                          </ul>
                                          <span style="margin-top: 6px;">
                                            Traders and investors use the Fear &amp; Greed Index as a contrarian tool. When the index shows extreme fear, it may indicate a buying opportunity. Conversely, extreme greed could be a sign to reduce exposure or tighten risk controls. While not a precise timing signal, it adds context to market sentiment and can help confirm or question prevailing price trends.
                                          </span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-9">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "NYSE") echo "US Stock Index Since 1700"; ?></h2>
                                <div>
                                    <h3 class="sr-only">DJI Monthly Since 1700 with UK Stock Data & Major Historical Events</h3>
                                    <div id="chartcontainerI1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment1700" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="I1Note">
                                      <span>
                                        The <span id="uslength"></span>year stock index chart is a long-term visualization that merges early UK equity market data (such as the South Sea Company) with DJI to represent centuries of equity market evolution. It is annotated with major U.S. historical events — including wars, financial crises, and territorial expansion — to show how markets responded over time.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Early data (1700s–1800s)</strong> reflects British market performance during the colonial era, used as a proxy before U.S. financial markets were fully developed.</li>
                                        <li><strong>Transitional data (1885–1896)</strong> is based on the first Dow Jones stock averages, primarily railroad and transportation companies, providing the earliest benchmark of U.S. market performance before the formal DJI.</li>
                                        <li><strong>U.S. data (post-1896)</strong> is represented by DJI, capturing industrialization, world wars, the Great Depression, the tech boom, and the modern era.</li>
                                        <li><strong>Historical markers</strong> include events like the Louisiana Purchase, the Civil War, the 1929 crash, Bretton Woods Agreement, oil embargo, the 2008 financial crisis, and the COVID-19 pandemic.</li>
                                      </ul>
                                      <span style="margin-top: 6px;">
                                        Beyond market insights, this chart serves as an engaging way to learn U.S. history, linking key economic and political developments to their impact on capital markets. It helps users understand how wars and financial crises shaped the American economy and investor behavior over centuries.
                                      </span>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only">DJI Yearly Since 1700 with UK Stock Data & Major Historical Events</h3>
                                    <div id="chartcontainerI2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="yearlyReturnComment1700" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <div style="margin: 15px 30px;">
                                        <div>
                                            <input type="text" class="js-range-slider" id="slider1700" name="seasonalityRangeSelector" value=""
                                                data-type="double"
                                                data-grid="true"
                                            />
                                        </div>
                                    </div>
                                    <h3 class="sr-only">DJI Average Monthly Return %</h3>
                                    <div id="chartcontainerSeasonality1700" class="not-selectable" style="height: 600px;"></div>
                                    <hr>
                                    <h3 class="sr-only">DJI Monthly Return % Table</h3>
                                    <h6 class="card-title" id="monthlytabletitle1700">Monthly Return %</h6>
                                    <p>Merging with pre-February 1885 UK data</p>
                                    <table id="monthlytable1700" class="display" width="99%" style="margin:0 auto;padding: 0px 15px 15px 15px"></table>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-10">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "HKEX") echo "Hong Kong Stock Index Since 1841"; ?></h2>
                                <div>
                                    <h3 class="sr-only">Hang Send Index Monthly Since 1700 with UK Stock Data</h3>
                                    <div id="chartcontainerJ1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment1841" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="I1Note">
                                      <span>
                                        The <span id="hklength"></span>year stock index chart, dating back to the establishment of British colonial Hong Kong in 1841, is a long-term visualization that merges early HK equity market data (such as HSBC) and UK stock data with HSI to represent centuries of equity market evolution.<!-- It is annotated with major HK and China historical events — including wars, financial crises, and the handover of Hong Kong — to show how markets responded over time.-->
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Early data (1841–1868)</strong> reflects UK market performance during the early colonial era, serving as a proxy before Hong Kong developed formal exchanges.</li>
                                        <li><strong>Hong Kong data (1868–1964)</strong> is based on locally available equities, with HSBC and other early listings forming the backbone of market representation.</li>
                                        <li><strong>Modern data (1964–present)</strong> is represented by HSI, capturing the city’s transformation into an international financial hub, through global shocks, the Asian financial crisis, the tech boom, and the 2008 financial crisis.</li>
                                        <!--li><strong>Historical markers</strong> include events such as the Opium Wars, the Taiping Rebellion, World War II and the Japanese occupation, the establishment of the People’s Republic of China, the 1997 handover of Hong Kong, the 2008 global financial crisis, and the COVID-19 pandemic.</li-->
                                      </ul>
                                      <!--span style="margin-top: 6px;">
                                        Beyond market insights, this chart also serves as an engaging lens into Hong Kong’s history, linking key economic and political developments to their impact on capital markets. It illustrates how wars, crises, and transitions shaped the territory’s economy and investor behavior over time.
                                      </span-->
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only">Hang Send Index Yearly Since 1700 with UK Stock Data</h3>
                                    <div id="chartcontainerJ2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="yearlyReturnComment1841" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <div style="margin: 15px 30px;">
                                        <div>
                                            <input type="text" class="js-range-slider" id="slider1841" name="seasonalityRangeSelector" value=""
                                                data-type="double"
                                                data-grid="true"
                                            />
                                        </div>
                                    </div>
                                    <div id="chartcontainerSeasonality1841" class="not-selectable" style="height: 600px;"></div>
                                    <hr>
                                    <h6 class="card-title" id="monthlytabletitle1841">Monthly Return %</h6>
                                    <p>Merging with pre-July 1964 HK & UK data</p>
                                    <table id="monthlytable1841" class="display" width="99%" style="margin:0 auto;padding: 0px 15px 15px 15px"></table>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-11">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($data == "NYSE") echo "Gold Price Since 1920"; ?></h2>
                                <div>
                                    <h3 class="sr-only">Gold Price Daily Since 1920</h3>
                                    <div id="chartcontainerK1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="dailyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                  <div class="showNote">Show more</div>
                                  <div class="noteWrapper collapsed" id="GoldNote">
                                    <span>
                                      The <span id="goldlength"></span>year gold price chart, spanning from 1920 to the present, offers a century-long perspective on global monetary trends, inflation cycles, and shifts in investor sentiment toward precious metals as a store of value and hedge against economic uncertainty.
                                    </span>
                                    <ul style="padding-left: 20px; margin: 6px 0;">
                                      <li><strong>Pre-Bretton Woods era (1920–1944)</strong> captures gold’s role under the classical gold standard and the global disruptions caused by the Great Depression and World War II.</li>
                                      <li><strong>Bretton Woods system (1944–1971)</strong> reflects the fixed $35 per ounce peg, when gold anchored global exchange rates under U.S. dollar convertibility.</li>
                                      <li><strong>Post-1971 free-market era (1971–present)</strong> traces the modern floating gold price following the collapse of the gold standard, highlighting inflationary spikes in the 1970s, the 2008 financial crisis, and surges amid global monetary easing and geopolitical tensions.</li>
                                    </ul>
                                  </div>
                                </div>
                                <hr>

                                <div>
                                    <h3 class="sr-only">Gold Price Weekly Since 1920</h3>
                                    <div id="chartcontainerK2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="weeklyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div>
                                    <h3 class="sr-only">Gold Price Monthly Since 1920</h3>
                                    <div id="chartcontainerK3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <hr>

                                <div>
                                    <h3 class="sr-only">Gold Price Yearly Since 1920</h3>
                                    <div id="chartcontainerK4" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="yearlyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <div class="toggle-container">
                                        <label for="toggle1920" class="label-on">Value</label>
                                        <input type="checkbox" id="toggle1920" class="toggle-checkbox" checked onChange="YearlyTrendToggle(this.checked, 'chartcontainerYearlyTrend1920', 'Gold Price')"/>
                                        <label for="toggle1920" class="toggle-switch"></label>
                                        <label for="toggle1920" class="label-off">Percent</label>
                                    </div>
                                    <h3 class="sr-only">Gold Price Yearly Trend</h3>
                                    <div id="chartcontainerYearlyTrend1920" class="not-selectable" style="height: 525px;"></div>
                                    <hr>

                                    <div style="margin: 15px 30px;">
                                        <div>
                                            <input type="text" class="js-range-slider" id="slider1920" name="seasonalityRangeSelector" value=""
                                                data-type="double"
                                                data-grid="true"
                                            />
                                        </div>
                                    </div>
                                    <h3 class="sr-only">Gold Price Average Monthly Return %</h3>
                                    <div id="chartcontainerSeasonality1920" class="not-selectable" style="height: 600px;"></div>
                                    <hr>
                                    <h3 class="sr-only">Gold Price Monthly Return % Table</h3>
                                    <h6 class="card-title" id="monthlytabletitle1920">Monthly Return %</h6>
                                    <table id="monthlytable1920" class="display" width="99%" style="margin:0 auto;padding: 0px 15px 15px 15px"></table>
                                </div>

                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-12">
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div id="chartcontainerC1" class="not-selectable">
                                    <div id="Heatmap" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Market Heatmap & Scatter</h2>
                                        </div>
                                        <div id="bodyContent">
                                            <div id="maptab" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptab-4">1 Day</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-5">5 Days</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-6">20 Days</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="maptab-4">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverA2"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (1-Day)</h3>
                                                            <div id="treecontainerA2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (1-Day)</h3>
                                                            <div id="bubblecontainerA2" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="maptab-5">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverB2"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (5-Day)</h3>
                                                            <div id="treecontainerB2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (5-Day)</h3>
                                                            <div id="bubblecontainerB2" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" role="tabpanel" id="maptab-6">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverC2"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (20-Day)</h3>
                                                            <div id="treecontainerC2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (20-Day)</h3>
                                                            <div id="bubblecontainerC2" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>    

                                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">

                                            <div id="tatab2" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatab-4">250-Day High vs Low</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-5">MA<?php 
                                                        if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX")
                                                            echo "250";
                                                        else
                                                            echo "200";?>&nbsp;vs MA50
                                                    </a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-6">RSI Weekly vs RSI Daily</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="tatab-4">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From 250-Day High vs 250-Day Low</h3>
                                                            <div id="tabubblecontainerA2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="highlowA2"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-5">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From MA<?php if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX") echo "250"; else echo "200"; ?> vs MA50</h3>
                                                            <div id="tabubblecontainerB2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="MA50_LongB2"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-6">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Weekly RSI vs Daily RSI</h3>
                                                            <div id="tabubblecontainerC2" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="RSIC2"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div id="Top10" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Top 10 Market Cap Stocks<button id="peersBtn" class="btn btn-warning btn-sm" onclick="openTOOL()" style="margin:-2px 5px 0px 0px;float:right;font-weight:bold;color:#383838;height:29px">RACE</button></h2>
                                        </div>
                                        <div>
                                            <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                            <div>
                                                <div id="top10container2" style="width:auto;height:700px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-13">
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div id="chartcontainerC1" class="not-selectable">
                                    <div id="Heatmap" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Market Heatmap & Scatter</h2>
                                        </div>
                                        <div id="bodyContent">
                                            <div id="maptab" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptab-7">1 Day</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-8">5 Days</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-9">20 Days</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="maptab-7">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverA3"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (1-Day)</h3>
                                                            <div id="treecontainerA3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (1-Day)</h3>
                                                            <div id="bubblecontainerA3" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="maptab-8">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverB3"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (5-Day)</h3>
                                                            <div id="treecontainerB3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (5-Day)</h3>
                                                            <div id="bubblecontainerB3" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" role="tabpanel" id="maptab-9">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverC3"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (20-Day)</h3>
                                                            <div id="treecontainerC3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (20-Day)</h3>
                                                            <div id="bubblecontainerC3" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>    

                                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">

                                            <div id="tatab3" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatab-7">250-Day High vs Low</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-8">MA<?php 
                                                        if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX")
                                                            echo "250";
                                                        else
                                                            echo "200";?>&nbsp;vs MA50
                                                    </a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-9">RSI Weekly vs RSI Daily</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="tatab-7">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From 250-Day High vs 250-Day Low</h3>
                                                            <div id="tabubblecontainerA3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="highlowA3"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-8">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From MA<?php if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX") echo "250"; else echo "200"; ?> vs MA50</h3>
                                                            <div id="tabubblecontainerB3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="MA50_LongB3"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-9">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Weekly RSI vs Daily RSI</h3>
                                                            <div id="tabubblecontainerC3" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="RSIC3"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div id="Top10" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Top 10 Market Cap Stocks<button id="peersBtn" class="btn btn-warning btn-sm" onclick="openTOOL()" style="margin:-2px 5px 0px 0px;float:right;font-weight:bold;color:#383838;height:29px">RACE</button></h2>
                                        </div>
                                        <div>
                                            <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                            <div>
                                                <div id="top10container3" style="width:auto;height:700px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-14">
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div id="chartcontainerC1" class="not-selectable">
                                    <div id="Heatmap" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Market Heatmap & Scatter</h2>
                                        </div>
                                        <div id="bodyContent">
                                            <div id="maptab" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptab-10">1 Day</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-11">5 Days</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptab-12">20 Days</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="maptab-10">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverA4"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (1-Day)</h3>
                                                            <div id="treecontainerA4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (1-Day)</h3>
                                                            <div id="bubblecontainerA4" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="maptab-11">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverB4"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (5-Day)</h3>
                                                            <div id="treecontainerB4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (5-Day)</h3>
                                                            <div id="bubblecontainerB4" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" role="tabpanel" id="maptab-12">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <div class="selectable" id="moverC4"></div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Heatmap (20-Day)</h3>
                                                            <div id="treecontainerC4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:36px 5% 20px 5%;">
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Stock Performance vs Relative Volume (20-Day)</h3>
                                                            <div id="bubblecontainerC4" style="width:auto;height:700px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>    

                                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">

                                            <div id="tatab4" class="panel panel-default" style="margin: 5px 0 0;">
                                                <ul class="nav nav-pills justify-content-center">
                                                    <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatab-10">250-Day High vs Low</a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-11">MA<?php 
                                                        if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX")
                                                            echo "250";
                                                        else
                                                            echo "200";?>&nbsp;vs MA50
                                                    </a></li>
                                                    <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatab-12">RSI Weekly vs RSI Daily</a></li>
                                                </ul>
                                            
                                                <div class="tab-content panel-body">
                                                    <div class="tab-pane active" role="tabpanel" id="tatab-10">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From 250-Day High vs 250-Day Low</h3>
                                                            <div id="tabubblecontainerA4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="highlowA4"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-11">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Deviation % From MA<?php if ($data == "SZSE" || $data == "SHSE" || $data == "HKEX") echo "250"; else echo "200"; ?> vs MA50</h3>
                                                            <div id="tabubblecontainerB4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="MA50_LongB4"></div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tatab-12">
                                                        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                                        <div>
                                                            <h3 class="sr-only"><?php echo $exchangeName." "; ?>Weekly RSI vs Daily RSI</h3>
                                                            <div id="tabubblecontainerC4" style="width:auto;height:700px;"></div>
                                                            <hr style="margin:0 5% 20px 5%;">
                                                            <div class="selectable" id="RSIC4"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div id="Top10" class="clean-card text-center container" style="padding:0;">
                                        <div class="card-header">
                                            <h2>Top 10 Market Cap Stocks<button id="peersBtn" class="btn btn-warning btn-sm" onclick="openTOOL()" style="margin:-2px 5px 0px 0px;float:right;font-weight:bold;color:#383838;height:29px">RACE</button></h2>
                                        </div>
                                        <div>
                                            <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                            <div>
                                                <div id="top10container4" style="width:auto;height:700px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<script src="https://code.highcharts.com/stock/8.2.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/pattern-fill.js"></script>
<script src="https://code.highcharts.com/8.2.0/highcharts-more.js"></script>
<script src="assets/js/TA.js"></script>
<script src="assets/js/Highstock.js?v=20260624.2"></script>
<script src="assets/js/stockCompare.js?v=20260623.10"></script>
<script src="assets/js/yearlyTrendChart.js?v=20260623.10"></script>
<script src="assets/js/seasonality.js?v=20260623.5"></script>
<script>
(function () {
    var originalSeasonalityChart = window.seasonalityChart;
    if (typeof originalSeasonalityChart !== "function")
        return;

    var indexNameMap = {
        "S&P 500": "SPX",
        "Nasdaq Composite": "Nas Composite",
        "Dow Industrial": "DJI",
        "Dow Transportation": "DJT",
        "FTSE 100": "UK 100",
        "TSX Composite": "Toronto Composite",
        "ASX 200": "Australia 200",
        "Nifty 50": "India 50",
        "Nikkei 225": "Japan 225",
        "Hang Seng": "Hong Kong"
    };

    function displayIndexName(text) {
        if (typeof text !== "string" || text.indexOf("S&P 500 ETF") !== -1)
            return text;

        Object.keys(indexNameMap).forEach(function (oldName) {
            text = text.replace(oldName, indexNameMap[oldName]);
        });
        return text;
    }

    window.seasonalityChart = function (stockcode, pricedata, firstDailyClose, chartcontainer, subtitle) {
        return originalSeasonalityChart(
            displayIndexName(stockcode),
            pricedata,
            firstDailyClose,
            chartcontainer,
            displayIndexName(subtitle)
        );
    };
})();
</script>
<script src="assets/js/sectorPerformance.js"></script>
<script src="assets/js/highcharts-theme.js?v=20260624.11"></script>

<!--script src="https://code.jquery.com/jquery-1.9.1.min.js"></script // commented out for DataTables -->
<script>
function loadMarketScript(src)
{
    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadMarketStyle(href)
{
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

var marketAnychartLibrariesLoaded = false;
var marketAnychartLibrariesReady = null;
var marketDataTableLibrariesLoaded = false;
var marketDataTableLibrariesReady = null;
var marketMajorEventsEnabled = <?php echo $benchmark === "SPY" ? 'true' : 'false'; ?>;
var marketMajorEventsLoaded = false;
var marketMajorEventsReady = null;

function ensureMarketAnychartLibraries()
{
    if (marketAnychartLibrariesReady)
        return marketAnychartLibrariesReady;

    loadMarketStyle('https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');
    loadMarketStyle('https://cdn.anychart.com/releases/8.9.0/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');

    marketAnychartLibrariesReady = loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33')
        .then(function() {
            return Promise.all([
                loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33')
            ]);
        })
        .then(function() {
            return loadMarketScript('assets/js/Treemap.js');
        })
        .then(function() {
            return loadMarketScript('assets/js/jquery.sparkline.min.js');
        })
        .then(function() {
            marketAnychartLibrariesLoaded = true;
        });

    return marketAnychartLibrariesReady;
}

function ensureMarketDataTableLibraries()
{
    if (marketDataTableLibrariesReady)
        return marketDataTableLibrariesReady;

    loadMarketStyle('https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css');
    loadMarketStyle('assets/css/dataTables.bootstrap4.min.css');
    loadMarketStyle('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');

    marketDataTableLibrariesReady = loadMarketScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js')
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js');
        })
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js');
        })
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js');
        })
        .then(function() {
            return loadMarketScript('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js');
        })
        .then(function() {
            marketDataTableLibrariesLoaded = true;
        });

    return marketDataTableLibrariesReady;
}

function ensureMarketMajorEvents()
{
    if (!marketMajorEventsEnabled)
        return Promise.resolve();

    if (marketMajorEventsReady)
        return marketMajorEventsReady;

    marketMajorEventsReady = loadMarketScript('assets/js/MajorEvents.js?v=20260619.4')
        .then(function() {
            marketMajorEventsLoaded = true;
        });

    return marketMajorEventsReady;
}

function areMarketTabLibrariesLoaded(tab)
{
    if (["3", "12", "13", "14"].indexOf(tab) !== -1)
        return marketAnychartLibrariesLoaded;
    if (["5", "10"].indexOf(tab) !== -1)
        return marketDataTableLibrariesLoaded;
    if (["9", "11"].indexOf(tab) !== -1)
        return marketDataTableLibrariesLoaded && (!marketMajorEventsEnabled || marketMajorEventsLoaded);
    return true;
}

function ensureMarketTabLibraries(tab)
{
    if (["3", "12", "13", "14"].indexOf(tab) !== -1)
        return ensureMarketAnychartLibraries();
    if (["5", "10"].indexOf(tab) !== -1)
        return ensureMarketDataTableLibraries();
    if (["9", "11"].indexOf(tab) !== -1)
        return Promise.all([
            ensureMarketDataTableLibraries(),
            ensureMarketMajorEvents()
        ]);
    return Promise.resolve();
}
</script>
<script src="assets/js/GaugeChart.js"></script>
<script>
window.__MARKET_PAGE_CONFIG = {
    "exchangeName": <?php echo json_encode($exchangeName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "benchmark": <?php echo json_encode($benchmark, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "benchmarkname": <?php echo json_encode($benchmarkname, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "longMA": <?php echo json_encode($longMA, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "valuationIdx": <?php echo json_encode($valuationIdx, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "ad": <?php echo json_encode($ad, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "shortMargin": <?php echo json_encode($short_Margin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "data": <?php echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "sort": <?php echo json_encode($sort, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "toMap": <?php echo json_encode($map, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "showbar": <?php echo json_encode($showbar, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "highlight": <?php echo json_encode($highlight, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "country": <?php echo json_encode($country, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "indexname": <?php echo json_encode($indexname, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/pages/market-main.js?v=20260623.10"></script>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/theme.js"></script>-->
    <script src="assets/js/ADChart.js"></script>
    <script src="assets/js/Bubble.js"></script>
    <!--<script src="assets/js/Anystock.js"></script>
    <script src="assets/js/CycleChart.js"></script>-->
    <!--<script src="assets/js/GaugeChart.js"></script>-->
    <!--script src="https://code.highcharts.com/maps/8.0.4/modules/map.js"></script-->
    <!--<script src="https://code.highcharts.com/stock/8.0.4/modules/pattern-fill.js"></script>-->
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
    <script src="assets/js/ValuationBands.js"></script>
    <!--<script src="assets/js/yaxis-panning.js"></script>-->
</body>

</html>
