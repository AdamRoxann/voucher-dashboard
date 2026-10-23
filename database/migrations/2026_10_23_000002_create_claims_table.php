<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->date('claim_date')->index();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->string('pic_name');
            $table->string('pic_phone', 40);
            $table->decimal('total_claimed', 15, 2);
            $table->string('mandiri_account_number', 50);
            $table->string('account_holder_name');
            $table->date('transaction_date')->index();
            $table->string('proof_image_path');
            $table->enum('transfer_status', ['pending', 'transferred'])->default('pending')->index();
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('claims'); }
};
