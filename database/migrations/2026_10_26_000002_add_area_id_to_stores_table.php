<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Nullable to keep existing stores and claim history intact until an admin assigns an area.
            $table->foreignId('area_id')
                ->nullable()
                ->after('id')
                ->constrained('areas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
        });
    }
};
