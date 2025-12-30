/**
 * Quidque Studio - Main JS
 */

(function () {
  'use strict';

  // ==========================================
  // Theme Toggle
  // ==========================================

  const THEME_KEY = 'quidque-theme';

  function getPreferredTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored) return stored;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(THEME_KEY, theme);
    updateThemeIcon(theme);
  }

  function updateThemeIcon(theme) {
    const sunIcon = document.querySelector('.sun-icon');
    const moonIcon = document.querySelector('.moon-icon');

    if (sunIcon && moonIcon) {
      if (theme === 'dark') {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
      } else {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
      }
    }
  }

  function initTheme() {
    const theme = getPreferredTheme();
    setTheme(theme);

    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
      toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        setTheme(current === 'dark' ? 'light' : 'dark');
      });
    }
  }

  // ==========================================
  // Sidebar Toggle
  // ==========================================

  const SIDEBAR_KEY = 'quidque-sidebar-collapsed';

  function initSidebar() {
    const toggle = document.getElementById('sidebar-toggle');
    const app = document.querySelector('.app');

    if (!toggle || !app) return;

    const collapsed = localStorage.getItem(SIDEBAR_KEY) === 'true';
    if (collapsed) {
      app.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', () => {
      app.classList.toggle('sidebar-collapsed');
      const isCollapsed = app.classList.contains('sidebar-collapsed');
      localStorage.setItem(SIDEBAR_KEY, isCollapsed);
    });
  }

  // ==========================================
  // Dropdowns
  // ==========================================

  function initDropdowns() {
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-dropdown]');

      if (trigger) {
        e.preventDefault();
        const dropdown = trigger.closest('.dropdown');

        document.querySelectorAll('.dropdown.open').forEach(d => {
          if (d !== dropdown) d.classList.remove('open');
        });

        dropdown.classList.toggle('open');
        return;
      }

      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(d => {
          d.classList.remove('open');
        });
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.open').forEach(d => {
          d.classList.remove('open');
        });
      }
    });
  }

  // ==========================================
  // Flash Messages Auto-dismiss
  // ==========================================

  function initAlerts() {
    document.querySelectorAll('.alert[data-autodismiss]').forEach(alert => {
      const delay = parseInt(alert.dataset.autodismiss) || 5000;
      setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 200);
      }, delay);
    });
  }

  // ==========================================
  // HTMX Events
  // ==========================================

  function initHtmx() {
    document.body.addEventListener('htmx:beforeRequest', (e) => {
      const trigger = e.detail.elt;
      if (trigger.tagName === 'BUTTON') {
        trigger.disabled = true;
        trigger.dataset.originalText = trigger.innerHTML;
        trigger.innerHTML = '<span class="loading"></span>';
      }
    });

    document.body.addEventListener('htmx:afterRequest', (e) => {
      const trigger = e.detail.elt;
      if (trigger.tagName === 'BUTTON' && trigger.dataset.originalText) {
        trigger.disabled = false;
        trigger.innerHTML = trigger.dataset.originalText;
        delete trigger.dataset.originalText;
      }
    });

    document.body.addEventListener('htmx:afterSwap', () => {
      initAlerts();
    });
  }

  // ==========================================
  // Confirm Modal
  // ==========================================

  let confirmModal = null;
  let confirmResolve = null;

  function createConfirmModal() {
    if (confirmModal) return;

    const modalHTML = `
      <div class="modal-overlay" id="confirm-modal">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3 class="modal-title" id="confirm-modal-title">Confirm</h3>
            <button type="button" class="modal-close" id="confirm-modal-close">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <p id="confirm-modal-message">Are you sure?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="confirm-modal-cancel">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirm-modal-confirm">Confirm</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    confirmModal = document.getElementById('confirm-modal');

    document.getElementById('confirm-modal-close').addEventListener('click', () => closeConfirm(false));
    document.getElementById('confirm-modal-cancel').addEventListener('click', () => closeConfirm(false));
    document.getElementById('confirm-modal-confirm').addEventListener('click', () => closeConfirm(true));

    confirmModal.addEventListener('click', (e) => {
      if (e.target === confirmModal) {
        closeConfirm(false);
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && confirmModal.classList.contains('open')) {
        closeConfirm(false);
      }
    });
  }

  function showConfirm(message, title = 'Confirm') {
    createConfirmModal();

    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-message').textContent = message;
    confirmModal.classList.add('open');

    document.getElementById('confirm-modal-confirm').focus();

    return new Promise((resolve) => {
      confirmResolve = resolve;
    });
  }

  function closeConfirm(result) {
    if (confirmModal) {
      confirmModal.classList.remove('open');
    }
    if (confirmResolve) {
      confirmResolve(result);
      confirmResolve = null;
    }
  }

  // Expose globally for inline scripts
  window.showConfirm = showConfirm;

  function initConfirmDialogs() {
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-confirm]');
      if (btn) {
        e.preventDefault();
        e.stopPropagation();

        const message = btn.dataset.confirm || 'Are you sure?';
        const title = btn.dataset.confirmTitle || 'Confirm';
        const confirmed = await showConfirm(message, title);

        if (confirmed) {
          // Handle different element types
          if (btn.tagName === 'A') {
            window.location.href = btn.href;
          } else if (btn.form) {
            btn.form.submit();
          } else if (btn.dataset.submitForm) {
            const form = document.getElementById(btn.dataset.submitForm);
            if (form) form.submit();
          } else {
            // Dispatch a custom event for other handlers
            btn.dispatchEvent(new CustomEvent('confirmed', { bubbles: true }));
          }
        }
      }
    });
  }

  // ==========================================
  // Initialize
  // ==========================================

  document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();
    initDropdowns();
    initAlerts();
    initHtmx();
    initConfirmDialogs();
  });

})();