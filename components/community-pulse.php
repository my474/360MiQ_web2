<?php
require_once __DIR__ . '/../account/config.php';
if (!miq_community_enabled()) {
    return;
}

$miq_pulse_context_type = isset($miq_account_context_type) ? $miq_account_context_type : 'site';
$miq_pulse_context_key = isset($miq_account_context_key) && $miq_account_context_key !== '' ? $miq_account_context_key : 'site';
if (!in_array($miq_pulse_context_type, array('stock', 'market'), true)) {
    $miq_pulse_context_type = 'site';
    $miq_pulse_context_key = 'site';
}
$miq_pulse_timeframe = '30d';
$miq_pulse_end_date = gmdate('Y-m-d', time() + (30 * 86400));
$miq_pulse_kicker = !empty($miq_pulse_featured) ? 'Featured this week' : 'Community pulse';
$miq_pulse_market_labels = array(
    'NYSE' => 'NYSE',
    'NASDAQ' => 'Nasdaq',
    'LSE' => 'London',
    'TSX' => 'Toronto TSX',
    'ASX' => 'Australia',
    'NSE' => 'India NSE',
    'TYO' => 'Tokyo',
    'HKEX' => 'Hong Kong',
    'SHSE' => 'Shanghai',
    'SZSE' => 'Shenzhen',
);

if ($miq_pulse_context_type === 'stock' && $miq_pulse_context_key !== 'site') {
    $miq_pulse_subject = $miq_pulse_context_key;
    $miq_pulse_title = 'Your view on ' . $miq_pulse_context_key . ' for the next 30 days ending ' . $miq_pulse_end_date . '?';
    $miq_pulse_aria_label = 'Thirty-day community outlook for ' . $miq_pulse_context_key . ' ending ' . $miq_pulse_end_date;
} elseif ($miq_pulse_context_type === 'market' && $miq_pulse_context_key !== 'site') {
    $miq_pulse_market_name = isset($miq_pulse_market_labels[$miq_pulse_context_key])
        ? $miq_pulse_market_labels[$miq_pulse_context_key]
        : $miq_pulse_context_key;
    $miq_pulse_subject = $miq_pulse_market_name;
    $miq_pulse_title = 'Your view on ' . $miq_pulse_market_name . ' for the next 30 days ending ' . $miq_pulse_end_date . '?';
    $miq_pulse_aria_label = 'Thirty-day community outlook for ' . $miq_pulse_market_name . ' ending ' . $miq_pulse_end_date;
} else {
    $miq_pulse_subject = 'Global market';
    $miq_pulse_title = 'Global market outlook for the next 30 days ending ' . $miq_pulse_end_date . '?';
    $miq_pulse_aria_label = 'Thirty-day global community outlook ending ' . $miq_pulse_end_date;
}
?>
<section id="miq-community-pulse" class="miq-community-pulse" data-context-type="<?php echo htmlspecialchars($miq_pulse_context_type, ENT_QUOTES, 'UTF-8'); ?>" data-context-key="<?php echo htmlspecialchars($miq_pulse_context_key, ENT_QUOTES, 'UTF-8'); ?>" data-timeframe="<?php echo $miq_pulse_timeframe; ?>" data-period-end="<?php echo $miq_pulse_end_date; ?>" aria-labelledby="miq-community-pulse-title">
    <div class="miq-community-pulse-copy">
        <span class="miq-community-pulse-kicker"><?php echo htmlspecialchars($miq_pulse_kicker, ENT_QUOTES, 'UTF-8'); ?></span>
        <h2 id="miq-community-pulse-title"><?php echo htmlspecialchars($miq_pulse_title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>Vote bullish, neutral, or bearish. You can change your view anytime.</p>
        <span class="miq-community-pulse-zero-state" data-pulse-zero-state hidden>Be the first to vote.</span>
        <div class="miq-sentiment-chart miq-sentiment-chart-compact" data-sentiment-chart data-chart-mode="compact" data-context-type="<?php echo htmlspecialchars($miq_pulse_context_type, ENT_QUOTES, 'UTF-8'); ?>" data-context-key="<?php echo htmlspecialchars($miq_pulse_context_key, ENT_QUOTES, 'UTF-8'); ?>" data-timeframe="<?php echo $miq_pulse_timeframe; ?>" data-subject="<?php echo htmlspecialchars($miq_pulse_subject, ENT_QUOTES, 'UTF-8'); ?>" hidden>
            <div class="miq-sentiment-chart-plot" data-sentiment-plot></div>
            <span class="miq-sentiment-chart-status" data-sentiment-status></span>
        </div>
    </div>
    <div class="miq-community-pulse-actions" role="group" aria-label="<?php echo htmlspecialchars($miq_pulse_aria_label, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="button" data-pulse-vote="bullish"><i class="fas fa-arrow-up"></i> Bullish <span data-count="bullish">–</span></button>
        <button type="button" data-pulse-vote="neutral"><i class="fas fa-minus"></i> Neutral <span data-count="neutral">–</span></button>
        <button type="button" data-pulse-vote="bearish"><i class="fas fa-arrow-down"></i> Bearish <span data-count="bearish">–</span></button>
    </div>
    <a class="miq-community-pulse-link" href="community<?php echo $miq_pulse_context_type === 'stock' && $miq_pulse_context_key !== 'site' ? '?code=' . rawurlencode($miq_pulse_context_key) : ''; ?>">See published ideas</a>
    <form class="miq-community-pulse-explanation" data-pulse-explanation hidden>
        <label for="miq-community-pulse-explanation-input">Why? Add an optional one-sentence explanation.</label>
        <div class="miq-community-pulse-explanation-row">
            <input id="miq-community-pulse-explanation-input" type="text" maxlength="280" data-pulse-explanation-input placeholder="What supports your view?">
            <button type="submit" data-pulse-explanation-submit>Submit for review</button>
        </div>
        <span class="miq-community-pulse-explanation-status" data-pulse-explanation-status aria-live="polite"></span>
    </form>
</section>
