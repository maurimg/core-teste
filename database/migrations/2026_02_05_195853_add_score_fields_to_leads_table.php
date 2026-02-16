<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('score_current')->default(0)->after('id');
            $table->string('temperature')->default('cold')->after('score_current');
            $table->timestamp('last_interaction_at')->nullable()->after('temperature');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'score_current',
                'temperature',
                'last_interaction_at'
            ]);
        });
    }
};
