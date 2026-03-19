<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Customer
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Product (Item)
            $table->foreignId('product_id')->constrained('items')->onDelete('cascade');

            // Details
            $table->string('size');
            $table->dateTime('rent_time');

            // delivery or pickup
            $table->enum('delivery', ['delivery', 'pickup'])->default('pickup');

            // Payment
            $table->string('gcash_reference')->nullable();
            $table->decimal('price', 10, 2)->default(0);

            // Status: Pending / Confirmed / Cancelled
            $table->enum('status', ['Pending', 'Confirmed', 'Cancelled'])->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
