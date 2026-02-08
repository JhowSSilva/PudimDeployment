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
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Commentable (what is being commented on)
            $table->string('commentable_type'); // Server, Site, Deployment, Alert, etc
            $table->unsignedBigInteger('commentable_id');
            $table->index(['commentable_type', 'commentable_id']);
            
            // Comment content
            $table->text('body');
            $table->json('mentions')->nullable(); // Array of mentioned user IDs
            
            // Thread support (for replies)
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            
            // Metadata
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['team_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
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
