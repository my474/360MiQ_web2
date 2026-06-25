<footer class="page-footer dark not-selectable">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <h5>Get Started</h5>
                <ul>
                    <li><a href="./">Home</a></li>
                    <li><a href="stockinfo">Stock</a></li>
                    <li><a href="screener">Screener</a></li>
                    <li><a href="econ?country=US">Econ - US</a></li>
                    <li><a href="econ?country=HK">Econ - HK</a></li>
                    <li><a href="tool">Tool</a></li>
                    <li><a href="/blog">Analysis</a></li>
                    <li><a href="writeforus">Write for Us&nbsp;<i class="fa-solid fa-pen-clip" title="Analysis Contributor Login/Register&#10;&#10;Free Sign-up for Everyone!"></i></a></li>
                    <li><a href="https://rapidapi.com/mchanlg/api/360miq1/" target="_blank">Data API<sub>&emsp13;by&nbsp;RapidAPI</sub></a></li>
                </ul>
            </div>
            <div class="col-sm-3">
                <!--<div class="col-sm-1">-->
                    <h5>Market</h5>
                    <ul>
                        <li><a href="market?data=NYSE">NYSE</a></li>
                        <li><a href="market?data=NASDAQ">Nasdaq</a></li>
                        <li><a href="market?data=LSE">London</a></li>
                        <li><a href="market?data=TSX">Toronto TSX</a></li>
                        <li><a href="market?data=ASX">Australia</a></li>
                        <li><a href="market?data=NSE">India NSE</a></li>
                        <li><a href="market?data=TYO">Tokyo</a></li>
                        <li><a href="market?data=HKEX">Hong Kong</a></li>
                        <li><a href="market?data=SHSE">Shanghai</a></li>
                        <li><a href="market?data=SZSE">Shenzhen</a></li>
                    </ul>
                <!--</div>
                <div class="col-sm-2">
                    <h5>&nbsp;</h5>
                    <ul>
                        <li><a href="market?data=TSX">Toronto TSX</a></li>
                        <li><a href="market?data=HKex">Hong Kong</a></li>
                        <li><a href="market?data=SHex">Shanghai</a></li>
                        <li><a href="market?data=SZex">Shenzhen</a></li>
                    </ul>
                </div>-->
            </div>                          
            <div class="col-sm-3">
                <h5>Our Info</h5>
                <ul>
                    <li><a href="<?php echo (isset($page) && $page == 'about' ? '#' : 'about-us'); ?>">About us</a></li>
                    <li><a href="<?php echo (isset($page) && $page == 'contact' ? '#' : 'contact-us'); ?>">Contact us</a></li>
                    <!--li><a href="https://x.com/360MiQ" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
                    <li><a href="https://facebook.com/360miqcom" target="_blank"><i class="fa-brands fa-facebook"></i></a></li>
                    <li><a href="https://www.youtube.com/@360miq" target="_blank"><i class="fa-brands fa-youtube"></i></a></li-->
                    <li><a href="https://play.google.com/store/apps/details?id=com.miq360" target="_blank"><img src="assets/img/google-play-badge.png" alt="Get it on Google Play" style="width:130;height:50px;" loading="lazy"></a></li>
                </ul>
            </div>
            <div class="col-sm-3">
                <h5>Legal</h5>
                <ul>
                    <li><a href="<?php echo (isset($page) && $page == 'terms' ? '#' : 'terms'); ?>">Terms and Conditions</a></li>
                    <li><a href="<?php echo (isset($page) && $page == 'disclaimer' ? '#' : 'disclaimer'); ?>">Disclaimer</a></li>
                    <li><a href="<?php echo (isset($page) && $page == 'privacypolicy' ? '#' : 'privacypolicy'); ?>">Privacy Policy</a></li>
                </ul>
                <!-- reCAPTCHA disclosure -->
                <p style="font-size: 12px; color: #ccc; margin-top: 15px; line-height: 1.4;">
                  This site is protected by reCAPTCHA and the Google
                  <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" style="color: #aaa; text-decoration: underline;">Privacy Policy</a> and
                  <a href="https://policies.google.com/terms" target="_blank" rel="noopener" style="color: #aaa; text-decoration: underline;">Terms of Service</a> apply.
                </p>
            </div>
        </div>
    </div>
    <div class="footer-copyright" style="margin: 15px 0px 0px;"><p>©&nbsp;<script>document.write(new Date().getFullYear());</script>&nbsp;&nbsp;360MiQ.com</p></div>
    <script type="text/javascript">var scrolltotop={setting:{startline:100,scrollto:0,scrollduration:1e3,fadeduration:[500,100]},controlHTML:'<i class="fas fa-arrow-circle-up" style="color:lightgrey; font-size: 60px"></i>',controlattrs:{offsetx:20,offsety:90},anchorkeyword:"#top",state:{isvisible:!1,shouldvisible:!1},scrollup:function(){this.cssfixedsupport||this.$control.css({opacity:0});var t=isNaN(this.setting.scrollto)?this.setting.scrollto:parseInt(this.setting.scrollto);t="string"==typeof t&&1==jQuery("#"+t).length?jQuery("#"+t).offset().top:0,this.$body.animate({scrollTop:t},this.setting.scrollduration)},keepfixed:function(){var t=jQuery(window),o=t.scrollLeft()+t.width()-this.$control.width()-this.controlattrs.offsetx,s=t.scrollTop()+t.height()-this.$control.height()-this.controlattrs.offsety;this.$control.css({left:o+"px",top:s+"px"})},togglecontrol:function(){var t=jQuery(window).scrollTop();this.cssfixedsupport||this.keepfixed(),this.state.shouldvisible=t>=this.setting.startline?!0:!1,this.state.shouldvisible&&!this.state.isvisible?(this.$control.stop().animate({opacity:1},this.setting.fadeduration[0]),this.state.isvisible=!0):0==this.state.shouldvisible&&this.state.isvisible&&(this.$control.stop().animate({opacity:0},this.setting.fadeduration[1]),this.state.isvisible=!1)},init:function(){jQuery(document).ready(function(t){var o=scrolltotop,s=document.all;o.cssfixedsupport=!s||s&&"CSS1Compat"==document.compatMode&&window.XMLHttpRequest,o.$body=t(window.opera?"CSS1Compat"==document.compatMode?"html":"body":"html,body"),o.$control=t('<div id="topcontrol">'+o.controlHTML+"</div>").css({position:o.cssfixedsupport?"fixed":"absolute",bottom:o.controlattrs.offsety,right:o.controlattrs.offsetx,opacity:0,cursor:"pointer","z-index":"10001"}).attr({title:"Scroll to Top"}).click(function(){return o.scrollup(),!1}).appendTo("body"),document.all&&!window.XMLHttpRequest&&""!=o.$control.text()&&o.$control.css({width:o.$control.width()}),o.togglecontrol(),t('a[href="'+o.anchorkeyword+'"]').click(function(){return o.scrollup(),!1}),t(window).bind("scroll resize",function(t){o.togglecontrol()})})}};scrolltotop.init();</script>

<!--link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css"-->
<!--script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script-->
<script>
  (function () {
    if (typeof Highcharts === 'undefined' || typeof Highcharts.Chart === 'undefined') {
      const scripts = [
        'https://code.highcharts.com/8.2.0/highcharts.js',
        'https://code.highcharts.com/8.2.0/modules/no-data-to-display.js',
        'https://code.highcharts.com/8.2.0/highcharts-more.js',
        'https://code.highcharts.com/8.2.0/modules/exporting.js'
      ];

      function loadSequentially(index) {
        if (index >= scripts.length) return;
        const script = document.createElement('script');
        script.src = scripts[index];
        script.onload = () => loadSequentially(index + 1);
        document.head.appendChild(script);
      }

      loadSequentially(0);
    }
  })();
</script>
<!--script type="text/javascript" src="assets/js/compromise.min.js"></script-->
<script src="https://unpkg.com/compromise"></script>
<style>
html[data-theme="dark"] .nav-tabs .nav-link:hover,
html[data-theme="dark"] .nav-tabs .nav-link:focus,
html[data-theme="dark"] .nav-tabs .nav-link.active:hover,
html[data-theme="dark"] .nav-tabs .nav-link.active:focus,
html[data-theme="dark"] .nav-tabs .nav-item.show .nav-link:hover,
html[data-theme="dark"] .nav-tabs .nav-item.show .nav-link:focus {
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}
</style>
<style>
@import url("https://fonts.googleapis.com/css?family=Open+Sans:300,600");
.chatbot {
position: fixed;
top: 0;
bottom: 0;
width: 100%;
max-width: 100%;
box-shadow: 0 -6px 99px -17px rgba(0, 0, 0, 0.68);
border-radius: 4px 4px 0px 0px;
overflow: hidden;
z-index: 100000;
text-align: start;
-webkit-touch-callout: none;
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
transition: all 0.3s ease;
}
.nonselect {
-webkit-touch-callout: none;
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
}

@media screen and (min-width: 640px) {
.chatbot {
max-width: 470px;
max-height: 100%;
right: 100px;
top: auto;
}
}
.chatbot.chatbot--closed {
top: auto;
width: 60px;
height: 60px;
right: 20px;
bottom: 70px;
border-radius: 50%;
box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

@media screen and (min-width: 640px) {
  .chatbot.chatbot--closed {
    right: 20px;
    width: 60px;
    bottom: 20px;
  }
}

#topcontrol {
    bottom: 90px !important;
    top: auto !important;
    transition: transform 0.3s ease-in-out, opacity 0.5s !important;
}
@media (max-width: 639px) {
    #topcontrol {
        bottom: 140px !important;
    }
}
.topcontrol-down {
    transform: translateY(70px) !important;
}

.bottom-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #fff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 200000;
    padding: 10px 5px;
    transition: transform 0.3s ease-in-out;
    height: 60px;
    justify-content: space-around;
}

.bottom-nav-item {
    min-width: 50px;
    text-align: center;
    color: #666 !important;
    text-decoration: none;
    font-size: 11px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    font-weight: 550 !important;
}
.bottom-nav-item span {
    font-weight: 550 !important;
}

.bottom-nav-item i {
    font-size: 18px;
    margin-bottom: 2px;
    color: #666 !important;
}

.bottom-nav-item.active {
    color: #FF8C00 !important;
}

.bottom-nav-item.active i {
    color: #FF8C00 !important;
}

/* Dark mode overrides for bottom nav */
[data-theme="dark"] .bottom-nav-item,
[data-theme="dark"] .bottom-nav-item span,
[data-theme="dark"] .bottom-nav-item i {
    color: #999 !important;
}

[data-theme="dark"] .bottom-nav-item.active,
[data-theme="dark"] .bottom-nav-item.active span,
[data-theme="dark"] .bottom-nav-item.active i,
[data-theme="dark"] .bottom-nav-item.active svg {
    color: #FF8C00 !important;
}

@media (max-width: 639px) {
    .bottom-nav {
        display: flex;
    }
}

.navbar, .chatbot.chatbot--closed {
    transition: transform 0.3s ease-in-out;
}
.nav-hidden {
    transform: translateY(-100%);
}
.chatbot.chatbot--closed.bottom-hidden {
    transform: translateY(200px) !important;
}
.bottom-nav.bottom-hidden {
    transform: translateY(100%);
}

/* Bottom Sheet Styles */
.sheet-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 200010;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.sheet-overlay.show {
    display: block;
    opacity: 1;
}

.bottom-sheet {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #fff;
    z-index: 200020;
    border-radius: 20px 20px 0 0;
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 80vh;
    overflow-y: auto;
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
.bottom-sheet.show {
    transform: translateY(0);
}

.sheet-handle {
    width: 40px;
    height: 4px;
    background: #ccc;
    border-radius: 2px;
    margin: 12px auto;
    cursor: pointer;
    touch-action: none;
    transition: background 0.2s;
}
.sheet-handle:active {
    background: #999;
}

.sheet-header {
    text-align: center;
    font-weight: bold;
    padding: 10px;
}

.chatbot__header {

    text-align: center;
    padding: 0 0 15px 0;
    font-weight: 600;
    color: #333;
    border-bottom: 0px solid #eee;
    font-size: 16px;
}

.sheet-content {
    display: flex;
    flex-direction: column;
}

.sheet-content a {
    padding: 16px 20px;
    color: #555 !important;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500 !important;
    border-bottom: 1px solid #f5f5f5;
}
.sheet-content a:last-child {
    border-bottom: none;
}

.chatbot__header {
color: #fff;
display: flex;
align-items: center;
justify-content: space-between;
background-color: #393285;
height: 54px;
padding: 0 20px;
width: 100%;
cursor: pointer;
transition: background-color 0.2s ease;
}
.chatbot--closed .chatbot__header {
  justify-content: center;
  padding: 0;
  height: 60px;
  width: 60px;
  border-radius: 50%;
}
.chatbot__header:hover {
background-color: #493285;
}
.chatbot__header p {
margin-right: 20px;
}
.chatbot--closed .chatbot__header p {
  display: none;
}

.chatbot__close-button {
fill: #fff;
}
.chatbot__close-button.icon-speech {
width: 20px;
display: none;
}
.chatbot--closed .chatbot__close-button.icon-speech {
display: block;
width: 28px;
height: 28px;
}
.chatbot__close-button.icon-close {
width: 14px;
}
.chatbot--closed .chatbot__close-button.icon-close {
display: none;
}

/* Updated .chatbot__header-icons */
.chatbot__header-icons {
  display: flex;
  align-items: center;
  gap: 4px; /* Reduced from 8px to 4px to minimize space between icons */
}

/* Ensure no extra margins or padding on icons */
.chatbot__close-button.icon-close {
  width: 14px;
  margin: 0; /* Explicitly set to 0 to prevent inherited margins */
  padding: 0; /* Prevent any padding */
}

.chatbot__close-button.icon-speech {
  width: 20px;
  display: none;
  margin: 0;
  padding: 0;
}

.chatbot__help-button {
  fill: #fff;
  width: 18px;
  cursor: pointer;
  transition: fill 0.2s ease;
  display: block;
  margin: 10px;
  padding: 0;
}

.chatbot__help-button:hover {
  fill: #e0e0e0;
}

.chatbot--closed .chatbot__help-button {
  display: none;
}


.chatbot__message-window {
height: calc(100% - (54px + 60px));
padding: 40px 20px 20px;
background-color: #fff;
overflow-x: none;
overflow-y: auto;
overscroll-behavior: contain;
-webkit-touch-callout: text;
-webkit-user-select: text;
-khtml-user-select: text;
-moz-user-select: text;
-ms-user-select: text;
user-select: text;
}
@media screen and (min-width: 640px) {
.chatbot__message-window {
height: 620px;
}
}
.chatbot__message-window::-webkit-scrollbar {
width: 6px;
}
.chatbot__message-window::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px
}

