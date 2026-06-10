#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <LoRa.h>
#include <PubSubClient.h>

// ============================================
// API (Railway)
// ============================================
const char* API_BASE  = "https://keamanan-rak-barang-production.up.railway.app/api";
const int   DEVICE_ID = 1;

// ============================================
// WIFI
// ============================================
const char* WIFI_SSID = "ini";
const char* WIFI_PASS = "00000000";

// ============================================
// MQTT HiveMQ
// ============================================
const char* MQTT_SERVER     = "broker.hivemq.com";
const int   MQTT_PORT       = 1883;
const char* TOPIC_PIR       = "keamanan/pir";
const char* TOPIC_REED      = "keamanan/reed";
const char* TOPIC_VIBRATION = "keamanan/vibration";
const char* TOPIC_STATUS    = "keamanan/status";
const char* TOPIC_TEST      = "keamanan/test";

WiFiClient   espClient;
PubSubClient mqtt(espClient);

// ============================================
// LORA CONFIG
// ============================================
#define LORA_SS        27
#define LORA_RST       14
#define LORA_DIO0      26
#define LORA_FREQ      433E6
#define LORA_SYNC_WORD 0xA5
#define LORA_SF        9
#define LORA_BW        125E3
#define LORA_CR        5

#define EXPECTED_NODE    "NODE_001"
#define EXPECTED_GATEWAY "GATEWAY_001"

// ============================================
// LED
// ============================================
#define LED_WIFI  2
#define LED_LORA  4
#define LED_HIJAU 33
#define LED_MERAH 25

// ============================================
// BUZZER
// ============================================
#define PIN_BUZZER 32   // Ganti sesuai pin yang tersedia di ESP32 gateway

// Pola bunyi per sensor
void buzzerPIR() {      // 3x beep pendek
  for (int i = 0; i < 3; i++) {
    digitalWrite(PIN_BUZZER, HIGH); delay(80);
    digitalWrite(PIN_BUZZER, LOW);  delay(80);
  }
}
void buzzerReed() {     // 1x beep panjang
  digitalWrite(PIN_BUZZER, HIGH); delay(600);
  digitalWrite(PIN_BUZZER, LOW);
}
void buzzerVib() {      // 2x beep sedang
  for (int i = 0; i < 2; i++) {
    digitalWrite(PIN_BUZZER, HIGH); delay(150);
    digitalWrite(PIN_BUZZER, LOW);  delay(100);
  }
}

// ============================================
// ALERT SYSTEM
// ============================================
#define ALERT_HOLD 3000
bool alertActive = false;
unsigned long alertStartTime = 0;

// ============================================
// STATISTIK
// ============================================
unsigned long totalReceived = 0;
unsigned long totalSent     = 0;
unsigned long totalFailed   = 0;
unsigned long lastStatusPrint = 0;

// ============================================
// BLINK LORA LED
// ============================================
void blinkLoRa() {
  digitalWrite(LED_LORA, HIGH);
  delay(50);
  digitalWrite(LED_LORA, LOW);
}

// ============================================
// WIFI CONNECT
// ============================================
void setupWifi() {
  Serial.println("\n[WiFi] Connecting...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 40) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WiFi] CONNECTED  IP: " + WiFi.localIP().toString());
    digitalWrite(LED_WIFI, HIGH);
  } else {
    Serial.println("\n[WiFi] FAILED — restart");
    ESP.restart();
  }
}

// ============================================
// WIFI AUTO RECONNECT
// ============================================
void checkWifi() {
  if (WiFi.status() == WL_CONNECTED) return;
  Serial.println("[WiFi] Reconnecting...");
  digitalWrite(LED_WIFI, LOW);
  WiFi.disconnect();
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 20) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WiFi] RECONNECTED");
    digitalWrite(LED_WIFI, HIGH);
  } else {
    Serial.println("\n[WiFi] FAILED RECONNECT");
  }
}

