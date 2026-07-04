<?php
defined('ABSPATH') || exit;

/**
 * Proper admin forms for every custom post type — schema-driven meta boxes
 * with media pickers, dropdowns, multi-selects and repeater tables.
 * No ACF required; values are stored exactly as the REST layer reads them.
 */
class DTC_Meta_Boxes
{
    public static function init(): void
    {
        add_action('add_meta_boxes', [self::class, 'add_boxes']);
        add_action('save_post', [self::class, 'save'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
        // All DTC types use the classic editor: a plain form with title,
        // description box and the structured fields below — no blocks.
        add_filter('use_block_editor_for_post_type', function ($use, $type) {
            return str_starts_with($type, 'dtc_') ? false : $use;
        }, 10, 2);
    }

    /**
     * Field schema per post type.
     * Types: text, textarea, lines (textarea → array), select, media (single
     * attachment ID), media_multi (ID array), post_multi (post ID array),
     * user_multi, user, repeater (array of rows).
     */
    private static function schemas(): array
    {
        $stages = [
            'received' => 'Received', 'inspection' => 'Inspection', 'repair' => 'Repair',
            'testing' => 'Testing', 'quality_check' => 'Quality Check', 'ready' => 'Ready', 'shipped' => 'Shipped',
        ];
        return [
            'dtc_product' => ['Product Details', [
                ['key' => 'features', 'label' => 'Features', 'type' => 'lines', 'hint' => 'One feature per line'],
                ['key' => 'specifications', 'label' => 'Specifications (simple rows)', 'type' => 'repeater', 'fields' => [
                    ['key' => 'label', 'label' => 'Label'], ['key' => 'value', 'label' => 'Value'],
                ]],
                ['key' => 'specifications_html', 'label' => 'Specifications (HTML)', 'type' => 'textarea', 'hint' => 'Optional: paste full HTML (multiple tables supported). When filled, this is shown on the website instead of the simple rows above.'],
                ['key' => 'applications', 'label' => 'Applications', 'type' => 'lines', 'hint' => 'One per line'],
                ['key' => 'certifications', 'label' => 'Certifications', 'type' => 'lines', 'hint' => 'One per line, e.g. MIL-STD-810G'],
                ['key' => 'videos', 'label' => 'Video URLs', 'type' => 'lines', 'hint' => 'One YouTube/Vimeo URL per line'],
                ['key' => 'gallery', 'label' => 'Gallery Images', 'type' => 'media_multi'],
                ['key' => 'downloads', 'label' => 'Downloads (datasheets, manuals…)', 'type' => 'post_multi', 'post_type' => 'dtc_download'],
                ['key' => 'accessories', 'label' => 'Accessories', 'type' => 'post_multi', 'post_type' => 'dtc_product'],
                ['key' => 'compatible', 'label' => 'Compatible Products', 'type' => 'post_multi', 'post_type' => 'dtc_product'],
                ['key' => 'related', 'label' => 'Related Products', 'type' => 'post_multi', 'post_type' => 'dtc_product'],
            ]],
            'dtc_brand' => ['Brand Details', [
                ['key' => 'website', 'label' => 'Official Website', 'type' => 'text', 'hint' => 'https://…'],
                ['key' => 'country', 'label' => 'Country', 'type' => 'text'],
            ]],
            'dtc_solution' => ['Solution Details', [
                ['key' => 'architecture', 'label' => 'Architecture', 'type' => 'textarea', 'hint' => 'HTML allowed'],
                ['key' => 'components', 'label' => 'Components', 'type' => 'lines', 'hint' => 'One per line'],
                ['key' => 'benefits', 'label' => 'Benefits', 'type' => 'lines', 'hint' => 'One per line'],
                ['key' => 'case_studies', 'label' => 'Case Studies', 'type' => 'repeater', 'fields' => [
                    ['key' => 'title', 'label' => 'Title'], ['key' => 'summary', 'label' => 'Summary'],
                ]],
                ['key' => 'related_products', 'label' => 'Related Products', 'type' => 'post_multi', 'post_type' => 'dtc_product'],
                ['key' => 'downloads', 'label' => 'Downloads', 'type' => 'post_multi', 'post_type' => 'dtc_download'],
            ]],
            'dtc_download' => ['File & Access', [
                ['key' => 'file', 'label' => 'File', 'type' => 'media', 'hint' => 'The downloadable file (PDF, ZIP, firmware…)'],
                ['key' => 'version', 'label' => 'Version', 'type' => 'text', 'hint' => 'e.g. 1.2'],
                ['key' => 'size', 'label' => 'Size', 'type' => 'text', 'hint' => 'e.g. 2.4 MB'],
                ['key' => 'access', 'label' => 'Access', 'type' => 'select', 'options' => [
                    'public' => 'Public — anyone can download',
                    'customer' => 'Customers — any logged-in portal user',
                    'assigned' => 'Assigned — only the users selected below',
                ]],
                ['key' => 'assigned_users', 'label' => 'Assigned Users', 'type' => 'user_multi', 'hint' => 'Only used when Access = Assigned'],
            ]],
            'dtc_branch' => ['Branch Details', [
                ['key' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                ['key' => 'working_hours', 'label' => 'Working Hours', 'type' => 'text', 'hint' => 'e.g. Sun–Thu 9:00–18:00'],
                ['key' => 'lat', 'label' => 'Latitude', 'type' => 'text', 'hint' => 'e.g. 25.2048'],
                ['key' => 'lng', 'label' => 'Longitude', 'type' => 'text', 'hint' => 'e.g. 55.2708'],
                ['key' => 'departments', 'label' => 'Departments', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Department'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'email', 'label' => 'Email'],
                ]],
            ]],
            'dtc_career' => ['Position Details', [
                ['key' => 'location', 'label' => 'Location', 'type' => 'text'],
                ['key' => 'type', 'label' => 'Employment Type', 'type' => 'select', 'options' => [
                    'full-time' => 'Full-time', 'part-time' => 'Part-time', 'contract' => 'Contract', 'internship' => 'Internship',
                ]],
                ['key' => 'department', 'label' => 'Department', 'type' => 'text'],
            ]],
            'dtc_ticket' => ['Ticket Management', [
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    'open' => 'Open', 'in_progress' => 'In Progress', 'waiting' => 'Waiting on Customer',
                    'resolved' => 'Resolved', 'closed' => 'Closed',
                ]],
                ['key' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => [
                    'technical' => 'Technical', 'warranty' => 'Warranty', 'repair' => 'Repair',
                    'documentation' => 'Documentation', 'training' => 'Training',
                ]],
                ['key' => 'user_id', 'label' => 'Customer', 'type' => 'user'],
                ['key' => '_reply', 'label' => 'Add Reply', 'type' => 'textarea', 'hint' => 'Appended to the conversation when you save', 'virtual' => true],
            ]],
            'dtc_repair' => ['Repair Case', [
                ['key' => 'user_id', 'label' => 'Customer', 'type' => 'user'],
                ['key' => 'rma', 'label' => 'RMA Number', 'type' => 'text', 'hint' => 'Leave empty to auto-generate'],
                ['key' => 'product', 'label' => 'Product', 'type' => 'text'],
                ['key' => 'serial', 'label' => 'Serial Number', 'type' => 'text'],
                ['key' => 'stage', 'label' => 'Stage', 'type' => 'select', 'options' => $stages],
                ['key' => '_stage_note', 'label' => 'Stage Note', 'type' => 'text', 'hint' => 'Optional note recorded with the stage change', 'virtual' => true],
            ]],
        ];
    }

