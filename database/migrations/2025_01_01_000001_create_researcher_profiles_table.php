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
        Schema::create('researcher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable(); // Dr., Prof., etc.
            $table->string('institution')->nullable();
            $table->string('department')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->json('research_interests')->nullable();
            $table->json('contact_information')->nullable(); // {phone, website, social_media}
            $table->string('slug')->unique();
            $table->json('education_history')->nullable(); // [{degree, institution, year}]
            $table->json('work_experience')->nullable(); // [{position, organization, years}]
            $table->unsignedBigInteger('total_views')->default(0);
            $table->unsignedBigInteger('total_downloads')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('researcher_profiles');
    }
};