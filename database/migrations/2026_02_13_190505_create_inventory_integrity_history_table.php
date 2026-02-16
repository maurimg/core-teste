<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_integrity_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('inventory_vehicles')
                  ->onDelete('cascade');

            $table->string('hash_anterior', 64)->nullable();
            $table->string('hash_novo', 64);

            $table->boolean('alteracao_detectada')->default(true);

            $table->timestamp('detected_at')->useCurrent();

            $table->timestamps();

            $table->index(['vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_integrity_history');
    }
};
