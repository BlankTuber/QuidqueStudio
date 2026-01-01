<?php

namespace Quidque\Controllers;

use Quidque\Core\Database;
use Quidque\Core\Request;
use Quidque\Core\Auth;
use Quidque\Core\Csrf;
use Quidque\Helpers\Seo;

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
    
    protected function getCommonData(array $data = []): array
    {
        $data['config'] = $this->config;
        $data['auth'] = [
            'check' => Auth::check(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ];
        $data['csrf'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        
        $flashMessages = [
            'saved' => 'Changes saved successfully.',
            'created' => 'Created successfully.',
            'deleted' => 'Deleted successfully.',
            'published' => 'Published successfully.',
            'unpublished' => 'Unpublished successfully.',
        ];
        
        foreach ($flashMessages as $key => $message) {
            if ($this->request->get($key) && !isset($data['success'])) {
                $data['success'] = $message;
                break;
            }
        }
        
        $error = $this->request->get('error');
        if ($error && !isset($data['error'])) {
            $data['error'] = urldecode($error);
        }
        
        if (!isset($data['seo'])) {
            $data['seo'] = Seo::index();
        }
        
        return $data;
    }
    
    protected function render(string $template, array $data = []): string
    {
        $data = $this->getCommonData($data);
        
        extract($data);
        
        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();
        
        if (isset($layout) && $layout === false) {
            return $content;
        }
        
        $pageTitle = $pageTitle ?? 'Quidque Studio';
        $pageClass = $pageClass ?? '';
        $showSidebar = $showSidebar ?? true;
        $sidebarCollapsed = $sidebarCollapsed ?? true;
        $breadcrumbs = $breadcrumbs ?? [];
        $seo = $seo ?? Seo::index();
        
        ob_start();
        require BASE_PATH . '/templates/layouts/app.php';
        return ob_get_clean();
    }

    protected function renderAdmin(string $template, array $data = []): string
    {
        $data['seo'] = Seo::noIndex();
        $data = $this->getCommonData($data);
        
        extract($data);
        
        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();
        
        $pageTitle = $pageTitle ?? 'Admin';
        $breadcrumbs = $breadcrumbs ?? [];
        
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

    protected function partial(string $template, array $data = []): string
    {
        $data = $this->getCommonData($data);
        
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
    
    protected function respond(string $redirectUrl, string $partial = '', array $partialData = []): string
    {
        if ($this->request->isHtmx() && $partial) {
            return $this->partial($partial, $partialData);
        }
        
        $this->redirect($redirectUrl);
        return '';
    }
    
    protected function error(string $message, int $status = 400, string $redirectUrl = ''): string
    {
        if ($this->request->isHtmx() || $this->request->isAjax()) {
            return $this->json(['error' => $message], $status);
        }
        
        if ($redirectUrl) {
            $this->redirect($redirectUrl . '?error=' . urlencode($message));
        }
        
        return $this->json(['error' => $message], $status);
    }
}