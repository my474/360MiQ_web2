<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="<?php echo "Tool - 360MiQ.com"; ?>" />
    <meta property="og:url" content="https://360miq.com/tool" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
    <meta name="description" content="360MiQ.com Tool: Performance Race & Chart Composer." />
    <meta property="og:description" content="360MiQ.com Tool: Performance Race & Chart Composer." />

    <title>Tool - 360MiQ.com</title>
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
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <link rel="stylesheet" href="assets/css/inlinehelp.css">
    <link rel="stylesheet" href="assets/css/help-tip2.css">
    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
</head>

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
<script src="assets/js/Utils.js"></script>
<script src="assets/js/TA.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css"/>
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
figure {
    margin: 0;
}

#play-controls, #date-slider{
    max-width: 600px;
    margin: 1em auto;
}

/* Race chart in Tool */
#raceContainer {
    margin: 0 auto;
}
@media (min-width: 300px) {
    #raceContainer {
        height: 525px;
    }
}
@media (min-width: 768px) {
    #raceContainer {
        height: 700px;
    }
}
@media (min-width: 992px) {
    #raceContainer {
        height: 730px;
    }
}


#csv {
    display: none;
}

#play-pause-button, #stop-button{
    margin-left: 10px;
    width: 50px;
    height: 50px;
    cursor: pointer;
    border: 1px solid rgba(2, 117, 255, 1);
    border-radius: 25px;
    color: white;
    background-color: rgba(2, 117, 255, 1);
    transition: background-color 250ms;
}

#play-pause-button:hover, #stop-button:hover {
    background-color: rgba(2, 117, 255, 0.5);
}

#play-range {
    transform: translateY(2.5px);
    width: calc(100% - 0px);
    background: #f8f8f8;
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
</style>

<style>
    /*body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }*/
    .search-container {
        position: relative;
        width: 400px;
    }
    
    .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        max-height: 1000px;
        overflow-y: auto;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        box-sizing: border-box;
        z-index: 1000;
        list-style: none;
        margin: 0;
        padding: 0;
        display: none;
        text-align: left;
    }
    [data-theme="dark"] .suggestions {
      background: #1e1e32;
      border-color: #3a3a4e;
      color: #e8e8e8;
    }
    .suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
    }
    [data-theme="dark"] .suggestion-item {
        background-color: #1e1e32;
        color: #e8e8e8;
    }
    .suggestion-item:nth-child(even) {
        background-color: #ECF6FC;
    }
    [data-theme="dark"] .suggestion-item:nth-child(even) {
        background-color: #242438;
    }
    .suggestion-item div {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Mouseover highlight via CSS, only when no active highlights */
    .suggestions:not(:has(.keyboard-highlight, .highlight)) .suggestion-item:hover {
        background-color: #007bff;
        color: #ffffff;
        font-weight: bold;
        border: none;
    }
    [data-theme="dark"] .suggestions:not(:has(.keyboard-highlight, .highlight)) .suggestion-item:hover {
        background-color: #147dce;
        color: #ffffff;
    }
    /* Mouseover highlight via JavaScript */
    .suggestion-item.highlight {
        background-color: #007bff !important;
        color: #ffffff !important;
        font-weight: bold !important;
        border: none !important;
    }
    [data-theme="dark"] .suggestion-item.highlight,
    [data-theme="dark"] .suggestion-item.keyboard-highlight {
        background-color: #147dce !important;
        color: #ffffff !important;
    }
    /* Keyboard highlight */
    .suggestion-item.keyboard-highlight {
        background-color: #007bff !important;
        color: #ffffff !important;
        font-weight: bold !important;
        border: none !important;
    }
    #results {
        margin-top: 20px;
        font-size: 14px;
    }
</style>

<style>
#container {
    height: 400px;
    width: 100%;
}
.control-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 15px;
    background-color: #f8f9fa;
    margin: 0 15px;
}
.controls {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.control-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
}
.control-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.control-group label {
    margin: 0 0 5px 2px;
    font-weight: 550;
}
.control-group select {
    width: 200px;
}
.control-group .combo-box {
    width: 420px;
    min-width: 200px;
    position: relative;
}
.control-group .combo-box input {
    width: 100%;
    box-sizing: border-box;
}
.control-group .button-group {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
    width: 100%;
    min-width: 200px;
}
.combo-box .dropdown-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 12px;
    color: #6c757d;
}
#series-list {
    margin-top: 10px;
}
.series-item {
    margin: 5px 0;
    display: flex;
    align-items: center;
    padding-left: 10px;
    padding-right: 10px;
}
.remove-series {
    color: red;
    font-size: 16px;
    margin-left: 10px;
    cursor: pointer;
    background: none;
    border: none;
}
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn-success:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn-danger:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
}
.tab-content {
    margin-top: 20px;
}
.panel-heading {
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
}
.not-selectable {
    user-select: none;
}
.ui-autocomplete {
    max-height: 720px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1000;
    position: absolute;
    background: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
[data-theme="dark"] .ui-autocomplete {
  background: var(--bg-card);
  border-color: var(--border-color);
}
.ui-autocomplete .ui-menu-item {
    padding: 5px 10px;
    cursor: pointer;
}
.ui-autocomplete .ui-state-active {
    background: #007bff;
    color: #fff;
}

[data-theme="dark"] #tab-1 .form-control,
[data-theme="dark"] #tab-1 .js-input-from,
[data-theme="dark"] #tab-1 .js-input-to {
    background-color: #242438;
    border-color: #6f74a8;
    color: #e8e8e8;
}

[data-theme="dark"] #tab-1 .form-control::placeholder {
    color: #9a9aaa;
}

[data-theme="dark"] #tab-1 .form-control:focus,
[data-theme="dark"] #tab-1 .js-input-from:focus,
[data-theme="dark"] #tab-1 .js-input-to:focus {
    background-color: #242438;
    border-color: #8ec8ff;
    color: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(142, 200, 255, 0.2);
}

[data-theme="dark"] #raceInput,
[data-theme="dark"] #raceInput:hover,
[data-theme="dark"] #raceInput:focus,
[data-theme="dark"] #raceInput:active {
    background-color: #242438 !important;
    border-color: #8ec8ff !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

[data-theme="dark"] #raceInput:not(:focus) {
    border-color: #6f74a8 !important;
    box-shadow: none !important;
}

[data-theme="dark"] #raceInput:focus {
    box-shadow: 0 0 0 0.2rem rgba(142, 200, 255, 0.2) !important;
}

[data-theme="dark"] #raceInput:-webkit-autofill,
[data-theme="dark"] #raceInput:-webkit-autofill:hover,
[data-theme="dark"] #raceInput:-webkit-autofill:focus,
[data-theme="dark"] #raceInput:-webkit-autofill:active {
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: 0 0 0 1000px #242438 inset !important;
    caret-color: #ffffff;
}

[data-theme="dark"] #raceInput::-webkit-search-cancel-button {
    filter: invert(1) brightness(1.4);
}

[data-theme="dark"] #tab-1 .btn-outline-primary {
    border-color: var(--text-link-bright, #66c7ff) !important;
    color: var(--text-link-bright, #66c7ff) !important;
}

[data-theme="dark"] #tab-1 .btn-outline-primary:hover,
[data-theme="dark"] #tab-1 .btn-outline-primary:focus,
[data-theme="dark"] #tab-1 .btn-outline-primary.active,
[data-theme="dark"] #tab-1 .btn-outline-primary:not(:disabled):not(.disabled):active,
[data-theme="dark"] #tab-1 .show > .btn-outline-primary.dropdown-toggle {
    background-color: var(--text-link-bright, #66c7ff) !important;
    border-color: var(--text-link-bright, #66c7ff) !important;
    color: #ffffff !important;
}

[data-theme="dark"] #tab-1 .btn-outline-primary:hover i,
[data-theme="dark"] #tab-1 .btn-outline-primary:focus i,
[data-theme="dark"] #tab-1 .btn-outline-primary.active i {
    color: #ffffff !important;
}

[data-theme="dark"] #play-range {
    background: transparent;
}

[data-theme="dark"] #play-range::-webkit-slider-runnable-track {
    height: 6px;
    background: #4a4a63;
    border-radius: 6px;
}

[data-theme="dark"] #play-range::-webkit-slider-thumb {
    margin-top: -7px;
}

[data-theme="dark"] #play-range::-moz-range-track {
    height: 6px;
    background: #4a4a63;
    border-radius: 6px;
}

[data-theme="dark"] #play-range::-moz-range-thumb {
    background: #007bff;
    border-color: #007bff;
}

[data-theme="dark"] .irs--round .irs-line {
    background-color: #4a4a63;
}

[data-theme="dark"] .irs--round .irs-min,
[data-theme="dark"] .irs--round .irs-max,
[data-theme="dark"] .irs--round .irs-grid-text {
    color: #b8b8c8;
}

[data-theme="dark"] #tab-2 .control-container {
    background-color: #242438;
    border-color: #6f74a8;
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .control-group label {
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .form-control {
    background-color: #1e1e32;
    border-color: #6f74a8;
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .form-control::placeholder {
    color: #9a9aaa;
}

[data-theme="dark"] #tab-2 .form-control:focus {
    background-color: #1e1e32;
    border-color: #8ec8ff;
    color: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(142, 200, 255, 0.2);
}

[data-theme="dark"] #tab-2 select.form-control option,
[data-theme="dark"] #tab-2 select.form-control optgroup {
    background-color: #1e1e32;
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .combo-box .dropdown-arrow {
    color: #b8b8c8;
}

[data-theme="dark"] #tab-2 .ui-autocomplete {
    background-color: #1e1e32;
    border-color: #6f74a8;
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .ui-autocomplete .ui-menu-item {
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .ui-autocomplete .ui-menu-item:nth-child(even) {
    background-color: #242438;
}

[data-theme="dark"] #tab-2 .ui-autocomplete .ui-state-active {
    background-color: #147dce;
    color: #ffffff;
}

[data-theme="dark"] #series-list {
    color: #e8e8e8;
}

[data-theme="dark"] #tab-2 .highcharts-stocktools-wrapper,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-submenu-wrapper {
    --highcharts-neutral-color-3: #242438;
    --highcharts-neutral-color-10: #33335a;
    --highcharts-button-hover-color: #33335a;
    background: #242438 !important;
    background-color: #242438 !important;
    border-color: #6f74a8 !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li,
[data-theme="dark"] #tab-2 .highcharts-toggle-toolbar {
    background: #242438 !important;
    background-color: #242438 !important;
    border-color: #6f74a8 !important;
    color: #e8e8e8 !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-menu-item-btn,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-menu-item-btn:hover,
[data-theme="dark"] #tab-2 .highcharts-toggle-toolbar {
    background: transparent !important;
    background-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li:hover,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li.highcharts-active {
    background: #33335a !important;
    background-color: #33335a !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-menu-item-btn,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-submenu-item-arrow,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-arrow-up,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-arrow-down,
[data-theme="dark"] #tab-2 .highcharts-toggle-toolbar {
    filter: brightness(0) invert(1) !important;
}

/* Optional styling for optgroup labels */
select optgroup {
    color: #6c757d;
    font-style: normal;
    font-weight: bold;
}

