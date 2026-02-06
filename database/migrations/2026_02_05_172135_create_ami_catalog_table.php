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
        Schema::create('ami_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('region'); // us-east-1, us-west-2, etc
            $table->string('ami_id'); // ami-xxxxx
            $table->string('os_name'); // Ubuntu 22.04 LTS
            $table->string('os_version'); // 22.04
            $table->string('architecture'); // x86_64 ou arm64
            $table->string('root_device_type')->default('ebs'); // ebs ou instance-store
            $table->string('virtualization_type')->default('hvm');
            $table->boolean('is_active')->default(true);
            $table->timestamp('ami_created_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['region', 'os_version', 'architecture']);
            $table->index(['region', 'architecture', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ami_catalog');
    }
};
