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
    Schema::create('shops', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider', 20)->index();      // 'shopify' | 'woo'
    $table->string('domain');
    $table->text('credentials_encrypted');
    $table->string('scopes')->nullable();
    $table->string('status', 20)->default('connected');
    $table->json('meta')->nullable();
    $table->timestamps();
    $table->unique(['user_id','provider','domain']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
