<?php
defined('ABSPATH') || exit;

/**
 * Public read-only content endpoints under /dtc/v1:
 * products, brands, categories, solutions, posts, pages, branches, careers.
 * Meta fields use simple post meta keys (compatible with ACF field names).
 */
class DTC_Rest_Content
{
    public static function register_routes(): void
    {
        $public = '__return_true';
        $r = fn(string $route, callable $cb) => register_rest_route(DTC_API_NAMESPACE, $route, [
            'methods' => 'GET',
            'callback' => $cb,
            'permission_callback' => $public,
        ]);

        $r('/products', [self::class, 'products']);
        $r('/products/(?P<slug>[\w-]+)', [self::class, 'product']);
        $r('/product-categories', [self::class, 'product_categories']);
        $r('/brands', [self::class, 'brands']);
        $r('/brands/(?P<slug>[\w-]+)', [self::class, 'brand']);
        $r('/solutions', [self::class, 'solutions']);
        $r('/solutions/(?P<slug>[\w-]+)', [self::class, 'solution']);
        $r('/posts', [self::class, 'posts']);
        $r('/posts/(?P<slug>[\w-]+)', [self::class, 'post']);
        $r('/pages/(?P<slug>[\w-]+)', [self::class, 'page']);
        $r('/branches', [self::class, 'branches']);
        $r('/careers', [self::class, 'careers']);
    }

    // ---------- helpers ----------

    private static function media(?int $id): ?array
    {
        if (!$id) {
            return null;
        }
        $url = wp_get_attachment_image_url($id, 'large');
        if (!$url) {
            return null;
        }
        return [
            'id' => $id,
            'url' => $url,
            'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: '',
        ];
    }

    private static function meta_list(int $post_id, string $key): array
    {
        $value = get_post_meta($post_id, $key, true);
        if (is_array($value)) {
            return $value;
        }
        // Allow newline-separated plain meta as a fallback for manual entry.
        return $value ? array_values(array_filter(array_map('trim', explode("\n", $value)))) : [];
    }

