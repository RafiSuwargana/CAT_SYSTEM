<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTimingColumnsNames extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename column to be more accurate
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->renameColumn('total_duration_seconds', 'total_duration_milliseconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->renameColumn('total_duration_milliseconds', 'total_duration_seconds');
        });
    }
}
