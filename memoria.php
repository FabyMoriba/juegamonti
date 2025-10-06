<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || $_SESSION['tipo_operador'] != 'hijo') {
    header("Location: inicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Memoria Motriz para Niños</title>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
  <style>
    /* (Todos los estilos CSS se mantienen igual) */
    :root {
      --primary: #FF6B6B;
      --primary-dark: #FF5252;
      --secondary: #4ECDC4;
      --accent: #FFD93D;
      --background: #FFFAF0;
      --card-bg: #FFFFFF;
      --text: #2C3E50;
      --success: #6BCF7F;
      --purple: #A56CF7;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;
      background: linear-gradient(135deg, #FFEAA7 0%, #FFD3B6 50%, #FFAAA5 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      color: var(--text);
      overflow-x: hidden;
    }
    
    .container {
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 25px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      padding: 30px;
      width: 100%;
      max-width: 850px;
      text-align: center;
      border: 8px solid var(--accent);
      position: relative;
      overflow: hidden;
    }
    
    .container::before {
      content: "";
      position: absolute;
      top: -10px;
      left: -10px;
      right: -10px;
      bottom: -10px;
      background: linear-gradient(45deg, var(--primary), var(--secondary), var(--accent), var(--purple));
      z-index: -1;
      border-radius: 30px;
      animation: rainbow-border 4s linear infinite;
    }
    
    @keyframes rainbow-border {
      0% { filter: hue-rotate(0deg); }
      100% { filter: hue-rotate(360deg); }
    }
    
    h1 {
      color: var(--primary);
      margin-bottom: 15px;
      font-size: 3rem;
      text-shadow: 3px 3px 0px rgba(0, 0, 0, 0.1);
      background: linear-gradient(to right, var(--primary), var(--purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      position: relative;
      display: inline-block;
    }
    
    h1::after {
      content: "🧠";
      position: absolute;
      right: -50px;
      top: -10px;
      font-size: 2.5rem;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    
    .stats {
      display: flex;
      justify-content: space-around;
      margin: 25px 0;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .stat-box {
      background-color: var(--card-bg);
      border-radius: 15px;
      padding: 15px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
      min-width: 130px;
      border: 4px solid;
      transition: transform 0.3s ease;
    }
    
    .stat-box:hover {
      transform: scale(1.05);
    }
    
    .stat-box:nth-child(1) {
      border-color: var(--primary);
    }
    
    .stat-box:nth-child(2) {
      border-color: var(--secondary);
    }
    
    .stat-box:nth-child(3) {
      border-color: var(--accent);
    }
    
    .stat-label {
      font-size: 1.1rem;
      color: var(--text);
      margin-bottom: 5px;
    }
    
    .stat-value {
      font-size: 2.2rem;
      font-weight: bold;
    }
    
    #nivel {
      color: var(--primary);
    }
    
    #tiempo {
      color: var(--secondary);
    }
    
    #errores {
      color: var(--accent);
    }
    
    .tablero {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 15px;
      margin: 30px 0;
    }
    
    .movimiento {
      background: var(--card-bg);
      border-radius: 20px;
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
      padding: 15px;
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
      aspect-ratio: 1/1;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 5px solid transparent;
      position: relative;
      overflow: hidden;
    }
    
    .movimiento::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.5), transparent);
      transform: translateX(-100%);
      transition: transform 0.5s;
    }
    
    .movimiento:hover::before {
      transform: translateX(100%);
    }
    
    .movimiento:hover {
      transform: translateY(-8px) rotate(3deg);
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
    }
    
    .movimiento.activo {
      background-color: var(--accent);
      transform: scale(1.1);
      border-color: var(--primary);
      animation: pulse 0.5s;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.15); }
      100% { transform: scale(1.1); }
    }
    
    .movimiento.correcto {
      background-color: var(--success);
      border-color: var(--success);
    }
    
    .movimiento.incorrecto {
      background-color: var(--primary);
      border-color: var(--primary-dark);
      animation: shake 0.5s;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }
    
    .movimiento img {
      width: 85%;
      height: auto;
      max-width: 90px;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }
    
    .movimiento:hover img {
      transform: scale(1.1);
    }
    
    .controls {
      margin: 25px 0;
      display: flex;
      justify-content: center;
      gap: 20px;
    }
    
    button {
      padding: 15px 30px;
      font-size: 1.3rem;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      color: white;
      margin: 0 10px;
      transition: all 0.3s ease;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
      font-weight: bold;
      position: relative;
      overflow: hidden;
    }
    
    button::before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.5s;
    }
    
    button:hover::before {
      left: 100%;
    }
    
    #iniciar-btn {
      background: linear-gradient(to right, var(--secondary), var(--purple));
    }
    
    #reiniciar-btn {
      background: linear-gradient(to right, var(--primary), #FF8E8E);
    }
    
    button:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }
    
    button:active {
      transform: translateY(0) scale(1);
    }
    
    button:disabled {
      background: #CCCCCC;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    .mensaje {
      margin-top: 25px;
      font-size: 1.6rem;
      font-weight: bold;
      min-height: 50px;
      color: var(--primary);
      padding: 15px;
      border-radius: 15px;
      background-color: rgba(255, 255, 255, 0.7);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .mensaje.correcto {
      background-color: rgba(107, 207, 127, 0.2);
      color: #2E7D32;
    }
    
    .mensaje.error {
      background-color: rgba(255, 107, 107, 0.2);
      color: #C62828;
    }
    
    .progress-container {
      width: 100%;
      height: 20px;
      background-color: #E0E0E0;
      border-radius: 10px;
      margin: 25px 0;
      overflow: hidden;
      box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
      border: 3px solid white;
    }
    
    .progress-bar {
      height: 100%;
      background: linear-gradient(to right, var(--secondary), var(--purple));
      width: 0%;
      transition: width 0.5s ease;
      border-radius: 7px;
    }
    
    .nivel-info {
      margin: 15px 0;
      font-size: 1.4rem;
      font-weight: bold;
      padding: 10px;
      border-radius: 10px;
      background: linear-gradient(to right, rgba(78, 205, 196, 0.2), rgba(165, 108, 247, 0.2));
    }
    
    .emoji {
      font-size: 1.5rem;
      margin: 0 5px;
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .conexion-tablero {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 10px 15px;
      border-radius: 20px;
      font-weight: bold;
      z-index: 1000;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .tablero-online {
      background-color: var(--success);
      color: white;
    }
    
    .tablero-offline {
      background-color: var(--primary);
      color: white;
    }
    
    @media (max-width: 600px) {
      .tablero {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
      }
      
      .stats {
        flex-direction: column;
        align-items: center;
      }
      
      h1 {
        font-size: 2.2rem;
      }
      
      .controls {
        flex-direction: column;
        align-items: center;
      }
      
      button {
        width: 80%;
        margin-bottom: 10px;
      }
      
      .conexion-tablero {
        position: relative;
        top: 0;
        right: 0;
        margin-bottom: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="conexion-tablero tablero-offline" id="conexion-tablero">⏳ Esperando tablero...</div>
  
  <div class="container">
    <h1>Memoria Motriz <span class="emoji">👧👦</span></h1>
    
    <div class="stats">
      <div class="stat-box">
        <div class="stat-label">Nivel</div>
        <div class="stat-value" id="nivel">0</div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Tiempo</div>
        <div class="stat-value" id="tiempo">0s</div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Errores</div>
        <div class="stat-value" id="errores">0/3</div>
      </div>
    </div>
    
    <div class="nivel-info" id="nivel-info">¡Presiona Iniciar para jugar! <span class="emoji">🎮</span></div>
    
    <div class="progress-container">
      <div class="progress-bar" id="progreso"></div>
    </div>
    
    <div class="tablero">
      <div class="movimiento" data-id="1">
        <img src="img/hands1.jpg" alt="Mano 1">
      </div>
      <div class="movimiento" data-id="2">
        <img src="img/hands2.jpg" alt="Mano 2">
      </div>
      <div class="movimiento" data-id="3">
        <img src="img/hands3.jpg" alt="Mano 3">
      </div>
      <div class="movimiento" data-id="4">
        <img src="img/hands4.jpg" alt="Mano 4">
      </div>
      <div class="movimiento" data-id="5">
        <img src="img/hands5.jpg" alt="Mano 5">
      </div>
    </div>

    <div class="controls">
      <button id="iniciar-btn">Iniciar Juego <span class="emoji">🚀</span></button>
      <button id="reiniciar-btn" disabled>Reiniciar <span class="emoji">🔄</span></button>
    </div>
    
    <div class="mensaje" id="mensaje">Conectando con el tablero táctil... <span class="emoji">⏳</span></div>
  </div>

  <script>
    // Configuración MQTT
    const MQTT_CONFIG = {
      broker: 'wss://broker.emqx.io:8084/mqtt',
      topic: 'Salida/06',
      options: {
        clientId: 'WebClient_' + Math.random().toString(16).substr(2, 8),
        keepalive: 60,
        reconnectPeriod: 2000
      }
    };

    let secuencia = [];
    let jugadorSecuencia = [];
    let nivel = 0;
    let errores = 0;
    let tiempoInicio = 0;
    let cronometro = null;
    let juegoActivo = false;
    let mostrandoSecuencia = false;
    let velocidadSecuencia = 1000;
    let clienteMQTT = null;
    let esperandoInputTouch = false;
    
    // Variables para control de conexión del tablero
    let ultimoMensajeTablero = 0;
    let tableroConectado = false;
    let intervaloVerificacion = null;
    
    // Variables para guardar datos del historial
    let tiempoTotalJuego = 0;
    let erroresTotales = 0;
    let nivelMaximoAlcanzado = 0;
    let descripcionNivel = "";
    
    const movimientoElements = document.querySelectorAll('.movimiento');
    const mensajeElement = document.getElementById('mensaje');
    const nivelElement = document.getElementById('nivel');
    const tiempoElement = document.getElementById('tiempo');
    const erroresElement = document.getElementById('errores');
    const progresoElement = document.getElementById('progreso');
    const iniciarBtn = document.getElementById('iniciar-btn');
    const reiniciarBtn = document.getElementById('reiniciar-btn');
    const nivelInfoElement = document.getElementById('nivel-info');
    const conexionTablero = document.getElementById('conexion-tablero');
    
    // Función para guardar datos en el historial (CORREGIDA)
// Función para guardar datos en el historial (MEJORADA)
function guardarDatosHistorial() {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('tiempo', tiempoTotalJuego);
        formData.append('errores', erroresTotales);
        formData.append('nivel', descripcionNivel);
        
        fetch('php/guardar_historial_memoria.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                // Limpiar la respuesta - eliminar posibles caracteres al inicio
                const cleanText = text.trim();
                
                // Buscar el inicio del JSON (primer {)
                const jsonStartIndex = cleanText.indexOf('{');
                if (jsonStartIndex === -1) {
                    throw new Error('No se encontró JSON en la respuesta: ' + cleanText.substring(0, 100));
                }
                
                // Extraer solo la parte JSON
                const jsonText = cleanText.substring(jsonStartIndex);
                
                try {
                    return JSON.parse(jsonText);
                } catch (e) {
                    console.error('Error parseando JSON:', e);
                    console.error('Texto recibido:', cleanText);
                    throw new Error('Error parseando JSON: ' + e.message);
                }
            });
        })
        .then(data => {
            if (data.success) {
                console.log('✅ Datos guardados:', data.message, 'ID:', data.id);
                resolve(data);
            } else {
                throw new Error(data.error || 'Error al guardar datos');
            }
        })
        .catch(error => {
            console.error('❌ Error en guardarDatosHistorial:', error);
            reject(error);
        });
    });
}

