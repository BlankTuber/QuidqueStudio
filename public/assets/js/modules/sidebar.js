/**
 * Sidebar Module
 * Handles sidebar toggle, responsive behavior, and state persistence
 */

const STORAGE_KEY = 'sidebar-collapsed';
const COLLAPSED_CLASS = 'sidebar-collapsed';
const MOBILE_BREAKPOINT = 1024;

let isCollapsed = false;

/**
 * Get saved sidebar state
 */
function getSavedState() {
  return localStorage.getItem(STORAGE_KEY) === 'true';
}

/**
 * Save sidebar state
 */
function saveState(collapsed) {
  localStorage.setItem(STORAGE_KEY, collapsed);
}

/**
 * Check if currently in mobile view
 */
function isMobile() {
  return window.innerWidth < MOBILE_BREAKPOINT;
}

/**
 * Update sidebar state
 */
function updateSidebar(collapsed) {
  isCollapsed = collapsed;
  document.body.classList.toggle(COLLAPSED_CLASS, collapsed);
  
  const sidebar = document.querySelector('.sidebar');
  const toggle = document.querySelector('[data-sidebar-toggle]');
  
  if (sidebar) {
    sidebar.setAttribute('aria-expanded', !collapsed);
  }
  
  if (toggle) {
    toggle.setAttribute('aria-pressed', collapsed);
    toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
  }
}

/**
 * Toggle sidebar
 */
function toggleSidebar() {
  const newState = !isCollapsed;
  updateSidebar(newState);
  
  // Only persist on desktop
  if (!isMobile()) {
    saveState(newState);
  }
}

/**
 * Handle responsive behavior
 */
function handleResize() {
  if (isMobile()) {
    // Always collapse on mobile
    updateSidebar(true);
  } else {
    // Restore saved state on desktop
    updateSidebar(getSavedState());
  }
}

/**
 * Close sidebar when clicking outside (mobile only)
 */
function handleOutsideClick(e) {
  if (!isMobile() || isCollapsed) return;
  
  const sidebar = document.querySelector('.sidebar');
  const toggle = e.target.closest('[data-sidebar-toggle]');
  
  if (sidebar && !sidebar.contains(e.target) && !toggle) {
    updateSidebar(true);
  }
}

/**
 * Initialize sidebar module
 */
export function init() {
  // Set initial state
  handleResize();
  
  // Listen for toggle clicks
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-sidebar-toggle]')) {
      toggleSidebar();
    }
  });
  
  // Handle outside clicks on mobile
  document.addEventListener('click', handleOutsideClick);
  
  // Handle resize
  let resizeTimeout;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(handleResize, 100);
  });
  
  // Handle escape key to close on mobile
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isMobile() && !isCollapsed) {
      updateSidebar(true);
    }
  });
}

export default { init };
