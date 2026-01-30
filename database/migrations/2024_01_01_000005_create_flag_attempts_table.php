<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flag_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_task_id')->constrained()->onDelete('cascade');
            $table->enum('flag_type', ['flag1', 'flag2']);
            $table->string('attempt');
            $table->boolean('is_correct')->default(false);
            $table->ipAddress('ip_address');
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
            $table->index(['team_task_id', 'is_correct']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flag_attempts');
    }
};