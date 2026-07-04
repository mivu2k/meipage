<?php
defined('ABSPATH') || exit;

/**
 * Minimal, dependency-free JWT (HS256) authentication for the REST API.
 *
 * Define DTC_JWT_SECRET in wp-config.php:
 *   define('DTC_JWT_SECRET', 'a-long-random-string');
 * Falls back to SECURE_AUTH_KEY if not defined.
 */
class DTC_JWT
{
    private const TTL = 8 * HOUR_IN_SECONDS;

    public static function init(): void
    {
        // Resolve Bearer tokens into the current WP user for every REST request.
        add_filter('determine_current_user', [self::class, 'determine_user'], 20);
    }

    private static function secret(): string
    {
        if (defined('DTC_JWT_SECRET')) {
            return DTC_JWT_SECRET;
        }
        return defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : wp_salt('secure_auth');
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string|false
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function issue(WP_User $user): string
    {
        $header = self::b64(wp_json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::b64(wp_json_encode([
            'sub' => $user->ID,
            'iat' => time(),
            'exp' => time() + self::TTL,
            'iss' => home_url(),
        ]));
        $sig = self::b64(hash_hmac('sha256', "$header.$payload", self::secret(), true));
        return "$header.$payload.$sig";
    }

    public static function validate(string $token): ?int
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $payload, $sig] = $parts;
        $expected = self::b64(hash_hmac('sha256', "$header.$payload", self::secret(), true));
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        $claims = json_decode(self::b64d($payload), true);
        if (!is_array($claims) || ($claims['exp'] ?? 0) < time()) {
            return null;
        }
        return (int) ($claims['sub'] ?? 0) ?: null;
    }

    public static function determine_user($user_id)
    {
        if ($user_id || !self::bearer()) {
            return $user_id;
        }
        return self::validate(self::bearer()) ?? $user_id;
    }

    private static function bearer(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        return preg_match('/^Bearer\s+(\S+)$/i', $auth, $m) ? $m[1] : null;
    }

    public static function register_routes(): void
    {
        register_rest_route(DTC_API_NAMESPACE, '/auth/login', [
            'methods' => 'POST',
            'callback' => [self::class, 'login'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route(DTC_API_NAMESPACE, '/auth/register', [
            'methods' => 'POST',
            'callback' => [self::class, 'register'],
            'permission_callback' => [DTC_Rest_Forms::class, 'rate_limit'],
        ]);
        register_rest_route(DTC_API_NAMESPACE, '/auth/me', [
            'methods' => 'GET',
            'callback' => [self::class, 'me'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function login(WP_REST_Request $req)
    {
        if (!DTC_Security::check_login_rate_limit()) {
            return new WP_Error('rate_limited', 'Too many login attempts. Try again later.', ['status' => 429]);
        }

        $user = wp_authenticate(
            sanitize_text_field($req['username'] ?? ''),
            (string) ($req['password'] ?? '')
        );

        if (is_wp_error($user)) {
            DTC_Security::record_failed_login();
            return new WP_Error('invalid_credentials', 'Invalid username or password.', ['status' => 401]);
        }

        if (in_array('dtc_pending', $user->roles, true)) {
            return new WP_Error(
                'pending_approval',
                'Your account is awaiting approval by our team. You will be notified once access is granted.',
                ['status' => 403]
            );
        }

        return [
            'token' => self::issue($user),
            'user' => self::user_payload($user),
        ];
    }

    /**
     * Public self-registration. The account is created with the
     * "Pending Approval" role; an administrator promotes it to Customer
     * (Users → edit user → Role) to grant portal access.
     */
    public static function register(WP_REST_Request $req)
    {
        $email = sanitize_email($req['email'] ?? '');
        $name = sanitize_text_field($req['name'] ?? '');
        $password = (string) ($req['password'] ?? '');

        if (!is_email($email) || $name === '') {
            return new WP_Error('invalid', 'A valid email and your full name are required.', ['status' => 400]);
        }
        if (strlen($password) < 8) {
            return new WP_Error('weak_password', 'Password must be at least 8 characters.', ['status' => 400]);
        }
        if (email_exists($email)) {
            return new WP_Error('exists', 'An account with this email already exists.', ['status' => 409]);
        }

        $user_id = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => $password,
            'display_name' => $name,
            'role' => 'dtc_pending',
        ]);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        update_user_meta($user_id, 'organization', sanitize_text_field($req['organization'] ?? ''));
        update_user_meta($user_id, 'country', sanitize_text_field($req['country'] ?? ''));
        DTC_Security::audit('user_registered', ['user' => $user_id, 'email' => $email]);

        $settings = DTC_Settings::get();
        $to = $settings['contact']['support_email'] ?: get_bloginfo('admin_email');
        wp_mail($to, 'New portal registration awaiting approval', sprintf(
            "Name: %s\nEmail: %s\nOrganization: %s\nCountry: %s\n\nApprove: %s (set Role to Customer)",
            $name,
            $email,
            $req['organization'] ?? '-',
            $req['country'] ?? '-',
            admin_url('user-edit.php?user_id=' . $user_id)
        ));

        return ['message' => 'Registration received. Your account will be reviewed and activated by our team.'];
    }

    public static function me()
    {
        return self::user_payload(wp_get_current_user());
    }

    public static function user_payload(WP_User $user): array
    {
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'roles' => array_values($user->roles),
        ];
    }
}
