/**
 * Modals Module
 * Handles modal dialogs, confirm dialogs, and drawers
 */

const ACTIVE_CLASS = 'active';
let activeModal = null;
let previouslyFocused = null;

/**
 * Open a modal
 */
export function open(modalId) {
  const overlay = document.querySelector(`[data-modal="${modalId}"]`);
  if (!overlay) return null;
  
  // Store currently focused element
  previouslyFocused = document.activeElement;
  
  // Close any existing modal
  if (activeModal) close();
  
  // Show modal
  overlay.classList.add(ACTIVE_CLASS);
  overlay.setAttribute('aria-hidden', 'false');
  activeModal = overlay;
  
  // Prevent body scroll
  document.body.style.overflow = 'hidden';
  
  // Focus first focusable element
  const modal = overlay.querySelector('.modal, .drawer');
  const focusable = modal?.querySelector(
    'button, [href], input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  if (focusable) focusable.focus();
  
  // Dispatch event
  overlay.dispatchEvent(new CustomEvent('modal:open', { bubbles: true }));
  
  return overlay;
}

/**
 * Close the active modal
 */
export function close() {
  if (!activeModal) return;
  
  activeModal.classList.remove(ACTIVE_CLASS);
  activeModal.setAttribute('aria-hidden', 'true');
  
  // Restore body scroll
  document.body.style.overflow = '';
  
  // Dispatch event
  activeModal.dispatchEvent(new CustomEvent('modal:close', { bubbles: true }));
  
  // Restore focus
  if (previouslyFocused) {
    previouslyFocused.focus();
    previouslyFocused = null;
  }
  
  activeModal = null;
}

/**
 * Show a confirm dialog
 */
export function confirm(options = {}) {
  const {
    title = 'Confirm',
    message = 'Are you sure?',
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    confirmClass = 'btn btn--primary',
    cancelClass = 'btn btn--secondary',
    type = 'warning' // 'warning', 'danger', 'success'
  } = options;
  
  return new Promise((resolve) => {
    // Create modal HTML
    const modalId = 'confirm-' + Date.now();
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.dataset.modal = modalId;
    overlay.setAttribute('role', 'alertdialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-labelledby', `${modalId}-title`);
    
    overlay.innerHTML = `
      <div class="modal modal--sm confirm-dialog">
        <div class="modal-body">
          <div class="confirm-icon confirm-icon--${type}">
            ${getConfirmIcon(type)}
          </div>
          <h3 class="confirm-title" id="${modalId}-title">${title}</h3>
          <p class="confirm-message">${message}</p>
        </div>
        <div class="modal-footer">
          <button class="${cancelClass}" data-modal-close>${cancelText}</button>
          <button class="${confirmClass}" data-confirm-action>${confirmText}</button>
        </div>
      </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Handle confirm
    overlay.querySelector('[data-confirm-action]').addEventListener('click', () => {
      close();
      overlay.remove();
      resolve(true);
    });
    
    // Handle cancel
    overlay.querySelector('[data-modal-close]').addEventListener('click', () => {
      close();
      overlay.remove();
      resolve(false);
    });
    
    // Handle overlay click
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        close();
        overlay.remove();
        resolve(false);
      }
    });
    
    // Open the modal
    open(modalId);
  });
}

/**
 * Get icon SVG for confirm dialog type
 */
function getConfirmIcon(type) {
  const icons = {
    warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    danger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
  };
  return icons[type] || icons.warning;
}

/**
 * Trap focus within modal
 */
function trapFocus(e) {
  if (!activeModal) return;
  
  const modal = activeModal.querySelector('.modal, .drawer');
  if (!modal) return;
  
  const focusable = modal.querySelectorAll(
    'button, [href], input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  
  const firstFocusable = focusable[0];
  const lastFocusable = focusable[focusable.length - 1];
  
  if (e.shiftKey && document.activeElement === firstFocusable) {
    e.preventDefault();
    lastFocusable.focus();
  } else if (!e.shiftKey && document.activeElement === lastFocusable) {
    e.preventDefault();
    firstFocusable.focus();
  }
}

/**
 * Initialize modals module
 */
export function init() {
  // Handle modal open triggers
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-modal-open]');
    if (trigger) {
      e.preventDefault();
      open(trigger.dataset.modalOpen);
    }
  });
  
  // Handle modal close triggers
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      e.preventDefault();
      close();
    }
  });
  
  // Handle overlay click
  document.addEventListener('click', (e) => {
    if (e.target.matches('.modal-overlay.active')) {
      close();
    }
  });
  
  // Handle escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && activeModal) {
      close();
    }
    
    // Trap focus
    if (e.key === 'Tab' && activeModal) {
      trapFocus(e);
    }
  });
  
  // Handle confirm buttons with data attributes
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    
    e.preventDefault();
    
    const confirmed = await confirm({
      title: btn.dataset.confirmTitle || 'Confirm',
      message: btn.dataset.confirm,
      confirmText: btn.dataset.confirmOk || 'Confirm',
      cancelText: btn.dataset.confirmCancel || 'Cancel',
      type: btn.dataset.confirmType || 'warning'
    });
    
    if (confirmed) {
      // If it's a link, navigate
      if (btn.href) {
        window.location.href = btn.href;
      }
      // If it's a form button, submit
      else if (btn.form) {
        btn.form.submit();
      }
      // Dispatch confirmed event
      btn.dispatchEvent(new CustomEvent('confirmed', { bubbles: true }));
    }
  });
  
  // Initialize ARIA attributes
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-hidden', 'true');
  });
}

export default { init, open, close, confirm };