.chatbot--closed .chatbot__message-window {
display: none;
}

.chatbot__messages {
padding: 0;
margin: 0;
list-style: none;
display: flex;
flex-direction: column;
width: auto;
}
.chatbot__messages li {
margin-bottom: 10px;
}
.chatbot__messages li.is-ai {
display: inline-flex;
align-items: flex-start;
}
.chatbot__messages li.is-user {
display: inline-flex;
align-self: flex-end;
}
.chatbot__messages li .is-ai__profile-picture {
margin-right: 8px;
}
.chatbot__messages li .is-ai__profile-picture .icon-avatar {
width: 24px;
height: 24px;
padding-top: 6px;
}

.chatbot__messages li .is-user__profile-picture {
margin-left: 8px;
}
.chatbot__messages li .is-user__profile-picture .icon-avatar {
width: 24px;
height: 24px;
padding-top: 6px;
}

.chatbot__message {
display: inline-block;
padding: 12px 20px;
word-break: break-word;
margin: 0;
border-radius: 6px;
letter-spacing: -0.01em;
line-height: 1.45;
overflow: hidden;
}
.is-ai .chatbot__message {
background-color: #f0f0f0;
margin-right: 30px;
}
.is-user .chatbot__message {
background-color: #bbffff;
margin-left: 30px;
}
.chatbot__message a {
color: #7226e0;
word-break: break-all;
display: inline-block;
}
.chatbot__message p:first-child {
margin-top: 0;
}
.chatbot__message p:last-child {
margin-bottom: 0;
}
.chatbot__message button {
background-color: #fff;
font-weight: 300;
border: 2px solid #7226e0;
border-radius: 50px;
padding: 8px 20px;
margin: -8px 10px 18px 0;
transition: background-color 0.2s ease;
cursor: pointer;
}
.chatbot__message button.no-chatbot-style {
  cursor: pointer; /* add back pointer */
  /* Add any other styles you want for these buttons */
  padding: 6px;
  margin: 6px 3px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #f8f9fa;
  color:#212529;
}
.chatbot__message button:hover {
background-color: #f2f2f2;
}
.chatbot__message button:focus {
outline: none;
}
.chatbot__message img {
max-width: 100%;
}
.chatbot__message .card {
background-color: #fff;
text-decoration: none;
overflow: hidden;
border-radius: 6px;
color: black;
word-break: normal;
}
.chatbot__message .card .card-content {
padding: 20px;
}
.chatbot__message .card .card-title {
margin-top: 0;
}
.chatbot__message .card .card-button {
color: #7226e0;
text-decoration: underline;
}

.animation:last-child {
-webkit-animation: fadein 0.25s;
animation: fadein 0.25s;
-webkit-animation-timing-function: all 200ms cubic-bezier(0.55, 0.055, 0.675, 0.19);
animation-timing-function: all 200ms cubic-bezier(0.55, 0.055, 0.675, 0.19);
}

.chatbot__arrow {
width: 0;
height: 0;
margin-top: 10px;
}

.chatbot__arrow--right {
border-top: 6px solid transparent;
border-bottom: 6px solid transparent;
border-left: 6px solid #bbffff;
}

.chatbot__arrow--left {
border-top: 6px solid transparent;
border-bottom: 6px solid transparent;
border-right: 6px solid #f0f0f0;
}

.chatbot__entry {
/*display: flex;
align-items: center;
justify-content: space-between;
height: 60px;
padding: 0 20px;
border-top: 1px solid #e6eaee;
background-color: #fff;*/

flex-shrink: 0;
  display: flex;
  align-items: flex-end;
  padding: 10px;
  background: #fff;
}
.chatbot--closed .chatbot__entry {
display: none;
}

/*.chatbot__input {
height: 100%;
width: 80%;
border: 0;
font-size: 20px;
}*/
.chatbot__input {
  width: 100%;
  border: 0;
  resize: none;             /* Prevent manual resizing */
  overflow: hidden;         /* Hide internal scrollbars */
  min-height: 40px;         /* Or any desired initial height */
  line-height: 1.15;         /* Match your design specs */
  font-size: 18px;
  
  color: #333; /* dark gray */
  max-height: 150px;
  overflow-y: auto;

  box-sizing: border-box;
}

.chatbot__input:focus {
outline: none;
}
.chatbot__input::-webkit-input-placeholder {
color: #7f7f7f;
}
.chatbot__input::-moz-placeholder {
color: #7f7f7f;
}
.chatbot__input::-ms-input-placeholder {
color: #7f7f7f;
}
.chatbot__input::-moz-placeholder {
color: #7f7f7f;
}

.chatbot__submit {
/*fill: #7226e0;
height: 22px;
width: 22px;
transition: fill 0.2s ease;
cursor: pointer;*/

fill: #7226e0;
width: 28px;
height: 28px;
flex-shrink: 0;
align-self: flex-end; /* pin to bottom of entry */
cursor: pointer;
transform: translateY(-3px);
}
.chatbot__submit:hover {
fill: #45148c;
}

[data-theme="dark"] .chatbot__message-window {
background-color: #1a1a2e;
color: #e8e8e8;
}

[data-theme="dark"] .chatbot__header {
background-color: #5f3dc4;
color: #fff;
}

[data-theme="dark"] .chatbot__header:hover {
background-color: #7048e8;
}

[data-theme="dark"] .is-ai .chatbot__message {
background-color: #2a2a3e;
color: #e8e8e8;
}

[data-theme="dark"] .is-user .chatbot__message {
background-color: #1a3a4e;
color: #e8e8e8;
}

[data-theme="dark"] .chatbot__message button {
background-color: #2a2a3e;
color: #e8e8e8;
border-color: #9b5ce0;
}

[data-theme="dark"] .chatbot__message button:hover {
background-color: #3a3a4e;
}

[data-theme="dark"] .chatbot__message button.no-chatbot-style {
background-color: #1e1e32;
color: #e8e8e8;
border-color: #555;
}

[data-theme="dark"] .chatbot__message .card {
background-color: #1e1e32;
color: #e8e8e8;
border-color: #3a3a4e;
}

[data-theme="dark"] .chatbot__message a,
[data-theme="dark"] .chatbot__message .card .card-button {
color: #b084ff;
}

[data-theme="dark"] .chatbot__arrow--left {
border-right-color: #2a2a3e;
}

[data-theme="dark"] .chatbot__arrow--right {
border-left-color: #1a3a4e;
}

[data-theme="dark"] .chatbot__entry {
background: #1e1e32;
border-top: 1px solid #3a3a4e;
}

[data-theme="dark"] .chatbot__input {
background-color: #1e1e32;
color: #e8e8e8;
}

[data-theme="dark"] .chatbot__input::placeholder {
color: #9a9aaa;
}

[data-theme="dark"] .chatbot__input::-webkit-input-placeholder {
color: #9a9aaa;
}

[data-theme="dark"] .chatbot__input::-moz-placeholder {
color: #9a9aaa;
}

[data-theme="dark"] .chatbot__submit {
fill: #ffa400;
}

[data-theme="dark"] .chatbot__submit:hover {
fill: #ffbd3d;
}

.u-text-highlight {
color: #00ffff;
}

.loader {
margin-bottom: -2px;
text-align: center;
opacity: 0.3;
}

.loader__dot {
display: inline-block;
vertical-align: middle;
width: 6px;
height: 6px;
margin: 0 1px;
background: black;
border-radius: 50px;
-webkit-animation: loader 0.45s infinite alternate;
animation: loader 0.45s infinite alternate;
}
.loader__dot:nth-of-type(2) {
-webkit-animation-delay: 0.15s;
animation-delay: 0.15s;
}
.loader__dot:nth-of-type(3) {
-webkit-animation-delay: 0.35s;
animation-delay: 0.35s;
}

@-webkit-keyframes loader {
0% {
transform: translateY(0);
}
100% {
transform: translateY(-5px);
}
}

@keyframes loader {
0% {
transform: translateY(0);
}
100% {
transform: translateY(-5px);
}
}
@-webkit-keyframes fadein {
from {
opacity: 0;
margin-top: 10px;
margin-bottom: 0;
}
to {
opacity: 1;
margin-top: 0;
margin-bottom: 10px;
}
}
@keyframes fadein {
from {
opacity: 0;
margin-top: 10px;
margin-bottom: 0;
}
to {
opacity: 1;
margin-top: 0;
margin-bottom: 10px;
}
}
* {
box-sizing: border-box;
}

/*body {
background: url("https://images.unsplash.com/photo-1464823063530-08f10ed1a2dd?dpr=1&auto=compress,format&fit=crop&w=1199&h=799&q=80&cs=tinysrgb&crop=&bg=") no-repeat center center;
background-size: cover;
height: 1000px;
font-family: "Open Sans", sans-serif;
font-size: 16px;
}*/

input {
font-family: "Open Sans", sans-serif;
}

strong {
font-weight: 600;
}

.intro {
display: block;
margin-bottom: 20px;
}

.screenerlist li:not(:last-child) {
    margin-bottom: 5px;
}
.chatbot p {
    margin-top: 0px;
}
</style>

<script>
window.console = window.console || function(t) {};
</script>

<!--/head>

<body translate="no"-->
<!-- Bottom Nav Bar for Mobile -->
<div class="bottom-nav">
    <a href="./" class="bottom-nav-item <?php echo (isset($page) && $page == 'home' ? 'active' : ''); ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <div class="bottom-nav-item <?php echo (isset($page) && $page == 'market' ? 'active' : ''); ?>" onclick="toggleBottomMenu(event, 'market')">
        <div style="display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-chart-line"></i>
            <i class="fas fa-caret-down nav-chevron" style="position: static; margin-left: 4px;"></i>
        </div>
        <span>Market</span>
    </div>
    <a href="stockinfo" class="bottom-nav-item <?php echo (isset($page) && $page == 'stockinfo' ? 'active' : ''); ?>">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 2px;">
            <rect x="2" y="7" width="3" height="4" fill="currentColor"/>
            <path d="M3.5 3V7M3.5 11V15" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
            <rect x="7" y="4" width="3" height="10" stroke="currentColor" stroke-width="1" fill="none"/>
            <path d="M8.5 1V4M8.5 14V17" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
            <rect x="12" y="8" width="3" height="4" stroke="currentColor" stroke-width="1" fill="none"/>
            <path d="M13.5 5V8M13.5 12V15" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
        </svg>
        <span>Stock</span>
    </a>
    <a href="screener" class="bottom-nav-item <?php echo (isset($page) && $page == 'screener' ? 'active' : ''); ?>">
        <i class="fas fa-filter"></i>
        <span>Screener</span>
    </a>
    <div class="bottom-nav-item <?php echo (isset($page) && $page == 'econ' ? 'active' : ''); ?>" onclick="toggleBottomMenu(event, 'econ')">
        <div style="display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-landmark"></i>
            <i class="fas fa-caret-down nav-chevron" style="position: static; margin-left: 4px;"></i>
        </div>
        <span>Econ</span>
    </div>
    <a href="tool" class="bottom-nav-item <?php echo (isset($page) && $page == 'tool' ? 'active' : ''); ?>">
        <i class="fas fa-wrench"></i>
        <span>Tool</span>
    </a>
</div>

<!-- Mobile Bottom Sheet -->
<div id="sheet-overlay" class="sheet-overlay" onclick="closeSheet()"></div>
<div id="bottom-sheet" class="bottom-sheet">
    <div class="sheet-handle" onclick="closeSheet()"></div>
    <div id="sheet-header" class="sheet-header">Menu</div>
    <div id="sheet-content" class="sheet-content"></div>
</div>

<div class="chatbot chatbot--closed notranslate">
<div class="chatbot__header">
  <p id='chatBar' style='padding-top: 16px;'><strong>Got a stock?</strong> <span class="u-text-highlight">Ask 360MiQ</span> <span style="color:#ffa400;font-weight:550">AI</span></p>
  <div class="chatbot__header-icons">
    <svg class="chatbot__close-button icon-speech" viewBox="0 0 32 32">
      <use xlink:href="#icon-speech" />
    </svg>
    <svg class="chatbot__help-button icon-help" viewBox="0 0 32 32" aria-label="Help">
      <use xlink:href="#icon-help" />
    </svg>
    <svg class="chatbot__close-button icon-close" viewBox="0 0 32 32">
      <use xlink:href="#icon-close" />
    </svg>
  </div>
