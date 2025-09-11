<?php

/**
 * WP Logo Link Frontend functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class WP_Logo_Link_Frontend
{
  private $cache_key = 'wpll_logo_selector_cache';
  private $cache_duration = 24 * HOUR_IN_SECONDS; // 24 hours

  /**
   * Constructor
   */
  public function __construct()
  {
    add_action('wp_enqueue_scripts', array($this, 'conditionally_enqueue_scripts'));
    add_action('switch_theme', array($this, 'clear_logo_cache'));
    add_action('customize_save_after', array($this, 'clear_logo_cache'));
  }

  /**
   * Conditionally enqueue scripts only when needed
   */
  public function conditionally_enqueue_scripts()
  {
    // Early exit if no valid configuration
    if (!$this->should_load_scripts()) {
      return;
    }

    // Check if logo exists on this page
    if (!$this->logo_exists_on_page()) {
      return;
    }

    // Enqueue our scripts and styles
    wp_enqueue_script(
      'wpll-frontend',
      WPLL_PLUGIN_URL . 'assets/js/frontend.js',
      array(),
      WPLL_VERSION,
      true
    );

    wp_enqueue_style(
      'wpll-frontend',
      WPLL_PLUGIN_URL . 'assets/css/frontend.css',
      array(),
      WPLL_VERSION
    );

    // Localize script with configuration
    wp_localize_script('wpll-frontend', 'wpllConfig', array(
      'homeUrl' => home_url('/'),
      'redirectUrl' => $this->get_redirect_url(),
      'menuLabel' => $this->get_menu_label(),
      'rightClickType' => get_option('wpll_right_click_type', 'assets'),
      'customText' => get_option('wpll_custom_text', ''),
      'logoSelector' => $this->get_cached_logo_selector()
    ));
  }

  /**
   * Check if scripts should be loaded based on configuration
   */
  private function should_load_scripts()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');

    // If custom type but no URL set, don't load
    if ($right_click_type === 'custom') {
      $custom_url = get_option('wpll_custom_url');
      if (empty($custom_url)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Check if logo exists on current page with caching
   */
  private function logo_exists_on_page()
  {
    // Use cached selector if available
    $cached_selector = $this->get_cached_logo_selector();

    if ($cached_selector) {
      return true; // If we have a cached selector, assume logo exists
    }

    // If no cache, we'll need to rely on common selectors
    // In a real implementation, this could be enhanced with AJAX detection
    return true;
  }

  /**
   * Get cached logo selector or detect and cache it
   */
  private function get_cached_logo_selector()
  {
    $cached_selector = get_transient($this->cache_key);

    if ($cached_selector !== false) {
      return $cached_selector;
    }

    // Detect logo selector and cache it
    $detected_selector = $this->detect_logo_selector();

    if ($detected_selector) {
      set_transient($this->cache_key, $detected_selector, $this->cache_duration);
      return $detected_selector;
    }

    return false;
  }

  /**
   * Detect which logo selector works on current theme
   */
  private function detect_logo_selector()
  {
    $selectors = $this->get_logo_selectors();

    // For now, return the first selector
    // In a more advanced implementation, this could use headless browser detection
    // or analyze the theme's structure
    return $selectors[0] ?? '.custom-logo-link';
  }

  /**
   * Clear logo selector cache
   */
  public function clear_logo_cache()
  {
    delete_transient($this->cache_key);
  }

  /**
   * Get the redirect URL based on settings
   */
  private function get_redirect_url()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');

    if ($right_click_type === 'custom') {
      $redirect_url = get_option('wpll_custom_url');
      if (empty($redirect_url)) {
        return '';
      }
    } else {
      $redirect_url = get_option('wpll_assets_url');
      if (empty($redirect_url)) {
        $redirect_url = admin_url('upload.php');
      }
    }

    return $redirect_url;
  }

  /**
   * Get the menu label for the second option
   */
  private function get_menu_label()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');
    $custom_text = get_option('wpll_custom_text');

    if ($right_click_type === 'custom') {
      return !empty($custom_text) ? $custom_text : 'Custom Link';
    } else {
      return 'Media Library';
    }
  }

  /**
   * Get logo selectors for different themes
   */
  public function get_logo_selectors()
  {
    $selectors = apply_filters('wpll_logo_selectors', array(
      '.custom-logo-link',
      '.site-logo',
      '.site-logo a',
      '.custom-logo',
      '.site-branding a',
      '.logo a',
      '.header-logo a',
      '.navbar-brand',
      '.brand',
      '[class*="logo"] a'
    ));

    return $selectors;
  }

  /**
   * Legacy method for backward compatibility
   * This is now replaced by conditional script loading
   */
  public function add_logo_behavior()
  {
    // This method is kept for backward compatibility but functionality
    // has been moved to conditionally_enqueue_scripts
  }
}
