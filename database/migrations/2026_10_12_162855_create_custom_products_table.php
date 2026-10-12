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
        Schema::create('custom_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('variant')->nullable();
            $table->string('category')->default('Aplikasi Premium');
            $table->integer('price');
            $table->integer('normal_price')->nullable();
            $table->integer('stock')->nullable();
            $table->string('type')->default('manual'); // 'manual' | 'auto'
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('active'); // 'active' | 'inactive'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_products');
    }
};
