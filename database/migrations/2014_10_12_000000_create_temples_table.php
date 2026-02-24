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
        Schema::create('temples', function (Blueprint $table) {
            $table->id();
            $table->string('temple_name')->nullable();
            $table->string('temple_address')->nullable();
            $table->string('temple_logo')->nullable();    // File path
            $table->string('phone')->unique()->nullable();
            $table->string('database_name')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // 👈 adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
