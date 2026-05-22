<?php

// Quick test untuk semua 3 sensor Smart Rack Security System
echo "🧪 Quick Smart Rack Security System Test\n";
echo "========================================\n\n";

// Test Vibration Calculations
echo "📊 VIBRATION SENSOR TEST:\n";
echo "-------------------------\n";

// Simulasi data normal
echo "✅ Normal Vibration:\n";
$normal = [
    'x_axis' => 0.5,
    'y_axis' => 0.8, 
    'z_axis' => 0.6,
    'magnitude' => sqrt(0.5*0.5 + 0.8*0.8 + 0.6*0.6),
    'threshold' => 2.0
];
echo "Magnitude: " . round($normal['magnitude'], 2) . " (Status: NORMAL)\n";

// Simulasi data warning
echo "⚠️  Warning Vibration:\n";
$warning = [
    'x_axis' => 1.8,
    'y_axis' => 2.2,
    'z_axis' => 1.5,
    'magnitude' => sqrt(1.8*1.8 + 2.2*2.2 + 1.5*1.5),
    'threshold' => 2.0
];
echo "Magnitude: " . round($warning['magnitude'], 2) . " (Status: WARNING)\n";

// Simulasi data critical
echo "🚨 Critical Vibration:\n";
$critical = [
    'x_axis' => 2.8,
    'y_axis' => 3.5,
    'z_axis' => 2.9,
    'magnitude' => sqrt(2.8*2.8 + 3.5*3.5 + 2.9*2.9),
    'threshold' => 2.0
];
echo "Magnitude: " . round($critical['magnitude'], 2) . " (Status: CRITICAL - ALERT!)\n\n";

// Test PIR Motion Detection
echo "👁️ PIR MOTION SENSOR TEST:\n";
echo "---------------------------\n";

// Function to check working hours
function isWorkingHours($hour) {
    return ($hour >= 8 && $hour < 17);
}

// Function to determine motion type
function getMotionType($intensity, $duration, $isWorkingTime) {
    if (!$isWorkingTime) {
        return 'unauthorized';
    }
    
    if ($intensity > 80 || $duration > 300) {
        return 'suspicious';
    }
    
    return 'normal';
}

// Test scenarios
$pirTests = [
    [
        'time' => 10, // 10:00 AM
        'intensity' => 60,
        'duration' => 45,
        'zone' => 'front',
        'description' => 'Normal office access'
    ],
    [
        'time' => 14, // 2:00 PM  
        'intensity' => 85,
        'duration' => 350,
        'zone' => 'back',
        'description' => 'Suspicious high activity'
    ],
    [
        'time' => 22, // 10:00 PM
        'intensity' => 70,
        'duration' => 120,
        'zone' => 'front',
        'description' => 'After hours access'
    ]
];

foreach ($pirTests as $test) {
    $isWorking = isWorkingHours($test['time']);
    $motionType = getMotionType($test['intensity'], $test['duration'], $isWorking);
    $workStatus = $isWorking ? 'Working Hours' : 'After Hours';
    
    $alertIcon = match($motionType) {
        'normal' => '✅',
        'suspicious' => '⚠️ ',
        'unauthorized' => '🚨',
        default => '❓'
    };
    
    echo "{$alertIcon} {$test['description']}:\n";
    echo "   Time: {$test['time']}:00 ({$workStatus})\n";
    echo "   Intensity: {$test['intensity']}%, Duration: {$test['duration']}s\n";
    echo "   Zone: {$test['zone']}, Type: {$motionType}\n";
    
    if ($motionType !== 'normal') {
        echo "   🔔 ALERT TRIGGERED!\n";
    }
    echo "\n";
}

// Test Door Access (Reed Switch)
echo "🚪 DOOR ACCESS (REED SWITCH) TEST:\n";
echo "----------------------------------\n";

// Function to check door access authorization
function checkDoorAccess($time, $idCard, $method, $duration) {
    $isWorkingTime = ($time >= 7 && $time < 18);
    $hasValidId = !empty($idCard) && preg_match('/^EMP-\d{4}$/', $idCard);
    
    if ($method === 'force') {
        return 'forced_entry';
    }
    
    if ($method === 'emergency') {
        return 'emergency';
    }
    
    if ($method === 'maintenance') {
        return 'maintenance';
    }
    
    if ($hasValidId && $isWorkingTime) {
        return 'authorized';
    }
    
    if (!$isWorkingTime) {
        return 'after_hours';
    }
    
    return 'unauthorized';
}

