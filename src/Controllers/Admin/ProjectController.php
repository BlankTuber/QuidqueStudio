<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Project;
use Quidque\Models\Tag;
use Quidque\Models\TechStack;
use Quidque\Models\ProjectBlock;
use Quidque\Models\BlockType;
use Quidque\Models\GalleryItem;
use Quidque\Models\Media;

class ProjectController extends Controller
{
    public function index(array $params): string
    {
        $projects = Project::getAllWithTags();
        
        return $this->render('admin/projects/index', [
            'projects' => $projects,
        ]);
    }
    
    public function create(array $params): string
    {
        $tags = Tag::allOrdered();
        $techStack = TechStack::allGroupedByTier();
        
        return $this->render('admin/projects/form', [
            'project' => null,
            'tags' => $tags,
            'techStack' => $techStack,
            'selectedTags' => [],
            'selectedTech' => [],
        ]);
    }
    
    public function store(array $params): string
    {
        $data = $this->validateProject();
        
        if (isset($data['error'])) {
            return $this->render('admin/projects/form', [
                'project' => null,
                'tags' => Tag::allOrdered(),
                'techStack' => TechStack::allGroupedByTier(),
                'selectedTags' => [],
                'selectedTech' => [],
                'error' => $data['error'],
            ]);
        }
        
        $projectId = Project::create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'status' => $data['status'],
            'is_featured' => $data['is_featured'],
            'settings' => json_encode($data['settings']),
        ]);
        
        if (!empty($data['tags'])) {
            Project::setTags($projectId, $data['tags']);
        }
        
        if (!empty($data['tech'])) {
            Project::setTechStack($projectId, $data['tech']);
        }
        
        $this->redirect('/admin/projects/' . $projectId . '/edit?created=1');
    }
    
    public function edit(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $tags = Tag::allOrdered();
        $techStack = TechStack::allGroupedByTier();
        $selectedTags = array_column(Project::getTags($project['id']), 'id');
        $selectedTech = array_column(Project::getTechStack($project['id']), 'id');
        $blocks = ProjectBlock::getForProject($project['id']);
        $blockTypes = BlockType::all('name', 'ASC');
        
        return $this->render('admin/projects/form', [
            'project' => $project,
            'tags' => $tags,
            'techStack' => $techStack,
            'selectedTags' => $selectedTags,
            'selectedTech' => $selectedTech,
            'blocks' => $blocks,
            'blockTypes' => $blockTypes,
        ]);
    }
    
    public function update(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->notFound();
        }
        
        $data = $this->validateProject($project['id']);
        
        if (isset($data['error'])) {
            return $this->render('admin/projects/form', [
                'project' => $project,
                'tags' => Tag::allOrdered(),
                'techStack' => TechStack::allGroupedByTier(),
                'selectedTags' => array_column(Project::getTags($project['id']), 'id'),
                'selectedTech' => array_column(Project::getTechStack($project['id']), 'id'),
                'blocks' => ProjectBlock::getForProject($project['id']),
                'blockTypes' => BlockType::all('name', 'ASC'),
                'error' => $data['error'],
            ]);
        }
        
        Project::update($project['id'], [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'status' => $data['status'],
            'is_featured' => $data['is_featured'],
            'settings' => json_encode($data['settings']),
        ]);
        
        Project::setTags($project['id'], $data['tags'] ?? []);
        Project::setTechStack($project['id'], $data['tech'] ?? []);
        
        $this->redirect('/admin/projects/' . $project['id'] . '/edit?saved=1');
    }
    
    public function delete(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }
        
        Project::delete($project['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/projects?deleted=1');
    }
    
    public function addBlock(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }
        
        $blockTypeId = (int) $this->request->post('block_type_id');
        $blockType = BlockType::find($blockTypeId);
        
        if (!$blockType) {
            return $this->json(['error' => 'Invalid block type'], 400);
        }
        
        $maxOrder = $this->db->fetch(
            "SELECT MAX(sort_order) as max_order FROM project_blocks WHERE project_id = ?",
            [$project['id']]
        );
        $sortOrder = ($maxOrder['max_order'] ?? -1) + 1;
        
        $blockId = ProjectBlock::createBlock($project['id'], $blockTypeId, [], $sortOrder);
        
        if ($this->request->isHtmx()) {
            $blocks = ProjectBlock::getForProject($project['id']);
            $blockTypes = BlockType::all('name', 'ASC');
            return $this->render('admin/partials/project-blocks', [
                'blocks' => $blocks,
                'blockTypes' => $blockTypes,
                'project' => $project,
            ]);
        }
        
        return $this->json(['success' => true, 'id' => $blockId]);
    }
    
    public function updateBlock(array $params): string
    {
        $block = ProjectBlock::find((int) $params['blockId']);
        
        if (!$block) {
            return $this->json(['error' => 'Block not found'], 404);
        }
        
        $data = $this->request->post('data', []);
        ProjectBlock::updateData($block['id'], $data);
        
        return $this->json(['success' => true]);
    }
    
    public function deleteBlock(array $params): string
    {
        $block = ProjectBlock::find((int) $params['blockId']);
        
        if (!$block) {
            return $this->json(['error' => 'Block not found'], 404);
        }
        
        GalleryItem::deleteForBlock($block['id']);
        ProjectBlock::delete($block['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    public function reorderBlocks(array $params): string
    {
        $project = Project::find((int) $params['id']);
        
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }
        
        $order = $this->request->post('order', []);
        ProjectBlock::reorder($project['id'], $order);
        
        return $this->json(['success' => true]);
    }
    
    private function validateProject(?int $excludeId = null): array
    {
        $title = trim($this->request->post('title', ''));
        $slug = trim($this->request->post('slug', ''));
        $description = trim($this->request->post('description', ''));
        $status = $this->request->post('status', 'active');
        $isFeatured = $this->request->post('is_featured') === '1';
        $tags = $this->request->post('tags', []);
        $tech = $this->request->post('tech', []);
        $devlogEnabled = $this->request->post('devlog_enabled') === '1';
        $commentsEnabled = $this->request->post('comments_enabled') === '1';
        
        if (empty($title)) {
            return ['error' => 'Title is required'];
        }
        
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        
        $existing = Project::findBySlug($slug);
        if ($existing && $existing['id'] !== $excludeId) {
            return ['error' => 'Slug already exists'];
        }
        
        if (!in_array($status, ['active', 'complete', 'on_hold', 'archived'])) {
            return ['error' => 'Invalid status'];
        }
        
        return [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'is_featured' => $isFeatured ? 1 : 0,
            'tags' => array_map('intval', $tags),
            'tech' => array_map('intval', $tech),
            'settings' => [
                'devlog_enabled' => $devlogEnabled,
                'comments_enabled' => $commentsEnabled,
            ],
        ];
    }
    
    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}