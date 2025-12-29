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
        
        return $this->render('admin/tags/index', [
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
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $slug = $this->slugify($name);
        
        if (Tag::findBySlug($slug)) {
            return $this->json(['error' => 'Tag already exists'], 400);
        }
        
        $id = Tag::create(['name' => $name, 'slug' => $slug]);
        
        if ($this->request->isHtmx()) {
            $tags = Tag::allOrdered();
            return $this->render('admin/partials/tag-list', ['tags' => $tags]);
        }
        
        return $this->json(['success' => true, 'id' => $id]);
    }
    
    public function deleteTag(array $params): string
    {
        $tag = Tag::find((int) $params['id']);
        
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], 404);
        }
        
        Tag::delete($tag['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    // Tech Stack
    public function storeTech(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        $tier = (int) $this->request->post('tier', 1);
        
        if (empty($name)) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $slug = $this->slugify($name);
        
        if (TechStack::findBySlug($slug)) {
            return $this->json(['error' => 'Tech already exists'], 400);
        }
        
        if ($tier < 1 || $tier > 4) {
            $tier = 1;
        }
        
        $id = TechStack::create(['name' => $name, 'slug' => $slug, 'tier' => $tier]);
        
        if ($this->request->isHtmx()) {
            $techStack = TechStack::allGroupedByTier();
            return $this->render('admin/partials/tech-list', ['techStack' => $techStack]);
        }
        
        return $this->json(['success' => true, 'id' => $id]);
    }
    
    public function deleteTech(array $params): string
    {
        $tech = TechStack::find((int) $params['id']);
        
        if (!$tech) {
            return $this->json(['error' => 'Tech not found'], 404);
        }
        
        TechStack::delete($tech['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    // Blog Tags
    public function storeBlogTag(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $id = BlogTag::findOrCreate($name);
        
        if ($this->request->isHtmx()) {
            $blogTags = BlogTag::allOrdered();
            return $this->render('admin/partials/blog-tag-list', ['blogTags' => $blogTags]);
        }
        
        return $this->json(['success' => true, 'id' => $id]);
    }
    
    public function deleteBlogTag(array $params): string
    {
        $tag = BlogTag::find((int) $params['id']);
        
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], 404);
        }
        
        BlogTag::delete($tag['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    // Blog Categories
    public function storeCategory(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $slug = $this->slugify($name);
        
        if (BlogCategory::findBySlug($slug)) {
            return $this->json(['error' => 'Category already exists'], 400);
        }
        
        $id = BlogCategory::create(['name' => $name, 'slug' => $slug]);
        
        if ($this->request->isHtmx()) {
            $blogCategories = BlogCategory::allOrdered();
            return $this->render('admin/partials/category-list', ['blogCategories' => $blogCategories]);
        }
        
        return $this->json(['success' => true, 'id' => $id]);
    }
    
    public function deleteCategory(array $params): string
    {
        $category = BlogCategory::find((int) $params['id']);
        
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }
        
        BlogCategory::delete($category['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}