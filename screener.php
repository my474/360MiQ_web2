<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="Stock Screener - 360MiQ.com" />
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i,600,600i&display=swap">
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
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css">
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
<script src="assets/js/price-display-policy.js?v=20260624.1"></script>

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

.screener-share-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

#searchresult .screener-share-actions:last-child {
    padding-bottom: 2px;
}

.screener-share-actions .btn {
    min-width: 104px;
    border-width: 1px;
    box-shadow: none;
    outline: 0;
}

.screener-share-actions .btn:focus,
.screener-share-actions .btn.focus,
.screener-share-actions .btn:active,
.screener-share-actions .btn:not(:disabled):not(.disabled):active {
    border-color: #007bff;
    border-width: 1px;
    box-shadow: none;
    outline: 2px solid rgba(0, 123, 255, 0.35);
    outline-offset: 2px;
}

.screener-share-actions .btn:not(:disabled):not(.disabled):active {
    background-color: #fff;
    color: #007bff;
}

[data-theme="dark"] .screener-share-actions .btn {
    border-color: var(--text-link-bright, #66c7ff) !important;
    color: var(--text-link-bright, #66c7ff) !important;
}

[data-theme="dark"] .screener-share-actions .btn:focus,
[data-theme="dark"] .screener-share-actions .btn.focus,
[data-theme="dark"] .screener-share-actions .btn:active,
[data-theme="dark"] .screener-share-actions .btn:not(:disabled):not(.disabled):active {
    border-color: var(--text-link-bright, #66c7ff) !important;
    outline-color: rgba(102, 199, 255, 0.35);
}

[data-theme="dark"] .screener-share-actions .btn:hover,
[data-theme="dark"] .screener-share-actions .btn:focus,
[data-theme="dark"] .screener-share-actions .btn.focus,
[data-theme="dark"] .screener-share-actions .btn:not(:disabled):not(.disabled):active {
    background-color: var(--text-link-bright, #66c7ff) !important;
    border-color: var(--text-link-bright, #66c7ff) !important;
    color: var(--bg, #1a1a2e) !important;
}

[data-theme="dark"] .screener-share-actions .btn:hover i,
[data-theme="dark"] .screener-share-actions .btn:focus i,
[data-theme="dark"] .screener-share-actions .btn:not(:disabled):not(.disabled):active i {
    color: var(--bg, #1a1a2e) !important;
}

.screener-share-notification {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #444;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    opacity: 0;
    transition: opacity 0.5s ease;
    pointer-events: none;
    font-family: sans-serif;
    z-index: 10000;
}

.screener-share-notification.show {
    opacity: 1;
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
<script>
//document.getElementById("screener_grid").style.visibility = "hidden";

jQuery(window).on('load', function(){
    jQuery("html,body").animate({scrollTop: 0}, 1000);
});
</script>


<!-- jQuery Library -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js"></script>
<script type="text/javascript" src="assets/js/datatables.input.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
window.__SCREENER_PAGE_CONFIG = {
    "marketfromURL": <?php echo json_encode(isset($_GET['market']) ? $_GET['market'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "sectorfromURL": <?php echo json_encode(isset($_GET['sector']) ? $_GET['sector'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "industryfromURL": <?php echo json_encode(isset($_GET['industry']) ? $_GET['industry'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/pages/screener-main.js?v=20260619.2"></script>
<script src="assets/js/pages/screener-share.js?v=20260709.1"></script>
<script src="assets/js/price-display-page-hooks.js?v=20260624.1"></script>

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
