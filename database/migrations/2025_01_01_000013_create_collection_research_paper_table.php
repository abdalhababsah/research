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
        Schema::create('collection_research_paper', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_paper_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['collection_id', 'research_paper_id']);
            $table->index('collection_id');
            $table->index('research_paper_id');
            $table->index(['collection_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_research_paper');
    }
};
