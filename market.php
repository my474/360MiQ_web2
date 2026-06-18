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
        $indexname = "Hang Seng";
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
        $indexname = "S&P 500";
    }
    else if ($data == "NASDAQ")
    {
        $exchangeName = "Nasdaq";
        $benchmark = "QQQ";
        $benchmarkname = "Invesco QQQ Trust (QQQ)";     
        $valuationIdx = "NDX";
        $ad = "R";
        $short_Margin = "NASDAQMarginDebt";
        $indexname = "Nasdaq Composite";
    }
    else if ($data == "LSE")
    {
        $exchangeName = "London";
        $benchmark = "ISF.L";
        $benchmarkname = "FTSE 100 ETF (ISF.L)";  
        $valuationIdx = "FTSE100";
        $ad = "U";
        $indexname = "FTSE 100";
    }
    else if ($data == "ASX")
    {
        $exchangeName = "Australia";
        $benchmark = "STW.AX";
        $benchmarkname = "SP/ASX 200 Fund (STW.AX)";   
        $valuationIdx = "AS";
        $ad = "V";
        $indexname = "ASX 200";
    }
    else if ($data == "TSX")
    {
        $exchangeName = "Toronto TSX";
        $benchmark = "XIC.TO";
        $benchmarkname = "SP/TSX Composite Index ETF (XIC.TO)";  
        $valuationIdx = "SPTSX";
        $ad = "W";
        $indexname = "TSX Composite";
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
        $indexname = "Nikkei 225";
    }
    else
    {
        header("Location: /error");
        exit();
    }
?>
<!DOCTYPE html>
<html style="opacity: 1;">

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="<?php echo $exchangeName . " Market Indicators - 360MiQ.com"; ?>" />
    <meta property="og:url" content="https://360miq.com/market" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
    <meta name="description" content="360MiQ.com Market Indicators: Market Breadth, Short Selling Ratio, Sector Performance, Market Heatmap, PE Band, Advance Decline Ratio." />
    <meta property="og:description" content="360MiQ.com Market Indicators: Market Breadth, Short Selling Ratio, Sector Performance, Market Heatmap, PE Band, Advance Decline Ratio." />

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

<body>
<style>
.not-selectable {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
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
                                    <div id="chartcontainerA1" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA2" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA3" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA4" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA5" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA6" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerA7" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerB1" class="not-selectable" style="height: 525px;"></div>
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
                                        echo "<div id='chartcontainerB2' class='not-selectable' style='height: 525px;'></div>";
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
                                    <hr>
                                    <h3 class="sr-only"><?php echo $indexname." "; ?>Index Monthly Return % Table</h3>
                                    <h6 class="card-title" id="monthlytabletitle">Monthly Return %</h6>
                                    <table id="monthlytable" class="display" width="99%" style="margin:0 auto;padding: 0px 15px 15px 15px"></table>
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
                                    <div id="chartcontainerH1" class="not-selectable" style="height: 825px;"></div>
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
                                        <div id="chartcontainerH2" class="not-selectable" style="height: 725px;"></div>
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
                                    <h3 class="sr-only">Dow Jones Industrial Index Monthly Since 1700 with UK Stock Data & Major Historical Events</h3>
                                    <div id="chartcontainerI1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment1700" style="text-align:left;padding-right: 15px"></div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="I1Note">
                                      <span>
                                        The <span id="uslength"></span>year stock index chart is a long-term visualization that merges early UK equity market data (such as the South Sea Company) with the Dow Jones Industrial Average (DJIA) to represent centuries of equity market evolution. It is annotated with major U.S. historical events — including wars, financial crises, and territorial expansion — to show how markets responded over time.
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Early data (1700s–1800s)</strong> reflects British market performance during the colonial era, used as a proxy before U.S. financial markets were fully developed.</li>
                                        <li><strong>Transitional data (1885–1896)</strong> is based on the first Dow Jones stock averages, primarily railroad and transportation companies, providing the earliest benchmark of U.S. market performance before the formal DJIA.</li>
                                        <li><strong>U.S. data (post-1896)</strong> is represented by the Dow Jones Industrial Average, capturing industrialization, world wars, the Great Depression, the tech boom, and the modern era.</li>
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
                                    <h3 class="sr-only">Dow Jones Industrial Index Yearly Since 1700 with UK Stock Data & Major Historical Events</h3>
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
                                    <h3 class="sr-only">Dow Jones Industrial Index Average Monthly Return %</h3>
                                    <div id="chartcontainerSeasonality1700" class="not-selectable" style="height: 600px;"></div>
                                    <hr>
                                    <h3 class="sr-only">Dow Jones Industrial Index Monthly Return % Table</h3>
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
                                        The <span id="hklength"></span>year stock index chart, dating back to the establishment of British colonial Hong Kong in 1841, is a long-term visualization that merges early HK equity market data (such as HSBC) and UK stock data with the Hang Seng Index (HSI) to represent centuries of equity market evolution.<!-- It is annotated with major HK and China historical events — including wars, financial crises, and the handover of Hong Kong — to show how markets responded over time.-->
                                      </span>
                                      <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Early data (1841–1868)</strong> reflects UK market performance during the early colonial era, serving as a proxy before Hong Kong developed formal exchanges.</li>
                                        <li><strong>Hong Kong data (1868–1964)</strong> is based on locally available equities, with HSBC and other early listings forming the backbone of market representation.</li>
                                        <li><strong>Modern data (1964–present)</strong> is represented by the Hang Seng Index (HSI), capturing the city’s transformation into an international financial hub, through global shocks, the Asian financial crisis, the tech boom, and the 2008 financial crisis.</li>
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
<script src="assets/js/Highstock.js"></script>
<script src="assets/js/stockCompare.js"></script>
<script src="assets/js/yearlyTrendChart.js"></script>
<script src="assets/js/seasonality.js"></script>
<script src="assets/js/sectorPerformance.js"></script>
<script src="assets/js/highcharts-theme.js"></script>

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

var marketOptionalLibrariesLoaded = false;
var marketOptionalLibrariesReady = null;

function ensureMarketOptionalLibraries()
{
    if (marketOptionalLibrariesReady)
        return marketOptionalLibrariesReady;

    loadMarketStyle('https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');
    loadMarketStyle('https://cdn.anychart.com/releases/8.9.0/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');
    loadMarketStyle('https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css');
    loadMarketStyle('assets/css/dataTables.bootstrap4.min.css');
    loadMarketStyle('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');

    var marketAnychartReady = loadMarketScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33')
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
        });

    var marketDataTablesReady = loadMarketScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js')
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js');
        })
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js');
        })
        .then(function() {
            return loadMarketScript('https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js');
        });

    marketOptionalLibrariesReady = Promise.all([
        marketAnychartReady,
        marketDataTablesReady,
        loadMarketScript('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js'),
        loadMarketScript('assets/js/jquery.sparkline.min.js')
    ]).then(function() {
        marketOptionalLibrariesLoaded = true;
    });

    return marketOptionalLibrariesReady;
}
</script>
<script src="assets/js/GaugeChart.js"></script>
<script>
var exchangeName = "<?php echo $exchangeName; ?>";
var benchmark = "<?php echo $benchmark; ?>";
var benchmarkname = "<?php echo $benchmarkname; ?>";
var longMA = "<?php echo $longMA; ?>";
var valuationIdx = "<?php echo $valuationIdx; ?>";
var ad = "<?php echo $ad; ?>";
var short_Margin = "<?php echo $short_Margin; ?>";
var data = "<?php echo $data; ?>";
var sort = "<?php echo $sort; ?>";
var toMap = "<?php echo $map; ?>";
var showbar = "<?php echo $showbar; ?>";
var highlight = "<?php echo $highlight; ?>";
var country = "<?php echo $country; ?>";
var indexname = "<?php echo $indexname; ?>";

var HKlaborSubtitle = 'HK Census and Statistics Department';
var HKpropertySubtitle = 'HK Rating and Valuation Department';
var tab1loaded = false;
var daydict = {"1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
var monthdict = {"b":"01", "c":"02", "d":"03", "e":"04", "f":"05", "g":"06", "h":"07", "i":"08", "j":"09", "k":"10", "l":"11", "m":"12"};
var creditY = -388;
var codeStrSQL = '';
var featuredGridCount = 0;

// Render expensive charts one at a time and only when they are close to the viewport.
// This replaces empty AJAX requests that were previously used to yield to the browser.
var chartRenderQueue = [];
var queuedChartIds = new Set();
var renderedChartIds = new Set();
var chartObservers = new Map();
var chartRenderQueueRunning = false;

function yieldToBrowser()
{
    return new Promise(function(resolve) {
        var scheduleFrame = window.requestAnimationFrame || function(callback) {
            return window.setTimeout(callback, 16);
        };

        scheduleFrame(function() {
            window.setTimeout(resolve, 0);
        });
    });
}

function stopObservingChart(containerId)
{
    var observer = chartObservers.get(containerId);
    if (observer)
    {
        observer.disconnect();
        chartObservers.delete(containerId);
    }
}

function markChartRenderFailed(containerId, error)
{
    var container = document.getElementById(containerId);
    if (container)
        container.setAttribute('data-chart-render-error', 'true');

    if (window.console && console.error)
        console.error('Failed to render ' + containerId, error);
}

function enqueueChartRender(containerId, renderChart)
{
    if (renderedChartIds.has(containerId) || queuedChartIds.has(containerId))
        return;

    stopObservingChart(containerId);
    queuedChartIds.add(containerId);
    chartRenderQueue.push({
        containerId: containerId,
        renderChart: renderChart
    });
    runChartRenderQueue();
}

async function runChartRenderQueue()
{
    if (chartRenderQueueRunning)
        return;

    chartRenderQueueRunning = true;

    while (chartRenderQueue.length > 0)
    {
        var task = chartRenderQueue.shift();
        queuedChartIds.delete(task.containerId);

        if (renderedChartIds.has(task.containerId))
            continue;

        await yieldToBrowser();

        try
        {
            await task.renderChart();
            renderedChartIds.add(task.containerId);
        }
        catch (error)
        {
            markChartRenderFailed(task.containerId, error);
        }
    }

    chartRenderQueueRunning = false;
}

function lazyRenderChart(containerId, renderChart, rootMargin)
{
    var container = document.getElementById(containerId);
    if (!container || renderedChartIds.has(containerId) || queuedChartIds.has(containerId) || chartObservers.has(containerId))
        return;

    if (!('IntersectionObserver' in window))
    {
        enqueueChartRender(containerId, renderChart);
        return;
    }

    var observer = new IntersectionObserver(function(entries) {
        for (var i = 0; i < entries.length; i++)
        {
            if (entries[i].isIntersecting)
            {
                enqueueChartRender(containerId, renderChart);
                break;
            }
        }
    }, {
        root: null,
        rootMargin: rootMargin || '800px 0px',
        threshold: 0.01
    });

    chartObservers.set(containerId, observer);
    observer.observe(container);
}
const minGridNum = 3;

// remove Highcharts chart series data to prevent web scraping. if the charts act weird, remove this to restore to normal
// this is also in Highstock.js
/*Highcharts.addEvent(Highcharts.Chart, 'load', function() {
    setTimeout(function() {
        try
        {
            Highcharts.charts.forEach((element, index, array) => {
                if (array[index].title.textStr != "Advance Decline % (Daily)") // don't empty AD Chart data as it can't scroll
                    array[index].series.forEach((element, index, array) => {
                        array[index].options.data = [];
                    });
            });
        }
        catch {}
    }, 0);
});*/

var marketFeaturedPostsLoaded = false;

function loadMarketFeaturedPosts()
{
    if (marketFeaturedPostsLoaded)
        return;

    marketFeaturedPostsLoaded = true;
    marketCriticalRequests.featuredPosts.done(function(result){
        if (result == '')
        {
            document.getElementById("featuredpost").style.display = 'none';
        }
        
        var localcount = 0;
        const width = window.innerWidth;
        var jsonObject = result.split(/\r?\n|\r/);
        for (var i = 0; i < jsonObject.length; i++) {
            var fields = jsonObject[i].split('@');
            if (fields.length == 7)
            {
                var html = '';
                var htmltitle = '';
                if (fields[3] != '')
                {
                    var parts = fields[3].split('-');
                    var featured_date_diff = Date.now() - (new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]))).getTime();
                    if (featured_date_diff < 2592000000) // within the last 30 days
                    {
                        featuredGridCount++;
                        
                        if (fields[4] == '' && fields[5] == '' && fields[6] == '')
                        {
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] == '' && fields[5] != '' && fields[6] == '')
                        {
                            html += '<a href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="width: 100%;height: 100%" /></a>';
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] != '' && fields[5] == '' && fields[6] == '')
                        {
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] != '' && fields[5] != '' && fields[6] == '')
                        {
                            html += '<a href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="width: 100%;height: 100%" /></a>';
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 7px rgba(0, 0, 0, 0.2), 0px 0px 7px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        
                        // youtube
                        else if (fields[4] != '' && fields[5] == '' && fields[6] != '')
                        {
                            document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="text-align:left"></div>';
                            embedVideo(fields[6], 'featured-video-container' + (i + 1));
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                            
                            document.getElementById("featuredposttitle" + (i + 1)).style.paddingTop = "4px";
                        }
                        else if (fields[4] == '' && fields[5] == '' && fields[6] != '')
                        {
                            document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="text-align:left"></div>';
                            embedVideo(fields[6], 'featured-video-container' + (i + 1));
                            htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                            
                            document.getElementById("featuredposttitle" + (i + 1)).style.paddingTop = "4px";
                        }
                    }
                }
                
                document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML += html;
                document.getElementById("featuredposttitle" + (i + 1)).innerHTML = htmltitle;
                
                if ((width >= 768 && i > 0) || i == 0) {
                    document.getElementById("blogGridItem" + (i + 1)).style.display = "";
                    localcount++;
                }
                //document.getElementById("featuredposttitle" + (i + 1)).style.display  = "block";
                //document.getElementById("featuredposttitle" + (i + 1)).style.padding = "10px 10px 0.5px 10px";
                
                if (i == 0)
                {
                    document.getElementById("featuredpost").style.display = "";
                    document.getElementById("indicatorCard").style.marginTop = "0px";
                }
            }
        }
        
        document.getElementById("blogGrid").style.columnCount = localcount < minGridNum && window.innerWidth >= 768 ? minGridNum : localcount;
    });
}

function embedVideo(videoId, videoContainer, title = '', description = '', videoDetails = '', videoTitle = '', videoDescription = '') {
    // Create iframe element
    const iframe = document.createElement('iframe');
    iframe.src = `https://www.youtube.com/embed/${videoId}`;
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    iframe.style.width = "100%";
    // Create video details container
    //if (videoDetails != '')
    //    const videodetails = document.getElementById(videoDetails);

    // Create and append title
    if (videoTitle != '')
    {
        document.getElementById(videoTitle).textContent = title;
    }

    // Create and append description
    if (videoDescription != '')
    {
        document.getElementById(videoDescription).innerHTML = description;
    }

    // Append iframe and details to the container
    document.getElementById(videoContainer).appendChild(iframe);

}

function updateColumns() {
    const width = window.innerWidth;

    if (width < 768 && featuredGridCount > 0) {
        document.getElementById("blogGridItem2").style.display = "none";
        document.getElementById("blogGridItem3").style.display = "none";
        document.getElementById("blogGridItem4").style.display = "none";
        document.getElementById("blogGridItem5").style.display = "none";
        
        document.getElementById("blogGrid").style.columnCount = 1;
    }
    else
    {
        for(var i = 0; i < featuredGridCount; i++)
        {
            document.getElementById("blogGridItem" + (i + 1)).style.display = "";
        }
        
        document.getElementById("blogGrid").style.columnCount = featuredGridCount < minGridNum ? minGridNum : featuredGridCount;
    }
}

window.addEventListener("resize", updateColumns);
updateColumns(); // Run initially

window.addEventListener('popstate', function() {
  const params = new URLSearchParams(window.location.search);
  const tab = params.get('tab') || '1';
  $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show');
});

$('.nav-tabs a').on('shown.bs.tab', function (e) {
  e.preventDefault();  // Prevent default hash navigation

  const tabId = e.target.hash.replace('#tab-', '');
  const url = new URL(window.location);
  url.searchParams.set('tab', tabId);
  url.hash = '';  // Optional: remove hash if you don't want it

  history.replaceState(null, '', url.toString());

  // Manually show the tab content via Bootstrap's API or your own method if needed
  $(e.target).tab('show');
});

