<?php
/**
 * Main App Layout
 * 
 * Variables available:
 * - $pageTitle (string) - Page title
 * - $pageClass (string) - Additional body class
 * - $content (string) - Main content HTML
 * - $breadcrumbs (array) - Optional breadcrumb items
 * - $showSidebar (bool) - Default true
 * - $sidebarCollapsed (bool) - Default false on home, true on subpages
 */

$pageTitle = $pageTitle ?? 'Quidque Studio';
$pageClass = $pageClass ?? '';
$showSidebar = $showSidebar ?? true;
$sidebarCollapsed = $sidebarCollapsed ?? false;
$breadcrumbs = $breadcrumbs ?? [];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= $config['site']['name'] ?></title>
    
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <script src="/assets/js/htmx.min.js" defer></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <div class="app <?= $sidebarCollapsed ? 'sidebar-collapsed' : '' ?> <?= htmlspecialchars($pageClass) ?>">
        
        <?php if ($showSidebar): ?>
            <?php include BASE_PATH . '/templates/partials/sidebar.php'; ?>
        <?php endif; ?>
        
        <div class="app-main">
            <?php include BASE_PATH . '/templates/partials/header.php'; ?>
            
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
        
        <?php include BASE_PATH . '/templates/partials/bottom-nav.php'; ?>
    </div>
</body>
</html>