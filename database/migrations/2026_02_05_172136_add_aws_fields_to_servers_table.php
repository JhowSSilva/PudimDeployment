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
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignId('aws_credential_id')->nullable()->constrained('aws_credentials')->onDelete('set null');
            $table->string('instance_id')->nullable()->unique(); // EC2 Instance ID (i-xxxxx)
            $table->string('instance_type')->nullable(); // t4g.micro, t3.small, etc
            $table->string('architecture')->nullable(); // x86_64 ou arm64
            $table->string('region')->nullable(); // us-east-1, us-west-2, etc
            $table->string('availability_zone')->nullable(); // us-east-1a
            $table->string('ami_id')->nullable(); // AMI Ubuntu usada
            $table->string('key_pair_name')->nullable(); // Nome da chave SSH
            $table->text('private_key')->nullable(); // Chave SSH privada (criptografada)
            $table->string('security_group_id')->nullable();
            $table->integer('disk_size')->default(20); // GB
            $table->string('public_ip')->nullable();
            $table->string('private_ip')->nullable();
            $table->decimal('monthly_cost', 10, 2)->nullable(); // Custo estimado/mês
            $table->json('stack_config')->nullable(); // PHP, webserver, database, etc
            $table->string('provision_status')->default('pending'); // pending, provisioning, active, failed
            $table->text('provision_log')->nullable();
            $table->timestamp('provisioned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['aws_credential_id']);
            $table->dropColumn([
                'aws_credential_id', 'instance_id', 'instance_type', 'architecture',
                'region', 'availability_zone', 'ami_id', 'key_pair_name', 'private_key',
                'security_group_id', 'disk_size', 'public_ip', 'private_ip',
                'monthly_cost', 'stack_config', 'provision_status', 'provision_log', 'provisioned_at'
            ]);
        });
    }
};
