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
        Schema::table('inventory_vehicles', function (Blueprint $table) {
            $table->text('descricao_raw')->nullable()->change();
            $table->string('hash_integridade')->nullable()->change();
            $table->json('raw_payload')->nullable()->change();
            $table->timestamp('last_sync_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_vehicles', function (Blueprint $table) {
            $table->text('descricao_raw')->nullable(false)->change();
            $table->string('hash_integridade')->nullable(false)->change();
            $table->json('raw_payload')->nullable(false)->change();
            $table->timestamp('last_sync_at')->nullable(false)->change();
        });
    }
};
