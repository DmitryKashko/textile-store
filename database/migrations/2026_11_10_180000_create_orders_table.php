<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('new');
            $table->unsignedInteger('total');
            $table->timestamps();

            // "my orders" list: filter by user, newest first
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            // snapshot, so the order doesn't change when the product is edited later
            $table->string('product_name');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->unsignedInteger('line_total');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
