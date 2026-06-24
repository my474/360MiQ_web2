<?php
    define('A',true);
    include '/home2/aamiqcom/php_script/mysql_vars_stock.php';

    $desc1 = ' the latest in-depth technical and fundamental analysis using our proprietary Trend Gauge, technical indicators, Price Channel, PE & PB Band, F Score, Z Score, M Score. We also provide industry median for those important metrics, so that you know where your favorite stock stands in the crowd.';
    $webtitle = 'Stock - 360MiQ.com';
    $line = '';
    $code_name = '';
    $exchange = '';
    /*$stockcode=formatInput(substr(trim(strtoupper($_GET["code"])), 0, 15), $connection);
    if ($stockcode == "")
        $stockcode=formatInput(substr(trim(strtoupper($_GET["stockcode"])), 0, 15), $connection);*/
    
    $stockcode=substr(trim(strtoupper(isset($_GET["code"]) ? $_GET["code"] : '')), 0, 15);
    if ($stockcode == "")
        $stockcode=substr(trim(strtoupper(isset($_GET["stockcode"]) ? $_GET["stockcode"] : '')), 0, 15);
        
    if ($stockcode != "" && /*preg_match('/[A-Za-z0-9&.-]/', $stockcode) &&*/ substr_count($stockcode, '.') <= 1)
    {
        if (preg_match('/[0-9]/', $stockcode))
            $codenum = floatval($stockcode);
            
        if (endsWith($stockcode, ".HK") && strlen($stockcode) > 3)
        {
            $code_pre = substr($stockcode, 0, -3);
            $code_num = floatval($code_pre);
            if ($code_num > 0)
            {
                $stockcode = HKcode($code_num);
            }
        }
        else if (isset($codenum) && !is_nan($codenum) && $codenum > 0 && $codenum < 90000 && strlen($stockcode) < 6)
        {
            $stockcode = HKcode($codenum);
        }

        $sqlExact = "SELECT code, exchange FROM stock_code_name_exchange WHERE code = ? AND exchange <> 'N/A' ORDER BY IF(FIELD(exchange,'NYSE','NASDAQ','NYSEARCA')=0,1,0) LIMIT 1";
        $sqlQuery0 = "SELECT code, exchange FROM (SELECT IF(code=?, 1, 0) AS exact, IF(code like ?, 1, 0) AS likecode, IF(code like ?, 1, 0) AS likecode2, IF(code like ?, 1, 0) AS likecode3, IF(name_en = ?, 1, 0) AS likecode4, IF(name_en like ?, 1, 0) AS likecode5, IF(name_en like ?, 1, 0) AS likecode6, IF(name_en like ?, 1, 0) AS likecode7, IF(name_en like ?, 1, 0) AS likecode8, IF(name_en like ?, 1, 0) AS likecode9, IF(name_en like ?, 1, 0) AS likecode10, IF(name_tc = ?, 1, 0) AS likecode4a, IF(name_tc like ?, 1, 0) AS likecode5a, IF(name_tc like ?, 1, 0) AS likecode6a, IF(name_tc like ?, 1, 0) AS likecode7a, IF(name_tc like ?, 1, 0) AS likecode8a, IF(name_tc like ?, 1, 0) AS likecode9a, IF(name_tc like ?, 1, 0) AS likecode10a, code, name_tc, name_en, exchange FROM stock_code_name_exchange WHERE (code like CONCAT('%',?,'%') or name_tc like CONCAT('%',?,'%') or name_en like CONCAT('%',?,'%')) AND exchange <> 'N/A' ORDER BY exact DESC, IF(FIELD(exchange,'NYSE','NASDAQ','NYSEARCA')=0,1,0), likecode DESC, likecode2 DESC, likecode3 DESC, likecode4 DESC, likecode4a DESC, likecode5 DESC, likecode5a DESC, likecode6 DESC, likecode6a DESC, likecode7 DESC, likecode7a DESC, likecode8 DESC, likecode8a DESC, likecode9 DESC, likecode9a DESC, likecode10 DESC, likecode10a DESC, CHAR_LENGTH(name_tc), CHAR_LENGTH(name_en), code LIMIT 1) a";
        //$sqlQuery3 = "SELECT d.code, f.market_cap, f.shares, f.close, d.exchange, f.eps, round(f.pe, 1), f.currency, IF( d.name_en LIKE CONCAT('%', d.name_tc, '%'), NULL, d.name_tc ) AS name_tc, d.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM (SELECT a.code, market_cap, shares, CLOSE, a.exchange, a.name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures FROM stock_code_name_exchange AS a LEFT JOIN price_current AS b ON a.code = b.code LEFT JOIN stock_HKEX AS c ON a.code = c.code WHERE a.code = @stock AND a.exchange = @exchange) AS d left JOIN price_current AS f on d.code = f.code LEFT JOIN trendgauge_stock_current g on f.code = g.code WHERE f.code = @stock AND d.exchange = @exchange";
        //$sqlQuery3 = "SELECT d.code, f.market_cap, f.shares, f.close, d.exchange, f.eps, round(f.pe, 1), f.currency, IF( d.name_en LIKE CONCAT('%', d.name_tc, '%'), NULL, d.name_tc ) AS name_tc, d.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM (SELECT a.code, a.exchange, a.name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures FROM stock_code_name_exchange AS a LEFT JOIN stock_HKEX AS c ON a.code = c.code WHERE a.code = @stock AND a.exchange = @exchange) AS d left JOIN price_current AS f on d.code = f.code LEFT JOIN trendgauge_stock_current g on f.code = g.code WHERE f.code = @stock AND d.exchange = @exchange";
        $sqlQuery3 = "SELECT a.code, f.market_cap, f.shares, f.close, a.exchange, f.eps, round(f.pe, 1), f.currency, IF( a.name_en LIKE CONCAT('%', a.name_tc, '%'), NULL, a.name_tc ) AS name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM stock_code_name_exchange AS a LEFT JOIN stock_HKEX AS c ON a.code = c.code left JOIN price_current AS f on a.code = f.code LEFT JOIN trendgauge_stock_current g on a.code = g.code WHERE a.code = ? AND (a.exchange = ? or f.stock_exchange_short = ?)";
        
        $resolvedStock = '';
        $resolvedExchange = '';

        $stmt = $connection->prepare($sqlExact);
        $stmt->bind_param("s", $stockcode);
        $stmt->execute();
        $lookupResult = $stmt->get_result();
        if ($lookupRow = $lookupResult->fetch_assoc())
        {
            $resolvedStock = $lookupRow['code'];
            $resolvedExchange = $lookupRow['exchange'];
        }
        $stmt->close();

        $likeVar1 = $stockcode."%";
        $likeVar2 = "%".$stockcode;
        $likeVar3 = "%".$stockcode."%";
        $likeVar5 = $stockcode." %";
        $likeVar6 = "% ".$stockcode;
        $likeVar7 = "% ".$stockcode." %";

        if ($resolvedStock == '')
        {
            $stmt = $connection->prepare($sqlQuery0);
            $stmt->bind_param("sssssssssssssssssssss",  $stockcode, $likeVar1, $likeVar2, $likeVar3, $stockcode, $likeVar5, $likeVar6, $likeVar7, $likeVar1, $likeVar2, $likeVar3, $stockcode, $likeVar5, $likeVar6, $likeVar7, $likeVar1, $likeVar2, $likeVar3, $stockcode, $stockcode, $stockcode);
            $stmt->execute();
            $lookupResult = $stmt->get_result();
            if ($lookupRow = $lookupResult->fetch_assoc())
            {
                $resolvedStock = $lookupRow['code'];
                $resolvedExchange = $lookupRow['exchange'];
            }
            $stmt->close();
        }

        $result = false;
        if ($resolvedStock != '' && $resolvedExchange != '')
        {
            $stmt = $connection->prepare($sqlQuery3);
            $stmt->bind_param("sss", $resolvedStock, $resolvedExchange, $resolvedExchange);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        }
    
        while($result && ($itemRow = $result->fetch_assoc()))
        {
            $exchange = $itemRow['exchange'];
            $marketcap = $itemRow['shares'] * $itemRow['close'];
            $currency = $itemRow['currency'];
            $trendvalue = $itemRow['value'];
            if ($exchange == "LSE" && $currency == "GBX") // price in penny
            {
                $currency = "GBP";
                $marketcap /= 100;
            }
            else if ($exchange == "SHG")
                $exchange = "SHSE";
            $marketcap2 = $itemRow['market_cap'];
            
            if (is_null($marketcap) || $marketcap == 0)
            {
                if (!is_null($marketcap2))
                    $marketcap = $marketcap2;
            }
    
            if (substr_compare($exchange, 'ETF', -strlen('ETF')) === 0) // endwith
                $exchange = substr($exchange, 0, strlen($exchange) - 3);        
                
            $name_tc = isset($itemRow['name_tc']) ? $itemRow['name_tc'] : '';
            $name_en = isset($itemRow['name_en']) ? $itemRow['name_en'] : '';
            $boardlot = isset($itemRow['boardlot']) ? $itemRow['boardlot'] : '';
            $shortsell_eligible = isset($itemRow['shortsell_eligible']) ? $itemRow['shortsell_eligible'] : '';
            $stock_options = isset($itemRow['stock_options']) ? $itemRow['stock_options'] : '';
            $stock_futures = isset($itemRow['stock_futures']) ? $itemRow['stock_futures'] : '';
            $marketcap_percentile = isset($itemRow['marketcap_percentile']) ? $itemRow['marketcap_percentile'] : '';
            $eps = isset($itemRow['eps']) ? $itemRow['eps'] : '';
            $pe = isset($itemRow['pe']) ? $itemRow['pe'] : '';
            $currency1 = isset($itemRow['currency']) ? $itemRow['currency'] : '';
            $isIEX = isset($itemRow['isIEX']) ? $itemRow['isIEX'] : '';
            $code = isset($itemRow['code']) ? $itemRow['code'] : '';
            $value = isset($itemRow['value']) ? $itemRow['value'] : '';
            $line = $marketcap.'_'. $exchange.'_'. $name_tc.'_'. $name_en.'_'. $boardlot.'_'. $shortsell_eligible.'_'. $stock_options.'_'. $stock_futures.'_'. $marketcap_percentile.'_'.$eps.'_'.$pe.'_'.$currency1.'_'.$isIEX.'_'.$code.'_'.$value;
        }

        mysqli_close($connection);

        if ($line != "")
        {
            $fields = explode("_", $line);
                
            if (sizeof($fields) == 15)
            {
                if ($fields[13] != "")
                {
                    $stockcode = $fields[13];
                    $webtitle = ($stockcode != '' ? $stockcode . ' ' : '') . "Stock - 360MiQ.com";
                    $code_name = $stockcode;
                }

                if ($fields[2] == "")
                {
                    $stockname_en_tc = $fields[3];
                    $webtitle = $stockcode . ' (' . $stockname_en_tc . ') Stock - 360MiQ.com';
                    $code_name = $stockcode . ' (' . $stockname_en_tc . ')';
                }
                else if ($fields[3] == "" || $fields[2] == $fields[3])
                {
                    $stockname_en_tc = $fields[2];
                    $webtitle = $stockcode . ' (' . $stockname_en_tc . ') Stock - 360MiQ.com';
                    $code_name = $stockcode . ' (' . $stockname_en_tc . ')';
                }
                else
                {
                    $webtitle = $stockcode . ' (' . $fields[2] . ' ' . $fields[3] . ') Stock - 360MiQ.com';
                    $code_name = $stockcode . ' ('  . $fields[2] . ' ' . $fields[3] . ')';
                }
            }
            else
            {
                if (sizeof($fields) == 3)
                {
                    if ($fields[1] == "")
                    {
                        $stockname_en_tc = $fields[2];
                        $webtitle = $stockcode . ' (' . $stockname_en_tc . ') Stock - 360MiQ.com';
                        $code_name = $stockcode . ' (' . $stockname_en_tc . ')';
                    }
                    else if ($fields[2] == "" || $fields[1] == $fields[2])
                    {
                        $stockname_en_tc = $fields[1];
                        $webtitle = $stockcode . ' (' . $stockname_en_tc . ') Stock - 360MiQ.com';
                        $code_name = $stockcode . ' (' . $stockname_en_tc . ')';
                    }
                    else
                    {
                        $webtitle = $stockcode . ' (' . $fields[1] . ' ' . $fields[2] . ') Stock - 360MiQ.com';
                        $code_name = $stockcode . ' ('  . $fields[1] . ' ' . $fields[2] . ')';
                    }
                }
            }
            $webtitle = str_replace(" ()", "", $webtitle);
            $code_name = str_replace(" ()", "", $code_name);
            $desc = $code_name . exchFn($exchange) . ' —' . mcapFn($marketcap, $currency) . trendgauge2txt($stockcode, $trendvalue) . ' More on' . $desc1;
        }
        else
            $desc = '360MiQ.com featuring' . $desc1;
    }
    else
        $desc = '360MiQ.com featuring' . $desc1;

