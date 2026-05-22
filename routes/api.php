<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VibrationController;
use App\Http\Controllers\Api\PirController;
use App\Http\Controllers\Api\DoorAccessController;
use App\Http\Controllers\Api\LoRaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Vibration Sensor API Routes
Route::prefix('vibration')->group(function () {
    // Terima data sensor getar dari IoT device
    Route::post('/data', [VibrationController::class, 'receiveData']);
    
    // Ambil data vibration terbaru
    Route::get('/readings', [VibrationController::class, 'getLatestReadings']);
    
    // Ambil statistik getaran
    Route::get('/statistics', [VibrationController::class, 'getStatistics']);
});

// PIR Sensor API Routes
Route::prefix('pir')->group(function () {
    // Terima data sensor PIR dari IoT device
    Route::post('/data', [PirController::class, 'receiveData']);
    
    // Ambil data PIR terbaru
    Route::get('/readings', [PirController::class, 'getLatestReadings']);
    
    // Ambil statistik gerakan
    Route::get('/statistics', [PirController::class, 'getStatistics']);
});

// Door Access (Reed Switch) API Routes
Route::prefix('door-access')->group(function () {
    // Terima data sensor Reed Switch dari IoT device
    Route::post('/data', [DoorAccessController::class, 'receiveData']);
    
    // Ambil data door access terbaru
    Route::get('/readings', [DoorAccessController::class, 'getLatestReadings']);
    
    // Ambil statistik door access
    Route::get('/statistics', [DoorAccessController::class, 'getStatistics']);
});

// LoRa Communication API Routes
Route::prefix('lora')->group(function () {
    // Terima message LoRa dari gateway
    Route::post('/receive', [LoRaController::class, 'receiveMessage']);
    
    // Kirim command ke LoRa node
    Route::post('/send-command', [LoRaController::class, 'sendCommand']);
    
    // Kirim konfigurasi ke LoRa node
    Route::post('/send-config', [LoRaController::class, 'sendConfig']);
    
    // Ambil messages LoRa terbaru
    Route::get('/messages', [LoRaController::class, 'getMessages']);
    
    // Ambil statistik LoRa communication
    Route::get('/statistics', [LoRaController::class, 'getStatistics']);
    
    // Process unprocessed messages
    Route::post('/process-messages', [LoRaController::class, 'processUnprocessedMessages']);
});

// Test notification endpoints
Route::post('/test-notification', function () {
    $notificationService = new \App\Services\NotificationService();
    $result = $notificationService->sendTestNotification();
    
    return response()->json([
        'success' => true,
        'message' => 'Test vibration notification sent',
        'results' => $result
    ]);
});

Route::post('/test-pir-notification', function () {
    $notificationService = new \App\Services\NotificationService();
    $result = $notificationService->sendTestPirNotification();
    
    return response()->json([
        'success' => true,
        'message' => 'Test PIR notification sent',
        'results' => $result
    ]);
});

Route::post('/test-door-access-notification', function () {
    $notificationService = new \App\Services\NotificationService();
    $result = $notificationService->sendTestDoorAccessNotification();
    
    return response()->json([
        'success' => true,
        'message' => 'Test Door Access notification sent',
        'results' => $result
    ]);
});