<?php

/**
 * Plugin Name:             WP Logo Link
 * Plugin URI:              https://github.com/Open-WP-Club/wp-logo-link/
 * Description:             Simply customize your site logo's left and right click behavior.
 * Version:                 1.2.0
 * Author:                  Open WP Club
 * Author URI:              https://openwpclub.com
 * License:                 GPL-2.0 License
 * Requires at least:       6.0
 * Requires PHP:            7.4
 * Tested up to:            6.6.2
 * Text Domain:             wp-logo-link
 * Domain Path:             /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPLL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPLL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPLL_VERSION', '1.2.0');

// Include required files
require_once WPLL_PLUGIN_DIR . 'includes/core.php';
require_once WPLL_PLUGIN_DIR . 'admin/admin.php';
require_once WPLL_PLUGIN_DIR . 'public/frontend.php';

// Initialize the plugin
function wpll_init()
{
    new WP_Logo_Link();
}
add_action('plugins_loaded', 'wpll_init');

// Add settings link on plugins page
function wpll_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php#wpll_settings_section') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpll_add_settings_link');

// Create assets directory on activation
function wpll_create_assets_directory()
{
    $assets_dir = WPLL_PLUGIN_DIR . 'assets';
    $js_dir = $assets_dir . '/js';
    $css_dir = $assets_dir . '/css';

    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }

    if (!file_exists($js_dir)) {
        wp_mkdir_p($js_dir);
    }

    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
}
register_activation_hook(__FILE__, 'wpll_create_assets_directory');

// Load text domain for translations
function wpll_load_textdomain()
{
    load_plugin_textdomain('wp-logo-link', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wpll_load_textdomain');

// Add debug information function for troubleshooting
function wpll_debug_info()
{
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $debug_info = array(
        'Plugin Version' => WPLL_VERSION,
        'WordPress Version' => get_bloginfo('version'),
        'PHP Version' => PHP_VERSION,
        'Theme' => get_template(),
        'Active Plugins' => count(get_option('active_plugins', array())),
        'Logo Cache Status' => get_transient('wpll_logo_selector_cache') ? 'Cached' : 'Not Cached',
        'Settings' => array(
            'Right Click Type' => get_option('wpll_right_click_type', 'Not Set'),
            'Assets URL' => get_option('wpll_assets_url', 'Not Set'),
            'Custom URL' => get_option('wpll_custom_url', 'Not Set'),
            'Custom Text' => get_option('wpll_custom_text', 'Not Set'),
        )
    );

    error_log('WP Logo Link Debug Info: ' . print_r($debug_info, true));
}

// Add debug hook for administrators (properly hooked after init)
function wpll_init_debug_hook()
{
    if (current_user_can('manage_options')) {
        add_action('wp_footer', function () {
            if (isset($_GET['wpll_debug'])) {
                wpll_debug_info();
            }
        });
    }
}
add_action('init', 'wpll_init_debug_hook');
