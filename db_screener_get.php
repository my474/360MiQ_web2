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
    	//include connection file 
    	include_once("../../php_script/mysql_vars_stock.php");
    	 
    	// initilize all variable
    	$params = $columns = $totalRecords = $data = array();
    
    	$params = $_REQUEST;
    
    	//define index of column
    	$columns = array( 
    		0 =>'code',
    		1 =>'name_en', 
    		2 => 'close',
    		3 => 'mcap',
    		4 => 'polarscoreTA',
    		5 => 'f.polarscore',
    		6 => 'polarscoreFA',
    		7 => 'g.value',
    		8 => 'f1.stdev', 
    		9 => 'f2.stdev',
    		10 => 'f',
    		11 => 'z',
    		12 => 'm',
    		13 => 'rsi14d', 
    		14 => 'rsi14w', 
    		/*13 => 'ma10', 
    		14 => 'ma20',
    		15 => 'ma50', 
    		16 => 'ma100', 
    		17 => 'ma200', 
    		18 => 'ma250', 
    		19 => 'rsi14d', 
    		20 => 'rsi14w', 
    		21 => 'macdd', 
    		22 => 'macdw',*/
    	);
    
    	$where = $sqlTot = $sqlRec = "";
    

        $alt = "";
    	if( !empty($params['exchange']) ) {
            if ($params['exchange'] == "HKEX")
            {
                $benchmark = "2800.HK";
                //$alt = "_alt";
            }
            else if ($params['exchange'] == "SZSE")
            {
                $benchmark = "159903.SZ";
            }
            else if ($params['exchange'] == "SHSE")
            {
                $params['exchange'] = "SHG";
                $benchmark = "510500.SH";
            }
            else if ($params['exchange'] == "NYSE" || $params['exchange'] == "NYSEARCA" || $params['exchange'] == "NYSE + NASDAQ")
            {
                $benchmark = "SPY";
            }
            else if ($params['exchange'] == "NASDAQ")
            {
                $benchmark = "QQQ";
            }
            else if ($params['exchange'] == "LSE")
            {
                //$benchmark = "ISF.L";
                $benchmark = "AZN.L"; // ISF.L trades on UK holidays
            }
            else if ($params['exchange'] == "NSE")
            {
                $benchmark = "NIFTYIETF.N";
            }
            else if ($params['exchange'] == "ASX")
            {
                $benchmark = "STW.AX";
            }
            else if ($params['exchange'] == "TSX")
            {
                $benchmark = "XIC.TO";
            }
            else if ($params['exchange'] == "TYO")
            {
                $benchmark = "1306.T";
            }
            else
                return;
        
        	$price_current = "price_current_screener";
        	// for US intraday
        	//if ($params['exchange'] == "NYSE" || $params['exchange'] == "NYSEARCA" || $params['exchange'] == "NYSE + NASDAQ" || $params['exchange'] == "NASDAQ")
        	//{
        	//    $price_current = "price_current";
        	//}
        	
    		$where .=" WHERE name_en is not null and a.close > 0 ";
    		$mcap_where = " WHERE close > 0 ";
    		if ($params['exchange'] == "NYSE + NASDAQ")
    		{
    		    $where .=" and (stock_exchange_short = 'NYSE' ";
    		    $where .=" or stock_exchange_short = 'NASDAQ') ";
                //$where .=" and a.tradedate >= (select DATE_FORMAT(tradedate,'%y-%m-%d') from $price_current where code = '".$benchmark."') ";

    		    $mcap_where .=" and (stock_exchange_short = 'NYSE' ";
    		    $mcap_where .=" or stock_exchange_short = 'NASDAQ') ";
    		    //$mcap_where .= " and tradedate >= (select DATE_FORMAT(tradedate,'%y-%m-%d') from $price_current where code = '".$benchmark."') and market_cap > 0 ";
    		}
    	    else
    	    {
    		    $where .=" and stock_exchange_short = '".$params['exchange']."' "; 
        		//$where .=" and a.tradedate >= (select DATE_FORMAT(tradedate,'%y-%m-%d') from $price_current where code = '".$benchmark."') ";
        		
        		$mcap_where .=" and stock_exchange_short = '".$params['exchange']."' "; 
        		//$mcap_where .= " and tradedate >= (select DATE_FORMAT(tradedate,'%y-%m-%d') from $price_current where code = '".$benchmark."') and market_cap > 0 ";
    	    }
		    $where .=" and a.tradedate >= (select DATE_FORMAT(MAX(tradedate), '%Y-%m-%d') from (select tradedate from $price_current where code = '".$benchmark."' union select max(tradedate) from price_history where code = '".$benchmark."') as date)";
		    $mcap_where .=" and tradedate >= (select DATE_FORMAT(MAX(tradedate), '%Y-%m-%d') from (select tradedate from $price_current where code = '".$benchmark."' union select max(tradedate) from price_history where code = '".$benchmark."') as date) and market_cap > 0 ";
    	}
    	else
    	    return;
    	
    	$leftjoin = "";
    	$rightjoin = "";
    	
    	if( !empty($params['sector']) ) {
    		if ($params['sector'] != "A")
    		{
    		    if($params['sector'] == "BM")
                	$where .=" and Sector = 'Basic Materials' ";
                else if($params['sector'] == "C")
                	$where .=" and Sector = 'Communication Services' ";
                else if($params['sector'] == "CC")
                	$where .=" and Sector = 'Consumer Cyclical' ";
                else if($params['sector'] == "CD")
                	$where .=" and Sector = 'Consumer Defensive' ";
                else if($params['sector'] == "E")
                	$where .=" and Sector = 'Energy' ";
                else if($params['sector'] == "F")
                	$where .=" and Sector = 'Financial Services' ";
                else if($params['sector'] == "H")
                	$where .=" and Sector = 'Healthcare' ";
                else if($params['sector'] == "I")
                	$where .=" and Sector = 'Industrials' ";
                else if($params['sector'] == "O")
                	$where .=" and Sector = 'Other' ";
                else if($params['sector'] == "RE")
                	$where .=" and Sector = 'Real Estate' ";
                else if($params['sector'] == "S")
                	$where .=" and Sector = 'Services' ";
                else if($params['sector'] == "T")
                	$where .=" and Sector = 'Technology' ";
                else if($params['sector'] == "U")
                	$where .=" and Sector = 'Utilities' ";
    		    //$where .=" and Sector = '".$params['sector']."' ";
    		}
    	}
    	
    	if( !empty($params['industry']) ) {
    		if ($params['industry'] != "All")
    		{
    		    $found = false;
    		    $indus = array("Other", "Banks-Regional", "Real Estate-Development", "Biotechnology", "Lodging", "Infrastructure Operations", "Conglomerates", "Engineering & Construction", "Specialty Chemicals", "Consumer Electronics", "Leisure", "Packaged Foods", "Electronic Components", "Computer Hardware", "Auto & Truck Dealerships", "Luxury Goods", "Utilities-Diversified", "Medical Distribution", "Auto Parts", "Info Technology Services", "Waste Management", "Utilities-Regulated Electric", "Internet Content & Info", "Metal Fabrication", "Utilities-Renewable", "Textile Manufacturing", "Farm Products", "Building Products & Equipment", "Real Estate Services", "Agricultural Inputs", "Other Industrial Metals & Mining", "Food Distribution", "Communication Equipment", "Drug Manufacturers-Specialty & Generic", "Marine Shipping", "Airports & Air Services", "Oil & Gas Refining & Marketing", "Integrated Freight & Logistics", "Medical Care Facilities", "Industrial Distribution", "Chemicals", "Entertainment", "Farm & Heavy Construction Machinery", "Capital Markets", "Utilities-Regulated Gas", "Specialty Industrial Machinery", "Electrical Equipment & Parts", "Building Materials", "Utilities-Regulated Water", "Drug Manufacturers-General", "Rental & Leasing Services", "Asset Management", "Department Stores", "Railroads", "Paper & Paper Products", "Banks-Diversified", "Healthcare Plans", "Household & Personal Products", "Education & Training Services", "Pollution & Treatment Controls", "Auto Manufacturers", "Thermal Coal", "Business Equipment & Supplies", "Beverages-Wineries & Distilleries", "Confectioners", "Solar", "Lumber & Wood Production", "Beverages-Brewers", "Utilities-Independent Power Producers", "Publishing", "Aluminum", "Financial Conglomerates", "Copper", "Real Estate-Diversified", "Health Info Services", "Coking Coal", "Packaging & Containers", "Software-Infrastructure", "Broadcasting", "Semiconductors", "Aerospace & Defense", "Electronics & Computer Distribution", "Steel", "Restaurants", "Discount Stores", "Travel Services", "Telecom Services", "Specialty Business Services", "Electronic Gaming & Multimedia", "Beverages-Non-Alcoholic", "Oil & Gas Equipment & Services", "Advertising Agencies", "Furnishings, Fixtures & Appliances", "Other Precious Metals & Mining", "Software-Application", "Apparel Manufacturing", "Specialty Retail", "Semiconductor Equipment & Materials", "Tools & Accessories", "Scientific & Technical Instruments", "Oil & Gas E&P", "Medical Instruments & Supplies", "Security & Protection Services", "Oil & Gas Integrated", "Oil & Gas Midstream", "Footwear & Accessories", "Grocery Stores", "Pharmaceutical Retailers", "Resorts & Casinos", "Silver", "Airlines", "Medical Devices", "Consulting Services", "Mortgage Finance", "Personal Services", "Internet Retail", "Apparel Retail", "Insurance-Property & Casualty", "Gold", "Credit Services", "Home Improvement Retail", "Financial Data & Stock Exchanges", "REIT-Diversified", "Recreational Vehicles", "Insurance-Life", "REIT-Retail", "Diagnostics & Research", "Gambling", "Residential Construction", "Uranium", "REIT-Hotel & Motel", "REIT-Office", "Insurance-Reinsurance", "Staffing & Employment Services", "Trucking", "Tobacco", "Insurance Brokers", "REIT-Mortgage", "Shell Companies", "REIT-Residential", "Insurance-Diversified", "Oil & Gas Independent", "Industrial Metals & Minerals", "Insurance-Specialty", "REIT-Healthcare Facilities", "REIT-Specialty", "REIT-Industrial", "Electronic Equipment", "Industrial Electrical Equipment", "Major Integrated Oil & Gas", "Health Care Plans", "Closed-End Fund - Debt", "Trucks & Other Vehicles", "Wireless Communications", "Diagnostic Substances");
    		    for($i = 0; $i < sizeof($indus); $i++)
    		        if ($params['industry'] == $indus[$i])
    		        {
    		            $found = true;
    		            break;
    		        }
		        
		        if (!$found)
		            return;
		            
    		    $where .=" and Industry = '".$params['industry']."' ";
    		}
    	}
    	
    	if( !empty($params['marketcap']) ) {
    		if ($params['marketcap'] != "A")
    		{
    		    if ($params['marketcap'] == "M")    
    		        $where .=" and a.close * shares > 300000000000 ";
    	        else if ($params['marketcap'] == "L")    
    		        $where .=" and a.close * shares > 10000000000 and a.close * shares <= 300000000000 ";
    	        else if ($params['marketcap'] == "Mid")    
    		        $where .=" and a.close * shares > 2000000000 and a.close * shares <= 10000000000 ";
    	        else if ($params['marketcap'] == "S")    
    		        $where .=" and a.close * shares > 300000000 and a.close * shares <= 2000000000 ";
    	        else if ($params['marketcap'] == "Mic")    
    		        $where .=" and a.close * shares > 50000000 and a.close * shares <= 300000000 ";
    	        else if ($params['marketcap'] == "N")    
    		        $where .=" and a.close * shares <= 50000000 ";
    	        else if ($params['marketcap'] == ">L")    
    		        $where .=" and a.close * shares > 10000000000 ";
    	        else if ($params['marketcap'] == ">Mid")    
    		        $where .=" and a.close * shares > 2000000000 ";
    	        else if ($params['marketcap'] == ">S")    
    		        $where .=" and a.close * shares > 300000000 ";
    	        else if ($params['marketcap'] == ">Mic")    
    		        $where .=" and a.close * shares > 50000000 ";
    	        else if ($params['marketcap'] == "<L")    
    		        $where .=" and a.close * shares <= 300000000000 ";
    	        else if ($params['marketcap'] == "<Mid")    
    		        $where .=" and a.close * shares <= 10000000000 ";
    	        else if ($params['marketcap'] == "<S")    
    		        $where .=" and a.close * shares <= 2000000000 ";
    	        else if ($params['marketcap'] == "<Mic")    
    		        $where .=" and a.close * shares <= 300000000 ";
		        else if ($params['marketcap'] == "T50")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 500) l on a.code = l.code ";
		        else if ($params['marketcap'] == "T20")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 200) l on a.code = l.code ";
		        else if ($params['marketcap'] == "T10")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 100) l on a.code = l.code ";
		        else if ($params['marketcap'] == "T5")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 50) l on a.code = l.code ";
		        else if ($params['marketcap'] == "T2")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 20) l on a.code = l.code ";
		        else if ($params['marketcap'] == "T1")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap desc limit 10) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B50")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 500) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B20")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 200) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B10")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 100) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B5")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 50) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B2")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 20) l on a.code = l.code ";
		        else if ($params['marketcap'] == "B1")
    		        $rightjoin = " right join (select code, market_cap from $price_current $mcap_where order by market_cap limit 10) l on a.code = l.code ";
    		}
    	}
    	
    	$where .= polar($params['polar_ta'], "polarscoreTA");
    	$where .= polar($params['polar_va'], "f.polarscore");
    	$where .= polar($params['polar_fa'], "polarscoreFA");
    	
    	if( !empty($params['polar_trendgauge']) ) {
    		if ($params['polar_trendgauge'] != "A")
    		{
    		    if ($params['polar_trendgauge'] == "OR")    
    		        $where .=" and g.value > 270 and g.value <= 315 ";
    	        else if ($params['polar_trendgauge'] == "RU")    
    		        $where .=" and g.value > 315 and g.value <= 360 ";
    	        else if ($params['polar_trendgauge'] == "USU")    
    		        $where .=" and g.value >= 0 and g.value <= 45 ";
    	        else if ($params['polar_trendgauge'] == "SUO")    
    		        $where .=" and g.value > 45 and g.value <= 90 ";
    	        else if ($params['polar_trendgauge'] == "OC")    
    		        $where .=" and g.value > 90 and g.value <= 135 ";
    	        else if ($params['polar_trendgauge'] == "CD")    
    		        $where .=" and g.value > 135 and g.value <= 180 ";
    	        else if ($params['polar_trendgauge'] == "DSD")    
    		        $where .=" and g.value > 180 and g.value <= 225 ";
    	        else if ($params['polar_trendgauge'] == "SDO")    
    		        $where .=" and g.value > 225 and g.value <= 270 ";
    	        else if ($params['polar_trendgauge'] == "OU")    
    		        $where .=" and g.value > 270 and g.value < 360 ";
    	        else if ($params['polar_trendgauge'] == "UO")    
    		        $where .=" and g.value >= 0 and g.value <= 90 ";
    	        else if ($params['polar_trendgauge'] == "OD")    
    		        $where .=" and g.value > 90 and g.value <= 180 ";
    	        else if ($params['polar_trendgauge'] == "DO")    
    		        $where .=" and g.value > 180 and g.value <= 270 ";
    		}
    	}
    
    	if( !empty($params['channel_pos']) ) {
    		if ($params['channel_pos'] != "A")
    		{
    		    if ($params['channel_pos'] == "boT")    
    		        $where .=" and (k.close > (k.midline + k.SE) and k.close_previous < (k.midline_previous + k.SE)) ";
    	        else if ($params['channel_pos'] == "bdB")    
    		        $where .=" and (k.close < (k.midline - k.SE) and k.close_previous > (k.midline_previous - k.SE)) ";
    	        else if ($params['channel_pos'] == "cuT")    
    		        $where .=" and (k.close < (k.midline + k.SE) and k.close_previous > (k.midline_previous + k.SE)) ";
    	        else if ($params['channel_pos'] == "coB")    
    		        $where .=" and (k.close > (k.midline - k.SE) and k.close_previous < (k.midline_previous - k.SE)) ";
    		        
		        else if ($params['channel_pos'] == "coM")    
    		        $where .=" and (k.close > k.midline and k.close_previous < k.midline_previous) ";
		        else if ($params['channel_pos'] == "cuM")    
    		        $where .=" and (k.close < k.midline and k.close_previous > k.midline_previous) ";
    		        
    		    /*else if ($params['channel_pos'] == "nT")    
    		        $where .=" and (k.close/(k.midline + k.SE) >= 0.985 and k.close/(k.midline + k.SE) <= 1.015) ";
    	        else if ($params['channel_pos'] == "nM")    
    		        $where .=" and (k.close/(k.midline) >= 0.985 and k.close/(k.midline) <= 1.015) ";
    	        else if ($params['channel_pos'] == "nB")    
    		        $where .=" and (k.close/(k.midline - k.SE) >= 0.985 and k.close/(k.midline - k.SE) <= 1.015) ";*/
    		    else if ($params['channel_pos'] == "nT")    
    		        $where .=" and (k.close > (k.midline + 0.8 * k.SE) and k.close < (k.midline + 1.2 * k.SE)) ";
    	        else if ($params['channel_pos'] == "nM")    
    		        $where .=" and (k.close > (k.midline - 0.2 * k.SE) and k.close < (k.midline + 0.2 * k.SE)) ";
    	        else if ($params['channel_pos'] == "nB")    
    		        $where .=" and (k.close > (k.midline - 1.2 * k.SE) and k.close < (k.midline - 0.8 * k.SE)) ";
    		        
    	        else if ($params['channel_pos'] == "aT")    
    		        $where .=" and k.close > (k.midline + k.SE) ";
    	        else if ($params['channel_pos'] == "bB")    
    		        $where .=" and k.close < (k.midline - k.SE) ";
    	        else if ($params['channel_pos'] == "UH")    
    		        $where .=" and (k.close > k.midline and k.close <= (k.midline + k.SE)) ";
    	        else if ($params['channel_pos'] == "LH")    
    		        $where .=" and (k.close < k.midline and k.close >= (k.midline - k.SE)) ";
    		}
    	}
    	
    	$slope_threshold = 0.00065;
    	if( !empty($params['channel_trend']) ) {
    		if ($params['channel_trend'] != "A")
    		{
    		    if ($params['channel_trend'] == "U")    
    		        $where .=" and (k.slope > $slope_threshold) ";
    	        else if ($params['channel_trend'] == "D")    
    		        $where .=" and (k.slope < -$slope_threshold) ";
    	        else if ($params['channel_trend'] == "S")    
    		        $where .=" and (k.slope >= -$slope_threshold and k.slope <= $slope_threshold) ";
    		}
    	}
    	
        $where .= bandSTDEV($params['pe_stdev'], "f1.stdev");
        $where .= bandSTDEV($params['pb_stdev'], "f2.stdev");
        
        $where .= bandTrend($params['pe_trend'], "f1.slope");
        $where .= bandTrend($params['pb_trend'], "f2.slope");
    
		if ($params['fscore'] != "A")
		{
		    if ($params['fscore'] == "9")    
		        $where .=" and f = 9 ";
	        else if ($params['fscore'] == "8")    
	            $where .=" and f = 8 ";
		    else if ($params['fscore'] == "7")    
		        $where .=" and f = 7 ";
	        else if ($params['fscore'] == "6")    
	            $where .=" and f = 6 ";
		    else if ($params['fscore'] == "5")    
		        $where .=" and f = 5 ";
	        else if ($params['fscore'] == "4")    
		        $where .=" and f = 4 ";
	        else if ($params['fscore'] == "3")    
		        $where .=" and f = 3 ";
	        else if ($params['fscore'] == "2")    
		        $where .=" and f = 2 ";
	        else if ($params['fscore'] == "1")    
		        $where .=" and f = 1 ";
	        else if ($params['fscore'] == "0")    
		        $where .=" and f = 0 ";
	        else if ($params['fscore'] == ">8")    
		        $where .=" and f >= 8 ";
	        else if ($params['fscore'] == ">7")    
		        $where .=" and f >= 7 ";
	        else if ($params['fscore'] == ">6")    
		        $where .=" and f >= 6 ";
	        else if ($params['fscore'] == ">5")    
		        $where .=" and f >= 5 ";
	        else if ($params['fscore'] == ">4")    
		        $where .=" and f >= 4 ";
	        else if ($params['fscore'] == ">3")    
		        $where .=" and f >= 3 ";
	        else if ($params['fscore'] == ">2")    
		        $where .=" and f >= 2 ";
	        else if ($params['fscore'] == ">1")    
		        $where .=" and f >= 1 ";
	        else if ($params['fscore'] == "<8")    
		        $where .=" and f <= 8 ";
	        else if ($params['fscore'] == "<7")    
		        $where .=" and f <= 7 ";
	        else if ($params['fscore'] == "<6")    
		        $where .=" and f <= 6 ";
	        else if ($params['fscore'] == "<5")    
		        $where .=" and f <= 5 ";
	        else if ($params['fscore'] == "<4")    
		        $where .=" and f <= 4 ";
	        else if ($params['fscore'] == "<3")    
		        $where .=" and f <= 3 ";
	        else if ($params['fscore'] == "<2")    
		        $where .=" and f <= 2 ";
	        else if ($params['fscore'] == "<1")    
		        $where .=" and f <= 1 and f >= 0 ";
		}

    	if( !empty($params['zscore']) ) {
    		if ($params['zscore'] != "A")
    		{
    		    if ($params['zscore'] == "S")    
    		        $where .=" and z > 2.99 ";
    	        else if ($params['zscore'] == "G")    
    		        $where .=" and z >= 1.81 && z <= 2.99 ";
    	        else if ($params['zscore'] == "D")    
    		        $where .=" and z < 1.81 ";
    		}
    	}
    
    	if( !empty($params['mscore']) ) {
    		if ($params['mscore'] != "A")
    		{
    		    if ($params['mscore'] == "M")    
    		        $where .=" and m > -2.22 ";
    	        else if ($params['mscore'] == "NM")    
    		        $where .=" and m < -2.22 ";
    		}
    	}
    	
    	if( !empty($params['ma10']) ) {
    		if ($params['ma10'] != "N")
    		{
    		    if ($params['ma10'] == "c>")    
    		        $where .=" and h.close > ma10 ";
    	        else if ($params['ma10'] == "c<")    
    		        $where .=" and h.close < ma10 ";
    	        else if ($params['ma10'] == ">20")    
    		        $where .=" and ma10 > ma20 ";
    	        else if ($params['ma10'] == "<20")    
    		        $where .=" and ma10 < ma20 ";
    	        else if ($params['ma10'] == ">50")    
    		        $where .=" and ma10 > ma50 ";
    	        else if ($params['ma10'] == "<50")    
    		        $where .=" and ma10 < ma50 ";
    	        else if ($params['ma10'] == ">0")    
    		        $where .=" and ma10 > ma100 ";
    	        else if ($params['ma10'] == "<0")    
    		        $where .=" and ma10 < ma100 ";
    	        else if ($params['ma10'] == ">200")    
    		        $where .=" and ma10 > ma200 ";
    	        else if ($params['ma10'] == "<200")    
    		        $where .=" and ma10 < ma200 ";
    	        else if ($params['ma10'] == ">250")    
    		        $where .=" and ma10 > ma250 ";
    	        else if ($params['ma10'] == "<250")    
    		        $where .=" and ma10 < ma250 ";
    	        else if ($params['ma10'] == "co")    
    		        $where .=" and h.close > ma10 and h.close_previous < ma10_previous ";
    	        else if ($params['ma10'] == "cu")    
    		        $where .=" and h.close < ma10 and h.close_previous > ma10_previous ";
    	        else if ($params['ma10'] == "o20")    
    		        $where .=" and ma10 > ma20 and ma10_previous < ma20_previous ";
    	        else if ($params['ma10'] == "u20")    
    		        $where .=" and ma10 < ma20 and ma10_previous > ma20_previous ";
    	        else if ($params['ma10'] == "o50")    
    		        $where .=" and ma10 > ma50 and ma10_previous < ma50_previous ";
    	        else if ($params['ma10'] == "u50")    
    		        $where .=" and ma10 < ma50 and ma10_previous > ma50_previous ";
    	        else if ($params['ma10'] == "o0")    
    		        $where .=" and ma10 > ma100 and ma10_previous < ma100_previous ";
    	        else if ($params['ma10'] == "u0")    
    		        $where .=" and ma10 < ma100 and ma10_previous > ma100_previous ";
    	        else if ($params['ma10'] == "o200")    
    		        $where .=" and ma10 > ma200 and ma10_previous < ma200_previous ";
    	        else if ($params['ma10'] == "u200")    
    		        $where .=" and ma10 < ma200 and ma10_previous > ma200_previous ";
    	        else if ($params['ma10'] == "o250")    
    		        $where .=" and ma10 > ma250 and ma10_previous < ma250_previous ";
    	        else if ($params['ma10'] == "u250")    
    		        $where .=" and ma10 < ma250 and ma10_previous > ma250_previous ";
    		}
    	}
    	
    	if( !empty($params['ma20']) ) {
    		if ($params['ma20'] != "N")
    		{
    		    if ($params['ma20'] == "c>")    
    		        $where .=" and h.close > ma20 ";
    	        else if ($params['ma20'] == "c<")    
    		        $where .=" and h.close < ma20 ";
    	        else if ($params['ma20'] == ">50")    
    		        $where .=" and ma20 > ma50 ";
    	        else if ($params['ma20'] == "<50")    
    		        $where .=" and ma20 < ma50 ";
    	        else if ($params['ma20'] == ">100")    
    		        $where .=" and ma20 > ma100 ";
    	        else if ($params['ma20'] == "<100")    
    		        $where .=" and ma20 < ma100 ";
    	        else if ($params['ma20'] == ">0")    
    		        $where .=" and ma20 > ma200 ";
    	        else if ($params['ma20'] == "<0")    
    		        $where .=" and ma20 < ma200 ";
    	        else if ($params['ma20'] == ">250")    
    		        $where .=" and ma20 > ma250 ";
    	        else if ($params['ma20'] == "<250")    
    		        $where .=" and ma20 < ma250 ";
    	        else if ($params['ma20'] == "co")    
    		        $where .=" and h.close > ma20 and h.close_previous < ma20_previous ";
    	        else if ($params['ma20'] == "cu")    
    		        $where .=" and h.close < ma20 and h.close_previous > ma20_previous ";
    	        else if ($params['ma20'] == "o50")    
    		        $where .=" and ma20 > ma50 and ma20_previous < ma50_previous ";
    	        else if ($params['ma20'] == "u50")    
    		        $where .=" and ma20 < ma50 and ma20_previous > ma50_previous ";
    	        else if ($params['ma20'] == "o100")    
    		        $where .=" and ma20 > ma100 and ma20_previous < ma100_previous ";
    	        else if ($params['ma20'] == "u100")    
    		        $where .=" and ma20 < ma100 and ma20_previous > ma100_previous ";
    	        else if ($params['ma20'] == "o0")    
    		        $where .=" and ma20 > ma200 and ma20_previous < ma200_previous ";
    	        else if ($params['ma20'] == "u0")    
    		        $where .=" and ma20 < ma200 and ma20_previous > ma200_previous ";
    	        else if ($params['ma20'] == "o250")    
    		        $where .=" and ma20 > ma250 and ma20_previous < ma250_previous ";
    	        else if ($params['ma20'] == "u250")    
    		        $where .=" and ma20 < ma250 and ma20_previous > ma250_previous ";
    		}
    	}
    	
    	if( !empty($params['ma50']) ) {
    		if ($params['ma50'] != "N")
    		{
    		    if ($params['ma50'] == "c>")    
    		        $where .=" and h.close > ma50 ";
    	        else if ($params['ma50'] == "c<")    
    		        $where .=" and h.close < ma50 ";
    	        else if ($params['ma50'] == ">100")    
    		        $where .=" and ma50 > ma100 ";
    	        else if ($params['ma50'] == "<100")    
    		        $where .=" and ma50 < ma100 ";
    	        else if ($params['ma50'] == ">200")    
    		        $where .=" and ma50 > ma200 ";
    	        else if ($params['ma50'] == "<200")    
    		        $where .=" and ma50 < ma200 ";
    	        else if ($params['ma50'] == ">250")    
    		        $where .=" and ma50 > ma250 ";
    	        else if ($params['ma50'] == "<250")    
    		        $where .=" and ma50 < ma250 ";
    	        else if ($params['ma50'] == "co")    
    		        $where .=" and h.close > ma50 and h.close_previous < ma50_previous ";
    	        else if ($params['ma50'] == "cu")    
    		        $where .=" and h.close < ma50 and h.close_previous > ma50_previous ";
    	        else if ($params['ma50'] == "o100")    
    		        $where .=" and ma50 > ma100 and ma50_previous < ma100_previous ";
    	        else if ($params['ma50'] == "u100")    
    		        $where .=" and ma50 < ma100 and ma50_previous > ma100_previous ";
    	        else if ($params['ma50'] == "o200")    
    		        $where .=" and ma50 > ma200 and ma50_previous < ma200_previous ";
    	        else if ($params['ma50'] == "u200")    
    		        $where .=" and ma50 < ma200 and ma50_previous > ma200_previous ";
    	        else if ($params['ma50'] == "o250")    
    		        $where .=" and ma50 > ma250 and ma50_previous < ma250_previous ";
    	        else if ($params['ma50'] == "u250")    
    		        $where .=" and ma50 < ma250 and ma50_previous > ma250_previous ";
    		}
    	}
    	
    	if( !empty($params['ma100']) ) {
    		if ($params['ma100'] != "N")
    		{
    		    if ($params['ma100'] == "c>")    
    		        $where .=" and h.close > ma100 ";
    	        else if ($params['ma100'] == "c<")    
    		        $where .=" and h.close < ma100 ";
    	        else if ($params['ma100'] == ">200")    
    		        $where .=" and ma100 > ma200 ";
    	        else if ($params['ma100'] == "<200")    
    		        $where .=" and ma100 < ma200 ";
    	        else if ($params['ma100'] == ">250")    
    		        $where .=" and ma100 > ma250 ";
    	        else if ($params['ma100'] == "<250")    
    		        $where .=" and ma100 < ma250 ";
    	        else if ($params['ma100'] == "co")    
    		        $where .=" and h.close > ma100 and h.close_previous < ma100_previous ";
    	        else if ($params['ma100'] == "cu")    
    		        $where .=" and h.close < ma100 and h.close_previous > ma100_previous ";
    	        else if ($params['ma100'] == "o200")    
    		        $where .=" and ma100 > ma200 and ma100_previous < ma200_previous ";
    	        else if ($params['ma100'] == "u200")    
    		        $where .=" and ma100 < ma200 and ma100_previous > ma200_previous ";
    	        else if ($params['ma100'] == "o250")    
    		        $where .=" and ma100 > ma250 and ma100_previous < ma250_previous ";
    	        else if ($params['ma100'] == "u250")    
    		        $where .=" and ma100 < ma250 and ma100_previous > ma250_previous ";
    		}
    	}
    	
    	if( !empty($params['ma200']) ) {
    		if ($params['ma200'] != "N")
    		{
    		    if ($params['ma200'] == "c>")    
    		        $where .=" and h.close > ma200 ";
    	        else if ($params['ma200'] == "c<")    
    		        $where .=" and h.close < ma200 ";
    	        else if ($params['ma200'] == ">250")    
    		        $where .=" and ma200 > ma250 ";
    	        else if ($params['ma200'] == "<250")    
    		        $where .=" and ma200 < ma250 ";
    	        else if ($params['ma200'] == "co")    
    		        $where .=" and h.close > ma200 and h.close_previous < ma200_previous ";
    	        else if ($params['ma200'] == "cu")    
    		        $where .=" and h.close < ma200 and h.close_previous > ma200_previous ";
    	        else if ($params['ma200'] == "o250")    
    		        $where .=" and ma200 > ma250 and ma200_previous < ma250_previous ";
    	        else if ($params['ma200'] == "u250")    
    		        $where .=" and ma200 < ma250 and ma200_previous > ma250_previous ";
    		}
    	}
    	
    	if( !empty($params['ma250']) ) {
    		if ($params['ma250'] != "N")
    		{
    		    if ($params['ma250'] == "c>")    
    		        $where .=" and h.close > ma250 ";
    	        else if ($params['ma250'] == "c<")    
    		        $where .=" and h.close < ma250 ";
    	        else if ($params['ma250'] == "co")    
    		        $where .=" and h.close > ma250 and h.close_previous < ma250_previous ";
    	        else if ($params['ma250'] == "cu")    
    		        $where .=" and h.close < ma250 and h.close_previous > ma250_previous ";
    		}
    	}

    	if( !empty($params['highlow']) ) {
    		if ($params['highlow'] != "N")
    		{
    		    if ($params['highlow'] == "25H")    
    		        $where .=" and h.close = high250 ";
    	        else if ($params['highlow'] == "25L")    
    		        $where .=" and h.close = low250 ";
    	        else if ($params['highlow'] == "525H")    
    		        $where .=" and h.close > 0.95 * high250 ";
    	        else if ($params['highlow'] == "525L")    
    		        $where .=" and h.close < 1.05 * low250 ";
		        else if ($params['highlow'] == "51025H")    
    		        $where .=" and h.close <= 0.95 * high250 and h.close > 0.9 * high250 ";
		        else if ($params['highlow'] == "51025L")    
    		        $where .=" and h.close >= 1.05 * low250 and h.close < 1.1 * low250 ";
		        else if ($params['highlow'] == "101525H")    
    		        $where .=" and h.close <= 0.9 * high250 and h.close > 0.85 * high250 ";
		        else if ($params['highlow'] == "101525L")    
    		        $where .=" and h.close >= 1.1 * low250 and h.close < 1.15 * low250 ";
    		        
		        else if ($params['highlow'] == "5H")    
    		        $where .=" and h.close = high50 ";
    	        else if ($params['highlow'] == "5L")    
    		        $where .=" and h.close = low50 ";

		        else if ($params['highlow'] == "2H")    
    		        $where .=" and h.close = high20 ";
    	        else if ($params['highlow'] == "2L")    
    		        $where .=" and h.close = low20 ";
    		        
		        else if ($params['highlow'] == "YH")    
    		        $where .=" and h.close = highYTD ";
    	        else if ($params['highlow'] == "YL")    
    		        $where .=" and h.close = lowYTD ";
    		}
    	}
    	
    	if( !empty($params['volume']) ) {
    		if ($params['volume'] != "N")
    		{
    		    if ($params['volume'] == ">35")    
    		        $where .=" and a.volume > 3 * vma5 ";
    	        else if ($params['volume'] == ">25")    
    		        $where .=" and a.volume > 2 * vma5 ";
    	        else if ($params['volume'] == ">5")    
    		        $where .=" and a.volume > vma5 ";
    	        else if ($params['volume'] == "<5")    
    		        $where .=" and a.volume < vma5 ";
		        else if ($params['volume'] == "<.55")    
    		        $where .=" and a.volume < 0.5 * vma5 ";
    		        
    		    else if ($params['volume'] == ">32")    
    		        $where .=" and a.volume > 3 * vma20 ";
    	        else if ($params['volume'] == ">22")    
    		        $where .=" and a.volume > 2 * vma20 ";
    	        else if ($params['volume'] == ">2")    
    		        $where .=" and a.volume > vma20 ";
    	        else if ($params['volume'] == "<2")    
    		        $where .=" and a.volume < vma20 ";
		        else if ($params['volume'] == "<.52")    
    		        $where .=" and a.volume < 0.5 * vma20 ";
    		        
		        else if ($params['volume'] == ">36")    
    		        $where .=" and a.volume > 3 * vma60 ";
    	        else if ($params['volume'] == ">26")    
    		        $where .=" and a.volume > 2 * vma60 ";
    	        else if ($params['volume'] == ">6")    
    		        $where .=" and a.volume > vma60 ";
    	        else if ($params['volume'] == "<6")    
    		        $where .=" and a.volume < vma60 ";
		        else if ($params['volume'] == "<.56")    
    		        $where .=" and a.volume < 0.5 * vma60 ";
    		    
    		    else if ($params['volume'] == "5>32")    
    		        $where .=" and vma5 > 3 * vma20 ";
    	        else if ($params['volume'] == "5>22")    
    		        $where .=" and vma5 > 2 * vma20 ";
    	        else if ($params['volume'] == "5>2")    
    		        $where .=" and vma5 > vma20 ";
    	        else if ($params['volume'] == "5<2")    
    		        $where .=" and vma5 < vma20 ";
		        else if ($params['volume'] == "5<.52")    
    		        $where .=" and vma5 < 0.5 * vma20 ";
    		        
		        else if ($params['volume'] == "2>36")    
    		        $where .=" and vma20 > 3 * vma60 ";
    	        else if ($params['volume'] == "2>26")    
    		        $where .=" and vma20 > 2 * vma60 ";
    	        else if ($params['volume'] == "2>6")    
    		        $where .=" and vma20 > vma60 ";
    	        else if ($params['volume'] == "2<6")    
    		        $where .=" and vma20 < vma60 ";
		        else if ($params['volume'] == "2<.56")    
    		        $where .=" and vma20 < 0.5 * vma60 ";
    		}
    	}
    	
        $where .= rsi("rsi14d", "i.bull_bear_not", $params);
        $where .= rsi("rsi14w", "j.bull_bear_not", $params);
    	
    	$where .= macd("macdd", "macdsignald", $params);
    	$where .= macd("macdw", "macdsignalw", $params);
    	
    	$leftjoin .= " left join fundamental_stock_general d on a.code = d.code ";
        $leftjoin .= " left join trendgauge_stock_current$alt g on a.code = g.code ";
        $leftjoin .= " left join pe_pb_bands_current$alt f on a.code = f.code and f.data_name = 'pe_pb_avg' ";
        $leftjoin .= " left join pe_pb_bands_current$alt f1 on a.code = f1.code and f1.data_name = 'pe' ";
        $leftjoin .= " left join pe_pb_bands_current$alt f2 on a.code = f2.code and f2.data_name = 'pb' ";
        $leftjoin .= " left join fundamental_stock_score_current e on a.code = e.code ";
        $leftjoin .= " left join stock_TA$alt h on a.code = h.code ";
        $leftjoin .= " left join stock_RSIdivergenceDaily_current$alt i on a.code = i.code ";
        $leftjoin .= " left join stock_RSIdivergenceWeekly_current$alt j on a.code = j.code ";
        $leftjoin .= " left join trendchannel_stock_current$alt k on a.code = k.code ";
    	/*if((!empty($params['polar_ta']) && $params['polar_ta'] != "A") || !empty($params['polar_trendgauge']) && $params['polar_trendgauge'] != "A")
    	    $leftjoin .= " left join trendgauge_stock_current g on a.code = g.code ";
    	    
        if((!empty($params['polar_valuation']) && $params['polar_valuation'] != "A") ||
            (!empty($params['pe_stdev']) && $params['pe_stdev'] != "A") ||
            (!empty($params['pe_trend']) && $params['pe_trend'] != "A") ||
            (!empty($params['pb_stdev']) && $params['pb_stdev'] != "A") ||
            (!empty($params['pb_trend']) && $params['pb_trend'] != "A"))
            $leftjoin .= " left join pe_pb_bands_current f on a.code = f.code and f.data_name = 'pe_pb_avg' ";
            
        if((!empty($params['polar_fa']) && $params['polar_fa'] != "A") ||
            (!empty($params['fscore']) && $params['fscore'] != "N") ||
            (!empty($params['zscore']) && $params['zscore'] != "N") ||
            (!empty($params['mscore']) && $params['mscore'] != "N"))
            $leftjoin .= " left join fundamental_stock_score_current e on a.code = e.code ";
    
        if((!empty($params['ma10']) && $params['ma10'] != "N") ||
            (!empty($params['ma20']) && $params['ma20'] != "N") ||
            (!empty($params['ma50']) && $params['ma50'] != "N") ||
            (!empty($params['ma100']) && $params['ma100'] != "N") ||
            (!empty($params['ma200']) && $params['ma200'] != "N") ||
            (!empty($params['ma250']) && $params['ma250'] != "N") ||
            (!empty($params['rsi14d']) && $params['rsi14d'] != "N" && $params['rsi14d'] != "u" && $params['rsi14d'] != "e") ||
            (!empty($params['rsi14w']) && $params['rsi14w'] != "N" && $params['rsi14w'] != "u" && $params['rsi14w'] != "e") ||
            (!empty($params['macdd']) && $params['macdd'] != "N") ||
            (!empty($params['macdw']) && $params['macdw'] != "N"))
            $leftjoin .= " left join stock_TA h on a.code = h.code ";
    
        if (!empty($params['rsi14d']) && $params['rsi14d'] != "N" && ($params['rsi14d'] == "u" || $params['rsi14d'] == "e"))
            $leftjoin .= " left join stock_RSIdivergenceDaily_current i on a.code = i.code ";
            
        if (!empty($params['rsi14w']) && $params['rsi14w'] != "N" && ($params['rsi14w'] == "u" || $params['rsi14w'] == "e"))
            $leftjoin .= " left join stock_RSIdivergenceWeekly_current j on a.code = j.code ";
        */
        
    	// getting total number records without any search
    	//$sql = "SELECT a.code, CONCAT_WS(' ', name_en, name_tc) as name, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM round(IFNULL(change_pct, 0), 2)))) +0) as close, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM if (close * shares >= if(currency = 'GBX', 100000000, 1000000), if(currency = 'GBX', round(close * shares / 100000000, 1), round(close * shares / 1000000, 1)), if(currency = 'GBX', round(close * shares / 100000000, 2), round(close * shares / 1000000, 2)))))) +0) as mcap, c.closeSeries, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM close))) +0) as change_pct FROM `price_current` a left join stock_code_name_exchange b on a.code = b.code left join price_history_sparkline c on a.code= c.code left join fundamental_stock_general d on a.code = d.code ";
    
    	//$sql = "SELECT a.code, CONCAT_WS(' ', name_en, name_tc) as name, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM round(IFNULL(change_pct, 0), 2)))) +0) as close, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM if (a.close * shares >= if(currency = 'GBX', 100000000, 1000000), round(a.close * shares / if(currency = 'GBX', 100000000, 1000000), 1), round(a.close * shares / if(currency = 'GBX', 100000000, 1000000), 2))))) +0) as mcap, if(polarscoreTA = -1, null, polarscoreTA), if(f.polarscore = -1, null, f.polarscore), if(polarscoreFA = -1, null, polarscoreFA), if(g.value = -1, null, round(g.value)), ifnull(f1.stdev, ''), ifnull(f2.stdev, ''), f, z, m, ma10, ma20, ma50, ma100, ma200, ma250, rsi14d, rsi14w, macdd, macdw, macdsignald, macdsignalw, f1.slope, f2.slope, c.closeSeries, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM a.close))) +0) as change_pct FROM `$price_current` a left join stock_code_name_exchange b on a.code = b.code left join price_history_sparkline c on a.code= c.code ";
    	$sql = "SELECT a.code, IF(name_en not like concat('%', name_tc, '%'), TRIM(CONCAT_WS('●', name_en, name_tc)), name_en) as name, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM round(IFNULL(change_pct, 0), 2)))) +0) as close, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM if (a.close * shares >= if(currency = 'GBX', 100000000, 1000000), round(a.close * shares / if(currency = 'GBX', 100000000, 1000000), 1), round(a.close * shares / if(currency = 'GBX', 100000000, 1000000), 2))))) +0) as mcap, if(polarscoreTA = -1, null, polarscoreTA), if(f.polarscore = -1, null, f.polarscore), if(polarscoreFA = -1, null, polarscoreFA), if(g.value = -1, null, round(g.value, 2)), ifnull(f1.stdev, ''), ifnull(f2.stdev, ''), f, round(z_without_x4 + 0.6 * a.close * shares / totalLiab_for_z / if(currency = 'GBX', 100, 1), 2) as z, m, round(rsi14d, 1), round(rsi14w, 1), i.bull_bear_not, j.bull_bear_not, f1.slope, f2.slope, c.closeSeries, (TRIM(TRAILING '.' FROM(TRIM(TRAILING '0' FROM a.close))) +0) as change_pct, k.midline, k.SE, IF(k.slope > $slope_threshold, 1, IF(k.slope < -$slope_threshold, -1, 0)), k.close_previous, k.midline_previous, REPLACE(sector, ' Services', ''), industry FROM `$price_current` a left join stock_code_name_exchange b on a.code = b.code left join price_history_sparkline$alt c on a.code= c.code ";
    	$sql .= $rightjoin;
    	$sql .= $leftjoin;
    	$sqlTot .= $sql;
    	$sqlRec .= $sql;
    	//concatenate search sql if value exist
    	if(isset($where) && $where != '') {
    
    		$sqlTot .= $where;
    		$sqlRec .= $where;
    	}
    
    
     	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir']."  LIMIT ".$params['start']." ,".$params['length']." ";
    
