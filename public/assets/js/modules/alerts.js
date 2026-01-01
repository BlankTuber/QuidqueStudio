/**
 * Alerts Module
 * Handles flash messages, notifications, and auto-dismissal
 */

const DEFAULT_DURATION = 5000;
const FADE_OUT_DURATION = 300;

/**
 * Dismiss an alert
 */
function dismissAlert(alert) {
  // Add fade-out animation
  alert.style.opacity = '0';
  alert.style.transform = 'translateX(20px)';
  alert.style.transition = `opacity ${FADE_OUT_DURATION}ms, transform ${FADE_OUT_DURATION}ms`;
  
  setTimeout(() => {
    alert.remove();
  }, FADE_OUT_DURATION);
}

/**
 * Setup auto-dismiss for an alert
 */
function setupAutoDismiss(alert) {
  const duration = parseInt(alert.dataset.autoDismiss) || DEFAULT_DURATION;
  
  if (duration > 0) {
    // Add progress bar if enabled
    if (alert.dataset.showProgress !== 'false') {
      const progress = document.createElement('div');
      progress.className = 'alert-progress';
      progress.style.cssText = `
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: currentColor;
        opacity: 0.3;
        width: 100%;
        animation: alert-progress ${duration}ms linear forwards;
      `;
      alert.style.position = 'relative';
      alert.style.overflow = 'hidden';
      alert.appendChild(progress);
    }
    
    setTimeout(() => dismissAlert(alert), duration);
  }
}

/**
 * Create and show a new alert
 */
export function show(message, type = 'info', options = {}) {
  const {
    duration = DEFAULT_DURATION,
    dismissible = true,
    container = document.querySelector('.alerts-container') || document.body
  } = options;
  
  const alert = document.createElement('div');
  alert.className = `alert alert--${type}`;
  alert.setAttribute('role', type === 'error' ? 'alert' : 'status');
  alert.dataset.autoDismiss = duration;
  
  alert.innerHTML = `
    <span class="alert-message">${message}</span>
    ${dismissible ? '<button class="alert-dismiss" aria-label="Dismiss">&times;</button>' : ''}
  `;
  
  container.appendChild(alert);
  
  // Trigger animation
  requestAnimationFrame(() => {
    alert.style.opacity = '1';
    alert.style.transform = 'translateX(0)';
  });
  
  if (duration > 0) {
    setupAutoDismiss(alert);
  }
  
  return alert;
}

/**
 * Show success alert
 */
export function success(message, options = {}) {
  return show(message, 'success', options);
}

/**
 * Show error alert
 */
export function error(message, options = {}) {
  return show(message, 'error', options);
}

/**
 * Show warning alert
 */
export function warning(message, options = {}) {
  return show(message, 'warning', options);
}

/**
 * Show info alert
 */
export function info(message, options = {}) {
  return show(message, 'info', options);
}

/**
 * Dismiss all alerts
 */
export function dismissAll() {
  document.querySelectorAll('.alert').forEach(dismissAlert);
}

/**
 * Initialize alerts module
 */
export function init() {
  // Add CSS for progress animation
  if (!document.getElementById('alert-styles')) {
    const style = document.createElement('style');
    style.id = 'alert-styles';
    style.textContent = `
      @keyframes alert-progress {
        from { width: 100%; }
        to { width: 0%; }
      }
      .alert {
        opacity: 0;
        transform: translateX(20px);
        transition: opacity 300ms, transform 300ms;
      }
    `;
    document.head.appendChild(style);
  }
  
  // Handle dismiss button clicks
  document.addEventListener('click', (e) => {
    const dismissBtn = e.target.closest('.alert-dismiss, [data-alert-dismiss]');
    if (dismissBtn) {
      const alert = dismissBtn.closest('.alert');
      if (alert) dismissAlert(alert);
    }
  });
  
  // Setup auto-dismiss for existing alerts
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(setupAutoDismiss);
}

export default { init, show, success, error, warning, info, dismissAll };
