<?php

if (!function_exists('miq_account_notification_preference_defaults')) {
    function miq_account_notification_preference_defaults()
    {
        return array(
            'price_alerts' => true,
            'community_replies' => false,
            'moderation' => true,
        );
    }
}

if (!function_exists('miq_account_notification_delivery_preferences')) {
    function miq_account_notification_delivery_preferences($user_id)
    {
        $defaults = miq_account_notification_preference_defaults();
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return $defaults;
        }
        $table = miq_account_table('notification_preferences');
        $row = miq_account_fetch_one(miq_account_query(
            "SELECT price_alerts_enabled, community_replies_enabled, moderation_enabled FROM {$table} WHERE user_id = ? LIMIT 1",
            'i',
            array($user_id)
        ));
        if (!$row) {
            return $defaults;
        }
        return array(
            'price_alerts' => (int) $row['price_alerts_enabled'] === 1,
            'community_replies' => (int) $row['community_replies_enabled'] === 1,
            'moderation' => (int) $row['moderation_enabled'] === 1,
        );
    }

}

if (!function_exists('miq_account_notification_preferences')) {
    function miq_account_notification_preferences($user_id)
    {
        try {
            return miq_account_notification_delivery_preferences($user_id);
        } catch (Throwable $error) {
            error_log('360MiQ notification preference read failure: ' . $error->getMessage());
            return miq_account_notification_preference_defaults();
        }
    }
}

