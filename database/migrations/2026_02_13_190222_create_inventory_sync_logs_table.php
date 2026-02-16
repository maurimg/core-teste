<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_sync_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');

            $table->enum('status', ['running', 'completed', 'failed'])
                  ->default('running')
                  ->index();

            $table->integer('total_veiculos_encontrados')->default(0);
            $table->integer('total_novos')->default(0);
            $table->integer('total_atualizados')->default(0);
            $table->integer('total_inalterados')->default(0);

            $table->integer('erros_detectados')->default(0);

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_sync_logs');
    }
};
