/**
 * ============================================
 * Smart Rack Security System - ESP32
 * PIR + Reed Switch + SW420 + LoRa + Laravel API
 * ============================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <LoRa.h>

// ============================================
// WIFI
// ============================================
const char* ssid     = "gege";
const char* password = "biasaaja";

// GANTI DENGAN IP LARAVEL KAMU
const char* API_BASE = "http://10.185.214.166:8000/api";

// ============================================
// DEVICE
// ============================================
const int DEVICE_ID = 1;
const String NODE_ID = "LORA_001";

// ============================================
// PIN SENSOR
// ============================================
#define PIR_PIN      33
#define REED_PIN     32
#define SW420_PIN    34
#define BUZZER       25
#define LED_MERAH    13
#define LED_HIJAU    4

// ============================================
// PIN LORA SX1278 RA-02
// ============================================
#define LORA_SS      27
#define LORA_RST     14
#define LORA_DIO0    26

// ============================================
// VARIABEL
// ============================================
int pirState;
int reedState;
int vibrationState;
int lastVibration = LOW;
bool motionActive = false;
bool doorOpen     = false;
unsigned long motionStartTime = 0;
unsigned long doorOpenTime    = 0;
unsigned long lastHeartbeat   = 0;
const unsigned long HEARTBEAT_INTERVAL = 300000;

// ============================================
// LED
// ============================================
void setLED(bool bahaya) {
  digitalWrite(LED_MERAH, bahaya ? HIGH : LOW);
  digitalWrite(LED_HIJAU, bahaya ? LOW : HIGH);
}

// ============================================
// BUZZER
// ============================================
void buzzerPIR() {
  digitalWrite(BUZZER, HIGH);
  delay(300);
  digitalWrite(BUZZER, LOW);
}

void buzzerREED() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(100);
    digitalWrite(BUZZER, LOW);
    delay(100);
  }
}

void buzzerGETAR() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(80);
    digitalWrite(BUZZER, LOW);
    delay(80);
  }
}

void buzzerStartup() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(200);
    digitalWrite(BUZZER, LOW);
    delay(100);
  }
}

// ============================================
// WIFI
// ============================================
void setupWifi() {
  Serial.println("Menghubungkan WiFi: " + String(ssid));
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 30) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("");
    Serial.println("WiFi Connected!");
    Serial.println("IP: " + WiFi.localIP().toString());
  } else {
    Serial.println("");
    Serial.println("WiFi gagal terhubung. Cek SSID/Password.");
    Serial.println("Pastikan hotspot 2.4GHz dan sudah aktif.");
  }
}

void checkWifi() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi putus, reconnecting...");
    WiFi.disconnect();
    delay(1000);
    WiFi.begin(ssid, password);

    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 20) {
      delay(500);
      Serial.print(".");
      attempt++;
    }

    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\nWiFi terhubung kembali! IP: " + WiFi.localIP().toString());
    } else {
      Serial.println("\nGagal reconnect. Tunggu 10 detik...");
      delay(10000); // tunggu lebih lama sebelum coba lagi
    }
  }
}

// ============================================
// HTTP POST
// ============================================
bool httpPost(String endpoint, String jsonBody) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected, skip kirim data");
    return false;
  }

  HTTPClient http;
  String url = String(API_BASE) + endpoint;

  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(8000);

  int httpCode = http.POST(jsonBody);

  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("HTTP " + String(httpCode) + " → " + endpoint);
    if (httpCode == 201 || httpCode == 200) {
      Serial.println("✓ Data tersimpan");
      http.end();
      return true;
    } else {
      Serial.println("✗ Response: " + response.substring(0, 150));
    }
  } else {
    Serial.println("✗ HTTP Error: " + String(httpCode));
    Serial.println("  URL: " + url);
    Serial.println("  Cek: server jalan? IP benar? Firewall?");
  }

  http.end();
  return false;
}

// ============================================
// KIRIM PIR
// ============================================
void kirimDataPIR(bool detected, int intensity, int duration, String zone) {
  StaticJsonDocument<256> doc;
  doc["device_id"]        = DEVICE_ID;
  doc["motion_detected"]  = detected;
  doc["motion_intensity"] = intensity;
  doc["duration_seconds"] = duration;
  doc["detection_zone"]   = zone;

  String body;
  serializeJson(doc, body);
  Serial.println("Kirim PIR → intensity=" + String(intensity) + " duration=" + String(duration) + "s");
  httpPost("/pir/data", body);
}

// ============================================
// KIRIM GETARAN
// ============================================
void kirimDataGetaran(float x, float y, float z) {
  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["x_axis"]    = x;
  doc["y_axis"]    = y;
  doc["z_axis"]    = z;
  doc["threshold"] = 2.0;

  String body;
  serializeJson(doc, body);
  float mag = sqrt(x*x + y*y + z*z);
  Serial.println("Kirim Getaran → magnitude=" + String(mag, 2));
  httpPost("/vibration/data", body);
}

// ============================================
// KIRIM REED SWITCH
// ============================================
void kirimDataReedSwitch(bool opened, int duration) {
  StaticJsonDocument<256> doc;
  doc["device_id"]        = DEVICE_ID;
  doc["door_opened"]      = opened;
  doc["duration_seconds"] = duration;
  doc["access_method"]    = "manual";
  doc["door_location"]    = "rack";
  doc["is_forced_entry"]  = false;

  String body;
  serializeJson(doc, body);
  Serial.println("Kirim Reed Switch → opened=" + String(opened) + " duration=" + String(duration) + "s");
  httpPost("/door-access/data", body);
}

// ============================================
// HEARTBEAT
// ============================================
void kirimHeartbeat() {
  String payload = "HEARTBEAT|" + NODE_ID;
  LoRa.beginPacket();
  LoRa.print(payload);
  LoRa.endPacket();
  Serial.println("Heartbeat terkirim. Uptime: " + String(millis()/1000) + "s");
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  // SENSOR
  pinMode(PIR_PIN,   INPUT);
  pinMode(REED_PIN,  INPUT_PULLUP); // PULLUP untuk reed switch
  pinMode(SW420_PIN, INPUT);

  // OUTPUT
  pinMode(BUZZER,    OUTPUT);
  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);

  digitalWrite(BUZZER, LOW);
  setLED(false); // LED hijau menyala default

  Serial.println("=================================");
  Serial.println("  SMART RACK SECURITY SYSTEM");
  Serial.println("=================================");

  // WIFI
  setupWifi();

  // LORA
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(433E6)) {
    Serial.println("LoRa gagal! Lanjut tanpa LoRa.");
  } else {
    LoRa.setTxPower(17);
    Serial.println("LoRa OK");
  }

  buzzerStartup();
  Serial.println("System Ready!");
  Serial.println("API: " + String(API_BASE));
  Serial.println("Device ID: " + String(DEVICE_ID));
}

// ============================================
// LOOP
// ============================================
void loop() {
  checkWifi();

  pirState       = digitalRead(PIR_PIN);
  reedState      = digitalRead(REED_PIN);  // LOW = tertutup, HIGH = terbuka
  vibrationState = digitalRead(SW420_PIN);

  // =========================================
  // PIR - Deteksi Gerakan
  // =========================================
  if (pirState == HIGH && !motionActive) {
    motionActive    = true;
    motionStartTime = millis();
    Serial.println(">>> PIR: Gerakan terdeteksi!");
    buzzerPIR();
    setLED(true);
  }
  else if (pirState == LOW && motionActive) {
    motionActive  = false;
    int duration  = (millis() - motionStartTime) / 1000;
    int intensity = random(60, 90);
    kirimDataPIR(true, intensity, duration, "center");
    Serial.println(">>> PIR: Gerakan selesai, durasi=" + String(duration) + "s");
  }

  // =========================================
  // REED SWITCH - Status Rak
  // =========================================
  if (reedState == HIGH && !doorOpen) {
    doorOpen     = true;
    doorOpenTime = millis();
    Serial.println(">>> Reed Switch: Rak terbuka!");
    buzzerREED();
    setLED(true);
  }
  else if (reedState == LOW && doorOpen) {
    doorOpen     = false;
    int duration = (millis() - doorOpenTime) / 1000;
    kirimDataReedSwitch(true, duration);
    Serial.println(">>> Reed Switch: Rak tertutup, durasi=" + String(duration) + "s");
  }

  // =========================================
  // SW-420 - Deteksi Getaran
  // =========================================
  if (vibrationState == HIGH && lastVibration == LOW) {
    Serial.println(">>> SW-420: Getaran terdeteksi!");
    float x = random(-300, 300) / 100.0;
    float y = random(-300, 300) / 100.0;
    float z = random(80, 120)   / 100.0;
    buzzerGETAR();
    setLED(true);
    kirimDataGetaran(x, y, z);
  }

  // =========================================
  // Kembali normal jika tidak ada bahaya
  // =========================================
  if (!motionActive && !doorOpen && vibrationState == LOW) {
    setLED(false);
  }

  lastVibration = vibrationState;

  // =========================================
  // HEARTBEAT setiap 5 menit
  // =========================================
  if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    kirimHeartbeat();
    lastHeartbeat = millis();
  }

  delay(300);
}
