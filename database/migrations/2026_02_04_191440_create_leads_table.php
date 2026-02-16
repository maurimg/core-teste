<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('source')->nullable(); // instagram, site, portal etc
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('interest')->nullable();

            $table->json('conversation')->nullable();

            $table->integer('score')->default(0);

            $table->string('status')->default('new'); 
            // new, qualified, forwarded, closed

            $table->string('forwarded_to')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