function HKcode($stockcode) {
    if ($stockcode < 10)
        $stockcode = "000" . $stockcode;
    else if ($stockcode < 100)
        $stockcode = "00" . $stockcode;
    else if ($stockcode < 1000)
        $stockcode = "0" . $stockcode;        
        
    return $stockcode . ".HK";  
}    
function endsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}
function exchFn($exchange){
    if ($exchange == 'NASDAQ')
        return ' ○ Nasdaq';
    else if ($exchange == 'NYSEARCA')
        return ' ○ NYSE Arca';
    return ' ○ ' . $exchange;
}
function mcapFn($number, $currency) {
    if (is_null($number) || is_nan($number) || $number <= 0)
        return '';
    else if ($number >= 1000000000000)
        return ' Market Cap: ' . round ($number / 1000000000000, 1) . 'T' . ($currency == '' ? '' : ' ') . $currency . '.';
    else if ($number >= 1000000000 && $number < 1000000000000)
        return ' Market Cap: ' . round ($number / 1000000000, 1) . 'B' . ($currency == '' ? '' : ' ') . $currency . '.';
    else if ($number >= 1000000 && $number < 1000000000)
        return ' Market Cap: ' . round ($number / 1000000, 1) . 'M' . ($currency == '' ? '' : ' ') . $currency . '.';
    else if ($number >= 1000 && $number < 1000000)
        return ' Market Cap: ' . round ($number / 1000, 1) . 'K' . ($currency == '' ? '' : ' ') . $currency . '.';
    else
        return ' Market Cap: ' . $number . ($currency == '' ? '' : ' ') . $currency . '.';
}
function trendgauge2txt($stockcode, $trendvalue) {
    if ($trendvalue > 22.5 && $trendvalue <= 67.5) 
        return ' ' . $stockcode . ' is trending strongly up.';
    else if ($trendvalue > 67.5 && $trendvalue <= 112.5) 
        return ' ' . $stockcode . ' is overbought.';
    else if ($trendvalue > 112.5 && $trendvalue <= 157.5) 
        return ' ' . $stockcode . ' is in correction.';
    else if ($trendvalue > 157.5 && $trendvalue <= 202.5) 
        return ' ' . $stockcode . ' is trending down.';
    else if ($trendvalue > 202.5 && $trendvalue <= 247.5) 
        return ' ' . $stockcode . ' is trending strongly down.';
    else if ($trendvalue > 247.5 && $trendvalue <= 292.5) 
        return ' ' . $stockcode . ' is oversold.';
    else if ($trendvalue > 292.5 && $trendvalue <= 337.5) 
        return ' ' . $stockcode . ' is rebounding.';
    else if (($trendvalue > 337.5 && $trendvalue <= 360) ||
            ($trendvalue >= 0 && $trendvalue <= 22.5)) 
        return ' ' . $stockcode . ' is trending up.';
    else
        return '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $webtitle; ?></title>
    <meta property="og:title" content="<?php echo $webtitle; ?>" />
    <meta property="twitter:title" content="<?php echo $webtitle; ?>" />
    <meta name="description" content="<?php echo $desc; ?>" />
    <meta property="og:description" content="<?php echo $desc; ?>" />
    <meta property="twitter:description" content="<?php echo $desc; ?>" />
    
    <?php include "./meta.php" ?>

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
    <!--link rel="stylesheet" href="assets/css/jquery-ui.css"-->
    <link rel="stylesheet" href="assets/css/jquery-ui1.12.1.min.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <link rel="stylesheet" href="assets/css/inlinehelp.css">
    <link rel="stylesheet" href="assets/css/help-tip2.css">
    <link rel="stylesheet" href="assets/css/toggleSwitch.css">
    <!--script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js"></script-->
    <!--script defer data-cfasync="false" src="assets/js/bot-detector.js"-->
    <script>
      const RECAPTCHA_MAX_RETRIES = 2;
      const RECAPTCHA_RETRY_DELAY_MS = 750;

      function reportRecaptchaMissing() {
        fetch('/recap.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Recaptcha-Failure': 'true'
          },
          body: JSON.stringify({ token: null, action: 'recaptcha_missing' })
        }).catch(function() {
          // The reporting endpoint may be unavailable for the same reason as reCAPTCHA.
        });
      }

      function hasRecaptchaBrowserError(payload) {
        if (!payload || typeof payload !== 'object') {
          return false;
        }

        const errorCodes = payload['error-codes'] || payload.errorCodes || [];
        const invalidReason = payload.invalidReason ||
          (payload.tokenProperties && payload.tokenProperties.invalidReason);

        return (Array.isArray(errorCodes) && errorCodes.some(function(code) {
          return String(code).toLowerCase() === 'browser-error';
        })) || String(invalidReason || '').toUpperCase() === 'BROWSER_ERROR';
      }

      function runRecaptchaPageview(attempt) {
        attempt = attempt || 0;

        if (typeof grecaptcha === 'undefined') {
          if (attempt < RECAPTCHA_MAX_RETRIES) {
            setTimeout(function() {
              runRecaptchaPageview(attempt + 1);
            }, RECAPTCHA_RETRY_DELAY_MS * (attempt + 1));
          } else {
            reportRecaptchaMissing();
          }
          return;
        }

        grecaptcha.ready(function() {
          grecaptcha.execute('6LfVMmIrAAAAAMM1qvj7delYBZmU0CwbMklOaB9b', {action: 'pageview'}).then(function(token) {
            return fetch('/recap.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ token: token, action: 'pageview' })
            }).then(function(response) {
              return response.text().then(function(body) {
                let payload = null;

                if (body) {
                  try {
                    payload = JSON.parse(body);
                  } catch (error) {
                    // Keep compatibility with an endpoint that returns no JSON body.
                  }
                }

                if (!response.ok || hasRecaptchaBrowserError(payload)) {
                  throw new Error('Retryable reCAPTCHA failure');
                }
              });
            });
          }).catch(function() {
            if (attempt < RECAPTCHA_MAX_RETRIES) {
              setTimeout(function() {
                // A retry must call execute() again to obtain a fresh token.
                runRecaptchaPageview(attempt + 1);
              }, RECAPTCHA_RETRY_DELAY_MS * (attempt + 1));
            } else {
              reportRecaptchaMissing();
            }
          });
        });
      }
    </script>
    <script async src="https://www.google.com/recaptcha/api.js?render=6LfVMmIrAAAAAMM1qvj7delYBZmU0CwbMklOaB9b&amp;onload=runRecaptchaPageview" onerror="reportRecaptchaMissing()"></script>
    <script defer data-cfasync="false">
        const start = Date.now();
        let moveEvents = [];
        let clickDetected = false;
        let touchDetected = false;
        let keyTyped = false;
        let enterPressed = false;
        let navigationKeysPressed = false;
        let isSubmitted = false; // ********** this is set and change in header.php. Obfuscating it to random name will break the code. Manually match the Obfuscated name both here and in header.php *****

        const decode = atob;
        
        const navigationKeys = [
          "QXJyb3dVcA==", "QXJyb3dEb3du", "QXJyb3dMZWZ0", "QXJyb3dSaWdodA==",
          "UGFnZVVw", "UGFnZURvd24=", "SG9tZQ==", "RW5k",
          "VGFi", "IA==", "U3BhY2ViYXI="
        ].map(k => decode(k));
        
        const ev = {
          a: decode("bW91c2Vtb3Zl"),     // "mousemove"
          b: decode("Y2xpY2s="),         // "click"
          c: decode("dG91Y2hzdGFydA=="), // "touchstart"
          d: decode("a2V5ZG93bg=="),       // "keydown"
          e: decode("RW50ZXI="),          // "Enter"
          f: decode("YmVmb3JldW5sb2Fk") // "beforeunload"
        };
        
        const keyProp = decode("a2V5");            // "key"
        const keyLen = decode("bGVuZ3Ro");         // "length"
        
        // Mouse movement tracking
        document.addEventListener(ev.a, e => {
          moveEvents.push([
            Date.now() - start,
            e[decode("Y2xpZW50WA==")],
            e[decode("Y2xpZW50WQ==")]
          ]);
        });
        
        // Click detection
        document.addEventListener(ev.b, () => {
          clickDetected = true;
        });
        
        // Touch detection
        document.addEventListener(ev.c, () => {
          touchDetected = true;
        });
        
        // Keyboard activity
        document.addEventListener(ev.d, e => {
          const k = e[keyProp];
          if (k[keyLen] === 1) keyTyped = true;
          if (k === ev.e) enterPressed = true;
          if (navigationKeys.includes(k)) navigationKeysPressed = true;
        });

        // Heuristic to detect human-like mouse movement
        function isHumanMove(events) {
          if (events.length < 5) return false;
        
          let totalDistance = 0;
          let directionChanges = 0;
          let curvatures = [];
          let velocities = [];
        
          let prevDx = 0, prevDy = 0;
          let prevTime = events[0][0];
        
          for (let i = 1; i < events.length; i++) {
            const [t1, x1, y1] = events[i - 1];
            const [t2, x2, y2] = events[i];
        
            const dx = x2 - x1;
            const dy = y2 - y1;
            const dt = t2 - t1 || 1; // Avoid divide by zero
        
            const dist = Math.sqrt(dx * dx + dy * dy);
            const velocity = dist / dt;
        
            velocities.push(velocity);
            totalDistance += dist;
        
            if (i > 1 && (dx * prevDx < 0 || dy * prevDy < 0)) {
              directionChanges++;
            }
        
            // Curvature: angle between movement vectors
            const prev = events[i - 2] || [0, 0, 0];
            const vx1 = x1 - prev[1], vy1 = y1 - prev[2];
            const vx2 = x2 - x1, vy2 = y2 - y1;
        
            const dot = vx1 * vx2 + vy1 * vy2;
            const mag1 = Math.sqrt(vx1 * vx1 + vy1 * vy1);
            const mag2 = Math.sqrt(vx2 * vx2 + vy2 * vy2);
            if (mag1 && mag2) {
              const angle = Math.acos(Math.min(Math.max(dot / (mag1 * mag2), -1), 1)); // Clamp between -1 and 1
              curvatures.push(angle);
            }
        
            prevDx = dx;
            prevDy = dy;
            prevTime = t2;
          }
        
          const avgCurvature = curvatures.reduce((a, b) => a + b, 0) / curvatures.length || 0;
        
          // Velocity entropy
          const velEntropy = computeEntropy(velocities);
        
          // Total gesture time
          const gestureDuration = events.at(-1)[0] - events[0][0]; // in ms
        
          // Idle gaps: time diff between spikes
          let idleGaps = 0;
          for (let i = 1; i < events.length; i++) {
            const dt = events[i][0] - events[i - 1][0];
            if (dt > 300) idleGaps++;
          }
        
          // Score
          let score = 0;
          if (totalDistance > 200) score++;
          if (directionChanges >= 6) score++;
          if (avgCurvature > 0.1) score++;
          if (velEntropy > 0.2) score++;
          if (gestureDuration > 1000) score++;
          if (idleGaps >= 1) score++;
        
          return score >= 2;
        }
        
        function computeEntropy(arr) {
          if (arr.length === 0) return 0;
        
          // Bin velocities
          const counts = {};
          for (let v of arr) {
            const bin = Math.round(v * 10); // bin width 0.1
            counts[bin] = (counts[bin] || 0) + 1;
          }
        
          const total = arr.length;
          let entropy = 0;
          for (let count of Object.values(counts)) {
            const p = count / total;
            entropy -= p * Math.log2(p);
          }
          return entropy;
        }

        

        // Final check before leaving
        window.addEventListener(ev.f, () => {
          const humanMove = isHumanMove(moveEvents);

          // If user shows any sign of legit interaction, skip logging
          if (touchDetected || humanMove || isSubmitted || navigationKeysPressed) return;

          // Otherwise, log potential bot fingerprint
          const payload = {
              h: location[decode('aHJlZg==')], // "href"
              u: navigator[decode('dXNlckFnZW50')], // "userAgent"
              //m: moveEvents,
              c: clickDetected ? 1 : 0,
              k: keyTyped ? 1 : 0,
              e: enterPressed ? 1 : 0,
              //f: moveFlag ? 1 : 0,
              //t: touchDetected ? 1 : 0,
              s: {
                w: screen[decode('d2lkdGg=')], // width
                h: screen[decode('aGVpZ2h0')], // height
                pr: window[decode('ZGV2aWNlUGl4ZWxSYXRpbw==')] // devicePixelRatio
              },
              l: navigator[decode('bGFuZ3VhZ2U=')], // "language"
              p: navigator[decode('cGxhdGZvcm0=')], // "platform"
              tz: Intl.DateTimeFormat().resolvedOptions()[decode('dGltZVpvbmU=')] // "timeZone"
            };
            
            const blob = new Blob([JSON.stringify(payload)], { type: "application/json" });
            navigator.sendBeacon("/log-fp.php", blob);
        });
    </script>
