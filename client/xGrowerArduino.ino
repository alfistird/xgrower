
// Se incluye la libreria para el uso de Ethernet ENC28J60
#include <EtherCard.h>
// Se incluye libreria para sensor de temperatura y humedad DHT11
#include <idDHT11.h>
// Libreria para manejo de strings en el armado de los parametros de get
#include <GString.h>
// Libreria para guardar valores en EEPROM
#include <EEPROM.h>
// Libreria para manejo de fechas y horas
#include <Time.h>

byte Ethernet::buffer[350];   // tcp/ip send and receive buffer /TODO: ver cual es el tamanio ideal

static unsigned long timer; // es para contar cada cuando hace el pedido de pulso
static unsigned long timerRiego; // para contar el tiempo de riego
static unsigned long timerRiegoHidroponico = 0;
static unsigned long timerComunicacionServer = 0;

// ethernet interface mac address
static byte mymac[] = { 0x74,0x69,0x69,0x2D,0x02,0x04 }; //TODO: cambiar por algo menos duro ver si hay alguna regla que se pueda seguir
//   0x74,0x69,0x69,0x2D,0x30,0x01
//	0x74,0x69,0x69,0x2D,0x02,0x02
// ethernet interface ip address
//static byte myip[] = { 10,0,0,9 };
// gateway ip address
//static byte gwip[] = { 10,0,0,2 };


// Mis variables
byte humedadGlobal = 0;
byte temperaturaGlobal = 0;
byte valorFotoresistencia = 0; // aca se pone lo que salga de la lectura del A0;
byte pinFotoresistencia = 0; // Analogo
byte pinLuz = 7;
byte pinVentilacion = 9;
byte pinAgua = 6; // antes era 10 pero daba problemas
byte pinDigitalHumedadTierra = 1;
byte pinAnalogoSoil18 = 4;  // TODO ver esto


// pines LED de feedback al usuario
byte pinLedEthernet = 5;
byte pinLedLoop = 10;

byte humedadSoil18 = 0; // TODO ver esto
byte luminosidad = 0;

boolean isEthernetInicializada = false;
boolean isDHCPInicializado = false;
boolean isDNSInicializado = false;

byte isLuzEncendida = 0;
byte isVentilacionEncendida = 0; //TODO
byte isAguaEncendida = 0;
byte estadoLuz = 0; // 0 apagado, 1 encendido, 2 vegetativo , 3 floracion
byte estadoVentilacion = 0; // 2 timer XX tiempo, 3 a cierta temperatura
byte estadoRiego = 0; //
byte customTemperatura = 0; //valores para ventilacion custom
byte customHumedad = 0;
byte isRiegoEncendido = 0;



byte cambioRecibido = 0;

// Manejo de EEPROM
const byte EEPROM_ID = 0x99;   // used to identify if valid data in EEPROM
//constants used to identify EEPROM addresses
const byte ID_ADDRESS = 0;       // the EEPROM address used to store the ID
const byte ESTADO_LUZ_ADDRESS = 1;      // the EEPROM address para luz
const byte ESTADO_VENTILACION_ADDRESS = 2; // the EEPROM address para ventilacion
const byte ESTADO_RIEGO_ADDRESS = 3; // EEPROM address
const byte CUSTOM_TEMPERATURA_ADDRESS = 5;
const byte CUSTOM_HUMEDAD_ADDRESS = 6;



char website[] PROGMEM = "pulso.xgrower.com"; //www.porrobot.com.ar
//char idEquipo[] PROGMEM = "porrobot1"; // esto es el idEquipo de la DB, es unico

//Buffer to store data para el GString con los paramtros del GET.
char gStringBufferPulso[ 80 ]; // parametros para Pulso
char gStringBufferInicioFoto[ 45 ]; // parametros para inicioFoto
char gStringBufferPaqueteFoto[ 570 ]; // parametros para paqueteFoto



