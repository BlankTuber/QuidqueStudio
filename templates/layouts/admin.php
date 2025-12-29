<?php
/**
 * Admin Layout
 * 
 * Variables:
 * - $pageTitle (string)
 * - $content (string)
 * - $breadcrumbs (array)
 */

$pageTitle = $pageTitle ?? 'Admin';
$breadcrumbs = $breadcrumbs ?? [];
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function adminIsActive(string $path, string $current): bool {
    if ($path === '/admin') {
        return $current === '/admin';
    }
    return str_starts_with($current, $path);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Admin - <?= $config['site']['name'] ?></title>
    
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <script src="/assets/js/htmx.min.js" defer></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="/admin" class="sidebar-logo">
                    <svg class="sidebar-logo-icon" width="32" height="32" viewBox="0 0 930 422" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="logo-gradient" x1="124" y1="487" x2="843" y2="-95" gradientUnits="userSpaceOnUse">
                                <stop offset=".11" stop-color="#f0f"/>
                                <stop offset=".84" stop-color="#39ffb6"/>
                            </linearGradient>
                        </defs>
                        <path d="M465.3,268.4c-71.7,71.7-146.3,150.5-260.2,150.5C86.9,418.9,2.5,331.7,2.5,210.7,2.5,99.6,92.5,2.5,216.3,2.5c104.1,0,181.5,83,249,150.5C537,81.3,611.6,2.5,725.5,2.5c119.6,0,202.6,87.2,202.6,208.2,0,111.1-90,208.2-213.8,208.2-104.1,0-181.5-83-249-150.5ZM409,210.7c-54.9-50.6-112.5-123.8-194.1-123.8-70.3,0-123.8,57.7-123.8,123.8,0,71.7,50.6,123.8,119.6,123.8,80.2,0,144.9-68.9,198.3-123.8ZM839.5,210.7c0-71.7-50.6-123.8-119.6-123.8-80.2,0-144.9,68.9-198.3,123.8,54.9,50.6,112.5,123.8,194.1,123.8,70.3,0,123.8-57.7,123.8-123.8Z" fill="url(#logo-gradient)" stroke="#000" stroke-width="5" stroke-miterlimit="10"/>
                    </svg>
                    <span class="sidebar-logo-text">Admin</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a href="/admin" class="nav-item <?= adminIsActive('/admin', $currentPath) && $currentPath === '/admin' ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span class="nav-item-text">Dashboard</span>
                </a>
                
                <a href="/admin/projects" class="nav-item <?= adminIsActive('/admin/projects', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span class="nav-item-text">Projects</span>
                </a>
                
                <a href="/admin/blog" class="nav-item <?= adminIsActive('/admin/blog', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span class="nav-item-text">Blog</span>
                </a>
                
                <a href="/admin/tags" class="nav-item <?= adminIsActive('/admin/tags', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    <span class="nav-item-text">Tags & Tech</span>
                </a>
                
                <a href="/admin/media" class="nav-item <?= adminIsActive('/admin/media', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21,15 16,10 5,21"/>
                    </svg>
                    <span class="nav-item-text">Media</span>
                </a>
                
                <div class="nav-divider"></div>
                
                <a href="/admin/users" class="nav-item <?= adminIsActive('/admin/users', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="nav-item-text">Users</span>
                </a>
                
                <a href="/admin/messages" class="nav-item <?= adminIsActive('/admin/messages', $currentPath) ? 'active' : '' ?>">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span class="nav-item-text">Messages</span>
                    <?php if (!empty($unreadMessages) && $unreadMessages > 0): ?>
                        <span class="nav-badge"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/" class="nav-item">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15,3 21,3 21,9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    <span class="nav-item-text">View Site</span>
                </a>
                
                <a href="/logout" class="nav-item">
                    <svg class="nav-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="nav-item-text">Logout</span>
                </a>
            </div>
        </aside>
        
        <div class="app-main">
            <header class="main-header">
                <div class="main-header-left">
                    <button class="btn-icon theme-toggle" id="sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    
                    <?php if (!empty($breadcrumbs)): ?>
                        <nav class="breadcrumb">
                            <a href="/admin">Admin</a>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <span class="breadcrumb-separator">/</span>
                                <?php if (isset($crumb['url'])): ?>
                                    <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                                <?php else: ?>
                                    <span><?= htmlspecialchars($crumb['label']) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </div>
                
                <div class="main-header-right">
                    <button class="theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle theme">
                        <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    
                    <span class="text-small text-secondary"><?= htmlspecialchars($auth['user']['username'] ?? 'Admin') ?></span>
                </div>
            </header>
            
            <main class="main-content">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error mb-4"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>
</body>
</html>