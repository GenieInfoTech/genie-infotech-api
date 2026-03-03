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
        Schema::create('blog_post_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained('blog_posts')->onDelete('cascade');
            
            // OpenGraph
            $table->string('og_title', 60)->nullable();
            $table->string('og_description', 160)->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type', 50)->default('article');
            
            // Twitter Cards
            $table->string('twitter_card', 50)->default('summary_large_image');
            $table->string('twitter_title', 60)->nullable();
            $table->string('twitter_description', 160)->nullable();
            $table->string('twitter_image')->nullable();
            
            // Schema.org
            $table->string('schema_type', 50)->default('Article');
            $table->text('schema_json')->nullable();
            
            // Advanced SEO
            $table->enum('robots_index', ['index', 'noindex'])->default('index');
            $table->enum('robots_follow', ['follow', 'nofollow'])->default('follow');
            $table->string('breadcrumb_title', 100)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_seo');
    }
};
