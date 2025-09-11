<?php

/**
 * Main WP Logo Link Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class WP_Logo_Link
{

  /**
   * Admin instance
   */
  public $admin;

  /**
   * Frontend instance
   */
  public $frontend;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->init_hooks();
    $this->init_components();
  }

  /**
   * Initialize hooks
   */
  private function init_hooks()
  {
    register_activation_hook(WPLL_PLUGIN_DIR . 'wp-logo-link.php', array($this, 'activate'));
    register_deactivation_hook(WPLL_PLUGIN_DIR . 'wp-logo-link.php', array($this, 'deactivate'));
  }

  /**
   * Initialize components
   */
  private function init_components()
  {
    // Initialize admin
    if (is_admin()) {
      $this->admin = new WP_Logo_Link_Admin();
    }

    // Initialize frontend
    if (!is_admin()) {
      $this->frontend = new WP_Logo_Link_Frontend();
    }
  }

  /**
   * Plugin activation
   */
  public function activate()
  {
    // Set default options only if they don't exist
    if (!get_option('wpll_right_click_type')) {
      update_option('wpll_right_click_type', 'assets');
    }
    if (!get_option('wpll_assets_url')) {
      update_option('wpll_assets_url', '');
    }
    if (!get_option('wpll_custom_text')) {
      update_option('wpll_custom_text', '');
    }
    if (!get_option('wpll_custom_url')) {
      update_option('wpll_custom_url', '');
    }
  }

  /**
   * Plugin deactivation
   */
  public function deactivate()
  {
    // Clean up if needed - keeping options for now in case of reactivation
    // Uncomment below to remove all plugin options on deactivation
    
    delete_option('wpll_right_click_type');
    delete_option('wpll_assets_url');
    delete_option('wpll_custom_text');
    delete_option('wpll_custom_url');
    
  }

  /**
   * Get plugin version
   */
  public function get_version()
  {
    return WPLL_VERSION;
  }
}
