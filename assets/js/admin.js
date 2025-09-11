/**
 * WP Logo Link Admin JavaScript
 * Handles admin interface interactions with lazy loading
 */

(function($) {
  'use strict';

  // Initialize when DOM is ready
  $(document).ready(function() {
    initAdminInterface();
  });

  function initAdminInterface() {
    // Initialize field toggling
    initFieldToggling();
    
    // Initialize URL testing
    initUrlTesting();
    
    // Initialize form validation
    initFormValidation();
  }

  /**
   * Initialize conditional field toggling
   */
  function initFieldToggling() {
    function toggleFields() {
      const selectedType = $('input[name="wpll_right_click_type"]:checked').val();
      
      if (selectedType === 'assets') {
        $('#wpll_assets_fields').slideDown(200);
        $('#wpll_custom_fields').slideUp(200);
      } else if (selectedType === 'custom') {
        $('#wpll_assets_fields').slideUp(200);
        $('#wpll_custom_fields').slideDown(200);
      }
    }
    
    // Toggle on page load
    toggleFields();
    
    // Toggle when radio buttons change
    $('input[name="wpll_right_click_type"]').on('change', function() {
      toggleFields();
      validateForm();
    });
  }

  /**
   * Initialize URL testing functionality
   */
  function initUrlTesting() {
    $('.wpll-test-url').on('click', function(e) {
      e.preventDefault();
      
      const button = $(this);
      const targetField = button.data('target');
      const urlInput = $('input[name="' + targetField + '"]');
      const statusDiv = button.siblings('.wpll-url-status');
      const url = urlInput.val().trim();
      
      if (!url) {
        showUrlStatus(statusDiv, 'error', 'Please enter a URL first');
        return;
      }
      
      // Validate URL format
      if (!isValidUrl(url)) {
        showUrlStatus(statusDiv, 'error', 'Please enter a valid URL');
        return;
      }
      
      testUrl(url, button, statusDiv);
    });
  }

  /**
   * Test URL accessibility
   */
  function testUrl(url, button, statusDiv) {
    const originalText = button.text();
    
    // Update button state
    button.prop('disabled', true).text(wpllAdminConfig.strings.testingConnection);
    showUrlStatus(statusDiv, 'loading', wpllAdminConfig.strings.testingConnection);
    
    // Create a simple test by trying to load the URL in a hidden iframe
    const testFrame = $('<iframe>', {
      src: url,
      style: 'display: none;',
      load: function() {
        // URL loaded successfully
        showUrlStatus(statusDiv, 'success', wpllAdminConfig.strings.connectionSuccessful);
        button.prop('disabled', false).text(originalText);
        $(this).remove();
      },
      error: function() {
        // URL failed to load
        showUrlStatus(statusDiv, 'error', wpllAdminConfig.strings.connectionFailed);
        button.prop('disabled', false).text(originalText);
        $(this).remove();
      }
    });
    
    // Add timeout for the test
    setTimeout(function() {
      if (button.prop('disabled')) {
        showUrlStatus(statusDiv, 'warning', 'Connection test timed out. URL might still work.');
        button.prop('disabled', false).text(originalText);
        testFrame.remove();
      }
    }, 10000);
    
    $('body').append(testFrame);
  }

  /**
   * Show URL test status
   */
  function showUrlStatus(statusDiv, type, message) {
    const iconMap = {
      loading: '⏳',
      success: '✅',
      error: '❌',
      warning: '⚠️'
    };
    
    statusDiv.removeClass('success error warning loading')
           .addClass(type)
           .html(`<span class="wpll-status-icon">${iconMap[type]}</span> ${message}`)
           .fadeIn(200);
  }

  /**
   * Initialize form validation
   */
  function initFormValidation() {
    const form = $('form');
    
    if (form.length) {
      form.on('submit', function(e) {
        if (!validateForm()) {
          e.preventDefault();
          showValidationError();
        }
      });
    }
    
    // Real-time validation
    $('input[name="wpll_custom_url"]').on('input', debounce(validateForm, 500));
  }

  /**
   * Validate form based on current settings
   */
  function validateForm() {
    const selectedType = $('input[name="wpll_right_click_type"]:checked').val();
    
    if (selectedType === 'custom') {
      const customUrl = $('input[name="wpll_custom_url"]').val().trim();
      
      if (!customUrl) {
        return false;
      }
      
      if (!isValidUrl(customUrl)) {
        return false;
      }
    }
    
    return true;
  }

  /**
   * Show validation error message
   */
  function showValidationError() {
    const errorMessage = $('<div class="notice notice-error is-dismissible"><p>Please fix the validation errors before saving.</p></div>');
    
    $('.wrap h1').after(errorMessage);
    
    setTimeout(function() {
      errorMessage.fadeOut(function() {
        $(this).remove();
      });
    }, 5000);
  }

  /**
   * Validate URL format
   */
  function isValidUrl(string) {
    try {
      new URL(string);
      return true;
    } catch (_) {
      return false;
    }
  }

  /**
   * Debounce function for performance
   */
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

})(jQuery);