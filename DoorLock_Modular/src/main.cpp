#include <Arduino.h>
#include <rdm6300.h>
#include <FastLED.h>
#include <WiFi.h>
#include <WiFiMulti.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Ping.h>
#include <WebServer.h>
#include <ESPmDNS.h>
#include <Preferences.h>
#include <FS.h>
#include <SPIFFS.h>
#include <unordered_map>
#include <ctime>

WebServer server(80);
Preferences preferences;

// Constants and Definitions
#define RDM6300_RX_PIN 4
#define MOSFET 15
#define RGB_STRIP_PIN 5
#define NUM_LEDS 3

#define CACHE_FILE "/cache.json"
struct CacheEntry {
  bool hasAccess;
  time_t lastUsed;
};

const char* API_KEY = "REPLACE_WITH_YOUR_API_KEY"; // Replace with your personal API key
const char* BEARER_HEADER = "Bearer ";
std::unordered_map<String, CacheEntry> cardCache;
const time_t SEVEN_DAYS = 7 * 24 * 60 * 60;

// Global Variables
Rdm6300 rdm6300;
CRGB leds[NUM_LEDS];
WiFiMulti wifiMulti; // Create a WiFiMulti object

// Function Declarations
void setupAccessPoint();
void connectToWiFi();
void setupWiFi();
void setupLEDs();
void setupRDM6300();
void RGBAnimation();
String getMemberID(String cardID);
bool checkMemberAccess(String memberID);
void openDoorLock();
void CheckCard();
void Leds_Blue();
void Leds_Green();
void Leds_Red();
void Leds_Yellow();
void pingGoogle();
void loadCacheFromFile();
void saveCacheToFile();
void cleanCache();
void initializeSPIFFS();

int door_timer = 5000;

void setup() {
  Serial.begin(115200);
  pinMode(MOSFET, OUTPUT);
  digitalWrite(MOSFET, LOW);
  
  // Initialize NVS
  preferences.begin("doorlock", false);
  initializeSPIFFS();
  loadCacheFromFile();

  // Check if WiFi is configured
  if (!preferences.isKey("ssid")) {
    setupAccessPoint();
  } else {
    connectToWiFi();
  }

  Leds_Yellow();
  setupRDM6300();
  
  // Add your networks to the WiFiMulti object
  wifiMulti.addAP("Your_SSID", "Your_Password");
  wifiMulti.addAP("Your_SSID", "Your_Password");
  wifiMulti.addAP("Your_SSID", "Your_Password");
  
  setupWiFi();
  
  Serial.println("\nReady");
}

void handleRoot() {
  String html = "<html><body><h1>ESP32 Door Lock Configuration</h1>";
  html += "<form method='post' action='/submit'>";
  // Add form fields for SSID, password, etc.
  html += "<input type='submit' value='Save'>";
  html += "</form></body></html>";

  server.send(200, "text/html", html);
}


void setupAccessPoint() {
  // Set up the ESP32 as an Access Point
  WiFi.softAP("ESP32-DoorLock", "password");

  // Start the web server
  server.on("/", HTTP_GET, handleRoot);
  server.begin();
}

void connectToWiFi() {
  // Connect to the stored WiFi network
  String ssid = preferences.getString("ssid");
  String password = preferences.getString("password");
  WiFi.begin(ssid.c_str(), password.c_str());

  // Wait for connection
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.println("Connecting to WiFi..");
  }

  Serial.println("Connected to the WiFi network");
}

void Leds_Blue() {;
  fill_solid(leds, NUM_LEDS, CRGB::Blue);
  FastLED.show();
}

void Leds_Green() {
  fill_solid(leds, NUM_LEDS, CRGB::Green);
  FastLED.show();
}

void Leds_Red() {
  fill_solid(leds, NUM_LEDS, CRGB::Red);
  FastLED.show();
}

void Leds_Yellow() {
  FastLED.addLeds<NEOPIXEL, RGB_STRIP_PIN>(leds, NUM_LEDS);
  fill_solid(leds, NUM_LEDS, CRGB::Yellow);
  FastLED.show();
}

void Leds_no_wifi_animation(){
  //make the leds blink rapidly to indicate no wifi
  for (int i = 0; i < 5; i++) {
    fill_solid(leds, NUM_LEDS, CRGB::Red);
    FastLED.show();
    delay(100);
    fill_solid(leds, NUM_LEDS, CRGB::Black);
    FastLED.show();
    delay(100);
  }
}

void setupWiFi() {
  // Replace the WiFi.begin() with wifiMulti.run()
  while (wifiMulti.run() != WL_CONNECTED) {
    delay(500);
    Serial.println("Connecting to WiFi..");
    Leds_no_wifi_animation();
  }
 
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.println("Connecting to WiFi..");
    Leds_no_wifi_animation();
  }

  Serial.println("Connected to the WiFi network");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  Serial.println(WiFi.RSSI());
  pingGoogle();
}

void check_wifi(){
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected");
    setupWiFi();
  }
}

void pingGoogle(){
  if(WiFi.status() != WL_CONNECTED){
    Serial.println("WiFi not connected");
    return;
  }
  bool success = Ping.ping("www.google.com", 3);
  if(!success){
    Serial.println("Ping failed");
    Leds_no_wifi_animation();
    return;
  }
  Serial.println("Ping succesful. I am ready.");
  Leds_Blue();
}

void setupRDM6300() {
  rdm6300.begin(RDM6300_RX_PIN);
}

