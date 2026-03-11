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
        Schema::create('receipt_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('receipt_id');
            $table->decimal('amount', 10, 2);
            $table->dateTime('payment_date');
            $table->enum('payment_mode', ['cash', 'online', 'upi', 'card'])->default('cash');
            $table->string('transaction_id', 100)->nullable();
            $table->unsignedBigInteger('member_id')->nullable()->comment('Staff who received');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');

            $table->index(['receipt_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipt_payments');
    }
};
