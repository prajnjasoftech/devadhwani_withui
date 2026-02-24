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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->comment('Reference to main temple table');
            $table->string('name', 150);
            $table->string('contact_number', 15);
            $table->text('address')->nullable();
            $table->enum('type', ['vendor', 'donor'])->default('vendor');
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('suppliers');
    }
};
