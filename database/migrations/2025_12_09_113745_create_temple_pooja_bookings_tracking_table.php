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
        Schema::create('temple_pooja_bookings_tracking', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id');
            $table->date('pooja_date');                 // each pooja date
            $table->enum('payment_status', ['pending', 'done'])->default('pending');
            $table->enum('booking_status', ['pending', 'completed', 'cancelled'])->default('pending');

            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('temple_pooja_bookings')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temple_pooja_bookings_tracking');
    }
};
