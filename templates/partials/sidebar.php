<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function isActive(string $path, string $current): bool {
    if ($path === '/') {
        return $current === '/';
    }
    return str_starts_with($current, $path);
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/" class="sidebar-logo">
            <svg class="sidebar-logo-icon" width="32" height="32" viewBox="0 0 930 422" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="logo-gradient" x1="124" y1="487" x2="843" y2="-95" gradientUnits="userSpaceOnUse">
                        <stop offset=".11" stop-color="#f0f"/>
                        <stop offset=".84" stop-color="#39ffb6"/>
                    </linearGradient>
                </defs>
                <path d="M465.3,268.4c-71.7,71.7-146.3,150.5-260.2,150.5C86.9,418.9,2.5,331.7,2.5,210.7,2.5,99.6,92.5,2.5,216.3,2.5c104.1,0,181.5,83,249,150.5C537,81.3,611.6,2.5,725.5,2.5c119.6,0,202.6,87.2,202.6,208.2,0,111.1-90,208.2-213.8,208.2-104.1,0-181.5-83-249-150.5ZM409,210.7c-54.9-50.6-112.5-123.8-194.1-123.8-70.3,0-123.8,57.7-123.8,123.8,0,71.7,50.6,123.8,119.6,123.8,80.2,0,144.9-68.9,198.3-123.8ZM839.5,210.7c0-71.7-50.6-123.8-119.6-123.8-80.2,0-144.9,68.9-198.3,123.8,54.9,50.6,112.5,123.8,194.1,123.8,70.3,0,123.8-57.7,123.8-123.8Z" fill="url(#logo-gradient)" stroke="#000" stroke-width="5" stroke-miterlimit="10"/>
            </svg>
            <span class="sidebar-logo-text">Quidque</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="/" class="nav-item <?= isActive('/', $currentPath) ? 'active' : '' ?>">
            <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
            <span class="nav-item-text">Home</span>
        </a>
        
        <a href="/projects" class="nav-item <?= isActive('/projects', $currentPath) ? 'active' : '' ?>">
            <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <span class="nav-item-text">Projects</span>
        </a>
        
        <a href="/blog" class="nav-item <?= isActive('/blog', $currentPath) ? 'active' : '' ?>">
            <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span class="nav-item-text">Blog</span>
        </a>
        
        <a href="/about" class="nav-item <?= isActive('/about', $currentPath) ? 'active' : '' ?>">
            <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="7" r="4"/>
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            </svg>
            <span class="nav-item-text">About</span>
        </a>
        
        <?php if ($auth['isAdmin']): ?>
            <div class="nav-divider"></div>
            <a href="/admin" class="nav-item <?= isActive('/admin', $currentPath) ? 'active' : '' ?>">
                <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span class="nav-item-text">Admin</span>
            </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <?php if ($auth['check']): ?>
            <a href="/settings" class="nav-item <?= isActive('/settings', $currentPath) ? 'active' : '' ?>">
                <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span class="nav-item-text">Settings</span>
            </a>
            <a href="/logout" class="nav-item">
                <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span class="nav-item-text">Logout</span>
            </a>
        <?php else: ?>
            <a href="/login" class="nav-item <?= isActive('/login', $currentPath) ? 'active' : '' ?>">
                <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10,17 15,12 10,7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                <span class="nav-item-text">Login</span>
            </a>
        <?php endif; ?>
    </div>
</aside>