.notification {
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
.notification.show {
  opacity: 1;
}
</style>
<?php $page = 'tool'; include "./header.php" ?>
<body>
    <main class="page" style="cursor: default;">
        <section class="clean-block clean-form" style="padding: 0px 0px 30px;">
            <h1 class="sr-only">Performance Race & Chart Composer Tool – Create Visual Stock Comparisons</h1>
            <div class="container" style="padding: 30px 0px 0px;">
                <div class="card clean-card text-center container" style="margin: 70px 0 0;padding-right:0;padding-left:0">
                    <div class="panel panel-default" style="margin:5px 0 0;">
                        <ul class="nav nav-tabs panel-heading not-selectable">
                            <li class="nav-item"><a class="nav-link active mynav-link" id="race-tab" data-bs-toggle="tab" data-bs-target="#race" role="tab" data-toggle="tab" href="#tab-1">Race</a></li>
                            <li class="nav-item"><a class="nav-link mynav-link" id="chartcomposer-tab" data-bs-toggle="tab" data-bs-target="#chartcomposer" role="tab" data-toggle="tab" href="#tab-2">Chart Composer&nbsp;<span class="new-badge">NEW</span><span class="sr-only new">(new feature)</span></a></li>
                        </ul>
                        
                        <!-- Tab Navigation -->
                        <!--ul class="nav nav-tabs panel-heading not-selectable">
                            <li class="nav-item">
                                <a class="nav-link active mynav-link" id="race-tab" data-bs-toggle="tab" data-bs-target="#race" role="tab" data-toggle="tab" href="#tab-1">Race</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mynav-link" id="chartcomposer-tab" data-bs-toggle="tab" data-bs-target="#chartcomposer" role="tab" data-toggle="tab" href="#tab-2">Chart Composer</a>
                            </li>
                        </ul-->
                        
             
        
                        <div class="tab-content panel-body">
                            <div class="tab-pane active" role="tabpanel" id="tab-1">
                                <h2 class="sr-only">Race Chart – Watch Stocks Race Against Each Other</h2>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div>
                                    <figure class="highcharts-figure">
                                        <div id="parent-container" style="margin: 30px 40px;">
                                            <div class="input-group">
                                                <input id="raceInput" size="12" class="form-control mr-sm-2" type="search" placeholder="Enter up to 10 Stock Codes separated by commas (,)" onchange="this.value = this.value.trim();" aria-label="Search" name="codes" spellcheck="false"  maxlength="100" required>
                                                <button id="submitBtn" class="btn btn-outline-primary" type="submit" onclick="validate()" style="margin-left:6px">&nbsp;<i class="fas fa-search"></i></button>
                                                <ul class="suggestions" id="suggestions"></ul>
                                            </div>
                                            <div id="play-controls" style="margin: 30px auto;">
                                                <button id="play-pause-button" class="fa fa-play" title="Play"></button>
                                                <button id="stop-button" class="fa-solid fa-stop" title="End" onclick="stop()"></button>
                                                <input id="play-range" type="range" style="margin-top:20px"/>
                                            </div>
                                            <div class="range-slider" id="date-slider">
                                                <input type="text" class="js-range-slider" value="" />
                                            </div>
                                            <div class="extra-controls">
                                                <input type="text" class="js-input-from" value="0" size="10" maxlength="10"/>&nbsp;-
                                                <input type="text" class="js-input-to" value="0" size="10" maxlength="10"/>
                                            </div>
                                        </div>
                                        <div style="margin: 30px auto;">
                                            <button id="m6" class="btn btn-outline-primary btn-sm" title="6m" onclick="periodBTN(this.title)">6m</button>
                                            <button id="ytd" class="btn btn-outline-primary btn-sm" title="YTD" onclick="periodBTN(this.title)">YTD</button>
                                            <button id="y1" class="btn btn-outline-primary btn-sm" title="1y" onclick="periodBTN(this.title)">1y</button>
                                            <button id="y2" class="btn btn-outline-primary btn-sm" title="2y" onclick="periodBTN(this.title)">2y</button>
                                            <button id="y3" class="btn btn-outline-primary btn-sm" title="3y" onclick="periodBTN(this.title)">3y</button>
                                            <button id="y5" class="btn btn-outline-primary btn-sm" title="5y" onclick="periodBTN(this.title)">5y</button>
                                            <button id="y8" class="btn btn-outline-primary btn-sm" title="8y" onclick="periodBTN(this.title)">8y</button>
                                            <button id="all" class="btn btn-outline-primary btn-sm" title="all" onclick="periodBTN(this.title)">All</button>
                                        </div>
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons" style="margin-bottom:30px;">
                                            <label class="btn btn-outline-primary btn-sm">
                                                <input type="radio" name="timeframe" id="radio_daily" onchange="timeframeBTN()">Daily
                                            </label>
                                            <label class="btn btn-outline-primary btn-sm active">
                                                <input type="radio" name="timeframe" id="radio_weekly" onchange="timeframeBTN()">Weekly
                                            </label>
                                            <label class="btn btn-outline-primary btn-sm">
                                                <input type="radio" name="timeframe" id="radio_monthly" onchange="timeframeBTN()">Monthly
                                            </label>
                                        </div>
                                        <div id="chartnoteRace" style="display:none;font-size:14px;">Bookmark this chart in your browser for future viewing</div>
                                        <div id="raceContainer"></div>
                                    </figure>
                                </div>
                            </div>
                            
                            <div class="tab-pane" role="tabpanel" id="tab-2">
                                <h2 class="sr-only">Chart Composer – Create Custom Charts with Any Stocks and Indicators</h2>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div class="control-container">
                                    <div class="controls">
                                        <div class="control-row">
                                            <div class="control-group">
                                                <label for="region-exchange">1. Region/Exchange</label>
                                                <select id="region-exchange" class="form-control">
                                                    <option value="all" selected>All</option>
                                                    <optgroup label="Region">
                                                        <option value="US">US</option>
                                                        <option value="China">China</option>
                                                        <option value="HK">Hong Kong</option>
                                                    </optgroup>
                                                    <optgroup label="Exchange">
                                                        <option value="NYSE">NYSE</option>
                                                        <option value="Nasdaq">Nasdaq</option>
                                                        <option value="LSE">London</option>
                                                        <option value="TSX">Toronto TSX</option>
                                                        <option value="ASX">Australia</option>
                                                        <option value="NSE">India NSE</option>
                                                        <option value="TYO">Tokyo</option>
                                                        <option value="HKEX">Hong Kong</option>
                                                        <option value="SHSE">Shanghai</option>
                                                        <option value="SZSE">Shenzhen</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="control-group">
                                                <label for="category">2. Category</label>
                                                <select id="category" class="form-control">
                                                    <option value="all" selected>All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-row">
                                            <div class="control-group">
                                                <label for="data-series">3. Data Series</label>
                                                <div class="combo-box">
                                                    <input id="data-series" class="form-control" type="text" placeholder="Type or select a series">
                                                    <span class="dropdown-arrow">▼</span>
                                                </div>
                                                <div class="button-group d-flex justify-content-center">
                                                    <button id="add-series" class="btn btn-success" disabled>Add Series</button>
                                                    <button id="remove-all" class="btn btn-danger" disabled>Remove All</button>
                                                    <button id="reset-controls" class="btn btn-warning">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="series-list"></div>
                                <div id="chartnoteComposer" style="display:none;font-size:14px;">Bookmark this chart in your browser for future viewing</div>
                                <div id="notification" class="notification"></div>
                                <div id="container" class="not-selectable" style="height: 725px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
<script src="https://code.highcharts.com/stock/11.4.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/11.4.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/11.4.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/11.4.0/modules/pattern-fill.js"></script>
<script src="https://code.highcharts.com/11.4.0/highcharts-more.js"></script>
<script src="https://code.highcharts.com/stock/11.4.0/modules/accessibility.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js"></script>
<script>
var codefromURL = "<?php echo isset($_GET["code"]) ? $_GET["code"] : ''; ?>";
var fromfromURL = "<?php echo isset($_GET["from"]) ? $_GET["from"] : ''; ?>";
var tofromURL = "<?php echo isset($_GET["to"]) ? $_GET["to"] : ''; ?>";
var timeframefromURL = "<?php echo isset($_GET["tf"]) ? $_GET["tf"] : ''; ?>";
var composerCodesfromURL = "<?php echo isset($_GET["codes"]) ? $_GET["codes"] : ''; ?>";
var composerFromfromURL = "<?php echo isset($_GET["cfrom"]) ? $_GET["cfrom"] : ''; ?>";
var composerTofromURL = "<?php echo isset($_GET["cto"]) ? $_GET["cto"] : ''; ?>";
var startupURL = new URL(window.location.href);
var startupTab = startupURL.searchParams.get('tab') || (startupURL.hash === '#tab-2' ? '2' : '1');
var composerRuntimePromise = null;
var composerControlsInitialized = false;
var newRelativePathQueryTab1 = '';
var newRelativePathQueryTab2 = '';
var exchangesOnMarketPage = ['NYSE', 'NASDAQ', 'TSX', 'LSE', 'ASX', 'TYO', 'NSE', 'HKEX', 'SHSE', 'SZSE'];
//var dict_daily_return = new Object();
var patchedDailyDict = new Object();
var dict_XXXly_return = new Object();
var nameDict = new Object();
var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
var chart = null;
const formatRevenue = [];
var min_current = 0;
var max_current = 0;
var endDate = new Date().toISOString().substring(0, 10);
var myDate = new Date();
myDate = new Date(myDate.setYear(myDate.getFullYear() - 8)).getTime();
var startDate = new Date(myDate).toISOString().substring(0, 10);
var ele = document.getElementsByName('timeframe');
var sliderStep = 7;
var codeString = '';
var loadfromDB = true;
var raceRequestSerial = 0;
var activeRaceRequest = null;
            
var $range = $(".js-range-slider"),
    $inputFrom = $(".js-input-from"),
    $inputTo = $(".js-input-to"),
    instance,
    min = new Date(startDate).getTime(),
    max = new Date(endDate).getTime(),
    from = new Date(startDate).getTime(),
    to = new Date(endDate).getTime();

$range.ionRangeSlider({
	skin: "round",
    type: "double",
    step:(24*60*60*1000) * 7,
    drag_interval: true,
    min: new Date(startDate).getTime(),
    max: new Date(endDate).getTime(),
    from: new Date(startDate).getTime(),
    to: new Date(endDate).getTime(),
    onStart: updateInputs,
    onFinish: updateInputs,
    onChange: pauseChart,
    prettify: function (tick) {
            return new Date(tick).toISOString().substring(0, 10);
    }
});
instance = $range.data("ionRangeSlider");

function pauseChart()
{
    if (chart !== null)
        pause(btn);
}
function timeframeBTN()
{
    stockcodeInputRED();
        
    instance.update({
        step: (24*60*60*1000) * sliderStep,
    });
    
    updateInputs(instance.result, true);
}
function stockcodeInputRED()
{
    var raceInput = document.getElementById('raceInput');
    if (raceInput.value == '')
        raceInput.style.borderColor = "red";
    else
        raceInput.style.borderColor = "";
}
function periodBTN(e)
{
    stockcodeInputRED();
        
    to = max;
    tmpfrom = new Date(max)
    if (e == '6m')
        from = add_Days(new Date(tmpfrom.setMonth(tmpfrom.getMonth() - 6)), 1).getTime();
    else if (e == 'YTD')
        from = new Date(Date.UTC(tmpfrom.getFullYear(), 0, 1)).getTime();
    else if (e == '1y')
        from = add_Days(new Date(tmpfrom.setYear(tmpfrom.getFullYear() - 1)), 1).getTime();
    else if (e == '2y')
        from = add_Days(new Date(tmpfrom.setYear(tmpfrom.getFullYear() - 2)), 1).getTime();
    else if (e == '3y')
        from = add_Days(new Date(tmpfrom.setYear(tmpfrom.getFullYear() - 3)), 1).getTime();
    else if (e == '5y')
        from = add_Days(new Date(tmpfrom.setYear(tmpfrom.getFullYear() - 5)), 1).getTime();
    else if (e == '8y')
        from = add_Days(new Date(tmpfrom.setYear(tmpfrom.getFullYear() - 8)), 1).getTime();
    else if (e == 'all')
        from = min;
        
    if (from < min)
        from = min;
    
    instance.update({
        from: from,
        to: to,
        step: (24*60*60*1000) * sliderStep,
    });
    
    $inputFrom.prop("value", dateFormatStr(from));
    $inputTo.prop("value", dateFormatStr(to));
    
    updateInputs(instance.result);
}
function add_Days(date, days) {
    const newDate = new Date(date);
    newDate.setDate(date.getDate() + days);
    return newDate;
}
function dateFormatStr(tick)
{
    return new Date(tick).toISOString().substring(0, 10);
}
function updateInputs (data, timeframeChanged = false) {
    if (chart !== null)
    {
        pause(btn);
    }
        
	from = Math.floor(data.from / 1000 / 24/ 3600) * 1000 * 24 * 3600;
    to = Math.floor(data.to / 1000 / 24/ 3600) * 1000 * 24 * 3600;
    
    if (min_current == from && max_current == to && !timeframeChanged)
    {
        if (chart !== null && (Object.keys(dict_XXXly_return)).length > 0)
        {
            play(btn);
        }
        
        return;
    }
        
    $inputFrom.prop("value", dateFormatStr(from));
    $inputTo.prop("value", dateFormatStr(to));
    
    min_current = from;
    max_current = to;
    
    
    dict_XXXly_return = new Object();
    var tmp_return = new Object();
    
    var startIdx = 0;
    for (var j = 0; j < (Object.keys(patchedDailyDict)).length; j++)
    {
        var tmpkey = (Object.keys(patchedDailyDict))[j];
        
        if (ele[1].checked)
        {
            tmp_return[tmpkey] = dataCompression_weekly(patchedDailyDict[tmpkey]);
            sliderStep = 7;
        }
        else if (ele[2].checked)
        {
            tmp_return[tmpkey] = dataCompression_monthly(patchedDailyDict[tmpkey]);
            sliderStep = 28;
        }
        else
        {
            tmp_return[tmpkey] = patchedDailyDict[tmpkey];
            sliderStep = 1;
        }
        
        // create XXXly return dict
        for (i = 0; i < tmp_return[tmpkey].length; i++)
        {
            //var tradedate_previous = parseInt(tmp_return[tmpkey][i - 1][0]);
            var tradedate = parseInt(tmp_return[tmpkey][i][0]);
            var tradedate_next = '';
            
            if (i < tmp_return[tmpkey].length - 1)
                tradedate_next = parseInt(tmp_return[tmpkey][i + 1][0]);
            
            if (tradedate > to)
                break;
                
            if (tradedate < from && (tradedate_next < from || tradedate_next == ''))
                continue;
            else if (tradedate < from && tradedate_next >= from)
            {
                startIdx = i;
            }

            if (dict_XXXly_return[tmpkey])
                dict_XXXly_return[tmpkey].push([parseInt(tmp_return[tmpkey][i][0]), rounding((tmp_return[tmpkey][i][1] / tmp_return[tmpkey][startIdx][1] - 1) * 100, 100) ]);
            else
                dict_XXXly_return[tmpkey] = [[parseInt(tmp_return[tmpkey][i][0]), rounding((tmp_return[tmpkey][i][1] / tmp_return[tmpkey][startIdx][1] - 1) * 100, 100) ]];

        }
    }
    
    chart = raceChart(nameDict);
    if ((Object.keys(dict_XXXly_return)).length > 0)
    {
        raceIdx = 0;
        input.value = 0;
        input.min = 0; //dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]][0][0];
        input.max = dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length;
        
        instance.update({
            step: (24*60*60*1000) * sliderStep,
        });

        var fromISO = new Date(from).toISOString().substring(0, 10);
        var toISO = new Date(to).toISOString().substring(0, 10);
        if (to == max)
            toISO = '';
        //var newRelativePathQuery = window.location.pathname + '?code=' + codeString + '&tf=' + (ele[0].checked ? 'd' : (ele[2].checked ? 'm' : 'w')) + '&from=' + fromISO + '&to=' + toISO;
        //history.pushState(null, '', newRelativePathQuery);
        newRelativePathQueryTab1 = window.location.pathname + '?code=' + codeString + '&tf=' + (ele[0].checked ? 'd' : (ele[2].checked ? 'm' : 'w')) + '&from=' + fromISO + '&to=' + toISO;
        history.pushState(null, '', newRelativePathQueryTab1);
        
        document.getElementById("chartnoteRace").style.display = "";
        
        play(btn);
    }
}

$inputFrom.on("input", function () {
    var inval = $(this).prop("value");
    var regEx = /^\d{4}-\d{2}-\d{2}$/;
    if(!inval.match(regEx))
        return;  // Invalid format
  
    var val = new Date(inval).getTime();
    
    // validate
    if (val < min) {
        val = min;
    } else if (val > to) {
        val = to;
    }
    
    instance.update({
        from: val,
        step: (24*60*60*1000) * sliderStep,
    });
    
    from = val;
    
    updateInputs(instance.result);
});

$inputTo.on("input", function () {
    var inval = $(this).prop("value");
    var regEx = /^\d{4}-\d{2}-\d{2}$/;
    if(!inval.match(regEx))
        return;  // Invalid format
    
    var val = new Date(inval).getTime();
    
    // validate
    if (val < from) {
        val = from;
    } else if (val > max) {
        val = max;
    }
    
    instance.update({
        to: val,
        step: (24*60*60*1000) * sliderStep,
    });
    
    to = val;
    
    updateInputs(instance.result);
});

function validate(){
    var value = document.getElementById('raceInput').value;
    if (value != '')
    {
        var initialTab =new URL(window.location).searchParams.get('tab');
        var url = document.location.toString();
        if ((!url.match('#') && !initialTab) || (url.match('#') && url.split('#')[1] == 'tab-1') || (initialTab && initialTab == 1)) // if #tab-1
        {
            race(value);
        } 
    }
    else
    {
        stockcodeInputRED();
    }
}

// Get the input field
var race_input = document.getElementById("raceInput");

// Execute a function when the user presses a key on the keyboard
race_input.addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("submitBtn").click();
  }
});

