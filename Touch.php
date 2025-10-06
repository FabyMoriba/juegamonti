<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego de Secuencias de Gestos</title>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .panel-title {
            font-size: 1.2em;
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .status {
            font-size: 1.5em;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
            background-color: #ecf0f1;
            transition: all 0.3s ease;
        }
        .status.active {
            background-color: #2ecc71;
            color: white;
        }
        .status.error {
            background-color: #e74c3c;
            color: white;
        }
        .status.instruction {
            background-color: #3498db;
            color: white;
        }
        .connection-status {
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .connected {
            background-color: #2ecc71;
            color: white;
        }
        .disconnected {
            background-color: #e74c3c;
            color: white;
        }
        .sequence-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 20px 0;
        }
        .sequence-row {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .sequence-step {
            width: 60px;
            height: 60px;
            border: 2px solid #3498db;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9em;
            text-align: center;
        }
        .sequence-step.target {
            background-color: #3498db;
            color: white;
        }
        .sequence-step.completed {
            background-color: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        .sequence-step.error {
            background-color: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }
        .score-display {
            text-align: center;
            font-size: 1.2em;
            margin: 15px 0;
        }
        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #2980b9;
        }
        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        .section-title {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            color: #2c3e50;
        }
        .gesto-name {
            font-size: 0.8em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Juego de Secuencias de Gestos</h1>
    
    <div id="connection-status" class="connection-status disconnected">
        Desconectado del broker MQTT
    </div>
    
    <div class="dashboard">
        <div class="panel">
            <div class="panel-title">Juego de Secuencias</div>
            
            <div id="current-gesto" class="status">Presiona "Iniciar Juego" para comenzar</div>
            
            <div class="section-title">Secuencia a Repetir:</div>
            <div class="sequence-container" id="target-sequence">
                <!-- La secuencia objetivo se mostrará aquí -->
            </div>
            
            <div class="section-title">Tus Gestos:</div>
            <div class="sequence-container" id="player-sequence">
                <!-- Los gestos del jugador se mostrarán aquí -->
            </div>
            
            <div class="score-display">
                Puntuación: <span id="score">0</span> | Nivel: <span id="level">1</span>
            </div>
            
            <div class="controls">
                <button id="start-btn">Iniciar Juego</button>
                <button id="reset-btn" disabled>Reiniciar</button>
            </div>
        </div>
    </div>

    <script>
        // Configuración MQTT para EMQX
        const brokerUrl = 'wss://broker.emqx.io:8084/mqtt';
        const options = {
            clean: true,
            connectTimeout: 4000,
            clientId: 'webClient_' + Math.random().toString(16).substr(2, 8),
            reconnectPeriod: 5000
        };
        
        // Topic a suscribirse (solo Salida/05)
        const salidaTopic = 'Salida/05';
        
        // Variables del juego
        let currentSequence = [];
        let playerSequence = [];
        let score = 0;
        let level = 1;
        let isPlaying = false;
        let isShowingSequence = false;
        const possibleGestos = ["Puño_Derecho", "Mano_Derecha_Abierta", "Puño_Izquierdo", "Mano_Izquierda_Abierta"];
        const gestoShortNames = {
            "Puño_Derecho": "Puño D",
            "Mano_Derecha_Abierta": "Mano D",
            "Puño_Izquierdo": "Puño I",
            "Mano_Izquierda_Abierta": "Mano I"
        };
        
        // Variables para el control de gestos constantes
        let lastGesto = null;
        let lastGestoTime = 0;
        const GESTO_DEBOUNCE_TIME = 500; // 500ms = 0.5 segundos
        
        // Elementos del DOM
        const connectionStatus = document.getElementById('connection-status');
        const currentGestoDisplay = document.getElementById('current-gesto');
        const targetSequenceDisplay = document.getElementById('target-sequence');
        const playerSequenceDisplay = document.getElementById('player-sequence');
        const scoreDisplay = document.getElementById('score');
        const levelDisplay = document.getElementById('level');
        const startBtn = document.getElementById('start-btn');
        const resetBtn = document.getElementById('reset-btn');
        
        // Conectar al broker
        const client = mqtt.connect(brokerUrl, options);
        
        // Manejar conexión
        client.on('connect', () => {
            console.log('Conectado a EMQX');
            connectionStatus.textContent = 'Conectado al broker EMQX';
            connectionStatus.className = 'connection-status connected';
            
            // Suscribirse solo al topic de salida
            client.subscribe(salidaTopic, { qos: 0 }, (err) => {
                if (!err) console.log(`Suscrito a ${salidaTopic}`);
            });
        });
        
        // Manejar errores
        client.on('error', (err) => {
            console.error('Error:', err);
            connectionStatus.textContent = 'Error de conexión: ' + err.message;
            connectionStatus.className = 'connection-status disconnected';
        });
        
        // Manejar reconexión
        client.on('reconnect', () => {
            console.log('Reconectando...');
            connectionStatus.textContent = 'Reconectando al broker...';
            connectionStatus.className = 'connection-status disconnected';
        });
        
        // Manejar desconexión
        client.on('close', () => {
            console.log('Desconectado');
            connectionStatus.textContent = 'Desconectado del broker MQTT';
            connectionStatus.className = 'connection-status disconnected';
        });
        
        // Manejar mensajes recibidos
        client.on('message', (topic, message) => {
            const msgString = message.toString();
            
            console.log(`Mensaje recibido [${topic}]: ${msgString}`);
            
            // Solo procesar mensajes del topic de salida
            if (topic === salidaTopic && isPlaying && !isShowingSequence) {
                const currentTime = Date.now();
                
                // Verificar si es un gesto nuevo o si ha pasado suficiente tiempo desde el último
                if (msgString !== lastGesto || (currentTime - lastGestoTime) >= GESTO_DEBOUNCE_TIME) {
                    lastGesto = msgString;
                    lastGestoTime = currentTime;
                    
                    currentGestoDisplay.textContent = `Gesto detectado: ${gestoShortNames[msgString] || msgString}`;
                    currentGestoDisplay.className = 'status active';
                    
                    // Procesar el gesto en la secuencia del jugador
                    processPlayerGesto(msgString);
                    
                    // Quitar la clase "active" después de 1 segundo
                    setTimeout(() => {
                        if (currentGestoDisplay.textContent.includes("Gesto detectado")) {
                            currentGestoDisplay.className = 'status';
                        }
                    }, 1000);
                }
            }
        });
        
        // Funciones del juego
        function startGame() {
            isPlaying = true;
            score = 0;
            level = 1;
            scoreDisplay.textContent = score;
            levelDisplay.textContent = level;
            currentSequence = [];
            playerSequence = [];
            lastGesto = null;
            lastGestoTime = 0;
            startBtn.disabled = true;
            resetBtn.disabled = false;
            
            generateNextSequence();
            showTargetSequence();
        }
        
        function resetGame() {
            isPlaying = false;
            currentSequence = [];
            playerSequence = [];
            lastGesto = null;
            lastGestoTime = 0;
            targetSequenceDisplay.innerHTML = '';
            playerSequenceDisplay.innerHTML = '';
            currentGestoDisplay.textContent = 'Juego reiniciado. Presiona "Iniciar Juego" para comenzar';
            currentGestoDisplay.className = 'status';
            startBtn.disabled = false;
            resetBtn.disabled = true;
        }
        
        function generateNextSequence() {
            // Añade un nuevo gesto aleatorio a la secuencia
            const randomIndex = Math.floor(Math.random() * possibleGestos.length);
            currentSequence.push(possibleGestos[randomIndex]);
        }
        
        function showTargetSequence() {
            isShowingSequence = true;
            targetSequenceDisplay.innerHTML = '';
            playerSequenceDisplay.innerHTML = '';
            currentGestoDisplay.textContent = `Observa la secuencia (Nivel ${level})`;
            currentGestoDisplay.className = 'status instruction';
            
            // Mostrar cada gesto de la secuencia uno por uno
            currentSequence.forEach((gesto, index) => {
                setTimeout(() => {
                    targetSequenceDisplay.innerHTML = '';
                    for (let i = 0; i < currentSequence.length; i++) {
                        const step = document.createElement('div');
                        step.className = 'sequence-step' + (i === index ? ' target' : '');
                        step.innerHTML = i+1 + `<div class="gesto-name">${i === index ? gestoShortNames[gesto] || gesto : ''}</div>`;
                        targetSequenceDisplay.appendChild(step);
                    }
                }, index * 1000);
            });
            
            // Terminar de mostrar la secuencia
            setTimeout(() => {
                isShowingSequence = false;
                currentGestoDisplay.textContent = 'Ahora repite la secuencia';
                currentGestoDisplay.className = 'status';
                targetSequenceDisplay.innerHTML = '';
                
                // Mostrar toda la secuencia estática
                const row = document.createElement('div');
                row.className = 'sequence-row';
                currentSequence.forEach((gesto, i) => {
                    const step = document.createElement('div');
                    step.className = 'sequence-step target';
                    step.innerHTML = i+1 + `<div class="gesto-name">${gestoShortNames[gesto] || gesto}</div>`;
                    row.appendChild(step);
                });
                targetSequenceDisplay.appendChild(row);
                
                // Preparar área para los gestos del jugador
                playerSequenceDisplay.innerHTML = '';
            }, currentSequence.length * 1000);
        }
        
        function processPlayerGesto(gesto) {
            playerSequence.push(gesto);
            updatePlayerSequenceDisplay();
            
            // Verificar si el gesto es correcto
            const currentIndex = playerSequence.length - 1;
            if (gesto !== currentSequence[currentIndex]) {
                // Secuencia incorrecta
                currentGestoDisplay.textContent = '¡Error! Intenta de nuevo';
                currentGestoDisplay.className = 'status error';
                playerSequence = [];
                setTimeout(() => {
                    updatePlayerSequenceDisplay();
                    currentGestoDisplay.textContent = 'Repite la secuencia';
                    currentGestoDisplay.className = 'status';
                }, 2000);
                return;
            }
            
            // Verificar si se completó la secuencia
            if (playerSequence.length === currentSequence.length) {
                // Secuencia completada correctamente
                score += level * 10;
                level++;
                scoreDisplay.textContent = score;
                levelDisplay.textContent = level;
                currentGestoDisplay.textContent = '¡Correcto! Pasando al siguiente nivel';
                currentGestoDisplay.className = 'status active';
                
                // Preparar siguiente ronda
                setTimeout(() => {
                    playerSequence = [];
                    lastGesto = null;
                    lastGestoTime = 0;
                    generateNextSequence();
                    showTargetSequence();
                }, 2000);
            }
        }
        
        function updatePlayerSequenceDisplay() {
            playerSequenceDisplay.innerHTML = '';
            const row = document.createElement('div');
            row.className = 'sequence-row';
            
            playerSequence.forEach((gesto, index) => {
                const step = document.createElement('div');
                const isCorrect = gesto === currentSequence[index];
                step.className = `sequence-step ${isCorrect ? 'completed' : 'error'}`;
                step.innerHTML = index+1 + `<div class="gesto-name">${gestoShortNames[gesto] || gesto}</div>`;
                row.appendChild(step);
            });
            
            playerSequenceDisplay.appendChild(row);
        }
        
        // Event listeners
        startBtn.addEventListener('click', startGame);
        resetBtn.addEventListener('click', resetGame);
        
        // Manejar cierre de la página
        window.addEventListener('beforeunload', () => {
            client.end();
        });
    </script>
</body>
</html>