</div>
<div class="chatbot__message-window">
<ol class="chatbot__messages">
<li class="is-ai animation">
<div class="is-ai__profile-picture">
<svg class="icon-avatar" viewBox="0 0 32 32">
<use xlink:href="#avatar" />
</svg>
</div>
<span class="chatbot__arrow chatbot__arrow--left"></span>
<p class='chatbot__message' id='help_msg'>Hi, I’m your AI stock assistant.<br><br>Please enter a stock code or company name with an optional exchange code:<br><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick='multiChoiceAnswer("AAPL")'><i>AAPL</i></button></span><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick='multiChoiceAnswer("Berkshire Hathaway")'><i>Berkshire Hathaway</i></button></span><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick='multiChoiceAnswer("7203 TYO")'><i>7203 TYO</i></button></span><br><span style="display: flex; justify-content: center;margin-top:14px"><strong>*** OR ***</strong></span><br>Ask any question about a stock:<br><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick="multiChoiceAnswer('What is AAPL\'s EPS?')"><i>What is AAPL's EPS?</i></button></span><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick='multiChoiceAnswer("How is NVIDIA doing?")'><i>How is NVIDIA doing?</i></button></span><br><span style="margin-left:16px"><button style="font-size:16px;font-weight:400" onclick='multiChoiceAnswer("Is Tesla overvalued?")'><i>Is Tesla overvalued?</i></button></span>
</p>
</li>
<!-- Answer and response added here -->
</ol>
</div>
<div class="chatbot__entry chatbot--closed">
<textarea class="chatbot__input" placeholder="Enter a stock or a question..."></textarea>
<svg class="chatbot__submit" viewBox="0 0 32 32">
<use xlink:href="#icon-send" />
</svg>
</div>
</div>

<!-- Symbols -->
<svg height="0px" width="0px" style="display: block;">
<!-- Close icon -->
<symbol id="icon-close" viewBox="0 0 32 32">
<title>Close</title>
<path d="M2.624 8.297l2.963-2.963 23.704 23.704-2.963 2.963-23.704-23.704z" />
<path d="M2.624 29.037l23.704-23.704 2.963 2.963-23.704 23.704-2.963-2.963z" />
</symbol>

<!-- Help icon -->
<symbol id="icon-help" viewBox="0 0 32 32">
  <title>Info</title>
  <!-- Circle outline -->
  <path style="fill: none; stroke: #fff; stroke-width: 3;" d="M16 2C8.3 2 2 8.3 2 16s6.3 14 14 14 14-6.3 14-14S23.7 2 16 2z" />
  
  <!-- Bolder, longer 'i' -->
  <!-- Dot of the 'i' -->
  <circle cx="16" cy="9.5" r="1.6" style="fill: #fff;" />
  <!-- Vertical bar of the 'i' -->
  <rect x="14.6" y="13" width="2.8" height="9" style="fill: #fff;" />
</symbol>
  
<!-- Speech icon -->
<symbol id="icon-speech" viewBox="0 0 32 32">
<title>Speech</title>
<path style="fill: #ffa400; fill-rule: evenodd;"
d="M21.795 5.333h-11.413c-0.038 0-0.077 0-0.114 0l-0.134 0.011v2.796c0.143-0.006 0.273-0.009 0.385-0.009h11.277c0.070 0 7.074 0.213 7.074 7.695 0 5.179-2.956 7.695-9.036 7.695h-3.792c-0.691 0-1.12 0.526-1.38 1.159l-1.387 3.093-1.625 3.77 0.245 0.453h2.56l2.538-5.678h2.837c7.633 0 11.84-3.727 11.84-10.494 0.001-8.564-7.313-10.492-9.875-10.492z" />
<path style="fill: #ffa400; fill-rule: evenodd;"
d="M10.912 24.259c-0.242-0.442-0.703-0.737-1.234-0.737-0 0-0 0-0 0h-0.56c-0.599-0.011-1.171-0.108-1.71-0.28l0.042 0.012c-1.82-0.559-4.427-2.26-4.427-7.424 0-6.683 5.024-7.597 7.109-7.686v-2.8c-2.042 0.095-9.91 1.067-9.91 10.483 0 4.832 1.961 7.367 3.606 8.64 1.38 1.067 3.109 1.744 4.991 1.843l0.033 0.019 2.805 5.211 1.41-3.273z" />
</symbol>

<!-- Send icon -->
<symbol id="icon-send" viewBox="0 0 23.97 21.9">
<title>Send</title>
<path style="fill: url(#linearGradient1030); fill-rule: evenodd;" d="M0.31,9.43a0.5,0.5,0,0,0,0,.93l8.3,3.23L23.15,0Z"/>
<path style="fill: url(#linearGradient1030); fill-rule: evenodd;" d="M9,14.6H9V21.4a0.5,0.5,0,0,0,.93.25L13.22,16l6,3.32A0.5,0.5,0,0,0,20,19l4-18.4Z"/>
</symbol>
<!-- Avatar icon -->
<symbol id="avatar" width="32" height="32" viewBox="0 0 44.25 44">
<title>Avatar</title>
<path style="fill: url(#linearGradient1030); fill-rule: evenodd;" d="M1035.88,1696.25a22,22,0,1,1-22.13,22A22.065,22.065,0,0,1,1035.88,1696.25Z" transform="translate(-1013.75 -1696.25)"/>
<!--path style="fill: #fff; fill-rule: evenodd;" d="M1030.18,1725.16h2.35a0.335,0.335,0,0,0,.34-0.33v-5.23h5.94v5.23a0.342,0.342,0,0,0,.34.33h2.36a0.342,0.342,0,0,0,.34-0.33v-12.36a0.34,0.34,0,0,0-.34-0.32h-2.36a0.34,0.34,0,0,0-.34.32v4.51h-5.94v-4.51a0.333,0.333,0,0,0-.34-0.32h-2.35a0.333,0.333,0,0,0-.34.32v12.36a0.335,0.335,0,0,0,.34.33" transform="translate(-1013.75 -1696.25)"/-->
<path style="fill: #fff; fill-rule: evenodd;" d="M1020.5,1729h5.04l1.82-4.62h8.96l1.82,4.62h5.04l-8.12-20.3h-6.3Zm7.14-8 3.08-7.98 3.08,7.98Zm15.7,8h5.04v-20.3h-5.04Z" transform="translate(-1013.75 -1696.25)" />

<defs
     id="defs5727">
    <linearGradient
       gradientTransform="matrix(0.26458333,0,0,0.26458333,-9.5679243,-196.47283)"
       inkscape:collect="always"
       xlink:href="#linearGradient1030"
       id="linearGradient1032"
       x1="310.11682"
       y1="1056.8599"
       x2="307.08636"
       y2="507.33679"
       gradientUnits="userSpaceOnUse" />
    <linearGradient
       inkscape:collect="always"
       id="linearGradient1030">
      <stop
         style="stop-color:#ffa400;stop-opacity:1;"
         offset="0"
         id="stop1026" />
      <stop
         style="stop-color:#ffa400;stop-opacity:0.55;"
         offset="1"
         id="stop1028" />
    </linearGradient>
  </defs>
<path
       style="fill:url(#linearGradient1032);fill-opacity:1;fill-rule:evenodd;stroke-width:0.26458332"
       d="M 71.815736,16.871765 A 40.491333,40.491333 0 0 0 31.324146,57.36335 40.491333,40.491333 0 0 0 71.815736,97.854426 40.491333,40.491333 0 0 0 112.30681,57.36335 40.491333,40.491333 0 0 0 71.815736,16.871765 Z m -0.0109,4.591968 a 30.335088,30.335088 0 0 1 30.335094,30.335098 30.335088,30.335088 0 0 1 -30.335094,30.335112 30.335088,30.335088 0 0 1 -30.3351,-30.335112 30.335088,30.335088 0 0 1 30.3351,-30.335098 z" transform="translate(-1013.75 -1696.25)"
/>
</symbol>

<!-- Avatar icon -->
<symbol id="avatar_user" width="32" height="32" viewBox="0 0 44.25 44">
<title>User Avatar</title>
<path style="fill: url(#linearGradient1031); fill-rule: evenodd;" d="M1035.88,1696.25a22,22,0,1,1-22.13,22A22.065,22.065,0,0,1,1035.88,1696.25Z" transform="translate(-1013.75 -1696.25)"/>
<!--path style="fill: #fff; fill-rule: evenodd;" d="M1030.18,1725.16h2.35a0.335,0.335,0,0,0,.34-0.33v-5.23h5.94v5.23a0.342,0.342,0,0,0,.34.33h2.36a0.342,0.342,0,0,0,.34-0.33v-12.36a0.34,0.34,0,0,0-.34-0.32h-2.36a0.34,0.34,0,0,0-.34.32v4.51h-5.94v-4.51a0.333,0.333,0,0,0-.34-0.32h-2.35a0.333,0.333,0,0,0-.34.32v12.36a0.335,0.335,0,0,0,.34.33" transform="translate(-1013.75 -1696.25)"/-->
<defs
     id="defs5727">
    <linearGradient
       gradientTransform="matrix(0.26458333,0,0,0.26458333,-9.5679243,-196.47283)"
       inkscape:collect="always"
       xlink:href="#linearGradient1030"
       id="linearGradient1032"
       x1="310.11682"
       y1="1056.8599"
       x2="307.08636"
       y2="507.33679"
       gradientUnits="userSpaceOnUse" />
    <linearGradient
       inkscape:collect="always"
       id="linearGradient1031">
      <stop
         style="stop-color:#77CCFF;stop-opacity:1;"
         offset="0"
         id="stop1026" />
      <stop
         style="stop-color:#77CCFF;stop-opacity:0.45;"
         offset="1"
         id="stop1028" />
    </linearGradient>
  </defs>
<path
       style="fill:url(#linearGradient1032);fill-opacity:1;fill-rule:evenodd;stroke-width:0.26458332"
       d="M 71.815736,16.871765 A 40.491333,40.491333 0 0 0 31.324146,57.36335 40.491333,40.491333 0 0 0 71.815736,97.854426 40.491333,40.491333 0 0 0 112.30681,57.36335 40.491333,40.491333 0 0 0 71.815736,16.871765 Z m -0.0109,4.591968 a 30.335088,30.335088 0 0 1 30.335094,30.335098 30.335088,30.335088 0 0 1 -30.335094,30.335112 30.335088,30.335088 0 0 1 -30.3351,-30.335112 30.335088,30.335088 0 0 1 30.3351,-30.335098 z" transform="translate(-1013.75 -1696.25)"
/>
</symbol>
</svg>
<!--script src="https://cpwebassets.codepen.io/assets/common/stopExecutionOnTimeout-2c7831bb44f98c1391d6a4ffda0e1fd302503391ca806e7fcc7b9b87197aec26.js"></script-->

<!--script src='https://cdnjs.cloudflare.com/ajax/libs/fetch/0.10.1/fetch.min.js'></script-->
<style>
  .checkbox-label {
    display: flex;
    align-items: flex-start;
  }

  .checkbox-label input[type="checkbox"] {
    margin-right: 8px;
    margin-top: 6px; /* optional: vertically align checkbox */
    flex-shrink: 0;
  }

  .checkbox-label span {
    display: inline-block;
  }
</style>

<script id="rendered-js" >
//clearChatState();
var isBlogPath = window.location.href.includes('/blog/');
if (isBlogPath) {
  const chatBar = document.getElementById('chatBar');
  if (chatBar) {
    chatBar.style.paddingTop = '24px';
  }
}

var pl = '';
var screenerURL = '/screener?market=';
var screenerATag = '<a href="' + screenerURL;
var isFirstOpen = true;
var checkboxStates = {};
var stockchatDict = new Object();
var count = 0;
var dot = '<span style="font-size:12px;"> ● </span>';
const accessToken = '3796899bd37c423bad3a21a25277bce0';
const baseUrl = 'https://360miq.com/db_chatbot.php?';
const sessionId = '20150910';
const loader = `<span class='loader'><span class='loader__dot'></span><span class='loader__dot'></span><span class='loader__dot'></span></span>`;
const errorMessage = 'Sorry, I could not find this stock.';
const urlPattern = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
const $document = document;
const $chatbot = $document.querySelector('.chatbot');
const $chatbotMessageWindow = $document.querySelector('.chatbot__message-window');
const $chatbotHeader = $document.querySelector('.chatbot__header');
const $chatbotMessages = $document.querySelector('.chatbot__messages');
const $chatbotInput = $document.querySelector('.chatbot__input');
const $chatbotSubmit = $document.querySelector('.chatbot__submit');
var initialMessage;

const botLoadingDelay = 200;
const botReplyDelay = 500;

document.addEventListener('keypress', event => {
if (event.which == 13) validateMessage();
}, false);

$chatbotHeader.addEventListener('click', () => {
toggle($chatbot, 'chatbot--closed');
$chatbotInput.focus();

if (isFirstOpen)
{
    initialMessage = $chatbotMessages.querySelector("#help_msg");
    restoreChatState();
    //restoreMessageWindow();
    isFirstOpen = false;
}
    
}, false);

$chatbotSubmit.addEventListener('click', () => {
validateMessage();
}, false);

const toggle = (element, klass) => {
const classes = element.className.match(/\S+/g) || [],
index = classes.indexOf(klass);
index >= 0 ? classes.splice(index, 1) : classes.push(klass);
element.className = classes.join(' ');
};

const userMessage = content => {
$chatbotMessages.innerHTML += `<li class='is-user animation'>
<p class='chatbot__message'>
${content}
</p>
<span class='chatbot__arrow chatbot__arrow--right'></span>
      <div class="is-user__profile-picture">
        <svg class="icon-avatar" viewBox="0 0 32 32">
          <use xlink:href="#avatar_user" />
        </svg>
      </div>
</li>`;
};

