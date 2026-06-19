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
window.__ECON_PAGE_CONFIG = {
    "exchangeName": <?php echo json_encode($exchangeName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "country": <?php echo json_encode($country, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "indexname": <?php echo json_encode($indexname, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/pages/econ-main.js?v=20260619.2"></script>

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
