<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogCategory;

class SchemaGenerator
{
    /**
     * Generate Article schema for blog post
     */
    public function generateArticleSchema(BlogPost $post): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description ?? strip_tags(substr($post->content, 0, 160)),
            'image' => $post->cover_image ? $this->getFullUrl($post->cover_image) : null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
                'email' => $post->author->email,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'Genie Infotech'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->getFullUrl('/images/logo.png'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getFullUrl("/blog/{$post->slug}"),
            ],
        ];

        // Add word count if available
        if ($post->word_count) {
            $schema['wordCount'] = $post->word_count;
        }

        // Add reading time
        if ($post->reading_time) {
            $schema['timeRequired'] = "PT{$post->reading_time}M";
        }

        // Add keywords/tags
        if ($post->relationLoaded('tags')) {
            /** @var \Illuminate\Database\Eloquent\Collection|null $tags */
            $tags = $post->getRelation('tags');
            if ($tags && $tags->isNotEmpty()) {
                $schema['keywords'] = $tags->pluck('name')->implode(', ');
            }
        }

        return array_filter($schema, fn($value) => $value !== null);
    }

    /**
     * Generate BlogPosting schema
     */
    public function generateBlogPostingSchema(BlogPost $post): array
    {
        $schema = $this->generateArticleSchema($post);
        $schema['@type'] = 'BlogPosting';

        return $schema;
    }

    /**
     * Generate BreadcrumbList schema
     */
    public function generateBreadcrumbSchema(BlogPost $post): array
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $this->getFullUrl('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Blog',
                'item' => $this->getFullUrl('/blog'),
            ],
        ];

        if ($post->category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $post->category->name,
                'item' => $this->getFullUrl("/blog/category/{$post->category->slug}"),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $post->title,
            'item' => $this->getFullUrl("/blog/{$post->slug}"),
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Generate WebSite schema with search action
     */
    public function generateWebsiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name', 'Genie Infotech'),
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->getFullUrl('/blog/search?q={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Generate Organization schema
     */
    public function generateOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'Genie Infotech'),
            'url' => config('app.url'),
            'logo' => $this->getFullUrl('/images/logo.png'),
            'sameAs' => [
                // Add social media profiles here
                'https://facebook.com/genieinfo',
                'https://twitter.com/genieinfo',
                'https://linkedin.com/company/genieinfo',
            ],
        ];
    }

    /**
     * Generate complete schema for a blog post page
     */
    public function generateCompleteSchema(BlogPost $post): string
    {
        $schemas = [
            $this->generateArticleSchema($post),
            $this->generateBreadcrumbSchema($post),
        ];

        return json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Get full URL from path
     */
    protected function getFullUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path = ltrim($path, '/');
        
        return "{$baseUrl}/{$path}";
    }
}
