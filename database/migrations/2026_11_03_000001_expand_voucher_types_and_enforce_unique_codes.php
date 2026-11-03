<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refuse to add the global unique index until historical duplicates are resolved.
        $blankCodes = DB::table('claim_vouchers')
            ->whereRaw("TRIM(voucher_code) = ''")
            ->count();

        if ($blankCodes > 0) {
            throw new RuntimeException(
                "Cannot enforce voucher uniqueness: {$blankCodes} blank voucher code row(s) exist."
            );
        }

        $duplicates = DB::table('claim_vouchers')
            ->selectRaw('UPPER(TRIM(voucher_code)) AS normalized_code, COUNT(*) AS usage_count')
            ->groupByRaw('UPPER(TRIM(voucher_code))')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('normalized_code');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot enforce global voucher uniqueness. Resolve these duplicate codes first: '
                . $duplicates->implode(', ')
            );
        }

        // Normalize historical codes; new codes are normalized by ClaimController too.
        DB::statement('UPDATE claim_vouchers SET voucher_code = UPPER(TRIM(voucher_code))');

        DB::statement("
            ALTER TABLE claim_vouchers
            MODIFY voucher_type ENUM('A', 'B', 'C', 'D', 'E') NOT NULL
        ");

        // Recalculate historical claim totals using the new voucher price rules.
        DB::statement("
            UPDATE claims AS c
            LEFT JOIN (
                SELECT
                    claim_id,
                    SUM(CASE WHEN voucher_type = 'E' THEN 100000 ELSE 50000 END) AS calculated_total
                FROM claim_vouchers
                GROUP BY claim_id
            ) AS voucher_totals ON voucher_totals.claim_id = c.id
            SET c.total_claimed = COALESCE(voucher_totals.calculated_total, 0)
        ");

        Schema::table('claim_vouchers', function (Blueprint $table) {
            $table->dropUnique('claim_vouchers_claim_id_voucher_type_voucher_code_unique');
            $table->unique('voucher_code', 'claim_vouchers_voucher_code_unique');
        });
    }

    public function down(): void
    {
        $newTypesExist = DB::table('claim_vouchers')
            ->whereIn('voucher_type', ['D', 'E'])
            ->exists();

        if ($newTypesExist) {
            throw new RuntimeException(
                'Cannot roll back voucher types while Voucher D or E records exist.'
            );
        }

        Schema::table('claim_vouchers', function (Blueprint $table) {
            $table->dropUnique('claim_vouchers_voucher_code_unique');
            $table->unique(
                ['claim_id', 'voucher_type', 'voucher_code'],
                'claim_vouchers_claim_id_voucher_type_voucher_code_unique'
            );
        });

        DB::statement("
            ALTER TABLE claim_vouchers
            MODIFY voucher_type ENUM('A', 'B', 'C') NOT NULL
        ");
    }
};
