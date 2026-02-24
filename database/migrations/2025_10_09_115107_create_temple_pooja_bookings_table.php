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
        Schema::create('temple_pooja_bookings', function (Blueprint $table) {
            $table->id();

            // Temple and Pooja Info
            $table->unsignedBigInteger('temple_id')->comment('Reference to temple table');
            $table->unsignedBigInteger('pooja_id')->comment('Reference to master pooja table');

            // Devotee / Member Info
            $table->unsignedBigInteger('member_id')->nullable()->comment('If registered user');
            $table->unsignedBigInteger('devotee_id')->nullable()->comment('If guest devotee');

            // Booking Info
            $table->string('booking_number', 50)->unique()->comment('Unique booking reference number');
            $table->date('booking_date')->comment('Date when pooja will be performed');
            $table->string('booking_time_slot', 50)->nullable()->comment('Morning / Evening / Custom slot');
            $table->enum('period', ['once', 'daily', 'monthly', 'yearly'])->default('once')->comment('Booking period type');

            // Amounts & Payment
            $table->decimal('pooja_amount', 10, 2)->default(0.00);
            $table->decimal('service_charge', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->storedAs('pooja_amount + service_charge');
            $table->enum('payment_status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_mode', ['cash', 'online', 'upi', 'card'])->nullable();
            $table->string('transaction_id', 100)->nullable();

            // Booking Status
            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('remarks')->nullable();

            // Device & Time Info
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            // Indexes & Foreign Keys
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            // $table->foreign('pooja_id')->references('id')->on('temple_poojas')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('devotee_id')->references('id')->on('devotees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temple_pooja_bookings');
    }
};