/*const aiMessage = (content, isLoading = false, delay = 0) => {
setTimeout(() => {
removeLoader();
$chatbotMessages.innerHTML += `<li
class='is-ai animation'
id='${isLoading ? "is-loading" : ""}'>
<div class="is-ai__profile-picture">
<svg class="icon-avatar" viewBox="0 0 32 32">
<use xlink:href="#avatar" />
</svg>
</div>
<span class='chatbot__arrow chatbot__arrow--left'></span>
<div class='chatbot__message'>${content}</div>
</li>`;
}, delay);

setTimeout(() => {
var $tds = $('span[data-sparklinechat'+(count - 1)+']');
var fullLen = $tds.length;

doChunkChat($tds, fullLen, 0, count);
}, delay);

setTimeout(() => {
scrollDown();

// to restore states:
Object.entries(checkboxStates).forEach(([id, checked]) => {
  const cb = document.querySelector(`.bulk-checkbox[id="${id}"]`);
  if (cb) cb.checked = checked;
});

// Call after chat restoration and chart insertion is complete
setTimeout(toggleFirstBulkCheckbox, 600);
saveChatState();
//saveMessageWindow();
}, delay);

};*/



const aiMessage = (content, isLoading = false, delay = 0) => {
  setTimeout(() => {
    removeLoader();

    // Replace newlines with <br> for HTML rendering
    const formattedContent = content.replace(/\n/g, '<br>');

    $chatbotMessages.innerHTML += `<li
      class='is-ai animation'
      id='${isLoading ? "is-loading" : ""}'>
      <div class="is-ai__profile-picture">
        <svg class="icon-avatar" viewBox="0 0 32 32">
          <use xlink:href="#avatar" />
        </svg>
      </div>
      <span class='chatbot__arrow chatbot__arrow--left'></span>
      <div class='chatbot__message'>${formattedContent}</div>
    </li>`;
  }, delay);

  setTimeout(() => {
    var $tds = $('span[data-sparklinechat' + (count - 1) + ']');
    var fullLen = $tds.length;
    doChunkChat($tds, fullLen, 0, count);
  }, delay);

  setTimeout(() => {
    scrollDown();

    // to restore states:
    Object.entries(checkboxStates).forEach(([id, checked]) => {
      const cb = document.querySelector(`.bulk-checkbox[id="${id}"]`);
      if (cb) cb.checked = checked;
    });

    // Call after chat restoration and chart insertion is complete
    setTimeout(toggleFirstBulkCheckbox, 600);
    saveChatState();
    //saveMessageWindow();
    resetInputField();
  }, delay);
};





function toggleFirstBulkCheckbox() { // without this, the last chart will be missing data line
  const checkboxes = document.querySelectorAll('.bulk-checkbox');
  if (checkboxes.length === 0) return;

  const firstCheckbox = checkboxes[0];

  // Toggle off then on with a delay between to force event handling
  firstCheckbox.checked = !firstCheckbox.checked;
  firstCheckbox.dispatchEvent(new Event('change', { bubbles: true }));

  setTimeout(() => {
    firstCheckbox.checked = !firstCheckbox.checked;
    firstCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
  }, 100);
}



const removeLoader = () => {
let loadingElem = document.getElementById('is-loading');
if (loadingElem) $chatbotMessages.removeChild(loadingElem);
};