    public static function assets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('dtc-admin', plugins_url('../assets/dtc-admin.js', __FILE__), ['jquery'], DTC_VERSION, true);
        wp_add_inline_style('wp-admin', '
            .dtc-field{margin:14px 0}.dtc-field>label{display:block;font-weight:600;margin-bottom:4px}
            .dtc-field .description{margin-top:2px}
            .dtc-field input[type=text],.dtc-field textarea,.dtc-field select{width:100%;max-width:640px}
            .dtc-field select[multiple]{min-height:110px}
            .dtc-repeater table{border-collapse:collapse;width:100%;max-width:760px}
            .dtc-repeater th,.dtc-repeater td{border:1px solid #dcdcde;padding:6px;text-align:left}
            .dtc-repeater input{width:100%}
            .dtc-media-list{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0;padding:0;list-style:none}
            .dtc-media-list li{position:relative;border:1px solid #dcdcde;padding:4px;border-radius:4px;font-size:12px}
            .dtc-media-list img{width:60px;height:60px;object-fit:cover;display:block}
            .dtc-messages{max-width:760px}.dtc-messages .msg{background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:8px 12px;margin:8px 0}
        ');
    }

    public static function add_boxes(): void
    {
        foreach (self::schemas() as $type => [$title, $fields]) {
            add_meta_box("dtc_{$type}_box", $title, function ($post) use ($fields) {
                self::render($post, $fields);
            }, $type, 'normal', 'high');
        }
        add_meta_box('dtc_inquiry_box', 'Inquiry Details', [self::class, 'render_inquiry'], 'dtc_inquiry', 'normal', 'high');
        add_meta_box('dtc_application_box', 'Application Details', [self::class, 'render_application'], 'dtc_application', 'normal', 'high');
    }

    // ---------------- rendering ----------------

    private static function render(WP_Post $post, array $fields): void
    {
        wp_nonce_field('dtc_meta', 'dtc_meta_nonce');

        if ($post->post_type === 'dtc_ticket') {
            self::render_messages($post);
        }
        if ($post->post_type === 'dtc_repair') {
            self::render_history($post);
        }

        foreach ($fields as $f) {
            $value = !empty($f['virtual']) ? '' : get_post_meta($post->ID, $f['key'], true);
            echo '<div class="dtc-field"><label for="dtc_' . esc_attr($f['key']) . '">' . esc_html($f['label']) . '</label>';
            self::render_field($f, $value);
            if (!empty($f['hint'])) {
                echo '<p class="description">' . esc_html($f['hint']) . '</p>';
            }
            echo '</div>';
        }
    }

    private static function render_field(array $f, $value): void
    {
        $name = 'dtc_meta[' . esc_attr($f['key']) . ']';
        $id = 'dtc_' . esc_attr($f['key']);

        switch ($f['type']) {
            case 'text':
                printf('<input type="text" id="%s" name="%s" value="%s">', $id, $name, esc_attr((string) $value));
                break;

            case 'textarea':
                printf('<textarea id="%s" name="%s" rows="4">%s</textarea>', $id, $name, esc_textarea((string) $value));
                break;

            case 'lines':
                $text = is_array($value) ? implode("\n", $value) : (string) $value;
                printf('<textarea id="%s" name="%s" rows="5">%s</textarea>', $id, $name, esc_textarea($text));
                break;

            case 'select':
                echo "<select id=\"$id\" name=\"$name\">";
                foreach ($f['options'] as $opt => $label) {
                    printf('<option value="%s"%s>%s</option>', esc_attr($opt), selected($value, $opt, false), esc_html($label));
                }
                echo '</select>';
                break;

            case 'media':
                $file = $value ? get_post($value) : null;
                printf('<input type="hidden" name="%s" value="%s">', $name, esc_attr((string) $value));
                echo '<div class="dtc-media-field" data-multi="0">';
                echo '<button class="button dtc-media-btn">Select File</button> ';
                echo '<span class="dtc-media-name">' . esc_html($file ? basename(get_attached_file($file->ID)) : 'No file selected') . '</span>';
                echo '</div>';
                break;

            case 'media_multi':
                $ids = array_filter(array_map('intval', (array) ($value ?: [])));
                echo '<div class="dtc-media-field" data-multi="1">';
                printf('<input type="hidden" name="%s" value="%s">', $name, esc_attr(implode(',', $ids)));
                echo '<button class="button dtc-media-btn">Add Images</button>';
                echo '<ul class="dtc-media-list">';
                foreach ($ids as $mid) {
                    $thumb = wp_get_attachment_image_url($mid, 'thumbnail');
                    echo '<li data-id="' . esc_attr($mid) . '">' . ($thumb ? '<img src="' . esc_url($thumb) . '">' : esc_html(basename((string) get_attached_file($mid))))
                        . ' <a href="#" class="dtc-media-remove">×</a></li>';
                }
                echo '</ul></div>';
                break;

            case 'post_multi':
                $selected = array_map('intval', (array) ($value ?: []));
                $posts = get_posts(['post_type' => $f['post_type'], 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
                echo "<select multiple name=\"{$name}[]\" id=\"$id\">";
                foreach ($posts as $p) {
                    printf('<option value="%d"%s>%s</option>', $p->ID, in_array($p->ID, $selected, true) ? ' selected' : '', esc_html(get_the_title($p)));
                }
                echo '</select><p class="description">Hold Cmd/Ctrl to select multiple.</p>';
                break;

            case 'user':
            case 'user_multi':
                $multi = $f['type'] === 'user_multi';
                $selected = array_map('intval', $multi ? (array) ($value ?: []) : [(int) $value]);
                echo '<select ' . ($multi ? 'multiple ' : '') . 'name="' . $name . ($multi ? '[]' : '') . '" id="' . $id . '">';
                if (!$multi) {
                    echo '<option value="0">— none —</option>';
                }
                foreach (get_users(['orderby' => 'display_name']) as $u) {
                    printf('<option value="%d"%s>%s (%s)</option>', $u->ID, in_array($u->ID, $selected, true) ? ' selected' : '', esc_html($u->display_name), esc_html($u->user_email));
                }
                echo '</select>';
                break;

            case 'repeater':
                $rows = is_array($value) ? array_values($value) : [];
                echo '<div class="dtc-repeater"><table><thead><tr>';
                foreach ($f['fields'] as $sub) {
                    echo '<th>' . esc_html($sub['label']) . '</th>';
                }
                echo '<th style="width:40px"></th></tr></thead><tbody>';
                foreach ($rows as $i => $row) {
                    echo '<tr>';
                    foreach ($f['fields'] as $sub) {
                        printf('<td><input type="text" name="%s[%d][%s]" value="%s"></td>', $name, $i, esc_attr($sub['key']), esc_attr((string) ($row[$sub['key']] ?? '')));
                    }
                    echo '<td><a href="#" class="dtc-rep-remove">×</a></td></tr>';
                }
                echo '</tbody></table>';
                // Template row for the JS "Add Row" button
                echo '<script type="text/template" class="dtc-rep-template">';
                foreach ($f['fields'] as $sub) {
                    printf('<td><input type="text" name="%s[__i__][%s]" value=""></td>', $name, esc_attr($sub['key']));
                }
                echo '<td><a href="#" class="dtc-rep-remove">×</a></td></script>';
                echo '<p><button class="button dtc-rep-add">Add Row</button></p></div>';
                break;
        }
    }

    private static function render_messages(WP_Post $post): void
    {
        $messages = get_post_meta($post->ID, 'messages', true) ?: [];
        echo '<div class="dtc-messages"><h4>Conversation</h4>';
        foreach ((array) $messages as $m) {
            printf(
                '<div class="msg"><strong>%s</strong> <em>%s</em><br>%s</div>',
                esc_html($m['author'] ?? ''),
                esc_html($m['date'] ?? ''),
                nl2br(esc_html($m['body'] ?? ''))
            );
        }
        if (!$messages) {
            echo '<p>No messages.</p>';
        }
        echo '</div>';
    }

    private static function render_history(WP_Post $post): void
    {
        $history = get_post_meta($post->ID, 'history', true) ?: [];
        if ($history) {
            echo '<div class="dtc-messages"><h4>Stage History</h4>';
            foreach ((array) $history as $h) {
                printf('<div class="msg"><strong>%s</strong> <em>%s</em> %s</div>', esc_html($h['stage'] ?? ''), esc_html($h['date'] ?? ''), esc_html($h['note'] ?? ''));
            }
            echo '</div>';
        }
    }

    public static function render_inquiry(WP_Post $post): void
    {
        $meta = fn($k) => esc_html((string) get_post_meta($post->ID, $k, true));
        echo '<table class="widefat striped" style="max-width:760px">';
        foreach (['name' => 'Name', 'email' => 'Email', 'organization' => 'Organization', 'country' => 'Country', 'message' => 'Message'] as $key => $label) {
            echo "<tr><th style='width:160px'>$label</th><td>{$meta($key)}</td></tr>";
        }
        $products = (array) get_post_meta($post->ID, 'products', true);
        $list = implode('<br>', array_map(fn($p) => esc_html(($p['title'] ?? '') . ' × ' . ($p['quantity'] ?? 1)), $products));
        echo "<tr><th>Products</th><td>$list</td></tr></table>";

        wp_nonce_field('dtc_meta', 'dtc_meta_nonce');
        $status = get_post_meta($post->ID, 'status', true) ?: 'new';
        echo '<div class="dtc-field"><label>Status</label><select name="dtc_meta[status]">';
        foreach (['new' => 'New', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost', 'closed' => 'Closed'] as $opt => $label) {
            printf('<option value="%s"%s>%s</option>', $opt, selected($status, $opt, false), $label);
        }
        echo '</select></div>';
    }

    public static function render_application(WP_Post $post): void
    {
        $meta = fn($k) => esc_html((string) get_post_meta($post->ID, $k, true));
        $position = get_post((int) get_post_meta($post->ID, 'position', true));
        echo '<table class="widefat striped" style="max-width:760px">';
        echo '<tr><th style="width:160px">Position</th><td>' . esc_html($position ? get_the_title($position) : '—') . '</td></tr>';
        foreach (['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'cover_letter' => 'Cover Letter'] as $key => $label) {
            echo "<tr><th>$label</th><td>{$meta($key)}</td></tr>";
        }
        $resume = (int) get_post_meta($post->ID, 'resume_media_id', true);
        if ($resume && ($url = wp_get_attachment_url($resume))) {
            echo '<tr><th>Resume</th><td><a href="' . esc_url($url) . '">Download</a></td></tr>';
        }
        echo '</table>';
    }

    // ---------------- saving ----------------

    public static function save(int $post_id, WP_Post $post): void
    {
        if (
            !isset($_POST['dtc_meta_nonce'])
            || !wp_verify_nonce($_POST['dtc_meta_nonce'], 'dtc_meta')
            || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
            || !current_user_can('edit_post', $post_id)
        ) {
            return;
        }

        $input = (array) ($_POST['dtc_meta'] ?? []);

        // Inquiry only exposes its status field.
        if ($post->post_type === 'dtc_inquiry') {
            if (isset($input['status'])) {
                update_post_meta($post_id, 'status', sanitize_key($input['status']));
            }
            return;
        }

        $schema = self::schemas()[$post->post_type] ?? null;
        if (!$schema) {
            return;
        }

        foreach ($schema[1] as $f) {
            $key = $f['key'];
            $raw = $input[$key] ?? null;

            if (!empty($f['virtual'])) {
                self::handle_virtual($post_id, $post, $key, $raw);
                continue;
            }

            $value = match ($f['type']) {
                'text' => sanitize_text_field((string) $raw),
                'textarea' => wp_kses_post((string) $raw),
                'lines' => array_values(array_filter(array_map('trim', explode("\n", (string) $raw)))),
                'select' => sanitize_key((string) $raw),
                'media', 'user' => (int) $raw,
                'media_multi' => array_values(array_filter(array_map('intval', explode(',', (string) $raw)))),
                'post_multi', 'user_multi' => array_values(array_filter(array_map('intval', (array) $raw))),
                'repeater' => array_values(array_filter(array_map(
                    fn($row) => array_map('sanitize_text_field', (array) $row),
                    (array) $raw
                ), fn($row) => implode('', $row) !== '')),
                default => null,
            };

            if ($value !== null) {
                // Track repair stage changes in the history log.
                if ($post->post_type === 'dtc_repair' && $key === 'stage') {
                    $old = get_post_meta($post_id, 'stage', true);
                    if ($old !== $value) {
                        $history = get_post_meta($post_id, 'history', true) ?: [];
                        $history[] = [
                            'stage' => $value,
                            'date' => current_time('mysql', true),
                            'note' => sanitize_text_field((string) ($input['_stage_note'] ?? '')),
                        ];
                        update_post_meta($post_id, 'history', $history);
                    }
                }
                update_post_meta($post_id, $key, $value);
            }
        }

        // Auto-generate an RMA number for new repair cases.
        if ($post->post_type === 'dtc_repair' && !get_post_meta($post_id, 'rma', true)) {
            update_post_meta($post_id, 'rma', 'RMA-' . date('Y') . '-' . $post_id);
        }
    }

    private static function handle_virtual(int $post_id, WP_Post $post, string $key, $raw): void
    {
        if ($key === '_reply' && $post->post_type === 'dtc_ticket' && trim((string) $raw) !== '') {
            $messages = get_post_meta($post_id, 'messages', true) ?: [];
            $messages[] = [
                'author' => wp_get_current_user()->display_name . ' (Support)',
                'body' => sanitize_textarea_field((string) $raw),
                'date' => current_time('mysql', true),
            ];
            update_post_meta($post_id, 'messages', $messages);
        }
    }
}
