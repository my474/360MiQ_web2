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
        include '/home2/aamiqcom/cronjobs/email.php';

        header('Content-Type: application/json');

        $payload = $_POST['payload'] ?? '';
        $isSearch = $_POST['isSearch'] ?? '';
        $rawStockdata = $_POST['stockdata'] ?? '{}';

        // Decode to associative array
        $stockdata = json_decode($rawStockdata, true);

        // Access values
        $code = $stockdata['code'] ?? null;
        $name_en = $stockdata['name_en'] ?? null;
        $name_tc = $stockdata['name_tc'] ?? null;
        $exchange = $stockdata['exchange'] ?? null;
        $tradedate = $stockdata['tradedate'] ?? null;
        $bull_bear_not_d = $stockdata['bull_bear_not_d'] ?? null;
        $bull_bear_not_w = $stockdata['bull_bear_not_w'] ?? null;
        $close = $stockdata['close'] ?? null;
        $close_previous = $stockdata['close_previous'] ?? null;
        $high20 = $stockdata['high20'] ?? null;
        $high50 = $stockdata['high50'] ?? null;
        $high250 = $stockdata['high250'] ?? null;
        $highYTD = $stockdata['highYTD'] ?? null;
        $low20 = $stockdata['low20'] ?? null;
        $low50 = $stockdata['low50'] ?? null;
        $low250 = $stockdata['low250'] ?? null;
        $lowYTD = $stockdata['lowYTD'] ?? null;
        $ma20 = smartRound($stockdata['ma20'] ?? null);
        $ma50 = smartRound($stockdata['ma50'] ?? null);
        $ma_long = smartRound($stockdata['ma_long'] ?? null);
        $ma20_previous = smartRound($stockdata['ma20_previous'] ?? null);
        $ma50_previous = smartRound($stockdata['ma50_previous'] ?? null);
        $ma_long_previous = smartRound($stockdata['ma_long_previous'] ?? null);
        $longperiod =  $stockdata['longperiod'] ?? null;
        $rsi14d = isset($stockdata['rsi14d']) && is_numeric($stockdata['rsi14d']) ? round($stockdata['rsi14d'], 1) : null;
        $rsi14d_previous = isset($stockdata['rsi14d_previous']) && is_numeric($stockdata['rsi14d_previous']) ? round($stockdata['rsi14d_previous'], 1) : null;
        $rsi14w = isset($stockdata['rsi14w']) && is_numeric($stockdata['rsi14w']) ? round($stockdata['rsi14w'], 1) : null;
        $rsi14w_previous = isset($stockdata['rsi14w_previous']) && is_numeric($stockdata['rsi14w_previous']) ? round($stockdata['rsi14w_previous'], 1) : null;
        $midline = $stockdata['midline'] ?? null;
        $midline_previous = $stockdata['midline_previous'] ?? null;
        $SE = $stockdata['SE'] ?? null;
        $slope = $stockdata['slope'] ?? null;
        $trendvalue = $stockdata['trendvalue'] ?? null;
        $polarscoreFA = $stockdata['polarscoreFA'] ?? null;
        $closeSeries = $stockdata['closeSeries'] ?? null;
        $fundamental = $stockdata['fundamental'] ?? null;
        $eps = $fundamental['EPS'] ?? null;
        $eps_yoy = $fundamental['EPS_YoY'] ?? null;
        $evEbitda = $fundamental['EVEbitda'] ?? null;
        $evRevenue = $fundamental['EVR'] ?? null;
        $forwardPE = $fundamental['FPE'] ?? null;
        $grossProfitTTM = $fundamental['GPTTM'] ?? null;
        $grossProfitTTM_yoy = $fundamental['GPTTM_YoY'] ?? null;
        $mostRecentQuarter = $fundamental['MRQ'] ?? null;
        $OperatingMarginTTM = $fundamental['OMTTM'] ?? null;
        $OperatingMarginTTM_yoy = $fundamental['OMTTM_YOY'] ?? null;
        $priceBookMRQ = $fundamental['PBMRQ'] ?? null;
        $ProfitMargin = $fundamental['PM'] ?? null;
        $ProfitMargin_yoy = $fundamental['PM_YoY'] ?? null;
        $priceSalesTTM = $fundamental['PSTTM'] ?? null;
        $ReturnOnAssetsTTM = $fundamental['ROATTM'] ?? null;
        $ReturnOnAssetsTTM_yoy = $fundamental['ROATTM_YoY'] ?? null;
        $ReturnOnEquityTTM = $fundamental['ROETTM'] ?? null;
        $ReturnOnEquityTTM_yoy = $fundamental['ROETTM_YoY'] ?? null;
        $RevenuePerShareTTM = $fundamental['RPSTTM'] ?? null;
        $RevenuePerShareTTM_yoy = $fundamental['RPSTTM_YoY'] ?? null;
        $RevenueTTM = $fundamental['RTTM'] ?? null;
        $RevenueTTM_yoy = $fundamental['RTTM_YoY'] ?? null;
        $trailingPE = $fundamental['TPE'] ?? null;

        $system_prompt = "Stock analysis context:\n";

        if ($code && $close && $tradedate) $system_prompt .= "The most recent quarter is $mostRecentQuarter. As of $tradedate, $name_en ($code) is trading at $close.\n";
        if ($ma20) $system_prompt .= "The 20-day moving average (MA20) is $ma20.\n";
        if ($ma50) $system_prompt .= "The 50-day moving average (MA50) is $ma50.\n";
        if ($ma_long && $longperiod) $system_prompt .= "The $longperiod-day moving average (MA$longperiod) is $ma_long.\n";
        if ($rsi14d) $system_prompt .= "The 14-day RSI is $rsi14d.\n";
        if ($rsi14w) $system_prompt .= "The 14-week RSI is $rsi14w.\n";

        if ($high20) $system_prompt .= "The 20-day high is $high20.\n";
        if ($high50) $system_prompt .= "The 50-day high is $high50.\n";
        if ($high250) $system_prompt .= "The 250-day high is $high250.\n";
        if ($highYTD) $system_prompt .= "The YTD high is $highYTD.\n";

        if ($low20) $system_prompt .= "The 20-day low is $low20.\n";
        if ($low50) $system_prompt .= "The 50-day low is $low50.\n";
        if ($low250) $system_prompt .= "The 250-day low is $low250.\n";
        if ($lowYTD) $system_prompt .= "The YTD low is $lowYTD.\n";

        if ($closeSeries) $system_prompt .= "The past 6 months price series is '$closeSeries', from left (oldest) to right (newest), each data point is about 2 weeks apart.\n";

        //if ($bull_bear_not_d) $system_prompt .= "Bull/Bear signal (daily): $bull_bear_not_d.\n";
        //if ($bull_bear_not_w) $system_prompt .= "Bull/Bear signal (weekly): $bull_bear_not_w.\n";

        if ($forwardPE) $system_prompt .= "The forward P/E ratio is $forwardPE.\n";
        if ($trailingPE) $system_prompt .= "The trailing P/E ratio is $trailingPE.\n";
        if ($evEbitda) $system_prompt .= "EV/EBITDA is $evEbitda.\n";
        if ($priceBookMRQ) $system_prompt .= "Price-to-book ratio (MRQ) is $priceBookMRQ.\n";
        if ($priceSalesTTM) $system_prompt .= "Price-to-sales ratio (TTM) is $priceSalesTTM.\n";

        if ($ReturnOnEquityTTM) $system_prompt .= "Return on equity (TTM) is $ReturnOnEquityTTM%";
        if ($ReturnOnEquityTTM_yoy)
        {
            $sign = signage($ReturnOnEquityTTM_yoy);
            $system_prompt .= ", $sign$ReturnOnEquityTTM_yoy% YoY.\n";
        }
        else $system_prompt .= ".\n";

        if ($ReturnOnAssetsTTM) $system_prompt .= "Return on assets (TTM) is $ReturnOnAssetsTTM%";
        if ($ReturnOnAssetsTTM_yoy)
        {
            $sign = signage($ReturnOnAssetsTTM_yoy);
            $system_prompt .= ", $sign$ReturnOnAssetsTTM_yoy% YoY.\n";
        }
        else $system_prompt .= ".\n";

        if ($OperatingMarginTTM) $system_prompt .= "Operating margin (TTM) is $OperatingMarginTTM%";
        if ($OperatingMarginTTM_yoy)
        {
            $sign = signage($OperatingMarginTTM_yoy);
            $system_prompt .= ", $sign$OperatingMarginTTM_yoy% YoY.\n";
        }
        else $system_prompt .= ".\n";

        if ($ProfitMargin) $system_prompt .= "Profit margin is $ProfitMargin%";
        if ($ProfitMargin_yoy)
        {
            $sign = signage($ProfitMargin_yoy);
            $system_prompt .= ", $sign$ProfitMargin_yoy% YoY.\n";
        }
        else $system_prompt .= ".\n";

        if ($RevenueTTM) $system_prompt .= "Revenue (TTM) is $RevenueTTM";
        if ($RevenueTTM_yoy)
        {
            $sign = signage($RevenueTTM_yoy);
            $system_prompt .= ", $sign$RevenueTTM_yoy% YoY.\n";
        }
        else $system_prompt .= ".\n";

        if ($eps) $system_prompt .= "Earnings per share (EPS) is $eps";
        if ($eps_yoy)
        {
            $sign = signage($eps_yoy);
            $system_prompt .= ", $sign$eps_yoy% YoY.\n";
        }
            else $system_prompt .= ".\n";

        $system_prompt .= "Use and/or show the above data to assist with stock interpretation, fundamental, valuation, or trend identification.\n";

        //file_put_contents("./search.txt", $system_prompt);

        $result = ollama($payload, $isSearch, $system_prompt);
        $data = json_decode($result, true);

        $text = $data['choices'][0]['message']['content'] ?? '';
        $finishReason = $data['choices'][0]['finish_reason'] ?? null;

        // Never show an incomplete answer or a raw reasoning trace to the user.
        if ($finishReason === 'length') {
            $text = "360MiQ AI's response was cut off. Please try again.";
        } else {
            $text = stripReasoningTrace($text);
        }

        $prefix = "Based on the provided data, ";
        if (str_starts_with($text, $prefix)) {
            $text = ucfirst(ltrim(substr($text, strlen($prefix))));
        }

        if ($text !== '' && !preg_match('/[,.?!]$/', trim($text))) {
            $text .= '.';
        }

        if (preg_match('/gemma[ ,\.]/i', $text)) {
            // 'gemma' found in $text
            $text = "I am your AI stock assistant. I can answer your stock-related questions.";
        }

        if ($isSearch != '' && $text)
        {
            preg_match_all('/@+([A-Z.]{1,10})@+/', $text, $matches);
            $json = json_encode(['code' => $matches[1][0] ?? null]);
            echo $json;
        }
        else if ($text) {
            echo json_encode(['result' => $text]);
        } else {
            echo json_encode(['status' => 'not_found', 'result' => null]);
        }
        //echo $result;
    }
    else
        header("Location: /");


