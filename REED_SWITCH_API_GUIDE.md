# 🚪 Smart Rack Door Access (Reed Switch) API

Backend API untuk menerima data sensor Reed Switch yang mendeteksi buka tutup pintu dan mengirim notifikasi otomatis jika terdeteksi akses mencurigakan atau tidak sah.

## 📡 **API Endpoints**

### 1. **Kirim Data Sensor Reed Switch**
```http
POST /api/door-access/data
Content-Type: application/json

{
    "device_id": 1,
    "door_opened": true,
    "access_method": "keycard",
    "user_id_card": "EMP-1234",
    "duration_seconds": 45,
    "door_location": "front_door",
    "is_forced_entry": false,
    "door_opened_at": "2026-04-28T10:30:00Z",
    "door_closed_at": "2026-04-28T10:30:45Z",
    "metadata": {
        "sensor_type": "Reed_Switch_Magnetic",
        "firmware_version": "3.1.0"
    }
}
```

**Response Authorized (Jam Kerja + ID Card):**
```json
{
    "success": true,
    "message": "Door access data received successfully",
    "data": {
        "id": 127,
        "door_opened": true,
        "access_type": "authorized",
        "is_authorized": true,
        "is_suspicious": false,
        "alert_sent": false,
        "priority": "info",
        "recorded_at": "2026-04-28T10:30:00Z"
    }
}
```

**Response Unauthorized (Alert Triggered):**
```json
{
    "success": true,
    "message": "Door access data received successfully",
    "data": {
        "id": 128,
        "door_opened": true,
        "access_type": "forced_entry",
        "is_authorized": false,
        "is_suspicious": true,
        "alert_sent": true,
        "priority": "critical",
        "recorded_at": "2026-04-28T22:15:00Z"
    }
}
```

### 2. **Ambil Data Door Access Terbaru**
```http
GET /api/door-access/readings?device_id=1&limit=50&access_type=unauthorized&door_location=front_door&suspicious=true
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 128,
            "device_id": 1,
            "door_opened": true,
            "is_authorized_access": false,
            "access_type": "forced_entry",
            "access_method": "force",
            "user_id_card": null,
            "duration_seconds": 180,
            "is_suspicious": true,
            "door_location": "front_door",
            "is_forced_entry": true,
            "door_opened_at": "2026-04-28T22:15:00Z",
            "door_closed_at": "2026-04-28T22:18:00Z",
            "recorded_at": "2026-04-28T22:15:00Z",
            "device": {
                "id": 1,
                "name": "Reed Switch Pintu Utama",
                "location": "Ruang Server"
            }
        }
    ],
    "count": 1
}
```

### 3. **Ambil Statistik Door Access**
```http
GET /api/door-access/statistics?device_id=1&hours=24
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_access_attempts": 85,
        "door_opened_count": 78,
        "authorized_count": 65,
        "unauthorized_count": 13,
        "suspicious_count": 8,
        "forced_entry_count": 2,
        "authorized_percentage": 76.47,
        "suspicious_percentage": 9.41,
        "avg_duration": 42.5,
        "max_duration": 180,
        "latest_access": {
            "id": 128,
            "access_type": "forced_entry",
            "user_id_card": null,
            "recorded_at": "2026-04-28T22:15:00Z"
        },
        "access_types": {
            "authorized": 65,
            "unauthorized": 8,
            "after_hours": 5,
            "forced_entry": 2,
            "emergency": 3,
            "maintenance": 2
        },
        "door_locations": {
            "front_door": 45,
            "back_door": 20,
            "side_door": 15,
            "main_entrance": 5
        },
        "access_methods": {
            "keycard": 60,
            "manual": 15,
            "force": 2,
            "emergency": 3,
            "maintenance": 2,
            "unknown": 3
        }
    },
    "period_hours": 24
}
```

### 4. **Test Notifikasi Door Access**
```http
POST /api/test-door-access-notification
```

## 🚨 **Sistem Deteksi & Alert**

