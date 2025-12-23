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
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Add new full_name column
            $table->string('full_name')->after('id');
            
            // Drop old columns
            $table->dropColumn(['first_name', 'last_name', 'city', 'postal_code', 'apartment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Restore old columns
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('city')->after('address');
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('apartment')->nullable()->after('address');
            
            // Drop new column
            $table->dropColumn('full_name');
        });
    }
};
