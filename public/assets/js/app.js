/**
 * App.js — Main Entry Point
 * 
 * This file imports and initializes all JavaScript modules.
 * Each module handles a specific concern for better maintainability.
 * 
 * Modules:
 * - theme: Dark/light theme switching
 * - sidebar: Sidebar toggle and responsive behavior
 * - dropdowns: Dropdown menus with keyboard navigation
 * - alerts: Flash messages and notifications
 * - modals: Modal dialogs and confirm dialogs
 * - htmx-handlers: HTMX event handling
 */

// Import modules
import theme from './modules/theme.js';
import sidebar from './modules/sidebar.js';
import dropdowns from './modules/dropdowns.js';
import alerts from './modules/alerts.js';
import modals from './modules/modals.js';
import htmxHandlers from './modules/htmx-handlers.js';

/**
 * Initialize all modules
 */
function initModules() {
  theme.init();
  sidebar.init();
  dropdowns.init();
  alerts.init();
  modals.init();
  htmxHandlers.init();
}

/**
 * Initialize app when DOM is ready
 */
function init() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModules);
  } else {
    initModules();
  }
}

// Auto-initialize
init();

// Export modules for external access
export { theme, sidebar, dropdowns, alerts, modals, htmxHandlers };

// Also expose on window for non-module scripts
window.App = {
  theme,
  sidebar,
  dropdowns,
  alerts,
  modals,
  htmxHandlers
};
