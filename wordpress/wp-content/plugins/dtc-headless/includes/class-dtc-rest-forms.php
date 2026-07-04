<?php
defined('ABSPATH') || exit;

/**
 * Public write endpoints: inquiries (quote requests), contact messages,
 * consultation requests and job applications. All rate-limited per IP.
 */
class DTC_Rest_Forms
{
    public static function register_routes(): void
    {
        $routes = [
            '/inquiries' => 'inquiry',
            '/contact' => 'contact',
            '/consultations' => 'consultation',
            '/applications' => 'application',
        ];
        foreach ($routes as $route => $handler) {
            register_rest_route(DTC_API_NAMESPACE, $route, [
                'methods' => 'POST',
                'callback' => [self::class, $handler],
                'permission_callback' => [self::class, 'rate_limit'],
            ]);
        }

        register_rest_route(DTC_API_NAMESPACE, '/uploads/resume', [
            'methods' => 'POST',
            'callback' => [self::class, 'upload_resume'],
            'permission_callback' => [self::class, 'rate_limit'],
        ]);
    }

    /** Resume upload for job applications: PDF/DOC/DOCX, max 10 MB. */
    public static function upload_resume(WP_REST_Request $req)
    {
        $files = $req->get_file_params();
        if (empty($files['file'])) {
            return new WP_Error('no_file', 'No file uploaded.', ['status' => 400]);
        }
        $file = $files['file'];
        if (($file['size'] ?? 0) > 10 * MB_IN_BYTES) {
            return new WP_Error('too_large', 'File must be under 10 MB.', ['status' => 400]);
        }
        $allowed = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $check = wp_check_filetype($file['name'], $allowed);
        if (!$check['ext']) {
            return new WP_Error('bad_type', 'Only PDF, DOC or DOCX files are accepted.', ['status' => 400]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $id = media_handle_sideload([
            'name' => sanitize_file_name($file['name']),
            'type' => $file['type'],
            'tmp_name' => $file['tmp_name'],
            'error' => $file['error'],
            'size' => $file['size'],
        ], 0, 'Resume upload');

        return is_wp_error($id) ? $id : ['id' => $id];
    }

    /** Simple per-IP rate limit for public forms: 10 submissions / 10 min. */
    public static function rate_limit(): bool
    {
        $key = 'dtc_form_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
        $count = (int) get_transient($key);
        if ($count >= 10) {
            return false;
        }
        set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
        return true;
    }

    private static function email(string $value): string
    {
        return sanitize_email($value);
    }

    private static function notify(string $subject, string $body): void
    {
        $settings = DTC_Settings::get();
        $to = $settings['contact']['support_email'] ?: get_bloginfo('admin_email');
        wp_mail($to, $subject, $body);
    }

    public static function inquiry(WP_REST_Request $req)
    {
        $products = array_map(fn($p) => [
            'id' => (int) ($p['id'] ?? 0),
            'title' => sanitize_text_field($p['title'] ?? ''),
            'quantity' => max(1, (int) ($p['quantity'] ?? 1)),
        ], (array) $req['products']);

        if (!$products || !self::email($req['email'] ?? '')) {
            return new WP_Error('invalid', 'Products and a valid email are required.', ['status' => 400]);
        }

        $id = wp_insert_post([
            'post_type' => 'dtc_inquiry',
            'post_status' => 'private',
            'post_title' => sprintf('Inquiry — %s (%s)', sanitize_text_field($req['organization'] ?? ''), self::email($req['email'])),
            'meta_input' => [
                'name' => sanitize_text_field($req['name'] ?? ''),
                'email' => self::email($req['email']),
                'organization' => sanitize_text_field($req['organization'] ?? ''),
                'country' => sanitize_text_field($req['country'] ?? ''),
                'message' => sanitize_textarea_field($req['message'] ?? ''),
                'products' => $products,
                'status' => 'new',
                'user_id' => get_current_user_id(),
            ],
        ], true);

        if (is_wp_error($id)) {
            return $id;
        }

        $lines = array_map(fn($p) => "- {$p['title']} × {$p['quantity']}", $products);
        self::notify('New quotation inquiry #' . $id, implode("\n", array_merge(
            ["From: {$req['name']} <{$req['email']}>", "Organization: {$req['organization']}", "Country: {$req['country']}", '', 'Products:'],
            $lines,
            ['', 'Message:', (string) $req['message']]
        )));

        return ['id' => $id];
    }

    public static function contact(WP_REST_Request $req)
    {
        if (!self::email($req['email'] ?? '')) {
            return new WP_Error('invalid', 'A valid email is required.', ['status' => 400]);
        }
        self::notify(
            '[Contact] ' . sanitize_text_field($req['subject'] ?? 'Website message'),
            sprintf(
                "From: %s <%s>\nDepartment: %s\n\n%s",
                sanitize_text_field($req['name'] ?? ''),
                self::email($req['email']),
                sanitize_text_field($req['department'] ?? '-'),
                sanitize_textarea_field($req['message'] ?? '')
            )
        );
        return ['id' => time()];
    }

    public static function consultation(WP_REST_Request $req)
    {
        if (!self::email($req['email'] ?? '')) {
            return new WP_Error('invalid', 'A valid email is required.', ['status' => 400]);
        }
        self::notify(
            '[Consultation] ' . sanitize_text_field($req['solution'] ?? ''),
            sprintf(
                "From: %s <%s>\nOrganization: %s\nSolution: %s\n\n%s",
                sanitize_text_field($req['name'] ?? ''),
                self::email($req['email']),
                sanitize_text_field($req['organization'] ?? ''),
                sanitize_text_field($req['solution'] ?? ''),
                sanitize_textarea_field($req['message'] ?? '')
            )
        );
        return ['id' => time()];
    }

    public static function application(WP_REST_Request $req)
    {
        if (!self::email($req['email'] ?? '')) {
            return new WP_Error('invalid', 'A valid email is required.', ['status' => 400]);
        }
        $id = wp_insert_post([
            'post_type' => 'dtc_application',
            'post_status' => 'private',
            'post_title' => sprintf('Application — %s (position #%d)', sanitize_text_field($req['name'] ?? ''), (int) $req['position']),
            'meta_input' => [
                'name' => sanitize_text_field($req['name'] ?? ''),
                'email' => self::email($req['email']),
                'phone' => sanitize_text_field($req['phone'] ?? ''),
                'position' => (int) $req['position'],
                'cover_letter' => sanitize_textarea_field($req['cover_letter'] ?? ''),
                'resume_media_id' => (int) ($req['resume_media_id'] ?? 0),
            ],
        ], true);
        return is_wp_error($id) ? $id : ['id' => $id];
    }
}
