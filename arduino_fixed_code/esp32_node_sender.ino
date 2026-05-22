/**
 * ============================================
 * Smart Rack Security System
 * ESP32 NODE (SENDER) — Di dalam / dekat rak
 * 
 * Sensor  : PIR + Reed Switch Magnetik + SW420
 * Output  : Buzzer + LED Merah + LED Hijau
 * Komunikasi : LoRa SX1278 (TX only)
 * TIDAK butuh WiFi
 * ============================================
 */

#include <ArduinoJson.h>
#include <SPI.h>
#include <LoRa.h>
#include <math.h>

// ============================================
// IDENTITAS NODE
// ============================================
#define NODE_ID   "NODE_001"   // ID unik node ini

// ============================================
// PIN SENSOR
// ============================================
#define PIR_PIN      33
#define REED_PIN     32   // INPUT_PULLUP — LOW = tertutup, HIGH = terbuka
#define SW420_PIN    34

// ============================================
// PIN OUTPUT
// ============================================
#define BUZZER       25
#define LED_MERAH    13
#define LED_HIJAU    4

// ============================================
// PIN LORA SX1278 RA-02
// ============================================
#define LORA_SS      27
#define LORA_RST     14
#define LORA_DIO0    26
#define LORA_FREQ    433E6   // 433 MHz — sesuaikan modul kamu

// ============================================
// VARIABEL STATE SENSOR
// ============================================
int  pirState       = LOW;
int  reedState      = LOW;
int  vibrationState = LOW;
int  lastVibration  = LOW;

bool motionActive   = false;
bool doorOpen       = false;

unsigned long motionStartTime = 0;
unsigned long doorOpenTime    = 0;
unsigned long lastHeartbeat   = 0;

// Heartbeat ke gateway setiap 2 menit
const unsigned long HEARTBEAT_INTERVAL = 120000;

// ============================================
// LED
// ============================================
void setLED(bool bahaya) {
  digitalWrite(LED_MERAH, bahaya ? HIGH : LOW);
  digitalWrite(LED_HIJAU, bahaya ? LOW  : HIGH);
}

// ============================================
// BUZZER
// ============================================
void buzzerPIR() {
  digitalWrite(BUZZER, HIGH); delay(300);
  digitalWrite(BUZZER, LOW);
}

void buzzerREED() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER, HIGH); delay(100);
    digitalWrite(BUZZER, LOW);  delay(100);
  }
}

void buzzerGETAR() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER, HIGH); delay(80);
    digitalWrite(BUZZER, LOW);  delay(80);
  }
}

void buzzerStartup() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER, HIGH); delay(200);
    digitalWrite(BUZZER, LOW);  delay(100);
  }
}

// ============================================
// KIRIM PAKET LORA
// Format: TYPE|NODE_ID|json_payload
// ============================================
bool loRaSend(String type, String jsonPayload) {
  String packet = type + "|" + NODE_ID + "|" + jsonPayload;

  LoRa.beginPacket();
  LoRa.print(packet);
  int result = LoRa.endPacket();

  if (result) {
    Serial.println("[LoRa TX] ✓ " + packet.substring(0, 80));
  } else {
    Serial.println("[LoRa TX] ✗ Gagal kirim paket");
  }
  return result;
}

// ============================================
// KIRIM DATA PIR
// ============================================
void kirimPIR(bool detected, int intensity, int duration) {
  StaticJsonDocument<200> doc;
  doc["motion_detected"]  = detected;
  doc["motion_intensity"] = intensity;
  doc["duration_seconds"] = duration;
  doc["detection_zone"]   = "center";

  String body;
  serializeJson(doc, body);
  Serial.println("[PIR] detected=" + String(detected) + " intensity=" + String(intensity) + " dur=" + String(duration) + "s");
  loRaSend("PIR", body);
}

// ============================================
// KIRIM DATA REED SWITCH
// ============================================
void kirimReed(bool opened, int duration) {
  StaticJsonDocument<200> doc;
  doc["door_opened"]      = opened;
  doc["duration_seconds"] = duration;
  doc["access_method"]    = "manual";
  doc["door_location"]    = "rack";
  doc["is_forced_entry"]  = false;

  String body;
  serializeJson(doc, body);
  Serial.println("[REED] opened=" + String(opened) + " dur=" + String(duration) + "s");
  loRaSend("REED", body);
}

