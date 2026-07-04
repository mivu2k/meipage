<?php
defined('ABSPATH') || exit;

/**
 * Authenticated customer portal endpoints under /dtc/v1/portal:
 * permission-based downloads, tickets, repair tracking, inquiry history.
 */
class DTC_Rest_Portal
{
    public static function register_routes(): void
    {
        $portal = fn() => is_user_logged_in() && current_user_can('dtc_portal_access');

        $routes = [
            ['/portal/downloads', 'GET', 'downloads'],
            ['/portal/products', 'GET', 'products'],
            ['/portal/tickets', 'GET', 'tickets'],
            ['/portal/tickets', 'POST', 'create_ticket'],
            ['/portal/tickets/(?P<id>\d+)', 'GET', 'ticket'],
            ['/portal/tickets/(?P<id>\d+)/reply', 'POST', 'reply_ticket'],
            ['/portal/repairs', 'GET', 'repairs'],
            ['/portal/inquiries', 'GET', 'inquiries'],
            ['/portal/download-file/(?P<id>\d+)', 'GET', 'download_file'],
        ];
        foreach ($routes as [$route, $method, $handler]) {
            register_rest_route(DTC_API_NAMESPACE, $route, [
                'methods' => $method,
                'callback' => [self::class, $handler],
                'permission_callback' => $portal,
            ]);
        }
    }

    // ---------- downloads ----------

    /**
     * Whether the current user may access a download post.
     * Access model (post meta):
     *   access = public | customer | assigned
     *   assigned_users = [user ids] (when access = assigned)
     *   allowed_roles = [role slugs] (optional additional restriction)
     */
    public static function can_access_download(int $post_id, ?WP_User $user = null): bool
    {
        $access = get_post_meta($post_id, 'access', true) ?: 'customer';
        if ($access === 'public') {
            return true;
        }
        $user ??= wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        if (user_can($user, 'manage_options')) {
            return true;
        }

        $roles = (array) (get_post_meta($post_id, 'allowed_roles', true) ?: []);
        if ($roles && !array_intersect($roles, $user->roles)) {
            return false;
        }

        if ($access === 'assigned') {
            $assigned = array_map('intval', (array) (get_post_meta($post_id, 'assigned_users', true) ?: []));
            return in_array($user->ID, $assigned, true);
        }

        return user_can($user, 'dtc_download_files');
    }

