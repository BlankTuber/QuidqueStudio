<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Tag;
use Quidque\Models\TechStack;
use Quidque\Models\BlogTag;
use Quidque\Models\BlogCategory;
use Quidque\Helpers\Str;
use Quidque\Constants;

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
    
    public function storeTag(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = Str::slug($name);
        
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
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    public function storeTech(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        $tier = (int) $this->request->post('tier', Constants::TIER_CORE);
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = Str::slug($name);
        
        if (TechStack::findBySlug($slug)) {
            $this->redirect('/admin/tags?error=Tech+already+exists');
            return '';
        }
        
        if ($tier < Constants::TIER_CORE || $tier > Constants::TIER_TOOL) {
            $tier = Constants::TIER_CORE;
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
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
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
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
    
    public function storeCategory(array $params): string
    {
        $name = trim($this->request->post('name', ''));
        
        if (empty($name)) {
            $this->redirect('/admin/tags?error=Name+is+required');
            return '';
        }
        
        $slug = Str::slug($name);
        
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
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/tags?deleted=1');
        return '';
    }
}