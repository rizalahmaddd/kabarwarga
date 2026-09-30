<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->string('head_name', 100);
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('dues_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('amount');
            $table->string('frequency', 10);
            $table->date('starts_on')->nullable();
            $table->date('due_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dues_type_id')->constrained()->cascadeOnDelete();
            // '' instead of NULL for one-time dues, so the unique index still blocks a double payment.
            $table->string('period', 7)->default('');
            $table->unsignedInteger('amount');
            $table->date('paid_on');
            $table->string('method', 20)->default('tunai');
            $table->string('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['household_id', 'dues_type_id', 'period']);
            $table->index('paid_on');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('spent_on');
            $table->string('description');
            $table->unsignedInteger('amount');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('spent_on');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 20);
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->dateTime('event_starts_at')->nullable();
            $table->string('event_location')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['published_at', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('dues_types');
        Schema::dropIfExists('households');
        Schema::dropIfExists('settings');
    }
};
