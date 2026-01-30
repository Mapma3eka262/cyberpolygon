<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('flag1_found')->default(false);
            $table->boolean('flag2_found')->default(false);
            $table->integer('wrong_attempts')->default(0);
            $table->integer('score')->default(0);
            $table->timestamps();
            $table->unique(['team_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_tasks');
    }
};