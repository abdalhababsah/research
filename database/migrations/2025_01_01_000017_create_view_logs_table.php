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
        Schema::create('view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address'); // Hashed for privacy
            $table->text('user_agent');
            $table->string('referrer')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->string('session_id')->index();

            $table->index('research_paper_id');
            $table->index('user_id');
            $table->index('viewed_at');
            $table->index(['research_paper_id', 'viewed_at']);
            $table->index('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_logs');
    }
};
