<?php

namespace Quidque\Helpers;

/**
 * Handles SEO meta tags like robots directives.
 * 
 * Usage in controllers:
 *   $data['seo'] = Seo::noIndex();
 *   $data['seo'] = Seo::index();
 *   $data['seo'] = Seo::make()->noIndex()->noFollow()->get();
 * 
 * In layout:
 *   <?= $seo['robots_meta'] ?? '' ?>
 */
class Seo
{
    private bool $index = true;
    private bool $follow = true;
    private ?string $canonical = null;
    private ?string $description = null;
    
    public static function make(): self
    {
        return new self();
    }
    
    public static function index(): array
    {
        return (new self())->get();
    }
    
    public static function noIndex(): array
    {
        return (new self())->setNoIndex()->get();
    }
    
    public function setIndex(bool $index = true): self
    {
        $this->index = $index;
        return $this;
    }
    
    public function setNoIndex(): self
    {
        $this->index = false;
        return $this;
    }
    
    public function setFollow(bool $follow = true): self
    {
        $this->follow = $follow;
        return $this;
    }
    
    public function setNoFollow(): self
    {
        $this->follow = false;
        return $this;
    }
    
    public function setCanonical(string $url): self
    {
        $this->canonical = $url;
        return $this;
    }
    
    public function setDescription(string $description): self
    {
        $this->description = Str::truncate(strip_tags($description), 160);
        return $this;
    }
    
    public function get(): array
    {
        $robots = [];
        $robots[] = $this->index ? 'index' : 'noindex';
        $robots[] = $this->follow ? 'follow' : 'nofollow';
        
        $robotsContent = implode(', ', $robots);
        $robotsMeta = '<meta name="robots" content="' . $robotsContent . '">';
        
        $result = [
            'index' => $this->index,
            'follow' => $this->follow,
            'robots' => $robotsContent,
            'robots_meta' => $robotsMeta,
        ];
        
        if ($this->canonical) {
            $result['canonical'] = $this->canonical;
            $result['canonical_link'] = '<link rel="canonical" href="' . htmlspecialchars($this->canonical) . '">';
        }
        
        if ($this->description) {
            $result['description'] = $this->description;
            $result['description_meta'] = '<meta name="description" content="' . htmlspecialchars($this->description) . '">';
        }
        
        return $result;
    }
}