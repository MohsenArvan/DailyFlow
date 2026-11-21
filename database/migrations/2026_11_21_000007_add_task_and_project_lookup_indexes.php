<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['user_id', 'project_id', 'status']);
            $table->index(['user_id', 'status', 'completed_at']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['user_id', 'is_favorite', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'project_id', 'status']);
            $table->dropIndex(['user_id', 'status', 'completed_at']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_favorite', 'order']);
        });
    }
};
