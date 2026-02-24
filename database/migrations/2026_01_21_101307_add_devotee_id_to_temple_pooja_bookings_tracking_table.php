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
        Schema::table('temple_pooja_bookings_tracking', function (Blueprint $table) {
            $table->unsignedBigInteger('devotee_id')
                ->nullable()
                ->after('booking_id');

            $table->index(['booking_id', 'devotee_id', 'pooja_date'], 'booking_devotee_date_idx');

            $table->foreign('devotee_id')
                ->references('id')
                ->on('devotees')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temple_pooja_bookings_tracking', function (Blueprint $table) {
            $table->dropForeign(['devotee_id']);
            $table->dropIndex('booking_devotee_date_idx');
            $table->dropColumn('devotee_id');
        });
    }
};