//Use like Serial
void actualizarParametrosPulso()
{
	
	//Create string, passing in buffer pointer.
	GString g_Test( gStringBufferPulso );
	
	g_Test.print(F("pulso.php?id="));
	g_Test += F("porrobot1"); // TODO tiene que haber forma de poner esto en PROGMEM
	g_Test += F("&te="); // temperatura
	g_Test += temperaturaGlobal;
	g_Test += F("&hu="); // humedad
	g_Test += humedadGlobal;
	g_Test += F("&lu="); // luminosidad - fotoresistencia
	g_Test += luminosidad;
	g_Test += F("&el="); // estado luz
	g_Test += estadoLuz;
	g_Test += F("&ev="); // estado ventilacion
	g_Test += estadoVentilacion;
	g_Test += F("&ea="); // estado agua
	g_Test += estadoRiego;
	g_Test += F("&ca="); // cambio recibido
	g_Test += cambioRecibido;
	g_Test += F("&ct="); // custom temperatura
	g_Test += customTemperatura;
	g_Test += F("&ch="); // custom humedad
	g_Test += customHumedad;
	g_Test += F("&il="); // is Luz Encendida
	g_Test += isLuzEncendida;
	g_Test.end();
	
	Serial.flush();
	showString(PSTR("Parametros GET para Pulso:\r\n"));
	Serial.println(gStringBufferPulso);
	Serial.flush();
}






// INICIALIZACION DHT11
/*
Board	          int.0	  int.1	  int.2	  int.3	  int.4	  int.5
Uno, Ethernet			2	  3
*/
byte idDHT11pin = 3; //Digital pin for communications
byte idDHT11intNumber = 1; //interrupt number (must be the one that use the previous defined pin (see table above)
//declaration
void dht11_wrapper(); // must be declared before the lib initialization

// Lib instantiate
idDHT11 DHT11(idDHT11pin,idDHT11intNumber,dht11_wrapper);

// This wrapper is in charge of calling
// must be defined like this for the lib work
void dht11_wrapper() {
	DHT11.isrCallback();
}


// buffer for an outgoing data packet
//static char outCount = -1;

// configuration, as stored in EEPROM
struct Config {
	byte band;
	byte group;
	byte collect;
	word refresh;
	byte valid; // keep this as last byte
} config;


static int freeRam () {
	extern int __heap_start, *__brkval;
	int v;
	return (int) &v - (__brkval == 0 ? (int) &__heap_start : (int) __brkval);
}


void mostrarRam(){
	Serial.flush();
	showString(PSTR("RAM: "));
	Serial.println(freeRam());
	Serial.flush();
}

static void procesarRespuestaHTTP (byte status, word off, word len) {
	
	
	for(byte i = 0; i < 3 ; i++){
		digitalWrite(pinLedEthernet, HIGH);
		delay(100);
		digitalWrite(pinLedEthernet, LOW);
		delay(100);
	}
	
	// el numero despues del offset es la cantidad de chars que hay en el header
	// TODO encontrar una forma de que ese valor sea dinamico por si el server cambia el header
	char* respuesta = (char*) Ethernet::buffer + off + 159;
	
	Serial.flush();
	Serial.println(F("\nRESPUESTA del server:\r\n"));
	Serial.println(respuesta);
	Serial.println(F("\n"));
	mostrarRam();
	
	if(strstr(respuesta, "BIEN") != 0)
	{
		Serial.flush();
		showString(PSTR("TODO OK - No se hace nada.\r\n"));
		if (cambioRecibido == 1){
			cambioRecibido = 0;
		}
		timerComunicacionServer = millis();
		
		char* respCorta = respuesta + 11;  // sin el BIEN,TIME=
		Serial.flush();
		showString(PSTR("Respuesta corta:\r\n"));
		Serial.println(respCorta);
		
		// se obtiene hora del servidor
		setTime(extraerValorHora(respCorta),extraerValorMinutos(respCorta),extraerValorSegundos(respCorta),1,1,11);
		mostrarRam();
	}
	else if (strstr(respuesta, "CAMBIO") != 0)
	{
		Serial.flush();
		showString(PSTR("Se detecta cambio, se parsea.\r\n"));

		char* respCorta = respuesta + 8; // sin el CAMBIO,

		showString(PSTR("Respuesta corta:\r\n"));
		Serial.println(respCorta);

		// Extraccion de valores del objeto mensaje
		extraerValorEstadoLuz(respCorta);
		extraerValorEstadoVentilacion(respCorta);
		extraerValorEstadoAgua(respCorta);
		
		cambioRecibido = 1;
		guardarEstadoEnEeprom();
		ejecutarCicloSegunEstado();
		mostrarRam();
		} else if (strstr(respuesta, "FOIN") != 0){
		// Foto iniciada
		showString(PSTR("Se detecta FOTO INICIADA.\r\n"));
	}
	else
	{
		// TODO informara a Server y a Usuario sobre estado de error
		showString(PSTR("TODO MAL: no se entiende mensaje de server\n"));
	}
	Serial.flush();
}

