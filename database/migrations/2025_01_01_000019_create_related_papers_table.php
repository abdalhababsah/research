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
        Schema::create('related_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_research_paper_id')->constrained('research_papers')->cascadeOnDelete();
            $table->float('relevance_score')->nullable();
            $table->timestamps();

            $table->unique(['research_paper_id', 'related_research_paper_id'], 'unique_related_papers');
            $table->index('research_paper_id');
            $table->index('related_research_paper_id');
            $table->index('relevance_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('related_papers');
    }
};
