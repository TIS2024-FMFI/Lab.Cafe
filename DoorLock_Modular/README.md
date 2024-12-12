<h1 align="center">DoorLock-2023</h1>

<p align="center">
  <strong>An ESP32-based RFID Door Lock System with Fabman API Integration and Custom WiFi Configuration</strong>
</p>

## Introduction
<p>DoorLock-2023 is an advanced door access control system that leverages the capabilities of the ESP32 DevKit V4. It integrates with the Fabman API for access management and features a custom WiFi setup for easy configuration. This versatile system is compatible with any ESP32 module and is enhanced with an extendable antenna for improved WiFi connectivity.</p>

## Requirements
<ul>
  <li>PlatformIO IDE</li>
  <li>Any ESP32 module (ESP32 DevKit V4 with an extendable antenna recommended)</li>
</ul>

## Hardware Components
<ul>
  <li>ESP32 DevKit V4: Ideal for its extendable antenna, enhancing WiFi connectivity. Compatible with any ESP32 module</li>
  <li>RDM6300 125MHz RFID Reader: For RFID tag reading in access control systems</li>
  <li>External WiFi Antenna: Enhances the WiFi range and stability for the ESP32</li>
  <li>18650 Single Cell Battery: Provides the primary power source for the system</li>
  <li>18650 Battery Holder with 5V Step-Up: Ensures a consistent power supply and includes a backup capability</li>
  <li>IRF540N MOSFET: Controls the electromagnetic lock</li>
  <li>WS2812 Addressable LED Strip: Used for visual feedback and status indicators</li>
</ul>

## Libraries
<p>The project requires the following libraries:</p>
<ul>
  <li>rdm6300</li>
  <li>FastLED</li>
  <li>ArduinoJson</li>
  <li>ESP32Ping</li>
</ul>
<p>These libraries can be added through PlatformIO's library management system.</p>

## Installation
<ol>
  <li><strong>Setting Up PlatformIO:</strong> Download and install PlatformIO, available as a standalone IDE or as a plugin for VSCode.</li>
  <li><strong>Cloning the Repository:</strong> Clone the DoorLock-2023 repository from GitHub using <code>git clone https://github.com/LabCafe/DoorLock-2023.git</code></li>
  <li><strong>Opening the Project:</strong> Open PlatformIO, navigate to 'Open Project', and select the cloned DoorLock-2023 directory.</li>
  <li><strong>Installing Libraries:</strong> Once the project is open in PlatformIO, the required libraries should be automatically detected from the <code>platformio.ini</code> file. If they are not installed automatically, install them manually through PlatformIO's library manager.</li>
  <li><strong>Configuring WiFi Settings:</strong> Initially, the system starts in Access Point mode, allowing you to connect to its network (SSID: 'ESP32-DoorLock', Password: 'password'). Open a browser and navigate to the provided IP address to access the configuration page and set your custom WiFi network credentials.</li>
  <li><strong>Uploading the Code:</strong> Connect your ESP32 board to your computer and use PlatformIO's upload feature to program the device.</li>
</ol>

## Usage
<p>After uploading the code to your ESP32 board and configuring the WiFi settings, the system will be ready for operation. Present RFID tags to the reader for authenticated door access control, managed seamlessly through the Fabman API.</p>

<footer>
  <p align="center">© 2023 DoorLock-2023 Project</p>
</footer>
