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

if (!function_exists('miq_account_notification_preferences')) {
    function miq_account_notification_preferences($user_id)
    {
        $defaults = miq_account_notification_preference_defaults();
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return $defaults;
        }

        try {
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
        } catch (Throwable $error) {
            error_log('360MiQ notification preference read failure: ' . $error->getMessage());
            return $defaults;
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

        $current = miq_account_notification_preferences($user_id);
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
        $firebase = array(
            'apiKey' => (string) $config['fcm_web_api_key'],
            'authDomain' => (string) $config['fcm_web_auth_domain'],
            'projectId' => (string) $config['fcm_project_id'],
            'storageBucket' => (string) $config['fcm_web_storage_bucket'],
            'messagingSenderId' => (string) $config['fcm_web_messaging_sender_id'],
            'appId' => (string) $config['fcm_web_app_id'],
        );
        $enabled = $firebase['apiKey'] !== ''
            && $firebase['authDomain'] !== ''
            && $firebase['projectId'] !== ''
            && $firebase['storageBucket'] !== ''
            && $firebase['messagingSenderId'] !== ''
            && $firebase['appId'] !== ''
            && (string) $config['fcm_web_vapid_key'] !== '';

        return array(
            'enabled' => $enabled,
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

if (!function_exists('miq_account_register_notification_device')) {
    function miq_account_register_notification_device($user_id, $channel, $token, $metadata = array())
    {
        $user_id = (int) $user_id;
        $channel = miq_account_notification_clean_channel($channel);
        $token = trim((string) $token);
        if ($user_id <= 0 || $channel === '' || $token === '') {
            throw new InvalidArgumentException('A valid notification channel and device token are required.');
        }
        if (function_exists('mb_substr')) {
            $token = mb_substr($token, 0, 4096, 'UTF-8');
        } else {
            $token = substr($token, 0, 4096);
        }
        $metadata = is_array($metadata) ? $metadata : array();
        $label = substr(trim((string) ($metadata['label'] ?? '')), 0, 120);
        $app_version = substr(trim((string) ($metadata['app_version'] ?? '')), 0, 40);
        $user_agent = substr(trim((string) ($metadata['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''))), 0, 500);
        $token_hash = hash('sha256', $token);
        $table = miq_account_table('notification_devices');
        miq_account_query(
            "INSERT INTO {$table} (user_id, channel, device_token, token_hash, label, app_version, user_agent, enabled, last_seen_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), device_token = VALUES(device_token), label = VALUES(label), app_version = VALUES(app_version), user_agent = VALUES(user_agent), enabled = 1, last_seen_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP()",
            'issssss',
            array($user_id, $channel, $token, $token_hash, $label, $app_version, $user_agent)
        )->close();

        $row = miq_account_fetch_one(miq_account_query(
            "SELECT id, channel, label, app_version, last_seen_at FROM {$table} WHERE channel = ? AND token_hash = ? LIMIT 1",
            'ss',
            array($channel, $token_hash)
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
    function miq_account_unregister_notification_device($user_id, $device_id = 0, $channel = '', $token = '')
    {
        $user_id = (int) $user_id;
        $device_id = (int) $device_id;
        $channel = miq_account_notification_clean_channel($channel);
        $token = trim((string) $token);
        $table = miq_account_table('notification_devices');
        if ($device_id > 0) {
            $statement = miq_account_query(
                "UPDATE {$table} SET enabled = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND user_id = ?",
                'ii',
                array($device_id, $user_id)
            );
        } elseif ($token !== '' && $channel !== '') {
            $statement = miq_account_query(
                "UPDATE {$table} SET enabled = 0, updated_at = UTC_TIMESTAMP() WHERE user_id = ? AND channel = ? AND token_hash = ?",
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
            $base_host = strtolower((string) parse_url($base_url, PHP_URL_HOST));
            $link_host = strtolower((string) parse_url($link_url, PHP_URL_HOST));
            return $link_host === '' || $link_host === $base_host ? $link_url : $base_url . '/workspace?tab=notifications';
        }
        return $base_url . '/' . ltrim($link_url, '/');
    }
}

if (!class_exists('MiqAccountFcmDeliveryException')) {
    class MiqAccountFcmDeliveryException extends RuntimeException
    {
        public $permanent;

        public function __construct($message, $permanent = false)
        {
            parent::__construct($message);
            $this->permanent = (bool) $permanent;
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
        curl_setopt_array($handle, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        $response_body = curl_exec($handle);
        $curl_error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($response_body === false) {
            throw new RuntimeException('FCM request failed: ' . ($curl_error !== '' ? $curl_error : 'unknown network error'));
        }
        return array('status' => $status, 'body' => (string) $response_body);
    }
}

if (!function_exists('miq_account_fcm_access_token')) {
    function miq_account_fcm_access_token($credentials)
    {
        static $access_token = '';
        static $expires_at = 0;
        if ($access_token !== '' && $expires_at > time() + 60) {
            return $access_token;
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
        $access_token = (string) $payload['access_token'];
        $expires_at = $issued_at + max(300, (int) ($payload['expires_in'] ?? 3600));
        return $access_token;
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
                    'title' => substr((string) $title, 0, 160),
                    'body' => substr((string) $message, 0, 500),
                ),
                'data' => $data,
                'android' => array(
                    'priority' => 'HIGH',
                    'notification' => array('channel_id' => 'miq_notifications'),
                ),
                'webpush' => array(
                    'headers' => array('Urgency' => 'high'),
                    'notification' => array(
                        'title' => substr((string) $title, 0, 160),
                        'body' => substr((string) $message, 0, 500),
                        'icon' => rtrim((string) miq_account_config()['base_url'], '/') . '/assets/img/360Logo_192.png',
                        'badge' => rtrim((string) miq_account_config()['base_url'], '/') . '/assets/img/360Logo_192.png',
                        'tag' => 'miq-notification-' . (int) $notification_id,
                        'data' => array('url' => $url, 'notification_id' => (string) ((int) $notification_id)),
                    ),
                    'fcm_options' => array('link' => $url),
                ),
            ),
        );
        $response = miq_account_fcm_http_post(
            'https://fcm.googleapis.com/v1/projects/' . rawurlencode($credentials['project_id']) . '/messages:send',
            array(
                'Authorization: Bearer ' . miq_account_fcm_access_token($credentials),
                'Content-Type: application/json; charset=UTF-8',
            ),
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        $response_payload = json_decode($response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($response_payload) || empty($response_payload['name'])) {
            $permanent = $response['status'] === 404
                || stripos($response['body'], 'UNREGISTERED') !== false
                || stripos($response['body'], 'registration-token-not-registered') !== false;
            throw new MiqAccountFcmDeliveryException('FCM send failed.', $permanent);
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
                "UPDATE {$table} SET status = 'pending', provider_message_id = NULL, error_message = NULL, attempted_at = NULL, delivered_at = NULL WHERE notification_id = ?",
                'i',
                array((int) $notification_id)
            )->close();
        } catch (Throwable $error) {
            // Delivery history is best-effort and must not prevent an in-app notification.
        }
    }
}

if (!function_exists('miq_account_dispatch_notification')) {
    function miq_account_dispatch_notification($notification_id, $user_id, $type, $title, $message, $link_url = '')
    {
        $preference_key = miq_account_notification_preference_key($type);
        if ($preference_key === null) {
            return 0;
        }
        $preferences = miq_account_notification_preferences($user_id);
        if (empty($preferences[$preference_key]) || !miq_account_fcm_service_account()) {
            return 0;
        }

        $devices_table = miq_account_table('notification_devices');
        $deliveries_table = miq_account_table('notification_deliveries');
        $devices = miq_account_fetch_all(miq_account_query(
            "SELECT id, device_token FROM {$devices_table} WHERE user_id = ? AND enabled = 1 ORDER BY updated_at DESC, id DESC LIMIT " . (int) miq_account_config()['fcm_max_devices_per_notification'],
            'i',
            array((int) $user_id)
        ));
        $unread_count = miq_account_unread_notification_count($user_id);
        $sent = 0;
        foreach ($devices as $device) {
            $device_id = (int) $device['id'];
            $existing = miq_account_fetch_one(miq_account_query(
                "SELECT id, status FROM {$deliveries_table} WHERE notification_id = ? AND device_id = ? LIMIT 1",
                'ii',
                array((int) $notification_id, $device_id)
            ));
            if ($existing && $existing['status'] === 'sent') {
                continue;
            }

            if ($existing) {
                $delivery_id = (int) $existing['id'];
                miq_account_query("UPDATE {$deliveries_table} SET status = 'pending', error_message = NULL WHERE id = ?", 'i', array($delivery_id))->close();
            } else {
                miq_account_query(
                    "INSERT INTO {$deliveries_table} (notification_id, device_id, status, created_at) VALUES (?, ?, 'pending', UTC_TIMESTAMP())",
                    'ii',
                    array((int) $notification_id, $device_id)
                )->close();
                $delivery_id = (int) miq_account_db()->insert_id;
            }

            try {
                $provider_id = miq_account_fcm_send($device['device_token'], $notification_id, $type, $title, $message, $link_url, $unread_count);
                miq_account_query(
                    "UPDATE {$deliveries_table} SET status = 'sent', provider_message_id = ?, error_message = NULL, attempted_at = UTC_TIMESTAMP(), delivered_at = UTC_TIMESTAMP() WHERE id = ?",
                    'si',
                    array($provider_id, $delivery_id)
                )->close();
                $sent++;
            } catch (MiqAccountFcmDeliveryException $error) {
                miq_account_query(
                    "UPDATE {$deliveries_table} SET status = 'failed', error_message = ?, attempted_at = UTC_TIMESTAMP() WHERE id = ?",
                    'si',
                    array(substr($error->getMessage(), 0, 500), $delivery_id)
                )->close();
                if ($error->permanent) {
                    miq_account_query("UPDATE {$devices_table} SET enabled = 0, updated_at = UTC_TIMESTAMP() WHERE id = ?", 'i', array($device_id))->close();
                }
                error_log('360MiQ FCM delivery failure: ' . $error->getMessage());
            } catch (Throwable $error) {
                miq_account_query(
                    "UPDATE {$deliveries_table} SET status = 'failed', error_message = ?, attempted_at = UTC_TIMESTAMP() WHERE id = ?",
                    'si',
                    array(substr($error->getMessage(), 0, 500), $delivery_id)
                )->close();
                error_log('360MiQ FCM delivery failure: ' . $error->getMessage());
            }
        }
        return $sent;
    }
}
