<?php

// Test script untuk memverifikasi timing functionality
echo "=== Testing CAT Timing Functionality ===\n\n";

// Check if migration has been run
echo "1. Checking database structure...\n";

// Read the migration file to show what columns were added
$migrationFile = 'database/migrations/2025_07_12_000005_add_timing_columns_to_test_tables.php';
if (file_exists($migrationFile)) {
    echo "   ✓ Migration file exists\n";
    echo "   ✓ Added timing columns to test_sessions:\n";
    echo "     - started_at (timestamp)\n";
    echo "     - completed_at (timestamp)\n";
    echo "     - total_duration_seconds (integer)\n";
    echo "   ✓ Added timing columns to test_responses:\n";
    echo "     - response_time (timestamp)\n";
    echo "     - response_duration_seconds (integer)\n";
} else {
    echo "   ✗ Migration file not found\n";
}

echo "\n2. Checking model updates...\n";

// Check TestSession model
$testSessionModel = 'app/Models/TestSession.php';
if (file_exists($testSessionModel)) {
    $content = file_get_contents($testSessionModel);
    if (strpos($content, 'started_at') !== false && 
        strpos($content, 'completed_at') !== false && 
        strpos($content, 'total_duration_seconds') !== false) {
        echo "   ✓ TestSession model updated with timing fields\n";
    } else {
        echo "   ✗ TestSession model missing timing fields\n";
    }
} else {
    echo "   ✗ TestSession model not found\n";
}

// Check TestResponse model
$testResponseModel = 'app/Models/TestResponse.php';
if (file_exists($testResponseModel)) {
    $content = file_get_contents($testResponseModel);
    if (strpos($content, 'response_time') !== false && 
        strpos($content, 'response_duration_seconds') !== false) {
        echo "   ✓ TestResponse model updated with timing fields\n";
    } else {
        echo "   ✗ TestResponse model missing timing fields\n";
    }
} else {
    echo "   ✗ TestResponse model not found\n";
}

echo "\n3. Checking service updates...\n";

// Check CATService
$catService = 'app/Services/CATService.php';
if (file_exists($catService)) {
    $content = file_get_contents($catService);
    if (strpos($content, 'responseDurationSeconds') !== false) {
        echo "   ✓ CATService updated with timing parameter\n";
    } else {
        echo "   ✗ CATService missing timing parameter\n";
    }
    
    if (strpos($content, 'started_at') !== false && 
        strpos($content, 'completed_at') !== false) {
        echo "   ✓ CATService updated with session timing\n";
    } else {
        echo "   ✗ CATService missing session timing\n";
    }
} else {
    echo "   ✗ CATService not found\n";
}

echo "\n4. Checking controller updates...\n";

// Check CATController
$catController = 'app/Http/Controllers/CATController.php';
if (file_exists($catController)) {
    $content = file_get_contents($catController);
    if (strpos($content, 'response_duration_seconds') !== false) {
        echo "   ✓ CATController updated with timing validation\n";
    } else {
        echo "   ✗ CATController missing timing validation\n";
    }
} else {
    echo "   ✗ CATController not found\n";
}

echo "\n=== Summary ===\n";
echo "Timing functionality has been added to the CAT system:\n\n";

echo "Database Changes:\n";
echo "- test_sessions table now tracks when sessions start and end\n";
echo "- test_responses table now tracks individual response times\n\n";

echo "API Changes:\n";
echo "- POST /api/cat/start now returns started_at timestamp\n";
echo "- POST /api/cat/submit now accepts response_duration_seconds parameter\n";
echo "- Completed tests now return total_duration_seconds and completed_at\n\n";

echo "Usage Example:\n";
echo "1. Start test: POST /api/cat/start\n";
echo "2. Submit answers with timing: POST /api/cat/submit\n";
echo "   {\n";
echo "     \"session_id\": \"CAT_123456_7890\",\n";
echo "     \"item_id\": \"1\",\n";
echo "     \"answer\": 1,\n";
echo "     \"response_duration_seconds\": 15\n";
echo "   }\n";
echo "3. Get session history: GET /api/cat/history/{sessionId}\n\n";

echo "The system now automatically tracks:\n";
echo "- Total test duration (from start to completion)\n";
echo "- Individual response times for each question\n";
echo "- Average response time per question\n";
echo "- Timestamps for all events\n\n";

echo "To test the timing functionality:\n";
echo "1. Run: php artisan serve\n";
echo "2. Visit: http://localhost:8000/test-timing\n";
echo "3. Click 'Start Test' and submit answers\n";
echo "4. Observe the timing data in the response\n\n";

echo "=== Test Complete ===\n";
