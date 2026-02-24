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
        Schema::create('devotees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id');
            $table->string('devotee_name', 100);
            $table->string('devotee_phone', 20)->nullable();
            $table->string('nakshatra', 100)->nullable();
            $table->text('address')->nullable();

            // Optional fields for your timezone structure
            $table->dateTime('device_created_at')->nullable()->comment('Local device time');
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at
            // Foreign key constraint
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devotees');
    }
};
