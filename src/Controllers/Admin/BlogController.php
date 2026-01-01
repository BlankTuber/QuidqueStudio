<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Core\Auth;
use Quidque\Models\BlogPost;
use Quidque\Models\BlogBlock;
use Quidque\Models\BlogBlockType;
use Quidque\Models\BlogCategory;
use Quidque\Models\BlogTag;
use Quidque\Models\Media;
use Quidque\Models\Project;
use Quidque\Helpers\Str;
use Quidque\Constants;

class BlogController extends Controller
{
    public function index(array $params): string
    {
        $status = $this->request->get('status');
        
        if ($status === Constants::POST_PUBLISHED) {
            $posts = BlogPost::getPublished(100);
        } elseif ($status === Constants::POST_DRAFT) {
            $posts = BlogPost::getDrafts();
        } else {
            $published = BlogPost::getPublished(100);
            $drafts = BlogPost::getDrafts();
            $posts = array_merge($drafts, $published);
        }
        
        return $this->renderAdmin('admin/blog/index', [
            'posts' => $posts,
            'currentStatus' => $status,
        ]);
    }
    
    public function create(array $params): string
    {
        $categories = BlogCategory::allOrdered();
        $tags = BlogTag::allOrdered();
        
        return $this->renderAdmin('admin/blog/form', [
            'post' => null,
            'categories' => $categories,
            'tags' => $tags,
            'blockTypes' => [],
            'selectedTags' => [],
            'blocks' => [],
        ]);
    }
    
    public function store(array $params): string
    {
        $data = $this->validatePost();
        
        if (isset($data['error'])) {
            return $this->renderAdmin('admin/blog/form', [
                'post' => null,
                'categories' => BlogCategory::allOrdered(),
                'tags' => BlogTag::allOrdered(),
                'blockTypes' => [],
                'selectedTags' => [],
                'blocks' => [],
                'error' => $data['error'],
            ]);
        }
        
        $postId = BlogPost::create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'author_id' => Auth::id(),
            'category_id' => $data['category_id'],
            'status' => Constants::POST_DRAFT,
        ]);
        
        if (!empty($data['tags'])) {
            BlogPost::setTags($postId, $data['tags']);
        }
        
        $this->redirect('/admin/blog/' . $postId . '/edit?created=1');
    }
    
    public function edit(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->notFound();
        }
        
        $categories = BlogCategory::allOrdered();
        $tags = BlogTag::allOrdered();
        $blockTypes = BlogBlockType::all('name', 'ASC');
        $selectedTags = array_column(BlogPost::getTags($post['id']), 'id');
        $blocks = BlogBlock::getForPost($post['id']);
        $allMedia = Media::images();
        $allProjects = Project::all('title', 'ASC');
        
        return $this->renderAdmin('admin/blog/form', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'blockTypes' => $blockTypes,
            'selectedTags' => $selectedTags,
            'blocks' => $blocks,
            'allMedia' => $allMedia,
            'allProjects' => $allProjects,
        ]);
    }
    
    public function update(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->notFound();
        }
        
        $data = $this->validatePost($post['id']);
        
        if (isset($data['error'])) {
            return $this->renderAdmin('admin/blog/form', [
                'post' => $post,
                'categories' => BlogCategory::allOrdered(),
                'tags' => BlogTag::allOrdered(),
                'blockTypes' => BlogBlockType::all('name', 'ASC'),
                'selectedTags' => array_column(BlogPost::getTags($post['id']), 'id'),
                'blocks' => BlogBlock::getForPost($post['id']),
                'allMedia' => Media::images(),
                'allProjects' => Project::all('title', 'ASC'),
                'error' => $data['error'],
            ]);
        }
        
        BlogPost::update($post['id'], [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
        ]);
        
        BlogPost::setTags($post['id'], $data['tags'] ?? []);
        
        $blocks = $this->request->post('blocks', []);
        foreach ($blocks as $blockId => $blockData) {
            BlogBlock::updateData((int) $blockId, $blockData);
        }
        
        $this->redirect('/admin/blog/' . $post['id'] . '/edit?saved=1');
    }
    
    public function publish(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }
        
        BlogPost::publish($post['id']);
        
        $this->redirect('/admin/blog/' . $post['id'] . '/edit?published=1');
    }
    
    public function unpublish(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }
        
        BlogPost::unpublish($post['id']);
        
        $this->redirect('/admin/blog/' . $post['id'] . '/edit?unpublished=1');
    }
    
    public function delete(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }
        
        BlogBlock::deleteForPost($post['id']);
        BlogPost::delete($post['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/blog?deleted=1');
    }
    
    public function addBlock(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }
        
        $blockTypeId = (int) $this->request->post('block_type_id');
        $blockType = BlogBlockType::find($blockTypeId);
        
        if (!$blockType) {
            return $this->json(['error' => 'Invalid block type'], 400);
        }
        
        $maxOrder = $this->db->fetch(
            "SELECT MAX(sort_order) as max_order FROM blog_blocks WHERE post_id = ?",
            [$post['id']]
        );
        $sortOrder = ($maxOrder['max_order'] ?? -1) + 1;
        
        BlogBlock::createBlock($post['id'], $blockTypeId, [], $sortOrder);
        
        $this->redirect('/admin/blog/' . $post['id'] . '/edit#blocks-section');
    }
    
    public function updateBlock(array $params): string
    {
        $block = BlogBlock::find((int) $params['blockId']);
        
        if (!$block) {
            return $this->json(['error' => 'Block not found'], 404);
        }
        
        $data = $this->request->post('data', []);
        BlogBlock::updateData($block['id'], $data);
        
        return $this->json(['success' => true]);
    }
    
    public function deleteBlock(array $params): string
    {
        $block = BlogBlock::find((int) $params['blockId']);
        
        if (!$block) {
            return $this->json(['error' => 'Block not found'], 404);
        }
        
        $postId = $block['post_id'];
        BlogBlock::delete($block['id']);
        
        $this->redirect('/admin/blog/' . $postId . '/edit#blocks-section');
    }
    
    public function reorderBlocks(array $params): string
    {
        $post = BlogPost::find((int) $params['id']);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }
        
        $order = $this->request->post('order', []);
        BlogBlock::reorder($post['id'], $order);
        
        return $this->json(['success' => true]);
    }
    
    public function createTag(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            return $this->json(['error' => 'Tag name is required'], 400);
        }
        
        $tagId = BlogTag::findOrCreate($name);
        $tag = BlogTag::find($tagId);
        
        return $this->json([
            'success' => true,
            'tag' => [
                'id' => $tag['id'],
                'name' => $tag['name'],
                'slug' => $tag['slug'],
            ]
        ]);
    }
    
    private function validatePost(?int $excludeId = null): array
    {
        $title = trim($this->request->post('title', ''));
        $slug = trim($this->request->post('slug', ''));
        $categoryId = $this->request->post('category_id');
        $tags = $this->request->post('tags', []);
        
        if (empty($title)) {
            return ['error' => 'Title is required'];
        }
        
        if (empty($slug)) {
            $slug = Str::slug($title);
        }
        
        $existing = BlogPost::findBySlug($slug);
        if ($existing && $existing['id'] !== $excludeId) {
            return ['error' => 'Slug already exists'];
        }
        
        return [
            'title' => $title,
            'slug' => $slug,
            'category_id' => $categoryId ? (int) $categoryId : null,
            'tags' => array_map('intval', $tags),
        ];
    }
}