if (!function_exists('miq_account_save_notification_preferences')) {
    function miq_account_save_notification_preferences($user_id, $values)
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return miq_account_notification_preference_defaults();
        }

        $current = miq_account_notification_delivery_preferences($user_id);
        $values = is_array($values) ? $values : array();
        foreach (array_keys($current) as $key) {
            if (array_key_exists($key, $values)) {
                $current[$key] = filter_var($values[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $table = miq_account_table('notification_preferences');
        miq_account_query(
            "INSERT INTO {$table} (user_id, price_alerts_enabled, community_replies_enabled, moderation_enabled, updated_at) VALUES (?, ?, ?, ?, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE price_alerts_enabled = VALUES(price_alerts_enabled), community_replies_enabled = VALUES(community_replies_enabled), moderation_enabled = VALUES(moderation_enabled), updated_at = UTC_TIMESTAMP()",
            'iiii',
            array(
                $user_id,
                $current['price_alerts'] ? 1 : 0,
                $current['community_replies'] ? 1 : 0,
                $current['moderation'] ? 1 : 0,
            )
        )->close();
        return $current;
    }
}

if (!function_exists('miq_account_notification_preference_key')) {
    function miq_account_notification_preference_key($type)
    {
        $type = strtolower(trim((string) $type));
        if ($type === 'price_alert' || strpos($type, 'price_alert') === 0) {
            return 'price_alerts';
        }
        if (strpos($type, 'community_reply') === 0) {
            return 'community_replies';
        }
        if (strpos($type, 'community_moderation') === 0 || strpos($type, 'moderation') === 0) {
            return 'moderation';
        }
        return null;
    }
}

if (!function_exists('miq_account_notification_web_config')) {
    function miq_account_notification_web_config()
    {
        $config = miq_account_config();
        $service_account = function_exists('miq_account_fcm_service_account')
            ? miq_account_fcm_service_account()
            : null;
        $project_id = is_array($service_account)
            ? (string) ($service_account['project_id'] ?? '')
            : (string) $config['fcm_project_id'];
        $firebase = array(
            'apiKey' => (string) $config['fcm_web_api_key'],
            'authDomain' => (string) $config['fcm_web_auth_domain'],
            'projectId' => $project_id,
            'storageBucket' => (string) $config['fcm_web_storage_bucket'],
            'messagingSenderId' => (string) $config['fcm_web_messaging_sender_id'],
            'appId' => (string) $config['fcm_web_app_id'],
        );
        $delivery_enabled = $service_account !== null;
        $enabled = $delivery_enabled
            && $firebase['apiKey'] !== ''
            && $firebase['authDomain'] !== ''
            && $firebase['projectId'] !== ''
            && $firebase['storageBucket'] !== ''
            && $firebase['messagingSenderId'] !== ''
            && $firebase['appId'] !== ''
            && (string) $config['fcm_web_vapid_key'] !== '';

        return array(
            'enabled' => $enabled,
            'deliveryEnabled' => $delivery_enabled,
            'sdkVersion' => (string) $config['fcm_web_sdk_version'],
            'firebase' => $firebase,
            'vapidKey' => (string) $config['fcm_web_vapid_key'],
            'serviceWorkerUrl' => rtrim((string) $config['base_url'], '/') . '/service-worker.js',
        );
    }
}

if (!function_exists('miq_account_notification_device_rows')) {
    function miq_account_notification_device_rows($user_id)
    {
        try {
            $table = miq_account_table('notification_devices');
            $rows = miq_account_fetch_all(miq_account_query(
                "SELECT id, channel, label, app_version, user_agent, enabled, last_seen_at, created_at, updated_at FROM {$table} WHERE user_id = ? AND enabled = 1 ORDER BY updated_at DESC, id DESC LIMIT 50",
                'i',
                array((int) $user_id)
            ));
            return array_map(function ($row) {
                return array(
                    'id' => (int) $row['id'],
                    'channel' => (string) $row['channel'],
                    'label' => (string) ($row['label'] ?? ''),
                    'app_version' => (string) ($row['app_version'] ?? ''),
                    'user_agent' => (string) ($row['user_agent'] ?? ''),
                    'enabled' => (int) $row['enabled'] === 1,
                    'last_seen_at' => $row['last_seen_at'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                );
            }, $rows);
        } catch (Throwable $error) {
            error_log('360MiQ notification device read failure: ' . $error->getMessage());
            return array();
        }
    }
}

if (!function_exists('miq_account_notification_settings_payload')) {
    function miq_account_notification_settings_payload($user_id)
    {
        return array(
            'preferences' => miq_account_notification_preferences($user_id),
            'devices' => miq_account_notification_device_rows($user_id),
            'web' => miq_account_notification_web_config(),
            'unread' => miq_account_unread_notification_count((int) $user_id),
        );
    }
}

if (!function_exists('miq_account_notification_clean_channel')) {
    function miq_account_notification_clean_channel($channel)
    {
        $channel = strtolower(trim((string) $channel));
        return in_array($channel, array('web', 'android'), true) ? $channel : '';
    }
}

if (!function_exists('miq_account_notification_clean_installation_id')) {
    function miq_account_notification_clean_installation_id($installation_id)
    {
        $installation_id = preg_replace('/[^A-Za-z0-9._:-]/', '', trim((string) $installation_id));
        $installation_id = substr((string) $installation_id, 0, 128);
        return strlen($installation_id) >= 16 ? $installation_id : '';
    }
}

if (!function_exists('miq_account_notification_tombstone_hash')) {
    function miq_account_notification_tombstone_hash($kind, $device_id, $previous_hash)
    {
        return hash('sha256', 'miq-' . (string) $kind . '-retired|' . (int) $device_id . '|' . (string) $previous_hash);
    }
}

if (!class_exists('MiqAccountNotificationDeviceLimitException')) {
    class MiqAccountNotificationDeviceLimitException extends RuntimeException
    {
    }
}

if (!function_exists('miq_account_release_notification_device_binding')) {
    function miq_account_release_notification_device_binding($device)
    {
        if (!$device || empty($device['id'])) {
            return;
        }
        $table = miq_account_table('notification_devices');
        $previous_hash = trim((string) ($device['token_hash'] ?? ''));
        $token_hash = miq_account_notification_tombstone_hash('token', $device['id'], $previous_hash);
        if ($previous_hash !== '') {
            // A provider response can arrive after this row was refreshed with
            // a rotated token. Never retire that newer binding.
            miq_account_query(
                "UPDATE {$table} SET device_token = '', token_hash = ?, installation_hash = NULL, session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND token_hash = ?",
                'sis',
                array($token_hash, (int) $device['id'], $previous_hash)
            )->close();
            return;
        }
        miq_account_query(
            "UPDATE {$table} SET device_token = '', token_hash = ?, installation_hash = NULL, session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE id = ?",
            'si',
            array($token_hash, (int) $device['id'])
        )->close();
    }
}

if (!function_exists('miq_account_register_notification_device')) {
    function miq_account_register_notification_device($user_id, $channel, $token, $metadata = array())
    {
        $user_id = (int) $user_id;
        $channel = miq_account_notification_clean_channel($channel);
        $token = trim((string) $token);
        if ($user_id <= 0 || $channel === '' || strlen($token) < 20 || strlen($token) > 4096) {
            throw new InvalidArgumentException('A valid notification channel and device token are required.');
        }
        $metadata = is_array($metadata) ? $metadata : array();
        $raw_label = trim((string) ($metadata['label'] ?? ''));
        $label = miq_account_notification_text_limit($raw_label, 120);
        $app_version = miq_account_notification_text_limit($metadata['app_version'] ?? '', 40);
        $user_agent = miq_account_notification_text_limit($metadata['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''), 500);
        $installation_id = miq_account_notification_clean_installation_id($metadata['installation_id'] ?? '');
        $installation_hash = $installation_id === '' ? null : hash('sha256', $installation_id);
        $token_hash = hash('sha256', $token);
        $session_hash = function_exists('miq_account_session_hash') ? miq_account_session_hash() : '';
        $session_version = max(1, (int) ($_SESSION['miq_account_session_version'] ?? 1));
        $table = miq_account_table('notification_devices');
        $sessions_table = miq_account_table('sessions');
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            if ($session_hash === '') {
                throw new RuntimeException('The notification device could not be bound to an authenticated session.');
            }
            $active_session = miq_account_fetch_one(miq_account_query(
                "SELECT id FROM {$sessions_table} WHERE user_id = ? AND session_hash = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1 FOR UPDATE",
                'is',
                array($user_id, $session_hash)
            ));
            if (!$active_session) {
                throw new RuntimeException('The notification device could not be bound to an active session.');
            }
            // Retire expired-session rows before enforcing the per-user cap so
            // stale devices cannot block a legitimate new registration.
            miq_account_query(
                "UPDATE {$table} device LEFT JOIN {$sessions_table} account_session ON account_session.user_id = device.user_id AND account_session.session_hash = device.session_hash AND account_session.expires_at > UTC_TIMESTAMP() SET device.device_token = '', device.session_hash = NULL, device.enabled = 0, device.updated_at = UTC_TIMESTAMP() WHERE device.user_id = ? AND device.enabled = 1 AND account_session.id IS NULL",
                'i',
                array($user_id)
            )->close();
            $where = 'channel = ? AND (token_hash = ?';
            $types = 'ss';
            $params = array($channel, $token_hash);
            if ($installation_hash !== null) {
                $where .= ' OR installation_hash = ?';
                $types .= 's';
                $params[] = $installation_hash;
            }
            $where .= ')';
            $candidates = miq_account_fetch_all(miq_account_query(
                "SELECT id, user_id, token_hash, installation_hash, enabled FROM {$table} WHERE {$where} FOR UPDATE",
                $types,
                $params
            ));

            $selected = null;
            foreach ($candidates as $candidate) {
                $same_user = (int) $candidate['user_id'] === $user_id;
                $installation_match = $installation_hash !== null
                    && hash_equals((string) $candidate['installation_hash'], $installation_hash);
                $token_match = hash_equals((string) $candidate['token_hash'], $token_hash);
                if ($same_user && $selected === null && ($installation_match || $token_match)) {
                    $selected = $candidate;
                    continue;
                }
                miq_account_release_notification_device_binding($candidate);
            }

            if (!$selected || (int) ($selected['enabled'] ?? 0) !== 1) {
                $owned_devices = miq_account_fetch_all(miq_account_query(
                    "SELECT id FROM {$table} WHERE user_id = ? AND enabled = 1 FOR UPDATE",
                    'i',
                    array($user_id)
                ));
                if (count($owned_devices) >= (int) miq_account_config()['fcm_max_devices_per_user']) {
                    throw new MiqAccountNotificationDeviceLimitException('The active push-device limit was reached. Remove an old device and try again.');
                }
            }

            if ($selected) {
                miq_account_query(
                    "UPDATE {$table} SET device_token = ?, token_hash = ?, installation_hash = ?, session_hash = ?, session_version = ?, label = ?, app_version = ?, user_agent = ?, enabled = 1, last_seen_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                    'ssssisssii',
                    array($token, $token_hash, $installation_hash, $session_hash, $session_version, $label, $app_version, $user_agent, (int) $selected['id'], $user_id)
                )->close();
                $device_id = (int) $selected['id'];
            } else {
                miq_account_query(
                    "INSERT INTO {$table} (user_id, channel, device_token, token_hash, installation_hash, session_hash, session_version, label, app_version, user_agent, enabled, last_seen_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())",
                    'isssssisss',
                    array($user_id, $channel, $token, $token_hash, $installation_hash, $session_hash, $session_version, $label, $app_version, $user_agent)
                )->close();
                $device_id = (int) $db->insert_id;
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }

        $row = miq_account_fetch_one(miq_account_query(
            "SELECT id, channel, label, app_version, last_seen_at FROM {$table} WHERE id = ? AND user_id = ? LIMIT 1",
            'ii',
            array($device_id, $user_id)
        ));
        return $row ? array(
            'id' => (int) $row['id'],
            'channel' => (string) $row['channel'],
            'label' => (string) ($row['label'] ?? ''),
            'app_version' => (string) ($row['app_version'] ?? ''),
            'last_seen_at' => $row['last_seen_at'],
        ) : null;
    }
}

if (!function_exists('miq_account_unregister_notification_device')) {
    function miq_account_unregister_notification_device($user_id, $device_id = 0, $channel = '', $token = '', $installation_id = '')
    {
        $user_id = (int) $user_id;
        $device_id = (int) $device_id;
        $channel = miq_account_notification_clean_channel($channel);
        $token = trim((string) $token);
        $installation_id = miq_account_notification_clean_installation_id($installation_id);
        $table = miq_account_table('notification_devices');
        if ($device_id > 0) {
            $statement = miq_account_query(
                "UPDATE {$table} SET device_token = '', session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                'ii',
                array($device_id, $user_id)
            );
        } elseif ($installation_id !== '' && $token !== '' && $channel !== '') {
            $statement = miq_account_query(
                "UPDATE {$table} SET device_token = '', session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE user_id = ? AND channel = ? AND (installation_hash = ? OR token_hash = ?)",
                'isss',
                array($user_id, $channel, hash('sha256', $installation_id), hash('sha256', $token))
            );
        } elseif ($installation_id !== '' && $channel !== '') {
            $statement = miq_account_query(
                "UPDATE {$table} SET device_token = '', session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE user_id = ? AND channel = ? AND installation_hash = ?",
                'iss',
                array($user_id, $channel, hash('sha256', $installation_id))
            );
        } elseif ($token !== '' && $channel !== '') {
            $statement = miq_account_query(
                "UPDATE {$table} SET device_token = '', session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE user_id = ? AND channel = ? AND token_hash = ?",
                'iss',
                array($user_id, $channel, hash('sha256', $token))
            );
        } else {
            return false;
        }
        $changed = $statement->affected_rows > 0;
        $statement->close();
        return $changed;
    }
}

if (!function_exists('miq_account_unregister_notification_session')) {
    function miq_account_unregister_notification_session($user_id, $session_hash)
    {
        $user_id = (int) $user_id;
        $session_hash = trim((string) $session_hash);
        if ($user_id <= 0 || $session_hash === '') {
            return 0;
        }
        $table = miq_account_table('notification_devices');
        $statement = miq_account_query(
            "UPDATE {$table} SET device_token = '', session_hash = NULL, enabled = 0, updated_at = UTC_TIMESTAMP() WHERE user_id = ? AND session_hash = ? AND enabled = 1",
            'is',
            array($user_id, $session_hash)
        );
        $changed = (int) $statement->affected_rows;
        $statement->close();
        return $changed;
    }
}

if (!function_exists('miq_account_notification_absolute_url')) {
    function miq_account_notification_absolute_url($link_url)
    {
        $config = miq_account_config();
        $base_url = rtrim((string) $config['base_url'], '/');
        $link_url = trim((string) $link_url);
        if ($link_url === '') {
            return $base_url . '/workspace?tab=notifications';
        }
        if (preg_match('/^https?:\/\//i', $link_url)) {
            $base_parts = parse_url($base_url);
            $link_parts = parse_url($link_url);
            $base_scheme = is_array($base_parts) ? strtolower((string) ($base_parts['scheme'] ?? '')) : '';
            $link_scheme = is_array($link_parts) ? strtolower((string) ($link_parts['scheme'] ?? '')) : '';
            $base_host = is_array($base_parts) ? strtolower((string) ($base_parts['host'] ?? '')) : '';
            $link_host = is_array($link_parts) ? strtolower((string) ($link_parts['host'] ?? '')) : '';
            $base_port = is_array($base_parts) ? (int) ($base_parts['port'] ?? ($base_scheme === 'https' ? 443 : 80)) : 0;
            $link_port = is_array($link_parts) ? (int) ($link_parts['port'] ?? ($link_scheme === 'https' ? 443 : 80)) : 0;
            $has_credentials = is_array($link_parts) && (isset($link_parts['user']) || isset($link_parts['pass']));
            if (!$has_credentials && $base_scheme === $link_scheme && $base_host !== '' && $base_host === $link_host && $base_port === $link_port) {
                return $link_url;
            }
            return $base_url . '/workspace?tab=notifications';
        }
        return $base_url . '/' . ltrim($link_url, '/');
    }
}

if (!class_exists('MiqAccountFcmDeliveryException')) {
    class MiqAccountFcmDeliveryException extends RuntimeException
    {
        public $permanent;
        public $retryable;
        public $error_code;
        public $http_status;
        public $retry_after_seconds;

        public function __construct($message, $permanent = false, $retryable = false, $error_code = '', $http_status = 0, $retry_after_seconds = 0)
        {
            parent::__construct($message);
            $this->permanent = (bool) $permanent;
            $this->retryable = (bool) $retryable;
            $this->error_code = substr(trim((string) $error_code), 0, 80);
            $this->http_status = max(0, (int) $http_status);
            $this->retry_after_seconds = max(0, (int) $retry_after_seconds);
        }
    }
}

if (!function_exists('miq_account_fcm_service_account')) {
    function miq_account_fcm_service_account()
    {
        static $credentials = false;
        if ($credentials !== false) {
            return $credentials;
        }
        $config = miq_account_config();
        $json = trim((string) $config['fcm_service_account_json']);
        $credential_file = trim((string) ($config['fcm_service_account_file'] ?? ''));
        if ($json === '' && $credential_file !== '') {
            $resolved_file = realpath($credential_file);
            if ($resolved_file !== false && is_file($resolved_file) && is_readable($resolved_file)) {
                $file_size = @filesize($resolved_file);
                if ($file_size !== false && $file_size > 0 && $file_size <= 65536) {
                    $file_json = @file_get_contents($resolved_file);
                    if (is_string($file_json)) {
                        $json = trim($file_json);
                    }
                }
            }
        }
        $decoded = $json !== '' ? json_decode($json, true) : array();
        $decoded = is_array($decoded) ? $decoded : array();
        $private_key = (string) ($decoded['private_key'] ?? $config['fcm_private_key']);
        $private_key = str_replace('\\n', "\n", $private_key);
        $project_id = (string) ($decoded['project_id'] ?? $config['fcm_project_id']);
        $client_email = (string) ($decoded['client_email'] ?? $config['fcm_client_email']);
        if ($project_id === '' || $client_email === '' || $private_key === '') {
            $credentials = null;
            return null;
        }
        $credentials = array(
            'project_id' => $project_id,
            'client_email' => $client_email,
            'private_key' => $private_key,
        );
        return $credentials;
    }
}

if (!function_exists('miq_account_fcm_base64url')) {
    function miq_account_fcm_base64url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

if (!function_exists('miq_account_fcm_http_post')) {
    function miq_account_fcm_http_post($url, $headers, $body)
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for FCM delivery.');
        }
        $handle = curl_init($url);
        $response_headers = array();
        curl_setopt_array($handle, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => function ($curl, $header_line) use (&$response_headers) {
                $length = strlen($header_line);
                $separator = strpos($header_line, ':');
                if ($separator === false) {
                    return $length;
                }
                $name = strtolower(trim(substr($header_line, 0, $separator)));
                $value = trim(substr($header_line, $separator + 1));
                if ($name !== '') {
                    $response_headers[$name] = $value;
                }
                return $length;
            },
        ));
        $response_body = curl_exec($handle);
        $curl_error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($response_body === false) {
            throw new RuntimeException('FCM request failed: ' . ($curl_error !== '' ? $curl_error : 'unknown network error'));
        }
        return array('status' => $status, 'body' => (string) $response_body, 'headers' => $response_headers);
    }
}

if (!function_exists('miq_account_fcm_access_token')) {
    function miq_account_fcm_access_token($credentials, $force_refresh = false)
    {
        static $tokens = array();
        $cache_key = hash('sha256', (string) $credentials['project_id'] . '|' . (string) $credentials['client_email']);
        if ($force_refresh) {
            unset($tokens[$cache_key]);
        }
        if (!empty($tokens[$cache_key]['token']) && (int) $tokens[$cache_key]['expires_at'] > time() + 60) {
            return $tokens[$cache_key]['token'];
        }
        if (!function_exists('openssl_sign')) {
            throw new RuntimeException('The PHP OpenSSL extension is required for FCM delivery.');
        }

        $issued_at = time();
        $header = miq_account_fcm_base64url(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
        $claims = miq_account_fcm_base64url(json_encode(array(
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issued_at,
            'exp' => $issued_at + 3600,
        )));
        $unsigned = $header . '.' . $claims;
        $signature = '';
        if (!openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('FCM service-account signing failed.');
        }
        $jwt = $unsigned . '.' . miq_account_fcm_base64url($signature);
        $response = miq_account_fcm_http_post(
            'https://oauth2.googleapis.com/token',
            array('Content-Type: application/x-www-form-urlencoded'),
            http_build_query(array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ), '', '&')
        );
        $payload = json_decode($response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($payload) || empty($payload['access_token'])) {
            throw new RuntimeException('FCM OAuth token request failed.');
        }
        $tokens[$cache_key] = array(
            'token' => (string) $payload['access_token'],
            'expires_at' => $issued_at + max(300, (int) ($payload['expires_in'] ?? 3600)),
        );
        return $tokens[$cache_key]['token'];
    }
}

if (!function_exists('miq_account_fcm_retry_after_seconds')) {
    function miq_account_fcm_retry_after_seconds($headers)
    {
        $value = is_array($headers) ? trim((string) ($headers['retry-after'] ?? '')) : '';
        if ($value === '') {
            return 0;
        }
        if (ctype_digit($value)) {
            return min(86400, max(1, (int) $value));
        }
        $timestamp = strtotime($value);
        return $timestamp === false ? 0 : min(86400, max(1, $timestamp - time()));
    }
}

if (!function_exists('miq_account_fcm_error_info')) {
    function miq_account_fcm_error_info($response)
    {
        $http_status = max(0, (int) ($response['status'] ?? 0));
        $payload = json_decode((string) ($response['body'] ?? ''), true);
        $error = is_array($payload) && isset($payload['error']) && is_array($payload['error']) ? $payload['error'] : array();
        $status_name = strtoupper(trim((string) ($error['status'] ?? '')));
        $fcm_code = '';
        foreach ((array) ($error['details'] ?? array()) as $detail) {
            if (!is_array($detail)) {
                continue;
            }
            $detail_type = (string) ($detail['@type'] ?? '');
            if ($detail_type === 'type.googleapis.com/google.firebase.fcm.v1.FcmError' && !empty($detail['errorCode'])) {
                $fcm_code = strtoupper(trim((string) $detail['errorCode']));
                break;
            }
        }
        $error_code = $fcm_code !== '' ? $fcm_code : ($status_name !== '' ? $status_name : 'HTTP_' . $http_status);
        $retryable = in_array($http_status, array(429, 500, 502, 503, 504), true)
            || in_array($status_name, array('RESOURCE_EXHAUSTED', 'INTERNAL', 'UNAVAILABLE'), true);
        $message = trim((string) ($error['message'] ?? 'FCM rejected the message.'));
        $message = preg_replace('/\s+/', ' ', $message);
        return array(
            // Only the structured FCM token error revokes a device. Generic
            // HTTP 404/INVALID_ARGUMENT responses may describe project or payload errors.
            'permanent' => $fcm_code === 'UNREGISTERED',
            'retryable' => $retryable,
            'error_code' => substr($error_code, 0, 80),
            'http_status' => $http_status,
            'retry_after_seconds' => miq_account_fcm_retry_after_seconds($response['headers'] ?? array()),
            'message' => substr($message !== '' ? $message : 'FCM rejected the message.', 0, 300),
        );
    }
}

if (!function_exists('miq_account_notification_text_limit')) {
    function miq_account_notification_text_limit($value, $maximum)
    {
        $value = trim((string) $value);
        $maximum = max(0, (int) $maximum);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maximum, 'UTF-8');
        }
        $characters = array();
        if (preg_match_all('/./us', $value, $characters) !== false) {
            return implode('', array_slice($characters[0], 0, $maximum));
        }
        $clean = function_exists('iconv') ? @iconv('UTF-8', 'UTF-8//IGNORE', $value) : false;
        $clean = is_string($clean) ? $clean : preg_replace('/[^\x00-\x7F]/', '', $value);
        return substr((string) $clean, 0, $maximum);
    }
}

if (!function_exists('miq_account_fcm_send')) {
    function miq_account_fcm_send($device_token, $notification_id, $type, $title, $message, $link_url, $unread_count = null)
    {
        $credentials = miq_account_fcm_service_account();
        if (!$credentials) {
            return null;
        }
        $url = miq_account_notification_absolute_url($link_url);
        $data = array(
            'notification_id' => (string) ((int) $notification_id),
            'notification_type' => (string) $type,
            'link_url' => $url,
            'unread_count' => (string) ($unread_count === null ? 0 : max(0, (int) $unread_count)),
        );
        $payload = array(
            'message' => array(
                'token' => (string) $device_token,
                'notification' => array(
                    'title' => miq_account_notification_text_limit($title, 160),
                    'body' => miq_account_notification_text_limit($message, 500),
                ),
                'data' => $data,
                'android' => array(
                    'priority' => 'HIGH',
                    'notification' => array('channel_id' => 'miq_notifications'),
                ),
                'webpush' => array(
                    'headers' => array('Urgency' => 'high'),
                    'notification' => array(
                        'title' => miq_account_notification_text_limit($title, 160),
                        'body' => miq_account_notification_text_limit($message, 500),
                        'icon' => rtrim((string) miq_account_config()['base_url'], '/') . '/assets/img/360Logo_192.png',
                        'badge' => rtrim((string) miq_account_config()['base_url'], '/') . '/assets/img/360Logo_192.png',
                        'tag' => 'miq-notification-' . (int) $notification_id,
                        'data' => array('url' => $url, 'notification_id' => (string) ((int) $notification_id)),
                    ),
                    'fcm_options' => array('link' => $url),
                ),
            ),
        );
        $encoded_payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded_payload === false) {
            throw new MiqAccountFcmDeliveryException('The FCM payload could not be encoded.', false, false, 'PAYLOAD_ENCODING', 0, 0);
        }
        $endpoint = 'https://fcm.googleapis.com/v1/projects/' . rawurlencode($credentials['project_id']) . '/messages:send';
        $send_request = function ($force_refresh = false) use ($endpoint, $credentials, $encoded_payload) {
            return miq_account_fcm_http_post(
                $endpoint,
                array(
                    'Authorization: Bearer ' . miq_account_fcm_access_token($credentials, $force_refresh),
                    'Content-Type: application/json; charset=UTF-8',
                ),
                $encoded_payload
            );
        };
        $response = $send_request(false);
        if ((int) $response['status'] === 401) {
            $response = $send_request(true);
        }
        $response_payload = json_decode($response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($response_payload) || empty($response_payload['name'])) {
            $info = miq_account_fcm_error_info($response);
            throw new MiqAccountFcmDeliveryException(
                'FCM send failed (' . $info['error_code'] . '): ' . $info['message'],
                $info['permanent'],
                $info['retryable'],
                $info['error_code'],
                $info['http_status'],
                $info['retry_after_seconds']
            );
        }
        return (string) $response_payload['name'];
    }
}

