/**
 * App.js — Bundled Version (IIFE)
 * 
 * This is a self-contained bundle that doesn't require ES module support.
 * Include this file with a regular <script> tag.
 */

(function() {
  'use strict';

  // ============================================
  // Theme Module
  // ============================================
  const Theme = (function() {
    const STORAGE_KEY = 'theme';
    const DARK_CLASS = 'dark';

    function getPreferredTheme() {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) return stored;
      return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }

    function applyTheme(theme) {
      document.documentElement.classList.toggle(DARK_CLASS, theme === 'dark');
      updateToggleButton(theme);
    }

    function updateToggleButton(theme) {
      const btn = document.querySelector('[data-theme-toggle]');
      if (!btn) return;
      const sunIcon = btn.querySelector('.icon-sun');
      const moonIcon = btn.querySelector('.icon-moon');
      if (sunIcon) sunIcon.style.display = theme === 'dark' ? 'block' : 'none';
      if (moonIcon) moonIcon.style.display = theme === 'light' ? 'block' : 'none';
      btn.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} theme`);
    }

    function toggleTheme() {
      const current = document.documentElement.classList.contains(DARK_CLASS) ? 'dark' : 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      localStorage.setItem(STORAGE_KEY, next);
      applyTheme(next);
    }

    function init() {
      applyTheme(getPreferredTheme());
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-theme-toggle]')) toggleTheme();
      });
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem(STORAGE_KEY)) applyTheme(e.matches ? 'dark' : 'light');
      });
    }

    return { init };
  })();

  // ============================================
  // Sidebar Module
  // ============================================
  const Sidebar = (function() {
    const STORAGE_KEY = 'sidebar-collapsed';
    const COLLAPSED_CLASS = 'sidebar-collapsed';
    const MOBILE_BREAKPOINT = 1024;
    let isCollapsed = false;

    function isMobile() { return window.innerWidth < MOBILE_BREAKPOINT; }
    function getSavedState() { return localStorage.getItem(STORAGE_KEY) === 'true'; }
    function saveState(collapsed) { localStorage.setItem(STORAGE_KEY, collapsed); }

    function updateSidebar(collapsed) {
      isCollapsed = collapsed;
      document.body.classList.toggle(COLLAPSED_CLASS, collapsed);
      const sidebar = document.querySelector('.sidebar');
      const toggle = document.querySelector('[data-sidebar-toggle]');
      if (sidebar) sidebar.setAttribute('aria-expanded', !collapsed);
      if (toggle) {
        toggle.setAttribute('aria-pressed', collapsed);
        toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
      }
    }

    function toggleSidebar() {
      const newState = !isCollapsed;
      updateSidebar(newState);
      if (!isMobile()) saveState(newState);
    }

    function handleResize() {
      if (isMobile()) updateSidebar(true);
      else updateSidebar(getSavedState());
    }

    function init() {
      handleResize();
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-sidebar-toggle]')) toggleSidebar();
      });
      document.addEventListener('click', (e) => {
        if (!isMobile() || isCollapsed) return;
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && !sidebar.contains(e.target) && !e.target.closest('[data-sidebar-toggle]')) {
          updateSidebar(true);
        }
      });
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 100);
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMobile() && !isCollapsed) updateSidebar(true);
      });
    }

    return { init };
  })();

  // ============================================
  // Dropdowns Module
  // ============================================
  const Dropdowns = (function() {
    const ACTIVE_CLASS = 'active';
    let activeDropdown = null;

    function closeAllDropdowns() {
      document.querySelectorAll('[data-dropdown-menu].' + ACTIVE_CLASS).forEach(menu => {
        const trigger = document.querySelector(`[data-dropdown-trigger="${menu.dataset.dropdownMenu}"]`);
        if (trigger) closeDropdown(trigger, menu);
      });
    }

    function openDropdown(trigger, menu) {
      closeAllDropdowns();
      trigger.setAttribute('aria-expanded', 'true');
      menu.classList.add(ACTIVE_CLASS);
      menu.hidden = false;
      activeDropdown = { trigger, menu };
      const firstItem = menu.querySelector('a, button');
      if (firstItem) firstItem.focus();
    }

    function closeDropdown(trigger, menu) {
      trigger.setAttribute('aria-expanded', 'false');
      menu.classList.remove(ACTIVE_CLASS);
      menu.hidden = true;
      if (activeDropdown?.menu === menu) activeDropdown = null;
    }

    function toggleDropdown(trigger) {
      const menuId = trigger.dataset.dropdownTrigger;
      const menu = document.querySelector(`[data-dropdown-menu="${menuId}"]`);
      if (!menu) return;
      const isOpen = menu.classList.contains(ACTIVE_CLASS);
      if (isOpen) { closeDropdown(trigger, menu); trigger.focus(); }
      else openDropdown(trigger, menu);
    }

    function init() {
      document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-dropdown-trigger]');
        if (trigger) { e.preventDefault(); e.stopPropagation(); toggleDropdown(trigger); return; }
        if (activeDropdown && !e.target.closest('[data-dropdown-menu]')) closeAllDropdowns();
      });
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-dropdown-menu] a, [data-dropdown-menu] button:not([data-dropdown-keep-open])')) {
          closeAllDropdowns();
        }
      });
      document.addEventListener('keydown', (e) => {
        if (!activeDropdown) return;
        const { menu, trigger } = activeDropdown;
        const items = Array.from(menu.querySelectorAll('a:not([disabled]), button:not([disabled])'));
        const currentIndex = items.indexOf(document.activeElement);
        if (e.key === 'Escape') { e.preventDefault(); closeDropdown(trigger, menu); trigger.focus(); }
        else if (e.key === 'ArrowDown') { e.preventDefault(); items[(currentIndex + 1) % items.length]?.focus(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); items[(currentIndex - 1 + items.length) % items.length]?.focus(); }
        else if (e.key === 'Tab') closeDropdown(trigger, menu);
      });
      document.querySelectorAll('[data-dropdown-trigger]').forEach(trigger => {
        trigger.setAttribute('aria-haspopup', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        const menu = document.querySelector(`[data-dropdown-menu="${trigger.dataset.dropdownTrigger}"]`);
        if (menu) { menu.hidden = true; menu.setAttribute('role', 'menu'); }
      });
    }

    return { init, closeAllDropdowns };
  })();

  // ============================================
  // Alerts Module
  // ============================================
  const Alerts = (function() {
    const DEFAULT_DURATION = 5000;

    function dismissAlert(alert) {
      alert.style.opacity = '0';
      alert.style.transform = 'translateX(20px)';
      setTimeout(() => alert.remove(), 300);
    }

    function show(message, type = 'info', options = {}) {
      const { duration = DEFAULT_DURATION, dismissible = true,
              container = document.querySelector('.alerts-container') || document.body } = options;
      const alert = document.createElement('div');
      alert.className = `alert alert--${type}`;
      alert.setAttribute('role', type === 'error' ? 'alert' : 'status');
      alert.innerHTML = `<span class="alert-message">${message}</span>
        ${dismissible ? '<button class="alert-dismiss" aria-label="Dismiss">&times;</button>' : ''}`;
      container.appendChild(alert);
      requestAnimationFrame(() => { alert.style.opacity = '1'; alert.style.transform = 'translateX(0)'; });
      if (duration > 0) setTimeout(() => dismissAlert(alert), duration);
      return alert;
    }

    function init() {
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.alert-dismiss, [data-alert-dismiss]');
        if (btn) { const alert = btn.closest('.alert'); if (alert) dismissAlert(alert); }
      });
      document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        const duration = parseInt(alert.dataset.autoDismiss) || DEFAULT_DURATION;
        setTimeout(() => dismissAlert(alert), duration);
      });
    }

    return { init, show, success: (m, o) => show(m, 'success', o), error: (m, o) => show(m, 'error', o),
             warning: (m, o) => show(m, 'warning', o), info: (m, o) => show(m, 'info', o) };
  })();

  // ============================================
  // Modals Module
  // ============================================
  const Modals = (function() {
    const ACTIVE_CLASS = 'active';
    let activeModal = null, previouslyFocused = null;

    function open(modalId) {
      const overlay = document.querySelector(`[data-modal="${modalId}"]`);
      if (!overlay) return null;
      previouslyFocused = document.activeElement;
      if (activeModal) close();
      overlay.classList.add(ACTIVE_CLASS);
      overlay.setAttribute('aria-hidden', 'false');
      activeModal = overlay;
      document.body.style.overflow = 'hidden';
      const modal = overlay.querySelector('.modal, .drawer');
      const focusable = modal?.querySelector('button, [href], input:not([type="hidden"]), select, textarea');
      if (focusable) focusable.focus();
      overlay.dispatchEvent(new CustomEvent('modal:open', { bubbles: true }));
      return overlay;
    }

    function close() {
      if (!activeModal) return;
      activeModal.classList.remove(ACTIVE_CLASS);
      activeModal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      activeModal.dispatchEvent(new CustomEvent('modal:close', { bubbles: true }));
      if (previouslyFocused) { previouslyFocused.focus(); previouslyFocused = null; }
      activeModal = null;
    }

    function confirm(options = {}) {
      const { title = 'Confirm', message = 'Are you sure?', confirmText = 'Confirm',
              cancelText = 'Cancel', confirmClass = 'btn btn--primary',
              cancelClass = 'btn btn--secondary', type = 'warning' } = options;
      return new Promise((resolve) => {
        const modalId = 'confirm-' + Date.now();
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.dataset.modal = modalId;
        overlay.innerHTML = `<div class="modal modal--sm confirm-dialog">
          <div class="modal-body"><div class="confirm-icon confirm-icon--${type}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <h3 class="confirm-title">${title}</h3><p class="confirm-message">${message}</p></div>
          <div class="modal-footer"><button class="${cancelClass}" data-modal-close>${cancelText}</button>
            <button class="${confirmClass}" data-confirm-action>${confirmText}</button></div></div>`;
        document.body.appendChild(overlay);
        overlay.querySelector('[data-confirm-action]').onclick = () => { close(); overlay.remove(); resolve(true); };
        overlay.querySelector('[data-modal-close]').onclick = () => { close(); overlay.remove(); resolve(false); };
        overlay.onclick = (e) => { if (e.target === overlay) { close(); overlay.remove(); resolve(false); } };
        open(modalId);
      });
    }

    function init() {
      document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-modal-open]');
        if (trigger) { e.preventDefault(); open(trigger.dataset.modalOpen); }
      });
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-modal-close]')) { e.preventDefault(); close(); }
      });
      document.addEventListener('click', (e) => { if (e.target.matches('.modal-overlay.active')) close(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && activeModal) close(); });
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-confirm]');
        if (!btn) return;
        e.preventDefault();
        const confirmed = await confirm({ title: btn.dataset.confirmTitle || 'Confirm',
          message: btn.dataset.confirm, type: btn.dataset.confirmType || 'warning' });
        if (confirmed) {
          if (btn.href) window.location.href = btn.href;
          else if (btn.form) btn.form.submit();
          btn.dispatchEvent(new CustomEvent('confirmed', { bubbles: true }));
        }
      });
    }

    return { init, open, close, confirm };
  })();

  // ============================================
  // HTMX Handlers Module
  // ============================================
  const HtmxHandlers = (function() {
    function init() {
      if (typeof htmx === 'undefined') return;
      
      document.body.addEventListener('htmx:beforeRequest', (e) => {
        e.detail.elt.classList.add('htmx-loading');
        e.detail.elt.setAttribute('aria-busy', 'true');
      });
      
      document.body.addEventListener('htmx:afterRequest', (e) => {
        e.detail.elt.classList.remove('htmx-loading');
        e.detail.elt.setAttribute('aria-busy', 'false');
        const xhr = e.detail.xhr;
        if (xhr) {
          const flash = xhr.getResponseHeader('X-Flash-Message');
          const type = xhr.getResponseHeader('X-Flash-Type') || 'info';
          if (flash) Alerts.show(flash, type);
        }
      });
      
      document.body.addEventListener('htmx:responseError', (e) => {
        Alerts.error('An error occurred. Please try again.');
      });
      
      document.body.addEventListener('htmx:configRequest', (e) => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) e.detail.headers['X-CSRF-Token'] = csrf;
        e.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
      });
    }

    return { init };
  })();

  // ============================================
  // Initialize All Modules
  // ============================================
  function init() {
    Theme.init();
    Sidebar.init();
    Dropdowns.init();
    Alerts.init();
    Modals.init();
    HtmxHandlers.init();
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose on window
  window.App = { Theme, Sidebar, Dropdowns, Alerts, Modals, HtmxHandlers };

})();
