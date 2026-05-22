<?php

/**
 * Script untuk test Vibration API
 */

function testVibrationAPI() {
    $baseUrl = 'http://localhost:8000/api';
    
    echo "🧪 Testing Vibration Monitoring API\n";
    echo "=====================================\n\n";
    
    // Test 1: Normal vibration
    echo "📊 Test 1: Normal Vibration\n";
    $normalData = [
        'device_id' => 1,
        'x_axis' => 0.5,
        'y_axis' => 0.8,
        'z_axis' => 0.6,
        'threshold' => 2.0,
        'metadata' => [
            'sensor_type' => 'accelerometer',
            'test_case' => 'normal_vibration'
        ]
    ];
    
    $result1 = sendVibrationData($baseUrl, $normalData);
    echo "Response: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: Warning vibration
    echo "⚠️  Test 2: Warning Vibration\n";
    $warningData = [
        'device_id' => 1,
        'x_axis' => 1.8,
        'y_axis' => 2.2,
        'z_axis' => 1.5,
        'threshold' => 2.0,
        'metadata' => [
            'sensor_type' => 'accelerometer',
            'test_case' => 'warning_vibration'
        ]
    ];
    
    $result2 = sendVibrationData($baseUrl, $warningData);
    echo "Response: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Critical vibration (akan trigger alert)
    echo "🚨 Test 3: Critical Vibration (Alert Trigger)\n";
    $criticalData = [
        'device_id' => 1,
        'x_axis' => 2.8,
        'y_axis' => 3.5,
        'z_axis' => 2.9,
        'threshold' => 2.0,
        'metadata' => [
            'sensor_type' => 'accelerometer',
            'test_case' => 'critical_vibration'
        ]
    ];
    
    $result3 = sendVibrationData($baseUrl, $criticalData);
    echo "Response: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Get latest readings
    echo "📋 Test 4: Get Latest Readings\n";
    $readings = getLatestReadings($baseUrl);
    echo "Latest readings: " . json_encode($readings, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 5: Get statistics
    echo "📊 Test 5: Get Statistics\n";
    $stats = getStatistics($baseUrl);
    echo "Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 6: Test notification
    echo "🔔 Test 6: Test Notification\n";
    $notifTest = testNotification($baseUrl);
    echo "Notification test: " . json_encode($notifTest, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "✅ All tests completed!\n";
}

function sendVibrationData($baseUrl, $data) {
    $url = $baseUrl . '/vibration/data';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return ['error' => 'cURL error'];
    }
    
    return json_decode($response, true);
}

function getLatestReadings($baseUrl) {
    $url = $baseUrl . '/vibration/readings?limit=5';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getStatistics($baseUrl) {
    $url = $baseUrl . '/vibration/statistics?hours=24';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function testNotification($baseUrl) {
    $url = $baseUrl . '/test-notification';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Jalankan test
testVibrationAPI();