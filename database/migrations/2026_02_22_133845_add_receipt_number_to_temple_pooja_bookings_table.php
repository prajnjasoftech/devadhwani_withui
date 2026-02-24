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
            $table->string('receipt_number', 20)->nullable()->after('booking_number');
            $table->index('receipt_number');
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
            $table->dropIndex(['receipt_number']);
            $table->dropColumn('receipt_number');
        });
    }
};
