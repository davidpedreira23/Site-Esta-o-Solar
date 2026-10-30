/*
 * ESTAÇÃO METEOROLÓGICA SOLAR — firmware do ESP32
 *
 * Lê temperatura/umidade (DHT22) e luminosidade (LDR), mostra no display OLED
 * e ENVIA os dados por Wi-Fi para o site em PHP (POST JSON em /api/leituras).
 *
 * Ligações (iguais ao esquema em /hardware):
 *   DHT22 ........ GPIO 4
 *   LDR + resistor (divisor de tensão) ... GPIO 34  (ADC1: funciona junto com o Wi-Fi)
 *   OLED SSD1306 . SDA = GPIO 21 | SCL = GPIO 22 (I2C, endereço 0x3C)
 *
 * Bibliotecas (Gerenciador de Bibliotecas da Arduino IDE):
 *   Adafruit GFX Library, Adafruit SSD1306, DHT sensor library (Adafruit) + Adafruit Unified Sensor
 */

#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DHT.h>

#include "config.h"   // Wi-Fi, URL do servidor e API_KEY (veja config.h.example)

// ---------- Display OLED ----------
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

#define SDA_PIN 21
#define SCL_PIN 22

// ---------- Sensores ----------
#define DHTPIN 4
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

#define LDR_PIN 34

unsigned long ultimoEnvio = 0;
String statusEnvio = "aguardando";

// Conecta (ou reconecta) ao Wi-Fi. Espera no máximo ~10 s para não travar o display.
void conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Conectando ao Wi-Fi");
  for (int i = 0; i < 20 && WiFi.status() != WL_CONNECTED; i++) {
    delay(500);
    Serial.print(".");
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("\nConectado! IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nSem Wi-Fi (vou tentar de novo).");
  }
}

// Envia uma leitura em JSON. O servidor identifica a estação pela API_KEY (cabeçalho).
// Retorna o código HTTP (201 = criado com sucesso) ou -1 se estiver sem Wi-Fi.
int enviarLeitura(float temp, float umid, int luzPct, int ldrBruto) {
  if (WiFi.status() != WL_CONNECTED) return -1;

  char json[128];
  snprintf(json, sizeof(json),
           "{\"temperatura\":%.1f,\"umidade\":%.1f,\"luminosidade\":%d,\"ldr_bruto\":%d}",
           temp, umid, luzPct, ldrBruto);

  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", API_KEY);
  http.setTimeout(5000);
  int codigo = http.POST(json);
  http.end();

  Serial.print("POST ");
  Serial.print(json);
  Serial.print(" -> HTTP ");
  Serial.println(codigo);
  return codigo;
}

void setup() {
  Serial.begin(115200);
  Wire.begin(SDA_PIN, SCL_PIN);
  dht.begin();

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Falha ao iniciar OLED!");
    while (true);
  }
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("ESTACAO SOLAR");
  display.println("Conectando Wi-Fi...");
  display.display();

  conectarWiFi();
}

void loop() {
  float temp = dht.readTemperature();
  float umid = dht.readHumidity();

  int ldrVal = analogRead(LDR_PIN);                 // 0..4095
  int luzPct = constrain(map(ldrVal, 0, 4095, 0, 100), 0, 100);

  if (isnan(temp) || isnan(umid)) {                 // falha de leitura do DHT22
    display.clearDisplay();
    display.setCursor(0, 0);
    display.println("ERRO DHT22");
    display.display();
    Serial.println("Erro ao ler DHT22!");
    delay(2000);
    return;
  }

  // Envia no intervalo configurado (sem usar delay longo: o display continua atualizando)
  if (millis() - ultimoEnvio >= INTERVALO_ENVIO_MS || ultimoEnvio == 0) {
    ultimoEnvio = millis();
    conectarWiFi();
    int codigo = enviarLeitura(temp, umid, luzPct, ldrVal);
    if (codigo == 201)      statusEnvio = "enviado OK";
    else if (codigo == -1)  statusEnvio = "sem Wi-Fi";
    else                    statusEnvio = "erro " + String(codigo);
  }

  // ---------- Display ----------
  display.clearDisplay();
  display.setCursor(0, 0);
  display.println("== ESTACAO SOLAR ==");
  display.print("Temp: "); display.print(temp, 1); display.println(" C");
  display.print("Umid: "); display.print(umid, 1); display.println(" %");
  display.print("Luz:  "); display.print(luzPct);  display.println(" %");
  display.print("WiFi: "); display.println(WiFi.status() == WL_CONNECTED ? "ok" : "--");
  display.print("Site: "); display.println(statusEnvio);
  display.display();

  delay(2000);
}