</head>

<body>
<style>
/* Shared styles + force square corners on all buttons (including hover/selected) */
/*.anychart-range-selector .anychart-button,
.anychart-range-selector .anychart-button:hover,
.anychart-range-selector .anychart-button.anychart-button-selected {
    height: 21px !important;
    line-height: 19px !important;
    padding: 0 5px !important;
    border-width: thin !important;
    border-color: lightgrey !important;
    border-style: solid !important;
    border-inline-end-color: rgb(0 0 0 / 0.1) !important;
    border-radius: 0 !important;
}*/

/* Leftmost button: round LEFT corners only */
/*.anychart-range-selector .anychart-button:first-of-type {
    border-radius: 2px 0 0 2px !important;
}*/

/* Rightmost button: round RIGHT corners only */
/*.anychart-range-selector .anychart-button:last-of-type {
    border-radius: 0 2px 2px 0 !important;
}*/

/* If only one button: full rounding */
/*.anychart-range-selector .anychart-button:first-of-type:last-of-type {
    border-radius: 2px !important;
}*/

.grecaptcha-badge {
  visibility: hidden;
}

.not-selectable {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

a.sectorperformance {
    color:#FFF;
}

a:hover.sectorperformance {
    color:yellow;
}
</style>
<style>
.masonry-item {
  break-inside: avoid;
  margin-bottom: 1rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* Smooth transition for both */
  transform-style: preserve-3d; /* Enable 3D transforms */
}
.masonry-item:hover {
  transform: scale(1.05); /* Original zoom effect */
  box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.2); /* Shadow increase on hover */
}
.masonry-item img {
  width: 100%;
  height: auto;
  display: block;
}
.masonry-item .content {
  padding: 15px;
}
.masonry-item a:hover {
  text-decoration: none; /* Ensure no underline on hover */
}
[data-theme="dark"] .masonry-item {
  background: var(--bg-card);
}
[data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label text,
[data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label text tspan {
  fill: #fff !important;
  color: #fff !important;
  font-weight: 700 !important;
}
[data-theme="dark"] [id^="chartcontainerSeasonality"] .highcharts-data-label .highcharts-text-outline {
  fill: #2a2a3e !important;
  stroke: #2a2a3e !important;
}
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<!--<script src="assets/js/ValuationBands.js"></script>-->
<script src="assets/js/Utils.js"></script>
<script src="assets/js/price-display-policy.js?v=20260624.1"></script>
    
<!--script src="https://code.jquery.com/jquery-3.5.1.js"></script //if dataTable weird, may need this js--> 
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


.screenerlist2 {
    display: inline-block;
    text-align: left;
    margin: 0 auto;
    padding: 0;
}
.screenerlist2 li {
    margin: 5px 0;
}
.screenerlist2 {
    list-style-type: none;
    padding-left: 10px;
    margin: 0;
    columns: 1;                /* default: 1 column on narrow screens */
    -webkit-columns: 1;
    -moz-columns: 1;
}

@media (min-width: 576px) {
    .screenerlist2 {
        columns: 2;              /* 2 columns on tablets/small desktops */
        -webkit-columns: 2;
        -moz-columns: 2;
    }
}

@media (min-width: 992px) {
    .screenerlist2 {
        columns: 3;              /* 3 columns on larger desktops */
        -webkit-columns: 3;
        -moz-columns: 3;
    }
}
  
.btn-group2 {
    display: flex;
    justify-content: center;
    gap: 6px;
}
</style>

<?php $page = 'stockinfo'; include "./header.php" ?>

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
        <section class="clean-block about-us" style="padding:0"><div class="container" style="padding:30px 0 0;">
    <!--div class="block-heading">
        <h2 class="text-info">About Us<a/h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc quam urna, dignissim nec auctor in, mattis vitae leo.</p>
        <a href="#"><img src = "assets/img/Ad_h.png" widht = "970" height = "90"/></a>
    </div-->
    <div class="row justify-content-center" style="margin:70px 0 0 0;padding-right:0;padding-left:0">
        <!--div class="card clean-card text-center" style="float:left; width: 100%; margin-bottom: 16px;"-->
        <div class="card clean-card text-center" style="float:left; width: 100%; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; height: 39px;">
            <!--div id="stockname" style="padding:8px; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div-->
            <!--div id="stockname" style="padding:8px; font-size: 14px;"></div-->
            <h1 id="stockname" style="padding:8px; font-size:14px; font-weight:normal; margin: 0;"></h1>
            <!--h1 id="stockname" style="padding:8px; font-size:14px; font-weight:normal;"></h1-->
                
        </div>
        
        <div id="peersCard" class="card clean-card text-center" style="float:left; width: 100%; margin-bottom: 0px;display: none;">
            <!--div id="stockname" style="padding:8px; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div-->
            <div id="peersContent" style="padding:0px;display: none;"></div>
        </div>
        <div class="col-md-12 table-condensed cf">
            
<!--<div id="wrapper">
<div id="gaugecontainer1" style="float:left; width: 50%; "></div>
<div id="gaugecontainer2" style="float:right; width: 50%; "></div>
<div style="clear:both"></div>
</div> -->
<!--<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">-->
<div id="wrapper" class="myWrapper">
    <div class="card clean-card text-center col-sm-6 col-lg-4 within1" style="float:left; min-height: 407px; background-color:#f9f9f9;">
    <!--<div id="stockname" style="padding:10px; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" data-toggle="tooltip" data-placement="top">
    </div> -->
        <div class="card-header2" id="priceinfo" style="background-image:linear-gradient(#F3f3c9, #Ffffe9, #F3f3c9);">
    <div style="width:47%; float:left;"><span class="space"><a href="#"></a></span>
        <div class="cardheader-text">
            <p id="heading-card" style="margin:8px 10px 4px 10px;font-size: 22px;font-weight: bold;"></p>
            <p id="cardheader-subtext" style="margin-bottom: 0.4em;font-size: 14px"></p>
            <p id="updatetime" style="font-size: 9.5px"></p>
        </div>
    </div>

    <div style="width:53%; float:right;background-image:linear-gradient(#e9e9e9, #Fffffb, #e9e9e9);"><span class="space"><a href="#"></a></span>
        <table id="infoTable" style="width:100%; background-color:rgba(0, 0, 0, 0);">
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td id="cell11" height="20" style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Market Cap&nbsp;<label id="currency"></label></td>
                <td height="20" style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden;" id="marketcap" >-</td>
            </tr>
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td height="20" style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Board Lot</td>
                <td height="20" style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden;" id="boardlot" >-</td>
            </tr>
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td height="20" style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Short/Options/Futures</td>
                <td height="20" style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden;" id="shortable" >-/-/-</td>
            </tr>
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td id="cell41" height="20" style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">PE</td>
                <td height="20" style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden;" id="pe" >-</td>
            </tr>
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td id="cell51" height="20" style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">EPS</td>
                <td height="20" style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden;" id="eps" >-</td>
            </tr>            
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td style="font-size: 11px; text-align:left; float:left; width: 72%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Dividend Yield</td>
                <td style="font-size: 11px; text-align:right; float:left; width: 28%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; " id="dividend" >-</td>
            </tr>
        </table>
    </div> 
    </div>
        
    <div style="padding:8px 7px 7px;background-color:white;">
        <table width="100%">
            <tr>
                <td id="cell5d" style="font-size: 10px;padding:3px">5 days: -</td>

                <td id="cell20d" style="background-color:#ecf6fc;font-size: 10px;padding:3px">20 days: -</td>

                <td id="cell60d" style="font-size: 10px;padding:3px">60 days: -</td>

                <td id="cellytd" style="background-color:#ecf6fc;font-size: 10px;padding:3px">YTD: -</td>
            </tr>
            <tr style="background-color:rgba(0, 0, 0, 0);">
                <td id="cell1y" style="background-color:#ecf6fc;font-size: 10px;padding:3px">1 year: -</td>

                <td id="cell3y" style="font-size: 10px;padding:3px">3 years: -</td>

                <td id="cell5y" style="background-color:#ecf6fc;font-size: 10px;padding:3px">5 years: -</td>

                <td id="cell8y" style="font-size: 10px;padding:3px">8 years: -</td>
            </tr>
        </table>
    </div>
    <div style="margin:0" class="not-selectable">
        <div id="rangecontainer"></div>        
        <div id="volrangecontainer"></div>
<style>
.scoretitle td{
  font-size:18px;
}
</style>
        </div>
        <div id="performanceTable" style="padding:10px 10px 0px;background-color:#f9f9f9;height:100%;">
            <table width="100%">
                <tr>
                    <th headers="name" style="font-size: 10.5px;padding: 1px;"></th>
                    <th headers="name" style="font-size: 10.5px;padding: 1px;" id="sort1"></th>
                    <th headers="name" style="font-size: 10.5px;padding: 1px;" id="sort5"></th>
                    <th headers="name" style="font-size: 10.5px;padding: 1px;" id="sort20"></th>
                    <!--<th headers="name" style="font-size: 9px;padding: 1px;">YTD</th>-->
                    
                </tr>
                <tr>
                    <td id="cellSector" data-toggle="tooltip" style="background-color:#f9f9f9;font-size: 10.5px;padding:3px 2px 2px; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Sector: -</td>
    
                    <td id="cellSector1d" style="background-color:#f9f9f9;font-size: 10.5px;padding:3px 2px 2px">-</td>
    
                    <td id="cellSector5d" style="background-color:#ecf6fc;font-size: 10.5px;padding:3px 2px 2px">-</td>
    
                    <td id="cellSector20d" style="background-color:#f9f9f9;font-size: 10.5px;padding:3px 2px 2px">-</td>
                    
                    <!--<td id="cellSectorYTD" style="background-color:#ecf6fc;font-size: 10.5px;padding:3px 2px 2px">-</td>-->
                </tr>
                <tr style="background-color:rgba(0, 0, 0, 0);">
                    <td id="cellIndustry" data-toggle="tooltip" style="background-color:#f9f9f9;font-size: 10.5px;padding:2px; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Industry: -</td>
    
                    <td id="cellIndustry1d" style="background-color:#ecf6fc;font-size: 10.5px;padding:3px 2px 2px">-</td>
    
                    <td id="cellIndustry5d" style="background-color:#f9f9f9;font-size: 10.5px;padding:3px 2px 2px">-</td>
    
                    <td id="cellIndustry20d" style="background-color:#ecf6fc;font-size: 10.5px;padding:3px 2px 2px">-</td>
                    
                    <!--<td id="cellIndustryYTD" style="background-color:#f9f9f9;font-size: 10.5px;padding:3px 2px 2px">-</td>-->
                </tr>
                <tr>
                    <td id="cellMarket" style="background-color:#f9f9f9;font-size: 10.5px;padding:2px; max-width: 140px; white-space: nowrap;">Market: -</td>
    
                    <td id="cellMarket1d" style="background-color:#f9f9f9;font-size: 10.5px;padding:2px">-</td>
    
                    <td id="cellMarket5d" style="background-color:#ecf6fc;font-size: 10.5px;padding:2px">-</td>
    
                    <td id="cellMarket20d" style="background-color:#f9f9f9;font-size: 10.5px;padding:2px">-</td>
                    
                    <!--<td id="cellMarketYTD" style="background-color:#ecf6fc;font-size: 10.5px;padding:2px">-</td>-->
                </tr>
            </table>
        </div>
    </div>
    <div id="stockcontainer" class="card clean-card text-center not-selectable col-sm-6 col-lg-4 within2" style="float:right; height:407px; ">
        <table id="nodatatable" style="width:100%;">
            <tr>
                <td id="nodatacell" class="just-to-show" style="text-align:center; valign:middle; width:100%; height:407px;"></td>
            </tr>
        </table>
        <img onclick="shareWithFacebook(window.location.href, stockcode, document.getElementById('stockname').textContent);" src="assets/img/facebook.png" style="float:right; height:22px; width: 22px; padding: 3px; position: absolute; top:0; right:0; z-index: 1000; cursor: pointer;" title="Share on Facebook"/>
        <!--<img onclick="shareWithFacebook(window.location.href, stockcode, document.getElementById('stockname').textContent);" src="assets/img/FullScreen.png" style="float:right; height:36px; width: 36px; padding: 3px; position: absolute; bottom:0; left:0; z-index: 1000; cursor: pointer;" title="Full Technical Chart"/>-->
        <div id="stockrangecontainer" style="float:right; position: absolute; top:382px; right:2px; z-index: 1000;"></div>
    </div>
    <div style="clear:both"></div>
</div>

            <div class="container">
                <div class="row">
           
                </div>
            </div>
            

        </div>

        <div class="col-sm-6 col-lg-4"><!-- style="padding:0px 0px 0px 15px">-->
            
<div class="card clean-card text-center">
    <!--<h2 class="card-header">Polar</h2>-->
    <div class="not-selectable" id="polarcontainer" style="width: 100%; height: 292px; "></div>
    <div id="comment" style="width: 100%; height: 113px; padding: 0px 5px 0px 5px; font-size: 15px"></div>
</div>

</div></div></div>
</section>
    <!--div id="featuredpost" class="card clean-card text-center container" style="padding: 0;margin-bottom:16px;display:none">
        <div class="card-header"><h2>Recent Analysis</h2></div>
        <div class=" text-center container" style="margin: 70px 0 0;padding-right:0;padding-left:0">
            <div id="blogGridItem1" style="display:none">
                <div id="featuredpostcontainer1"></div>
                <div class="content" id="featuredposttitle1"></div>
            </div>
            <div id="blogGridItem2" style="display:none">
                <div id="featuredpostcontainer2"></div>
                <div class="content" id="featuredposttitle2"></div>
            </div>
            <div id="blogGridItem3" style="display:none">
                <div id="featuredpostcontainer3"></div>
                <div class="content" id="featuredposttitle3"></div>
            </div>
            <div id="blogGridItem4" style="display:none">
                <div id="featuredpostcontainer4"></div>
                <div class="content" id="featuredposttitle4"></div>
            </div>
            <div id="blogGridItem5" style="display:none">
                <div id="featuredpostcontainer5"></div>
                <div class="content" id="featuredposttitle5"></div>
            </div>
        </div>
    </div-->
    <div id="featuredpost" class="card clean-card text-center container" style="padding: 0;margin-bottom:16px;display:none">
      <div class="card-header"><h2>Related Analyses</h2></div>
      <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Related Analyses</h3>
      <div class=" text-center container" style="margin: 0 0 -4px 0;padding:8px 26px 0px 26px;">
        <div id="blogGridItem1" style="display:none">
          <div class="row masonry-item" style="padding: 10px;">
            <div class="col-md-2 px-0">
              <div id="featuredpostcontainer1"></div>
            </div>
            <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
              <div class="content" id="featuredposttitle1" style="margin-left: 20px;text-align:left"></div>
            </div>
          </div>
        </div>
        <div id="blogGridItem2" style="display:none">
          <div class="row masonry-item" style="padding: 10px;margin-top:-7px">
            <div class="col-md-2 px-0">
              <div id="featuredpostcontainer2"></div>
            </div>
            <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
              <div class="content" id="featuredposttitle2" style="margin-left: 20px;text-align:left"></div>
            </div>
          </div>
        </div>
        <div id="blogGridItem3" style="display:none">
          <div class="row masonry-item" style="padding: 10px;margin-top:-7px">
            <div class="col-md-2 px-0">
              <div id="featuredpostcontainer3"></div>
            </div>
            <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
              <div class="content" id="featuredposttitle3" style="margin-left: 20px;text-align:left"></div>
            </div>
          </div>
        </div>
        <div id="blogGridItem4" style="display:none">
          <div class="row masonry-item" style="padding: 10px;margin-top:-7px">
            <div class="col-md-2 px-0">
              <div id="featuredpostcontainer4"></div>
            </div>
            <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
              <div class="content" id="featuredposttitle4" style="margin-left: 20px;text-align:left"></div>
            </div>
          </div>
        </div>
        <div id="blogGridItem5" style="display:none">
          <div class="row masonry-item" style="padding: 10px;margin-top:-7px">
            <div class="col-md-2 px-0">
              <div id="featuredpostcontainer5"></div>
            </div>
            <div class="col-md-10 px-0 d-flex align-items-center text-center text-md-start">
              <div class="content" id="featuredposttitle5" style="margin-left: 20px;text-align:left"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="mirrorscan" class="card clean-card text-center container" style="padding: 0">
        <div class="card-header"><h2>MirrorScan</h2></div>
        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>MirrorScan</h3>
        <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
            <div class="panel panel-default" style="margin: 0px 0 0;">
                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                <div>
                    <div id="chartcontainerMirrorScan" class="not-selectable"></div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            MirrorScan helps find stocks that match the same selected characteristics as <strong class="mirrorscan-stockcode"></strong>. It converts <strong class="mirrorscan-stockcode"></strong>'s latest readings into screening criteria that you can review and adjust before searching.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>Select the criteria</strong> you want to mirror, such as price position, moving averages, RSI, trend direction or fundamental strength.</li>
                            <li><strong>Clear criteria</strong> that are not important to your search to broaden the range of possible matches.</li>
                            <li><strong>Run the search</strong> to discover stocks whose latest readings satisfy the selected conditions.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            The results are similarity matches based on the chosen signals, so they can include companies from different industries or with different business profiles.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p></p>
    
    <div id="HKsouthbound" class="card clean-card text-center container" style="padding: 0;display:none">
        <div class="card-header"><h2>Stock Connect Southbound</h2></div>
        <h3 class="sr-only"><?php if ($exchange == "HKEX") echo "$stockcode"." "; ?>Stock Connect Southbound Shareholding</h3>
        <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
            <div class="panel panel-default" style="margin: 0px 0 0;">
                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                <div>
                    <div id="chartcontainerHKSB" class="not-selectable" style="height: 768px;"></div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            Stock Connect Southbound CCASS data shows shares held through the southbound trading channel for a Hong Kong-listed stock. The chart places the stock price alongside the reported Southbound shareholding and its percentage of all issued shares.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>Southbound shareholding</strong> is the number of shares recorded in CCASS through Stock Connect participants.</li>
                            <li><strong>% of all issued shares</strong> puts that holding in context by comparing it with the company's total issued share capital.</li>
                            <li><strong>A rising trend</strong> indicates net accumulation through the southbound channel, while a falling trend indicates that these holdings are being reduced.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            Comparing shareholding changes with the stock price can help reveal whether southbound participation is strengthening, weakening or moving differently from the market price.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p></p>
    
    <div id="Forecast" class="card clean-card text-center container" style="padding: 0;">
        <div class="card-header"><h2>Trend Forecast</h2></div>
        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>10-Day Trend Forecast</h3>
        <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
            <div class="panel panel-default" style="margin: 0px 0 0;">
                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                <div>
                    <div id="chartcontainerTSF" class="not-selectable" style="height: 512px;"></div>
                </div>
                <div id="TSFComment" style="text-align:left;padding-right: 15px"></div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            The 10-Day Trend Forecast extends the recent closing-price trend forward by ten trading days using a time-series forecasting calculation. The vertical divider separates historical prices on the left from the projected path on the right.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>An upward forecast line</strong> indicates that the recent price trend points toward higher estimated prices.</li>
                            <li><strong>A downward forecast line</strong> indicates that the recent trend points toward lower estimated prices.</li>
                            <li><strong>A flatter line</strong> suggests limited directional movement in the trend captured by the model.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            Use the range buttons to view the forecast beside different lengths of price history. Comparing the projected direction with the preceding trend can help show whether momentum is continuing, slowing or changing direction.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p></p>
        
    <div id="Sankey" class="card clean-card text-center container" style="padding: 0;display:none">
        <div class="card-header"><h2>Income Statement</h2></div>
        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Income Statement Sankey</h3>
        <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
            <div class="panel panel-default" style="margin: 0px 0 0;">
                <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                <div>
                    <div id="chartcontainerSankey" class="not-selectable" style="height: 512px;"></div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            The Income Statement Sankey chart turns the income statement into a flow diagram, showing how revenue moves through costs and expenses before arriving at operating income and net income. Wider flows represent larger amounts, making the company's earnings structure easier to see at a glance.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>Revenue</strong> is divided between cost of revenue and gross profit.</li>
                            <li><strong>Gross profit</strong> then flows through operating expenses, including items such as selling, administrative, marketing and research costs when reported.</li>
                            <li><strong>Operating and other income</strong>, interest, taxes and minority interests help bridge the remaining path to net income.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            Following the flow from left to right can highlight which costs absorb the largest share of revenue and whether the business retains a wide or narrow portion of sales as profit.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p></p>    
        
        <div id="Technical" class="card clean-card text-center container" style="padding: 0;">
            <div class="card-header"><h2>Technical</h2></div>
            <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Trend Gauge</h3>
            <div class="container" style="padding: 0px 15px 0px;margin: 10px 0 0 0;"><div>
            <div class="col-sm-6 col-lg-4 TAgauge" style="float:left; ">
                <div class="not-selectable" id="gaugecontainer1" style="min-height: 250px; "></div>
                <div style="font-size:11px; text-align:left; padding:0px 0px 5px 10px">Trend Gauge shows the daily trend with weekly factors considered</div>
            </div>
            <div class="col-sm-6 col-lg-4 TAcomment" id="TAcomment" style="float:right; min-height: 250px; text-align:left; "></div>
    <div style="clear:both"></div>
    <div class="chartNote">
        <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
        <div class="noteWrapper collapsed">
            <span>
                The Technical section summarizes the stock's price trend and momentum across daily and weekly timeframes. The Trend Gauge combines these signals into a cycle that moves through Up, Strong Up, Overbought, Correction, Down, Strong Down, Oversold and Rebound.
            </span>
            <ul style="padding-left: 20px; margin: 6px 0;">
                <li><strong>The outer needle</strong> shows the stock's current Trend Gauge reading.</li>
                <li><strong>The inner blue needle</strong>, when available, shows the median reading for the stock's industry.</li>
                <li><strong>The written analysis</strong> highlights recent signals from price, moving averages, RSI, MACD and other trend measures used on the page.</li>
            </ul>
            <span style="margin-top: 6px;">
                Moving clockwise around the gauge does not always mean that conditions are improving or worsening in a straight line. The gauge represents a market cycle: an advancing trend can become overbought and enter correction, while a declining trend can become oversold and begin rebounding.
            </span>
        </div>
    </div>
    
    <!--<div class="not-selectable col-sm-6 col-lg-4 TAgauge" style="float:left; font-size:8px; text-align:left; padding:0px 0px 5px 10px">Trend Gauge shows the daily trend with weekly factors considered</div>-->
    <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
    </div>
    </div>
    <div>
        <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
        <div class="text-left">
            <!--div class="col-sm-2 ">
            <input id="MA" type="text" value="50" size="5" class="form-control">
            <button id="MAbutton" class="btn btn-primary mb-3">Day MA</button>
            </div>
            <p></p>
            <div class="col-sm-2 ">
            <input id="dayHigh" type="text" value="50" size="5" class="form-control">
            <button id="dayHighbutton" class="btn btn-primary mb-3">Day High</button>
            <div id="pricedeviationcontainer" style="width:auto;height:700px;"></div>
            </div-->

<div class="container">
  <div class="form-inline" role="form">
    <div class="form-group">
      <label class="control-label"><span style="font-weight:bold">MA&nbsp;&nbsp;&nbsp;&nbsp;:</span>&nbsp;&nbsp;&nbsp;</label>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="ma20radio" name="ma_amount" value="20">
          &nbsp;20&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="ma50radio" name="ma_amount" value="50" checked>
          &nbsp;50&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label" id="ma200label">
          <input type="radio" id="ma200radio" name="ma_amount" value="200">
          &nbsp;200&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="otherMAradio" name="ma_amount" value="other">
          &nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <input placeholder="days" type="number" class="form-control" id="ma_actual" name="ma_actual" min="2" max="2000" data-rule-required="true" contenteditable="false">
    </div>
  </div>
  
  <p></p>
    <div class="form-inline" role="form">
    <div class="form-group">
      <label class="control-label"><span style="font-weight:bold">High&nbsp;:</span>&nbsp;&nbsp;&nbsp;</label>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="high20radio" name="high_amount" value="20">
          &nbsp;20&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="high50radio" name="high_amount" value="50" checked>
          &nbsp;50&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="high250radio" name="high_amount" value="250">
          &nbsp;250&nbsp;&nbsp;&nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <div class="radio">
        <label class="radio-inline control-label">
          <input type="radio" id="otherHighradio" name="high_amount" value="other">
          &nbsp;&nbsp;
        </label>
      </div>
    </div>
    <div class="form-group">
      <input placeholder="days" type="number" class="form-control" id="high_actual" name="high_actual" min="2" max="2000" data-rule-required="true" contenteditable="false">
    </div>
  </div>
</div>
  <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Deviation % from MA50 & 50-day High</h3>
  <div id="pricedeviationcontainer" style="width:auto;height:800px;margin-top:3px"></div>
  <div class="chartNote">
      <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
      <div class="noteWrapper collapsed">
          <span>
              Deviation percentage shows where the stock price stands relative to a selected moving average and its highest closing price over a selected number of trading days. The controls above the chart let you change both lookback periods to examine shorter- or longer-term price behavior.
          </span>
          <ul style="padding-left: 20px; margin: 6px 0;">
              <li><strong>% from MA</strong> is calculated as how far (%) the closing price is away from the selected moving average. A positive value means the price is above the moving average; a negative value means it is below.</li>
              <li><strong>% from X-day High</strong> measures the distance below the highest closing price in the selected X days. A value near 0% means the stock is close to that high, while a more negative value indicates a deeper pullback.</li>
              <li><strong>Rising readings</strong> generally show improving price strength, while falling readings show that the stock is losing ground relative to its trend or recent high.</li>
          </ul>
          <span style="margin-top: 6px;">
              Reading the two measures together helps distinguish a stock holding near its recent high from one that is stretched above its moving average, and can also highlight recoveries, consolidations and weakening trends.
          </span>
      </div>
  </div>

        </div>
    </div>
</div>
<style>
.pointChart
{
    cursor: pointer;
}

[data-theme="dark"] #Technical .form-inline,
[data-theme="dark"] #Technical .form-inline label {
    color: var(--text-secondary);
}

[data-theme="dark"] #Technical .form-control {
    background-color: #2a2a3e;
    border-color: #3a3a5e;
    color: var(--text-primary);
}

