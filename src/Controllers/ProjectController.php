<?php

namespace Quidque\Controllers;

use Quidque\Models\Project;
use Quidque\Models\ProjectBlock;
use Quidque\Models\GalleryItem;
use Quidque\Models\Comment;
use Quidque\Constants;

class ProjectController extends Controller
{
    public function index(array $params): string
    {
        $status = $this->request->get('status');
        $tag = $this->request->get('tag');
        $page = max(1, (int) $this->request->get('page', 1));
        $perPage = 12;
        
        if ($status && !Project::isValidStatus($status)) {
            $status = null;
        }
        
        if ($tag || $status) {
            $projects = Project::getAllWithTags($status, $tag);
            $totalPages = 1;
        } else {
            $result = Project::getPaginated($page, $perPage, $status);
            $projects = $result['items'];
            $totalPages = $result['total_pages'];
            
            foreach ($projects as &$project) {
                $tags = Project::getTags($project['id']);
                $project['tag_names'] = implode(', ', array_column($tags, 'name'));
                $project['tag_slugs'] = implode(',', array_column($tags, 'slug'));
            }
        }
        
        return $this->render('projects/index', [
            'projects' => $projects,
            'currentStatus' => $status,
            'currentTag' => $tag,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }
    
    public function show(array $params): string
    {
        $project = Project::findBySlug($params['slug']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $techStack = Project::getTechStack($project['id']);
        $tags = Project::getTags($project['id']);
        $blocks = ProjectBlock::getForProject($project['id']);
        $settings = Project::getSettings($project['id']);
        
        foreach ($blocks as &$block) {
            $block['data'] = json_decode($block['data'], true) ?? [];
            
            if ($block['block_type_slug'] === 'gallery') {
                $block['gallery_items'] = GalleryItem::getForBlock($block['id']);
            }
        }
        
        $comments = [];
        $commentCount = 0;
        if ($settings['comments_enabled'] ?? false) {
            $comments = Comment::getThreaded($project['id']);
            $commentCount = Comment::countForProject($project['id']);
        }
        
        return $this->render('projects/show', [
            'project' => $project,
            'techStack' => $techStack,
            'tags' => $tags,
            'blocks' => $blocks,
            'settings' => $settings,
            'comments' => $comments,
            'commentCount' => $commentCount,
        ]);
    }
    
    public function search(array $params): string
    {
        $query = trim($this->request->get('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return $this->render('projects/search', [
                'projects' => [],
                'query' => $query,
                'error' => 'Search query must be at least 2 characters',
            ]);
        }
        
        $projects = Project::search($query, 20);
        
        return $this->render('projects/search', [
            'projects' => $projects,
            'query' => $query,
        ]);
    }
}