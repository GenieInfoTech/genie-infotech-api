<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostAnalytics extends Model
{
    protected $fillable = [
        'post_id',
        'date',
        'views',
        'unique_views',
        'avg_time_on_page',
        'bounce_rate',
        'scroll_depth',
        'shares',
        'likes',
        'organic_views',
        'social_views',
        'direct_views',
        'referral_views',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'unique_views' => 'integer',
            'avg_time_on_page' => 'integer',
            'bounce_rate' => 'decimal:2',
            'scroll_depth' => 'integer',
            'shares' => 'integer',
            'likes' => 'integer',
            'organic_views' => 'integer',
            'social_views' => 'integer',
            'direct_views' => 'integer',
            'referral_views' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function getEngagementRateAttribute(): float
    {
        if ($this->unique_views === 0) {
            return 0;
        }
        $engaged = $this->shares + $this->likes;
        return round(($engaged / $this->unique_views) * 100, 2);
    }
}