byte extraerValorHora(char* respCorta){
	// El primer valor es la hora
	char valor1[3] = {(char)respCorta[11], (char)respCorta[12]};
	int intValor1 = atoi(valor1);
	return intValor1;
}

byte extraerValorMinutos(char* respCorta){
	// El segundo valor son los minutos
	char valor2[3] = {(char)respCorta[14], (char)respCorta[15]};
	int intValor2 = atoi(valor2);
	return intValor2;
}

byte extraerValorSegundos(char* respCorta){
	// El tercer valor son los segundos
	char valor3[3] = {(char)respCorta[17], (char)respCorta[18]};
	int intValor3 = atoi(valor3);
	return intValor3;
}

void extraerValorEstadoLuz(char* respCorta){
	// El primer valor es la Luz
	char valor[3] = {(char)respCorta[0], (char)respCorta[1]};
	showString(PSTR("Valor 1:\r\n"));
	Serial.println(valor);
	int intValor = atoi(valor);
	if (intValor != 99)
	estadoLuz = intValor;
	
	showString(PSTR("Estado Luz:\r\n"));
	Serial.println(estadoLuz);
}

void extraerValorEstadoVentilacion(char* respCorta){
	// El valor 2 es ventilacion
	char valor[3] = {(char)respCorta[3], (char)respCorta[4]};
	showString(PSTR("Valor 2:\r\n"));
	Serial.println(valor);
	int intValor = atoi(valor);
	if (intValor != 99)
	estadoVentilacion = intValor;
	
	showString(PSTR("Estado Ventilacion:\r\n"));
	Serial.println(estadoVentilacion);
	
	if(estadoVentilacion == 4){
		// El estado de ventilacion es Custom, se extraen valores custom
		// extraccion valor temperatura custom
		valor[0] = (char)respCorta[9];
		valor[1] = (char)respCorta[10];
		showString(PSTR("Valor 4:\r\n"));
		Serial.println(valor);
		intValor = atoi(valor);
		customTemperatura = intValor;
		showString(PSTR("Custom Temperatura:\r\n"));
		Serial.println(customTemperatura);
		Serial.flush();
		
		// extraccion valor humedad custom
		valor[0] = (char)respCorta[12];
		valor[1] = (char)respCorta[13];
		showString(PSTR("Valor 5:\r\n"));
		Serial.println(valor);
		intValor = atoi(valor);
		customHumedad = intValor;
		showString(PSTR("Custom Humedad:\r\n"));
		Serial.println(customHumedad);
		Serial.flush();
	}
}

void extraerValorEstadoAgua(char* respCorta){
	
	// El valor 3 es agua
	char valor[3] = {(char)respCorta[6], (char)respCorta[7]};
	int intValor = atoi(valor);
	estadoRiego = intValor;

	showString(PSTR("Estado Riego: "));
	Serial.println(estadoRiego);
	
	Serial.flush();
}