// ============================================
// MQTT CONNECT
// ============================================
void connectMQTT() {
  mqtt.setServer(MQTT_SERVER, MQTT_PORT);
  while (!mqtt.connected()) {
    String clientId = "ESP32-GW-" + String(random(0xffff), HEX);
    Serial.println("[MQTT] Connecting...");
    if (mqtt.connect(clientId.c_str())) {
      Serial.println("[MQTT] CONNECTED");
    } else {
      Serial.print("[MQTT] FAILED rc=");
      Serial.println(mqtt.state());
      delay(2000);
    }
  }
}

// ============================================
// MQTT PUBLISH
// ============================================
void publishMQTT(const char* topic, String payload) {
  if (!mqtt.connected()) return;
  mqtt.publish(topic, payload.c_str());
  Serial.println("[MQTT] SENT -> " + String(topic));
}

// ============================================
// HTTP POST
// ============================================
bool httpPost(String endpoint, String body) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] Skip — WiFi tidak terhubung");
    return false;
  }

  WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  String url = String(API_BASE) + endpoint;
  Serial.println("[HTTP] POST -> " + url);
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(10000);

  int code = http.POST(body);
  Serial.print("[HTTP] CODE: ");
  Serial.println(code);

  if (code != 200 && code != 201) {
    String resp = http.getString();
    Serial.println("[HTTP] RESP: " + resp.substring(0, 150));
  }

  http.end();

  if (code == 200 || code == 201) {
    totalSent++;
    return true;
  }
  totalFailed++;
  return false;
}

// ============================================
// ALERT
// ============================================
void setAlert(String msg) {
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_HIJAU, LOW);
  alertActive    = true;
  alertStartTime = millis();
  Serial.println("[ALERT] " + msg);
}

