<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="360MiQ.com - Free Stock Market Insights & Analytics" />
    <meta property="og:url" content="https://360miq.com/" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i,600,600i">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/simple-line-icons.min.css">
    <link rel="stylesheet" href="assets/css/card.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <script src="assets/js/Utils.js"></script>
    <script src="assets/js/LanguageTimezone.js"></script>
    <script src="assets/js/TA.js"></script>
    <script src="assets/js/AdvDecPie.js"></script>
    <script src="assets/js/sectorPerformance.js"></script>
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
<script src="https://code.highcharts.com/stock/8.2.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/8.2.0/highcharts-more.js"></script>
<script src="assets/js/stockCompare.js"></script>
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
        <h3 class="sr-only">World Equity Indices: S&P 500, Nasdaq Composite, Dow Industrial, Dow Transportation, FTSE 100, TSX Composite, ASX 200, Nifty 50, Nikkei 225, Hang Seng, Shanghai Composite, Bitcoin</h3>
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
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet">
<link href="https://cdn.anychart.com/releases/8.9.0/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet">
<!--script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-ui.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-exports.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-treemap.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-data-adapter.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/v8/css/anychart-ui.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet">
<link href="https://cdn.anychart.com/releases/v8/fonts/css/anychart-font.min.css?hcode=c11e6e3cfefb406e8ce8d99fa8368d33" type="text/css" rel="stylesheet"-->
<script src="assets/js/Treemap.js"></script>
<script src="assets/js/GaugeChart.js"></script>
<script type = "text/javascript">
var scrollPosition = 0;
var isIE = detectIE();
if (isIE)
{
    document.getElementById("TVWidget").innerHTML = '<span class="not-selectable"><br><br><br><br><br><br><br><br><br><br><br><br>Internet Explorer<br>does not support this widget.<br><br>Please use other browsers.</span>';
}

var isMobile = window.mobileAndTabletcheck();
var dict = new Object();

