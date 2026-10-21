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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->string('purpose');
            $table->json('person');
            $table->string('phone', 30)->nullable();
            $table->string('status', 20)->default('menunggu')->index();
            $table->unsignedInteger('sequence')->nullable();
            $table->string('number', 80)->nullable();
            $table->date('issued_on')->nullable();
            $table->string('reject_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
