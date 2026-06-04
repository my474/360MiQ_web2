<nav class="navbar navbar-light navbar-expand-lg fixed-top bg-white clean-navbar container not-selectable">
  <a class="navbar-brand" href="./"><img src="assets/img/Logo/360Logo.png" width="70" alt="360MiQ logo"></a>
  <span class="navbar-toggler collapsed navtitle" style="margin:0;padding:0">
      <form id="ac" class="form-inline my-2 my-lg-0" action="stockinfo">
    <!--<input id="autocomplete" size="12" class="form-control mr-sm-2 copyMe" style="max-width:128px;padding-left:8px" type="search" placeholder="Stock Code" aria-label="Search" name="code" spellcheck="false"  maxlength="25" autofocus required>-->
        <input id="autocomplete" size="13" class="form-control mr-sm-2 copyMe" type="search" placeholder="Stock Code" onchange="this.value = this.value.trim();" aria-label="Search" name="code" spellcheck="false"  maxlength="25" required>
        <span style="color:#ffffff">.</span>
        <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" aria-label="Stock Search">&nbsp;<i class="fas fa-search"></i></button>
    </form>
  </span>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item <?php echo (isset($page) && $page == 'home' ? 'active' : ''); ?>">
        <a class="nav-link" href="./">Home<span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item dropdown <?php echo (isset($page) && $page == 'market' ? 'active' : ''); ?>">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo (!isset($exchangeName) || $page == 'econ' ? "Market" : $exchangeName); ?>&nbsp;</a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="market?data=NYSE">NYSE</a>
          <a class="dropdown-item" href="market?data=NASDAQ">Nasdaq</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=LSE">London</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=TSX">Toronto TSX</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=ASX">Australia</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=NSE">India NSE</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=TYO">Tokyo</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=HKEX">Hong Kong</a>
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="market?data=SHSE">Shanghai</a>
          <a class="dropdown-item" href="market?data=SZSE">Shenzhen</a>
        </div>
      </li>
      <li class="nav-item <?php echo (isset($page) && $page == 'stockinfo' ? 'active' : ''); ?>">
        <a class="nav-link" href="stockinfo">Stock</a>
      </li>
      <li class="nav-item <?php echo (isset($page) && $page == 'screener' ? 'active' : ''); ?>">
        <a class="nav-link" href="screener">Screener</a>
      </li>
      <li class="nav-item dropdown <?php echo (isset($page) && $page == 'econ' ? 'active' : ''); ?>">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Econ</a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown1">
          <a class="dropdown-item" href="econ?country=US">US</a>
          <a class="dropdown-item" href="econ?country=HK">Hong Kong</a>
        </div>
      </li>
      <li class="nav-item <?php echo (isset($page) && $page == 'tool' ? 'active' : ''); ?>">
        <a class="nav-link" href="tool">Tool
            <span class="new-badge">new</span>
            <span class="sr-only new">(new feature)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/blog">Analysis</a>
      </li>
      <!-- li class="nav-item">
        <a class="nav-link" href="/blog/wp-login.php" target="_blank"><i class="fa-solid fa-pen-clip fa-xl" title="Analysis Contributor Login/Register&#10;&#10;Free Sign-up for Everyone!"></i></a>
      </li-->
      <li class="nav-item">
        <a class="nav-link" href="https://play.google.com/store/apps/details?id=com.miq360" target="_blank"><img src="assets/img/googleplay_play_logo_icon.png" alt="Get it on Google Play" title="Get it on Google Play" width="22" height="22"></a>
      </li>
      <!-- theme toggle -->
      <li class="nav-item">
        <a class="nav-link" href="#" id="theme-toggle" title="Switch to dark mode">☀</a>
      </li>
      <!--li class="navbar-toggler collapsed nav-item <?php echo (isset($page) && $page == 'about' ? 'active' : ''); ?>" style="border-width:0;padding-left:0;">
        <a class="nav-link" href="about-us">About us</a>
      </li>
      <li class="navbar-toggler collapsed nav-item <?php echo (isset($page) && $page == 'contact' ? 'active' : ''); ?>" style="border-width:0;padding-left:0;">
        <a class="nav-link" href="contact-us">Contact us</a>
      </li>
      <li class="navbar-toggler collapsed nav-item <?php echo (isset($page) && $page == 'terms' ? 'active' : ''); ?>" style="border-width:0;padding-left:0;">
        <a class="nav-link" href="terms">Terms and Conditions</a>
      </li>
      <li class="navbar-toggler collapsed nav-item <?php echo (isset($page) && $page == 'disclaimer' ? 'active' : ''); ?>" style="border-width:0;padding-left:0;">
        <a class="nav-link" href="disclaimer">Disclaimer</a>
      </li>
      <li class="navbar-toggler collapsed nav-item <?php echo (isset($page) && $page == 'privacypolicy' ? 'active' : ''); ?>" style="border-width:0;padding-left:0;">
        <a class="nav-link" href="privacypolicy">Privacy Policy</a>
      </li-->          
