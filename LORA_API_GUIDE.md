# 📡 Smart Rack LoRa Communication API

Backend API untuk komunikasi LoRa (Long Range) yang dapat menerima dan mengirim data dari/ke sensor-sensor IoT dengan jangkauan jauh dan konsumsi daya rendah.

## 📡 **API Endpoints**

### 1. **Terima Message LoRa dari Gateway**
```http
POST /api/lora/receive
Content-Type: application/json

{
    "node_id": "LORA_001",
    "gateway_id": "GATEWAY_001",
    "payload": "SENSOR|VIBRATION|1.5,2.3,1.8|2.0",
    "rssi": -85.5,
    "snr": 8.2,
    "spreading_factor": 7,
    "frequency": 868.1,
    "bandwidth": 125000,
    "received_at": "2026-04-28T10:30:00Z",
    "metadata": {
        "gateway_location": "Building_A",
        "channel": 0
    }
}
```

**Response Success:**
```json
{
    "success": true,
    "message": "LoRa message received successfully",
    "data": {
        "message_id": 129,
        "node_id": "LORA_001",
        "message_type": "sensor_data",
        "signal_quality": "good",
        "estimated_distance": 2.5,
        "processing_result": {
            "success": true,
            "action": "vibration_data_saved",
            "vibration_reading_id": 45,
            "magnitude": 3.21,
            "status": "warning",
            "is_abnormal": true
        },
        "received_at": "2026-04-28T10:30:00Z"
    }
}
```

### 2. **Kirim Command ke LoRa Node**
```http
POST /api/lora/send-command
Content-Type: application/json

{
    "node_id": "LORA_001",
    "action": "set_threshold",
    "parameters": ["2.5", "vibration"],
    "gateway_id": "GATEWAY_001",
    "priority": "high"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Command sent to LoRa node",
    "data": {
        "message_id": 130,
        "command_id": "CMD_507f1f77bcf86cd799439011",
        "node_id": "LORA_001",
        "action": "set_threshold",
        "payload": "COMMAND|set_threshold|2.5,vibration|CMD_507f1f77bcf86cd799439011",
        "transmission_result": {
            "success": true,
            "simulated": true
        },
        "transmitted_at": "2026-04-28T10:35:00Z"
    }
}
```

### 3. **Kirim Konfigurasi ke LoRa Node**
```http
POST /api/lora/send-config
Content-Type: application/json

{
    "node_id": "LORA_001",
    "parameter": "sleep_time",
    "value": "300",
    "gateway_id": "GATEWAY_001"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Configuration sent to LoRa node",
    "data": {
        "message_id": 131,
        "config_id": "CFG_507f1f77bcf86cd799439012",
        "node_id": "LORA_001",
        "parameter": "sleep_time",
        "value": "300",
        "payload": "CONFIG|sleep_time|300|CFG_507f1f77bcf86cd799439012",
        "transmission_result": {
            "success": true,
            "simulated": true
        },
        "transmitted_at": "2026-04-28T10:40:00Z"
    }
}
```

### 4. **Ambil Messages LoRa Terbaru**
```http
GET /api/lora/messages?node_id=LORA_001&direction=inbound&message_type=sensor_data&limit=50
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 129,
            "node_id": "LORA_001",
            "gateway_id": "GATEWAY_001",
            "direction": "inbound",
            "message_type": "sensor_data",
            "payload": "SENSOR|VIBRATION|1.5,2.3,1.8|2.0",
            "parsed_data": {
                "sensor_type": "VIBRATION",
                "x_axis": 1.5,
                "y_axis": 2.3,
                "z_axis": 1.8,
                "threshold": 2.0
            },
            "rssi": -85.5,
            "snr": 8.2,
            "spreading_factor": 7,
            "frequency": 868.1,
            "signal_quality": "good",
            "estimated_distance": 2.5,
            "is_processed": true,
            "status": "processed",
            "received_at": "2026-04-28T10:30:00Z",
            "device": {
                "id": 1,
                "name": "LoRa Vibration Sensor A",
                "location": "Ruang Server"
            }
        }
    ],
    "count": 1
}
```

