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
        Schema::table('panchangs', function (Blueprint $table) {
            Schema::table('panchangs', function (Blueprint $table) {
                $table->string('day_name')->nullable()->after('day');
                $table->longText('day_raw')->nullable()->after('day_name');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('panchangs', function (Blueprint $table) {
            $table->dropColumn(['day_name', 'day_raw']);
        });
    }
};