// ============================================
// ROUTER CORE
// Terima JSON mentah dari node sender, parse,
// lalu kirim ke API dengan format yang benar.
// ============================================
void routePacket(String raw) {
  StaticJsonDocument<512> doc;
  DeserializationError err = deserializeJson(doc, raw);
  if (err) {
    Serial.println("[JSON] INVALID: " + String(err.c_str()));
    return;
  }

  String node    = doc["node_id"]    | "";
  String gateway = doc["gateway_id"] | "";
  String type    = doc["type"]       | "";

  // Filter node & gateway
  if (node != EXPECTED_NODE) {
    Serial.println("[FILTER] INVALID NODE: " + node);
    return;
  }
  if (gateway != EXPECTED_GATEWAY) {
    Serial.println("[FILTER] INVALID GATEWAY: " + gateway);
    return;
  }

  Serial.println("[ROUTE] type=" + type + " node=" + node);
  checkWifi();

  // =========================================
  // PIR
  // =========================================
  if (type == "PIR") {
    setAlert("PIR detected");
    buzzerPIR();   // bunyi di gateway

    // Build payload untuk API
    StaticJsonDocument<256> apiDoc;
    apiDoc["device_id"]        = DEVICE_ID;
    apiDoc["motion_detected"]  = doc["motion_detected"]  | false;
    apiDoc["motion_intensity"] = doc["motion_intensity"]  | 50;
    apiDoc["duration_seconds"] = doc["duration_seconds"]  | 0;
    apiDoc["detection_zone"]   = doc["detection_zone"]    | "center";
    String apiBody;
    serializeJson(apiDoc, apiBody);
    httpPost("/pir/data", apiBody);

    // Build payload untuk MQTT
    StaticJsonDocument<256> mqttDoc;
    mqttDoc["device_id"] = DEVICE_ID;
    mqttDoc["node"]      = node;
    mqttDoc["type"]      = type;
    mqttDoc["motion"]    = doc["motion_detected"] | false;
    String mqttBody;
    serializeJson(mqttDoc, mqttBody);
    publishMQTT(TOPIC_PIR, mqttBody);
  }

  // =========================================
  // REED
  // =========================================
  else if (type == "REED") {
    setAlert("DOOR event");
    buzzerReed();  // bunyi di gateway

    // Build payload untuk API
    StaticJsonDocument<256> apiDoc;
    apiDoc["device_id"]        = DEVICE_ID;
    apiDoc["door_opened"]      = doc["door_opened"]      | false;
    apiDoc["duration_seconds"] = doc["duration_seconds"] | 0;
    apiDoc["access_method"]    = doc["access_method"]    | "manual";
    apiDoc["door_location"]    = doc["door_location"]    | "rack";
    apiDoc["is_forced_entry"]  = doc["is_forced_entry"]  | false;
    String apiBody;
    serializeJson(apiDoc, apiBody);
    httpPost("/door-access/data", apiBody);

    // Build payload untuk MQTT
    StaticJsonDocument<256> mqttDoc;
    mqttDoc["device_id"] = DEVICE_ID;
    mqttDoc["node"]      = node;
    mqttDoc["type"]      = type;
    mqttDoc["door"]      = doc["door_opened"] | false;
    String mqttBody;
    serializeJson(mqttDoc, mqttBody);
    publishMQTT(TOPIC_REED, mqttBody);
  }

  // =========================================
  // VIBRATION
  // =========================================
  else if (type == "VIBRATION") {
    setAlert("VIBRATION detected");
    buzzerVib();   // bunyi di gateway

    // Build payload untuk API — butuh x_axis, y_axis, z_axis
    // Jika node sender tidak kirim (firmware lama), generate simulasi di gateway
    StaticJsonDocument<256> apiDoc;
    apiDoc["device_id"] = DEVICE_ID;

    if (doc.containsKey("x_axis")) {
      apiDoc["x_axis"] = doc["x_axis"] | 0.0;
      apiDoc["y_axis"] = doc["y_axis"] | 0.0;
      apiDoc["z_axis"] = doc["z_axis"] | 0.0;
    } else {
      // Firmware lama tidak kirim axis — simulasikan nilai getaran terdeteksi
      apiDoc["x_axis"] = (float)(random(-300, 300)) / 100.0;
      apiDoc["y_axis"] = (float)(random(-300, 300)) / 100.0;
      apiDoc["z_axis"] = 2.0 + (float)(random(0, 150)) / 100.0;
    }
    apiDoc["threshold"] = doc["threshold"] | 2.0;

    String apiBody;
    serializeJson(apiDoc, apiBody);
    httpPost("/vibration/data", apiBody);

    // Build payload untuk MQTT
    StaticJsonDocument<256> mqttDoc;
    mqttDoc["device_id"] = DEVICE_ID;
    mqttDoc["node"]      = node;
    mqttDoc["type"]      = type;
    mqttDoc["x_axis"]    = apiDoc["x_axis"];
    mqttDoc["y_axis"]    = apiDoc["y_axis"];
    mqttDoc["z_axis"]    = apiDoc["z_axis"];
    String mqttBody;
    serializeJson(mqttDoc, mqttBody);
    publishMQTT(TOPIC_VIBRATION, mqttBody);
  }

  // =========================================
  // STATUS (heartbeat)
  // =========================================
  else if (type == "STATUS") {
    // Kirim ke /lora/receive agar device tetap Online di dashboard
    StaticJsonDocument<128> apiDoc;
    apiDoc["node_id"]    = node;
    apiDoc["gateway_id"] = gateway;
    apiDoc["payload"]    = "HEARTBEAT|" + node;
    apiDoc["uptime"]     = doc["uptime"] | 0;
    String apiBody;
    serializeJson(apiDoc, apiBody);
    httpPost("/lora/receive", apiBody);

    // MQTT
    StaticJsonDocument<128> mqttDoc;
    mqttDoc["device_id"] = DEVICE_ID;
    mqttDoc["node"]      = node;
    mqttDoc["type"]      = type;
    mqttDoc["uptime"]    = doc["uptime"] | 0;
    String mqttBody;
    serializeJson(mqttDoc, mqttBody);
    publishMQTT(TOPIC_STATUS, mqttBody);

    Serial.println("[STATUS] RECEIVED & FORWARDED");
  }

  // =========================================
  // TEST
  // =========================================
  else if (type == "TEST") {
    Serial.println("[TEST] RECEIVED OK");
    StaticJsonDocument<128> mqttDoc;
    mqttDoc["device_id"] = DEVICE_ID;
    mqttDoc["node"]      = node;
    mqttDoc["type"]      = type;
    String mqttBody;
    serializeJson(mqttDoc, mqttBody);
    publishMQTT(TOPIC_TEST, mqttBody);
  }

  else {
    Serial.println("[ROUTER] UNKNOWN TYPE: " + type);
  }
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println("\n==========================");
  Serial.println("  SMART RACK GATEWAY      ");
  Serial.println("==========================");

  pinMode(LED_WIFI,  OUTPUT);
  pinMode(LED_LORA,  OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);
  pinMode(LED_MERAH, OUTPUT);
  pinMode(PIN_BUZZER, OUTPUT);
  digitalWrite(LED_WIFI,  LOW);
  digitalWrite(LED_LORA,  LOW);
  digitalWrite(LED_HIJAU, HIGH);
  digitalWrite(LED_MERAH, LOW);
  digitalWrite(PIN_BUZZER, LOW);

  setupWifi();
  connectMQTT();

  // =========================================
  // LORA — hardware reset + retry
  // =========================================
  Serial.println("[1] LoRa setPins");
  pinMode(LORA_RST, OUTPUT);
  digitalWrite(LORA_RST, LOW);  delay(20);
  digitalWrite(LORA_RST, HIGH); delay(150);
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);

  Serial.println("[2] Starting LoRa...");
  int loraRetry = 0;
  while (!LoRa.begin(LORA_FREQ)) {
    loraRetry++;
    Serial.print("[LoRa] INIT FAILED, retry: ");
    Serial.println(loraRetry);
    digitalWrite(LORA_RST, LOW);  delay(20);
    digitalWrite(LORA_RST, HIGH); delay(150);
    delay(500);
    if (loraRetry >= 10) {
      Serial.println("[LoRa] GIVING UP - RESTART");
      delay(1000);
      ESP.restart();
    }
  }

  LoRa.setSpreadingFactor(LORA_SF);
  LoRa.setSignalBandwidth(LORA_BW);
  LoRa.setCodingRate4(LORA_CR);
  LoRa.setSyncWord(LORA_SYNC_WORD);
  Serial.println("[3] LoRa READY");
  Serial.println("[SYSTEM] READY — menunggu paket...");
  Serial.println("  API     : " + String(API_BASE));
  Serial.println("  DeviceID: " + String(DEVICE_ID));
}

