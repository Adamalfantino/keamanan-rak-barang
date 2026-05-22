/**
 * ============================================
 * Smart Rack Security System
 * ESP32 GATEWAY (RECEIVER) — Di luar rak
 * 
 * Fungsi  : Terima data LoRa dari Node, forward ke Railway API
 * Komunikasi : LoRa SX1278 (RX) + WiFi (HTTPS ke Railway)
 * TIDAK ada sensor di sini
 * ============================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <LoRa.h>

// ============================================
// KONFIGURASI — SESUAIKAN
// ============================================
const char* WIFI_SSID     = "gege";
const char* WIFI_PASSWORD = "biasaaja";
const char* API_BASE      = "https://keamanan-rak-barang-production.up.railway.app/api";
const int   DEVICE_ID     = 1;   // ID device di database Railway

// ============================================
// PIN LORA SX1278 RA-02
// ============================================
#define LORA_SS      27
#define LORA_RST     14
#define LORA_DIO0    26
#define LORA_FREQ    433E6   // Harus sama dengan Node Sender!

// ============================================
// PIN LED STATUS GATEWAY (opsional)
// ============================================
#define LED_WIFI     2    // LED bawaan ESP32 — nyala = WiFi OK
#define LED_LORA     4    // Kedip = terima paket LoRa

// ============================================
// STATISTIK
// ============================================
unsigned long totalReceived = 0;
unsigned long totalSent     = 0;
unsigned long totalFailed   = 0;
unsigned long lastStatusPrint = 0;

// ============================================
// SETUP WIFI
// ============================================
void setupWifi() {
  Serial.println("[WiFi] Menghubungkan ke: " + String(WIFI_SSID));
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 40) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WiFi] ✓ Terhubung! IP: " + WiFi.localIP().toString());
    digitalWrite(LED_WIFI, HIGH);
  } else {
    Serial.println("\n[WiFi] ✗ Gagal. Cek SSID/Password atau pastikan 2.4GHz.");
    digitalWrite(LED_WIFI, LOW);
  }
}

void checkWifi() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] Putus, reconnecting...");
    digitalWrite(LED_WIFI, LOW);
    WiFi.disconnect();
    delay(1000);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 20) {
      delay(500);
      Serial.print(".");
      attempt++;
    }

    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\n[WiFi] ✓ Terhubung kembali!");
      digitalWrite(LED_WIFI, HIGH);
    } else {
      Serial.println("\n[WiFi] ✗ Gagal reconnect.");
    }
  }
}

// ============================================
// HTTP POST KE RAILWAY API
// ============================================
bool httpPost(String endpoint, String jsonBody) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] Skip — WiFi tidak terhubung");
    return false;
  }

  WiFiClientSecure client;
  client.setInsecure(); // Skip SSL verify — cukup untuk Railway

  HTTPClient http;
  String url = String(API_BASE) + endpoint;

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(10000);

  int httpCode = http.POST(jsonBody);

  if (httpCode > 0) {
    if (httpCode == 200 || httpCode == 201) {
      Serial.println("[HTTP] ✓ " + endpoint + " → " + String(httpCode));
      http.end();
      totalSent++;
      return true;
    } else {
      String resp = http.getString();
      Serial.println("[HTTP] ✗ " + endpoint + " → " + String(httpCode));
      Serial.println("       " + resp.substring(0, 120));
    }
  } else {
    Serial.println("[HTTP] ✗ Error: " + String(httpCode) + " — " + endpoint);
  }

  http.end();
  totalFailed++;
  return false;
}

// ============================================
// PROSES PAKET PIR
// ============================================
void prosesPIR(String nodeId, String jsonStr) {
  StaticJsonDocument<256> src;
  if (deserializeJson(src, jsonStr) != DeserializationError::Ok) {
    Serial.println("[PIR] JSON parse error");
    return;
  }

  StaticJsonDocument<256> doc;
  doc["device_id"]        = DEVICE_ID;
  doc["motion_detected"]  = src["motion_detected"]  | false;
  doc["motion_intensity"] = src["motion_intensity"]  | 50;
  doc["duration_seconds"] = src["duration_seconds"]  | 0;
  doc["detection_zone"]   = src["detection_zone"]    | "center";

  String body;
  serializeJson(doc, body);
  Serial.println("[PIR] Node=" + nodeId + " → forward ke API");
  httpPost("/pir/data", body);
}

// ============================================
// PROSES PAKET REED SWITCH
// ============================================
void prosesREED(String nodeId, String jsonStr) {
  StaticJsonDocument<256> src;
  if (deserializeJson(src, jsonStr) != DeserializationError::Ok) {
    Serial.println("[REED] JSON parse error");
    return;
  }

  StaticJsonDocument<256> doc;
  doc["device_id"]        = DEVICE_ID;
  doc["door_opened"]      = src["door_opened"]      | false;
  doc["duration_seconds"] = src["duration_seconds"] | 0;
  doc["access_method"]    = src["access_method"]    | "manual";
  doc["door_location"]    = src["door_location"]    | "rack";
  doc["is_forced_entry"]  = src["is_forced_entry"]  | false;

  String body;
  serializeJson(doc, body);
  Serial.println("[REED] Node=" + nodeId + " → forward ke API");
  httpPost("/door-access/data", body);
}

// ============================================
// PROSES PAKET GETARAN SW420
// ============================================
void prosesVIBRATION(String nodeId, String jsonStr) {
  StaticJsonDocument<256> src;
  if (deserializeJson(src, jsonStr) != DeserializationError::Ok) {
    Serial.println("[VIBRATION] JSON parse error");
    return;
  }

  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["x_axis"]    = src["x_axis"]    | 0.0;
  doc["y_axis"]    = src["y_axis"]    | 0.0;
  doc["z_axis"]    = src["z_axis"]    | 0.0;
  doc["threshold"] = src["threshold"] | 2.0;

  String body;
  serializeJson(doc, body);
  Serial.println("[VIBRATION] Node=" + nodeId + " → forward ke API");
  httpPost("/vibration/data", body);
}

// ============================================
// PROSES PAKET HEARTBEAT
// ============================================
void prosesHEARTBEAT(String nodeId, String jsonStr) {
  StaticJsonDocument<128> src;
  deserializeJson(src, jsonStr);
  long uptime = src["uptime_s"] | 0;
  Serial.println("[HEARTBEAT] Node=" + nodeId + " masih hidup, uptime=" + String(uptime) + "s");

  // Kirim heartbeat ke API agar device tetap Online di dashboard
  StaticJsonDocument<128> doc;
  doc["node_id"]    = nodeId;
  doc["gateway_id"] = "GATEWAY_001";
  doc["payload"]    = "HEARTBEAT|" + nodeId;

  String body;
  serializeJson(doc, body);
  httpPost("/lora/receive", body);
}

// ============================================
// PARSE & ROUTE PAKET LORA
// Format paket: TYPE|NODE_ID|{json}
// ============================================
void prosesPacket(String packet, int rssi, float snr) {
  totalReceived++;

  // Kedip LED tanda terima paket
  digitalWrite(LED_LORA, HIGH); delay(50); digitalWrite(LED_LORA, LOW);

  Serial.println("─────────────────────────────────");
  Serial.println("[LoRa RX] RSSI=" + String(rssi) + " SNR=" + String(snr, 1));
  Serial.println("[LoRa RX] Raw: " + packet.substring(0, 100));

  // Parse format: TYPE|NODE_ID|{json}
  int sep1 = packet.indexOf('|');
  if (sep1 < 0) {
    Serial.println("[LoRa RX] Format tidak dikenal, skip.");
    return;
  }

  int sep2 = packet.indexOf('|', sep1 + 1);
  if (sep2 < 0) {
    Serial.println("[LoRa RX] Format tidak lengkap, skip.");
    return;
  }

  String type    = packet.substring(0, sep1);
  String nodeId  = packet.substring(sep1 + 1, sep2);
  String payload = packet.substring(sep2 + 1);

  Serial.println("[LoRa RX] Type=" + type + " Node=" + nodeId);

  checkWifi(); // Pastikan WiFi masih konek sebelum forward

  if      (type == "PIR")       prosesPIR(nodeId, payload);
  else if (type == "REED")      prosesREED(nodeId, payload);
  else if (type == "VIBRATION") prosesVIBRATION(nodeId, payload);
  else if (type == "HEARTBEAT") prosesHEARTBEAT(nodeId, payload);
  else {
    Serial.println("[LoRa RX] Type tidak dikenal: " + type);
  }
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  pinMode(LED_WIFI, OUTPUT);
  pinMode(LED_LORA, OUTPUT);
  digitalWrite(LED_WIFI, LOW);
  digitalWrite(LED_LORA, LOW);

  Serial.println("=================================");
  Serial.println("  SMART RACK — GATEWAY RECEIVER");
  Serial.println("  API: " + String(API_BASE));
  Serial.println("  Device ID: " + String(DEVICE_ID));
  Serial.println("=================================");

  // WiFi
  setupWifi();

  // LoRa — HARUS setting sama persis dengan Node Sender
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("[LoRa] GAGAL INIT! Cek wiring.");
    while (true) {
      digitalWrite(LED_LORA, HIGH); delay(200);
      digitalWrite(LED_LORA, LOW);  delay(200);
    }
  }

  LoRa.setSpreadingFactor(9);    // Harus sama dengan Node!
  LoRa.setSignalBandwidth(125E3);
  LoRa.setCodingRate4(5);

  Serial.println("[LoRa] ✓ OK — " + String(LORA_FREQ / 1E6) + " MHz, menunggu paket...");
  Serial.println("Gateway siap!");
}

// ============================================
// LOOP
// ============================================
void loop() {
  // Cek apakah ada paket LoRa masuk
  int packetSize = LoRa.parsePacket();

  if (packetSize > 0) {
    String packet = "";
    while (LoRa.available()) {
      packet += (char)LoRa.read();
    }
    int   rssi = LoRa.packetRssi();
    float snr  = LoRa.packetSnr();

    prosesPacket(packet, rssi, snr);
  }

  // Print statistik setiap 60 detik
  if (millis() - lastStatusPrint >= 60000) {
    Serial.println("─────────────────────────────────");
    Serial.println("[STATUS] Uptime: " + String(millis()/1000) + "s");
    Serial.println("[STATUS] LoRa diterima : " + String(totalReceived));
    Serial.println("[STATUS] API berhasil  : " + String(totalSent));
    Serial.println("[STATUS] API gagal     : " + String(totalFailed));
    Serial.println("[STATUS] WiFi: " + String(WiFi.status() == WL_CONNECTED ? "OK" : "PUTUS"));
    Serial.println("─────────────────────────────────");
    lastStatusPrint = millis();
  }

  delay(10); // Kecil saja agar LoRa tidak miss paket
}
