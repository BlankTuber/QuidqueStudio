<?php 
$pageTitle = '500 - Server Error';
$showSidebar = false;
$seo = \Quidque\Helpers\Seo::noIndex();
?>

<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">500</h1>
        <p class="error-message">Something went wrong on our end.</p>
        <p class="error-suggestion">We've been notified and are looking into it. In the meantime, you can try refreshing the page or come back later.</p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <a href="javascript:location.reload()" class="btn btn-secondary">Try Again</a>
        </div>
    </div>
</div>