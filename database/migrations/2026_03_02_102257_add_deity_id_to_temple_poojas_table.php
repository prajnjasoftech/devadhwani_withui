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
        Schema::table('temple_poojas', function (Blueprint $table) {
            $table->unsignedBigInteger('deity_id')->nullable()->after('temple_id');
            $table->foreign('deity_id')->references('id')->on('temple_deities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temple_poojas', function (Blueprint $table) {
            $table->dropForeign(['deity_id']);
            $table->dropColumn('deity_id');
        });
    }
};
