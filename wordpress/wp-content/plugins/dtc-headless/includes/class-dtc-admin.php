<?php
defined('ABSPATH') || exit;

/**
 * Admin: Website Settings screen — a Branding section with media pickers
 * (logo, dark logo, favicon) plus a JSON editor for everything else.
 */
class DTC_Admin
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_enqueue_scripts', function (string $hook) {
            if ($hook === 'toplevel_page_dtc-settings') {
                wp_enqueue_media();
            }
        });
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

        if (isset($_POST['dtc_settings_submit']) && check_admin_referer('dtc_settings')) {
            $settings = DTC_Settings::get();

            // Branding fields (URLs from the media pickers)
            $settings['company']['name'] = sanitize_text_field(wp_unslash($_POST['dtc_company_name'] ?? $settings['company']['name']));
            $settings['company']['slogan'] = sanitize_text_field(wp_unslash($_POST['dtc_company_slogan'] ?? $settings['company']['slogan']));
            foreach (['logo', 'logo_dark', 'favicon'] as $key) {
                $settings['company'][$key] = esc_url_raw(wp_unslash($_POST["dtc_{$key}"] ?? ''));
            }

            // Advanced JSON (everything else)
            if (!empty($_POST['dtc_settings_json'])) {
                $decoded = json_decode(wp_unslash($_POST['dtc_settings_json']), true);
                if (is_array($decoded)) {
                    $decoded['company'] = $settings['company']; // branding fields win
                    $settings = array_replace_recursive($settings, $decoded);
                } else {
                    echo '<div class="notice notice-error"><p>Invalid JSON in the advanced editor — JSON changes were skipped, branding was saved.</p></div>';
                }
            }

            update_option('dtc_settings', $settings);
            echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
        }

        $s = DTC_Settings::get();
        $json = wp_json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $picker = function (string $key, string $label, string $value, string $hint = ''): void {
            ?>
            <tr>
                <th scope="row"><label for="dtc_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                <td>
                    <div style="display:flex;gap:8px;align-items:center;max-width:640px">
                        <input type="text" class="regular-text" style="flex:1" id="dtc_<?php echo esc_attr($key); ?>"
                               name="dtc_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" placeholder="https://…">
                        <button type="button" class="button dtc-pick" data-target="dtc_<?php echo esc_attr($key); ?>">Select Image</button>
                    </div>
                    <?php if ($value) : ?>
                        <img src="<?php echo esc_url($value); ?>" alt="" style="margin-top:8px;max-height:48px;background:#f0f0f1;padding:6px;border-radius:6px">
                    <?php endif; ?>
                    <?php if ($hint) : ?><p class="description"><?php echo esc_html($hint); ?></p><?php endif; ?>
                </td>
            </tr>
            <?php
        };
        ?>
        <div class="wrap">
            <h1>Website Settings</h1>
            <form method="post">
                <?php wp_nonce_field('dtc_settings'); ?>

                <h2>Branding</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="dtc_company_name">Company Name</label></th>
                        <td><input type="text" class="regular-text" id="dtc_company_name" name="dtc_company_name"
                                   value="<?php echo esc_attr($s['company']['name']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dtc_company_slogan">Slogan</label></th>
                        <td><input type="text" class="regular-text" id="dtc_company_slogan" name="dtc_company_slogan"
                                   value="<?php echo esc_attr($s['company']['slogan']); ?>"></td>
                    </tr>
                    <?php
                    $picker('logo', 'Logo (navbar)', $s['company']['logo'], 'Shown in the site header. Transparent PNG/SVG recommended, ~40px tall.');
                    $picker('logo_dark', 'Logo (dark background)', $s['company']['logo_dark'], 'Shown in the footer / dark sections. Falls back to the company name if empty.');
                    $picker('favicon', 'Favicon', $s['company']['favicon'], 'Browser tab icon. Square PNG (32×32 or 64×64) or .ico.');
                    ?>
                </table>

                <h2>Advanced (all settings)</h2>
                <p class="description">Contact info, social links, theme colors, homepage hero &amp; statistics, menus, footer —
                   served to the frontend at <code>/wp-json/dtc/v1/settings</code>.</p>
                <textarea name="dtc_settings_json" rows="24" style="width:100%;font-family:monospace;"><?php echo esc_textarea($json); ?></textarea>

                <p><button class="button button-primary" name="dtc_settings_submit" value="1">Save Settings</button></p>
            </form>
        </div>
        <script>
        jQuery(function ($) {
            $('.dtc-pick').on('click', function () {
                var target = $('#' + $(this).data('target'));
                var frame = wp.media({ multiple: false, library: { type: 'image' } });
                frame.on('select', function () {
                    target.val(frame.state().get('selection').first().toJSON().url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }
}
