<?php
// Parameters that define unique content on your pages
$keep_params = ['data', 'map', 'code', 'codes', 'from', 'to', 'tf', 'market', 'country', 'tab'];

// Parse all current query parameters
parse_str($_SERVER['QUERY_STRING'], $query_params);

// Keep only the relevant parameters for canonical
$canonical_params = array_intersect_key($query_params, array_flip($keep_params));

// Construct the canonical URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$canonical_url = $protocol . $host . $path;

if (!empty($canonical_params)) {
    $canonical_url .= '?' . http_build_query($canonical_params);
}
?>
<!-- Anti-FOUC: must be first in <head>, before all stylesheets -->
<script>(function(){var s=localStorage.getItem('360miq-dark-mode');if(s==='true'||(s===null&&window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.setAttribute('data-theme','dark');}})();</script>
<link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>" />

<script>
    function getCookie(c_name) {
        if (document.cookie.length>0) {
            c_start=document.cookie.indexOf(c_name + "=")
            if (c_start!=-1) {
                c_start=c_start + c_name.length+1
                c_end=document.cookie.indexOf(";",c_start)
                if (c_end==-1) c_end=document.cookie.length
                    return unescape(document.cookie.substring(c_start,c_end))
            }
        }
    }

    var optout = false;
    optout = getCookie('optout')==1? true : false;
</script>
<!-- Load gtag.js or Tag Manager as normal, e.g.: -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-148758255-1">
</script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}

    if (optout)
    {
        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'analytics_storage': 'denied'
        });
    }

    gtag('js', new Date());
    gtag('config', 'UA-148758255-1', { 'anonymize_ip': true });

    if (!optout)
    {
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "4h0ts9dme8");
    }
</script>

<!--script src="assets/js/cookieconsent.js"></script>
<script src="assets/js/cookieconsentrun.js"></script-->

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<!--meta name="keywords" content="360miq,360 Market iQ,PE Band,PB Band,Price Earnings Band,Price Book Band,Trend Gauge,Stock screener,Sector Performance,Market Heatmap,Market breadth,MA Market Breadth,RSI Market Breadth,McClellan Oscillator,McClellan Summation Index,Advance Decline Ratio,Market indicator,Stock Quote,Market Sector,Market Industry,Stock,Invest,Trade,Technical analysis,Fundamental analysis,Price Channel,F Score,Z Score,M Score,Stock Polar,Market info,Market trend,升跌比例,市寬,市盈率區間圖,市賬率區間圖,市場熱圖,板塊熱圖,行業表現,股票篩選,大市指標" /-->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="360MiQ" />
<meta property="og:image" content="https://360miq.com/assets/img/Logo/360Logo.png" />
<meta http-equiv='content-language' content='en-us'>
<link rel="stylesheet" href="assets/css/theme.css">