/*function ollama($prompt, $isSearch, $system_prompt)
{
    $modelFile = '/home2/aamiqcom/cronjobs/latest_LLMmodel_openrouter.txt';
    $model = is_file($modelFile) ? trim((string)file_get_contents($modelFile)) : 'google/gemma-3-27b-it:free'; // default OpenRouter model

    // OpenRouter API endpoint
    $url = 'https://openrouter.ai/api/v1/chat/completions';

    // Build request data
    $data = [
        'model' => $model,
        'messages' => [
            [
                "role"=> "user",
                "content"=> $prompt
            ],
            [
                "role"=> "system",
                "content"=> $isSearch == ''
                    ? $system_prompt . "Consider the data is your internal knowledge, no need to say 'Based on the provided data'. Just answer and explain concisely in less than 5 sentences. DO NOT give trading or investment recommendation, such as buy/sell/hold. You are an AI stock assistant. Only answer questions that are related to finance, investing, stock markets, stock, macroeconomics, or business. If the question is not related to these topics, respond with 'I'm only able to answer stock-related questions.'"
                    : "Do not guess. Do not explain. Only return a stock symbol found in the user's message. Wrap the stock symbol in @@. If no symbol is mentioned or implied explicitly, return an empty string.",
            ],
        ],
        'max_tokens' => 1000,
        'temperature' => 0.9,
        'top_p' => 0.95,
        'presence_penalty' => 0.4,
        'frequency_penalty' => 0.2,
        'stop' => ['\n', '. ', '? ', '! '],
        'stream' => false,
    ];

    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . getenv('OPENROUTER_API_KEY'),
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    $response = curl_exec($ch);

    if ($response === false) {
        error_log("OpenRouter API cURL error: " . curl_error($ch));
        curl_close($ch);
        return json_encode([
            'choices' => [
                [
                    'message' => ['content' => '360MiQ AI is temporarily unavailable.']
                ]
            ]
        ]);
    }

    curl_close($ch);

    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        $msg = "OpenRouter API error: " . $decoded['error']['message'] . " (model: $model)";
        error_log($msg);
        return json_encode([
            'choices' => [
                [
                    'message' => ['content' => '360MiQ AI has gone AWOL! Please try again later.']
                ]
            ]
        ]);
    }

    // Trim leading and trailing newlines from the returned content
    if (isset($decoded['choices'][0]['message']['content'])) {
        $decoded['choices'][0]['message']['content'] = preg_replace('/^\n+|\n+$/', '', $decoded['choices'][0]['message']['content']);
    }

    return json_encode($decoded);
}*/

