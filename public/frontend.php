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

  /**
   * Constructor
   */
  public function __construct()
  {
    add_action('wp_footer', array($this, 'add_logo_behavior'));
  }

  /**
   * Add logo click behavior to frontend
   */
  public function add_logo_behavior()
  {
    $right_click_type = get_option('wpll_right_click_type', 'assets');
    $custom_text = get_option('wpll_custom_text');

    // Determine redirect URL based on settings
    $redirect_url = $this->get_redirect_url($right_click_type);

    // Don't add behavior if no redirect URL is available
    if (empty($redirect_url)) {
      return;
    }

    $this->output_logo_script($redirect_url, $right_click_type, $custom_text);
  }

  /**
   * Get the redirect URL based on settings
   */
  private function get_redirect_url($right_click_type)
  {
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
   * Output the JavaScript for logo behavior
   */
  private function output_logo_script($redirect_url, $right_click_type, $custom_text)
  {
?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const siteLogo = document.querySelector('.site-logo, .custom-logo-link, .site-logo a, .custom-logo');
        if (siteLogo) {
          // Add custom text as title attribute if provided
          <?php if ($right_click_type === 'custom' && !empty($custom_text)): ?>
            siteLogo.setAttribute('title', <?php echo json_encode($custom_text); ?>);
          <?php endif; ?>

          // Prevent default right-click menu and redirect
          siteLogo.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            window.location.href = <?php echo json_encode($redirect_url); ?>;
          });

          // Ensure left click goes to homepage
          if (siteLogo.tagName.toLowerCase() === 'a') {
            siteLogo.setAttribute('href', <?php echo json_encode(home_url('/')); ?>);
          } else {
            siteLogo.addEventListener('click', function(e) {
              if (e.button === 0) { // Left click
                window.location.href = <?php echo json_encode(home_url('/')); ?>;
              }
            });
          }
        }
      });
    </script>
<?php
  }

  /**
   * Get logo selectors for different themes
   */
  public function get_logo_selectors()
  {
    $selectors = apply_filters('wpll_logo_selectors', array(
      '.site-logo',
      '.custom-logo-link',
      '.site-logo a',
      '.custom-logo',
      '.site-branding a',
      '.logo a',
      '.header-logo a'
    ));

    return implode(', ', $selectors);
  }
}