//file_put_contents("../php_script/sql.txt", $sqlRec);
    
    	$queryTot = mysqli_query($connection, $sqlTot) or die("database error:". mysqli_error($connection));
    
    	$totalRecords = mysqli_num_rows($queryTot);
    
    	$queryRecords = mysqli_query($connection, $sqlRec) or die("error to fetch stock data rows");
    
    	//iterate on results row and create new index array of data
    	while( $row = mysqli_fetch_row($queryRecords) ) { 
    		$data[] = $row;
    	}	
    
    
    	//$sql = "select DATE_FORMAT(tradedate,'%Y-%m-%d') from price_current where code = '$benchmark'";
    	$sql = "SELECT DATE_FORMAT(MAX(tradedate), '%Y-%m-%d') FROM ( SELECT tradedate FROM $price_current WHERE CODE = '$benchmark' UNION SELECT max(tradedate) FROM price_history WHERE CODE = '$benchmark' ) date";
    	$queryRecords = mysqli_query($connection, $sql) or die("error to fetch maxdate data");
    	while( $row = mysqli_fetch_row($queryRecords) ) { 
    		$maxdate = $row[0];
    		break;
    	}


    	$json_data = array(
    			"draw"            => intval( $params['draw'] ),   
    			"recordsTotal"    => intval( $totalRecords ),  
    			"recordsFiltered" => intval($totalRecords),
    			"data"            => $data,   // total data array
    			"maxdate"         => $maxdate
    			);
    
    	echo json_encode($json_data);  // send data as json format
    	
    	mysqli_close($connection);
    }
    else
        header("Location: /");
        
