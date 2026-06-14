<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="Stock Screener - 360MiQ.com" />
    <meta property="og:url" content="https://360miq.com/screener" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
    <meta name="description" content="360MiQ.com Stock Screener. The screener can help you to screen your favorite stocks using both technical and fundamental criteria." />
    <meta property="og:description" content="360MiQ.com Stock Screener. The screener can help you to screen your favorite stocks using both technical and fundamental criteria." />

    <title>Stock Screener - 360MiQ.com</title>
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
    <link rel="stylesheet" href="assets/css/jquery-ui1.12.1.min.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <link rel="stylesheet" href="assets/css/inlinehelp2.css">

<style>
.dataTables_filter {
   display: none;
}
table#screener_grid.dataTable tbody tr:hover {
  background-color: #FEFFDF !important;
}
 
table#screener_grid.dataTable tbody tr:hover > .sorting_1 {
  background-color: #FEFFDF !important;
}

[data-theme="dark"] table#screener_grid.dataTable tbody tr:hover,
[data-theme="dark"] table#screener_grid.dataTable tbody tr:hover > td,
[data-theme="dark"] table#screener_grid.dataTable tbody tr:hover > .sorting_1 {
  background-color: #33335a !important;
}
</style>
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">-->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap4.min.css">
<!--<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">-->
<link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css">
<style>
.dataTables_length {
    margin-top:7px;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.dataTables_wrapper .dataTables_info {
    clear: both;
    float: left;
    padding-top: 0.755em
}

.dataTables_wrapper .dataTables_paginate {
    float: right;
    text-align: right;
    padding-top: 0.25em
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    box-sizing: border-box;
    display: inline-block;
    min-width: 1.5em;
    padding: 0.5em 0.5em;
    margin-left: 2px;
    text-align: center;
    text-decoration: none !important;
    cursor: pointer;
    *cursor: hand;
    color: #007bff !important;
    border: 0px solid transparent;
    border-radius: 2px
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    color: #007bff !important;
    border: 0px solid #979797;
    background-color: white;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #fff), color-stop(100%, #dcdcdc));
    background: -webkit-linear-gradient(top, #fff 0%, #dcdcdc 100%);
    background: -moz-linear-gradient(top, #fff 0%, #dcdcdc 100%);
    background: -ms-linear-gradient(top, #fff 0%, #dcdcdc 100%);
    background: -o-linear-gradient(top, #fff 0%, #dcdcdc 100%);
    background: linear-gradient(to bottom, #fff 0%, #dcdcdc 100%)
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
    cursor: default;
    color: #666 !important;
    border: 0px solid transparent;
    background: transparent;
    box-shadow: none
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    color: white !important;
    border: 0px solid #007bff;
    background-color: #007bff;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #007bff), color-stop(100%, #007bff));
    background: -webkit-linear-gradient(top, #007bff 0%, #007bff 100%);
    background: -moz-linear-gradient(top, #007bff 0%, #007bff 100%);
    background: -ms-linear-gradient(top, #007bff 0%, #007bff 100%);
    background: -o-linear-gradient(top, #007bff 0%, #007bff 100%);
    background: linear-gradient(to bottom, #007bff 0%, #007bff 100%)
}

.dataTables_wrapper .dataTables_paginate .paginate_button:active {
    outline: none;
    background-color: #2b2b2b;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #2b2b2b), color-stop(100%, #0c0c0c));
    background: -webkit-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
    background: -moz-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
    background: -ms-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
    background: -o-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
    background: linear-gradient(to bottom, #2b2b2b 0%, #0c0c0c 100%);
    box-shadow: inset 0 0 3px #007bff
}

.dataTables_wrapper .dataTables_paginate .ellipsis {
    padding: 0 1em
}

.dataTables_wrapper .dataTables_processing {
    z-index: 999999;
    background-color: #ffe;
}

.dataTables_wrapper .dataTables_length,.dataTables_wrapper .dataTables_filter,.dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_processing,.dataTables_wrapper .dataTables_paginate {
    color: #333
}
/*
table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc {
    cursor: pointer;
    *cursor: hand
}

table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc,table.dataTable thead .sorting_asc_disabled,table.dataTable thead .sorting_desc_disabled {
    background-repeat: no-repeat;
    background-position: bottom right;
}

table.dataTable thead .sorting {
    background-image: url("https://cdn.datatables.net/r/dt/dt-1.10.9/DataTables-1.10.9/images/sort_both.png")
}

table.dataTable thead .sorting_asc {
    background-image: url("https://cdn.datatables.net/r/dt/dt-1.10.9/DataTables-1.10.9/images/sort_asc.png")
}

table.dataTable thead .sorting_desc {
    background-image: url("https://cdn.datatables.net/r/dt/dt-1.10.9/DataTables-1.10.9/images/sort_desc.png")
}

table.dataTable thead .sorting_asc_disabled {
    background-image: url("https://cdn.datatables.net/r/dt/dt-1.10.9/DataTables-1.10.9/images/sort_asc_disabled.png")
}

table.dataTable thead .sorting_desc_disabled {
    background-image: url("https://cdn.datatables.net/r/dt/dt-1.10.9/DataTables-1.10.9/images/sort_desc_disabled.png")
*/

@media screen and (max-width: 767px) {
    .dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_paginate {
        float:none;
        text-align: center
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 0.5em
    }
}
</style>

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
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<!--<script src="assets/js/ValuationBands.js"></script>
<script src="assets/js/TA.js"></script>
<script src="assets/js/GaugeChart.js"></script>-->
<script src="assets/js/Utils.js"></script>

<?php $page = 'screener'; include "./header.php" ?>

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
    <main class="page">
        <section class="clean-block about-us" style="padding:0">
            <h1 class="sr-only" id="h1_text">Stock Screener – Filter by Technical & Fundamental Criteria</h1>
            <div class="container" style="padding:30px 0 0;">
    <!--div class="block-heading">
        <h2 class="text-info">About Us<a/h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc quam urna, dignissim nec auctor in, mattis vitae leo.</p>
        <a href="#"><img src = "assets/img/Ad_h.png" widht = "970" height = "90"/></a>
    </div-->
    <div class="row justify-content-center" style="margin:70px 0 0 0;padding-right:0;padding-left:0">

        <div class="col-md-12 table-condensed cf">
            
<!--<div id="wrapper">
<div id="gaugecontainer1" style="float:left; width: 50%; "></div>
<div id="gaugecontainer2" style="float:right; width: 50%; "></div>
<div style="clear:both"></div>
</div> -->
<!--<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">-->
<div id="wrapper">

    <!--<div id="stockname" style="padding:10px; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" data-toggle="tooltip" data-placement="top">
    </div> -->

<!--<script src="assets/js/LanguageTimezone.js"></script>
<script src="assets/js/Anystock.js"></script>
<script>
Number.prototype.padLeft = function(base,chr){
   var  len = (String(base || 10).length - String(this).length)+1;
   return len > 0? new Array(len).join(chr || '0')+this : this;
}
function numberformat(number)
{
    if (number >= 1000000000000)
        return rounding(number / 1000000000000, 10) + 'T';
    else if (number >= 1000000000 && number < 1000000000000)
        return rounding(number / 1000000000, 10) + 'B';
    else if (number >= 1000000 && number < 1000000000)
        return rounding(number / 1000000, 10) + 'M';
    else if (number >= 1000 && number < 1000000)
        return rounding(number / 1000, 10) + 'K';
    else
        return number;
}
function numberformatM(number)
{
    if (number >= 1000000)
        return rounding(number / 1000000, 10) + 'M';
    else if (number >= 1000 && number < 1000000)
        return rounding(number / 1000, 10) + 'K';
    else
        return number;
}
var url = window.location.search;
var stockcode = url.substr(url.indexOf("=") + 1).toUpperCase();
var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};



</script>
-->


</div>
        </div>

</div></div>
</section>
<style>
.dropdown-header { 
    color: #FFFFFF;
    background-color: #2636E3;
    font-weight: bold;
    text-align: center;
}

.show .dropdown-menu.screener {
    opacity: 1;
    visibility: visible;
}

.dropdown-menu.screener {
    -webkit-transition: opacity 0.3s;
    -moz-transition: opacity 0.3s;
    -ms-transition: opacity 0.3s;
    -o-transition: opacity 0.3s;
    transition: opacity 0.3s;
    display: block;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    position: fixed;
    z-index: 999999;
}

[data-theme="dark"] #screenerrule,
[data-theme="dark"] #Result,
[data-theme="dark"] #searchresult {
    color: #e8e8e8;
}

[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle {
    background-color: transparent !important;
    border-color: transparent !important;
    color: #d8d8e8 !important;
    box-shadow: none;
}

[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle:hover,
[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle:focus {
    color: #ffffff !important;
    background-color: #2a2a3e !important;
}

[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle:disabled {
    color: #8f8fa3 !important;
    background-color: #242438 !important;
    opacity: 1;
}

[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle u {
    color: #bfc6ff;
    text-decoration-color: #555a85;
}

[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle span[style*="background-color:yellow"],
[data-theme="dark"] #screenerrule .btn.btn-default.dropdown-toggle span[style*="background-color: yellow"] {
    background-color: #ffd84d !important;
    color: #111111 !important;
}

[data-theme="dark"] .dropdown-menu.screener {
    background-color: #242438;
    border-color: #4b5cff;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

[data-theme="dark"] .dropdown-menu.screener .dropdown-item {
    color: #e8e8e8;
}

[data-theme="dark"] .dropdown-menu.screener .dropdown-item:hover,
[data-theme="dark"] .dropdown-menu.screener .dropdown-item:focus {
    color: #ffffff;
    background-color: #33335a;
}

[data-theme="dark"] .dropdown-menu.screener .dropdown-divider {
    border-top-color: #3a3a4e;
}

[data-theme="dark"] #screener_grid,
[data-theme="dark"] #screener_grid_wrapper,
[data-theme="dark"] #screener_grid_wrapper .dataTables_info,
[data-theme="dark"] #screener_grid_wrapper .dataTables_length,
[data-theme="dark"] #screener_grid_wrapper .dataTables_filter {
    color: #e8e8e8;
}

[data-theme="dark"] #screener_grid_wrapper .dataTables_length select {
    background-color: #242438 !important;
    border-color: #6f74a8 !important;
    color: #e8e8e8 !important;
    box-shadow: none !important;
}

[data-theme="dark"] #screener_grid_wrapper .dataTables_length select:focus {
    border-color: #8ec8ff !important;
    box-shadow: 0 0 0 0.2rem rgba(142, 200, 255, 0.2) !important;
}

[data-theme="dark"] #screener_grid_wrapper .dataTables_length select option {
    background-color: #242438;
    color: #e8e8e8;
}

[data-theme="dark"] div.dt-button-collection {
    background-color: #242438 !important;
    border-color: #4a4a63 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45) !important;
}

[data-theme="dark"] div.dt-button-collection .dt-button:not(.active),
[data-theme="dark"] div.dt-button-collection .dropdown-item:not(.active) {
    background-color: #242438 !important;
    background-image: none !important;
    color: #e8e8e8 !important;
}

[data-theme="dark"] div.dt-button-collection .dt-button.active,
[data-theme="dark"] div.dt-button-collection .dropdown-item.active {
    background-color: #008cff !important;
    background-image: none !important;
    color: #ffffff !important;
}

[data-theme="dark"] div.dt-button-collection .dt-button:hover,
[data-theme="dark"] div.dt-button-collection .dt-button:focus,
[data-theme="dark"] div.dt-button-collection .dropdown-item:hover,
[data-theme="dark"] div.dt-button-collection .dropdown-item:focus {
    background-color: #147dce !important;
    background-image: none !important;
    color: #ffffff !important;
}

[data-theme="dark"] #screener_grid_processing {
    background-color: #242438;
    color: #e8e8e8;
    border: 1px solid #3a3a4e;
}
</style>
<div id="screenerrule" class="card clean-card text-center container" style="padding: 0;">
            <!--<div class="container" style="padding: 0px 15px 0px;margin: 10px 0 0 0;">
                <div>
                    <div class="col-sm-6 col-lg-4 TAgauge" style="float:left; ">
                        <div class="not-selectable" id="col1" style="min-height: 250px; "></div>
                        <div style="font-size:10px; text-align:left; padding:0px 0px 5px 10px">Trend Gauge shows daily trend with weekly factors considered</div>
                    </div>
                    <div class="col-sm-6 col-lg-4 TAcomment" id="TAcomment" style="float:right; min-height: 250px; text-align:left; "></div>
                    <div style="clear:both"></div>
                </div>
            </div>-->
    <div class="container not-selectable">
        <p></p>
        <div class="row">
        <div class="col-md-6 col-lg-4 btn-group" id="Market">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Market: <u><span style="background-color:yellow;">NYSE + Nasdaq</span></u>&nbsp;<span class="caret"></span></button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#" selected>NYSE + Nasdaq</a>
                <a class="dropdown-item" href="#">NYSE</a>
                <a class="dropdown-item" href="#">Nasdaq</a>
                <a class="dropdown-item" href="#">NYSE Arca</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">London</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Toronto TSX</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Australia</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">India NSE</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Tokyo</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Hong Kong</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Shanghai</a>
                <a class="dropdown-item" href="#">Shenzhen</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>

        <div class="col-md-6 col-lg-4 btn-group" id="Sector">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Sector: <u>All</u>&nbsp;<span class="caret"></span></button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Basic Materials</a>
                <a class="dropdown-item" href="#">Communication</a>
                <!--<a class="dropdown-item" href="#">Conglomerates</a>-->
                <a class="dropdown-item" href="#">Consumer Cyclical</a>
                <a class="dropdown-item" href="#">Consumer Defensive</a>
                <!--<a class="dropdown-item" href="#">Consumer Goods</a>-->
                <a class="dropdown-item" href="#">Energy</a>
                <!--<a class="dropdown-item" href="#">Financial</a>-->
                <a class="dropdown-item" href="#">Financial</a>
                <a class="dropdown-item" href="#">Healthcare</a>
                <!--<a class="dropdown-item" href="#">Industrial Goods</a>-->
                <a class="dropdown-item" href="#">Industrials</a>
                <a class="dropdown-item" href="#">Other</a>
                <a class="dropdown-item" href="#">Real Estate</a>
                <!--<a class="dropdown-item" href="#">Services</a>-->
                <a class="dropdown-item" href="#">Technology</a>
                <a class="dropdown-item" href="#">Utilities</a>
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="Industry">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Industry: <u>All</u>&nbsp;<span class="caret"></span></button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <h6 class="dropdown-header">Basic Materials</h6>
                <a class="dropdown-item" href="#">Agricultural Inputs</a>
                <a class="dropdown-item" href="#">Aluminum</a>
                <a class="dropdown-item" href="#">Building Materials</a>
                <a class="dropdown-item" href="#">Chemicals</a>
                <a class="dropdown-item" href="#">Coking Coal</a>
                <a class="dropdown-item" href="#">Copper</a>
                <a class="dropdown-item" href="#">Gold</a>
                
                <a class="dropdown-item" href="#">Industrial Metals & Minerals</a>
                <a class="dropdown-item" href="#">Lumber & Wood Production</a>
                <!--<a class="dropdown-item" href="#">Major Integrated Oil & Gas</a>
                <a class="dropdown-item" href="#">Oil & Gas Drilling & Exploration</a>
                <a class="dropdown-item" href="#">Oil & Gas Equipment & Services</a>
                <a class="dropdown-item" href="#">Oil & Gas Pipelines</a>
                <a class="dropdown-item" href="#">Oil & Gas Refining & Marketing</a>-->
                <a class="dropdown-item" href="#">Other Industrial Metals & Mining</a>
                <a class="dropdown-item" href="#">Other Precious Metals & Mining</a>
                <a class="dropdown-item" href="#">Paper & Paper Products</a>
                <a class="dropdown-item" href="#">Silver</a>
                <a class="dropdown-item" href="#">Specialty Chemicals</a>
                <a class="dropdown-item" href="#">Steel</a>
                
                <h6 class="dropdown-header">Communication</h6>
                <a class="dropdown-item" href="#">Advertising Agencies</a>
                <a class="dropdown-item" href="#">Broadcasting</a>
                <a class="dropdown-item" href="#">Electronic Gaming & Multimedia</a>
                <a class="dropdown-item" href="#">Entertainment</a>
                <a class="dropdown-item" href="#">Internet Content & Info</a>
                <a class="dropdown-item" href="#">Publishing</a>
                <a class="dropdown-item" href="#">Telecom Services</a>
                
                <h6 class="dropdown-header">Consumer Cyclical</h6>
                <a class="dropdown-item" href="#">Apparel Manufacturing</a>
                <a class="dropdown-item" href="#">Apparel Retail</a>
                <a class="dropdown-item" href="#">Auto & Truck Dealerships</a>
                <a class="dropdown-item" href="#">Auto Manufacturers</a>
                <a class="dropdown-item" href="#">Auto Parts</a>
                <a class="dropdown-item" href="#">Department Stores</a>
                <a class="dropdown-item" href="#">Footwear & Accessories</a>
                <a class="dropdown-item" href="#">Furnishings, Fixtures & Appliances</a>
                <a class="dropdown-item" href="#">Gambling</a>
                <a class="dropdown-item" href="#">Home Improvement Retail</a>
                <a class="dropdown-item" href="#">Internet Retail</a>
                <a class="dropdown-item" href="#">Leisure</a>
                <a class="dropdown-item" href="#">Lodging</a>
                <a class="dropdown-item" href="#">Luxury Goods</a>
                <a class="dropdown-item" href="#">Packaging & Containers</a>
                <a class="dropdown-item" href="#">Personal Services</a>
                <a class="dropdown-item" href="#">Recreational Vehicles</a>
                <a class="dropdown-item" href="#">Residential Construction</a>
                <a class="dropdown-item" href="#">Resorts & Casinos</a>
                <a class="dropdown-item" href="#">Restaurants</a>
                <a class="dropdown-item" href="#">Specialty Retail</a>
                <a class="dropdown-item" href="#">Textile Manufacturing</a>
                <a class="dropdown-item" href="#">Travel Services</a>
                
                <h6 class="dropdown-header">Consumer Defensive</h6>
                <a class="dropdown-item" href="#">Beverages-Brewers</a>
                <a class="dropdown-item" href="#">Beverages-Non-Alcoholic</a>
                <a class="dropdown-item" href="#">Beverages-Wineries & Distilleries</a>
                <a class="dropdown-item" href="#">Confectioners</a>
                <a class="dropdown-item" href="#">Discount Stores</a>
                <a class="dropdown-item" href="#">Education & Training Services</a>
                <a class="dropdown-item" href="#">Farm Products</a>
                <a class="dropdown-item" href="#">Food Distribution</a>
                <a class="dropdown-item" href="#">Grocery Stores</a>
                <a class="dropdown-item" href="#">Household & Personal Products</a>
                <a class="dropdown-item" href="#">Packaged Foods</a>
                <a class="dropdown-item" href="#">Tobacco</a>
                
                <!--<h6 class="dropdown-header">Consumer Goods</h6>-->
                <!--<a class="dropdown-item" href="#">Beverages - Soft Drinks</a>-->
                <!--<a class="dropdown-item" href="#">Electronic Equipment</a>-->
                
                <h6 class="dropdown-header">Energy</h6>
                <!--<a class="dropdown-item" href="#">Oil & Gas Drilling</a>-->
                <a class="dropdown-item" href="#">Oil & Gas E&P</a>
                <a class="dropdown-item" href="#">Oil & Gas Equipment & Services</a>
                <a class="dropdown-item" href="#">Oil & Gas Independent</a>
                <a class="dropdown-item" href="#">Oil & Gas Integrated</a>
                <a class="dropdown-item" href="#">Oil & Gas Midstream</a>
                <a class="dropdown-item" href="#">Oil & Gas Refining & Marketing</a>
                <a class="dropdown-item" href="#">Thermal Coal</a>
                <a class="dropdown-item" href="#">Uranium</a>
                
                <!--<h6 class="dropdown-header">Financial</h6>-->
                <!--<a class="dropdown-item" href="#">Asset Management</a>-->
                <!--<a class="dropdown-item" href="#">Closed-End Fund - Debt</a>-->
                <!--<a class="dropdown-item" href="#">Life Insurance</a>-->
                <!--<a class="dropdown-item" href="#">Money Center Banks</a>-->
                <!--<a class="dropdown-item" href="#">Property Management</a>-->
                <!--<a class="dropdown-item" href="#">Real Estate Development</a>-->
                <!--<a class="dropdown-item" href="#">Savings & Loans</a>-->
                
                <h6 class="dropdown-header">Financial</h6>
                <a class="dropdown-item" href="#">Asset Management</a>
                <a class="dropdown-item" href="#">Banks-Diversified</a>
                <a class="dropdown-item" href="#">Banks-Regional</a>
                <a class="dropdown-item" href="#">Capital Markets</a>
                <a class="dropdown-item" href="#">Credit Services</a>
                <a class="dropdown-item" href="#">Financial Conglomerates</a>
                <a class="dropdown-item" href="#">Financial Data & Stock Exchanges</a>
                <a class="dropdown-item" href="#">Insurance Brokers</a>
                <a class="dropdown-item" href="#">Insurance-Diversified</a>
                <a class="dropdown-item" href="#">Insurance-Life</a>
                <a class="dropdown-item" href="#">Insurance-Property & Casualty</a>
                <a class="dropdown-item" href="#">Insurance-Reinsurance</a>
                <a class="dropdown-item" href="#">Insurance-Specialty</a>
                <a class="dropdown-item" href="#">Mortgage Finance</a>
                <a class="dropdown-item" href="#">Shell Companies</a>
                
                <h6 class="dropdown-header">Healthcare</h6>
                <a class="dropdown-item" href="#">Biotechnology</a>
                <a class="dropdown-item" href="#">Diagnostics & Research</a>
                <a class="dropdown-item" href="#">Drug Delivery</a>
                <a class="dropdown-item" href="#">Drug Manufacturers-General</a>
                <a class="dropdown-item" href="#">Drug Manufacturers-Specialty & Generic</a>
                <!--<a class="dropdown-item" href="#">Health Care Plans</a>-->
                <a class="dropdown-item" href="#">Health Info Services</a>
                <a class="dropdown-item" href="#">Healthcare Plans</a>
                <a class="dropdown-item" href="#">Medical Care Facilities</a>
                <a class="dropdown-item" href="#">Medical Devices</a>
                <a class="dropdown-item" href="#">Medical Distribution</a>
                <a class="dropdown-item" href="#">Medical Instruments & Supplies</a>
                <a class="dropdown-item" href="#">Pharmaceutical Retailers</a>
                
                <!--<h6 class="dropdown-header">Industrial Goods</h6>-->
                <!--<a class="dropdown-item" href="#">Aerospace/Defense Products & Services</a>-->
                <!--<a class="dropdown-item" href="#">Diversified Machinery</a>-->
                <!--<a class="dropdown-item" href="#">Waste Management</a>-->
                
                <h6 class="dropdown-header">Industrials</h6>
                <a class="dropdown-item" href="#">Aerospace & Defense</a>
                <a class="dropdown-item" href="#">Airlines</a>
                <a class="dropdown-item" href="#">Airports & Air Services</a>
                <a class="dropdown-item" href="#">Building Products & Equipment</a>
                <a class="dropdown-item" href="#">Business Equipment & Supplies</a>
                <a class="dropdown-item" href="#">Conglomerates</a>
                <a class="dropdown-item" href="#">Consulting Services</a>
                <a class="dropdown-item" href="#">Electrical Equipment & Parts</a>
                <a class="dropdown-item" href="#">Engineering & Construction</a>
                <a class="dropdown-item" href="#">Farm & Heavy Construction Machinery</a>
                <a class="dropdown-item" href="#">Industrial Distribution</a>
                <a class="dropdown-item" href="#">Infrastructure Operations</a>
                <a class="dropdown-item" href="#">Integrated Freight & Logistics</a>
                <a class="dropdown-item" href="#">Marine Shipping</a>
                <a class="dropdown-item" href="#">Metal Fabrication</a>
                <a class="dropdown-item" href="#">Pollution & Treatment Controls</a>
                <a class="dropdown-item" href="#">Railroads</a>
                <a class="dropdown-item" href="#">Rental & Leasing Services</a>
                <a class="dropdown-item" href="#">Security & Protection Services</a>
                <a class="dropdown-item" href="#">Specialty Business Services</a>
                <a class="dropdown-item" href="#">Specialty Industrial Machinery</a>
                <a class="dropdown-item" href="#">Staffing & Employment Services</a>
                <a class="dropdown-item" href="#">Tools & Accessories</a>
                <a class="dropdown-item" href="#">Trucking</a>
                <a class="dropdown-item" href="#">Waste Management</a>
                
                <h6 class="dropdown-header">Other</h6>
                <a class="dropdown-item" href="#">Other</a>
                
                <h6 class="dropdown-header">Real Estate</h6>
                <a class="dropdown-item" href="#">Real Estate Services</a>
                <a class="dropdown-item" href="#">Real Estate-Development</a>
                <a class="dropdown-item" href="#">Real Estate-Diversified</a>
                <a class="dropdown-item" href="#">REIT-Diversified</a>
                <a class="dropdown-item" href="#">REIT-Healthcare Facilities</a>
                <a class="dropdown-item" href="#">REIT-Hotel & Motel</a>
                <a class="dropdown-item" href="#">REIT-Industrial</a>
                <a class="dropdown-item" href="#">REIT-Mortgage</a>
                <a class="dropdown-item" href="#">REIT-Office</a>
                <a class="dropdown-item" href="#">REIT-Residential</a>
                <a class="dropdown-item" href="#">REIT-Retail</a>
                <a class="dropdown-item" href="#">REIT-Specialty</a>
                
                <!--<h6 class="dropdown-header">Services</h6>
                <a class="dropdown-item" href="#">Air Delivery & Freight Services</a>
                <a class="dropdown-item" href="#">Business Services</a>
                <a class="dropdown-item" href="#">Entertainment - Diversified</a>
                <a class="dropdown-item" href="#">Publishing - Books</a>
                <a class="dropdown-item" href="#">Specialty Retail, Other</a>
                <a class="dropdown-item" href="#">Sporting Activities</a>
                <a class="dropdown-item" href="#">Staffing & Outsourcing Services</a>
                <a class="dropdown-item" href="#">Trucking</a>-->
                
                <h6 class="dropdown-header">Technology</h6>
                <a class="dropdown-item" href="#">Communication Equipment</a>
                <a class="dropdown-item" href="#">Computer Hardware</a>
                <a class="dropdown-item" href="#">Consumer Electronics</a>
                <a class="dropdown-item" href="#">Diversified Electronics</a>
                <a class="dropdown-item" href="#">Electronic Components</a>
                <a class="dropdown-item" href="#">Electronics & Computer Distribution</a>
                <a class="dropdown-item" href="#">Info Technology Services</a>
                <a class="dropdown-item" href="#">Scientific & Technical Instruments</a>
                <a class="dropdown-item" href="#">Semiconductor Equipment & Materials</a>
                <a class="dropdown-item" href="#">Semiconductors</a>
                <a class="dropdown-item" href="#">Software-Application</a>
                <a class="dropdown-item" href="#">Software-Infrastructure</a>
                <a class="dropdown-item" href="#">Solar</a>
                <a class="dropdown-item" href="#">Wireless Communications</a>
                
                <h6 class="dropdown-header">Utilities</h6>
                <!--<a class="dropdown-item" href="#">Diversified Utilities</a>-->
                <!--<a class="dropdown-item" href="#">Electric Utilities</a>-->
                <a class="dropdown-item" href="#">Utilities-Diversified</a>
                <a class="dropdown-item" href="#">Utilities-Independent Power Producers</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Electric</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Gas</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Water</a>
                <a class="dropdown-item" href="#">Utilities-Renewable</a>

                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Advertising Agencies</a>
                <a class="dropdown-item" href="#">Aerospace & Defense</a>
                <a class="dropdown-item" href="#">Agricultural Inputs</a>
                <a class="dropdown-item" href="#">Airlines</a>
                <a class="dropdown-item" href="#">Airports & Air Services</a>
                <a class="dropdown-item" href="#">Aluminum</a>
                <a class="dropdown-item" href="#">Apparel Manufacturing</a>
                <a class="dropdown-item" href="#">Apparel Retail</a>
                <a class="dropdown-item" href="#">Asset Management</a>
                <a class="dropdown-item" href="#">Auto & Truck Dealerships</a>
                <a class="dropdown-item" href="#">Auto Manufacturers</a>
                <a class="dropdown-item" href="#">Auto Parts</a>
                <h6 class="dropdown-header">Bank</h6>
                <a class="dropdown-item" href="#">Banks-Diversified</a>
                <a class="dropdown-item" href="#">Banks-Regional</a>
                <a class="dropdown-item" href="#">Beverages - Soft Drinks</a>
                <a class="dropdown-item" href="#">Beverages-Brewers</a>
                <a class="dropdown-item" href="#">Beverages-Non-Alcoholic</a>
                <a class="dropdown-item" href="#">Beverages-Wineries & Distilleries</a>
                <a class="dropdown-item" href="#">Biotechnology</a>
                <a class="dropdown-item" href="#">Broadcasting</a>
                <a class="dropdown-item" href="#">Building Materials</a>
                <a class="dropdown-item" href="#">Building Products & Equipment</a>
                <a class="dropdown-item" href="#">Business Equipment & Supplies</a>
                <a class="dropdown-item" href="#">Business Services</a>
                <a class="dropdown-item" href="#">Capital Markets</a>
                <a class="dropdown-item" href="#">Chemicals</a>
                <a class="dropdown-item" href="#">Closed-End Fund - Debt</a>
                <a class="dropdown-item" href="#">Coking Coal</a>
                <a class="dropdown-item" href="#">Communication Equipment</a>
                <a class="dropdown-item" href="#">Computer Hardware</a>
                <a class="dropdown-item" href="#">Confectioners</a>
                <a class="dropdown-item" href="#">Conglomerates</a>
                <a class="dropdown-item" href="#">Consulting Services</a>
                <a class="dropdown-item" href="#">Consumer Electronics</a>
                <a class="dropdown-item" href="#">Copper</a>
                <a class="dropdown-item" href="#">Credit Services</a>
                <a class="dropdown-item" href="#">Department Stores</a>
                <a class="dropdown-item" href="#">Diagnostics & Research</a>
                <a class="dropdown-item" href="#">Discount Stores</a>
                <a class="dropdown-item" href="#">Diversified Machinery</a>
                <a class="dropdown-item" href="#">Drug Manufacturers-General</a>
                <a class="dropdown-item" href="#">Drug Manufacturers-Specialty & Generic</a>
                <a class="dropdown-item" href="#">Education & Training Services</a>
                <a class="dropdown-item" href="#">Electric Utilities</a>
                <a class="dropdown-item" href="#">Electrical Equipment & Parts</a>
                <a class="dropdown-item" href="#">Electronic Components</a>
                <a class="dropdown-item" href="#">Electronic Equipment</a>
                <a class="dropdown-item" href="#">Electronic Gaming & Multimedia</a>
                <a class="dropdown-item" href="#">Electronics & Computer Distribution</a>
                <a class="dropdown-item" href="#">Engineering & Construction</a>
                <a class="dropdown-item" href="#">Entertainment</a>
                <a class="dropdown-item" href="#">Entertainment - Diversified</a>
                <a class="dropdown-item" href="#">Farm & Heavy Construction Machinery</a>
                <a class="dropdown-item" href="#">Farm Products</a>
                <a class="dropdown-item" href="#">Financial Conglomerates</a>
                <a class="dropdown-item" href="#">Financial Data & Stock Exchanges</a>
                <a class="dropdown-item" href="#">Food Distribution</a>
                <a class="dropdown-item" href="#">Footwear & Accessories</a>
                <a class="dropdown-item" href="#">Furnishings, Fixtures & Appliances</a>
                <a class="dropdown-item" href="#">Gambling</a>
                <a class="dropdown-item" href="#">Gold</a>
                <a class="dropdown-item" href="#">Grocery Stores</a>
                <a class="dropdown-item" href="#">Health Info Services</a>
                <a class="dropdown-item" href="#">Healthcare Plans</a>
                <a class="dropdown-item" href="#">Home Improvement Retail</a>
                <a class="dropdown-item" href="#">Home Improvement Stores</a>
                <a class="dropdown-item" href="#">Household & Personal Products</a>
                <a class="dropdown-item" href="#">Independent Oil & Gas</a>
                <a class="dropdown-item" href="#">Industrial Distribution</a>
                <a class="dropdown-item" href="#">Industrial Metals & Minerals</a>
                <a class="dropdown-item" href="#">Info Technology Services</a>
                <a class="dropdown-item" href="#">Infrastructure Operations</a>
                <a class="dropdown-item" href="#">Insurance Brokers</a>
                <a class="dropdown-item" href="#">Insurance-Diversified</a>
                <a class="dropdown-item" href="#">Insurance-Life</a>
                <a class="dropdown-item" href="#">Insurance-Property & Casualty</a>
                <a class="dropdown-item" href="#">Insurance-Reinsurance</a>
                <a class="dropdown-item" href="#">Insurance-Specialty</a>
                <a class="dropdown-item" href="#">Integrated Freight & Logistics</a>
                <a class="dropdown-item" href="#">Internet Content & Info</a>
                <a class="dropdown-item" href="#">Internet Retail</a>
                <a class="dropdown-item" href="#">Leisure</a>
                <a class="dropdown-item" href="#">Lodging</a>
                <a class="dropdown-item" href="#">Lumber & Wood Production</a>
                <a class="dropdown-item" href="#">Luxury Goods</a>
                <a class="dropdown-item" href="#">Major Integrated Oil & Gas</a>
                <a class="dropdown-item" href="#">Marine Shipping</a>
                <a class="dropdown-item" href="#">Medical Care Facilities</a>
                <a class="dropdown-item" href="#">Medical Devices</a>
                <a class="dropdown-item" href="#">Medical Distribution</a>
                <a class="dropdown-item" href="#">Medical Instruments & Supplies</a>
                <a class="dropdown-item" href="#">Metal Fabrication</a>
                <a class="dropdown-item" href="#">Money Center Banks</a>
                <a class="dropdown-item" href="#">Mortgage Finance</a>
                <a class="dropdown-item" href="#">Oil & Gas Drilling</a>
                <a class="dropdown-item" href="#">Oil & Gas E&P</a>
                <a class="dropdown-item" href="#">Oil & Gas Equipment & Services</a>
                <a class="dropdown-item" href="#">Oil & Gas Integrated</a>
                <a class="dropdown-item" href="#">Oil & Gas Midstream</a>
                <a class="dropdown-item" href="#">Oil & Gas Pipelines</a>
                <a class="dropdown-item" href="#">Oil & Gas Refining & Marketing</a>
                <a class="dropdown-item" href="#">Other</a>
                <a class="dropdown-item" href="#">Other Industrial Metals & Mining</a>
                <a class="dropdown-item" href="#">Other Precious Metals & Mining</a>
                <a class="dropdown-item" href="#">Packaged Foods</a>
                <a class="dropdown-item" href="#">Packaging & Containers</a>
                <a class="dropdown-item" href="#">Paper & Paper Products</a>
                <a class="dropdown-item" href="#">Personal Services</a>
                <a class="dropdown-item" href="#">Pharmaceutical Retailers</a>
                <a class="dropdown-item" href="#">Pollution & Treatment Controls</a>
                <a class="dropdown-item" href="#">Property Management</a>
                <a class="dropdown-item" href="#">Publishing</a>
                <a class="dropdown-item" href="#">Railroads</a>
                <a class="dropdown-item" href="#">Real Estate Development</a>
                <a class="dropdown-item" href="#">Real Estate Services</a>
                <a class="dropdown-item" href="#">Real Estate-Development</a>
                <a class="dropdown-item" href="#">Real Estate-Diversified</a>
                <a class="dropdown-item" href="#">Recreational Vehicles</a>
                <a class="dropdown-item" href="#">REIT-Diversified</a>
                <a class="dropdown-item" href="#">REIT-Healthcare Facilities</a>
                <a class="dropdown-item" href="#">REIT-Hotel & Motel</a>
                <a class="dropdown-item" href="#">REIT-Industrial</a>
                <a class="dropdown-item" href="#">REIT-Mortgage</a>
                <a class="dropdown-item" href="#">REIT-Office</a>
                <a class="dropdown-item" href="#">REIT-Residential</a>
                <a class="dropdown-item" href="#">REIT-Retail</a>
                <a class="dropdown-item" href="#">REIT-Specialty</a>
                <a class="dropdown-item" href="#">Rental & Leasing Services</a>
                <a class="dropdown-item" href="#">Residential Construction</a>
                <a class="dropdown-item" href="#">Resorts & Casinos</a>
                <a class="dropdown-item" href="#">Restaurants</a>
                <a class="dropdown-item" href="#">Scientific & Technical Instruments</a>
                <a class="dropdown-item" href="#">Security & Protection Services</a>
                <a class="dropdown-item" href="#">Semiconductor Equipment & Materials</a>
                <a class="dropdown-item" href="#">Semiconductors</a>
                <a class="dropdown-item" href="#">Shell Companies</a>
                <a class="dropdown-item" href="#">Silver</a>
                <a class="dropdown-item" href="#">Software-Application</a>
                <a class="dropdown-item" href="#">Software-Infrastructure</a>
                <a class="dropdown-item" href="#">Solar</a>
                <a class="dropdown-item" href="#">Specialty Business Services</a>
                <a class="dropdown-item" href="#">Specialty Chemicals</a>
                <a class="dropdown-item" href="#">Specialty Industrial Machinery</a>
                <a class="dropdown-item" href="#">Specialty Retail</a>
                <a class="dropdown-item" href="#">Specialty Retail, Other</a>
                <a class="dropdown-item" href="#">Staffing & Employment Services</a>
                <a class="dropdown-item" href="#">Steel</a>
                <a class="dropdown-item" href="#">Telecom Services</a>
                <a class="dropdown-item" href="#">Textile Manufacturing</a>
                <a class="dropdown-item" href="#">Thermal Coal</a>
                <a class="dropdown-item" href="#">Tobacco</a>
                <a class="dropdown-item" href="#">Tools & Accessories</a>
                <a class="dropdown-item" href="#">Travel Services</a>
                <a class="dropdown-item" href="#">Trucking</a>
                <a class="dropdown-item" href="#">Uranium</a>
                <a class="dropdown-item" href="#">Utilities-Diversified</a>
                <a class="dropdown-item" href="#">Utilities-Independent Power Producers</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Electric</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Gas</a>
                <a class="dropdown-item" href="#">Utilities-Regulated Water</a>
                <a class="dropdown-item" href="#">Utilities-Renewable</a>
                <a class="dropdown-item" href="#">Waste Management</a>
                <a class="dropdown-item" href="#">Wireless Communications</a>-->
              </div>
        </div>

        <div class="col-md-6 col-lg-4 btn-group" id="Market_Cap">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Market Cap: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Mega (&gt; $300 Bil)</a>
                <a class="dropdown-item" href="#">Large ($10 to $300 Bil)</a>
                <a class="dropdown-item" href="#">Mid ($2 to $10 Bil)</a>
                <a class="dropdown-item" href="#">Small ($0.3 to $2 Bil)</a>
                <a class="dropdown-item" href="#">Micro ($50 to $300 Mil)</a>
                <a class="dropdown-item" href="#">Nano (&lt; $50 Mil)</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&ge; Large</a>
                <a class="dropdown-item" href="#">&ge; Mid</a>
                <a class="dropdown-item" href="#">&ge; Small</a>
                <a class="dropdown-item" href="#">&ge; Micro</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&le; Large</a>
                <a class="dropdown-item" href="#">&le; Mid</a>
                <a class="dropdown-item" href="#">&le; Small</a>
                <a class="dropdown-item" href="#">&le; Micro</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Top 500</a>
                <a class="dropdown-item" href="#">Top 200</a>
                <a class="dropdown-item" href="#">Top 100</a>
                <a class="dropdown-item" href="#">Top 50</a>
                <a class="dropdown-item" href="#">Top 20</a>
                <a class="dropdown-item" href="#">Top 10</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Bottom 500</a>
                <a class="dropdown-item" href="#">Bottom 200</a>
                <a class="dropdown-item" href="#">Bottom 100</a>
                <a class="dropdown-item" href="#">Bottom 50</a>
                <a class="dropdown-item" href="#">Bottom 20</a>
                <a class="dropdown-item" href="#">Bottom 10</a>
              </div>
        </div>
    </div>
    
    <hr>

    <div class="row">
        <div class="col-md-6 col-lg-4 btn-group" id="Technical_Polar">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu5" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><img src="assets/img/TApolar.png" width="24" height="26">
            Technical Polar: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&ge; 4</a>
                <a class="dropdown-item" href="#">&ge; 3</a>
                <a class="dropdown-item" href="#">&ge; 2</a>
                <a class="dropdown-item" href="#">&ge; 1</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&le; 4</a>
                <a class="dropdown-item" href="#">&le; 3</a>
                <a class="dropdown-item" href="#">&le; 2</a>
                <a class="dropdown-item" href="#">&le; 1</a>
                <div class="dropdown-divider"></div>
                
                <a class="dropdown-item" href="#">5</a>
                <a class="dropdown-item" href="#">4</a>
                <a class="dropdown-item" href="#">3</a>
                <a class="dropdown-item" href="#">2</a>
                <a class="dropdown-item" href="#">1</a>
                <a class="dropdown-item" href="#">0</a>
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Outperforms Industry</a>
                <a class="dropdown-item" href="#">Underperforms Industry</a>
                <div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="Valuation_Polar">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu6" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><img src="assets/img/VApolar.png" width="24" height="24">
            Valuation Polar: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&ge; 4</a>
                <a class="dropdown-item" href="#">&ge; 3</a>
                <a class="dropdown-item" href="#">&ge; 2</a>
                <a class="dropdown-item" href="#">&ge; 1</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&le; 4</a>
                <a class="dropdown-item" href="#">&le; 3</a>
                <a class="dropdown-item" href="#">&le; 2</a>
                <a class="dropdown-item" href="#">&le; 1</a>
                <div class="dropdown-divider"></div>
                
                <a class="dropdown-item" href="#">5</a>
                <a class="dropdown-item" href="#">4</a>
                <a class="dropdown-item" href="#">3</a>
                <a class="dropdown-item" href="#">2</a>
                <a class="dropdown-item" href="#">1</a>
                <a class="dropdown-item" href="#">0</a>
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Outperforms Industry</a>
                <a class="dropdown-item" href="#">Underperforms Industry</a>
                <div class="dropdown-divider"></div>-->
              </div>
        </div>

        <div class="col-md-6 col-lg-4 btn-group" id="Fundamental_Polar">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><img src="assets/img/FApolar.png" width="24" height="24">
            Fundamental Polar: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&ge; 4</a>
                <a class="dropdown-item" href="#">&ge; 3</a>
                <a class="dropdown-item" href="#">&ge; 2</a>
                <a class="dropdown-item" href="#">&ge; 1</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&le; 4</a>
                <a class="dropdown-item" href="#">&le; 3</a>
                <a class="dropdown-item" href="#">&le; 2</a>
                <a class="dropdown-item" href="#">&le; 1</a>
                <div class="dropdown-divider"></div>
                
                <a class="dropdown-item" href="#">5</a>
                <a class="dropdown-item" href="#">4</a>
                <a class="dropdown-item" href="#">3</a>
                <a class="dropdown-item" href="#">2</a>
                <a class="dropdown-item" href="#">1</a>
                <a class="dropdown-item" href="#">0</a>
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Outperforms Industry</a>
                <a class="dropdown-item" href="#">Underperforms Industry</a>
                <div class="dropdown-divider"></div>-->
              </div>
        </div>
    </div>
    
    <hr>

    <div class="row">        
        <div class="col-md-6 col-lg-4 btn-group" id="Trend_Gauge">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu8" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span style="font-size: 21px; color:#b8b8b8;"><i class="fas fa-tachometer-alt"></i></span>
            Trend Gauge: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Oversold - Rebound</a>
                <a class="dropdown-item" href="#">Rebound - Up</a>
                <a class="dropdown-item" href="#">Up - Strong Up</a>
                <a class="dropdown-item" href="#">Strong Up - Overbought</a>
                <a class="dropdown-item" href="#">Overbought - Correction</a>
                <a class="dropdown-item" href="#">Correction - Down</a>
                <a class="dropdown-item" href="#">Down - Strong Down</a>
                <a class="dropdown-item" href="#">Strong Down - Oversold</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Oversold - Up</a>
                <a class="dropdown-item" href="#">Up - Overbought</a>
                <a class="dropdown-item" href="#">Overbought - Down</a>
                <a class="dropdown-item" href="#">Down - Oversold</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>

        <div class="col-md-6 col-lg-4 btn-group" id="Price_Channel_Pos">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu8a" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Price Channel Pos: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">break-out Top</a>
                <a class="dropdown-item" href="#">break-down Bottom</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">cross-under Top</a>
                <a class="dropdown-item" href="#">cross-over Bottom</a>
                <a class="dropdown-item" href="#">cross-over Middle</a>
                <a class="dropdown-item" href="#">cross-under Middle</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">near Top</a>
                <a class="dropdown-item" href="#">near Middle</a>
                <a class="dropdown-item" href="#">near Bottom</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">above Top</a>
                <a class="dropdown-item" href="#">below Bottom</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Upper Half</a>
                <a class="dropdown-item" href="#">Lower Half</a>
              </div>
        </div>
     
        <div class="col-md-6 col-lg-4 btn-group" id="Price_Channel_Trend">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu8b" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Price Channel Trend: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Up</a>
                <a class="dropdown-item" href="#">Down</a>
                <a class="dropdown-item" href="#">Sideways</a>
              </div>
        </div>
    </div>
    
    <hr>

    <div class="row">        
        <div class="col-md-6 col-lg-4 btn-group" id="PE_Band_STDEV_σ">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu9" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            PE Band STDEV σ: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Median to -1</a>
                <a class="dropdown-item" href="#">-1 to -2</a>
                <a class="dropdown-item" href="#">-2 to -3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Median to 1</a>
                <a class="dropdown-item" href="#">1 to 2</a>
                <a class="dropdown-item" href="#">2 to 3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&lt; 3</a>
                <a class="dropdown-item" href="#">&lt; 2</a>
                <a class="dropdown-item" href="#">&lt; 1</a>
                <a class="dropdown-item" href="#">&lt; Median</a>
                <a class="dropdown-item" href="#">&lt; -1</a>
                <a class="dropdown-item" href="#">&lt; -2</a>
                <a class="dropdown-item" href="#">&lt; -3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 3</a>
                <a class="dropdown-item" href="#">&gt; 2</a>
                <a class="dropdown-item" href="#">&gt; 1</a>
                <a class="dropdown-item" href="#">&gt; Median</a>
                <a class="dropdown-item" href="#">&gt; -1</a>
                <a class="dropdown-item" href="#">&gt; -2</a>
                <a class="dropdown-item" href="#">&gt; -3</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="PE_Band_Trend">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu10" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            PE Band Trend <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Strong Up + Mild Up</a>
                <a class="dropdown-item" href="#">Strong Up</a>
                <a class="dropdown-item" href="#">Mild Up</a>
                <a class="dropdown-item" href="#">Sideways</a>
                <a class="dropdown-item" href="#">Mild Down</a>
                <a class="dropdown-item" href="#">Strong Down</a>
                <a class="dropdown-item" href="#">Strong Down + Mild Down</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="PB_Band_STDEV_σ">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu11" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            PB Band STDEV σ: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Median to -1</a>
                <a class="dropdown-item" href="#">-1 to -2</a>
                <a class="dropdown-item" href="#">-2 to -3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Median to 1</a>
                <a class="dropdown-item" href="#">1 to 2</a>
                <a class="dropdown-item" href="#">2 to 3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&lt; 3</a>
                <a class="dropdown-item" href="#">&lt; 2</a>
                <a class="dropdown-item" href="#">&lt; 1</a>
                <a class="dropdown-item" href="#">&lt; Median</a>
                <a class="dropdown-item" href="#">&lt; -1</a>
                <a class="dropdown-item" href="#">&lt; -2</a>
                <a class="dropdown-item" href="#">&lt; -3</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 3</a>
                <a class="dropdown-item" href="#">&gt; 2</a>
                <a class="dropdown-item" href="#">&gt; 1</a>
                <a class="dropdown-item" href="#">&gt; Median</a>
                <a class="dropdown-item" href="#">&gt; -1</a>
                <a class="dropdown-item" href="#">&gt; -2</a>
                <a class="dropdown-item" href="#">&gt; -3</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="PB_Band_Trend">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            PB Band Trend <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Strong Up + Mild Up</a>
                <a class="dropdown-item" href="#">Strong Up</a>
                <a class="dropdown-item" href="#">Mild Up</a>
                <a class="dropdown-item" href="#">Sideways</a>
                <a class="dropdown-item" href="#">Mild Down</a>
                <a class="dropdown-item" href="#">Strong Down</a>
                <a class="dropdown-item" href="#">Strong Down + Mild Down</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
    </div>
    
    <hr>

    <div class="row">        
        <div class="col-md-6 col-lg-4 btn-group" id="F-Score">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu13" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            F-Score: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&ge; 8</a>
                <a class="dropdown-item" href="#">&ge; 7</a>
                <a class="dropdown-item" href="#">&ge; 6</a>
                <a class="dropdown-item" href="#">&ge; 5</a>
                <a class="dropdown-item" href="#">&ge; 4</a>
                <a class="dropdown-item" href="#">&ge; 3</a>
                <a class="dropdown-item" href="#">&ge; 2</a>
                <a class="dropdown-item" href="#">&ge; 1</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&le; 8</a>
                <a class="dropdown-item" href="#">&le; 7</a>
                <a class="dropdown-item" href="#">&le; 6</a>
                <a class="dropdown-item" href="#">&le; 5</a>
                <a class="dropdown-item" href="#">&le; 4</a>
                <a class="dropdown-item" href="#">&le; 3</a>
                <a class="dropdown-item" href="#">&le; 2</a>
                <a class="dropdown-item" href="#">&le; 1</a>
                <div class="dropdown-divider"></div>
                
                <a class="dropdown-item" href="#">9</a>
                <a class="dropdown-item" href="#">8</a>
                <a class="dropdown-item" href="#">7</a>
                <a class="dropdown-item" href="#">6</a>
                <a class="dropdown-item" href="#">5</a>
                <a class="dropdown-item" href="#">4</a>
                <a class="dropdown-item" href="#">3</a>
                <a class="dropdown-item" href="#">2</a>
                <a class="dropdown-item" href="#">1</a>
                <a class="dropdown-item" href="#">0</a>
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Outperforms Industry</a>
                <a class="dropdown-item" href="#">Underperforms Industry</a>
                <div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="Z-Score">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu14" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Z-Score: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Safe</a>
                <a class="dropdown-item" href="#">Grey</a>
                <a class="dropdown-item" href="#">Distress</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="M-Score">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu15" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            M-Score: <u>All</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">All</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Non Manipulator</a>
                <a class="dropdown-item" href="#">Manipulator</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
      </div>
    
    <hr>

    <div class="row">        
        <div class="col-md-6 col-lg-4 btn-group" id="MA10">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu16" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            MA10: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA10</a>
                <a class="dropdown-item" href="#">Close &lt; MA10</a>
                <a class="dropdown-item" href="#">MA10 &gt; MA20</a>
                <a class="dropdown-item" href="#">MA10 &lt; MA20</a>
                <a class="dropdown-item" href="#">MA10 &gt; MA50</a>
                <a class="dropdown-item" href="#">MA10 &lt; MA50</a>
                <a class="dropdown-item" href="#">MA10 &gt; MA100</a>
                <a class="dropdown-item" href="#">MA10 &lt; MA100</a>
                <a class="dropdown-item" href="#">MA10 &gt; MA200</a>
                <a class="dropdown-item" href="#">MA10 &lt; MA200</a>
                <a class="dropdown-item" href="#">MA10 &gt; MA250</a>
                <a class="dropdown-item" href="#">MA10 &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA10</a>
                <a class="dropdown-item" href="#">Close cross-under MA10</a>
                <a class="dropdown-item" href="#">MA10 cross-over MA20</a>
                <a class="dropdown-item" href="#">MA10 cross-under MA20</a>
                <a class="dropdown-item" href="#">MA10 cross-over MA50</a>
                <a class="dropdown-item" href="#">MA10 cross-under MA50</a>
                <a class="dropdown-item" href="#">MA10 cross-over MA100</a>
                <a class="dropdown-item" href="#">MA10 cross-under MA100</a>
                <a class="dropdown-item" href="#">MA10 cross-over MA200</a>
                <a class="dropdown-item" href="#">MA10 cross-under MA200</a>
                <a class="dropdown-item" href="#">MA10 cross-over MA250</a>
                <a class="dropdown-item" href="#">MA10 cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MA20">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu17" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            MA20: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA20</a>
                <a class="dropdown-item" href="#">Close &lt; MA20</a>
                <a class="dropdown-item" href="#">MA20 &gt; MA50</a>
                <a class="dropdown-item" href="#">MA20 &lt; MA50</a>
                <a class="dropdown-item" href="#">MA20 &gt; MA100</a>
                <a class="dropdown-item" href="#">MA20 &lt; MA100</a>
                <a class="dropdown-item" href="#">MA20 &gt; MA200</a>
                <a class="dropdown-item" href="#">MA20 &lt; MA200</a>
                <a class="dropdown-item" href="#">MA20 &gt; MA250</a>
                <a class="dropdown-item" href="#">MA20 &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA20</a>
                <a class="dropdown-item" href="#">Close cross-under MA20</a>
                <a class="dropdown-item" href="#">MA20 cross-over MA50</a>
                <a class="dropdown-item" href="#">MA20 cross-under MA50</a>
                <a class="dropdown-item" href="#">MA20 cross-over MA100</a>
                <a class="dropdown-item" href="#">MA20 cross-under MA100</a>
                <a class="dropdown-item" href="#">MA20 cross-over MA200</a>
                <a class="dropdown-item" href="#">MA20 cross-under MA200</a>
                <a class="dropdown-item" href="#">MA20 cross-over MA250</a>
                <a class="dropdown-item" href="#">MA20 cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MA50">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu18" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            MA50: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA50</a>
                <a class="dropdown-item" href="#">Close &lt; MA50</a>
                <a class="dropdown-item" href="#">MA50 &gt; MA100</a>
                <a class="dropdown-item" href="#">MA50 &lt; MA100</a>
                <a class="dropdown-item" href="#">MA50 &gt; MA200</a>
                <a class="dropdown-item" href="#">MA50 &lt; MA200</a>
                <a class="dropdown-item" href="#">MA50 &gt; MA250</a>
                <a class="dropdown-item" href="#">MA50 &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA50</a>
                <a class="dropdown-item" href="#">Close cross-under MA50</a>
                <a class="dropdown-item" href="#">MA50 cross-over MA100</a>
                <a class="dropdown-item" href="#">MA50 cross-under MA100</a>
                <a class="dropdown-item" href="#">MA50 cross-over MA200</a>
                <a class="dropdown-item" href="#">MA50 cross-under MA200</a>
                <a class="dropdown-item" href="#">MA50 cross-over MA250</a>
                <a class="dropdown-item" href="#">MA50 cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MA100">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu19" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            MA100: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA100</a>
                <a class="dropdown-item" href="#">Close &lt; MA100</a>
                <a class="dropdown-item" href="#">MA100 &gt; MA200</a>
                <a class="dropdown-item" href="#">MA100 &lt; MA200</a>
                <a class="dropdown-item" href="#">MA100 &gt; MA250</a>
                <a class="dropdown-item" href="#">MA100 &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA100</a>
                <a class="dropdown-item" href="#">Close cross-under MA100</a>
                <a class="dropdown-item" href="#">MA100 cross-over MA200</a>
                <a class="dropdown-item" href="#">MA100 cross-under MA200</a>
                <a class="dropdown-item" href="#">MA100 cross-over MA250</a>
                <a class="dropdown-item" href="#">MA100 cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MA200">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu20" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            MA200: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA200</a>
                <a class="dropdown-item" href="#">Close &lt; MA200</a>
                <a class="dropdown-item" href="#">MA200 &gt; MA250</a>
                <a class="dropdown-item" href="#">MA200 &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA200</a>
                <a class="dropdown-item" href="#">Close cross-under MA200</a>
                <a class="dropdown-item" href="#">MA200 cross-over MA250</a>
                <a class="dropdown-item" href="#">MA200 cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MA250">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu21" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            MA250: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close &gt; MA250</a>
                <a class="dropdown-item" href="#">Close &lt; MA250</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Close cross-over MA250</a>
                <a class="dropdown-item" href="#">Close cross-under MA250</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="RSI14_Daily">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu22" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            RSI14 Daily: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 70</a>
                <a class="dropdown-item" href="#">&gt; 50</a>
                <a class="dropdown-item" href="#">&lt; 50</a>
                <a class="dropdown-item" href="#">&lt; 30</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">cross-over 70</a>
                <a class="dropdown-item" href="#">cross-under 70</a>
                <a class="dropdown-item" href="#">cross-over 50</a>
                <a class="dropdown-item" href="#">cross-under 50</a>
                <a class="dropdown-item" href="#">cross-over 30</a>
                <a class="dropdown-item" href="#">cross-under 30</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Bullish Divergence</a>
                <a class="dropdown-item" href="#">Bearish Divergence</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="RSI14_Weekly">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu23" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            RSI14 Weekly: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 70</a>
                <a class="dropdown-item" href="#">&gt; 50</a>
                <a class="dropdown-item" href="#">&lt; 50</a>
                <a class="dropdown-item" href="#">&lt; 30</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">cross-over 70</a>
                <a class="dropdown-item" href="#">cross-under 70</a>
                <a class="dropdown-item" href="#">cross-over 50</a>
                <a class="dropdown-item" href="#">cross-under 50</a>
                <a class="dropdown-item" href="#">cross-over 30</a>
                <a class="dropdown-item" href="#">cross-under 30</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Bullish Divergence</a>
                <a class="dropdown-item" href="#">Bearish Divergence</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MACD_Daily">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu24" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            MACD Daily: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 0</a>
                <a class="dropdown-item" href="#">&lt; 0</a>
                <a class="dropdown-item" href="#">&gt; Signal</a>
                <a class="dropdown-item" href="#">&lt; Signal</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; Signal, &gt; 0</a>
                <a class="dropdown-item" href="#">&lt; Signal, &lt; 0</a>
                <a class="dropdown-item" href="#">&gt; Signal, &lt; 0</a>
                <a class="dropdown-item" href="#">&lt; Signal, &gt; 0</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">cross-over 0</a>
                <a class="dropdown-item" href="#">cross-under 0</a>
                <a class="dropdown-item" href="#">cross-over Signal</a>
                <a class="dropdown-item" href="#">cross-under Signal</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="MACD_Weekly">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu25" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            MACD Weekly: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 0</a>
                <a class="dropdown-item" href="#">&lt; 0</a>
                <a class="dropdown-item" href="#">&gt; Signal</a>
                <a class="dropdown-item" href="#">&lt; Signal</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; Signal, &gt; 0</a>
                <a class="dropdown-item" href="#">&lt; Signal, &lt; 0</a>
                <a class="dropdown-item" href="#">&gt; Signal, &lt; 0</a>
                <a class="dropdown-item" href="#">&lt; Signal, &gt; 0</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">cross-over 0</a>
                <a class="dropdown-item" href="#">cross-under 0</a>
                <a class="dropdown-item" href="#">cross-over Signal</a>
                <a class="dropdown-item" href="#">cross-under Signal</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="High_Low">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu26" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            High Low: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">250-day High</a>
                <a class="dropdown-item" href="#">250-day Low</a>
                <a class="dropdown-item" href="#">within 5% of 250-day High</a>
                <a class="dropdown-item" href="#">within 5% of 250-day Low</a>
                <a class="dropdown-item" href="#">[5% to 10%) below 250-day High</a>
                <a class="dropdown-item" href="#">[5% to 10%) above 250-day Low</a>
                <a class="dropdown-item" href="#">[10% to 15%) below 250-day High</a>
                <a class="dropdown-item" href="#">[10% to 15%) above 250-day Low</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">50-day High</a>
                <a class="dropdown-item" href="#">50-day Low</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">20-day High</a>
                <a class="dropdown-item" href="#">20-day Low</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Year-To-Date High</a>
                <a class="dropdown-item" href="#">Year-To-Date Low</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
        <div class="col-md-6 col-lg-4 btn-group" id="Volume">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu27" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            Volume: <u>None</u>
            <span class="caret"></span>
          </button>
              <div class="dropdown-menu screener">
                <a class="dropdown-item" href="#">None</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 3x Volume MA5</a>
                <a class="dropdown-item" href="#">&gt; 2x Volume MA5</a>
                <a class="dropdown-item" href="#">&gt; Volume MA5</a>
                <a class="dropdown-item" href="#">&lt; Volume MA5</a>
                <a class="dropdown-item" href="#">&lt; 0.5x Volume MA5</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 3x Volume MA20</a>
                <a class="dropdown-item" href="#">&gt; 2x Volume MA20</a>
                <a class="dropdown-item" href="#">&gt; Volume MA20</a>
                <a class="dropdown-item" href="#">&lt; Volume MA20</a>
                <a class="dropdown-item" href="#">&lt; 0.5x Volume MA20</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">&gt; 3x Volume MA60</a>
                <a class="dropdown-item" href="#">&gt; 2x Volume MA60</a>
                <a class="dropdown-item" href="#">&gt; Volume MA60</a>
                <a class="dropdown-item" href="#">&lt; Volume MA60</a>
                <a class="dropdown-item" href="#">&lt; 0.5x Volume MA60</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Volume MA5 &gt; 3x Volume MA20</a>
                <a class="dropdown-item" href="#">Volume MA5 &gt; 2x Volume MA20</a>
                <a class="dropdown-item" href="#">Volume MA5 &gt; Volume MA20</a>
                <a class="dropdown-item" href="#">Volume MA5 &lt; Volume MA20</a>
                <a class="dropdown-item" href="#">Volume MA5 &lt; 0.5x Volume MA20</a>
                <a class="dropdown-item" href="#">Volume MA20 &gt; 3x Volume MA60</a>
                <a class="dropdown-item" href="#">Volume MA20 &gt; 2x Volume MA60</a>
                <a class="dropdown-item" href="#">Volume MA20 &gt; Volume MA60</a>
                <a class="dropdown-item" href="#">Volume MA20 &lt; Volume MA60</a>
                <a class="dropdown-item" href="#">Volume MA20 &lt; 0.5x Volume MA60</a>
                <!--<div class="dropdown-divider"></div>-->
              </div>
        </div>
        
    </div>
      
    <div id="resetsearchbtn">
        <p></p>
        <button class="btn btn-danger reset" type="button">Reset</button>
        <button class="btn btn-warning search" type="button">Search</button>
        <p></p>
    </div>
      
    </div>
        </div>
        <p></p>
        <div id="Result" class="card clean-card text-center container" style="padding: 0;">
            <div class="card-header" id="searchresult"><h2>Search Result</h2></div>
            	<div class="container">
                    <div class="table-responsive" style="padding-top: 7px;">
                		<table id="screener_grid" class="table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                    		        <th><div><div style="float:left">Price&nbsp;</div><div style="float:right">6m</div></div></th>
                                    <th title="Market Cap" class="defaultSort">Cap (mil)</th>
                                    <th title="Technical Polar (0 - 5)">TA <img class="not-selectable" src="assets/img/TApolarW.png" width="18" height="20"></th>
                                    <th title="Valuation Polar (0 - 5)">VA <img class="not-selectable" src="assets/img/VApolarW.png" width="18" height="18"></th>
                                    <th title="Fundamental Polar (0 - 5)">FA <img class="not-selectable" src="assets/img/FApolarW.png" width="18" height="18"></th>
                                    <th>Trend Gauge</th>
                                    <th title="PE Band STDEV, Trend">PE Band σ,&nbsp<span style="font-size:10px"><i class="fas fa-chart-line"></i></span></th>
                                    <th title="PB Band STDEV, Trend">PB Band σ,&nbsp<span style="font-size:10px"><i class="fas fa-chart-line"></i></span></th>
                                    <th title="F-Score (0 - 9)">F</th>
                                    <th title="Z-Score">Z</th>
                                    <th title="M-Score">M</th>
                                    <!--<th>MA10</th>
                                    <th>MA20</th>
                                    <th>MA50</th>
                                    <th>MA100</th>
                                    <th>MA200</th>
                                    <th>MA250</th>-->
                                    <th title="RSI 14 Daily">RSI (d)</th>
                                    <th title="RSI 14 Weekly">RSI (w)</th>
                                    <!--<th>MACD(d)</th>
                                    <th>MACD(w)</th>-->
                                </tr>
                            </thead>
                     
                            <tfoot>
                                <tr>
                                   <th>Code</th>
                                    <th>Name</th>
                    		        <th><div><div style="float:left">Price&nbsp;</div><div style="float:right">6m</div></div></th>
                                    <th title="Market Cap">Cap (mil)</th>
                                    <!--<th title="Technical Polar">TA</th>
                                    <th title="Valuation Polar">VA</th>
                                    <th title="Fundamental Polar">FA</th>-->
                                    <th title="Technical Polar (0 - 5)">TA <img class="not-selectable" src="assets/img/TApolarW.png" width="18" height="20"></th>
                                    <th title="Valuation Polar (0 - 5)">VA <img class="not-selectable" src="assets/img/VApolarW.png" width="18" height="18"></th>
                                    <th title="Fundamental Polar (0 - 5)">FA <img class="not-selectable" src="assets/img/FApolarW.png" width="18" height="18"></th>
                                    <th>Trend Gauge</th>
                                    <th title="PE Band STDEV, Trend">PE Band σ,&nbsp<span style="font-size:10px"><i class="fas fa-chart-line"></i></span></th>
                                    <th title="PB Band STDEV, Trend">PB Band σ,&nbsp<span style="font-size:10px"><i class="fas fa-chart-line"></i></span></th>
                                    <th title="F-Score (0 - 9)">F</th>
                                    <th title="Z-Score">Z</th>
                                    <th title="M-Score">M</th>
                                    <!--<th>MA10</th>
                                    <th>MA20</th>
                                    <th>MA50</th>
                                    <th>MA100</th>
                                    <th>MA200</th>
                                    <th>MA250</th>-->
                                    <th title="RSI 14 Daily">RSI (d)</th>
                                    <th title="RSI 14 Weekly">RSI (w)</th>
                                    <!--<th>MACD(d)</th>
                                    <th>MACD(w)</th>-->
                                </tr>
                            </tfoot>
                        </table>
                    </div>
    
                </div>
<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
<script>
//document.getElementById("screener_grid").style.visibility = "hidden";

jQuery(window).load(function(){
    jQuery("html,body").animate({scrollTop: 0}, 1000);
});
</script>


<!-- jQuery Library -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap4.min.js"></script>
<script type="text/javascript" src="assets/js/datatables.input.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script type="text/javascript">
  var marketfromURL = "<?php echo isset($_GET["market"]) ? $_GET["market"] : ''; ?>";
  var sectorfromURL = "<?php echo isset($_GET["sector"]) ? $_GET["sector"] : ''; ?>";
  var industryfromURL = "<?php echo isset($_GET["industry"]) ? $_GET["industry"] : ''; ?>";
    
  var BasicMaterials = ["Agricultural Inputs","Aluminum","Building Materials","Chemicals","Coking Coal","Copper","Gold","Industrial Metals & Minerals","Lumber & Wood Production","Other Industrial Metals & Mining","Other Precious Metals & Mining","Paper & Paper Products","Silver","Specialty Chemicals","Steel"];
  var CommunicationServices = ["Advertising Agencies","Broadcasting","Electronic Gaming & Multimedia","Entertainment","Internet Content & Info","Publishing","Telecom Services"];
  var ConsumerCyclical = ["Apparel Manufacturing","Apparel Retail","Auto & Truck Dealerships","Auto Manufacturers","Auto Parts","Department Stores","Footwear & Accessories","Furnishings, Fixtures & Appliances","Gambling","Home Improvement Retail","Internet Retail","Leisure","Lodging","Luxury Goods","Packaging & Containers","Personal Services","Recreational Vehicles","Residential Construction","Resorts & Casinos","Restaurants","Specialty Retail","Textile Manufacturing","Travel Services"];
  var ConsumerDefensive = ["Beverages-Brewers","Beverages-Non-Alcoholic","Beverages-Wineries & Distilleries","Confectioners","Discount Stores","Education & Training Services","Farm Products","Food Distribution","Grocery Stores","Household & Personal Products","Packaged Foods","Tobacco"];
  var Energy = ["Oil & Gas E&P","Oil & Gas Equipment & Services","Oil & Gas Independent","Oil & Gas Integrated","Oil & Gas Midstream","Oil & Gas Refining & Marketing","Thermal Coal","Uranium"];
  var FinancialServices = ["Asset Management","Banks-Diversified","Banks-Regional","Capital Markets","Credit Services","Financial Conglomerates","Financial Data & Stock Exchanges","Insurance Brokers","Insurance-Diversified","Insurance-Life","Insurance-Property & Casualty","Insurance-Reinsurance","Insurance-Specialty","Mortgage Finance","Shell Companies"];
  var Healthcare = ["Biotechnology","Diagnostics & Research","Drug Delivery","Drug Manufacturers-General","Drug Manufacturers-Specialty & Generic","Health Info Services","Healthcare Plans","Medical Care Facilities","Medical Devices","Medical Distribution","Medical Instruments & Supplies","Pharmaceutical Retailers"];
  var Industrials = ["Aerospace & Defense","Airlines","Airports & Air Services","Building Products & Equipment","Business Equipment & Supplies","Conglomerates","Consulting Services","Electrical Equipment & Parts","Engineering & Construction","Farm & Heavy Construction Machinery","Industrial Distribution","Infrastructure Operations","Integrated Freight & Logistics","Marine Shipping","Metal Fabrication","Pollution & Treatment Controls","Railroads","Rental & Leasing Services","Security & Protection Services","Specialty Business Services","Specialty Industrial Machinery","Staffing & Employment Services","Tools & Accessories","Trucking","Waste Management"];
  var Other = ["Other"];
  var RealEstate = ["Real Estate Services","Real Estate-Development","Real Estate-Diversified","REIT-Diversified","REIT-Healthcare Facilities","REIT-Hotel & Motel","REIT-Industrial","REIT-Mortgage","REIT-Office","REIT-Residential","REIT-Retail","REIT-Specialty"];
  //var Services = ["Air Delivery & Freight Services","Business Services","Entertainment - Diversified","Publishing - Books","Specialty Retail, Other","Sporting Activities","Staffing & Outsourcing Services","Trucking"];
  var Technology = ["Communication Equipment","Computer Hardware","Consumer Electronics","Diversified Electronics","Electronic Components","Electronics & Computer Distribution","Info Technology Services","Scientific & Technical Instruments","Semiconductor Equipment & Materials","Semiconductors","Software-Application","Software-Infrastructure","Solar","Wireless Communications"];
  var Utilities = ["Utilities-Diversified","Utilities-Independent Power Producers","Utilities-Regulated Electric","Utilities-Regulated Gas","Utilities-Regulated Water","Utilities-Renewable"];
  var allSector = [BasicMaterials, CommunicationServices, ConsumerCyclical, ConsumerDefensive, Energy, FinancialServices, Healthcare, Industrials, Other, RealEstate, /*Services,*/ Technology, Utilities];
  var allSectorTXT = ["Basic Materials", "Communication", "Consumer Cyclical", "Consumer Defensive", "Energy", "Financial", "Healthcare", "Industrials", "Other", "Real Estate", /*"Services",*/ "Technology", "Utilities"];

$(".dropdown-menu a").click(function(){
  var group = $(this).parents(".btn-group");
  var button = group.find('.btn');
  
  if($(this).text() == "All" || $(this).text() == "None")
  {
    if (group[0].id == "PE_Band_Trend" || group[0].id == "PB_Band_Trend")
        button.html(group[0].id.replace(/[_]/g,' ') + ' <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u>' + $(this).text() + '</u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Technical_Polar")
        button.html('<img src="assets/img/TApolar.png" width="24" height="26"> ' + group[0].id.replace(/[_]/g,' ') + ": <u>" + $(this).text() + '</u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Valuation_Polar")
        button.html('<img src="assets/img/VApolar.png" width="24" height="24"> ' + group[0].id.replace(/[_]/g,' ') + ": <u>" + $(this).text() + '</u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Fundamental_Polar")
        button.html('<img src="assets/img/FApolar.png" width="24" height="24"> ' + group[0].id.replace(/[_]/g,' ') + ": <u>" + $(this).text() + '</u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Trend_Gauge")
        button.html('<span style="font-size: 21px; color:#b8b8b8;"><i class="fas fa-tachometer-alt"></i></span> ' + group[0].id.replace(/[_]/g,' ') + ": <u>" + $(this).text() + '</u><span class="caret">&nbsp;</span>');
    else
        button.html(group[0].id.replace(/[_]/g,' ') + ": <u>" + $(this).text() + '</u><span class="caret">&nbsp;</span>');
  }
  else
  {
    if (group[0].id == "PE_Band_Trend" || group[0].id == "PB_Band_Trend")
        button.html(group[0].id.replace(/[_]/g,' ') + ' <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u><span style="background-color:yellow;">' + $(this).text() + '</span></u><span class="caret">&nbsp;</span>');
        else if (group[0].id == "Technical_Polar")
        button.html('<img src="assets/img/TApolar.png" width="24" height="26"> ' + group[0].id.replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + $(this).text() + '</span></u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Valuation_Polar")
        button.html('<img src="assets/img/VApolar.png" width="24" height="24"> ' + group[0].id.replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + $(this).text() + '</span></u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Fundamental_Polar")
        button.html('<img src="assets/img/FApolar.png" width="24" height="24"> ' + group[0].id.replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + $(this).text() + '</span></u><span class="caret">&nbsp;</span>');
    else if (group[0].id == "Trend_Gauge")
        button.html('<span style="font-size: 21px; color:#b8b8b8;"><i class="fas fa-tachometer-alt"></i></span> ' + group[0].id.replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + $(this).text() + '</span></u><span class="caret">&nbsp;</span>');
    else
        button.html(group[0].id.replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + $(this).text() + '</span></u>&nbsp;<span class="caret"></span>');
  }
    
  button.val($(this).data('value'));
  
  if (button[0].id == "dropdownMenu3")
  {
    for (var i = 0; i < allSector.length; i++)
    {
        for (var j = 0; j < allSector[i].length; j++)
        {
            if ($(this).text() == allSector[i][j])
            {
                $("#Sector").find('.btn').html('Sector: <u><span style="background-color:yellow;">' + allSectorTXT[i] + '</span></u>&nbsp;<span class="caret"></span>');
                break;
            }
        }
    }
  }
  
  if (button[0].id == "dropdownMenu2")
  {
    $("#Industry").find('.btn').html('Industry: <u>' + 'All' + '</u>&nbsp;<span class="caret"></span>');
  }
  
  if (button[0].id == "dropdownMenu1")
  {
    if($(this).text() == "NYSE Arca")
    {
        menu_disable();
    }
    else
    {
        menu_enable();
    }
  }
  

  jQuery("html,body").animate({scrollTop: 0}, 500);


});

function menu_enable()
{
    document.getElementById("dropdownMenu2").disabled=false;
    document.getElementById("dropdownMenu3").disabled=false;
    document.getElementById("dropdownMenu6").disabled=false;
    document.getElementById("dropdownMenu7").disabled=false;
    document.getElementById("dropdownMenu9").disabled=false;
    document.getElementById("dropdownMenu10").disabled=false;
    document.getElementById("dropdownMenu11").disabled=false;
    document.getElementById("dropdownMenu12").disabled=false;
    document.getElementById("dropdownMenu13").disabled=false;
    document.getElementById("dropdownMenu14").disabled=false;
    document.getElementById("dropdownMenu15").disabled=false;
    
    document.getElementById("dropdownMenu2").style.backgroundColor = "white";
    document.getElementById("dropdownMenu3").style.backgroundColor = "white";
    document.getElementById("dropdownMenu6").style.backgroundColor = "white";
    document.getElementById("dropdownMenu7").style.backgroundColor = "white";
    document.getElementById("dropdownMenu9").style.backgroundColor = "white";
    document.getElementById("dropdownMenu10").style.backgroundColor = "white";
    document.getElementById("dropdownMenu11").style.backgroundColor = "white";
    document.getElementById("dropdownMenu12").style.backgroundColor = "white";
    document.getElementById("dropdownMenu13").style.backgroundColor = "white";
    document.getElementById("dropdownMenu14").style.backgroundColor = "white";
    document.getElementById("dropdownMenu15").style.backgroundColor = "white";
}

function menu_disable()
{
    document.getElementById("dropdownMenu2").disabled=true;
    document.getElementById("dropdownMenu3").disabled=true;
    document.getElementById("dropdownMenu6").disabled=true;
    document.getElementById("dropdownMenu7").disabled=true;
    document.getElementById("dropdownMenu9").disabled=true;
    document.getElementById("dropdownMenu10").disabled=true;
    document.getElementById("dropdownMenu11").disabled=true;
    document.getElementById("dropdownMenu12").disabled=true;
    document.getElementById("dropdownMenu13").disabled=true;
    document.getElementById("dropdownMenu14").disabled=true;
    document.getElementById("dropdownMenu15").disabled=true;
    
    document.getElementById("dropdownMenu2").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu3").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu6").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu7").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu9").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu10").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu11").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu12").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu13").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu14").style.backgroundColor = "lightgrey";
    document.getElementById("dropdownMenu15").style.backgroundColor = "lightgrey";
}

var all = ["Sector","Industry","Market_Cap","Technical_Polar","Valuation_Polar","Fundamental_Polar","Trend_Gauge","Price_Channel_Pos","Price_Channel_Trend","PE_Band_STDEV_σ","PE_Band_Trend","PB_Band_STDEV_σ","PB_Band_Trend","F-Score","Z-Score","M-Score"];
var none = ["MA10","MA20","MA50","MA100","MA200","MA250","RSI14_Daily","RSI14_Weekly","MACD_Daily","MACD_Weekly","High_Low","Volume"];
$(document).ready(function(){ 
    if (!window.mobileAndTabletcheck())
        document.getElementById("autocomplete").focus();

    $('.copyMe').on('change', function() {
        $(".copyMe").val($(this).val());
    });
    
    $("#resetsearchbtn .reset").click(function(){
        $("#Market").find('.btn').html('Market: <u><span style="background-color:yellow;">NYSE + Nasdaq</span></u>&nbsp;<span class="caret"></span>');
        
        for (var i = 0; i < all.length; i++)
        {
            if (all[i] == "PE_Band_Trend" || all[i] == "PB_Band_Trend")
                $("#" + all[i]).find('.btn').html(all[i].replace(/[_]/g,' ') + ' <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u>All</u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Technical_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/TApolar.png" width="24" height="26"> ' + all[i].replace(/[_]/g,' ') + ': <u>All</u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Valuation_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/VApolar.png" width="24" height="24"> ' + all[i].replace(/[_]/g,' ') + ': <u>All</u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Fundamental_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/FApolar.png" width="24" height="24"> ' + all[i].replace(/[_]/g,' ') + ': <u>All</u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Trend_Gauge")
                $("#" + all[i]).find('.btn').html('<span style="font-size: 21px; color:#b8b8b8;"><i class="fas fa-tachometer-alt"></i></span> ' + all[i].replace(/[_]/g,' ') + ': <u>All</u>&nbsp;<span class="caret"></span>');
            else
                $("#" + all[i]).find('.btn').html(all[i].replace(/[_]/g,' ') + ': <u>All</u>&nbsp;<span class="caret"></span>');
        }
        for (var i = 0; i < none.length; i++)
        {
            $("#" + none[i]).find('.btn').html(none[i].replace(/[_]/g,' ') + ': <u>None</u>&nbsp;<span class="caret"></span>');
        }
        
        menu_enable();
    });

    if (marketfromURL != '')
        search(marketfromURL, sectorfromURL, industryfromURL);
        
    $("#resetsearchbtn .search").click(function(){
        search('', '', '');
    });

});

function search(marketfromURL, sectorfromURL, industryfromURL) {
    //document.getElementById("screener_grid").style.visibility = "visible";
    
    var market = "";
    var markettxt0 = document.getElementById('dropdownMenu1').textContent;
    var markettxt = markettxt0.trim();//substring(0, markettxt0.length - 1);
    if (markettxt == "Market: NYSE + Nasdaq" || markettxt0 == "Market: NYSE + Nasdaq")
        market = "NYSE + NASDAQ";
    else if (markettxt == "Market: NYSE")
        market = "NYSE";
    else if (markettxt == "Market: Nasdaq")
        market = "NASDAQ";
    else if (markettxt == "Market: NYSE Arca")
        market = "NYSEARCA";
    else if (markettxt == "Market: London")
        market = "LSE";
    else if (markettxt == "Market: Australia")
        market = "ASX";
    else if (markettxt == "Market: Toronto TSX")
        market = "TSX";
    else if (markettxt == "Market: India NSE")
        market = "NSE";
    else if (markettxt == "Market: Tokyo")
        market = "TYO";
    else if (markettxt == "Market: Hong Kong")
        market = "HKEX";
    else if (markettxt == "Market: Shanghai")
        market = "SHSE";
    else if (markettxt == "Market: Shenzhen")
        market = "SZSE";
    
    var sector = document.getElementById('dropdownMenu2').textContent.split(':')[1].trim();
    var industry = document.getElementById('dropdownMenu3').textContent.split(':')[1].trim();

    if (marketfromURL != '')
    {
        market = marketfromURL;//.split('@').join(' ').split('$').join('&');
        if (market == "NYSE + NASDAQ")
        {
            markettxt = "NYSE + Nasdaq";
        }
        else if (market == "NYSE")
        {
            markettxt = "NYSE";
        }
        else if (market == "NASDAQ")
        {
            //market = "NASDAQ";
            markettxt = "Nasdaq";
        }
        else if (market == "NYSEARCA")
        {
            markettxt = "NYSE Arca";

            menu_disable();
        }
        else if (market == "LSE")
        {
            markettxt = "London";
        }
        else if (market == "ASX")
        {
            markettxt = "Australia";
        }
        else if (market == "TSX")
        {
            markettxt = "Toronto TSX";
        }
        else if (market == "NSE")
        {
            markettxt = "India NSE";
        }
        else if (market == "TYO")
        {
            markettxt = "Tokyo";
        }
        else if (market == "HKEX")
        {
            //market = "HKEX";
            markettxt = "Hong Kong";
        }
        else if (market == "SZSE")
        {
            markettxt = "Shenzhen";
        }
        else if (market == "SHSE")
        {
            markettxt = "Shanghai";
        }
        else
        {
            return; // no such market
        }
            
        $("#Market").find('.btn').html('Market: <u><span style="background-color:yellow;">'+ markettxt +'</span></u>&nbsp;<span class="caret"></span>');
    }
    
    document.title = (markettxt.replace('Market: ', '') != '' ? markettxt.replace('Market: ', '') + ' ' : '') + "Stock Screener - 360MiQ.com";
    document.getElementById('h1_text').textContent = (markettxt.replace('Market: ', '') != '' ? markettxt.replace('Market: ', '') + ' ' : '') + "Stock Screener – Filter by Technical & Fundamental Criteria";
    
    var isSectorFound = false;
    if (sectorfromURL != '')
    {
        sector = sectorfromURL;//.split('@').join(' ').split('$').join('&');
        if (sector != 'All')
        {
            for (var i = 0; i < allSectorTXT.length; i++)
            {
                if (sector == allSectorTXT[i])
                {
                    isSectorFound = true;
                    break;
                }
            }
            
            if (isSectorFound)
                $("#Sector").find('.btn').html('Sector: <u><span style="background-color:yellow;">'+ sector +'</span></u>&nbsp;<span class="caret"></span>');
            else
                return;
        }
    }

    var isIdustryFound = false;
    if (industryfromURL != '')
    {
        industry = industryfromURL;//.split('@').join(' ').split('$').join('&');
        if (industry != 'All')
        {
            if (isSectorFound)
            {
                for (var i = 0; i < allSector.length; i++)
                {
                    for (var j = 0; j < allSector[i].length; j++)
                    {
                        if (industry == allSector[i][j])
                        {
                            isIdustryFound = true;
                            break;
                        }
                    }
                }
            }
            
            if (isIdustryFound)
                $("#Industry").find('.btn').html('Industry: <u><span style="background-color:yellow;">'+ industry +'</span></u>&nbsp;<span class="caret"></span>');
            else 
                return;
        }
    }
    
/*    if (sector == "Communication")
        sector = "Communication Services";
    else if (sector == "Financial")
        sector = "Financial Services";
*/        
    sector = sector.match(/\b(\w)/g).join('');
    
    var searchParams = new URLSearchParams(window.location.search);
    var marketcap = searchParams.get("marketcap");
    var polar_ta = searchParams.get("polar_ta");
    var polar_va = searchParams.get("polar_va");
    var polar_fa = searchParams.get("polar_fa");
    var polar_trendgauge = searchParams.get("polar_trendgauge");
    var channel_pos = searchParams.get("channel_pos");
    var channel_trend = searchParams.get("channel_trend");
    var pe_stdev = searchParams.get("pe_stdev");
    var pe_trend = searchParams.get("pe_trend");
    var pb_stdev = searchParams.get("pb_stdev");
    var pb_trend = searchParams.get("pb_trend");
    var fscore = searchParams.get("fscore");
    var zscore = searchParams.get("zscore");
    var mscore = searchParams.get("mscore");
    var ma10 = searchParams.get("ma10");
    var ma20 = searchParams.get("ma20");
    var ma50 = searchParams.get("ma50");
    var ma100 = searchParams.get("ma100");
    var ma200 = searchParams.get("ma200");
    var ma250 = searchParams.get("ma250");
    var rsi14d = searchParams.get("rsi14d");
    var rsi14w = searchParams.get("rsi14w");
    var macdd = searchParams.get("macdd");
    var macdw = searchParams.get("macdw");
    var highlow = searchParams.get("highlow");
    var volume = searchParams.get("volume");

    var alltmp = [sector, industry, marketcap, polar_ta, polar_va, polar_fa, polar_trendgauge, channel_pos, channel_trend, pe_stdev, pe_trend, pb_stdev, pb_trend, fscore, zscore, mscore];
    var nonetmp = [ma10, ma20, ma50, ma100, ma200, ma250, rsi14d, rsi14w, macdd, macdw, highlow, volume];

    if (marketfromURL != '')
    {
        for (var i = 2; i < all.length; i++)
        {
            if (alltmp[i] === null)
                continue
                
            if (all[i] == "PE_Band_Trend" || all[i] == "PB_Band_Trend")
                $("#" + all[i]).find('.btn').html(all[i].replace(/[_]/g,' ') + ' <span style="font-size:12px"><i class="fas fa-chart-line"></i></span>: <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Technical_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/TApolar.png" width="24" height="26"> ' + all[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Valuation_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/VApolar.png" width="24" height="24"> ' + all[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Fundamental_Polar")
                $("#" + all[i]).find('.btn').html('<img src="assets/img/FApolar.png" width="24" height="24"> ' + all[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
            else if (all[i] == "Trend_Gauge")
                $("#" + all[i]).find('.btn').html('<span style="font-size: 21px; color:#b8b8b8;"><i class="fas fa-tachometer-alt"></i></span> ' + all[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
            else
                $("#" + all[i]).find('.btn').html(all[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + alltmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
        }
        for (var i = 0; i < none.length; i++)
        {
            if (nonetmp[i] === null)
                continue
                
            $("#" + none[i]).find('.btn').html(none[i].replace(/[_]/g,' ') + ': <u><span style="background-color:yellow;">' + nonetmp[i] + '</span></u>&nbsp;<span class="caret"></span>');
        }
    }

    if (marketcap !== null)
        marketcap = marketcap.replace(/All/g, 'A').split('(')[0].replace(/ /g, '').replace(/10/g, '1').replace(/20/g, '2').replace(/50/g, '5').replace(/100/g, '10').replace(/200/g, '20').replace(/500/g, '50').replace(/Top/g, 'T').replace(/Bottom/g, 'B').replace(/Mega/g, 'M').replace(/Large/g, 'L').replace(/Small/g, 'S').replace(/Micro/g, 'Mic').replace(/Nano/g, 'N').replace(/≤/g, '<').replace(/≥/g, '>').trim();
    if (polar_ta !== null)    
        polar_ta = polar_ta.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
    if (polar_va !== null)
        polar_va = polar_va.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
    if (polar_fa !== null)
        polar_fa = polar_fa.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
    if (polar_trendgauge !== null)        
        polar_trendgauge = polar_trendgauge.match(/\b(\w)/g).join('');
    if (channel_pos !== null)
        channel_pos = channel_pos.match(/\b(\w)/g).join('');
    if (channel_trend !== null)
        channel_trend = channel_trend.match(/\b(\w)/g).join('');
    if (pe_stdev !== null)
        pe_stdev = pe_stdev.replace(/Median/g, '').replace(/to/g, '').replace(/ /g, '').replace(/All/g, 'A');
    if (pe_trend !== null)
        pe_trend = pe_trend.trim().match(/\b(\w)/g).join('');
    if (pb_stdev !== null)
        pb_stdev = pb_stdev.replace(/Median/g, '').replace(/to/g, '').replace(/ /g, '').replace(/All/g, 'A');
    if (pb_trend !== null)
        pb_trend = pb_trend.trim().match(/\b(\w)/g).join('');
    if (fscore !== null)
        fscore = fscore.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
    if (zscore !== null)
        zscore = zscore.match(/\b(\w)/g).join('');
    if (mscore !== null)
        mscore = mscore.match(/\b(\w)/g).join('');
    if (ma10 !== null)
        ma10 = ma10.replace(/MA10/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (ma20 !== null)
        ma20 = ma20.replace(/MA20/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (ma50 !== null)
        ma50 = ma50.replace(/MA50/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (ma100 !== null)
        ma100 = ma100.replace(/MA100/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (ma200 !== null)
        ma200 = ma200.replace(/MA200/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (ma250 !== null)
        ma250 = ma250.replace(/MA250/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
    if (rsi14d !== null)
        rsi14d = rsi14d.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Bullish Divergence/g, 'u').replace(/Bearish Divergence/g, 'e').replace(/ /g, '').replace(/None/g, 'N').replace(/0/g, '');
    if (rsi14w !== null)
        rsi14w = rsi14w.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Bullish Divergence/g, 'u').replace(/Bearish Divergence/g, 'e').replace(/ /g, '').replace(/None/g, 'N').replace(/0/g, '');
    if (macdd !== null)
        macdd = macdd.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Signal/g, 's').replace(/,/g, '').replace(/ /g, '').replace(/None/g, 'N');
    if (macdw !== null)
        macdw = macdw.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Signal/g, 's').replace(/,/g, '').replace(/ /g, '').replace(/None/g, 'N');
    if (highlow !== null)
        highlow = highlow.replace(/\[/g, '').replace(/\)/g, '').replace(/ear-To-Date/g, '').replace(/0-day/g, '').replace(/%/g, '').replace(/within/g, '').replace(/of/g, '').replace(/to/g, '').replace(/above/g, '').replace(/below/g, '').replace(/High/g, 'H').replace(/Low/g, 'L').replace(/ /g, '').replace(/None/g, 'N');
    if (volume !== null)
        volume = volume.replace(/Volume MA/g, '').replace(/ /g, '').replace(/x/g, '').replace(/0/g, '').replace(/None/g, 'N');
        
    var marketcaptxt = document.getElementById('dropdownMenu4').textContent.split(':')[1];
    var sectortxt = document.getElementById('dropdownMenu2').textContent.split(':')[1].trim();
    var industrytxt = document.getElementById('dropdownMenu3').textContent.split(':')[1].trim();
    var marketcaptxt = document.getElementById('dropdownMenu4').textContent.split(':')[1].trim();
    var polar_tatxt = document.getElementById('dropdownMenu5').textContent.split(':')[1].trim();
    var polar_vatxt = document.getElementById('dropdownMenu6').textContent.split(':')[1].trim();
    var polar_fatxt = document.getElementById('dropdownMenu7').textContent.split(':')[1].trim();
    var polar_trendgaugetxt = document.getElementById('dropdownMenu8').textContent.split(':')[1].trim();
    var channel_postxt = document.getElementById('dropdownMenu8a').textContent.split(':')[1].trim();
    var channel_trendtxt = document.getElementById('dropdownMenu8b').textContent.split(':')[1].trim();
    var pe_stdevtxt = document.getElementById('dropdownMenu9').textContent.split(':')[1].trim();
    var pe_trendtxt = document.getElementById('dropdownMenu10').textContent.split(':')[1].trim();
    var pb_stdevtxt = document.getElementById('dropdownMenu11').textContent.split(':')[1].trim();
    var pb_trendtxt = document.getElementById('dropdownMenu12').textContent.split(':')[1].trim();
    var fscoretxt = document.getElementById('dropdownMenu13').textContent.split(':')[1].trim();
    var zscoretxt = document.getElementById('dropdownMenu14').textContent.split(':')[1].trim();
    var mscoretxt = document.getElementById('dropdownMenu15').textContent.split(':')[1].trim();
    var ma10txt = document.getElementById('dropdownMenu16').textContent.split(':')[1].trim();
    var ma20txt = document.getElementById('dropdownMenu17').textContent.split(':')[1].trim();
    var ma50txt = document.getElementById('dropdownMenu18').textContent.split(':')[1].trim();
    var ma100txt = document.getElementById('dropdownMenu19').textContent.split(':')[1].trim();
    var ma200txt = document.getElementById('dropdownMenu20').textContent.split(':')[1].trim();
    var ma250txt = document.getElementById('dropdownMenu21').textContent.split(':')[1].trim();
    var rsi14dtxt = document.getElementById('dropdownMenu22').textContent.split(':')[1].trim();
    var rsi14wtxt = document.getElementById('dropdownMenu23').textContent.split(':')[1].trim();
    var macddtxt = document.getElementById('dropdownMenu24').textContent.split(':')[1].trim();
    var macdwtxt = document.getElementById('dropdownMenu25').textContent.split(':')[1].trim();
    var highlowtxt = document.getElementById('dropdownMenu26').textContent.split(':')[1].trim();
    var volumetxt = document.getElementById('dropdownMenu27').textContent.split(':')[1].trim();

    if (marketfromURL == '')
    {
        marketcap = marketcaptxt.replace(/All/g, 'A').split('(')[0].replace(/ /g, '').replace(/10/g, '1').replace(/20/g, '2').replace(/50/g, '5').replace(/100/g, '10').replace(/200/g, '20').replace(/500/g, '50').replace(/Top/g, 'T').replace(/Bottom/g, 'B').replace(/Mega/g, 'M').replace(/Large/g, 'L').replace(/Small/g, 'S').replace(/Micro/g, 'Mic').replace(/Nano/g, 'N').replace(/≤/g, '<').replace(/≥/g, '>').trim();
        
        polar_ta = polar_tatxt.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
        polar_va = polar_vatxt.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
        polar_fa = polar_fatxt.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
        
        polar_trendgauge = polar_trendgaugetxt.match(/\b(\w)/g).join('');
        channel_pos = channel_postxt.match(/\b(\w)/g).join('');
        channel_trend = channel_trendtxt.match(/\b(\w)/g).join('');
        
        pe_stdev = pe_stdevtxt.replace(/Median/g, '').replace(/to/g, '').replace(/ /g, '').replace(/All/g, 'A');
        pe_trend = pe_trendtxt.trim().match(/\b(\w)/g).join('');
        pb_stdev = pb_stdevtxt.replace(/Median/g, '').replace(/to/g, '').replace(/ /g, '').replace(/All/g, 'A');
        pb_trend = pb_trendtxt.trim().match(/\b(\w)/g).join('');
        
        fscore = fscoretxt.replace(/Outperforms Industry/g, 'oi').replace(/Underperforms Industry/g, 'ui').replace(/ /g, '').replace(/All/g, 'A').replace(/≤/g, '<').replace(/≥/g, '>');
        zscore = zscoretxt.match(/\b(\w)/g).join('');
        mscore = mscoretxt.match(/\b(\w)/g).join('');
        
        ma10 = ma10txt.replace(/MA10/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        ma20 = ma20txt.replace(/MA20/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        ma50 = ma50txt.replace(/MA50/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        ma100 = ma100txt.replace(/MA100/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        ma200 = ma200txt.replace(/MA200/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        ma250 = ma250txt.replace(/MA250/g, '').replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/MA/g, '').replace(/ /g, '').replace(/Close/g, 'c').replace(/None/g, 'N');
        rsi14d = rsi14dtxt.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Bullish Divergence/g, 'u').replace(/Bearish Divergence/g, 'e').replace(/ /g, '').replace(/None/g, 'N').replace(/0/g, '');
        rsi14w = rsi14wtxt.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Bullish Divergence/g, 'u').replace(/Bearish Divergence/g, 'e').replace(/ /g, '').replace(/None/g, 'N').replace(/0/g, '');
        macdd = macddtxt.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Signal/g, 's').replace(/,/g, '').replace(/ /g, '').replace(/None/g, 'N');
        macdw = macdwtxt.replace(/cross-over/g, 'o').replace(/cross-under/g, 'u').replace(/Signal/g, 's').replace(/,/g, '').replace(/ /g, '').replace(/None/g, 'N');
        highlow = highlowtxt.replace(/\[/g, '').replace(/\)/g, '').replace(/ear-To-Date/g, '').replace(/0-day/g, '').replace(/%/g, '').replace(/within/g, '').replace(/of/g, '').replace(/to/g, '').replace(/above/g, '').replace(/below/g, '').replace(/High/g, 'H').replace(/Low/g, 'L').replace(/ /g, '').replace(/None/g, 'N');
        volume = volumetxt.replace(/Volume MA/g, '').replace(/ /g, '').replace(/x/g, '').replace(/0/g, '').replace(/None/g, 'N');
    }
    
    if (market == "NYSEARCA")
    {
        sector = "A";
        industry = "All";
        polar_va = "A";
        polar_fa = "A";
        pe_stdev = "A";
        pe_trend = "A";
        pb_stdev = "A";
        pb_trend = "A";
        fscore = "A";
        zscore = "A";
        mscore = "A";
    }
    
    if ('URLSearchParams' in window) {
        if (market !== null)
            searchParams.set("market", market);
            
		if (sector !== null)
		{
			if (sectortxt != 'All')
                searchParams.set("sector", sectortxt);
            else
                searchParams.delete("sector");
		}
        
        if (industry !== null)
		{
			if (industrytxt != 'All')
                searchParams.set("industry", industrytxt);
            else
                searchParams.delete("industry");
		}
            
        if (marketcap !== null)
		{
			if (marketcaptxt != 'All')
                searchParams.set("marketcap", marketcaptxt);
            else
                searchParams.delete("marketcap");
		}
		
        if (polar_ta !== null)
		{
			if (polar_tatxt != 'All')
            {
                searchParams.set("polar_ta", polar_tatxt);
            }
            else
                searchParams.delete("polar_ta");
		}
		
        if (polar_va !== null)
		{
			if (polar_vatxt != 'All')
                searchParams.set("polar_va", polar_vatxt);
            else
                searchParams.delete("polar_va");
		}
		
        if (polar_fa !== null)
		{
			if (polar_fatxt != 'All')
                searchParams.set("polar_fa", polar_fatxt);
            else
                searchParams.delete("polar_fa");
		}
		
        if (polar_trendgauge !== null)
		{
			if (polar_trendgaugetxt != 'All')
                searchParams.set("polar_trendgauge", polar_trendgaugetxt);
            else
                searchParams.delete("polar_trendgauge");
		}
		
        if (channel_pos !== null)
		{
			if (channel_postxt != 'All')
                searchParams.set("channel_pos", channel_postxt);
            else
                searchParams.delete("channel_pos");
		}
		    
        if (channel_trend !== null)
		{
			if (channel_trendtxt != 'All')
                searchParams.set("channel_trend", channel_trendtxt);
            else
                searchParams.delete("channel_trend");
		}
		    
        if (pe_stdev !== null)
		{
			if (pe_stdevtxt != 'All')
                searchParams.set("pe_stdev", pe_stdevtxt);
            else
                searchParams.delete("pe_stdev");
		}
		    
        if (pe_trend !== null)
		{
			if (pe_trendtxt != 'All')
                searchParams.set("pe_trend", pe_trendtxt);
            else
                searchParams.delete("pe_trend");
		}
		    
        if (pb_stdev !== null)
		{
			if (pb_stdevtxt != 'All')
                searchParams.set("pb_stdev", pb_stdevtxt);
            else
                searchParams.delete("pb_stdev");
		}
		    
        if (pb_trend !== null)
		{
			if (pb_trendtxt != 'All')
                searchParams.set("pb_trend", pb_trendtxt);
            else
                searchParams.delete("pb_trend");
		}
		    
        if (fscore !== null)
		{
			if (fscoretxt != 'All')
                searchParams.set("fscore", fscoretxt);
            else
                searchParams.delete("fscore");
		}
		    
        if (zscore !== null)
		{
			if (zscoretxt != 'All')
                searchParams.set("zscore", zscoretxt);
            else
                searchParams.delete("zscore");
		}
		    
        if (mscore !== null)
		{
			if (mscoretxt != 'All')
                searchParams.set("mscore", mscoretxt);
            else
                searchParams.delete("mscore");
		}
		    
        if (ma10 !== null)
		{
			if (ma10txt != 'None')
                searchParams.set("ma10", ma10txt);
            else
                searchParams.delete("ma10");
		}
		    
        if (ma20 !== null)
		{
			if (ma20txt != 'None')
                searchParams.set("ma20", ma20txt);
            else
                searchParams.delete("ma20");
		}
		    
        if (ma50 !== null)
		{
			if (ma50txt != 'None')
                searchParams.set("ma50", ma50txt);
            else
                searchParams.delete("ma50");
		}
		    
        if (ma100 !== null)
		{
			if (ma100txt != 'None')
                searchParams.set("ma100", ma100txt);
            else
                searchParams.delete("ma100");
		}
		    
        if (ma200 !== null)
		{
			if (ma200txt != 'None')
                searchParams.set("ma200", ma200txt);
            else
                searchParams.delete("ma200");
		}
		    
        if (ma250 !== null)
		{
			if (ma250txt != 'None')
                searchParams.set("ma250", ma250txt);
            else
                searchParams.delete("ma250");
		}
		    
        if (rsi14d !== null)
		{
			if (rsi14dtxt != 'None')
                searchParams.set("rsi14d", rsi14dtxt);
            else
                searchParams.delete("rsi14d");
		}
		    
        if (rsi14w !== null)
		{
			if (rsi14wtxt != 'None')
                searchParams.set("rsi14w", rsi14wtxt);
            else
                searchParams.delete("rsi14w");
		}
		    
        if (macdd !== null)
		{
			if (macddtxt != 'None')
                searchParams.set("macdd", macddtxt);
            else
                searchParams.delete("macdd");
		}
		    
        if (macdw !== null)
		{
			if (macdwtxt != 'None')
                searchParams.set("macdw", macdwtxt);
            else
                searchParams.delete("macdw");
		}
		    
        if (highlow !== null)
		{
			if (highlowtxt != 'None')
                searchParams.set("highlow", highlowtxt);
            else
                searchParams.delete("highlow");
		}
		
        if (volume !== null)
		{
			if (volumetxt != 'None')
                searchParams.set("volume", volumetxt);
            else
                searchParams.delete("volume");
		}
        var newRelativePathQuery = window.location.pathname + '?' + searchParams.toString().replace(/\+/g, "%20"); // make sure url is the same from stockinfo and sectorperformance
        history.pushState(null, '', newRelativePathQuery);
    }
    
    
    var table = $('#screener_grid')
        .on('preXhr.dt', function () {
            $('#screener_grid tbody').empty();
            
            document.getElementById("searchresult").innerHTML = '<h2>Search Result</h2>';
        })
        .on("xhr.dt", function (e, settings, json, xhr) {
            settings.json = {
                data: json
            };
         
            document.getElementById("searchresult").innerHTML = '<h2>Search Result</h2><span style="vertical-align:top;font-size:14px">(showing stocks that have traded on ' + settings.json.data.maxdate + ')</span><br><span style="font-size:14px;">Bookmark this search in your browser for future screening</span>';
        })
        /*.on( 'draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        })*/
        .DataTable({
        order: [
            [$('th.defaultSort').index(), 'desc'], // sort market_cap desc
        ],
        lengthMenu: [30, 60, 100, 200],
        language : {
            sLengthMenu: "Show _MENU_ entries per page"
        },
        destroy: true,
        pagingType: 'input',
        //pageLength: 30,
        dom: 'Blfrtip',
        buttons: [
            { extend: 'copyHtml5', footer: true },
            { extend: 'excelHtml5', footer: true },
            { extend: 'csvHtml5', footer: true },
            //{ extend: 'pdfHtml5', footer: true },
            //{ extend: 'print', footer: true },
            { extend: 'colvis', postfixButtons: [ 'colvisRestore' ], columns: ':gt(0)'}
        ],
    	bProcessing: true,
    	responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.childRowImmediate,
                type: 'none',
                target: ''
            }
        },
        serverSide: true,
        ajax: {
            url :"db_screener_get.php", // json datasource
            type: "post",  // type of method  ,GET/POST/DELETE
            "data": {
                "exchange": market,
                "sector": sector,
                "industry": industry,
                "marketcap": marketcap,
                "polar_ta": polar_ta,
                "polar_va": polar_va,
                "polar_fa": polar_fa,
                "polar_trendgauge": polar_trendgauge,
                "channel_pos": channel_pos,
                "channel_trend": channel_trend,
                "pe_stdev": pe_stdev,
                "pe_trend": pe_trend,
                "pb_stdev": pb_stdev,
                "pb_trend": pb_trend,
                "fscore": fscore,
                "zscore": zscore,
                "mscore": mscore,
                "ma10": ma10,
                "ma20": ma20,
                "ma50": ma50,
                "ma100": ma100,
                "ma200": ma200,
                "ma250": ma250,
                "rsi14d": rsi14d,
                "rsi14w": rsi14w,
                "macdd": macdd,
                "macdw": macdw,
                "highlow": highlow,
                "volume": volume,
            },
            error: function(){
                $("#screener_grid_processing").css("display","none");
            }
        },
        "columnDefs": [
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    if (data.length > 7)
                        return '<span style="font-size:11px"><a href="stockinfo?code=' + data + '" target="_blank">'+data+'</a></span>';
                    else                        
                        return '<a href="stockinfo?code=' + data + '" target="_blank">'+data+'</a>';
                },
                "targets": 0
            },
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    if (row[26] === null || row[26] == '')
                        row[26] = '&#8209;';
                    if (row[27] === null || row[27] == '')
                        row[27] = '&#8209;';
                    var info = ' &nbsp;<span class="help-tip4 mytooltip3" data-tooltip="Sector:&nbsp;' + row[26].replace(/ /g, '&nbsp;') + '&#13;Industry:&nbsp;' + row[27].replace(/ /g, '&nbsp;').replace(/-/g, '&#8209;') + '"></span>';
                    data = data.replace('●', '<span style="font-size:10px;"> ● </span>');
                    if (row[21] !== null && row[22] !== null && row[23] !== null && row[24] !== null && row[25] !== null)
                    {
                        var trend = "";
                        var pos = "";
                        
                        var close = parseFloat(row[20]);
                        var midline = parseFloat(row[21]);
                        var SE = parseFloat(row[22]);
                        var slope = parseFloat(row[23]);
                        var close_previous = parseFloat(row[24]);
                        var midline_previous = parseFloat(row[25]);
                        
                        var height = 18;
                        if (slope == 1)
                            trend = "up-";
                        else if (slope == -1)
                            trend = "down-";
                        else
                        {
                            trend = "sideways ";
                            height = 14;
                        }
                        
                        if (close > (midline + SE) && close_previous < (midline_previous + SE))
                        {
                            pos = "breakout";
                            alt = "Break-out of the " + trend + "channel top";
                            height += 2;
                        }
                        else if (close < (midline - SE) && close_previous > (midline_previous - SE))
                        {
                            pos = "breakdown";
                            alt = "Break-down of the " + trend + "channel bottom";
                            height += 2;
                        }
                        /*else if (close/(midline + SE) >= 0.985 && close/(midline + SE) <= 1.015)
                        {
                            pos = "top";
                            alt = "Near the " + pos + " of the " + trend + " channel";
                        }
                        else if (close/(midline) >= 0.985 && close/(midline) <= 1.015)
                        {
                            pos = "middle";
                            alt = "Near the " + pos + " of the " + trend + " channel";
                        }
                        else if (close/(midline - SE) >= 0.985 && close/(midline - SE) <= 1.015)
                        {
                            pos = "bottom";
                            alt = "Near the " + pos + " of the " + trend + " channel";
                        }*/
                        else if (close > (midline + 0.8 * SE) && close < (midline + 1.2 * SE))
                        {
                            pos = "top";
                            alt = "Near the " + trend + "channel " + pos;
                        }
                        else if (close > (midline - 0.2 * SE) && close < (midline + 0.2 * SE))
                        {
                            pos = "middle";
                            alt = "Near the " + pos + " of the " + trend + "channel";
                        }
                        else if (close > (midline - 1.2 * SE) && close < (midline - 0.8 * SE))
                        {
                            pos = "bottom";
                            alt = "Near the " + trend + "channel " + pos;
                        }
                        else if (close > (midline + SE))
                        {
                            pos = "above";
                            alt = "Above the " + trend + "channel top";
                            height += 2;
                        }
                        else if (close < (midline - SE))
                        {
                            pos = "below";
                            alt = "Below the " + trend + "channel bottom";
                            height += 2;
                        }
                        else if (close > midline && close <= (midline + SE))
                        {
                            pos = "upper";
                            alt = "In the " + pos + " half of the " + trend + "channel";
                        }
                        else if (close < midline && close >= (midline - SE))
                        {
                            pos = "lower";
                            alt = "In the " + pos + " half of the " + trend + "channel";
                        }
                        
                        if (trend === "" || pos === "")
                            return data + info;
                        else
                            //return data + ' <a href="stockinfo?code=' + row[0] + '" target="_blank"><img class="not-selectable" src="assets/img/' + trend + '_' + pos + '.png" height="' + height + '" title="' + alt + '"></a>' + info;
                            //return data + ' <img class="not-selectable" src="assets/img/' + trend + '_' + pos + '.png" height="' + height + '" title="' + alt + '">' + info;
                            return data + ' <span class="mytooltip3" data-tooltip="' + alt.replace(/ /g, '&nbsp;').replace(/-/g, '&#8209;') + '"><img class="not-selectable" src="assets/img/' + trend.replace('-', '').replace(' ', '') + '_' + pos + '.png" height="' + height + '" ondragstart="return false;"></span>' + info;
                    }
                    else
                        return data + info;
                },
                "targets": 1
            },
            {
                "targets": 2,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    /*if(type === 'sort')
                    {
                        return parseFloat(data);
                    }
                    else*/
                    {
                        if (row[19] !== '' && row[19] !== null) // if row[19] == row[20] also makes row[19] + ',' + row[20]. If not, no sparkline is drawn as only 1 point
                            return '<div><div style="float:left">' + row[20] + ' ('+ (data > 0 ? '<font color="green">+' + data + '%': data < 0 ? '<font color="red">' + data + '%' : '<font color="grey">\u00B1' + data + '%') +'</font>)</div><div style="float:right"><a href="stockinfo?code=' + row[0] + '" target="_blank"><span data-sparkline="' + (row[19].endsWith(',' + row[20]) ? row[19] : row[19] + ',' + row[20]) + '"></span></a></div><div style="float: clear;"></div></div>';
                        else
                            return '<div><div style="float:left">' + row[20] + ' ('+ (data > 0 ? '<font color="green">+' + data + '%': data < 0 ? '<font color="red">' + data + '%' : '<font color="grey">\u00B1' + data + '%') +'</font>)</div><div style="float:right"></div><div style="float: clear;"></div></div>';
                    }
                },
            },
            {
                "targets": 3,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if(type === 'display' || type === 'sort')
                    {
                        var mcap = parseFloat(data);
                        if (mcap >= 100000) 
                            return '<span style="font-size:12px">' + Math.round(data) + '</span>';
                        else 
                            return data;
                    }
                },
            },
            {
                "targets": 4,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                    else
                        //return '<span title="' + data + ' of 5">' + data + '</span>';
                        return '<span class="mytooltip2" data-tooltip="Technical&nbsp;Polar:&#13;' + data + '&nbsp;of&nbsp;5">' + data + '</span>';
                },
            },
            {
                "targets": 5,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                    else
                        //return '<span title="' + data + ' of 5">' + data + '</span>';
                        return '<span class="mytooltip2" data-tooltip="Valuation&nbsp;Polar:&#13;' + data + '&nbsp;of&nbsp;5">' + data + '</span>';
                },
            },
            {
                "targets": 6,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                    else
                        //return '<span title="' + data + ' of 5">' + data + '</span>';
                        return '<span class="mytooltip2" data-tooltip="Fundamental&nbsp;Polar:&#13;' + data + '&nbsp;of&nbsp;5">' + data + '</span>';
                },
            },
            {
                "targets": 7,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if(type === 'display' || type === 'sort')
                    {
                        if (row[7] === null)
                            return '';
                            
                        var trendvalue = parseFloat(row[7]);
                        if (trendvalue >= 0 && trendvalue <= 45) 
                            return '<span style="font-size:10px">Up - Strong Up</span>';
                        else if (trendvalue > 45 && trendvalue <= 90) 
                            return '<span style="font-size:10px">Strong Up - Overbought</span>';
                        else if (trendvalue > 90 && trendvalue <= 135) 
                            return '<span style="font-size:10px">Overbought - Correction</span>';
                        else if (trendvalue > 135 && trendvalue <= 180) 
                            return '<span style="font-size:10px">Correction - Down</span>';
                        else if (trendvalue > 180 && trendvalue <= 225) 
                            return '<span style="font-size:10px">Down - Strong Down</span>';
                        else if (trendvalue > 225 && trendvalue <= 270) 
                            return '<span style="font-size:10px">Strong Down - Oversold</span>';
                        else if (trendvalue > 270 && trendvalue <= 315) 
                            return '<span style="font-size:10px">Oversold - Rebound</span>';
                        else if (trendvalue > 315 && trendvalue < 360) 
                            return '<span style="font-size:10px">Rebound - Up</span>';
                    }
                },
            },
            {
                "targets": 8,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    //return '<span title="At ' + data + ' STDEV away from median' + bandComment(parseFloat(data)) + '">' + data + '</span> ' + bandTrend(row[17], 'PE');
                    return '<span class="mytooltip4" data-tooltip="At&nbsp;' + data + '&nbsp;STDEV&nbsp;away&nbsp;from&nbsp;median' + bandComment(parseFloat(data), 'PE') + '">' + data + '</span> ' + bandTrend(row[17], 'PE');
                },
            },
            {
                "targets": 9,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    //return '<span title="At ' + data + ' STDEV away from median' + bandComment(parseFloat(data)) + '">' + data + '</span> ' + bandTrend(row[18], 'PB');
                    return '<span class="mytooltip4" data-tooltip="At&nbsp;' + data + '&nbsp;STDEV&nbsp;away&nbsp;from&nbsp;median' + bandComment(parseFloat(data), 'PB') + '">' + data + '</span> ' + bandTrend(row[18], 'PB');
                },
            },
            {
                "targets": 10,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                    else
                    {
                        //return '<span title="' + data + ' of 9">' + data + '</span>';
                        return '<span class="mytooltip2" data-tooltip="F&#8209;Score:&#13;' + data + '&nbsp;of&nbsp;9">' + data + '</span>';
                    }
                },
            },
            {
                "targets": 11,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                        
                    var color = '#ff7070';
                    var tooltip = 'Distress';
                    if (data >= 1.81 && data <= 2.99)
                    {
                        color = 'darkgrey';
                        tooltip = 'Grey';
                    }
                    else if (data > 2.99)
                    {
                        color = '#80bf0c';
                        tooltip = 'Safe';
                    }
                    //return '<span title="' + tooltip + '" style="color: '+ color +';"><i class="fas fa-circle"></i></span>';
                    return '<span class="mytooltip2" data-tooltip="Z&#8209;Score:&#13;' + tooltip + '" style="color: '+ color +';"><i class="fas fa-circle"></i></span>';
                },
            },
            {
                "targets": 12,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    if (data === null)
                        return '';
                        
                    var color = 'darkgrey';
                    var tooltip = 'Grey';
                    if (data > -2.22)
                    {
                        color = '#ff7070';
                        tooltip = 'Manipulator';
                    }
                    else if (data < -2.22)
                    {
                        color = '#80bf0c';
                        tooltip = 'Non&nbsp;Manipulator';
                    }
                    //return '<span title="' + tooltip + '" style="color: '+ color +';"><i class="fas fa-circle"></i></span>';
                    return '<span class="mytooltip3" data-tooltip="M&#8209;Score:&#13;' + tooltip + '" style="color: '+ color +';"><i class="fas fa-circle"></i></span>';
                },
            },
            {
                "targets": 13,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    return rsiDivergence(data, row[15], "Daily");
                },
            },
            {
                "targets": 14,
                "type": "num",
                "render": function ( data, type, row ,meta) {
                    return rsiDivergence(data, row[16], "Weekly");
                },
            },
            //{ "visible": false,  "targets": [ 13, 14, 15, 16, 17, 18, 19, 20, 21, 22 ] }
        ],
        "aoColumns": [
            null,
            null,
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] }
        ],
        "drawCallback": function( settings ) {
            $([document.documentElement, document.body]).animate({
                scrollTop: $("#Result").offset().top - 165
                }, 1000);
                        
            //var start = +new Date(),
            $tds = $('span[data-sparkline]'),
            fullLen = $tds.length,
            n = 0;
            
            doChunk($tds, fullLen, 0);
        }
    }); 

//  $('#screener_grid').on('change', function () {
//    table.draw();
//  });
/*    var header = ["Code", "Name", "Close", "Market Cap (mil)"];
    $('#screener_grid tbody').on( 'click', 'td', function () {
        var cell = this.innerText;//.split("\t");
        if (this.cellIndex === 0)
        {
            var found = false; // find responsive header
            for(var i = 0; i < header.length; i++)
            {
                if (cell.startsWith(header[i]))
                {
                    found = true;
                    break;
                }
                //alert(this.innerText.replace(/[\t]/g,','));
            }
            if (!found)
                //location.href = "stockinfo?code=" + cell;
                window.open('stockinfo?code=' + cell, '_blank');
        }
        
    } );*/
}

function bandComment(data, type)
{
    var comment = '';
    
    if (data === 0)
    {
    	comment = ".&#13;At fair value in terms of " + type;
    }
    else if (data < 0.5 && data > -0.5)
    {
    	comment = ".&#13;Near fair value in terms of " + type;
    }
    else if (data <= -0.5 && data > -1.2)
    {
    	comment = ".&#13;Mildly below fair value in terms of " + type;
    }
    else if (data <= -1.2 && data > -1.8)
    {
    	comment = ".&#13;Cheap in terms of " + type;
    }
    else if (data <= -1.8 && data > -2.8)
    {
    	comment = ".&#13;Very cheap in terms of " + type;
    }
    else if (data <= -2.8)
    {
    	comment = ".&#13;Extremely cheap in terms of " + type;
    }
    else if (data >= 0.5 && data < 1.2)
    {
    	comment = ".&#13;Mildly above fair value in terms of " + type;
    }
    else if (data >= 1.2 && data < 1.8)
    {
    	comment = ".&#13;Expensive in terms of " + type;
    }
    else if (data >= 1.8 && data < 2.8)
    {
    	comment = ".&#13;Very expensive in terms of " + type;
    }
    else if (data >= 2.8)
    {
    	comment = ".&#13;Extremely expensive in terms of " + type;
    }
    
    return comment.replace(/ /g, '&nbsp;').replace(/-/g, '&#8209;');
}

function rsiDivergence(rsi, divergence, timeframe)
{
    var color = '';
    var tooltip = '';
    var char = '';
    if (divergence == "-1")
    {
        color = '#ff7070';
        tooltip = 'Bearish Divergence ' + timeframe;
        char = '</span><span class="mytooltip2" data-tooltip="' + tooltip + '"/><span style="color: '+ color +';font-weight:600;font-size:16px;">↷';
    }
    else if (divergence == "1")
    {
        color = '#80bf0c';
        tooltip = 'Bullish Divergence ' + timeframe;
        char = '</span><span class="mytooltip2" data-tooltip="' + tooltip + '"/><span style="color: '+ color +';font-weight:600;;font-size:16px;">⤻';
    }
    else
        return rsi;
        
    //return '<span title="' + tooltip + '" style="color: '+ color +';font-weight:600;">' + rsi + ' ' + char + '</span>';
    return '<span class="mytooltip2" data-tooltip="' + tooltip + '"/><span style="color: '+ color +';font-weight:600;">' + rsi + '</span> ' + char + '</span>';
}

function bandTrend(value, type)
{
    var trend = '';
    if (value == 0)
        //trend = '<span title="'+ type +' Band is moving sideways" style="color: darkgrey;"><i class="fas fa-angle-left"></i><i class="fas fa-angle-right"></i></span>';
        trend = '<span class="mytooltip4" data-tooltip="'+ type +'&nbsp;Band&nbsp;is&nbsp;moving&nbsp;sideways" style="color: darkgrey;"><i class="fas fa-angle-left"></i><i class="fas fa-angle-right"></i></span>';
    else if (value == 1)
        //trend = '<span title="'+ type +' Band is mildly trending up" style="color: #80bf0c;"><i class="fas fa-angle-up"></i></span>';
        trend = '<span class="mytooltip4" data-tooltip="'+ type +'&nbsp;Band&nbsp;is&nbsp;mildly&nbsp;trending&nbsp;up" style="color: #80bf0c;"><i class="fas fa-angle-up"></i></span>';
    else if (value == 2)
        //trend = '<span title="'+ type +' Band is strongly trending up"style="color: #80bf0c;"><i class="fas fa-angle-double-up"></i></span>';
        trend = '<span class="mytooltip4" data-tooltip="'+ type +'&nbsp;Band&nbsp;is&nbsp;strongly&nbsp;trending&nbsp;up"style="color: #80bf0c;"><i class="fas fa-angle-double-up"></i></span>';
    else if (value == -1)
        //trend = '<span title="'+ type +' Band is mildly trending down" style="color: #ff7070;"><i class="fas fa-angle-down"></i></span>';
        trend = '<span class="mytooltip4" data-tooltip="'+ type +'&nbsp;Band&nbsp;is&nbsp;mildly&nbsp;trending&nbsp;down" style="color: #ff7070;"><i class="fas fa-angle-down"></i></span>';
    else if (value == -2)
        //trend = '<span title="'+ type +' Band is strongly trending down" style="color: #ff7070;"><i class="fas fa-angle-double-down"></i></span>';
        trend = '<span class="mytooltip4" data-tooltip="'+ type +'&nbsp;Band&nbsp;is&nbsp;strongly&nbsp;trending&nbsp;down" style="color: #ff7070;"><i class="fas fa-angle-double-down"></i></span>';
        
    return trend;
}


// Creating 153 sparkline charts is quite fast in modern browsers, but IE8 and mobile
// can take some seconds, so we split the input into chunks and apply them in timeouts
// in order avoid locking up the browser process and allow interaction.
function doChunk($tds, fullLen, n) {
    var time = +new Date(),
        i,
        len = $tds.length,
        $td,
        stringdata,
        arr,
        data,
        chart;

    for (i = 0; i < len; i += 1) {
        $td = $($tds[i]);
        stringdata = $td.data('sparkline');
        if (stringdata === null)
            continue;
        arr = stringdata.split(';');
        data = $.map(arr[0].split(','), parseFloat);
        if (data.length > 1 &&
            (data[data.length - 1] / data[data.length - 2] > 90 || data[data.length - 2] / data[data.length - 1] > 90)) // in case of LSE gbp gbx error
        {
            data.pop(); // remove last item
        }
        
        chart = {};

        if (arr[1]) {
            chart.type = arr[1];
        }
        $td.highcharts('SparkLine', {
            series: [{
                data: data,
                pointStart: 1
            }],
            tooltip: {
                headerFormat: '<span style="font-size: 10px">{point.x}:</span><br/>',
                pointFormat: '<b>{point.y}</b>'
            },
            chart: chart,
            yAxis: {
                min: Math.min.apply(this, data) * 0.98, // : Math.min(...data) * 0.98,
                max: Math.max.apply(this, data) * 1.02 // : Math.max(...data) * 1.02
            },
        });

        n++;

        // If the process takes too much time, run a timeout to allow interaction with the browser
        if (new Date() - time > 10000) {
            $tds.splice(0, i + 1);
            setTimeout(doChunk, 0);
            break;
        }

        // Print a feedback on the performance
        /*if (n === fullLen) {
            $('#result').html('Generated ' + fullLen + ' sparklines in ' + (new Date() - start) + ' ms');
        }*/
    }
}
    

/**
 * Create a constructor for sparklines that takes some sensible defaults and merges in the individual
 * chart options. This function is also available from the jQuery plugin as $(element).highcharts('SparkLine').
 */
Highcharts.SparkLine = function (a, b, c) {
    var hasRenderToArg = typeof a === 'string' || a.nodeName,
        options = arguments[hasRenderToArg ? 1 : 0],
        defaultOptions = {
            chart: {
                renderTo: (options.chart && options.chart.renderTo) || this,
                backgroundColor: null,
                borderWidth: 0,
                type: 'areaspline',
                margin: [0, 0, 0, 0],
                width: 60,
                height: 20,
                style: {
                    overflow: 'visible'
                },

                // small optimalization, saves 1-2 ms each sparkline
                skipClone: true
            },
            title: {
                text: ''
            },
            credits: {
                enabled: false
            },
            xAxis: {
                labels: {
                    enabled: false
                },
                title: {
                    text: null
                },
                startOnTick: false,
                endOnTick: false,
                tickPositions: []
            },
            yAxis: {
                endOnTick: false,
                startOnTick: false,
                labels: {
                    enabled: false
                },
                title: {
                    text: null
                },
                tickPositions: []
            },
            legend: {
                enabled: false
            },
            tooltip: {
                enabled: false,
                hideDelay: 0,
                outside: true,
                shared: true
            },
            plotOptions: {
                series: {
                    animation: false,
                    lineWidth: 1,
                    shadow: false,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    marker: {
                        enabled: false,
                        radius: 1,
                        states: {
                            hover: {
                                radius: 2
                            }
                        }
                    },
                    fillOpacity: 0.25
                },
                column: {
                    negativeColor: '#910000',
                    borderColor: 'silver'
                }
            }
        };

    options = Highcharts.merge(defaultOptions, options);

    return hasRenderToArg ?
        new Highcharts.Chart(a, options, c) :
        new Highcharts.Chart(options, b);
};

if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
      position = position || 0;
      return this.substr(position, searchString.length) === searchString;
  };
}
</script>

            <div class="container" style="padding: 0px 15px 0px;margin: 10px 0 0 0;">
                       

            </div>
        </div>
        

    </main>
    <p></p>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <!--<script src="assets/js/theme.js"></script>-->
    <script src="assets/js/ValuationBands.js"></script>-->
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
</body>

</html>
