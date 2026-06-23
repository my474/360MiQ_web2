<?php
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
        '/(<div id="(bubblecontainer|tabubblecontainer)[A-Z]([123])" style="width:auto;height:(?:600|700)px;"><\/div>)/',
        function ($matches) {
            $type = ["1" => "performance1", "2" => "performance5", "3" => "performance20"][$matches[3]];
            if ($matches[2] === "tabubblecontainer") {
                $type = ["1" => "highlow", "2" => "ma", "3" => "rsi"][$matches[3]];
            }

            return $matches[1] . scatterChartNoteMarkup($type);
        },
        $html
    );
});
?>
<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="360MiQ.com - Free Stock Market Insights & Analytics" />
    <meta name="description" content="360MiQ.com offers free global stock market insights & analytics in a simple and straightforward way for every investor." />
    <meta property="og:description" content="360MiQ.com offers free global stock market insights & analytics in a simple and straightforward way for every investor." />

    <title>360MiQ.com - Free Stock Market Insights &amp; Analytics</title>
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
    <script src="assets/js/Utils.js"></script>
</head>

<body><style>
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
  
a.recentpost {
    color:#000;
    font-size:14px;
}

a:hover.recentpost {
    color:#20A4E8;
    text-decoration: none;
}

.chartNote {
  text-align: left;
  margin: 0 5% 20px;
}

.noteWrapper {
  overflow: hidden;
  position: relative;
  max-height: 43px;
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
  max-height: 500px;
}

.noteWrapper.expanded::after {
  display: none;
}

.showNote {
  font-size: 14px;
  font-weight: 550;
  cursor: pointer;
  margin: 6px 0 12px;
  user-select: none;
  color: #007bff;
}
[data-theme="dark"] a.recentpost {
  color: #fff;
}

[data-theme="dark"] a.recentpost:hover {
  color: #6ea8ff;
}
[data-theme="dark"] div[id^="featuredposttitle"] a.recentpost {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
</style>
<style>
    #news-bar {
      top: 0;
      width: 100%;
      background: #fff;
      overflow: hidden;

      padding: 0 10px 0 10px;
      font-size: 16px;
      z-index: 999;
    }
    [data-theme="dark"] #news-bar {
      background: #1e1e32;
    }
    [data-theme="dark"] #news-scroll {
      color: #cccccc;
    }

    #news-scroll {
      display: inline-block;
      white-space: nowrap;
      padding-left: 67%; /* this adds the starting empty space */
      animation: scroll-left 180s linear infinite;
      animation-play-state: running;
    }

    #news-bar:hover #news-scroll,
    #news-bar.paused #news-scroll {
      animation-play-state: paused !important;
    }

    @keyframes scroll-left {
      from { transform: translateX(0%); }
      to { transform: translateX(-100%); }
    }

    .code-link {
      color: #007bff;
      margin: 0px;
      text-decoration: none;
      cursor: pointer;
      white-space: nowrap;
    }
    .code-link:hover {
        color: 0056b3;
        text-decoration: underline;
    }
    .code-link2:hover {
        color: #0056b3;
        text-decoration: underline;
    }
    .code-link2, .s-link {
        cursor: pointer;
    }
    .code-link2 {
        color: #007bff;
        text-decoration: none;
        background-color: transparent;
    }

    .title-link {
      margin-right: 50px;
      cursor: pointer;
      white-space: nowrap;
    }

    .title-link:hover {
      text-decoration: underline !important;
    }

    #modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1031;
      overflow: auto; /* allow overlay scroll */
    }

    #modal {
      position: relative;
      margin: 170px auto;
      max-width: 600px;
      max-height: 81vh; /* limit modal height */
      background: #fff;
      color: #292929;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0,0,0,0.4);
      padding: 20px;
      overflow-y: auto; /* scroll inside modal */
      box-sizing: border-box;
      text-align: left;
    }
    [data-theme="dark"] #modal {
      background: #1e1e32;
      color: #e8e8e8;
    }

    @media (max-width: 600px) {
      #modal {
        width: 90%;
        margin: 86px auto 20px auto;
        max-height: 76.2vh;
      }
    }
    .close {
      position: absolute;
      top: 8px;
      right: 12px;
      font-size: 24px;
      font-weight: bold;
      color: #888;
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }

    [data-theme="dark"] .close {
      color: #aaa;
    }
    [data-theme="dark"] .close:hover {
      color: #e8e8e8;
    }

    #modal-title {
      margin-top: 0;
      margin-right: 15px;
      text-align: left;
    }
    #modal-content {
      font-size: 1.0em;
      margin-bottom: 0;
      color: #000;
    }
    [data-theme="dark"] #modal-content {
      color: #e8e8e8;
    }
    #modal-date {
      font-size: 0.9em;
      color: #666;
      margin-bottom: 8px;
      text-align: left;
    }
    [data-theme="dark"] #modal-date {
      color: #999999;
    }
  </style>
  <style>
    .tooltip-bubble {
      position: absolute;
      background: #333;
      color: #fff;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 14px;
      white-space: nowrap;
      z-index: 1031;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s ease-in-out;
    }
  </style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="assets/js/jquery.sparkline.min.js"></script>
