<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('payment_details_source_enum', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE payment_details 
            MODIFY source 
            ENUM('pooja','donation','salary','purchase') 
            NOT NULL DEFAULT 'pooja'
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
        Schema::table('payment_details_source_enum', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE payment_details 
            MODIFY source 
            ENUM('pooja','donation','salary') 
            NOT NULL DEFAULT 'pooja'
        ");
        });
    }
};
