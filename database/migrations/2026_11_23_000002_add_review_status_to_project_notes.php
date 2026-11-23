<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
            $table->enum('review_status', ['unreviewed', 'in_review', 'reviewed'])->default('unreviewed');
        });
    }

    public function down(): void
    {
        Schema::table('project_notes', function (Blueprint $table) {
            $table->dropColumn('review_status');
        });
    }
};
