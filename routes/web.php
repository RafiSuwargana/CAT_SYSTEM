<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CATController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Test timing route
Route::get('/test-timing', function () {
    return view('test-timing-fixed');
})->name('test-timing');

// API Routes for CAT
Route::prefix('api')->group(function () {
    Route::post('/cat/start', [CATController::class, 'startTest'])->name('api.cat.start');
    Route::post('/cat/submit', [CATController::class, 'submitResponse'])->name('api.cat.submit');
    Route::get('/cat/history/{sessionId}', [CATController::class, 'getSessionHistory'])->name('api.cat.history');
    
    // Legacy routes (untuk backward compatibility)
    Route::post('/start-test', [CATController::class, 'startTest'])->name('api.start-test');
    Route::post('/submit-response', [CATController::class, 'submitResponse'])->name('api.submit-response');
    Route::get('/session-history/{sessionId}', [CATController::class, 'getSessionHistory'])->name('api.session-history');
});
