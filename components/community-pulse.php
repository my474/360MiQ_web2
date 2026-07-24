<?php
$miq_pulse_context_type = isset($miq_account_context_type) ? $miq_account_context_type : 'site';
$miq_pulse_context_key = isset($miq_account_context_key) && $miq_account_context_key !== '' ? $miq_account_context_key : 'site';
?>
<section id="miq-community-pulse" class="miq-community-pulse" data-context-type="<?php echo htmlspecialchars($miq_pulse_context_type, ENT_QUOTES, 'UTF-8'); ?>" data-context-key="<?php echo htmlspecialchars($miq_pulse_context_key, ENT_QUOTES, 'UTF-8'); ?>" aria-labelledby="miq-community-pulse-title">
    <div class="miq-community-pulse-copy">
        <span class="miq-community-pulse-kicker">Community pulse</span>
        <h2 id="miq-community-pulse-title">What is your view?</h2>
        <p>Share a one-tap, time-bound market opinion. Published explanations are moderated.</p>
    </div>
    <div class="miq-community-pulse-actions" role="group" aria-label="Community sentiment">
        <button type="button" data-pulse-vote="bullish"><i class="fas fa-arrow-up"></i> Bullish <span data-count="bullish">–</span></button>
        <button type="button" data-pulse-vote="neutral"><i class="fas fa-minus"></i> Neutral <span data-count="neutral">–</span></button>
        <button type="button" data-pulse-vote="bearish"><i class="fas fa-arrow-down"></i> Bearish <span data-count="bearish">–</span></button>
    </div>
    <a class="miq-community-pulse-link" href="/community<?php echo $miq_pulse_context_type === 'stock' && $miq_pulse_context_key !== 'site' ? '?code=' . rawurlencode($miq_pulse_context_key) : ''; ?>">See published ideas</a>
</section>