function macd($value, $signal, $params)
{
    $where = "";
	if( !empty($params[$value]) ) {
		if ($params[$value] != "N")
		{
		    if ($params[$value] == ">0")    
		        $where .=" and $value > 0 ";
	        else if ($params[$value] == "<0")    
	            $where .=" and $value < 0 ";
		    else if ($params[$value] == ">s")    
		        $where .=" and $value > $signal ";
	        else if ($params[$value] == "<s")    
	            $where .=" and $value < $signal ";
		    else if ($params[$value] == ">s>0")    
		        $where .=" and $value > $signal and $value > 0 ";
	        else if ($params[$value] == "<s<0")    
		        $where .=" and $value < $signal and $value < 0 ";
	        else if ($params[$value] == ">s<0")    
		        $where .=" and $value > $signal and $value < 0 ";
	        else if ($params[$value] == "<s>0")    
		        $where .=" and $value < $signal and $value > 0 ";
	        else if ($params[$value] == "o0")    
		        $where .=" and $value > 0 and $value"."_previous < 0 ";
	        else if ($params[$value] == "u0")    
		        $where .=" and $value < 0 and $value"."_previous > 0 ";
	        else if ($params[$value] == "os")    
		        $where .=" and $value > $signal and $value"."_previous < $signal"."_previous ";
	        else if ($params[$value] == "us")    
		        $where .=" and $value < $signal and $value"."_previous > $signal"."_previous ";
		}
	}    
	
	return $where;
}

