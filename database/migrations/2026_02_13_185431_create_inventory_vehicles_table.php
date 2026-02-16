<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_vehicles', function (Blueprint $table) {
            $table->id();

            // Relacionamento com tenant
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');

            // Identificação externa
            $table->string('original_id')->nullable()->index();
            $table->string('url_original')->nullable();

            // Dados estruturados
            $table->string('marca')->nullable()->index();
            $table->string('modelo')->nullable()->index();
            $table->string('versao')->nullable();
            $table->string('ano')->nullable()->index();
            $table->decimal('preco', 15, 2)->nullable()->index();
            $table->integer('km')->nullable();
            $table->string('combustivel')->nullable();
            $table->string('cambio')->nullable();

            // Conteúdo bruto imutável
            $table->longText('descricao_raw');

            // Hash de integridade (SHA256 do conteúdo original)
            $table->string('hash_integridade', 64)->index();

            // Payload bruto opcional para prova jurídica
            $table->json('raw_payload')->nullable();

            // Controle de sincronização
            $table->timestamp('last_sync_at')->nullable();

            $table->timestamps();

            // Índice composto para evitar duplicidade por tenant
            $table->unique(['tenant_id', 'original_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_vehicles');
    }
};