// Groq blocks Bluehost IP, no longer works
function ollama($prompt, $isSearch, $system_prompt)
{
    $modelFile = '/home2/aamiqcom/cronjobs/latest_LLMmodel.txt';
    $model = is_file($modelFile) ? trim((string)file_get_contents($modelFile)) : 'llama-3.3-70b-versatile';
    $isQwen = stripos($model, 'qwen') !== false;

    $instructions = $isSearch == ''
        ? $system_prompt . "Consider the data is your internal knowledge, no need to say 'Based on the provided data'. Just answer and explain concisely in 5 to 10 sentences. Format the answer into bullet points using ● and/or paragraphs. Use a $ sign in front of prices. Do not reveal your thinking or reasoning process. DO NOT give trading or investment recommendations, such as buy/sell/hold. You are an AI stock assistant. Only answer questions related to finance, investing, stock markets, stocks, macroeconomics, or business. If the question is unrelated to these topics, respond with 'I'm only able to answer stock-related questions.'"
        : "Do not guess. Do not explain. Only return a stock symbol found in the user's message. Wrap the stock symbol in @@. If no symbol is mentioned or explicitly implied, return an empty string.";

    // Groq recommends putting instructions in the user message for Qwen reasoning models.
    // Other chat models receive the conventional system-first message ordering.
    $messages = $isQwen
        ? [[
            'role' => 'user',
            'content' => "Instructions:\n" . $instructions . "\n\nUser question:\n" . $prompt,
        ]]
        : [
            [
                'role' => 'system',
                'content' => $instructions,
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];


    // Replace with your actual API endpoint path
    //$url = 'https://openrouter.ai/api/v1/chat/completions';
    $url = 'https://api.groq.com/openai/v1/chat/completions';

    // Build request data - adapt keys to your API spec
    $data = [
        //'model' => 'tinyllama:1.1b', // if needed
        //'model' => 'google/gemma-3-27b-it:free',
        //'model' => 'llama3-70b-8192',
        //'model' => 'llama-3.3-70b-versatile',
        'model' => $model,
        'messages' => $messages,
        //'prompt' => $prompt . ' (Answer in 1 to 2 sentences and less than 40 words.)', //'Explain the risk-return tradeoff in finance.',
        //'prompt' => "User asked: \"$prompt\"\n\n(If the user question is not related to finance, investing, stock markets, stock, macroeconomics, or business, respond only with: \"I'm only able to answer finance-related questions.\") (Answer in 1 to 2 sentences and less than 40 words.)",
        //'system' => $isSearch == '' ? "Do not explain or reason. Just answer concisely in less than 2 to 3 sentences. You are a financial assistant. Only answer questions that are related to finance, investing, stock markets, stock, macroeconomics, or business. If the question is not related to these topics, respond with 'I'm only able to answer finance-related questions.'" : "Do not guess. Do not explain. Only return a stock symbol found in the user's message. Wrap the stock symbol in @@. If no symbol is mentioned or implied explicitly, return an empty string.",
        'max_completion_tokens' => 1000,
        //'options' => [
            'temperature' => 0.9,
            //'top_k' => 0,
            'top_p' => 0.95,
            //'repeat_penalty' => 1.1,
            'presence_penalty' => 0.4,          // Slight bias toward introducing new facts
            'frequency_penalty' => 0.2,         // Reduces repeated terms
            //'stop' => ['\n', '. ', '? ', '! '], // Clean sentence-ending breaks
            //'mirostat' => 0,                    // Off for deterministic output
            //'num_ctx' => 2048,                  // Typical context window
            //'num_predict' => 60,                // Limit answer length
            //'seed' => 42,                       // Reproducible responses
            'stream' => false,                   // Disable streaming for clean API use
        //],
        //'reasoning' => [
        //    'max_tokens' => 2000,
        //    'exclude'=> true
        // ]

    ];

    if ($isQwen) {
        // Qwen 3.x enables raw reasoning by default; disable it for concise end-user chat.
        $data['reasoning_effort'] = 'none';
        $data['temperature'] = 0.7;
        $data['top_p'] = 0.8;
    }

    // Encode to JSON
    $jsonData = json_encode($data);

    $groqApiKey = trim((string)getenv('GROQ_API_KEY'));
    if ($groqApiKey === '') {
        error_log('Groq API error: GROQ_API_KEY is not configured.');
        return json_encode([
            'choices' => [[
                'message' => [
                    'content' => '360MiQ AI is temporarily unavailable.'
                ]
            ]]
        ]);
    }

    // Setup cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $groqApiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Execute request
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode and check for errors
    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        $msg = "Groq API error: " . $decoded['error']['message'] . " (model: $model)\n\n360MiQ AI chatbot is not working. Update model in LLM_request.php and/or cronjobs/GROQ_LLM_model_updater.php";
        error_log($msg);

        // Email only once per 12 hours
        $flagFile = __DIR__ . '/groq_email_flag.txt';
        $lastSent = is_file($flagFile) ? (int)file_get_contents($flagFile) : 0;
        $now = time();

        if ($now - $lastSent > 43200) { // 12 hours = 43200 seconds
            email("Groq Model Error", $msg);
            file_put_contents($flagFile, $now);
        }

        // Always start with structured array
        $decommissionedResponse = [
            'choices' => [
                [
                    'message' => [
                        'content' => '360MiQ AI has gone AWOL! Please try again later.'
                    ]
                ]
            ]
        ];

        return json_encode($decommissionedResponse);
    }

    return $response;

}

function stripReasoningTrace($text)
{
    $text = trim((string)$text);

    // reasoning_format=raw wraps Qwen reasoning in <think> tags. Keep this as a
    // defensive fallback in case a model configuration changes upstream.
    $text = preg_replace('/^\s*<think>.*?<\/think>\s*/su', '', $text);

    // If the trace itself was truncated, there is no safe final answer to show.
    if (preg_match('/^\s*<think>/u', $text)) {
        return '';
    }

    return trim($text);
}

function signage($value)
{
    $sign = "+";
    if ($value < 0)
        $sign = '';
    else if ($value == 0)
        $sign = '±';

    return $sign;
}

function smartRound($value) {
    if (!is_numeric($value)) return null;

    if ($value >= 1) {
        return round($value, 2);
    } elseif ($value >= 0.1) {
        return round($value, 3);
    } else {
        return round($value, 4);
    }
}