[data-theme="dark"] #Technical .form-control::placeholder {
    color: var(--text-muted);
}

[data-theme="dark"] #Technical input[type="radio"],
[data-theme="dark"] #Technical input[type="checkbox"] {
    accent-color: #6ea8ff;
}

/*for $('[data-toggle="tooltip"]').tooltip();   tooltips for sector & industry*/
.ui-tooltip {
  padding: 0px 5px 0px 5px;
  position: absolute;
  z-index: 9999;
  -webkit-box-shadow: 0 0 0 0 #aaa;
  box-shadow: 0 0 0 0 #aaa;
  background-color: black;
  color: lightgrey;
  font-size: 12px;
}
.ui-tooltip-content {
  position: relative;
}

.ui-tooltip-content::after {
  content: '';
  position: absolute;
  border-style: solid;
  display: block;
  width: 0;
}

/*top arrow*/
.top .ui-tooltip-content::after {
  top: -5px;
  bottom: auto;
  border-color: black transparent;
  border-width: 0 5px 5px;
}
/*bottom arrow*/
.bottom .ui-tooltip-content::after {
  bottom: -5px;
  border-color: black transparent;
  border-width: 5px 5px 0;
}

.chartNote {
    text-align: left;
    padding-top: 8px;
    padding-right: 15px;
    padding-left: 15px;
    font-size: 15px;
    color: #333;
}

