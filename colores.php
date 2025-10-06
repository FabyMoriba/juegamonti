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
    <title>Descubre los Colores del Bosque</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@0.8.4/dist/teachablemachine-image.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Patrick Hand', cursive;
            background-color: #e8f4ea;
            text-align: center;
            padding: 20px;
            background-image: url('https://img.freepik.com/vector-gratis/fondo-acuarela-verde-degradado_23-2148403778.jpg');
            background-size: cover;
            margin: 0;
            min-height: 100vh;
        }

        h1 {
            color: #2a5a3a;
            font-family: 'Quicksand', sans-serif;
            font-size: 2.8em;
            text-shadow: 2px 2px 0px #d4edda;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .game-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
            border: 6px solid #c8e6c9;
            position: relative;
            overflow: hidden;
        }

        .game-container::before {
            content: "";
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: url('https://img.freepik.com/vector-gratis/conjunto-dibujado-mano-elementos-bosque_23-2148857152.jpg') center/cover no-repeat;
            opacity: 0.1;
            z-index: -1;
        }

        #webcam-container {
            margin-top: 20px;
            border: 5px solid #a5d6a7;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        #label-container {
            display: none; /* Ocultamos el contenedor de etiquetas */
        }

        #status {
            margin-top: 20px;
            font-size: 26px;
            font-weight: bold;
            color: #2e7d32;
            background-color: #f1f8e9;
            padding: 12px;
            border-radius: 40px;
            display: inline-block;
            border: 3px solid #aed581;
        }

        .info-box {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            background-color: #dcedc8;
            padding: 10px 20px;
            border-radius: 18px;
            font-size: 20px;
            font-weight: bold;
            color: #33691e;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            border: 2px solid #9ccc65;
            min-width: 160px;
        }

        #main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 20px;
            flex-wrap: wrap;
        }

        #symbol-image {
            background-color: #fff;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 4px solid #81c784;
        }

        #color-symbol {
            width: 180px;
            height: 180px;
            object-fit: contain;
            transition: all 0.3s;
        }

        .nature-elements {
            font-size: 28px;
            color: #689f38;
            margin: 10px 0;
            letter-spacing: 5px;
        }

        .button {
            background-color: #81c784;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 20px;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Patrick Hand', cursive;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 4px 0 #519657;
            transition: all 0.2s;
            letter-spacing: 1px;
            margin: 10px;
        }

        .button:hover {
            background-color: #66bb6a;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #519657;
        }

        .button.back {
            background-color: #ff9800;
            box-shadow: 0 4px 0 #e65100;
        }

        .button.back:hover {
            background-color: #fb8c00;
            box-shadow: 0 2px 0 #e65100;
        }

        .leaf {
            position: absolute;
            width: 40px;
            height: 40px;
            background-image: url('https://cdn-icons-png.flaticon.com/512/2909/2909491.png');
            background-size: contain;
            animation: fall 8s linear infinite;
            opacity: 0.7;
            z-index: -1;
        }

        @keyframes fall {
            0% { transform: translateY(-100px) rotate(0deg); }
            100% { transform: translateY(calc(100vh + 100px)) rotate(360deg); }
        }

        .animal {
            position: absolute;
            width: 80px;
            height: auto;
            animation: float 4s ease-in-out infinite;
            opacity: 0.8;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .left-animal {
            left: 10px;
            top: 30%;
        }

        .right-animal {
            right: 10px;
            top: 60%;
        }

        @media (max-width: 768px) {
            #main-container {
                flex-direction: column;
            }
            
            .info-item {
                min-width: 120px;
                font-size: 18px;
            }
            
            h1 {
                font-size: 2.2em;
            }
        }
    </style>
