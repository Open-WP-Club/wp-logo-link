<?php

/**
 * WP Logo Link Admin functionality with lazy loading optimizations
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
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('admin_notices', array($this, 'show_admin_notices'));

    // Clear frontend cache when settings change
    add_action('update_option_wpll_right_click_type', array($this, 'clear_frontend_cache'));
    add_action('update_option_wpll_assets_url', array($this, 'clear_frontend_cache'));
    add_action('update_option_wpll_custom_url', array($this, 'clear_frontend_cache'));
  }

  /**
   * Lazy load admin scripts only on relevant pages
   */
  public function enqueue_admin_scripts($hook)
  {
    // Only load on general settings page
    if ($hook !== 'options-general.php') {
      return;
    }

    // Enqueue admin JavaScript
    wp_enqueue_script(
      'wpll-admin',
      WPLL_PLUGIN_URL . 'assets/js/admin.js',
      array('jquery'),
      WPLL_VERSION,
      true
    );

    // Enqueue admin CSS
    wp_enqueue_style(
      'wpll-admin',
      WPLL_PLUGIN_URL . 'assets/css/admin.css',
      array(),
      WPLL_VERSION
    );

    // Localize script with admin configuration
    wp_localize_script('wpll-admin', 'wpllAdminConfig', array(
      'nonce' => wp_create_nonce('wpll_admin_nonce'),
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'strings' => array(
        'testingConnection' => __('Testing connection...', 'wp-logo-link'),
        'connectionSuccessful' => __('Connection successful!', 'wp-logo-link'),
        'connectionFailed' => __('Connection failed. Please check the URL.', 'wp-logo-link'),
      )
    ));
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
    echo '<p class="wpll-performance-info">';
    echo '<small><strong>Performance:</strong> Scripts are loaded conditionally only when needed.</small>';
    echo '</p>';
  }

  /**
   * Right-click type selection
   */
  public function right_click_type_callback()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');
?>
    <fieldset>
      <label class="wpll-radio-option">
        <input type="radio" id="wpll_assets" name="wpll_right_click_type" value="assets" <?php checked($right_click_type, 'assets'); ?>>
        <span class="wpll-radio-label">Go to Assets/Media Page</span>
      </label>

      <label class="wpll-radio-option">
        <input type="radio" id="wpll_custom" name="wpll_right_click_type" value="custom" <?php checked($right_click_type, 'custom'); ?>>
        <span class="wpll-radio-label">Custom Link</span>
      </label>
    </fieldset>
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

    echo '<div id="wpll_assets_fields" class="wpll-conditional-field" ' . $style . '>';
    echo '<input type="url" name="wpll_assets_url" value="' . esc_attr($assets_url) . '" class="regular-text wpll-url-input" placeholder="https://example.com/assets">';
    echo '<button type="button" class="button wpll-test-url" data-target="wpll_assets_url">Test URL</button>';
    echo '<div class="wpll-url-status"></div>';
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

    echo '<div id="wpll_custom_fields" class="wpll-conditional-field" ' . $style . '>';
    echo '<input type="text" name="wpll_custom_text" value="' . esc_attr($custom_text) . '" class="regular-text" placeholder="e.g., View Portfolio">';
    echo '<p class="description">Text to display when hovering over the logo (optional)</p>';
  }

  /**
   * Custom URL field callback
   */
  public function custom_url_callback()
  {
    $custom_url = get_option('wpll_custom_url');

    echo '<input type="url" name="wpll_custom_url" value="' . esc_attr($custom_url) . '" class="regular-text wpll-url-input" placeholder="https://example.com/portfolio">';
    echo '<button type="button" class="button wpll-test-url" data-target="wpll_custom_url">Test URL</button>';
    echo '<div class="wpll-url-status"></div>';
    echo '<p class="description">URL where right-click should redirect</p>';
    echo '</div>'; // Close the div opened in custom_text_callback
  }

  /**
   * Clear frontend cache when settings change
   */
  public function clear_frontend_cache()
  {
    delete_transient('wpll_logo_selector_cache');

    // Also clear any page caches if popular caching plugins are active
    if (function_exists('w3tc_flush_all')) {
      w3tc_flush_all();
    }
    if (function_exists('wp_cache_clear_cache')) {
      wp_cache_clear_cache();
    }
    if (function_exists('rocket_clean_domain')) {
      rocket_clean_domain();
    }
  }

  /**
   * Sanitize right-click type input
   */
  public function sanitize_right_click_type($input)
  {
    $valid_types = array('assets', 'custom');
    return in_array($input, $valid_types) ? $input : 'assets';
  }

  /**
   * Sanitize URL input
   */
  public function sanitize_url($input)
  {
    return esc_url_raw($input);
  }

  /**
   * Sanitize custom URL with validation
   */
  public function sanitize_custom_url($input)
  {
    $url = esc_url_raw($input);

    // If custom type is selected and URL is empty, show error
    if (get_option('wpll_right_click_type') === 'custom' && empty($url)) {
      add_settings_error(
        'wpll_custom_url',
        'wpll_custom_url_empty',
        'Custom URL is required when using custom link behavior.',
        'error'
      );
    }

    return $url;
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
