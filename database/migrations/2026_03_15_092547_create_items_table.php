<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('quantity')->default(0);
            $table->integer('rented')->default(0);
            $table->decimal('rental_fee', 10, 2)->default(0);
            $table->string('category');
            $table->string('image')->nullable();
            $table->json('sizes');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
