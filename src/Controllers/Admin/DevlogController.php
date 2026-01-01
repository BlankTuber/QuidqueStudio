<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Project;
use Quidque\Models\Devlog;

class DevlogController extends Controller
{
    public function index(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $entries = Devlog::getForProject($project['id'], 100);
        
        return $this->renderAdmin('admin/devlog/index', [
            'project' => $project,
            'entries' => $entries,
        ]);
    }
    
    public function create(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        return $this->renderAdmin('admin/devlog/form', [
            'project' => $project,
            'entry' => null,
        ]);
    }
    
    public function store(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $title = trim($this->request->post('title', ''));
        $content = trim($this->request->post('content', ''));
        
        if (empty($title)) {
            return $this->renderAdmin('admin/devlog/form', [
                'project' => $project,
                'entry' => null,
                'error' => 'Title is required',
            ]);
        }
        
        Devlog::createEntry($project['id'], $title, $content);
        
        $this->redirect('/admin/projects/' . $project['id'] . '/devlog?created=1');
    }
    
    public function edit(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $entry = Devlog::find((int) $params['entryId']);
        
        if (!$entry || $entry['project_id'] !== $project['id']) {
            return $this->notFound();
        }
        
        return $this->renderAdmin('admin/devlog/form', [
            'project' => $project,
            'entry' => $entry,
        ]);
    }
    
    public function update(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $entry = Devlog::find((int) $params['entryId']);
        
        if (!$entry || $entry['project_id'] !== $project['id']) {
            return $this->notFound();
        }
        
        $title = trim($this->request->post('title', ''));
        $content = trim($this->request->post('content', ''));
        
        if (empty($title)) {
            return $this->renderAdmin('admin/devlog/form', [
                'project' => $project,
                'entry' => $entry,
                'error' => 'Title is required',
            ]);
        }
        
        Devlog::update($entry['id'], [
            'title' => $title,
            'content' => $content,
        ]);
        
        $this->redirect('/admin/projects/' . $project['id'] . '/devlog?saved=1');
    }
    
    public function delete(array $params): string
    {
        $entry = Devlog::find((int) $params['entryId']);
        
        if (!$entry) {
            return $this->json(['error' => 'Entry not found'], 404);
        }
        
        $projectId = $entry['project_id'];
        Devlog::delete($entry['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/projects/' . $projectId . '/devlog?deleted=1');
    }
}