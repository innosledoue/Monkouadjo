<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('limit_amount', 14, 2);
            $table->unsignedTinyInteger('alert_threshold')->default(80);
            $table->enum('period', ['weekly', 'monthly'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
