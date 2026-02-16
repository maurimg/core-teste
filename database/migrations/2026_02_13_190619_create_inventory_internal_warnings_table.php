<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_internal_warnings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('inventory_vehicles')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');

            $table->string('tipo')->index(); 
            // Ex: campo_ausente, estrutura_invalida, erro_parser, inconsistencia_dados

            $table->text('descricao');

            $table->timestamp('detected_at')->useCurrent();

            $table->timestamps();

            $table->index(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_internal_warnings');
    }
};
