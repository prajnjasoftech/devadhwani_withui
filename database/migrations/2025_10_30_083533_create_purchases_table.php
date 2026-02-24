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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->comment('Reference to main temples table');
            $table->unsignedBigInteger('item_id')->comment('Reference to items table');
            $table->unsignedBigInteger('supplier_id')->comment('Reference to suppliers table');
            $table->decimal('quantity', 10, 2)->default(0.00);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->date('received_date');
            $table->enum('mode', ['purchase', 'donation'])->default('purchase');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
