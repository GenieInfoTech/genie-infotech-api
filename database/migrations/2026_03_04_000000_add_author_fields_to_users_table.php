<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('avatar')->nullable()->after('slug');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('job_title')->nullable()->after('bio');
            $table->string('website_url')->nullable()->after('job_title');
            $table->string('twitter_url')->nullable()->after('website_url');
            $table->string('linkedin_url')->nullable()->after('twitter_url');
            $table->string('github_url')->nullable()->after('linkedin_url');
        });

        // Populate slugs for existing users
        $users = DB::table('users')->whereNull('slug')->get();
        foreach ($users as $user) {
            $baseSlug = Str::slug($user->name);
            $slug = $baseSlug;
            $counter = 1;

            // Handle duplicate slugs
            while (DB::table('users')->where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
        }

        // Add unique index after all slugs are populated
        Schema::table('users', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'avatar',
                'bio',
                'job_title',
                'website_url',
                'twitter_url',
                'linkedin_url',
                'github_url',
            ]);
        });
    }
};