    public static function download_payload(int $post_id): ?array
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'dtc_download') {
            return null;
        }
        $accessible = self::can_access_download($post_id);
        $types = get_the_terms($post, 'dtc_download_type');
        return [
            'id' => $post_id,
            'title' => get_the_title($post),
            'type' => $types && !is_wp_error($types) ? $types[0]->slug : 'manual',
            'version' => (string) get_post_meta($post_id, 'version', true),
            'size' => (string) get_post_meta($post_id, 'size', true),
            // Serve through the API so access is checked on every request.
            'url' => $accessible ? rest_url(DTC_API_NAMESPACE . "/portal/download-file/{$post_id}") : null,
            'restricted' => !$accessible,
        ];
    }

    public static function downloads()
    {
        $posts = get_posts(['post_type' => 'dtc_download', 'numberposts' => -1]);
        $items = [];
        foreach ($posts as $p) {
            if (self::can_access_download($p->ID)) {
                $items[] = self::download_payload($p->ID);
            }
        }
        return $items;
    }

    /** Stream the actual file after re-checking permission; audits every download. */
    public static function download_file(WP_REST_Request $req)
    {
        $id = (int) $req['id'];
        if (!self::can_access_download($id)) {
            return new WP_Error('forbidden', 'You do not have access to this file.', ['status' => 403]);
        }
        $media_id = (int) get_post_meta($id, 'file', true);
        $path = get_attached_file($media_id);
        if (!$path || !file_exists($path)) {
            return new WP_Error('not_found', 'File missing.', ['status' => 404]);
        }

        DTC_Security::audit('file_download', ['download' => $id, 'user' => get_current_user_id()]);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // ---------- my products ----------

    public static function products()
    {
        $ids = array_map('intval', (array) get_user_meta(get_current_user_id(), 'dtc_products', true) ?: []);
        return array_values(array_filter(array_map(function ($id) {
            $post = get_post($id);
            return $post && $post->post_type === 'dtc_product'
                ? DTC_Rest_Content::product_payload($post)
                : null;
        }, $ids)));
    }

    // ---------- tickets ----------

    private static function ticket_payload(WP_Post $p): array
    {
        $messages = get_post_meta($p->ID, 'messages', true);
        return [
            'id' => $p->ID,
            'subject' => get_the_title($p),
            'type' => (string) get_post_meta($p->ID, 'type', true) ?: 'technical',
            'status' => (string) get_post_meta($p->ID, 'status', true) ?: 'open',
            'created' => $p->post_date_gmt,
            'updated' => $p->post_modified_gmt,
            'messages' => is_array($messages) ? $messages : [],
        ];
    }

    private static function own_ticket(int $id): WP_Post|WP_Error
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'dtc_ticket'
            || ((int) get_post_meta($id, 'user_id', true) !== get_current_user_id() && !current_user_can('manage_options'))) {
            return new WP_Error('not_found', 'Ticket not found.', ['status' => 404]);
        }
        return $post;
    }

    public static function tickets()
    {
        $posts = get_posts([
            'post_type' => 'dtc_ticket',
            'post_status' => 'private',
            'numberposts' => -1,
            'meta_key' => 'user_id',
            'meta_value' => get_current_user_id(),
        ]);
        return array_map([self::class, 'ticket_payload'], $posts);
    }

    public static function ticket(WP_REST_Request $req)
    {
        $post = self::own_ticket((int) $req['id']);
        return is_wp_error($post) ? $post : self::ticket_payload($post);
    }

    public static function create_ticket(WP_REST_Request $req)
    {
        $user = wp_get_current_user();
        $id = wp_insert_post([
            'post_type' => 'dtc_ticket',
            'post_status' => 'private',
            'post_title' => sanitize_text_field($req['subject'] ?? 'Support request'),
            'meta_input' => [
                'user_id' => $user->ID,
                'type' => sanitize_key($req['type'] ?? 'technical'),
                'status' => 'open',
                'messages' => [[
                    'author' => $user->display_name,
                    'body' => sanitize_textarea_field($req['message'] ?? ''),
                    'date' => current_time('mysql', true),
                ]],
            ],
        ], true);
        if (is_wp_error($id)) {
            return $id;
        }
        return self::ticket_payload(get_post($id));
    }

    public static function reply_ticket(WP_REST_Request $req)
    {
        $post = self::own_ticket((int) $req['id']);
        if (is_wp_error($post)) {
            return $post;
        }
        $messages = get_post_meta($post->ID, 'messages', true) ?: [];
        $messages[] = [
            'author' => wp_get_current_user()->display_name,
            'body' => sanitize_textarea_field($req['message'] ?? ''),
            'date' => current_time('mysql', true),
        ];
        update_post_meta($post->ID, 'messages', $messages);
        update_post_meta($post->ID, 'status', 'waiting');
        wp_update_post(['ID' => $post->ID]); // bump modified date
        return self::ticket_payload(get_post($post->ID));
    }

    // ---------- repairs ----------

    public static function repairs()
    {
        $posts = get_posts([
            'post_type' => 'dtc_repair',
            'post_status' => 'private',
            'numberposts' => -1,
            'meta_key' => 'user_id',
            'meta_value' => get_current_user_id(),
        ]);
        return array_map(function (WP_Post $p) {
            $history = get_post_meta($p->ID, 'history', true);
            $documents = array_values(array_filter(array_map(function ($row) {
                if (!is_array($row) || empty($row['file'])) {
                    return null;
                }
                $url = wp_get_attachment_url((int) $row['file']);
                return $url ? ['label' => (string) ($row['label'] ?: 'Document'), 'url' => $url] : null;
            }, (array) (get_post_meta($p->ID, 'documents', true) ?: []))));
            return [
                'id' => $p->ID,
                'rma' => (string) get_post_meta($p->ID, 'rma', true) ?: ('RMA-' . $p->ID),
                'product' => (string) get_post_meta($p->ID, 'product', true),
                'serial' => (string) get_post_meta($p->ID, 'serial', true),
                'stage' => (string) get_post_meta($p->ID, 'stage', true) ?: 'received',
                'history' => is_array($history) ? $history : [],
                'documents' => $documents,
            ];
        }, $posts);
    }

    // ---------- inquiries ----------

    public static function inquiries()
    {
        $posts = get_posts([
            'post_type' => 'dtc_inquiry',
            'post_status' => 'private',
            'numberposts' => -1,
            'meta_key' => 'user_id',
            'meta_value' => get_current_user_id(),
        ]);
        return array_map(fn(WP_Post $p) => [
            'id' => $p->ID,
            'date' => $p->post_date_gmt,
            'status' => (string) get_post_meta($p->ID, 'status', true) ?: 'new',
            'products' => array_map(fn($x) => $x['title'] ?? '', (array) get_post_meta($p->ID, 'products', true)),
        ], $posts);
    }
}
