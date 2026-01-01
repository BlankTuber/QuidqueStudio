<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;
use Quidque\Models\BlogPost;
use Quidque\Models\Devlog;
use Quidque\Helpers\Seo;

class HomeController extends Controller
{
    public function index(array $params): string
    {
        $featuredProjects = Project::getFeatured(3);
        $recentPosts = BlogPost::getRecent(4);
        $recentDevlogs = Devlog::getRecent(5);
        
        return $this->render('home', [
            'projects' => $featuredProjects,
            'posts' => $recentPosts,
            'devlogs' => $recentDevlogs,
            'seo' => Seo::index(),
        ]);
    }
}