$.ajax({
    url : "/db_market_trendgauge_get.php",
    success : function(result){
        var jsonObject = result.split(/\r?\n|\r/);
        for (var i = 0; i < jsonObject.length; i++) {
            var row = jsonObject[i].split(',')

    	    if (row.length == 2)
    	    {
                if (dict[row[0]]) {
                     dict[row[0]].push(parseFloat(row[1]));
                } else {
                    dict[row[0]] = [parseFloat(row[1])];
                }
    	    }
        }
        
        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
        var fontsize = '9.2px';
        if (browserwidth <= 480)
            fontsize = '8px';
                
        if (dict["N"] && dict["Y"] && dict["N"].length > 0 && dict["Y"].length > 0)
        	trendgauge("gaugecontainer1", dict["N"], dict["Y"], "NYSE", "market?data=NYSE", fontsize, 'pointer', '');
    	else
    	    trendgauge("gaugecontainer1", -1, -1, "NYSE", "market?data=NYSE", fontsize, 'pointer', '');   

        if (dict["E"] && dict["L"] && dict["E"].length > 0 && dict["L"].length > 0)
        	trendgauge("gaugecontainer2", dict["E"], dict["L"], "London", "market?data=LSE", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer2", -1, -1, "London", "market?data=LSE", fontsize, 'pointer', '');

/*        if (dict["G"] && dict["Z"] && dict["G"].length > 0 && dict["Z"].length > 0)
        	trendgauge("gaugecontainer3", dict["G"], dict["Z"], "Shenzhen", "market?data=SZSE", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer3", -1, -1, "Shenzhen", "market?data=SZSE", fontsize, 'pointer', '');
*/
        if (dict["G"] && dict["Z"] && dict["G"].length > 0 && dict["Z"].length > 0)
        	trendgauge("gaugecontainer3", dict["G"], dict["Z"], "India NSE", "market?data=NSE", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer3", -1, -1, "India NSE", "market?data=NSE", fontsize, 'pointer', '');

        if (dict["F"] && dict["S"] && dict["F"].length > 0 && dict["S"].length > 0)
        	trendgauge("gaugecontainer4", dict["F"], dict["S"], "Tokyo", "market?data=TYO", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer4", -1, -1, "Tokyo", "market?data=TYO", fontsize, 'pointer', '');
        	

        if (dict["D"] && dict["Q"] && dict["D"].length > 0 && dict["Q"].length > 0)
        	trendgauge("gaugecontainer5", dict["D"], dict["Q"], "Nasdaq", "market?data=NASDAQ", fontsize, 'pointer', '');
    	else
    	    trendgauge("gaugecontainer5", -1, -1, "Nasdaq", "market?data=NASDAQ", fontsize, 'pointer', '');

        if (dict["I"] && dict["T"] && dict["I"].length > 0 && dict["T"].length > 0)
        	trendgauge("gaugecontainer6", dict["I"], dict["T"], "Toronto", "market?data=TSX", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer6", -1, -1, "Toronto", "market?data=TSX", fontsize, 'pointer', '');

        if (dict["X"] && dict["A"] && dict["X"].length > 0 && dict["A"].length > 0)
        	trendgauge("gaugecontainer7", dict["X"], dict["A"], "Australia", "market?data=ASX", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer7", -1, -1, "Australia", "market?data=ASX", fontsize, 'pointer', '');
        	
        if (dict["K"] && dict["H"] && dict["K"].length > 0 && dict["H"].length > 0)
        	trendgauge("gaugecontainer8", dict["K"], dict["H"], "Hong Kong", "market?data=HKEX", fontsize, 'pointer', '');
    	else
        	trendgauge("gaugecontainer8", -1, -1, "Hong Kong", "market?data=HKEX", fontsize, 'pointer', '');
    }
});

var dict = new Object();
var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
var creditY = -485;
$.ajax({
    url : "db_index_get.php",
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
                else if (row[0] == "C")
                    row[0] = "Bitcoin";
                else if (row[0] == "D")
                    row[0] = "Dow Industrial";
                else if (row[0] == "E")
                    row[0] = "Dow Transportation";

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
        
        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
        var rangeselector = 4;
        var creditYoffset = 2;
        if (browserwidth > 576)
        {
            rangeselector = 6;
            creditYoffset = 0;
        }
        stockCompare('weicontainer', dict, ["S&P 500", "Nasdaq Composite", "Dow Industrial", "Dow Transportation", "FTSE 100", "TSX Composite", "ASX 200", "Nifty 50", "Nikkei 225", "Hang Seng", "Shanghai Composite", "Bitcoin", "tmp"], "World Equity Indices Performance", "", "Performance %", "", "day", rangeselector, creditY + creditYoffset, 0, true);
    }
});
    
var sectorDict = new Object();
$.ajax({
    url : "/db_sectorperformance_get.php",
    success : function(result){
        var rows = result.split(/\r?\n|\r/);

        var dict_sector = new Object();
        var tmpDict;
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
                    
                if (!sectorDict[fields[0]])
                    tmpDict = new Object();
                if (tmpDict[fields[1]]) {
                    tmpDict[fields[1]].push([parseFloat(fields[2]), parseFloat(fields[3]), parseFloat(fields[4]), datestr1]);
                } else {
                    tmpDict[fields[1]] = [parseFloat(fields[2]), parseFloat(fields[3]), parseFloat(fields[4]), datestr1];
                }
                
                if (!sectorDict[fields[0]])
                {
                    sectorDict[fields[0]] = tmpDict;
                }
            }
        }
        
        var showbar = '';
        if (isMobile)
            showbar = '1';
        var chart1 = sectorPerformance('barcontainer1', 'NYSE', "market?data=NYSE", dict_sector['NYSE'], true, '1', showbar, true);
        var chart2 = sectorPerformance('barcontainer2', 'Nasdaq', "market?data=NASDAQ", dict_sector['NASDAQ'], true, '1', showbar, true);
        var chart3 = sectorPerformance('barcontainer3', 'London', "market?data=LSE", dict_sector['LSE'], true, '1', showbar, true);
        var chart4 = sectorPerformance('barcontainer4', 'Toronto TSX', "market?data=TSX", dict_sector['TSX'], true, '1', showbar, true);
        var chart5 = sectorPerformance('barcontainer5', 'Australia', "market?data=ASX", dict_sector['ASX'], true, '1', showbar, true);
        //var chart6 = sectorPerformance('barcontainer6', 'Shenzhen', "market?data=SZSE", dict_sector['SZSE'], true, '1', showbar, true);
        var chart6 = sectorPerformance('barcontainer6', 'India NSE', "market?data=NSE", dict_sector['NSE'], true, '1', showbar, true);
        var chart7 = sectorPerformance('barcontainer7', 'Tokyo', "market?data=TYO", dict_sector['TYO'], true, '1', showbar, true);
        var chart8 = sectorPerformance('barcontainer8', 'Hong Kong', "market?data=HKEX", dict_sector['HKEX'], true, '1', showbar, true);
        var chart9 = sectorPerformance('barcontainer9', 'Shanghai', "market?data=SHSE", dict_sector['SHSE'], true, '1', showbar, true);
        var chart10 = sectorPerformance('barcontainer10', 'Shenzhen', "market?data=SZSE", dict_sector['SZSE'], true, '1', showbar, true);

        setLabelEvent(chart1, "NYSE", true);
        setLabelEvent(chart2, "NASDAQ", true);
        setLabelEvent(chart3, "LSE", true);
        setLabelEvent(chart4, "TSX", true);
        setLabelEvent(chart5, "ASX", true);
        //setLabelEvent(chart6, "SZSE", true);
        setLabelEvent(chart6, "NSE", true);
        setLabelEvent(chart7, "TYO", true);
        setLabelEvent(chart8, "HKEX", true);
        setLabelEvent(chart9, "SHSE", true);
        setLabelEvent(chart10, "SZSE", true);
        
        $(window).resize(function() {
            setLabelEvent(chart1, "NYSE", true);
            setLabelEvent(chart2, "NASDAQ", true);
            setLabelEvent(chart3, "LSE", true);
            setLabelEvent(chart4, "TSX", true);
            setLabelEvent(chart5, "ASX", true);
            //setLabelEvent(chart6, "SZSE", true);
            setLabelEvent(chart6, "NSE", true);
            setLabelEvent(chart7, "TYO", true);
            setLabelEvent(chart8, "HKEX", true);
            setLabelEvent(chart9, "SHSE", true);
            setLabelEvent(chart10, "SZSE", true);
        });    
    }
});

var tab1loaded = false, tab2loaded = false, tab3loaded = false, tab4loaded = false, tab5loaded = false, tab6loaded = false, tab7loaded = false, tab8loaded = false, tab9loaded = false, tab10loaded = false;
var initialTab =new URL(window.location).searchParams.get('tab');
var initialTreeTab =new URL(window.location).searchParams.get('treetab');
$(function() {
    if (document.location.toString().match('#') || initialTab || initialTreeTab)
	    openTabHash(); // for the initial page load

	window.addEventListener("hashchange", openTabHash, false); // for later changes to url
	
});

// Handle browser back/forward
window.addEventListener('popstate', function () {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    const treetab = params.get('treetab');
    var url = document.location.toString();
    
    if (tab) {
        $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show');
    } else if (treetab) {
        $('.nav-tabs a[href="#treetab-' + treetab + '"]').tab('show');
    }
    else if (url.match('#')) {
        $('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show') ;
    } else {
        $('.nav-tabs a:first').tab('show'); // fallback
    }
});

// Handle tab click and update URL
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    e.preventDefault();

    const hash = e.target.hash; // e.g. #tab-2 or #treetab-3
    const url = new URL(window.location);

    if (hash.startsWith('#tab-')) {
        const tabId = hash.replace('#tab-', '');
        url.searchParams.set('tab', tabId);
        url.searchParams.delete('treetab'); // remove treetab
    } else if (hash.startsWith('#treetab-')) {
        const treetabId = hash.replace('#treetab-', '');
        url.searchParams.set('treetab', treetabId);
        url.searchParams.delete('tab'); // remove tab
    }

    url.hash = ''; // clean up the hash
    history.replaceState(null, '', url.toString());
});

