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
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipeline_id');
            $table->string('name');
            $table->string('type'); // build, test, deploy, notify, custom
            $table->integer('order')->default(0);
            $table->json('commands')->nullable(); // array of shell commands
            $table->string('docker_image')->nullable(); // for containerized builds
            $table->json('artifacts')->nullable(); // files to preserve/pass to next stage
            $table->boolean('parallel')->default(false);
            $table->boolean('allow_failure')->default(false);
            $table->string('when')->default('always'); // always, on_success, on_failure, manual
            $table->integer('timeout_minutes')->default(15);
            $table->json('environment_variables')->nullable();
            $table->unsignedBigInteger('depends_on_stage_id')->nullable(); // dependency on another stage
            $table->timestamps();
            
            $table->foreign('pipeline_id')->references('id')->on('pipelines')->onDelete('cascade');
            $table->foreign('depends_on_stage_id')->references('id')->on('pipeline_stages')->onDelete('set null');
            $table->index(['pipeline_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
