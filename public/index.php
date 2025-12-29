<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/config/autoload.php';

$config = require BASE_PATH . '/config/config.php';

use Quidque\Core\Database;
use Quidque\Core\Router;
use Quidque\Core\Request;
use Quidque\Core\Auth;
use Quidque\Core\Csrf;
use Quidque\Core\ErrorHandler;
use Quidque\Models\Model;
use Quidque\Helpers\Mail;
use Quidque\Middleware\AuthMiddleware;
use Quidque\Controllers\HomeController;
use Quidque\Controllers\ProjectController;
use Quidque\Controllers\BlogController;
use Quidque\Controllers\AboutController;
use Quidque\Controllers\DevlogController;
use Quidque\Controllers\CommentController;
use Quidque\Controllers\MessageController;
use Quidque\Controllers\UserController;
use Quidque\Controllers\AuthController;
use Quidque\Controllers\Admin\DashboardController as AdminDashboard;
use Quidque\Controllers\Admin\ProjectController as AdminProject;
use Quidque\Controllers\Admin\BlogController as AdminBlog;
use Quidque\Controllers\Admin\DevlogController as AdminDevlog;
use Quidque\Controllers\Admin\TagController as AdminTag;
use Quidque\Controllers\Admin\UserController as AdminUser;
use Quidque\Controllers\Admin\MessageController as AdminMessage;
use Quidque\Controllers\Admin\MediaController as AdminMedia;

ErrorHandler::init($config['site']['debug']);

session_start();

$db = new Database($config);
$router = new Router();
$request = new Request();

Model::setDatabase($db);
Mail::init($config);
Auth::init($config);

// Middleware
$router->addMiddleware('auth', [AuthMiddleware::class, 'requireAuth']);
$router->addMiddleware('admin', [AuthMiddleware::class, 'requireAdmin']);
$router->addMiddleware('guest', [AuthMiddleware::class, 'requireGuest']);
$router->addMiddleware('csrf', [AuthMiddleware::class, 'verifyCsrf']);

// Public routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/projects', [ProjectController::class, 'index']);
$router->get('/projects/{slug}', [ProjectController::class, 'show']);
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'show']);
$router->get('/blog/category/{slug}', [BlogController::class, 'byCategory']);
$router->get('/blog/tag/{slug}', [BlogController::class, 'byTag']);
$router->get('/about', [AboutController::class, 'index']);

// Devlog routes
$router->get('/projects/{slug}/devlog', [DevlogController::class, 'index']);
$router->get('/projects/{slug}/devlog/{entry}', [DevlogController::class, 'show']);
$router->get('/feed/devlogs', [DevlogController::class, 'feed']);

