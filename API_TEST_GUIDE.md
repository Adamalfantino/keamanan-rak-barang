# Panduan Test API - Smart Rack Security

## Base URL
```
http://localhost:8000/api
```

---

## 1. Test Sensor PIR

### Kirim data PIR (gerakan terdeteksi)
```
POST /api/pir/data
Content-Type: application/json

{
    "device_id": 1,
    "motion_detected": true,
    "motion_intensity": 85,
    "duration_seconds": 10,
    "detection_zone": "center"
}
```

### Kirim data PIR (tidak ada gerakan)
```
POST /api/pir/data
Content-Type: application/json

{
    "device_id": 1,
    "motion_detected": false,
    "motion_intensity": 0,
    "duration_seconds": 0,
    "detection_zone": "center"
}
```

### Ambil data PIR terbaru
```
GET /api/pir/readings?limit=10
```

---

## 2. Test Sensor Getaran (SW-420)

### Kirim data getaran (abnormal)
```
POST /api/vibration/data
Content-Type: application/json

{
    "device_id": 1,
    "x_axis": 3.5,
    "y_axis": 2.8,
    "z_axis": 4.1,
    "threshold": 2.0
}
```

### Kirim data getaran (normal)
```
POST /api/vibration/data
Content-Type: application/json

{
    "device_id": 1,
    "x_axis": 0.1,
    "y_axis": 0.2,
    "z_axis": 0.1,
    "threshold": 2.0
}
```

### Ambil data getaran terbaru
```
GET /api/vibration/readings?limit=10
```

---

## 3. Test Reed Switch (Door Access)

### Kirim data pintu terbuka
```
POST /api/door-access/data
Content-Type: application/json

{
    "device_id": 1,
    "door_opened": true,
    "access_method": "manual",
    "duration_seconds": 5,
    "door_location": "main_entrance",
    "is_forced_entry": false
}
```

### Kirim data pintu tertutup
```
POST /api/door-access/data
Content-Type: application/json

{
    "device_id": 1,
    "door_opened": false,
    "access_method": "manual",
    "duration_seconds": 0,
    "door_location": "main_entrance",
    "is_forced_entry": false
}
```

### Ambil data door access terbaru
```
GET /api/door-access/readings?limit=10
```

---

## 4. Test LoRa Gateway

### Kirim data LoRa dari gateway
```
POST /api/lora/receive
Content-Type: application/json

{
    "node_id": "LORA_001",
    "gateway_id": "GATEWAY_001",
    "payload": "SENSOR|PIR|1|85|10|center",
    "rssi": -75,
    "snr": 8.5
}
```

### Kirim heartbeat
```
POST /api/lora/receive
Content-Type: application/json

{
    "node_id": "LORA_001",
    "gateway_id": "GATEWAY_001",
    "payload": "HEARTBEAT|85|-75|3600",
    "rssi": -75,
    "snr": 8.5
}
```

---

## Cara Test Menggunakan Postman

1. Buka Postman
2. Buat request baru
3. Pilih method POST
4. Masukkan URL: `http://localhost:8000/api/pir/data`
5. Pilih tab Body → raw → JSON
6. Paste JSON di atas
7. Klik Send
8. Cek response — harus `"success": true`

## Cara Test Menggunakan Browser (GET only)

Buka langsung di browser:
- http://localhost:8000/api/pir/readings
- http://localhost:8000/api/vibration/readings
- http://localhost:8000/api/door-access/readings
