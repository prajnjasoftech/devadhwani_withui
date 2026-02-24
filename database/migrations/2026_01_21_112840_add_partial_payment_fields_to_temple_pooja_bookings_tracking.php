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
            $table->decimal('paid_amount', 10, 2)
                ->default(0)
                ->after('pooja_date');

            $table->decimal('due_amount', 10, 2)
                ->default(0)
                ->after('paid_amount');

            // ✅ Update existing enum values using raw SQL
            DB::statement("
            ALTER TABLE temple_pooja_bookings_tracking 
            MODIFY payment_status 
            ENUM('pending','partial','done') 
            DEFAULT 'pending'
        ");
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
            $table->dropColumn(['paid_amount', 'due_amount']);

            DB::statement("
            ALTER TABLE temple_pooja_bookings_tracking 
            MODIFY payment_status 
            ENUM('pending','done') 
            DEFAULT 'pending'
        ");
        });
    }
};