if (!function_exists('miq_account_reset_notification_deliveries')) {
    function miq_account_reset_notification_deliveries($notification_id)
    {
        try {
            $table = miq_account_table('notification_deliveries');
            miq_account_query(
                "UPDATE {$table} SET requeue_requested = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), 1, 0), attempt_count = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), attempt_count, 0), next_attempt_at = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), next_attempt_at, UTC_TIMESTAMP()), lease_token = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), lease_token, NULL), lease_expires_at = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), lease_expires_at, NULL), provider_message_id = NULL, error_message = NULL, error_code = NULL, http_status = NULL, attempted_at = NULL, delivered_at = NULL, updated_at = UTC_TIMESTAMP(), status = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), 'processing', 'pending') WHERE notification_id = ?",
                'i',
                array((int) $notification_id)
            )->close();
        } catch (Throwable $error) {
            // Delivery history is best-effort and must not prevent an in-app notification.
        }
    }
}

if (!function_exists('miq_account_enqueue_notification')) {
    function miq_account_enqueue_notification($notification_id, $user_id, $type)
    {
        $notification_id = (int) $notification_id;
        $user_id = (int) $user_id;
        if ($notification_id <= 0 || $user_id <= 0) {
            return 0;
        }
        $preference_key = miq_account_notification_preference_key($type);
        if ($preference_key === null) {
            return 0;
        }
        $preferences = miq_account_notification_delivery_preferences($user_id);
        $deliveries_table = miq_account_table('notification_deliveries');
        if (empty($preferences[$preference_key])) {
            miq_account_query(
                "UPDATE {$deliveries_table} SET status = 'skipped', lease_token = NULL, lease_expires_at = NULL, error_message = 'Disabled by notification preference.', error_code = 'PREFERENCE_DISABLED', updated_at = UTC_TIMESTAMP() WHERE notification_id = ? AND status IN ('pending', 'retry')",
                'i',
                array($notification_id)
            )->close();
            return 0;
        }

        $devices_table = miq_account_table('notification_devices');
        $users_table = miq_account_table('users');
        $sessions_table = miq_account_table('sessions');
        $devices = miq_account_fetch_all(miq_account_query(
            "SELECT device.id FROM {$devices_table} device INNER JOIN {$users_table} account_user ON account_user.id = device.user_id INNER JOIN {$sessions_table} account_session ON account_session.user_id = device.user_id AND account_session.session_hash = device.session_hash AND account_session.expires_at > UTC_TIMESTAMP() WHERE device.user_id = ? AND device.enabled = 1 AND device.device_token <> '' AND device.session_version = account_user.session_version AND account_user.status = 'active' ORDER BY device.updated_at DESC, device.id DESC LIMIT " . (int) miq_account_config()['fcm_max_devices_per_notification'],
            'i',
            array($user_id)
        ));
        foreach ($devices as $device) {
            $device_id = (int) $device['id'];
            miq_account_query(
                "INSERT INTO {$deliveries_table} (user_id, notification_id, device_id, status, attempt_count, next_attempt_at, requeue_requested, created_at, updated_at) VALUES (?, ?, ?, 'pending', 0, UTC_TIMESTAMP(), 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), requeue_requested = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), 1, 0), attempt_count = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), attempt_count, 0), next_attempt_at = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), next_attempt_at, UTC_TIMESTAMP()), lease_token = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), lease_token, NULL), lease_expires_at = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), lease_expires_at, NULL), provider_message_id = NULL, error_message = NULL, error_code = NULL, http_status = NULL, attempted_at = NULL, delivered_at = NULL, updated_at = UTC_TIMESTAMP(), status = IF(status = 'processing' AND lease_expires_at > UTC_TIMESTAMP(), 'processing', 'pending')",
                'iii',
                array($user_id, $notification_id, $device_id)
            )->close();
        }
        return count($devices);
    }
}

