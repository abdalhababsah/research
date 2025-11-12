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
        Schema::create('research_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('abstract')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('doi')->nullable()->unique();
            $table->string('journal_name')->nullable();
            $table->date('publication_date')->nullable();
            $table->enum('status', ['draft', 'published', 'under_review'])->default('draft');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('visibility', ['public', 'unlisted', 'private', 'registered_only'])->default('public');
            $table->dateTime('scheduled_publication_date')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('author_id');
            $table->index('status');
            $table->index('visibility');
            $table->index('category_id');
            $table->index('is_featured');
            $table->index('created_at');
            $table->index('publication_date');
            $table->index(['status', 'visibility', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_papers');
    }
};
