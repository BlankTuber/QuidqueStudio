<?php 
$pageTitle = '403 - Forbidden';
$showSidebar = false;
$seo = \Quidque\Helpers\Seo::noIndex();
?>

<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">403</h1>
        <p class="error-message">Access denied.</p>
        <p class="error-suggestion">You don't have permission to view this page. If you think this is a mistake, try logging in or contact the administrator.</p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <?php if (!($auth['check'] ?? false)): ?>
            <a href="/login" class="btn btn-secondary">Log In</a>
            <?php endif; ?>
        </div>
    </div>
</div>