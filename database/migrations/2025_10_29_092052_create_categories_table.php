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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->comment('Reference to temple table');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft delete

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
        Schema::dropIfExists('categories');
    }
};
