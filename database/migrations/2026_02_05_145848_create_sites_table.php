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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('domain');
            $table->string('document_root')->default('/public');
            $table->string('php_version')->default('8.3');
            $table->string('git_repository')->nullable();
            $table->string('git_branch')->default('main');
            $table->text('git_token')->nullable(); // Encrypted
            $table->text('deploy_script')->nullable();
            $table->text('env_variables')->nullable(); // JSON encrypted
            $table->string('nginx_config_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['active', 'inactive', 'deploying', 'error'])->default('inactive');
            $table->timestamps();
            
            $table->index(['server_id', 'status']);
            $table->unique(['server_id', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