### **Tipe Akses:**
- **authorized**: Akses sah dengan ID card dalam jam kerja
- **unauthorized**: Akses tanpa ID card atau ID tidak valid
- **after_hours**: Akses di luar jam kerja (07:00-18:00)
- **forced_entry**: Paksa masuk (CRITICAL ALERT)
- **emergency**: Akses darurat (diizinkan kapan saja)
- **maintenance**: Akses maintenance dalam jam kerja

### **Kondisi Mencurigakan:**
1. **Forced Entry** - Paksa masuk tanpa ID card
2. **Akses luar jam kerja** tanpa ID card yang valid
3. **Durasi pintu terbuka terlalu lama** (>180 detik)
4. **Akses tanpa ID card** di jam kerja normal
5. **ID card tidak valid** (format bukan EMP-XXXX)

### **Jam Kerja (Authorized Time):**
- **Senin - Jumat**: 07:00 - 18:00
- **Sabtu - Minggu**: Semua akses dianggap after_hours

### **Lokasi Pintu:**
- **front_door**: Pintu depan (akses utama)
- **back_door**: Pintu belakang (akses service)
- **side_door**: Pintu samping (akses sekunder)
- **main_entrance**: Pintu masuk utama gedung

### **Metode Akses:**
- **keycard**: Menggunakan kartu akses (paling aman)
- **manual**: Buka manual dengan kunci
- **force**: Paksa masuk (trigger alert)
- **emergency**: Akses darurat
- **maintenance**: Akses maintenance
- **unknown**: Metode tidak diketahui

## 🔔 **Sistem Notifikasi**

### **Priority Level:**
- **critical**: Forced entry (paksa masuk)
- **high**: Unauthorized access, after hours tanpa ID
- **medium**: Emergency access, suspicious activity
- **low**: Maintenance access
- **info**: Authorized access normal

### **Jenis Notifikasi:**
1. **📧 Email** - Semua alert
2. **📱 SMS** - Priority CRITICAL & HIGH (forced entry, unauthorized)
3. **🔔 Push Notification** - Real-time ke security app
4. **🔗 Webhook** - Ke sistem access control eksternal
5. **📝 Security Log** - Audit trail lengkap dengan user tracking

### **Contoh Notifikasi:**
```
🚨 SECURITY BREACH: PAKSA MASUK pada Reed Switch Pintu Utama di front_door
Durasi: 180s. Waktu: 2026-04-28 22:15:00

🚨 DOOR ALERT: Akses unauthorized pada Reed Switch Pintu Utama di front_door tanpa ID
Metode: manual. Durasi: 45s. Waktu: 2026-04-28 22:30:00
```

## 📊 **Database Schema**

### **door_access_readings table:**
```sql
- id (bigint, primary key)
- device_id (foreign key to devices)
- door_opened (boolean) - Status pintu terbuka
- is_authorized_access (boolean) - Akses sah/tidak
- access_type (enum) - Tipe akses
- access_method (enum) - Metode akses
- user_id_card (string) - ID card pengguna
- duration_seconds (integer) - Durasi pintu terbuka
- is_suspicious (boolean) - Akses mencurigakan
- door_location (enum) - Lokasi pintu
- is_forced_entry (boolean) - Paksa masuk
- metadata (json) - Data tambahan sensor
- door_opened_at (timestamp) - Waktu pintu dibuka
- door_closed_at (timestamp) - Waktu pintu ditutup
- recorded_at (timestamp) - Waktu pembacaan
- created_at, updated_at
```

## 🔌 **Integrasi IoT Device**

### **Arduino/ESP32 Example:**
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <RFID.h>

int reedSwitchPin = 2;
int rfidReaderPin = 4;
bool doorState = false;
unsigned long doorOpenTime = 0;
String lastIdCard = "";

void setup() {
    pinMode(reedSwitchPin, INPUT_PULLUP);
    Serial.begin(115200);
    // WiFi setup...
    // RFID setup...
}

