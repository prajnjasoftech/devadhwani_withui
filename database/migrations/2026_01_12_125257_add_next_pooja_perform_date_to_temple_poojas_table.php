<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temple_poojas', function (Blueprint $table) {
            $table->date('next_pooja_perform_date')
                ->nullable()
                ->after('details');
        });
    }

    public function down(): void
    {
        Schema::table('temple_poojas', function (Blueprint $table) {
            $table->dropColumn('next_pooja_perform_date');
        });
    }
};
