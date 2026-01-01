<?php

namespace Quidque\Controllers;

use Quidque\Models\BlogPost;
use Quidque\Models\BlogBlock;
use Quidque\Models\BlogCategory;
use Quidque\Models\BlogTag;
use Quidque\Models\Media;
use Quidque\Helpers\Seo;
use Quidque\Constants;

class BlogController extends Controller
{
    public function index(array $params): string
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $posts = BlogPost::getPublished($limit, $offset);
        $total = BlogPost::countPublished();
        $totalPages = (int) ceil($total / $limit);
        
        return $this->render('blog/index', [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'seo' => Seo::index(),
        ]);
    }
    
    public function show(array $params): string
    {
        $post = BlogPost::findBySlug($params['slug']);
        
        if (!$post || $post['status'] !== Constants::POST_PUBLISHED) {
            return $this->notFound();
        }
        
        $blocks = BlogBlock::getForPost($post['id']);
        $tags = BlogPost::getTags($post['id']);
        $category = $post['category_id'] ? BlogCategory::find($post['category_id']) : null;
        
        foreach ($blocks as &$block) {
            $block['data'] = json_decode($block['data'], true) ?? [];
            
            if ($block['block_type_slug'] === 'image' && !empty($block['data']['media_id'])) {
                $block['media'] = Media::find((int) $block['data']['media_id']);
            }
        }
        
        $seo = Seo::make()->setNoIndex();
        $excerpt = BlogPost::getExcerpt($post['id']);
        if ($excerpt) {
            $seo->setDescription($excerpt);
        }
        
        return $this->render('blog/show', [
            'post' => $post,
            'blocks' => $blocks,
            'tags' => $tags,
            'category' => $category,
            'seo' => $seo->get(),
        ]);
    }
    
    public function byCategory(array $params): string
    {
        $category = BlogCategory::findBySlug($params['slug']);
        
        if (!$category) {
            return $this->notFound();
        }
        
        $posts = BlogPost::getByCategory($params['slug']);
        
        return $this->render('blog/index', [
            'posts' => $posts,
            'category' => $category,
            'page' => 1,
            'totalPages' => 1,
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function byTag(array $params): string
    {
        $tag = BlogTag::findBySlug($params['slug']);
        
        if (!$tag) {
            return $this->notFound();
        }
        
        $posts = BlogPost::getByTag($params['slug']);
        
        return $this->render('blog/index', [
            'posts' => $posts,
            'tag' => $tag,
            'page' => 1,
            'totalPages' => 1,
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function search(array $params): string
    {
        $query = trim($this->request->get('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return $this->render('blog/search', [
                'posts' => [],
                'query' => $query,
                'error' => 'Search query must be at least 2 characters',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $posts = BlogPost::search($query, 20);
        
        return $this->render('blog/search', [
            'posts' => $posts,
            'query' => $query,
            'seo' => Seo::noIndex(),
        ]);
    }
}