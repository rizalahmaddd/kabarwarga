<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 60);
            $table->string('account_number', 60)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('qris_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dues_type_id')->constrained()->cascadeOnDelete();
            $table->json('periods');
            $table->unsignedInteger('unit_amount');
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payer_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('note')->nullable();
            $table->string('proof_path');
            $table->string('status', 10)->default('menunggu');
            $table->string('reject_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['household_id', 'dues_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
        Schema::dropIfExists('bank_accounts');
    }
};
