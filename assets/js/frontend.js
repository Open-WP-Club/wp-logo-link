/**
 * WP Logo Link Frontend JavaScript
 * Handles logo click behavior
 */

(function() {
  'use strict';

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogoLink);
  } else {
    initLogoLink();
  }

  function initLogoLink() {
    // Get configuration from localized script
    if (typeof wpllConfig === 'undefined') {
      console.warn('WP Logo Link: Configuration not found');
      return;
    }

    const config = wpllConfig;
    
    // Find logo element using cached selector first, then fallback to detection
    const siteLogo = findLogoElement(config.logoSelector);
    
    if (!siteLogo) {
      console.warn('WP Logo Link: Logo element not found');
      return;
    }

    // Validate configuration
    if (!config.redirectUrl) {
      console.warn('WP Logo Link: No redirect URL configured');
      return;
    }

    setupLogoInteractions(siteLogo, config);
  }

  function findLogoElement(cachedSelector) {
    // Try cached selector first
    if (cachedSelector) {
      const element = document.querySelector(cachedSelector);
      if (element) {
        return element;
      }
    }

    // Fallback to common selectors
    const selectors = [
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
    ];

    for (const selector of selectors) {
      const element = document.querySelector(selector);
      if (element) {
        return element;
      }
    }

    return null;
  }

  function setupLogoInteractions(siteLogo, config) {
    let contextMenu = null;

    // Create context menu lazily (only when needed)
    function createContextMenu() {
      if (contextMenu) {
        return contextMenu;
      }

      contextMenu = document.createElement('div');
      contextMenu.id = 'wpll-context-menu';
      contextMenu.className = 'wpll-context-menu';
      contextMenu.innerHTML = `
        <a href="${escapeHtml(config.homeUrl)}" class="wpll-home-link">
          Homepage
        </a>
        <a href="${escapeHtml(config.redirectUrl)}" class="wpll-custom-link">
          ${escapeHtml(config.menuLabel)}
        </a>
      `;
      
      document.body.appendChild(contextMenu);
      return contextMenu;
    }

    // Show context menu on right-click
    siteLogo.addEventListener('contextmenu', function(e) {
      e.preventDefault();
      
      const menu = createContextMenu();
      showContextMenu(menu, e);
    });

    // Hide context menu when clicking elsewhere
    document.addEventListener('click', function(e) {
      if (contextMenu && !contextMenu.contains(e.target)) {
        hideContextMenu();
      }
    });

    // Hide context menu on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && contextMenu) {
        hideContextMenu();
      }
    });

    // Ensure left click goes to homepage
    setupLeftClick(siteLogo, config.homeUrl);

    // Add tooltip
    setupTooltip(siteLogo, config);
  }

  function showContextMenu(menu, event) {
    // Position the menu near the cursor
    menu.style.left = event.pageX + 'px';
    menu.style.top = event.pageY + 'px';
    menu.style.display = 'block';

    // Adjust position if menu goes off screen
    requestAnimationFrame(() => {
      const rect = menu.getBoundingClientRect();
      
      if (rect.right > window.innerWidth) {
        menu.style.left = (event.pageX - rect.width) + 'px';
      }
      
      if (rect.bottom > window.innerHeight) {
        menu.style.top = (event.pageY - rect.height) + 'px';
      }
    });
  }

  function hideContextMenu() {
    if (contextMenu) {
      contextMenu.style.display = 'none';
    }
  }

  function setupLeftClick(siteLogo, homeUrl) {
    if (siteLogo.tagName.toLowerCase() === 'a') {
      siteLogo.setAttribute('href', homeUrl);
    } else {
      siteLogo.addEventListener('click', function(e) {
        if (e.button === 0) { // Left click
          window.location.href = homeUrl;
        }
      });
    }
  }

  function setupTooltip(siteLogo, config) {
    let tooltipText = 'Left-click: Homepage | Right-click: More options';
    
    if (config.rightClickType === 'custom' && config.customText) {
      tooltipText = `Left-click: Homepage | Right-click: ${config.customText}`;
    }
    
    siteLogo.setAttribute('title', tooltipText);
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

})();