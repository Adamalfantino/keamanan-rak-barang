# 👁️ Smart Rack PIR Motion Detection API

Backend API untuk menerima data sensor PIR (Passive Infrared) dan mengirim notifikasi otomatis jika terdeteksi gerakan tidak wajar atau mencurigakan.

## 📡 **API Endpoints**

### 1. **Kirim Data Sensor PIR**
```http
POST /api/pir/data
Content-Type: application/json

{
    "device_id": 1,
    "motion_detected": true,
    "motion_intensity": 75,
    "duration_seconds": 120,
    "detection_zone": "front",
    "motion_start": "2026-04-28T10:30:00Z",
    "motion_end": "2026-04-28T10:32:00Z",
    "metadata": {
        "sensor_type": "PIR_HC-SR501",
        "firmware_version": "2.1.0"
    }
}
```

**Response Normal (Jam Kerja):**
```json
{
    "success": true,
    "message": "PIR data received successfully",
    "data": {
        "id": 125,
        "motion_detected": true,
        "motion_type": "normal",
        "is_suspicious": false,
        "is_authorized_time": true,
        "alert_sent": false,
        "recorded_at": "2026-04-28T10:30:00Z"
    }
}
```

**Response Mencurigakan (Alert Triggered):**
```json
{
    "success": true,
    "message": "PIR data received successfully",
    "data": {
        "id": 126,
        "motion_detected": true,
        "motion_type": "unauthorized",
        "is_suspicious": true,
        "is_authorized_time": false,
        "alert_sent": true,
        "recorded_at": "2026-04-28T22:15:00Z"
    }
}
```

### 2. **Ambil Data PIR Terbaru**
```http
GET /api/pir/readings?device_id=1&limit=50&motion_type=suspicious&detection_zone=front
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 126,
            "device_id": 1,
            "motion_detected": true,
            "motion_intensity": 85,
            "duration_seconds": 180,
            "is_authorized_time": false,
            "is_suspicious": true,
            "motion_type": "unauthorized",
            "detection_zone": "front",
            "motion_start": "2026-04-28T22:15:00Z",
            "motion_end": "2026-04-28T22:18:00Z",
            "recorded_at": "2026-04-28T22:15:00Z",
            "device": {
                "id": 1,
                "name": "PIR Rak Server A",
                "location": "Ruang Server"
            }
        }
    ],
    "count": 1
}
```

### 3. **Ambil Statistik Gerakan**
```http
GET /api/pir/statistics?device_id=1&hours=24
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_readings": 200,
        "motion_detected_count": 45,
        "suspicious_count": 8,
        "unauthorized_count": 3,
        "normal_count": 34,
        "motion_percentage": 22.5,
        "suspicious_percentage": 4.0,
        "avg_intensity": 65.5,
        "avg_duration": 85.2,
        "max_intensity": 95,
        "max_duration": 300,
        "latest_motion": {
            "id": 126,
            "motion_type": "unauthorized",
            "intensity": 85,
            "recorded_at": "2026-04-28T22:15:00Z"
        },
        "detection_zones": {
            "front": 15,
            "back": 8,
            "side": 12,
            "center": 10
        }
    },
    "period_hours": 24
}
```

### 4. **Test Notifikasi PIR**
```http
POST /api/test-pir-notification
```

## 🚨 **Sistem Deteksi & Alert**

### **Tipe Gerakan:**
- **none**: Tidak ada gerakan terdeteksi
- **normal**: Gerakan normal dalam jam kerja
- **suspicious**: Gerakan mencurigakan (intensitas/durasi tinggi)
- **unauthorized**: Gerakan di luar jam kerja

### **Kondisi Mencurigakan:**
1. **Gerakan di luar jam kerja** (Senin-Jumat 08:00-17:00)
2. **Intensitas tinggi** (>80%)
3. **Durasi lama** (>300 detik)
4. **Zona sensitif** dengan aktivitas tidak normal

### **Jam Kerja (Authorized Time):**
- **Senin - Jumat**: 08:00 - 17:00
- **Sabtu - Minggu**: Tidak ada jam kerja (semua gerakan dianggap unauthorized)

### **Zona Deteksi:**
- **front**: Depan rak (akses utama)
- **back**: Belakang rak (area maintenance)
- **side**: Samping rak (area sekunder)
- **center**: Tengah ruangan (area umum)