### 5. **Ambil Statistik LoRa Communication**
```http
GET /api/lora/statistics?node_id=LORA_001&hours=24
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_messages": 150,
        "inbound_messages": 120,
        "outbound_messages": 30,
        "processed_messages": 145,
        "failed_messages": 5,
        "acknowledged_messages": 25,
        "success_rate": 96.67,
        "avg_rssi": -82.3,
        "avg_snr": 7.8,
        "message_types": {
            "sensor_data": 100,
            "heartbeat": 20,
            "command": 25,
            "ack": 3,
            "config": 2
        },
        "node_statistics": {
            "LORA_001": {
                "message_count": 85,
                "avg_rssi": -80.5,
                "last_seen": "2026-04-28T10:30:00Z"
            },
            "LORA_002": {
                "message_count": 65,
                "avg_rssi": -88.2,
                "last_seen": "2026-04-28T10:25:00Z"
            }
        },
        "signal_quality_distribution": {
            "excellent": 15,
            "good": 45,
            "fair": 60,
            "poor": 25,
            "unknown": 5
        },
        "latest_message": {
            "id": 129,
            "node_id": "LORA_001",
            "message_type": "sensor_data",
            "received_at": "2026-04-28T10:30:00Z"
        }
    },
    "period_hours": 24
}
```

### 6. **Process Unprocessed Messages**
```http
POST /api/lora/process-messages
```

## 📊 **LoRa Message Format**

### **Sensor Data Payload Format:**
```
SENSOR|{SENSOR_TYPE}|{DATA}|{ADDITIONAL_PARAMS}
```

**Contoh:**
- **Vibration**: `SENSOR|VIBRATION|1.5,2.3,1.8|2.0`
- **PIR**: `SENSOR|PIR|1|85|120|front`
- **Door**: `SENSOR|DOOR|1|EMP-1234|45|keycard|front_door`

### **Command Payload Format:**
```
COMMAND|{ACTION}|{PARAMETERS}|{COMMAND_ID}
```

**Contoh:**
- `COMMAND|reboot||CMD_123456`
- `COMMAND|set_threshold|2.5,vibration|CMD_789012`
- `COMMAND|read_sensors||CMD_345678`

### **Config Payload Format:**
```
CONFIG|{PARAMETER}|{VALUE}|{CONFIG_ID}
```

**Contoh:**
- `CONFIG|sleep_time|300|CFG_123456`
- `CONFIG|tx_power|14|CFG_789012`
- `CONFIG|threshold|2.0|CFG_345678`

### **Heartbeat Payload Format:**
```
HEARTBEAT|{BATTERY_LEVEL}|{SIGNAL_STRENGTH}|{UPTIME}
```

**Contoh:**
- `HEARTBEAT|85|75|3600`

### **ACK Payload Format:**
```
ACK|{COMMAND_ID}|{STATUS}|{MESSAGE}
```

**Contoh:**
- `ACK|CMD_123456|success|Command executed`
- `ACK|CFG_789012|failed|Invalid parameter`

## 🔧 **LoRa Parameters**

### **Signal Quality Levels:**
- **Excellent**: RSSI ≥ -70 dBm
- **Good**: -85 dBm ≤ RSSI < -70 dBm
- **Fair**: -100 dBm ≤ RSSI < -85 dBm
- **Poor**: RSSI < -100 dBm

### **Spreading Factor (SF):**
- **SF7**: Fastest data rate, shortest range
- **SF8-SF10**: Balanced speed and range
- **SF11-SF12**: Slowest data rate, longest range

### **Frequency Bands:**
- **EU868**: 868.1 - 868.5 MHz (Europe)
- **US915**: 902 - 928 MHz (North America)
- **AS923**: 923 MHz (Asia)

### **Bandwidth Options:**
- **125 kHz**: Standard bandwidth
- **250 kHz**: Higher data rate
- **500 kHz**: Highest data rate

## 📡 **Database Schema**