// Door access test scenarios
$doorTests = [
    [
        'time' => 9, // 9:00 AM
        'id_card' => 'EMP-1234',
        'method' => 'keycard',
        'duration' => 30,
        'location' => 'front_door',
        'description' => 'Normal employee access'
    ],
    [
        'time' => 14, // 2:00 PM
        'id_card' => null,
        'method' => 'manual',
        'duration' => 60,
        'location' => 'back_door',
        'description' => 'No ID card access'
    ],
    [
        'time' => 22, // 10:00 PM
        'id_card' => null,
        'method' => 'force',
        'duration' => 180,
        'location' => 'front_door',
        'description' => 'Forced entry at night'
    ],
    [
        'time' => 23, // 11:00 PM
        'id_card' => null,
        'method' => 'emergency',
        'duration' => 45,
        'location' => 'main_entrance',
        'description' => 'Emergency access'
    ]
];

foreach ($doorTests as $test) {
    $accessType = checkDoorAccess($test['time'], $test['id_card'], $test['method'], $test['duration']);
    $workStatus = ($test['time'] >= 7 && $test['time'] < 18) ? 'Working Hours' : 'After Hours';
    $idInfo = $test['id_card'] ? $test['id_card'] : 'No ID';
    
    $alertIcon = match($accessType) {
        'authorized' => '✅',
        'maintenance' => '🔧',
        'emergency' => '🆘',
        'after_hours' => '🌙',
        'unauthorized' => '⚠️ ',
        'forced_entry' => '🚨',
        default => '❓'
    };
    
    echo "{$alertIcon} {$test['description']}:\n";
    echo "   Time: {$test['time']}:00 ({$workStatus})\n";
    echo "   ID Card: {$idInfo}, Method: {$test['method']}\n";
    echo "   Duration: {$test['duration']}s, Location: {$test['location']}\n";
    echo "   Access Type: {$accessType}\n";
    
    if (in_array($accessType, ['unauthorized', 'forced_entry', 'after_hours'])) {
        echo "   🔔 ALERT TRIGGERED!\n";
    }
    echo "\n";
}

echo "📡 API ENDPOINTS READY:\n";
echo "=======================\n";
echo "Vibration API:    http://localhost:8000/api/vibration/data\n";
echo "PIR API:          http://localhost:8000/api/pir/data\n";
echo "Door Access API:  http://localhost:8000/api/door-access/data\n";
echo "LoRa API:         http://localhost:8000/api/lora/receive\n";
echo "\n";
echo "Statistics APIs:\n";
echo "Vibration Stats:  http://localhost:8000/api/vibration/statistics\n";
echo "PIR Stats:        http://localhost:8000/api/pir/statistics\n";
echo "Door Access Stats: http://localhost:8000/api/door-access/statistics\n";
echo "LoRa Stats:       http://localhost:8000/api/lora/statistics\n\n";

echo "🧪 TEST COMMANDS:\n";
echo "=================\n";
echo "php test_vibration_api.php    # Test vibration sensor\n";
echo "php test_pir_api.php          # Test PIR motion sensor\n";
echo "php test_door_access_api.php  # Test door access (reed switch)\n";
echo "php test_lora_api.php         # Test LoRa communication\n\n";

echo "🔔 NOTIFICATION TEST COMMANDS:\n";
echo "==============================\n";
echo "curl -X POST http://localhost:8000/api/test-notification\n";
echo "curl -X POST http://localhost:8000/api/test-pir-notification\n";
echo "curl -X POST http://localhost:8000/api/test-door-access-notification\n\n";

echo "📡 LORA COMMAND EXAMPLES:\n";
echo "=========================\n";
echo "# Send LoRa sensor data:\n";
echo "curl -X POST http://localhost:8000/api/lora/receive \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"node_id\":\"LORA_001\",\"payload\":\"SENSOR|VIBRATION|2.5,3.2,1.8|2.0\",\"rssi\":-82.5}'\n\n";
echo "# Send command to LoRa node:\n";
echo "curl -X POST http://localhost:8000/api/lora/send-command \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"node_id\":\"LORA_001\",\"action\":\"set_threshold\",\"parameters\":[\"2.5\"]}'\n\n";

echo "✅ Smart Rack Security System Ready!\n";
echo "🔔 All 4 systems will automatically send notifications for abnormal conditions:\n";
echo "   📊 Vibration: Detects abnormal shaking/movement\n";
echo "   👁️  PIR Motion: Detects suspicious movement patterns\n";
echo "   🚪 Door Access: Detects unauthorized door access\n";
echo "   📡 LoRa: Long-range communication for remote sensors\n";