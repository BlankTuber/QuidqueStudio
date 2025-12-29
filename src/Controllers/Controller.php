<?php

namespace Quidque\Controllers;

use Quidque\Core\Database;
use Quidque\Core\Request;
use Quidque\Core\Auth;
use Quidque\Core\Csrf;

abstract class Controller
{
    protected Database $db;
    protected Request $request;
    protected array $config;
    
    public function __construct()
    {
        global $db, $request, $config;
        $this->db = $db;
        $this->request = $request;
        $this->config = $config;
    }
    
    /**
     * Render a view with the app layout
     */
    protected function render(string $template, array $data = []): string
    {
        // Add global data
        $data['config'] = $this->config;
        $data['auth'] = [
            'check' => Auth::check(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ];
        $data['csrf'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        
        // Check for flash messages in query string
        if ($this->request->get('saved')) {
            $data['success'] = $data['success'] ?? 'Changes saved successfully.';
        }
        if ($this->request->get('created')) {
            $data['success'] = $data['success'] ?? 'Created successfully.';
        }
        if ($this->request->get('deleted')) {
            $data['success'] = $data['success'] ?? 'Deleted successfully.';
        }
        
        // Extract data to local scope
        extract($data);
        
        // Capture template content
        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();
        
        // If template sets $layout = false, return content directly (for partials/HTMX)
        if (isset($layout) && $layout === false) {
            return $content;
        }
        
        // Default layout values
        $pageTitle = $pageTitle ?? 'Quidque Studio';
        $pageClass = $pageClass ?? '';
        $showSidebar = $showSidebar ?? true;
        $sidebarCollapsed = $sidebarCollapsed ?? true;
        $breadcrumbs = $breadcrumbs ?? [];
        
        // Render with layout
        ob_start();
        require BASE_PATH . '/templates/layouts/app.php';
        return ob_get_clean();
    }

    /**
     * Render a view with the admin layout
     */
    protected function renderAdmin(string $template, array $data = []): string
    {
        $data['config'] = $this->config;
        $data['auth'] = [
            'check' => Auth::check(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ];
        $data['csrf'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        
        // Check for flash messages
        if ($this->request->get('saved')) {
            $data['success'] = $data['success'] ?? 'Changes saved successfully.';
        }
        if ($this->request->get('created')) {
            $data['success'] = $data['success'] ?? 'Created successfully.';
        }
        if ($this->request->get('deleted')) {
            $data['success'] = $data['success'] ?? 'Deleted successfully.';
        }
        if ($this->request->get('published')) {
            $data['success'] = $data['success'] ?? 'Published successfully.';
        }
        if ($this->request->get('unpublished')) {
            $data['success'] = $data['success'] ?? 'Unpublished successfully.';
        }
        
        extract($data);
        
        // Capture template content
        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();
        
        // Default layout values
        $pageTitle = $pageTitle ?? 'Admin';
        $breadcrumbs = $breadcrumbs ?? [];
        
        // Render with admin layout
        ob_start();
        require BASE_PATH . '/templates/layouts/admin.php';
        return ob_get_clean();
    }

    protected function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('M j', $time);
        }
    }
    
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
    }

    /**
     * Render without layout (for HTMX partials)
     */
    protected function partial(string $template, array $data = []): string
    {
        $data['config'] = $this->config;
        $data['auth'] = [
            'check' => Auth::check(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ];
        $data['csrf'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        
        extract($data);
        
        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        return ob_get_clean();
    }
    
    protected function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data);
    }
    
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
    
    protected function notFound(): string
    {
        http_response_code(404);
        return $this->render('errors/404');
    }
}