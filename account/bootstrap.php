<?php
if (!defined('MIQ_ACCOUNT_BOOTSTRAPPED')) {
    define('MIQ_ACCOUNT_BOOTSTRAPPED', true);
    require_once __DIR__ . '/auth.php';
    miq_account_bootstrap();
}
