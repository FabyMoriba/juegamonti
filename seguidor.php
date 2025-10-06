<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || $_SESSION['tipo_operador'] != 'hijo') {
    header("Location: inicio.php");
    exit();
}

// Recuperar la ficha seleccionada de sessionStorage (se enviará por POST desde JavaScript)
$fichaSeleccionada = "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SEGUIDOR DE LINEA</title>
  <style>
    :root {
      --primary-color: #3498db;
      --success-color: #2ecc71;
      --error-color: #e74c3c;
      --warning-color: #f39c12;
      --background-gradient: linear-gradient(135deg, #83a4d4, #b6fbff);
    }

    * {
      box-sizing: border-box;
      transition: all 0.3s ease;
    }

    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      font-family: 'Comic Sans MS', cursive, sans-serif;
      background: var(--background-gradient);
      overflow: hidden;
      position: relative;
      color: #333;
      padding: 20px;
    }

    /* Efecto de partículas */
    .particles-container {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: -1;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.6);
      border-radius: 50%;
      opacity: 0;
      animation: floatParticle 15s linear infinite;
    }

    @keyframes floatParticle {
      0% {
        transform: translateY(100vh) scale(0);
        opacity: 0;
      }
      10% {
        opacity: 0.4;
      }
      90% {
        opacity: 0.3;
      }
      100% {
        transform: translateY(-100px) scale(1);
        opacity: 0;
      }
    }

    /* Botón atrás mejorado */
    #back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      background-color: var(--primary-color);
      border: none;
      color: white;
      padding: 12px 24px;
      font-size: 1em;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      transition: all 0.3s;
      z-index: 10;
    }
    
    #back-button:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    #back-button:active {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Círculos flotantes mejorados */
    .floating-circle {
      position: absolute;
      border-radius: 50%;
      opacity: 0.4;
      background: rgba(255, 255, 255, 0.6);
      animation: float 8s ease-in-out infinite;
      z-index: -1;
    }
    
    .circle1 { 
      width: 120px; 
      height: 120px; 
      top: 10%; 
      left: 5%; 
      animation-delay: 0s;
    }
    
    .circle2 { 
      width: 90px; 
      height: 90px; 
      bottom: 15%; 
      right: 10%; 
      animation-delay: 1.5s; 
    }
    
    .circle3 { 
      width: 70px; 
      height: 70px; 
      top: 30%; 
      right: 20%; 
      animation-delay: 3s; 
    }
    
    .circle4 {
      width: 100px;
      height: 100px;
      bottom: 25%;
      left: 15%;
      animation-delay: 4.5s;
    }

    @keyframes float {
      0%, 100% { 
        transform: translateY(0) rotate(0deg); 
      }
      33% { 
        transform: translateY(-20px) rotate(5deg); 
      }
      66% { 
        transform: translateY(10px) rotate(-5deg); 
      }
    }

    h1 {
      font-size: 2.8em;
      margin-bottom: 15px;
      text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.8);
      animation: titlePulse 3s infinite;
    }

    @keyframes titlePulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    h2 {
      margin: 15px 0;
      font-size: 1.6em;
    }

    #timer {
      font-size: 2.2em;
      font-weight: bold;
      color: var(--primary-color);
      animation: pulse 1s infinite;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }

    .status-container {
      display: flex;
      justify-content: space-around;
      align-items: center;
      gap: 20px;
      width: 90%;
      max-width: 650px;
      background-color: rgba(255, 255, 255, 0.85);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      text-align: center;
      margin-bottom: 25px;
      animation: containerFloat 4s ease-in-out infinite;
      backdrop-filter: blur(5px);
    }

    @keyframes containerFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }

    .status-item {
      padding: 15px;
      border-radius: 15px;
      transition: all 0.3s;
    }

    .status-item.active {
      background: rgba(52, 152, 219, 0.1);
      transform: scale(1.05);
    }

    .buzzer-on {
      color: var(--error-color);
      font-weight: bold;
      font-size: 1.3em;
      text-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
      animation: buzzerOn 0.5s infinite alternate;
    }

    @keyframes buzzerOn {
      from { opacity: 1; }
      to { opacity: 0.7; }
    }

    .buzzer-off {
      color: var(--success-color);
      font-weight: bold;
      font-size: 1.3em;
    }

    .progress-container {
      width: 85%;
      max-width: 700px;
      margin: 20px 0;
    }

    .progress-bar {
      width: 100%;
      height: 25px;
      background-color: rgba(238, 238, 238, 0.8);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .progress-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
      border-radius: 15px;
      transition: width 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
      position: relative;
      overflow: hidden;
    }

    .progress-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, 0.2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.2) 50%,
        rgba(255, 255, 255, 0.2) 75%,
        transparent 75%,
        transparent
      );
      z-index: 1;
      background-size: 30px 30px;
      animation: move 1s linear infinite;
      border-radius: 15px;
    }

    @keyframes move {
      0% {
        background-position: 0 0;
      }
      100% {
        background-position: 30px 30px;
      }
    }

    .progress-labels {
      display: flex;
      justify-content: space-between;
      width: 100%;
      margin-top: 8px;
      font-size: 0.9em;
      color: #555;
    }

    .face-container {
      margin: 20px 0;
      perspective: 1000px;
    }

    .face {
      font-size: 3.5em;
      margin: 0;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      display: inline-block;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
      transform-style: preserve-3d;
    }

    .face.animate {
      animation: faceBounce 0.5s;
    }

    @keyframes faceBounce {
      0%, 100% { transform: scale(1) rotate(0deg); }
      25% { transform: scale(1.3) rotate(5deg); }
      50% { transform: scale(0.9) rotate(-5deg); }
      75% { transform: scale(1.2) rotate(2deg); }
    }

    .error-flash {
      animation: errorFlash 0.5s;
    }

    @keyframes errorFlash {
      0%, 100% { background-color: transparent; }
      50% { background-color: rgba(231, 76, 60, 0.2); }
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
      background-color: var(--success-color);
      box-shadow: 0 0 8px var(--success-color);
    }

    .status-dot.disconnected {
      background-color: var(--error-color);
    }

    /* Efecto de confeti */
    .confetti {
      position: absolute;
      width: 10px;
      height: 20px;
      opacity: 0;
      z-index: 100;
      animation: confettiFall 5s linear forwards;
    }

    @keyframes confettiFall {
      0% {
        opacity: 1;
        transform: translateY(-100px) rotate(0deg);
      }
      100% {
        opacity: 0;
        transform: translateY(100vh) rotate(720deg);
      }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .status-container {
        flex-direction: column;
        gap: 15px;
      }
      
      h1 {
        font-size: 2.2em;
        text-align: center;
      }
      
      .progress-container {
        width: 95%;
      }
    }
  </style>
