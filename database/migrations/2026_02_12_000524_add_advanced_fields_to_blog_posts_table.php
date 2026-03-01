<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Featured & Display
            $table->boolean('featured')->default(false)->after('published');
            $table->boolean('sticky')->default(false)->after('featured');
            $table->timestamp('featured_until')->nullable()->after('sticky');
            
            // Content Metrics
            $table->integer('reading_time')->default(0)->after('content');
            $table->integer('word_count')->default(0)->after('reading_time');
            
            // Status Enhancement
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])
                ->default('draft')->after('published');
            $table->timestamp('scheduled_for')->nullable()->after('published_at');
            
            // SEO
            $table->string('focus_keyword', 100)->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('focus_keyword');
            
            // Engagement
            $table->boolean('allow_comments')->default(true)->after('views');
            $table->integer('comment_count')->default(0)->after('allow_comments');
            $table->integer('share_count')->default(0)->after('comment_count');
            $table->integer('like_count')->default(0)->after('share_count');
            
            // Tracking
            $table->foreignId('last_modified_by')->nullable()
                ->constrained('users')->onDelete('set null')->after('author_id');
            
            // Password Protection
            $table->string('password')->nullable()->after('slug');
            
            // Indexes
            $table->index('status');
            $table->index('featured');
            $table->index('sticky');
            $table->index(['status', 'published_at']);
            $table->index(['featured', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['blog_posts_status_index']);
            $table->dropIndex(['blog_posts_featured_index']);
            $table->dropIndex(['blog_posts_sticky_index']);
            $table->dropIndex(['blog_posts_status_published_at_index']);
            $table->dropIndex(['blog_posts_featured_published_at_index']);
            
            $table->dropColumn([
                'featured', 'sticky', 'featured_until',
                'reading_time', 'word_count',
                'status', 'scheduled_for',
                'focus_keyword', 'canonical_url',
                'allow_comments', 'comment_count', 'share_count', 'like_count',
                'last_modified_by', 'password'
            ]);
        });
    }
};