// Auth routes
$router->get('/login', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
$router->get('/register', [AuthController::class, 'registerForm'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest', 'csrf']);
$router->get('/auth/verify', [AuthController::class, 'verify']);
$router->get('/logout', [AuthController::class, 'logout'], ['auth']);

// User routes (authenticated)
$router->get('/settings', [UserController::class, 'settings'], ['auth']);
$router->post('/settings', [UserController::class, 'updateSettings'], ['auth', 'csrf']);
$router->get('/settings/sessions', [UserController::class, 'sessions'], ['auth']);
$router->post('/settings/sessions/{id}/delete', [UserController::class, 'destroySession'], ['auth', 'csrf']);
$router->post('/settings/sessions/delete-all', [UserController::class, 'destroyAllSessions'], ['auth', 'csrf']);
$router->get('/settings/delete', [UserController::class, 'deleteAccount'], ['auth']);
$router->post('/settings/delete', [UserController::class, 'confirmDeleteAccount'], ['auth', 'csrf']);

// Messages (authenticated)
$router->get('/messages', [MessageController::class, 'index'], ['auth']);
$router->post('/messages', [MessageController::class, 'send'], ['auth', 'csrf']);

// Comments (authenticated)
$router->post('/projects/{slug}/comments', [CommentController::class, 'store'], ['auth', 'csrf']);
$router->post('/comments/{id}/edit', [CommentController::class, 'update'], ['auth', 'csrf']);
$router->post('/comments/{id}/delete', [CommentController::class, 'delete'], ['auth', 'csrf']);

// Admin routes
$router->get('/admin', [AdminDashboard::class, 'index'], ['admin']);

// Admin: Projects
$router->get('/admin/projects', [AdminProject::class, 'index'], ['admin']);
$router->get('/admin/projects/create', [AdminProject::class, 'create'], ['admin']);
$router->post('/admin/projects', [AdminProject::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/projects/{id}/edit', [AdminProject::class, 'edit'], ['admin']);
$router->post('/admin/projects/{id}', [AdminProject::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/delete', [AdminProject::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks', [AdminProject::class, 'addBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/{blockId}', [AdminProject::class, 'updateBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/{blockId}/delete', [AdminProject::class, 'deleteBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/reorder', [AdminProject::class, 'reorderBlocks'], ['admin', 'csrf']);

// Admin: Project Gallery
$router->post('/admin/projects/{id}/blocks/{blockId}/gallery', [AdminProject::class, 'addGalleryItems'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/gallery/{itemId}/delete', [AdminProject::class, 'removeGalleryItem'], ['admin', 'csrf']);

// Admin: Devlogs
$router->get('/admin/projects/{id}/devlog', [AdminDevlog::class, 'index'], ['admin']);
$router->get('/admin/projects/{id}/devlog/create', [AdminDevlog::class, 'create'], ['admin']);
$router->post('/admin/projects/{id}/devlog', [AdminDevlog::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/projects/{id}/devlog/{entryId}/edit', [AdminDevlog::class, 'edit'], ['admin']);
$router->post('/admin/projects/{id}/devlog/{entryId}', [AdminDevlog::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/devlog/{entryId}/delete', [AdminDevlog::class, 'delete'], ['admin', 'csrf']);

// Admin: Blog
$router->get('/admin/blog', [AdminBlog::class, 'index'], ['admin']);
$router->get('/admin/blog/create', [AdminBlog::class, 'create'], ['admin']);
$router->post('/admin/blog', [AdminBlog::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/blog/{id}/edit', [AdminBlog::class, 'edit'], ['admin']);
$router->post('/admin/blog/{id}', [AdminBlog::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/publish', [AdminBlog::class, 'publish'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/unpublish', [AdminBlog::class, 'unpublish'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/delete', [AdminBlog::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks', [AdminBlog::class, 'addBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/{blockId}', [AdminBlog::class, 'updateBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/{blockId}/delete', [AdminBlog::class, 'deleteBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/reorder', [AdminBlog::class, 'reorderBlocks'], ['admin', 'csrf']);

// Admin: Tags & Tech
$router->get('/admin/tags', [AdminTag::class, 'index'], ['admin']);
$router->post('/admin/tags', [AdminTag::class, 'storeTag'], ['admin', 'csrf']);
$router->post('/admin/tags/{id}/delete', [AdminTag::class, 'deleteTag'], ['admin', 'csrf']);
$router->post('/admin/tech', [AdminTag::class, 'storeTech'], ['admin', 'csrf']);
$router->post('/admin/tech/{id}/delete', [AdminTag::class, 'deleteTech'], ['admin', 'csrf']);
$router->post('/admin/blog-tags', [AdminTag::class, 'storeBlogTag'], ['admin', 'csrf']);
$router->post('/admin/blog-tags/{id}/delete', [AdminTag::class, 'deleteBlogTag'], ['admin', 'csrf']);
$router->post('/admin/categories', [AdminTag::class, 'storeCategory'], ['admin', 'csrf']);
$router->post('/admin/categories/{id}/delete', [AdminTag::class, 'deleteCategory'], ['admin', 'csrf']);

// Admin: Users
$router->get('/admin/users', [AdminUser::class, 'index'], ['admin']);
$router->get('/admin/users/{id}', [AdminUser::class, 'show'], ['admin']);
$router->post('/admin/users/{id}/toggle-admin', [AdminUser::class, 'toggleAdmin'], ['admin', 'csrf']);
$router->post('/admin/users/{id}/delete', [AdminUser::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/users/{id}/sessions/{sessionId}/delete', [AdminUser::class, 'destroySession'], ['admin', 'csrf']);

// Admin: Messages
$router->get('/admin/messages', [AdminMessage::class, 'index'], ['admin']);
$router->get('/admin/messages/{id}', [AdminMessage::class, 'show'], ['admin']);
$router->post('/admin/messages/{id}', [AdminMessage::class, 'reply'], ['admin', 'csrf']);

// Admin: Media
$router->get('/admin/media', [AdminMedia::class, 'index'], ['admin']);
$router->post('/admin/media', [AdminMedia::class, 'upload'], ['admin', 'csrf']);
$router->post('/admin/media/{id}', [AdminMedia::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/media/{id}/delete', [AdminMedia::class, 'delete'], ['admin', 'csrf']);

echo $router->dispatch($request->method(), $request->uri());