<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('invite_code')->unique()->nullable(); // Для приглашения участников
            $table->string('target_ip')->nullable();
            $table->integer('task_timer')->nullable()->comment('В минутах');
            $table->timestamp('task_started_at')->nullable();
            $table->timestamp('task_ends_at')->nullable();
            $table->integer('flags_found')->default(0);
            $table->integer('wrong_attempts')->default(0);
            $table->integer('score')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};