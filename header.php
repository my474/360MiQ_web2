<?php $miq_nav_user = isset($miq_account_user) ? $miq_account_user : miq_account_current_user(); ?>
<link rel="stylesheet" href="assets/css/account.css">
<nav class="navbar navbar-light navbar-expand-lg fixed-top bg-white clean-navbar container not-selectable">
  <a class="navbar-brand" href="./"><img src="assets/img/Logo/360Logo.png" width="70" alt="360MiQ logo"></a>
  <span class="navbar-toggler collapsed navtitle" style="margin:0;padding:0">
      <form id="ac" class="form-inline my-2 my-lg-0" action="stockinfo">
    <!--<input id="autocomplete" size="12" class="form-control mr-sm-2 copyMe" style="max-width:128px;padding-left:8px" type="search" placeholder="Stock Code" aria-label="Search" name="code" spellcheck="false"  maxlength="25" autofocus required>-->
        <input id="autocomplete" size="13" class="form-control mr-sm-2 copyMe" type="search" placeholder="Stock Code" onchange="this.value = this.value.trim();" aria-label="Search" name="code" spellcheck="false"  maxlength="25" required>
        &nbsp;
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
            <span class="new-badge-group" data-new-badge-start="2026-07-14">
                <span class="timed-new-badge">NEW</span>
                <span class="sr-only">(new feature)</span>
            </span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/blog">Analysis</a>
      </li>
      <!-- li class="nav-item">
        <a class="nav-link" href="blog/wp-login.php" target="_blank"><i class="fa-solid fa-pen-clip fa-xl" title="Analysis Contributor Login/Register&#10;&#10;Free Sign-up for Everyone!"></i></a>
      </li-->
      <!--li class="nav-item">
        <a class="nav-link" href="https://play.google.com/store/apps/details?id=com.miq360" target="_blank"><img src="assets/img/googleplay_play_logo_icon.png" alt="Get it on Google Play" title="Get it on Google Play" width="22" height="22"></a>
      </li-->
      <!-- theme toggle -->
      <li class="nav-item">
        <?php if ($miq_nav_user): ?>
          <div class="dropdown miq-account-nav-item">
            <a class="nav-link dropdown-toggle miq-account-trigger" href="#" id="miqAccountMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Account">
              <span class="miq-account-avatar" aria-hidden="true"><i class="fas fa-user"></i></span><span class="miq-account-name"><?php echo htmlspecialchars($miq_nav_user['display_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="miqAccountMenu">
              <a class="dropdown-item" href="/workspace"><i class="fas fa-layer-group fa-fw"></i> My Workspace</a>
              <a class="dropdown-item" href="/workspace?tab=charts"><i class="fas fa-chart-line fa-fw"></i> Saved Charts</a>
              <a class="dropdown-item" href="/workspace?tab=scripts"><i class="fas fa-code fa-fw"></i> Pine Scripts</a>
              <a class="dropdown-item" href="/community"><i class="fas fa-users fa-fw"></i> Community Ideas</a>
              <a class="dropdown-item" href="account_settings"><i class="fas fa-cog fa-fw"></i> Account Settings</a>
              <?php if (miq_account_is_moderator($miq_nav_user)): ?><a class="dropdown-item" href="/community_moderation"><i class="fas fa-shield-alt fa-fw"></i> Moderation Queue</a><?php endif; ?>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="account_logout"><i class="fas fa-sign-out-alt fa-fw"></i> Sign out</a>
            </div>
          </div>
        <?php else: ?>
          <a class="nav-link miq-signin-link" href="account.php?view=login&amp;return_to=<?php echo rawurlencode(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'); ?>" title="Sign in to save charts and scripts" aria-label="Sign in"><i class="fas fa-user-circle" aria-hidden="true"></i><span class="miq-signin-label">Sign in</span></a>
        <?php endif; ?>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" id="theme-toggle" title="Switch to dark mode" aria-label="Switch theme"><span class="theme-icon-light" aria-hidden="true">&#x2600;&#xFE0F;</span><span class="theme-icon-dark" aria-hidden="true">&#x1F319;</span></a>
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
        <div class="miq-header-search">
            <form id="ac2" class="hidden-xs form-inline my-2 my-lg-0" action="stockinfo">
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
.new-badge,
.timed-new-badge {
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
<script src="assets/js/account.js" defer></script>
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
.ui-autocomplete .recent-autocomplete-heading,
.ui-autocomplete .recent-autocomplete-footer {
    padding: 7px 12px;
    color: var(--text-muted, #666);
    background: var(--bg-card, #fff);
    border-bottom: 1px solid var(--border-color, #dee2e6);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.ui-autocomplete .recent-autocomplete-footer {
    border-top: 1px solid var(--border-color, #dee2e6);
    border-bottom: 0;
    text-align: right;
    text-transform: none;
    letter-spacing: 0;
}
.recent-autocomplete-clear {
    padding: 0;
    border: 0;
    color: var(--text-link, #007bff);
    background: transparent;
    cursor: pointer;
    font: inherit;
}
.recent-autocomplete-row {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.recent-autocomplete-label {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.recent-autocomplete-remove {
    flex: 0 0 auto;
    width: 22px;
    height: 22px;
    padding: 0;
    border: 0;
    border-radius: 3px;
    color: var(--text-muted, #666);
    background: transparent;
    cursor: pointer;
    font-size: 16px;
    line-height: 20px;
}
.recent-autocomplete-remove:hover,
.recent-autocomplete-remove:focus {
    color: var(--text-primary, #000);
    background: var(--bg-autocomplete-alt, #ecf6fc);
    outline: none;
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
    var recentStorageKey = '360miq-recent-stocks';
    var recentLimit = 10;

    function readRecentStocks() {
        try {
            var stored = JSON.parse(localStorage.getItem(recentStorageKey) || '[]');
            if (!Array.isArray(stored)) return [];
            return stored.filter(function (item) {
                return item && item.code && item.label;
            }).slice(0, recentLimit);
        } catch (error) {
            return [];
        }
    }

    function writeRecentStocks(stocks) {
        try {
            localStorage.setItem(recentStorageKey, JSON.stringify(stocks.slice(0, recentLimit)));
        } catch (error) {
            return;
        }
    }

    function rememberRecentStock(item) {
        var code = String(item && item.value || '').trim().toUpperCase();
        if (!code) return;

        var label = String(item && item.label || code).trim();
        var recent = readRecentStocks().filter(function (stock) {
            return String(stock.code).toUpperCase() !== code;
        });
        recent.unshift({ code: code, label: label || code });
        writeRecentStocks(recent);
    }

    function removeRecentStock(code) {
        var normalizedCode = String(code || '').trim().toUpperCase();
        writeRecentStocks(readRecentStocks().filter(function (stock) {
            return String(stock.code).toUpperCase() !== normalizedCode;
        }));
    }

    function clearRecentStocks() {
        try {
            localStorage.removeItem(recentStorageKey);
        } catch (error) {
            return;
        }
    }

    function recentItems(term) {
        var normalizedTerm = String(term || '').trim().toLowerCase();
        return readRecentStocks().filter(function (stock) {
            return !normalizedTerm || stock.label.toLowerCase().indexOf(normalizedTerm) !== -1;
        }).map(function (stock) {
            return {
                label: stock.label,
                value: stock.code,
                recent: true
            };
        });
    }

    function remoteItems(data) {
        return $.map(data, function (pn) {
            var label = pn.code + dot + pn.name_tc + dot + pn.name_en + dot + pn.exchange;
            if (pn.name_tc == null || pn.name_tc == pn.name_en) {
                if (pn.exchange == '') {
                    if (pn.name_en == '')
                        label = pn.code;
                    else
                        label = pn.code + dot + pn.name_en;
                } else if (pn.name_en == '') {
                    label = pn.code + dot + pn.exchange;
                } else {
                    label = pn.code + dot + pn.name_en + dot + pn.exchange;
                }
            }

            return {
                label: label,
                value: pn.code
            };
        });
    }

    function installStockAutocomplete(inputSelector, formSelector) {
        var $input = $(inputSelector);
        var recentActiveIndex = -1;
        var autocomplete = $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                var term = request.term.trim();
                if (!term) {
                    response(recentItems(term));
                    return;
                }

                $.ajax({
                    url: "db_autocomplete.php",
                    type: 'post',
                    dataType: "json",
                    data: { search: term },
                    success: function (data) {
                        response(remoteItems(data));
                    },
                    error: function () {
                        response([]);
                    }
                });
            },
            focus: function () {
                if (!this.value.trim()) {
                    $input.autocomplete('search', '');
                }
            },
            select: function (event, ui) {
                rememberRecentStock(ui.item);
                $(event.target).val(ui.item.value);
                $(formSelector).submit();
                return false;
            }
        });

        var instance = autocomplete.autocomplete('instance');

        function recentMenuItems() {
            return autocomplete.autocomplete('widget').find('li.recent-autocomplete-entry');
        }

        function clearRecentKeyboardSelection() {
            recentActiveIndex = -1;
            autocomplete.autocomplete('widget').find('.ui-state-active').removeClass('ui-state-active');
        }

        function setRecentKeyboardSelection(index) {
            var $items = recentMenuItems();
            if (!$items.length) return false;

            if (index < 0) index = $items.length - 1;
            if (index >= $items.length) index = 0;

            var $widget = autocomplete.autocomplete('widget');
            $widget.find('.ui-state-active').removeClass('ui-state-active');
            $items.removeAttr('aria-selected');

            var $item = $items.eq(index);
            $item.addClass('ui-state-active').attr('aria-selected', 'true');
            $item.children('.ui-menu-item-wrapper').addClass('ui-state-active');
            recentActiveIndex = index;
            return true;
        }

        function selectRecentKeyboardItem() {
            var $items = recentMenuItems();
            if (recentActiveIndex < 0 || recentActiveIndex >= $items.length) return false;

            var item = $items.eq(recentActiveIndex).data('recentItem');
            if (!item) return false;

            rememberRecentStock(item);
            $input.val(item.value);
            autocomplete.autocomplete('close');
            $(formSelector).submit();
            return true;
        }

        instance._renderItem = function (ul, item) {
            var $li = $('<li>');
            var $row = $('<div>', { class: 'ui-menu-item-wrapper recent-autocomplete-row' });

            if (item.recent) {
                $('<span>', {
                    class: 'recent-autocomplete-label',
                    text: item.label
                }).appendTo($row);
                $('<button>', {
                    type: 'button',
                    class: 'recent-autocomplete-remove',
                    text: '\u00d7',
                    title: 'Remove ' + item.value + ' from recent searches',
                    'aria-label': 'Remove ' + item.value + ' from recent searches',
                    'data-code': item.value
                }).appendTo($row);
                $li.addClass('recent-autocomplete-entry');
                $li.data('recentItem', item);
                $li.on('mouseenter.recentStocks', function () {
                    recentActiveIndex = recentMenuItems().index(this);
                });
                $row.find('.recent-autocomplete-remove').on('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    removeRecentStock($(this).attr('data-code'));
                    $input.autocomplete('search', '');
                });
            } else {
                $row.text(item.label);
            }

            return $li.append($row).appendTo(ul);
        };

        instance._renderMenu = function (ul, items) {
            clearRecentKeyboardSelection();
            var hasRecentItems = items.some(function (item) { return item.recent; });
            if (hasRecentItems) {
                $('<li>', {
                    class: 'recent-autocomplete-heading',
                    text: 'Recent searches',
                    role: 'presentation'
                }).appendTo(ul);
            }

            var self = this;
            $.each(items, function (index, item) {
                self._renderItemData(ul, item);
            });

            if (hasRecentItems) {
                var $clearButton = $('<button>', {
                    type: 'button',
                    class: 'recent-autocomplete-clear',
                    text: 'Clear recent'
                }).on('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    clearRecentStocks();
                    $input.autocomplete('close');
                });
                $('<li>', { class: 'recent-autocomplete-footer', role: 'presentation' })
                    .append($clearButton)
                    .appendTo(ul);
            }
        };

        $input[0].addEventListener('keydown', function (event) {
            var $items = recentMenuItems();
            var $menu = autocomplete.autocomplete('widget');
            if (!$menu.is(':visible') || !$items.length) return;

            var key = event.key || event.which;
            if (key === 'ArrowDown' || key === 40) {
                event.preventDefault();
                event.stopImmediatePropagation();
                setRecentKeyboardSelection(recentActiveIndex + 1);
            } else if (key === 'ArrowUp' || key === 38) {
                event.preventDefault();
                event.stopImmediatePropagation();
                setRecentKeyboardSelection(recentActiveIndex < 0 ? $items.length - 1 : recentActiveIndex - 1);
            } else if ((key === 'Enter' || key === 13) && selectRecentKeyboardItem()) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);

        $input.on('autocompleteopen.recentStocks autocompleteclose.recentStocks', clearRecentKeyboardSelection);

        $input.on('mousedown', function () {
            if (!this.value.trim()) $(this).autocomplete('search', '');
        });
        $input.closest('form').on('submit.recentStocks', function () {
            rememberRecentStock({ value: $input.val(), label: $input.val() });
        });
    }

    installStockAutocomplete('#autocomplete', '#ac');
    installStockAutocomplete('#autocomplete2', '#ac2');
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
    
    const currentDate = new Date();
    document.querySelectorAll('[data-new-badge-start]').forEach(function (badge) {
        const startDate = new Date(badge.getAttribute('data-new-badge-start') + 'T00:00:00');
        if (!Number.isNaN(startDate.getTime()) && currentDate >= new Date(startDate.getTime() + 90 * 24 * 60 * 60 * 1000)) {
            badge.remove();
        }
    });
    
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
