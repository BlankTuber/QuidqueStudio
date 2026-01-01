/**
 * HTMX Handlers Module
 * Handles HTMX events, loading states, and response processing
 */

import alerts from './alerts.js';

/**
 * Show loading indicator on element
 */
function showLoading(element) {
  element.classList.add('htmx-loading');
  element.setAttribute('aria-busy', 'true');
  
  // Disable form elements
  if (element.matches('form')) {
    element.querySelectorAll('button, input, select, textarea').forEach(el => {
      el.disabled = true;
    });
  }
}

/**
 * Hide loading indicator on element
 */
function hideLoading(element) {
  element.classList.remove('htmx-loading');
  element.setAttribute('aria-busy', 'false');
  
  // Re-enable form elements
  if (element.matches('form')) {
    element.querySelectorAll('button, input, select, textarea').forEach(el => {
      el.disabled = false;
    });
  }
}

/**
 * Process response headers for flash messages
 */
function processResponseHeaders(event) {
  const xhr = event.detail.xhr;
  if (!xhr) return;
  
  // Check for flash message header
  const flashMessage = xhr.getResponseHeader('X-Flash-Message');
  const flashType = xhr.getResponseHeader('X-Flash-Type') || 'info';
  
  if (flashMessage) {
    alerts.show(flashMessage, flashType);
  }
}

/**
 * Handle form validation errors
 */
function handleValidationErrors(event) {
  const xhr = event.detail.xhr;
  if (!xhr || xhr.status !== 422) return;
  
  try {
    const response = JSON.parse(xhr.responseText);
    
    // Clear previous errors
    event.target.querySelectorAll('.field-error').forEach(el => el.remove());
    event.target.querySelectorAll('.input-error').forEach(el => {
      el.classList.remove('input-error');
    });
    
    // Show new errors
    if (response.errors) {
      Object.entries(response.errors).forEach(([field, messages]) => {
        const input = event.target.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add('input-error');
          
          const errorEl = document.createElement('div');
          errorEl.className = 'field-error';
          errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
          
          input.parentNode.appendChild(errorEl);
        }
      });
    }
    
    // Show general error message
    if (response.message) {
      alerts.error(response.message);
    }
  } catch (e) {
    console.error('Failed to parse validation errors:', e);
  }
}

/**
 * Handle successful responses
 */
function handleSuccess(event) {
  const xhr = event.detail.xhr;
  if (!xhr) return;
  
  // Handle redirect header
  const redirect = xhr.getResponseHeader('X-Redirect');
  if (redirect) {
    window.location.href = redirect;
    return;
  }
  
  // Handle refresh header
  if (xhr.getResponseHeader('X-Refresh') === 'true') {
    window.location.reload();
    return;
  }
}

/**
 * Handle errors
 */
function handleError(event) {
  const xhr = event.detail.xhr;
  
  if (!xhr) {
    alerts.error('Network error. Please check your connection.');
    return;
  }
  
  let message = 'An error occurred. Please try again.';
  
  try {
    const response = JSON.parse(xhr.responseText);
    if (response.message) {
      message = response.message;
    }
  } catch (e) {
    // Use default message
  }
  
  // Show error based on status code
  if (xhr.status === 403) {
    message = 'You do not have permission to perform this action.';
  } else if (xhr.status === 404) {
    message = 'The requested resource was not found.';
  } else if (xhr.status >= 500) {
    message = 'A server error occurred. Please try again later.';
  }
  
  alerts.error(message);
}

/**
 * Handle before swap (process content before insertion)
 */
function handleBeforeSwap(event) {
  // Initialize any components in the new content
  // This runs before the content is inserted into the DOM
}

/**
 * Handle after swap (initialize new content)
 */
function handleAfterSwap(event) {
  // Re-initialize components in swapped content
  const target = event.detail.target;
  
  // Focus management - focus first error or first input
  const firstError = target.querySelector('.input-error, .field-error');
  const firstInput = target.querySelector('input:not([type="hidden"]), textarea, select');
  
  if (firstError) {
    const input = firstError.closest('.form-group')?.querySelector('input, textarea, select');
    if (input) input.focus();
  } else if (firstInput && target.matches('form')) {
    firstInput.focus();
  }
}

/**
 * Handle confirm before request
 */
function handleConfirm(event) {
  const element = event.detail.elt;
  const confirmMessage = element.getAttribute('hx-confirm');
  
  if (confirmMessage && !window.htmxConfirmed) {
    event.preventDefault();
    
    import('./modals.js').then(modals => {
      modals.confirm({
        title: element.dataset.confirmTitle || 'Confirm',
        message: confirmMessage,
        type: element.dataset.confirmType || 'warning'
      }).then(confirmed => {
        if (confirmed) {
          window.htmxConfirmed = true;
          htmx.trigger(element, 'click');
          window.htmxConfirmed = false;
        }
      });
    });
  }
}

/**
 * Initialize HTMX handlers
 */
export function init() {
  // Skip if HTMX not available
  if (typeof htmx === 'undefined') {
    console.warn('HTMX not found, skipping HTMX handlers initialization');
    return;
  }
  
  // Loading states
  document.body.addEventListener('htmx:beforeRequest', (e) => {
    showLoading(e.detail.elt);
  });
  
  document.body.addEventListener('htmx:afterRequest', (e) => {
    hideLoading(e.detail.elt);
    processResponseHeaders(e);
  });
  
  // Success/error handling
  document.body.addEventListener('htmx:responseError', handleError);
  document.body.addEventListener('htmx:sendError', handleError);
  document.body.addEventListener('htmx:afterOnLoad', handleSuccess);
  
  // Validation
  document.body.addEventListener('htmx:afterRequest', (e) => {
    if (e.detail.xhr?.status === 422) {
      handleValidationErrors(e);
    }
  });
  
  // Content processing
  document.body.addEventListener('htmx:beforeSwap', handleBeforeSwap);
  document.body.addEventListener('htmx:afterSwap', handleAfterSwap);
  
  // Configure HTMX defaults
  htmx.config.defaultSwapStyle = 'innerHTML';
  htmx.config.defaultSettleDelay = 100;
  htmx.config.historyCacheSize = 10;
  
  // Add CSRF token to all requests
  document.body.addEventListener('htmx:configRequest', (e) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
      e.detail.headers['X-CSRF-Token'] = csrfToken;
    }
    
    // Add request header for AJAX detection
    e.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
  });
}

export default { init };
