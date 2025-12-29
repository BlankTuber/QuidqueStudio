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

    // Restore state
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

        // Close other dropdowns
        document.querySelectorAll('.dropdown.open').forEach(d => {
          if (d !== dropdown) d.classList.remove('open');
        });

        dropdown.classList.toggle('open');
        return;
      }

      // Close all dropdowns when clicking outside
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(d => {
          d.classList.remove('open');
        });
      }
    });

    // Close on escape
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
    // Add loading state to buttons during requests
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

    // Re-init components after HTMX swap
    document.body.addEventListener('htmx:afterSwap', () => {
      initAlerts();
    });
  }

  // ==========================================
  // Confirm Dialogs
  // ==========================================

  function initConfirmDialogs() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-confirm]');
      if (btn) {
        const message = btn.dataset.confirm || 'Are you sure?';
        if (!confirm(message)) {
          e.preventDefault();
          e.stopPropagation();
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