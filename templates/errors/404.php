<?php 
$pageTitle = '404 - Not Found';
$showSidebar = false;
$seo = \Quidque\Helpers\Seo::noIndex();
?>

<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">404</h1>
        <p class="error-message">The page you're looking for has wandered off into the void.</p>
        <p class="error-suggestion">Maybe it's working on a side project? Or perhaps it never existed in the first place.</p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <a href="/projects" class="btn btn-secondary">Browse Projects</a>
        </div>
    </div>
</div>