# ESP8266 Smart Irrigation Controller

## Hardware Requirements

### Components
- ESP8266 NodeMCU or Wemos D1 Mini
- PZEM-004T Energy Meter Module
- 5V Relay Module
- Jumper wires
- Breadboard or PCB
- 5V Power Supply
- Enclosure box (waterproof recommended)

### Pin Connections

| Component | ESP8266 Pin | GPIO | Description |
|-----------|-------------|------|-------------|
| PZEM RX | D4 | GPIO2 | PZEM receive data |
| PZEM TX | D9 | GPIO3 | PZEM transmit data |
| Relay IN | D2 | GPIO4 | Relay control signal |
| Status LED | D1 | GPIO5 | System status indicator |
| PZEM VCC | 5V | - | Power supply |
| PZEM GND | GND | - | Ground |
| Relay VCC | 5V | - | Power supply |
| Relay GND | GND | - | Ground |

## Arduino Libraries Required

Install these libraries through Arduino IDE Library Manager:

1. **ESP8266WiFi** - Built-in with ESP8266 core
2. **ESP8266HTTPClient** - Built-in with ESP8266 core  
3. **ArduinoJson** by Benoit Blanchon (v6.x)
4. **PZEM004Tv30** by Jakub Mandula
5. **SoftwareSerial** - Built-in

## Installation Steps

1. **Install Arduino IDE**
   - Download from [arduino.cc](https://www.arduino.cc/en/software)

2. **Add ESP8266 Board Support**
   - File → Preferences
   - Add to Additional Board Manager URLs:
     ```
     http://arduino.esp8266.com/stable/package_esp8266com_index.json
     ```
   - Tools → Board → Boards Manager
   - Search "ESP8266" and install

3. **Install Required Libraries**
   - Sketch → Include Library → Manage Libraries
   - Search and install each library listed above

4. **Configure the Code**
   - Open `smart_irrigation_controller.ino`
   - Update WiFi credentials:
     ```cpp
     const char* ssid = "YOUR_WIFI_SSID";
     const char* password = "YOUR_WIFI_PASSWORD";
     ```
   - Update server URL:
     ```cpp
     const char* serverURL = "http://your-server.com/api/v1";
     ```
   - Set unique pump ID:
     ```cpp
     #define PUMP_ID 1  // Change for each pump
     ```

5. **Upload Code**
   - Connect ESP8266 to computer via USB
   - Select board: Tools → Board → ESP8266 Boards → NodeMCU 1.0
   - Select port: Tools → Port → (your COM port)
   - Click Upload button

## Wiring Diagram

```
ESP8266 NodeMCU          PZEM-004T
    5V    ──────────────── VCC
    GND   ──────────────── GND
    D4    ──────────────── RX
    D9    ──────────────── TX

ESP8266 NodeMCU          Relay Module
    5V    ──────────────── VCC
    GND   ──────────────── GND
    D2    ──────────────── IN

ESP8266 NodeMCU          Status LED
    D1    ──────────────── Anode (+)
    GND   ──────────────── Cathode (-)
```

## PZEM-004T Wiring to Load

```
AC Power Source          PZEM-004T          Pump Motor
Live (L) ───────────────── L in ──────────── L in ─────────── Live to Motor
Neutral (N) ─────────────────────────────── N in ─────────── Neutral to Motor
                         L out ─────────── Through Relay
```

## Safety Considerations

⚠️ **ELECTRICAL SAFETY WARNING** ⚠️

- Work with AC power requires electrical expertise
- Use proper circuit breakers and fuses
- Install ground fault circuit interrupters (GFCI)
- Use weatherproof enclosures for outdoor installation
- Follow local electrical codes and regulations
- Consider hiring a certified electrician for AC connections

## Testing Procedure

1. **Power On Test**
   - Connect USB power to ESP8266
   - Check serial monitor (115200 baud)
   - Verify WiFi connection

2. **PZEM Communication Test**
   - Connect PZEM without AC load
   - Check sensor readings in serial monitor
   - Values should show 0 for current/power

3. **Server Communication Test**
   - Verify pump status API calls
   - Check sensor data transmission
   - Monitor server logs for received data

4. **Relay Control Test**
   - Test relay activation from server
   - Use multimeter to verify relay switching
   - Test with low voltage load initially

5. **Full System Test**
   - Connect to actual pump load
   - Start usage session from admin panel
   - Monitor real-time data flow
   - Test quota exceeded auto-stop

## Troubleshooting

### WiFi Connection Issues
- Check SSID and password
- Verify WiFi signal strength
- Use WiFi analyzer to check interference

### PZEM Communication Issues
- Verify wiring connections
- Check baud rate (9600 for PZEM004Tv30)
- Test with different GPIO pins

### Server Communication Issues
- Check server URL and network connectivity
- Verify API endpoints are working
- Check firewall settings

### Relay Not Working
- Verify 5V power supply
- Check relay module LED indicators
- Test relay with multimeter

## Monitoring and Maintenance

- Monitor serial output for error messages
- Check WiFi signal strength regularly
- Inspect connections for corrosion
- Update firmware when new versions available
- Backup configuration before changes

## Advanced Configuration

### Multiple Pumps
Each pump requires:
- Unique PUMP_ID in code
- Separate ESP8266 controller
- Individual database entry

### Security Enhancements
- Add API key authentication
- Use HTTPS for secure communication
- Implement device certificates

### Power Management
- Add sleep modes for battery operation
- Implement watchdog timer
- Add power failure detection