String getMemberID(String cardID) {
    HTTPClient http; //init http client
    DynamicJsonDocument doc(1024); //json document for parsing response

    Serial.println("API Request for getMemberID:");
    Serial.println("https://fabman.io/api/v1/members?keyType=em4102&keyToken=" + cardID + "&limit=50");
    Serial.println("Authorization: " + String(BEARER_HEADER) + String(API_KEY));

    String authHeader = String(BEARER_HEADER) + String(API_KEY); //create auth header
    http.begin("https://fabman.io/api/v1/members?keyType=em4102&keyToken=" + cardID + "&limit=50");
    http.addHeader("Authorization", authHeader.c_str());
    int httpResponseCode = http.GET();
    
    String payload = http.getString();
    Serial.print("HTTP Response Code: ");
    Serial.println(httpResponseCode);
    Serial.println("API Response for getMemberID:");
    Serial.println(payload);

    if (httpResponseCode == 200 && payload != "[]" ) {
        deserializeJson(doc, payload);
        String memberID = doc[0]["id"].as<String>();
        return memberID;
    } else if (httpResponseCode == 409) {
        // Handle optimistic locking conflict
        // Fetch the latest version of the entity, merge changes, and retry
    }
    
    http.end();
    return "";
}

bool checkMemberAccess(String memberID) {
    if (memberID == "") {
        return false;
    }
    HTTPClient http;
    DynamicJsonDocument doc(1024);

    String authHeader = String(BEARER_HEADER) + String(API_KEY);
    http.begin("https://fabman.io/api/v1/members/" + memberID + "/trainings");
    http.addHeader("Authorization", authHeader.c_str());
    int httpResponseCode = http.GET();
    if (httpResponseCode == 200) {
        String payload = http.getString();

        deserializeJson(doc, payload);
        for (JsonObject training : doc.as<JsonArray>()) {
            if (training["trainingCourse"] == 1031) {
                return true;
            }
        }
    } else if (httpResponseCode == 429) {
        // Handle rate limiting
        delay(2000); // Wait for 2 seconds before retrying
        return checkMemberAccess(memberID); // Retry the request
    }
    
    http.end();
    return false;
}

void openDoorLock() {
  digitalWrite(MOSFET, HIGH);
  delay(door_timer);
  digitalWrite(MOSFET, LOW);
}

void initializeSPIFFS() {
  if (!SPIFFS.begin(true)) {
    Serial.println("Failed to mount SPIFFS. Formatting...");
    while (true) { delay(1000); }
  }
  Serial.println("SPIFFS mounted successfully.");
}

void loadCacheFromFile() {
  if (!SPIFFS.exists(CACHE_FILE)) {
    Serial.println("No cache file found.");
    return;
  }

  File file = SPIFFS.open(CACHE_FILE, "r");
  if (!file) {
    Serial.println("Failed to open cache file for reading.");
    return;
  }

  DynamicJsonDocument doc(2048);
  DeserializationError error = deserializeJson(doc, file);
  if (error) {
    Serial.print("Failed to parse cache file: ");
    Serial.println(error.c_str());
    file.close();
    return;
  }

  for (JsonPair kv : doc.as<JsonObject>()) {
    String cardID = kv.key().c_str();
    JsonObject entry = kv.value();
    cardCache[cardID] = { entry["hasAccess"], entry["lastUsed"] };
  }

  file.close();
  Serial.println("Cache loaded successfully from file.");
}

void saveCacheToFile() {
  DynamicJsonDocument doc(2048);

  for (const auto& kv : cardCache) {
    JsonObject entry = doc.createNestedObject(kv.first);
    entry["hasAccess"] = kv.second.hasAccess;
    entry["lastUsed"] = kv.second.lastUsed;
  }

  File file = SPIFFS.open(CACHE_FILE, "w");
  if (!file) {
    Serial.println("Failed to open cache file for writing.");
    return;
  }

  if (serializeJson(doc, file) == 0) {
    Serial.println("Failed to write cache data to file.");
  } else {
    Serial.println("Cache saved successfully to file.");
  }

  file.close();
}

void cleanCache() {
  time_t currentTime = time(nullptr);

  for (auto it = cardCache.begin(); it != cardCache.end();) {
    if (currentTime - it->second.lastUsed > SEVEN_DAYS) {
      Serial.print("Removing stale card: ");
      Serial.println(it->first);
      it = cardCache.erase(it); // Remove from memory
    } else {
      ++it;
    }
  }

  saveCacheToFile(); // Save updated cache to file
}

void CheckCard() {
  if (rdm6300.get_new_tag_id()) {
    int cardCode = rdm6300.get_tag_id(); // Get the raw card code as a uint32_t
    String cardID = "01" + String(cardCode, HEX); // Convert it to a string

    Serial.print("Card ID: ");
    Serial.println(cardID);

    // Check if the cardID is already in the cache
    if (cardCache.find(cardID) != cardCache.end()) {
      // Use cached result
      Serial.println("Card found in cache.");
      cardCache[cardID].lastUsed = time(nullptr); // Update last used timestamp
      saveCacheToFile();

      if (cardCache[cardID].hasAccess) {
        Leds_Green();
        openDoorLock();
      } else {
        Leds_Red();
        delay(1000);
      }
    } else {
      check_wifi();
      Serial.println("Card not found in cache. Querying backend...");
      String memberID = getMemberID(cardID);
      bool hasAccess = false;
      hasAccess = checkMemberAccess(memberID);
      cardCache[cardID] = {hasAccess, time(nullptr)};
      saveCacheToFile();

      if (hasAccess) {
        Leds_Green();
        openDoorLock();
      } else {
        Leds_Red();
        delay(1000);
      }
    }
  }
  cleanCache();
  delay(10);
}

void loop() {
  server.handleClient();
  check_wifi();
  Leds_Blue();
  CheckCard();
}