<script>
var homeCriticalRequests = {
    trendGauge: $.ajax({
        url: "/db_market_trendgauge_get.php",
        dataType: "text"
    }),
    indices: $.ajax({
        url: "db_index_get.php",
        dataType: "text"
    }),
    news: $.ajax({
        url: "db_news_get.php",
        method: "GET"
    })
};

var homeAjaxQueue = [];
var homeAjaxQueueRunning = false;

function scheduleHomeAjax(options)
{
    homeAjaxQueue.push(options);

    if (homeAjaxQueueRunning)
        return;

    homeAjaxQueueRunning = true;

    function startNextHomeRequest()
    {
        if (homeAjaxQueue.length === 0)
        {
            homeAjaxQueueRunning = false;
            return;
        }

        var startRequest = function() {
            $.ajax(homeAjaxQueue.shift());
            window.setTimeout(startNextHomeRequest, 250);
        };

        if ('requestIdleCallback' in window)
            window.requestIdleCallback(startRequest, { timeout: 1500 });
        else
            window.setTimeout(startRequest, 500);
    }

    startNextHomeRequest();
}
</script>

<?php $page = 'home'; include "./header.php" ?>

    <main class="page">
    <section class="clean-block about-us" style="padding:0">
        <!--h1 style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;"-->
        <h1 class="sr-only">All-in-One Stock Market Analytics Dashboard</h1>
    <div class="container" style="padding:30px 0 0;">
    <!--div class="block-heading">
        <h2 class="text-info">About Us</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc quam urna, dignissim nec auctor in, mattis vitae leo.</p>
        <a href="#"><img src = "assets/img/Ad_h.png" widht = "970" height = "90"/></a>
    </div-->
    <div id="newscard" class="card clean-card text-center" style="margin:70px 0 16px 0;float:left; width: 100%;">
        <!--div id="stockname" style="padding:8px; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div-->
        <div style="padding:8px; font-size: 14px;">
            <div id="news-bar"><div id="news-scroll">Loading news...</div></div>
        </div>

        <div id="modal-overlay">
          <div id="modal">
            <span class="close">&times;</span>
            <h6 id="modal-title" style="font-weight:bold"></h6>
            <p id="modal-date"></p>
            <p id="modal-content"></p>
          </div>
        </div>

    </div>
    <div id="maincard" class="row justify-content-center" style="margin:0;padding-right:0.1px;padding-left:0px"> <!-- layout goes haywire if both padding-left & right = 0, and without margin:0. Only happens after News feature added -->
        <div class="col-md-12 table-condensed cf">
<!--<div id="adblockerMSG" class="container">
  Our website is made possible by displaying online advertisements to our visitors.<br>
  Please consider supporting us by disabling your ad blocker.
</div>
<style>
#adblockerMSG {
display: none;
margin-bottom: 22px;
margin-top: 1px;
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
</script>            -->
<!--<div id="wrapper">
<div id="gaugecontainer1" style="float:left; width: 50%; "></div>
<div id="gaugecontainer2" style="float:right; width: 50%; "></div>
<div style="clear:both"></div>
</div> -->
<div id="wrapper" class = "card clean-card text-center container" style="padding:0">
    <div class="card-header">
        <h2>Stock Market Trend</h2>
    </div>
    <div>
        <h3 class="sr-only">Stock Market Trend for NYSE, Nasdaq, London, Toronto TSX, India NSE, Australia, Tokyo, Hong Kong</h3>
        <div class="col-sm-6 col-lg-4 MarketGaugeLeft" style="float:left; height: 480px; ">
            <div class="not-selectable" id="gaugecontainer1" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer2" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer5" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer6" style="float:left; height: 240px; width: 50%; "></div>
            <div style="clear:both"></div>
        </div>
        <div class="col-sm-6 col-lg-4 MarketGaugeRight" style="float:left; height: 480px; ">
            <div class="not-selectable" id="gaugecontainer3" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer4" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer7" style="float:left; height: 240px; width: 50%; "></div>
            <div class="not-selectable" id="gaugecontainer8" style="float:left; height: 240px; width: 50%; "></div>
            <div style="clear:both"></div>
        </div>
        
        <div style="clear:both"></div>
        <div style="float:left; width: 100%; font-size:11px; text-align:left; padding:0px 0px 5px 10px">The&nbsp;<span style="color:#777777;font-weight:600;font-style:italic">Dark grey needle</span>&nbsp;is the whole market trend based on market breadth. The&nbsp;<span style="color:#79ABE0;font-weight:600;font-style:italic">Blue needle</span>&nbsp;is the major stock index trend.</div><div style="float:left; width: 100%; font-size:11px; text-align:left; padding:0px 0px 5px 10px">If the smaller angle of the two needles is greater than 90&#176;, that indicates both trends are not well aligned.</div>
    </div>
</div>

<p></p>
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
</style>
<div id="WEI" class = "card clean-card text-center container" style="padding:0">
    <div class="card-header">
        <h2>World Equity Indices</h2>
    </div>
    <div>
        <h3 class="sr-only">World Equity Indices: SPX, Nas Composite, DJI, DJT, UK 100, Toronto Composite, Australia 200, India 50, Japan 225, Hong Kong, Shanghai Composite, Bitcoin</h3>
        <div id="weicontainer" style="height: 600px; min-width: 100%; margin: 5px auto 0"></div>
    </div>
</div>