const escapeScript = unsafe => {
const safeString = unsafe.
replace(/</g, ' ').
replace(/>/g, ' ').
replace(/&/g, '&amp;').
replace(/"/g, ' ').
replace(/\\/, ' ').
replace(/\s+/g, ' ');
return safeString.trim();
};

const linkify = inputText => {
return inputText.replace(urlPattern, `<a href='$1' target='_blank'>$1</a>`);
};

const linkifyStock = (inputText, code, exchange, isLLM = false) => {
    if (inputText !== null && checkExchanges(exchange))
    {
        var d = dot;
        if (isLLM)
            d = '';
        return inputText.replace(code + d, `<a href='/stockinfo?code=` + code + `' target='_blank' style='color: revert;text-decoration:revert' >` + code + `</a>` + d);
    }
    else
        return inputText;
};

const validateMessage = () => {
const text = $chatbotInput.value;
const safeText = text ? escapeScript(text) : '';
if (safeText.length && safeText !== ' ') {
resetInputField();

// Store states in an object keyed by checkbox id
document.querySelectorAll('.bulk-checkbox[id]').forEach(cb => {
  checkboxStates[cb.id] = cb.checked;
});


userMessage(safeText);
send(safeText);
}
scrollDown();
return;
};

const multiChoiceAnswer = text => {
const decodedText = text.replace(/zzz/g, "'");
userMessage(decodedText);
send(decodedText);
scrollDown();
return;
};

const processResponse = (val, code, exchange) => {
if (val !== null && val != '') {
let output = '';
let messagesLength = val.length;

//for (let i = 0; i < messagesLength; i++) {if (window.CP.shouldStopExecution(0)) break;
let message = val;
let type = 0;

switch (type) {
// 0 fulfillment is text
case 0:
let parsedText = linkifyStock(message, code, exchange);
output += `<p>${parsedText}</p>`;
break;

// 1 fulfillment is card
case 1:
let imageUrl = message.imageUrl;
let imageTitle = message.title;
let imageSubtitle = message.subtitle;
let button = message.buttons[0];

if (!imageUrl && !button && !imageTitle && !imageSubtitle) break;

output += `
<a class='card' href='${button.postback}' target='_blank'>
<img src='${imageUrl}' alt='${imageTitle}' />
<div class='card-content'>
<h4 class='card-title'>${imageTitle}</h4>
<p class='card-title'>${imageSubtitle}</p>
<span class='card-button'>${button.text}</span>
</div>
</a>
`;
break;

// 2 fulfillment is a quick reply with multi-choice buttons
case 2:
let title = message.title;
let replies = message.replies;
let repliesLength = replies.length;
output += `<p>${title}</p>`;

for (let i = 0; i < repliesLength; i++) {if (window.CP.shouldStopExecution(1)) break;
let reply = replies[i];
let encodedText = reply.replace(/'/g, 'zzz');
output += `<button onclick='multiChoiceAnswer("${encodedText}")'>${reply}</button>`;
}window.CP.exitedLoop(1);
break;}

//}window.CP.exitedLoop(0);

removeLoader();
return output;
}

removeLoader();
return `<p>${errorMessage}</p>`;
};

const setResponse = (val, code, exchange, delay = 0) => {
setTimeout(() => {
aiMessage(processResponse(val, code, exchange));
}, delay);
};

const resetInputField = () => {
$chatbotInput.value = '';
$chatbotInput.style.height = 'auto';
setTimeout(() => {
    $chatbotInput.focus();
}, 100);
};

const scrollDown = () => {
const distanceToScroll =
$chatbotMessageWindow.scrollHeight - (
$chatbotMessages.lastChild.offsetHeight + 60);
$chatbotMessageWindow.scrollTop = distanceToScroll;
return false;
};

const send = (text = '') => {
//fetch(`${baseUrl}&search=${text}&lang=en&sessionId=${sessionId}`, {
/*fetch(`${baseUrl}search=${text}`, {
method: 'POST',
dataType: 'json',
headers: {
Authorization: 'Bearer ' + accessToken,
'Content-Type': 'application/json; charset=utf-8' } }).


then(response => response.json()).
then(res => {
if (res.status < 200 || res.status >= 300) {
let error = new Error(res.statusText);
throw error;
}
return res;
}).
then(res => {
setResponse(res.result, botLoadingDelay + botReplyDelay);
}).
catch(error => {
setResponse(errorMessage, botLoadingDelay + botReplyDelay);
resetInputField();
console.log(error);
});
$.ajax({
    url : "./db_chatbot.php?search=" + text,
    success : function(result){
        echo result;
    }
}); */
var fulltext = text;
var exch = '';
if (hasWhiteSpace(text))
{
    var places = {
        'london': 'lse', 
        'britain': 'lse', 
        'england': 'lse',
        'uk': 'lse', 
        'lse': 'lse', 
        'new york': 'us', 
        'newyork': 'us', 
        'ny': 'us', 
        'nyse': 'us', 
        'nasdaq': 'us', 
        'nas': 'us', 
        'usa': 'us',
        'us': 'us', 
        'toronto': 'tsx', 
        'tsx': 'tsx', 
        'tokyo': 'tyo', 
        'jp': 'tyo',
        'japan': 'tyo',
        'tyo': 'tyo', 
        'shanghai': 'cn', 
        'sh': 'cn',
        'ss': 'cn',
        'shse': 'cn', 
        'shenzhen': 'cn', 
        'sz': 'cn', 
        'szse': 'cn', 
        'china': 'cn',
        'cn': 'cn',
        'hong kong': 'hkex',
        'hongkong': 'hkex',
        'hk': 'hkex',
        'hkex': 'hkex',
        'india': 'nse', 
        'nse': 'nse', 
        'australia': 'asx', 
        'asx': 'asx', 
        'ax': 'asx',
        'au': 'asx',
    }
    
    const protectedText = text.replace(/\b[\w\d]+\.[\w\d]+\b/g, (match) => match.replace('.', '<<dot>>'));

    let docx = nlp(protectedText);
    docx = docx.normalize({ possessives: true });
    
    const rawTags = docx.sentences().terms().out('tags'); // flat array of tag objects
    
    const tags = rawTags.map(tagObj => {
      const copy = {};
      for (const key in tagObj) {
        // Replace <<DOT>> with . in the new object key
        const newKey = key.replace(/<<dot>>/g, '.');
        copy[newKey] = tagObj[key]; // copy tags array as is
      }
      return copy;
    });
    
    
    // Restore sentence data with dot
    let data = docx.sentences().data().map(sentence => {
      const restoredTerms = sentence.terms.map(term => ({
        ...term,
        text: term.text.replace(/<<dot>>/g, '.'),
        normal: term.normal.replace(/<<dot>>/g, '.')
      }));
      return {
        ...sentence,
        text: sentence.text.replace(/<<dot>>/g, '.'),
        terms: restoredTerms
      };
    });

    var organization = '';
    var propernoun = '';
    var stock = '';
    var stockDict = new Object();
    if (data.length == 1)
    {
        pl = docx.places().text().toLowerCase();
        var place = places[pl];
        if (place !== undefined)
        {
            exch = place;
        }
            
        var idx = 0;
        var itemlowercase_prev = '';
        data[0].terms.forEach((item) => {
            var itemlowercase = item.text.toLowerCase();
            if (places[itemlowercase] !== undefined && exch == '')
            {
                exch = places[itemlowercase];
            }
            else if (tags[idx] != undefined && tags[idx][itemlowercase] != undefined && (tags[idx][itemlowercase].includes('Value') || tags[idx][itemlowercase].includes('Noun') || tags[idx][itemlowercase].includes('Adjective') || tags[idx][itemlowercase].includes('Gerund') || tags[idx][itemlowercase].includes('Organization') || tags[idx][itemlowercase].includes('ProperNoun')) && itemlowercase != 'stocks' && itemlowercase != 'stock')
            {
                if (!tags[idx][itemlowercase].includes('Pronoun') ||
                    (tags[idx][itemlowercase].includes('Pronoun') && stock == ''))
                {
                    var is_a_stock = false;
                    
                    if ((!(idx > 0 && itemlowercase_prev != '' && tags[idx - 1][itemlowercase_prev].includes('Possessive')) &&
                    //((idx < tags.length - 1 && !tags[idx + 1][Object.keys(tags[idx + 1])[0]].includes('Preposition')))) ||
                    ((idx < tags.length - 1 && Object.keys(tags[idx + 1])[0] != 'of'))) ||
                    (idx == tags.length - 1 && tags.length > 1 && !(itemlowercase_prev != '' && tags[idx - 1][itemlowercase_prev].includes('Possessive'))))
                    {
                        if (tags[idx][itemlowercase].includes('Noun'))
                        {
                            if ((itemlowercase_prev != '' && !tags[idx - 1][itemlowercase_prev].includes('Determiner') && !tags[idx - 1][itemlowercase_prev].includes('Organization')) || 
                            itemlowercase_prev == '')
                            {
                                is_a_stock = true;
                            }
                        }
                    }
                            
                    if (pl != '')
                    {
                        if (!pl.includes(itemlowercase))
                        {
                            if (is_a_stock)
                            {
                                if (item.text.endsWith("'s")) {
                                    tags[idx][itemlowercase].push('Possessive');
                                }
                                stock = item.text.replace(/'s$/, '');
                                if (tags[idx][itemlowercase].includes('Organization'))
                                    stockDict[stock] = 'Organization';
                                else if (tags[idx][itemlowercase].includes('ProperNoun'))
                                    stockDict[stock] = 'ProperNoun';
                                else if (tags[idx][itemlowercase].includes('Noun'))
                                    stockDict[stock] = 'Noun';
                            }
                        }
                    }
                    else if (is_a_stock)
                    {
                        if (item.text.endsWith("'s")) {
                            tags[idx][itemlowercase].push('Possessive');
                        }
                        stock = item.text.replace(/'s$/, '');
                        if (tags[idx][itemlowercase].includes('Organization'))
                            stockDict[stock] = 'Organization';
                        else if (tags[idx][itemlowercase].includes('ProperNoun'))
                            stockDict[stock] = 'ProperNoun';
                        else if (tags[idx][itemlowercase].includes('Noun'))
                            stockDict[stock] = 'Noun';
                    }
                }

                if (tags[idx][itemlowercase].includes('Organization'))
                {
                    organization += item.text + ' ';
                }
                
                if (tags[idx][itemlowercase].includes('ProperNoun'))
                {
                    propernoun += item.text + ' ';
                }
                
                if (itemlowercase == 'amp' && item.pre == '&' && item.post.trim() == ';')
                {
                    if (organization != '')
                        organization += '&amp; ';
                    else if (propernoun != '')
                        propernoun += '&amp; ';
                }
            }
            idx ++;
            itemlowercase_prev = itemlowercase;
        });
        
        if (organization != '' && organization.length > !propernoun.length && places[organization.trim().toLowerCase()])
            text = organization.trim();
        else if (propernoun != '' && !places[propernoun.trim().toLowerCase()])
            text = propernoun.trim();
        else if (stock != '')
            text = stock.trim();

        if (propernoun != '')
            stockDict[propernoun.trim()] = 'ProperNoun';
            
        if (organization != '')
            stockDict[organization.trim()] = 'Organization';
            
        //if (stock != '')    
        //    stockDict[stock.trim()] = '';
    }
}

if (text > 0 && isNumeric(text) && text.length <= 5 && exch != 'tyo')
{
    if (text.length == 1)
        text = "000" + text;
    else if (text.length == 2)
        text = "00" + text;
    else if (text.length == 3)
        text = "0" + text;        
        
    text = text + ".HK";  
}
else if (text.toLowerCase().endsWith('.hk'))
{
    var tmpcode = text.substring(0, text.length - 3);
    if (isNumeric(tmpcode))
    {
        if (tmpcode.length == 1)
            text = "000" + text;
        else if (tmpcode.length == 2)
            text = "00" + text;
        else if (tmpcode.length == 3)
            text = "0" + text;  
    }
}

if (fulltext == text)
    fulltext = '';

$.ajax({
    url : "db_chatbot.php?search=" + text.toUpperCase().replace(exch.toUpperCase(), "").replace(pl, "").trim() + "&fullsearch=" + fulltext + "&exchange=" + exch,
    dataType: "json",
    success : function(result){
        var stockcode = '';
        var errMsg = '360MiQ AI has gone AWOL! Please try again later.';
        if (result.length == 1)
        {
            chatbot(result);
            
            var payLoad = fulltext; 
            if (fulltext == '')
                payLoad = text;
                
            setTimeout(() => {
                $.ajax({
                    url : "LLM_request.php",
                    dataType: "json",
                    type: 'post',
                    data: { payload: payLoad, isSearch: '', stockdata: JSON.stringify(result[0])},
                    success: function(data0) {
                        var timeout = 0;
                        if (data0['result'] === null || data0['result'] == errMsg)
                            timeout = 500;
                            
                        setTimeout(() => {
                            setResponse(linkifyStock(data0['result'], result[0].code, result[0].exchange, true), result[0].code, result[0].exchange, botLoadingDelay + botReplyDelay);
                            
                            aiMessage(loader, true, botLoadingDelay);
                        }, timeout);
                    }
                });
            }, 500);
        }
        else
        {
            var payLoad = fulltext; 
            if (fulltext == '')
                payLoad = text;
            $.ajax({
                url : "LLM_request.php",
                dataType: "json",
                type: 'post',
                data: { payload: payLoad, isSearch: 1 },
                success: function(data) {
                    stockcode = data['code'];
                     
                    if (stockcode !== null)
                    {
                        $.ajax({
                            url : "db_chatbot.php?search=" + stockcode + "&fullsearch=&exchange=",
                            dataType: "json",
                            success : function(result1){
                                if (result1.length == 1)
                                {
                                    chatbot(result1);
                                    
                                    setTimeout(() => {
                                        $.ajax({
                                            url : "LLM_request.php",
                                            dataType: "json",
                                            type: 'post',
                                            data: { payload: payLoad, isSearch: '', stockdata: JSON.stringify(result1[0])},
                                            success: function(data1) {
                                                var timeout = 0;
                                                if (data1['result'] === null || data1['result'] == errMsg)
                                                    timeout = 500;
                                                    
                                                setTimeout(() => {
                                                    setResponse(linkifyStock(data1['result'], stockcode, result1[0].exchange, true), stockcode, result1[0].exchange, botLoadingDelay + botReplyDelay);
                                                    
                                                    aiMessage(loader, true, botLoadingDelay);
                                                }, timeout);
                                            }
                                        });
                                    }, 500);
                                }
                                else
                                {
                                    setResponse(errorMessage, '', '', botLoadingDelay + botReplyDelay);
                                    resetInputField();
                                }
                            }
                        });
                    }
                }
            });
        }

        aiMessage(loader, true, botLoadingDelay);
    }
});
//aiMessage(loader, true, botLoadingDelay);
};

function chatbot(result, returnTitle = false)
{
    const weekday = ["Sun","Mon","Tue","Wed","Thur","Fri","Sat"];
    
    id = result[0].code + '_' + count;

    stockchatDict[id] = {
        name_en: result[0].name_en,
        name_tc: result[0].name_tc,
        exchange: result[0].exchange,
        code: result[0].code,
        tradedate: result[0].tradedate,
        utcDate: new Date(Date.UTC(
        ...result[0].tradedate.split('-').map((v, i) => i === 1 ? parseInt(v) - 1 : parseInt(v))
        )),
        get dayofweek() {
            return this.utcDate.getUTCDay();
        },
        bull_bear_not_d: result[0].bull_bear_not_d,
        bull_bear_not_w: result[0].bull_bear_not_w,
        close: result[0].close,
        close_previous: result[0].close_previous,
        high20: result[0].high20,
        high50: result[0].high50,
        high250: result[0].high250,
        highYTD: result[0].highYTD,
        low20: result[0].low20,
        low50: result[0].low50,
        low250: result[0].low250,
        lowYTD: result[0].lowYTD,
        ma20: result[0].ma20,
        ma50: result[0].ma50,
        ma_long: result[0].ma_long,
        ma20_previous: result[0].ma20_previous,
        ma50_previous: result[0].ma50_previous,
        ma_long_previous: result[0].ma_long_previous,
        rsi14d: result[0].rsi14d,
        rsi14d_previous: result[0].rsi14d_previous,
        rsi14w: result[0].rsi14w,
        rsi14w_previous: result[0].rsi14w_previous,
        midline: result[0].midline,
        midline_previous: result[0].midline_previous,
        SE: result[0].SE,
        slope: result[0].slope,
        trendvalue: result[0].trendvalue,
        polarscoreFA: result[0].polarscoreFA,
        closeSeries: result[0].closeSeries
    };
    
    var stock = stockchatDict[id];
    var closeSeries = stock.closeSeries;
    
    var longperiod = "200";
    if (stock.exchange == "HKEX" || stock.exchange == "SHSE" || stock.exchange == "SZSE")
    {
        longperiod = "250";
    }
    
    stock.longperiod = longperiod;
    result[0].longperiod = longperiod;
    
    var change = '- ';
    var changestr = '';
    var hideStockPriceValues = window.PriceDisplayPolicy && !PriceDisplayPolicy.showStockIndexPrices();
    if (stock.close !== null)
    {
        if (stock.close_previous !== null && stock.close_previous > 0)
        {
            change = ((stock.close / stock.close_previous - 1) * 100).toFixed(2);
            changestr = "<font style='font-weight:550'>" + stock.close + "</font>" + (change > 0? " <font style='font-weight:550' color='green'>+" + change + "%</font>" : (change < 0? " <font " + (isBlogPath ? "" : "style='font-weight:550'") + " color='red'><b>" + change + "%</b></font>" : " <font " + (isBlogPath ? "" : "style='font-weight:550'") + " color='grey'><b>±0%</b></font>"));
        }
        else
            changestr = "<font style='font-weight:550'>" + stock.close + "</font> <font style='font-weight:550' color='darkgrey'><b>- %</b></font>";

        if (hideStockPriceValues)
            changestr = stock.close_previous !== null && stock.close_previous > 0 ? changestr.replace(/^<font[^>]*>.*?<\/font>\s*/, '') : '';
    
        closeSeries = (stock.closeSeries.endsWith(',' + stock.close) ? stock.closeSeries : stock.closeSeries + ',' + stock.close);
        result[0].closeSeries = closeSeries;
        
        var sparkline = '<div id = "chatchart' + count + '" style="width: 100%; margin:0px"><span data-sparklinechat'+ count + '="' + closeSeries +'"></span></div>';
        //if (exchange != 'LSE' && exchange != 'TYO' && exchange != 'SHSE' && exchange != 'SZSE')
        sparkline = '<div style="margin: 10px 0 0 10px"><a href="/stockinfo?code=' + stock.code + '" target="_blank">' + sparkline + '</a></div>';
            
        var exchangeLink = '<a href="/market?data=' + stock.exchange + '" target="_blank" style="color: revert;text-decoration:revert" >' + stock.exchange + '</a><br>';
        if (stock.exchange == 'NYSE Arca')
            exchangeLink = stock.exchange + '<br>';
            
        var YTD_result = YTD(stock.close, stock.highYTD, stock.high250, stock.high50, stock.high20, stock.lowYTD, stock.low250, stock.low50, stock.low20, stock.tradedate, stock.exchange, id);
        var FA_result = FA(stock.polarscoreFA, stock.exchange, id);
        var trendgauge2txt_result = trendgauge2txt(stock.trendvalue, stock.exchange, id);
        var trendchannel_result = trendchannel(stock.close, stock.close_previous, stock.midline, stock.midline_previous, stock.SE, stock.slope, stock.exchange, id);
        var RSIdivergence_result = RSIdivergence(stock.bull_bear_not_d, stock.bull_bear_not_w, stock.exchange, id);
        var RSId_result = RSI("Daily", stock.rsi14d, stock.rsi14d_previous, stock.exchange, id);
        var RSIw_result = RSI("Weekly", stock.rsi14w, stock.rsi14w_previous, stock.exchange, id);
        var MAxMA_result = MAxMA(stock.ma50, stock.ma50_previous, stock.ma_long, stock.ma_long_previous, 50, stock.longperiod, stock.exchange, id);
        var MA1_result = MA(stock.close, stock.close_previous, stock.ma20, stock.ma20_previous, "20", stock.exchange, 1, id);
        var MA2_result = MA(stock.close, stock.close_previous, stock.ma50, stock.ma50_previous, "50", stock.exchange, 2, id);
        var MA3_result = MA(stock.close, stock.close_previous, stock.ma_long, stock.ma_long_previous, stock.longperiod, stock.exchange, 3, id);
        
        var title = stock.code + dot + (stock.name_tc == null? '' : stock.name_tc + dot) + stock.name_en + dot + exchangeLink + '<nobr>' + stock.tradedate + '</nobr> ' + weekday[stock.dayofweek] + ": " + changestr + '<div class="checkbox-group mb-3"><ol class="screenerlist" style="list-style-type:none;padding-left:10px">' + YTD_result[0] + FA_result[0] + trendgauge2txt_result[0] + trendchannel_result[0] + RSIdivergence_result[0] + RSId_result[0] + RSIw_result[0] + MAxMA_result[0] + MA1_result[0] + MA2_result[0] + MA3_result[0] + '</ol><div class="btn-group mt-2" role="group"><button class="btn btn-secondary btn-sm no-chatbot-style" onclick="checkAll(this, true)">Check All</button><button class="btn btn-secondary btn-sm no-chatbot-style" onclick="checkAll(this, false)">Clear All</button><button id="'+id+'"class="btn btn-warning btn-sm no-chatbot-style text-dark" style="background-color: #ffc107; border-color: #ffc107;color: #212529;" onclick="doSearch(this)" title="Search stocks based on selected criteria">Search</button></div>' + (returnTitle ? '' : sparkline);
        
        count++
        
        if (returnTitle)
        {
            return title.replace('</ol>', '</ol><p>').replace('<div class="checkbox-group mb-3">', '<p><div class="checkbox-group mb-3">').replace('screenerlist', 'screenerlist2').replace('checkbox-group', 'checkbox-group2').replace('btn-group', 'btn-group2').replace("checkAll(this, true)", "checkAll(this, true, '.checkbox-group2')").replace("checkAll(this, false)", "checkAll(this, false, '.checkbox-group2')");
        }
            
        setResponse(title, stock.code, stock.exchange, botLoadingDelay + botReplyDelay);
        
        
    }
}
//# sourceURL=pen.js
function FA(FAscore, exchange, id) {
    var result = '';
    var this_search_filter = '';
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        
        if (FAscore == 1)
        {
            result = screenerATag + exchange + '&polar_fa=1" target="_blank" ' + linkstyle() + '>Very <nobr>weak</nobr> <nobr>fundamental</nobr></a>';
            this_search_filter = '&polar_fa=1';
        }
        else if (FAscore == 2)
        {
            result = screenerATag + exchange + '&polar_fa=2" target="_blank" ' + linkstyle() + '>Weak <nobr>fundamental</nobr></a>';
            this_search_filter = '&polar_fa=2';
        }
        else if (FAscore == 3)
        {
            result = screenerATag + exchange + '&polar_fa=3" target="_blank" ' + linkstyle() + '>Average <nobr>fundamental</nobr></a>';
            this_search_filter = '&polar_fa=3';
        }
        else if (FAscore == 4)
        {
            result = screenerATag + exchange + '&polar_fa=4" target="_blank" ' + linkstyle() + '>Strong <nobr>fundamental</nobr></a>';
            this_search_filter = '&polar_fa=4';
        }
        else if (FAscore == 5)
        {
            result = screenerATag + exchange + '&polar_fa=5" target="_blank" ' + linkstyle() + '>Very <nobr>strong</nobr> <nobr>fundamental</nobr></a>';
            this_search_filter = '&polar_fa=5';
        }
        else
            return ['', ''];
    }
    else
    {
        if (FAscore == 1)
            result = 'Very weak fundamental.';
        else if (FAscore == 2)
            result = 'Weak fundamental.';
        else if (FAscore == 3)
            result = 'Average fundamental.';
        else if (FAscore == 4)
            result = 'Strong fundamental.';
        else if (FAscore == 5)
            result = 'Very strong fundamental.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="fa_'+id+'" class="bulk-checkbox" type="checkbox" name="fa" checked>' + result + '</label></li>', this_search_filter];
}

function MAxMA(ma1, ma1_previous, ma2, ma2_previous, period1, period2, exchange, id) {
    var result = '';
    var this_search_filter = '';

    if (checkExchanges(exchange))
    {    
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
        
        if (ma1 < ma2 && ma1_previous >= ma2_previous)
        {
            result = screenerATag + exchange + '&ma'+period1+'=MA'+period1+'%20cross-under%20MA'+period2+'" ' + linkstyle() + '>MA' + period1 + ' <nobr>crossed</nobr> <nobr>under</nobr> <nobr>MA' + period2 + '</nobr></a>';
            this_search_filter = '&ma'+period1+'=MA'+period1+'%20cross-under%20MA'+period2;
        }
        else if (ma1 > ma2 && ma1_previous <= ma2_previous)
        {
            result = screenerATag + exchange + '&ma'+period1+'=MA'+period1+'%20cross-over%20MA'+period2+'" ' + linkstyle() + '>MA' + period1 + ' <nobr>crossed</nobr> <nobr>over</nobr> <nobr>MA' + period2 + '</nobr></a>';
            this_search_filter = '&ma'+period1+'=MA'+period1+'%20cross-over%20MA'+period2;
        }
        else
            return ['', ''];
    }
    else
    {
        if (ma1 < ma2 && ma1_previous >= ma2_previous)
            result = 'MA' + period1 + ' crossed under MA' + period2 + '.';
        else if (ma1 > ma2 && ma1_previous <= ma2_previous)
            result = 'MA' + period1 + ' crossed over MA' + period2 + '.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="maxma_'+id+'" class="bulk-checkbox" type="checkbox" name="maxma" checked>' + result + '</label></li>', this_search_filter];
}

function MA(close, close_previous, ma, ma_previous, period, exchange, idx, id) {
    var result = '';
    var this_search_filter = '';
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
        
        if (close < ma && close_previous >= ma_previous)
        {
            result = screenerATag + exchange + '&ma'+period+'=Close%20cross-under%20MA'+period +'" ' + linkstyle() + '>Price <nobr>crossed</nobr> <nobr>under</nobr> <nobr>MA' + period + '</nobr></a>';
            this_search_filter = '&ma'+period+'=Close%20cross-under%20MA'+period;
        }
        else if (close > ma && close_previous <= ma_previous)
        {
            result = screenerATag + exchange + '&ma'+period+'=Close%20cross-over%20MA'+period +'" ' + linkstyle() + '>Price <nobr>crossed</nobr> <nobr>over</nobr> <nobr>MA' + period + '</nobr></a>';
            this_search_filter = '&ma'+period+'=Close%20cross-over%20MA'+period;
        }
        else if (close > ma)
        {
            result = screenerATag + exchange + '&ma'+period+'=Close%20>%20MA'+period +'" ' + linkstyle() + '>Price > <nobr>MA' + period + '</nobr></a>';
            this_search_filter = '&ma'+period+'=Close%20>%20MA'+period;
        }
        else if (close < ma)
        {
            result = screenerATag + exchange + '&ma'+period+'=Close%20<%20MA'+period +'" ' + linkstyle() + '>Price < <nobr>MA' + period + '</nobr></a>';
            this_search_filter = '&ma'+period+'=Close%20<%20MA'+period;
        }
        else
            return ['', ''];
    }
    else
    {
        if (close < ma && close_previous >= ma_previous)
            result = 'Price crossed under MA' + period + '.';
        else if (close > ma && close_previous <= ma_previous)
            result = 'Price crossed over MA' + period + '.';
        else if (close > ma)
            result = 'Price > MA' + period + '.';
        else if (close < ma)
            result = 'Price < MA' + period + '.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="ma'+idx+'_'+id+'" class="bulk-checkbox" type="checkbox" name="ma' + idx + '" checked>' + result + '</label></li>', this_search_filter];
}

function trendgauge2txt(trendvalue, exchange, id) {
    var result = '';
    var this_search_filter = '';
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
        
        
        
        var aTagLabel = '';
        
        if (trendvalue > 22.5 && trendvalue <= 67.5)
        {
            aTagLabel = '><nobr>Trending</nobr> <nobr>strongly</nobr> <nobr>up</nobr></a>';
        }
        else if (trendvalue > 67.5 && trendvalue <= 112.5) 
        {
            aTagLabel = '><nobr>Overbought</nobr></a>';
        }
        else if (trendvalue > 112.5 && trendvalue <= 157.5) 
        {
            aTagLabel = '>In <nobr>correction</nobr></a>';
        }
        else if (trendvalue > 157.5 && trendvalue <= 202.5) 
        {
            aTagLabel = '><nobr>Trending</nobr> <nobr>down</nobr></a>';
        }
        else if (trendvalue > 202.5 && trendvalue <= 247.5) 
        {
            aTagLabel = '><nobr>Trending</nobr> <nobr>strongly</nobr> <nobr>down</nobr></a>';
        }
        else if (trendvalue > 247.5 && trendvalue <= 292.5) 
        {
            aTagLabel = '><nobr>Oversold</nobr></a>';
        }
        else if (trendvalue > 292.5 && trendvalue <= 337.5) 
        {
            aTagLabel = '><nobr>Rebounding</nobr></a>';
        }
        else if ((trendvalue > 337.5 && trendvalue <= 360) ||
                (trendvalue >= 0 && trendvalue <= 22.5)) 
        {
            aTagLabel = '><nobr>Trending</nobr> <nobr>up</nobr></a>';
        }

            
        if (trendvalue > 270 && trendvalue <= 315)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Oversold%20-%20Rebound" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Oversold%20-%20Rebound';
        }
        else if (trendvalue > 315 && trendvalue <= 360)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Rebound%20-%20Up" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Rebound%20-%20Up';
        }
        else if (trendvalue >= 0 && trendvalue <= 45)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Up%20-%20Strong%20Up" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Up%20-%20Strong%20Up';
        }
        else if (trendvalue > 45 && trendvalue <= 90)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Strong%20Up%20-%20Overbought" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Strong%20Up%20-%20Overbought';
        }
        else if (trendvalue > 90 && trendvalue <= 135)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Overbought%20-%20Correction" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Overbought%20-%20Correction';
        }
        else if (trendvalue > 135 && trendvalue <= 180)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Correction%20-%20Down" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Correction%20-%20Down';
        }
        else if (trendvalue > 180 && trendvalue <= 225)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Down%20-%20Strong%20Down" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Down%20-%20Strong%20Down';
        }
        else if (trendvalue > 225 && trendvalue <= 270)
        {
            result = screenerATag + exchange + '&polar_trendgauge=Strong%20Down%20-%20Oversold" ' + linkstyle() + aTagLabel;
            this_search_filter = '&polar_trendgauge=Strong%20Down%20-%20Oversold';
        }
        else
            return ['', ''];		        
    }
    else
    {
        if (trendvalue > 22.5 && trendvalue <= 67.5) 
            result = 'Trending strongly up.';
        else if (trendvalue > 67.5 && trendvalue <= 112.5) 
            result = 'Overbought.';
        else if (trendvalue > 112.5 && trendvalue <= 157.5) 
            result = 'In correction.';
        else if (trendvalue > 157.5 && trendvalue <= 202.5) 
            result = 'Trending down.';
        else if (trendvalue > 202.5 && trendvalue <= 247.5) 
            result = 'Trending strongly down.';
        else if (trendvalue > 247.5 && trendvalue <= 292.5) 
            result = 'Oversold.';
        else if (trendvalue > 292.5 && trendvalue <= 337.5) 
            result = 'Rebounding.';
        else if ((trendvalue > 337.5 && trendvalue <= 360) ||
                (trendvalue >= 0 && trendvalue <= 22.5)) 
            result = 'Trending up.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="tg_'+id+'" class="bulk-checkbox" type="checkbox" style="margin-right: 8px;" name="tg" checked>' + result + '</label></li>', this_search_filter];
}

