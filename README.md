# Smart Irrigation Billing System

A comprehensive Laravel-based system for monitoring and billing electricity usage of irrigation pumps with real-time IoT integration.

## Features

### 🔧 Admin Panel (Laravel Filament)
- **User Management**: Manage farmers and admin accounts
- **Pump Management**: CRUD operations for water pumps
- **Usage Sessions**: Real-time monitoring of pump usage
- **Billing System**: Automated billing and payment tracking
- **Tariff Management**: Flexible electricity rate configuration
- **Real-time Dashboard**: Live monitoring with charts and statistics

### 🌐 API Endpoints for ESP8266 Integration
- `POST /api/v1/usage/start` - Start a new usage session
- `POST /api/v1/usage/update` - Update real-time consumption
- `POST /api/v1/usage/stop` - Stop session and generate billing
- `GET /api/v1/pump/status/{id}` - Get pump status and relay control
- `POST /api/v1/sensor/data` - Send PZEM sensor readings

### 📊 Real-time Monitoring
- Live power consumption tracking
- Quota management with auto-stop functionality
- Usage statistics and analytics
- Billing and payment tracking

## System Architecture

### Database Schema
1. **users** - Farmer and admin accounts
2. **pumps** - Water pump configurations
3. **usage_sessions** - Usage tracking with quotas
4. **sensor_readings** - Real-time PZEM sensor data
5. **billings** - Billing and payment records
6. **tariffs** - Electricity rate management

### Hardware Integration
- **ESP8266** - IoT controller
- **PZEM-004T** - Energy meter sensor
- **Relay Module** - Pump control
- **HTTP Communication** - Real-time data transmission

## Installation

### Prerequisites
- PHP 8.1+
- MySQL/MariaDB
- Composer
- Node.js (for frontend assets)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Rasiharunar/smart-irrigation-billing.git
   cd smart-irrigation-billing
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smart_irrigation_billing
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

7. **Access the admin panel**
   - URL: `http://localhost:8000/admin`
   - Default admin: `admin@smartirrigation.com`
   - Password: `password`

## API Usage

### Starting a Usage Session
```http
POST /api/v1/usage/start
Content-Type: application/json

{
    "pump_id": 1,
    "user_id": 2,
    "quota_kwh": 2.5
}
```

### Updating Power Consumption
```http
POST /api/v1/usage/update
Content-Type: application/json

{
    "session_id": 1,
    "current_kwh": 1.25
}
```

### Sending Sensor Data
```http
POST /api/v1/sensor/data
Content-Type: application/json

{
    "pump_id": 1,
    "session_id": 1,
    "voltage": 230.5,
    "current": 4.2,
    "power": 967.1,
    "energy": 1.25,
    "frequency": 50.0,
    "power_factor": 0.95
}
```

## ESP8266 Arduino Code Example

```cpp
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <PZEM004Tv30.h>

// WiFi credentials
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// Server configuration
const char* serverURL = "http://your-server.com/api/v1";

// PZEM sensor
PZEM004Tv30 pzem(2, 3); // RX, TX pins

// Pump configuration
int relayPin = 4;
int pumpId = 1;
int sessionId = 0;
bool pumpActive = false;

void setup() {
    Serial.begin(115200);
    pinMode(relayPin, OUTPUT);
    digitalWrite(relayPin, LOW);
    
    // Connect to WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.println("Connecting to WiFi...");
    }
    Serial.println("WiFi connected!");
}

void loop() {
    if (WiFi.status() == WL_CONNECTED) {
        checkPumpStatus();
        
        if (pumpActive) {
            sendSensorData();
        }
        
        delay(5000); // Update every 5 seconds
    }
}

void checkPumpStatus() {
    HTTPClient http;
    http.begin(String(serverURL) + "/pump/status/" + String(pumpId));
    
    int httpCode = http.GET();
    if (httpCode == 200) {
        String payload = http.getString();
        
        DynamicJsonDocument doc(1024);
        deserializeJson(doc, payload);
        
        String relayStatus = doc["relay_status"];
        pumpActive = (relayStatus == "ON");
        sessionId = doc["current_session"]["session_id"] | 0;
        
        digitalWrite(relayPin, pumpActive ? HIGH : LOW);
    }
    
    http.end();
}

void sendSensorData() {
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    float frequency = pzem.frequency();
    float pf = pzem.pf();
    
    if (!isnan(voltage) && !isnan(current)) {
        HTTPClient http;
        http.begin(String(serverURL) + "/sensor/data");
        http.addHeader("Content-Type", "application/json");
        
        DynamicJsonDocument doc(512);
        doc["pump_id"] = pumpId;
        doc["session_id"] = sessionId;
        doc["voltage"] = voltage;
        doc["current"] = current;
        doc["power"] = power;
        doc["energy"] = energy;
        doc["frequency"] = frequency;
        doc["power_factor"] = pf;
        
        String jsonString;
        serializeJson(doc, jsonString);
        
        int httpCode = http.POST(jsonString);
        if (httpCode == 200) {
            String response = http.getString();
            
            DynamicJsonDocument responseDoc(512);
            deserializeJson(responseDoc, response);
            
            if (responseDoc["quota_exceeded"] == true) {
                digitalWrite(relayPin, LOW);
                pumpActive = false;
                Serial.println("Quota exceeded - pump stopped");
            }
        }
        
        http.end();
    }
}
```

## System Workflow

### 1. Pre-Usage
- User opens admin panel or mobile app
- Requests quota: "I want to use 2 kWh"
- System stores quota in database
- Relay activates → pump turns ON

### 2. During Usage
- PZEM reads real-time power consumption
- ESP8266 accumulates kWh usage
- Data sent to Laravel server via HTTP
- System monitors quota vs actual usage

### 3. Auto-Stop
- When consumption ≥ quota:
- Relay deactivates → pump turns OFF
- UI displays: "Quota exhausted, please top up"
- Billing record generated automatically

## Default Accounts

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Admin | admin@smartirrigation.com | password | System administrator |
| Farmer | farmer@example.com | password | Sample farmer account |

## Hardware Requirements

- ESP8266 NodeMCU/Wemos D1
- PZEM-004T Energy Meter
- 5V Relay Module
- Contactor/MCB for pump control
- 5V Power Supply
- WiFi network connectivity

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Create an issue on GitHub
- Email: support@smartirrigation.com

## Acknowledgments

- Laravel Filament for the amazing admin panel
- PZEM-004T for reliable energy measurement
- ESP8266 community for IoT integration examples