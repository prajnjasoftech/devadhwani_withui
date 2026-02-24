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
            // ❌ Drop old generated column
            $table->dropColumn('total_amount');
        });

        Schema::table('temple_pooja_bookings', function (Blueprint $table) {
            // ✅ Recreate with NEW formula
            $table->decimal('total_amount', 10, 2)
                ->storedAs('pooja_amount_total_received + pooja_amount_remaining');
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
            $table->dropColumn('total_amount');
        });

        Schema::table('temple_pooja_bookings', function (Blueprint $table) {
            // rollback to old formula if needed
            $table->decimal('total_amount', 10, 2)
                ->storedAs('pooja_amount + service_charge');
        });
    }
};
