<?php
$site_url = 'https://360miq.com';
$show_stock_index_prices_env = getenv('SHOW_STOCK_INDEX_PRICES');
$show_stock_index_prices = true;
if ($show_stock_index_prices_env !== false && $show_stock_index_prices_env !== '') {
    $show_stock_index_prices = filter_var(
        $show_stock_index_prices_env,
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    );
    if ($show_stock_index_prices === null) {
        $show_stock_index_prices = true;
    }
}
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';
$path = preg_replace('/\.php$/i', '', $path);

if ($path === '/index' || $path === '') {
    $path = '/';
}

$route = trim($path, '/');
$canonical_params = array();

if ($route === 'market') {
    $canonical_params['data'] = isset($data) ? strtoupper($data) : strtoupper(isset($_GET['data']) ? $_GET['data'] : 'NYSE');
} elseif ($route === 'econ') {
    $canonical_params['country'] = isset($country) ? strtoupper($country) : strtoupper(isset($_GET['country']) ? $_GET['country'] : 'US');
} elseif ($route === 'stockinfo') {
    $canonical_stock_code = isset($stockcode) ? $stockcode : (isset($_GET['code']) ? $_GET['code'] : '');
    $canonical_stock_code = substr(trim(strtoupper($canonical_stock_code)), 0, 15);

    if ($canonical_stock_code !== '') {
        $canonical_params['code'] = $canonical_stock_code;
    }
}

$canonical_url = $site_url . ($path === '/' ? '/' : rtrim($path, '/'));
if (!empty($canonical_params)) {
    $canonical_url .= '?' . http_build_query($canonical_params, '', '&', PHP_QUERY_RFC3986);
}

$robots_content = $route === 'error' ? 'noindex, nofollow' : 'index, follow, max-image-preview:large';
$social_image = $site_url . '/assets/img/360Logo_512.png';
$structured_data = array(
    '@context' => 'https://schema.org',
    '@graph' => array(
        array(
            '@type' => 'Organization',
            '@id' => $site_url . '/#organization',
            'name' => '360MiQ',
            'url' => $site_url . '/',
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => $social_image
            )
        ),
        array(
            '@type' => 'WebSite',
            '@id' => $site_url . '/#website',
            'url' => $site_url . '/',
            'name' => '360MiQ',
            'publisher' => array('@id' => $site_url . '/#organization'),
            'inLanguage' => 'en-US',
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => array(
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $site_url . '/stockinfo?code={search_term_string}'
                ),
                'query-input' => 'required name=search_term_string'
            )
        )
    )
);
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<!-- Anti-FOUC: before all stylesheets -->
<script>(function(){var s=localStorage.getItem('360miq-dark-mode');if(s==='true'||(s===null&&window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.setAttribute('data-theme','dark');}})();</script>
<script>window.__SITE_DISPLAY_POLICY={showStockIndexPrices:<?php echo $show_stock_index_prices ? 'true' : 'false'; ?>};</script>
<script>window.__SCHEMA_MARKUP_CONFIG={siteUrl:<?php echo json_encode($site_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,canonicalUrl:<?php echo json_encode($canonical_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,route:<?php echo json_encode($route === '' ? 'home' : $route, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>};</script>
<link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="robots" content="<?php echo $robots_content; ?>">
<meta name="theme-color" content="#ffc107">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta property="og:type" content="website">
<meta property="og:site_name" content="360MiQ">
<meta property="og:locale" content="en_US">
<meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:image" content="<?php echo $social_image; ?>">
<meta property="og:image:alt" content="360MiQ stock market analytics">
<meta name="twitter:card" content="summary">
<meta name="twitter:image" content="<?php echo $social_image; ?>">
<script type="application/ld+json"><?php echo json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>

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

<!--meta name="keywords" content="360miq,360 Market iQ,PE Band,PB Band,Price Earnings Band,Price Book Band,Trend Gauge,Stock screener,Sector Performance,Market Heatmap,Market breadth,MA Market Breadth,RSI Market Breadth,McClellan Oscillator,McClellan Summation Index,Advance Decline Ratio,Market indicator,Stock Quote,Market Sector,Market Industry,Stock,Invest,Trade,Technical analysis,Fundamental analysis,Price Channel,F Score,Z Score,M Score,Stock Polar,Market info,Market trend,升跌比例,市寬,市盈率區間圖,市賬率區間圖,市場熱圖,板塊熱圖,行業表現,股票篩選,大市指標" /-->
<meta http-equiv='content-language' content='en-us'>
<link rel="stylesheet" href="assets/css/theme.css">
