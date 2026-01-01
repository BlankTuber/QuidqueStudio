<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;
use Quidque\Models\Devlog;
use Quidque\Helpers\Seo;

class DevlogController extends Controller
{
    public function index(array $params): string
    {
        $project = Project::findBySlug($params['slug']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $settings = Project::getSettings($project['id']);
        if (!($settings['devlog_enabled'] ?? false)) {
            return $this->notFound();
        }
        
        $entries = Devlog::getForProject($project['id'], 50);
        
        return $this->render('devlog/index', [
            'project' => $project,
            'entries' => $entries,
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function show(array $params): string
    {
        $project = Project::findBySlug($params['slug']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $entry = Devlog::findBySlug($project['id'], $params['entry']);
        
        if (!$entry) {
            return $this->notFound();
        }
        
        return $this->render('devlog/show', [
            'project' => $project,
            'entry' => $entry,
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function feed(array $params): string
    {
        $entries = Devlog::getRecent(20);
        
        if ($this->request->isHtmx()) {
            return $this->render('partials/devlog-feed', [
                'entries' => $entries,
            ]);
        }
        
        return $this->render('devlog/feed', [
            'entries' => $entries,
            'seo' => Seo::noIndex(),
        ]);
    }
}