<p></p>
<div id="minimal-tabs" class = "card clean-card text-center container" style="padding:0">
    <div class="card-header">
        <h2>Sector Performance</h2>
    </div>
    <ul class="nav nav-tabs not-selectable" style="padding: 5px 0px 0px 5px">
        <li class="nav-item"><a class="nav-link nl active" role="tab" data-toggle="tab" href="#tab-1" title="NYSE">NYSE</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-2" title="Nasdaq">NAS</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-3" title="London">LSE</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-4" title="Toronto TSX">TSX</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-5" title="Australia">ASX</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-6" title="India NSE">NSE</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-7" title="Tokyo">TYO</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-8" title="Hong Kong">HK</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-9" title="Shanghai">SH</a></li>
        <li class="nav-item"><a class="nav-link nl" role="tab" data-toggle="tab" href="#tab-10" title="Shenzhen">SZ</a></li>
    </ul>
    <div class="tab-content not-selectable">
        <div class="tab-pane active" role="tabpanel" id="tab-1">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1NYSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2NYSE" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3NYSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4NYSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">NYSE Sector Performance</h3>
            <div id="barcontainer1" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab-2">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1Nasdaq" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2Nasdaq" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3Nasdaq" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4Nasdaq" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Nasdaq Sector Performance</h3>
            <div id="barcontainer2" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab-3">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1London" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2London" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3London" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4London" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">London Sector Performance</h3>
            <div id="barcontainer3" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
      
        <div class="tab-pane" role="tabpanel" id="tab-4">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1TorontoTSX" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2TorontoTSX" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3TorontoTSX" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4TorontoTSX" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Toronto TSX Sector Performance</h3>
            <div id="barcontainer4" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
      
        <div class="tab-pane" role="tabpanel" id="tab-5">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1Australia" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2Australia" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3Australia" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4Australia" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Australia Sector Performance</h3>
            <div id="barcontainer5" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
      
        <div class="tab-pane" role="tabpanel" id="tab-6">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1IndiaNSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2IndiaNSE" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3IndiaNSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4IndiaNSE" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">India NSE Sector Performance</h3>
            <div id="barcontainer6" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
        
        <div class="tab-pane" role="tabpanel" id="tab-7">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1Tokyo" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2Tokyo" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3Tokyo" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4Tokyo" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Tokyo Sector Performance</h3>
            <div id="barcontainer7" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
      
        <div class="tab-pane" role="tabpanel" id="tab-8">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1HongKong" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2HongKong" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3HongKong" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4HongKong" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Hong Kong Sector Performance</h3>
            <div id="barcontainer8" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
        
        <div class="tab-pane" role="tabpanel" id="tab-9">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1Shanghai" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2Shanghai" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3Shanghai" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4Shanghai" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Shanghai Sector Performance</h3>
            <div id="barcontainer9" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
        
        <div class="tab-pane" role="tabpanel" id="tab-10">
            <span class="label" style="padding: .25rem .4rem; font-size: .875rem; line-height: .5; color:grey">Sort:</span>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <button id="sort1Shenzhen" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">A - Z</button>
            <button id="sort2Shenzhen" type="radio" class="btn btn-xs btn-outline-secondary active" onclick="this.blur();">1 Day</button>
            <button id="sort3Shenzhen" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">5 Days</button>
            <button id="sort4Shenzhen" type="radio" class="btn btn-xs btn-outline-secondary" onclick="this.blur();">20 Days</button>
            </div>
            <h3 class="sr-only">Shenzhen Sector Performance</h3>
            <div id="barcontainer10" style="height: 400px; min-width: 100%; margin: 0 auto"></div>
        </div>
        
    </div>
</div>
<style>
.btns {
    float: right;
}
</style>
            
