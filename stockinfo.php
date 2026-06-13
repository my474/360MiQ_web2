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

        $sqlQuery0 = "set @stock = (Select code from (SELECT IF(code=?, 1, 0) AS exact, IF(code like ?, 1, 0) AS likecode, IF(code like ?, 1, 0) AS likecode2, IF(code like ?, 1, 0) AS likecode3, IF(name_en = ?, 1, 0) AS likecode4, IF(name_en like ?, 1, 0) AS likecode5, IF(name_en like ?, 1, 0) AS likecode6, IF(name_en like ?, 1, 0) AS likecode7, IF(name_en like ?, 1, 0) AS likecode8, IF(name_en like ?, 1, 0) AS likecode9, IF(name_en like ?, 1, 0) AS likecode10, IF(name_tc = ?, 1, 0) AS likecode4a, IF(name_tc like ?, 1, 0) AS likecode5a, IF(name_tc like ?, 1, 0) AS likecode6a, IF(name_tc like ?, 1, 0) AS likecode7a, IF(name_tc like ?, 1, 0) AS likecode8a, IF(name_tc like ?, 1, 0) AS likecode9a, IF(name_tc like ?, 1, 0) AS likecode10a, code, name_tc, name_en, exchange FROM stock_code_name_exchange WHERE (code like CONCAT('%',?,'%') or name_tc like CONCAT('%',?,'%') or name_en like CONCAT('%',?,'%')) ORDER BY exact DESC, IF(FIELD(exchange,'NYSE','NASDAQ','NYSEARCA')=0,1,0), likecode DESC, likecode2 DESC, likecode3 DESC, likecode4 DESC, likecode4a DESC, likecode5 DESC, likecode5a DESC, likecode6 DESC, likecode6a DESC, likecode7 DESC, likecode7a DESC, likecode8 DESC, likecode8a DESC, likecode9 DESC, likecode9a DESC, likecode10 DESC, likecode10a DESC, CHAR_LENGTH(name_tc), CHAR_LENGTH(name_en), code LIMIT 1) a);";
    
        $sqlQuery1 = "set @exchange = (select exchange from stock_code_name_exchange where code = @stock and exchange <> 'N/A');";
        //$sqlQuery3 = "SELECT d.code, f.market_cap, f.shares, f.close, d.exchange, f.eps, round(f.pe, 1), f.currency, IF( d.name_en LIKE CONCAT('%', d.name_tc, '%'), NULL, d.name_tc ) AS name_tc, d.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM (SELECT a.code, market_cap, shares, CLOSE, a.exchange, a.name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures FROM stock_code_name_exchange AS a LEFT JOIN price_current AS b ON a.code = b.code LEFT JOIN stock_HKEX AS c ON a.code = c.code WHERE a.code = @stock AND a.exchange = @exchange) AS d left JOIN price_current AS f on d.code = f.code LEFT JOIN trendgauge_stock_current g on f.code = g.code WHERE f.code = @stock AND d.exchange = @exchange";
        //$sqlQuery3 = "SELECT d.code, f.market_cap, f.shares, f.close, d.exchange, f.eps, round(f.pe, 1), f.currency, IF( d.name_en LIKE CONCAT('%', d.name_tc, '%'), NULL, d.name_tc ) AS name_tc, d.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM (SELECT a.code, a.exchange, a.name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures FROM stock_code_name_exchange AS a LEFT JOIN stock_HKEX AS c ON a.code = c.code WHERE a.code = @stock AND a.exchange = @exchange) AS d left JOIN price_current AS f on d.code = f.code LEFT JOIN trendgauge_stock_current g on f.code = g.code WHERE f.code = @stock AND d.exchange = @exchange";
        $sqlQuery3 = "SELECT a.code, f.market_cap, f.shares, f.close, a.exchange, f.eps, round(f.pe, 1), f.currency, IF( a.name_en LIKE CONCAT('%', a.name_tc, '%'), NULL, a.name_tc ) AS name_tc, a.name_en, boardlot, shortsell_eligible, stock_options, stock_futures, isIEX, value FROM stock_code_name_exchange AS a LEFT JOIN stock_HKEX AS c ON a.code = c.code left JOIN price_current AS f on a.code = f.code LEFT JOIN trendgauge_stock_current g on a.code = g.code WHERE (a.code = @stock or f.code = @stock) AND (a.exchange = @exchange or f.stock_exchange_short = @exchange)";
        
        $stmt = $connection->prepare($sqlQuery0);
        $likeVar1 = $stockcode."%";
        $likeVar2 = "%".$stockcode;
        $likeVar3 = "%".$stockcode."%";
        $likeVar5 = $stockcode." %";
        $likeVar6 = "% ".$stockcode;
        $likeVar7 = "% ".$stockcode." %";
        $stmt->bind_param("sssssssssssssssssssss",  $stockcode, $likeVar1, $likeVar2, $likeVar3, $stockcode, $likeVar5, $likeVar6, $likeVar7, $likeVar1, $likeVar2, $likeVar3, $stockcode, $likeVar5, $likeVar6, $likeVar7, $likeVar1, $likeVar2, $likeVar3, $stockcode, $stockcode, $stockcode);
        $stmt->execute();
        $stmt->close();
    
        $stmt = $connection->prepare($sqlQuery1);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $connection->prepare($sqlQuery3);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    
        while($itemRow = $result->fetch_assoc())
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
    <meta property="og:url" content="https://360miq.com/stockinfo" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />

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
    <script src="https://www.google.com/recaptcha/api.js?render=6LfVMmIrAAAAAMM1qvj7delYBZmU0CwbMklOaB9b"></script>
    <script>
      if (typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
          grecaptcha.execute('6LfVMmIrAAAAAMM1qvj7delYBZmU0CwbMklOaB9b', {action: 'pageview'}).then(function(token) {
            fetch('/recap.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ token: token, action: 'pageview' })
            });
          });
        });
      } else {
        fetch('/recap.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Recaptcha-Failure': 'true'
          },
          body: JSON.stringify({ token: null, action: 'recaptcha_missing' })
        });
        /*.then(response => response.json())
        .then(data => {
          if (!data.success && data.reason === 'reCAPTCHA not loaded') {
            // Option 1: Block interaction
            document.body.innerHTML = '<h1>Access denied</h1>';
        
            // Option 2: Redirect to an error or challenge page
            // window.location.href = '/access-denied.html';
          }
        });*/
      }
    </script>
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
</style>
<script src="https://code.highcharts.com/stock/8.2.0/highstock.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/no-data-to-display.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/8.2.0/modules/sankey.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<!--<script src="assets/js/ValuationBands.js"></script>-->
<script src="https://code.highcharts.com/stock/8.2.0/modules/pattern-fill.js"></script>
<script src="assets/js/Utils.js"></script>
<script src="assets/js/TA.js"></script>
<script src="assets/js/GaugeChart.js"></script>
<script src="assets/js/RangeChart.js"></script>
<script src="assets/js/F-Z-M-ScoreChart.js"></script>
<script src="assets/js/PolarChart-Comment.js"></script>
<script src="assets/js/TSF.js"></script>
<script src="assets/js/priceDeviation.js"></script>
<script src="assets/js/CCASS.js"></script>
<script src="assets/js/stockCompare.js"></script>
<script src="assets/js/yearlyTrendChart.js"></script>
    
<!--script src="https://code.jquery.com/jquery-3.5.1.js"></script //if dataTable weird, may need this js--> 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.4/js/responsive.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js"></script>
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
            
<script src="https://code.highcharts.com/8.2.0/highcharts-more.js"></script>
<script src="https://code.highcharts.com/8.2.0/modules/exporting.js"></script>
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
                        <div style="padding: 0px 15px 0px;margin: 15px 0 15px 0;">
                            <hr style="height:3px;border:3px;border-radius:3px;color:#ddd;background-color:#ddd;" />
                            <h3 class="sr-only"><?php echo "$stockcode"." "; ?>Monthly Return % Table</h3>
                            <h6 class="card-title" id="monthlytabletitle">Monthly Return %</h6>
                            <table id="monthlytable" class="display" width="99%" style="margin:0 auto"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p></p>
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
            </div>
        </div>
    </main>
    <p></p>

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.4/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
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
<script src="assets/js/anychart-bundle.min.js"></script>
<!--link href="https://cdn.anychart.com/releases/8.13.1/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet"-->
<link href="https://cdn.anychart.com/releases/8.9.0/css/anychart-ui.min.css?hcode=be5162d915534272a57d0bb781d27f2b" type="text/css" rel="stylesheet">
<script src="assets/js/Anystock.js"></script>
<script src="assets/js/seasonality.js"></script>
<script src="assets/js/LanguageTimezone.js"></script>
<script src="assets/js/jquery.sparkline.min.js"></script>
<script>
var stockcode = "<?php echo $stockcode; ?>";
var stockinfo = "<?php echo $line; ?>";

/*window.addEventListener("DOMContentLoaded", async function() {
  try {
    const fp = await FingerprintJS.load();
    const result = await fp.get();
    const visitorId = result.visitorId;

    const start = Date.now();
    let moves = [];
    let clickDetected = false;

    document.addEventListener("mousemove", e => {
      moves.push([Date.now() - start, e.clientX, e.clientY]);
    });

    document.addEventListener("click", () => clickDetected = true);

    window.addEventListener("beforeunload", () => {
      if (moves.length > 0 || clickDetected) return; // real user, skip logging

      const payload = {
        visitorId,
        path: location.pathname,
        userAgent: navigator.userAgent,
        timestamp: Date.now(),
        mouseData: moves,
        click: clickDetected
      };

      const blob = new Blob([JSON.stringify(payload)], { type: "application/json" });
      navigator.sendBeacon("/log-fp.php", blob);
    });
  } catch (err) {
    console.error("FingerprintJS failed to load", err);
  }
});*/



var dictYearlyTrend = new Object();
var seriesname = [];
var dailydata = [];
var priceDeviationChart = null;
var isIE = detectIE();
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
function exchFn(exchange){
    if (exchange == 'NASDAQ')
        return ' ○ Nasdaq';
    else if (exchange == 'NYSEARCA')
        return ' ○ NYSE Arca';
    return ' ○ ' + exchange;
}
function mcapFn(number, currency) {
    if (number === null || isNaN(number) || number <= 0)
        return '';
    else if (number >= 1000000000000)
        return ' Market Cap: ' + rounding (number / 1000000000000, 10) + 'T' + (currency == '' ? '' : ' ') + currency + '.';
    else if (number >= 1000000000 && number < 1000000000000)
        return ' Market Cap: ' + rounding (number / 1000000000, 10) + 'B' + (currency == '' ? '' : ' ') + currency + '.';
    else if (number >= 1000000 && number < 1000000000)
        return ' Market Cap: ' + rounding (number / 1000000, 10) + 'M' + (currency == '' ? '' : ' ') + currency + '.';
    else if (number >= 1000 && number < 1000000)
        return ' Market Cap: ' + rounding (number / 1000, 10) + 'K' + (currency == '' ? '' : ' ') + currency + '.';
    else
        return ' Market Cap: ' + number + (currency == '' ? '' : ' ') + currency + '.';
}
function trendgauge2txt(stockcode, trendvalue) {
    if (trendvalue > 22.5 && trendvalue <= 67.5) 
        return ' ' + stockcode + ' is trending strongly up.';
    else if (trendvalue > 67.5 && trendvalue <= 112.5) 
        return ' ' + stockcode + ' is overbought.';
    else if (trendvalue > 112.5 && trendvalue <= 157.5) 
        return ' ' + stockcode + ' is in correction.';
    else if (trendvalue > 157.5 && trendvalue <= 202.5) 
        return ' ' + stockcode + ' is trending down.';
    else if (trendvalue > 202.5 && trendvalue <= 247.5) 
        return ' ' + stockcode + ' is trending strongly down.';
    else if (trendvalue > 247.5 && trendvalue <= 292.5) 
        return ' ' + stockcode + ' is oversold.';
    else if (trendvalue > 292.5 && trendvalue <= 337.5) 
        return ' ' + stockcode + ' is rebounding.';
    else if ((trendvalue > 337.5 && trendvalue <= 360) ||
            (trendvalue >= 0 && trendvalue <= 22.5)) 
        return ' ' + stockcode + ' is trending up.';
    else
        return '';
}
var url = window.location.search;
url = url.split("+").join("");
var longMAperiod = 200; // 200 or 250MA
var exchange = "";
var toLoadAjax = false;
//var stockcode = encodeURIComponent(decodeURIComponent(url.substr(url.indexOf("=") + 1).toUpperCase()));
if (stockcode == "")
{
    toLoadAjax = true;
    stockcode = "AAPL";
    if (!isIE)
    {
        var timezone = LanguageTimezone(); 
        if (timezone.includes("Hong Kong"))
            stockcode = "0001.HK";
        else if (timezone.includes("United Kingdom"))
            stockcode = "GSK.L";
        else if (timezone.includes("Brisbane") || timezone.includes("Darwin") || timezone.includes("Sydney"))
            stockcode = "ANZ.AX";
        else if (timezone.includes("India") || timezone.includes("Bangladesh") || timezone.includes("United Arab Emirates"))
            stockcode = "RELIANCE.N";
    }
    
    document.title = (stockcode != '' ? stockcode + ' ' : '') + "Stock - 360MiQ.com";
}

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
var stockname_en_tc = '';
var desc = ' the latest in-depth technical and fundamental analysis using our proprietary Trend Gauge, technical indicators, Price Channel, PE & PB Band, F Score, Z Score, M Score. We also provide industry median for those important metrics, so that you know where your favorite stock stands in the crowd.';
var polarStockArray = [-1, -1, -1];
var polarIndustryArray = [-1, -1, -1];
var polarchart = null;
var fscorechart = null;
var zscorechart = null;
var marketcap = 0;
var eps;
var pe;
var isIEX = 0;
var trendgaugevalue = -1;
var tab1loaded = false;
var tab2loaded = false, tab3loaded = false, tab4loaded = false, tab5loaded = false;
var daydict = {"0":"00", "1":"01", "2":"02", "3":"03", "4":"04", "5":"05", "6":"06", "7":"07", "8":"08", "9":"09", "A":"10", "B":"11", "C":"12", "D":"13", "E":"14", "F":"15", "G":"16", "H":"17", "I":"18", "J":"19", "K":"20", "L":"21", "M":"22", "N":"23", "O":"24", "P":"25", "Q":"26", "R":"27", "S":"28", "T":"29", "U":"30", "V":"31"};
document.getElementById("stockname").textContent=stockcode;
var peersCode = "";
var peersCodeArray = [stockcode];
var peersNameArray = [];
var featuredGridCount = 0;
var browserwidthOnLoad = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
var numberOfPeers = 5;
if (browserwidthOnLoad < 576)
    numberOfPeers = 3;
else if (browserwidthOnLoad < 768)
    numberOfPeers = 4;
//else if (browserwidthOnLoad < 1200)
//    numberOfPeers = 5;

$.ajax({
    url : "./db_peers_get.php?data=" + encodeURIComponent(stockcode) + "&limit=" + numberOfPeers,
    success : function(result){
        var peers = result.split("\n");
        var peer_result = '<table style="width: 100%;">';
        var cs = [];
        for (i = 0; i < peers.length; i++)
        {
            var fields = peers[i].split("_");
            if (fields.length == 6)
            {
                var percent = rounding(parseFloat(fields[2]), 100);
                if (percent > 0)
                    percent = '<span style="color:green;font-weight:550;">' + "+" + percent + "%</span>";
                else if (percent == 0)
                    percent = '<span style="color:darkgrey;font-weight:550;">' + "\u00B1" + percent + "%</span>";
                else
                    percent = '<span style="color:red;font-weight:550;">' + percent + "%</span>";
                
                var td = '<td>';
                if (i % 2 == 1)
                    var td = '<td style="background-color:#f9f9f9">';
                if (fields[4] == "")
                {
                    peer_result += td + '<a href="stockinfo?code='+fields[0]+'">' + fields[0] + '</a>' + ", " + fields[3] + ", " + fields[1] + " " + percent + '&nbsp;<span style="cursor:pointer" onclick="scroll2peers()" title="6-month chart" id="sparkline' + i + '"></span></td>';
                    peersNameArray.push({'code':fields[0], 'name':fields[0] + " ● " + fields[3]});
                }
                else
                {
                    peer_result += td + '<a href="stockinfo?code='+fields[0]+'">' + fields[0] + '</a>' + ", " + fields[4] + ", " + fields[3] + ", " + fields[1] + " " + percent + '&nbsp;<span style="cursor:pointer" onclick="scroll2peers()" title="6-month chart" id="sparkline' + i + '"></span></td>';
                    peersNameArray.push({'code':fields[0], 'name':fields[0] + " ● " + fields[4] + " ● " + fields[3]});
                }
                    
                peersCode += fields[0] + ',';
                peersCodeArray.push(fields[0]);
                
                var closeSeries = fields[5];
                closeSeries = (closeSeries.endsWith(',' + fields[1]) ? closeSeries : closeSeries + ',' + fields[1]);
                cs.push(closeSeries.split(","));
            }
        }
        
        if (peersCode.endsWith(','))
        {
            peersCode = peersCode.substring(0, peersCode.length - 1);
        }
        
        if (peer_result != '<table style="width: 100%;">')
        {
            document.getElementById("peersCard").style.marginBottom = "16px";
            document.getElementById("peersContent").style.padding = "8px";
            document.getElementById("peersContent").innerHTML = peer_result.replaceAll(', ,', ',') + "</table>";
            document.getElementById("peersCard").style.display = "block";
            document.getElementById("peersContent").style.display = "block";
            
            for(var i = 0; i < cs.length; i++)
            {
                if (cs[i] !== null && cs[i] != undefined && cs[i].length > 0)
                {
                    var cs_number = cs[i].map(Number);
                    if (cs_number !== null && cs_number != undefined && cs_number.length > 0)
                        $("#sparkline" + i).sparkline(cs_number, { type: 'line', spotRadius: 0, disableInteraction: true});
                }
            }
        }
    }
});    

if (toLoadAjax)
{
    $.ajax({
        url : "/db_stockinfo_get.php?data=" + encodeURIComponent(stockcode),
        success : function(result){
            maincontent(result);
        }
    });
}
else
{
    maincontent(stockinfo)
}