race_input.addEventListener("input", function(event) {
  // If the user presses the "Enter" key on the keyboard
  loadfromDB = true;
});

function race(data)
{
    if (data === '')
        return;

    var requestId = ++raceRequestSerial;
    if (activeRaceRequest && activeRaceRequest.readyState !== 4)
        activeRaceRequest.abort();
        
    //var isIEX = 0;
    //if (tmpname == "NYSE" || tmpname == "NASDAQ")
    //    isIEX = 1;
    activeRaceRequest = $.ajax({
        url : "db_top10_get.php?data=" + encodeURIComponent(data.replace(/\s/g, ',').replace(/,+/g, ',').replace(/,+$/, "")) + "&isIEX=1",
        /*method: "post",
        url : "db_top10_get.php",
    
        dataType : "html",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8", // this is the default value, so it's optional
        data : {"data": encodeURIComponent(peersCode), "isIEX": isIEX},*/
        beforeSend: function(){
            isLoading = true;
    	},
    	complete: function(){
            if (requestId === raceRequestSerial)
                isLoading = false;
    	},
    /*	error: function (error) {
    	    $('#loader-icon').hide();
    	    $('#loader-tip').hide();
            document.getElementById("failedtable").style.visibility = "visible";
            document.getElementById("failedcell").textContent = "Failed to load. Please refresh the page.";
        },*/
        success : function(result){
            if (requestId !== raceRequestSerial)
                return;
            if (result == '')
            {
                chart = raceChart(new Object());
                return;
            }
            
            loadfromDB = false;
            
            var dict = new Object();
            //dict_daily_return = new Object();
            dict_XXXly_return = new Object();
            raceIdx = 0;
            input.value = 0;
            
            if (result != "")
                isLoadedSuccessfully = true;
                
            var mindates = [];

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
                         
                        //if (dict_daily_return[row[0]])
                        //    dict_daily_return[row[0]].push([new Date(row[1]).getTime(), rounding((row[2] / dict[row[0]][0][1] - 1) * 100, 100) ]);
                        //else
                        //    dict_daily_return[row[0]] = [[new Date(row[1]).getTime(), rounding((row[2] / dict[row[0]][0][1] - 1) * 100, 100) ]];
                            
                    } else {
                        dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                        mindates.push(new Date(row[1]).getTime());
                    }
        	    }
            }
            
            patchedDailyDict = new Object();
            var tmpXXXlyDict = new Object();
            var tmpXXXlyDict2 = new Object();

            if ((Object.keys(dict)).length == 1)
            {
                var tmpkey = (Object.keys(dict))[0];
                
                patchedDailyDict[tmpkey] = dict[tmpkey];
 
                if (ele[1].checked)
                {
                    tmpXXXlyDict2[tmpkey] = dataCompression_weekly(dict[tmpkey]);
                    sliderStep = 7;
                }
                else if (ele[2].checked)
                {
                    tmpXXXlyDict2[tmpkey] = dataCompression_monthly(dict[tmpkey]);
                    sliderStep = 28;
                }
                else
                {
                    tmpXXXlyDict2[tmpkey] = dict[tmpkey];
                    sliderStep = 1;
                }
                
                // create XXXly return dict
                /*for (i = 1; i < tmpXXXlyDict[tmpkey].length; i++)
                {
                    if (dict_XXXly_return[tmpkey])
                        dict_XXXly_return[tmpkey].push([parseInt(tmpXXXlyDict[tmpkey][i][0]), rounding((tmpXXXlyDict[tmpkey][i][1] / tmpXXXlyDict[tmpkey][0][1] - 1) * 100, 100) ]);
                    else
                        dict_XXXly_return[tmpkey] = [[parseInt(tmpXXXlyDict[tmpkey][i][0]), rounding((tmpXXXlyDict[tmpkey][i][1] / tmpXXXlyDict[tmpkey][0][1] - 1) * 100, 100) ]];
                }*/
                
                
                var startIdx = 0;
                from = fromfromURL == '' ? parseInt(tmpXXXlyDict2[tmpkey][0][0]) : new Date(fromfromURL).getTime();
                to = tofromURL == '' ? parseInt(tmpXXXlyDict2[tmpkey][tmpXXXlyDict2[tmpkey].length - 1][0]) : new Date(tofromURL).getTime();
                if (to < from)
                    to = parseInt(tmpXXXlyDict2[tmpkey][tmpXXXlyDict2[tmpkey].length - 1][0]);
                    
                // create XXXly return dict
                for (i = 0; i < tmpXXXlyDict2[tmpkey].length; i++)
                {
                    //var tradedate_previous = parseInt(tmpXXXlyDict2[tmpkey][i - 1][0]);
                    var tradedate = parseInt(tmpXXXlyDict2[tmpkey][i][0]);
                    var tradedate_next = '';
            
                    if (i < tmpXXXlyDict2[tmpkey].length - 1)
                        tradedate_next = parseInt(tmpXXXlyDict2[tmpkey][i + 1][0]);
                    
                    if (tradedate > to)
                        break;
                        
                    if (tradedate < from && (tradedate_next < from || tradedate_next == ''))
                        continue;
                    else if (tradedate < from && tradedate_next >= from)
                    {
                        startIdx = i;
                    }
        
                    if (dict_XXXly_return[tmpkey])
                        dict_XXXly_return[tmpkey].push([parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][startIdx][1] - 1) * 100, 100) ]);
                    else
                        dict_XXXly_return[tmpkey] = [[parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][startIdx][1] - 1) * 100, 100) ]];
        
                }
            }
            else
            {
                for (var j = 0; j < (Object.keys(dict)).length; j++)
                {
                    var tmpkey = (Object.keys(dict))[j];
                    //tmpXXXlyDict[tmpkey] = dataCompression_XXXly(dict[tmpkey]);
                    tmpXXXlyDict[tmpkey] = dict[tmpkey];
                }
                
                // fill all dates in all series into a dict
                var tmpDateDict = new Object();
                for (var j = 0; j < (Object.keys(tmpXXXlyDict)).length; j++)
                {
                    var tmpkey = (Object.keys(tmpXXXlyDict))[j];
                    for (var i = 0; i < tmpXXXlyDict[tmpkey].length; i++)
                        tmpDateDict[tmpXXXlyDict[tmpkey][i][0]] = 0;
                }
                
                // sort all date dict
                var sorted = [];
                for(var key in tmpDateDict) {
                    sorted[sorted.length] = parseInt(key);
                }
                sorted.sort();
                
                for (var j = 0; j < (Object.keys(tmpXXXlyDict)).length; j++)
                {
                    var tmpXXXlyDateDict = new Object();
                    var tmpkey = (Object.keys(tmpXXXlyDict))[j];
                    
                    // convert XXXly price data into data-close dict
                    for (var i = 0; i < tmpXXXlyDict[tmpkey].length; i++)
                    {
                        tmpXXXlyDateDict[tmpXXXlyDict[tmpkey][i][0]] = tmpXXXlyDict[tmpkey][i][1];
                    }
                    
                    // patch date holes with date and null
                    for (var k = 0; k < sorted.length; k++)
                    {
                        if (tmpXXXlyDateDict[sorted[k]])
                        {
                            if (patchedDailyDict[tmpkey])
                                patchedDailyDict[tmpkey].push([sorted[k], tmpXXXlyDateDict[sorted[k]] ]);
                            else
                                patchedDailyDict[tmpkey] = [[sorted[k], tmpXXXlyDateDict[sorted[k]] ]];
                        }
                        else
                        {
                            if (patchedDailyDict[tmpkey])
                                patchedDailyDict[tmpkey].push([sorted[k], null ]);
                            else
                                patchedDailyDict[tmpkey] = [[sorted[k], null ]];
                        }
                    }
                }
                
                for (var j = 0; j < (Object.keys(patchedDailyDict)).length; j++)
                {
                    var tmpkey = (Object.keys(patchedDailyDict))[j];
                    
                    // patch date holes with previous close
                    for (var i = 1; i < patchedDailyDict[tmpkey].length; i++)
                    {
                        if (patchedDailyDict[tmpkey][i][1] === null)
                            patchedDailyDict[tmpkey][i][1] = patchedDailyDict[tmpkey][i - 1][1];
                    }
                    
                    // patch date holes with next close
                    for (var i = patchedDailyDict[tmpkey].length - 2; i >= 0; i--)
                    {
                        if (patchedDailyDict[tmpkey][i][1] === null)
                            patchedDailyDict[tmpkey][i][1] = patchedDailyDict[tmpkey][i + 1][1];
                    }
                    
                    tmpXXXlyDict2 = new Object();
                    
                    if (ele[1].checked)
                    {
                        tmpXXXlyDict2[tmpkey] = dataCompression_weekly(patchedDailyDict[tmpkey]);
                        sliderStep = 7;
                    }
                    else if (ele[2].checked)
                    {
                        tmpXXXlyDict2[tmpkey] = dataCompression_monthly(patchedDailyDict[tmpkey]);
                        sliderStep = 28;
                    }
                    else
                    {
                        tmpXXXlyDict2[tmpkey] = patchedDailyDict[tmpkey];
                        sliderStep = 1;
                    }
                    
                    // create XXXly return dict
                    /*for (i = 1; i < tmpXXXlyDict2[tmpkey].length; i++)
                    {
                        if (dict_XXXly_return[tmpkey])
                            dict_XXXly_return[tmpkey].push([parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][0][1] - 1) * 100, 100) ]);
                        else
                            dict_XXXly_return[tmpkey] = [[parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][0][1] - 1) * 100, 100) ]];
                    }*/
                    
                    
                    var startIdx = 0;
                    from = fromfromURL == '' ? parseInt(tmpXXXlyDict2[tmpkey][0][0]) : new Date(fromfromURL).getTime();
                    to = tofromURL == '' ? parseInt(tmpXXXlyDict2[tmpkey][tmpXXXlyDict2[tmpkey].length - 1][0]) : new Date(tofromURL).getTime();
                    if (to < from)
                        to = parseInt(tmpXXXlyDict2[tmpkey][tmpXXXlyDict2[tmpkey].length - 1][0]);
                    
                    // create XXXly return dict
                    for (i = 0; i < tmpXXXlyDict2[tmpkey].length; i++)
                    {
                        //var tradedate_previous = parseInt(tmpXXXlyDict2[tmpkey][i - 1][0]);
                        var tradedate = parseInt(tmpXXXlyDict2[tmpkey][i][0]);
                        var tradedate_next = '';
            
                        if (i < tmpXXXlyDict2[tmpkey].length - 1)
                            tradedate_next = parseInt(tmpXXXlyDict2[tmpkey][i + 1][0]);
                        
                        if (tradedate > to)
                            break;
                            
                        if (tradedate < from && (tradedate_next < from || tradedate_next == ''))
                            continue;
                        else if (tradedate < from && tradedate_next >= from)
                        {
                            startIdx = i;
                        }
            
                        if (dict_XXXly_return[tmpkey])
                            dict_XXXly_return[tmpkey].push([parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][startIdx][1] - 1) * 100, 100) ]);
                        else
                            dict_XXXly_return[tmpkey] = [[parseInt(tmpXXXlyDict2[tmpkey][i][0]), rounding((tmpXXXlyDict2[tmpkey][i][1] / tmpXXXlyDict2[tmpkey][startIdx][1] - 1) * 100, 100) ]];
            
                    }
                    
                    
                    
                }
            }

            codeString = '';
            for (var i = 0; i < (Object.keys(dict_XXXly_return)).length; i++)
            {
                codeString += (Object.keys(dict_XXXly_return))[i] + ',';
            }
            
            if (codeString.endsWith(','))
                codeString = codeString.substring(0, codeString.length - 1).replace(/ /g, '');
            
            /*maxlengthSeries = 0;
            for (var i = 0; i < (Object.keys(dict_XXXly_return)).length; i++)
            {
                if (dict_XXXly_return[(Object.keys(dict_XXXly_return))[i]].length == maxDictlength)
                {
                    maxlengthSeries = i;
                    break;
                }
            }*/
            
            var mindate = Math.min(...mindates);

            //const stocks = Object.keys(dict); // sort by stockcode
            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
            var rangeselector = 4;
            var creditYoffset = 26;
            if (browserwidth > 576)
            {
                rangeselector = 6;
                creditYoffset = 0;
            }
            //stockCompare('peerPerformancecontainer', dict, peersCodeArray, stockcode + " & Peers Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 2, true, true, peersNameArray);
            
            if (chart !== null)
                pause(btn);
                
            nameDict = new Object();
            var namesRequest = null;
            if (data != '')
            {
                if (codeString != '')
                {
                    namesRequest = $.ajax({
                        url : "db_top10_name_get.php?data=" + codeString,
                        success : function(names){
                            if (names != '')
                            {
                                var jsonObj = names.split(/\r?\n|\r/);
                                for (var i = 0; i < jsonObj.length; i++) {
                                    if ((jsonObj[i].match(/;/g) || []).length == 1) // no data_name field
                                        jsonObj[i] = ";" + jsonObj[i];
                                        
                                    var name_row = jsonObj[i].split(';')
                                    
                                    if (name_row.length == 4)
                                    {
                                        if (nameDict[name_row[0]]) {
                                            if (name_row[1] == '')
                                                nameDict[name_row[0]].push(name_row[2] + ' ● ' + name_row[3]);
                                            else
                                                nameDict[name_row[0]].push(name_row[1] + ' ● ' + name_row[2] + ' ● ' + name_row[3]);
                                        } else {
                                            if (name_row[1] == '')
                                                nameDict[name_row[0]] = [name_row[2] + ' ● ' + name_row[3]];
                                            else
                                                nameDict[name_row[0]] = [name_row[1] + ' ● ' + name_row[2] + ' ● ' + name_row[3]];
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            var finishRaceChart = function() {
                if (requestId !== raceRequestSerial)
                    return;

                chart = raceChart(nameDict);
                if ((Object.keys(tmpXXXlyDict2)).length > 0)
                {
                var stock =(Object.keys(tmpXXXlyDict2))[0];
                var sd = tmpXXXlyDict2[stock][0][0];
                var ed = tmpXXXlyDict2[stock][tmpXXXlyDict2[stock].length - 1][0];
                
                min = sd;
                max = ed;
                from = fromfromURL == '' ? sd : new Date(fromfromURL).getTime();
                to = tofromURL == '' ? ed : new Date(tofromURL).getTime();
                if (to < from)
                    to = parseInt(tmpXXXlyDict2[tmpkey][tmpXXXlyDict2[tmpkey].length - 1][0]);
                    
                min_current = from;
                max_current = to;
                
                instance.update({
                    from: from,
                    to: to,
                    min: sd,
                    max: ed,
                    step: (24*60*60*1000) * sliderStep,
                });
                
                $inputFrom.prop("value", dateFormatStr(from));
                $inputTo.prop("value", dateFormatStr(to));

                var fromISO = new Date(from).toISOString().substring(0, 10);
                var toISO = new Date(to).toISOString().substring(0, 10);
                if (to == max)
                    toISO = '';
                newRelativePathQueryTab1 = window.location.pathname + '?code=' + codeString + '&tf=' + (ele[0].checked ? 'd' : (ele[2].checked ? 'm' : 'w')) + '&from=' + fromISO + '&to=' + toISO;
                history.pushState(null, '', newRelativePathQueryTab1);
                document.getElementById("chartnoteRace").style.display = "";
                
                    play(btn);
                }
            };

            if (namesRequest)
                namesRequest.always(finishRaceChart);
            else
                finishRaceChart();
        }
    });
}

race('');

function raceChart(nameDict)
{
    var tmpchart = Highcharts.chart('raceContainer', {
        stockTools: {
            gui: {
                enabled: false,
                buttons: [
                    //'indicators',
                    'separator',
                    'simpleShapes',
                    'lines',
                    'crookedLines',
                    'measure',
                    'advanced',
                    'toggleAnnotations',
                    'separator',
                    'verticalLabels',
                    'separator',
                    'zoomChange',
                    //'fullScreen',
                    //'typeChange',
                    //'separator',
                    // Disabling 'flags' and 'currentPriceIndicator'
                ]
            }
        },
        chart: {
            events: {
                // Some annotation labels need to be rotated to make room
                render: function() {
                    this.legend.render();
                },
            },
            type: 'area',
            //marginTop: 46,
            animation: {
                duration: 0,
                easing: t => t
            },
            style: {
                fontFamily: 'Montserrat'
            }
        },
        
        credits: {
            enabled: true,
            text: '360MiQ.com',
            href: '',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
                cursor: 'arrow',
                color: 'lightgrey'
            },
            position: {
                align: 'center',
                verticalAlign: 'middle',
                //x: 0,
                //y: -document.getElementById('raceContainer').clientHeight / 2
            }
        },
            
        title: {
            text: 'Performance Race'
        },
        subtitle: {
            text: getSubtitle(),
            //floating: true,
            align: 'left',
            verticalAlign: 'top',
            x: 40,//-100,
            y: 110
        },
        /*data: {
            csv: document.getElementById('csv').innerHTML,
            itemDelimiter: '\t',
            complete: function (options) {
                for (let i = 0; i < options.series.length; i++) {
                    formatRevenue[i] = arrToAssociative(options.series[i].data);
                    options.series[i].data = null;
                }
            }
        },*/
        xAxis: {
            crosshair: true,
            allowDecimals: false,
            //min: startYear,
            labels: {
                formatter: function() {
                  return Highcharts.dateFormat("%b '%y", this.value);
                }
            }
        },
        yAxis: [{
            opposite: true,
            reversedStacks: false,
            title: {
                text: 'Performance %'
            },
            plotLines: [{
                color: '#c9c9c9', // Color value
                //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
                value: 0, // Value of where the line will appear
                width: 4, // Width of the line    
                zIndex: 3
            }]
        }],
        legend: {
                enabled: true,
                //verticalAlign: 'top',
                borderWidth: 0,
                //y: -35,
                layout: 'horizontal',
                labelFormatter: function() {
                    if (this.processedYData === undefined)
                        return;
    
                    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    var performance = this.processedYData[this.processedYData.length - 1];
                    var sign = '';

                    if(performance > 0)
                          sign = "+";
                        else if (performance === 0)
                            sign = "\u00B1";

                        if (performance < 0)
                        {
                            return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:red">' + performance + '%</span>';
                        }
                        else if (performance === null || isNaN(performance))
                        {
                            return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">-</span>';
                        }
                        else
                        {
                            return '<span style="font-weight:bold;color:' + this.color + '">' + this.name + ': <span style="color:' + (isDark ? '#cccccc' : 'black') + '">' + sign + performance + '%</span>';
                        }
                }
            },
            
        tooltip: {
            shared: true,
            split: false,
            useHTML: true,
            //valueDecimals: 4,
            formatter: function (tooltip) {
                    var s = '<span style="font-size:10px">To: ' + Highcharts.dateFormat('%a, %B %d, %Y', this.x) + '<br>From: ' + Highcharts.dateFormat('%a, %B %d, %Y', from) + '</span>';
                                
                    /*this.points.forEach(function(point) {
                        var y = point.y;
                        y = Math.round( point.y * 100) / 100;
                            
                        s = s + '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + ': ' + '<b>' + y + '</b>';
                    });
        
                    return s;*/
                                            
                    var sortedPoints = this.points.sort(function(a, b){
                        return (a.y < b.y ? -1 : (a.y > b.y ? 1 : 0));
                    });
                    
                    sortedPoints.reverse();
                    
                    sortedPoints.forEach(function(point) {
                        var performance = point.y;
                        if (performance < 0)
                            s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + (nameDict[point.series.name] ? ' ● ' + nameDict[point.series.name] : '') + ': <span style="color:red;font-weight:bold">'+ performance + '%</span>';
                        else if (performance > 0)
                            s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + (nameDict[point.series.name] ? ' ● ' + nameDict[point.series.name] : '') + ': <b>+'+ performance + '%</b>';
                        else
                            s += '<br/><span style="color:' + point.color + '">●</span> ' + point.series.name + (nameDict[point.series.name] ? ' ● ' + nameDict[point.series.name] : '') + ': <b>\u00B1'+ performance + '%</b>';
                    });
                    
                    return s;
                    
                 /*   return this.points.reduce(function (s, point) {
                        var point_series_color = point.series.color;
                        var performance = rounding((point.y/visibleDict[point.series.name][0][0] - 1) * 100, 10);
                        
                        if (performance < 0)
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<span style="color:red;font-weight:bold">' + performance + '%</span>';
                        else if (performance > 0)
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>+' + performance + '%</b>';
                        else
                            return s + '<br/><span style="color:' + point_series_color + '">●</span> ' + point.series.name + ': ' + '<b>\u00B1' + performance + '%</b>';
    
                    }, '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '</span>');*/
    
            },
    
        },
        plotOptions: {
            area: {
                stacking: null,
                //pointStart: startYear,
                marker: {
                    enabled: false
                },
                fillColor: '#00000000'
            },
            series: {
                cursor: 'pointer',
                lineWidth: 2.5,
                dataLabels: {
                    enabled: true,
                    align: 'right',
                    x: 24,
                    formatter: function() {
                      if (this.point.index == this.series.points.length - 1) {
                        var fontsize = '';
                        //if (this.series.index == 5)
                          fontsize = ';font-size:14';
                    
                        return '<span style="color:' + this.point.color + fontsize + '">'+this.series.name+'</span><br>' + '<span style="color:' + this.point.color + ';font-size:12">' + (this.point.y > 0 ? '+' : (this.point.y == 0 ? '\u00B1' : '')) + this.point.y + '%</span>';
                      } else {
                        return null;
                      }
                    },
                    crop: false,
                    overflow: false,
                    allowOverlap: true,
                    align: 'center',
                },
                events: {
                    click: function (event) {
                        window.open("stockinfo?code=" + this.name, '_blank');
                    },
                }
            }
        },
    
        /*responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    title: {
                        align: 'left'
                    },
                    subtitle: {
                        y: -150,
                        x: -20
                    },
                    yAxis: {
                        labels: {
                            align: 'left',
                            x: 0,
                            y: -3
                        },
                        tickLength: 0,
                        title: {
                            align: 'high',
                            reserveSpace: false,
                            rotation: 0,
                            textAlign: 'left',
                            y: -20
                        }
                    }
                }
            }]
        }*/
    });
    
    
    var dlength = Object.keys(dict_XXXly_return).length;
    for (var i = 0; i < dlength; i++)
    {
        //var colorSeries = colors[i]; //new Highcharts.Color(Highcharts.getOptions().colors[i]).setOpacity(seriesOpacity).get();
		tmpchart.addSeries({
		    type: 'area',
			name: (Object.keys(dict_XXXly_return))[i],
			data: null,
			//color: colorSeries,
			//negativeColor: colorSeries,
			yAxis: 0,
			allowOverlapX: true,
			zIndex: 1,
			title: '',
			width: 5,
            height: 5,
            dataGrouping: {
                enabled: true,
                forced: true,
                approximation: 'close',
                units: [
                    ['day', [1]]
                ]
            }
    	}, true);
    }
    
    return tmpchart;
}
                                
const btn = document.getElementById('play-pause-button'),
    input = document.getElementById('play-range');
//var startYear = 0,
//    endYear = 0;

input.min = 0;
input.value = 0;

// General helper functions
/*const arrToAssociative = arr => {
    const tmp = {};
    arr.forEach(item => {
        tmp[item[0]] = item[1];
    });

    return tmp;
};*/

function getSubtitle() {
    //return `<span style='font-size: 26px;font-weight:bold;color:grey'>${(Object.keys(dict_XXXly_return)).length == 0 ? '' : (new Date(dict_XXXly_return[(Object.keys(dict_XXXly_return))[maxlengthSeries]][parseInt(input.value)][0])).toISOString().substring(0, 10)}</span>`;
    if ((Object.keys(dict_XXXly_return)).length == 0 || chart === null || chart.series === null || chart.series === undefined || chart.series.length <= 0 || chart.series[0].options === null || chart.series[0].options === undefined || chart.series[0].options.data.length == 0)
        return '';
        
    return `<span style='font-size: 26px;font-weight:bold;color:grey'>${(new Date(chart.series[0].options.data[chart.series[0].options.data.length - 1][0])).toISOString().substring(0, 10)}</span>`;
}

function stop()
{
    if (chart === null || chart === undefined)
    {
        return;
    }
    
    stockcodeInputRED();
        
    if ((Object.keys(dict_XXXly_return)).length > 0)
    {
        if (btn.title == 'Play' && input.value != input.max)
            play(btn);
            
        input.value = input.max;
    }
}
function pause(button) {
    button.title = 'Play';
    button.className = 'fa fa-play';
    clearTimeout(chart.sequenceTimer);
    chart.sequenceTimer = undefined;
}

var raceIdx = 0;
function update() {
    const series = chart.series;
    const yearIndex = parseInt(input.value) - parseInt(input.min);
    const dataLength = series.length > 0 && series[0].options !== undefined ? series[0].options.data.length : 0;

    // If slider moved back in time
    if (yearIndex != dataLength) 
    {
        /*for (let i = 0; i < series.length; i++) {
            const seriesData = series[i].data.slice(0, parseInt(input.value));
            series[i].setData(seriesData, false);
        }
        
        raceIdx = parseInt(input.value);*/
        
        
        for (let i = 0; i < chart.series.length; i++) {
            var cs = chart.series[i];
            cs.setData(null, false);
            raceIdx = 0;
            
            var values = dict_XXXly_return[(Object.keys(dict_XXXly_return))[i]];
            for (var j = 0; j <= yearIndex && j < values.length; j++)
            {
                const newY = values[j];
                series[i].addPoint(newY, false);
                raceIdx++;

            }
        }
    }

    // If slider moved forward in time
    /*if (yearIndex > dataLength - 1) {
        const remainingYears = yearIndex - dataLength;
        for (let i = 0; i < series.length; i++) {
            var values = dict_XXXly_return[(Object.keys(dict_XXXly_return))[i]];
            for (let j = parseInt(input.value) - remainingYears; j < parseInt(input.value); j++) {
                const newY = values[j];
                series[i].addPoint(newY, false);
                raceIdx++;
            }
            raceIdx--;
        }
    }*/

    // Add current year

    for (let i = 0; i < series.length; i++) {
        //const newY = formatRevenue[i][input.value];
        var values = dict_XXXly_return[(Object.keys(dict_XXXly_return))[i]];
        if (raceIdx < values.length)
        {
            const newY = values[raceIdx];
            series[i].addPoint(newY, false);

        }
    }

    chart.redraw();
    raceIdx += 1;
    
    
    

    //if (input.value > endYear) {
        // Auto-pause
        //pause(btn);
    //}
    
    //if (raceIdx > maxDictlength) {
    if (raceIdx > dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length) {
        // Auto-pause
        pause(btn);
        //reset = true;
    }
    else
        input.value = raceIdx;
        
    chart.update(
        {
            subtitle: {
                text: getSubtitle()
            },
        },
        false,
        false,
        false
    );
}

function play(button) {
    if (chart === null || chart === undefined)
    {
        return;
    }

    stockcodeInputRED();
        
    if ((Object.keys(dict_XXXly_return)).length > 0 && !loadfromDB)
    {
        
        if (raceIdx >= dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length - 1)
        {
            for (let i = 0; i < chart.series.length; i++) {
                var cs = chart.series[i];
                //const seriesData = cs.data.slice(0, cs.data.length);
                cs.setData(null, false);
            }
            
            raceIdx = 0;
            //reset = false;
            
            input.value = 0;//dict[(Object.keys(dict))[0]][0][0];
        }
    
    
        input.min = 0; //dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]][0][0];
        input.max = dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length;// dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length - 1; //dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]][dict_XXXly_return[(Object.keys(dict_XXXly_return))[0]].length - 1][0];
        //startYear = input.min;
        //endYear = input.max;
    
        // Reset slider at the end
        //if (input.value > endYear) {
        //    input.value = startYear;
        //}
        button.title = 'Pause';
        button.className = 'fa fa-pause';
        chart.sequenceTimer = setInterval(function () {
            update();
        }, 0);
    }
    else
    {
        validate();   
    }
}

btn.addEventListener('click', function () {
    if (chart.sequenceTimer) {
        pause(this);
    } else {
        play(this);
    }
}, { passive: true });

//play(btn);

// Trigger the update on the range bar click.
input.addEventListener('input', update, { passive: true });

$(document).ready(function(){
    if (timeframefromURL == 'd')
    {
        document.getElementById('radio_daily').checked = true;
        document.getElementById('radio_daily').click();
        //$('#radio_daily').prop('checked', true);
    }
    else if (timeframefromURL == 'm')
    {
        document.getElementById('radio_monthly').checked = true;
        document.getElementById('radio_monthly').click();
        //$('#radio_monthly').prop('checked', true);
    }
    else
    {
        document.getElementById('radio_weekly').checked = true;
        document.getElementById('radio_weekly').click();
        //$('#radio_weekly').prop('checked', true);
    }
    if (codefromURL != "")
    {
        race_input.value = codefromURL.replace(/,/g, ', ');
        validate();
    }
    
    document.getElementById("raceInput").focus();
    
    if (startupTab == '2') {
        ensureComposerReady().then(function() {
            if (composerCodesfromURL != "")
                return restoreComposerSeries(composerCodesfromURL.split(','));
        });
    }
})

function restoreComposerSeries(parts) {
    var seriesToLoad = parts.slice(0, maxSeriesNumber).map(function(part) {
        const decoded = decodeURIComponent(part);
        const pair = decoded.split(':');
        const value = pair[0];
        const key = pair.slice(1).join(':');

        if (value == 'stock') {
            return {
                name: '',
                region: '',
                id: key,
                type: value
            };
        }

        return findNameByIdAndType(dataStructure, key, value);
    }).filter(Boolean);

    return seriesToLoad.reduce(function(promise, series) {
        return promise.then(function() {
            return createChart(series, true);
        });
    }, Promise.resolve());
}


// suppress "Added non-passive event listener to a scroll-blocking 'touchstart' event" and 'touchmove' event in browser
jQuery.event.special.touchstart = {
  setup: function( _, ns, handle ){
    if ( ns.includes("noPreventDefault") ) {
      this.addEventListener("touchstart", handle, { passive: false });
    } else {
      this.addEventListener("touchstart", handle, { passive: true });
    }
  }
};
jQuery.event.special.touchmove = {
  setup: function( _, ns, handle ){
    if ( ns.includes("noPreventDefault") ) {
      this.addEventListener("touchmove", handle, { passive: false });
    } else {
      this.addEventListener("touchmove", handle, { passive: true });
    }
  }
};


$(document).ready(function() {
    const $input = $("#raceInput");
    const $suggestions = $("#suggestions");
    const $results = $("#results");
    let highlightedIndex = -1; // Track highlighted item (keyboard or mouseover)
    let isKeyboardNavigating = false; // Flag to track keyboard navigation

    // Update results div
    function updateResults() {
        const terms = $input.val().split(/,\s*/).filter(term => term.trim() !== "");
        $results.html("<strong>Selected Terms:</strong> " + (terms.length ? terms.join(", ") : "None"));
    }

    // Show suggestions
    function showSuggestions(data) {
        $suggestions.empty();
        highlightedIndex = -1;
        isKeyboardNavigating = false;
        if (data.length === 0) {
            $suggestions.hide();
            return;
        }
        
        // Set dropdown width to match input width
        const inputWidth = $input.outerWidth();
        $suggestions.css("width", inputWidth + "px");

        data.forEach(item => {
            const $li = $("<li>")
                .addClass("suggestion-item")
                .append($("<div>").text(item.label))
                .on("click", () => selectItem(item.value))
                .on("mouseover", () => {
                    // Always allow mouseover to override, clear keyboard highlight
                    $suggestions.find(".suggestion-item").removeClass("highlight").removeClass("keyboard-highlight");
                    $li.addClass("highlight");
                    highlightedIndex = $suggestions.find(".suggestion-item").index($li);
                    isKeyboardNavigating = false; // Switch to mouse control
                    //console.log("Mouseover item:", item.label, "Index:", highlightedIndex, "Keyboard navigating:", isKeyboardNavigating);
                })
                .on("mouseout", () => {
                    // Remove highlight unless it's the keyboard-highlighted item
                    if ($suggestions.find(".suggestion-item").index($li) !== highlightedIndex || !isKeyboardNavigating) {
                        $li.removeClass("highlight");
                    }
                    //console.log("Mouseout item:", item.label);
                });
            $suggestions.append($li);
        });
        $suggestions.show();
    }

    // Select an item
    function selectItem(value) {
        const terms = $input.val().split(/,\s*/);
        terms.pop(); // Remove last term
        terms.push(value, "");
        const newValue = terms.join(", ");
        $input.val(newValue);
        $suggestions.hide();
        highlightedIndex = -1;
        isKeyboardNavigating = false;
        updateResults();
        // Return focus to input and place cursor at the end
        $input.focus();
        $input[0].setSelectionRange(newValue.length, newValue.length);
        //console.log("Selection complete: Focused input, value:", newValue);
    }

    var dot = ' \u25CF ';
    // Handle keyboard navigation
    $input.on("keydown", function(e) {
        const items = $suggestions.find(".suggestion-item");
        if (!items.length) return;

        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
            e.preventDefault();
            isKeyboardNavigating = true; // Set keyboard navigation mode
            // Clear all highlights
            items.removeClass("highlight").removeClass("keyboard-highlight");
            const itemCount = items.length;
            if (e.key === "ArrowDown") {
                // Circular navigation: wrap to first item if at end
                highlightedIndex = highlightedIndex >= itemCount - 1 ? 0 : highlightedIndex + 1;
            } else if (e.key === "ArrowUp") {
                // Circular navigation: wrap to last item if at start or -1
                highlightedIndex = highlightedIndex <= 0 ? itemCount - 1 : highlightedIndex - 1;
            }
            if (highlightedIndex >= 0) {
                const $item = $(items[highlightedIndex]);
                $item.addClass("keyboard-highlight");
                //console.log(`${e.key}: Highlighted item:`, $item.text(), "Index:", highlightedIndex, "Keyboard navigating:", isKeyboardNavigating);
            }
        } else if (e.key === "Enter" && highlightedIndex >= 0) {
            e.preventDefault();
            const item = items.eq(highlightedIndex).find("div").text();
            const fields = item.split(dot);
            const code = fields.length > 0 ? fields[0] : '';
            selectItem(code);
            //console.log("Enter: Selected item:", item);
        } else if (e.key === "Escape") {
            $suggestions.hide();
            highlightedIndex = -1;
            isKeyboardNavigating = false;
            items.removeClass("highlight").removeClass("keyboard-highlight");
            //console.log("Escape: Cleared suggestions");
        }
    });

    // Reset keyboard navigation flag on input to allow mouseover
    $input.on("input", function() {
        isKeyboardNavigating = false;
        const terms = $input.val().split(/,\s*/);
        const lastTerm = terms[terms.length - 1].trim();

        if (lastTerm.length === 0) {
            $suggestions.hide();
            highlightedIndex = -1;
            return;
        }

        $.ajax({
            url: "db_autocomplete.php",
            type: 'post',
            dataType: "json",
            data: { search: lastTerm },
            success: function(data) {
                const dataArray = [];
                data.forEach(pn => {
                    var label = pn.code + dot + pn.name_tc + dot + pn.name_en + dot + pn.exchange;
                    if (pn.name_tc == null || pn.name_tc == pn.name_en)
                    {
                        if (pn.exchange == '')
                        {
                            if (pn.name_en == '')
                                label = pn.code;
                            else
                                label = pn.code + dot + pn.name_en;
                        }
                        else if (pn.name_en == '')
                            label = pn.code + dot + pn.exchange;
                        else
                            label = pn.code + dot + pn.name_en + dot + pn.exchange;
                    }
                    
                    dataArray.push({ label: label, value: pn.code });


                });
                showSuggestions(dataArray);
            },
            error: function(xhr, status, error) {
                //console.error("Error fetching suggestions:", error);
                $suggestions.hide();
            }
        });
    });

    // Update results on change or keyup
    $input.on("change keyup", updateResults);

    // Hide suggestions on click outside
    $(document).on("click", function(e) {
        if (!$(e.target).closest(".search-container").length) {
            $suggestions.hide();
            highlightedIndex = -1;
            isKeyboardNavigating = false;
            $suggestions.find(".suggestion-item").removeClass("highlight").removeClass("keyboard-highlight");
            //console.log("Click outside: Cleared suggestions");
        }
    });
});

// Composer
var maxSeriesNumber = 6;
// Check if jQuery UI Autocomplete is loaded
if (typeof $.fn.autocomplete === 'undefined') {
    console.error('jQuery UI Autocomplete is not loaded. Please ensure jQuery UI is included and loaded after jQuery.');
}

const urls = {
    NYSE: {tab1: 'market?data=NYSE', tab2: 'market?data=NYSE&tab=2', tab3: 'market?data=NYSE&tab=3', tab4: 'market?data=NYSE&tab=4', tab5: 'market?data=NYSE&tab=5', tab8: 'market?data=NYSE&tab=8', tab9: 'market?data=NYSE&tab=9', tab11: 'market?data=NYSE&tab=11'},
    Nasdaq: {tab1: 'market?data=NASDAQ', tab2: 'market?data=NASDAQ&tab=2', tab3: 'market?data=NASDAQ&tab=3', tab4: 'market?data=NASDAQ&tab=4', tab5: 'market?data=NASDAQ&tab=5'},
    LSE: {tab1: 'market?data=LSE', tab3: 'market?data=LSE&tab=3', tab4: 'market?data=LSE&tab=4', tab5: 'market?data=LSE&tab=5'},
    TSX: {tab1: 'market?data=TSX', tab3: 'market?data=TSX&tab=3', tab4: 'market?data=TSX&tab=4', tab5: 'market?data=TSX&tab=5'},
    ASX: {tab1: 'market?data=ASX', tab3: 'market?data=ASX&tab=3', tab4: 'market?data=ASX&tab=4', tab5: 'market?data=ASX&tab=5'},
    NSE: {tab1: 'market?data=NSE', tab3: 'market?data=NSE&tab=3', tab4: 'market?data=NSE&tab=4', tab5: 'market?data=NSE&tab=5'},
    TYO: {tab1: 'market?data=TYO', tab3: 'market?data=TYO&tab=3', tab4: 'market?data=TYO&tab=4', tab5: 'market?data=TYO&tab=5'},
    HKEX: {tab1: 'market?data=HKEX', tab2: 'market?data=HKEX&tab=2', tab3: 'market?data=HKEX&tab=3', tab4: 'market?data=HKEX&tab=4', tab5: 'market?data=HKEX&tab=5', tab6: 'market?data=HKEX&tab=6', tab7: 'market?data=HKEX&tab=7'},
    SHSE: {tab1: 'market?data=SHSE', tab2: 'market?data=SHSE&tab=2', tab3: 'market?data=SHSE&tab=3', tab4: 'market?data=SHSE&tab=4', tab5: 'market?data=SHSE&tab=5', tab8: 'market?data=SHSE&tab=8'},
    SZSE: {tab1: 'market?data=SZSE', tab2: 'market?data=SZSE&tab=2', tab3: 'market?data=SZSE&tab=3', tab4: 'market?data=SZSE&tab=4', tab5: 'market?data=SZSE&tab=5'},
    US: {tab1: 'econ?country=US', tab2: 'econ?country=US&tab=2', tab3: 'econ?country=US&tab=3', tab4: 'econ?country=US&tab=4', tab5: 'econ?country=US&tab=5', tab6: 'econ?country=US&tab=6', tab8: 'econ?country=US&tab=8'},
    HK: {tab1: 'econ?country=HK', tab7: 'econ?country=HK&tab=7'},
}

// Chart Composer catalog is loaded on demand.
let dataStructure = null;

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

const baseChartConfig = {
    stockTools: {
        gui: {
            buttons: [
                //'indicators',
                'separator',
                'simpleShapes',
                'lines',
                'crookedLines',
                'measure',
                'advanced',
                'toggleAnnotations',
                'separator',
                'verticalLabels',
                'separator',
                'zoomChange',
                //'fullScreen',
                //'typeChange',
                //'separator',
                // Disabling 'flags' and 'currentPriceIndicator'
            ]
        }
    },
    chart: {
        zooming: {
            mouseWheel: false
        },
        style: {
            fontFamily: 'Montserrat'
        },
        events: {
            load: function () {
                this.seriesList = []; // Track added series
            },
            render: function () {
                // Update credits position: plotTop + offset
                this.credits.attr({
                    y: this.plotTop + 30 // 20px below plot area top
                });
            }
        }
    },
    xAxis: {
        events: {
            afterSetExtremes: function (e) {
                updateHistory(true);
            }
        }
    },

    credits: {
        enabled: true,
        text: '360MiQ.com',
        href: '',
        style: {
            fontSize: '20px',
            fontWeight: 'bold',
            cursor: 'arrow',
            color: 'lightgrey'
        },
        position: {
            align: 'left',
            verticalAlign: 'middle',
            x: 100,
            y: -22,
            //x: 0,
            //y: -document.getElementById('raceContainer').clientHeight / 2
        }
    },
    legend: {
        enabled: true,
        align: 'center',
        verticalAlign: 'bottom',
        layout: 'horizontal',
        labelFormatter: function () {
            var current = this.yData[this.yData.length - 1];
            const code = this.options.custom?.code || '';
            
            if (code.startsWith('idx:') || code.endsWith(':JHDUSRGDPBR'))
                return `<span style="color: ${this.color}">${this.name}</span>`;
            else
                return `<span style="color: ${this.color}">${this.name}: </span><b>${current}</b>`;
        }
    },
    tooltip: {
        shared: true, // Join tooltip for all series
        valueDecimals: 2,
        split: false, // Ensure single tooltip
        xDateFormat: '%A, %b %e, %Y',
        formatter: function () {
            var s = '<span style="font-size:10px">' + Highcharts.dateFormat('%A, %B %d, %Y', this.x) + '</span>';
                        
            $.each(this.points , function(i, point) {
                const code = point.series.options.custom?.code || '';

                if (code.startsWith('idx:'))
                {
                    s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name;
                }
                else
                {
                    s = s + '<br/><span style="color:' + point.series.color + '">●</span> ' + point.series.name + ': ' + '<b>' + point.y + '</b>';
                }
            });
            
            return s;
        },
        useHTML: true // Required for HTML dot
    },
    title: {
        text: 'Chart Composer'
    },
    rangeSelector: {
        inputEnabled: true,
        selected: 8,
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
            count: 5,
            text: '5y'
        }, {
            type: 'year',
            count: 10,
            text: '10y'
        }, {
            type: 'year',
            count: 50,
            text: '50y'
        }, {
            type: 'all',
            text: 'All'
        }]
    },
    plotOptions: {
        series: {
            dataGrouping: {
                enabled: false,
                forced: true,
                approximation: 'close',
            },
            cursor: 'auto',
            showInLegend: true, // Ensure series appear in legend
            /*point: {
                events: {
                    mouseOver: function() {
                        if (this.series.userOptions.custom.code.startsWith('stock:'))
                        {
                            if (this.series.halo)
                            {
                                this.series.halo.css({
                                    cursor: 'pointer'
                                });
                            }
                        }
                    },
                    mouseOut: function() {
                        if (this.series.halo)
                        {
                            this.series.halo.css({
                                cursor: 'auto'
                            });
                        }
                    }
                }
            },
            events: {
                click: function (event) {
                    if (this.userOptions.custom.code.startsWith('stock:'))
                    {
                        window.open("stockinfo?code=" + encodeURIComponent(this.userOptions.id), '_blank');
                    }
                },
                mouseOver: function() {
                    if (this.userOptions.custom.code.startsWith('stock:'))
                    {
                        if (this.halo)
                        {
                            this.halo.css({
                                cursor: 'pointer'
                            });
                        }
                    }
                },
                mouseOut: function() {
                    if (this.halo)
                    {
                        this.halo.css({
                            cursor: 'auto'
                        });
                    }
                }
            }*/
        },
    },
    series: [],
    navigator: {
      yAxis :{
          type: 'linear',
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
};

let chartComposer = null;

function loadScriptOnce(src) {
    var existing = document.querySelector('script[src="' + src + '"]');
    if (existing)
        return existing.dataset.loaded === '1' ? Promise.resolve() : new Promise(function(resolve, reject) {
            existing.addEventListener('load', resolve, { once: true });
            existing.addEventListener('error', reject, { once: true });
        });

    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = function() {
            script.dataset.loaded = '1';
            resolve();
        };
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadStylesheetOnce(href) {
    if (document.querySelector('link[href="' + href + '"]'))
        return;

    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

function loadComposerCatalog() {
    if (dataStructure)
        return Promise.resolve(dataStructure);

    return fetch('assets/data/chart-composer-catalog.json', {
        cache: 'force-cache',
        credentials: 'same-origin'
    }).then(function(response) {
        if (!response.ok)
            throw new Error('Chart Composer catalog request failed');
        return response.json();
    }).then(function(catalog) {
        dataStructure = catalog;
        return catalog;
    });
}

function setComposerLoading(isLoading) {
    var container = document.getElementById('container');
    if (!container)
        return;

    $('#tab-2').find('input, select, button').prop('disabled', isLoading);
    if (!isLoading) {
        $addSeries.prop('disabled', true);
        $removeAll.prop('disabled', !chartComposer || chartComposer.seriesList.length === 0);
    }
    container.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    if (isLoading && !chartComposer)
        container.innerHTML = '<div class="text-center" style="padding-top:80px">Loading Chart Composer…</div>';
}

function ensureComposerReady() {
    if (chartComposer)
        return Promise.resolve(chartComposer);
    if (composerRuntimePromise)
        return composerRuntimePromise;

    setComposerLoading(true);
    loadStylesheetOnce('https://code.highcharts.com/11.4.0/css/stocktools/gui.css');
    loadStylesheetOnce('https://code.highcharts.com/11.4.0/css/annotations/popup.css');

    var composerScripts = [
        'https://code.highcharts.com/stock/11.4.0/modules/annotations.js',
        'https://code.highcharts.com/stock/11.4.0/modules/data.js',
        'https://code.highcharts.com/11.4.0/modules/annotations-advanced.js',
        'https://code.highcharts.com/11.4.0/modules/price-indicator.js',
        'https://code.highcharts.com/11.4.0/modules/stock-tools.js'
    ];

    composerRuntimePromise = loadComposerCatalog().then(function() {
        return composerScripts.reduce(function(promise, src) {
            return promise.then(function() { return loadScriptOnce(src); });
        }, Promise.resolve());
    }).then(function() {
        chartComposer = Highcharts.stockChart('container', {...baseChartConfig});
        if (!composerControlsInitialized) {
            initializeControls();
            composerControlsInitialized = true;
        }
        setComposerLoading(false);
        return chartComposer;
    }).catch(function(error) {
        composerRuntimePromise = null;
        setComposerLoading(false);
        showNotification('Failed to load Chart Composer.');
        throw error;
    });

    return composerRuntimePromise;
}

// Populate dropdowns and combo box
const $regionExchange = $('#region-exchange');
const $category = $('#category');
const $dataSeries = $('#data-series');
const $dataSeriesList = $('#data-series-list');
const $addSeries = $('#add-series');
const $removeAll = $('#remove-all');
const $seriesList = $('#series-list');
const $comboBox = $dataSeries.parent('.combo-box');
let seriesList = [];

// Function to adjust combo box width based on control-container width
/*function adjustComboBoxWidth() {
    const defaultComboWidth = 420;
    const minComboWidth = 200;
    const controlContainerWidth = $('.control-container').innerWidth(); // Exclude padding
    const effectiveContainerWidth = controlContainerWidth - 20; // Subtract 10px left + 10px right padding
    const newComboWidth = (defaultComboWidth + 15 > effectiveContainerWidth && effectiveContainerWidth > 0) 
        ? Math.max(effectiveContainerWidth - 15, minComboWidth) 
        : defaultComboWidth;

    // Update combo box and related elements
    $('.combo-box').css('width', newComboWidth + 'px');
    $('.button-group').css('width', newComboWidth + 'px');
    $('.ui-autocomplete').css('width', newComboWidth + 'px');
}*/
function adjustComboBoxWidth() {
    const defaultComboWidth = 420;
    const minComboWidth = 200;
    var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
    // Cap at max-width of control-container (1200px)
    const controlContainerWidth = Math.min(browserwidth, 1200);
    const effectiveContainerWidth = controlContainerWidth - 30; // Subtract padding
    const newComboWidth = (defaultComboWidth + 15 > effectiveContainerWidth && effectiveContainerWidth > 0)
        ? Math.max(effectiveContainerWidth - 30, minComboWidth)
        : defaultComboWidth;

    // Update elements
    $('.combo-box').css('width', newComboWidth + 'px');
    $('.button-group').css('width', newComboWidth + 'px');
    $('.ui-autocomplete').css('width', newComboWidth + 'px');
    console.log('Browser width:', browserwidth, 'Control container width:', controlContainerWidth, 'Combo-box width:', newComboWidth);
}

// Update datalist options based on seriesList
function updateDataSeriesList() {
    const source = seriesList.map(item => ({
        label: item.name,
        value: item.name,
        id: item.id,
        region: item.region,
        category: item.category,
        url: item.url
    }));
    //console.log('Updating Autocomplete source:', source); // Debug log
    $dataSeries.autocomplete('option', 'source', source);
}

/*function mapExchangeToRegion(exchange) {
    const exchangeMap = {
        'Nasdaq': 'us',
        'NYSE': 'us',
        'NYSE Arca': 'us',
        'SHSE': 'asia'
    };
    return exchangeMap[exchange] || 'other';
}*/

// Get all series based on region and category selections
function getSeriesList(region, category) {
    const series = [];
    const regions = region === 'all' ? Object.keys(dataStructure) : [region];
    
    regions.forEach(reg => {
        if (dataStructure[reg]) {
            const categories = category === 'all' ? Object.keys(dataStructure[reg]) : [category];
            categories.forEach(cat => {
                if (dataStructure[reg][cat]) {
                    dataStructure[reg][cat].forEach(item => {
                        series.push({
                            ...item,
                            region: reg,
                            category: cat
                        });
                    });
                }
            });
        }
    });
    
    return series;
}

function fetchAjaxSeriesData(code, /*region, category,*/ type, url) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: 'db_chartcomposer_get.php',
            async: true,
            type: 'post',
            dataType: 'json',
            data: { code, /*region, category,*/ type },
            timeout: 10000,
            success: function(data) {
                if (code == 'JHDUSRGDPBR') {
                    const steppedData = insertStepTransitions(data);
                    steppedData.url = url;
                    resolve(steppedData);
                }
                else {
                    data.url = url;
                    resolve(data);
                }
            },
            error: function(xhr, status, error) {
                console.error('fetchAjaxSeriesData error:', status, error, xhr.responseText);
                reject(new Error('Failed to fetch series data'));
            }
        });
    });
}

