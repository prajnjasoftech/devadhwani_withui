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
            $table->dateTime('booking_end_date')->nullable()->after('booking_date');
            $table->double('pooja_amount_receipt', 10, 2)->default(0)->after('pooja_amount');
            $table->double('pooja_amount_remaining', 10, 2)->default(0)->after('pooja_amount_receipt');
            $table->double('pooja_amount_total_received', 10, 2)->default(0)->after('pooja_amount_remaining');
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
            $table->dropColumn([
                'booking_end_date',
                'pooja_amount_receipt',
                'pooja_amount_remaining',
                'pooja_amount_total_received',
            ]);
        });
    }
};
