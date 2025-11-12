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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('file_type'); // pdf, csv, xlsx, etc.
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->string('license_type')->nullable();
            $table->text('collection_methodology')->nullable();
            $table->text('citation_guidelines')->nullable();
            $table->integer('version_number')->default(1);
            $table->foreignId('parent_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->boolean('is_current_version')->default(true);
            $table->string('folder_path')->nullable();
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('public');
            $table->boolean('view_only')->default(false);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('research_paper_id');
            $table->index('parent_file_id');
            $table->index('is_current_version');
            $table->index('visibility');
            $table->index(['research_paper_id', 'is_current_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