### **lo_ra_messages table:**
```sql
- id (bigint, primary key)
- device_id (foreign key to devices, nullable)
- node_id (string) - LoRa node identifier
- gateway_id (string) - LoRa gateway identifier
- direction (enum: inbound, outbound)
- message_type (enum: sensor_data, command, heartbeat, ack, config)
- payload (text) - Raw LoRa message
- parsed_data (json) - Parsed sensor data
- rssi (float) - Signal strength
- snr (float) - Signal-to-noise ratio
- spreading_factor (integer) - LoRa SF (7-12)
- frequency (float) - Frequency in MHz
- bandwidth (integer) - Bandwidth in Hz
- is_processed (boolean) - Processing status
- is_acknowledged (boolean) - ACK status
- status (string) - Message status
- error_message (text) - Error details
- metadata (json) - Additional parameters
- transmitted_at (timestamp) - Transmission time
- received_at (timestamp) - Reception time
- created_at, updated_at
```

## 🔌 **Integrasi LoRa Device**

### **Arduino/ESP32 + LoRa Module Example:**
```cpp
#include <SPI.h>
#include <LoRa.h>
#include <ArduinoJson.h>

// LoRa pins
#define SS 18
#define RST 14
#define DIO0 26

String nodeId = "LORA_001";
float vibrationThreshold = 2.0;

void setup() {
    Serial.begin(115200);
    
    // Initialize LoRa
    LoRa.setPins(SS, RST, DIO0);
    if (!LoRa.begin(868E6)) {
        Serial.println("Starting LoRa failed!");
        while (1);
    }
    
    // LoRa configuration
    LoRa.setSpreadingFactor(7);
    LoRa.setSignalBandwidth(125E3);
    LoRa.setCodingRate4(5);
    LoRa.setTxPower(14);
    
    Serial.println("LoRa Sensor Node Started");
}

void loop() {
    // Read sensors
    float x = readAccelX();
    float y = readAccelY(); 
    float z = readAccelZ();
    
    // Send sensor data
    sendVibrationData(x, y, z, vibrationThreshold);
    
    // Check for incoming commands
    checkForCommands();
    
    // Send heartbeat every 5 minutes
    static unsigned long lastHeartbeat = 0;
    if (millis() - lastHeartbeat > 300000) {
        sendHeartbeat();
        lastHeartbeat = millis();
    }
    
    delay(10000); // Send data every 10 seconds
}

void sendVibrationData(float x, float y, float z, float threshold) {
    String payload = "SENSOR|VIBRATION|" + String(x) + "," + String(y) + "," + String(z) + "|" + String(threshold);
    
    LoRa.beginPacket();
    LoRa.print(payload);
    LoRa.endPacket();
    
    Serial.println("Sent: " + payload);
}

void sendHeartbeat() {
    int batteryLevel = readBatteryLevel();
    int signalStrength = LoRa.packetRssi();
    unsigned long uptime = millis() / 1000;
    
    String payload = "HEARTBEAT|" + String(batteryLevel) + "|" + String(signalStrength) + "|" + String(uptime);
    
    LoRa.beginPacket();
    LoRa.print(payload);
    LoRa.endPacket();
    
    Serial.println("Heartbeat sent: " + payload);
}

void checkForCommands() {
    int packetSize = LoRa.parsePacket();
    if (packetSize) {
        String command = "";
        while (LoRa.available()) {
            command += (char)LoRa.read();
        }
        
        Serial.println("Received command: " + command);
        processCommand(command);
    }
}

void processCommand(String command) {
    // Parse command: COMMAND|action|parameters|command_id
    int firstPipe = command.indexOf('|');
    int secondPipe = command.indexOf('|', firstPipe + 1);
    int thirdPipe = command.indexOf('|', secondPipe + 1);
    
    if (firstPipe == -1) return;
    
    String type = command.substring(0, firstPipe);
    String action = command.substring(firstPipe + 1, secondPipe);
    String parameters = command.substring(secondPipe + 1, thirdPipe);
    String commandId = command.substring(thirdPipe + 1);
    
    if (type == "COMMAND") {
        if (action == "reboot") {
            sendAck(commandId, "success", "Rebooting");
            ESP.restart();
        } else if (action == "set_threshold") {
            vibrationThreshold = parameters.toFloat();
            sendAck(commandId, "success", "Threshold updated");
        } else if (action == "read_sensors") {
            float x = readAccelX();
            float y = readAccelY();
            float z = readAccelZ();
            sendVibrationData(x, y, z, vibrationThreshold);
            sendAck(commandId, "success", "Sensors read");
        } else {
            sendAck(commandId, "failed", "Unknown command");
        }
    }
}

void sendAck(String commandId, String status, String message) {
    String payload = "ACK|" + commandId + "|" + status + "|" + message;
    
    LoRa.beginPacket();
    LoRa.print(payload);
    LoRa.endPacket();
    
    Serial.println("ACK sent: " + payload);
}

float readAccelX() { return random(-300, 300) / 100.0; }
float readAccelY() { return random(-300, 300) / 100.0; }
float readAccelZ() { return random(-300, 300) / 100.0; }
int readBatteryLevel() { return random(20, 100); }
```

