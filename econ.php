<?php
    $country = strtoupper(isset($_GET["country"]) ? $_GET["country"] : 'US');
    $indexname = "";
    $exchangeName = "";
    
    if ($country == "HK")
    {
        $exchangeName = "Hong Kong";
    }
    else if ($country == "US" || $country == "")
    {
        //$data = "NYSE";
        $exchangeName = $country;
        $country = "US";
        $indexname = "S&P 500";
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
    <meta property="og:title" content="<?php echo $exchangeName . " Econ Indicators - 360MiQ.com"; ?>" />
    <meta property="og:url" content="https://360miq.com/econ" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
    <meta name="description" content="360MiQ.com Econ Indicators: Labor, Growth, Inflation, Interest Rate, Consumer & Business, Gov Finance, Trade, Housing." />
    <meta property="og:description" content="360MiQ.com Econ Indicators: Labor, Growth, Inflation, Interest Rate, Consumer & Business, Gov Finance, Trade, Housing." />

    <title><?php echo $exchangeName . " Econ Indicators - 360MiQ.com"; ?></title>
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
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="assets/js/Utils.js"></script>
<!--script src="https://code.jquery.com/jquery-3.5.1.js"></script //if dataTable weird, may need this js--> 
<script>
var econCriticalRequests = {
    labor: $.ajax({
        url: "db_econ_get.php?country=<?php echo rawurlencode($country); ?>&data=labor",
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

<?php $page = 'econ'; include "./header.php" ?>

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
            <h1 class="sr-only"><?php echo $exchangeName . " "; ?>Econ Indicators - Labor, Growth, Inflation, Interest Rate, Consumer & Business, Gov Finance, Trade, Housing</h1>
            <div class="container" style="padding: 30px 0px 0px;">
                <div style="text-align:center;font-size:1.7rem;font-weight:500;margin: 70px 0 0;"><?php echo $exchangeName." "; ?>Econ Indicators</div>
                <div class="card clean-card text-center container" style="margin: 0px 0 0;padding-right:0;padding-left:0">
                    <div class="panel panel-default" style="margin: 5px 0 0;">
                        <ul class="nav nav-tabs panel-heading not-selectable">
                            <li class="nav-item"><a class="nav-link active mynav-link" role="tab" data-toggle="tab" href="#tab-1">Labor</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-2">Growth</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-3">Inflation</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-4">Interest Rate</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-5">Consumer & Business</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-6">Gov Finance</a></li>
                            <li class="nav-item" <?php if ($country != "HK") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-7">Housing</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-8">Trade</a></li>
                            <li class="nav-item" <?php if ($country != "US") echo " style='display: none'"; ?>><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-9">Gold</a></li>
                        </ul>
                        <div class="tab-content panel-body">
                            <div class="tab-pane active" role="tabpanel" id="tab-1">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?>Labor Market</h2>
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
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Unemployment Rate</h3>
                                    <div id="chartcontainerA1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A1Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php if ($exchangeName == "US") echo $exchangeName." All Employees, Total Nonfarm ('000)"; else if ($exchangeName == "Hong Kong") echo $exchangeName." Underemployment Rate (%)";?></h3>
                                    <div id="chartcontainerA2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A2Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php if ($exchangeName == "US") echo $exchangeName." Job Openings: Total Nonfarm ('000)"; else if ($exchangeName == "Hong Kong") echo $exchangeName." Labor Force ('000)";?></h3>
                                    <div id="chartcontainerA3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A3Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php if ($exchangeName == "US") echo $exchangeName." Average Hourly Earnings ($)"; else if ($exchangeName == "Hong Kong") echo $exchangeName." Labor Force YoY (%)";?></h3>
                                    <div id="chartcontainerA4" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A4Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." Labor Force Participation Rate (%)";?></h3>
                                    <div id="chartcontainerA5" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A5Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." Employment-Population Ratio (%)";?></h3>
                                    <div id="chartcontainerA6" class="not-selectable" style="height: 525px;<?php if ($country != "US") echo "display: none"; ?>"></div>
                                </div>
                                <div class="chartNote" <?php if ($country != "US") echo " style='display: none'"; ?>>
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="A6Note">
                                    </div>
                                </div>
                                <!--hr>
                                <div class="text-center" style="margin: 15px 0;"></div>
                                <div>
                                    <div id="chartcontainerA7" class="not-selectable" style="height: 525px;"></div>
                                </div-->
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-2">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Econ Growth</h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Real GDP Growth Rate (%)</h3>
                                    <div id="chartcontainerB1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="B1Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Real GDP ($ billion)</h3>
                                    <div id='chartcontainerB2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="B2Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Real Potential GDP ($ billion)</h3>
                                    <div id='chartcontainerB3' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="B3Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Gross Domestic Product (GDP) ($ billion)</h3>
                                    <div id='chartcontainerB4' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="B4Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Gross Domestic Income ($ billion)</h3>
                                    <div id='chartcontainerB5' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="B5Note">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-3">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Inflation</h2>
                                <div>
                                    <h3 class="sr-only">University of Michigan:<?php echo " ".$exchangeName." ";?>Inflation Expectation (%)</h3>
                                    <div id="chartcontainerC1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C1Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Sticky Price CPI less Food and Energy (YoY %)</h3>
                                    <div id='chartcontainerC2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C2Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Consumer Price Index (CPI) for All Urban Consumers: All Items in U.S. City Average</h3>
                                    <div id='chartcontainerC3' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C3Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Producer Price Index (PPI) All Commodities</h3>
                                    <div id='chartcontainerC4' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C4Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Core PCE Price Index</h3>
                                    <div id='chartcontainerC5' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C5Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Personal Consumption Expenditures (PCE) ($ billion)</h3>
                                    <div id='chartcontainerC6' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="C6Note">
                                    </div>
                                </div>
                                
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-4">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Interest Rate</h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Federal Funds Effective Rate (%)</h3>
                                    <div id="chartcontainerD1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D1Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>3-Month Treasury Bill Secondary Market Rate, Discount Basis (%)</h3>
                                    <div id='chartcontainerD2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D2Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>10-Year Treasury Yield (%)</h3>
                                    <div id='chartcontainerD3' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D3Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>30-Year Mortgage Rate (%)</h3>
                                    <div id='chartcontainerD4' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D4Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Bank Prime Loan Rate (%)</h3>
                                    <div id='chartcontainerD5' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D5Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>M2 ($ billion)</h3>
                                    <div id='chartcontainerD6' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="D6Note">
                                    </div>
                                </div>
                                
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-5">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Consumer & Business Activity</h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $country." "; ?>Economic Surprise Index</h3>
                                    <div id="chartcontainerE1" class="not-selectable" style="height: 825px;"></div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only">University of Michigan:<?php echo " ".$exchangeName." ";?>Consumer Sentiment</h3>
                                    <div id="chartcontainerE2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E2Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Manufacturers' New Orders: Durable Goods ($ million)</h3>
                                    <div id="chartcontainerE3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E3Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Existing Home Sales</h3>
                                    <div id="chartcontainerE4" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E4Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>New One Family Homes for Sale ('000)</h3>
                                    <div id="chartcontainerE5" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E5Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>New Privately-Owned Housing Units Started: Total Units ('000)</h3>
                                    <div id="chartcontainerE6" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E6Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>New Privately-Owned Housing Units Authorized in Permit-Issuing Places: Total Units ('000)</h3>
                                    <div id="chartcontainerE7" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E7Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Motor Vehicle Retail Sales: Heavy Weight Trucks (million)</h3>
                                    <div id="chartcontainerE8" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E8Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Total Business Inventories ($ million)</h3>
                                    <div id="chartcontainerE9" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="E9Note">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-6">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Government Finance</h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Federal Debt: Total Public Debt as Percent of GDP</h3>
                                    <div id="chartcontainerF1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="F1Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class='text-center' style='margin: 15px 0;'><!--<a href='#'><img src='assets/img/Ad_h.png'></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Government Consumption Expenditures and Gross Investment</h3>
                                    <div id='chartcontainerF2' class='not-selectable' style='height: 525px;'></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="F2Note">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-7">
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Housing Market</h2>
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
                                <hr>
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
                                <h2 style="text-align:center; margin:15px 0;"><?php echo $exchangeName." ";?> Trade Balance</h2>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Current Account Balance</h3>
                                    <div id="chartcontainerH1" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H1Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Trade Balance: Goods and Services, Balance of Payments Basis ($ million)</h3>
                                    <div id="chartcontainerH2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H2Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Trade Balance: Goods, Balance of Payments Basis ($ million)</h3>
                                    <div id="chartcontainerH3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H3Note">
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>Trade Balance: Services, Balance of Payments Basis ($ million)</h3>
                                    <div id="chartcontainerH4" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H4Note">
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <h3 class="sr-only"><?php echo $exchangeName." ";?>U.S. Dollar Index</h3>
                                    <div id="chartcontainerH5" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div class="chartNote">
                                    <div class="showNote">Show more</div>
                                    <div class="noteWrapper collapsed" id="H5Note">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-9">
                                <h2 style="text-align:center; margin:15px 0;"><?php if ($exchangeName == "US") echo "Gold Price Since 1920"; ?></h2>
                                <div>
                                    <h3 class="sr-only">Gold Price Daily Since 1920</h3>
                                    <div id="chartcontainerI1" class="not-selectable" style="height: 525px;"></div>
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
                                    <div id="chartcontainerI2" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="weeklyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <hr>
                                <div>
                                    <h3 class="sr-only">Gold Price Monthly Since 1920</h3>
                                    <div id="chartcontainerI3" class="not-selectable" style="height: 525px;"></div>
                                </div>
                                <div id="monthlyReturnComment1920" style="text-align:left;padding-right: 15px"></div>
                                <hr>

                                <div>
                                    <h3 class="sr-only">Gold Price Yearly Since 1920</h3>
                                    <div id="chartcontainerI4" class="not-selectable" style="height: 525px;"></div>
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
<script src="assets/js/yearlyTrendChart.js"></script>
<script src="assets/js/seasonality.js"></script>
<script src="assets/js/highcharts-theme.js"></script>

<!--script src="https://code.jquery.com/jquery-1.9.1.min.js"></script // commented out for DataTables -->
<script>
function loadEconScript(src)
{
    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadEconStyle(href)
{
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

var econOptionalLibrariesLoaded = false;
var econOptionalLibrariesReady = null;

function ensureEconOptionalLibraries()
{
    if (econOptionalLibrariesReady)
        return econOptionalLibrariesReady;

    loadEconStyle('https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css');
    loadEconStyle('assets/css/dataTables.bootstrap4.min.css');
    loadEconStyle('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');

    econOptionalLibrariesReady = loadEconScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js')
        .then(function() {
            return loadEconScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js');
        })
        .then(function() {
            return loadEconScript('https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js');
        })
        .then(function() {
            return loadEconScript('https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js');
        })
        .then(function() {
            return loadEconScript('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js');
        })
        .then(function() {
            econOptionalLibrariesLoaded = true;
        });

    return econOptionalLibrariesReady;
}
</script>
<script>
var exchangeName = "<?php echo $exchangeName; ?>";
var country = "<?php echo $country; ?>";
var indexname = "<?php echo $indexname; ?>";

var HKlaborSubtitle = 'HK Census and Statistics Department';
var HKpropertySubtitle = 'HK Rating and Valuation Department';
var tab1loaded = false;
var daydict = {"1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
var monthdict = {"b":"01", "c":"02", "d":"03", "e":"04", "f":"05", "g":"06", "h":"07", "i":"08", "j":"09", "k":"10", "l":"11", "m":"12"};
var creditY = -388;
var codeStrSQL = '';

// Render expensive charts one at a time and only when they are close to the viewport.
// This replaces empty AJAX requests that previously reloaded the current page.
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
function tab1()
{
    if (tab1loaded)
        return;

    tab1loaded = true;
    var dict = new Object();

    document.getElementById("failedtable").style.display = "none";
    $('#loader-icon').show();
    $('#loader-tip').show();

    econCriticalRequests.labor.done(function(result){
            var dictnote = new Object();
            
            var jsonObject = result.split(/\r?\n|\r/);
            
            if (country == "US")
            {
                for (var i = 0; i < jsonObject.length; i++) {
                    if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                        jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                        
                    var row = jsonObject[i].split('¤')
                    if (i == 0)
                    {
                        row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                    }
                    else if (row.length == 5)
                    {
                        if (row[1].length == 1)
                            row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                        else if (row[1].length == 3)
                            row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                        else if (row[1].length == 5 || row[1].length == 7)
                            row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];

                        row[1] = row[1].replace("-", "");
                    }
                    
            	    if (row.length == 5)
            	    { 
            	        if (row[0] != "")
            	        {
            	            if (row[0] == 'Recession')
            	                row[0] += '_plotBands';
            	            previousName = row[0];
            	            
            	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
            	        }
        	            else
        	            {
        	                row[0] = previousName;
        	            }
        	                
        	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                        var previousDate = row[1];
    
                        if (dict[row[0]]) {
                             dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                        } else {
                            dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                        }
            	    }
                }
                
                var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                var rangeselector = 6;
                var creditYoffset = 19;
                if (browserwidth > 576)
                {
                    rangeselector = 6;
                    creditYoffset = 0;
                }
                highstock('chartcontainerA1', dict, [Object.keys(dict)[5], 'Recession_plotBands'], Object.keys(dict)[5], exchangeName, Object.keys(dict)[5], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                document.getElementById("A1Note").innerHTML = dictnote[Object.keys(dict)[5]];

                renderedChartIds.add('chartcontainerA1');

                lazyRenderChart('chartcontainerA2', function() {
                    highstock('chartcontainerA2', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A2Note").innerHTML = dictnote[Object.keys(dict)[4]];
                });

                lazyRenderChart('chartcontainerA3', function() {
                    highstock('chartcontainerA3', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A3Note").innerHTML = dictnote[Object.keys(dict)[3]];
                });

                lazyRenderChart('chartcontainerA4', function() {
                    highstock('chartcontainerA4', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A4Note").innerHTML = dictnote[Object.keys(dict)[0]];
                });

                lazyRenderChart('chartcontainerA5', function() {
                    highstock('chartcontainerA5', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A5Note").innerHTML = dictnote[Object.keys(dict)[1]];
                });

                lazyRenderChart('chartcontainerA6', function() {
                    highstock('chartcontainerA6', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2].replace(" (JOLTS)", ""), "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A6Note").innerHTML = dictnote[Object.keys(dict)[2]];
                });
            }
            else if (country == "HK")
            {
                for (var i = 0; i < jsonObject.length; i++) {

                    var row = jsonObject[i].split('¤')

            	    if (row.length == 7)
            	    { 
                        if (dict['Unemployment Rate (%)']) {
                             dict['Unemployment Rate (%)'].push([new Date(row[0]).getTime(), row[1]/1]);
                        } else {
                            dict['Unemployment Rate (%)'] = [[new Date(row[0]).getTime(), row[1]/1]];
                        }
                        
                        if (dict['Unemployment Rate Seasonally Adjusted (%)']) {
                             dict['Unemployment Rate Seasonally Adjusted (%)'].push([new Date(row[0]).getTime(), row[2]/1]);
                        } else {
                            dict['Unemployment Rate Seasonally Adjusted (%)'] = [[new Date(row[0]).getTime(), row[2]/1]];
                        }
                        
                        if (dict['Underemployment Rate (%)']) {
                             dict['Underemployment Rate (%)'].push([new Date(row[0]).getTime(), row[3]/1]);
                        } else {
                            dict['Underemployment Rate (%)'] = [[new Date(row[0]).getTime(), row[3]/1]];
                        }
                        
                        if (dict["Labor Force ('000)"]) {
                             dict["Labor Force ('000)"].push([new Date(row[0]).getTime(), row[4]/1]);
                        } else {
                            dict["Labor Force ('000)"] = [[new Date(row[0]).getTime(), row[4]/1]];
                        }
                        
                        if (dict['Labor Force YoY (%)']) {
                             dict['Labor Force YoY (%)'].push([new Date(row[0]).getTime(), row[5]/1]);
                        } else {
                            dict['Labor Force YoY (%)'] = [[new Date(row[0]).getTime(), row[5]/1]];
                        }
                        
                        if (dict['Labor Force Participation Rate (%)']) {
                             dict['Labor Force Participation Rate (%)'].push([new Date(row[0]).getTime(), row[6]/1]);
                        } else {
                            dict['Labor Force Participation Rate (%)'] = [[new Date(row[0]).getTime(), row[6]/1]];
                        }
            	    }
                }
                
                var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                var rangeselector = 6;
                var creditYoffset = 19;
                if (browserwidth > 576)
                {
                    rangeselector = 6;
                    creditYoffset = 0;
                }
                highstock('chartcontainerA1', dict, ['Unemployment Rate (%)', 'Unemployment Rate Seasonally Adjusted (%)'], 'Unemployment Rate (%)', HKlaborSubtitle, 'Rate (%)', "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                document.getElementById("A1Note").innerHTML = "<span>Hong Kong’s unemployment rate, published by the Census and Statistics Department, measures the percentage of the labor force that is jobless and actively seeking work. The raw (unadjusted) figure reflects the actual reported jobless rate for each period, while the <strong>seasonally adjusted</strong> unemployment rate accounts for predictable fluctuations in hiring and layoffs throughout the year — such as post-holiday slowdowns or summer hiring cycles.</span><p></p><span>Analysts and policymakers often focus on the seasonally adjusted rate for a clearer picture of underlying labor market trends. A rising seasonally adjusted unemployment rate may indicate weakening economic momentum, while a falling rate can confirm recovery or resilience. Comparing the adjusted and unadjusted figures can also help detect short-term shocks versus structural shifts.</span>";

                renderedChartIds.add('chartcontainerA1');

                lazyRenderChart('chartcontainerA2', function() {
                    highstock('chartcontainerA2', dict, ['Underemployment Rate (%)'], 'Underemployment Rate (%)', HKlaborSubtitle, 'Underemployment Rate (%)', "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A2Note").innerHTML = "<span>The underemployment rate in Hong Kong reflects the percentage of the labor force that is working less than 35 hours per week during the reference period and is available and willing to take on more work. It captures individuals who are not fully utilizing their skills or capacity, even if they are technically employed.</span><p></p><span>This indicator complements the unemployment rate by revealing hidden slack in the labor market. A rising underemployment rate may signal weakening demand for labor or cautious hiring practices, even when the headline unemployment rate remains stable. Investors and analysts often monitor both figures together to assess the true strength and flexibility of the job market.</span>";
                });

                lazyRenderChart('chartcontainerA3', function() {
                    highstock('chartcontainerA3', dict, ["Labor Force ('000)"], "Labor Force ('000)", HKlaborSubtitle, "Labor Force ('000)", "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A3Note").innerHTML = "<span>The labor force (in thousands) represents the total number of individuals aged 15 and over who are either employed or actively seeking employment in Hong Kong. This includes both full-time and part-time workers, as well as those temporarily unemployed but still participating in the job market.  </span><p></p><span>Tracking changes in the labor force helps assess the availability of labor and the underlying strength of the economy. A growing labor force may indicate demographic expansion or increased participation due to better job prospects. Conversely, a shrinking labor force can signal aging population trends, discouraged workers exiting the market, or structural changes. When analyzed alongside employment and unemployment data, it provides essential context for understanding shifts in labor market dynamics.</span>";
                });

                lazyRenderChart('chartcontainerA4', function() {
                    highstock('chartcontainerA4', dict, ['Labor Force YoY (%)'], 'Labor Force YoY (%)', HKlaborSubtitle, 'Labor Force YoY (%)', "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A4Note").innerHTML = "<span>The labor force YoY (%) shows the annual percentage change in the total number of people available for work, including both employed individuals and active job seekers. This indicator captures the growth or contraction of the workforce over time.</span><p></p><span>Monitoring labor force YoY helps identify structural trends such as demographic shifts, changes in immigration patterns, and evolving labor market participation. A consistently positive YoY rate may reflect economic optimism or population growth, while a negative trend could indicate an aging population, reduced labor participation, or a declining working-age population. It's often used in conjunction with employment and unemployment indicators to evaluate long-term labor market health and sustainability.</span>";
                });

                lazyRenderChart('chartcontainerA5', function() {
                    highstock('chartcontainerA5', dict, ['Labor Force Participation Rate (%)'], 'Labor Force Participation Rate (%)', HKlaborSubtitle, 'Labor Force Participation Rate (%)', "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                    document.getElementById("A5Note").innerHTML = "<span>The labor force participation rate (%) measures the proportion of the working-age population (typically aged 15 and above) that is either employed or actively looking for work. It provides insight into how engaged the population is with the labor market, beyond just whether people are employed.</span><p></p><span>In contrast, the unemployment rate measures the percentage of the labor force that is unemployed but actively seeking work. This means the unemployment rate only reflects those already participating in the labor market, while the participation rate captures the broader context — including those who may have dropped out entirely.</span><p></p><span>A high unemployment rate alongside a stable or rising labor force participation rate suggests that, despite economic challenges, people are still actively looking for work rather than dropping out of the job market. On the other hand, a falling participation rate alongside low unemployment could mask hidden labor market slack. Understanding both metrics together offers a fuller picture of labor dynamics and workforce availability.</span>";
                });
            }
    }).fail(function(error) {
        document.getElementById("failedtable").style.visibility = "visible";
        document.getElementById("failedcell").textContent = "Failed to load. Please refresh the page.";
    }).always(function() {
        $('#loader-icon').hide();
        $('#loader-tip').hide();
    });
}

    var tab2loaded = false, tab3loaded = false, tab4loaded = false, tab5loaded = false, tab6loaded = false, tab7loaded = false, tab8loaded = false, tab9loaded = false; 
    $(function() {
        openTabHash(); // for the initial page load
        window.addEventListener("hashchange", openTabHash, false); // for later changes to url
        
    });

    var econOptionalTabWaiting = false;

    function openTabHash()
    {
        console.log('openTabHash');
        
        // Javascript to enable link to tab
        const urlNew = new URL(window.location);
        const params = urlNew.searchParams;
        let tab = params.get('tab');
        var hashTabMatch = window.location.hash.match(/^#tab-(\d+)$/);
        var requestedTab = tab || (hashTabMatch ? hashTabMatch[1] : "1");

        if (requestedTab == "9" && !econOptionalLibrariesLoaded)
        {
            if (econOptionalTabWaiting)
                return;

            econOptionalTabWaiting = true;
            ensureEconOptionalLibraries().then(function() {
                econOptionalTabWaiting = false;
                openTabHash();
            }).catch(function(error) {
                econOptionalTabWaiting = false;
                if (window.console && console.error)
                    console.error('Failed to load optional econ libraries.', error);
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
            //if (benchmark != "2800.HK" && benchmark != "159903.SZ" && benchmark != "510500.SH" && benchmark != "SPY" && benchmark != "QQQ")
            //    window.location.hash = "#tab-1";
            //else
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
            //if (benchmark != "2800.HK")
            //    window.location.hash = "#tab-1";
            //else
            {
                chartcontainer = "chartcontainerF1";
                tab6loaded = true;
            }
        }
        else if (window.location.hash == "#tab-7" || tab == "7")
        {
            //if (benchmark != "2800.HK")
            //    window.location.hash = "#tab-1";
            //else
            {
                chartcontainer = "chartcontainerG1";
                tab7loaded = true;
            }
        }
        else if (window.location.hash == "#tab-8" || tab == "8")
        {
            //if (benchmark != "SPY" && benchmark != "510500.SH" )
            //    window.location.hash = "#tab-1";
            //else
            {
                chartcontainer = "chartcontainerH1";
                tab8loaded = true;
            }
        }
        else if (window.location.hash == "#tab-9" || tab == "9")
        {
            //if (benchmark != "SPY" && benchmark != "510500.SH" )
            //    window.location.hash = "#tab-1";
            //else
            {
                chartcontainer = "chartcontainerI1";
                tab9loaded = true;
            }
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
                    url : "db_econ_get.php?country=" + country + "&data=growth",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        highstock('chartcontainerB1', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                        document.getElementById("B1Note").innerHTML = dictnote[Object.keys(dict)[0]];

                        renderedChartIds.add('chartcontainerB1');

                        lazyRenderChart('chartcontainerB2', function() {
                            highstock('chartcontainerB2', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("B2Note").innerHTML = dictnote[Object.keys(dict)[3]];
                        });

                        lazyRenderChart('chartcontainerB3', function() {
                            highstock('chartcontainerB3', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, '0_opposite');
                            document.getElementById("B3Note").innerHTML = dictnote[Object.keys(dict)[4]];
                        });

                        lazyRenderChart('chartcontainerB4', function() {
                            highstock('chartcontainerB4', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("B4Note").innerHTML = dictnote[Object.keys(dict)[2]];
                        });

                        lazyRenderChart('chartcontainerB5', function() {
                            highstock('chartcontainerB5', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("B5Note").innerHTML = dictnote[Object.keys(dict)[1]];
                        });
                    }
                });
            }
            else if (chartcontainer == "chartcontainerC1")
            {    
                //cchart(chartcontainer);  
                var dict = new Object();
                
                $.ajax({
                    url : "db_econ_get.php?country=" + country + "&data=inflation",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        highstock('chartcontainerC1', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                        document.getElementById("C1Note").innerHTML = dictnote[Object.keys(dict)[2]];

                        renderedChartIds.add('chartcontainerC1');

                        lazyRenderChart('chartcontainerC2', function() {
                            highstock('chartcontainerC2', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("C2Note").innerHTML = dictnote[Object.keys(dict)[0]];
                        });

                        lazyRenderChart('chartcontainerC3', function() {
                            highstock('chartcontainerC3', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("C3Note").innerHTML = dictnote[Object.keys(dict)[1]];
                        });

                        lazyRenderChart('chartcontainerC4', function() {
                            highstock('chartcontainerC4', dict, [Object.keys(dict)[5], 'Recession_plotBands'], Object.keys(dict)[5], exchangeName, Object.keys(dict)[5], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("C4Note").innerHTML = dictnote[Object.keys(dict)[5]];
                        });

                        lazyRenderChart('chartcontainerC5', function() {
                            highstock('chartcontainerC5', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("C5Note").innerHTML = dictnote[Object.keys(dict)[4]];
                        });

                        lazyRenderChart('chartcontainerC6', function() {
                            highstock('chartcontainerC6', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("C6Note").innerHTML = dictnote[Object.keys(dict)[3]];
                        });
                    }
                });
            }
            else if (chartcontainer == "chartcontainerD1")
            {    
                //cchart(chartcontainer);  
                var dict = new Object();
                
                $.ajax({
                    url : "db_econ_get.php?country=" + country + "&data=interest",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        highstock('chartcontainerD1', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                        document.getElementById("D1Note").innerHTML = dictnote[Object.keys(dict)[0]];

                        renderedChartIds.add('chartcontainerD1');

                        lazyRenderChart('chartcontainerD2', function() {
                            highstock('chartcontainerD2', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("D2Note").innerHTML = dictnote[Object.keys(dict)[2]];
                        });

                        lazyRenderChart('chartcontainerD3', function() {
                            highstock('chartcontainerD3', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("D3Note").innerHTML = dictnote[Object.keys(dict)[1]];
                        });

                        lazyRenderChart('chartcontainerD4', function() {
                            highstock('chartcontainerD4', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("D4Note").innerHTML = dictnote[Object.keys(dict)[3]];
                        });

                        lazyRenderChart('chartcontainerD5', function() {
                            highstock('chartcontainerD5', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("D5Note").innerHTML = dictnote[Object.keys(dict)[4]];
                        });

                        lazyRenderChart('chartcontainerD6', function() {
                            highstock('chartcontainerD6', dict, [Object.keys(dict)[5], 'Recession_plotBands'], Object.keys(dict)[5], exchangeName, Object.keys(dict)[5], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("D6Note").innerHTML = dictnote[Object.keys(dict)[5]];
                        });
                    }
                });
            }
            else if (chartcontainer == "chartcontainerE1")
            {    
                var dictSurprise = new Object();

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
                                    if (dictSurprise[country + ' Econ Surprise Index']) {
                                         dictSurprise[country + ' Econ Surprise Index'].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]);
                                    } else {
                                        dictSurprise[country + ' Econ Surprise Index'] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])/100]];
                                    }
                    	        }
                    	        else if (row[0] == "S" || row[0] == "1")
                    	        {
                                    if (dictSurprise[indexname]) {
                                         dictSurprise[indexname].push([new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]);
                                    } else {
                                        dictSurprise[indexname] = [[new Date(datedecrypt(row[1])).getTime(), parseFloat(row[2])]];
                                    }
                    	        }
                    	    }
                        }
                        
                        for(var i = 12; i < dictSurprise[indexname].length; i++)
            	        {
            	            if (dictSurprise[indexname + ' QoQ %']) {
                                 dictSurprise[indexname + ' QoQ %'].push([dictSurprise[indexname][i][0], rounding((dictSurprise[indexname][i][1]/dictSurprise[indexname][i-12][1] - 1) * 100, 10)]);
                            } else {
                                dictSurprise[indexname + ' QoQ %'] = [[dictSurprise[indexname][i][0], rounding((dictSurprise[indexname][i][1]/dictSurprise[indexname][i-12][1] - 1) * 100, 10)]];
                            }
            	        }
            	        
            	        if (dictSurprise[indexname][0][0] < dictSurprise[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dictSurprise[indexname][0][0] < dictSurprise[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dictSurprise[indexname].shift();
                	        }
            	        }
            	        else if (dictSurprise[indexname][0][0] > dictSurprise[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dictSurprise[indexname][0][0] > dictSurprise[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dictSurprise[country + ' Econ Surprise Index'].shift();
                	        }
            	        }
            	        
            	        if (dictSurprise[indexname + ' QoQ %'][0][0] < dictSurprise[country + ' Econ Surprise Index'][0][0])
            	        {
                	        while(dictSurprise[indexname + ' QoQ %'][0][0] < dictSurprise[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dictSurprise[indexname + ' QoQ %'].shift();
                	        }
            	        }   
                	    else if (dictSurprise[indexname + ' QoQ %'][0][0] > dictSurprise[country + ' Econ Surprise Index'][0][0])
                	    {
                	        while(dictSurprise[indexname + ' QoQ %'][0][0] > dictSurprise[country + ' Econ Surprise Index'][0][0])
                	        {
                	            dictSurprise[country + ' Econ Surprise Index'].shift();
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
                            
        		        Highcharts.stockChart('chartcontainerE1', {

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
                                data: dictSurprise[indexname],
                                lineWidth: 3,
                                shadow: true
                            }, {
                                type: 'line',
                                name: indexname + ' QoQ %',
                                id: 'QoQ',
                                zIndex: 2,
                                data: dictSurprise[indexname + ' QoQ %'],
                                yAxis: 1,
                                //lineWidth: 3
                            }, {
                                type: 'line',
                                name: country + ' Econ Surprise Index',
                                id: 'surprise',
                                data: dictSurprise[country + ' Econ Surprise Index'],
                                yAxis: 2,
                                color: 'green',
                                //lineWidth: 3,
                                //shadow: true
                            }]
                        });

                    }
                });
                
                //cchart(chartcontainer);  
                var dict = new Object();
                
                $.ajax({
                    url : "db_econ_get.php?country=" + country + "&data=consumerbusiness",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        lazyRenderChart('chartcontainerE2', function() {
                            highstock('chartcontainerE2', dict, [Object.keys(dict)[7], 'Recession_plotBands'], Object.keys(dict)[7], exchangeName, Object.keys(dict)[7], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E2Note").innerHTML = dictnote[Object.keys(dict)[7]];
                        });

                        lazyRenderChart('chartcontainerE3', function() {
                            highstock('chartcontainerE3', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E3Note").innerHTML = dictnote[Object.keys(dict)[1]];
                        });

                        lazyRenderChart('chartcontainerE4', function() {
                            highstock('chartcontainerE4', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E4Note").innerHTML = dictnote[Object.keys(dict)[2]];
                        });

                        lazyRenderChart('chartcontainerE5', function() {
                            highstock('chartcontainerE5', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E5Note").innerHTML = dictnote[Object.keys(dict)[3]];
                        });

                        lazyRenderChart('chartcontainerE6', function() {
                            highstock('chartcontainerE6', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E6Note").innerHTML = dictnote[Object.keys(dict)[4]];
                        });

                        lazyRenderChart('chartcontainerE7', function() {
                            highstock('chartcontainerE7', dict, [Object.keys(dict)[6], 'Recession_plotBands'], Object.keys(dict)[6], exchangeName, Object.keys(dict)[6], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E7Note").innerHTML = dictnote[Object.keys(dict)[6]];
                        });

                        lazyRenderChart('chartcontainerE8', function() {
                            highstock('chartcontainerE8', dict, [Object.keys(dict)[5], 'Recession_plotBands'], Object.keys(dict)[5], exchangeName, Object.keys(dict)[5], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E8Note").innerHTML = dictnote[Object.keys(dict)[5]];
                        });

                        lazyRenderChart('chartcontainerE9', function() {
                            highstock('chartcontainerE9', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("E9Note").innerHTML = dictnote[Object.keys(dict)[0]];
                        });
                    }
                });
            }
            else if (chartcontainer == "chartcontainerF1")
            {
                var dict = new Object();

                $.ajax({
                    url : "db_econ_get.php?country=" + country + "&data=gov",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        highstock('chartcontainerF1', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                        document.getElementById("F1Note").innerHTML = dictnote[Object.keys(dict)[1]];

                        renderedChartIds.add('chartcontainerF1');
                        lazyRenderChart('chartcontainerF2', function() {
                            highstock('chartcontainerF2', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("F2Note").innerHTML = dictnote[Object.keys(dict)[0]];
                        });
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

            		    highstock('chartcontainerG1', price, ['Residential', 'A', 'B', 'C', 'D', 'E', '# of Sale Used_stacked', '# of Sale New_stacked'], "Hong Kong Residential Price Indices by Class", HKpropertySubtitle, "Index Value", "# of Sale", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
                        renderedChartIds.add('chartcontainerG1');

                        lazyRenderChart('chartcontainerG2', function() {
                            highstock('chartcontainerG2', price, ['Residential', 'Office', 'Retail', 'Flatted Factory', '# of Sale Residential_column', '# of Sale Office_column', '# of Sale Commercial_column', '# of Sale Flatted Factory_column'], "Hong Kong Property Price Indices by Type", HKpropertySubtitle, "Index Value", "# of Sale", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
                        });

                        lazyRenderChart('chartcontainerG3', function() {
                            highstock('chartcontainerG3', rent, ['Residential', 'Office', 'Retail', 'Flatted Factory'], "Hong Kong Property Rental Indices by Type", HKpropertySubtitle, "Index Value", "", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
                        });

                        lazyRenderChart('chartcontainerG4', function() {
                            highstock('chartcontainerG4', rent_price_ratio, ['Residential', 'Office', 'Retail', 'Flatted Factory'], "Hong Kong Property Rent / Price Ratio by Type", HKpropertySubtitle, "Rent / Price Ratio", "", null, null, "day", 6, "line", 1, creditY + creditY_extra, null, 'linear', true);
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
                    url : "db_econ_get.php?country=" + country + "&data=trade",
                    success : function(result){
                        var dictnote = new Object();
                        
                        var jsonObject = result.split(/\r?\n|\r/);
                        for (var i = 0; i < jsonObject.length; i++) {
                            if ((jsonObject[i].match(/¤/g) || []).length == 1) // no data_name field
                                jsonObject[i] = "¤" + jsonObject[i] + "¤¤";
                                
                            var row = jsonObject[i].split('¤')
                            if (i == 0)
                            {
                                row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                            }
                            else if (row.length == 5)
                            {
                                if (row[1].length == 1)
                                    row[1] = previousDate.substring(0, 7) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 3)
                                    row[1] = previousDate.substring(0, 5) + row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];
                                else if (row[1].length == 5 || row[1].length == 7)
                                    row[1] = row[1].substring(0, row[1].length - 1) + daydict[row[1].substring(row[1].length - 1)];               
                                    
                                row[1] = row[1].replace("-", "");
                            }
                            
                    	    if (row.length == 5)
                    	    { 
                    	        if (row[0] != "")
                    	        {
                    	            if (row[0] == 'Recession')
                    	                row[0] += '_plotBands';
                    	            previousName = row[0];
                    	            
                    	            dictnote[row[0]] = (row[4] == "" ? "" : row[4] + "<br><br>") + row[3];
                    	        }
                	            else
                	            {
                	                row[0] = previousName;
                	            }
                	                
                	            row[1] = row[1].substr(0,4) + "-" + row[1].substr(4,2) + "-" + row[1].substr(6,2);
                                var previousDate = row[1];
            
                                if (dict[row[0]]) {
                                     dict[row[0]].push([new Date(row[1]).getTime(), row[2]/1]);
                                } else {
                                    dict[row[0]] = [[new Date(row[1]).getTime(), row[2]/1]];
                                }
                    	    }
                        }
                        
                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                        var rangeselector = 6;
                        var creditYoffset = 19;
                        if (browserwidth > 576)
                        {
                            rangeselector = 6;
                            creditYoffset = 0;
                        }
                        highstock('chartcontainerH1', dict, [Object.keys(dict)[4], 'Recession_plotBands'], Object.keys(dict)[4], exchangeName, Object.keys(dict)[4], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                        document.getElementById("H1Note").innerHTML = dictnote[Object.keys(dict)[4]];

                        renderedChartIds.add('chartcontainerH1');

                        lazyRenderChart('chartcontainerH2', function() {
                            highstock('chartcontainerH2', dict, [Object.keys(dict)[0], 'Recession_plotBands'], Object.keys(dict)[0], exchangeName, Object.keys(dict)[0], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("H2Note").innerHTML = dictnote[Object.keys(dict)[0]];
                        });

                        lazyRenderChart('chartcontainerH3', function() {
                            highstock('chartcontainerH3', dict, [Object.keys(dict)[1], 'Recession_plotBands'], Object.keys(dict)[1], exchangeName, Object.keys(dict)[1], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("H3Note").innerHTML = dictnote[Object.keys(dict)[1]];
                        });

                        lazyRenderChart('chartcontainerH4', function() {
                            highstock('chartcontainerH4', dict, [Object.keys(dict)[2], 'Recession_plotBands'], Object.keys(dict)[2], exchangeName, Object.keys(dict)[2], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("H4Note").innerHTML = dictnote[Object.keys(dict)[2]];
                        });

                        lazyRenderChart('chartcontainerH5', function() {
                            highstock('chartcontainerH5', dict, [Object.keys(dict)[3], 'Recession_plotBands'], Object.keys(dict)[3], exchangeName, Object.keys(dict)[3], "", null, null, "month", rangeselector, "line", 1, creditY + creditYoffset, null);
                            document.getElementById("H5Note").innerHTML = dictnote[Object.keys(dict)[3]];
                        });

                    }
                });
            }
            
            else if (chartcontainer == "chartcontainerI1")
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
                        
                        highstock('chartcontainerI1', dict, [indexname + ' Daily', "Daily Return %"], indexname + " (Daily Return %)", "", indexname, "Daily Return %", null, null, "day", rangeselector, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'month', count: 6, text: '6m'}, {type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:2, text:'2y'}, {type: 'year', count: 3, text: '3y'}, {type: 'year', count: 8, text: '8y'}, {type: 'all', text: 'All'}]);
                        document.getElementById("dailyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Daily'], dict['Daily Return %'], "days");
                        renderedChartIds.add('chartcontainerI1');

                        lazyRenderChart('chartcontainerI2', function() {
                            highstock('chartcontainerI2', dict, [indexname + ' Weekly', "Weekly Return %"], indexname + " (Weekly Return %)", "", indexname, "Weekly Return %", null, null, "day", 4, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'month', count: 6, text: '6m'}, {type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:2, text:'2y'}, {type: 'year', count: 3, text: '3y'}, {type: 'year', count: 8, text: '8y'}, {type: 'all', text: 'All'}]);
                            document.getElementById("weeklyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Weekly'], dict['Weekly Return %'], "weeks");
                        });

                        lazyRenderChart('chartcontainerI3', function() {
                            highstock('chartcontainerI3', dict, [indexname + ' Monthly', "Monthly Return %"], indexname + " (Monthly Return %)", "", indexname, "Monthly Return %", null, null, "day", 5, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'ytd', text: 'YTD'}, {type: 'year', count: 1, text: '1y'}, {type: 'year', count:5, text:'5y'}, {type: 'year', count: 10, text: '10y'}, {type: 'year', count: 20, text: '20y'}, {type: 'year', count: 30, text: '30y'}, {type: 'all', text: 'All'}]);
                            document.getElementById("monthlyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Monthly'], dict['Monthly Return %'], "months");
                        });

                        lazyRenderChart('chartcontainerI4', function() {
                            highstock('chartcontainerI4', dict, [indexname + ' Yearly', "Yearly Return %"], indexname + " (Yearly Return %)", "", indexname, "Yearly Return %", null, null, "day", 6, "column", 1, creditY + 2, null, 'logarithmic', false, true, 'close', Infinity, true, 'GOLD', [{type: 'year', count: 5, text: '5y'}, {type: 'year', count:10, text:'10y'}, {type: 'year', count: 25, text: '25y'}, {type: 'year', count: 50, text: '50y'}, {type: 'year', count: 100, text: '100y'}, {type: 'year', count: 150, text: '150y'}, {type: 'all', text: 'All'}]);
                            document.getElementById("yearlyReturnComment1920").innerHTML = returnComment(indexname, dict[indexname + ' Yearly'], dict['Yearly Return %'], "years");
                        });

                        lazyRenderChart('chartcontainerYearlyTrend1920', function() {
                            seasonality('chartcontainerYearlyTrend1920', dict, indexname, '#monthlytable1920', "#slider1920", 'monthlytabletitle1920', 'chartcontainerSeasonality1920', dict[indexname + ' Daily'][0][1], dict[indexname + ' Daily']);
                            isLoadedSuccessfully = true;
                        });
                    }
                });
            }
        }
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
    
    /*jQuery(window).load(function(){
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
    });*/

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
                !(this.hash == "#tab-9" && tab9loaded))
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

/*function openTOOL()
{
    var codeStr = encodeURIComponent(codeStrSQL);
    codeStr = codeStr.trim();
    if (codeStr.startsWith(','))
        codeStr = codeStr.substring(1);
    if (codeStr.endsWith(','))
        codeStr = codeStr.substring(0, codeStr.length - 1);
        
    location.href = "tool?code=" + codeStr.trim();
}*/

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


/*
$(".showNote").click(function () {

    $header = $(this);
    //getting the next element
    $content = $header.next();
    //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
    $content.slideToggle(500, function () {
        //execute this after slideToggle is done
        //change text of header based on visibility of content div
        $header.text(function () {
            //change text based on condition
            return $content.is(":visible") ? "Hide Note" : "Show Note";
        });
    });

});*/
</script>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/theme.js"></script>-->
    <!--<script src="assets/js/Anystock.js"></script>
    <script src="assets/js/CycleChart.js"></script>-->
    <!--script src="https://code.highcharts.com/maps/8.0.4/modules/map.js"></script-->
    <!--<script src="https://code.highcharts.com/stock/8.0.4/modules/pattern-fill.js"></script>-->
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
    <!--<script src="assets/js/yaxis-panning.js"></script>-->
</body>

</html>
