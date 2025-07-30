/*
 * Smart Irrigation Billing System - ESP8266 Controller
 * 
 * This code interfaces with PZEM-004T energy meter and controls
 * irrigation pump via relay based on server commands.
 * 
 * Hardware Requirements:
 * - ESP8266 (NodeMCU/Wemos D1)
 * - PZEM-004T Energy Meter
 * - 5V Relay Module
 * - Power Supply 5V
 * 
 * Pin Connections:
 * - PZEM RX -> GPIO2 (D4)
 * - PZEM TX -> GPIO3 (D9) 
 * - Relay Control -> GPIO4 (D2)
 * - Status LED -> GPIO5 (D1) [Optional]
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <PZEM004Tv30.h>
#include <SoftwareSerial.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// Server Configuration
const char* serverURL = "http://your-server.com/api/v1";
const char* apiKey = "your-api-key"; // Optional for authentication

// Hardware Configuration
#define PZEM_RX_PIN 2   // D4
#define PZEM_TX_PIN 3   // D9
#define RELAY_PIN 4     // D2
#define STATUS_LED 5    // D1
#define PUMP_ID 1       // Configure for each pump

// PZEM Communication
SoftwareSerial pzemSerial(PZEM_RX_PIN, PZEM_TX_PIN);
PZEM004Tv30 pzem(pzemSerial);

// Global Variables
int sessionId = 0;
bool pumpActive = false;
bool wifiConnected = false;
unsigned long lastSensorRead = 0;
unsigned long lastStatusCheck = 0;
const unsigned long SENSOR_INTERVAL = 5000;  // 5 seconds
const unsigned long STATUS_INTERVAL = 10000; // 10 seconds

// Function Declarations
void setupWiFi();
void checkPumpStatus();
void sendSensorData();
void controlRelay(bool state);
void blinkLED(int times);
void printSensorData();

void setup() {
    Serial.begin(115200);
    Serial.println("\n=== Smart Irrigation System ESP8266 ===");
    
    // Initialize pins
    pinMode(RELAY_PIN, OUTPUT);
    pinMode(STATUS_LED, OUTPUT);
    digitalWrite(RELAY_PIN, LOW);
    digitalWrite(STATUS_LED, LOW);
    
    // Initialize PZEM
    Serial.println("Initializing PZEM sensor...");
    blinkLED(2);
    
    // Setup WiFi
    setupWiFi();
    
    Serial.println("System ready!");
    blinkLED(3);
}

void loop() {
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        wifiConnected = false;
        digitalWrite(STATUS_LED, LOW);
        Serial.println("WiFi disconnected, attempting reconnection...");
        setupWiFi();
        return;
    }
    
    wifiConnected = true;
    digitalWrite(STATUS_LED, HIGH);
    
    unsigned long currentTime = millis();
    
    // Check pump status from server
    if (currentTime - lastStatusCheck >= STATUS_INTERVAL) {
        checkPumpStatus();
        lastStatusCheck = currentTime;
    }
    
    // Send sensor data if pump is active
    if (pumpActive && (currentTime - lastSensorRead >= SENSOR_INTERVAL)) {
        sendSensorData();
        lastSensorRead = currentTime;
    }
    
    delay(1000);
}

void setupWiFi() {
    Serial.print("Connecting to WiFi: ");
    Serial.println(ssid);
    
    WiFi.begin(ssid, password);
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi connected successfully!");
        Serial.print("IP address: ");
        Serial.println(WiFi.localIP());
        wifiConnected = true;
    } else {
        Serial.println("\nWiFi connection failed!");
        wifiConnected = false;
    }
}

void checkPumpStatus() {
    if (!wifiConnected) return;
    
    WiFiClient client;
    HTTPClient http;
    
    String url = String(serverURL) + "/pump/status/" + String(PUMP_ID);
    http.begin(client, url);
    http.addHeader("Content-Type", "application/json");
    
    int httpCode = http.GET();
    
    if (httpCode == 200) {
        String payload = http.getString();
        
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, payload);
        
        if (!error) {
            String relayStatus = doc["relay_status"].as<String>();
            bool shouldBeActive = (relayStatus == "ON");
            
            if (doc["current_session"].is<JsonObject>()) {
                sessionId = doc["current_session"]["session_id"] | 0;
            } else {
                sessionId = 0;
            }
            
            if (shouldBeActive != pumpActive) {
                pumpActive = shouldBeActive;
                controlRelay(pumpActive);
                
                Serial.print("Pump status changed: ");
                Serial.println(pumpActive ? "ON" : "OFF");
                if (sessionId > 0) {
                    Serial.print("Session ID: ");
                    Serial.println(sessionId);
                }
            }
        } else {
            Serial.println("Error parsing pump status response");
        }
    } else {
        Serial.print("Error checking pump status. HTTP code: ");
        Serial.println(httpCode);
    }
    
    http.end();
}

void sendSensorData() {
    if (!wifiConnected || sessionId == 0) return;
    
    // Read sensor data
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    float frequency = pzem.frequency();
    float pf = pzem.pf();
    
    // Check if readings are valid
    if (isnan(voltage) || isnan(current)) {
        Serial.println("Error reading PZEM sensor data");
        return;
    }
    
    // Print sensor data for debugging
    printSensorData();
    
    // Prepare JSON payload
    DynamicJsonDocument doc(512);
    doc["pump_id"] = PUMP_ID;
    doc["session_id"] = sessionId;
    doc["voltage"] = voltage;
    doc["current"] = current;
    doc["power"] = power;
    doc["energy"] = energy;
    doc["frequency"] = frequency;
    doc["power_factor"] = pf;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    // Send to server
    WiFiClient client;
    HTTPClient http;
    
    String url = String(serverURL) + "/sensor/data";
    http.begin(client, url);
    http.addHeader("Content-Type", "application/json");
    
    int httpCode = http.POST(jsonString);
    
    if (httpCode == 200) {
        String response = http.getString();
        
        DynamicJsonDocument responseDoc(512);
        DeserializationError error = deserializeJson(responseDoc, response);
        
        if (!error) {
            if (responseDoc["quota_exceeded"] == true) {
                Serial.println("⚠️  QUOTA EXCEEDED - Stopping pump!");
                pumpActive = false;
                sessionId = 0;
                controlRelay(false);
                blinkLED(5); // Alert blink pattern
            }
            
            String relayCommand = responseDoc["relay_command"].as<String>();
            if (relayCommand == "OFF") {
                pumpActive = false;
                controlRelay(false);
                Serial.println("Server command: Pump OFF");
            }
        }
    } else {
        Serial.print("Error sending sensor data. HTTP code: ");
        Serial.println(httpCode);
    }
    
    http.end();
}

void controlRelay(bool state) {
    digitalWrite(RELAY_PIN, state ? HIGH : LOW);
    Serial.print("Relay: ");
    Serial.println(state ? "ON" : "OFF");
}

void blinkLED(int times) {
    for (int i = 0; i < times; i++) {
        digitalWrite(STATUS_LED, HIGH);
        delay(200);
        digitalWrite(STATUS_LED, LOW);
        delay(200);
    }
}

void printSensorData() {
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    float frequency = pzem.frequency();
    float pf = pzem.pf();
    
    Serial.println("--- PZEM Sensor Data ---");
    Serial.print("Voltage: "); Serial.print(voltage); Serial.println(" V");
    Serial.print("Current: "); Serial.print(current); Serial.println(" A");
    Serial.print("Power: "); Serial.print(power); Serial.println(" W");
    Serial.print("Energy: "); Serial.print(energy); Serial.println(" kWh");
    Serial.print("Frequency: "); Serial.print(frequency); Serial.println(" Hz");
    Serial.print("Power Factor: "); Serial.println(pf);
    Serial.println("------------------------");
}