<p></p>
        <!--<script type="text/javascript" src="/moboxAndLabelData.json"></script>-->
        <!--style type="text/css">


            #content { margin-right: auto; margin-left: auto; width: 100%; }
            canvas { border: 0px solid #f00; }
            #treemap { display: inline-block; }
            .highlighter { border: 2px solid #ff0; position: absolute; display: none; pointer-events: none; }
            .mouseoverbox {
                -moz-border-radius-topright: 5px;
                -moz-border-radius-topleft: 5px;
                -moz-border-radius-bottomright: 5px;
                -moz-border-radius-bottomleft: 5px;
                -webkit-border-top-right-radius: 5px;
                -webkit-border-top-left-radius: 5px;
                -webkit-border-bottom-right-radius: 5px;
                -webkit-border-bottom-left-radius: 5px;
                border: 1px solid #ccc;
                position: absolute;
                background-color: #fff;
                pointer-events: none;
                display: none;
            }
            [data-theme="dark"] .mouseoverbox {
              background-color: #1e1e32;
              border: 1px solid #3a3a4e;
            }
            .mouseoverbox p {
                margin: 0px 2px 0px 2px;
                padding: 0px 2px 0px 2px;
                font-size: 0.8em;
                color: #444;
            }
            [data-theme="dark"] .mouseoverbox p {
              color: #c0c0c0;
            }
            .codeDiv {
                border: 1px solid #aaa;
            }
        </style-->

        <div id="content" class="clean-card text-center container" style="padding:0;display: none;">
            <div class="card-header">
                <h2>Market Heatmap & Scatter</h2>
            </div>
            <div id="bodyContent">
                <ul class="nav nav-tabs not-selectable" style="padding: 5px 0px 0px 5px">
                    <li class="nav-item"><a class="nav-link mynav-link nl active" role="tab" data-toggle="tab" href="#treetab-1" title="NYSE">NYSE</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-2" title="Nasdaq">NAS</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-3" title="London">LSE</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-4" title="Toronto TSX">TSX</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-5" title="Australia">ASX</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-6" title="India NSE">NSE</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-7" title="Tokyo">TYO</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-8" title="Hong Kong">HK</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-9" title="Shanghai">SH</a></li>
                    <li class="nav-item"><a class="nav-link mynav-link nl" role="tab" data-toggle="tab" href="#treetab-10" title="Shenzhen">SZ</a></li>
                </ul>
                <div class="tab-content not-selectable">
                    <!--<div class="tab-pane active" role="tabpanel" id="treetab-1">
                        <div class="not-selectable" id="example1" onmouseover="" style="cursor: pointer;"><font size = "4px">
                        <div><span id="treemap"><canvas width="100%" ></canvas></span></div>
                        <div id="highlighter" style="display: none;"><div id="highlighter0" class="highlighter" style="display: inline; left: 879px; top: 567.352px; width: 114px; height: 223px;"></div><div id="highlighter1" class="highlighter" style="display: none; left: 879px; top: 567.352px; width: 114px; height: 223px;"></div><div id="highlighter2" class="highlighter" style="display: none; left: 731px; top: 567.352px; width: 147px; height: 223px;"></div></div>
                        <div id="mouseoverbox" class="mouseoverbox" style="background-color: rgba(255, 255, 200, 0.85); left: 819px; top: 575px; display: none;"><span><p><b>group B</b></p><p>x = <b>0.702835113889</b></p><p>y = <b>2243</b></p><p>z = <b>60.5670829936</b></p></span></div></font></div>
                    </div>-->
                    
                    <div class="tab-pane active" role="tabpanel" id="treetab-1">
                        <div id="maptabA" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabA-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabA-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabA-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabA-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverA1"></div>
                                        <h3 class="sr-only">NYSE Heatmap (1-Day)</h3>
                                        <div id="chartcontainerA1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">NYSE Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerA1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabA-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverA2"></div>
                                        <h3 class="sr-only">NYSE Heatmap (5-Day)</h3>
                                        <div id="chartcontainerA2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">NYSE Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerA2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabA-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverA3"></div>
                                        <h3 class="sr-only">NYSE Heatmap (20-Day)</h3>
                                        <div id="chartcontainerA3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">NYSE Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerA3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">

                        <div id="tatabA" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabA-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabA-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabA-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabA-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">NYSE Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerA1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowA1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabA-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">NYSE Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerA2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongA2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabA-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">NYSE Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerA3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIA3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="tab-pane" role="tabpanel" id="treetab-2">
                        <div id="maptabB" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabB-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabB-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabB-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabB-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverB1"></div>
                                        <h3 class="sr-only">Nasdaq Heatmap (1-Day)</h3>
                                        <div id="chartcontainerB1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Nasdaq Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerB1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabB-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverB2"></div>
                                        <h3 class="sr-only">Nasdaq Heatmap (5-Day)</h3>
                                        <div id="chartcontainerB2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Nasdaq Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerB2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabB-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverB3"></div>
                                        <h3 class="sr-only">Nasdaq Heatmap (20-Day)</h3>
                                        <div id="chartcontainerB3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Nasdaq Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerB3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabB" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabB-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabB-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabB-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabB-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Nasdaq Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerB1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowB1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabB-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Nasdaq Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerB2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongB2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabB-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Nasdaq Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerB3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIB3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
            
                    <div class="tab-pane" role="tabpanel" id="treetab-3">
                        <div id="maptabC" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabC-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabC-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabC-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabC-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverC1"></div>
                                        <h3 class="sr-only">London Heatmap (1-Day)</h3>
                                        <div id="chartcontainerC1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">London Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerC1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabC-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverC2"></div>
                                        <h3 class="sr-only">London Heatmap (5-Day)</h3>
                                        <div id="chartcontainerC2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">London Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerC2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabC-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverC3"></div>
                                        <h3 class="sr-only">London Heatmap (20-Day)</h3>
                                        <div id="chartcontainerC3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">London Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerC3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabC" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabC-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabC-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabC-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabC-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">London Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerC1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowC1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabC-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">London Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerC2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongC2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabC-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">London Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerC3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIC3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  
                    <div class="tab-pane" role="tabpanel" id="treetab-4">
                        <div id="maptabD" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabD-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabD-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabD-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabD-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverD1"></div>
                                        <h3 class="sr-only">Toronto TSX Heatmap (1-Day)</h3>
                                        <div id="chartcontainerD1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Toronto TSX Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerD1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabD-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverD2"></div>
                                        <h3 class="sr-only">Toronto TSX Heatmap (5-Day)</h3>
                                        <div id="chartcontainerD2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Toronto TSX Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerD2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabD-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverD3"></div>
                                        <h3 class="sr-only">Toronto TSX Heatmap (20-Day)</h3>
                                        <div id="chartcontainerD3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Toronto TSX Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerD3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabD" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabD-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabD-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabD-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabD-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Toronto TSX Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerD1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowD1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabD-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Toronto TSX Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerD2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongD2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabD-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Toronto TSX Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerD3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSID3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  
                    <div class="tab-pane" role="tabpanel" id="treetab-5">
                        <div id="maptabE" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabE-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabE-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabE-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabE-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverE1"></div>
                                        <h3 class="sr-only">Australia Heatmap (1-Day)</h3>
                                        <div id="chartcontainerE1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Australia Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerE1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabE-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverE2"></div>
                                        <h3 class="sr-only">Australia Heatmap (5-Day)</h3>
                                        <div id="chartcontainerE2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Australia Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerE2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabE-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverE3"></div>
                                        <h3 class="sr-only">Australia Heatmap (20-Day)</h3>
                                        <div id="chartcontainerE3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Australia Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerE3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabE" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabE-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabE-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabE-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabE-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Australia Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerE1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowE1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabE-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Australia Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerE2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongE2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabE-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Australia Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerE3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIE3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  
                    <div class="tab-pane" role="tabpanel" id="treetab-6">
                        <div id="maptabF" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabF-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabF-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabF-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabF-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverF1"></div>
                                        <h3 class="sr-only">India NSE Heatmap (1-Day)</h3>
                                        <div id="chartcontainerF1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">India NSE Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerF1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabF-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverF2"></div>
                                        <h3 class="sr-only">India NSE Heatmap (5-Day)</h3>
                                        <div id="chartcontainerF2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">India NSE Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerF2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabF-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverF3"></div>
                                        <h3 class="sr-only">India NSE Heatmap (20-Day)</h3>
                                        <div id="chartcontainerF3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">India NSE Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerF3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabF" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabF-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabF-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabF-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabF-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">India NSE Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerF1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowF1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabF-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">India NSE Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerF2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongF2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabF-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">India NSE Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerF3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIF3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  
                    <div class="tab-pane" role="tabpanel" id="treetab-7">
                        <div id="maptabG" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabG-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabG-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabG-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabG-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverG1"></div>
                                        <h3 class="sr-only">Tokyo Heatmap (1-Day)</h3>
                                        <div id="chartcontainerG1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Tokyo Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerG1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabG-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverG2"></div>
                                        <h3 class="sr-only">Tokyo Heatmap (5-Day)</h3>
                                        <div id="chartcontainerG2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Tokyo Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerG2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabG-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverG3"></div>
                                        <h3 class="sr-only">Tokyo Heatmap (20-Day)</h3>
                                        <div id="chartcontainerG3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Tokyo Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerG3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabG" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabG-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabG-2">MA200 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabG-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabG-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Tokyo Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerG1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowG1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabG-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Tokyo Deviation % From MA200 vs MA50</h3>
                                        <div id="tabubblecontainerG2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongG2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabG-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Tokyo Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerG3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIG3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                  
                    <div class="tab-pane" role="tabpanel" id="treetab-8">
                        <div id="maptabH" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabH-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabH-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabH-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabH-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverH1"></div>
                                        <h3 class="sr-only">Hong Kong Heatmap (1-Day)</h3>
                                        <div id="chartcontainerH1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Hong Kong Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerH1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabH-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverH2"></div>
                                        <h3 class="sr-only">Hong Kong Heatmap (5-Day)</h3>
                                        <div id="chartcontainerH2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Hong Kong Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerH2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabH-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverH3"></div>
                                        <h3 class="sr-only">Hong Kong Heatmap (20-Day)</h3>
                                        <div id="chartcontainerH3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Hong Kong Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerH3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabH" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabH-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabH-2">MA250 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabH-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabH-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Hong Kong Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerH1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowH1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabH-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Hong Kong Deviation % From MA250 vs MA50</h3>
                                        <div id="tabubblecontainerH2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongH2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabH-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Hong Kong Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerH3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIH3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="tab-pane" role="tabpanel" id="treetab-9">
                        <div id="maptabI" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabI-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabI-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabI-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabI-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverI1"></div>
                                        <h3 class="sr-only">Shanghai Heatmap (1-Day)</h3>
                                        <div id="chartcontainerI1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shanghai Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerI1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabI-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverI2"></div>
                                        <h3 class="sr-only">Shanghai Heatmap (5-Day)</h3>
                                        <div id="chartcontainerI2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shanghai Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerI2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabI-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverI3"></div>
                                        <h3 class="sr-only">Shanghai Heatmap (20-Day)</h3>
                                        <div id="chartcontainerI3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shanghai Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerI3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabI" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabI-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabI-2">MA250 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabI-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabI-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shanghai Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerI1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowI1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabI-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shanghai Deviation % From MA250 vs MA50</h3>
                                        <div id="tabubblecontainerI2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongI2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabI-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shanghai Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerI3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSII3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="tab-pane" role="tabpanel" id="treetab-10">
                        <div id="maptabJ" class="panel panel-default" style="margin: 5.5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#maptabJ-1">1 Day</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabJ-2">5 Days</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#maptabJ-3">20 Days</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="maptabJ-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverJ1"></div>
                                        <h3 class="sr-only">Shenzhen Heatmap (1-Day)</h3>
                                        <div id="chartcontainerJ1" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shenzhen Stock Performance vs Relative Volume (1-Day)</h3>
                                        <div id="bubblecontainerJ1" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="maptabJ-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverJ2"></div>
                                        <h3 class="sr-only">Shenzhen Heatmap (5-Day)</h3>
                                        <div id="chartcontainerJ2" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shenzhen Stock Performance vs Relative Volume (5-Day)</h3>
                                        <div id="bubblecontainerJ2" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
    
                                <div class="tab-pane" role="tabpanel" id="maptabJ-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <div class="selectable" id="moverJ3"></div>
                                        <h3 class="sr-only">Shenzhen Heatmap (20-Day)</h3>
                                        <div id="chartcontainerJ3" style="width:auto;height:600px;"></div>
                                        <hr style="margin:36px 5% 20px 5%;">
                                        <h3 class="sr-only">Shenzhen Stock Performance vs Relative Volume (20-Day)</h3>
                                        <div id="bubblecontainerJ3" style="width:auto;height:600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;margin: 10px 15px 18px;">
                        
                        <div id="tatabJ" class="panel panel-default" style="margin: 5px 0 0;">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item"><a class="nav-link pill-link nl active" role="tab" data-toggle="tab" href="#tatabJ-1">250-Day High vs Low</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabJ-2">MA250 vs MA50</a></li>
                                <li class="nav-item"><a class="nav-link pill-link nl" role="tab" data-toggle="tab" href="#tatabJ-3">RSI Weekly vs RSI Daily</a></li>
                            </ul>
                        
                            <div class="tab-content panel-body">
                                <div class="tab-pane active" role="tabpanel" id="tatabJ-1">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shenzhen Deviation % From 250-Day High vs 250-Day Low</h3>
                                        <div id="tabubblecontainerJ1" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="highlowJ1"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabJ-2">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shenzhen Deviation % From MA250 vs MA50</h3>
                                        <div id="tabubblecontainerJ2" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="MA50_LongJ2"></div>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="tatabJ-3">
                                    <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                    <div>
                                        <h3 class="sr-only">Shenzhen Weekly RSI vs Daily RSI</h3>
                                        <div id="tabubblecontainerJ3" style="width:auto;height:700px;"></div>
                                        <hr style="margin:0 5% 20px 5%;">
                                        <div class="selectable" id="RSIJ3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>
        
        <p></p>
            <div align="center">
                <!--<a href="#"><img src = "assets/img/Ad_h2.png" widht = "728" height = "90" align="center"/></a>-->
            </div>
        <p></p>
        </div>

        <div class="col-sm-6 col-lg-4"><!-- style="padding:0px 0px 0px 15px">-->
            
<div class = "card clean-card text-center container" style="padding:0;">
    <div class="card-header">
        <h2>Summary</h2>
    </div>

    <!-- TradingView Widget BEGIN -->
    <div class="tradingview-widget-container" id="TVWidget">
        <div class="tradingview-widget-container__widget"></div>
        <div class="tradingview-widget-copyright not-selectable"><a href="https://www.tradingview.com" rel="noopener" target="_blank"><span class="blue-text">Market Data</span></a>&nbsp;by TradingView</div>
    </div>
    <script>
    function initTVWidget() {
        var container = document.getElementById('TVWidget');
        if (!container) return;

        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        /* remove existing widget iframe */
        var existingIframe = container.querySelector('iframe');
        if (existingIframe) existingIframe.remove();

        /* remove any previously injected script to avoid duplicate load */
        var oldScript = container.querySelector('script[data-tv-init]');
        if (oldScript) oldScript.remove();

        var config = {
            "colorTheme": isDark ? "dark" : "light",
            "dateRange": "12m",
            "showChart": true,
            "locale": "en",
            "width": "100%",
            "height": "775",
            "largeChartUrl": "https://360miq.com",
            "isTransparent": false,
            "plotLineColorGrowing": "rgba(33, 150, 243, 1)",
            "plotLineColorFalling": "rgba(33, 150, 243, 1)",
            "gridLineColor": isDark ? "rgba(42, 42, 62, 1)" : "rgba(233, 233, 234, 1)",
            "scaleFontColor": isDark ? "rgba(200, 200, 210, 1)" : "rgba(120, 123, 134, 1)",
            "belowLineFillColorGrowing": "rgba(33, 150, 243, 0.12)",
            "belowLineFillColorFalling": "rgba(33, 150, 243, 0.12)",
            "symbolActiveColor": "rgba(33, 150, 243, 0.12)",
            "tabs": [
                {
                    "title": "Index",
                    "symbols": [
                        { "s": "OANDA:SPX500USD" },
                        { "s": "OANDA:NAS100USD" },
                        { "s": "FOREXCOM:DJI" },
                        { "s": "OANDA:UK100GBP" },
                        { "s": "ASX:XJO" },
                        { "s": "WHSELFINVEST:JAPAN225CFD" },
                        { "s": "FOREXCOM:HKXHKD" },
                        { "s": "OANDA:CN50USD" }
                    ],
                    "originalTitle": "Index"
                },
                {
                    "title": "Commodity",
                    "symbols": [
                        { "s": "TVC:GOLD", "d": "Gold" },
                        { "s": "TVC:SILVER", "d": "Silver" },
                        { "s": "OANDA:XCUUSD", "d": "Copper" },
                        { "s": "TVC:USOIL", "d": "WTI Crude Oil" },
                        { "s": "OANDA:NATGASUSD", "d": "Natural Gas" },
                        { "s": "OANDA:CORNUSD", "d": "Corn" },
                        { "s": "OANDA:SOYBNUSD", "d": "Soybean" },
                        { "s": "BITSTAMP:BTCUSD", "d": "Bitcoin" }
                    ],
                    "originalTitle": "Commodity"
                },
                {
                    "title": "FX",
                    "symbols": [
                        { "s": "FX:EURUSD" },
                        { "s": "FX:GBPUSD" },
                        { "s": "FX:EURGBP" },
                        { "s": "FX:USDJPY" },
                        { "s": "FX:USDCHF" },
                        { "s": "FX:AUDUSD" },
                        { "s": "FX:USDCAD" },
                        { "s": "FX:USDCNH" }
                    ],
                    "originalTitle": "FX"
                },
                {
                    "title": "Bond",
                    "symbols": [
                        { "s": "CME:GE1!", "d": "Eurodollar" },
                        { "s": "CBOT:ZB1!", "d": "T-Bond" },
                        { "s": "CBOT:UB1!", "d": "Ultra T-Bond" },
                        { "s": "CBOT:ZT1!", "d": "2 Year T-Note" },
                        { "s": "EUREX:FGBL1!", "d": "Euro Bund" },
                        { "s": "EUREX:FBTP1!", "d": "Euro BTP" },
                        { "s": "EUREX:FGBM1!", "d": "Euro BOBL" },
                        { "s": "EUREX:FOAT1!", "d": "French Govt Bonds" }
                    ],
                    "originalTitle": "Bond"
                },
                {
                    "title": "Econ",
                    "symbols": [
                        { "s": "FRED:FEDFUNDS", "d": "Effective Fed Funds Rate" },
                        { "s": "FRED:UNRATE" },
                        { "s": "FRED:PAYEMS", "d": "Employees: Nonfarm" },
                        { "s": "FRED:HOUST", "d": "Total Housing Starts" },
                        { "s": "FRED:BASE", "d": "St Louis Monetary Base" },
                        { "s": "FRED:STLFSI", "d": "St Louis Financial Stress Index" },
                        { "s": "FRED:CPIAUCSL", "d": "Consumer Price Index" },
                        { "s": "ISM:MAN_PMI" }
                    ]
                }
            ]
        };

        var scriptEl = document.createElement('script');
        scriptEl.type = 'text/javascript';
        scriptEl.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js';
        scriptEl.async = true;
        scriptEl.setAttribute('data-tv-init', '');
        scriptEl.text = JSON.stringify(config);
        container.appendChild(scriptEl);
    }

    /* initial load */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTVWidget);
    } else {
        initTVWidget();
    }

    /* re-create on theme change */
    document.documentElement.addEventListener('themechange', function () {
        /* hide the copyright during transition to avoid flashing */
        var container = document.getElementById('TVWidget');
        var copyright = container ? container.querySelector('.tradingview-widget-copyright') : null;
        if (copyright) copyright.style.display = 'none';

        /* small delay so the widget script's CDN/iframe cleanup doesn't race */
        setTimeout(initTVWidget, 100);
        /* restore copyright at the bottom after widget finishes rendering */
        setTimeout(function() {
            if (!container) return;
            var cr = container.querySelector('.tradingview-widget-copyright');
            if (!cr) cr = copyright;
            if (cr) {
                container.appendChild(cr);
                cr.style.display = '';
            }
        }, 1000);
    });
    </script>
    <!-- TradingView Widget END -->    
