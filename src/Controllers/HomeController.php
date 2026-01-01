<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;
use Quidque\Models\BlogPost;

class HomeController extends Controller
{
    public function index(array $params): string
    {
        $featuredProjects = Project::getFeatured(3);
        $recentPosts = BlogPost::getRecent(4);
        
        return $this->render('home', [
            'projects' => $featuredProjects,
            'posts' => $recentPosts,
        ]);
    }
}