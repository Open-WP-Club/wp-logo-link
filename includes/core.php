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
    // Set default options
    if (!get_option('wpll_right_click_type')) {
      update_option('wpll_right_click_type', 'assets');
    }
  }

  /**
   * Plugin deactivation
   */
  public function deactivate()
  {
    // Clean up if needed
  }

  /**
   * Get plugin version
   */
  public function get_version()
  {
    return WPLL_VERSION;
  }
}
