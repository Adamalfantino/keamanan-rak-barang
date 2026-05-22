# 🔔 Smart Rack Vibration Monitoring API

Backend API untuk menerima data sensor getar dan mengirim notifikasi otomatis jika getaran tidak wajar.

## 📡 **API Endpoints**

### 1. **Kirim Data Sensor Getar**
```http
POST /api/vibration/data
Content-Type: application/json

{
    "device_id": 1,
    "x_axis": 0.5,
    "y_axis": 1.2,
    "z_axis": 0.8,
    "threshold": 2.0,
    "metadata": {
        "sensor_type": "accelerometer",
        "firmware_version": "1.2.3"
    }
}
```

**Response Success:**
```json
{
    "success": true,
    "message": "Vibration data received successfully",
    "data": {
        "id": 123,
        "magnitude": 1.56,
        "status": "normal",
        "is_abnormal": false,
        "alert_sent": false
    }
}
```

**Response Abnormal (Alert Triggered):**
```json
{
    "success": true,
    "message": "Vibration data received successfully", 
    "data": {
        "id": 124,
        "magnitude": 3.45,
        "status": "critical",
        "is_abnormal": true,
        "alert_sent": true
    }
}
```

### 2. **Ambil Data Vibration Terbaru**
```http
GET /api/vibration/readings?device_id=1&limit=50&status=critical
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 124,
            "device_id": 1,
            "x_axis": 2.1,
            "y_axis": 2.8,
            "z_axis": 1.9,
            "magnitude": 3.45,
            "is_abnormal": true,
            "threshold": 2.0,
            "status": "critical",
            "recorded_at": "2026-04-28T10:30:00Z",
            "device": {
                "id": 1,
                "name": "Rak Server A",
                "location": "Ruang Server"
            }
        }
    ],
    "count": 1
}
```

### 3. **Ambil Statistik Getaran**
```http
GET /api/vibration/statistics?device_id=1&hours=24
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_readings": 150,
        "normal_count": 120,
        "warning_count": 25,
        "critical_count": 5,
        "abnormal_percentage": 20.0,
        "avg_magnitude": 1.85,
        "max_magnitude": 4.2,
        "latest_reading": {
            "id": 124,
            "magnitude": 3.45,
            "status": "critical",
            "recorded_at": "2026-04-28T10:30:00Z"
        }
    },
    "period_hours": 24
}
```

### 4. **Test Notifikasi**
```http
POST /api/test-notification
```

## 🚨 **Sistem Alert & Notifikasi**

### **Status Getaran:**
- **normal**: magnitude ≤ threshold
- **warning**: threshold < magnitude ≤ threshold × 1.5  
- **critical**: magnitude > threshold × 1.5

### **Jenis Notifikasi:**
1. **📧 Email** - Ke admin sistem
2. **📱 SMS** - Untuk alert critical
3. **🔔 Push Notification** - Real-time ke mobile app
4. **🔗 Webhook** - Ke sistem monitoring eksternal
5. **📝 Log** - Audit trail lengkap

### **Contoh Notifikasi:**
```
🚨 ALERT: Getaran abnormal pada Rak Server A
Magnitude: 3.45 (Normal: <2.0)
Status: critical
Lokasi: Ruang Server
Waktu: 2026-04-28 10:30:00
Sumbu: X=2.1, Y=2.8, Z=1.9
```

## 🔧 **Konfigurasi Threshold**

### **Default Threshold:** 2.0
### **Custom Threshold per Device:**
```json
{
    "device_id": 1,
    "threshold": 1.5,  // Lebih sensitif
    "x_axis": 0.8,
    "y_axis": 1.2,
    "z_axis": 0.9
}
```

### **Threshold Recommendations:**
- **Server Rack**: 1.5 - 2.0 (sensitif)
- **Storage Rack**: 2.0 - 3.0 (normal)
- **Network Equipment**: 1.0 - 1.5 (sangat sensitif)

## 📊 **Database Schema**