<!--      <li class="nav-item">
        <a class="nav-link" href="about-us">About</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="contact-us">Contact</a>
      </li>-->
    </ul>
<!--<div class="dropdown">-->
<!--  <button class="langbtn btn btn-default dropdown-toggle mr-sm-2" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><font size="2"><span class="underline">繁</span> | 简 | En</font></button>
  <button class="langbtn btn btn-default dropdown-toggle mr-sm-2" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" title="Settings"><font size="5"><i class="fas fa-cog" aria-hidden="true"></i></font></button>
  <div class="dropdown-menu">
    <span class="dropdown-item-text" style="background:darkgrey;color:white">Language</span>
    <a href="#" class="lang-item dropdown-item lang-item-checked">繁體中文</a>
    <a href="#" class="lang-item dropdown-item">简体中文</a>
    <a href="#" class="lang-item dropdown-item">English</a>
    <span class="dropdown-item-text" style="background:darkgrey;color:white">Up&nbsp;/&nbsp;Down&nbsp;Color</span>
    <a href="#" class="color-item dropdown-item color-item-checked">Up: <i class="fas fa-arrow-alt-circle-up" style="color:green"></i> Down: <i class="fas fa-arrow-alt-circle-down" style="color:red"></i></a>
    <a href="#" class="color-item dropdown-item">Up: <i class="fas fa-arrow-alt-circle-up" style="color:red"></i> Down: <i class="fas fa-arrow-alt-circle-down" style="color:green"></i></a>
      
  </div>
</div>-->

<!--script>      
$(document).on('click', '.lang-item', function(event) {
  event.preventDefault();
  $(this).toggleClass('lang-item-checked').siblings().removeClass('lang-item-checked');
});
$(document).on('click', '.color-item', function(event) {
  event.preventDefault();
  $(this).toggleClass('color-item-checked').siblings().removeClass('color-item-checked');
});
/*    var originalText = '<font size="2"><span class="normal">繁</span> | <span class="normal">简</span> | <span class="normal">En</span></font>';
     if ($(this).text() == "繁"){
        var span = '<span class="underline">繁</span>';
        $('.langbtn').html(originalText);
        var text = $('.langbtn').html();         
        $('.langbtn').html(text.replace('<span class="normal">繁</span>', span));
     }
    else if ($(this).text() == "简"){
        var span = '<span class="underline">简</span>';
        $('.langbtn').html(originalText);
        var text = $('.langbtn').html();        
        $('.langbtn').html(text.replace('<span class="normal">简</span>', span));
     }
    else if ($(this).text() == "En"){
        var span = '<span class="underline">En</span>';
        $('.langbtn').html(originalText);
        var text = $('.langbtn').html();        
        $('.langbtn').html(text.replace('<span class="normal">En</span>', span));
     }*/
</script-->
        <div style="float:right;">
            <form id="ac2" class="hidden-xs collapse navbar-collapse form-inline my-2 my-lg-0" action="stockinfo">
            <!--<input id="autocomplete2" size="12" class="form-control mr-sm-2 copyMe" style="max-width:150px;" type="search" placeholder="Stock Code" aria-label="Search" name="code" spellcheck="false"  maxlength="25" autofocus required>-->
                <input id="autocomplete2" size="10" class="form-control mr-sm-2 copyMe" type="search" placeholder="Stock Code" onchange="this.value = this.value.trim();" aria-label="Search" name="code" spellcheck="false"  maxlength="25" autofocus required>
                <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" aria-label="Stock Search" style="width:39px"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</nav>

<style>
.color-item-checked::before,.lang-item-checked::before {
  position: absolute;
  left: .4rem;
  content: '✓';
  font-weight: 600;
}
.underline { text-decoration: underline; font-weight: 900;}
.normal {
    font-weight: normal;
}

@media (min-width: 1199.98px) {
    .navbar-nav li:hover>.dropdown-menu {
      opacity: 1;
      transform: translateY(-11px);
      visibility: visible;
    }
    
    .dropdown .dropdown-menu {
        -webkit-transition: all 0.3s;
        -moz-transition: all 0.3s;
        -ms-transition: all 0.3s;
        -o-transition: all 0.3s;
        transition: all 0.3s;
        top: 45px;
        display: block;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
    }
}

