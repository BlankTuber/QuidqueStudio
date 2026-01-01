<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;
use Quidque\Helpers\Seo;

class AboutController extends Controller
{
    public function index(array $params): string
    {
        $currentProject = Project::getCurrentlyWorkingOn();
        $featuredProjects = Project::getFeatured(3);
        
        return $this->render('about/index', [
            'currentProject' => $currentProject,
            'featuredProjects' => $featuredProjects,
            'seo' => Seo::index(),
        ]);
    }
}