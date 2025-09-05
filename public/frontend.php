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
    $home_url = home_url('/');
    $menu_label = $this->get_menu_label($right_click_type, $custom_text);
?>
    <style>
      #wpll-context-menu {
        position: fixed;
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        padding: 8px 0;
        z-index: 9999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 14px;
        min-width: 180px;
        display: none;
      }

      #wpll-context-menu a {
        display: block;
        padding: 8px 16px;
        color: #333;
        text-decoration: none;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        box-sizing: border-box;
      }

      #wpll-context-menu a:hover {
        background-color: #f5f5f5;
        color: #000;
      }

      #wpll-context-menu .wpll-menu-icon {
        margin-right: 8px;
        font-style: normal;
      }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const siteLogo = document.querySelector('.site-logo, .custom-logo-link, .site-logo a, .custom-logo');
        if (siteLogo) {
          // Create custom context menu
          const contextMenu = document.createElement('div');
          contextMenu.id = 'wpll-context-menu';
          contextMenu.innerHTML = `
                    <a href="<?php echo esc_js($home_url); ?>" class="wpll-home-link">
                        <span class="wpll-menu-icon">🏠</span>Go to Homepage
                    </a>
                    <a href="<?php echo esc_js($redirect_url); ?>" class="wpll-custom-link">
                        <span class="wpll-menu-icon"><?php echo $right_click_type === 'custom' ? '🔗' : '📁'; ?></span><?php echo esc_js($menu_label); ?>
                    </a>
                `;
          document.body.appendChild(contextMenu);

          // Show context menu on right-click
          siteLogo.addEventListener('contextmenu', function(e) {
            e.preventDefault();

            // Position the menu near the cursor
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.display = 'block';

            // Adjust position if menu goes off screen
            const rect = contextMenu.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
              contextMenu.style.left = (e.pageX - rect.width) + 'px';
            }
            if (rect.bottom > window.innerHeight) {
              contextMenu.style.top = (e.pageY - rect.height) + 'px';
            }
          });

          // Hide context menu when clicking elsewhere
          document.addEventListener('click', function(e) {
            if (!contextMenu.contains(e.target)) {
              contextMenu.style.display = 'none';
            }
          });

          // Hide context menu on escape key
          document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
              contextMenu.style.display = 'none';
            }
          });

          // Ensure left click goes to homepage
          if (siteLogo.tagName.toLowerCase() === 'a') {
            siteLogo.setAttribute('href', <?php echo json_encode($home_url); ?>);
          } else {
            siteLogo.addEventListener('click', function(e) {
              if (e.button === 0) { // Left click
                window.location.href = <?php echo json_encode($home_url); ?>;
              }
            });
          }

          // Add custom text as title attribute if provided
          <?php if ($right_click_type === 'custom' && !empty($custom_text)): ?>
            siteLogo.setAttribute('title', 'Left-click: Homepage | Right-click: <?php echo esc_js($custom_text); ?>');
          <?php else: ?>
            siteLogo.setAttribute('title', 'Left-click: Homepage | Right-click: More options');
          <?php endif; ?>
        }
      });
    </script>
<?php
  }

  /**
   * Get the menu label for the second option
   */
  private function get_menu_label($right_click_type, $custom_text)
  {
    if ($right_click_type === 'custom') {
      return !empty($custom_text) ? $custom_text : 'Custom Link';
    } else {
      return 'Go to Media Library';
    }
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