var isLoadedSuccessfully = false;
function openTabHash()
{
	// Javascript to enable link to tab
	const urlNew = new URL(window.location);
    const params = urlNew.searchParams;
    let tab = params.get('tab');
    let treetab = params.get('treetab');
      
      
	var url = document.location.toString();
	if (url.match('#')) {
		$('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show') ;
	} 
    else if (tab) {
        $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show') ;
    }
    else if (treetab) {
        $('.nav-tabs a[href="#treetab-' + treetab + '"]').tab('show') ;
    }

    initialTab =new URL(window.location).searchParams.get('tab');
    initialTreeTab =new URL(window.location).searchParams.get('treetab');
    tab = new URL(window.location).searchParams.get('tab'); // need to find tab again as .tab('show') fires .on('shown.bs.tab', will render window.location.hash = ""
    treetab = new URL(window.location).searchParams.get('treetab'); // need to find treetab again as .tab('show') fires .on('shown.bs.tab', will render window.location.hash = ""
    
	window.scrollTo(0, scrollPosition);
	
	var type;
	if (window.location.hash == "#treetab-1" || window.location.hash == "#tab-1" || tab == "1" || treetab == "1")
	{
		chartcontainer = "chartcontainerA1";
		type = "NYSE";
	}
	else if (window.location.hash == "#treetab-2" || window.location.hash == "#tab-2" || tab == "2" || treetab == "2")
	{
		chartcontainer = "chartcontainerB1";
		type = "Nasdaq";
	}
	else if (window.location.hash == "#treetab-3" || window.location.hash == "#tab-3" || tab == "3" || treetab == "3")
	{
		chartcontainer = "chartcontainerC1";
		type = "London";
	}
	else if (window.location.hash == "#treetab-4" || window.location.hash == "#tab-4" || tab == "4" || treetab == "4")
	{
		chartcontainer = "chartcontainerD1";
		type = "Toronto TSX";
	}
	else if (window.location.hash == "#treetab-5" || window.location.hash == "#tab-5" || tab == "5" || treetab == "5")
	{
		chartcontainer = "chartcontainerE1";
		type = "Australia";
	}
	else if (window.location.hash == "#treetab-6" || window.location.hash == "#tab-6" || tab == "6" || treetab == "6")
	{
		chartcontainer = "chartcontainerF1";
		type = "India NSE";        			
	}
	else if (window.location.hash == "#treetab-7" || window.location.hash == "#tab-7" || tab == "7" || treetab == "7")
	{
		chartcontainer = "chartcontainerG1";
		type = "Tokyo";
	}
	else if (window.location.hash == "#treetab-8" || window.location.hash == "#tab-8" || tab == "8" || treetab == "8")
	{
		chartcontainer = "chartcontainerH1";
		type = "Hong Kong";
	}
	else if (window.location.hash == "#treetab-9" || window.location.hash == "#tab-9" || tab == "9" || treetab == "9")
	{
		chartcontainer = "chartcontainerI1";
		type = "Shanghai";
	}
	else if (window.location.hash == "#treetab-10" || window.location.hash == "#tab-10" || tab == "10" || treetab == "10")
	{
		chartcontainer = "chartcontainerJ1";
		type = "Shenzhen";
	}
	else if (window.location.hash != "")
	{
		window.location.hash = "#treetab-1"; // show tab1
	}
	else
	{
	    chartcontainer = "chartcontainerA1";
		type = "NYSE";
	}
		
	if (typeof(chartcontainer) != "undefined" && typeof(type) != "undefined")
	{
	    if (isLoadedSuccessfully)
	    {
	        loadTreemapScatter();
	    }   
        else
        {
            $(window).scroll(function() {
                var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                var scrollPosArray = [0.55, 0.33, 0.2];
                
                if (document.getElementById("featuredpost").style.display == "")
                    scrollPosArray = [0.28, 0.22, 0.08]
                    
                var scrollPos = scrollPosArray[0];
                if (browserwidth < 768)
                {
                    scrollPos = scrollPosArray[1];
                }
                else if (browserwidth < 1200)
                {
                    scrollPos = scrollPosArray[2];
                }
                
            	if ($(window).scrollTop() > ($(document).height() - $(window).height()) * scrollPos) {
                    loadTreemapScatter();
            	}
            });
        }
	}
}

