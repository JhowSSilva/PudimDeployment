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
        Schema::create('server_software_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // composer, git, docker
            $table->string('name'); // Composer
            $table->string('category'); // essential, runtime, database, monitoring
            $table->text('description');
            $table->boolean('is_default')->default(false); // instalado por padrão
            $table->json('dependencies')->nullable(); // ["php", "curl"]
            $table->json('install_commands'); // comandos de instalação
            $table->json('verify_commands')->nullable(); // comandos de verificação
            $table->integer('install_order')->default(100); // ordem de instalação
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_software_catalog');
    }
};
