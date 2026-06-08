#include <SPI.h>
#include <LoRa.h>
#include <ArduinoJson.h>

// ============================================
// LORA PIN
// ============================================
#define LORA_SS      27
#define LORA_RST     14
#define LORA_DIO0    26
#define LORA_FREQ    433E6

// ============================================
// LORA CONFIG
// ============================================
#define LORA_SYNC_WORD 0xA5
#define LORA_SF         9
#define LORA_BW         125E3
#define LORA_CR         5

// ============================================
// SENSOR PIN
// ============================================
#define PIN_SW420    34
#define PIN_REED     32
#define PIN_PIR      33
#define PIN_BUZZER   25

// ============================================
// LED
// ============================================
#define LED_STATUS   2

// ============================================
// NODE INFO
// ============================================
#define NODE_ID      "NODE_001"
#define GATEWAY_ID   "GATEWAY_001"
#define DEVICE_ID    1

// ============================================
// TIMING
// ============================================
#define HEARTBEAT_INTERVAL   30000
#define SENSOR_READ_INTERVAL 50

// ============================================
// VIBRATION
// ============================================
#define VIB_DEBOUNCE 1500

// ============================================
// BUZZER PATTERN
// ============================================
enum BuzzerPattern {
  BUZ_NONE,
  BUZ_PIR,
  BUZ_REED,
  BUZ_VIB
};

// ============================================
// BUZZER STATE
// ============================================
BuzzerPattern currentPattern = BUZ_NONE;
int buzzerStep = 0;
unsigned long buzzerNextTime = 0;

// ============================================
// PIR STATE
// ============================================
bool pirStableState = false;
unsigned long pirLastChange = 0;
unsigned long pirTriggerTime = 0;

// ============================================
// REED STATE
// ============================================
bool reedWasOpen = false;
unsigned long reedOpenTime = 0;

// ============================================
// VIBRATION STATE
// ============================================
bool vibLastState = LOW;
unsigned long vibLastTrigger = 0;

// ============================================
// TIMING
// ============================================
unsigned long lastHeartbeat = 0;
unsigned long lastSensorRead = 0;

// ============================================
// STATISTIC
// ============================================
unsigned long totalSent = 0;
unsigned long totalFailed = 0;

// ============================================
// PROTOTYPE
// ============================================
void readPIR(unsigned long now);
void readReed(unsigned long now);
void readVibration(unsigned long now);
void startBuzzer(BuzzerPattern pattern);
void updateBuzzer(unsigned long now);
void sendPIRData(bool motion, int intensity, int duration, String zone);
void sendReedData(bool opened, int duration, bool forced);
void sendVibrationData();
void sendStatus();
void sendLoRa(StaticJsonDocument<256>& doc);
void sendLoRa(StaticJsonDocument<128>& doc);
void blinkLED(int delayMs);
void beep(int ms);

// ============================================
// BLINK LED
// ============================================
void blinkLED(int delayMs = 50) {
  digitalWrite(LED_STATUS, HIGH);
  delay(delayMs);
  digitalWrite(LED_STATUS, LOW);
}

// ============================================
// BEEP
// ============================================
void beep(int ms) {
  digitalWrite(PIN_BUZZER, HIGH);
  delay(ms);
  digitalWrite(PIN_BUZZER, LOW);
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println("\n==========================");
  Serial.println("   SMART RACK NODE        ");
  Serial.println("==========================");

  pinMode(PIN_SW420, INPUT_PULLUP);
  pinMode(PIN_REED,  INPUT_PULLUP);
  pinMode(PIN_PIR,   INPUT);
  pinMode(PIN_BUZZER, OUTPUT);
  pinMode(LED_STATUS, OUTPUT);
  digitalWrite(PIN_BUZZER, LOW);
  digitalWrite(LED_STATUS, LOW);

  // Startup beep
  beep(80); delay(80); beep(80);

  // =========================================
  // LORA
  // =========================================
  Serial.println("[1] LoRa setPins");
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);

  Serial.println("[2] Starting LoRa...");
  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("[LoRa] INIT FAILED");
    while (1) {
      digitalWrite(PIN_BUZZER, HIGH);
      digitalWrite(LED_STATUS, HIGH);
      delay(100);
      digitalWrite(PIN_BUZZER, LOW);
      digitalWrite(LED_STATUS, LOW);
      delay(100);
    }
  }

  LoRa.setSpreadingFactor(LORA_SF);
  LoRa.setSignalBandwidth(LORA_BW);
  LoRa.setCodingRate4(LORA_CR);
  LoRa.setSyncWord(LORA_SYNC_WORD);
  Serial.println("[3] LoRa READY");

  // PIR stabilization
  delay(5000);
  for (int i = 0; i < 3; i++) {
    blinkLED(100);
    delay(100);
  }

  sendStatus();
  lastHeartbeat = millis();
  Serial.println("[NODE] STARTED");
}