void guardarEstadoEnEeprom(){
	EEPROM.write(ESTADO_LUZ_ADDRESS, estadoLuz); // guarda en eeprom
	EEPROM.write(ESTADO_VENTILACION_ADDRESS, estadoVentilacion);
	EEPROM.write(ESTADO_RIEGO_ADDRESS, estadoRiego);
	EEPROM.write(CUSTOM_TEMPERATURA_ADDRESS, customTemperatura);
	EEPROM.write(CUSTOM_HUMEDAD_ADDRESS, customHumedad);
}


void inicializarEthernet(){
	digitalWrite(pinLedEthernet, HIGH);
	if (ether.begin(sizeof Ethernet::buffer, mymac) == 0){
		showString(PSTR("Failed to access Ethernet controller\r\n"));
	}
	else {
		showString(PSTR("Ethernet controller initialized\r\n"));
		isEthernetInicializada = true;
		
		
		if (!ether.dhcpSetup()){
			showString(PSTR("Failed to get configuration from DHCP\r\n"));
		}
		else {
			showString(PSTR("DHCP configuration done\r\n"));
			isDHCPInicializado = true;
			
			mostrarRam();
			Serial.flush();
			ether.printIp(F("IP Address:\t"), ether.myip);
			ether.printIp(F("Netmask:\t"), ether.mymask);
			ether.printIp(F("Gateway:\t"), ether.gwip);
			ether.printIp(F("DNS IP:\t"), ether.dnsip);
			mostrarRam();
			
			Serial.println(F("Resolviendo DNS..."));
			boolean resultadoResolucionDNS = ether.dnsLookup(website);
			if (!resultadoResolucionDNS){
				showString(PSTR("No se pudo resolver DNS Lookup ERROR\r\n"));
			}
			else {
				showString(PSTR("DNS resolution EXITOSO\r\n"));
				ether.printIp(F("IP del server:\t"), ether.hisip);
				isDNSInicializado = true;
			}
		}
	}
	delay(200);
	mostrarRam();
	digitalWrite(pinLedEthernet, LOW);
}


//SETUP
void setup(){
	
	// inicializacion de pines
	pinMode(pinLuz, OUTPUT);
	digitalWrite(pinLuz, LOW);
	
	pinMode(pinVentilacion, OUTPUT);
	digitalWrite(pinVentilacion, LOW);
	
	pinMode(pinAgua, OUTPUT);
	digitalWrite(pinAgua, LOW);
	
	pinMode(pinDigitalHumedadTierra, OUTPUT);
	digitalWrite(pinDigitalHumedadTierra, LOW);

	pinMode(14, INPUT); //pinFotoresistencia

	pinMode(pinLedEthernet, OUTPUT);
	digitalWrite(pinLedEthernet, LOW);

	pinMode(pinLedLoop, OUTPUT);
	digitalWrite(pinLedLoop, LOW);

	Serial.begin(9600);
	delay(1000); // un tiempito para hacer buen sync del serial a PC
	showString(PSTR("Metodo Setup()\r\n"));
	
	showString(PSTR("DHT11 LIB version: "));
	Serial.println(IDDHT11LIB_VERSION);
	Serial.flush();
	
	Serial.println(F("\nInicializacion de pines"));
	pinMode(0, OUTPUT);
	pinMode(1, OUTPUT);
	// pinMode(2, OUTPUT); asignado a camara
	pinMode(15, OUTPUT);
	pinMode(16, OUTPUT);
	pinMode(17, OUTPUT);
	pinMode(18, OUTPUT);
	pinMode(19, OUTPUT);
	digitalWrite(0, LOW);
	digitalWrite(1, LOW);
	///digitalWrite(2, LOW); asignado a camara
	digitalWrite(15, LOW);
	digitalWrite(16, LOW);
	digitalWrite(17, LOW);
	digitalWrite(18, LOW);
	digitalWrite(19, LOW);
	
	
	
	// ETHERNET
	showString(PSTR("\n[etherNode]\r\n"));
	inicializarEthernet();
	
	
	// Inicializacion de EEPROM
	byte id = EEPROM.read(ID_ADDRESS); // read the first byte from the EEPROM
	if( id == EEPROM_ID)
	{
		// here if the id value read matches the value saved when writing eeprom
		showString(PSTR("Usando los datos de la EEPROM\r\n"));
		estadoLuz = EEPROM.read(ESTADO_LUZ_ADDRESS);
		estadoVentilacion = EEPROM.read(ESTADO_VENTILACION_ADDRESS);
		estadoRiego = EEPROM.read(ESTADO_RIEGO_ADDRESS);
		customTemperatura = EEPROM.read(CUSTOM_TEMPERATURA_ADDRESS);
		customHumedad = EEPROM.read(CUSTOM_HUMEDAD_ADDRESS);
	}
	else
	{
		// here if the ID is not found, so write the default data
		showString(PSTR("Escribiendo datos Default en EEPROM\r\n"));
		EEPROM.write(ID_ADDRESS,EEPROM_ID); // write the ID to indicate valid data
		guardarEstadoEnEeprom();
	}
	
	ejecutarCicloSegunEstado();
	

	mostrarRam();
}







