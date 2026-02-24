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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id');
            $table->string('name');
            $table->string('phone')->unique()->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable();
            $table->integer('role_id')->nullable();
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
        Schema::dropIfExists('members');
    }
};