function YTD(close, highYTD, high250, high50, high20, lowYTD, low250, low50, low20, tradedate, exchange, id) {
    var month = new Date(tradedate).getMonth();
    var result = '';
    var this_search_filter = '';
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
        
        if (close == high250) 
        {
            result = screenerATag + exchange + '&highlow=250-day%20High" ' + linkstyle() + '>A <nobr>250-day</nobr> <nobr>high</nobr></a>';
            this_search_filter = '&highlow=250-day%20High';
        }
        else if (close == highYTD && month != 0) 
        {
            result = screenerATag + exchange + '&highlow=Year-To-Date%20High" ' + linkstyle() + '>A <nobr>YTD</nobr> <nobr>high</nobr></a>';
            this_search_filter = '&highlow=Year-To-Date%20High';
        }
        else if (close == high50) 
        {
            result = screenerATag + exchange + '&highlow=50-day%20High" ' + linkstyle() + '>A <nobr>50-day</nobr> <nobr>high</nobr></a>';
            this_search_filter = '&highlow=50-day%20High';
        }
        else if (close == high20) 
        {
            result = screenerATag + exchange + '&highlow=20-day%20High" ' + linkstyle() + '>A <nobr>20-day</nobr> <nobr>high</nobr></a>';
            this_search_filter = '&highlow=20-day%20High';
        }
        else if (close == low250) 
        {
            result = screenerATag + exchange + '&highlow=250-day%20Low" ' + linkstyle() + '>A <nobr>250-day</nobr> <nobr>low</nobr></a>';
            this_search_filter = '&highlow=250-day%20Low';
        }
        else if (close == lowYTD && month != 0) 
        {
            result = screenerATag + exchange + '&highlow=Year-To-Date%20Low" ' + linkstyle() + '>A <nobr>YTD</nobr> <nobr>low</nobr></a>';
            this_search_filter = '&highlow=Year-To-Date%20Low';
        }
        else if (close == low50) 
        {
            result = screenerATag + exchange + '&highlow=50-day%20Low" ' + linkstyle() + '>A <nobr>50-day</nobr> <nobr>low</nobr></a>';
            this_search_filter = '&highlow=50-day%20Low';
        }
        else if (close == low20) 
        {
            result = screenerATag + exchange + '&highlow=20-day%20Low" ' + linkstyle() + '>A <nobr>20-day</nobr> <nobr>low</nobr></a>';
            this_search_filter = '&highlow=20-day%20Low';
        }
        else
            return ['', ''];
    }
    else
    {
        if (close == high250) 
            result = 'A 250-day high.';
        else if (close == highYTD && month != 0) 
            result = 'A YTD high.';
        else if (close == high50) 
            result = 'A 50-day high.';
        else if (close == high20) 
            result = 'A 20-day high.';
        else if (close == low250) 
            result = 'A 250-day low.';
        else if (close == lowYTD && month != 0) 
            result = 'A YTD low.';
        else if (close == low50) 
            result = 'A 50-day low.';
        else if (close == low20) 
            result = 'A 20-day low.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="ytd_'+id+'" class="bulk-checkbox" type="checkbox" name="ytd" checked>' + result + '</label></li>', this_search_filter];
}

