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
        Schema::create('blog_post_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->date('date');
            
            // Traffic
            $table->integer('views')->default(0);
            $table->integer('unique_views')->default(0);
            
            // Engagement
            $table->integer('avg_time_on_page')->default(0); // seconds
            $table->decimal('bounce_rate', 5, 2)->nullable();
            $table->integer('scroll_depth')->default(0); // percentage
            
            // Social
            $table->integer('shares')->default(0);
            $table->integer('likes')->default(0);
            
            // Sources
            $table->integer('organic_views')->default(0);
            $table->integer('social_views')->default(0);
            $table->integer('direct_views')->default(0);
            $table->integer('referral_views')->default(0);
            
            $table->timestamps();
            
            $table->unique(['post_id', 'date']);
            $table->index(['post_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_analytics');
    }
};
