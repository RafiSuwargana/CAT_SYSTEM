<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimingColumnsToTestTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add timing columns to test_sessions table
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('test_completed');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->integer('total_duration_seconds')->default(0)->after('completed_at');
        });
        
        // Add timing columns to test_responses table
        Schema::table('test_responses', function (Blueprint $table) {
            $table->float('expected_fisher_information')->nullable()->after('information');
            $table->timestamp('response_time')->nullable()->after('expected_fisher_information');
            $table->integer('response_duration_seconds')->default(0)->after('response_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'completed_at', 'total_duration_seconds']);
        });
        
        Schema::table('test_responses', function (Blueprint $table) {
            $table->dropColumn(['expected_fisher_information', 'response_time', 'response_duration_seconds']);
        });
    }
}