    private static function term_payload(WP_Term $t): array
    {
        return ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'parent' => $t->parent, 'count' => $t->count];
    }

    private static function paged(WP_Query $q, callable $mapper): array
    {
        return [
            'items' => array_map($mapper, $q->posts),
            'total' => (int) $q->found_posts,
            'pages' => (int) $q->max_num_pages,
        ];
    }

    /** Downloads attached to a post, hiding URLs the current user may not access. */
    public static function downloads_for(int $post_id): array
    {
        $ids = get_post_meta($post_id, 'downloads', true);
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_filter(array_map(
            fn($id) => DTC_Rest_Portal::download_payload((int) $id),
            $ids
        )));
    }

    // ---------- products ----------

    public static function product_payload(WP_Post $p, bool $full = false): array
    {
        $brand_terms = get_the_terms($p, 'dtc_brand_tax') ?: [];
        $cats = get_the_terms($p, 'dtc_product_cat') ?: [];

        $data = [
            'id' => $p->ID,
            'slug' => $p->post_name,
            'title' => get_the_title($p),
            'excerpt' => wp_strip_all_tags(get_the_excerpt($p)),
            'image' => self::media((int) get_post_thumbnail_id($p)),
            'brand' => $brand_terms ? self::term_payload($brand_terms[0]) : null,
            'categories' => array_map([self::class, 'term_payload'], is_array($cats) ? $cats : []),
        ];

        if ($full) {
            $gallery_ids = get_post_meta($p->ID, 'gallery', true);
            $specs = get_post_meta($p->ID, 'specifications', true);
            $data += [
                'content' => apply_filters('the_content', $p->post_content),
                'gallery' => array_values(array_filter(array_map(
                    fn($id) => self::media((int) $id),
                    is_array($gallery_ids) ? $gallery_ids : []
                ))),
                'videos' => self::meta_list($p->ID, 'videos'),
                'features' => self::meta_list($p->ID, 'features'),
                'specifications' => is_array($specs) ? $specs : [],
                'applications' => self::meta_list($p->ID, 'applications'),
                'accessories' => array_map('intval', (array) (get_post_meta($p->ID, 'accessories', true) ?: [])),
                'compatible' => array_map('intval', (array) (get_post_meta($p->ID, 'compatible', true) ?: [])),
                'related' => array_map('intval', (array) (get_post_meta($p->ID, 'related', true) ?: [])),
                'downloads' => self::downloads_for($p->ID),
                'certifications' => self::meta_list($p->ID, 'certifications'),
            ];
        }
        return $data;
    }

    public static function products(WP_REST_Request $req)
    {
        $tax_query = [];
        if ($req['brand']) {
            $tax_query[] = ['taxonomy' => 'dtc_brand_tax', 'field' => 'slug', 'terms' => sanitize_title($req['brand'])];
        }
        if ($req['category']) {
            $tax_query[] = ['taxonomy' => 'dtc_product_cat', 'field' => 'slug', 'terms' => sanitize_title($req['category'])];
        }

        $q = new WP_Query([
            'post_type' => 'dtc_product',
            'post_status' => 'publish',
            'posts_per_page' => min(48, (int) ($req['per_page'] ?: 12)),
            'paged' => max(1, (int) ($req['page'] ?: 1)),
            's' => sanitize_text_field($req['search'] ?? ''),
            'tax_query' => $tax_query,
        ]);
        return self::paged($q, fn($p) => self::product_payload($p));
    }

    public static function product(WP_REST_Request $req)
    {
        $post = get_page_by_path(sanitize_title($req['slug']), OBJECT, 'dtc_product');
        if (!$post || $post->post_status !== 'publish') {
            return new WP_Error('not_found', 'Product not found', ['status' => 404]);
        }
        return self::product_payload($post, true);
    }

    public static function product_categories()
    {
        $terms = get_terms(['taxonomy' => 'dtc_product_cat', 'hide_empty' => false]);
        return array_map([self::class, 'term_payload'], is_wp_error($terms) ? [] : $terms);
    }

    // ---------- brands ----------

    private static function brand_payload(WP_Post $p): array
    {
        return [
            'id' => $p->ID,
            'slug' => $p->post_name,
            'title' => get_the_title($p),
            'content' => apply_filters('the_content', $p->post_content),
            'logo' => self::media((int) get_post_thumbnail_id($p)),
            'website' => (string) get_post_meta($p->ID, 'website', true),
            'country' => (string) get_post_meta($p->ID, 'country', true),
        ];
    }

    public static function brands()
    {
        $posts = get_posts(['post_type' => 'dtc_brand', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
        return array_map([self::class, 'brand_payload'], $posts);
    }

    public static function brand(WP_REST_Request $req)
    {
        $post = get_page_by_path(sanitize_title($req['slug']), OBJECT, 'dtc_brand');
        return $post ? self::brand_payload($post) : new WP_Error('not_found', 'Brand not found', ['status' => 404]);
    }

    // ---------- solutions ----------

    private static function solution_payload(WP_Post $p): array
    {
        $cases = get_post_meta($p->ID, 'case_studies', true);
        return [
            'id' => $p->ID,
            'slug' => $p->post_name,
            'title' => get_the_title($p),
            'excerpt' => wp_strip_all_tags(get_the_excerpt($p)),
            'content' => apply_filters('the_content', $p->post_content),
            'image' => self::media((int) get_post_thumbnail_id($p)),
            'architecture' => wp_kses_post((string) get_post_meta($p->ID, 'architecture', true)),
            'components' => self::meta_list($p->ID, 'components'),
            'benefits' => self::meta_list($p->ID, 'benefits'),
            'related_products' => array_map('intval', (array) (get_post_meta($p->ID, 'related_products', true) ?: [])),
            'case_studies' => is_array($cases) ? $cases : [],
            'downloads' => self::downloads_for($p->ID),
        ];
    }

    public static function solutions()
    {
        $posts = get_posts(['post_type' => 'dtc_solution', 'numberposts' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC']);
        return array_map([self::class, 'solution_payload'], $posts);
    }

    public static function solution(WP_REST_Request $req)
    {
        $post = get_page_by_path(sanitize_title($req['slug']), OBJECT, 'dtc_solution');
        return $post ? self::solution_payload($post) : new WP_Error('not_found', 'Solution not found', ['status' => 404]);
    }

    // ---------- blog / pages ----------

    private static function post_payload(WP_Post $p, bool $full = false): array
    {
        $cats = get_the_category($p->ID);
        return [
            'id' => $p->ID,
            'slug' => $p->post_name,
            'title' => get_the_title($p),
            'excerpt' => wp_strip_all_tags(get_the_excerpt($p)),
            'content' => $full ? apply_filters('the_content', $p->post_content) : '',
            'date' => $p->post_date_gmt,
            'author' => get_the_author_meta('display_name', (int) $p->post_author),
            'image' => self::media((int) get_post_thumbnail_id($p)),
            'categories' => array_map([self::class, 'term_payload'], $cats),
        ];
    }

    public static function posts(WP_REST_Request $req)
    {
        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => min(24, (int) ($req['per_page'] ?: 9)),
            'paged' => max(1, (int) ($req['page'] ?: 1)),
            'category_name' => sanitize_title($req['category'] ?? ''),
        ]);
        return self::paged($q, fn($p) => self::post_payload($p));
    }

    public static function post(WP_REST_Request $req)
    {
        $post = get_page_by_path(sanitize_title($req['slug']), OBJECT, 'post');
        return $post && $post->post_status === 'publish'
            ? self::post_payload($post, true)
            : new WP_Error('not_found', 'Post not found', ['status' => 404]);
    }

    public static function page(WP_REST_Request $req)
    {
        $post = get_page_by_path(sanitize_title($req['slug']), OBJECT, 'page');
        return $post && $post->post_status === 'publish'
            ? self::post_payload($post, true)
            : new WP_Error('not_found', 'Page not found', ['status' => 404]);
    }

    // ---------- branches / careers ----------

    public static function branches()
    {
        return array_map(function (WP_Post $p) {
            $departments = get_post_meta($p->ID, 'departments', true);
            return [
                'id' => $p->ID,
                'title' => get_the_title($p),
                'address' => (string) get_post_meta($p->ID, 'address', true),
                'lat' => (float) get_post_meta($p->ID, 'lat', true),
                'lng' => (float) get_post_meta($p->ID, 'lng', true),
                'phone' => (string) get_post_meta($p->ID, 'phone', true),
                'email' => (string) get_post_meta($p->ID, 'email', true),
                'working_hours' => (string) get_post_meta($p->ID, 'working_hours', true),
                'departments' => is_array($departments) ? $departments : [],
            ];
        }, get_posts(['post_type' => 'dtc_branch', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']));
    }

    public static function careers()
    {
        return array_map(fn(WP_Post $p) => [
            'id' => $p->ID,
            'slug' => $p->post_name,
            'title' => get_the_title($p),
            'content' => apply_filters('the_content', $p->post_content),
            'location' => (string) get_post_meta($p->ID, 'location', true),
            'type' => (string) get_post_meta($p->ID, 'type', true) ?: 'full-time',
            'department' => (string) get_post_meta($p->ID, 'department', true),
        ], get_posts(['post_type' => 'dtc_career', 'numberposts' => -1]));
    }
}