void llamadaSensorDHT11(){
	mostrarRam();
	Serial.println(F("Llamada a sensor DHT11:"));
	//	byte result = DHT11.acquireAndWait();
	
	// Esto es solo para saber el status de la lectura de temperatura
	//switch (result)
	//{
	//Serial.flush();
	//case IDDHTLIB_OK:
	//Serial.print(F("Lectura temperatura OK\r\n"));
	//break;
	//case IDDHTLIB_ERROR_CHECKSUM:
	//Serial.println(F("Error\n\r\tChecksum error"));
	//break;
	//case IDDHTLIB_ERROR_TIMEOUT:
	//Serial.println(F("Error\n\r\tTime out error"));
	//break;
	//case IDDHTLIB_ERROR_ACQUIRING:
	//Serial.println(F("Error\n\r\tAcquiring"));
	//break;
	//case IDDHTLIB_ERROR_DELTA:
	//Serial.println(F("Error\n\r\tDelta time to small"));
	//break;
	//case IDDHTLIB_ERROR_NOTSTARTED:
	//Serial.println(F("Error\n\r\tNot started"));
	//break;
	//default:
	//Serial.println(F("Unknown error"));
	//break;
	//Serial.flush();
	//}
	//mostrarRam();
	humedadGlobal = (DHT11.getHumidity());
	temperaturaGlobal = (DHT11.getCelsius());
}

void showString (PGM_P s) {
	Serial.flush();
	char c;
	while ((c = pgm_read_byte(s++)) != 0)
	Serial.print(c);
	Serial.flush();
}

void actualizarDatosSensores(){
	llamadaSensorDHT11();
	delay(500);
	
	valorFotoresistencia = analogRead(pinFotoresistencia);
	luminosidad = map(map(valorFotoresistencia, 0, 1023, 0, 99), 0, 25, 0, 99);
	//luminosidad = valorFotoresistencia;
	
	mostrarRam();
	Serial.flush();
	showString(PSTR("Valores:\r\n"));
	Serial.print(F("Valor Temperatura: "));
	Serial.println(temperaturaGlobal);
	Serial.flush();
	Serial.print(F("Valor Humedad: "));
	Serial.println(humedadGlobal);
	Serial.print(F("Valor Luminosidad: "));
	Serial.println(luminosidad);
	Serial.print(F("Valor Estado Luz: "));
	Serial.println(estadoLuz);
	Serial.print(F("Valor Estado Ventilacion: "));
	Serial.println(estadoVentilacion);
	Serial.print(F("Valor Estado Riego: "));
	Serial.println(estadoRiego);
	Serial.print(F("Valor Custom Temperatura: "));
	Serial.println(customTemperatura);
	Serial.print(F("Valor Custom Humedad: "));
	Serial.println(customHumedad);
	Serial.flush();
	mostrarRam();
}