## 🔔 **Sistem Notifikasi**

### **Priority Level:**
- **high**: Gerakan unauthorized (di luar jam kerja)
- **medium**: Gerakan suspicious (dalam jam kerja tapi mencurigakan)
- **low**: Gerakan normal dengan intensitas tinggi

### **Jenis Notifikasi:**
1. **📧 Email** - Semua alert
2. **📱 SMS** - Hanya priority HIGH (unauthorized)
3. **🔔 Push Notification** - Real-time ke security app
4. **🔗 Webhook** - Ke sistem CCTV/security eksternal
5. **📝 Security Log** - Audit trail lengkap

### **Contoh Notifikasi:**
```
🚨 SECURITY ALERT: Gerakan unauthorized pada PIR Rak Server A zona front (luar jam kerja)
Intensitas: 85%. Durasi: 180s
Waktu: 2026-04-28 22:15:00
```

## 📊 **Database Schema**

### **pir_readings table:**
```sql
- id (bigint, primary key)
- device_id (foreign key to devices)
- motion_detected (boolean) - Gerakan terdeteksi
- motion_intensity (integer 0-100) - Intensitas gerakan
- duration_seconds (integer) - Durasi gerakan
- is_authorized_time (boolean) - Dalam jam kerja
- is_suspicious (boolean) - Gerakan mencurigakan
- motion_type (enum: none, normal, suspicious, unauthorized)
- detection_zone (enum: front, back, side, center)
- metadata (json) - Data tambahan sensor
- motion_start (timestamp) - Waktu mulai gerakan
- motion_end (timestamp) - Waktu selesai gerakan
- recorded_at (timestamp) - Waktu pembacaan
- created_at, updated_at
```

## 🔌 **Integrasi IoT Device**

### **Arduino/ESP32 Example:**
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

int pirPin = 2;
int motionStartTime = 0;
bool motionActive = false;

void setup() {
    pinMode(pirPin, INPUT);
    Serial.begin(115200);
    // WiFi setup...
}

void loop() {
    int pirState = digitalRead(pirPin);
    
    if (pirState == HIGH && !motionActive) {
        // Motion started
        motionActive = true;
        motionStartTime = millis();
        Serial.println("Motion detected!");
        
    } else if (pirState == LOW && motionActive) {
        // Motion ended
        motionActive = false;
        int duration = (millis() - motionStartTime) / 1000;
        
        sendPirData(true, 75, duration, "front");
        Serial.println("Motion ended, data sent");
    }
    
    delay(100);
}

