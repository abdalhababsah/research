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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_comment_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('content');
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('research_paper_id');
            $table->index('user_id');
            $table->index('parent_comment_id');
            $table->index('is_flagged');
            $table->index('created_at');
            $table->index(['research_paper_id', 'parent_comment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
