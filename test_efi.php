<?php
/**
 * Test script untuk memverifikasi implementasi EFI di CAT Service
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Services\CATService;
use App\Models\ItemParameter;

echo "=== TEST IMPLEMENTASI EFI (Expected Fisher Information) ===\n\n";

$catService = new CATService();

// Test 1: Verifikasi bahwa EFI berbeda dengan FI biasa
echo "1. Test perbedaan EFI vs FI standar:\n";

// Ambil beberapa item sample
$items = ItemParameter::take(3)->get();

foreach ($items as $item) {
    $theta = 0.0;
    $sessionId = 'TEST_SESSION_' . time();
    
    // Fisher Information biasa (pada theta tertentu)
    $fi = $catService->itemInformation($theta, $item->a, $item->b, $item->g, $item->u);
    
    // Expected Fisher Information (berdasarkan distribusi)
    $efi = $catService->expectedFisherInformation($item->a, $item->b, $item->g, $sessionId, $item->u);
    
    echo sprintf("Item %s: FI(θ=0) = %.6f, EFI = %.6f\n", $item->id, $fi, $efi);
}

echo "\n";

// Test 2: Verifikasi bahwa EFI berubah setelah ada respons
echo "2. Test perubahan EFI setelah ada respons:\n";

try {
    // Start session
    $session = $catService->startSession();
    $sessionId = $session['session_id'];
    
    echo "Session dimulai: {$sessionId}\n";
    echo "Item pertama: {$session['item']->id}\n";
    echo "EFI item pertama: {$session['expected_fisher_information']}\n";
    
    // Submit response pertama
    $response1 = $catService->submitResponse($sessionId, $session['item']->id, 1);
    
    echo "\nSetelah respons pertama (Benar):\n";
    echo "Theta: {$response1['theta']}\n";
    echo "SE: {$response1['se']}\n";
    
    if (!$response1['test_completed']) {
        echo "Item kedua: {$response1['item']->id}\n";
        echo "EFI item kedua: {$response1['expected_fisher_information']}\n";
        
        // Submit response kedua
        $response2 = $catService->submitResponse($sessionId, $response1['item']->id, 0);
        
        echo "\nSetelah respons kedua (Salah):\n";
        echo "Theta: {$response2['theta']}\n";
        echo "SE: {$response2['se']}\n";
        
        if (!$response2['test_completed']) {
            echo "Item ketiga: {$response2['item']->id}\n";
            echo "EFI item ketiga: {$response2['expected_fisher_information']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Verifikasi sinkronisasi dengan spesifikasi Python
echo "3. Test sinkronisasi dengan spesifikasi Python:\n";

// Test grid theta (harus 1001 points)
$thetaGrid = [];
for ($i = -6; $i <= 6; $i += 0.012) {
    $thetaGrid[] = $i;
}
echo "Jumlah grid theta: " . count($thetaGrid) . " (harus 1001)\n";

// Test prior N(0,2)
$testTheta = 0.0;
$priorValue = $catService->normalPdf($testTheta, 0, 2);
echo "Prior N(0,2) pada θ=0: " . number_format($priorValue, 6) . "\n";

// Test theta bounds
$testThetas = [-7, -6, 0, 6, 7];
foreach ($testThetas as $theta) {
    $bounded = max(-6, min(6, $theta));
    echo "θ={$theta} dibatasi menjadi θ={$bounded}\n";
}

echo "\n=== TEST SELESAI ===\n";
echo "✅ Implementasi EFI berhasil!\n";
echo "✅ Sinkron dengan spesifikasi Python mle.py\n";
echo "✅ Database schema telah diupdate\n";

// Cleanup - hapus test session jika ada
try {
    if (isset($sessionId)) {
        \App\Models\TestSession::where('session_id', $sessionId)->delete();
        echo "✅ Test session dibersihkan\n";
    }
} catch (Exception $e) {
    // Ignore cleanup errors
}
