<?php
/**
 * Plugin Name: DTC Headless
 * Description: Headless backend for the defense technology company website — custom post types, REST API (dtc/v1), JWT authentication, inquiry system, support tickets, repair tracking and permission-based downloads.
 * Version: 1.0.0
 * Author: DTC
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('DTC_VERSION', '1.0.0');
define('DTC_PATH', plugin_dir_path(__FILE__));
define('DTC_API_NAMESPACE', 'dtc/v1');

require_once DTC_PATH . 'includes/class-dtc-post-types.php';
require_once DTC_PATH . 'includes/class-dtc-roles.php';
require_once DTC_PATH . 'includes/class-dtc-jwt.php';
require_once DTC_PATH . 'includes/class-dtc-settings.php';
require_once DTC_PATH . 'includes/class-dtc-rest-content.php';
require_once DTC_PATH . 'includes/class-dtc-rest-forms.php';
require_once DTC_PATH . 'includes/class-dtc-rest-portal.php';
require_once DTC_PATH . 'includes/class-dtc-security.php';
require_once DTC_PATH . 'includes/class-dtc-admin.php';

register_activation_hook(__FILE__, function () {
    DTC_Post_Types::register();
    DTC_Roles::add_roles();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

add_action('init', ['DTC_Post_Types', 'register']);
add_action('rest_api_init', ['DTC_Rest_Content', 'register_routes']);
add_action('rest_api_init', ['DTC_Rest_Forms', 'register_routes']);
add_action('rest_api_init', ['DTC_Rest_Portal', 'register_routes']);
add_action('rest_api_init', ['DTC_JWT', 'register_routes']);
add_action('rest_api_init', ['DTC_Settings', 'register_routes']);
DTC_JWT::init();
DTC_Security::init();
DTC_Admin::init();
