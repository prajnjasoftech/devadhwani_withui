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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id');
            $table->string('event_name');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes(); // 👈 adds deleted_at column
            // Foreign key constraint
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            // Deletes devotees when temple is deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