// Función para guardar resultados (modificada para ser más robusta)
function guardarResultados() {
    if (nivelMaximoAlcanzado > 0 && tiempoTotalJuego > 0) {
        console.log('Guardando resultados:', {
            tiempo: tiempoTotalJuego,
            errores: erroresTotales,
            nivel: descripcionNivel
        });
        
        guardarDatosHistorial()
            .then((data) => {
                console.log('✅ Resultados guardados exitosamente:', data);
            })
            .catch(error => {
                console.error('❌ Error al guardar resultados:', error);
                // Mostrar mensaje al usuario pero no interrumpir el juego
                mensajeElement.innerHTML = '⚠️ Error al guardar historial, pero el juego continúa <span class="emoji">📝</span>';
                setTimeout(() => {
                    if (juegoActivo) {
                        mensajeElement.innerHTML = "¡Tu turno! Toca los sensores físicos 👆";
                    }
                }, 3000);
            });
    }
}

    // Función para guardar resultados
    function guardarResultados() {
        if (nivelMaximoAlcanzado > 0) {
            guardarDatosHistorial()
                .then(() => {
                    console.log('Resultados guardados exitosamente');
                })
                .catch(error => {
                    console.error('Error al guardar resultados:', error);
                });
        }
    }

    // Inicializar conexión MQTT
    function conectarMQTT() {
      try {
        console.log('Conectando a MQTT...');
        clienteMQTT = mqtt.connect(MQTT_CONFIG.broker, MQTT_CONFIG.options);

        clienteMQTT.on('connect', function () {
          console.log('Conectado a MQTT');
          mensajeElement.innerHTML = 'Conectado al broker, esperando tablero... <span class="emoji">⏳</span>';
          
          clienteMQTT.subscribe(MQTT_CONFIG.topic, function (err) {
            if (!err) {
              console.log('Suscrito a Salida/06');
              iniciarVerificacionTablero();
            }
          });
        });

        clienteMQTT.on('message', function (topic, message) {
          const data = message.toString();
          console.log('Mensaje recibido:', data);
          
          // Actualizar timestamp del último mensaje
          ultimoMensajeTablero = Date.now();
          
          // Procesar mensaje del tablero
          procesarMensajeTablero(data);
        });

        clienteMQTT.on('error', function (error) {
          console.error('Error MQTT:', error);
          actualizarEstadoTablero(false, 'Error de conexión');
          detenerVerificacionTablero();
        });

        clienteMQTT.on('reconnect', function () {
          console.log('Reconectando...');
          actualizarEstadoTablero(false, 'Reconectando...');
          detenerVerificacionTablero();
        });

      } catch (error) {
        console.error('Error al conectar MQTT:', error);
        actualizarEstadoTablero(false, 'Error de conexión');
      }
    }

    function procesarMensajeTablero(mensaje) {
      try {
        // Verificar si es un mensaje de estado del ESP32
        if (mensaje.includes('ESP32 conectado') || mensaje.includes('WiFi:') || mensaje.includes('MQTT:')) {
          console.log('Mensaje de estado del tablero:', mensaje);
          actualizarEstadoTablero(true, 'Tablero conectado');
          
        } 
        // Verificar si es un mensaje de touch
        else if (mensaje.includes(':')) {
          const partes = mensaje.split(':');
          if (partes.length >= 2) {
            const nombreTouch = partes[0];
            const estado = partes[1];
            
            console.log(`Touch: ${nombreTouch} - ${estado}`);
            
            // Marcar tablero como conectado
            actualizarEstadoTablero(true, 'Tablero respondiendo');
            
            // Procesar touch si estamos en juego
            if (estado === 'ACTIVADO' && esperandoInputTouch && juegoActivo && !mostrandoSecuencia) {
              const mapeoTouch = {
                'uno': 1, 'dos': 2, 'tres': 3, 'cuatro': 4, 'cinco': 5
              };
              
              const movimientoId = mapeoTouch[nombreTouch];
              if (movimientoId) {
                console.log(`Touch detectado: ${nombreTouch} -> Movimiento ${movimientoId}`);
                seleccionarMovimiento(movimientoId);
                
                // Resaltar visualmente el movimiento
                const movimientoElement = document.querySelector(`[data-id='${movimientoId}']`);
                if (movimientoElement) {
                  movimientoElement.classList.add('activo');
                  setTimeout(() => movimientoElement.classList.remove('activo'), 500);
                }
              }
            }
          }
        }
      } catch (error) {
        console.error('Error procesando mensaje:', error);
      }
    }

    function iniciarVerificacionTablero() {
      // Verificar cada 5 segundos si estamos recibiendo mensajes del tablero
      intervaloVerificacion = setInterval(() => {
        const tiempoDesdeUltimoMensaje = Date.now() - ultimoMensajeTablero;
        
        if (ultimoMensajeTablero === 0) {
          // Nunca hemos recibido un mensaje
          actualizarEstadoTablero(false, 'Esperando tablero...');
        } else if (tiempoDesdeUltimoMensaje > 15000) {
          // No hay mensajes en los últimos 15 segundos
          actualizarEstadoTablero(false, 'Tablero no responde');
        }
      }, 5000);
    }

    function detenerVerificacionTablero() {
      if (intervaloVerificacion) {
        clearInterval(intervaloVerificacion);
        intervaloVerificacion = null;
      }
    }

    function actualizarEstadoTablero(conectado, mensaje) {
      tableroConectado = conectado;
      
      if (conectado) {
        conexionTablero.textContent = '✅ Tablero Conectado';
        conexionTablero.className = 'conexion-tablero tablero-online';
        mensajeElement.innerHTML = '✅ Tablero conectado. ¡Listo para jugar! <span class="emoji">🎮</span>';
      } else {
        conexionTablero.textContent = '❌ ' + (mensaje || 'Tablero Desconectado');
        conexionTablero.className = 'conexion-tablero tablero-offline';
        mensajeElement.innerHTML = '❌ ' + (mensaje || 'Tablero no disponible') + ' <span class="emoji">🔌</span>';
      }
    }

    // Añadir event listeners para clics
    movimientoElements.forEach(movimiento => {
      movimiento.addEventListener('click', () => {
        if (!juegoActivo || mostrandoSecuencia) return;
        
        const id = parseInt(movimiento.getAttribute('data-id'));
        seleccionarMovimiento(id);
      });
    });
    
    iniciarBtn.addEventListener('click', iniciarJuego);
    reiniciarBtn.addEventListener('click', reiniciarJuego);
    
    function iniciarJuego() {
      // Verificar si el tablero está conectado antes de iniciar
      if (!tableroConectado) {
        mensajeElement.innerHTML = '❌ Tablero no detectado. Verifica la conexión. <span class="emoji">🔌</span>';
        mensajeElement.className = 'mensaje error';
        return;
      }
      
      // Reiniciar variables de juego
      secuencia = [];
      jugadorSecuencia = [];
      nivel = 0;
      errores = 0;
      juegoActivo = true;
      mostrandoSecuencia = false;
      velocidadSecuencia = 1000;
      esperandoInputTouch = false;
      
      // Reiniciar estadísticas para el historial
      tiempoTotalJuego = 0;
      erroresTotales = 0;
      nivelMaximoAlcanzado = 0;
      descripcionNivel = "";
      
      iniciarBtn.disabled = true;
      reiniciarBtn.disabled = false;
      
      siguienteNivel();
    }
    
    function reiniciarJuego() {
      // Guardar resultados antes de reiniciar
      guardarResultados();
      
      clearInterval(cronometro);
      juegoActivo = false;
      esperandoInputTouch = false;
      mensajeElement.textContent = "¡Juego reiniciado! 🎉";
      mensajeElement.className = "mensaje";
      nivelElement.textContent = "0";
      tiempoElement.textContent = "0s";
      erroresElement.textContent = "0/3";
      progresoElement.style.width = "0%";
      nivelInfoElement.textContent = "¡Presiona Iniciar para jugar! 🎮";
      
      movimientoElements.forEach(mov => {
        mov.classList.remove('activo', 'correcto', 'incorrecto');
      });
      
      iniciarBtn.disabled = false;
      reiniciarBtn.disabled = true;
    }
    
    function iniciarCronometro() {
      tiempoInicio = Date.now();
      clearInterval(cronometro);
      
      cronometro = setInterval(() => {
        const tiempoTranscurrido = Math.floor((Date.now() - tiempoInicio) / 1000);
        tiempoElement.textContent = `${tiempoTranscurrido}s`;
        tiempoTotalJuego = tiempoTranscurrido; // Actualizar tiempo total
      }, 1000);
    }
    
    function detenerCronometro() {
      clearInterval(cronometro);
    }
    
    function determinarLongitudSecuencia() {
      if (nivel <= 2) return 1;
      if (nivel <= 5) return 2;
      if (nivel <= 8) return 3;
      if (nivel <= 11) return 4;
      return 5;
    }
    
    function actualizarInfoNivel(longitud) {
      let textoDificultad = "";
      let emoji = "";
      
      if (longitud === 1) {
        textoDificultad = "Fácil (1 movimiento)";
        emoji = "😊";
      } else if (longitud === 2) {
        textoDificultad = "Básico (2 movimientos)";
        emoji = "👍";
      } else if (longitud === 3) {
        textoDificultad = "Intermedio (3 movimientos)";
        emoji = "💪";
      } else if (longitud === 4) {
        textoDificultad = "Avanzado (4 movimientos)";
        emoji = "🚀";
      } else {
        textoDificultad = "Experto (5 movimientos)";
        emoji = "🏆";
      }
      
      // Guardar la descripción del nivel exactamente como se muestra
      descripcionNivel = `Nivel ${nivel} - ${textoDificultad}`;
      nivelMaximoAlcanzado = nivel;
      
      nivelInfoElement.innerHTML = `${descripcionNivel} <span class="emoji">${emoji}</span>`;
    }
    
    function siguienteNivel() {
      jugadorSecuencia = [];
      nivel++;
      errores = 0;
      nivelElement.textContent = nivel;
      erroresElement.textContent = "0/3";
      progresoElement.style.width = "0%";
      
      const longitudSecuencia = determinarLongitudSecuencia();
      actualizarInfoNivel(longitudSecuencia);
      
      mensajeElement.innerHTML = `Nivel ${nivel}. ¡Observa la secuencia! 👀`;
      mensajeElement.className = "mensaje";
      
      secuencia = [];
      for (let i = 0; i < longitudSecuencia; i++) {
        secuencia.push(Math.floor(Math.random() * 5) + 1);
      }
      
      mostrandoSecuencia = true;
      esperandoInputTouch = false;
      
      setTimeout(() => {
        mostrarSecuencia();
      }, 1000);
    }
    
    function mostrarSecuencia() {
      let i = 0;
      const interval = setInterval(() => {
        const movimientoId = secuencia[i];
        const movimiento = document.querySelector(`[data-id='${movimientoId}']`);
        
        movimiento.classList.add('activo');
        setTimeout(() => {
          movimiento.classList.remove('activo');
        }, 500);
        
        i++;
        if (i >= secuencia.length) {
          clearInterval(interval);
          setTimeout(() => {
            mensajeElement.innerHTML = "¡Tu turno! Toca los sensores físicos 👆";
            mostrandoSecuencia = false;
            esperandoInputTouch = true;
            iniciarCronometro();
          }, 500);
        }
      }, velocidadSecuencia);
    }
    
    function seleccionarMovimiento(num) {
      if (!esperandoInputTouch) return;
      
      const movimiento = document.querySelector(`[data-id='${num}']`);
      movimiento.classList.add('activo');
      setTimeout(() => movimiento.classList.remove('activo'), 300);
      
      jugadorSecuencia.push(num);
      
      const progreso = (jugadorSecuencia.length / secuencia.length) * 100;
      progresoElement.style.width = `${progreso}%`;
      
      if (num !== secuencia[jugadorSecuencia.length - 1]) {
        errores++;
        erroresTotales++; // Acumular errores totales
        erroresElement.textContent = `${errores}/3`;
        movimiento.classList.add('incorrecto');
        setTimeout(() => movimiento.classList.remove('incorrecto'), 500);
        
       if (errores >= 3) {
    mensajeElement.innerHTML = "❌ Demasiados errores. ¡Siguiente nivel! 🌈";
    mensajeElement.className = "mensaje error";
    detenerCronometro();
    esperandoInputTouch = false;
    
    // Guardar resultados (no esperar a que termine)
    guardarResultados();
    
    setTimeout(() => siguienteNivel(), 2000);
    return;
} else {
          mensajeElement.innerHTML = `❌ Error. Te quedan ${3 - errores} intentos. 💪`;
          mensajeElement.className = "mensaje error";
          jugadorSecuencia = [];
          progresoElement.style.width = "0%";
          return;
        }
      } else {
        movimiento.classList.add('correcto');
        setTimeout(() => movimiento.classList.remove('correcto'), 500);
      }
      
      if (jugadorSecuencia.length === secuencia.length) {
    detenerCronometro();
    esperandoInputTouch = false;
    mensajeElement.innerHTML = '✔️ ¡Correcto! ¡Pasando al siguiente nivel! 🎉';
    mensajeElement.className = "mensaje correcto";
    
    // Guardar resultados (no esperar a que termine)
    guardarResultados();
    
    setTimeout(() => siguienteNivel(), 1500);
}
    }

    // Inicializar cuando la página cargue
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Página cargada, conectando...');
      conectarMQTT();
    });

    // Manejar cierre de página - guardar resultados finales
    window.addEventListener('beforeunload', function() {
      guardarResultados();
      
      if (clienteMQTT && clienteMQTT.connected) {
        clienteMQTT.end();
      }
      detenerVerificacionTablero();
    });
  </script>
</body>
</html>