</div>
<p></p>

<div class="card clean-card text-center container" style="padding:0;">
    <div class="card-header">
        <h2>Advance Decline Ratio</h2>
    </div>
    <div class="not-selectable">
    <div id="ad1container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad2container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad3container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad4container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad5container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad6container" style="float:left; width: 25%; height: 80px; "></div>
    <div id="ad7container" style="float:left; width: 25%; height: 80px; "></div>    
    <div id="ad8container" style="float:left; width: 25%; height: 80px; "></div>
    <div style="clear:both"></div>
    </div>
</div>
<p></p>
<div id="youtube" class="card clean-card text-center container" style="padding:0;display:none">
    <div class="card-header">
        <h2>Latest Video</h2>
    </div>
    <div id="latest-video-container" style="text-align:left"></div>
    <div id="video-details">
        <div id="video-title"></div>
        <div id="video-description"></div>
    </div>
    <style>
        /* Center the video container */
        #latest-video-container {
            display: flex;
            width: 100%;
            justify-content: center;
            align-items: center;
        }
        /* Ensure the iframe maintains aspect ratio */
        iframe {
            width: 100%;
            max-width: 100%;
            height: auto;
            aspect-ratio: 16/9;
            border: 0;
        }
        /* Style for video details */
        #video-details {
            margin: 12px 20px;
            text-align: left;
        }
        #video-title {
            margin: 0;
            font-size:15px;
        }
        #video-description {
            color: #555;
            margin: 10px 0 0;
            font-size:14px;
        }
    </style>
