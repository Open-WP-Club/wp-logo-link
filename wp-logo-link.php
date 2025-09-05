<?php

/**
 * Plugin Name:             WP Logo Link
 * Plugin URI:              https://github.com/Open-WP-Club/wp-logo-link/
 * Description:             Simply customize your site logo's left and right click behavior.
 * Version:                 1.1.0
 * Author:                  Open WP Club
 * Author URI:              https://openwpclub.com
 * License:                 GPL-2.0 License
 * Requires at least:       6.0
 * Requires PHP:            7.4
 * Tested up to:            6.6.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPLL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPLL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPLL_VERSION', '1.1.0');

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