/* Menu styles */
.new-badge {
    display: inline-block;
    margin-left: -2px; /* Reduced from 8px to bring closer to "Chart" */
    padding: 1px 3px; /* Slightly adjusted for rotation */
    font-size: 11px;
    font-weight: bold;
    color: #fff;
    background-color: #28a745; /* Green, Bootstrap success */
    border-radius: 6px; /* Pill shape */
    vertical-align: middle;
    transform: rotate(20deg); /* Rotate 30 degrees clockwise */
    transform-origin: center; /* Rotate around center */
}
</style>

<!-- Script -->
<script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
<!-- jQuery UI -->
<link href='autocomplete/jquery-ui.min.css' rel='stylesheet' type='text/css'>
<style>
#autocomplete {
    position: relative;
    z-index: 10000;
}
.ui-autocomplete {
    z-index: 9999 !important;
    font-size: 13px;
}
.ui-autocomplete .ui-menu-item:nth-child(even){
    background-color: #ecf6fc;  // alternate item bgcolor
}
[data-theme="dark"] #autocomplete,
[data-theme="dark"] #autocomplete2 {
    background-color: #2a2a3e;
    color: #e8e8e8;
}
[data-theme="dark"] .ui-autocomplete {
    background: #2a2a3e;
    color: #e8e8e8;
}
[data-theme="dark"] .ui-autocomplete .ui-menu-item-wrapper {
    color: #e8e8e8;
}
[data-theme="dark"] .ui-autocomplete .ui-menu-item:nth-child(even) {
    background-color: #1e1e32;
}
[data-theme="dark"] .ui-autocomplete .ui-state-focus,
[data-theme="dark"] .ui-autocomplete .ui-state-active {
    background: #3a3a50;
    color: #ffffff;
}
</style>    
<script type='text/javascript' >
$( function() {
    var dot = ' \u25CF ';
    $( "#autocomplete" ).autocomplete({
        source: function( request, response ) {
            
            $.ajax({
                url: "db_autocomplete.php",
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term.trim()
                },
                success: function( data ) {
                    response($.map(data, function (pn) {
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

                        return {
                            label: label,
                            value: pn.code,
                        };
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#autocomplete').val(ui.item.value); // display the selected text
            $(event.target).val(ui.item.value);
            $('#ac').submit();
            return false;
        }
    });
});
$( function() {
    var dot = ' \u25CF ';
    $( "#autocomplete2" ).autocomplete({
        source: function( request, response ) {
            
            $.ajax({
                url: "db_autocomplete.php",
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term.trim()
                },
                success: function( data ) {
                    response($.map(data, function (pn) {
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

                        return {
                            label: label,
                            value: pn.code,
                        };
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#autocomplete2').val(ui.item.value); // display the selected text
            $(event.target).val(ui.item.value);
            $('#ac2').submit();
            return false;
        }
    });
});

function split( val ) {
  return val.split( /,\s*/ );
}
function extractLast( term ) {
  return split( term ).pop();
}

$(document).ready(function() {
  if (!window.mobileAndTabletcheck())
    document.getElementById("autocomplete").focus();
    
  $('.copyMe').on('change', function() {
    $(".copyMe").val($(this).val());
  });
});

window.onload = function () {  
    var images = document.getElementsByTagName('img');   
    for (var i = 0; img = images[i++];) {    
        img.ondragstart = function() { return false; };
    }
    
    // remove NEW badge after 90 days
    const badge = document.querySelector('.new-badge');
    const srOnly = document.querySelector('.sr-only.new');
    const startDate = new Date('2025-05-16'); // Badge added on May 16, 2025
    const currentDate = new Date();
    const daysDiff = (currentDate - startDate) / (1000 * 60 * 60 * 24); // Days since start

    if (daysDiff >= 90) {
        if (badge) badge.remove(); // Remove "New" badge
        if (srOnly) srOnly.remove(); // Remove screen reader text
        //console.log('New badge and sr-only.new removed: 90 days elapsed');
    }
    
     // ********** this is set and change in header.php. Obfuscating it to random name will break the code. Manually match the Obfuscated name both here and in header.php *****
    isSubmitted = false;
};

document.getElementById('ac').addEventListener('submit', function () {
    isSubmitted = true;  // ********** this is set and change in header.php. Obfuscating it to random name will break the code. Manually match the Obfuscated name both here and in header.php *****
});

document.getElementById('ac2').addEventListener('submit', function () {
    isSubmitted = true;  // ********** this is set and change in header.php. Obfuscating it to random name will break the code. Manually match the Obfuscated name both here and in header.php *****
});

</script>