</div>
<p></p>
    <style>
        .img-hover-zoom {
          overflow: hidden; /* [1.2] Hide the overflowing of child elements */
        }
        
        /* [2] Transition property for smooth transformation of images */
        .img-hover-zoom img {
          transition: transform .5s ease;
        }
        
        /* [3] Finally, transforming the image when container gets hovered */
        .img-hover-zoom:hover img {
          transform: scale(1.15);
        }

        .bottom-center {
          position: absolute;
          display: none;
          left: 50%; transform: translate(-50%, -115%); width: 95%;
    	  text-shadow: 0px 1px 5px #888;
    	  text-align:center;
    	  padding: 0;
    	  background-color: rgba(255,255,255,0.9);
    	  border-radius: 5px;
    	  font-weight:550;
        }
    </style>
<div id="featuredpost" class="card clean-card text-center container" style="padding: 0;display:none">
    <div class="card-header">
        <h2>Featured Analyses</h2>
    </div>
    <div class="img-hover-zoom">
        <div id="featuredpostcontainer1" class="img-hover-zoom"></div>
        <!--div class="bottom-left" id="featuredposttitle" style="text-align:left;padding: 0px 20px 10px 20px;"></div-->
        <div class="bottom-center" id="featuredposttitle1"></div>
    </div>
    
    <div class="img-hover-zoom">
        <div id="featuredpostcontainer2" class="img-hover-zoom"></div>
        <!--div class="bottom-left" id="featuredposttitle" style="text-align:left;padding: 0px 20px 10px 20px;"></div-->
        <div class="bottom-center" id="featuredposttitle2"></div>
    </div>
    
    <div class="img-hover-zoom">
        <div id="featuredpostcontainer3" class="img-hover-zoom"></div>
        <!--div class="bottom-left" id="featuredposttitle" style="text-align:left;padding: 0px 20px 10px 20px;"></div-->
        <div class="bottom-center" id="featuredposttitle3"></div>
    </div>
    
    <div class="img-hover-zoom">
        <div id="featuredpostcontainer4" class="img-hover-zoom"></div>
        <!--div class="bottom-left" id="featuredposttitle" style="text-align:left;padding: 0px 20px 10px 20px;"></div-->
        <div class="bottom-center" id="featuredposttitle4"></div>
    </div>
    
    <div class="img-hover-zoom">
        <div id="featuredpostcontainer5" class="img-hover-zoom"></div>
        <!--div class="bottom-left" id="featuredposttitle" style="text-align:left;padding: 0px 20px 10px 20px;"></div-->
        <!--div class="bottom-center" id="featuredposttitle5" style="text-align:center;padding: 10px 10px 0.5px 10px;background-color: rgba(255,255,255,0.9);border-radius: 5px;"></div-->
        <div class="bottom-center" id="featuredposttitle5"></div>
    </div>