function loadTreemapScatter()
{
    if (chartcontainer == "chartcontainerA1" && tab1loaded == false) // no need to load PE band when clicked as it is loaded at start for polar chart
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=NYSE', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['NYSE']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverA1", d1_movers, subtitle, 'NYSE', '1 Day Performance');
            renderMovers("moverA2", d5_movers, subtitle, 'NYSE', '5 Days Performance');
            renderMovers("moverA3", d20_movers, subtitle, 'NYSE', '20 Days Performance');

            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowA1", dd_highlow, subtitle, 'NYSE', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongA2", dd_MA50_long, subtitle, 'NYSE', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIA3", dd_rsi, subtitle, 'NYSE', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['NYSE'], isMobile);
            //bubble(dd.d1, "bubblecontainerA1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerA1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerA1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);

            setTimeout(function() {
                treemap(data, "chartcontainerA2", 1, 5, sectorDict['NYSE'], isMobile);
                //bubble(dd.d5, "bubblecontainerA2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerA2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerA2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);
            
            setTimeout(function() {
                treemap(data, "chartcontainerA3", 1, 20, sectorDict['NYSE'], isMobile);
                //bubble(dd.d20, "bubblecontainerA3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerA3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerA3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
		tab1loaded = true;
		document.getElementById("content").style.display = "";
		isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerB1" && tab2loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=NASDAQ', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['NASDAQ']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverB1", d1_movers, subtitle, 'NASDAQ', '1 Day Performance');
            renderMovers("moverB2", d5_movers, subtitle, 'NASDAQ', '5 Days Performance');
            renderMovers("moverB3", d20_movers, subtitle, 'NASDAQ', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowB1", dd_highlow, subtitle, 'NASDAQ', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongB2", dd_MA50_long, subtitle, 'NASDAQ', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIB3", dd_rsi, subtitle, 'NASDAQ', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['NASDAQ'], isMobile);
            //bubble(dd.d1, "bubblecontainerB1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerB1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerB1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {
                treemap(data, "chartcontainerB2", 1, 5, sectorDict['NASDAQ'], isMobile);
                //bubble(dd.d5, "bubblecontainerB2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerB2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerB2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);
            
            setTimeout(function() {
                treemap(data, "chartcontainerB3", 1, 20, sectorDict['NASDAQ'], isMobile);
                //bubble(dd.d20, "bubblecontainerB3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerB3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerB3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
		tab2loaded = true;
		document.getElementById("content").style.display = "";
		isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerC1" && tab3loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=LSE', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['LSE']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverC1", d1_movers, subtitle, 'LSE', '1 Day Performance');
            renderMovers("moverC2", d5_movers, subtitle, 'LSE', '5 Days Performance');
            renderMovers("moverC3", d20_movers, subtitle, 'LSE', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowC1", dd_highlow, subtitle, 'LSE', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongC2", dd_MA50_long, subtitle, 'LSE', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIC3", dd_rsi, subtitle, 'LSE', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['LSE'], isMobile);
            //bubble(dd.d1, "bubblecontainerC1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerC1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerC1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerC2", 1, 5, sectorDict['LSE'], isMobile);
                //bubble(dd.d5, "bubblecontainerC2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerC2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerC2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerC3", 1, 20, sectorDict['LSE'], isMobile);
                //bubble(dd.d20, "bubblecontainerC3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerC3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerC3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
		tab3loaded = true;
		document.getElementById("content").style.display = "";
		isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerD1" && tab4loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=TSX', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['TSX']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverD1", d1_movers, subtitle, 'TSX', '1 Day Performance');
            renderMovers("moverD2", d5_movers, subtitle, 'TSX', '5 Days Performance');
            renderMovers("moverD3", d20_movers, subtitle, 'TSX', '20 Days Performance');

            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowD1", dd_highlow, subtitle, 'TSX', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongD2", dd_MA50_long, subtitle, 'TSX', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSID3", dd_rsi, subtitle, 'TSX', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['TSX'], isMobile);
            //bubble(dd.d1, "bubblecontainerD1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerD1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerD1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerD2", 1, 5, sectorDict['TSX'], isMobile);
                //bubble(dd.d5, "bubblecontainerD2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerD2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerD2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerD3", 1, 20, sectorDict['TSX'], isMobile);
                //bubble(dd.d20, "bubblecontainerD3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerD3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerD3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab4loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerE1" && tab5loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=ASX', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['ASX']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverE1", d1_movers, subtitle, 'ASX', '1 Day Performance');
            renderMovers("moverE2", d5_movers, subtitle, 'ASX', '5 Days Performance');
            renderMovers("moverE3", d20_movers, subtitle, 'ASX', '20 Days Performance');

            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowE1", dd_highlow, subtitle, 'ASX', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongE2", dd_MA50_long, subtitle, 'ASX', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIE3", dd_rsi, subtitle, 'ASX', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['ASX'], isMobile);
            //bubble(dd.d1, "bubblecontainerE1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerE1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerE1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerE2", 1, 5, sectorDict['ASX'], isMobile);
                //bubble(dd.d5, "bubblecontainerE2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerE2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerE2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerE3", 1, 20, sectorDict['ASX'], isMobile);
                //bubble(dd.d20, "bubblecontainerE3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerE3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerE3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab5loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerF1" && tab6loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=NSE', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['NSE']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverF1", d1_movers, subtitle, 'NSE', '1 Day Performance');
            renderMovers("moverF2", d5_movers, subtitle, 'NSE', '5 Days Performance');
            renderMovers("moverF3", d20_movers, subtitle, 'NSE', '20 Days Performance');

            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowF1", dd_highlow, subtitle, 'NSE', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongF2", dd_MA50_long, subtitle, 'NSE', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIF3", dd_rsi, subtitle, 'NSE', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['NSE'], isMobile);
            //bubble(dd.d1, "bubblecontainerF1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerF1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerF1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerF2", 1, 5, sectorDict['NSE'], isMobile);
                //bubble(dd.d5, "bubblecontainerF2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerF2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerF2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerF3", 1, 20, sectorDict['NSE'], isMobile);
                //bubble(dd.d20, "bubblecontainerF3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerF3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerF3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab6loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerG1" && tab7loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=TYO', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['TYO']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverG1", d1_movers, subtitle, 'TYO', '1 Day Performance');
            renderMovers("moverG2", d5_movers, subtitle, 'TYO', '5 Days Performance');
            renderMovers("moverG3", d20_movers, subtitle, 'TYO', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowG1", dd_highlow, subtitle, 'TYO', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongG2", dd_MA50_long, subtitle, 'TYO', 'MA200 & MA50', 'MA200');
            renderMoversXY("RSIG3", dd_rsi, subtitle, 'TYO', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['TYO'], isMobile);
            //bubble(dd.d1, "bubblecontainerG1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerG1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerG1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerG2", 1, 5, sectorDict['TYO'], isMobile);
                //bubble(dd.d5, "bubblecontainerG2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerG2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerG2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerG3", 1, 20, sectorDict['TYO'], isMobile);
                //bubble(dd.d20, "bubblecontainerG3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerG3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerG3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab7loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerH1" && tab8loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=HKEX', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['HKEX']);

            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverH1", d1_movers, subtitle, 'HKEX', '1 Day Performance');
            renderMovers("moverH2", d5_movers, subtitle, 'HKEX', '5 Days Performance');
            renderMovers("moverH3", d20_movers, subtitle, 'HKEX', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowH1", dd_highlow, subtitle, 'HKEX', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongH2", dd_MA50_long, subtitle, 'HKEX', 'MA250 & MA50', 'MA250');
            renderMoversXY("RSIH3", dd_rsi, subtitle, 'HKEX', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['HKEX'], isMobile);
            //bubble(dd.d1, "bubblecontainerH1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerH1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerH1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerH2", 1, 5, sectorDict['HKEX'], isMobile);
                //bubble(dd.d5, "bubblecontainerH2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerH2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerH2", 1, '% From MA250 vs MA50', subtitle, "", "% From MA250", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerH3", 1, 20, sectorDict['HKEX'], isMobile);
                //bubble(dd.d20, "bubblecontainerH3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerH3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerH3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab8loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerI1" && tab9loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=SHSE', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['SHSE']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverI1", d1_movers, subtitle, 'SHSE', '1 Day Performance');
            renderMovers("moverI2", d5_movers, subtitle, 'SHSE', '5 Days Performance');
            renderMovers("moverI3", d20_movers, subtitle, 'SHSE', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowI1", dd_highlow, subtitle, 'SHSE', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongI2", dd_MA50_long, subtitle, 'SHSE', 'MA250 & MA50', 'MA250');
            renderMoversXY("RSII3", dd_rsi, subtitle, 'SHSE', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['SHSE'], isMobile);
            //bubble(dd.d1, "bubblecontainerI1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerI1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerI1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerI2", 1, 5, sectorDict['SHSE'], isMobile);
                //bubble(dd.d5, "bubblecontainerI2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerI2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerI2", 1, '% From MA250 vs MA50', subtitle, "", "% From MA250", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerI3", 1, 20, sectorDict['SHSE'], isMobile);
                //bubble(dd.d20, "bubblecontainerI3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerI3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerI3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab9loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
	else if (chartcontainer == "chartcontainerJ1" && tab10loaded == false)
	{
		anychart.data.loadJsonFile('db_treemap_get.php?data=SZSE', function (data) {
        	var subtitle = data[0].product;

            var dd = bubbleData(data, sectorDict['SZSE']);
            
            const d1_movers = getTopBottom10Movers(dd.d1);
            const d5_movers = getTopBottom10Movers(dd.d5);
            const d20_movers = getTopBottom10Movers(dd.d20);
            
            renderMovers("moverJ1", d1_movers, subtitle, 'SZSE', '1 Day Performance');
            renderMovers("moverJ2", d5_movers, subtitle, 'SZSE', '5 Days Performance');
            renderMovers("moverJ3", d20_movers, subtitle, 'SZSE', '20 Days Performance');
            
            const dd_highlow = getTopBottom10HighLow(dd.hl250);
            const dd_MA50_long = getTopBottom10MA(dd.ma50_long);
            const dd_rsi = getTopBottom10HighLow(dd.rsi14w_d);
            
            renderMoversXY("highlowJ1", dd_highlow, subtitle, 'SZSE', '250-Day High & 250-Day Low');
            renderMoversXY("MA50_LongJ2", dd_MA50_long, subtitle, 'SZSE', 'MA250 & MA50', 'MA250');
            renderMoversXY("RSIJ3", dd_rsi, subtitle, 'SZSE', 'RSI Weekly & RSI Daily', 'RSI');

            treemap(data, chartcontainer, 1, 1, sectorDict['SZSE'], isMobile);
            //bubble(dd.d1, "bubblecontainerJ1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
            bubble(dd.d1, "bubblecontainerJ1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
            bubble(dd.hl250, "tabubblecontainerJ1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
            
            setTimeout(function() {        
                treemap(data, "chartcontainerJ2", 1, 5, sectorDict['SZSE'], isMobile);
                //bubble(dd.d5, "bubblecontainerJ2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                bubble(dd.d5, "bubblecontainerJ2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.ma50_long, "tabubblecontainerJ2", 1, '% From MA250 vs MA50', subtitle, "", "% From MA250", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
            }, 100);

            setTimeout(function() {
                treemap(data, "chartcontainerJ3", 1, 20, sectorDict['SZSE'], isMobile);
                //bubble(dd.d20, "bubblecontainerJ3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                bubble(dd.d20, "bubblecontainerJ3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                bubble(dd.rsi14w_d, "tabubblecontainerJ3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
            }, 100);
		});
        tab10loaded = true;
        document.getElementById("content").style.display = "";
        isLoadedSuccessfully = true;
	}
}

$.ajax({
    url : "/db_adv_get2.php",
    success : function(result){
        var jsonObject = result.split(/\r?\n|\r/);
        for (var i = 0; i < jsonObject.length; i++) {
            var fields = jsonObject[i].split(',');
            if (fields.length == 2)
            {
                var pair = fields[1].split('_');
                if (pair.length == 2)
                {
                    if (fields[0] == "NY")
                        adpie('ad1container', pair[0], parseInt(pair[1]), 'NY', 'NYSE', "market?data=NYSE&tab=5");
                    else if (fields[0] == "LS")
                        adpie('ad2container', pair[0], parseInt(pair[1]), 'LSE', 'London', "market?data=LSE&tab=5");
                    /*else if (fields[0] == "SZ")
                        adpie('ad3container', pair[0], parseInt(pair[1]), 'SZ', 'Shenzhen', "market?data=SZSE&tab=5");*/
                    else if (fields[0] == "NS")
                        adpie('ad3container', pair[0], parseInt(pair[1]), 'NSE', 'India NSE', "market?data=NSE&tab=5");
                    else if (fields[0] == "TY")
                        adpie('ad4container', pair[0], parseInt(pair[1]), 'TYO', 'Tokyo', "market?data=TYO&tab=5");
                    else if (fields[0] == "NA")
                        adpie('ad5container', pair[0], parseInt(pair[1]), 'NAS', 'Nasdaq', "market?data=NASDAQ&tab=5");
                    else if (fields[0] == "TS")
                        adpie('ad6container', pair[0], parseInt(pair[1]), 'TSX', 'Toronto TSX', "market?data=TSX&tab=5");
                    else if (fields[0] == "AS")
                        adpie('ad7container', pair[0], parseInt(pair[1]), 'ASX', 'Australia', "market?data=ASX&tab=5");
                    else if (fields[0] == "HK")
                        adpie('ad8container', pair[0], parseInt(pair[1]), 'HK', 'Hong Kong', "market?data=HKEX&tab=5");
                }
            }
        }
    }
});

$.ajax({
    url : "db_featuredblogpost_get.php",
    success : function(result){
        
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
                        document.getElementById("featuredpost").style.display  = "";

                        if (fields[4] == '' && fields[5] == '' && fields[6] == '')
                        {
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] == '' && fields[5] != '' && fields[6] == '')
                        {
                            html += '<a class="recentpost" href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="width: 100%;height: 100%" /></a>';
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] != '' && fields[5] == '' && fields[6] == '')
                        {
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] != '' && fields[5] != '' && fields[6] == '')
                        {
                            html += '<a class="recentpost" href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="width: 100%;height: 100%" /></a>';
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 7px rgba(0, 0, 0, 0.2), 0px 0px 7px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        
                        // youtube
                        else if (fields[4] != '' && fields[5] == '' && fields[6] != '')
                        {
                            document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="text-align:left"></div>';
                            embedVideo(fields[6], 'featured-video-container' + (i + 1));
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                        else if (fields[4] == '' && fields[5] == '' && fields[6] != '')
                        {
                            document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="text-align:left"></div>';
                            embedVideo(fields[6], 'featured-video-container' + (i + 1));
                            htmltitle = '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0];// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                        }
                    }
                }
                
                // if not youtube
                if (fields[6] == '')
                {
                    document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML += html;
                    document.getElementById("featuredposttitle" + (i + 1)).innerHTML = htmltitle;
                    document.getElementById("featuredposttitle" + (i + 1)).style.display  = "block";
                    document.getElementById("featuredposttitle" + (i + 1)).style.padding = "10px 10px 0.5px 10px";
                }
                
                if (i > 0)
                {
                    document.getElementById("featuredpostcontainer" + (i + 1)).style.paddingTop  = "20px";
                }
            }
        }
    }
});

$.ajax({
    url : "db_blogpost_get.php",
    success : function(result){
        var html = '<div style="padding: 20px 20px 10px 20px">';
        var jsonObject = result.split(/\r?\n|\r/);
        for (var i = 0; i < jsonObject.length; i++) {
            var fields = jsonObject[i].split('@');
            if (fields.length == 2)
            {
                html += '<p><a class="recentpost" href="/blog/'+ fields[1] + '">' + fields[0] + '</a></p>';
            }
        }
        document.getElementById("postcontainer").innerHTML = html + '</div>';
    }
});

$(document).ready(function(){
    $('.copyMe').on('change', function() {
        $(".copyMe").val($(this).val());
    });

    $("a.nl").click(function() {
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
    });

    $("a.mynav-link").click(function() {
        if (!(this.hash == "#treetab-1" && tab1loaded) &&
            !(this.hash == "#treetab-2" && tab2loaded) &&
            !(this.hash == "#treetab-3" && tab3loaded) &&
            !(this.hash == "#treetab-4" && tab4loaded) &&
            !(this.hash == "#treetab-5" && tab5loaded) &&
            !(this.hash == "#treetab-6" && tab6loaded) &&
            !(this.hash == "#treetab-7" && tab7loaded) &&
            !(this.hash == "#treetab-8" && tab8loaded) &&
            !(this.hash == "#treetab-9" && tab9loaded) &&
            !(this.hash == "#treetab-10" && tab10loaded))
        {
            scrollPosition = window.scrollY;
            window.location.href = this.hash;
            //jQuery("html,body").animate({scrollTop: 0}, 1000);
        }
    });
    
    var url = document.location.toString();
    if (!isIE)
    {
        var timezone = LanguageTimezone(); 
        if (timezone.includes("Hong Kong") && ((!url.match('#') && !initialTab && !initialTreeTab) || url.endsWith('#treetab-8') || url.endsWith('#tab-8') || initialTab == "8" || initialTreeTab == "8"))
        {
            if (!window.location.href.endsWith("#treetab-8") && tab8loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-8';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=HKEX', function (data) {
                	var subtitle = data[0].product;
    
                    var dd = bubbleData(data, sectorDict['HKEX']);
    
                    treemap(data, "chartcontainerH1", 1, 1, sectorDict['HKEX'], isMobile);
                    //bubble(dd.d1, "bubblecontainerH1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerH1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerH1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {        
                        treemap(data, "chartcontainerH2", 1, 5, sectorDict['HKEX'], isMobile);
                        //bubble(dd.d5, "bubblecontainerH2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerH2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerH2", 1, '% From MA250 vs MA50', subtitle, "", "% From MA250", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
        
                    setTimeout(function() {
                        treemap(data, "chartcontainerH3", 1, 20, sectorDict['HKEX'], isMobile);
                        //bubble(dd.d20, "bubblecontainerH3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerH3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerH3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab8loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-8"]').tab('show');
            $('.nav-tabs a[href="#treetab-8"]').tab('show');
        }
        else if (timezone.includes("Japan") && ((!url.match('#') && !initialTab && !initialTreeTab) || url.endsWith('#treetab-7') || url.endsWith('#tab-7') || initialTab == "7" || initialTreeTab == "7"))
        {
            if (!window.location.href.endsWith("#treetab-7") && tab7loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-7';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=TYO', function (data) {
                	var subtitle = data[0].product;
    
                    var dd = bubbleData(data, sectorDict['TYO']);
    
                    treemap(data, "chartcontainerG1", 1, 1, sectorDict['TYO'], isMobile);
                    //bubble(dd.d1, "bubblecontainerG1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerG1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerG1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {        
                        treemap(data, "chartcontainerG2", 1, 5, sectorDict['TYO'], isMobile);
                        //bubble(dd.d5, "bubblecontainerG2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerG2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerG2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
        
                    setTimeout(function() {
                        treemap(data, "chartcontainerG3", 1, 20, sectorDict['TYO'], isMobile);
                        //bubble(dd.d20, "bubblecontainerG3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerG3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerG3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab7loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-7"]').tab('show');
            $('.nav-tabs a[href="#treetab-7"]').tab('show');
        }
        else if (timezone.includes("India") && ((!url.match('#') && !initialTab && !initialTreeTab) || url.endsWith('#treetab-6') || url.endsWith('#tab-6') || initialTab == "6" || initialTreeTab == "6"))
        {
            if (!window.location.href.endsWith("#treetab-6") && tab6loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-6';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=NSE', function (data) {
                	var subtitle = data[0].product;
    
                    var dd = bubbleData(data, sectorDict['NSE']);
    
                    treemap(data, "chartcontainerF1", 1, 1, sectorDict['NSE'], isMobile);
                    //bubble(dd.d1, "bubblecontainerF1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerF1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerF1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {        
                        treemap(data, "chartcontainerF2", 1, 5, sectorDict['NSE'], isMobile);
                        //bubble(dd.d5, "bubblecontainerF2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerF2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerF2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
        
                    setTimeout(function() {
                        treemap(data, "chartcontainerF3", 1, 20, sectorDict['NSE'], isMobile);
                        //bubble(dd.d20, "bubblecontainerF3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerF3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerF3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab6loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-6"]').tab('show');
            $('.nav-tabs a[href="#treetab-6"]').tab('show');
        }
        else if (timezone.includes("United Kingdom") && ((!url.match('#') && !initialTab && !initialTreeTab) || url.endsWith('#treetab-3') || url.endsWith('#tab-3') || initialTab == "3" || initialTreeTab == "3"))
        {
            if (!window.location.href.endsWith("#treetab-3") && tab3loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-3';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=LSE', function (data) {
                	var subtitle = data[0].product;
    
                    var dd = bubbleData(data, sectorDict['LSE']);
    
                    treemap(data, "chartcontainerC1", 1, 1, sectorDict['LSE'], isMobile);
                    //bubble(dd.d1, "bubblecontainerC1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerC1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerC1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {        
                        treemap(data, "chartcontainerC2", 1, 5, sectorDict['LSE'], isMobile);
                        //bubble(dd.d5, "bubblecontainerC2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerC2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerC2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
        
                    setTimeout(function() {
                        treemap(data, "chartcontainerC3", 1, 20, sectorDict['LSE'], isMobile);
                        //bubble(dd.d20, "bubblecontainerC3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerC3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerC3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab3loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-3"]').tab('show');
            $('.nav-tabs a[href="#treetab-3"]').tab('show');
        }
        else if ((timezone.includes("Brisbane") || timezone.includes("Darwin") || timezone.includes("Sydney"))  && ((!url.match('#') && !initialTab && !initialTreeTab) || url.endsWith('#treetab-5') || url.endsWith('#tab-5') || initialTab == "5" || initialTreeTab == "5"))
        {
            if (!window.location.href.endsWith("#treetab-5") && tab5loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-5';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=ASX', function (data) {
                	var subtitle = data[0].product;
    
                    var dd = bubbleData(data, sectorDict['ASX']);
    
                    treemap(data, "chartcontainerE1", 1, 1, sectorDict['ASX'], isMobile);
                    //bubble(dd.d1, "bubblecontainerE1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerE1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerE1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {        
                        treemap(data, "chartcontainerE2", 1, 5, sectorDict['ASX'], isMobile);
                        //bubble(dd.d5, "bubblecontainerE2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerE2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerE2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
        
                    setTimeout(function() {
                        treemap(data, "chartcontainerE3", 1, 20, sectorDict['ASX'], isMobile);
                        //bubble(dd.d20, "bubblecontainerE3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerE3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerE3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab5loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-5"]').tab('show');
            $('.nav-tabs a[href="#treetab-5"]').tab('show');
        }
        else if (!url.match('#') && !initialTab && !initialTreeTab)
        {
            // this line is no needed
            if (!window.location.href.endsWith("#treetab-1") && !window.location.href.endsWith("#tab-1") && initialTab != "1" && initialTreeTab != "1" && tab1loaded == false)
            {
                scrollPosition = window.scrollY;
                window.location.href = '#treetab-1';
                /*anychart.data.loadJsonFile('db_treemap_get.php?data=NYSE', function (data) {
					var subtitle = data[0].product;

                    var dd = bubbleData(data, sectorDict['NYSE']);
                    
                    treemap(data, "chartcontainerA1", 1, 1, sectorDict['NYSE'], isMobile);
                    //bubble(dd.d1, "bubblecontainerA1", 1, subtitle, "1 Day", "1-day Volume relative to 5-day Volume Avg");
                    bubble(dd.d1, "bubblecontainerA1", 1, 'Stock Performance vs Relative Volume', subtitle, "1 Day", "1 Day Return %", "1-day Volume relative to 5-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                    bubble(dd.hl250, "tabubblecontainerA1", 1, '% From 250-Day High vs 250-Day Low', subtitle, "", "% Below 250-Day High", "% Above 250-Day Low", null, 1, '', false, false, true, true, false, 0, 0);
                    
                    setTimeout(function() {
                        treemap(data, "chartcontainerA2", 1, 5, sectorDict['NYSE'], isMobile);
                        //bubble(dd.d5, "bubblecontainerA2", 1, subtitle, "5 Days", "5-day Volume Avg relative to 20-day Volume Avg");
                        bubble(dd.d5, "bubblecontainerA2", 1, 'Stock Performance vs Relative Volume', subtitle, "5 Days", "5 Days Return %", "5-day Volume Avg relative to 20-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.ma50_long, "tabubblecontainerA2", 1, '% From MA200 vs MA50', subtitle, "", "% From MA200", "% From MA50", null, null, '', false, true, true, true, true, 0, 0);
                    }, 100);
                    
                    setTimeout(function() {
                        treemap(data, "chartcontainerA3", 1, 20, sectorDict['NYSE'], isMobile);
                        //bubble(dd.d20, "bubblecontainerA3", 1, subtitle, "20 Days", "20-day Volume Avg relative to 60-day Volume Avg");
                        bubble(dd.d20, "bubblecontainerA3", 1, 'Stock Performance vs Relative Volume', subtitle, "20 Days", "20 Days Return %", "20-day Volume Avg relative to 60-day Volume Avg", null, null, 'x', true, true, null, null, true, 1, 0);
                        bubble(dd.rsi14w_d, "tabubblecontainerA3", 1, 'RSI Weekly vs RSI Daily', subtitle, "", "RSI Weekly", "RSI Daily", null, null, '', false, false, false, false, true, 50, 50);
                    }, 100);
				});
				tab1loaded = true;*/
            }
            $('.nav-tabs a[href="#tab-1"]').tab('show');
            $('.nav-tabs a[href="#treetab-1"]').tab('show');
        }
        else if (!tab1loaded &&
                !tab2loaded &&
                !tab3loaded &&
                !tab4loaded &&
                !tab5loaded &&
                !tab6loaded &&
                !tab7loaded &&
                !tab8loaded &&
                !tab9loaded &&
                !tab10loaded)
        {
            //if (!window.location.href.endsWith("#treetab-1") && tab1loaded == false)
            const hash = window.location.hash; // gets #tab-3 or #treetab-2
            var x;
            if (hash.startsWith('#tab-') || hash.startsWith('#treetab-')) {
                const match = hash.match(/#(?:tab|treetab)-(\d+)/);
                if (match) {
                    x = match[1]; // x is the number after tab- or treetab-
                    scrollPosition = window.scrollY;
                    window.location.href = '#treetab-' + x;
                }
            }
            else if (initialTab)
            {
                x = initialTab;
            }
            else if (initialTreeTab)
            {
                x = initialTreeTab;
            }
            else
            {
                x = "1";
            }
            $('.nav-tabs a[href="#tab-'+x+'"]').tab('show');
            $('.nav-tabs a[href="#treetab-'+x+'"]').tab('show');
        }
        else
        {
            if (tab1loaded)
            {
                $('.nav-tabs a[href="#tab-1"]').tab('show');
                $('.nav-tabs a[href="#treetab-1"]').tab('show');
            }
            else if (tab2loaded)
            {
                $('.nav-tabs a[href="#tab-2"]').tab('show');
                $('.nav-tabs a[href="#treetab-2"]').tab('show');
            }
            else if (tab3loaded)
            {
                $('.nav-tabs a[href="#tab-3"]').tab('show');
                $('.nav-tabs a[href="#treetab-3"]').tab('show');
            }
            else if (tab4loaded)
            {
                $('.nav-tabs a[href="#tab-4"]').tab('show');
                $('.nav-tabs a[href="#treetab-4"]').tab('show');
            }
            else if (tab5loaded)
            {
                $('.nav-tabs a[href="#tab-5"]').tab('show');
                $('.nav-tabs a[href="#treetab-5"]').tab('show');
            }
            else if (tab6loaded)
            {
                $('.nav-tabs a[href="#tab-6"]').tab('show');
                $('.nav-tabs a[href="#treetab-6"]').tab('show');
            }
            else if (tab7loaded)
            {
                $('.nav-tabs a[href="#tab-7"]').tab('show');
                $('.nav-tabs a[href="#treetab-7"]').tab('show');
            }
            else if (tab8loaded)
            {
                $('.nav-tabs a[href="#tab-8"]').tab('show');
                $('.nav-tabs a[href="#treetab-8"]').tab('show');
            }
            else if (tab9loaded)
            {
                $('.nav-tabs a[href="#tab-9"]').tab('show');
                $('.nav-tabs a[href="#treetab-9"]').tab('show');
            }
            else if (tab10loaded)
            {
                $('.nav-tabs a[href="#tab-10"]').tab('show');
                $('.nav-tabs a[href="#treetab-10"]').tab('show');
            }
        }
    }
    
    if (!isMobile) // this doesn't work after hashchanged, no solution yet
    {
        setTimeout(function () {
            document.getElementById("autocomplete").focus();
        }, 0);
    }
});

async function fetchLatestVideo() {
    
    try {
        $.ajax({
            url : "youtube_get.php",
            success : function(result){
                var fields = result.split(';');
                
                if (fields.length == 3)
                {
                    var videoId = fields[0];
                    var title = fields[1];
                    var description = fields[2];
                    
                    var stocks = [];
                    var stock = '';
                    var found = false;
                    var regex = /[^A-Za-z0-9&.-]/gm;
                    for (let i = 0; i < description.length; i++)
                    {
                        if (found)
                        {
                            stock += description.charAt(i);
                        }
                        
                        if (description.charAt(i) == '(')
                        {
                            found = true;
                        }
                        
                        if (description.charAt(i) == ')')
                        {
                            found = false;
                            if (!regex.test(stock.replace(')', '')))
                                stocks.push(stock.replace(')', ''));
                            stock = '';
                        }
                    }
                    
                    for (let i = 0; i < stocks.length; i++)
                    {
                        description = description.replace('(' + stocks[i] + ')', '(<a href="stockinfo?code=' + encodeURIComponent(stocks[i]) + '">' + stocks[i] + '</a>)')
                    }
                    
                    embedVideo(videoId, 'latest-video-container', title, description, 'video-details', 'video-title', 'video-description');
                    
                    document.getElementById("youtube").style.display  = "";
                }
            }
        });    
    } catch (error) {
        console.error('Error fetching the latest video:', error);
    }
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

// Fetch and embed the latest video when the page loads
document.addEventListener('DOMContentLoaded', fetchLatestVideo);

function pauseScrolling() {
    $('#news-bar').addClass('paused');
}

function resumeScrolling() {
    $('#news-bar').removeClass('paused');
}

function resetScrolling() {
  const scrollEl = document.getElementById('news-scroll');
  scrollEl.style.animation = 'none'; // Step 1: stop animation
  void scrollEl.offsetWidth;         // Step 2: trigger reflow
  scrollEl.style.animation = '';     // Step 3: restore animation (starts from beginning)
}

$(function () {
  const scroll = $('#news-scroll');

  $.ajax({
    url: 'db_news_get.php',
    method: 'GET',
    success: function (data) {
        var dictcode = new Object();
        if (!data || data.length === 0) {
            setTimeout(() => {
                document.getElementById("maincard").style.margin = "70px 0px 0px 0px";
                document.getElementById("maincard").style.paddingRight = "0px";
                document.getElementById("maincard").style.paddingLeft = "0px";
                document.getElementById("newscard").style.display = "none";
            }, 2500);
            return;
        }
    
        scroll.empty();
        
        // 1. Create a memory fragment to hold HTML before injection
        const fragment = $(document.createDocumentFragment());
    
        data.forEach(item => {
            const container = $('<span class="news-item">');
            const sparkline = '<span title="6-month chart" data-tooltip="6-month chart" class="sparkline" id="sparkline_' + item.code + '"></span> ';
            
            if (item.code) {
                const codeLink = $('<a>')
                    .addClass('code-link')
                    .attr('href', `/stockinfo?code=${item.code}`)
                    .attr('target', '_blank')
                    .text(`${item.code}`);
                container.append(codeLink);
                container.append(' \u25CF ');
                container.append(sparkline);
                
                if (item.closeSeries !== null && item.close !== null) {
                    var closeSeries = item.closeSeries;
                    closeSeries = (closeSeries.endsWith(',' + item.close) ? closeSeries : closeSeries + ',' + item.close);
                    dictcode[item.code] = closeSeries;
                }
            }
            
            const titleLink = $('<a>')
                .addClass('title-link')
                .text(item.title)
                .click(function () {
                    const titleHTML = (item.code ? ` <a class="code-link2" href="/stockinfo?code=${item.code}" target="_blank">${item.code}</a> \u25CF ` + sparkline.replace("sparkline_", "sparkline1_") : '') + item.title;
                    $('#modal-title').html(titleHTML);
                    $('#modal-date').text(getWeekdayUTC(item.date));
                    $('#modal-content').text(item.content);
                    $('#modal-overlay').fadeIn();
                    
                    if (item.code) {
                        $("#sparkline1_" + item.code).sparkline(dictcode[item.code].split(",").map(Number), { type: 'line', spotRadius: 0, disableInteraction: true});
                        var sparklineElement = document.getElementById("sparkline1_" + item.code);
                        sparklineElement.removeEventListener("touchstart", handleSparklineTouch);
                        sparklineElement.addEventListener("touchstart", handleSparklineTouch);
                    }
                    pauseScrolling();
                });
                
            container.append(titleLink);
            fragment.append(container); // Append to memory, not the screen
        });
    
        // 2. Inject everything into the page at once
        scroll.append(fragment);
        resetScrolling();
    
        // 3. Stagger sparkline rendering so the browser doesn't freeze
        requestAnimationFrame(() => {
            Object.entries(dictcode).forEach(([code, eods]) => {
                $("#sparkline_" + code).sparkline(eods.split(",").map(Number), { 
                    type: 'line', 
                    spotRadius: 0, 
                    disableInteraction: true
                });
            });
        });
    },
    error: function () {
      scroll.text('Failed to load news.');
      setTimeout(() => {
        document.getElementById("maincard").style.margin = "70px 0px 0px 0px";
        document.getElementById("maincard").style.paddingRight = "0px";
        document.getElementById("maincard").style.paddingLeft = "0px";
        document.getElementById("newscard").style.display  = "none";
      }, 2500);
    }
  });

  $('.close').click(function () {
    $('#modal-overlay').fadeOut();
    resumeScrolling();
  });

  $('#modal-overlay').click(function (e) {
    if (e.target.id === 'modal-overlay') {
      $(this).fadeOut();
      resumeScrolling();
    }
  });

  $(window).on('keydown', function (e) {
    if (e.key === 'Escape') {
      $('#modal-overlay').fadeOut();
      resumeScrolling();
    }
  });
});

function getWeekdayUTC(dateStr) {
  const [year, month, day] = dateStr.split('-').map(Number);
  const date = new Date(Date.UTC(year, month - 1, day)); // treat as UTC
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const weekday = days[date.getUTCDay()];
  return `${dateStr} ${weekday}`;
}

document.addEventListener("DOMContentLoaded", function () {
    var scroller = document.getElementById("news-scroll");
    scroller.addEventListener("touchstart", handleSparklineTouch);    
});

function handleSparklineTouch(e) {
    const target = e.target.closest(".sparkline");
    if (!target) return;

    if (e.cancelable) e.preventDefault();

    document.querySelector(".tooltip-bubble")?.remove();

    const tooltip = document.createElement("div");
    tooltip.className = "tooltip-bubble";
    tooltip.textContent = target.getAttribute("data-tooltip");
    document.body.appendChild(tooltip);

    requestAnimationFrame(() => {
        const rect = target.getBoundingClientRect();
        tooltip.style.top = `${rect.top + window.scrollY - tooltip.offsetHeight - 8}px`;
        tooltip.style.left = `${rect.left + window.scrollX + rect.width / 2 - tooltip.offsetWidth / 2}px`;
        tooltip.style.opacity = 1;

        pauseScrolling();
        setTimeout(() => {
            resumeScrolling();
            tooltip.remove();
        }, 2000);
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