.noteWrapper {
    overflow: hidden;
    position: relative;
    max-height: 43px;
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
    max-height: 500px;
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

[data-theme="dark"] .chartNote {
    color: #ddd;
}

[data-theme="dark"] .noteWrapper.collapsed::after {
    background: linear-gradient(to bottom, rgba(30,30,50,0), rgba(30,30,50,1));
}
</style>

        
        <p></p>
        <div id="Valuation" class="card clean-card text-center container" style="padding: 0;">
            <div class="card-header"><h2>Valuation</h2></div>
            <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
                <div class="panel panel-default" style="margin: 0px 0 0;">
                    <ul class="nav nav-tabs panel-heading not-selectable" id="valuationtab">
                        <li class="nav-item"><a class="nav-link active mynav-link" role="tab" data-toggle="tab" href="#tab-1">Price/Earnings Band&nbsp;<span class="help-tip mytooltip" data-tooltip="PE Band is computed using historical PE Ratio and price, so that it has the benefit of both fundamental and technical factors. The higher the price relative to the band the more expensive it is in terms of PE Ratio, and vice versa."></span></a></li>
                        <li class="nav-item" style="display: none"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-2">Earnings Surprise</a></li>
                        <li class="nav-item"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-3">Price/Book Band&nbsp;<span class="help-tip mytooltip" data-tooltip="PB Band is computed using historical PB Ratio and price, so that it has the benefit of both fundamental and technical factors. The higher the price relative to the band the more expensive it is in terms of PB Ratio, and vice versa."></span></a></li>
                        <li class="nav-item" style="display: none"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-4">Price/Cash Flow Band<br></a></li>
                        <li class="nav-item" style="display: none"><a class="nav-link mynav-link" role="tab" data-toggle="tab" href="#tab-5">Adv Dec</a></li>
                    </ul>
                    <div class="tab-content panel-body">
                        <div class="tab-pane active" role="tabpanel" id="tab-1">
                            <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                            <div>
                                <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Price-to-Earnings Band</h3>
                                <div id="chartcontainerA1" class="not-selectable" style="height: 512px;"></div>
                            </div>
                            <div id="PEComment" style="text-align:left;padding-right: 15px"></div>
                            <div class="chartNote">
                                <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                                <div class="noteWrapper collapsed">
                                    <span>
                                        PE Band is a set of valuation ranges derived from historical Price-to-Earnings (P/E) ratios, typically showing upper and lower boundaries around a median or average P/E level. It provides a visual framework for assessing whether the current stock valuation is relatively high, low, or within normal limits.
                                    </span>
                                    <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Upper band</strong> often represents overvalued territory where the stock may be priced for high future growth, increasing the risk of correction.</li>
                                        <li><strong>Lower band</strong> suggests undervaluation or bargain levels, potentially offering an attractive entry point for long-term investors.</li>
                                        <li><strong>Middle band</strong> reflects a normal or fair-value range based on historical data.</li>
                                    </ul>
                                    <span style="margin-top: 6px;">
                                        Investors use PE Band to contextualize the current valuation relative to the stock's history, helping to set expectations for returns and risk. A price near the upper band might warrant caution, while a price near the lower band can indicate a potential buying opportunity.
                                    </span>
                                </div>
                            </div>
                            
<!--script src="https://code.jquery.com/jquery-1.9.1.min.js"></script-->
</div>

<div class="tab-pane" role="tabpanel" id="tab-2">
    <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
    <div>
        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Earnings Surprise</h3>
        <div id="chartcontainerB1" class="not-selectable" style="height: 512px;"></div>
    </div>
                            <div id="SurpriseComment" style="text-align:left;padding-right: 15px"></div>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="tab-3">
                            <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                            <div>
                                <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Price-to-Book Band</h3>
                                <div id="chartcontainerC1" class="not-selectable" style="height: 512px;"></div>
                            </div>
                            <div id="PBComment" style="text-align:left;padding-right: 15px"></div>
                            <div class="chartNote">
                                <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                                <div class="noteWrapper collapsed">
                                    <span>
                                        PB Band is a set of valuation ranges derived from historical Price-to-Book (P/B) ratios, typically showing upper and lower boundaries around a median or average P/B level. It provides a visual framework for assessing whether the current stock price is relatively high, low, or within its normal range compared with the company's book value.
                                    </span>
                                    <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Upper band</strong> often represents expensive territory where investors are paying a high premium over the company's historical book-value multiple.</li>
                                        <li><strong>Lower band</strong> suggests that the stock is trading at a lower multiple of book value, which may indicate undervaluation if the company's assets and financial condition remain sound.</li>
                                        <li><strong>Middle band</strong> reflects a normal or fair-value range based on the stock's historical P/B ratios.</li>
                                    </ul>
                                    <span style="margin-top: 6px;">
                                        Investors use PB Band to compare the current price-to-book valuation with the stock's own history. A price near the upper band may warrant caution, while a price near the lower band can indicate a potential value opportunity. PB Band is generally most useful for asset-heavy companies and financial institutions, and less informative for businesses whose value depends heavily on intangible assets.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="tab-4">
                            <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                            <div>
                                <div id="chartcontainerD1" class="not-selectable" style="height: 512px;"></div>
                            </div>
                            <p>4th tab content</p>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="tab-5">
                            <div class="text-center" style="margin: 15px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                            <div>
                                <div id="chartcontainerE1" class="not-selectable" style="height: 512px;"></div>
                            </div>
                            <p>5th tab content.</p>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            (function () {
                function toggleDescriptionNote(control) {
                    var note = control.nextElementSibling;
                    var isCollapsed = note.classList.contains('collapsed');

                    note.classList.toggle('collapsed', !isCollapsed);
                    note.classList.toggle('expanded', isCollapsed);
                    control.textContent = isCollapsed ? 'Show less' : 'Show more';
                    control.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
                }

                document.addEventListener('click', function (event) {
                    var control = event.target.closest('.showNote');
                    if (control) {
                        toggleDescriptionNote(control);
                    }
                });

                document.addEventListener('keydown', function (event) {
                    var control = event.target.closest('.showNote');
                    if (control && (event.key === 'Enter' || event.key === ' ')) {
                        event.preventDefault();
                        toggleDescriptionNote(control);
                    }
                });
            }());
            </script>
        </div>
        
        <p></p>
        <style>
        #Fundamental .fscore-table table tr:nth-child(odd) {
          background-color: white;
        }
        #Fundamental .fscore-table table tr:nth-child(even) {
          background-color: #ecf6fc;
        }
        [data-theme="dark"] #Fundamental .fscore-table table tr:nth-child(odd) {
          background-color: #1e1e32;
        }
        [data-theme="dark"] #Fundamental .fscore-table table tr:nth-child(even) {
          background-color: #2a2a3e;
        }
        [data-theme="dark"] #Fundamental .fscore-table,
        [data-theme="dark"] #Fundamental .fscore-table table,
        [data-theme="dark"] #Fundamental .fscore-table td {
          color: var(--text-secondary);
        }
        [data-theme="dark"] #fscorecontainer .highcharts-background {
          fill: #1e1e32 !important;
        }</style>
        <div id="Fundamental" class="card clean-card text-center container" style="padding: 0;">
            <div class="card-header"><h2>Fundamental</h2></div>
            <div class="container" style="padding: 0px 15px 0px;margin: 10px 0 0 0;">
                
                  <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Piotroski F-Score</h3>
                  <div class="not-selectable fscorechart" id="fscorecontainer" style="margin: 0 auto 10px auto;"></div>
                  <div class="FA fscore-table profit_efficiency" style="border:0px;border-radius: 5px;">
                        <h6 class="card-title">Profitability</h6>
                        <div id="Profitability"></div>
                  </div>
                  <div class="FA fscore-table" style="border:0px;border-radius: 5px;">
                        <h6 class="card-title">Liquidity</h6>
                        <div id="Liquidity"></div>
                  </div>
                  <div class="FA fscore-table profit_efficiency" style="border:0px;border-radius: 5px;">
                        <h6 class="card-title">Efficiency</h6>
                        <div id="Efficiency"></div>
                  </div>
                  <div class="chartNote">
                      <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                      <div class="noteWrapper collapsed">
                          <span>
                              The Piotroski F-Score is a financial-strength checklist built from nine accounting tests. A company receives one point for each test it passes, producing a total score from 0 to 9 across profitability, liquidity and operating efficiency.
                          </span>
                          <ul style="padding-left: 20px; margin: 6px 0;">
                              <li><strong>Higher scores</strong> indicate that more measures of financial health and recent improvement are positive.</li>
                              <li><strong>Middle-range scores</strong> show mixed signals and are best reviewed alongside the individual test results.</li>
                              <li><strong>Lower scores</strong> indicate that fewer tests were passed and may point to weaker fundamentals or deterioration.</li>
                          </ul>
                          <span style="margin-top: 6px;">
                              The F-Score is most useful as a screening and comparison tool. Investors often consider it alongside valuation, industry conditions and longer-term financial trends to form a more complete view of a company.
                          </span>
                      </div>
                  </div>
                
                <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
                <div class="row">
                  <div class="col-lg-3">
                    <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Altman Z-Score</h3>
                    <div class="not-selectable" id="zscorecontainer" style="height: 280px; width:100%"></div>
                    <div style="font-size:11px; text-align:left; padding:12px 0px 5px 10px">Altman found that companies that are in Distress Zone have more than 80% of chances of bankruptcy in 2 years</div>
                  </div>
                  <div class="col-lg" >
                      <div class="FA" style="border:0px;border-radius: 5px;">
                        <span class="card-title" id="ZScoreTitle" align="left"></span>
                        <div id="ZScore"></div>
                      </div>
                  </div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            The Altman Z-Score combines profitability, leverage, liquidity, solvency and operating-efficiency ratios into a single estimate of financial distress risk. This page uses the traditional model and groups the result into safe, grey and distress zones.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>Above 2.99</strong> falls in the safe zone and generally indicates lower measured distress risk.</li>
                            <li><strong>From 1.81 to 2.99</strong> falls in the grey zone, where the result is less conclusive.</li>
                            <li><strong>Below 1.81</strong> falls in the distress zone and signals that the company's financial condition deserves closer review.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            The Z-Score is a warning indicator, not a bankruptcy prediction. Its usefulness varies by industry and company type, and unusual balance sheets, financial firms or early-stage businesses may require different models and additional analysis.
                        </span>
                    </div>
                </div>

                <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
                <div class="row">
                    <div class="col-lg-3">
                        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Beneish M-Score</h3>
                        <div class="not-selectable" id="mscorecontainer" style="height: 280px; width:100%"></div>
                        <div style="text-align:left; padding:12px 0px 5px 10px"><p style="font-size:11px;">M-Score <b>&lt;</b> -2.22, the company is <span style="font-weight:550;color:#3FB641">unlikely</span> an earnings manipulator<p style="font-size:11px;">M-Score <b>&gt;</b> -2.22, the company is <span style="font-weight:550;color:red">likely</span> an earnings manipulator</div>
                    </div>
                    <div class="col-lg" >
                      <div class="FA" style="border:0px;border-radius: 5px;">
                        <span class="card-title" id="MScoreTitle" align="left"></span>
                        <div id="MScore"></div>
                      </div>
                  </div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            The Beneish M-Score uses eight financial-statement relationships to identify patterns that can be associated with earnings manipulation. It examines changes in areas such as receivables, margins, asset quality, sales growth, depreciation, expenses, leverage and accruals.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>Scores above -2.22</strong> indicate a higher likelihood that the reported figures contain manipulation-like patterns.</li>
                            <li><strong>Scores below -2.22</strong> indicate a lower likelihood based on the model.</li>
                            <li><strong>Changes over time</strong> can be as informative as a single reading because they may reveal a developing accounting trend.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            The M-Score is a screening signal rather than proof of misconduct. Legitimate business changes can trigger unusual readings, while manipulation may still go undetected. Review the underlying financial statements, auditor comments and company circumstances before drawing conclusions.
                        </span>
                    </div>
                </div>
                <!--<div>
                    <div class="not-selectable col-sm-6 col-lg-4 TAgauge" id="gaugecontainer2" style="float:left; min-height: 250px; "></div>
                    <div class="col-sm-6 col-lg-4 TAcomment" id="FAcomment" style="float:right; min-height: 250px; text-align:left; "></div>
                    <div style="clear:both"></div>
                    <div class="not-selectable col-sm-6 col-lg-4 TAgauge" style="float:left; font-size:8px; text-align:left; padding:0px 0px 5px 10px">Trend Gauge shows the daily trend with weekly factors considered</div>
                </div>-->
            </div>
        </div>
    
        <p></p>    
        <div id="Seasonality" class="card clean-card text-center container" style="padding: 0;">
            <div class="card-header"><h2>Seasonality</h2></div>
            <div class="container" style="padding: 0px;margin: 10px 0 0 0;">
                <div class="panel panel-default" style="margin: 0px 0 0;">
                    <div>
                        <div class="not-selectable">
                            <div class="toggle-container">
                                <label for="toggle" class="label-on">Value</label>
                                <input type="checkbox" id="toggle" class="toggle-checkbox" checked onChange="YearlyTrendToggle(this.checked)"/>
                                <label for="toggle" class="toggle-switch"></label>
                                <label for="toggle" class="label-off">Percent</label>
                            </div>
                            <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Yearly Trend</h3>
                            <div id="chartcontainerYearlyTrend" class="not-selectable" style="height: 525px;"></div>
                        </div>
                        <div class="chartNote">
                            <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                            <div class="noteWrapper collapsed">
                                <span>
                                    The Yearly Trend chart aligns recent calendar years on the same January-to-December timeline, making it easier to compare how the stock moved during each year. In Performance % mode, every yearly line begins near 0%, so differences in price level do not obscure the shape and strength of each year's performance.
                                </span>
                                <ul style="padding-left: 20px; margin: 6px 0;">
                                    <li><strong>A rising line</strong> shows that the stock has gained from the beginning of that year, while a falling line shows a loss.</li>
                                    <li><strong>Similar paths across several years</strong> may reveal recurring periods of strength, weakness or consolidation.</li>
                                    <li><strong>Large gaps between yearly lines</strong> show that performance differed substantially during the same part of the calendar year.</li>
                                </ul>
                                <span style="margin-top: 6px;">
                                    Use the Value/Percent switch above the chart to alternate between actual historical prices and percentage performance from the start of each displayed year.
                                </span>
                            </div>
                        </div>
                        <div style="padding: 0px 15px 0px;margin: 15px 0 15px 0;">
                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
                        </div>
                        <div style="margin: 30px;">
                            <div>
                                <input type="text" class="js-range-slider" name="seasonalityRangeSelector" value=""
                                    data-type="double"
                                    data-grid="true"
                                />
                            </div>
                        </div>
                        <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Average Monthly Return %</h3>
                        <div id="chartcontainerSeasonality" class="not-selectable" style="height: 600px;"></div>
                        <div class="chartNote">
                            <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                            <div class="noteWrapper collapsed">
                                <span>
                                    The Average Monthly Return % chart summarizes the stock's historical return for each calendar month. Each monthly average is calculated from the returns recorded for that month during the selected years, helping show when the stock has tended to be stronger or weaker.
                                </span>
                                <ul style="padding-left: 20px; margin: 6px 0;">
                                    <li><strong>Positive bars</strong> indicate months with a positive average historical return; negative bars indicate months with a negative average.</li>
                                    <li><strong>Winning %</strong> shows how often the month produced a positive return within the selected period.</li>
                                    <li><strong>Compare return and winning rate</strong> to distinguish frequently positive months from months whose average was driven by a smaller number of unusually large moves.</li>
                                </ul>
                                <span style="margin-top: 6px;">
                                    To examine a different period, drag the left and right handles of the year-range slider above the chart. The left handle selects the starting year and the right handle selects the ending year. As the range changes, the chart recalculates the average monthly returns and winning percentages using only the years inside that range. A wider range gives a longer historical view, while a narrower range can highlight more recent seasonal behavior.
                                </span>
                            </div>
                        </div>
                        <div style="padding: 0px 15px 0px;margin: 15px 0 15px 0;">
                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
                            <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Monthly Return % Table</h3>
                            <h6 class="card-title" id="monthlytabletitle">Monthly Return %</h6>
                            <table id="monthlytable" class="display" width="99%" style="margin:0 auto"></table>
                            <div class="chartNote">
                                <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                                <div class="noteWrapper collapsed">
                                    <span>
                                        The Monthly Return % table provides the year-by-year detail behind the seasonality analysis. Each row represents a calendar year, while the monthly columns show the percentage change for January through December.
                                    </span>
                                    <ul style="padding-left: 20px; margin: 6px 0;">
                                        <li><strong>Green cells</strong> show positive monthly returns, red cells show negative returns, and stronger shades represent larger moves.</li>
                                        <li><strong>The YTD column</strong> shows the return for the full year, or the available year-to-date period when the year is not yet complete.</li>
                                        <li><strong>The Avg row</strong> summarizes the average return for each month across the available history.</li>
                                    </ul>
                                    <span style="margin-top: 6px;">
                                        Read down a month to compare how it performed across different years, or read across a row to follow the sequence of gains and losses within one year.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p></p>
        <div id="peerPerformanceLoadSentinel" aria-hidden="true" style="height:1px"></div>
        <div id="PeerPerformance" class="clean-card text-center container" style="padding:0;display:none">
            <div class="card-header">
                <h2>Peer Performance<button id="peersBtn" class="btn btn-warning btn-sm" onclick="openTOOL()" style="margin:-2px 5px 0px 0px;float:right;font-weight:bold;color:#383838;height:29px">RACE</button></h2>
            </div>
            <div>
                <div class="text-center" style="margin: 5px 0;"><!--<a href="#"><img src="assets/img/Ad_h.png"></a>--></div>
                <div>
                    <h3 class="sr-only"><?php echo "$stockcode"." "; ?>& Peers Performance</h3>
                    <div id="peerPerformancecontainer" style="width:auto;height:700px;"></div>
                </div>
                <div class="chartNote">
                    <div class="showNote" role="button" tabindex="0" aria-expanded="false">Show more</div>
                    <div class="noteWrapper collapsed">
                        <span>
                            Peer Performance compares the stock with a group of related companies on the same percentage-return scale. Each line is rebased at the beginning of the displayed period, allowing stocks with very different share prices to be compared directly.
                        </span>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <li><strong>A higher line</strong> indicates stronger performance over the selected period, while a lower line indicates relative underperformance.</li>
                            <li><strong>Similar paths</strong> may show that the stocks are responding to shared industry or market forces.</li>
                            <li><strong>Diverging paths</strong> highlight periods when one stock begins outperforming or falling behind its peers.</li>
                        </ul>
                        <span style="margin-top: 6px;">
                            Use the chart's time-range controls to compare different periods. Select <strong>RACE</strong> to open the stock and its peers in the animated performance comparison tool.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <p></p>

