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
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable and remove unique constraint
            $table->string('email')->nullable()->change();
            
            // Add phone field (nullable but unique)
            $table->string('phone')->nullable()->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove phone field
            $table->dropColumn('phone');
            
            // Restore email to non-nullable with unique constraint
            $table->string('email')->nullable(false)->change();
        });
    }
};
