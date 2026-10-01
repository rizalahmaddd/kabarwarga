<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->text('qris_payload')->nullable()->after('qris_path');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('unique_code')->default(0)->after('unit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('qris_payload');
        });
    }
};
