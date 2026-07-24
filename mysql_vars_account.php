<?php
/**
 * Dedicated account-database connection include.
 *
 * Deploy this file outside the public web root. Credentials should be
 * provided through the hosting environment rather than committed here.
 * account/db.php expects this file to provide a mysqli $connection object.
 */

$account_db_host = getenv('ACCOUNT_DB_HOST') ?: 'localhost';
$account_db_name = getenv('ACCOUNT_DB_NAME') ?: 'aamiqcom_accounts';
$account_db_user = getenv('ACCOUNT_DB_USER') ?: '';
$account_db_password = getenv('ACCOUNT_DB_PASSWORD') ?: '';
$account_db_port = (int) (getenv('ACCOUNT_DB_PORT') ?: 3306);

if ($account_db_user === '') {
    throw new RuntimeException('ACCOUNT_DB_USER is not configured.');
}

$connection = new mysqli(
    $account_db_host,
    $account_db_user,
    $account_db_password,
    $account_db_name,
    $account_db_port
);

if ($connection->connect_errno) {
    throw new RuntimeException('Unable to connect to the account database.');
}

$connection->set_charset('utf8mb4');
