<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('material', 32);
            $table->string('color', 32);
            $table->unsignedSmallInteger('width_cm');
            // price per metre in minor units (kopecks)
            $table->unsignedInteger('price');
            // stock in metres
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // covers the main catalog query: active products of a category filtered/sorted by price
            $table->index(['is_active', 'category_id', 'price']);
            $table->index(['is_active', 'material']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
