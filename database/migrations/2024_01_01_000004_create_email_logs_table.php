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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('type'); // welcome, follow_up, proposal, custom
            $table->string('subject');
            $table->text('body')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->string('status')->default('sent'); // sent, failed
            $table->text('error_message')->nullable();

            $table->index(['lead_id', 'type']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
