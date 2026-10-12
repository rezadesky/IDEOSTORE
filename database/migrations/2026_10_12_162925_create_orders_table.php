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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // e.g. '3656' or IDEO-xxxx
            $table->string('ref')->nullable(); // e.g. 'WEBWRG...'
            $table->string('product_name');
            $table->string('variant')->nullable();
            $table->integer('qty')->default(1);
            $table->integer('amount');
            $table->string('customer_name')->default('Pembeli');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('payment_method')->default('QRIS');
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            $table->text('accounts')->nullable(); // JSON string
            $table->text('note')->nullable();
            $table->string('qr_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
