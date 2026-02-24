<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('temple_id')->comment('Reference to main temples table');
            $table->unsignedBigInteger('category_id')->comment('Reference to categories table');
            $table->string('item_name', 150);
            $table->string('unit', 50);
            $table->decimal('min_quantity', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
