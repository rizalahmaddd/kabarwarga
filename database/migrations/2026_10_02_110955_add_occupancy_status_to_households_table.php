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
        Schema::table('households', function (Blueprint $table) {
            $table->string('occupancy_status', 20)->default('pemilik')->after('head_name');
        });

        Schema::table('household_members', function (Blueprint $table) {
            $table->string('occupancy_status', 20)->nullable()->after('family_relation');
        });
    }

    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            $table->dropColumn('occupancy_status');
        });

        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('occupancy_status');
        });
    }
};