</head>

<body>

  <!-- Efecto de partículas -->
  <div class="particles-container" id="particles-container"></div>

  <!-- Círculos flotantes -->
  <div class="floating-circle circle1"></div>
  <div class="floating-circle circle2"></div>
  <div class="floating-circle circle3"></div>
  <div class="floating-circle circle4"></div>

  <!-- Botón Atrás -->
  <button id="back-button" onclick="window.location.href='niveles_seguidor.php'">← Atrás</button>

  <!-- Estado de conexión -->
  <div class="connection-status">
    <div class="status-dot" id="connection-dot"></div>
    <span id="connection-text">Conectando...</span>
  </div>

  <h1>🚗 CAMINITOS</h1>

  <h2>Errores: <span id="errorCount">0</span></h2>

  <div class="status-container">
    <div class="status-item" id="sensor-item">
      <p>Sensor Digital: <span id="sensorStatus">Desconectado</span></p>
    </div>
    <div class="status-item" id="buzzer-item">
      <p>Estado del Buzzer: <span id="buzzerStatus" class="buzzer-off">Apagado</span></p>
    </div>
  </div>

  <h2>⏱️ Tiempo: <span id="timer">0</span> segundos</h2>

  <div class="progress-container">
    <div class="progress-bar">
      <div class="progress-fill" id="progressFill"></div>
    </div>
    <div class="progress-labels">
      <span>0%</span>
      <span id="progress-percentage">0%</span>
      <span>100%</span>
    </div>
  </div>

  <div class="face-container">
    <div class="face" id="face">😐</div>
  </div>

  <!-- Efectos de sonido -->
  <audio id="buzzer-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-retro-game-emergency-alarm-1000.mp3" preload="auto"></audio>
  <audio id="success-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-winning-chimes-2015.mp3" preload="auto"></audio>
  <audio id="error-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-wrong-answer-fail-notification-946.mp3" preload="auto"></audio>
  <audio id="start-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-game-show-intro-331.mp3" preload="auto"></audio>
  <audio id="finish-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-achievement-bell-600.mp3" preload="auto"></audio>

  <!-- MQTT.js desde CDN -->
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

  <script>
    // Configuración MQTT
    const BROKER_WS = "wss://broker.emqx.io:8084/mqtt";
    const TOPIC = "Salida/06";
    
    let mqttClient;
    let timer;
    let seconds = 0;
    let errorCount = 0;
    let counting = false;
    let errorLogged = false;

    // Elementos DOM
    const connectionDot = document.getElementById('connection-dot');
    const connectionText = document.getElementById('connection-text');
    const sensorItem = document.getElementById('sensor-item');
    const buzzerItem = document.getElementById('buzzer-item');
    const progressPercentage = document.getElementById('progress-percentage');

    // Elementos de audio
    const buzzerSound = document.getElementById('buzzer-sound');
    const successSound = document.getElementById('success-sound');
    const errorSound = document.getElementById('error-sound');
    const startSound = document.getElementById('start-sound');
    const finishSound = document.getElementById('finish-sound');

    // Inicializar conexión MQTT
    function initMQTT() {
      console.log("Conectando a MQTT...");
      
      // clientId aleatorio para evitar colisiones
      const clientId = 'web_' + Math.random().toString(16).substr(2, 8);

      // Opciones de conexión
      const options = {
        keepalive: 30,
        clientId: clientId,
        reconnectPeriod: 3000,
        connectTimeout: 30 * 1000,
      };

      // Conectar al broker por WebSocket
      mqttClient = mqtt.connect(BROKER_WS, options);

      // Configurar event handlers
      mqttClient.on('connect', function () {
        console.log("Conectado a MQTT broker");
        connectionDot.className = 'status-dot connected';
        connectionText.textContent = 'Conectado MQTT';
        
        // Suscribirse al topic
        mqttClient.subscribe(TOPIC, function(err) {
          if (err) {
            console.error('Error suscribiendo a ' + TOPIC + ': ' + err.message);
          } else {
            console.log('Suscrito a ' + TOPIC);
          }
        });
      });

      mqttClient.on('reconnect', function () {
        console.log("Reconectando a MQTT...");
        connectionDot.className = 'status-dot';
        connectionText.textContent = 'Reconectando MQTT...';
      });

      mqttClient.on('close', function () {
        console.log("Conexión MQTT cerrada");
        connectionDot.className = 'status-dot disconnected';
        connectionText.textContent = 'Desconectado MQTT';
      });

      mqttClient.on('error', function (err) {
        console.error('Error de conexión MQTT: ' + err.message);
        connectionDot.className = 'status-dot disconnected';
        connectionText.textContent = 'Error MQTT';
      });

      mqttClient.on('message', function (topic, payload) {
        const message = payload.toString();
        console.log("Mensaje recibido [" + topic + "]: " + message);
        handleMQTTMessage(message);
      });
    }

    // Inicializar partículas
    function initParticles() {
      const container = document.getElementById('particles-container');
      const particleCount = 30;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Tamaño aleatorio entre 3 y 8px
        const size = Math.random() * 5 + 3;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        // Posición horizontal aleatoria
        particle.style.left = `${Math.random() * 100}%`;
        
        // Retraso de animación aleatorio
        particle.style.animationDelay = `${Math.random() * 15}s`;
        
        // Duración de animación aleatoria
        const duration = 10 + Math.random() * 20;
        particle.style.animationDuration = `${duration}s`;
        
        container.appendChild(particle);
      }
    }

    // Crear efecto de confeti
    function createConfetti() {
      const colors = ['#f94144', '#f3722c', '#f8961e', '#f9c74f', '#90be6d', '#43aa8b', '#577590'];
      const confettiCount = 100;
      
      for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti');
        
        // Estilo aleatorio
        const color = colors[Math.floor(Math.random() * colors.length)];
        const size = Math.random() * 10 + 5;
        const left = Math.random() * 100;
        
        confetti.style.backgroundColor = color;
        confetti.style.width = `${size}px`;
        confetti.style.height = `${size * 1.5}px`;
        confetti.style.left = `${left}%`;
        confetti.style.animationDelay = `${Math.random() * 2}s`;
        
        document.body.appendChild(confetti);
        
        // Eliminar después de la animación
        setTimeout(() => {
          confetti.remove();
        }, 5000);
      }
    }

    // Procesar mensajes MQTT
    function handleMQTTMessage(data) {
      const sensorStatus = document.getElementById("sensorStatus");
      const buzzerStatus = document.getElementById("buzzerStatus");
      const errorCounter = document.getElementById("errorCount");
      const face = document.getElementById("face");

      if (data.includes("BLANCO detectado → buzzer ON")) {
        sensorStatus.innerText = "Detectado";
        buzzerStatus.innerText = "Encendido";
        buzzerStatus.classList.remove("buzzer-off");
        buzzerStatus.classList.add("buzzer-on");
        
        // Efectos visuales
        sensorItem.classList.add('active');
        buzzerItem.classList.add('active');
        document.body.classList.add('error-flash');
        
        // Sonido
        if (buzzerSound.paused) {
          buzzerSound.currentTime = 0;
          buzzerSound.play().catch(e => console.log("Audio error:", e));
        }

        if (counting && !errorLogged) {
          errorCount++;
          errorCounter.innerText = errorCount;
          errorCounter.classList.add('error-flash');
          setTimeout(() => errorCounter.classList.remove('error-flash'), 500);
          
          // Sonido de error
          errorSound.currentTime = 0;
          errorSound.play().catch(e => console.log("Audio error:", e));
          
          updateFace();
          errorLogged = true;
        }
      } else if (data.includes("NEGRO detectado → buzzer OFF")) {
        sensorStatus.innerText = "No Detectado";
        buzzerStatus.innerText = "Apagado";
        buzzerStatus.classList.remove("buzzer-on");
        buzzerStatus.classList.add("buzzer-off");
        
        // Quitar efectos visuales
        sensorItem.classList.remove('active');
        buzzerItem.classList.remove('active');
        document.body.classList.remove('error-flash');

        errorLogged = false;
      } else if (data === "INICIO") {
        // Sonido de inicio
        startSound.play().catch(e => console.log("Audio error:", e));
        startTimer();
      } else if (data === "FIN") {
        // Sonido de finalización
        finishSound.play().catch(e => console.log("Audio error:", e));
        
        // Efecto de confeti si el rendimiento fue bueno
        if (errorCount <= 3) {
          createConfetti();
        }
        
        // Primero guardamos los datos
        guardarDatos()
          .then(() => {
            // Luego detenemos el temporizador
            stopTimer();
          })
          .catch(error => {
            console.error("Error al guardar datos:", error);
            stopTimer();
          });
      }
    }

    function startTimer() {
      clearInterval(timer);
      seconds = 0;
      errorCount = 0;
      counting = true;
      errorLogged = false;
      document.getElementById("timer").innerText = seconds;
      document.getElementById("errorCount").innerText = errorCount;
      document.getElementById("progressFill").style.width = "0%";
      progressPercentage.textContent = "0%";
      document.getElementById("face").innerText = "😐";
      
      // Reiniciar animación de la cara
      const face = document.getElementById("face");
      face.classList.remove('animate');
      setTimeout(() => face.classList.add('animate'), 10);

      timer = setInterval(() => {
        seconds++;
        document.getElementById("timer").innerText = seconds;
        updateProgressBar();
      }, 1000);
    }

    function stopTimer() {
      clearInterval(timer);
      counting = false;
    }

    function updateProgressBar() {
      const progressFill = document.getElementById("progressFill");
      let progress = Math.min((seconds / 60) * 100, 100);
      progressFill.style.width = progress + "%";
      progressPercentage.textContent = `${Math.round(progress)}%`;
    }

    function updateFace() {
      const face = document.getElementById("face");
      
      // Añadir animación
      face.classList.remove('animate');
      setTimeout(() => face.classList.add('animate'), 10);

      if (errorCount === 0) {
        face.innerText = "😃";
      } else if (errorCount <= 2) {
        face.innerText = "😐";
      } else if (errorCount <= 4) {
        face.innerText = "😟";
      } else {
        face.innerText = "😨";
      }
    }

    function guardarDatos() {
      return new Promise((resolve, reject) => {
        // Obtener la ficha seleccionada de sessionStorage
        const fichaSeleccionada = sessionStorage.getItem('fichaSeleccionada');
        
        // Crear FormData con todos los datos
        const formData = new FormData();
        formData.append('ficha', fichaSeleccionada);
        formData.append('tiempo', seconds);
        formData.append('errores', errorCount);
        
        // Enviar los datos al servidor mediante fetch
        fetch('php/guardar_historial_caminitos.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          return response.text();
        })
        .then(data => {
          console.log('Datos guardados:', data);
          resolve();
        })
        .catch(error => {
          console.error('Error:', error);
          reject(error);
        });
      });
    }

    // Inicializar
    window.addEventListener('load', () => {
      initParticles();
      initMQTT(); // Iniciar conexión MQTT
    });
  </script>
</body>
</html>