// ============================================
// KIRIM DATA GETARAN SW420
// ============================================
void kirimGetaran(float x, float y, float z) {
  StaticJsonDocument<200> doc;
  doc["x_axis"]    = x;
  doc["y_axis"]    = y;
  doc["z_axis"]    = z;
  doc["threshold"] = 2.0;

  String body;
  serializeJson(doc, body);
  float mag = sqrt(x*x + y*y + z*z);
  Serial.println("[SW420] magnitude=" + String(mag, 2));
  loRaSend("VIBRATION", body);
}

// ============================================
// HEARTBEAT — beri tahu gateway node masih hidup
// ============================================
void kirimHeartbeat() {
  StaticJsonDocument<64> doc;
  doc["uptime_s"] = millis() / 1000;

  String body;
  serializeJson(doc, body);
  Serial.println("[HEARTBEAT] uptime=" + String(millis()/1000) + "s");
  loRaSend("HEARTBEAT", body);
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  // Pin sensor
  pinMode(PIR_PIN,   INPUT);
  pinMode(REED_PIN,  INPUT_PULLUP);
  pinMode(SW420_PIN, INPUT);

  // Pin output
  pinMode(BUZZER,    OUTPUT);
  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);

  digitalWrite(BUZZER, LOW);
  setLED(false); // LED hijau nyala default

  Serial.println("=================================");
  Serial.println("  SMART RACK — NODE SENDER");
  Serial.println("  Node ID : " NODE_ID);
  Serial.println("=================================");

  // Init LoRa
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("[LoRa] GAGAL INIT! Cek wiring.");
    // Blink LED merah terus sebagai tanda error
    while (true) {
      digitalWrite(LED_MERAH, HIGH); delay(200);
      digitalWrite(LED_MERAH, LOW);  delay(200);
    }
  }

  LoRa.setTxPower(17);
  LoRa.setSpreadingFactor(9);    // SF9 — balance range vs speed
  LoRa.setSignalBandwidth(125E3);
  LoRa.setCodingRate4(5);

  Serial.println("[LoRa] OK — " + String(LORA_FREQ / 1E6) + " MHz");

  buzzerStartup();
  Serial.println("Node siap!");
}

// ============================================
// LOOP
// ============================================
void loop() {
  pirState       = digitalRead(PIR_PIN);
  reedState      = digitalRead(REED_PIN);
  vibrationState = digitalRead(SW420_PIN);

  // =========================================
  // PIR — Deteksi Gerakan
  // =========================================
  if (pirState == HIGH && !motionActive) {
    motionActive    = true;
    motionStartTime = millis();
    Serial.println(">>> PIR: Gerakan terdeteksi!");
    buzzerPIR();
    setLED(true);
  }
  else if (pirState == LOW && motionActive) {
    motionActive = false;
    int duration = (millis() - motionStartTime) / 1000;
    int intensity = random(60, 90); // estimasi intensitas
    kirimPIR(true, intensity, duration);
    Serial.println(">>> PIR: Selesai, durasi=" + String(duration) + "s");
  }

  // =========================================
  // REED SWITCH — Status Rak
  // =========================================
  if (reedState == HIGH && !doorOpen) {
    doorOpen     = true;
    doorOpenTime = millis();
    Serial.println(">>> REED: Rak terbuka!");
    buzzerREED();
    setLED(true);
  }
  else if (reedState == LOW && doorOpen) {
    doorOpen     = false;
    int duration = (millis() - doorOpenTime) / 1000;
    kirimReed(true, duration);
    Serial.println(">>> REED: Rak tertutup, durasi=" + String(duration) + "s");
  }

  // =========================================
  // SW-420 — Deteksi Getaran
  // =========================================
  if (vibrationState == HIGH && lastVibration == LOW) {
    Serial.println(">>> SW420: Getaran terdeteksi!");
    // Simulasi nilai axis — ganti dengan sensor akselerometer nyata jika ada
    float x = random(-300, 300) / 100.0;
    float y = random(-300, 300) / 100.0;
    float z = random(80, 120)   / 100.0;
    buzzerGETAR();
    setLED(true);
    kirimGetaran(x, y, z);
  }

  // =========================================
  // Reset LED jika semua aman
  // =========================================
  if (!motionActive && !doorOpen && vibrationState == LOW) {
    setLED(false);
  }

  lastVibration = vibrationState;

  // =========================================
  // HEARTBEAT setiap 2 menit
  // =========================================
  if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    kirimHeartbeat();
    lastHeartbeat = millis();
  }

  delay(300);
}