void ejecutarCicloSegunEstado()
{
	cicloLuz();
	cicloVentilacion();
	cicloRiego();
}

void cicloLuz(){
	// LUZ
	switch (estadoLuz) {
		case 0:
		// APAGADA MANUAL
		digitalWrite(pinLuz, LOW);
		isLuzEncendida = 0;
		break;
		case 1:
		// ENCENDIDA MANUAL
		digitalWrite(pinLuz, HIGH);
		isLuzEncendida = 1;
		break;
		case 2:
		// estadoLuz vegetativo 18/6
		// harcoded de 6-0 y de 0-6
		showString(PSTR("Estado Luz: Vegetativo\r\n"));
		if(timeStatus() != timeNotSet){
			if(6 <= hour() && hour() < 24){
				digitalWrite(pinLuz, HIGH);
				isLuzEncendida = 1;
				showString(PSTR("Hora dentro de rango: Luz encendida\r\n"));
				} else {
				digitalWrite(pinLuz, LOW);
				isLuzEncendida = 0;
				showString(PSTR("Hora fuera de rango: Luz apagada\r\n"));
			}
			} else {
			Serial.println(F("ERROR: No se sabe la hora, no se puede lanzar rutina de LUZ"));
		}
		break;
		case 3:
		// floracion
		// hardcoded 6- 18 y 18-6
		showString(PSTR("Estado Luz: Floracion\r\n"));
		if(timeStatus() != timeNotSet){
			if(6 <= hour() && hour() < 18 ){
				digitalWrite(pinLuz, HIGH);
				isLuzEncendida = 1;
				showString(PSTR("Hora dentro de rango: Luz encendida\r\n"));
				} else {
				digitalWrite(pinLuz, LOW);
				isLuzEncendida = 0;
				showString(PSTR("Hora fuera de rango: Luz apagada\r\n"));
			}
			} else {
			Serial.println(F("ERROR: No se sabe la hora, no se puede lanzar rutina de LUZ"));
		}
		break;
	}
	mostrarRam();
}


void cicloVentilacion(){
	// VENTILACION
	switch (estadoVentilacion) {
		case 0:
		// APAGADA MANUAL
		digitalWrite(pinVentilacion, LOW);
		
		break;
		case 1:
		// ENCENDIDA MANUAL
		digitalWrite(pinVentilacion, HIGH);
		
		break;
		case 2:
		// estado ventilacion 2 BAJO (vegetativo)
		Serial.println(F("Estado Ventilacion: BAJO temp 30, hum 70"));
		if(temperaturaGlobal >= 30 || humedadGlobal >= 70){
			digitalWrite(pinVentilacion, HIGH);
			} else {
			digitalWrite(pinVentilacion, LOW);
		}
		break;
		case 3:
		// estado ventilacion 3 ALTO (floracion)
		Serial.println(F("Estado Ventilacion: ALTO temp 25, hum 50"));
		if(temperaturaGlobal >= 25 || humedadGlobal >= 50){
			digitalWrite(pinVentilacion, HIGH);
			} else {
			digitalWrite(pinVentilacion, LOW);
		}
		break;
		case 4:
		// estado custom, usa los settings
		Serial.print(F("Estado Ventilacion: CUSTOM temp "));
		Serial.print(customTemperatura);
		Serial.print(F(", hum "));
		Serial.println(customHumedad);
		if((temperaturaGlobal >= customTemperatura || humedadGlobal >= customHumedad) && (temperaturaGlobal != 0 && humedadGlobal != 0)){
			digitalWrite(pinVentilacion, HIGH);
			} else {
			digitalWrite(pinVentilacion, LOW);
		}
		break;
	}
	mostrarRam();
}

