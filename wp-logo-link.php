<?php

/**
 * Plugin Name:             WP Logo Link
 * Plugin URI:              https://github.com/Open-WP-Club/wp-logo-link/
 * Description:             Simply customize your site logo's left and right click behavior.
 * Version:                 1.0.0
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

// Add settings to WordPress admin
function wpll_add_settings() {
    add_settings_section(
        'wpll_settings_section',
        'WP Logo Link Settings',
        'wpll_settings_section_callback',
        'general'
    );

    add_settings_field(
        'wpll_assets_url',
        'Assets Page URL',
        'wpll_assets_url_callback',
        'general',
        'wpll_settings_section'
    );

    register_setting('general', 'wpll_assets_url');
}
add_action('admin_init', 'wpll_add_settings');

// Settings section description
function wpll_settings_section_callback() {
    echo '<p>Configure the right-click destination for your site logo. Left-click will always go to your homepage.</p>';
}

// Assets URL field callback
function wpll_assets_url_callback() {
    $assets_url = get_option('wpll_assets_url');
    echo '<input type="url" name="wpll_assets_url" value="' . esc_attr($assets_url) . '" class="regular-text">';
    echo '<p class="description">Enter the full URL where you want the logo right-click to lead (e.g., your assets or media page)</p>';
}

// Modify logo behavior
function wpll_modify_logo_behavior() {
    $assets_url = get_option('wpll_assets_url');
    if (empty($assets_url)) {
        $assets_url = admin_url('upload.php');
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const siteLogo = document.querySelector('.site-logo, .custom-logo-link');
        if (siteLogo) {
            // Prevent default right-click menu
            siteLogo.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                window.location.href = <?php echo json_encode($assets_url); ?>;
            });

            // Ensure left click goes to homepage
            siteLogo.setAttribute('href', '<?php echo esc_js(home_url('/')); ?>');
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'wpll_modify_logo_behavior');

// Add settings link on plugins page
function wpll_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php#wpll_settings_section') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpll_add_settings_link');