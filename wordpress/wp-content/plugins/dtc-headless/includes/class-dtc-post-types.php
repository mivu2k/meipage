<?php
defined('ABSPATH') || exit;

/**
 * Registers all custom post types and taxonomies for the catalog,
 * solutions, downloads, support and company structure.
 */
class DTC_Post_Types
{
    public static function register(): void
    {
        $types = [
            'dtc_product' => ['Products', 'Product', 'dashicons-products', true],
            'dtc_brand' => ['Brands', 'Brand', 'dashicons-awards', true],
            'dtc_solution' => ['Solutions', 'Solution', 'dashicons-networking', true],
            'dtc_download' => ['Downloads', 'Download', 'dashicons-download', false],
            'dtc_branch' => ['Branches', 'Branch', 'dashicons-location', false],
            'dtc_career' => ['Careers', 'Career', 'dashicons-groups', true],
            'dtc_testimonial' => ['Testimonials', 'Testimonial', 'dashicons-format-quote', false],
            'dtc_partner' => ['Partners', 'Partner', 'dashicons-businessperson', false],
            'dtc_inquiry' => ['Inquiries', 'Inquiry', 'dashicons-cart', false],
            'dtc_ticket' => ['Tickets', 'Ticket', 'dashicons-sos', false],
            'dtc_repair' => ['Repairs', 'Repair', 'dashicons-hammer', false],
            'dtc_application' => ['Applications', 'Application', 'dashicons-id', false],
        ];

        foreach ($types as $slug => [$plural, $singular, $icon, $public]) {
            register_post_type($slug, [
                'labels' => [
                    'name' => $plural,
                    'singular_name' => $singular,
                ],
                'public' => $public,
                'show_ui' => true,
                'show_in_rest' => true, // Gutenberg + wp/v2 API in admin
                'menu_icon' => $icon,
                'has_archive' => false,
                'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
                'capability_type' => 'post',
                'rewrite' => ['slug' => str_replace('dtc_', '', $slug)],
            ]);
        }

        // Hierarchical product categories (category → subcategory)
        register_taxonomy('dtc_product_cat', 'dtc_product', [
            'labels' => ['name' => 'Product Categories', 'singular_name' => 'Product Category'],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'product-category'],
        ]);

        // Brand relation as taxonomy for fast filtering (the dtc_brand CPT holds the page content)
        register_taxonomy('dtc_brand_tax', ['dtc_product', 'dtc_download', 'dtc_solution'], [
            'labels' => ['name' => 'Brand (relation)', 'singular_name' => 'Brand'],
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'brand'],
        ]);

        // Download type + access level
        register_taxonomy('dtc_download_type', 'dtc_download', [
            'labels' => ['name' => 'Download Types', 'singular_name' => 'Download Type'],
            'hierarchical' => false,
            'show_in_rest' => true,
        ]);
    }
}
