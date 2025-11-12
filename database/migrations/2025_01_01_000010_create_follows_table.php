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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->enum('following_type', ['researcher', 'tag']);
            $table->foreignId('following_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            // Unique constraints for each type
            $table->unique(['follower_id', 'following_id'], 'unique_user_follow');
            $table->unique(['follower_id', 'tag_id'], 'unique_tag_follow');

            $table->index('follower_id');
            $table->index('following_id');
            $table->index('tag_id');
            $table->index('following_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
