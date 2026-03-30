<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devotees', function (Blueprint $table) {
            // Add index on temple_id for filtering (most important)
            $table->index('temple_id', 'devotees_temple_id_index');

            // Add index on devotee_name for search
            $table->index('devotee_name', 'devotees_devotee_name_index');

            // Add index on devotee_phone for search
            $table->index('devotee_phone', 'devotees_devotee_phone_index');

            // Composite index for temple_id + soft deletes (common query pattern)
            $table->index(['temple_id', 'deleted_at'], 'devotees_temple_deleted_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devotees', function (Blueprint $table) {
            // Drop foreign key first so we can drop any index on temple_id
            $table->dropForeign(['temple_id']);
        });

        Schema::table('devotees', function (Blueprint $table) {
            $table->dropIndex('devotees_temple_deleted_index');
            $table->dropIndex('devotees_temple_id_index');
            $table->dropIndex('devotees_devotee_name_index');
            $table->dropIndex('devotees_devotee_phone_index');
        });

        Schema::table('devotees', function (Blueprint $table) {
            // Re-add the foreign key (this will auto-create necessary index)
            $table->foreign('temple_id')->references('id')->on('temples')->onDelete('cascade');
        });
    }
};
