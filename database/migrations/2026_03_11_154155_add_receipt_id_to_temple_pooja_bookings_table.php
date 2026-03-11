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
        Schema::table('temple_pooja_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('receipt_id')->nullable()->after('receipt_number');
            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('set null');
            $table->index('receipt_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temple_pooja_bookings', function (Blueprint $table) {
            $table->dropForeign(['receipt_id']);
            $table->dropIndex(['receipt_id']);
            $table->dropColumn('receipt_id');
        });
    }
};
