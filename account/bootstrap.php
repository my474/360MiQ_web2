<?php
if (!defined('MIQ_ACCOUNT_BOOTSTRAPPED')) {
    define('MIQ_ACCOUNT_BOOTSTRAPPED', true);
    require_once __DIR__ . '/auth.php';
    require_once __DIR__ . '/productivity.php';
    require_once __DIR__ . '/lifecycle.php';
}