void cicloRiego(){
	
	// RIEGO
	// TODO controlar el caso de los millis reiniciandose cada 50 dias

	switch (estadoRiego) {
		case 0:
		// Agua OFF
		Serial.println(F("Estado Agua: OFF"));
		digitalWrite(pinAgua, LOW);
		isAguaEncendida = 0;
		break;
		
		case 1:
		// Agua ON
		Serial.println(F("Estado Agua: ON"));
		digitalWrite(pinAgua, HIGH);
		isAguaEncendida = 1;
		break;
		
		case 2:
		// Agua Hidroponica
		Serial.println(F("Estado Agua: Hidroponica"));
		if(millis() > timerRiegoHidroponico){
			//cambio de estado
			if(isAguaEncendida == 0){
				isAguaEncendida = 1;
				digitalWrite(pinAgua, HIGH);
				} else {
				isAguaEncendida = 0;
				digitalWrite(pinAgua, LOW);
			}
			timerRiegoHidroponico = millis() + 900000; // 900000 son 15 minutos
		}
		break;
	}
	mostrarRam();
	
}

void lecturaHumedadTierra(){
	// por ahora solo funciona el Soil18
	Serial.println(F("Lectura de humedad en tierra"));
	
	digitalWrite(pinDigitalHumedadTierra, HIGH);
	humedadSoil18 = map(analogRead(pinAnalogoSoil18), 700, 35, 0, 99);
	digitalWrite(pinDigitalHumedadTierra, LOW);
	
	Serial.print(F("Valor Soil18: "));
	Serial.println(humedadSoil18);
	
	// esto es para evitar problemas en el pulso si algo esta descalibrado
	if(humedadSoil18 < 0)
	humedadSoil18 = 0;
	
	if(humedadSoil18 > 99)
	humedadSoil18 = 99;
	
	mostrarRam();
}

boolean isConexionConServer(){
	if((timerComunicacionServer + 150000) < millis()){
		Serial.println(F("Se perdio comunicacion con Server hace mas de 2 minutos"));
		isEthernetInicializada = false;
		return false;
	}
	else
	{
		return true;
	}
}

// LOOP
void loop(){
	
	// Esto es necesario para que ande el objeto Ether
	ether.packetLoop(ether.packetReceive());
	
	// Actualizacion de LED Loop
	digitalWrite(pinLedLoop, HIGH);
	delay(200);
	digitalWrite(pinLedLoop, LOW);
	delay(200);

	
	
	// TODO en caso de haber tenido un CAMBIO mandar pulso confimatorio sin esperar
	
	if (millis() > timer) {
		
		Serial.print(F("\nMillis actuales: "));
		Serial.println(millis());
		
		// tiempo de ms entre pulsos
		timer = millis() + 5000;
		
		// Comprobar si Ethernet esta funcionando
		isConexionConServer();
		if(!isEthernetInicializada || !isDHCPInicializado || !isDNSInicializado){
			inicializarEthernet();
		}
		
		actualizarDatosSensores();
		
		if(ether.isLinkUp()){
			Serial.println(F("El link esta UP"));
			actualizarParametrosPulso();
			mostrarRam();
			
			// llamada al PULSO
			ether.browseUrl(PSTR("/"), gStringBufferPulso, website, procesarRespuestaHTTP);
		}
		else
		{
			Serial.println(F("El link esta DOWN"));
			inicializarEthernet();
		}
		

		if(timeStatus() != timeNotSet){
			// Se sabe la hora, se llama a rutinas
			Serial.println(F("Se sabe la hora."));
			Serial.print(F("Hora: "));
			Serial.print(hour());
			Serial.print(F(":"));
			Serial.print(minute());
			Serial.print(F(":"));
			Serial.println(second());
			Serial.flush();
			} else {
			//No se sabe que hora es, no se puede correr rutinas
			Serial.println(F("ERROR: no se sabe la hora"));
		}
		// se llama a las rutinas y comandos
		ejecutarCicloSegunEstado();
	}
	
	
	
	Serial.flush();

}