if (!function_exists('miq_account_claim_notification_delivery')) {
    function miq_account_claim_notification_delivery()
    {
        $config = miq_account_config();
        $lease_seconds = (int) $config['fcm_delivery_lease_seconds'];
        $deliveries = miq_account_table('notification_deliveries');
        $notifications = miq_account_table('notifications');
        $devices = miq_account_table('notification_devices');
        $users = miq_account_table('users');
        $sessions = miq_account_table('sessions');
        $db = miq_account_db();
        $db->begin_transaction();
        try {
            $row = miq_account_fetch_one(miq_account_query(
                "SELECT delivery.id AS delivery_id, delivery.user_id, delivery.attempt_count, notification.id AS notification_id, notification.notification_type, notification.title, notification.message, notification.link_url, notification.read_at, device.id AS device_id, device.device_token, device.token_hash, device.installation_hash, device.enabled AS device_enabled, device.session_version AS device_session_version, account_user.session_version AS user_session_version, account_user.status AS user_status, account_session.id AS session_id FROM {$deliveries} delivery INNER JOIN {$notifications} notification ON notification.id = delivery.notification_id AND notification.user_id = delivery.user_id LEFT JOIN {$devices} device ON device.id = delivery.device_id AND device.user_id = delivery.user_id LEFT JOIN {$users} account_user ON account_user.id = delivery.user_id LEFT JOIN {$sessions} account_session ON account_session.user_id = delivery.user_id AND account_session.session_hash = device.session_hash AND account_session.expires_at > UTC_TIMESTAMP() WHERE ((delivery.status IN ('pending', 'retry') AND (delivery.next_attempt_at IS NULL OR delivery.next_attempt_at <= UTC_TIMESTAMP())) OR (delivery.status = 'processing' AND (delivery.lease_expires_at IS NULL OR delivery.lease_expires_at <= UTC_TIMESTAMP()))) ORDER BY COALESCE(delivery.next_attempt_at, delivery.created_at), delivery.id LIMIT 1 FOR UPDATE"
            ));
            if (!$row) {
                $db->commit();
                return null;
            }
            $lease_token = bin2hex(random_bytes(32));
            miq_account_query(
                "UPDATE {$deliveries} SET status = 'processing', attempt_count = attempt_count + 1, requeue_requested = 0, lease_token = ?, lease_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL {$lease_seconds} SECOND), attempted_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ?",
                'si',
                array($lease_token, (int) $row['delivery_id'])
            )->close();
            $db->commit();
            $row['lease_token'] = $lease_token;
            $row['attempt_count'] = (int) $row['attempt_count'] + 1;
            return $row;
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
}

if (!function_exists('miq_account_notification_retry_delay')) {
    function miq_account_notification_retry_delay($attempt_count, $retry_after_seconds = 0)
    {
        $config = miq_account_config();
        $attempt_count = max(1, (int) $attempt_count);
        $base = (int) $config['fcm_retry_base_seconds'];
        $maximum = (int) $config['fcm_retry_max_seconds'];
        $exponential = min($maximum, (int) ($base * pow(2, min(16, $attempt_count - 1))));
        try {
            $jitter = random_int(0, max(1, (int) floor($exponential / 4)));
        } catch (Throwable $error) {
            $jitter = mt_rand(0, max(1, (int) floor($exponential / 4)));
        }
        return min(86400, max($exponential + $jitter, (int) $retry_after_seconds));
    }
}

if (!function_exists('miq_account_complete_notification_delivery')) {
    function miq_account_complete_notification_delivery($delivery_id, $lease_token, $provider_id)
    {
        $table = miq_account_table('notification_deliveries');
        miq_account_query(
            "UPDATE {$table} SET status = IF(requeue_requested = 1, 'pending', 'sent'), attempt_count = IF(requeue_requested = 1, 0, attempt_count), next_attempt_at = IF(requeue_requested = 1, UTC_TIMESTAMP(), NULL), provider_message_id = IF(requeue_requested = 1, NULL, ?), error_message = NULL, error_code = NULL, http_status = NULL, lease_token = NULL, lease_expires_at = NULL, delivered_at = IF(requeue_requested = 1, NULL, UTC_TIMESTAMP()), requeue_requested = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND lease_token = ? AND status = 'processing'",
            'sis',
            array((string) $provider_id, (int) $delivery_id, (string) $lease_token)
        )->close();
    }
}

if (!function_exists('miq_account_skip_notification_delivery')) {
    function miq_account_skip_notification_delivery($delivery_id, $lease_token, $code, $message)
    {
        $table = miq_account_table('notification_deliveries');
        miq_account_query(
            "UPDATE {$table} SET status = IF(requeue_requested = 1, 'pending', 'skipped'), attempt_count = IF(requeue_requested = 1, 0, attempt_count), error_message = IF(requeue_requested = 1, NULL, ?), error_code = IF(requeue_requested = 1, NULL, ?), http_status = NULL, lease_token = NULL, lease_expires_at = NULL, next_attempt_at = IF(requeue_requested = 1, UTC_TIMESTAMP(), NULL), requeue_requested = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND lease_token = ? AND status = 'processing'",
            'ssis',
            array(substr((string) $message, 0, 500), substr((string) $code, 0, 80), (int) $delivery_id, (string) $lease_token)
        )->close();
    }
}

if (!function_exists('miq_account_fail_notification_delivery')) {
    function miq_account_fail_notification_delivery($delivery, $error, $retryable)
    {
        $table = miq_account_table('notification_deliveries');
        $attempt_count = (int) $delivery['attempt_count'];
        $maximum_attempts = (int) miq_account_config()['fcm_delivery_max_attempts'];
        $can_retry = (bool) $retryable && $attempt_count < $maximum_attempts;
        $error_code = $error instanceof MiqAccountFcmDeliveryException && $error->error_code !== ''
            ? $error->error_code
            : 'NETWORK_ERROR';
        $http_status = $error instanceof MiqAccountFcmDeliveryException ? (int) $error->http_status : 0;
        $retry_after = $error instanceof MiqAccountFcmDeliveryException ? (int) $error->retry_after_seconds : 0;
        $message = substr((string) $error->getMessage(), 0, 500);
        if ($can_retry) {
            $delay = miq_account_notification_retry_delay($attempt_count, $retry_after);
            miq_account_query(
                "UPDATE {$table} SET status = IF(requeue_requested = 1, 'pending', 'retry'), attempt_count = IF(requeue_requested = 1, 0, attempt_count), error_message = IF(requeue_requested = 1, NULL, ?), error_code = IF(requeue_requested = 1, NULL, ?), http_status = IF(requeue_requested = 1, NULL, NULLIF(?, 0)), lease_token = NULL, lease_expires_at = NULL, next_attempt_at = IF(requeue_requested = 1, UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL {$delay} SECOND)), requeue_requested = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND lease_token = ? AND status = 'processing'",
                'ssiis',
                array($message, $error_code, $http_status, (int) $delivery['delivery_id'], (string) $delivery['lease_token'])
            )->close();
            return 'retry';
        }
        miq_account_query(
            "UPDATE {$table} SET status = IF(requeue_requested = 1, 'pending', 'failed'), attempt_count = IF(requeue_requested = 1, 0, attempt_count), error_message = IF(requeue_requested = 1, NULL, ?), error_code = IF(requeue_requested = 1, NULL, ?), http_status = IF(requeue_requested = 1, NULL, NULLIF(?, 0)), lease_token = NULL, lease_expires_at = NULL, next_attempt_at = IF(requeue_requested = 1, UTC_TIMESTAMP(), NULL), requeue_requested = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND lease_token = ? AND status = 'processing'",
            'ssiis',
            array($message, $error_code, $http_status, (int) $delivery['delivery_id'], (string) $delivery['lease_token'])
        )->close();
        return 'failed';
    }
}

if (!function_exists('miq_account_process_notification_queue')) {
    function miq_account_process_notification_queue($limit = null)
    {
        $config = miq_account_config();
        $limit = $limit === null ? (int) $config['fcm_worker_batch_size'] : max(1, min(200, (int) $limit));
        $stats = array('configured' => (bool) miq_account_fcm_service_account(), 'claimed' => 0, 'sent' => 0, 'retry' => 0, 'failed' => 0, 'skipped' => 0);
        if (!$stats['configured']) {
            return $stats;
        }
        for ($index = 0; $index < $limit; $index++) {
            $delivery = miq_account_claim_notification_delivery();
            if (!$delivery) {
                break;
            }
            $stats['claimed']++;
            $preference_key = miq_account_notification_preference_key($delivery['notification_type']);
            try {
                $preferences = $preference_key === null
                    ? array()
                    : miq_account_notification_delivery_preferences((int) $delivery['user_id']);
            } catch (Throwable $error) {
                $outcome = miq_account_fail_notification_delivery($delivery, $error, true);
                $stats[$outcome]++;
                error_log('360MiQ notification preference lookup failure: ' . $error->getMessage());
                continue;
            }
            $valid_device = !empty($delivery['device_id'])
                && !empty($delivery['session_id'])
                && (int) $delivery['device_enabled'] === 1
                && trim((string) $delivery['device_token']) !== ''
                && (int) $delivery['device_session_version'] === (int) $delivery['user_session_version']
                && (string) $delivery['user_status'] === 'active';
            $notification_unread = empty($delivery['read_at']);
            if (!$notification_unread || !$valid_device || $preference_key === null || empty($preferences[$preference_key])) {
                $code = !$notification_unread
                    ? 'NOTIFICATION_READ'
                    : (!$valid_device ? 'DEVICE_INACTIVE' : ($preference_key === null ? 'UNSUPPORTED_TYPE' : 'PREFERENCE_DISABLED'));
                miq_account_skip_notification_delivery($delivery['delivery_id'], $delivery['lease_token'], $code, 'Notification delivery is no longer enabled.');
                $stats['skipped']++;
                continue;
            }

            try {
                $provider_id = miq_account_fcm_send(
                    $delivery['device_token'],
                    $delivery['notification_id'],
                    $delivery['notification_type'],
                    $delivery['title'],
                    $delivery['message'],
                    $delivery['link_url'],
                    miq_account_unread_notification_count((int) $delivery['user_id'])
                );
                miq_account_complete_notification_delivery($delivery['delivery_id'], $delivery['lease_token'], $provider_id);
                $stats['sent']++;
            } catch (MiqAccountFcmDeliveryException $error) {
                if ($error->permanent) {
                    miq_account_release_notification_device_binding(array(
                        'id' => (int) $delivery['device_id'],
                        'token_hash' => (string) $delivery['token_hash'],
                    ));
                }
                $outcome = miq_account_fail_notification_delivery($delivery, $error, $error->retryable);
                $stats[$outcome]++;
                error_log('360MiQ FCM delivery failure: ' . $error->getMessage());
            } catch (Throwable $error) {
                $outcome = miq_account_fail_notification_delivery($delivery, $error, true);
                $stats[$outcome]++;
                error_log('360MiQ FCM delivery failure: ' . $error->getMessage());
            }
        }
        return $stats;
    }
}

if (!function_exists('miq_account_dispatch_notification')) {
    function miq_account_dispatch_notification($notification_id, $user_id, $type, $title = '', $message = '', $link_url = '')
    {
        // Backward-compatible name: dispatch now means durable enqueue only.
        return miq_account_enqueue_notification($notification_id, $user_id, $type);
    }
}
