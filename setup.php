<?php
/**
 * CAT Laravel Setup Script
 * Skrip untuk setup awal project Laravel CAT
 */

echo "CAT Laravel System - Setup Script\n";
echo "==================================\n\n";

// Check PHP version
echo "1. Checking PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    echo "   ⚠ PHP 8.1+ required for Laravel 10\n";
} else {
    echo "   ✓ PHP version OK\n";
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring'];
echo "\n2. Required Extensions:\n";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✓" : "✗";
    echo "   $status $ext\n";
}

// Check if .env exists
echo "\n3. Environment Setup:\n";
if (file_exists('.env')) {
    echo "   ✓ .env file exists\n";
} else {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "   ✓ .env created from example\n";
    } else {
        echo "   ✗ .env.example not found\n";
    }
}

// Check CSV file
echo "\n4. Data File Check:\n";
if (file_exists('Parameter_Item_IST.csv')) {
    $lines = count(file('Parameter_Item_IST.csv'));
    echo "   ✓ Parameter_Item_IST.csv found ({$lines} lines)\n";
} else {
    echo "   ✗ Parameter_Item_IST.csv not found\n";
    echo "      Please copy the CSV file to project root\n";
}

// Check directories
echo "\n5. Directory Structure:\n";
$directories = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'database/migrations',
    'app/Models',
    'app/Services'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "   ✓ {$dir}\n";
    } else {
        mkdir($dir, 0755, true);
        echo "   ✓ {$dir} (created)\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ SETUP COMPLETED!\n";
echo str_repeat("=", 50) . "\n\n";

echo "Next Steps:\n";
echo "1. Install Composer dependencies: composer install\n";
echo "2. Generate app key: php artisan key:generate\n";
echo "3. Create database: CREATE DATABASE cat_laravel;\n";
echo "4. Update .env with database credentials\n";
echo "5. Run migrations: php artisan migrate\n";
echo "6. Seed database: php artisan db:seed\n";
echo "7. Start server: php artisan serve\n";
echo "8. Access: http://localhost:8000\n\n";
?>
