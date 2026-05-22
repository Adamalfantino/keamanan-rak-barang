<?php

/**
 * Script untuk test Door Access (Reed Switch) API
 */

function testDoorAccessAPI() {
    $baseUrl = 'http://localhost:8000/api';
    
    echo "🚪 Testing Door Access (Reed Switch) API\n";
    echo "========================================\n\n";
    
    // Test 1: Authorized access (jam kerja + ID card)
    echo "✅ Test 1: Authorized Access (Working Hours + ID Card)\n";
    $authorizedData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'keycard',
        'user_id_card' => 'EMP-1234',
        'duration_seconds' => 30,
        'door_location' => 'front_door',
        'is_forced_entry' => false,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'authorized_access'
        ]
    ];
    
    $result1 = sendDoorAccessData($baseUrl, $authorizedData);
    echo "Response: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: Unauthorized access (tanpa ID card)
    echo "⚠️  Test 2: Unauthorized Access (No ID Card)\n";
    $unauthorizedData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'manual',
        'duration_seconds' => 60,
        'door_location' => 'back_door',
        'is_forced_entry' => false,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'unauthorized_access'
        ]
    ];
    
    $result2 = sendDoorAccessData($baseUrl, $unauthorizedData);
    echo "Response: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Forced entry (CRITICAL ALERT)
    echo "🚨 Test 3: Forced Entry (CRITICAL ALERT)\n";
    $forcedEntryData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'force',
        'duration_seconds' => 180,
        'door_location' => 'front_door',
        'is_forced_entry' => true,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'forced_entry'
        ]
    ];
    
    $result3 = sendDoorAccessData($baseUrl, $forcedEntryData);
    echo "Response: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: After hours access (luar jam kerja)
    echo "🌙 Test 4: After Hours Access\n";
    $afterHoursData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'keycard',
        'user_id_card' => 'EMP-5678',
        'duration_seconds' => 45,
        'door_location' => 'side_door',
        'is_forced_entry' => false,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'after_hours_access',
            'simulated_time' => '20:30:00' // Simulasi malam hari
        ]
    ];
    
    $result4 = sendDoorAccessData($baseUrl, $afterHoursData);
    echo "Response: " . json_encode($result4, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 5: Emergency access
    echo "🆘 Test 5: Emergency Access\n";
    $emergencyData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'emergency',
        'duration_seconds' => 90,
        'door_location' => 'main_entrance',
        'is_forced_entry' => false,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'emergency_access',
            'emergency_code' => 'FIRE-001'
        ]
    ];
    
    $result5 = sendDoorAccessData($baseUrl, $emergencyData);
    echo "Response: " . json_encode($result5, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 6: Maintenance access
    echo "🔧 Test 6: Maintenance Access\n";
    $maintenanceData = [
        'device_id' => 1,
        'door_opened' => true,
        'access_method' => 'maintenance',
        'user_id_card' => 'MAINT-001',
        'duration_seconds' => 300,
        'door_location' => 'back_door',
        'is_forced_entry' => false,
        'metadata' => [
            'sensor_type' => 'Reed_Switch_Magnetic',
            'test_case' => 'maintenance_access',
            'work_order' => 'WO-2026-001'
        ]
    ];
    
    $result6 = sendDoorAccessData($baseUrl, $maintenanceData);
    echo "Response: " . json_encode($result6, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 7: Get latest door access readings
    echo "📋 Test 7: Get Latest Door Access Readings\n";
    $readings = getLatestDoorAccessReadings($baseUrl);
    echo "Latest readings: " . json_encode($readings, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 8: Get door access statistics
    echo "📊 Test 8: Get Door Access Statistics\n";
    $stats = getDoorAccessStatistics($baseUrl);
    echo "Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 9: Test door access notification
    echo "🔔 Test 9: Test Door Access Notification\n";
    $notifTest = testDoorAccessNotification($baseUrl);
    echo "Door access notification test: " . json_encode($notifTest, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "✅ All Door Access tests completed!\n";
    echo "📊 Summary:\n";
    echo "- Authorized access: Should not trigger alert\n";
    echo "- Unauthorized access: Should trigger medium/high priority alert\n";
    echo "- Forced entry: Should trigger CRITICAL alert\n";
    echo "- After hours: Should trigger high priority alert\n";
    echo "- Emergency: Should be logged but may not trigger alert\n";
    echo "- Maintenance: Should be logged with low priority\n";
}

function sendDoorAccessData($baseUrl, $data) {
    $url = $baseUrl . '/door-access/data';
    
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

function getLatestDoorAccessReadings($baseUrl) {
    $url = $baseUrl . '/door-access/readings?limit=5';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getDoorAccessStatistics($baseUrl) {
    $url = $baseUrl . '/door-access/statistics?hours=24';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function testDoorAccessNotification($baseUrl) {
    $url = $baseUrl . '/test-door-access-notification';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Simulasi skenario door access untuk testing
function simulateDoorAccessScenarios() {
    echo "🎭 Door Access Simulation Scenarios\n";
    echo "===================================\n\n";
    
    // Scenario 1: Normal office access patterns
    echo "📊 Scenario 1: Normal Office Access Patterns\n";
    $normalScenarios = [
        ['time' => '08:30', 'id' => 'EMP-1234', 'method' => 'keycard', 'duration' => 25, 'location' => 'front_door', 'description' => 'Employee morning arrival'],
        ['time' => '12:00', 'id' => 'EMP-5678', 'method' => 'keycard', 'duration' => 15, 'location' => 'side_door', 'description' => 'Lunch break access'],
        ['time' => '17:30', 'id' => 'EMP-9012', 'method' => 'keycard', 'duration' => 20, 'location' => 'front_door', 'description' => 'End of day exit']
    ];
    
    foreach ($normalScenarios as $scenario) {
        echo "- {$scenario['description']}: {$scenario['time']}, ID: {$scenario['id']}, Duration: {$scenario['duration']}s, Location: {$scenario['location']}\n";
    }
    echo "\n";
    
    // Scenario 2: Suspicious access patterns
    echo "⚠️  Scenario 2: Suspicious Access Patterns\n";
    $suspiciousScenarios = [
        ['time' => '14:30', 'id' => null, 'method' => 'manual', 'duration' => 120, 'location' => 'back_door', 'description' => 'No ID card access during work hours'],
        ['time' => '16:45', 'id' => 'EMP-INVALID', 'method' => 'keycard', 'duration' => 200, 'location' => 'side_door', 'description' => 'Invalid ID card format'],
        ['time' => '11:00', 'id' => 'EMP-1234', 'method' => 'keycard', 'duration' => 300, 'location' => 'front_door', 'description' => 'Door open too long (5 minutes)']
    ];
    
    foreach ($suspiciousScenarios as $scenario) {
        $idInfo = $scenario['id'] ? $scenario['id'] : 'No ID';
        echo "- {$scenario['description']}: {$scenario['time']}, ID: {$idInfo}, Duration: {$scenario['duration']}s, Location: {$scenario['location']} (ALERT!)\n";
    }
    echo "\n";
    
    // Scenario 3: Critical security breaches
    echo "🚨 Scenario 3: Critical Security Breaches\n";
    $criticalScenarios = [
        ['time' => '22:15', 'id' => null, 'method' => 'force', 'duration' => 180, 'location' => 'front_door', 'description' => 'Forced entry at night'],
        ['time' => '03:30', 'id' => null, 'method' => 'manual', 'duration' => 240, 'location' => 'back_door', 'description' => 'Unauthorized access early morning'],
        ['time' => '19:45', 'id' => 'EMP-1234', 'method' => 'keycard', 'duration' => 600, 'location' => 'side_door', 'description' => 'After hours with valid ID but suspicious duration']
    ];
    
    foreach ($criticalScenarios as $scenario) {
        $idInfo = $scenario['id'] ? $scenario['id'] : 'No ID';
        echo "- {$scenario['description']}: {$scenario['time']}, ID: {$idInfo}, Duration: {$scenario['duration']}s, Location: {$scenario['location']} (CRITICAL ALERT!)\n";
    }
    echo "\n";
    
    // Scenario 4: Special access types
    echo "🔧 Scenario 4: Special Access Types\n";
    $specialScenarios = [
        ['time' => '06:00', 'id' => 'MAINT-001', 'method' => 'maintenance', 'duration' => 1800, 'location' => 'back_door', 'description' => 'Early maintenance work'],
        ['time' => '23:00', 'id' => null, 'method' => 'emergency', 'duration' => 60, 'location' => 'main_entrance', 'description' => 'Emergency evacuation access'],
        ['time' => '20:00', 'id' => 'SEC-001', 'method' => 'keycard', 'duration' => 300, 'location' => 'front_door', 'description' => 'Security guard patrol']
    ];
    
    foreach ($specialScenarios as $scenario) {
        $idInfo = $scenario['id'] ? $scenario['id'] : 'Emergency';
        echo "- {$scenario['description']}: {$scenario['time']}, ID: {$idInfo}, Duration: {$scenario['duration']}s, Location: {$scenario['location']}\n";
    }
    echo "\n";
}

// ID Card validation test
function testIdCardValidation() {
    echo "🆔 ID Card Validation Test\n";
    echo "=========================\n\n";
    
    $testCards = [
        'EMP-1234' => true,   // Valid format
        'EMP-5678' => true,   // Valid format
        'EMP-0001' => true,   // Valid format
        'INVALID' => false,   // Invalid format
        'EMP-12345' => false, // Too many digits
        'EMP-123' => false,   // Too few digits
        'MAINT-001' => false, // Wrong prefix
        '' => false,          // Empty
        null => false         // Null
    ];
    
    foreach ($testCards as $card => $expected) {
        $isValid = preg_match('/^EMP-\d{4}$/', $card ?? '');
        $result = $isValid ? 'VALID' : 'INVALID';
        $status = ($isValid == $expected) ? '✅' : '❌';
        
        echo "{$status} ID Card: '{$card}' -> {$result}\n";
    }
    echo "\n";
}

// Jalankan test
echo "🚀 Starting Door Access API Tests...\n\n";
simulateDoorAccessScenarios();
testIdCardValidation();
testDoorAccessAPI();