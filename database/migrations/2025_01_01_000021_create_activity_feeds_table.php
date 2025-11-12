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
        Schema::create('activity_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type'); // published_paper, commented, followed_user, etc.
            $table->morphs('activityable'); // polymorphic relationship
            $table->json('metadata')->nullable(); // Additional activity info
            $table->timestamps();

            $table->index('user_id');
            $table->index('activity_type');
            $table->index(['activityable_id', 'activityable_type']);
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_feeds');
    }
};