function maincontent(result)
{
    var webtitle = document.title;
    var code_name = stockcode;
    var dot = '<span style="font-size:12px;"> ● </span>';
    var fields = result.split("_");
    var mcaptxt = '';
    if (fields.length == 15)
    {
        if (fields[13] != "")
        {
            stockcode = fields[13];
            webtitle = (stockcode != '' ? stockcode + ' ' : '') + "Stock - 360MiQ.com";
            code_name = stockcode;
        }
            
        var exch = fields[1];
        var exch2 = exch;
        
        var exchangeLink = '<a href="market?data=' + exch + '">' + (fields[1] == 'NASDAQ' ? 'Nasdaq' : fields[1]) + '</a>';
        
        if (exch == 'NASDAQ')
            exch2 = 'Nasdaq';
        else if (exch == "LSE")
            exch2 = "London";
        else if (exch == "ASX")
            exch2 = "Australia";
        else if (exch == "TSX")
            exch2 = "Toronto TSX";
        else if (exch == "NSE")
            exch2 = "India NSE";
        else if (exch == "TYO")
            exch2 = "Tokyo";
        else if (exch == "HKEX")
            exch2 = "Hong Kong";
        else if (exch == "SZSE")
            exch2 = "Shenzhen";
        else if (exch == "SHSE")
            exch2 = "Shanghai";
        else if (exch == 'NYSEARCA')
        {
            exch2 = 'NYSE Arca';
            exchangeLink = exch2;
        }
            
        if (exchangeLink !== "")
            //document.getElementById("cellMarket").innerHTML = "Market: " + '<a href="screener?market='+exch+'&sector=All&industry=All">' + exch2 + '</a>';
            document.getElementById("cellMarket").innerHTML = "Market: " + '<a href="screener?market='+exch+'">' + exch2 + '</a>';
        
        if (fields[2] == "" && fields[3] == "")
        {
            if (fields[1] != "")
                document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + exchangeLink;
                
            peersNameArray.push({'code':stockcode, 'name':stockcode});
        }
        else if (fields[2] == "")
        {
            document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[3] + (fields[1] == ""? "" : dot + exchangeLink);
            stockname_en_tc = fields[3];
            webtitle = stockcode + ' (' + stockname_en_tc + ') Stock - 360MiQ.com';
            code_name += ' (' + stockname_en_tc + ')';
            peersNameArray.push({'code':stockcode, 'name':stockcode + " ● " + fields[3]});
        }
        else if (fields[3] == "" || fields[2] == fields[3])
        {
            document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[2] + (fields[1] == ""? "" : dot + exchangeLink);
            stockname_en_tc = fields[2];
            webtitle = stockcode + ' (' + stockname_en_tc + ') Stock - 360MiQ.com';
            code_name += ' (' + stockname_en_tc + ')';
            peersNameArray.push({'code':stockcode, 'name':stockcode + " ● " + fields[2]});
        }
        else
        {
            document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[2] + dot + fields[3] + (fields[1] == ""? "" : dot + exchangeLink);
            stockname_en_tc = fields[2] + dot + fields[3];
            webtitle = stockcode + ' (' + fields[2] + ' ' + fields[3] + ') Stock - 360MiQ.com';
            code_name += ' ('  + fields[2] + ' ' + fields[3] + ')';
            peersNameArray.push({'code':stockcode, 'name':stockcode + " ● " + fields[2] + " ● " + fields[3]});
        }

        //document.getElementById('stockname').title = document.getElementById("stockname").textContent;
        exchange = fields[1];
        var currency = '';
        var currencyGBX = '';
        if (fields[11] != "")
        {
            currency = fields[11];
            if (currency == "GBX")
            {
                currency = "GBP";
                currencyGBX = '<span style="color:gray; font-size:8px">&nbsp;GBX</span>';
            }
            
            document.getElementById("currency").textContent = "(" + currency + ")";
        }
            
        if (fields[0] != "")
        {
            var tmp = parseFloat(fields[0]);
            marketcap = tmp;
            if (tmp >= 1000000000000)
                tmp = rounding(tmp / 1000000000000, 100) + 'T';
            else if (tmp >= 1000000000 && tmp < 1000000000000)
                tmp =  rounding(tmp / 1000000000, 10) + 'B';
            else if (tmp >= 1000000 && tmp < 1000000000)
                tmp =  rounding(tmp / 1000000, 10) + 'M';
            else if (tmp >= 1000 && tmp < 1000000)
                tmp =  rounding(tmp / 1000, 10) + 'K';
            else if (tmp <= 0)
                tmp = "-";
                
            document.getElementById("marketcap").textContent=tmp;
            mcaptxt = mcapFn(marketcap, currency);
        }
        
        if (fields[4] != "")
            document.getElementById("boardlot").textContent=parseFloat(fields[4]);

        if (fields[9] != "")
        {
            eps = parseFloat(fields[9]);
            pe = parseFloat(fields[10]);
        }
        
        if (fields[12] == "1")
            isIEX = 1;
                
        if (fields[14] != "")
            trendgaugevalue = parseFloat(fields[14]);

        if (fields[1] == "HKEX")
        {
            document.getElementById("heading-card").style.marginTop = "23px";
            
            var line = "-/-/-";
            if (fields[5] == "Y")
                line = "<span style=\"color:green\"><b>✓</b></span>/";
            else
                line = "<span style=\"color:red\"><b>✗</b></span>/";
                
            if (fields[6] == "Y")
                line += "<span style=\"color:green\"><b>✓</b></span>/";
            else
                line += "<span style=\"color:red\"><b>✗</b></span>/";
                
            if (fields[7] == "Y")
                line += "<span style=\"color:green\"><b>✓</b></span>";
            else
                line += "<span style=\"color:red\"><b>✗</b></span>";
                
            document.getElementById("shortable").innerHTML = line;
            //document.getElementById("currency").textContent = "(HKD)";
        }
        else
        {
            // not HK, no boardlot & short
            document.getElementById("infoTable").deleteRow(2);
            document.getElementById("infoTable").deleteRow(1);

            document.getElementById('cell11').height = "22";
            document.getElementById('cell41').height = "22";
            document.getElementById('cell51').height = "22";
            document.getElementById('marketcap').height = "22";
            document.getElementById('pe').height = "22";
            document.getElementById('eps').height = "22";

            
            /*if (fields[1] == "SZCE")
                document.getElementById("currency").textContent = "(RMB)";
            else if (fields[1] == "NYSE" || fields[1] == "NYSEARCA" || 
                    fields[1] == "NASDAQ" || fields[1] == "OTCMKTS" || 
                    fields[1] == "AMEX" || fields[1] == "BATS")
                document.getElementById("currency").textContent = "(USD)";
            */
        }                
        
    }
    else
    {
        if (fields.length == 3)
        {
            var exch = fields[0];
            var exch2 = exch;
            
            var exchangeLink = '<a href="market?data=' + exch + '">' + (fields[0] == 'NASDAQ' ? 'Nasdaq' : fields[0]) + '</a>';
            
            if (exch == 'NASDAQ')
                exch2 = 'Nasdaq';
            else if (exch == "LSE")
                exch2 = "London";
            else if (exch == "ASX")
                exch2 = "Australia";
            else if (exch == "TSX")
                exch2 = "Toronto TSX";
            else if (exch == "NSE")
                exch2 = "India NSE";
            else if (exch == "TYO")
                exch2 = "Tokyo";
            else if (exch == "HKEX")
                exch2 = "Hong Kong";
            else if (exch == "SZSE")
                exch2 = "Shenzhen";
            else if (exch == "SHSE")
                exch2 = "Shanghai";
            else if (exch == 'NYSEARCA')
            {
                exch2 = 'NYSE Arca';
                exchangeLink = exch2;
            }

            if (exchangeLink !== "")
                //document.getElementById("cellMarket").innerHTML = "Market: " + '<a href="screener?market='+exch+'&sector=All&industry=All">' + exch2 + '</a>';
                document.getElementById("cellMarket").innerHTML = "Market: " + '<a href="screener?market='+exch+'">' + exch2 + '</a>';

            if (fields[1] == "" && fields[2] == "")
            {
                if (fields[0] != "")
                    document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + exchangeLink;
            }
            else if (fields[1] == "")
            {
                document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[2] + (fields[0] == ""? "" : dot + exchangeLink);
                stockname_en_tc = fields[2];
                webtitle = stockcode + ' (' + stockname_en_tc + ') Stock - 360MiQ.com';
                code_name += ' (' + stockname_en_tc + ')';
            }
            else if (fields[2] == "" || fields[1] == fields[2])
            {
                document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[1] + (fields[0] == ""? "" : dot + exchangeLink);
                stockname_en_tc = fields[1];
                webtitle = stockcode + ' (' + stockname_en_tc + ') Stock - 360MiQ.com';
                code_name += ' (' + stockname_en_tc + ')';
            }
            else
            {
                document.getElementById("stockname").innerHTML = '<a href="tool?code='+stockcode+'">' + stockcode + '</a>' + dot + fields[1] + dot + fields[2] + (fields[0] == ""? "" : dot + exchangeLink);
                stockname_en_tc = fields[1] + dot + fields[2];
                webtitle = stockcode + ' (' + fields[1] + ' ' + fields[2] + ') Stock - 360MiQ.com';
                code_name += ' ('  + fields[1] + ' ' + fields[2] + ')';
            }

            //document.getElementById('stockname').title = document.getElementById("stockname").textContent;
            exchange = fields[0];
        }
        
        // not HK, no boardlot & short
        document.getElementById("infoTable").deleteRow(2);
        document.getElementById("infoTable").deleteRow(1);

        document.getElementById('cell11').height = "22";
        document.getElementById('cell41').height = "22";
        document.getElementById('cell51').height = "22";
        document.getElementById('marketcap').height = "22";
        document.getElementById('pe').height = "22";
        document.getElementById('eps').height = "22";
    }
    
    $.ajax({
        url : "db_chatbot.php?search=" + stockcode + "&fullsearch=&exchange=" + exch,
        dataType: "json",
        success : function(result){
            document.getElementById("chartcontainerMirrorScan").innerHTML = chatbot(result, true);
        }
    });

    if (toLoadAjax)
    {
        document.title = webtitle;
        document.querySelector('meta[property="og:title"]').setAttribute("content", webtitle);
        document.querySelector('meta[property="twitter:title"]').setAttribute("content", webtitle);
        desc = code_name + exchFn(exchange) + ' —' + mcaptxt + trendgauge2txt(stockcode, trendgaugevalue) + ' More on' + desc;
        document.querySelector('meta[name="description"]').setAttribute("content", desc);
        document.querySelector('meta[property="og:description"]').setAttribute("content", desc);
        document.querySelector('meta[property="twitter:description"]').setAttribute("content", desc);
    }
    
    document.getElementById('sort1').innerHTML = exchange == 'NYSEARCA'? '1 day' : '<a class="sectorperformance" href="market?data=' + exchange + '&sort=1&map=1&highlight=' + stockcode + '&tab=3">1 day</a>';
    document.getElementById('sort5').innerHTML = exchange == 'NYSEARCA'? '5 days' : '<a class="sectorperformance" href="market?data=' + exchange + '&sort=5&map=5&highlight=' + stockcode + '&tab=3">5 days</a>';
    document.getElementById('sort20').innerHTML = exchange == 'NYSEARCA'? '20 days' : '<a class="sectorperformance" href="market?data=' + exchange + '&sort=20&map=20&highlight=' + stockcode + '&tab=3">20 days</a>';
     
    if (exchange == "HKEX" || exchange == "SZSE" || exchange == "SHSE")
    {
        longMAperiod = 250;
        //document.getElementById('ma200radio').value = longMAperiod;
        document.getElementById('ma200label').innerHTML = '<input type="radio" id="ma200radio" name="ma_amount" value="' + longMAperiod + '">&nbsp;' + longMAperiod + '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
        
    
    if (exchange == "HKEX")
    {
        document.getElementById("performanceTable").style.paddingTop = "0px";
        document.getElementById("performanceTable").style.paddingLeft = "10px";
        document.getElementById("performanceTable").style.paddingBottom = "0px";
        document.getElementById("performanceTable").style.paddingRight = "10px";
        document.getElementById("performanceTable").style.lineHeight = 1.3;
    }
    /*else
    {
        document.getElementById("performanceTable").style.paddingTop = "10px";
        document.getElementById("performanceTable").style.paddingLeft = "10px";
        document.getElementById("performanceTable").style.paddingBottom = "0px";
        document.getElementById("performanceTable").style.paddingRight = "10px";
    }*/
        
    $.ajax({
        url : "/db_stockquote_get.php?data=" + encodeURIComponent(stockcode) + "&isIEX=" + isIEX,
        success : function(result){
            var pricedata = "";
            var tmprows = result.split("\n");
            for (i = 0; i < tmprows.length - 1; i++)
            {
                date = tmprows[i].substring(0, tmprows[i].indexOf(","));
                if (date.indexOf(':') == -1 && tmprows[i] != "" && date != "")
                {
                    if (i == 0)
                    {
                        if (parseInt(date.substring(0, 2)) >= 90)
                            date = "19" + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];    
                        else
                            date = "20" + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];  
                    }
                    else
                    {
                        if (date.length == 1)
                            date = previousDate.substring(0, 7) + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];
                        else if (date.length == 3)
                            date = previousDate.substring(0, 5) + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];
                        else if (date.length == 5)
                        {
                            if (parseInt(date.substring(0, 2)) >= 90)
                                date = "19" + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];    
                            else
                                date = "20" + date.substring(0, date.length - 1) + daydict[date.substring(date.length - 1)];                
                        }    
                        date = date.replace("-", "");
                    }
                    
                    date = date.substr(0,4) + "-" + date.substr(4,2) + "-" + date.substr(6,2);
                    data = tmprows[i].substring(tmprows[i].indexOf(","));
                    var previousDate = date;
                    
                    pricedata += date+data+"\n";
                }
                else if (date.indexOf(':') > -1)
                {
                    pricedata += tmprows[i]+"\n";
                }
            }

            if (pricedata == "")
            {
                document.getElementById("nodatatable").style.visibility = "visible";
                document.getElementById("nodatacell").innerHTML = 'No Data. Please check if the stock code is correct.<br><br>We support stocks from:<p></p><ul style="text-align:left;width:150px;margin:0 auto"><li>NYSE</li><li>Nasdaq</li><li>NYSE Arca</li><li>London</li><li>Toronto TSX</li><li>Australia</li><li>India NSE</li><li>Tokyo</li><li>Hong Kong</li><li>Shanghai</li><li>Shenzhen</li></ul>';
                return;
            }
            else 
                document.getElementById("nodatatable").style.display = "none";
                
            var rows = pricedata.split("\n");
             
            var low250, high250, low50, high50, firstprice, lastprice, lastyearendprice, ma50, ma250, sum50, sum250, lastdate, nextday, date1y, date3y, date5y, date8y;
            var vollow20, volhigh20, vollow60, volhigh60, lastvol, volma20, volma60, volsum20, volsum60;
            var close5, close20, close60, close1y, close3y, close5y, close8y;
            var myList = new Array();
            var gotlastyearendprice = false;
            var firstTradedate = '';
            //var i = 0;
            
            for (i = 0; /*i < 260 &&*/ i < rows.length - 1; i++) // don't use longMAperiod + 10 instead of 260
            {
                var fieldsA = rows[i].split(",");
                if (fieldsA.length != 6)
                    continue;
                    
                var tradedate = utc(str2date(formatDate(str2date(fieldsA[0]))));
                if (dailydata === undefined || dailydata.length === 0 || (dailydata.length > 0 && dailydata[dailydata.length - 1][0] < tradedate))
                    dailydata.push([tradedate, parseFloat(fieldsA[1]), parseFloat(fieldsA[2]), parseFloat(fieldsA[3]), parseFloat(fieldsA[4]), parseFloat(fieldsA[5])]);

                var fields = rows[rows.length - i - 2].split(",");

                myList.push(fields);
                if (i == 0)
                {
                    firstTradedate = fieldsA[0];
                    lastvol = parseFloat(myList[0][myList[0].length - 1]);
                    volsum20 = volsum60 = lastvol;
                    vollow20 = vollow60 = volhigh20 = volhigh60 = parseFloat(myList[0][myList[0].length - 1]);
                    
                    //lastdate = new Date(myList[0][0].replace(/\s/, 'T')); // Safari must have 'T', but wrong timezone, use below instead
                    var a = myList[0][0].split(/[^0-9]/);
                    if (a.length == 5)
                        lastdate = new Date (a[0],a[1]-1,a[2],a[3],a[4]);
                    else
                        lastdate = new Date (a[0],a[1]-1,a[2]);

                    yesterday = lastdate;        
                    lastprice = parseFloat(myList[0][4]);
                    firstprice = parseFloat(fieldsA[4]);
                    lastyearendprice = '-';
                    sum50 = sum250 = lastprice;
                    low250 = low50 = parseFloat(myList[0][3]);
                    high250 = high50 = parseFloat(myList[0][2]);
                    
                    document.getElementById("heading-card").innerHTML = lastprice + currencyGBX;

                    date1y = add_Days(new Date (a[0]-1,a[1]-1,a[2]), 1);
                    date3y = add_Days(new Date (a[0]-3,a[1]-1,a[2]), 1);
                    date5y = add_Days(new Date (a[0]-5,a[1]-1,a[2]), 1);
                    date8y = add_Days(new Date (a[0]-8,a[1]-1,a[2]), 1);

                    // use either eps or pe, not both as they are not matched in DB often, trust eps more as pe changes with close
                    if (!isNaN(eps))
                    {
                        document.getElementById("eps").textContent = Math.abs(eps) < 0.01 ? rounding(eps, 10000) : rounding(eps, 100);
                        if (eps != null && eps > 0 && !isNaN(lastprice) && lastprice != null && lastprice > 0) // this is redundant in db_stockquote_get below, so that whichever success 1st should work
                            document.getElementById("pe").textContent=rounding(lastprice/eps, 10); // pe from DB is not quite well, calculate it
                    }
                    else if (!isNaN(pe))
                    {
                        if (!isNaN(eps) && eps != null && eps > 0 && !isNaN(lastprice) && lastprice != null && lastprice > 0 && (pe*eps/lastprice > 1.2 || pe*eps/lastprice < 0.8)) // this is redundant in db_stockquote_get below, so that whichever success 1st should work
                        {
                            pe = lastprice/eps;
                            document.getElementById("pe").textContent=pe; // pe from DB is not quite well, calculate it
                        }
                        else if (pe > 0)
                            document.getElementById("pe").textContent = pe;
                            
                        if (!isNaN(lastprice) && lastprice != null && lastprice > 0)
                        {
                            var epstmp = lastprice / pe;
                            document.getElementById("eps").textContent = Math.abs(epstmp) < 0.01 ? rounding(epstmp, 10000) : rounding(epstmp, 100);
                        }
                    }

                    var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    
                    if (isIE)
                    {
                        if (myList[0][0].length == 10)
                        {
                            dformat = myList[0][0];
                            var tmpDate = new Date(dformat);
                            dformat += ' ' + days[tmpDate.getDay()];
                        }
                        else if (myList[0][0].length == 16)
                        {
                            dformat = myList[0][0]; // ignore second
                            var tmpDate = new Date(dformat.substring(0, 10));
                            dformat += ' ' + days[tmpDate.getDay()];
                        }
                    }
                    else
                    {
                        dformat = [ lastdate.getFullYear(),
                                   (lastdate.getMonth()+1).padLeft(),
                                    lastdate.getDate().padLeft(),
                                    ].join('-');
                                    
                        if (myList[0][0].length > 10)
                        {
                            dformat = [ lastdate.getFullYear(),
                                       (lastdate.getMonth()+1).padLeft(),
                                        lastdate.getDate().padLeft(),
                                        ].join('-')+
                                        ' ' +
                                      [ lastdate.getHours().padLeft(),
                                        lastdate.getMinutes().padLeft()].join(':');
                        }
                        
                        dformat += ' ' + days[lastdate.getDay()];
                    }
                    
                    if (myList[0][0].length == 16)
                    {
                        var olddate = myList[0][0].substring(0, 16);
                        var newdate = myList[0][0].substring(0, 10);
                        pricedata = pricedata.replace(olddate, newdate); // IE and Safari can't handle HH:mm:ss in Anystock
                    }
                        
                    document.getElementById("updatetime").innerHTML = /* isIEX == 1 ? (dformat + ' &nbsp;<a target="_blank" rel="noopener noreferrer" href="https://iexcloud.io" style="font-size:7.5px;color:#8ACCE8"><b>IEX Cloud</b></a>') : */ dformat;
                    
                    if (rows.length == 2) // just on board
                    {
                        document.getElementById("cardheader-subtext").innerHTML = '<i class="fas fa-dot-circle"  style="color:darkgrey"></i> - (-%)';
                        //document.getElementById("priceinfo").style.backgroundColor = "#F3f3c9";
                        document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#F3f3c9, #Ffffe9, #F3f3c9)";
                        if (document.documentElement.getAttribute('data-theme') === 'dark')
                            document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#2a2a3e, #1a1a2e, #2a2a3e)";
                    }
                }
                else if (i < 250) // don't use longMAperiod instead of 250
                {
                    if (i == 1)
                    {
                        var change = lastprice - parseFloat(myList[1][4]);
                        var chgpercent = 0;
                        if (lastprice == 0 && parseFloat(myList[1][4]) == 0)
                            chgpercent = 0;
                        else if (lastprice > 0 && parseFloat(myList[1][4]) == 0)
                            chgpercent = '-';
                        else
                            chgpercent = rounding((lastprice/parseFloat(myList[1][4]) - 1) * 100, 100);
                        
                        if (change > 0)
                        {
                            document.getElementById("cardheader-subtext").innerHTML = '<i class="fas fa-arrow-alt-circle-up" style="color:green"></i> +' + rounding(change, 10000) + ' (+' + chgpercent + '%)';
                            //document.getElementById("priceinfo").style.backgroundColor = "#ddf388";
                            document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#ddf3aa, #ffffe0, #ddf3aa)";
                            if (document.documentElement.getAttribute('data-theme') === 'dark')
                                document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#1a3e1a, #1e2a2e, #1a3e1a)";
                        }
                        else if (change < 0)
                        {
                            document.getElementById("cardheader-subtext").innerHTML = '<i class="fas fa-arrow-alt-circle-down" style="color:red"></i> ' + rounding(change, 10000) + ' ('+ chgpercent +'%)';
                            //document.getElementById("priceinfo").style.backgroundColor = "#FFE7E6";
                            document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#FFE0E0, #FFf7f6, #FFE0E0)";
                            if (document.documentElement.getAttribute('data-theme') === 'dark')
                                document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#3e1a1a, #2a1a2e, #3e1a1a)";
                        }
                        else
                        {
                            document.getElementById("cardheader-subtext").innerHTML = '<i class="fas fa-dot-circle"  style="color:darkgrey"></i> \u00B1' + rounding(change, 10000) + ' (\u00B1'+ chgpercent +'%)';
                            //document.getElementById("priceinfo").style.backgroundColor = "#F3f3c9";
                            document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#F3f3c9, #Ffffe9, #F3f3c9)";
                            if (document.documentElement.getAttribute('data-theme') === 'dark')
                                document.getElementById("priceinfo").style.backgroundImage = "linear-gradient(#2a2a3e, #1a1a2e, #2a2a3e)";
                        }
                    }

                    if (low250 > parseFloat(myList[i][3]))
                        low250 = parseFloat(myList[i][3]);
                    
                    if (high250 < parseFloat(myList[i][2]))
                        high250 = parseFloat(myList[i][2]);            
    
                    if (i < 50)
                    {
                        if (low50 > parseFloat(myList[i][3]))
                            low50 = parseFloat(myList[i][3]);
                        
                        if (high50 < parseFloat(myList[i][2]))
                            high50 = parseFloat(myList[i][2]);
                        
                        sum50 += parseFloat(myList[i][4]);                
                    }
                    
                    if (i < 60)
                    {
                        if (vollow60 > parseFloat(myList[i][myList[i].length - 1]))
                            vollow60 = parseFloat(myList[i][myList[i].length - 1]);
                        
                        if (volhigh60 < parseFloat(myList[i][myList[i].length - 1]))
                            volhigh60 = parseFloat(myList[i][myList[i].length - 1]);
                        
                        volsum60 += parseFloat(myList[i][myList[i].length - 1]);
                    }
        
                    if (i < 20)
                    {
                        if (vollow20 > parseFloat(myList[i][myList[i].length - 1]))
                            vollow20 = parseFloat(myList[i][myList[i].length - 1]);
                        
                        if (volhigh20 < parseFloat(myList[i][myList[i].length - 1]))
                            volhigh20 = parseFloat(myList[i][myList[i].length - 1]);
                        
                        volsum20 += parseFloat(myList[i][myList[i].length - 1]);
                    }            
                    
                    if (i < longMAperiod)
                    {
                        sum250 += parseFloat(myList[i][4]);
                    }
                    
                }  
                
                //var now = new Date(myList[i][0].replace(/\s/, 'T')); // Safari must have 'T', but wrong timezone, use below instead
                var a = myList[i][0].split(/[^0-9]/);
                var now, now2;
                if (a.length == 6)
                {
                    now = new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5]);
                    now2 = new Date (a[0],a[1]-1,a[2]);
                }
                else
                {
                    now = new Date (a[0],a[1]-1,a[2]);
                    now2 = new Date (a[0],a[1]-1,a[2]);
                }
                if (!gotlastyearendprice && lastdate.getFullYear() != now.getFullYear())
                {
                    gotlastyearendprice = true;
                    lastyearendprice = parseFloat(myList[i][4]);        
                }
                
                if (i == 5)
                    close5 = parseFloat(myList[i][4]);
                else if (i == 20)
                    close20 = parseFloat(myList[i][4]);
                else if (i == 60)
                    close60 = parseFloat(myList[i][4]);
                    
                if (i > 0)
                {
                    if (nextday >= date1y && now2 < date1y)
                        close1y = parseFloat(myList[i][4]);
                    else if (nextday >= date3y && now2 < date3y)
                        close3y = parseFloat(myList[i][4]);
                    else if (nextday >= date5y && now2 < date5y)
                        close5y = parseFloat(myList[i][4]);
                    else if (nextday >= date8y && now2 < date8y)
                        close8y = parseFloat(myList[i][4]);
                }
                
                /*
                if (yesterday < date1y)
                    close1y = parseFloat(myList[i][4]);
                if (yesterday < date3y)
                    close3y = parseFloat(myList[i][4]);
                if (yesterday < date5y)
                    close5y = parseFloat(myList[i][4]);
                */    
                nextday = now2;
            }
            
            var getYTD = false;
            var lastyearendpriceTmp = '-';
            if (gotlastyearendprice)
            {
                getYTD = true;
                lastyearendpriceTmp = lastyearendprice;
            }
            else if (lastdate.getFullYear() == parseInt(firstTradedate.substring(0, 4), 10))
            {
                getYTD = true;
                lastyearendpriceTmp = firstprice;
            }
            
            if (getYTD)
            {
                var ytd_percent = rounding((lastprice/lastyearendpriceTmp -1)* 100, 10);
                if (Math.abs(lastprice/lastyearendprice -1) >= 2)
                    ytd_percent = rounding((lastprice/lastyearendpriceTmp -1)* 100, 1);
                    
                if (ytd_percent > 0)
                {
                    document.getElementById("cellytd").innerHTML = 'YTD: <span style="color:green;font-size:10.5px;">+' + ytd_percent + '%</span>';
                }
                else if (ytd_percent < 0)
                {
                    document.getElementById("cellytd").innerHTML = 'YTD: <span style="color:red;font-size:10.5px;">' + ytd_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cellytd").innerHTML = 'YTD: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + ytd_percent + '%</span>';
                }
            }
            
            if (close5 > 0)
            {
                var close5_percent = rounding((lastprice/close5 -1)* 100, 10);
                if (Math.abs(lastprice/close5 -1) >= 2)
                    close5_percent = rounding((lastprice/close5 -1)* 100, 1);
                    
                if (close5_percent > 0)
                {
                    document.getElementById("cell5d").innerHTML = '5 days: <span style="color:green;font-size:10.5px;">+' + close5_percent + '%</span>';
                }
                else if (close5_percent < 0)
                {
                    document.getElementById("cell5d").innerHTML = '5 days: <span style="color:red;font-size:10.5px;">' + close5_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell5d").innerHTML = '5 days: <span style="color:grey;font-size:10.5px;">\u00B1' + close5_percent + '%</span>';
                }
            }
            
            if (close20 > 0)
            {
                var close20_percent = rounding((lastprice/close20 -1)* 100, 10);
                if (Math.abs(lastprice/close20 -1) >= 2)
                    close20_percent = rounding((lastprice/close20 -1)* 100, 1);
                    
                if (close20_percent > 0)
                {
                    document.getElementById("cell20d").innerHTML = '20 days: <span style="color:green;font-size:10.5px;">+' + close20_percent + '%</span>';
                }
                else if (close20_percent < 0)
                {
                    document.getElementById("cell20d").innerHTML = '20 days: <span style="color:red;font-size:10.5px;">' + close20_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell20d").innerHTML = '20 days: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close20_percent + '%</span>';
                }
            }
            
            if (close60 > 0)
            {
                var close60_percent = rounding((lastprice/close60 -1)* 100, 10);
                if (Math.abs(lastprice/close60 -1) >= 2)
                    close60_percent = rounding((lastprice/close60 -1)* 100, 1);
                    
                if (close60_percent > 0)
                {
                    document.getElementById("cell60d").innerHTML = '60 days: <span style="color:green;font-size:10.5px;">+' + close60_percent + '%</span>';
                }
                else if (close60_percent < 0)
                {
                    document.getElementById("cell60d").innerHTML = '60 days: <span style="color:red;font-size:10.5px;">' + close60_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell60d").innerHTML = '60 days: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close60_percent + '%</span>';
                }
            }
            
            if (close1y > 0)
            {
                var close1y_percent = rounding((lastprice/close1y -1)* 100, 10);
                if (Math.abs(lastprice/close1y -1) >= 2)
                    close1y_percent = rounding((lastprice/close1y -1)* 100, 1);
                    
                if (close1y_percent > 0)
                {
                    document.getElementById("cell1y").innerHTML = '1 year: <span style="color:green;font-size:10.5px;">+' + close1y_percent + '%</span>';
                }
                else if (close1y_percent < 0)
                {
                    document.getElementById("cell1y").innerHTML = '1 year: <span style="color:red;font-size:10.5px;">' + close1y_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell1y").innerHTML = '1 year: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close1y_percent + '%</span>';
                }
            }
            
            if (close3y > 0)
            {
                var close3y_percent = rounding((lastprice/close3y -1)* 100, 10);
                if (Math.abs(lastprice/close3y -1) >= 2)
                    close3y_percent = rounding((lastprice/close3y -1)* 100, 1);
                    
                if (close3y_percent > 0)
                {
                    document.getElementById("cell3y").innerHTML = '3 years: <span style="color:green;font-size:10.5px;">+' + close3y_percent + '%</span>';
                }
                else if (close3y_percent < 0)
                {
                    document.getElementById("cell3y").innerHTML = '3 years: <span style="color:red;font-size:10.5px;">' + close3y_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell3y").innerHTML = '3 years: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close3y_percent + '%</span>';
                }
            }
            
            if (close5y > 0)
            {
                var close5y_percent = rounding((lastprice/close5y -1)* 100, 10);
                if (Math.abs(lastprice/close5y -1) >= 2)
                    close5y_percent = rounding((lastprice/close5y -1)* 100, 1);
                    
                if (close5y_percent > 0)
                {
                    document.getElementById("cell5y").innerHTML = '5 years: <span style="color:green;font-size:10.5px;">+' + close5y_percent + '%</span>';
                }
                else if (close5y_percent < 0)
                {
                    document.getElementById("cell5y").innerHTML = '5 years: <span style="color:red;font-size:10.5px;">' + close5y_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell5y").innerHTML = '5 years: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close5y_percent + '%</span>';
                }
            }    
            
            if (close8y > 0)
            {
                var close8y_percent = rounding((lastprice/close8y -1)* 100, 10);
                if (Math.abs(lastprice/close8y -1) >= 2)
                    close8y_percent = rounding((lastprice/close8y -1)* 100, 1);
                    
                if (close8y_percent > 0)
                {
                    document.getElementById("cell8y").innerHTML = '8 years: <span style="color:green;font-size:10.5px;">+' + close8y_percent + '%</span>';
                }
                else if (close8y_percent < 0)
                {
                    document.getElementById("cell8y").innerHTML = '8 years: <span style="color:red;font-size:10.5px;">' + close8y_percent + '%</span>';
                }
                else
                {
                    document.getElementById("cell8y").innerHTML = '8 years: <span style="color:darkgrey;font-size:10.5px;">\u00B1' + close8y_percent + '%</span>';
                }
            } 
            
            //pricedata = result;
            anystock(pricedata, stockcode, stockname_en_tc.replace(dot, " ● ").substring(0, 45), longMAperiod, 'stockcontainer', 'stockrangecontainer', true);

            if (i >= longMAperiod)
                ma250 = sum250/longMAperiod;
            //else if (i == 0)
            //    ma250 = sum250;
            else
                ma250 = '-';
            //    ma250 = sum250/i;
        
            if (i >= 50)
                ma50 = sum50/50;
            //else if (i == 0)
             //   ma50 = sum50;
            else
                ma50 = '-';
            //    ma50 = sum50/i; 
            
        
            
            if (i >= 60)
                volma60 = volsum60/60;
            //else if (i == 0)
            //    ma250 = sum250;
            else
                volma60 = '-';
            //    ma250 = sum250/i;
        
            if (i >= 20)
                volma20 = volsum20/20;
            //else if (i == 0)
             //   ma50 = sum50;
            else
                volma20 = '-';
            
            
            rangeChart('rangecontainer', '#f9f9f9', low250, high250, low50, high50, lastprice, lastyearendprice, ma50, ma250, "Price", "Last Year End", "MA50", "MA" + longMAperiod, "50-day Range", "250-day Range", '#D2B4DE', '#c2a4cE', '#F9E79F', '#e9d78F', "#5FB1EE", "#A5CF00");
            rangeChart('volrangecontainer', '#ffffff', vollow60, volhigh60, vollow20, volhigh20, lastvol, '-', volma20, volma60, "Volume", "DON'T DISPLAY", "Vol MA20", "Vol MA60", "20-day Range", "60-day Range", '#99E7FF', '#89d7eF', '#DCFF99', '#ccef89', "#ee5fb1", "#af86f8");
            
            // PE Band
            /*if (window.location.hash == "") // no need to load PE band as it is loaded at the beginning
            {    
                valuationChart("chartcontainerA1", 'PE Band'); 
            }*/
            
            $.ajax({
                url : "db_featuredblogpost_get.php?data=" + encodeURIComponent(stockcode),
                success : function(result){
                    
                    var localcount = 0;
                    const width = window.innerWidth;
                    var jsonObject = result.split(/\r?\n|\r/);
                    for (var i = 0; i < jsonObject.length; i++) {
                        var fields = jsonObject[i].split('@');
                        if (fields.length == 7)
                        {
                            var html = '';
                            var htmltitle = '';
                            if (fields[3] != '')
                            {
                                var parts = fields[3].split('-');
                                var featured_date_diff = Date.now() - (new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]))).getTime();
                                if (featured_date_diff < 5184000000) // within the last 60 days
                                {
                                    featuredGridCount++;
                                    
                                    if (fields[4] == '' && fields[5] == '' && fields[6] == '')
                                    {
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                    }
                                    else if (fields[4] == '' && fields[5] != '' && fields[6] == '')
                                    {
                                        html += '<a href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="border-radius: 5px;width: 100%;height: 100%" /></a>';
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                    }
                                    else if (fields[4] != '' && fields[5] == '' && fields[6] == '')
                                    {
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                    }
                                    else if (fields[4] != '' && fields[5] != '' && fields[6] == '')
                                    {
                                        html += '<a href="/blog/'+ fields[1] + '"><img src="https://' + fields[5] + '" style="border-radius: 5px;width: 100%;height: 100%" /></a>';
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 7px rgba(0, 0, 0, 0.2), 0px 0px 7px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                    }
                                    
                                    // youtube
                                    else if (fields[4] != '' && fields[5] == '' && fields[6] != '')
                                    {
                                        document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="border-radius: 5px;text-align:left"></div>';
                                        embedVideo(fields[6], 'featured-video-container' + (i + 1));
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br><img src="https://' + fields[4] + '" style="width: 32px;height: 32px;border-radius: 50%;border: 2px solid white;box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2), 0px 0px 8px rgba(0, 0, 0, 0.19);" />&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                        
                                        document.getElementById("featuredposttitle" + (i + 1)).style.paddingTop = "4px";
                                    }
                                    else if (fields[4] == '' && fields[5] == '' && fields[6] != '')
                                    {
                                        document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML = '<div id="featured-video-container' + (i + 1) + '" style="border-radius: 5px;text-align:left"></div>';
                                        embedVideo(fields[6], 'featured-video-container' + (i + 1));
                                        htmltitle = '<a href="/blog/'+ fields[1] + '">' + fields[0] + '</a>' + '<br><i><span style="font-size:14px">' + fields[3] + '</span></i>';// + '<br>&nbsp;<i>' + fields[2] + ', ' + fields[3] + '</i></a></p>';
                                        
                                        document.getElementById("featuredposttitle" + (i + 1)).style.paddingTop = "4px";
                                    }
                                }
                            }
                            
                            document.getElementById("featuredpostcontainer" + (i + 1)).innerHTML += html;
                            document.getElementById("featuredposttitle" + (i + 1)).innerHTML = htmltitle;
                            
                            if ((width >= 768 && i > 0) || i == 0) {
                                document.getElementById("blogGridItem" + (i + 1)).style.display = "";
                                localcount++;
                            }
                            //document.getElementById("featuredposttitle" + (i + 1)).style.display  = "block";
                            //document.getElementById("featuredposttitle" + (i + 1)).style.padding = "10px 10px 0.5px 10px";
                            
                            if (i == 0)
                            {
                                document.getElementById("featuredpost").style.display = "";
                                //document.getElementById("indicatorCard").style.marginTop = "0px";
                            }
                        }
                    }
                    
                    updateColumns();
                    //document.getElementById("blogGrid").style.columnCount = localcount < minGridNum && window.innerWidth >= 768 ? minGridNum : localcount;
                }
            });

            if (exchange == "HKEX")
            {
                $.ajax({
                    url : "db_get_CCASS.php?data=" + encodeURIComponent(parseInt(stockcode.replace('.HK', ''), 10)) + '&from_date=' + firstTradedate,
                    success : function(result){
                        
                        var CCASSshare = [];
                        var CCASSpercent = [];
                        const width = window.innerWidth;
                        var jsonObject = result.split("\n");
                        for (var i = 0; i < jsonObject.length; i++) {
                            var fields = jsonObject[i].split(';');
                            if (fields.length == 3)
                            {
                                var tradedate = utc(str2date(formatDate(str2date(fields[0]))));
                                CCASSshare.push([tradedate, parseFloat(fields[1]) * 1000]);
                                CCASSpercent.push([tradedate, parseFloat(fields[2])]);
                            }
                        }
                        
                        if (CCASSshare.length > 0 && CCASSpercent.length > 0)
                        {
                            document.getElementById("HKsouthbound").style.display = "";
                            
                            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                            var rangeselector = 4;
                            //var creditYoffset = 26;
                            if (browserwidth > 576)
                            {
                                rangeselector = 7;
                                //creditYoffset = 3;
                            }
                            
                            CCASS('chartcontainerHKSB', dailydata, stockcode, CCASSshare, CCASSpercent, stockcode + " Stock Connect Southbound Shareholding", stockname_en_tc.replace(dot, " ● ").substring(0, 45), "Stock Price", "", "day", rangeselector, -622 + creditYoffset);
                        }
                    }
                });
            }
            
            //var dailydata = text2data(pricedata);
            if (dailydata.length > 0)
            {
                TSFchart(stockcode, pricedata, 'chartcontainerTSF', stockname_en_tc.replace(dot, " ● ").substring(0, 45));
                
                var lineCount = 0;
                var dailyclose = dailydata[dailydata.length - 1][4];
                var TAcommentTXT = ['<table id="TAcommentTable" style="width:100%; line-height: 1">'];
                var TAcommentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
                var TAcommentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
                var TAcommentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
                //var emadaily = ema(dailydata, 20);
                var shortMAperiod = 20;
                var medMAperiod = 50;
                var smaSdaily = sma(dailydata, shortMAperiod);
                var smaMdaily = sma(dailydata, medMAperiod);
                var smaLdaily = sma(dailydata, longMAperiod);
                
                var rsidaily = rsi(dailydata, 14);
                var rsiMAdaily = sma(rsidaily, 9);
                var rsiMAdaily2 = sma(rsidaily, 4);
                var macddaily = macd(dailydata, 12, 26, 9);
                
                var weeklydata = dataCompression_weekly(dailydata);
                var rsiweekly = rsi(weeklydata, 14);
                var rsiMAweekly = sma(rsiweekly, 3);
                var macdweekly = macd(weeklydata, 12, 26, 9);
                
                var monthlydata = dataCompression_monthly(dailydata);
                var rsimonthly = rsi(monthlydata, 14);
                //var rsiMAmonthly = sma(rsimonthly, 9);
                
                // trendguage
                //trendgaugevalue = trendgaugeCalc(rsidaily, rsiweekly, rsiMAdaily, rsiMAdaily2, rsiMAweekly, smaSdaily, dailyclose);
                /*if (rsidaily != null && rsiweekly != null && rsiMAdaily != null && rsiMAweekly != null &&
                    rsidaily.length > 0 && rsiweekly.length > 0 && rsiMAdaily.length > 0 && rsiMAweekly.length > 0 &&
                    smaSdaily != null && smaSdaily.length > 0)
                {
                    var rsidailyvalue = rsidaily[rsidaily.length - 1][1];
                    var rsiweeklyvalue = rsiweekly[rsiweekly.length - 1][1];
                    var rsiupperlimit = 70;
                    var rsilowerlimit = 30;
                    rsidailyvalue = rsidailyvalue > rsiupperlimit? rsiupperlimit : rsidailyvalue < rsilowerlimit? rsilowerlimit : rsidailyvalue;
                    rsiweeklyvalue = rsiweeklyvalue > rsiupperlimit? rsiupperlimit : rsiweeklyvalue < rsilowerlimit? rsilowerlimit : rsiweeklyvalue;
                    trendgaugevalue = rangeconverter(rsidailyvalue, rsilowerlimit, rsiupperlimit, 0, 90);
                    if (rsiweeklyvalue > 50)
                    {
                        percentage = rangeconverter(rsiweeklyvalue, 50, rsiupperlimit, 50, 100); // weekly rsi weighted 0.5 only
                        trendgaugevalue *= percentage / 100;
                        trendgaugevalue = rsiMAdaily2[rsiMAdaily2.length - 1][1] < rsiMAdaily[rsiMAdaily.length - 1][1] && rsiweeklyvalue < rsiMAweekly[rsiMAweekly.length - 1][1] && dailyclose < smaSdaily[smaSdaily.length - 1][1]? 180 - trendgaugevalue : trendgaugevalue;
                        //trendgaugevalue = trendgaugevalue > 170 && rsiweeklyvalue > 53 ? 170 : trendgaugevalue;
                    }
                    else
                    {
                        percentage = rangeconverter(rsiweeklyvalue, rsilowerlimit, 50, 50, 100);
                        trendgaugevalue *= percentage / 100;                    
                        trendgaugevalue = rsiMAdaily2[rsiMAdaily2.length - 1][1] < rsiMAdaily[rsiMAdaily.length - 1][1] && rsiweeklyvalue < rsiMAweekly[rsiMAweekly.length - 1][1] && dailyclose < smaSdaily[smaSdaily.length - 1][1]? 270 - trendgaugevalue : 270 + trendgaugevalue;
                        //trendgaugevalue = trendgaugevalue > 350  && rsiweeklyvalue < 47 ? 350 : trendgaugevalue;
                    }
                    
                    if (isNaN(trendgaugevalue))
                        trendgaugevalue = -1;
                }*/
                
                // price vs sma
                var tmp = 0;
                var tmpcomment = "";
                var smaCount = 3;
                if (smaSdaily != null && smaSdaily.length > 0)
                {
                    if (smaSdaily.length > 1 && dailyclose > smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 2][4] <= smaSdaily[smaSdaily.length - 2][1])
                    {
                        tmp++;
                        tmpcomment = "Price crossed above daily MA" + shortMAperiod;
                    }
                    else if (smaSdaily.length > 1 && dailyclose < smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 2][4] >= smaSdaily[smaSdaily.length - 2][1])
                    {
                        tmpcomment = "Price crossed below daily MA" + shortMAperiod;
                    }
                    else if (smaSdaily.length > 2 && dailyclose > smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 2][4] > smaSdaily[smaSdaily.length - 2][1] && dailydata[dailydata.length - 3][4] <= smaSdaily[smaSdaily.length - 3][1])
                    {
                        tmp++;
                        tmpcomment = "Price crossed above daily MA" + shortMAperiod + " one trading day ago";
                    }
                    else if (smaSdaily.length > 2 && dailyclose < smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 2][4] < smaSdaily[smaSdaily.length - 2][1] && dailydata[dailydata.length - 3][4] >= smaSdaily[smaSdaily.length - 3][1])
                    {
                        tmpcomment = "Price crossed below daily MA" + shortMAperiod + " one trading day ago";
                    }
                    else if (smaSdaily.length > 3 && dailyclose > smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 3][4] > smaSdaily[smaSdaily.length - 3][1] && dailydata[dailydata.length - 4][4] <= smaSdaily[smaSdaily.length - 4][1])
                    {
                        tmp++;
                        tmpcomment = "Price crossed above daily MA" + shortMAperiod + " two trading days ago";
                    }
                    else if (smaSdaily.length > 3 && dailyclose < smaSdaily[smaSdaily.length - 1][1] && dailydata[dailydata.length - 3][4] < smaSdaily[smaSdaily.length - 3][1] && dailydata[dailydata.length - 4][4] >= smaSdaily[smaSdaily.length - 4][1])
                    {
                        tmpcomment = "Price crossed below daily MA" + shortMAperiod + " two trading days ago";
                    }                
                    else if (dailyclose > smaSdaily[smaSdaily.length - 1][1])
                    {
                        tmp++;
                        tmpcomment = "Price > daily MA" + shortMAperiod;
                    }
                    else if (dailyclose < smaSdaily[smaSdaily.length - 1][1])
                    {
                        tmpcomment = "Price < daily MA" + shortMAperiod;
                    }
                    else
                        tmpcomment = "Price = daily MA" + shortMAperiod;
                }
                else
                    smaCount--;
                
                if (smaMdaily != null && smaMdaily.length > 0)
                {
                    if (smaMdaily.length > 1 && dailyclose > smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 2][4] <= smaMdaily[smaMdaily.length - 2][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + medMAperiod;
                    }
                    else if (smaMdaily.length > 1 && dailyclose < smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 2][4] >= smaMdaily[smaMdaily.length - 2][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + medMAperiod;
                    }
                    else if (smaMdaily.length > 2 && dailyclose > smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 2][4] > smaMdaily[smaMdaily.length - 2][1] && dailydata[dailydata.length - 3][4] <= smaMdaily[smaMdaily.length - 3][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + medMAperiod + " one trading day ago";
                    }
                    else if (smaMdaily.length > 2 && dailyclose < smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 2][4] < smaMdaily[smaMdaily.length - 2][1] && dailydata[dailydata.length - 3][4] >= smaMdaily[smaMdaily.length - 3][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + medMAperiod + " one trading day ago";
                    }
                    else if (smaMdaily.length > 3 && dailyclose > smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 3][4] > smaMdaily[smaMdaily.length - 3][1] && dailydata[dailydata.length - 4][4] <= smaMdaily[smaMdaily.length - 4][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + medMAperiod + " two trading days ago";
                    }
                    else if (smaMdaily.length > 3 && dailyclose < smaMdaily[smaMdaily.length - 1][1] && dailydata[dailydata.length - 3][4] < smaMdaily[smaMdaily.length - 3][1] && dailydata[dailydata.length - 4][4] >= smaMdaily[smaMdaily.length - 4][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + medMAperiod + " two trading days ago";
                    }                
                    else if (dailyclose > smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp++;
                        tmpcomment += "; price > daily MA" + medMAperiod;
                    }
                    else if (dailyclose < smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmpcomment += "; price < daily MA" + medMAperiod;
                    }
                    else
                        tmpcomment += "; price = daily MA" + medMAperiod;
                }
                else
                    smaCount--;
                
                if (smaLdaily != null && smaLdaily.length > 0)
                {
                    if (smaLdaily.length > 1 && dailyclose > smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 2][4] <= smaLdaily[smaLdaily.length - 2][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + longMAperiod;
                    }
                    else if (smaLdaily.length > 1 && dailyclose < smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 2][4] >= smaLdaily[smaLdaily.length - 2][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + longMAperiod;
                    }
                    else if (smaLdaily.length > 2 && dailyclose > smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 2][4] > smaLdaily[smaLdaily.length - 2][1] && dailydata[dailydata.length - 3][4] <= smaLdaily[smaLdaily.length - 3][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + longMAperiod + " one trading day ago";
                    }
                    else if (smaLdaily.length > 2 && dailyclose < smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 2][4] < smaLdaily[smaLdaily.length - 2][1] && dailydata[dailydata.length - 3][4] >= smaLdaily[smaLdaily.length - 3][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + longMAperiod + " one trading day ago";
                    }
                    else if (smaLdaily.length > 3 && dailyclose > smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 3][4] > smaLdaily[smaLdaily.length - 3][1] && dailydata[dailydata.length - 4][4] <= smaLdaily[smaLdaily.length - 4][1])
                    {
                        tmp++;
                        tmpcomment += "; price crossed above daily MA" + longMAperiod + " two trading days ago";
                    }
                    else if (smaLdaily.length > 3 && dailyclose < smaLdaily[smaLdaily.length - 1][1] && dailydata[dailydata.length - 3][4] < smaLdaily[smaLdaily.length - 3][1] && dailydata[dailydata.length - 4][4] >= smaLdaily[smaLdaily.length - 4][1])
                    {
                        tmpcomment += "; price crossed below daily MA" + longMAperiod + " two trading days ago";
                    }                      
                    else if (dailyclose > smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp++;
                        if (tmp == 0)
                            tmpcomment += "; but price > daily MA" + longMAperiod;
                        else
                            tmpcomment += "; price > daily MA" + longMAperiod;
                    }
                    else if (dailyclose < smaLdaily[smaLdaily.length - 1][1])
                    {
                        if (tmp == 2)
                            tmpcomment += "; but price < daily MA" + longMAperiod;
                        else
                            tmpcomment += "; price < daily MA" + longMAperiod;
                    }
                    else
                        tmpcomment += "; price = daily MA" + longMAperiod;
                }
                else
                    smaCount--;
                
                if (tmpcomment != "")
                {
                    lineCount++;
                    if (tmp / smaCount > 0.5) // 2, 3 is good
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp / smaCount < 0.5) // 1, 2 is bad
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));
                }
                
                // sma vs sma
                tmpcomment = "";
                tmp = 0;
                if (smaMdaily != null && smaSdaily != null && smaMdaily.length > 0)
                {
                    if (smaMdaily.length > 1 && smaSdaily[smaSdaily.length - 1][1] > smaMdaily[smaMdaily.length - 1][1] && smaSdaily[smaSdaily.length - 2][1] <= smaMdaily[smaMdaily.length - 2][1])
                    {
                        tmp++;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed above MA" + medMAperiod;
                    }
                    else if (smaMdaily.length > 1 && smaSdaily[smaSdaily.length - 1][1] < smaMdaily[smaMdaily.length - 1][1] && smaSdaily[smaSdaily.length - 2][1] >= smaMdaily[smaMdaily.length - 2][1])
                    {
                        tmp += 3;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed below MA" + medMAperiod;
                    }
                    else if (smaMdaily.length > 2 && smaSdaily[smaSdaily.length - 2][1] > smaMdaily[smaMdaily.length - 2][1] && smaSdaily[smaSdaily.length - 3][1] <= smaMdaily[smaMdaily.length - 3][1] &&
                            smaSdaily[smaSdaily.length - 1][1] > smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp++;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed above MA" + medMAperiod + " one trading day ago";
                    }
                    else if (smaMdaily.length > 2 && smaSdaily[smaSdaily.length - 2][1] < smaMdaily[smaMdaily.length - 2][1] && smaSdaily[smaSdaily.length - 3][1] >= smaMdaily[smaMdaily.length - 3][1] && 
                            smaSdaily[smaSdaily.length - 1][1] < smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp += 3;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed below MA" + medMAperiod + " one trading day ago";
                    }
                    else if (smaMdaily.length > 3 && smaSdaily[smaSdaily.length - 3][1] > smaMdaily[smaMdaily.length - 3][1] && smaSdaily[smaSdaily.length - 4][1] <= smaMdaily[smaMdaily.length - 4][1] &&
                            smaSdaily[smaSdaily.length - 1][1] > smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp++;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed above MA" + medMAperiod + " two trading days ago";
                    }
                    else if (smaMdaily.length > 3 && smaSdaily[smaSdaily.length - 3][1] < smaMdaily[smaMdaily.length - 3][1] && smaSdaily[smaSdaily.length - 4][1] >= smaMdaily[smaMdaily.length - 4][1] && 
                            smaSdaily[smaSdaily.length - 1][1] < smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp += 3;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed below MA" + medMAperiod + " two trading days ago";
                    }
                    else if (smaMdaily.length > 4 && smaSdaily[smaSdaily.length - 4][1] > smaMdaily[smaMdaily.length - 4][1] && smaSdaily[smaSdaily.length - 5][1] <= smaMdaily[smaMdaily.length - 5][1] &&
                            smaSdaily[smaSdaily.length - 1][1] > smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp++;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed above MA" + medMAperiod + " three trading days ago";
                    }
                    else if (smaMdaily.length > 4 && smaSdaily[smaSdaily.length - 4][1] < smaMdaily[smaMdaily.length - 4][1] && smaSdaily[smaSdaily.length - 5][1] >= smaMdaily[smaMdaily.length - 5][1] && 
                            smaSdaily[smaSdaily.length - 1][1] < smaMdaily[smaMdaily.length - 1][1])
                    {
                        tmp += 3;
                        tmpcomment = "Daily MA" + shortMAperiod + " crossed below MA" + medMAperiod + " three trading days ago";
                    }                
                }
                
                if (smaLdaily != null && smaMdaily != null && smaLdaily.length > 0)
                {
                    if (smaLdaily.length > 1 && smaMdaily[smaMdaily.length - 1][1] > smaLdaily[smaLdaily.length - 1][1] && smaMdaily[smaMdaily.length - 2][1] <= smaLdaily[smaLdaily.length - 2][1])
                    {
                        tmp++;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed above MA" + longMAperiod + " (Golden Cross)";
                    }
                    else if (smaLdaily.length > 1 && smaMdaily[smaMdaily.length - 1][1] < smaLdaily[smaLdaily.length - 1][1] && smaMdaily[smaMdaily.length - 2][1] >= smaLdaily[smaLdaily.length - 2][1])
                    {
                        tmp += 6;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed below MA" + longMAperiod + " (Death Cross)";
                    }
                    else if (smaLdaily.length > 2 && smaMdaily[smaMdaily.length - 2][1] > smaLdaily[smaLdaily.length - 2][1] && smaMdaily[smaMdaily.length - 3][1] <= smaLdaily[smaLdaily.length - 3][1] &&
                            smaMdaily[smaMdaily.length - 1][1] > smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp++;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed above MA" + longMAperiod + " (Golden Cross) one trading day ago";
                    }
                    else if (smaLdaily.length > 2 && smaMdaily[smaMdaily.length - 2][1] < smaLdaily[smaLdaily.length - 2][1] && smaMdaily[smaMdaily.length - 3][1] >= smaLdaily[smaLdaily.length - 3][1] && 
                            smaMdaily[smaMdaily.length - 1][1] < smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp += 6;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed below MA" + longMAperiod + " (Death Cross) one trading day ago";
                    }
                    else if (smaLdaily.length > 3 && smaMdaily[smaMdaily.length - 3][1] > smaLdaily[smaLdaily.length - 3][1] && smaMdaily[smaMdaily.length - 4][1] <= smaLdaily[smaLdaily.length - 4][1] &&
                            smaMdaily[smaMdaily.length - 1][1] > smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp++;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed above MA" + longMAperiod + " (Golden Cross) two trading days ago";
                    }
                    else if (smaLdaily.length > 3 && smaMdaily[smaMdaily.length - 3][1] < smaLdaily[smaLdaily.length - 3][1] && smaMdaily[smaMdaily.length - 4][1] >= smaLdaily[smaLdaily.length - 4][1] && 
                            smaMdaily[smaMdaily.length - 1][1] < smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp += 6;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed below MA" + longMAperiod + " (Death Cross) two trading days ago";
                    }
                    else if (smaLdaily.length > 4 && smaMdaily[smaMdaily.length - 4][1] > smaLdaily[smaLdaily.length - 4][1] && smaMdaily[smaMdaily.length - 5][1] <= smaLdaily[smaLdaily.length - 5][1] &&
                            smaMdaily[smaMdaily.length - 1][1] > smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp++;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed above MA" + longMAperiod + " (Golden Cross) three trading days ago";
                    }
                    else if (smaLdaily.length > 4 && smaMdaily[smaMdaily.length - 4][1] < smaLdaily[smaLdaily.length - 4][1] && smaMdaily[smaMdaily.length - 5][1] >= smaLdaily[smaLdaily.length - 5][1] && 
                            smaMdaily[smaMdaily.length - 1][1] < smaLdaily[smaLdaily.length - 1][1])
                    {
                        tmp += 6;
                        if (tmpcomment != "")
                            tmpcomment += ", d";
                        else 
                            tmpcomment += "D";
                        tmpcomment += "aily MA" + medMAperiod + " crossed below MA" + longMAperiod + " (Death Cross) three trading days ago";
                    }                
                    
                }            
    
                if (tmpcomment != "")
                {
                    lineCount++;
                    if (tmp == 1 || tmp == 2)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 3 || tmp == 6 || tmp == 9)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else if (tmp != 0)
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));
                }
                
                // Daily MACD
                tmpcomment = "";
                tmp = 0;
                if (macddaily != null && macddaily.length > 0 && macddaily[macddaily.length - 1][2] != null) // && signal != null
                {
                    if (macddaily.length > 1 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 1][1] > macddaily[macddaily.length - 1][2] && macddaily[macddaily.length - 2][1] <= macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 2][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Daily MACD crossed above Signal," + macdZeroTXT(macddaily, true, true, true);
                    }
                    else if (macddaily.length > 1 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 1][1] < macddaily[macddaily.length - 1][2] && macddaily[macddaily.length - 2][1] >= macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 2][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed below Signal," + macdZeroTXT(macddaily, true, false);
                    }
                    else if (macddaily.length > 1 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 1][1] > macddaily[macddaily.length - 1][2] && macddaily[macddaily.length - 2][1] <= macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 2][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed above Signal," + macdZeroTXT(macddaily, false, false, true);
                    }
                    else if (macddaily.length > 1 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 1][1] < macddaily[macddaily.length - 1][2] && macddaily[macddaily.length - 2][1] >= macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 2][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Daily MACD crossed below Signal," + macdZeroTXT(macddaily, false, true, true);
                    }
                    else if (macddaily.length > 2 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 2][1] > macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 3][1] <= macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 3][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Daily MACD crossed above Signal one trading day ago," + macdZeroTXT(macddaily, true, true, true);
                    }
                    else if (macddaily.length > 2 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 2][1] < macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 3][1] >= macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 3][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed below Signal one trading day ago," + macdZeroTXT(macddaily, true, false, true);
                    }
                    else if (macddaily.length > 2 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 2][1] > macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 3][1] <= macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 3][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed above Signal one trading day ago," + macdZeroTXT(macddaily, false, false, true);
                    }
                    else if (macddaily.length > 2 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 2][1] < macddaily[macddaily.length - 2][2] && macddaily[macddaily.length - 3][1] >= macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 3][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Daily MACD crossed below Signal one trading day ago," + macdZeroTXT(macddaily, false, true, true);
                    }                
                    else if (macddaily.length > 3 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 3][1] > macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 4][1] <= macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 4][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Daily MACD crossed above Signal two trading days ago," + macdZeroTXT(macddaily, true, true, true);
                    }
                    else if (macddaily.length > 3 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 3][1] < macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 4][1] >= macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 4][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed below Signal two trading days ago," + macdZeroTXT(macddaily, true, false, true);
                    }     
                    else if (macddaily.length > 3 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 3][1] > macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 4][1] <= macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 4][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed above Signal two trading days ago," + macdZeroTXT(macddaily, false, false, true);
                    }
                    else if (macddaily.length > 3 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 3][1] < macddaily[macddaily.length - 3][2] && macddaily[macddaily.length - 4][1] >= macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 4][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Daily MACD crossed below Signal two trading days ago," + macdZeroTXT(macddaily, false, true, true);
                    }
                    else if (macddaily.length > 4 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 4][1] > macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 5][1] <= macddaily[macddaily.length - 5][2] && macddaily[macddaily.length - 5][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Daily MACD crossed above Signal three trading days ago," + macdZeroTXT(macddaily, true, true, true);
                    }
                    else if (macddaily.length > 4 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 4][1] < macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 5][1] >= macddaily[macddaily.length - 5][2] && macddaily[macddaily.length - 5][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed below Signal three trading days ago," + macdZeroTXT(macddaily, true, false, true);
                    }                      
                    else if (macddaily.length > 4 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 4][1] > macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 5][1] <= macddaily[macddaily.length - 5][2] && macddaily[macddaily.length - 5][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD crossed above Signal three trading days ago," + macdZeroTXT(macddaily, false, false, true);
                    }
                    else if (macddaily.length > 4 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 4][1] < macddaily[macddaily.length - 4][2] && macddaily[macddaily.length - 5][1] >= macddaily[macddaily.length - 5][2] && macddaily[macddaily.length - 5][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Daily MACD crossed below Signal three trading days ago," + macdZeroTXT(macddaily, false, true, true);
                    }                     
                    else if (macddaily.length > 0 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 1][1] > macddaily[macddaily.length - 1][2])
                    {
                        tmp++;
                        tmpcomment += "Daily MACD > Signal," + macdZeroTXT(macddaily, true, true, true);
                    }
                    else if (macddaily.length > 0 && macddaily[macddaily.length - 1][1] > 0 && macddaily[macddaily.length - 1][1] < macddaily[macddaily.length - 1][2])
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD < Signal," + macdZeroTXT(macddaily, true, false, true);
                    }
                    else if (macddaily.length > 0 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 1][1] > macddaily[macddaily.length - 1][2])
                    {
                        tmp += 3;
                        tmpcomment += "Daily MACD > Signal," + macdZeroTXT(macddaily, false, false, true);
                    }
                    else if (macddaily.length > 0 && macddaily[macddaily.length - 1][1] <= 0 && macddaily[macddaily.length - 1][1] < macddaily[macddaily.length - 1][2])
                    {
                        tmp += 6;
                        tmpcomment += "Daily MACD < Signal," + macdZeroTXT(macddaily, false, true, true);
                    }
                }            
    
                if (tmpcomment != "")
                {
                    lineCount++;
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else if (tmp != 0)
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));                
                }
    
                // Daily RSI
                tmpcomment = "";
                tmp = 0;
                var rsidailyresult = rsiTXT(rsidaily, rsiMAdaily2, true);
                if (rsidailyresult != null)
                {
                    tmp = rsidailyresult[0];
                    tmpcomment = rsidailyresult[1];
                }
                
                if (tmpcomment != "")
                {
                    lineCount++;
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else// if (tmp != 0)
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));
                }    
    
                // Daily RSI Divergence
                tmpcomment = "";
                tmp = 0;
                var rsidivergencedailyresult = divergence("price", "Daily RSI", dailydata, rsidaily, 10, 5);
                //var rsidivergencedailyresult = rsidivergence(rsidaily, dailydata, 5, true);
                tmp = rsidivergencedailyresult[0];
                tmpcomment = rsidivergencedailyresult[1];

                if (tmpcomment != "")
                {
                    lineCount++;            
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                }
                    
                    
                // Weekly MACD
                tmpcomment = "";
                tmp = 0;
                if (macdweekly != null && macdweekly.length > 0)
                {
                    if (macdweekly.length > 1 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 1][1] > macdweekly[macdweekly.length - 1][2] && macdweekly[macdweekly.length - 2][1] <= macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 2][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Weekly MACD crossed above Signal," + macdZeroTXT(macdweekly, true, true, false);
                    }
                    else if (macdweekly.length > 1 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 1][1] < macdweekly[macdweekly.length - 1][2] && macdweekly[macdweekly.length - 2][1] >= macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 2][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD crossed below Signal," + macdZeroTXT(macdweekly, true, false, false);
                    }
                    else if (macdweekly.length > 1 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 1][1] > macdweekly[macdweekly.length - 1][2] && macdweekly[macdweekly.length - 2][1] <= macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 2][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD crossed above Signal," + macdZeroTXT(macdweekly, false, false, false);
                    }
                    else if (macdweekly.length > 1 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 1][1] < macdweekly[macdweekly.length - 1][2] && macdweekly[macdweekly.length - 2][1] >= macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 2][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Weekly MACD crossed below Signal," + macdZeroTXT(macdweekly, false, true, false);
                    }
                    else if (macdweekly.length > 2 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 2][1] > macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 3][1] <= macdweekly[macdweekly.length - 3][2] && macdweekly[macdweekly.length - 3][2] !== null)
                    {
                        tmp++;
                        tmpcomment += "Weekly MACD crossed above Signal one week ago," + macdZeroTXT(macdweekly, true, true, false);
                    }
                    else if (macdweekly.length > 2 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 2][1] < macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 3][1] >= macdweekly[macdweekly.length - 3][2] && macdweekly[macdweekly.length - 3][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD crossed below Signal one week ago," + macdZeroTXT(macdweekly, true, false, false);
                    }
                    else if (macdweekly.length > 2 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 2][1] > macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 3][1] <= macdweekly[macdweekly.length - 3][2] && macdweekly[macdweekly.length - 3][2] !== null)
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD crossed above Signal one week ago," + macdZeroTXT(macdweekly, false, false, false);
                    }
                    else if (macdweekly.length > 2 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 2][1] < macdweekly[macdweekly.length - 2][2] && macdweekly[macdweekly.length - 3][1] >= macdweekly[macdweekly.length - 3][2] && macdweekly[macdweekly.length - 3][2] !== null)
                    {
                        tmp += 6;
                        tmpcomment += "Weekly MACD crossed below Signal one week ago," + macdZeroTXT(macdweekly, false, true, false);
                    }                
                    else if (macdweekly.length > 0 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 1][1] > macdweekly[macdweekly.length - 1][2])
                    {
                        tmp++;
                        tmpcomment += "Weekly MACD > Signal," + macdZeroTXT(macdweekly, true, true, false);
                    }
                    else if (macdweekly.length > 0 && macdweekly[macdweekly.length - 1][1] > 0 && macdweekly[macdweekly.length - 1][1] < macdweekly[macdweekly.length - 1][2])
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD < Signal," + macdZeroTXT(macdweekly, true, false, false);
                    }
                    else if (macdweekly.length > 0 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 1][1] > macdweekly[macdweekly.length - 1][2])
                    {
                        tmp += 3;
                        tmpcomment += "Weekly MACD > Signal," + macdZeroTXT(macdweekly, false, false, false);
                    }
                    else if (macdweekly.length > 0 && macdweekly[macdweekly.length - 1][1] <= 0 && macdweekly[macdweekly.length - 1][1] < macdweekly[macdweekly.length - 1][2])
                    {
                        tmp += 6;
                        tmpcomment += "Weekly MACD < Signal," + macdZeroTXT(macdweekly, false, true, false);
                    }
                    
                }            
    
                if (tmpcomment != "")
                {
                    lineCount++;
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else if (tmp != 0)
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));                
                }
    
                    
                // Weekly RSI
                tmpcomment = "";
                tmp = 0;
                var rsiweeklyresult = rsiTXT(rsiweekly, rsiMAweekly, false);
                if (rsiweeklyresult != null)
                {
                    tmp = rsiweeklyresult[0];
                    tmpcomment = rsiweeklyresult[1];
                }
                
                if (tmpcomment != "")
                {
                    lineCount++;            
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));
                    else// if (tmp != 0)
                        TAcommentTXT.push(TAcommentHTMLsideway.format(tmpcomment));      
                }
    
                // Weekly RSI Divergence
                tmpcomment = "";
                tmp = 0;
                var rsidivergenceweeklyresult = divergence("price", "Weekly RSI", weeklydata, rsiweekly, 10, 10);
                //var rsidivergenceweeklyresult = rsidivergence(rsiweekly, weeklydata, 10, false);
                tmp = rsidivergenceweeklyresult[0];
                tmpcomment = rsidivergenceweeklyresult[1];

                if (tmpcomment != "")
                {
                    lineCount++;            
                    if (tmp == 1)
                        TAcommentTXT.push(TAcommentHTMLup.format(tmpcomment));
                    else if (tmp == 6)
                        TAcommentTXT.push(TAcommentHTMLdown.format(tmpcomment));                
                }
                
                document.getElementById("TAcomment").innerHTML = TAcommentTXT.join("") + "</table>";
                document.getElementById("TAcommentTable").style.lineHeight = 9/lineCount;            
            }

            $.ajax({
                url : "db_income4Sankey_get.php?data=" + encodeURIComponent(stockcode),
                success : function(resultSK){
                    var rowSK = resultSK.split("\n");

                    if (rowSK.length > 0)
                    {
                        var fieldsSK = rowSK[0].split("_");
                        var fieldsSK2 = "";
                        
                        if (rowSK.length == 3 && rowSK[2] == "")
                            fieldsSK2 = rowSK[1].split("_");
                            
                        if (fieldsSK.length == 14)
                        {
                            var dateSK = fieldsSK[0];
                            var totalRevenue = parseFloat(fieldsSK[1]),
                        	    costOfRevenue = parseFloat(fieldsSK[2]),
                        	    grossProfit = parseFloat(fieldsSK[3]),
                        	    totalOperatingExpenses = parseFloat(fieldsSK[4]),
                        	    totalOtherIncomeExpenseNet = parseFloat(fieldsSK[5]),
                        	    operatingIncome = parseFloat(fieldsSK[6]),
                        	    sellingGeneralAdministrative = parseFloat(fieldsSK[7]),
                        	    sellingAndMarketingExpenses = parseFloat(fieldsSK[8]),
                        	    researchDevelopment = parseFloat(fieldsSK[9])
                        	    //incomeBeforeTax = parseFloat(fieldsSK[10]),
                        	    //otherOperatingExpenses = parseFloat(fieldsSK[10]),
                        	    interestIncome = parseFloat(fieldsSK[10]),
                        	    netIncome = parseFloat(fieldsSK[11]),
                        	    taxProvision = parseFloat(fieldsSK[12]),
                        	    minorityInterest = parseFloat(fieldsSK[13]);
                        	    
                    	    var ssr = sellingGeneralAdministrative + sellingAndMarketingExpenses + researchDevelopment;
                    	    var go = grossProfit - operatingIncome;
                        	totalOperatingExpenses = (Math.abs(totalOperatingExpenses) > Math.abs(1.1 * (go)) && Math.abs(go) >= 0.9 * Math.abs(ssr)) || (totalOperatingExpenses < 0 && go > 0) || (Math.abs(totalOperatingExpenses) < Math.abs(0.9 * ssr) && Math.abs(go) >= 0.9 * Math.abs(ssr)) || Math.abs(Math.abs(go) - Math.abs(ssr)) < Math.abs(Math.abs(totalOperatingExpenses) - Math.abs(ssr)) ? go : totalOperatingExpenses;
                        	    
                    	    var totalRevenue2 = 0,
                        	    costOfRevenue2 = 0,
                        	    grossProfit2 = 0,
                        	    totalOperatingExpenses2 = 0,
                        	    totalOtherIncomeExpenseNet2 = 0,
                        	    operatingIncome2 = 0,
                        	    sellingGeneralAdministrative2 = 0,
                        	    sellingAndMarketingExpenses2 = 0,
                        	    researchDevelopment2 = 0,
                        	    //incomeBeforeTax2 = 0,
                        	    //otherOperatingExpenses2 = 0,
                        	    interestIncome2 = 0,
                        	    netIncome2 = 0,
                        	    taxProvision2 = 0,
                        	    minorityInterest2 = 0;
                        	    
                    	    if (fieldsSK2.length == 14)
                            {
                                totalRevenue2 = parseFloat(fieldsSK2[1]);
                        	    costOfRevenue2 = parseFloat(fieldsSK2[2]);
                        	    grossProfit2 = parseFloat(fieldsSK2[3]);
                        	    totalOperatingExpenses2 = parseFloat(fieldsSK2[4]);
                        	    totalOtherIncomeExpenseNet2 = parseFloat(fieldsSK2[5]);
                        	    operatingIncome2 = parseFloat(fieldsSK2[6]);
                        	    sellingGeneralAdministrative2 = parseFloat(fieldsSK2[7]);
                        	    sellingAndMarketingExpenses2 = parseFloat(fieldsSK2[8]);
                        	    researchDevelopment2 = parseFloat(fieldsSK2[9]);
                        	    //incomeBeforeTax2 = parseFloat(fieldsSK2[10]);
                        	    //otherOperatingExpenses2 = parseFloat(fieldsSK2[10]);
                        	    interestIncome2 = parseFloat(fieldsSK2[10]);
                        	    netIncome2 = parseFloat(fieldsSK2[11]);
                        	    taxProvision2 = parseFloat(fieldsSK2[12]);
                        	    minorityInterest2 = parseFloat(fieldsSK2[13]);
                        	    
                        	    var ssr2 = sellingGeneralAdministrative2 + sellingAndMarketingExpenses2 + researchDevelopment2;
                        	    var go2 = grossProfit2 - operatingIncome2;
                            	totalOperatingExpenses2 = (Math.abs(totalOperatingExpenses2) > Math.abs(1.1 * (go2)) && Math.abs(go2) >= 0.9 * Math.abs(ssr2)) || (totalOperatingExpenses2 < 0 && go2 > 0) || (Math.abs(totalOperatingExpenses2) < Math.abs(0.9 * ssr2) && Math.abs(go2) >= 0.9 * Math.abs(ssr2)) || Math.abs(Math.abs(go2) - Math.abs(ssr2)) < Math.abs(Math.abs(totalOperatingExpenses2) - Math.abs(ssr2)) ? go2 : totalOperatingExpenses2;
                            }
                            
                            var totalRevenue_yoy = totalRevenue2 != 0 ? rounding((totalRevenue / totalRevenue2 - 1) * 100, 10) : 0;
                            totalRevenue_yoy = totalRevenue2 == 0 ? '' : ' ' + (totalRevenue_yoy > 0 ? '+' + totalRevenue_yoy + '% YoY' : totalRevenue_yoy + '% YoY');
                            
                            var costOfRevenue_yoy = costOfRevenue2 != 0 ? rounding((costOfRevenue / costOfRevenue2 - 1) * 100, 10) : 0;
                            costOfRevenue_yoy = costOfRevenue2 == 0 ? '' : ' ' + (costOfRevenue_yoy > 0 ? '+' + costOfRevenue_yoy + '% YoY' : costOfRevenue_yoy + '% YoY');
                            
                            var grossProfit_yoy = grossProfit2 != 0 ? rounding((grossProfit / grossProfit2 - 1) * 100, 10) : 0;
                            grossProfit_yoy = grossProfit2 == 0 ? '' : ' ' + (grossProfit_yoy > 0 ? '+' + grossProfit_yoy + '% YoY' : grossProfit_yoy + '% YoY');
                            
                            var totalOperatingExpenses_yoy = totalOperatingExpenses2 != 0 ? rounding((totalOperatingExpenses / totalOperatingExpenses2 - 1) * 100, 10) : 0;
                            totalOperatingExpenses_yoy = totalOperatingExpenses2 == 0 ? '' : ' ' + (totalOperatingExpenses_yoy > 0 ? '+' + totalOperatingExpenses_yoy + '% YoY' : totalOperatingExpenses_yoy + '% YoY');
                            
                            var totalOtherIncomeExpenseNet_yoy = totalOtherIncomeExpenseNet2 != 0 ? rounding((totalOtherIncomeExpenseNet / totalOtherIncomeExpenseNet2 - 1) * 100, 10) : 0;
                            totalOtherIncomeExpenseNet_yoy = totalOtherIncomeExpenseNet2 == 0 ? '' : ' ' + (totalOtherIncomeExpenseNet_yoy > 0 ? '+' + totalOtherIncomeExpenseNet_yoy + '% YoY' : totalOtherIncomeExpenseNet_yoy + '% YoY');
                        	    
                    	    var operatingIncome_yoy = operatingIncome2 != 0 ? rounding((operatingIncome / operatingIncome2 - 1) * 100, 10) : 0;
                            operatingIncome_yoy = operatingIncome2 == 0 ? '' : ' ' + (operatingIncome_yoy > 0 ? '+' + operatingIncome_yoy + '% YoY' : operatingIncome_yoy + '% YoY');
                            
                            var sellingGeneralAdministrative_yoy = sellingGeneralAdministrative2 != 0 ? rounding((sellingGeneralAdministrative / sellingGeneralAdministrative2 - 1) * 100, 10) : 0;
                            sellingGeneralAdministrative_yoy = sellingGeneralAdministrative2 == 0 ? '' : ' ' + (sellingGeneralAdministrative_yoy > 0 ? '+' + sellingGeneralAdministrative_yoy + '% YoY' : sellingGeneralAdministrative_yoy + '% YoY');
                            
                            var sellingAndMarketingExpenses_yoy = sellingAndMarketingExpenses2 != 0 ? rounding((sellingAndMarketingExpenses / sellingAndMarketingExpenses2 - 1) * 100, 10) : 0;
                            sellingAndMarketingExpenses_yoy = sellingAndMarketingExpenses2 == 0 ? '' : ' ' + (sellingAndMarketingExpenses_yoy > 0 ? '+' + sellingAndMarketingExpenses_yoy + '% YoY' : sellingAndMarketingExpenses_yoy + '% YoY');
                            
                            var researchDevelopment_yoy = researchDevelopment2 != 0 ? rounding((researchDevelopment / researchDevelopment2 - 1) * 100, 10) : 0;
                            researchDevelopment_yoy = researchDevelopment2 == 0 ? '' : ' ' + (researchDevelopment_yoy > 0 ? '+' + researchDevelopment_yoy + '% YoY' : researchDevelopment_yoy + '% YoY');
                            
                            //var incomeBeforeTax_yoy = incomeBeforeTax2 != 0 ? rounding((incomeBeforeTax / incomeBeforeTax2 - 1) * 100, 10) : 0;
                            //incomeBeforeTax_yoy = incomeBeforeTax2 == 0 ? '' : ' ' + (incomeBeforeTax_yoy > 0 ? '+' + incomeBeforeTax_yoy + '% YoY' : incomeBeforeTax_yoy + '% YoY');
                            
                            //var otherOperatingExpenses_yoy = otherOperatingExpenses2 != 0 ? rounding((otherOperatingExpenses / otherOperatingExpenses2 - 1) * 100, 10) : 0;
                            //otherOperatingExpenses_yoy = otherOperatingExpenses2 == 0 ? '' : ' ' + (otherOperatingExpenses_yoy > 0 ? '+' + otherOperatingExpenses_yoy + '% YoY' : otherOperatingExpenses_yoy + '% YoY');
                            
                            var interestIncome_yoy = interestIncome2 != 0 ? rounding((interestIncome / interestIncome2 - 1) * 100, 10) : 0;
                            interestIncome_yoy = interestIncome2 == 0 ? '' : ' ' + (interestIncome_yoy > 0 ? '+' + interestIncome_yoy + '% YoY' : interestIncome_yoy + '% YoY');
                            
                            var netIncome_yoy = netIncome2 != 0 ? rounding((netIncome / netIncome2 - 1) * 100, 10) : 0;
                            netIncome_yoy = netIncome2 == 0 ? '' : ' ' + (netIncome_yoy > 0 ? '+' + netIncome_yoy + '% YoY' : netIncome_yoy + '% YoY');
                            
                            var taxProvision_yoy = taxProvision2 != 0 ? rounding((taxProvision / taxProvision2 - 1) * 100, 10) : 0;
                            taxProvision_yoy = taxProvision2 == 0 ? '' : ' ' + (taxProvision_yoy > 0 ? '+' + taxProvision_yoy + '% YoY' : taxProvision_yoy + '% YoY');
                            
                            var minorityInterest_yoy = minorityInterest2 != 0 ? rounding((minorityInterest / minorityInterest2 - 1) * 100, 10) : 0;
                            minorityInterest_yoy = minorityInterest2 == 0 ? '' : ' ' + (minorityInterest_yoy > 0 ? '+' + minorityInterest_yoy + '% YoY' : minorityInterest_yoy + '% YoY');
                            
                            totalRevenue = Math.abs(totalRevenue) >= 1 ? rounding(totalRevenue, 10) : (Math.abs(totalRevenue) >= 0.1 ? rounding(totalRevenue, 100) : (Math.abs(totalRevenue) >= 0.01 ? rounding(totalRevenue, 1000) : rounding(totalRevenue, 10000)));
                            costOfRevenue = Math.abs(costOfRevenue) >= 1 ? rounding(costOfRevenue, 10) : (Math.abs(costOfRevenue) >= 0.1 ? rounding(costOfRevenue, 100) : (Math.abs(costOfRevenue) >= 0.01 ? rounding(costOfRevenue, 1000) : rounding(costOfRevenue, 10000)));
                            grossProfit = Math.abs(grossProfit) >= 1 ? rounding(grossProfit, 10) : (Math.abs(grossProfit) >= 0.1 ? rounding(grossProfit, 100) : (Math.abs(grossProfit) >= 0.01 ? rounding(grossProfit, 1000) : rounding(grossProfit, 10000)));
                            totalOperatingExpenses = Math.abs(totalOperatingExpenses) >= 1 ? rounding(totalOperatingExpenses, 10) : (Math.abs(totalOperatingExpenses) >= 0.1 ? rounding(totalOperatingExpenses, 100) : (Math.abs(totalOperatingExpenses) >= 0.01 ? rounding(totalOperatingExpenses, 1000) : rounding(totalOperatingExpenses, 10000)));
                            totalOtherIncomeExpenseNet = Math.abs(totalOtherIncomeExpenseNet) >= 1 ? rounding(totalOtherIncomeExpenseNet, 10) : (Math.abs(totalOtherIncomeExpenseNet >= 0.1) ? rounding(totalOtherIncomeExpenseNet, 100) : (Math.abs(totalOtherIncomeExpenseNet >= 0.01) ? rounding(totalOtherIncomeExpenseNet, 1000) : rounding(totalOtherIncomeExpenseNet, 10000)));
                            operatingIncome = Math.abs(operatingIncome) >= 1 ? rounding(operatingIncome, 10) : (Math.abs(operatingIncome) >= 0.1 ? rounding(operatingIncome, 100) : (Math.abs(operatingIncome) >= 0.01 ? rounding(operatingIncome, 1000) : rounding(operatingIncome, 10000)));
                            sellingGeneralAdministrative = Math.abs(sellingGeneralAdministrative) >= 1 ? rounding(sellingGeneralAdministrative, 10) : (Math.abs(sellingGeneralAdministrative) >= 0.1 ? rounding(sellingGeneralAdministrative, 100) : (Math.abs(sellingGeneralAdministrative) >= 0.01 ? rounding(sellingGeneralAdministrative, 1000) : rounding(sellingGeneralAdministrative, 10000)));
                            sellingAndMarketingExpenses = Math.abs(sellingAndMarketingExpenses) >= 1 ? rounding(sellingAndMarketingExpenses, 10) : (Math.abs(sellingAndMarketingExpenses) >= 0.1 ? rounding(sellingAndMarketingExpenses, 100) : (Math.abs(sellingAndMarketingExpenses) >= 0.01 ? rounding(sellingAndMarketingExpenses, 1000) : rounding(sellingAndMarketingExpenses, 10000)));
                            researchDevelopment = Math.abs(researchDevelopment) >= 1 ? rounding(researchDevelopment, 10) : (Math.abs(researchDevelopment) >= 0.1 ? rounding(researchDevelopment, 100) : (Math.abs(researchDevelopment) >= 0.01 ? rounding(researchDevelopment, 1000) : rounding(researchDevelopment, 10000)));
                            //incomeBeforeTax = Math.abs(incomeBeforeTax >= 1) ? rounding(incomeBeforeTax, 10) : (Math.abs(incomeBeforeTax >= 0.1) ? rounding(incomeBeforeTax, 100) : incomeBeforeTax);
                            //otherOperatingExpenses = Math.abs(otherOperatingExpenses >= 1) ? rounding(otherOperatingExpenses, 10) : (Math.abs(otherOperatingExpenses >= 0.1) ? rounding(otherOperatingExpenses, 100) : otherOperatingExpenses);
                            interestIncome = Math.abs(interestIncome) >= 1 ? rounding(interestIncome, 10) : (Math.abs(interestIncome) >= 0.1 ? rounding(interestIncome, 100) : (Math.abs(interestIncome) >= 0.01 ? rounding(interestIncome, 1000) : rounding(interestIncome, 10000)));
                            netIncome = Math.abs(netIncome) >= 1 ? rounding(netIncome, 10) : (Math.abs(netIncome) >= 0.1 ? rounding(netIncome, 100) : (Math.abs(netIncome) >= 0.01 ? rounding(netIncome, 1000) : rounding(netIncome, 10000)));
                            taxProvision = Math.abs(taxProvision) >= 1 ? rounding(taxProvision, 10) : (Math.abs(taxProvision) >= 0.1 ? rounding(taxProvision, 100) : (Math.abs(taxProvision) >= 0.01 ? rounding(taxProvision, 1000) : rounding(taxProvision, 10000)));
                            minorityInterest = Math.abs(minorityInterest) >= 1 ? rounding(minorityInterest, 10) : (Math.abs(minorityInterest) >= 0.1 ? rounding(minorityInterest, 100) : (Math.abs(minorityInterest) >= 0.01 ? rounding(minorityInterest, 1000) : rounding(minorityInterest, 10000)));
                            
                            document.getElementById("Sankey").style.display  = "";
                            
                    	    Highcharts.chart('chartcontainerSankey', {
                    	        chart: {
                                    style: {
                                        fontFamily: 'Montserrat'
                                    }
                    	        },
                                title: {
                                    text: stockcode + ' Income Statement Sankey'
                                },
                                subtitle: {
                                    text: stockname_en_tc.replace(dot, " ● ").substring(0, 45) + " ● " + 'Quarter: ' + dateSK
                                },
                                credits: {
                                    enabled: true,
                                    text: '360MiQ.com',
                                    href: '',
                                    style: {
                                        fontSize: '11px',
                                        cursor: 'arrow'
                                    },
                                    /*position: {
                                        align: 'left',
                                        x: 25,
                                        y: creditY
                                    }*/
                                },
                                accessibility: {
                                    point: {
                                        valueDescriptionFormat: '{index}. {point.from} to {point.to}, ' + '{point.weight}.'
                                    }
                                },
                                tooltip: {
                                    headerFormat: null,
                                    pointFormat: '<b>{point.fromNode.name}</b><br><pre>\u0009</pre>to<br><b>{point.toNode.name}</b>',
                                    nodeFormat: '<b>{point.name}</b>'
                                },
                                series: [{
                                    keys: ['from', 'to', 'weight'],
                            
                                    nodes: [
                                        {
                                            id: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy,
                                            color: grossProfit < 0 ? '#ff8da1' : '#8cff74',
                                            //offset: -110
                                        },
                                        //{
                                        //    id: 'Pretax Income<br>$' + incomeBeforeTax + 'B' + incomeBeforeTax_yoy,
                                        //    color: '#8cff74',
                                        //},
                                        {
                                            id: 'Interest Income<br>$' + interestIncome + 'B' + interestIncome_yoy,
                                            color: interestIncome < 0 ? '#ff8da1' : '#8cff74',
                                            column: 1,
                                            //offset: -110
                                        },
                                        {
                                            id: 'Net Income<br>$' + netIncome + 'B' + netIncome_yoy,
                                            color: netIncome < 0 ? '#ff8da1' : '#8cff74',
                                            //offset: -110
                                        },
                                        {
                                            id: 'Total Revenue<br>$' + totalRevenue + 'B' + totalRevenue_yoy,
                                            color: totalRevenue < 0 ? '#ff8da1' : '#8cff74',
                                            //offset: -110
                                        },
                                        {
                                            id: 'Cost of Revenue<br>$' + costOfRevenue + 'B' + costOfRevenue_yoy,
                                            color: costOfRevenue > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 2,
                                            //offset: 50
                                        },
                                        {
                                            id: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy,
                                            color: operatingIncome < 0 ? '#ff8da1' : '#8cff74',
                                            column: 2,
                                            //offset: 50
                                        },
                                        {
                                            id: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy,
                                            color: totalOperatingExpenses > 0 ? '#ff8da1' : '#8cff74',
                                            column: 2,
                                            //offset: 50
                                        },
                                        {
                                            id: 'G&A<br>$' + sellingGeneralAdministrative + 'B' + sellingGeneralAdministrative_yoy,
                                            color: sellingGeneralAdministrative > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3,
                                            //offset: -30
                                        },
                                        {
                                            id: 'S&M<br>$' + sellingAndMarketingExpenses + 'B' + sellingAndMarketingExpenses_yoy,
                                            color: sellingAndMarketingExpenses > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3,
                                            //offset: -30
                                        },
                                        {
                                            id: 'R&D<br>$' + researchDevelopment + 'B' + researchDevelopment_yoy,
                                            color: researchDevelopment > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3
                                        },
                                        //{
                                        //    id: 'Other Operating Expenses<br>$' + otherOperatingExpenses + 'B' + otherOperatingExpenses_yoy,
                                        //    color: otherOperatingExpenses > 0 ? '#ff8da1' : '#8cff74',
                                        //},
                                        {
                                            id: 'Tax<br>$' + taxProvision + 'B' + taxProvision_yoy,
                                            color: taxProvision > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3
                                        },
                                        {
                                            id: 'Other Income Expenses<br>$' + totalOtherIncomeExpenseNet + 'B' + totalOtherIncomeExpenseNet_yoy,
                                            color: totalOtherIncomeExpenseNet > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3
                                        },
                                        {
                                            id: 'Minority Interest<br>$' + minorityInterest + 'B' + minorityInterest_yoy,
                                            color: minorityInterest > 0 ? '#ff8da1' : '#8cff74',
                                            //column: 3
                                        },
                                    ],
                            
                                    data: [
                                        
                                        //{name: 'Interest Income<br>$' + interestIncome + 'B' + interestIncome_yoy, color: interestIncome < 0 ? '#ff8da1' : '#8cff74', from: 'Interest Income<br>$' + interestIncome + 'B' + interestIncome_yoy, to: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, weight: Math.abs(interestIncome)},

                                        {name: 'Total Revenue<br>$' + totalRevenue + 'B' + totalRevenue_yoy, color: grossProfit < 0 ? '#ff8da1' : '#8cff74', from: 'Total Revenue<br>$' + totalRevenue + 'B' + totalRevenue_yoy, to: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy, weight: Math.abs(grossProfit)},
                                        {name: 'Total Revenue<br>$' + totalRevenue + 'B' + totalRevenue_yoy, color: costOfRevenue > 0 ? '#ff8da1' : '#8cff74', from: 'Total Revenue<br>$' + totalRevenue + 'B' + totalRevenue_yoy, to: 'Cost of Revenue<br>$' + costOfRevenue + 'B' + costOfRevenue_yoy, weight: Math.abs(costOfRevenue)},

                                        {name: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy, color: operatingIncome < 0 ? '#ff8da1' : '#8cff74', from: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy, to: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, weight: Math.abs(operatingIncome)},
                                        {name: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy, color: totalOperatingExpenses > 0 ? '#ff8da1' : '#8cff74',  from: 'Gross Profit<br>$' + grossProfit + 'B' + grossProfit_yoy, to: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, weight: Math.abs(totalOperatingExpenses)},
                                        
                                        //{name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, weight: operatingIncome},
                                        {name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, color: totalOtherIncomeExpenseNet > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Other Income Expenses<br>$' + totalOtherIncomeExpenseNet + 'B' + totalOtherIncomeExpenseNet_yoy, weight: Math.abs(totalOtherIncomeExpenseNet)},
                                        //{name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, color: otherOperatingExpenses > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Other Operating Expenses<br>$' + otherOperatingExpenses + 'B' + otherOperatingExpenses_yoy, weight: Math.abs(otherOperatingExpenses)},
                                        
                                        {name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, color: minorityInterest > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Minority Interest<br>$' + minorityInterest + 'B' + minorityInterest_yoy, weight: Math.abs(minorityInterest)},
                                        {name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, color: taxProvision > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Tax<br>$' + taxProvision + 'B' + taxProvision_yoy, weight: Math.abs(taxProvision)},
                                        {name: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, color: netIncome < 0 ? '#ff8da1' : '#8cff74', from: 'Operating Income<br>$' + operatingIncome + 'B' + operatingIncome_yoy, to: 'Net Income<br>$' + netIncome + 'B' + netIncome_yoy, weight: Math.abs(netIncome)},
                                        
                                        {name: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, color: researchDevelopment > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, to: 'R&D<br>$' + researchDevelopment + 'B' + researchDevelopment_yoy, weight: Math.abs(researchDevelopment)},
                                        {name: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, color: sellingGeneralAdministrative > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, to: 'G&A<br>$' + sellingGeneralAdministrative + 'B' + sellingGeneralAdministrative_yoy, weight: Math.abs(sellingGeneralAdministrative)},
                                        {name: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, color: sellingAndMarketingExpenses > 0 ? '#ff8da1' : '#8cff74', from: 'Operating Expenses<br>$' + totalOperatingExpenses + 'B' + totalOperatingExpenses_yoy, to: 'S&M<br>$' + sellingAndMarketingExpenses + 'B' + sellingAndMarketingExpenses_yoy, weight: Math.abs(sellingAndMarketingExpenses)},
                                    ],
                                    type: 'sankey',
                                    //name: 'Sankey demo series'
                                }]
                            
                            });
                        }
                    }
                }
            });
                
                
            //var tchart = trendgauge("gaugecontainer1", trendgaugevalue, -1, stockcode, "#", '9.2px', undefined, '');
            var trendgaugevalueIndustry = -1;


            var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
            var rangeselector = 1;
            var creditYoffset = 26;
            if (browserwidth > 576)
            {
                rangeselector = 4;
                creditYoffset = 3;
            }
            var MA_amount = $('input[name="ma_amount"]:checked').val();
            var High_amount = $('input[name="high_amount"]:checked').val();
            priceDeviationChart = priceDeviation('pricedeviationcontainer', dailydata, stockcode, MA_amount, High_amount, stockcode + " Deviation % from MA" + MA_amount + " & " + High_amount + "-day High", stockname_en_tc.replace(dot, " ● ").substring(0, 45), "Stock Price", "", "day", rangeselector, -578 + creditYoffset);

            if (dailydata.length > 0)
            {
                var lastdate = new Date(dailydata[dailydata.length - 1][0]);

                var count = 0;
                for (var i = 0; i < dailydata.length; i++) {
                    var tmpdate = new Date(dailydata[i][0]);

                    if (lastdate.getFullYear() - 5 > tmpdate.getFullYear() )
                        continue;
                    else
                    {
                        if (dictYearlyTrend[tmpdate.getFullYear()])
                        {
                            dictYearlyTrend[tmpdate.getFullYear()].push([dayofyear(tmpdate), dailydata[i][4]]);
                        }
                        else
                        {
                            if (i > 0)
                            {
                                dictYearlyTrend[tmpdate.getFullYear()] = [[0, dailydata[i - 1][4]]]; // add last day of the previous year
                            }
                            
                            if (dictYearlyTrend[tmpdate.getFullYear()])
                            {
                                dictYearlyTrend[tmpdate.getFullYear()].push([dayofyear(tmpdate), dailydata[i][4]]);
                            }
                            else
                            {
                                dictYearlyTrend[tmpdate.getFullYear()] = [[dayofyear(tmpdate), dailydata[i][4]]]; // if no last day of the previous year
                            }
                            
                            seriesname[count] = tmpdate.getFullYear();
                            count++;
                        }
                    }
                }
            }
            
            yearlyTrendChart('chartcontainerYearlyTrend', dictYearlyTrend, seriesname, stockcode, browserwidth, true, 'Stock Price', stockname_en_tc, true);
                		
            var monthlytabledata = seasonalityChart(stockcode, monthlydata, dailydata[0][4], 'chartcontainerSeasonality', stockname_en_tc.replace(dot, " ● ").substring(0, 45));
            
            if (monthlytabledata !== null)
            {
                document.getElementById("monthlytabletitle").textContent = stockcode + " Monthly Return %";
                
                var dataSet = [];
                
                
                Object.keys(monthlytabledata).forEach(function(key) {
                    var dataSettmp = [];
                    dataSettmp = [key, null, null, null, null, null, null, null, null, null, null, null, null, null];
                    for (const [key1, value1] of Object.entries(monthlytabledata[key]))
                    {
                        
                        for (const [key2, value2] of Object.entries(value1)) {
                            if (key2 == "January")
                                dataSettmp[1] = value2;
                            else if (key2 == "February")
                                dataSettmp[2] = value2;
                            else if (key2 == "March")
                                dataSettmp[3] = value2;
                            else if (key2 == "April")
                                dataSettmp[4] = value2;
                            else if (key2 == "May")
                                dataSettmp[5] = value2;
                            else if (key2 == "June")
                                dataSettmp[6] = value2;
                            else if (key2 == "July")
                                dataSettmp[7] = value2;
                            else if (key2 == "August")
                                dataSettmp[8] = value2;
                            else if (key2 == "September")
                                dataSettmp[9] = value2;
                            else if (key2 == "October")
                                dataSettmp[10] = value2;
                            else if (key2 == "November")
                                dataSettmp[11] = value2;
                            else if (key2 == "December")
                                dataSettmp[12] = value2;
                            else if (key2 == "YTD")
                                dataSettmp[13] = value2;
                        }
                        
                    }
                    dataSet.push(dataSettmp);
                });
    
                /*dataSet.forEach(r => {
                    var div1 = document.createElement('div');
                    div1.innerHTML = r[1];
                    r[1] = div1;
                 
                    var div3 = document.createElement('div');
                    div3.innerHTML = r[3];
                    r[3] = div3;
                })*/
                 
                var table = $('#monthlytable').DataTable({
                    responsive: true,
                    order: [[0, 'desc']],
                    columns: [
                        { title: 'Year', className: 'dt-body-center' },
                        { title: 'Jan', className: 'dt-body-center' },
                        { title: 'Feb', className: 'dt-body-center' },
                        { title: 'Mar', className: 'dt-body-center' },
                        { title: 'Apr', className: 'dt-body-center' },
                        { title: 'May', className: 'dt-body-center' },
                        { title: 'Jun', className: 'dt-body-center' },
                        { title: 'Jul', className: 'dt-body-center' },
                        { title: 'Aug', className: 'dt-body-center' },
                        { title: 'Sep', className: 'dt-body-center' },
                        { title: 'Oct', className: 'dt-body-center' },
                        { title: 'Nov', className: 'dt-body-center' },
                        { title: 'Dec', className: 'dt-body-center' },
                        { title: 'YTD', className: 'dt-body-center' },
                    ],
                    /*columnDefs: [{    // show +, ± sign for cells, commented out bz the table looks messy with this
                        "targets": [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13 ],
                        "render": function ( data, type, full, meta ) {
                            if (data > 0)
                                data = '+' + data;
                            else if (data === 0)
                                data = '\u00B1' + data;
                                
                            return data;
                        }
                    }],*/
                    data: dataSet,
                    searching: false, paging: false, info: false,
                    rowCallback: function(row, data, index){
                        $(row).find('td:eq(0)').css('color', 'dimgrey').css('font-weight', 'bold').css('font-size', '14px');
                        for (var i = 1; i < data.length; i++)
                        {
                            if (data[i] === null)
                                continue;
                                
                            if(data[i] >= -0.5 && data[i] < 0.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#666666').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= 0.5 && data[i] < 1.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#008f00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= 1.5 && data[i] < 4.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#009900').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= 4.5 && data[i] < 7.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#00ba00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= 7.5 && data[i] < 10.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#00dd00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= 10.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#00ff00').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= -1.5 && data[i] < -0.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#8f0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= -4.5 && data[i] < -1.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#990000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= -7.5 && data[i] < -4.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#ba0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] >= -10.5 && data[i] < -7.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#dd0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            else if(data[i] < -10.5)
                            {
                                $(row).find('td:eq('+i+')').css('background-color', '#ff0000').css('color', 'white').css('font-weight', 'bold').css('font-size', '14px');
                            }
                            
                            if(i == data.length - 1)
                            {
                                $(row).find('td:eq('+i+')').css('text-shadow', '0 0 3px #000000, 0 0 5px #000000').css('border-left', '2px solid #000');
                            }
                        }
                    }
                });
            }
            
            if (monthlydata !== null && monthlydata !== undefined && monthlydata.length > 0)
            {
                var gridNum = new Date(monthlydata[monthlydata.length - 1][0]).getFullYear() - new Date(monthlydata[0][0]).getFullYear();
                
                var min_current = 0;
                var max_current = 0;
                if (monthlydata.length > 0 && monthlydata.length - 1 > 0 && monthlydata[0].length > 0  && monthlydata[monthlydata.length - 1].length > 0)
                {
                    min_current = new Date(monthlydata[0][0]).getFullYear();
                    max_current = new Date(monthlydata[monthlydata.length - 1][0]).getFullYear();
                }
                        
                $(".js-range-slider").ionRangeSlider({
                	skin: "round",
                	prettify_enabled: false,
                	//grid_snap: true,
                	grid_num: gridNum > 5 ? 5 : (gridNum === 0 ? 1 : gridNum),
                	drag_interval: true,
                    min: min_current,
                    max: max_current,
                    from: min_current,
                    to: max_current,
                    onChange: function (data) {
                                //console.log(`Range changed for: ${e.detail.sliderId}. Min/Max range values are available in this object too`);
                                var dictIndexnameMonthly = monthlydata.map((x) => x); // clone
                                var minRangeValue = data.from;
                                var maxRangeValue = data.to;
                                
                                if (min_current == minRangeValue && max_current == maxRangeValue)
                                    return;
                                            
                                if (minRangeValue !== null && maxRangeValue !== null)
                                {
                                    var startIndex = 0;
                                    var endIndex = dictIndexnameMonthly.length - 1;
                                    
                                    for (var i = 0; i < dictIndexnameMonthly.length; i++)
                                    {
                                        if (i > 0 && new Date(dictIndexnameMonthly[i][0]).getFullYear() == minRangeValue &&
                                                    new Date(dictIndexnameMonthly[i - 1][0]).getFullYear() < minRangeValue)
                                                    startIndex = i - 1;
                                            
                                        if (new Date(dictIndexnameMonthly[i][0]).getFullYear() < maxRangeValue + 1)
                                        {
                                            endIndex = i;
                                        }
                                    }
                                }
                                
                                if (endIndex >= 0)
                                {
                                    dictIndexnameMonthly.splice(endIndex + 1);
                                }
                                
                                if (startIndex >= 0)
                                {
                                    dictIndexnameMonthly.splice(0, startIndex);
                                }

                                seasonalityChart(stockcode, dictIndexnameMonthly, dictIndexnameMonthly[0][0] == monthlydata[0][0] && new Date(dictIndexnameMonthly[0][0]).getFullYear() == minRangeValue ? dailydata[0][4] : null, 'chartcontainerSeasonality', stockname_en_tc.replace(dot, " ● ").substring(0, 45));
                                
                                min_current = minRangeValue;
                                max_current = maxRangeValue;
                            }
                });
            }

            if (valuationdata === undefined || valuationdata.length === 0)
            {
                $.ajax({
                    url : "db_fa_get.php?data=" + encodeURIComponent(stockcode),
                    success : function(resultFA){
                        var rowFA = resultFA.split("#");
                        var dividend = 0, EnterpriseValueEbitda = 0, EBITDA = 0, dividendYield = 0;
                        var mostrecentquarter;
                        var fscoreValue = -1;
                        var fscoreValue_median = -1;
                        var polarFundamental_median = -1;
                        var zscoreValue = null;
                        var mscoreValue = null;
                        var shareOutstanding = [];
                        var shareOutstanding2 = [];
                        var listFA3 = [], listFA4 = [], listFA5 = [];
                        var totalCashFromOperatingActivities = 0;
                        
                        if (rowFA.length == 13)
                        {
                            var fieldsFA1 = rowFA[0].split("_");
                            if (fieldsFA1.length == 5)
                            {
                                for (var i = 0; i < fieldsFA1.length; i++)
                                {
                                    fieldsFA1[i] = fieldsFA1[i].trim();
                                }
                                
                                if (fieldsFA1[0] != "" && fieldsFA1[0] != "00010101")
                                {
                                    mostrecentquarter = str2date(fieldsFA1[0]);
                                }
                              /*  if (fieldsFA1[1] != "")
                                {
                                    EnterpriseValueEbitda = parseFloat(fieldsFA1[1]);
                                }
                                if (fieldsFA1[2] != "")
                                {
                                    EBITDA = parseFloat(fieldsFA1[2]);
                                }*/
                                
                                if (fieldsFA1[2] != "") // 0005.HK dividend in USD, so use dividendYield 1st
                                {
                                    dividendYield = parseFloat(fieldsFA1[2]);
                                    if (dividendYield > 0)
                                        document.getElementById("dividend").textContent = dividendYield + "%";
                                }
                                else if (fieldsFA1[1] != "")
                                {
                                    dividend = parseFloat(fieldsFA1[1]);
                                    if (dividend > 0)
                                    {
                                        document.getElementById("dividend").textContent = rounding(dividend/lastprice*100, 10) + "%";
                                    }                                                
                                }
                                
                                if (fieldsFA1[3] != "")
                                {
                                    //document.getElementById("cellSector").innerHTML = "Sector: " + '<a href="screener?market='+exch+'&sector=' + encodeURIComponent(fieldsFA1[3]) + '&industry=All">'  + fieldsFA1[3] + '</a>';//.replace("Independent Power Producers", "Power Producers");
                                    document.getElementById("cellSector").innerHTML = "Sector: " + '<a href="screener?market='+exch+'&sector=' + encodeURIComponent(fieldsFA1[3]) + '">'  + fieldsFA1[3] + '</a>';//.replace("Independent Power Producers", "Power Producers");
                                    document.getElementById('cellSector').title = fieldsFA1[3];
                                }
                                if (fieldsFA1[4] != "")
                                {
                                    //document.getElementById("cellIndustry").innerHTML = "Industry: " + '<a href="screener?market='+exch+'&sector=' + fieldsFA1[3].split(' ').join('@').split('&').join('$')  + '&industry=' + fieldsFA1[4].split(' ').join('@').split('&').join('$')  + '">' + fieldsFA1[4] + '</a>';//.replace("Independent Power Producers", "Power Producers");
                                    document.getElementById("cellIndustry").innerHTML = "Industry: " + '<a href="screener?market='+exch+'&sector=' + encodeURIComponent(fieldsFA1[3]) + '&industry=' + encodeURIComponent(fieldsFA1[4]) + '">' + fieldsFA1[4] + '</a>';//.replace("Independent Power Producers", "Power Producers");
                                    document.getElementById('cellIndustry').title = fieldsFA1[4];
                                }
                            }
                            
                            var fieldsFA2 = rowFA[1].split("\n");
                            if (fieldsFA2.length >= 2)
                            {
                                var s0 = parseFloat(fieldsFA2[0].split("_")[1]);
                                var s1 = parseFloat(fieldsFA2[1].split("_")[1]);
                                if (!isNaN(s0))
                                    shareOutstanding[0] = s0;
                                if (!isNaN(s1))
                                    shareOutstanding[1] = s1;
                            }
                            
                            var rowsFA3 = rowFA[2].split("\n");
                            for (var i = 0; i < rowsFA3.length; i++)
                            {
                                var fieldsFA3 = rowsFA3[i].split("_");
                                if (fieldsFA3.length == 9)
                                {
                                    var currentratio = rounding(parseFloat(fieldsFA3[1])/parseFloat(fieldsFA3[2]), 10000);
                                    //currentratio = isNaN(currentratio) ? 0 : currentratio;
                                    //listFA3.push({date: str2date(fieldsFA3[0]), currentratio: currentratio, totalCurrentAssets:parseFloat(fieldsFA3[1]), totalCurrentLiabilities:parseFloat(fieldsFA3[2]), debt2equity: parseFloat(fieldsFA3[3]), totalAssets: parseFloat(fieldsFA3[4]), longTermDebt: parseFloat(fieldsFA3[5]), retainedEarnings: parseFloat(fieldsFA3[6]), totalLiab: parseFloat(fieldsFA3[7]), propertyPlantEquipment: parseFloat(fieldsFA3[8]), netReceivables: parseFloat(fieldsFA3[9])});
                                    listFA3.push({date: str2date(fieldsFA3[0]), currentratio: currentratio, totalCurrentAssets:parseFloat(fieldsFA3[1]), totalCurrentLiabilities:parseFloat(fieldsFA3[2]), totalAssets: parseFloat(fieldsFA3[3]), longTermDebt: parseFloat(fieldsFA3[4]), retainedEarnings: parseFloat(fieldsFA3[5]), totalLiab: parseFloat(fieldsFA3[6]), propertyPlantEquipment: parseFloat(fieldsFA3[7]), netReceivables: parseFloat(fieldsFA3[8])});
                                }
                            }
                            
                            var rowsFA4 = rowFA[3].split("\n");
                            for (var i = 0; i < rowsFA4.length; i++)
                            {
                                var fieldsFA4 = rowsFA4[i].split("_");
                                if (fieldsFA4.length == 4)
                                {
                                    listFA4.push({date: str2date(fieldsFA4[0]), netIncome: parseFloat(fieldsFA4[1]), totalCashFromOperatingActivities: parseFloat(fieldsFA4[2]), depreciation: parseFloat(fieldsFA4[3])});
                                }
                            }
                            
                            var rowsFA5 = rowFA[4].split("\n");
                            for (var i = 0; i < rowsFA5.length; i++)
                            {
                                var fieldsFA5 = rowsFA5[i].split("_");
                                if (fieldsFA5.length == 6)
                                {
                                    listFA5.push({date: str2date(fieldsFA5[0]), grossProfit: parseFloat(fieldsFA5[1]), totalRevenue: parseFloat(fieldsFA5[2]), ebit: parseFloat(fieldsFA5[3]), sellingGeneralAdministrative: parseFloat(fieldsFA5[4]), netIncomeFromContinuingOps: parseFloat(fieldsFA5[5])});
                                }
                            }
                            
                            var rowsFA6 = rowFA[5].split("\n");
                            for (var i = 0; i < rowsFA6.length; i++) // for Fscore backup
                            {
                                var fieldsFA6 = rowsFA6[i].split("_");
                                if (fieldsFA6.length == 1)
                                {
                                    shareOutstanding2.push(parseFloat(fieldsFA6[0]));
                                }
                            }
                            
                            var rowsFA7 = rowFA[6].split("\n");
                            for (var i = 0; i < rowsFA7.length; i++) // for Fscore backup
                            {
                                var fieldsFA7 = rowsFA7[i].split("_");
                                if (fieldsFA7.length == 1)
                                {
                                    totalCashFromOperatingActivities = parseFloat(fieldsFA7[0]);
                                }
                            }
                            
                            var rowsFA8 = rowFA[7].split("\n");
                            if (rowsFA8.length > 0 && rowsFA8[0] !== "")
                            {
                                var fieldsFA8 = rowsFA8[0].split("_");
                                if (fieldsFA8.length == 2)
                                {
                                    fscoreValue_median = parseFloat(fieldsFA8[0]);
                                    polarIndustryArray[2] = parseFloat(fieldsFA8[1]);
                                }
                            }
                            
                            var rowsFA9 = rowFA[8].split("\n");
                            if (rowsFA9.length > 0 && rowsFA9[0] !== "")
                            {
                                trendgaugevalueIndustry = parseFloat(rowsFA9[0]);
                            }
                            
                            var rowsFA10 = rowFA[9].split("\n");
                            if (rowsFA10.length > 0 && rowsFA10[0] !== "")
                            {
                                polarIndustryArray[1] = parseFloat(rowsFA10[0]);
                            }
                            
                            var rowsFA11 = rowFA[10].split("_");
                            if (rowsFA11.length >= 3)
                            {
                                if (rowsFA11[0] !== "")
                                {
                                    var d1 = parseFloat(rowsFA11[0]);
                                    if (d1 > 0)
                                        document.getElementById("cellMarket1d").innerHTML = '<span style="color:green;">+' + rowsFA11[0] + "%</span>";
                                    else if (d1 < 0)
                                        document.getElementById("cellMarket1d").innerHTML = '<span style="color:red;">' + rowsFA11[0] + "%</span>";
                                    else
                                        document.getElementById("cellMarket1d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA11[0] + "%</span>";
                                }
                                if (rowsFA11[1] !== "")
                                {
                                    var d5 = parseFloat(rowsFA11[1]);
                                    if (d5 > 0)
                                        document.getElementById("cellMarket5d").innerHTML = '<span style="color:green;">+' + rowsFA11[1] + "%</span>";
                                    else if (d5 < 0)
                                        document.getElementById("cellMarket5d").innerHTML = '<span style="color:red;">' + rowsFA11[1] + "%</span>";
                                    else
                                        document.getElementById("cellMarket5d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA11[1] + "%</span>";
                                }
                                if (rowsFA11[2] !== "")
                                {
                                    var d20 = parseFloat(rowsFA11[2]);
                                    if (d20 > 0)
                                        document.getElementById("cellMarket20d").innerHTML = '<span style="color:green;">+' + rowsFA11[2] + "%</span>";
                                    else if (d20 < 0)
                                        document.getElementById("cellMarket20d").innerHTML = '<span style="color:red;">' + rowsFA11[2] + "%</span>";
                                    else
                                        document.getElementById("cellMarket20d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA11[2] + "%</span>";
                                }
                            }
                            
                            var rowsFA12 = rowFA[11].split("_");
                            if (rowsFA12.length >= 3)
                            {
                                if (rowsFA12[0] !== "")
                                {
                                    var d1 = parseFloat(rowsFA12[0]);
                                    if (d1 > 0)
                                        document.getElementById("cellIndustry1d").innerHTML = '<span style="color:green;">+' + rowsFA12[0] + "%</span>";
                                    else if (d1 < 0)
                                        document.getElementById("cellIndustry1d").innerHTML = '<span style="color:red;">' + rowsFA12[0] + "%</span>";
                                    else
                                        document.getElementById("cellIndustry1d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA12[0] + "%</span>";
                                }
                                if (rowsFA12[1] !== "")
                                {
                                    var d5 = parseFloat(rowsFA12[1]);
                                    if (d5 > 0)
                                        document.getElementById("cellIndustry5d").innerHTML = '<span style="color:green;">+' + rowsFA12[1] + "%</span>";
                                    else if (d5 < 0)
                                        document.getElementById("cellIndustry5d").innerHTML = '<span style="color:red;">' + rowsFA12[1] + "%</span>";
                                    else
                                        document.getElementById("cellIndustry5d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA12[1] + "%</span>";
                                }
                                if (rowsFA12[2] !== "")
                                {
                                    var d20 = parseFloat(rowsFA12[2]);
                                    if (d20 > 0)
                                        document.getElementById("cellIndustry20d").innerHTML = '<span style="color:green;">+' + rowsFA12[2] + "%</span>";
                                    else if (d20 < 0)
                                        document.getElementById("cellIndustry20d").innerHTML = '<span style="color:red;">' + rowsFA12[2] + "%</span>";
                                    else
                                        document.getElementById("cellIndustry20d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA12[2] + "%</span>";
                                }
                            }
                            
                            var rowsFA13 = rowFA[12].split("_");
                            if (rowsFA13.length >= 3)
                            {
                                if (rowsFA13[0] !== "")
                                {
                                    var d1 = parseFloat(rowsFA13[0]);
                                    if (d1 > 0)
                                        document.getElementById("cellSector1d").innerHTML = '<span style="color:green;">+' + rowsFA13[0] + "%</span>";
                                    else if (d1 < 0)
                                        document.getElementById("cellSector1d").innerHTML = '<span style="color:red;">' + rowsFA13[0] + "%</span>";
                                    else
                                        document.getElementById("cellSector1d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA13[0] + "%</span>";
                                }
                                if (rowsFA13[1] !== "")
                                {
                                    var d5 = parseFloat(rowsFA13[1]);
                                    if (d5 > 0)
                                        document.getElementById("cellSector5d").innerHTML = '<span style="color:green;">+' + rowsFA13[1] + "%</span>";
                                    else if (d5 < 0)
                                        document.getElementById("cellSector5d").innerHTML = '<span style="color:red;">' + rowsFA13[1] + "%</span>";
                                    else
                                        document.getElementById("cellSector5d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA13[1] + "%</span>";
                                }
                                if (rowsFA13[2] !== "")
                                {
                                    var d20 = parseFloat(rowsFA13[2]);
                                    if (d20 > 0)
                                        document.getElementById("cellSector20d").innerHTML = '<span style="color:green;">+' + rowsFA13[2] + "%</span>";
                                    else if (d20 < 0)
                                        document.getElementById("cellSector20d").innerHTML = '<span style="color:red;">' + rowsFA13[2] + "%</span>";
                                    else
                                        document.getElementById("cellSector20d").innerHTML = '<span style="color:darkgrey;">\u00B1' + rowsFA13[2] + "%</span>";
                                }
                            }
                            
                            polarIndustryArray[0] = polarscoreTA(trendgaugevalueIndustry);
                            //polarchart.series[1].setData(polarIndustryArray);
                            polarchart = pchart(stockcode, polarStockArray, polarIndustryArray, isIE);
                                        
                            var fscoreValue1 = fscoreProfitabilityTXT(shareOutstanding, totalCashFromOperatingActivities, listFA3, listFA4, listFA5);
                            var fscoreValue2 = fscoreLiquidityTXT(shareOutstanding, shareOutstanding2, listFA3, listFA4, listFA5);
                            var fscoreValue3 = fscoreEfficiencyTXT(shareOutstanding, listFA3, listFA4, listFA5);
                            if (fscoreValue1 == -1 && fscoreValue2 == -1 && fscoreValue3 == -1)
                                fscoreValue = -1;
                            else
                            {
                                if (fscoreValue1 == -1)
                                    fscoreValue1 = 0;
                                if (fscoreValue2 == -1)
                                    fscoreValue2 = 0;
                                if (fscoreValue3 == -1)
                                    fscoreValue3 = 0;
                                    
                                fscoreValue = fscoreValue1 + fscoreValue2 + fscoreValue3;
                            }
                            zscoreValue = zscoreTXT(shareOutstanding, listFA3, listFA4, listFA5);
                            mscoreValue = mscoreTXT(shareOutstanding, listFA3, listFA4, listFA5);
                            
                        }

                        /*if (trendgaugevalueIndustry != -1)
                        {
                            tchart.addSeries({
                    			name:'Industry Median',
                    			data: [trendgaugevalueIndustry],
                    			dataLabels: {
                                    enabled: false
                                },        
                                tooltip: {
                                    valueSuffix: ''
                                },
                                color: '#79ABE0',
                                dial: {
                                    radius: '60%',
                    				backgroundColor: '#79ABE0',
                    				borderWidth: 0,
                    				baseWidth: 5,
                    				topWidth: 1,
                    				baseLength: '25%', // of radius
                    				rearLength: '-13%',
                                },
                                showInLegend: true,
                                marker: {
                                    enabled: false
                                }
                        	}, true);
                        }*/
                        trendgauge("gaugecontainer1", trendgaugevalue, trendgaugevalueIndustry, stockcode, "#", '9.2px', undefined, '');
                        //trendgauge("gaugecontainer1", trendgaugevalue, trendgaugevalueIndustry, stockcode, "#", '9.2px');
                        
                        if (fscoreValue == -1 && fscoreValue_median == -1)
                            fscore(stockcode, undefined, undefined, typeof mostrecentquarter === 'undefined'? '': 'Most Recent Quarter: ' + formatDateNoBreak(mostrecentquarter));
                        else
                            fscore(stockcode, fscoreValue, fscoreValue_median, typeof mostrecentquarter === 'undefined'? '': 'Most Recent Quarter: ' + formatDateNoBreak(mostrecentquarter));
                        zscore(stockcode, zscoreValue, 4, typeof mostrecentquarter === 'undefined'? '': 'Most Recent Quarter: ' + formatDateNoBreak(mostrecentquarter));
                        mscore(stockcode, mscoreValue, 4, mscoreMRQ == ""? '<span style="color:#ffffff">.</span>' : mscoreMRQ);
        
                        var resultArrayPB = null;
                        var PBresult = 0;
                        $.ajax({
                            url : "/db_PBband_get.php?data=" + encodeURIComponent(stockcode),
                            success : function(result){
                                valTablePB = result.split("\n");
                                valTablePB.reverse();
                                if (valTablePB[0] == "")
                                    valTablePB.splice(0, 1);
                                valuationdataPB = valTablePB;
                                //document.getElementById("PBComment").innerHTML = Pxband(stockcode, valTable, pricedata, type, chartcontainer, 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', "");
                                resultArrayPB = valuationBands(stockcode, valTablePB, pricedata, "PB Band", 'chartcontainerC1', 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', stockname_en_tc);
                                if (resultArrayPB !== null && resultArrayPB.length > 1)
                                {
                                    document.getElementById("PBComment").innerHTML = resultArrayPB[0];
                                    PBresult = resultArrayPB[1];
                                }

                                $.ajax({
                                    url : "/db_valuationbands_get.php?data=" + encodeURIComponent(stockcode),
                                    success : function(resultPE){
                                        valTable = resultPE.split("\n");
                                        valTable.reverse();
                                        if (valTable[0] == "")
                                            valTable.splice(0, 1);
                                        valuationdata = valTable;
                                        
                                        var surprise = [];
                                        for (i = 0; i < valuationdata.length; i++)
                                        {
                                            var fields = valuationdata[i].split(";");
                                            if (fields.length == 3 && fields[2] !== "")
                                            {
                                                surprise.push(str2date(fields[0]));
                                            }
                                        }
                                        if (surprise.length > 0)
                                        {
                                            var Difference_In_Time =  mostrecentquarter.getTime() - surprise[surprise.length - 1].getTime(); 
                                            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24); 
                                            if (Difference_In_Days <= 180)
                                            {
                                                document.getElementById("valuationtab").children[1].style.display = "inline";
                                                tab2loaded = true;
                                                document.getElementById("SurpriseComment").innerHTML = earningSurpriseChart(stockcode, valuationdata, pricedata, "Earnings Surprise", "chartcontainerB1", stockname_en_tc);
                                            }
                                        }
                                        
                                        var resultArray = valuationBands(stockcode, valTable, pricedata, 'PE Band', 'chartcontainerA1', 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', stockname_en_tc);
                                        var PEresult = 0;
                                        if (resultArray !== null && resultArray.length > 0)
                                        {
                                            document.getElementById("PEComment").innerHTML = resultArray[0];
                                            PEresult = resultArray[1];
                                        }
                                                                            
                                        polarStockArray[0] = polarscoreTA(trendgaugevalue);
                                        polarStockArray[1] = PEresult == -1 && PBresult == -1 ? -1 : PEresult == -1 ? Math.round(PBresult/2) : PBresult == -1 ? Math.round(PEresult/2) : Math.round((PEresult + PBresult)/2); // both -1=>-1, either one -1=>another one/2
                                        var tmpZ = (zscoreValue !== null && zscoreValue < 1.81? 1.81 - zscoreValue : zscoreValue === null ? 0.5 : 0);
                                        tmpZ = tmpZ > 1.5 ? 1.5 : tmpZ;
                                        var tmpM = (mscoreValue !== null && mscoreValue > -2.22? (mscoreValue + 2.22) * 2.5 : mscoreValue === null ? 0.5 : 0);
                                        tmpM = tmpM > 1.5 ? 1.5 : tmpM;
                                        var tmpFAscore = Math.round((fscoreValue - tmpZ - tmpM)/2);
                                        polarStockArray[2] = fscoreValue == -1? -1 : tmpFAscore < 0? 0 : Math.round(tmpFAscore);
                                        //polarchart = pchart(stockcode, polarStockArray, polarIndustryArray, isIE);
                                        if (polarStockArray[0] == -1 && polarStockArray[1] == -1 && polarStockArray[2] == -1 && 
                                            polarIndustryArray[0] == -1 && polarIndustryArray[1] == -1 && polarIndustryArray[2] == -1)
                                            polarchart.setTitle(null, { style: {
                                                fontSize: '12px',
                                                color: '#666666',
                                                fontWeight: 'bold'
                                            },
                                            text: 'No data to display',
                                            align: 'center',
                                            verticalAlign: 'middle',
                                            y: 4});
                                        else
                                            polarchart.series[0].setData(polarStockArray);
                                        document.getElementById("comment").innerHTML = polarComment(polarStockArray);
                                        
                                        $(window).resize(function() {
                                            setLabelEvent(isIE);
                                        });    
                                        setLabelEvent(isIE);
                                        
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
                                        
                                          // Manually show the tab content via Bootstrap's API or your own method if needed
                                          $(e.target).tab('show');
                                        });

                                        $(function() {
                                            openTabHash(); // for the initial page load
                                            window.addEventListener("hashchange", openTabHash, false); // for later changes to url
                                            
                                        });
                                    
                                        function openTabHash()
                                        {
                                            console.log('openTabHash');
                                            
                                            // Javascript to enable link to tab
                                            const urlNew = new URL(window.location);
                                            const params = urlNew.searchParams;
                                            let tab = params.get('tab');
                                            
                                            var url = document.location.toString();
                                            if (url.match('#')) {
                                                $('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show');
                                            } 
                                            else if (tab) {
                                                $('.nav-tabs a[href="#tab-' + tab + '"]').tab('show') ;
                                            }

                                            var type;
                                            if (window.location.hash == "#tab-1" || tab == "1")
                                            {
                                                chartcontainer = "chartcontainerA1";
                                                type = "PE Band";
                                                tab1loaded = true;
                                            }
                                            else if (window.location.hash == "#tab-2" || tab == "2")
                                            {
                                                if (surprise.length === 0)
                                                    window.location.hash = "#tab-1";
                                                else
                                                {
                                                    chartcontainer = "chartcontainerB1";
                                                    type = "Earnings Surprise";
                                                    tab2loaded = true;
                                                }
                                                
                                            }
                                            else if (window.location.hash == "#tab-3" || tab == "3")
                                            {
                                                chartcontainer = "chartcontainerC1";
                                                type = "PB Band";
                                                tab3loaded = true;
                                            }
                                            else if (window.location.hash != "")
                                                window.location.hash = "#tab-1"; // show tab1
                                            /*else if (window.location.hash == "#tab-4")
                                            {
                                                chartcontainer = "chartcontainerD1";
                                                type = "PC Band";
                                                tab4loaded = true;
                                            }
                                            else if (window.location.hash == "#tab-5")
                                            {
                                                chartcontainer = "chartcontainerE1";
                                            }*/
                                    
                                            if (typeof(chartcontainer) != "undefined" && typeof(type) != "undefined")
                                            {
                                                /*if (chartcontainer == "chartcontainerA1") // no need to load PE band when clicked as it is loaded at start for polar chart
                                                {
                                                    // PE Band
                                                    valuationChart("chartcontainerA1", type);
                                                }
                                                else*/ if (chartcontainer == "chartcontainerB1")
                                                    if (valuationdata === undefined || valuationdata.length === 0)
                                                    {
                                                        // PE Band
                                                        $.ajax({
                                                            url : "/db_valuationbands_get.php?data=" + encodeURIComponent(stockcode),
                                                            success : function(result){
                                                                valTable = result.split("\n");
                                                                valTable.reverse();
                                                                if (valTable[0] == "")
                                                                    valTable.splice(0, 1);
                                                                valuationdata = valTable;
                                                                document.getElementById("SurpriseComment").innerHTML = earningSurpriseChart(stockcode, valuationdata, pricedata, type, chartcontainer, stockname_en_tc);
                                                            }
                                                        });    
                                                    }
                                                    else
                                                    {
                                                        document.getElementById("SurpriseComment").innerHTML = earningSurpriseChart(stockcode, valuationdata, pricedata, type, chartcontainer, stockname_en_tc);
                                                    }
                                                else if (chartcontainer == "chartcontainerC1")
                                                {
                                                    /*
                                                    if (valuationdataPB === undefined || valuationdataPB.length === 0)
                                                    {
                                                        $.ajax({
                                                            url : "/db_PBband_get.php?data=" + stockcode,
                                                            success : function(result){
                                                                var valTable = result.split("\n");
                                                                valTable.reverse();
                                                                if (valTable[0] == "")
                                                                    valTable.splice(0, 1);
                                                                //document.getElementById("PBComment").innerHTML = Pxband(stockcode, valTable, pricedata, type, chartcontainer, 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', "");
                                                                document.getElementById("PBComment").innerHTML = valuationBands(stockcode, valTable, pricedata, type, chartcontainer, 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11', "")[0];
                                                            }
                                                        });
                                                    }*/
                                                }
                                                else if (chartcontainer == "chartcontainerD1")
                                                    ;
                                                //highstock(chartcontainer, data);
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            }
            
            // Must use loading on scrolling, otherwise, Ajax 40% times won't pass data to php
            var isLoadedSuccessfully = false;
            var isLoading = false;
            $(window).scroll(function() {
                //var aaa = $(window).scrollTop();
                //var bbb = $(document).height();
                //var ccc =  $(window).height();
            	if ($(window).scrollTop() > ($(document).height() - $(window).height()) * 0.7) {
                        if (!isLoadedSuccessfully && !isLoading && peersCode != "")
                        {
                            var dict = new Object();
                            //var isIEX = 0;
                            //if (tmpname == "NYSE" || tmpname == "NASDAQ")
                            //    isIEX = 1;
                            $.ajax({
                                url : "db_top10_get.php?data=" + encodeURIComponent(peersCode) + "&isIEX=" + isIEX,
                                /*method: "post",
                                url : "db_top10_get.php",
                
                                dataType : "html",
                                contentType: "application/x-www-form-urlencoded; charset=UTF-8", // this is the default value, so it's optional
                                data : {"data": encodeURIComponent(peersCode), "isIEX": isIEX},*/
                                beforeSend: function(){
                                    isLoading = true;
                            	},
                            	complete: function(){
                            		isLoading = false;
                            	},
                            /*	error: function (error) {
                            	    $('#loader-icon').hide();
                            	    $('#loader-tip').hide();
                                    document.getElementById("failedtable").style.visibility = "visible";
                                    document.getElementById("failedcell").textContent = "Failed to load. Please refresh the page.";
                                },*/
                                success : function(result){
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
                                            } else {
                                                dict[row[0]] = [[new Date(row[1]).getTime(), row[2]]];
                                                mindates.push(new Date(row[1]).getTime());
                                            }
                                	    }
                                    }
                                    
                                    var mindate = Math.min(...mindates);
                                    for (var i = 0; i < dailydata.length; i++) {
                                        if (dailydata[i][0] >= mindate)
                                        {
                                            if (dict[stockcode]) {
                                                 dict[stockcode].push([dailydata[i][0], dailydata[i][4]]);
                                            } else {
                                                dict[stockcode] = [[dailydata[i][0], dailydata[i][4]]];
                                            }
                                        }
                                    }
                                    
                                    dict['tmp'] = [[1, 1]]; // Add a tmp series at the end to avoid error. Will remove it in stockCompare.
                                    
                                    if (dict[stockcode])
                                    {
                                        document.getElementById("PeerPerformance").style.display  = "";
                                    
                                        peersCodeArray.push('tmp'); // sort by market cap
                                        //const stocks = Object.keys(dict); // sort by stockcode
                                        var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
                                        var rangeselector = 4;
                                        var creditYoffset = 26;
                                        if (browserwidth > 576)
                                        {
                                            rangeselector = 6;
                                            creditYoffset = 0;
                                        }
                                        stockCompare('peerPerformancecontainer', dict, peersCodeArray, stockcode + " & Peers Performance", "", "Performance %", "", "day", rangeselector, -560 + creditYoffset, 2, true, true, peersNameArray);
                                    }
                                }
                            });
                        }
            		}
            	}
            );
        }
    });   
}

/*function rsidivergence(rsi, pricedata, pricemovepercent, isDaily)
{
    var tmpcomment = "";
    var tmp = 0;
	var dailyweekly = "Daily";
	var dayweek = "trading day";
	
	if (!isDaily)
	{
		dailyweekly = "Weekly";
		dayweek = "week";
	}
	
    var barresult = findPeaksAndTroughsBarFromEnd(pricedata, pricemovepercent);
    if (barresult.peaks.length > 2 && barresult.troughs.length > 2)
    {
        var peakbar1 = barresult.peaks[0];
        var troughbar1 = barresult.troughs[0];                
        if (peakbar1 < troughbar1) // peak
        {   
            if (peakbar1 > 0) // not making a high
            {
                var peakbar2 = barresult.peaks[1];
                var peakbar3 = barresult.peaks[2];
                if (rsi.length - 1 > peakbar1)
                {
                    if (rsi.length - 1 > peakbar2 && pricedata[pricedata.length - 1 - peakbar1][4] > pricedata[pricedata.length - 1 - peakbar2][4] &&
                        rsi[rsi.length - 1 - peakbar1][1] < 0.9 * rsi[rsi.length - 1 - peakbar2][1])
                    {
                        tmp += 6;
                        tmpcomment += dailyweekly + " RSI is bearishly diverged with price";
                    }
                    else if (rsi.length - 1 > peakbar3 && pricedata[pricedata.length - 1 - peakbar1][4] > pricedata[pricedata.length - 1 - peakbar3][4] &&
                        rsi[rsi.length - 1 - peakbar1][1] < 0.9 * rsi[rsi.length - 1 - peakbar3][1])
                    {
                        tmp += 6;
                        tmpcomment += dailyweekly + " RSI is bearishly diverged with price";
                    }
                }
            }
        }
        else
        {
            if (troughbar1 > 0) // not making a low
            {
                var troughbar2 = barresult.troughs[1];
                var troughbar3 = barresult.troughs[2];
                if (rsi.length - 1 > troughbar1)
                {
                    if (rsi.length - 1 > troughbar2 && pricedata[pricedata.length - 1 - troughbar1][4] < pricedata[pricedata.length - 1 - troughbar2][4] &&
                        rsi[rsi.length - 1 - troughbar1][1] > 1.1 * rsi[rsi.length - 1 - troughbar2][1])
                    {
                        tmp++;
                        tmpcomment += dailyweekly + " RSI is bullishly diverged with price";
                    }
                    else if (rsi.length - 1 > troughbar3 && pricedata[pricedata.length - 1 - troughbar1][4] < pricedata[pricedata.length - 1 - troughbar3][4] &&
                        rsi[rsi.length - 1 - troughbar1][1] > 1.1 * rsi[rsi.length - 1 - troughbar3][1])
                    {
                        tmp++;
                        tmpcomment += dailyweekly + " RSI is bullishly diverged with price";
                    }
                }
            }
        }
    }
    
    return [tmp, tmpcomment];
}*/
function fscoreProfitabilityTXT(shareOutstanding, totalCashFromOperatingActivities, listFA3, listFA4, listFA5)
{
    var score = 0;
    var commentTXT = ['<table id="ProfitabilityCommentTable" style="width:100%; line-height: 1">'];
    var commentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';

    if (listFA4.length >= 1)
    {
        var netincomeTTM = listFA4[0].netIncome;
        var totalCashFromOperatingActivitiesTTM = listFA4[0].totalCashFromOperatingActivities;
        for (var i = 1; i < listFA4.length - 1; i++)
        {
            netincomeTTM += listFA4[i].netIncome;
            totalCashFromOperatingActivitiesTTM += listFA4[i].totalCashFromOperatingActivities;
        }
        if (totalCashFromOperatingActivitiesTTM == 0 && totalCashFromOperatingActivities != 0)
            totalCashFromOperatingActivitiesTTM = totalCashFromOperatingActivities;
            
        var niStr = "";
        if (netincomeTTM > 0)
        {
            niStr = '$' + numberformatM(netincomeTTM*1000000);
            commentTXT.push(commentHTMLup.format('Positive Net Income<sup><span style="color:darkgrey">TTM</span></sup>: ' + niStr));
            score++;
        }
        else if (netincomeTTM < 0)
        {
            niStr = '-$' + numberformatM(-netincomeTTM*1000000);
            commentTXT.push(commentHTMLdown.format('Negative Net Income<sup><span style="color:darkgrey">TTM</span></sup>: ' + niStr));        
        }
        else
        {
            niStr = '$0';
            commentTXT.push(commentHTMLdown.format('Net Income<sup><span style="color:darkgrey">TTM</span></sup> is $0'));        
        }
        
        var tcStr = "";
        if (totalCashFromOperatingActivitiesTTM > 0)
        {
            tcStr = '$' + numberformatM(totalCashFromOperatingActivitiesTTM*1000000);
            commentTXT.push(commentHTMLup.format('Positive Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup>: ' + tcStr));
            score++;
        }
        else if (totalCashFromOperatingActivitiesTTM < 0)
        {
            tcStr = '-$' + numberformatM(-totalCashFromOperatingActivitiesTTM*1000000);
            commentTXT.push(commentHTMLdown.format('Negative Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup>: ' + tcStr));        
        }
        else
        {
            tcStr = '$0';
            commentTXT.push(commentHTMLdown.format('Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup> is $0'));
        }
        
        if (totalCashFromOperatingActivitiesTTM > netincomeTTM)
        {
            commentTXT.push(commentHTMLup.format('Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup>: '+tcStr+' > Net&nbsp;Income<sup><span style="color:darkgrey">TTM</span></sup>:&nbsp;'+niStr));
            score++;
        }
        else if (totalCashFromOperatingActivitiesTTM < netincomeTTM)
            commentTXT.push(commentHTMLdown.format('Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup>: '+tcStr+' < Net&nbsp;Income<sup><span style="color:darkgrey">TTM</span></sup>:&nbsp;'+niStr));
        else
            commentTXT.push(commentHTMLdown.format('Operating Cash Flow<sup><span style="color:darkgrey">TTM</span></sup>: '+tcStr+' = Net&nbsp;Income<sup><span style="color:darkgrey">TTM</span></sup>:&nbsp;'+niStr));        
    
    
        if (listFA3.length >= 1)
        {
            var totalAssets1AVG = listFA3[0].totalAssets;
            var period = 1;
            for (var i = 1; i < listFA3.length - 1; i++)
            {
                totalAssets1AVG += listFA3[i].totalAssets;
                period++;
            }
            totalAssets1AVG /= period;
    
            var ReturnOnAssetsTTM2 = rounding(netincomeTTM / totalAssets1AVG, 10000);
            if (ReturnOnAssetsTTM2 > 0)
            {
                commentTXT.push(commentHTMLup.format('Positive Return On Assets<sup><span style="color:darkgrey">TTM</span></sup>: ' + ReturnOnAssetsTTM2));
                score++;
            }
            else if (ReturnOnAssetsTTM2 < 0)
                commentTXT.push(commentHTMLdown.format('Negative Return On Assets<sup><span style="color:darkgrey">TTM</span></sup>: ' + ReturnOnAssetsTTM2));
            else
                commentTXT.push(commentHTMLdown.format('Return On Assets<sup><span style="color:darkgrey">TTM</span></sup> is 0'));
        }
    }
    else
        score = -1;

    
/*    if (listFA4[0].netIncome > 0)
    {
        commentTXT.push(commentHTMLup.format('Positive Net Income: $' + numberformatM(listFA4[0].netIncome*1000000)));
        score++;
    }
    else if (listFA4[0].netIncome < 0)
        commentTXT.push(commentHTMLdown.format('Negative Net Income: $' + numberformatM(listFA4[0].netIncome*1000000)));        
    else
        commentTXT.push(commentHTMLdown.format('Net Income is $0'));        
    
    if (listFA4[0].totalCashFromOperatingActivities > 0)
    {
        commentTXT.push(commentHTMLup.format('Positive Operating Cash Flow: $' + numberformatM(listFA4[0].totalCashFromOperatingActivities*1000000)));
        score++;
    }
    else if (listFA4[0].totalCashFromOperatingActivities < 0)
        commentTXT.push(commentHTMLdown.format('Negative Operating Cash Flow: $' + numberformatM(listFA4[0].totalCashFromOperatingActivities*1000000)));        
    else
        commentTXT.push(commentHTMLdown.format('Operating Cash Flow is $0'));
        
    if (listFA4[0].totalCashFromOperatingActivities > listFA4[0].netIncome)
    {
        commentTXT.push(commentHTMLup.format('Operating Cash Flow: $'+numberformatM(listFA4[0].totalCashFromOperatingActivities*1000000)+' > Net&nbsp;Income:&nbsp;$'+numberformatM(listFA4[0].netIncome*1000000)));
        score++;
    }
    else if (listFA4[0].totalCashFromOperatingActivities < listFA4[0].netIncome)
        commentTXT.push(commentHTMLdown.format('Operating Cash Flow: $'+numberformatM(listFA4[0].totalCashFromOperatingActivities*1000000)+' <= Net&nbsp;Income:&nbsp;$'+numberformatM(listFA4[0].netIncome*1000000)));        
*/
    document.getElementById("Profitability").innerHTML = commentTXT.join("") + "</table>";
    document.getElementById("ProfitabilityCommentTable").style.lineHeight = 7.5/5;                                          
    
    return score;
}
function fscoreLiquidityTXT(shareOutstanding, shareOutstanding2, listFA3, listFA4, listFA5)
{
    var score = 0;
    var commentTXT = ['<table id="LiquidityCommentTable" style="width:100%; line-height: 1">'];
    var commentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';

    if (listFA3.length >= 2)
    {
        var totalAssets1AVG = listFA3[0].totalAssets;
        var period = 1;
        for (var i = 1; i < listFA3.length - 1; i++)
        {
            totalAssets1AVG += listFA3[i].totalAssets;
            period++;
        }
        var totalAssets2AVG = totalAssets1AVG - listFA3[0].totalAssets + listFA3[listFA3.length - 1].totalAssets;
        totalAssets1AVG /= period;
        totalAssets2AVG /= period;
        var LongTermDebtRatio1 = rounding(listFA3[0].longTermDebt/totalAssets1AVG, 10000);
        var LongTermDebtRatio2 = rounding(listFA3[1].longTermDebt/totalAssets2AVG, 10000);
        if (LongTermDebtRatio1 < LongTermDebtRatio2)
        {
            commentTXT.push(commentHTMLup.format('Lower Long Term Debt Ratio: ' + LongTermDebtRatio1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + LongTermDebtRatio2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
            score++;
        }
        else if (LongTermDebtRatio1 > LongTermDebtRatio2)
        {
            commentTXT.push(commentHTMLdown.format('Higher Long Term Debt Ratio: ' + LongTermDebtRatio1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + LongTermDebtRatio2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        }
        else if (LongTermDebtRatio1 == LongTermDebtRatio2)
            commentTXT.push(commentHTMLdown.format('Higher/unchanged Long Term Debt Ratio: ' + LongTermDebtRatio1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + LongTermDebtRatio2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        else
        {
            var ld1 = LongTermDebtRatio1;
            if (isNaN(ld1))
                ld1 = "NA";
                
            var ld2 = LongTermDebtRatio2;
            if (isNaN(ld2))
                ld2 = "NA";
            commentTXT.push(commentHTMLdown.format('Higher/unchanged Long Term Debt Ratio: ' + ld1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + ld2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));            
        }
            
        if (listFA3[0].currentratio > listFA3[1].currentratio)
        {
            commentTXT.push(commentHTMLup.format('Higher Current Ratio: ' + listFA3[0].currentratio + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + listFA3[1].currentratio+ '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
            score++;
        }
        else if (listFA3[0].currentratio < listFA3[1].currentratio)
        {
            commentTXT.push(commentHTMLdown.format('Lower Current Ratio: ' + listFA3[0].currentratio + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + listFA3[1].currentratio+ '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        }
        else
        {
            var cr0 = listFA3[0].currentratio;
            if (isNaN(cr0))
                cr0 = "NA";
                
            var cr1 = listFA3[1].currentratio;
            if (isNaN(cr1))
                cr1 = "NA";
            commentTXT.push(commentHTMLdown.format('Lower/unchanged Current Ratio: ' + cr0 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + cr1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        }
        

        if (shareOutstanding.length >= 2)   
        {
            if ((shareOutstanding[0] == 0 && shareOutstanding[1] == 0) || (shareOutstanding[0] === null && shareOutstanding[1] === null))
            {
                commentTXT.push(commentHTMLdown.format('Shares data not available: NA&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;NA&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
            }
            else if (shareOutstanding[0] <= shareOutstanding[1])
            {
                commentTXT.push(commentHTMLup.format('No new shares issued: ' + rounding(shareOutstanding[0], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + rounding(shareOutstanding[1], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
                score++;
            }
            else
                commentTXT.push(commentHTMLdown.format('New shares issued: ' + rounding(shareOutstanding[0], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + rounding(shareOutstanding[1], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        }
        else if (shareOutstanding2.length >= 2)   
        {
            if ((shareOutstanding2[0] == 0 && shareOutstanding2[1] == 0) || (shareOutstanding2[0] === null && shareOutstanding2[1] === null))
            {
                commentTXT.push(commentHTMLdown.format('Shares data not available: NA&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;NA&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
            }
            else if (shareOutstanding2[0] <= shareOutstanding2[1])
            {
                commentTXT.push(commentHTMLup.format('No new shares issued: ' + rounding(shareOutstanding2[0], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + rounding(shareOutstanding2[1], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
                score++;
            }
            else
                commentTXT.push(commentHTMLdown.format('New shares issued: ' + rounding(shareOutstanding2[0], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[0].date)+')</span>&nbsp;vs&nbsp;' + rounding(shareOutstanding2[1], 10) + 'M&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA3[1].date)+')</span>'));
        }
    }
    else
        score = -1;
     
    document.getElementById("Liquidity").innerHTML = commentTXT.join("") + "</table>";
    document.getElementById("LiquidityCommentTable").style.lineHeight = 7.5/5;   
    
    return score;
}
function fscoreEfficiencyTXT(shareOutstanding, listFA3, listFA4, listFA5)
{
    var score = 0;
    var commentTXT = ['<table id="EfficiencyCommentTable" style="width:100%; line-height: 1">'];
    var commentHTMLup = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLdown = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font> </td><td style="font-size:13px">{0}</td></tr>';
    var commentHTMLsideway = '<tr><td style="width:10px;"><font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font> </td><td style="font-size:13px">{0}</td></tr>';

    if (listFA5.length >= 2 && listFA3.length >= 2)
    {
        var grossProfitTTM = listFA5[0].grossProfit;
        var totalRevenueTTM = listFA5[0].totalRevenue;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            grossProfitTTM += listFA5[i].grossProfit;
            totalRevenueTTM += listFA5[i].totalRevenue;
        }
        var grossProfitTTM2 = grossProfitTTM - listFA5[0].grossProfit + listFA5[listFA5.length - 1].grossProfit;
        var totalRevenueTTM2 = totalRevenueTTM - listFA5[0].totalRevenue + listFA5[listFA5.length - 1].totalRevenue;
        var grossMargin = rounding(grossProfitTTM / totalRevenueTTM, 10000);
        var grossMargin2 = rounding(grossProfitTTM2 / totalRevenueTTM2, 10000);
        var g = (isNaN(grossMargin) ? "NA" : grossMargin);
        var g2 = (isNaN(grossMargin2) ? "NA" : grossMargin2);
        if (grossMargin > grossMargin2)
        {
            commentTXT.push(commentHTMLup.format('Higher Gross Margin<sup><span style="color:darkgrey">TTM</span></sup>: ' + g + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + g2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
            score++;
        }
        else if (grossMargin < grossMargin2)
        {
            commentTXT.push(commentHTMLdown.format('Lower Gross Margin<sup><span style="color:darkgrey">TTM</span></sup>: ' + g + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + g2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
        }
        else
            commentTXT.push(commentHTMLdown.format('Lower/unchanged Gross Margin<sup><span style="color:darkgrey">TTM</span></sup>: ' + g + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + g2 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
            
        var totalAssets1AVG = listFA3[0].totalAssets;
        var period = 1;
        for (var i = 1; i < listFA3.length - 1; i++)
        {
            totalAssets1AVG += listFA3[i].totalAssets;
            period++;
        }
        var totalAssets2AVG = totalAssets1AVG - listFA3[0].totalAssets + listFA3[listFA3.length - 1].totalAssets;
        totalAssets1AVG /= period;
        totalAssets2AVG /= period;
        
        var atr0 = rounding(totalRevenueTTM/totalAssets1AVG, 10000);
        var atr1 = rounding(totalRevenueTTM2/totalAssets2AVG, 10000);
        var a0 = (isNaN(atr0) ? "NA" : atr0);
        var a1 = (isNaN(atr1) ? "NA" : atr1);
        if (atr0 > atr1)
        {
            commentTXT.push(commentHTMLup.format('Higher Asset Turnover Ratio<sup><span style="color:darkgrey">TTM</span></sup>: ' + a0 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + a1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
            score++;
        }
        else if (atr0 < atr1)
        {
            commentTXT.push(commentHTMLdown.format('Lower Asset Turnover Ratio<sup><span style="color:darkgrey">TTM</span></sup>: ' + a0 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + a1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
        }
        else
            commentTXT.push(commentHTMLdown.format('Lower/unchanged Asset Turnover Ratio<sup><span style="color:darkgrey">TTM</span></sup>: ' + a0 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[0].date)+')</span>&nbsp;vs&nbsp;' + a1 + '&nbsp;<span style="color:darkgrey">(' +formatDateNoBreak(listFA5[1].date)+')</span>'));
    }
    else
        score = -1;
    
    document.getElementById("Efficiency").innerHTML = commentTXT.join("") + "</table>";
    document.getElementById("EfficiencyCommentTable").style.lineHeight = 7.5/5;      
    
    return score;
}
var mscoreMRQ = "";
function mscoreTXT(shareOutstanding, listFA3, listFA4, listFA5)
{
    var score = null;
    var DSRI = 0, GMI = 0, AQI = 0, SGI = 0, DEPI = 0, SGAI = 0, LVGI = 0, TATA = 0;
    var commentTXT = ['<table id="MScoreCommentTable" style="width:100%; line-height: 1;">'];
    var commentHTML = '<tr><td style="font-size:13px">{0}</td></tr>';
    var invalid = 0;
    
    if (listFA5.length >= 2 && listFA4.length >= 2 && listFA3.length >= 2)
    {
        var totalRevenueTTM = listFA5[0].totalRevenue;
        var totalRevenueTTM2 = listFA5[1].totalRevenue;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            totalRevenueTTM += listFA5[i].totalRevenue;
        }
        totalRevenueTTM2 = totalRevenueTTM - listFA5[0].totalRevenue + listFA5[listFA5.length - 1].totalRevenue;
        
        var netReceivablesTTM = listFA3[0].netReceivables;
        var netReceivablesTTM2 = listFA3[1].netReceivables;
        for (var i = 1; i < listFA3.length - 1; i++)
        {
            netReceivablesTTM += listFA3[i].netReceivables;
        }
        netReceivablesTTM2 = netReceivablesTTM - listFA3[0].netReceivables + listFA3[listFA3.length - 1].netReceivables;
        
        DSRI = rounding((netReceivablesTTM / totalRevenueTTM)/(netReceivablesTTM2 / totalRevenueTTM2), 10000);
        if (isNaN(DSRI) || !isFinite(DSRI))
        {
            DSRI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Days&nbsp;Sales in Receivables Index (DSRI)<br>= (Receivables<sub>t</sub>&nbsp;/&nbsp;Revenue<sub>t</sub>) /&nbsp;(Receivables<sub>t\u20111</sub>&nbsp;/&nbsp;Revenue<sub>t\u20111</sub>) =&nbsp;' + DSRI));

        var grossProfitTTM = listFA5[0].grossProfit;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            grossProfitTTM += listFA5[i].grossProfit;
        }
        var grossProfitTTM2 = grossProfitTTM - listFA5[0].grossProfit + listFA5[listFA5.length - 1].grossProfit;
        var grossMargin = rounding(grossProfitTTM / totalRevenueTTM, 10000);
        var grossMargin2 = rounding(grossProfitTTM2 / totalRevenueTTM2, 10000);
        GMI = rounding((grossMargin2/totalRevenueTTM2)/(grossMargin/totalRevenueTTM), 10000);
        if (isNaN(GMI) || !isFinite(GMI))
        {
            GMI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Gross&nbsp;Margin&nbsp;Index (GMI)<br>= Gross&nbsp;Margin<sub>t\u20111</sub> / Gross&nbsp;Margin<sub>t</sub> = ' + GMI));
        
        AQI = rounding((1 - (listFA3[0].totalCurrentAssets + listFA3[0].propertyPlantEquipment) / listFA3[0].totalAssets) / (1 - (listFA3[1].totalCurrentAssets + listFA3[1].propertyPlantEquipment) / listFA3[1].totalAssets), 10000);
        if (isNaN(AQI) || !isFinite(AQI))
        {
            AQI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Asset&nbsp;Quality&nbsp;Index (AQI)<br>= [1&nbsp;-&nbsp;(Current&nbsp;Assets<sub>t</sub>&nbsp;+&nbsp;PPE<sub>t</sub>)&nbsp;/&nbsp;Total&nbsp;Assets<sub>t</sub>] /&nbsp;[1&nbsp;-&nbsp;(Current&nbsp;Assets<sub>t\u20111</sub>&nbsp;+&nbsp;PPE<sub>t\u20111</sub>)&nbsp;/&nbsp;Total&nbsp;Assets<sub>t\u20111</sub>] =&nbsp;' + AQI));
                
        SGI = rounding(totalRevenueTTM / totalRevenueTTM2, 10000);
        if (isNaN(SGI) || !isFinite(SGI))
        {
            SGI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Sales&nbsp;Growth&nbsp;Index (SGI)<br>= Sales<sub>t</sub> / Sales<sub>t\u20111</sub> = ' + SGI));
                        
        var depreciationTTM = listFA4[0].depreciation;
        var depreciationTTM2 = listFA4[1].depreciation;
        for (var i = 1; i < listFA4.length - 1; i++)
        {
            depreciationTTM += listFA4[i].depreciation;
        }
        depreciationTTM2 = depreciationTTM - listFA4[0].depreciation + listFA4[listFA4.length - 1].depreciation;
        
        DEPI = rounding((depreciationTTM2 / (depreciationTTM2 + listFA3[1].propertyPlantEquipment)) / (depreciationTTM / (depreciationTTM + listFA3[0].propertyPlantEquipment)), 10000);
        if (isNaN(DEPI) || !isFinite(DEPI))
        {
            DEPI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Depreciation&nbsp;Index (DEPI)<br>= [Depreciation<sub>t\u20111</sub>&nbsp;/&nbsp;(Depreciaton<sub>t\u20111</sub>&nbsp;+&nbsp;PPE<sub>t\u20111</sub>)] /&nbsp;[Depreciation<sub>t</sub>&nbsp;/&nbsp;(Depreciaton<sub>t</sub>&nbsp;+&nbsp;PPE<sub>t</sub>)] =&nbsp;' + DEPI));
        
        var SGATTM = listFA5[0].sellingGeneralAdministrative;
        var SGATTM2 = listFA5[1].sellingGeneralAdministrative;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            SGATTM += listFA5[i].sellingGeneralAdministrative;
        }
        SGATTM2 = SGATTM - listFA5[0].sellingGeneralAdministrative + listFA5[listFA5.length - 1].sellingGeneralAdministrative;
        SGAI = rounding((SGATTM / totalRevenueTTM) / (SGATTM2 / totalRevenueTTM2), 10000);
        if (isNaN(SGAI) || !isFinite(SGAI))
        {
            SGAI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Sales&nbsp;General &amp;&nbsp;Administrative&nbsp;Expenses&nbsp;Index&nbsp;(SGAI)<br>= (SGA<sub>t</sub>&nbsp;/&nbsp;Sales<sub>t</sub>) /&nbsp;(SGA<sub>t\u20111</sub>&nbsp;/&nbsp;Sales<sub>t\u20111</sub>) =&nbsp;' + SGAI));
        
        LVGI = rounding(((listFA3[0].longTermDebt + listFA3[0].totalCurrentLiabilities) / listFA3[0].totalAssets) / ((listFA3[1].longTermDebt + listFA3[1].totalCurrentLiabilities) / listFA3[1].totalAssets), 10000);
        if (isNaN(LVGI) || !isFinite(LVGI))
        {
            LVGI = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Leverage&nbsp;Index (LVGI)<br>= [(LT&nbsp;Debt<sub>t</sub>&nbsp;+&nbsp;Current&nbsp;Liabilities<sub>t</sub>)&nbsp;/&nbsp;Total&nbsp;Assets<sub>t</sub>] /&nbsp;[(LT&nbsp;Debt<sub>t\u20111</sub>&nbsp;+&nbsp;Current&nbsp;Liabilities<sub>t\u20111</sub>)&nbsp;/&nbsp;Total&nbsp;Assets<sub>t\u20111</sub>] =&nbsp;' + LVGI));
        
        var totalCashFromOperatingActivitiesTTM = listFA4[0].totalCashFromOperatingActivities;
        for (var i = 1; i < listFA4.length - 1; i++)
        {
            totalCashFromOperatingActivitiesTTM += listFA4[i].totalCashFromOperatingActivities;
        }
        var netIncomeFromContinuingOpsTTM = listFA5[0].netIncomeFromContinuingOps;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            netIncomeFromContinuingOpsTTM += listFA5[i].netIncomeFromContinuingOps;
        }
        TATA = rounding((netIncomeFromContinuingOpsTTM - totalCashFromOperatingActivitiesTTM) / listFA3[0].totalAssets, 10000);
        if (isNaN(TATA) || !isFinite(TATA))
        {
            TATA = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('\u25CF&nbsp;Total&nbsp;Accruals to Total&nbsp;Assets (TATA)<br>= (Continuing&nbsp;Operations&nbsp;Income<sub>t</sub> -&nbsp;Operations&nbsp;Cash&nbsp;Flows<sub>t</sub>) /&nbsp;Total&nbsp;Assets<sub>t</sub> =&nbsp;' + TATA));

        score = rounding(-4.84 + 0.92 * DSRI + 0.528 * GMI + 0.404 * AQI + 0.892 * SGI + 0.115 * DEPI - 0.172 * SGAI + 4.679 * TATA - 0.327 * LVGI, 100);
    }
    
    if (isNaN(score) || !isFinite(score))
        score = null;
    var scoreTXT = score != null ? score > -2.22? ('<span style="color:red">' + score + ', a&nbsp;Manipulator&nbsp;<font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font></span>') : score < -2.22? ('<span style="color:#3FB641">' + score + ', a&nbsp;Non&nbsp;Manipulator&nbsp;<font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font></span>') : ('<span style="color:darkgrey">' + score + '</span>') : "Not&nbsp;Available";
    if (invalid == 8)
    {
        score = null;
        scoreTXT = "Not&nbsp;Available";
        commentTXT = ['<table id="MScoreCommentTable" style="width:100%; line-height: 1;">'];
    }
    document.getElementById("MScoreTitle").innerHTML = commentHTML.format('<table class="scoretitle"><tr><td valign="top">M</td><td valign="top">= -4.84 +&nbsp;0.92&nbsp;*&nbsp;DSRI +&nbsp;0.528&nbsp;*&nbsp;GMI +&nbsp;0.404&nbsp;*&nbsp;AQI +&nbsp;0.892&nbsp;*&nbsp;SGI +&nbsp;0.115&nbsp;*&nbsp;DEPI -&nbsp;0.172&nbsp;*&nbsp;SGAI +&nbsp;4.679&nbsp;*&nbsp;TATA -&nbsp;0.327&nbsp;*&nbsp;LVGI =&nbsp;<b>' + scoreTXT + '</b></td></tr></table>');
    document.getElementById("MScore").innerHTML = commentTXT.join("") + "</table>";
    document.getElementById("MScoreCommentTable").style.lineHeight = 7.5/3;      
    //document.getElementById("MScoreMRQ").innerHTML = '<table align="center"><tr><td>t: '+ formatDateNoBreak(listFA3[0].date) +"</td><td>t-1: " + formatDateNoBreak(listFA3[1].date) + "</td></tr></table>";   
    if (listFA3.length > 1)
        mscoreMRQ = 't:<span style="color:#ffffff">.</span>'+ formatDateNoBreak(listFA3[0].date) +'<span style="color:#ffffff">.........</span>t-1:<span style="color:#ffffff">.</span>' + formatDateNoBreak(listFA3[1].date);
    
    return score;
}
function zscoreTXT(shareOutstanding, listFA3, listFA4, listFA5)
{
    var score = null;
    var x1 = 0, x2 = 0, x3 = 0, x4 = 0, x5 = 0;
    var commentTXT = ['<table id="ZScoreCommentTable" style="width:100%; line-height: 1">'];
    var commentHTML = '<tr><td style="font-size:13px">{0}</td></tr>';
    var invalid = 0;
    
    if (listFA5.length >= 2 && listFA4.length >= 2 && listFA3.length >= 2)
    {
        x1 = rounding((listFA3[0].totalCurrentAssets - listFA3[0].totalCurrentLiabilities)/listFA3[0].totalAssets, 10000);
        if (isNaN(x1) || !isFinite(x1))
        {
            x1 = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('X<sub>1</sub> = Working Capital / Total Assets =&nbsp;' + x1));

        x2 = rounding(listFA3[0].retainedEarnings/listFA3[0].totalAssets, 10000);
        if (isNaN(x2) || !isFinite(x2))
        {
            x2 = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('X<sub>2</sub> = Retained Earnings	/ Total Assets =&nbsp;' + x2));
        
        var ebitTTM = listFA5[0].ebit;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            ebitTTM += listFA5[i].ebit;
        }
        x3 = rounding(ebitTTM/listFA3[0].totalAssets, 10000);
        if (isNaN(x3) || !isFinite(x3))
        {
            x3 = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('X<sub>3</sub> = EBIT / Total Assets =&nbsp;' + x3));
                
        x4 = rounding(marketcap / 1000000 / listFA3[0].totalLiab, 10000);
        if (isNaN(x4) || !isFinite(x4))
        {
            x4 = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('X<sub>4</sub> = Market Cap / Total Liabilities =&nbsp;' + x4));
                        
        var totalRevenueTTM = listFA5[0].totalRevenue;
        for (var i = 1; i < listFA5.length - 1; i++)
        {
            totalRevenueTTM += listFA5[i].totalRevenue;
        }
        x5 = rounding(totalRevenueTTM / listFA3[0].totalAssets, 10000);
        if (isNaN(x5) || !isFinite(x5))
        {
            x5 = 0;
            invalid++;
        }
        commentTXT.push(commentHTML.format('X<sub>5</sub> = Revenue / Total Assets =&nbsp;' + x5));

        score = rounding(1.2 * x1 + 1.4 * x2 + 3.3 * x3 + 0.6 * x4 + x5, 100);
    }
    if (isNaN(score) || !isFinite(score))
        score = null;
    var scoreTXT = score != null ? score > 2.99? ('<span style="color:#3FB641">' + score + ', in&nbsp;Safe&nbsp;Zone&nbsp;<font size="5"><i class="fas fa-smile-beam" style="color:#80bf0c"></i></font></span>') : score >= 1.81? ('<span style="color:darkgrey">' + score + ', in&nbsp;Grey&nbsp;Zone&nbsp;<font size="5"><i class="fas fa-meh" style="color:darkgrey"></i></font></span>') : ('<span style="color:red">' + score + ', in&nbsp;Distress&nbsp;Zone&nbsp;<font size="5"><i class="fas fa-frown-open" style="color:#ff7070"></i></font></span>') : "Not&nbsp;Available";
    if (invalid == 5)
    {
        score = null;
        scoreTXT = "Not&nbsp;Available";
        commentTXT = ['<table id="ZScoreCommentTable" style="width:100%; line-height: 1">'];
    }
    document.getElementById("ZScoreTitle").innerHTML = commentHTML.format('<table class="scoretitle"><tr><td valign="top">Z</td><td valign="top">=&nbsp;1.2&nbsp;*&nbsp;X<sub>1</sub> +&nbsp;1.4&nbsp;*&nbsp;X<sub>2</sub> +&nbsp;3.3&nbsp;*&nbsp;X<sub>3</sub> +&nbsp;0.6&nbsp;*&nbsp;X<sub>4</sub> +&nbsp;X<sub>5</sub> =&nbsp;<b>' + scoreTXT + '</b></td></tr></table>');
    document.getElementById("ZScore").innerHTML = commentTXT.join("") + "</table>";
    document.getElementById("ZScoreCommentTable").style.lineHeight = 3.5;      
    
    return score;
}
function rsiTXT(rsi, rsiMA, isDaily)
{
    var tmpcomment = "";
    var tmp = 0;
	var dailyweekly = "Daily";
	var dayweek = "trading day";
	
	if (!isDaily)
	{
		dailyweekly = "Weekly";
		dayweek = "week";
	}
	
    if (rsi != null && rsi.length > 0)
    {
        if (rsi.length > 1 && rsi[rsi.length - 1][1] > 70 && rsi[rsi.length - 2][1] < 70)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed above 70";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] > 50 && rsi[rsi.length - 2][1] < 50)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 50";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] > 30 && rsi[rsi.length - 2][1] < 30)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 30";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] < 30 && rsi[rsi.length - 2][1] > 30)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed below 30";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] < 50 && rsi[rsi.length - 2][1] > 50)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 50";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] < 70 && rsi[rsi.length - 2][1] > 70)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 70";
        }
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] > 70 && rsi[rsi.length - 2][1] > 70 && rsi[rsi.length - 3][1] < 70)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed above 70 one " + dayweek + " ago";
        }
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] > 50 && rsi[rsi.length - 2][1] > 50 && rsi[rsi.length - 3][1] < 50)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 50 one " + dayweek + " ago";
        }
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] > 30 && rsi[rsi.length - 2][1] > 30 && rsi[rsi.length - 3][1] < 30)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 30 one " + dayweek + " ago";
        }
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] < 30 && rsi[rsi.length - 2][1] < 30 && rsi[rsi.length - 3][1] > 30)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed below 30 one " + dayweek + " ago";
        }     
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] < 50 && rsi[rsi.length - 2][1] < 50 && rsi[rsi.length - 3][1] > 50)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 50 one " + dayweek + " ago";
        }
        else if (rsi.length > 2 && rsi[rsi.length - 1][1] < 70 && rsi[rsi.length - 2][1] < 70 && rsi[rsi.length - 3][1] > 70)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 70 one " + dayweek + " ago";
        }
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] > 70 && rsi[rsi.length - 3][1] > 70 && rsi[rsi.length - 4][1] < 70)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed above 70 two " + dayweek + "s ago";
        }
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] > 50 && rsi[rsi.length - 3][1] > 50 && rsi[rsi.length - 4][1] < 50)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 50 two " + dayweek + "s ago";
        }
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] > 30 && rsi[rsi.length - 3][1] > 30 && rsi[rsi.length - 4][1] < 30)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI crossed above 30 two " + dayweek + "s ago";
        }
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] < 30 && rsi[rsi.length - 3][1] < 30 && rsi[rsi.length - 4][1] > 30)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI crossed below 30 two " + dayweek + "s ago";
        }    
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] < 50 && rsi[rsi.length - 3][1] < 50 && rsi[rsi.length - 4][1] > 50)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 50 two " + dayweek + "s ago";
        }
        else if (isDaily && rsi.length > 3 && rsi[rsi.length - 1][1] < 70 && rsi[rsi.length - 3][1] < 70 && rsi[rsi.length - 4][1] > 70)
        {
            tmp += 6;
            tmpcomment += dailyweekly + " RSI crossed below 70 two " + dayweek + "s ago";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] > 70)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI > 70";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] > 50)
        {
            tmp++;
            tmpcomment += dailyweekly + " RSI > 50";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] > 50 && rsi[rsi.length - 1][1] < rsiMA[rsiMA.length - 1][1])
        {
            //tmp++;
            tmpcomment += dailyweekly + " RSI > 50, but is trending down";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] < 30)
        {
            //tmp += 3;
            tmpcomment += dailyweekly + " RSI < 30";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] <= 50)
        {
            tmp += 6
            tmpcomment += dailyweekly + " RSI < 50";
        }
        else if (rsi.length > 1 && rsi[rsi.length - 1][1] <= 50 && rsi[rsi.length - 1][1] > rsiMA[rsiMA.length - 1][1])
        {
            //tmp += 6
            tmpcomment += dailyweekly + " RSI < 50, but is trending up";
        }        
    }   
    
    return [tmp, tmpcomment];
}

function macdZeroTXT(macd, isAboveZero, isAnd, isDaily)
{
    if (macd === null)
        return null;
        
    var andbut = " but";
    if (isAnd)
        andbut = " and";
   
    var dayweek = "trading day";
    if (!isDaily)
        dayweek = "week";
        
    if (isAboveZero)
    {
        if (macd.length > 1 && macd[macd.length - 2][1] <= 0)
            return andbut + " MACD crossed above 0";
        else if (macd.length > 2 && macd[macd.length - 2][1] > 0 && macd[macd.length - 3][1] <= 0)
            return andbut + " MACD crossed above 0 one " + dayweek + " ago";
        else if (macd.length > 3 && macd[macd.length - 3][1] > 0 && macd[macd.length - 4][1] <= 0)
            return andbut + " MACD crossed above 0 two " + dayweek + "s ago";
        else if (macd.length > 4 && macd[macd.length - 4][1] > 0 && macd[macd.length - 5][1] <= 0)
            return andbut + " MACD crossed above 0 three " + dayweek + "s ago";                             
        else                    
            return andbut + " MACD > 0";
    }
    else
    {
        if (macd.length > 1 && macd[macd.length - 2][1] > 0)
            return andbut + " MACD crossed below 0";
        else if (macd.length > 2 && macd[macd.length - 2][1] <= 0 && macd[macd.length - 3][1] > 0)
            return andbut + " MACD crossed below 0 one " + dayweek + " ago";
        else if (macd.length > 3 && macd[macd.length - 3][1] <= 0 && macd[macd.length - 4][1] > 0)
            return andbut + " MACD crossed below 0 two " + dayweek + "s ago";
        else if (macd.length > 4 && macd[macd.length - 4][1] <= 0 && macd[macd.length - 5][1] > 0)
            return andbut + " MACD crossed below 0 three " + dayweek + "s ago";                        
        else                      
            return andbut + " MACD < 0";
    }
}
var valuationdataPB;
var valuationdata;

jQuery(window).load(function(){
    jQuery("html,body").animate({scrollTop: 0}, 1000);
});

$(document).ready(function(){ /* tooltips for sector & industry*/
    $('[data-toggle="tooltip"]').tooltip({
  position: {
    my: 'center bottom',
    at: 'center top',
    using: function(position, feedback) {
      //console.log(feedback);
      $(this).css(position);
      $(this).addClass(feedback.vertical);
    }
  }
});
});

var valTable, valTablePB;
/*function valuationChart(chartcontainer, type)
{
    var resultArray = [];
    if (valuationdata === undefined || valuationdata.length === 0)
    {
        $.ajax({
            url : "/db_valuationbands_get.php?data=" + stockcode,
            success : function(result){
                var valTable = result.split("\n");
                valTable.reverse();
                if (valTable[0] == "")
                    valTable.splice(0, 1);
                valuationdata = valTable;
                resultArray = valuationBands(stockcode, valTable, pricedata, type, 'chartcontainerA1', 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11');
                document.getElementById("PEComment").innerHTML = resultArray[0];
            }
        });
    }
    else
    {
        resultArray = valuationBands(stockcode, valuationdata, pricedata, type, 'chartcontainerA1', 'M 0 10 L 10 0 M -1 1 L 1.1 -1 M 9 11 L 11 9', 'M 0 0 L 10 10 M 9 -1 L 11 1 M -1 9 L 1.1 11');
        document.getElementById("PEComment").innerHTML = resultArray[0];
    }
        
    tab1loaded = true;
    
    if (resultArray.length > 0)
        return resultArray[1];
    else
        return 0;

}*/

$(document).ready(function() {
    if (!window.mobileAndTabletcheck())
        document.getElementById("autocomplete").focus();

    $('.copyMe').on('change', function() {
        $(".copyMe").val($(this).val());
    });
  
    //When page loads...
  //  $(".tab-pane").hide(); //Hide all content
  //  $("ul.nav-tabs li:first").addClass("active").show(); //Activate first tab
  //  $(".tab-pane:first").show(); //Show first tab content
    //On Click Event
    $("a.mynav-link").click(function() {
        /*if (!(this.hash == "#tab-1" && tab1loaded) &&
            !(this.hash == "#tab-2" && tab2loaded) &&
            !(this.hash == "#tab-3" && tab3loaded) &&
            !(this.hash == "#tab-4" && tab4loaded) &&
            !(this.hash == "#tab-5" && tab5loaded))
        {
            window.location.href = this.hash;
            //jQuery("html,body").animate({scrollTop: 0}, 1000);
        }*/
        
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
    });

});

function priceDeviationChartDraw(MA_amount, High_amount)
{
    var rangeSelectorCurrent = priceDeviationChart.rangeSelector.selected;
    var startx = null;
    var endx = null;
    startx = priceDeviationChart.series[0].processedXData[0];
    endx = priceDeviationChart.series[0].processedXData[priceDeviationChart.series[0].processedXData.length - 1];

    document.getElementById('pricedeviationcontainer').innerHTML = '';
    priceDeviationChart = null;
    
    var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
    var rangeselector = 1;
    var creditYoffset = 26;
    if (browserwidth > 576)
    {
        rangeselector = 4;
        creditYoffset = 3;
    }
    
    priceDeviationChart = priceDeviation('pricedeviationcontainer', dailydata, stockcode, MA_amount, High_amount, stockcode + " Deviation % from MA" + MA_amount + " & " + High_amount + "-day High", stockname_en_tc.replace(dot, " ● ").substring(0, 45), "Stock Price", stockname_en_tc.replace(dot, " ● ").substring(0, 45), "day", rangeSelectorCurrent, -578 + creditYoffset, startx, endx);
}

$("#ma_actual").on("input", function() {
    if ($("#ma_actual").val().length === 0) {
        $('#otherMAradio').prop('checked', false);
        $('#ma50radio').prop('checked', true);
    } else {
        $('#otherMAradio').prop('checked', true);
        //document.getElementById("otherMAradio").value = document.getElementById("otherMAradio").textContent;
    }
    
    var MA_amount = $('input[name="ma_amount"]:checked').val();
    var High_amount = $('input[name="high_amount"]:checked').val();
    
    if (MA_amount == 'other')
        MA_amount = $('input[name="ma_actual"]').val();
        
    if (High_amount == 'other')
        High_amount = $('input[name="high_actual"]').val();
        
    if (parseInt(MA_amount) > 1 && parseInt(MA_amount) <= 2000 && parseInt(High_amount) > 1 && parseInt(High_amount) <= 2000)
        priceDeviationChartDraw(MA_amount, High_amount);
});

$("#high_actual").on("input", function() {
    if ($("#high_actual").val().length === 0) {
        $('#otherHighradio').prop('checked', false);
        $('#high50radio').prop('checked', true);
    } else {
        $('#otherHighradio').prop('checked', true);
        //document.getElementById("otherHighradio").value = document.getElementById("otherHighradio").textContent;
    }

    var MA_amount = $('input[name="ma_amount"]:checked').val();
    var High_amount = $('input[name="high_amount"]:checked').val();
    
    if (MA_amount == 'other')
        MA_amount = $('input[name="ma_actual"]').val();
        
    if (High_amount == 'other')
        High_amount = $('input[name="high_actual"]').val();
        
    if (parseInt(MA_amount) > 1 && parseInt(MA_amount) <= 2000 && parseInt(High_amount) > 1 && parseInt(High_amount) <= 2000)
        priceDeviationChartDraw(MA_amount, High_amount);
});


document.addEventListener('input',(e)=>{
    if(e.target.getAttribute('name')=="ma_amount" || e.target.getAttribute('name')=="high_amount")
    {
        var MA_amount = $('input[name="ma_amount"]:checked').val();
        var High_amount = $('input[name="high_amount"]:checked').val();
        
        if (MA_amount == 'other')
            MA_amount = $('input[name="ma_actual"]').val();
            
        if (High_amount == 'other')
            High_amount = $('input[name="high_actual"]').val();
            
        if (parseInt(MA_amount) > 1 && parseInt(MA_amount) <= 2000 && parseInt(High_amount) > 1 && parseInt(High_amount) <= 2000)
            priceDeviationChartDraw(MA_amount, High_amount);
    }
})

function scroll2peers()
{
    document.getElementById("PeerPerformance").style.display  = "";
    
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#PeerPerformance").offset().top - 85
    }, 1000);
}
//$("#high_amount").on("click change", function() {
//    priceDeviationChartDraw();
//});
function openTOOL()
{
    var codeStr = stockcode + ',' + encodeURIComponent(peersCode);
    codeStr = codeStr.trim();
    if (codeStr.startsWith(','))
        codeStr = codeStr.substring(1);
    if (codeStr.endsWith(','))
        codeStr = codeStr.substring(0, codeStr.length - 1);
        
    location.href = "tool?code=" + codeStr.trim();
}

function updateColumns() {
    const width = window.innerWidth;

    if (width < 768 && featuredGridCount > 0) {
        document.getElementById("blogGridItem2").style.display = "none";
        document.getElementById("blogGridItem3").style.display = "none";
        document.getElementById("blogGridItem4").style.display = "none";
        document.getElementById("blogGridItem5").style.display = "none";
        
        //document.getElementById("blogGrid").style.columnCount = 1;
        document.getElementById("featuredposttitle1").style.marginTop = '10px';
        document.getElementById("featuredposttitle1").style.textAlign = 'center';
    }
    else
    {
        for(var i = 0; i < featuredGridCount; i++)
        {
            document.getElementById("blogGridItem" + (i + 1)).style.display = "";
        }
        
        document.getElementById("featuredposttitle1").style.marginTop = '0px';
        document.getElementById("featuredposttitle1").style.textAlign = 'left';
        
        //document.getElementById("blogGrid").style.columnCount = featuredGridCount < minGridNum ? minGridNum : featuredGridCount;
    }
}

window.addEventListener("resize", updateColumns);
updateColumns(); // Run initially

function add_Days(date, days) {
    const newDate = new Date(date);
    newDate.setDate(date.getDate() + days);
    return newDate;
}

function dayofyear (date)
{
    var start = new Date(date.getFullYear(), 0, 0);
    var diff = date - start;
    var oneDay = 1000 * 60 * 60 * 24;
    return day = Math.floor(diff / oneDay);    
}

function YearlyTrendToggle(isPercent)
{
    var browserwidth = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth;
    yearlyTrendChart('chartcontainerYearlyTrend', dictYearlyTrend, seriesname, stockcode, browserwidth, true, 'Stock Price', stockname_en_tc, isPercent);
}
</script>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/theme.js"></script>-->
    <script src="assets/js/ValuationBands.js"></script>
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
</body>

</html>
