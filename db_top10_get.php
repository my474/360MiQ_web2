<?php 
    if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || 
        (empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")) {
        if (realpath($_SERVER["SCRIPT_FILENAME"]) == __FILE__) { // direct access denied
            header("Location: /");
            exit;
        }
    }
    
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($referer, 'https://360miq.com') === 0 ||
        strpos($referer, 'https://www.360miq.com') === 0 ||
        strpos($referer, 'https://aamiq.com') === 0 ||
        strpos($referer, 'https://www.aamiq.com') === 0 ||        
        ((strpos($browser, 'PaleMoon') !== false || strpos($browser, 'Firefox') !== false) && $referer == '')) // Palemoon, WaterFox, SeaMonkey has no referer
    {
        define('A',true);
    	include '../../php_script/mysql_vars_stock.php';
 	
    	$data= isset($_GET["data"]) ? strtoupper(urldecode($_GET["data"])) : '';

        $codes = explode(",", $data);
        
		$MS = [];
		$ALT = [];
		$EOD = [];
		
		for ($i = 0; $i < sizeof($codes); $i++)
		{
			if (endsWith($codes[$i], ".HK"))
			{
				array_push($ALT, $codes[$i]);
			}
			else if (endsWith($codes[$i], ".L") || endsWith($codes[$i], ".T") || endsWith($codes[$i], ".SZ") || endsWith($codes[$i], ".SS") || endsWith($codes[$i], ".SH"))
			{
				array_push($EOD, $codes[$i]);
			}
			else
			{
				array_push($MS, $codes[$i]);
			}
		}
		
        $size = 10 - sizeof($ALT);
        for ($i = 0; $i < $size; $i++)
        {
            array_push($ALT, "");
        }
		
		$size = 10 - sizeof($EOD);
        for ($i = 0; $i < $size; $i++)
        {
            array_push($EOD, "");
        }
		
		$size = 10 - sizeof($MS);
        for ($i = 0; $i < $size; $i++)
        {
            array_push($MS, "");
        }

	    //if ($data == "510500.SH") // EODhistorical.com has this wrong in 2015, use .SS instead as also for market breadth
	        //$data = "510500.SS";
	        
	    $isIEX = isset($_GET["isIEX"]) && $_GET["isIEX"] === "1" ? "1" : "0";

        $price_history = "price_history";
        /*if (endsWith($data, ".HK"))
            $price_history = "price_history_alt";
        else if (endsWith($data, ".L") || endsWith($data, ".T") || endsWith($data, ".SZ") || endsWith($data, ".SS") || endsWith($data, ".SH"))
            $price_history = "price_history";*/
            
        if (endsWith($data, ".HK") || endsWith($data, ".SZ") || endsWith($data, ".SS") || endsWith($data, ".SH"))
            date_default_timezone_set('Asia/Hong_Kong');
        else if (endsWith($data, ".TO"))
            date_default_timezone_set('America/Toronto');
        else if (endsWith($data, ".AX"))
            date_default_timezone_set('Australia/Sydney');
        else if (endsWith($data, ".L"))
            date_default_timezone_set('Europe/London');
        else if (endsWith($data, ".N"))
            date_default_timezone_set('Asia/Kolkata');
        else if (endsWith($data, ".T"))
            date_default_timezone_set('Asia/Tokyo');
        else
            date_default_timezone_set('America/New_York');

        //$sqlQuery = "SELECT * FROM `price_history` where code =  '$data' and tradedate > DATE_SUB(NOW(), INTERVAL '3.1' YEAR_MONTH) ORDER BY `tradedate` ASC;";
        //$sqlQuery = "SELECT tradedate,open,high,low,close,volume from price_current where price_current.code = '$data' and tradedate >= IFNULL((SELECT DATE(max(tradedate)) from price_current where code = '$data'), 0) union SELECT tradedate,open,high,low,close,volume from price_history where close > 0 and price_history.code = '$data' and price_history.tradedate <> IFNULL((SELECT DATE(max(tradedate)) from price_current where code = '$data'), 0) and price_history.tradedate > DATE_SUB(NOW(), INTERVAL '10.1' YEAR_MONTH) ORDER BY `tradedate` ASC;";
        //if ($isIEX == "1")
        //    $sqlQuery = "SELECT tradedate,open,high,low,close,volume from price_current where price_current.code = ? and price_current.tradedate is not null and price_current.volume > 0 union SELECT tradedate,open,high,low,close,volume from $price_history h where close > 0 and h.code = ? and h.tradedate < IFNULL((SELECT DATE(tradedate) from price_current where code = ? and volume > 0), '9999-12-31') and h.tradedate > DATE_SUB(NOW(), INTERVAL '10.1' YEAR_MONTH) ORDER BY `tradedate` ASC;";
        //else
        //    $sqlQuery = "SELECT tradedate,open,high,low,close,volume from price_current where price_current.code = ? and price_current.tradedate is not null and price_current.volume > 0 union SELECT tradedate,open,high,low,close,volume from $price_history h where close > 0 and h.code = ? and h.tradedate <> IFNULL((SELECT DATE(tradedate) from price_current where code = ? and volume > 0), 0) and h.tradedate > DATE_SUB(NOW(), INTERVAL '10.1' YEAR_MONTH) ORDER BY `tradedate` ASC;";

        //if ($isIEX == "1")
        //    $sqlQuery = "(SELECT code,tradedate,close from price_history where volume > 0 and close > 0 and price_history.code in (?,?,?,?,?,?,?,?,?,?) and price_history.tradedate < IFNULL((SELECT MAX(DATE(tradedate)) from price_current where code in (?,?,?,?,?,?,?,?,?,?)), '9999-12-31') and price_history.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current where price_current.code in (?,?,?,?,?,?,?,?,?,?) and price_current.tradedate is not null union(SELECT code,tradedate,close from price_history_MS where volume > 0 and close > 0 and price_history_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_history_MS.tradedate < IFNULL((SELECT MAX(DATE(tradedate)) from price_current_MS where code in (?,?,?,?,?,?,?,?,?,?)), '9999-12-31') and price_history_MS.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current_MS where price_current_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_current_MS.tradedate is not null union (SELECT code,tradedate,close from price_history_alt where volume > 0 and close > 0 and price_history_alt.code in (?,?,?,?,?,?,?,?,?,?) and price_history_alt.tradedate < IFNULL((SELECT MAX(DATE(tradedate)) from price_current_MS where code in (?,?,?,?,?,?,?,?,?,?)), '9999-12-31') and price_history_alt.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current_MS where price_current_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_current_MS.tradedate is not null;";
        //else
        //    $sqlQuery = "(SELECT code,tradedate,close from price_history where volume > 0 and close > 0 and price_history.code in (?,?,?,?,?,?,?,?,?,?) and price_history.tradedate <> IFNULL((SELECT MAX(DATE(tradedate)) from price_current where code in (?,?,?,?,?,?,?,?,?,?)), 0) and price_history.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current where price_current.code in (?,?,?,?,?,?,?,?,?,?) and price_current.tradedate is not null union (SELECT code,tradedate,close from price_history_MS where volume > 0 and close > 0 and price_history_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_history_MS.tradedate <> IFNULL((SELECT MAX(DATE(tradedate)) from price_current_MS where code in (?,?,?,?,?,?,?,?,?,?)), 0) and price_history_MS.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current_MS where price_current_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_current_MS.tradedate is not null union (SELECT code,tradedate,close from price_history_alt where volume > 0 and close > 0 and price_history_alt.code in (?,?,?,?,?,?,?,?,?,?) and price_history_alt.tradedate <> IFNULL((SELECT MAX(DATE(tradedate)) from price_current_MS where code in (?,?,?,?,?,?,?,?,?,?)), 0) and price_history_alt.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current_MS where price_current_MS.code in (?,?,?,?,?,?,?,?,?,?) and price_current_MS.tradedate is not null;";

        if ($isIEX == "1")
            $sqlQuery = "(SELECT code,tradedate,close from $price_history h where volume > 0 and close > 0 and h.code in (?,?,?,?,?,?,?,?,?,?) and h.tradedate < IFNULL((SELECT MAX(DATE(tradedate)) from price_current where code in (?,?,?,?,?,?,?,?,?,?)), '9999-12-31') and h.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current where price_current.code in (?,?,?,?,?,?,?,?,?,?) and price_current.tradedate is not null;";
        else
            $sqlQuery = "(SELECT code,tradedate,close from $price_history h where volume > 0 and close > 0 and h.code in (?,?,?,?,?,?,?,?,?,?) and h.tradedate <> IFNULL((SELECT MAX(DATE(tradedate)) from price_current where code in (?,?,?,?,?,?,?,?,?,?)), 0) and h.tradedate > DATE_SUB(NOW(), INTERVAL '8.1' YEAR_MONTH) ORDER BY `tradedate` ASC) union SELECT code,tradedate,close from price_current where price_current.code in (?,?,?,?,?,?,?,?,?,?) and price_current.tradedate is not null;";
            
        $stmt = $connection->prepare($sqlQuery);
            
        //$stmt->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss", $EOD[0], $EOD[1], $EOD[2], $EOD[3], $EOD[4], $EOD[5], $EOD[6], $EOD[7], $EOD[8], $EOD[9], $EOD[0], $EOD[1], $EOD[2], $EOD[3], $EOD[4], $EOD[5], $EOD[6], $EOD[7], $EOD[8], $EOD[9], $EOD[0], $EOD[1], $EOD[2], $EOD[3], $EOD[4], $EOD[5], $EOD[6], $EOD[7], $EOD[8], $EOD[9], $MS[0], $MS[1], $MS[2], $MS[3], $MS[4], $MS[5], $MS[6], $MS[7], $MS[8], $MS[9], $MS[0], $MS[1], $MS[2], $MS[3], $MS[4], $MS[5], $MS[6], $MS[7], $MS[8], $MS[9], $MS[0], $MS[1], $MS[2], $MS[3], $MS[4], $MS[5], $MS[6], $MS[7], $MS[8], $MS[9], $ALT[0], $ALT[1], $ALT[2], $ALT[3], $ALT[4], $ALT[5], $ALT[6], $ALT[7], $ALT[8], $ALT[9], $ALT[0], $ALT[1], $ALT[2], $ALT[3], $ALT[4], $ALT[5], $ALT[6], $ALT[7], $ALT[8], $ALT[9], $ALT[0], $ALT[1], $ALT[2], $ALT[3], $ALT[4], $ALT[5], $ALT[6], $ALT[7], $ALT[8], $ALT[9]);
		
		$stmt->bind_param("ssssssssssssssssssssssssssssss", $codes[0], $codes[1], $codes[2], $codes[3], $codes[4], $codes[5], $codes[6], $codes[7], $codes[8], $codes[9], $codes[0], $codes[1], $codes[2], $codes[3], $codes[4], $codes[5], $codes[6], $codes[7], $codes[8], $codes[9], $codes[0], $codes[1], $codes[2], $codes[3], $codes[4], $codes[5], $codes[6], $codes[7], $codes[8], $codes[9]);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
    	//mysqli_select_db($db, $connection);
    	//$result = mysqli_query($connection, $sqlQuery);	
    
        $tradedate = "";
    
        $cumValue = 0;
        //$itemRow = mysqli_fetch_array($result, MYSQLI_ASSOC);
        
        $dayarray = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V");
        $previousName = "";
        $decimalplace = 2; // HK should have 3 decimals for < $0.5, but worldtradingdata only has 2
        if (endsWith($data, ".SZ") || endsWith($data, ".SS") || endsWith($data, ".SH"))
            $decimalplace = 3;
                
        $count = 0;
        //$numResults = mysqli_num_rows($result);
        $numResults = $result->num_rows;
    	//while($itemRow = mysqli_fetch_array($result, MYSQLI_ASSOC))
    	$openLast = -1;
    	$highLast = -1;
    	$lowLast = -1;
    	$closeLast = -1;
    	$previousVolume = -1;
    	while($itemRow = $result->fetch_assoc())
    	{    	    
    	    $count++;
    	    
    	    $close = $itemRow['close']+0;

            if (endsWith($data, ".HK"))
            {
        	    if ($count < $numResults) // don't round the latest close, as IEX may have 3 decimals. The Latest close has no adj anyway
        	        $close = round($close, $close >= 0.5? 2 : 3);
            }
            else if (endsWith($data, ".AX")) // https://www.asx.com.au/services/trading-services/price.htm
            {
        	    if ($count < $numResults) // don't round the latest close, as IEX may have 3 decimals. The Latest close has no adj anyway
        	        $close = round($close, $close > 2? 2 : ($close < 0.01 ? 4 : 3));
            }
            else
            {
        	    if ($count < $numResults) // don't round the latest close, as IEX may have 3 decimals. The Latest close has no adj anyway
        	        $close = round($close, $decimalplace);
            }
            
            $name = $itemRow['code'];

            // fix 100x error in LSE
            if (endsWith($data, ".L") && $count > 1 && $name == $nameLast) // the very last rows are different stocks' current price
            {
                if ($close > 0 && $close * 70 < $closeLast)
                    $close = round($close * 100, $decimalplace);

                if ($closeLast > 0 && $close > $closeLast * 70)
                    $close = round($close / 100, $decimalplace);
            }
                
	        $date = str_replace(" 00:00:00", "", $itemRow['tradedate']);

            $td = datestrFormat($tradedate, $date, $dayarray);

    	    $line = ','. $close;
	        $y = date("Y", strtotime($itemRow['tradedate'])); 
	        $m = date("m", strtotime($itemRow['tradedate'])); 
	        $d = date("d", strtotime($itemRow['tradedate'])); 
	        
	        $closingHour = "+16 hours";
	        if (endsWith($data, ".L"))
                $closingHour = "+16 hours 30 minutes";
            else if (endsWith($data, ".T"))
                $closingHour = "+15 hours 15 minutes";
            else if (endsWith($data, ".SH") || endsWith($data, ".SZ"))
                $closingHour = "+15 hours 30 minutes";
    	    if ($line != "" && ($isIEX == "1" || date("Y-m-d H:i:s", strtotime($closingHour, strtotime($y."-".$m."-".$d))) < date("Y-m-d H:i:s", strtotime("-45 minutes")))) // delay 45 mins
    	    {
    	        if (strpos($date, ':') !== false) {
                    $date = date("Ymd", strtotime($date));
	                echo $name, ',', $date, $line, "\n";
                }
                else if ($name == $previousName)
                {
                    $name = "";
                    echo str_replace("-", "", $td), $line, "\n";
                }
                else
    	            echo $name, ',', str_replace("-", "", $td), $line, "\n";
    	    }
    	    
    	    if ($name != "")
		        $previousName = $name;
		        
    	    $tradedate = $date;  // store current row datetime
    	    $closeLast = $close;
            $nameLast = $name;
    	}
    	mysqli_close($connection);
    }
    else 
    {
        header("Location: /");
    }

function datestrFormat($tradedate, $date, $dayarray)
{
    if ($tradedate == "")
    {
        $td = substr($date, 2);  // strip yy
    }
    else
    {
	    if (substr($date , 0, 7) == substr($tradedate, 0, 7))
	    {
	        $td = substr($date, 7);  // strip yyyyMM
	    }
        else if (substr($date, 0, 4) == substr($tradedate, 0, 4))
        {
            $td = substr($date, 4);  // strip yyyy
        }
        else
        {
            $td = substr($date, 2);  // strip yy
        }
    }        
    
    $day = intval(substr($td, -2)); 
    if (isset($dayarray[$day])) // added 'if' to see if 'Undefined array key' warning remains. If fixed, check why it happened and if this fix causes another problem with date
        $td = substr($td, 0, strlen($td) - 2) . $dayarray[$day];
    
    return $td;
}

function endsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

?>
