<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address', 500);
            $table->string('apartment')->nullable();
            $table->string('city');
            $table->string('postal_code', 20);
            $table->string('phone', 20);
            $table->boolean('default')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
