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
        Schema::create('usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->comment('Reference to temples table');
            $table->unsignedBigInteger('item_id')->comment('Reference to items table');
            $table->unsignedBigInteger('used_by')->nullable()->comment('Reference to members/users table');
            $table->decimal('quantity', 10, 2);
            $table->string('used_for', 150);
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('used_by')->references('id')->on('members')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usages');
    }
};
