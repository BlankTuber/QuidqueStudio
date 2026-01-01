<?php

use Quidque\Core\Router;
use Quidque\Middleware\AuthMiddleware;

// Public routes
$router->get('/', [\Quidque\Controllers\HomeController::class, 'index']);
$router->get('/about', [\Quidque\Controllers\AboutController::class, 'index']);

// Projects
$router->get('/projects', [\Quidque\Controllers\ProjectController::class, 'index']);
$router->get('/projects/search', [\Quidque\Controllers\ProjectController::class, 'search']);
$router->get('/projects/{slug}', [\Quidque\Controllers\ProjectController::class, 'show']);

// Blog
$router->get('/blog', [\Quidque\Controllers\BlogController::class, 'index']);
$router->get('/blog/category/{slug}', [\Quidque\Controllers\BlogController::class, 'byCategory']);
$router->get('/blog/tag/{slug}', [\Quidque\Controllers\BlogController::class, 'byTag']);
$router->get('/blog/{slug}', [\Quidque\Controllers\BlogController::class, 'show']);

// Devlog
$router->get('/devlog', [\Quidque\Controllers\DevlogController::class, 'feed']);
$router->get('/projects/{slug}/devlog', [\Quidque\Controllers\DevlogController::class, 'index']);
$router->get('/projects/{slug}/devlog/{entry}', [\Quidque\Controllers\DevlogController::class, 'show']);

// Auth (guests only)
$router->get('/login', [\Quidque\Controllers\AuthController::class, 'loginForm'], ['guest']);
$router->post('/login', [\Quidque\Controllers\AuthController::class, 'login'], ['guest', 'csrf']);
$router->get('/register', [\Quidque\Controllers\AuthController::class, 'registerForm'], ['guest']);
$router->post('/register', [\Quidque\Controllers\AuthController::class, 'register'], ['guest', 'csrf']);
$router->get('/auth/verify', [\Quidque\Controllers\AuthController::class, 'verify']);
$router->post('/logout', [\Quidque\Controllers\AuthController::class, 'logout'], ['auth', 'csrf']);

// Comments (auth required)
$router->post('/projects/{slug}/comments', [\Quidque\Controllers\CommentController::class, 'store'], ['auth', 'csrf']);
$router->post('/comments/{id}/update', [\Quidque\Controllers\CommentController::class, 'update'], ['auth', 'csrf']);
$router->post('/comments/{id}/delete', [\Quidque\Controllers\CommentController::class, 'delete'], ['auth', 'csrf']);

// Messages (auth required)
$router->get('/messages', [\Quidque\Controllers\MessageController::class, 'index'], ['auth']);
$router->post('/messages', [\Quidque\Controllers\MessageController::class, 'send'], ['auth', 'csrf']);

// User settings (auth required)
$router->get('/settings', [\Quidque\Controllers\UserController::class, 'settings'], ['auth']);
$router->post('/settings', [\Quidque\Controllers\UserController::class, 'updateSettings'], ['auth', 'csrf']);
$router->get('/settings/sessions', [\Quidque\Controllers\UserController::class, 'sessions'], ['auth']);
$router->post('/settings/sessions/{id}/destroy', [\Quidque\Controllers\UserController::class, 'destroySession'], ['auth', 'csrf']);
$router->post('/settings/sessions/destroy-all', [\Quidque\Controllers\UserController::class, 'destroyAllSessions'], ['auth', 'csrf']);
$router->get('/settings/delete', [\Quidque\Controllers\UserController::class, 'deleteAccount'], ['auth']);
$router->post('/settings/delete', [\Quidque\Controllers\UserController::class, 'confirmDeleteAccount'], ['auth', 'csrf']);

// Admin routes
$router->get('/admin', [\Quidque\Controllers\Admin\DashboardController::class, 'index'], ['admin']);

