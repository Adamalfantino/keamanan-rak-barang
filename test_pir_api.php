<?php

/**
 * Script untuk test PIR Motion Detection API
 */

function testPirAPI() {
    $baseUrl = 'http://localhost:8000/api';
    
    echo "👁️ Testing PIR Motion Detection API\n";
    echo "====================================\n\n";
    
    // Test 1: Normal motion (jam kerja)
    echo "✅ Test 1: Normal Motion (Working Hours)\n";
    $normalData = [
        'device_id' => 1,
        'motion_detected' => true,
        'motion_intensity' => 60,
        'duration_seconds' => 45,
        'detection_zone' => 'front',
        'metadata' => [
            'sensor_type' => 'PIR_HC-SR501',
            'test_case' => 'normal_motion'
        ]
    ];
    
    $result1 = sendPirData($baseUrl, $normalData);
    echo "Response: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: Suspicious motion (intensitas tinggi)
    echo "⚠️  Test 2: Suspicious Motion (High Intensity)\n";
    $suspiciousData = [
        'device_id' => 1,
        'motion_detected' => true,
        'motion_intensity' => 90,
        'duration_seconds' => 350,
        'detection_zone' => 'back',
        'metadata' => [
            'sensor_type' => 'PIR_HC-SR501',
            'test_case' => 'suspicious_motion'
        ]
    ];
    
    $result2 = sendPirData($baseUrl, $suspiciousData);
    echo "Response: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Unauthorized motion (simulasi luar jam kerja)
    echo "🚨 Test 3: Unauthorized Motion (After Hours)\n";
    $unauthorizedData = [
        'device_id' => 1,
        'motion_detected' => true,
        'motion_intensity' => 75,
        'duration_seconds' => 120,
        'detection_zone' => 'front',
        'metadata' => [
            'sensor_type' => 'PIR_HC-SR501',
            'test_case' => 'unauthorized_motion',
            'simulated_time' => '22:30:00' // Simulasi malam hari
        ]
    ];
    
    $result3 = sendPirData($baseUrl, $unauthorizedData);
    echo "Response: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: No motion detected
    echo "❌ Test 4: No Motion Detected\n";
    $noMotionData = [
        'device_id' => 1,
        'motion_detected' => false,
        'motion_intensity' => 0,
        'duration_seconds' => 0,
        'detection_zone' => 'center',
        'metadata' => [
            'sensor_type' => 'PIR_HC-SR501',
            'test_case' => 'no_motion'
        ]
    ];
    
    $result4 = sendPirData($baseUrl, $noMotionData);
    echo "Response: " . json_encode($result4, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 5: Get latest PIR readings
    echo "📋 Test 5: Get Latest PIR Readings\n";
    $readings = getLatestPirReadings($baseUrl);
    echo "Latest readings: " . json_encode($readings, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 6: Get PIR statistics
    echo "📊 Test 6: Get PIR Statistics\n";
    $stats = getPirStatistics($baseUrl);
    echo "Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 7: Test PIR notification
    echo "🔔 Test 7: Test PIR Notification\n";
    $notifTest = testPirNotification($baseUrl);
    echo "PIR notification test: " . json_encode($notifTest, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "✅ All PIR tests completed!\n";
    echo "📊 Summary:\n";
    echo "- Normal motion: Should not trigger alert\n";
    echo "- Suspicious motion: Should trigger medium priority alert\n";
    echo "- Unauthorized motion: Should trigger high priority alert\n";
    echo "- No motion: Should be logged normally\n";
}

function sendPirData($baseUrl, $data) {
    $url = $baseUrl . '/pir/data';
    
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

function getLatestPirReadings($baseUrl) {
    $url = $baseUrl . '/pir/readings?limit=5';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getPirStatistics($baseUrl) {
    $url = $baseUrl . '/pir/statistics?hours=24';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function testPirNotification($baseUrl) {
    $url = $baseUrl . '/test-pir-notification';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Simulasi data PIR untuk testing
function simulatePirScenarios() {
    echo "🎭 PIR Motion Simulation Scenarios\n";
    echo "==================================\n\n";
    
    // Scenario 1: Normal office activity
    echo "📊 Scenario 1: Normal Office Activity (Working Hours)\n";
    $scenarios = [
        ['intensity' => 45, 'duration' => 30, 'zone' => 'front', 'description' => 'Employee accessing rack'],
        ['intensity' => 60, 'duration' => 90, 'zone' => 'center', 'description' => 'Maintenance work'],
        ['intensity' => 35, 'duration' => 15, 'zone' => 'side', 'description' => 'Quick check']
    ];
    
    foreach ($scenarios as $scenario) {
        echo "- {$scenario['description']}: Intensity {$scenario['intensity']}%, Duration {$scenario['duration']}s, Zone {$scenario['zone']}\n";
    }
    echo "\n";
    
    // Scenario 2: Suspicious activity
    echo "⚠️  Scenario 2: Suspicious Activity\n";
    $suspicious = [
        ['intensity' => 85, 'duration' => 400, 'zone' => 'back', 'description' => 'Prolonged unauthorized access'],
        ['intensity' => 95, 'duration' => 180, 'zone' => 'front', 'description' => 'High intensity movement'],
        ['intensity' => 70, 'duration' => 600, 'zone' => 'side', 'description' => 'Very long duration activity']
    ];
    
    foreach ($suspicious as $scenario) {
        echo "- {$scenario['description']}: Intensity {$scenario['intensity']}%, Duration {$scenario['duration']}s, Zone {$scenario['zone']} (ALERT!)\n";
    }
    echo "\n";
    
    // Scenario 3: After hours (unauthorized)
    echo "🚨 Scenario 3: After Hours Activity (Unauthorized)\n";
    $afterHours = [
        ['time' => '22:30', 'intensity' => 60, 'duration' => 120, 'description' => 'Night access attempt'],
        ['time' => '06:15', 'intensity' => 75, 'duration' => 200, 'description' => 'Early morning activity'],
        ['time' => '23:45', 'intensity' => 40, 'duration' => 60, 'description' => 'Late night movement']
    ];
    
    foreach ($afterHours as $scenario) {
        echo "- {$scenario['description']} at {$scenario['time']}: Intensity {$scenario['intensity']}%, Duration {$scenario['duration']}s (HIGH ALERT!)\n";
    }
    echo "\n";
}

// Jalankan test
echo "🚀 Starting PIR API Tests...\n\n";
simulatePirScenarios();
testPirAPI();