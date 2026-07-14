<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="Stock Comparison & Chart Tools - 360MiQ.com" />
    <meta name="description" content="Compare stock performance over time and build custom market charts with 360MiQ's free visualization tools." />
    <meta property="og:description" content="Compare stock performance over time and build custom market charts with 360MiQ's free visualization tools." />

    <title>Stock Comparison &amp; Chart Tools - 360MiQ.com</title>
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
    <link rel="stylesheet" href="assets/js/stock-chart-engine/stock-chart-engine.css?v=20260714.2">
    

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
<script src="assets/js/price-display-policy.js?v=20260624.1"></script>
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
    color: var(--bg, #1a1a2e) !important;
}

[data-theme="dark"] #tab-1 .btn-outline-primary:hover i,
[data-theme="dark"] #tab-1 .btn-outline-primary:focus i,
[data-theme="dark"] #tab-1 .btn-outline-primary.active i {
    color: var(--bg, #1a1a2e) !important;
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
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper {
    --highcharts-neutral-color-3: #242438;
    --highcharts-neutral-color-10: #33335a;
    --highcharts-button-hover-color: #33335a;
    background: transparent !important;
    background-color: transparent !important;
    border-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-menu-wrapper {
    background: transparent !important;
    background-color: transparent !important;
    border-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-submenu-wrapper {
    background: #242438 !important;
    background-color: #242438 !important;
    border-color: #6f74a8 !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li {
    background: #242438 !important;
    background-color: #242438 !important;
    border-color: #6f74a8 !important;
    color: #e8e8e8 !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li.highcharts-separator,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li.highcharts-separator:hover {
    background: transparent !important;
    background-color: transparent !important;
    border-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-menu-item-btn,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-menu-item-btn:hover {
    background-color: transparent !important;
}

#tab-2 .highcharts-bindings-wrapper li > button.highcharts-submenu-item-arrow {
    background-color: transparent !important;
}

#tab-2 .highcharts-bindings-wrapper li > button.highcharts-submenu-item-arrow:hover {
    background-color: rgba(108, 117, 125, 0.16) !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-submenu-item-arrow {
    background-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li > button.highcharts-submenu-item-arrow:hover {
    background-color: rgba(111, 116, 168, 0.34) !important;
}

[data-theme="dark"] #tab-2 .highcharts-toggle-toolbar {
    background-color: transparent !important;
    filter: brightness(0) invert(1) !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-stocktools-toolbar li:hover,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper li.highcharts-active {
    background: #33335a !important;
    background-color: #33335a !important;
}

[data-theme="dark"] #tab-2 .highcharts-toggle-toolbar:hover {
    background-color: transparent !important;
}

[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-menu-item-btn,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-submenu-item-arrow,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-arrow-up,
[data-theme="dark"] #tab-2 .highcharts-bindings-wrapper .highcharts-arrow-down {
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

.chart-share-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 8px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.chart-share-actions .btn {
  min-width: 104px;
  border-width: 1px;
  box-shadow: none;
  outline: 0;
}

.chart-share-actions .btn:focus,
.chart-share-actions .btn.focus,
.chart-share-actions .btn:active,
.chart-share-actions .btn:not(:disabled):not(.disabled):active {
  border-color: #007bff;
  border-width: 1px;
  box-shadow: none;
  outline: 2px solid rgba(0, 123, 255, 0.35);
  outline-offset: 2px;
}

.chart-share-actions .btn:not(:disabled):not(.disabled):active {
  background-color: #fff;
  color: #007bff;
}

[data-theme="dark"] .chart-share-actions .btn:not(:disabled):not(.disabled):active {
  background-color: transparent;
  color: #8ec8ff;
}

#tab-3 {
  text-align: left;
}

.stock-chart-tool-shell {
  margin: 26px clamp(12px, 3vw, 40px) 34px;
}

.stock-chart-search-card {
  align-items: center;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 14px;
  padding: 14px;
}

.stock-chart-search-card label {
  color: #334155;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .02em;
  margin: 0 4px 0 0;
  text-transform: uppercase;
}

.stock-chart-search-card .stock-chart-input-wrap {
  flex: 1 1 260px;
  min-width: 0;
  position: relative;
}

.stock-chart-search-card .form-control {
  border-color: #cbd5e1;
  border-radius: 6px;
  height: 42px;
}

.stock-chart-search-card .btn {
  border-radius: 6px;
  font-weight: 700;
  height: 42px;
  min-width: 116px;
}

.stock-chart-status {
  color: #64748b;
  font-size: 13px;
  min-height: 20px;
  width: 100%;
}

.stock-chart-save-shared {
  justify-self: end;
  margin-left: auto;
}

.stock-chart-status.is-error {
  color: #dc2626;
}

.stock-chart-stage {
  background: #ffffff;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  height: min(78vh, 820px);
  min-height: 560px;
  overflow: hidden;
}

#toolStockChart {
  height: 100%;
  min-height: 560px;
  width: 100%;
}

#tab-3 .ui-autocomplete {
  z-index: 10000;
}

[data-theme="dark"] .stock-chart-search-card,
[data-theme="dark"] .stock-chart-stage {
  background: #111827;
  border-color: #293244;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
}

[data-theme="dark"] .stock-chart-search-card label {
  color: #dbeafe;
}

[data-theme="dark"] .stock-chart-search-card .form-control {
  background: #172033;
  border-color: #374151;
  color: #f8fafc;
}

[data-theme="dark"] .stock-chart-search-card .form-control::placeholder {
  color: #94a3b8;
}

[data-theme="dark"] .stock-chart-search-card .form-control:focus {
  background: #172033;
  border-color: #60a5fa;
  box-shadow: 0 0 0 0.18rem rgba(96, 165, 250, 0.24);
  color: #f8fafc;
}

[data-theme="dark"] .stock-chart-status {
  color: #a8c7e8;
}

[data-theme="dark"] .stock-chart-status.is-error {
  color: #fca5a5;
}

@media (max-width: 767.98px) {
  .stock-chart-tool-shell {
    margin: 18px 8px 26px;
  }

  .stock-chart-search-card {
    align-items: stretch;
    display: grid;
    grid-template-columns: 1fr;
  }

  .stock-chart-search-card .btn {
    width: 100%;
  }

  .stock-chart-save-shared {
    justify-self: stretch;
    margin-left: 0;
  }

  .stock-chart-stage,
  #toolStockChart {
    min-height: 620px;
  }
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
                            <li class="nav-item"><a class="nav-link mynav-link" id="chartcomposer-tab" data-bs-toggle="tab" data-bs-target="#chartcomposer" role="tab" data-toggle="tab" href="#tab-2">Chart Composer</a></li>
                            <li class="nav-item"><a class="nav-link mynav-link" id="stockchart-tab" data-bs-toggle="tab" data-bs-target="#tab-3" role="tab" data-toggle="tab" href="#tab-3">Advanced Chart <span class="new-badge-group" data-new-badge-start="2026-07-14"><span class="timed-new-badge">NEW</span><span class="sr-only">(new feature)</span></span></a></li>
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
                                        <div id="chartnoteRace" style="display:none;font-size:14px;">
                                            Bookmark this chart in your browser for future viewing
                                            <div class="chart-share-actions">
                                                <button id="copyLinkRace" class="btn btn-outline-primary btn-sm" type="button" title="Copy chart link"><i class="fas fa-link"></i> Copy Link</button>
                                                <button id="shareChartRace" class="btn btn-outline-primary btn-sm" type="button" title="Share chart"><i class="fas fa-share-alt"></i> Share</button>
                                            </div>
                                        </div>
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
                                <div id="chartnoteComposer" style="display:none;font-size:14px;">
                                    Bookmark this chart in your browser for future viewing
                                    <div class="chart-share-actions">
                                        <button id="copyLinkComposer" class="btn btn-outline-primary btn-sm" type="button" title="Copy chart link"><i class="fas fa-link"></i> Copy Link</button>
                                        <button id="shareChartComposer" class="btn btn-outline-primary btn-sm" type="button" title="Share chart"><i class="fas fa-share-alt"></i> Share</button>
                                    </div>
                                </div>
                                <div id="notification" class="notification"></div>
                                <div id="container" class="not-selectable" style="height: 725px;"></div>
                            </div>

                            <div class="tab-pane" role="tabpanel" id="tab-3">
                                <h2 class="sr-only">Advanced Chart - Interactive Technical Analysis Chart</h2>
                                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                                <div class="stock-chart-tool-shell">
                                    <div class="stock-chart-search-card">
                                        <label for="toolStockChartCode">Stock Code</label>
                                        <div class="stock-chart-input-wrap">
                                            <input id="toolStockChartCode" class="form-control" type="search" placeholder="Type a stock code, e.g. AAPL, SPY, 0005.HK" autocomplete="off" spellcheck="false" maxlength="40" aria-label="Stock code">
                                        </div>
                                        <button id="toolStockChartLoad" class="btn btn-primary" type="button"><i class="fas fa-search"></i> Load Chart</button>
                                        <button id="toolStockChartSaveShared" class="btn btn-outline-primary stock-chart-save-shared" type="button" hidden>Save Layout</button>
                                        <div id="toolStockChartStatus" class="stock-chart-status" aria-live="polite"></div>
                                    </div>
                                    <div class="stock-chart-stage">
                                        <div id="toolStockChart"></div>
                                    </div>
                                </div>
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
window.__TOOL_PAGE_CONFIG = {
    "codefromURL": <?php echo json_encode(isset($_GET['code']) ? $_GET['code'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "fromfromURL": <?php echo json_encode(isset($_GET['from']) ? $_GET['from'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "tofromURL": <?php echo json_encode(isset($_GET['to']) ? $_GET['to'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "timeframefromURL": <?php echo json_encode(isset($_GET['tf']) ? $_GET['tf'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "composerCodesfromURL": <?php echo json_encode(isset($_GET['codes']) ? $_GET['codes'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "composerFromfromURL": <?php echo json_encode(isset($_GET['cfrom']) ? $_GET['cfrom'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "composerTofromURL": <?php echo json_encode(isset($_GET['cto']) ? $_GET['cto'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "stockChartCodefromURL": <?php echo json_encode(isset($_GET['stockcode']) ? $_GET['stockcode'] : ((isset($_GET['tab']) && $_GET['tab'] == '3' && isset($_GET['code'])) ? $_GET['code'] : ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/pages/tool-main.js?v=20260619.2"></script>
<script src="assets/js/pages/tool-share.js?v=20260709.2"></script>
<script src="assets/js/price-display-page-hooks.js?v=20260624.1"></script>
<script src="assets/js/stock-chart-engine/stock-chart-engine.js?v=20260714.3"></script>
<script src="assets/js/pages/tool-stock-chart.js?v=20260714.1"></script>
    
<?php include "./footer.php" ?>
    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
    <!--<script src="assets/js/yaxis-panning.js"></script>-->
</body>

</html>
