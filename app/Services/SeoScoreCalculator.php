<?php

namespace App\Services;

use App\Models\BlogPost;

class SeoScoreCalculator
{
    /**
     * Calculate SEO score for a blog post (0-100)
     */
    public function calculate(BlogPost $post): int
    {
        $score = 0;

        // Title optimization (15 points)
        $titleLength = strlen($post->title);
        if ($titleLength >= 30 && $titleLength <= 60) {
            $score += 15;
        } elseif ($titleLength > 0) {
            $score += 8;
        }

        // Meta description (15 points)
        $metaDescLength = strlen($post->meta_description ?? '');
        if ($metaDescLength >= 120 && $metaDescLength <= 160) {
            $score += 15;
        } elseif ($metaDescLength > 0) {
            $score += 8;
        }

        // Slug quality (10 points)
        $slugWords = count(explode('-', $post->slug));
        if ($slugWords >= 3 && $slugWords <= 5) {
            $score += 10;
        } elseif ($slugWords > 0) {
            $score += 5;
        }

        // Content length (20 points)
        $wordCount = $post->word_count;
        if ($wordCount >= 1000 && $wordCount <= 2500) {
            $score += 20;
        } elseif ($wordCount >= 500) {
            $score += 12;
        } elseif ($wordCount >= 300) {
            $score += 6;
        }

        // Cover image (5 points)
        if ($post->cover_image) {
            $score += 5;
        }

        // Additional media with alt text (10 points)
        $mediaCount = $post->media()->count();
        $mediaWithAlt = $post->media()->whereNotNull('alt_text')->count();
        if ($mediaCount > 0 && $mediaWithAlt === $mediaCount) {
            $score += 10;
        } elseif ($mediaWithAlt > 0) {
            $score += 5;
        }

        // Focus keyword (10 points)
        if ($post->focus_keyword) {
            $keyword = strtolower($post->focus_keyword);
            $titleMatch = str_contains(strtolower($post->title), $keyword);
            $contentLower = strtolower($post->content);
            $keywordCount = substr_count($contentLower, $keyword);

            if ($titleMatch) $score += 5;
            if ($keywordCount >= 3 && $keywordCount <= 10) {
                $score += 5;
            } elseif ($keywordCount > 0) {
                $score += 2;
            }
        }

        // Internal links (5 points)
        $appUrl = config('app.url');
        if ($appUrl) {
            $internalLinks = substr_count($post->content, $appUrl);
            if ($internalLinks >= 2) {
                $score += 5;
            } elseif ($internalLinks > 0) {
                $score += 2;
            }
        }

        // External links (5 points)
        preg_match_all('/<a\s+[^>]*href=["\']https?:\/\//', $post->content, $matches);
        if (count($matches[0]) >= 1) {
            $score += 5;
        }

        // Canonical URL (5 points)
        if ($post->canonical_url) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Get SEO recommendations
     */
    public function getRecommendations(BlogPost $post): array
    {
        $recommendations = [];

        // Title
        $titleLength = strlen($post->title);
        if ($titleLength < 30) {
            $recommendations[] = 'Title is too short. Aim for 30-60 characters.';
        } elseif ($titleLength > 60) {
            $recommendations[] = 'Title is too long. Keep it under 60 characters.';
        }

        // Meta description
        if (!$post->meta_description) {
            $recommendations[] = 'Add a meta description for better search results.';
        } elseif (strlen($post->meta_description) < 120) {
            $recommendations[] = 'Meta description is too short. Aim for 120-160 characters.';
        }

        // Content length
        if ($post->word_count < 300) {
            $recommendations[] = 'Content is too short. Aim for at least 500 words.';
        }

        // Images
        if (!$post->cover_image) {
            $recommendations[] = 'Add a cover image to improve engagement.';
        }

        if ($post->media()->whereNull('alt_text')->exists()) {
            $recommendations[] = 'Add alt text to all images for accessibility and SEO.';
        }

        // Focus keyword
        if (!$post->focus_keyword) {
            $recommendations[] = 'Set a focus keyword to optimize your content.';
        }

        return $recommendations;
    }
}
