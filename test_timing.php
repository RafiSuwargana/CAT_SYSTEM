<?php

require_once 'vendor/autoload.php';

use App\Services\CATService;
use App\Models\TestSession;
use App\Models\TestResponse;
use Illuminate\Support\Facades\Log;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$catService = new CATService();

echo "=== Test CAT System dengan Timing ===\n\n";

try {
    // Start session
    echo "1. Memulai sesi CAT...\n";
    $session = $catService->startSession();
    $sessionId = $session['session_id'];
    echo "   Session ID: {$sessionId}\n";
    echo "   Started at: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Simulate answering questions with timing
    $responses = [
        ['answer' => 1, 'duration' => 5],  // 5 detik
        ['answer' => 0, 'duration' => 8],  // 8 detik  
        ['answer' => 1, 'duration' => 3],  // 3 detik
        ['answer' => 1, 'duration' => 12], // 12 detik
        ['answer' => 0, 'duration' => 7],  // 7 detik
    ];
    
    $currentItem = $session['item'];
    $responseCount = 0;
    
    foreach ($responses as $response) {
        $responseCount++;
        echo "2.{$responseCount} Menjawab soal {$responseCount}...\n";
        echo "   Item ID: {$currentItem['id']}\n";
        echo "   Jawaban: " . ($response['answer'] ? 'Benar' : 'Salah') . "\n";
        echo "   Durasi: {$response['duration']} detik\n";
        
        // Submit response dengan timing
        $result = $catService->submitResponse(
            $sessionId,
            $currentItem['id'],
            $response['answer'],
            $response['duration']
        );
        
        if ($result['test_completed']) {
            echo "   Tes selesai!\n";
            echo "   Total durasi: {$result['total_duration_seconds']} detik\n";
            echo "   Completed at: {$result['completed_at']}\n";
            echo "   Final score: {$result['final_score']}\n";
            echo "   Stop reason: {$result['stop_reason']}\n";
            break;
        } else {
            echo "   Theta baru: {$result['theta']}\n";
            echo "   SE baru: {$result['se']}\n";
            $currentItem = $result['item'];
        }
        echo "\n";
        
        // Simulasi delay untuk testing
        sleep(1);
    }
    
    // Get session history
    echo "3. Mengambil riwayat sesi...\n";
    $history = $catService->getSessionHistory($sessionId);
    
    echo "   Total responses: " . count($history['responses']) . "\n";
    echo "   Total duration: {$history['total_duration_seconds']} detik\n";
    echo "   Average response time: " . number_format($history['average_response_time'], 2) . " detik\n\n";
    
    // Show detailed timing for each response
    echo "4. Detail timing per soal:\n";
    foreach ($history['responses'] as $i => $resp) {
        echo "   Soal " . ($i + 1) . ": {$resp->response_duration_seconds} detik\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test selesai ===\n";
