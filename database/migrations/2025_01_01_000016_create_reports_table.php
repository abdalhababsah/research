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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('reportable'); // polymorphic: research_papers, comments, users, etc.
            $table->enum('reason', ['spam', 'inappropriate', 'copyright', 'harassment', 'other']);
            $table->text('description');
            $table->enum('status', ['pending', 'reviewed', 'dismissed', 'action_taken'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('reporter_id');
            $table->index(['reportable_id', 'reportable_type']);
            $table->index('status');
            $table->index('reason');
            $table->index('reviewed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