function RSI(timeframe, value, value_previous, exchange, id) {
    var result = '';
    var this_search_filter = '';
            
    if (checkExchanges(exchange))
    {
        var tf = 'w';
        if (timeframe == "Daily")
            tf = 'd';
            
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
            
        if (value < 30 && value_previous >= 30)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-under%2030" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>under</nobr> <nobr>30</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-under%2030';
        }
        else if (value < 50 && value_previous >= 50)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-under%2050" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>under</nobr> <nobr>50</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-under%2050';
        }
        else if (value < 70 && value_previous >= 70)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-under%2070" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>under</nobr> <nobr>70</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-under%2070';
        }
        else if (value > 30 && value_previous <= 30)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-over%2030" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>over</nobr> <nobr>30</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-over%2030';
        }
        else if (value > 50 && value_previous <= 50)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-over%2050" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>over</nobr> <nobr>50</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-over%2050';
        }
        else if (value > 70 && value_previous <= 70)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=cross-over%2070" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> <nobr>crossed</nobr> <nobr>over</nobr> <nobr>70</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=cross-over%2070';
        }
        else if (value > 70)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=>%2070" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> > <nobr>70</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=>%2070';
        }
        else if (value < 30)
        {
            result = screenerATag + exchange + '&rsi14' + tf + '=<%2030" ' + linkstyle() + '>' + timeframe + ' <nobr>RSI</nobr> < <nobr>30</nobr></a>';
            this_search_filter = '&rsi14' + tf + '=<%2030';
        }
        else
            return ['', ''];
    }
    else
    {
        if (value < 30 && value_previous >= 30)
            result = timeframe + ' RSI crossed under 30.';
        else if (value < 50 && value_previous >= 50)
            result = timeframe + ' RSI crossed under 50.';
        else if (value < 70 && value_previous >= 70)
            result = timeframe + ' RSI crossed under 70.';
        else if (value > 30 && value_previous <= 30)
            result = timeframe + ' RSI crossed over 30.';
        else if (value > 50 && value_previous <= 50)
            result = timeframe + ' RSI crossed over 50.';
        else if (value > 70 && value_previous <= 70)
            result = timeframe + ' RSI crossed over 70.';
        else if (value > 70)
            result = timeframe + ' RSI > 70.';
        else if (value < 30)
            result = timeframe + ' RSI < 30.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="rsi'+timeframe+'_'+id+'" class="bulk-checkbox" type="checkbox" name="rsi'+timeframe+'" checked>' + result + '</label></li>', this_search_filter];
}
function trendchannel(close, close_previous, midline, midline_previous, SE, slope, exchange, id)
{
    var alt = "";
    var trend = "", screenerTrend = "";
    var pos = "";
    var this_search_filter = '';
    
    var slope_threshold = 0.00065;
    if (slope > slope_threshold)
    {
        trend = "up-";
        screenerTrend = "Up";
    }
    else if (slope < -slope_threshold)
    {
        trend = "down-";
        screenerTrend = "Down";
    }
    else if (slope >= -slope_threshold && slope <= slope_threshold)
    {
        trend = "sideways ";
        screenerTrend = "Sideways";
    }
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
        
        if (close > (midline + SE) && close_previous < (midline_previous + SE))
        {
            pos = "breakout";
            alt = screenerATag + exchange + '&channel_pos=break-out%20Top&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Break-out of <nobr>the</nobr> <nobr>' + trend + '</nobr><nobr>channel</nobr> <nobr>top</nobr></a>';
            this_search_filter = '&channel_pos=break-out%20Top&channel_trend=' + screenerTrend;
        }
        else if (close < (midline - SE) && close_previous > (midline_previous - SE))
        {
            pos = "breakdown";
            alt = screenerATag + exchange + '&channel_pos=break-down%20Bottom&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Break-down of <nobr>the</nobr> <nobr>' + trend + '</nobr><nobr>channel</nobr> <nobr>bottom</nobr></a>';
            this_search_filter = '&channel_pos=break-down%20Bottom&channel_trend=' + screenerTrend;
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
            alt = screenerATag + exchange + '&channel_pos=near%20Top&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Near the <nobr>' + trend + '</nobr><nobr>channel</nobr> <nobr>' + pos + '</nobr></a>';
            this_search_filter = '&channel_pos=near%20Top&channel_trend=' + screenerTrend;
        }
        else if (close > (midline - 0.2 * SE) && close < (midline + 0.2 * SE))
        {
            pos = "middle";
            alt = screenerATag + exchange + '&channel_pos=near%20Middle&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Near the <nobr>' + pos + '</nobr> of <nobr>the</nobr> <nobr>' + trend + '</nobr><nobr>channel</nobr></a>';
            this_search_filter = '&channel_pos=near%20Middle&channel_trend=' + screenerTrend;
        }
        else if (close > (midline - 1.2 * SE) && close < (midline - 0.8 * SE))
        {
            pos = "bottom";
            alt = screenerATag + exchange + '&channel_pos=near%20Bottom&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Near the <nobr>' + trend + '</nobr><nobr>channel</nobr> <nobr>' + pos + '</nobr></a>';
            this_search_filter = '&channel_pos=near%20Bottom&channel_trend=' + screenerTrend;
        }
        else if (close > (midline + SE))
        {
            pos = "above";
            alt = screenerATag + exchange + '&channel_pos=above%20Top&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Above the <nobr>' + trend + '</nobr>channel <nobr>top</nobr></a>';
            this_search_filter = '&channel_pos=above%20Top&channel_trend=' + screenerTrend;
        }
        else if (close < (midline - SE))
        {
            pos = "below";
            alt = screenerATag + exchange + '&channel_pos=below%20Bottom&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>Below the <nobr>' + trend + '</nobr>channel <nobr>bottom</nobr></a>';
            this_search_filter = '&channel_pos=below%20Bottom&channel_trend=' + screenerTrend;
        }
        else if (close > midline && close <= (midline + SE))
        {
            pos = "upper";
            alt = screenerATag + exchange + '&channel_pos=Upper%20Half&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>In the <nobr>' + pos + '</nobr> <nobr>half</nobr> of <nobr>the</nobr> <nobr>' +  trend + '</nobr><nobr>channel</nobr></a>';
            this_search_filter = '&channel_pos=Upper%20Half&channel_trend=' + screenerTrend;
        }
        else if (close < midline && close >= (midline - SE))
        {
            pos = "lower";
            alt = screenerATag + exchange + '&channel_pos=Lower%20Half&channel_trend=' + screenerTrend + '" ' + linkstyle() + '>In the <nobr>' + pos + '</nobr> <nobr>half</nobr> of <nobr>the</nobr> <nobr>' +  trend + '</nobr><nobr>channel</nobr></a>';
            this_search_filter = '&channel_pos=Lower%20Half&channel_trend=' + screenerTrend;
        }
    }
    else
    {
        if (close > (midline + SE) && close_previous < (midline_previous + SE))
        {
            pos = "breakout";
            alt = "Break-out of the " + trend + "channel top.";
        }
        else if (close < (midline - SE) && close_previous > (midline_previous - SE))
        {
            pos = "breakdown";
            alt = "Break-down of the " + trend + "channel bottom.";
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
            alt = "Near the " + trend + "channel " + pos + ".";
        }
        else if (close > (midline - 0.2 * SE) && close < (midline + 0.2 * SE))
        {
            pos = "middle";
            alt = "Near the " + pos + " of the " + trend + "channel.";
        }
        else if (close > (midline - 1.2 * SE) && close < (midline - 0.8 * SE))
        {
            pos = "bottom";
            alt = "Near the " + trend + "channel " + pos+ ".";
        }
        else if (close > (midline + SE))
        {
            pos = "above";
            alt = "Above the " + trend + "channel top.";
        }
        else if (close < (midline - SE))
        {
            pos = "below";
            alt = "Below the " + trend + "channel bottom.";
        }
        else if (close > midline && close <= (midline + SE))
        {
            pos = "upper";
            alt = "In the " + pos + " half of the " + trend + "channel.";
        }
        else if (close < midline && close >= (midline - SE))
        {
            pos = "lower";
            alt = "In the " + pos + " half of the " + trend + "channel.";
        }
    }
    
    if (trend === "" || pos === "")
        return ['', ''];
    else
        return ['<li><label class="checkbox-label"><input id="tc_'+id+'" class="bulk-checkbox" type="checkbox" name="tc" checked>' + alt + '</label></li>', this_search_filter];
}

function RSIdivergence(rsid, rsiw, exchange, id) {
    var result = '';
    var this_search_filter = '';
    
    if (checkExchanges(exchange))
    {
        if (exchange == 'NYSE' || exchange == 'Nasdaq')
            exchange = "NYSE%20%2B%20NASDAQ";
        else if (exchange == 'NYSE Arca')
            exchange = "NYSEARCA";
            
        if (rsid == 1 && rsiw == 1)
        {
            result = screenerATag + exchange + '&rsi14d=Bullish%20Divergence&rsi14w=Bullish%20Divergence" ' + linkstyle() + '><nobr>Bullishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>and</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bullish%20Divergence&rsi14w=Bullish%20Divergence';
        }
        else if (rsid == -1 && rsiw == -1)
        {
            result = screenerATag + exchange + '&rsi14d=Bearish%20Divergence&rsi14w=Bearish%20Divergence" ' + linkstyle() + '><nobr>Bearishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>and</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bearish%20Divergence&rsi14w=Bearish%20Divergence';
        }
        else if (rsid == -1 && rsiw == 1)
        {
            result = screenerATag + exchange + '&rsi14d=Bearish%20Divergence&rsi14w=Bullish%20Divergence" ' + linkstyle() + '><nobr>Bearishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>but</nobr> <nobr>bullishly</nobr> <nobr>diverges</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bearish%20Divergence&rsi14w=Bullish%20Divergence';
        }
        else if (rsid == 1 && rsiw == -1)
        {
            result = screenerATag + exchange + '&rsi14d=Bullish%20Divergence&rsi14w=Bearish%20Divergence" ' + linkstyle() + '><nobr>Bullishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>but</nobr> <nobr>bearishly</nobr> <nobr>diverges</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bullish%20Divergence&rsi14w=Bearish%20Divergence';
        }
        else if (rsid == 1)
        {
            result = screenerATag + exchange + '&rsi14d=Bullish%20Divergence" ' + linkstyle() + '><nobr>Bullishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bullish%20Divergence';
        }
        else if (rsiw == 1)
        {
            result = screenerATag + exchange + '&rsi14w=Bullish%20Divergence" ' + linkstyle() + '><nobr>Bullishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14w=Bullish%20Divergence';
        }
        else if (rsid == -1)
        {
            result = screenerATag + exchange + '&rsi14d=Bearish%20Divergence" ' + linkstyle() + '><nobr>Bearishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>daily</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14d=Bearish%20Divergence';
        }
        else if (rsiw == -1)
        {
            result = screenerATag + exchange + '&rsi14w=Bearish%20Divergence" ' + linkstyle() + '><nobr>Bearishly</nobr> <nobr>diverges</nobr> <nobr>with</nobr> <nobr>weekly</nobr> <nobr>RSI</nobr></a>';
            this_search_filter = '&rsi14w=Bearish%20Divergence';
        }
        else
            return ['', ''];
    }
    else
    {
        if (rsid == 1 && rsiw == 1)
            result = ' Bullishly diverges with daily and weekly RSI.';
        else if (rsid == -1 && rsiw == -1)
            result = ' Bearishly diverges with daily and weekly RSI.';
        else if (rsid == -1 && rsiw == 1)
            result = ' Bearishly diverges with daily but bullishly diverges weekly RSI.';
        else if (rsid == 1 && rsiw == -1)
            result = ' Bullishly diverges with daily but bearishly diverges weekly RSI.';
        else if (rsid == 1)
            result = ' Bullishly diverges with daily RSI.';
        else if (rsiw == 1)
            result = ' Bullishly diverges with weekly RSI.';
        else if (rsid == -1)
            result = ' Bearishly diverges with daily RSI.';
        else if (rsiw == -1)
            result = ' Bearishly diverges with weekly RSI.';
        else
            return '';
    }
        
    return ['<li><label class="checkbox-label"><input id="rsidiv_'+id+'" class="bulk-checkbox" type="checkbox" name="rsidiv" checked>' + result + '</label></li>', this_search_filter];
}

function isNumeric(value) {
    return /^-?\d+$/.test(value);
}

function hasWhiteSpace(s) {
  return /\s/g.test(s);
}

function linkstyle() {
    return 'target="_blank" style="color:revert;text-decoration:revert;"';
}

function checkAll(btn, checked, checkboxgroup = '.checkbox-group') {
    const group = btn.closest(checkboxgroup);
    const checkboxes = group.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
    if (checkboxgroup == '.checkbox-group')
        saveChatState();
}

function doSearch(btn)
{
    var id = btn.id;
    var stock = stockchatDict[id];
    var exchange = stock.exchange;
    if (exchange == 'NYSE' || exchange == 'Nasdaq')
        exchange = "NYSE%20%2B%20NASDAQ";
            
    var YTD_result = YTD(stock.close, stock.highYTD, stock.high250, stock.high50, stock.high20, stock.lowYTD, stock.low250, stock.low50, stock.low20, stock.tradedate, stock.exchange, id);
    var FA_result = FA(stock.polarscoreFA, stock.exchange, id);
    var trendgauge2txt_result = trendgauge2txt(stock.trendvalue, stock.exchange, id);
    var trendchannel_result = trendchannel(stock.close, stock.close_previous, stock.midline, stock.midline_previous, stock.SE, stock.slope, stock.exchange, id);
    var RSIdivergence_result = RSIdivergence(stock.bull_bear_not_d, stock.bull_bear_not_w, stock.exchange, id);
    var RSId_result = RSI("Daily", stock.rsi14d, stock.rsi14d_previous, stock.exchange, id);
    var RSIw_result = RSI("Weekly", stock.rsi14w, stock.rsi14w_previous, stock.exchange, id);
    var MAxMA_result = MAxMA(stock.ma50, stock.ma50_previous, stock.ma_long, stock.ma_long_previous, 50, stock.longperiod, stock.exchange, id);
    var MA1_result = MA(stock.close, stock.close_previous, stock.ma20, stock.ma20_previous, "20", stock.exchange, 1, id);
    var MA2_result = MA(stock.close, stock.close_previous, stock.ma50, stock.ma50_previous, "50", stock.exchange, 2, id);
    var MA3_result = MA(stock.close, stock.close_previous, stock.ma_long, stock.ma_long_previous, stock.longperiod, stock.exchange, 3, id);
    
    const ytd = document.querySelector('.bulk-checkbox[id="ytd_'+btn.id+'"]');
    const fa = document.querySelector('.bulk-checkbox[id="fa_'+btn.id+'"]');
    const tg = document.querySelector('.bulk-checkbox[id="tg_'+btn.id+'"]');
    const tc = document.querySelector('.bulk-checkbox[id="tc_'+btn.id+'"]');
    const rsidiv = document.querySelector('.bulk-checkbox[id="rsidiv_'+btn.id+'"]');
    const rsidaily = document.querySelector('.bulk-checkbox[id="rsiDaily_'+btn.id+'"]');
    const rsiweeky = document.querySelector('.bulk-checkbox[id="rsiWeeky_'+btn.id+'"]');
    const maxma = document.querySelector('.bulk-checkbox[id="maxma_'+btn.id+'"]');
    const ma1 = document.querySelector('.bulk-checkbox[id="ma1_'+btn.id+'"]');
    const ma2 = document.querySelector('.bulk-checkbox[id="ma2_'+btn.id+'"]');
    const ma3 = document.querySelector('.bulk-checkbox[id="ma3_'+btn.id+'"]');
    
    
    const isYTD = ytd ? ytd.checked : false;
    const isFA = fa ? fa.checked : false;
    const isTG = tg ? tg.checked : false;
    const isTC = tc ? tc.checked : false;
    const isRSIdiv = rsidiv ? rsidiv.checked : false;
    const isRsiDaily = rsidaily ? rsidaily.checked : false;
    const isRsiWeeky = rsiweeky ? rsiweeky.checked : false;
    const isMAxMA = maxma ? maxma.checked : false;
    const isMA1 = ma1 ? ma1.checked : false;
    const isMA2 = ma2 ? ma2.checked : false;
    const isMA3 = ma3 ? ma3.checked : false;
        
    var searchfilters = screenerURL + exchange + (isYTD ? YTD_result[1] : '') + (isFA ? FA_result[1] : '') + (isTG ? trendgauge2txt_result[1] : '') + (isTC ? trendchannel_result[1] : '') + (isRSIdiv ? RSIdivergence_result[1] : '') + (isRsiDaily ? RSId_result[1] : '') + (isRsiWeeky ? RSIw_result[1] : '') + (isMAxMA ? MAxMA_result[1] : '') + (isMA1 ? MA1_result[1] : '') + (isMA2 ? MA2_result[1] : '') + (isMA3 ? MA3_result[1] : '');
    window.open(searchfilters, '_blank'); // opens in a new tab
    $chatbotInput.focus();
}