function rsi($value, $bull_bear_not, $params)
{
    $where = "";
	if( !empty($params[$value]) ) {
		if ($params[$value] != "N")
		{
		    if ($params[$value] == ">7")    
		        $where .=" and $value > 70 ";
	        else if ($params[$value] == ">5")    
	            $where .=" and $value > 50 ";
		    else if ($params[$value] == "<3")    
		        $where .=" and $value < 30 ";
	        else if ($params[$value] == "<5")    
	            $where .=" and $value < 50 ";
		    else if ($params[$value] == "o7")    
		        $where .=" and $value > 70 and $value"."_previous < 70 ";
	        else if ($params[$value] == "u7")    
		        $where .=" and $value < 70 and $value"."_previous > 70 ";
	        else if ($params[$value] == "o5")    
		        $where .=" and $value > 50 and $value"."_previous < 50 ";
	        else if ($params[$value] == "u5")    
		        $where .=" and $value < 50 and $value"."_previous > 50 ";
	        else if ($params[$value] == "o3")    
		        $where .=" and $value > 30 and $value"."_previous < 30 ";
	        else if ($params[$value] == "u3")    
		        $where .=" and $value < 30 and $value"."_previous > 30 ";
	        else if ($params[$value] == "u")    
		        $where .=" and $bull_bear_not = 1 ";
	        else if ($params[$value] == "e")    
		        $where .=" and $bull_bear_not = -1 ";
		}
	}    
	
	return $where;
}

