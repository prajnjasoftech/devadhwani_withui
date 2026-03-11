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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('temple_id');
            $table->string('receipt_number', 20)->unique();
            $table->unsignedBigInteger('devotee_id')->nullable()->comment('Primary contact/payer');
            $table->unsignedBigInteger('member_id')->nullable()->comment('Staff who created');

            $table->date('receipt_date');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Sum of all line items');
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0)->comment('total_amount - discount');
            $table->decimal('amount_paid', 10, 2)->default(0)->comment('Sum of all payments');
            $table->decimal('balance_due', 10, 2)->default(0)->comment('net_amount - amount_paid');

            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('devotee_id')->references('id')->on('devotees')->onDelete('set null');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');

            $table->index(['temple_id', 'receipt_date']);
            $table->index(['temple_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipts');
    }
};
