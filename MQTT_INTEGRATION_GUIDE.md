# MQTT Integration Guide

## Arsitektur

```
ESP32 (Publisher)
    │
    │  MQTT Publish (broker.hivemq.com:1883)
    ▼
HiveMQ Broker
    │
    │  MQTT Subscribe → HTTP Forward
    ▼
MQTT Bridge (Node-RED / Python / MQTTx Webhook)
    │
    │  HTTP POST /api/mqtt/ingest
    ▼
Laravel Backend → Database
```

Karena PHP/Laravel tidak bisa subscribe MQTT secara native, kita butuh **MQTT Bridge** yang:
1. Subscribe ke topic di HiveMQ
2. Forward setiap pesan ke endpoint `/api/mqtt/ingest`

---

## Endpoint Backend

### 1. Terima data dari MQTT Bridge
```
POST /api/mqtt/ingest
Content-Type: application/json

{
  "topic": "keamanan/pir",
  "payload": "{\"device_id\":1,\"node\":\"NODE_001\",\"type\":\"PIR\",\"motion\":true}"
}
```

### 2. Terima batch data
```
POST /api/mqtt/ingest-batch
Content-Type: application/json

{
  "messages": [
    {"topic": "keamanan/pir", "payload": "{...}"},
    {"topic": "keamanan/vibration", "payload": "{...}"}
  ]
}
```

### 3. Publish command ke ESP32
```
POST /api/mqtt/command
Content-Type: application/json

{
  "node_id": "NODE_001",
  "command": "REBOOT",
  "params": {}
}
```

---

## Cara Setup MQTT Bridge

### Opsi A: Node-RED (Paling Mudah)

1. Install Node-RED: `npm install -g node-red`
2. Buka `http://localhost:1880`
3. Tambah node: **mqtt in** → **function** → **http request**

Node **mqtt in**:
- Server: `broker.hivemq.com:1883`
- Topic: `keamanan/#` (subscribe semua topic keamanan)

Node **function** (transform payload):
```javascript
msg.url = "https://keamanan-rak-barang-production.up.railway.app/api/mqtt/ingest";
msg.method = "POST";
msg.headers = { "Content-Type": "application/json" };
msg.payload = JSON.stringify({
    topic: msg.topic,
    payload: typeof msg.payload === 'string' ? msg.payload : JSON.stringify(msg.payload)
});
return msg;
```

Node **http request**:
- Method: POST
- URL: (dari msg.url)

---

### Opsi B: Python Script (Ringan, bisa di-deploy di server)

```python
# mqtt_bridge.py
import paho.mqtt.client as mqtt
import requests
import json

BACKEND_URL = "https://keamanan-rak-barang-production.up.railway.app/api/mqtt/ingest"
BROKER = "broker.hivemq.com"
PORT = 1883
TOPICS = [
    "keamanan/pir",
    "keamanan/reed",
    "keamanan/vibration",
    "keamanan/heartbeat",
]

def on_connect(client, userdata, flags, rc):
    print(f"Connected to MQTT broker, rc={rc}")
    for topic in TOPICS:
        client.subscribe(topic)
        print(f"Subscribed to: {topic}")

def on_message(client, userdata, msg):
    topic = msg.topic
    payload = msg.payload.decode("utf-8")
    print(f"[{topic}] {payload}")

    try:
        response = requests.post(BACKEND_URL, json={
            "topic": topic,
            "payload": payload
        }, timeout=10)
        print(f"  → Backend: {response.status_code}")
    except Exception as e:
        print(f"  → Error: {e}")

client = mqtt.Client(client_id=f"bridge-{__import__('random').randint(1000,9999)}")
client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER, PORT, 60)
client.loop_forever()
```

Install dependency: `pip install paho-mqtt requests`
Jalankan: `python mqtt_bridge.py`

---

### Opsi C: MQTTx CLI (Untuk Testing)

MQTTx bisa subscribe dan forward ke webhook. Atau gunakan untuk test manual:

```bash
# Subscribe dan lihat pesan
mqttx sub -h broker.hivemq.com -p 1883 -t "keamanan/#" -v

# Publish test (simulasi ESP32)
mqttx pub -h broker.hivemq.com -p 1883 \
  -t "keamanan/pir" \
  -m '{"device_id":1,"node":"NODE_001","type":"PIR","motion":true}'
```

---

## Format Payload dari ESP32

ESP32 mengirim dua format tergantung dari mana datanya:

### Format 1: buildPayload (MQTT publish dari ESP32)
```json
{
  "device_id": 1,
  "node": "NODE_001",
  "type": "PIR",
  "motion": true
}
```

### Format 2: Raw LoRa JSON (dikirim langsung via HTTP)
```json
{
  "node_id": "NODE_001",
  "gateway_id": "GATEWAY_001",
  "type": "PIR",
  "motion_detected": true,
  "device_id": 1
}
```

Backend mendukung **kedua format** ini secara otomatis.

---

## Topic Mapping

| Topic MQTT          | Tipe Sensor | Endpoint HTTP Alternatif |
|---------------------|-------------|--------------------------|
| `keamanan/pir`      | PIR         | `POST /api/pir/data`     |
| `keamanan/reed`     | Reed Switch | `POST /api/door-access/data` |
| `keamanan/vibration`| Vibration   | `POST /api/vibration/data` |
| `keamanan/heartbeat`| Heartbeat   | `POST /api/lora/receive` |

---

## Troubleshooting: Data Tidak Masuk Database

### Cek 1: Device ada di database?
```sql
SELECT id, name, device_id, is_active FROM devices;
```
Jika kosong, jalankan seeder:
```bash
php artisan db:seed --class=DeviceSeeder
```

### Cek 2: Test endpoint langsung
```bash
curl -X POST https://keamanan-rak-barang-production.up.railway.app/api/pir/data \
  -H "Content-Type: application/json" \
  -d '{"device_id":1,"motion_detected":true}'
```

### Cek 3: Test MQTT ingest
```bash
curl -X POST https://keamanan-rak-barang-production.up.railway.app/api/mqtt/ingest \
  -H "Content-Type: application/json" \
  -d '{"topic":"keamanan/pir","payload":"{\"device_id\":1,\"node\":\"NODE_001\",\"type\":\"PIR\",\"motion\":true}"}'
```

### Cek 4: Lihat Laravel logs
```bash
tail -f storage/logs/laravel.log
```
