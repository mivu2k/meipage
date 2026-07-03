<?php
defined('ABSPATH') || exit;

/**
 * Security hardening: CORS for the SPA, login rate limiting,
 * optional GeoIP country blocking, and a lightweight audit log.
 */
class DTC_Security
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW = 15 * MINUTE_IN_SECONDS;

    public static function init(): void
    {
        add_action('rest_api_init', [self::class, 'cors'], 15);
        add_action('init', [self::class, 'geo_block'], 1);
        add_action('wp_login', [self::class, 'audit_login'], 10, 2);
    }

    /** Allow the SPA origin(s) configured in settings; same-origin needs nothing. */
    public static function cors(): void
    {
        $allowed = get_option('dtc_allowed_origins', []); // array of origins
        $origin = get_http_origin();
        if ($origin && in_array($origin, (array) $allowed, true)) {
            remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
            add_filter('rest_pre_serve_request', function ($value) use ($origin) {
                header('Access-Control-Allow-Origin: ' . esc_url_raw($origin));
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Authorization, Content-Type');
                header('Vary: Origin');
                return $value;
            });
        }
    }

    private static function client_ip(): string
    {
        // Behind Nginx, REMOTE_ADDR is trustworthy; do not trust X-Forwarded-For blindly.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function check_login_rate_limit(): bool
    {
        $key = 'dtc_login_' . md5(self::client_ip());
        return (int) get_transient($key) < self::MAX_ATTEMPTS;
    }

    public static function record_failed_login(): void
    {
        $key = 'dtc_login_' . md5(self::client_ip());
        $count = (int) get_transient($key);
        set_transient($key, $count + 1, self::WINDOW);
        self::audit('login_failed', ['ip' => self::client_ip()]);
    }

    public static function audit_login(string $login, WP_User $user): void
    {
        self::audit('login_success', ['user' => $user->ID, 'ip' => self::client_ip()]);
    }

    /**
     * GeoIP country blocking. Nginx (with the geoip2 module) or Cloudflare
     * should set a country header; blocked countries are configured in settings.
     */
    public static function geo_block(): void
    {
        $blocked = get_option('dtc_blocked_countries', []); // ISO codes, e.g. ['XX']
        if (!$blocked) {
            return;
        }
        $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['GEOIP_COUNTRY_CODE'] ?? '';
        if ($country && in_array(strtoupper($country), array_map('strtoupper', (array) $blocked), true)) {
            self::audit('geo_blocked', ['country' => $country, 'ip' => self::client_ip()]);
            wp_die('Access from your region is not available.', 'Blocked', ['response' => 451]);
        }
    }

    /** Append-only audit log stored as a custom table-free option ring buffer. */
    public static function audit(string $event, array $context = []): void
    {
        $log = get_option('dtc_audit_log', []);
        $log[] = ['event' => $event, 'context' => $context, 'time' => current_time('mysql', true)];
        // Keep the last 2000 entries; rotate to file for long-term retention.
        if (count($log) > 2000) {
            $log = array_slice($log, -2000);
        }
        update_option('dtc_audit_log', $log, false);
    }
}
