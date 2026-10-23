<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('claim_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->enum('voucher_type', ['A', 'B', 'C'])->index();
            $table->string('voucher_code', 100);
            $table->timestamps();
            $table->unique(['claim_id', 'voucher_type', 'voucher_code']);
        });
    }
    public function down(): void { Schema::dropIfExists('claim_vouchers'); }
};
