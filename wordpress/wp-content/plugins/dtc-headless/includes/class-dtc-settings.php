<?php
defined('ABSPATH') || exit;

/**
 * Global site settings exposed at GET /dtc/v1/settings.
 * Stored as a single option (dtc_settings) editable via the admin screen.
 * Nothing is hardcoded — the Vue frontend renders whatever this returns.
 */
class DTC_Settings
{
    public static function defaults(): array
    {
        return [
            'company' => [
                'name' => get_bloginfo('name'),
                'slogan' => get_bloginfo('description'),
                'logo' => '',
                'logo_dark' => '',
                'favicon' => '',
            ],
            'contact' => [
                'phone' => '',
                'email' => get_bloginfo('admin_email'),
                'support_email' => '',
                'whatsapp' => '',
                'working_hours' => '',
            ],
            'social' => [],
            'theme' => [
                'primary' => '#0b1f3a',
                'secondary' => '#12365f',
                'accent' => '#2dd4bf',
            ],
            'homepage' => [
                'hero_title' => '',
                'hero_subtitle' => '',
                'hero_cta_label' => '',
                'hero_cta_link' => '/solutions',
                'hero_animation' => 'network',
                'statistics' => [],
                'cta' => ['title' => '', 'text' => '', 'button_label' => '', 'button_link' => '/contact'],
            ],
            'menus' => [
                'primary' => [
                    ['label' => 'Products', 'url' => '/products'],
                    ['label' => 'Brands', 'url' => '/brands'],
                    ['label' => 'Solutions', 'url' => '/solutions'],
                    ['label' => 'News', 'url' => '/news'],
                    ['label' => 'About', 'url' => '/about'],
                    ['label' => 'Support', 'url' => '/support'],
                    ['label' => 'Contact', 'url' => '/contact'],
                ],
            ],
            'footer' => ['about' => '', 'copyright' => ''],
        ];
    }

    public static function get(): array
    {
        $saved = get_option('dtc_settings', []);
        return array_replace_recursive(self::defaults(), is_array($saved) ? $saved : []);
    }

    public static function register_routes(): void
    {
        register_rest_route(DTC_API_NAMESPACE, '/settings', [
            [
                'methods' => 'GET',
                'callback' => fn() => self::get(),
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => 'POST',
                'callback' => function (WP_REST_Request $req) {
                    $incoming = $req->get_json_params();
                    update_option('dtc_settings', array_replace_recursive(self::get(), (array) $incoming));
                    return self::get();
                },
                'permission_callback' => fn() => current_user_can('manage_options'),
            ],
        ]);
    }
}
