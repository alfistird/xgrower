//Board = Arduino Uno
#define __AVR_ATmega328P__
#define ARDUINO 101
#define F_CPU 16000000L
#define __AVR__
extern "C" void __cxa_pure_virtual() {;}

void actualizarParametrosPulso();
void dht11_wrapper();
static int freeRam ();
void mostrarRam();
static void procesarRespuestaHTTP (byte status, word off, word len);
byte extraerValorHora(char* respCorta);
byte extraerValorMinutos(char* respCorta);
byte extraerValorSegundos(char* respCorta);
void extraerValorEstadoLuz(char* respCorta);
void extraerValorEstadoVentilacion(char* respCorta);
void extraerValorEstadoAgua(char* respCorta);
void guardarEstadoEnEeprom();
void inicializarEthernet();
//
void llamadaSensorDHT11();
void showString (PGM_P s);
void actualizarDatosSensores();
void ejecutarCicloSegunEstado();
void cicloLuz();
void cicloVentilacion();
void cicloRiego();
void lecturaHumedadTierra();
boolean isConexionConServer();
//

#include "C:\Program Files (x86)\Arduino\hardware\arduino\variants\standard\pins_arduino.h" 
#include "C:\Program Files (x86)\Arduino\hardware\arduino\cores\arduino\arduino.h"
#include "D:\Dropbox\PorroBot\repo\xGrowerArduino\xGrowerArduino.ino"