### **Python LoRa Gateway Example:**
```python
import serial
import requests
import json
import time
from datetime import datetime

class LoRaGateway:
    def __init__(self, serial_port='/dev/ttyUSB0', api_url='http://localhost:8000/api/lora'):
        self.serial_port = serial_port
        self.api_url = api_url
        self.gateway_id = 'GATEWAY_001'
        
    def start_listening(self):
        try:
            ser = serial.Serial(self.serial_port, 115200, timeout=1)
            print(f"LoRa Gateway started on {self.serial_port}")
            
            while True:
                if ser.in_waiting > 0:
                    line = ser.readline().decode('utf-8').strip()
                    if line:
                        self.process_lora_message(line)
                        
                time.sleep(0.1)
                
        except Exception as e:
            print(f"Gateway error: {e}")
    
    def process_lora_message(self, raw_message):
        try:
            # Parse LoRa message format: NODE_ID:PAYLOAD:RSSI:SNR
            parts = raw_message.split(':')
            if len(parts) >= 4:
                node_id = parts[0]
                payload = parts[1]
                rssi = float(parts[2])
                snr = float(parts[3])
                
                # Send to backend API
                self.send_to_backend(node_id, payload, rssi, snr)
                
        except Exception as e:
            print(f"Message processing error: {e}")
    
    def send_to_backend(self, node_id, payload, rssi, snr):
        try:
            data = {
                'node_id': node_id,
                'gateway_id': self.gateway_id,
                'payload': payload,
                'rssi': rssi,
                'snr': snr,
                'spreading_factor': 7,
                'frequency': 868.1,
                'bandwidth': 125000,
                'received_at': datetime.now().isoformat(),
                'metadata': {
                    'gateway_location': 'Building_A',
                    'channel': 0
                }
            }
            
            response = requests.post(f"{self.api_url}/receive", json=data)
            
            if response.status_code == 201:
                print(f"Message sent to backend: {node_id} -> {payload}")
            else:
                print(f"Backend error: {response.status_code}")
                
        except Exception as e:
            print(f"Backend communication error: {e}")

# Usage
if __name__ == "__main__":
    gateway = LoRaGateway()
    gateway.start_listening()
```

## 🧪 **Testing API**

### **Test dengan cURL:**
```bash
# Test receive LoRa message (vibration sensor)
curl -X POST http://localhost:8000/api/lora/receive \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "LORA_001",
    "gateway_id": "GATEWAY_001",
    "payload": "SENSOR|VIBRATION|2.5,3.2,1.8|2.0",
    "rssi": -82.5,
    "snr": 7.8,
    "spreading_factor": 7,
    "frequency": 868.1
  }'

# Test receive LoRa message (PIR sensor)
curl -X POST http://localhost:8000/api/lora/receive \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "LORA_002",
    "payload": "SENSOR|PIR|1|85|120|front",
    "rssi": -88.2,
    "snr": 6.5
  }'

# Test receive LoRa message (door access)
curl -X POST http://localhost:8000/api/lora/receive \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "LORA_003",
    "payload": "SENSOR|DOOR|1|EMP-1234|45|keycard|front_door",
    "rssi": -75.8,
    "snr": 9.2
  }'

# Send command to LoRa node
curl -X POST http://localhost:8000/api/lora/send-command \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "LORA_001",
    "action": "set_threshold",
    "parameters": ["2.5", "vibration"],
    "priority": "high"
  }'

# Send config to LoRa node
curl -X POST http://localhost:8000/api/lora/send-config \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "LORA_001",
    "parameter": "sleep_time",
    "value": "300"
  }'

# Get LoRa messages
curl "http://localhost:8000/api/lora/messages?node_id=LORA_001&limit=10"

# Get LoRa statistics
curl "http://localhost:8000/api/lora/statistics?hours=24"
```

