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
    
    protected function render(string $template, array $data = []): string
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