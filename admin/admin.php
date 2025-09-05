<?php

/**
 * WP Logo Link Admin functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class WP_Logo_Link_Admin
{

  /**
   * Constructor
   */
  public function __construct()
  {
    add_action('admin_init', array($this, 'add_settings'));
    add_action('admin_footer', array($this, 'admin_scripts'));
    add_action('admin_notices', array($this, 'show_admin_notices'));
  }

  /**
   * Add settings to WordPress admin
   */
  public function add_settings()
  {
    add_settings_section(
      'wpll_settings_section',
      'WP Logo Link Settings',
      array($this, 'settings_section_callback'),
      'general'
    );

    add_settings_field(
      'wpll_right_click_type',
      'Right-click Behavior',
      array($this, 'right_click_type_callback'),
      'general',
      'wpll_settings_section'
    );

    add_settings_field(
      'wpll_assets_url',
      'Assets Page URL',
      array($this, 'assets_url_callback'),
      'general',
      'wpll_settings_section'
    );

    add_settings_field(
      'wpll_custom_text',
      'Custom Link Text',
      array($this, 'custom_text_callback'),
      'general',
      'wpll_settings_section'
    );

    add_settings_field(
      'wpll_custom_url',
      'Custom Link URL',
      array($this, 'custom_url_callback'),
      'general',
      'wpll_settings_section'
    );

    register_setting('general', 'wpll_right_click_type', array($this, 'sanitize_right_click_type'));
    register_setting('general', 'wpll_assets_url', array($this, 'sanitize_url'));
    register_setting('general', 'wpll_custom_text', array($this, 'sanitize_text'));
    register_setting('general', 'wpll_custom_url', array($this, 'sanitize_custom_url'));
  }

  /**
   * Settings section description
   */
  public function settings_section_callback()
  {
    echo '<p>Configure your site logo click behavior. Left-click will always go to your homepage.</p>';
  }

  /**
   * Right-click type selection
   */
  public function right_click_type_callback()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');
?>
    <input type="radio" id="wpll_assets" name="wpll_right_click_type" value="assets" <?php checked($right_click_type, 'assets'); ?>>
    <label for="wpll_assets">Go to Assets/Media Page</label><br>

    <input type="radio" id="wpll_custom" name="wpll_right_click_type" value="custom" <?php checked($right_click_type, 'custom'); ?>>
    <label for="wpll_custom">Custom Link</label><br>

    <p class="description">Choose what happens when users right-click on your logo</p>
<?php
  }

  /**
   * Assets URL field callback
   */
  public function assets_url_callback()
  {
    $assets_url = get_option('wpll_assets_url');
    $right_click_type = get_option('wpll_right_click_type', 'assets');
    $style = ($right_click_type === 'assets') ? '' : 'style="display:none;"';

    echo '<div id="wpll_assets_fields" ' . $style . '>';
    echo '<input type="url" name="wpll_assets_url" value="' . esc_attr($assets_url) . '" class="regular-text">';
    echo '<p class="description">Enter the full URL for your assets/media page (leave empty to use default Media Library)</p>';
    echo '</div>';
  }

  /**
   * Custom text field callback
   */
  public function custom_text_callback()
  {
    $custom_text = get_option('wpll_custom_text');
    $right_click_type = get_option('wpll_right_click_type', 'assets');
    $style = ($right_click_type === 'custom') ? '' : 'style="display:none;"';

    echo '<div id="wpll_custom_fields" ' . $style . '>';
    echo '<input type="text" name="wpll_custom_text" value="' . esc_attr($custom_text) . '" class="regular-text" placeholder="e.g., View Portfolio">';
    echo '<p class="description">Text to display when hovering over the logo (optional)</p>';
  }

  /**
   * Custom URL field callback
   */
  public function custom_url_callback()
  {
    $custom_url = get_option('wpll_custom_url');

    echo '<input type="url" name="wpll_custom_url" value="' . esc_attr($custom_url) . '" class="regular-text" placeholder="https://example.com/portfolio">';
    echo '<p class="description">URL where right-click should redirect</p>';
    echo '</div>'; // Close the div opened in custom_text_callback
  }

  /**
   * Sanitize URL input
   */
  public function sanitize_url($input)
  {
    return esc_url_raw($input);
  }

  /**
   * Sanitize text input
   */
  public function sanitize_text($input)
  {
    return sanitize_text_field($input);
  }

  /**
   * Show admin notices
   */
  public function show_admin_notices()
  {
    settings_errors('wpll_settings');
  }
}