## 📈 **LoRa Dashboard Features**

### **Real-time Monitoring:**
- Active LoRa nodes status
- Signal quality per node
- Message throughput
- Battery levels
- Network coverage map

### **Communication Management:**
- Send commands to nodes
- Configure node parameters
- Monitor acknowledgments
- Retry failed transmissions

## 🔒 **Security & Best Practices**

### **LoRa Security:**
- **Payload Encryption**: Encrypt sensitive sensor data
- **Node Authentication**: Validate node IDs
- **Message Integrity**: Check message checksums
- **Replay Protection**: Prevent message replay attacks

### **Network Optimization:**
- **Adaptive Data Rate (ADR)**: Optimize SF and TX power
- **Duty Cycle Compliance**: Respect regional regulations
- **Gateway Load Balancing**: Distribute traffic across gateways
- **Message Prioritization**: Handle urgent messages first

## 🚀 **Deployment & Configuration**

### **Environment Variables:**
```env
# LoRa Gateway Settings
LORA_GATEWAY_URL=http://localhost:8080/lora/send
LORA_DEFAULT_FREQUENCY=868.1
LORA_DEFAULT_SF=7
LORA_DEFAULT_TX_POWER=14

# LoRa Network Settings
LORA_NETWORK_ID=1
LORA_APP_KEY=your_app_key_here
LORA_DEVICE_EUI_PREFIX=LORA_

# Processing Settings
LORA_AUTO_PROCESS=true
LORA_RETRY_FAILED_MESSAGES=true
LORA_MAX_RETRY_ATTEMPTS=3
```

### **Konfigurasi LoRa Network:**
```php
// config/lora.php
return [
    'gateway' => [
        'url' => env('LORA_GATEWAY_URL', 'http://localhost:8080/lora/send'),
        'timeout' => 10,
        'retry_attempts' => 3
    ],
    'network' => [
        'frequency' => env('LORA_DEFAULT_FREQUENCY', 868.1),
        'spreading_factor' => env('LORA_DEFAULT_SF', 7),
        'tx_power' => env('LORA_DEFAULT_TX_POWER', 14),
        'bandwidth' => 125000
    ],
    'processing' => [
        'auto_process' => env('LORA_AUTO_PROCESS', true),
        'batch_size' => 100,
        'retry_failed' => env('LORA_RETRY_FAILED_MESSAGES', true)
    ]
];
```

## 📞 **Troubleshooting**

### **Common Issues:**
1. **Poor signal quality**: Adjust SF or TX power
2. **Message loss**: Check gateway connectivity
3. **Processing delays**: Increase batch processing
4. **Node offline**: Check battery and coverage

### **Monitoring Commands:**
```bash
# Check LoRa logs
tail -f storage/logs/laravel.log | grep LoRa

# Process unprocessed messages
curl -X POST http://localhost:8000/api/lora/process-messages

# Check node statistics
curl "http://localhost:8000/api/lora/statistics?node_id=LORA_001"
```

---

**Backend Smart Rack LoRa Communication siap digunakan!** 🎉

Sistem akan otomatis:
- ✅ Menerima data sensor via LoRa dengan jangkauan jauh
- ✅ Parsing dan processing data sensor secara otomatis
- ✅ Mengirim command dan konfigurasi ke LoRa nodes
- ✅ Monitoring kualitas sinyal dan status nodes
- ✅ Integrasi seamless dengan sensor vibration, PIR, dan door access
- ✅ Menyediakan statistik dan analytics LoRa network
- ✅ Support multiple LoRa gateways dan nodes
- ✅ Battery monitoring dan low power management