// ============================================
// LOOP
// ============================================
void loop() {
  checkWifi();
  if (!mqtt.connected()) connectMQTT();
  mqtt.loop();

  // =========================================
  // RECEIVE LORA
  // =========================================
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    totalReceived++;
    blinkLoRa();

    String packet = "";
    while (LoRa.available()) {
      packet += (char)LoRa.read();
    }

    Serial.println("\n========== LORA RX ==========");
    Serial.println(packet);
    Serial.print("RSSI: "); Serial.println(LoRa.packetRssi());
    Serial.print("SNR:  "); Serial.println(LoRa.packetSnr());
    Serial.println("=============================");

    routePacket(packet);
  }

  // =========================================
  // ALERT RESET
  // =========================================
  if (alertActive && millis() - alertStartTime > ALERT_HOLD) {
    alertActive = false;
    digitalWrite(LED_HIJAU, HIGH);
    digitalWrite(LED_MERAH, LOW);
  }

  // =========================================
  // STATUS PRINT setiap 60 detik
  // =========================================
  if (millis() - lastStatusPrint >= 60000) {
    Serial.println("─────────────────────────────────");
    Serial.println("[STATUS] Uptime  : " + String(millis() / 1000) + "s");
    Serial.println("[STATUS] Diterima: " + String(totalReceived));
    Serial.println("[STATUS] API OK  : " + String(totalSent));
    Serial.println("[STATUS] API FAIL: " + String(totalFailed));
    Serial.println("[STATUS] WiFi    : " + String(WiFi.status() == WL_CONNECTED ? "OK" : "PUTUS"));
    Serial.println("[STATUS] MQTT    : " + String(mqtt.connected() ? "OK" : "PUTUS"));
    Serial.println("─────────────────────────────────");
    lastStatusPrint = millis();
  }

  delay(10);
}