function initializeAutocomplete() {
    $dataSeries.autocomplete({
        source: function(request, response) {
            var dot = ' \u25CF ';
            const region = $regionExchange.val() || 'all';
            const category = $category.val() || 'all';
            const term = (request.term || '').trim().toLowerCase();

            seriesList = getSeriesList(region, category);

            /*const staticResults = seriesList.map(item => ({
                label: `${item.name}${dot}${item.region}`,
                value: item.id,
                id: item.id,
                region: item.region,
                category: item.category,
                source: 'dataStructure'
            })).filter(item => !term || item.label.toLowerCase().includes(term) || item.id.toLowerCase().includes(term));*/
            
            // Ensure unique id and name
            const uniqueSeriesList = [];
            const seen = new Map();
            for (const item of seriesList) {
                const key = `${item.id}|${item.name}`; // Unique key based on id and name
                if (!seen.has(key)) {
                    seen.set(key, true);
                    uniqueSeriesList.push(item);
                }
            }
            
            /*const staticResults = uniqueSeriesList.map(item => ({
                label: `${item.name}${dot}${item.region}`,
                value: item.id,
                id: item.id,
                region: item.region,
                category: item.category,
                source: 'dataStructure'
            })).filter(item => !term || item.label.toLowerCase().includes(term) || item.id.toLowerCase().includes(term));*/
            
            
            
            /*const staticResults = uniqueSeriesList.map(item => ({
                label: `${item.name}${dot}${item.region}`,
                value: item.id,
                id: item.id,
                name: item.name,
                region: item.region,
                category: item.category,
                url: item.url,
                source: 'dataStructure'
            })).filter(item => {
                const termLower = term.toLowerCase();
                return term
                    ? item.label.toLowerCase().includes(term) || item.id.toLowerCase().includes(term)
                    : (region == 'all' && category == 'all' ? item.region.toUpperCase() === 'NASDAQ'
                    : (region == 'all' && category != 'all' ? item.category === category
                    : (region != 'all' && category == 'all' ? item.region === region
                    : (item.region === region && item.category === category))));
            }).sort((a, b) => {
                const catCompare = a.category.localeCompare(b.category);
                return catCompare !== 0 ? catCompare : a.name.localeCompare(b.name);
            });*/
            
            const termWords = term.split(/\s+/).filter(Boolean); // Split by space

        	const staticResults = uniqueSeriesList.map(item => ({
        		label: `${item.name}${dot}${item.region}`,
        		value: item.id,
        		id: item.id,
        		name: item.name,
        		region: item.region,
        		category: item.category,
        		url: item.url,
        		source: 'dataStructure'
        	})).filter(item => {
        		const labelLower = item.label.toLowerCase();
        		const idLower = item.id.toLowerCase();
        
        		if (termWords.length > 0) {
        			// Match only if ALL words are in the label or id (regardless of order)
        			return termWords.every(word => labelLower.includes(word) || idLower.includes(word));
        		} else {
        			// Fallback filtering when term is empty
        			if (region === 'all' && category === 'all') return item.region.toUpperCase() === 'NASDAQ';
        			if (region === 'all') return item.category === category;
        			if (category === 'all') return item.region === region;
        			return item.region === region && item.category === category;
        		}
        	}).sort((a, b) => {
        		const catCompare = a.category.localeCompare(b.category);
        		return catCompare !== 0 ? catCompare : a.name.localeCompare(b.name);
        	});

            var exchange = region;
            if (exchange == 'China')
                exchange = 'SHSE_SZSE'
            else if (exchange == 'US')
                exchange = 'NYSE_Nasdaq'
            else if (exchange == 'HK')
                exchange = 'HKEX'
            else if (exchange == 'all')
                exchange = '';
                
            $.ajax({
                url: 'db_autocomplete.php',
                type: 'post',
                dataType: 'json',
                data: { search: term, exchange: exchange },
                success: function(data) {
                    if (!Array.isArray(data)) {
                        console.error('Invalid AJAX data format:', data);
                        response(staticResults);
                        return;
                    }

                    const ajaxResults = data.map(pn => {
                        if (!pn.code) return null;
                        let label = pn.code + dot + (pn.name_tc || '') + dot + (pn.name_en || '') + dot + (pn.exchange || '');
                        if (pn.name_tc == null || pn.name_tc == pn.name_en) {
                            if (pn.exchange == '') {
                                if (pn.name_en == '') label = pn.code;
                                else label = pn.code + dot + pn.name_en;
                            } else if (pn.name_en == '') label = pn.code + dot + pn.exchange;
                            else label = pn.code + dot + pn.name_en + dot + pn.exchange;
                        }
                        const itemRegion = pn.exchange;//mapExchangeToRegion(pn.exchange);
                        return {
                            label: label,
                            value: pn.name_en || pn.code,
                            id: pn.code,
                            region: itemRegion,
                            category: 'stock',
                            source: 'ajax'
                        };
                    }).filter(item => item && (!term || item.label.toLowerCase().includes(term)) && 
                        (region === 'all' || item.region === region || (item.region == 'HKEX' && region == 'HK') || (item.region == 'SHSE' && region == 'China') || (item.region == 'SZSE' && region == 'China') || (item.region == 'NYSE' && region == 'US') || (item.region == 'Nasdaq' && region == 'US')) && 
                        (category === 'all' || item.category === category));

                    //const combinedResults = [...ajaxResults.slice(0, 13), ...staticResults].slice(0, 20);
                    const ajaxLimit = Math.min(13, ajaxResults.length);
                    const staticLimit = Math.min(7, 20 - ajaxLimit, staticResults.length);
                    
                    // Adjust ajaxLimit if staticResults has fewer than 7
                    const adjustedAjaxLimit = Math.min(20 - staticLimit, ajaxResults.length);
                    
                    const ajaxSlice = ajaxResults.slice(0, adjustedAjaxLimit);
                    const staticSlice = staticResults.slice(0, 20 - ajaxSlice.length);
                    
                    const combinedResults = [...ajaxSlice, ...staticSlice];

                    response(combinedResults);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    response(staticResults);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            //console.log('Autocomplete select:', ui.item);
            $dataSeries.val(ui.item.id); // Set input to id
            $dataSeries.data('selected-id', ui.item.id);
            $dataSeries.data('selected-name', ui.item.name); // Store name
            $dataSeries.data('selected-region', ui.item.region);
            $dataSeries.data('selected-category', ui.item.category);
            $dataSeries.data('selected-url', ui.item.url);
            $dataSeries.data('selected-source', ui.item.source);
            $addSeries.prop('disabled', false);
            $addSeries.click();
        },
        change: function(event, ui) {
            //console.log('Autocomplete change:', ui.item);
            /*if (!ui.item) {
                $dataSeries.val('');
                $dataSeries.data('selected-id', null);
                $dataSeries.data('selected-region', null);
                $dataSeries.data('selected-category', null);
                $dataSeries.data('selected-source', null);
                $addSeries.prop('disabled', true);
            }*/
        },
        open: function() {
            //console.log('Autocomplete dropdown opened');
            $('.ui-autocomplete').css('z-index', 1000);
        },
        close: function() {
            //console.log('Autocomplete dropdown closed');
        }
    }).on('input focus', function() {
        //console.log('Input or focus event');
        $(this).autocomplete('search', $(this).val() || '');
    });
}

// Toggle dropdown on arrow click
$comboBox.find('.dropdown-arrow').on('click', function () {
    if (!composerControlsInitialized)
        return;
    $dataSeries.focus();
    $dataSeries.autocomplete('search', $dataSeries.val() || '');
});

$dataSeries.on('keyup', function () {
    if ($dataSeries.val() != '')
        $addSeries.prop('disabled', false);
    else
        $addSeries.prop('disabled', true);
});

// Update Category dropdown and combo box based on Region/Exchange
$regionExchange.on('change', function() {
    const region = $(this).val();
    const currentCategory = $category.val() || 'all';
    $category.empty();

    const categories = new Set(['all']);
    if (region === 'all' || region === 'US' || region === 'China' || region === 'HK') {
        Object.keys(dataStructure).forEach(reg => {
            if (region === 'all' || reg === region) {
                Object.keys(dataStructure[reg]).forEach(cat => {
                    categories.add(cat);
                });
            }
        });
    }
    //if (region === 'all' || region === 'asia' || region === 'other') {
        categories.add('stock');
    //}
    categories.add('index');
    categories.add('market');

    categories.forEach(cat => {
        const displayName = cat === 'all' ? 'All' : cat.charAt(0).toUpperCase() + cat.slice(1);
        $category.append(`<option value="${cat}">${displayName}</option>`);
    });

    if (categories.has(currentCategory)) {
        $category.val(currentCategory);
    } else {
        $category.val('all');
    }

    $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
    seriesList = getSeriesList(region, $category.val());
    $addSeries.prop('disabled', true);
    $dataSeries.autocomplete('search', '');
});

$category.on('change', function() {
    const region = $regionExchange.val();
    const category = $(this).val();
    $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
    seriesList = getSeriesList(region, category);
    $addSeries.prop('disabled', true);
    $dataSeries.autocomplete('search', '');
});

$addSeries.on('click', function() {
    const region = $dataSeries.data('selected-region');
    const category = $dataSeries.data('selected-category');
    const url = $dataSeries.data('selected-url');
    var seriesId = $dataSeries.data('selected-id');
    const source = $dataSeries.data('selected-source');

    $dataSeries.autocomplete( "close" );
    
    if (chartComposer.seriesList.length >= maxSeriesNumber) {
        showNotification(`Maximum of ${maxSeriesNumber} series allowed.`);
        return;
    }
    
    var dot = ' \u25CF ';
    if (source === 'dataStructure') {
        //const seriesName = dataStructure[region][category].find(s => s.id === seriesId).name;
        const series = dataStructure[region]?.[category]?.find(s => s.id === seriesId);
        series.region = region;
        if (series != null)
        {
            if (chartComposer.seriesList.includes(seriesId))
            {
                $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
                $addSeries.prop('disabled', true);
                showNotification('This series is already in the chart.');
            }
            else
            {
                createChart(series, true);
            }
        }    
    } else if (source === undefined || source === null || source === 'ajax') {
        seriesId = seriesId === undefined || seriesId === null? $dataSeries.val() : seriesId.toUpperCase();
        seriesId = seriesId.toUpperCase();
        
        if (chartComposer.seriesList.includes(seriesId))
        {
            $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
            $addSeries.prop('disabled', true);
            showNotification('This series is already in the chart.');
        }
        else
        {
            var series = findNameByIdAndType(dataStructure, seriesId);
            if (series != null)
                createChart(series, false);
            else
            {
                createChart({
                    name: '',
                    region: '',
                    id: seriesId,
                    type: 'stock',
                    url: url
                }, true);
            }
        }
    }
});

// Remove individual series
$seriesList.on('click', '.remove-series', function () {
    const $item = $(this).closest('.series-item');
    const seriesId = $item.data('id');
    const series = chartComposer.get(seriesId);
    if (series) {
        series.remove();
        chartComposer.seriesList = chartComposer.seriesList.filter(id => id !== seriesId);
        $item.remove();
        // Disable Remove All button if no series remain
        if (chartComposer.seriesList.length === 0) {
            clearChart();
            //$removeAll.prop('disabled', true);
        }
    }
    updateNavigatorToEarliestSeries();
});

// Remove all series
$removeAll.on('click', function () {
    clearChart();
});

 // Adjust combo box width after DOM and CSS are fully rendered
$(window).on('load', function() {
    setTimeout(function() {
        adjustComboBoxWidth();
        if (composerControlsInitialized)
            $dataSeries.autocomplete("close");
        //openTabHash();
    }, 100);
    jQuery("html,body").animate({scrollTop: 0}, 1000);
});
$(window).resize(adjustComboBoxWidth);

function clearChart()
{
    try {
        // Destroy the current chart
        if (chartComposer) {
            chartComposer.destroy();
            chartComposer = null; // Clear reference
        }

        // Create a new chart
        chartComposer = Highcharts.stockChart('container', baseChartConfig);

        // Redraw (optional, as new chart renders automatically)
        chartComposer.redraw();
        
        $seriesList.empty();
        $removeAll.prop('disabled', true);
        $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
        $addSeries.prop('disabled', true);
        document.getElementById("chartnoteComposer").style.display = "none";
        history.replaceState(null, '', 'tool&tab=2');
        newRelativePathQueryTab2 = '';
    } catch (error) {
        console.error('Error clearing chart:', error);
    }
}

// Helper function to update navigator to the series with the earliest date
function updateNavigatorToEarliestSeries() {
    try {
        // Found earliest series
        let earliestSeries = null;
        let earliestDate = Infinity;
        chartComposer.series.forEach(series => {
            if (series.options.className !== 'highcharts-navigator-series' && series.name.indexOf('Navigator') == -1)
            {
                if (series && series.xData && series.xData.length > 0 && series.xData[0]) {
                    const firstDate = series.xData[0];
                    if (firstDate < earliestDate) {
                        earliestSeries = series;
                        earliestDate = firstDate;
                    }
                }
            }
        });

        //var urlstring = '';
        chartComposer.series.forEach(series => {
            if (series.options.className !== 'highcharts-navigator-series' && series.name.indexOf('Navigator') == -1)
            {
                if (series && series.xData && series.xData.length > 0 && series.xData[0]) {
                    if (series.xData[0] == earliestSeries.xData[0])
                    {
                        series.update({
                            showInNavigator: true
                        });
                    }
                    else
                    {
                        series.update({
                            showInNavigator: false
                        });
                    }
                    
                    if (series.options.custom?.code == "econ:JHDUSRGDPBR")
                    {
                        series.update({
                            type: 'area',
                            color: '#808080',
                            opacity: 0.25,
                            fillOpacity: 1,
                        });
                        
                        series.yAxis.update({
                            max: 1,
                            labels: {
                                enabled: false
                            },
                            title: {
                                text: null
                            },
                        }, false); 
                        
                        series.update({
                            step: 'right'
                        }, true); 
                    }
                    
                    //urlstring += encodeURIComponent(series.options.custom?.code) + ',';
                }
            }
        });
        
        // Convert to timestamps
        let fromTime = composerFromfromURL ? new Date(composerFromfromURL).getTime() : null;
        let toTime = composerTofromURL ? new Date(composerTofromURL).getTime() : null;
        
        // Collect all valid xData points from non-internal series
        const allXData = chartComposer.series
          .filter(s => !s.options.isInternal)
          .flatMap(s => s.xData || []);
        
        if (allXData.length > 0) {
          const minX = Math.min(...allXData);
          const maxX = Math.max(...allXData);
        
          const clampedFrom = fromTime !== null ? Math.max(fromTime, minX) : minX;
          const clampedTo = toTime !== null ? Math.min(toTime, maxX) : maxX;
        
          if (clampedTo >= clampedFrom) {
            chartComposer.xAxis[0].setExtremes(clampedFrom, clampedTo);
          }
        }

        //chartComposer.redraw();
        
        if (chartComposer.series.length > 0) {
            //if (urlstring.endsWith(',')) {
            //    urlstring = urlstring.slice(0, -1); // Remove the last character
            //}
            
            updateHistory(true);
            
        }

        //if (urlstring != '')
        //    document.getElementById("chartnoteComposer").style.display = "";
    } catch (error) {
        console.error('Error updating navigator:', error);
    }
}

function updateHistory (isReplace)
{
    var fromISO = '';
    var toISO = '';
    var urlstring = '';
    
    if (chartComposer.xAxis && chartComposer.xAxis[0]) {
        const extremes = chartComposer.xAxis[0].getExtremes();
        const start = new Date(extremes.min).toISOString().split('T')[0];
        const end = new Date(extremes.max).toISOString().split('T')[0];
    
        let latestX = -Infinity;
        let earliestX = Infinity;
    
        chartComposer.series.forEach(series => {
            if (!series.options.isInternal && series.visible && series.xData.length) {
                // Find earliest x in series.xData
                const seriesFirstX = Math.min(...series.xData);
                if (seriesFirstX < earliestX) {
                    earliestX = seriesFirstX;
                }
    
                // Find latest x in series.xData
                const seriesLastX = Math.max(...series.xData);
                if (seriesLastX > latestX) {
                    latestX = seriesLastX;
                }
                
                urlstring += encodeURIComponent(series.options.custom?.code) + ',';
            }
        });
    
        const earliestDate = new Date(earliestX).toISOString().split('T')[0];
        const latestDate = new Date(latestX).toISOString().split('T')[0];
    
        fromISO = start;
        toISO = '';
    
        if (start === earliestDate) {
            fromISO = '';
        }
    
        if (end !== latestDate) {
            toISO = end;
        }
    
        // Use fromISO and toISO as needed
    }


    if (chartComposer.series.length > 0) {
        if (urlstring.endsWith(',')) {
            urlstring = urlstring.slice(0, -1); // Remove the last character
        }
        
        if (fromISO == '' && toISO == '')
            newRelativePathQueryTab2 = window.location.pathname + '?codes=' + urlstring + '&tab=2';
        else
            newRelativePathQueryTab2 = window.location.pathname + '?codes=' + urlstring + '&cfrom=' + fromISO + '&cto=' + toISO + '&tab=2';
            
        if (isReplace)
        {
            history.replaceState(null, '', newRelativePathQueryTab2);
        }
        else
        {
            history.pushState(null, '', newRelativePathQueryTab2); // no one calls here           
        }
        
        if (urlstring != '')
            document.getElementById("chartnoteComposer").style.display = "";
    }
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

  if (tabId == '2')
      ensureComposerReady();
});

//var tab1loaded = false, tab2loaded = false, tab3loaded = false, tab4loaded = false, tab5loaded = false, tab6loaded = false, tab7loaded = false, tab8loaded = false; 
$(function() {
    openTabHash(); // for the initial page load
    window.addEventListener("hashchange", openTabHash, false); // for later changes to url
    
});

function openTabHash()
{
    //console.log('openTabHash');
    
    // Javascript to enable link to tab
    const urlNew = new URL(window.location);
    const params = urlNew.searchParams;
    let tab = params.get('tab');
    
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show') ;
    } 
    else if (tab) {
        $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show') ;
    }
}

$(document).ready(function() {
    let lastClickedNav = this.hash;
    
    //On Click Event
    $("a.mynav-link").click(function() {

        /*if (!(this.hash == "#tab-1" && tab1loaded) &&
            !(this.hash == "#tab-2" && tab2loaded) &&
            !(this.hash == "#tab-3" && tab3loaded) &&
            !(this.hash == "#tab-4" && tab4loaded) &&
            !(this.hash == "#tab-5" && tab5loaded) &&
            !(this.hash == "#tab-6" && tab6loaded) &&
            !(this.hash == "#tab-7" && tab7loaded) &&
            !(this.hash == "#tab-8" && tab8loaded))
        {
            window.location.href = this.hash;
            jQuery("html,body").animate({scrollTop: 0}, 1000);
        }*/
        
        if (lastClickedNav !== undefined && lastClickedNav !== this.hash) {

            highchartReflow();
            
            if (this.hash == "#tab-1" && newRelativePathQueryTab1 != '')
            {
                history.pushState(null, '', newRelativePathQueryTab1);
            }
            else if (this.hash == "#tab-2" && newRelativePathQueryTab2 != '')
            {
                history.pushState(null, '', newRelativePathQueryTab2);
            }
        }
        lastClickedNav = this.hash;
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

// Initialize dropdowns and combo box on page load
function initializeControls() {
    $category.empty().append('<option value="all" selected>All</option>');
    const categories = new Set(['stock', 'index', 'market', 'econ']);
    categories.forEach(cat => {
        $category.append(`<option value="${cat}">${cat.charAt(0).toUpperCase() + cat.slice(1)}</option>`);
    });

    seriesList = getSeriesList('all', 'all');
    initializeAutocomplete();
    $dataSeries.autocomplete('search', '');
}

// Execute a function when the user presses a key on the keyboard
document.getElementById("data-series").addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    $addSeries.click();
  }
});

// Function to search name by id and optional type across all exchanges
function findNameByIdAndType(data, id, type = null) {
    // Validate inputs
    if (!data || typeof id !== 'string') {
        console.warn('Invalid input: data and id (string) must be provided');
        return null;
    }

    // All possible categories
    const categories = ['stock', 'index', 'market', 'econ', 'hklabor', 'hkhousing'];

    // Iterate through all exchanges
    for (const region in data) {
        // If type is provided, search only the relevant category
        if (type) {
            const category = {
                idx: 'index',
                mb: 'market',
                sp500: 'market',
                econ: 'econ',
                hklabor: 'econ',
                hkhousing: 'econ',
                hkhousing1: 'econ',
            }[type];

            if (category && data[region][category]) {
                const item = data[region][category].find(
                    (item) => item.id === id && item.type === type
                );
                if (item) {
                    return { name: item.name, region : region, id : id, type: type, url: item.url };
                }
            }
        } else {
            // If no type, search all categories in the exchange
            for (const category of categories) {
                if (data[region][category]) {
                    const item = data[region][category].find(
                        (item) => item.id === id
                    );
                    if (item) {
                        return { name: item.name, region : region, id : id, type: item.type, url: item.url };
                    }
                }
            }
        }
    }

    return null;
}

function createChart(series, isAsync)
{
    var seriesType = series.type;
    if (seriesType == 'stock')
        seriesType = '';

    return ensureComposerReady()
    .then(function() {
        return fetchAjaxSeriesData(series.id, /*region, category,*/ seriesType, series.url);
    })
    .then(function(seriesData) {
        if (seriesData && !chartComposer.seriesList.includes(seriesData.id)) {
            //var yaxisIdx = chartComposer.yAxis.length;
            //var xaxisIdx = chartComposer.xAxis.length;
            series.name = series.name == ''? seriesData.name : series.name;
            
            chartComposer.addAxis({
                id: seriesData.id,
                title: {
                    text: series.name
                },
                opposite: true,
                labels: {
                    //padding: 10, // Add padding between labels and axis
                    //x: -10, // Move labels left to avoid overlap
                    reserveSpace: true // Ensure space is reserved for labels
                },
                //offset: 20, // Move y-axis away from plot area
                //minPadding: 0.05 // Add padding to prevent data points from touching axis
            }, false, true, false);
            
            chartComposer.addSeries({
                id: seriesData.id,
                name: seriesType == '' && seriesData.id != '' ? series.name + ` (${seriesData.id})` : series.name,
                data: seriesData.data,
                yAxis: seriesData.id,
                custom: {
                    code: series.type + ':' + seriesData.id
                },
                //enableMouseTracking: !(series.type + ':' + seriesData.id).startsWith('idx:') // Disable tooltip for idx: series
            });

            // Update navigator to the earliest series
            updateNavigatorToEarliestSeries();
    
            chartComposer.seriesList.push(seriesData.id);
            
            chartComposer.series.forEach(function(s) {
                s.yAxis.update({
                    labels: {
                        x: 10,
                        style: {
                            color: s.color
                        }
                    },
                    title: {
                        style: {
                            color: s.color
                        }
                    }
                }, false);
            });
            chartComposer.redraw();

            var dot = '&nbsp;\u25CF&nbsp;';
            if (series.region != '') // dataStructure
            {
                //if (exchangesOnMarketPage.includes(series.region.toUpperCase()))
                //{
                    $seriesList.append(`
                        <div class="series-item" data-id="${seriesData.id}">
                            ${series.name}${dot}
                            <a href="${seriesData.url}" target="_blank">${series.region}</a>
                            <button class="remove-series" title="Remove">✖</button>
                        </div>
                    `);
                //}
                /*else
                {
                    $seriesList.append(`
                        <div class="series-item" data-id="${seriesData.id}">
                            ${series.name}${dot}${series.region}
                            <button class="remove-series" title="Remove">✖</button>
                        </div>
                    `);
                }*/
            }
            else // stock
            {
                if (exchangesOnMarketPage.includes(seriesData.exchange.toUpperCase()))
                {
                    $seriesList.append(`
                        <div class="series-item" data-id="${seriesData.id}">
                            <a href="stockinfo?code=${seriesData.id}" target="_blank">${seriesData.id}</a>
                            ${dot}${seriesData.name}${dot}
                            <a href="${urls[seriesData.exchange].tab3}" target="_blank">${seriesData.exchange}</a>
                            <button class="remove-series" title="Remove">✖</button>
                        </div>
                    `);
                }
                else // NYSE Arca ETF
                {
                    $seriesList.append(`
                        <div class="series-item" data-id="${seriesData.id}">
                            <a href="stockinfo?code=${seriesData.id}" target="_blank">${seriesData.id}</a>
                            ${dot}${seriesData.name}${dot}${seriesData.exchange}
                            <button class="remove-series" title="Remove">✖</button>
                        </div>
                    `);
                }
            }
            
            $removeAll.prop('disabled', false);
            $dataSeries.val('').data('selected-id', null).data('selected-region', null).data('selected-category', null).data('selected-source', null).data('selected-url', null);
            $addSeries.prop('disabled', true);
        }
        return seriesData;
    })
    .catch(function(error) {
        showNotification('Failed to load series data.');
        return null;
    });
}

function insertStepTransitions(data) {
    const result = [];

    for (let i = 1; i < data.data.length; i++) {
        const [prevX, prevY] = data.data[i - 1];
        const [currX, currY] = data.data[i];

        result.push([prevX, prevY]);

        if (currY !== prevY && prevY == 0) {
            // Insert a transition point at current x with previous y
            result.push([currX, 0]);
        }
        
        else if (currY !== prevY && prevY == 1) {
            // Insert a transition point at current x with previous y
            result.push([prevX, 0]);
        }
    }

    // Push the final point
    const last = data.data[data.data.length - 1];
    result.push([last[0], last[1]]);

    return {id: data.id, data: result};
}

function showNotification(msg) {
  let notification = document.createElement('div');
  notification.className = 'notification';
  notification.textContent = msg;
  document.body.appendChild(notification);

  // Show
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Hide after 3 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      notification.remove();
    }, 500);
  }, 4000);
}