void loop() {
    bool currentDoorState = !digitalRead(reedSwitchPin); // Reed switch logic
    
    if (currentDoorState != doorState) {
        doorState = currentDoorState;
        
        if (doorState) {
            // Door opened
            doorOpenTime = millis();
            lastIdCard = readRFIDCard(); // Read ID card if available
            Serial.println("Door opened, ID: " + lastIdCard);
            
        } else {
            // Door closed
            unsigned long duration = (millis() - doorOpenTime) / 1000;
            
            sendDoorAccessData(true, lastIdCard, duration, "front_door");
            Serial.println("Door closed, data sent");
            
            lastIdCard = "";
        }
    }
    
    delay(100);
}

String readRFIDCard() {
    // Implementasi baca RFID card
    // Return format: "EMP-1234" atau "" jika tidak ada
    return "EMP-1234"; // Simulasi
}

void sendDoorAccessData(bool opened, String idCard, int duration, String location) {
    HTTPClient http;
    http.begin("http://your-server.com/api/door-access/data");
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<400> doc;
    doc["device_id"] = 1;
    doc["door_opened"] = opened;
    doc["access_method"] = idCard.length() > 0 ? "keycard" : "manual";
    doc["user_id_card"] = idCard.length() > 0 ? idCard : nullptr;
    doc["duration_seconds"] = duration;
    doc["door_location"] = location;
    doc["is_forced_entry"] = (idCard.length() == 0 && duration > 10);
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
    }
    
    http.end();
}
```

### **Python Example (Raspberry Pi):**
```python
import requests
import json
import time
from datetime import datetime
import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522

REED_SWITCH_PIN = 18
reader = SimpleMFRC522()

def setup_reed_switch():
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(REED_SWITCH_PIN, GPIO.IN, pull_up_down=GPIO.PUD_UP)

def read_rfid_card():
    try:
        id, text = reader.read_no_block()
        if id:
            return f"EMP-{id % 10000:04d}"  # Format ke EMP-XXXX
        return None
    except:
        return None

def send_door_access_data(device_id, door_opened, id_card, duration, location):
    url = "http://your-server.com/api/door-access/data"
    
    access_method = "keycard" if id_card else "manual"
    is_forced = not id_card and duration > 10
    
    data = {
        "device_id": device_id,
        "door_opened": door_opened,
        "access_method": access_method,
        "user_id_card": id_card,
        "duration_seconds": duration,
        "door_location": location,
        "is_forced_entry": is_forced,
        "metadata": {
            "timestamp": datetime.now().isoformat(),
            "sensor_type": "Reed_Switch_Magnetic",
            "gpio_pin": REED_SWITCH_PIN
        }
    }
    
    response = requests.post(url, json=data)
    return response.json()

def monitor_door_access():
    door_open = False
    door_open_time = None
    current_id_card = None
    
    while True:
        # Read reed switch (False = door closed, True = door open)
        door_state = not GPIO.input(REED_SWITCH_PIN)
        
        if door_state and not door_open:
            # Door just opened
            door_open = True
            door_open_time = time.time()
            current_id_card = read_rfid_card()
            print(f"Door opened, ID Card: {current_id_card}")
            
        elif not door_state and door_open:
            # Door just closed
            door_open = False
            duration = int(time.time() - door_open_time) if door_open_time else 0
            
            result = send_door_access_data(1, True, current_id_card, duration, "front_door")
            print(f"Door access data sent: {result}")
            
            current_id_card = None
            door_open_time = None
        
        time.sleep(0.1)

# Usage
setup_reed_switch()
monitor_door_access()
```

## 🧪 **Testing API**

### **Test dengan cURL:**
```bash
# Test authorized access (jam kerja + ID card)
curl -X POST http://localhost:8000/api/door-access/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "door_opened": true,
    "access_method": "keycard",
    "user_id_card": "EMP-1234",
    "duration_seconds": 30,
    "door_location": "front_door"
  }'

# Test unauthorized access (tanpa ID card)
curl -X POST http://localhost:8000/api/door-access/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "door_opened": true,
    "access_method": "manual",
    "duration_seconds": 60,
    "door_location": "back_door"
  }'

