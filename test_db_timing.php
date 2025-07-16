<?php

// Test database untuk memverifikasi kolom timing
echo "=== Database Timing Test ===\n\n";

try {
    // Check if database file exists
    $dbFile = 'database/database.sqlite';
    if (!file_exists($dbFile)) {
        echo "Database file not found: $dbFile\n";
        exit(1);
    }
    
    // Connect to database
    $pdo = new PDO("sqlite:$dbFile");
    
    // Check test_sessions table structure
    echo "1. Checking test_sessions table structure...\n";
    $stmt = $pdo->query("PRAGMA table_info(test_sessions)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $timingColumns = ['started_at', 'completed_at', 'total_duration_seconds'];
    foreach ($timingColumns as $col) {
        $found = false;
        foreach ($columns as $column) {
            if ($column['name'] === $col) {
                echo "   ✓ Column '$col' exists\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "   ✗ Column '$col' missing\n";
        }
    }
    
    // Check test_responses table structure
    echo "\n2. Checking test_responses table structure...\n";
    $stmt = $pdo->query("PRAGMA table_info(test_responses)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $timingColumns = ['response_time', 'response_duration_seconds'];
    foreach ($timingColumns as $col) {
        $found = false;
        foreach ($columns as $column) {
            if ($column['name'] === $col) {
                echo "   ✓ Column '$col' exists\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "   ✗ Column '$col' missing\n";
        }
    }
    
    echo "\n3. Testing timing functionality...\n";
    
    // Insert test session with timing
    $sessionId = 'TEST_' . time();
    $startTime = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        INSERT INTO test_sessions 
        (session_id, theta, standard_error, test_completed, started_at, total_duration_seconds)
        VALUES (?, 0.0, 1.0, 0, ?, 0)
    ");
    $stmt->execute([$sessionId, $startTime]);
    echo "   ✓ Test session created with timing\n";
    
    // Insert test response with timing
    $responseTime = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO test_responses 
        (session_id, item_id, answer, theta_before, theta_after, se_after, item_order, 
         probability, information, response_time, response_duration_seconds)
        VALUES (?, 1, 1, 0.0, 0.5, 0.8, 1, 0.5, 0.3, ?, 15)
    ");
    $stmt->execute([$sessionId, $responseTime]);
    echo "   ✓ Test response created with timing\n";
    
    // Update session completion time
    $endTime = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        UPDATE test_sessions 
        SET test_completed = 1, completed_at = ?, total_duration_seconds = 25
        WHERE session_id = ?
    ");
    $stmt->execute([$endTime, $sessionId]);
    echo "   ✓ Session completion time updated\n";
    
    // Verify data
    $stmt = $pdo->prepare("
        SELECT started_at, completed_at, total_duration_seconds 
        FROM test_sessions 
        WHERE session_id = ?
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n4. Verification:\n";
    echo "   Session ID: $sessionId\n";
    echo "   Started at: {$session['started_at']}\n";
    echo "   Completed at: {$session['completed_at']}\n";
    echo "   Total duration: {$session['total_duration_seconds']} seconds\n";
    
    $stmt = $pdo->prepare("
        SELECT response_time, response_duration_seconds 
        FROM test_responses 
        WHERE session_id = ?
    ");
    $stmt->execute([$sessionId]);
    $response = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Response time: {$response['response_time']}\n";
    echo "   Response duration: {$response['response_duration_seconds']} seconds\n";
    
    // Clean up test data
    $pdo->prepare("DELETE FROM test_responses WHERE session_id = ?")->execute([$sessionId]);
    $pdo->prepare("DELETE FROM test_sessions WHERE session_id = ?")->execute([$sessionId]);
    echo "\n   ✓ Test data cleaned up\n";
    
    echo "\n=== Test PASSED - Timing functionality works correctly! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
