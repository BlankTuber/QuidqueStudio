/**
 * Theme Module
 * Handles dark/light theme switching with system preference detection
 */

const STORAGE_KEY = 'theme';
const DARK_CLASS = 'dark';

/**
 * Get the current theme preference
 * Priority: localStorage > system preference > default (dark)
 */
function getPreferredTheme() {
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored) return stored;
  
  return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

/**
 * Apply theme to document
 */
function applyTheme(theme) {
  document.documentElement.classList.toggle(DARK_CLASS, theme === 'dark');
  updateToggleButton(theme);
}

/**
 * Update toggle button icon/state
 */
function updateToggleButton(theme) {
  const btn = document.querySelector('[data-theme-toggle]');
  if (!btn) return;
  
  const sunIcon = btn.querySelector('.icon-sun');
  const moonIcon = btn.querySelector('.icon-moon');
  
  if (sunIcon) sunIcon.style.display = theme === 'dark' ? 'block' : 'none';
  if (moonIcon) moonIcon.style.display = theme === 'light' ? 'block' : 'none';
  
  btn.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} theme`);
}

/**
 * Toggle between themes
 */
function toggleTheme() {
  const current = document.documentElement.classList.contains(DARK_CLASS) ? 'dark' : 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  
  localStorage.setItem(STORAGE_KEY, next);
  applyTheme(next);
}

/**
 * Initialize theme module
 */
export function init() {
  // Apply initial theme
  applyTheme(getPreferredTheme());
  
  // Listen for toggle clicks
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-theme-toggle]')) {
      toggleTheme();
    }
  });
  
  // Listen for system preference changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    // Only apply if user hasn't set explicit preference
    if (!localStorage.getItem(STORAGE_KEY)) {
      applyTheme(e.matches ? 'dark' : 'light');
    }
  });
}

export default { init };
