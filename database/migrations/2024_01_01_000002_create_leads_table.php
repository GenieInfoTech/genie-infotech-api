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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Contact info
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();

            // Project details
            $table->string('project_type')->nullable();
            $table->string('budget')->nullable();
            $table->string('timeline')->nullable();
            $table->text('description')->nullable();

            // Service preferences
            $table->string('service_interest')->nullable();
            $table->string('team_package')->nullable();

            // Attribution
            $table->string('source')->default('website');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('landing_page')->nullable();

            // Status & management
            $table->string('status')->default('new');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            $table->string('assigned_to')->nullable();

            // Tracking
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('lost_reason')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index('status');
            $table->index('source');
            $table->index('priority');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
