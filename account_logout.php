<?php
require_once __DIR__ . '/account/bootstrap.php';
miq_account_logout(true);
header('Location: /');
exit;
