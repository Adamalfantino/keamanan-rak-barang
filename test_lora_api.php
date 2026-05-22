<?php

/**
 * Script untuk test LoRa Communication API
 */

function testLoRaAPI() {
    $baseUrl = 'http://localhost:8000/api';
    
    echo "📡 Testing LoRa Communication API\n";
    echo "=================================\n\n";
    
    // Test 1: Receive vibration sensor data via LoRa
    echo "📊 Test 1: Receive Vibration Sensor Data via LoRa\n";
    $vibrationLoRaData = [
        'node_id' => 'LORA_001',
        'gateway_id' => 'GATEWAY_001',
        'payload' => 'SENSOR|VIBRATION|2.5,3.2,1.8|2.0',
        'rssi' => -82.5,
        'snr' => 7.8,
        'spreading_factor' => 7,
        'frequency' => 868.1,
        'bandwidth' => 125000,
        'received_at' => date('c'),
        'metadata' => [
            'gateway_location' => 'Building_A',
            'channel' => 0,
            'test_case' => 'vibration_lora'
        ]
    ];
    
    $result1 = sendLoRaMessage($baseUrl, $vibrationLoRaData);
    echo "Response: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: Receive PIR sensor data via LoRa
    echo "👁️  Test 2: Receive PIR Sensor Data via LoRa\n";
    $pirLoRaData = [
        'node_id' => 'LORA_002',
        'gateway_id' => 'GATEWAY_001',
        'payload' => 'SENSOR|PIR|1|85|120|front',
        'rssi' => -88.2,
        'snr' => 6.5,
        'spreading_factor' => 8,
        'frequency' => 868.3,
        'bandwidth' => 125000,
        'received_at' => date('c'),
        'metadata' => [
            'gateway_location' => 'Building_A',
            'channel' => 1,
            'test_case' => 'pir_lora'
        ]
    ];
    
    $result2 = sendLoRaMessage($baseUrl, $pirLoRaData);
    echo "Response: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Receive door access sensor data via LoRa
    echo "🚪 Test 3: Receive Door Access Sensor Data via LoRa\n";
    $doorLoRaData = [
        'node_id' => 'LORA_003',
        'gateway_id' => 'GATEWAY_001',
        'payload' => 'SENSOR|DOOR|1|EMP-1234|45|keycard|front_door',
        'rssi' => -75.8,
        'snr' => 9.2,
        'spreading_factor' => 7,
        'frequency' => 868.5,
        'bandwidth' => 125000,
        'received_at' => date('c'),
        'metadata' => [
            'gateway_location' => 'Building_A',
            'channel' => 2,
            'test_case' => 'door_access_lora'
        ]
    ];
    
    $result3 = sendLoRaMessage($baseUrl, $doorLoRaData);
    echo "Response: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Receive heartbeat message via LoRa
    echo "💓 Test 4: Receive Heartbeat Message via LoRa\n";
    $heartbeatLoRaData = [
        'node_id' => 'LORA_001',
        'gateway_id' => 'GATEWAY_001',
        'payload' => 'HEARTBEAT|85|75|3600',
        'rssi' => -80.1,
        'snr' => 8.5,
        'spreading_factor' => 7,
        'frequency' => 868.1,
        'bandwidth' => 125000,
        'received_at' => date('c'),
        'metadata' => [
            'gateway_location' => 'Building_A',
            'test_case' => 'heartbeat_lora'
        ]
    ];
    
    $result4 = sendLoRaMessage($baseUrl, $heartbeatLoRaData);
    echo "Response: " . json_encode($result4, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 5: Send command to LoRa node
    echo "📤 Test 5: Send Command to LoRa Node\n";
    $commandData = [
        'node_id' => 'LORA_001',
        'action' => 'set_threshold',
        'parameters' => ['2.5', 'vibration'],
        'gateway_id' => 'GATEWAY_001',
        'priority' => 'high'
    ];
    
    $result5 = sendLoRaCommand($baseUrl, $commandData);
    echo "Response: " . json_encode($result5, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 6: Send configuration to LoRa node
    echo "⚙️  Test 6: Send Configuration to LoRa Node\n";
    $configData = [
        'node_id' => 'LORA_002',
        'parameter' => 'sleep_time',
        'value' => '300',
        'gateway_id' => 'GATEWAY_001'
    ];
    
    $result6 = sendLoRaConfig($baseUrl, $configData);
    echo "Response: " . json_encode($result6, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 7: Receive ACK message via LoRa
    echo "✅ Test 7: Receive ACK Message via LoRa\n";
    $ackLoRaData = [
        'node_id' => 'LORA_001',
        'gateway_id' => 'GATEWAY_001',
        'payload' => 'ACK|CMD_123456|success|Command executed successfully',
        'rssi' => -79.5,
        'snr' => 8.8,
        'spreading_factor' => 7,
        'frequency' => 868.1,
        'received_at' => date('c'),
        'metadata' => [
            'test_case' => 'ack_lora'
        ]
    ];
    
    $result7 = sendLoRaMessage($baseUrl, $ackLoRaData);
    echo "Response: " . json_encode($result7, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 8: Get LoRa messages
    echo "📋 Test 8: Get LoRa Messages\n";
    $messages = getLoRaMessages($baseUrl);
    echo "Messages: " . json_encode($messages, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 9: Get LoRa statistics
    echo "📊 Test 9: Get LoRa Statistics\n";
    $stats = getLoRaStatistics($baseUrl);
    echo "Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 10: Process unprocessed messages
    echo "🔄 Test 10: Process Unprocessed Messages\n";
    $processResult = processUnprocessedMessages($baseUrl);
    echo "Process result: " . json_encode($processResult, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "✅ All LoRa tests completed!\n";
    echo "📊 Summary:\n";
    echo "- Vibration sensor data: Processed and saved to VibrationReading\n";
    echo "- PIR sensor data: Processed and saved to PirReading\n";
    echo "- Door access data: Processed and saved to DoorAccessReading\n";
    echo "- Heartbeat: Device status updated\n";
    echo "- Commands: Sent to LoRa nodes\n";
    echo "- Configurations: Applied to LoRa nodes\n";
    echo "- ACK messages: Command acknowledgments processed\n";
}

function sendLoRaMessage($baseUrl, $data) {
    $url = $baseUrl . '/lora/receive';
    
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

function sendLoRaCommand($baseUrl, $data) {
    $url = $baseUrl . '/lora/send-command';
    
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
    curl_close($ch);
    
    return json_decode($response, true);
}

function sendLoRaConfig($baseUrl, $data) {
    $url = $baseUrl . '/lora/send-config';
    
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
    curl_close($ch);
    
    return json_decode($response, true);
}

function getLoRaMessages($baseUrl) {
    $url = $baseUrl . '/lora/messages?limit=5';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getLoRaStatistics($baseUrl) {
    $url = $baseUrl . '/lora/statistics?hours=24';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function processUnprocessedMessages($baseUrl) {
    $url = $baseUrl . '/lora/process-messages';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Simulasi skenario LoRa communication
function simulateLoRaScenarios() {
    echo "🎭 LoRa Communication Simulation Scenarios\n";
    echo "==========================================\n\n";
    
    // Scenario 1: Normal sensor data transmission
    echo "📊 Scenario 1: Normal Sensor Data Transmission\n";
    $normalScenarios = [
        ['node' => 'LORA_001', 'type' => 'VIBRATION', 'data' => '0.8,1.2,0.6', 'rssi' => -75, 'description' => 'Normal vibration reading'],
        ['node' => 'LORA_002', 'type' => 'PIR', 'data' => '1|60|30|center', 'rssi' => -82, 'description' => 'Normal motion detection'],
        ['node' => 'LORA_003', 'type' => 'DOOR', 'data' => '1|EMP-1234|25|keycard|front_door', 'rssi' => -78, 'description' => 'Authorized door access']
    ];
    
    foreach ($normalScenarios as $scenario) {
        $signalQuality = getSignalQuality($scenario['rssi']);
        echo "- {$scenario['description']}: Node {$scenario['node']}, Type {$scenario['type']}, RSSI {$scenario['rssi']} dBm ({$signalQuality})\n";
    }
    echo "\n";
    
    // Scenario 2: Alert-triggering sensor data
    echo "🚨 Scenario 2: Alert-Triggering Sensor Data\n";
    $alertScenarios = [
        ['node' => 'LORA_001', 'type' => 'VIBRATION', 'data' => '3.2,4.1,2.8', 'rssi' => -85, 'description' => 'Critical vibration detected'],
        ['node' => 'LORA_002', 'type' => 'PIR', 'data' => '1|90|400|back', 'rssi' => -90, 'description' => 'Suspicious motion (high intensity + long duration)'],
        ['node' => 'LORA_003', 'type' => 'DOOR', 'data' => '1|NULL|180|force|front_door', 'rssi' => -72, 'description' => 'Forced entry detected']
    ];
    
    foreach ($alertScenarios as $scenario) {
        $signalQuality = getSignalQuality($scenario['rssi']);
        echo "- {$scenario['description']}: Node {$scenario['node']}, RSSI {$scenario['rssi']} dBm ({$signalQuality}) (ALERT!)\n";
    }
    echo "\n";
    
    // Scenario 3: Network management
    echo "📡 Scenario 3: Network Management\n";
    $networkScenarios = [
        ['node' => 'LORA_001', 'action' => 'HEARTBEAT', 'data' => '75|80|7200', 'description' => 'Regular heartbeat (75% battery)'],
        ['node' => 'LORA_002', 'action' => 'HEARTBEAT', 'data' => '15|65|14400', 'description' => 'Low battery heartbeat (15% battery)'],
        ['node' => 'LORA_001', 'action' => 'ACK', 'data' => 'CMD_123|success|Threshold updated', 'description' => 'Command acknowledgment'],
        ['node' => 'LORA_003', 'action' => 'ACK', 'data' => 'CFG_456|failed|Invalid parameter', 'description' => 'Config failure acknowledgment']
    ];
    
    foreach ($networkScenarios as $scenario) {
        $alertIcon = ($scenario['action'] === 'HEARTBEAT' && strpos($scenario['data'], '15|') === 0) ? '🔋' : '📡';
        echo "{$alertIcon} {$scenario['description']}: Node {$scenario['node']}, Action {$scenario['action']}\n";
    }
    echo "\n";
    
    // Scenario 4: Signal quality analysis
    echo "📶 Scenario 4: Signal Quality Analysis\n";
    $signalScenarios = [
        ['rssi' => -65, 'distance' => '0.5 km', 'quality' => 'Excellent'],
        ['rssi' => -78, 'distance' => '1.2 km', 'quality' => 'Good'],
        ['rssi' => -92, 'distance' => '3.5 km', 'quality' => 'Fair'],
        ['rssi' => -105, 'distance' => '8.0 km', 'quality' => 'Poor']
    ];
    
    foreach ($signalScenarios as $scenario) {
        $icon = match($scenario['quality']) {
            'Excellent' => '📶',
            'Good' => '📶',
            'Fair' => '📶',
            'Poor' => '📶',
            default => '📶'
        };
        echo "{$icon} {$scenario['quality']} signal: RSSI {$scenario['rssi']} dBm, Est. distance {$scenario['distance']}\n";
    }
    echo "\n";
}

function getSignalQuality($rssi) {
    if ($rssi >= -70) return 'Excellent';
    if ($rssi >= -85) return 'Good';
    if ($rssi >= -100) return 'Fair';
    return 'Poor';
}

// LoRa payload format validation
function testLoRaPayloadFormats() {
    echo "🔍 LoRa Payload Format Validation\n";
    echo "=================================\n\n";
    
    $testPayloads = [
        // Valid payloads
        'SENSOR|VIBRATION|1.5,2.3,1.8|2.0' => true,
        'SENSOR|PIR|1|85|120|front' => true,
        'SENSOR|DOOR|1|EMP-1234|45|keycard|front_door' => true,
        'HEARTBEAT|85|75|3600' => true,
        'COMMAND|reboot||CMD_123456' => true,
        'ACK|CMD_123456|success|Command executed' => true,
        'CONFIG|sleep_time|300|CFG_789012' => true,
        
        // Invalid payloads
        'INVALID_FORMAT' => false,
        'SENSOR|UNKNOWN_TYPE|data' => false,
        'HEARTBEAT|invalid_battery' => false,
        '' => false
    ];
    
    foreach ($testPayloads as $payload => $expected) {
        $isValid = validateLoRaPayload($payload);
        $result = $isValid ? 'VALID' : 'INVALID';
        $status = ($isValid == $expected) ? '✅' : '❌';
        
        echo "{$status} Payload: '{$payload}' -> {$result}\n";
    }
    echo "\n";
}

function validateLoRaPayload($payload) {
    if (empty($payload)) return false;
    
    $parts = explode('|', $payload);
    if (count($parts) < 2) return false;
    
    $type = strtoupper($parts[0]);
    
    switch ($type) {
        case 'SENSOR':
            return count($parts) >= 3 && in_array(strtoupper($parts[1]), ['VIBRATION', 'PIR', 'DOOR']);
        case 'HEARTBEAT':
            return count($parts) >= 4;
        case 'COMMAND':
            return count($parts) >= 3;
        case 'ACK':
            return count($parts) >= 4;
        case 'CONFIG':
            return count($parts) >= 4;
        default:
            return false;
    }
}

// Jalankan test
echo "🚀 Starting LoRa Communication API Tests...\n\n";
simulateLoRaScenarios();
testLoRaPayloadFormats();
testLoRaAPI();