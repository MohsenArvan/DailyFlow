<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#3b82f6');
            $table->string('icon', 50)->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->integer('order')->default(0);
            $table->string('view_mode', 20)->default('list');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'order']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
