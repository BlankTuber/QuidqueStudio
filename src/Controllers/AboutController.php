<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;

class AboutController extends Controller
{
    public function index(array $params): string
    {
        $currentProject = Project::getFeatured(1);
        $currentProject = $currentProject[0] ?? null;
        
        return $this->render('about/index', [
            'currentProject' => $currentProject,
        ]);
    }
}