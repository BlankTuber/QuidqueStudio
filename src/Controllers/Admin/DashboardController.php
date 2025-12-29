<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Project;
use Quidque\Models\BlogPost;
use Quidque\Models\User;
use Quidque\Models\Comment;
use Quidque\Models\Message;
use Quidque\Models\Devlog;

class DashboardController extends Controller
{
    public function index(array $params): string
    {
        $stats = [
            'projects' => Project::count(),
            'posts' => BlogPost::countPublished(),
            'drafts' => BlogPost::count("status = 'draft'"),
            'users' => User::count(),
            'comments' => Comment::count(),
            'devlogs' => Devlog::count(),
        ];
        
        $recentProjects = Project::all('updated_at', 'DESC');
        $recentProjects = array_slice($recentProjects, 0, 5);
        
        $recentPosts = BlogPost::getDrafts();
        $recentPosts = array_slice($recentPosts, 0, 5);
        
        $conversations = Message::getAllConversations();
        $unreadCount = 0;
        foreach ($conversations as $conv) {
            $unreadCount += $conv['unread_count'];
        }
        
        return $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentPosts' => $recentPosts,
            'unreadMessages' => $unreadCount,
        ]);
    }
}