<script>
function loadStockScript(src)
{
    return new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadStockStyle(href)
{
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

var preloadedStockQuoteRequest = null;
<?php if ($line != "" && $stockcode != "") { ?>
preloadedStockQuoteRequest = $.ajax({
    url: "/db_stockquote_get.php?data=<?php echo rawurlencode($stockcode); ?>&isIEX=<?php echo rawurlencode($isIEX); ?>",
    dataType: "text"
});
<?php } ?>

var stockOptionalLibrariesReady = null;

function ensureStockOptionalLibraries()
{
    if (stockOptionalLibrariesReady)
        return stockOptionalLibrariesReady;

    loadStockStyle('https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css');
    loadStockStyle('assets/css/dataTables.bootstrap4.min.css');
    loadStockStyle('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');

    stockOptionalLibrariesReady = loadStockScript('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js')
        .then(function() {
            return loadStockScript('https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js');
        })
        .then(function() {
            return loadStockScript('https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js');
        })
        .then(function() {
            return loadStockScript('https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js');
        })
        .then(function() {
            return loadStockScript('https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js');
        });

    return stockOptionalLibrariesReady;
}
</script>
<script src="https://code.highcharts.com/stock/8.2.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/sankey.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/pattern-fill.js"></script>
<script src="https://code.highcharts.com/8.2.0/highcharts-more.js"></script>
<script src="assets/js/TA.js"></script>
<script src="assets/js/GaugeChart.js"></script>
<script src="assets/js/RangeChart.js"></script>
<script src="assets/js/F-Z-M-ScoreChart.js"></script>
<script src="assets/js/PolarChart-Comment.js"></script>
<script src="assets/js/TSF.js?v=20260624.1"></script>
<script src="assets/js/priceDeviation.js?v=20260624.1"></script>
<script src="assets/js/CCASS.js?v=20260624.1"></script>
<script src="assets/js/stockCompare.js"></script>
<script src="assets/js/yearlyTrendChart.js?v=20260625.1"></script>
<!--script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-base.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-ui.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-exports.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-stock.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-data-adapter.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/8.9.0/js/anychart-annotations.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet">-->
<!--script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-ui.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-exports.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-stock.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-data-adapter.min.js?hcode=be5162d915534272a57d0bb781d27f2b"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-annotations.min.js?hcode=c11e6e3cfefb406e8ce8d99fa8368d33"></script>
<link href="https://cdn.anychart.com/releases/v8/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet"-->
<!--<link href="https://cdn.anychart.com/releases/v8/fonts/css/anychart-font.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet">-->
<link href="assets/css/anychart-font.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet">
<!--link href="https://cdn.anychart.com/releases/8.13.1/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet"-->
<link rel="preload" href="https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet"></noscript>
<script>
var anychartReady = loadStockScript('assets/js/anychart-bundle.min.js')
    .then(function() {
        return loadStockScript('assets/js/Anystock.js?v=20260625.4');
    });
</script>
<script src="assets/js/seasonality.js"></script>
<script src="assets/js/LanguageTimezone.js"></script>
<script src="assets/js/jquery.sparkline.min.js"></script>
<script>
window.__STOCKINFO_PAGE_CONFIG = {
    "stockcode": <?php echo json_encode($stockcode, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    "stockinfo": <?php echo json_encode($line, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/ValuationBands.js?v=20260624.1"></script>
<script src="assets/js/pages/stockinfo-main.js?v=20260619.2"></script>
<script src="assets/js/price-display-page-hooks.js?v=20260624.1"></script>
<script>
document.querySelectorAll('.mirrorscan-stockcode').forEach(function (element) {
    element.textContent = stockcode;
});
</script>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/theme.js"></script>-->
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
</body>

</html>
