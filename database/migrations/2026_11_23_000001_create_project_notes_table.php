<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('body');
            $table->timestampTz('pinned_at')->nullable();
            $table->timestampsTz();
        });

        DB::statement('CREATE INDEX project_notes_listing_index ON project_notes (project_id, (pinned_at IS NULL), updated_at DESC, id DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_notes');
    }
};
