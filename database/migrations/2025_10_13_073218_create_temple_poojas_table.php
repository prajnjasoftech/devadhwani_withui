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
        Schema::create('temple_poojas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temple_id')->comment('Reference to temple table');
            //  Member Info
            $table->unsignedBigInteger('member_id')->nullable()->comment('If registered user');
            $table->string('pooja_name', 150);
            $table->enum('period', ['once', 'daily', 'monthly', 'yearly'])->default('once');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->text('details')->nullable();
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temple_poojas');
    }
};