function bandTrend($value, $trend)
{
    $where = "";
    
	if( !empty($value) ) {
		if ($value != "A")
		{
		    if ($value == "SUMU")
		        $where .=" and ($trend = 2 or $trend = 1) ";
		    else if ($value == "SU")
		        $where .=" and $trend = 2 ";
	        else if ($value == "MU")    
		        $where .=" and $trend = 1 ";
	        else if ($value == "S")    
		        $where .=" and $trend = 0 ";
	        else if ($value == "MD")    
		        $where .=" and $trend = -1 ";
	        else if ($value == "SD")    
		        $where .=" and $trend = -2 ";
	        else if ($value == "SDMD")    
		        $where .=" and ($trend = -2 or $trend = -1) ";
		}
	}
	
	return $where;
}

function bandSTDEV($value, $stdev)
{
    $where = "";
    
	if( !empty($value) ) {
		if ($value != "A")
		{
		    if ($value == "-1")    
		        $where .=" and $stdev < 0 and $stdev >= -1 ";
	        else if ($value == "-1-2")    
		        $where .=" and $stdev < -1 and $stdev >= -2 ";
	        else if ($value == "-2-3")    
		        $where .=" and $stdev < -2 and $stdev >= -3 ";
	        else if ($value == "1")    
		        $where .=" and $stdev >= 0 and $stdev <= 1 ";
	        else if ($value == "12")    
		        $where .=" and $stdev > 1 and $stdev <= 2 ";
	        else if ($value == "23")    
		        $where .=" and $stdev > 2 and $stdev <= 3 ";
		        
	        else if ($value == "<3")    
		        $where .=" and $stdev < 3 ";
	        else if ($value == "<2")    
		        $where .=" and $stdev < 2 ";
	        else if ($value == "<1")    
		        $where .=" and $stdev < 1 ";
	        else if ($value == "<")    
		        $where .=" and $stdev < 0 ";
	        else if ($value == "<-1")    
		        $where .=" and $stdev < -1 ";
	        else if ($value == "<-2")    
		        $where .=" and $stdev < -2 ";
	        else if ($value == "<-3")    
		        $where .=" and $stdev < -3 ";
		        
	        else if ($value == ">3")    
		        $where .=" and $stdev > 3 ";
	        else if ($value == ">2")    
		        $where .=" and $stdev > 2 ";
	        else if ($value == ">1")    
		        $where .=" and $stdev > 1 ";
	        else if ($value == ">")    
		        $where .=" and $stdev > 0 ";
	        else if ($value == ">-1")    
		        $where .=" and $stdev > -1 ";
	        else if ($value == ">-2")    
		        $where .=" and $stdev > -2 ";
	        else if ($value == ">-3")    
		        $where .=" and $stdev > -3 ";
		}
	}
	
	return $where;
}

