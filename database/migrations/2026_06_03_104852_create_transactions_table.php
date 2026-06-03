<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['expense', 'income'])->default('expense');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 8)->default('XOF');
            $table->dateTime('occurred_at');
            $table->enum('payment_method', ['cash', 'om', 'momo', 'wave', 'card', 'bank', 'other'])->default('cash');
            $table->string('source_account')->nullable();
            $table->string('beneficiary')->nullable();
            $table->text('note')->nullable();
            $table->enum('source', ['manual', 'voice', 'sms', 'import'])->default('manual');
            $table->boolean('is_synced')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index(['user_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
