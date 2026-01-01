/**
 * Dropdowns Module
 * Handles dropdown menus with keyboard navigation and accessibility
 */

const ACTIVE_CLASS = 'active';
let activeDropdown = null;

/**
 * Open a dropdown
 */
function openDropdown(trigger, menu) {
  closeAllDropdowns();
  
  trigger.setAttribute('aria-expanded', 'true');
  menu.classList.add(ACTIVE_CLASS);
  menu.hidden = false;
  activeDropdown = { trigger, menu };
  
  // Position the dropdown
  positionDropdown(trigger, menu);
  
  // Focus first focusable item
  const firstItem = menu.querySelector('a, button');
  if (firstItem) firstItem.focus();
}

/**
 * Close a dropdown
 */
function closeDropdown(trigger, menu) {
  trigger.setAttribute('aria-expanded', 'false');
  menu.classList.remove(ACTIVE_CLASS);
  menu.hidden = true;
  
  if (activeDropdown?.menu === menu) {
    activeDropdown = null;
  }
}

/**
 * Close all open dropdowns
 */
function closeAllDropdowns() {
  document.querySelectorAll('[data-dropdown-menu].' + ACTIVE_CLASS).forEach(menu => {
    const trigger = document.querySelector(`[data-dropdown-trigger="${menu.dataset.dropdownMenu}"]`);
    if (trigger) closeDropdown(trigger, menu);
  });
}

/**
 * Toggle a dropdown
 */
function toggleDropdown(trigger) {
  const menuId = trigger.dataset.dropdownTrigger;
  const menu = document.querySelector(`[data-dropdown-menu="${menuId}"]`);
  
  if (!menu) return;
  
  const isOpen = menu.classList.contains(ACTIVE_CLASS);
  
  if (isOpen) {
    closeDropdown(trigger, menu);
    trigger.focus();
  } else {
    openDropdown(trigger, menu);
  }
}

/**
 * Position dropdown relative to trigger
 */
function positionDropdown(trigger, menu) {
  const triggerRect = trigger.getBoundingClientRect();
  const menuRect = menu.getBoundingClientRect();
  const viewport = {
    width: window.innerWidth,
    height: window.innerHeight
  };
  
  // Reset position
  menu.style.left = '';
  menu.style.right = '';
  menu.style.top = '';
  menu.style.bottom = '';
  
  // Check if menu would overflow right edge
  if (triggerRect.left + menuRect.width > viewport.width) {
    menu.style.right = '0';
    menu.style.left = 'auto';
  }
  
  // Check if menu would overflow bottom edge
  if (triggerRect.bottom + menuRect.height > viewport.height) {
    menu.style.bottom = '100%';
    menu.style.top = 'auto';
  }
}

/**
 * Handle keyboard navigation within dropdown
 */
function handleKeydown(e) {
  if (!activeDropdown) return;
  
  const { menu, trigger } = activeDropdown;
  const items = Array.from(menu.querySelectorAll('a:not([disabled]), button:not([disabled])'));
  const currentIndex = items.indexOf(document.activeElement);
  
  switch (e.key) {
    case 'Escape':
      e.preventDefault();
      closeDropdown(trigger, menu);
      trigger.focus();
      break;
      
    case 'ArrowDown':
      e.preventDefault();
      if (currentIndex < items.length - 1) {
        items[currentIndex + 1].focus();
      } else {
        items[0].focus();
      }
      break;
      
    case 'ArrowUp':
      e.preventDefault();
      if (currentIndex > 0) {
        items[currentIndex - 1].focus();
      } else {
        items[items.length - 1].focus();
      }
      break;
      
    case 'Home':
      e.preventDefault();
      items[0]?.focus();
      break;
      
    case 'End':
      e.preventDefault();
      items[items.length - 1]?.focus();
      break;
      
    case 'Tab':
      closeDropdown(trigger, menu);
      break;
  }
}

/**
 * Initialize dropdowns module
 */
export function init() {
  // Handle trigger clicks
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-dropdown-trigger]');
    
    if (trigger) {
      e.preventDefault();
      e.stopPropagation();
      toggleDropdown(trigger);
      return;
    }
    
    // Close if clicking outside
    if (activeDropdown && !e.target.closest('[data-dropdown-menu]')) {
      closeAllDropdowns();
    }
  });
  
  // Handle item clicks (close dropdown)
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-dropdown-menu] a, [data-dropdown-menu] button:not([data-dropdown-keep-open])')) {
      closeAllDropdowns();
    }
  });
  
  // Handle keyboard navigation
  document.addEventListener('keydown', handleKeydown);
  
  // Initialize ARIA attributes
  document.querySelectorAll('[data-dropdown-trigger]').forEach(trigger => {
    trigger.setAttribute('aria-haspopup', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    
    const menuId = trigger.dataset.dropdownTrigger;
    const menu = document.querySelector(`[data-dropdown-menu="${menuId}"]`);
    if (menu) {
      menu.hidden = true;
      menu.setAttribute('role', 'menu');
    }
  });
}

export default { init, closeAllDropdowns };