function polar($value, $polarTXT) {
    $where = "";
    
	if ($value != "A")
	{
	    if ($value == "5")    
	        $where .=" and $polarTXT = 5 ";
        else if ($value == "4")    
	        $where .=" and $polarTXT = 4 ";
        else if ($value == "3")    
	        $where .=" and $polarTXT = 3 ";
        else if ($value == "2")    
	        $where .=" and $polarTXT = 2 ";
        else if ($value == "1")    
	        $where .=" and $polarTXT = 1 ";
        else if ($value == "0")    
	        $where .=" and $polarTXT = 0 ";
        else if ($value == ">4")    
	        $where .=" and $polarTXT >= 4 ";
        else if ($value == ">3")    
	        $where .=" and $polarTXT >= 3 ";
        else if ($value == ">2")    
	        $where .=" and $polarTXT >= 2 ";
        else if ($value == ">1")    
	        $where .=" and $polarTXT >= 1 ";
        else if ($value == "<4")    
	        $where .=" and $polarTXT <= 4 ";
        else if ($value == "<3")    
	        $where .=" and $polarTXT <= 3 ";
        else if ($value == "<2")    
	        $where .=" and $polarTXT <= 2 ";
        else if ($value == "<1")    
	        $where .=" and $polarTXT <= 1 and $polarTXT >= 0 ";
	}

	return $where;
}

	/*
	//include connection file 
	include_once("connection.php");
	 
	// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		0 =>'id',
		1 =>'employee_name', 
		2 => 'employee_salary',
		3 => 'employee_age'
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" WHERE ";
		$where .=" ( employee_name LIKE '".$params['search']['value']."%' ";    
		$where .=" OR employee_salary LIKE '".$params['search']['value']."%' ";

		$where .=" OR employee_age LIKE '".$params['search']['value']."%' )";
	}

	if( !empty($params['user_id']) ) {   
		$where .=" WHERE ";
		$where .="  id = '".$params['user_id']."' ";    
//		$where .=" ( employee_name LIKE '".$params['user_id']."%' ";    
//		$where .=" OR employee_salary LIKE '".$params['user_id']."%' ";

//		$where .=" OR employee_age LIKE '".$params['user_id']."%' )";
	}
	
	// getting total number records without any search
	$sql = "SELECT * FROM `employee` ";
	$sqlTot .= $sql;
	$sqlRec .= $sql;
	//concatenate search sql if value exist
	if(isset($where) && $where != '') {

		$sqlTot .= $where;
		$sqlRec .= $where;
	}


 	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir']."  LIMIT ".$params['start']." ,".$params['length']." ";

	$queryTot = mysqli_query($connection, $sqlTot) or die("database error:". mysqli_error($connection));


	$totalRecords = mysqli_num_rows($queryTot);

	$queryRecords = mysqli_query($connection, $sqlRec) or die("error to fetch employees data");

	//iterate on results row and create new index array of data
	while( $row = mysqli_fetch_row($queryRecords) ) { 
		$data[] = $row;
	}	

	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format*/
?>
	