var initialTab =new URL(window.location).searchParams.get('tab');
if (window.location.hash == "" && (!initialTab || initialTab == 1))
{
    tab1();

    tab1loaded = true;
}
else
{
    yieldToBrowser().then(loadMarketFeaturedPosts);
}
function tab1()
{
    if (exchangeName == "Hong Kong" || exchangeName == "Shanghai" || exchangeName == "Shenzhen")
    {
        document.querySelectorAll('.longperiod').forEach(function(el) {
          el.textContent = 250;
        });
    }
    
    var dict = new Object();

    document.getElementById("failedtable").style.display = "none";
    $('#loader-icon').show();
    $('#loader-tip').show();

    marketCriticalRequests.marketData.done(function(result){
            var jsonObject = result.split(/\r?\n|\r/);
            for (var i = 0; i < jsonObject.length; i++) {
                if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                    jsonObject[i] = "," + jsonObject[i];
                    
                var row = jsonObject[i].split(',')
                if (i == 0)
                {
                    row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                }
                else if (row.length == 3)
                {
                    if (row[1].length == 1)
                        row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                    else if (row[1].length == 3)
                        row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                    else if (row[1].length == 5)
                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                        
                    row[1] = row[1].replace("-", "");
                }
                
        	    if (row.length == 3)
        	    { 
        	        if (row[0] != "")
        	            previousName = row[0];
    	            else
    	                row[0] = previousName;
    	                
            	    if (row[0] == "1")
            	        row[0] = "3>18";
        	        else if (row[0] == "2")
        	            row[0] = "10>50";
                    else if (row[0] == "3")
                        row[0] = "50>200";
                    else if (row[0] == "4")
                        row[0] = "50>250";
                    else if (row[0] == "5")
                        row[0] = ">20";
                    else if (row[0] == "6")
                        row[0] = ">50";
                    else if (row[0] == "7")
                        row[0] = ">200";
                    else if (row[0] == "8")
                        row[0] = ">250";
                    else if (row[0] == "9")
                        row[0] = "14d<30";
                    else if (row[0] == "A")
                        row[0] = "14d>50";
                    else if (row[0] == "B")
                        row[0] = "14d>70";
                    else if (row[0] == "C")
                        row[0] = "14w<30";
                    else if (row[0] == "D")
                        row[0] = "14w>50";
                    else if (row[0] == "E")
                        row[0] = "14w>70";
                    else if (row[0] == "F")
                        row[0] = "250HL";
                    else if (row[0] == "G")
                        row[0] = "Osc";
                    else if (row[0] == "H")
                        row[0] = "XIC.TO";
                    else if (row[0] == "I")
                        row[0] = "STW.AX";
                    else if (row[0] == "J")
                        row[0] = "ISF.L";
                    else if (row[0] == "K")
                        row[0] = "2800.HK";
                    else if (row[0] == "L")
                        row[0] = "159903.SZ";
                    else if (row[0] == "M")
                        row[0] = "510500.SH";
                    else if (row[0] == "N")
                        row[0] = "SPY";
                    else if (row[0] == "O")
                        row[0] = "QQQ";				
                    else if (row[0] == "0")
                        row[0] = "1306.T";	
                    else if (row[0] == "#")
                        row[0] = "NIFTYIETF.N";	
                        
    	            if (row[0] != "MD" && row[0] != "Sum" && row[0] != benchmark)
    	                row[2] /= 10; // percent * 10 to eliminate '.'
	                else
    	                row[2] /= 1;
    	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                    var previousDate = row[1];

                    if (dict[row[0]]) {
                         dict[row[0]].push([new Date(row[1]).getTime(), row[0] == benchmark ? row[2] : rounding(row[2], 10)]);
                    } else {
                        dict[row[0]] = [[new Date(row[1]).getTime(), row[0] == benchmark ? row[2] : rounding(row[2], 10)]];
                    }
        	    }
            }
            
            if (dict["Osc"])
                for (var i = 0; i < dict["Osc"].length; i++) {
                    if (dict["SumIdx"]) {
                        dict["SumIdx"].push([dict["Osc"][i][0], rounding((dict["Osc"][i][1]+dict["SumIdx"][i-1][1]), 100)]);
                    } else {
                        dict["SumIdx"] = [[dict["Osc"][i][0], dict["Osc"][i][1]]];
                    }
                }

            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
            var rangeselector = 0;
            var creditYoffset = 19;
            if (browserwidth > 576)
            {
                rangeselector = 2;
                creditYoffset = 0;
            }
            highstock('chartcontainerA1', dict, [benchmark, "3>18", "10>50", "50>" + longMA, "Hindenburg"], "MAˢʰᵒʳᵗ > MAˡᵒⁿᵍ Market Breadth (Daily)", exchangeName, benchmarkname, "% of stocks MAˢʰᵒʳᵗ above MAˡᵒⁿᵍ", 0, 100, "day", rangeselector, "line", 1, creditY + creditYoffset, 50);

            renderedChartIds.add('chartcontainerA1');
            yieldToBrowser().then(loadMarketFeaturedPosts);

            lazyRenderChart('chartcontainerA2', function() {
                highstock('chartcontainerA2', dict, [benchmark, ">20", ">50", ">" + longMA], "Close > MA Market Breadth (Daily)", exchangeName, benchmarkname, '% of stocks Close above MA', 0, 100, "day", rangeselector, "line", 1, creditY + creditYoffset, 50);
            });

            lazyRenderChart('chartcontainerA3', function() {
                highstock('chartcontainerA3', dict, [benchmark, "14w>50", "14w<30", "14w>70"], "RSI Market Breadth (Weekly)", exchangeName, benchmarkname, "% of stocks 14-Week RSI", 0, 100, "week", rangeselector == 2 ? 5 : 4, "line", 1, creditY, 50);
            });

            lazyRenderChart('chartcontainerA4', function() {
                highstock('chartcontainerA4', dict, [benchmark, "14d>50", "14d<30", "14d>70"], "RSI Market Breadth (Daily)", exchangeName, benchmarkname, "% of stocks 14-Day RSI", 0, 100, "day", rangeselector, "line", 1, creditY, 50);
            });

            lazyRenderChart('chartcontainerA5', function() {
                highstock('chartcontainerA5', dict, [benchmark, "250HL"], "250-Day High minus Low Market Breadth (Daily)", exchangeName, benchmarkname, "% of stocks at 250-Day High minus % of stocks at 250-Day Low", null, null, "day", rangeselector == 2 ? 3 : 2, "line", 1, creditY + creditYoffset, 0);
            });

            lazyRenderChart('chartcontainerA6', function() {
                //highstock('chartcontainerA6', dict, [benchmark, "AD"], "Advance - Decline Market Breadth", benchmarkname, "Advance - Decline %", null, null, "day", rangeselector, "line", 1, true);
                highstock('chartcontainerA6', dict, [benchmark, "Osc"], "McClellan Oscillator (Daily)", exchangeName, benchmarkname, "McClellan Oscillator", null, null, "day", rangeselector, "line", 1, creditY, 0);
            });

            lazyRenderChart('chartcontainerA7', function() {
                highstock('chartcontainerA7', dict, [benchmark, "SumIdx"], "McClellan Summation Index (Daily)", exchangeName, benchmarkname, "McClellan Summation Index", null, null, "day", rangeselector == 2 ? 4 : 3, "line", 1, creditY + creditYoffset, 0);
            });

            lazyRenderChart('chartcontainerA8', function() {
                return $.ajax({
                    url : "/db_market_trendgauge_get.php?data=" + data,
                    success: function(result){
                        var jsonObject = result.split(/\r?\n|\r/);

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var fontsize = '10px';
                        if (browserwidth <= 480)
                            fontsize = '9.2px';

                        if (jsonObject.length == 3)
                            trendgauge("chartcontainerA8", parseFloat(jsonObject[0]), parseFloat(jsonObject[1]), exchangeName, "#", fontsize, undefined, benchmarkname);
                        else
                            trendgauge("chartcontainerA8", -1, -1, exchangeName, "#", fontsize, undefined, benchmarkname);
                    }
                });
            });
    }).fail(function(error) {
        document.getElementById("failedtable").style.visibility = "visible";
        document.getElementById("failedcell").textContent = "Failed to load. Please refresh the page.";
    }).always(function() {
        $('#loader-icon').hide();
        $('#loader-tip').hide();
    });
}

    var tab2loaded = false, tab3loaded = false, tab4loaded = false, tab5loaded = false, tab6loaded = false, tab7loaded = false, tab8loaded = false, tab9loaded = false, tab10loaded = false, tab11loaded = false, tab12loaded = false, tab13loaded = false, tab14loaded = false;
    $(function() {
        openTabHash(); // for the initial page load
        window.addEventListener("hashchange", openTabHash, false); // for later changes to url
        
    });

    var marketOptionalTabWaiting = false;

    function openTabHash()
    {
        console.log('openTabHash');
                
        // Javascript to enable link to tab
        const urlNew = new URL(window.location);
        const params = urlNew.searchParams;
        let tab = params.get('tab');
        var hashTabMatch = window.location.hash.match(/^#tab-(\d+)$/);
        var requestedTab = tab || (hashTabMatch ? hashTabMatch[1] : "1");
        var optionalLibraryTabs = ["3", "5", "12", "13", "14"];

        if (optionalLibraryTabs.indexOf(requestedTab) !== -1 && !marketOptionalLibrariesLoaded)
        {
            if (marketOptionalTabWaiting)
                return;

            marketOptionalTabWaiting = true;
            ensureMarketOptionalLibraries().then(function() {
                marketOptionalTabWaiting = false;
                openTabHash();
            }).catch(function(error) {
                marketOptionalTabWaiting = false;
                if (window.console && console.error)
                    console.error('Failed to load optional market libraries.', error);
            });
            return;
        }
          
        var url = document.location.toString();
        if (url.match('#')) {
            $('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show') ;
        } 
        else if (tab) {
            $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show') ;
        }

        tab = new URL(window.location).searchParams.get('tab'); // need to find tab again as .tab('show') fires .on('shown.bs.tab', will render window.location.hash = ""
        
        var chartcontainer;
        var activeTab = this.hash;
        if (window.location.hash == "#tab-1" || tab == "1")
        {
            chartcontainer = "chartcontainerA1";
            tab1loaded = true;
        }
        else if (window.location.hash == "#tab-2" || tab == "2")
        {
            if (benchmark != "2800.HK" && benchmark != "159903.SZ" && benchmark != "510500.SH" && benchmark != "SPY" && benchmark != "QQQ")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerB1";
                tab2loaded = true;
            }
        }
        else if (window.location.hash == "#tab-3" || tab == "3")
        {
            //window.location.hash = "#tab-1"; // redirect to tab1, as tab3 not implemented
            
            chartcontainer = "chartcontainerC1";
            tab3loaded = true;
        }
        else if (window.location.hash == "#tab-4" || tab == "4")
        {
            chartcontainer = "chartcontainerD1";
            tab4loaded = true;
        }
        else if (window.location.hash == "#tab-5" || tab == "5")
        {
            chartcontainer = "chartcontainerE1";
            tab5loaded = true;
        }
        else if (window.location.hash == "#tab-6" || tab == "6")
        {
            if (benchmark != "2800.HK")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerF1";
                tab6loaded = true;
            }
        }
        else if (window.location.hash == "#tab-7" || tab == "7")
        {
            if (benchmark != "2800.HK")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerG1";
                tab7loaded = true;
            }
        }
        else if (window.location.hash == "#tab-8" || tab == "8")
        {
            if (benchmark != "SPY" && benchmark != "510500.SH" )
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerH1";
                tab8loaded = true;
            }
        }
        else if (window.location.hash == "#tab-9" || tab == "9")
        {
            if (benchmark != "SPY")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerI1";
                tab9loaded = true;
            }
        }
        else if (window.location.hash == "#tab-10" || tab == "10")
        {
            if (benchmark != "2800.HK")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerJ1";
                tab10loaded = true;
            }
        }
        else if (window.location.hash == "#tab-11" || tab == "11")
        {
            if (benchmark != "SPY")
                window.location.hash = "#tab-1";
            else
            {
                chartcontainer = "chartcontainerK1";
                tab11loaded = true;
            }
        }
        else if (window.location.hash == "#tab-12" || tab == "12")
        {
            //window.location.hash = "#tab-1"; // redirect to tab1, as tab3 not implemented
            
            chartcontainer = "chartcontainerC2";
            tab12loaded = true;
        }
        else if (window.location.hash == "#tab-13" || tab == "13")
        {
            //window.location.hash = "#tab-1"; // redirect to tab1, as tab3 not implemented
            
            chartcontainer = "chartcontainerC3";
            tab13loaded = true;
        }
        else if (window.location.hash == "#tab-14" || tab == "14")
        {
            //window.location.hash = "#tab-1"; // redirect to tab1, as tab3 not implemented
            
            chartcontainer = "chartcontainerC4";
            tab14loaded = true;
        }
        else if (window.location.hash != "")
            window.location.hash = "#tab-1"; // show tab1

        if (typeof(chartcontainer) != "undefined")
        {
            if (chartcontainer == "chartcontainerA1")
            {
                tab1();
            }
            else if (chartcontainer == "chartcontainerB1")
            {
                var dict = new Object();

                $.ajax({
                    url : "/db_market_get.php?data=" + short_Margin,
                    success : function(result){
                        var MDM = "Margin Debt Monthly $ (bil)";
                        var MDQ = "Margin Debt Quarterly Avg $ (bil)";
                        var MDY = "Margin Debt Yearly Avg $ (bil)";
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "," + jsonObject[i];
                    
                            var row = jsonObject[i].split(',')
                            if (i == 0)
                            {
                                if (parseInt(row[1].substring(0, 2)) >= 90)
                                    row[1] = "19" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];    
                                else
                                    row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];  
                            }
                            else if (row.length == 3)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5)
                                {
                                    if (parseInt(row[1].substring(0, 2)) >= 90)
                                        row[1] = "19" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];    
                                    else
                                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];                
                                }    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 3)
                    	    { 
	                	        if (row[0] != "")
                    	            previousName = row[0];
                	            else
                	                row[0] = previousName;
                	                
                    	        if (row[0] == "H")
                                    row[0] = "XIC.TO";
                                else if (row[0] == "I")
                                    row[0] = "STW.AX";
                                else if (row[0] == "J")
                                    row[0] = "ISF.L";
                                else if (row[0] == "K")
                                    row[0] = "2800.HK";
                                else if (row[0] == "L")
                                    row[0] = "159903.SZ";
                                else if (row[0] == "M")
                                    row[0] = "510500.SH";
                                else if (row[0] == "N")
                                    row[0] = "SPY";
                                else if (row[0] == "O")
                                    row[0] = "QQQ";
                                else if (row[0] == "0")
                                    row[0] = "1306.T";	
                                else if (row[0] == "#")
                                    row[0] = "NIFTYIETF.N";
                                    
                    	        if (row[0] == "ZM" || row[0] == "HM")
                    	            row[0] = "Margin Debt $ (bil CNY)";
                	            else if (row[0] == "ZS" || row[0] == "HS")
                    	            row[0] = "Short Selling $ (bil CNY)";

                                if (row[0] == "m") // NYSE, Nasdaq
                    	        {
                    	            row[0] = MDM;
                    	            row[2] /= 1;
                    	        }
                    	        else if (row[0] == "Q") // NYSE, Nasdaq
                    	        {
                    	            row[0] = MDQ;
                    	            row[2] /= 1;
                    	        }
                	            else if (row[0] == "Y") // NYSE, Nasdaq
                	            {
                    	            row[0] = MDY;
            	                    row[2] /= 1;
                	            }
                	            else if (row[0] == "X") // HK
                    	        {
                    	            row[0] = "HKexShortSelling";
                    	            
            	                    row[2] /= 10; // percent * 10 to eliminate '.'
                    	        }
                	            else if (row[0] != benchmark) // SZ, SH
                	                row[2] = rounding(row[2] / 100.0, 100); // percent * 10 to eliminate '.'
            	                else
            	                    row[2] /= 1;
            	                    
                 	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 0;
                        if (browserwidth > 576)
                            rangeselector = 2;
                            
                        if (benchmark == "159903.SZ" || benchmark == "510500.SH")
                        {
                            highstock('chartcontainerB1', dict, [benchmark, "Margin Debt $ (bil CNY)"], "Margin Debt Amount (Daily)", exchangeName, benchmarkname, "Margin Debt $ (bil CNY)", null, null, "day", rangeselector, "line", 1, creditY, NaN);
                            highstock('chartcontainerB2', dict, [benchmark, "Short Selling $ (bil CNY)"], "Short Selling Amount (Daily)", exchangeName, benchmarkname, "Short Selling $ (bil CNY)", null, null, "day", rangeselector, "line", 1, creditY, NaN);
                        }
                        else if (benchmark == "2800.HK")
                        {
                            if (dict["HKexShortSelling"])
                            {
                                var smaArray = [];
                                var sum = 0;
                                var mRange = 5;
                                for (var j = 0; j < mRange; j++) {
                                    sum += dict["HKexShortSelling"][j][1];
                                }
                                smaArray.push([dict["HKexShortSelling"][mRange-1][0], sum / mRange]);
                                // for the rest of the items, they are computed with the previous one
                                for (var i = mRange; i < dict["HKexShortSelling"].length; i++) {
                                    var last = smaArray[smaArray.length - 1];  
                                    smaArray.push([dict["HKexShortSelling"][i][0], rounding((last[1] * mRange - dict["HKexShortSelling"][i - mRange][1] + dict["HKexShortSelling"][i][1]) / mRange, 10)]);
                                }
                                dict["Short Selling % MA5"] = smaArray;
                            }
                            
                            highstock('chartcontainerB1', dict, [benchmark, "HKexShortSelling", "Short Selling % MA5"], "Short Selling Ratio (Daily)", exchangeName, benchmarkname, "Short Selling %", 5, null, "day", rangeselector, "column", 0.35, creditY, NaN);
                        }
                        else if (benchmark == "SPY" || benchmark == "QQQ")                                
                        {
                            highstock('chartcontainerB1', dict, [benchmark, MDM, MDQ, MDY], "Margin Debt (Monthly)", "FINRA", benchmarkname, "Margin Debt $ (bil)", null, null, "month", 6, "line", 1, creditY, NaN);
                            
                            var TAcommentTXT = ['<table style="width:100%; line-height: 1">'];
                            var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td>{0}</td></tr>';
                            var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td>{0}</td></tr>';
                            var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
                            var TAcommentHTMLup1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td>{0}</td></tr>';
                            var TAcommentHTMLdown1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td>{0}</td></tr>';
                            var TAcommentHTMLsideway1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
                            var tmp = 0;
                            var comment = "";
                            if (dict[MDY].length > 1  && dict[MDQ].length > 1)
                            {
                                if (dict[MDY][dict[MDY].length - 1][1] > dict[MDQ][dict[MDQ].length - 1][1] && 
                                    dict[MDY][dict[MDY].length - 2][1] < dict[MDQ][dict[MDQ].length - 2][1])
                                {
                                    tmp += 6;
                                    comment += "Margin Debt Quarterly Avg crossed below Yearly Avg";
                                }
                                else if (dict[MDY][dict[MDY].length - 1][1] < dict[MDQ][dict[MDQ].length - 1][1] && 
                                    dict[MDY][dict[MDY].length - 2][1] > dict[MDQ][dict[MDQ].length - 2][1])
                                {
                                    tmp++;
                                    comment += "Margin Debt Quarterly Avg crossed above Yearly Avg";
                                }
                                else if (dict[MDY][dict[MDY].length - 1][1] > dict[MDQ][dict[MDQ].length - 1][1])
                                {
                                    tmp += 6;
                                    comment += "Margin Debt Quarterly Avg is below Yearly Avg";
                                }
                                else if (dict[MDY][dict[MDY].length - 1][1] < dict[MDQ][dict[MDQ].length - 1][1])
                                {
                                    tmp++;
                                    comment += "Margin Debt Quarterly Avg is above Yearly Avg";
                                }
                            }
                            
                            if (comment != "")
                            {
                                if (tmp == 1)
                                    TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
                                else if (tmp == 6)
                                    TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
                            }  
                            
                            tmp = 0;
                            comment = "";
                            for (i = dict[benchmark].length - 1; i >= 0; i--) // remove excessive benchmark quotes
                            {
                                if (dict[benchmark][i][0] != dict[MDQ][dict[MDQ].length -1][0])
                                    dict[benchmark].pop();
                                else
                                    break;
                            }
                            var divergenceResult = divergence(benchmark, "Margin Debt Quarterly Avg", dict[benchmark], dict[MDQ], 3, 5);
                            tmp = divergenceResult[0];
                            comment = divergenceResult[1]; 
                            
                            if (comment != "")
                            {
                                if (tmp == 1)
                                    TAcommentTXT.push(TAcommentHTMLup.format(comment));
                                else if (tmp == 6)
                                    TAcommentTXT.push(TAcommentHTMLdown.format(comment));
                            }  
    
                            tmp = 0;
                            comment = "";
                            for (i = dict[benchmark].length - 1; i >= 0; i--) // remove excessive benchmark quotes
                            {
                                if (dict[benchmark][i][0] != dict[MDY][dict[MDY].length -1][0])
                                    dict[benchmark].pop();
                                else
                                    break;
                            }
                            var divergenceResult = divergence(benchmark, "Margin Debt Yearly Avg", dict[benchmark], dict[MDY], 3, 5);
                            tmp = divergenceResult[0];
                            comment = divergenceResult[1]; 
                            
                            if (comment != "")
                            {
                                if (tmp == 1)
                                    TAcommentTXT.push(TAcommentHTMLup.format(comment));
                                else if (tmp == 6)
                                    TAcommentTXT.push(TAcommentHTMLdown.format(comment));
                            }  
                            
                            document.getElementById("marginComment").innerHTML = TAcommentTXT.join("") + "</table>";                                
                        }
                    
                    }
                });
            }
            else if (chartcontainer == "chartcontainerC1")
            {    
                //cchart(chartcontainer);  
                var tmpname = data.toUpperCase();
                var isMobile = window.mobileAndTabletcheck();
                var sectorDict = new Object();
                
                $.ajax({
                    url : '/db_sectorperformance_get.php?data=' + tmpname,
                    success : function(result){
                        var rows = result.split(/\r?\n|\r/);
                
                        var dict_sector = new Object();
                
                        var datestr1 = "";
                        for (var i = 0; i < rows.length; i++)
                        {
                            var fields = rows[i].split('_');
                            if (fields.length == 5)
                            {
                                var datestr = "";
                                var d = new Date(fields[1]); 
                                // If the date object is invalid it 
                                // will return 'NaN' on getTime()  
                                // and NaN is never equal to itself. 
                                if (d.getTime() === d.getTime())
                                {
                                    datestr = fields[1];
                                    fields[1] = "Market";
                                    datestr1 = datestr;
                                }
                                    
                                if (dict_sector[fields[0]]) {
                                    dict_sector[fields[0]].push([fields[1], parseFloat(fields[2]), parseFloat(fields[3]), parseFloat(fields[4]), datestr]);
                                } else {
                                    dict_sector[fields[0]] = [[fields[1], parseFloat(fields[2]), parseFloat(fields[3]), parseFloat(fields[4]), datestr]];
                                }
                                
                                sectorDict[fields[1]] = [parseFloat(fields[2]), parseFloat(fields[3]), parseFloat(fields[4]), datestr1];
                            }
                        }
                        
                        if (isMobile && showbar === '')
                        {
                            if (sort !== '')
                                showbar = sort;
                            else
                                showbar = '1';
                        }
                        var chart1 = sectorPerformance('barcontainer1', exchangeName, '='+data.toUpperCase(), dict_sector[tmpname], false, sort, showbar, true);
                
                        $(window).resize(function() {
                            setLabelEvent(chart1, data.toUpperCase(), true);
                        });    
                        setLabelEvent(chart1, data.toUpperCase(), true);

                        // load heatmap & scatter after sector performance so that sector % is available
                        anychart.data.loadJsonFile('db_treemap_get.php?data=' + tmpname, function (data) {
                            var longperiod = 200;
                            if (exchangeName == "Hong Kong" || exchangeName == "Shanghai" || exchangeName == "Shenzhen")
                                longperiod = 250;
                                
        					var subtitle = data[0].product;
        
                            var dd = bubbleData(data, sectorDict);
                            
                            const d1_movers = getTopBottom10Movers(dd.d1);
                            const d5_movers = getTopBottom10Movers(dd.d5);
                            const d20_movers = getTopBottom10Movers(dd.d20);
                            
                            renderMovers("moverA1", d1_movers, subtitle, tmpname, '1 Day Performance');
                            renderMovers("moverB1", d5_movers, subtitle, tmpname, '5 Days Performance');
                            renderMovers("moverC1", d20_movers, subtitle, tmpname, '20 Days Performance');
                            
                            const dd_highlow = getTopBottom10HighLow(dd.hl250);
                            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
                            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
                            
                            renderMoversXY("highlowA1", dd_highlow, subtitle, tmpname, '250-Day High & 250-Day Low');
                            renderMoversXY("MA50_LongB1", dd_MA50_long, subtitle, tmpname, `MA${longperiod} & MA50`, `MA${longperiod}`);
                            renderMoversXY("RSIC1", dd_rsi, subtitle, tmpname, 'RSI Weekly & RSI Daily', 'RSI');
                            
                            treemap(data, "treecontainerA1", 1, 1, sectorDict, isMobile, highlight);
                            bubble(dd.d1, "bubblecontainerA1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                            bubble(dd.hl250, "tabubblecontainerA1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0, highlight);
                            
                            setTimeout(function() {
                                treemap(data, "treecontainerB1", 1, 5, sectorDict, isMobile, highlight);
                                bubble(dd.d5, "bubblecontainerB1", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                                bubble(dd.ma50_long, "tabubblecontainerB1", 1, '% From MA' + longperiod + ' vs MA50', subtitle, "", "% From MA" + longperiod, "% From MA50", null, null, '', false, true, true, true, true, 0, 0, highlight);
                            }, 100);
                            
                            setTimeout(function() {
                                treemap(data, "treecontainerC1", 1, 20, sectorDict, isMobile, highlight);
                                bubble(dd.d20, "bubblecontainerC1", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                                bubble(dd.rsi14w_d, "tabubblecontainerC1", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50, highlight);
                            }, 100);
                            
                            
                            var dataTop10 = dd.d1.slice(0, 10);
                            
                            codeStrSQL = '';
                            var codeArray = []
                            for (var i = 0; i < dataTop10.length; i++)
                            {
                                codeStrSQL += dataTop10[i].code;
                                if (i < dataTop10.length - 1)
                                    codeStrSQL += ",";
                                    
                                codeArray.push(dataTop10[i].code);
                            }

                            var dict = new Object();
                            var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
                            var isIEX = 0;
                            if (tmpname == "NYSE" || tmpname == "NASDAQ")
                                isIEX = 1;
                            $.ajax({
                                url : "db_top10_get.php?data=" + encodeURIComponent(codeStrSQL) + "&isIEX=" + isIEX,
                            /*    beforeSend: function(){
                                    document.getElementById("failedtable").style.display = "none";
                            		$('#loader-icon').show();
                            		$('#loader-tip').show();
                            	},
                            	complete: function(){
                            		$('#loader-icon').hide();
                            		$('#loader-tip').hide();
                            	},
                            	error: function (error) {
                            	    $('#loader-icon').hide();
                            	    $('#loader-tip').hide();
                                    document.getElementById("failedtable").style.visibility = "visible";
                                    document.getElementById("failedcell").textContent = "Failed to load. Please refresh the page.";
                                },*/
                                success : function(result){
                                    var jsonObject = result.split(/\r?\n|\r/);
                                    for (var i = 0; i < jsonObject.length; i++) {
                                        if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                            jsonObject[i] = "," + jsonObject[i];
                                            
                                        var row = jsonObject[i].split(',')
                                        /*if (i == 0)
                                        {
                                            row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                        }
                                        else*/ if (row.length == 3)
                                        {
                                            if (row[1].length == 1)
                                                row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                            else if (row[1].length == 3)
                                                row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                            else if (row[1].length == 5)
                                                row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                                
                                            row[1] = row[1].replace("-", "");
                                        }
                                        
                                	    if (row.length == 3)
                                	    { 
                                	        if (row[0] != "")
                                	            previousName = row[0];
                            	            else
                            	                row[0] = previousName;
                            	            
                                            row[2] /= 1;
                            	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                            var previousDate = row[1];
                            
                                            if (dict[row[0]]) {
                                                 dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                            } else {
                                                dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                            }
                                	    }
                                    }
                                    
                                    dict['tmp'] = [[1, 1]]; // Add a tmp series at the end to avoid error. Will remove it in stockCompare.
                                    codeArray.push('tmp'); // sort by market cap
                                    //const stocks = Object.keys(dict); // sort by stockcode
                                    var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                                    var rangeselector = 4;
                                    var creditYoffset = 26;
                                    if (browserwidth > 576)
                                    {
                                        rangeselector = 6;
                                        creditYoffset = 0;
                                    }
                                    stockCompare('top10container', dict, codeArray, exchangeName + " Top 10 Market Cap Stocks Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 1, true, true, dataTop10);
                                }
                            });
                        });
                    }
                });
            }
            else if (chartcontainer == "chartcontainerD1")
            {
                var dict = new Object();
                    
                $.ajax({
                    url : "/db_valuationbandsIndex_get.php?data="+benchmark,
                    success : function(result){
                        var EPS = [];
                        var pricedata = "";
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            
                            if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "," + jsonObject[i];
                                
                            var row = jsonObject[i].split(',')
                            if (i == 0)
                            {
                                row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 3)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5)
                                    row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                
                    	    if (row.length == 3)
                    	    { 
                                row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                
                    	        if (row[0] != "")
                    	            previousName = row[0];
                	            else
                	                row[0] = previousName;
                	                
                                if (row[0] == valuationIdx)
                                    EPS.push(row[1]+";"+row[2]);
                                else if (row[0] == benchmark)
                                    pricedata += row[1]+","+row[2]+"\n";
                                    
                    	        var previousDate = row[1];
                    	    }
                        }                            
                        
                        EPS.reverse();
                        var latestepsdate = EPS[EPS.length - 1].split(";")[0];
                        var datearray = latestepsdate.split("-");
                        var year = datearray[0];
                        var month = datearray[1];
                        var day = 31;
                        if (month == 4 || month == 6 || month == 9 || month == 11)
                            day = 30;
                        else if (month == 2)    
                        {
                            day = 28;
                            if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0))
                                day = 29;
                        }
                        EPS[EPS.length - 1] = year+"-"+month+"-"+day+";"+EPS[EPS.length - 1].split(";")[1];
                        
                        document.getElementById("valuationComment").innerHTML = Pxband(benchmark, EPS, pricedata, "PE Band", chartcontainer, 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', benchmarkname, exchangeName)[0];
                    }
                });
                
                if (benchmark == "SPY")
                {
                    var dict2 = new Object();
    
                    $.ajax({
                        url : "db_get_SP500_ratio.php",
                        success : function(result){
                            var jsonObject = result.split(/\r?\n|\r/);
                            for (var i = 0; i < jsonObject.length; i++) {
                                var row = jsonObject[i].split(',')
                                
                                if (row.length == 3)
                                {
                                    if (row[0] == "A")
                        	            row[0] = "Shiller PE";
                    	            else if (row[0] == "B")
                        	            row[0] = "S&P 500 Real Price";
                    	            else if (row[0] == "C")
                        	            row[0] = "S&P 500 Earnings Yield";
                    	            else if (row[0] == "D")
                        	            row[0] = "S&P 500 Dividend Yield";
                        	            
                                    if (dict2[row[0]]) {
                                         dict2[row[0]].push([new Date(row[1]).getTime(), parseFloat(row[2])]);
                                    } else {
                                        dict2[row[0]] = [[new Date(row[1]).getTime(), parseFloat(row[2])]];
                                    }
                                }
    
                            }
                            
                            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                            var rangeselector = 6;
                            //if (browserwidth > 576)
                              //  rangeselector = 6;
                                
                        
                            highstock('chartcontainerD2', dict2, ["S&P 500 Real Price", "Shiller PE"], "Shiller PE - Cyclically Adjusted PE (CAPE)", exchangeName, "S&P 500 Real Price", "Shiller PE Ratio", null, null, "month", rangeselector, "line", 1, creditY, NaN, 'logarithmic');
                            highstock('chartcontainerD3', dict2, ["S&P 500 Real Price", "S&P 500 Earnings Yield", "S&P 500 Dividend Yield"], "S&P 500 Earnings Yield & Dividend Yield", exchangeName, "S&P 500 Real Price", "Yield %", null, null, "month", rangeselector, "line", 1, creditY, NaN, 'logarithmic');
                        }
                    });
                }
            }                      
            else if (chartcontainer == "chartcontainerE1")
            {
                var dict = new Object();
                
                $.ajax({
                    url : "/db_market_get.php?data=" + data + "Adv",
                    success : function(result){
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "," + jsonObject[i];
                    
                            var row = jsonObject[i].split(',')
                            if (i == 0)
                            {
                                row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 3)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5)
                                    row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];                
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 3)
                    	    { 
	                	        if (row[0] != "")
                    	            previousName = row[0];
                	            else
                	                row[0] = previousName;
                	                
                    	        if (row[0] == "H")
                                    row[0] = "XIC.TO";
                                else if (row[0] == "I")
                                    row[0] = "STW.AX";
                                else if (row[0] == "J")
                                    row[0] = "ISF.L";
                                else if (row[0] == "K")
                                    row[0] = "2800.HK";
                                else if (row[0] == "L")
                                    row[0] = "159903.SZ";
                                else if (row[0] == "M")
                                    row[0] = "510500.SH";
                                else if (row[0] == "N")
                                    row[0] = "SPY";
                                else if (row[0] == "O")
                                    row[0] = "QQQ";
                                else if (row[0] == "0")
                                    row[0] = "1306.T";	
                                else if (row[0] == "#")
                                    row[0] = "NIFTYIETF.N";
                                    
                    	        if (row[0] == ad)
                    	        {
                    	            row[0] = "Adv";
                    	            
            	                    row[2] /= 10; // percent * 10 to eliminate '.'
            	                    
            	                    row[2] = Math.round(row[2]);
                    	        }
                    	        else
                    	            row[2] /= 1;

                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                }
                                
                                if (row[0]=="Adv")
                                {
                                    if (dict["Dec"]) {
                                        dict["Dec"].push([new Date(row[1]).getTime(), 100-row[2]]);
                                    } else {
                                        dict["Dec"] = [[new Date(row[1]).getTime(), 100-row[2]]];
                                    }
                                }
                    	    }
                        }

                        document.getElementById("adComment").innerHTML = adchart(chartcontainer, dict, [benchmark, "Adv", "Dec"], "Advance Decline % (Daily)", exchangeName, benchmarkname, "Adv Dec %", 0, 100, "day", 0, "column");
                    }
                });
                
                
                dict = new Object();
                
                $.ajax({
                    url : "db_index_get.php?data=" + data,
                    success : function(result){
                        var indexname = '';
                        var previousDate = '';
                        var previousDateTick = new Date('1900-01-01');
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "," + jsonObject[i];
                                
                            var row = jsonObject[i].split(',')
                            if (i == 0)
                            {
                                var startyear = parseInt(row[1].substring(0, 2));
                                row[1] = (startyear < 15? "20" : "19") + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 3)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5)
                                {
                                    var tmprow = "19" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)].replace("-", "");
                                    var tmprowTick = new Date(tmprow.substr(0,4) + "-" + tmprow.substr(4,2) + "-" + tmprow.substr(6,2)).getTime();
                                    if (previousDateTick.getTime() > tmprowTick || previousDateTick.getYear() >= 2000)
                                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else
                                        row[1] = tmprow;
                                }
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            if (row.length > 1 && row[1].length == 8)
                                previousDateTick = new Date(row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2));
                            
                    	    if (row.length == 3)
                    	    { 
                    	        if (row[0] != "")
                    	            previousName = row[0];
                	            else
                	                row[0] = previousName;
                	            
                	            if (row[0] == "1")
                                    row[0] = "S&P 500";    
                        	    else if (row[0] == "2")
                                    row[0] = "Nasdaq Composite";
                    	        else if (row[0] == "3")
                    	            row[0] = "FTSE 100";
                                else if (row[0] == "4")
                                    row[0] = "TSX Composite";
                                else if (row[0] == "5")
                        	        row[0] = "ASX 200";
                    	        else if (row[0] == "6")
                                    row[0] = "Nifty 50";
                                else if (row[0] == "7")
                                    row[0] = "Nikkei 225";
                                else if (row[0] == "8")
                                    row[0] = "Hang Seng";                
                                else if (row[0] == "9")
                                    row[0] = "Shanghai Composite";
                                else if (row[0] == "A")
                                    row[0] = "Shenzhen Composite";
                                else if (row[0] == "B")
                                    row[0] = "BSE Sensex";
                                    
                                row[2] /= 1;
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
                
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                     
                                    if (dict['Daily Return %'])
                                        dict['Daily Return %'].push([new Date(row[1]).getTime(), rounding((row[2] / dict[row[0]][dict[row[0]].length - 2][1] - 1) * 100, 100) ]);
                                    else
                                        dict['Daily Return %'] = [[new Date(row[1]).getTime(), rounding((row[2] / dict[row[0]][dict[row[0]].length - 2][1] - 1) * 100, 100) ]];
                                     
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                    indexname = row[0];
                                }
                    	    }
                        }
                        
                        dict[indexname + ' Daily'] = dict[indexname];

                        dict[indexname + ' Weekly'] = dataCompression_weekly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Weekly'].length; i++)
                        {
                            if (dict['Weekly Return %'])
                                dict['Weekly Return %'].push([dict[indexname + ' Weekly'][i][0], rounding((dict[indexname + ' Weekly'][i][1] / dict[indexname + ' Weekly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Weekly Return %'] = [[dict[indexname + ' Weekly'][i][0], rounding((dict[indexname + ' Weekly'][i][1] / dict[indexname + ' Weekly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        dict[indexname + ' Monthly'] = dataCompression_monthly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Monthly'].length; i++)
                        {
                            if (dict['Monthly Return %'])
                                dict['Monthly Return %'].push([dict[indexname + ' Monthly'][i][0], rounding((dict[indexname + ' Monthly'][i][1] / dict[indexname + ' Monthly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Monthly Return %'] = [[dict[indexname + ' Monthly'][i][0], rounding((dict[indexname + ' Monthly'][i][1] / dict[indexname + ' Monthly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        dict[indexname + ' Yearly'] = dataCompression_yearly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Yearly'].length; i++)
                        {
                            if (dict['Yearly Return %'])
                                dict['Yearly Return %'].push([dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Yearly Return %'] = [[dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        delete dict[indexname];

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 0;
                        if (browserwidth > 576)
                            rangeselector = 2;
                            
                        highstock('chartcontainerE2', dict, [indexname + ' Daily', "Daily Return %"], indexname + " Index (Daily Return %)", "", indexname, "Daily Return %", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false);
                        document.getElementById("dailyReturnComment").innerHTML = returnComment(indexname, dict[indexname + ' Daily'], dict['Daily Return %'], "days");
                        
                        highstock('chartcontainerE3', dict, [indexname + ' Weekly', "Weekly Return %"], indexname + " Index (Weekly Return %)", "", indexname, "Weekly Return %", null, null, "day", 4, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false);
                        document.getElementById("weeklyReturnComment").innerHTML = returnComment(indexname, dict[indexname + ' Weekly'], dict['Weekly Return %'], "weeks");
                        
                        highstock('chartcontainerE4', dict, [indexname + ' Monthly', "Monthly Return %"], indexname + " Index (Monthly Return %)", "", indexname, "Monthly Return %", null, null, "day", 3, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("monthlyReturnComment").innerHTML = returnComment(indexname, dict[indexname + ' Monthly'], dict['Monthly Return %'], "months");
                        
                        highstock('chartcontainerE5', dict, [indexname + ' Yearly', "Yearly Return %"], indexname + " Index (Yearly Return %)", "", indexname, "Yearly Return %", null, null, "day", 6, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false, [{type: 'year', count: 5, text: '5y'}, {type: 'year', count:10, text:'10y'}, {type: 'year', count: 15, text: '15y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'year', count: 50, text: '50y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("yearlyReturnComment").innerHTML = returnComment(indexname, dict[indexname + ' Yearly'], dict['Yearly Return %'], "years");
                        
                        seasonality('chartcontainerYearlyTrend', dict, indexname, '#monthlytable', "#slider", 'monthlytabletitle', 'chartcontainerSeasonality', dict[indexname + ' Daily'][0][1], dict[indexname + ' Daily']);
                        /*monthlytabledata = seasonalityChart(indexname + ' Index', dict[indexname + ' Monthly'], dict[indexname + ' Daily'][0][1], 'chartcontainerSeasonality', indexname);
                        
                        if (monthlytabledata !== null)
                        {
                            document.getElementById("monthlytabletitle").textContent = indexname + " Index Monthly Return %";
                            
                            var dataSet = [];
                            
                            
                            Object.keys(monthlytabledata).forEach(function(key) {
                                var dataSettmp = [];
                                dataSettmp = [key, null, null, null, null, null, null, null, null, null, null, null, null, null];
                                for (const [key1, value1] of Object.entries(monthlytabledata[key]))
                                {
                                    
                                    for (const [key2, value2] of Object.entries(value1)) {
                                        if (key2 == "January")
                                            dataSettmp[1] = value2;
                                        else if (key2 == "February")
                                            dataSettmp[2] = value2;
                                        else if (key2 == "March")
                                            dataSettmp[3] = value2;
                                        else if (key2 == "April")
                                            dataSettmp[4] = value2;
                                        else if (key2 == "May")
                                            dataSettmp[5] = value2;
                                        else if (key2 == "June")
                                            dataSettmp[6] = value2;
                                        else if (key2 == "July")
                                            dataSettmp[7] = value2;
                                        else if (key2 == "August")
                                            dataSettmp[8] = value2;
                                        else if (key2 == "September")
                                            dataSettmp[9] = value2;
                                        else if (key2 == "October")
                                            dataSettmp[10] = value2;
                                        else if (key2 == "November")
                                            dataSettmp[11] = value2;
                                        else if (key2 == "December")
                                            dataSettmp[12] = value2;
                                        else if (key2 == "YTD")
                                            dataSettmp[13] = value2;
                                    }
                                    
                                }
                                dataSet.push(dataSettmp);
                            });

                            var table = $('#monthlytable').DataTable({
                                responsive: true,
                                order: [[0, 'desc']],
                                columns: [
                                    { title: 'Year', className: 'dt-body-center' },
                                    { title: 'Jan', className: 'dt-body-center' },
                                    { title: 'Feb', className: 'dt-body-center' },
                                    { title: 'Mar', className: 'dt-body-center' },
                                    { title: 'Apr', className: 'dt-body-center' },
                                    { title: 'May', className: 'dt-body-center' },
                                    { title: 'Jun', className: 'dt-body-center' },
                                    { title: 'Jul', className: 'dt-body-center' },
                                    { title: 'Aug', className: 'dt-body-center' },
                                    { title: 'Sep', className: 'dt-body-center' },
                                    { title: 'Oct', className: 'dt-body-center' },
                                    { title: 'Nov', className: 'dt-body-center' },
                                    { title: 'Dec', className: 'dt-body-center' },
                                    { title: 'YTD', className: 'dt-body-center' },
                                ],
                                data: dataSet,
                                searching: false, paging: false, info: false,
                                rowCallback: function(row, data, index){
                                    $(row).find('td:eq(0)').css('color', 'dimgrey').css('font-weight', 'bold').css('font-size', '14px');
                                    for (var i = 1; i < data.length; i++)
                                    {
                                        if (data[i] === null)
                                            continue;
                                            
                                        if(data[i] >= -0.5 && data[i] < 0.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#666666').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= 0.5 && data[i] < 1.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#008f00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= 1.5 && data[i] < 4.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#009900').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= 4.5 && data[i] < 7.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#00ba00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= 7.5 && data[i] < 10.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#00dd00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= 10.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#00ff00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= -1.5 && data[i] < -0.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#8f0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= -4.5 && data[i] < -1.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#990000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= -7.5 && data[i] < -4.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#ba0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] >= -10.5 && data[i] < -7.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#dd0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        else if(data[i] < -10.5)
                                        {
                                            $(row).find('td:eq('+i+')').css('background-color', '#ff0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                                        }
                                        
                                        if(i == data.length - 1)
                                        {
                                            $(row).find('td:eq('+i+')').css('text-shadow', '0 0 3px #000000, 0 0 5px #000000').css('border-left', '2px solid #000');
                                        }
                                    }
                                }
                            });
                        }
                        
                        var min_current = 0;
                        var max_current = 0;
                        if (dict[indexname + ' Monthly'].length > 0 && dict[indexname + ' Monthly'].length - 1 > 0 && dict[indexname + ' Monthly'][0].length > 0  && dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1].length > 0)
                        {
                            min_current = new Date(dict[indexname + ' Monthly'][0][0]).getFullYear();
                            max_current = new Date(dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1][0]).getFullYear();
                        }
                        
                        $("#slider").ionRangeSlider({
                        	skin: "round",
                        	prettify_enabled: false,
                        	//grid_snap: true,
                        	grid_num: 5,
                        	drag_interval: true,
                            min: min_current,
                            max: max_current,
                            from: min_current,
                            to: max_current,
                            onChange: function (data) {
                                        //console.log(`Range changed for: ${e.detail.sliderId}. Min/Max range values are available in this object too`);
                                        var dictIndexnameMonthly = dict[indexname + ' Monthly'].map((x) => x);
                                        var minRangeValue = data.from;
                                        var maxRangeValue = data.to;
                                        
                                        if (min_current == minRangeValue && max_current == maxRangeValue)
                                            return;
                                            
                                        if (minRangeValue !== null && maxRangeValue !== null)
                                        {
                                            var startIndex = 0;
                                            var endIndex = dictIndexnameMonthly.length - 1;
                                            
                                            for (var i = 0; i < dictIndexnameMonthly.length; i++)
                                            {
                                                if (i > 0 && new Date(dictIndexnameMonthly[i][0]).getFullYear() == minRangeValue &&
                                                    new Date(dictIndexnameMonthly[i - 1][0]).getFullYear() < minRangeValue)
                                                    startIndex = i - 1;
                                                    
                                                if (new Date(dictIndexnameMonthly[i][0]).getFullYear() < maxRangeValue + 1)
                                                {
                                                    endIndex = i;
                                                }
                                            }
                                        }
                                        
                                        if (endIndex >= 0)
                                        {
                                            dictIndexnameMonthly.splice(endIndex + 1);
                                        }
                                        
                                        if (startIndex >= 0)
                                        {
                                            dictIndexnameMonthly.splice(0, startIndex);
                                        }
                                        
                                        seasonalityChart(indexname + ' Index', dictIndexnameMonthly, dictIndexnameMonthly[0][0] == dict[indexname + ' Monthly'][0][0] && new Date(dictIndexnameMonthly[0][0]).getFullYear() == minRangeValue ? dict[indexname + ' Daily'][0][1] : null, 'chartcontainerSeasonality', indexname);
                                        
                                        min_current = minRangeValue;
                                        max_current = maxRangeValue;
                                    }
                        });*/
                    }
                });
            }
            else if (chartcontainer == "chartcontainerF1")
            {
                var dict = new Object();

                $.ajax({
                    url : "db_get_CCASS_market.php",
                    success : function(result){
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            var row = jsonObject[i].split(',')

                    	    if (row.length == 3)
                    	    { 
                                if (dict['2800.HK']) {
                                     dict['2800.HK'].push([new Date(row[0]).getTime(), parseFloat(row[2])]);
                                } else {
                                    dict['2800.HK'] = [[new Date(row[0]).getTime(), parseFloat(row[2])]];
                                }
                                
                                if (dict['Southbound Flow $ (mil)']) {
                                     dict['Southbound Flow $ (mil)'].push([new Date(row[0]).getTime(), parseFloat(row[1])]);
                                } else {
                                    dict['Southbound Flow $ (mil)'] = [[new Date(row[0]).getTime(), parseFloat(row[1])]];
                                }
                    	    }
                        }
                        
                        var monthlyFund = 0;
                        if (dict['Southbound Flow $ (mil)'].length > 0)
                        {
                            var lastdate = new Date(dict['Southbound Flow $ (mil)'][dict['Southbound Flow $ (mil)'].length - 1][0]);
                            monthlyFund = dict['Southbound Flow $ (mil)'][dict['Southbound Flow $ (mil)'].length - 1][1];
                            
                            for (var i = dict['Southbound Flow $ (mil)'].length - 2; i >= 0; i--) {
                                var tmpdate = new Date(dict['Southbound Flow $ (mil)'][i][0]);
                                if (tmpdate.getFullYear() == lastdate.getFullYear() && tmpdate.getMonth() == lastdate.getMonth())
                                {
                                    monthlyFund += dict['Southbound Flow $ (mil)'][i][1];
                                }
                                else
                                    break;
                            }
                            
                            var seriesname = [];
                            var count = 0;
                            for (var i = 1; i < dict['Southbound Flow $ (mil)'].length; i++) {
                                var tmpdate = new Date(dict['Southbound Flow $ (mil)'][i][0]);

                                if (lastdate.getFullYear() - 4 > tmpdate.getFullYear() )
                                    continue;
                                else
                                {
                                    if (dict[tmpdate.getFullYear()])
                                    {
                                        dict[tmpdate.getFullYear()].push([dayofyear(tmpdate), Math.round(dict[tmpdate.getFullYear()][dict[tmpdate.getFullYear()].length - 1][1] + dict['Southbound Flow $ (mil)'][i][1])]);
                                    }
                                    else
                                    {
                                        dict[tmpdate.getFullYear()] = [[dayofyear(tmpdate), dict['Southbound Flow $ (mil)'][i][1]]];
                                        seriesname[count] = tmpdate.getFullYear();
                                        count++;
                                    }
                                }
                            }
                            
                            var last_year_index;
                            lastdate = new Date(dict['2800.HK'][dict['2800.HK'].length - 1][0]);
                            for (var i = dict['2800.HK'].length - 2; i >= 0; i--) {
                                var tmpdate = new Date(dict['2800.HK'][i][0]);
                                if (tmpdate.getFullYear() != lastdate.getFullYear())
                                {
                                    last_year_index = i;
                                    break;
                                }
                            }
                            
                            for (var i = last_year_index + 1; i < dict['2800.HK'].length; i++) {
                                var tmpdate = new Date(dict['2800.HK'][i][0]);
                                if (dict['2800.HK_%'])
                                {
                                    dict['2800.HK_%'].push([dayofyear(tmpdate), rounding((dict['2800.HK'][i][1] / dict['2800.HK'][last_year_index][1] - 1) * 100, 100)]);
                                }
                                else
                                {
                                    dict['2800.HK_%'] = [[dayofyear(tmpdate), rounding((dict['2800.HK'][i][1] / dict['2800.HK'][last_year_index][1] - 1) * 100, 100)]];
                                }
                            }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 0;
                        if (browserwidth > 576)
                            rangeselector = 2;
                            
                        highstock('chartcontainerF1', dict, ['2800.HK', "Southbound Flow $ (mil)"], "HK Stock Connect Southbound Fund Flow (Daily)", "", benchmarkname, "Daily Southbound Flow $ (mil)", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'linear', false, true, 'sum');
                        document.getElementById("dailyCCASSComment").innerHTML = CCASSComment(dict["Southbound Flow $ (mil)"], "day");
                        
                        highstock('chartcontainerF2', dict, ['2800.HK', "Southbound Flow $ (mil)"], "HK Stock Connect Southbound Fund Flow (Monthly)", "", benchmarkname, "Monthly Southbound Flow $ (mil)", null, null, "month", 5, "column", 1, creditY + 2, null, 'linear', false, true, 'sum', monthlyFund);
                        document.getElementById("monthlyCCASSComment").innerHTML = CCASSComment(CCASSmonthlyCompression(dict["Southbound Flow $ (mil)"]), "month");

                        var benchmark_title = '2800.HK '+ seriesname[4] +' Return %';
                        var text_array = [];
                        var text_count = 0;
                        var chart = Highcharts.chart('chartcontainerF3',{
                        	title: { 
                    	        text: 'HK Stock Connect Southbound Cumulative Annual Fund Flow',
                            	style: {
                                    fontSize: '16px',
                                },
                        	},
                        	chart: {
                                style: {
                                    fontFamily: 'Montserrat'
                                },
                                /*events: {
                                    render() {
                                        const chart = this
                                        const series = chart.series
                                
                                        for(var i = 0; i < text_array.length; i++)
                                        {
                                            text_array[i].destroy();
                                        }
                                        text_array = [];
                                        
                                        series.forEach((s) => {
                                            const len = s.data.length
                                            const point = s.data[len - 1]
                                            
                                            if (point.series.name != benchmark_title)
                                                text_array[text_count] = chart.renderer.text(
                                                  	point.series.name,
                                                    point.plotX + chart.plotLeft - 30,
                                                    point.plotY + chart.plotTop - 5
                                                )
                                                .attr({
                                              	    fill: point.series.color,
                                                    zIndex: 5
                                                })
                                                .add();
                                                 text_count++;
                                        })
                                    }
                                }*/
                            },
                            plotOptions: {
                                series: {
                                    dataLabels: {
                                        enabled: true,
                                        align: 'right',
                                        x: 4,
                                        formatter: function() {
                                            if (this.point.index == this.series.points.length - 1 && this.series.index != 0) {
                                                var fontsize = '';
                                                if (this.series.index == 5)
                                                    fontsize = ';font-size:14';

                                                return '<span style="color:' + this.point.color + fontsize + '">'+this.series.name+'</span> ';
                                            } else {
                                                return null;
                                            }
                                        },
                                        crop: false,
                                        overflow: false
                                    }
                                }
                            },
                        	xAxis: {
                        	    crosshair: {
                                    color: '#9d81ba88'
                                },
                        		title: { text: 'Day of Year' },
                        		tickInterval: 30,
                        		tickAmount: 12,
                        		ceiling: 366,
                    			visible: true,
                    			dateTimeLabelFormaty: {month: '%b'},
                        		min: 1,
                        		max: 366,
                        		minPadding: 0.0,
                        		maxPadding: 0.0,
                        		labels: {
                                    rotation: browserwidth <= 576 ? -45 : 0
                                },
                                plotLines: [{
                                    color: '#c9c9c9', // Color value
                                    value: 1, // Value of where the line will appear
                                    width: 0,
                                    dashStyle: 'Dash',
                                    label: {
                                        text: 'Q1', // Label for the next quarter
                                        rotation: 0,
                                        style: { color: '#c9c9c9', fontSize: '14px' },
                                        x: 1, // Adjust horizontal position
                                        y: 12    // Position above the line
                                    }
                                }, {
                                    color: '#c9c9c9', // Color value
                                    value: 91, // Value of where the line will appear
                                    dashStyle: 'Dash',
                                    label: {
                                        text: 'Q2', // Label for the next quarter
                                        rotation: 0,
                                        style: { color: '#c9c9c9', fontSize: '14px' },
                                        x: 1, // Adjust horizontal position
                                        y: 12    // Position above the line
                                    }
                                }, {
                                    color: '#c9c9c9', // Color value
                                    value: 182, // Value of where the line will appear
                                    dashStyle: 'Dash',
                                    label: {
                                        text: 'Q3', // Label for the next quarter
                                        rotation: 0,
                                        style: { color: '#c9c9c9', fontSize: '14px' },
                                        x: 1, // Adjust horizontal position
                                        y: 12    // Position above the line
                                    }
                                }, {
                                    color: '#c9c9c9', // Color value
                                    value: 274, // Value of where the line will appear
                                    dashStyle: 'Dash',
                                    label: {
                                        text: 'Q4', // Label for the next quarter
                                        rotation: 0,
                                        style: { color: '#c9c9c9', fontSize: '14px' },
                                        x: 1, // Adjust horizontal position
                                        y: 12    // Position above the line
                                    }
                                }]
                    		},
                			yAxis: [{
                                title: {
                                    text: benchmark_title,
                                    style: {
                                      color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                },
                    
                                lineWidth: 1,
                                opposite: true,
                                showLastLabel: true,
                                tickAmount : 8,
                                labels: {
                                    y: 5,
                                    align: 'right',
                                    reserveSpace: true,
                                    style: {
                                      color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                }
                            }, {
                              	crosshair: {
                                    color: '#9d81ba88'
                                },
                                title: { text: 'Cumulative Southbound Flow $ (mil)' },
                        		lineWidth: 1,
                        		tickAmount : 8,
                        		opposite: false,

                        		plotLines: [{
                                    color: '#dadada', // Color value
                                    //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                                    value: 0, // Value of where the line will appear
                                    width: 4 // Width of the line    
                                }]
                            }],

                        	credits: {
                                enabled: true,
                                text: '360MiQ.com',
                                href: '',
                                style: {
                                    fontSize: '11px',
                                    cursor: 'arrow'
                                },
                                position: {
                                    align: 'left',
                                    x: 85,
                                    y: creditY - 50
                                }
                            },
                        	labels: {
                    			align: 'right',
                            		step: 30,
                    			x: 0,
                    			y: 0
                			},
                        	tooltip: {
                				shared: true,
                				split: false,
                				enabled: true,
                				formatter: function (tooltip) {
                    				return this.points.reduce(function (s, point) {
                    				    var mil = ' ';
                    				    var dollar = '';
                    				    if (point.series.index > 0)
                    				    {
                    				        mil = ' mil';
                    				        dollar = '$';
                    				    }
                    				    if (point.y > 0)
                                            return s + '<br><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>+' + dollar + point.y + mil + '</b>';
                                        else if (point.y < 0)
                                            return s + '<br><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">-' + dollar + Math.abs(point.y) + mil + '</span>';
                                        else
                                            return s + '<br><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>\u00B1' + dollar + point.y + mil + '</b>';
                                    }, 'Day Of Year: <b>' + this.x + '<b>');
                				},
                			},
                			legend: {
                                enabled: true,
                                borderWidth: 0,
                                layout: 'horizontal',
                                labelFormatter: function() {
                                    return '<span style="color:' + this.color + '">' + this.name + '</span>';
                                }
                            },
                        	series: [
                        	    {
                            		name: benchmark_title,
                            		color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get(),
                            		lineWidth: 1,
                            		shadow: true,
                            		data: dict['2800.HK_%'],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 0,
                                    dashStyle: 'Dash',
                        		},
                			    {
                            		name: seriesname[0],
                            		color: '#00BAFF',
                            		//lineWidth: 2,
                            		data: dict[seriesname[0]],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 1,
                        		},
                			    {
                            		name: seriesname[1],
                            		color: '#F88888',
                            		//lineWidth: 2,
                            		data: dict[seriesname[1]],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 1,
                        		},
                                	{
                            		name: seriesname[2],
                            		color: '#F9C82A',
                            		//lineWidth: 2,
                            		data: dict[seriesname[2]],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 1,
                        		},
                                	{
                            		name: seriesname[3],
                            		color: '#03ACA8',
                            		//lineWidth: 2,
                            		data: dict[seriesname[3]],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 1,
                        		},
                            	{
                            		name: seriesname[4],
                            		color: '#F3088D',
                            		lineWidth: 3,
                            		data: dict[seriesname[4]],
                            		marker: {
                                        enabled: false,
                                    },
                                    yAxis: 1,
                        		}
                    		]
                		});
                		
                		
                		let netfund0 = dict[seriesname[4]][dict[seriesname[4]].length - 1][1];
                		
                		const dayyear = dict[seriesname[4]][dict[seriesname[4]].length - 1][0];

                        let netfund1 = 0;
                        for (let i = 0; dict[seriesname[3]][i][0] <= dayyear && i < dict[seriesname[3]].length; i++) {
                            netfund1 = dict[seriesname[3]][i][1];
                        }

                		document.getElementById("yearlyCCASSComment").innerHTML = CCASSyearlyComment(seriesname[4], netfund0, seriesname[3], netfund1);
                		
                		/*yAxis0Extremes = chart.yAxis[0].getExtremes();
                        yAxisMaxMinRatio = yAxis0Extremes.max / yAxis0Extremes.min;
                        yAxis1Extremes = chart.yAxis[1].getExtremes();
                        yAxis1Min = (yAxis1Extremes.max / yAxisMaxMinRatio).toFixed(0);
                        chart.yAxis[1].setExtremes(yAxis1Min, yAxis1Extremes.max);*/
                    }
                });
            }
            
            
            
            else if (chartcontainer == "chartcontainerG1")
            {
                var price = new Object();
                var rent = new Object();
                var rent_price_ratio = new Object();
                var r_c_v_t = new Object();

                $.ajax({
                    url : "db_get_HKHousing.php",
                    success : function(result){
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            var row = jsonObject[i].split(',')

                    	    if (row.length == 4)  //completions_stock_vacancy_takeup
                    	    { 
                    	        if (r_c_v_t['Completion_column']) {
                    	            if (!isNaN(parseFloat(row[1])))
                                        r_c_v_t['Completion_column'].push([new Date(row[0]+'-12-31').getTime(), parseFloat(row[1])]);
                                } else if (!isNaN(parseFloat(row[1]))) {
                                    r_c_v_t['Completion_column'] = [[new Date(row[0]+'-12-31').getTime(), parseFloat(row[1])]];
                                }
                                
                                if (r_c_v_t['Vacancy %_column_newY']) {
                    	            if (!isNaN(parseFloat(row[2])))
                                        r_c_v_t['Vacancy %_column_newY'].push([new Date(row[0]+'-12-31').getTime(), parseFloat(row[2])]);
                                } else if (!isNaN(parseFloat(row[2]))) {
                                    r_c_v_t['Vacancy %_column_newY'] = [[new Date(row[0]+'-12-31').getTime(), parseFloat(row[2])]];
                                }
                                
                                if (r_c_v_t['Takeup_column']) {
                    	            if (!isNaN(parseFloat(row[3])))
                                        r_c_v_t['Takeup_column'].push([new Date(row[0]+'-12-31').getTime(), parseFloat(row[3])]);
                                } else if (!isNaN(parseFloat(row[2]))) {
                                    r_c_v_t['Takeup_column'] = [[new Date(row[0]+'-12-31').getTime(), parseFloat(row[3])]];
                                }
                    	    }
                    	    else if (row.length == 19)
                    	    { 
                                if (price['A']) {
                                    if (!isNaN(parseFloat(row[1])))
                                        price['A'].push([new Date(row[0]).getTime(), parseFloat(row[1])]);
                                } else if (!isNaN(parseFloat(row[1]))) {
                                    price['A'] = [[new Date(row[0]).getTime(), parseFloat(row[1])]];
                                }
                                
                                if (price['B']) {
                                    if (!isNaN(parseFloat(row[2])))
                                        price['B'].push([new Date(row[0]).getTime(), parseFloat(row[2])]);
                                } else if (!isNaN(parseFloat(row[2]))) {
                                    price['B'] = [[new Date(row[0]).getTime(), parseFloat(row[2])]];
                                }
                                
                                if (price['C']) {
                                    if (!isNaN(parseFloat(row[3])))
                                        price['C'].push([new Date(row[0]).getTime(), parseFloat(row[3])]);
                                } else if (!isNaN(parseFloat(row[3]))) {
                                    price['C'] = [[new Date(row[0]).getTime(), parseFloat(row[3])]];
                                }
                                
                                if (price['D']) {
                                    if (!isNaN(parseFloat(row[4])))
                                        price['D'].push([new Date(row[0]).getTime(), parseFloat(row[4])]);
                                } else if (!isNaN(parseFloat(row[4]))) {
                                    price['D'] = [[new Date(row[0]).getTime(), parseFloat(row[4])]];
                                }
                                
                                if (price['E']) {
                                    if (!isNaN(parseFloat(row[5])))
                                        price['E'].push([new Date(row[0]).getTime(), parseFloat(row[5])]);
                                } else if (!isNaN(parseFloat(row[5]))) {
                                    price['E'] = [[new Date(row[0]).getTime(), parseFloat(row[5])]];
                                }
                                
                                if (price['Residential']) {
                                    if (!isNaN(parseFloat(row[6])))
                                        price['Residential'].push([new Date(row[0]).getTime(), parseFloat(row[6])]);
                                } else if (!isNaN(parseFloat(row[6]))) {
                                    price['Residential'] = [[new Date(row[0]).getTime(), parseFloat(row[6])]];
                                }

                                if (i == jsonObject.length - 2)
                                {
                                    if (r_c_v_t['Residential']) {
                                        r_c_v_t['Residential'].push([price['Residential'][price['Residential'].length - 1][0], price['Residential'][price['Residential'].length - 1][1]]);
                                    } else {
                                        r_c_v_t['Residential'] = [[price['Residential'][price['Residential'].length - 1][0], price['Residential'][price['Residential'].length - 1][1]]];
                                    }
                                }
                                else if (price['Residential'].length > 1 && new Date(price['Residential'][price['Residential'].length - 1][0]).getFullYear() > new Date(price['Residential'][price['Residential'].length - 2][0]).getFullYear())
                                {
                                    if (r_c_v_t['Residential']) {
                                        r_c_v_t['Residential'].push([new Date(new Date(price['Residential'][price['Residential'].length - 2][0]).getFullYear()+'-12-31').getTime(), price['Residential'][price['Residential'].length - 2][1]]);
                                    } else {
                                        r_c_v_t['Residential'] = [[new Date(new Date(price['Residential'][price['Residential'].length - 2][0]).getFullYear()+'-12-31').getTime(), price['Residential'][price['Residential'].length - 2][1]]];
                                    }
                                }

                                if (price['Office']) {
                                    if (!isNaN(parseFloat(row[7])))
                                        price['Office'].push([new Date(row[0]).getTime(), parseFloat(row[7])]);
                                } else if (!isNaN(parseFloat(row[7]))) {
                                    price['Office'] = [[new Date(row[0]).getTime(), parseFloat(row[7])]];
                                }
                                
                                if (price['Retail']) {
                                    if (!isNaN(parseFloat(row[8])))
                                        price['Retail'].push([new Date(row[0]).getTime(), parseFloat(row[8])]);
                                } else if (!isNaN(parseFloat(row[8]))) {
                                    price['Retail'] = [[new Date(row[0]).getTime(), parseFloat(row[8])]];
                                }
                                
                                if (price['Flatted Factory']) {
                                    if (!isNaN(parseFloat(row[9])))
                                        price['Flatted Factory'].push([new Date(row[0]).getTime(), parseFloat(row[9])]);
                                } else if (!isNaN(parseFloat(row[9]))){
                                    price['Flatted Factory'] = [[new Date(row[0]).getTime(), parseFloat(row[9])]];
                                }
                                
                                
                                if (rent['Residential']) {
                                    if (!isNaN(parseFloat(row[10])))
                                        rent['Residential'].push([new Date(row[0]).getTime(), parseFloat(row[10])]);
                                } else if (!isNaN(parseFloat(row[10]))) {
                                    rent['Residential'] = [[new Date(row[0]).getTime(), parseFloat(row[10])]];
                                }
                                
                                if (rent['Office']) {
                                    if (!isNaN(parseFloat(row[11])))
                                        rent['Office'].push([new Date(row[0]).getTime(), parseFloat(row[11])]);
                                } else if (!isNaN(parseFloat(row[11]))) {
                                    rent['Office'] = [[new Date(row[0]).getTime(), parseFloat(row[11])]];
                                }
                                
                                if (rent['Retail']) {
                                    if (!isNaN(parseFloat(row[12])))
                                        rent['Retail'].push([new Date(row[0]).getTime(), parseFloat(row[12])]);
                                } else if (!isNaN(parseFloat(row[12]))) {
                                    rent['Retail'] = [[new Date(row[0]).getTime(), parseFloat(row[12])]];
                                }
                                
                                if (rent['Flatted Factory']) {
                                    if (!isNaN(parseFloat(row[13])))
                                        rent['Flatted Factory'].push([new Date(row[0]).getTime(), parseFloat(row[13])]);
                                } else if (!isNaN(parseFloat(row[13]))) {
                                    rent['Flatted Factory'] = [[new Date(row[0]).getTime(), parseFloat(row[13])]];
                                }
                                
                                
                                if (rent_price_ratio['Residential']) {
                                    if (!isNaN(parseFloat(row[10])) && !isNaN(parseFloat(row[6])))
                                        rent_price_ratio['Residential'].push([new Date(row[0]).getTime(), rounding(parseFloat(row[10])/parseFloat(row[6]), 100)]);
                                } else if (!isNaN(parseFloat(row[10])) && !isNaN(parseFloat(row[6]))) {
                                    rent_price_ratio['Residential'] = [[new Date(row[0]).getTime(), rounding(parseFloat(row[10])/parseFloat(row[6]), 100)]];
                                }
                                
                                if (rent_price_ratio['Office']) {
                                    if (!isNaN(parseFloat(row[11])) && !isNaN(parseFloat(row[7])))
                                    rent_price_ratio['Office'].push([new Date(row[0]).getTime(), rounding(parseFloat(row[11])/parseFloat(row[7]), 100)]);
                                } else if (!isNaN(parseFloat(row[11])) && !isNaN(parseFloat(row[7]))) {
                                    rent_price_ratio['Office'] = [[new Date(row[0]).getTime(), rounding(parseFloat(row[11])/parseFloat(row[7]), 100)]];
                                }
                                
                                if (rent_price_ratio['Retail']) {
                                    if (!isNaN(parseFloat(row[12])) && !isNaN(parseFloat(row[8])))
                                        rent_price_ratio['Retail'].push([new Date(row[0]).getTime(), rounding(parseFloat(row[12])/parseFloat(row[8]), 100)]);
                                } else if (!isNaN(parseFloat(row[12])) && !isNaN(parseFloat(row[8]))) {
                                    rent_price_ratio['Retail'] = [[new Date(row[0]).getTime(), rounding(parseFloat(row[12])/parseFloat(row[8]), 100)]];
                                }
                                
                                if (rent_price_ratio['Flatted Factory']) {
                                    if (!isNaN(parseFloat(row[13])) && !isNaN(parseFloat(row[9])))
                                        rent_price_ratio['Flatted Factory'].push([new Date(row[0]).getTime(), rounding(parseFloat(row[13])/parseFloat(row[9]), 100)]);
                                } else if (!isNaN(parseFloat(row[13])) && !isNaN(parseFloat(row[9]))) {
                                    rent_price_ratio['Flatted Factory'] = [[new Date(row[0]).getTime(), rounding(parseFloat(row[13])/parseFloat(row[9]), 100)]];
                                }
                                
                                var sale_new = 0;
                                if (price['# of Sale New_stacked']) {
                                    if (!isNaN(parseFloat(row[14])))
                                    {
                                        price['# of Sale New_stacked'].push([new Date(row[0]).getTime(), parseFloat(row[14])]);
                                        sale_new = parseFloat(row[14]);
                                    }
                                } else if (!isNaN(parseFloat(row[14]))) {
                                    price['# of Sale New_stacked'] = [[new Date(row[0]).getTime(), parseFloat(row[14])]];
                                    sale_new = parseFloat(row[14]);
                                }
                                
                                var sale_used = 0;
                                if (price['# of Sale Used_stacked']) {
                                    if (!isNaN(parseFloat(row[15])))
                                    {
                                        price['# of Sale Used_stacked'].push([new Date(row[0]).getTime(), parseFloat(row[15])]);
                                        sale_used = parseFloat(row[15]);
                                    }
                                } else if (!isNaN(parseFloat(row[15]))) {
                                    price['# of Sale Used_stacked'] = [[new Date(row[0]).getTime(), parseFloat(row[15])]];
                                    sale_used = parseFloat(row[15]);
                                }
                                
                                if (price['# of Sale Residential_column']) {
                                    price['# of Sale Residential_column'].push([new Date(row[0]).getTime(), sale_new + sale_used]);
                                } else {
                                    price['# of Sale Residential_column'] = [[new Date(row[0]).getTime(), sale_new + sale_used]];
                                }
                                
                                if (price['# of Sale Office_column']) {
                                    if (!isNaN(parseFloat(row[16])))
                                        price['# of Sale Office_column'].push([new Date(row[0]).getTime(), parseFloat(row[16])]);
                                } else if (!isNaN(parseFloat(row[16]))) {
                                    price['# of Sale Office_column'] = [[new Date(row[0]).getTime(), parseFloat(row[16])]];
                                }
                                
                                if (price['# of Sale Commercial_column']) {
                                    if (!isNaN(parseFloat(row[17])))
                                        price['# of Sale Commercial_column'].push([new Date(row[0]).getTime(), parseFloat(row[17])]);
                                } else if (!isNaN(parseFloat(row[17]))) {
                                    price['# of Sale Commercial_column'] = [[new Date(row[0]).getTime(), parseFloat(row[17])]];
                                }
                                
                                if (price['# of Sale Flatted Factory_column']) {
                                    if (!isNaN(parseFloat(row[18])))
                                        price['# of Sale Flatted Factory_column'].push([new Date(row[0]).getTime(), parseFloat(row[18])]);
                                } else if (!isNaN(parseFloat(row[18]))) {
                                    price['# of Sale Flatted Factory_column'] = [[new Date(row[0]).getTime(), parseFloat(row[18])]];
                                }
                    	    }
                        }
                        
                        // reassign the last data point timestamp to match price index
                        if (r_c_v_t['Residential'])
                        {
                            if (r_c_v_t['Completion_column'])
                                if (r_c_v_t['Completion_column'][r_c_v_t['Completion_column'].length - 1][0] > r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0])
                                    r_c_v_t['Completion_column'][r_c_v_t['Completion_column'].length - 1][0] = r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0];

                            if (r_c_v_t['Vacancy %_column_newY'])
                                if (r_c_v_t['Vacancy %_column_newY'][r_c_v_t['Vacancy %_column_newY'].length - 1][0] > r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0])
                                    r_c_v_t['Vacancy %_column_newY'][r_c_v_t['Vacancy %_column_newY'].length - 1][0] = r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0];
                                    
                            if (r_c_v_t['Takeup_column'])
                                if (r_c_v_t['Takeup_column'][r_c_v_t['Takeup_column'].length - 1][0] > r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0])
                                    r_c_v_t['Takeup_column'][r_c_v_t['Takeup_column'].length - 1][0] = r_c_v_t['Residential'][r_c_v_t['Residential'].length - 1][0];
                        }
                        
                        /*for (var i = price['A'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['A'][i][1]))
                                price['A'].splice(i, 1);
                        }
                        
                        for (var i = price['B'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['B'][i][1]))
                                price['B'].splice(i, 1);
                        }
                        
                        for (var i = price['C'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['C'][i][1]))
                                price['C'].splice(i, 1);
                        }
                        
                        for (var i = price['D'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['D'][i][1]))
                                price['D'].splice(i, 1);
                        }
                        
                        for (var i = price['E'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['E'][i][1]))
                                price['E'].splice(i, 1);
                        }
                        
                        
                        for (var i = price['Residential'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['Residential'][i][1]))
                                price['Residential'].splice(i, 1);
                        }
                        
                        for (var i = price['Office'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['Office'][i][1]))
                                price['Office'].splice(i, 1);
                        }
                        
                        for (var i = price['Retail'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['Retail'][i][1]))
                                price['Retail'].splice(i, 1);
                        }
                        
                        for (var i = price['Flatted Factory'].length - 1; i >= 0; i--)
                        {
                            if(isNaN(price['Flatted Factory'][i][1]))
                                price['Flatted Factory'].splice(i, 1);
                        }

                            
                        if (isNaN(rent['Residential'][rent['Residential'].length - 1][1]))
                            rent['Residential'] = rent['Residential'].slice(0, -1);
                        
                        if (isNaN(rent['Office'][rent['Office'].length - 1][1]))
                            rent['Office'] = rent['Office'].slice(0, -1);

                        if (isNaN(rent['Retail'][rent['Retail'].length - 1][1]))
                            rent['Retail'] = rent['Retail'].slice(0, -1);
                        
                        if (isNaN(rent['Flatted Factory'][rent['Flatted Factory'].length - 1][1]))
                            rent['Flatted Factory'] = rent['Flatted Factory'].slice(0, -1);
                            
                            
                        if (isNaN(rent_price_ratio['Residential'][rent_price_ratio['Residential'].length - 1][1]))
                            rent_price_ratio['Residential'] = rent_price_ratio['Residential'].slice(0, -1);
                        
                        if (isNaN(rent_price_ratio['Office'][rent_price_ratio['Office'].length - 1][1]))
                            rent_price_ratio['Office'] = rent_price_ratio['Office'].slice(0, -1);

                        if (isNaN(rent_price_ratio['Retail'][rent_price_ratio['Retail'].length - 1][1]))
                            rent_price_ratio['Retail'] = rent_price_ratio['Retail'].slice(0, -1);
                        
                        if (isNaN(rent_price_ratio['Flatted Factory'][rent_price_ratio['Flatted Factory'].length - 1][1]))
                            rent_price_ratio['Flatted Factory'] = rent_price_ratio['Flatted Factory'].slice(0, -1);
                            
                        if (isNaN(price['# of Sale New_stacked'][price['# of Sale New_stacked'].length - 1][1]))
                            price['# of Sale New_stacked'] = price['# of Sale New_stacked'].slice(0, -1);

                        if (isNaN(price['# of Sale Used_stacked'][price['# of Sale Used_stacked'].length - 1][1]))
                            price['# of Sale Used_stacked'] = price['# of Sale Used_stacked'].slice(0, -1);
                            
                        if (isNaN(price['# of Sale Office_column'][price['# of Sale Office_column'].length - 1][1]))
                            price['# of Sale Office_column'] = price['# of Sale Office_column'].slice(0, -1);
                            
                        if (isNaN(price['# of Sale Commercial_column'][price['# of Sale Commercial_column'].length - 1][1]))
                            price['# of Sale Commercial_column'] = price['# of Sale Commercial_column'].slice(0, -1);
                            
                        if (isNaN(price['# of Sale Flatted Factory_column'][price['# of Sale Flatted Factory_column'].length - 1][1]))
                            price['# of Sale Flatted Factory_column'] = price['# of Sale Flatted Factory_column'].slice(0, -1);*/
                            
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var creditY_extra = 3;
                        if (browserwidth <= 576)
                            creditY_extra = 20;

            		    highstock('chartcontainerG1', price, ['Residential', 'A', 'B', 'C', 'D', 'E', '# of Sale Used_stacked', '# of Sale New_stacked'], "Hong Kong Residential Price Indices by Class", HKpropertySubtitle, "Index Value", "# of Sale", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true, false, 'close', Infinity, true, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);

                        renderedChartIds.add('chartcontainerG1');

                        lazyRenderChart('chartcontainerG2', function() {
                            highstock('chartcontainerG2', price, ['Residential', 'Office', 'Retail', 'Flatted Factory', '# of Sale Residential_column', '# of Sale Office_column', '# of Sale Commercial_column', '# of Sale Flatted Factory_column'], "Hong Kong Property Price Indices by Type", HKpropertySubtitle, "Index Value", "# of Sale", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true, false, 'close', Infinity, true, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        });

                        lazyRenderChart('chartcontainerG3', function() {
                            highstock('chartcontainerG3', rent, ['Residential', 'Office', 'Retail', 'Flatted Factory'], "Hong Kong Property Rental Indices by Type", HKpropertySubtitle, "Index Value", "", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true, false, 'close', Infinity, true, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        });

                        lazyRenderChart('chartcontainerG4', function() {
                            highstock('chartcontainerG4', rent_price_ratio, ['Residential', 'Office', 'Retail', 'Flatted Factory'], "Hong Kong Property Rent / Price Ratio by Type", HKpropertySubtitle, "Rent / Price Ratio", "", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true, false, 'close', Infinity, true, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        });

                        lazyRenderChart('chartcontainerG5', function() {
                            highstock('chartcontainerG5', r_c_v_t, ['Residential', 'Completion_column', 'Takeup_column', 'Vacancy %_column_newY'], "Hong Kong Residential Price, Completion, Takeup, Vacancy", HKpropertySubtitle, "Index Value", "# of Unit", null, null, "month", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
                        });
                    }
                });
            }
            
            else if (chartcontainer == "chartcontainerH1")
            {
                var dict = new Object();

                $.ajax({
                    url : "db_SurpriseIndex_get.php?data=" + country,
                    success : function(result){
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            var row = jsonObject[i].split(',')

                    	    if (row.length == 3)
                    	    { 
                                if (row[0] == "U" || row[0] == "C")
                    	        {
                                    if (dict[country + ' Econ Surprise Index']) {
                                         dict[country + ' Econ Surprise Index'].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]);
                                    } else {
                                        dict[country + ' Econ Surprise Index'] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]];
                                    }
                    	        }
                    	        else if (row[0] == "S" || row[0] == "1")
                    	        {
                                    if (dict[indexname]) {
                                         dict[indexname].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]);
                                    } else {
                                        dict[indexname] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]];
                                    }
                    	        }
                    	    }
                        }
                        
                        for(var i = 12; i < dict[indexname].length; i++)
            	        {
            	            if (dict[indexname + ' QoQ %']) {
                                 dict[indexname + ' QoQ %'].push([dict[indexname][i][0], rounding((dict[indexname][i][1]/dict[indexname][i-12][1] - 1) * 100, 10)]);
                            } else {
                                dict[indexname + ' QoQ %'] = [[dict[indexname][i][0], rounding((dict[indexname][i][1]/dict[indexname][i-12][1] - 1) * 100, 10)]];
                            }
            	        }
            	        
            	        if (dict[indexname][0][0] < dict[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dict[indexname][0][0] < dict[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dict[indexname].shift();
                	        }
            	        }
            	        else if (dict[indexname][0][0] > dict[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dict[indexname][0][0] > dict[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dict[country + ' Econ Surprise Index'].shift();
                	        }
            	        }
            	        
            	        if (dict[indexname + ' QoQ %'][0][0] < dict[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dict[indexname + ' QoQ %'][0][0] < dict[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dict[indexname + ' QoQ %'].shift();
                	        }
            	        }   
                	    else if (dict[indexname + ' QoQ %'][0][0] > dict[country + ' Econ Surprise Index'][0][0])
                	    {
                	        while(dict[indexname + ' QoQ %'][0][0] > dict[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dict[country + ' Econ Surprise Index'].shift();
                	        }
                	    }

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var creditY_extra = 3;
                        var rangeSelected = 6;
                        if (browserwidth <= 576)
                        {
                            creditY_extra = 20;
                            rangeSelected = 5;
                        }

            		    //highstock('chartcontainerH1', dict, ['SP500 QoQ Return %', 'US Econ Surprise Index'], "US Economic Surprise Index vs SP500", "", "Index Value", "Surpise Value", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
            		    
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
                            
        		        Highcharts.stockChart('chartcontainerH1', {

                            rangeSelector: {
                                selected: rangeSelected,
                                buttons: [{
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
                                    count: 8,
                                    text: '8y'
                                }, {
                                    type: 'all',
                                    text: 'All'
                                }]
                            },
                    
                            title: {
                                text: country + ' Econ Surprise vs ' + indexname + ' QoQ Performance',
                                style: {
                                    fontSize: '16px',
                                },
                            },
                            
                            credits: {
                                enabled: true,
                                text: '360MiQ.com',
                                href: '',
                                style: {
                                    fontSize: '11px',
                                    cursor: 'arrow'
                                },
                                position: {
                                    align: 'left',
                                    x: 50,
                                    y: -686 + creditY_extra
                                }
                            },
        
                        	chart: {
                                style: {
                                    fontFamily: 'Montserrat'
                                },
                            },
                            
                            plotOptions: {
                                series: {
                                    states: {
                                        inactive: {
                                            opacity: 1
                                        }
                                    },
                                    dataGrouping: {
                                        enabled: false
                                    },
                                }
                            },
    
                            subtitle: {
                                text: ''
                            },
                    
                            yAxis: [{
                                crosshair: true,
                                endOnTick: false,
                                startOnTick: false,
                                showLastLabel: true,
                                tickPositioner: function () {
                                    const positions = [Math.round((Math.round(this.dataMax) + Math.round(this.dataMin))/200) * 100, Math.round(this.dataMax/100)*100, Math.round(this.dataMin/100)*100];
                                    return positions;
                                },
                                labels: {
                                    y: 5,
                                    align: 'left',
                                    reserveSpace: true,
                                    style: {
                                      color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                },
                                title: {
                                    text: indexname,
                                    style: {
                                        color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                },
                                height: '32%',
                                lineWidth: 2,
                                resize: {
                                    enabled: true
                                },
                            }, {
                                crosshair: true,
                                endOnTick: false,
                                startOnTick: false,
                                showLastLabel: true,
                                tickPositioner: function () {
                                    const positions = [0, Math.round(this.dataMax/5)*5, Math.round(this.dataMin/5)*5];
                                    return positions;
                                },
                                labels: {
                                    y: 5,
                                    align: 'left',
                                    reserveSpace: true,
                                },
                                title: {
                                    text: indexname + ' QoQ %',
                                    style: {
                                        color: new Highcharts.Color(Highcharts.getOptions().colors[1]).setOpacity(1).get()
                                    }
                                },
                                top: '33%',
                                height: '32%',
                                offset: 0,
                                lineWidth: 2,
                                plotLines: [{
                                    color: '#eaeaea', // Color value
                                    value: 0, // Value of where the line will appear
                                    width: 4 // Width of the line    
                                }]
                            }, {
                                crosshair: true,
                                endOnTick: false,
                                startOnTick: false,
                                showLastLabel: true,
                                tickPositioner: function () {
                                    const positions = [0, Math.round(this.dataMax/5)*5, Math.round(this.dataMin/5)*5];
                                    return positions;
                                },
                                labels: {
                                    y: 5,
                                    align: 'left',
                                    reserveSpace: true,
                                    style: {
                                      color: 'green'
                                    }
                                },
                                title: {
                                    text: country + ' Econ Surpise Index',
                                    style: {
                                        color: 'green'
                                    }
                                },
                                top: '66.3%',
                                height: '32%',
                                offset: 0,
                                lineWidth: 2,
                                plotLines: [{
                                    color: '#eaeaea', // Color value
                                    value: 0, // Value of where the line will appear
                                    width: 4 // Width of the line    
                                }],
                            }],
                    
                            tooltip: {
                                enabled: true,
                                shared: true,
                                split: false,
                                useHTML: true,
                                xDateFormat: '%A, %b %e, %Y',
                                formatter: function (tooltip) {
                                    return '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>';
                                }
                            },
                    
                            legend: {
                                enabled: true,
                                labelFormatter: function() {
                                    return '<span style="color:' + this.color + '">' + this.name + '</span>';
                                }
                            },
                            
                            navigator: {
                                yAxis :{
                                    type: 'logarithmic',
                                },
                                outlineWidth: 1,
                                maskFill: (document.documentElement.getAttribute('data-theme')==='dark'?'rgba(0,0,0,0.35)':'rgba(0, 0, 0, 0.15)'),
                                handles: {
                                    backgroundColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#2a2a3e':'#eee'),
                                    borderColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#555':'#777'),
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
                                type: 'line',
                                name: indexname,
                                id: 'index',
                                zIndex: 2,
                                data: dict[indexname],
                                lineWidth: 3,
                                shadow: true
                            }, {
                                type: 'line',
                                name: indexname + ' QoQ %',
                                id: 'QoQ',
                                zIndex: 2,
                                data: dict[indexname + ' QoQ %'],
                                yAxis: 1,
                                //lineWidth: 3
                            }, {
                                type: 'line',
                                name: country + ' Econ Surprise Index',
                                id: 'surprise',
                                data: dict[country + ' Econ Surprise Index'],
                                yAxis: 2,
                                color: 'green',
                                //lineWidth: 3,
                                //shadow: true
                            }]
                        });

                    }
                });
                
                
                if (country != 'US')
                    return;
                
                document.getElementById("fear_greed").style.display = "";
                
                var dict1 = new Object();

                $.ajax({
                    url : "db_SurpriseIndex_get.php?data=US_Fear",
                    success : function(result){
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            var row = jsonObject[i].split(',')

                    	    if (row.length == 3)
                    	    { 
                                if (row[0] == "F")
                    	        {
                                    if (dict1[country + ' Fear and Greed Index']) {
                                         dict1[country + ' Fear and Greed Index'].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]);
                                    } else {
                                        dict1[country + ' Fear and Greed Index'] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]];
                                    }
                    	        }
                    	        else if (row[0] == "S")
                    	        {
                                    if (dict1[indexname]) {
                                         dict1[indexname].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]);
                                    } else {
                                        dict1[indexname] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]];
                                    }
                    	        }
                    	    }
                        }
                        
            	        if (dict1[indexname][0][0] < dict1[country + ' Fear and Greed Index'][0][0])
            	        {
                	        while(dict1[indexname][0][0] < dict1[country + ' Fear and Greed Index'][0][0])
                	        {
                	            dict1[indexname].shift();
                	        }
            	        }
            	        else if (dict1[indexname][0][0] > dict1[country + ' Fear and Greed Index'][0][0])
            	        {
                	        while(dict1[indexname][0][0] > dict1[country + ' Fear and Greed Index'][0][0])
                	        {
                	            dict1[country + ' Econ Surprise Index'].shift();
                	        }
            	        }
            	        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var creditY_extra = 3;
                        var rangeSelected = 6;
                        if (browserwidth <= 576)
                        {
                            creditY_extra = 20;
                            rangeSelected = 5;
                        }

            		    //highstock('chartcontainerH1', dict, ['SP500 QoQ Return %', 'US Econ Surprise Index'], "US Economic Surprise Index vs SP500", "", "Index Value", "Surpise Value", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
            		    
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
                            
        		        Highcharts.stockChart('chartcontainerH2', {

                            rangeSelector: {
                                selected: rangeSelected,
                                buttons: [{
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
                                    count: 8,
                                    text: '8y'
                                }, {
                                    type: 'all',
                                    text: 'All'
                                }]
                            },
                    
                            title: {
                                text: country + ' Stock Fear and Greed',
                                style: {
                                    fontSize: '16px',
                                },
                            },
                            
                            credits: {
                                enabled: true,
                                text: '360MiQ.com',
                                href: '',
                                style: {
                                    fontSize: '11px',
                                    cursor: 'arrow'
                                },
                                position: {
                                    align: 'left',
                                    x: 50,
                                    y: -586 + creditY_extra
                                }
                            },
        
                        	chart: {
                                style: {
                                    fontFamily: 'Montserrat'
                                },
                            },
                            
                            plotOptions: {
                                series: {
                                    states: {
                                        inactive: {
                                            opacity: 1
                                        }
                                    },
                                    dataGrouping: {
                                        enabled: false
                                    },
                                }
                            },
    
                            subtitle: {
                                text: ''
                            },
                    
                            yAxis: [{
                                crosshair: true,
                                endOnTick: false,
                                startOnTick: false,
                                showLastLabel: true,
                                tickPositioner: function () {
                                    const positions = [Math.round((Math.round(this.dataMax) + Math.round(this.dataMin))/200) * 100, Math.round(this.dataMax/100)*100, Math.round(this.dataMin/100)*100];
                                    return positions;
                                },
                                labels: {
                                    y: 5,
                                    align: 'left',
                                    reserveSpace: true,
                                    style: {
                                      color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                },
                                title: {
                                    text: indexname,
                                    style: {
                                        color: new Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(1).get()
                                    }
                                },
                                height: '49.2%',
                                lineWidth: 2,
                                resize: {
                                    enabled: true
                                },
                            }, {
                                min: 0, max: 10,
                                plotBands: [
                                    { from: 0, to: 2.5, color: 'rgb(255,0,0,0.2)', label: { text: 'Extreme Fear', align: 'left', style: {fontWeight: 'bold', color: 'grey' } } },
                                    { from: 2.5, to: 4.5, color: 'rgb(255,165,0,0.2)', label: { text: 'Fear', align: 'left', style: {fontWeight: 'bold', color: 'grey' } } },
                                    { from: 4.5, to: 5.5, color: 'rgb(255,255,0,0.2)', label: { text: 'Neutral', align: 'left', style: {fontWeight: 'bold', color: 'grey' } } },
                                    { from: 5.5, to: 7.5, color: 'rgb(0,128,0,0.2)', label: { text: 'Greed', align: 'left', style: {fontWeight: 'bold', color: 'grey' } } },
                                    { from: 7.5, to: 10, color: 'rgb(0,0,255,0.2)', label: { text: 'Extreme Greed', align: 'left', style: {fontWeight: 'bold', color: 'grey' } } }
                                ],
                                crosshair: true,
                                endOnTick: false,
                                startOnTick: false,
                                showLastLabel: true,
                                tickPositioner: function () {
                                    const positions = [0, Math.round(this.dataMax/5)*5, Math.round(this.dataMin/5)*5];
                                    return positions;
                                },
                                labels: {
                                    y: 5,
                                    align: 'left',
                                    reserveSpace: true,
                                    style: {
                                      color: 'green'
                                    }
                                },
                                title: {
                                    text: country + ' Fear and Greed Index',
                                    style: {
                                        color: 'green'
                                    }
                                },
                                top: '50.8%',
                                height: '49.2%',
                                offset: 0,
                                lineWidth: 2,
                                //plotLines: [{
                                //    color: '#eaeaea', // Color value
                                //    value: 0, // Value of where the line will appear
                                //    width: 4 // Width of the line    
                                //}],
                            }],
                    
                            tooltip: {
                                enabled: true,
                                shared: true,
                                split: false,
                                useHTML: true,
                                xDateFormat: '%A, %b %e, %Y',
                                formatter: function (tooltip) {
                                    return '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>';
                                }
                            },
                    
                            legend: {
                                enabled: true,
                                labelFormatter: function() {
                                    return '<span style="color:' + this.color + '">' + this.name + '</span>';
                                }
                            },
                            
                            navigator: {
                                yAxis :{
                                    type: 'logarithmic',
                                },
                                outlineWidth: 1,
                                maskFill: (document.documentElement.getAttribute('data-theme')==='dark'?'rgba(0,0,0,0.35)':'rgba(0, 0, 0, 0.15)'),
                                handles: {
                                    backgroundColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#2a2a3e':'#eee'),
                                    borderColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#555':'#777'),
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
                                type: 'line',
                                name: indexname,
                                id: 'index',
                                zIndex: 2,
                                data: dict1[indexname],
                                lineWidth: 3,
                                shadow: true
                            }, {
                                type: 'line',
                                name: country + ' Fear and Greed Index',
                                id: 'surprise',
                                data: dict1[country + ' Fear and Greed Index'],
                                yAxis: 1,
                                color: 'green',
                                //lineWidth: 3,
                                //shadow: true
                            }]
                        });

                    }
                });
            }
            
            
            else if (chartcontainer == "chartcontainerI1")
            {
                var isLoadedSuccessfully = false;

                var dict = new Object();

                $.ajax({
                    url : "db_index_get.php?data=1700",
                    success : function(result){
                        var indexname = 'Dow Jones Industrial';
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {

                            var row = jsonObject[i].split(',')

                    	    if (row.length == 2)
                    	    { 
                                row[1] /= 1;
                	            row[0] = row[0].substr(0,4) + "-" + row[0].substr(4,2) + "-" + row[0].substr(6,2);

                                if (dict[indexname]) {
                                     dict[indexname].push([new Date(row[0]).getTime(), row[1]]);
                                     
                                    if (dict['Monthly Return %'])
                                        dict['Monthly Return %'].push([new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]);
                                    else
                                        dict['Monthly Return %'] = [[new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]];
                                     
                                } else {
                                    dict[indexname] = [[new Date(row[0]).getTime(), row[1]]];
                                }
                    	    }
                        }
                        
                        dict[indexname + ' Monthly'] = dict[indexname];

                        dict[indexname + ' Yearly'] = dataCompression_yearly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Yearly'].length; i++)
                        {
                            if (dict['Yearly Return %'])
                                dict['Yearly Return %'].push([dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Yearly Return %'] = [[dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        delete dict[indexname];

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 4;
                        if (browserwidth >= 768)
                            rangeselector = 5;
                            
                        var yearsCount = new Date(dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1][0]).getYear() - new Date(dict[indexname + ' Monthly'][0][0]).getYear();
                        document.getElementById("uslength").innerHTML = yearsCount.toString().endsWith("0") ? yearsCount + ' ' : (Math.floor(yearsCount/10) * 10 + '+ ');
                        
                        highstock('chartcontainerI1', dict, [indexname + ' Monthly', "Monthly Return %"], indexname + " Index (Monthly Return %)", "Merging with pre-February 1885 UK data", indexname, "Monthly Return %", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, 'US', [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("monthlyReturnComment1700").innerHTML = returnComment(indexname, dict[indexname + ' Monthly'], dict['Monthly Return %'], "months");
                        
                        highstock('chartcontainerI2', dict, [indexname + ' Yearly', "Yearly Return %"], indexname + " Index (Yearly Return %)", "Merging with pre-February 1885 UK data", indexname, "Yearly Return %", null, null, "day", rangeselector + 1, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, 'US', [{type: 'year', count: 5, text: '5y'}, {type: 'year', count:10, text:'10y'}, {type: 'year', count: 25, text: '25y'}, {type: 'year', count: 50, text: '50y'}, {type: 'year', count: 100, text: '100y'}, {type: 'year', count: 150, text: '150y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("yearlyReturnComment1700").innerHTML = returnComment(indexname, dict[indexname + ' Yearly'], dict['Yearly Return %'], "years");
                        
                        // Must use loading on scrolling, otherwise, Ajax 40% times won't pass data to php
                        $(window).scroll(function() {
                            //var aaa = $(window).scrollTop();
                            //var bbb = $(document).height();
                            //var ccc =  $(window).height();
                        	if (!isLoadedSuccessfully && $(window).scrollTop() > ($(document).height() - $(window).height()) * 0.1) {
                                    seasonality('', dict, indexname, '#monthlytable1700', "#slider1700", 'monthlytabletitle1700', 'chartcontainerSeasonality1700', dict[indexname + ' Monthly'][0][1]);
                                    isLoadedSuccessfully = true;
                        		}
                        	}
                        );
                    }
                });
            }
            
            
            else if (chartcontainer == "chartcontainerJ1")
            {
                var isLoadedSuccessfully = false;

                var dict = new Object();

                $.ajax({
                    url : "db_index_get.php?data=1841",
                    success : function(result){
                        var indexname = 'Hang Seng';
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {

                            var row = jsonObject[i].split(',')

                    	    if (row.length == 2)
                    	    { 
                                row[1] /= 1;
                	            row[0] = row[0].substr(0,4) + "-" + row[0].substr(4,2) + "-" + row[0].substr(6,2);

                                if (dict[indexname]) {
                                     dict[indexname].push([new Date(row[0]).getTime(), row[1]]);
                                     
                                    if (dict['Monthly Return %'])
                                        dict['Monthly Return %'].push([new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]);
                                    else
                                        dict['Monthly Return %'] = [[new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]];
                                     
                                } else {
                                    dict[indexname] = [[new Date(row[0]).getTime(), row[1]]];
                                }
                    	    }
                        }
                        
                        dict[indexname + ' Monthly'] = dict[indexname];

                        dict[indexname + ' Yearly'] = dataCompression_yearly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Yearly'].length; i++)
                        {
                            if (dict['Yearly Return %'])
                                dict['Yearly Return %'].push([dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Yearly Return %'] = [[dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        delete dict[indexname];

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 4;
                        if (browserwidth >= 768)
                            rangeselector = 5;
                            
                        var yearsCount = new Date(dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1][0]).getYear() - new Date(dict[indexname + ' Monthly'][0][0]).getYear();
                        document.getElementById("hklength").innerHTML = yearsCount.toString().endsWith("0") ? yearsCount + ' ' : (Math.floor(yearsCount/10) * 10 + '+ ');
                        
                        highstock('chartcontainerJ1', dict, [indexname + ' Monthly', "Monthly Return %"], indexname + " Index (Monthly Return %)", "Merging with pre-July 1964 HK & UK data", indexname, "Monthly Return %", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false, [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("monthlyReturnComment1841").innerHTML = returnComment(indexname, dict[indexname + ' Monthly'], dict['Monthly Return %'], "months");
                        
                        highstock('chartcontainerJ2', dict, [indexname + ' Yearly', "Yearly Return %"], indexname + " Index (Yearly Return %)", "Merging with pre-July 1964 HK & UK data", indexname, "Yearly Return %", null, null, "day", rangeselector + 1, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, false, false, [{type: 'year', count: 5, text: '5y'}, {type: 'year', count:10, text:'10y'}, {type: 'year', count: 25, text: '25y'}, {type: 'year', count: 50, text: '50y'}, {type: 'year', count: 100, text: '100y'}, {type: 'year', count: 150, text: '150y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("yearlyReturnComment1841").innerHTML = returnComment(indexname, dict[indexname + ' Yearly'], dict['Yearly Return %'], "years");
                        
                        // Must use loading on scrolling, otherwise, Ajax 40% times won't pass data to php
                        $(window).scroll(function() {
                            //var aaa = $(window).scrollTop();
                            //var bbb = $(document).height();
                            //var ccc =  $(window).height();
                        	if (!isLoadedSuccessfully && $(window).scrollTop() > ($(document).height() - $(window).height()) * 0.1) {
                                    seasonality('', dict, indexname, '#monthlytable1841', "#slider1841", 'monthlytabletitle1841', 'chartcontainerSeasonality1841', dict[indexname + ' Monthly'][0][1]);
                                    isLoadedSuccessfully = true;
                        		}
                        	}
                        );
                    }
                });
            }
            
            else if (chartcontainer == "chartcontainerK1")
            {
                var isLoadedSuccessfully = false;

                var dict = new Object();

                $.ajax({
                    url : "db_index_get.php?data=1920",
                    success : function(result){
                        var indexname = 'Gold Price';
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {

                            var row = jsonObject[i].split(',')

                    	    if (row.length == 2)
                    	    { 
                                row[1] /= 1;
                	            row[0] = row[0].substr(0,4) + "-" + row[0].substr(4,2) + "-" + row[0].substr(6,2);

                                if (dict[indexname]) {
                                     dict[indexname].push([new Date(row[0]).getTime(), row[1]]);
                                     
                                    if (dict['Daily Return %'])
                                        dict['Daily Return %'].push([new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]);
                                    else
                                        dict['Daily Return %'] = [[new Date(row[0]).getTime(), rounding((row[1] / dict[indexname][dict[indexname].length - 2][1] - 1) * 100, 100) ]];
                                     
                                } else {
                                    dict[indexname] = [[new Date(row[0]).getTime(), row[1]]];
                                }
                    	    }
                        }
                        
                        dict[indexname + ' Daily'] = dict[indexname];

                        dict[indexname + ' Weekly'] = dataCompression_weekly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Weekly'].length; i++)
                        {
                            if (dict['Weekly Return %'])
                                dict['Weekly Return %'].push([dict[indexname + ' Weekly'][i][0], rounding((dict[indexname + ' Weekly'][i][1] / dict[indexname + ' Weekly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Weekly Return %'] = [[dict[indexname + ' Weekly'][i][0], rounding((dict[indexname + ' Weekly'][i][1] / dict[indexname + ' Weekly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        dict[indexname + ' Monthly'] = dataCompression_monthly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Monthly'].length; i++)
                        {
                            if (dict['Monthly Return %'])
                                dict['Monthly Return %'].push([dict[indexname + ' Monthly'][i][0], rounding((dict[indexname + ' Monthly'][i][1] / dict[indexname + ' Monthly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Monthly Return %'] = [[dict[indexname + ' Monthly'][i][0], rounding((dict[indexname + ' Monthly'][i][1] / dict[indexname + ' Monthly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        dict[indexname + ' Yearly'] = dataCompression_yearly(dict[indexname]);
                        for (i = 1; i < dict[indexname + ' Yearly'].length; i++)
                        {
                            if (dict['Yearly Return %'])
                                dict['Yearly Return %'].push([dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]);
                            else
                                dict['Yearly Return %'] = [[dict[indexname + ' Yearly'][i][0], rounding((dict[indexname + ' Yearly'][i][1] / dict[indexname + ' Yearly'][i - 1][1] - 1) * 100, 100) ]];
                        }
                        
                        delete dict[indexname];

                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 0;
                        if (browserwidth > 576)
                            rangeselector = 2;
                            
                        var yearsCount = new Date(dict[indexname + ' Daily'][dict[indexname + ' Daily'].length - 1][0]).getYear() - new Date(dict[indexname + ' Daily'][0][0]).getYear();
                        document.getElementById("goldlength").innerHTML = yearsCount.toString().endsWith("0") ? yearsCount + ' ' : (Math.floor(yearsCount/10) * 10 + '+ ');
                        
                        highstock('chartcontainerK1', dict, [indexname + ' Daily', "Daily Return %"], indexname + " (Daily Return %)", "", indexname, "Daily Return %", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'month', count: 6, text: '6m'}, {type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:2, text:'2y'}, {type: 'year', count: 3, text: '3y'}, {type: 'year', count: 8, text: '8y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("dailyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Daily'], dict['Daily Return %'], "days");
                        
                        highstock('chartcontainerK2', dict, [indexname + ' Weekly', "Weekly Return %"], indexname + " (Weekly Return %)", "", indexname, "Weekly Return %", null, null, "day", 4, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'month', count: 6, text: '6m'}, {type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:2, text:'2y'}, {type: 'year', count: 3, text: '3y'}, {type: 'year', count: 8, text: '8y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("weeklyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Weekly'], dict['Weekly Return %'], "weeks");
                        
                        highstock('chartcontainerK3', dict, [indexname + ' Monthly', "Monthly Return %"], indexname + " (Monthly Return %)", "", indexname, "Monthly Return %", null, null, "day", 5, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("monthlyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Monthly'], dict['Monthly Return %'], "months");
                        
                        highstock('chartcontainerK4', dict, [indexname + ' Yearly', "Yearly Return %"], indexname + " (Yearly Return %)", "", indexname, "Yearly Return %", null, null, "day", 6, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'year', count: 5, text: '5y'}, {type: 'year', count:10, text:'10y'}, {type: 'year', count: 25, text: '25y'}, {type: 'year', count: 50, text: '50y'}, {type: 'year', count: 100, text: '100y'}, {type: 'year', count: 150, text: '150y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("yearlyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Yearly'], dict['Yearly Return %'], "years");
                        
                        // Must use loading on scrolling, otherwise, Ajax 40% times won't pass data to php
                        $(window).scroll(function() {
                            //var aaa = $(window).scrollTop();
                            //var bbb = $(document).height();
                            //var ccc =  $(window).height();
                        	if (!isLoadedSuccessfully && $(window).scrollTop() > ($(document).height() - $(window).height()) * 0.1) {
                                    seasonality('chartcontainerYearlyTrend1920', dict, indexname, '#monthlytable1920', "#slider1920", 'monthlytabletitle1920', 'chartcontainerSeasonality1920', dict[indexname + ' Daily'][0][1], dict[indexname + ' Daily']);
                                    isLoadedSuccessfully = true;
                        		}
                        	}
                        );
                    }
                });
            }
            
            else if (chartcontainer == "chartcontainerC2")
            {    
                //cchart(chartcontainer);  
                var tmpname = data.toUpperCase();
                var indextmpname = 'HS';
                var isMobile = window.mobileAndTabletcheck();
                var sectorDict = new Object();
                var indexconstituent = "HSI + HSCEI + HSTECH Constituents";
                

                // load heatmap & scatter after sector performance so that sector % is available
                anychart.data.loadJsonFile('db_treemap_index_get.php?data=' + tmpname, function (data) {
                    var longperiod = 200;
                    if (exchangeName == "Hong Kong" || exchangeName == "Shanghai" || exchangeName == "Shenzhen")
                        longperiod = 250;
                        
					data[0].product = data[0].product.replace(exchangeName, indexconstituent);
					var subtitle = data[0].product;

                    var dd = bubbleData(data, sectorDict);
                    
                    const d1_movers = getTopBottom10Movers(dd.d1);
                    const d5_movers = getTopBottom10Movers(dd.d5);
                    const d20_movers = getTopBottom10Movers(dd.d20);
                    
                    renderMovers("moverA2", d1_movers, subtitle, indextmpname, '1 Day Performance');
                    renderMovers("moverB2", d5_movers, subtitle, indextmpname, '5 Days Performance');
                    renderMovers("moverC2", d20_movers, subtitle, indextmpname, '20 Days Performance');
                    
                    const dd_highlow = getTopBottom10HighLow(dd.hl250);
                    const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
                    const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
                    
                    renderMoversXY("highlowA2", dd_highlow, subtitle, indextmpname, '250-Day High & 250-Day Low');
                    renderMoversXY("MA50_LongB2", dd_MA50_long, subtitle, indextmpname, `MA${longperiod} & MA50`, `MA${longperiod}`);
                    renderMoversXY("RSIC2", dd_rsi, subtitle, indextmpname, 'RSI Weekly & RSI Daily', 'RSI');
                    
                    treemap(data, "treecontainerA2", 1, 1, sectorDict, isMobile, highlight);
                    bubble(dd.d1, "bubblecontainerA2", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                    bubble(dd.hl250, "tabubblecontainerA2", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0, highlight);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerB2", 1, 5, sectorDict, isMobile, highlight);
                        bubble(dd.d5, "bubblecontainerB2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.ma50_long, "tabubblecontainerB2", 1, '% From MA' + longperiod + ' vs MA50', subtitle, "", "% From MA" + longperiod, "% From MA50", null, null, '', false, true, true, true, true, 0, 0, highlight);
                    }, 100);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerC2", 1, 20, sectorDict, isMobile, highlight);
                        bubble(dd.d20, "bubblecontainerC2", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.rsi14w_d, "tabubblecontainerC2", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50, highlight);
                    }, 100);
                    
                    
                    var dataTop10 = dd.d1.slice(0, 10);
                    
                    codeStrSQL = '';
                    var codeArray = []
                    for (var i = 0; i < dataTop10.length; i++)
                    {
                        codeStrSQL += dataTop10[i].code;
                        if (i < dataTop10.length - 1)
                            codeStrSQL += ",";
                            
                        codeArray.push(dataTop10[i].code);
                    }

                    var dict = new Object();
                    var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
                    var isIEX = 0;
                    if (tmpname == "NYSE" || tmpname == "NASDAQ")
                        isIEX = 1;
                    $.ajax({
                        url : "db_top10_get.php?data=" + encodeURIComponent(codeStrSQL) + "&isIEX=" + isIEX,
                        success : function(result){
                            var jsonObject = result.split(/\r?\n|\r/);
                            for (var i = 0; i < jsonObject.length; i++) {
                                if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                    jsonObject[i] = "," + jsonObject[i];
                                    
                                var row = jsonObject[i].split(',')
                                /*if (i == 0)
                                {
                                    row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                }
                                else*/ if (row.length == 3)
                                {
                                    if (row[1].length == 1)
                                        row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 3)
                                        row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 5)
                                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                        
                                    row[1] = row[1].replace("-", "");
                                }
                                
                        	    if (row.length == 3)
                        	    { 
                        	        if (row[0] != "")
                        	            previousName = row[0];
                    	            else
                    	                row[0] = previousName;
                    	            
                                    row[2] /= 1;
                    	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                    var previousDate = row[1];
                    
                                    if (dict[row[0]]) {
                                         dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                    } else {
                                        dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                    }
                        	    }
                            }
                            
                            dict['tmp'] = [[1, 1]]; // Add a tmp series at the end to avoid error. Will remove it in stockCompare.
                            codeArray.push('tmp'); // sort by market cap
                            //const stocks = Object.keys(dict); // sort by stockcode
                            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                            var rangeselector = 4;
                            var creditYoffset = 26;
                            if (browserwidth > 576)
                            {
                                rangeselector = 6;
                                creditYoffset = 0;
                            }
                            stockCompare('top10container2', dict, codeArray, indexconstituent + " Top 10 Market Cap Stocks Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 1, true, true, dataTop10);
                        }
                    });
                });
            }
            
            else if (chartcontainer == "chartcontainerC3")
            {    
                //cchart(chartcontainer);  
                var tmpname = 'NYSE';
                var indextmpname = 'SP100';
                var isMobile = window.mobileAndTabletcheck();
                var sectorDict = new Object();
                var indexconstituent = "S&P 100 Constituents";
                

                // load heatmap & scatter after sector performance so that sector % is available
                anychart.data.loadJsonFile('db_treemap_index_get.php?data=' + tmpname, function (data) {
                    var longperiod = 200;
                    if (exchangeName == "Hong Kong" || exchangeName == "Shanghai" || exchangeName == "Shenzhen")
                        longperiod = 250;
                        
                    data[0].product = data[0].product.replace('NYSE', indexconstituent);
					var subtitle = data[0].product;

                    var dd = bubbleData(data, sectorDict);
                    
                    const d1_movers = getTopBottom10Movers(dd.d1);
                    const d5_movers = getTopBottom10Movers(dd.d5);
                    const d20_movers = getTopBottom10Movers(dd.d20);
                    
                    renderMovers("moverA3", d1_movers, subtitle, indextmpname, '1 Day Performance');
                    renderMovers("moverB3", d5_movers, subtitle, indextmpname, '5 Days Performance');
                    renderMovers("moverC3", d20_movers, subtitle, indextmpname, '20 Days Performance');
                    
                    const dd_highlow = getTopBottom10HighLow(dd.hl250);
                    const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
                    const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
                    
                    renderMoversXY("highlowA3", dd_highlow, subtitle, indextmpname, '250-Day High & 250-Day Low');
                    renderMoversXY("MA50_LongB3", dd_MA50_long, subtitle, indextmpname, `MA${longperiod} & MA50`, `MA${longperiod}`);
                    renderMoversXY("RSIC3", dd_rsi, subtitle, indextmpname, 'RSI Weekly & RSI Daily', 'RSI');
                    
                    treemap(data, "treecontainerA3", 1, 1, sectorDict, isMobile, highlight);
                    bubble(dd.d1, "bubblecontainerA3", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                    bubble(dd.hl250, "tabubblecontainerA3", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0, highlight);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerB3", 1, 5, sectorDict, isMobile, highlight);
                        bubble(dd.d5, "bubblecontainerB3", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.ma50_long, "tabubblecontainerB3", 1, '% From MA' + longperiod + ' vs MA50', subtitle, "", "% From MA" + longperiod, "% From MA50", null, null, '', false, true, true, true, true, 0, 0, highlight);
                    }, 100);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerC3", 1, 20, sectorDict, isMobile, highlight);
                        bubble(dd.d20, "bubblecontainerC3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.rsi14w_d, "tabubblecontainerC3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50, highlight);
                    }, 100);
                    
                    
                    var dataTop10 = dd.d1.slice(0, 10);
                    
                    codeStrSQL = '';
                    var codeArray = []
                    for (var i = 0; i < dataTop10.length; i++)
                    {
                        codeStrSQL += dataTop10[i].code;
                        if (i < dataTop10.length - 1)
                            codeStrSQL += ",";
                            
                        codeArray.push(dataTop10[i].code);
                    }

                    var dict = new Object();
                    var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
                    var isIEX = 0;
                    if (tmpname == "NYSE" || tmpname == "NASDAQ")
                        isIEX = 1;
                    $.ajax({
                        url : "db_top10_get.php?data=" + encodeURIComponent(codeStrSQL) + "&isIEX=" + isIEX,
                        success : function(result){
                            var jsonObject = result.split(/\r?\n|\r/);
                            for (var i = 0; i < jsonObject.length; i++) {
                                if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                    jsonObject[i] = "," + jsonObject[i];
                                    
                                var row = jsonObject[i].split(',')
                                /*if (i == 0)
                                {
                                    row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                }
                                else*/ if (row.length == 3)
                                {
                                    if (row[1].length == 1)
                                        row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 3)
                                        row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 5)
                                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                        
                                    row[1] = row[1].replace("-", "");
                                }
                                
                        	    if (row.length == 3)
                        	    { 
                        	        if (row[0] != "")
                        	            previousName = row[0];
                    	            else
                    	                row[0] = previousName;
                    	            
                                    row[2] /= 1;
                    	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                    var previousDate = row[1];
                    
                                    if (dict[row[0]]) {
                                         dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                    } else {
                                        dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                    }
                        	    }
                            }
                            
                            dict['tmp'] = [[1, 1]]; // Add a tmp series at the end to avoid error. Will remove it in stockCompare.
                            codeArray.push('tmp'); // sort by market cap
                            //const stocks = Object.keys(dict); // sort by stockcode
                            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                            var rangeselector = 4;
                            var creditYoffset = 26;
                            if (browserwidth > 576)
                            {
                                rangeselector = 6;
                                creditYoffset = 0;
                            }
                            stockCompare('top10container3', dict, codeArray, indexconstituent + " Top 10 Market Cap Stocks Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 1, true, true, dataTop10);
                        }
                    });
                });
            }
            
            else if (chartcontainer == "chartcontainerC4")
            {    
                //cchart(chartcontainer);  
                var tmpname = 'NASDAQ';
                var indextmpname = 'NDX';
                var isMobile = window.mobileAndTabletcheck();
                var sectorDict = new Object();
                var indexconstituent = "Nasdaq 100 Constituents";
                

                // load heatmap & scatter after sector performance so that sector % is available
                anychart.data.loadJsonFile('db_treemap_index_get.php?data=' + tmpname, function (data) {
                    var longperiod = 200;
                    if (exchangeName == "Hong Kong" || exchangeName == "Shanghai" || exchangeName == "Shenzhen")
                        longperiod = 250;
                        
                    data[0].product = data[0].product.replace('Nasdaq', indexconstituent);
					var subtitle = data[0].product;

                    var dd = bubbleData(data, sectorDict);
                    
                    const d1_movers = getTopBottom10Movers(dd.d1);
                    const d5_movers = getTopBottom10Movers(dd.d5);
                    const d20_movers = getTopBottom10Movers(dd.d20);
                    
                    renderMovers("moverA4", d1_movers, subtitle, indextmpname, '1 Day Performance');
                    renderMovers("moverB4", d5_movers, subtitle, indextmpname, '5 Days Performance');
                    renderMovers("moverC4", d20_movers, subtitle, indextmpname, '20 Days Performance');
                    
                    const dd_highlow = getTopBottom10HighLow(dd.hl250);
                    const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
                    const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
                    
                    renderMoversXY("highlowA4", dd_highlow, subtitle, indextmpname, '250-Day High & 250-Day Low');
                    renderMoversXY("MA50_LongB4", dd_MA50_long, subtitle, indextmpname, `MA${longperiod} & MA50`, `MA${longperiod}`);
                    renderMoversXY("RSIC4", dd_rsi, subtitle, indextmpname, 'RSI Weekly & RSI Daily', 'RSI');
                    
                    treemap(data, "treecontainerA4", 1, 1, sectorDict, isMobile, highlight);
                    bubble(dd.d1, "bubblecontainerA4", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                    bubble(dd.hl250, "tabubblecontainerA4", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0, highlight);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerB4", 1, 5, sectorDict, isMobile, highlight);
                        bubble(dd.d5, "bubblecontainerB4", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.ma50_long, "tabubblecontainerB4", 1, '% From MA' + longperiod + ' vs MA50', subtitle, "", "% From MA" + longperiod, "% From MA50", null, null, '', false, true, true, true, true, 0, 0, highlight);
                    }, 100);
                    
                    setTimeout(function() {
                        treemap(data, "treecontainerC4", 1, 20, sectorDict, isMobile, highlight);
                        bubble(dd.d20, "bubblecontainerC4", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0, highlight);
                        bubble(dd.rsi14w_d, "tabubblecontainerC4", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50, highlight);
                    }, 100);
                    
                    
                    var dataTop10 = dd.d1.slice(0, 10);
                    
                    codeStrSQL = '';
                    var codeArray = []
                    for (var i = 0; i < dataTop10.length; i++)
                    {
                        codeStrSQL += dataTop10[i].code;
                        if (i < dataTop10.length - 1)
                            codeStrSQL += ",";
                            
                        codeArray.push(dataTop10[i].code);
                    }

                    var dict = new Object();
                    var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
                    var isIEX = 0;
                    if (tmpname == "NYSE" || tmpname == "NASDAQ")
                        isIEX = 1;
                    $.ajax({
                        url : "db_top10_get.php?data=" + encodeURIComponent(codeStrSQL) + "&isIEX=" + isIEX,
                        success : function(result){
                            var jsonObject = result.split(/\r?\n|\r/);
                            for (var i = 0; i < jsonObject.length; i++) {
                                if ((jsonObject[i].match(/,/g) || []).length == 1) // no data_name field
                                    jsonObject[i] = "," + jsonObject[i];
                                    
                                var row = jsonObject[i].split(',')
                                /*if (i == 0)
                                {
                                    row[1] = "20"+ row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                }
                                else*/ if (row.length == 3)
                                {
                                    if (row[1].length == 1)
                                        row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 3)
                                        row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                    else if (row[1].length == 5)
                                        row[1] = "20" + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                        
                                    row[1] = row[1].replace("-", "");
                                }
                                
                        	    if (row.length == 3)
                        	    { 
                        	        if (row[0] != "")
                        	            previousName = row[0];
                    	            else
                    	                row[0] = previousName;
                    	            
                                    row[2] /= 1;
                    	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                    var previousDate = row[1];
                    
                                    if (dict[row[0]]) {
                                         dict[row[0]].push([new Date(row[1]).getTime(), row[2]]);
                                    } else {
                                        dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                    }
                        	    }
                            }
                            
                            dict['tmp'] = [[1, 1]]; // Add a tmp series at the end to avoid error. Will remove it in stockCompare.
                            codeArray.push('tmp'); // sort by market cap
                            //const stocks = Object.keys(dict); // sort by stockcode
                            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                            var rangeselector = 4;
                            var creditYoffset = 26;
                            if (browserwidth > 576)
                            {
                                rangeselector = 6;
                                creditYoffset = 0;
                            }
                            stockCompare('top10container4', dict, codeArray, indexconstituent + " Top 10 Market Cap Stocks Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 1, true, true, dataTop10);
                        }
                    });
                });
            }

        }
    }
    
    var dictYearlyTrend = new Object();
    var dictYearlyTrend1920 = new Object();
    var seriesname = [];
    function seasonality(id, dict, indexname, monthlytable, slider, monthlytabletitle, chartcontainerSeasonality, firstClose, dailydata = null)
    {
        if (dailydata !== null)
        {
            var dictYearlyTrend_local = new Object();
                
            if (dailydata.length > 0)
            {
                var lastdate = new Date(dailydata[dailydata.length - 1][0]);
    
                var count = 0;
                for (var i = 0; i < dailydata.length; i++) {
                    var tmpdate = new Date(dailydata[i][0]);
    
                    if (lastdate.getFullYear() - 5 > tmpdate.getFullYear() )
                        continue;
                    else
                    {
                        if (dictYearlyTrend_local[tmpdate.getFullYear()])
                        {
                            dictYearlyTrend_local[tmpdate.getFullYear()].push([dayofyear(tmpdate), dailydata[i][1]]);
                        }
                        else
                        {
                            if (i > 0)
                            {
                                dictYearlyTrend_local[tmpdate.getFullYear()] = [[0, dailydata[i - 1][1]]]; // add last day of the previous year
                            }
                            
                            if (dictYearlyTrend_local[tmpdate.getFullYear()])
                            {
                                dictYearlyTrend_local[tmpdate.getFullYear()].push([dayofyear(tmpdate), dailydata[i][4]]);
                            }
                            else
                            {
                                dictYearlyTrend_local[tmpdate.getFullYear()] = [[dayofyear(tmpdate), dailydata[i][4]]]; // if no last day of the previous year
                            }
                            
                            dictYearlyTrend_local[tmpdate.getFullYear()].push([dayofyear(tmpdate), dailydata[i][1]]);
                            seriesname[count] = tmpdate.getFullYear();
                            count++;
                        }
                    }
                }
            }
            
            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;

            yearlyTrendChart(id, dictYearlyTrend_local, seriesname, indexname + (id.endsWith('1920') ? '' : ' Index'), browserwidth, true, indexname, '', true);
            
            if (id.endsWith('1920'))
                dictYearlyTrend1920 = dictYearlyTrend_local;
            else
                dictYearlyTrend = dictYearlyTrend_local;
        }            
            
        var subtitle = chartcontainerSeasonality == 'chartcontainerSeasonality1700' ? 'Merging with pre-February 1885 UK data' : chartcontainerSeasonality == 'chartcontainerSeasonality1841' ? 'Merging with pre-July 1964 HK & UK data' : indexname;
        monthlytabledata = seasonalityChart(indexname + (indexname == 'Gold Price' ? '' : ' Index'), dict[indexname + ' Monthly'], firstClose, chartcontainerSeasonality, subtitle);
                        
        if (monthlytabledata !== null)
        {
            document.getElementById(monthlytabletitle).textContent = indexname + (indexname == 'Gold Price' ? ' Monthly Return %' : " Index Monthly Return %");
            
            var dataSet = [];
            
            
            Object.keys(monthlytabledata).forEach(function(key) {
                var dataSettmp = [];
                dataSettmp = [key, null, null, null, null, null, null, null, null, null, null, null, null, null];
                for (const [key1, value1] of Object.entries(monthlytabledata[key]))
                {
                    
                    for (const [key2, value2] of Object.entries(value1)) {
                        if (key2 == "January")
                            dataSettmp[1] = value2;
                        else if (key2 == "February")
                            dataSettmp[2] = value2;
                        else if (key2 == "March")
                            dataSettmp[3] = value2;
                        else if (key2 == "April")
                            dataSettmp[4] = value2;
                        else if (key2 == "May")
                            dataSettmp[5] = value2;
                        else if (key2 == "June")
                            dataSettmp[6] = value2;
                        else if (key2 == "July")
                            dataSettmp[7] = value2;
                        else if (key2 == "August")
                            dataSettmp[8] = value2;
                        else if (key2 == "September")
                            dataSettmp[9] = value2;
                        else if (key2 == "October")
                            dataSettmp[10] = value2;
                        else if (key2 == "November")
                            dataSettmp[11] = value2;
                        else if (key2 == "December")
                            dataSettmp[12] = value2;
                        else if (key2 == "YTD")
                            dataSettmp[13] = value2;
                    }
                    
                }
                dataSet.push(dataSettmp);
            });

            /*dataSet.forEach(r => {
                var div1 = document.createElement('div');
                div1.innerHTML = r[1];
                r[1] = div1;
             
                var div3 = document.createElement('div');
                div3.innerHTML = r[3];
                r[3] = div3;
            })*/
             
            var table = $(monthlytable).DataTable({
                responsive: true,
                order: [[0, 'desc']],
                columns: [
                    { title: 'Year', className: 'dt-body-center' },
                    { title: 'Jan', className: 'dt-body-center' },
                    { title: 'Feb', className: 'dt-body-center' },
                    { title: 'Mar', className: 'dt-body-center' },
                    { title: 'Apr', className: 'dt-body-center' },
                    { title: 'May', className: 'dt-body-center' },
                    { title: 'Jun', className: 'dt-body-center' },
                    { title: 'Jul', className: 'dt-body-center' },
                    { title: 'Aug', className: 'dt-body-center' },
                    { title: 'Sep', className: 'dt-body-center' },
                    { title: 'Oct', className: 'dt-body-center' },
                    { title: 'Nov', className: 'dt-body-center' },
                    { title: 'Dec', className: 'dt-body-center' },
                    { title: 'YTD', className: 'dt-body-center' },
                ],
                /*columnDefs: [{    // show +, ± sign for cells, commented out bz the table looks messy with this
                    "targets": [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13 ],
                    "render": function ( data, type, full, meta ) {
                        if (data > 0)
                            data = '+' + data;
                        else if (data === 0)
                            data = '\u00B1' + data;
                            
                        return data;
                    }
                }],*/
                data: dataSet,
                searching: false, paging: false, info: false,
                rowCallback: function(row, data, index){
                    $(row).find('td:eq(0)').css('color', 'dimgrey').css('font-weight', 'bold').css('font-size', '14px');
                    for (var i = 1; i < data.length; i++)
                    {
                        if (data[i] === null)
                            continue;
                            
                        if(data[i] >= -0.5 && data[i] < 0.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#666666').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= 0.5 && data[i] < 1.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#008f00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= 1.5 && data[i] < 4.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#009900').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= 4.5 && data[i] < 7.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#00ba00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= 7.5 && data[i] < 10.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#00dd00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= 10.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#00ff00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= -1.5 && data[i] < -0.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#8f0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= -4.5 && data[i] < -1.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#990000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= -7.5 && data[i] < -4.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#ba0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] >= -10.5 && data[i] < -7.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#dd0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        else if(data[i] < -10.5)
                        {
                            $(row).find('td:eq('+i+')').css('background-color', '#ff0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        }
                        
                        if(i == data.length - 1)
                        {
                            $(row).find('td:eq('+i+')').css('text-shadow', '0 0 3px #000000, 0 0 5px #000000').css('border-left', '2px solid #000');
                        }
                    }
                },
                createdRow: function(row, data, dataIndex) {
                    if (chartcontainerSeasonality == 'chartcontainerSeasonality1700' && data[0] == "1914") {
                        var startCol = 8; // first col to merge
                
                        // Merge 4 monthly columns
                        $('td', row).eq(startCol)
                            .attr('colspan', 4)
                            .addClass('text-center')
                            .text('World War I Stock Market Shutdown').css('font-weight', 'bold');
                
                        // Remove only the next 3 cells after the merged cell
                        for (var i = 0; i < 3; i++) {
                            $('td', row).eq(startCol + 1).remove();
                        }
                
                        // Re-apply background color for Dec and last column manually
                        var decCell = $('td', row).eq(startCol + 1);
                        var lastCell = $('td', row).eq(startCol + 2);
                
                        decCell.css('background-color', 'red').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                        lastCell.css('background-color', 'red').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px').css('text-shadow', '0 0 3px #000000, 0 0 5px #000000').css('border-left', '2px solid #000');
                    }
                }
            });
        }
        
        var min_current = 0;
        var max_current = 0;
        if (dict[indexname + ' Monthly'].length > 0 && dict[indexname + ' Monthly'].length - 1 > 0 && dict[indexname + ' Monthly'][0].length > 0  && dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1].length > 0)
        {
            min_current = new Date(dict[indexname + ' Monthly'][0][0]).getFullYear();
            max_current = new Date(dict[indexname + ' Monthly'][dict[indexname + ' Monthly'].length - 1][0]).getFullYear();
        }
        
        $(slider).ionRangeSlider({
        	skin: "round",
        	prettify_enabled: false,
        	//grid_snap: true,
        	grid_num: 5,
        	drag_interval: true,
            min: min_current,
            max: max_current,
            from: min_current,
            to: max_current,
            onChange: function (data) {
                        //console.log(`Range changed for: ${e.detail.sliderId}. Min/Max range values are available in this object too`);
                        var dictIndexnameMonthly = dict[indexname + ' Monthly'].map((x) => x);
                        var minRangeValue = data.from;
                        var maxRangeValue = data.to;
                        
                        if (min_current == minRangeValue && max_current == maxRangeValue)
                            return;
                            
                        if (minRangeValue !== null && maxRangeValue !== null)
                        {
                            var startIndex = 0;
                            var endIndex = dictIndexnameMonthly.length - 1;
                            
                            for (var i = 0; i < dictIndexnameMonthly.length; i++)
                            {
                                if (i > 0 && new Date(dictIndexnameMonthly[i][0]).getFullYear() == minRangeValue &&
                                    new Date(dictIndexnameMonthly[i - 1][0]).getFullYear() < minRangeValue)
                                    startIndex = i - 1;
                                    
                                if (new Date(dictIndexnameMonthly[i][0]).getFullYear() < maxRangeValue + 1)
                                {
                                    endIndex = i;
                                }
                            }
                        }
                        
                        if (endIndex >= 0)
                        {
                            dictIndexnameMonthly.splice(endIndex + 1);
                        }
                        
                        if (startIndex >= 0)
                        {
                            dictIndexnameMonthly.splice(0, startIndex);
                        }
                        
                        seasonalityChart(indexname + ' Index', dictIndexnameMonthly, dictIndexnameMonthly[0][0] == dict[indexname + ' Monthly'][0][0] && new Date(dictIndexnameMonthly[0][0]).getFullYear() == minRangeValue ? firstClose : null, chartcontainerSeasonality, subtitle);
                        
                        min_current = minRangeValue;
                        max_current = maxRangeValue;
                    }
        });    
    }
    
    /*function numberWithCommas(x) {
        return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
    }*/
    function datedecrypt (date)
    {
        if (date.length == 3)
        {
            return "200" + date.substring(0, 1) + "-" + monthdict[date.substring(1, 2)] + "-" + daydict[date.substring(2, 3)];
        }
        else if (date.length == 4)
        {
            return "20" + date.substring(0, 2) + "-" + monthdict[date.substring(2, 3)] + "-" + daydict[date.substring(3, 4)];
        }
        else
            return "";
    }
    
    function dayofyear (date)
    {
        var start = new Date(date.getFullYear(), 0, 0);
        var diff = date - start;
        var oneDay = 1000 * 60 * 60 * 24;
        return day = Math.floor(diff / oneDay);    
    }
    
    jQuery(window).load(function(){
        if (toMap == "1" || toMap == "5" || toMap == "20")
        {
            if (sort == "")
                jQuery("html,body").animate({scrollTop: $("#Heatmap").offset().top - 85}, 1000);
            else
                jQuery("html,body").animate({scrollTop: 0}, 1000);

            if (toMap == "5")
                $('a[href="#maptab-2"]').click();
            else if (toMap == "20")
                $('a[href="#maptab-3"]').click();
        }
        else
            jQuery("html,body").animate({scrollTop: 0}, 1000);
    });

    $(document).ready(function() {
        //On Click Event
        $("a.mynav-link").click(function() {
    
            if (!(this.hash == "#tab-1" && tab1loaded) &&
                !(this.hash == "#tab-2" && tab2loaded) &&
                !(this.hash == "#tab-3" && tab3loaded) &&
                !(this.hash == "#tab-4" && tab4loaded) &&
                !(this.hash == "#tab-5" && tab5loaded) &&
                !(this.hash == "#tab-6" && tab6loaded) &&
                !(this.hash == "#tab-7" && tab7loaded) &&
                !(this.hash == "#tab-8" && tab8loaded) &&
                !(this.hash == "#tab-9" && tab9loaded) &&
                !(this.hash == "#tab-10" && tab10loaded) &&
                !(this.hash == "#tab-11" && tab11loaded) &&
                !(this.hash == "#tab-12" && tab12loaded) &&
                !(this.hash == "#tab-13" && tab13loaded) &&
                !(this.hash == "#tab-14" && tab14loaded))
            {
                window.location.href = this.hash;
                jQuery("html,body").animate({scrollTop: 0}, 1000);
            }
            
            highchartReflow();
        });
        
        $("a.nl").click(function() {
            highchartReflow();
        });
    });
    
function highchartReflow()
{
    setTimeout(function() {
        if (Highcharts.charts) {
            for (var i = Highcharts.charts.length - 1; i >= 0; i--) {
                var chart = Highcharts.charts[i];
                if (chart && chart !== undefined && chart.renderTo !== null) {
                    chart.reflow();
                }
            }
        }
    }, 0);
}

function returnComment(indexname, priceData, returnData, timeframe)
{
    var comment = '';
    var upCount = 0;
    var downCount = 0;
    var sinceCount = 0;
    var sinceDate0 = new Date(priceData[0][0]);
    var totalLoss;
    var totalGain;
    
    /*for (var i = returnData.length - 1; i >= 0; i--)
    {
        if (i == returnData.length - 1)
        {
            if (returnData[i][1] > 0)
            {
                upCount++;
                totalGain = 1 + returnData[i][1] / 100;
            }
            else if (returnData[i][1] < 0)
            {
                downCount++;
                totalLoss = 1 + returnData[i][1] / 100;
            }
            else
                break;
        }
        else
        {
            if (upCount > 0 && returnData[i][1] > 0)
            {
                upCount++;
                totalGain *= 1 + returnData[i][1] / 100;
            }
            else if (downCount > 0 && returnData[i][1] < 0)
            {
                downCount++;
                totalLoss *= 1 + returnData[i][1] / 100;
            }
            else
                break;
        }
    }*/
    if (priceData.length > 1)
    {
        var stopCount = false;
        for (var i = priceData.length - 1; i >= 0; i--)
        {
            if (i == priceData.length - 1)
            {
                if (priceData[i][1] > priceData[i - 1][1])
                {
                    upCount++;
                    totalGain = priceData[i][1] / priceData[i - 1][1];
                }
                else if (priceData[i][1] < priceData[i - 1][1])
                {
                    downCount++;
                    totalLoss = priceData[i][1] / priceData[i - 1][1];
                }
                else
                    break;
            }
            else
            {
                if (!stopCount && upCount > 0 && priceData[i][1] > priceData[i - 1][1])
                {
                    upCount++;
                    totalGain *= priceData[i][1] / priceData[i - 1][1];
                }
                else if (!stopCount && downCount > 0 && priceData[i][1] < priceData[i - 1][1])
                {
                    downCount++;
                    totalLoss *= priceData[i][1] / priceData[i - 1][1];
                }
                else
                {
                    stopCount = true;
                    
                    if (i > 0 && upCount > 0 && priceData[i][1] > priceData[i - 1][1])
                    {
                        sinceCount++;
                    }
                    else if (i > 0 && downCount > 0 && priceData[i][1] < priceData[i - 1][1])
                    {
                        sinceCount++;
                    }
                    else if (upCount > 0 && sinceCount >= upCount)
                    {
                        sinceDate0 = new Date(priceData[i + sinceCount][0]);
                        break;
                    }
                    else if (downCount > 0 && sinceCount >= downCount)
                    {
                        sinceDate0 = new Date(priceData[i + sinceCount][0]);
                        break;
                    }
                    else
                    {
                        sinceCount = 0;
                    }
                }
            }
        }
    }

    var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
    var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLup1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var TAcommentHTMLdown1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    
    if (upCount > 1)
    {
        comment += indexname + " rose " + upCount + " " + timeframe + ' in a row with a total gain of <span style="font-weight:bold">+' + rounding((totalGain - 1) * 100, 100) + "%</span>. The longest winning streak since " + sinceDate0.toISOString().slice(0,10) + " " + sinceDate0.toLocaleDateString('en-US',{weekday: 'short'});
        TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
    }
    else if (downCount > 1)
    {
        comment += indexname + " fell " + downCount + " " + timeframe + ' in a row with a total loss of <span style="color:red;font-weight:bold">' + rounding((totalLoss - 1) * 100, 100) + "%</span>. The longest losing streak since " + sinceDate0.toISOString().slice(0,10) + " " + sinceDate0.toLocaleDateString('en-US',{weekday: 'short'});
        TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
    }
    
    if (returnData[returnData.length - 1][1] < 0)
    {
        var minSince = 0;
        for (j = returnData.length - 2; j >= 0 ; j--)
        {
            var advtmp = returnData[j][1];
            if (returnData[returnData.length - 1][1] > advtmp)
            {
                minSince = returnData.length - 1 - j;
                break;
            }
        }
        
        if (minSince > 5)
        {
            var sinceDate = new Date(returnData[returnData.length - 1 - minSince][0])
            var sinceDateStr = sinceDate.toISOString().slice(0,10) + " " + sinceDate.toLocaleDateString('en-US',{weekday: 'short'});
            var sincetxt = '<span style="color:red;font-weight:bold">' + returnData[returnData.length - 1][1] + "%</span> is the worst " + (timeframe == "days" ? timeframe.substring(0, timeframe.length - 2) + "i" : timeframe.substring(0, timeframe.length - 1)) + "ly loss in " + minSince + " " + timeframe + " since " + sinceDateStr;
            
            if (upCount > 1 || downCount > 1)
                TAcommentTXT.push(TAcommentHTMLdown.format(sincetxt.replace('dayly', 'daily').replace('$-', '-$')));
            else
                TAcommentTXT.push(TAcommentHTMLdown1st.format(sincetxt.replace('dayly', 'daily').replace('$-', '-$')));
        }

    }
    else if (returnData[returnData.length - 1][1] > 0)
    {
        var maxSince = 0;
        for (j = returnData.length - 2; j >= 0 ; j--)
        {
            var advtmp = returnData[j][1];
            if (returnData[returnData.length - 1][1] < advtmp)
            {
                maxSince = returnData.length - 1 - j;
                break;
            }
        }
        
        if (maxSince > 5)
        {
            var sinceDate = new Date(returnData[returnData.length - 1 - maxSince][0])
            var sinceDateStr = sinceDate.toISOString().slice(0,10) + " " + sinceDate.toLocaleDateString('en-US',{weekday: 'short'});
            var sincetxt = '<span style="font-weight:bold">+' + returnData[returnData.length - 1][1] + "%</span> is the best " + (timeframe == "days" ? timeframe.substring(0, timeframe.length - 2) + "i" : timeframe.substring(0, timeframe.length - 1)) + "ly gain in " + maxSince + " " + timeframe+ " since " + sinceDateStr;
            
            if (upCount > 1 || downCount > 1)
                TAcommentTXT.push(TAcommentHTMLup.format(sincetxt.replace('dayly', 'daily')));
            else
                TAcommentTXT.push(TAcommentHTMLup1st.format(sincetxt.replace('dayly', 'daily')));
        }
    }
    
    
    return TAcommentTXT.join("") + "</table><br>";
}

function CCASSComment(returnData, timeframe)
{
    var comment = '';
    var upCount = 0;
    var downCount = 0;
    var sinceCount = 0;
    //var sinceDate0 = new Date(priceData[0][0]);
    var totalLoss;
    var totalGain;
    
    if (returnData.length > 1)
    {
        var stopCount = false;
        for (var i = returnData.length - 1; i >= 0; i--)
        {
            if (i == returnData.length - 1)
            {
                if (returnData[i][1] > 0)
                {
                    upCount++;
                    totalGain = returnData[i][1];
                }
                else if (returnData[i][1] < 0)
                {
                    downCount++;
                    totalLoss = returnData[i][1];
                }
                else
                    break;
            }
            else
            {
                if (!stopCount && upCount > 0 && returnData[i][1] > 0)
                {
                    upCount++;
                    totalGain += returnData[i][1];
                }
                else if (!stopCount && downCount > 0 && returnData[i][1] < 0)
                {
                    downCount++;
                    totalLoss += returnData[i][1];
                }
                else
                {
                    stopCount = true;
                    
                    if (i > 0 && upCount > 0 && returnData[i][1] > 0)
                    {
                        sinceCount++;
                    }
                    else if (i > 0 && downCount > 0 && returnData[i][1] < 0)
                    {
                        sinceCount++;
                    }
                    else if (upCount > 0 && sinceCount >= upCount)
                    {
                        sinceDate0 = new Date(returnData[i + sinceCount][0]);
                        break;
                    }
                    else if (downCount > 0 && sinceCount >= downCount)
                    {
                        sinceDate0 = new Date(returnData[i + sinceCount][0]);
                        break;
                    }
                    else
                    {
                        sinceCount = 0;
                    }
                }
            }
        }
    }

    var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
    var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLup1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var TAcommentHTMLdown1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    
    var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLsideway1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
    
    if (upCount > 1)
    {
        comment += "Southbound funds have recorded inflows for " + upCount + " consecutive " + timeframe + 's with a net fund inflow of <span style="font-weight:bold">$' + rounding(totalGain / 1000, 1000) + " bil</span>, the longest " + timeframe + 'ly inflow streak since <span style="white-space:nowrap;">' + sinceDate0.toISOString().slice(0,10) + "</span> " + sinceDate0.toLocaleDateString('en-US',{weekday: 'short'});
        TAcommentTXT.push(TAcommentHTMLup1st.format(comment.replace('dayly', 'daily')));
    }
    else if (downCount > 1)
    {
        comment += "Southbound funds have recorded outflow for " + downCount + " consecutive " + timeframe + 's with a net fund outflow of <span style="color:red;font-weight:bold">$' + rounding(totalLoss / 1000, 1000) + " bil</span>, the longest " + timeframe + 'ly outflow streak since <span style="white-space:nowrap;">' + sinceDate0.toISOString().slice(0,10) + "</span> " + sinceDate0.toLocaleDateString('en-US',{weekday: 'short'});
        TAcommentTXT.push(TAcommentHTMLdown1st.format(comment.replace('dayly', 'daily')));
    }
    
    if (timeframe == 'month')
    {
        if (returnData[returnData.length - 1][1] > 0)
            TAcommentTXT.push(TAcommentHTMLup.format(formatToMonthYear(returnData[returnData.length - 1][0]) + ' has a net fund inflow of <span style="font-weight:bold">$'+ rounding(returnData[returnData.length - 1][1] / 1000, 1000) + ' bil</span>'));
        else if (returnData[returnData.length - 1][1] < 0)
            TAcommentTXT.push(TAcommentHTMLdown.format(formatToMonthYear(returnData[returnData.length - 1][0]) + ' has a net find outflow of <span style="color:red;font-weight:bold">$'+ rounding(returnData[returnData.length - 1][1] / 1000, 1000) + ' bil</span>'));    
        else
            TAcommentTXT.push(TAcommentHTMLsideway.format(formatToMonthYear(returnData[returnData.length - 1][0]) + ' has a net fund flow of $0'));    
    }
    
    if (returnData[returnData.length - 1][1] < 0)
    {
        var minSince = 0;
        for (j = returnData.length - 2; j >= 0 ; j--)
        {
            var advtmp = returnData[j][1];
            if (returnData[returnData.length - 1][1] > advtmp)
            {
                minSince = returnData.length - 1 - j;
                break;
            }
        }
        
        if (minSince > 5)
        {
            var sinceDate = new Date(returnData[returnData.length - 1 - minSince][0])
            var sinceDateStr = sinceDate.toISOString().slice(0,10) + " " + sinceDate.toLocaleDateString('en-US',{weekday: 'short'});
            var sincetxt = '<span style="color:red;font-weight:bold">$' + rounding(returnData[returnData.length - 1][1] / 1000, 1000) + " bil</span> is the worst net " + (timeframe == "days" ? timeframe.substring(0, timeframe.length - 2) + "i" : timeframe.substring(0, timeframe.length)) + "ly outflow in " + minSince + " " + timeframe + " since " + sinceDateStr;
            
            if (upCount > 1 || downCount > 1)
                TAcommentTXT.push(TAcommentHTMLdown.format(sincetxt.replace('dayly', 'daily').replace('$-', '-$')));
            else
                TAcommentTXT.push(TAcommentHTMLdown1st.format(sincetxt.replace('dayly', 'daily').replace('$-', '-$')));
        }

    }
    else if (returnData[returnData.length - 1][1] > 0)
    {
        var maxSince = 0;
        for (j = returnData.length - 2; j >= 0 ; j--)
        {
            var advtmp = returnData[j][1];
            if (returnData[returnData.length - 1][1] < advtmp)
            {
                maxSince = returnData.length - 1 - j;
                break;
            }
        }
        
        if (maxSince > 5)
        {
            var sinceDate = new Date(returnData[returnData.length - 1 - maxSince][0])
            var sinceDateStr = sinceDate.toISOString().slice(0,10) + " " + sinceDate.toLocaleDateString('en-US',{weekday: 'short'});
            var sincetxt = '<span style="font-weight:bold">+$' + rounding(returnData[returnData.length - 1][1] / 1000, 1000) + " bil</span> is the best net " + (timeframe == "days" ? timeframe.substring(0, timeframe.length - 2) + "i" : timeframe.substring(0, timeframe.length)) + "ly inflow in " + maxSince + " " + timeframe+ " since " + sinceDateStr;
            
            if (upCount > 1 || downCount > 1)
                TAcommentTXT.push(TAcommentHTMLup.format(sincetxt.replace('dayly', 'daily')));
            else
                TAcommentTXT.push(TAcommentHTMLup1st.format(sincetxt.replace('dayly', 'daily')));
        }
    }
    
    
    return TAcommentTXT.join("") + "</table><br>";
}

function CCASSyearlyComment(currentYear, currentYearFund, previousYear, previousYearFund)
{
    var comment = '';
    var upCount = 0;
    var downCount = 0;
    var sinceCount = 0;
    //var sinceDate0 = new Date(priceData[0][0]);
    var totalLoss;
    var totalGain;
    

    var TAcommentTXT = ['<table style="width:100%; line-height: 1.5">'];
    var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLup1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var TAcommentHTMLdown1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px; font-size:13px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    
    var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
    var TAcommentHTMLsideway1st = '<tr><td rowspan="5" style="text-align:left; vertical-align: top; padding:10px 20px 0 10px; white-space:nowrap; width:55px"><b>Analysis:</b></td><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td>{0}</td></tr>';
        
    var percentage = previousYearFund != 0 ? rounding((currentYearFund / previousYearFund - 1) * 100, 1) : null;
    var str = percentage !== null ? ('which is <span style="font-weight:bold">' + percentage + (percentage > 0? '%</span> higher than' : '%</span> lower than')) : '';
    
    if (currentYearFund > 0)
    {
        comment += currentYear + ' has a net fund inflow of <span style="font-weight:bold">$' + rounding(currentYearFund / 1000, 1000) + ' bil</span>, ' + str + ' the net fund inflow of <span style="font-weight:bold">$' + rounding(previousYearFund / 1000, 1000) +' bil</span> during the same period in ' + previousYear;
        TAcommentTXT.push(TAcommentHTMLup1st.format(comment));
    }
    else if (currentYearFund < 0)
    {
        comment += currentYear + ' has a net fund inflow of <span style="color:red;font-weight:bold">$' + rounding(currentYearFund / 1000, 1000) + ' bil</span>, ' + str + ' the net fund inflow of <span style="font-weight:bold">$' + rounding(previousYearFund / 1000, 1000) + ' bil</span> during the same period in ' + previousYear;
        TAcommentTXT.push(TAcommentHTMLdown1st.format(comment));
    }
    else
    {
        comment += currentYear + ' has a net fund inflow of <span style="font-weight:bold">$' + rounding(currentYearFund / 1000, 1000) + ' bil</span>, ' + str + ' the net fund inflow of <span style="font-weight:bold">$' + rounding(previousYearFund / 1000, 1000) + ' bil</span> during the same period in ' + previousYear;
        TAcommentTXT.push(TAcommentHTMLsideway1st.format(comment));
    }
    
    return TAcommentTXT.join("") + "</table><br>";
}

function openTOOL()
{
    var codeStr = encodeURIComponent(codeStrSQL);
    codeStr = codeStr.trim();
    if (codeStr.startsWith(','))
        codeStr = codeStr.substring(1);
    if (codeStr.endsWith(','))
        codeStr = codeStr.substring(0, codeStr.length - 1);
        
    location.href = "tool?code=" + codeStr.trim();
}

// Select all masonry items
    const items = document.querySelectorAll('.masonry-item');

    items.forEach(item => {
      item.addEventListener('mousemove', (e) => {
        // Get item dimensions and position
        const rect = item.getBoundingClientRect();
        const itemWidth = rect.width;
        const itemHeight = rect.height;
        
        // Mouse position relative to item's top-left corner
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Calculate rotation angles based on mouse position
        const maxAngle = 15; // Maximum tilt angle
        const rotateY = ((mouseX / itemWidth) - 0.5) * 2 * maxAngle; // Left-right tilt
        const rotateX = -((mouseY / itemHeight) - 0.5) * 2 * maxAngle; // Top-bottom tilt

        // Apply 3D transform with perspective
        item.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
      });

      // Reset transform to neutral state (no tilt, no scale) when mouse leaves
      item.addEventListener('mouseleave', () => {
        item.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) scale(1)';
      });
    });
    
function YearlyTrendToggle(isPercent, id, Series_Name = '')
{
    if (Series_Name == '')
        Series_Name = indexname;
    var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
    yearlyTrendChart(id, id.endsWith('1920') ? dictYearlyTrend1920 : dictYearlyTrend, seriesname, Series_Name + (id.endsWith('1920') ? '' : ' Index'), browserwidth, isPercent || id.endsWith('1920'), indexname, '', isPercent);
}

$(".showNote").click(function () {
  const $btn = $(this);
  const $note = $btn.next(".noteWrapper");

  const isCollapsed = $note.hasClass("collapsed");

  $note
    .toggleClass("collapsed", !isCollapsed)
    .toggleClass("expanded", isCollapsed);

  $btn.text(isCollapsed ? "Show less" : "Show more");
});

function CCASSmonthlyCompression(rawData)
{
    // Convert and group by YYYY-MM
    const monthlyData = {};
    
    rawData.forEach(([timestamp, value]) => {
        const date = new Date(timestamp);
        const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
        if (!monthlyData[monthKey]) {
            monthlyData[monthKey] = 0;
        }
        monthlyData[monthKey] += value;
    });
    
    // Convert grouped object to array format: [[timestamp (1st of month), sum], ...]
    const result = Object.entries(monthlyData).map(([month, sum]) => {
        const [year, monthNum] = month.split('-');
        const monthTimestamp = new Date(`${year}-${monthNum}-01T00:00:00Z`).getTime();
        return [monthTimestamp, sum];
    });
    
    // Optional: sort chronologically
    return result.sort((a, b) => a[0] - b[0]);
}

function formatToMonthYear(unixTimeMs) {
    const date = new Date(unixTimeMs);
    return date.toLocaleString('en-US', {
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC'
    });
}

function getTopBottom10Movers(dataArray) {
    // Sort ascending by y
    const sorted = [...dataArray].sort(function(a, b) {
        if (a.y === null && b.y === null) return 0;
        if (a.y === null) return 1;
        if (b.y === null) return -1;
        return a.y - b.y;
    });

    // Filter negatives and positives
    const negatives = sorted.filter(item => item.y !== null && item.y < 0);
    const positives = sorted.filter(item => item.y !== null && item.y > 0);

    // Select bottom and top 10
    const bottom10 = negatives.slice(0, 10);
    const top10 = positives.slice(-10).reverse();

    return { top10, bottom10 };
}
function getTopBottom10HighLow(dataArray) {
    // Sort ascending by y
    const sortedY = [...dataArray].sort(function(a, b) {
        if (a.y === null && b.y === null) return 0;
        if (a.y === null) return 1;
        if (b.y === null) return -1;
        return a.y - b.y;
    });
    
    const sortedX = [...dataArray].sort(function(a, b) {
        if (a.x === null && b.x === null) return 0;
        if (a.x === null) return 1;
        if (b.x === null) return -1;
        return a.x - b.x;
    });

    // Select bottom and top 10
    const bottomLow10 = sortedX.filter(item => item.x !== null).slice(0, 10);
    const topLow10 = sortedX.filter(item => item.x !== null).slice(-10).reverse();
    
    const bottomHigh10 = sortedY.filter(item => item.y !== null).slice(0, 10);
    const topHigh10 = sortedY.filter(item => item.y !== null).slice(-10).reverse();

    return { topLow10, bottomLow10, topHigh10, bottomHigh10};
}
function getTopBottom10MA(dataArray) {
    // Sort ascending by y
    const sortedY = [...dataArray].sort(function(a, b) {
        if (a.y === null && b.y === null) return 0;
        if (a.y === null) return 1;
        if (b.y === null) return -1;
        return a.y - b.y;
    });
    
    const sortedX = [...dataArray].sort(function(a, b) {
        if (a.x === null && b.x === null) return 0;
        if (a.x === null) return 1;
        if (b.x === null) return -1;
        return a.x - b.x;
    });

    // Select bottom and top 10
    const bottomLow10 = sortedX.filter(item => item.x !== null && item.y < 0).slice(0, 10);
    const topLow10 = sortedX.filter(item => item.x !== null && item.y > 0).slice(-10).reverse();
    
    const bottomHigh10 = sortedY.filter(item => item.y !== null && item.y < 0).slice(0, 10);
    const topHigh10 = sortedY.filter(item => item.y !== null && item.y > 0).slice(-10).reverse();

    return { topLow10, bottomLow10, topHigh10, bottomHigh10};
}
function renderMovers(containerId, movers, subtitle, exchange, daysStr) {
    if (!containerId || !movers) return;
    var timeframe = daysStr.replace(" Performance", "");
    const timeframeNoSpaces = timeframe.replace(/\s/g, ''); 
    var cs = [];
    var chartcount = 0;
    
    let html = `
        <div style="text-align:center; margin:15px 0 10px 0;font-size:12px">
            ${addWeekday(subtitle, daysStr)}
        </div>
        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px;">
            <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                <h4 style="text-align:center;color:#444444">Top Gainers (${timeframe})</h4>
    `;

    if (movers.top10 && movers.top10.length > 0) {
        html += "<table border='0' cellspacing='0' cellpadding='4'>";
        movers.top10.forEach(item => {
            const yVal = item.y !== null ? parseFloat(item.y).toFixed(1) : "N/A";
            // Replace only the first occurrence at the start
            let nameHtml = item.name.startsWith(item.code) ?
                item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                item.name;

            html += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${timeframeNoSpaces}${chartcount}"></span></a></td><td style="color:green;font-weight:550">+${yVal}%</td></tr>`;
            
            var closeSeries = item.s;
            closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
            cs.push(closeSeries.split(","));
            
            chartcount++;
        });
        html += "</table>";
    } else {
        html += "<p>No positive movers found.</p>";
    }

    html += `
            </div>
            <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                <h4 style="text-align:center;color:#444444">Top Losers (${timeframe})</h4>
    `;

    if (movers.bottom10 && movers.bottom10.length > 0) {
        html += "<table border='0' cellspacing='0' cellpadding='4'>";
        movers.bottom10.forEach(item => {
            const yVal = item.y !== null ? parseFloat(item.y).toFixed(1) : "N/A";
            // Replace only the first occurrence at the start
            let nameHtml = item.name.startsWith(item.code) ?
                item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                item.name;

            html += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${timeframeNoSpaces}${chartcount}"></span></a></td><td style="color:red;font-weight:550">${yVal}%</td></tr>`;
            
            var closeSeries = item.s;
            closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
            cs.push(closeSeries.split(","));
            
            chartcount++;
        });
        html += "</table>";
    } else {
        html += "<p>No negative movers found.</p>";
    }

    html += `
            </div>
        </div>
    `;

    const container = document.getElementById(containerId);
    if (container) container.innerHTML = html;
    
    for(var i = 0; i < cs.length; i++)
    {
        if (cs[i] !== null && cs[i] != undefined && cs[i].length > 0)
        {
            var cs_number = cs[i].map(Number);
            if (cs_number !== null && cs_number != undefined && cs_number.length > 0)
            {
                var aaa =`#sparkline${timeframeNoSpaces}${i}`;
                $(`#sparkline${exchange}${timeframeNoSpaces}${i}`).sparkline(cs_number, { type: 'line', spotRadius: 0, disableInteraction: true, height: '15'});
            }
        }
    }
}

function renderMoversXY(containerId, movers, subtitle, exchange, daysStr, type = '') {
    if (!containerId || !movers) return;
    var cs = [];
    var chartcount = 0;

    var t1 = 'Nearest to 250-Day High (%)';
    var t2 = 'Most Gain from 250-Day Low (%)';
    var t3 = 'Nearest to 250-Day Low (%)';
    var t4 = 'Most Loss from 250-Day High (%)';
    var t5 = 'No 250-Day Highs found.';
    var t6 = 'No 250-Day Highs found.';
    var t7 = 'No 250-Day Highs found.';
    var t8 = 'No 250-Day Highs found.';
    var pre_sign = '+';
    var pre_sign2 = '';
    var post_sign = '%';
    var colorRed = '';
    var colorGreen = '';
    var sparktype = type;
    if (sparktype === '')
        sparktype = 'HL';
        
    if (type == 'RSI')
    {
        t1 = 'Strongest Weekly RSI';
        t2 = 'Strongest Daily RSI';
        t3 = 'Weakest Weekly RSI';
        t4 = 'Weakest Daily RSI';
        t5 = 'No Weekly RSI found';
        t6 = 'No Daily RSI found';
        pre_sign = '';
        post_sign = '';
    }
    else if (type == 'MA200' || type == 'MA250')
    {
        t1 = 'Most Gain above ' + type + ' (%)';
        t2 = 'Most Gain above MA50 (%)';
        t3 = 'Most Loss below ' + type + ' (%)';
        t4 = 'Most Loss below MA50 (%)';
        t5 = 'No '+type+' found';
        t6 = 'No MA50 found';
        pre_sign = '';
        pre_sign2 = '+';
        post_sign = '%';
        colorRed = 'color:red';
        colorGreen = 'color:green';
    }
    
    
    let html = `
        <div style="text-align:center; margin:15px 0 10px 0;font-size:12px">
            ${addWeekday(subtitle, daysStr)}
        </div>
        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px;">
            <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                <h4 style="text-align:center;color:#444444">${t1}</h4>
    `;

    if (movers.topHigh10 && movers.topHigh10.length > 0) {
        html += "<table border='0' cellspacing='0' cellpadding='4'>";
        movers.topHigh10.forEach(item => {
            const yVal = item.y !== null ? parseFloat(item.y).toFixed(1) : "N/A";
            // Replace only the first occurrence at the start
            let nameHtml = item.name.startsWith(item.code) ?
                item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                item.name;

            html += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550;${colorGreen}">${pre_sign2}${yVal}${post_sign}</td></tr>`;
            
            var closeSeries = item.s;
            closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
            cs.push(closeSeries.split(","));
            
            chartcount++;
        });
        html += "</table>";
    } else {
        html += `<p>${t5}</p>`;
    }

    html += `
            </div>
            <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                <h4 style="text-align:center;color:#444444">${t2}</h4>
    `;

    if (movers.topLow10 && movers.topLow10.length > 0) {
        html += "<table border='0' cellspacing='0' cellpadding='4'>";
        movers.topLow10.forEach(item => {
            const xVal = item.x !== null ? parseFloat(item.x).toFixed(1) : "N/A";
            // Replace only the first occurrence at the start
            let nameHtml = item.name.startsWith(item.code) ?
                item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                item.name;

            html += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550;${colorGreen}">${pre_sign}${pre_sign2}${xVal}${post_sign}</td></tr>`;

            var closeSeries = item.s;
            closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
            cs.push(closeSeries.split(","));
            
            chartcount++;
        });
        html += "</table>";
    } else {
        html += `<p>${t6}</p>`;
    }

    html += `
            </div>
        </div>
    `;
    
    let html2 = `
        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px;">
            <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                <h4 style="text-align:center;color:#444444">${t3}</h4>
    `;
    
    if (type !== '')
    {
        if (movers.bottomHigh10 && movers.bottomHigh10.length > 0) {
            html2 += "<table border='0' cellspacing='0' cellpadding='4'>";
            movers.bottomHigh10.forEach(item => {
                const yVal = item.y !== null ? parseFloat(item.y).toFixed(1) : "N/A";
                // Replace only the first occurrence at the start
                let nameHtml = item.name.startsWith(item.code) ?
                    item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                    item.name;
    
                html2 += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550;${colorRed}">${yVal}${post_sign}</td></tr>`;
                
                var closeSeries = item.s;
                closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
                cs.push(closeSeries.split(","));
                
                chartcount++;
            });
            html2 += "</table>";
        } else {
            html2 += `<p>${t6}</p>`;
        }
    
        html2 += `
                </div>
                <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                    <h4 style="text-align:center;color:#444444">${t4}</h4>
        `;
    
        if (movers.bottomLow10 && movers.bottomLow10.length > 0) {
            html2 += "<table border='0' cellspacing='0' cellpadding='4'>";
            movers.bottomLow10.forEach(item => {
                const xVal = item.x !== null ? parseFloat(item.x).toFixed(1) : "N/A";
                // Replace only the first occurrence at the start
                let nameHtml = item.name.startsWith(item.code) ?
                    item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                    item.name;
    
                html2 += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550;${colorRed}">${pre_sign}${xVal}${post_sign}</td></tr>`;
                
                var closeSeries = item.s;
                closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
                cs.push(closeSeries.split(","));
                
                chartcount++;
            });
            html2 += "</table>";
        } else {
            html2 += `<p>${t5}</p>`;
        }
        
        html2 += `
                </div>
            </div>
        `;
    }
    else
    {
        if (movers.bottomLow10 && movers.bottomLow10.length > 0) {
            html2 += "<table border='0' cellspacing='0' cellpadding='4'>";
            movers.bottomLow10.forEach(item => {
                const xVal = item.x !== null ? parseFloat(item.x).toFixed(1) : "N/A";
                // Replace only the first occurrence at the start
                let nameHtml = item.name.startsWith(item.code) ?
                    item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                    item.name;
    
                html2 += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550">${pre_sign}${xVal}${post_sign}</td></tr>`;
                
                var closeSeries = item.s;
                closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
                cs.push(closeSeries.split(","));
                
                chartcount++;
            });
            html2 += "</table>";
        } else {
            html2 += `<p>${t5}</p>`;
        }
    
        html2 += `
                </div>
                <div style="flex:1 1 300px; min-width:300px; max-width:550px; margin:0px 20px 20px 20px;">
                    <h4 style="text-align:center;color:#444444">${t4}</h4>
        `;
    
        if (movers.bottomHigh10 && movers.bottomHigh10.length > 0) {
            html2 += "<table border='0' cellspacing='0' cellpadding='4'>";
            movers.bottomHigh10.forEach(item => {
                const yVal = item.y !== null ? parseFloat(item.y).toFixed(1) : "N/A";
                // Replace only the first occurrence at the start
                let nameHtml = item.name.startsWith(item.code) ?
                    item.name.replace(new RegExp("^" + item.code), `<a href="stockinfo?code=${item.code}" target="_blank" style="font-weight:500">${item.code}</a>`) :
                    item.name;
    
                html2 += `<tr><td>${nameHtml}</td><td><a href="stockinfo?code=${item.code}" target="_blank"><span style="cursor:pointer" title="6-month chart" id="sparkline${exchange}${sparktype}${chartcount}"></span></a></td><td style="font-weight:550">${yVal}${post_sign}</td></tr>`;
                
                var closeSeries = item.s;
                closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
                cs.push(closeSeries.split(","));
                
                chartcount++;
            });
            html2 += "</table>";
        } else {
            html2 += `<p>${t6}</p>`;
        }
        
        html2 += `
                </div>
            </div>
        `;
    }

    const container = document.getElementById(containerId);
    if (container) container.innerHTML = html + html2;
    
    for(var i = 0; i < cs.length; i++)
    {
        if (cs[i] !== null && cs[i] != undefined && cs[i].length > 0)
        {
            var cs_number = cs[i].map(Number);
            if (cs_number !== null && cs_number != undefined && cs_number.length > 0)
                $(`#sparkline${exchange}${sparktype}${i}`).sparkline(cs_number, { type: 'line', spotRadius: 0, disableInteraction: true, height: '15'});
        }
    }
}

function addWeekday(str, str2 = '') {
    // Split the string by comma
    const parts = str.split(',');
    if (parts.length < 2) return str;

    const exchange = parts[0].trim();
    const dateStr = parts[1].trim();

    const date = new Date(dateStr);
    if (isNaN(date)) return str; // fallback if invalid date

    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const dayAbbr = weekdays[date.getUTCDay()]; // Use getUTCDay for consistent UTC interpretation

    if (str2 === '')
        return `${exchange}, ${dateStr} ${dayAbbr}`;
        
    return `${exchange}, ${str2}, ${dateStr} ${dayAbbr}`;
}
</script>

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
    <script src="assets/js/Utils.js"></script>
    <script src="assets/js/ValuationBands.js"></script>
    <!--<script src="assets/js/yaxis-panning.js"></script>-->
</body>

</html>
