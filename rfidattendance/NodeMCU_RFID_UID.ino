//*******************************libraries********************************
//RFID-----------------------------
#include <SPI.h>
#include <MFRC522.h>
//NodeMCU--------------------------
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
//************************************************************************
#define SS_PIN  D2  //D2
#define RST_PIN D1  //D1
//************************************************************************
MFRC522 mfrc522(SS_PIN, RST_PIN); // Create MFRC522 instance.
//************************************************************************
/* Set these to your desired credentials. */
const char *ssid = "motorola edge 50 pro";
const char *password = "1122339988";
const char* device_token  = "619afcf8e2c7f139";
//************************************************************************
String URL = "http://192.168.219.74/rfidattendance/getdata.php"; //computer IP or the server domain
String OldCardID = "";
unsigned long previousMillis = 0;
//************************************************************************
void setup() {
  delay(1000);
  Serial.begin(115200);
  SPI.begin();  // Init SPI bus
  mfrc522.PCD_Init(); // Init MFRC522 card
  
  Serial.println("\nRFID Attendance System");
  Serial.println("Initializing...");
  
  //---------------------------------------------
  connectToWiFi();
}
//************************************************************************
void loop() {
  // Check if WiFi is still connected
  if (!WiFi.isConnected()) {
    connectToWiFi();    
  }

  //---------------------------------------------
  if (millis() - previousMillis >= 15000) {
    previousMillis = millis();
    OldCardID = "";
  }
  delay(50);
  
  //---------------------------------------------
  // Look for new cards
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }
  
  // Select one of the cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  String CardID = "";
  // Convert UID to hex string
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) {
      CardID += "0";
    }
    CardID += String(mfrc522.uid.uidByte[i], HEX);
  }
  CardID.toUpperCase();
  
  Serial.println("\n✅ Card Detected!");
  Serial.print("Card UID: ");
  Serial.println(CardID);

  //---------------------------------------------
  if (CardID == OldCardID) {
    Serial.println("Card already scanned");
    return;
  } 
  OldCardID = CardID;

  //---------------------------------------------
  SendCardID(CardID);
  
  // Halt PICC
  mfrc522.PICC_HaltA();
  // Stop encryption on PCD
  mfrc522.PCD_StopCrypto1();
  
  delay(1000);
}
//************send the Card UID to the website*************
void SendCardID(String Card_uid) {
  Serial.println("Sending Card ID to server...");
  
  if (WiFi.isConnected()) {
    WiFiClient client;
    HTTPClient http;
    
    // URL encode the parameters
    String postData = "card_uid=" + Card_uid + "&device_token=" + String(device_token);
    String fullUrl = URL + "?" + postData;  // Using GET method
    
    Serial.print("Connecting to: ");
    Serial.println(fullUrl);
    
    http.begin(client, fullUrl);
    
    // Send GET request
    int httpCode = http.GET();
    
    if (httpCode > 0) {
      Serial.printf("HTTP Response code: %d\n", httpCode);
      String payload = http.getString();
      Serial.println("Server response: " + payload);
    } else {
      Serial.printf("❌ HTTP Request failed, error: %s\n", http.errorToString(httpCode).c_str());
    }
    
    http.end();
  } else {
    Serial.println("❌ WiFi not connected!");
  }
}

//********************connect to the WiFi******************
void connectToWiFi() {
    WiFi.mode(WIFI_OFF);  // Prevents reconnection issues
    delay(1000);
    WiFi.mode(WIFI_STA);
    
    Serial.print("Connecting to WiFi: ");
    Serial.println(ssid);

    WiFi.begin(ssid, password);

    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 20) {
        delay(500);
        Serial.print(".");
        attempt++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n✅ Connected to WiFi!");
        Serial.print("IP Address: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("\n❌ Failed to connect! Check WiFi credentials.");
    }
} 