void sendPirData(bool detected, int intensity, int duration, String zone) {
    HTTPClient http;
    http.begin("http://your-server.com/api/pir/data");
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<300> doc;
    doc["device_id"] = 1;
    doc["motion_detected"] = detected;
    doc["motion_intensity"] = intensity;
    doc["duration_seconds"] = duration;
    doc["detection_zone"] = zone;
    
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

### **Python Example:**
```python
import requests
import json
import time
from datetime import datetime
import RPi.GPIO as GPIO

PIR_PIN = 18

def setup_pir():
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(PIR_PIN, GPIO.IN)

def send_pir_data(device_id, motion_detected, intensity, duration, zone):
    url = "http://your-server.com/api/pir/data"
    
    data = {
        "device_id": device_id,
        "motion_detected": motion_detected,
        "motion_intensity": intensity,
        "duration_seconds": duration,
        "detection_zone": zone,
        "metadata": {
            "timestamp": datetime.now().isoformat(),
            "sensor_type": "PIR_HC-SR501",
            "gpio_pin": PIR_PIN
        }
    }
    
    response = requests.post(url, json=data)
    return response.json()

def monitor_motion():
    motion_start = None
    
    while True:
        if GPIO.input(PIR_PIN):
            if motion_start is None:
                motion_start = time.time()
                print("Motion detected!")
        else:
            if motion_start is not None:
                duration = int(time.time() - motion_start)
                intensity = min(100, duration * 2)  # Simple intensity calculation
                
                result = send_pir_data(1, True, intensity, duration, "front")
                print(f"Motion data sent: {result}")
                
                motion_start = None
        
        time.sleep(0.1)

# Usage
setup_pir()
monitor_motion()
```

## 🧪 **Testing API**

### **Test dengan cURL:**
```bash
# Test normal motion (jam kerja)
curl -X POST http://localhost:8000/api/pir/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "motion_detected": true,
    "motion_intensity": 60,
    "duration_seconds": 45,
    "detection_zone": "front"
  }'

# Test suspicious motion (intensitas tinggi)
curl -X POST http://localhost:8000/api/pir/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "motion_detected": true,
    "motion_intensity": 90,
    "duration_seconds": 350,
    "detection_zone": "back"
  }'

# Test unauthorized motion (simulasi luar jam kerja)
curl -X POST http://localhost:8000/api/pir/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "motion_detected": true,
    "motion_intensity": 75,
    "duration_seconds": 120,
    "detection_zone": "front",
    "metadata": {
      "simulated_time": "2026-04-28T22:30:00Z"
    }
  }'

# Get statistics
curl "http://localhost:8000/api/pir/statistics?device_id=1&hours=24"

# Test PIR notification
curl -X POST http://localhost:8000/api/test-pir-notification
```

## 📈 **Security Dashboard**

### **Real-time Monitoring:**
- Current motion status per zone
- Alert count by priority (24h)
- Unauthorized access attempts
- Motion patterns analysis
- Device status monitoring

### **Security Reports:**
- Daily motion summary
- Unauthorized access log
- Peak activity hours
- Zone-based statistics
- Alert response times

## 🔒 **Security Features**

### **Smart Detection:**
- **Time-based analysis**: Jam kerja vs luar jam kerja
- **Pattern recognition**: Gerakan berulang mencurigakan
- **Zone-based rules**: Aturan berbeda per zona
- **Intensity thresholds**: Deteksi gerakan abnormal

### **Multi-level Alerts:**
- **Immediate**: Push notification real-time
- **Escalation**: SMS untuk alert critical
- **Logging**: Audit trail lengkap
- **Integration**: Webhook ke sistem CCTV

## 🚀 **Deployment & Configuration**

### **Environment Variables:**
```env
# PIR Settings
PIR_WEBHOOK_URL=https://your-cctv-system.com/alerts
WORK_START_HOUR=8
WORK_END_HOUR=17
SUSPICIOUS_INTENSITY_THRESHOLD=80
SUSPICIOUS_DURATION_THRESHOLD=300

# Notification settings
SECURITY_EMAIL=security@yourcompany.com
SECURITY_SMS_NUMBER=+6281234567890
```

### **Konfigurasi Jam Kerja:**
```php
// config/pir.php
return [
    'work_hours' => [
        'start' => 8,  // 08:00
        'end' => 17,   // 17:00
        'weekdays_only' => true
    ],
    'thresholds' => [
        'suspicious_intensity' => 80,
        'suspicious_duration' => 300,
        'high_intensity' => 90
    ],
    'zones' => [
        'front' => ['priority' => 'high'],
        'back' => ['priority' => 'medium'],
        'side' => ['priority' => 'medium'],
        'center' => ['priority' => 'low']
    ]
];
```

## 📞 **Troubleshooting**

### **Common Issues:**
1. **False positives**: Adjust intensity threshold
2. **Missing alerts**: Check notification service logs
3. **Wrong time detection**: Verify server timezone
4. **High CPU usage**: Implement data cleanup job

### **Monitoring Commands:**
```bash
# Check PIR logs
tail -f storage/logs/laravel.log | grep PIR

# Database cleanup
php artisan pir:cleanup-old-data

# Test notification system
php artisan pir:test-notifications
```

---

**Backend Smart Rack PIR Motion Detection siap digunakan!** 🎉

Sistem akan otomatis:
- ✅ Menerima data sensor PIR real-time
- ✅ Mendeteksi gerakan mencurigakan berdasarkan waktu, intensitas, dan durasi
- ✅ Membedakan jam kerja vs luar jam kerja
- ✅ Mengirim notifikasi multi-channel (email, SMS, push, webhook)
- ✅ Menyimpan audit trail lengkap untuk analisis keamanan
- ✅ Menyediakan statistik dan laporan keamanan
- ✅ Integrasi mudah dengan sistem CCTV dan security lainnya