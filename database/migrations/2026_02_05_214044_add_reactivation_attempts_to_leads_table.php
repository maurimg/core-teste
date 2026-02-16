<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('reactivation_attempts')
                ->default(0)
                ->after('last_interaction_at');

            $table->timestamp('last_reactivation_at')
                ->nullable()
                ->after('reactivation_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'reactivation_attempts',
                'last_reactivation_at'
            ]);
        });
    }
};
