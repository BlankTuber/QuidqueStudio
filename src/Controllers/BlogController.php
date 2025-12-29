<?php

namespace Quidque\Controllers;

use Quidque\Models\BlogPost;
use Quidque\Models\BlogBlock;
use Quidque\Models\BlogCategory;
use Quidque\Models\BlogTag;

class BlogController extends Controller
{
    public function index(array $params): string
    {
        $page = (int) $this->request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $posts = BlogPost::getPublished($limit, $offset);
        $total = BlogPost::countPublished();
        $totalPages = ceil($total / $limit);
        
        return $this->render('blog/index', [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }
    
    public function show(array $params): string
    {
        $post = BlogPost::findBySlug($params['slug']);
        
        if (!$post || $post['status'] !== 'published') {
            return $this->notFound();
        }
        
        $blocks = BlogBlock::getForPost($post['id']);
        $tags = BlogPost::getTags($post['id']);
        
        return $this->render('blog/show', [
            'post' => $post,
            'blocks' => $blocks,
            'tags' => $tags,
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
        ]);
    }
}