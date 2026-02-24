<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panchangs', function (Blueprint $table) {
            $table->text('tithi')->nullable()->change();
            $table->text('nakshatra')->nullable()->change();
            $table->text('karana')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('panchangs', function (Blueprint $table) {
            // Revert back to previous type (most likely string 255)
            $table->string('tithi', 255)->nullable()->change();
            $table->string('nakshatra', 255)->nullable()->change();
            $table->string('karana', 255)->nullable()->change();
        });
    }
};
