const WebSocket = require('ws');
const https = require('https');
const fs = require('fs');

// Certificados SSL para HTTPS (usa tus archivos letsencrypt o similares)
const server = https.createServer({
    cert: fs.readFileSync('/ruta/a/fullchain.pem'),
    key: fs.readFileSync('/ruta/a/privkey.pem')
});

const wss = new WebSocket.Server({ server });

const ESP32_WS_URL = 'ws://192.168.137.134:81'; // IP local del ESP32 y puerto WS

wss.on('connection', (clientSocket) => {
    console.log('Cliente conectado');

    // Conectar al ESP32 cuando cliente se conecta
    const espSocket = new WebSocket(ESP32_WS_URL);

    espSocket.on('open', () => {
        console.log('Conectado a ESP32');
    });

    // Transferir mensajes del cliente al ESP32
    clientSocket.on('message', (msg) => {
        console.log('Mensaje del cliente:', msg);
        if (espSocket.readyState === WebSocket.OPEN) {
            espSocket.send(msg);
        }
    });

    // Transferir mensajes del ESP32 al cliente
    espSocket.on('message', (msg) => {
        console.log('Mensaje del ESP32:', msg);
        if (clientSocket.readyState === WebSocket.OPEN) {
            clientSocket.send(msg);
        }
    });

    // Manejar cierres y errores
    clientSocket.on('close', () => {
        console.log('Cliente desconectado');
        espSocket.close();
    });

    espSocket.on('close', () => {
        console.log('Conexión con ESP32 cerrada');
        clientSocket.close();
    });

    espSocket.on('error', (err) => {
        console.error('Error en conexión con ESP32:', err);
    });

    clientSocket.on('error', (err) => {
        console.error('Error en conexión con cliente:', err);
    });
});

server.listen(443, () => {
    console.log('Proxy WebSocket HTTPS escuchando en puerto 443');
});
