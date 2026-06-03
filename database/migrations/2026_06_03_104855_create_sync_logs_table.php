<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('table_name', 64);
            $table->string('record_uuid')->nullable();
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
