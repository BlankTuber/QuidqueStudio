<?php

namespace Quidque\Controllers;

use Quidque\Models\BlogPost;
use Quidque\Models\BlogBlock;
use Quidque\Models\Devlog;
use Quidque\Models\Project;

class FeedController extends Controller
{
    public function blog(array $params): string
    {
        $posts = BlogPost::getPublished(20);
        
        return $this->rss([
            'title' => $this->config['site']['name'] . ' - Blog',
            'description' => 'Latest blog posts from ' . $this->config['site']['name'],
            'link' => $this->config['site']['url'] . '/blog',
            'items' => array_map(function ($post) {
                return [
                    'title' => $post['title'],
                    'link' => $this->config['site']['url'] . '/blog/' . $post['slug'],
                    'description' => $this->getPostExcerpt($post),
                    'pubDate' => $post['published_at'],
                    'guid' => $this->config['site']['url'] . '/blog/' . $post['slug'],
                    'author' => $post['author_name'] ?? 'Admin',
                ];
            }, $posts),
        ]);
    }
    
    public function devlog(array $params): string
    {
        $entries = Devlog::getRecent(20);
        
        return $this->rss([
            'title' => $this->config['site']['name'] . ' - Devlog',
            'description' => 'Latest development updates from ' . $this->config['site']['name'],
            'link' => $this->config['site']['url'] . '/devlog',
            'items' => array_map(function ($entry) {
                return [
                    'title' => ($entry['project_title'] ?? 'Update') . ': ' . $entry['title'],
                    'link' => $this->config['site']['url'] . '/projects/' . $entry['project_slug'] . '/devlog/' . $entry['slug'],
                    'description' => $this->truncate($entry['content'] ?? '', 300),
                    'pubDate' => $entry['created_at'],
                    'guid' => $this->config['site']['url'] . '/projects/' . $entry['project_slug'] . '/devlog/' . $entry['slug'],
                ];
            }, $entries),
        ]);
    }
    
    public function project(array $params): string
    {
        $project = Project::findBySlug($params['slug']);
        
        if (!$project) {
            http_response_code(404);
            return '<?xml version="1.0" encoding="UTF-8"?><error>Project not found</error>';
        }
        
        $settings = Project::getSettings($project['id']);
        if (!($settings['devlog_enabled'] ?? false)) {
            http_response_code(404);
            return '<?xml version="1.0" encoding="UTF-8"?><error>Devlog not enabled</error>';
        }
        
        $entries = Devlog::getForProject($project['id'], 20);
        
        return $this->rss([
            'title' => $project['title'] . ' - Devlog',
            'description' => 'Development updates for ' . $project['title'],
            'link' => $this->config['site']['url'] . '/projects/' . $project['slug'] . '/devlog',
            'items' => array_map(function ($entry) use ($project) {
                return [
                    'title' => $entry['title'],
                    'link' => $this->config['site']['url'] . '/projects/' . $project['slug'] . '/devlog/' . $entry['slug'],
                    'description' => $this->truncate($entry['content'] ?? '', 300),
                    'pubDate' => $entry['created_at'],
                    'guid' => $this->config['site']['url'] . '/projects/' . $project['slug'] . '/devlog/' . $entry['slug'],
                ];
            }, $entries),
        ]);
    }
    
    private function rss(array $feed): string
    {
        header('Content-Type: application/rss+xml; charset=UTF-8');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>' . $this->escape($feed['title']) . '</title>' . "\n";
        $xml .= '<link>' . $this->escape($feed['link']) . '</link>' . "\n";
        $xml .= '<description>' . $this->escape($feed['description']) . '</description>' . "\n";
        $xml .= '<language>en-us</language>' . "\n";
        $xml .= '<lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        $xml .= '<atom:link href="' . $this->escape($feed['link']) . '/feed" rel="self" type="application/rss+xml"/>' . "\n";
        
        foreach ($feed['items'] as $item) {
            $xml .= '<item>' . "\n";
            $xml .= '<title>' . $this->escape($item['title']) . '</title>' . "\n";
            $xml .= '<link>' . $this->escape($item['link']) . '</link>' . "\n";
            $xml .= '<description><![CDATA[' . $item['description'] . ']]></description>' . "\n";
            $xml .= '<pubDate>' . date('r', strtotime($item['pubDate'])) . '</pubDate>' . "\n";
            $xml .= '<guid isPermaLink="true">' . $this->escape($item['guid']) . '</guid>' . "\n";
            if (!empty($item['author'])) {
                $xml .= '<author>' . $this->escape($item['author']) . '</author>' . "\n";
            }
            $xml .= '</item>' . "\n";
        }
        
        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';
        
        return $xml;
    }
    
    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
    
    private function truncate(string $text, int $length): string
    {
        $text = strip_tags($text);
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 3) . '...';
    }
    
    private function getPostExcerpt(array $post): string
    {
        $blocks = BlogBlock::getForPost($post['id']);
        
        foreach ($blocks as $block) {
            if ($block['block_type_slug'] === 'text') {
                $data = json_decode($block['data'], true) ?? [];
                if (!empty($data['content'])) {
                    return $this->truncate($data['content'], 300);
                }
            }
        }
        
        return 'Read more...';
    }
}