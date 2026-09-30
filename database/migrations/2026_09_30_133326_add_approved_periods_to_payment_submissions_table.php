<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->json('approved_periods')->nullable()->after('periods');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_submission_id')->nullable()->after('recorded_by')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_submission_id');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropColumn('approved_periods');
        });
    }
};