// Reset button handler
document.getElementById('reset-controls').addEventListener('click', () => {
    if (!composerControlsInitialized || !chartComposer)
        return;

    const regionExchange = document.getElementById('region-exchange');
    const category = document.getElementById('category');
    const dataSeries = document.getElementById('data-series');

    // Reset controls
    regionExchange.value = 'all';
    category.value = 'all';
    dataSeries.value = '';

    // Trigger change event to refresh dependent controls
    const changeEvent = new Event('change');
    regionExchange.dispatchEvent(changeEvent);
    $dataSeries.autocomplete("close");

    // Update button states (assuming app logic)
    document.getElementById('add-series').disabled = true;
    document.getElementById('remove-all').disabled = chartComposer.series.length === 0;

    //console.log('Controls reset: region-exchange = all, category = all, data-series = ""');
});

function removeBadgeIfExpired() {
    const startDate = new Date('2025-05-16');
    const currentDate = new Date();
    const daysDiff = (currentDate - startDate) / (1000 * 60 * 60 * 24);

    if (daysDiff >= 90) {
        //const badges = document.querySelectorAll('.new-badge, .sr-only');
        const badges = document.querySelectorAll('.new-badge, .sr-only.new');
        if (badges.length > 0) {
            badges.forEach(el => el.remove());
            observer.disconnect(); // stop observing after removal
        }
    }
}

// Run once on load
window.onload = removeBadgeIfExpired;

// Set up observer
const observer = new MutationObserver(removeBadgeIfExpired);
observer.observe(document.body, { childList: true, subtree: true });
</script>
    
<?php include "./footer.php" ?>
    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
    <!--<script src="assets/js/yaxis-panning.js"></script>-->
</body>

</html>