function saveChatState() {
  checkboxStates = {}; // reset
  document.querySelectorAll('.bulk-checkbox[id]').forEach(cb => {
    checkboxStates[cb.id] = cb.checked;
  });

  // Save everything
  const messages = Array.from(document.querySelectorAll('.chatbot__messages li'))
    .filter(msg => msg.className.includes('is-ai') || msg.className.includes('is-user'))
    .slice(-40)
    .map(msg => msg.outerHTML);

  const state = {
    messages,
    stockchatDict,
    checkboxStates
  };

  localStorage.setItem('chatbotState', JSON.stringify(state));
}

function restoreChatState() {
  const savedState = localStorage.getItem('chatbotState');
  if (!savedState) return;

  try {
    const { messages, stockchatDict: savedDict, checkboxStates: savedCheckboxes } = JSON.parse(savedState);

    const messagesEl = document.querySelector('.chatbot__messages');
    if (messagesEl) messagesEl.innerHTML = messages.join('');;

    Object.assign(stockchatDict, savedDict);
    Object.assign(checkboxStates, savedCheckboxes);

    count = Object.keys(stockchatDict).length > 0 ? Object.keys(stockchatDict).length : 0;
    
    // Restore checkboxes
    Object.entries(checkboxStates).forEach(([id, checked]) => {
      const cb = document.querySelector(`.bulk-checkbox[id="${id}"]`);
      if (cb) cb.checked = checked;
    });

    scrollDown();
  } catch (e) {
    console.error('Failed to restore chat state:', e);
  }
}

function clearChatState() {
  try {
    localStorage.removeItem('chatbotState');
  } catch (e) {
    console.error('Error clearing message window:', e);
  }
}

document.addEventListener('change', function (event) {
  if (event.target && event.target.matches('.bulk-checkbox')) {
    saveChatState();
  }
});

// Creating 153 sparkline charts is quite fast in modern browsers, but IE8 and mobile
// can take some seconds, so we split the input into chunks and apply them in timeouts
// in order avoid locking up the browser process and allow interaction.
function doChunkChat($tds, fullLen, n, count) {
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
        stringdata = $td.data('sparklinechat' + (count - 1));
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
        
        Highcharts.setOptions({
        	lang: {
          	thousandsSep: ""
          }
        })
        chart = Highcharts.chart('chatchart' + (count - 1), {
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
            series: [{
                data: data,
                pointStart: 1,
                color: '#4CBAFD',
                fillColor: '#BCE4FD',
                fillOpacity: 1,
            }],
            tooltip: {
                headerFormat: '<span style="font-size: 10px">{point.x}:</span><br/>',
                pointFormat: '<b>{point.y}</b>'
            },
            chart: {
                chart,
                backgroundColor: null,
                borderWidth: 0,
                type: 'areaspline',
                margin: [3, 32, 18, 0],
                width: 200,
                height: 100,
                style: {
                    overflow: 'visible',
                    fontFamily: 'Montserrat'
                },
            },
            title: {
                text: null,
            },
            credits: {
                enabled: false
            },
            xAxis: {
                labels: {
                    enabled: false
                },
                title: {
                    text: '6 months',
                    style: {
                        color: (document.documentElement.getAttribute('data-theme')==='dark'?'#aaaaaa':'grey'),
                        opacity: 0.9,
                        fontSize: 16,
                    },
                    x: 10,
                    y: -4,
                },
                lineColor: (document.documentElement.getAttribute('data-theme')==='dark'?'#555':'#bbb'),
                startOnTick: false,
                endOnTick: false,
                tickPositions: []
            },
            yAxis: {
                min: Math.min.apply(this, data) * 0.98, // : Math.min(...data) * 0.98,
                max: Math.max.apply(this, data) * 1.02, // : Math.max(...data) * 1.02
                opposite: true,
                offset: 0,
                endOnTick: false,
                startOnTick: false,
                labels: {
                    enabled: true,
                    x: 1, 
                    style: {
                        color: (document.documentElement.getAttribute('data-theme')==='dark'?'#aaaaaa':'grey'),
                        opacity: 0.9,
                        fontSize: 12,
                    },
                    formatter: function() {
                        if ( this.value >= 10000 )
                            return Highcharts.numberFormat( this.value/1000, 0) + "K";  //  only switch if >= 10000
                        else if ( this.value >= 1000000 )
                            return Highcharts.numberFormat( this.value/1000000, 2) + "M";  //  only switch if >= 1000000
                        else if (this.value < 0.01)
                            return Highcharts.numberFormat(this.value,4);
                        else if (this.value < 0.1)
                            return Highcharts.numberFormat(this.value,3);
                        else if (this.value < 1)
                            return Highcharts.numberFormat(this.value,2);
                        else if (this.value < 10 || (this.axis.max - this.axis.min) < 5)
                            return Highcharts.numberFormat(this.value,1);
                        else
                            return Highcharts.numberFormat(this.value,0);
                    }
                },
                title: {
                    text: null
                },
                tickPositioner: function () {
                    var positions = [],
                        tick = Math.floor(this.dataMin),
                        increment = (this.dataMax - this.dataMin) / 2;
        
                    if (this.dataMax !== null && this.dataMin !== null) {
                        for (tick; tick - increment <= this.dataMax; tick += increment) {
                            positions.push(tick);
                        }
                    }
                    return positions;
                }
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
                    animation: true,
                    lineWidth: 2,
                    shadow: false,
                    states: {
                        hover: {
                            lineWidth: 2
                        },
                        select: {
                            enabled: false
                        }
                    },
                    marker: {
                        enabled: false,
                        radius: 0,
                        states: {
                            hover: {
                                radius: 0
                            }
                        }
                    },
                    fillOpacity: 0.25
                },
                column: {
                    negativeColor: '#910000',
                    borderColor: 'silver'
                }
            },
            exporting: {
                buttons: {
                    contextButton: {
                        enabled: false
                    }
                }
            }
        });

        n++;

        // If the process takes too much time, run a timeout to allow interaction with the browser
        if (new Date() - time > 10000) {
            $tds.splice(0, i + 1);
            setTimeout(doChunkChat, 0);
            break;
        }

        // Print a feedback on the performance
        /*if (n === fullLen) {
            $('#result').html('Generated ' + fullLen + ' sparklines in ' + (new Date() - start) + ' ms');
        }*/
    }
}

$document.querySelector('.chatbot__help-button').addEventListener('click', () => {
  // Prevent the click event from bubbling up to the header
  event.stopPropagation();
  
  // Extract the help message content from the initial message in the HTML
  if (!initialMessage) {
    console.error('Initial help message not found in the DOM.');
    return;
  }
  const helpMessage = initialMessage.innerHTML;

  // Call aiMessage with the help message content
  aiMessage(helpMessage, false, botReplyDelay);
}, false);

const chatInput = document.querySelector('.chatbot__input');

function autoExpand() {
  this.style.height = 'auto';                   // Reset height
  this.style.height = this.scrollHeight + 'px'; // Set to fit text
}

chatInput.addEventListener('input', autoExpand);

// Optionally run on page load or chat open
window.addEventListener('DOMContentLoaded', () => {
  autoExpand.call(chatInput);
});

function checkExchanges(exchange)
{
    if (exchange == 'NYSE' || exchange == 'NYSE Arca' || exchange == 'Nasdaq' || exchange == 'TSX' || exchange == 'ASX' || exchange == 'NSE' || exchange == 'HKEX' || exchange == "SHSE" || exchange == "SZSE" || exchange == "LSE" || exchange == "TYO")
        return true;
    else
        return false;
}

// Drag-to-dismiss logic with requestAnimationFrame
let sheetStartY = 0;
let lastTouchY = 0;
let lastTimestamp = 0;
let isDragging = false;
let rafId = null;

const sheet = document.getElementById('bottom-sheet');

sheet.addEventListener('touchstart', (e) => {
    sheetStartY = e.touches[0].clientY;
    lastTouchY = sheetStartY;
    lastTimestamp = Date.now();
    isDragging = true;
});

sheet.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    
    const currentY = e.touches[0].clientY;
    const deltaY = currentY - sheetStartY;
    
    if (deltaY > 0) {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(() => {
            sheet.style.transform = `translateY(${deltaY}px)`;
        });
    }
    
    lastTouchY = currentY;
    lastTimestamp = Date.now();
});

sheet.addEventListener('touchend', (e) => {
    if (!isDragging) return;
    isDragging = false;
    
    const endY = e.changedTouches[0].clientY;
    const deltaY = endY - sheetStartY;
    const timeDelta = Date.now() - lastTimestamp;
    const velocity = deltaY / timeDelta;
    
    // Close if dragged down > 100px OR quick swipe
    if (deltaY > 100 || (deltaY > 20 && velocity > 0.5)) {
        closeSheet();
    }
    
    sheet.style.transform = '';
});

function toggleBottomMenu(event, type) {
    event.stopPropagation();
    const sheet = document.getElementById('bottom-sheet');
    const overlay = document.getElementById('sheet-overlay');
    const content = document.getElementById('sheet-content');
    const header = document.getElementById('sheet-header');
    
    let html = '';
    if (type === 'market') {
        header.innerText = 'Select Market';
        html = `
            <a href="market?data=NYSE">NYSE</a>
            <a href="market?data=NASDAQ">Nasdaq</a>
            <a href="market?data=LSE">London</a>
            <a href="market?data=TSX">Toronto</a>
            <a href="market?data=ASX">Australia</a>
            <a href="market?data=NSE">India</a>
            <a href="market?data=TYO">Tokyo</a>
            <a href="market?data=HKEX">Hong Kong</a>
            <a href="market?data=SHSE">Shanghai</a>
            <a href="market?data=SZSE">Shenzhen</a>
        `;
    } else if (type === 'econ') {
        header.innerText = 'Select Economy';
        html = `
            <a href="econ?country=US">United States</a>
            <a href="econ?country=HK">Hong Kong</a>
        `;
    }
    
    content.innerHTML = html;
    overlay.classList.add('show');
    sheet.classList.add('show');
    
    // Toggle caret for the clicked item
    document.querySelectorAll('.bottom-nav-item').forEach(p => p.classList.remove('menu-open'));
    event.currentTarget.classList.add('menu-open');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeSheet() {
    const sheet = document.getElementById('bottom-sheet');
    const overlay = document.getElementById('sheet-overlay');
    if (sheet) sheet.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    document.querySelectorAll('.bottom-nav-item').forEach(p => p.classList.remove('menu-open'));
    document.body.style.overflow = '';
}

// Close sheet on click outside (overlay already handles it via onclick)
document.addEventListener('click', function(e) {
    if (!e.target.closest('.bottom-sheet') && !e.target.closest('.bottom-nav-item')) {
        closeSheet();
    }
});

// Scroll Animation Logic
(function() {
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar');
    const bottomNav = document.querySelector('.bottom-nav');
    const chatbot = document.querySelector('.chatbot');
    const topcontrol = document.getElementById('topcontrol');

    window.addEventListener('scroll', function() {
        if (window.innerWidth > 1024) return;
        
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scroll down
            if (navbar) navbar.classList.add('nav-hidden');
            if (bottomNav) bottomNav.classList.add('bottom-hidden');
            if (chatbot && chatbot.classList.contains('chatbot--closed')) chatbot.classList.add('bottom-hidden');
            if (topcontrol) topcontrol.classList.add('topcontrol-down');
            // Hide opened menus/sheets and reset carets
            closeSheet();
        } else {
            // Scroll up
            if (navbar) navbar.classList.remove('nav-hidden');
            if (bottomNav) bottomNav.classList.remove('bottom-hidden');
            if (chatbot) chatbot.classList.remove('bottom-hidden');
            if (topcontrol) topcontrol.classList.remove('topcontrol-down');
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            if (navbar) navbar.classList.remove('nav-hidden');
            if (bottomNav) bottomNav.classList.remove('bottom-hidden');
            if (chatbot) chatbot.classList.remove('bottom-hidden');
            if (topcontrol) topcontrol.classList.remove('topcontrol-down');
        }
    }, false);
})();
</script>

<!-- Theme scripts -->
<script src="assets/js/highcharts-theme.js?v=20260625.5"></script>
<script src="assets/js/theme.js"></script>

</footer>
