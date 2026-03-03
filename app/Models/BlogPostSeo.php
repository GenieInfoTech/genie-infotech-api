<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostSeo extends Model
{
    protected $table = 'blog_post_seo';

    protected $fillable = [
        'post_id',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_type',
        'schema_json',
        'robots_index',
        'robots_follow',
        'breadcrumb_title',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function getSchemaDataAttribute(): ?array
    {
        return $this->schema_json ? json_decode($this->schema_json, true) : null;
    }

    public function setSchemaDataAttribute(array $value): void
    {
        $this->attributes['schema_json'] = json_encode($value);
    }
}