# Test forced entry (CRITICAL ALERT)
curl -X POST http://localhost:8000/api/door-access/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "door_opened": true,
    "access_method": "force",
    "duration_seconds": 180,
    "door_location": "front_door",
    "is_forced_entry": true
  }'

# Get statistics
curl "http://localhost:8000/api/door-access/statistics?device_id=1&hours=24"

# Test door access notification
curl -X POST http://localhost:8000/api/test-door-access-notification
```

## 📈 **Security Dashboard**

### **Real-time Monitoring:**
- Current door status (open/closed)
- Active access attempts
- Unauthorized access count (24h)
- Forced entry alerts
- User access patterns

### **Access Control Reports:**
- Daily access summary by user
- Unauthorized access attempts
- After-hours access log
- Door usage statistics by location
- ID card usage tracking

## 🔒 **Security Features**

### **Smart Access Control:**
- **ID Card Validation**: Format EMP-XXXX validation
- **Time-based Rules**: Working hours vs after hours
- **Duration Monitoring**: Alert untuk pintu terbuka terlalu lama
- **Forced Entry Detection**: Deteksi paksa masuk real-time
- **User Tracking**: Track akses per ID card

### **Multi-level Security:**
- **Immediate**: Push notification untuk forced entry
- **Escalation**: SMS untuk unauthorized access
- **Audit Trail**: Log lengkap semua akses
- **Integration**: Webhook ke sistem access control

## 🚀 **Deployment & Configuration**

### **Environment Variables:**
```env
# Door Access Settings
DOOR_ACCESS_WEBHOOK_URL=https://your-access-control.com/alerts
WORK_START_HOUR=7
WORK_END_HOUR=18
MAX_DOOR_OPEN_DURATION=180

# ID Card Settings
ID_CARD_FORMAT_REGEX=^EMP-\d{4}$
VALID_ID_CARDS=EMP-1234,EMP-5678,EMP-9012

# Notification settings
SECURITY_EMAIL=security@yourcompany.com
SECURITY_SMS_NUMBER=+6281234567890
```

### **Konfigurasi Access Control:**
```php
// config/door_access.php
return [
    'work_hours' => [
        'start' => 7,  // 07:00
        'end' => 18,   // 18:00
        'weekdays_only' => true
    ],
    'thresholds' => [
        'max_door_open_duration' => 180, // 3 minutes
        'suspicious_duration' => 120,    // 2 minutes
    ],
    'id_card' => [
        'format_regex' => '^EMP-\d{4}$',
        'required_for_access' => true
    ],
    'locations' => [
        'front_door' => ['priority' => 'high', 'require_id' => true],
        'back_door' => ['priority' => 'medium', 'require_id' => true],
        'side_door' => ['priority' => 'medium', 'require_id' => false],
        'main_entrance' => ['priority' => 'high', 'require_id' => true]
    ]
];
```

## 📞 **Troubleshooting**

### **Common Issues:**
1. **False alarms**: Adjust door open duration threshold
2. **ID card not detected**: Check RFID reader connection
3. **Missing alerts**: Verify notification service configuration
4. **Wrong access type**: Check working hours configuration

### **Monitoring Commands:**
```bash
# Check door access logs
tail -f storage/logs/laravel.log | grep "Door Access"

# Database cleanup
php artisan door-access:cleanup-old-data

# Test access control system
php artisan door-access:test-system
```

---

**Backend Smart Rack Door Access (Reed Switch) siap digunakan!** 🎉

Sistem akan otomatis:
- ✅ Menerima data sensor Reed Switch real-time
- ✅ Mendeteksi akses mencurigakan berdasarkan ID card, waktu, dan metode
- ✅ Membedakan akses sah vs tidak sah
- ✅ Mengirim notifikasi multi-channel untuk security breach
- ✅ Menyimpan audit trail lengkap untuk compliance
- ✅ Menyediakan statistik akses dan laporan keamanan
- ✅ Integrasi mudah dengan sistem access control dan CCTV
- ✅ Support multiple door locations dan access methods