### **vibration_readings table:**
```sql
- id (bigint, primary key)
- device_id (foreign key to devices)
- x_axis (float) - Getaran sumbu X
- y_axis (float) - Getaran sumbu Y  
- z_axis (float) - Getaran sumbu Z
- magnitude (float) - Total magnitude
- is_abnormal (boolean) - Status abnormal
- threshold (float) - Batas normal
- status (enum: normal, warning, critical)
- metadata (json) - Data tambahan
- recorded_at (timestamp) - Waktu pembacaan
- created_at, updated_at
```

## 🔌 **Integrasi IoT Device**

### **Arduino/ESP32 Example:**
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

void sendVibrationData(float x, float y, float z) {
    HTTPClient http;
    http.begin("http://your-server.com/api/vibration/data");
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<200> doc;
    doc["device_id"] = 1;
    doc["x_axis"] = x;
    doc["y_axis"] = y;
    doc["z_axis"] = z;
    doc["threshold"] = 2.0;
    
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
from datetime import datetime

def send_vibration_data(device_id, x, y, z, threshold=2.0):
    url = "http://your-server.com/api/vibration/data"
    
    data = {
        "device_id": device_id,
        "x_axis": x,
        "y_axis": y, 
        "z_axis": z,
        "threshold": threshold,
        "metadata": {
            "timestamp": datetime.now().isoformat(),
            "sensor_type": "MPU6050"
        }
    }
    
    response = requests.post(url, json=data)
    return response.json()

# Contoh penggunaan
result = send_vibration_data(1, 0.5, 1.2, 0.8)
print(f"Status: {result['data']['status']}")
print(f"Alert sent: {result['data']['alert_sent']}")
```

## 🧪 **Testing API**

### **Test dengan cURL:**
```bash
# Test normal vibration
curl -X POST http://localhost:8000/api/vibration/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "x_axis": 0.5,
    "y_axis": 0.8,
    "z_axis": 0.6,
    "threshold": 2.0
  }'

# Test abnormal vibration (akan trigger alert)
curl -X POST http://localhost:8000/api/vibration/data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "x_axis": 2.5,
    "y_axis": 3.2,
    "z_axis": 2.8,
    "threshold": 2.0
  }'

# Get statistics
curl "http://localhost:8000/api/vibration/statistics?device_id=1&hours=24"

# Test notification
curl -X POST http://localhost:8000/api/test-notification
```

## 📈 **Monitoring Dashboard**

### **Real-time Metrics:**
- Current vibration level
- Alert count (24h)
- Device status
- Threshold violations
- Historical trends

### **Alert Management:**
- View active alerts
- Mark alerts as resolved
- Configure notification settings
- Export alert reports

## 🔒 **Security & Best Practices**

### **API Security:**
- Rate limiting per device
- API key authentication
- Input validation & sanitization
- HTTPS only in production

### **Data Retention:**
- Keep raw data: 30 days
- Keep aggregated data: 1 year
- Auto-cleanup old records

### **Performance:**
- Batch insert for high-frequency data
- Database indexing on device_id + timestamp
- Caching for statistics queries

## 🚀 **Deployment**

### **Environment Variables:**
```env
# Notification settings
VIBRATION_WEBHOOK_URL=https://your-webhook.com/alerts
NOTIFICATION_EMAIL=admin@yourcompany.com
SMS_API_KEY=your-sms-api-key

# Alert thresholds
DEFAULT_VIBRATION_THRESHOLD=2.0
CRITICAL_MULTIPLIER=1.5
```

### **Cron Jobs:**
```bash
# Cleanup old data (daily)
0 2 * * * php artisan vibration:cleanup

# Generate daily reports
0 8 * * * php artisan vibration:daily-report
```

## 📞 **Support**

Jika ada pertanyaan atau masalah:
1. Check log files: `storage/logs/laravel.log`
2. Verify database connection
3. Test API endpoints dengan Postman
4. Check notification service logs

---

**Backend Smart Rack Vibration Monitoring siap digunakan!** 🎉

Sistem akan otomatis:
- ✅ Menerima data sensor getar
- ✅ Menghitung magnitude getaran  
- ✅ Mendeteksi getaran abnormal
- ✅ Mengirim notifikasi real-time
- ✅ Menyimpan data untuk analisis
- ✅ Menyediakan statistik dan laporan