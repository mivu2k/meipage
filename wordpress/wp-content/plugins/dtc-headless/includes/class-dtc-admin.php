<?php
defined('ABSPATH') || exit;

/**
 * Admin: a simple Website Settings screen (JSON editor over dtc_settings)
 * plus meta boxes hints. For richer field UIs install ACF — the REST layer
 * reads plain post meta, so ACF field names map 1:1.
 */
class DTC_Admin
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
    }

    public static function menu(): void
    {
        add_menu_page(
            'Website Settings',
            'Website Settings',
            'manage_options',
            'dtc-settings',
            [self::class, 'render_settings'],
            'dashicons-admin-generic',
            59
        );
    }

    public static function render_settings(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['dtc_settings_json']) && check_admin_referer('dtc_settings')) {
            $decoded = json_decode(wp_unslash($_POST['dtc_settings_json']), true);
            if (is_array($decoded)) {
                update_option('dtc_settings', $decoded);
                echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Invalid JSON — nothing saved.</p></div>';
            }
        }

        $json = wp_json_encode(DTC_Settings::get(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        ?>
        <div class="wrap">
            <h1>Website Settings</h1>
            <p>Global settings served to the Vue frontend at <code>/wp-json/dtc/v1/settings</code>:
               company info, contact, social links, theme colors, homepage hero &amp; CTA, menus and footer.</p>
            <form method="post">
                <?php wp_nonce_field('dtc_settings'); ?>
                <textarea name="dtc_settings_json" rows="30" style="width:100%;font-family:monospace;"><?php echo esc_textarea($json); ?></textarea>
                <p><button class="button button-primary">Save Settings</button></p>
            </form>
        </div>
        <?php
    }
}