// ============================================
// LOOP
// ============================================
void loop() {
  unsigned long now = millis();

  if (now - lastSensorRead >= SENSOR_READ_INTERVAL) {
    lastSensorRead = now;
    readPIR(now);
    readReed(now);
    readVibration(now);
  }

  updateBuzzer(now);

  if (now - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    sendStatus();
    lastHeartbeat = now;
  }
}

// ============================================
// PIR
// ============================================
void readPIR(unsigned long now) {
  bool current = digitalRead(PIN_PIR);

  if (current != pirStableState && now - pirLastChange > 200) {
    pirLastChange  = now;
    pirStableState = current;

    if (pirStableState) {
      pirTriggerTime = now;
      Serial.println("[PIR] DETECTED");
      startBuzzer(BUZ_PIR);
      sendPIRData(true, 75, 0, "center");
    } else {
      int duration = (now - pirTriggerTime) / 1000;
      Serial.println("[PIR] END");
      sendPIRData(false, 0, duration, "center");
    }
  }
}

// ============================================
// REED
// ============================================
void readReed(unsigned long now) {
  static bool lastStable = LOW;
  static unsigned long lastDebounce = 0;

  bool current = digitalRead(PIN_REED);
  if (current != lastStable) {
    if (now - lastDebounce > 150) {
      lastDebounce = now;

      if (current == HIGH) {
        reedOpenTime = now;
        reedWasOpen  = true;
        Serial.println("[REED] OPEN");
        startBuzzer(BUZ_REED);
        sendReedData(true, 0, false);
      } else {
        int duration = reedWasOpen ? (now - reedOpenTime) / 1000 : 0;
        reedWasOpen  = false;
        Serial.println("[REED] CLOSE");
        startBuzzer(BUZ_REED);
        sendReedData(false, duration, false);
      }
      lastStable = current;
    }
  }
}

// ============================================
// VIBRATION
// ============================================
void readVibration(unsigned long now) {
  bool current = digitalRead(PIN_SW420);

  if (current == HIGH && vibLastState == LOW) {
    if (now - vibLastTrigger > VIB_DEBOUNCE) {
      vibLastTrigger = now;
      Serial.println("[VIB] DETECTED");
      startBuzzer(BUZ_VIB);
      sendVibrationData();
    }
  }
  vibLastState = current;
}

// ============================================
// START BUZZER
// ============================================
void startBuzzer(BuzzerPattern pattern) {
  digitalWrite(PIN_BUZZER, LOW);
  currentPattern = pattern;
  buzzerStep     = 0;
  buzzerNextTime = millis();
}

// ============================================
// UPDATE BUZZER
// ============================================
void updateBuzzer(unsigned long now) {
  if (currentPattern == BUZ_NONE) return;
  if (now < buzzerNextTime)       return;

  // PIR: 3x beep pendek
  if (currentPattern == BUZ_PIR) {
    if (buzzerStep < 6) {
      digitalWrite(PIN_BUZZER, buzzerStep % 2 == 0 ? HIGH : LOW);
      buzzerNextTime = now + 80;
      buzzerStep++;
    } else {
      digitalWrite(PIN_BUZZER, LOW);
      currentPattern = BUZ_NONE;
    }
  }
  // REED: 1x beep panjang
  else if (currentPattern == BUZ_REED) {
    if (buzzerStep == 0) {
      digitalWrite(PIN_BUZZER, HIGH);
      buzzerNextTime = now + 600;
      buzzerStep++;
    } else {
      digitalWrite(PIN_BUZZER, LOW);
      currentPattern = BUZ_NONE;
    }
  }
  // VIBRATION: 2x beep sedang
  else if (currentPattern == BUZ_VIB) {
    if (buzzerStep < 4) {
      digitalWrite(PIN_BUZZER, buzzerStep % 2 == 0 ? HIGH : LOW);
      buzzerNextTime = now + (buzzerStep % 2 == 0 ? 150 : 100);
      buzzerStep++;
    } else {
      digitalWrite(PIN_BUZZER, LOW);
      currentPattern = BUZ_NONE;
    }
  }
}