// Admin - Projects
$router->get('/admin/projects', [\Quidque\Controllers\Admin\ProjectController::class, 'index'], ['admin']);
$router->get('/admin/projects/create', [\Quidque\Controllers\Admin\ProjectController::class, 'create'], ['admin']);
$router->post('/admin/projects', [\Quidque\Controllers\Admin\ProjectController::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/projects/{id}/edit', [\Quidque\Controllers\Admin\ProjectController::class, 'edit'], ['admin']);
$router->post('/admin/projects/{id}', [\Quidque\Controllers\Admin\ProjectController::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/delete', [\Quidque\Controllers\Admin\ProjectController::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks', [\Quidque\Controllers\Admin\ProjectController::class, 'addBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/{blockId}', [\Quidque\Controllers\Admin\ProjectController::class, 'updateBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/{blockId}/delete', [\Quidque\Controllers\Admin\ProjectController::class, 'deleteBlock'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/reorder', [\Quidque\Controllers\Admin\ProjectController::class, 'reorderBlocks'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/blocks/{blockId}/gallery', [\Quidque\Controllers\Admin\ProjectController::class, 'addGalleryItems'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/gallery/{itemId}/delete', [\Quidque\Controllers\Admin\ProjectController::class, 'removeGalleryItem'], ['admin', 'csrf']);

// Admin - Devlog
$router->get('/admin/projects/{id}/devlog', [\Quidque\Controllers\Admin\DevlogController::class, 'index'], ['admin']);
$router->get('/admin/projects/{id}/devlog/create', [\Quidque\Controllers\Admin\DevlogController::class, 'create'], ['admin']);
$router->post('/admin/projects/{id}/devlog', [\Quidque\Controllers\Admin\DevlogController::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/projects/{id}/devlog/{entryId}/edit', [\Quidque\Controllers\Admin\DevlogController::class, 'edit'], ['admin']);
$router->post('/admin/projects/{id}/devlog/{entryId}', [\Quidque\Controllers\Admin\DevlogController::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/projects/{id}/devlog/{entryId}/delete', [\Quidque\Controllers\Admin\DevlogController::class, 'delete'], ['admin', 'csrf']);

// Admin - Blog
$router->get('/admin/blog', [\Quidque\Controllers\Admin\BlogController::class, 'index'], ['admin']);
$router->get('/admin/blog/create', [\Quidque\Controllers\Admin\BlogController::class, 'create'], ['admin']);
$router->post('/admin/blog', [\Quidque\Controllers\Admin\BlogController::class, 'store'], ['admin', 'csrf']);
$router->get('/admin/blog/{id}/edit', [\Quidque\Controllers\Admin\BlogController::class, 'edit'], ['admin']);
$router->post('/admin/blog/{id}', [\Quidque\Controllers\Admin\BlogController::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/publish', [\Quidque\Controllers\Admin\BlogController::class, 'publish'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/unpublish', [\Quidque\Controllers\Admin\BlogController::class, 'unpublish'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/delete', [\Quidque\Controllers\Admin\BlogController::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks', [\Quidque\Controllers\Admin\BlogController::class, 'addBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/{blockId}', [\Quidque\Controllers\Admin\BlogController::class, 'updateBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/{blockId}/delete', [\Quidque\Controllers\Admin\BlogController::class, 'deleteBlock'], ['admin', 'csrf']);
$router->post('/admin/blog/{id}/blocks/reorder', [\Quidque\Controllers\Admin\BlogController::class, 'reorderBlocks'], ['admin', 'csrf']);
$router->post('/admin/blog/tags', [\Quidque\Controllers\Admin\BlogController::class, 'createTag'], ['admin', 'csrf']);

// Admin - Media
$router->get('/admin/media', [\Quidque\Controllers\Admin\MediaController::class, 'index'], ['admin']);
$router->post('/admin/media', [\Quidque\Controllers\Admin\MediaController::class, 'upload'], ['admin', 'csrf']);
$router->post('/admin/media/ajax', [\Quidque\Controllers\Admin\MediaController::class, 'uploadAjax'], ['admin', 'csrf']);
$router->post('/admin/media/{id}', [\Quidque\Controllers\Admin\MediaController::class, 'update'], ['admin', 'csrf']);
$router->post('/admin/media/{id}/delete', [\Quidque\Controllers\Admin\MediaController::class, 'delete'], ['admin', 'csrf']);

// Admin - Tags
$router->get('/admin/tags', [\Quidque\Controllers\Admin\TagController::class, 'index'], ['admin']);
$router->post('/admin/tags/project', [\Quidque\Controllers\Admin\TagController::class, 'storeTag'], ['admin', 'csrf']);
$router->post('/admin/tags/project/{id}/delete', [\Quidque\Controllers\Admin\TagController::class, 'deleteTag'], ['admin', 'csrf']);
$router->post('/admin/tags/tech', [\Quidque\Controllers\Admin\TagController::class, 'storeTech'], ['admin', 'csrf']);
$router->post('/admin/tags/tech/{id}/delete', [\Quidque\Controllers\Admin\TagController::class, 'deleteTech'], ['admin', 'csrf']);
$router->post('/admin/tags/blog', [\Quidque\Controllers\Admin\TagController::class, 'storeBlogTag'], ['admin', 'csrf']);
$router->post('/admin/tags/blog/{id}/delete', [\Quidque\Controllers\Admin\TagController::class, 'deleteBlogTag'], ['admin', 'csrf']);
$router->post('/admin/tags/category', [\Quidque\Controllers\Admin\TagController::class, 'storeCategory'], ['admin', 'csrf']);
$router->post('/admin/tags/category/{id}/delete', [\Quidque\Controllers\Admin\TagController::class, 'deleteCategory'], ['admin', 'csrf']);

// Admin - Users
$router->get('/admin/users', [\Quidque\Controllers\Admin\UserController::class, 'index'], ['admin']);
$router->get('/admin/users/{id}', [\Quidque\Controllers\Admin\UserController::class, 'show'], ['admin']);
$router->post('/admin/users/{id}/toggle-admin', [\Quidque\Controllers\Admin\UserController::class, 'toggleAdmin'], ['admin', 'csrf']);
$router->post('/admin/users/{id}/delete', [\Quidque\Controllers\Admin\UserController::class, 'delete'], ['admin', 'csrf']);
$router->post('/admin/users/sessions/{sessionId}/destroy', [\Quidque\Controllers\Admin\UserController::class, 'destroySession'], ['admin', 'csrf']);

// Admin - Messages
$router->get('/admin/messages', [\Quidque\Controllers\Admin\MessageController::class, 'index'], ['admin']);
$router->get('/admin/messages/{id}', [\Quidque\Controllers\Admin\MessageController::class, 'show'], ['admin']);
$router->post('/admin/messages/{id}', [\Quidque\Controllers\Admin\MessageController::class, 'reply'], ['admin', 'csrf']);