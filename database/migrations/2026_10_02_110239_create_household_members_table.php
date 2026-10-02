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
            $table->string('kk_number', 30)->nullable()->after('head_name');
            $table->string('kk_image_path')->nullable()->after('note');
        });

        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('nik', 20)->nullable();
            $table->string('name', 100);
            $table->string('gender', 10)->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('education', 50)->nullable();
            $table->string('job', 100)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('family_relation', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();

            $table->index(['household_id', 'family_relation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_members');

        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn(['kk_number', 'kk_image_path']);
        });
    }
};
