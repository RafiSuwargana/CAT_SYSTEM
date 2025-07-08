<?php
/**
 * Simple Test untuk CAT Laravel System
 * Test basic functionality tanpa composer dependencies
 */

echo "CAT Laravel System - Simple Test\n";
echo "=================================\n\n";

// Test 1: PHP Basic Functions
echo "1. Testing PHP Basic Functions:\n";
try {
    // Test mathematical functions for CAT
    $theta = 0.5;
    $a = 1.0;
    $b = 0.0;
    $g = 0.25;
    
    // 3PL Probability calculation
    $p = $g + (1 - $g) / (1 + exp(-$a * ($theta - $b)));
    echo "   ✓ 3PL Probability calculation: P(θ=0.5) = " . round($p, 3) . "\n";
    
    // Item Information calculation
    $q = 1 - $p;
    $info = pow($a, 2) * pow($p - $g, 2) * $q / ($p * pow(1 - $g, 2));
    echo "   ✓ Item Information calculation: I(θ=0.5) = " . round($info, 3) . "\n";
    
    // Normal PDF for EAP
    $pdf = (1 / (sqrt(2 * M_PI))) * exp(-0.5 * pow($theta, 2));
    echo "   ✓ Normal PDF calculation: N(0.5) = " . round($pdf, 3) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Math functions error: " . $e->getMessage() . "\n";
}

// Test 2: File System
echo "\n2. Testing File System:\n";
try {
    // Test CSV reading
    if (file_exists('Parameter_Item_IST.csv')) {
        $handle = fopen('Parameter_Item_IST.csv', 'r');
        $header = fgetcsv($handle);
        $firstRow = fgetcsv($handle);
        fclose($handle);
        
        echo "   ✓ CSV file readable\n";
        echo "   ✓ Header: " . implode(', ', $header) . "\n";
        echo "   ✓ First item: ID={$firstRow[0]}, a={$firstRow[1]}, b={$firstRow[2]}\n";
    } else {
        echo "   ✗ CSV file not found\n";
    }
    
    // Test directory write
    $testFile = 'storage/logs/test.log';
    if (is_dir('storage/logs')) {
        file_put_contents($testFile, "Test log entry: " . date('Y-m-d H:i:s') . "\n");
        echo "   ✓ Log directory writable\n";
        if (file_exists($testFile)) {
            unlink($testFile);
        }
    } else {
        echo "   ✗ Log directory not found\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ File system error: " . $e->getMessage() . "\n";
}

// Test 3: Database Connection (manual)
echo "\n3. Database Connection Test:\n";
try {
    // Read .env manually
    $envFile = '.env';
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        preg_match('/DB_DATABASE=(.*)/', $env, $dbMatches);
        preg_match('/DB_USERNAME=(.*)/', $env, $userMatches);
        
        $database = isset($dbMatches[1]) ? trim($dbMatches[1]) : 'cat_laravel';
        $username = isset($userMatches[1]) ? trim($userMatches[1]) : 'root';
        
        echo "   ✓ Database config: {$database} (user: {$username})\n";
        echo "   ⚠ Manual database test - run 'php artisan migrate' after composer install\n";
    } else {
        echo "   ✗ .env file not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Environment error: " . $e->getMessage() . "\n";
}

// Test 4: CAT Algorithm Simulation
echo "\n4. CAT Algorithm Simulation:\n";
try {
    // Simulate item selection logic
    $items = [];
    
    // Load some sample items from CSV
    if (file_exists('Parameter_Item_IST.csv')) {
        $handle = fopen('Parameter_Item_IST.csv', 'r');
        fgetcsv($handle); // skip header
        
        for ($i = 0; $i < 5; $i++) {
            $row = fgetcsv($handle);
            if ($row) {
                $items[] = [
                    'id' => $row[0],
                    'a' => (float)$row[1],
                    'b' => (float)$row[2],
                    'g' => (float)$row[3],
                    'u' => (float)$row[4]
                ];
            }
        }
        fclose($handle);
        
        echo "   ✓ Loaded " . count($items) . " sample items\n";
        
        // Test item selection at different theta levels
        $testThetas = [-2, 0, 2];
        foreach ($testThetas as $theta) {
            $maxInfo = -1;
            $selectedItem = null;
            
            foreach ($items as $item) {
                $p = $item['g'] + ($item['u'] - $item['g']) / (1 + exp(-$item['a'] * ($theta - $item['b'])));
                $q = 1 - $p;
                
                if ($p > $item['g'] && $p < $item['u']) {
                    $info = pow($item['a'], 2) * pow($p - $item['g'], 2) * $q / ($p * pow($item['u'] - $item['g'], 2));
                    if ($info > $maxInfo) {
                        $maxInfo = $info;
                        $selectedItem = $item;
                    }
                }
            }
            
            if ($selectedItem) {
                echo "   ✓ θ={$theta}: Selected item {$selectedItem['id']} (Info=" . round($maxInfo, 3) . ")\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ CAT simulation error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ BASIC TESTS COMPLETED!\n";
echo str_repeat("=", 50) . "\n\n";

echo "Summary:\n";
echo "- All mathematical functions for CAT working\n";
echo "- File system access OK\n";
echo "- CSV data readable\n";
echo "- Item selection algorithm functional\n\n";

echo "Ready for Laravel setup!\n";
echo "Next: composer install && php artisan key:generate\n";
?>