</head>
<body>
    <img src="https://cdn-icons-png.flaticon.com/512/3069/3069172.png" class="animal left-animal" alt="Animal del bosque">
    <img src="https://cdn-icons-png.flaticon.com/512/3069/3069187.png" class="animal right-animal" alt="Animal del bosque">

    <div class="game-container">
        <div class="nature-elements">❀ ✿ ❁</div>
        <h1>Descubre los Colores del Bosque</h1>
        <div class="nature-elements">❀ ✿ ❁</div>
        
        <div class="info-box">
            <div id="timer" class="info-item">⏱️ Tiempo: 0s</div>
            <div id="errors" class="info-item">🍃 Errores: 0</div>
        </div>

        <div id="main-container">
            <div id="symbol-image">
                <img id="color-symbol" src="" alt="" width="180" height="180" style="display: none;">
            </div>

            <div id="webcam-container"></div>
        </div>
        
        <div id="label-container"></div>
        <div id="status">Cargando el juego mágico...</div>
        
        <div>
            <button class="button back" onclick="window.location.href='juegos.php'">Atrás</button>
            <button class="button" onclick="location.reload()">Jugar Otra Vez</button>
        </div>
    </div>
    <div id="estado-conexion" style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 5px;">
        Estado conexión: Conectando...
    </div>
    <script>
    // Función para crear hojas que caen
    function createLeaves() {
        for (let i = 0; i < 10; i++) {
            const leaf = document.createElement('div');
            leaf.className = 'leaf';
            leaf.style.left = Math.random() * 100 + 'vw';
            leaf.style.animationDuration = Math.random() * 5 + 5 + 's';
            leaf.style.animationDelay = Math.random() * 5 + 's';
            document.body.appendChild(leaf);
        }
    }
    // Función para guardar datos en la base de datos (modificada)
    function guardarDatos() {
        // Solo guardamos datos para los primeros dos colores (Azul y Rojo)
        if (currentStep >= 6) {
            return Promise.resolve(); // No hacemos nada para Verde y Amarillo
        }

        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('tiempo', secondsElapsed);
            formData.append('errores', errorCount);
            
            fetch('php/guardar_historial_colores.php', {
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
    const URL = "https://teachablemachine.withgoogle.com/models/LUQgchEOi/";
    let model, webcam, labelContainer, maxPredictions;
    let currentStep = 0;
    const sequence = ["Azul", "Rojo", "Verde", "Amarillo"];
    const confidenceThreshold = 0.95;
    let isWaiting = false;
    let reminderTimer;
    let lastPrediction = "";
    let errorDetectionTimeout = null;
    let timerInterval;
    let secondsElapsed = 0;
    let errorCount = 0;

    function startTimer() {
        clearInterval(timerInterval);
        secondsElapsed = 0;
        document.getElementById("timer").innerText = `⏱️ Tiempo: 0s`;
        timerInterval = setInterval(() => {
            secondsElapsed++;
            document.getElementById("timer").innerText = `⏱️ Tiempo: ${secondsElapsed}s`;
        }, 1000);
    }

    function stopTimer() {
        clearInterval(timerInterval);
    }
    // Configuración WebSocket para el ESP32
    const ESP32_IP = "juegamonti.online/ws-servo"; // Cambia por la IP de tu ESP32
    const socket = new WebSocket(`wss://${ESP32_IP}`);
    const estadoConexion = document.getElementById("estado-conexion");

    // Manejo de conexión WebSocket
    socket.onopen = () => {
        estadoConexion.textContent = "Estado conexión: Conectado al ESP32";
        console.log("Conexión WebSocket establecida");
        // Enciende el relé al iniciar la conexión
        socket.send("1");
    };

    socket.onclose = () => {
        estadoConexion.textContent = "Estado conexión: Desconectado";
        console.log("Conexión WebSocket cerrada");
    };

    socket.onerror = (error) => {
        estadoConexion.textContent = "Estado conexión: Error de conexión";
        console.error("Error en WebSocket:", error);
    };

    // Función para controlar el relé
    function controlarRele(comando) {
        if (socket.readyState === WebSocket.OPEN) {
            socket.send(comando);
            console.log(`Comando enviado al ESP32: ${comando}`);
        } else {
            console.error("WebSocket no está conectado");
        }
    }
    async function init() {
        // Enciende el relé cuando el modelo se carga
        controlarRele("1");
        
        createLeaves();
        
        const modelURL = URL + "model.json";
        const metadataURL = URL + "metadata.json";

        model = await tmImage.load(modelURL, metadataURL);
        maxPredictions = model.getTotalClasses();

        webcam = new tmImage.Webcam(300, 300, true);
        await webcam.setup();
        await webcam.play();
        window.requestAnimationFrame(loop);

        document.getElementById("webcam-container").appendChild(webcam.canvas);
        labelContainer = document.getElementById("label-container");
        labelContainer.innerHTML = '';

        for (let i = 0; i < maxPredictions; i++) {
            labelContainer.appendChild(document.createElement("div"));
        }

        speakAndShow(`Ahora encuentra el color: ${sequence[currentStep]}`);
        startTimer();
        window.requestAnimationFrame(loop);
    }

    async function loop() {
        webcam.update();
        await predict();
        window.requestAnimationFrame(loop);
    }

    function speakAndShow(message) {
        if (typeof message !== 'string' || message.includes("undefined")) {
            return;
        }

        const allowedMessages = [
            "Ahora encuentra el color:",
            "¡Muy bien! Color correcto",
            "Intenta otra vez",
            "¡Felicidades! Completaste todos los colores"
        ];

        const isAllowed = allowedMessages.some(allowed => message.includes(allowed));
        if (!isAllowed) return;

        updateStatus(message);
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'es-ES';
        utterance.rate = 0.85;
        window.speechSynthesis.speak(utterance);

        const color = sequence[currentStep];
        const imageElement = document.getElementById("color-symbol");
        
        if (message.includes("Ahora encuentra el color:") && ["Azul", "Rojo", "Verde", "Amarillo"].includes(color)) {
            const imageMap = {
                "Azul": "https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEj_3B8uF3PpmDN0tWFwad_xMlmk9j1uo6h0tAVRvECYmNew2nB5QseDaycm49KL1SroEjBSlj31hxlX8iQNI3_Qx_hSZ1OuNA6y5RuF8ITY2JX4gmumAgdWijde-_iPXamOaCEz2PR8owU/s640/gota+azul.jpg",
                "Rojo": "https://media.istockphoto.com/id/861067560/es/vector/trazos-de-pincel-mano-alzada.jpg?s=612x612&w=0&k=20&c=x-W_WtMGta10YNXrtue4XVjWruCY0FAQAnNX_CunbWo=",
                "Verde": "https://i.pinimg.com/736x/12/6a/0d/126a0ddb26e398bc6aeae4c9660c01bc.jpg",
                "Amarillo": "https://img.freepik.com/vector-premium/mancha-amarilla_74669-210.jpg"
            };
            
            imageElement.src = imageMap[color];
            imageElement.style.display = "block";
            imageElement.alt = `Color ${color}`;
        } else if (message.includes("¡Felicidades!")) {
            imageElement.style.display = "none";
        }
    }

       async function predict() {
        if (isWaiting) return;

        const prediction = await model.predict(webcam.canvas);
        prediction.sort((a, b) => b.probability - a.probability);
        lastPrediction = prediction[0].className;

        const expected = sequence[currentStep];
        const match = prediction.find(p => p.className === expected && p.probability >= confidenceThreshold);

        if (match) {
            isWaiting = true;
            updateStatus("🌿 ¡CORRECTO! 🌿");
            speakAndShow("¡Muy bien! Color correcto");

            if (errorDetectionTimeout) {
                clearTimeout(errorDetectionTimeout);
                errorDetectionTimeout = null;
            }

            try {
                await guardarDatos();
                console.log(`Datos guardados: Tiempo ${secondsElapsed}s, Errores ${errorCount} para ${expected}`);
            } catch (error) {
                console.error("Error al guardar datos:", error);
            }

            currentStep++;
            if (currentStep < sequence.length) {
                setTimeout(() => {
                    errorCount = 0;
                    secondsElapsed = 0;
                    document.getElementById("errors").innerText = `🍃 Errores: 0`;
                    document.getElementById("timer").innerText = `⏱️ Tiempo: 0s`;
                    isWaiting = false;
                    speakAndShow(`Ahora encuentra el color: ${sequence[currentStep]}`);
                    startTimer();
                }, 1500);
            } else {
                updateStatus("🏆 ¡LO LOGRASTE! 🌳");
                speakAndShow("¡Felicidades! Completaste todos los colores");
                stopTimer();
                createLeaves();
                document.getElementById("color-symbol").style.display = "none";
                
                // Apaga el relé cuando se completa el juego
                controlarRele("2");
            }
        } else {
            // Resto del código de detección de errores permanece igual
            const nadaPred = prediction.find(pred => pred.className === "nada" && pred.probability.toFixed(2) >= 0.5);

            if (!nadaPred && !errorDetectionTimeout) {
                errorDetectionTimeout = setTimeout(() => {
                    const strongWrong = prediction.some(pred => 
                        pred.probability >= 0.95 && 
                        pred.className !== "Nada" && 
                        pred.className !== expected
                    );
                    
                    if (strongWrong) {
                        errorCount++;
                        document.getElementById("errors").innerText = `🍃 Errores: ${errorCount}`;
                        updateStatus("¡Ups! Intenta otra vez");
                        speakAndShow(`Ahora encuentra el color: ${sequence[currentStep]}`);
                    }
                    errorDetectionTimeout = null;
                }, 1500);
            }
        }
    }

    function updateStatus(message) {
        const statusElement = document.getElementById("status");
        statusElement.innerText = message;
        
        statusElement.style.animation = "none";
        void statusElement.offsetWidth;
        statusElement.style.animation = "pulse 0.5s";
    }

    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);

    window.addEventListener("load", () => {
        init();
    });

    // Cierra la conexión WebSocket cuando se cierra la página
    window.addEventListener("beforeunload", () => {
        if (socket.readyState === WebSocket.OPEN) {
            socket.close();
        }
    });
    </script>
</body>
</html>