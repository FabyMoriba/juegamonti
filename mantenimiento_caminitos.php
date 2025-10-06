<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'servicio') {
    header("Location: inicio.php");
    exit();
}
include 'php/conexion.php'; // <-- AGREGA ESTO
$nombre_tecnico = $_SESSION['usuario'];
$fecha_actual = date('d/m/Y H:i:s');
$contenido_activo = isset($_GET['seccion']) ? $_GET['seccion'] : 'inicio' 
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Mantenimiento - Verificación de Componentes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px 0;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 20px;
        }
        
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .connected {
            background-color: var(--success);
            box-shadow: 0 0 8px var(--success);
        }
        
        .disconnected {
            background-color: var(--danger);
        }
        
        .pruebas-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .card-prueba {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-prueba:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background-color: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }
        
        h2, h3 {
            color: var(--primary);
        }
        
        h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
        }
        
        h4 {
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        p {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .button-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: scale(1.03);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .test-counter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .status-indicator-small {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #e74c3c;
            transition: background 0.3s ease;
        }
        
        .status-active {
            background: var(--success);
        }
        
        .button-test-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .button-test-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        
        .button-test-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .estado-boton {
            padding: 20px;
            margin: 10px 0;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .verde { 
            background-color: var(--success);
            animation: pulse 0.5s;
        }
        
        .rojo { 
            background-color: var(--danger);
        }
        
        .gris { 
            background-color: #6c757d;
        }
        
        .sensor-display {
            height: 120px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 15px 0;
            transition: background-color 0.5s ease;
        }
        
        .sensor-black {
            background-color: #2c3e50;
            color: white;
        }
        
        .sensor-white {
            background-color: #ecf0f1;
            color: #2c3e50;
            border: 2px solid #bdc3c7;
        }
        
        .buzzer-status {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: bold;
        }
        
        .buzzer-on {
            background-color: var(--warning);
            color: white;
            animation: pulse 1s infinite;
        }
        
        .buzzer-off {
            background-color: var(--light);
            color: var(--dark);
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .log-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .log-content {
            height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            font-family: monospace;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
        }
        
        .log-entry {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .log-time {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .log-message {
            color: var(--dark);
        }
        
        .log-success {
            color: var(--success);
        }
        
        .log-warning {
            color: var(--warning);
        }
        
        .log-danger {
            color: var(--danger);
        }
        
        .log-info {
            color: var(--secondary);
        }
        
        .completed-test {
            color: var(--success);
            font-weight: bold;
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(39, 174, 96, 0.1);
            border-radius: 5px;
            border-left: 4px solid var(--success);
        }
        
        .verification-completed {
            background-color: var(--success);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            animation: celebrate 2s ease-in-out;
        }
        
        @keyframes celebrate {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .connection-status {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 0.9em;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-dot.connected {
            background-color: var(--success);
            box-shadow: 0 0 8px var(--success);
        }
        
        .status-dot.disconnected {
            background-color: var(--danger);
        }
        
        .botones-feedback {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid var(--secondary);
        }
        
        .boton-event {
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            background-color: white;
            border-left: 3px solid var(--success);
        }
        
        .boton-event.inicio {
            border-left-color: var(--primary);
        }
        
        .boton-event.fin {
            border-left-color: var(--warning);
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .back-button {
            position: absolute;
            top: 10px;
            left: 1040px;
            background-color: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-button:hover {
            background-color: white;
            color: var(--primary);
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .pruebas-container {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .status-indicator {
                margin-top: 15px;
            }
            
            .button-test-section {
                grid-template-columns: 1fr;
            }
            
            .back-button {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 15px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <!-- Botón Atrás agregado aquí -->
            <button class="back-button" onclick="window.location.href='mantenimiento.php'">
                <i class="fas fa-arrow-left"></i> Atrás
            </button>
            
            <div class="header-content">
                <div>
                    <h1>Sistema de Mantenimiento</h1>
                    <p>Verificación de componentes: Botones, Sensor y Buzzer</p>
                </div>
                <div class="status-indicator">
                    <div class="status-dot disconnected" id="statusDot"></div>
                    <span id="statusText">Desconectado</span>
                </div>
            </div>
        </header>

        <!-- Estado de conexión MQTT -->
        <div class="connection-status">
            <div class="status-dot disconnected" id="connection-dot"></div>
            <span id="connection-text">Conectando MQTT...</span>
        </div>
        
        <h2>Pruebas de Componentes</h2>
        <div class="pruebas-container">
            <!-- Prueba del Buzzer -->
            <div class="card-prueba">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-bell"></i></div>
                    <h3>Prueba de Buzzer</h3>
                </div>
                <p>Verifique que el buzzer emite sonido (5 pruebas máx.)</p>
                
                <div class="button-container">
                    <button class="btn btn-warning" id="btnBuzzerOn" disabled>
                        <i class="fas fa-power-off"></i> Encender
                    </button>
                    <button class="btn btn-danger" id="btnBuzzerOff" disabled>
                        <i class="fas fa-stop"></i> Apagar
                    </button>
                </div>
                
                <div class="test-counter">
                    <span>Pruebas realizadas:</span>
                    <span id="buzzer-count">0/5</span>
                </div>
                
                <div class="buzzer-status buzzer-off" id="buzzerStatus">
                    BUZZER APAGADO
                </div>
                
                <div id="buzzer-result"></div>
                <div id="buzzer-completed"></div>
            </div>
            
            <!-- Prueba de Botones Físicos -->
            <div class="card-prueba">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-toggle-on"></i></div>
                    <h3>Monitoreo de Botones Físicos</h3>
                </div>
                <p>Presione los botones físicos INICIO y FIN para probarlos (5 veces cada uno)</p>
                
                <div class="button-test-section">
                    <div class="button-test-card">
                        <div class="button-test-header">
                            <h4>🔘 Botón Inicio</h4>
                        </div>
                        <div class="estado-boton gris" id="btn-inicio-status">
                            INICIO: Esperando...
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span>Pruebas:</span>
                            <span id="btn-inicio-count">0/5</span>
                        </div>
                    </div>
                    
                    <div class="button-test-card">
                        <div class="button-test-header">
                            <h4>⭕ Botón Fin</h4>
                        </div>
                        <div class="estado-boton gris" id="btn-fin-status">
                            FIN: Esperando...
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span>Pruebas:</span>
                            <span id="btn-fin-count">0/5</span>
                        </div>
                    </div>
                </div>

                <!-- Área de feedback de botones físicos -->
                <div class="botones-feedback">
                    <h4 style="margin-bottom: 10px;">Eventos de Botones Físicos:</h4>
                    <div id="botones-events">
                        <p style="color: #7f8c8d; font-style: italic; text-align: center;">
                            Esperando eventos de botones físicos...
                        </p>
                    </div>
                </div>

                <div class="button-container">
                    <button class="btn btn-primary" id="btnTestInicio" disabled>
                        <i class="fas fa-search"></i> Probar Inicio
                    </button>
                    <button class="btn btn-primary" id="btnTestFin" disabled>
                        <i class="fas fa-search"></i> Probar Fin
                    </button>
                </div>
                
                <div id="botones-result"></div>
                <div id="botones-completed"></div>
            </div>
            
            <!-- Prueba de Sensor de Color -->
            <div class="card-prueba">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                    <h3>Prueba de Sensor de Color</h3>
                </div>
                <p>Verifique la detección de colores (blanco/negro) por el sensor (5 pruebas máx.)</p>
                
                <button class="btn btn-danger" id="btnTestSensor" style="width: 100%; margin-bottom: 15px;" disabled>
                    <i class="fas fa-search"></i> Leer Sensor
                </button>
                
                <div class="test-counter">
                    <span>Pruebas realizadas:</span>
                    <span id="sensor-count">0/5</span>
                </div>
                
                <div class="sensor-display sensor-white" id="sensorDisplay">
                    BLANCO detectado
                </div>
                
                <div style="margin-top: 15px;">
                    <h4>Estado del sensor:</h4>
                    <div id="sensorStatus">
                        <p>Valor: <span id="sensorValue">-</span></p>
                        <p>Color detectado: <span id="colorDetected" class="log-info">-</span></p>
                    </div>
                </div>
                
                <div id="sensor-result"></div>
                <div id="sensor-completed"></div>
            </div>
        </div>

        <!-- Mensaje de todas las verificaciones completadas -->
        <div id="all-completed" style="display: none;"></div>
        
        <div class="log-container">
            <div class="log-header">
                <h2>Registro de Eventos MQTT</h2>
                <button class="btn btn-primary" id="btnClearLog">
                    <i class="fas fa-broom"></i> Limpiar Registro
                </button>
            </div>
            <div class="log-content" id="logContent">
                <div class="log-entry">
                    <span class="log-time" id="currentTime"></span>
                    <span class="log-message log-info">Sistema de mantenimiento iniciado. Conectando a MQTT...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración MQTT
        const BROKER_WS = "wss://broker.emqx.io:8084/mqtt";
        const TOPIC_ENTRADA = "Entrada/05";
        const TOPIC_SALIDA = "Salida/06";
        
        let mqttClient;
        let isConnected = false;

        // Elementos DOM
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const connectionDot = document.getElementById('connection-dot');
        const connectionText = document.getElementById('connection-text');
        const btnBuzzerOn = document.getElementById('btnBuzzerOn');
        const btnBuzzerOff = document.getElementById('btnBuzzerOff');
        const buzzerCount = document.getElementById('buzzer-count');
        const buzzerStatus = document.getElementById('buzzerStatus');
        const buzzerResult = document.getElementById('buzzer-result');
        const buzzerCompleted = document.getElementById('buzzer-completed');
        const btnInicioStatus = document.getElementById('btn-inicio-status');
        const btnInicioCount = document.getElementById('btn-inicio-count');
        const btnFinStatus = document.getElementById('btn-fin-status');
        const btnFinCount = document.getElementById('btn-fin-count');
        const btnTestInicio = document.getElementById('btnTestInicio');
        const btnTestFin = document.getElementById('btnTestFin');
        const botonesEvents = document.getElementById('botones-events');
        const botonesResult = document.getElementById('botones-result');
        const botonesCompleted = document.getElementById('botones-completed');
        const btnTestSensor = document.getElementById('btnTestSensor');
        const sensorCount = document.getElementById('sensor-count');
        const sensorDisplay = document.getElementById('sensorDisplay');
        const sensorValue = document.getElementById('sensorValue');
        const colorDetected = document.getElementById('colorDetected');
        const sensorResult = document.getElementById('sensor-result');
        const sensorCompleted = document.getElementById('sensor-completed');
        const allCompleted = document.getElementById('all-completed');
        const logContent = document.getElementById('logContent');
        const btnClearLog = document.getElementById('btnClearLog');
        const currentTime = document.getElementById('currentTime');
        
        // Contadores de pruebas (máximo 5)
        let buzzerTests = 0;
        let btnInicioTests = 0;
        let btnFinTests = 0;
        let sensorTests = 0;
        let buzzerCompletedFlag = false;
        let buttonsCompletedFlag = false;
        let sensorCompletedFlag = false;

        // Actualizar hora actual
        function updateTime() {
            const now = new Date();
            currentTime.textContent = now.toLocaleTimeString();
        }
        
        setInterval(updateTime, 1000);
        updateTime();
        
        // Función para agregar entradas al registro
        function addLogEntry(message, type = 'info') {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            
            const logEntry = document.createElement('div');
            logEntry.className = 'log-entry';
            
            const timeSpan = document.createElement('span');
            timeSpan.className = 'log-time';
            timeSpan.textContent = timeString + ' - ';
            
            const messageSpan = document.createElement('span');
            messageSpan.className = `log-message log-${type}`;
            messageSpan.textContent = message;
            
            logEntry.appendChild(timeSpan);
            logEntry.appendChild(messageSpan);
            
            logContent.appendChild(logEntry);
            logContent.scrollTop = logContent.scrollHeight;
        }

        // Función para agregar eventos de botones en el área específica
        function addBotonEvent(boton, estado) {
            // Limpiar el mensaje inicial si existe
            if (botonesEvents.querySelector('p') && botonesEvents.querySelector('p').style.fontStyle === 'italic') {
                botonesEvents.innerHTML = '';
            }
            
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            
            const eventDiv = document.createElement('div');
            eventDiv.className = `boton-event ${boton}`;
            
            const icon = boton === 'inicio' ? '🔘' : '⭕';
            const color = boton === 'inicio' ? 'primary' : 'warning';
            
            eventDiv.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: var(--${color});">${icon} Botón ${boton.toUpperCase()}</strong>
                        <span style="margin-left: 10px;">${estado}</span>
                    </div>
                    <small style="color: #6c757d;">${timeString}</small>
                </div>
            `;
            
            botonesEvents.appendChild(eventDiv);
            botonesEvents.scrollTop = botonesEvents.scrollHeight;
            
            // Mantener máximo 10 eventos visibles
            const events = botonesEvents.querySelectorAll('.boton-event');
            if (events.length > 10) {
                events[0].remove();
            }
        }

        // Inicializar conexión MQTT
        function initMQTT() {
            console.log("Conectando a MQTT...");
            
            // clientId aleatorio para evitar colisiones
            const clientId = 'web_mantenimiento_' + Math.random().toString(16).substr(2, 8);

            // Opciones de conexión (adaptadas del código de referencia)
            const options = {
                keepalive: 30,
                clientId: clientId,
                clean: true,
                reconnectPeriod: 3000,
                connectTimeout: 4000
            };

            // Conectar al broker por WebSocket
            mqttClient = mqtt.connect(BROKER_WS, options);

            // Configurar event handlers (adaptados del código de referencia)
            mqttClient.on('connect', function () {
                console.log("Conectado a MQTT broker");
                isConnected = true;
                connectionDot.className = 'status-dot connected';
                connectionText.textContent = '✅ Conectado al broker EMQX';
                statusDot.className = 'status-dot connected';
                statusText.textContent = 'Conectado';
                
                // Suscribirse al topic de salida
                mqttClient.subscribe(TOPIC_SALIDA, function(err) {
                    if (!err) {
                        console.log("Suscrito al topic:", TOPIC_SALIDA);
                        addLogEntry('Conectado a MQTT. Suscrito a: ' + TOPIC_SALIDA, 'success');
                    } else {
                        console.error('Error suscribiendo a ' + TOPIC_SALIDA + ': ' + err.message);
                        addLogEntry('Error suscribiendo a ' + TOPIC_SALIDA, 'danger');
                    }
                });
                
                // Habilitar botones
                enableButtons();
            });

            mqttClient.on('reconnect', function () {
                console.log("Reconectando a MQTT...");
                connectionDot.className = 'status-dot';
                connectionText.textContent = '🔄 Reintentando conexión...';
                addLogEntry('Reconectando a MQTT...', 'warning');
            });

            mqttClient.on('close', function () {
                console.log("Conexión MQTT cerrada");
                isConnected = false;
                connectionDot.className = 'status-dot disconnected';
                connectionText.textContent = 'Desconectado MQTT';
                statusDot.className = 'status-dot disconnected';
                statusText.textContent = 'Desconectado';
                addLogEntry('Conexión MQTT cerrada', 'danger');
                disableButtons();
            });

            mqttClient.on('error', function (err) {
                console.error('Error de conexión MQTT: ' + err.message);
                connectionDot.className = 'status-dot disconnected';
                connectionText.textContent = '❌ Error de conexión MQTT';
                addLogEntry('Error de conexión MQTT: ' + err.message, 'danger');
                disableButtons();
            });

            mqttClient.on('message', function (topic, payload) {
                const message = payload.toString().trim();
                console.log("Mensaje MQTT [" + topic + "]: " + message);
                handleMQTTMessage(message);
            });
        }

        // Habilitar botones cuando esté conectado
        function enableButtons() {
            btnBuzzerOn.disabled = false;
            btnBuzzerOff.disabled = false;
            btnTestSensor.disabled = false;
            btnTestInicio.disabled = false;
            btnTestFin.disabled = false;
        }

        // Deshabilitar botones cuando se desconecte
        function disableButtons() {
            btnBuzzerOn.disabled = true;
            btnBuzzerOff.disabled = true;
            btnTestSensor.disabled = true;
            btnTestInicio.disabled = true;
            btnTestFin.disabled = true;
        }

        // Enviar comando MQTT
        function sendCommand(command) {
            if (!isConnected || !mqttClient) {
                addLogEntry('Error: No conectado a MQTT', 'danger');
                return;
            }
            
            mqttClient.publish(TOPIC_ENTRADA, command);
            addLogEntry(`Comando enviado [${TOPIC_ENTRADA}]: ${command}`, 'warning');
        }

        // Procesar mensajes MQTT recibidos (adaptado del código de referencia)
        function handleMQTTMessage(message) {
            addLogEntry(`Mensaje recibido [${TOPIC_SALIDA}]: ${message}`, 'info');

            // ========== DETECCIÓN DE BOTONES FÍSICOS (del código de referencia) ==========
            if (message === "INICIO") {
                // Actualizar estado visual del botón INICIO
                btnInicioStatus.className = "estado-boton verde";
                btnInicioStatus.textContent = "INICIO: PRESIONADO ✅";
                
                // Agregar evento en el área de botones físicos
                addBotonEvent('inicio', 'PRESIONADO ✓');
                
                // Incrementar contador si no ha llegado al límite
                if (btnInicioTests < 5) {
                    btnInicioTests++;
                    btnInicioCount.textContent = `${btnInicioTests}/5`;
                    addLogEntry('Botón INICIO presionado físicamente - Confirmado', 'success');
                }
                
                // Restaurar estado después de 2 segundos (como en el código de referencia)
                setTimeout(() => {
                    btnInicioStatus.className = "estado-boton rojo";
                    btnInicioStatus.textContent = "INICIO: No presionado";
                }, 2000);
                
                checkButtonsCompletion();
            }

            if (message === "FIN") {
                // Actualizar estado visual del botón FIN
                btnFinStatus.className = "estado-boton verde";
                btnFinStatus.textContent = "FIN: PRESIONADO ✅";
                
                // Agregar evento en el área de botones físicos
                addBotonEvent('fin', 'PRESIONADO ✓');
                
                // Incrementar contador si no ha llegado al límite
                if (btnFinTests < 5) {
                    btnFinTests++;
                    btnFinCount.textContent = `${btnFinTests}/5`;
                    addLogEntry('Botón FIN presionado físicamente - Confirmado', 'success');
                }
                
                // Restaurar estado después de 2 segundos (como en el código de referencia)
                setTimeout(() => {
                    btnFinStatus.className = "estado-boton rojo";
                    btnFinStatus.textContent = "FIN: No presionado";
                }, 2000);
                
                checkButtonsCompletion();
            }

            // Procesar estado del sensor y buzzer automático
            else if (message.includes("NEGRO detectado") || message.includes("BLANCO detectado")) {
                if (message.includes("NEGRO")) {
                    sensorDisplay.textContent = 'NEGRO detectado';
                    sensorDisplay.className = 'sensor-display sensor-black';
                    colorDetected.textContent = 'NEGRO';
                    buzzerStatus.textContent = 'BUZZER APAGADO';
                    buzzerStatus.className = 'buzzer-status buzzer-off';
                } else {
                    sensorDisplay.textContent = 'BLANCO detectado';
                    sensorDisplay.className = 'sensor-display sensor-white';
                    colorDetected.textContent = 'BLANCO';
                    buzzerStatus.textContent = 'BUZZER ACTIVADO';
                    buzzerStatus.className = 'buzzer-status buzzer-on';
                }
                
                // Extraer valor del sensor
                const match = message.match(/Valor:? (\d+)/);
                if (match) {
                    sensorValue.textContent = match[1];
                }
            }
            
            // Procesar respuestas de comandos de prueba del buzzer
            else if (message.includes("Buzzer encendido manualmente")) {
                buzzerResult.innerHTML = '<p style="color: #27ae60;"><i class="fas fa-check-circle"></i> Buzzer encendido correctamente</p>';
                updateBuzzerTestCount();
            }
            else if (message.includes("Buzzer apagado manualmente")) {
                buzzerResult.innerHTML = '<p style="color: #27ae60;"><i class="fas fa-check-circle"></i> Buzzer apagado correctamente</p>';
                updateBuzzerTestCount();
            }
            
            // Procesar mensajes de botones desde comandos de prueba
            else if (message.includes("test_inicio") || message.includes("test_fin")) {
                botonesResult.innerHTML = '<p style="color: #27ae60;"><i class="fas fa-check-circle"></i> ' + message + '</p>';
            }
            
            // Procesar respuestas del sensor
            else if (message.includes("Sensor:")) {
                sensorResult.innerHTML = '<p style="color: #27ae60;"><i class="fas fa-check-circle"></i> ' + message + '</p>';
                updateSensorTestCount();
            }
        }

        // Actualizar contador de pruebas del buzzer
        function updateBuzzerTestCount() {
            if (buzzerCompletedFlag) return;
            
            buzzerTests++;
            buzzerCount.textContent = `${buzzerTests}/5`;
            
            if (buzzerTests >= 5) {
                buzzerCompletedFlag = true;
                btnBuzzerOn.disabled = true;
                btnBuzzerOff.disabled = true;
                buzzerCompleted.innerHTML = '<div class="verification-completed"><i class="fas fa-check-circle"></i> Verificación del Buzzer Completada (5/5)</div>';
                addLogEntry('Verificación del Buzzer completada - 5 pruebas realizadas', 'success');
                checkAllCompletions();
            }
        }

        // Actualizar contador de pruebas del sensor
        function updateSensorTestCount() {
            if (sensorCompletedFlag) return;
            
            sensorTests++;
            sensorCount.textContent = `${sensorTests}/5`;
            
            if (sensorTests >= 5) {
                sensorCompletedFlag = true;
                btnTestSensor.disabled = true;
                sensorCompleted.innerHTML = '<div class="verification-completed"><i class="fas fa-check-circle"></i> Verificación del Sensor Completada (5/5)</div>';
                addLogEntry('Verificación del Sensor completada - 5 pruebas realizadas', 'success');
                checkAllCompletions();
            }
        }

        // Verificar si se completaron ambos botones
        function checkButtonsCompletion() {
            if (buttonsCompletedFlag) return;
            
            if (btnInicioTests >= 5 && btnFinTests >= 5) {
                buttonsCompletedFlag = true;
                btnTestInicio.disabled = true;
                btnTestFin.disabled = true;
                botonesCompleted.innerHTML = '<div class="verification-completed"><i class="fas fa-check-circle"></i> Verificación de Botones Completada (5/5 cada uno)</div>';
                addLogEntry('Verificación de Botones completada - 5 pruebas en cada botón', 'success');
                checkAllCompletions();
            }
        }

        // Verificar si todas las verificaciones están completas
        function checkAllCompletions() {
            if (buzzerCompletedFlag && buttonsCompletedFlag && sensorCompletedFlag) {
                allCompleted.style.display = 'block';
                allCompleted.innerHTML = '<div class="verification-completed" style="font-size: 1.2em; padding: 20px;">' +
                    '<i class="fas fa-trophy"></i> ¡TODAS LAS VERIFICACIONES COMPLETADAS! <i class="fas fa-trophy"></i><br>' +
                    '<span style="font-size: 0.9em;">Todos los componentes han sido verificados exitosamente</span>' +
                    '</div>';
                addLogEntry('¡TODAS LAS VERIFICACIONES COMPLETADAS! - Sistema listo para uso', 'success');
            }
        }

        // Event Listeners para los botones de prueba manual
        btnBuzzerOn.addEventListener('click', function() {
            sendCommand("test_buzzer_on");
        });
        
        btnBuzzerOff.addEventListener('click', function() {
            sendCommand("test_buzzer_off");
        });

        btnTestInicio.addEventListener('click', function() {
            sendCommand("test_inicio");
        });

        btnTestFin.addEventListener('click', function() {
            sendCommand("test_fin");
        });

        btnTestSensor.addEventListener('click', function() {
            sendCommand("test_sensor");
        });

        // Limpiar registro
        btnClearLog.addEventListener('click', function() {
            logContent.innerHTML = '';
            addLogEntry('Registro limpiado', 'info');
        });

        // Inicializar cuando cargue la página
        window.addEventListener('load', function() {
            initMQTT();
            addLogEntry('Sistema de mantenimiento iniciado', 'success');
            addLogEntry('Conectando a broker MQTT...', 'info');
            addLogEntry('Presione los botones físicos INICIO y FIN 5 veces cada uno para verificarlos', 'info');
        });

        // Para pruebas desde consola
        window.sendMQTTCommand = function(command) {
            sendCommand(command);
        };
    </script>
</body>
</html>