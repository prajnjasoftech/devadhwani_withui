<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('panchangs', function (Blueprint $table) {
            $table->id();
            $table->string('datetime');
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->string('timezone');

            // Panchang details
            $table->string('day')->nullable();
            $table->string('tithi')->nullable();
            $table->string('nakshatra')->nullable();
            $table->string('yoga')->nullable();
            $table->string('karana')->nullable();
            $table->string('sunrise')->nullable();
            $table->string('sunset')->nullable();

            $table->json('raw_data')->nullable(); // full JSON response for reference

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('panchangs');
    }
};
