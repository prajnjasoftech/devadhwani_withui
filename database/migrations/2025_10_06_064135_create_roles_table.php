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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->nullable()->index(); // 🔹 Linked to temples
            $table->string('role_name')->nullable(); // optional: e.g. Admin / Staff
            $table->json('role')->nullable();        // JSON permissions
            $table->timestamps();
            $table->softDeletes();

            // optional: add foreign key
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
        Schema::dropIfExists('roles');
    }
};
