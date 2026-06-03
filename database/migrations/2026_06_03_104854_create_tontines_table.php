<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tontines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('members_count');
            $table->decimal('contribution', 14, 2);
            $table->enum('frequency', ['weekly', 'monthly'])->default('monthly');
            $table->unsignedSmallInteger('current_turn')->default(1);
            $table->date('start_date')->nullable();
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tontines');
    }
};
