<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('temple_id');
            $table->unsignedBigInteger('source_id')->nullable();   // ID of pooja/donation/salary item
            $table->unsignedBigInteger('member_id')->nullable();   // who processed or who paid
            $table->unsignedBigInteger('booking_id')->nullable();  // for pooja booking payments

            $table->dateTime('payment_date')->nullable();

            $table->double('payment', 10, 2)->default(0);

            // source: pooja | donation | salary
            $table->enum('source', ['pooja', 'donation', 'salary'])->default('pooja');

            // type: credit (incoming), debit (outgoing)
            $table->enum('type', ['credit', 'debit'])->default('credit');

            // Payment method
            $table->enum('payment_mode', ['cash', 'online', 'upi', 'card'])->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('booking_id')->references('id')->on('temple_pooja_bookings')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
};