</div>
<p></p>
<div class="card clean-card text-center container" style="padding:0;">
    <div class="card-header">
        <h2>Recent Analyses</h2>
    </div>
    <div id="postcontainer" style="text-align:left"></div>
</div>
<script src="https://code.highcharts.com/stock/8.2.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/8.2.0/highcharts-more.js"></script>
<script src="assets/js/LanguageTimezone.js"></script>
<script src="assets/js/TA.js"></script>
<script src="assets/js/AdvDecPie.js"></script>
<script src="assets/js/sectorPerformance.js"></script>
<script src="assets/js/stockCompare.js"></script>
<!--script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/v8/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet">
<link href="https://cdn.anychart.com/releases/v8/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet"-->
<script>
function loadHomeScript(src)
{
    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadHomeStyle(href)
{
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

var homeAnychartLoaded = false;
var homeTreemapWaiting = false;
var homeAnychartReady = null;

function ensureHomeAnychart()
{
    if (homeAnychartReady)
        return homeAnychartReady;

    loadHomeStyle('https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');
    loadHomeStyle('https://cdn.anychart.com/releases/8.9.0/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33');

    homeAnychartReady = loadHomeScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33')
        .then(function() {
            return Promise.all([
                loadHomeScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadHomeScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadHomeScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33'),
                loadHomeScript('https://cdn.anychart.com/releases/8.9.0/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33')
            ]);
        })
        .then(function() {
            return loadHomeScript('assets/js/Treemap.js');
        })
        .then(function() {
            homeAnychartLoaded = true;
        });

    return homeAnychartReady;
}
</script>
<script src="assets/js/GaugeChart.js"></script>
<script src="assets/js/pages/index-main.js?v=20260619.2"></script>
<script>
$(".showNote").click(function() {
    const $btn = $(this);
    const $note = $btn.next(".noteWrapper");
    const isCollapsed = $note.hasClass("collapsed");

    $note.toggleClass("collapsed", !isCollapsed).toggleClass("expanded", isCollapsed);
    $btn.text(isCollapsed ? "Show less" : "Show more");
});
</script>
<!--        <p></p>
        <div class = "card clean-card text-center container" style="padding:0;">
            <div class="card-header">
                <h2>Latest Video</h2>
            </div>
        <div class="video-container"><iframe allowfullscreen frameborder="0" src="https://www.youtube.com/embed/KSJ7Kfonn7I" width="100%" height="100%"></iframe></div></div>
-->            
        <p></p>
        <div>
            <!--<a href="#"><img  style="float:left;" src = "assets/img/Ad_v2.png" widht = "160" height = "600"/></a>
            <a href="#"><img  style="float:right;" src = "assets/img/Ad_v2.png" widht = "160" height = "600"/></a>-->
        </div>
    </div>
</div></div>
</section>
</main>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/Bubble.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/theme.js"></script>-->
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
</body>

</html>
