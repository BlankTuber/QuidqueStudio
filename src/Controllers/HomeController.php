<?php

namespace Quidque\Controllers;

class HomeController extends Controller
{
    public function index(array $params): string
    {
        $featuredProjects = $this->db->fetchAll(
            "SELECT * FROM projects WHERE is_featured = 1 AND status = 'active' ORDER BY updated_at DESC LIMIT 3"
        );
        
        $recentPosts = $this->db->fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 4"
        );
        
        return $this->render('home', [
            'projects' => $featuredProjects,
            'posts' => $recentPosts,
        ]);
    }
}