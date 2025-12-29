<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Tag;
use Quidque\Models\TechStack;
use Quidque\Models\BlogTag;
use Quidque\Models\BlogCategory;

class TagController extends Controller
{
    public function index(array $params): string
    {
        $tags = Tag::allOrdered();
        $techStack = TechStack::allGroupedByTier();
        $blogTags = BlogTag::allOrdered();
        $blogCategories = BlogCategory::allOrdered();
        
        return $this->renderAdmin('admin/tags/index', [
            'tags' => $tags,
            'techStack' => $techStack,
            'blogTags' => $blogTags,
            'blogCategories' => $blogCategories,
        ]);
    }
    
    // Project Tags
    public function storeTag(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = $this->slugify($name);
        
        if (Tag::findBySlug($slug)) {
            $this->redirect('/admin/tags?error=Tag+already+exists');
            return '';
        }
        
        Tag::create(['name' => $name, 'slug' => $slug]);
        
        $this->redirect('/admin/tags?saved=1');
        return '';
    }
    
    public function deleteTag(array $params): string
    {
        $tag = Tag::find((int) $params['id']);
        
        if (!$tag) {
            $this->redirect('/admin/tags?error=Tag+not+found');
            return '';
        }
        
        Tag::delete($tag['id']);
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    // Tech Stack
    public function storeTech(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        $tier = (int) $this->request->post('tier', 1);
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = $this->slugify($name);
        
        if (TechStack::findBySlug($slug)) {
            $this->redirect('/admin/tags?error=Tech+already+exists');
            return '';
        }
        
        if ($tier < 1 || $tier > 4) {
            $tier = 1;
        }
        
        TechStack::create(['name' => $name, 'slug' => $slug, 'tier' => $tier]);
        
        $this->redirect('/admin/tags?saved=1');
        return '';
    }
    
    public function deleteTech(array $params): string
    {
        $tech = TechStack::find((int) $params['id']);
        
        if (!$tech) {
            $this->redirect('/admin/tags?error=Tech+not+found');
            return '';
        }
        
        TechStack::delete($tech['id']);
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    // Blog Tags
    public function storeBlogTag(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        BlogTag::findOrCreate($name);
        
        $this->redirect('/admin/tags?saved=1');
        return '';
    }
    
    public function deleteBlogTag(array $params): string
    {
        $tag = BlogTag::find((int) $params['id']);
        
        if (!$tag) {
            $this->redirect('/admin/tags?error=Tag+not+found');
            return '';
        }
        
        BlogTag::delete($tag['id']);
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    // Blog Categories
    public function storeCategory(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = $this->slugify($name);
        
        if (BlogCategory::findBySlug($slug)) {
            $this->redirect('/admin/tags?error=Category+already+exists');
            return '';
        }
        
        BlogCategory::create(['name' => $name, 'slug' => $slug]);
        
        $this->redirect('/admin/tags?saved=1');
        return '';
    }
    
    public function deleteCategory(array $params): string
    {
        $category = BlogCategory::find((int) $params['id']);
        
        if (!$category) {
            $this->redirect('/admin/tags?error=Category+not+found');
            return '';
        }
        
        BlogCategory::delete($category['id']);
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}