// ============================================
// SEND PIR
// ============================================
void sendPIRData(bool motion, int intensity, int duration, String zone) {
  StaticJsonDocument<256> doc;
  doc["type"]             = "PIR";
  doc["node_id"]          = NODE_ID;
  doc["gateway_id"]       = GATEWAY_ID;
  doc["device_id"]        = DEVICE_ID;
  doc["motion_detected"]  = motion;
  doc["motion_intensity"] = intensity;
  doc["duration_seconds"] = duration;
  doc["detection_zone"]   = zone;
  sendLoRa(doc);
}

// ============================================
// SEND REED
// ============================================
void sendReedData(bool opened, int duration, bool forced) {
  StaticJsonDocument<256> doc;
  doc["type"]             = "REED";
  doc["node_id"]          = NODE_ID;
  doc["gateway_id"]       = GATEWAY_ID;
  doc["device_id"]        = DEVICE_ID;
  doc["door_opened"]      = opened;
  doc["duration_seconds"] = duration;
  doc["access_method"]    = "manual";
  doc["door_location"]    = "rack";
  doc["is_forced_entry"]  = forced;
  sendLoRa(doc);
}

// ============================================
// SEND VIBRATION
// SW-420 = sensor digital (on/off), tidak ada sumbu XYZ fisik.
// Nilai X/Y/Z disimulasikan agar API dapat hitung magnitude.
// ============================================
void sendVibrationData() {
  StaticJsonDocument<256> doc;
  doc["type"]       = "VIBRATION";
  doc["node_id"]    = NODE_ID;
  doc["gateway_id"] = GATEWAY_ID;
  doc["device_id"]  = DEVICE_ID;
  doc["x_axis"]     = random(-300, 300) / 100.0;
  doc["y_axis"]     = random(-300, 300) / 100.0;
  doc["z_axis"]     = 2.0 + random(0, 150) / 100.0;  // Z selalu di atas threshold
  doc["threshold"]  = 2.0;
  sendLoRa(doc);
}

// ============================================
// SEND STATUS (heartbeat)
// ============================================
void sendStatus() {
  StaticJsonDocument<128> doc;
  doc["type"]       = "STATUS";
  doc["node_id"]    = NODE_ID;
  doc["gateway_id"] = GATEWAY_ID;
  doc["payload"]    = "gateway hidup";
  doc["uptime"]     = millis() / 1000;
  sendLoRa(doc);
  Serial.println("[STATUS] SENT");
}

// ============================================
// SEND LORA — 256 byte doc
// ============================================
void sendLoRa(StaticJsonDocument<256>& doc) {
  String payload;
  serializeJson(doc, payload);
  Serial.println("[LoRa TX] -> " + payload);
  LoRa.beginPacket();
  LoRa.print(payload);
  int result = LoRa.endPacket();
  if (result) {
    totalSent++;
    blinkLED(50);
    Serial.println("[LoRa] SUCCESS");
  } else {
    totalFailed++;
    Serial.println("[LoRa] FAILED");
  }
}

// ============================================
// SEND LORA — 128 byte doc
// ============================================
void sendLoRa(StaticJsonDocument<128>& doc) {
  String payload;
  serializeJson(doc, payload);
  Serial.println("[LoRa TX] -> " + payload);
  LoRa.beginPacket();
  LoRa.print(payload);
  int result = LoRa.endPacket();
  if (result) {
    totalSent++;
    blinkLED(50);
  } else {
    totalFailed++;
  }
}
