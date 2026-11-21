<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('recurrence_source_id')->nullable()->unique()->constrained('tasks')->nullOnDelete();
            $table->timestampTz('reminder_delivered_at')->nullable();
            $table->index(['status', 'reminder_delivered_at', 'reminder_at'], 'tasks_pending_reminder_index');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurrence_source_id');
            $table->dropIndex('tasks_pending_reminder_index');
            $table->dropColumn('reminder_delivered_at');
        });
    }
};
