<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE claims
            MODIFY transfer_status
            ENUM('pending', 'transferred', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Convert rejected claims before removing the enum value.
        DB::table('claims')
            ->where('transfer_status', 'rejected')
            ->update(['transfer_status' => 'pending']);

        DB::statement("
            ALTER TABLE claims
            MODIFY transfer_status
            ENUM('pending', 'transferred')
            NOT NULL DEFAULT 'pending'
        ");
    }
};